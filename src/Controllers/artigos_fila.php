<?php
// ================================================
// ARTIGOS — FILA POR STATUS
// ================================================
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

$usuario_id      = (int)$_SESSION['usuario_id'];
$usuario_tipo    = $_SESSION['usuario_tipo']    ?? '';
$usuario_subtipo = strtolower(trim($_SESSION['usuario_subtipo'] ?? ''));
$usuario_nome    = $_SESSION['usuario_nome']    ?? '';

// Acesso: gestor ou colaborador especialista
$is_gestor    = $usuario_tipo === 'gestor';
$is_especialista = $usuario_tipo === 'colaborador'
               && in_array($usuario_subtipo, ['especialista', 'gestor']);

if (!$is_gestor && !$is_especialista) {
    header('Location: ' . APP_BASE . '/index.php');
    exit;
}

// ================================================
// FILTRO DE STATUS
// ================================================
$filtro         = $_GET['status'] ?? 'pendentes';
$status_validos = ['rascunho', 'confirmado', 'registrado', 'revisando', 'revisado', 'publicado'];

// ================================================
// CONTADORES (filtrados por revisor_id para especialista)
// ================================================
try {
    if ($is_gestor) {
        $contadores = $pdo->query("
            SELECT status, COUNT(*) as total FROM artigos GROUP BY status
        ")->fetchAll(PDO::FETCH_KEY_PAIR);
    } else {
        $stmt = $pdo->prepare("
            SELECT status, COUNT(*) as total FROM artigos
            WHERE revisor_id = ? GROUP BY status
        ");
        $stmt->execute([$usuario_id]);
        $contadores = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
} catch (Exception $e) {
    $contadores = [];
}

// ================================================
// BUSCAR ARTIGOS
// ================================================
try {
    $params = [];

    if ($filtro === 'pendentes') {
        if ($is_gestor) {
            $where = "a.status IN ('registrado','revisando','revisado')";
        } else {
            $where  = "a.status IN ('registrado','revisando') AND a.revisor_id = ?";
            $params = [$usuario_id];
        }
    } elseif (in_array($filtro, $status_validos)) {
        if ($is_gestor) {
            $where  = "a.status = ?";
            $params = [$filtro];
        } else {
            $where  = "a.status = ? AND a.revisor_id = ?";
            $params = [$filtro, $usuario_id];
        }
    } else {
        // todos
        if ($is_gestor) {
            $where = "1=1";
        } else {
            $where  = "a.revisor_id = ?";
            $params = [$usuario_id];
        }
    }

    $stmt = $pdo->prepare("
        SELECT
            a.id           AS artigo_id,
            a.especie_id,
            a.status,
            a.gerado_em,
            a.atualizado_em,
            e.nome_cientifico,
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
                WHEN 'registrado' THEN 1
                WHEN 'revisando'  THEN 2
                WHEN 'revisado'   THEN 3
                WHEN 'confirmado' THEN 4
                WHEN 'rascunho'   THEN 5
                WHEN 'publicado'  THEN 6
            END,
            CASE e.prioridade
                WHEN 'urgente' THEN 1
                WHEN 'alta'    THEN 2
                WHEN 'media'   THEN 3
                WHEN 'baixa'   THEN 4
                ELSE 5
            END,
            a.gerado_em DESC
    ");
    $stmt->execute($params);
    $artigos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $artigos = [];
}

// ================================================
// LABELS E DESCRIÇÕES DE STATUS
// ================================================
$info_status = [
    'rascunho'   => [
        'label'   => 'Rascunho',
        'classe'  => 'st-rascunho',
        'descricao' => 'Dados inseridos, aguardando confirmação',
    ],
    'confirmado' => [
        'label'   => 'Confirmado',
        'classe'  => 'st-confirmado',
        'descricao' => 'Dados confirmados, aguardando imagens de campo',
    ],
    'registrado' => [
        'label'   => 'Registrado',
        'classe'  => 'st-registrado',
        'descricao' => 'Imagens completas — pronto para revisão científica',
    ],
    'revisando'  => [
        'label'   => 'Em revisão',
        'classe'  => 'st-revisando',
        'descricao' => 'Revisão em andamento pelo especialista',
    ],
    'revisado'   => [
        'label'   => 'Revisado',
        'classe'  => 'st-revisado',
        'descricao' => 'Aprovado pelo especialista, aguardando publicação',
    ],
    'publicado'  => [
        'label'   => 'Publicado',
        'classe'  => 'st-publicado',
        'descricao' => 'Artigo disponível ao público',
    ],
];

// ================================================
// ABAS (gestor vê tudo; especialista vê só os seus)
// ================================================
$pendentes_num = ($contadores['registrado'] ?? 0) + ($contadores['revisando'] ?? 0)
              + ($is_gestor ? ($contadores['revisado'] ?? 0) : 0);

if ($is_gestor) {
    $tabs = [
        'pendentes'  => ['label' => 'Pendentes',   'num' => $pendentes_num],
        'registrado' => ['label' => 'Registrado',  'num' => $contadores['registrado'] ?? 0],
        'revisando'  => ['label' => 'Em revisão',  'num' => $contadores['revisando']  ?? 0],
        'revisado'   => ['label' => 'Revisado',    'num' => $contadores['revisado']   ?? 0],
        'rascunho'   => ['label' => 'Rascunho',    'num' => $contadores['rascunho']   ?? 0],
        'confirmado' => ['label' => 'Confirmado',  'num' => $contadores['confirmado'] ?? 0],
        'publicado'  => ['label' => 'Publicado',   'num' => $contadores['publicado']  ?? 0],
        'todos'      => ['label' => 'Todos',        'num' => array_sum($contadores)],
    ];
} else {
    $tabs = [
        'pendentes'  => ['label' => 'Pendentes',   'num' => $pendentes_num],
        'registrado' => ['label' => 'Registrado',  'num' => $contadores['registrado'] ?? 0],
        'revisando'  => ['label' => 'Em revisão',  'num' => $contadores['revisando']  ?? 0],
        'revisado'   => ['label' => 'Revisado',    'num' => $contadores['revisado']   ?? 0],
        'publicado'  => ['label' => 'Publicado',   'num' => $contadores['publicado']  ?? 0],
    ];
}

$voltar_url = $is_gestor
    ? '/penomato_mvp/src/Controllers/controlador_gestor.php'
    : '/penomato_mvp/src/Views/entrar_colaborador.php';
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

        /* ── Abas ── */
        .status-tabs { display: flex; gap: var(--esp-2); flex-wrap: wrap; margin-bottom: var(--esp-6); }
        .status-tab {
            display: flex; align-items: center; gap: var(--esp-2);
            padding: var(--esp-2) var(--esp-5);
            border-radius: var(--raio-pill);
            font-size: var(--texto-sm); font-weight: var(--peso-semi);
            text-decoration: none;
            border: 2px solid transparent;
            background: var(--branco); color: var(--cinza-700);
            box-shadow: var(--sombra-sm); transition: var(--transicao);
        }
        .status-tab:hover { border-color: var(--cor-primaria); color: var(--cor-primaria); text-decoration: none; }
        .status-tab.ativo { background: var(--cor-primaria); color: var(--branco); border-color: var(--cor-primaria); }
        .status-tab .num {
            background: rgba(255,255,255,0.25); border-radius: var(--raio-pill);
            padding: 1px var(--esp-2); font-size: var(--texto-xs);
            min-width: 22px; text-align: center;
        }
        .status-tab:not(.ativo) .num { background: var(--cinza-200); color: var(--cinza-700); }

        /* ── Tabela ── */
        .card { background: var(--branco); border-radius: var(--raio-lg); box-shadow: var(--sombra-md); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: var(--cinza-50); padding: var(--esp-3) var(--esp-5);
            text-align: left; font-size: var(--texto-xs); font-weight: var(--peso-bold);
            text-transform: uppercase; letter-spacing: 0.05em;
            color: var(--cinza-500); border-bottom: 1px solid var(--cinza-200);
        }
        tbody tr { border-bottom: 1px solid var(--cinza-100); transition: background var(--transicao); }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--cinza-50); }
        td { padding: var(--esp-4) var(--esp-5); vertical-align: middle; }

        .especie-nome { font-weight: var(--peso-semi); color: var(--cinza-900); font-style: italic; font-size: var(--texto-sm); }
        .especie-sub  { font-size: var(--texto-xs); color: var(--cinza-500); margin-top: 2px; }

        /* badges */
        .badge {
            display: inline-block; padding: 2px var(--esp-3);
            border-radius: var(--raio-pill); font-size: var(--texto-xs);
            font-weight: var(--peso-semi); white-space: nowrap;
        }
        .st-rascunho   { background: var(--cinza-200);     color: var(--cinza-700); }
        .st-confirmado { background: #e0f2fe;              color: #0369a1; }
        .st-registrado { background: #fef9c3;              color: #854d0e; }
        .st-revisando  { background: #ffedd5;              color: #9a3412; }
        .st-revisado   { background: var(--sucesso-fundo); color: var(--sucesso-texto); }
        .st-publicado  { background: #dbeafe;              color: #1e40af; }

        .status-descricao { font-size: var(--texto-xs); color: var(--cinza-400); margin-top: 3px; }

        /* botões de ação */
        .btns-acao { display: flex; gap: var(--esp-2); flex-wrap: nowrap; }
        .btn-acao {
            padding: var(--esp-2) var(--esp-4); border-radius: var(--raio-md);
            font-size: var(--texto-xs); font-weight: var(--peso-semi);
            text-decoration: none; white-space: nowrap;
            transition: var(--transicao); display: inline-block;
            border: none; cursor: pointer; font-family: inherit;
        }
        .btn-ver      { background: var(--cinza-100); color: var(--cinza-700); border: 1px solid var(--cinza-300); }
        .btn-ver:hover { background: var(--cinza-200); color: var(--cinza-800); text-decoration: none; }
        .btn-revisar  { background: var(--cor-primaria); color: var(--branco); }
        .btn-revisar:hover { background: var(--cor-primaria-hover); color: var(--branco); text-decoration: none; }
        .btn-publicar { background: #1e40af; color: var(--branco); }
        .btn-publicar:hover { background: #1e3a8a; color: var(--branco); text-decoration: none; }
        .btn-voltar {
            background: rgba(255,255,255,0.15); color: var(--branco);
            border: 1px solid rgba(255,255,255,0.5); border-radius: var(--raio-md);
            padding: var(--esp-2) var(--esp-5); font-size: var(--texto-sm);
            font-weight: var(--peso-semi); text-decoration: none; display: inline-flex;
            align-items: center; gap: var(--esp-2); transition: var(--transicao);
        }
        .btn-voltar:hover { background: rgba(255,255,255,0.30); text-decoration: none; }

        .data-cell { font-size: var(--texto-xs); color: var(--cinza-500); white-space: nowrap; }

        .vazia { text-align: center; padding: var(--esp-16) var(--esp-5); color: var(--cinza-400); }
        .vazia i { font-size: 2.5rem; display: block; margin-bottom: var(--esp-3); }

        @media (max-width: 700px) {
            .col-gestor { display: none; }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="page-header">
        <div>
            <h1><i class="fas fa-file-alt"></i> Artigos — Fila de Revisão</h1>
            <p>
                <?php if ($is_gestor): ?>
                    Visão geral de todos os artigos do sistema
                <?php else: ?>
                    Artigos atribuídos a você para revisão científica
                <?php endif; ?>
            </p>
        </div>
        <a href="<?php echo $voltar_url; ?>" class="btn-voltar">
            <i class="fas fa-arrow-left"></i> Voltar ao painel
        </a>
    </div>

    <!-- Abas de status -->
    <div class="status-tabs">
        <?php foreach ($tabs as $key => $tab):
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
                    <?php if ($is_gestor): ?>
                    <th class="col-gestor">Família</th>
                    <?php endif; ?>
                    <th>Status</th>
                    <?php if ($is_gestor): ?>
                    <th class="col-gestor">Revisado por</th>
                    <?php endif; ?>
                    <th>Data</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($artigos as $a):
                $st = $info_status[$a['status']] ?? ['label' => $a['status'], 'classe' => '', 'descricao' => ''];
            ?>
            <tr>
                <td>
                    <div class="especie-nome"><?php echo htmlspecialchars($a['nome_cientifico']); ?></div>
                    <?php if ($a['nome_popular']): ?>
                    <div class="especie-sub"><?php echo htmlspecialchars($a['nome_popular']); ?></div>
                    <?php endif; ?>
                </td>
                <?php if ($is_gestor): ?>
                <td class="col-gestor especie-sub">
                    <?php echo htmlspecialchars($a['familia'] ?? '—'); ?>
                </td>
                <?php endif; ?>
                <td>
                    <span class="badge <?php echo $st['classe']; ?>"><?php echo $st['label']; ?></span>
                    <div class="status-descricao"><?php echo $st['descricao']; ?></div>
                </td>
                <?php if ($is_gestor): ?>
                <td class="col-gestor data-cell">
                    <?php echo htmlspecialchars($a['revisado_por_nome'] ?? '—'); ?>
                </td>
                <?php endif; ?>
                <td class="data-cell">
                    <?php echo date('d/m/Y', strtotime($a['gerado_em'])); ?>
                </td>
                <td>
                    <div class="btns-acao">
                        <a href="/penomato_mvp/src/Views/artigo_revisao.php?id=<?php echo $a['especie_id']; ?>&modo=ver"
                           class="btn-acao btn-ver">
                            <i class="fas fa-eye"></i> Ver
                        </a>

                        <?php if ($a['status'] === 'registrado'): ?>
                        <a href="/penomato_mvp/src/Views/artigo_revisao.php?id=<?php echo $a['especie_id']; ?>&modo=revisar"
                           class="btn-acao btn-revisar">
                            <i class="fas fa-pen"></i> Iniciar revisão
                        </a>

                        <?php elseif ($a['status'] === 'revisando'): ?>
                        <a href="/penomato_mvp/src/Views/artigo_revisao.php?id=<?php echo $a['especie_id']; ?>&modo=revisar"
                           class="btn-acao btn-revisar">
                            <i class="fas fa-pen"></i> Continuar revisão
                        </a>

                        <?php elseif ($a['status'] === 'revisado' && $is_gestor): ?>
                        <form method="POST" action="/penomato_mvp/src/Controllers/controlador_painel_revisor.php"
                              style="display:inline;"
                              onsubmit="return confirm('Publicar o artigo de <?php echo htmlspecialchars($a['nome_cientifico'], ENT_QUOTES); ?>?')">
                            <input type="hidden" name="acao"       value="publicar">
                            <input type="hidden" name="especie_id" value="<?php echo $a['especie_id']; ?>">
                            <button type="submit" class="btn-acao btn-publicar">
                                <i class="fas fa-globe"></i> Publicar
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
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
