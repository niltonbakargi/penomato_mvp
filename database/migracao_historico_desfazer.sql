-- ================================================
-- MIGRAÇÃO: suporte a desfazer ações do usuário
-- Executar uma vez no banco local e em produção
-- ================================================

ALTER TABLE historico_alteracoes
  ADD COLUMN IF NOT EXISTS revertida           TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Ação já foi desfeita',
  ADD COLUMN IF NOT EXISTS dados_extras        TEXT       DEFAULT NULL       COMMENT 'JSON com dados extras para revert (imagem_id, caminho, etc)',
  ADD COLUMN IF NOT EXISTS notificacao_enviada TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Notificação de expiração de janela enviada';

-- Índice para buscas por usuário + não revertidas
ALTER TABLE historico_alteracoes
  ADD INDEX IF NOT EXISTS idx_usuario_revertida (id_usuario, revertida, data_alteracao);
