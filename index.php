<?php
/**
 * INDEX SIMPLIFICADO - PENOMATO MVP
 * Página inicial com botão para buscar espécies e botão SAIR quando logado
 */

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Função simples para verificar login
function estaLogadoSimples() {
    return isset($_SESSION['usuario_id']);
}

// Pegar a URL solicitada
$url = $_SERVER['REQUEST_URI'];
$url = str_replace('/penomato_mvp/', '', $url);
$url = explode('?', $url)[0];
$url = trim($url, '/');

// ============================================================
// ROTEAMENTO
// ============================================================

// Se for index.php ou vazio, mostrar página inicial
if ($url === 'index.php' || empty($url)) {
    $mostrar_inicial = true;
} else {
    // Mapeamento de rotas
    $rotas = [
        'busca' => '/src/Views/publico/busca_caracteristicas.php',
        'sobre' => '/src/Views/publico/sobre.php',
        'contato' => '/src/Views/publico/contato.php',
        'login' => '/src/Views/auth/login.php',
        'cadastro' => '/src/Views/auth/cadastro.php',
        'sair' => '/src/Controllers/auth/logout_controlador.php',
        'perfil' => '/src/Views/usuario/meu_perfil.php',
        'editar-perfil' => '/src/Views/usuario/editar_perfil.php',
        'alterar-senha' => '/src/Views/usuario/alterar_senha.php',
        'minhas-contribuicoes' => '/src/Views/usuario/minhas_contribuicoes.php',
        'nova-especie' => '/src/Views/colaborador/cadastrar_caracteristicas.php',
        'upload-imagem' => '/src/Views/colaborador/upload_imagem.php',
        'painel-revisor' => '/src/Views/revisor/painel_revisor.php',
        'painel-validador' => '/src/Views/validador/painel_validador.php',
        'painel-gestor' => '/src/Views/gestor/painel_gestor.php',
        'gerenciar-usuarios' => '/src/Views/gestor/gerenciar_usuarios.php'
    ];

    if (isset($rotas[$url])) {
        header('Location: /penomato_mvp' . $rotas[$url]);
        exit;
    }

    if (strpos($url, 'especie/') === 0) {
        $id = str_replace('especie/', '', $url);
        if (is_numeric($id)) {
            header('Location: /penomato_mvp/src/Views/publico/especie_detalhes.php?id=' . $id);
            exit;
        }
    }

    if (strpos($url, 'revisao/') === 0) {
        $id = str_replace('revisao/', '', $url);
        if (is_numeric($id)) {
            header('Location: /penomato_mvp/src/Views/revisor/artigo_revisao.php?id=' . $id);
            exit;
        }
    }

    $mostrar_404 = true;
}
?>

<?php if (isset($mostrar_inicial) && $mostrar_inicial): ?>
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
        
        .search-box {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            margin-bottom: 40px;
        }
        
        .search-box h2 {
            color: #0b5e42;
            font-size: 1.8rem;
            margin-bottom: 20px;
        }
        
        .search-box p {
            color: #4b5563;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        
        .btn-buscar {
            background: #0b5e42;
            color: white;
            border: none;
            padding: 18px 50px;
            font-size: 1.4rem;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 10px 25px rgba(11,94,66,0.4);
            display: inline-flex;
            align-items: center;
            gap: 15px;
        }
        
        .btn-buscar:hover {
            background: #0a4c35;
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(11,94,66,0.5);
        }
        
        .btn-buscar i {
            font-size: 1.8rem;
        }
        
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 40px 0;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .stat-item {
            text-align: center;
            flex: 1;
            min-width: 120px;
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #0b5e42;
            line-height: 1;
        }
        
        .stat-label {
            color: #4b5563;
            font-size: 1rem;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }
        
        .feature {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
            transition: transform 0.3s;
        }
        
        .feature:hover {
            transform: translateY(-5px);
        }
        
        .feature i {
            font-size: 2.5rem;
            color: #0b5e42;
            margin-bottom: 10px;
        }
        
        .feature h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: #1e293b;
        }
        
        .feature p {
            font-size: 0.85rem;
            color: #4b5563;
            margin: 0;
        }
        
        .auth-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
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
            transition: all 0.3s;
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
            transition: all 0.3s;
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
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-sair:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220,53,69,0.3);
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            color: #6b7280;
            font-size: 0.9rem;
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
                    </div>
                    
                    <!-- Corpo -->
                    <div class="home-body">
                        
                        <!-- Botão de Busca -->
                        <div class="search-box">
                            <h2>🔍 Buscar Espécies</h2>
                            <p>Encontre espécies por características morfológicas, nome científico ou popular</p>
                            
                            <a href="/penomato_mvp/busca" class="btn-buscar">
                                <i class="fas fa-search"></i>
                                BUSCAR ESPÉCIES
                            </a>
                        </div>
                        
                        <!-- Estatísticas -->
                        <div class="stats">
                            <div class="stat-item">
                                <div class="stat-value">12</div>
                                <div class="stat-label">Espécies</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">3</div>
                                <div class="stat-label">Colaboradores</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">45</div>
                                <div class="stat-label">Imagens</div>
                            </div>
                        </div>
                        
                        <!-- Características -->
                        <div class="features">
                            <div class="feature">
                                <i class="fas fa-tree"></i>
                                <h3>Espécies Florestais</h3>
                                <p>Documentação detalhada da flora brasileira</p>
                            </div>
                            <div class="feature">
                                <i class="fas fa-camera"></i>
                                <h3>Exsicatas Digitais</h3>
                                <p>Imagens padronizadas</p>
                            </div>
                            <div class="feature">
                                <i class="fas fa-check-double"></i>
                                <h3>Validação por Pares</h3>
                                <p>Revisão colaborativa</p>
                            </div>
                            <div class="feature">
                                <i class="fas fa-book"></i>
                                <h3>Referências</h3>
                                <p>Rastreabilidade científica</p>
                            </div>
                        </div>
                        
                        <!-- BOTÕES DE AUTENTICAÇÃO - VERSÃO CORRIGIDA COM SAIR -->
                        <?php if (!estaLogadoSimples()): ?>
                            <!-- USUÁRIO NÃO LOGADO -->
                            <div class="auth-buttons">
                                <a href="/penomato_mvp/login" class="btn-login">
                                    <i class="fas fa-sign-in-alt"></i> Entrar
                                </a>
                                <a href="/penomato_mvp/cadastro" class="btn-cadastro">
                                    <i class="fas fa-user-plus"></i> Cadastrar
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- USUÁRIO LOGADO - COM BOTÃO SAIR -->
                            <div class="auth-buttons">
                                <a href="/penomato_mvp/perfil" class="btn-login">
                                    <i class="fas fa-user"></i> Perfil
                                </a>
                                <a href="/penomato_mvp/nova-especie" class="btn-cadastro">
                                    <i class="fas fa-plus-circle"></i> Nova Espécie
                                </a>
                                <a href="/penomato_mvp/sair" class="btn-sair">
                                    <i class="fas fa-sign-out-alt"></i> Sair
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Rodapé -->
                        <div class="footer">
                            <p>© 2026 Penomato - Em parceria com UEMS</p>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php elseif (isset($mostrar_404) && $mostrar_404): ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página não encontrada - Penomato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #0b5e42 0%, #1a7a5a 100%); min-height: 100vh; display: flex; align-items: center; }
        .card { border: none; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
        .btn-success { background: #0b5e42; border: none; }
        .btn-success:hover { background: #0a4e36; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card p-5 text-center">
                    <i class="fas fa-leaf fa-4x text-success mb-3"></i>
                    <h1 class="display-1 fw-bold text-success">404</h1>
                    <h2 class="h4 mb-3">Página não encontrada</h2>
                    <p class="text-muted mb-4">
                        A página que você está procurando não existe no Penomato.
                    </p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <a href="/penomato_mvp/" class="btn btn-success">
                            <i class="fas fa-home"></i> Início
                        </a>
                        <a href="/penomato_mvp/busca" class="btn btn-outline-success">
                            <i class="fas fa-search"></i> Busca
                        </a>
                        <a href="/penomato_mvp/login" class="btn btn-outline-success">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </div>
                    
                    <hr>
                    
                    <p class="small text-muted">
                        <strong>URL:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php endif; ?>