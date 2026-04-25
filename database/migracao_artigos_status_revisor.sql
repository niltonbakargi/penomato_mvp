-- ============================================================
-- MIGRAÇÃO: artigos — novo fluxo de status + revisor_id
-- Data: 2026-04-25
-- Descrição:
--   1. Atualiza ENUM status para refletir o fluxo real do artigo
--   2. Migra registros antigos para os novos valores equivalentes
--   3. Adiciona revisor_id (especialista atribuído para revisar)
--   4. Adiciona datas de cada etapa do fluxo
--   5. Cria índices e FK necessários
-- ============================================================

-- ------------------------------------------------------------
-- PASSO 1: Ampliar o ENUM para aceitar valores antigos E novos
-- (necessário antes de migrar os dados)
-- ------------------------------------------------------------
ALTER TABLE `artigos`
  MODIFY `status` ENUM(
    'rascunho',
    'confirmado',
    'registrado',
    'revisando',
    'revisado',
    'publicado',
    'em_revisao',
    'aprovado'
  ) NOT NULL DEFAULT 'rascunho';

-- ------------------------------------------------------------
-- PASSO 2: Migrar registros com status antigos
-- ------------------------------------------------------------

-- 'em_revisao' → 'revisando'  (especialista já tinha aberto)
UPDATE `artigos` SET `status` = 'revisando'  WHERE `status` = 'em_revisao';

-- 'aprovado'   → 'revisado'   (já havia sido aprovado)
UPDATE `artigos` SET `status` = 'revisado'   WHERE `status` = 'aprovado';

-- ------------------------------------------------------------
-- PASSO 3: Remover os valores antigos do ENUM
-- ------------------------------------------------------------
ALTER TABLE `artigos`
  MODIFY `status` ENUM(
    'rascunho',
    'confirmado',
    'registrado',
    'revisando',
    'revisado',
    'publicado'
  ) NOT NULL DEFAULT 'rascunho';

-- ------------------------------------------------------------
-- PASSO 4: Adicionar revisor_id
-- Quem está atribuído para revisar (diferente de revisado_por,
-- que registra quem de fato concluiu a revisão)
-- ------------------------------------------------------------
ALTER TABLE `artigos`
  ADD COLUMN `revisor_id` INT(11) NULL DEFAULT NULL
    AFTER `revisado_por`;

-- ------------------------------------------------------------
-- PASSO 5: Adicionar datas de cada etapa do fluxo
-- ------------------------------------------------------------
ALTER TABLE `artigos`
  ADD COLUMN `data_confirmado`  DATETIME NULL DEFAULT NULL AFTER `data_revisao`,
  ADD COLUMN `data_registrado`  DATETIME NULL DEFAULT NULL AFTER `data_confirmado`,
  ADD COLUMN `data_revisando`   DATETIME NULL DEFAULT NULL AFTER `data_registrado`,
  ADD COLUMN `data_revisado`    DATETIME NULL DEFAULT NULL AFTER `data_revisando`,
  ADD COLUMN `data_publicado`   DATETIME NULL DEFAULT NULL AFTER `data_revisado`;

-- ------------------------------------------------------------
-- PASSO 6: Índice + FK para revisor_id
-- ------------------------------------------------------------
ALTER TABLE `artigos`
  ADD KEY `idx_revisor_id` (`revisor_id`);

ALTER TABLE `artigos`
  ADD CONSTRAINT `artigos_fk_revisor`
    FOREIGN KEY (`revisor_id`)
    REFERENCES `usuarios` (`id`)
    ON DELETE SET NULL;
