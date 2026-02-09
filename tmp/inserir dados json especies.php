<?php
// ================================================
// SCRIPT PARA INSERIR ACCA SELLOWIANA VIA JSON
// ================================================

// Configurar timezone
date_default_timezone_set('America/Sao_Paulo');

// ===============================
// CONFIGURAÇÕES DO BANCO
// ===============================
$host = "localhost";
$dbname = "penomato";
$username = "root";
$password = "";

// ===============================
// 1. CONEXÃO COM O BANCO
// ===============================
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Conexão com o banco estabelecida.\n";
} catch (PDOException $e) {
    die("❌ Erro na conexão: " . $e->getMessage());
}

// ===============================
// 2. BUSCAR O ID DA ESPÉCIE NO BANCO
// ===============================
$nome_cientifico = "Acca sellowiana";
$sql_busca_especie = "SELECT id FROM especies_administrativo WHERE nome_cientifico = :nome_cientifico";
$stmt = $pdo->prepare($sql_busca_especie);
$stmt->execute([':nome_cientifico' => $nome_cientifico]);
$especie = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$especie) {
    die("❌ Espécie '$nome_cientifico' não encontrada na tabela especies_administrativo.\n");
}

$id_especie = $especie['id'];
echo "✅ Espécie encontrada: ID = $id_especie\n";

// ===============================
// 3. LER O ARQUIVO JSON - CAMINHO CORRIGIDO
// ===============================
// O arquivo está em: C:\xampp\htdocs\penomato_mvp\tmp\especies json\Acca sellowiana.json
// O script está em: C:\xampp\htdocs\penomato_mvp\tmp\especies\insere_acca_sellowiana.php

$arquivo_json = "C:\\xampp\\htdocs\\penomato_mvp\\tmp\\especies json\\Acca sellowiana.json";

// Versão alternativa usando caminho relativo
// $arquivo_json = __DIR__ . "/../especies json/Acca sellowiana.json";

if (!file_exists($arquivo_json)) {
    die("❌ Arquivo JSON não encontrado: $arquivo_json\nVerifique se o arquivo existe neste local.\n");
}

echo "✅ Arquivo JSON encontrado: $arquivo_json\n";

$json_content = file_get_contents($arquivo_json);
$dados = json_decode($json_content, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("❌ Erro ao decodificar JSON: " . json_last_error_msg() . "\n");
}

echo "✅ JSON carregado com sucesso.\n";

// Verificar se há dados
if (empty($dados)) {
    die("❌ O arquivo JSON está vazio ou não contém dados válidos.\n");
}

echo "✅ Dados extraídos do JSON: " . count($dados) . " campos encontrados.\n";

// ===============================
// 4. PREPARAR DADOS PARA INSERÇÃO
// ===============================
$dados_inserir = [
    // Dados principais
    ':especie_id' => $id_especie,
    ':nome_cientifico_completo' => $dados['nome_cientifico_completo'] ?? null,
    ':nome_cientifico_completo_ref' => $dados['nome_cientifico_completo_ref'] ?? null,
    ':nome_popular' => $dados['nome_popular'] ?? null,
    ':familia' => $dados['familia'] ?? null,
    ':forma_folha' => $dados['forma_folha'] ?? null,
    ':filotaxia_folha' => $dados['filotaxia_folha'] ?? null,
    ':tipo_folha' => $dados['tipo_folha'] ?? null,
    ':tamanho_folha' => $dados['tamanho_folha'] ?? null,
    ':textura_folha' => $dados['textura_folha'] ?? null,
    ':margem_folha' => $dados['margem_folha'] ?? null,
    ':venacao_folha' => $dados['venacao_folha'] ?? null,
    ':cor_flores' => $dados['cor_flores'] ?? null,
    ':simetria_floral' => $dados['simetria_floral'] ?? null,
    ':numero_petalas' => $dados['numero_petalas'] ?? null,
    ':disposicao_flores' => $dados['disposicao_flores'] ?? null,
    ':aroma' => $dados['aroma'] ?? null,
    ':tamanho_flor' => $dados['tamanho_flor'] ?? null,
    ':tipo_fruto' => $dados['tipo_fruto'] ?? null,
    ':tamanho_fruto' => $dados['tamanho_fruto'] ?? null,
    ':cor_fruto' => $dados['cor_fruto'] ?? null,
    ':textura_fruto' => $dados['textura_fruto'] ?? null,
    ':dispersao_fruto' => $dados['dispersao_fruto'] ?? null,
    ':aroma_fruto' => $dados['aroma_fruto'] ?? null,
    ':tipo_semente' => $dados['tipo_semente'] ?? null,
    ':tamanho_semente' => $dados['tamanho_semente'] ?? null,
    ':cor_semente' => $dados['cor_semente'] ?? null,
    ':textura_semente' => $dados['textura_semente'] ?? null,
    ':quantidade_sementes' => $dados['quantidade_sementes'] ?? null,
    ':tipo_caule' => $dados['tipo_caule'] ?? null,
    ':estrutura_caule' => $dados['estrutura_caule'] ?? null,
    ':textura_caule' => $dados['textura_caule'] ?? null,
    ':cor_caule' => $dados['cor_caule'] ?? null,
    ':forma_caule' => $dados['forma_caule'] ?? null,
    ':modificacao_caule' => !empty($dados['modificacao_caule']) ? $dados['modificacao_caule'] : null,
    ':diametro_caule' => $dados['diametro_caule'] ?? null,
    ':ramificacao_caule' => $dados['ramificacao_caule'] ?? null,
    ':possui_espinhos' => $dados['possui_espinhos'] ?? null,
    ':possui_latex' => $dados['possui_latex'] ?? null,
    ':possui_seiva' => $dados['possui_seiva'] ?? null,
    ':possui_resina' => $dados['possui_resina'] ?? null,
    ':referencias' => $dados['referencias'] ?? null,
    
    // Referências
    ':familia_ref' => $dados['familia_ref'] ?? null,
    ':forma_folha_ref' => $dados['forma_folha_ref'] ?? null,
    ':filotaxia_folha_ref' => $dados['filotaxia_folha_ref'] ?? null,
    ':tipo_folha_ref' => $dados['tipo_folha_ref'] ?? null,
    ':tamanho_folha_ref' => $dados['tamanho_folha_ref'] ?? null,
    ':textura_folha_ref' => $dados['textura_folha_ref'] ?? null,
    ':margem_folha_ref' => $dados['margem_folha_ref'] ?? null,
    ':venacao_folha_ref' => $dados['venacao_folha_ref'] ?? null,
    ':cor_flores_ref' => $dados['cor_flores_ref'] ?? null,
    ':simetria_floral_ref' => $dados['simetria_floral_ref'] ?? null,
    ':numero_petalas_ref' => $dados['numero_petalas_ref'] ?? null,
    ':disposicao_flores_ref' => $dados['disposicao_flores_ref'] ?? null,
    ':aroma_ref' => $dados['aroma_ref'] ?? null,
    ':tamanho_flor_ref' => $dados['tamanho_flor_ref'] ?? null,
    ':tipo_fruto_ref' => $dados['tipo_fruto_ref'] ?? null,
    ':tamanho_fruto_ref' => $dados['tamanho_fruto_ref'] ?? null,
    ':cor_fruto_ref' => $dados['cor_fruto_ref'] ?? null,
    ':textura_fruto_ref' => $dados['textura_fruto_ref'] ?? null,
    ':dispersao_fruto_ref' => $dados['dispersao_fruto_ref'] ?? null,
    ':aroma_fruto_ref' => $dados['aroma_fruto_ref'] ?? null,
    ':tipo_semente_ref' => $dados['tipo_semente_ref'] ?? null,
    ':tamanho_semente_ref' => $dados['tamanho_semente_ref'] ?? null,
    ':cor_semente_ref' => $dados['cor_semente_ref'] ?? null,
    ':textura_semente_ref' => $dados['textura_semente_ref'] ?? null,
    ':quantidade_sementes_ref' => $dados['quantidade_sementes_ref'] ?? null,
    ':tipo_caule_ref' => $dados['tipo_caule_ref'] ?? null,
    ':estrutura_caule_ref' => $dados['estrutura_caule_ref'] ?? null,
    ':textura_caule_ref' => $dados['textura_caule_ref'] ?? null,
    ':cor_caule_ref' => $dados['cor_caule_ref'] ?? null,
    ':forma_caule_ref' => $dados['forma_caule_ref'] ?? null,
    ':modificacao_caule_ref' => $dados['modificacao_caule_ref'] ?? null,
    ':possui_espinhos_ref' => $dados['possui_espinhos_ref'] ?? null,
    ':possui_latex_ref' => $dados['possui_latex_ref'] ?? null,
    ':possui_seiva_ref' => $dados['possui_seiva_ref'] ?? null,
    ':possui_resina_ref' => $dados['possui_resina_ref'] ?? null
];

// Verificar alguns valores importantes
echo "\n📋 Verificando alguns dados importantes:\n";
echo "- Nome científico completo: " . ($dados_inserir[':nome_cientifico_completo'] ?: '(vazio)') . "\n";
echo "- Família: " . ($dados_inserir[':familia'] ?: '(vazio)') . "\n";
echo "- Tipo de fruto: " . ($dados_inserir[':tipo_fruto'] ?: '(vazio)') . "\n";

// ===============================
// 5. INSERIR NA TABELA especies_caracteristicas
// ===============================
echo "\n📝 Inserindo dados na tabela especies_caracteristicas...\n";

$sql_inserir = "
    INSERT INTO especies_caracteristicas (
        especie_id,
        nome_cientifico_completo,
        nome_cientifico_completo_ref,
        nome_popular,
        familia,
        forma_folha,
        filotaxia_folha,
        tipo_folha,
        tamanho_folha,
        textura_folha,
        margem_folha,
        venacao_folha,
        cor_flores,
        simetria_floral,
        numero_petalas,
        disposicao_flores,
        aroma,
        tamanho_flor,
        tipo_fruto,
        tamanho_fruto,
        cor_fruto,
        textura_fruto,
        dispersao_fruto,
        aroma_fruto,
        tipo_semente,
        tamanho_semente,
        cor_semente,
        textura_semente,
        quantidade_sementes,
        tipo_caule,
        estrutura_caule,
        textura_caule,
        cor_caule,
        forma_caule,
        modificacao_caule,
        diametro_caule,
        ramificacao_caule,
        possui_espinhos,
        possui_latex,
        possui_seiva,
        possui_resina,
        referencias,
        versao_dados,
        data_cadastro_botanico,
        familia_ref,
        forma_folha_ref,
        filotaxia_folha_ref,
        tipo_folha_ref,
        tamanho_folha_ref,
        textura_folha_ref,
        margem_folha_ref,
        venacao_folha_ref,
        cor_flores_ref,
        simetria_floral_ref,
        numero_petalas_ref,
        disposicao_flores_ref,
        aroma_ref,
        tamanho_flor_ref,
        tipo_fruto_ref,
        tamanho_fruto_ref,
        cor_fruto_ref,
        textura_fruto_ref,
        dispersao_fruto_ref,
        aroma_fruto_ref,
        tipo_semente_ref,
        tamanho_semente_ref,
        cor_semente_ref,
        textura_semente_ref,
        quantidade_sementes_ref,
        tipo_caule_ref,
        estrutura_caule_ref,
        textura_caule_ref,
        cor_caule_ref,
        forma_caule_ref,
        modificacao_caule_ref,
        possui_espinhos_ref,
        possui_latex_ref,
        possui_seiva_ref,
        possui_resina_ref
    ) VALUES (
        :especie_id,
        :nome_cientifico_completo,
        :nome_cientifico_completo_ref,
        :nome_popular,
        :familia,
        :forma_folha,
        :filotaxia_folha,
        :tipo_folha,
        :tamanho_folha,
        :textura_folha,
        :margem_folha,
        :venacao_folha,
        :cor_flores,
        :simetria_floral,
        :numero_petalas,
        :disposicao_flores,
        :aroma,
        :tamanho_flor,
        :tipo_fruto,
        :tamanho_fruto,
        :cor_fruto,
        :textura_fruto,
        :dispersao_fruto,
        :aroma_fruto,
        :tipo_semente,
        :tamanho_semente,
        :cor_semente,
        :textura_semente,
        :quantidade_sementes,
        :tipo_caule,
        :estrutura_caule,
        :textura_caule,
        :cor_caule,
        :forma_caule,
        :modificacao_caule,
        :diametro_caule,
        :ramificacao_caule,
        :possui_espinhos,
        :possui_latex,
        :possui_seiva,
        :possui_resina,
        :referencias,
        1,
        NOW(),
        :familia_ref,
        :forma_folha_ref,
        :filotaxia_folha_ref,
        :tipo_folha_ref,
        :tamanho_folha_ref,
        :textura_folha_ref,
        :margem_folha_ref,
        :venacao_folha_ref,
        :cor_flores_ref,
        :simetria_floral_ref,
        :numero_petalas_ref,
        :disposicao_flores_ref,
        :aroma_ref,
        :tamanho_flor_ref,
        :tipo_fruto_ref,
        :tamanho_fruto_ref,
        :cor_fruto_ref,
        :textura_fruto_ref,
        :dispersao_fruto_ref,
        :aroma_fruto_ref,
        :tipo_semente_ref,
        :tamanho_semente_ref,
        :cor_semente_ref,
        :textura_semente_ref,
        :quantidade_sementes_ref,
        :tipo_caule_ref,
        :estrutura_caule_ref,
        :textura_caule_ref,
        :cor_caule_ref,
        :forma_caule_ref,
        :modificacao_caule_ref,
        :possui_espinhos_ref,
        :possui_latex_ref,
        :possui_seiva_ref,
        :possui_resina_ref
    )
";

try {
    $stmt = $pdo->prepare($sql_inserir);
    $stmt->execute($dados_inserir);
    
    $id_inserido = $pdo->lastInsertId();
    echo "✅ Dados inseridos com sucesso na tabela especies_caracteristicas! ID: $id_inserido\n";
} catch (PDOException $e) {
    echo "❌ Erro ao inserir dados: " . $e->getMessage() . "\n";
    exit;
}

// ===============================
// 6. ATUALIZAR STATUS NA TABELA especies_administrativo
// ===============================
echo "\n🔄 Atualizando status da espécie...\n";

$sql_atualizar = "
    UPDATE especies_administrativo
    SET
        status_caracteristicas = 'completo',
        status_identificacao = 'identificada',
        data_identificacao = NOW(),
        data_ultima_atualizacao = NOW()
    WHERE id = :id_especie
";

try {
    $stmt = $pdo->prepare($sql_atualizar);
    $stmt->execute([':id_especie' => $id_especie]);
    
    echo "✅ Status da espécie atualizado para 'completo' e 'identificada'.\n";
} catch (PDOException $e) {
    echo "❌ Erro ao atualizar status: " . $e->getMessage() . "\n";
}

// ===============================
// 7. MENSAGEM FINAL
// ===============================
echo "\n" . str_repeat("=", 40) . "\n";
echo "✅ PROCESSO CONCLUÍDO COM SUCESSO!\n";
echo str_repeat("=", 40) . "\n";
echo "Espécie: $nome_cientifico\n";
echo "ID da espécie: $id_especie\n";
echo "ID do registro de características: $id_inserido\n";
echo "Data/Hora: " . date('d/m/Y H:i:s') . "\n";
echo "Arquivo JSON: $arquivo_json\n";
echo str_repeat("=", 40) . "\n";
?>