<?php
// ================================================
// MINHAS AÇÕES RECENTES — desfazer ou solicitar
// ================================================
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';
require_once __DIR__ . '/../../config/email.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

$usuario_id = (int)$_SESSION['usuario_id'];

// ── Verificação lazy: notificar colaborador quando janela de correção expirou ──
// (revisor desfez aprovação há +1 dia e espécie ainda está em em_revisao)
try {
    $stmt_exp = $pdo->query("
        SELECT h.especie_id, h.id, e.nome_cientifico,
               u_colab.email AS email_colab, u_colab.nome AS nome_colab
        FROM historico_alteracoes h
        JOIN especies_administrativo e ON e.id = h.especie_id
        JOIN usuarios u_colab ON u_colab.id = e.autor_dados_internet_id
        WHERE h.tipo_acao = 'revisao'
          AND h.campo_alterado = 'status'
          AND h.valor_novo = 'publicado'
          AND h.revertida = 1
          AND h.notificacao_enviada = 0
          AND h.data_alteracao < NOW() - INTERVAL 1 DAY
          AND e.status = 'em_revisao'
    ");
    foreach ($stmt_exp->fetchAll(PDO::FETCH_ASSOC) as $exp) {
        if (!empty($exp['email_colab'])) {
            $corpo = "<p>Olá, <strong>" . htmlspecialchars($exp['nome_colab']) . "</strong>!</p>
                <p>A espécie <em>" . htmlspecialchars($exp['nome_cientifico']) . "</em>
                voltou para revisão e aguarda sua atenção.</p>
                <p>Acesse o Penomato para verificar as informações.</p>";
            enviarEmail($exp['email_colab'], 'Espécie aguardando revisão — Penomato',
                templateEmail('Espécie aguardando revisão', $corpo));
        }
        $pdo->prepare("UPDATE historico_alteracoes SET notificacao_enviada = 1 WHERE id = ?")
            ->execute([$exp['id']]);
    }
} catch (Exception $e) { /* silencioso — colunas podem não existir ainda */ }

// ── Mensagem de retorno ────────────────────────────────────────────────────────
$msg_ok   = $_GET['ok']   ?? '';
$msg_erro = $_GET['erro'] ?? '';

// ── Buscar ações do usuário (não revertidas, últimos 7 dias) ──────────────────
$stmt = $pdo->prepare("
    SELECT h.id, h.especie_id, h.tabela_afetada, h.campo_alterado,
           h.valor_anterior, h.valor_novo, h.tipo_acao, h.dados_extras,
           h.data_alteracao, h.revertida,
           e.nome_cientifico
    FROM historico_alteracoes h
    JOIN especies_administrativo e ON e.id = h.especie_id
    WHERE h.id_usuario = ?
      AND h.data_alteracao > NOW() - INTERVAL 7 DAY
    ORDER BY h.data_alteracao DESC
    LIMIT 50
");
$stmt->execute([$usuario_id]);
$acoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Labels humanos ────────────────────────────────────────────────────────────
function descricaoAcao(array $h): string {
    $t  = $h['tipo_acao'];
    $ca = $h['campo_alterado'] ?? '';
    $vn = $h['valor_novo'] ?? '';
    $tb = $h['tabela_afetada'] ?? '';

    if ($tb === 'especies_imagens')         return 'Upload de imagem (' . ($h['valor_novo'] ?? '') . ')';
    if ($tb === 'especies_caracteristicas') return 'Inserção de dados da internet';
    if ($ca === 'status' && $vn === 'descrita')   return 'Confirmação dos dados';
    if ($ca === 'status' && $vn === 'publicado')  return 'Aprovação do artigo';
    if ($ca === 'status' && $vn === 'contestado') return 'Contestação do artigo';
    if ($ca === 'status')                         return 'Alteração de status → ' . $vn;
    return ucfirst($t);
}

function prazoInfo(string $data_alteracao): array {
    $feito_em   = strtotime($data_alteracao);
    $expira_em  = $feito_em + 86400; // +1 dia
    $agora      = time();
    $restante   = $expira_em - $agora;
    $dentro     = $restante > 0;

    if ($dentro) {
        $h = floor($restante / 3600);
        $m = floor(($restante % 3600) / 60);
        $texto = $h > 0 ? "{$h}h {$m}min restantes" : "{$m}min restantes";
    } else {
        $venceu_ha = $agora - $expira_em;
        $h = floor($venceu_ha / 3600);
        $texto = $h > 0 ? "Prazo vencido há {$h}h" : 'Prazo vencido';
    }
    return ['dentro' => $dentro, 'texto' => $texto];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Ações — Penomato</title>
    <link rel="stylesheet" href="<?= APP_BASE ?>/assets/css/estilo.css">
    <style>
        body { background: #f0f4f0; padding: 24px 20px; }
        .container { max-width: 860px; margin: 0 auto; }

        .pg-header {
            background: var(--cor-primaria);
            color: white;
            padding: 18px 28px;
            border-radius: 10px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .pg-header h1 { font-size: 1.15rem; font-weight: 700; }
        .pg-header p  { font-size: .82rem; opacity: .8; margin-top: 3px; }
        .btn-voltar {
            background: rgba(255,255,255,.18);
            color: white;
            text-decoration: none;
            padding: 7px 18px;
            border-radius: 30px;
            font-size: .85rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .btn-voltar:hover { background: rgba(255,255,255,.3); }

        /* Alertas */
        .alerta { padding: 12px 18px; border-radius: 8px; margin-bottom: 20px; font-size: .9rem; font-weight: 600; }
        .alerta-ok   { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alerta-erro { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        /* Card de ação */
        .acao-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 18px 22px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }
        .acao-card.revertida {
            opacity: .55;
            background: #f8fafc;
        }
        .acao-icone {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .ic-insercao   { background: #dbeafe; }
        .ic-edicao     { background: #fef3c7; }
        .ic-revisao    { background: #d1fae5; }
        .ic-contestacao{ background: #fee2e2; }

        .acao-info { flex: 1; min-width: 0; }
        .acao-titulo {
            font-weight: 700;
            font-size: .95rem;
            color: #1e293b;
            margin-bottom: 3px;
        }
        .acao-especie {
            font-size: .82rem;
            font-style: italic;
            color: var(--cor-primaria);
            margin-bottom: 3px;
        }
        .acao-data { font-size: .78rem; color: #94a3b8; }

        .prazo-chip {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: .78rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .prazo-ok     { background: #d1fae5; color: #065f46; }
        .prazo-vencido{ background: #fee2e2; color: #991b1b; }
        .prazo-feito  { background: #f1f5f9; color: #64748b; }

        /* Botões */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 8px;
            font-size: .85rem;
            font-weight: 700;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: .15s;
            white-space: nowrap;
        }
        .btn-desfazer  { background: #b45309; color: #fff; }
        .btn-desfazer:hover { background: #92400e; }
        .btn-solicitar { background: #334155; color: #fff; }
        .btn-solicitar:hover { background: #1e293b; }

        .sem-acoes {
            text-align: center;
            padding: 48px 20px;
            color: #94a3b8;
            background: white;
            border-radius: 10px;
            font-size: .95rem;
        }

        /* Modal de solicitação */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.aberto { display: flex; }
        .modal-box {
            background: white;
            border-radius: 12px;
            padding: 28px 32px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 8px 32px rgba(0,0,0,.2);
        }
        .modal-titulo { font-size: 1rem; font-weight: 700; margin-bottom: 6px; }
        .modal-sub { font-size: .82rem; color: #64748b; margin-bottom: 16px; }
        .modal-box textarea {
            width: 100%;
            min-height: 90px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: .88rem;
            resize: vertical;
            margin-bottom: 16px;
        }
        .modal-acoes { display: flex; gap: 10px; justify-content: flex-end; }
        .btn-cancelar { background: #f1f5f9; color: #475569; }
        .btn-cancelar:hover { background: #e2e8f0; }
        .btn-enviar { background: #334155; color: #fff; }
        .btn-enviar:hover { background: #1e293b; }

        .legenda {
            font-size: .78rem;
            color: #94a3b8;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="pg-header">
        <div>
            <h1>↩ Minhas Ações Recentes</h1>
            <p>Você pode desfazer ações realizadas nas últimas 24 horas.</p>
        </div>
        <a href="<?= APP_BASE ?>/src/Controllers/controlador_gestor.php" class="btn-voltar">← Voltar</a>
    </div>

    <?php if ($msg_ok): ?>
        <div class="alerta alerta-ok">✅ <?= htmlspecialchars($msg_ok) ?></div>
    <?php endif; ?>
    <?php if ($msg_erro): ?>
        <div class="alerta alerta-erro">⚠️ <?= htmlspecialchars($msg_erro) ?></div>
    <?php endif; ?>

    <?php if (!$acoes): ?>
        <div class="sem-acoes">
            <div style="font-size:2rem;margin-bottom:12px;">📋</div>
            Nenhuma ação registrada nos últimos 7 dias.
        </div>
    <?php else: ?>

    <p class="legenda">Exibindo até 50 ações dos últimos 7 dias. Ações já desfeitas aparecem esmaecidas.</p>

    <?php
    $icones = ['insercao'=>'📥','edicao'=>'✏️','revisao'=>'✅','contestacao'=>'❌'];
    foreach ($acoes as $h):
        $descricao = descricaoAcao($h);
        $prazo     = prazoInfo($h['data_alteracao']);
        $tempo_ago = human_time_diff($h['data_alteracao']);
    ?>
    <div class="acao-card <?= $h['revertida'] ? 'revertida' : '' ?>">

        <div class="acao-icone ic-<?= $h['tipo_acao'] ?>">
            <?= $icones[$h['tipo_acao']] ?? '🔧' ?>
        </div>

        <div class="acao-info">
            <div class="acao-titulo"><?= htmlspecialchars($descricao) ?></div>
            <div class="acao-especie"><?= htmlspecialchars($h['nome_cientifico']) ?></div>
            <div class="acao-data"><?= $tempo_ago ?></div>
        </div>

        <?php if ($h['revertida']): ?>
            <span class="prazo-chip prazo-feito">✓ Desfeita</span>

        <?php elseif ($prazo['dentro']): ?>
            <span class="prazo-chip prazo-ok">⏱ <?= $prazo['texto'] ?></span>
            <form method="POST" action="desfazer_acao.php"
                  onsubmit="return confirm('Desfazer: <?= htmlspecialchars(addslashes($descricao)) ?>?');">
                <input type="hidden" name="acao" value="desfazer">
                <input type="hidden" name="hist_id" value="<?= $h['id'] ?>">
                <button type="submit" class="btn btn-desfazer">↩ Desfazer</button>
            </form>

        <?php else: ?>
            <span class="prazo-chip prazo-vencido">⌛ <?= $prazo['texto'] ?></span>
            <button class="btn btn-solicitar"
                    onclick="abrirModal(<?= $h['id'] ?>, '<?= htmlspecialchars(addslashes($descricao)) ?>')">
                📩 Solicitar ao gestor
            </button>
        <?php endif; ?>

    </div>
    <?php endforeach; ?>
    <?php endif; ?>

</div>

<!-- Modal de solicitação ao gestor -->
<div class="modal-overlay" id="modal">
    <div class="modal-box">
        <div class="modal-titulo">Solicitar desfazer ao gestor</div>
        <div class="modal-sub" id="modal-descricao"></div>
        <form method="POST" action="desfazer_acao.php">
            <input type="hidden" name="acao" value="solicitar">
            <input type="hidden" name="hist_id" id="modal-hist-id" value="">
            <textarea name="justificativa" placeholder="Explique o motivo da solicitação…" required></textarea>
            <div class="modal-acoes">
                <button type="button" class="btn btn-cancelar" onclick="fecharModal()">Cancelar</button>
                <button type="submit" class="btn btn-enviar">📩 Enviar solicitação</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModal(histId, descricao) {
    document.getElementById('modal-hist-id').value = histId;
    document.getElementById('modal-descricao').textContent = 'Ação: ' + descricao;
    document.getElementById('modal').classList.add('aberto');
    document.body.style.overflow = 'hidden';
}
function fecharModal() {
    document.getElementById('modal').classList.remove('aberto');
    document.body.style.overflow = '';
}
document.getElementById('modal').addEventListener('click', function(e) {
    if (e.target === this) fecharModal();
});
</script>
</body>
</html>
<?php
function human_time_diff(string $data): string {
    $diff = time() - strtotime($data);
    if ($diff < 60)   return 'Agora mesmo';
    if ($diff < 3600) return floor($diff/60) . 'min atrás';
    if ($diff < 86400) return floor($diff/3600) . 'h atrás';
    return floor($diff/86400) . 'd atrás';
}
