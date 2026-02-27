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
// VERIFICAR SE USUÁRIO ESTÁ LOGADO
// ================================================
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../Views/auth/login.php?erro=" . urlencode("Faça login para finalizar a importação."));
    exit;
}
$id_usuario = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';

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

// ================================================
// VERIFICAR SE O USUÁRIO DA SESSÃO É O MESMO DA IMPORTAÇÃO
// ================================================
if (!isset($dados_temporarios['usuario_id']) || $dados_temporarios['usuario_id'] != $id_usuario) {
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode("Você não tem permissão para finalizar esta importação."));
    exit;
}

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
// VERIFICAR SE A COLUNA status_imagens EXISTE
// ================================================
$check_column = $conexao->query("SHOW COLUMNS FROM especies_administrativo LIKE 'status_imagens'");
if ($check_column->num_rows == 0) {
    $conexao->query("ALTER TABLE especies_administrativo 
                     ADD COLUMN status_imagens ENUM('sem_imagens', 'internet', 'registrada') 
                     NOT NULL DEFAULT 'sem_imagens' 
                     COMMENT 'Status do conjunto de imagens da espécie'");
}

// ================================================
// INICIAR TRANSAÇÃO
// ================================================
$conexao->begin_transaction();

try {
    
    // ================================================
    // 1. SALVAR DADOS DAS CARACTERÍSTICAS
    // ================================================
    
    $sql_check = "SELECT id FROM especies_caracteristicas WHERE especie_id = ?";
    $stmt_check = $conexao->prepare($sql_check);
    $stmt_check->bind_param("i", $especie_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    $ja_existe = $stmt_check->num_rows > 0;
    $stmt_check->close();
    
    if ($ja_existe) {
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
    // 2. ATUALIZAR STATUS E AUTOR DA ESPÉCIE
    // ================================================
    $sql_status = "UPDATE especies_administrativo 
                   SET status = 'dados_internet',
                       autor_dados_internet_id = ?,
                       data_dados_internet = NOW()
                   WHERE id = ?";
    $stmt_status = $conexao->prepare($sql_status);
    $stmt_status->bind_param("ii", $id_usuario, $especie_id);
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
            
            // ================================================
            // INSERIR NA TABELA especies_imagens (CORRIGIDO)
            // ================================================
            $sql_insert = "INSERT INTO especies_imagens (
                especie_id,
                tipo_imagem,
                parte_planta,
                caminho_imagem,
                nome_original,
                tamanho_bytes,
                mime_type,
                fonte_nome,
                fonte_url,
                autor_imagem,
                licenca,
                descricao,
                id_usuario_identificador,
                status_validacao,
                data_upload
            ) VALUES (?, 'provisoria', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendente', NOW())";
            
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
            
            if ($stmt->execute()) {
                $imagens_salvas++;
                error_log("Imagem salva: ID " . $stmt->insert_id . " - " . $caminho_relativo);
            } else {
                error_log("Erro ao inserir imagem: " . $stmt->error);
            }
            $stmt->close();
        } else {
            error_log("Erro ao mover arquivo: " . $caminho_temp . " -> " . $caminho_definitivo);
        }
    }
    
    // ================================================
    // 4. ATUALIZAR STATUS_IMAGENS DA ESPÉCIE
    // ================================================
    if ($imagens_salvas > 0) {
        $sql_status_imagens = "UPDATE especies_administrativo SET status_imagens = 'registrada' WHERE id = ?";
        $stmt_status_imagens = $conexao->prepare($sql_status_imagens);
        $stmt_status_imagens->bind_param("i", $especie_id);
        $stmt_status_imagens->execute();
        $stmt_status_imagens->close();
    }
    
    // ================================================
    // COMMIT - TUDO OK
    // ================================================
    $conexao->commit();
    
    // ================================================
    // 5. LIMPAR DADOS TEMPORÁRIOS
    // ================================================
    if (file_exists($pasta_temp)) {
        $itens = scandir($pasta_temp);
        foreach ($itens as $item) {
            if ($item == '.' || $item == '..') continue;
            $caminho_item = $pasta_temp . $item;
            if (is_dir($caminho_item)) {
                rmdir($caminho_item);
            } else {
                unlink($caminho_item);
            }
        }
        rmdir($pasta_temp);
    }
    
    unset($_SESSION['importacao_temporaria']);
    
    // ================================================
    // REDIRECIONAR PARA PÁGINA DE SUCESSO
    // ================================================
    header("Location: ../Views/sucesso_importacao.php?especie_id=" . $especie_id . "&imagens=" . $imagens_salvas);
    exit;
    
} catch (Exception $e) {
    $conexao->rollback();
    
    $erro = "Erro ao salvar dados: " . $e->getMessage();
    error_log("ERRO NA TRANSAÇÃO: " . $erro);
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode($erro));
    exit;
}

$conexao->close();
ob_end_flush();
exit;