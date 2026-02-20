<?php
// ================================================
// UPLOAD DE IMAGENS DA INTERNET
// ================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$servidor = "127.0.0.1";
$usuario_db = "root";
$senha_db = "";
$banco = "penomato";

// Verificar se usuário está logado (temporário)
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = 1; // Usuário padrão para testes
}

$id_usuario = $_SESSION['usuario_id'];

// ================================================
// BUSCAR DADOS DA ESPÉCIE
// ================================================
$especie_id = isset($_GET['especie_id']) ? (int)$_GET['especie_id'] : 0;

if ($especie_id <= 0) {
    die("ID da espécie não informado.");
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

if ($resultado->num_rows === 0) {
    die("Espécie não encontrada.");
}

$especie = $resultado->fetch_assoc();
$stmt->close();

// ================================================
// BUSCAR IMAGENS JÁ CADASTRADAS
// ================================================
$sql_imagens = "SELECT id, parte_planta, caminho_imagem, nome_original, 
                       fonte_nome, fonte_url, autor_imagem, licenca,
                       descricao, local_coleta, data_coleta, coletor_nome,
                       status_validacao, data_upload
                FROM especies_imagens 
                WHERE especie_id = ? 
                ORDER BY parte_planta, data_upload DESC";
$stmt = $conexao->prepare($sql_imagens);
$stmt->bind_param("i", $especie_id);
$stmt->execute();
$resultado = $stmt->get_result();

$imagens = [];
$contagem_por_parte = [
    'folha' => 0, 'flor' => 0, 'fruto' => 0, 'caule' => 0,
    'semente' => 0, 'habito' => 0, 'exsicata_completa' => 0, 'detalhe' => 0
];

while ($row = $resultado->fetch_assoc()) {
    $imagens[] = $row;
    $parte = $row['parte_planta'];
    if (isset($contagem_por_parte[$parte])) {
        $contagem_por_parte[$parte]++;
    }
}

$stmt->close();
$conexao->close();

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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penomato - Upload de Imagens</title>
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

        .species-status {
            padding: 8px 15px;
            border-radius: 30px;
            font-weight: 600;
        }

        .status-sem_imagens { background-color: #f8d7da; color: #721c24; }
        .status-internet { background-color: #fff3cd; color: #856404; }
        .status-registrada { background-color: #d4edda; color: #155724; }

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
            padding: 15px;
            text-align: center;
            border: 2px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.2s;
        }

        .parte-card:hover {
            border-color: #0b5e42;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .parte-card.selecionado {
            border-color: #0b5e42;
            background-color: #e6f7e6;
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
        .upload-form {
            background-color: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
        }

        .upload-form h3 {
            color: #0b5e42;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-row {
            margin-bottom: 15px;
        }

        .form-row label {
            display: block;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .form-row input, .form-row select, .form-row textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            font-size: 1rem;
        }

        .form-row input:focus, .form-row select:focus, .form-row textarea:focus {
            outline: none;
            border-color: #0b5e42;
        }

        .form-row input[type="file"] {
            padding: 8px;
            background-color: white;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .btn-upload {
            background-color: #0b5e42;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-upload:hover {
            background-color: #0a4c35;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(11,94,66,0.3);
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

        .status-pendente { background-color: #fff3cd; color: #856404; }
        .status-aprovado { background-color: #d4edda; color: #155724; }
        .status-rejeitado { background-color: #f8d7da; color: #721c24; }

        /* Botões de ação */
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 30px 0;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #0b5e42;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0a4c35;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(11,94,66,0.3);
        }

        .btn-secondary {
            background-color: #e2e8f0;
            color: #2d3748;
        }

        .btn-secondary:hover {
            background-color: #cbd5e0;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(40,167,69,0.3);
        }

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

        .footer {
            text-align: center;
            color: #718096;
            font-size: 0.85rem;
            margin-top: 30px;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .partes-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- Cabeçalho -->
        <div class="header">
            <h1>📸 PENOMATO • UPLOAD DE IMAGENS</h1>
            <div class="subtitle">
                Adicione imagens para compor o artigo científico da espécie
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
                    <span class="species-status status-<?php echo $especie['status_imagens'] ?? 'sem_imagens'; ?>">
                        <?php 
                        $status_imagens = $especie['status_imagens'] ?? 'sem_imagens';
                        echo $status_imagens == 'sem_imagens' ? '🔴 SEM IMAGENS' : 
                             ($status_imagens == 'internet' ? '🟡 INTERNET' : '✅ REGISTRADA'); 
                        ?>
                    </span>
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

            <!-- Grid de partes da planta -->
            <div class="partes-grid" id="partesGrid">
                <?php
                $partes = [
                    'folha' => ['icone' => '🍃', 'nome' => 'Folha'],
                    'flor' => ['icone' => '🌸', 'nome' => 'Flor'],
                    'fruto' => ['icone' => '🍎', 'nome' => 'Fruto'],
                    'caule' => ['icone' => '🌿', 'nome' => 'Caule'],
                    'semente' => ['icone' => '🌱', 'nome' => 'Semente'],
                    'habito' => ['icone' => '🌳', 'nome' => 'Hábito'],
                    'exsicata_completa' => ['icone' => '📋', 'nome' => 'Exsicata'],
                    'detalhe' => ['icone' => '🔍', 'nome' => 'Detalhe']
                ];

                foreach ($partes as $key => $parte):
                    $contagem = $contagem_por_parte[$key] ?? 0;
                    $classe = in_array($key, $partes_obrigatorias) && $contagem > 0 ? 'completa' : 'incompleta';
                ?>
                <div class="parte-card" data-parte="<?php echo $key; ?>" onclick="selecionarParte('<?php echo $key; ?>')">
                    <div class="parte-icone"><?php echo $parte['icone']; ?></div>
                    <div class="parte-nome"><?php echo $parte['nome']; ?></div>
                    <div class="parte-contagem">
                        <span><?php echo $contagem; ?></span> imagem(ns)
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Formulário de upload -->
            <div class="upload-form">
                <h3>
                    <span>📤</span>
                    ADICIONAR NOVA IMAGEM
                </h3>

                <form action="processar_upload_imagem.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="especie_id" value="<?php echo $especie_id; ?>">
                    
                    <div class="form-row">
                        <label>Parte da planta *</label>
                        <select name="parte_planta" id="select_parte" required>
                            <option value="">Selecione...</option>
                            <?php foreach ($partes as $key => $parte): ?>
                            <option value="<?php echo $key; ?>"><?php echo $parte['nome'] . ' ' . $parte['icone']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <label>Arquivo de imagem * (JPG, PNG - máx 10MB)</label>
                        <input type="file" name="imagem" accept="image/jpeg,image/png,image/jpg" required>
                    </div>

                    <div class="form-row">
                        <label>Nome original (opcional)</label>
                        <input type="text" name="nome_original" placeholder="Ex: folha_anadenanthera.jpg">
                    </div>

                    <div class="form-grid">
                        <div class="form-row">
                            <label>Fonte</label>
                            <input type="text" name="fonte_nome" placeholder="Ex: Flora do Brasil, Wikipedia">
                        </div>
                        <div class="form-row">
                            <label>URL da fonte</label>
                            <input type="url" name="fonte_url" placeholder="https://...">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-row">
                            <label>Autor da imagem</label>
                            <input type="text" name="autor_imagem" placeholder="Nome do fotógrafo/autor">
                        </div>
                        <div class="form-row">
                            <label>Licença</label>
                            <select name="licenca">
                                <option value="">Selecione...</option>
                                <option value="Domínio público">Domínio público</option>
                                <option value="CC BY 4.0">CC BY 4.0</option>
                                <option value="CC BY-SA 4.0">CC BY-SA 4.0</option>
                                <option value="CC0">CC0 (Domínio público)</option>
                                <option value="Outra">Outra (especifique na descrição)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <label>Descrição / Observações</label>
                        <textarea name="descricao" rows="3" placeholder="Descrição da imagem, observações, créditos adicionais..."></textarea>
                    </div>

                    <div style="text-align: center; margin-top: 20px;">
                        <button type="submit" class="btn-upload">
                            📤 ENVIAR IMAGEM
                        </button>
                    </div>
                </form>
            </div>

            <!-- Galeria de imagens -->
            <?php if (count($imagens) > 0): ?>
            <div class="galeria">
                <h3>
                    <span>🖼️</span>
                    IMAGENS ADICIONADAS (<?php echo count($imagens); ?>)
                </h3>

                <div class="imagens-grid">
                    <?php foreach ($imagens as $img): ?>
                    <div class="imagem-card">
                        <div class="imagem-preview">
                            <img src="../<?php echo htmlspecialchars($img['caminho_imagem']); ?>" 
                                 alt="<?php echo htmlspecialchars($img['nome_original'] ?? 'Imagem'); ?>"
                                 onerror="this.src='../assets/img/no-image.png';">
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
                                <strong>Fonte:</strong> <?php echo htmlspecialchars($img['fonte_nome']); ?>
                            </div>
                            <?php endif; ?>
                            <?php if ($img['autor_imagem']): ?>
                            <div class="imagem-creditos">
                                <strong>Autor:</strong> <?php echo htmlspecialchars($img['autor_imagem']); ?>
                            </div>
                            <?php endif; ?>
                            <div style="margin-top: 8px;">
                                <span class="imagem-status status-<?php echo $img['status_validacao']; ?>">
                                    <?php echo $img['status_validacao'] == 'pendente' ? '⏳ Pendente' : 
                                         ($img['status_validacao'] == 'aprovado' ? '✅ Aprovado' : '❌ Rejeitado'); ?>
                                </span>
                            </div>
                            <div style="margin-top: 8px; font-size: 0.8rem; color: #999;">
                                <?php echo date('d/m/Y', strtotime($img['data_upload'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Botões de ação -->
            <div class="action-buttons">
                <?php if ($partes_completas == count($partes_obrigatorias)): ?>
                <a href="finalizar_upload.php?especie_id=<?php echo $especie_id; ?>" class="btn btn-success">
                    ✅ FINALIZAR E IR PARA REVISÃO
                </a>
                <?php endif; ?>
                <a href="inserir_dados_internet.php" class="btn btn-secondary">
                    ⏪ VOLTAR
                </a>
            </div>

        </div>

        <div class="footer">
            Penomato • Adicione imagens para compor o artigo científico
        </div>
    </div>

    <script>
    function selecionarParte(parte) {
        // Remover seleção anterior
        document.querySelectorAll('.parte-card').forEach(card => {
            card.classList.remove('selecionado');
        });
        
        // Adicionar seleção na parte clicada
        document.querySelector(`[data-parte="${parte}"]`).classList.add('selecionado');
        
        // Selecionar no dropdown
        document.getElementById('select_parte').value = parte;
        
        // Rolar até o formulário
        document.querySelector('.upload-form').scrollIntoView({ behavior: 'smooth' });
    }
    </script>
</body>
</html>