<?php
// ============================================================
// SALVAR ATRIBUIÇÕES DE IMAGENS
// Chamado via AJAX POST quando o modal de busca automática fecha.
// Recebe array JSON de atribuições {parte, url_foto, metadata}.
// Baixa cada imagem, salva em disco e insere em especies_imagens.
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

$especie_id = (int)$_SESSION['importacao_temporaria']['especie_id'];

$atribuicoes = json_decode($json, true);
if (!is_array($atribuicoes) || empty($atribuicoes)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Nenhuma atribuição recebida']);
    exit;
}

// ============================================================
// GARANTIR DIRETÓRIO DE UPLOAD
// ============================================================
$dir_upload   = __DIR__ . '/../../uploads/exsicatas/' . $especie_id . '/';
$dir_relativo = 'uploads/exsicatas/' . $especie_id . '/';

if (!is_dir($dir_upload)) {
    mkdir($dir_upload, 0755, true);
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
    $parte     = $atr['parte']     ?? '';
    $url_foto  = $atr['url_foto']  ?? '';
    $principal = (int)($atr['principal'] ?? 0);

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

    // --- Gerar nome de arquivo ---
    $nome_arquivo     = $parte . '_' . date('Ymd') . '_' . date('His') . '_' . rand(100, 999) . '.' . $ext;
    $caminho_completo = $dir_upload   . $nome_arquivo;
    $caminho_relativo = $dir_relativo . $nome_arquivo;

    // --- Salvar em disco ---
    if (file_put_contents($caminho_completo, $conteudo) === false) {
        $erros[] = "Falha ao gravar arquivo: $nome_arquivo";
        error_log("[Penomato] salvar_atribuicoes: falha ao gravar $caminho_completo");
        continue;
    }

    // --- Inserir em especies_imagens ---
    $stmt = $pdo->prepare("
        INSERT INTO especies_imagens
            (especie_id, tipo_imagem, origem, parte_planta,
             caminho_imagem, nome_original, tamanho_bytes, mime_type,
             fonte_nome, fonte_url, autor_imagem, licenca, principal,
             local_coleta, data_coleta,
             id_usuario_identificador, status_validacao)
        VALUES
            (?, 'provisoria', 'internet', ?,
             ?, ?, ?, ?,
             ?, ?, ?, ?, ?,
             ?, ?,
             ?, 'aprovado')
    ");

    $stmt->execute([
        $especie_id,
        $parte,
        $caminho_relativo,
        $nome_arquivo,
        strlen($conteudo),
        'image/' . $ext,
        $atr['fonte_nome']      ?? null,
        $atr['fonte_url']       ?? null,
        $atr['autor']           ?? null,
        $atr['licenca']         ?? null,
        $principal,
        $atr['local_coleta']    ?? null,
        $atr['data_observacao'] ?? null,
        $usuario_id,
    ]);

    $imagem_id = (int)$pdo->lastInsertId();

    // --- Atualizar sessão ---
    $_SESSION['importacao_temporaria']['imagens'][] = [
        'parte_planta' => $parte,
        'caminho'      => $caminho_relativo,
        'imagem_id'    => $imagem_id,
        'principal'    => $principal,
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
