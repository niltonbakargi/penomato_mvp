-- ============================================================
-- TABELA: tokens_alteracao_email
-- Execute no phpMyAdmin antes de usar a funcionalidade
-- de alteração de e-mail.
-- ============================================================

CREATE TABLE IF NOT EXISTS `tokens_alteracao_email` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `usuario_id`  INT(11) NOT NULL,
  `novo_email`  VARCHAR(150) NOT NULL,
  `token`       VARCHAR(64) NOT NULL,
  `expira_em`   DATETIME NOT NULL,
  `usado`       TINYINT(1) NOT NULL DEFAULT 0,
  `criado_em`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `fk_token_alt_email_usuario`
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
