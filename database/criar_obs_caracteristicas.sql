-- ============================================================
-- Cria tabela de observações por atributo das características
-- ============================================================
CREATE TABLE IF NOT EXISTS especies_caracteristicas_obs (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    especie_id    INT          NOT NULL,
    campo         VARCHAR(100) NOT NULL,
    observacao    TEXT         NOT NULL,
    criado_em     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_obs (especie_id, campo),
    CONSTRAINT fk_obs_especie FOREIGN KEY (especie_id)
        REFERENCES especies_administrativo(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
