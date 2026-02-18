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
    header('Location: /penomato_mvp/src/Controllers/controlador_gestor.php');
    exit;
}

// ============================================================
// PROCESSAR LOGIN
// ============================================================
$email = '';
$erro = '';

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
        // Buscar usuário
        $sql = "SELECT * FROM usuarios WHERE email = ? AND ativo = 1 LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verificar senha
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            
            // Atualizar último acesso
            $sql = "UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario['id']]);
            
            // Criar sessão
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];
            $_SESSION['usuario_instituicao'] = $usuario['instituicao'] ?? '';
            
            // Redirecionar para o gestor (página principal)
            header('Location: /penomato_mvp/src/Controllers/controlador_gestor.php');
            exit;
            
        } else {
            $erro = "Email ou senha incorretos.";
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

header('Location: /penomato_mvp/src/Views/auth/login.php');
exit;