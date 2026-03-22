<?php
/**
 * CONTROLADOR DE LOGIN - PENOMATO MVP
 * Versão simplificada para o MVP
 */

// ============================================================
// INICIALIZAÇÃO
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../config/banco_de_dados.php';

// ============================================================
// VERIFICAR SE JÁ ESTÁ LOGADO
// ============================================================
if (isset($_SESSION['usuario_id'])) {
    // Redirecionar baseado no tipo de usuário
    if ($_SESSION['usuario_tipo'] === 'gestor') {
        header('Location: ' . APP_BASE . '/src/Controllers/controlador_gestor.php');
    } else {
        header('Location: ' . APP_BASE . '/src/Views/entrar_colaborador.php');
    }
    exit;
}

// ============================================================
// CONFIGURAÇÕES DE BRUTE FORCE
// ============================================================
define('BF_MAX_TENTATIVAS', 5);   // Máximo de falhas por IP
define('BF_JANELA_MINUTOS', 15);  // Janela de tempo em minutos
define('BF_LIMPEZA_CHANCE', 50);  // 1-em-N chance de limpar registros antigos

// ============================================================
// PROCESSAR LOGIN
// ============================================================
$email    = '';
$erro     = '';
$redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $ip    = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // --------------------------------------------------------
    // VERIFICAR BRUTE FORCE ANTES DE QUALQUER PROCESSAMENTO
    // --------------------------------------------------------
    $janela_inicio = date('Y-m-d H:i:s', time() - BF_JANELA_MINUTOS * 60);

    $contagem = buscarUm(
        "SELECT COUNT(*) as total FROM tentativas_login
         WHERE ip = :ip AND criado_em >= :janela",
        [':ip' => $ip, ':janela' => $janela_inicio]
    );

    if ($contagem && $contagem['total'] >= BF_MAX_TENTATIVAS) {
        $erro = "Muitas tentativas falhas. Aguarde " . BF_JANELA_MINUTOS . " minutos e tente novamente.";
        $_SESSION['mensagem_erro']   = $erro;
        $_SESSION['email_tentativa'] = $email;
        header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
        exit;
    }

    // Limpeza ocasional de registros antigos (evita crescimento infinito da tabela)
    if (rand(1, BF_LIMPEZA_CHANCE) === 1) {
        $limite_limpeza = date('Y-m-d H:i:s', time() - 86400); // 24h
        executarQuery(
            "DELETE FROM tentativas_login WHERE criado_em < :limite",
            [':limite' => $limite_limpeza]
        );
    }

    // Validações básicas
    if (empty($email) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Email inválido.";
    } else {
        $usuario = buscarUm(
            "SELECT * FROM usuarios WHERE email = :email AND ativo = 1 LIMIT 1",
            [':email' => $email]
        );

        // Verificar senha
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {

            // ------------------------------------------------
            // BLOQUEAR CONTA NÃO VERIFICADA
            // ------------------------------------------------
            if ($usuario['status_verificacao'] === 'pendente') {
                $link_reenvio$redirect = APP_BASE . '/src/Controllers/auth/reenviar_verificacao_controlador.php';
                $erro = "Sua conta ainda não foi verificada. "
                      . "Verifique seu e-mail ou "
                      . "<a href=\"{$link_reenvio}?email=" . urlencode($email) . "\">clique aqui para reenviar o link</a>.";
                $_SESSION['mensagem_erro'] = $erro;
                $_SESSION['email_tentativa'] = $email;
                header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
                exit;
            }

            if ($usuario['status_verificacao'] === 'bloqueado') {
                $erro = "Esta conta foi bloqueada. Entre em contato com o suporte.";
                $_SESSION['mensagem_erro'] = $erro;
                header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
                exit;
            }

            // Login bem-sucedido: limpar tentativas falhas deste IP
            executarQuery(
                "DELETE FROM tentativas_login WHERE ip = :ip",
                [':ip' => $ip]
            );

            // Atualizar último acesso
            atualizar('usuarios', ['ultimo_acesso' => date('Y-m-d H:i:s')], 'id = :id', [':id' => $usuario['id']]);

            // Criar sessão
            $_SESSION['usuario_id']          = $usuario['id'];
            $_SESSION['usuario_nome']        = $usuario['nome'];
            $_SESSION['usuario_email']       = $usuario['email'];
            $_SESSION['usuario_tipo']        = $usuario['categoria'];
            $_SESSION['usuario_subtipo']     = $usuario['subtipo_colaborador'] ?? '';
            $_SESSION['usuario_instituicao'] = $usuario['instituicao'] ?? '';
            $_SESSION['login_time']          = time();

            // ------------------------------------------------
            // REDIRECIONAR BASEADO NO TIPO DE USUÁRIO
            // ------------------------------------------------
            if ($usuario['categoria'] === 'gestor') {
                header('Location: ' . APP_BASE . '/src/Controllers/controlador_gestor.php');
            } else {
                header('Location: ' . APP_BASE . '/src/Views/entrar_colaborador.php');
            }
            exit;

        } else {
            // Registrar tentativa falha
            inserir('tentativas_login', [
                'ip'       => $ip,
                'email'    => $email,
                'criado_em' => date('Y-m-d H:i:s'),
            ]);

            // Calcular tentativas restantes para feedback ao usuário
            $contagem_atual = buscarUm(
                "SELECT COUNT(*) as total FROM tentativas_login
                 WHERE ip = :ip AND criado_em >= :janela",
                [':ip' => $ip, ':janela' => $janela_inicio]
            );
            $restantes = BF_MAX_TENTATIVAS - ($contagem_atual['total'] ?? 1);

            if ($restantes > 0) {
                $erro = "Email ou senha incorretos. Você tem mais {$restantes} tentativa(s) antes do bloqueio temporário.";
            } else {
                $erro = "Muitas tentativas falhas. Aguarde " . BF_JANELA_MINUTOS . " minutos e tente novamente.";
            }
        }
    }
}

// ============================================================
// EM CASO DE ERRO, VOLTAR PARA O LOGIN
// ============================================================
if (!empty($erro)) {
    $_SESSION['mensagem_erro'] = $erro;
    $_SESSION['email_tentativa'] = $email;
}

// Se tiver redirect, mantém na URL
$redirect_param = $redirect ? '?redirect=' . urlencode($redirect) : '';
header('Location: ' . APP_BASE . '/src/Views/auth/login.php' . $redirect_param);
exit;