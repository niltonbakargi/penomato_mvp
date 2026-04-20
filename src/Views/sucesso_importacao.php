<?php
// ================================================
// PÁGINA DE SUCESSO - IMPORTAÇÃO CONCLUÍDA
// ================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../../config/banco_de_dados.php';

// ================================================
// VERIFICAR SE USUÁRIO ESTÁ LOGADO
// ================================================
if (!isset($_SESSION['usuario_id'])) {
    $url_atual = urlencode($_SERVER['REQUEST_URI']);
    header("Location: auth/login.php?redirect=" . $url_atual);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';

// ================================================
// RECEBER PARÂMETROS
// ================================================
$especie_id = isset($_GET['especie_id']) ? (int)$_GET['especie_id'] : 0;
$imagens_salvas = isset($_GET['imagens']) ? (int)$_GET['imagens'] : 0;

if ($especie_id <= 0) {
    // Se não veio ID, redireciona para o painel
    header("Location: entrar_colaborador.php?erro=" . urlencode("Nenhuma espécie informada."));
    exit;
}

// ================================================
// BUSCAR DADOS DA ESPÉCIE
// ================================================
$stmt = $pdo->prepare(
    "SELECT id, nome_cientifico, status,
            data_dados_internet, data_registrada,
            autor_dados_internet_id
     FROM especies_administrativo
     WHERE id = ?"
);
$stmt->execute([$especie_id]);
$especie = $stmt->fetch() ?: [
    'nome_cientifico' => 'Espécie não encontrada',
    'status'          => 'desconhecido',
    'data_dados_internet' => null,
    'data_registrada'     => null,
];

// Mapear status para mensagem amigável
$status_mensagem = [
    'sem_dados' => 'Sem dados cadastrados',
    'dados_internet' => 'Dados da internet importados',
    'descrita' => 'Características descritas',
    'registrada' => 'Registrada com imagens',
    'em_revisao' => 'Em revisão',
    'revisada' => 'Revisada',
    'contestado' => 'Contestada',
    'publicado' => 'Publicada'
];

$status_texto = $status_mensagem[$especie['status']] ?? $especie['status'];

// Formatar datas
$data_importacao = !empty($especie['data_dados_internet']) 
    ? date('d/m/Y H:i', strtotime($especie['data_dados_internet'])) 
    : 'Não informada';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penomato - Importação Concluída</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f2e9 0%, #e8e2d4 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-container {
            max-width: 600px;
            width: 100%;
            background: var(--branco);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from { transform: translateY(30px); opacity: 0; }
            to   { transform: translateY(0); opacity: 1; }
        }

        .success-header {
            background: var(--cor-primaria);
            color: var(--branco);
            padding: 40px 30px;
            text-align: center;
        }

        .success-header .icon {
            font-size: 5rem;
            margin-bottom: 20px;
            animation: bounce 1s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .success-header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .success-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .success-body {
            padding: 40px 30px;
        }

        .info-card {
            background: var(--cinza-50);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 4px solid var(--cor-primaria);
        }

        .info-card h2 {
            color: var(--cor-primaria);
            font-size: 1.5rem;
            margin-bottom: 15px;
            font-style: italic;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }

        .info-item {
            background: var(--branco);
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .info-item .label {
            font-size: 0.85rem;
            color: var(--cinza-500);
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .info-item .value {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--cor-primaria);
        }

        .stats {
            display: flex;
            justify-content: space-around;
            margin: 30px 0;
            padding: 20px;
            background: var(--verde-50);
            border-radius: 12px;
        }

        .stat {
            text-align: center;
        }

        .stat .number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--cor-primaria);
        }

        .stat .label {
            font-size: 0.9rem;
            color: var(--cinza-500);
        }

        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            background: var(--cor-primaria);
            color: var(--branco);
            border-radius: 30px;
            font-size: 1rem;
            font-weight: 600;
            margin-top: 10px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transicao);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary {
            background: var(--cor-primaria);
            color: var(--branco);
            box-shadow: 0 4px 10px rgba(11,94,66,0.3);
        }

        .btn-primary:hover {
            background: var(--cor-primaria-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(11,94,66,0.4);
        }

        .btn-secondary {
            background: var(--cinza-500);
            color: var(--branco);
        }

        .btn-secondary:hover {
            background: var(--cinza-600);
            transform: translateY(-2px);
        }

        .footer {
            text-align: center;
            color: var(--cinza-500);
            font-size: 0.9rem;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-header">
            <div class="icon">✅</div>
            <h1>Importação Concluída!</h1>
            <p>Os dados e imagens foram salvos com sucesso</p>
        </div>
        
        <div class="success-body">
            
            <!-- Mensagem de sucesso adicional -->
            <div class="alerta--sucesso">
                <i class="fas fa-check-circle"></i> 
                Todas as informações foram processadas e armazenadas permanentemente.
            </div>
            
            <div class="info-card">
                <h2><i class="fas fa-leaf"></i> <?php echo htmlspecialchars($especie['nome_cientifico']); ?></h2>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="label"><i class="fas fa-tag"></i> Status atual</div>
                        <div class="value"><?php echo $status_texto; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label"><i class="fas fa-hashtag"></i> ID da espécie</div>
                        <div class="value">#<?php echo $especie_id; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label"><i class="fas fa-calendar"></i> Data da importação</div>
                        <div class="value"><?php echo $data_importacao; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label"><i class="fas fa-user"></i> Colaborador</div>
                        <div class="value"><?php echo htmlspecialchars($nome_usuario); ?></div>
                    </div>
                </div>
                
                <div class="stats">
                    <div class="stat">
                        <div class="number"><?php echo $imagens_salvas; ?></div>
                        <div class="label"><i class="fas fa-image"></i> Imagens salvas</div>
                    </div>
                    <div class="stat">
                        <div class="number">✓</div>
                        <div class="label"><i class="fas fa-file-alt"></i> Dados morfológicos</div>
                    </div>
                </div>
                
                <div style="text-align: center;">
                    <span class="status-badge">
                        <i class="fas fa-check-circle"></i> 
                        Importação finalizada com sucesso
                    </span>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="escolher_especie.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> NOVA IMPORTAÇÃO
                </a>
                <a href="entrar_colaborador.php" class="btn btn-secondary">
                    <i class="fas fa-tachometer-alt"></i> VOLTAR AO PAINEL
                </a>
            </div>
            
            <div class="footer">
                <p>Os dados já estão disponíveis para consulta e revisão por especialistas.</p>
                <p style="margin-top: 5px; font-size: 0.8rem;">Status: <strong><?php echo $status_texto; ?></strong> - aguardando revisão</p>
            </div>
            
        </div>
    </div>
</body>
</html>