<?php
// ================================================
// INTERFACE DE ENVIO DE IMAGENS - IDENTIFICADOR
// VERSÃO CORRIGIDA
// ================================================

session_start();
ob_start();

// Configurações do banco
$servidor = "127.0.0.1";
$usuario = "root";
$senha = "";
$banco = "penomato";

// Conectar ao banco
$conexao = mysqli_connect($servidor, $usuario, $senha, $banco);

if (!$conexao) {
    die("Erro de conexão: " . mysqli_connect_error());
}

mysqli_set_charset($conexao, "utf8mb4");

// ================================================
// USUÁRIO LOGADO (SIMULAÇÃO - VIRÁ DA SESSÃO)
// ================================================
$id_usuario_logado = 1;
$nome_usuario_logado = "João Silva";

// ================================================
// FUNÇÕES
// ================================================

function getEspeciesPorStatus($conexao, $status) {
    $sql = "SELECT id, nome_cientifico, status_imagens, prioridade
            FROM especies_administrativo 
            WHERE status_imagens = ?
            ORDER BY 
                CASE 
                    WHEN prioridade = 'alta' THEN 1
                    WHEN prioridade = 'media' THEN 2
                    WHEN prioridade = 'baixa' THEN 3
                    ELSE 4
                END ASC,
                nome_cientifico ASC
            LIMIT 50";
    
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "s", $status);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    $especies = [];
    while ($linha = mysqli_fetch_assoc($resultado)) {
        $especies[] = $linha;
    }
    mysqli_stmt_close($stmt);
    return $especies;
}

function getTodasEspecies($conexao) {
    $sql = "SELECT id, nome_cientifico, status_imagens, prioridade
            FROM especies_administrativo 
            ORDER BY 
                CASE 
                    WHEN status_imagens = 'sem_imagens' THEN 1
                    WHEN status_imagens = 'parcial' THEN 2
                    WHEN status_imagens = 'completo' THEN 3
                    ELSE 4
                END ASC,
                nome_cientifico ASC";
    
    $resultado = mysqli_query($conexao, $sql);
    
    $especies = [];
    while ($linha = mysqli_fetch_assoc($resultado)) {
        $especies[] = $linha;
    }
    mysqli_free_result($resultado);
    return $especies;
}

function getResumoImagensPorEspecie($conexao, $especie_id) {
    // CORRIGIDO: Adicionado 'habito' e removido 'exsicata' (ajuste conforme sua tabela)
    $partes = ['folha', 'flor', 'fruto', 'caule', 'semente', 'habito'];
    $resumo = [];
    
    foreach ($partes as $parte) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status_validacao = 'validado' THEN 1 ELSE 0 END) as validados,
                    SUM(CASE WHEN status_validacao = 'pendente' THEN 1 ELSE 0 END) as pendentes,
                    SUM(CASE WHEN status_validacao = 'rejeitado' THEN 1 ELSE 0 END) as rejeitados
                FROM imagens_especies 
                WHERE especie_id = ? AND parte = ?";
        
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "is", $especie_id, $parte);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $dados = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);
        
        $resumo[$parte] = [
            'total' => $dados['total'] ?? 0,
            'validados' => $dados['validados'] ?? 0,
            'pendentes' => $dados['pendentes'] ?? 0,
            'rejeitados' => $dados['rejeitados'] ?? 0
        ];
    }
    
    return $resumo;
}

function getEspeciePorId($conexao, $especie_id) {
    $sql = "SELECT id, nome_cientifico, status_imagens, status_caracteristicas, prioridade
            FROM especies_administrativo 
            WHERE id = ?";
    
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $especie_id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $especie = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);
    
    return $especie;
}

function getMinhasContribuicoes($conexao, $id_usuario) {
    $sql = "SELECT 
                i.id,
                i.especie_id,
                i.parte,
                i.status_validacao,
                i.data_upload,
                i.descricao,
                e.nome_cientifico
            FROM imagens_especies i
            INNER JOIN especies_administrativo e ON i.especie_id = e.id
            WHERE i.id_usuario_identificador = ?
            ORDER BY i.data_upload DESC
            LIMIT 20";
    
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_usuario);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    $contribuicoes = [];
    while ($linha = mysqli_fetch_assoc($resultado)) {
        $contribuicoes[] = $linha;
    }
    mysqli_stmt_close($stmt);
    return $contribuicoes;
}

// ================================================
// PROCESSAR PARÂMETROS
// ================================================

$aba_ativa = isset($_GET['aba']) ? $_GET['aba'] : 'prioritarias';
$especie_id_selecionada = isset($_GET['especie_id']) ? (int)$_GET['especie_id'] : 0;

// Limpar seleção
if (isset($_GET['limpar'])) {
    $especie_id_selecionada = 0;
    header("Location: upload_imagens.php");
    exit;
}

// ================================================
// BUSCAR DADOS
// ================================================

$todas_especies = getTodasEspecies($conexao);
$especies_sem_imagens = getEspeciesPorStatus($conexao, 'sem_imagens');
$especies_parciais = getEspeciesPorStatus($conexao, 'parcial');
$especies_completas = getEspeciesPorStatus($conexao, 'completo');

$especie_selecionada = null;
$resumo_imagens = [];

if ($especie_id_selecionada > 0) {
    $especie_selecionada = getEspeciePorId($conexao, $especie_id_selecionada);
    if ($especie_selecionada) {
        $resumo_imagens = getResumoImagensPorEspecie($conexao, $especie_id_selecionada);
    }
}

$minhas_contribuicoes = getMinhasContribuicoes($conexao, $id_usuario_logado);

mysqli_close($conexao);
ob_end_clean();

// ================================================
// MENSAGENS DE FEEDBACK (da sessão)
// ================================================
$mensagem_sucesso = $_SESSION['mensagem_sucesso'] ?? null;
$mensagem_erro = $_SESSION['mensagem_erro'] ?? null;
unset($_SESSION['mensagem_sucesso'], $_SESSION['mensagem_erro']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penomato - Envio de Exsicatas</title>
    <style>
        /* ========== RESET E VARIÁVEIS ========== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        :root {
            --verde-penomato: #0b5e42;
            --verde-claro: #e8f5e9;
            --verde-hover: #0a4c35;
            --cinza-fundo: #f8fafc;
            --cinza-borda: #e2e8f0;
            --cinza-texto: #4b5563;
            --vermelho: #dc2626;
            --vermelho-claro: #fef2f2;
            --amarelo: #f59e0b;
            --amarelo-claro: #fffbeb;
            --azul: #3b82f6;
            --azul-claro: #eff6ff;
            --verde-sucesso: #10b981;
            --verde-sucesso-claro: #d1fae5;
        }

        body {
            background-color: var(--cinza-fundo);
            color: #1a2634;
            line-height: 1.6;
            padding: 30px 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        h1 {
            font-size: 2rem;
            font-weight: 600;
            color: var(--verde-penomato);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        h2 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ========== MENSAGENS ========== */
        .mensagem-sucesso {
            background: var(--verde-sucesso-claro);
            color: #065f46;
            padding: 16px 24px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 6px solid var(--verde-sucesso);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .mensagem-erro {
            background: var(--vermelho-claro);
            color: var(--vermelho);
            padding: 16px 24px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 6px solid var(--vermelho);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* ========== HEADER ========== */
        .header {
            background: white;
            border-radius: 20px;
            padding: 20px 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .usuario-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .usuario-avatar {
            width: 48px;
            height: 48px;
            background: var(--verde-penomato);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            font-weight: 600;
        }

        /* ========== BADGES ========== */
        .badge-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .badge-sem_imagens { background: var(--vermelho-claro); color: var(--vermelho); border: 1px solid var(--vermelho); }
        .badge-parcial { background: var(--amarelo-claro); color: #92400e; border: 1px solid var(--amarelo); }
        .badge-completo { background: var(--verde-sucesso-claro); color: #065f46; border: 1px solid var(--verde-sucesso); }
        .badge-pendente { background: #fef3c7; color: #92400e; }
        .badge-validado { background: var(--verde-sucesso-claro); color: #065f46; }
        .badge-rejeitado { background: var(--vermelho-claro); color: var(--vermelho); }

        /* ========== LAYOUT ========== */
        .grid-dashboard {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            margin-bottom: 25px;
            border: 1px solid var(--cinza-borda);
        }

        /* ========== SELECT DE ESPÉCIES ========== */
        .selecao-especie {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            border-left: 8px solid var(--verde-penomato);
        }

        .select-grande {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid var(--cinza-borda);
            border-radius: 12px;
            font-size: 1rem;
            background: white;
            margin: 15px 0;
            cursor: pointer;
        }

        .select-grande:focus {
            border-color: var(--verde-penomato);
            outline: none;
            box-shadow: 0 0 0 3px rgba(11,94,66,0.1);
        }

        /* ========== CARDS DE PARTES ========== */
        .partes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .parte-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid var(--cinza-borda);
            transition: all 0.2s;
        }

        .parte-card:hover {
            border-color: var(--verde-penomato);
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }

        .parte-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--cinza-borda);
        }

        .parte-icone {
            font-size: 1.8rem;
        }

        .parte-nome {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1e293b;
        }

        .parte-stats {
            display: flex;
            gap: 12px;
            margin-bottom: 15px;
            font-size: 0.9rem;
            flex-wrap: wrap;
        }

        .preview-area {
            width: 100%;
            height: 100px;
            background: var(--cinza-fundo);
            border-radius: 12px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--cinza-texto);
            border: 1px solid var(--cinza-borda);
        }

        /* ========== ÁREA DE UPLOAD ========== */
        .upload-area {
            background: var(--cinza-fundo);
            border: 3px dashed var(--cinza-borda);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            transition: all 0.2s;
            cursor: pointer;
            margin-top: 10px;
        }

        .upload-area:hover {
            border-color: var(--verde-penomato);
            background: #f0fdf4;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 40px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
        }

        .btn-primary {
            background: var(--verde-penomato);
            color: white;
        }

        .btn-primary:hover {
            background: var(--verde-hover);
            transform: translateY(-2px);
        }

        .btn-outline {
            background: white;
            border: 2px solid var(--cinza-borda);
            color: #1e293b;
        }

        /* ========== CONTRIBUIÇÕES ========== */
        .contribuicao-item {
            display: flex;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid var(--cinza-borda);
        }

        .contribuicao-item:last-child {
            border-bottom: none;
        }

        .contribuicao-icone {
            font-size: 1.5rem;
        }

        .contribuicao-conteudo {
            flex: 1;
        }

        .contribuicao-titulo {
            font-weight: 600;
            color: #1e293b;
        }

        /* ========== FORMULÁRIO ========== */
        .campo {
            margin-bottom: 12px;
        }

        .campo label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 4px;
        }

        .campo input, .campo textarea {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid var(--cinza-borda);
            border-radius: 10px;
            font-size: 0.95rem;
        }

        .campo input:focus, .campo textarea:focus {
            border-color: var(--verde-penomato);
            outline: none;
        }

        .especie-header {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 8px solid var(--verde-penomato);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .especie-titulo {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--verde-penomato);
        }

        .empty-state {
            background: white;
            border-radius: 20px;
            padding: 60px;
            text-align: center;
            border: 2px dashed var(--cinza-borda);
        }

        .stats-grid {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
            min-width: 120px;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- ========== HEADER ========== -->
        <div class="header">
            <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                <h1>📸 ENVIO DE EXSICATAS</h1>
                <?php if ($especie_selecionada): ?>
                <span class="badge-status badge-<?php echo $especie_selecionada['status_imagens']; ?>">
                    <?php echo strtoupper($especie_selecionada['status_imagens']); ?>
                </span>
                <?php endif; ?>
            </div>
            <div class="usuario-info">
                <div class="usuario-avatar"><?php echo substr($nome_usuario_logado, 0, 2); ?></div>
                <div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($nome_usuario_logado); ?></div>
                    <div style="font-size: 0.85rem; color: var(--cinza-texto);">Identificador</div>
                </div>
            </div>
        </div>

        <!-- ========== MENSAGENS DE FEEDBACK ========== -->
        <?php if ($mensagem_sucesso): ?>
        <div class="mensagem-sucesso">
            <span style="font-size: 1.5rem;">✅</span>
            <div><?php echo htmlspecialchars($mensagem_sucesso); ?></div>
        </div>
        <?php endif; ?>

        <?php if ($mensagem_erro): ?>
        <div class="mensagem-erro">
            <span style="font-size: 1.5rem;">❌</span>
            <div><?php echo htmlspecialchars($mensagem_erro); ?></div>
        </div>
        <?php endif; ?>

        <!-- ========== SELETOR DE ESPÉCIE ========== -->
        <div class="selecao-especie">
            <h2>🔍 1. SELECIONE UMA ESPÉCIE</h2>
            
            <form method="GET" action="">
                <select name="especie_id" class="select-grande" onchange="this.form.submit()">
                    <option value="">-- Selecione uma espécie para enviar imagens --</option>
                    
                    <?php if (count($especies_sem_imagens) > 0): ?>
                    <optgroup label="🔴 PRIORITÁRIAS (sem imagens)">
                        <?php foreach ($especies_sem_imagens as $especie): ?>
                        <option value="<?php echo $especie['id']; ?>" <?php echo $especie_id_selecionada == $especie['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($especie['nome_cientifico']); ?>
                            <?php if (!empty($especie['prioridade'])): ?>[<?php echo strtoupper($especie['prioridade']); ?>]<?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endif; ?>
                    
                    <?php if (count($especies_parciais) > 0): ?>
                    <optgroup label="🟡 EM ANDAMENTO (parcial)">
                        <?php foreach ($especies_parciais as $especie): ?>
                        <option value="<?php echo $especie['id']; ?>" <?php echo $especie_id_selecionada == $especie['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($especie['nome_cientifico']); ?>
                        </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endif; ?>
                    
                    <?php if (count($especies_completas) > 0): ?>
                    <optgroup label="✅ COMPLETAS">
                        <?php foreach ($especies_completas as $especie): ?>
                        <option value="<?php echo $especie['id']; ?>" <?php echo $especie_id_selecionada == $especie['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($especie['nome_cientifico']); ?>
                        </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endif; ?>
                </select>
            </form>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; flex-wrap: wrap; gap: 15px;">
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div><span style="color: var(--vermelho); font-weight: 600;">🔴</span> Sem imagens: <strong><?php echo count($especies_sem_imagens); ?></strong></div>
                    <div><span style="color: #92400e; font-weight: 600;">🟡</span> Parcial: <strong><?php echo count($especies_parciais); ?></strong></div>
                    <div><span style="color: #065f46; font-weight: 600;">✅</span> Completo: <strong><?php echo count($especies_completas); ?></strong></div>
                </div>
                <a href="?limpar=1" style="color: var(--cinza-texto); text-decoration: none; padding: 6px 16px; border: 1px solid var(--cinza-borda); border-radius: 30px; font-size: 0.9rem;">
                    🧹 Limpar seleção
                </a>
            </div>
        </div>

        <!-- ========== SE ESPÉCIE SELECIONADA ========== -->
        <?php if ($especie_selecionada): ?>
        
        <!-- ========== CABEÇALHO DA ESPÉCIE ========== -->
        <div class="especie-header">
            <div>
                <div class="especie-titulo"><?php echo htmlspecialchars($especie_selecionada['nome_cientifico']); ?></div>
                <div style="margin-top: 12px; color: var(--cinza-texto); display: flex; gap: 20px; flex-wrap: wrap;">
                    <span>📌 ID: <?php echo $especie_selecionada['id']; ?></span>
                    <?php if ($especie_selecionada['prioridade']): ?>
                    <span>🎯 Prioridade: <span style="font-weight: 600; color: var(--verde-penomato);"><?php echo strtoupper($especie_selecionada['prioridade']); ?></span></span>
                    <?php endif; ?>
                    <span>📊 Status: <?php echo strtoupper($especie_selecionada['status_imagens'] ?? 'SEM IMAGENS'); ?></span>
                </div>
            </div>
            <div>
                <span class="badge-status badge-<?php echo $especie_selecionada['status_imagens']; ?>" style="font-size: 0.9rem; padding: 8px 20px;">
                    <?php echo strtoupper($especie_selecionada['status_imagens']); ?>
                </span>
            </div>
        </div>

        <!-- ========== GRID PRINCIPAL ========== -->
        <div class="grid-dashboard">
            <!-- COLUNA 1: FORMULÁRIOS DE UPLOAD -->
            <div>
                <h2>📸 2. ESCOLHA A PARTE E ENVIE AS IMAGENS</h2>
                
                <div class="partes-grid">
                    <!-- FOLHA -->
                    <div class="parte-card">
                        <div class="parte-header">
                            <span class="parte-icone">🍃</span>
                            <span class="parte-nome">Folha</span>
                        </div>
                        <?php $folha = $resumo_imagens['folha'] ?? ['validados' => 0, 'pendentes' => 0, 'rejeitados' => 0]; ?>
                        <div class="parte-stats">
                            <span>✅ <strong><?php echo $folha['validados']; ?></strong> validados</span>
                            <span>⏳ <strong><?php echo $folha['pendentes']; ?></strong> pendentes</span>
                            <?php if ($folha['rejeitados'] > 0): ?>
                            <span style="color: var(--vermelho);">❌ <strong><?php echo $folha['rejeitados']; ?></strong> rejeitados</span>
                            <?php endif; ?>
                        </div>
                        <div class="preview-area">
                            <?php if ($folha['validados'] > 0): ?>
                            <span style="color: var(--verde-penomato); font-weight: 500;">✅ Imagem validada</span>
                            <?php else: ?>
                            <span style="color: var(--cinza-texto);">📸 Nenhuma imagem validada</span>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data" action="../Controllers/upload_imagem.php">
                            <input type="hidden" name="especie_id" value="<?php echo $especie_selecionada['id']; ?>">
                            <input type="hidden" name="parte" value="folha">
                            <input type="hidden" name="id_usuario_identificador" value="<?php echo $id_usuario_logado; ?>">
                            
                            <div class="upload-area" onclick="document.getElementById('file_folha').click();">
                                <span style="font-size: 1.5rem;">📂</span>
                                <p style="font-weight: 500; margin-top: 5px;">Clique para selecionar</p>
                                <p style="font-size: 0.75rem; color: var(--cinza-texto);">JPG, PNG · Máx 10MB</p>
                                <input type="file" id="file_folha" name="imagem" accept="image/*" style="display: none;" required>
                            </div>
                            
                            <div class="campo" style="margin-top: 10px;">
                                <label>Legenda descritiva *</label>
                                <input type="text" name="descricao" placeholder="Ex: Face adaxial da folha" required>
                            </div>
                            
                            <div class="campo">
                                <label>Local da coleta</label>
                                <input type="text" name="localizacao" placeholder="Ex: São Francisco de Paula, RS">
                            </div>
                            
                            <div class="campo">
                                <label>Data da coleta</label>
                                <input type="date" name="data_coleta">
                            </div>
                            
                            <div class="campo">
                                <label>Observações</label>
                                <textarea name="observacoes" rows="2" placeholder="Informações adicionais sobre a imagem"></textarea>
                            </div>
                            
                            <button type="submit" name="acao_upload" class="btn btn-primary" style="margin-top: 10px;">
                                <?php echo $folha['validados'] > 0 ? '➕ ADICIONAR MAIS' : '📤 ENVIAR PRIMEIRA IMAGEM'; ?>
                            </button>
                        </form>
                    </div>
                    
                    <!-- FLOR -->
                    <div class="parte-card">
                        <div class="parte-header">
                            <span class="parte-icone">🌸</span>
                            <span class="parte-nome">Flor</span>
                        </div>
                        <?php $flor = $resumo_imagens['flor'] ?? ['validados' => 0, 'pendentes' => 0, 'rejeitados' => 0]; ?>
                        <div class="parte-stats">
                            <span>✅ <strong><?php echo $flor['validados']; ?></strong> validados</span>
                            <span>⏳ <strong><?php echo $flor['pendentes']; ?></strong> pendentes</span>
                        </div>
                        <div class="preview-area">
                            <?php if ($flor['validados'] > 0): ?>
                            <span style="color: var(--verde-penomato);">✅ Imagem validada</span>
                            <?php else: ?>
                            <span style="color: var(--cinza-texto);">📸 Nenhuma imagem</span>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data" action="../Controllers/upload_imagem.php">
                            <input type="hidden" name="especie_id" value="<?php echo $especie_selecionada['id']; ?>">
                            <input type="hidden" name="parte" value="flor">
                            <input type="hidden" name="id_usuario_identificador" value="<?php echo $id_usuario_logado; ?>">
                            
                            <div class="upload-area" onclick="document.getElementById('file_flor').click();">
                                <span style="font-size: 1.5rem;">📂</span>
                                <p style="font-weight: 500;">Selecionar arquivo</p>
                                <input type="file" id="file_flor" name="imagem" accept="image/*" style="display: none;" required>
                            </div>
                            
                            <div class="campo" style="margin-top: 10px;">
                                <label>Legenda descritiva *</label>
                                <input type="text" name="descricao" placeholder="Ex: Vista frontal da flor" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">
                                <?php echo $flor['validados'] > 0 ? '➕ ADICIONAR MAIS' : '📤 ENVIAR'; ?>
                            </button>
                        </form>
                    </div>
                    
                    <!-- FRUTO -->
                    <div class="parte-card">
                        <div class="parte-header">
                            <span class="parte-icone">🍎</span>
                            <span class="parte-nome">Fruto</span>
                        </div>
                        <?php $fruto = $resumo_imagens['fruto'] ?? ['validados' => 0, 'pendentes' => 0, 'rejeitados' => 0]; ?>
                        <div class="parte-stats">
                            <span>✅ <strong><?php echo $fruto['validados']; ?></strong> validados</span>
                            <span>⏳ <strong><?php echo $fruto['pendentes']; ?></strong> pendentes</span>
                        </div>
                        <div class="preview-area">
                            <?php if ($fruto['validados'] > 0): ?>
                            <span style="color: var(--verde-penomato);">✅ Imagem validada</span>
                            <?php else: ?>
                            <span style="color: var(--cinza-texto);">📸 Nenhuma imagem</span>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data" action="../Controllers/upload_imagem.php">
                            <input type="hidden" name="especie_id" value="<?php echo $especie_selecionada['id']; ?>">
                            <input type="hidden" name="parte" value="fruto">
                            <input type="hidden" name="id_usuario_identificador" value="<?php echo $id_usuario_logado; ?>">
                            
                            <div class="upload-area" onclick="document.getElementById('file_fruto').click();">
                                <span style="font-size: 1.5rem;">📂</span>
                                <p style="font-weight: 500;">Selecionar arquivo</p>
                                <input type="file" id="file_fruto" name="imagem" accept="image/*" style="display: none;" required>
                            </div>
                            
                            <div class="campo" style="margin-top: 10px;">
                                <label>Legenda descritiva *</label>
                                <input type="text" name="descricao" placeholder="Ex: Fruto inteiro" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">
                                <?php echo $fruto['validados'] > 0 ? '➕ ADICIONAR MAIS' : '📤 ENVIAR'; ?>
                            </button>
                        </form>
                    </div>
                    
                    <!-- CAULE -->
                    <div class="parte-card">
                        <div class="parte-header">
                            <span class="parte-icone">🌿</span>
                            <span class="parte-nome">Caule</span>
                        </div>
                        <?php $caule = $resumo_imagens['caule'] ?? ['validados' => 0, 'pendentes' => 0, 'rejeitados' => 0]; ?>
                        <div class="parte-stats">
                            <span>✅ <strong><?php echo $caule['validados']; ?></strong> validados</span>
                            <?php if ($caule['pendentes'] > 0): ?>
                            <span>⏳ <strong><?php echo $caule['pendentes']; ?></strong> pendentes</span>
                            <?php endif; ?>
                        </div>
                        <div class="preview-area">
                            <?php if ($caule['validados'] > 0): ?>
                            <span style="color: var(--verde-penomato);">✅ Imagem validada</span>
                            <?php else: ?>
                            <span style="color: var(--cinza-texto);">📸 Nenhuma imagem</span>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data" action="../Controllers/upload_imagem.php">
                            <input type="hidden" name="especie_id" value="<?php echo $especie_selecionada['id']; ?>">
                            <input type="hidden" name="parte" value="caule">
                            <input type="hidden" name="id_usuario_identificador" value="<?php echo $id_usuario_logado; ?>">
                            
                            <div class="upload-area" onclick="document.getElementById('file_caule').click();">
                                <span style="font-size: 1.5rem;">📂</span>
                                <p style="font-weight: 500;">Selecionar arquivo</p>
                                <input type="file" id="file_caule" name="imagem" accept="image/*" style="display: none;" required>
                            </div>
                            
                            <div class="campo" style="margin-top: 10px;">
                                <label>Legenda descritiva *</label>
                                <input type="text" name="descricao" placeholder="Ex: Detalhe da casca" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">
                                <?php echo $caule['validados'] > 0 ? '➕ ADICIONAR MAIS' : '📤 ENVIAR'; ?>
                            </button>
                        </form>
                    </div>
                    
                    <!-- SEMENTE -->
                    <div class="parte-card">
                        <div class="parte-header">
                            <span class="parte-icone">🌱</span>
                            <span class="parte-nome">Semente</span>
                        </div>
                        <?php $semente = $resumo_imagens['semente'] ?? ['validados' => 0, 'pendentes' => 0, 'rejeitados' => 0]; ?>
                        <div class="parte-stats">
                            <span>✅ <strong><?php echo $semente['validados']; ?></strong> validados</span>
                        </div>
                        <div class="preview-area">
                            <?php if ($semente['validados'] > 0): ?>
                            <span style="color: var(--verde-penomato);">✅ Imagem validada</span>
                            <?php else: ?>
                            <span style="color: var(--cinza-texto);">📸 Nenhuma imagem</span>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data" action="../Controllers/upload_imagem.php">
                            <input type="hidden" name="especie_id" value="<?php echo $especie_selecionada['id']; ?>">
                            <input type="hidden" name="parte" value="semente">
                            <input type="hidden" name="id_usuario_identificador" value="<?php echo $id_usuario_logado; ?>">
                            
                            <div class="upload-area" onclick="document.getElementById('file_semente').click();">
                                <span style="font-size: 1.5rem;">📂</span>
                                <p style="font-weight: 500;">Selecionar arquivo</p>
                                <input type="file" id="file_semente" name="imagem" accept="image/*" style="display: none;" required>
                            </div>
                            
                            <div class="campo" style="margin-top: 10px;">
                                <label>Legenda descritiva *</label>
                                <input type="text" name="descricao" placeholder="Ex: Sementes" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">
                                <?php echo $semente['validados'] > 0 ? '➕ ADICIONAR MAIS' : '📤 ENVIAR'; ?>
                            </button>
                        </form>
                    </div>
                    
                    <!-- HÁBITO (ADICIONADO - ESTAVA FALTANDO) -->
                    <div class="parte-card">
                        <div class="parte-header">
                            <span class="parte-icone">🌳</span>
                            <span class="parte-nome">Hábito</span>
                        </div>
                        <?php $habito = $resumo_imagens['habito'] ?? ['validados' => 0, 'pendentes' => 0, 'rejeitados' => 0]; ?>
                        <div class="parte-stats">
                            <span>✅ <strong><?php echo $habito['validados']; ?></strong> validados</span>
                        </div>
                        <div class="preview-area">
                            <?php if ($habito['validados'] > 0): ?>
                            <span style="color: var(--verde-penomato);">✅ Imagem validada</span>
                            <?php else: ?>
                            <span style="color: var(--cinza-texto);">📸 Nenhuma imagem</span>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data" action="../Controllers/upload_imagem.php">
                            <input type="hidden" name="especie_id" value="<?php echo $especie_selecionada['id']; ?>">
                            <input type="hidden" name="parte" value="habito">
                            <input type="hidden" name="id_usuario_identificador" value="<?php echo $id_usuario_logado; ?>">
                            
                            <div class="upload-area" onclick="document.getElementById('file_habito').click();">
                                <span style="font-size: 1.5rem;">📂</span>
                                <p style="font-weight: 500;">Selecionar arquivo</p>
                                <input type="file" id="file_habito" name="imagem" accept="image/*" style="display: none;" required>
                            </div>
                            
                            <div class="campo" style="margin-top: 10px;">
                                <label>Legenda descritiva *</label>
                                <input type="text" name="descricao" placeholder="Ex: Planta inteira" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary" style="margin-top: 10px;">
                                <?php echo $habito['validados'] > 0 ? '➕ ADICIONAR MAIS' : '📤 ENVIAR'; ?>
                            </button>
                        </form>
                    </div>
                </div> <!-- FIM partes-grid -->
            </div> <!-- FIM COLUNA 1 -->
            
            <!-- COLUNA 2: MINHAS CONTRIBUIÇÕES E PROGRESSO -->
            <div>
                <!-- MINHAS CONTRIBUIÇÕES -->
                <div class="card">
                    <h2>📋 MINHAS CONTRIBUIÇÕES</h2>
                    <p style="font-size: 0.9rem; color: var(--cinza-texto); margin-bottom: 20px;">
                        Últimas imagens que você enviou
                    </p>
                    
                    <?php if (count($minhas_contribuicoes) > 0): ?>
                        <?php foreach ($minhas_contribuicoes as $contrib): ?>
                        <div class="contribuicao-item">
                            <div class="contribuicao-icone">
                                <?php 
                                    $icones = [
                                        'folha' => '🍃', 
                                        'flor' => '🌸', 
                                        'fruto' => '🍎', 
                                        'caule' => '🌿', 
                                        'semente' => '🌱',
                                        'habito' => '🌳'
                                    ];
                                    echo $icones[$contrib['parte']] ?? '📸';
                                ?>
                            </div>
                            <div class="contribuicao-conteudo">
                                <div class="contribuicao-titulo">
                                    <?php echo htmlspecialchars($contrib['nome_cientifico']); ?>
                                </div>
                                <div style="display: flex; gap: 8px; align-items: center; margin-top: 5px; flex-wrap: wrap;">
                                    <span class="badge-status badge-<?php echo $contrib['status_validacao']; ?>">
                                        <?php echo $contrib['status_validacao']; ?>
                                    </span>
                                    <span style="font-size: 0.8rem; color: var(--cinza-texto);">
                                        <?php echo date('d/m/Y', strtotime($contrib['data_upload'])); ?>
                                    </span>
                                </div>
                                <?php if ($contrib['descricao']): ?>
                                <div style="font-size: 0.8rem; color: var(--cinza-texto); margin-top: 5px;">
                                    📝 <?php echo htmlspecialchars($contrib['descricao']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 30px; background: var(--cinza-fundo); border-radius: 12px;">
                            <span style="font-size: 2rem;">📸</span>
                            <p style="margin-top: 10px; color: var(--cinza-texto);">
                                Você ainda não enviou nenhuma imagem.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- PROGRESSO DA ESPÉCIE -->
                <div class="card">
                    <h2>📊 PROGRESSO DA ESPÉCIE</h2>
                    <div style="display: flex; flex-direction: column; gap: 20px;">
                        <!-- FOLHA -->
                        <div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span style="font-weight: 600;">🍃 Folha</span>
                                <span style="color: var(--verde-penomato); font-weight: 600;">
                                    <?php echo $resumo_imagens['folha']['validados'] ?? 0; ?> validados
                                </span>
                            </div>
                            <div style="height: 8px; background: var(--cinza-borda); border-radius: 4px; overflow: hidden;">
                                <div style="height: 100%; width: <?php echo min(100, ($resumo_imagens['folha']['validados'] ?? 0) * 20); ?>%; background: var(--verde-penomato);"></div>
                            </div>
                            <?php if (($resumo_imagens['folha']['pendentes'] ?? 0) > 0 || ($resumo_imagens['folha']['rejeitados'] ?? 0) > 0): ?>
                            <div style="display: flex; gap: 15px; margin-top: 6px; font-size: 0.8rem;">
                                <?php if (($resumo_imagens['folha']['pendentes'] ?? 0) > 0): ?>
                                <span style="color: #92400e;">⏳ <?php echo $resumo_imagens['folha']['pendentes']; ?> pendentes</span>
                                <?php endif; ?>
                                <?php if (($resumo_imagens['folha']['rejeitados'] ?? 0) > 0): ?>
                                <span style="color: var(--vermelho);">❌ <?php echo $resumo_imagens['folha']['rejeitados']; ?> rejeitados</span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- FLOR -->
                        <div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span style="font-weight: 600;">🌸 Flor</span>
                                <span style="color: var(--verde-penomato); font-weight: 600;">
                                    <?php echo $resumo_imagens['flor']['validados'] ?? 0; ?> validados
                                </span>
                            </div>
                            <div style="height: 8px; background: var(--cinza-borda); border-radius: 4px; overflow: hidden;">
                                <div style="height: 100%; width: <?php echo min(100, ($resumo_imagens['flor']['validados'] ?? 0) * 20); ?>%; background: var(--verde-penomato);"></div>
                            </div>
                        </div>
                        
                        <!-- FRUTO -->
                        <div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span style="font-weight: 600;">🍎 Fruto</span>
                                <span style="color: var(--verde-penomato); font-weight: 600;">
                                    <?php echo $resumo_imagens['fruto']['validados'] ?? 0; ?> validados
                                </span>
                            </div>
                            <div style="height: 8px; background: var(--cinza-borda); border-radius: 4px; overflow: hidden;">
                                <div style="height: 100%; width: <?php echo min(100, ($resumo_imagens['fruto']['validados'] ?? 0) * 20); ?>%; background: var(--verde-penomato);"></div>
                            </div>
                        </div>
                        
                        <!-- CAULE -->
                        <div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span style="font-weight: 600;">🌿 Caule</span>
                                <span style="color: var(--verde-penomato); font-weight: 600;">
                                    <?php echo $resumo_imagens['caule']['validados'] ?? 0; ?> validados
                                </span>
                            </div>
                            <div style="height: 8px; background: var(--cinza-borda); border-radius: 4px; overflow: hidden;">
                                <div style="height: 100%; width: <?php echo min(100, ($resumo_imagens['caule']['validados'] ?? 0) * 20); ?>%; background: var(--verde-penomato);"></div>
                            </div>
                        </div>
                        
                        <!-- SEMENTE -->
                        <div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span style="font-weight: 600;">🌱 Semente</span>
                                <span style="color: var(--verde-penomato); font-weight: 600;">
                                    <?php echo $resumo_imagens['semente']['validados'] ?? 0; ?> validados
                                </span>
                            </div>
                            <div style="height: 8px; background: var(--cinza-borda); border-radius: 4px; overflow: hidden;">
                                <div style="height: 100%; width: <?php echo min(100, ($resumo_imagens['semente']['validados'] ?? 0) * 20); ?>%; background: var(--verde-penomato);"></div>
                            </div>
                        </div>
                        
                        <!-- HÁBITO -->
                        <div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span style="font-weight: 600;">🌳 Hábito</span>
                                <span style="color: var(--verde-penomato); font-weight: 600;">
                                    <?php echo $resumo_imagens['habito']['validados'] ?? 0; ?> validados
                                </span>
                            </div>
                            <div style="height: 8px; background: var(--cinza-borda); border-radius: 4px; overflow: hidden;">
                                <div style="height: 100%; width: <?php echo min(100, ($resumo_imagens['habito']['validados'] ?? 0) * 20); ?>%; background: var(--verde-penomato);"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- STATUS PARA PUBLICAÇÃO -->
                    <div style="margin-top: 30px; padding: 20px; background: var(--verde-claro); border-radius: 12px;">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <span style="font-size: 1.8rem;">🏆</span>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; color: var(--verde-penomato); margin-bottom: 8px;">Status para publicação</div>
                                <div style="font-size: 0.95rem; color: var(--cinza-texto);">
                                    <?php 
                                    // CORRIGIDO: Cálculo correto do progresso
                                    $partes_obrigatorias = ['folha', 'flor', 'fruto', 'caule', 'habito'];
                                    $total_partes = count($partes_obrigatorias);
                                    $partes_validadas = 0;
                                    
                                    foreach ($partes_obrigatorias as $p) {
                                        if (($resumo_imagens[$p]['validados'] ?? 0) > 0) {
                                            $partes_validadas++;
                                        }
                                    }
                                    
                                    $percentual = round(($partes_validadas / $total_partes) * 100);
                                    ?>
                                    <strong><?php echo $partes_validadas; ?> de <?php echo $total_partes; ?> partes obrigatórias</strong> com imagem validada
                                </div>
                                <div style="height: 8px; background: var(--cinza-borda); border-radius: 4px; margin-top: 12px; overflow: hidden;">
                                    <div style="height: 100%; width: <?php echo $percentual; ?>%; background: var(--verde-penomato);"></div>
                                </div>
                                <div style="margin-top: 8px; font-size: 0.85rem; color: var(--cinza-texto);">
                                    Progresso: <?php echo $percentual; ?>%
                                </div>
                                <?php if ($percentual == 100): ?>
                                <div style="margin-top: 15px; background: var(--verde-sucesso-claro); color: #065f46; padding: 12px; border-radius: 8px; text-align: center; font-weight: 600;">
                                    ✅ Esta espécie está pronta para publicação!
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- FIM COLUNA 2 -->
        </div> <!-- FIM grid-dashboard -->
        
        <?php else: ?>
        
        <!-- ========== MENSAGEM QUANDO NENHUMA ESPÉCIE SELECIONADA ========== -->
        <div class="empty-state">
            <span style="font-size: 4rem;">📸</span>
            <h2 style="margin-top: 20px; color: var(--verde-penomato);">Nenhuma espécie selecionada</h2>
            <p style="margin-top: 10px; color: var(--cinza-texto); max-width: 500px; margin-left: auto; margin-right: auto;">
                Selecione uma espécie na lista acima para começar a enviar imagens. 
                Dê prioridade às espécies com status <span style="background: var(--vermelho-claro); color: var(--vermelho); padding: 4px 12px; border-radius: 30px; font-weight: 600;">SEM_IMAGENS</span>.
            </p>
            
            <div class="stats-grid">
                <div class="stat-item">
                    <div style="font-size: 2rem; margin-bottom: 10px;">🔴</div>
                    <div style="font-weight: 600;">Prioritárias</div>
                    <div style="font-size: 0.9rem; color: var(--cinza-texto);">Nunca enviaram</div>
                    <div class="stat-value" style="color: var(--vermelho);">
                        <?php echo count($especies_sem_imagens); ?>
                    </div>
                </div>
                <div class="stat-item">
                    <div style="font-size: 2rem; margin-bottom: 10px;">🟡</div>
                    <div style="font-weight: 600;">Em andamento</div>
                    <div style="font-size: 0.9rem; color: var(--cinza-texto);">Parcial</div>
                    <div class="stat-value" style="color: #92400e;">
                        <?php echo count($especies_parciais); ?>
                    </div>
                </div>
                <div class="stat-item">
                    <div style="font-size: 2rem; margin-bottom: 10px;">✅</div>
                    <div style="font-weight: 600;">Completas</div>
                    <div style="font-size: 0.9rem; color: var(--cinza-texto);">Acervo completo</div>
                    <div class="stat-value" style="color: #065f46;">
                        <?php echo count($especies_completas); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php endif; ?>
        
        <!-- ========== RODAPÉ ========== -->
        <div style="margin-top: 50px; text-align: center; color: var(--cinza-texto); font-size: 0.85rem; border-top: 1px solid var(--cinza-borda); padding-top: 30px;">
            <p>Penomato - Sistema de Documentação Botânica</p>
            <p style="margin-top: 5px;">Todas as imagens enviadas passam por validação de especialistas antes de serem publicadas.</p>
            <p style="margin-top: 5px;">📸 Créditos são mantidos integralmente aos fotógrafos.</p>
        </div>
        
    </div> <!-- FIM container -->
    
    <script>
        // Melhorar experiência de drag & drop
        document.querySelectorAll('.upload-area').forEach(area => {
            area.addEventListener('dragover', (e) => {
                e.preventDefault();
                area.style.borderColor = 'var(--verde-penomato)';
                area.style.background = '#f0fdf4';
            });
            
            area.addEventListener('dragleave', (e) => {
                e.preventDefault();
                area.style.borderColor = 'var(--cinza-borda)';
                area.style.background = 'var(--cinza-fundo)';
            });
            
            area.addEventListener('drop', (e) => {
                e.preventDefault();
                area.style.borderColor = 'var(--cinza-borda)';
                area.style.background = 'var(--cinza-fundo)';
                
                const input = area.querySelector('input[type="file"]');
                if (input && e.dataTransfer.files.length > 0) {
                    input.files = e.dataTransfer.files;
                    
                    const p = area.querySelector('p');
                    if (p) {
                        p.innerHTML = `📁 ${e.dataTransfer.files[0].name}`;
                    }
                }
            });
        });
        
        // Preview do nome do arquivo
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const area = this.closest('.upload-area');
                if (area && this.files.length > 0) {
                    const p = area.querySelector('p');
                    if (p) {
                        p.innerHTML = `📁 ${this.files[0].name}`;
                    }
                }
            });
        });
    </script>
</body>
</html>