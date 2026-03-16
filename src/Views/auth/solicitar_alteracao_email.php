<?php
// ============================================================
// SOLICITAR ALTERAÇÃO DE E-MAIL - PENOMATO
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../Controllers/auth/verificar_acesso.php';

protegerPagina('Faça login para alterar seu e-mail.');

$usuario_id    = getIdUsuario();
$email_atual   = getEmailUsuario();
$usuario_nome  = getNomeUsuario();

$erro    = $_SESSION['mensagem_erro']    ?? '';
$sucesso = $_SESSION['mensagem_sucesso'] ?? '';
unset($_SESSION['mensagem_erro'], $_SESSION['mensagem_sucesso']);

// Gerar CSRF
if (!isset($_SESSION['csrf_token_email'])) {
    $_SESSION['csrf_token_email'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar E-mail - Penomato</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
            background:linear-gradient(135deg,#0b5e42 0%,#1a7a5a 100%);
            min-height:100vh; display:flex;
            align-items:center; justify-content:center; padding:20px;
        }
        .container { width:100%; max-width:480px; }
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
            padding:28px 30px; text-align:center;
        }
        .card-header .icone {
            font-size:2.5rem; background:rgba(255,255,255,0.2);
            width:70px; height:70px; border-radius:50%; display:flex;
            align-items:center; justify-content:center;
            margin:0 auto 12px; border:3px solid white;
        }
        .card-header h1 { font-size:1.5rem; font-weight:700; margin-bottom:4px; }
        .card-header p  { color:rgba(255,255,255,0.85); font-size:0.85rem; }
        .card-body { padding:32px 36px; }
        .alert {
            padding:13px 15px; border-radius:10px;
            margin-bottom:18px; font-size:0.88rem;
            display:flex; align-items:flex-start; gap:10px;
        }
        .alert-error   { background:#f8d7da; color:#721c24; border-left:4px solid #dc3545; }
        .alert-success { background:#d4edda; color:#155724; border-left:4px solid #28a745; }
        .email-atual {
            background:#f0f9f5; border:1px solid #b2dfce; border-radius:10px;
            padding:12px 15px; margin-bottom:22px; font-size:0.88rem; color:#0b5e42;
        }
        .email-atual strong { display:block; margin-bottom:3px; color:#333; }
        .form-group { margin-bottom:18px; }
        .form-group label {
            display:block; font-weight:600; color:#333;
            margin-bottom:7px; font-size:0.88rem;
        }
        .input-wrapper {
            display:flex; align-items:center;
            border:2px solid #e0e0e0; border-radius:10px; overflow:hidden;
            transition:border-color 0.3s;
        }
        .input-wrapper:focus-within { border-color:#0b5e42; }
        .input-wrapper .ic {
            padding:0 13px; color:#888; background:#f8f9fa;
            height:44px; display:flex; align-items:center; font-size:0.95rem;
        }
        .input-wrapper input {
            flex:1; border:none; outline:none;
            padding:11px 13px; font-size:0.92rem; color:#333;
        }
        .toggle-pw {
            padding:0 13px; cursor:pointer; color:#888;
            height:44px; display:flex; align-items:center;
            background:#f8f9fa; transition:color 0.2s;
        }
        .toggle-pw:hover { color:#0b5e42; }
        .aviso {
            background:#fff8e1; border:1px solid #ffe082; border-radius:8px;
            padding:11px 14px; font-size:0.83rem; color:#7a5800; margin-bottom:20px;
            display:flex; gap:9px; align-items:flex-start;
        }
        .btn-submit {
            width:100%; background:#0b5e42; color:white; border:none;
            border-radius:10px; padding:12px; font-size:0.95rem;
            font-weight:700; cursor:pointer; transition:all 0.3s;
        }
        .btn-submit:hover { background:#0a4e36; transform:translateY(-2px); }
        .links {
            text-align:center; margin-top:18px;
            font-size:0.85rem; color:#666;
        }
        .links a { color:#0b5e42; text-decoration:none; font-weight:600; }
        .links a:hover { text-decoration:underline; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="icone"><i class="fas fa-envelope"></i></div>
            <h1>Alterar E-mail</h1>
            <p>Olá, <?php echo htmlspecialchars($usuario_nome); ?></p>
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
            <?php endif; ?>

            <div class="email-atual">
                <strong>E-mail atual:</strong>
                <?php echo htmlspecialchars($email_atual); ?>
            </div>

            <div class="aviso">
                <i class="fas fa-info-circle" style="margin-top:2px;"></i>
                <span>Um link de confirmação será enviado para o <strong>novo e-mail</strong>.
                A alteração só é efetivada após a confirmação.</span>
            </div>

            <form action="/penomato_mvp/src/Controllers/auth/solicitar_alteracao_email_controlador.php"
                  method="POST" novalidate>

                <input type="hidden" name="csrf_token"
                       value="<?php echo $_SESSION['csrf_token_email']; ?>">

                <div class="form-group">
                    <label for="novo_email">
                        <i class="fas fa-at" style="color:#0b5e42;margin-right:5px;"></i>Novo E-mail *
                    </label>
                    <div class="input-wrapper">
                        <span class="ic"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="novo_email" name="novo_email"
                               placeholder="novo@email.com" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmar_novo_email">
                        <i class="fas fa-at" style="color:#0b5e42;margin-right:5px;"></i>Confirmar Novo E-mail *
                    </label>
                    <div class="input-wrapper">
                        <span class="ic"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="confirmar_novo_email" name="confirmar_novo_email"
                               placeholder="novo@email.com" required>
                    </div>
                    <small id="emailMatch" style="font-size:0.8rem;"></small>
                </div>

                <div class="form-group">
                    <label for="senha_atual">
                        <i class="fas fa-lock" style="color:#0b5e42;margin-right:5px;"></i>Senha Atual (confirmação) *
                    </label>
                    <div class="input-wrapper">
                        <span class="ic"><i class="fas fa-key"></i></span>
                        <input type="password" id="senha_atual" name="senha_atual"
                               placeholder="Digite sua senha atual" required>
                        <span class="toggle-pw" onclick="togglePw('senha_atual','iconPw')">
                            <i class="fas fa-eye" id="iconPw"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane" style="margin-right:8px;"></i>Enviar link de confirmação
                </button>

            </form>

            <div class="links">
                <a href="/penomato_mvp/src/Views/usuario/editar_perfil.php">
                    <i class="fas fa-arrow-left" style="margin-right:4px;"></i>Voltar ao perfil
                </a>
            </div>

        </div>
    </div>
</div>
<script>
    function togglePw(id, iconId) {
        const c = document.getElementById(id);
        const i = document.getElementById(iconId);
        c.type = c.type === 'password' ? 'text' : 'password';
        i.classList.toggle('fa-eye');
        i.classList.toggle('fa-eye-slash');
    }

    const novoEmail     = document.getElementById('novo_email');
    const confirmarEmail = document.getElementById('confirmar_novo_email');
    const matchMsg      = document.getElementById('emailMatch');

    function checarEmail() {
        if (!confirmarEmail.value) { matchMsg.textContent = ''; return; }
        if (novoEmail.value === confirmarEmail.value) {
            matchMsg.innerHTML = '<i class="fas fa-check-circle" style="color:#28a745"></i> E-mails conferem';
            matchMsg.style.color = '#155724';
        } else {
            matchMsg.innerHTML = '<i class="fas fa-times-circle" style="color:#dc3545"></i> E-mails não conferem';
            matchMsg.style.color = '#721c24';
        }
    }

    novoEmail.addEventListener('input', checarEmail);
    confirmarEmail.addEventListener('input', checarEmail);
</script>
</body>
</html>
