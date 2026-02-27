<?php
// ================================================
// UPLOAD DE IMAGENS - VERSÃO COM CONTROLE DE USUÁRIO
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

        /* Formulário de upload por parte */
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

        .upload-area {
            border: 2px dashed #0b5e42;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            background-color: #f0f8f0;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
        }

        .upload-area:hover {
            background-color: #e0f0e0;
            border-color: #0a4c35;
        }

        .upload-area .icone {
            font-size: 4rem;
            margin-bottom: 15px;
            color: #0b5e42;
        }

        .upload-area .texto {
            font-size: 1.2rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .upload-area .subtexto {
            font-size: 0.9rem;
            color: #666;
        }

        /* Preview das imagens selecionadas para esta parte */
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

        /* Campos de metadados */
        .metadata-fields {
            background-color: #edf2f7;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .metadata-fields h4 {
            color: #0b5e42;
            margin-bottom: 15px;
        }

        .metadata-fields .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .metadata-fields label {
            display: block;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .metadata-fields input,
        .metadata-fields select {
            width: 100%;
            padding: 8px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
        }

        .metadata-fields input:focus,
        .metadata-fields select:focus {
            outline: none;
            border-color: #0b5e42;
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
            .metadata-fields .form-grid {
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
        
        <!-- Cabeçalho -->
        <div class="header">
            <h1>📸 PENOMATO • UPLOAD DE IMAGENS</h1>
            <div class="subtitle">
                Adicione as imagens para cada parte da planta
            </div>
        </div>

        <!-- Aviso temporário -->
        <div class="temp-warning">
            <span>⚠️</span>
            <div>
                <strong>Dados temporários</strong> - As imagens ainda não foram salvas no banco.
                Utilize os botões abaixo para adicionar imagens a cada parte.
                Ao finalizar, tudo será salvo permanentemente.
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
                    
                    <!-- Área de upload -->
                    <div class="upload-area" id="uploadArea" onclick="document.getElementById('fileInput').click()">
                        <div class="icone">📁</div>
                        <div class="texto">Clique aqui ou arraste as imagens para <?php echo $parte_atual['nome']; ?></div>
                        <div class="subtexto">Formatos: JPG, PNG (máx 10MB por imagem)</div>
                        <input type="file" id="fileInput" name="imagens[]" multiple accept="image/jpeg,image/png,image/jpg" style="display: none;" onchange="handleFiles(this.files)">
                    </div>

                    <!-- Metadados comuns -->
                    <div class="metadata-fields">
                        <h4>📋 Metadados (aplicados a todas as imagens desta parte)</h4>
                        <div class="form-grid">
                            <div>
                                <label>Fonte</label>
                                <input type="text" name="fonte_nome" placeholder="Ex: Flora do Brasil">
                            </div>
                            <div>
                                <label>URL da fonte</label>
                                <input type="url" name="fonte_url" placeholder="https://...">
                            </div>
                            <div>
                                <label>Autor da imagem</label>
                                <input type="text" name="autor_imagem" placeholder="Nome do autor">
                            </div>
                            <div>
                                <label>Licença</label>
                                <select name="licenca">
                                    <option value="">Selecione...</option>
                                    <option value="Domínio público">Domínio público</option>
                                    <option value="CC BY 4.0">CC BY 4.0</option>
                                    <option value="CC BY-SA 4.0">CC BY-SA 4.0</option>
                                    <option value="CC0">CC0 (Domínio público)</option>
                                </select>
                            </div>
                            <div>
                                <label>Descrição (opcional)</label>
                                <input type="text" name="descricao" placeholder="Descrição geral">
                            </div>
                        </div>
                    </div>

                    <!-- Preview das imagens selecionadas -->
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

            <!-- Botões de ação global -->
            <div class="action-buttons">
                <?php if ($partes_completas == count($partes_obrigatorias)): ?>
                <a href="../Controllers/finalizar_upload_temporario.php?temp_id=<?php echo urlencode($temp_id); ?>" class="btn btn-success">
                    ✅ FINALIZAR E SALVAR TUDO NO BANCO
                </a>
                <?php else: ?>
                <button class="btn btn-success" disabled title="Complete todas as partes obrigatórias primeiro">
                    ⏳ AGUARDANDO PARTES OBRIGATÓRIAS
                </button>
                <?php endif; ?>
                <a href="../Controllers/inserir_dados_internet.php" class="btn btn-secondary" onclick="return confirm('Tem certeza? Todo o progresso atual será perdido.')">
                    ⏪ CANCELAR IMPORTAÇÃO
                </a>
            </div>

        </div>

        <div class="footer">
            Penomato • Upload temporário - Nada foi salvo permanentemente ainda
        </div>
    </div>

    <script>
    let arquivosSelecionados = [];

    // Elementos DOM
    const previewContainer = document.getElementById('previewContainer');
    const btnEnviar = document.getElementById('btnEnviar');

    // Função para lidar com arquivos selecionados
    function handleFiles(files) {
        arquivosSelecionados = Array.from(files);
        atualizarPreview();
    }

    // Função para atualizar preview das imagens
    function atualizarPreview() {
        previewContainer.innerHTML = '';
        
        if (arquivosSelecionados.length === 0) {
            btnEnviar.disabled = true;
            btnEnviar.innerHTML = '📤 ADICIONAR À SESSÃO TEMPORÁRIA';
            return;
        }

        btnEnviar.disabled = false;
        btnEnviar.innerHTML = `📤 ADICIONAR ${arquivosSelecionados.length} IMAGEM(S)`;

        arquivosSelecionados.forEach((arquivo, index) => {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item';
                previewItem.dataset.index = index;

                previewItem.innerHTML = `
                    <div class="preview-image">
                        <img src="${e.target.result}" alt="Preview">
                    </div>
                    <div class="preview-info">
                        <strong>${arquivo.name}</strong>
                        <small>${(arquivo.size / 1024).toFixed(1)} KB</small>
                    </div>
                    <div class="remove-btn" onclick="removerImagem(${index})">×</div>
                `;

                previewContainer.appendChild(previewItem);
            };

            reader.readAsDataURL(arquivo);
        });
    }

    // Função para remover imagem da lista
    function removerImagem(index) {
        arquivosSelecionados.splice(index, 1);
        atualizarPreview();
    }

    // Evento de drag and drop
    const uploadArea = document.getElementById('uploadArea');

    if (uploadArea) {
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.style.backgroundColor = '#e0f0e0';
            uploadArea.style.borderColor = '#0a4c35';
        });

        uploadArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            uploadArea.style.backgroundColor = '#f0f8f0';
            uploadArea.style.borderColor = '#0b5e42';
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.style.backgroundColor = '#f0f8f0';
            uploadArea.style.borderColor = '#0b5e42';
            
            const files = e.dataTransfer.files;
            handleFiles(files);
        });
    }

    // Prevenir comportamento padrão do drag and drop
    document.addEventListener('dragover', (e) => e.preventDefault());
    document.addEventListener('drop', (e) => e.preventDefault());
    </script>
</body>
</html>