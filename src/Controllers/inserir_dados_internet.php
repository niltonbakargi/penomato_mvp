<?php
// ================================================
// INSERIR DADOS INTERNET - VERSÃO COM CONTROLE DE USUÁRIO
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
// PROCESSAR O ENVIO DO FORMULÁRIO
// ================================================
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_caracteristicas'])) {
    
    $conexao = new mysqli($servidor, $usuario_db, $senha_db, $banco);
    
    if ($conexao->connect_error) {
        $erro = "Erro de conexão: " . $conexao->connect_error;
    } else {
        
        $conexao->set_charset("utf8mb4");
        $especie_id = isset($_POST['especie_id']) ? (int)$_POST['especie_id'] : 0;
        
        if ($especie_id <= 0) {
            $erro = "Selecione uma espécie válida!";
        } else {
            
            // VERIFICAR SE A ESPÉCIE ESTÁ COM STATUS PERMITIDO
            $sql_status = "SELECT status FROM especies_administrativo WHERE id = ?";
            $stmt_status = $conexao->prepare($sql_status);
            $stmt_status->bind_param("i", $especie_id);
            $stmt_status->execute();
            $resultado_status = $stmt_status->get_result();
            
            if ($resultado_status->num_rows === 0) {
                $erro = "Espécie não encontrada no banco de dados!";
            } else {
                $row = $resultado_status->fetch_assoc();
                $status_atual = $row['status'];
                
                if (!in_array($status_atual, ['sem_dados', 'dados_internet'])) {
                    $erro = "Espécie com status '$status_atual' não permite novos cadastros.";
                } else {
                    
                    // ================================================
                    // DADOS VALIDADOS - ARMAZENAR NA SESSÃO
                    // ================================================
                    
                    // Construir array de campos a partir do POST
                    $dados_caracteristicas = [];
                    $todos_campos = [
                        'especie_id' => $especie_id,
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
                    
                    // Pegar apenas os campos que existem no POST
                    foreach ($todos_campos as $campo) {
                        if (is_array($campo)) {
                            continue;
                        }
                        if (isset($_POST[$campo]) && $_POST[$campo] !== '') {
                            $dados_caracteristicas[$campo] = $_POST[$campo];
                        }
                    }
                    
                    // Adicionar especie_id
                    $dados_caracteristicas['especie_id'] = $especie_id;
                    
                    // ================================================
                    // GERAR ID ÚNICO PARA A SESSÃO TEMPORÁRIA
                    // ================================================
                    $temp_id = uniqid('temp_', true);
                    
                    // Armazenar na sessão com ID do usuário
                    $_SESSION['importacao_temporaria'] = [
                        'temp_id' => $temp_id,
                        'especie_id' => $especie_id,
                        'usuario_id' => $id_usuario,
                        'dados' => $dados_caracteristicas,
                        'imagens' => [],
                        'data_criacao' => time()
                    ];
                    
                    // ================================================
                    // REDIRECIONAR PARA UPLOAD DE IMAGENS COM ID TEMPORÁRIO
                    // ================================================
                    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . $temp_id);
                    exit;
                }
            }
            $stmt_status->close();
        }
        
        $conexao->close();
    }
}

// ================================================
// BUSCAR ESPÉCIES PARA O SELECT
// ================================================
$conexao = new mysqli($servidor, $usuario_db, $senha_db, $banco);
$opcoes_especies = [];
$opcoes_especies_html = '';

if (!$conexao->connect_error) {
    $sql = "SELECT id, nome_cientifico, status 
            FROM especies_administrativo 
            WHERE status IN ('sem_dados', 'dados_internet')
            ORDER BY 
                CASE status 
                    WHEN 'dados_internet' THEN 1
                    WHEN 'sem_dados' THEN 2
                END,
                nome_cientifico";
    
    $resultado = $conexao->query($sql);
    
    if ($resultado && $resultado->num_rows > 0) {
        while ($linha = $resultado->fetch_assoc()) {
            $id_especie = htmlspecialchars($linha['id']);
            $nome_cientifico = htmlspecialchars($linha['nome_cientifico']);
            $status = htmlspecialchars($linha['status']);
            
            $opcoes_especies[$nome_cientifico] = [
                'id' => $id_especie,
                'status' => $status
            ];
            
            $badge = $status === 'dados_internet' ? ' (parcial)' : '';
            $opcoes_especies_html .= "<option value=\"{$id_especie}\">{$nome_cientifico}{$badge}</option>\n";
        }
    }
    
    $conexao->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penomato - Importação de Dados Científicos</title>
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
            max-width: 900px;
            margin: 0 auto;
            position: relative;
        }
        
        .paper-header {
            background: white;
            padding: 30px 40px;
            border-radius: 12px 12px 0 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-bottom: 4px solid #0b5e42;
            margin-bottom: 5px;
        }
        
        .paper-header h1 {
            color: #0b5e42;
            font-size: 2rem;
            font-weight: 500;
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
        
        .paper-card {
            background: white;
            padding: 30px 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .json-import-area {
            background-color: #f8fafc;
            border: 2px dashed #0b5e42;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .json-import-area h3 {
            color: #0b5e42;
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
            border-color: #0b5e42;
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
            background-color: #0b5e42;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0a4c35;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #e2e8f0;
            color: #2d3748;
        }
        
        .btn-secondary:hover {
            background-color: #cbd5e0;
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
            background-color: #0b5e42;
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
            border-left: 4px solid #0b5e42;
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
            color: #0b5e42;
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
            border-color: #0b5e42;
        }
        
        .opcao-item.selecionado {
            border-color: #0b5e42;
            background-color: #e6f7e6;
            font-weight: 500;
        }
        
        .opcao-item.sugerida {
            border-left: 4px solid #0b5e42;
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
            background-color: #0b5e42;
            color: white;
        }
        
        .btn-modal-confirm:hover:not(:disabled) {
            background-color: #0a4c35;
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
            color: #0b5e42;
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
            color: #0b5e42;
            display: block;
            margin-bottom: 5px;
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        
        .section-divider {
            font-size: 1.3rem;
            color: #0b5e42;
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
            color: #0b5e42;
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
            border-top: 3px solid #0b5e42;
        }
        
        .references-title {
            font-size: 1.1rem;
            color: #0b5e42;
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
            color: #0b5e42;
            font-size: 0.85rem;
        }
        
        .species-selector {
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 2px solid #e2e8f0;
        }
        
        .species-selector label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 10px;
        }
        
        .species-selector select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            margin-top: 8px;
            font-size: 1rem;
            background-color: white;
        }
        
        .species-selector select:focus {
            outline: none;
            border-color: #0b5e42;
        }
        
        .auto-select-info {
            margin-top: 10px;
            padding: 12px;
            border-radius: 6px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            border-left: 4px solid;
        }
        
        .auto-select-info.success {
            background-color: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }
        
        .auto-select-info.warning {
            background-color: #fff3cd;
            color: #856404;
            border-left-color: #ffc107;
        }
        
        .auto-select-info.error {
            background-color: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
        
        .save-button-container {
            text-align: center;
            margin: 30px 0;
        }
        
        .btn-save {
            background-color: #0b5e42;
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
            background-color: #0a4c35;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(11,94,66,0.3);
        }
        
        .btn-save:disabled {
            background-color: #cbd5e0;
            cursor: not-allowed;
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
        
        <div class="paper-header">
            <h1>📄 PENOMATO • IMPORTAR DADOS</h1>
            <div class="subtitle">
                Cole o JSON da pesquisa. A espécie será identificada automaticamente.
            </div>
        </div>
        
        <?php if (isset($erro)): ?>
            <div class="alert alert-danger">❌ <?php echo $erro; ?></div>
        <?php endif; ?>
        
        <div class="paper-card">
            
            <div class="json-import-area">
                <h3>
                    <span>📋</span>
                    PASSO 1 • Cole o JSON
                </h3>
                <textarea id="json_input" rows="6" placeholder='Cole o JSON aqui...'></textarea>
                
                <div class="btn-group">
                    <button type="button" class="btn btn-primary" id="btn_carregar_json">
                        🔄 VALIDAR E PROCESSAR
                    </button>
                    <button type="button" class="btn btn-secondary" id="btn_limpar">
                        🗑️ LIMPAR
                    </button>
                </div>
                
                <div id="mensagem_json" style="display: none;" class="alert"></div>
            </div>
            
            <form method="POST" action="" id="form_principal">
                
                <div class="species-selector">
                    <label>
                        <span>🔍</span>
                        PASSO 2 • Espécie no banco de dados
                    </label>
                    <select id="especie_id" name="especie_id" required>
                        <option value="" disabled selected>— Selecione uma espécie —</option>
                        <?php echo $opcoes_especies_html; ?>
                    </select>
                    <div id="auto-select-info" class="auto-select-info" style="display: none;"></div>
                </div>
                
                <div class="technical-paper" id="artigo_visualizacao">
                    <div class="species-title" id="preview_nome_cientifico">[Nome científico]</div>
                    
                    <div class="info-grid">
                        <div class="info-item"><strong>Família</strong><span id="preview_familia">—</span></div>
                        <div class="info-item"><strong>Nomes populares</strong><span id="preview_nome_popular">—</span></div>
                        <div class="info-item"><strong>Sinônimos</strong><span id="preview_sinonimos">—</span></div>
                        <div class="info-item"><strong>Autor</strong><span id="preview_autor">—</span></div>
                    </div>
                    
                    <div id="preview_secoes"></div>
                    
                    <div class="references-section">
                        <div class="references-title">📚 Referências</div>
                        <ul class="reference-list" id="preview_referencias">
                            <li>Nenhuma referência</li>
                        </ul>
                    </div>
                </div>
                
                <div id="campos_ocultos"></div>
                
                <div class="save-button-container">
                    <button type="submit" name="salvar_caracteristicas" value="1" class="btn-save" id="btn_salvar" disabled>
                        💾 PREPARAR PARA UPLOAD DE IMAGENS
                    </button>
                </div>
                
                <p style="text-align: center; margin-top: 10px; color: #666; font-size: 0.9rem;">
                    ⚠️ Nenhum dado será salvo ainda. Após preparar, você fará o upload das imagens e tudo será salvo junto.
                </p>
            </form>
        </div>
        
        <div class="footer">
            Penomato • Dados temporários até finalização com imagens
        </div>
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
    // VARIÁVEIS GLOBAIS
    // ================================================
    let dadosJson = null;
    let camposParaValidar = [];
    let opcoesValidas = <?php echo json_encode($opcoes_validas); ?>;
    let escolhasUsuario = {};
    let especiesDisponiveis = <?php echo json_encode($opcoes_especies); ?>;
    
    // ================================================
    // FUNÇÕES DO MODAL
    // ================================================
    function abrirModal() {
        document.getElementById('modalValidacao').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    function fecharModal() {
        document.getElementById('modalValidacao').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    function atualizarContador() {
        const total = camposParaValidar.length;
        const resolvidos = Object.keys(escolhasUsuario).length;
        document.getElementById('modal-contador').innerHTML = `${resolvidos}/${total}`;
        document.getElementById('btnConfirmarValidacao').disabled = resolvidos !== total;
    }
    
    function selecionarOpcao(campo, valor) {
        escolhasUsuario[campo] = valor;
        
        document.querySelectorAll(`.opcao-item[data-campo="${campo}"]`).forEach(el => {
            if (el.dataset.valor === valor) {
                el.classList.add('selecionado');
                el.querySelector('input[type="radio"]').checked = true;
            } else {
                el.classList.remove('selecionado');
            }
        });
        
        atualizarContador();
    }
    
    function gerarModalValidacao() {
        const modalBody = document.getElementById('modal-body');
        let html = '';
        
        camposParaValidar.forEach(campo => {
            const valorJson = dadosJson[campo] || '';
            const opcoes = opcoesValidas[campo] || [];
            
            let sugestao = '';
            const valorLower = valorJson.toLowerCase();
            
            for (let opcao of opcoes) {
                if (valorLower.includes(opcao.toLowerCase())) {
                    sugestao = opcao;
                    break;
                }
            }
            
            if (!sugestao && opcoes.length > 0) {
                sugestao = opcoes[0];
            }
            
            if (!escolhasUsuario[campo] && sugestao) {
                escolhasUsuario[campo] = sugestao;
            }
            
            html += `<div class="campo-validacao">`;
            html += `<div class="campo-titulo"><span>🔍</span> ${formatarNomeCampo(campo)}</div>`;
            html += `<div class="campo-json">JSON: "${valorJson}"</div>`;
            html += `<div class="opcoes-grid">`;
            
            opcoes.forEach(opcao => {
                const selecionado = (escolhasUsuario[campo] === opcao) ? 'selecionado' : '';
                const checked = (escolhasUsuario[campo] === opcao) ? 'checked' : '';
                const sugerida = (opcao === sugestao && !escolhasUsuario[campo]) ? 'sugerida' : '';
                
                html += `<div class="opcao-item ${selecionado} ${sugerida}" data-campo="${campo}" data-valor="${opcao}" onclick="selecionarOpcao('${campo}', '${opcao}')">`;
                html += `<input type="radio" name="${campo}" value="${opcao}" ${checked} onchange="selecionarOpcao('${campo}', '${opcao}')">`;
                html += `<span>${opcao}</span>`;
                html += `</div>`;
            });
            
            html += `</div></div>`;
        });
        
        modalBody.innerHTML = html;
        atualizarContador();
    }
    
    function formatarNomeCampo(campo) {
        return campo.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }
    
    // ================================================
    // SELECIONAR ESPÉCIE AUTOMATICAMENTE
    // ================================================
    function selecionarEspecieAutomatica(nomeCientifico) {
        const select = document.getElementById('especie_id');
        const infoDiv = document.getElementById('auto-select-info');
        
        select.value = '';
        infoDiv.style.display = 'none';
        infoDiv.className = 'auto-select-info';
        
        if (!nomeCientifico) {
            infoDiv.style.display = 'flex';
            infoDiv.className = 'auto-select-info warning';
            infoDiv.innerHTML = '⚠️ JSON não contém "especie_id". Selecione manualmente.';
            return false;
        }
        
        for (let especie in especiesDisponiveis) {
            if (especie.toLowerCase() === nomeCientifico.toLowerCase()) {
                select.value = especiesDisponiveis[especie].id;
                infoDiv.style.display = 'flex';
                infoDiv.className = 'auto-select-info success';
                infoDiv.innerHTML = `✅ Espécie "${especie}" selecionada automaticamente`;
                return true;
            }
        }
        
        for (let especie in especiesDisponiveis) {
            if (nomeCientifico.toLowerCase().startsWith(especie.toLowerCase())) {
                select.value = especiesDisponiveis[especie].id;
                infoDiv.style.display = 'flex';
                infoDiv.className = 'auto-select-info success';
                infoDiv.innerHTML = `✅ Espécie "${especie}" selecionada (baseado em: ${nomeCientifico})`;
                return true;
            }
        }
        
        infoDiv.style.display = 'flex';
        infoDiv.className = 'auto-select-info error';
        infoDiv.innerHTML = `❌ Espécie "${nomeCientifico}" não encontrada no banco.`;
        return false;
    }
    
    // ================================================
    // GERAR CAMPOS OCULTOS PARA O FORMULÁRIO
    // ================================================
    function gerarCamposOcultos() {
        if (!dadosJson) return;
        
        const container = document.getElementById('campos_ocultos');
        container.innerHTML = '';
        
        const todosCampos = [
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
        
        todosCampos.forEach(campo => {
            if (dadosJson[campo] && dadosJson[campo] !== '') {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = campo;
                input.value = dadosJson[campo];
                container.appendChild(input);
            }
        });
    }
    
    // ================================================
    // ATUALIZAR VISUALIZAÇÃO DO ARTIGO
    // ================================================
    function atualizarVisualizacaoArtigo() {
        if (!dadosJson) return;
        
        document.getElementById('preview_nome_cientifico').innerHTML = dadosJson.nome_cientifico_completo || '[Nome não informado]';
        document.getElementById('preview_familia').innerHTML = dadosJson.familia || '—';
        document.getElementById('preview_nome_popular').innerHTML = dadosJson.nome_popular || '—';
        document.getElementById('preview_sinonimos').innerHTML = dadosJson.sinonimos || '—';
        document.getElementById('preview_autor').innerHTML = (dadosJson.nome_cientifico_completo || '').replace(/^[^(]+/, '') || '—';
        
        const secoesContainer = document.getElementById('preview_secoes');
        secoesContainer.innerHTML = '';
        
        const secoes = [
            { titulo: 'Folhas', icone: '🍃', campos: ['forma_folha', 'filotaxia_folha', 'tipo_folha', 'tamanho_folha', 'textura_folha', 'margem_folha', 'venacao_folha'] },
            { titulo: 'Flores', icone: '🌸', campos: ['cor_flores', 'simetria_floral', 'numero_petalas', 'disposicao_flores', 'aroma', 'tamanho_flor'] },
            { titulo: 'Frutos', icone: '🍎', campos: ['tipo_fruto', 'tamanho_fruto', 'cor_fruto', 'textura_fruto', 'dispersao_fruto', 'aroma_fruto'] },
            { titulo: 'Sementes', icone: '🌱', campos: ['tipo_semente', 'tamanho_semente', 'cor_semente', 'textura_semente', 'quantidade_sementes'] },
            { titulo: 'Caule', icone: '🌿', campos: ['tipo_caule', 'estrutura_caule', 'textura_caule', 'cor_caule', 'forma_caule', 'modificacao_caule', 'diametro_caule', 'ramificacao_caule'] },
            { titulo: 'Outras', icone: '⚡', campos: ['possui_espinhos', 'possui_latex', 'possui_seiva', 'possui_resina'] }
        ];
        
        secoes.forEach(secao => {
            const temCampos = secao.campos.some(campo => dadosJson[campo] && dadosJson[campo] !== '');
            
            if (temCampos) {
                let secaoHtml = `<div class="section-divider"><span>${secao.icone}</span> ${secao.titulo}</div>`;
                secaoHtml += `<div class="characteristic-block">`;
                
                secao.campos.forEach(campo => {
                    if (dadosJson[campo] && dadosJson[campo] !== '') {
                        const ref = dadosJson[`${campo}_ref`] ? `<span class="ref-note">[${dadosJson[`${campo}_ref`]}]</span>` : '';
                        
                        secaoHtml += `<div class="characteristic-item">`;
                        secaoHtml += `<span class="characteristic-label">${formatarNomeCampo(campo)}</span>`;
                        secaoHtml += `<span class="characteristic-value">${dadosJson[campo]} ${ref}</span>`;
                        secaoHtml += `</div>`;
                    }
                });
                
                secaoHtml += `</div>`;
                secoesContainer.innerHTML += secaoHtml;
            }
        });
        
        const refList = document.getElementById('preview_referencias');
        refList.innerHTML = '';
        
        if (dadosJson.referencias && dadosJson.referencias.trim() !== '') {
            const linhas = dadosJson.referencias.split('\n');
            linhas.forEach(linha => {
                if (linha.trim()) {
                    const li = document.createElement('li');
                    li.innerHTML = linha;
                    refList.appendChild(li);
                }
            });
        } else {
            refList.innerHTML = '<li>Nenhuma referência fornecida</li>';
        }
        
        gerarCamposOcultos();
    }
    
    function confirmarValidacao() {
        for (let campo in escolhasUsuario) {
            dadosJson[campo] = escolhasUsuario[campo];
        }
        
        atualizarVisualizacaoArtigo();
        fecharModal();
        
        const especieId = document.getElementById('especie_id').value;
        if (especieId && especieId !== '') {
            document.getElementById('btn_salvar').disabled = false;
        }
    }
    
    document.getElementById('btn_carregar_json').addEventListener('click', function() {
        const jsonInput = document.getElementById('json_input');
        const mensagemDiv = document.getElementById('mensagem_json');
        
        mensagemDiv.style.display = 'none';
        
        try {
            const jsonTexto = jsonInput.value.trim();
            if (!jsonTexto) throw new Error('Nenhum JSON foi colado!');
            
            dadosJson = JSON.parse(jsonTexto);
            
            if (dadosJson.especie_id) {
                selecionarEspecieAutomatica(dadosJson.especie_id);
            } else {
                document.getElementById('auto-select-info').style.display = 'flex';
                document.getElementById('auto-select-info').className = 'auto-select-info warning';
                document.getElementById('auto-select-info').innerHTML = '⚠️ JSON não contém "especie_id". Selecione manualmente.';
            }
            
            camposParaValidar = [];
            escolhasUsuario = {};
            
            for (let campo in opcoesValidas) {
                if (dadosJson[campo] && !opcoesValidas[campo].includes(dadosJson[campo])) {
                    camposParaValidar.push(campo);
                }
            }
            
            if (camposParaValidar.length > 0) {
                gerarModalValidacao();
                abrirModal();
                
                mensagemDiv.style.display = 'block';
                mensagemDiv.className = 'alert alert-warning';
                mensagemDiv.innerHTML = `⚠️ ${camposParaValidar.length} campo(s) precisam de validação.`;
            } else {
                atualizarVisualizacaoArtigo();
                
                mensagemDiv.style.display = 'block';
                mensagemDiv.className = 'alert alert-success';
                mensagemDiv.innerHTML = '✅ Todos os campos estão padronizados!';
                
                const especieId = document.getElementById('especie_id').value;
                if (especieId && especieId !== '') {
                    document.getElementById('btn_salvar').disabled = false;
                }
            }
            
        } catch (erro) {
            mensagemDiv.style.display = 'block';
            mensagemDiv.className = 'alert alert-danger';
            mensagemDiv.innerHTML = `❌ Erro: ${erro.message}`;
        }
    });
    
    document.getElementById('especie_id').addEventListener('change', function() {
        const btnSalvar = document.getElementById('btn_salvar');
        const temDados = dadosJson !== null;
        btnSalvar.disabled = !(this.value && this.value !== '' && temDados);
    });
    
    document.getElementById('btn_limpar').addEventListener('click', function() {
        if (confirm('Limpar todos os dados?')) {
            document.getElementById('json_input').value = '';
            document.getElementById('mensagem_json').style.display = 'none';
            document.getElementById('campos_ocultos').innerHTML = '';
            document.getElementById('btn_salvar').disabled = true;
            document.getElementById('especie_id').value = '';
            document.getElementById('auto-select-info').style.display = 'none';
            dadosJson = null;
            
            document.getElementById('preview_nome_cientifico').innerHTML = '[Nome científico]';
            document.getElementById('preview_familia').innerHTML = '—';
            document.getElementById('preview_nome_popular').innerHTML = '—';
            document.getElementById('preview_sinonimos').innerHTML = '—';
            document.getElementById('preview_autor').innerHTML = '—';
            document.getElementById('preview_secoes').innerHTML = '';
            document.getElementById('preview_referencias').innerHTML = '<li>Nenhuma referência</li>';
        }
    });
    
    window.onclick = function(event) {
        const modal = document.getElementById('modalValidacao');
        if (event.target === modal) fecharModal();
    };
    </script>
</body>
</html>