<?php
session_start();

// ===============================
// CONEXÃO COM O BANCO
// ===============================
$pdo = new PDO(
    "mysql:host=localhost;dbname=penomato;charset=utf8mb4",
    "root",
    "",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

/* ==========================================================
   MODO POST → SALVAR CARACTERÍSTICAS (com todas as referências)
========================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ===============================
    // 1. ESPÉCIE SELECIONADA
    // ===============================
    $id_especie = intval($_POST['especie_id'] ?? 0);

    if ($id_especie <= 0) {
        die("Erro: espécie inválida.");
    }

    // ===============================
    // 2. INSERIR CARACTERÍSTICAS NA TABELA especies_caracteristicas
    // ===============================
    $sql = "
        INSERT INTO especies_caracteristicas (
            -- Dados principais
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
            
            -- REFERÊNCIAS (35 campos)
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
            -- Dados principais
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
            1,
            NOW(),
            
            -- REFERÊNCIAS
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

    $stmt = $pdo->prepare($sql);
    
    // Mapeamento dos campos do formulário para os parâmetros
    $stmt->execute([
        // Dados principais (42)
        ':especie_id' => $id_especie,
        ':nome_popular' => $_POST['nome_popular'] ?? null,
        ':familia' => $_POST['familia'] ?? null,
        ':forma_folha' => $_POST['forma_folha'] ?? null,
        ':filotaxia_folha' => $_POST['filotaxia_folha'] ?? null,
        ':tipo_folha' => $_POST['tipo_folha'] ?? null,
        ':tamanho_folha' => $_POST['tamanho_folha'] ?? null,
        ':textura_folha' => $_POST['textura_folha'] ?? null,
        ':margem_folha' => $_POST['margem_folha'] ?? null,
        ':venacao_folha' => $_POST['venacao_folha'] ?? null,
        ':cor_flores' => $_POST['cor_flores'] ?? null,
        ':simetria_floral' => $_POST['simetria_floral'] ?? null,
        ':numero_petalas' => $_POST['numero_petalas'] ?? null,
        ':disposicao_flores' => $_POST['disposicao_flores'] ?? null,
        ':aroma' => $_POST['aroma'] ?? null,
        ':tamanho_flor' => $_POST['tamanho_flor'] ?? null,
        ':tipo_fruto' => $_POST['tipo_fruto'] ?? null,
        ':tamanho_fruto' => $_POST['tamanho_fruto'] ?? null,
        ':cor_fruto' => $_POST['cor_fruto'] ?? null,
        ':textura_fruto' => $_POST['textura_fruto'] ?? null,
        ':dispersao_fruto' => $_POST['dispersao_fruto'] ?? null,
        ':aroma_fruto' => $_POST['aroma_fruto'] ?? null,
        ':tipo_semente' => $_POST['tipo_semente'] ?? null,
        ':tamanho_semente' => $_POST['tamanho_semente'] ?? null,
        ':cor_semente' => $_POST['cor_semente'] ?? null,
        ':textura_semente' => $_POST['textura_semente'] ?? null,
        ':quantidade_sementes' => $_POST['quantidade_sementes'] ?? null,
        ':tipo_caule' => $_POST['tipo_caule'] ?? null,
        ':estrutura_caule' => $_POST['estrutura_caule'] ?? null,
        ':textura_caule' => $_POST['textura_caule'] ?? null,
        ':cor_caule' => $_POST['cor_caule'] ?? null,
        ':forma_caule' => $_POST['forma_caule'] ?? null,
        ':modificacao_caule' => $_POST['modificacao_caule'] ?? null,
        ':diametro_caule' => $_POST['diametro_caule'] ?? null,
        ':ramificacao_caule' => $_POST['ramificacao_caule'] ?? null,
        ':possui_espinhos' => $_POST['possui_espinhos'] ?? null,
        ':possui_latex' => $_POST['possui_latex'] ?? null,
        ':possui_seiva' => $_POST['possui_seiva'] ?? null,
        ':possui_resina' => $_POST['possui_resina'] ?? null,
        ':referencias' => $_POST['referencias'] ?? null,
        
        // Referências (35) - Correção dos nomes dos campos
        ':familia_ref' => $_POST['familia_ref'] ?? null,
        ':forma_folha_ref' => $_POST['forma_folha_ref'] ?? null,
        ':filotaxia_folha_ref' => $_POST['filotaxia_folha_ref'] ?? null,
        ':tipo_folha_ref' => $_POST['tipo_folha_ref'] ?? null,
        ':tamanho_folha_ref' => $_POST['tamanho_folha_ref'] ?? null,
        ':textura_folha_ref' => $_POST['textura_folha_ref'] ?? null,
        ':margem_folha_ref' => $_POST['margem_folha_ref'] ?? null,
        ':venacao_folha_ref' => $_POST['venacao_folha_ref'] ?? null,
        ':cor_flores_ref' => $_POST['cor_flores_ref'] ?? null,
        ':simetria_floral_ref' => $_POST['simetria_floral_ref'] ?? null,
        ':numero_petalas_ref' => $_POST['numero_petalas_ref'] ?? null,
        ':disposicao_flores_ref' => $_POST['disposicao_flores_ref'] ?? null,
        ':aroma_ref' => $_POST['aroma_ref'] ?? null,
        ':tamanho_flor_ref' => $_POST['tamanho_flor_ref'] ?? null,
        ':tipo_fruto_ref' => $_POST['tipo_fruto_ref'] ?? null,
        ':tamanho_fruto_ref' => $_POST['tamanho_fruto_ref'] ?? null,
        ':cor_fruto_ref' => $_POST['cor_fruto_ref'] ?? null,
        ':textura_fruto_ref' => $_POST['textura_fruto_ref'] ?? null,
        ':dispersao_fruto_ref' => $_POST['dispersao_fruto_ref'] ?? null,
        ':aroma_fruto_ref' => $_POST['aroma_fruto_ref'] ?? null,
        ':tipo_semente_ref' => $_POST['tipo_semente_ref'] ?? null,
        ':tamanho_semente_ref' => $_POST['tamanho_semente_ref'] ?? null,
        ':cor_semente_ref' => $_POST['cor_semente_ref'] ?? null,
        ':textura_semente_ref' => $_POST['textura_semente_ref'] ?? null,
        ':quantidade_sementes_ref' => $_POST['quantidade_sementes_ref'] ?? null,
        ':tipo_caule_ref' => $_POST['tipo_caule_ref'] ?? null,
        ':estrutura_caule_ref' => $_POST['estrutura_caule_ref'] ?? null,
        ':textura_caule_ref' => $_POST['textura_caule_ref'] ?? null,
        ':cor_caule_ref' => $_POST['cor_caule_ref'] ?? null,
        ':forma_caule_ref' => $_POST['forma_caule_ref'] ?? null,
        ':modificacao_caule_ref' => $_POST['modificacao_caule_ref'] ?? null,
        ':possui_espinhos_ref' => $_POST['possui_espinhos_ref'] ?? null,
        ':possui_latex_ref' => $_POST['possui_latex_ref'] ?? null,
        ':possui_seiva_ref' => $_POST['possui_seiva_ref'] ?? null,
        ':possui_resina_ref' => $_POST['possui_resina_ref'] ?? null
    ]);

    // ===============================
    // 3. ATUALIZAR STATUS DA ESPÉCIE NA TABELA especies_administrativo
    // ===============================
    $sql_update = "
        UPDATE especies_administrativo
        SET
            status_caracteristicas = 'completo',
            status_identificacao = 'identificada',
            data_identificacao = NOW(),
            data_ultima_atualizacao = NOW()
        WHERE id = :id_especie
    ";

    $stmt = $pdo->prepare($sql_update);
    $stmt->execute([
        ':id_especie' => $id_especie
    ]);

    // ===============================
    // 4. REDIRECIONAR PARA PÁGINA DE SUCESSO
    // ===============================
    header("Location: sucesso_cadastro.php?id=$id_especie");
    exit;
}

// Se não for POST, mostrar erro
die("Método não permitido. Use o formulário para enviar os dados.");
?>