<?php
// artigo_revisao.php
// MVP - Versão corrigida e integrada com PDO

session_start();

// ================================================
// CARREGAR CONFIGURAÇÃO
// ================================================
require_once __DIR__ . '/../../config/banco_de_dados.php';

// ================================================
// VERIFICAÇÕES DE ACESSO
// ================================================
$usuario_id = $_SESSION['usuario_id'] ?? 0;
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Revisor';
$usuario_tipo = $_SESSION['usuario_tipo'] ?? '';

if (!$usuario_id) {
    header('Location: ' . APP_BASE . '/index.php');
    exit;
}

// Apenas revisores e gestores podem acessar
if ($usuario_tipo !== 'revisor' && $usuario_tipo !== 'gestor') {
    die('Acesso negado. Apenas revisores podem acessar esta página.');
}

// ================================================
// PARÂMETROS
// ================================================
$especie_id = $_GET['id'] ?? 0;
if (!$especie_id) {
    header('Location: ' . APP_BASE . '/src/Controllers/controlador_painel_revisor.php');
    exit;
}

// ================================================
// VERIFICAR SE ESPÉCIE ESTÁ EM REVISÃO
// ================================================
try {
    // Primeiro, verificar se o usuário já está revisando esta espécie
    // (por segurança, mas como não temos campo, apenas verificamos status)
    $sql_check = "SELECT status FROM especies_administrativo WHERE id = ?";
    $stmt = $pdo->prepare($sql_check);
    $stmt->execute([$especie_id]);
    $status_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$status_data) {
        die("Espécie não encontrada");
    }
    
    // Se não estiver em revisão, tentar bloquear para este revisor
    if ($status_data['status'] !== 'em_revisao') {
        // Tenta atualizar para 'em_revisao' se ainda estiver disponível
        $sql_lock = "UPDATE especies_administrativo 
                     SET status = 'em_revisao', 
                         data_ultima_atualizacao = NOW() 
                     WHERE id = ? AND status = 'registrada'";
        $stmt_lock = $pdo->prepare($sql_lock);
        $stmt_lock->execute([$especie_id]);
        
        if ($stmt_lock->rowCount() === 0) {
            // Não conseguiu travar - redireciona
            header('Location: ' . APP_BASE . '/src/Controllers/controlador_painel_revisor.php?erro=indisponivel');
            exit;
        }
    }
    
} catch (Exception $e) {
    die("Erro ao verificar espécie: " . $e->getMessage());
}

// ================================================
// BUSCAR DADOS DA ESPÉCIE
// ================================================
try {
    // Dados administrativos
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
    $stmt = $pdo->prepare($sql_especie);
    $stmt->execute([$especie_id]);
    $especie = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$especie) {
        die("Espécie não encontrada");
    }
    
    // Características (com nomes de campos corrigidos)
    $sql_carac = "SELECT * FROM especies_caracteristicas WHERE especie_id = ?";
    $stmt = $pdo->prepare($sql_carac);
    $stmt->execute([$especie_id]);
    $carac = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Imagens por parte
    $partes = ['folha', 'flor', 'fruto', 'caule', 'semente', 'habito'];
    $imagens = [];
    
    foreach ($partes as $parte) {
        $sql_img = "SELECT 
                        id,
                        caminho_imagem,
                        descricao,
                        data_upload
                    FROM imagens_especies 
                    WHERE especie_id = ? AND parte = ?
                    ORDER BY data_upload DESC";
        $stmt = $pdo->prepare($sql_img);
        $stmt->execute([$especie_id, $parte]);
        $imagens[$parte] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Processar referências
    $referencias = [];
    if ($carac && !empty($carac['referencias'])) {
        $refs = explode("\n", $carac['referencias']);
        foreach ($refs as $index => $ref) {
            $referencias[$index + 1] = trim($ref);
        }
    }
    
} catch (Exception $e) {
    die("Erro ao buscar dados: " . $e->getMessage());
}

// ================================================
// FUNÇÃO AUXILIAR
// ================================================
function exibirCaracteristica($valor, $ref) {
    if (empty($valor) && $valor !== '0') return '';
    
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

// Mapeamento de ícones
$icones = [
    'folha' => '🍃',
    'flor' => '🌸', 
    'fruto' => '🍎',
    'caule' => '🌿',
    'semente' => '🌱',
    'habito' => '🌳'
];

$nomes_partes = [
    'folha' => 'Folha',
    'flor' => 'Flor',
    'fruto' => 'Fruto',
    'caule' => 'Caule',
    'semente' => 'Semente',
    'habito' => 'Hábito'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisão: <?php echo htmlspecialchars($especie['nome_cientifico']); ?></title>
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
    <style>
        body {
            background-color: var(--cinza-50);
            padding: 20px;
            color: var(--cinza-900);
        }
        .container { max-width: 1200px; margin: 0 auto; }
        
        .back-link {
            display: inline-block;
            margin-bottom: 15px;
            color: var(--cor-primaria);
            text-decoration: none;
            font-weight: bold;
        }
        .back-link:hover { text-decoration: underline; }
        
        .header {
            background: var(--cor-primaria);
            color: var(--branco);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .header h1 { font-size: 1.8em; margin-bottom: 5px; }
        .header-meta { font-size: 0.95em; opacity: 0.9; line-height: 1.5; }
        .badge-status {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            background: rgba(255,255,255,0.2);
            color: var(--branco);
        }
        
        .grid-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }
        
        .card {
            background: var(--branco);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .card-title {
            font-size: 1.3em;
            color: var(--cor-primaria);
            margin-bottom: 15px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 2px solid var(--cinza-200);
            padding-bottom: 10px;
        }
        
        .section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--cinza-200);
        }
        
        .section-title {
            font-size: 1.2em;
            color: var(--cor-primaria);
            margin-bottom: 15px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 5px;
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
            color: var(--cinza-500);
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
            background: var(--cinza-200);
            color: var(--cor-primaria);
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 0.7em;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            display: inline-block;
        }
        .ref-link:hover {
            background: var(--cor-primaria);
            color: var(--branco);
        }
        
        .references {
            background: var(--cinza-50);
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }
        .references h3 {
            color: var(--cor-primaria);
            margin-bottom: 15px;
        }
        .ref-item {
            padding: 8px 0;
            border-bottom: 1px dashed var(--cinza-300);
            font-size: 0.9em;
        }
        .ref-number {
            display: inline-block;
            background: var(--cor-primaria);
            color: var(--branco);
            width: 22px;
            height: 22px;
            text-align: center;
            border-radius: 4px;
            font-size: 0.8em;
            margin-right: 10px;
            line-height: 22px;
        }
        
        /* Imagens */
        .imagens-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .imagem-card {
            background: var(--cinza-50);
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--cinza-300);
        }
        .imagem-preview {
            width: 100%;
            height: 120px;
            background: var(--cinza-200);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--cinza-500);
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
            background: var(--cinza-50);
            padding: 20px;
            text-align: center;
            color: var(--cinza-400);
            border-radius: 8px;
            border: 2px dashed var(--cinza-300);
        }
        
        /* Decisão */
        .decision-box {
            background: var(--branco);
            padding: 25px;
            border-radius: 12px;
            margin-top: 30px;
            border: 2px solid var(--cor-primaria);
        }
        .decision-title {
            font-size: 1.3em;
            color: var(--cor-primaria);
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
            border: 2px solid var(--cinza-300);
            border-radius: 8px;
            transition: all 0.2s;
            flex: 1;
        }
        .radio-group label:hover {
            border-color: var(--cor-primaria);
            background: var(--verde-50);
        }
        .radio-group input[type="radio"] {
            width: 18px;
            height: 18px;
        }
        textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid var(--cinza-300);
            border-radius: 8px;
            margin: 15px 0;
            font-family: inherit;
            resize: vertical;
            font-size: 1em;
        }
        textarea:focus {
            border-color: var(--cor-primaria);
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
            background: var(--sucesso-cor);
            color: var(--branco);
        }
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        .btn-danger {
            background: var(--perigo-cor);
            color: var(--branco);
        }
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: var(--cinza-500);
            color: var(--branco);
        }
        .btn-secondary:hover {
            background: var(--cinza-600);
            transform: translateY(-2px);
        }
        
        /* Modal */
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
        .modal.active { display: flex; }
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
            color: var(--branco);
            font-size: 2em;
            cursor: pointer;
        }
        
        .info-box {
            background: var(--aviso-fundo);
            border-left: 4px solid var(--aviso-borda);
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        @media (max-width: 768px) {
            .grid-2col { grid-template-columns: 1fr; }
            .radio-group { flex-direction: column; gap: 10px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- LINK VOLTAR -->
        <a href="/penomato_mvp/src/Controllers/controlador_painel_revisor.php" class="back-link">← Voltar ao painel</a>

        <!-- CABEÇALHO -->
        <div class="header">
            <div>
                <h1><?php echo htmlspecialchars($especie['nome_cientifico']); ?></h1>
                <div class="header-meta">
                    <?php if (!empty($carac['familia'])): ?>
                        <div>🌿 Família: <?php echo htmlspecialchars($carac['familia']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($carac['nome_popular'])): ?>
                        <div>🌳 Nome popular: <?php echo htmlspecialchars($carac['nome_popular']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <span class="badge-status">EM REVISÃO</span>
            </div>
        </div>

        <!-- GRID PRINCIPAL -->
        <div class="grid-2col">
            <!-- COLUNA 1: CARACTERÍSTICAS -->
            <div>
                <div class="card">
                    <div class="card-title">📋 CARACTERÍSTICAS MORFOLÓGICAS</div>
                    
                    <?php if ($carac): ?>

                        <!-- Nome científico completo -->
                        <?php if (!empty($carac['nome_cientifico_completo'])): ?>
                        <div class="section">
                            <div class="section-title">📋 Nome Científico Completo</div>
                            <div class="char-value">
                                <?php echo exibirCaracteristica(
                                    $carac['nome_cientifico_completo'],
                                    $carac['nome_cientifico_completo_ref'] ?? ''
                                ); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- FOLHA -->
                        <?php 
                        $tem_folha = !empty($carac['forma_folha']) || 
                                     !empty($carac['filotaxia']) || 
                                     !empty($carac['tipo_folha']) ||
                                     !empty($carac['margem_folha']) ||
                                     !empty($carac['textura_folha']) ||
                                     !empty($carac['venacao_folha']) ||
                                     !empty($carac['tamanho_folha']);
                        
                        if ($tem_folha): 
                        ?>
                        <div class="section">
                            <div class="section-title">🍃 Folha</div>
                            <div class="char-grid">
                                <?php if (!empty($carac['forma_folha'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Forma</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $carac['forma_folha'],
                                            $carac['forma_folha_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($carac['filotaxia'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Filotaxia</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $carac['filotaxia'],
                                            $carac['filotaxia_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($carac['tipo_folha'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Tipo</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $carac['tipo_folha'],
                                            $carac['tipo_folha_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($carac['margem_folha'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Margem</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $carac['margem_folha'],
                                            $carac['margem_folha_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($carac['textura_folha'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Textura</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $carac['textura_folha'],
                                            $carac['textura_folha_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($carac['tamanho_folha'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Tamanho</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $carac['tamanho_folha'],
                                            $carac['tamanho_folha_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- FLOR -->
                        <?php 
                        $tem_flor = !empty($carac['cor_flor']) || 
                                    !empty($carac['simetria_flor']) || 
                                    !empty($carac['numero_petalas']) ||
                                    !empty($carac['aroma_flor']) ||
                                    !empty($carac['disposicao_flor']) ||
                                    !empty($carac['tamanho_flor']);
                        
                        if ($tem_flor): 
                        ?>
                        <div class="section">
                            <div class="section-title">🌸 Flor</div>
                            <div class="char-grid">
                                <?php if (!empty($carac['cor_flor'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Cor</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $carac['cor_flor'],
                                            $carac['cor_flor_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($carac['aroma_flor'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Aroma</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $carac['aroma_flor'],
                                            $carac['aroma_flor_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($carac['numero_petalas'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Nº pétalas</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $carac['numero_petalas'],
                                            $carac['numero_petalas_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($carac['simetria_flor'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Simetria</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $carac['simetria_flor'],
                                            $carac['simetria_flor_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- FRUTO -->
                        <?php 
                        $tem_fruto = !empty($carac['tipo_fruto']) || 
                                     !empty($carac['dispersao_fruto']) ||
                                     !empty($carac['cor_fruto']) ||
                                     !empty($carac['tamanho_fruto']) ||
                                     !empty($carac['textura_fruto']);
                        
                        if ($tem_fruto): 
                        ?>
                        <div class="section">
                            <div class="section-title">🍎 Fruto</div>
                            <div class="char-grid">
                                <?php if (!empty($carac['tipo_fruto'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Tipo</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $carac['tipo_fruto'],
                                            $carac['tipo_fruto_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($carac['dispersao_fruto'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Dispersão</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $carac['dispersao_fruto'],
                                            $carac['dispersao_fruto_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($carac['cor_fruto'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Cor</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $carac['cor_fruto'],
                                            $carac['cor_fruto_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- SEMENTE -->
                        <?php 
                        $tem_semente = !empty($carac['tipo_semente']) || 
                                       !empty($carac['cor_semente']) ||
                                       !empty($carac['tamanho_semente']);
                        
                        if ($tem_semente): 
                        ?>
                        <div class="section">
                            <div class="section-title">🌱 Semente</div>
                            <div class="char-grid">
                                <?php if (!empty($carac['tipo_semente'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Tipo</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $carac['tipo_semente'],
                                            $carac['tipo_semente_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($carac['cor_semente'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Cor</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $carac['cor_semente'],
                                            $carac['cor_semente_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- CAULE -->
                        <?php 
                        $tem_caule = !empty($carac['tipo_caule']) || 
                                     !empty($carac['textura_caule']) ||
                                     !empty($carac['cor_caule']);
                        
                        if ($tem_caule): 
                        ?>
                        <div class="section">
                            <div class="section-title">🌿 Caule</div>
                            <div class="char-grid">
                                <?php if (!empty($carac['tipo_caule'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Tipo</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $carac['tipo_caule'],
                                            $carac['tipo_caule_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($carac['textura_caule'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Textura</div>
                                    <div class="char-value">
                                        <?php echo exibirCaracteristica(
                                            $carac['textura_caule'],
                                            $carac['textura_caule_ref'] ?? ''
                                        ); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- OUTRAS CARACTERÍSTICAS -->
                        <?php 
                        $tem_outras = !empty($carac['possui_espinhos']) || 
                                      !empty($carac['possui_latex']) ||
                                      !empty($carac['possui_resina']);
                        
                        if ($tem_outras): 
                        ?>
                        <div class="section">
                            <div class="section-title">🔍 Outras Características</div>
                            <div class="char-grid">
                                <?php if (!empty($carac['possui_espinhos'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Espinhos</div>
                                    <div class="char-value">
                                        <?php echo $carac['possui_espinhos']; ?>
                                        <?php if (!empty($carac['possui_espinhos_ref'])): ?>
                                            <span class='ref-link' data-ref='<?php echo $carac['possui_espinhos_ref']; ?>'>[<?php echo $carac['possui_espinhos_ref']; ?>]</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($carac['possui_latex'])): ?>
                                <div class="char-item">
                                    <div class="char-label">Látex</div>
                                    <div class="char-value">
                                        <?php echo $carac['possui_latex']; ?>
                                        <?php if (!empty($carac['possui_latex_ref'])): ?>
                                            <span class='ref-link' data-ref='<?php echo $carac['possui_latex_ref']; ?>'>[<?php echo $carac['possui_latex_ref']; ?>]</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

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
                    
                    <?php foreach ($partes as $parte): ?>
                        <div class="section">
                            <div class="section-title"><?php echo $icones[$parte] . ' ' . $nomes_partes[$parte]; ?></div>
                            
                            <?php if (count($imagens[$parte]) > 0): ?>
                                <div class="imagens-grid">
                                    <?php foreach ($imagens[$parte] as $img): ?>
                                    <div class="imagem-card">
                                        <div class="imagem-preview" onclick="abrirImagem('<?php echo '/penomato_mvp/' . $img['caminho_imagem']; ?>')">
                                            <?php 
                                            $caminho_completo = $_SERVER['DOCUMENT_ROOT'] . '/penomato_mvp/' . $img['caminho_imagem'];
                                            if (file_exists($caminho_completo)): 
                                            ?>
                                                <img src="<?php echo '/penomato_mvp/' . $img['caminho_imagem']; ?>" alt="Imagem">
                                            <?php else: ?>
                                                <span>🖼️</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="imagem-info">
                                            <div class="imagem-descricao"><?php echo htmlspecialchars($img['descricao'] ?? 'Sem descrição'); ?></div>
                                            <div style="color: var(--cinza-500); margin-top: 4px;">
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

        <?php if (!empty($_GET['erro'])): ?>
        <div style="background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:12px 18px;border-radius:8px;margin-bottom:16px;">
            ⚠️ <?php echo htmlspecialchars($_GET['erro']); ?>
        </div>
        <?php endif; ?>

        <!-- DECISÃO DA REVISÃO -->
        <div class="decision-box">
            <div class="decision-title">⚖️ DECISÃO DA REVISÃO</div>
            
            <form id="formDecisao" method="POST" action="<?php echo APP_BASE; ?>/src/Controllers/controlador_painel_revisor.php">
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
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='/penomato_mvp/src/Controllers/controlador_painel_revisor.php'">
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
                return confirm('Confirmar rejeição? O motivo será registrado.');
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