<?php
// ================================================
// ARTIGOS — FILA POR STATUS
// Geração de artigos é automática (finalizar_upload_temporario.php).
// Esta página lista e permite revisar os artigos gerados.
// ================================================
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

$usuario_tipo   = $_SESSION['usuario_tipo']   ?? '';
$usuario_subtipo = strtolower(trim($_SESSION['usuario_subtipo'] ?? ''));
$usuario_nome   = $_SESSION['usuario_nome']   ?? '';

// Acesso: gestor, revisor ou colaborador especialista
$tem_acesso = in_array($usuario_tipo, ['gestor', 'revisor'])
           || ($usuario_tipo === 'colaborador' && in_array($usuario_subtipo, ['especialista', 'gestor']));

if (!$tem_acesso) {
    header('Location: ' . APP_BASE . '/index.php');
    exit;
}

// ================================================
// FILTRO DE STATUS
// ================================================
$filtro = $_GET['status'] ?? 'pendentes';

$contadores = $pdo->query("
    SELECT status, COUNT(*) as total
    FROM artigos
    GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

// ================================================
// BUSCAR ARTIGOS
// ================================================
if ($filtro === 'pendentes') {
    $where = "a.status IN ('rascunho','em_revisao')";
} elseif (in_array($filtro, ['rascunho', 'em_revisao', 'aprovado', 'publicado'])) {
    $where = "a.status = " . $pdo->quote($filtro);
} else {
    $where = "1=1";
}

$artigos = $pdo->query("
    SELECT
        a.id           AS artigo_id,
        a.especie_id,
        a.status,
        a.gerado_em,
        a.atualizado_em,
        e.nome_cientifico,
        e.status       AS status_especie,
        e.prioridade,
        c.nome_popular,
        c.familia,
        u.nome         AS revisado_por_nome
    FROM artigos a
    INNER JOIN especies_administrativo e ON a.especie_id = e.id
    LEFT JOIN  especies_caracteristicas c ON c.especie_id = e.id
    LEFT JOIN  usuarios u ON u.id = a.revisado_por
    WHERE {$where}
    GROUP BY a.id
    ORDER BY
        CASE a.status
            WHEN 'rascunho'    THEN 1
            WHEN 'em_revisao'  THEN 2
            WHEN 'aprovado'    THEN 3
            WHEN 'publicado'   THEN 4
        END,
        CASE e.prioridade
            WHEN 'urgente' THEN 1
            WHEN 'alta'    THEN 2
            WHEN 'media'   THEN 3
            WHEN 'baixa'   THEN 4
            ELSE 5
        END,
        a.gerado_em DESC
")->fetchAll(PDO::FETCH_ASSOC);

// ================================================
// LABELS
// ================================================
$labels_status_artigo = [
    'rascunho'   => ['label' => 'Rascunho',    'classe' => 'st-rascunho'],
    'em_revisao' => ['label' => 'Em revisão',  'classe' => 'st-revisao'],
    'aprovado'   => ['label' => 'Aprovado',    'classe' => 'st-aprovado'],
    'publicado'  => ['label' => 'Publicado',   'classe' => 'st-publicado'],
];
$labels_prio = [
    'urgente' => ['label' => 'Urgente', 'classe' => 'prio-urgente'],
    'alta'    => ['label' => 'Alta',    'classe' => 'prio-alta'],
    'media'   => ['label' => 'Média',   'classe' => 'prio-media'],
    'baixa'   => ['label' => 'Baixa',   'classe' => 'prio-baixa'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artigos — Fila de Revisão · Penomato</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha384-blOohCVdhjmtROpu8+CfTnUWham9nkX7P7OZQMst+RUnhtoY/9qemFAkIKOYxDI3" crossorigin="anonymous">
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
    <style>
        body { background: var(--cinza-100); padding: var(--esp-8) var(--esp-5); }

        .container { max-width: 960px; margin: 0 auto; }

        .page-header {
            background: var(--cor-primaria);
            color: var(--branco);
            padding: var(--esp-6) var(--esp-8);
            border-radius: var(--raio-lg);
            margin-bottom: var(--esp-6);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--esp-3);
        }
        .page-header h1 { font-size: var(--texto-xl); font-weight: var(--peso-semi); color: var(--branco); }
        .page-header p  { font-size: var(--texto-sm); opacity: 0.8; margin-top: var(--esp-1); }

        /* ── Contadores de status ── */
        .status-tabs {
            display: flex;
            gap: var(--esp-2);
            flex-wrap: wrap;
            margin-bottom: var(--esp-6);
        }
        .status-tab {
            display: flex;
            align-items: center;
            gap: var(--esp-2);
            padding: var(--esp-2) var(--esp-5);
            border-radius: var(--raio-pill);
            font-size: var(--texto-sm);
            font-weight: var(--peso-semi);
            text-decoration: none;
            border: 2px solid transparent;
            background: var(--branco);
            color: var(--cinza-700);
            box-shadow: var(--sombra-sm);
            transition: var(--transicao);
        }
        .status-tab:hover { border-color: var(--cor-primaria); color: var(--cor-primaria); text-decoration: none; }
        .status-tab.ativo { background: var(--cor-primaria); color: var(--branco); border-color: var(--cor-primaria); }
        .status-tab .num {
            background: rgba(255,255,255,0.25);
            border-radius: var(--raio-pill);
            padding: 1px var(--esp-2);
            font-size: var(--texto-xs);
            min-width: 22px;
            text-align: center;
        }
        .status-tab:not(.ativo) .num { background: var(--cinza-200); color: var(--cinza-700); }

        /* ── Tabela de artigos ── */
        .card {
            background: var(--branco);
            border-radius: var(--raio-lg);
            box-shadow: var(--sombra-md);
            overflow: hidden;
        }

        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: var(--cinza-50);
            padding: var(--esp-3) var(--esp-5);
            text-align: left;
            font-size: var(--texto-xs);
            font-weight: var(--peso-bold);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--cinza-500);
            border-bottom: 1px solid var(--cinza-200);
        }
        tbody tr { border-bottom: 1px solid var(--cinza-100); transition: background var(--transicao); }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--cinza-50); }
        td { padding: var(--esp-4) var(--esp-5); vertical-align: middle; }

        .especie-nome { font-weight: var(--peso-semi); color: var(--cinza-900); font-style: italic; font-size: var(--texto-sm); }
        .especie-sub  { font-size: var(--texto-xs); color: var(--cinza-500); margin-top: 2px; }

        /* badges */
        .badge {
            display: inline-block;
            padding: 2px var(--esp-3);
            border-radius: var(--raio-pill);
            font-size: var(--texto-xs);
            font-weight: var(--peso-semi);
            white-space: nowrap;
        }
        .st-rascunho  { background: var(--cinza-200);     color: var(--cinza-700); }
        .st-revisao   { background: #fef9c3;               color: #854d0e; }
        .st-aprovado  { background: var(--sucesso-fundo);  color: var(--sucesso-texto); }
        .st-publicado { background: #dbeafe;               color: #1e40af; }

        .prio-urgente { background: #fee2e2; color: #991b1b; }
        .prio-alta    { background: #ffedd5; color: #9a3412; }
        .prio-media   { background: #fef9c3; color: #854d0e; }
        .prio-baixa   { background: var(--cinza-100); color: var(--cinza-600); }

        .btn-revisar {
            background: var(--cor-primaria);
            color: var(--branco);
            padding: var(--esp-2) var(--esp-4);
            border-radius: var(--raio-md);
            font-size: var(--texto-xs);
            font-weight: var(--peso-semi);
            text-decoration: none;
            white-space: nowrap;
            transition: var(--transicao);
            display: inline-block;
        }
        .btn-revisar:hover { background: var(--cor-primaria-hover); color: var(--branco); text-decoration: none; }

        .data-cell { font-size: var(--texto-xs); color: var(--cinza-500); white-space: nowrap; }

        .vazia {
            text-align: center;
            padding: var(--esp-16) var(--esp-5);
            color: var(--cinza-400);
        }
        .vazia i { font-size: 2.5rem; display: block; margin-bottom: var(--esp-3); }

        @media (max-width: 700px) {
            .col-familia, .col-prio, .col-revisado { display: none; }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="page-header">
        <div>
            <h1><i class="fas fa-file-alt"></i> Artigos — Fila de Revisão</h1>
            <p>Artigos gerados automaticamente ao concluir importação de dados</p>
        </div>
        <a href="<?php echo $usuario_tipo === 'gestor' ? '/penomato_mvp/src/Views/entrada_gestor.php' : '/penomato_mvp/src/Views/entrar_colaborador.php'; ?>" class="btn btn-outline-branco">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <!-- Abas de status -->
    <div class="status-tabs">
        <?php
        $pendentes = ($contadores['rascunho'] ?? 0) + ($contadores['em_revisao'] ?? 0);
        $tabs = [
            'pendentes'  => ['label' => 'Pendentes',   'num' => $pendentes],
            'rascunho'   => ['label' => 'Rascunho',    'num' => $contadores['rascunho']   ?? 0],
            'em_revisao' => ['label' => 'Em revisão',  'num' => $contadores['em_revisao'] ?? 0],
            'aprovado'   => ['label' => 'Aprovado',    'num' => $contadores['aprovado']   ?? 0],
            'publicado'  => ['label' => 'Publicado',   'num' => $contadores['publicado']  ?? 0],
            'todos'      => ['label' => 'Todos',        'num' => array_sum($contadores)],
        ];
        foreach ($tabs as $key => $tab):
            $ativo = $filtro === $key ? ' ativo' : '';
        ?>
        <a href="?status=<?php echo $key; ?>" class="status-tab<?php echo $ativo; ?>">
            <?php echo htmlspecialchars($tab['label']); ?>
            <span class="num"><?php echo $tab['num']; ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <?php if (empty($artigos)): ?>
        <div class="vazia">
            <i class="fas fa-check-circle" style="color:var(--sucesso-cor)"></i>
            Nenhum artigo nesta categoria.
        </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Espécie</th>
                    <th class="col-familia">Família</th>
                    <th>Status</th>
                    <th class="col-prio">Prioridade</th>
                    <th class="col-revisado">Revisado por</th>
                    <th>Gerado em</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($artigos as $a):
                $st  = $labels_status_artigo[$a['status']] ?? ['label' => $a['status'], 'classe' => ''];
                $pr  = $labels_prio[$a['prioridade']] ?? ['label' => $a['prioridade'], 'classe' => 'prio-baixa'];
            ?>
            <tr>
                <td>
                    <div class="especie-nome"><?php echo htmlspecialchars($a['nome_cientifico']); ?></div>
                    <?php if ($a['nome_popular']): ?>
                    <div class="especie-sub"><?php echo htmlspecialchars($a['nome_popular']); ?></div>
                    <?php endif; ?>
                </td>
                <td class="col-familia">
                    <span class="especie-sub"><?php echo htmlspecialchars($a['familia'] ?? '—'); ?></span>
                </td>
                <td>
                    <span class="badge <?php echo $st['classe']; ?>"><?php echo $st['label']; ?></span>
                </td>
                <td class="col-prio">
                    <span class="badge <?php echo $pr['classe']; ?>"><?php echo $pr['label']; ?></span>
                </td>
                <td class="col-revisado data-cell">
                    <?php echo htmlspecialchars($a['revisado_por_nome'] ?? '—'); ?>
                </td>
                <td class="data-cell">
                    <?php echo date('d/m/Y', strtotime($a['gerado_em'])); ?>
                </td>
                <td>
                    <a href="/penomato_mvp/src/Views/artigo_revisao.php?id=<?php echo $a['especie_id']; ?>"
                       class="btn-revisar">
                        <i class="fas fa-pen"></i> Revisar
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
