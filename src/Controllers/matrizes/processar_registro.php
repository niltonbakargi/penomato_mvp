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

$tipos_permitidos = ['image/jpeg', 'image/png', 'image/webp'];
$partes_opcionais = ['folha', 'flor', 'fruto', 'caule', 'semente'];

// ── Valida GPS ────────────────────────────────────────────────
$lat = $_POST['latitude']  ?? '';
$lon = $_POST['longitude'] ?? '';

if (empty($lat) || empty($lon)) {
    mensagemErro('As coordenadas GPS são obrigatórias.');
    header('Location: /penomato_mvp/src/Views/matrizes/registrar.php');
    exit;
}

// ── Valida foto geral ─────────────────────────────────────────
if (!isset($_FILES['foto_geral']) || $_FILES['foto_geral']['error'] !== UPLOAD_ERR_OK) {
    mensagemErro('A foto da árvore inteira é obrigatória.');
    header('Location: /penomato_mvp/src/Views/matrizes/registrar.php');
    exit;
}

// ── Função auxiliar: salva uma foto ──────────────────────────
function salvarFoto(array $arquivo, string $dir): string|false {
    global $tipos_permitidos;

    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $tipo     = finfo_file($finfo, $arquivo['tmp_name']);
    finfo_close($finfo);

    if (!in_array($tipo, $tipos_permitidos))   return false;
    if ($arquivo['size'] > 10 * 1024 * 1024)  return false;

    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $ext      = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION)) ?: 'jpg';
    $nome     = uniqid('mt_') . '.' . $ext;
    $destino  = $dir . $nome;

    return move_uploaded_file($arquivo['tmp_name'], $destino) ? $nome : false;
}

// ── Salva foto geral ──────────────────────────────────────────
$dir_geral = __DIR__ . '/../../../uploads/matrizes/';
$nome_geral = salvarFoto($_FILES['foto_geral'], $dir_geral);

if (!$nome_geral) {
    mensagemErro('Erro ao salvar a foto. Verifique o formato (JPG/PNG) e tamanho (máx. 10 MB).');
    header('Location: /penomato_mvp/src/Views/matrizes/registrar.php');
    exit;
}

// ── Gera código único ─────────────────────────────────────────
function gerarCodigoMatriz(PDO $pdo): string {
    do {
        $letras = strtoupper(chr(rand(65,90)) . chr(rand(65,90)));
        $stmt   = $pdo->query("SELECT MAX(CAST(SUBSTRING(codigo, 3) AS UNSIGNED)) AS ultimo FROM matrizes");
        $seq    = (($stmt->fetch())['ultimo'] ?? 0) + 1;
        $codigo = $letras . str_pad($seq, 3, '0', STR_PAD_LEFT);
        $chk    = $pdo->prepare("SELECT id FROM matrizes WHERE codigo = ?");
        $chk->execute([$codigo]);
    } while ($chk->rowCount() > 0);
    return $codigo;
}

$especie_nome         = trim($_POST['especie_nome']         ?? '');
$especie_nome_popular = trim($_POST['especie_nome_popular'] ?? '');
$observacoes          = trim($_POST['observacoes']          ?? '');
$usuario_id           = $_SESSION['usuario_id'];
$codigo               = gerarCodigoMatriz($pdo);

// ── Insere matriz ─────────────────────────────────────────────
$id = inserir('matrizes', [
    'codigo'               => $codigo,
    'especie_nome'         => $especie_nome         ?: null,
    'especie_nome_popular' => $especie_nome_popular ?: null,
    'latitude'             => $lat,
    'longitude'            => $lon,
    'foto_geral'           => 'uploads/matrizes/' . $nome_geral,
    'observacoes'          => $observacoes          ?: null,
    'cadastrado_por'       => $usuario_id,
]);

if (!$id) {
    mensagemErro('Erro ao registrar a matriz. Tente novamente.');
    header('Location: /penomato_mvp/src/Views/matrizes/registrar.php');
    exit;
}

// ── Salva fotos de partes opcionais ───────────────────────────
$dir_partes = __DIR__ . '/../../../uploads/matrizes/partes/';

foreach ($partes_opcionais as $parte) {
    if (!isset($_FILES["foto_{$parte}"]) || $_FILES["foto_{$parte}"]['error'] !== UPLOAD_ERR_OK) {
        continue;
    }

    $nome_parte = salvarFoto($_FILES["foto_{$parte}"], $dir_partes);
    if (!$nome_parte) continue;

    inserir('matrizes_fotos', [
        'matriz_id'    => $id,
        'parte'        => $parte,
        'caminho_foto' => 'uploads/matrizes/partes/' . $nome_parte,
        'enviada_por'  => $usuario_id,
    ]);
}

mensagemSucesso("Matriz <strong>{$codigo}</strong> registrada com sucesso!");
header("Location: /penomato_mvp/src/Views/matrizes/ficha.php?id={$id}");
exit;
