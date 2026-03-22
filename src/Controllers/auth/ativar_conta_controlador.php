<?php
/**
 * CONTROLADOR DE ATIVAÇÃO DE CONTA - PENOMATO MVP
 *
 * Processa o link de verificação enviado por email após o cadastro.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/banco_de_dados.php';

// ============================================================
// VALIDAR TOKEN VIA GET
// ============================================================
$token = trim($_GET['token'] ?? '');

if (empty($token) || !ctype_xdigit($token) || strlen($token) !== 64) {
    $_SESSION['mensagem_erro'] = "Link de ativação inválido.";
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

// ============================================================
// BUSCAR TOKEN NO BANCO
// ============================================================
$registro = buscarUm(
    "SELECT id, usuario_id, expira_em, usado
     FROM tokens_verificacao_email
     WHERE token = :token LIMIT 1",
    [':token' => $token]
);

if (!$registro) {
    $_SESSION['mensagem_erro'] = "Link de ativação não encontrado. Solicite um novo cadastro.";
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

if ($registro['usado']) {
    $_SESSION['mensagem_sucesso'] = "Sua conta já foi ativada anteriormente. Faça login.";
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

if (strtotime($registro['expira_em']) < time()) {
    $link_reenvio$redirect = APP_BASE . '/src/Controllers/auth/reenviar_verificacao_controlador.php';
    $_SESSION['mensagem_erro'] = "Link de ativação expirado. "
        . "<a href=\"{$link_reenvio}?usuario_id={$registro['usuario_id']}\">Clique aqui para reenviar</a>.";
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

// ============================================================
// ATIVAR CONTA E MARCAR TOKEN COMO USADO
// ============================================================
iniciarTransacao();
try {
    atualizar(
        'usuarios',
        ['status_verificacao' => 'verificado'],
        'id = :id',
        [':id' => $registro['usuario_id']]
    );

    atualizar(
        'tokens_verificacao_email',
        ['usado' => 1],
        'id = :id',
        [':id' => $registro['id']]
    );

    confirmarTransacao();
} catch (Exception $e) {
    reverterTransacao();
    error_log("Erro ao ativar conta: " . $e->getMessage());
    $_SESSION['mensagem_erro'] = "Erro ao ativar a conta. Tente novamente.";
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

// ============================================================
// SUCESSO
// ============================================================
$_SESSION['mensagem_sucesso'] = "Conta ativada com sucesso! Faça login para continuar.";
header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
exit;
