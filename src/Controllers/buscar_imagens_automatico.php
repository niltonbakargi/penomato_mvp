<?php
// ============================================================
// BUSCAR IMAGENS AUTOMÁTICO
// Chamado via AJAX (fetch POST) pela página upload_imagens_internet.php
// Busca candidatas no iNaturalist e Wikimedia Commons,
// pontua e retorna as top 5 como JSON.
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
$pagina     = max(1, (int)($_POST['pagina'] ?? 1));
$fonte_filtro = trim($_POST['fonte'] ?? 'todas'); // todas | inaturalist | gbif | wikimedia | flora_digital | powo

if (!$especie_id) {
    echo json_encode(['sucesso' => false, 'erro' => 'Parâmetros inválidos']);
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
// FUNÇÃO: requisição HTTP com cURL
// ============================================================
$_http_erros = [];   // acumula erros das chamadas cURL
$_ssl_verify = (defined('APP_ENV') && APP_ENV === 'dev') ? false : true;

function http_get(string $url): ?string
{
    global $_ssl_verify, $_http_erros;
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_CONNECTTIMEOUT => 6,
        CURLOPT_USERAGENT      => 'Penomato/1.0 (penomato.app.br; contato@penomato.app.br)',
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        CURLOPT_SSL_VERIFYPEER => $_ssl_verify,
        CURLOPT_SSL_VERIFYHOST => $_ssl_verify ? 2 : 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 3,
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $erro      = curl_error($ch);
    curl_close($ch);

    if ($erro || $http_code !== 200 || !$response) {
        $msg = "HTTP $http_code" . ($erro ? " — $erro" : '');
        error_log("[Penomato] buscar_imagens — $msg — URL: $url");
        $_http_erros[] = $msg;
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

    // Fonte
    if ($c['fonte'] === 'inaturalist')       $pts += 15;
    elseif ($c['fonte'] === 'gbif')          $pts += 12;
    elseif ($c['fonte'] === 'flora_digital') $pts += 18; // fonte botânica acadêmica brasileira
    elseif ($c['fonte'] === 'powo')          $pts += 16; // Kew Gardens — referência taxonômica global

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
function buscar_inaturalist(string $nome, int $pagina = 1): array
{
    $url = 'https://api.inaturalist.org/v1/observations?' . http_build_query([
        'taxon_name'    => $nome,
        'quality_grade' => 'research',
        'photos'        => 'true',
        'per_page'      => 15,
        'page'          => $pagina,
        'captive'       => 'false',
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
function buscar_wikimedia(string $nome, int $pagina = 1): array
{
    $url = 'https://commons.wikimedia.org/w/api.php?' . http_build_query([
        'action'       => 'query',
        'generator'    => 'search',
        'gsrsearch'    => $nome,
        'gsrnamespace' => 6,        // Namespace de arquivos
        'gsrlimit'     => 10,
        'gsroffset'    => ($pagina - 1) * 10,
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

        // url pode vir depois de thumburl no JSON — usar thumburl como fallback
        $url_foto      = $info['url']      ?? ($info['thumburl'] ?? '');
        $url_thumbnail = $info['thumburl'] ?? $url_foto;

        // Aceitar apenas formatos de imagem raster (excluir SVG, OGG, PDF, WebM)
        // Regex sem âncora $ para tolerar query params na URL
        if (!$url_foto || !preg_match('/\.(jpg|jpeg|png|gif|webp)/i', $url_foto)) continue;

        $meta    = $info['extmetadata'] ?? [];
        $licenca = normalizar_licenca(
            strip_tags($meta['LicenseShortName']['value'] ?? '')
        );
        $autor = strip_tags($meta['Artist']['value'] ?? ($info['user'] ?? ''));
        $autor = $autor ? mb_substr(trim($autor), 0, 255) : null;

        $candidatas[] = [
            'url_foto'        => $url_foto,
            'url_thumbnail'   => $url_thumbnail,
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
// BUSCA 3 — GBIF (Global Biodiversity Information Facility)
// Busca ocorrências com mídia fotográfica, priorizando Brasil
// ============================================================
function buscar_gbif(string $nome, int $pagina = 1): array
{
    $url = 'https://api.gbif.org/v1/occurrence/search?' . http_build_query([
        'scientificName' => $nome,
        'mediaType'      => 'StillImage',
        'country'        => 'BR',
        'limit'          => 20,
        'offset'         => ($pagina - 1) * 20,
    ]);

    $response = http_get($url);
    if (!$response) return [];

    $data = json_decode($response, true);
    if (empty($data['results'])) return [];

    $candidatas = [];

    foreach ($data['results'] as $occ) {
        if (empty($occ['media'])) continue;

        foreach ($occ['media'] as $media) {
            if (($media['type'] ?? '') !== 'StillImage') continue;

            $url_foto = $media['identifier'] ?? '';
            if (!$url_foto || !preg_match('/\.(jpg|jpeg|png|gif|webp)/i', $url_foto)) continue;

            // Normalizar licença — GBIF retorna URL da licença
            $lic_raw = $media['license'] ?? '';
            if (str_contains($lic_raw, 'cc0'))         $licenca = 'CC0';
            elseif (str_contains($lic_raw, '/by/'))    $licenca = 'CC BY 4.0';
            elseif (str_contains($lic_raw, '/by-sa/')) $licenca = 'CC BY-SA 4.0';
            elseif (str_contains($lic_raw, '/by-nc/')) $licenca = 'CC BY-NC 4.0';
            else                                        $licenca = normalizar_licenca($lic_raw);

            $estado = $occ['stateProvince'] ?? null;
            $local  = trim(implode(', ', array_filter([$estado, 'Brasil'])));

            $candidatas[] = [
                'url_foto'        => $url_foto,
                'url_thumbnail'   => $url_foto,
                'fonte'           => 'gbif',
                'fonte_url'       => 'https://www.gbif.org/occurrence/' . ($occ['key'] ?? ''),
                'fonte_nome'      => 'GBIF',
                'id_externo'      => (string)($occ['key'] ?? ''),
                'autor'           => $media['rightsHolder'] ?? ($occ['recordedBy'] ?? null),
                'licenca'         => $licenca,
                'local_coleta'    => $local ?: null,
                'latitude'        => $occ['decimalLatitude']  ?? null,
                'longitude'       => $occ['decimalLongitude'] ?? null,
                'data_observacao' => isset($occ['eventDate']) ? substr($occ['eventDate'], 0, 10) : null,
                'largura_px'      => null,
                'altura_px'       => null,
            ];

            // Uma imagem por ocorrência é suficiente
            break;
        }
    }

    return $candidatas;
}

// ============================================================
// BUSCA 4 — Flora Digital UFSC
// Tenta raspar imagens do HTML estático. Se JS dinâmico, retorna 0
// mas o frontend exibe botão de link direto para o site.
// Licença: CC BY-NC-SA 4.0
// ============================================================
function buscar_flora_digital(string $nome, int $pagina = 1): array
{
    if ($pagina > 1) return []; // scraping estático — sem paginação real

    // 1. Confirmar que a espécie existe no Flora Digital
    $url_search = 'https://floradigital.ufsc.br/search_sp.php?query=' . urlencode($nome);
    $resp_search = http_get($url_search);
    if (!$resp_search) return [];

    $nomes = json_decode($resp_search, true);
    if (!is_array($nomes) || empty($nomes)) return [];

    // Encontrar correspondência exata ou parcial
    $nome_lower = strtolower($nome);
    $nome_encontrado = null;
    foreach ($nomes as $n) {
        if (strtolower($n) === $nome_lower) { $nome_encontrado = $n; break; }
    }
    if (!$nome_encontrado) {
        // Correspondência parcial (apenas gênero+epíteto, sem autoridade)
        $partes = explode(' ', $nome);
        $prefixo = strtolower(implode(' ', array_slice($partes, 0, 2)));
        foreach ($nomes as $n) {
            if (str_starts_with(strtolower($n), $prefixo)) { $nome_encontrado = $n; break; }
        }
    }
    if (!$nome_encontrado) return [];

    // 2. Tentar raspar a página da espécie
    $url_sp = 'https://floradigital.ufsc.br/open_sp.php?sp=' . urlencode($nome_encontrado);
    $html = http_get($url_sp);

    $candidatas = [];

    if ($html) {
        // Buscar padrões de imagem no HTML estático
        preg_match_all('/imagens\/([a-f0-9]+\.(jpg|jpeg|png))/i', $html, $matches);
        $urls_vistas = [];

        foreach ($matches[0] as $caminho) {
            $url_foto = 'https://floradigital.ufsc.br/' . $caminho;
            $url_thumb = str_replace('imagens/', 'thumbs/', $url_foto);

            if (in_array($url_foto, $urls_vistas)) continue;
            $urls_vistas[] = $url_foto;

            $candidatas[] = [
                'url_foto'        => $url_foto,
                'url_thumbnail'   => $url_thumb,
                'fonte'           => 'flora_digital',
                'fonte_url'       => $url_sp,
                'fonte_nome'      => 'Flora Digital UFSC',
                'id_externo'      => md5($url_foto),
                'autor'           => null,
                'licenca'         => 'CC BY-NC-SA 4.0',
                'local_coleta'    => 'Brasil',
                'latitude'        => null,
                'longitude'       => null,
                'data_observacao' => null,
                'largura_px'      => null,
                'altura_px'       => null,
            ];

            if (count($candidatas) >= 6) break;
        }
    }

    return $candidatas;
}

// ============================================================
// BUSCA 5 — Plants of the World Online (POWO / Kew)
// Usa IPNI API para obter o fqId e raspa a página do POWO.
// Licença: CC BY 3.0 (dados Kew)
// ============================================================
function buscar_powo(string $nome, int $pagina = 1): array
{
    if ($pagina > 1) return []; // scraping estático — sem paginação real
    // 1. Buscar fqId via IPNI
    $url_ipni = 'https://www.ipni.org/api/1/search?' . http_build_query([
        'q' => $nome,
        'f' => 'infraspecific',
    ]);

    $resp_ipni = http_get($url_ipni);
    if (!$resp_ipni) { error_log("[POWO] IPNI falhou: $nome"); return []; }

    $dados_ipni = json_decode($resp_ipni, true);
    $resultados = $dados_ipni['results'] ?? [];
    if (empty($resultados)) { error_log("[POWO] IPNI sem resultados: $nome"); return []; }

    // Encontrar resultado aceito no POWO com correspondência de nome
    $fq_id = null;
    $nome_lower = strtolower($nome);
    $partes  = explode(' ', $nome);
    $prefixo = strtolower(implode(' ', array_slice($partes, 0, 2)));

    foreach ($resultados as $r) {
        if (empty($r['inPowo']) || empty($r['fqId'])) continue;
        $nome_resultado = strtolower(trim(($r['genus'] ?? '') . ' ' . ($r['species'] ?? '')));
        if ($nome_resultado === $prefixo || strtolower($r['name'] ?? '') === $nome_lower) {
            $fq_id = $r['fqId']; break;
        }
    }
    if (!$fq_id) {
        foreach ($resultados as $r) {
            if (!empty($r['inPowo']) && !empty($r['fqId'])) { $fq_id = $r['fqId']; break; }
        }
    }

    if (!$fq_id) { error_log("[POWO] fqId não encontrado: $nome"); return []; }
    error_log("[POWO] fqId: $fq_id");

    // 2. Raspar página do POWO
    $url_powo = 'https://powo.science.kew.org/taxon/' . urlencode($fq_id);
    $html = http_get($url_powo);
    if (!$html) { error_log("[POWO] página falhou: $url_powo"); return []; }
    error_log("[POWO] HTML: " . strlen($html) . " bytes");

    // 3. Extrair imagens do CloudFront — padrão amplo
    preg_match_all(
        '#(?:https?:)?//d2seqvvyy3b8p2\.cloudfront\.net/\S+\.(?:jpg|jpeg|png)#i',
        $html,
        $matches
    );
    error_log("[POWO] imagens encontradas: " . count($matches[0]));

    $candidatas  = [];
    $urls_vistas = [];

    foreach ($matches[0] as $url_foto) {
        if (!str_starts_with($url_foto, 'http')) $url_foto = 'https:' . $url_foto;
        if (in_array($url_foto, $urls_vistas)) continue;
        $urls_vistas[] = $url_foto;

        $candidatas[] = [
            'url_foto'        => $url_foto,
            'url_thumbnail'   => $url_foto,
            'fonte'           => 'powo',
            'fonte_url'       => $url_powo,
            'fonte_nome'      => 'POWO / Kew',
            'id_externo'      => md5($url_foto),
            'autor'           => 'Royal Botanic Gardens, Kew',
            'licenca'         => 'CC BY 3.0',
            'local_coleta'    => null,
            'latitude'        => null,
            'longitude'       => null,
            'data_observacao' => null,
            'largura_px'      => null,
            'altura_px'       => null,
        ];

        if (count($candidatas) >= 6) break;
    }

    return $candidatas;
}

// ============================================================
// EXECUTAR BUSCAS
// ============================================================
$candidatas_inat  = in_array($fonte_filtro, ['todas','inaturalist'])   ? buscar_inaturalist($nome_cientifico, $pagina)  : [];
$candidatas_wiki  = in_array($fonte_filtro, ['todas','wikimedia'])     ? buscar_wikimedia($nome_cientifico, $pagina)    : [];
$candidatas_gbif  = in_array($fonte_filtro, ['todas','gbif'])          ? buscar_gbif($nome_cientifico, $pagina)         : [];
$candidatas_flora = []; // Flora Digital carrega imagens via JS — scraping não funciona
$candidatas_powo  = in_array($fonte_filtro, ['todas','powo'])          ? buscar_powo($nome_cientifico, $pagina)          : [];

$todas = array_merge($candidatas_inat, $candidatas_wiki, $candidatas_gbif, $candidatas_flora, $candidatas_powo);

if (empty($todas)) {
    echo json_encode([
        'sucesso'    => true,
        'total'      => 0,
        'candidatas' => [],
        'aviso'      => 'Nenhuma imagem encontrada.',
        'debug'      => [
            'inat'  => count($candidatas_inat),
            'wiki'  => count($candidatas_wiki),
            'gbif'  => count($candidatas_gbif),
            'erros' => $GLOBALS['_http_erros'],
        ],
    ]);
    exit;
}

// ============================================================
// PONTUAR, ORDENAR E RETORNAR TOP 10
// ============================================================
foreach ($todas as &$c) {
    $c['pontuacao'] = pontuar($c);
}
unset($c);

usort($todas, fn($a, $b) => $b['pontuacao'] <=> $a['pontuacao']);

$top10 = array_slice($todas, 0, 10);

// ============================================================
// RESPOSTA
// ============================================================
echo json_encode([
    'sucesso'    => true,
    'total'      => count($top10),
    'pagina'     => $pagina,
    'especie'    => $nome_cientifico,
    'candidatas' => $top10,
]);
