<?php
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';
require_once __DIR__ . '/../../config/email.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

$gestor_id = $_SESSION['usuario_id'];

$labels_tipo = [
    'dados_internet' => 'Dados da Internet',
    'confirmacao'    => 'Confirmação',
    'imagem'         => 'Imagem',
    'revisao'        => 'Revisão',
    'contestacao'    => 'Contestação',
];

// ================================================
// PROCESSAR DECISÃO (POST)
// ================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao       = $_POST['acao']       ?? '';
    $item_id    = (int)($_POST['item_id'] ?? 0);
    $motivo     = trim($_POST['motivo'] ?? '');

    if ($item_id && in_array($acao, ['aprovar', 'rejeitar'])) {
        $status = $acao === 'aprovar' ? 'aprovado' : 'rejeitado';

        // Buscar dados da ação e do solicitante ANTES de atualizar
        $stmt_item = $pdo->prepare("
            SELECT f.tipo, f.subtipo, f.especie_id, f.usuario_id, f.observacoes,
                   u.email AS usuario_email, u.nome AS usuario_nome,
                   e.nome_cientifico
            FROM fila_aprovacao f
            JOIN usuarios u ON u.id = f.usuario_id
            JOIN especies_administrativo e ON e.id = f.especie_id
            WHERE f.id = ? AND f.status = 'pendente'
        ");
        $stmt_item->execute([$item_id]);
        $row = $stmt_item->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            header('Location: aprovacao_acoes.php');
            exit;
        }

        $pdo->prepare("
            UPDATE fila_aprovacao
            SET status = ?, motivo_rejeicao = ?, data_decisao = NOW(), gestor_id = ?
            WHERE id = ?
        ")->execute([$status, $motivo ?: null, $gestor_id, $item_id]);

        $tipo_label = $labels_tipo[$row['tipo']] ?? $row['tipo'];

        if ($status === 'aprovado') {

            // ── Solicitação de DESFAZER (prazo expirado) ──────────────────
            if ($row['subtipo'] === 'desfazer') {
                $extras  = json_decode($row['observacoes'] ?? '{}', true);
                $hist_id = (int)($extras['hist_id'] ?? 0);

                if ($hist_id) {
                    $stmt_h = $pdo->prepare("
                        SELECT * FROM historico_alteracoes
                        WHERE id = ? AND especie_id = ? AND revertida = 0
                    ");
                    $stmt_h->execute([$hist_id, $row['especie_id']]);
                    $hist = $stmt_h->fetch(PDO::FETCH_ASSOC);

                    if ($hist) {
                        $especie_id = (int)$hist['especie_id'];
                        $h_extras   = $hist['dados_extras'] ? json_decode($hist['dados_extras'], true) : [];

                        if ($hist['tabela_afetada'] === 'especies_imagens' && isset($h_extras['imagem_id'])) {
                            $img_stmt = $pdo->prepare("SELECT caminho_imagem FROM especies_imagens WHERE id = ? AND especie_id = ?");
                            $img_stmt->execute([$h_extras['imagem_id'], $especie_id]);
                            $img = $img_stmt->fetch();
                            if ($img) {
                                $arq = __DIR__ . '/../../../' . $img['caminho_imagem'];
                                if (file_exists($arq)) unlink($arq);
                                $pdo->prepare("DELETE FROM especies_imagens WHERE id = ?")->execute([$h_extras['imagem_id']]);
                            }
                            $pdo->prepare("UPDATE especies_administrativo SET data_ultima_atualizacao = NOW() WHERE id = ?")->execute([$especie_id]);

                        } elseif ($hist['tabela_afetada'] === 'especies_caracteristicas' && $hist['tipo_acao'] === 'insercao') {
                            $pdo->prepare("DELETE FROM especies_caracteristicas WHERE especie_id = ?")->execute([$especie_id]);
                            $pdo->prepare("DELETE FROM artigos WHERE especie_id = ?")->execute([$especie_id]);
                            $si = $pdo->prepare("SELECT caminho_imagem FROM especies_imagens WHERE especie_id = ? AND origem = 'internet'");
                            $si->execute([$especie_id]);
                            foreach ($si->fetchAll(PDO::FETCH_COLUMN) as $caminho) {
                                $arq = __DIR__ . '/../../../' . $caminho;
                                if (file_exists($arq)) unlink($arq);
                            }
                            $pdo->prepare("DELETE FROM especies_imagens WHERE especie_id = ? AND origem = 'internet'")->execute([$especie_id]);
                            $pdo->prepare("UPDATE especies_administrativo SET status = 'sem_dados', data_ultima_atualizacao = NOW() WHERE id = ?")->execute([$especie_id]);

                        } elseif ($hist['campo_alterado'] === 'status' && $hist['valor_novo'] === 'descrita') {
                            $pdo->prepare("UPDATE especies_administrativo SET status = 'dados_internet', data_ultima_atualizacao = NOW() WHERE id = ?")->execute([$especie_id]);

                        } elseif ($hist['campo_alterado'] === 'status' && $hist['valor_novo'] === 'publicado' && $hist['tipo_acao'] === 'revisao') {
                            $pdo->prepare("UPDATE especies_administrativo SET status = 'em_revisao', data_ultima_atualizacao = NOW() WHERE id = ?")->execute([$especie_id]);
                            $pdo->prepare("UPDATE artigos SET status = 'rascunho', atualizado_em = NOW() WHERE especie_id = ?")->execute([$especie_id]);

                        } elseif ($hist['campo_alterado'] === 'status' && $hist['valor_novo'] === 'contestado') {
                            $pdo->prepare("UPDATE especies_administrativo SET status = 'em_revisao', data_ultima_atualizacao = NOW() WHERE id = ?")->execute([$especie_id]);
                        }

                        $pdo->prepare("UPDATE historico_alteracoes SET revertida = 1 WHERE id = ?")->execute([$hist_id]);
                    }
                }

                $tipo_label = 'Desfazer ação';

            } else {
                // ── Outros tipos: avança status conforme tipo ──────────────
                $novo_status = match($row['tipo']) {
                    'dados_internet' => 'dados_internet',
                    'confirmacao'    => 'identificado',
                    'imagem'         => 'registrada',
                    'revisao'        => 'revisada',
                    default          => null,
                };
                if ($novo_status) {
                    $pdo->prepare("UPDATE especies_administrativo SET status = ? WHERE id = ?")
                        ->execute([$novo_status, $row['especie_id']]);
                }
            }

            $conteudo_email = "
                <p>Olá, <strong>" . htmlspecialchars($row['usuario_nome']) . "</strong>!</p>
                <p>Sua solicitação de <strong>" . htmlspecialchars($tipo_label) . "</strong>
                para a espécie <em>" . htmlspecialchars($row['nome_cientifico']) . "</em>
                foi <strong style='color:#0b5e42;'>APROVADA</strong> pelo gestor.</p>"
                . ($motivo ? "<p><strong>Observações:</strong> " . htmlspecialchars($motivo) . "</p>" : "")
                . "<p>Acesse a plataforma para acompanhar o resultado.</p>";
            enviarEmail(
                $row['usuario_email'],
                'Solicitação aprovada — Penomato',
                templateEmail('Sua solicitação foi aprovada', $conteudo_email)
            );

        } else {
            $conteudo_email = "
                <p>Olá, <strong>" . htmlspecialchars($row['usuario_nome']) . "</strong>!</p>
                <p>Sua solicitação de <strong>" . htmlspecialchars($row['subtipo'] === 'desfazer' ? 'Desfazer ação' : $tipo_label) . "</strong>
                para a espécie <em>" . htmlspecialchars($row['nome_cientifico']) . "</em>
                foi <strong style='color:#dc3545;'>REJEITADA</strong> pelo gestor.</p>"
                . ($motivo ? "<p><strong>Motivo:</strong> " . htmlspecialchars($motivo) . "</p>" : "")
                . "<p>Em caso de dúvidas, entre em contato com o gestor.</p>";
            enviarEmail(
                $row['usuario_email'],
                'Solicitação rejeitada — Penomato',
                templateEmail('Solicitação não aprovada', $conteudo_email)
            );
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
    SELECT f.id, f.tipo, f.subtipo, f.descricao, f.observacoes,
           f.status, f.motivo_rejeicao,
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
    <link rel="stylesheet" href="<?= APP_BASE ?>/assets/css/estilo.css">
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
            background: var(--cor-primaria);
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
        .chip:hover { border-color: var(--cor-primaria); }
        .chip.ativo { border-color: var(--cor-primaria); background: #e8f5e9; color: var(--cor-primaria); font-weight: 700; }
        .chip .n { font-weight: 700; color: var(--cor-primaria); }
        .chip.ativo .n { color: var(--cor-primaria); }
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
        .card.aprovado  { border-left-color: var(--cor-primaria); }
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
        .input-motivo:focus { outline: none; border-color: var(--cor-primaria); }
        .btn-aprovar {
            background: var(--cor-primaria); color: white; border: none;
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
                                    <?php if ($item['subtipo'] === 'desfazer'): ?>
                                        <span class="badge" style="background:#fef3c7;color:#92400e;">↩ Desfazer ação</span>
                                    <?php else: ?>
                                        <span class="badge badge-<?php echo $item['tipo']; ?>">
                                            <?php echo $labels_tipo[$item['tipo']] ?? $item['tipo']; ?>
                                        </span>
                                    <?php endif; ?>
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
                    <?php
                    if ($item['subtipo'] === 'desfazer' && $item['observacoes']) {
                        $obs = json_decode($item['observacoes'], true);
                        if (!empty($obs['justificativa'])):
                    ?>
                        <div class="descricao" style="margin-top:6px;border-left:3px solid #d97706;">
                            <strong>Justificativa:</strong> <?php echo htmlspecialchars($obs['justificativa']); ?>
                        </div>
                    <?php endif; } ?>

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
