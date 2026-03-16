<?php
/**
 * CONTROLADOR DE EXCLUSÃO DE CONTA - PENOMATO MVP
 *
 * Realiza soft-delete: desativa a conta e anonimiza dados pessoais.
 * As contribuições (espécies, imagens) são mantidas sem vínculo ao usuário.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../auth/verificar_acesso.php';

protegerPagina();

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /penomato_mvp/src/Views/usuario/editar_perfil.php');
    exit;
}

// ============================================================
// VALIDAR CSRF
// ============================================================
$csrf = $_POST['csrf_token'] ?? '';

if (empty($csrf) || $csrf !== ($_SESSION['csrf_token_exclusao'] ?? '')) {
    $_SESSION['mensagem_erro'] = "Requisição inválida. Tente novamente.";
    header('Location: /penomato_mvp/src/Views/usuario/editar_perfil.php');
    exit;
}
unset($_SESSION['csrf_token_exclusao']);

// ============================================================
// VERIFICAR CONFIRMAÇÃO DIGITADA
// ============================================================
$confirmacao = trim($_POST['confirmacao_exclusao'] ?? '');

if ($confirmacao !== 'EXCLUIR') {
    $_SESSION['mensagem_erro'] = "Digite EXCLUIR para confirmar a exclusão.";
    header('Location: /penomato_mvp/src/Views/usuario/editar_perfil.php');
    exit;
}

// ============================================================
// VERIFICAR SENHA ATUAL
// ============================================================
$senha_exclusao = $_POST['senha_exclusao'] ?? '';
$usuario_id     = getIdUsuario();

$usuario = buscarUm(
    "SELECT id, senha_hash FROM usuarios WHERE id = :id AND ativo = 1 LIMIT 1",
    [':id' => $usuario_id]
);

if (!$usuario || !password_verify($senha_exclusao, $usuario['senha_hash'])) {
    $_SESSION['mensagem_erro'] = "Senha incorreta. A conta não foi excluída.";
    header('Location: /penomato_mvp/src/Views/usuario/editar_perfil.php');
    exit;
}

// ============================================================
// SOFT DELETE: DESATIVAR E ANONIMIZAR
// ============================================================
$timestamp = date('YmdHis');

iniciarTransacao();
try {
    atualizar(
        'usuarios',
        [
            'ativo'               => 0,
            'nome'                => 'Usuário Removido',
            'email'               => "removido_{$timestamp}_{$usuario_id}@excluido.penomato",
            'senha_hash'          => '',
            'bio'                 => null,
            'foto_perfil'         => null,
            'lattes'              => null,
            'orcid'               => null,
            'instituicao'         => null,
            'status_verificacao'  => 'bloqueado',
        ],
        'id = :id',
        [':id' => $usuario_id]
    );

    // Invalidar todos os tokens pendentes do usuário
    executarQuery(
        "UPDATE tokens_recuperacao_senha SET usado = 1 WHERE usuario_id = :uid",
        [':uid' => $usuario_id]
    );
    executarQuery(
        "UPDATE tokens_verificacao_email SET usado = 1 WHERE usuario_id = :uid",
        [':uid' => $usuario_id]
    );
    executarQuery(
        "UPDATE tokens_alteracao_email SET usado = 1 WHERE usuario_id = :uid",
        [':uid' => $usuario_id]
    );

    confirmarTransacao();
} catch (Exception $e) {
    reverterTransacao();
    error_log("Erro ao excluir conta: " . $e->getMessage());
    $_SESSION['mensagem_erro'] = "Erro ao excluir a conta. Tente novamente.";
    header('Location: /penomato_mvp/src/Views/usuario/editar_perfil.php');
    exit;
}

// ============================================================
// ENCERRAR SESSÃO
// ============================================================
session_unset();
session_destroy();
session_start();

$_SESSION['mensagem_sucesso'] = "Sua conta foi excluída. Obrigado por ter feito parte do Penomato.";
header('Location: /penomato_mvp/src/Views/auth/login.php');
exit;
