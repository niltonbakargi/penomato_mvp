<?php
/**
 * HELPER DE EMAIL - PENOMATO MVP
 *
 * Envia emails via PHPMailer + SMTP.
 * Para desenvolvimento, use o Mailtrap (mailtrap.io) — gratuito.
 * Para produção, troque pelas credenciais SMTP reais.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

// ============================================================
// CONFIGURAÇÕES DA APLICAÇÃO
// ============================================================

define('EMAIL_REMETENTE',      'noreply@penomato.app.br');
define('EMAIL_NOME_REMETENTE', 'Penomato');
if (!defined('APP_URL')) define('APP_URL', 'https://penomato.app.br');
if (!defined('APP_NOME')) define('APP_NOME', 'Penomato');

// ============================================================
// CONFIGURAÇÕES SMTP
// ------------------------------------------------------------
// Desenvolvimento → Mailtrap:
//   1. Crie conta grátis em mailtrap.io
//   2. Vá em Email Testing > Inboxes > sua caixa > SMTP Settings
//   3. Cole as credenciais abaixo
//
// Gmail (produção):
//   Host: smtp.gmail.com | Port: 587
//   User: seu@gmail.com  | Pass: senha de app (não a senha normal)
// ============================================================

// Credenciais SMTP ficam em config/dev_local.php (dev) ou config/producao.php (prod).
// Nunca coloque senhas reais aqui — este arquivo vai para o git.
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST',       'localhost');
    define('SMTP_PORT',       1025);
    define('SMTP_USER',       '');
    define('SMTP_PASS',       '');
    define('SMTP_ENCRYPTION', '');
}

// ============================================================
// FUNÇÃO PRINCIPAL
// ============================================================

/**
 * Envia um email HTML via PHPMailer/SMTP.
 * Se SMTP não estiver configurado, grava em logs/email_dev.log.
 *
 * @param string $destinatario  Email do destinatário
 * @param string $assunto       Assunto
 * @param string $corpo_html    Corpo em HTML
 * @return bool Sempre retorna true (nunca quebra o fluxo)
 */
function enviarEmail($destinatario, $assunto, $corpo_html) {

    // Sem credenciais → fallback para log
    if (empty(SMTP_USER) || empty(SMTP_PASS)) {
        return _logEmail($destinatario, $assunto, $corpo_html);
    }

    try {
        $mail = new PHPMailer(true);

        // Servidor SMTP
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Remetente e destinatário
        $mail->setFrom(EMAIL_REMETENTE, EMAIL_NOME_REMETENTE);
        $mail->addAddress($destinatario);

        // Conteúdo
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $corpo_html;
        $mail->AltBody = strip_tags($corpo_html);

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Erro ao enviar email para {$destinatario}: " . $e->getMessage());
        return _logEmail($destinatario, $assunto, $corpo_html);
    }
}

// ============================================================
// FALLBACK: LOG EM ARQUIVO
// ============================================================

function _logEmail($destinatario, $assunto, $corpo_html) {
    $log_dir = __DIR__ . '/../logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }

    $linha = "[" . date('Y-m-d H:i:s') . "]\n"
           . "Para: $destinatario\n"
           . "Assunto: $assunto\n"
           . str_repeat('-', 60) . "\n"
           . strip_tags($corpo_html) . "\n"
           . str_repeat('=', 60) . "\n\n";

    file_put_contents($log_dir . '/email_dev.log', $linha, FILE_APPEND | LOCK_EX);
    return true;
}

// ============================================================
// TEMPLATE HTML
// ============================================================

/**
 * Monta o template padrão de email do Penomato.
 */
function templateEmail($titulo, $conteudo) {
    return "
    <!DOCTYPE html>
    <html lang='pt-br'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>{$titulo}</title>
    </head>
    <body style='margin:0;padding:0;background:#f4f4f4;font-family:Segoe UI,Arial,sans-serif;'>
        <table width='100%' cellpadding='0' cellspacing='0' style='background:#f4f4f4;padding:30px 0;'>
            <tr>
                <td align='center'>
                    <table width='560' cellpadding='0' cellspacing='0'
                           style='background:#ffffff;border-radius:12px;overflow:hidden;
                                  box-shadow:0 4px 12px rgba(0,0,0,0.1);'>

                        <!-- Cabeçalho -->
                        <tr>
                            <td style='background:#0b5e42;padding:30px;text-align:center;'>
                                <h1 style='color:#ffffff;margin:0;font-size:22px;font-weight:700;'>
                                    🌿 " . APP_NOME . "
                                </h1>
                                <p style='color:rgba(255,255,255,0.85);margin:6px 0 0;font-size:14px;'>
                                    {$titulo}
                                </p>
                            </td>
                        </tr>

                        <!-- Corpo -->
                        <tr>
                            <td style='padding:35px 40px;color:#333333;font-size:15px;line-height:1.7;'>
                                {$conteudo}
                            </td>
                        </tr>

                        <!-- Rodapé -->
                        <tr>
                            <td style='background:#f8f9fa;padding:20px 40px;text-align:center;
                                       font-size:12px;color:#888888;border-top:1px solid #e0e0e0;'>
                                Este email foi enviado automaticamente — não responda.<br>
                                &copy; " . date('Y') . " " . APP_NOME . " · Todos os direitos reservados.
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>";
}
