<?php
// Ativar exibição de erros (apenas para desenvolvimento)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$id = $_GET['id'] ?? null;
$nome_cientifico = '';

// Se houver ID, buscar nome científico
if ($id) {
    try {
        $pdo = new PDO(
            "mysql:host=localhost;dbname=penomato;charset=utf8mb4",
            "root",
            "",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        $stmt = $pdo->prepare("SELECT nome_cientifico FROM especies_administrativo WHERE id = ?");
        $stmt->execute([$id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado) {
            $nome_cientifico = htmlspecialchars($resultado['nome_cientifico']);
        }
    } catch (PDOException $e) {
        // Em caso de erro, apenas não mostra o nome
        $nome_cientifico = '';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro realizado com sucesso</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .success-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 50px;
            max-width: 600px;
            width: 100%;
            text-align: center;
            border-top: 5px solid #28a745;
            animation: fadeIn 0.5s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #28a745;
            margin-bottom: 15px;
            font-size: 28px;
        }
        
        .message {
            color: #555;
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        
        .details-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
            border-left: 4px solid #28a745;
            text-align: left;
        }
        
        .detail-item {
            margin: 10px 0;
            font-size: 16px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #333;
        }
        
        .detail-value {
            color: #28a745;
        }
        
        .buttons-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }
        
        .btn-primary {
            background: #28a745;
            color: white;
        }
        
        .btn-primary:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .btn-view {
            background: #17a2b8;
            color: white;
        }
        
        .btn-view:hover {
            background: #138496;
            transform: translateY(-2px);
        }
        
        @media (max-width: 480px) {
            .success-container {
                padding: 30px 20px;
            }
            
            .buttons-container {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>

<div class="success-container">
    <div class="success-icon">
        <i>✅</i>
    </div>
    
    <h1>Cadastro Realizado com Sucesso!</h1>
    
    <div class="message">
        As características morfológicas foram salvas no banco de dados.
        Agora a espécie está identificada e disponível para consulta.
    </div>
    
    <div class="details-box">
        <?php if ($id): ?>
            <div class="detail-item">
                <span class="detail-label">ID da Espécie:</span>
                <span class="detail-value">#<?= htmlspecialchars($id) ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($nome_cientifico): ?>
            <div class="detail-item">
                <span class="detail-label">Nome Científico:</span>
                <span class="detail-value"><?= $nome_cientifico ?></span>
            </div>
        <?php endif; ?>
        
        <div class="detail-item">
            <span class="detail-label">Status Atual:</span>
            <span class="detail-value">Identificada ✓</span>
        </div>
        
        <div class="detail-item">
            <span class="detail-label">Data do Cadastro:</span>
            <span class="detail-value"><?= date('d/m/Y H:i') ?></span>
        </div>
    </div>
    
    <div class="buttons-container">
        <a href="formulario_caracteristicas.php" class="btn btn-primary">
            Cadastrar Nova Espécie
        </a>
        
        <a href="lista_especies.php" class="btn btn-secondary">
            Ver Todas as Espécies
        </a>
        
        <?php if ($id): ?>
            <a href="detalhes_especie.php?id=<?= $id ?>" class="btn btn-view">
                Ver Detalhes Desta Espécie
            </a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>