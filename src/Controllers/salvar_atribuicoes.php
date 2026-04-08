<?php
// ============================================================
// SALVAR ATRIBUIÇÕES (TEMPORÁRIO)
// Chamado via AJAX POST quando o modal de busca automática fecha.
// Baixa cada imagem para a pasta TEMPORÁRIA (uploads/temp/{temp_id}/)
// e registra na sessão — exatamente como o fluxo de colar/upload.
// O finalizar_upload_temporario.php move tudo para o definitivo.
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
// INPUTS
// ============================================================
$temp_id = trim($_POST['temp_id'] ?? '');
$json    = trim($_POST['atribuicoes_json'] ?? '[]');

// Validar sessão
if (
    !isset($_SESSION['importacao_temporaria']) ||
    $_SESSION['importacao_temporaria']['temp_id']         !== $temp_id ||
    (int)$_SESSION['importacao_temporaria']['usuario_id'] !== $usuario_id
) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão de importação inválida']);
    exit;
}

$atribuicoes = json_decode($json, true);
if (!is_array($atribuicoes) || empty($atribuicoes)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Nenhuma atribuição recebida']);
    exit;
}

// ============================================================
// PASTA TEMPORÁRIA  (mesmo padrão do fluxo de colar)
// ============================================================
$pasta_temp   = dirname(dirname(__DIR__)) . '/uploads/temp/' . $temp_id . '/';
$rel_temp_dir = 'uploads/temp/' . $temp_id . '/';

if (!is_dir($pasta_temp)) {
    mkdir($pasta_temp, 0755, true);
}

// ============================================================
// MIME → extensão
// ============================================================
$mime_para_ext = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
];

$partes_validas = ['folha','flor','fruto','caule','semente','habito','exsicata_completa','detalhe'];

// ============================================================
// PROCESSAR CADA ATRIBUIÇÃO
// ============================================================
$salvas = 0;
$erros  = [];

foreach ($atribuicoes as $atr) {
    $parte    = $atr['parte']    ?? '';
    $url_foto = $atr['url_foto'] ?? '';

    if (!in_array($parte, $partes_validas, true) || empty($url_foto)) {
        $erros[] = "Atribuição inválida: parte='$parte'";
        continue;
    }

    // --- Baixar imagem ---
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url_foto,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_USERAGENT      => 'Penomato/1.0 (penomato.app.br)',
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 3,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $conteudo  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $mime_real = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    if (!$conteudo || $http_code !== 200) {
        $erros[] = "Falha ao baixar (HTTP $http_code): $url_foto";
        error_log("[Penomato] salvar_atribuicoes: falha download — HTTP $http_code — $url_foto");
        continue;
    }

    // --- Determinar extensão ---
    $mime_base = strtolower(trim(explode(';', $mime_real)[0]));
    $ext = $mime_para_ext[$mime_base]
        ?? strtolower(pathinfo(parse_url($url_foto, PHP_URL_PATH), PATHINFO_EXTENSION))
        ?: 'jpg';

    // --- Nome do arquivo na pasta temporária ---
    $nome_arquivo       = $parte . '_' . date('Ymd') . '_' . date('His') . '_' . rand(100, 999) . '.' . $ext;
    $caminho_completo   = $pasta_temp . $nome_arquivo;
    $caminho_temporario = $rel_temp_dir . $nome_arquivo;   // mesmo padrão do fluxo colar

    // --- Salvar em disco (pasta temporária) ---
    if (file_put_contents($caminho_completo, $conteudo) === false) {
        $erros[] = "Falha ao gravar arquivo: $nome_arquivo";
        error_log("[Penomato] salvar_atribuicoes: falha ao gravar $caminho_completo");
        continue;
    }

    // --- Registrar na sessão (igual ao fluxo colar) ---
    // O finalizar_upload_temporario.php vai mover para uploads/exsicatas/ e inserir no BD
    $_SESSION['importacao_temporaria']['imagens'][] = [
        'nome_original'     => $nome_arquivo,
        'caminho_temporario'=> $caminho_temporario,
        'parte_planta'      => $parte,
        'tamanho_bytes'     => strlen($conteudo),
        'mime_type'         => 'image/' . $ext,
        'fonte_nome'        => $atr['fonte_nome']      ?? null,
        'fonte_url'         => $atr['fonte_url']       ?? null,
        'autor_imagem'      => $atr['autor']           ?? null,
        'licenca'           => $atr['licenca']         ?? null,
        'descricao'         => null,
        'principal'         => (int)($atr['principal'] ?? 0),
        'usuario_id'        => $usuario_id,
        'data_upload_temp'  => time(),
        'origem'            => 'internet',
    ];

    $salvas++;
}

// ============================================================
// RESPOSTA
// ============================================================
echo json_encode([
    'sucesso' => $salvas > 0,
    'salvas'  => $salvas,
    'erros'   => $erros,
    'erro'    => $salvas === 0 ? 'Nenhuma imagem pôde ser salva. Verifique os logs.' : null,
]);
