<?php
/**
 * PÁGINA DE CADASTRO - PENOMATO MVP
 * 
 * Formulário para novos usuários se registrarem.
 */

// Incluir verificação de acesso
require_once __DIR__ . '/../../Controllers/auth/verificar_acesso.php';

// Proteger página para visitantes (se já logado, redireciona)
if (estaLogado()) {
    header('Location: /penomato_mvp/perfil');
    exit;
}

// Pegar mensagens da sessão
$mensagem_erro = $_SESSION['mensagem_erro'] ?? '';
$mensagem_sucesso = $_SESSION['mensagem_sucesso'] ?? '';
$dados_tentativa = $_SESSION['dados_cadastro'] ?? [];

// Limpar mensagens da sessão
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0b5e42 0%, #1a7a5a 100%);
            min-height: 100vh;
            padding: 30px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card-cadastro {
            max-width: 800px;
            margin: 0 auto;
            border-radius: 30px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .card-header {
            background: #0b5e42;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .card-header i {
            font-size: 4rem;
            margin-bottom: 15px;
        }
        .card-header h1 {
            font-size: 2rem;
            font-weight: 700;
        }
        .card-body {
            padding: 40px;
            background: white;
        }
        .btn-cadastrar {
            background: #0b5e42;
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-cadastrar:hover {
            background: #0a4c35;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(11,94,66,0.3);
        }
        .form-label {
            font-weight: 600;
            color: #333;
        }
        .texto-termos {
            font-size: 0.9rem;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card-cadastro">
            <div class="card-header">
                <i class="fas fa-leaf"></i>
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
                
                <form action="/penomato_mvp/src/Controllers/auth/cadastro_controlador.php" method="POST" enctype="multipart/form-data">
                    
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
                            <label class="form-label">Senha * (mínimo 8 caracteres)</label>
                            <input type="password" name="senha" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmar Senha *</label>
                            <input type="password" name="confirmar_senha" class="form-control" required>
                        </div>
                    </div>
                    
                    <!-- Perfil -->
                    <h4 class="mb-3 mt-4 text-success">👤 Perfil de Atuação</h4>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Perfil *</label>
                            <select name="tipo" class="form-control" required>
                                <option value="">Selecione...</option>
                                <option value="gestor" <?php echo ($dados_tentativa['tipo'] ?? '') == 'gestor' ? 'selected' : ''; ?>>Gestor</option>
                                <option value="colaborador" <?php echo ($dados_tentativa['tipo'] ?? '') == 'colaborador' ? 'selected' : ''; ?>>Colaborador</option>
                                <option value="revisor" <?php echo ($dados_tentativa['tipo'] ?? '') == 'revisor' ? 'selected' : ''; ?>>Revisor</option>
                                <option value="validador" <?php echo ($dados_tentativa['tipo'] ?? '') == 'validador' ? 'selected' : ''; ?>>Validador</option>
                                <option value="visitante" <?php echo ($dados_tentativa['tipo'] ?? '') == 'visitante' ? 'selected' : ''; ?>>Visitante</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Área (se colaborador)</label>
                            <select name="subtipo" class="form-control">
                                <option value="">Não se aplica</option>
                                <option value="identificador">Identificador</option>
                                <option value="coletor">Coletor</option>
                                <option value="fotografo">Fotógrafo</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Acadêmico -->
                    <h4 class="mb-3 mt-4 text-success">🎓 Informações Acadêmicas (opcional)</h4>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Instituição</label>
                            <input type="text" name="instituicao" class="form-control"
                                   value="<?php echo htmlspecialchars($dados_tentativa['instituicao'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lattes (URL)</label>
                            <input type="url" name="lattes" class="form-control"
                                   value="<?php echo htmlspecialchars($dados_tentativa['lattes'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ORCID</label>
                            <input type="text" name="orcid" class="form-control" placeholder="0000-0002-1825-0097"
                                   value="<?php echo htmlspecialchars($dados_tentativa['orcid'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <!-- Foto -->
                    <h4 class="mb-3 mt-4 text-success">📸 Foto de Perfil (opcional)</h4>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <small class="text-muted">Máximo 2MB. Formatos: JPG, PNG, GIF</small>
                        </div>
                    </div>
                    
                    <!-- Termos -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="termos" id="termos" required>
                                <label class="form-check-label texto-termos" for="termos">
                                    Li e aceito os <a href="#" class="text-success">Termos de Uso</a> e a 
                                    <a href="#" class="text-success">Política de Privacidade</a> *
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botão -->
                    <button type="submit" class="btn-cadastrar">
                        <i class="fas fa-user-plus me-2"></i>CRIAR MINHA CONTA
                    </button>
                    
                    <!-- Link para login -->
                    <div class="text-center mt-4">
                        Já tem uma conta? <a href="/penomato_mvp/login" class="text-success">Faça login</a>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>
</body>
</html>