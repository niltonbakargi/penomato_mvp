<?php
/**
 * CONTROLADOR DE REDEFINIÇÃO DE SENHA - PENOMATO MVP
 *
 * Valida o token e atualiza a senha do usuário.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/banco_de_dados.php';

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE . '/src/Views/auth/recuperar_senha.php');
    exit;
}

// ============================================================
// RECEBER DADOS
// ============================================================
$token           = trim($_POST['token'] ?? '');
$nova_senha      = $_POST['nova_senha'] ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';

// ============================================================
// VALIDAR TOKEN (FORMAT)
// ============================================================
if (empty($token) || !ctype_xdigit($token) || strlen($token) !== 64) {
    $_SESSION['mensagem_erro'] = "Link inválido. Solicite um novo link de recuperação.";
    header('Location: ' . APP_BASE . '/src/Views/auth/recuperar_senha.php');
    exit;
}

// ============================================================
// BUSCAR TOKEN NO BANCO
// ============================================================
$registro = buscarUm(
    "SELECT t.id, t.usuario_id, t.expira_em, t.usado
     FROM tokens_recuperacao_senha t
     WHERE t.token = :token
     LIMIT 1",
    [':token' => $token]
);

if (!$registro) {
    $_SESSION['mensagem_erro'] = "Link inválido. Solicite um novo link de recuperação.";
    header('Location: ' . APP_BASE . '/src/Views/auth/recuperar_senha.php');
    exit;
}

if ($registro['usado']) {
    $_SESSION['mensagem_erro'] = "Este link já foi utilizado. Solicite um novo.";
    header('Location: ' . APP_BASE . '/src/Views/auth/recuperar_senha.php');
    exit;
}

if (strtotime($registro['expira_em']) < time()) {
    $_SESSION['mensagem_erro'] = "Este link expirou. Solicite um novo link de recuperação.";
    header('Location: ' . APP_BASE . '/src/Views/auth/recuperar_senha.php');
    exit;
}

// ============================================================
// VALIDAR NOVA SENHA
// ============================================================
$erros = [];

if (empty($nova_senha) || strlen($nova_senha) < 8) {
    $erros[] = "A nova senha deve ter pelo menos 8 caracteres.";
}

if ($nova_senha !== $confirmar_senha) {
    $erros[] = "As senhas não conferem.";
}

if (!empty($erros)) {
    $_SESSION['mensagem_erro'] = implode('<br>', $erros);
    header('Location: ' . APP_BASE . '/src/Views/auth/redefinir_senha.php?token=' . urlencode($token));
    exit;
}

// ============================================================
// ATUALIZAR SENHA E MARCAR TOKEN COMO USADO
// ============================================================
$nova_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

iniciarTransacao();
try {
    atualizar('usuarios', ['senha_hash' => $nova_hash], 'id = :id', [':id' => $registro['usuario_id']]);
    atualizar('tokens_recuperacao_senha', ['usado' => 1], 'id = :id', [':id' => $registro['id']]);
    confirmarTransacao();
} catch (Exception $e) {
    reverterTransacao();
    error_log("Erro ao redefinir senha: " . $e->getMessage());
    $_SESSION['mensagem_erro'] = "Erro ao redefinir a senha. Tente novamente.";
    header('Location: ' . APP_BASE . '/src/Views/auth/redefinir_senha.php?token=' . urlencode($token));
    exit;
}

// ============================================================
// SUCESSO — REDIRECIONAR PARA LOGIN
// ============================================================
$_SESSION['mensagem_sucesso'] = "Senha redefinida com sucesso! Faça login com sua nova senha.";
header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
exit;
