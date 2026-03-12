<?php
// ============================================================
// UPLOAD DE IMAGENS - VERSÃO LEGADO (MVP)
// ============================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// ================================================
// VERIFICAR SE USUÁRIO ESTÁ LOGADO
// ================================================
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /penomato_mvp/src/Views/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';

// ================================================
// CONFIGURAÇÕES DO BANCO
// ================================================
$servidor = "127.0.0.1";
$usuario_db = "root";
$senha_db = "";
$banco = "penomato";

$conexao = new mysqli($servidor, $usuario_db, $senha_db, $banco);

if ($conexao->connect_error) {
    die("Erro de conexão: " . $conexao->connect_error);
}

$conexao->set_charset("utf8mb4");

// ================================================
// BUSCAR ESPÉCIES DISPONÍVEIS
// ================================================
$sql_especies = "SELECT id, nome_cientifico FROM especies_administrativo ORDER BY nome_cientifico";
$resultado_especies = $conexao->query($sql_especies);

$especies = [];
if ($resultado_especies && $resultado_especies->num_rows > 0) {
    while ($row = $resultado_especies->fetch_assoc()) {
        $especies[] = $row;
    }
}

// ================================================
// VERIFICAR SE TEM ESPÉCIE SELECIONADA
// ================================================
$especie_id = isset($_GET['especie_id']) ? (int)$_GET['especie_id'] : 0;
$especie_selecionada = null;

if ($especie_id > 0) {
    $sql_especie = "SELECT id, nome_cientifico FROM especies_administrativo WHERE id = ?";
    $stmt = $conexao->prepare($sql_especie);
    $stmt->bind_param("i", $especie_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $especie_selecionada = $resultado->fetch_assoc();
    $stmt->close();
}

// ================================================
// BUSCAR IMAGENS DA ESPÉCIE SELECIONADA
// ================================================
$imagens = [];
if ($especie_selecionada) {
    $sql_imagens = "SELECT id, parte_planta, caminho_imagem, nome_original, 
                           fonte_nome, autor_imagem, licenca, status_validacao, data_upload
                    FROM especies_imagens 
                    WHERE especie_id = ? 
                    ORDER BY parte_planta, data_upload DESC";
    $stmt = $conexao->prepare($sql_imagens);
    $stmt->bind_param("i", $especie_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    while ($row = $resultado->fetch_assoc()) {
        $imagens[] = $row;
    }
    $stmt->close();
}

$conexao->close();

// ================================================
// MENSAGENS DE RETORNO
// ================================================
$mensagem_sucesso = isset($_GET['sucesso']) ? urldecode($_GET['sucesso']) : '';
$mensagem_erro = isset($_GET['erro']) ? urldecode($_GET['erro']) : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload de Imagens - Penomato</title>
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
            background-color: #f5f2e9;
            padding: 30px 20px;
            color: #2c3e50;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Cabeçalho */
        .header {
            background: white;
            padding: 30px 40px;
            border-radius: 12px 12px 0 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-bottom: 4px solid #0b5e42;
            margin-bottom: 5px;
            position: relative;
        }

        .header h1 {
            color: #0b5e42;
            font-size: 2rem;
            font-weight: 500;
        }

        .header .subtitle {
            color: #666;
            font-style: italic;
            margin-top: 10px;
            font-size: 0.95rem;
        }

        /* Informações do usuário */
        .user-info {
            position: absolute;
            top: 20px;
            right: 30px;
            background: #f8f9fa;
            padding: 10px 20px;
            border-radius: 40px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .user-info i {
            color: #0b5e42;
        }

        .user-name {
            font-weight: 600;
        }

        .btn-logout {
            color: #dc3545;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 20px;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background: #dc3545;
            color: white;
        }

        /* Card principal */
        .card {
            background: white;
            padding: 30px 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            border-radius: 0 0 12px 12px;
        }

        /* Seletor de espécie */
        .selector-section {
            background-color: #f8fafc;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 2px solid #e2e8f0;
        }

        .selector-section h3 {
            color: #0b5e42;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .selector-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .selector-form .form-group {
            flex: 1;
            min-width: 250px;
        }

        .selector-form label {
            display: block;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .selector-form select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
        }

        .selector-form select:focus {
            outline: none;
            border-color: #0b5e42;
        }

        .btn-select {
            background: #0b5e42;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-select:hover {
            background: #0a4c35;
            transform: translateY(-2px);
        }

        /* Alertas */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }

        /* Mensagem quando nenhuma espécie selecionada */
        .no-species {
            text-align: center;
            padding: 60px 20px;
            background: #f8fafc;
            border-radius: 12px;
            color: #718096;
        }

        .no-species i {
            font-size: 4rem;
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .no-species h3 {
            color: #4a5568;
            margin-bottom: 10px;
        }

        /* Info da espécie */
        .species-info {
            background-color: #e8f5e9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .species-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #0b5e42;
        }

        .species-id {
            color: #666;
            font-size: 0.9rem;
        }

        /* Grid de partes */
        .partes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 30px 0;
        }

        .parte-card {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            border: 2px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .parte-card:hover {
            border-color: #0b5e42;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .parte-icone {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .parte-nome {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .parte-contagem {
            font-size: 0.9rem;
            color: #666;
        }

        .parte-contagem span {
            font-weight: 600;
            color: #0b5e42;
        }

        /* Galeria de imagens */
        .galeria {
            margin-top: 40px;
        }

        .galeria h3 {
            color: #0b5e42;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .imagens-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .imagem-card {
            background-color: white;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.2s;
        }

        .imagem-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .imagem-preview {
            height: 150px;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #e2e8f0;
        }

        .imagem-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .imagem-info {
            padding: 15px;
        }

        .imagem-parte {
            font-weight: 600;
            color: #0b5e42;
            margin-bottom: 5px;
        }

        .imagem-creditos {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 5px;
        }

        .imagem-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-pendente {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-aprovado {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejeitado {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Botões */
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 30px;
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
        }

        .btn-primary:hover {
            background: #0a4c35;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .footer {
            text-align: center;
            color: #718096;
            font-size: 0.85rem;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- Cabeçalho -->
        <div class="header">
            <h1>📸 ENVIO DE IMAGENS</h1>
            <div class="subtitle">
                Adicione exsicatas digitais e imagens de habitat
            </div>
            
            <!-- Informações do usuário -->
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span class="user-name"><?php echo htmlspecialchars($nome_usuario); ?></span>
                <a href="/penomato_mvp/src/Controllers/auth/logout_controlador.php" class="btn-logout" onclick="return confirm('Deseja sair?')">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
        
        <!-- Card principal -->
        <div class="card">
            
            <!-- Mensagens -->
            <?php if ($mensagem_sucesso): ?>
                <div class="alert alert-success">✅ <?php echo $mensagem_sucesso; ?></div>
            <?php endif; ?>
            
            <?php if ($mensagem_erro): ?>
                <div class="alert alert-danger">❌ <?php echo $mensagem_erro; ?></div>
            <?php endif; ?>
            
            <!-- Seletor de espécie -->
            <div class="selector-section">
                <h3>
                    <i class="fas fa-tree"></i>
                    Selecione uma espécie
                </h3>
                
                <form class="selector-form" method="GET" action="">
                    <div class="form-group">
                        <label for="especie_id">Espécie</label>
                        <select name="especie_id" id="especie_id" required>
                            <option value="">-- Selecione --</option>
                            <?php foreach ($especies as $esp): ?>
                                <option value="<?php echo $esp['id']; ?>" <?php echo $especie_id == $esp['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($esp['nome_cientifico']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-select">
                        <i class="fas fa-arrow-right"></i> Carregar
                    </button>
                </form>
            </div>
            
            <?php if ($especie_selecionada): ?>
                
                <!-- Info da espécie -->
                <div class="species-info">
                    <span class="species-name"><?php echo htmlspecialchars($especie_selecionada['nome_cientifico']); ?></span>
                    <span class="species-id">ID: <?php echo $especie_id; ?></span>
                </div>
                
                <!-- Grid de partes da planta -->
                <h3 style="margin: 20px 0 10px;">📍 Partes da planta</h3>
                <div class="partes-grid">
                    <?php
                    $partes = [
                        'folha' => ['icone' => '🍃', 'nome' => 'Folha'],
                        'flor' => ['icone' => '🌸', 'nome' => 'Flor'],
                        'fruto' => ['icone' => '🍎', 'nome' => 'Fruto'],
                        'caule' => ['icone' => '🌿', 'nome' => 'Caule'],
                        'semente' => ['icone' => '🌱', 'nome' => 'Semente'],
                        'habito' => ['icone' => '🌳', 'nome' => 'Hábito']
                    ];
                    
                    foreach ($partes as $key => $parte):
                        $contagem = 0;
                        foreach ($imagens as $img) {
                            if ($img['parte_planta'] == $key) $contagem++;
                        }
                    ?>
                    <a href="/penomato_mvp/src/Views/upload_parte.php?especie_id=<?php echo $especie_id; ?>&parte=<?php echo $key; ?>" class="parte-card">
                        <div class="parte-icone"><?php echo $parte['icone']; ?></div>
                        <div class="parte-nome"><?php echo $parte['nome']; ?></div>
                        <div class="parte-contagem">
                            <span><?php echo $contagem; ?></span> imagem(ns)
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                
                <!-- Galeria de imagens -->
                <?php if (count($imagens) > 0): ?>
                <div class="galeria">
                    <h3>
                        <i class="fas fa-images"></i>
                        Imagens adicionadas (<?php echo count($imagens); ?>)
                    </h3>
                    
                    <div class="imagens-grid">
                        <?php foreach ($imagens as $img): ?>
                        <div class="imagem-card">
                            <div class="imagem-preview">
                                <img src="/penomato_mvp/<?php echo htmlspecialchars($img['caminho_imagem']); ?>" 
                                     alt="<?php echo htmlspecialchars($img['nome_original'] ?? 'Imagem'); ?>"
                                     onerror="this.src='/penomato_mvp/assets/img/no-image.png';">
                            </div>
                            <div class="imagem-info">
                                <div class="imagem-parte">
                                    <?php 
                                    $icone = $partes[$img['parte_planta']]['icone'] ?? '📷';
                                    echo $icone . ' ' . ucfirst($img['parte_planta']); 
                                    ?>
                                </div>
                                <?php if ($img['fonte_nome']): ?>
                                <div class="imagem-creditos">
                                    <i class="fas fa-link"></i> <?php echo htmlspecialchars($img['fonte_nome']); ?>
                                </div>
                                <?php endif; ?>
                                <div style="margin-top: 8px;">
                                    <span class="imagem-status status-<?php echo $img['status_validacao']; ?>">
                                        <?php 
                                        echo $img['status_validacao'] == 'pendente' ? '⏳ Pendente' : 
                                             ($img['status_validacao'] == 'aprovado' ? '✅ Aprovado' : '❌ Rejeitado'); 
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
            <?php else: ?>
                
                <!-- Nenhuma espécie selecionada -->
                <div class="no-species">
                    <i class="fas fa-images"></i>
                    <h3>Nenhuma espécie selecionada</h3>
                    <p>Escolha uma espécie no menu acima para começar</p>
                </div>
                
            <?php endif; ?>
            
            <!-- Botões de navegação -->
            <div class="action-buttons">
                <a href="/penomato_mvp/src/Views/entrar_colaborador.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar ao Painel
                </a>
            </div>
            
        </div>
        
        <!-- Rodapé -->
        <div class="footer">
            Penomato • Upload de imagens
        </div>
        
    </div>
</body>
</html>