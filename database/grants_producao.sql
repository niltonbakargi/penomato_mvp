-- ============================================================
-- GRANTS DE PRODUÇÃO — PENOMATO MVP
-- Princípio do menor privilégio (P15 do Plano de Segurança)
-- ============================================================
-- Execute este script como root/admin do MySQL DEPOIS de criar
-- o banco penomato e importar a estrutura das tabelas.
--
-- Em hospedagem compartilhada (Hostinger/Hostgator):
--   1. Crie o usuário via cPanel → Bancos de Dados MySQL
--   2. Execute os GRANTs via phpMyAdmin logado como root,
--      ou peça ao suporte se não tiver acesso a GRANT.
--   O nome real do usuário terá prefixo do painel (ex: u123_app).
--   Substitua 'penomato_app' e 'penomato' pelos nomes reais.
-- ============================================================

-- ------------------------------------------------------------
-- 1. Criar usuário dedicado (apenas se não existir)
-- ------------------------------------------------------------
CREATE USER IF NOT EXISTS 'penomato_app'@'localhost'
    IDENTIFIED BY 'SUBSTITUA_POR_SENHA_FORTE_ALEATORIA';

-- ------------------------------------------------------------
-- 2. Revogar tudo — garante estado limpo antes de aplicar
-- ------------------------------------------------------------
-- (seguro mesmo na primeira execução: REVOKE em user sem grants não gera erro)
REVOKE ALL PRIVILEGES, GRANT OPTION
    ON `penomato`.*
    FROM 'penomato_app'@'localhost';

-- ------------------------------------------------------------
-- 3. Tabelas com operações CRUD completo
--    (SELECT + INSERT + UPDATE + DELETE)
-- ------------------------------------------------------------
GRANT SELECT, INSERT, UPDATE, DELETE ON `penomato`.`usuarios`               TO 'penomato_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `penomato`.`especies_administrativo` TO 'penomato_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `penomato`.`especies_caracteristicas` TO 'penomato_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `penomato`.`especies_imagens`        TO 'penomato_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `penomato`.`exemplares`              TO 'penomato_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `penomato`.`fila_aprovacao`          TO 'penomato_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `penomato`.`partes_dispensadas`      TO 'penomato_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `penomato`.`sugestoes_usuario`       TO 'penomato_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `penomato`.`temp_imagens_candidatas` TO 'penomato_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `penomato`.`tentativas_login`        TO 'penomato_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `penomato`.`tokens_alteracao_email`  TO 'penomato_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `penomato`.`tokens_recuperacao_senha` TO 'penomato_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `penomato`.`tokens_verificacao_email` TO 'penomato_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `penomato`.`artigos`                 TO 'penomato_app'@'localhost';

-- ------------------------------------------------------------
-- 4. Tabelas de referência — somente leitura
--    Dados importados da Flora do Brasil; nunca modificados
--    pela aplicação em tempo de execução.
-- ------------------------------------------------------------
GRANT SELECT ON `penomato`.`flora_brasil_plantas`   TO 'penomato_app'@'localhost';
GRANT SELECT ON `penomato`.`flora_brasil_sinonimos` TO 'penomato_app'@'localhost';

-- ------------------------------------------------------------
-- 5. Trilha de auditoria — SELECT + INSERT apenas (imutável)
--    UPDATE e DELETE são intencionalmente negados:
--    registros de auditoria não devem poder ser apagados
--    ou alterados pela aplicação.
--    Nota: ON DELETE CASCADE nas FKs é executado pelo engine
--    do MySQL internamente; não exige DELETE no usuário da app.
-- ------------------------------------------------------------
GRANT SELECT, INSERT ON `penomato`.`historico_alteracoes` TO 'penomato_app'@'localhost';

-- ------------------------------------------------------------
-- 6. Aplicar
-- ------------------------------------------------------------
FLUSH PRIVILEGES;

-- ============================================================
-- VERIFICAÇÃO (execute como root para confirmar)
-- ============================================================
-- SHOW GRANTS FOR 'penomato_app'@'localhost';
