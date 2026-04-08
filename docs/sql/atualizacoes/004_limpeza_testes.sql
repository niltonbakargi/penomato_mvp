-- ============================================================
-- RESET COMPLETO DO BANCO — Penomato MVP
-- Data: 2026-04-09
-- Propósito: Apaga TODOS os dados para importação limpa.
--            A estrutura das tabelas é preservada.
-- Execute no phpMyAdmin (local e Hostinger).
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE artigos;
TRUNCATE TABLE especies_administrativo;
TRUNCATE TABLE especies_caracteristicas;
TRUNCATE TABLE especies_imagens;
TRUNCATE TABLE exemplares;
TRUNCATE TABLE fila_aprovacao;
TRUNCATE TABLE historico_alteracoes;
TRUNCATE TABLE partes_dispensadas;
TRUNCATE TABLE sugestoes_usuario;
TRUNCATE TABLE temp_imagens_candidatas;
TRUNCATE TABLE tentativas_login;
TRUNCATE TABLE tokens_alteracao_email;
TRUNCATE TABLE tokens_recuperacao_senha;
TRUNCATE TABLE tokens_verificacao_email;
TRUNCATE TABLE usuarios;

SET FOREIGN_KEY_CHECKS = 1;
