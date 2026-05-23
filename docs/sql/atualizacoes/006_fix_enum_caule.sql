-- ============================================================
-- 006_fix_enum_caule.sql
-- Corrige ENUMs de caule para alinhar com vocabulario_botanico.php
-- ============================================================

-- ── 1. Limpar valores que não existirão no novo ENUM ────────

-- tipo_caule: os valores antigos (Ereto, Prostrado...) são errados
-- categoria era habito, não tipo morfológico do caule
UPDATE `especies_caracteristicas`
    SET `tipo_caule` = NULL
    WHERE `tipo_caule` NOT IN ('Tronco','Estipe','Colmo','Liana','Haste','Escapo')
      AND `tipo_caule` IS NOT NULL;

-- textura_caule: mapear 'Espinhosa' → 'Aculeada' (termo botânico correto)
UPDATE `especies_caracteristicas`
    SET `textura_caule` = 'Aculeada'
    WHERE `textura_caule` = 'Espinhosa';

-- cor_caule: mapear valores antigos para novos
UPDATE `especies_caracteristicas` SET `cor_caule` = 'Acinzentado'   WHERE `cor_caule` = 'Cinza';
UPDATE `especies_caracteristicas` SET `cor_caule` = 'Esverdeado'    WHERE `cor_caule` = 'Verde';
UPDATE `especies_caracteristicas`
    SET `cor_caule` = NULL
    WHERE `cor_caule` NOT IN ('Marrom','Acinzentado','Avermelhado','Alaranjado','Esbranquiçado','Esverdeado','Pardacento')
      AND `cor_caule` IS NOT NULL;

-- forma_caule: NULL out valores fora do novo ENUM
UPDATE `especies_caracteristicas`
    SET `forma_caule` = NULL
    WHERE `forma_caule` NOT IN ('Cilíndrico','Quadrangular','Triangular','Achatado','Alado')
      AND `forma_caule` IS NOT NULL;

-- ── 2. Alterar ENUMs ────────────────────────────────────────

ALTER TABLE `especies_caracteristicas`

    MODIFY COLUMN `tipo_caule` ENUM(
        'Tronco','Estipe','Colmo','Liana','Haste','Escapo'
    ) DEFAULT NULL,

    MODIFY COLUMN `textura_caule` ENUM(
        'Lisa','Rugosa','Sulcada','Fissurada',
        'Estriada','Escamosa','Suberosa','Aculeada','Cerosa'
    ) DEFAULT NULL,

    MODIFY COLUMN `cor_caule` ENUM(
        'Marrom','Acinzentado','Avermelhado','Alaranjado',
        'Esbranquiçado','Esverdeado','Pardacento'
    ) DEFAULT NULL,

    MODIFY COLUMN `forma_caule` ENUM(
        'Cilíndrico','Quadrangular','Triangular','Achatado','Alado'
    ) DEFAULT NULL;
