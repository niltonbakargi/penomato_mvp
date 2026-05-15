<?php
// ================================================
// GERENCIAR MEMBROS — VISÃO DO GESTOR
// Aceitar pendentes e excluir membros ativos
// ================================================
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';
require_once __DIR__ . '/../../config/email.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'gestor') {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

$gestor_id = (int)$_SESSION['usuario_id'];
$msg = [];

// ================================================
// POST: ACEITAR MEMBRO
// ================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aceitar_membro'])) {
    $membro_id = (int)($_POST['membro_aceitar_id'] ?? 0);
    $motivacao = trim($_POST['motivacao_aceitar'] ?? '');
    $categoria = trim($_POST['categoria_aceitar'] ?? 'colaborador');

    if ($membro_id) {
        $stmt_m = $pdo->prepare("SELECT email, nome, status_verificacao FROM usuarios WHERE id = ?");
        $stmt_m->execute([$membro_id]);
        $membro = $stmt_m->fetch(PDO::FETCH_ASSOC);

        if (!$membro || $membro['status_verificacao'] !== 'aguardando_gestor') {
            $msg[] = ['tipo' => 'err', 'texto' => 'Este membro ainda não confirmou o e-mail. A aprovação só é possível após a confirmação.'];
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET status_verificacao = 'verificado', ativo = 1, categoria = ? WHERE id = ?");
            $stmt->execute([$categoria, $membro_id]);
            error_log(sprintf(
                '[GESTOR_AUDIT] aceitar_membro | gestor_id=%d | membro_id=%d | nome=%s | categoria=%s | ip=%s',
                $gestor_id, $membro_id, $membro['nome'] ?? 'desconhecido', $categoria, $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ));
            $msg[] = ['tipo' => 'ok', 'texto' => 'Membro aceito com sucesso.' . ($motivacao ? " Motivo: $motivacao" : '')];

            $conteudo_email = "
                <p>Olá, <strong>" . htmlspecialchars($membro['nome']) . "</strong>!</p>
                <p>Seu cadastro no <strong>Penomato</strong> foi <strong style='color:#0b5e42;'>ACEITO</strong>!</p>
                <p>Sua conta foi ativada como <strong>" . htmlspecialchars(ucfirst($categoria)) . "</strong> e você já pode acessar a plataforma.</p>"
                . ($motivacao ? "<p><strong>Observações:</strong> " . htmlspecialchars($motivacao) . "</p>" : "")
                . "<p style='margin-top:20px;'>
                    <a href='" . APP_URL . "/src/Views/auth/login.php'
                       style='background:#0b5e42;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:600;'>
                        Acessar a plataforma
                    </a>
                </p>";
            enviarEmail($membro['email'], 'Cadastro aceito — Penomato', templateEmail('Bem-vindo ao Penomato!', $conteudo_email));
        }
    } else {
        $msg[] = ['tipo' => 'err', 'texto' => 'Selecione um membro.'];
    }
}

// ================================================
// POST: EXCLUIR MEMBRO
// ================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_membro'])) {
    $membro_id = (int)($_POST['membro_excluir_id'] ?? 0);
    $motivacao = trim($_POST['motivacao_excluir'] ?? '');

    if ($membro_id && $membro_id !== $gestor_id) {
        $stmt_m = $pdo->prepare("SELECT email, nome FROM usuarios WHERE id = ?");
        $stmt_m->execute([$membro_id]);
        $membro_excluir = $stmt_m->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$membro_id]);
        error_log(sprintf(
            '[GESTOR_AUDIT] excluir_membro | gestor_id=%d | membro_id=%d | nome=%s | email=%s | motivo=%s | ip=%s',
            $gestor_id, $membro_id,
            $membro_excluir['nome']  ?? 'desconhecido',
            $membro_excluir['email'] ?? 'desconhecido',
            $motivacao ?: '(sem motivo)',
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ));
        $msg[] = ['tipo' => 'ok', 'texto' => 'Membro removido.' . ($motivacao ? " Motivo: $motivacao" : '')];

        if ($membro_excluir) {
            $conteudo_email = "
                <p>Olá, <strong>" . htmlspecialchars($membro_excluir['nome']) . "</strong>.</p>
                <p>Informamos que seu acesso ao <strong>Penomato</strong> foi <strong style='color:#dc3545;'>removido</strong>.</p>"
                . ($motivacao ? "<p><strong>Motivo:</strong> " . htmlspecialchars($motivacao) . "</p>" : "")
                . "<p>Para mais informações, entre em contato com a equipe gestora.</p>";
            enviarEmail($membro_excluir['email'], 'Acesso removido — Penomato', templateEmail('Notificação de remoção', $conteudo_email));
        }
    } else {
        $msg[] = ['tipo' => 'err', 'texto' => 'Selecione um membro válido (você não pode excluir a si mesmo).'];
    }
}

// ================================================
// BUSCAR DADOS
// ================================================
try {
    $membros_pendentes = $pdo->query("
        SELECT id, nome, email, categoria, data_cadastro
        FROM usuarios
        WHERE status_verificacao = 'aguardando_gestor'
        ORDER BY data_cadastro ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $membros_ativos = $pdo->query("
        SELECT id, nome, email, categoria, data_cadastro
        FROM usuarios
        WHERE ativo = 1 AND status_verificacao = 'verificado'
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $membros_pendentes = [];
    $membros_ativos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Membros — Penomato</title>
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
    <style>
        body {
            background: #e8ede8;
            min-height: 100vh;
            padding: 30px 20px;
            color: #1a2a1a;
        }

        .page-header {
            background: #0b5e42;
            color: #fff;
            padding: 20px 32px;
            border-radius: 12px;
            margin-bottom: 28px;
            max-width: 860px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .page-header h1 { font-size: 1.4em; font-weight: 700; color: #fff; margin: 0; }
        .btn-voltar {
            background: rgba(255,255,255,0.15);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.4);
            border-radius: 6px;
            padding: 7px 16px;
            font-size: 0.88em;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
        }
        .btn-voltar:hover { background: rgba(255,255,255,0.25); }

        .container { max-width: 860px; margin: 0 auto; }

        .msg-ok   { background:var(--sucesso-fundo); color:var(--sucesso-texto); border:1px solid #c3e6cb; border-radius:6px; padding:10px 14px; margin-bottom:14px; font-size:0.9em; }
        .msg-warn { background:var(--aviso-fundo);   color:var(--aviso-texto);   border:1px solid #ffeeba; border-radius:6px; padding:10px 14px; margin-bottom:14px; font-size:0.9em; }
        .msg-err  { background:var(--perigo-fundo);  color:var(--perigo-texto);  border:1px solid #f5c6cb; border-radius:6px; padding:10px 14px; margin-bottom:14px; font-size:0.9em; }

        .secao {
            background: var(--branco);
            border-radius: 12px;
            padding: 24px 28px;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .secao h2 {
            font-size: 1.1em;
            font-weight: 700;
            color: var(--cor-primaria);
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0ede8;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .secao h2.danger { color: var(--perigo-cor); border-color: #f5c6cb; }
        .badge-count {
            background: #dc2626;
            color: #fff;
            font-size: 0.72em;
            font-weight: 700;
            min-width: 20px;
            height: 20px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
        }

        .membro-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #fafafa;
        }
        .membro-card:last-child { margin-bottom: 0; }
        .membro-info { flex: 1; min-width: 0; }
        .membro-nome { font-weight: 600; font-size: 0.95em; color: #1a2a1a; }
        .membro-email { font-size: 0.82em; color: #666; margin-top: 2px; }
        .membro-meta { font-size: 0.78em; color: #888; margin-top: 3px; }
        .tag-categoria {
            display: inline-block;
            background: #e8f5ee;
            color: #0b5e42;
            border-radius: 4px;
            padding: 2px 8px;
            font-size: 0.78em;
            font-weight: 600;
        }
        .membro-acoes { display: flex; gap: 8px; align-items: center; flex-shrink: 0; }

        .btn-aceitar {
            background: var(--cor-primaria);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 7px 16px;
            font-size: 0.85em;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-aceitar:hover { background: var(--cor-primaria-hover); }

        .btn-excluir {
            background: none;
            color: var(--perigo-cor);
            border: 1px solid var(--perigo-cor);
            border-radius: 6px;
            padding: 7px 14px;
            font-size: 0.85em;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-excluir:hover { background: var(--perigo-fundo); }

        .vazio { font-size: 0.88em; color: #888; text-align: center; padding: 16px 0; }

        /* formulário de confirmação inline */
        .form-inline {
            display: none;
            background: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 16px;
            margin-top: 10px;
        }
        .form-inline.ativo { display: block; }
        .form-inline label { display: block; font-size: 0.85em; font-weight: 600; color: #555; margin-bottom: 5px; }
        .form-inline select,
        .form-inline textarea,
        .form-inline input[type=text] {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 0.9em;
            font-family: inherit;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        .form-inline select:focus,
        .form-inline textarea:focus { outline: none; border-color: var(--cor-primaria); }
        .form-inline textarea { min-height: 70px; resize: vertical; }
        .form-inline-footer { display: flex; gap: 8px; justify-content: flex-end; }
        .btn-cancel-sm {
            background: none; color: #777; border: 1px solid #ccc;
            border-radius: 6px; padding: 7px 14px; font-size: 0.85em; cursor: pointer;
        }
        .btn-cancel-sm:hover { background: #f0f0f0; }

        @media (max-width: 600px) {
            .membro-card { flex-direction: column; align-items: flex-start; }
            .page-header { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

<div class="page-header">
    <h1>👥 Gerenciar Membros</h1>
    <a href="<?= APP_BASE ?>/src/Controllers/controlador_gestor.php" class="btn-voltar">← Voltar ao painel</a>
</div>

<div class="container">

    <?php foreach ($msg as $m): ?>
        <div class="msg-<?= $m['tipo'] ?>"><?= htmlspecialchars($m['texto']) ?></div>
    <?php endforeach; ?>

    <!-- ════════════════════════════════════════ -->
    <!-- MEMBROS PENDENTES                         -->
    <!-- ════════════════════════════════════════ -->
    <div class="secao">
        <h2>
            ⏳ Pendentes de aprovação
            <?php if (count($membros_pendentes) > 0): ?>
                <span class="badge-count"><?= count($membros_pendentes) ?></span>
            <?php endif; ?>
        </h2>

        <?php if (empty($membros_pendentes)): ?>
            <p class="vazio">Nenhum membro aguardando aprovação.</p>
        <?php else: ?>
            <?php foreach ($membros_pendentes as $p): ?>
                <div class="membro-card" id="card-p-<?= $p['id'] ?>">
                    <div class="membro-info">
                        <div class="membro-nome"><?= htmlspecialchars($p['nome']) ?></div>
                        <div class="membro-email"><?= htmlspecialchars($p['email']) ?></div>
                        <div class="membro-meta">
                            Cadastrado em <?= date('d/m/Y', strtotime($p['data_cadastro'])) ?>
                            — E-mail confirmado ✉️
                        </div>
                    </div>
                    <div class="membro-acoes">
                        <button class="btn-aceitar" onclick="toggleForm('aceitar-<?= $p['id'] ?>')">
                            Aceitar
                        </button>
                    </div>
                </div>
                <div class="form-inline" id="aceitar-<?= $p['id'] ?>">
                    <form method="POST" action="<?= APP_BASE ?>/src/Controllers/gerenciar_membros.php">
                        <input type="hidden" name="membro_aceitar_id" value="<?= $p['id'] ?>">
                        <label>Categoria</label>
                        <select name="categoria_aceitar">
                            <option value="colaborador">Colaborador</option>
                            <option value="revisor">Revisor</option>
                            <option value="gestor">Gestor</option>
                        </select>
                        <label>Observação (opcional)</label>
                        <textarea name="motivacao_aceitar" placeholder="Ex: aprovado por critérios do edital XYZ..."></textarea>
                        <div class="form-inline-footer">
                            <button type="button" class="btn-cancel-sm" onclick="toggleForm('aceitar-<?= $p['id'] ?>')">Cancelar</button>
                            <button type="submit" name="aceitar_membro" value="1" class="btn-aceitar">✅ Confirmar aceite</button>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ════════════════════════════════════════ -->
    <!-- MEMBROS ATIVOS                            -->
    <!-- ════════════════════════════════════════ -->
    <div class="secao">
        <h2 class="danger">
            👤 Membros ativos
            <span style="font-size:0.85em;font-weight:400;color:#555;">(<?= count($membros_ativos) ?>)</span>
        </h2>

        <?php if (empty($membros_ativos)): ?>
            <p class="vazio">Nenhum membro ativo.</p>
        <?php else: ?>
            <?php foreach ($membros_ativos as $u): ?>
                <div class="membro-card" id="card-a-<?= $u['id'] ?>">
                    <div class="membro-info">
                        <div class="membro-nome"><?= htmlspecialchars($u['nome']) ?></div>
                        <div class="membro-email"><?= htmlspecialchars($u['email']) ?></div>
                        <div class="membro-meta">
                            <span class="tag-categoria"><?= htmlspecialchars(ucfirst($u['categoria'])) ?></span>
                            · membro desde <?= date('d/m/Y', strtotime($u['data_cadastro'])) ?>
                        </div>
                    </div>
                    <div class="membro-acoes">
                        <?php if ($u['id'] !== $gestor_id): ?>
                            <button class="btn-excluir" onclick="toggleForm('excluir-<?= $u['id'] ?>')">
                                Excluir
                            </button>
                        <?php else: ?>
                            <span style="font-size:0.78em;color:#aaa;">(você)</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($u['id'] !== $gestor_id): ?>
                <div class="form-inline" id="excluir-<?= $u['id'] ?>">
                    <form method="POST" action="<?= APP_BASE ?>/src/Controllers/gerenciar_membros.php"
                          onsubmit="return confirm('Remover <?= htmlspecialchars(addslashes($u['nome'])) ?> permanentemente?')">
                        <input type="hidden" name="membro_excluir_id" value="<?= $u['id'] ?>">
                        <label>Motivo da remoção (opcional)</label>
                        <textarea name="motivacao_excluir" placeholder="Ex: inatividade, violação de conduta..."></textarea>
                        <div class="form-inline-footer">
                            <button type="button" class="btn-cancel-sm" onclick="toggleForm('excluir-<?= $u['id'] ?>')">Cancelar</button>
                            <button type="submit" name="excluir_membro" value="1" class="btn-excluir">🗑️ Confirmar remoção</button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<script>
function toggleForm(id) {
    const el = document.getElementById(id);
    el.classList.toggle('ativo');
}
</script>
</body>
</html>
