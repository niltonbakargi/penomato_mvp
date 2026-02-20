<?php
// ================================================
// FINALIZAR UPLOAD TEMPORÁRIO - SALVAR TUDO NO BANCO
// ================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
ob_start();

$servidor = "127.0.0.1";
$usuario_db = "root";
$senha_db = "";
$banco = "penomato";

// ================================================
// VERIFICAR SESSÃO TEMPORÁRIA
// ================================================
$temp_id = isset($_GET['temp_id']) ? $_GET['temp_id'] : '';

if (empty($temp_id) || !isset($_SESSION['importacao_temporaria']) || $_SESSION['importacao_temporaria']['temp_id'] !== $temp_id) {
    header("Location: ../Views/upload_imagens_internet.php?erro=" . urlencode("Sessão temporária inválida ou expirada."));
    exit;
}

$dados_temporarios = $_SESSION['importacao_temporaria'];
$especie_id = $dados_temporarios['especie_id'];
$dados_caracteristicas = $dados_temporarios['dados'];
$imagens_temporarias = $dados_temporarios['imagens'];

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = 1; // Temporário
}
$id_usuario = $_SESSION['usuario_id'];

// ================================================
// VERIFICAR PARTES OBRIGATÓRIAS
// ================================================
$partes_obrigatorias = ['folha', 'flor', 'fruto', 'caule', 'habito'];
$partes_presentes = [];

foreach ($imagens_temporarias as $img) {
    $partes_presentes[$img['parte_planta']] = true;
}

$faltam = [];
foreach ($partes_obrigatorias as $parte) {
    if (!isset($partes_presentes[$parte])) {
        $faltam[] = $parte;
    }
}

if (count($faltam) > 0) {
    $mensagem = "Faltam imagens para as partes: " . implode(", ", $faltam);
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode($mensagem));
    exit;
}

// ================================================
// CONECTAR AO BANCO
// ================================================
$conexao = new mysqli($servidor, $usuario_db, $senha_db, $banco);

if ($conexao->connect_error) {
    die("Erro de conexão: " . $conexao->connect_error);
}

$conexao->set_charset("utf8mb4");

// ================================================
// INICIAR TRANSAÇÃO
// ================================================
$conexao->begin_transaction();

try {
    
    // ================================================
    // 1. SALVAR DADOS DAS CARACTERÍSTICAS
    // ================================================
    
    // Verificar se já existe registro
    $sql_check = "SELECT id FROM especies_caracteristicas WHERE especie_id = ?";
    $stmt_check = $conexao->prepare($sql_check);
    $stmt_check->bind_param("i", $especie_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    $ja_existe = $stmt_check->num_rows > 0;
    $stmt_check->close();
    
    if ($ja_existe) {
        // UPDATE
        $sql = "UPDATE especies_caracteristicas SET ";
        $sets = [];
        $tipos = "";
        $valores = [];
        
        foreach ($dados_caracteristicas as $campo => $valor) {
            if ($campo != 'especie_id') {
                $sets[] = "$campo = ?";
                $valores[] = $valor;
                $tipos .= "s";
            }
        }
        
        $sql .= implode(", ", $sets);
        $sql .= " WHERE especie_id = ?";
        $valores[] = $especie_id;
        $tipos .= "i";
        
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param($tipos, ...$valores);
        $stmt->execute();
        $stmt->close();
        
    } else {
        // INSERT
        $colunas = implode(", ", array_keys($dados_caracteristicas));
        $placeholders = implode(", ", array_fill(0, count($dados_caracteristicas), "?"));
        $sql = "INSERT INTO especies_caracteristicas ($colunas) VALUES ($placeholders)";
        
        $tipos = str_repeat("s", count($dados_caracteristicas));
        $valores = array_values($dados_caracteristicas);
        
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param($tipos, ...$valores);
        $stmt->execute();
        $stmt->close();
    }
    
    // ================================================
    // 2. ATUALIZAR STATUS DA ESPÉCIE
    // ================================================
    $sql_status = "UPDATE especies_administrativo SET status = 'dados_internet' WHERE id = ?";
    $stmt_status = $conexao->prepare($sql_status);
    $stmt_status->bind_param("i", $especie_id);
    $stmt_status->execute();
    $stmt_status->close();
    
    // ================================================
    // 3. SALVAR IMAGENS (MOVER DA PASTA TEMP PARA DEFINITIVA)
    // ================================================
    $pasta_temp = dirname(dirname(__DIR__)) . '/uploads/temp/' . $temp_id . '/';
    $pasta_definitiva = dirname(dirname(__DIR__)) . '/uploads/especies/' . $especie_id . '/';
    
    if (!file_exists($pasta_definitiva)) {
        mkdir($pasta_definitiva, 0777, true);
    }
    
    $imagens_salvas = 0;
    
    foreach ($imagens_temporarias as $img) {
        
        $caminho_temp = dirname(dirname(__DIR__)) . '/' . $img['caminho_temporario'];
        $nome_arquivo = basename($caminho_temp);
        $caminho_definitivo = $pasta_definitiva . $nome_arquivo;
        $caminho_relativo = 'uploads/especies/' . $especie_id . '/' . $nome_arquivo;
        
        // Mover arquivo
        if (rename($caminho_temp, $caminho_definitivo)) {
            
            // Inserir no banco
            $sql_insert = "INSERT INTO especies_imagens (
                especie_id, parte_planta, caminho_imagem, nome_original,
                tamanho_bytes, mime_type, fonte_nome, fonte_url,
                autor_imagem, licenca, descricao, id_usuario_identificador,
                status_validacao, data_upload
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendente', NOW())";
            
            $stmt = $conexao->prepare($sql_insert);
            $stmt->bind_param(
                "isssissssssi",
                $especie_id,
                $img['parte_planta'],
                $caminho_relativo,
                $img['nome_original'],
                $img['tamanho_bytes'],
                $img['mime_type'],
                $img['fonte_nome'],
                $img['fonte_url'],
                $img['autor_imagem'],
                $img['licenca'],
                $img['descricao'],
                $id_usuario
            );
            $stmt->execute();
            $stmt->close();
            
            $imagens_salvas++;
        }
    }
    
    // ================================================
    // 4. ATUALIZAR STATUS_IMAGENS DA ESPÉCIE
    // ================================================
    $sql_status_imagens = "UPDATE especies_administrativo SET status_imagens = 'registrada' WHERE id = ?";
    $stmt_status_imagens = $conexao->prepare($sql_status_imagens);
    $stmt_status_imagens->bind_param("i", $especie_id);
    $stmt_status_imagens->execute();
    $stmt_status_imagens->close();
    
    // ================================================
    // COMMIT - TUDO OK
    // ================================================
    $conexao->commit();
    
    // ================================================
    // 5. LIMPAR DADOS TEMPORÁRIOS
    // ================================================
    
    // Remover pasta temporária
    $pasta_temp = dirname(dirname(__DIR__)) . '/uploads/temp/' . $temp_id . '/';
    if (file_exists($pasta_temp)) {
        // Função para remover diretório recursivamente
        function removerDiretorio($dir) {
            if (!file_exists($dir)) return true;
            if (!is_dir($dir)) return unlink($dir);
            foreach (scandir($dir) as $item) {
                if ($item == '.' || $item == '..') continue;
                if (!removerDiretorio($dir . DIRECTORY_SEPARATOR . $item)) return false;
            }
            return rmdir($dir);
        }
        removerDiretorio($pasta_temp);
    }
    
    // Remover da sessão
    unset($_SESSION['importacao_temporaria']);
    
    // ================================================
    // REDIRECIONAR PARA PÁGINA DE SUCESSO
    // ================================================
    header("Location: ../Views/sucesso_importacao.php?especie_id=" . $especie_id . "&imagens=" . $imagens_salvas);
    exit;
    
} catch (Exception $e) {
    // ================================================
    // ROLLBACK EM CASO DE ERRO
    // ================================================
    $conexao->rollback();
    
    $erro = "Erro ao salvar dados: " . $e->getMessage();
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode($erro));
    exit;
}

$conexao->close();
ob_end_flush();
exit;