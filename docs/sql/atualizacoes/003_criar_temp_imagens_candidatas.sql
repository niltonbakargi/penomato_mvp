-- ============================================================
-- MIGRAÇÃO 003 — Tabela de candidatas para busca automática de imagens
-- Criada em: 2026-04-08
-- Propósito: Armazena temporariamente as imagens sugeridas pela busca
--            automática (iNaturalist, Wikimedia) para curadoria humana.
--            Nenhuma imagem é baixada antes da aprovação.
-- ============================================================

CREATE TABLE IF NOT EXISTS `temp_imagens_candidatas` (
  `id`               int(11)       NOT NULL AUTO_INCREMENT,

  -- Vínculo com o fluxo de importação
  `especie_id`       int(11)       NOT NULL,
  `usuario_id`       int(11)       NOT NULL,
  `temp_id`          varchar(100)  NOT NULL,   -- mesmo temp_id da sessão importacao_temporaria

  -- Parte da planta que originou a busca
  `parte_planta`     enum('folha','flor','fruto','caule','semente','habito','exsicata_completa','detalhe') NOT NULL,

  -- URLs (nada é baixado ainda — o browser carrega direto da fonte)
  `url_foto`         varchar(2048) NOT NULL,
  `url_thumbnail`    varchar(2048) DEFAULT NULL,

  -- Metadados da fonte
  `fonte`            enum('inaturalist','wikimedia','gbif','outro') NOT NULL,
  `fonte_url`        varchar(2048) DEFAULT NULL,   -- URL da página de origem
  `fonte_nome`       varchar(255)  DEFAULT NULL,   -- ex: "iNaturalist", "Wikimedia Commons"
  `id_externo`       varchar(100)  DEFAULT NULL,   -- ID da observação na fonte (ex: iNat obs ID)

  -- Metadados do autor e licença
  `autor`            varchar(255)  DEFAULT NULL,
  `licenca`          varchar(100)  DEFAULT NULL,   -- ex: "CC BY 4.0", "CC0"

  -- Metadados geográficos e temporais
  `local_coleta`     varchar(255)  DEFAULT NULL,   -- ex: "Campo Grande, MS"
  `latitude`         decimal(10,7) DEFAULT NULL,
  `longitude`        decimal(10,7) DEFAULT NULL,
  `data_observacao`  date          DEFAULT NULL,

  -- Metadados técnicos da imagem
  `largura_px`       int(11)       DEFAULT NULL,
  `altura_px`        int(11)       DEFAULT NULL,

  -- Pontuação calculada pelo PHP (critérios: research_grade, localidade, licença, resolução)
  `pontuacao`        smallint(6)   DEFAULT 0,

  -- Status de curadoria
  `status`           enum('pendente','aprovado','rejeitado') DEFAULT 'pendente',

  -- Controle de tempo (registros expiram em 24h para limpeza automática)
  `criado_em`        datetime      DEFAULT current_timestamp(),
  `expira_em`        datetime      GENERATED ALWAYS AS (DATE_ADD(`criado_em`, INTERVAL 24 HOUR)) STORED,

  PRIMARY KEY (`id`),
  KEY `idx_temp_id`       (`temp_id`),
  KEY `idx_especie_parte` (`especie_id`, `parte_planta`),
  KEY `idx_status`        (`status`),
  KEY `idx_expira_em`     (`expira_em`),

  CONSTRAINT `fk_candidatas_especie`  FOREIGN KEY (`especie_id`) REFERENCES `especies_administrativo` (`id`),
  CONSTRAINT `fk_candidatas_usuario`  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
