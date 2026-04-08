<?php
// ============================================================
// BUSCAR IMAGENS AUTOMÁTICO
// Chamado via AJAX (fetch POST) pela página upload_imagens_internet.php
// Busca candidatas no iNaturalist e Wikimedia Commons,
// pontua, e salva as top 5 em temp_imagens_candidatas.
// Retorna JSON.
// ============================================================

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/banco_de_dados.php';

// ============================================================
// AUTENTICAÇÃO
// ============================================================
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado']);
    exit;
}

$usuario_id = (int)$_SESSION['usuario_id'];

// ============================================================
// VALIDAR INPUTS
// ============================================================
$especie_id = (int)($_POST['especie_id'] ?? 0);
$parte      = trim($_POST['parte_planta'] ?? '');
$temp_id    = trim($_POST['temp_id'] ?? '');

$partes_validas = ['folha','flor','fruto','caule','semente','habito','exsicata_completa','detalhe'];

if (!$especie_id || !in_array($parte, $partes_validas, true) || empty($temp_id)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Parâmetros inválidos']);
    exit;
}

// Confirmar que o temp_id pertence a este usuário
if (
    !isset($_SESSION['importacao_temporaria']) ||
    $_SESSION['importacao_temporaria']['temp_id']    !== $temp_id ||
    (int)$_SESSION['importacao_temporaria']['usuario_id'] !== $usuario_id
) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão de importação inválida']);
    exit;
}

// ============================================================
// BUSCAR NOME CIENTÍFICO
// ============================================================
$stmt = $pdo->prepare("SELECT nome_cientifico FROM especies_administrativo WHERE id = ?");
$stmt->execute([$especie_id]);
$especie = $stmt->fetch();

if (!$especie) {
    echo json_encode(['sucesso' => false, 'erro' => 'Espécie não encontrada']);
    exit;
}

$nome_cientifico = $especie['nome_cientifico'];

// ============================================================
// MAPA: parte da planta → termo de busca em inglês
// ============================================================
$termos_en = [
    'folha'             => 'leaf',
    'flor'              => 'flower',
    'fruto'             => 'fruit',
    'caule'             => 'stem',
    'semente'           => 'seed',
    'habito'            => 'habit',
    'exsicata_completa' => 'herbarium specimen',
    'detalhe'           => 'detail',
];

$termo_en = $termos_en[$parte];

// ============================================================
// FUNÇÃO: requisição HTTP com cURL
// ============================================================
function http_get(string $url): ?string
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_CONNECTTIMEOUT => 6,
        CURLOPT_USERAGENT      => 'Penomato/1.0 (penomato.app.br; contato@penomato.app.br)',
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 3,
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $erro      = curl_error($ch);
    curl_close($ch);

    if ($erro || $http_code !== 200 || !$response) {
        error_log("[Penomato] buscar_imagens curl erro — HTTP $http_code — $erro — URL: $url");
        return null;
    }

    return $response;
}

// ============================================================
// FUNÇÃO: normalizar licença para texto legível
// ============================================================
function normalizar_licenca(string $raw): string
{
    $mapa = [
        'cc-by'           => 'CC BY 4.0',
        'cc-by-4.0'       => 'CC BY 4.0',
        'cc by 4.0'       => 'CC BY 4.0',
        'cc-by-sa'        => 'CC BY-SA 4.0',
        'cc-by-sa-4.0'    => 'CC BY-SA 4.0',
        'cc-by-nc'        => 'CC BY-NC 4.0',
        'cc-by-nc-4.0'    => 'CC BY-NC 4.0',
        'cc-by-nc-sa'     => 'CC BY-NC-SA 4.0',
        'cc-by-nc-sa-4.0' => 'CC BY-NC-SA 4.0',
        'cc0'             => 'CC0',
        'cc-pdm'          => 'Domínio Público',
        'pd'              => 'Domínio Público',
        'public domain'   => 'Domínio Público',
    ];

    $chave = strtolower(trim($raw));
    return $mapa[$chave] ?? (strtoupper($raw) ?: 'Não informada');
}

// ============================================================
// FUNÇÃO: pontuar candidata
// ============================================================
function pontuar(array $c): int
{
    $pts = 0;

    // Fonte (iNaturalist = observação de campo validada)
    if ($c['fonte'] === 'inaturalist') $pts += 15;

    // Licença (quanto mais aberta, melhor)
    $lic = strtolower($c['licenca'] ?? '');
    if (str_contains($lic, 'cc0') || str_contains($lic, 'domínio público')) $pts += 25;
    elseif ($lic === 'cc by 4.0')                                            $pts += 22;
    elseif (str_contains($lic, 'cc by-sa'))                                  $pts += 18;
    elseif (str_contains($lic, 'cc by-nc'))                                  $pts += 12;

    // Localidade (prioriza estados do Cerrado)
    $local = strtolower($c['local_coleta'] ?? '');
    if (str_contains($local, 'mato grosso do sul') || str_contains($local, ', ms')) $pts += 20;
    elseif (str_contains($local, 'goiás')           || str_contains($local, ', go')) $pts += 15;
    elseif (str_contains($local, 'mato grosso')     || str_contains($local, ', mt')) $pts += 15;
    elseif (str_contains($local, 'brasil')          || str_contains($local, 'brazil')) $pts += 5;

    // Resolução
    $largura = (int)($c['largura_px'] ?? 0);
    if ($largura >= 1024)     $pts += 15;
    elseif ($largura >= 800)  $pts += 10;
    elseif ($largura >= 500)  $pts += 5;

    // Recência
    if (!empty($c['data_observacao'])) {
        $ano = (int)substr($c['data_observacao'], 0, 4);
        if ($ano >= 2020)      $pts += 10;
        elseif ($ano >= 2015)  $pts += 5;
    }

    return max(0, $pts);
}

// ============================================================
// BUSCA 1 — iNaturalist
// Retorna observações research_grade com foto, no Brasil
// ============================================================
function buscar_inaturalist(string $nome): array
{
    $url = 'https://api.inaturalist.org/v1/observations?' . http_build_query([
        'taxon_name'    => $nome,
        'quality_grade' => 'research',
        'photos'        => 'true',
        'per_page'      => 20,
        'place_id'      => 6744,    // Brasil
        'order'         => 'desc',
        'order_by'      => 'votes',
    ]);

    $response = http_get($url);
    if (!$response) return [];

    $data = json_decode($response, true);
    if (empty($data['results'])) return [];

    $candidatas = [];

    foreach ($data['results'] as $obs) {
        if (empty($obs['photos'])) continue;

        $foto     = $obs['photos'][0];
        $url_base = $foto['url'] ?? '';

        // iNaturalist serve tamanhos: square, small, medium, large, original
        // A URL vem no tamanho 'square' — substituímos pelo tamanho desejado
        $url_foto      = str_replace('/square.', '/large.',  $url_base);
        $url_thumbnail = str_replace('/square.', '/medium.', $url_base);

        // Coordenadas
        $lat = null;
        $lng = null;
        if (!empty($obs['location'])) {
            $coords = explode(',', $obs['location']);
            $lat    = isset($coords[0]) ? (float)$coords[0] : null;
            $lng    = isset($coords[1]) ? (float)$coords[1] : null;
        }

        $candidatas[] = [
            'url_foto'        => $url_foto,
            'url_thumbnail'   => $url_thumbnail,
            'fonte'           => 'inaturalist',
            'fonte_url'       => 'https://www.inaturalist.org/observations/' . ($obs['id'] ?? ''),
            'fonte_nome'      => 'iNaturalist',
            'id_externo'      => (string)($obs['id'] ?? ''),
            'autor'           => $obs['user']['login'] ?? null,
            'licenca'         => normalizar_licenca($foto['license_code'] ?? ''),
            'local_coleta'    => $obs['place_guess'] ?? null,
            'latitude'        => $lat,
            'longitude'       => $lng,
            'data_observacao' => $obs['observed_on'] ?? null,
            'largura_px'      => null,  // iNaturalist não informa dimensões no endpoint de observações
            'altura_px'       => null,
        ];
    }

    return $candidatas;
}

// ============================================================
// BUSCA 2 — Wikimedia Commons
// Busca por nome científico + termo da parte em inglês
// ============================================================
function buscar_wikimedia(string $nome): array
{
    $url = 'https://commons.wikimedia.org/w/api.php?' . http_build_query([
        'action'       => 'query',
        'generator'    => 'search',
        'gsrsearch'    => $nome,
        'gsrnamespace' => 6,        // Namespace de arquivos
        'gsrlimit'     => 12,
        'prop'         => 'imageinfo',
        'iiprop'       => 'url|user|extmetadata|size',
        'iiurlwidth'   => 800,      // Gera thumbnail de 800px
        'format'       => 'json',
        'origin'       => '*',
    ]);

    $response = http_get($url);
    if (!$response) return [];

    $data = json_decode($response, true);
    if (empty($data['query']['pages'])) return [];

    $candidatas = [];

    foreach ($data['query']['pages'] as $page) {
        $info = $page['imageinfo'][0] ?? null;
        if (!$info) continue;

        $url_foto = $info['url'] ?? '';

        // Aceitar apenas formatos de imagem raster (excluir SVG, OGG, PDF)
        if (!preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $url_foto)) continue;

        $meta    = $info['extmetadata'] ?? [];
        $licenca = normalizar_licenca(
            strip_tags($meta['LicenseShortName']['value'] ?? '')
        );
        $autor = strip_tags($meta['Artist']['value'] ?? ($info['user'] ?? ''));
        $autor = $autor ? mb_substr(trim($autor), 0, 255) : null;

        $candidatas[] = [
            'url_foto'        => $url_foto,
            'url_thumbnail'   => $info['thumburl'] ?? $url_foto,
            'fonte'           => 'wikimedia',
            'fonte_url'       => 'https://commons.wikimedia.org/wiki/' . urlencode($page['title'] ?? ''),
            'fonte_nome'      => 'Wikimedia Commons',
            'id_externo'      => (string)($page['pageid'] ?? ''),
            'autor'           => $autor,
            'licenca'         => $licenca,
            'local_coleta'    => null,
            'latitude'        => null,
            'longitude'       => null,
            'data_observacao' => null,
            'largura_px'      => $info['width']  ?? null,
            'altura_px'       => $info['height'] ?? null,
        ];
    }

    return $candidatas;
}

// ============================================================
// EXECUTAR BUSCAS
// ============================================================
$candidatas_inat = buscar_inaturalist($nome_cientifico);
$candidatas_wiki = buscar_wikimedia($nome_cientifico);

$todas = array_merge($candidatas_inat, $candidatas_wiki);

if (empty($todas)) {
    echo json_encode([
        'sucesso'    => true,
        'total'      => 0,
        'candidatas' => [],
        'aviso'      => 'Nenhuma imagem encontrada nas fontes consultadas.',
    ]);
    exit;
}

// ============================================================
// PONTUAR E ORDENAR
// ============================================================
foreach ($todas as &$c) {
    $c['pontuacao'] = pontuar($c);
}
unset($c);

usort($todas, fn($a, $b) => $b['pontuacao'] <=> $a['pontuacao']);

$top5 = array_slice($todas, 0, 5);

// ============================================================
// SALVAR NO BANCO — limpa candidatas pendentes anteriores e insere novas
// ============================================================
$pdo->prepare(
    "DELETE FROM temp_imagens_candidatas
     WHERE temp_id = ? AND parte_planta = ? AND status = 'pendente'"
)->execute([$temp_id, $parte]);

$sql_insert = "
    INSERT INTO temp_imagens_candidatas
        (especie_id, usuario_id, temp_id, parte_planta,
         url_foto, url_thumbnail,
         fonte, fonte_url, fonte_nome, id_externo,
         autor, licenca,
         local_coleta, latitude, longitude,
         data_observacao, largura_px, altura_px, pontuacao)
    VALUES
        (?, ?, ?, ?,
         ?, ?,
         ?, ?, ?, ?,
         ?, ?,
         ?, ?, ?,
         ?, ?, ?, ?)
";

$stmt = $pdo->prepare($sql_insert);
$candidatas_salvas = [];

foreach ($top5 as $c) {
    $stmt->execute([
        $especie_id, $usuario_id, $temp_id, $parte,
        $c['url_foto'],      $c['url_thumbnail'],
        $c['fonte'],         $c['fonte_url'],  $c['fonte_nome'], $c['id_externo'],
        $c['autor'],         $c['licenca'],
        $c['local_coleta'],  $c['latitude'],   $c['longitude'],
        $c['data_observacao'], $c['largura_px'], $c['altura_px'], $c['pontuacao'],
    ]);

    $c['id'] = (int)$pdo->lastInsertId();
    $candidatas_salvas[] = $c;
}

// ============================================================
// RESPOSTA
// ============================================================
echo json_encode([
    'sucesso'    => true,
    'total'      => count($candidatas_salvas),
    'parte'      => $parte,
    'especie'    => $nome_cientifico,
    'candidatas' => $candidatas_salvas,
]);
