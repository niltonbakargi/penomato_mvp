<?php
/**
 * CONTROLADOR DE CONFIRMAÇÃO DE ALTERAÇÃO DE E-MAIL - PENOMATO MVP
 *
 * Processa o link enviado para o novo e-mail.
 * Valida o token e efetiva a troca de e-mail.
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
    $_SESSION['mensagem_erro'] = "Link de confirmação inválido.";
    header('Location: /penomato_mvp/src/Views/auth/login.php');
    exit;
}

// ============================================================
// BUSCAR TOKEN NO BANCO
// ============================================================
$registro = buscarUm(
    "SELECT id, usuario_id, novo_email, expira_em, usado
     FROM tokens_alteracao_email
     WHERE token = :token LIMIT 1",
    [':token' => $token]
);

if (!$registro) {
    $_SESSION['mensagem_erro'] = "Link não encontrado. Solicite a alteração novamente.";
    header('Location: /penomato_mvp/src/Views/usuario/editar_perfil.php');
    exit;
}

if ($registro['usado']) {
    $_SESSION['mensagem_alerta'] = "Este link já foi utilizado. Seu e-mail já foi alterado.";
    header('Location: /penomato_mvp/src/Views/usuario/meu_perfil.php');
    exit;
}

if (strtotime($registro['expira_em']) < time()) {
    $_SESSION['mensagem_erro'] = "Link expirado. Solicite a alteração de e-mail novamente.";
    header('Location: /penomato_mvp/src/Views/auth/solicitar_alteracao_email.php');
    exit;
}

// ============================================================
// VERIFICAR SE O NOVO E-MAIL AINDA ESTÁ DISPONÍVEL
// ============================================================
$conflito = buscarUm(
    "SELECT id FROM usuarios WHERE email = :email AND id != :id LIMIT 1",
    [':email' => $registro['novo_email'], ':id' => $registro['usuario_id']]
);

if ($conflito) {
    executarQuery(
        "UPDATE tokens_alteracao_email SET usado = 1 WHERE id = :id",
        [':id' => $registro['id']]
    );
    $_SESSION['mensagem_erro'] = "O e-mail solicitado já foi utilizado por outra conta. Solicite a alteração novamente.";
    header('Location: /penomato_mvp/src/Views/auth/solicitar_alteracao_email.php');
    exit;
}

// ============================================================
// EFETIVAR A TROCA E MARCAR TOKEN COMO USADO
// ============================================================
iniciarTransacao();
try {
    atualizar(
        'usuarios',
        ['email' => $registro['novo_email']],
        'id = :id',
        [':id' => $registro['usuario_id']]
    );

    atualizar(
        'tokens_alteracao_email',
        ['usado' => 1],
        'id = :id',
        [':id' => $registro['id']]
    );

    confirmarTransacao();
} catch (Exception $e) {
    reverterTransacao();
    error_log("Erro ao confirmar alteração de e-mail: " . $e->getMessage());
    $_SESSION['mensagem_erro'] = "Erro ao alterar o e-mail. Tente novamente.";
    header('Location: /penomato_mvp/src/Views/auth/solicitar_alteracao_email.php');
    exit;
}

// ============================================================
// ATUALIZAR SESSÃO SE O USUÁRIO ESTIVER LOGADO
// ============================================================
if (isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] == $registro['usuario_id']) {
    $_SESSION['usuario_email'] = $registro['novo_email'];
}

// ============================================================
// SUCESSO
// ============================================================
$_SESSION['mensagem_sucesso'] = "E-mail alterado com sucesso para <strong>" . htmlspecialchars($registro['novo_email']) . "</strong>.";
header('Location: /penomato_mvp/src/Views/usuario/meu_perfil.php');
exit;
