<?php
// ============================================================
// PROCESSAR FOTO DE PARTE DA MATRIZ
// ============================================================

require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../auth/verificar_acesso.php';

if (!estaLogado()) {
    header('Location: /penomato_mvp/src/Views/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /penomato_mvp/src/Views/matrizes/mapa.php');
    exit;
}

$matriz_id = intval($_POST['matriz_id'] ?? 0);
$parte     = $_POST['parte'] ?? '';
$partes_validas = ['folha', 'flor', 'fruto', 'caule', 'semente'];

if (!$matriz_id || !in_array($parte, $partes_validas)) {
    setMensagem('erro', 'Dados inválidos.');
    header("Location: /penomato_mvp/src/Views/matrizes/adicionar_foto.php?id={$matriz_id}");
    exit;
}

if (!isset($_FILES['foto_parte']) || $_FILES['foto_parte']['error'] !== UPLOAD_ERR_OK) {
    setMensagem('erro', 'Erro no upload da foto.');
    header("Location: /penomato_mvp/src/Views/matrizes/adicionar_foto.php?id={$matriz_id}");
    exit;
}

$foto = $_FILES['foto_parte'];
$tipos_permitidos = ['image/jpeg', 'image/png', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$tipo_real = finfo_file($finfo, $foto['tmp_name']);
finfo_close($finfo);

if (!in_array($tipo_real, $tipos_permitidos)) {
    setMensagem('erro', 'Formato inválido. Use JPG ou PNG.');
    header("Location: /penomato_mvp/src/Views/matrizes/adicionar_foto.php?id={$matriz_id}");
    exit;
}

if ($foto['size'] > 10 * 1024 * 1024) {
    setMensagem('erro', 'A foto deve ter no máximo 10 MB.');
    header("Location: /penomato_mvp/src/Views/matrizes/adicionar_foto.php?id={$matriz_id}");
    exit;
}

$dir_upload = __DIR__ . '/../../../uploads/matrizes/partes/';
if (!is_dir($dir_upload)) {
    mkdir($dir_upload, 0755, true);
}

$ext = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION)) ?: 'jpg';
$nome_arquivo = uniqid("mt{$matriz_id}_{$parte}_") . '.' . $ext;

if (!move_uploaded_file($foto['tmp_name'], $dir_upload . $nome_arquivo)) {
    setMensagem('erro', 'Erro ao salvar a foto.');
    header("Location: /penomato_mvp/src/Views/matrizes/adicionar_foto.php?id={$matriz_id}");
    exit;
}

inserir('matrizes_fotos', [
    'matriz_id'    => $matriz_id,
    'parte'        => $parte,
    'caminho_foto' => 'uploads/matrizes/partes/' . $nome_arquivo,
    'enviada_por'  => $_SESSION['usuario_id'],
]);

setMensagem('sucesso', 'Foto de ' . ucfirst($parte) . ' adicionada.');
header("Location: /penomato_mvp/src/Views/matrizes/ficha.php?id={$matriz_id}");
exit;
