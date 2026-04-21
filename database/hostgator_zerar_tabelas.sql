-- =============================================================
-- HOSTGATOR — Zerar tabelas antes de reimportar
-- Ordem respeita dependências de FK (filhos antes dos pais)
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Filhos primeiro (têm FK apontando para outras tabelas)
DROP TABLE IF EXISTS `artigos`;
DROP TABLE IF EXISTS `especies_caracteristicas`;
DROP TABLE IF EXISTS `especies_imagens`;
DROP TABLE IF EXISTS `exemplares`;
DROP TABLE IF EXISTS `fila_aprovacao`;
DROP TABLE IF EXISTS `historico_alteracoes`;
DROP TABLE IF EXISTS `partes_dispensadas`;
DROP TABLE IF EXISTS `sugestoes_usuario`;
DROP TABLE IF EXISTS `temp_imagens_candidatas`;
DROP TABLE IF EXISTS `tentativas_login`;
DROP TABLE IF EXISTS `tokens_alteracao_email`;
DROP TABLE IF EXISTS `tokens_recuperacao_senha`;
DROP TABLE IF EXISTS `tokens_verificacao_email`;

-- especies_administrativo tem FK para usuarios — cai antes
DROP TABLE IF EXISTS `especies_administrativo`;

-- Pais (referenciados)
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `flora_brasil_plantas`;
DROP TABLE IF EXISTS `flora_brasil_sinonimos`;

SET FOREIGN_KEY_CHECKS = 1;
