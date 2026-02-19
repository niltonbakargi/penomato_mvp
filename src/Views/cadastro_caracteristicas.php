<?php
// ================================================
// CADASTRO DE CARACTERÍSTICAS - CONTROLADOR
// VERSÃO CORRIGIDA E OTIMIZADA
// ================================================

// Iniciar sessão
session_start();

// ================================================
// FUNÇÕES AUXILIARES
// ================================================

/**
 * Função para escapar valores com segurança (evita SQL Injection)
 */
function esc($valor) {
    global $conexao;
    return mysqli_real_escape_string($conexao, $valor);
}

/**
 * Verifica se o usuário está logado (proteção básica)
 */
function estaLogado() {
    return isset($_SESSION['usuario_id']);
}

// ================================================
// VERIFICAR AUTENTICAÇÃO
// ================================================
if (!estaLogado()) {
    header('Location: /penomato_mvp/src/Views/auth/login.php');
    exit;
}

// ================================================
// CONFIGURAÇÕES DO BANCO
// ================================================
$servidor = "127.0.0.1";
$usuario = "root";
$senha = "";
$banco = "penomato";

// Variáveis de controle
$mensagem_erro = '';

// ================================================
// PROCESSAR FORMULÁRIO
// ================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Conectar ao banco
    $conexao = mysqli_connect($servidor, $usuario, $senha, $banco);
    
    if (!$conexao) {
        die("Erro de conexão: " . mysqli_connect_error());
    }
    
    mysqli_set_charset($conexao, "utf8mb4");
    
    // ================================================
    // VALIDAÇÕES BÁSICAS
    // ================================================
    
    // Verificar se a espécie foi selecionada
    if (empty($_POST['especie_id'])) {
        $mensagem_erro = "Selecione uma espécie para cadastrar as características.";
    } else {
        
        // ================================================
        // COLETAR DADOS DO FORMULÁRIO
        // ================================================
        
        // Dados básicos
        $especie_id = (int)$_POST['especie_id'];
        $nome_cientifico_completo = esc($_POST['nome_cientifico_completo'] ?? '');
        $nome_cientifico_completo_ref = esc($_POST['nome_cientifico_completo_ref'] ?? '');
        
        // Sinônimos
        $sinonimos = esc($_POST['sinonimos'] ?? '');
        $sinonimos_ref = esc($_POST['sinonimos_ref'] ?? '');
        
        $nome_popular = esc($_POST['nome_popular'] ?? '');
        $nome_popular_ref = esc($_POST['nome_popular_ref'] ?? '');
        $familia = esc($_POST['familia'] ?? '');
        $familia_ref = esc($_POST['familia_ref'] ?? '');
        
        // Folha
        $forma_folha = esc($_POST['forma_folha'] ?? '');
        $forma_folha_ref = esc($_POST['forma_folha_ref'] ?? '');
        $filotaxia_folha = esc($_POST['filotaxia_folha'] ?? '');
        $filotaxia_folha_ref = esc($_POST['filotaxia_folha_ref'] ?? '');
        $tipo_folha = esc($_POST['tipo_folha'] ?? '');
        $tipo_folha_ref = esc($_POST['tipo_folha_ref'] ?? '');
        $tamanho_folha = esc($_POST['tamanho_folha'] ?? '');
        $tamanho_folha_ref = esc($_POST['tamanho_folha_ref'] ?? '');
        $textura_folha = esc($_POST['textura_folha'] ?? '');
        $textura_folha_ref = esc($_POST['textura_folha_ref'] ?? '');
        $margem_folha = esc($_POST['margem_folha'] ?? '');
        $margem_folha_ref = esc($_POST['margem_folha_ref'] ?? '');
        $venacao_folha = esc($_POST['venacao_folha'] ?? '');
        $venacao_folha_ref = esc($_POST['venacao_folha_ref'] ?? '');
        
        // ================================================
        // FLORES - NOMES REAIS DA TABELA
        // ================================================
        $cor_flores = esc($_POST['cor_flores'] ?? '');
        $cor_flores_ref = esc($_POST['cor_flores_ref'] ?? '');
        
        $simetria_floral = esc($_POST['simetria_floral'] ?? '');
        $simetria_floral_ref = esc($_POST['simetria_floral_ref'] ?? '');
        
        $numero_petalas = esc($_POST['numero_petalas'] ?? '');
        $numero_petalas_ref = esc($_POST['numero_petalas_ref'] ?? '');
        
        $tamanho_flor = esc($_POST['tamanho_flor'] ?? '');
        $tamanho_flor_ref = esc($_POST['tamanho_flor_ref'] ?? '');
        
        $disposicao_flores = esc($_POST['disposicao_flores'] ?? '');
        $disposicao_flores_ref = esc($_POST['disposicao_flores_ref'] ?? '');
        
        $aroma = esc($_POST['aroma'] ?? '');
        $aroma_ref = esc($_POST['aroma_ref'] ?? '');
        
        // Frutos
        $tipo_fruto = esc($_POST['tipo_fruto'] ?? '');
        $tipo_fruto_ref = esc($_POST['tipo_fruto_ref'] ?? '');
        $tamanho_fruto = esc($_POST['tamanho_fruto'] ?? '');
        $tamanho_fruto_ref = esc($_POST['tamanho_fruto_ref'] ?? '');
        $cor_fruto = esc($_POST['cor_fruto'] ?? '');
        $cor_fruto_ref = esc($_POST['cor_fruto_ref'] ?? '');
        $textura_fruto = esc($_POST['textura_fruto'] ?? '');
        $textura_fruto_ref = esc($_POST['textura_fruto_ref'] ?? '');
        $dispersao_fruto = esc($_POST['dispersao_fruto'] ?? '');
        $dispersao_fruto_ref = esc($_POST['dispersao_fruto_ref'] ?? '');
        $aroma_fruto = esc($_POST['aroma_fruto'] ?? '');
        $aroma_fruto_ref = esc($_POST['aroma_fruto_ref'] ?? '');
        
        // Sementes
        $tipo_semente = esc($_POST['tipo_semente'] ?? '');
        $tipo_semente_ref = esc($_POST['tipo_semente_ref'] ?? '');
        $tamanho_semente = esc($_POST['tamanho_semente'] ?? '');
        $tamanho_semente_ref = esc($_POST['tamanho_semente_ref'] ?? '');
        $cor_semente = esc($_POST['cor_semente'] ?? '');
        $cor_semente_ref = esc($_POST['cor_semente_ref'] ?? '');
        $textura_semente = esc($_POST['textura_semente'] ?? '');
        $textura_semente_ref = esc($_POST['textura_semente_ref'] ?? '');
        $quantidade_sementes = esc($_POST['quantidade_sementes'] ?? '');
        $quantidade_sementes_ref = esc($_POST['quantidade_sementes_ref'] ?? '');
        
        // Caule
        $tipo_caule = esc($_POST['tipo_caule'] ?? '');
        $tipo_caule_ref = esc($_POST['tipo_caule_ref'] ?? '');
        $estrutura_caule = esc($_POST['estrutura_caule'] ?? '');
        $estrutura_caule_ref = esc($_POST['estrutura_caule_ref'] ?? '');
        $textura_caule = esc($_POST['textura_caule'] ?? '');
        $textura_caule_ref = esc($_POST['textura_caule_ref'] ?? '');
        $cor_caule = esc($_POST['cor_caule'] ?? '');
        $cor_caule_ref = esc($_POST['cor_caule_ref'] ?? '');
        $forma_caule = esc($_POST['forma_caule'] ?? '');
        $forma_caule_ref = esc($_POST['forma_caule_ref'] ?? '');
        $modificacao_caule = esc($_POST['modificacao_caule'] ?? '');
        $modificacao_caule_ref = esc($_POST['modificacao_caule_ref'] ?? '');
        $diametro_caule = esc($_POST['diametro_caule'] ?? '');
        $diametro_caule_ref = esc($_POST['diametro_caule_ref'] ?? '');
        $ramificacao_caule = esc($_POST['ramificacao_caule'] ?? '');
        $ramificacao_caule_ref = esc($_POST['ramificacao_caule_ref'] ?? '');
        
        // Outras
        $possui_espinhos = esc($_POST['possui_espinhos'] ?? '');
        $possui_espinhos_ref = esc($_POST['possui_espinhos_ref'] ?? '');
        $possui_latex = esc($_POST['possui_latex'] ?? '');
        $possui_latex_ref = esc($_POST['possui_latex_ref'] ?? '');
        $possui_seiva = esc($_POST['possui_seiva'] ?? '');
        $possui_seiva_ref = esc($_POST['possui_seiva_ref'] ?? '');
        $possui_resina = esc($_POST['possui_resina'] ?? '');
        $possui_resina_ref = esc($_POST['possui_resina_ref'] ?? '');
        
        // Referências completas
        $referencias = esc($_POST['referencias'] ?? '');
        
        // ================================================
        // VERIFICAR SE A ESPÉCIE JÁ TEM CARACTERÍSTICAS
        // ================================================
        $sql_check = "SELECT id FROM especies_caracteristicas WHERE especie_id = $especie_id";
        $result_check = mysqli_query($conexao, $sql_check);
        $ja_existe = mysqli_num_rows($result_check) > 0;
        
        if ($ja_existe) {
            // UPDATE - Já existe, então atualiza
            $sql = "UPDATE especies_caracteristicas SET
                nome_cientifico_completo = '$nome_cientifico_completo',
                nome_cientifico_completo_ref = '$nome_cientifico_completo_ref',
                sinonimos = '$sinonimos',
                sinonimos_ref = '$sinonimos_ref',
                nome_popular = '$nome_popular',
                nome_popular_ref = '$nome_popular_ref',
                familia = '$familia',
                familia_ref = '$familia_ref',
                forma_folha = '$forma_folha',
                forma_folha_ref = '$forma_folha_ref',
                filotaxia_folha = '$filotaxia_folha',
                filotaxia_folha_ref = '$filotaxia_folha_ref',
                tipo_folha = '$tipo_folha',
                tipo_folha_ref = '$tipo_folha_ref',
                tamanho_folha = '$tamanho_folha',
                tamanho_folha_ref = '$tamanho_folha_ref',
                textura_folha = '$textura_folha',
                textura_folha_ref = '$textura_folha_ref',
                margem_folha = '$margem_folha',
                margem_folha_ref = '$margem_folha_ref',
                venacao_folha = '$venacao_folha',
                venacao_folha_ref = '$venacao_folha_ref',
                
                -- FLORES
                cor_flores = '$cor_flores',
                cor_flores_ref = '$cor_flores_ref',
                simetria_floral = '$simetria_floral',
                simetria_floral_ref = '$simetria_floral_ref',
                numero_petalas = '$numero_petalas',
                numero_petalas_ref = '$numero_petalas_ref',
                tamanho_flor = '$tamanho_flor',
                tamanho_flor_ref = '$tamanho_flor_ref',
                disposicao_flores = '$disposicao_flores',
                disposicao_flores_ref = '$disposicao_flores_ref',
                aroma = '$aroma',
                aroma_ref = '$aroma_ref',
                
                -- FRUTOS
                tipo_fruto = '$tipo_fruto',
                tipo_fruto_ref = '$tipo_fruto_ref',
                tamanho_fruto = '$tamanho_fruto',
                tamanho_fruto_ref = '$tamanho_fruto_ref',
                cor_fruto = '$cor_fruto',
                cor_fruto_ref = '$cor_fruto_ref',
                textura_fruto = '$textura_fruto',
                textura_fruto_ref = '$textura_fruto_ref',
                dispersao_fruto = '$dispersao_fruto',
                dispersao_fruto_ref = '$dispersao_fruto_ref',
                aroma_fruto = '$aroma_fruto',
                aroma_fruto_ref = '$aroma_fruto_ref',
                
                -- SEMENTES
                tipo_semente = '$tipo_semente',
                tipo_semente_ref = '$tipo_semente_ref',
                tamanho_semente = '$tamanho_semente',
                tamanho_semente_ref = '$tamanho_semente_ref',
                cor_semente = '$cor_semente',
                cor_semente_ref = '$cor_semente_ref',
                textura_semente = '$textura_semente',
                textura_semente_ref = '$textura_semente_ref',
                quantidade_sementes = '$quantidade_sementes',
                quantidade_sementes_ref = '$quantidade_sementes_ref',
                
                -- CAULE
                tipo_caule = '$tipo_caule',
                tipo_caule_ref = '$tipo_caule_ref',
                estrutura_caule = '$estrutura_caule',
                estrutura_caule_ref = '$estrutura_caule_ref',
                textura_caule = '$textura_caule',
                textura_caule_ref = '$textura_caule_ref',
                cor_caule = '$cor_caule',
                cor_caule_ref = '$cor_caule_ref',
                forma_caule = '$forma_caule',
                forma_caule_ref = '$forma_caule_ref',
                modificacao_caule = '$modificacao_caule',
                modificacao_caule_ref = '$modificacao_caule_ref',
                diametro_caule = '$diametro_caule',
                diametro_caule_ref = '$diametro_caule_ref',
                ramificacao_caule = '$ramificacao_caule',
                ramificacao_caule_ref = '$ramificacao_caule_ref',
                
                -- OUTRAS
                possui_espinhos = '$possui_espinhos',
                possui_espinhos_ref = '$possui_espinhos_ref',
                possui_latex = '$possui_latex',
                possui_latex_ref = '$possui_latex_ref',
                possui_seiva = '$possui_seiva',
                possui_seiva_ref = '$possui_seiva_ref',
                possui_resina = '$possui_resina',
                possui_resina_ref = '$possui_resina_ref',
                
                -- REFERÊNCIAS
                referencias = '$referencias',
                data_atualizacao = NOW()
            WHERE especie_id = $especie_id";
            
        } else {
            // INSERT - Novo registro
            $sql = "INSERT INTO especies_caracteristicas (
                especie_id,
                nome_cientifico_completo,
                nome_cientifico_completo_ref,
                sinonimos,
                sinonimos_ref,
                nome_popular,
                nome_popular_ref,
                familia,
                familia_ref,
                forma_folha,
                forma_folha_ref,
                filotaxia_folha,
                filotaxia_folha_ref,
                tipo_folha,
                tipo_folha_ref,
                tamanho_folha,
                tamanho_folha_ref,
                textura_folha,
                textura_folha_ref,
                margem_folha,
                margem_folha_ref,
                venacao_folha,
                venacao_folha_ref,
                
                -- FLORES
                cor_flores,
                cor_flores_ref,
                simetria_floral,
                simetria_floral_ref,
                numero_petalas,
                numero_petalas_ref,
                tamanho_flor,
                tamanho_flor_ref,
                disposicao_flores,
                disposicao_flores_ref,
                aroma,
                aroma_ref,
                
                -- FRUTOS
                tipo_fruto,
                tipo_fruto_ref,
                tamanho_fruto,
                tamanho_fruto_ref,
                cor_fruto,
                cor_fruto_ref,
                textura_fruto,
                textura_fruto_ref,
                dispersao_fruto,
                dispersao_fruto_ref,
                aroma_fruto,
                aroma_fruto_ref,
                
                -- SEMENTES
                tipo_semente,
                tipo_semente_ref,
                tamanho_semente,
                tamanho_semente_ref,
                cor_semente,
                cor_semente_ref,
                textura_semente,
                textura_semente_ref,
                quantidade_sementes,
                quantidade_sementes_ref,
                
                -- CAULE
                tipo_caule,
                tipo_caule_ref,
                estrutura_caule,
                estrutura_caule_ref,
                textura_caule,
                textura_caule_ref,
                cor_caule,
                cor_caule_ref,
                forma_caule,
                forma_caule_ref,
                modificacao_caule,
                modificacao_caule_ref,
                diametro_caule,
                diametro_caule_ref,
                ramificacao_caule,
                ramificacao_caule_ref,
                
                -- OUTRAS
                possui_espinhos,
                possui_espinhos_ref,
                possui_latex,
                possui_latex_ref,
                possui_seiva,
                possui_seiva_ref,
                possui_resina,
                possui_resina_ref,
                
                -- REFERÊNCIAS
                referencias
            ) VALUES (
                $especie_id,
                '$nome_cientifico_completo',
                '$nome_cientifico_completo_ref',
                '$sinonimos',
                '$sinonimos_ref',
                '$nome_popular',
                '$nome_popular_ref',
                '$familia',
                '$familia_ref',
                '$forma_folha',
                '$forma_folha_ref',
                '$filotaxia_folha',
                '$filotaxia_folha_ref',
                '$tipo_folha',
                '$tipo_folha_ref',
                '$tamanho_folha',
                '$tamanho_folha_ref',
                '$textura_folha',
                '$textura_folha_ref',
                '$margem_folha',
                '$margem_folha_ref',
                '$venacao_folha',
                '$venacao_folha_ref',
                
                -- FLORES
                '$cor_flores',
                '$cor_flores_ref',
                '$simetria_floral',
                '$simetria_floral_ref',
                '$numero_petalas',
                '$numero_petalas_ref',
                '$tamanho_flor',
                '$tamanho_flor_ref',
                '$disposicao_flores',
                '$disposicao_flores_ref',
                '$aroma',
                '$aroma_ref',
                
                -- FRUTOS
                '$tipo_fruto',
                '$tipo_fruto_ref',
                '$tamanho_fruto',
                '$tamanho_fruto_ref',
                '$cor_fruto',
                '$cor_fruto_ref',
                '$textura_fruto',
                '$textura_fruto_ref',
                '$dispersao_fruto',
                '$dispersao_fruto_ref',
                '$aroma_fruto',
                '$aroma_fruto_ref',
                
                -- SEMENTES
                '$tipo_semente',
                '$tipo_semente_ref',
                '$tamanho_semente',
                '$tamanho_semente_ref',
                '$cor_semente',
                '$cor_semente_ref',
                '$textura_semente',
                '$textura_semente_ref',
                '$quantidade_sementes',
                '$quantidade_sementes_ref',
                
                -- CAULE
                '$tipo_caule',
                '$tipo_caule_ref',
                '$estrutura_caule',
                '$estrutura_caule_ref',
                '$textura_caule',
                '$textura_caule_ref',
                '$cor_caule',
                '$cor_caule_ref',
                '$forma_caule',
                '$forma_caule_ref',
                '$modificacao_caule',
                '$modificacao_caule_ref',
                '$diametro_caule',
                '$diametro_caule_ref',
                '$ramificacao_caule',
                '$ramificacao_caule_ref',
                
                -- OUTRAS
                '$possui_espinhos',
                '$possui_espinhos_ref',
                '$possui_latex',
                '$possui_latex_ref',
                '$possui_seiva',
                '$possui_seiva_ref',
                '$possui_resina',
                '$possui_resina_ref',
                
                -- REFERÊNCIAS
                '$referencias'
            )";
        }
        
        // Executar a query
        if (mysqli_query($conexao, $sql)) {
            
            // ================================================
            // ATUALIZAR STATUS DA ESPÉCIE
            // ================================================
            $sql_status = "UPDATE especies_administrativo 
                          SET status = 'dados_internet', 
                              data_ultima_atualizacao = NOW() 
                          WHERE id = $especie_id";
            mysqli_query($conexao, $sql_status);
            
            // Redirecionar para página de sucesso
            header("Location: sucesso_cadastro.php?id=$especie_id");
            exit;
            
        } else {
            $mensagem_erro = "Erro ao salvar: " . mysqli_error($conexao);
        }
    }
    
    mysqli_close($conexao);
}

// ================================================
// SE HOUVER ERRO, MOSTRA MENSAGEM
// ================================================
if (!empty($mensagem_erro)) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erro no Cadastro</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: linear-gradient(135deg, #0b5e42 0%, #1a7a5a 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0;
            }
            .card {
                background: white;
                border-radius: 15px;
                padding: 40px;
                max-width: 500px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                text-align: center;
            }
            .error {
                color: #dc3545;
                font-size: 1.2rem;
                margin: 20px 0;
            }
            .btn {
                background: #0b5e42;
                color: white;
                padding: 12px 30px;
                border: none;
                border-radius: 40px;
                font-size: 1.1rem;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                margin-top: 20px;
            }
            .btn:hover {
                background: #0a4c35;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <h2 style="color: #0b5e42;">❌ Erro no Cadastro</h2>
            <div class="error"><?php echo $mensagem_erro; ?></div>
            <a href="javascript:history.back()" class="btn">← Voltar e corrigir</a>
        </div>
    </body>
    </html>
    <?php
}
?>