<?php
// ============================================================
// LOGIN - PENOMATO (VERSÃO CORRIGIDA)
// ============================================================

if (!defined('APP_ENV')) {
    require_once __DIR__ . '/../../../config/app.php';
}

// Iniciar sessão
session_start();

// Se já estiver logado, redireciona baseado no tipo de usuário
if (isset($_SESSION['usuario_id'])) {
    if ($_SESSION['usuario_tipo'] === 'gestor') {
        header('Location: ' . APP_BASE . '/src/Controllers/controlador_gestor.php');
    } else {
        header('Location: ' . APP_BASE . '/src/Views/entrar_colaborador.php');
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
    <style>
        body {
            background: linear-gradient(135deg, var(--cor-primaria) 0%, var(--verde-600) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--esp-5);
        }

        .login-container {
            width: 100%;
            max-width: 450px;
        }

        .login-card {
            background: var(--branco);
            border-radius: var(--raio-2xl);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }

        .login-header {
            background: var(--cor-primaria);
            color: var(--branco);
            padding: var(--esp-8);
            text-align: center;
        }

        .login-header i {
            font-size: var(--texto-4xl);
            background: rgba(255,255,255,0.2);
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--esp-4);
            border: 3px solid var(--branco);
        }

        .login-header h1 {
            font-size: var(--texto-2xl);
            font-weight: var(--peso-semi);
            color: var(--branco);
        }

        .login-body {
            padding: var(--esp-10);
        }

        .form-group {
            margin-bottom: var(--esp-6);
        }

        .input-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: var(--esp-4);
            color: var(--cor-primaria);
            font-size: var(--texto-md);
        }

        .input-group input {
            width: 100%;
            padding: var(--esp-4) var(--esp-4) var(--esp-4) var(--esp-11);
            border: 2px solid var(--cinza-200);
            border-radius: var(--raio-lg);
            font-size: var(--texto-md);
            transition: var(--transicao);
            background-color: var(--cinza-50);
            font-family: var(--fonte-principal);
        }

        .input-group input:focus {
            border-color: var(--cor-primaria);
            outline: none;
            background-color: var(--branco);
            box-shadow: var(--sombra-foco);
        }

        .input-group input::placeholder { color: var(--cinza-400); }

        .btn-login {
            width: 100%;
            padding: var(--esp-4);
            background: var(--cor-primaria);
            color: var(--branco);
            border: none;
            border-radius: var(--raio-lg);
            font-size: var(--texto-lg);
            font-weight: var(--peso-semi);
            cursor: pointer;
            transition: var(--transicao);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--esp-2);
            margin-top: var(--esp-2);
        }

        .btn-login:hover {
            background: var(--cor-primaria-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(11,94,66,0.3);
        }

        .btn-login:active { transform: translateY(0); }

        .links {
            text-align: center;
            margin-top: var(--esp-6);
        }

        .links a {
            color: var(--cor-primaria);
            text-decoration: none;
            font-weight: var(--peso-semi);
            transition: var(--transicao);
        }

        .links a:hover {
            color: var(--cor-primaria-hover);
            text-decoration: underline;
        }

        .back-link {
            text-align: center;
            margin-top: var(--esp-6);
            padding-top: var(--esp-5);
            border-top: 2px solid var(--cinza-200);
        }

        .back-link a {
            color: var(--cinza-500);
            text-decoration: none;
            font-size: var(--texto-sm);
            transition: var(--transicao);
            display: inline-flex;
            align-items: center;
            gap: var(--esp-2);
        }

        .back-link a:hover { color: var(--cor-primaria); }

        .footer {
            text-align: center;
            margin-top: var(--esp-5);
            color: rgba(255,255,255,0.7);
            font-size: var(--texto-sm);
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
                    <div class="alerta--perigo">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $erro; ?>
                    </div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                    <div class="alerta--sucesso">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $sucesso; ?>
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
                    <a href="/penomato_mvp/src/Views/auth/recuperar_senha.php">
                        <i class="fas fa-key"></i> Esqueceu sua senha?
                    </a>
                    &nbsp;·&nbsp;
                    <a href="/penomato_mvp/src/Views/auth/cadastro.php">
                        <i class="fas fa-user-plus"></i> Criar nova conta
                    </a>
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