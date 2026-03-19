<?php
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /penomato_mvp/src/Views/auth/login.php');
    exit;
}

// ================================================
// FUNÇÕES AUXILIARES
// ================================================

/**
 * Retorna superscript com os números de referência.
 * Ex: "2,4" → <sup>2,4</sup>
 */
function ref(string $refs): string {
    $refs = trim($refs);
    if ($refs === '' || $refs === null) return '';
    return '<sup>' . htmlspecialchars($refs) . '</sup>';
}

/**
 * Retorna o valor apenas se não estiver vazio, senão retorna o fallback.
 */
function val(?string $v, string $fallback = ''): string {
    return (trim($v ?? '') !== '') ? trim($v) : $fallback;
}

/**
 * Junta atributos em texto corrido, ignorando vazios.
 * Cada item: ['texto' => '...', 'ref' => '2,4']
 */
function listar(array $itens, string $separador = ', '): string {
    $partes = [];
    foreach ($itens as $item) {
        $texto = trim($item['texto'] ?? '');
        if ($texto === '' || strtolower($texto) === 'não informado') continue;
        $partes[] = $texto . ref($item['ref'] ?? '');
    }
    return implode($separador, $partes);
}

/**
 * Parseia o campo "referencias" em array indexado pelo número.
 * Ex: "1. AUTOR... 2. AUTOR..." → [1 => 'AUTOR...', 2 => 'AUTOR...']
 */
function parsearReferencias(string $texto): array {
    $resultado = [];
    // Divide a cada número seguido de ponto no início de linha ou após quebra
    $partes = preg_split('/(?=\n?\d+\.\s)/', $texto, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($partes as $parte) {
        $parte = trim($parte);
        if (preg_match('/^(\d+)\.\s+(.+)$/s', $parte, $m)) {
            $resultado[(int)$m[1]] = trim($m[2]);
        }
    }
    return $resultado;
}

/**
 * Coleta todos os números de referência usados no artigo
 * e retorna apenas os que existem no array de referências.
 */
function coletarRefsUsadas(array $c, array $refs_map): array {
    $usados = [];
    foreach ($c as $val) {
        if (!is_string($val)) continue;
        // Pega campos que terminam em _ref
        foreach (explode(',', $val) as $n) {
            $n = (int)trim($n);
            if ($n > 0) $usados[$n] = true;
        }
    }
    ksort($usados);
    return array_keys($usados);
}

// ================================================
// PROCESSAR AÇÕES
// ================================================
$especie_id = (int)($_GET['especie_id'] ?? $_POST['especie_id'] ?? 0);
$acao = $_POST['acao'] ?? '';
$msg = null;

if ($acao === 'salvar' && $especie_id) {
    $texto_html = $_POST['texto_html'] ?? '';
    $pdo->prepare("
        INSERT INTO artigos (especie_id, texto_html, status, gerado_em)
        VALUES (?, ?, 'rascunho', NOW())
        ON DUPLICATE KEY UPDATE texto_html = VALUES(texto_html), atualizado_em = NOW(), status = 'rascunho'
    ")->execute([$especie_id, $texto_html]);

    // Envia para fila de aprovação
    $pdo->prepare("
        INSERT INTO fila_aprovacao (tipo, especie_id, usuario_id, descricao)
        VALUES ('revisao', ?, ?, 'Artigo gerado automaticamente — aguarda revisão')
        ON DUPLICATE KEY UPDATE data_submissao = NOW(), status = 'pendente'
    ")->execute([$especie_id, $_SESSION['usuario_id']]);

    $msg = ['tipo' => 'ok', 'texto' => 'Artigo salvo e enviado para revisão do gestor.'];
}

// ================================================
// CARREGAR ESPÉCIES DISPONÍVEIS
// ================================================
$especies_disponiveis = $pdo->query("
    SELECT e.id, e.nome_cientifico, e.status,
           a.id AS artigo_id, a.status AS artigo_status, a.gerado_em
    FROM especies_administrativo e
    LEFT JOIN artigos a ON a.especie_id = e.id
    ORDER BY e.nome_cientifico
")->fetchAll(PDO::FETCH_ASSOC);

// ================================================
// GERAR ARTIGO
// ================================================
$artigo_html = null;
$especie_nome = null;

if ($especie_id) {
    // Dados administrativos
    $ea = $pdo->prepare("SELECT * FROM especies_administrativo WHERE id = ?");
    $ea->execute([$especie_id]);
    $adm = $ea->fetch(PDO::FETCH_ASSOC);

    // Características
    $ec = $pdo->prepare("SELECT * FROM especies_caracteristicas WHERE especie_id = ? LIMIT 1");
    $ec->execute([$especie_id]);
    $c = $ec->fetch(PDO::FETCH_ASSOC) ?: [];

    // Imagens aprovadas
    $ei = $pdo->prepare("
        SELECT parte_planta, caminho_imagem, autor_imagem, licenca, fonte_nome
        FROM especies_imagens
        WHERE especie_id = ?
        ORDER BY FIELD(parte_planta,'habito','folha','flor','fruto','caule','semente','exsicata_completa','detalhe')
    ");
    $ei->execute([$especie_id]);
    $imagens = $ei->fetchAll(PDO::FETCH_ASSOC);

    if ($adm && $c) {
        $especie_nome = $adm['nome_cientifico'];
        $refs_map = parsearReferencias($c['referencias'] ?? '');

        // ── Coletar todos os números de ref usados ──
        $todos_refs = [];
        foreach ($c as $k => $v) {
            if (str_ends_with($k, '_ref') && trim($v ?? '') !== '') {
                foreach (explode(',', $v) as $n) {
                    $n = (int)trim($n);
                    if ($n > 0) $todos_refs[$n] = true;
                }
            }
        }
        ksort($todos_refs);

        // ================================================
        // MONTAR TEXTO DO ARTIGO
        // ================================================
        ob_start();
        ?>
<div class="artigo">

<h2 class="art-titulo"><?php echo htmlspecialchars(val($c['nome_cientifico_completo'], $adm['nome_cientifico'])); ?><?php echo ref($c['nome_cientifico_completo_ref'] ?? ''); ?></h2>
<p class="art-familia"><strong>Família:</strong> <?php echo htmlspecialchars(val($c['familia'])); ?><?php echo ref($c['familia_ref'] ?? ''); ?></p>

<?php if (!empty($c['sinonimos'])): ?>
<p class="art-sinonimos"><strong>Sinonímia:</strong> <em><?php
    $sins = array_map('trim', explode(',', $c['sinonimos']));
    echo implode(', ', array_map('htmlspecialchars', $sins));
?></em><?php echo ref($c['sinonimos_ref'] ?? ''); ?></p>
<?php endif; ?>

<?php if (!empty($c['nome_popular'])): ?>
<p class="art-nomes"><strong>Nomes populares:</strong> <?php echo htmlspecialchars($c['nome_popular']); ?><?php echo ref($c['nome_popular_ref'] ?? ''); ?></p>
<?php endif; ?>

<h3 class="art-secao">Descrição</h3>

<?php
// ── CAULE ──
$caule_partes = listar([
    ['texto' => val($c['tipo_caule']),       'ref' => $c['tipo_caule_ref'] ?? ''],
    ['texto' => val($c['estrutura_caule']),  'ref' => $c['estrutura_caule_ref'] ?? ''],
    ['texto' => val($c['forma_caule']),      'ref' => $c['forma_caule_ref'] ?? ''],
    ['texto' => val($c['diametro_caule']) ? 'diâmetro ' . strtolower($c['diametro_caule']) : '', 'ref' => $c['diametro_caule_ref'] ?? ''],
]);
$cor_caule   = val($c['cor_caule'])   ? 'coloração ' . strtolower($c['cor_caule'])   . ref($c['cor_caule_ref'] ?? '')   : '';
$tex_caule   = val($c['textura_caule']) ? 'textura ' . strtolower($c['textura_caule']) . ref($c['textura_caule_ref'] ?? '') : '';
$ram_caule   = val($c['ramificacao_caule']) ? 'ramificação ' . strtolower($c['ramificacao_caule']) . ref($c['ramificacao_caule_ref'] ?? '') : '';
$mod_caule   = val($c['modificacao_caule']) ? strtolower($c['modificacao_caule']) . ref($c['modificacao_caule_ref'] ?? '') : '';

$extras_caule = array_filter([$cor_caule, $tex_caule, $ram_caule, $mod_caule]);

$espinhos = (strtolower(val($c['possui_espinhos'], 'Não')) === 'não')
    ? 'desprovido de espinhos' . ref($c['possui_espinhos_ref'] ?? '')
    : 'com espinhos' . ref($c['possui_espinhos_ref'] ?? '');
$latex = (strtolower(val($c['possui_latex'], 'Não')) === 'não')
    ? 'látex ausente' . ref($c['possui_latex_ref'] ?? '')
    : 'com látex' . ref($c['possui_latex_ref'] ?? '');
$resina = (strtolower(val($c['possui_resina'], 'Não')) === 'não')
    ? 'resina ausente' . ref($c['possui_resina_ref'] ?? '')
    : 'com resina' . ref($c['possui_resina_ref'] ?? '');

$frase_caule = 'Caule ' . $caule_partes;
if ($extras_caule) $frase_caule .= ', com ' . implode(', ', $extras_caule);
$frase_caule .= ', ' . implode(', ', array_filter([$espinhos, $latex, $resina])) . '.';
?>
<p class="art-paragrafo"><?php echo $frase_caule; ?></p>

<?php
// ── FOLHAS ──
$folha_partes = listar([
    ['texto' => val($c['tipo_folha']),       'ref' => $c['tipo_folha_ref'] ?? ''],
    ['texto' => val($c['filotaxia_folha']),  'ref' => $c['filotaxia_folha_ref'] ?? ''],
    ['texto' => val($c['forma_folha']) ? 'de forma ' . strtolower($c['forma_folha']) : '', 'ref' => $c['forma_folha_ref'] ?? ''],
    ['texto' => val($c['textura_folha']) ? 'textura ' . strtolower($c['textura_folha']) : '', 'ref' => $c['textura_folha_ref'] ?? ''],
    ['texto' => val($c['margem_folha']) ? 'margem ' . strtolower($c['margem_folha']) : '', 'ref' => $c['margem_folha_ref'] ?? ''],
    ['texto' => val($c['venacao_folha']) ? 'venação ' . strtolower($c['venacao_folha']) : '', 'ref' => $c['venacao_folha_ref'] ?? ''],
    ['texto' => val($c['tamanho_folha']) ? 'tamanho ' . strtolower($c['tamanho_folha']) : '', 'ref' => $c['tamanho_folha_ref'] ?? ''],
]);
if ($folha_partes):
?>
<p class="art-paragrafo">Folhas <?php echo $folha_partes; ?>.</p>
<?php endif; ?>

<?php
// ── FLORES ──
$flor_partes = listar([
    ['texto' => val($c['disposicao_flores']),  'ref' => $c['disposicao_flores_ref'] ?? ''],
    ['texto' => val($c['simetria_floral']),    'ref' => $c['simetria_floral_ref'] ?? ''],
    ['texto' => val($c['numero_petalas']) ? 'com ' . strtolower($c['numero_petalas']) : '', 'ref' => $c['numero_petalas_ref'] ?? ''],
    ['texto' => val($c['cor_flores']) ? 'de coloração ' . strtolower($c['cor_flores']) : '', 'ref' => $c['cor_flores_ref'] ?? ''],
    ['texto' => val($c['tamanho_flor']) ? 'tamanho ' . strtolower($c['tamanho_flor']) : '', 'ref' => $c['tamanho_flor_ref'] ?? ''],
    ['texto' => val($c['aroma']) ? 'aroma ' . strtolower($c['aroma']) : '', 'ref' => $c['aroma_ref'] ?? ''],
]);
if ($flor_partes):
?>
<p class="art-paragrafo">Flores <?php echo $flor_partes; ?>.</p>
<?php endif; ?>

<?php
// ── FRUTOS ──
$fruto_tipo   = val($c['tipo_fruto']) ? strtolower($c['tipo_fruto']) . ref($c['tipo_fruto_ref'] ?? '') : '';
$fruto_tam    = val($c['tamanho_fruto']) ? strtolower($c['tamanho_fruto']) . ref($c['tamanho_fruto_ref'] ?? '') : '';
$fruto_cor    = val($c['cor_fruto']) ? 'de coloração ' . strtolower($c['cor_fruto']) . ref($c['cor_fruto_ref'] ?? '') : '';
$fruto_tex    = val($c['textura_fruto']) ? 'textura ' . strtolower($c['textura_fruto']) . ref($c['textura_fruto_ref'] ?? '') : '';
$fruto_aroma  = val($c['aroma_fruto']) ? 'aroma ' . strtolower($c['aroma_fruto']) . ref($c['aroma_fruto_ref'] ?? '') : '';
$fruto_disp   = val($c['dispersao_fruto']) ? 'dispersão ' . strtolower($c['dispersao_fruto']) . ref($c['dispersao_fruto_ref'] ?? '') : '';

$fruto_partes = implode(', ', array_filter([$fruto_tam, $fruto_cor, $fruto_tex, $fruto_aroma, $fruto_disp]));

if ($fruto_tipo || $fruto_partes):
?>
<p class="art-paragrafo">Fruto do tipo <?php echo $fruto_tipo; ?><?php echo $fruto_partes ? ', ' . $fruto_partes : ''; ?>.</p>
<?php endif; ?>

<?php
// ── SEMENTES ──
$sem_partes = listar([
    ['texto' => val($c['tipo_semente']),     'ref' => $c['tipo_semente_ref'] ?? ''],
    ['texto' => val($c['tamanho_semente']) ? strtolower($c['tamanho_semente']) : '', 'ref' => $c['tamanho_semente_ref'] ?? ''],
    ['texto' => val($c['cor_semente']) ? 'de coloração ' . strtolower($c['cor_semente']) : '', 'ref' => $c['cor_semente_ref'] ?? ''],
    ['texto' => val($c['textura_semente']) ? 'textura ' . strtolower($c['textura_semente']) : '', 'ref' => $c['textura_semente_ref'] ?? ''],
    ['texto' => val($c['quantidade_sementes']) ? strtolower($c['quantidade_sementes']) . ' sementes por fruto' : '', 'ref' => $c['quantidade_sementes_ref'] ?? ''],
]);
if ($sem_partes):
?>
<p class="art-paragrafo">Sementes <?php echo $sem_partes; ?>.</p>
<?php endif; ?>

<?php if ($imagens): ?>
<h3 class="art-secao">Prancha Fotográfica</h3>
<div class="art-galeria">
<?php foreach ($imagens as $img): ?>
    <figure class="art-figura">
        <img src="/penomato_mvp/<?php echo htmlspecialchars($img['caminho_imagem']); ?>"
             alt="<?php echo htmlspecialchars($img['parte_planta']); ?>">
        <figcaption>
            <?php echo ucfirst(htmlspecialchars($img['parte_planta'])); ?>
            <?php if ($img['autor_imagem']): ?> — <?php echo htmlspecialchars($img['autor_imagem']); ?><?php endif; ?>
            <?php if ($img['licenca']): ?> (<?php echo htmlspecialchars($img['licenca']); ?>)<?php endif; ?>
        </figcaption>
    </figure>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php
// ── REFERÊNCIAS ──
if (!empty($refs_map)):
    // Filtrar apenas as referências realmente citadas
    $refs_citadas = array_intersect_key($refs_map, $todos_refs);
    ksort($refs_citadas);
?>
<h3 class="art-secao">Referências</h3>
<ol class="art-refs">
<?php foreach ($refs_citadas as $num => $texto): ?>
    <li id="ref-<?php echo $num; ?>"><?php echo htmlspecialchars($texto); ?></li>
<?php endforeach; ?>
</ol>
<?php endif; ?>

</div><!-- .artigo -->
        <?php
        $artigo_html = ob_get_clean();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de Artigo — Penomato</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f4f0;
            color: #1e2e1e;
            padding: 24px 20px;
        }
        .container { max-width: 1100px; margin: 0 auto; }

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

        .layout { display: grid; grid-template-columns: 280px 1fr; gap: 20px; align-items: start; }
        @media (max-width: 800px) { .layout { grid-template-columns: 1fr; } }

        /* Painel lateral */
        .sidebar { background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); overflow: hidden; }
        .sidebar-header { background: #f7f9f7; padding: 12px 16px; border-bottom: 2px solid #e0e8e0; font-size: 0.88em; font-weight: 700; color: var(--cor-primaria); }
        .especie-item { padding: 10px 16px; border-bottom: 1px solid #f3f3f3; font-size: 0.84em; cursor: pointer; display: flex; flex-direction: column; gap: 3px; text-decoration: none; color: #333; }
        .especie-item:hover { background: #f0fdf0; }
        .especie-item.ativa { background: #e8f5e9; border-left: 3px solid var(--cor-primaria); }
        .especie-item .nome { font-style: italic; font-weight: 600; color: #1a3a28; }
        .especie-item .meta { font-size: 0.78em; color: #aaa; display: flex; gap: 8px; }
        .badge-artigo { background: #d4edda; color: #155724; padding: 1px 7px; border-radius: 8px; font-size: 0.75em; font-weight: 600; }
        .badge-sem { background: #f0f0f0; color: #999; padding: 1px 7px; border-radius: 8px; font-size: 0.75em; }

        /* Área principal */
        .main-card { background: white; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); overflow: hidden; }
        .main-header { background: #f7f9f7; padding: 14px 20px; border-bottom: 2px solid #e0e8e0; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .main-header h2 { font-size: 0.95em; font-weight: 700; color: var(--cor-primaria); font-style: italic; }

        .btn-gerar { background: var(--cor-primaria); color: white; border: none; padding: 8px 20px; border-radius: 6px; font-size: 0.88em; font-weight: 600; cursor: pointer; }
        .btn-gerar:hover { background: #094d36; }
        .btn-salvar { background: #155724; color: white; border: none; padding: 8px 20px; border-radius: 6px; font-size: 0.88em; font-weight: 600; cursor: pointer; }
        .btn-salvar:hover { background: #0d3a18; }

        .msg-ok  { background:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:6px; padding:10px 14px; margin:14px 20px 0; font-size:0.9em; }
        .msg-err { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; border-radius:6px; padding:10px 14px; margin:14px 20px 0; font-size:0.9em; }

        .empty-main { text-align: center; padding: 60px; color: #ccc; font-size: 0.95em; }

        /* Artigo */
        .artigo-preview { padding: 30px 40px; }

        .artigo { font-family: 'Georgia', serif; line-height: 1.8; color: #1a1a1a; }
        .art-titulo { font-size: 1.3em; font-weight: 700; font-style: italic; color: var(--cor-primaria); margin-bottom: 6px; }
        .art-familia { font-size: 0.9em; color: #555; margin-bottom: 6px; }
        .art-sinonimos { font-size: 0.88em; color: #555; margin-bottom: 4px; }
        .art-nomes { font-size: 0.88em; color: #555; margin-bottom: 16px; }
        .art-secao { font-size: 1em; font-weight: 700; color: var(--cor-primaria); margin: 20px 0 8px; border-bottom: 1px solid #e0e8e0; padding-bottom: 4px; font-family: 'Segoe UI', sans-serif; }
        .art-paragrafo { font-size: 0.95em; margin-bottom: 10px; text-align: justify; }
        .art-paragrafo sup { font-size: 0.7em; color: var(--cor-primaria); font-family: sans-serif; }

        .art-galeria { display: flex; gap: 12px; flex-wrap: wrap; margin: 12px 0; }
        .art-figura { text-align: center; flex: 0 0 auto; }
        .art-figura img { width: 140px; height: 100px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; display: block; }
        .art-figura figcaption { font-size: 0.72em; color: #888; margin-top: 4px; font-family: sans-serif; max-width: 140px; }

        .art-refs { padding-left: 20px; font-size: 0.82em; color: #444; line-height: 1.7; }
        .art-refs li { margin-bottom: 4px; }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1>📄 Gerador de Artigo Científico</h1>
        <a href="/penomato_mvp/src/Controllers/controlador_gestor.php" class="btn-voltar">← Voltar</a>
    </div>

    <div class="layout">

        <!-- Sidebar: lista de espécies -->
        <div class="sidebar">
            <div class="sidebar-header">🌿 Espécies</div>
            <?php foreach ($especies_disponiveis as $e): ?>
            <a href="gerar_artigo.php?especie_id=<?php echo $e['id']; ?>"
               class="especie-item <?php echo $e['id'] == $especie_id ? 'ativa' : ''; ?>">
                <span class="nome"><?php echo htmlspecialchars($e['nome_cientifico']); ?></span>
                <span class="meta">
                    <span><?php echo $e['status']; ?></span>
                    <?php if ($e['artigo_id']): ?>
                        <span class="badge-artigo">✓ Artigo</span>
                    <?php else: ?>
                        <span class="badge-sem">Sem artigo</span>
                    <?php endif; ?>
                </span>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Área principal -->
        <div class="main-card">
            <?php if ($msg): ?>
                <div class="msg-<?php echo $msg['tipo']; ?>"><?php echo htmlspecialchars($msg['texto']); ?></div>
            <?php endif; ?>

            <?php if ($especie_id && $artigo_html): ?>
            <div class="main-header">
                <h2><?php echo htmlspecialchars($especie_nome); ?></h2>
                <div style="display:flex;gap:8px;">
                    <a href="gerar_artigo.php?especie_id=<?php echo $especie_id; ?>" class="btn-gerar">↺ Regenerar</a>
                    <form method="POST" action="gerar_artigo.php" style="display:inline;">
                        <input type="hidden" name="especie_id" value="<?php echo $especie_id; ?>">
                        <input type="hidden" name="acao" value="salvar">
                        <input type="hidden" name="texto_html" value="<?php echo htmlspecialchars($artigo_html); ?>">
                        <button type="submit" class="btn-salvar">💾 Salvar e enviar para revisão</button>
                    </form>
                </div>
            </div>
            <div class="artigo-preview">
                <?php echo $artigo_html; ?>
            </div>

            <?php elseif ($especie_id): ?>
            <div class="empty-main">
                Espécie selecionada não possui características cadastradas.<br>
                <small style="color:#ddd;">Preencha os dados antes de gerar o artigo.</small>
            </div>

            <?php else: ?>
            <div class="empty-main">
                ← Selecione uma espécie para gerar o artigo.
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>
</body>
</html>
