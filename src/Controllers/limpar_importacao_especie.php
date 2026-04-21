<?php
// ============================================================
// LIMPAR IMPORTAÇÃO — apaga imagens do banco e do disco
// para a espécie informada, permitindo recomeçar do zero.
// Recebe especie_id via POST e redireciona para escolher_especie.
// ============================================================
session_start();

require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../Views/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../Views/escolher_especie.php');
    exit;
}

$especie_id = (int) ($_POST['especie_id'] ?? 0);

// Fallback: tentar pegar da sessão temporária
if ($especie_id <= 0 && isset($_SESSION['importacao_temporaria']['especie_id'])) {
    $especie_id = (int) $_SESSION['importacao_temporaria']['especie_id'];
}

if ($especie_id <= 0) {
    $_SESSION['erro_importacao'] = 'Espécie inválida para limpar.';
    header('Location: ../Views/escolher_especie.php');
    exit;
}

// Apagar arquivos físicos com segurança (só dentro da raiz do projeto)
$raiz = realpath(__DIR__ . '/../../');
$stmt = $pdo->prepare("SELECT caminho_imagem FROM especies_imagens WHERE especie_id = ?");
$stmt->execute([$especie_id]);
foreach ($stmt->fetchAll() as $row) {
    $caminho = realpath($raiz . '/' . $row['caminho_imagem']);
    if ($caminho && str_starts_with($caminho, $raiz) && file_exists($caminho)) {
        unlink($caminho);
    }
}

// Apagar registros do banco
$pdo->prepare("DELETE FROM especies_imagens WHERE especie_id = ?")->execute([$especie_id]);
$pdo->prepare("DELETE FROM especies_caracteristicas WHERE especie_id = ?")->execute([$especie_id]);
$pdo->prepare("DELETE FROM artigos WHERE especie_id = ? AND status = 'rascunho'")->execute([$especie_id]);
$pdo->prepare("UPDATE especies_administrativo SET status = 'sem_dados', atribuido_a = NULL WHERE id = ?")->execute([$especie_id]);

// Limpar sessão temporária
unset($_SESSION['importacao_temporaria']);

// Voltar para escolher espécie para recomeçar do zero
header('Location: ../Views/escolher_especie.php');
exit;
