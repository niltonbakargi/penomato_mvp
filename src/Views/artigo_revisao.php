<?php
// artigo_revisao.php
// Local: C:\xampp\htdocs\penomato_mvp\src\Views\artigo_revisao.php
// VERSÃO CORRIGIDA - 15/02/2026
// Integrada com o controlador e nova estrutura do banco

session_start();

// ================================================
// VERIFICAÇÕES INICIAIS
// ================================================

// Verificar se usuário está logado
$usuario_id = $_SESSION['usuario_id'] ?? 0;
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Revisor';
$usuario_tipo = $_SESSION['usuario_tipo'] ?? '';

if (!$usuario_id) {
    header('Location: ../../login.php');
    exit;
}

// Verificar se é revisor (pode ser gestor também)
if ($usuario_tipo !== 'revisor' && $usuario_tipo !== 'gestor') {
    die('Acesso negado. Apenas revisores podem acessar esta página.');
}

// ================================================
// PARÂMETROS
// ================================================

$especie_id = $_GET['id'] ?? 0;
if (!$especie_id) {
    header('Location: ../Controllers/controlador_painel_revisor.php');
    exit;
}

// ================================================
// CONEXÃO COM O BANCO
// ================================================

$conn = mysqli_connect("127.0.0.1", "root", "", "penomato");
if (!$conn) {
    die("Erro de conexão: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

// ================================================
// VERIFICAR SE ESPÉCIE ESTÁ EM REVISÃO
// ================================================

$sql_check = "SELECT status FROM especies_administrativo WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt, "i", $especie_id);
mysqli_stmt_execute($stmt);
$result_check = mysqli_stmt_get_result($stmt);
$status_data = mysqli_fetch_assoc($result_check);
mysqli_stmt_close($stmt);

if (!$status_data) {
    die("Espécie não encontrada");
}

if ($status_data['status'] !== 'em_revisao') {
    // Se não está em revisão, redireciona
    header('Location: ../Controllers/controlador_painel_revisor.php?erro=nao_em_revisao');
    exit;
}

// ================================================
// BUSCAR DADOS DA ESPÉCIE
// ================================================

// Buscar informações básicas da espécie
$sql_especie = "SELECT 
                    id,
                    nome_cientifico,
                    status,
                    prioridade,
                    data_registrada,
                    data_revisada,
                    autor_revisada_id,
                    motivo_contestado
                FROM especies_administrativo 
                WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql_especie);
mysqli_stmt_bind_param($stmt, "i", $especie_id);
mysqli_stmt_execute($stmt);
$result_especie = mysqli_stmt_get_result($stmt);
$especie = mysqli_fetch_assoc($result_especie);
mysqli_stmt_close($stmt);

// Buscar características da espécie
$sql_caracteristicas = "SELECT * FROM especies_caracteristicas WHERE especie_id = ?";
$stmt = mysqli_prepare($conn, $sql_caracteristicas);
mysqli_stmt_bind_param($stmt, "i", $especie_id);
mysqli_stmt_execute($stmt);
$result_caracteristicas = mysqli_stmt_get_result($stmt);
$caracteristicas = mysqli_fetch_assoc($result_caracteristicas);
mysqli_stmt_close($stmt);

// ================================================
// BUSCAR IMAGENS DA ESPÉCIE
// ================================================

$imagens = [];
$partes = ['folha', 'flor', 'fruto', 'caule', 'semente', 'habito'];

foreach ($partes as $parte) {
    $sql_imagens = "SELECT 
                        id,
                        caminho_imagem,
                        descricao,
                        data_upload,
                        id_usuario_identificador
                    FROM imagens_especies 
                    WHERE especie_id = ? AND parte = ?
                    ORDER BY data_upload DESC";
    
    $stmt = mysqli_prepare($conn, $sql_imagens);
    mysqli_stmt_bind_param($stmt, "is", $especie_id, $parte);
    mysqli_stmt_execute($stmt);
    $result_imagens = mysqli_stmt_get_result($stmt);
    
    $imagens[$parte] = [];
    while ($img = mysqli_fetch_assoc($result_imagens)) {
        $imagens[$parte][] = $img;
    }
    mysqli_stmt_close($stmt);
}

// ================================================
// PROCESSAR REFERÊNCIAS
// ================================================

$referencias = [];
if ($caracteristicas && !empty($caracteristicas['referencias'])) {
    $refs = explode("\n", $caracteristicas['referencias']);
    foreach ($refs as $index => $ref) {
        $referencias[$index + 1] = trim($ref);
    }
}

mysqli_close($conn);

// ================================================
// FUNÇÃO AUXILIAR
// ================================================

function exibirCaracteristica($valor, $ref) {
    if (empty($valor)) return '';
    
    $html = htmlspecialchars($valor);
    if (!empty($ref)) {
        $refs = explode(',', $ref);
        foreach ($refs as $r) {
            $r = trim($r);
            if (!empty($r)) {
                $html .= " <span class='ref-link' data-ref='$r'>[$r]</span>";
            }
        }
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisão: <?php echo htmlspecialchars($especie['nome_cientifico']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f0f4f0;
            padding: 20px;
            color: #1e2e1e;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 15px;
            color: #0b5e42;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .header {
            background: #0b5e42;
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .header h1 {
            font-size: 1.8em;
            margin-bottom: 5px;
        }

        .header-meta {
            font-size: 0.95em;
            opacity: 0.9;
            line-height: 1.5;
        }

        .badge-status {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .grid-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 1.3em;
            color: #0b5e42;
            margin-bottom: 15px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }

        .section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .section-title {
            font-size: 1.2em;
            color: #0b5e42;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .char-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .char-item {
            margin-bottom: 10px;
        }

        .char-label {
            font-weight: bold;
            font-size: 0.8em;
            color: #666;
            text-transform: uppercase;
        }

        .char-value {
            font-size: 1em;
            display: flex;
            align-items: center;
            gap: 5px;
            flex-wrap: wrap;
        }

        .ref-link {
            background: #e9ecef;
            color: #0b5e42;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 0.7em;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            display: inline-block;
        }

        .ref-link:hover {
            background: #0b5e42;
            color: white;
        }

        .references {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }

        .references h3 {
            color: #0b5e42;
            margin-bottom: 15px;
        }

        .ref-item {
            padding: 8px 0;
            border-bottom: 1px dashed #ddd;
            font-size: 0.9em;
        }

        .ref-number {
            display: inline-block;
            background: #0b5e42;
            color: white;
            width: 22px;
            height: 22px;
            text-align: center;
            border-radius: 4px;
            font-size: 0.8em;
            margin-right: 10px;
            line-height: 22px;
        }

        /* ========== IMAGENS ========== */
        .imagens-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .imagem-card {
            background: #f8f9fa;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #ddd;
        }

        .imagem-preview {
            width: 100%;
            height: 120px;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 2em;
            cursor: pointer;
        }

        .imagem-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .imagem-info {
            padding: 8px;
            font-size: 0.8em;
        }

        .imagem-descricao {
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sem-imagem {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #999;
            border-radius: 8px;
            border: 2px dashed #ddd;
        }

        /* ========== DECISÃO ========== */
        .decision-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-top: 30px;
            border: 2px solid #0b5e42;
        }

        .decision-title {
            font-size: 1.3em;
            color: #0b5e42;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .radio-group {
            display: flex;
            gap: 30px;
            margin-bottom: 20px;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 10px 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .radio-group label:hover {
            border-color: #0b5e42;
            background: #f0fdf4;
        }

        .radio-group input[type="radio"] {
            width: 18px;
            height: 18px;
        }

        textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            margin: 15px 0;
            font-family: inherit;
            resize: vertical;
            font-size: 1em;
        }

        textarea:focus {
            border-color: #0b5e42;
            outline: none;
        }

        .action-bar {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1em;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            max-width: 90%;
            max-height: 90%;
        }

        .modal-content img {
            max-width: 100%;
            max-height: 90vh;
            border-radius: 8px;
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 2em;
            cursor: pointer;
        }

        .info-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        @media (max-width: 768px) {
            .grid-2col {
                grid-template-columns: 1fr;
            }
            
            .radio-group {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- LINK VOLTAR -->
        <a href="../Controllers/controlador_painel_revisor.php" class="back-link">← Voltar ao painel</a>

        <!-- CABEÇALHO -->
        <div class="header">
            <div>
                <h1><?php echo htmlspecialchars($especie['nome_cientifico']); ?></h1>
                <div class="header-meta">
                    <?php if (!empty($caracteristicas['familia'])): ?>
                        <div>🌿 Família: <?php echo htmlspecialchars($caracteristicas['familia']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($caracteristicas['nome_popular'])): ?>
                        <div>🌳 Nome popular: <?php echo htmlspecialchars($caracteristicas['nome_popular']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <span class="badge-status">EM REVISÃO</span>
            </div>
        </div>

        <!-- GRID PRINCIPAL: CARACTERÍSTICAS + IMAGENS -->
        <div class="grid-2col">
            <!-- COLUNA 1: CARACTERÍSTICAS -->
            <div>
                <div class="card">
                    <div class="card-title">📋 CARACTERÍSTICAS MORFOLÓGICAS</div>
                    
                    <?php if ($caracteristicas): ?>

                        <!-- Nome científico completo -->
                        <?php if (!empty($caracteristicas['nome_cientifico_completo'])): ?>
                        <div class="section">
                            <div class="section-title">📋 Nome Científico Completo</div>
                            <div class="char-value">
                                <?php echo exibirCaracteristica(
                                    $caracteristicas['nome_cientifico_completo'],
                                    $caracteristicas['nome_cientifico_completo_ref'] ?? ''
                                ); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- FOLHA -->
                        <?php 
                        $tem_folha = !empty($caracteristicas['forma_folha']) || 
                                     !empty($caracteristicas['filotaxia_folha']) || 
                                     !empty($caracteristicas['tipo_folha']) ||
                                     !empty($caracteristicas['margem_folha']) ||
                                     !empty($caracteristicas['textura_folha']) ||
                                     !empty($caracteristicas['venacao_folha']) ||
                                     !empty($caracteristicas['tamanho_folha']);
                        
                        if ($tem_folha): 
                        ?>
                        <div class="section">
                            <div class="section-title">🍃 Folha</div>
                            <div class="char-grid">
                                <?php if (!empty($caracteristicas['forma_folha'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Forma</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $caracteristicas['forma_folha'],
                                            $caracteristicas['forma_folha_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($caracteristicas['filotaxia_folha'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Filotaxia</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $caracteristicas['filotaxia_folha'],
                                            $caracteristicas['filotaxia_folha_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($caracteristicas['tipo_folha'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Tipo</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $caracteristicas['tipo_folha'],
                                            $caracteristicas['tipo_folha_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($caracteristicas['margem_folha'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Margem</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $caracteristicas['margem_folha'],
                                            $caracteristicas['margem_folha_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- FLOR -->
                        <?php 
                        $tem_flor = !empty($caracteristicas['cor_flores']) || 
                                    !empty($caracteristicas['simetria_floral']) || 
                                    !empty($caracteristicas['numero_petalas']) ||
                                    !empty($caracteristicas['aroma']);
                        
                        if ($tem_flor): 
                        ?>
                        <div class="section">
                            <div class="section-title">🌸 Flor</div>
                            <div class="char-grid">
                                <?php if (!empty($caracteristicas['cor_flores'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Cor</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $caracteristicas['cor_flores'],
                                            $caracteristicas['cor_flores_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($caracteristicas['aroma'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Aroma</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $caracteristicas['aroma'],
                                            $caracteristicas['aroma_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($caracteristicas['numero_petalas'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Nº pétalas</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $caracteristicas['numero_petalas'],
                                            $caracteristicas['numero_petalas_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- FRUTO -->
                        <?php 
                        $tem_fruto = !empty($caracteristicas['tipo_fruto']) || 
                                     !empty($caracteristicas['dispersao_fruto']) ||
                                     !empty($caracteristicas['cor_fruto']);
                        
                        if ($tem_fruto): 
                        ?>
                        <div class="section">
                            <div class="section-title">🍎 Fruto</div>
                            <div class="char-grid">
                                <?php if (!empty($caracteristicas['tipo_fruto'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Tipo</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $caracteristicas['tipo_fruto'],
                                            $caracteristicas['tipo_fruto_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($caracteristicas['dispersao_fruto'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Dispersão</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $caracteristicas['dispersao_fruto'],
                                            $caracteristicas['dispersao_fruto_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- OUTRAS PARTES (resumo) -->
                        <div class="section">
                            <div class="section-title">📦 Outras Partes</div>
                            <div class="char-grid">
                                <?php if (!empty($caracteristicas['tipo_semente'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Semente</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $caracteristicas['tipo_semente'],
                                            $caracteristicas['tipo_semente_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($caracteristicas['tipo_caule'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Caule</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $caracteristicas['tipo_caule'],
                                            $caracteristicas['tipo_caule_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- REFERÊNCIAS -->
                        <?php if (!empty($referencias)): ?>
                        <div class="references">
                            <h3>📚 Referências</h3>
                            <?php foreach ($referencias as $num => $ref): ?>
                                <div class="ref-item" id="ref-<?php echo $num; ?>">
                                    <span class="ref-number"><?php echo $num; ?></span>
                                    <?php echo htmlspecialchars($ref); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="info-box">
                            <strong>Atenção:</strong> Nenhuma característica cadastrada para esta espécie.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- COLUNA 2: IMAGENS -->
            <div>
                <div class="card">
                    <div class="card-title">📸 IMAGENS ENVIADAS</div>
                    
                    <?php foreach ($partes as $parte): 
                        $icones = [
                            'folha' => '🍃',
                            'flor' => '🌸', 
                            'fruto' => '🍎',
                            'caule' => '🌿',
                            'semente' => '🌱',
                            'habito' => '🌳'
                        ];
                        $nomes = [
                            'folha' => 'Folha',
                            'flor' => 'Flor',
                            'fruto' => 'Fruto',
                            'caule' => 'Caule',
                            'semente' => 'Semente',
                            'habito' => 'Hábito'
                        ];
                    ?>
                        <div class="section">
                            <div class="section-title"><?php echo $icones[$parte] . ' ' . $nomes[$parte]; ?></div>
                            
                            <?php if (count($imagens[$parte]) > 0): ?>
                                <div class="imagens-grid">
                                    <?php foreach ($imagens[$parte] as $img): ?>
                                    <div class="imagem-card">
                                        <div class="imagem-preview" onclick="abrirImagem('<?php echo '../../' . $img['caminho_imagem']; ?>')">
                                            <?php if (file_exists('../../' . $img['caminho_imagem'])): ?>
                                                <img src="<?php echo '../../' . $img['caminho_imagem']; ?>" alt="Imagem">
                                            <?php else: ?>
                                                <span>🖼️</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="imagem-info">
                                            <div class="imagem-descricao"><?php echo htmlspecialchars($img['descricao']); ?></div>
                                            <div style="color: #666; margin-top: 4px;">
                                                <?php echo date('d/m/Y', strtotime($img['data_upload'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="sem-imagem">
                                    <span style="font-size: 2em;">📸</span>
                                    <p>Nenhuma imagem enviada para esta parte</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- DECISÃO DA REVISÃO -->
        <div class="decision-box">
            <div class="decision-title">⚖️ DECISÃO DA REVISÃO</div>
            
            <form id="formDecisao" method="POST" action="../Controllers/controlador_painel_revisor.php">
                <input type="hidden" name="especie_id" value="<?php echo $especie_id; ?>">
                
                <div class="radio-group">
                    <label>
                        <input type="radio" name="decisao" value="aprovar" required> 
                        <span style="font-size: 1.2em;">✅</span> APROVAR - Dados corretos e completos
                    </label>
                    <label>
                        <input type="radio" name="decisao" value="contestar" required> 
                        <span style="font-size: 1.2em;">❌</span> REJEITAR (Contestar) - Precisa de ajustes
                    </label>
                </div>

                <textarea name="motivo" id="motivo" rows="4" 
                    placeholder="Motivo da decisão (obrigatório se rejeitar)..." 
                    style="width: 100%;"><?php echo isset($_GET['motivo']) ? htmlspecialchars($_GET['motivo']) : ''; ?></textarea>

                <div class="action-bar">
                    <button type="submit" name="acao" value="aprovar" class="btn btn-success" onclick="return validarDecisao('aprovar')">
                        ✅ CONFIRMAR APROVAÇÃO
                    </button>
                    <button type="submit" name="acao" value="contestar" class="btn btn-danger" onclick="return validarDecisao('contestar')">
                        ❌ CONFIRMAR REJEIÇÃO
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='../Controllers/controlador_painel_revisor.php'">
                        CANCELAR
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL PARA VISUALIZAR IMAGENS -->
    <div id="modalImagem" class="modal" onclick="fecharModal()">
        <span class="modal-close" onclick="fecharModal()">&times;</span>
        <div class="modal-content" id="modalContent"></div>
    </div>

    <script>
        function abrirReferencia(num) {
            const target = document.getElementById('ref-' + num);
            if (target) {
                target.style.backgroundColor = '#fff3cd';
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => {
                    target.style.backgroundColor = '';
                }, 2000);
            }
        }

        function abrirImagem(caminho) {
            const modal = document.getElementById('modalImagem');
            const content = document.getElementById('modalContent');
            content.innerHTML = `<img src="${caminho}" alt="Imagem">`;
            modal.classList.add('active');
        }

        function fecharModal() {
            document.getElementById('modalImagem').classList.remove('active');
        }

        function validarDecisao(acao) {
            const motivo = document.getElementById('motivo').value;
            
            if (acao === 'contestar' && !motivo.trim()) {
                alert('O motivo é obrigatório para rejeitar uma espécie.');
                return false;
            }
            
            if (acao === 'aprovar') {
                return confirm('Confirmar aprovação desta espécie? Esta ação não pode ser desfeita.');
            } else {
                return confirm('Confirmar rejeição? O motivo será registrado e o identificador será notificado.');
            }
        }

        // Links de referência
        document.querySelectorAll('.ref-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const ref = link.getAttribute('data-ref');
                if (ref) abrirReferencia(ref);
            });
        });

        // Fechar modal com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') fecharModal();
        });
    </script>
</body>
</html>