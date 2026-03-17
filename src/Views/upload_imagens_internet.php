<?php
// ================================================
// UPLOAD DE IMAGENS - VERSÃO COM BOTÃO AVANÇAR
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
    $url_atual = urlencode($_SERVER['REQUEST_URI']);
    header("Location: ../Views/auth/login.php?redirect=" . $url_atual);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';

// ================================================
// VERIFICAR SESSÃO TEMPORÁRIA
// ================================================
$temp_id = isset($_GET['temp_id']) ? $_GET['temp_id'] : '';

if (empty($temp_id) || !isset($_SESSION['importacao_temporaria']) || $_SESSION['importacao_temporaria']['temp_id'] !== $temp_id) {
    die("Sessão temporária inválida ou expirada. Volte e inicie uma nova importação.");
}

$dados_temporarios = $_SESSION['importacao_temporaria'];
$especie_id = $dados_temporarios['especie_id'];
$dados_caracteristicas = $dados_temporarios['dados'];

// Verificar se o usuário da sessão é o mesmo que iniciou a importação
if ($_SESSION['importacao_temporaria']['usuario_id'] != $id_usuario) {
    die("Você não tem permissão para acessar esta importação.");
}

// ================================================
// BUSCAR DADOS DA ESPÉCIE NO BANCO (APENAS PARA EXIBIÇÃO)
// ================================================
$conexao = new mysqli($servidor, $usuario_db, $senha_db, $banco);

if ($conexao->connect_error) {
    die("Erro de conexão: " . $conexao->connect_error);
}

$conexao->set_charset("utf8mb4");

$sql_especie = "SELECT id, nome_cientifico FROM especies_administrativo WHERE id = ?";
$stmt = $conexao->prepare($sql_especie);
$stmt->bind_param("i", $especie_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Espécie não encontrada.");
}

$especie = $resultado->fetch_assoc();
$stmt->close();
$conexao->close();

// ================================================
// INICIALIZAR ESTRUTURA DE IMAGENS NA SESSÃO
// ================================================
if (!isset($_SESSION['importacao_temporaria']['imagens'])) {
    $_SESSION['importacao_temporaria']['imagens'] = [];
}

$imagens_temporarias = $_SESSION['importacao_temporaria']['imagens'];

// ================================================
// CONTAGEM POR PARTE (BASEADO NAS IMAGENS TEMPORÁRIAS)
// ================================================
$contagem_por_parte = [
    'folha' => 0, 'flor' => 0, 'fruto' => 0, 'caule' => 0,
    'semente' => 0, 'habito' => 0, 'exsicata_completa' => 0, 'detalhe' => 0
];

foreach ($imagens_temporarias as $img) {
    $parte = $img['parte_planta'];
    if (isset($contagem_por_parte[$parte])) {
        $contagem_por_parte[$parte]++;
    }
}

// ================================================
// DEFINIR STATUS DAS IMAGENS
// ================================================
$partes_obrigatorias = ['folha', 'flor', 'fruto', 'caule', 'habito'];
$partes_completas = 0;

foreach ($partes_obrigatorias as $parte) {
    if ($contagem_por_parte[$parte] > 0) {
        $partes_completas++;
    }
}

$progresso = round(($partes_completas / count($partes_obrigatorias)) * 100);

// ================================================
// PROCESSAR MENSAGENS DE RETORNO
// ================================================
$mensagem_sucesso = isset($_GET['sucesso']) ? urldecode($_GET['sucesso']) : '';
$mensagem_erro = isset($_GET['erro']) ? urldecode($_GET['erro']) : '';

// ================================================
// PARTE SELECIONADA (para destacar no grid)
// ================================================
$parte_selecionada = isset($_GET['parte']) ? $_GET['parte'] : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penomato - Upload de Imagens</title>
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
            max-width: 1400px;
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

        /* Cabeçalho */
        .header {
            background: white;
            padding: 30px 40px;
            border-radius: 12px 12px 0 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-bottom: 4px solid #0b5e42;
            margin-bottom: 5px;
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

        /* Aviso temporário */
        .temp-warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .temp-warning strong {
            font-size: 1.1rem;
        }

        /* Card principal */
        .card {
            background: white;
            padding: 30px 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        /* Informações da espécie */
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

        .temp-badge {
            background-color: #ffc107;
            color: #2c3e50;
            padding: 5px 15px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Barra de progresso */
        .progress-container {
            margin: 20px 0 30px;
        }

        .progress-bar {
            height: 20px;
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .progress-fill {
            height: 100%;
            background-color: #0b5e42;
            width: <?php echo $progresso; ?>%;
            transition: width 0.3s ease;
        }

        .progress-text {
            text-align: center;
            font-size: 0.9rem;
            color: #666;
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

        /* Grid de partes */
        .partes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 30px 0;
        }

        .parte-card {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 15px;
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

        .parte-card.selecionado {
            border-color: #0b5e42;
            background-color: #e6f7e6;
            border-width: 3px;
        }

        .parte-card.completa {
            background-color: #d4edda;
            border-color: #28a745;
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

        /* Formulário de upload */
        .upload-parte-form {
            background-color: #f8fafc;
            border: 3px solid #0b5e42;
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
        }

        .upload-parte-form h3 {
            color: #0b5e42;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.4rem;
        }

        .parte-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #e8f5e9;
            border-radius: 8px;
        }

        .parte-info-icone {
            font-size: 3rem;
        }

        .parte-info-nome {
            font-size: 1.8rem;
            font-weight: 600;
            color: #0b5e42;
        }

        .parte-info-status {
            margin-left: auto;
            padding: 8px 20px;
            border-radius: 30px;
            font-weight: 600;
            background-color: <?php echo $contagem_por_parte[$parte_selecionada] > 0 ? '#d4edda' : '#fff3cd'; ?>;
            color: <?php echo $contagem_por_parte[$parte_selecionada] > 0 ? '#155724' : '#856404'; ?>;
        }

        /* ================================================ */
        /* ÁREA: COLAR IMAGEM (Ctrl+V) */
        /* ================================================ */
        .colar-area {
            border: 2px dashed #0b5e42;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            background-color: #f0f8f0;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
        }

        .colar-area:hover {
            background-color: #e0f0e0;
            border-color: #0a4c35;
        }

        .colar-area .icone {
            font-size: 4rem;
            margin-bottom: 15px;
            color: #0b5e42;
        }

        .colar-area .texto {
            font-size: 1.2rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .colar-area .subtexto {
            font-size: 0.9rem;
            color: #666;
        }

        /* Textarea escondido para capturar o Ctrl+V */
        #colarInput {
            position: absolute;
            opacity: 0;
            height: 0;
            width: 0;
            pointer-events: none;
        }

        /* ================================================ */
        /* CAMPOS DA FONTE (URL SEPARADA) */
        /* ================================================ */
        .fonte-info {
            background-color: #edf2f7;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .fonte-info h4 {
            color: #0b5e42;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .fonte-info .form-group {
            margin-bottom: 15px;
        }

        .fonte-info label {
            display: block;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .fonte-info input, 
        .fonte-info select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 1rem;
        }

        .fonte-info input:focus,
        .fonte-info select:focus {
            outline: none;
            border-color: #0b5e42;
        }

        .fonte-info small {
            color: #666;
            font-size: 0.85rem;
            display: block;
            margin-top: 5px;
        }

        /* Preview das imagens */
        .preview-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .preview-item {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }

        .preview-image {
            height: 150px;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #e2e8f0;
        }

        .preview-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .preview-info {
            padding: 15px;
        }

        .preview-info strong {
            display: block;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .preview-info small {
            color: #666;
        }

        .remove-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid #dc3545;
            color: #dc3545;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.2s;
        }

        .remove-btn:hover {
            background: #dc3545;
            color: white;
        }

        /* Botões de ação */
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 30px 0;
            flex-wrap: wrap;
        }

        .btn {
            padding: 15px 40px;
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
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(11,94,66,0.4);
        }

        .btn-primary:disabled {
            background-color: #cbd5e0;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
            box-shadow: 0 4px 10px rgba(40,167,69,0.3);
        }

        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(40,167,69,0.4);
        }

        .btn-avancar {
            background-color: #0b5e42;
            color: white;
            box-shadow: 0 4px 10px rgba(11,94,66,0.3);
        }

        .btn-avancar:hover {
            background-color: #0a4c35;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(11,94,66,0.4);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }

        .footer {
            text-align: center;
            color: #718096;
            font-size: 0.85rem;
            margin-top: 30px;
        }

        @media (max-width: 768px) {
            .user-info {
                position: static;
                margin-bottom: 20px;
                justify-content: center;
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
        
        <!-- Cabeçalho -->
        <div class="header">
            <h1>📸 PENOMATO • UPLOAD DE IMAGENS</h1>
            <div class="subtitle">
                PASSO 2: Adicione as imagens para cada parte da planta
            </div>
        </div>

        <!-- Aviso temporário -->
        <div class="temp-warning">
            <span>⚠️</span>
            <div>
                <strong>Dados temporários</strong> - As imagens ainda não foram salvas no banco.
                Utilize os botões abaixo para adicionar imagens a cada parte.
                Quando terminar, clique em "AVANÇAR PARA DADOS".
            </div>
        </div>

        <!-- Card principal -->
        <div class="card">
            
            <!-- Informações da espécie -->
            <div class="species-info">
                <div>
                    <span class="species-name"><?php echo htmlspecialchars($especie['nome_cientifico']); ?></span>
                    <span style="margin-left: 15px; color: #666;">ID: <?php echo $especie_id; ?></span>
                </div>
                <div>
                    <span class="temp-badge">⚡ SESSÃO: <?php echo substr($temp_id, -8); ?></span>
                </div>
            </div>

            <!-- Barra de progresso -->
            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <div class="progress-text">
                    <?php echo $partes_completas; ?> de <?php echo count($partes_obrigatorias); ?> partes obrigatórias completas (<?php echo $progresso; ?>%)
                </div>
            </div>

            <!-- Mensagens de retorno -->
            <?php if ($mensagem_sucesso): ?>
                <div class="alert alert-success">✅ <?php echo $mensagem_sucesso; ?></div>
            <?php endif; ?>
            
            <?php if ($mensagem_erro): ?>
                <div class="alert alert-danger">❌ <?php echo $mensagem_erro; ?></div>
            <?php endif; ?>

            <!-- Grid de partes da planta -->
            <h3 style="margin: 20px 0 10px;">📍 SELECIONE UMA PARTE PARA ADICIONAR IMAGENS</h3>
            <div class="partes-grid">
                <?php
                $partes = [
                    'folha' => ['icone' => '🍃', 'nome' => 'Folha', 'obrigatoria' => true],
                    'flor' => ['icone' => '🌸', 'nome' => 'Flor', 'obrigatoria' => true],
                    'fruto' => ['icone' => '🍎', 'nome' => 'Fruto', 'obrigatoria' => true],
                    'caule' => ['icone' => '🌿', 'nome' => 'Caule', 'obrigatoria' => true],
                    'semente' => ['icone' => '🌱', 'nome' => 'Semente', 'obrigatoria' => false],
                    'habito' => ['icone' => '🌳', 'nome' => 'Hábito', 'obrigatoria' => true],
                    'exsicata_completa' => ['icone' => '📋', 'nome' => 'Exsicata', 'obrigatoria' => false],
                    'detalhe' => ['icone' => '🔍', 'nome' => 'Detalhe', 'obrigatoria' => false]
                ];

                foreach ($partes as $key => $parte):
                    $contagem = $contagem_por_parte[$key] ?? 0;
                    $classe = 'parte-card';
                    if ($key == $parte_selecionada) $classe .= ' selecionado';
                    if ($contagem > 0 && in_array($key, $partes_obrigatorias)) $classe .= ' completa';
                ?>
                <a href="?temp_id=<?php echo urlencode($temp_id); ?>&parte=<?php echo $key; ?>" class="<?php echo $classe; ?>">
                    <div class="parte-icone"><?php echo $parte['icone']; ?></div>
                    <div class="parte-nome"><?php echo $parte['nome']; ?></div>
                    <div class="parte-contagem">
                        <span><?php echo $contagem; ?></span> imagem(ns)
                    </div>
                    <?php if ($parte['obrigatoria'] && $contagem == 0): ?>
                        <div style="font-size: 0.8rem; color: #dc3545; margin-top: 5px;">⛔ Obrigatória</div>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Formulário de upload para a parte selecionada -->
            <?php if ($parte_selecionada && isset($partes[$parte_selecionada])): 
                $parte_atual = $partes[$parte_selecionada];
            ?>
            <div class="upload-parte-form">
                <h3>
                    <span>📤</span>
                    ADICIONAR IMAGENS PARA: <?php echo $parte_atual['icone']; ?> <?php echo $parte_atual['nome']; ?>
                </h3>

                <div class="parte-info">
                    <span class="parte-info-icone"><?php echo $parte_atual['icone']; ?></span>
                    <span class="parte-info-nome"><?php echo $parte_atual['nome']; ?></span>
                    <span class="parte-info-status">
                        <?php echo $contagem_por_parte[$parte_selecionada]; ?> imagem(ns) já adicionadas
                    </span>
                </div>

                <form id="uploadForm" action="../Controllers/processar_upload_temporario.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="temp_id" value="<?php echo htmlspecialchars($temp_id); ?>">
                    <input type="hidden" name="parte_planta" value="<?php echo $parte_selecionada; ?>">
                    
                    <!-- ================================================ -->
                    <!-- ÁREA PARA COLAR A IMAGEM (Ctrl+V) -->
                    <!-- ================================================ -->
                    <div class="colar-area" id="colarArea" onclick="document.getElementById('colarInput').focus()">
                        <div class="icone">📋</div>
                        <div class="texto">Cole a imagem aqui (Ctrl+V)</div>
                        <div class="subtexto">Copie a imagem de qualquer lugar e cole neste campo</div>
                        <textarea id="colarInput" placeholder="Clique aqui e pressione Ctrl+V para colar a imagem..."></textarea>
                    </div>

                    <!-- Campo hidden para armazenar a imagem em base64 -->
                    <input type="hidden" name="imagem_base64" id="imagemBase64">

                    <!-- ================================================ -->
                    <!-- CAMPOS DA FONTE (URL SEPARADA) -->
                    <!-- ================================================ -->
                    <div class="fonte-info">
                        <h4><i class="fas fa-link"></i> URL da fonte (de onde você copiou a imagem)</h4>
                        <div class="form-group">
                            <label for="fonte_url">Cole a URL da fonte aqui:</label>
                            <input type="url" id="fonte_url" name="fonte_url" placeholder="https://exemplo.com/fonte-da-imagem" value="<?php echo isset($dados_caracteristicas['fonte_url']) ? htmlspecialchars($dados_caracteristicas['fonte_url']) : ''; ?>">
                            <small>Ex: link do site, artigo, herbário digital, etc.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="fonte_nome">Nome da fonte (opcional):</label>
                            <input type="text" id="fonte_nome" name="fonte_nome" placeholder="Ex: Flora do Brasil, Lorenzi, etc." value="<?php echo isset($dados_caracteristicas['fonte_nome']) ? htmlspecialchars($dados_caracteristicas['fonte_nome']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="autor_imagem">Autor da imagem (opcional):</label>
                            <input type="text" id="autor_imagem" name="autor_imagem" placeholder="Nome do fotógrafo/ilustrador">
                        </div>
                        
                        <div class="form-group">
                            <label for="licenca">Licença:</label>
                            <select id="licenca" name="licenca" onchange="toggleLicencaOutros(this.value)">
                                <option value="">Selecione...</option>
                                <option value="Domínio público">Domínio público</option>
                                <option value="CC BY 4.0">CC BY 4.0</option>
                                <option value="CC BY-SA 4.0">CC BY-SA 4.0</option>
                                <option value="CC BY-NC 4.0">CC BY-NC 4.0</option>
                                <option value="CC0">CC0 (Domínio público)</option>
                                <option value="Privado">Privado</option>
                                <option value="outros">Outros...</option>
                            </select>
                        </div>
                        <div class="form-group" id="licenca_outros_grupo" style="display:none;">
                            <label for="licenca_outros">Especifique a licença:</label>
                            <input type="text" id="licenca_outros" name="licenca_outros" placeholder="Descreva o tipo de direitos autorais">
                        </div>
                    </div>

                    <!-- Preview das imagens coladas -->
                    <div id="previewContainer" class="preview-container"></div>

                    <!-- Botões -->
                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary" id="btnEnviar" disabled>
                            📤 ADICIONAR À SESSÃO TEMPORÁRIA
                        </button>
                        <a href="?temp_id=<?php echo urlencode($temp_id); ?>" class="btn btn-secondary">
                            ⏪ VOLTAR AO GRID
                        </a>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- ================================================ -->
            <!-- BOTÕES DE AÇÃO GLOBAL - MODIFICADO -->
            <!-- ================================================ -->
            <div class="action-buttons">
                <!-- NOVO BOTÃO: AVANÇAR PARA DADOS (sempre visível) -->
                <a href="../Controllers/inserir_dados_internet.php?temp_id=<?php echo urlencode($temp_id); ?>" class="btn btn-avancar">
                    ➡️ AVANÇAR PARA DADOS (PASSO 3)
                </a>
                
                <!-- Botão cancelar (mantido) -->
                <a href="escolher_especie.php" class="btn btn-secondary" onclick="return confirm('Tem certeza? Todo o progresso atual será perdido.')">
                    ⏪ CANCELAR IMPORTAÇÃO
                </a>
            </div>
            
            <!-- Informação adicional -->
            <p style="text-align: center; margin-top: 20px; color: #666; font-size: 0.9rem;">
                <i class="fas fa-info-circle"></i> 
                Você pode adicionar imagens agora ou clicar em "AVANÇAR PARA DADOS" para ir para o próximo passo.
                As imagens já adicionadas ficarão salvas na sessão.
            </p>

        </div>

        <div class="footer">
            Penomato • PASSO 2 DE 3 - Upload de imagens
        </div>
    </div>

    <script>
    // ================================================
    // SCRIPT PARA COLAR IMAGEM (Ctrl+V)
    // ================================================
    const colarArea = document.getElementById('colarArea');
    const colarInput = document.getElementById('colarInput');
    const previewContainer = document.getElementById('previewContainer');
    const imagemBase64 = document.getElementById('imagemBase64');
    const btnEnviar = document.getElementById('btnEnviar');

    // Variável para armazenar a imagem colada
    let imagemColada = null;

    // Função para processar a imagem colada
    function processarImagemColada(item) {
        if (item.type && item.type.indexOf('image') !== -1) {
            const blob = item.getAsFile();
            const reader = new FileReader();

            reader.onload = function(e) {
                imagemColada = e.target.result;
                imagemBase64.value = e.target.result;

                // Mostrar imagem DENTRO da colarArea
                colarArea.innerHTML = `
                    <img src="${e.target.result}" alt="Imagem colada"
                         style="max-width:100%;max-height:320px;border-radius:6px;box-shadow:0 2px 10px rgba(0,0,0,0.15);display:block;margin:0 auto;">
                    <div style="margin-top:12px;font-size:0.9rem;color:#155724;font-weight:600;">
                        ✅ Imagem colada (${(blob.size / 1024).toFixed(1)} KB)
                    </div>
                    <button type="button" onclick="removerImagem()"
                            style="margin-top:10px;padding:6px 18px;border:2px solid #dc3545;background:white;color:#dc3545;border-radius:20px;cursor:pointer;font-weight:600;">
                        × Remover imagem
                    </button>
                `;
                colarArea.style.backgroundColor = '#e8f5e9';
                colarArea.style.borderColor = '#28a745';
                colarArea.style.padding = '20px';

                // Limpar preview separado (não é mais necessário)
                previewContainer.innerHTML = '';

                // Habilitar botão
                btnEnviar.disabled = false;
                btnEnviar.innerHTML = '📤 ADICIONAR À SESSÃO TEMPORÁRIA';
            };

            reader.readAsDataURL(blob);
        }
    }

    // Evento de colar (Ctrl+V) — usa delegação para funcionar após removerImagem()
    document.addEventListener('paste', function(e) {
        if (colarArea.contains(document.activeElement) || document.activeElement === colarArea) {
            e.preventDefault();
            const items = (e.clipboardData || e.originalEvent.clipboardData).items;
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    processarImagemColada(items[i]);
                    break;
                }
            }
        }
    });

    // Focar no textarea ao clicar na área (delegado, funciona após recriação)
    colarArea.addEventListener('click', function() {
        const input = document.getElementById('colarInput');
        if (input) {
            input.focus();
            colarArea.style.backgroundColor = '#e0f0e0';
            colarArea.style.borderColor = '#0a4c35';
        }
    });

    // Função para remover a imagem e restaurar a área de colar
    function removerImagem() {
        imagemColada = null;
        imagemBase64.value = '';
        previewContainer.innerHTML = '';
        btnEnviar.disabled = true;
        btnEnviar.innerHTML = '📤 ADICIONAR À SESSÃO TEMPORÁRIA';

        colarArea.innerHTML = `
            <div class="icone">📋</div>
            <div class="texto">Cole a imagem aqui (Ctrl+V)</div>
            <div class="subtexto">Copie a imagem de qualquer lugar e cole neste campo</div>
            <textarea id="colarInput" placeholder="Clique aqui e pressione Ctrl+V para colar a imagem..."></textarea>
        `;
        colarArea.style.backgroundColor = '#f0f8f0';
        colarArea.style.borderColor = '#0b5e42';
        colarArea.style.padding = '40px';

        // Reanexar referência ao novo textarea
        const novoColarInput = document.getElementById('colarInput');
        novoColarInput.addEventListener('focus', function() {
            colarArea.style.backgroundColor = '#e0f0e0';
            colarArea.style.borderColor = '#0a4c35';
        });
        novoColarInput.addEventListener('blur', function() {
            if (!imagemColada) {
                colarArea.style.backgroundColor = '#f0f8f0';
                colarArea.style.borderColor = '#0b5e42';
            }
        });
    }

    // Mostrar/esconder campo "Outros" na licença
    function toggleLicencaOutros(valor) {
        const grupo = document.getElementById('licenca_outros_grupo');
        const input = document.getElementById('licenca_outros');
        if (valor === 'outros') {
            grupo.style.display = 'block';
            input.required = true;
        } else {
            grupo.style.display = 'none';
            input.required = false;
            input.value = '';
        }
    }

    // Validação do formulário antes de enviar
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        if (!imagemColada) {
            e.preventDefault();
            alert('Por favor, cole uma imagem primeiro!');
            return;
        }
    });
    </script>
</body>
</html>