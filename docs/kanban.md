# Kanban — Penomato MVP
**Atualizado em:** 28/03/2026
**Branch ativo:** `main`
**Último commit:** `e532856` feat: pré-preenche e-mail na recuperação de senha vindo do login

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

### Fase 5 — Deploy em Produção
- [x] Workflow CI/CD via GitHub Actions + FTP HostGator
- [x] Configuração de ambiente prod/dev separados
- [x] Sistema funcionando em `penomato.app.br`

### Fase 6 — E-mail e Segurança
- [x] SMTP configurado (noreply@penomato.app.br via HostGator)
- [x] E-mail transacional em todos os fluxos (cadastro, aprovação, rejeição, recuperação)
- [x] Confirmação de e-mail obrigatória no cadastro
- [x] Identificador com acesso imediato; demais perfis aguardam aprovação do gestor
- [x] Bloqueio de aprovação de membros sem e-mail confirmado
- [x] Pré-preenchimento de e-mail na tela de recuperação de senha

---

## EM ANDAMENTO — Pós-MVP / Estabilização

- [ ] **Limpeza da pasta `penomato/`** — sobrou da reestruturação revertida (`8b16a74` → `eed76aa`); contém composer.lock, config/ e vendor/ duplicados que não deveriam estar no repositório
- [ ] **Testes integrados em produção** — validar fluxo completo: cadastro exemplar → revisão → upload de partes → geração de artigo → revisão do especialista
- [ ] **Atualizar README** — incluir perfis corretos e fluxo com exemplares

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

## BACKLOG — Visão de Produto (futuro)

- [ ] App móvel com GPS nativo e modo offline (coleta em campo sem internet)
- [ ] Integração com LLM por bioma para identificação assistida
- [ ] Expansão para outros biomas (Pantanal, Amazônia, Mata Atlântica)
- [ ] Game de identificação (mecânica Pokémon Go florestal)
- [ ] FarmGame de recuperação ambiental
- [ ] Plataforma de educação ambiental fitomorfológica

---

## Métricas do MVP

| Métrica | Valor |
|---|---|
| Total de commits | 94 |
| Período de desenvolvimento | 08/02 – 25/03/2026 (46 dias) |
| Fases concluídas | 6 / 6 |
| Controllers PHP | ~30 |
| Views PHP | ~40 |
| Perfis de usuário | 4 (Gestor, Colaborador, Revisor/Especialista, Desenvolvedor) |
| Status de espécie | 7 (Sem dados → Publicada) |
| Deploy | penomato.app.br (HostGator) |
