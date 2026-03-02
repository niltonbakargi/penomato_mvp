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
$dados_caracteristicas = $dados_temporarios['dados'] ?? [];
$imagens_temporarias = $dados_temporarios['imagens'] ?? [];

// ================================================
// LOG INICIAL - DIAGNÓSTICO
// ================================================
error_log("========== INICIANDO FINALIZAÇÃO ==========");
error_log("Temp ID: " . $temp_id);
error_log("Espécie ID: " . $especie_id);
error_log("Total imagens na sessão: " . count($imagens_temporarias));
error_log("Total campos características: " . count($dados_caracteristicas));

// ================================================
// VERIFICAR SE O USUÁRIO DA SESSÃO É O MESMO DA IMPORTAÇÃO
// ================================================
if (!isset($dados_temporarios['usuario_id']) || $dados_temporarios['usuario_id'] != $id_usuario) {
    error_log("ERRO: Usuário sem permissão");
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode("Você não tem permissão para finalizar esta importação."));
    exit;
}

// ================================================
// VERIFICAR SE HÁ IMAGENS PARA SALVAR
// ================================================
if (empty($imagens_temporarias)) {
    error_log("ERRO: Nenhuma imagem para salvar");
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode("Não há imagens para salvar."));
    exit;
}

// ================================================
// VERIFICAR SE HÁ DADOS PARA SALVAR
// ================================================
if (empty($dados_caracteristicas)) {
    error_log("ERRO: Nenhum dado para salvar");
    header("Location: ../Controllers/inserir_dados_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode("Você precisa colar os dados morfológicos primeiro."));
    exit;
}

// ================================================
// VERIFICAR PASTAS
// ================================================
$pasta_temp = dirname(dirname(__DIR__)) . '/uploads/temp/' . $temp_id . '/';
$pasta_definitiva = dirname(dirname(__DIR__)) . '/uploads/exsicatas/' . $especie_id . '/';

error_log("Pasta temp: " . $pasta_temp);
error_log("Pasta definitiva: " . $pasta_definitiva);

// Verificar se pasta temporária existe
if (!file_exists($pasta_temp)) {
    error_log("ERRO: Pasta temporária não encontrada");
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode("Pasta temporária não encontrada."));
    exit;
}

// Listar arquivos na pasta temp
$arquivos_temp = scandir($pasta_temp);
error_log("Arquivos na pasta temp: " . implode(", ", $arquivos_temp));

// ================================================
// VERIFICAR ARQUIVOS ANTES DE COMEÇAR
// ================================================
$imagens_validas = [];
foreach ($imagens_temporarias as $i => $img) {
    $nome_arquivo = basename($img['caminho_temporario']);
    $caminho_completo = $pasta_temp . $nome_arquivo;
    
    error_log("Verificando imagem $i - Parte: " . $img['parte_planta']);
    error_log("  Nome: " . $nome_arquivo);
    error_log("  Caminho: " . $caminho_completo);
    error_log("  Existe? " . (file_exists($caminho_completo) ? "SIM" : "NÃO"));
    
    if (file_exists($caminho_completo)) {
        $imagens_validas[] = $img;
    } else {
        error_log("  REMOVIDA: Imagem fantasma - " . $nome_arquivo);
    }
}

$imagens_temporarias = $imagens_validas;
$_SESSION['importacao_temporaria']['imagens'] = $imagens_validas;

error_log("Imagens válidas após verificação: " . count($imagens_temporarias));

// Verificar se ainda há imagens após limpeza
if (empty($imagens_temporarias)) {
    error_log("ERRO: Nenhuma imagem válida encontrada");
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode("Nenhuma imagem válida encontrada para salvar."));
    exit;
}

// ================================================
// CONECTAR AO BANCO
// ================================================
$conexao = new mysqli($servidor, $usuario_db, $senha_db, $banco);

if ($conexao->connect_error) {
    error_log("ERRO: Falha na conexão com banco");
    die("Erro de conexão: " . $conexao->connect_error);
}

$conexao->set_charset("utf8mb4");
error_log("Conectado ao banco com sucesso");

// ================================================
// INICIAR TRANSAÇÃO
// ================================================
$conexao->begin_transaction();
error_log("Transação iniciada");

try {
    
    // ================================================
    // 1. SALVAR DADOS DAS CARACTERÍSTICAS
    // ================================================
    error_log("--- Salvando características ---");
    
    // Verificar se já existem características para esta espécie
    $sql_check = "SELECT id FROM especies_caracteristicas WHERE especie_id = ?";
    $stmt_check = $conexao->prepare($sql_check);
    $stmt_check->bind_param("i", $especie_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    $ja_existe = $stmt_check->num_rows > 0;
    $stmt_check->close();
    
    error_log("Características já existem? " . ($ja_existe ? "SIM" : "NÃO"));
    
    if ($ja_existe) {
        // UPDATE - características já existem
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
        
        error_log("SQL UPDATE: " . $sql);
        
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param($tipos, ...$valores);
        $stmt->execute();
        $stmt->close();
        
        error_log("UPDATE realizado com sucesso");
        
    } else {
        // INSERT - novas características
        $colunas = implode(", ", array_keys($dados_caracteristicas));
        $placeholders = implode(", ", array_fill(0, count($dados_caracteristicas), "?"));
        $sql = "INSERT INTO especies_caracteristicas ($colunas) VALUES ($placeholders)";
        
        $tipos = str_repeat("s", count($dados_caracteristicas));
        $valores = array_values($dados_caracteristicas);
        
        error_log("SQL INSERT: " . $sql);
        
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param($tipos, ...$valores);
        $stmt->execute();
        $stmt->close();
        
        error_log("INSERT realizado com sucesso");
    }
    
    // ================================================
    // 2. ATUALIZAR STATUS DA ESPÉCIE (dados_internet)
    // ================================================
    error_log("--- Atualizando status da espécie ---");
    
    $sql_status = "UPDATE especies_administrativo 
                   SET status = 'dados_internet',
                       autor_dados_internet_id = ?,
                       data_dados_internet = NOW()
                   WHERE id = ?";
    $stmt_status = $conexao->prepare($sql_status);
    $stmt_status->bind_param("ii", $id_usuario, $especie_id);
    $stmt_status->execute();
    $stmt_status->close();
    
    error_log("Status atualizado para 'dados_internet'");
    
    // ================================================
    // 3. SALVAR IMAGENS (MOVER DA PASTA TEMP PARA DEFINITIVA)
    // ================================================
    error_log("--- Processando imagens ---");
    
    // Criar pasta definitiva se não existir
    if (!file_exists($pasta_definitiva)) {
        mkdir($pasta_definitiva, 0777, true);
        error_log("Pasta definitiva criada: " . $pasta_definitiva);
    }
    
    $imagens_salvas = 0;
    $imagens_falhas = [];
    
    foreach ($imagens_temporarias as $i => $img) {
        
        $nome_arquivo = basename($img['caminho_temporario']);
        $caminho_temp = $pasta_temp . $nome_arquivo;
        $caminho_definitivo = $pasta_definitiva . $nome_arquivo;
        $caminho_relativo = 'uploads/exsicatas/' . $especie_id . '/' . $nome_arquivo;
        
        error_log("--- Imagem $i ---");
        error_log("Parte: " . $img['parte_planta']);
        error_log("Nome: " . $nome_arquivo);
        error_log("Temp: " . $caminho_temp);
        error_log("Definitivo: " . $caminho_definitivo);
        error_log("Arquivo existe? " . (file_exists($caminho_temp) ? "SIM" : "NÃO"));
        error_log("Tamanho: " . (file_exists($caminho_temp) ? filesize($caminho_temp) . " bytes" : "N/A"));
        
        // Mover arquivo
        if (rename($caminho_temp, $caminho_definitivo)) {
            error_log("RENAME SUCESSO: Arquivo movido");
            
            // Verificar se o arquivo foi movido corretamente
            if (file_exists($caminho_definitivo)) {
                error_log("Arquivo confirmado no destino: " . $caminho_definitivo);
            } else {
                error_log("ALERTA: Arquivo não encontrado no destino após rename");
            }
            
            // ================================================
            // INSERT NA TABELA especies_imagens
            // ================================================
            $sql_insert = "INSERT INTO especies_imagens (
                especie_id,
                parte_planta,
                caminho_imagem,
                id_usuario_identificador,
                data_upload,
                descricao,
                fonte_nome,
                fonte_url,
                autor_imagem,
                licenca,
                status_validacao
            ) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, 'pendente')";
            
            $stmt = $conexao->prepare($sql_insert);
            $stmt->bind_param(
                "ississsss",
                $especie_id,
                $img['parte_planta'],
                $caminho_relativo,
                $id_usuario,
                $img['descricao'],
                $img['fonte_nome'],
                $img['fonte_url'],
                $img['autor_imagem'],
                $img['licenca']
            );
            
            if ($stmt->execute()) {
                $imagens_salvas++;
                error_log("INSERT SUCESSO: ID " . $stmt->insert_id);
            } else {
                error_log("INSERT FALHOU: " . $stmt->error);
                $imagens_falhas[] = $nome_arquivo . " (erro banco: " . $stmt->error . ")";
            }
            $stmt->close();
            
        } else {
            $erro_rename = error_get_last();
            error_log("RENAME FALHOU: " . ($erro_rename['message'] ?? 'Erro desconhecido'));
            $imagens_falhas[] = $nome_arquivo . " (erro mover arquivo)";
        }
    }
    
    error_log("--- Resumo imagens ---");
    error_log("Salvas com sucesso: " . $imagens_salvas);
    if (!empty($imagens_falhas)) {
        error_log("Falhas: " . implode(", ", $imagens_falhas));
    }
    
    // ================================================
    // COMMIT - TUDO OK
    // ================================================
    $conexao->commit();
    error_log("COMMIT realizado com sucesso");
    
    // ================================================
    // 4. LIMPAR PASTA TEMPORÁRIA
    // ================================================
    if (file_exists($pasta_temp)) {
        $itens = scandir($pasta_temp);
        foreach ($itens as $item) {
            if ($item == '.' || $item == '..') continue;
            $caminho_item = $pasta_temp . $item;
            if (is_file($caminho_item)) {
                unlink($caminho_item);
                error_log("Arquivo temporário removido: " . $item);
            }
        }
        rmdir($pasta_temp);
        error_log("Pasta temporária removida: " . $pasta_temp);
    }
    
    // ================================================
    // 5. LIMPAR SESSÃO TEMPORÁRIA
    // ================================================
    unset($_SESSION['importacao_temporaria']);
    error_log("Sessão temporária limpa");
    
    error_log("========== FINALIZAÇÃO CONCLUÍDA COM SUCESSO ==========");
    
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
    error_log("ERRO NA TRANSAÇÃO: " . $erro);
    error_log("========== FINALIZAÇÃO FALHOU ==========");
    
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode($erro));
    exit;
}

$conexao->close();
ob_end_flush();
exit;
?>