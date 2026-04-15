<?php
// ============================================================
// FLORA DO CERRADO — consulta pública REFLORA/JBRJ
// ============================================================

$titulo_pagina    = 'Flora do Cerrado — Penomato';
$descricao_pagina = 'Explore mais de 12.000 espécies nativas do Cerrado com dados taxonômicos oficiais da base REFLORA — Jardim Botânico do Rio de Janeiro';

require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../../Controllers/auth/verificar_acesso.php';

// ── Filtros via GET ────────────────────────────────────────────
$filtro_busca    = trim($_GET['busca']      ?? '');
$filtro_grupo    = trim($_GET['grupo']      ?? '');
$filtro_familia  = trim($_GET['familia']    ?? '');
$filtro_forma    = trim($_GET['forma_vida'] ?? '');
$filtro_origem   = trim($_GET['origem']     ?? '');
$filtro_endemica = trim($_GET['endemica']   ?? '');
$filtro_uf       = trim($_GET['uf']         ?? '');
$pagina          = max(1, (int)($_GET['pagina'] ?? 1));
$por_pagina      = 25;

// ── Construção do WHERE ────────────────────────────────────────
$where_parts = [];
$params      = [];

if ($filtro_grupo)    { $where_parts[] = 'grupo = ?';          $params[] = $filtro_grupo; }
if ($filtro_familia)  { $where_parts[] = 'familia = ?';         $params[] = $filtro_familia; }
if ($filtro_forma)    { $where_parts[] = 'formas_vida LIKE ?';  $params[] = "%{$filtro_forma}%"; }
if ($filtro_origem)   { $where_parts[] = 'origem = ?';          $params[] = $filtro_origem; }
if ($filtro_endemica) { $where_parts[] = 'endemica LIKE ?';     $params[] = "%{$filtro_endemica}%"; }
if ($filtro_uf)       { $where_parts[] = 'distr_uf LIKE ?';     $params[] = "% {$filtro_uf}%" ; }
if ($filtro_busca)    {
    $where_parts[] = '(nome_cientifico LIKE ? OR nomes_vernaculares LIKE ?)';
    $params[]      = "%{$filtro_busca}%";
    $params[]      = "%{$filtro_busca}%";
}

$where_sql = $where_parts ? ('WHERE ' . implode(' AND ', $where_parts)) : '';

// ── Cards de estatísticas ──────────────────────────────────────
$stmt = $pdo->prepare("SELECT COUNT(*) FROM flora_brasil_plantas $where_sql");
$stmt->execute($params);
$total_registros = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(DISTINCT familia) FROM flora_brasil_plantas $where_sql");
$stmt->execute($params);
$total_familias = (int)$stmt->fetchColumn();

$params_end   = array_merge($params, ['é endêmica do Brasil']);
$where_end    = $where_parts
    ? 'WHERE ' . implode(' AND ', $where_parts) . " AND endemica = ?"
    : "WHERE endemica = ?";
$stmt = $pdo->prepare("SELECT COUNT(*) FROM flora_brasil_plantas $where_end");
$stmt->execute($params_end);
$total_endemicas = (int)$stmt->fetchColumn();

// ── Dados para gráficos ────────────────────────────────────────
// Top 10 famílias
$stmt = $pdo->prepare(
    "SELECT familia, COUNT(*) AS total FROM flora_brasil_plantas $where_sql
     GROUP BY familia ORDER BY total DESC LIMIT 10"
);
$stmt->execute($params);
$top_familias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Formas de vida — parsing em PHP
$where_formas_parts   = array_merge($where_parts, ['formas_vida IS NOT NULL']);
$where_formas_sql     = 'WHERE ' . implode(' AND ', $where_formas_parts);
$params_formas        = array_merge($params);
$stmt = $pdo->prepare("SELECT formas_vida FROM flora_brasil_plantas $where_formas_sql");
$stmt->execute($params_formas);
$contagem_formas = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    foreach (preg_split('/[;,]/', $row['formas_vida']) as $f) {
        $f = trim($f);
        if ($f) $contagem_formas[$f] = ($contagem_formas[$f] ?? 0) + 1;
    }
}
arsort($contagem_formas);

// ── Paginação ──────────────────────────────────────────────────
$total_paginas = max(1, (int)ceil($total_registros / $por_pagina));
$pagina        = min($pagina, $total_paginas);
$offset        = ($pagina - 1) * $por_pagina;

$stmt = $pdo->prepare(
    "SELECT * FROM flora_brasil_plantas $where_sql
     ORDER BY nome_cientifico
     LIMIT {$por_pagina} OFFSET {$offset}"
);
$stmt->execute($params);
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Listas para filtros ────────────────────────────────────────
$stmt          = $pdo->query("SELECT DISTINCT familia FROM flora_brasil_plantas ORDER BY familia");
$lista_familias = $stmt->fetchAll(PDO::FETCH_COLUMN);

$ufs_cerrado = ['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT',
                'PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'];

// ── Helper para manter filtros na URL de paginação ─────────────
function queryString(array $extra = []) {
    $p = array_filter(array_merge([
        'busca'    => $_GET['busca']      ?? '',
        'grupo'    => $_GET['grupo']      ?? '',
        'familia'  => $_GET['familia']    ?? '',
        'forma_vida'=> $_GET['forma_vida']?? '',
        'origem'   => $_GET['origem']     ?? '',
        'endemica' => $_GET['endemica']   ?? '',
        'uf'       => $_GET['uf']         ?? '',
    ], $extra));
    return $p ? '?' . http_build_query($p) : '';
}

include __DIR__ . '/../includes/cabecalho.php';
?>

<style>
    .flora-hero {
        background: linear-gradient(135deg, var(--cor-primaria) 0%, var(--verde-600) 100%);
        color: var(--branco);
        padding: var(--esp-10) var(--esp-6) var(--esp-8);
        text-align: center;
        margin-bottom: var(--esp-8);
    }
    .flora-hero h1 { font-size: 2.2rem; font-weight: 700; margin-bottom: var(--esp-2); }
    .flora-hero p  { opacity: .85; font-size: 1.05rem; max-width: 680px; margin: 0 auto; }
    .flora-hero .badge-fonte {
        display: inline-block;
        margin-top: var(--esp-4);
        background: rgba(255,255,255,.15);
        border-radius: var(--raio-pill);
        padding: var(--esp-1) var(--esp-4);
        font-size: .85rem;
    }

    /* ── Cards estatísticos ── */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: var(--esp-4);
        margin-bottom: var(--esp-8);
    }
    .stat-card {
        background: var(--branco);
        border-radius: var(--raio-lg);
        padding: var(--esp-5) var(--esp-4);
        text-align: center;
        box-shadow: var(--sombra-sm);
        border-top: 4px solid var(--cor-primaria);
    }
    .stat-card.verde-claro { border-top-color: var(--verde-600); }
    .stat-card.amarelo     { border-top-color: var(--aviso-cor); }
    .stat-card.azul        { border-top-color: var(--info-cor); }
    .stat-numero {
        font-size: 2rem;
        font-weight: 700;
        color: var(--cor-primaria);
        line-height: 1;
    }
    .stat-rotulo {
        font-size: .8rem;
        color: var(--cinza-500);
        margin-top: var(--esp-1);
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    /* ── Filtros ── */
    .filtros-card {
        background: var(--branco);
        border-radius: var(--raio-lg);
        padding: var(--esp-6);
        box-shadow: var(--sombra-sm);
        margin-bottom: var(--esp-6);
    }
    .filtros-card h2 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--cinza-700);
        margin-bottom: var(--esp-4);
    }
    .filtros-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: var(--esp-3);
        align-items: end;
    }
    .filtros-grid label { font-size: .82rem; color: var(--cinza-600); margin-bottom: var(--esp-1); display: block; }
    .filtros-grid select,
    .filtros-grid input[type="text"] {
        width: 100%;
        padding: var(--esp-2) var(--esp-3);
        border: 1px solid var(--cinza-200);
        border-radius: var(--raio-md);
        font-size: .9rem;
        background: var(--cinza-50);
        color: var(--cinza-800);
    }
    .filtros-grid select:focus,
    .filtros-grid input:focus { outline: none; border-color: var(--cor-primaria); background: var(--branco); }
    .btn-filtrar {
        background: var(--cor-primaria);
        color: var(--branco);
        border: none;
        padding: var(--esp-2) var(--esp-6);
        border-radius: var(--raio-pill);
        font-weight: var(--peso-semi);
        cursor: pointer;
        transition: background var(--transicao);
        height: 38px;
    }
    .btn-filtrar:hover { background: var(--cor-primaria-hover); }
    .btn-limpar-filtro {
        background: var(--cinza-200);
        color: var(--cinza-700);
        border: none;
        padding: var(--esp-2) var(--esp-4);
        border-radius: var(--raio-pill);
        font-size: .85rem;
        cursor: pointer;
        transition: background var(--transicao);
        height: 38px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }
    .btn-limpar-filtro:hover { background: var(--cinza-300); color: var(--cinza-800); }

    /* ── Gráficos ── */
    .charts-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--esp-6);
        margin-bottom: var(--esp-8);
    }
    @media (max-width: 768px) { .charts-grid { grid-template-columns: 1fr; } }
    .chart-card {
        background: var(--branco);
        border-radius: var(--raio-lg);
        padding: var(--esp-5);
        box-shadow: var(--sombra-sm);
    }
    .chart-card h3 {
        font-size: .95rem;
        font-weight: 600;
        color: var(--cinza-700);
        margin-bottom: var(--esp-4);
    }

    /* ── Tabela ── */
    .tabela-card {
        background: var(--branco);
        border-radius: var(--raio-lg);
        box-shadow: var(--sombra-sm);
        overflow: hidden;
        margin-bottom: var(--esp-8);
    }
    .tabela-header {
        padding: var(--esp-4) var(--esp-6);
        border-bottom: 1px solid var(--cinza-200);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: var(--esp-2);
    }
    .tabela-header h2 { font-size: 1rem; font-weight: 600; color: var(--cinza-700); margin: 0; }
    .tabela-header .total-badge {
        background: var(--verde-50);
        color: var(--cor-primaria);
        border-radius: var(--raio-pill);
        padding: var(--esp-1) var(--esp-3);
        font-size: .82rem;
        font-weight: var(--peso-semi);
    }
    table.flora-table { width: 100%; border-collapse: collapse; font-size: .88rem; }
    table.flora-table thead th {
        background: var(--cinza-50);
        padding: var(--esp-3) var(--esp-4);
        text-align: left;
        font-weight: 600;
        color: var(--cinza-600);
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        border-bottom: 1px solid var(--cinza-200);
    }
    table.flora-table tbody tr { border-bottom: 1px solid var(--cinza-100); transition: background var(--transicao); }
    table.flora-table tbody tr:hover { background: var(--verde-50); }
    table.flora-table td { padding: var(--esp-3) var(--esp-4); vertical-align: middle; color: var(--cinza-700); }
    table.flora-table td .nome-cientifico { font-style: italic; font-weight: 600; color: var(--cinza-900); }
    table.flora-table td .nome-popular { font-size: .78rem; color: var(--cinza-500); margin-top: 2px; }

    .badge-endemica {
        font-size: .72rem;
        padding: 2px 8px;
        border-radius: var(--raio-pill);
    }
    .badge-sim   { background: var(--sucesso-fundo); color: var(--sucesso-texto); }
    .badge-nao   { background: var(--cinza-100);     color: var(--cinza-500); }
    .badge-desc  { background: var(--aviso-fundo);   color: var(--aviso-texto); }

    .tag-forma {
        display: inline-block;
        font-size: .72rem;
        background: var(--cinza-100);
        color: var(--cinza-600);
        border-radius: var(--raio-pill);
        padding: 1px 7px;
        margin: 1px 2px 1px 0;
    }

    .btn-penomato {
        font-size: .75rem;
        padding: 3px 10px;
        border-radius: var(--raio-pill);
        border: 1px solid var(--cor-primaria);
        color: var(--cor-primaria);
        text-decoration: none;
        white-space: nowrap;
        transition: all var(--transicao);
    }
    .btn-penomato:hover {
        background: var(--cor-primaria);
        color: var(--branco);
    }

    /* ── Paginação ── */
    .paginacao {
        padding: var(--esp-4) var(--esp-6);
        border-top: 1px solid var(--cinza-100);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: var(--esp-3);
    }
    .paginacao-info { font-size: .83rem; color: var(--cinza-500); }
    .paginacao-btns { display: flex; gap: var(--esp-1); flex-wrap: wrap; }
    .paginacao-btns a, .paginacao-btns span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        border-radius: var(--raio-md);
        border: 1px solid var(--cinza-200);
        text-decoration: none;
        font-size: .85rem;
        color: var(--cinza-700);
        padding: 0 var(--esp-2);
        transition: all var(--transicao);
    }
    .paginacao-btns a:hover { border-color: var(--cor-primaria); color: var(--cor-primaria); background: var(--verde-50); }
    .paginacao-btns span.atual { background: var(--cor-primaria); color: var(--branco); border-color: var(--cor-primaria); font-weight: 600; }
    .paginacao-btns span.desabilitado { opacity: .4; cursor: default; }

    /* ── Sem resultados ── */
    .sem-resultados {
        text-align: center;
        padding: var(--esp-14) var(--esp-6);
        color: var(--cinza-400);
    }
    .sem-resultados i { font-size: 3rem; margin-bottom: var(--esp-4); display: block; }

    @media (max-width: 600px) {
        .flora-hero h1 { font-size: 1.6rem; }
        .tabela-header { flex-direction: column; align-items: flex-start; }
        table.flora-table td:nth-child(3),
        table.flora-table th:nth-child(3),
        table.flora-table td:nth-child(5),
        table.flora-table th:nth-child(5) { display: none; }
    }
</style>

<!-- Hero -->
<div class="flora-hero">
    <h1><i class="fas fa-seedling me-2"></i>Flora do Cerrado</h1>
    <p>Explore espécies de angiospermas e gimnospermas nativas com dados taxonômicos oficiais</p>
    <span class="badge-fonte">
        <i class="fas fa-database me-1"></i>
        Base REFLORA — Flora e Funga do Brasil 2020 &middot; JBRJ &middot; CC-BY
    </span>
</div>

<div class="container-fluid px-4">

    <!-- Cards estatísticos -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-numero"><?php echo number_format($total_registros, 0, ',', '.'); ?></div>
            <div class="stat-rotulo">Espécies encontradas</div>
        </div>
        <div class="stat-card verde-claro">
            <div class="stat-numero"><?php echo number_format($total_familias, 0, ',', '.'); ?></div>
            <div class="stat-rotulo">Famílias botânicas</div>
        </div>
        <div class="stat-card amarelo">
            <div class="stat-numero"><?php echo number_format($total_endemicas, 0, ',', '.'); ?></div>
            <div class="stat-rotulo">Endêmicas do Brasil</div>
        </div>
        <div class="stat-card azul">
            <div class="stat-numero">
                <?php
                $perc = $total_registros > 0 ? round(($total_endemicas / $total_registros) * 100) : 0;
                echo $perc . '%';
                ?>
            </div>
            <div class="stat-rotulo">Taxa de endemismo</div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filtros-card">
        <h2><i class="fas fa-sliders-h me-2"></i>Filtros</h2>
        <form method="GET" action="">
            <div class="filtros-grid">

                <div>
                    <label>Busca por nome</label>
                    <input type="text" name="busca"
                           placeholder="Nome científico ou popular..."
                           value="<?php echo htmlspecialchars($filtro_busca); ?>">
                </div>

                <div>
                    <label>Grupo</label>
                    <select name="grupo">
                        <option value="">Todos</option>
                        <option value="Angiospermas" <?php echo $filtro_grupo === 'Angiospermas' ? 'selected' : ''; ?>>Angiospermas</option>
                        <option value="Gimnospermas" <?php echo $filtro_grupo === 'Gimnospermas' ? 'selected' : ''; ?>>Gimnospermas</option>
                    </select>
                </div>

                <div>
                    <label>Família</label>
                    <select name="familia">
                        <option value="">Todas</option>
                        <?php foreach ($lista_familias as $fam): ?>
                            <option value="<?php echo htmlspecialchars($fam); ?>"
                                <?php echo $filtro_familia === $fam ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($fam); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Forma de vida</label>
                    <select name="forma_vida">
                        <option value="">Todas</option>
                        <?php foreach (['Árvore','Arbusto','Subarbusto','Erva','Liana/volúvel/trepadeira','Palmeira','Hemiepífita','Epífita','Parasita'] as $f): ?>
                            <option value="<?php echo htmlspecialchars($f); ?>"
                                <?php echo $filtro_forma === $f ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($f); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Origem</label>
                    <select name="origem">
                        <option value="">Todas</option>
                        <option value="Nativa"      <?php echo $filtro_origem === 'Nativa'      ? 'selected' : ''; ?>>Nativa</option>
                        <option value="Naturalizada"<?php echo $filtro_origem === 'Naturalizada'? 'selected' : ''; ?>>Naturalizada</option>
                        <option value="Cultivada"   <?php echo $filtro_origem === 'Cultivada'   ? 'selected' : ''; ?>>Cultivada</option>
                    </select>
                </div>

                <div>
                    <label>Endemismo</label>
                    <select name="endemica">
                        <option value="">Todos</option>
                        <option value="é endêmica"     <?php echo $filtro_endemica === 'é endêmica'     ? 'selected' : ''; ?>>Endêmica do Brasil</option>
                        <option value="não é endêmica" <?php echo $filtro_endemica === 'não é endêmica' ? 'selected' : ''; ?>>Não endêmica</option>
                    </select>
                </div>

                <div>
                    <label>Estado (UF)</label>
                    <select name="uf">
                        <option value="">Todos</option>
                        <?php foreach ($ufs_cerrado as $uf): ?>
                            <option value="<?php echo $uf; ?>" <?php echo $filtro_uf === $uf ? 'selected' : ''; ?>>
                                <?php echo $uf; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display:flex; gap: var(--esp-2); align-items: flex-end;">
                    <button type="submit" class="btn-filtrar">
                        <i class="fas fa-search me-1"></i> Filtrar
                    </button>
                    <a href="flora_cerrado.php" class="btn-limpar-filtro">
                        <i class="fas fa-times me-1"></i> Limpar
                    </a>
                </div>

            </div>
        </form>
    </div>

    <!-- Gráficos -->
    <?php if ($total_registros > 0): ?>
    <div class="charts-grid">

        <div class="chart-card">
            <h3><i class="fas fa-chart-bar me-2"></i>Top 10 Famílias</h3>
            <canvas id="graficoFamilias" height="260"></canvas>
        </div>

        <div class="chart-card">
            <h3><i class="fas fa-chart-pie me-2"></i>Formas de Vida</h3>
            <canvas id="graficoFormas" height="260"></canvas>
        </div>

    </div>
    <?php endif; ?>

    <!-- Tabela de resultados -->
    <div class="tabela-card">
        <div class="tabela-header">
            <h2><i class="fas fa-list me-2"></i>Espécies</h2>
            <?php if ($total_registros > 0): ?>
                <span class="total-badge">
                    <?php echo number_format($total_registros, 0, ',', '.'); ?> resultado(s)
                    — página <?php echo $pagina; ?> de <?php echo $total_paginas; ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if (empty($registros)): ?>
            <div class="sem-resultados">
                <i class="fas fa-leaf"></i>
                <p>Nenhuma espécie encontrada com os filtros selecionados.</p>
                <a href="flora_cerrado.php" class="btn-limpar-filtro">Limpar filtros</a>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="flora-table">
                    <thead>
                        <tr>
                            <th>Nome científico / Popular</th>
                            <th>Família</th>
                            <th>Formas de vida</th>
                            <th>Endêmica</th>
                            <th>Estados</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($registros as $r): ?>
                        <?php
                        $endemica_val = $r['endemica'] ?? '';
                        if (str_contains($endemica_val, 'é endêmica')) {
                            $badge_class = 'badge-sim'; $badge_txt = 'Sim';
                        } elseif (str_contains($endemica_val, 'não')) {
                            $badge_class = 'badge-nao'; $badge_txt = 'Não';
                        } else {
                            $badge_class = 'badge-desc'; $badge_txt = '?';
                        }

                        $formas = $r['formas_vida'] ? preg_split('/[;,]/', $r['formas_vida']) : [];
                        $nome_busca = urlencode($r['nome_cientifico']);
                        ?>
                        <tr>
                            <td>
                                <div class="nome-cientifico">
                                    <?php echo htmlspecialchars($r['nome_cientifico']); ?>
                                    <small style="font-style:normal; font-weight:400; color:var(--cinza-500);">
                                        <?php echo htmlspecialchars($r['autor'] ?? ''); ?>
                                    </small>
                                </div>
                                <?php if ($r['nomes_vernaculares']): ?>
                                    <div class="nome-popular">
                                        <?php
                                        $vernaculares = explode(';', $r['nomes_vernaculares']);
                                        echo htmlspecialchars(trim($vernaculares[0]));
                                        if (count($vernaculares) > 1) echo ' <em>…</em>';
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($r['familia']); ?></td>
                            <td>
                                <?php foreach ($formas as $f): ?>
                                    <span class="tag-forma"><?php echo htmlspecialchars(trim($f)); ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <span class="badge-endemica <?php echo $badge_class; ?>">
                                    <?php echo $badge_txt; ?>
                                </span>
                            </td>
                            <td style="font-size:.78rem; color:var(--cinza-500); max-width:140px;">
                                <?php echo htmlspecialchars($r['distr_uf'] ?? ''); ?>
                            </td>
                            <td>
                                <a href="/penomato_mvp/src/Views/publico/busca_caracteristicas.php?nome_cientifico_completo=<?php echo $nome_busca; ?>"
                                   class="btn-penomato" title="Buscar exemplares no Penomato">
                                    <i class="fas fa-leaf me-1"></i>Penomato
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <?php if ($total_paginas > 1): ?>
            <div class="paginacao">
                <span class="paginacao-info">
                    Exibindo <?php echo ($offset + 1); ?>–<?php echo min($offset + $por_pagina, $total_registros); ?>
                    de <?php echo number_format($total_registros, 0, ',', '.'); ?> espécies
                </span>
                <div class="paginacao-btns">
                    <?php if ($pagina > 1): ?>
                        <a href="<?php echo queryString(['pagina' => 1]); ?>" title="Primeira">«</a>
                        <a href="<?php echo queryString(['pagina' => $pagina - 1]); ?>">‹</a>
                    <?php else: ?>
                        <span class="desabilitado">«</span>
                        <span class="desabilitado">‹</span>
                    <?php endif; ?>

                    <?php
                    $inicio = max(1, $pagina - 2);
                    $fim    = min($total_paginas, $pagina + 2);
                    for ($p = $inicio; $p <= $fim; $p++):
                    ?>
                        <?php if ($p === $pagina): ?>
                            <span class="atual"><?php echo $p; ?></span>
                        <?php else: ?>
                            <a href="<?php echo queryString(['pagina' => $p]); ?>"><?php echo $p; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($pagina < $total_paginas): ?>
                        <a href="<?php echo queryString(['pagina' => $pagina + 1]); ?>">›</a>
                        <a href="<?php echo queryString(['pagina' => $total_paginas]); ?>" title="Última">»</a>
                    <?php else: ?>
                        <span class="desabilitado">›</span>
                        <span class="desabilitado">»</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>

    <!-- Créditos da fonte -->
    <div style="text-align:center; padding: var(--esp-4) 0 var(--esp-10); font-size:.8rem; color:var(--cinza-400);">
        Dados provenientes da base
        <strong>REFLORA — Flora e Funga do Brasil 2020</strong>,
        Instituto de Pesquisas Jardim Botânico do Rio de Janeiro (JBRJ).
        Licença <strong>CC-BY</strong>.
        Atualizado em 2020.
    </div>

</div><!-- /container -->

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const verde  = '#0b5e42';
    const verdes = ['#0b5e42','#1a7a5a','#2d8f63','#3da472','#52b882','#68cc92',
                    '#7edda2','#94edb2','#aafdc2','#c0ffd2'];

    // ── Top 10 Famílias ────────────────────────────────────────
    const dadosFamilias = <?php echo json_encode(array_values($top_familias)); ?>;
    if (dadosFamilias.length > 0) {
        const ctxF = document.getElementById('graficoFamilias');
        new Chart(ctxF, {
            type: 'bar',
            data: {
                labels: dadosFamilias.map(d => d.familia),
                datasets: [{
                    label: 'Espécies',
                    data: dadosFamilias.map(d => parseInt(d.total)),
                    backgroundColor: verdes,
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: '#f0f0f0' } },
                    y: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });
    }

    // ── Formas de Vida ─────────────────────────────────────────
    const dadosFormas = <?php echo json_encode(array_map(
        fn($k, $v) => ['forma' => $k, 'total' => $v],
        array_keys($contagem_formas),
        array_values($contagem_formas)
    )); ?>;
    if (dadosFormas.length > 0) {
        const ctxV = document.getElementById('graficoFormas');
        new Chart(ctxV, {
            type: 'doughnut',
            data: {
                labels: dadosFormas.map(d => d.forma),
                datasets: [{
                    data: dadosFormas.map(d => d.total),
                    backgroundColor: verdes,
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { font: { size: 11 }, boxWidth: 14 }
                    }
                }
            }
        });
    }
})();
</script>

<?php include __DIR__ . '/../includes/rodape.php'; ?>
