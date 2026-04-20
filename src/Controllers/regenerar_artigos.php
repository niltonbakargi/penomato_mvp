<?php
/**
 * REGENERAR ARTIGOS SALVOS
 * Acesse via browser (logado como gestor) para regenerar todos os artigos
 * existentes no banco usando o gerador de texto atual.
 * Delete este arquivo após o uso.
 */
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';
require_once __DIR__ . '/../Config/gerador_texto_botanico.php';

// Apenas gestores podem acessar esta ferramenta
if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'gestor') {
    http_response_code(403);
    die('Acesso negado. Apenas gestores podem usar esta ferramenta.');
}

// ── helpers (mesmos do gerar_artigo.php) ────────────────────────────────
function ref2(string $refs): string {
    $refs = trim($refs);
    if ($refs === '') return '';
    return '<sup>' . htmlspecialchars($refs) . '</sup>';
}
function val2(?string $v, string $fallback = ''): string {
    return (trim($v ?? '') !== '') ? trim($v) : $fallback;
}
function parsearRefs2(string $texto): array {
    $resultado = [];
    $partes = preg_split('/(?=\n?\d+\.\s)/', $texto, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($partes as $parte) {
        $parte = trim($parte);
        if (preg_match('/^(\d+)\.\s+(.+)$/s', $parte, $m)) {
            $resultado[(int)$m[1]] = trim($m[2]);
        }
    }
    return $resultado;
}

$refs_por_parte = [
    'caule'   => ['tipo_caule','estrutura_caule','forma_caule','textura_caule','cor_caule','diametro_caule','ramificacao_caule','modificacao_caule','possui_espinhos','possui_latex','possui_seiva','possui_resina'],
    'folha'   => ['tipo_folha','filotaxia_folha','forma_folha','textura_folha','margem_folha','venacao_folha','tamanho_folha'],
    'flor'    => ['cor_flores','simetria_floral','numero_petalas','disposicao_flores','tamanho_flor','aroma'],
    'fruto'   => ['tipo_fruto','tamanho_fruto','cor_fruto','textura_fruto','dispersao_fruto','aroma_fruto'],
    'semente' => ['tipo_semente','tamanho_semente','cor_semente','textura_semente','quantidade_sementes'],
    'outros'  => [],
];

// ── buscar todos os artigos existentes ──────────────────────────────────
$artigos = $pdo->query("
    SELECT a.especie_id
    FROM artigos a
    INNER JOIN especies_caracteristicas ec ON ec.especie_id = a.especie_id
")->fetchAll(PDO::FETCH_COLUMN);

$resultados = [];

foreach ($artigos as $especie_id) {

    $adm = $pdo->prepare("SELECT * FROM especies_administrativo WHERE id = ?");
    $adm->execute([$especie_id]);
    $adm = $adm->fetch(PDO::FETCH_ASSOC);

    $ec = $pdo->prepare("SELECT * FROM especies_caracteristicas WHERE especie_id = ? LIMIT 1");
    $ec->execute([$especie_id]);
    $c = $ec->fetch(PDO::FETCH_ASSOC) ?: [];

    $ei = $pdo->prepare("
        SELECT parte_planta, caminho_imagem, autor_imagem, licenca, fonte_nome, fonte_url
        FROM especies_imagens
        WHERE especie_id = ?
        ORDER BY FIELD(parte_planta,'habito','folha','flor','fruto','caule','semente','exsicata_completa','detalhe')
    ");
    $ei->execute([$especie_id]);
    $imagens = $ei->fetchAll(PDO::FETCH_ASSOC);

    if (!$adm || !$c) {
        $resultados[] = ['id' => $especie_id, 'ok' => false, 'msg' => 'Dados incompletos'];
        continue;
    }

    $refs_map = parsearRefs2($c['referencias'] ?? '');

    // Coletar todas as refs usadas
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

    $paragrafos = gerar_paragrafos($c);

    // ── montar HTML ──────────────────────────────────────────────────────
    ob_start();
    ?>
<div class="artigo">

<h2 class="art-titulo"><?php echo htmlspecialchars(val2($c['nome_cientifico_completo'], $adm['nome_cientifico'])); ?><?php echo ref2($c['nome_cientifico_completo_ref'] ?? ''); ?></h2>
<p class="art-familia"><strong>Família:</strong> <?php echo htmlspecialchars(val2($c['familia'])); ?><?php echo ref2($c['familia_ref'] ?? ''); ?></p>

<?php if (!empty($c['sinonimos'])): ?>
<p class="art-sinonimos"><strong>Sinonímia:</strong> <em><?php
    $sins = array_map('trim', explode(',', $c['sinonimos']));
    echo implode(', ', array_map('htmlspecialchars', $sins));
?></em><?php echo ref2($c['sinonimos_ref'] ?? ''); ?></p>
<?php endif; ?>

<?php if (!empty($c['nome_popular'])): ?>
<p class="art-nomes"><strong>Nomes populares:</strong> <?php echo htmlspecialchars($c['nome_popular']); ?><?php echo ref2($c['nome_popular_ref'] ?? ''); ?></p>
<?php endif; ?>

<h3 class="art-secao">Descrição</h3>

<?php foreach ($paragrafos as $parte => $texto):
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

<?php if (!empty($refs_map)):
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
    $novo_html = ob_get_clean();

    $stmt = $pdo->prepare("
        UPDATE artigos
        SET texto_html = ?, atualizado_em = NOW()
        WHERE especie_id = ?
    ");
    $stmt->execute([$novo_html, $especie_id]);

    $resultados[] = [
        'id'   => $especie_id,
        'nome' => $adm['nome_cientifico'],
        'ok'   => true,
        'msg'  => 'Atualizado',
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Regenerar Artigos</title>
<style>
body { font-family: sans-serif; padding: 30px; max-width: 700px; margin: auto; }
h1 { color: #0d4f35; margin-bottom: 20px; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 0.9em; }
th { background: #f5f5f5; }
.ok  { color: #155724; }
.err { color: #721c24; }
.aviso { background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 12px 16px; margin-top: 24px; font-size: 0.88em; }
</style>
</head>
<body>
<h1>Regeneração de Artigos</h1>
<table>
<tr><th>Espécie</th><th>Resultado</th></tr>
<?php foreach ($resultados as $r): ?>
<tr>
    <td><em><?php echo htmlspecialchars($r['nome'] ?? 'ID ' . $r['id']); ?></em></td>
    <td class="<?php echo $r['ok'] ? 'ok' : 'err'; ?>"><?php echo htmlspecialchars($r['msg']); ?></td>
</tr>
<?php endforeach; ?>
</table>

<div class="aviso">
    ⚠️ <strong>Delete este arquivo após o uso:</strong>
    <code>src/Controllers/regenerar_artigos.php</code>
</div>
</body>
</html>
