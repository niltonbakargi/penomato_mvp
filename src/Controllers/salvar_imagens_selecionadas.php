<?php
// ============================================================
// SALVAR IMAGENS SELECIONADAS
// Chamado via AJAX POST após o colaborador confirmar no carrossel.
// Baixa cada imagem aprovada, salva em disco e insere em especies_imagens.
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
$temp_id       = trim($_POST['temp_id']        ?? '');
$ids_json      = trim($_POST['candidatos_ids'] ?? '[]');
$principal_id  = (int)($_POST['principal_id']  ?? 0);

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

// Decodificar IDs
$ids = json_decode($ids_json, true);
if (!is_array($ids) || empty($ids)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Nenhuma imagem selecionada']);
    exit;
}
$ids = array_map('intval', array_filter($ids));

// ============================================================
// BUSCAR CANDIDATAS NO BANCO
// ============================================================
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare(
    "SELECT * FROM temp_imagens_candidatas
     WHERE id IN ($placeholders)
       AND temp_id  = ?
       AND status   = 'pendente'"
);
$stmt->execute([...$ids, $temp_id]);
$candidatas = $stmt->fetchAll();

if (empty($candidatas)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Candidatas não encontradas ou já processadas']);
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
// EXTENSÃO A PARTIR DO MIME TYPE
// ============================================================
$mime_para_ext = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
];

// ============================================================
// PROCESSAR CADA CANDIDATA APROVADA
// ============================================================
$salvas = 0;
$erros  = [];

foreach ($candidatas as $cand) {

    // --- Baixar imagem ---
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $cand['url_foto'],
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
        $erros[] = "Falha ao baixar imagem da candidata ID {$cand['id']} (HTTP $http_code)";
        error_log("[Penomato] salvar_imagens: falha download ID {$cand['id']} — HTTP $http_code — {$cand['url_foto']}");
        continue;
    }

    // --- Determinar extensão ---
    $mime_base = strtolower(trim(explode(';', $mime_real)[0]));
    $ext = $mime_para_ext[$mime_base]
        ?? strtolower(pathinfo(parse_url($cand['url_foto'], PHP_URL_PATH), PATHINFO_EXTENSION))
        ?: 'jpg';

    // --- Gerar nome de arquivo ---
    $nome_arquivo     = $cand['parte_planta'] . '_' . date('Ymd') . '_' . date('His') . '_' . rand(100, 999) . '.' . $ext;
    $caminho_completo = $dir_upload   . $nome_arquivo;
    $caminho_relativo = $dir_relativo . $nome_arquivo;

    // --- Salvar em disco ---
    if (file_put_contents($caminho_completo, $conteudo) === false) {
        $erros[] = "Falha ao gravar arquivo: $nome_arquivo";
        error_log("[Penomato] salvar_imagens: falha ao gravar $caminho_completo");
        continue;
    }

    $eh_principal = ($cand['id'] == $principal_id) ? 1 : 0;

    // --- Inserir em especies_imagens ---
    $stmt_insert = $pdo->prepare("
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

    $stmt_insert->execute([
        $especie_id,
        $cand['parte_planta'],
        $caminho_relativo,
        $nome_arquivo,
        strlen($conteudo),
        'image/' . $ext,
        $cand['fonte_nome'],
        $cand['fonte_url'],
        $cand['autor'],
        $cand['licenca'],
        $eh_principal,
        $cand['local_coleta'],
        $cand['data_observacao'],
        $usuario_id,
    ]);

    $imagem_id = (int)$pdo->lastInsertId();

    // --- Marcar candidata como aprovada ---
    $pdo->prepare("UPDATE temp_imagens_candidatas SET status = 'aprovado' WHERE id = ?")
        ->execute([$cand['id']]);

    // --- Atualizar sessão ---
    $_SESSION['importacao_temporaria']['imagens'][] = [
        'parte_planta' => $cand['parte_planta'],
        'caminho'      => $caminho_relativo,
        'imagem_id'    => $imagem_id,
        'principal'    => $eh_principal,
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
