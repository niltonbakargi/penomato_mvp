-- ============================================================
-- BANCO DE MATRIZES FLORESTAIS — PENOMATO MVP
-- Execute este script no phpMyAdmin
-- ============================================================

CREATE TABLE IF NOT EXISTS `matrizes` (
  `id`                   INT(11)        NOT NULL AUTO_INCREMENT,
  `codigo`               VARCHAR(6)     NOT NULL,
  `especie_nome`         VARCHAR(255)   DEFAULT NULL,
  `especie_nome_popular` VARCHAR(255)   DEFAULT NULL,
  `latitude`             DECIMAL(10,8)  NOT NULL,
  `longitude`            DECIMAL(11,8)  NOT NULL,
  `foto_geral`           VARCHAR(500)   NOT NULL,
  `observacoes`          TEXT           DEFAULT NULL,
  `cadastrado_por`       INT(11)        NOT NULL,
  `data_cadastro`        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status`               ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_codigo` (`codigo`),
  KEY `fk_matriz_usuario` (`cadastrado_por`),
  CONSTRAINT `fk_matriz_usuario` FOREIGN KEY (`cadastrado_por`) REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `matrizes_fotos` (
  `id`           INT(11)      NOT NULL AUTO_INCREMENT,
  `matriz_id`    INT(11)      NOT NULL,
  `parte`        ENUM('folha','flor','fruto','casca','semente') NOT NULL,
  `caminho_foto` VARCHAR(500) NOT NULL,
  `enviada_por`  INT(11)      NOT NULL,
  `data_envio`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_mfoto_matriz`   (`matriz_id`),
  KEY `fk_mfoto_usuario`  (`enviada_por`),
  CONSTRAINT `fk_mfoto_matriz`  FOREIGN KEY (`matriz_id`)   REFERENCES `matrizes`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mfoto_usuario` FOREIGN KEY (`enviada_por`) REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `matrizes_comentarios` (
  `id`         INT(11)  NOT NULL AUTO_INCREMENT,
  `matriz_id`  INT(11)  NOT NULL,
  `usuario_id` INT(11)  NOT NULL,
  `texto`      TEXT     NOT NULL,
  `data`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_mcoment_matriz`   (`matriz_id`),
  KEY `fk_mcoment_usuario`  (`usuario_id`),
  CONSTRAINT `fk_mcoment_matriz`  FOREIGN KEY (`matriz_id`)  REFERENCES `matrizes`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mcoment_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
