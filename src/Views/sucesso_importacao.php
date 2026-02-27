<?php
// ================================================
// PÁGINA DE SUCESSO - IMPORTAÇÃO CONCLUÍDA
// ================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$servidor = "127.0.0.1";
$usuario_db = "root";
$senha_db = "";
$banco = "penomato";

// ================================================
// VERIFICAR SE USUÁRIO ESTÁ LOGADO
// ================================================
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../Views/auth/login.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';

// ================================================
// VERIFICAR ID DA ESPÉCIE
// ================================================
$especie_id = isset($_GET['especie_id']) ? (int)$_GET['especie_id'] : 0;
$total_imagens = isset($_GET['imagens']) ? (int)$_GET['imagens'] : 0;

if ($especie_id <= 0) {
    header("Location: ../Controllers/inserir_dados_internet.php");
    exit;
}

// ================================================
// CONECTAR AO BANCO
// ================================================
$conexao = new mysqli($servidor, $usuario_db, $senha_db, $banco);

if ($conexao->connect_error) {
    die("Erro de conexão: " . $conexao->connect_error);
}

$conexao->set_charset("utf8mb4");

// Buscar dados da espécie
$sql_especie = "SELECT id, nome_cientifico, status, status_imagens 
                FROM especies_administrativo 
                WHERE id = ?";
$stmt = $conexao->prepare($sql_especie);
$stmt->bind_param("i", $especie_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Espécie não encontrada.");
}

$especie = $resultado->fetch_assoc();
$stmt->close();

// Buscar contagem de imagens por parte
$sql_imagens = "SELECT parte_planta, COUNT(*) as total 
                FROM especies_imagens 
                WHERE especie_id = ? 
                GROUP BY parte_planta";
$stmt = $conexao->prepare($sql_imagens);
$stmt->bind_param("i", $especie_id);
$stmt->execute();
$resultado = $stmt->get_result();

$imagens_por_parte = [];
while ($row = $resultado->fetch_assoc()) {
    $imagens_por_parte[$row['parte_planta']] = $row['total'];
}

$stmt->close();
$conexao->close();

$total_imagens_banco = array_sum($imagens_por_parte);

// ================================================
// PARTES PARA EXIBIÇÃO
// ================================================
$partes = [
    'folha' => '🍃 Folha',
    'flor' => '🌸 Flor',
    'fruto' => '🍎 Fruto',
    'caule' => '🌿 Caule',
    'semente' => '🌱 Semente',
    'habito' => '🌳 Hábito',
    'exsicata_completa' => '📋 Exsicata',
    'detalhe' => '🔍 Detalhe'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penomato - Importação Concluída</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f2e9;
            padding: 30px 20px;
            color: #2c3e50;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
        }

        /* Informações do usuário */
        .user-info {
            position: absolute;
            top: 20px;
            right: 20px;
            background: white;
            padding: 10px 25px;
            border-radius: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
            z-index: 100;
        }
        
        .user-info i {
            color: #0b5e42;
            font-size: 1.2rem;
        }
        
        .user-name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .user-logout {
            color: #dc3545;
            text-decoration: none;
            font-size: 0.9rem;
            padding: 5px 10px;
            border-radius: 20px;
            transition: all 0.2s;
        }
        
        .user-logout:hover {
            background: #dc3545;
            color: white;
        }

        .success-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 50px 40px;
            text-align: center;
            border-top: 8px solid #28a745;
        }

        .success-icon {
            font-size: 6rem;
            margin-bottom: 20px;
            animation: bounce 1s ease;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        h1 {
            color: #28a745;
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .species-name {
            font-size: 2rem;
            color: #0b5e42;
            font-style: italic;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 30px 0;
            text-align: left;
        }

        .stat-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border-left: 5px solid #0b5e42;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #0b5e42;
        }

        .stat-detail {
            font-size: 0.9rem;
            color: #495057;
            margin-top: 5px;
        }

        .success-message {
            background-color: #e8f5e9;
            padding: 20px;
            border-radius: 12px;
            margin: 30px 0;
            font-size: 1.1rem;
            border-left: 5px solid #28a745;
            text-align: left;
        }

        /* Detalhes das imagens por parte */
        .partes-detalhe {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }

        .partes-detalhe h4 {
            color: #0b5e42;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .partes-lista {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }

        .parte-badge {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .parte-badge.completa {
            border-left: 4px solid #28a745;
            background-color: #f0fff4;
        }

        .parte-icone {
            font-size: 1.5rem;
        }

        .parte-nome {
            flex: 1;
            font-weight: 500;
        }

        .parte-contagem {
            background: #0b5e42;
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 40px 0 20px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 15px 35px;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary {
            background-color: #0b5e42;
            color: white;
            box-shadow: 0 4px 10px rgba(11,94,66,0.3);
        }

        .btn-primary:hover {
            background-color: #0a4c35;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(11,94,66,0.4);
        }

        .btn-success {
            background-color: #28a745;
            color: white;
            box-shadow: 0 4px 10px rgba(40,167,69,0.3);
        }

        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(40,167,69,0.4);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-3px);
        }

        .footer {
            text-align: center;
            color: #718096;
            font-size: 0.9rem;
            margin-top: 30px;
        }

        @media (max-width: 768px) {
            .user-info {
                position: static;
                margin-bottom: 20px;
                justify-content: center;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- Informações do usuário logado -->
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span class="user-name"><?php echo htmlspecialchars($nome_usuario); ?></span>
            <a href="../Controllers/auth/logout_controlador.php" class="user-logout" onclick="return confirm('Deseja sair do sistema?')">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </div>
        
        <div class="success-card">
            
            <div class="success-icon">✅</div>
            
            <h1>IMPORTAÇÃO CONCLUÍDA!</h1>
            
            <div class="species-name">
                <?php echo htmlspecialchars($especie['nome_cientifico']); ?>
            </div>
            
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-label">Características</div>
                    <div class="stat-value">✓</div>
                    <div class="stat-detail">Dados morfológicos salvos</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Imagens</div>
                    <div class="stat-value"><?php echo $total_imagens_banco; ?></div>
                    <div class="stat-detail">arquivos salvos permanentemente</div>
                </div>
            </div>
            
            <!-- Detalhes das imagens por parte -->
            <div class="partes-detalhe">
                <h4>
                    <i class="fas fa-images"></i>
                    Imagens por parte da planta
                </h4>
                <div class="partes-lista">
                    <?php foreach ($partes as $key => $nome): 
                        $qtd = $imagens_por_parte[$key] ?? 0;
                        $classe = $qtd > 0 ? 'completa' : '';
                    ?>
                    <div class="parte-badge <?php echo $classe; ?>">
                        <span class="parte-icone"><?php echo substr($nome, 0, 2); ?></span>
                        <span class="parte-nome"><?php echo $nome; ?></span>
                        <span class="parte-contagem"><?php echo $qtd; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="success-message">
                <strong>✅ Tudo salvo com sucesso!</strong><br>
                Os dados morfológicos e as imagens foram permanentemente armazenados no banco de dados.
                A espécie agora está disponível para consulta.
            </div>
            
            <div class="action-buttons">
                <a href="../Controllers/inserir_dados_internet.php" class="btn btn-primary">
                    <i class="fas fa-upload"></i> IMPORTAR NOVA ESPÉCIE
                </a>
                <a href="../Views/busca_caracteristicas.php" class="btn btn-success">
                    <i class="fas fa-search"></i> VER LISTA DE ESPÉCIES
                </a>
                <a href="../../index.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> PÁGINA INICIAL
                </a>
            </div>
            
            <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 8px; font-size: 0.9rem; color: #666; text-align: left;">
                <div style="display: flex; gap: 20px; flex-wrap: wrap; justify-content: center;">
                    <span><i class="fas fa-tag"></i> ID: <?php echo $especie_id; ?></span>
                    <span><i class="fas fa-check-circle" style="color: #28a745;"></i> Status: <?php echo $especie['status']; ?></span>
                    <span><i class="fas fa-image" style="color: #0b5e42;"></i> Imagens: <?php echo $especie['status_imagens'] ?? 'registrada'; ?></span>
                    <span><i class="fas fa-user" style="color: #6c757d;"></i> Autor: <?php echo $nome_usuario; ?></span>
                </div>
            </div>
        </div>
        
        <div class="footer">
            Penomato • Dados importados com sucesso • <?php echo date('d/m/Y H:i'); ?>
        </div>
    </div>
</body>
</html>