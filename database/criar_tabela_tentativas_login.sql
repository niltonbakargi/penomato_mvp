-- ============================================================
-- TABELA: tentativas_login
-- Registra tentativas de login falhas por IP para
-- proteção básica contra brute force.
-- Execute no phpMyAdmin antes de ativar a proteção.
-- ============================================================

CREATE TABLE IF NOT EXISTS `tentativas_login` (
  `id`         INT(11) NOT NULL AUTO_INCREMENT,
  `ip`         VARCHAR(45) NOT NULL,
  `email`      VARCHAR(150) NOT NULL DEFAULT '',
  `criado_em`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`),
  KEY `criado_em` (`criado_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
