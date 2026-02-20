<?php
// ================================================
// FINALIZAR UPLOAD E IR PARA REVISÃO
// ================================================

session_start();

$servidor = "127.0.0.1";
$usuario_db = "root";
$senha_db = "";
$banco = "penomato";

$especie_id = isset($_GET['especie_id']) ? (int)$_GET['especie_id'] : 0;

if ($especie_id <= 0) {
    header("Location: upload_imagens_internet.php?erro=" . urlencode("ID da espécie não informado."));
    exit;
}

$conexao = new mysqli($servidor, $usuario_db, $senha_db, $banco);

if ($conexao->connect_error) {
    header("Location: upload_imagens_internet.php?especie_id=" . $especie_id . "&erro=" . urlencode("Erro de conexão"));
    exit;
}

// Verificar se todas as partes obrigatórias têm imagens
$partes_obrigatorias = ['folha', 'flor', 'fruto', 'caule', 'habito'];
$faltam = [];

foreach ($partes_obrigatorias as $parte) {
    $sql = "SELECT COUNT(*) as total FROM especies_imagens 
            WHERE especie_id = ? AND parte_planta = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("is", $especie_id, $parte);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $row = $resultado->fetch_assoc();
    
    if ($row['total'] == 0) {
        $faltam[] = $parte;
    }
    $stmt->close();
}

$conexao->close();

if (count($faltam) > 0) {
    $mensagem = "Ainda faltam imagens para: " . implode(", ", $faltam);
    header("Location: upload_imagens_internet.php?especie_id=" . $especie_id . "&erro=" . urlencode($mensagem));
    exit;
}

// Se tudo OK, redireciona para a página de revisão (a criar)
header("Location: revisar_especie.php?especie_id=" . $especie_id);
exit;