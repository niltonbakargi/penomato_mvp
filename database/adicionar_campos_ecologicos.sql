-- ============================================================
-- Migração: adiciona campos ecológicos/distributivos vindos do REFLORA
-- Tabela: especies_caracteristicas
-- Data: 2026-05-26
-- ============================================================

ALTER TABLE `especies_caracteristicas`
  ADD COLUMN `forma_vida`              varchar(255) DEFAULT NULL AFTER `familia`,
  ADD COLUMN `forma_vida_ref`          varchar(100) DEFAULT NULL AFTER `forma_vida`,
  ADD COLUMN `origem`                  enum('Nativa','Exótica','Naturalizada','Cultivada') DEFAULT NULL AFTER `forma_vida_ref`,
  ADD COLUMN `origem_ref`              varchar(100) DEFAULT NULL AFTER `origem`,
  ADD COLUMN `endemismo`               enum('Endêmica','Não endêmica') DEFAULT NULL AFTER `origem_ref`,
  ADD COLUMN `endemismo_ref`           varchar(100) DEFAULT NULL AFTER `endemismo`,
  ADD COLUMN `biomas`                  text DEFAULT NULL AFTER `endemismo_ref`,
  ADD COLUMN `biomas_ref`              varchar(100) DEFAULT NULL AFTER `biomas`,
  ADD COLUMN `estados_ocorrencia`      text DEFAULT NULL AFTER `biomas_ref`,
  ADD COLUMN `estados_ocorrencia_ref`  varchar(100) DEFAULT NULL AFTER `estados_ocorrencia`;
