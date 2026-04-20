G    # AVALIAÇÃO DO MÓDULO 2 — TESTE DE SOFTWARE APLICADO

**Projeto de Extensão:** Programa de Extensão UFMS Digital (95DX7.200525)

**Nome completo:** Nilton Bakargi de Araújo

**Disciplina:** Projeto Integrador de Tecnologia da Informação III

**Semestre letivo:** 1º Semestre de 2026

**Curso:** Tecnologia da Informação

**Público-alvo:** Colaboradores (alunos e professores de Engenharia Florestal da UEMS), gestores de projeto e especialistas/revisores (docentes do Departamento de Botânica)

**Local de realização:** Universidade Federal de Mato Grosso do Sul (UFMS) — Campo Grande/MS, em parceria com a Universidade Estadual de Mato Grosso do Sul (UEMS) — Dourados/MS

---

# PLANO DE TESTES — SISTEMA PENOMATO MVP

---

## Resumo

O presente documento constitui o plano de testes do sistema Penomato MVP, uma plataforma web colaborativa de coleta, validação e publicação de dados científicos sobre espécies vegetais nativas do Cerrado brasileiro, desenvolvida como Trabalho de Conclusão de Curso do curso de Tecnologia da Informação da UFMS em parceria com o Departamento de Botânica da UEMS. O plano define estratégias, casos de teste representativos, ferramentas, responsabilidades, política de gestão de defeitos e cronograma para validação do sistema frente a seus requisitos funcionais, de segurança e de usabilidade, cobrindo os quatro perfis de usuário do sistema (gestor, colaborador, revisor/especialista e público) e o fluxo científico completo do cadastro de espécies à publicação de fichas técnicas.

**Palavras-chave:** Teste de software. Plano de testes. Sistemas web. Biodiversidade. Cerrado.

---

## 1 Introdução

### 1.1 O projeto em desenvolvimento

O Penomato é uma plataforma web colaborativa criada para resolver um problema estrutural da Engenharia Florestal: a identificação de espécies vegetais em florestas nativas do Cerrado brasileiro. O conhecimento empírico dos chamados "mateiros" — profissionais capazes de identificar espécies pelo tato, cheiro e aspecto visual — está desaparecendo, e bases de dados digitais de qualidade para o bioma Cerrado são escassas. O sistema resolve isso organizando a produção colaborativa de dados científicos com valor acadêmico reconhecido: cada espécie completamente documentada gera uma ficha técnica revisada por especialistas, com crédito formal para todos os contribuidores.

O MVP foi desenvolvido em PHP 7.4/8.0, MySQL 8.0 e HTML/CSS/JavaScript, seguindo arquitetura MVC (*Model-View-Controller*), com as camadas de controle em `src/Controllers/`, visualização em `src/Views/` e configuração em `config/`. O banco de dados conta com 13 tabelas normalizadas, cobrindo usuários, espécies, características botânicas, imagens, exemplares de campo, artigos científicos, fila de aprovação e histórico de alterações. O acesso é controlado por quatro papéis de usuário: **gestor**, **colaborador**, **revisor/especialista** e acesso **público** (sem autenticação).

O fluxo central do sistema é a progressão de status de uma espécie vegetal ao longo de seis estágios:

```
sem_dados → dados_internet → identificada → registrada → em_revisao → revisada/publicado
```

Cada transição é condicionada por regras de negócio rígidas: o status *identificada* só é atingido quando 100% dos atributos morfológicos forem confirmados; o status *registrada* é disparado automaticamente quando todas as partes da planta (folha, flor, fruto, caule, semente e hábito) estiverem fotografadas ou formalmente dispensadas; a geração do artigo científico exige obrigatoriamente os dois status anteriores de forma simultânea.

### 1.2 Objetivo do plano de testes

Este plano tem como objetivos específicos:

1. Verificar a correção das regras de negócio que governam a progressão de status das espécies, especialmente as transições automáticas e as pré-condições de cada etapa;
2. Validar a integridade das operações de escrita no banco de dados, com ênfase nas transações envolvendo upload de imagens, consolidação de sessões temporárias e geração de artigos;
3. Identificar vulnerabilidades de segurança relevantes para um sistema que armazena dados científicos, imagens de campo e informações de usuários vinculados a instituições de ensino;
4. Avaliar a usabilidade da interface para colaboradores sem formação em TI, que representam o principal grupo de usuários do sistema.

### 1.3 Escopo do projeto testado

O escopo inclui todos os módulos funcionais implementados até a versão atual (commit `fb48fef`):

| Módulo | Componentes Principais |
|---|---|
| Autenticação | Cadastro com confirmação por e-mail (`cadastro_controlador.php`, `ativar_conta_controlador.php`), login com proteção contra força bruta (`login_controlador.php`), recuperação e redefinição de senha, alteração de e-mail, logout |
| Gestão de espécies | Cadastro de espécies em lote, atribuição de colaboradores, priorização, aprovação de membros aguardando (`controlador_gestor.php`) |
| Dados morfológicos | Inserção de atributos botânicos e imagens da internet em fluxo de duas etapas com sessão temporária (`inserir_dados_internet.php`, `processar_upload_temporario.php`, `finalizar_upload_temporario.php`) |
| Confirmação de características | Revisão atributo a atributo com transição automática para status *identificada* (`confirmar_caracteristicas.php`) |
| Exemplares de campo | Cadastro de exemplares físicos com geração de código sequencial (PN001...), extração de GPS via EXIF e envio de e-mail ao especialista (`processar_cadastro_exemplar.php`) |
| Upload de exsicatas | Upload de fotografias de campo vinculadas a exemplar e parte da planta, validação por MIME type, transação com verificação automática de completude (`processar_upload_exsicata.php`) |
| Fila de aprovação | Registro de ações, aprovação/rejeição com notificação por e-mail, mapeamento de aprovação para transição de status (`aprovacao_acoes.php`, `fila_aprovacao`) |
| Revisão por especialista | Listagem de pendentes, início de revisão, aprovação/rejeição com motivo (`controlador_painel_revisor.php`) |
| Geração de artigo | Composição da ficha técnica em HTML (`gerar_artigo.php`) |
| Publicação pública | Ficha pública de espécie, busca por características morfológicas, módulo Flora do Cerrado |
| Gestão de usuários | Perfil, alteração de senha, exclusão de conta |

**Fora do escopo:** módulo de QR Code (não implementado), testes de carga/desempenho com volume elevado de usuários simultâneos, compatibilidade com dispositivos móveis além do definido no ambiente, módulo de contestação pós-publicação (implementado parcialmente).

### 1.4 Recursos do projeto considerados para a construção dos testes

A construção dos casos de teste se baseou diretamente nos seguintes recursos:

- **Código-fonte** dos 40+ arquivos PHP de controle e visualização, lidos e analisados para extração das regras de validação, fluxos de dados e condições de erro;
- **Esquema do banco de dados** (`database/penomato_2026-04-08.sql`) com 13 tabelas, incluindo constraints, chaves estrangeiras, enumerações e índices;
- **Regras de negócio documentadas** no fluxo científico do sistema (do interesse à publicação);
- **Análise de lacunas de segurança** identificadas durante a leitura do código: ausência de token CSRF, ausência de requisitos mínimos de senha, ausência de rate limiting em endpoints de upload.

---

## 2 Estratégias de teste

### 2.1 Testes funcionais

#### 2.1.1 Objetivos

Verificar se cada funcionalidade do sistema se comporta conforme os requisitos definidos, simulando a interação real de cada perfil de usuário com a interface. Garantir que os fluxos de ponta a ponta — do cadastro de espécie à publicação da ficha — sejam executáveis sem erros e produzam os estados esperados no banco de dados.

#### 2.1.2 Escopo e casos de teste representativos

Os casos de teste são organizados por módulo, priorizando os fluxos de maior criticidade para o negócio.

---

**Módulo: Autenticação**

| ID | Descrição | Pré-condição | Dados de entrada | Resultado esperado |
|---|---|---|---|---|
| FT-AUTH-01 | Cadastro de colaborador com e-mail válido | Nenhum usuário com o e-mail existe | nome="Ana Botânica", email="ana@uems.br", perfil="identificador", senha confirmada | Usuário criado com `status_verificacao='pendente'`, `ativo=0`; e-mail de ativação enviado com token de 64 caracteres |
| FT-AUTH-02 | Ativação de conta por e-mail | Token válido e não expirado em `tokens_verificacao_email` | URL com `?token={token_valido}` | `status_verificacao='verificado'`, `ativo=1`, token marcado como `usado=1` |
| FT-AUTH-03 | Tentativa de ativação com token expirado (>24h) | Token presente mas `expira_em < NOW()` | URL com `?token={token_expirado}` | Mensagem de erro "link expirado"; link para reenvio exibido |
| FT-AUTH-04 | Login bem-sucedido de colaborador | Usuário verificado e ativo | email e senha corretos | Sessão criada com `usuario_tipo`, `usuario_id`, `login_time`; redirecionamento para `/entrar_colaborador.php` |
| FT-AUTH-05 | Bloqueio após 5 tentativas de login falhas | — | 5 senhas erradas consecutivas do mesmo IP | Na 6ª tentativa: mensagem de bloqueio por 15 minutos; nova entrada em `tentativas_login` |
| FT-AUTH-06 | Expiração de sessão por inatividade | Usuário logado há 31 minutos sem interação | Acesso a qualquer página protegida | Redirecionamento para login; sessão destruída |
| FT-AUTH-07 | Recuperação de senha — link válido | Conta ativa com e-mail cadastrado | e-mail enviado ao formulário | Token gerado em `tokens_recuperacao_senha` com `expira_em = NOW() + 1h`; e-mail enviado |
| FT-AUTH-08 | Redefinição de senha — nova senha aplicada | Token válido, não expirado, não usado | nova_senha e confirmacao_senha idênticas | `senha_hash` atualizado com `password_hash()`; token marcado como `usado=1` |
| FT-AUTH-09 | Impedimento de segundo cadastro de gestor | Um gestor já existe no banco | Tentativa de cadastro com perfil "gestor" | Mensagem de erro "já existe gestor cadastrado"; nenhum registro inserido |
| FT-AUTH-10 | Controle de acesso — colaborador acessa painel do gestor | Usuário logado como colaborador | Acesso direto à URL `/controlador_gestor.php` | HTTP 403 ou redirecionamento com mensagem "acesso negado" |

---

**Módulo: Fluxo de progressão de status (regra de negócio central)**

| ID | Descrição | Pré-condição | Ação | Resultado esperado |
|---|---|---|---|---|
| FT-STATUS-01 | Inserção de dados morfológicos muda status para `dados_internet` | Espécie com status `sem_dados` | Colaborador finaliza importação via `finalizar_upload_temporario.php` | `species_administrativo.status = 'dados_internet'`; entrada em `fila_aprovacao` com `tipo='dados_internet'` |
| FT-STATUS-02 | Confirmação de todos os atributos muda status para `identificada` | Espécie com `dados_internet`; todos os 60+ campos confirmados | Último atributo confirmado via `confirmar_caracteristicas.php` | `status = 'identificada'`; campo `data_descrita` preenchido com timestamp atual |
| FT-STATUS-03 | Status NÃO muda para `identificada` com atributo pendente | Espécie com 59 de 60 atributos confirmados | Tentativa de avanço de status | Status permanece `dados_internet`; nenhum campo de data atualizado |
| FT-STATUS-04 | Upload da última parte muda status para `registrada` automaticamente | Espécie com 5 das 6 partes fotografadas (sem partes dispensadas) | Upload de foto da 6ª parte via `processar_upload_exsicata.php` | `status = 'registrada'` dentro da mesma transação do INSERT; `data_registrada` preenchida |
| FT-STATUS-05 | Parte dispensada conta para completude de registro | Espécie com 5 partes fotografadas e 1 registrada em `partes_dispensadas` | — | Sistema considera a espécie `registrada`; verificação em `processar_upload_exsicata.php` incluiu dispensadas na checagem |
| FT-STATUS-06 | Geração de artigo bloqueada se espécie não é `identificada` | Espécie `registrada` mas não `identificada` | Tentativa de geração via `gerar_artigo.php` | Erro: pré-condição não atendida; artigo não gerado |
| FT-STATUS-07 | Geração de artigo bloqueada se espécie não é `registrada` | Espécie `identificada` mas sem partes registradas | Tentativa de geração | Erro: pré-condição não atendida |
| FT-STATUS-08 | Geração de artigo bem-sucedida quando ambas as condições são atendidas | Espécie `identificada` E `registrada` | Geração via `gerar_artigo.php` | Artigo HTML inserido em `artigos` com `status='rascunho'`; entrada em `fila_aprovacao` com `tipo='revisao'` |
| FT-STATUS-09 | Aprovação do revisor muda status para `revisada` | Artigo em `fila_aprovacao` com `status='pendente'` e `tipo='revisao'` | Revisor aprova via `aprovacao_acoes.php` | `fila_aprovacao.status='aprovado'`; `artigos.status='publicado'`; `species.status='revisada'` |
| FT-STATUS-10 | Rejeição com motivo retorna espécie ao status anterior | Artigo em revisão | Revisor rejeita com motivo preenchido | `fila_aprovacao.motivo_rejeicao` salvo; e-mail de notificação enviado ao colaborador; status da espécie revertido |

---

**Módulo: Upload de exsicatas e exemplares**

| ID | Descrição | Dados de entrada | Resultado esperado |
|---|---|---|---|
| FT-UPLOAD-01 | Upload de imagem JPEG válida | Arquivo `.jpg`, MIME `image/jpeg`, 3 MB, exemplar aprovado, parte `folha` | Arquivo salvo em `uploads/exsicatas/{especie_id}/folha_{YYYYMMDD}_{HHMMSS}_{RRR}.jpg`; registro em `especies_imagens` com `status_validacao='aprovado'` |
| FT-UPLOAD-02 | Rejeição de arquivo acima de 15 MB | Arquivo JPEG de 16 MB | Erro "arquivo excede o tamanho máximo"; nenhum arquivo salvo; nenhum registro inserido |
| FT-UPLOAD-03 | Rejeição de arquivo com extensão `.php` disfarçado | Arquivo renomeado para `malware.jpg` mas MIME `application/x-php` detectado por `finfo` | Erro "tipo de arquivo não permitido"; verificação por `finfo_file()` impede o salvamento |
| FT-UPLOAD-04 | Upload com exemplar não aprovado | Exemplar com `status='aguardando_revisao'` | Erro: consulta `SELECT id FROM exemplares WHERE id=? AND status='aprovado'` retorna vazio; upload bloqueado |
| FT-UPLOAD-05 | Rollback de transação em falha de banco após salvar arquivo | Arquivo salvo em disco; INSERT em `especies_imagens` falha | Arquivo deletado do disco; nenhum registro órfão no banco; usuário recebe mensagem de erro interno |
| FT-UPLOAD-06 | Cadastro de exemplar com extração automática de GPS via EXIF | Foto com metadados EXIF contendo GPSLatitude/GPSLongitude; campos de latitude/longitude deixados em branco | `exemplares.latitude` e `exemplares.longitude` preenchidos com valores convertidos de frações EXIF para decimal; sinal negativo aplicado para referência `S` |
| FT-UPLOAD-07 | Geração de código sequencial de exemplar | Último código no banco é `PN042` | Novo cadastro de exemplar | Novo código gerado = `PN043`; unicidade verificada pela `UNIQUE KEY uk_codigo` |

---

**Módulo: Sessão temporária de importação**

| ID | Descrição | Pré-condição | Resultado esperado |
|---|---|---|---|
| FT-TEMP-01 | Finalização bloqueada com partes faltando | Sessão com imagens para 4 das 6 partes | `array_diff()` detecta partes ausentes; mensagem de erro lista quais partes faltam; consolidação abortada |
| FT-TEMP-02 | Sessão expirada não pode ser retomada | `temp_imagens_candidatas.expira_em < NOW()` | Acesso ao passo de finalização retorna erro "sessão expirada"; registros com `status='expirado'` não são processados |
| FT-TEMP-03 | Consolidação completa com 6 partes | Sessão com 6 imagens e dados botânicos preenchidos | Imagens movidas de `temp_imagens_candidatas` para `especies_imagens`; características inseridas em `especies_caracteristicas`; status avançado; sessão `$_SESSION['importacao_temporaria']` destruída |

---

#### 2.1.3 Ferramentas

- **Playwright** (Node.js 20 LTS) para automação de fluxos de regressão — os 5 fluxos críticos (autenticação, cadastro de espécie, upload de exsicata, geração de artigo, revisão e aprovação) serão automatizados para execução após cada conjunto de correções;
- **Testes manuais guiados por roteiro** para todos os casos de teste listados, com registro em planilha de controle de defeitos (descrita na Seção 4);
- **Browser DevTools** (Chrome) para inspeção de requisições XHR, cookies de sessão e cabeçalhos de resposta HTTP.

---

### 2.2 Testes de integração

#### 2.2.1 Objetivos

Verificar a comunicação correta entre os componentes internos da aplicação: os controllers PHP com o banco de dados MySQL (via PDO), a integridade das transações multi-passo, a consistência dos dados após operações compostas e o envio de e-mails transacionais via PHPMailer.

A escolha por testes de integração como estratégia central (em oposição a testes unitários com mocks) é justificada pelo histórico do projeto: as regras de negócio mais críticas — como a transição automática de status — estão implementadas dentro de transações SQL complexas que somente podem ser validadas com acesso real ao banco. Isolar esses comportamentos com mocks de banco introduziria divergências capazes de ocultar falhas reais de integridade, como race conditions em transações concorrentes ou violações de constraint que só se manifestam contra MySQL real.

#### 2.2.2 Escopo e casos de teste representativos

| ID | Componente | Cenário | Assertivas |
|---|---|---|---|
| IT-BD-01 | `login_controlador.php` + tabela `usuarios` | Login com credenciais corretas | `password_verify()` retorna `true`; sessão populada com todos os 7 campos esperados; `usuarios.ultimo_acesso` atualizado |
| IT-BD-02 | `login_controlador.php` + tabela `tentativas_login` | 5 falhas consecutivas do mesmo IP | 5 registros em `tentativas_login`; na 6ª tentativa, `SELECT COUNT(*) >= 5` bloqueia acesso sem consultar `usuarios` |
| IT-BD-03 | `processar_upload_exsicata.php` + transação | Upload da última parte pendente de uma espécie | `BEGIN` → INSERT em `especies_imagens` → INSERT em `historico_alteracoes` → `SELECT DISTINCT parte_planta` retorna todas as 6 → `UPDATE especies_administrativo SET status='registrada'` → `COMMIT`; nenhum estado intermediário persiste em caso de falha |
| IT-BD-04 | `processar_cadastro_exemplar.php` + `exemplares` | Código sequencial com banco vazio e com registros | Banco vazio: `MAX(CAST(SUBSTRING(codigo,3) AS UNSIGNED))` retorna NULL → código = `PN001`; com último `PN042` → código = `PN043` |
| IT-BD-05 | `finalizar_upload_temporario.php` + múltiplas tabelas | Consolidação bem-sucedida de sessão temporária | Em uma única transação: `temp_imagens_candidatas.status='confirmado'`, registros inseridos em `especies_imagens`, `especies_caracteristicas` atualizado via `INSERT ... ON DUPLICATE KEY UPDATE`, `fila_aprovacao` com entrada `tipo='dados_internet'` |
| IT-BD-06 | `aprovacao_acoes.php` + mapeamento de status | Aprovação de item `tipo='revisao'` | `fila_aprovacao.status='aprovado'`, `fila_aprovacao.data_decisao` preenchida, `artigos.status='publicado'`, `especies_administrativo.status='revisada'` — tudo em transação única |
| IT-EMAIL-01 | `cadastro_controlador.php` + PHPMailer | Cadastro de colaborador novo | Mailtrap captura 1 e-mail para o endereço cadastrado; subject contém "ativação"; corpo contém URL com token de 64 chars; token em `tokens_verificacao_email` com `expira_em = NOW() + 24h` |
| IT-EMAIL-02 | `processar_cadastro_exemplar.php` + PHPMailer | Cadastro de exemplar com especialista atribuído | Mailtrap captura 1 e-mail para o e-mail do especialista; corpo referencia código do exemplar gerado |
| IT-EMAIL-03 | `aprovacao_acoes.php` + PHPMailer | Rejeição de artigo com motivo | E-mail enviado ao usuário que submeteu; corpo contém `motivo_rejeicao` salvo na base |
| IT-INT-01 | Integridade referencial | Exclusão de usuário atribuído a espécie | `especies_administrativo.atribuido_a` muda para `NULL` (ON DELETE SET NULL); sem erro de FK; espécie permanece no sistema |
| IT-INT-02 | Integridade referencial | Exclusão de espécie | Cascade em `especies_caracteristicas`, `especies_imagens`, `exemplares`, `artigos`, `fila_aprovacao`, `historico_alteracoes`; nenhum registro órfão remanescente |

#### 2.2.3 Ferramentas

- **PHPUnit 10.x** — framework de testes para PHP; os testes de integração criarão fixtures no banco de teste antes de cada caso e limpam após (`setUp()`/`tearDown()`);
- **Banco de dados isolado** — instância MySQL separada (`penomato_test`), populada com scripts determinísticos em `database/seeds/`; credenciais configuradas via variável de ambiente `APP_ENV=test`, que carrega `config/banco_de_dados.php` apontando para o banco de teste;
- **Mailtrap** (plano gratuito, SaaS) — servidor SMTP falso que captura todos os e-mails enviados durante os testes; a API do Mailtrap permite assertivas programáticas sobre destinatário, assunto e corpo das mensagens.

---

### 2.3 Testes de segurança

#### 2.3.1 Objetivos

Identificar e documentar vulnerabilidades que comprometam a integridade dos dados científicos, a privacidade dos usuários ou o controle de acesso por perfil. Os testes são orientados pelo **OWASP Top 10 (2021)**, priorizando as categorias mais relevantes para o perfil da aplicação.

#### 2.3.2 Escopo e casos de teste representativos

A análise do código-fonte revelou, previamente aos testes formais, as seguintes condições que deverão ser verificadas:

**A01 — Quebra de controle de acesso**

| ID | Cenário | Procedimento | Resultado esperado |
|---|---|---|---|
| ST-AC-01 | Colaborador acessa endpoint do gestor via URL direta | Logar como colaborador; acessar diretamente `GET /controlador_gestor.php` | `verificar_acesso.php::permitirApenas('gestor')` intercepta; redirecionamento ou HTTP 403 |
| ST-AC-02 | Usuário não autenticado acessa área restrita | Sem sessão ativa; acessar `GET /confirmar_caracteristicas.php?especie_id=1` | `sessaoValida()` retorna `false`; redirecionamento para login |
| ST-AC-03 | Colaborador aprova item da fila de aprovação | Logar como colaborador; POST direto para `aprovacao_acoes.php` com `acao=aprovar` | `permitirApenas(['gestor','revisor'])` bloqueia; ação não executada |
| ST-AC-04 | Acesso a imagens de upload via URL sem autenticação | Sem sessão; acessar diretamente `GET /uploads/exsicatas/{especie_id}/folha_*.jpg` | Arquivo não deve ser servido sem verificação de sessão (configuração Apache `.htaccess`) |

**A02 — Falhas criptográficas**

| ID | Cenário | Procedimento | Resultado esperado |
|---|---|---|---|
| ST-CRYPT-01 | Armazenamento de senha | Inspecionar `usuarios.senha_hash` após cadastro | Hash começa com `$2y$` (bcrypt, custo padrão); nenhum campo de texto claro presente |
| ST-CRYPT-02 | Token de recuperação de senha na URL | Capturar URL do link de recuperação de senha | Token de 64 caracteres (32 bytes hexadecimais gerados por `random_bytes()`); não previsível por enumeração |
| ST-CRYPT-03 | Transmissão de credenciais | Inspecionar form de login no browser | Campo `senha` com `type="password"`; em produção, verificar HTTPS obrigatório |

**A03 — Injeção SQL**

| ID | Cenário | Procedimento | Resultado esperado |
|---|---|---|---|
| ST-INJ-01 | Campo de busca de espécies | Inserir `' OR '1'='1` no campo de nome científico | Query usa prepared statement com `escaparLike()`; nenhuma linha adicional retornada; sem erro SQL exposto |
| ST-INJ-02 | Parâmetro `especie_id` em URL | Acessar `?especie_id=1 UNION SELECT 1,2,3--` | `(int)$_GET['especie_id']` converte para 0 ou 1; UNION não executado |
| ST-INJ-03 | Campo de e-mail no login | Inserir `admin@test.com' --` no campo e-mail | `filter_var(..., FILTER_VALIDATE_EMAIL)` rejeita antes da query; nenhuma consulta executada |

**A04 — Design inseguro / CSRF (lacuna identificada no código)**

| ID | Cenário | Procedimento | Resultado esperado (situação atual) |
|---|---|---|---|
| ST-CSRF-01 | Exclusão de conta via CSRF | Construir requisição POST forjada para `excluir_conta_controlador.php` a partir de domínio externo, com vítima logada | **FALHA ESPERADA**: não há token CSRF implementado; ação executada; esta é uma vulnerabilidade real documentada para correção |
| ST-CSRF-02 | Aprovação forjada via CSRF | POST forjado para `aprovacao_acoes.php?acao=aprovar` com id válido | **FALHA ESPERADA**: sem proteção CSRF; aprovação executada se vítima tem perfil gestor/revisor |

**A07 — Falhas de identificação e autenticação**

| ID | Cenário | Procedimento | Resultado esperado |
|---|---|---|---|
| ST-AUTH-01 | Brute force no login | Script com 6 tentativas de senha errada em 15 minutos, mesmo IP | Após a 5ª, `SELECT COUNT(*) FROM tentativas_login WHERE ip=? AND criado_em >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)` retorna ≥ 5; acesso bloqueado |
| ST-AUTH-02 | Reutilização de token de ativação | Acessar URL de ativação novamente após conta já ativada | `tokens_verificacao_email.usado = 1`; retorno de erro "link já utilizado" |
| ST-AUTH-03 | Sessão não invalidada corretamente no logout | Após logout, usar botão "Voltar" do browser e tentar acessar página protegida | `session_destroy()` + `setcookie()` com expiração no passado; `sessaoValida()` retorna false |

**A05 — Configuração de segurança inadequada**

| ID | Cenário | Procedimento | Resultado esperado |
|---|---|---|---|
| ST-CFG-01 | Exibição de erros em produção | Forçar erro de banco (credencial errada); verificar resposta HTTP | `config/producao.php` define `display_errors=0`; nenhuma stack trace exposta ao cliente |
| ST-CFG-02 | Upload de arquivo PHP executável | Enviar arquivo `shell.php` renomeado para `shell.jpg` no upload de exsicata | `finfo_file()` detecta MIME `application/x-php`; arquivo recusado antes de ser movido para o diretório de uploads |

#### 2.3.3 Ferramentas

- **OWASP ZAP 2.15** — varredura automatizada de vulnerabilidades web; configurado em modo *Active Scan* contra o ambiente local; os alertas serão triados manualmente para descarte de falsos positivos, com documentação justificada dos descartados;
- **Burp Suite Community Edition 2024** — interceptação e manipulação manual de requisições HTTP para os testes de injeção SQL, CSRF e controle de acesso;
- **Testes manuais por perfil** — execução dos casos de controle de acesso com sessões simultâneas em abas diferentes do browser (Chrome e Firefox).

---

### 2.4 Testes de usabilidade

#### 2.4.1 Objetivos

Avaliar se a interface é compreensível e utilizável pelo público-alvo real do sistema: colaboradores com formação em Engenharia Florestal, sem treinamento prévio em sistemas de informação, que precisam realizar tarefas como inserir dados morfológicos, fotografar partes da planta e acompanhar o progresso de suas contribuições. A avaliação quantitativa será complementada por observação qualitativa de pontos de fricção.

#### 2.4.2 Escopo

As tarefas avaliadas refletem os fluxos de maior frequência de uso:

| # | Tarefa | Perfil | Critério de sucesso |
|---|---|---|---|
| T1 | Fazer cadastro e ativar conta por e-mail | Novo colaborador | Conta ativada sem assistência em até 5 minutos |
| T2 | Localizar uma espécie atribuída e inserir dados morfológicos | Colaborador autenticado | Dados salvos e status atualizado sem assistência |
| T3 | Fazer upload de foto de uma parte da planta vinculada ao número de etiqueta | Colaborador autenticado | Upload concluído com parte e exemplar corretos |
| T4 | Identificar o status atual de uma espécie e a próxima ação necessária | Colaborador autenticado | Resposta correta em até 2 minutos |
| T5 | Buscar uma espécie por característica morfológica na área pública | Visitante sem conta | Resultado obtido sem assistência |

#### 2.4.3 Ferramentas

- **Protocolo *think-aloud*** com 3 a 5 participantes recrutados no curso de Engenharia Florestal da UEMS; sessões individuais de 30 a 45 minutos com roteiro de tarefas predefinido;
- **Escala SUS** (*System Usability Scale*, Brooke, 1996) — questionário pós-sessão de 10 afirmações em escala Likert, cujo score (0–100) permite comparação com benchmarks da literatura (score > 68 = usabilidade acima da média);
- **Formulário de observação** — registro de erros cometidos, pontos de hesitação, dúvidas verbalizadas e comentários espontâneos para triangulação com o score SUS.

---

## 3 Recursos e ambientes

### 3.1 Hardware

| Componente | Especificação |
|---|---|
| Máquina de desenvolvimento e execução de testes | Notebook Acer, Windows 10 Education (Build 19045), Intel Core i5, 8 GB RAM |
| Servidor local | XAMPP 8.2 (Apache 2.4 + MySQL 8.0 + PHP 8.2) rodando na mesma máquina |
| Resolução de tela testada | 1366×768 (notebook) e 1920×1080 (monitor externo) |
| Dispositivos adicionais para usabilidade | Notebooks dos participantes da UEMS (resolução variável) |

### 3.2 Dependências de software

| Software | Versão | Propósito no contexto de testes |
|---|---|---|
| PHP | 7.4 / 8.0 | Runtime da aplicação; testes de integração executados com PHP 8.0 |
| MySQL | 8.0 (via XAMPP) | Banco de produção (`penomato`) e banco de teste isolado (`penomato_test`) |
| Apache | 2.4 (via XAMPP) | Servidor web local; configuração `.htaccess` relevante para ST-AC-04 |
| Composer | 2.x | Instalação do PHPUnit |
| PHPUnit | 10.x | Testes de integração e contratos de banco de dados |
| Node.js | 20 LTS | Runtime do Playwright |
| Playwright | 1.x | Automação dos 5 fluxos críticos de regressão |
| OWASP ZAP | 2.15 | Varredura automatizada de vulnerabilidades |
| Burp Suite Community | 2024.x | Interceptação manual de requisições HTTP |
| Mailtrap | Gratuito (SaaS) | Captura de e-mails transacionais em ambiente de teste |
| Google Chrome | Atual | Navegador principal |
| Mozilla Firefox | Atual | Navegador secundário (compatibilidade) |

### 3.3 Configuração do ambiente de teste isolado

Para garantir que os testes de integração não contaminem os dados reais e sejam reproduzíveis, será criado um banco de dados separado:

```sql
-- Criação do banco de teste
CREATE DATABASE penomato_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Variável de ambiente que chaveará config/banco_de_dados.php
APP_ENV=test
```

Os scripts de *seed* em `database/seeds/` populam o banco de teste com um conjunto mínimo e determinístico de dados: 1 gestor, 2 colaboradores, 1 revisor, 5 espécies em diferentes estados de progressão (uma por status principal), 2 exemplares aprovados e 6 imagens de exsicata.

### 3.4 Equipe e responsabilidades

| Papel | Responsabilidade |
|---|---|
| Desenvolvedor/Testador (autor) | Elaboração de todos os casos de teste, implementação dos scripts PHPUnit e Playwright, execução de todos os testes, registro e resolução de defeitos |
| Orientador (UFMS) | Revisão do plano de testes, validação dos critérios de aceitação técnicos, homologação do encerramento |
| Parceiro de domínio (UEMS/Botânica) | Participação e recrutamento para os testes de usabilidade, validação dos fluxos botânicos e da terminologia morfológica apresentada nas telas |
| Participantes externos (usabilidade) | 3 a 5 alunos/professores de Engenharia Florestal da UEMS; participação voluntária nas sessões *think-aloud* |

---

## 4 Gestão de erros e defeitos

### 4.1 Registro e acompanhamento

Cada defeito identificado durante qualquer fase de teste será registrado em uma planilha de controle (`docs/defeitos.csv`, versionada no repositório Git) com os seguintes campos:

| Campo | Tipo | Descrição |
|---|---|---|
| `DEF_ID` | Texto | Identificador sequencial — DEF-001, DEF-002, ... |
| `DATA` | Data | Data de identificação |
| `TIPO_TESTE` | Enum | Funcional / Integração / Segurança / Usabilidade |
| `MODULO` | Texto | Módulo afetado — ex.: autenticação, upload_exsicata, status |
| `DESCRICAO` | Texto | Comportamento observado vs. comportamento esperado |
| `PASSOS` | Texto | Sequência mínima para reprodução |
| `DADOS_ENTRADA` | Texto | Valores exatos que provocaram o defeito |
| `EVIDENCIA` | Texto | Caminho para screenshot ou log capturado |
| `PRIORIDADE` | Enum | Crítica / Alta / Média / Baixa |
| `STATUS` | Enum | Aberto / Em correção / Corrigido / Verificado / Fechado / Não será corrigido |
| `COMMIT_CORRECAO` | Texto | Hash do commit que aplicou a correção |
| `CAUSA_RAIZ` | Texto | Causa identificada após investigação |

Os defeitos de segurança identificados em análise estática do código (CSRF ausente, sem rate limiting em uploads, força de senha sem validação) são pré-registrados como **DEF-SEC-001** a **DEF-SEC-003** com status *Aberto* antes da execução formal dos testes.

### 4.2 Política de priorização

A prioridade é determinada pela combinação de **impacto** (severidade das consequências) e **frequência** (probabilidade de ocorrência em uso normal):

| Prioridade | Critério de impacto | Critério de frequência | Prazo de resolução | Exemplos concretos neste projeto |
|---|---|---|---|---|
| **Crítica** | Perda ou corrupção de dados científicos; falha de segurança explorável; bloqueio total de um perfil de usuário | Qualquer | Imediato, antes de qualquer outra atividade | Transação de upload não faz rollback: arquivo salvo no disco sem registro no banco; SQL injection funcional em campo de busca; login inacessível para todos os usuários |
| **Alta** | Regra de negócio crítica com comportamento incorreto; funcionalidade principal indisponível para caso específico | Frequente | Até 2 dias úteis | Status não transiciona para `registrada` após upload da última parte; artigo gerado sem verificar pré-condições de status; token de ativação aceito após expiração |
| **Média** | Comportamento inesperado em fluxo secundário; mensagens de erro confusas para o usuário; dados exibidos incorretamente | Ocasional | Até 5 dias úteis | Filtro de busca retorna conjunto incorreto em combinação específica de características; mensagem de erro não distingue "e-mail inválido" de "e-mail já cadastrado" |
| **Baixa** | Defeito estético, de texto, de alinhamento ou de tradução sem impacto na funcionalidade | Qualquer | Sprint seguinte ou versão futura | Rótulo de campo com typo; espaçamento quebrado em resolução específica; data exibida em formato incorreto |

**Regra de encerramento de fase:** a fase de testes só pode ser considerada encerrada quando não houver defeitos abertos de prioridade **Crítica** ou **Alta**. Defeitos de prioridade **Média** devem ter plano de resolução documentado. Defeitos de prioridade **Baixa** são registrados para ciclos futuros.

---

## 5 Cronograma

O cronograma abrange 6 semanas, com início previsto para a semana de 21 de abril de 2026. As estimativas de esforço consideram a equipe de uma única pessoa executando todas as atividades.

| Semana | Etapa | Atividades | Complexidade | Esforço |
|---|---|---|---|---|
| **Sem. 1** (21–25 abr) | Preparação do ambiente | Criar banco `penomato_test`; configurar variável `APP_ENV`; instalar PHPUnit via Composer; instalar Playwright e Node.js; configurar conta Mailtrap; instalar OWASP ZAP | Baixa | 6 h |
| **Sem. 1** (21–25 abr) | Elaboração dos casos de teste | Detalhar todos os casos funcionais por módulo em planilha; criar roteiro de tarefas para usabilidade; criar template de registro de defeitos | Média | 8 h |
| **Sem. 2** (28 abr–2 mai) | Testes funcionais — autenticação e gestão | Executar FT-AUTH-01 a FT-AUTH-10; executar FT-STATUS-01 a FT-STATUS-10; registrar defeitos encontrados | Alta | 10 h |
| **Sem. 2–3** (28 abr–9 mai) | Testes funcionais — upload e sessão temp. | Executar FT-UPLOAD-01 a FT-UPLOAD-07; executar FT-TEMP-01 a FT-TEMP-03; testar fluxo completo de ponta a ponta | Alta | 10 h |
| **Sem. 3** (5–9 mai) | Implementação dos scripts PHPUnit | Criar fixtures de banco; implementar IT-BD-01 a IT-BD-06 e IT-EMAIL-01 a IT-EMAIL-03 e IT-INT-01 a IT-INT-02 | Alta | 14 h |
| **Sem. 3–4** (5–16 mai) | Automação com Playwright | Implementar scripts para os 5 fluxos críticos de regressão; executar e corrigir flakiness | Alta | 10 h |
| **Sem. 4** (12–16 mai) | Testes de segurança | Varredura OWASP ZAP; testes manuais ST-AC-01 a ST-AC-04; testes de injeção ST-INJ-01 a ST-INJ-03; testes CSRF ST-CSRF-01 a ST-CSRF-02; triagem de alertas do ZAP | Média | 10 h |
| **Sem. 5** (19–23 mai) | Testes de usabilidade | Agendamento e condução das sessões *think-aloud* com 3–5 participantes da UEMS; aplicação do SUS; análise dos resultados | Média | 8 h |
| **Sem. 4–5** (12–23 mai) | Análise e correção de defeitos | Triagem e priorização de todos os defeitos registrados; correção dos Críticos e Altos; documentação dos demais | Alta | 14 h |
| **Sem. 6** (26–30 mai) | Reteste, encerramento e relatório | Verificação de todas as correções; reexecução dos scripts Playwright como smoke test; fechamento dos defeitos verificados; elaboração do relatório final de testes | Média | 8 h |

**Esforço total estimado: 98 horas**

**Marco de encerramento:** semana de 26–30 de maio de 2026, com zero defeitos Críticos ou Altos em aberto.

---

## 6 Riscos e mitigações

| # | Risco | Prob. | Impacto | Estratégia de mitigação |
|---|---|---|---|---|
| **R1** | **Transação incompleta introduz inconsistência entre disco e banco** — arquivos salvos em disco sem o correspondente registro no banco, ou vice-versa, em caso de falha de energia ou crash durante upload | Baixa | Crítico | Verificar explicitamente em IT-BD-03 que um erro forçado no INSERT reverte o `move_uploaded_file`; se não houver rollback de arquivo implementado, registrar como defeito Crítico e corrigir antes do encerramento |
| **R2** | **Vulnerabilidade CSRF não corrigida antes da implantação** — já identificada em análise de código; nenhum formulário POST possui token CSRF | Alta | Alto | DEF-SEC-001 já registrado como Alta prioridade; correção requer adição de `$_SESSION['csrf_token']` gerado por `bin2hex(random_bytes(32))` em todos os formulários sensíveis; incluída no cronograma da Sem. 4–5 |
| **R3** | **Divergência entre banco de desenvolvimento e banco de teste** — seeds desatualizados causam falha nos testes de integração mesmo sem defeito real | Média | Médio | Manter scripts de seed sincronizados com migrations; executar `penomato_test` a partir do mesmo dump de schema, não de seed manual; versionar seeds em `database/seeds/` |
| **R4** | **Participantes indisponíveis para sessões de usabilidade** — agenda acadêmica da UEMS pode conflitar com o cronograma | Alta | Médio | Agendar com antecedência mínima de 3 semanas (ainda na Sem. 1); ter lista de 7–8 candidatos para substituição; como plano B, realizar com colegas de TI da UFMS (score SUS menos representativo, mas ainda válido para identificar problemas graves de navegação) |
| **R5** | **Scripts Playwright flaky em uploads de arquivo** — testes de automação de upload são intrinsecamente instáveis por dependência de diálogos nativos do SO | Média | Baixo | Usar a API `page.setInputFiles()` do Playwright (contorna diálogo nativo); definir timeout explícito de 10s para operações de I/O; anotar casos de falha intermitente para análise separada |
| **R6** | **Regressões após correções de defeitos** — a correção de um defeito de integração pode impactar um controller adjacente que compartilha a mesma transação | Média | Alto | Reexecutar suite completa do Playwright (smoke test) após cada sessão de correção; PHPUnit com cobertura mínima dos controllers críticos |
| **R7** | **ZAP reporta alto volume de falsos positivos** — alertas de baixa confiança podem consumir tempo de triagem desproporcional | Alta | Baixo | Executar ZAP em modo *Passive Scan* primeiro para mapear superfície; limitar *Active Scan* às rotas autenticadas de maior risco; documentar cada alerta descartado com justificativa técnica |
| **R8** | **Limite de código sequencial de exemplar (PN999)** — a função `gerarCodigo()` tem um cap de 999 registros (`if ($proximo > 999) $proximo = 999`) que provoca colisão de código no banco | Baixa | Crítico | Verificar explicitamente em IT-BD-04 o comportamento com 999 registros; a `UNIQUE KEY uk_codigo` lança exceção; registrar como defeito Alta se confirmado e propor expansão do formato (ex.: PN0001) |

---

## Referências

ASSOCIAÇÃO BRASILEIRA DE NORMAS TÉCNICAS. **NBR 6023**: informação e documentação: referências: elaboração. Rio de Janeiro: ABNT, 2018.

BROOKE, John. SUS: a quick and dirty usability scale. In: JORDAN, P. W. et al. (org.). **Usability Evaluation in Industry**. London: Taylor & Francis, 1996. p. 189–194.

INSTITUTO DE ENGENHEIROS ELÉTRICOS E ELETRÔNICOS. **IEEE Std 829-2008**: standard for software and system test documentation. New York: IEEE, 2008.

MYERS, Glenford J.; BADGETT, Tom; SANDLER, Corey. **The Art of Software Testing**. 3. ed. Hoboken: John Wiley & Sons, 2011.

OWASP FOUNDATION. **OWASP Top Ten 2021**. [S.l.]: OWASP Foundation, 2021. Disponível em: https://owasp.org/www-project-top-ten/. Acesso em: 16 abr. 2026.

PHPUNIT TEAM. **PHPUnit 10 Documentation**. [S.l.]: PHPUnit Team, 2023. Disponível em: https://phpunit.de/documentation.html. Acesso em: 16 abr. 2026.

PLAYWRIGHT TEAM. **Playwright for Python/Node.js**: reliable end-to-end testing for modern web apps. [S.l.]: Microsoft, 2024. Disponível em: https://playwright.dev/. Acesso em: 16 abr. 2026.

PRESSMAN, Roger S.; MAXIM, Bruce R. **Engenharia de software**: uma abordagem profissional. 8. ed. Porto Alegre: AMGH, 2016.

MAILTRAP. **Mailtrap Email Testing**: documentation. [S.l.]: Railsware, 2024. Disponível em: https://mailtrap.io/. Acesso em: 16 abr. 2026.
