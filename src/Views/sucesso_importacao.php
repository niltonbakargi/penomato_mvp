<?php
// ================================================
// PÁGINA DE SUCESSO - IMPORTAÇÃO CONCLUÍDA
// ================================================

session_start();

$servidor = "127.0.0.1";
$usuario_db = "root";
$senha_db = "";
$banco = "penomato";

$especie_id = isset($_GET['especie_id']) ? (int)$_GET['especie_id'] : 0;
$total_imagens = isset($_GET['imagens']) ? (int)$_GET['imagens'] : 0;

if ($especie_id <= 0) {
    header("Location: ../Controllers/inserir_dados_internet.php");
    exit;
}

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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penomato - Importação Concluída</title>
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
            max-width: 800px;
            margin: 0 auto;
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

        .success-message {
            background-color: #e8f5e9;
            padding: 20px;
            border-radius: 12px;
            margin: 30px 0;
            font-size: 1.1rem;
            border-left: 5px solid #28a745;
            text-align: left;
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
    </style>
</head>
<body>
    <div class="container">
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
                    <div class="stat-detail">arquivos salvos no banco</div>
                </div>
            </div>
            
            <div class="success-message">
                <strong>✅ Tudo salvo com sucesso!</strong><br>
                Os dados morfológicos e as imagens foram permanentemente armazenados no banco de dados.
                A espécie agora está disponível para consulta.
            </div>
            
            <div class="action-buttons">
                <a href="../Controllers/inserir_dados_internet.php" class="btn btn-primary">
                    📤 IMPORTAR NOVA ESPÉCIE
                </a>
                <a href="../Views/busca_caracteristicas.php" class="btn btn-success">
                    🔍 VER LISTA DE ESPÉCIES
                </a>
                <a href="../../index.php" class="btn btn-secondary">
                    🏠 PÁGINA INICIAL
                </a>
            </div>
            
            <div style="margin-top: 30px; font-size: 0.9rem; color: #999;">
                ID da espécie: <?php echo $especie_id; ?> • 
                Status: <?php echo $especie['status']; ?> • 
                Imagens: <?php echo $especie['status_imagens'] ?? 'registrada'; ?>
            </div>
        </div>
        
        <div class="footer">
            Penomato • Dados importados com sucesso
        </div>
    </div>
</body>
</html>