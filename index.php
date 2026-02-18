<?php
/**
 * INDEX MVP - PENOMATO
 * Versão simplificada: apenas PESQUISAR, COLABORAR e GESTOR
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
        
        /* Cards principais */
        .main-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .option-card {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 35px 25px;
            text-align: center;
            transition: all 0.3s;
            border: 2px solid transparent;
            cursor: pointer;
        }
        
        .option-card:hover {
            transform: translateY(-5px);
            border-color: #0b5e42;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .option-icon {
            font-size: 3.5rem;
            color: #0b5e42;
            margin-bottom: 15px;
        }
        
        .option-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }
        
        .option-desc {
            color: #4b5563;
            margin-bottom: 20px;
            font-size: 1rem;
        }
        
        .btn-option {
            background: #0b5e42;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 40px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-option:hover {
            background: #0a4c35;
            transform: scale(1.05);
        }
        
        /* Sub-opções do Colaborador (aparece ao clicar) */
        .sub-options {
            display: none;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: #e9ecef;
            border-radius: 15px;
        }
        
        .sub-options.active {
            display: grid;
        }
        
        .sub-option {
            background: white;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            border: 1px solid #dee2e6;
        }
        
        .sub-option:hover {
            border-color: #0b5e42;
            background: #f8f9fa;
        }
        
        .sub-icon {
            font-size: 1.8rem;
            color: #0b5e42;
            margin-bottom: 8px;
        }
        
        .sub-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
        }
        
        .sub-desc {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        /* Acesso gestor */
        .gestor-area {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .gestor-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .gestor-icon {
            background: #0b5e42;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .btn-gestor {
            background: #0b5e42;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 40px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-gestor:hover {
            background: #0a4c35;
        }
        
        /* Botões de autenticação */
        .auth-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 30px 0 20px;
            flex-wrap: wrap;
        }
        
        .btn-login {
            background: white;
            color: #0b5e42;
            border: 2px solid #0b5e42;
            padding: 12px 25px;
            border-radius: 40px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-login:hover {
            background: #0b5e42;
            color: white;
        }
        
        .btn-cadastro {
            background: #0b5e42;
            color: white;
            border: 2px solid #0b5e42;
            padding: 12px 25px;
            border-radius: 40px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-cadastro:hover {
            background: white;
            color: #0b5e42;
        }
        
        .btn-sair {
            background: #dc3545;
            color: white;
            border: 2px solid #dc3545;
            padding: 12px 25px;
            border-radius: 40px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-sair:hover {
            background: #c82333;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .main-options {
                grid-template-columns: 1fr;
            }
            .sub-options {
                grid-template-columns: 1fr;
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
                        
                        <!-- OPÇÕES PRINCIPAIS -->
                        <div class="main-options">
                            <!-- Card PESQUISAR -->
                            <div class="option-card" onclick="window.location.href='/penomato_mvp/src/Views/publico/busca_caracteristicas.php'">
                                <div class="option-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                                <div class="option-title">PESQUISAR</div>
                                <div class="option-desc">
                                    Buscar espécies por características morfológicas, nome científico ou popular
                                </div>
                                <div class="btn-option">
                                    <i class="fas fa-search me-2"></i>Acessar Busca
                                </div>
                            </div>
                            
                            <!-- Card COLABORAR -->
                            <div class="option-card" id="colaborarCard">
                                <div class="option-icon">
                                    <i class="fas fa-hands-helping"></i>
                                </div>
                                <div class="option-title">COLABORAR</div>
                                <div class="option-desc">
                                    Contribua com dados, imagens, revisões ou contestações
                                </div>
                                <div class="btn-option" onclick="event.stopPropagation(); toggleSubOptions()">
                                    <i class="fas fa-arrow-down me-2"></i>Ver Opções
                                </div>
                            </div>
                        </div>
                        
                        <!-- SUB-OPÇÕES DO COLABORADOR -->
                        <div class="sub-options" id="subOptions">
                            <div class="sub-option" onclick="window.location.href='/penomato_mvp/src/Controllers/inserir_caracteristicas.php'">
                                <div class="sub-icon"><i class="fas fa-pen-fancy"></i></div>
                                <div class="sub-title">DESCREVER</div>
                                <div class="sub-desc">Cadastrar características de espécies</div>
                            </div>
                            
                            <div class="sub-option" onclick="window.location.href='/penomato_mvp/src/Views/upload_imagem_views.php'">
                                <div class="sub-icon"><i class="fas fa-camera"></i></div>
                                <div class="sub-title">REGISTRAR IMAGEM</div>
                                <div class="sub-desc">Enviar exsicatas digitais</div>
                            </div>
                            
                            <div class="sub-option" onclick="window.location.href='/penomato_mvp/src/Controllers/controlador_painel_revisor.php'">
                                <div class="sub-icon"><i class="fas fa-check-double"></i></div>
                                <div class="sub-title">REVISAR</div>
                                <div class="sub-desc">Validar dados de espécies</div>
                            </div>
                            
                            <div class="sub-option" onclick="if(confirm('Função em desenvolvimento')){}">
                                <div class="sub-icon"><i class="fas fa-exclamation-triangle"></i></div>
                                <div class="sub-title">CONTESTAR DADOS</div>
                                <div class="sub-desc">Sugerir correções (em breve)</div>
                            </div>
                        </div>
                        
                        <!-- ACESSO GESTOR (sempre visível) -->
                        <div class="gestor-area">
                            <div class="gestor-info">
                                <div class="gestor-icon">
                                    <i class="fas fa-crown"></i>
                                </div>
                                <div>
                                    <strong>Acesso Gestor</strong><br>
                                    <small>Administração e relatórios</small>
                                </div>
                            </div>
                            <a href="/penomato_mvp/src/Controllers/controlador_gestor.php" class="btn-gestor">
                                <i class="fas fa-sign-in-alt me-2"></i>Entrar como Gestor
                            </a>
                        </div>
                        
                        <!-- BOTÕES DE AUTENTICAÇÃO -->
                        <?php if (!estaLogadoSimples()): ?>
                            <!-- USUÁRIO NÃO LOGADO -->
                            <div class="auth-buttons">
                                <a href="/penomato_mvp/src/Views/auth/login.php" class="btn-login">
                                    <i class="fas fa-sign-in-alt"></i> Entrar
                                </a>
                                <a href="/penomato_mvp/src/Views/auth/cadastro.php" class="btn-cadastro">
                                    <i class="fas fa-user-plus"></i> Cadastrar
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- USUÁRIO LOGADO -->
                            <div class="auth-buttons">
                                <a href="/penomato_mvp/src/Views/usuario/meu_perfil.php" class="btn-login">
                                    <i class="fas fa-user"></i> Perfil
                                </a>
                                <a href="/penomato_mvp/src/Controllers/auth/logout_controlador.php" class="btn-sair">
                                    <i class="fas fa-sign-out-alt"></i> Sair
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

    <script>
        function toggleSubOptions() {
            document.getElementById('subOptions').classList.toggle('active');
        }
        
        // Fecha sub-opções se clicar fora
        document.addEventListener('click', function(event) {
            const subOptions = document.getElementById('subOptions');
            const colaborarCard = document.getElementById('colaborarCard');
            
            if (!colaborarCard.contains(event.target) && !subOptions.contains(event.target)) {
                subOptions.classList.remove('active');
            }
        });
    </script>
</body>
</html>