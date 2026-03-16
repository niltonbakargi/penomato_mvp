<?php
/**
 * CONTROLADOR DE LOGOUT - PENOMATO MVP
 *
 * Encerra a sessão do usuário e redireciona para o login.
 * Também expõe fazerLogout() para uso interno (ex: expiração de sessão).
 */

// ============================================================
// FUNÇÃO AUXILIAR (deve ser definida antes do código de execução
// para que require_once deste arquivo apenas carregue a função
// sem disparar o logout)
// ============================================================

/**
 * Faz logout do usuário.
 *
 * @param bool $redirecionar Se deve redirecionar para o login após o logout
 */
function fazerLogout($redirecionar = true) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Registrar no log
    if (isset($_SESSION['usuario_id'])) {
        error_log("Logout: ID {$_SESSION['usuario_id']} - "
            . ($_SESSION['usuario_nome'] ?? '') . " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? ''));
    }

    // 1. Limpar variáveis da sessão
    $_SESSION = [];

    // 2. Remover cookie da sessão
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // 3. Destruir sessão
    session_destroy();

    // 4. Remover cookies de "lembrar-me" (se existirem)
    if (isset($_COOKIE['lembrar_token'])) {
        setcookie('lembrar_token', '', time() - 42000, '/');
    }
    if (isset($_COOKIE['lembrar_usuario'])) {
        setcookie('lembrar_usuario', '', time() - 42000, '/');
    }

    if ($redirecionar) {
        session_start();
        $_SESSION['mensagem_sucesso'] = "Você saiu do sistema com sucesso.";
        header('Location: /penomato_mvp/src/Views/auth/login.php');
        exit;
    }
}

// ============================================================
// EXECUÇÃO DIRETA (acesso via URL /logout_controlador.php)
// Garante que o código de logout só rode quando este arquivo
// for o script principal — não quando for incluído por require_once.
// ============================================================

if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    fazerLogout(true);
}
