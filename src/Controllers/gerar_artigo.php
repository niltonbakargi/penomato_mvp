<?php
// DEPRECATED — Geração de artigos agora é automática (finalizar_upload_temporario.php).
// Redireciona para a fila de artigos por status.
session_start();
header('Location: ' . APP_BASE . '/src/Controllers/artigos_fila.php');
exit;

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
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
require_once __DIR__ . '/../Config/gerador_texto_botanico.php';
$paragrafos = gerar_paragrafos($c);

// Mapeia qual parte usa quais campos de referência
$refs_por_parte = [
    'caule'   => ['tipo_caule','forma_caule','textura_caule','cor_caule','ramificacao_caule','modificacao_caule','possui_espinhos','possui_latex','possui_seiva','possui_resina'],
    'folha'   => ['tipo_folha','filotaxia_folha','forma_folha','textura_folha','margem_folha','venacao_folha','tamanho_folha'],
    'flor'    => ['cor_flores','simetria_floral','numero_petalas','disposicao_flores','tamanho_flor','aroma'],
    'fruto'   => ['tipo_fruto','tamanho_fruto','cor_fruto','textura_fruto','dispersao_fruto','aroma_fruto'],
    'semente' => ['tipo_semente','tamanho_semente','cor_semente','textura_semente','quantidade_sementes'],
    'outros'  => [],
];

foreach ($paragrafos as $parte => $texto):
    // Coletar refs de todos os atributos desta parte
    $nums_ref = [];
    foreach (($refs_por_parte[$parte] ?? []) as $campo) {
        $r = trim($c[$campo . '_ref'] ?? '');
        if ($r !== '') {
            foreach (explode(',', $r) as $n) {
                $n = (int)trim($n);
                if ($n > 0) $nums_ref[$n] = true;
            }
        }
    }
    ksort($nums_ref);
    $sufixo_ref = !empty($nums_ref)
        ? '<sup class="art-ref">[' . implode(',', array_keys($nums_ref)) . ']</sup>'
        : '';
?>
<p class="art-paragrafo"><?php echo htmlspecialchars($texto); ?><?php echo $sufixo_ref; ?></p>
<?php endforeach; ?>


<?php
// ── REFERÊNCIAS ──
if (!empty($refs_map)):
    // Filtrar apenas as referências realmente citadas
    $refs_citadas = array_intersect_key($refs_map, $todos_refs);
    ksort($refs_citadas);
?>
<?php if ($imagens): ?>
<h3 class="art-secao">Prancha Fotográfica</h3>
<div class="art-galeria">
<?php foreach ($imagens as $img): ?>
    <figure class="art-figura">
        <div class="art-figura-titulo"><?php echo ucfirst(htmlspecialchars($img['parte_planta'])); ?></div>
        <img src="/penomato_mvp/<?php echo htmlspecialchars($img['caminho_imagem']); ?>"
             alt="<?php echo htmlspecialchars($img['parte_planta']); ?>">
        <figcaption>
            <?php if ($img['autor_imagem']): ?><?php echo htmlspecialchars($img['autor_imagem']); ?><?php endif; ?>
            <?php if ($img['licenca']): ?> (<?php echo htmlspecialchars($img['licenca']); ?>)<?php endif; ?>
            <?php if ($img['fonte_nome']): ?> · <?php echo htmlspecialchars($img['fonte_nome']); ?><?php endif; ?>
        </figcaption>
    </figure>
<?php endforeach; ?>
</div>
<?php endif; ?>

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

        .art-galeria { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 16px 0; }
        .art-figura { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #fafafa; }
        .art-figura-titulo { font-size: 0.8em; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #555; padding: 8px 10px 4px; }
        .art-figura img { width: 100%; height: auto; object-fit: contain; display: block; }
        .art-figura figcaption { font-size: 0.75em; color: #888; padding: 6px 10px 8px; line-height: 1.4; }

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
