<?php
/**
 * CONTROLADOR DE SOLICITAÇÃO DE ALTERAÇÃO DE E-MAIL - PENOMATO MVP
 *
 * Valida senha, verifica disponibilidade do novo e-mail,
 * gera token e envia link de confirmação para o novo endereço.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../../../config/email.php';
require_once __DIR__ . '/verificar_acesso.php';

protegerPagina();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /penomato_mvp/src/Views/auth/solicitar_alteracao_email.php');
    exit;
}

// ============================================================
// VALIDAR CSRF
// ============================================================
$csrf = $_POST['csrf_token'] ?? '';

if (empty($csrf) || $csrf !== ($_SESSION['csrf_token_email'] ?? '')) {
    $_SESSION['mensagem_erro'] = "Requisição inválida. Tente novamente.";
    header('Location: /penomato_mvp/src/Views/auth/solicitar_alteracao_email.php');
    exit;
}
unset($_SESSION['csrf_token_email']);

// ============================================================
// RECEBER DADOS
// ============================================================
$novo_email        = strtolower(trim($_POST['novo_email']        ?? ''));
$confirmar_email   = strtolower(trim($_POST['confirmar_novo_email'] ?? ''));
$senha_atual       = $_POST['senha_atual'] ?? '';

$usuario_id        = getIdUsuario();
$email_atual       = getEmailUsuario();

// ============================================================
// VALIDAÇÕES
// ============================================================
$erros = [];

if (empty($novo_email) || !filter_var($novo_email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = "Informe um e-mail válido.";
}

if ($novo_email === $email_atual) {
    $erros[] = "O novo e-mail deve ser diferente do atual.";
}

if ($novo_email !== $confirmar_email) {
    $erros[] = "Os e-mails não conferem.";
}

if (empty($senha_atual)) {
    $erros[] = "Informe sua senha atual para confirmar.";
}

if (!empty($erros)) {
    $_SESSION['mensagem_erro'] = implode('<br>', $erros);
    header('Location: /penomato_mvp/src/Views/auth/solicitar_alteracao_email.php');
    exit;
}

// ============================================================
// VERIFICAR SENHA ATUAL
// ============================================================
$usuario = buscarUm(
    "SELECT id, nome, senha_hash FROM usuarios WHERE id = :id AND ativo = 1 LIMIT 1",
    [':id' => $usuario_id]
);

if (!$usuario || !password_verify($senha_atual, $usuario['senha_hash'])) {
    $_SESSION['mensagem_erro'] = "Senha incorreta.";
    header('Location: /penomato_mvp/src/Views/auth/solicitar_alteracao_email.php');
    exit;
}

// ============================================================
// VERIFICAR SE NOVO E-MAIL JÁ ESTÁ EM USO
// ============================================================
$existente = buscarUm(
    "SELECT id FROM usuarios WHERE email = :email AND id != :id LIMIT 1",
    [':email' => $novo_email, ':id' => $usuario_id]
);

if ($existente) {
    $_SESSION['mensagem_erro'] = "Este e-mail já está em uso por outra conta.";
    header('Location: /penomato_mvp/src/Views/auth/solicitar_alteracao_email.php');
    exit;
}

// ============================================================
// INVALIDAR TOKENS ANTERIORES E GERAR NOVO
// ============================================================
executarQuery(
    "UPDATE tokens_alteracao_email SET usado = 1
     WHERE usuario_id = :uid AND usado = 0",
    [':uid' => $usuario_id]
);

$token     = bin2hex(random_bytes(32));
$expira_em = date('Y-m-d H:i:s', time() + 3600); // 1 hora

inserir('tokens_alteracao_email', [
    'usuario_id' => $usuario_id,
    'novo_email' => $novo_email,
    'token'      => $token,
    'expira_em'  => $expira_em,
    'usado'      => 0,
]);

// ============================================================
// ENVIAR E-MAIL PARA O NOVO ENDEREÇO
// ============================================================
$link = APP_URL . '/src/Controllers/auth/confirmar_alteracao_email_controlador.php?token=' . $token;

$conteudo = "
    <p>Olá, <strong>" . htmlspecialchars($usuario['nome']) . "</strong>!</p>
    <p>Você solicitou a alteração do e-mail da sua conta no <strong>" . APP_NOME . "</strong>.</p>
    <p>Clique no botão abaixo para confirmar este endereço como seu novo e-mail.
       O link é válido por <strong>1 hora</strong>.</p>
    <p style='text-align:center;margin:30px 0;'>
        <a href='{$link}'
           style='background:#0b5e42;color:#ffffff;text-decoration:none;
                  padding:14px 32px;border-radius:8px;font-weight:700;
                  font-size:15px;display:inline-block;'>
            Confirmar novo e-mail
        </a>
    </p>
    <p style='font-size:13px;color:#666;'>
        Se o botão não funcionar, copie e cole este link no navegador:<br>
        <a href='{$link}' style='color:#0b5e42;word-break:break-all;'>{$link}</a>
    </p>
    <p style='font-size:13px;color:#999;margin-top:25px;'>
        Se você não solicitou esta alteração, seu e-mail atual permanece o mesmo
        e você pode ignorar este e-mail.
    </p>";

enviarEmail(
    $novo_email,
    'Confirme seu novo e-mail - ' . APP_NOME,
    templateEmail('Confirmação de alteração de e-mail', $conteudo)
);

// ============================================================
// REDIRECIONAR
// ============================================================
$_SESSION['mensagem_sucesso'] = "Link de confirmação enviado para <strong>{$novo_email}</strong>. Verifique sua caixa de entrada.";
header('Location: /penomato_mvp/src/Views/auth/solicitar_alteracao_email.php');
exit;
