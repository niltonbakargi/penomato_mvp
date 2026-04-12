-- ============================================================
-- MIGRAÇÃO 003 — Busca automática de imagens
-- Data: 2026-04-08
-- ============================================================
-- Execute este arquivo no phpMyAdmin (local e Hostinger)
-- Ordem: 1) nova tabela  2) alter em especies_imagens
-- ============================================================

-- ------------------------------------------------------------
-- 1. NOVA TABELA: temp_imagens_candidatas
--    Armazena temporariamente os resultados da busca automática
--    (iNaturalist / Wikimedia) antes da curadoria humana.
--    Nenhuma imagem é baixada até o colaborador aprovar.
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `temp_imagens_candidatas` (
  `id`               INT           NOT NULL AUTO_INCREMENT,

  -- Vínculo com o fluxo de importação
  `especie_id`       INT           NOT NULL,
  `usuario_id`       INT           NOT NULL,
  `temp_id`          varchar(100)  NOT NULL,

  -- Parte da planta que originou a busca
  `parte_planta`     enum('folha','flor','fruto','caule','semente','habito','exsicata_completa','detalhe') NOT NULL,

  -- URLs (browser carrega direto da fonte — nada baixado ainda)
  `url_foto`         varchar(2048) NOT NULL,
  `url_thumbnail`    varchar(2048) DEFAULT NULL,

  -- Metadados da fonte
  `fonte`            enum('inaturalist','wikimedia','gbif','outro') NOT NULL,
  `fonte_url`        varchar(2048) DEFAULT NULL,
  `fonte_nome`       varchar(255)  DEFAULT NULL,
  `id_externo`       varchar(100)  DEFAULT NULL,

  -- Metadados do autor e licença
  `autor`            varchar(255)  DEFAULT NULL,
  `licenca`          varchar(100)  DEFAULT NULL,

  -- Metadados geográficos e temporais
  `local_coleta`     varchar(255)  DEFAULT NULL,
  `latitude`         decimal(10,7) DEFAULT NULL,
  `longitude`        decimal(10,7) DEFAULT NULL,
  `data_observacao`  date          DEFAULT NULL,

  -- Dimensões da imagem
  `largura_px`       INT           DEFAULT NULL,
  `altura_px`        INT           DEFAULT NULL,

  -- Pontuação calculada pelo PHP
  `pontuacao`        SMALLINT      DEFAULT 0,

  -- Curadoria
  `status`           enum('pendente','aprovado','rejeitado') DEFAULT 'pendente',
  `principal`        tinyint(1)    DEFAULT 0,
  -- principal = 1 → esta imagem vai para o artigo (apenas uma por parte)
  -- principal = 0 → aprovada mas fica só no acervo para ML futuro

  -- Controle de tempo
  `criado_em`        datetime      NOT NULL DEFAULT current_timestamp(),
  `expira_em`        datetime      GENERATED ALWAYS AS (DATE_ADD(`criado_em`, INTERVAL 24 HOUR)) STORED,

  PRIMARY KEY (`id`),
  KEY `idx_temp_id`       (`temp_id`),
  KEY `idx_especie_parte` (`especie_id`, `parte_planta`),
  KEY `idx_status`        (`status`),
  KEY `idx_expira_em`     (`expira_em`),

  CONSTRAINT `fk_cand_especie` FOREIGN KEY (`especie_id`) REFERENCES `especies_administrativo` (`id`),
  CONSTRAINT `fk_cand_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ------------------------------------------------------------
-- 2. ALTER: especies_imagens — adiciona coluna principal
--    principal = 1 → imagem exibida no artigo gerado (uma por parte)
--    principal = 0 → imagem no acervo (usada no futuro para ML/identificação)
-- ------------------------------------------------------------

ALTER TABLE `especies_imagens`
  ADD COLUMN `principal` tinyint(1) NOT NULL DEFAULT 0
    COMMENT '1 = exibida no artigo; 0 = acervo para ML futuro'
  AFTER `licenca`;
