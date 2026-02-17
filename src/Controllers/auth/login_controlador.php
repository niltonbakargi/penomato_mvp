<?php
/**
 * CONTROLADOR DE LOGIN - PENOMATO MVP
 * 
 * Processa o formulário de login, verifica credenciais
 * e inicia a sessão do usuário.
 * 
 * @package Penomato
 * @author Equipe Penomato
 * @version 1.0
 */

// ============================================================
// INICIALIZAÇÃO
// ============================================================

// Iniciar sessão (se não estiver iniciada)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir conexão com o banco de dados
require_once __DIR__ . '/../../../config/banco_de_dados.php';

// ============================================================
// VERIFICAR SE JÁ ESTÁ LOGADO
// ============================================================

// Se o usuário já estiver logado, redireciona para o painel
if (isset($_SESSION['usuario_id'])) {
    header('Location: ../../Views/usuario/meu_perfil.php');
    exit;
}

// ============================================================
// PROCESSAR LOGIN
// ============================================================

// Inicializar variáveis
$email = '';
$erro = '';
$sucesso = '';

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Receber e sanitizar dados
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $lembrar = isset($_POST['lembrar']) ? true : false;
    
    // ============================================================
    // VALIDAÇÕES
    // ============================================================
    
    // Validar campos obrigatórios
    if (empty($email) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    }
    // Validar formato do email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Por favor, insira um email válido.";
    }
    else {
        // ============================================================
        // BUSCAR USUÁRIO NO BANCO
        // ============================================================
        
        $usuario = buscarUm(
            "SELECT * FROM usuarios 
             WHERE email = :email 
             AND ativo = 1 
             LIMIT 1",
            [':email' => $email]
        );
        
        // ============================================================
        // VERIFICAR SENHA
        // ============================================================
        
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            
            // ============================================================
            // LOGIN BEM-SUCEDIDO
            // ============================================================
            
            // Atualizar último acesso
            atualizar(
                'usuarios',
                ['ultimo_acesso' => date('Y-m-d H:i:s')],
                'id = :id',
                [':id' => $usuario['id']]
            );
            
            // Criar sessão do usuário
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];
            $_SESSION['usuario_subtipo'] = $usuario['subtipo_colaborador'] ?? '';
            $_SESSION['usuario_instituicao'] = $usuario['instituicao'] ?? '';
            $_SESSION['login_time'] = time();
            
            // Se "Lembrar-me" estiver marcado, criar cookie de longa duração
            if ($lembrar) {
                // Gerar token único (pode ser implementado depois)
                // Por enquanto, só aumenta o tempo da sessão
                $_SESSION['lembrar'] = true;
            }
            
            // Registrar login no log (opcional)
            error_log("Login bem-sucedido: {$usuario['email']} - {$usuario['nome']}");
            
            // ============================================================
            // REDIRECIONAR BASEADO NO TIPO DE USUÁRIO
            // ============================================================
            
            // Verificar se há uma URL de destino salva
            $destino = $_SESSION['url_destino'] ?? '';
            
            if (!empty($destino)) {
                // Limpar a URL de destino
                unset($_SESSION['url_destino']);
                header("Location: $destino");
            } else {
                // Redirecionar baseado no tipo de usuário
                switch ($usuario['tipo']) {
                    case 'gestor':
                        header('Location: ../../Views/gestor/painel_gestor.php');
                        break;
                    case 'revisor':
                        header('Location: ../../Views/revisor/painel_revisor.php');
                        break;
                    case 'validador':
                        header('Location: ../../Views/validador/painel_validador.php');
                        break;
                    case 'colaborador':
                        header('Location: ../../Views/colaborador/cadastrar_caracteristicas.php');
                        break;
                    default:
                        header('Location: ../../Views/usuario/meu_perfil.php');
                }
            }
            exit;
            
        } else {
            // ============================================================
            // LOGIN FALHOU
            // ============================================================
            
            // Log da tentativa (útil para detectar ataques)
            error_log("Tentativa de login falhou para email: $email - IP: " . $_SERVER['REMOTE_ADDR']);
            
            // Mensagem genérica por segurança (não revela se email existe)
            $erro = "Email ou senha incorretos.";
            
            // Opcional: contar tentativas para bloquear após X tentativas
            // (pode ser implementado depois)
        }
    }
}

// ============================================================
// REDIRECIONAR DE VOLTA PARA O LOGIN COM MENSAGEM DE ERRO
// ============================================================

// Se chegou aqui, houve erro
if (!empty($erro)) {
    // Guardar mensagem na sessão
    $_SESSION['mensagem_erro'] = $erro;
    $_SESSION['email_tentativa'] = $email; // Para preencher o campo email novamente
}

// Voltar para a página de login
header('Location: ../../Views/auth/login.php');
exit;

// ============================================================
// FUNÇÕES AUXILIARES (opcional - podem ficar em verificar_acesso.php)
// ============================================================

/**
 * Verifica se o usuário está logado
 * 
 * @return bool
 */
function estaLogado() {
    return isset($_SESSION['usuario_id']);
}

/**
 * Redireciona para login se não estiver logado
 * 
 * @param string $mensagem Mensagem opcional
 */
function protegerPagina($mensagem = 'Faça login para acessar esta página.') {
    if (!estaLogado()) {
        // Salvar a URL que o usuário tentou acessar
        $_SESSION['url_destino'] = $_SERVER['REQUEST_URI'];
        
        // Salvar mensagem
        $_SESSION['mensagem_erro'] = $mensagem;
        
        // Redirecionar para login
        header('Location: /penomato_mvp/src/Views/auth/login.php');
        exit;
    }
}

/**
 * Verifica se o usuário tem um tipo específico
 * 
 * @param string|array $tipos Tipo(s) permitido(s)
 * @return bool
 */
function usuarioTipoPermitido($tipos) {
    if (!estaLogado()) {
        return false;
    }
    
    if (is_string($tipos)) {
        return $_SESSION['usuario_tipo'] === $tipos;
    }
    
    return in_array($_SESSION['usuario_tipo'], $tipos);
}

/**
 * Redireciona se não tiver permissão
 * 
 * @param string|array $tipos Tipo(s) permitido(s)
 * @param string $mensagem Mensagem de erro
 */
function protegerPorTipo($tipos, $mensagem = 'Acesso negado.') {
    if (!usuarioTipoPermitido($tipos)) {
        $_SESSION['mensagem_erro'] = $mensagem;
        header('Location: /penomato_mvp/src/Views/publico/busca_caracteristicas.php');
        exit;
    }
}

// ============================================================
// FIM DO ARQUIVO
// ============================================================
?>