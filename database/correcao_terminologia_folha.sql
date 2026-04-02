-- ============================================================
-- CORREÇÃO: Terminologia botânica — campos de folha
-- Data: 2026-04-02
-- ============================================================

-- PASSO 1: Migrar valores antigos antes de alterar o ENUM

-- forma_folha: Oval → Ovada
UPDATE `especies_caracteristicas` SET `forma_folha` = 'Ovada'         WHERE `forma_folha` = 'Oval';
-- forma_folha: Composta pinnada/bipinada eram tipos, não formas — zera
UPDATE `especies_caracteristicas` SET `forma_folha` = NULL             WHERE `forma_folha` IN ('Composta pinnada', 'Composta bipinada');

-- filotaxia_folha: Rosetada não é padrão de filotaxia — zera
UPDATE `especies_caracteristicas` SET `filotaxia_folha` = NULL         WHERE `filotaxia_folha` = 'Rosetada';

-- tamanho_folha: corrige gênero (masculino → feminino)
UPDATE `especies_caracteristicas` SET `tamanho_folha` = 'Microfilas (< 2 cm)'   WHERE `tamanho_folha` = 'Microfilos (< 2 cm)';
UPDATE `especies_caracteristicas` SET `tamanho_folha` = 'Nanofilas (2–7 cm)'    WHERE `tamanho_folha` = 'Nanofilos (2–7 cm)';
UPDATE `especies_caracteristicas` SET `tamanho_folha` = 'Mesofilas (7–20 cm)'   WHERE `tamanho_folha` = 'Mesofilos (7–20 cm)';
UPDATE `especies_caracteristicas` SET `tamanho_folha` = 'Macrófilas (20–50 cm)' WHERE `tamanho_folha` = 'Macrófilos (20–50 cm)';

-- ============================================================
-- PASSO 2: Alterar os ENUMs com os valores corretos
-- ============================================================

ALTER TABLE `especies_caracteristicas`

    MODIFY COLUMN `forma_folha` ENUM(
        'Lanceolada','Linear','Elíptica','Ovada','Orbicular',
        'Cordiforme','Espatulada','Sagitada','Reniforme','Obovada',
        'Trilobada','Palmada','Lobada'
    ) DEFAULT NULL,

    MODIFY COLUMN `filotaxia_folha` ENUM(
        'Alterna','Oposta Simples','Oposta Decussada',
        'Verticilada','Dística','Espiralada'
    ) DEFAULT NULL,

    MODIFY COLUMN `tamanho_folha` ENUM(
        'Microfilas (< 2 cm)','Nanofilas (2–7 cm)',
        'Mesofilas (7–20 cm)','Macrófilas (20–50 cm)',
        'Megafilas (> 50 cm)'
    ) DEFAULT NULL;
