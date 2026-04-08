<?php
// ================================================
// FINALIZAR UPLOAD - VERSÃO LEGADO
// ================================================
// ATENÇÃO: Este arquivo é mantido para compatibilidade
// com o sistema antigo. O novo sistema usa 
// finalizar_upload_temporario.php com transação.
// ================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../../config/app.php';
$servidor   = DB_HOST;
$usuario_db = DB_USER;
$senha_db   = DB_PASS;
$banco      = DB_NAME;

// ================================================
// VERIFICAR SE USUÁRIO ESTÁ LOGADO
// ================================================
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../Views/auth/login.php?erro=" . urlencode("Faça login para finalizar."));
    exit;
}
$id_usuario = $_SESSION['usuario_id'];

// ================================================
// VERIFICAR ID DA ESPÉCIE
// ================================================
$especie_id = isset($_GET['especie_id']) ? (int)$_GET['especie_id'] : 0;

if ($especie_id <= 0) {
    header("Location: ../Views/upload_imagens_internet.php?erro=" . urlencode("ID da espécie não informado."));
    exit;
}

// ================================================
// CONECTAR AO BANCO
// ================================================
$conexao = new mysqli($servidor, $usuario_db, $senha_db, $banco);

if ($conexao->connect_error) {
    header("Location: ../Views/upload_imagens_internet.php?especie_id=" . $especie_id . "&erro=" . urlencode("Erro de conexão com banco."));
    exit;
}

$conexao->set_charset("utf8mb4");

// ================================================
// VERIFICAR SE TODAS AS PARTES OBRIGATÓRIAS TÊM IMAGENS
// ================================================
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

// ================================================
// REDIRECIONAMENTO BASEADO NO RESULTADO
// ================================================
if (count($faltam) > 0) {
    // Ainda faltam imagens
    $mensagem = "Ainda faltam imagens para: " . implode(", ", $faltam);
    header("Location: ../Views/upload_imagens_internet.php?especie_id=" . $especie_id . "&erro=" . urlencode($mensagem));
    exit;
}

// ================================================
// SUCESSO - TUDO COMPLETO
// ================================================
// NOTA: Este arquivo apenas verifica e redireciona.
// O salvamento real dos dados já deve ter ocorrido
// em etapas anteriores (upload individual).

// Redirecionar para página de sucesso
header("Location: ../Views/sucesso_importacao.php?especie_id=" . $especie_id . "&imagens=" . count($faltam));
exit;