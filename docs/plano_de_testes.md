# PLANO DE TESTES

**Projeto de Extensão:** Programa de Extensão UFMS Digital (95DX7.200525)

**Nome completo:** [SEU NOME COMPLETO]
**Disciplina:** Projeto Integrador de Tecnologia da Informação III
**Semestre letivo:** [SEU SEMESTRE]
**Curso:** [SEU CURSO]
**Público-alvo:** Engenheiros florestais, estudantes de botânica e colaboradores científicos do Cerrado
**Local de realização:** Campo Grande – MS (desenvolvimento local via XAMPP e produção em penomato.app.br)

---

## PLANO DE TESTES — PENOMATO MVP: PLATAFORMA WEB DE CADASTRO E VALIDAÇÃO CIENTÍFICA DE ESPÉCIES DO CERRADO

---

## Resumo

O presente plano de testes foi elaborado para garantir a qualidade do Penomato MVP, plataforma web desenvolvida para o cadastro, validação e publicação colaborativa de informações morfológicas de espécies vegetais do Cerrado, em parceria com a UEMS — Universidade Estadual de Mato Grosso do Sul. O documento define as estratégias, tipos de teste, recursos, cronograma e critérios de aceitação aplicados ao sistema, cobrindo desde a validação de fluxos funcionais ponta a ponta até verificações de segurança e integridade de dados, com o objetivo de assegurar a confiabilidade da aplicação antes de sua utilização científica em campo.

**Palavras-chave:** Teste de software. Plano de testes. Aplicação web. Cerrado. Botânica.

---

## 1. Introdução

O Penomato é uma plataforma web colaborativa desenvolvida como projeto de extensão universitária em parceria entre a UFMS e a UEMS (Engenharia Florestal), com foco no bioma Cerrado. O sistema permite que colaboradores científicos realizem o cadastro de características morfológicas de espécies vegetais nativas, com suporte de Inteligência Artificial para preenchimento automatizado dos atributos botânicos, e que especialistas revisem, aprovem ou contestem os dados cadastrados antes de sua publicação. O fluxo completo da plataforma percorre os seguintes estágios: cadastro de dados → confirmação → revisão especializada → publicação.

### 1.1 Objetivo do Plano de Testes

O objetivo deste plano é definir uma estratégia estruturada de testes para o Penomato MVP, garantindo que os módulos críticos do sistema funcionem corretamente de forma isolada e integrada, e que os requisitos de segurança, usabilidade e confiabilidade sejam atendidos antes da utilização do sistema em contexto científico real.

### 1.2 Escopo

O plano cobre os seguintes módulos e funcionalidades do sistema:

- **Autenticação e controle de acesso** — cadastro de usuários, login, confirmação de e-mail, recuperação de senha e redirecionamento por perfil (Gestor, Colaborador, Revisor/Especialista, Desenvolvedor)
- **Fluxo do colaborador** — upload de imagens, preenchimento de atributos morfológicos com auxílio de IA (DeepSeek/Claude/OpenAI/Gemini), confirmação de dados e geração de artigo científico
- **Fluxo do revisor/especialista** — visualização da fila de artigos, aprovação e contestação com notificação por e-mail
- **Módulo de exemplares** — cadastro de exemplar em campo com geolocalização, foto de identificação e código único (XX000)
- **Módulo Flora do Cerrado** — consulta pública de nomes aceitos via REFLORA/JBRJ
- **Segurança** — proteção de rotas, ausência de credenciais no repositório, prevenção de SQL Injection

### 1.3 O que não está no escopo

Não fazem parte deste plano de testes:
- Testes de performance sob alta carga (não aplicável ao estágio atual do MVP)
- Testes automatizados via frameworks (ex: PHPUnit) — os testes são executados manualmente
- Testes de acessibilidade (WCAG)
- Aplicativo móvel nativo (previsto para v3.0)

### 1.4 Recursos considerados para os testes

Os testes foram construídos com base em:
- Histórico de 210+ commits no repositório Git (08/02 a 22/04/2026)
- Roteiro de TVV (Teste, Validação e Verificação) elaborado durante o desenvolvimento
- Kanban do projeto com registro de todas as funcionalidades implementadas
- Análise dos arquivos-chave: `controlador_painel_revisor.php`, `inserir_dados_internet.php`, `banco_de_dados.php`, `config/producao.php`

---

## 2. Estratégias de Teste

### 2.1 Testes Funcionais

**Objetivo:** Verificar se cada funcionalidade do sistema produz o resultado esperado conforme os requisitos definidos.

**Escopo:** Todos os módulos listados na seção 1.2, com foco nos fluxos de maior criticidade: autenticação, upload de dados, integração com IA e fluxo do revisor.

**Ferramentas:**
- Navegador Google Chrome (versão mais recente)
- DevTools do Chrome para inspeção de requisições e erros de console
- Ambiente de produção: `penomato.app.br`

**Casos de teste funcionais:**

| ID | Módulo | Cenário | Resultado esperado |
|----|--------|---------|-------------------|
| FT-01 | Autenticação | Cadastro de novo colaborador | Conta criada, e-mail de confirmação enviado |
| FT-02 | Autenticação | Login com e-mail não confirmado | Acesso negado com mensagem clara |
| FT-03 | Autenticação | Recuperação de senha | E-mail com link enviado; e-mail pré-preenchido na tela |
| FT-04 | Gestor | Aprovar membro sem e-mail confirmado | Sistema bloqueia aprovação com aviso |
| FT-05 | Colaborador | Upload de imagens das partes da planta | Imagens salvas no banco; progresso exibido |
| FT-06 | IA | Preencher atributos morfológicos via IA | Formulário preenchido automaticamente com dados do REFLORA/Lorenzi |
| FT-07 | IA | IA retorna valor fora do vocabulário botânico | Modal de validação abre com opções válidas para seleção |
| FT-08 | Colaborador | Salvar atributos confirmados | Status da espécie avança corretamente |
| FT-09 | Revisor | Aprovar artigo | Status → `revisada`; e-mail de confirmação enviado ao colaborador |
| FT-10 | Revisor | Contestar artigo com motivo | Status → `contestado`; e-mail com feedback enviado ao colaborador |
| FT-11 | Revisor | Contestar artigo sem motivo | Sistema exibe erro; contestação não é registrada |
| FT-12 | Exemplar | Cadastrar exemplar com geolocalização | Exemplar salvo com GPS, foto e código único XX000 |
| FT-13 | Flora do Cerrado | Consultar nome científico | Retorna nomes aceitos do JBRJ via REFLORA |
| FT-14 | Busca | Busca morfológica por atributos | Retorna espécies compatíveis com os filtros selecionados |

---

### 2.2 Testes de Integração

**Objetivo:** Validar que os módulos do sistema funcionam corretamente em conjunto, percorrendo os fluxos completos ponta a ponta como um usuário real faria.

**Escopo:** Dois fluxos principais de integração entre módulos.

**Ferramentas:**
- Navegador Google Chrome
- Duas contas de teste: uma como Colaborador, outra como Revisor/Especialista
- Ambiente de produção: `penomato.app.br`
- Caixa de entrada de e-mail real para verificar notificações

**Fluxo 1 — Colaborador até Revisor:**

```
Cadastro → Confirmação de e-mail → Aprovação pelo Gestor
→ Login como Colaborador → Upload de imagens
→ IA preenche atributos → Colaborador confirma/edita
→ Artigo gerado → Aparece na fila do Revisor com status "pendente"
→ Revisor aprova → Status vira "revisada" → E-mail chega ao Colaborador
```

**Fluxo 2 — Contestação e retrabalho:**

```
Revisor contesta → Status vira "contestado" → E-mail com motivo chega ao Colaborador
→ Colaborador corrige os dados → Novo artigo submetido → Retorna à fila do Revisor
```

---

### 2.3 Testes de Segurança

**Objetivo:** Verificar que o sistema não expõe dados sensíveis, que as rotas protegidas são inacessíveis sem autenticação e que as entradas de usuário são tratadas de forma segura.

**Escopo:** Autenticação, controle de sessão, armazenamento de credenciais e consultas ao banco de dados.

**Ferramentas:**
- Google Chrome com DevTools
- Git (`git status`, `git log`) para verificar rastreamento de arquivos sensíveis
- Tentativas manuais de acesso direto a URLs protegidas

**Casos de teste de segurança:**

| ID | Cenário | Resultado esperado |
|----|---------|-------------------|
| ST-01 | Acessar página interna sem sessão ativa | Redirecionamento para tela de login |
| ST-02 | Verificar se `config/dev_local.php` está no repositório | Arquivo não rastreado pelo Git (`.gitignore`) |
| ST-03 | Verificar se credenciais de banco/SMTP estão em algum arquivo do repositório | Nenhuma credencial exposta; apenas `config/producao.exemplo.php` com valores fictícios |
| ST-04 | Inserir caracteres especiais SQL em campos de busca | Consulta tratada via prepared statements; sem erro ou execução indevida |
| ST-05 | Tentar acessar painel do Gestor logado como Colaborador | Acesso negado ou redirecionamento |
| ST-06 | Tentar aprovar membro com e-mail não confirmado | Sistema bloqueia com mensagem de aviso |

---

### 2.4 Testes de Usabilidade

**Objetivo:** Identificar problemas de navegação, links quebrados, mensagens de erro ausentes ou confusas e comportamentos inesperados na interface.

**Escopo:** Todos os menus e fluxos de navegação do sistema, com foco no painel do colaborador.

**Ferramentas:**
- Google Chrome com DevTools (aba Network para identificar erros 404/500)
- Navegação manual por todos os itens de menu de cada perfil

**Casos de teste de usabilidade:**

| ID | Cenário | Resultado esperado |
|----|---------|-------------------|
| UT-01 | Navegar por todas as opções do menu do Colaborador | Nenhuma página em branco ou erro 404 |
| UT-02 | Acessar `colaborador/upload_imagem.php` | Redirecionamento correto para `enviar_imagem.php` |
| UT-03 | IA falha (chave inválida) | Botão "Preencher manualmente" aparece; formulário abre normalmente |
| UT-04 | Contestar artigo sem preencher motivo | Mensagem de erro clara exibida na tela |
| UT-05 | Verificar e-mails enviados (aprovação, contestação) | E-mail recebido com conteúdo correto e remetente `noreply@penomato.app.br` |
| UT-06 | Acessar sistema em dispositivo móvel | Layout responsivo; formulários navegáveis |

---

## 3. Recursos e Ambientes

### 3.1 Hardware

| Recurso | Especificação |
|---------|--------------|
| Computador de desenvolvimento | Windows 10 Education, processador Intel/AMD, 8GB+ RAM |
| Smartphone (testes mobile) | Android com Chrome mobile (testes de responsividade) |
| Conexão de internet | Necessária para testes em produção e para chamadas à API de IA |

### 3.2 Software e Dependências

| Software | Finalidade |
|----------|-----------|
| XAMPP (Apache + MySQL + PHP 8.x) | Ambiente de desenvolvimento local |
| Google Chrome (versão atual) | Execução dos testes manuais |
| Git | Verificação de arquivos rastreados e histórico de commits |
| PHPMailer (via Composer) | Envio de e-mails transacionais — dependência do sistema |
| MySQL 8.x | Banco de dados da aplicação |
| Servidor HostGator (penomato.app.br) | Ambiente de produção para testes de integração |

### 3.3 Ambientes de Teste

| Ambiente | URL / Acesso | Uso |
|----------|-------------|-----|
| Desenvolvimento | `localhost/penomato_mvp` via XAMPP | Testes funcionais unitários |
| Produção | `penomato.app.br` | Testes de integração e segurança |

### 3.4 Equipe e Responsabilidades

| Papel | Responsabilidade |
|-------|----------------|
| Desenvolvedor / Testador | Elaboração do plano, execução de todos os casos de teste, registro de defeitos e correções |
| Orientador (UEMS) | Validação dos requisitos científicos e critérios de aceitação dos dados botânicos |

---

## 4. Gestão de Erros e Defeitos

### 4.1 Processo de Registro

Todo defeito encontrado durante a execução dos testes será registrado com as seguintes informações:

- **ID do caso de teste** que originou o defeito (ex: FT-07)
- **Descrição do comportamento observado** versus o esperado
- **Passos para reproduzir** o defeito
- **Evidência** (print ou descrição da tela)
- **Ambiente** em que foi encontrado (local ou produção)

O registro será feito no Kanban do projeto (`penomato_kanban.html`) como card na coluna "Em andamento" com tag `Bug`.

### 4.2 Política de Priorização

Os defeitos serão priorizados de acordo com o impacto no fluxo científico:

| Prioridade | Critério | Ação |
|-----------|---------|------|
| **Crítica (P1)** | Impede a execução de qualquer fluxo principal (login, upload, revisão, publicação) | Correção imediata antes de prosseguir |
| **Alta (P2)** | Afeta funcionalidade importante mas com contorno disponível | Correção antes da entrega |
| **Média (P3)** | Problema de UX ou comportamento inesperado sem impacto no fluxo | Correção programada |
| **Baixa (P4)** | Melhoria visual ou textual | Backlog |

### 4.3 Critério de Aceitação

O sistema será considerado aprovado para uso quando:
- Todos os casos de teste P1 (Crítica) passarem sem falha
- Todos os casos de teste P2 (Alta) passarem ou tiverem defeitos registrados com data de correção
- O fluxo de integração ponta a ponta (Fluxo 1 e Fluxo 2) executar sem interrupções

---

## 5. Cronograma

| Etapa | Atividade | Estimativa de esforço | Data prevista |
|-------|-----------|----------------------|--------------|
| 1 | Elaboração do Plano de Testes | 4h | 22/04/2026 |
| 2 | Preparação dos ambientes e contas de teste | 1h | 22/04/2026 |
| 3 | Execução dos Testes Funcionais (FT-01 a FT-14) | 3h | 23/04/2026 |
| 4 | Execução dos Testes de Integração (Fluxo 1 e 2) | 2h | 23/04/2026 |
| 5 | Execução dos Testes de Segurança (ST-01 a ST-06) | 1h | 24/04/2026 |
| 6 | Execução dos Testes de Usabilidade (UT-01 a UT-06) | 1h | 24/04/2026 |
| 7 | Registro e priorização de defeitos encontrados | 1h | 24/04/2026 |
| 8 | Correção dos defeitos P1 e P2 | 4h | 25/04/2026 |
| 9 | Re-execução dos casos de teste que falharam (regressão) | 2h | 26/04/2026 |
| 10 | Consolidação dos resultados e elaboração do relatório | 2h | 27/04/2026 |

**Total estimado:** 21 horas

---

## 6. Riscos e Mitigações

| Risco | Probabilidade | Impacto | Mitigação |
|-------|--------------|---------|-----------|
| API de IA (DeepSeek) fora do ar durante os testes | Média | Alto | Testar fallback manual (UT-03); alternar provider via config |
| E-mails transacionais caindo em spam | Média | Médio | Verificar pasta de spam; confirmar configuração SPF/DKIM no HostGator |
| Banco de dados de produção sem dados de teste | Alta | Alto | Executar script SQL de limpeza e reimportação (004) antes dos testes |
| Deploy automático sobrescrevendo correções durante os testes | Baixa | Alto | Evitar commits durante a janela de execução dos testes |
| Timeout do servidor durante chamada à IA (>120s) | Baixa | Médio | Parâmetro `max_execution_time=120` já configurado no `.htaccess` |
| Acesso a `penomato.app.br` indisponível (HostGator) | Baixa | Alto | Executar testes funcionais no ambiente local (XAMPP) como contingência |
| Perfis de teste insuficientes (falta Gestor/Revisor) | Média | Alto | Criar previamente contas para cada perfil antes de iniciar os testes |

---

## 7. Referências

BARBOSA, E. F.; SOUZA, S. R. S. de. **Introdução ao Teste de Software**. São Paulo: Elsevier, 2007.

IEEE. **IEEE Std 829-2008: Standard for Software and System Test Documentation**. New York: IEEE, 2008.

MYERS, G. J.; BADGETT, T.; SANDLER, C. **The Art of Software Testing**. 3. ed. Hoboken: John Wiley & Sons, 2011.

OWASP FOUNDATION. **OWASP Testing Guide v4.2**. Beaverton: OWASP, 2020. Disponível em: https://owasp.org/www-project-web-security-testing-guide/. Acesso em: 22 abr. 2026.

PRESSMAN, R. S.; MAXIM, B. R. **Engenharia de Software: uma abordagem profissional**. 8. ed. Porto Alegre: AMGH, 2016.

SOMMERVILLE, I. **Engenharia de Software**. 10. ed. São Paulo: Pearson, 2018.
