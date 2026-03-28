# Cronograma de Desenvolvimento — Penomato MVP

**Período total:** 08/02/2026 a 25/03/2026 (46 dias)
**Desenvolvedor:** [nome do autor]
**Orientador:** [nome do orientador]
**Especialista colaborador:** Prof. Carlos Alberto da Silva
**Instituição:** Universidade Federal de Mato Grosso do Sul (UFMS)

---

## Fase 1 — Fundação do Sistema (08–12/02/2026)

**Objetivo:** Estruturar o projeto e implementar as primeiras funcionalidades de coleta de dados botânicos.

| Data | Atividade |
|------|-----------|
| 08/02 | Commit inicial — estrutura de pastas, banco de dados e configuração do ambiente |
| 08/02 | Formulário de cadastro de características botânicas (morfologia, referências) |
| 09/02 | Sistema de cadastro funcionando com persistência no banco de dados |
| 09/02 | Adição de campos de referências bibliográficas e novos campos morfológicos |
| 12/02 | Sistema de busca por características morfológicas com filtros por categoria |
| 12/02 | Sistema de upload de imagens das exsicatas com metadados e rastreabilidade de autoria |

**Entregável:** Formulário de dados botânicos + upload de imagens operacional.

---

## Fase 2 — Autenticação e Gestão de Usuários (13–27/02/2026)

**Objetivo:** Implementar controle de acesso por perfil, painel do gestor e fluxo de importação de dados.

| Data | Atividade |
|------|-----------|
| 13/02 | Protótipo do painel do revisor/especialista (busca em tempo real de espécies pendentes) |
| 15/02 | Refatoração do modelo de status — migração para status único cumulativo no banco |
| 17/02 | Sistema completo de autenticação (login, sessão, perfis: gestor, colaborador, especialista) |
| 18/02 | Painel do Gestor com aprovação de membros e gestão de espécies |
| 18/02 | Simplificação do index e correções na busca de espécies |
| 19/02 | Campo de sinônimos botânicos e melhoria do formulário de cadastro |
| 20/02 | Sistema completo de importação de dados da internet (JSON estruturado por espécie) |
| 27/02 | Painel do Colaborador com visão das espécies atribuídas e ações disponíveis |

**Entregável:** Sistema multiusuário operacional com controle de acesso por perfil.

---

## Fase 3 — Fluxo Científico Central (01–18/03/2026)

**Objetivo:** Implementar o núcleo científico do sistema — confirmação de identificação, geração de artigo e contestação.

| Data | Atividade |
|------|-----------|
| 02/03 | Refatoração completa do fluxo de importação de dados (nova arquitetura de importação) |
| 12/03 | Renomeação de todos os arquivos do inglês para o português (padronização do projeto) |
| 12/03 | Correção de inconsistências entre nomes de colunas do banco e código PHP |
| 16/03 | Página de confirmação de identificação de espécies (atributo por atributo) |
| 16/03 | Refatoração completa do sistema de autenticação |
| 17/03 | Reformulação do painel do gestor com modais e gestão de membros |
| 17/03 | Página de Gestão de Espécies com inserção de espécies de interesse |
| 17/03 | Gerador de artigo científico com template estruturado |
| 17/03 | Página de contestação de informações incorretas |
| 17/03 | Monitoramento do sistema e relatório de colaboradores |
| 17/03 | Prompt de IA estruturado para inserção assistida de dados |
| 18/03 | Limpeza de código e arquivos desnecessários |

**Entregável:** Fluxo completo — do cadastro da espécie à geração do artigo científico.

---

## Fase 4 — Exemplares, Interface e Busca (19–21/03/2026)

**Objetivo:** Implementar o módulo de exemplares de campo e refinar a experiência do usuário.

| Data | Atividade |
|------|-----------|
| 19/03 | Módulo completo de exemplares de campo (registro fotográfico por parte da planta, vinculado ao indivíduo físico por etiqueta) |
| 19/03 | Migração completa da interface para design tokens CSS (sistema de variáveis globais) |
| 19/03 | Remoção de arquivos mortos e pasta temporária |
| 20/03 | Gerador de texto botânico em prosa (converte atributos estruturados em descrição científica) |
| 20/03 | Proteção do banco com ENUM nos campos controlados |
| 20/03 | Seleção de orientador no cadastro e registro de perfil do usuário |
| 21/03 | Busca morfológica completa com sugestões em tempo real |
| 21/03 | Fila de artigos aguardando revisão e limpeza do painel do gestor |

**Entregável:** Módulo de exemplares operacional + interface padronizada + busca morfológica.

---

## Fase 5 — Infraestrutura e Deploy em Produção (22–23/03/2026)

**Objetivo:** Publicar o sistema em servidor de produção (penomato.app.br) com deploy automático via CI/CD.

| Data | Atividade |
|------|-----------|
| 22/03 | Separação de configurações dev/prod |
| 22/03 | Workflow de deploy automático via FTP (GitHub Actions → HostGator) |
| 22/03 | Correção de prefixo de URLs para funcionar em produção |
| 23/03 | Configuração e testes de conexão com banco em produção |
| 23/03 | Resolução de protocolo FTP (ftps → ftp simples) e ajustes de diretório |
| 23/03 | Correções de sintaxe PHP detectadas em produção |
| 23/03 | Correção de URLs com app.php carregado em todos os pontos de entrada |
| 23/03 | Módulo de exemplares — revisão e consolidação final |

**Entregável:** Sistema no ar em `penomato.app.br` com pipeline de deploy automático.

---

## Fase 6 — E-mail, Segurança e Ajustes Finais (24–25/03/2026)

**Objetivo:** Implementar fluxo completo de e-mail transacional e regras de segurança de acesso.

| Data | Atividade |
|------|-----------|
| 24/03 | Configuração do SMTP HostGator para `noreply@penomato.app.br` |
| 24/03 | Envio de e-mails em todos os fluxos do sistema (cadastro, aprovação, rejeição, recuperação de senha) |
| 24/03 | Confirmação de e-mail obrigatória no cadastro |
| 24/03 | Regra: identificador tem acesso imediato; demais perfis aguardam aprovação do gestor |
| 24/03 | Ajustes no formulário de cadastro (perfis, seleção, ícones) |
| 24/03 | Crédito institucional UFMS/UEMS no rodapé |
| 25/03 | Pré-preenchimento de e-mail na tela de recuperação de senha vinda do login |
| 25/03 | Impedimento de aprovação de membros sem e-mail confirmado |
| 25/03 | Estabilização final e correções de sessão no logout |

**Entregável:** Sistema completo, seguro e estável em produção.

---

## Resumo Executivo

| Fase | Período | Dias | Foco principal |
|------|---------|------|----------------|
| 1 — Fundação | 08–12/02 | 5 | Banco de dados, formulário botânico, upload de imagens |
| 2 — Autenticação | 13–27/02 | 15 | Multiusuário, perfis, painel do gestor, importação |
| 3 — Fluxo científico | 01–18/03 | 18 | Confirmação, artigo científico, contestação |
| 4 — Exemplares e UI | 19–21/03 | 3 | Exemplares de campo, design tokens, busca morfológica |
| 5 — Deploy | 22–23/03 | 2 | CI/CD, produção, HostGator |
| 6 — E-mail e segurança | 24–25/03 | 2 | E-mail transacional, aprovação de membros |
| **Total** | **08/02 – 25/03** | **46** | |

---

## Total de Commits por Natureza

| Tipo | Quantidade | Descrição |
|------|-----------|-----------|
| `feat` | 35 | Novas funcionalidades |
| `fix` | 20 | Correções de bugs |
| `refactor` | 5 | Reestruturação de código |
| `chore` | 18 | Infraestrutura, testes de deploy, limpeza |
| `style` | 1 | Padronização de interface |
| `ci` | 1 | Pipeline de integração contínua |
| **Total** | **~80** | |
