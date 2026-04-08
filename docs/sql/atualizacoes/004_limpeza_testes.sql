-- ============================================================
-- RESET COMPLETO DO BANCO — Penomato MVP
-- Data: 2026-04-09
-- Propósito: Apaga TODOS os dados para importação limpa.
--            A estrutura das tabelas é preservada.
-- Execute no phpMyAdmin (local e Hostinger).
--
-- Usa DELETE FROM (não TRUNCATE) para evitar erro #1701 no
-- phpMyAdmin, que não persiste SET FOREIGN_KEY_CHECKS entre
-- statements. Ordem: tabelas filhas antes das mães.
-- ============================================================

-- Tabelas sem dependentes (ou que são filhas)
DELETE FROM artigos;
DELETE FROM especies_caracteristicas;
DELETE FROM especies_imagens;
DELETE FROM exemplares;
DELETE FROM fila_aprovacao;
DELETE FROM historico_alteracoes;
DELETE FROM partes_dispensadas;
DELETE FROM sugestoes_usuario;
DELETE FROM temp_imagens_candidatas;
DELETE FROM tentativas_login;
DELETE FROM tokens_alteracao_email;
DELETE FROM tokens_recuperacao_senha;
DELETE FROM tokens_verificacao_email;

-- Tabelas mãe (referenciadas por FK)
DELETE FROM especies_administrativo;
DELETE FROM usuarios;

-- Resetar auto_increment
ALTER TABLE artigos                  AUTO_INCREMENT = 1;
ALTER TABLE especies_administrativo  AUTO_INCREMENT = 1;
ALTER TABLE especies_caracteristicas AUTO_INCREMENT = 1;
ALTER TABLE especies_imagens         AUTO_INCREMENT = 1;
ALTER TABLE exemplares               AUTO_INCREMENT = 1;
ALTER TABLE fila_aprovacao           AUTO_INCREMENT = 1;
ALTER TABLE historico_alteracoes     AUTO_INCREMENT = 1;
ALTER TABLE partes_dispensadas       AUTO_INCREMENT = 1;
ALTER TABLE sugestoes_usuario        AUTO_INCREMENT = 1;
ALTER TABLE temp_imagens_candidatas  AUTO_INCREMENT = 1;
ALTER TABLE tentativas_login         AUTO_INCREMENT = 1;
ALTER TABLE tokens_alteracao_email   AUTO_INCREMENT = 1;
ALTER TABLE tokens_recuperacao_senha AUTO_INCREMENT = 1;
ALTER TABLE tokens_verificacao_email AUTO_INCREMENT = 1;
ALTER TABLE usuarios                 AUTO_INCREMENT = 1;
