-- ============================================================
-- MIGRAÇÃO: ajustes pós-análise do banco
-- Penomato MVP — 2026-03-16
-- Execute no phpMyAdmin antes de continuar os testes.
-- ============================================================

-- ============================================================
-- 1. COLUNAS FALTANDO NA TABELA usuarios
-- ============================================================

ALTER TABLE `usuarios`
  ADD COLUMN `foto_perfil` VARCHAR(255)  DEFAULT NULL AFTER `bio`,
  ADD COLUMN `instituicao` VARCHAR(255)  DEFAULT NULL AFTER `foto_perfil`,
  ADD COLUMN `lattes`      VARCHAR(500)  DEFAULT NULL AFTER `instituicao`,
  ADD COLUMN `orcid`       VARCHAR(20)   DEFAULT NULL AFTER `lattes`;

-- ============================================================
-- 2. ATIVAR GESTORES EXISTENTES (status_verificacao pendente)
--    O admin (ID 1) estava bloqueado pelo fluxo de verificação.
--    Gestores cadastrados manualmente nunca precisam de e-mail.
-- ============================================================

UPDATE `usuarios`
SET `status_verificacao` = 'verificado'
WHERE `categoria` = 'gestor';

-- ============================================================
-- 3. FK FALTANDO EM tokens_recuperacao_senha
-- ============================================================

ALTER TABLE `tokens_recuperacao_senha`
  ADD CONSTRAINT `fk_token_recuperacao_usuario`
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
    ON DELETE CASCADE;

-- ============================================================
-- 4. FK FALTANDO EM tokens_verificacao_email
-- ============================================================

ALTER TABLE `tokens_verificacao_email`
  ADD CONSTRAINT `fk_token_verificacao_usuario`
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`)
    ON DELETE CASCADE;
