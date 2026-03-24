<?php
/**
 * PÁGINA DE CADASTRO - PENOMATO MVP
 * Versão simplificada para o MVP
 */

if (!defined('APP_ENV')) {
    require_once __DIR__ . '/../../../config/app.php';
}

session_start();

// Se já estiver logado, redireciona
if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Controllers/controlador_gestor.php');
    exit;
}

// Pegar mensagens da sessão
$mensagem_erro = $_SESSION['mensagem_erro'] ?? '';
$mensagem_sucesso = $_SESSION['mensagem_sucesso'] ?? '';
$dados_tentativa = $_SESSION['dados_cadastro'] ?? [];

unset($_SESSION['mensagem_erro']);
unset($_SESSION['mensagem_sucesso']);
unset($_SESSION['dados_cadastro']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Penomato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
    <style>
        body {
            background: linear-gradient(135deg, var(--cor-primaria) 0%, var(--verde-600) 100%);
            min-height: 100vh;
            padding: var(--esp-8) 0;
        }
        .card-cadastro { max-width: 800px; margin: 0 auto; border-radius: var(--raio-2xl); box-shadow: 0 30px 60px rgba(0,0,0,0.3); overflow: hidden; }
        .card-header { background: var(--cor-primaria); color: var(--branco); padding: var(--esp-8); text-align: center; border-radius: 0; }
        .card-header h1 { font-size: var(--texto-3xl); font-weight: var(--peso-bold); color: var(--branco); }
        .card-header p { color: rgba(255,255,255,0.85); }
        .card-body { padding: var(--esp-10); background: var(--branco); }
        .btn-cadastrar { background: var(--cor-primaria); color: var(--branco); border: none; padding: var(--esp-4) var(--esp-10); font-size: var(--texto-lg); font-weight: var(--peso-semi); border-radius: var(--raio-full); width: 100%; cursor: pointer; transition: var(--transicao); }
        .btn-cadastrar:hover { background: var(--cor-primaria-hover); }
        .form-label { font-weight: var(--peso-semi); color: var(--cinza-800); }
        .texto-termos { font-size: var(--texto-sm); color: var(--cinza-500); }
        .text-success { color: var(--cor-primaria) !important; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card-cadastro">
            <div class="card-header">
                <h1>Criar Conta no Penomato</h1>
                <p>Preencha os dados abaixo para se cadastrar</p>
            </div>
            <div class="card-body">
                
                <?php if ($mensagem_erro): ?>
                <div class="alert alert-danger"><?php echo $mensagem_erro; ?></div>
                <?php endif; ?>
                
                <?php if ($mensagem_sucesso): ?>
                <div class="alert alert-success"><?php echo $mensagem_sucesso; ?></div>
                <?php endif; ?>
                
                <form action="/penomato_mvp/src/Controllers/auth/cadastro_controlador.php" method="POST">
                    
                    <!-- Dados básicos -->
                    <h4 class="mb-3 text-success">📋 Dados Básicos</h4>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Nome Completo *</label>
                            <input type="text" name="nome" class="form-control" required 
                                   value="<?php echo htmlspecialchars($dados_tentativa['nome'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">E-mail *</label>
                            <input type="email" name="email" class="form-control" required
                                   value="<?php echo htmlspecialchars($dados_tentativa['email'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmar E-mail *</label>
                            <input type="email" name="confirmar_email" class="form-control" required
                                   value="<?php echo htmlspecialchars($dados_tentativa['confirmar_email'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <!-- Senha -->
                    <h4 class="mb-3 mt-4 text-success">🔐 Segurança</h4>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Senha *</label>
                            <input type="password" name="senha" id="senha" class="form-control" required minlength="8">
                            <small class="text-muted">Mínimo 8 caracteres.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmar Senha *</label>
                            <input type="password" name="confirmar_senha" id="confirmar_senha" class="form-control" required>
                            <small id="senha-feedback" class="text-muted"></small>
                        </div>
                    </div>
                    
                    <!-- Perfil -->
                    <h4 class="mb-3 mt-4 text-success">👤 Perfil de Atuação</h4>
                    <?php
                    require_once __DIR__ . '/../../../config/banco_de_dados.php';
                    $gestor_existe = (bool) buscarUm("SELECT id FROM usuarios WHERE categoria = 'gestor' LIMIT 1", []);
                    $tipo_salvo = $dados_tentativa['tipo'] ?? '';
                    ?>

                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100 <?php echo $tipo_salvo === 'identificador' ? 'border-success' : ''; ?>" style="cursor:pointer;" onclick="document.getElementById('tipo_identificador').checked=true;selecionarPerfil('identificador')">
                                        <input type="radio" name="tipo" value="identificador" id="tipo_identificador" class="d-none" <?php echo $tipo_salvo === 'identificador' ? 'checked' : ''; ?> required>
                                        <div class="fw-bold mb-1">🌿 Colaborador Identificador</div>
                                        <small class="text-muted">Acesso imediato após confirmar o e-mail. Registra exemplares em campo e envia fotos das partes da planta.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100 <?php echo $tipo_salvo === 'especialista' ? 'border-success' : ''; ?>" style="cursor:pointer;" onclick="document.getElementById('tipo_especialista').checked=true;selecionarPerfil('especialista')">
                                        <input type="radio" name="tipo" value="especialista" id="tipo_especialista" class="d-none" <?php echo $tipo_salvo === 'especialista' ? 'checked' : ''; ?>>
                                        <div class="fw-bold mb-1">🔬 Colaborador Especialista</div>
                                        <small class="text-muted">Requer aprovação do gestor. Revisa e valida dados botânicos registrados pelos identificadores.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Instituição (opcional)</label>
                            <input type="text" name="instituicao" class="form-control"
                                   value="<?php echo htmlspecialchars($dados_tentativa['instituicao'] ?? ''); ?>"
                                   placeholder="Ex: UFMS, UEMS, EMBRAPA...">
                        </div>
                    </div>
                    
                    <!-- Termos -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="termos" id="termos" required>
                                <label class="form-check-label texto-termos" for="termos">
                                    Li e aceito os <a href="/penomato_mvp/src/Views/publico/termos.php" target="_blank">Termos de Uso</a> *
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botão -->
                    <button type="submit" class="btn-cadastrar">
                        CRIAR MINHA CONTA
                    </button>
                    
                    <!-- Link para login -->
                    <div class="text-center mt-4">
                        Já tem uma conta? <a href="/penomato_mvp/src/Views/auth/login.php" class="text-success">Faça login</a>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>
<script>
function selecionarPerfil(tipo) {
    document.querySelectorAll('.border.rounded').forEach(el => {
        el.classList.remove('border-success', 'bg-light');
    });
    const el = document.getElementById('tipo_' + tipo).closest('.border.rounded');
    el.classList.add('border-success', 'bg-light');
}

// Highlight inicial
document.addEventListener('DOMContentLoaded', function() {
    const checked = document.querySelector('input[name="tipo"]:checked');
    if (checked) selecionarPerfil(checked.value);

    // Validação de senha em tempo real
    document.getElementById('confirmar_senha').addEventListener('input', function() {
        const fb = document.getElementById('senha-feedback');
        if (this.value === document.getElementById('senha').value) {
            fb.textContent = '✔ Senhas conferem';
            fb.style.color = '#0b5e42';
        } else {
            fb.textContent = '✖ Senhas não conferem';
            fb.style.color = '#dc3545';
        }
    });
});
</script>
</body>
</html>