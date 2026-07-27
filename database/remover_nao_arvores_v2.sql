-- ============================================================
-- remover_nao_arvores_v2.sql
-- Remove todas as espécies que NÃO são árvores puras,
-- usando a tabela flora_brasil_plantas como referência.
--
-- Critério de EXCLUSÃO (remove):
--   1. Sem registro no Flora do Brasil
--   2. formas_vida contém 'Arbusto' (inclui Árvore/Arbusto)
--   3. formas_vida não contém 'Árvore' (lianas, bambus, ervas, cactos...)
--
-- Critério de PERMANÊNCIA (mantém):
--   formas_vida contém 'Árvore' E não contém 'Arbusto'
--
-- Execute os blocos nesta ordem:
--   1. VERIFICAÇÃO → confira a lista
--   2. DELETE      → execute dentro da transação
--   3. PÓS-VERIFICAÇÃO → confirme o resultado
-- ============================================================


-- ============================================================
-- 1. VERIFICAÇÃO PRÉVIA
-- ============================================================
/*
SELECT
    ea.id,
    ea.nome_cientifico,
    ea.status,
    COALESCE(fbp.formas_vida, '-- sem registro --') AS formas_vida
FROM especies_administrativo ea
LEFT JOIN flora_brasil_plantas fbp
    ON fbp.nome_cientifico = ea.nome_cientifico COLLATE utf8mb4_unicode_ci
WHERE
    fbp.id IS NULL
    OR fbp.formas_vida LIKE '%Arbusto%'
    OR fbp.formas_vida NOT LIKE '%Árvore%'
ORDER BY fbp.formas_vida, ea.nome_cientifico;
*/

-- Contagem resumida por formas_vida:
/*
SELECT
    COALESCE(fbp.formas_vida, '-- sem registro --') AS formas_vida,
    COUNT(*) AS total
FROM especies_administrativo ea
LEFT JOIN flora_brasil_plantas fbp
    ON fbp.nome_cientifico = ea.nome_cientifico COLLATE utf8mb4_unicode_ci
WHERE
    fbp.id IS NULL
    OR fbp.formas_vida LIKE '%Arbusto%'
    OR fbp.formas_vida NOT LIKE '%Árvore%'
GROUP BY fbp.formas_vida
ORDER BY total DESC;
*/


-- ============================================================
-- 2. DELETE (execute dentro da transação)
-- Tabelas filhas com ON DELETE CASCADE são tratadas automaticamente.
-- temp_imagens_candidatas NÃO tem CASCADE → passo manual obrigatório.
-- ============================================================

START TRANSACTION;

-- Passo 1: temp_imagens_candidatas (sem CASCADE)
DELETE tic
FROM temp_imagens_candidatas tic
INNER JOIN especies_administrativo ea ON ea.id = tic.especie_id
LEFT JOIN flora_brasil_plantas fbp
    ON fbp.nome_cientifico = ea.nome_cientifico COLLATE utf8mb4_unicode_ci
WHERE
    fbp.id IS NULL
    OR fbp.formas_vida LIKE '%Arbusto%'
    OR fbp.formas_vida NOT LIKE '%Árvore%';

-- Passo 2: especies_administrativo (CASCADE para todas as demais)
DELETE ea
FROM especies_administrativo ea
LEFT JOIN flora_brasil_plantas fbp
    ON fbp.nome_cientifico = ea.nome_cientifico COLLATE utf8mb4_unicode_ci
WHERE
    fbp.id IS NULL
    OR fbp.formas_vida LIKE '%Arbusto%'
    OR fbp.formas_vida NOT LIKE '%Árvore%';

-- Quantas foram removidas:
SELECT ROW_COUNT() AS especies_removidas;

COMMIT;


-- ============================================================
-- 3. PÓS-VERIFICAÇÃO
-- ============================================================
/*
-- Total restante (somente árvores puras):
SELECT COUNT(*) AS total_arvores FROM especies_administrativo;

-- Confirmar que não sobrou nenhuma não-árvore:
SELECT ea.nome_cientifico, fbp.formas_vida
FROM especies_administrativo ea
INNER JOIN flora_brasil_plantas fbp
    ON fbp.nome_cientifico = ea.nome_cientifico COLLATE utf8mb4_unicode_ci
WHERE
    fbp.formas_vida LIKE '%Arbusto%'
    OR fbp.formas_vida NOT LIKE '%Árvore%'
ORDER BY ea.nome_cientifico;

-- Espécies sem registro no Flora do Brasil que ficaram:
SELECT ea.nome_cientifico
FROM especies_administrativo ea
LEFT JOIN flora_brasil_plantas fbp
    ON fbp.nome_cientifico = ea.nome_cientifico COLLATE utf8mb4_unicode_ci
WHERE fbp.id IS NULL
ORDER BY ea.nome_cientifico;
*/
