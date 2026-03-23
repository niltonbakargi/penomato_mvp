<?php
// ============================================================
// RECUPERAÇÃO DE SENHA - PENOMATO
// ============================================================

if (!defined('APP_ENV')) {
    require_once __DIR__ . '/../../../config/app.php';
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se já estiver logado, redireciona
if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/entrar_colaborador.php');
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
    <style>
        body {
            background: linear-gradient(135deg, var(--cor-primaria) 0%, var(--verde-600) 100%);
            min-height: 100vh; display: flex;
            align-items: center; justify-content: center; padding: var(--esp-5);
        }
        .container { width: 100%; max-width: 450px; }
        .card { background: var(--branco); border-radius: var(--raio-2xl); box-shadow: 0 20px 40px rgba(0,0,0,0.3); overflow: hidden; animation: slideUp 0.4s ease-out; }
        .card-header { background: var(--cor-primaria); color: var(--branco); padding: var(--esp-8); text-align: center; }
        .card-header .icone { font-size: var(--texto-3xl); background: rgba(255,255,255,0.2); width: 80px; height: 80px; border-radius: var(--raio-full); display: flex; align-items: center; justify-content: center; margin: 0 auto var(--esp-4); border: 3px solid var(--branco); }
        .card-header h1 { font-size: var(--texto-2xl); font-weight: var(--peso-bold); color: var(--branco); }
        .card-header p  { color: rgba(255,255,255,0.85); font-size: var(--texto-sm); }
        .card-body { padding: var(--esp-8) var(--esp-10); }
        .descricao { color: var(--cinza-500); font-size: var(--texto-sm); line-height: 1.6; margin-bottom: var(--esp-6); text-align: center; }
        .form-group { margin-bottom: var(--esp-5); }
        .form-group label { display: block; font-weight: var(--peso-semi); color: var(--cinza-800); margin-bottom: var(--esp-2); font-size: var(--texto-sm); }
        .input-wrapper { display: flex; align-items: center; border: 2px solid var(--cinza-200); border-radius: var(--raio-md); overflow: hidden; transition: var(--transicao); }
        .input-wrapper:focus-within { border-color: var(--cor-primaria); }
        .input-wrapper .icone-input { padding: 0 var(--esp-4); color: var(--cinza-400); background: var(--cinza-50); height: 46px; display: flex; align-items: center; }
        .input-wrapper input { flex: 1; border: none; outline: none; padding: var(--esp-3) var(--esp-4); font-size: var(--texto-sm); color: var(--cinza-800); font-family: var(--fonte-principal); }
        .btn-submit { width: 100%; background: var(--cor-primaria); color: var(--branco); border: none; border-radius: var(--raio-md); padding: var(--esp-3); font-size: var(--texto-md); font-weight: var(--peso-bold); cursor: pointer; transition: var(--transicao); }
        .btn-submit:hover { background: var(--cor-primaria-hover); transform: translateY(-2px); }
        .links { text-align: center; margin-top: var(--esp-5); font-size: var(--texto-xs); color: var(--cinza-500); }
        .links a { color: var(--cor-primaria); text-decoration: none; font-weight: var(--peso-semi); }
        .links a:hover { text-decoration: underline; }
        .links .sep { margin: 0 var(--esp-2); color: var(--cinza-300); }
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
                <div class="alerta--perigo">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $erro; ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($sucesso)): ?>
                <div class="alerta--sucesso">
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
                        <label for="email"><i class="fas fa-envelope" style="color:var(--cor-primaria);margin-right:6px;"></i>E-mail cadastrado</label>
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
