<?php
// entrada_gestor.php - View do gestor
// As variáveis $total_especies, $em_revisao, $validadas, $total_usuarios
// já foram definidas no controlador
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Gestor - Penomato</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f0;
            padding: 30px;
            color: #1e2e1e;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        
        .header {
            background: #0b5e42;
            color: white;
            padding: 20px 30px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { font-size: 1.5em; }
        .user-badge {
            background-color: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 40px;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        .card-icon { font-size: 2.5em; margin-bottom: 10px; }
        .card-value { font-size: 2em; font-weight: bold; color: #0b5e42; }
        .card-label { color: #666; font-size: 0.9em; margin-top: 5px; }
        
        .section-title {
            font-size: 1.3em;
            color: #0b5e42;
            margin: 25px 0 15px 0;
            border-bottom: 2px solid #0b5e42;
            padding-bottom: 8px;
        }
        
        .nav-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        
        .nav-btn {
            background: white;
            border: 2px solid #0b5e42;
            border-radius: 10px;
            padding: 20px 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 600;
            color: #0b5e42;
        }
        .nav-btn:hover {
            background: #0b5e42;
            color: white;
        }
        .nav-icon { font-size: 2em; margin-bottom: 10px; }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 40px;
            padding: 12px 30px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 30px;
            width: 200px;
        }
        .logout-btn:hover { background: #c82333; }
        
        .footer { display: flex; justify-content: center; }
        
        @media (max-width: 768px) {
            .dashboard-grid { grid-template-columns: repeat(2, 1fr); }
            .nav-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <h1>📊 Painel do Gestor</h1>
            <div class="user-badge">
                👤 <?php echo htmlspecialchars($usuario_nome); ?> · <?php echo htmlspecialchars($usuario_instituicao); ?>
            </div>
        </div>

        <!-- DASHBOARD -->
        <div class="dashboard-grid">
            <div class="card">
                <div class="card-icon">🌳</div>
                <div class="card-value"><?php echo $total_especies; ?></div>
                <div class="card-label">Espécies</div>
            </div>
            <div class="card">
                <div class="card-icon">🔍</div>
                <div class="card-value"><?php echo $em_revisao; ?></div>
                <div class="card-label">Em revisão</div>
            </div>
            <div class="card">
                <div class="card-icon">✅</div>
                <div class="card-value"><?php echo $validadas; ?></div>
                <div class="card-label">Validadas</div>
            </div>
            <div class="card">
                <div class="card-icon">👥</div>
                <div class="card-value"><?php echo $total_usuarios; ?></div>
                <div class="card-label">Usuários</div>
            </div>
        </div>

        <!-- NAVEGAÇÃO ENTRE PERFIS -->
        <h2 class="section-title">🧭 Acesso aos Perfis</h2>
        
        <div class="nav-grid">
            <div class="nav-btn" onclick="window.location.href='/penomato_mvp/src/Controllers/controlador_colaborador.php'">
                <div class="nav-icon">👤</div>
                <div>Colaborador</div>
            </div>
            <div class="nav-btn" onclick="window.location.href='/penomato_mvp/src/Controllers/controlador_painel_revisor.php'">
                <div class="nav-icon">🔍</div>
                <div>Revisor</div>
            </div>
            <div class="nav-btn" onclick="window.location.href='/penomato_mvp/src/Controllers/controlador_autenticador.php'">
                <div class="nav-icon">✅</div>
                <div>Autenticador</div>
            </div>
            <div class="nav-btn" style="background:#0b5e42; color:white;" onclick="window.location.href='/penomato_mvp/src/Controllers/controlador_gestor.php'">
                <div class="nav-icon">📊</div>
                <div>Gestor</div>
            </div>
        </div>

        <!-- BOTÃO SAIR -->
        <div class="footer">
            <button class="logout-btn" onclick="window.location.href='/penomato_mvp/src/Controllers/auth/logout_controlador.php'">
                🚪 Sair
            </button>
        </div>
    </div>
</body>
</html>