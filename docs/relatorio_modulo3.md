# AVALIAÇÃO DO MÓDULO 3 — Banco de Dados e Controle de Versão

**Projeto de Extensão:** Programa de Extensão UFMS Digital (95DX7.200525)

**Nome completo:** *(preencher)*
**Disciplina:** *(preencher)*
**Semestre letivo:** *(preencher)*
**Curso:** *(preencher)*
**Público-alvo:** Colaboradores de campo, especialistas botânicos e gestores acadêmicos da UFMS e UEMS
**Local de realização:** Universidade Federal de Mato Grosso do Sul (UFMS) — Campo Grande, MS

---

## Título da ação

PENOMATO: MODELAGEM E VERSIONAMENTO DE UM BANCO DE DADOS PARA CATALOGAÇÃO MORFOLÓGICA DE ESPÉCIES DO CERRADO

---

## Resumo

Este relatório apresenta a modelagem, implementação e o gerenciamento por controle de versão do banco de dados do sistema Penomato — uma plataforma web voltada à catalogação científica de espécies arbóreas do Cerrado, desenvolvida em parceria entre a UFMS e a UEMS no âmbito da Engenharia Florestal. O banco de dados, implementado em MariaDB, é composto por nove tabelas relacionais que cobrem o ciclo completo de uma espécie: do cadastro inicial pelo gestor até a publicação do artigo científico revisado por especialista. O modelo contempla entidades para usuários com controle de permissões por categoria, espécies com progressão de status auditável, características morfológicas organizadas por parte da planta, exemplares físicos de campo com geolocalização, imagens vinculadas a exemplares aprovados, histórico de auditoria, sugestões da comunidade e controles de segurança de acesso. O projeto é versionado com Git desde o commit inicial, com 114 commits documentados e mensagens descritivas seguindo boas práticas de versionamento semântico.

**Palavras-chave:** Banco de Dados Relacional. Controle de Versão. Cerrado. Catalogação Botânica. SQL.

---

## 1. Introdução

A conservação da biodiversidade do Cerrado — segundo maior bioma do Brasil e um dos 34 hotspots de biodiversidade do planeta — demanda sistemas de informação capazes de organizar, validar e disseminar dados sobre sua flora de forma científica e colaborativa. Nesse contexto surge o Penomato, um sistema web desenvolvido para apoiar pesquisadores e alunos da área de Engenharia Florestal da UEMS (Universidade Estadual de Mato Grosso do Sul) na tarefa de catalogar morfologicamente espécies arbóreas do Cerrado.

O desafio central é garantir que os dados inseridos no sistema — atributos morfológicos de folha, flor, fruto, caule e semente — sejam confiáveis, rastreáveis e validados cientificamente. Para isso, a base de dados precisa refletir fielmente o fluxo de trabalho científico: desde a coleta de dados em campo, passando pela revisão de um especialista, até a publicação de um artigo com créditos a todos os contribuidores.

A escolha pelo banco de dados relacional (MariaDB) é justificada pela natureza fortemente estruturada dos dados botânicos e pelas relações complexas entre entidades: um exemplar pertence a uma espécie, suas imagens pertencem ao exemplar, o artigo gerado precisa passar pelo mesmo especialista que aprovou o exemplar. Essas dependências são expressas com precisão por chaves estrangeiras e restrições de integridade referencial.

O controle de versão com Git é adotado desde o início do projeto para garantir rastreabilidade de todas as decisões de desenvolvimento, permitindo reverter alterações, documentar a evolução do sistema e colaborar de forma organizada.

---

## 2. Objetivo Geral

Desenvolver e documentar o banco de dados relacional do sistema Penomato, implementando o esquema de tabelas, restrições de integridade e operações de manipulação de dados, gerenciando toda a evolução do código por meio de controle de versão com Git.

---

## 3. Objetivos Específicos

1. **Projetar o modelo de dados** do Penomato, definindo entidades, atributos, relacionamentos e restrições que representem o fluxo científico de catalogação de espécies.
2. **Implementar o banco de dados** em MariaDB/MySQL utilizando SQL, aplicando normalização, chaves primárias, chaves estrangeiras, índices e tipos de dados adequados a cada domínio.
3. **Executar operações DML** (inserção, atualização, remoção e consultas) que reflitam as ações reais do sistema, como o cadastro de espécies, envio de imagens e aprovação de exemplares.
4. **Gerenciar o código do projeto** com Git, utilizando commits frequentes e com mensagens descritivas, organizando a evolução do banco de dados em arquivos SQL versionados.
5. **Publicar o repositório** no GitHub, tornando o histórico de desenvolvimento auditável e o código acessível para a comunidade acadêmica.

---

## 4. Justificativa e Delimitação do Problema

A documentação científica de espécies do Cerrado é realizada, em grande parte, por meio de fichas em papel ou planilhas isoladas, sem integração entre coleta de campo, revisão especializada e publicação. Esse processo fragmentado dificulta a colaboração, compromete a rastreabilidade dos dados e impede a construção de um acervo público e confiável.

O Penomato resolve esse problema ao centralizar todo o fluxo em um único sistema com banco de dados relacional. A escolha do MariaDB — sistema open source amplamente utilizado, compatível com MySQL e de alta performance para aplicações web — é justificada pela familiaridade da equipe, pelo suporte nativo ao XAMPP (ambiente de desenvolvimento utilizado) e pela adequação ao porte do projeto.

O uso do Git como sistema de controle de versão é essencial para um projeto em desenvolvimento ativo: permite rastrear cada alteração no esquema do banco de dados, documentar o motivo de cada mudança nas mensagens de commit e manter um histórico completo que serve como documentação técnica do projeto.

A delimitação do problema concentra-se nas espécies arbóreas do Cerrado registradas pela UEMS no Mato Grosso do Sul, com foco nos biomas Cerrado, Pantanal e Mata Atlântica do estado.

---

## 5. Fundamentação Teórica

### 5.1 Modelagem de Banco de Dados Relacional

O modelo relacional, proposto por Edgar F. Codd (1970), organiza dados em tabelas (relações) compostas por linhas (tuplas) e colunas (atributos). Cada tabela representa uma entidade do domínio, e as relações entre entidades são expressas por chaves estrangeiras. O modelo Entidade-Relacionamento (ER), desenvolvido por Chen (1976), é a ferramenta padrão para projetar a estrutura conceitual antes da implementação física.

No Penomato, foram identificadas as seguintes entidades principais: **Usuário**, **Espécie**, **Características Morfológicas**, **Exemplar**, **Imagem de Parte**, **Histórico de Alterações** e **Sugestão**.

### 5.2 Normalização

A normalização é o processo de organizar as tabelas de um banco de dados para reduzir redundâncias e anomalias de inserção, atualização e exclusão (DATE, 2003). O banco do Penomato aplica as três primeiras formas normais (1FN, 2FN, 3FN):

- **1FN:** todos os atributos são atômicos — cada célula contém um único valor.
- **2FN:** todos os atributos não-chave dependem da chave primária completa.
- **3FN:** nenhum atributo não-chave depende de outro atributo não-chave (sem dependência transitiva).

### 5.3 SQL — Structured Query Language

SQL é a linguagem padrão para criação e manipulação de bancos de dados relacionais, padronizada pela ISO/IEC 9075. É dividida em:

- **DDL (Data Definition Language):** `CREATE TABLE`, `ALTER TABLE`, `DROP TABLE` — define a estrutura.
- **DML (Data Manipulation Language):** `INSERT`, `UPDATE`, `DELETE`, `SELECT` — manipula os dados.
- **DCL (Data Control Language):** `GRANT`, `REVOKE` — controla permissões.

### 5.4 Controle de Versão com Git

Git é um sistema de controle de versão distribuído criado por Linus Torvalds (2005). Permite que múltiplos desenvolvedores trabalhem em paralelo, revertam mudanças indesejadas e mantenham histórico completo das alterações (CHACON; STRAUB, 2014). Boas práticas incluem: commits atômicos (uma mudança por commit), mensagens descritivas no padrão `tipo: descrição` (Conventional Commits), uso de branches para funcionalidades isoladas e tags para marcar versões.

### 5.5 Referências

CHEN, P. P. The entity-relationship model—toward a unified view of data. **ACM Transactions on Database Systems**, v. 1, n. 1, p. 9–36, 1976.

CHACON, S.; STRAUB, B. **Pro Git**. 2. ed. New York: Apress, 2014. Disponível em: https://git-scm.com/book/pt-br/v2. Acesso em: 07 abr. 2026.

CODD, E. F. A relational model of data for large shared data banks. **Communications of the ACM**, v. 13, n. 6, p. 377–387, 1970.

DATE, C. J. **Introdução a sistemas de banco de dados**. 8. ed. Rio de Janeiro: Campus, 2003.

ELMASRI, R.; NAVATHE, S. B. **Sistemas de banco de dados**. 7. ed. São Paulo: Pearson, 2018.

---

## 6. Metodologia

### 6.1 Modelagem do Banco de Dados

#### Entidades e atributos principais

O modelo foi projetado a partir do fluxo de trabalho científico do sistema, resultando em 9 tabelas:

| Tabela | Responsabilidade |
|---|---|
| `usuarios` | Perfis de acesso com quatro categorias: gestor, colaborador, revisor, visitante |
| `especies_administrativo` | Registro administrativo da espécie com progressão de status auditável |
| `especies_caracteristicas` | Atributos morfológicos por parte da planta com campo de referência bibliográfica |
| `exemplares` | Espécimes físicos de campo com código único, geolocalização e ciclo de aprovação |
| `especies_imagens` | Fotografias das partes da planta vinculadas a um exemplar aprovado |
| `historico_alteracoes` | Log imutável de todas as ações realizadas no sistema |
| `sugestoes_usuario` | Canal para propostas de novas espécies ou correções |
| `tokens_recuperacao_senha` | Tokens de uso único para redefinição de senha por e-mail |
| `tentativas_login` | Proteção contra força bruta por IP |

#### Relacionamentos

- `especies_caracteristicas` → `especies_administrativo` (N:1, CASCADE DELETE)
- `exemplares` → `especies_administrativo` (N:1, CASCADE DELETE)
- `exemplares` → `usuarios` como `especialista_id` e `cadastrado_por` (N:1)
- `especies_imagens` → `especies_administrativo` (N:1, CASCADE DELETE)
- `especies_imagens` → `exemplares` (N:1, SET NULL ao deletar exemplar)
- `especies_imagens` → `usuarios` como identificador, validador e coletor (N:1)
- `especies_imagens` → `especies_imagens` como `substituida_por` (auto-referência para versionamento)
- `historico_alteracoes` → `especies_administrativo` e `usuarios` (N:1)
- `sugestoes_usuario` → `usuarios` (N:1, CASCADE DELETE)
- `tokens_recuperacao_senha` → `usuarios` (N:1, CASCADE DELETE)

#### Progressão de status da espécie

```
sem_dados → dados_internet → descrita → registrada
         → em_revisao → revisada → publicado
                     ↘ contestado
```

Cada transição registra automaticamente data e autor responsável nos campos `data_*` e `autor_*_id` da tabela `especies_administrativo`.

#### Código do exemplar

Cada exemplar recebe um código único gerado automaticamente pelo sistema no formato `XX000` (2 letras maiúsculas aleatórias + 3 dígitos sequenciais). Exemplos: `KT001`, `BR042`, `ZA117`. O código é anotado na etiqueta de alumínio pregada na planta no campo, garantindo o vínculo físico entre o espécime real e o registro digital.

---

### 6.2 Implementação e Manipulação de Dados

#### DDL — Criação de tabelas (exemplos representativos)

**Tabela `usuarios`** — controle de acesso por categoria com ENUM:
```sql
CREATE TABLE `usuarios` (
  `id`                 INT(11)      NOT NULL AUTO_INCREMENT,
  `nome`               VARCHAR(150) NOT NULL,
  `email`              VARCHAR(150) NOT NULL,
  `senha_hash`         VARCHAR(255) NOT NULL,
  `categoria`          ENUM('gestor','colaborador','revisor','visitante')
                       NOT NULL DEFAULT 'visitante',
  `status_verificacao` ENUM('pendente','verificado','bloqueado')
                       NOT NULL DEFAULT 'pendente',
  `ativo`              TINYINT(1)   NOT NULL DEFAULT 1,
  `data_cadastro`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Tabela `exemplares`** — espécimes de campo com ciclo de revisão:
```sql
CREATE TABLE `exemplares` (
  `id`              INT(11)      NOT NULL AUTO_INCREMENT,
  `codigo`          VARCHAR(6)   NOT NULL,
  `especie_id`      INT(11)      NOT NULL,
  `numero_etiqueta` VARCHAR(50)  DEFAULT NULL,
  `latitude`        DECIMAL(10,8) DEFAULT NULL,
  `longitude`       DECIMAL(11,8) DEFAULT NULL,
  `cidade`          VARCHAR(150) DEFAULT NULL,
  `estado`          CHAR(2)      DEFAULT NULL,
  `bioma`           VARCHAR(100) DEFAULT NULL,
  `especialista_id` INT(11)      NOT NULL,
  `cadastrado_por`  INT(11)      NOT NULL,
  `status`          ENUM('aguardando_revisao','aprovado','rejeitado')
                    NOT NULL DEFAULT 'aguardando_revisao',
  `data_cadastro`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `data_revisao`    DATETIME     DEFAULT NULL,
  `motivo_rejeicao` TEXT         DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_codigo` (`codigo`),
  CONSTRAINT `fk_exemplar_especie`
    FOREIGN KEY (`especie_id`) REFERENCES `especies_administrativo`(`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_exemplar_especialista`
    FOREIGN KEY (`especialista_id`) REFERENCES `usuarios`(`id`),
  CONSTRAINT `fk_exemplar_cadastrador`
    FOREIGN KEY (`cadastrado_por`)  REFERENCES `usuarios`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### DML — Inserção de dados

**Cadastro do gestor responsável:**
```sql
INSERT INTO `usuarios`
  (nome, email, senha_hash, categoria, status_verificacao)
VALUES
  ('Prof. Nilton Bakargi', 'nilton.bakargi@ufms.br',
   '$2y$10$hash_bcrypt_aqui', 'gestor', 'verificado');
```

**Cadastro de espécies do Cerrado:**
```sql
INSERT INTO `especies_administrativo` (nome_cientifico, status, prioridade)
VALUES
  ('Caryocar brasiliense', 'sem_dados', 'alta'),    -- pequizeiro
  ('Handroanthus albus',   'sem_dados', 'alta'),    -- ipê-amarelo
  ('Mauritia flexuosa',    'sem_dados', 'media'),   -- buriti
  ('Stryphnodendron adstringens', 'sem_dados', 'media'); -- barbatimão
```

**Registro de exemplar coletado em campo:**
```sql
INSERT INTO `exemplares`
  (codigo, especie_id, numero_etiqueta, latitude, longitude,
   cidade, estado, bioma, descricao_local, especialista_id, cadastrado_por)
VALUES
  ('KT001', 1, 'ETQ-047',
   -20.48290500, -54.61520000,
   'Campo Grande', 'MS', 'Cerrado',
   'Margem do córrego Segredo, trilha principal, árvore isolada em área de cerradão',
   2, 3);
```

**Inserção de características morfológicas da folha:**
```sql
INSERT INTO `especies_caracteristicas`
  (especie_id, familia, familia_ref,
   forma_folha, forma_folha_ref,
   tipo_folha, tipo_folha_ref,
   filotaxia_folha, filotaxia_folha_ref,
   cor_flores, cor_flores_ref)
VALUES
  (1, 'Caryocaraceae', 'Flora do Brasil 2020',
   'obovada a elíptica', 'Lorenzi, 2002',
   'composta trifoliolada', 'Flora do Brasil 2020',
   'oposta', 'Lorenzi, 2002',
   'amarela', 'Almeida et al., 1998');
```

#### DML — Atualização de dados

**Aprovação de exemplar pelo especialista:**
```sql
UPDATE `exemplares`
SET
  status       = 'aprovado',
  data_revisao = NOW()
WHERE id = 1
  AND especialista_id = 2;
```

**Avanço do status da espécie após descrição completa:**
```sql
UPDATE `especies_administrativo`
SET
  status           = 'descrita',
  data_descrita    = NOW(),
  autor_descrita_id = 3
WHERE id = 1
  AND status = 'dados_internet';
```

**Rejeição de imagem com motivo:**
```sql
UPDATE `especies_imagens`
SET
  status_validacao = 'rejeitado',
  data_validacao   = CURDATE(),
  motivo_rejeicao  = 'Imagem desfocada — folha não identificável. Reenviar com foco na nervura central.'
WHERE id = 5
  AND id_usuario_validador = 2;
```

#### DML — Remoção de dados

**Exclusão de sugestão já processada:**
```sql
DELETE FROM `sugestoes_usuario`
WHERE id = 3
  AND status_sugestao IN ('aprovada', 'rejeitada');
```

**Limpeza de tokens de senha expirados:**
```sql
DELETE FROM `tokens_recuperacao_senha`
WHERE expira_em < NOW()
   OR usado = 1;
```

**Limpeza de tentativas de login antigas (janela de 1 hora):**
```sql
DELETE FROM `tentativas_login`
WHERE criado_em < DATE_SUB(NOW(), INTERVAL 1 HOUR);
```

#### DML — Consultas SQL

**Listar espécies com total de partes fotografadas por espécie:**
```sql
SELECT
  ea.nome_cientifico,
  ea.status,
  COUNT(ei.id)                                          AS total_imagens,
  COUNT(DISTINCT ei.parte_planta)                       AS partes_distintas,
  SUM(ei.status_validacao = 'aprovado')                 AS imagens_aprovadas
FROM especies_administrativo ea
LEFT JOIN especies_imagens ei ON ei.especie_id = ea.id
GROUP BY ea.id, ea.nome_cientifico, ea.status
ORDER BY partes_distintas DESC;
```

**Exemplares aguardando revisão com dados do especialista:**
```sql
SELECT
  e.codigo,
  ea.nome_cientifico,
  e.cidade,
  e.estado,
  e.bioma,
  e.data_cadastro,
  u_esp.nome  AS especialista,
  u_col.nome  AS coletado_por
FROM exemplares e
JOIN especies_administrativo ea ON ea.id = e.especie_id
JOIN usuarios u_esp ON u_esp.id = e.especialista_id
JOIN usuarios u_col ON u_col.id = e.cadastrado_por
WHERE e.status = 'aguardando_revisao'
ORDER BY e.data_cadastro ASC;
```

**Histórico completo de alterações de uma espécie:**
```sql
SELECT
  ha.data_alteracao,
  u.nome             AS responsavel,
  u.categoria,
  ha.tipo_acao,
  ha.tabela_afetada,
  ha.campo_alterado,
  ha.valor_anterior,
  ha.valor_novo,
  ha.justificativa
FROM historico_alteracoes ha
JOIN usuarios u ON u.id = ha.id_usuario
WHERE ha.especie_id = 1
ORDER BY ha.data_alteracao DESC;
```

**Espécies prontas para geração de artigo (identificadas + registradas):**
```sql
SELECT
  ea.id,
  ea.nome_cientifico,
  ea.data_descrita,
  ea.data_registrada,
  DATEDIFF(NOW(), ea.data_registrada) AS dias_aguardando
FROM especies_administrativo ea
WHERE ea.status = 'registrada'
  AND ea.data_descrita IS NOT NULL
ORDER BY ea.data_registrada ASC;
```

---

### 6.3 Uso do Controle de Versão

#### Configuração do repositório

O projeto foi iniciado com `git init` e o primeiro commit registrado em fevereiro de 2026. O repositório acompanha toda a evolução do sistema, incluindo as alterações no esquema do banco de dados documentadas em arquivos SQL na pasta `database/` e `docs/sql/`.

#### Estratégia de versionamento adotada

O projeto adota um fluxo simplificado com branch principal `main`, adequado ao desenvolvimento individual com contribuições pontuais. As convenções de mensagem de commit seguem o padrão **Conventional Commits**:

| Prefixo | Uso |
|---|---|
| `feat:` | Nova funcionalidade implementada |
| `fix:` | Correção de bug |
| `refactor:` | Reestruturação de código sem mudança de comportamento |
| `docs:` | Atualização de documentação |
| `chore:` | Tarefas de manutenção (dependências, configurações) |
| `revert:` | Reversão de commit anterior |

#### Exemplos de commits representativos

```
49fb6d5  🎉 initial commit: projeto penomato mvp
8f64c7f     adiciona sistema completo de cadastro de características
1d941e8  ✨ feat: sistema de cadastro de características botânicas funcionando
761833f     chore: remove perfil validador e adiciona extração de GPS da foto
8630055     refactor: simplifica seleção de foto — botão único, sem captura separada
4dba957     fix: remove condição tentarExif indefinida que bloqueava leitura de GPS
a9e6dd8     revert: volta cadastrar_exemplar ao estado pré-mobile
5048649     docs: registra diretrizes de dispositivo e evolução tecnológica
09961ac     fix: corrige terminologia botânica nos atributos de folha
```

**Total de commits até a data deste relatório: 114**

#### Estrutura do repositório

```
penomato_mvp/
├── database/               ← dumps do banco em cada versão
│   ├── penomato (26).sql   ← versão mais recente do schema completo
│   ├── criar_tabela_exemplares.sql
│   ├── criar_tabela_tokens_senha.sql
│   └── ...
├── docs/
│   └── sql/
│       ├── esquema_penomato.sql    ← DDL consolidado e comentado
│       ├── operacoes_dml.sql       ← exemplos de INSERT/UPDATE/DELETE/SELECT
│       └── atualizacoes/           ← migrações incrementais numeradas
│           ├── 001_status_unico.sql
│           └── 002_enum_especies_caracteristicas.sql
├── src/
│   ├── Controllers/        ← lógica de negócio em PHP
│   └── Views/              ← páginas HTML/PHP
└── ...
```

#### Link para o repositório

*(inserir URL do repositório GitHub após publicação)*

---

## 7. Resultados Preliminares

### Banco de dados implementado

O banco de dados `penomato` foi criado com sucesso no servidor MariaDB 10.4 (XAMPP) e conta atualmente com:

- **9 tabelas** criadas e em operação
- **117 espécies** do Cerrado cadastradas na tabela `especies_administrativo`
- **1 usuário gestor** cadastrado (`nilton.bakargi@ufms.br`)
- **Todas as restrições de integridade** (FKs, UNIQUEs, ENUMs, CHECK) aplicadas
- **Índices** criados nos campos de busca mais frequentes (status, categoria, datas)

### Ciclo de vida de uma espécie no banco

O fluxo abaixo resume o comportamento real do sistema após as inserções:

```
[Gestor cadastra]       → status: sem_dados
[Colaborador descreve]  → status: dados_internet → descrita
[Colaborador coleta]    → exemplar: aguardando_revisao
[Especialista aprova]   → exemplar: aprovado
[Fotos enviadas]        → status espécie: registrada
[Artigo gerado]         → status: em_revisao
[Especialista revisa]   → status: revisada → publicado
```

### Controle de versão

Com 114 commits documentados, o histórico do Git demonstra a evolução progressiva do sistema: desde o commit inicial com a estrutura básica do banco, passando pela adição de tabelas de segurança (`tokens_recuperacao_senha`, `tentativas_login`), pela criação da tabela `exemplares`, até as correções de terminologia botânica nos atributos morfológicos. Cada alteração no schema foi acompanhada de um arquivo SQL de migração numerado em `docs/sql/atualizacoes/`, permitindo reproduzir o banco em qualquer versão do histórico.

---

## 8. Conclusão

O desenvolvimento do banco de dados do Penomato demonstrou na prática os conceitos fundamentais de modelagem relacional, normalização e SQL aplicados a um problema real da área de ciências biológicas e engenharia florestal. O modelo resultante, com 9 tabelas e relacionamentos bem definidos, reflete fielmente o fluxo científico de catalogação: cada entidade corresponde a um ator ou artefato do processo real, e as restrições de integridade do banco garantem que regras de negócio críticas — como a obrigatoriedade de aprovação do exemplar antes do envio de fotos — sejam aplicadas no nível do dado, não apenas na aplicação.

O controle de versão com Git provou ser indispensável para um projeto de software em evolução contínua. Os 114 commits documentados funcionam como um diário técnico do projeto, registrando não apenas o que foi alterado, mas por que foi alterado — informação essencial para qualquer desenvolvedor que precise compreender ou manter o sistema no futuro.

A combinação entre banco de dados relacional robusto e versionamento disciplinado forma a base técnica sobre a qual o Penomato poderá crescer: novas tabelas podem ser adicionadas como migrações numeradas, novas funcionalidades podem ser desenvolvidas em branches isoladas, e toda a equipe — professores, alunos e colaboradores — tem acesso a um histórico confiável e auditável do projeto.
