<?php
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

// ================================================
// CONTRIBUIÇÕES POR COLABORADOR
// ================================================
// Conta espécies distintas em que cada usuário atuou em algum papel de autor
$stmt = $pdo->query("
    SELECT
        u.id,
        u.nome,
        u.email,
        u.categoria,
        u.subtipo_colaborador,
        u.ultimo_acesso,

        -- Por tipo de contribuição
        COUNT(DISTINCT CASE WHEN e.autor_dados_internet_id = u.id THEN e.id END) AS qtd_internet,
        COUNT(DISTINCT CASE WHEN e.autor_descrita_id        = u.id THEN e.id END) AS qtd_descrita,
        COUNT(DISTINCT CASE WHEN e.autor_registrada_id      = u.id THEN e.id END) AS qtd_registrada,
        COUNT(DISTINCT CASE WHEN e.autor_revisada_id        = u.id THEN e.id END) AS qtd_revisada,
        COUNT(DISTINCT CASE WHEN e.autor_publicado_id       = u.id THEN e.id END) AS qtd_publicada,

        -- Total de espécies distintas tocadas
        COUNT(DISTINCT CASE WHEN (
            e.autor_dados_internet_id = u.id OR
            e.autor_descrita_id       = u.id OR
            e.autor_registrada_id     = u.id OR
            e.autor_revisada_id       = u.id OR
            e.autor_publicado_id      = u.id
        ) THEN e.id END) AS total_especies,

        -- Data da contribuição mais recente
        GREATEST(
            COALESCE(MAX(CASE WHEN e.autor_dados_internet_id = u.id THEN e.data_dados_internet END), '1970-01-01'),
            COALESCE(MAX(CASE WHEN e.autor_descrita_id       = u.id THEN e.data_descrita       END), '1970-01-01'),
            COALESCE(MAX(CASE WHEN e.autor_registrada_id     = u.id THEN e.data_registrada     END), '1970-01-01'),
            COALESCE(MAX(CASE WHEN e.autor_revisada_id       = u.id THEN e.data_revisada       END), '1970-01-01'),
            COALESCE(MAX(CASE WHEN e.autor_publicado_id      = u.id THEN e.data_publicado      END), '1970-01-01')
        ) AS ultima_contrib

    FROM usuarios u
    LEFT JOIN especies_administrativo e ON (
        e.autor_dados_internet_id = u.id OR
        e.autor_descrita_id       = u.id OR
        e.autor_registrada_id     = u.id OR
        e.autor_revisada_id       = u.id OR
        e.autor_publicado_id      = u.id
    )
    WHERE u.ativo = 1 AND u.status_verificacao = 'verificado'
    GROUP BY u.id
    ORDER BY total_especies DESC, u.nome
");
$colaboradores = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_usuarios = count($colaboradores);
$total_contrib  = array_sum(array_column($colaboradores, 'total_especies'));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Colaboradores — Penomato</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f4f0;
            color: #1e2e1e;
            padding: 24px 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }

        .header {
            background: var(--cor-primaria);
            color: white;
            padding: 18px 28px;
            border-radius: 10px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header h1 { font-size: 1.3em; font-weight: 600; }
        .header-right { display: flex; align-items: center; gap: 12px; }
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
        .btn-csv {
            background: rgba(255,255,255,0.9);
            color: var(--cor-primaria);
            border: none;
            padding: 8px 18px;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.88em;
            font-weight: 600;
        }
        .btn-csv:hover { background: white; }

        /* Resumo */
        .resumo {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .card-resumo {
            background: white;
            border-radius: 10px;
            padding: 16px 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            flex: 1;
            min-width: 140px;
        }
        .card-resumo .num {
            font-size: 2em;
            font-weight: 700;
            color: var(--cor-primaria);
            line-height: 1.1;
        }
        .card-resumo .label {
            font-size: 0.82em;
            color: #888;
            margin-top: 4px;
        }

        /* Tabela */
        .table-wrap {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        table { width: 100%; border-collapse: collapse; font-size: 0.87em; }
        th {
            background: #f7f9f7;
            padding: 12px 14px;
            text-align: left;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #e0e8e0;
            white-space: nowrap;
        }
        th.num-col { text-align: center; }
        td { padding: 11px 14px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        td.num-col { text-align: center; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fafffe; }

        .nome { font-weight: 600; color: #1a3a28; }
        .sub  { font-size: 0.82em; color: #888; margin-top: 2px; }

        .badge-cat {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 9px;
            font-size: 0.78em;
            font-weight: 600;
            white-space: nowrap;
        }
        .cat-colaborador { background: #d4edda; color: #155724; }
        .cat-revisor     { background: #fff3cd; color: #856404; }
        .cat-gestor      { background: var(--cor-primaria); color: white; }
        .cat-visitante   { background: #f0f0f0; color: #666; }

        .num-destaque {
            font-weight: 700;
            font-size: 1.05em;
            color: var(--cor-primaria);
        }
        .num-zero { color: #ccc; }

        .barra-wrap { width: 80px; display: inline-block; vertical-align: middle; }
        .barra {
            height: 6px;
            background: #e0ebe0;
            border-radius: 3px;
            overflow: hidden;
        }
        .barra-fill {
            height: 100%;
            background: var(--cor-primaria);
            border-radius: 3px;
            transition: width 0.3s;
        }

        .tempo { color: #999; font-size: 0.82em; white-space: nowrap; }
        .empty { text-align: center; padding: 40px; color: #bbb; font-size: 0.95em; }

        .breakdown {
            display: flex;
            gap: 6px;
            font-size: 0.78em;
            color: #888;
            margin-top: 2px;
            flex-wrap: wrap;
        }
        .breakdown span { white-space: nowrap; }
        .breakdown .v { color: var(--cor-primaria); font-weight: 600; }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1>👥 Contribuições por Colaborador</h1>
        <div class="header-right">
            <a href="relatorio_colaboradores.php?csv=1" class="btn-csv">⬇ Exportar CSV</a>
            <a href="/penomato_mvp/src/Controllers/controlador_gestor.php" class="btn-voltar">← Voltar</a>
        </div>
    </div>

    <!-- Resumo -->
    <div class="resumo">
        <div class="card-resumo">
            <div class="num"><?php echo $total_usuarios; ?></div>
            <div class="label">Membros ativos</div>
        </div>
        <div class="card-resumo">
            <div class="num"><?php echo $total_contrib; ?></div>
            <div class="label">Contribuições totais</div>
        </div>
        <div class="card-resumo">
            <div class="num"><?php echo $total_usuarios > 0 ? round($total_contrib / $total_usuarios, 1) : 0; ?></div>
            <div class="label">Média por membro</div>
        </div>
        <div class="card-resumo">
            <div class="num"><?php echo count(array_filter($colaboradores, fn($c) => $c['total_especies'] > 0)); ?></div>
            <div class="label">Com contribuições</div>
        </div>
    </div>

    <!-- Tabela -->
    <div class="table-wrap">
        <?php if (empty($colaboradores)): ?>
            <div class="empty">Nenhum membro ativo encontrado.</div>
        <?php else: ?>
        <?php
        $max = max(array_column($colaboradores, 'total_especies')) ?: 1;
        ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Membro</th>
                    <th>Categoria</th>
                    <th class="num-col">Internet</th>
                    <th class="num-col">Descrita</th>
                    <th class="num-col">Registrada</th>
                    <th class="num-col">Revisada</th>
                    <th class="num-col">Publicada</th>
                    <th class="num-col">Total</th>
                    <th>Progresso</th>
                    <th>Última contrib.</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($colaboradores as $i => $c): ?>
                <?php
                $total = (int)$c['total_especies'];
                $pct = round($total / $max * 100);
                $ultima = $c['ultima_contrib'] && $c['ultima_contrib'] !== '1970-01-01 00:00:00'
                    ? date('d/m/Y', strtotime($c['ultima_contrib']))
                    : null;
                ?>
                <tr>
                    <td style="color:#bbb;font-size:0.82em;"><?php echo $i + 1; ?></td>
                    <td>
                        <div class="nome"><?php echo htmlspecialchars($c['nome']); ?></div>
                        <?php if ($c['subtipo_colaborador']): ?>
                            <div class="sub"><?php echo htmlspecialchars($c['subtipo_colaborador']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge-cat cat-<?php echo $c['categoria']; ?>">
                            <?php echo ucfirst($c['categoria']); ?>
                        </span>
                    </td>
                    <td class="num-col <?php echo $c['qtd_internet'] > 0 ? '' : 'num-zero'; ?>">
                        <?php echo $c['qtd_internet'] ?: '—'; ?>
                    </td>
                    <td class="num-col <?php echo $c['qtd_descrita'] > 0 ? '' : 'num-zero'; ?>">
                        <?php echo $c['qtd_descrita'] ?: '—'; ?>
                    </td>
                    <td class="num-col <?php echo $c['qtd_registrada'] > 0 ? '' : 'num-zero'; ?>">
                        <?php echo $c['qtd_registrada'] ?: '—'; ?>
                    </td>
                    <td class="num-col <?php echo $c['qtd_revisada'] > 0 ? '' : 'num-zero'; ?>">
                        <?php echo $c['qtd_revisada'] ?: '—'; ?>
                    </td>
                    <td class="num-col <?php echo $c['qtd_publicada'] > 0 ? '' : 'num-zero'; ?>">
                        <?php echo $c['qtd_publicada'] ?: '—'; ?>
                    </td>
                    <td class="num-col">
                        <span class="<?php echo $total > 0 ? 'num-destaque' : 'num-zero'; ?>">
                            <?php echo $total ?: '0'; ?>
                        </span>
                    </td>
                    <td>
                        <div class="barra-wrap">
                            <div class="barra">
                                <div class="barra-fill" style="width:<?php echo $pct; ?>%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="tempo">
                        <?php echo $ultima ?? '<span style="color:#ddd">—</span>'; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>
<?php
// ================================================
// EXPORTAR CSV
// ================================================
if (isset($_GET['csv'])) {
    // Rebuffer como CSV
    ob_end_clean();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="colaboradores_' . date('Y-m-d') . '.csv"');
    echo "\xEF\xBB\xBF"; // BOM para Excel reconhecer UTF-8

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Nome', 'Email', 'Categoria', 'Subtipo', 'Internet', 'Descrita', 'Registrada', 'Revisada', 'Publicada', 'Total', 'Última contribuição'], ';');
    foreach ($colaboradores as $c) {
        $ultima = $c['ultima_contrib'] && $c['ultima_contrib'] !== '1970-01-01 00:00:00'
            ? date('d/m/Y', strtotime($c['ultima_contrib'])) : '';
        fputcsv($out, [
            $c['nome'], $c['email'], $c['categoria'], $c['subtipo_colaborador'] ?? '',
            $c['qtd_internet'], $c['qtd_descrita'], $c['qtd_registrada'],
            $c['qtd_revisada'], $c['qtd_publicada'], $c['total_especies'], $ultima
        ], ';');
    }
    fclose($out);
    exit;
}
?>
</body>
</html>
