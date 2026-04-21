-- =============================================================
-- PENOMATO MVP — Script de limpeza para estado inicial de produção
-- Gerado em: 2026-04-21
-- =============================================================
-- O que este script PRESERVA:
--   • flora_brasil_plantas    → catálogo Reflora/JBRJ (Cerrado)
--   • flora_brasil_sinonimos  → sinônimos Reflora/JBRJ
--   • especies_administrativo → lista das 99 espécies (todas com status 'sem_dados')
--   • usuarios                → usuário gestor cadastrado
--
-- O que este script APAGA:
--   • artigos
--   • especies_caracteristicas
--   • especies_imagens
--   • exemplares
--   • fila_aprovacao
--   • historico_alteracoes
--   • partes_dispensadas
--   • sugestoes_usuario
--   • temp_imagens_candidatas
--   • tentativas_login
--   • tokens_alteracao_email
--   • tokens_recuperacao_senha
--   • tokens_verificacao_email
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `artigos`;
TRUNCATE TABLE `especies_caracteristicas`;
TRUNCATE TABLE `especies_imagens`;
DELETE FROM `exemplares`;
ALTER TABLE `exemplares` AUTO_INCREMENT = 1;
TRUNCATE TABLE `fila_aprovacao`;
TRUNCATE TABLE `historico_alteracoes`;
TRUNCATE TABLE `partes_dispensadas`;
TRUNCATE TABLE `sugestoes_usuario`;
TRUNCATE TABLE `temp_imagens_candidatas`;
TRUNCATE TABLE `tentativas_login`;
TRUNCATE TABLE `tokens_alteracao_email`;
TRUNCATE TABLE `tokens_recuperacao_senha`;
TRUNCATE TABLE `tokens_verificacao_email`;

-- Reseta status de todas as espécies para sem_dados
UPDATE `especies_administrativo` SET
    `status`                  = 'sem_dados',
    `data_dados_internet`     = NULL,
    `data_descrita`           = NULL,
    `data_registrada`         = NULL,
    `data_revisada`           = NULL,
    `data_contestado`         = NULL,
    `data_publicado`          = NULL,
    `autor_dados_internet_id` = NULL,
    `autor_descrita_id`       = NULL,
    `autor_registrada_id`     = NULL,
    `autor_revisada_id`       = NULL,
    `autor_contestado_id`     = NULL,
    `autor_publicado_id`      = NULL,
    `motivo_contestado`       = NULL,
    `data_revisao`            = NULL,
    `observacoes_revisao`     = NULL,
    `observacoes`             = NULL,
    `versao_registro`         = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- Verificação
SELECT 'flora_brasil_plantas'    AS tabela, COUNT(*) AS registros FROM flora_brasil_plantas
UNION ALL
SELECT 'flora_brasil_sinonimos', COUNT(*) FROM flora_brasil_sinonimos
UNION ALL
SELECT 'especies_administrativo', COUNT(*) FROM especies_administrativo
UNION ALL
SELECT 'usuarios',                COUNT(*) FROM usuarios;
