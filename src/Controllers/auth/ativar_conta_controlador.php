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
require_once __DIR__ . '/../../../config/email.php';

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
    $link_reenvio = APP_BASE . '/src/Controllers/auth/reenviar_verificacao_controlador.php';
    $_SESSION['mensagem_erro'] = "Link de ativação expirado. "
        . "<a href=\"{$link_reenvio}?usuario_id={$registro['usuario_id']}\">Clique aqui para reenviar</a>.";
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

// ============================================================
// BUSCAR DADOS DO USUÁRIO
// ============================================================
$usuario = buscarUm(
    "SELECT id, nome, email, subtipo_colaborador FROM usuarios WHERE id = :id LIMIT 1",
    [':id' => $registro['usuario_id']]
);

$precisa_aprovacao_gestor = ($usuario['subtipo_colaborador'] !== 'identificador');

// ============================================================
// ATIVAR CONTA E MARCAR TOKEN COMO USADO
// ============================================================
iniciarTransacao();
try {
    if ($precisa_aprovacao_gestor) {
        // E-mail confirmado mas aguarda aprovação do gestor
        atualizar(
            'usuarios',
            ['status_verificacao' => 'aguardando_gestor', 'ativo' => 0],
            'id = :id',
            [':id' => $registro['usuario_id']]
        );
    } else {
        // Identificador: acesso imediato
        atualizar(
            'usuarios',
            ['status_verificacao' => 'verificado', 'ativo' => 1],
            'id = :id',
            [':id' => $registro['usuario_id']]
        );
    }

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
if ($precisa_aprovacao_gestor) {
    // Notificar todos os gestores
    $gestores = buscarTodos(
        "SELECT email, nome FROM usuarios WHERE categoria = 'gestor' AND ativo = 1",
        []
    );
    foreach ($gestores as $gestor) {
        $conteudo_email = "
            <p>Olá, <strong>" . htmlspecialchars($gestor['nome']) . "</strong>!</p>
            <p>Um novo membro confirmou o e-mail e aguarda sua aprovação:</p>
            <table style='margin:16px 0;border-collapse:collapse;width:100%;'>
                <tr><td style='padding:6px 12px;background:#f4f4f4;font-weight:600;'>Nome</td><td style='padding:6px 12px;'>" . htmlspecialchars($usuario['nome']) . "</td></tr>
                <tr><td style='padding:6px 12px;background:#f4f4f4;font-weight:600;'>E-mail</td><td style='padding:6px 12px;'>" . htmlspecialchars($usuario['email']) . "</td></tr>
            </table>
            <p>
                <a href='" . APP_URL . "/src/Controllers/controlador_gestor.php'
                   style='background:#0b5e42;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:600;'>
                    Ir para o painel do gestor
                </a>
            </p>";
        enviarEmail($gestor['email'], 'Novo membro aguardando aprovação — Penomato', templateEmail('Aprovação necessária', $conteudo_email));
    }

    $_SESSION['mensagem_sucesso'] = "E-mail confirmado! Seu cadastro foi enviado para aprovação do gestor. Você receberá um aviso quando for liberado.";
} else {
    $_SESSION['mensagem_sucesso'] = "Conta ativada com sucesso! Faça login para continuar.";
}

header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
exit;
