<?php
// ================================================
// INTERFACE DE ENVIO DE IMAGENS - IDENTIFICADOR
// VERSÃO CORRIGIDA - 15/02/2026
// Adaptada para nova estrutura do banco (status único)
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
// USUÁRIO LOGADO (DA SESSÃO)
// ================================================
$id_usuario_logado = $_SESSION['usuario_id'] ?? 1; // Fallback para 1 durante testes
$nome_usuario_logado = "Usuário";

// Buscar nome do usuário se possível
if ($id_usuario_logado) {
    $sql_user = "SELECT nome FROM usuarios WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql_user);
    mysqli_stmt_bind_param($stmt, "i", $id_usuario_logado);
    mysqli_stmt_execute($stmt);
    $result_user = mysqli_stmt_get_result($stmt);
    $user_data = mysqli_fetch_assoc($result_user);
    if ($user_data) {
        $nome_usuario_logado = $user_data['nome'];
    }
    mysqli_stmt_close($stmt);
}

// ================================================
// FUNÇÕES
// ================================================

/**
 * Busca espécies que precisam de imagens (status = dados_internet ou descrita)
 */
function getEspeciesQuePrecisamImagens($conexao) {
    $sql = "SELECT id, nome_cientifico, status, prioridade
            FROM especies_administrativo 
            WHERE status IN ('dados_internet', 'descrita')
            ORDER BY 
                CASE prioridade 
                    WHEN 'urgente' THEN 1
                    WHEN 'alta' THEN 2 
                    WHEN 'media' THEN 3 
                    WHEN 'baixa' THEN 4
                    ELSE 5
                END,
                nome_cientifico
            LIMIT 50";
    
    $resultado = mysqli_query($conexao, $sql);
    $especies = [];
    while ($linha = mysqli_fetch_assoc($resultado)) {
        $especies[] = $linha;
    }
    mysqli_free_result($resultado);
    return $especies;
}

/**
 * Busca espécies que já estão completas (status = registrada ou superior)
 */
function getEspeciesCompletas($conexao) {
    $sql = "SELECT id, nome_cientifico, status, prioridade
            FROM especies_administrativo 
            WHERE status IN ('registrada', 'em_revisao', 'revisada', 'publicado')
            ORDER BY nome_cientifico
            LIMIT 30";
    
    $resultado = mysqli_query($conexao, $sql);
    $especies = [];
    while ($linha = mysqli_fetch_assoc($resultado)) {
        $especies[] = $linha;
    }
    mysqli_free_result($resultado);
    return $especies;
}

/**
 * Busca todas as espécies (para fallback)
 */
function getTodasEspecies($conexao) {
    $sql = "SELECT id, nome_cientifico, status, prioridade
            FROM especies_administrativo 
            ORDER BY 
                CASE status
                    WHEN 'dados_internet' THEN 1
                    WHEN 'descrita' THEN 2
                    WHEN 'sem_dados' THEN 3
                    ELSE 4
                END,
                nome_cientifico
            LIMIT 100";
    
    $resultado = mysqli_query($conexao, $sql);
    $especies = [];
    while ($linha = mysqli_fetch_assoc($resultado)) {
        $especies[] = $linha;
    }
    mysqli_free_result($resultado);
    return $especies;
}

/**
 * Busca resumo das imagens por espécie
 */
function getResumoImagensPorEspecie($conexao, $especie_id) {
    $partes = ['folha', 'flor', 'fruto', 'caule', 'semente', 'habito'];
    $resumo = [];
    
    foreach ($partes as $parte) {
        $sql = "SELECT COUNT(*) as total
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
            'tem_imagem' => ($dados['total'] ?? 0) > 0
        ];
    }
    
    return $resumo;
}

/**
 * Busca dados de uma espécie específica
 */
function getEspeciePorId($conexao, $especie_id) {
    $sql = "SELECT id, nome_cientifico, status, prioridade
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

/**
 * Busca contribuições do usuário logado
 */
function getMinhasContribuicoes($conexao, $id_usuario) {
    $sql = "SELECT 
                i.id,
                i.especie_id,
                i.parte,
                i.data_upload,
                i.descricao,
                i.caminho_imagem,
                e.nome_cientifico,
                e.status as status_especie
            FROM imagens_especies i
            INNER JOIN especies_administrativo e ON i.especie_id = e.id
            WHERE i.id_usuario_identificador = ?
            ORDER BY i.data_upload DESC
            LIMIT 15";
    
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

$especie_id_selecionada = isset($_GET['especie_id']) ? (int)$_GET['especie_id'] : 0;

// Limpar seleção
if (isset($_GET['limpar'])) {
    $especie_id_selecionada = 0;
    header("Location: upload_imagem.php");
    exit;
}

// ================================================
// BUSCAR DADOS
// ================================================

$especies_prioritarias = getEspeciesQuePrecisamImagens($conexao);
$especies_completas = getEspeciesCompletas($conexao);
$todas_especies = getTodasEspecies($conexao);

$especie_selecionada = null;
$resumo_imagens = [];

if ($especie_id_selecionada > 0) {
    $especie_selecionada = getEspeciePorId($conexao, $especie_id_selecionada);
    if ($especie_selecionada) {
        $resumo_imagens = getResumoImagensPorEspecie($conexao, $especie_id_selecionada);
    } else {
        $especie_id_selecionada = 0;
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

        .badge-dados_internet { background: var(--azul-claro); color: #1e40af; border: 1px solid var(--azul); }
        .badge-descrita { background: var(--verde-claro); color: #065f46; border: 1px solid var(--verde-penomato); }
        .badge-registrada { background: var(--verde-sucesso-claro); color: #065f46; border: 1px solid var(--verde-sucesso); }
        .badge-em_revisao { background: var(--amarelo-claro); color: #92400e; border: 1px solid var(--amarelo); }
        .badge-revisada { background: var(--verde-sucesso-claro); color: #065f46; border: 1px solid var(--verde-sucesso); }
        .badge-publicado { background: #f3e8ff; color: #6b21a8; border: 1px solid #a855f7; }

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
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
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
            height: 80px;
            background: var(--cinza-fundo);
            border-radius: 12px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--cinza-texto);
            border: 1px solid var(--cinza-borda);
            font-size: 0.9rem;
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

        .btn-outline:hover {
            border-color: var(--verde-penomato);
        }

        .btn-success {
            background: var(--verde-sucesso);
            color: white;
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

        .contribuicao-meta {
            font-size: 0.8rem;
            color: var(--cinza-texto);
            margin-top: 4px;
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

        .campo-obrigatorio::after {
            content: " *";
            color: var(--vermelho);
            font-weight: bold;
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

        .progresso-container {
            margin-top: 20px;
            padding: 20px;
            background: var(--verde-claro);
            border-radius: 12px;
        }

        .barra-progresso {
            height: 8px;
            background: var(--cinza-borda);
            border-radius: 4px;
            overflow: hidden;
            margin: 10px 0;
        }

        .barra-progresso-preenchimento {
            height: 100%;
            background: var(--verde-penomato);
            transition: width 0.3s ease;
        }

        .info-importante {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px 16px;
            border-radius: 8px;
            margin: 15px 0;
            font-size: 0.95rem;
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
                <span class="badge-status badge-<?php echo $especie_selecionada['status']; ?>">
                    <?php echo strtoupper($especie_selecionada['status']); ?>
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
                    
                    <?php if (count($especies_prioritarias) > 0): ?>
                    <optgroup label="🔴 PRIORITÁRIAS (precisam de imagens)">
                        <?php foreach ($especies_prioritarias as $especie): ?>
                        <option value="<?php echo $especie['id']; ?>" <?php echo $especie_id_selecionada == $especie['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($especie['nome_cientifico']); ?>
                            <?php if ($especie['prioridade'] == 'urgente'): ?>[URGENTE]<?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endif; ?>
                    
                    <?php if (count($especies_completas) > 0): ?>
                    <optgroup label="✅ COMPLETAS (já tem imagens)">
                        <?php foreach ($especies_completas as $especie): ?>
                        <option value="<?php echo $especie['id']; ?>" <?php echo $especie_id_selecionada == $especie['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($especie['nome_cientifico']); ?>
                        </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <?php endif; ?>
                    
                    <optgroup label="📋 TODAS AS ESPÉCIES">
                        <?php foreach ($todas_especies as $especie): ?>
                        <option value="<?php echo $especie['id']; ?>" <?php echo $especie_id_selecionada == $especie['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($especie['nome_cientifico']); ?> 
                            (<?php echo $especie['status']; ?>)
                        </option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </form>
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px; flex-wrap: wrap; gap: 15px;">
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div><span style="color: var(--azul); font-weight: 600;">🔵</span> Prioritárias: <strong><?php echo count($especies_prioritarias); ?></strong></div>
                    <div><span style="color: #065f46; font-weight: 600;">✅</span> Completas: <strong><?php echo count($especies_completas); ?></strong></div>
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
                </div>
            </div>
            <div>
                <span class="badge-status badge-<?php echo $especie_selecionada['status']; ?>" style="font-size: 0.9rem; padding: 8px 20px;">
                    <?php echo strtoupper($especie_selecionada['status']); ?>
                </span>
            </div>
        </div>

        <?php if (in_array($especie_selecionada['status'], ['registrada', 'em_revisao', 'revisada', 'publicado'])): ?>
        <div class="info-importante">
            <strong>ℹ️ Esta espécie já está em estágio avançado.</strong> 
            Você ainda pode enviar mais imagens, mas elas serão adicionadas ao acervo existente.
        </div>
        <?php endif; ?>

        <!-- ========== GRID PRINCIPAL ========== -->
        <div class="grid-dashboard">
            <!-- COLUNA 1: FORMULÁRIOS DE UPLOAD -->
            <div>
                <h2>📸 2. ESCOLHA A PARTE E ENVIE AS IMAGENS</h2>
                
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
                    
                    foreach ($partes as $parte_key => $parte_info):
                        $dados_parte = $resumo_imagens[$parte_key] ?? ['total' => 0, 'tem_imagem' => false];
                    ?>
                    <div class="parte-card">
                        <div class="parte-header">
                            <span class="parte-icone"><?php echo $parte_info['icone']; ?></span>
                            <span class="parte-nome"><?php echo $parte_info['nome']; ?></span>
                        </div>
                        
                        <div class="parte-stats">
                            <span>📸 <strong><?php echo $dados_parte['total']; ?></strong> imagens</span>
                            <?php if ($dados_parte['tem_imagem']): ?>
                            <span style="color: var(--verde-penomato);">✅ possui imagem</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="preview-area">
                            <?php if ($dados_parte['tem_imagem']): ?>
                            <span style="color: var(--verde-penomato);">✅ Já possui <?php echo $dados_parte['total']; ?> imagem(ns)</span>
                            <?php else: ?>
                            <span style="color: var(--cinza-texto);">📸 Nenhuma imagem ainda</span>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data" action="../Controllers/upload_imagem_controlador.php">
                            <input type="hidden" name="especie_id" value="<?php echo $especie_selecionada['id']; ?>">
                            <input type="hidden" name="parte" value="<?php echo $parte_key; ?>">
                            
                            <div class="upload-area" onclick="document.getElementById('file_<?php echo $parte_key; ?>').click();">
                                <span style="font-size: 1.5rem;">📂</span>
                                <p style="font-weight: 500; margin-top: 5px;">Clique para selecionar</p>
                                <p style="font-size: 0.75rem; color: var(--cinza-texto);">JPG, PNG · Máx 10MB</p>
                                <input type="file" id="file_<?php echo $parte_key; ?>" name="imagem" accept="image/*" style="display: none;" required>
                            </div>
                            
                            <div class="campo">
                                <label class="campo-obrigatorio">Legenda descritiva</label>
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
                                <?php echo $dados_parte['tem_imagem'] ? '➕ ADICIONAR MAIS' : '📤 ENVIAR PRIMEIRA IMAGEM'; ?>
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>
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
                                <div class="contribuicao-meta">
                                    <span><?php echo ucfirst($contrib['parte']); ?></span> · 
                                    <span><?php echo date('d/m/Y H:i', strtotime($contrib['data_upload'])); ?></span>
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
                    
                    <?php
                    $partes_obrigatorias = ['folha', 'flor', 'fruto', 'caule', 'habito'];
                    $total_partes = count($partes_obrigatorias);
                    $partes_com_imagem = 0;
                    
                    foreach ($partes_obrigatorias as $p) {
                        if ($resumo_imagens[$p]['tem_imagem']) {
                            $partes_com_imagem++;
                        }
                    }
                    
                    $percentual = round(($partes_com_imagem / $total_partes) * 100);
                    ?>
                    
                    <div style="margin-bottom: 25px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="font-weight: 600;">Partes obrigatórias</span>
                            <span style="color: var(--verde-penomato); font-weight: 600;">
                                <?php echo $partes_com_imagem; ?>/<?php echo $total_partes; ?>
                            </span>
                        </div>
                        <div class="barra-progresso">
                            <div class="barra-progresso-preenchimento" style="width: <?php echo $percentual; ?>%;"></div>
                        </div>
                        <div style="margin-top: 5px; font-size: 0.85rem; color: var(--cinza-texto);">
                            Progresso: <?php echo $percentual; ?>%
                        </div>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach ($partes_obrigatorias as $p): ?>
                        <div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                <span style="font-weight: 500;">
                                    <?php 
                                        $nomes = ['folha' => '🍃 Folha', 'flor' => '🌸 Flor', 'fruto' => '🍎 Fruto', 
                                                  'caule' => '🌿 Caule', 'habito' => '🌳 Hábito'];
                                        echo $nomes[$p];
                                    ?>
                                </span>
                                <span style="color: <?php echo $resumo_imagens[$p]['tem_imagem'] ? 'var(--verde-penomato)' : 'var(--cinza-texto)'; ?>; font-weight: 600;">
                                    <?php echo $resumo_imagens[$p]['total']; ?> imagens
                                </span>
                            </div>
                            <div class="barra-progresso" style="height: 6px;">
                                <div class="barra-progresso-preenchimento" 
                                     style="width: <?php echo $resumo_imagens[$p]['tem_imagem'] ? '100%' : '0%'; ?>; 
                                            background: <?php echo $resumo_imagens[$p]['tem_imagem'] ? 'var(--verde-penomato)' : 'var(--cinza-borda)'; ?>;">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($partes_com_imagem == $total_partes): ?>
                    <div class="progresso-container" style="margin-top: 25px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-size: 2rem;">🏆</span>
                            <div>
                                <div style="font-weight: 700; color: var(--verde-penomato); margin-bottom: 5px;">
                                    Todas as partes têm imagens!
                                </div>
                                <div style="font-size: 0.9rem; color: var(--cinza-texto);">
                                    Esta espécie está pronta para revisão.
                                    <?php if ($especie_selecionada['status'] == 'dados_internet' || $especie_selecionada['status'] == 'descrita'): ?>
                                    O sistema vai avançar automaticamente para "registrada".
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($especie_selecionada['status'] == 'dados_internet'): ?>
                    <div class="info-importante" style="margin-top: 20px;">
                        <strong>ℹ️ Dados da internet</strong> - As características desta espécie vieram de fontes não verificadas. As imagens que você enviar ajudarão na validação.
                    </div>
                    <?php endif; ?>
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
                Dê prioridade às espécies com status <span style="background: var(--azul-claro); color: #1e40af; padding: 4px 12px; border-radius: 30px; font-weight: 600;">DADOS_INTERNET</span> ou <span style="background: var(--verde-claro); color: #065f46; padding: 4px 12px; border-radius: 30px; font-weight: 600;">DESCRITA</span>.
            </p>
            
            <div class="stats-grid">
                <div class="stat-item">
                    <div style="font-size: 2rem; margin-bottom: 10px;">🔵</div>
                    <div style="font-weight: 600;">Precisam de imagens</div>
                    <div class="stat-value" style="color: var(--azul);">
                        <?php echo count($especies_prioritarias); ?>
                    </div>
                </div>
                <div class="stat-item">
                    <div style="font-size: 2rem; margin-bottom: 10px;">✅</div>
                    <div style="font-weight: 600;">Já completas</div>
                    <div class="stat-value" style="color: var(--verde-penomato);">
                        <?php echo count($especies_completas); ?>
                    </div>
                </div>
                <div class="stat-item">
                    <div style="font-size: 2rem; margin-bottom: 10px;">📋</div>
                    <div style="font-weight: 600;">Total no sistema</div>
                    <div class="stat-value">
                        <?php echo count($todas_especies); ?>
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