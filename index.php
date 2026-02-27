<?php
/**
 * INDEX MVP - PENOMATO
 * Versão simplificada: apenas PESQUISAR e AUTENTICAÇÃO
 */

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Função simples para verificar login
function estaLogadoSimples() {
    return isset($_SESSION['usuario_id']);
}

// Pegar tipo de usuário
$tipo_usuario = $_SESSION['usuario_tipo'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penomato - Plataforma Colaborativa de Espécies Florestais</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0b5e42 0%, #1a7a5a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .home-card {
            background: white;
            border-radius: 30px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .home-header {
            background: #0b5e42;
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .home-header i {
            font-size: 5rem;
            background: rgba(255,255,255,0.2);
            width: 120px;
            height: 120px;
            border-radius: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            border: 4px solid white;
        }
        
        .home-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .home-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .home-body {
            padding: 50px 40px;
        }
        
        /* Card de Pesquisa (único) */
        .search-card {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 45px 30px;
            text-align: center;
            transition: all 0.3s;
            border: 2px solid transparent;
            cursor: pointer;
            margin-bottom: 40px;
        }
        
        .search-card:hover {
            transform: translateY(-5px);
            border-color: #0b5e42;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .search-icon {
            font-size: 4.5rem;
            color: #0b5e42;
            margin-bottom: 20px;
        }
        
        .search-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 15px;
        }
        
        .search-desc {
            color: #4b5563;
            margin-bottom: 25px;
            font-size: 1.1rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .btn-search {
            background: #0b5e42;
            color: white;
            border: none;
            padding: 15px 50px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.2rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-search:hover {
            background: #0a4c35;
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(11,94,66,0.3);
        }
        
        /* Separador */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 30px 0;
            color: #9ca3af;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .divider span {
            padding: 0 15px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
        }
        
        /* Botões de autenticação */
        .auth-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin: 20px 0 30px;
            flex-wrap: wrap;
        }
        
        .btn-login {
            background: white;
            color: #0b5e42;
            border: 2px solid #0b5e42;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            flex: 1;
            min-width: 200px;
            justify-content: center;
        }
        
        .btn-login:hover {
            background: #0b5e42;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(11,94,66,0.2);
        }
        
        .btn-cadastro {
            background: #0b5e42;
            color: white;
            border: 2px solid #0b5e42;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            flex: 1;
            min-width: 200px;
            justify-content: center;
        }
        
        .btn-cadastro:hover {
            background: white;
            color: #0b5e42;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(11,94,66,0.2);
        }
        
        .btn-sair {
            background: #dc3545;
            color: white;
            border: 2px solid #dc3545;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            min-width: 200px;
            justify-content: center;
        }
        
        .btn-sair:hover {
            background: #c82333;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(220,53,69,0.2);
        }
        
        .user-greeting {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f0f9f4;
            border-radius: 50px;
            color: #0b5e42;
            font-weight: 600;
        }
        
        .user-greeting i {
            margin-right: 8px;
        }
        
        /* Link para o painel do colaborador */
        .painel-link {
            text-align: center;
            margin-top: 15px;
            padding: 10px;
            background: #e8f5e9;
            border-radius: 12px;
        }
        
        .painel-link a {
            color: #0b5e42;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            border-radius: 30px;
            transition: all 0.3s;
        }
        
        .painel-link a:hover {
            background: #0b5e42;
            color: white;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #9ca3af;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .auth-buttons {
                flex-direction: column;
            }
            .btn-login, .btn-cadastro, .btn-sair {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="home-card">
                    
                    <!-- Cabeçalho -->
                    <div class="home-header">
                        <i class="fas fa-leaf"></i>
                        <h1>Penomato</h1>
                        <p>Plataforma colaborativa para documentação botânica com validação científica</p>
                        <div style="margin-top: 10px; font-size: 0.9rem; background: rgba(255,255,255,0.1); display: inline-block; padding: 5px 20px; border-radius: 40px;">
                            🌳 Bioma: Cerrado (MVP)
                        </div>
                    </div>
                    
                    <!-- Corpo -->
                    <div class="home-body">
                        
                        <!-- Card de Pesquisa (único) -->
                        <div class="search-card" onclick="window.location.href='/penomato_mvp/src/Views/publico/busca_caracteristicas.php'">
                            <div class="search-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="search-title">PESQUISAR ESPÉCIES</div>
                            <div class="search-desc">
                                Busque por características morfológicas, nome científico ou nome popular
                            </div>
                            <div class="btn-search">
                                <i class="fas fa-search me-2"></i>Acessar Busca
                            </div>
                        </div>
                        
                        <!-- Separador -->
                        <div class="divider">
                            <span>ACESSO AO SISTEMA</span>
                        </div>
                        
                        <!-- BOTÕES DE AUTENTICAÇÃO -->
                        <?php if (!estaLogadoSimples()): ?>
                            <!-- USUÁRIO NÃO LOGADO -->
                            <div class="auth-buttons">
                                <a href="/penomato_mvp/src/Views/auth/login.php" class="btn-login">
                                    <i class="fas fa-sign-in-alt"></i> Entrar
                                </a>
                                <a href="/penomato_mvp/src/Views/auth/cadastro.php" class="btn-cadastro">
                                    <i class="fas fa-user-plus"></i> Cadastrar-se
                                </a>
                            </div>
                            
                            <div style="text-align: center; margin-top: 15px; font-size: 0.9rem; color: #6b7280;">
                                Faça login para contribuir com dados, imagens e revisões
                            </div>
                            
                        <?php else: ?>
                            <!-- USUÁRIO LOGADO -->
                            <div class="user-greeting">
                                <i class="fas fa-user-circle"></i> Olá, <strong><?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário'); ?></strong>!
                            </div>
                            
                            <div class="auth-buttons">
                                <a href="/penomato_mvp/src/Views/entrar_colaborador.php" class="btn-login">
                                    <i class="fas fa-tachometer-alt"></i> Painel do Colaborador
                                </a>
                                <a href="/penomato_mvp/src/Controllers/auth/logout_controlador.php" class="btn-sair">
                                    <i class="fas fa-sign-out-alt"></i> Sair
                                </a>
                            </div>
                            
                            <!-- Links rápidos para funcionalidades principais -->
                            <div class="painel-link">
                                <a href="/penomato_mvp/src/Controllers/inserir_dados_internet.php">
                                    <i class="fas fa-globe"></i> Importar dados da internet
                                </a>
                                <span style="margin: 0 10px; color: #d1d5db;">|</span>
                                <a href="/penomato_mvp/src/Controllers/inserir_caracteristicas.php">
                                    <i class="fas fa-pen-fancy"></i> Descrever espécie
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Rodapé -->
                        <div class="footer">
                            <p>© 2026 Penomato - Em parceria com UEMS (Bioma Cerrado)</p>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>