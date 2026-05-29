<?php
// ============================================================
// PROCESSAR REGISTRO DE MATRIZ
// ============================================================

require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../auth/verificar_acesso.php';

if (!estaLogado()) {
    header('Location: /penomato_mvp/src/Views/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /penomato_mvp/src/Views/matrizes/registrar.php');
    exit;
}

// ── Validação básica ──────────────────────────────────────────
$lat = $_POST['latitude']  ?? '';
$lon = $_POST['longitude'] ?? '';

if (empty($lat) || empty($lon)) {
    setMensagem('erro', 'As coordenadas GPS são obrigatórias.');
    header('Location: /penomato_mvp/src/Views/matrizes/registrar.php');
    exit;
}

if (!isset($_FILES['foto_geral']) || $_FILES['foto_geral']['error'] !== UPLOAD_ERR_OK) {
    setMensagem('erro', 'A foto da árvore é obrigatória.');
    header('Location: /penomato_mvp/src/Views/matrizes/registrar.php');
    exit;
}

// ── Valida imagem ────────────────────────────────────────────
$foto = $_FILES['foto_geral'];
$tipos_permitidos = ['image/jpeg', 'image/png', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$tipo_real = finfo_file($finfo, $foto['tmp_name']);
finfo_close($finfo);

if (!in_array($tipo_real, $tipos_permitidos)) {
    setMensagem('erro', 'Formato de imagem inválido. Use JPG ou PNG.');
    header('Location: /penomato_mvp/src/Views/matrizes/registrar.php');
    exit;
}

if ($foto['size'] > 10 * 1024 * 1024) {
    setMensagem('erro', 'A foto deve ter no máximo 10 MB.');
    header('Location: /penomato_mvp/src/Views/matrizes/registrar.php');
    exit;
}

// ── Gera código único (MT + 3 dígitos) ───────────────────────
function gerarCodigoMatriz(PDO $pdo): string {
    do {
        $letras = strtoupper(chr(rand(65,90)) . chr(rand(65,90)));
        $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(codigo, 3) AS UNSIGNED)) AS ultimo FROM matrizes");
        $row = $stmt->fetch();
        $seq = ($row['ultimo'] ?? 0) + 1;
        $codigo = $letras . str_pad($seq, 3, '0', STR_PAD_LEFT);
        $existe = $pdo->prepare("SELECT id FROM matrizes WHERE codigo = ?");
        $existe->execute([$codigo]);
    } while ($existe->rowCount() > 0);
    return $codigo;
}

// ── Salva foto ────────────────────────────────────────────────
$dir_upload = __DIR__ . '/../../../uploads/matrizes/';
if (!is_dir($dir_upload)) {
    mkdir($dir_upload, 0755, true);
}

$ext = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION)) ?: 'jpg';
$nome_arquivo = uniqid('mt_') . '.' . $ext;
$caminho_completo = $dir_upload . $nome_arquivo;

if (!move_uploaded_file($foto['tmp_name'], $caminho_completo)) {
    setMensagem('erro', 'Erro ao salvar a foto. Tente novamente.');
    header('Location: /penomato_mvp/src/Views/matrizes/registrar.php');
    exit;
}

// ── Monta dados ───────────────────────────────────────────────
$especie_nome         = trim($_POST['especie_nome']         ?? '');
$especie_nome_popular = trim($_POST['especie_nome_popular'] ?? '');
$observacoes          = trim($_POST['observacoes']          ?? '');
$usuario_id           = $_SESSION['usuario_id'];

$codigo = gerarCodigoMatriz($pdo);

// ── Insere no banco ───────────────────────────────────────────
$id = inserir('matrizes', [
    'codigo'               => $codigo,
    'especie_nome'         => $especie_nome         ?: null,
    'especie_nome_popular' => $especie_nome_popular ?: null,
    'latitude'             => $lat,
    'longitude'            => $lon,
    'foto_geral'           => 'uploads/matrizes/' . $nome_arquivo,
    'observacoes'          => $observacoes          ?: null,
    'cadastrado_por'       => $usuario_id,
]);

if (!$id) {
    setMensagem('erro', 'Erro ao registrar a matriz. Tente novamente.');
    header('Location: /penomato_mvp/src/Views/matrizes/registrar.php');
    exit;
}

setMensagem('sucesso', "Matriz <strong>{$codigo}</strong> registrada com sucesso!");
header("Location: /penomato_mvp/src/Views/matrizes/ficha.php?id={$id}");
exit;
