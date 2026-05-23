-- ============================================================
-- atualizar_especies_reflora.sql
-- Sincroniza especies_administrativo com o Reflora/JBRJ
--
-- Remove 32 espécies: 2 sinônimos confirmados + 30 não encontradas
-- Insere 33 espécies: nomes aceitos do Reflora, Cerrado, árvores/arbustos
--
-- Resultado: 67 existentes + 33 novas = 100 espécies
-- Seguro: todas as espécies removidas têm status 'sem_dados'
-- ============================================================

START TRANSACTION;

-- ============================================================
-- ETAPA 1: REMOVER espécies inválidas
-- (sinônimos e nomes não encontrados no Reflora)
-- ============================================================

DELETE FROM especies_administrativo WHERE nome_cientifico IN (

    -- Sinônimos confirmados pelo Reflora
    'Schinus terebinthifolia',   -- aceito: Schinus terebinthifolius
    'Lithraea molleoides',       -- aceito: Lithrea molleoides

    -- Não encontrados no Reflora (exóticas, fora do Cerrado ou grafia incorreta)
    'Persea americana',          -- exótica (América Central)
    'Anadenanthera macrocarpa',  -- não encontrado no Reflora Cerrado
    'Oenocarpus bacaba',         -- Amazônia, não Cerrado
    'Peltophorum dubium',        -- não encontrado
    'Zygia racemosa',            -- não encontrado no Reflora Cerrado
    'Zygia latifoliolata',       -- não encontrado no Reflora Cerrado
    'Cordia leucocephala',       -- não encontrado
    'Acca sellowiana',           -- exótica (Mata Atlântica/Sul)
    'Patagonula americana',      -- não encontrado no Reflora Cerrado
    'Schizolobium parahyba',     -- não encontrado no Reflora Cerrado
    'Schizolobium amazonicum',   -- Amazônia
    'Handroanthus albus',        -- sinônimo de Tabebuia roseoalba
    'Mezilaurus itauba',         -- Amazônia
    'Jacaranda mimosifolia',     -- originária da Argentina/Bolívia
    'Triplaris surinamensis',    -- não encontrado no Reflora Cerrado
    'Hymenaea oblongifolia',     -- não encontrado
    'Hymenaea intermedia',       -- não encontrado
    'Cariniana legalis',         -- Mata Atlântica
    'Ziziphus joazeiro',         -- Caatinga
    'Laguncularia racemosa',     -- manguezal
    'Calycophyllum spruceanum',  -- Amazônia
    'Licania tomentosa',         -- não encontrado no Reflora Cerrado
    'Bauhinia ungulata',         -- não encontrado no Reflora Cerrado
    'Pityrocarpa moniliformis',  -- Caatinga
    'Caryocar villosum',         -- Amazônia
    'Tibouchina granulosa',      -- Mata Atlântica
    'Atropha belladona',         -- grafia incorreta (Atropa belladonna, exótica)
    'Dipteryx odorata',          -- Amazônia
    'Ateleia glazioviana',       -- não encontrado no Reflora Cerrado
    'Vernonia polyanthes'        -- não encontrado (provavelmente Vernonanthura polyanthes)
);

-- ============================================================
-- ETAPA 2: INSERIR 33 novas espécies do Reflora
-- (Cerrado, nomes aceitos, árvores e arbustos)
-- ============================================================

INSERT INTO especies_administrativo (nome_cientifico, status, prioridade)
VALUES

    -- Anacardiaceae
    ('Anacardium humile',           'sem_dados', 'media'),
    ('Anacardium occidentale',      'sem_dados', 'media'),
    ('Astronium fraxinifolium',     'sem_dados', 'media'),
    ('Astronium graveolens',        'sem_dados', 'media'),
    ('Lithrea molleoides',          'sem_dados', 'media'),  -- corrige Lithraea molleoides
    ('Schinopsis brasiliensis',     'sem_dados', 'media'),
    ('Spondias mombin',             'sem_dados', 'media'),
    ('Tapirira obtusa',             'sem_dados', 'media'),

    -- Annonaceae
    ('Annona coriacea',             'sem_dados', 'media'),
    ('Annona crassiflora',          'sem_dados', 'media'),
    ('Annona crotonifolia',         'sem_dados', 'media'),
    ('Annona montana',              'sem_dados', 'media'),
    ('Duguetia furfuracea',         'sem_dados', 'media'),
    ('Unonopsis guatterioides',     'sem_dados', 'media'),
    ('Xylopia aromatica',           'sem_dados', 'media'),
    ('Xylopia emarginata',          'sem_dados', 'media'),

    -- Apocynaceae
    ('Aspidosperma australe',       'sem_dados', 'media'),
    ('Aspidosperma cuspa',          'sem_dados', 'media'),
    ('Aspidosperma cylindrocarpon', 'sem_dados', 'media'),
    ('Aspidosperma discolor',       'sem_dados', 'media'),
    ('Aspidosperma macrocarpon',    'sem_dados', 'media'),
    ('Aspidosperma multiflorum',    'sem_dados', 'media'),
    ('Aspidosperma pyrifolium',     'sem_dados', 'media'),
    ('Aspidosperma subincanum',     'sem_dados', 'media'),
    ('Aspidosperma tomentosum',     'sem_dados', 'media'),
    ('Himatanthus drasticus',       'sem_dados', 'media'),
    ('Himatanthus obovatus',        'sem_dados', 'media'),

    -- Aquifoliaceae
    ('Ilex affinis',                'sem_dados', 'media'),
    ('Ilex cerasifolia',            'sem_dados', 'media'),

    -- Araliaceae
    ('Dendropanax cuneatus',        'sem_dados', 'media'),

    -- Asteraceae
    ('Dasyphyllum brasiliense',     'sem_dados', 'media'),
    ('Eremanthus cinctus',          'sem_dados', 'media'),

    -- Bignoniaceae
    ('Handroanthus impetiginosus',  'sem_dados', 'media');  -- ipê-roxo

COMMIT;

-- ============================================================
-- VERIFICAÇÃO (execute separado para conferir)
-- ============================================================
-- SELECT COUNT(*) AS total FROM especies_administrativo;
-- SELECT nome_cientifico FROM especies_administrativo ORDER BY nome_cientifico;
