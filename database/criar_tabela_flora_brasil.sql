-- ============================================================
-- TABELA: flora_brasil_plantas  (versão Cerrado MVP)
-- Fonte: REFLORA — Lista de Espécies da Flora do Brasil 2020
--        Jardim Botânico do Rio de Janeiro (JBRJ) — CC-BY
-- Uso: módulo público "Flora do Cerrado" — Penomato
-- ============================================================

DROP TABLE IF EXISTS `flora_brasil_plantas`;

CREATE TABLE `flora_brasil_plantas` (
    `id`                 INT UNSIGNED  NOT NULL,
    `grupo`              VARCHAR(30)   NOT NULL DEFAULT '',  -- Angiospermas | Gimnospermas
    `familia`            VARCHAR(50)   NOT NULL DEFAULT '',
    `genero`             VARCHAR(50)   NOT NULL DEFAULT '',
    `nome_cientifico`    VARCHAR(150)  NOT NULL DEFAULT '',  -- Gênero + epiteto
    `autor`              VARCHAR(100)           DEFAULT NULL,
    `origem`             VARCHAR(20)            DEFAULT NULL, -- Nativa | Naturalizada | Cultivada
    `endemica`           VARCHAR(30)            DEFAULT NULL, -- é endêmica | não é endêmica | desconhecido
    `formas_vida`        VARCHAR(100)           DEFAULT NULL, -- ex: "Árvore, Arbusto"
    `distr_uf`           VARCHAR(150)           DEFAULT NULL, -- ex: "MS, GO, MT"
    `dom_fitogeografico` VARCHAR(100)           DEFAULT NULL, -- ex: "Cerrado, Mata Atlântica"
    `nomes_vernaculares` TEXT                   DEFAULT NULL,

    PRIMARY KEY (`id`),
    INDEX `idx_grupo`            (`grupo`),
    INDEX `idx_familia`          (`familia`),
    INDEX `idx_origem`           (`origem`),
    INDEX `idx_endemica`         (`endemica`),
    FULLTEXT INDEX `ft_nome`     (`nome_cientifico`, `nomes_vernaculares`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='REFLORA/JBRJ — espécies do Cerrado, nomes aceitos';
