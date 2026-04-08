-- ============================================================
-- LIMPEZA DO BANCO — Penomato MVP
-- Data: 2026-04-09
-- Propósito: Remove dados acumulados durante testes de desenvolvimento.
--            Mantém os dados legítimos da Acca sellowiana (especie_id=32)
--            e os cadastros reais de usuários e espécies.
--
-- INSTRUÇÃO DE USO:
--   1. Execute o bloco "DIAGNÓSTICO ANTES" para ver o estado atual.
--   2. Execute os blocos de limpeza desejados.
--   3. Execute o bloco "DIAGNÓSTICO DEPOIS" para confirmar.
--
-- Execute no phpMyAdmin (local e Hostinger).
-- ============================================================


-- ============================================================
-- DIAGNÓSTICO ANTES DA LIMPEZA
-- Execute este SELECT antes para saber o que será afetado.
-- ============================================================

SELECT 'artigos'                AS tabela, COUNT(*) AS total FROM artigos
UNION ALL
SELECT 'especies_administrativo',           COUNT(*) FROM especies_administrativo
UNION ALL
SELECT 'especies_caracteristicas',          COUNT(*) FROM especies_caracteristicas
UNION ALL
SELECT 'especies_imagens (total)',          COUNT(*) FROM especies_imagens
UNION ALL
SELECT 'especies_imagens (id > 6)',         COUNT(*) FROM especies_imagens WHERE id > 6
UNION ALL
SELECT 'temp_imagens_candidatas',           COUNT(*) FROM temp_imagens_candidatas
UNION ALL
SELECT 'fila_aprovacao',                    COUNT(*) FROM fila_aprovacao
UNION ALL
SELECT 'historico_alteracoes',              COUNT(*) FROM historico_alteracoes
UNION ALL
SELECT 'tokens_recuperacao_senha',          COUNT(*) FROM tokens_recuperacao_senha
UNION ALL
SELECT 'tokens_verificacao_email',          COUNT(*) FROM tokens_verificacao_email
UNION ALL
SELECT 'tokens_alteracao_email',            COUNT(*) FROM tokens_alteracao_email
UNION ALL
SELECT 'tentativas_login',                  COUNT(*) FROM tentativas_login
UNION ALL
SELECT 'usuarios',                          COUNT(*) FROM usuarios;


-- ============================================================
-- 1. TABELA TEMPORÁRIA DE CANDIDATAS
--    Projetada para ser efêmera: URLs de busca automática que
--    expiram em 24h. Pode ser truncada a qualquer momento.
-- ============================================================
TRUNCATE TABLE temp_imagens_candidatas;


-- ============================================================
-- 2. IMAGENS DE TESTE ACUMULADAS
--    IDs 1–6 são as imagens legítimas da Acca sellowiana salvas
--    no primeiro ciclo completo (dump 2026-04-08).
--    Tudo acima foi gerado nos testes do modal de busca.
-- ============================================================
DELETE FROM especies_imagens WHERE id > 6;

-- Resetar o AUTO_INCREMENT para o próximo após o último legítimo
ALTER TABLE especies_imagens AUTO_INCREMENT = 7;


-- ============================================================
-- 3. ARTIGOS DE TESTE
--    Mantém apenas o artigo id=1 (Acca sellowiana, especie_id=32).
--    Remove qualquer rascunho gerado por testes com outras espécies.
-- ============================================================
DELETE FROM artigos WHERE id > 1;

ALTER TABLE artigos AUTO_INCREMENT = 2;


-- ============================================================
-- 4. CARACTERÍSTICAS DE ESPÉCIES DE TESTE
--    Mantém apenas os dados da Acca sellowiana (especie_id=32).
--    Remove características de espécies usadas em testes.
-- ============================================================
DELETE FROM especies_caracteristicas WHERE especie_id != 32;


-- ============================================================
-- 5. STATUS DE ESPÉCIES USADAS EM TESTES
--    Qualquer espécie além da 32 que tenha avançado de status
--    durante testes é resetada para 'sem_dados'.
-- ============================================================
UPDATE especies_administrativo
SET
    status                   = 'sem_dados',
    data_dados_internet      = NULL,
    autor_dados_internet_id  = NULL,
    data_descrita            = NULL,
    autor_descrita_id        = NULL,
    data_registrada          = NULL,
    autor_registrada_id      = NULL,
    data_ultima_atualizacao  = data_ultima_atualizacao   -- sem efeito, só para não disparar ON UPDATE
WHERE id != 32
  AND status != 'sem_dados';


-- ============================================================
-- 6. FILA DE APROVAÇÃO E HISTÓRICO DE TESTES
--    Itens gerados por importações de teste.
--    Se houver itens legítimos (aprovados/revisados), ajuste o WHERE.
-- ============================================================
DELETE FROM fila_aprovacao WHERE status = 'pendente';
TRUNCATE TABLE historico_alteracoes;


-- ============================================================
-- 7. TOKENS EXPIRADOS E USADOS
--    Tokens válidos e não usados são preservados.
-- ============================================================
DELETE FROM tokens_recuperacao_senha
WHERE expira_em < NOW() OR usado = 1;

DELETE FROM tokens_verificacao_email
WHERE expira_em < NOW() OR usado = 1;

DELETE FROM tokens_alteracao_email
WHERE expira_em < NOW() OR usado = 1;


-- ============================================================
-- 8. TENTATIVAS DE LOGIN ANTIGAS (mais de 7 dias)
-- ============================================================
DELETE FROM tentativas_login
WHERE criado_em < NOW() - INTERVAL 7 DAY;


-- ============================================================
-- DIAGNÓSTICO DEPOIS DA LIMPEZA
-- Execute este SELECT para confirmar o estado final.
-- ============================================================

SELECT 'artigos'                AS tabela, COUNT(*) AS total FROM artigos
UNION ALL
SELECT 'especies_administrativo',           COUNT(*) FROM especies_administrativo
UNION ALL
SELECT 'especies_caracteristicas',          COUNT(*) FROM especies_caracteristicas
UNION ALL
SELECT 'especies_imagens',                  COUNT(*) FROM especies_imagens
UNION ALL
SELECT 'temp_imagens_candidatas',           COUNT(*) FROM temp_imagens_candidatas
UNION ALL
SELECT 'fila_aprovacao',                    COUNT(*) FROM fila_aprovacao
UNION ALL
SELECT 'historico_alteracoes',              COUNT(*) FROM historico_alteracoes
UNION ALL
SELECT 'usuarios',                          COUNT(*) FROM usuarios;

-- Resultado esperado após limpeza:
--   artigos                  → 1   (Acca sellowiana)
--   especies_administrativo  → 32  (lista completa intacta)
--   especies_caracteristicas → 1   (Acca sellowiana)
--   especies_imagens         → 6   (imagens originais Acca sellowiana)
--   temp_imagens_candidatas  → 0
--   fila_aprovacao           → 0
--   historico_alteracoes     → 0
--   usuarios                 → N   (todos os cadastros reais)
