<?php
// Ativar exibição de erros (apenas para desenvolvimento)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$id = $_GET['id'] ?? null;
$nome_cientifico = '';
$status = '';

// Se houver ID, buscar nome científico e status
if ($id) {
    try {
        $pdo = new PDO(
            "mysql:host=localhost;dbname=penomato;charset=utf8mb4",
            "root",
            "",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        $stmt = $pdo->prepare("SELECT nome_cientifico, status FROM especies_administrativo WHERE id = ?");
        $stmt->execute([$id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado) {
            $nome_cientifico = htmlspecialchars($resultado['nome_cientifico']);
            $status = $resultado['status'];
        }
    } catch (PDOException $e) {
        // Em caso de erro, apenas não mostra o nome
        $nome_cientifico = '';
        $status = '';
    }
}

// Mapear status para exibição amigável
$status_amigavel = [
    'sem_dados' => 'Sem dados',
    'dados_internet' => 'Dados da internet (não verificados)',
    'descrita' => 'Descrita (dados próprios)',
    'registrada' => 'Registrada (com imagens)',
    'em_revisao' => 'Em revisão',
    'revisada' => 'Revisada',
    'contestado' => 'Contestada',
    'publicado' => 'Publicada'
];

$status_texto = $status_amigavel[$status] ?? $status;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro realizado com sucesso</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha384-blOohCVdhjmtROpu8+CfTnUWham9nkX7P7OZQMst+RUnhtoY/9qemFAkIKOYxDI3" crossorigin="anonymous">
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .success-container {
            background: var(--branco);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 50px;
            max-width: 600px;
            width: 100%;
            text-align: center;
            border-top: 5px solid var(--sucesso-cor);
            animation: fadeIn 0.5s ease-out;
        }

        .success-icon {
            font-size: 80px;
            color: var(--sucesso-cor);
            margin-bottom: 20px;
        }

        h1 {
            color: var(--sucesso-cor);
            margin-bottom: 15px;
            font-size: 28px;
        }

        .message {
            color: var(--cinza-600);
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .details-box {
            background: var(--cinza-50);
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
            border-left: 4px solid var(--sucesso-cor);
            text-align: left;
        }

        .detail-item {
            margin: 10px 0;
            font-size: 16px;
        }

        .detail-label {
            font-weight: bold;
            color: var(--cinza-800);
        }

        .detail-value {
            color: var(--sucesso-cor);
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
            transition: var(--transicao);
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--sucesso-cor);
            color: var(--branco);
        }

        .btn-primary:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-secondary {
            background: var(--cinza-500);
            color: var(--branco);
        }

        .btn-secondary:hover {
            background: var(--cinza-600);
            transform: translateY(-2px);
        }

        .btn-view {
            background: var(--info-cor);
            color: var(--branco);
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
        <?php if ($status == 'dados_internet'): ?>
            <br><small style="color: var(--aviso-texto);">(Dados marcados como "internet" - aguardando verificação)</small>
        <?php endif; ?>
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
            <span class="detail-value"><?= $status_texto ?></span>
        </div>
        
        <div class="detail-item">
            <span class="detail-label">Data do Cadastro:</span>
            <span class="detail-value"><?= date('d/m/Y H:i') ?></span>
        </div>
    </div>
    
    <div class="buttons-container">
        <a href="confirmar_caracteristicas.php" class="btn btn-primary">
            Cadastrar Nova Espécie
        </a>
        
        <a href="busca_caracteristicas.php" class="btn btn-secondary">
            Ver Todas as Espécies
        </a>
        
        <?php if ($id): ?>
            <a href="especie_detalhes.php?id=<?= $id ?>" class="btn btn-view">
                Ver Detalhes Desta Espécie
            </a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>