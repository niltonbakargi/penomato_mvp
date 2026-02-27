<?php
// ============================================================
// ENTRAR COLABORADOR - PAINEL DO COLABORADOR
// ============================================================

// Iniciar sessão
session_start();

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /penomato_mvp/src/Views/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Pegar dados do usuário
$id_usuario = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Colaborador';
$tipo_usuario = $_SESSION['usuario_tipo'] ?? 'colaborador';
$email_usuario = $_SESSION['usuario_email'] ?? '';

// Mensagens de boas-vindas baseadas na hora
$hora = date('H');
if ($hora >= 5 && $hora < 12) {
    $saudacao = "Bom dia";
} elseif ($hora >= 12 && $hora < 18) {
    $saudacao = "Boa tarde";
} else {
    $saudacao = "Boa noite";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Colaborador - Penomato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f2e9 0%, #e8e2d4 100%);
            min-height: 100vh;
            padding: 30px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Cabeçalho */
        .header {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            border-left: 8px solid #0b5e42;
        }

        .header-info h1 {
            color: #0b5e42;
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .header-info p {
            color: #666;
            font-size: 1.1rem;
        }

        .header-info i {
            color: #0b5e42;
            margin-right: 8px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 20px;
            background: #f8f9fa;
            padding: 15px 25px;
            border-radius: 50px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            background: #0b5e42;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
        }

        .user-details h4 {
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .user-details span {
            color: #666;
            font-size: 0.9rem;
        }

        .user-details i {
            color: #0b5e42;
            margin-right: 5px;
        }

        .badge-colaborador {
            background: #0b5e42;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Cards de opções */
        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .option-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
            text-align: center;
            transition: all 0.3s;
            border: 2px solid transparent;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
            position: relative;
            overflow: hidden;
        }

        .option-card:hover {
            transform: translateY(-5px);
            border-color: #0b5e42;
            box-shadow: 0 20px 40px rgba(11,94,66,0.2);
        }

        .option-card:hover .option-icon {
            transform: scale(1.1);
        }

        .option-icon {
            font-size: 3.5rem;
            color: #0b5e42;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }

        .option-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .option-desc {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .option-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #ffc107;
            color: #2c3e50;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .option-badge.dev {
            background: #6c757d;
            color: white;
        }

        /* Estatísticas */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: #e8f5e9;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0b5e42;
            font-size: 1.5rem;
        }

        .stat-info h3 {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: #666;
            font-size: 0.9rem;
        }

        /* Atividades recentes */
        .recent-section {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }

        .recent-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: #0b5e42;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .recent-list {
            list-style: none;
        }

        .recent-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .recent-item:last-child {
            border-bottom: none;
        }

        .recent-icon {
            width: 40px;
            height: 40px;
            background: #f8f9fa;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0b5e42;
        }

        .recent-content {
            flex: 1;
        }

        .recent-content h4 {
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .recent-content p {
            font-size: 0.85rem;
            color: #666;
        }

        .recent-time {
            color: #999;
            font-size: 0.8rem;
        }

        /* Botões de ação */
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 12px 25px;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: #0b5e42;
            color: white;
            box-shadow: 0 4px 10px rgba(11,94,66,0.3);
        }

        .btn-primary:hover {
            background: #0a4c35;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(11,94,66,0.4);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            box-shadow: 0 4px 10px rgba(220,53,69,0.3);
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(220,53,69,0.4);
        }

        .footer {
            text-align: center;
            color: #718096;
            font-size: 0.9rem;
            margin-top: 40px;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }
            .user-profile {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- Cabeçalho com saudação -->
        <div class="header">
            <div class="header-info">
                <h1><i class="fas fa-hand-peace"></i> <?php echo $saudacao; ?>, <?php echo htmlspecialchars(explode(' ', $nome_usuario)[0]); ?>!</h1>
                <p><i class="fas fa-tachometer-alt"></i> Painel do Colaborador</p>
            </div>
            
            <div class="user-profile">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="user-details">
                    <h4><?php echo htmlspecialchars($nome_usuario); ?></h4>
                    <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($email_usuario); ?></span>
                    <div class="badge-colaborador" style="margin-top: 5px;">
                        <i class="fas fa-user-tag"></i> Colaborador
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estatísticas rápidas -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-pen-fancy"></i></div>
                <div class="stat-info">
                    <h3>0</h3>
                    <p>Características descritas</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-camera"></i></div>
                <div class="stat-info">
                    <h3>0</h3>
                    <p>Imagens enviadas</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-double"></i></div>
                <div class="stat-info">
                    <h3>0</h3>
                    <p>Revisões realizadas</p>
                </div>
            </div>
        </div>
        
        <!-- Grid de opções principais -->
        <div class="options-grid">
            
            <!-- Descrever características -->
            <a href="/penomato_mvp/src/Controllers/inserir_caracteristicas.php" class="option-card">
                <div class="option-icon"><i class="fas fa-pen-fancy"></i></div>
                <h3 class="option-title">DESCREVER CARACTERÍSTICAS</h3>
                <p class="option-desc">
                    Cadastre características morfológicas de espécies, incluindo folhas, flores, frutos e muito mais.
                </p>
            </a>
            
            <!-- Registrar imagens -->
            <a href="/penomato_mvp/src/Views/upload_imagem_views.php" class="option-card">
                <div class="option-icon"><i class="fas fa-camera"></i></div>
                <h3 class="option-title">REGISTRAR IMAGENS</h3>
                <p class="option-desc">
                    Envie exsicatas digitais e imagens de habitat para compor o acervo científico.
                </p>
            </a>
            
            <!-- Dados da Internet -->
            <a href="/penomato_mvp/src/Controllers/inserir_dados_internet.php" class="option-card">
                <div class="option-icon"><i class="fas fa-globe"></i></div>
                <h3 class="option-title">DADOS DA INTERNET</h3>
                <p class="option-desc">
                    Importe dados de fontes científicas via JSON (Flora do Brasil, Lorenzi, etc.).
                </p>
                <span class="option-badge"><i class="fas fa-star"></i> NOVO</span>
            </a>
            
            <!-- DEV (Em desenvolvimento) -->
            <div class="option-card" onclick="if(confirm('🚧 Esta funcionalidade está em desenvolvimento. Deseja ser notificado quando estiver pronta?')){}">
                <div class="option-icon"><i class="fas fa-code"></i></div>
                <h3 class="option-title">DEV</h3>
                <p class="option-desc">
                    Acesso a ferramentas de desenvolvimento e documentação técnica da API.
                </p>
                <span class="option-badge dev"><i class="fas fa-tools"></i> EM BREVE</span>
            </div>
            
            <!-- Contestar informação -->
            <div class="option-card" onclick="if(confirm('🚧 Esta funcionalidade está em desenvolvimento. Deseja ser notificado quando estiver pronta?')){}">
                <div class="option-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <h3 class="option-title">CONTESTAR INFORMAÇÃO</h3>
                <p class="option-desc">
                    Sugira correções para dados existentes ou reporte inconsistências.
                </p>
                <span class="option-badge dev"><i class="fas fa-tools"></i> EM BREVE</span>
            </div>
            
            <!-- Validar informações -->
            <div class="option-card" onclick="if(confirm('🚧 Esta funcionalidade está em desenvolvimento. Deseja ser notificado quando estiver pronta?')){}">
                <div class="option-icon"><i class="fas fa-check-circle"></i></div>
                <h3 class="option-title">VALIDAR INFORMAÇÕES</h3>
                <p class="option-desc">
                    Valide dados e imagens de outros colaboradores (requer permissão especial).
                </p>
                <span class="option-badge dev"><i class="fas fa-tools"></i> EM BREVE</span>
            </div>
        </div>
        
        <!-- Atividades recentes -->
        <div class="recent-section">
            <div class="recent-title">
                <i class="fas fa-history"></i>
                <span>Minhas atividades recentes</span>
            </div>
            
            <ul class="recent-list">
                <li class="recent-item">
                    <div class="recent-icon"><i class="fas fa-check-circle" style="color: #28a745;"></i></div>
                    <div class="recent-content">
                        <h4>Bem-vindo ao Penomato!</h4>
                        <p>Comece a contribuir com dados e imagens.</p>
                    </div>
                    <div class="recent-time">Agora</div>
                </li>
            </ul>
        </div>
        
        <!-- Botões de ação -->
        <div class="action-buttons">
            <a href="/penomato_mvp/index.php" class="btn-action btn-primary">
                <i class="fas fa-home"></i> Página Inicial
            </a>
            <a href="/penomato_mvp/src/Controllers/auth/logout_controlador.php" class="btn-action btn-danger" onclick="return confirm('Deseja realmente sair do sistema?')">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </div>
        
        <!-- Rodapé -->
        <div class="footer">
            <p>© 2026 Penomato - Em parceria com UEMS (Bioma Cerrado)</p>
        </div>
        
    </div>
</body>
</html>