<?php
// ============================================================
// PROCESSAR UPLOAD DE IMAGENS DA INTERNET
// ============================================================
session_start();
ob_start();

require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../Views/auth/login.php?erro=' . urlencode('Faça login para enviar imagens.'));
    exit;
}

$usuario_id = (int)$_SESSION['usuario_id'];

// ── Configurações ─────────────────────────────────────────────────────────────
$tamanho_maximo     = 10 * 1024 * 1024; // 10 MB
$formatos_permitidos = ['image/jpeg', 'image/png', 'image/jpg'];
$extensoes_permitidas = ['jpg', 'jpeg', 'png'];

// ── Validações de entrada ─────────────────────────────────────────────────────
$especie_id  = isset($_POST['especie_id'])  ? (int)$_POST['especie_id']  : 0;
$parte_planta = trim($_POST['parte_planta'] ?? '');

$redirect_base = '../Views/adicionar_imagens_internet.php?especie_id=' . $especie_id;

if ($especie_id <= 0) {
    header('Location: ../Views/adicionar_imagens_internet.php?erro=' . urlencode('Espécie não informada.'));
    exit;
}
if (empty($parte_planta)) {
    header('Location: ' . $redirect_base . '&erro=' . urlencode('Parte da planta não informada.'));
    exit;
}
if (!isset($_FILES['imagens']) || empty($_FILES['imagens']['name'][0])) {
    header('Location: ' . $redirect_base . '&parte=' . urlencode($parte_planta) . '&erro=' . urlencode('Nenhuma imagem selecionada.'));
    exit;
}

// ── Metadados ─────────────────────────────────────────────────────────────────
$fonte_nome   = trim($_POST['fonte_nome']   ?? '');
$fonte_url    = trim($_POST['fonte_url']    ?? '') ?: null;
$autor_imagem = trim($_POST['autor_imagem'] ?? '') ?: null;
$licenca      = trim($_POST['licenca']      ?? '') ?: null;
$descricao    = trim($_POST['descricao']    ?? '') ?: null;

if (empty($fonte_nome)) {
    header('Location: ' . $redirect_base . '&parte=' . urlencode($parte_planta) . '&erro=' . urlencode('Informe a fonte da imagem.'));
    exit;
}

// ── Pasta de upload ───────────────────────────────────────────────────────────
$pasta_especie = dirname(dirname(__DIR__)) . '/uploads/especies/' . $especie_id . '/';
if (!file_exists($pasta_especie)) {
    mkdir($pasta_especie, 0755, true);
}

// ── Processar arquivos ────────────────────────────────────────────────────────
$sucessos = 0;
$erros    = [];
$total    = count($_FILES['imagens']['name']);

for ($i = 0; $i < $total; $i++) {

    if ($_FILES['imagens']['error'][$i] !== UPLOAD_ERR_OK) {
        $erros[] = 'Erro no upload do arquivo ' . ($i + 1) . '.';
        continue;
    }

    $tmp      = $_FILES['imagens']['tmp_name'][$i];
    $tamanho  = $_FILES['imagens']['size'][$i];
    $nome_orig = $_FILES['imagens']['name'][$i];

    if ($tamanho > $tamanho_maximo) {
        $erros[] = htmlspecialchars($nome_orig) . ': arquivo muito grande (máx 10 MB).';
        continue;
    }

    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mime     = finfo_file($finfo, $tmp);
    finfo_close($finfo);

    if (!in_array($mime, $formatos_permitidos)) {
        $erros[] = htmlspecialchars($nome_orig) . ': formato não permitido (use JPG ou PNG).';
        continue;
    }

    $ext = strtolower(pathinfo($nome_orig, PATHINFO_EXTENSION));
    if (!in_array($ext, $extensoes_permitidas)) {
        $erros[] = htmlspecialchars($nome_orig) . ': extensão não permitida.';
        continue;
    }

    // Gerar nome único
    $nome_arquivo = $especie_id . '_' . $parte_planta . '_' . date('Ymd_His') . '_' . rand(100, 999) . '.' . $ext;
    $caminho_abs  = $pasta_especie . $nome_arquivo;
    $caminho_rel  = 'uploads/especies/' . $especie_id . '/' . $nome_arquivo;

    // Redimensionar e salvar (mantém proporção, máx 1920px)
    $info = getimagesize($tmp);
    if (!$info) {
        $erros[] = htmlspecialchars($nome_orig) . ': não é uma imagem válida.';
        continue;
    }

    if ($info[0] <= 1920 && $info[1] <= 1920) {
        $salvo = copy($tmp, $caminho_abs);
    } else {
        $salvo = _redimensionar($tmp, $caminho_abs, $info, $mime);
    }

    if (!$salvo) {
        $erros[] = htmlspecialchars($nome_orig) . ': erro ao salvar o arquivo.';
        continue;
    }

    // Inserir no banco
    $stmt = $pdo->prepare("
        INSERT INTO especies_imagens (
            especie_id, parte_planta, caminho_imagem, nome_original,
            tamanho_bytes, mime_type, fonte_nome, fonte_url,
            autor_imagem, licenca, descricao,
            id_usuario_identificador, origem, status_validacao, data_upload
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'internet', 'pendente', NOW())
    ");

    if ($stmt->execute([
        $especie_id, $parte_planta, $caminho_rel, $nome_orig,
        $tamanho, $mime, $fonte_nome, $fonte_url,
        $autor_imagem, $licenca, $descricao,
        $usuario_id,
    ])) {
        $sucessos++;
    } else {
        $erros[] = htmlspecialchars($nome_orig) . ': erro ao salvar no banco.';
        if (file_exists($caminho_abs)) unlink($caminho_abs);
    }
}

// ── Redirecionar ──────────────────────────────────────────────────────────────
$parte_param = '&parte=' . urlencode($parte_planta);

if ($sucessos > 0) {
    $msg = $sucessos . ' imagem' . ($sucessos > 1 ? 'ns' : '') . ' de ' . $parte_planta . ' adicionada' . ($sucessos > 1 ? 's' : '') . ' com sucesso.';
    if ($erros) $msg .= ' Avisos: ' . implode(' | ', $erros);
    header('Location: ' . $redirect_base . $parte_param . '&sucesso=' . urlencode($msg));
} else {
    $msg = 'Nenhuma imagem foi enviada. ' . implode(' | ', $erros);
    header('Location: ' . $redirect_base . $parte_param . '&erro=' . urlencode($msg));
}

ob_end_flush();
exit;

// ── Função auxiliar ───────────────────────────────────────────────────────────
function _redimensionar($origem, $destino, $info, $mime) {
    $max = 1920;
    $ratio = $info[0] / $info[1];
    if ($ratio > 1) { $w = $max; $h = (int)($max / $ratio); }
    else            { $h = $max; $w = (int)($max * $ratio); }

    $src = $mime === 'image/png' ? imagecreatefrompng($origem) : imagecreatefromjpeg($origem);
    if (!$src) return false;

    $dst = imagecreatetruecolor($w, $h);
    if ($mime === 'image/png') {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $info[0], $info[1]);

    $ok = $mime === 'image/png' ? imagepng($dst, $destino, 9) : imagejpeg($dst, $destino, 90);
    imagedestroy($src);
    imagedestroy($dst);
    return $ok;
}
