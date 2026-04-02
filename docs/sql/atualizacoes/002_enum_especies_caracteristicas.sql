-- ============================================================
-- MIGRAÇÃO 002 — Proteger espécies_caracteristicas com ENUM
-- ============================================================
-- Converte todos os campos de características botânicas de
-- VARCHAR(255) para ENUM com os valores predefinidos aceitos.
-- Campos de referência (_ref) e texto livre permanecem varchar.
-- ============================================================

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
        'Simples',
        'Composta pinnada','Composta bipinada',
        'Composta tripinada','Composta tetrapinada'
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
        'Alada','Carnosa','Dura','Oleosa','Peluda'
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

    MODIFY COLUMN `estrutura_caule` ENUM(
        'Lenhoso','Herbáceo','Suculento'
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

    MODIFY COLUMN `diametro_caule` ENUM(
        'Fino','Médio','Grosso'
    ) DEFAULT NULL,

    MODIFY COLUMN `ramificacao_caule` ENUM(
        'Dicotômica','Monopodial','Simpodial'
    ) DEFAULT NULL;

-- ── já eram ENUM: possui_espinhos, possui_latex, possui_seiva, possui_resina
-- ── permanecem VARCHAR: familia, nome_cientifico_completo, sinonimos,
--    nome_popular, e todos os campos _ref (referências bibliográficas)
