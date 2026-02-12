<?php
session_start();

// ================================================
// CONEXÃO COM O BANCO DE DADOS
// ================================================
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=penomato;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch (PDOException $e) {
    die("❌ Erro de conexão: " . $e->getMessage());
}

// ================================================
// FUNÇÃO PARA SALVAR ESPÉCIE NO BANCO
// ================================================
function salvarEspecie($pdo, $dados) {
    
    // 1. INSERIR NA TABELA especies_administrativo
    $sql_admin = "
        INSERT INTO especies_administrativo (
            id,
            nome_cientifico,
            nome_popular,
            familia,
            data_cadastro,
            status_caracteristicas,
            status_identificacao,
            data_ultima_atualizacao
        ) VALUES (
            :id,
            :nome_cientifico,
            :nome_popular,
            :familia,
            NOW(),
            'completo',
            'identificada',
            NOW()
        )
        ON DUPLICATE KEY UPDATE
            nome_cientifico = VALUES(nome_cientifico),
            nome_popular = VALUES(nome_popular),
            familia = VALUES(familia),
            status_caracteristicas = 'completo',
            status_identificacao = 'identificada',
            data_ultima_atualizacao = NOW()
    ";
    
    $stmt_admin = $pdo->prepare($sql_admin);
    $stmt_admin->execute([
        ':id' => $dados['especie_id'],
        ':nome_cientifico' => $dados['nome_cientifico_completo'] ?? $dados['especie_id'],
        ':nome_popular' => $dados['nome_popular'] ?? null,
        ':familia' => $dados['familia'] ?? null
    ]);
    
    // 2. INSERIR NA TABELA especies_caracteristicas
    $sql_carac = "
        INSERT INTO especies_caracteristicas (
            especie_id,
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
            '1.0',
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
        ON DUPLICATE KEY UPDATE
            nome_popular = VALUES(nome_popular),
            familia = VALUES(familia),
            forma_folha = VALUES(forma_folha),
            filotaxia_folha = VALUES(filotaxia_folha),
            tipo_folha = VALUES(tipo_folha),
            tamanho_folha = VALUES(tamanho_folha),
            textura_folha = VALUES(textura_folha),
            margem_folha = VALUES(margem_folha),
            venacao_folha = VALUES(venacao_folha),
            cor_flores = VALUES(cor_flores),
            simetria_floral = VALUES(simetria_floral),
            numero_petalas = VALUES(numero_petalas),
            disposicao_flores = VALUES(disposicao_flores),
            aroma = VALUES(aroma),
            tamanho_flor = VALUES(tamanho_flor),
            tipo_fruto = VALUES(tipo_fruto),
            tamanho_fruto = VALUES(tamanho_fruto),
            cor_fruto = VALUES(cor_fruto),
            textura_fruto = VALUES(textura_fruto),
            dispersao_fruto = VALUES(dispersao_fruto),
            aroma_fruto = VALUES(aroma_fruto),
            tipo_semente = VALUES(tipo_semente),
            tamanho_semente = VALUES(tamanho_semente),
            cor_semente = VALUES(cor_semente),
            textura_semente = VALUES(textura_semente),
            quantidade_sementes = VALUES(quantidade_sementes),
            tipo_caule = VALUES(tipo_caule),
            estrutura_caule = VALUES(estrutura_caule),
            textura_caule = VALUES(textura_caule),
            cor_caule = VALUES(cor_caule),
            forma_caule = VALUES(forma_caule),
            modificacao_caule = VALUES(modificacao_caule),
            diametro_caule = VALUES(diametro_caule),
            ramificacao_caule = VALUES(ramificacao_caule),
            possui_espinhos = VALUES(possui_espinhos),
            possui_latex = VALUES(possui_latex),
            possui_seiva = VALUES(possui_seiva),
            possui_resina = VALUES(possui_resina),
            referencias = VALUES(referencias),
            familia_ref = VALUES(familia_ref),
            forma_folha_ref = VALUES(forma_folha_ref),
            filotaxia_folha_ref = VALUES(filotaxia_folha_ref),
            tipo_folha_ref = VALUES(tipo_folha_ref),
            tamanho_folha_ref = VALUES(tamanho_folha_ref),
            textura_folha_ref = VALUES(textura_folha_ref),
            margem_folha_ref = VALUES(margem_folha_ref),
            venacao_folha_ref = VALUES(venacao_folha_ref),
            cor_flores_ref = VALUES(cor_flores_ref),
            simetria_floral_ref = VALUES(simetria_floral_ref),
            numero_petalas_ref = VALUES(numero_petalas_ref),
            disposicao_flores_ref = VALUES(disposicao_flores_ref),
            aroma_ref = VALUES(aroma_ref),
            tamanho_flor_ref = VALUES(tamanho_flor_ref),
            tipo_fruto_ref = VALUES(tipo_fruto_ref),
            tamanho_fruto_ref = VALUES(tamanho_fruto_ref),
            cor_fruto_ref = VALUES(cor_fruto_ref),
            textura_fruto_ref = VALUES(textura_fruto_ref),
            dispersao_fruto_ref = VALUES(dispersao_fruto_ref),
            aroma_fruto_ref = VALUES(aroma_fruto_ref),
            tipo_semente_ref = VALUES(tipo_semente_ref),
            tamanho_semente_ref = VALUES(tamanho_semente_ref),
            cor_semente_ref = VALUES(cor_semente_ref),
            textura_semente_ref = VALUES(textura_semente_ref),
            quantidade_sementes_ref = VALUES(quantidade_sementes_ref),
            tipo_caule_ref = VALUES(tipo_caule_ref),
            estrutura_caule_ref = VALUES(estrutura_caule_ref),
            textura_caule_ref = VALUES(textura_caule_ref),
            cor_caule_ref = VALUES(cor_caule_ref),
            forma_caule_ref = VALUES(forma_caule_ref),
            modificacao_caule_ref = VALUES(modificacao_caule_ref),
            possui_espinhos_ref = VALUES(possui_espinhos_ref),
            possui_latex_ref = VALUES(possui_latex_ref),
            possui_seiva_ref = VALUES(possui_seiva_ref),
            possui_resina_ref = VALUES(possui_resina_ref)
    ";
    
    $stmt_carac = $pdo->prepare($sql_carac);
    
    $params = [
        ':especie_id' => $dados['especie_id'] ?? null,
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
        ':modificacao_caule' => $dados['modificacao_caule'] ?? null,
        ':diametro_caule' => $dados['diametro_caule'] ?? null,
        ':ramificacao_caule' => $dados['ramificacao_caule'] ?? null,
        ':possui_espinhos' => $dados['possui_espinhos'] ?? null,
        ':possui_latex' => $dados['possui_latex'] ?? null,
        ':possui_seiva' => $dados['possui_seiva'] ?? null,
        ':possui_resina' => $dados['possui_resina'] ?? null,
        ':referencias' => $dados['referencias'] ?? null,
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
    
    $stmt_carac->execute($params);
    
    return true;
}

// ================================================
// LER E PROCESSAR O ARQUIVO JSON
// ================================================

// Nome do arquivo JSON
$arquivo_json = 'dados_especies.json';

// Verificar se o arquivo existe
if (!file_exists($arquivo_json)) {
    die("❌ Arquivo '{$arquivo_json}' não encontrado!");
}

// Ler o conteúdo do arquivo
$conteudo_json = file_get_contents($arquivo_json);

if ($conteudo_json === false) {
    die("❌ Erro ao ler o arquivo JSON!");
}

// Converter o conteúdo JSON para array
$especies = json_decode($conteudo_json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("❌ Erro no formato JSON: " . json_last_error_msg());
}

// Se for um único objeto, transformar em array
if (isset($especies['especie_id'])) {
    $especies = [$especies];
}

// ================================================
// PROCESSAR CADA ESPÉCIE
// ================================================

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Importação de Espécies</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #28a745; padding-bottom: 10px; }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 4px; margin: 5px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 4px; margin: 5px 0; }
        .resumo { background: #e9ecef; padding: 15px; border-radius: 4px; margin-top: 20px; }
        .badge { background: #007bff; color: white; padding: 3px 8px; border-radius: 4px; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>📦 Importação de Espécies do JSON</h1>";

$total = count($especies);
$sucesso = 0;
$erros = 0;

echo "<p>📄 Arquivo: <strong>{$arquivo_json}</strong> | Total de espécies: <span class='badge'>{$total}</span></p>";
echo "<hr>";

foreach ($especies as $index => $dados) {
    try {
        // Validar dados mínimos
        if (empty($dados['especie_id'])) {
            throw new Exception("Espécie sem ID na linha " . ($index + 1));
        }
        
        // Salvar no banco
        salvarEspecie($pdo, $dados);
        
        echo "<div class='success'>✅ [" . ($index + 1) . "/{$total}] <strong>" . htmlspecialchars($dados['especie_id']) . "</strong> importada com sucesso!</div>";
        $sucesso++;
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ [" . ($index + 1) . "/{$total}] Erro ao importar " . htmlspecialchars($dados['especie_id'] ?? 'Desconhecida') . ": " . $e->getMessage() . "</div>";
        $erros++;
    }
}

echo "<div class='resumo'>";
echo "<h2>📊 RESUMO DA IMPORTAÇÃO</h2>";
echo "<p>✅ Sucesso: <strong style='color: #28a745;'>{$sucesso}</strong> espécies</p>";
echo "<p>❌ Erros: <strong style='color: #dc3545;'>{$erros}</strong> espécies</p>";
echo "<p>📋 Total processado: <strong>{$total}</strong> espécies</p>";

if ($sucesso == $total) {
    echo "<p style='color: #28a745; font-size: 18px;'>🎉 TODAS AS ESPÉCIES FORAM IMPORTADAS COM SUCESSO!</p>";
} elseif ($sucesso > 0) {
    echo "<p style='color: #ffc107;'>⚠️ Importação parcial concluída!</p>";
} else {
    echo "<p style='color: #dc3545;'>❌ Nenhuma espécie foi importada!</p>";
}

echo "</div>";
echo "</div></body></html>";
?>