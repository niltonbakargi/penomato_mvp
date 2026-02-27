<?php
// ============================================================
// LOGIN - PENOMATO (VERSÃO CORRIGIDA)
// ============================================================

// Iniciar sessão
session_start();

// Se já estiver logado, redireciona baseado no tipo de usuário
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['usuario_tipo'] === 'gestor') {
        header('Location: /penomato_mvp/src/Controllers/controlador_gestor.php');
    } else {
        header('Location: /penomato_mvp/src/Views/entrar_colaborador.php');
    }
    exit;
}

// Pegar mensagens da sessão
$erro = $_SESSION['mensagem_erro'] ?? '';
$sucesso = $_SESSION['mensagem_sucesso'] ?? '';
$email_tentativa = $_SESSION['email_tentativa'] ?? '';

// Pegar redirect da URL
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';

// Limpar mensagens
unset($_SESSION['mensagem_erro']);
unset($_SESSION['mensagem_sucesso']);
unset($_SESSION['email_tentativa']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Penomato</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0b5e42 0%, #1a7a5a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            background: #0b5e42;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .login-header i {
            font-size: 4rem;
            background: rgba(255,255,255,0.2);
            width: 100px;
            height: 100px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            border: 3px solid white;
        }
        
        .login-header h1 {
            font-size: 2rem;
            font-weight: 600;
        }
        
        .login-body {
            padding: 40px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            color: #0b5e42;
            font-size: 1.1rem;
        }
        
        input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
            background-color: #f8fafc;
        }
        
        input:focus {
            border-color: #0b5e42;
            outline: none;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(11,94,66,0.1);
        }
        
        input::placeholder {
            color: #a0aec0;
        }
        
        .btn-login {
            width: 100%;
            padding: 15px;
            background: #0b5e42;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            background: #0a4c35;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(11,94,66,0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .links {
            text-align: center;
            margin-top: 25px;
        }
        
        .links a {
            color: #0b5e42;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .links a:hover {
            color: #0a4c35;
            text-decoration: underline;
        }
        
        .separator {
            margin: 0 10px;
            color: #cbd5e0;
        }
        
        .back-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
        }
        
        .back-link a {
            color: #718096;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-link a:hover {
            color: #0b5e42;
        }
        
        .test-info {
            margin-top: 25px;
            padding: 15px;
            background: #f0f9f4;
            border-radius: 12px;
            font-size: 0.9rem;
            color: #2c3e50;
            border-left: 4px solid #0b5e42;
        }
        
        .test-info strong {
            color: #0b5e42;
            display: block;
            margin-bottom: 8px;
        }
        
        .test-info p {
            margin-bottom: 5px;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #718096;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            
            <!-- Cabeçalho -->
            <div class="login-header">
                <i class="fas fa-leaf"></i>
                <h1>Bem-vindo</h1>
            </div>
            
            <!-- Corpo -->
            <div class="login-body">
                
                <!-- Mensagens de erro/sucesso -->
                <?php if ($erro): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($erro); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($sucesso): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($sucesso); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Formulário de login -->
                <form action="/penomato_mvp/src/Controllers/auth/login_controlador.php" method="POST">
                    
                    <!-- Campo redirect oculto -->
                    <?php if ($redirect): ?>
                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>E-mail</label>
                        <div class="input-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input 
                                type="email" 
                                name="email" 
                                placeholder="seu@email.com" 
                                value="<?php echo htmlspecialchars($email_tentativa); ?>" 
                                required 
                                autofocus
                            >
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Senha</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input 
                                type="password" 
                                name="senha" 
                                placeholder="••••••••" 
                                required
                            >
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i>
                        Entrar no Sistema
                    </button>
                </form>
                
                <!-- Links -->
                <div class="links">
                    <a href="/penomato_mvp/src/Views/auth/cadastro.php">
                        <i class="fas fa-user-plus"></i> Criar nova conta
                    </a>
                </div>
                
                <!-- Informações de teste -->
                <div class="test-info">
                    <strong><i class="fas fa-flask"></i> Ambiente de Teste</strong>
                    <p><i class="fas fa-envelope" style="width: 20px;"></i> admin@penomato.com</p>
                    <p><i class="fas fa-lock" style="width: 20px;"></i> 123456</p>
                </div>
                
                <!-- Link voltar -->
                <div class="back-link">
                    <a href="/penomato_mvp/index.php">
                        <i class="fas fa-arrow-left"></i> Voltar para página inicial
                    </a>
                </div>
                
            </div>
        </div>
        
        <div class="footer">
            Penomato • Plataforma colaborativa de espécies florestais
        </div>
    </div>
</body>
</html>