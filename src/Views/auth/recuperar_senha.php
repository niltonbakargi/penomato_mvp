<?php
// ============================================================
// RECUPERAÇÃO DE SENHA - PENOMATO
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se já estiver logado, redireciona
if (isset($_SESSION['usuario_id'])) {
    header('Location: /penomato_mvp/src/Views/entrar_colaborador.php');
    exit;
}

$erro    = $_SESSION['mensagem_erro']    ?? '';
$sucesso = $_SESSION['mensagem_sucesso'] ?? '';
unset($_SESSION['mensagem_erro'], $_SESSION['mensagem_sucesso']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Penomato</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
            background:linear-gradient(135deg,#0b5e42 0%,#1a7a5a 100%);
            min-height:100vh; display:flex;
            align-items:center; justify-content:center; padding:20px;
        }
        .container { width:100%; max-width:450px; }
        .card {
            background:white; border-radius:20px;
            box-shadow:0 20px 40px rgba(0,0,0,0.3); overflow:hidden;
            animation:slideUp 0.4s ease-out;
        }
        @keyframes slideUp {
            from { opacity:0; transform:translateY(30px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .card-header {
            background:#0b5e42; color:white;
            padding:30px; text-align:center;
        }
        .card-header .icone {
            font-size:3rem; background:rgba(255,255,255,0.2);
            width:80px; height:80px; border-radius:50%; display:flex;
            align-items:center; justify-content:center;
            margin:0 auto 15px; border:3px solid white;
        }
        .card-header h1 { font-size:1.6rem; font-weight:700; margin-bottom:5px; }
        .card-header p  { color:rgba(255,255,255,0.85); font-size:0.9rem; }
        .card-body { padding:35px 40px; }
        .alert {
            padding:14px 16px; border-radius:10px;
            margin-bottom:20px; font-size:0.9rem;
            display:flex; align-items:flex-start; gap:10px;
        }
        .alert-error   { background:#f8d7da; color:#721c24; border-left:4px solid #dc3545; }
        .alert-success { background:#d4edda; color:#155724; border-left:4px solid #28a745; }
        .descricao {
            color:#555; font-size:0.9rem; line-height:1.6;
            margin-bottom:25px; text-align:center;
        }
        .form-group { margin-bottom:20px; }
        .form-group label {
            display:block; font-weight:600; color:#333;
            margin-bottom:8px; font-size:0.9rem;
        }
        .input-wrapper {
            display:flex; align-items:center;
            border:2px solid #e0e0e0; border-radius:10px; overflow:hidden;
            transition:border-color 0.3s;
        }
        .input-wrapper:focus-within { border-color:#0b5e42; }
        .input-wrapper .icone-input {
            padding:0 14px; color:#888; font-size:1rem;
            background:#f8f9fa; height:46px; display:flex; align-items:center;
        }
        .input-wrapper input {
            flex:1; border:none; outline:none;
            padding:12px 14px; font-size:0.95rem; color:#333;
        }
        .btn-submit {
            width:100%; background:#0b5e42; color:white; border:none;
            border-radius:10px; padding:13px; font-size:1rem;
            font-weight:700; cursor:pointer; transition:all 0.3s;
        }
        .btn-submit:hover { background:#0a4e36; transform:translateY(-2px); }
        .links {
            text-align:center; margin-top:20px;
            font-size:0.88rem; color:#666;
        }
        .links a { color:#0b5e42; text-decoration:none; font-weight:600; }
        .links a:hover { text-decoration:underline; }
        .links .sep { margin:0 8px; color:#ccc; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="icone"><i class="fas fa-key"></i></div>
            <h1>Recuperar Senha</h1>
            <p>Penomato — Sistema de Dados de Penas</p>
        </div>
        <div class="card-body">

            <?php if (!empty($erro)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $erro; ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($sucesso)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $sucesso; ?></span>
                </div>
            <?php else: ?>

                <p class="descricao">
                    Informe o e-mail cadastrado na sua conta.<br>
                    Enviaremos um link para você criar uma nova senha.
                </p>

                <form action="/penomato_mvp/src/Controllers/auth/recuperar_senha_controlador.php"
                      method="POST" novalidate>

                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope" style="color:#0b5e42;margin-right:6px;"></i>E-mail cadastrado</label>
                        <div class="input-wrapper">
                            <span class="icone-input"><i class="fas fa-at"></i></span>
                            <input type="email" id="email" name="email"
                                   placeholder="seu@email.com"
                                   required autofocus>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane" style="margin-right:8px;"></i>Enviar link de recuperação
                    </button>

                </form>

            <?php endif; ?>

            <div class="links">
                <a href="/penomato_mvp/src/Views/auth/login.php">
                    <i class="fas fa-arrow-left" style="margin-right:4px;"></i>Voltar ao login
                </a>
                <span class="sep">|</span>
                <a href="/penomato_mvp/src/Views/auth/cadastro.php">Criar conta</a>
            </div>

        </div>
    </div>
</div>
</body>
</html>
