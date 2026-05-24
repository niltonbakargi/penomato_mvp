/* -- ============================================================
-- MIGRAÇÃO 002 — Proteger espécies_caracteristicas com ENUM
-- ============================================================
-- Converte todos os campos de características botânicas de
-- VARCHAR(255) para ENUM com os valores predefinidos aceitos.
-- Campos de referência (_ref) e texto livre permanecem varchar.
-- NOTA: colunas divisao_folha e paridade_pinnacao já existem no banco.
-- ============================================================

-- ── PASSO 1: Migrar dados existentes de tipo_folha ────────────
UPDATE `especies_caracteristicas` SET
    `tipo_folha` = 'Composta', `divisao_folha` = 'Pinnada'
    WHERE `tipo_folha` = 'Composta pinnada';

UPDATE `especies_caracteristicas` SET
    `tipo_folha` = 'Composta', `divisao_folha` = 'Bipinnada'
    WHERE `tipo_folha` = 'Composta bipinada';

UPDATE `especies_caracteristicas` SET
    `tipo_folha` = 'Composta', `divisao_folha` = 'Tripinnada'
    WHERE `tipo_folha` = 'Composta tripinada';

UPDATE `especies_caracteristicas` SET
    `tipo_folha` = 'Composta', `divisao_folha` = 'Tetrapinnada'
    WHERE `tipo_folha` = 'Composta tetrapinada';

UPDATE `especies_caracteristicas` SET
    `tipo_folha` = 'Composta', `divisao_folha` = 'Pinnada', `paridade_pinnacao` = 'Paripinnada'
    WHERE `tipo_folha` = 'Composta paripinada';

UPDATE `especies_caracteristicas` SET
    `tipo_folha` = 'Composta', `divisao_folha` = 'Pinnada', `paridade_pinnacao` = 'Imparipinnada'
    WHERE `tipo_folha` = 'Composta imparipinada';

UPDATE `especies_caracteristicas` SET
    `tipo_folha` = 'Composta', `divisao_folha` = 'Trifoliada'
    WHERE `tipo_folha` = 'Composta trifoliada';

-- Corrigir erro de digitação em margem_folha
UPDATE `especies_caracteristicas` SET `margem_folha` = 'Serrada'
    WHERE `margem_folha` = 'Serreada';

-- ── PASSO 2: Anular valores fora do padrão ────────────────────
UPDATE `especies_caracteristicas` SET `forma_folha` = NULL
    WHERE `forma_folha` NOT IN (
        'Lanceolada','Linear','Elíptica','Ovada','Orbicular',
        'Cordiforme','Espatulada','Sagitada','Reniforme','Obovada',
        'Trilobada','Palmada','Lobada'
    ) AND `forma_folha` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `textura_folha` = NULL
    WHERE `textura_folha` NOT IN (
        'Coriácea','Cartácea','Membranácea','Suculenta','Pilosa','Glabra','Rugosa','Cerosa'
    ) AND `textura_folha` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `tipo_folha` = NULL
    WHERE `tipo_folha` NOT IN ('Simples','Composta') AND `tipo_folha` IS NOT NULL;

-- ── PASSO 2b: Anular valores fora do padrão em todos os campos ─
UPDATE `especies_caracteristicas` SET `filotaxia_folha` = NULL
    WHERE `filotaxia_folha` NOT IN ('Alterna','Oposta Simples','Oposta Decussada','Verticilada','Dística','Espiralada') AND `filotaxia_folha` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `tamanho_folha` = NULL
    WHERE `tamanho_folha` NOT IN ('Microfilas (< 2 cm)','Nanofilas (2–7 cm)','Mesofilas (7–20 cm)','Macrófilas (20–50 cm)','Megafilas (> 50 cm)') AND `tamanho_folha` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `margem_folha` = NULL
    WHERE `margem_folha` NOT IN ('Inteira','Serrada','Dentada','Crenada','Ondulada','Lobada','Partida','Revoluta','Involuta') AND `margem_folha` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `venacao_folha` = NULL
    WHERE `venacao_folha` NOT IN ('Reticulada Pinnada','Reticulada Palmada','Paralela','Peninérvea','Dicotômica','Curvinérvea') AND `venacao_folha` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `cor_flores` = NULL
    WHERE `cor_flores` NOT IN ('Brancas','Amarelas','Vermelhas','Rosadas','Roxas','Azuis','Laranjas','Verdes') AND `cor_flores` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `simetria_floral` = NULL
    WHERE `simetria_floral` NOT IN ('Actinomorfa','Zigomorfa','Assimétrica') AND `simetria_floral` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `numero_petalas` = NULL
    WHERE `numero_petalas` NOT IN ('3 pétalas','4 pétalas','5 pétalas','Muitas pétalas') AND `numero_petalas` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `disposicao_flores` = NULL
    WHERE `disposicao_flores` NOT IN ('Isoladas','Inflorescência') AND `disposicao_flores` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `aroma` = NULL
    WHERE `aroma` NOT IN ('Sem cheiro','Aroma suave','Aroma forte','Aroma desagradável') AND `aroma` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `tamanho_flor` = NULL
    WHERE `tamanho_flor` NOT IN ('Pequena','Média') AND `tamanho_flor` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `tipo_fruto` = NULL
    WHERE `tipo_fruto` NOT IN ('Baga','Drupa','Cápsula','Folículo','Legume','Síliqua','Aquênio','Sâmara','Cariopse','Pixídio','Hespéridio','Pepo') AND `tipo_fruto` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `tamanho_fruto` = NULL
    WHERE `tamanho_fruto` NOT IN ('Pequeno','Médio','Grande') AND `tamanho_fruto` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `cor_fruto` = NULL
    WHERE `cor_fruto` NOT IN ('Verde','Amarelo','Vermelho','Roxo','Laranja','Marrom','Preto','Branco') AND `cor_fruto` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `textura_fruto` = NULL
    WHERE `textura_fruto` NOT IN ('Lisa','Rugosa','Coriácea','Peluda','Espinhosa','Cerosa') AND `textura_fruto` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `dispersao_fruto` = NULL
    WHERE `dispersao_fruto` NOT IN ('Zoocórica','Anemocórica','Hidrocórica','Autocórica') AND `dispersao_fruto` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `aroma_fruto` = NULL
    WHERE `aroma_fruto` NOT IN ('Sem cheiro','Aroma suave','Aroma forte','Aroma desagradável') AND `aroma_fruto` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `tipo_semente` = NULL
    WHERE `tipo_semente` NOT IN ('Alada','Carnosa','Dura','Oleaginosa','Plumosa','Ruminada','Arilada') AND `tipo_semente` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `tamanho_semente` = NULL
    WHERE `tamanho_semente` NOT IN ('Pequena','Média','Grande') AND `tamanho_semente` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `cor_semente` = NULL
    WHERE `cor_semente` NOT IN ('Preta','Marrom','Branca','Amarela','Verde') AND `cor_semente` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `textura_semente` = NULL
    WHERE `textura_semente` NOT IN ('Lisa','Rugosa','Estriada','Cerosa') AND `textura_semente` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `quantidade_sementes` = NULL
    WHERE `quantidade_sementes` NOT IN ('Uma','Poucas','Muitas') AND `quantidade_sementes` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `tipo_caule` = NULL
    WHERE `tipo_caule` NOT IN ('Ereto','Prostrado','Rastejante','Trepador','Subterrâneo') AND `tipo_caule` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `textura_caule` = NULL
    WHERE `textura_caule` NOT IN ('Lisa','Rugosa','Sulcada','Fissurada','Cerosa','Espinhosa','Suberosa') AND `textura_caule` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `cor_caule` = NULL
    WHERE `cor_caule` NOT IN ('Marrom','Verde','Cinza','Avermelhado','Alaranjado') AND `cor_caule` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `forma_caule` = NULL
    WHERE `forma_caule` NOT IN ('Cilíndrico','Quadrangular','Achatado','Irregular') AND `forma_caule` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `modificacao_caule` = NULL
    WHERE `modificacao_caule` NOT IN ('Estolão','Cladódio','Rizoma','Tubérculo','Espinhos') AND `modificacao_caule` IS NOT NULL;

UPDATE `especies_caracteristicas` SET `ramificacao_caule` = NULL
    WHERE `ramificacao_caule` NOT IN ('Dicotômica','Monopodial','Simpodial') AND `ramificacao_caule` IS NOT NULL;

-- ── PASSO 3: Converter ENUMs ──────────────────────────────────
ALTER TABLE `especies_caracteristicas`

    -- ── FOLHA ─────────────────────────────────────────────────────
    MODIFY COLUMN `forma_folha` ENUM(
        'Lanceolada','Linear','Elíptica','Ovada','Orbicular',
        'Cordiforme','Espatulada','Sagitada','Reniforme','Obovada',
        'Trilobada','Palmada','Lobada'
    ) DEFAULT NULL,

    MODIFY COLUMN `filotaxia_folha` ENUM(
        'Alterna','Oposta Simples','Oposta Decussada',
        'Verticilada','Dística','Espiralada'
    ) DEFAULT NULL,

    MODIFY COLUMN `tipo_folha` ENUM(
        'Simples','Composta'
    ) DEFAULT NULL,

    MODIFY COLUMN `divisao_folha` ENUM(
        'Trifoliada','Digitada','Pinnada','Bipinnada','Tripinnada','Tetrapinnada'
    ) DEFAULT NULL,

    MODIFY COLUMN `paridade_pinnacao` ENUM(
        'Paripinnada','Imparipinnada'
    ) DEFAULT NULL,

    MODIFY COLUMN `tamanho_folha` ENUM(
        'Microfilas (< 2 cm)','Nanofilas (2–7 cm)',
        'Mesofilas (7–20 cm)','Macrófilas (20–50 cm)',
        'Megafilas (> 50 cm)'
    ) DEFAULT NULL,

    MODIFY COLUMN `textura_folha` ENUM(
        'Coriácea','Cartácea','Membranácea',
        'Suculenta','Pilosa','Glabra','Rugosa','Cerosa'
    ) DEFAULT NULL,

    MODIFY COLUMN `margem_folha` ENUM(
        'Inteira','Serrada','Dentada','Crenada',
        'Ondulada','Lobada','Partida','Revoluta','Involuta'
    ) DEFAULT NULL,

    MODIFY COLUMN `venacao_folha` ENUM(
        'Reticulada Pinnada','Reticulada Palmada',
        'Paralela','Peninérvea','Dicotômica','Curvinérvea'
    ) DEFAULT NULL,

    -- ── FLOR ──────────────────────────────────────────────────────
    MODIFY COLUMN `cor_flores` ENUM(
        'Brancas','Amarelas','Vermelhas','Rosadas',
        'Roxas','Azuis','Laranjas','Verdes'
    ) DEFAULT NULL,

    MODIFY COLUMN `simetria_floral` ENUM(
        'Actinomorfa','Zigomorfa','Assimétrica'
    ) DEFAULT NULL,

    MODIFY COLUMN `numero_petalas` ENUM(
        '3 pétalas','4 pétalas','5 pétalas','Muitas pétalas'
    ) DEFAULT NULL,

    MODIFY COLUMN `disposicao_flores` ENUM(
        'Isoladas','Inflorescência'
    ) DEFAULT NULL,

    MODIFY COLUMN `aroma` ENUM(
        'Sem cheiro','Aroma suave','Aroma forte','Aroma desagradável'
    ) DEFAULT NULL,

    MODIFY COLUMN `tamanho_flor` ENUM(
        'Pequena','Média'
    ) DEFAULT NULL,

    -- ── FRUTO ─────────────────────────────────────────────────────
    MODIFY COLUMN `tipo_fruto` ENUM(
        'Baga','Drupa','Cápsula','Folículo','Legume',
        'Síliqua','Aquênio','Sâmara','Cariopse',
        'Pixídio','Hespéridio','Pepo'
    ) DEFAULT NULL,

    MODIFY COLUMN `tamanho_fruto` ENUM(
        'Pequeno','Médio','Grande'
    ) DEFAULT NULL,

    MODIFY COLUMN `cor_fruto` ENUM(
        'Verde','Amarelo','Vermelho','Roxo',
        'Laranja','Marrom','Preto','Branco'
    ) DEFAULT NULL,

    MODIFY COLUMN `textura_fruto` ENUM(
        'Lisa','Rugosa','Coriácea','Peluda','Espinhosa','Cerosa'
    ) DEFAULT NULL,

    MODIFY COLUMN `dispersao_fruto` ENUM(
        'Zoocórica','Anemocórica','Hidrocórica','Autocórica'
    ) DEFAULT NULL,

    MODIFY COLUMN `aroma_fruto` ENUM(
        'Sem cheiro','Aroma suave','Aroma forte','Aroma desagradável'
    ) DEFAULT NULL,

    -- ── SEMENTE ───────────────────────────────────────────────────
    MODIFY COLUMN `tipo_semente` ENUM(
        'Alada','Carnosa','Dura','Oleaginosa','Plumosa','Ruminada','Arilada'
    ) DEFAULT NULL,

    MODIFY COLUMN `tamanho_semente` ENUM(
        'Pequena','Média','Grande'
    ) DEFAULT NULL,

    MODIFY COLUMN `cor_semente` ENUM(
        'Preta','Marrom','Branca','Amarela','Verde'
    ) DEFAULT NULL,

    MODIFY COLUMN `textura_semente` ENUM(
        'Lisa','Rugosa','Estriada','Cerosa'
    ) DEFAULT NULL,

    MODIFY COLUMN `quantidade_sementes` ENUM(
        'Uma','Poucas','Muitas'
    ) DEFAULT NULL,

    -- ── CAULE ─────────────────────────────────────────────────────
    MODIFY COLUMN `tipo_caule` ENUM(
        'Ereto','Prostrado','Rastejante','Trepador','Subterrâneo'
    ) DEFAULT NULL,

    MODIFY COLUMN `textura_caule` ENUM(
        'Lisa','Rugosa','Sulcada','Fissurada',
        'Cerosa','Espinhosa','Suberosa'
    ) DEFAULT NULL,

    MODIFY COLUMN `cor_caule` ENUM(
        'Marrom','Verde','Cinza','Avermelhado','Alaranjado'
    ) DEFAULT NULL,

    MODIFY COLUMN `forma_caule` ENUM(
        'Cilíndrico','Quadrangular','Achatado','Irregular'
    ) DEFAULT NULL,

    MODIFY COLUMN `modificacao_caule` ENUM(
        'Estolão','Cladódio','Rizoma','Tubérculo','Espinhos'
    ) DEFAULT NULL,

    MODIFY COLUMN `ramificacao_caule` ENUM(
        'Dicotômica','Monopodial','Simpodial'
    ) DEFAULT NULL;

-- ── já eram ENUM: possui_espinhos, possui_latex, possui_seiva, possui_resina
-- ── permanecem VARCHAR: familia, nome_cientifico_completo, sinonimos,
--    nome_popular, e todos os campos _ref (referências bibliográficas)
 */