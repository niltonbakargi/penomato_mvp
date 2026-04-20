<?php
// ============================================================
// LIMPAR IMPORTAÇÃO — apaga imagens do banco e do disco
// para a espécie da sessão ativa, permitindo recomeçar.
// Também apaga o artigo rascunho gerado anteriormente.
// ============================================================
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado.']);
    exit;
}

$temp_id = trim($_POST['temp_id'] ?? '');

// Debug — remove após confirmar funcionamento
if (empty($temp_id)) {
    echo json_encode(['sucesso' => false, 'erro' => 'temp_id vazio.']); exit;
}
if (!isset($_SESSION['importacao_temporaria'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão importacao_temporaria não existe.']); exit;
}
if ($_SESSION['importacao_temporaria']['temp_id'] !== $temp_id) {
    echo json_encode(['sucesso' => false, 'erro' => 'temp_id não bate: sessão=' . $_SESSION['importacao_temporaria']['temp_id'] . ' post=' . $temp_id]); exit;
}
if ($_SESSION['importacao_temporaria']['usuario_id'] != $_SESSION['usuario_id']) {
    echo json_encode(['sucesso' => false, 'erro' => 'usuario_id não bate: sessão_imp=' . $_SESSION['importacao_temporaria']['usuario_id'] . ' sessão=' . $_SESSION['usuario_id']]); exit;
}

$especie_id = (int) $_SESSION['importacao_temporaria']['especie_id'];
$usuario_id = (int) $_SESSION['usuario_id'];

// Busca caminhos antes de deletar para apagar do disco
$stmt = $pdo->prepare(
    "SELECT caminho_imagem FROM especies_imagens
     WHERE especie_id = ? AND id_usuario_identificador = ?"
);
$stmt->execute([$especie_id, $usuario_id]);
$rows = $stmt->fetchAll();

// Apaga arquivos físicos com segurança (só dentro da raiz do projeto)
$raiz = realpath(__DIR__ . '/../../');
foreach ($rows as $row) {
    $caminho = realpath($raiz . '/' . $row['caminho_imagem']);
    if ($caminho && str_starts_with($caminho, $raiz) && file_exists($caminho)) {
        unlink($caminho);
    }
}

// Apaga registros de imagens
$stmt_del = $pdo->prepare(
    "DELETE FROM especies_imagens WHERE especie_id = ? AND id_usuario_identificador = ?"
);
$stmt_del->execute([$especie_id, $usuario_id]);
$deletados = $stmt_del->rowCount();

// Apaga artigo rascunho gerado anteriormente para esta espécie
$pdo->prepare("DELETE FROM artigos WHERE especie_id = ? AND status = 'rascunho'")->execute([$especie_id]);

// Apaga características salvas (permite reimportar dados também)
$pdo->prepare("DELETE FROM especies_caracteristicas WHERE especie_id = ?")->execute([$especie_id]);

// Reverte status da espécie para sem_dados
$pdo->prepare("UPDATE especies_administrativo SET status = 'sem_dados' WHERE id = ?")->execute([$especie_id]);

// Reinicia sessão temporária mantendo especie_id e usuario_id
$_SESSION['importacao_temporaria']['imagens'] = [];
unset($_SESSION['importacao_temporaria']['dados']);

echo json_encode([
    'sucesso'   => true,
    'deletados' => $deletados,
    'arquivos'  => count($rows),
]);
