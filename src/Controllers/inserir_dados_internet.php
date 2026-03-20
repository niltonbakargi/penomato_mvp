<?php
// ==============================src\Controllers\inserir_dados_internet.php==================
// INSERIR DADOS INTERNET - VERSÃO MODIFICADA
// AGORA É A TERCEIRA TELA (depois das imagens)
// ================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
ob_start();

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

$servidor = "127.0.0.1";
$usuario_db = "root";
$senha_db = "";
$banco = "penomato";

// ================================================
// VERIFICAR SESSÃO TEMPORÁRIA
// ================================================
$temp_id = isset($_GET['temp_id']) ? $_GET['temp_id'] : '';

if (empty($temp_id) || !isset($_SESSION['importacao_temporaria']) || $_SESSION['importacao_temporaria']['temp_id'] !== $temp_id) {
    header("Location: escolher_especie.php?erro=" . urlencode("Sessão expirada. Inicie novamente."));
    exit;
}

// Verificar se o usuário da sessão é o mesmo
if ($_SESSION['importacao_temporaria']['usuario_id'] != $id_usuario) {
    die("Você não tem permissão para acessar esta importação.");
}

$dados_temporarios = $_SESSION['importacao_temporaria'];
$especie_id = $dados_temporarios['especie_id'];

// Buscar nome da espécie para exibir
$conexao = new mysqli($servidor, $usuario_db, $senha_db, $banco);
if (!$conexao->connect_error) {
    $conexao->set_charset("utf8mb4");
    $sql_especie = "SELECT nome_cientifico FROM especies_administrativo WHERE id = ?";
    $stmt = $conexao->prepare($sql_especie);
    $stmt->bind_param("i", $especie_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $especie = $resultado->fetch_assoc();
    $nome_cientifico = $especie['nome_cientifico'] ?? 'Espécie desconhecida';
    $stmt->close();
    $conexao->close();
}

// ================================================
// LISTA DE VALORES PADRONIZADOS PARA VALIDAÇÃO
// ================================================
$opcoes_validas = [
    'forma_folha' => ['Lanceolada', 'Linear', 'Elíptica', 'Oval', 'Orbicular', 'Cordiforme', 'Espatulada', 'Sagitada', 'Reniforme', 'Obovada', 'Trilobada', 'Palmada', 'Lobada', 'Composta pinnada', 'Composta bipinada'],
    'filotaxia_folha' => ['Alterna', 'Oposta Simples', 'Oposta Decussada', 'Verticilada', 'Rosetada', 'Dística', 'Espiralada'],
    'tipo_folha' => ['Simples', 'Composta pinnada', 'Composta bipinada', 'Composta tripinada', 'Composta tetrapinada'],
    'tamanho_folha' => ['Microfilos (< 2 cm)', 'Nanofilos (2–7 cm)', 'Mesofilos (7–20 cm)', 'Macrófilos (20–50 cm)', 'Megafilas (> 50 cm)'],
    'textura_folha' => ['Coriácea', 'Cartácea', 'Membranácea', 'Suculenta', 'Pilosa', 'Glabra', 'Rugosa', 'Cerosa'],
    'margem_folha' => ['Inteira', 'Serrada', 'Dentada', 'Crenada', 'Ondulada', 'Lobada', 'Partida', 'Revoluta', 'Involuta'],
    'venacao_folha' => ['Reticulada Pinnada', 'Reticulada Palmada', 'Paralela', 'Peninérvea', 'Dicotômica', 'Curvinérvea'],
    'cor_flores' => ['Brancas', 'Amarelas', 'Vermelhas', 'Rosadas', 'Roxas', 'Azuis', 'Laranjas', 'Verdes'],
    'simetria_floral' => ['Actinomorfa', 'Zigomorfa', 'Assimétrica'],
    'numero_petalas' => ['3 pétalas', '4 pétalas', '5 pétalas', 'Muitas pétalas'],
    'disposicao_flores' => ['Isoladas', 'Inflorescência'],
    'aroma' => ['Sem cheiro', 'Aroma suave', 'Aroma forte', 'Aroma desagradável'],
    'tamanho_flor' => ['Pequena', 'Média'],
    'tipo_fruto' => ['Baga', 'Drupa', 'Cápsula', 'Folículo', 'Legume', 'Síliqua', 'Aquênio', 'Sâmara', 'Cariopse', 'Pixídio', 'Hespéridio', 'Pepo'],
    'tamanho_fruto' => ['Pequeno', 'Médio', 'Grande'],
    'cor_fruto' => ['Verde', 'Amarelo', 'Vermelho', 'Roxo', 'Laranja', 'Marrom', 'Preto', 'Branco'],
    'textura_fruto' => ['Lisa', 'Rugosa', 'Coriácea', 'Peluda', 'Espinhosa', 'Cerosa'],
    'dispersao_fruto' => ['Zoocórica', 'Anemocórica', 'Hidrocórica', 'Autocórica'],
    'aroma_fruto' => ['Sem cheiro', 'Aroma suave', 'Aroma forte', 'Aroma desagradável'],
    'tipo_semente' => ['Alada', 'Carnosa', 'Dura', 'Oleosa', 'Peluda'],
    'tamanho_semente' => ['Pequena', 'Média', 'Grande'],
    'cor_semente' => ['Preta', 'Marrom', 'Branca', 'Amarela', 'Verde'],
    'textura_semente' => ['Lisa', 'Rugosa', 'Estriada', 'Cerosa'],
    'quantidade_sementes' => ['Uma', 'Poucas', 'Muitas'],
    'tipo_caule' => ['Ereto', 'Prostrado', 'Rastejante', 'Trepador', 'Subterrâneo'],
    'estrutura_caule' => ['Lenhoso', 'Herbáceo', 'Suculento'],
    'textura_caule' => ['Lisa', 'Rugosa', 'Sulcada', 'Fissurada', 'Cerosa', 'Espinhosa', 'Suberosa'],
    'cor_caule' => ['Marrom', 'Verde', 'Cinza', 'Avermelhado', 'Alaranjado'],
    'forma_caule' => ['Cilíndrico', 'Quadrangular', 'Achatado', 'Irregular'],
    'modificacao_caule' => ['Estolão', 'Cladódio', 'Rizoma', 'Tubérculo', 'Espinhos'],
    'diametro_caule' => ['Fino', 'Médio', 'Grosso'],
    'ramificacao_caule' => ['Dicotômica', 'Monopodial', 'Simpodial'],
    'possui_espinhos' => ['Sim', 'Não'],
    'possui_latex' => ['Sim', 'Não'],
    'possui_seiva' => ['Sim', 'Não'],
    'possui_resina' => ['Sim', 'Não']
];

// ================================================
// PROCESSAR O ENVIO DO FORMULÁRIO (FINALIZAR)
// ================================================
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_importacao'])) {
    
    // Pega os dados do JSON do POST (campos ocultos gerados pelo JavaScript)
    $dados_caracteristicas = [];
    $todos_campos = [
        'nome_cientifico_completo', 'nome_cientifico_completo_ref',
        'sinonimos', 'sinonimos_ref', 'nome_popular', 'nome_popular_ref',
        'familia', 'familia_ref', 'forma_folha', 'forma_folha_ref',
        'filotaxia_folha', 'filotaxia_folha_ref', 'tipo_folha', 'tipo_folha_ref',
        'tamanho_folha', 'tamanho_folha_ref', 'textura_folha', 'textura_folha_ref',
        'margem_folha', 'margem_folha_ref', 'venacao_folha', 'venacao_folha_ref',
        'cor_flores', 'cor_flores_ref', 'simetria_floral', 'simetria_floral_ref',
        'numero_petalas', 'numero_petalas_ref', 'disposicao_flores', 'disposicao_flores_ref',
        'aroma', 'aroma_ref', 'tamanho_flor', 'tamanho_flor_ref',
        'tipo_fruto', 'tipo_fruto_ref', 'tamanho_fruto', 'tamanho_fruto_ref',
        'cor_fruto', 'cor_fruto_ref', 'textura_fruto', 'textura_fruto_ref',
        'dispersao_fruto', 'dispersao_fruto_ref', 'aroma_fruto', 'aroma_fruto_ref',
        'tipo_semente', 'tipo_semente_ref', 'tamanho_semente', 'tamanho_semente_ref',
        'cor_semente', 'cor_semente_ref', 'textura_semente', 'textura_semente_ref',
        'quantidade_sementes', 'quantidade_sementes_ref',
        'tipo_caule', 'tipo_caule_ref', 'estrutura_caule', 'estrutura_caule_ref',
        'textura_caule', 'textura_caule_ref', 'cor_caule', 'cor_caule_ref',
        'forma_caule', 'forma_caule_ref', 'modificacao_caule', 'modificacao_caule_ref',
        'diametro_caule', 'diametro_caule_ref', 'ramificacao_caule', 'ramificacao_caule_ref',
        'possui_espinhos', 'possui_espinhos_ref', 'possui_latex', 'possui_latex_ref',
        'possui_seiva', 'possui_seiva_ref', 'possui_resina', 'possui_resina_ref',
        'referencias'
    ];
    
    foreach ($todos_campos as $campo) {
        if (isset($_POST[$campo]) && $_POST[$campo] !== '') {
            $dados_caracteristicas[$campo] = $_POST[$campo];
        }
    }
    
    // Adicionar especie_id da sessão
    $dados_caracteristicas['especie_id'] = $especie_id;
    
    // ATUALIZAR A SESSÃO COM OS DADOS CARREGADOS
    $_SESSION['importacao_temporaria']['dados'] = $dados_caracteristicas;
    
    // REDIRECIONAR PARA FINALIZAR (que vai processar tudo)
    header("Location: finalizar_upload_temporario.php?temp_id=" . urlencode($temp_id));
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penomato - Importar Dados da Espécie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ── Planilha de campos ── */
        .section-title {
            background: var(--cor-primaria);
            color: #fff;
            padding: 9px 14px;
            margin: 24px 0 14px;
            border-radius: 6px;
            font-size: 0.95em;
            font-weight: 600;
        }
        .input-group {
            display: flex;
            gap: 12px;
            margin-bottom: 14px;
            align-items: flex-start;
        }
        .main-input { flex: 2; }
        .ref-col    { flex: 1; }
        .input-group label {
            display: block;
            font-weight: 600;
            font-size: 0.88em;
            margin-bottom: 4px;
            color: #2d3d2d;
        }
        .input-group label .subtext { font-weight: normal; font-size: 0.9em; color: #666; }
        .info-text { font-size: 0.78em; color: #666; margin-top: 3px; }
        .input-group select,
        .input-group input[type="text"],
        .input-group textarea {
            width: 100%;
            padding: 7px 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 0.9em;
            font-family: inherit;
            transition: border-color 0.15s;
        }
        .input-group select:focus,
        .input-group input[type="text"]:focus,
        .input-group textarea:focus { border-color: var(--cor-primaria); outline: none; }
        .input-group textarea { resize: vertical; }
        .auto-filled { border-color: var(--cor-primaria) !important; background: #f0faf5; }
        .ref-wrapper { display: flex; gap: 5px; align-items: center; }
        .ref-wrapper input { flex: 1; }
        .confirm-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; border-radius: 5px;
            border: 2px solid #ccc; background: #f8f9fa; color: #ccc;
            font-size: 1.1em; font-weight: 700; flex-shrink: 0;
            cursor: pointer; padding: 0; line-height: 1; user-select: none;
            transition: background 0.15s, border-color 0.15s, color 0.15s;
        }
        .confirm-btn:hover { border-color: var(--cor-primaria); color: var(--cor-primaria); }
        .confirm-btn.confirmed { background: var(--cor-primaria); border-color: var(--cor-primaria); color: #fff; }
        /* ── Prompt box ── */
        .prompt-box {
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 10px; padding: 18px 20px; margin-bottom: 24px;
        }
        .prompt-box-header {
            display: flex; align-items: center;
            justify-content: space-between; margin-bottom: 12px;
        }
        .prompt-box-title { font-weight: 600; color: #2d3d2d; font-size: 0.95rem; }
        .prompt-content {
            background: white; border: 1px solid #e2e8f0; border-radius: 6px;
            padding: 14px; font-family: 'Courier New', monospace; font-size: 0.75rem;
            color: #374151; max-height: 200px; overflow-y: auto;
            white-space: pre-wrap; word-break: break-word; line-height: 1.5;
            margin-bottom: 12px;
        }
        .prompt-placeholder { color: #aaa; font-style: italic; font-family: inherit; font-size: 0.88rem; }
        .btn-copy { background: #17a2b8; color: white; }
        .btn-copy:hover { background: #138496; transform: translateY(-1px); }
        .btn-copy.copied { background: var(--cor-primaria); }
        .ai-section-header {
            font-size: 1.1rem; font-weight: 700; color: var(--cor-primaria);
            margin-bottom: 6px; display: flex; align-items: center; gap: 8px;
        }
        .ai-section-sub { color: #666; font-size: 0.88rem; margin-bottom: 20px; }
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
            max-width: 900px;
            margin: 0 auto;
            position: relative;
        }
        
        .paper-header {
            background: white;
            padding: 30px 40px;
            border-radius: 12px 12px 0 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-bottom: 4px solid var(--cor-primaria);
            margin-bottom: 5px;
        }
        
        .paper-header h1 {
            color: var(--cor-primaria);
            font-size: 2rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .paper-header .subtitle {
            color: #666;
            font-style: italic;
            margin-top: 10px;
            font-size: 0.95rem;
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
            z-index: 10;
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
        
        /* Info da espécie atual */
        .current-species {
            background-color: #e8f5e9;
            padding: 15px 20px;
            border-radius: 8px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 15px;
            border-left: 4px solid var(--cor-primaria);
        }
        
        .current-species i {
            font-size: 2rem;
            color: var(--cor-primaria);
        }
        
        .current-species h2 {
            font-size: 1.5rem;
            color: var(--cor-primaria);
            font-style: italic;
        }
        
        .current-species span {
            margin-left: auto;
            background-color: var(--cor-primaria);
            color: white;
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 0.9rem;
        }
        
        .paper-card {
            background: white;
            padding: 30px 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .json-import-area {
            background-color: #f8fafc;
            border: 2px dashed var(--cor-primaria);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .json-import-area h3 {
            color: var(--cor-primaria);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            margin: 15px 0;
        }
        
        textarea:focus {
            outline: none;
            border-color: var(--cor-primaria);
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 28px;
            border: none;
            border-radius: 30px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: var(--cor-primaria);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--cor-primaria-hover);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #e2e8f0;
            color: #2d3748;
        }
        
        .btn-secondary:hover {
            background-color: #cbd5e0;
        }
        
        .btn-back {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-back:hover {
            background-color: #5a6268;
        }
        
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-container {
            background-color: white;
            border-radius: 12px;
            width: 90%;
            max-width: 700px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            padding: 20px 25px;
            background-color: var(--cor-primaria);
            color: white;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .modal-header h2 {
            font-size: 1.3rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .modal-header .badge {
            background-color: #ffc107;
            color: #2c3e50;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.9rem;
        }
        
        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.8rem;
            cursor: pointer;
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .campo-validacao {
            background-color: #f8fafc;
            border-left: 4px solid var(--cor-primaria);
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 0 8px 8px 0;
        }
        
        .campo-titulo {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--cor-primaria);
            margin-bottom: 10px;
        }
        
        .campo-json {
            background-color: #edf2f7;
            padding: 12px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            margin-bottom: 15px;
            border-left: 3px solid #718096;
        }
        
        .opcoes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
        }
        
        .opcao-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background-color: white;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .opcao-item:hover {
            border-color: var(--cor-primaria);
        }
        
        .opcao-item.selecionado {
            border-color: var(--cor-primaria);
            background-color: #e6f7e6;
            font-weight: 500;
        }
        
        .opcao-item.sugerida {
            border-left: 4px solid var(--cor-primaria);
        }
        
        .opcao-item input[type="radio"] {
            width: 16px;
            height: 16px;
        }
        
        .modal-footer {
            padding: 20px 25px;
            background-color: #f8fafc;
            border-top: 2px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            position: sticky;
            bottom: 0;
            z-index: 10;
        }
        
        .btn-modal {
            padding: 12px 30px;
            border: none;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }
        
        .btn-modal-confirm {
            background-color: var(--cor-primaria);
            color: white;
        }
        
        .btn-modal-confirm:hover:not(:disabled) {
            background-color: var(--cor-primaria-hover);
        }
        
        .btn-modal-confirm:disabled {
            background-color: #cbd5e0;
            cursor: not-allowed;
        }
        
        .btn-modal-cancel {
            background-color: #e2e8f0;
            color: #2d3748;
        }
        
        .technical-paper {
            margin-top: 30px;
        }
        
        .species-title {
            font-size: 2rem;
            font-style: italic;
            color: var(--cor-primaria);
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .info-item {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
        }
        
        .info-item strong {
            color: var(--cor-primaria);
            display: block;
            margin-bottom: 5px;
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        
        .section-divider {
            font-size: 1.3rem;
            color: var(--cor-primaria);
            margin: 30px 0 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .characteristic-block {
            background-color: #faf9f7;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .characteristic-item {
            margin-bottom: 10px;
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
        }
        
        .characteristic-label {
            font-weight: 600;
            min-width: 140px;
            color: #4a5568;
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        
        .characteristic-value {
            font-size: 1rem;
            color: #1a202c;
            flex: 1;
        }
        
        .ref-note {
            display: inline-block;
            font-size: 0.7rem;
            color: var(--cor-primaria);
            background: #e8f0fe;
            padding: 2px 6px;
            border-radius: 12px;
            margin-left: 8px;
            vertical-align: super;
        }
        
        .references-section {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8fafc;
            border-radius: 8px;
            border-top: 3px solid var(--cor-primaria);
        }
        
        .references-title {
            font-size: 1.1rem;
            color: var(--cor-primaria);
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .reference-list {
            list-style: none;
            padding: 0;
            counter-reset: item;
        }
        
        .reference-list li {
            margin-bottom: 8px;
            padding-left: 25px;
            position: relative;
            font-size: 0.9rem;
            color: #4a5568;
        }
        
        .reference-list li::before {
            content: "[" counter(item) "]";
            counter-increment: item;
            position: absolute;
            left: 0;
            color: var(--cor-primaria);
            font-size: 0.85rem;
        }
        
        .save-button-container {
            text-align: center;
            margin: 30px 0;
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-save {
            background-color: var(--cor-primaria);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1.2rem;
            border-radius: 40px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-save:hover:not(:disabled) {
            background-color: var(--cor-primaria-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(11,94,66,0.3);
        }
        
        .btn-save:disabled {
            background-color: #cbd5e0;
            cursor: not-allowed;
        }
        
        .btn-back-to-images {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            padding: 15px 40px;
            font-size: 1.2rem;
            border-radius: 40px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }
        
        .btn-back-to-images:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
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
            
            .current-species {
                flex-direction: column;
                text-align: center;
            }
            
            .current-species span {
                margin-left: 0;
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

        <div class="paper-header">
            <h1><span>📄</span> PENOMATO • IMPORTAR DADOS</h1>
            <div class="subtitle">PASSO 3: Preencha as características morfológicas manualmente ou use a pesquisa por IA abaixo</div>
        </div>

        <?php if (isset($erro)): ?>
            <div class="alert alert-danger">❌ <?php echo $erro; ?></div>
        <?php endif; ?>

        <!-- Informação da espécie atual -->
        <div class="current-species">
            <i class="fas fa-tree"></i>
            <h2><?php echo htmlspecialchars($nome_cientifico); ?></h2>
            <span>ID: <?php echo $especie_id; ?></span>
        </div>

        <!-- ══════════════════════════════════════════
             PLANILHA MANUAL
        ══════════════════════════════════════════ -->
        <div class="paper-card">

            <div class="alert alert-info" style="margin-bottom:20px;">
                <i class="fas fa-info-circle"></i>
                <strong>Imagens já adicionadas:</strong>
                <?php
                $total_imagens = count($_SESSION['importacao_temporaria']['imagens'] ?? []);
                echo $total_imagens > 0 ? "{$total_imagens} imagem(ns) na sessão" : "Nenhuma imagem ainda (volte para adicionar)";
                ?>
            </div>

            <form method="POST" action="" id="form_principal">
                <input type="hidden" name="temp_id" value="<?php echo htmlspecialchars($temp_id); ?>">

                <!-- ── IDENTIFICAÇÃO BÁSICA ── -->
                <div class="section-title">📌 Identificação Básica</div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="nome_cientifico_completo">Nome Científico Completo</label>
                        <input type="text" id="nome_cientifico_completo" name="nome_cientifico_completo"
                               placeholder="Ex: Mauritia flexuosa L.f.">
                    </div>
                    <div class="ref-col">
                        <label for="nome_cientifico_completo_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="nome_cientifico_completo_ref" name="nome_cientifico_completo_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="sinonimos">Sinônimos <span class="subtext">(nomes científicos antigos)</span></label>
                        <input type="text" id="sinonimos" name="sinonimos" placeholder="Separados por vírgula">
                    </div>
                    <div class="ref-col">
                        <label for="sinonimos_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="sinonimos_ref" name="sinonimos_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="nome_popular">Nome Popular</label>
                        <input type="text" id="nome_popular" name="nome_popular" placeholder="Nome popular da espécie">
                    </div>
                    <div class="ref-col">
                        <label for="nome_popular_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="nome_popular_ref" name="nome_popular_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="familia">Família</label>
                        <input type="text" id="familia" name="familia" placeholder="Família botânica">
                    </div>
                    <div class="ref-col">
                        <label for="familia_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="familia_ref" name="familia_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <!-- ── FOLHA ── -->
                <div class="section-title">🍃 Características da Folha</div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="forma_folha">Forma</label>
                        <select id="forma_folha" name="forma_folha">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Lanceolada</option><option>Linear</option><option>Elíptica</option>
                            <option>Oval</option><option>Orbicular</option><option>Cordiforme</option>
                            <option>Espatulada</option><option>Sagitada</option><option>Reniforme</option>
                            <option>Obovada</option><option>Trilobada</option><option>Palmada</option><option>Lobada</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="forma_folha_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="forma_folha_ref" name="forma_folha_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="filotaxia_folha">Filotaxia</label>
                        <select id="filotaxia_folha" name="filotaxia_folha">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Alterna</option><option>Oposta Simples</option><option>Oposta Decussada</option>
                            <option>Verticilada</option><option>Rosetada</option><option>Dística</option><option>Espiralada</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="filotaxia_folha_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="filotaxia_folha_ref" name="filotaxia_folha_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="tipo_folha">Tipo</label>
                        <select id="tipo_folha" name="tipo_folha">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Simples</option><option>Composta pinnada</option><option>Composta bipinada</option>
                            <option>Composta tripinada</option><option>Composta tetrapinada</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="tipo_folha_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="tipo_folha_ref" name="tipo_folha_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="tamanho_folha">Tamanho</label>
                        <select id="tamanho_folha" name="tamanho_folha">
                            <option value="" disabled selected>Selecione…</option>
                            <option value="Microfilos (< 2 cm)">Microfilos (&lt; 2 cm)</option>
                            <option value="Nanofilos (2–7 cm)">Nanofilos (2–7 cm)</option>
                            <option value="Mesofilos (7–20 cm)">Mesofilos (7–20 cm)</option>
                            <option value="Macrófilos (20–50 cm)">Macrófilos (20–50 cm)</option>
                            <option value="Megafilas (> 50 cm)">Megafilas (&gt; 50 cm)</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="tamanho_folha_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="tamanho_folha_ref" name="tamanho_folha_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="textura_folha">Textura</label>
                        <select id="textura_folha" name="textura_folha">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Coriácea</option><option>Cartácea</option><option>Membranácea</option>
                            <option>Suculenta</option><option>Pilosa</option><option>Glabra</option>
                            <option>Rugosa</option><option>Cerosa</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="textura_folha_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="textura_folha_ref" name="textura_folha_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="margem_folha">Margem</label>
                        <select id="margem_folha" name="margem_folha">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Inteira</option><option>Serrada</option><option>Dentada</option>
                            <option>Crenada</option><option>Ondulada</option><option>Lobada</option>
                            <option>Partida</option><option>Revoluta</option><option>Involuta</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="margem_folha_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="margem_folha_ref" name="margem_folha_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="venacao_folha">Venação</label>
                        <select id="venacao_folha" name="venacao_folha">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Reticulada Pinnada</option><option>Reticulada Palmada</option>
                            <option>Paralela</option><option>Peninérvea</option>
                            <option>Dicotômica</option><option>Curvinérvea</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="venacao_folha_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="venacao_folha_ref" name="venacao_folha_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <!-- ── FLORES ── -->
                <div class="section-title">🌸 Características das Flores</div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="cor_flores">Cor das Flores</label>
                        <select id="cor_flores" name="cor_flores">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Brancas</option><option>Amarelas</option><option>Vermelhas</option>
                            <option>Rosadas</option><option>Roxas</option><option>Azuis</option>
                            <option>Laranjas</option><option>Verdes</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="cor_flores_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="cor_flores_ref" name="cor_flores_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="simetria_floral">Simetria Floral</label>
                        <select id="simetria_floral" name="simetria_floral">
                            <option value="" disabled selected>Selecione…</option>
                            <option value="Actinomorfa">Actinomorfa (simetria radial)</option>
                            <option value="Zigomorfa">Zigomorfa (simetria bilateral)</option>
                            <option value="Assimétrica">Assimétrica (sem simetria)</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="simetria_floral_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="simetria_floral_ref" name="simetria_floral_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="numero_petalas">Número de Pétalas</label>
                        <select id="numero_petalas" name="numero_petalas">
                            <option value="" disabled selected>Selecione…</option>
                            <option value="3 pétalas">3 pétalas</option>
                            <option value="4 pétalas">4 pétalas</option>
                            <option value="5 pétalas">5 pétalas</option>
                            <option value="Muitas pétalas">Muitas pétalas</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="numero_petalas_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="numero_petalas_ref" name="numero_petalas_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="disposicao_flores">Disposição das Flores</label>
                        <select id="disposicao_flores" name="disposicao_flores">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Isoladas</option>
                            <option value="Inflorescência">Inflorescência (cacho, espiga, capítulo, umbela)</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="disposicao_flores_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="disposicao_flores_ref" name="disposicao_flores_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="aroma">Aroma das Flores</label>
                        <select id="aroma" name="aroma">
                            <option value="" disabled selected>Selecione…</option>
                            <option value="Sem cheiro">Sem cheiro</option>
                            <option value="Aroma suave">Aroma suave</option>
                            <option value="Aroma forte">Aroma forte</option>
                            <option value="Aroma desagradável">Aroma desagradável</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="aroma_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="aroma_ref" name="aroma_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="tamanho_flor">Tamanho da Flor</label>
                        <select id="tamanho_flor" name="tamanho_flor">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Pequena</option><option>Média</option><option>Grande</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="tamanho_flor_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="tamanho_flor_ref" name="tamanho_flor_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <!-- ── FRUTOS ── -->
                <div class="section-title">🍎 Características dos Frutos</div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="tipo_fruto">Tipo de Fruto</label>
                        <select id="tipo_fruto" name="tipo_fruto">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Baga</option><option>Drupa</option><option>Cápsula</option>
                            <option>Folículo</option><option>Legume</option><option>Síliqua</option>
                            <option>Aquênio</option><option>Sâmara</option><option>Cariopse</option>
                            <option>Pixídio</option><option>Hespéridio</option><option>Pepo</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="tipo_fruto_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="tipo_fruto_ref" name="tipo_fruto_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="tamanho_fruto">Tamanho do Fruto</label>
                        <select id="tamanho_fruto" name="tamanho_fruto">
                            <option value="" disabled selected>Selecione…</option>
                            <option value="Pequeno">Pequeno (&lt; 2 cm)</option>
                            <option value="Médio">Médio (2–5 cm)</option>
                            <option value="Grande">Grande (&gt; 5 cm)</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="tamanho_fruto_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="tamanho_fruto_ref" name="tamanho_fruto_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="cor_fruto">Cor do Fruto</label>
                        <select id="cor_fruto" name="cor_fruto">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Verde</option><option>Amarelo</option><option>Vermelho</option>
                            <option>Roxo</option><option>Laranja</option><option>Marrom</option>
                            <option>Preto</option><option>Branco</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="cor_fruto_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="cor_fruto_ref" name="cor_fruto_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="textura_fruto">Textura do Fruto</label>
                        <select id="textura_fruto" name="textura_fruto">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Lisa</option><option>Rugosa</option><option>Coriácea</option>
                            <option>Peluda</option><option>Espinhosa</option><option>Cerosa</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="textura_fruto_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="textura_fruto_ref" name="textura_fruto_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="dispersao_fruto">Tipo de Dispersão</label>
                        <select id="dispersao_fruto" name="dispersao_fruto">
                            <option value="" disabled selected>Selecione…</option>
                            <option value="Zoocórica">Zoocórica (por animais)</option>
                            <option value="Anemocórica">Anemocórica (pelo vento)</option>
                            <option value="Hidrocórica">Hidrocórica (pela água)</option>
                            <option value="Autocórica">Autocórica (pelo próprio fruto)</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="dispersao_fruto_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="dispersao_fruto_ref" name="dispersao_fruto_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="aroma_fruto">Aroma do Fruto</label>
                        <select id="aroma_fruto" name="aroma_fruto">
                            <option value="" disabled selected>Selecione…</option>
                            <option value="Sem cheiro">Sem cheiro</option>
                            <option value="Aroma suave">Aroma suave</option>
                            <option value="Aroma forte">Aroma forte</option>
                            <option value="Aroma desagradável">Aroma desagradável</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="aroma_fruto_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="aroma_fruto_ref" name="aroma_fruto_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <!-- ── SEMENTES ── -->
                <div class="section-title">🌱 Características das Sementes</div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="tipo_semente">Tipo de Semente</label>
                        <select id="tipo_semente" name="tipo_semente">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Alada</option><option>Carnosa</option><option>Dura</option>
                            <option>Oleosa</option><option>Peluda</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="tipo_semente_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="tipo_semente_ref" name="tipo_semente_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="tamanho_semente">Tamanho da Semente</label>
                        <select id="tamanho_semente" name="tamanho_semente">
                            <option value="" disabled selected>Selecione…</option>
                            <option value="Pequena">Pequena (&lt; 5 mm)</option>
                            <option value="Média">Média (5–10 mm)</option>
                            <option value="Grande">Grande (&gt; 10 mm)</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="tamanho_semente_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="tamanho_semente_ref" name="tamanho_semente_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="cor_semente">Cor da Semente</label>
                        <select id="cor_semente" name="cor_semente">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Preta</option><option>Marrom</option><option>Branca</option>
                            <option>Amarela</option><option>Verde</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="cor_semente_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="cor_semente_ref" name="cor_semente_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="textura_semente">Textura da Semente</label>
                        <select id="textura_semente" name="textura_semente">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Lisa</option><option>Rugosa</option><option>Estriada</option><option>Cerosa</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="textura_semente_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="textura_semente_ref" name="textura_semente_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="quantidade_sementes">Quantidade de Sementes por Fruto</label>
                        <select id="quantidade_sementes" name="quantidade_sementes">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Uma</option>
                            <option value="Poucas">Poucas (2–5)</option>
                            <option value="Muitas">Muitas (&gt; 5)</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="quantidade_sementes_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="quantidade_sementes_ref" name="quantidade_sementes_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <!-- ── CAULE ── -->
                <div class="section-title">🌿 Características do Caule</div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="tipo_caule">Tipo de Caule</label>
                        <select id="tipo_caule" name="tipo_caule">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Ereto</option><option>Prostrado</option><option>Rastejante</option>
                            <option>Trepador</option><option>Subterrâneo</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="tipo_caule_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="tipo_caule_ref" name="tipo_caule_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="estrutura_caule">Estrutura do Caule</label>
                        <select id="estrutura_caule" name="estrutura_caule">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Lenhoso</option><option>Herbáceo</option><option>Suculento</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="estrutura_caule_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="estrutura_caule_ref" name="estrutura_caule_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="textura_caule">Textura do Caule</label>
                        <select id="textura_caule" name="textura_caule">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Lisa</option><option>Rugosa</option><option>Sulcada</option>
                            <option>Fissurada</option><option>Cerosa</option><option>Espinhosa</option><option>Suberosa</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="textura_caule_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="textura_caule_ref" name="textura_caule_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="cor_caule">Cor do Caule</label>
                        <select id="cor_caule" name="cor_caule">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Marrom</option><option>Verde</option><option>Cinza</option>
                            <option>Avermelhado</option><option>Alaranjado</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="cor_caule_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="cor_caule_ref" name="cor_caule_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="forma_caule">Forma do Caule</label>
                        <select id="forma_caule" name="forma_caule">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Cilíndrico</option><option>Quadrangular</option>
                            <option>Achatado</option><option>Irregular</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="forma_caule_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="forma_caule_ref" name="forma_caule_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="modificacao_caule">Modificações do Caule</label>
                        <select id="modificacao_caule" name="modificacao_caule">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Estolão</option><option>Cladódio</option><option>Rizoma</option>
                            <option>Tubérculo</option><option>Espinhos</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="modificacao_caule_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="modificacao_caule_ref" name="modificacao_caule_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="diametro_caule">Diâmetro do Caule</label>
                        <select id="diametro_caule" name="diametro_caule">
                            <option value="" disabled selected>Selecione…</option>
                            <option value="Fino">Fino (&lt; 1 cm)</option>
                            <option value="Médio">Médio (1–5 cm)</option>
                            <option value="Grosso">Grosso (&gt; 5 cm)</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="diametro_caule_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="diametro_caule_ref" name="diametro_caule_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="ramificacao_caule">Ramificação do Caule</label>
                        <select id="ramificacao_caule" name="ramificacao_caule">
                            <option value="" disabled selected>Selecione…</option>
                            <option>Dicotômica</option><option>Monopodial</option><option>Simpodial</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="ramificacao_caule_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="ramificacao_caule_ref" name="ramificacao_caule_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <!-- ── OUTRAS ── -->
                <div class="section-title">⚡ Outras Características</div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="possui_espinhos">Possui Espinhos?</label>
                        <select id="possui_espinhos" name="possui_espinhos">
                            <option value="">Selecione…</option>
                            <option>Sim</option><option>Não</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="possui_espinhos_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="possui_espinhos_ref" name="possui_espinhos_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="possui_latex">Possui Látex?</label>
                        <select id="possui_latex" name="possui_latex">
                            <option value="">Selecione…</option>
                            <option>Sim</option><option>Não</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="possui_latex_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="possui_latex_ref" name="possui_latex_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="possui_seiva">Possui Seiva?</label>
                        <select id="possui_seiva" name="possui_seiva">
                            <option value="">Selecione…</option>
                            <option>Sim</option><option>Não</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="possui_seiva_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="possui_seiva_ref" name="possui_seiva_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <div class="main-input">
                        <label for="possui_resina">Possui Resina?</label>
                        <select id="possui_resina" name="possui_resina">
                            <option value="">Selecione…</option>
                            <option>Sim</option><option>Não</option>
                        </select>
                    </div>
                    <div class="ref-col">
                        <label for="possui_resina_ref">Referência</label>
                        <div class="ref-wrapper">
                            <input type="text" id="possui_resina_ref" name="possui_resina_ref" placeholder="URL ou nº">
                            <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
                        </div>
                    </div>
                </div>

                <!-- ── REFERÊNCIAS ── -->
                <div class="section-title">📚 Referências</div>
                <div class="input-group">
                    <div class="main-input">
                        <label for="referencias">Lista Completa de Referências</label>
                        <textarea id="referencias" name="referencias" rows="6"
                            placeholder="1. Lorenzi, H. (2002). Árvores Brasileiras Vol.1&#10;2. https://floradobrasil.jbrj.gov.br/"></textarea>
                        <div class="info-text">Use [1], [2]… nos campos de referência para referenciar esta lista</div>
                    </div>
                </div>

                <div class="save-button-container">
                    <a href="upload_imagens_internet.php?temp_id=<?php echo urlencode($temp_id); ?>" class="btn-back-to-images">
                        <i class="fas fa-arrow-left"></i> Voltar às Imagens
                    </a>
                    <button type="submit" name="finalizar_importacao" value="1" class="btn-save" id="btn_finalizar">
                        ✅ FINALIZAR IMPORTAÇÃO
                    </button>
                </div>
                <p style="text-align:center;margin-top:10px;color:#666;font-size:0.9rem;">
                    ⚠️ Após finalizar, todas as imagens e dados serão salvos permanentemente.
                </p>
            </form>
        </div>

        <!-- ══════════════════════════════════════════
             SEÇÃO DE PESQUISA POR IA
        ══════════════════════════════════════════ -->
        <div class="paper-card">
            <div class="ai-section-header">🤖 Pesquisa Automatizada com Agentes de IA</div>
            <p class="ai-section-sub">
                Copie o prompt abaixo e cole em um agente de IA (Claude, ChatGPT, Gemini…).
                O agente retornará um JSON — cole-o abaixo para preencher a planilha automaticamente.
            </p>

            <!-- Prompt -->
            <div class="prompt-box">
                <div class="prompt-box-header">
                    <div class="prompt-box-title">📝 Prompt de Pesquisa — <em><?php echo htmlspecialchars($nome_cientifico); ?></em></div>
                    <button type="button" class="btn btn-copy" id="btn_copiar_prompt" onclick="copiarPrompt()">📋 Copiar Prompt</button>
                </div>
                <div class="prompt-content" id="prompt-display"><?php
echo htmlspecialchars(
'Você é um especialista em botânica sistemática. Preencha o JSON abaixo com as características botânicas da espécie indicada.

ESPÉCIE-ALVO: ' . $nome_cientifico . '

REGRAS OBRIGATÓRIAS — leia antes de responder:

1. FORMATO DE SAÍDA: Responda APENAS com o JSON preenchido, sem nenhum texto antes ou depois, sem blocos de código markdown (sem ```json), sem comentários.

2. ESTRUTURA: O JSON de saída deve ser PLANO (flat), com todos os campos no mesmo nível — sem seções aninhadas como "folha", "flor", "fruto" etc. Siga exatamente a estrutura do EXEMPLO DE SAÍDA abaixo.

3. CAMPOS DE SELEÇÃO: Para cada campo de seleção, escreva APENAS a string exata da opção escolhida, sem nenhum wrapper. Escolha somente entre as opções listadas. NUNCA use valor fora da lista.

4. CAMPOS MÚLTIPLOS (sinonimos, nome_popular): Use uma única string com os valores separados por vírgula e espaço. Ex: "Valor A, Valor B, Valor C"

5. CAMPO referencias: Use uma única string com cada referência separada por \n, no formato:
   N. SOBRENOME, Nome. Título. Local: Editora, Ano.

6. CAMPOS _ref: String com números separados por vírgula. Ex: "1,3". Se sem referência, use string vazia "".

7. CAMPOS OPCIONAIS INAPLICÁVEIS: Use string vazia "" (ex: modificacao_caule quando a espécie não possui modificação caulinar).

8. DADOS AUSENTES: Se a informação não puder ser confirmada com segurança por uma fonte bibliográfica, use "Não informado" para campos de seleção e "" para campos livres. NUNCA invente ou suponha dados.

9. especie_id deve conter EXATAMENTE o nome científico da espécie: ' . $nome_cientifico . '

---

CAMPOS DE SELEÇÃO E SUAS OPÇÕES VÁLIDAS:

forma_folha: Acicular | Cordiforme | Elíptica | Lanceolada | Linear | Lobada | Obovada | Orbicular | Oval | Ovalada | Palmada | Reniforme | Sagitada | Trifoliada
filotaxia_folha: Alterna | Alterna dística | Alterna espiralada | Oposta | Oposta decussada | Rosulada | Verticilada
tipo_folha: Simples | Composta bipinada | Composta digitada | Composta imparipinada | Composta paripinada | Composta pinnada | Composta trifoliada | Composta tripinada
tamanho_folha: Microfila | Nanofila | Mesofila | Macrofila | Megafila
textura_folha: Cartácea | Coriácea | Glabra | Membranácea | Pilosa | Pubescente | Rugosa | Suculenta | Tomentosa | Cerosa
margem_folha: Crenada | Dentada | Inteira | Lobada | Ondulada | Serreada | Serrilhada | Partida
venacao_folha: Curvinérvea | Dicotômica | Paralela | Peninérvea | Reticulada palmada | Reticulada pinada
cor_flores: Alaranjada | Amarela | Avermelhada | Azul | Branca | Esverdeada | Lilás | Púrpura | Rósea | Roxa | Vermelha | Vinácea
simetria_floral: Actinomorfa | Zigomorfa | Assimétrica
numero_petalas: 3 pétalas | 4 pétalas | 5 pétalas | 6 pétalas | Muitas pétalas | Ausentes
disposicao_flores: Solitária | Capítulo | Cacho | Corimbo | Espádice | Espiga | Panícula | Umbela
aroma: Ausente | Suave | Forte | Desagradável | Adocicada | Cítrica
tamanho_flor: Muito pequena | Pequena | Média | Grande | Muito grande
tipo_fruto: Aquênio | Baga | Cápsula | Drupa | Folículo | Legume | Pixídio | Sâmara | Síliqua | Cariopse | Hespéridio | Pepo
tamanho_fruto: Minúsculo | Pequeno | Médio | Grande | Muito grande
cor_fruto: Alaranjado | Amarelo | Avermelhado | Branco | Esverdeado | Marrom | Preto | Roxo | Verde | Vináceo
textura_fruto: Lisa | Rugosa | Coriácea | Pubescente | Pilosa | Espinhosa | Cerosa | Tuberculada
dispersao_fruto: Anemocórica | Autocórica | Hidrocórica | Zoocórica | Mirmecocórica | Ornitocórica
aroma_fruto: Ausente | Suave | Forte | Adocicado | Cítrico | Desagradável
tipo_semente: Alada | Carnosa | Dura | Oleaginosa | Plumosa | Ruminada | Arilada
tamanho_semente: Minúscula | Muito pequena | Pequena | Média | Grande | Muito grande
cor_semente: Amarela | Branca | Castanha | Cinza | Marrom | Preta | Vermelha | Alaranjada
textura_semente: Lisa | Rugosa | Estriada | Pontuada | Foveolada | Reticulada | Tuberculada
quantidade_sementes: 1 | 2–3 | 4–10 | 11–50 | > 50
tipo_caule: Ereto | Prostrado | Escandente | Trepador | Rastejante | Subterrâneo
estrutura_caule: Herbáceo | Lenhoso | Suculento | Sublenhoso
textura_caule: Lisa | Rugosa | Fissurada | Sulcada | Estriada | Escamosa | Suberosa | Aculeada
cor_caule: Acinzentado | Alaranjado | Avermelhado | Esbranquiçado | Esverdeado | Marrom | Pardacento
forma_caule: Cilíndrico | Quadrangular | Triangular | Achatado | Alado | Irregular
modificacao_caule: Cladódio | Espinho | Estolão | Gavinha | Rizoma | Tubérculo | Bulbo
diametro_caule: Capilar | Delgado | Fino | Médio | Grosso | Muito grosso
ramificacao_caule: Monopodial | Simpodial | Dicotômica | Pseudodicotômica
possui_espinhos: Sim | Não | Não informado
possui_latex: Sim | Não | Não informado
possui_seiva: Sim | Não | Não informado
possui_resina: Sim | Não | Não informado

---

ESTRUTURA DO JSON DE SAÍDA (preencha todos os campos):

{
  "especie_id": "' . $nome_cientifico . '",
  "nome_cientifico_completo": "",
  "nome_cientifico_completo_ref": "",
  "sinonimos": "",
  "sinonimos_ref": "",
  "nome_popular": "",
  "nome_popular_ref": "",
  "familia": "",
  "familia_ref": "",
  "forma_folha": "",
  "forma_folha_ref": "",
  "filotaxia_folha": "",
  "filotaxia_folha_ref": "",
  "tipo_folha": "",
  "tipo_folha_ref": "",
  "tamanho_folha": "",
  "tamanho_folha_ref": "",
  "textura_folha": "",
  "textura_folha_ref": "",
  "margem_folha": "",
  "margem_folha_ref": "",
  "venacao_folha": "",
  "venacao_folha_ref": "",
  "cor_flores": "",
  "cor_flores_ref": "",
  "simetria_floral": "",
  "simetria_floral_ref": "",
  "numero_petalas": "",
  "numero_petalas_ref": "",
  "disposicao_flores": "",
  "disposicao_flores_ref": "",
  "aroma": "",
  "aroma_ref": "",
  "tamanho_flor": "",
  "tamanho_flor_ref": "",
  "tipo_fruto": "",
  "tipo_fruto_ref": "",
  "tamanho_fruto": "",
  "tamanho_fruto_ref": "",
  "cor_fruto": "",
  "cor_fruto_ref": "",
  "textura_fruto": "",
  "textura_fruto_ref": "",
  "dispersao_fruto": "",
  "dispersao_fruto_ref": "",
  "aroma_fruto": "",
  "aroma_fruto_ref": "",
  "tipo_semente": "",
  "tipo_semente_ref": "",
  "tamanho_semente": "",
  "tamanho_semente_ref": "",
  "cor_semente": "",
  "cor_semente_ref": "",
  "textura_semente": "",
  "textura_semente_ref": "",
  "quantidade_sementes": "",
  "quantidade_sementes_ref": "",
  "tipo_caule": "",
  "tipo_caule_ref": "",
  "estrutura_caule": "",
  "estrutura_caule_ref": "",
  "textura_caule": "",
  "textura_caule_ref": "",
  "cor_caule": "",
  "cor_caule_ref": "",
  "forma_caule": "",
  "forma_caule_ref": "",
  "modificacao_caule": "",
  "modificacao_caule_ref": "",
  "diametro_caule": "",
  "diametro_caule_ref": "",
  "ramificacao_caule": "",
  "ramificacao_caule_ref": "",
  "possui_espinhos": "",
  "possui_espinhos_ref": "",
  "possui_latex": "",
  "possui_latex_ref": "",
  "possui_seiva": "",
  "possui_seiva_ref": "",
  "possui_resina": "",
  "possui_resina_ref": "",
  "referencias": ""
}'
);
                ?></div>
            </div>

            <!-- Cole o JSON -->
            <div class="json-import-area">
                <h3><span>📋</span> Cole aqui o JSON da espécie</h3>
                <p>Após obter o JSON do agente de IA, cole-o abaixo e clique em "Preencher Planilha". Revise cada campo antes de finalizar.</p>
                <textarea id="json_input" rows="8" placeholder='{
    "nome_cientifico_completo": "Mauritia flexuosa L.f.",
    "familia": "Arecaceae",
    "forma_folha": "Palmada",
    "cor_flores": "Amarelas",
    "referencias": "1. Flora Brasiliensis\n2. Lorenzi, 2002"
}'></textarea>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" id="btn_carregar_json">🔄 PREENCHER PLANILHA</button>
                    <button type="button" class="btn btn-secondary" id="btn_limpar">🗑️ LIMPAR</button>
                </div>
                <div id="mensagem_json" style="display:none;" class="alert"></div>

                <div id="btn_finalizar_wrapper" style="display:none; margin-top:1.5rem; text-align:center;">
                    <button type="button" class="btn-save" onclick="document.getElementById('btn_finalizar').click()">
                        ✅ FINALIZAR IMPORTAÇÃO
                    </button>
                    <p style="margin-top:8px; color:#666; font-size:0.85rem;">
                        ⚠️ Após finalizar, todas as imagens e dados serão salvos permanentemente.
                    </p>
                </div>
            </div>
        </div>

        <div class="footer">Penomato • Importação de dados - PASSO 3 DE 3</div>
    </div>
    
    <!-- MODAL DE VALIDAÇÃO -->
    <div class="modal-overlay" id="modalValidacao">
        <div class="modal-container">
            <div class="modal-header">
                <h2><span>⚠️</span> Validar termos não padronizados</h2>
                <span class="badge" id="modal-contador">0/0</span>
                <button class="modal-close" onclick="fecharModal()">&times;</button>
            </div>
            <div class="modal-body" id="modal-body"></div>
            <div class="modal-footer">
                <button class="btn-modal btn-modal-cancel" onclick="fecharModal()">CANCELAR</button>
                <button class="btn-modal btn-modal-confirm" id="btnConfirmarValidacao" onclick="confirmarValidacao()" disabled>CONFIRMAR ESCOLHAS</button>
            </div>
        </div>
    </div>
    
    <script>
    // ================================================
    // CONFIRM-BTNS: toggle e progresso visual
    // ================================================
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.confirm-btn');
        if (btn) btn.classList.toggle('confirmed');
    });

    // ================================================
    // PREENCHER campo visível (select ou input/textarea)
    // ================================================
    function preencherCampo(id, valor) {
        const el = document.getElementById(id);
        if (!el || valor === null || valor === undefined || valor === '') return;
        const v = String(valor).trim();
        if (!v) return;
        if (el.tagName === 'SELECT') {
            const opts = Array.from(el.options);
            let match = opts.find(o => o.value === v);
            if (!match) match = opts.find(o => o.text.trim() === v);
            if (!match) match = opts.find(o => o.value.startsWith(v) || v.startsWith(o.value));
            if (!match) match = opts.find(o => o.text.trim().startsWith(v) || v.startsWith(o.text.trim()));
            if (match) { el.value = match.value; el.classList.add('auto-filled'); }
        } else {
            el.value = v;
            el.classList.add('auto-filled');
        }
    }

    // ================================================
    // LIMPAR PLANILHA
    // ================================================
    function limparPlanilha() {
        document.querySelectorAll('#form_principal input[type="text"], #form_principal textarea').forEach(el => {
            el.value = ''; el.classList.remove('auto-filled');
        });
        document.querySelectorAll('#form_principal select').forEach(el => {
            el.value = ''; el.classList.remove('auto-filled');
            if (el.options[0] && el.options[0].disabled) el.selectedIndex = 0;
        });
        document.querySelectorAll('.confirm-btn').forEach(b => b.classList.remove('confirmed'));
    }

    // ================================================
    // CARREGAR JSON → preenche planilha visível
    // ================================================
    document.getElementById('btn_carregar_json').addEventListener('click', function() {
        const mensagemDiv = document.getElementById('mensagem_json');
        mensagemDiv.style.display = 'none';

        try {
            const jsonTexto = document.getElementById('json_input').value.trim();
            if (!jsonTexto) throw new Error('Nenhum JSON foi colado!');
            const dados = JSON.parse(jsonTexto);

            const todosCampos = [
                'nome_cientifico_completo','sinonimos','nome_popular','familia',
                'forma_folha','filotaxia_folha','tipo_folha','tamanho_folha',
                'textura_folha','margem_folha','venacao_folha',
                'cor_flores','simetria_floral','numero_petalas','disposicao_flores','aroma','tamanho_flor',
                'tipo_fruto','tamanho_fruto','cor_fruto','textura_fruto','dispersao_fruto','aroma_fruto',
                'tipo_semente','tamanho_semente','cor_semente','textura_semente','quantidade_sementes',
                'tipo_caule','estrutura_caule','textura_caule','cor_caule','forma_caule',
                'modificacao_caule','diametro_caule','ramificacao_caule',
                'possui_espinhos','possui_latex','possui_seiva','possui_resina','referencias',
                'nome_cientifico_completo_ref','sinonimos_ref','nome_popular_ref','familia_ref',
                'forma_folha_ref','filotaxia_folha_ref','tipo_folha_ref','tamanho_folha_ref',
                'textura_folha_ref','margem_folha_ref','venacao_folha_ref',
                'cor_flores_ref','simetria_floral_ref','numero_petalas_ref','disposicao_flores_ref',
                'aroma_ref','tamanho_flor_ref','tipo_fruto_ref','tamanho_fruto_ref','cor_fruto_ref',
                'textura_fruto_ref','dispersao_fruto_ref','aroma_fruto_ref',
                'tipo_semente_ref','tamanho_semente_ref','cor_semente_ref','textura_semente_ref',
                'quantidade_sementes_ref','tipo_caule_ref','estrutura_caule_ref','textura_caule_ref',
                'cor_caule_ref','forma_caule_ref','modificacao_caule_ref','diametro_caule_ref',
                'ramificacao_caule_ref','possui_espinhos_ref','possui_latex_ref',
                'possui_seiva_ref','possui_resina_ref'
            ];

            let preenchidos = 0;
            todosCampos.forEach(function(campo) {
                if (dados[campo] !== undefined && dados[campo] !== '') {
                    preencherCampo(campo, dados[campo]);
                    preenchidos++;
                }
            });

            // Scroll até a planilha
            document.getElementById('form_principal').scrollIntoView({ behavior: 'smooth', block: 'start' });

            mensagemDiv.style.display = 'block';
            mensagemDiv.className = 'alert alert-success';
            mensagemDiv.innerHTML = '✅ ' + preenchidos + ' campos preenchidos! Revise a planilha acima e clique em Finalizar Importação.';
            document.getElementById('btn_finalizar_wrapper').style.display = 'block';

        } catch (erro) {
            mensagemDiv.style.display = 'block';
            mensagemDiv.className = 'alert alert-danger';
            mensagemDiv.innerHTML = '❌ Erro: ' + erro.message;
        }
    });

    document.getElementById('btn_limpar').addEventListener('click', function() {
        if (confirm('Limpar o JSON e todos os campos da planilha?')) {
            document.getElementById('json_input').value = '';
            document.getElementById('mensagem_json').style.display = 'none';
            document.getElementById('btn_finalizar_wrapper').style.display = 'none';
            limparPlanilha();
        }
    });

    // ================================================
    // COPIAR PROMPT
    // ================================================
    function copiarPrompt() {
        const texto = document.getElementById('prompt-display').textContent;
        const btn   = document.getElementById('btn_copiar_prompt');
        navigator.clipboard.writeText(texto).then(function() {
            btn.textContent = '✅ Copiado!';
            btn.classList.add('copied');
            setTimeout(function() { btn.textContent = '📋 Copiar Prompt'; btn.classList.remove('copied'); }, 2500);
        }).catch(function() {
            const ta = document.createElement('textarea');
            ta.value = texto; ta.style.position = 'fixed'; ta.style.opacity = '0';
            document.body.appendChild(ta); ta.select(); document.execCommand('copy');
            document.body.removeChild(ta);
            btn.textContent = '✅ Copiado!'; btn.classList.add('copied');
            setTimeout(function() { btn.textContent = '📋 Copiar Prompt'; btn.classList.remove('copied'); }, 2500);
        });
    }

    window.onclick = function(event) {
        const modal = document.getElementById('modalValidacao');
        if (modal && event.target === modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    };
    </script>
</body>
</html>