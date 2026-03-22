<?php
/**
 * CONTROLADOR DE ALTERAÇÃO DE SENHA - PENOMATO MVP
 */

// ============================================================
// INICIALIZAÇÃO
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../auth/verificar_acesso.php';

// Apenas usuários logados
protegerPagina('Faça login para alterar sua senha.');

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE . '/src/Views/usuario/alterar_senha.php');
    exit;
}

// ============================================================
// VALIDAR TOKEN CSRF
// ============================================================
$csrf_token = $_POST['csrf_token'] ?? '';

if (empty($csrf_token) || $csrf_token !== ($_SESSION['csrf_token'] ?? '')) {
    $_SESSION['mensagem_erro'] = "Requisição inválida. Tente novamente.";
    header('Location: ' . APP_BASE . '/src/Views/usuario/alterar_senha.php');
    exit;
}

// Invalidar token após uso
unset($_SESSION['csrf_token']);

// ============================================================
// RECEBER DADOS
// ============================================================
$senha_atual    = $_POST['senha_atual']    ?? '';
$nova_senha     = $_POST['nova_senha']     ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';

// ============================================================
// VALIDAÇÕES
// ============================================================
$erros = [];

if (empty($senha_atual)) {
    $erros[] = "Senha atual é obrigatória.";
}

if (empty($nova_senha) || strlen($nova_senha) < 8) {
    $erros[] = "A nova senha deve ter pelo menos 8 caracteres.";
}

if ($nova_senha !== $confirmar_senha) {
    $erros[] = "As senhas não conferem.";
}

if (!empty($erros)) {
    $_SESSION['mensagem_erro'] = implode('<br>', $erros);
    header('Location: ' . APP_BASE . '/src/Views/usuario/alterar_senha.php');
    exit;
}

// ============================================================
// BUSCAR USUÁRIO NO BANCO
// ============================================================
$usuario_id = getIdUsuario();

$usuario = buscarUm(
    "SELECT id, senha_hash FROM usuarios WHERE id = :id AND ativo = 1 LIMIT 1",
    [':id' => $usuario_id]
);

if (!$usuario) {
    $_SESSION['mensagem_erro'] = "Usuário não encontrado.";
    header('Location: ' . APP_BASE . '/src/Views/usuario/alterar_senha.php');
    exit;
}

// ============================================================
// VERIFICAR SENHA ATUAL
// ============================================================
if (!password_verify($senha_atual, $usuario['senha_hash'])) {
    $_SESSION['mensagem_erro'] = "Senha atual incorreta.";
    header('Location: ' . APP_BASE . '/src/Views/usuario/alterar_senha.php');
    exit;
}

// Impedir que a nova senha seja igual à atual
if (password_verify($nova_senha, $usuario['senha_hash'])) {
    $_SESSION['mensagem_erro'] = "A nova senha não pode ser igual à senha atual.";
    header('Location: ' . APP_BASE . '/src/Views/usuario/alterar_senha.php');
    exit;
}

// ============================================================
// ATUALIZAR SENHA
// ============================================================
$nova_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

$resultado = atualizar(
    'usuarios',
    ['senha_hash' => $nova_hash],
    'id = :id',
    [':id' => $usuario_id]
);

if (!$resultado) {
    $_SESSION['mensagem_erro'] = "Erro ao atualizar a senha. Tente novamente.";
    header('Location: ' . APP_BASE . '/src/Views/usuario/alterar_senha.php');
    exit;
}

// ============================================================
// ENCERRAR SESSÃO (SEGURANÇA - REAUTENTICAR COM NOVA SENHA)
// ============================================================
session_unset();
session_destroy();
session_start();

$_SESSION['mensagem_sucesso'] = "Senha alterada com sucesso! Faça login com sua nova senha.";
header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
exit;
