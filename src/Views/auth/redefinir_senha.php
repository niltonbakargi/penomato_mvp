<?php
// ============================================================
// REDEFINIÇÃO DE SENHA - PENOMATO
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../Controllers/auth/verificar_acesso.php';
require_once __DIR__ . '/../../../config/banco_de_dados.php';

// Se já estiver logado, redireciona
if (isset($_SESSION['usuario_id'])) {
    header('Location: /penomato_mvp/src/Views/entrar_colaborador.php');
    exit;
}

// ============================================================
// VALIDAR TOKEN VIA GET
// ============================================================
$token = trim($_GET['token'] ?? '');

$token_valido  = false;
$erro_token    = '';

if (empty($token) || !ctype_xdigit($token) || strlen($token) !== 64) {
    $erro_token = "Link inválido ou mal formado.";
} else {
    $registro = buscarUm(
        "SELECT id, expira_em, usado FROM tokens_recuperacao_senha
         WHERE token = :token LIMIT 1",
        [':token' => $token]
    );

    if (!$registro) {
        $erro_token = "Link não encontrado. Solicite um novo.";
    } elseif ($registro['usado']) {
        $erro_token = "Este link já foi utilizado. Solicite um novo.";
    } elseif (strtotime($registro['expira_em']) < time()) {
        $erro_token = "Este link expirou. Solicite um novo.";
    } else {
        $token_valido = true;
    }
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
    <title>Redefinir Senha - Penomato</title>
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
            padding:0 14px; color:#888; background:#f8f9fa;
            height:46px; display:flex; align-items:center;
        }
        .input-wrapper input {
            flex:1; border:none; outline:none;
            padding:12px 14px; font-size:0.95rem; color:#333;
        }
        .toggle-senha {
            padding:0 14px; cursor:pointer; color:#888;
            height:46px; display:flex; align-items:center;
            background:#f8f9fa; transition:color 0.2s;
        }
        .toggle-senha:hover { color:#0b5e42; }
        /* Força da senha */
        .forca-barra {
            height:5px; border-radius:5px; background:#e0e0e0;
            overflow:hidden; margin-top:8px;
        }
        .forca-barra-fill {
            height:100%; width:0; transition:all 0.3s;
        }
        .forca-texto { font-size:0.78rem; text-align:right; margin-top:4px; }
        .fraca  { background:#dc3545; width:33%; }
        .media  { background:#ffc107; width:66%; }
        .forte  { background:#28a745; width:100%; }
        /* botão */
        .btn-submit {
            width:100%; background:#0b5e42; color:white; border:none;
            border-radius:10px; padding:13px; font-size:1rem;
            font-weight:700; cursor:pointer; transition:all 0.3s;
        }
        .btn-submit:hover { background:#0a4e36; transform:translateY(-2px); }
        .links { text-align:center; margin-top:20px; font-size:0.88rem; color:#666; }
        .links a { color:#0b5e42; text-decoration:none; font-weight:600; }
        .links a:hover { text-decoration:underline; }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="icone"><i class="fas fa-lock-open"></i></div>
            <h1>Nova Senha</h1>
            <p>Penomato — Sistema de Dados de Penas</p>
        </div>
        <div class="card-body">

            <?php if (!empty($erro)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $erro; ?></span>
                </div>
            <?php endif; ?>

            <?php if (!$token_valido): ?>

                <div class="alert alert-error">
                    <i class="fas fa-times-circle"></i>
                    <span><?php echo htmlspecialchars($erro_token); ?></span>
                </div>
                <div class="links">
                    <a href="/penomato_mvp/src/Views/auth/recuperar_senha.php">
                        <i class="fas fa-redo" style="margin-right:4px;"></i>Solicitar novo link
                    </a>
                </div>

            <?php else: ?>

                <form action="/penomato_mvp/src/Controllers/auth/redefinir_senha_controlador.php"
                      method="POST" id="formRedefinir" novalidate>

                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                    <div class="form-group">
                        <label for="nova_senha">
                            <i class="fas fa-lock" style="color:#0b5e42;margin-right:6px;"></i>Nova Senha
                        </label>
                        <div class="input-wrapper">
                            <span class="icone-input"><i class="fas fa-key"></i></span>
                            <input type="password" id="nova_senha" name="nova_senha"
                                   placeholder="Mínimo 8 caracteres" required autofocus>
                            <span class="toggle-senha" onclick="toggle('nova_senha','icon1')">
                                <i class="fas fa-eye" id="icon1"></i>
                            </span>
                        </div>
                        <div class="forca-barra"><div class="forca-barra-fill" id="forcaFill"></div></div>
                        <div class="forca-texto" id="forcaTexto"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirmar_senha">
                            <i class="fas fa-lock" style="color:#0b5e42;margin-right:6px;"></i>Confirmar Nova Senha
                        </label>
                        <div class="input-wrapper">
                            <span class="icone-input"><i class="fas fa-key"></i></span>
                            <input type="password" id="confirmar_senha" name="confirmar_senha"
                                   placeholder="Digite a senha novamente" required>
                            <span class="toggle-senha" onclick="toggle('confirmar_senha','icon2')">
                                <i class="fas fa-eye" id="icon2"></i>
                            </span>
                        </div>
                        <small id="matchMsg" style="font-size:0.8rem;"></small>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save" style="margin-right:8px;"></i>Salvar nova senha
                    </button>

                </form>

            <?php endif; ?>

            <div class="links">
                <a href="/penomato_mvp/src/Views/auth/login.php">
                    <i class="fas fa-arrow-left" style="margin-right:4px;"></i>Voltar ao login
                </a>
            </div>

        </div>
    </div>
</div>
<script>
    function toggle(campoId, iconeId) {
        const c = document.getElementById(campoId);
        const i = document.getElementById(iconeId);
        c.type = c.type === 'password' ? 'text' : 'password';
        i.classList.toggle('fa-eye');
        i.classList.toggle('fa-eye-slash');
    }

    const novaSenha     = document.getElementById('nova_senha');
    const confirmarSenha = document.getElementById('confirmar_senha');

    if (novaSenha) {
        novaSenha.addEventListener('input', function () {
            const s = this.value;
            const fill = document.getElementById('forcaFill');
            const txt  = document.getElementById('forcaTexto');
            let forca = 0;
            if (s.length >= 8)                        forca++;
            if (s.match(/[a-z]/) && s.match(/[A-Z]/)) forca++;
            if (s.match(/[0-9]/))                     forca++;
            if (s.match(/[^a-zA-Z0-9]/))              forca++;

            fill.className = 'forca-barra-fill';
            if (!s.length)      { fill.style.width='0'; txt.textContent=''; }
            else if (forca <= 2){ fill.classList.add('fraca'); txt.textContent='Fraca'; txt.style.color='#dc3545'; }
            else if (forca == 3){ fill.classList.add('media'); txt.textContent='Média'; txt.style.color='#856404'; }
            else                { fill.classList.add('forte'); txt.textContent='Forte'; txt.style.color='#155724'; }
        });
    }

    if (confirmarSenha) {
        confirmarSenha.addEventListener('input', function () {
            const msg = document.getElementById('matchMsg');
            if (!this.value) { msg.textContent=''; return; }
            if (this.value === novaSenha.value) {
                msg.innerHTML='<i class="fas fa-check-circle" style="color:#28a745"></i> Senhas conferem';
                msg.style.color='#155724';
            } else {
                msg.innerHTML='<i class="fas fa-times-circle" style="color:#dc3545"></i> Senhas não conferem';
                msg.style.color='#721c24';
            }
        });
    }
</script>
</body>
</html>
