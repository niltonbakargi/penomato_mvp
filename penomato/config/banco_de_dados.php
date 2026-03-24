<?php
/**
 * CONFIGURAÇÃO DO BANCO DE DADOS - PENOMATO MVP
 * 
 * Este arquivo gerencia a conexão com o banco de dados MySQL
 * usando PDO (PHP Data Objects) para segurança e flexibilidade.
 * 
 * @package Penomato
 * @author Equipe Penomato
 * @version 1.0
 */

// ============================================================
// CONFIGURAÇÕES DO BANCO DE DADOS
// ============================================================

// Carrega ambiente (dev ou prod) — define DB_HOST, DB_NAME, DB_USER, DB_PASS, APP_URL
require_once __DIR__ . '/app.php';

// ============================================================
// CONEXÃO COM O BANCO DE DADOS
// ============================================================

try {
    // String de conexão DSN (Data Source Name)
    $dsn = "mysql:host=" . DB_HOST . 
           ";dbname=" . DB_NAME . 
           ";charset=" . DB_CHARSET;
    
    // Opções do PDO para melhor segurança e performance
    $opcoes = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lança exceções em erros
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna arrays associativos
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Desativa emulação de prepared statements (segurança)
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"     // Força UTF-8 na conexão
    ];
    
    // Criar conexão PDO
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $opcoes);
    
    // Definir fuso horário da conexão (opcional)
    $pdo->exec("SET time_zone = '-03:00'"); // Horário de Brasília
    
} catch (PDOException $e) {
    // ============================================================
    // TRATAMENTO DE ERROS
    // ============================================================
    
    // Em produção, log em vez de exibir
    error_log("Erro de conexão com banco de dados: " . $e->getMessage());
    
    // Mensagem amigável para o usuário (sem detalhes técnicos)
    $msg_dev = (defined('APP_ENV') && APP_ENV === 'dev')
        ? '<p style="color:#856404;background:#fff3cd;padding:10px;border-radius:3px;">
               <strong>Dev:</strong> verifique se o XAMPP está rodando e o banco \'penomato\' existe.
           </p>'
        : '';
    die("
        <div style='font-family:Arial;padding:20px;background:#f8d7da;border:1px solid #f5c6cb;border-radius:5px;margin:20px;'>
            <h2 style='color:#721c24;'>Erro de conexão</h2>
            <p style='color:#721c24;'>Não foi possível conectar ao banco de dados. Tente novamente mais tarde.</p>
            {$msg_dev}
        </div>
    ");
}

// ============================================================
// FUNÇÕES AUXILIARES PARA O BANCO DE DADOS
// ============================================================

/**
 * Executa uma query SQL com prepared statements
 * 
 * @param string $sql Instrução SQL com placeholders (:nome ou ?)
 * @param array $parametros Array com os parâmetros para bind
 * @return PDOStatement|false Resultado da query ou false em erro
 */
function executarQuery($sql, $parametros = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Erro na query: " . $e->getMessage() . " | SQL: " . $sql);
        return false;
    }
}

/**
 * Busca um único registro do banco
 * 
 * @param string $sql Instrução SQL
 * @param array $parametros Parâmetros para bind
 * @return array|false Array associativo com o resultado ou false
 */
function buscarUm($sql, $parametros = []) {
    $stmt = executarQuery($sql, $parametros);
    return $stmt ? $stmt->fetch() : false;
}

/**
 * Busca múltiplos registros do banco
 * 
 * @param string $sql Instrução SQL
 * @param array $parametros Parâmetros para bind
 * @return array Array com os resultados (vazio se não encontrar)
 */
function buscarTodos($sql, $parametros = []) {
    $stmt = executarQuery($sql, $parametros);
    return $stmt ? $stmt->fetchAll() : [];
}

/**
 * Insere um registro no banco e retorna o ID inserido
 * 
 * @param string $tabela Nome da tabela
 * @param array $dados Array associativo [coluna => valor]
 * @return int|false ID inserido ou false em erro
 */
function inserir($tabela, $dados) {
    global $pdo;
    
    try {
        $colunas = implode(', ', array_keys($dados));
        $placeholders = ':' . implode(', :', array_keys($dados));
        
        $sql = "INSERT INTO {$tabela} ({$colunas}) VALUES ({$placeholders})";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($dados);
        
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Erro ao inserir em {$tabela}: " . $e->getMessage());
        return false;
    }
}

/**
 * Atualiza um registro no banco
 * 
 * @param string $tabela Nome da tabela
 * @param array $dados Array associativo [coluna => valor]
 * @param string $condicao Condição WHERE (ex: "id = :id")
 * @param array $parametros_condicao Parâmetros da condição
 * @return int|false Número de linhas afetadas ou false
 */
function atualizar($tabela, $dados, $condicao, $parametros_condicao = []) {
    global $pdo;
    
    try {
        $sets = [];
        foreach (array_keys($dados) as $coluna) {
            $sets[] = "{$coluna} = :{$coluna}";
        }
        
        $sql = "UPDATE {$tabela} SET " . implode(', ', $sets) . " WHERE {$condicao}";
        
        $parametros = array_merge($dados, $parametros_condicao);
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($parametros);
        
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("Erro ao atualizar {$tabela}: " . $e->getMessage());
        return false;
    }
}

/**
 * Inicia uma transação
 */
function iniciarTransacao() {
    global $pdo;
    $pdo->beginTransaction();
}

/**
 * Confirma uma transação
 */
function confirmarTransacao() {
    global $pdo;
    $pdo->commit();
}

/**
 * Reverte uma transação
 */
function reverterTransacao() {
    global $pdo;
    $pdo->rollBack();
}

/**
 * Escapa strings para uso em LIKE (segurança)
 * 
 * @param string $termo Termo a ser escapado
 * @return string Termo escapado
 */
function escaparLike($termo) {
    return addcslashes($termo, '%_');
}

// ============================================================
// VERIFICAÇÃO RÁPIDA DA CONEXÃO (OPCIONAL - DESCOMENTAR PARA TESTAR)
// ============================================================

/*
try {
    $teste = $pdo->query("SELECT 1")->fetch();
    echo "<div style='background: #d4edda; color: #155724; padding: 10px; margin: 20px; border-radius: 5px;'>";
    echo "✅ Conexão com o banco de dados estabelecida com sucesso!";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; margin: 20px; border-radius: 5px;'>";
    echo "❌ Falha na conexão: " . $e->getMessage();
    echo "</div>";
}
*/

// ============================================================
// INSTRUÇÕES PARA CRIAR O BANCO DE DADOS
// ============================================================

/*
Execute este SQL no phpMyAdmin ou MySQL para criar o banco:

CREATE DATABASE IF NOT EXISTS penomato 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE penomato;

-- As tabelas serão criadas pelos scripts individuais
-- Veja a documentação completa em /docs/sql/estrutura_inicial.sql
*/

// ============================================================
// FIM DO ARQUIVO
// ============================================================
?>