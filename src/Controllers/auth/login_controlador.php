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
        header('Location: /penomato_mvp/src/Controllers/controlador_gestor.php');
    } else {
        header('Location: /penomato_mvp/src/Views/entrar_colaborador.php');
    }
    exit;
}

// ============================================================
// PROCESSAR LOGIN
// ============================================================
$email = '';
$erro = '';
$redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    // Validações básicas
    if (empty($email) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Email inválido.";
    }
    else {
        // Buscar usuário (adaptado para mysqli, não PDO)
        $conexao = new mysqli('127.0.0.1', 'root', '', 'penomato');
        
        if ($conexao->connect_error) {
            $erro = "Erro de conexão com o banco de dados.";
        } else {
            $sql = "SELECT * FROM usuarios WHERE email = ? AND ativo = 1 LIMIT 1";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $usuario = $resultado->fetch_assoc();
            
            // Verificar senha
            if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
                
                // Atualizar último acesso
                $sql_update = "UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?";
                $stmt_update = $conexao->prepare($sql_update);
                $stmt_update->bind_param("i", $usuario['id']);
                $stmt_update->execute();
                $stmt_update->close();
                
                // Criar sessão
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_tipo'] = $usuario['categoria']; // Campo 'categoria' na tabela
                $_SESSION['usuario_instituicao'] = $usuario['instituicao'] ?? '';
                
                // ================================================
                // REDIRECIONAR BASEADO NO TIPO DE USUÁRIO
                // ================================================
                if ($usuario['categoria'] === 'gestor') {
                    header('Location: /penomato_mvp/src/Controllers/controlador_gestor.php');
                } else {
                    // Qualquer outro tipo (colaborador, revisor, validador, visitante)
                    header('Location: /penomato_mvp/src/Views/entrar_colaborador.php');
                }
                exit;
                
            } else {
                $erro = "Email ou senha incorretos.";
            }
            $conexao->close();
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
header('Location: /penomato_mvp/src/Views/auth/login.php' . $redirect_param);
exit;