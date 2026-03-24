<?php
/**
 * CONTROLADOR DE RECUPERAÇÃO DE SENHA - PENOMATO MVP
 *
 * Recebe o email, gera token e envia link de redefinição.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../../../config/email.php';

// Apenas POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE . '/src/Views/auth/recuperar_senha.php');
    exit;
}

// ============================================================
// RECEBER E VALIDAR EMAIL
// ============================================================
$email = strtolower(trim($_POST['email'] ?? ''));

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['mensagem_erro'] = "Informe um e-mail válido.";
    header('Location: ' . APP_BASE . '/src/Views/auth/recuperar_senha.php');
    exit;
}

// ============================================================
// VERIFICAR SE EXISTE NO BANCO (SEM REVELAR AO USUÁRIO)
// ============================================================
$usuario = buscarUm(
    "SELECT id, nome FROM usuarios WHERE email = :email AND ativo = 1 LIMIT 1",
    [':email' => $email]
);

// Sempre mostramos a mesma mensagem para não vazar se o email existe
$mensagem_padrao = "Se este e-mail estiver cadastrado, você receberá o link de recuperação em instantes.";

if ($usuario) {

    // ========================================================
    // INVALIDAR TOKENS ANTERIORES DESTE USUÁRIO
    // ========================================================
    executarQuery(
        "UPDATE tokens_recuperacao_senha SET usado = 1
         WHERE usuario_id = :uid AND usado = 0",
        [':uid' => $usuario['id']]
    );

    // ========================================================
    // GERAR NOVO TOKEN (64 chars hex = 32 bytes)
    // ========================================================
    $token     = bin2hex(random_bytes(32));
    $expira_em = date('Y-m-d H:i:s', time() + 3600); // 1 hora

    inserir('tokens_recuperacao_senha', [
        'usuario_id' => $usuario['id'],
        'token'      => $token,
        'expira_em'  => $expira_em,
        'usado'      => 0,
    ]);

    // ========================================================
    // MONTAR E ENVIAR EMAIL
    // ========================================================
    $link = APP_URL . '/src/Views/auth/redefinir_senha.php?token=' . $token;

    $conteudo = "
        <p>Olá, <strong>" . htmlspecialchars($usuario['nome']) . "</strong>!</p>
        <p>Recebemos uma solicitação para redefinir a senha da sua conta no <strong>" . APP_NOME . "</strong>.</p>
        <p>Clique no botão abaixo para criar uma nova senha. O link é válido por <strong>1 hora</strong>.</p>
        <p style='text-align:center;margin:30px 0;'>
            <a href='{$link}'
               style='background:#0b5e42;color:#ffffff;text-decoration:none;
                      padding:14px 32px;border-radius:8px;font-weight:700;
                      font-size:15px;display:inline-block;'>
                Redefinir minha senha
            </a>
        </p>
        <p style='font-size:13px;color:#666;'>
            Se o botão não funcionar, copie e cole este link no navegador:<br>
            <a href='{$link}' style='color:#0b5e42;word-break:break-all;'>{$link}</a>
        </p>
        <p style='font-size:13px;color:#999;margin-top:25px;'>
            Se você não solicitou a recuperação de senha, ignore este e-mail.
            Sua senha permanece a mesma.
        </p>";

    enviarEmail(
        $email,
        'Recuperação de senha - ' . APP_NOME,
        templateEmail('Recuperação de senha', $conteudo)
    );
}

// ============================================================
// REDIRECIONAR (mesma mensagem independente de encontrar o email)
// ============================================================
$_SESSION['mensagem_sucesso'] = $mensagem_padrao;
header('Location: ' . APP_BASE . '/src/Views/auth/recuperar_senha.php');
exit;
