<?php
/**
 * CABEÇALHO PADRÃO DO PENOMATO MVP
 * 
 * Inclui o doctype, head, abertura do body e menu de navegação.
 * O menu é dinâmico baseado no tipo de usuário logado.
 * 
 * @package Penomato
 * @author Equipe Penomato
 * @version 1.0
 */

// ============================================================
// INICIALIZAÇÃO
// ============================================================

// Carregar configuração de ambiente (dev/prod) — define APP_BASE, ob_start em prod
if (!defined('APP_ENV')) {
    require_once __DIR__ . '/../../../config/app.php';
}

// Garantir que a sessão está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir verificação de acesso se não foi incluído ainda
if (!function_exists('estaLogado')) {
    require_once __DIR__ . '/../../Controllers/auth/verificar_acesso.php';
}

// Pegar mensagens para exibir
$mensagens = getMensagens();

// ============================================================
// CONFIGURAÇÕES DA PÁGINA (podem ser definidas antes de incluir)
// ============================================================

$titulo_pagina = $titulo_pagina ?? 'Penomato - Plataforma Colaborativa';
$descricao_pagina = $descricao_pagina ?? 'Documentação botânica colaborativa com validação científica';
$pagina_atual = basename($_SERVER['PHP_SELF']);

// ============================================================
// FUNÇÃO PARA DESTACAR ITEM DO MENU
// ============================================================

function menuAtivo($url, $pagina_atual) {
    $nome_arquivo = basename($url);
    return $nome_arquivo === $pagina_atual ? 'active' : '';
}

// ============================================================
// INÍCIO DO HTML
// ============================================================
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $descricao_pagina; ?>">
    <meta name="author" content="Penomato Team">
    <meta name="theme-color" content="#0b5e42">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/penomato_mvp/assets/imagens/favicon.ico">
    <link rel="apple-touch-icon" href="/penomato_mvp/assets/imagens/logo-apple.png">
    
    <title><?php echo $titulo_pagina; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha384-blOohCVdhjmtROpu8+CfTnUWham9nkX7P7OZQMst+RUnhtoY/9qemFAkIKOYxDI3" crossorigin="anonymous">
    
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS Customizado -->
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
    <link rel="stylesheet" href="/penomato_mvp/assets/css/bootstrap-ajustes.css">
    
    <style>
        /* Estilos do cabeçalho e menu */
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--cinza-50);
            padding-top: 80px; /* Espaço para o menu fixo */
        }
        
        /* Navbar principal */
        .navbar-penomato {
            background: linear-gradient(135deg, var(--cor-primaria) 0%, var(--verde-600) 100%);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 0.5rem 1rem;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            transition: all 0.3s;
        }
        
        .navbar-penomato.shrink {
            padding: 0.3rem 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .navbar-brand i {
            font-size: 2rem;
            background: white;
            color: var(--cor-primaria);
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s;
        }
        
        .navbar-brand:hover i {
            transform: rotate(15deg) scale(1.1);
        }
        
        .navbar-nav .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            margin: 0 2px;
            border-radius: 8px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .navbar-nav .nav-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        
        .navbar-nav .nav-link:hover {
            background: rgba(255,255,255,0.15);
            color: white !important;
            transform: translateY(-2px);
        }
        
        .navbar-nav .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white !important;
            font-weight: 600;
        }
        
        /* Dropdown do usuário */
        .user-dropdown .dropdown-toggle {
            background: rgba(255,255,255,0.15);
            border: none;
            color: white;
            border-radius: 30px;
            padding: 0.3rem 1rem 0.3rem 0.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .user-dropdown .dropdown-toggle:hover {
            background: rgba(255,255,255,0.25);
            transform: scale(1.02);
        }
        
        .user-dropdown .dropdown-toggle:after {
            display: none;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: white;
            color: var(--cor-primaria);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.1rem;
            border: 2px solid rgba(255,255,255,0.5);
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .user-type {
            font-size: 0.7rem;
            opacity: 0.8;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            border-radius: 12px;
            padding: 0.5rem;
            min-width: 240px;
            margin-top: 10px;
        }
        
        .dropdown-item {
            padding: 0.6rem 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
        }
        
        .dropdown-item i {
            width: 20px;
            color: var(--cor-primaria);
            font-size: 1rem;
        }
        
        .dropdown-item:hover {
            background: var(--cinza-50);
            transform: translateX(3px);
        }
        
        .dropdown-divider {
            margin: 0.5rem 0;
        }
        
        /* Badge de notificações */
        .badge-notification {
            position: absolute;
            top: 0;
            right: 0;
            background: #dc3545;
            color: white;
            font-size: 0.6rem;
            padding: 2px 5px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }
        
        /* Breadcrumb */
        .breadcrumb-custom {
            background: white;
            padding: 0.8rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-radius: 0;
        }
        
        .breadcrumb-custom a {
            color: var(--cor-primaria);
            text-decoration: none;
        }
        
        .breadcrumb-custom a:hover {
            text-decoration: underline;
        }
        
        /* Mensagens de feedback */
        .alert-container {
            max-width: 1200px;
            margin: 20px auto 0;
            padding: 0 20px;
        }
        
        .alert {
            border: none;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 1rem;
            animation: slideDown 0.3s ease-out;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert i {
            font-size: 1.2rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        /* Botão de fechar alerta */
        .alert .close {
            background: none;
            border: none;
            font-size: 1.5rem;
            line-height: 1;
            margin-left: auto;
            cursor: pointer;
            opacity: 0.5;
            transition: opacity 0.3s;
        }
        
        .alert .close:hover {
            opacity: 1;
        }
        
        /* Responsividade */
        @media (max-width: 991px) {
            .navbar-nav {
                background: white;
                border-radius: 10px;
                padding: 10px;
                margin-top: 10px;
            }
            
            .navbar-nav .nav-link {
                color: var(--cor-primaria) !important;
            }
            
            .navbar-nav .nav-link:hover {
                background: var(--cinza-50);
            }
            
            .user-dropdown {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>

<!-- ============================================================ -->
<!-- NAVBAR PRINCIPAL -->
<!-- ============================================================ -->

<nav class="navbar navbar-expand-lg navbar-penomato" id="mainNavbar">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand" href="/penomato_mvp/src/Views/publico/busca_caracteristicas.php">
            <i class="fas fa-leaf"></i>
            <span>Penomato</span>
        </a>
        
        <!-- Botão toggle para mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" 
                aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Menu colapsável -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <!-- Menu central (esquerda) -->
            <ul class="navbar-nav me-auto">
                <!-- Menu público - sempre visível -->
                <li class="nav-item">
                    <a class="nav-link <?php echo menuAtivo('/penomato_mvp/src/Views/publico/busca_caracteristicas.php', $pagina_atual); ?>" 
                       href="/penomato_mvp/src/Views/publico/busca_caracteristicas.php">
                        <i class="fas fa-search"></i> Buscar Espécies
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo menuAtivo('/penomato_mvp/src/Views/publico/flora_cerrado.php', $pagina_atual); ?>"
                       href="/penomato_mvp/src/Views/publico/flora_cerrado.php">
                        <i class="fas fa-seedling"></i> Flora do Cerrado
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo menuAtivo('/penomato_mvp/src/Views/publico/sobre.php', $pagina_atual); ?>"
                       href="/penomato_mvp/src/Views/publico/sobre.php">
                        <i class="fas fa-info-circle"></i> Sobre
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo menuAtivo('/penomato_mvp/src/Views/publico/contato.php', $pagina_atual); ?>" 
                       href="/penomato_mvp/src/Views/publico/contato.php">
                        <i class="fas fa-envelope"></i> Contato
                    </a>
                </li>
                
                <!-- Menu para COLABORADOR -->
                <?php if (estaLogado() && getTipoUsuario() === 'colaborador'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="colaboradorDropdown" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-plus-circle"></i> Contribuir
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="colaboradorDropdown">
                        <li>
                            <a class="dropdown-item" href="/penomato_mvp/src/Views/colaborador/cadastrar_caracteristicas.php">
                                <i class="fas fa-pen"></i> Cadastrar Características
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/penomato_mvp/src/Views/colaborador/upload_imagem.php">
                                <i class="fas fa-camera"></i> Upload de Imagens
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <!-- Menu para REVISOR -->
                <?php if (estaLogado() && in_array(getTipoUsuario(), ['revisor', 'gestor'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo menuAtivo('/penomato_mvp/src/Views/revisor/painel_revisor.php', $pagina_atual); ?>" 
                       href="/penomato_mvp/src/Views/revisor/painel_revisor.php">
                        <i class="fas fa-check-circle"></i> Painel Revisor
                        <?php if (temPendenciasRevisao()): ?>
                        <span class="badge-notification"><?php echo contarPendenciasRevisao(); ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endif; ?>
                
                <!-- Menu para GESTOR -->
                <?php if (estaLogado() && getTipoUsuario() === 'gestor'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="gestorDropdown" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cog"></i> Gestão
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="gestorDropdown">
                        <li>
                            <a class="dropdown-item" href="/penomato_mvp/src/Views/gestor/painel_gestor.php">
                                <i class="fas fa-chart-line"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/penomato_mvp/src/Views/gestor/gerenciar_usuarios.php">
                                <i class="fas fa-users"></i> Gerenciar Usuários
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/penomato_mvp/src/Views/gestor/relatorios.php">
                                <i class="fas fa-file-alt"></i> Relatórios
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/penomato_mvp/src/Views/gestor/configuracoes.php">
                                <i class="fas fa-sliders-h"></i> Configurações
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
            
            <!-- Menu direito (usuário) -->
            <ul class="navbar-nav ms-auto">
                <?php if (estaLogado()): ?>
                <!-- Usuário logado -->
                <li class="nav-item dropdown user-dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar">
                            <?php if (file_exists(__DIR__ . '/../../../uploads/fotos_perfil/' . $_SESSION['usuario_id'] . '.jpg')): ?>
                                <img src="/penomato_mvp/uploads/fotos_perfil/<?php echo $_SESSION['usuario_id']; ?>.jpg" 
                                     alt="<?php echo htmlspecialchars(getNomeUsuario()); ?>">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <div class="user-info">
                            <span class="user-name"><?php echo htmlspecialchars(explode(' ', getNomeUsuario())[0]); ?></span>
                            <span class="user-type"><?php echo traduzirTipo(getTipoUsuario()); ?></span>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="/penomato_mvp/src/Views/usuario/meu_perfil.php">
                                <i class="fas fa-user-circle"></i> Meu Perfil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/penomato_mvp/src/Views/usuario/minhas_contribuicoes.php">
                                <i class="fas fa-chart-bar"></i> Minhas Contribuições
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/penomato_mvp/src/Views/usuario/editar_perfil.php">
                                <i class="fas fa-edit"></i> Editar Perfil
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/penomato_mvp/src/Views/usuario/alterar_senha.php">
                                <i class="fas fa-key"></i> Alterar Senha
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="/penomato_mvp/src/Controllers/auth/logout_controlador.php">
                                <i class="fas fa-sign-out-alt"></i> Sair
                            </a>
                        </li>
                    </ul>
                </li>
                
                <?php else: ?>
                <!-- Usuário não logado -->
                <li class="nav-item">
                    <a class="nav-link" href="/penomato_mvp/src/Views/auth/login.php">
                        <i class="fas fa-sign-in-alt"></i> Entrar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/penomato_mvp/src/Views/auth/cadastro.php">
                        <i class="fas fa-user-plus"></i> Cadastrar
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- ============================================================ -->
<!-- BREADCRUMB (OPCIONAL) -->
<!-- ============================================================ -->

<?php if (isset($mostrar_breadcrumb) && $mostrar_breadcrumb): ?>
<div class="breadcrumb-custom">
    <div class="container-fluid">
        <a href="/penomato_mvp/src/Views/publico/busca_caracteristicas.php">Home</a>
        <?php if (isset($breadcrumb_itens) && is_array($breadcrumb_itens)): ?>
            <?php foreach ($breadcrumb_itens as $item): ?>
                <i class="fas fa-chevron-right mx-2" style="font-size: 0.8rem; color: #999;"></i>
                <?php if (isset($item['url'])): ?>
                    <a href="<?php echo $item['url']; ?>"><?php echo $item['nome']; ?></a>
                <?php else: ?>
                    <span><?php echo $item['nome']; ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- ============================================================ -->
<!-- CONTAINER PARA MENSAGENS -->
<!-- ============================================================ -->

<div class="alert-container">
    <?php if (!empty($mensagens['sucesso'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <?php echo $mensagens['sucesso']; ?>
            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($mensagens['erro'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $mensagens['erro']; ?>
            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($mensagens['alerta'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo $mensagens['alerta']; ?>
            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- ============================================================ -->
<!-- SCRIPTS (colocados aqui para carregar rápido) -->
<!-- ============================================================ -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

<script>
    // ============================================================
    // EFEITO DE ENCOLHER NAVBAR AO ROLAR
    // ============================================================
    
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('mainNavbar');
        if (window.scrollY > 50) {
            navbar.classList.add('shrink');
        } else {
            navbar.classList.remove('shrink');
        }
    });
    
    // ============================================================
    // FECHAR ALERTAS AUTOMATICAMENTE APÓS 5 SEGUNDOS
    // ============================================================
    
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // ============================================================
    // DESTACAR ITEM ATIVO NO MENU
    // ============================================================
    
    document.addEventListener('DOMContentLoaded', function() {
        const currentLocation = window.location.pathname;
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
        
        navLinks.forEach(link => {
            if (link.getAttribute('href') === currentLocation) {
                link.classList.add('active');
            }
        });
    });
</script>

<!-- ============================================================ -->
<?php
// ============================================================
// FUNÇÕES AUXILIARES PARA O MENU
// ============================================================

/**
 * Traduz o tipo de usuário para exibição
 */
function traduzirTipo($tipo) {
    $traducoes = [
        'gestor' => 'Gestor',
        'colaborador' => 'Colaborador',
        'revisor' => 'Revisor',
        'visitante' => 'Visitante'
    ];
    
    return $traducoes[$tipo] ?? $tipo;
}

/**
 * Conta pendências de revisão (exemplo)
 */
function contarPendenciasRevisao() {
    // Esta função será implementada depois
    // Por enquanto, retorna 0
    return 0;
}

/**
 * Verifica se há pendências de revisão
 */
function temPendenciasRevisao() {
    return contarPendenciasRevisao() > 0;
}

// ============================================================
// FIM DO CABEÇALHO
// ============================================================
?>