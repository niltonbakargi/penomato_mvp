-- Migração 005: remove campos do caule eliminados na revisão botânica (2026-04-27)
-- Campos removidos: estrutura_caule, estrutura_caule_ref, diametro_caule, diametro_caule_ref
-- Motivo: estrutura_caule (Lenhoso/Herbáceo/Suculento) e diametro_caule (Fino/Médio/Grosso)
--         substituídos por tipo_caule morfológico (Tronco/Estipe/Colmo/Liana/Haste/Escapo)

ALTER TABLE especies_caracteristicas
    DROP COLUMN estrutura_caule,
    DROP COLUMN estrutura_caule_ref,
    DROP COLUMN diametro_caule,
    DROP COLUMN diametro_caule_ref;
