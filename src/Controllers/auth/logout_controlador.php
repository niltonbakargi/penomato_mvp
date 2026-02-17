<?php
/**
 * CONTROLADOR DE LOGOUT - PENOMATO MVP
 * 
 * Encerra a sessão do usuário, remove todos os dados da sessão
 * e redireciona para a página de login.
 * 
 * @package Penomato
 * @author Equipe Penomato
 * @version 1.0
 */

// ============================================================
// INICIALIZAÇÃO
// ============================================================

// Iniciar sessão (se não estiver iniciada)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// REGISTRAR LOGOUT (OPCIONAL)
// ============================================================

// Se houver um usuário logado, registrar o logout (útil para auditoria)
if (isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_email'])) {
    $usuario_id = $_SESSION['usuario_id'];
    $usuario_email = $_SESSION['usuario_email'];
    $usuario_nome = $_SESSION['usuario_nome'] ?? 'Desconhecido';
    
    // Registrar no log do PHP
    error_log("Logout: ID {$usuario_id} - {$usuario_nome} ({$usuario_email}) - IP: " . $_SERVER['REMOTE_ADDR']);
    
    // Opcional: registrar no banco de dados (tabela de logs)
    // require_once __DIR__ . '/../../../config/banco_de_dados.php';
    // inserir('logs_acesso', [
    //     'usuario_id' => $usuario_id,
    //     'acao' => 'logout',
    //     'ip' => $_SERVER['REMOTE_ADDR'],
    //     'data' => date('Y-m-d H:i:s')
    // ]);
}

// ============================================================
// DESTRUIR A SESSÃO COMPLETAMENTE
// ============================================================

// 1. Limpar todas as variáveis da sessão
$_SESSION = [];

// 2. Se há um cookie de sessão, removê-lo
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000, // Expirar no passado
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 3. Destruir a sessão no servidor
session_destroy();

// ============================================================
// REMOVER COOKIES DE "LEMBRAR-ME" (SE EXISTIREM)
// ============================================================

// Se você implementar "Lembrar-me" com cookies permanentes no futuro
if (isset($_COOKIE['lembrar_token'])) {
    setcookie('lembrar_token', '', time() - 42000, '/');
}

if (isset($_COOKIE['lembrar_usuario'])) {
    setcookie('lembrar_usuario', '', time() - 42000, '/');
}

// ============================================================
// REDIRECIONAR PARA O LOGIN
// ============================================================

// Definir mensagem de sucesso (opcional)
session_start(); // Re-iniciar para poder criar mensagem
$_SESSION['mensagem_sucesso'] = "Você saiu do sistema com sucesso.";

// Redirecionar para a página de login
header('Location: ../../Views/auth/login.php');
exit;

// ============================================================
// FUNÇÃO AUXILIAR (OPCIONAL)
// ============================================================

/**
 * Função para fazer logout forçado (pode ser chamada de outros lugares)
 * 
 * @param bool $redirecionar Se deve redirecionar para o login
 * @return void
 */
function fazerLogout($redirecionar = true) {
    // Iniciar sessão se necessário
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Limpar variáveis
    $_SESSION = [];
    
    // Remover cookie da sessão
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Destruir sessão
    session_destroy();
    
    // Redirecionar se solicitado
    if ($redirecionar) {
        session_start();
        $_SESSION['mensagem_sucesso'] = "Sessão encerrada.";
        header('Location: ../../Views/auth/login.php');
        exit;
    }
}

// ============================================================
// FIM DO ARQUIVO
// ============================================================
?>