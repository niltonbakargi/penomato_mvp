<?php
// ============================================================
// LOGIN SIMPLES - MVP PENOMATO
// ============================================================

// Iniciar sessão
session_start();

// Se já estiver logado, redireciona
if (isset($_SESSION['usuario_id'])) {
    header('Location: /penomato_mvp/perfil');
    exit;
}

// Pegar mensagens da sessão
$erro = $_SESSION['mensagem_erro'] ?? '';
$email_tentativa = $_SESSION['email_tentativa'] ?? '';

// Limpar mensagens
unset($_SESSION['mensagem_erro']);
unset($_SESSION['email_tentativa']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Penomato</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #0b5e42;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: white;
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        h1 {
            text-align: center;
            color: #0b5e42;
            margin-bottom: 30px;
            font-size: 2rem;
        }
        .logo {
            text-align: center;
            font-size: 3rem;
            color: #0b5e42;
            margin-bottom: 10px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        input:focus {
            border-color: #0b5e42;
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #0b5e42;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background: #0a4c35;
        }
        .erro {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .link {
            text-align: center;
            margin-top: 20px;
        }
        .link a {
            color: #0b5e42;
            text-decoration: none;
        }
        .link a:hover {
            text-decoration: underline;
        }
        .info {
            margin-top: 20px;
            padding: 10px;
            background: #e8f4f8;
            border-left: 4px solid #0b5e42;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="logo">🌿</div>
        <h1>Penomato</h1>
        
        <?php if ($erro): ?>
            <div class="erro"><?php echo $erro; ?></div>
        <?php endif; ?>
        
        <form action="/penomato_mvp/src/Controllers/auth/login_controlador.php" method="POST">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email_tentativa); ?>" required autofocus>
            
            <label>Senha</label>
            <input type="password" name="senha" required>
            
            <button type="submit">Entrar</button>
        </form>
        
        <div class="link">
            <a href="/penomato_mvp/cadastro">Criar nova conta</a>
        </div>
        
        <div class="info">
            <strong>Teste:</strong> nilton.bakargi@ufms.br / [sua senha]
        </div>
    </div>
</body>
</html>