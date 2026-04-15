-- ============================================================
-- TABELA: flora_brasil_sinonimos
-- Fonte: REFLORA — Lista de Espécies da Flora do Brasil 2020
--        Jardim Botânico do Rio de Janeiro (JBRJ) — CC-BY
-- Uso: busca por sinônimos no módulo Flora do Cerrado
-- ============================================================

CREATE TABLE IF NOT EXISTS `flora_brasil_sinonimos` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `sinonimo`    VARCHAR(150)  NOT NULL,   -- nome inválido/antigo
    `autor`       VARCHAR(100)           DEFAULT NULL,
    `familia`     VARCHAR(50)            DEFAULT NULL,
    `nome_aceito` VARCHAR(150)  NOT NULL,   -- nome válido atual
    `tipo`        VARCHAR(20)            DEFAULT NULL, -- heterotipico | homotipico | basiônimo

    PRIMARY KEY (`id`),
    INDEX `idx_sinonimo`    (`sinonimo`),
    INDEX `idx_nome_aceito` (`nome_aceito`),
    FULLTEXT INDEX `ft_sinonimo` (`sinonimo`)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Sinônimos nomenclaturais do Cerrado — REFLORA/JBRJ CC-BY';
