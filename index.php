<?php
/**
 * INDEX - PENOMATO MVP
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/src/Controllers/auth/verificar_acesso.php';

$logado      = sessaoValida();
$tipo        = getTipoUsuario();      // 'visitante' se não logado
$nome        = getNomeUsuario();      // 'Visitante' se não logado

// Painel destino baseado no tipo
$url_painel = ($tipo === 'gestor')
    ? '/penomato_mvp/src/Controllers/controlador_gestor.php'
    : '/penomato_mvp/src/Views/entrar_colaborador.php';
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
            from { opacity: 0; transform: translateY(40px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .home-header {
            background: #0b5e42;
            color: white;
            padding: 40px;
            text-align: center;
        }

        .home-header .logo-icon {
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

        .badge-bioma {
            margin-top: 12px;
            font-size: 0.9rem;
            background: rgba(255,255,255,0.15);
            display: inline-block;
            padding: 5px 20px;
            border-radius: 40px;
        }

        .home-body {
            padding: 50px 40px;
        }

        /* Card de pesquisa */
        .search-card {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 45px 30px;
            text-align: center;
            transition: all 0.3s;
            border: 2px solid transparent;
            cursor: pointer;
            margin-bottom: 40px;
            text-decoration: none;
            display: block;
            color: inherit;
        }

        .search-card:hover {
            transform: translateY(-5px);
            border-color: #0b5e42;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            color: inherit;
        }

        .search-icon {
            font-size: 4.5rem;
            color: #0b5e42;
            margin-bottom: 20px;
        }

        .search-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
        }

        .search-desc {
            color: #4b5563;
            margin-bottom: 25px;
            font-size: 1.05rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-search {
            background: #0b5e42;
            color: white;
            border: none;
            padding: 14px 50px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            display: inline-block;
            transition: all 0.3s;
        }

        .search-card:hover .btn-search {
            background: #0a4c35;
            box-shadow: 0 10px 20px rgba(11,94,66,0.3);
        }

        .reflora-card {
            background: #f0faf5;
            border-color: #a7d7bf;
            margin-top: -16px;
        }

        .reflora-card:hover {
            border-color: #2d8f63;
        }

        .reflora-card .search-icon {
            color: #2d8f63;
        }

        .btn-reflora {
            background: #2d8f63;
        }

        .reflora-card:hover .btn-reflora {
            background: #22704d;
            box-shadow: 0 10px 20px rgba(45,143,99,0.3);
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
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        /* Saudação */
        .user-greeting {
            text-align: center;
            margin-bottom: 20px;
            padding: 12px 20px;
            background: #f0f9f4;
            border-radius: 50px;
            color: #0b5e42;
            font-weight: 600;
        }

        /* Botões de autenticação */
        .auth-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 20px 0 25px;
            flex-wrap: wrap;
        }

        .btn-auth {
            padding: 14px 36px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            flex: 1;
            min-width: 180px;
            justify-content: center;
            border: 2px solid #0b5e42;
        }

        .btn-auth-outline {
            background: white;
            color: #0b5e42;
        }

        .btn-auth-outline:hover {
            background: #0b5e42;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(11,94,66,0.2);
        }

        .btn-auth-filled {
            background: #0b5e42;
            color: white;
        }

        .btn-auth-filled:hover {
            background: #0a4c35;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(11,94,66,0.3);
        }

        .btn-auth-danger {
            background: white;
            color: #dc3545;
            border-color: #dc3545;
        }

        .btn-auth-danger:hover {
            background: #dc3545;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(220,53,69,0.2);
        }

        /* Links rápidos (só para usuários logados) */
        .quick-links {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            padding: 12px 16px;
            background: #e8f5e9;
            border-radius: 14px;
            margin-top: 5px;
        }

        .quick-links a {
            color: #0b5e42;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 30px;
            transition: all 0.3s;
        }

        .quick-links a:hover {
            background: #0b5e42;
            color: white;
        }

        .quick-links .sep {
            color: #d1d5db;
            align-self: center;
        }

        .footer {
            text-align: center;
            margin-top: 35px;
            color: #9ca3af;
            font-size: 0.85rem;
            line-height: 1.6;
        }

        @media (max-width: 576px) {
            .home-body { padding: 30px 20px; }
            .home-header { padding: 30px 20px; }
            .home-header h1 { font-size: 2rem; }
            .search-card { padding: 30px 20px; }
            .auth-buttons { flex-direction: column; }
            .btn-auth { width: 100%; }
        }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="home-card">

                <!-- Cabeçalho -->
                <div class="home-header">
                    <div class="logo-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h1>Penomato</h1>
                    <p>Plataforma colaborativa para documentação botânica com validação científica</p>
                    <div class="badge-bioma">🌳 Bioma: Cerrado (MVP)</div>
                </div>

                <!-- Corpo -->
                <div class="home-body">

                    <!-- Card de pesquisa -->
                    <a href="/penomato_mvp/src/Views/publico/busca_caracteristicas.php" class="search-card">
                        <div class="search-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="search-title">PESQUISAR ESPÉCIES</div>
                        <div class="search-desc">
                            Busque por características morfológicas, nome científico ou nome popular
                        </div>
                        <span class="btn-search">
                            <i class="fas fa-search me-2"></i>Acessar Busca
                        </span>
                    </a>

                    <!-- Card REFLORA -->
                    <a href="/penomato_mvp/src/Views/publico/flora_cerrado.php" class="search-card reflora-card">
                        <div class="search-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="search-title">FLORA DO CERRADO</div>
                        <div class="search-desc">
                            Explore +14.000 espécies nativas com dados taxonômicos oficiais da base REFLORA — Jardim Botânico do Rio de Janeiro
                        </div>
                        <span class="btn-search btn-reflora">
                            <i class="fas fa-leaf me-2"></i>Explorar Flora
                        </span>
                    </a>

                    <!-- Separador -->
                    <div class="divider">
                        <span>ACESSO AO SISTEMA</span>
                    </div>

                    <?php if (!$logado): ?>

                        <!-- Não logado -->
                        <div class="auth-buttons">
                            <a href="/penomato_mvp/src/Views/auth/login.php" class="btn-auth btn-auth-outline">
                                <i class="fas fa-sign-in-alt"></i> Entrar
                            </a>
                            <a href="/penomato_mvp/src/Views/auth/cadastro.php" class="btn-auth btn-auth-filled">
                                <i class="fas fa-user-plus"></i> Cadastrar-se
                            </a>
                        </div>

                        <p class="text-center text-muted small">
                            Faça login para contribuir com dados, imagens e revisões
                        </p>

                    <?php else: ?>

                        <!-- Logado -->
                        <div class="user-greeting">
                            <i class="fas fa-user-circle me-2"></i>
                            Olá, <strong><?php echo htmlspecialchars($nome); ?></strong>!
                            <?php if ($tipo !== 'visitante'): ?>
                                <span class="ms-2 badge bg-success"><?php echo htmlspecialchars(ucfirst($tipo)); ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="auth-buttons">
                            <a href="<?php echo $url_painel; ?>" class="btn-auth btn-auth-outline">
                                <i class="fas fa-tachometer-alt"></i>
                                <?php echo $tipo === 'gestor' ? 'Painel do Gestor' : 'Meu Painel'; ?>
                            </a>
                            <a href="/penomato_mvp/src/Views/usuario/meu_perfil.php" class="btn-auth btn-auth-outline">
                                <i class="fas fa-user"></i> Meu Perfil
                            </a>
                            <a href="/penomato_mvp/src/Controllers/auth/logout_controlador.php" class="btn-auth btn-auth-danger">
                                <i class="fas fa-sign-out-alt"></i> Sair
                            </a>
                        </div>

                        <!-- Links rápidos por tipo -->
                        <?php if (in_array($tipo, ['colaborador', 'gestor'])): ?>
                        <div class="quick-links">
                            <a href="/penomato_mvp/src/Views/colaborador/upload_imagem.php">
                                <i class="fas fa-camera"></i> Upload de Imagens
                            </a>
                            <span class="sep">|</span>
                            <a href="/penomato_mvp/src/Views/colaborador/cadastrar_caracteristicas.php">
                                <i class="fas fa-pen-fancy"></i> Descrever Espécie
                            </a>
                        </div>
                        <?php elseif ($tipo === 'revisor'): ?>
                        <div class="quick-links">
                            <a href="/penomato_mvp/src/Views/revisor/painel_revisor.php">
                                <i class="fas fa-clipboard-check"></i> Fila de Revisão
                            </a>
                        </div>
                        <?php endif; ?>

                    <?php endif; ?>

                    <!-- Rodapé -->
                    <div class="footer">
                        © <?php echo date('Y'); ?> Penomato<br>
                        Desenvolvido como Projeto Integrador do Curso de Tecnologia da Informação da UFMS,
                        em parceria com o Grupo de Estudos em Botânica e Recursos Florestais da UEMS,
                        com orientação e apoio do Prof. Dr. Norton Hayd Rêgo.
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
