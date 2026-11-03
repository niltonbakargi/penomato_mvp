# Análise do Banco de Dados de Produção
**Arquivo:** `database/b62a4147_penomato (10).sql`
**Data da análise:** 2026-11-03
**Ambiente prod:** phpMyAdmin 5.2.3 · MySQL 8.0.46 · PHP 8.4.27
**Ambiente dev:** MariaDB 10.4.32

---

## 1. Visão Geral

| Métrica | Valor |
|---|---|
| Banco | `b62a4147_penomato` |
| Total de tabelas | 22 |
| Espécies cadastradas | 648 |
| Imagens | 2.749 |
| Artigos | 149 |
| Usuários | 3 ativos (5 total) |
| Sinônimos REFLORA | 21.193 |
| Histórico de alterações | 161 registros |

---

## 2. Divergência de SGBD (dev vs. prod)

**Dev:** MariaDB 10.4.32
**Prod:** MySQL 8.0.46

Embora compatíveis na maioria dos casos, existem diferenças reais:

- MySQL 8.0 ativa `STRICT_TRANS_TABLES` por padrão — inserções com valores inválidos em ENUMs geram erro (não silêncio)
- Comportamento de `ONLY_FULL_GROUP_BY` mais restrito no MySQL 8.0
- Funções JSON e expressões de janela (`OVER()`) têm implementação ligeiramente diferente
- **Recomendação:** testar localmente com MySQL 8.0 ao invés de MariaDB para eliminar surpresas em produção

---

## 3. Problemas Encontrados

### 3.1 ALTO — Foreign Keys com ON DELETE RESTRICT implícito (bloqueio de exclusão de usuário)

Diversas FKs apontam para `usuarios(id)` **sem ON DELETE** declarado, o que significa `RESTRICT` por padrão. Isso impede deletar qualquer usuário que tenha registros associados nessas tabelas:

| Tabela | Coluna(s) | FK | Comportamento atual |
|---|---|---|---|
| `historico_alteracoes` | `id_usuario` | `fk_historico_usuario` | RESTRICT |
| `especies_imagens` | `id_usuario_identificador` | `especies_imagens_ibfk_2` | RESTRICT |
| `especies_imagens` | `id_usuario_validador` | `especies_imagens_ibfk_3` | RESTRICT |
| `especies_imagens` | `coletor_id` | `especies_imagens_ibfk_4` | RESTRICT |
| `matrizes` | `cadastrado_por` | `fk_matriz_usuario` | RESTRICT |
| `matrizes_comentarios` | `usuario_id` | `fk_mcoment_usuario` | RESTRICT |
| `matrizes_fotos` | `enviada_por` | `fk_mfoto_usuario` | RESTRICT |
| `exemplares` | `especialista_id`, `cadastrado_por` | `fk_exemplar_*` | RESTRICT |

**Impacto:** com apenas 5 usuários ainda não causou problema, mas ao tentar remover qualquer colaborador ativo o banco retornará erro. Todas essas colunas deveriam ter `ON DELETE SET NULL` (o usuário sumiu mas o registro histórico permanece).

**Correção sugerida:**
```sql
ALTER TABLE historico_alteracoes
  DROP FOREIGN KEY fk_historico_usuario,
  ADD CONSTRAINT fk_historico_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE especies_imagens
  DROP FOREIGN KEY especies_imagens_ibfk_2,
  ADD CONSTRAINT especies_imagens_ibfk_2
    FOREIGN KEY (id_usuario_identificador) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE especies_imagens
  DROP FOREIGN KEY especies_imagens_ibfk_3,
  ADD CONSTRAINT especies_imagens_ibfk_3
    FOREIGN KEY (id_usuario_validador) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE especies_imagens
  DROP FOREIGN KEY especies_imagens_ibfk_4,
  ADD CONSTRAINT especies_imagens_ibfk_4
    FOREIGN KEY (coletor_id) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE matrizes
  DROP FOREIGN KEY fk_matriz_usuario,
  ADD CONSTRAINT fk_matriz_usuario
    FOREIGN KEY (cadastrado_por) REFERENCES usuarios(id) ON DELETE SET NULL;
```

---

### 3.2 MÉDIO — Índices ausentes em colunas consultadas frequentemente

#### `fila_aprovacao.status` — sem índice
```sql
-- Consulta típica (aprovacao_acoes.php):
SELECT * FROM fila_aprovacao WHERE status = 'pendente' ...

-- Índice sugerido:
ALTER TABLE fila_aprovacao ADD INDEX idx_status (status);
```

#### `especies_administrativo.autor_*_id` — sem índices
Os 6 campos `autor_*_id` são usados em JOINs em `relatorio_colaboradores.php`:
```sql
-- JOINs que ocorrem sem índice:
JOIN usuarios u1 ON u1.id = ea.autor_descrita_id
JOIN usuarios u2 ON u2.id = ea.autor_registrada_id
-- ... etc

-- Índices sugeridos:
ALTER TABLE especies_administrativo
  ADD INDEX idx_autor_dados_internet (autor_dados_internet_id),
  ADD INDEX idx_autor_descrita       (autor_descrita_id),
  ADD INDEX idx_autor_registrada     (autor_registrada_id),
  ADD INDEX idx_autor_revisada       (autor_revisada_id),
  ADD INDEX idx_autor_contestado     (autor_contestado_id),
  ADD INDEX idx_autor_publicado      (autor_publicado_id);
```

#### `tentativas_login` — índice composto ausente
```sql
-- Consulta de rate-limit (tipicamente):
WHERE ip = ? AND criado_em > DATE_SUB(NOW(), INTERVAL 15 MINUTE)

-- Índices atuais:
KEY ip (ip)        -- separados
KEY criado_em (criado_em)

-- Índice sugerido (composto — muito mais eficiente):
ALTER TABLE tentativas_login
  DROP INDEX ip,
  DROP INDEX criado_em,
  ADD INDEX idx_ip_criado (ip, criado_em);
```

#### `flora_brasil_plantas.nome_cientifico` — sem índice de igualdade
```sql
-- Consulta típica da integração REFLORA:
WHERE nome_cientifico = 'Qualea grandiflora'

-- Tem apenas FULLTEXT (ft_nome) — não serve para igualdade exata
ALTER TABLE flora_brasil_plantas
  ADD INDEX idx_nome_cientifico (nome_cientifico(191));
```

---

### 3.3 MÉDIO — Colação mista entre tabelas relacionadas

| Tabela | Colação |
|---|---|
| Todas as demais | `utf8mb4_general_ci` |
| `especies_caracteristicas_obs` | `utf8mb4_unicode_ci` |
| `flora_brasil_plantas` | `utf8mb4_unicode_ci` |
| `flora_brasil_sinonimos` | `utf8mb4_unicode_ci` |

**Impacto:** JOINs e comparações entre tabelas com colações diferentes fazem o MySQL realizar conversão implícita, impedindo o uso de índices e podendo causar resultados inesperados em comparações de strings com acentos.

**Correção:** uniformizar para `utf8mb4_unicode_ci` (mais correto para português) em todas as tabelas, ou manter `utf8mb4_general_ci` em todas. Não misturar.

```sql
-- Opção A: uniformizar tudo para unicode_ci
ALTER TABLE artigos                   CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE especies_administrativo   CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE especies_caracteristicas  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE especies_imagens          CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE fila_aprovacao            CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE historico_alteracoes      CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE usuarios                  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- (demais tabelas idem)
```

---

### 3.4 BAIXO — ENUM `validador` em `usuarios.categoria` sem código correspondente

```sql
categoria enum('gestor','colaborador','revisor','validador','visitante')
```

O valor `'validador'` existe no ENUM de produção mas **não há nenhuma rota de código** que trate este papel. Se um usuário for definido como `validador`, o RBAC o tratará como `visitante` (fallback padrão). Remover ou mapear explicitamente no código.

```sql
-- Remover o valor não utilizado (após confirmar que nenhum usuário tem categoria='validador'):
SELECT id, nome, categoria FROM usuarios WHERE categoria = 'validador';
-- Se vazio, alterar:
ALTER TABLE usuarios MODIFY categoria
  ENUM('gestor','colaborador','revisor','visitante')
  COLLATE utf8mb4_general_ci DEFAULT 'visitante';
```

---

### 3.5 BAIXO — `artigos.status` ENUM difere do ciclo de status de espécies

```sql
-- artigos.status (prod):
enum('rascunho','confirmado','registrado','revisando','revisado','publicado')

-- especies_administrativo.status (prod):
enum('sem_dados','dados_internet','descrita','registrada','em_revisao','revisada','contestado','publicado')
```

Isso é **esperado** (tabelas distintas), mas o código em `artigos_fila.php` usa os valores corretos — nenhuma ação necessária, apenas documentar para evitar confusão futura.

---

### 3.6 BAIXO — `historico_alteracoes.id_usuario` pode ser NULL na prática

A coluna `id_usuario` é `NOT NULL` mas a FK proposta acima seria `ON DELETE SET NULL`. Antes de aplicar a correção da FK, alterar a coluna para permitir NULL:

```sql
ALTER TABLE historico_alteracoes
  MODIFY id_usuario int DEFAULT NULL;
```

---

## 4. O que está OK

- **`artigos_autores`** — não existe no dump e **não é referenciada em nenhum arquivo PHP** (a helper `autores_artigo.php` usa `artigos`, `especies_administrativo` e `especies_imagens`). Sem problema.
- **`artigos` → `especies_administrativo`** FK tem `ON DELETE CASCADE` — correto.
- **`fila_aprovacao` → `especie/usuario`** FKs têm `ON DELETE CASCADE` — correto.
- **`tokens_*`** tabelas têm `ON DELETE CASCADE` — correto.
- **`artigos.especie_id`** tem `UNIQUE KEY` — garante 1 artigo por espécie.
- **`partes_dispensadas`** tem `UNIQUE KEY uk_especie_parte (especie_id, parte_planta)` — correto.
- **`historico_alteracoes`** tem índice composto `idx_usuario_revertida (id_usuario, revertida, data_alteracao)` — bem otimizado para a tela de desfazer.
- **`especies_administrativo`** tem `idx_status`, `idx_data_*` — cobrem as consultas mais comuns.
- **Campos `senha_hash`** usam `varchar(255)` — correto para bcrypt.

---

## 5. Resumo de Prioridades

| # | Severidade | Item | Ação |
|---|---|---|---|
| 1 | ALTO | FKs RESTRICT impedem exclusão de usuário | ALTER TABLE ... ON DELETE SET NULL |
| 2 | MÉDIO | `fila_aprovacao` sem índice em `status` | ADD INDEX idx_status |
| 3 | MÉDIO | `especies_administrativo` sem índices em `autor_*_id` | ADD INDEX (6 colunas) |
| 4 | MÉDIO | `tentativas_login` índices separados em vez de composto | DROP + ADD INDEX composto |
| 5 | MÉDIO | Colações mistas (general_ci vs unicode_ci) | CONVERT TO CHARACTER SET |
| 6 | MÉDIO | `flora_brasil_plantas` sem índice em `nome_cientifico` | ADD INDEX |
| 7 | BAIXO | ENUM `validador` sem handler no código | Remover ou mapear |
| 8 | BAIXO | `historico_alteracoes.id_usuario` NOT NULL incompatível com SET NULL | MODIFY para DEFAULT NULL |

---

## 6. Script SQL Consolidado (executar em produção)

```sql
-- ============================================================
-- PENOMATO MVP — Correções de banco de dados
-- Gerado em: 2026-11-03
-- EXECUTAR COM CUIDADO — faça backup antes
-- ============================================================

-- 1. Permitir NULL em id_usuario (pré-requisito para FK SET NULL)
ALTER TABLE historico_alteracoes
  MODIFY id_usuario int DEFAULT NULL;

-- 2. Corrigir FKs para ON DELETE SET NULL
ALTER TABLE historico_alteracoes
  DROP FOREIGN KEY fk_historico_usuario,
  ADD CONSTRAINT fk_historico_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE especies_imagens
  DROP FOREIGN KEY especies_imagens_ibfk_2,
  ADD CONSTRAINT especies_imagens_ibfk_2
    FOREIGN KEY (id_usuario_identificador) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE especies_imagens
  DROP FOREIGN KEY especies_imagens_ibfk_3,
  ADD CONSTRAINT especies_imagens_ibfk_3
    FOREIGN KEY (id_usuario_validador) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE especies_imagens
  DROP FOREIGN KEY especies_imagens_ibfk_4,
  ADD CONSTRAINT especies_imagens_ibfk_4
    FOREIGN KEY (coletor_id) REFERENCES usuarios(id) ON DELETE SET NULL;

ALTER TABLE matrizes
  DROP FOREIGN KEY fk_matriz_usuario,
  ADD CONSTRAINT fk_matriz_usuario
    FOREIGN KEY (cadastrado_por) REFERENCES usuarios(id) ON DELETE SET NULL;

-- 3. Índices ausentes
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
  ADD INDEX idx_nome_cientifico (nome_cientifico(191));

-- 4. Verificar e remover ENUM validador (verificar antes se há usuários com esse valor)
-- SELECT id, nome, categoria FROM usuarios WHERE categoria = 'validador';
-- Se vazio:
-- ALTER TABLE usuarios MODIFY categoria
--   ENUM('gestor','colaborador','revisor','visitante')
--   COLLATE utf8mb4_general_ci DEFAULT 'visitante';

-- 5. (OPCIONAL — requer janela de manutenção) Uniformizar colações
-- ALTER TABLE artigos                  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- ALTER TABLE especies_administrativo  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- ... (demais tabelas)
```
