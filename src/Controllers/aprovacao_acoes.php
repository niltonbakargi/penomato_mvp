<?php
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /penomato_mvp/src/Views/auth/login.php');
    exit;
}

$gestor_id = $_SESSION['usuario_id'];

// ================================================
// PROCESSAR DECISÃO (POST)
// ================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao       = $_POST['acao']       ?? '';
    $item_id    = (int)($_POST['item_id'] ?? 0);
    $motivo     = trim($_POST['motivo'] ?? '');

    if ($item_id && in_array($acao, ['aprovar', 'rejeitar'])) {
        $status = $acao === 'aprovar' ? 'aprovado' : 'rejeitado';

        $stmt = $pdo->prepare("
            UPDATE fila_aprovacao
            SET status = ?, motivo_rejeicao = ?, data_decisao = NOW(), gestor_id = ?
            WHERE id = ? AND status = 'pendente'
        ");
        $stmt->execute([$status, $motivo ?: null, $gestor_id, $item_id]);

        // Se aprovado, avança o status da espécie conforme o tipo da ação
        if ($status === 'aprovado') {
            $item = $pdo->prepare("SELECT tipo, especie_id FROM fila_aprovacao WHERE id = ?");
            $item->execute([$item_id]);
            $row = $item->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $novo_status = match($row['tipo']) {
                    'dados_internet' => 'dados_internet',
                    'confirmacao'    => 'identificado',
                    'imagem'         => 'registrada',
                    'revisao'        => 'revisada',
                    'contestacao'    => 'contestado',
                    default          => null,
                };
                if ($novo_status) {
                    $pdo->prepare("UPDATE especies_administrativo SET status = ? WHERE id = ?")
                        ->execute([$novo_status, $row['especie_id']]);
                }
            }
        }
    }

    header('Location: aprovacao_acoes.php');
    exit;
}

// ================================================
// FILTRO
// ================================================
$filtro = $_GET['status'] ?? 'pendente';
$filtros_validos = ['pendente', 'aprovado', 'rejeitado'];
if (!in_array($filtro, $filtros_validos)) $filtro = 'pendente';

// ================================================
// BUSCAR ITENS
// ================================================
$stmt = $pdo->prepare("
    SELECT f.id, f.tipo, f.descricao, f.status, f.motivo_rejeicao,
           f.data_submissao, f.data_decisao,
           u.nome  AS colaborador_nome,
           u.categoria AS colaborador_categoria,
           e.nome_cientifico,
           e.status AS especie_status,
           g.nome  AS gestor_nome
    FROM fila_aprovacao f
    JOIN usuarios u ON u.id = f.usuario_id
    JOIN especies_administrativo e ON e.id = f.especie_id
    LEFT JOIN usuarios g ON g.id = f.gestor_id
    WHERE f.status = ?
    ORDER BY f.data_submissao DESC
");
$stmt->execute([$filtro]);
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contagens por status
$contagens = $pdo->query("
    SELECT status, COUNT(*) FROM fila_aprovacao GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

$labels_tipo = [
    'dados_internet' => 'Dados da Internet',
    'confirmacao'    => 'Confirmação',
    'imagem'         => 'Imagem',
    'revisao'        => 'Revisão',
    'contestacao'    => 'Contestação',
];
$labels_status_esp = [
    'sem_dados'      => 'Sem dados',
    'dados_internet' => 'Dados internet',
    'identificado'   => 'Identificado',
    'registrada'     => 'Registrada',
    'em_revisao'     => 'Em revisão',
    'revisada'       => 'Revisada',
    'contestado'     => 'Contestado',
    'publicado'      => 'Publicado',
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprovação de Ações — Penomato</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f4f0;
            color: #1e2e1e;
            padding: 24px 20px;
        }
        .container { max-width: 1000px; margin: 0 auto; }

        .header {
            background: #0b5e42;
            color: white;
            padding: 18px 28px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header h1 { font-size: 1.3em; font-weight: 600; }
        .btn-voltar {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 8px 18px;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.88em;
        }
        .btn-voltar:hover { background: rgba(255,255,255,0.35); }

        /* Filtros */
        .filtros {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .chip {
            background: white;
            border: 2px solid transparent;
            border-radius: 20px;
            padding: 7px 18px;
            font-size: 0.85em;
            cursor: pointer;
            text-decoration: none;
            color: #555;
            box-shadow: 0 1px 3px rgba(0,0,0,0.07);
            transition: all 0.15s;
        }
        .chip:hover { border-color: #0b5e42; }
        .chip.ativo { border-color: #0b5e42; background: #e8f5e9; color: #0b5e42; font-weight: 700; }
        .chip .n { font-weight: 700; color: #0b5e42; }
        .chip.ativo .n { color: #0b5e42; }
        .chip .n-warn { color: #d97706; font-weight: 700; }

        /* Cards */
        .lista { display: flex; flex-direction: column; gap: 14px; }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            overflow: hidden;
            border-left: 5px solid #ccc;
        }
        .card.pendente  { border-left-color: #d97706; }
        .card.aprovado  { border-left-color: #0b5e42; }
        .card.rejeitado { border-left-color: #dc3545; }

        .card-body { padding: 16px 20px; }

        .card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .card-info { flex: 1; }

        .especie { font-style: italic; font-weight: 600; color: #1a3a28; font-size: 1em; }
        .meta {
            display: flex;
            gap: 10px;
            margin-top: 5px;
            flex-wrap: wrap;
            font-size: 0.82em;
            color: #888;
        }
        .meta span { white-space: nowrap; }

        .badge {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 9px;
            font-size: 0.78em;
            font-weight: 600;
            white-space: nowrap;
        }
        .badge-dados_internet { background:#cfe2ff; color:#084298; }
        .badge-confirmacao    { background:#d1ecf1; color:#0c5460; }
        .badge-imagem         { background:#e2d9f3; color:#6f42c1; }
        .badge-revisao        { background:#fff3cd; color:#856404; }
        .badge-contestacao    { background:#f8d7da; color:#721c24; }

        .descricao {
            margin-top: 10px;
            font-size: 0.88em;
            color: #555;
            background: #f8f9f8;
            padding: 8px 12px;
            border-radius: 6px;
            line-height: 1.5;
        }

        .motivo-rej {
            margin-top: 8px;
            font-size: 0.85em;
            color: #721c24;
            background: #fff5f5;
            padding: 6px 12px;
            border-radius: 6px;
        }

        /* Formulário de decisão */
        .card-actions {
            display: flex;
            gap: 8px;
            align-items: flex-start;
            flex-wrap: wrap;
            margin-top: 12px;
        }
        .input-motivo {
            flex: 1;
            min-width: 180px;
            padding: 7px 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.85em;
            font-family: inherit;
        }
        .input-motivo:focus { outline: none; border-color: #0b5e42; }
        .btn-aprovar {
            background: #0b5e42; color: white; border: none;
            border-radius: 6px; padding: 8px 18px;
            font-size: 0.85em; font-weight: 600; cursor: pointer;
            white-space: nowrap;
        }
        .btn-aprovar:hover { background: #094d36; }
        .btn-rejeitar {
            background: white; color: #dc3545;
            border: 1px solid #dc3545;
            border-radius: 6px; padding: 8px 18px;
            font-size: 0.85em; font-weight: 600; cursor: pointer;
            white-space: nowrap;
        }
        .btn-rejeitar:hover { background: #dc3545; color: white; }

        .decisao-info {
            font-size: 0.82em;
            color: #999;
            margin-top: 10px;
        }
        .decisao-info strong { color: #555; }

        .empty {
            background: white;
            border-radius: 10px;
            padding: 50px;
            text-align: center;
            color: #bbb;
            font-size: 0.95em;
        }
        .empty .icon { font-size: 2.5em; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1>✅ Aprovação de Ações</h1>
        <a href="/penomato_mvp/src/Controllers/controlador_gestor.php" class="btn-voltar">← Voltar ao painel</a>
    </div>

    <!-- Filtros -->
    <div class="filtros">
        <a href="aprovacao_acoes.php?status=pendente"
           class="chip <?php echo $filtro === 'pendente' ? 'ativo' : ''; ?>">
            Pendentes <span class="n-warn"><?php echo $contagens['pendente'] ?? 0; ?></span>
        </a>
        <a href="aprovacao_acoes.php?status=aprovado"
           class="chip <?php echo $filtro === 'aprovado' ? 'ativo' : ''; ?>">
            Aprovados <span class="n"><?php echo $contagens['aprovado'] ?? 0; ?></span>
        </a>
        <a href="aprovacao_acoes.php?status=rejeitado"
           class="chip <?php echo $filtro === 'rejeitado' ? 'ativo' : ''; ?>">
            Rejeitados <span class="n"><?php echo $contagens['rejeitado'] ?? 0; ?></span>
        </a>
    </div>

    <!-- Lista -->
    <div class="lista">
        <?php if (empty($itens)): ?>
            <div class="empty">
                <div class="icon"><?php echo $filtro === 'pendente' ? '🎉' : '📭'; ?></div>
                <?php echo $filtro === 'pendente'
                    ? 'Nenhuma ação pendente de aprovação.'
                    : 'Nenhum item encontrado.'; ?>
            </div>
        <?php else: ?>
            <?php foreach ($itens as $item): ?>
            <div class="card <?php echo $item['status']; ?>">
                <div class="card-body">
                    <div class="card-top">
                        <div class="card-info">
                            <div class="especie"><?php echo htmlspecialchars($item['nome_cientifico']); ?></div>
                            <div class="meta">
                                <span>
                                    <span class="badge badge-<?php echo $item['tipo']; ?>">
                                        <?php echo $labels_tipo[$item['tipo']] ?? $item['tipo']; ?>
                                    </span>
                                </span>
                                <span>👤 <?php echo htmlspecialchars($item['colaborador_nome']); ?> (<?php echo $item['colaborador_categoria']; ?>)</span>
                                <span>🕐 <?php echo date('d/m/Y H:i', strtotime($item['data_submissao'])); ?></span>
                                <?php if ($item['especie_status']): ?>
                                <span>Status espécie: <strong><?php echo $labels_status_esp[$item['especie_status']] ?? $item['especie_status']; ?></strong></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($item['descricao']): ?>
                        <div class="descricao"><?php echo htmlspecialchars($item['descricao']); ?></div>
                    <?php endif; ?>

                    <?php if ($item['status'] === 'rejeitado' && $item['motivo_rejeicao']): ?>
                        <div class="motivo-rej">⚠️ Motivo: <?php echo htmlspecialchars($item['motivo_rejeicao']); ?></div>
                    <?php endif; ?>

                    <?php if ($item['status'] === 'pendente'): ?>
                    <!-- Botões de decisão -->
                    <div class="card-actions">
                        <form method="POST" action="aprovacao_acoes.php" style="display:contents;">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <input type="text" name="motivo" class="input-motivo" placeholder="Motivo (opcional para aprovação, recomendado para rejeição)">
                            <button type="submit" name="acao" value="aprovar" class="btn-aprovar">✅ Aprovar</button>
                            <button type="submit" name="acao" value="rejeitar" class="btn-rejeitar"
                                    onclick="return this.form.motivo.value.trim() !== '' || confirm('Rejeitar sem motivo?')">
                                ✖ Rejeitar
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <div class="decisao-info">
                        <?php echo $item['status'] === 'aprovado' ? '✅' : '✖'; ?>
                        <strong><?php echo $item['status'] === 'aprovado' ? 'Aprovado' : 'Rejeitado'; ?></strong>
                        por <?php echo htmlspecialchars($item['gestor_nome'] ?? '—'); ?>
                        em <?php echo $item['data_decisao'] ? date('d/m/Y H:i', strtotime($item['data_decisao'])) : '—'; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
