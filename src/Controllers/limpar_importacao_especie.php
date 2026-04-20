<?php
// ============================================================
// LIMPAR IMPORTAÇÃO — apaga imagens do banco e do disco
// para a espécie da sessão ativa, permitindo recomeçar.
// Também apaga o artigo rascunho gerado anteriormente.
// ============================================================
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/app.php';

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

$conexao = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conexao->connect_error) {
    echo json_encode(['sucesso' => false, 'erro' => 'Erro ao conectar ao banco.']);
    exit;
}
$conexao->set_charset('utf8mb4');

// Busca caminhos antes de deletar para apagar do disco
// coluna correta: id_usuario_identificador
$stmt = $conexao->prepare(
    "SELECT caminho_imagem FROM especies_imagens
     WHERE especie_id = ? AND id_usuario_identificador = ?"
);
$stmt->bind_param('ii', $especie_id, $usuario_id);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Apaga arquivos físicos com segurança (só dentro da raiz do projeto)
$raiz      = realpath(__DIR__ . '/../../');
foreach ($rows as $row) {
    $caminho = realpath($raiz . '/' . $row['caminho_imagem']);
    if ($caminho && str_starts_with($caminho, $raiz) && file_exists($caminho)) {
        unlink($caminho);
    }
}

// Apaga registros de imagens
$stmt_del = $conexao->prepare(
    "DELETE FROM especies_imagens WHERE especie_id = ? AND id_usuario_identificador = ?"
);
$stmt_del->bind_param('ii', $especie_id, $usuario_id);
$stmt_del->execute();
$deletados = $stmt_del->affected_rows;
$stmt_del->close();

// Apaga artigo rascunho gerado anteriormente para esta espécie
$stmt_art = $conexao->prepare(
    "DELETE FROM artigos WHERE especie_id = ? AND status = 'rascunho'"
);
$stmt_art->bind_param('i', $especie_id);
$stmt_art->execute();
$stmt_art->close();

// Apaga características salvas (permite reimportar dados também)
$stmt_car = $conexao->prepare(
    "DELETE FROM especies_caracteristicas WHERE especie_id = ?"
);
$stmt_car->bind_param('i', $especie_id);
$stmt_car->execute();
$stmt_car->close();

// Reverte status da espécie para sem_dados
$stmt_status = $conexao->prepare(
    "UPDATE especies_administrativo SET status = 'sem_dados' WHERE id = ?"
);
$stmt_status->bind_param('i', $especie_id);
$stmt_status->execute();
$stmt_status->close();

$conexao->close();

// Reinicia sessão temporária mantendo especie_id e usuario_id
$_SESSION['importacao_temporaria']['imagens'] = [];
unset($_SESSION['importacao_temporaria']['dados']);

echo json_encode([
    'sucesso'   => true,
    'deletados' => $deletados,
    'arquivos'  => count($rows),
]);
