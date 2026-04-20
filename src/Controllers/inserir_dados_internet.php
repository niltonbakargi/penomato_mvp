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

require_once __DIR__ . '/../../config/app.php';
$servidor   = DB_HOST;
$usuario_db = DB_USER;
$senha_db   = DB_PASS;
$banco      = DB_NAME;

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
    'forma_folha'       => ['Acicular','Cordiforme','Elíptica','Lanceolada','Linear','Lobada','Obovada','Orbicular','Ovada','Palmada','Reniforme','Sagitada','Trifoliada'],
    'filotaxia_folha'   => ['Alterna','Alterna dística','Alterna espiralada','Oposta','Oposta decussada','Verticilada'],
    'tipo_folha'        => ['Simples','Composta bipinada','Composta digitada','Composta imparipinada','Composta paripinada','Composta pinnada','Composta trifoliada','Composta tripinada'],
    'tamanho_folha'     => ['Microfila','Nanofila','Mesofila','Macrofila','Megafila'],
    'textura_folha'     => ['Cartácea','Coriácea','Glabra','Membranácea','Pilosa','Pubescente','Rugosa','Suculenta','Tomentosa','Cerosa'],
    'margem_folha'      => ['Crenada','Dentada','Inteira','Lobada','Ondulada','Serreada','Serrilhada','Partida'],
    'venacao_folha'     => ['Curvinérvea','Dicotômica','Paralela','Peninérvea','Reticulada palmada','Reticulada pinada'],
    'cor_flores'        => ['Alaranjada','Amarela','Avermelhada','Azul','Branca','Esverdeada','Lilás','Púrpura','Rósea','Roxa','Vermelha','Vinácea'],
    'simetria_floral'   => ['Actinomorfa','Zigomorfa','Assimétrica'],
    'numero_petalas'    => ['3 pétalas','4 pétalas','5 pétalas','6 pétalas','Muitas pétalas','Ausentes'],
    'disposicao_flores' => ['Solitária','Capítulo','Cacho','Corimbo','Espádice','Espiga','Panícula','Umbela'],
    'aroma'             => ['Ausente','Suave','Forte','Desagradável','Adocicada','Cítrica'],
    'tamanho_flor'      => ['Muito pequena','Pequena','Média','Grande','Muito grande'],
    'tipo_fruto'        => ['Aquênio','Baga','Cápsula','Drupa','Folículo','Legume','Pixídio','Sâmara','Síliqua','Cariopse','Hespéridio','Pepo'],
    'tamanho_fruto'     => ['Minúsculo','Pequeno','Médio','Grande','Muito grande'],
    'cor_fruto'         => ['Alaranjado','Amarelo','Avermelhado','Branco','Esverdeado','Marrom','Preto','Roxo','Verde','Vináceo'],
    'textura_fruto'     => ['Lisa','Rugosa','Coriácea','Pubescente','Pilosa','Espinhosa','Cerosa','Tuberculada'],
    'dispersao_fruto'   => ['Anemocórica','Autocórica','Hidrocórica','Zoocórica','Mirmecocórica','Ornitocórica'],
    'aroma_fruto'       => ['Ausente','Suave','Forte','Adocicado','Cítrico','Desagradável'],
    'tipo_semente'      => ['Alada','Carnosa','Dura','Oleaginosa','Plumosa','Ruminada','Arilada'],
    'tamanho_semente'   => ['Minúscula','Muito pequena','Pequena','Média','Grande','Muito grande'],
    'cor_semente'       => ['Amarela','Branca','Castanha','Cinza','Marrom','Preta','Vermelha','Alaranjada'],
    'textura_semente'   => ['Lisa','Rugosa','Estriada','Pontuada','Foveolada','Reticulada','Tuberculada'],
    'quantidade_sementes' => ['1','2–3','4–10','11–50','> 50'],
    'tipo_caule'        => ['Ereto','Prostrado','Escandente','Trepador','Rastejante','Subterrâneo'],
    'estrutura_caule'   => ['Herbáceo','Lenhoso','Suculento','Sublenhoso'],
    'textura_caule'     => ['Lisa','Rugosa','Fissurada','Sulcada','Estriada','Escamosa','Suberosa','Aculeada'],
    'cor_caule'         => ['Acinzentado','Alaranjado','Avermelhado','Esbranquiçado','Esverdeado','Marrom','Pardacento'],
    'forma_caule'       => ['Cilíndrico','Quadrangular','Triangular','Achatado','Alado','Irregular'],
    'modificacao_caule' => ['Cladódio','Espinho','Estolão','Gavinha','Rizoma','Tubérculo','Bulbo'],
    'diametro_caule'    => ['Capilar','Delgado','Fino','Médio','Grosso','Muito grosso'],
    'ramificacao_caule' => ['Monopodial','Simpodial','Dicotômica','Pseudodicotômica'],
    'possui_espinhos'   => ['Sim','Não','Não informado'],
    'possui_latex'      => ['Sim','Não','Não informado'],
    'possui_seiva'      => ['Sim','Não','Não informado'],
    'possui_resina'     => ['Sim','Não','Não informado'],
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
    <link rel="stylesheet" href="../../assets/css/estilo.css">
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
        /* ── Botão IA ── */
        .ia-toolbar {
            display: flex; align-items: center; justify-content: flex-end;
            gap: 10px; margin-bottom: 16px;
        }
        #ia_status { margin-top: 8px; }
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

            <div class="ia-toolbar">
                <button type="button" id="btn_pesquisar_ia" class="btn btn-primary">
                    🤖 Pesquisar com IA
                </button>
            </div>
            <div id="ia_status" style="display:none;" class="alert"></div>

            <div id="form_wrapper" style="display:none;">

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
                            <option>Acicular</option><option>Cordiforme</option><option>Elíptica</option>
                            <option>Lanceolada</option><option>Linear</option><option>Lobada</option>
                            <option>Obovada</option><option>Orbicular</option><option>Ovada</option>
                            <option>Palmada</option><option>Reniforme</option><option>Sagitada</option><option>Trifoliada</option>
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
                            <option>Alterna</option><option>Alterna dística</option><option>Alterna espiralada</option>
                            <option>Oposta</option><option>Oposta decussada</option><option>Verticilada</option>
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
                            <option>Simples</option><option>Composta bipinada</option><option>Composta digitada</option>
                            <option>Composta imparipinada</option><option>Composta paripinada</option>
                            <option>Composta pinnada</option><option>Composta trifoliada</option><option>Composta tripinada</option>
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
                            <option>Microfila</option><option>Nanofila</option><option>Mesofila</option>
                            <option>Macrofila</option><option>Megafila</option>
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
                            <option>Cartácea</option><option>Coriácea</option><option>Glabra</option>
                            <option>Membranácea</option><option>Pilosa</option><option>Pubescente</option>
                            <option>Rugosa</option><option>Suculenta</option><option>Tomentosa</option><option>Cerosa</option>
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
                            <option>Crenada</option><option>Dentada</option><option>Inteira</option>
                            <option>Lobada</option><option>Ondulada</option><option>Serreada</option>
                            <option>Serrilhada</option><option>Partida</option>
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
                            <option>Curvinérvea</option><option>Dicotômica</option><option>Paralela</option>
                            <option>Peninérvea</option><option>Reticulada palmada</option><option>Reticulada pinada</option>
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
                            <option>Alaranjada</option><option>Amarela</option><option>Avermelhada</option>
                            <option>Azul</option><option>Branca</option><option>Esverdeada</option>
                            <option>Lilás</option><option>Púrpura</option><option>Rósea</option>
                            <option>Roxa</option><option>Vermelha</option><option>Vinácea</option>
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
                            <option>3 pétalas</option><option>4 pétalas</option><option>5 pétalas</option>
                            <option>6 pétalas</option><option>Muitas pétalas</option><option>Ausentes</option>
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
                            <option>Solitária</option><option>Capítulo</option><option>Cacho</option>
                            <option>Corimbo</option><option>Espádice</option><option>Espiga</option>
                            <option>Panícula</option><option>Umbela</option>
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
                            <option>Ausente</option><option>Suave</option><option>Forte</option>
                            <option>Desagradável</option><option>Adocicada</option><option>Cítrica</option>
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
                            <option>Muito pequena</option><option>Pequena</option><option>Média</option>
                            <option>Grande</option><option>Muito grande</option>
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
                            <option>Minúsculo</option><option>Pequeno</option><option>Médio</option>
                            <option>Grande</option><option>Muito grande</option>
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
                            <option>Alaranjado</option><option>Amarelo</option><option>Avermelhado</option>
                            <option>Branco</option><option>Esverdeado</option><option>Marrom</option>
                            <option>Preto</option><option>Roxo</option><option>Verde</option><option>Vináceo</option>
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
                            <option>Pubescente</option><option>Pilosa</option><option>Espinhosa</option>
                            <option>Cerosa</option><option>Tuberculada</option>
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
                            <option>Anemocórica</option><option>Autocórica</option><option>Hidrocórica</option>
                            <option>Zoocórica</option><option>Mirmecocórica</option><option>Ornitocórica</option>
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
                            <option>Ausente</option><option>Suave</option><option>Forte</option>
                            <option>Adocicado</option><option>Cítrico</option><option>Desagradável</option>
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
                            <option>Oleaginosa</option><option>Plumosa</option><option>Ruminada</option><option>Arilada</option>
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
                            <option>Minúscula</option><option>Muito pequena</option><option>Pequena</option>
                            <option>Média</option><option>Grande</option><option>Muito grande</option>
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
                            <option>Amarela</option><option>Branca</option><option>Castanha</option>
                            <option>Cinza</option><option>Marrom</option><option>Preta</option>
                            <option>Vermelha</option><option>Alaranjada</option>
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
                            <option>Lisa</option><option>Rugosa</option><option>Estriada</option>
                            <option>Pontuada</option><option>Foveolada</option><option>Reticulada</option><option>Tuberculada</option>
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
                            <option>1</option><option>2–3</option><option>4–10</option>
                            <option>11–50</option><option>&gt; 50</option>
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
                            <option>Ereto</option><option>Prostrado</option><option>Escandente</option>
                            <option>Trepador</option><option>Rastejante</option><option>Subterrâneo</option>
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
                            <option>Herbáceo</option><option>Lenhoso</option><option>Suculento</option><option>Sublenhoso</option>
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
                            <option>Lisa</option><option>Rugosa</option><option>Fissurada</option>
                            <option>Sulcada</option><option>Estriada</option><option>Escamosa</option>
                            <option>Suberosa</option><option>Aculeada</option>
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
                            <option>Acinzentado</option><option>Alaranjado</option><option>Avermelhado</option>
                            <option>Esbranquiçado</option><option>Esverdeado</option><option>Marrom</option><option>Pardacento</option>
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
                            <option>Cilíndrico</option><option>Quadrangular</option><option>Triangular</option>
                            <option>Achatado</option><option>Alado</option><option>Irregular</option>
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
                            <option>Cladódio</option><option>Espinho</option><option>Estolão</option>
                            <option>Gavinha</option><option>Rizoma</option><option>Tubérculo</option><option>Bulbo</option>
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
                            <option>Capilar</option><option>Delgado</option><option>Fino</option>
                            <option>Médio</option><option>Grosso</option><option>Muito grosso</option>
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
                            <option>Monopodial</option><option>Simpodial</option>
                            <option>Dicotômica</option><option>Pseudodicotômica</option>
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
                            <option value="" disabled selected>Selecione…</option>
                            <option>Sim</option><option>Não</option><option>Não informado</option>
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
                            <option value="" disabled selected>Selecione…</option>
                            <option>Sim</option><option>Não</option><option>Não informado</option>
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
                            <option value="" disabled selected>Selecione…</option>
                            <option>Sim</option><option>Não</option><option>Não informado</option>
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
                            <option value="" disabled selected>Selecione…</option>
                            <option>Sim</option><option>Não</option><option>Não informado</option>
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
            </div><!-- /#form_wrapper -->
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
    // PESQUISAR COM IA — fetch para buscar_dados_especie_ai.php
    // ================================================
    (function() {
        const btnIA = document.getElementById('btn_pesquisar_ia');
        if (!btnIA) return;

        btnIA.addEventListener('click', function() {
            const statusDiv = document.getElementById('ia_status');
            btnIA.disabled = true;
            btnIA.textContent = '⏳ Pesquisando...';
            statusDiv.style.display = 'none';

            const fd = new FormData();
            fd.append('temp_id', '<?php echo addslashes($temp_id); ?>');

            fetch('buscar_dados_especie_ai.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(resp) {
                btnIA.disabled = false;
                btnIA.textContent = '🤖 Pesquisar com IA';

                if (!resp.sucesso) {
                    statusDiv.style.display = 'block';
                    statusDiv.className = 'alert alert-danger';
                    statusDiv.textContent = '❌ ' + resp.erro;
                    return;
                }

                const validos = resp.campos_validos || {};
                let preenchidos = 0;
                Object.keys(validos).forEach(function(campo) {
                    preencherCampo(campo, validos[campo]);
                    preenchidos++;
                });

                // Revelar formulário
                document.getElementById('form_wrapper').style.display = 'block';

                if (resp.campos_divergentes && resp.campos_divergentes.length > 0) {
                    abrirModalDivergentes(resp.campos_divergentes);
                } else {
                    statusDiv.style.display = 'block';
                    statusDiv.className = 'alert alert-success';
                    statusDiv.textContent = '✅ ' + preenchidos + ' campos preenchidos! Revise e finalize.';
                    document.getElementById('form_wrapper').scrollIntoView({ behavior: 'smooth' });
                }
            })
            .catch(function(err) {
                btnIA.disabled = false;
                btnIA.textContent = '🤖 Pesquisar com IA';
                statusDiv.style.display = 'block';
                statusDiv.className = 'alert alert-danger';
                statusDiv.textContent = '❌ Erro de rede: ' + err.message;
            });
        });
    })();

    // ================================================
    // MODAL DE DIVERGÊNCIAS
    // ================================================
    var _divergentes = [];
    var _escolhas    = {};

    function abrirModalDivergentes(divergentes) {
        _divergentes = divergentes;
        _escolhas    = {};

        const body     = document.getElementById('modal-body');
        const contador = document.getElementById('modal-contador');
        body.innerHTML = '';

        divergentes.forEach(function(item, idx) {
            const nome = item.campo.replace(/_/g, ' ')
                             .replace(/\b\w/g, function(c) { return c.toUpperCase(); });

            var opcoesHtml = '<div class="opcoes-grid">';
            item.opcoes.forEach(function(opcao) {
                var sugerida = (opcao === item.sugestao) ? ' style="font-weight:700;"' : '';
                opcoesHtml += '<label class="opcao-item"' + sugerida + '>'
                    + '<input type="radio" name="modal_campo_' + idx + '" value="' + opcao + '"'
                    + (opcao === item.sugestao ? ' checked' : '') + '> ' + opcao
                    + '</label>';
            });
            opcoesHtml += '</div>';

            _escolhas[item.campo] = item.sugestao;

            const bloco = document.createElement('div');
            bloco.className = 'campo-validacao';
            bloco.innerHTML = '<div class="campo-titulo">⚠️ ' + nome + '</div>'
                + '<div class="campo-json">IA retornou: <strong>' + item.valor_ia + '</strong>'
                + ' — sugestão: <strong>' + item.sugestao + '</strong></div>'
                + opcoesHtml;

            bloco.querySelectorAll('input[type=radio]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    _escolhas[item.campo] = this.value;
                    atualizarContadorModal();
                });
            });

            body.appendChild(bloco);
        });

        atualizarContadorModal();
        const modal = document.getElementById('modalValidacao');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function atualizarContadorModal() {
        const total   = _divergentes.length;
        const prontos = Object.keys(_escolhas).length;
        document.getElementById('modal-contador').textContent = prontos + '/' + total;
        document.getElementById('btnConfirmarValidacao').disabled = (prontos < total);
    }

    function fecharModal() {
        document.getElementById('modalValidacao').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function confirmarValidacao() {
        Object.keys(_escolhas).forEach(function(campo) {
            preencherCampo(campo, _escolhas[campo]);
        });
        fecharModal();
        const statusDiv = document.getElementById('ia_status');
        statusDiv.style.display = 'block';
        statusDiv.className = 'alert alert-success';
        statusDiv.textContent = '✅ Campos preenchidos e divergências resolvidas! Revise e finalize.';
        document.getElementById('form_wrapper').scrollIntoView({ behavior: 'smooth' });
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