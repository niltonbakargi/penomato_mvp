<?php
/**
 * CONTROLADOR DE REENVIO DE VERIFICAÇÃO - PENOMATO MVP
 *
 * Reenvio do link de ativação para contas com verificação pendente.
 * Aceita GET (link direto do login) ou POST (formulário dedicado).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../../../config/email.php';

// ============================================================
// RECEBER IDENTIFICADOR (email via GET/POST ou usuario_id via GET)
// ============================================================
$email      = strtolower(trim($_GET['email']      ?? $_POST['email']      ?? ''));
$usuario_id = intval($_GET['usuario_id'] ?? 0);

// Mensagem padrão (não revela se email existe)
$mensagem_padrao = "Se a conta existir e estiver pendente de verificação, um novo link foi enviado.";

// ============================================================
// BUSCAR USUÁRIO
// ============================================================
if ($usuario_id > 0) {
    $usuario = buscarUm(
        "SELECT id, nome, email, status_verificacao FROM usuarios
         WHERE id = :id AND ativo = 1 LIMIT 1",
        [':id' => $usuario_id]
    );
} elseif (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $usuario = buscarUm(
        "SELECT id, nome, email, status_verificacao FROM usuarios
         WHERE email = :email AND ativo = 1 LIMIT 1",
        [':email' => $email]
    );
} else {
    $_SESSION['mensagem_erro'] = "Dados inválidos para reenvio.";
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

// ============================================================
// PROCESSAR SOMENTE SE CONTA EXISTIR E ESTIVER PENDENTE
// ============================================================
if ($usuario && $usuario['status_verificacao'] === 'pendente') {

    // Invalidar tokens anteriores
    executarQuery(
        "UPDATE tokens_verificacao_email SET usado = 1
         WHERE usuario_id = :uid AND usado = 0",
        [':uid' => $usuario['id']]
    );

    // Gerar novo token (24h)
    $novo_token = bin2hex(random_bytes(32));
    $expira_em  = date('Y-m-d H:i:s', time() + 86400);

    inserir('tokens_verificacao_email', [
        'usuario_id' => $usuario['id'],
        'token'      => $novo_token,
        'expira_em'  => $expira_em,
        'usado'      => 0,
    ]);

    // Montar e enviar email
    $link = APP_URL . '/src/Controllers/auth/ativar_conta_controlador.php?token=' . $novo_token;

    $conteudo = "
        <p>Olá, <strong>" . htmlspecialchars($usuario['nome']) . "</strong>!</p>
        <p>Você solicitou um novo link para ativar sua conta no <strong>" . APP_NOME . "</strong>.</p>
        <p>Clique no botão abaixo para confirmar seu e-mail. O link é válido por <strong>24 horas</strong>.</p>
        <p style='text-align:center;margin:30px 0;'>
            <a href='{$link}'
               style='background:#0b5e42;color:#ffffff;text-decoration:none;
                      padding:14px 32px;border-radius:8px;font-weight:700;
                      font-size:15px;display:inline-block;'>
                Ativar minha conta
            </a>
        </p>
        <p style='font-size:13px;color:#666;'>
            Se o botão não funcionar, copie e cole este link no navegador:<br>
            <a href='{$link}' style='color:#0b5e42;word-break:break-all;'>{$link}</a>
        </p>
        <p style='font-size:13px;color:#999;margin-top:25px;'>
            Se você não solicitou isso, ignore este e-mail.
        </p>";

    enviarEmail(
        $usuario['email'],
        'Ative sua conta - ' . APP_NOME,
        templateEmail('Confirmação de e-mail', $conteudo)
    );
}

// ============================================================
// REDIRECIONAR (mesma mensagem sempre)
// ============================================================
$_SESSION['mensagem_sucesso'] = $mensagem_padrao;
header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
exit;
