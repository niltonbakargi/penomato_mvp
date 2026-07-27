-- ============================================================
-- remover_nao_arvores.sql
-- Remove espécies não-arbóreas inseridas por engano na lista
-- de árvores do MS (bambus, lianas, arbustos, subarbustos)
--
-- Execute o bloco de VERIFICAÇÃO primeiro,
-- depois execute o bloco de DELETE.
-- ============================================================

-- ============================================================
-- VERIFICAÇÃO PRÉVIA (execute antes, confira o resultado)
-- ============================================================
/*
SELECT ea.id, ea.nome_cientifico, ea.status, fbp.formas_vida
FROM especies_administrativo ea
LEFT JOIN flora_brasil_plantas fbp ON fbp.nome_cientifico = ea.nome_cientifico
WHERE ea.nome_cientifico IN (

    -- BAMBUS
    'Actinocladum verticillatum',
    'Bambusa vulgaris',
    'Eremocaulon capitatum',
    'Filgueirasia arenicola',
    'Guadua capitata',
    'Guadua paniculata',

    -- LIANAS / TREPADEIRAS
    'Abuta grandifolia',
    'Banisteriopsis latifolia',
    'Combretum laxum',
    'Peritassa dulcis',
    'Ptilochaeta densiflora',
    'Ptilochaeta glabra',
    'Rourea psammophila',
    'Salacia elliptica',
    'Tontelea micrantha',

    -- ARBUSTOS / SUBARBUSTOS
    'Andira humilis',
    'Boehmeria pavonii',
    'Chamaecrista dumalis',
    'Chamaecrista multiseta',
    'Cnidoscolus paucistamineus',
    'Cordyline spectabilis',
    'Graffenrieda weddellii',
    'Hyptidendron canum',
    'Hyptidendron glutinosum',
    'Jablonskia congesta',
    'Kaunia rufescens',
    'Lantana camara',
    'Leandra aurea',
    'Macairea radula',
    'Mimosa flocculosa',
    'Mimosa hexandra',
    'Mimosa laticifera',
    'Phyllanthus poeppigianus',
    'Piper aduncum',
    'Retiniphyllum kuhlmannii',

    -- CACTO
    'Brasiliopuntia brasiliensis'

)
ORDER BY fbp.formas_vida, ea.nome_cientifico;
*/

-- ============================================================
-- TAMBÉM VERIFIQUE se alguma outra espécie do BD
-- não tem 'Árvore' no REFLORA (pode ter passado despercebida)
-- ============================================================
/*
SELECT ea.nome_cientifico, fbp.formas_vida
FROM especies_administrativo ea
INNER JOIN flora_brasil_plantas fbp ON fbp.nome_cientifico = ea.nome_cientifico
WHERE fbp.formas_vida NOT LIKE '%Árvore%'
ORDER BY fbp.formas_vida, ea.nome_cientifico;
*/

-- ============================================================
-- DELETE
-- Todas as tabelas filhas têm ON DELETE CASCADE, exceto
-- temp_imagens_candidatas — tratada manualmente no passo 1.
-- ============================================================

START TRANSACTION;

-- Passo 1: temp_imagens_candidatas (sem CASCADE)
DELETE tic
FROM temp_imagens_candidatas tic
INNER JOIN especies_administrativo ea ON ea.id = tic.especie_id
WHERE ea.nome_cientifico IN (
    'Actinocladum verticillatum',
    'Bambusa vulgaris',
    'Eremocaulon capitatum',
    'Filgueirasia arenicola',
    'Guadua capitata',
    'Guadua paniculata',
    'Abuta grandifolia',
    'Banisteriopsis latifolia',
    'Combretum laxum',
    'Peritassa dulcis',
    'Ptilochaeta densiflora',
    'Ptilochaeta glabra',
    'Rourea psammophila',
    'Salacia elliptica',
    'Tontelea micrantha',
    'Andira humilis',
    'Boehmeria pavonii',
    'Chamaecrista dumalis',
    'Chamaecrista multiseta',
    'Cnidoscolus paucistamineus',
    'Cordyline spectabilis',
    'Graffenrieda weddellii',
    'Hyptidendron canum',
    'Hyptidendron glutinosum',
    'Jablonskia congesta',
    'Kaunia rufescens',
    'Lantana camara',
    'Leandra aurea',
    'Macairea radula',
    'Mimosa flocculosa',
    'Mimosa hexandra',
    'Mimosa laticifera',
    'Phyllanthus poeppigianus',
    'Piper aduncum',
    'Retiniphyllum kuhlmannii',
    'Brasiliopuntia brasiliensis'
);

-- Passo 2: especies_administrativo (CASCADE para todas as demais)
DELETE FROM especies_administrativo
WHERE nome_cientifico IN (

    -- BAMBUS (6)
    'Actinocladum verticillatum',
    'Bambusa vulgaris',
    'Eremocaulon capitatum',
    'Filgueirasia arenicola',
    'Guadua capitata',
    'Guadua paniculata',

    -- LIANAS / TREPADEIRAS (9)
    'Abuta grandifolia',
    'Banisteriopsis latifolia',
    'Combretum laxum',
    'Peritassa dulcis',
    'Ptilochaeta densiflora',
    'Ptilochaeta glabra',
    'Rourea psammophila',
    'Salacia elliptica',
    'Tontelea micrantha',

    -- ARBUSTOS / SUBARBUSTOS (16)
    'Andira humilis',
    'Boehmeria pavonii',
    'Chamaecrista dumalis',
    'Chamaecrista multiseta',
    'Cnidoscolus paucistamineus',
    'Cordyline spectabilis',
    'Graffenrieda weddellii',
    'Hyptidendron canum',
    'Hyptidendron glutinosum',
    'Jablonskia congesta',
    'Kaunia rufescens',
    'Lantana camara',
    'Leandra aurea',
    'Macairea radula',
    'Mimosa flocculosa',
    'Mimosa hexandra',
    'Mimosa laticifera',
    'Phyllanthus poeppigianus',
    'Piper aduncum',
    'Retiniphyllum kuhlmannii',

    -- CACTO (1)
    'Brasiliopuntia brasiliensis'

);

-- Confirme o total removido antes de commitar
-- (deve ser até 36 linhas)
SELECT ROW_COUNT() AS removidas;

COMMIT;

-- ============================================================
-- PÓS-VERIFICAÇÃO
-- ============================================================
/*
SELECT COUNT(*) AS total_especies FROM especies_administrativo;

-- Checar se alguma não-árvore ainda ficou
SELECT ea.nome_cientifico, fbp.formas_vida
FROM especies_administrativo ea
INNER JOIN flora_brasil_plantas fbp ON fbp.nome_cientifico = ea.nome_cientifico
WHERE fbp.formas_vida NOT LIKE '%Árvore%'
ORDER BY ea.nome_cientifico;
*/
