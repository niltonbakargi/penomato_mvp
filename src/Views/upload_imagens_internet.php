<?php
// ================================================
// UPLOAD DE IMAGENS - VERSÃO COM BOTÃO AVANÇAR
// ================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../../config/app.php';
$servidor   = DB_HOST;
$usuario_db = DB_USER;
$senha_db   = DB_PASS;
$banco      = DB_NAME;

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
    <style>
        body {
            background-color: var(--cinza-50);
            padding: var(--esp-8) var(--esp-5);
            color: var(--cinza-800);
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
            color: var(--cor-primaria);
            font-size: 1.2rem;
        }
        
        .user-name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .user-logout {
            color: var(--perigo-cor);
            text-decoration: none;
            font-size: 0.9rem;
            padding: 5px 10px;
            border-radius: 20px;
            transition: all 0.2s;
        }
        
        .user-logout:hover {
            background: var(--perigo-cor);
            color: white;
        }

        /* Cabeçalho */
        .header {
            background: white;
            padding: 30px 40px;
            border-radius: 12px 12px 0 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-bottom: 4px solid var(--cor-primaria);
            margin-bottom: 5px;
        }

        .header h1 {
            color: var(--cor-primaria);
            font-size: 2rem;
            font-weight: 500;
        }

        .header .subtitle {
            color: var(--cinza-500);
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
            background-color: var(--verde-50);
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
            color: var(--cor-primaria);
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
            background-color: var(--cinza-200);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .progress-fill {
            height: 100%;
            background-color: var(--cor-primaria);
            width: <?php echo $progresso; ?>%;
            transition: width 0.3s ease;
        }

        .progress-text {
            text-align: center;
            font-size: 0.9rem;
            color: var(--cinza-500);
        }

        /* Alertas */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .alert-success {
            background-color: var(--sucesso-fundo);
            color: var(--sucesso-texto);
            border-left: 4px solid var(--sucesso-cor);
        }

        .alert-danger {
            background-color: var(--perigo-fundo);
            color: var(--perigo-texto);
            border-left: 4px solid var(--perigo-cor);
        }

        /* Grid de partes */
        .partes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 30px 0;
        }

        .parte-card {
            background-color: var(--cinza-50);
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            border: 2px solid var(--cinza-200);
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .parte-card:hover {
            border-color: var(--cor-primaria);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .parte-card.selecionado {
            border-color: var(--cor-primaria);
            background-color: #e6f7e6;
            border-width: 3px;
        }

        .parte-card.completa {
            background-color: #d4edda;
            border-color: var(--sucesso-cor);
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
            color: var(--cinza-500);
        }

        .parte-contagem span {
            font-weight: 600;
            color: var(--cor-primaria);
        }

        /* Formulário de upload */
        .upload-parte-form {
            background-color: var(--cinza-50);
            border: 3px solid var(--cor-primaria);
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
        }

        .upload-parte-form h3 {
            color: var(--cor-primaria);
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
            background-color: var(--verde-50);
            border-radius: 8px;
        }

        .parte-info-icone {
            font-size: 3rem;
        }

        .parte-info-nome {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--cor-primaria);
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
            border: 2px dashed var(--cor-primaria);
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            background-color: var(--verde-50);
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
        }

        .colar-area:hover {
            background-color: #e0f0e0;
            border-color: var(--cor-primaria-hover);
        }

        .colar-area .icone {
            font-size: 4rem;
            margin-bottom: 15px;
            color: var(--cor-primaria);
        }

        .colar-area .texto {
            font-size: 1.2rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .colar-area .subtexto {
            font-size: 0.9rem;
            color: var(--cinza-500);
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
            color: var(--cor-primaria);
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
            border: 2px solid var(--cinza-200);
            border-radius: 6px;
            font-size: 1rem;
        }

        .fonte-info input:focus,
        .fonte-info select:focus {
            outline: none;
            border-color: var(--cor-primaria);
        }

        .fonte-info small {
            color: var(--cinza-500);
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
            border: 2px solid var(--cinza-200);
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }

        .preview-image {
            height: 150px;
            background-color: var(--cinza-50);
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid var(--cinza-200);
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
            color: var(--cinza-500);
        }

        .remove-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid var(--perigo-cor);
            color: var(--perigo-cor);
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
            background: var(--perigo-cor);
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
            background-color: var(--cor-primaria);
            color: white;
            box-shadow: 0 4px 10px rgba(11,94,66,0.3);
        }

        .btn-primary:hover {
            background-color: var(--cor-primaria-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(11,94,66,0.4);
        }

        .btn-primary:disabled {
            background-color: var(--cinza-300);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-success {
            background-color: var(--sucesso-cor);
            color: white;
            box-shadow: 0 4px 10px rgba(40,167,69,0.3);
        }

        .btn-success:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(40,167,69,0.4);
        }

        .btn-avancar {
            background-color: var(--cor-primaria);
            color: white;
            box-shadow: 0 4px 10px rgba(11,94,66,0.3);
        }

        .btn-avancar:hover {
            background-color: var(--cor-primaria-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(11,94,66,0.4);
        }

        .btn-secondary {
            background-color: var(--cinza-500);
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }

        .footer {
            text-align: center;
            color: var(--cinza-500);
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

        /* ================================================ */
        /* CARROSSEL DE BUSCA AUTOMÁTICA                    */
        /* ================================================ */
        .parte-card { cursor: pointer; }

        .carrossel-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.75);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: var(--esp-5);
        }
        .carrossel-overlay.aberto { display: flex; }

        .carrossel-container {
            background: var(--branco);
            border-radius: var(--raio-lg);
            box-shadow: var(--sombra-lg);
            width: 100%;
            max-width: 700px;
            max-height: 95vh;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .carrossel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--esp-5) var(--esp-8);
            border-bottom: 2px solid var(--cor-primaria);
            position: sticky;
            top: 0;
            background: var(--branco);
            z-index: 10;
        }
        .carrossel-header h2 {
            color: var(--cor-primaria);
            font-size: var(--texto-xl);
            margin: 0;
        }
        .btn-fechar-carrossel {
            background: none;
            border: 2px solid var(--cinza-300);
            border-radius: var(--raio-full);
            width: 36px; height: 36px;
            font-size: var(--texto-lg);
            cursor: pointer;
            color: var(--cinza-600);
            display: flex; align-items: center; justify-content: center;
            transition: var(--transicao);
        }
        .btn-fechar-carrossel:hover { background: var(--perigo-fundo); color: var(--perigo-cor); border-color: var(--perigo-cor); }

        .carrossel-corpo { padding: var(--esp-6) var(--esp-8); }

        /* Loading */
        .carrossel-loading { text-align: center; padding: var(--esp-16) 0; }
        .spinner {
            width: 48px; height: 48px;
            border: 5px solid var(--cinza-200);
            border-top-color: var(--cor-primaria);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto var(--esp-5);
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Sem resultados */
        .carrossel-vazio { text-align: center; padding: var(--esp-10) 0; color: var(--cinza-600); }
        .carrossel-vazio p { margin-bottom: var(--esp-5); font-size: var(--texto-lg); }

        /* Navegação da imagem */
        .carrossel-nav-topo {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--esp-4);
            font-size: var(--texto-sm);
            color: var(--cinza-500);
        }

        .carrossel-imagem-wrap {
            display: flex;
            align-items: center;
            gap: var(--esp-3);
            margin-bottom: var(--esp-5);
        }
        .btn-nav-carrossel {
            background: var(--cinza-100);
            border: 2px solid var(--cinza-200);
            border-radius: var(--raio-full);
            width: 44px; height: 44px;
            font-size: 1.5rem;
            cursor: pointer;
            flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            transition: var(--transicao);
        }
        .btn-nav-carrossel:hover { background: var(--verde-50); border-color: var(--cor-primaria); }

        .carrossel-imagem {
            flex: 1;
            position: relative;
            background: var(--cinza-50);
            border-radius: var(--raio-md);
            overflow: hidden;
            min-height: 260px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--cinza-200);
        }
        .carrossel-imagem img {
            max-width: 100%;
            max-height: 300px;
            object-fit: contain;
            cursor: zoom-in;
        }
        .carrossel-badge-status {
            position: absolute;
            top: 8px; left: 8px;
            padding: 4px 12px;
            border-radius: var(--raio-full);
            font-size: var(--texto-xs);
            font-weight: var(--peso-semi);
            display: none;
        }
        .carrossel-badge-status.selecionada { display: block; background: var(--sucesso-fundo); color: var(--sucesso-texto); }
        .carrossel-badge-status.principal   { display: block; background: #fff3cd; color: #856404; }

        /* Metadados */
        .carrossel-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--esp-2) var(--esp-5);
            background: var(--cinza-50);
            border-radius: var(--raio-md);
            padding: var(--esp-4) var(--esp-5);
            margin-bottom: var(--esp-5);
            font-size: var(--texto-sm);
        }
        .meta-item { display: flex; align-items: flex-start; gap: var(--esp-2); color: var(--cinza-700); }
        .meta-item i { color: var(--cor-primaria); margin-top: 2px; flex-shrink: 0; }
        .meta-item a { color: var(--cor-primaria); text-decoration: none; }
        .meta-item a:hover { text-decoration: underline; }

        /* Botões de ação */
        .carrossel-acoes {
            display: flex;
            gap: var(--esp-4);
            justify-content: center;
            margin-bottom: var(--esp-6);
        }
        .btn-usar {
            background: var(--cor-primaria); color: var(--branco);
            border: none; padding: var(--esp-3) var(--esp-8);
            border-radius: var(--raio-full); font-weight: var(--peso-semi);
            cursor: pointer; font-size: var(--texto-md); transition: var(--transicao);
        }
        .btn-usar:hover:not(:disabled) { background: var(--cor-primaria-hover); transform: translateY(-1px); }
        .btn-usar:disabled { background: var(--cinza-300); cursor: not-allowed; }
        .btn-descartar {
            background: var(--branco); color: var(--perigo-cor);
            border: 2px solid var(--perigo-cor);
            padding: var(--esp-3) var(--esp-8);
            border-radius: var(--raio-full); font-weight: var(--peso-semi);
            cursor: pointer; font-size: var(--texto-md); transition: var(--transicao);
        }
        .btn-descartar:hover { background: var(--perigo-fundo); }

        /* Thumbnails selecionadas */
        .carrossel-selecionadas {
            border-top: 1px solid var(--cinza-200);
            padding-top: var(--esp-5);
            margin-bottom: var(--esp-5);
        }
        .carrossel-selecionadas h4 { color: var(--cinza-700); margin-bottom: var(--esp-4); font-size: var(--texto-sm); }
        .thumbs-grid {
            display: flex;
            gap: var(--esp-3);
            flex-wrap: wrap;
        }
        .thumb-item {
            position: relative;
            width: 80px; height: 80px;
            border-radius: var(--raio-md);
            overflow: hidden;
            border: 3px solid var(--cinza-200);
            cursor: pointer;
            transition: var(--transicao);
        }
        .thumb-item:hover { border-color: var(--cor-primaria); }
        .thumb-item.principal { border-color: #ffc107; box-shadow: 0 0 0 2px #ffc107; }
        .thumb-item img { width: 100%; height: 100%; object-fit: cover; }
        .thumb-estrela {
            position: absolute; top: 2px; right: 2px;
            background: rgba(255,193,7,0.9);
            border-radius: 50%; width: 20px; height: 20px;
            font-size: 12px;
            display: none; align-items: center; justify-content: center;
        }
        .thumb-item.principal .thumb-estrela { display: flex; }
        .thumb-remover {
            position: absolute; bottom: 2px; right: 2px;
            background: rgba(220,53,69,0.85);
            color: white; border: none;
            border-radius: 50%; width: 20px; height: 20px;
            font-size: 11px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }

        /* Botão confirmar */
        .carrossel-rodape {
            border-top: 1px solid var(--cinza-200);
            padding-top: var(--esp-5);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--esp-4);
        }
        .btn-manual-link {
            background: none; border: none;
            color: var(--cinza-500); text-decoration: underline;
            cursor: pointer; font-size: var(--texto-sm);
        }
        .btn-manual-link:hover { color: var(--cinza-700); }
        .btn-confirmar-selecao {
            background: var(--sucesso-cor); color: var(--branco);
            border: none; padding: var(--esp-3) var(--esp-8);
            border-radius: var(--raio-full); font-weight: var(--peso-semi);
            cursor: pointer; font-size: var(--texto-md); transition: var(--transicao);
        }
        .btn-confirmar-selecao:hover:not(:disabled) { background: #218838; transform: translateY(-1px); }
        .btn-confirmar-selecao:disabled { background: var(--cinza-300); cursor: not-allowed; }
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
                    <span style="margin-left: 15px; color: var(--cinza-500);">ID: <?php echo $especie_id; ?></span>
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
                    if ($contagem > 0 && in_array($key, $partes_obrigatorias)) $classe .= ' completa';
                ?>
                <button type="button"
                        class="<?php echo $classe; ?>"
                        data-parte="<?php echo $key; ?>"
                        onclick="iniciarBusca('<?php echo $key; ?>')">
                    <div class="parte-icone"><?php echo $parte['icone']; ?></div>
                    <div class="parte-nome"><?php echo $parte['nome']; ?></div>
                    <div class="parte-contagem">
                        <span id="count-<?php echo $key; ?>"><?php echo $contagem; ?></span> imagem(ns)
                    </div>
                    <?php if ($parte['obrigatoria'] && $contagem == 0): ?>
                        <div style="font-size:0.8rem;color:var(--perigo-cor);margin-top:5px;">⛔ Obrigatória</div>
                    <?php endif; ?>
                </button>
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
            <p style="text-align: center; margin-top: 20px; color: var(--cinza-500); font-size: 0.9rem;">
                <i class="fas fa-info-circle"></i> 
                Você pode adicionar imagens agora ou clicar em "AVANÇAR PARA DADOS" para ir para o próximo passo.
                As imagens já adicionadas ficarão salvas na sessão.
            </p>

        </div>

        <div class="footer">
            Penomato • PASSO 2 DE 3 - Upload de imagens
        </div>
    </div>

    <!-- ================================================ -->
    <!-- MODAL: CARROSSEL DE BUSCA AUTOMÁTICA             -->
    <!-- ================================================ -->
    <div id="carrosselModal" class="carrossel-overlay">
        <div class="carrossel-container">

            <!-- Cabeçalho -->
            <div class="carrossel-header">
                <h2 id="carrosselTitulo">🔍 Buscando imagens...</h2>
                <button class="btn-fechar-carrossel" onclick="fecharCarrossel()" title="Fechar">✕</button>
            </div>

            <div class="carrossel-corpo">

                <!-- Estado: carregando -->
                <div id="carrosselLoading" class="carrossel-loading">
                    <div class="spinner"></div>
                    <p style="color:var(--cinza-600);">Buscando no iNaturalist e Wikimedia Commons...</p>
                </div>

                <!-- Estado: sem resultados -->
                <div id="carrosselVazio" style="display:none;" class="carrossel-vazio">
                    <p>😕 Nenhuma imagem encontrada nas fontes automáticas.</p>
                    <button onclick="abrirFormManual()" class="btn btn-secondary" style="margin:0 auto;">
                        ✏️ Adicionar manualmente
                    </button>
                </div>

                <!-- Estado: carrossel com imagens -->
                <div id="carrosselConteudo" style="display:none;">

                    <!-- Contador de navegação -->
                    <div class="carrossel-nav-topo">
                        <span id="carrosselCounter">1 / 5</span>
                        <span style="color:var(--cinza-400);font-size:var(--texto-xs);">Clique na imagem para abrir em tamanho original</span>
                    </div>

                    <!-- Imagem + botões de navegação -->
                    <div class="carrossel-imagem-wrap">
                        <button class="btn-nav-carrossel" onclick="navegarCarrossel(-1)" title="Anterior">‹</button>

                        <div class="carrossel-imagem">
                            <img id="carrosselImg" src="" alt="Candidata" title="Clique para abrir em tamanho original">
                            <div id="carrosselBadge" class="carrossel-badge-status"></div>
                        </div>

                        <button class="btn-nav-carrossel" onclick="navegarCarrossel(1)" title="Próxima">›</button>
                    </div>

                    <!-- Metadados -->
                    <div class="carrossel-meta">
                        <div class="meta-item"><i class="fas fa-user"></i><span id="metaAutor">—</span></div>
                        <div class="meta-item"><i class="fas fa-balance-scale"></i><span id="metaLicenca">—</span></div>
                        <div class="meta-item"><i class="fas fa-database"></i><span id="metaFonte">—</span></div>
                        <div class="meta-item"><i class="fas fa-map-marker-alt"></i><span id="metaLocal">—</span></div>
                        <div class="meta-item"><i class="fas fa-calendar"></i><span id="metaData">—</span></div>
                        <div class="meta-item"><i class="fas fa-star"></i><span id="metaPontuacao">—</span> pts</div>
                    </div>

                    <!-- Ações -->
                    <div class="carrossel-acoes">
                        <button onclick="descartarAtual()" class="btn-descartar">✕ Descartar</button>
                        <button onclick="usarAtual()" class="btn-usar" id="btnUsar">✓ Usar esta</button>
                    </div>

                    <!-- Thumbnails das selecionadas -->
                    <div class="carrossel-selecionadas">
                        <h4>
                            Selecionadas: <span id="countSelecionadas">0</span>/5
                            <small style="color:var(--cinza-400);font-weight:normal;margin-left:8px;">⭐ = aparece no artigo • clique para trocar principal</small>
                        </h4>
                        <div class="thumbs-grid" id="thumbsSelecionadas"></div>
                    </div>

                    <!-- Rodapé: confirmar ou manual -->
                    <div class="carrossel-rodape">
                        <button onclick="abrirFormManual()" class="btn-manual-link">✏️ Adicionar manualmente</button>
                        <button onclick="confirmarSelecao()" class="btn-confirmar-selecao" id="btnConfirmar" disabled>
                            ⬇️ Baixar e salvar selecionadas
                        </button>
                    </div>

                </div><!-- /carrosselConteudo -->
            </div><!-- /carrossel-corpo -->
        </div><!-- /carrossel-container -->
    </div><!-- /carrosselModal -->

    <script>
    // ================================================
    // CARROSSEL — estado
    // ================================================
    let carrosselParte = '';
    let candidatas     = [];
    let indiceAtual    = 0;
    let selecionadas   = [];   // [{id, thumbnail, principal}]
    let principalId    = null;

    const ESPECIE_ID = <?php echo (int)$especie_id; ?>;
    const TEMP_ID    = <?php echo json_encode($temp_id); ?>;

    // ------------------------------------------------
    // Abrir busca ao clicar numa parte
    // ------------------------------------------------
    function iniciarBusca(parte) {
        carrosselParte = parte;
        candidatas     = [];
        selecionadas   = [];
        indiceAtual    = 0;
        principalId    = null;

        const nomes = {
            folha:'Folha', flor:'Flor', fruto:'Fruto', caule:'Caule',
            semente:'Semente', habito:'Hábito',
            exsicata_completa:'Exsicata', detalhe:'Detalhe'
        };

        document.getElementById('carrosselTitulo').textContent =
            '🔍 Buscando imagens para: ' + (nomes[parte] || parte);

        document.getElementById('carrosselLoading').style.display  = 'block';
        document.getElementById('carrosselVazio').style.display     = 'none';
        document.getElementById('carrosselConteudo').style.display  = 'none';
        document.getElementById('carrosselModal').classList.add('aberto');

        const fd = new FormData();
        fd.append('especie_id',   ESPECIE_ID);
        fd.append('parte_planta', parte);
        fd.append('temp_id',      TEMP_ID);

        fetch('../Controllers/buscar_imagens_automatico.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                document.getElementById('carrosselLoading').style.display = 'none';
                if (!data.sucesso || !data.candidatas || data.candidatas.length === 0) {
                    document.getElementById('carrosselVazio').style.display = 'block';
                    return;
                }
                candidatas  = data.candidatas;
                indiceAtual = 0;
                document.getElementById('carrosselConteudo').style.display = 'block';
                renderCandidataAtual();
            })
            .catch(() => {
                document.getElementById('carrosselLoading').style.display = 'none';
                document.getElementById('carrosselVazio').style.display   = 'block';
            });
    }

    // ------------------------------------------------
    // Renderizar imagem atual no carrossel
    // ------------------------------------------------
    function renderCandidataAtual() {
        const c = candidatas[indiceAtual];
        if (!c) return;

        const img = document.getElementById('carrosselImg');
        img.src     = c.url_thumbnail || c.url_foto;
        img.onclick = () => window.open(c.url_foto, '_blank');

        document.getElementById('carrosselCounter').textContent =
            (indiceAtual + 1) + ' / ' + candidatas.length;
        document.getElementById('metaAutor').textContent      = c.autor        || 'Não informado';
        document.getElementById('metaLicenca').textContent    = c.licenca      || 'Não informada';
        document.getElementById('metaFonte').textContent      = c.fonte_nome   || c.fonte;
        document.getElementById('metaLocal').textContent      = c.local_coleta || '—';
        document.getElementById('metaData').textContent       = c.data_observacao || '—';
        document.getElementById('metaPontuacao').textContent  = c.pontuacao;

        // Badge e botão usar
        const badge   = document.getElementById('carrosselBadge');
        const btnUsar = document.getElementById('btnUsar');
        const sel     = selecionadas.find(s => s.id == c.id);

        badge.className = 'carrossel-badge-status';
        if (sel) {
            badge.textContent = sel.principal ? '⭐ Principal (artigo)' : '✓ Selecionada';
            badge.classList.add(sel.principal ? 'principal' : 'selecionada');
            btnUsar.textContent = '✓ Já selecionada';
            btnUsar.disabled    = true;
        } else {
            badge.textContent = '';
            btnUsar.textContent = '✓ Usar esta';
            btnUsar.disabled    = selecionadas.length >= 5;
        }
    }

    // ------------------------------------------------
    // Navegação
    // ------------------------------------------------
    function navegarCarrossel(dir) {
        indiceAtual = (indiceAtual + dir + candidatas.length) % candidatas.length;
        renderCandidataAtual();
    }

    // ------------------------------------------------
    // Usar imagem atual
    // ------------------------------------------------
    function usarAtual() {
        const c = candidatas[indiceAtual];
        if (!c || selecionadas.find(s => s.id == c.id) || selecionadas.length >= 5) return;

        const ehPrincipal = selecionadas.length === 0;
        if (ehPrincipal) principalId = c.id;

        selecionadas.push({
            id:        c.id,
            thumbnail: c.url_thumbnail || c.url_foto,
            principal: ehPrincipal,
        });

        renderThumbs();

        // Avança automaticamente
        if (indiceAtual < candidatas.length - 1) indiceAtual++;
        renderCandidataAtual();
    }

    // ------------------------------------------------
    // Descartar e avançar
    // ------------------------------------------------
    function descartarAtual() {
        if (indiceAtual < candidatas.length - 1) indiceAtual++;
        renderCandidataAtual();
    }

    // ------------------------------------------------
    // Marcar principal (para o artigo)
    // ------------------------------------------------
    function marcarPrincipal(id) {
        principalId  = id;
        selecionadas = selecionadas.map(s => ({ ...s, principal: s.id == id }));
        renderThumbs();
        renderCandidataAtual();
    }

    // ------------------------------------------------
    // Remover da seleção
    // ------------------------------------------------
    function removerSelecionada(id) {
        selecionadas = selecionadas.filter(s => s.id != id);
        if (principalId == id) {
            principalId = selecionadas.length > 0 ? selecionadas[0].id : null;
            if (selecionadas.length > 0) selecionadas[0].principal = true;
        }
        renderThumbs();
        renderCandidataAtual();
    }

    // ------------------------------------------------
    // Renderizar thumbnails selecionadas
    // ------------------------------------------------
    function renderThumbs() {
        document.getElementById('countSelecionadas').textContent = selecionadas.length;
        document.getElementById('btnConfirmar').disabled = selecionadas.length === 0;

        document.getElementById('thumbsSelecionadas').innerHTML = selecionadas.map(s => `
            <div class="thumb-item ${s.principal ? 'principal' : ''}"
                 onclick="marcarPrincipal(${s.id})"
                 title="${s.principal ? 'Principal do artigo' : 'Clique para usar no artigo'}">
                <img src="${s.thumbnail}" alt="">
                <div class="thumb-estrela">⭐</div>
                <button class="thumb-remover"
                        onclick="event.stopPropagation();removerSelecionada(${s.id})"
                        title="Remover">✕</button>
            </div>
        `).join('');
    }

    // ------------------------------------------------
    // Confirmar e baixar
    // ------------------------------------------------
    function confirmarSelecao() {
        if (selecionadas.length === 0) return;

        const btn = document.getElementById('btnConfirmar');
        btn.textContent = '⏳ Baixando...';
        btn.disabled    = true;

        const fd = new FormData();
        fd.append('temp_id',        TEMP_ID);
        fd.append('candidatos_ids', JSON.stringify(selecionadas.map(s => s.id)));
        fd.append('principal_id',   principalId || 0);

        fetch('../Controllers/salvar_imagens_selecionadas.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(data => {
                if (data.sucesso) {
                    // Atualiza contador do card da parte
                    const span = document.getElementById('count-' + carrosselParte);
                    if (span) span.textContent = parseInt(span.textContent || 0) + data.salvas;

                    const card = document.querySelector('[data-parte="' + carrosselParte + '"]');
                    if (card && data.salvas > 0) card.classList.add('completa');

                    fecharCarrossel();
                    mostrarAlertaSucesso(data.salvas + ' imagem(ns) salva(s) para ' + carrosselParte + '!');
                } else {
                    alert('Erro: ' + (data.erro || 'Tente novamente.'));
                    btn.textContent = '⬇️ Baixar e salvar selecionadas';
                    btn.disabled    = false;
                }
            })
            .catch(() => {
                alert('Erro de conexão. Tente novamente.');
                btn.textContent = '⬇️ Baixar e salvar selecionadas';
                btn.disabled    = false;
            });
    }

    // ------------------------------------------------
    // Fechar modal
    // ------------------------------------------------
    function fecharCarrossel() {
        document.getElementById('carrosselModal').classList.remove('aberto');
        candidatas   = [];
        selecionadas = [];
    }

    // Fechar clicando fora
    document.getElementById('carrosselModal').addEventListener('click', function(e) {
        if (e.target === this) fecharCarrossel();
    });

    // ------------------------------------------------
    // Fallback: abrir formulário manual (Ctrl+V)
    // ------------------------------------------------
    function abrirFormManual() {
        fecharCarrossel();
        window.location.href = '?temp_id=' + encodeURIComponent(TEMP_ID) + '&parte=' + carrosselParte;
    }

    // ------------------------------------------------
    // Alerta de sucesso temporário
    // ------------------------------------------------
    function mostrarAlertaSucesso(msg) {
        const div = document.createElement('div');
        div.className = 'alerta--sucesso';
        div.style.cssText = 'position:fixed;top:24px;left:50%;transform:translateX(-50%);z-index:9999;padding:14px 28px;border-radius:8px;white-space:nowrap;box-shadow:0 4px 15px rgba(0,0,0,0.15);';
        div.textContent = '✅ ' + msg;
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 3500);
    }

    // ================================================
    // SCRIPT PARA COLAR IMAGEM (Ctrl+V) — fallback manual
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
                            style="margin-top:10px;padding:6px 18px;border:2px solid var(--perigo-cor);background:white;color:var(--perigo-cor);border-radius:20px;cursor:pointer;font-weight:600;">
                        × Remover imagem
                    </button>
                `;
                colarArea.style.backgroundColor = 'var(--verde-50)';
                colarArea.style.borderColor = 'var(--sucesso-cor)';
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
            colarArea.style.borderColor = 'var(--cor-primaria-hover)';
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
        colarArea.style.backgroundColor = 'var(--verde-50)';
        colarArea.style.borderColor = 'var(--cor-primaria)';
        colarArea.style.padding = '40px';

        // Reanexar referência ao novo textarea
        const novoColarInput = document.getElementById('colarInput');
        novoColarInput.addEventListener('focus', function() {
            colarArea.style.backgroundColor = '#e0f0e0';
            colarArea.style.borderColor = 'var(--cor-primaria-hover)';
        });
        novoColarInput.addEventListener('blur', function() {
            if (!imagemColada) {
                colarArea.style.backgroundColor = 'var(--verde-50)';
                colarArea.style.borderColor = 'var(--cor-primaria)';
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