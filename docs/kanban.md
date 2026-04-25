# Kanban — Penomato MVP
**Atualizado em:** 25/04/2026
**Branch ativo:** `main`
**Último commit:** `ebc2914` feat: fluxo completo do artigo científico (rascunho → publicado)

---

## CONCLUÍDO — MVP v1.0 ✓

### Fase 1 — Fundação
- [x] Estrutura de pastas, banco de dados e ambiente
- [x] Formulário de cadastro de características botânicas
- [x] Sistema de upload de imagens das exsicatas com metadados

### Fase 2 — Autenticação e Gestão de Usuários
- [x] Sistema de autenticação (login, sessão, logout, recuperação de senha)
- [x] Perfis: Gestor, Colaborador, Revisor/Especialista, Desenvolvedor
- [x] Painel do Gestor — aprovação de membros e gestão de espécies
- [x] Painel do Colaborador — espécies atribuídas e ações disponíveis
- [x] Importação de dados da internet (JSON estruturado por espécie)

### Fase 3 — Fluxo Científico Central
- [x] Confirmação de identificação (atributo por atributo, 100% obrigatório)
- [x] Gerador de artigo científico com template estruturado
- [x] Página de contestação de informações incorretas
- [x] Painel do revisor — fila de artigos aguardando revisão
- [x] Monitoramento e relatório de colaboradores
- [x] Busca morfológica completa com sugestões em tempo real
- [x] Gerador de texto botânico em prosa (atributos → descrição científica)

### Fase 4 — Exemplares e Interface
- [x] Módulo completo de exemplares: cadastro, código XX000, foto de identificação
- [x] Revisão do exemplar pelo especialista (aprovar/rejeitar com motivo)
- [x] Mapa Leaflet de exemplares do especialista (por status, filtros)
- [x] Upload de partes vinculado ao exemplar aprovado
- [x] Design tokens CSS globais (sistema de variáveis)

### Fase 5 — Deploy em Produção (22-23/03)
- [x] Workflow CI/CD via GitHub Actions + FTP HostGator (`975831c`)
- [x] Configuração de ambiente prod/dev separados (config/producao.php + dev_local.php) (`625f26a`)
- [x] Sistema funcionando em `penomato.app.br` — URLs, RewriteBase e APP_BASE corrigidos (`baab88b`, `0b5e901`, `d06a652`)
- [x] Módulo de exemplares reimplementado e funcionando em produção (`47a892b`)
- [x] Fix erros de sintaxe $var$var em todos os controllers (`1f20943`)
- [x] Opção Desenvolvedor no cadastro + crédito institucional UEMS no rodapé (`01b1344`, `aed225b`)

### Fase 6 — E-mail e Autenticação Avançada (24-25/03)
- [x] SMTP configurado (noreply@penomato.app.br via HostGator) (`c9aca30`)
- [x] E-mail transacional em todos os fluxos (cadastro, aprovação, rejeição, recuperação) (`bd12aaa`)
- [x] Confirmação de e-mail obrigatória no cadastro (`6b3e322`)
- [x] Identificador com acesso imediato; demais perfis aguardam aprovação do gestor (`c3a51fd`)
- [x] Bloqueio de aprovação de membros sem e-mail confirmado (`ca992a2`)
- [x] Pré-preenchimento de e-mail na tela de recuperação de senha (`e532856`)

### Fase 6b — GPS do Exemplar + Remoção do Validador (28/03)
- [x] Remoção do perfil validador/autenticador do sistema (`761833f`)
- [x] Extração de GPS da foto do exemplar via EXIF + fallback para geolocalização do dispositivo (`3aff777`, `2d9ff65`, `1c2b5dd`, `3739322`) — ~15 commits; revertido ao formulário manual por instabilidade nos browsers (`a9e6dd8`)

### Fase 6c — Terminologia e Banco (02-08/04)
- [x] Terminologia botânica corrigida nos atributos de folha (`09961ac`)
- [x] Opção gestor no cadastro restrita ao primeiro gestor (`db44d42`)
- [x] Script SQL de limpeza de dados de testes / reset para reimportação (`d3a6271`, `3ebea1a`)
- [x] Snapshot do banco de produção em 08/04 (`f83246c`)

### Fase 7 — Pipeline de Imagens + Flora do Cerrado (08-15/04)
- [x] Pipeline de imagens refatorado: pasta temp eliminada — imagens salvas direto no banco (`a6e0715`, `cdbce13`)
- [x] Módulo público Flora do Cerrado (consulta REFLORA/JBRJ por nome aceito) (`ac7628e`, `5f17554`, `fb48fef`)
- [x] Carrossel de curadoria de imagens automáticas (iNaturalist + Wikimedia) (`b411ad0`, `8ac6529`)
- [x] Modal de seleção de imagem por parte da planta (`bd6a913`)
- [x] Cards de resultado viram links clicáveis para navegação por parte (`35ea7e4`)

### Fase 8 — Integração IA (11-21/04)
- [x] Integração IA (DeepSeek/Claude/OpenAI/Gemini) para preenchimento morfológico automático (`ba3e8a1`, `58ecc09`, `a08c96c`)
- [x] Modal de validação para valores não reconhecidos vindos da IA ou JSON manual (`6c5222f`)
- [x] Botão "Preencher manualmente" como fallback quando IA falha ou está offline (`411e181`)
- [x] Fix timeout IA: 110s HTTP + max_execution_time=120 no .htaccess (Hostgator) (`8759d4b`, `8558249`)
- [x] Fix: modal de busca de imagens, botão cancelar importação e config de e-mail (`69bdb28`)

### Fase 9 — Migração e Segurança
- [x] Migração MySQLi → PDO 100% concluída (9 arquivos, prepared statements em todos)
- [x] Remoção de credenciais hardcoded de 7 arquivos (SMTP, MySQL movidos para config/)
- [x] Correções de vulnerabilidades de segurança (XSS, SQL Injection prevention)
- [x] Limpeza de arquivos mortos: controllers órfãos, dumps SQL antigos, uploads legados

### Fase 10 — Fluxo do Revisor (fechado)
- [x] Fluxo completo aprovação/contestação do revisor com e-mail de feedback
- [x] Aprovar: status → `revisada`, notifica colaborador por e-mail
- [x] Contestar: status → `contestado`, motivo obrigatório, notifica com feedback
- [x] Validação de erro se contestar sem motivo

### Fase 11 — Fluxo Completo do Artigo Científico (`ebc2914`, 25/04)
- [x] Migração do banco: novo ENUM de status (`rascunho → confirmado → registrado → revisando → revisado → publicado`) + coluna `revisor_id` na tabela `artigos`
- [x] Permissões por subtipo de colaborador corrigidas — identificador e dev recebem `contestar`; dev recebe todos os cards do identificador + `dev_tools`
- [x] `confirmar_caracteristicas.php`: ao confirmar dados, artigo avança automaticamente para `confirmado`
- [x] `processar_upload_exsicata.php`: ao completar todas as partes, artigo avança para `registrado`, atribui `revisor_id` do exemplar e notifica especialista (ou todos, se sem orientador)
- [x] `artigos_fila.php`: abas e badges atualizados para os 6 novos status; botões **Ver** (sempre) e **Revisar** (só em `registrado`/`revisando`); botão **Publicar** (azul, só gestor, só em `revisado`)
- [x] `artigo_revisao.php`: parâmetro `?modo=ver|revisar`; modo revisar avança para `revisando`; formulário de decisão só exibido no modo revisar; badge de status dinâmico
- [x] `controlador_painel_revisor.php`: aprovar → `revisado` + e-mail; contestar → volta para `registrado` + e-mail com motivo; nova ação `publicar` (gestor) → `publicado` + e-mail
- [x] `helpers/autores_artigo.php`: função `montarAutoresArtigo()` monta lista de contribuidores por hierarquia científica (revisor, coletores, confirmador, compilador, editor) com deduplicação
- [x] Seção "Autores e Contribuidores" exibida na página de revisão do artigo
- [x] `docs/fluxo_artigo.md`: documentação completa do fluxo, regras de negócio e mapeamento de banco

---

## TVV PENDENTE — Teste, Validação e Verificação

- [ ] **Fluxo completo do artigo ponta a ponta** — dados internet → confirmado → imagens → registrado → revisar → revisado → publicar → publicado
- [ ] **Atribuição de revisor_id** — verificar que ao finalizar upload o `revisor_id` é gravado corretamente
- [ ] **Notificações por e-mail** — especialista recebe ao chegar em `registrado`; colaborador recebe ao ser aprovado/contestado/publicado
- [ ] **Botão Publicar** — só aparece para gestor em artigos `revisado`; não aparece para especialista
- [ ] **Modo Ver vs Revisar** — Ver não altera status; Revisar avança de `registrado` para `revisando`
- [ ] **Autores do artigo** — seção exibe hierarquia correta, sem duplicatas
- [ ] **Fallback da IA** — simular falha, verificar botão "Preencher manualmente"
- [ ] **Segurança** — dev_local.php fora do repo, páginas protegidas redirecionam sem sessão
- [ ] **Links quebrados** — navegar menu do colaborador buscando 404 ou páginas em branco

---

## A FAZER — Pós-TVV

### Crítico
- [ ] **Fila pessoal do especialista** — página filtrada por `revisor_id` com seções "Meus artigos" e "Disponíveis" + botão "Assumir" (atribui `revisor_id`)
- [ ] **Badge no card "Revisar Artigos"** — contador de artigos aguardando o especialista logado
- [ ] **Alerta ao gestor** — artigos em `confirmado` sem imagens por prazo a definir
- [ ] **Vincular foto de identificação ao exemplar** — `exemplar_id` existe em `especies_imagens` mas o upload não pergunta; mudança cirúrgica no controller
- [ ] **Criar 1 espécie com fluxo completo validado** — meta de demonstração para a banca
- [ ] **Verificar requisitos formais UEMS** — projeto integrador

### Alta
- [ ] Implementar view pública do artigo publicado (destino do botão "Abrir Artigo")
- [ ] Implementar contato.php e sobre.php (stubs vazios)
- [ ] Adicionar imagens reais a pelo menos 3 espécies do Cerrado
- [ ] Escrever introdução e justificativa do TCC
- [ ] Documentar arquitetura e fluxo científico no relatório

### Média
- [ ] Atualizar README com perfis corretos e fluxo com exemplares
- [ ] Limpeza da pasta `penomato/` duplicada no repositório
- [ ] Lightbox para ampliar imagens com créditos e licença
- [ ] Exportação da ficha da espécie em PDF
- [ ] Responsividade mobile nos formulários longos
- [ ] Preparar apresentação para a banca (slides)
- [ ] Preparar demonstração ao vivo do sistema

### Baixa
- [ ] API REST pública para consulta de espécies
- [ ] Configurações do sistema: subtipos de colaborador e partes obrigatórias

---

## PRÓXIMO (v1.1 — Sprint pós-TCC)

| Prioridade | Item | Contexto |
|---|---|---|
| Alta | Aviso de distância mínima entre exemplares (250m) | Design decidido — aviso, não bloqueio; especialista decide |
| Alta | Painel público de espécies publicadas | Ficha pública com artigo, galeria e créditos |
| Média | QR Code gerado para cada exemplar | Substitui etiqueta de alumínio no campo |
| Média | Múltiplos exemplares por espécie → múltiplas edições do artigo | Hoje: 1 exemplar por espécie |
| Baixa | Exemplar como entidade pública no mapa do sistema | GPS consultável publicamente |

---

## Diretrizes de Uso por Dispositivo

| Perfil | Dispositivo | Motivo |
|---|---|---|
| Usuário público (sem login) | Mobile ou desktop | Consulta de espécies, ficha pública, mapa |
| Colaborador / Revisor / Gestor | Desktop (recomendado) | Trabalho científico exige tela maior e precisão |

---

## Evolução Tecnológica Natural

### v2.0 — Stack moderna (pós-TCC)
- **Backend:** Laravel (PHP com framework) — rotas, ORM, filas de e-mail
- **Frontend web:** React ou Vue — SPA desacoplada consumindo API REST
- **Motivação:** formulários assíncronos, upload com progresso, mapa reativo

### v3.0 — App móvel
- **Tecnologia:** React Native — único código para iOS e Android
- **Motivo da escolha:** acesso nativo à câmera com EXIF completo (resolve GPS de fotos), modo offline para coleta em campo, e reaproveitamento do código React do frontend web
- **Funcionalidades alvo:** coleta em campo (foto + GPS), rascunho offline, sincronização ao conectar

---

## BACKLOG — Visão de Produto (futuro)

- [ ] App móvel com GPS nativo e modo offline (React Native — v3.0)
- [ ] Integração com LLM por bioma para identificação assistida
- [ ] Expansão para outros biomas (Pantanal, Amazônia, Mata Atlântica)
- [ ] Game de identificação (mecânica Pokémon Go florestal)
- [ ] FarmGame de recuperação ambiental
- [ ] Plataforma de educação ambiental fitomorfológica

---

## Métricas do MVP

| Métrica | Valor |
|---|---|
| Total de commits | ~215 |
| Período de desenvolvimento | 08/02 – 25/04/2026 (77 dias) |
| Fases concluídas | 11 / 11 |
| Controllers PHP | ~32 |
| Views PHP | ~40 |
| Helpers PHP | 1 (`autores_artigo.php`) |
| Perfis de usuário | 4 (Gestor, Colaborador, Revisor/Especialista, Desenvolvedor) |
| Subtipos de colaborador | 4 (Identificador, Especialista, Dev, Gestor de equipe) |
| Status do artigo | 6 (rascunho → confirmado → registrado → revisando → revisado → publicado) |
| Providers de IA | 4 (DeepSeek, Claude, OpenAI, Gemini) |
| Deploy | penomato.app.br (HostGator) |
