<?php
// ================================================
// SCRIPT PARA INSERIR ESPÉCIE VIA JSON
// VERSÃO CORRIGIDA - COMPATÍVEL COM STATUS ÚNICO
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
// CONFIGURAÇÕES DO SCRIPT
// ===============================
$pasta_json = "C:\\xampp\\htdocs\\penomato_mvp\\tmp\\especies json\\";

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
    die("❌ Erro na conexão: " . $e->getMessage() . "\n");
}

// ===============================
// 2. LISTAR ARQUIVOS JSON NA PASTA
// ===============================
echo "\n📂 Verificando arquivos JSON na pasta: $pasta_json\n";

if (!is_dir($pasta_json)) {
    die("❌ Pasta não encontrada: $pasta_json\n");
}

$arquivos_json = glob($pasta_json . "*.json");

if (empty($arquivos_json)) {
    die("❌ Nenhum arquivo JSON encontrado na pasta.\n");
}

echo "✅ " . count($arquivos_json) . " arquivo(s) JSON encontrado(s).\n\n";

// ===============================
// 3. PROCESSAR CADA ARQUIVO JSON
// ===============================
$contador_sucesso = 0;
$contador_erro = 0;
$resultados = [];

foreach ($arquivos_json as $arquivo_json) {
    echo str_repeat("-", 60) . "\n";
    echo "📄 Processando: " . basename($arquivo_json) . "\n";
    
    // ===============================
    // 4. EXTRAIR NOME CIENTÍFICO DO NOME DO ARQUIVO
    // ===============================
    $nome_arquivo = basename($arquivo_json, '.json');
    $nome_cientifico = trim($nome_arquivo);
    
    echo "🔍 Nome científico identificado: $nome_cientifico\n";
    
    // ===============================
    // 5. BUSCAR O ID DA ESPÉCIE NO BANCO
    // ===============================
    $sql_busca_especie = "SELECT id FROM especies_administrativo WHERE nome_cientifico = :nome_cientifico";
    $stmt = $pdo->prepare($sql_busca_especie);
    $stmt->execute([':nome_cientifico' => $nome_cientifico]);
    $especie = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$especie) {
        echo "❌ Espécie '$nome_cientifico' não encontrada na tabela especies_administrativo.\n";
        echo "   Deseja cadastrar esta espécie primeiro? (pule manualmente)\n";
        $contador_erro++;
        $resultados[] = [
            'arquivo' => $nome_arquivo,
            'status' => 'erro',
            'mensagem' => 'Espécie não cadastrada'
        ];
        continue;
    }
    
    $id_especie = $especie['id'];
    echo "✅ Espécie encontrada: ID = $id_especie\n";
    
    // ===============================
    // 6. LER O ARQUIVO JSON
    // ===============================
    if (!file_exists($arquivo_json)) {
        echo "❌ Arquivo JSON não encontrado: $arquivo_json\n";
        $contador_erro++;
        $resultados[] = [
            'arquivo' => $nome_arquivo,
            'status' => 'erro',
            'mensagem' => 'Arquivo não encontrado'
        ];
        continue;
    }
    
    $json_content = file_get_contents($arquivo_json);
    $dados = json_decode($json_content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ Erro ao decodificar JSON: " . json_last_error_msg() . "\n";
        $contador_erro++;
        $resultados[] = [
            'arquivo' => $nome_arquivo,
            'status' => 'erro',
            'mensagem' => 'JSON inválido: ' . json_last_error_msg()
        ];
        continue;
    }
    
    if (empty($dados)) {
        echo "❌ O arquivo JSON está vazio ou não contém dados válidos.\n";
        $contador_erro++;
        $resultados[] = [
            'arquivo' => $nome_arquivo,
            'status' => 'erro',
            'mensagem' => 'JSON vazio'
        ];
        continue;
    }
    
    echo "✅ JSON carregado com sucesso: " . count($dados) . " campos encontrados.\n";
    
    // ===============================
    // 7. VERIFICAR SE JÁ EXISTE REGISTRO PARA ESTA ESPÉCIE
    // ===============================
    $sql_verifica_existente = "SELECT id FROM especies_caracteristicas WHERE especie_id = :especie_id LIMIT 1";
    $stmt_verifica = $pdo->prepare($sql_verifica_existente);
    $stmt_verifica->execute([':especie_id' => $id_especie]);
    $registro_existente = $stmt_verifica->fetch(PDO::FETCH_ASSOC);
    
    if ($registro_existente) {
        echo "⚠️  Já existe um registro para esta espécie (ID: {$registro_existente['id']}).\n";
        echo "   Deseja atualizar os dados? (pulando por enquanto)\n";
        $contador_erro++;
        $resultados[] = [
            'arquivo' => $nome_arquivo,
            'status' => 'pulado',
            'mensagem' => 'Registro já existe',
            'id_registro' => $registro_existente['id']
        ];
        continue;
    }
    
    // ===============================
    // 8. PREPARAR DADOS PARA INSERÇÃO
    // ===============================
    $dados_inserir = [
        // Dados principais
        ':especie_id' => $id_especie,
        ':nome_cientifico_completo' => $dados['nome_cientifico_completo'] ?? $nome_cientifico,
        ':nome_cientifico_completo_ref' => $dados['nome_cientifico_completo_ref'] ?? null,
        ':sinonimos' => $dados['sinonimos'] ?? null,
        ':sinonimos_ref' => $dados['sinonimos_ref'] ?? null,
        ':nome_popular' => $dados['nome_popular'] ?? null,
        ':nome_popular_ref' => $dados['nome_popular_ref'] ?? null,
        ':familia' => $dados['familia'] ?? null,
        ':familia_ref' => $dados['familia_ref'] ?? null,
        
        ':forma_folha' => $dados['forma_folha'] ?? null,
        ':forma_folha_ref' => $dados['forma_folha_ref'] ?? null,
        ':filotaxia_folha' => $dados['filotaxia_folha'] ?? null,
        ':filotaxia_folha_ref' => $dados['filotaxia_folha_ref'] ?? null,
        ':tipo_folha' => $dados['tipo_folha'] ?? null,
        ':tipo_folha_ref' => $dados['tipo_folha_ref'] ?? null,
        ':tamanho_folha' => $dados['tamanho_folha'] ?? null,
        ':tamanho_folha_ref' => $dados['tamanho_folha_ref'] ?? null,
        ':textura_folha' => $dados['textura_folha'] ?? null,
        ':textura_folha_ref' => $dados['textura_folha_ref'] ?? null,
        ':margem_folha' => $dados['margem_folha'] ?? null,
        ':margem_folha_ref' => $dados['margem_folha_ref'] ?? null,
        ':venacao_folha' => $dados['venacao_folha'] ?? null,
        ':venacao_folha_ref' => $dados['venacao_folha_ref'] ?? null,
        
        ':cor_flores' => $dados['cor_flores'] ?? null,
        ':cor_flores_ref' => $dados['cor_flores_ref'] ?? null,
        ':simetria_floral' => $dados['simetria_floral'] ?? null,
        ':simetria_floral_ref' => $dados['simetria_floral_ref'] ?? null,
        ':numero_petalas' => $dados['numero_petalas'] ?? null,
        ':numero_petalas_ref' => $dados['numero_petalas_ref'] ?? null,
        ':disposicao_flores' => $dados['disposicao_flores'] ?? null,
        ':disposicao_flores_ref' => $dados['disposicao_flores_ref'] ?? null,
        ':aroma' => $dados['aroma'] ?? null,
        ':aroma_ref' => $dados['aroma_ref'] ?? null,
        ':tamanho_flor' => $dados['tamanho_flor'] ?? null,
        ':tamanho_flor_ref' => $dados['tamanho_flor_ref'] ?? null,
        
        ':tipo_fruto' => $dados['tipo_fruto'] ?? null,
        ':tipo_fruto_ref' => $dados['tipo_fruto_ref'] ?? null,
        ':tamanho_fruto' => $dados['tamanho_fruto'] ?? null,
        ':tamanho_fruto_ref' => $dados['tamanho_fruto_ref'] ?? null,
        ':cor_fruto' => $dados['cor_fruto'] ?? null,
        ':cor_fruto_ref' => $dados['cor_fruto_ref'] ?? null,
        ':textura_fruto' => $dados['textura_fruto'] ?? null,
        ':textura_fruto_ref' => $dados['textura_fruto_ref'] ?? null,
        ':dispersao_fruto' => $dados['dispersao_fruto'] ?? null,
        ':dispersao_fruto_ref' => $dados['dispersao_fruto_ref'] ?? null,
        ':aroma_fruto' => $dados['aroma_fruto'] ?? null,
        ':aroma_fruto_ref' => $dados['aroma_fruto_ref'] ?? null,
        
        ':tipo_semente' => $dados['tipo_semente'] ?? null,
        ':tipo_semente_ref' => $dados['tipo_semente_ref'] ?? null,
        ':tamanho_semente' => $dados['tamanho_semente'] ?? null,
        ':tamanho_semente_ref' => $dados['tamanho_semente_ref'] ?? null,
        ':cor_semente' => $dados['cor_semente'] ?? null,
        ':cor_semente_ref' => $dados['cor_semente_ref'] ?? null,
        ':textura_semente' => $dados['textura_semente'] ?? null,
        ':textura_semente_ref' => $dados['textura_semente_ref'] ?? null,
        ':quantidade_sementes' => $dados['quantidade_sementes'] ?? null,
        ':quantidade_sementes_ref' => $dados['quantidade_sementes_ref'] ?? null,
        
        ':tipo_caule' => $dados['tipo_caule'] ?? null,
        ':tipo_caule_ref' => $dados['tipo_caule_ref'] ?? null,
        ':estrutura_caule' => $dados['estrutura_caule'] ?? null,
        ':estrutura_caule_ref' => $dados['estrutura_caule_ref'] ?? null,
        ':textura_caule' => $dados['textura_caule'] ?? null,
        ':textura_caule_ref' => $dados['textura_caule_ref'] ?? null,
        ':cor_caule' => $dados['cor_caule'] ?? null,
        ':cor_caule_ref' => $dados['cor_caule_ref'] ?? null,
        ':forma_caule' => $dados['forma_caule'] ?? null,
        ':forma_caule_ref' => $dados['forma_caule_ref'] ?? null,
        ':modificacao_caule' => !empty($dados['modificacao_caule']) ? $dados['modificacao_caule'] : null,
        ':modificacao_caule_ref' => $dados['modificacao_caule_ref'] ?? null,
        ':diametro_caule' => $dados['diametro_caule'] ?? null,
        ':diametro_caule_ref' => $dados['diametro_caule_ref'] ?? null,
        ':ramificacao_caule' => $dados['ramificacao_caule'] ?? null,
        ':ramificacao_caule_ref' => $dados['ramificacao_caule_ref'] ?? null,
        
        ':possui_espinhos' => $dados['possui_espinhos'] ?? null,
        ':possui_espinhos_ref' => $dados['possui_espinhos_ref'] ?? null,
        ':possui_latex' => $dados['possui_latex'] ?? null,
        ':possui_latex_ref' => $dados['possui_latex_ref'] ?? null,
        ':possui_seiva' => $dados['possui_seiva'] ?? null,
        ':possui_seiva_ref' => $dados['possui_seiva_ref'] ?? null,
        ':possui_resina' => $dados['possui_resina'] ?? null,
        ':possui_resina_ref' => $dados['possui_resina_ref'] ?? null,
        
        ':referencias' => $dados['referencias'] ?? null
    ];

    // ===============================
    // 9. INSERIR NA TABELA especies_caracteristicas
    // ===============================
    $sql_inserir = "
        INSERT INTO especies_caracteristicas (
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
            cor_flores,
            cor_flores_ref,
            simetria_floral,
            simetria_floral_ref,
            numero_petalas,
            numero_petalas_ref,
            disposicao_flores,
            disposicao_flores_ref,
            aroma,
            aroma_ref,
            tamanho_flor,
            tamanho_flor_ref,
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
            possui_espinhos,
            possui_espinhos_ref,
            possui_latex,
            possui_latex_ref,
            possui_seiva,
            possui_seiva_ref,
            possui_resina,
            possui_resina_ref,
            referencias,
            versao_dados,
            data_cadastro_botanico
        ) VALUES (
            :especie_id,
            :nome_cientifico_completo,
            :nome_cientifico_completo_ref,
            :sinonimos,
            :sinonimos_ref,
            :nome_popular,
            :nome_popular_ref,
            :familia,
            :familia_ref,
            :forma_folha,
            :forma_folha_ref,
            :filotaxia_folha,
            :filotaxia_folha_ref,
            :tipo_folha,
            :tipo_folha_ref,
            :tamanho_folha,
            :tamanho_folha_ref,
            :textura_folha,
            :textura_folha_ref,
            :margem_folha,
            :margem_folha_ref,
            :venacao_folha,
            :venacao_folha_ref,
            :cor_flores,
            :cor_flores_ref,
            :simetria_floral,
            :simetria_floral_ref,
            :numero_petalas,
            :numero_petalas_ref,
            :disposicao_flores,
            :disposicao_flores_ref,
            :aroma,
            :aroma_ref,
            :tamanho_flor,
            :tamanho_flor_ref,
            :tipo_fruto,
            :tipo_fruto_ref,
            :tamanho_fruto,
            :tamanho_fruto_ref,
            :cor_fruto,
            :cor_fruto_ref,
            :textura_fruto,
            :textura_fruto_ref,
            :dispersao_fruto,
            :dispersao_fruto_ref,
            :aroma_fruto,
            :aroma_fruto_ref,
            :tipo_semente,
            :tipo_semente_ref,
            :tamanho_semente,
            :tamanho_semente_ref,
            :cor_semente,
            :cor_semente_ref,
            :textura_semente,
            :textura_semente_ref,
            :quantidade_sementes,
            :quantidade_sementes_ref,
            :tipo_caule,
            :tipo_caule_ref,
            :estrutura_caule,
            :estrutura_caule_ref,
            :textura_caule,
            :textura_caule_ref,
            :cor_caule,
            :cor_caule_ref,
            :forma_caule,
            :forma_caule_ref,
            :modificacao_caule,
            :modificacao_caule_ref,
            :diametro_caule,
            :diametro_caule_ref,
            :ramificacao_caule,
            :ramificacao_caule_ref,
            :possui_espinhos,
            :possui_espinhos_ref,
            :possui_latex,
            :possui_latex_ref,
            :possui_seiva,
            :possui_seiva_ref,
            :possui_resina,
            :possui_resina_ref,
            :referencias,
            1,
            NOW()
        )
    ";

    try {
        $stmt = $pdo->prepare($sql_inserir);
        $stmt->execute($dados_inserir);
        
        $id_inserido = $pdo->lastInsertId();
        echo "✅ Dados inseridos com sucesso! ID: $id_inserido\n";
        
        // ===============================
        // 10. ATUALIZAR STATUS NA TABELA especies_administrativo
        // ===============================
        $sql_atualizar = "
            UPDATE especies_administrativo
            SET
                status = 'dados_internet',
                data_dados_internet = NOW(),
                data_ultima_atualizacao = NOW()
            WHERE id = :id_especie
        ";
        
        $stmt_atualizar = $pdo->prepare($sql_atualizar);
        $stmt_atualizar->execute([':id_especie' => $id_especie]);
        
        echo "✅ Status da espécie atualizado para 'dados_internet'.\n";
        
        $contador_sucesso++;
        $resultados[] = [
            'arquivo' => $nome_arquivo,
            'status' => 'sucesso',
            'id_especie' => $id_especie,
            'id_registro' => $id_inserido
        ];
        
    } catch (PDOException $e) {
        echo "❌ Erro ao inserir dados: " . $e->getMessage() . "\n";
        $contador_erro++;
        $resultados[] = [
            'arquivo' => $nome_arquivo,
            'status' => 'erro',
            'mensagem' => 'Erro no banco: ' . $e->getMessage()
        ];
    }
}

// ===============================
// 11. RESUMO FINAL
// ===============================
echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 RESUMO DO PROCESSAMENTO\n";
echo str_repeat("=", 60) . "\n";
echo "Total de arquivos encontrados: " . count($arquivos_json) . "\n";
echo "✅ Inserções com sucesso: $contador_sucesso\n";
echo "❌ Erros/Pulados: $contador_erro\n";
echo str_repeat("-", 60) . "\n";

foreach ($resultados as $resultado) {
    $icone = $resultado['status'] == 'sucesso' ? '✅' : ($resultado['status'] == 'pulado' ? '⚠️' : '❌');
    echo "$icone {$resultado['arquivo']}: ";
    
    if ($resultado['status'] == 'sucesso') {
        echo "ID Espécie: {$resultado['id_especie']}, ID Registro: {$resultado['id_registro']}\n";
    } else {
        echo $resultado['mensagem'] . "\n";
    }
}

echo str_repeat("=", 60) . "\n";
echo "📅 Data/Hora: " . date('d/m/Y H:i:s') . "\n";
echo str_repeat("=", 60) . "\n";
?>