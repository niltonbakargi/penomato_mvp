-- ============================================================
-- PENOMATO MVP — Correções de banco de dados
-- Gerado em: 2026-11-03
-- Aplicar via phpMyAdmin > banco b62a4147_penomato > aba SQL
-- ============================================================

SET foreign_key_checks = 0;

-- ── 1. Tornar nullable as colunas que receberão ON DELETE SET NULL ─────────────
ALTER TABLE especies_imagens        MODIFY id_usuario_identificador int DEFAULT NULL;
ALTER TABLE exemplares              MODIFY cadastrado_por            int DEFAULT NULL;
ALTER TABLE exemplares              MODIFY especialista_id           int DEFAULT NULL;
ALTER TABLE matrizes                MODIFY cadastrado_por            int DEFAULT NULL;
ALTER TABLE matrizes_comentarios    MODIFY usuario_id                int DEFAULT NULL;
ALTER TABLE matrizes_fotos          MODIFY enviada_por               int DEFAULT NULL;
ALTER TABLE partes_dispensadas      MODIFY dispensado_por            int DEFAULT NULL;
ALTER TABLE temp_imagens_candidatas MODIFY usuario_id                int DEFAULT NULL;
ALTER TABLE historico_alteracoes    MODIFY id_usuario                int DEFAULT NULL;

-- ── 2. Dropar FKs RESTRICT que apontam para usuarios ──────────────────────────
ALTER TABLE historico_alteracoes     DROP FOREIGN KEY fk_historico_usuario;
ALTER TABLE especies_imagens         DROP FOREIGN KEY especies_imagens_ibfk_2;
ALTER TABLE especies_imagens         DROP FOREIGN KEY especies_imagens_ibfk_3;
ALTER TABLE especies_imagens         DROP FOREIGN KEY especies_imagens_ibfk_4;
ALTER TABLE matrizes                 DROP FOREIGN KEY fk_matriz_usuario;
ALTER TABLE matrizes_comentarios     DROP FOREIGN KEY fk_mcoment_usuario;
ALTER TABLE matrizes_fotos           DROP FOREIGN KEY fk_mfoto_usuario;
ALTER TABLE exemplares               DROP FOREIGN KEY fk_exemplar_especialista;
ALTER TABLE exemplares               DROP FOREIGN KEY fk_exemplar_cadastrador;
ALTER TABLE partes_dispensadas       DROP FOREIGN KEY fk_dispensa_usuario;
ALTER TABLE temp_imagens_candidatas  DROP FOREIGN KEY fk_cand_usuario;

-- ── 3. Re-adicionar FKs com ON DELETE SET NULL ────────────────────────────────
ALTER TABLE historico_alteracoes
  ADD CONSTRAINT fk_historico_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE especies_imagens
  ADD CONSTRAINT especies_imagens_ibfk_2
    FOREIGN KEY (id_usuario_identificador) REFERENCES usuarios(id) ON DELETE SET NULL,
  ADD CONSTRAINT especies_imagens_ibfk_3
    FOREIGN KEY (id_usuario_validador) REFERENCES usuarios(id) ON DELETE SET NULL,
  ADD CONSTRAINT especies_imagens_ibfk_4
    FOREIGN KEY (coletor_id) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE matrizes
  ADD CONSTRAINT fk_matriz_usuario
    FOREIGN KEY (cadastrado_por) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE matrizes_comentarios
  ADD CONSTRAINT fk_mcoment_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE matrizes_fotos
  ADD CONSTRAINT fk_mfoto_usuario
    FOREIGN KEY (enviada_por) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE exemplares
  ADD CONSTRAINT fk_exemplar_especialista
    FOREIGN KEY (especialista_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  ADD CONSTRAINT fk_exemplar_cadastrador
    FOREIGN KEY (cadastrado_por) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE partes_dispensadas
  ADD CONSTRAINT fk_dispensa_usuario
    FOREIGN KEY (dispensado_por) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE temp_imagens_candidatas
  ADD CONSTRAINT fk_cand_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL;

-- ── 4. Índices ausentes ───────────────────────────────────────────────────────
ALTER TABLE fila_aprovacao
  ADD INDEX idx_status (status);

ALTER TABLE especies_administrativo
  ADD INDEX idx_autor_dados_internet (autor_dados_internet_id),
  ADD INDEX idx_autor_descrita       (autor_descrita_id),
  ADD INDEX idx_autor_registrada     (autor_registrada_id),
  ADD INDEX idx_autor_revisada       (autor_revisada_id),
  ADD INDEX idx_autor_contestado     (autor_contestado_id),
  ADD INDEX idx_autor_publicado      (autor_publicado_id);

ALTER TABLE tentativas_login
  DROP INDEX ip,
  DROP INDEX criado_em,
  ADD INDEX idx_ip_criado (ip, criado_em);

ALTER TABLE flora_brasil_plantas
  ADD INDEX idx_nome_cientifico (nome_cientifico);

-- ── 5. Remover ENUM 'validador' sem handler no código ────────────────────────
--  (verifique antes: SELECT id, nome FROM usuarios WHERE categoria = 'validador';)
ALTER TABLE usuarios MODIFY categoria
  ENUM('gestor','colaborador','revisor','visitante')
  COLLATE utf8mb4_general_ci DEFAULT 'visitante';

SET foreign_key_checks = 1;
