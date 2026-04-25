# Fluxo do Artigo — Penomato MVP

Descreve o ciclo de vida completo de um artigo científico no sistema,
desde a inserção dos dados até a publicação.

---

## Visão geral dos status

```
rascunho → confirmado → registrado → revisando → revisado → publicado
```

| Status | Significado |
|---|---|
| `rascunho` | Artigo gerado automaticamente ao inserir dados da internet |
| `confirmado` | Dados morfológicos verificados pelo colaborador |
| `registrado` | Dados confirmados + todas as imagens/exsicatas salvas |
| `revisando` | Especialista abriu o artigo com intenção de revisar |
| `revisado` | Especialista concluiu e aprovou |
| `publicado` | Gestor publicou na plataforma |

---

## Etapa 1 — Dados da Internet → `rascunho`

**Quem:** colaborador (qualquer subtipo)
**Onde:** `src/Controllers/finalizar_upload_temporario.php`

O colaborador importa dados científicos da espécie (Flora do Brasil, Lorenzi etc.).
Ao finalizar, o sistema:
- Salva as características em `especies_caracteristicas`
- Atualiza `especies_administrativo.status = 'dados_internet'`
- Gera o HTML do artigo e insere em `artigos` com `status = 'rascunho'`
- Envia e-mail de notificação ao orientador indicado (ou a todos os especialistas se nenhum for indicado)

Nesta etapa não há especialista vinculado ao artigo.

---

## Etapa 2 — Confirmar Dados → `confirmado`

**Quem:** colaborador
**Onde:** `src/Controllers/confirmar_caracteristicas.php`

O colaborador revisa e confirma os dados morfológicos importados.
Ao salvar:
- Atualiza `especies_administrativo.status = 'descrita'`
- Atualiza `artigos SET status = 'confirmado', data_confirmado = NOW()`
  — apenas se o artigo ainda estiver em `rascunho` (não retrocede status)

---

## Etapa 3 — Registrar Imagens → `registrado`

**Quem:** colaborador
**Onde:** `src/Controllers/processar_upload_exsicata.php`

O colaborador cadastra um exemplar de campo e aponta um especialista orientador.
Se não indicar especialista, o próprio colaborador assume a orientação
e a aprovação do exemplar é automática.

A cada imagem enviada, o sistema verifica se todas as partes obrigatórias
estão completas (folha, flor, fruto, caule, semente, hábito),
consultando também a tabela `partes_dispensadas`.

Quando todas as partes estiverem completas **e** o artigo já estiver `confirmado`:
- Atualiza `artigos SET status = 'registrado', data_registrado = NOW(), revisor_id = ?`
  — o `revisor_id` vem do `especialista_id` do exemplar (ou `NULL` se sem especialista)
- Envia e-mail ao especialista atribuído, ou a todos os especialistas se `revisor_id` for nulo

Se as imagens chegarem antes da confirmação dos dados, o status fica em
`confirmado` até que o colaborador confirme — a mudança para `registrado`
ocorre na próxima vez que uma imagem for enviada e as condições forem satisfeitas.

**Alerta ao gestor:** artigos que permanecem em `confirmado` por muito tempo
(sem imagens) devem gerar um alerta — a implementar.

---

## Etapa 4 — Especialista inicia revisão → `revisando`

**Quem:** especialista (colaborador subtipo `especialista`) ou gestor
**Onde:** `src/Controllers/artigos_fila.php` → `src/Views/artigo_revisao.php`

Na fila de artigos, cada linha exibe dois botões:

| Botão | Condição | Efeito |
|---|---|---|
| **Ver** (cinza) | Sempre visível | Abre o artigo sem alterar status |
| **Revisar** (verde) | Só quando `registrado` ou `revisando` | Abre o artigo e avança para `revisando` |

Ao clicar em **Revisar**:
- Passa `?modo=revisar` na URL
- `artigo_revisao.php` executa `UPDATE artigos SET status = 'revisando', data_revisando = NOW()`
  apenas se o artigo estiver em `registrado` — se já estiver em `revisando` não altera nada

O cabeçalho da página exibe **"REVISANDO"** ou **"VISUALIZAÇÃO"** conforme o modo.
O formulário de decisão só é exibido no modo `revisar`.

---

## Etapa 5 — Especialista conclui revisão → `revisado` ou volta para `registrado`

**Quem:** especialista ou gestor
**Onde:** `src/Controllers/controlador_painel_revisor.php` (POST `acao=aprovar|contestar`)

### Aprovar
- `artigos SET status = 'revisado', data_revisado = NOW(), revisado_por = usuario_id`
- `especies_administrativo`: grava `data_revisada` e `autor_revisada_id`
- Registra no `historico_alteracoes`: `revisando → revisado`
- E-mail ao colaborador: "artigo aprovado, aguardando publicação"

### Contestar (rejeitar)
- `artigos SET status = 'registrado'` — volta para o início do ciclo de revisão
- `especies_administrativo SET status = 'contestado'`, grava motivo
- Registra no `historico_alteracoes`: `revisando → registrado`
- E-mail ao colaborador com o motivo da rejeição

O motivo é obrigatório para contestar (validado no front e no back).

---

## Etapa 6 — Gestor publica → `publicado`

**Quem:** gestor
**Onde:** `src/Controllers/artigos_fila.php` → `src/Controllers/controlador_painel_revisor.php` (POST `acao=publicar`)

Na fila, artigos com status `revisado` exibem o botão **Publicar** (azul),
visível apenas para o gestor. Um `confirm()` evita clique acidental.

Ao confirmar:
- `artigos SET status = 'publicado'`
- `especies_administrativo SET status = 'publicado', data_publicado = NOW(), autor_publicado_id = usuario_id`
- Registra no `historico_alteracoes`: `revisado → publicado`
- E-mail ao colaborador: "artigo publicado"

---

## Arquivos envolvidos

| Arquivo | Papel |
|---|---|
| `finalizar_upload_temporario.php` | Cria o artigo como `rascunho` |
| `confirmar_caracteristicas.php` | Avança para `confirmado` |
| `processar_upload_exsicata.php` | Avança para `registrado`, atribui `revisor_id`, notifica |
| `artigos_fila.php` | Fila de artigos com abas por status, botões Ver/Revisar/Publicar |
| `artigo_revisao.php` | Exibe dados + imagens; modo `revisar` avança para `revisando` |
| `controlador_painel_revisor.php` | Processa aprovar → `revisado`, contestar → `registrado`, publicar → `publicado` |

---

## Banco de dados — tabela `artigos`

Migração aplicada: `database/migracao_artigos_status_revisor.sql`

Colunas relevantes adicionadas ou alteradas:

| Coluna | Tipo | Descrição |
|---|---|---|
| `status` | ENUM (6 valores) | Status atual do artigo no fluxo |
| `revisor_id` | INT NULL | Especialista atribuído (do `exemplares.especialista_id`) |
| `revisado_por` | INT NULL | Quem de fato concluiu a revisão |
| `data_confirmado` | DATETIME | Quando dados foram confirmados |
| `data_registrado` | DATETIME | Quando imagens foram completadas |
| `data_revisando` | DATETIME | Quando especialista iniciou a revisão |
| `data_revisado` | DATETIME | Quando especialista concluiu |
| `data_publicado` | DATETIME | Quando gestor publicou |

---

## Regras de negócio

- O status nunca retrocede automaticamente — exceto quando o especialista **contesta**,
  que volta de `revisando` para `registrado`
- O colaborador pode enviar imagens antes de confirmar os dados, mas o artigo
  só avança para `registrado` quando **ambas** as condições forem satisfeitas
- Se nenhum especialista for indicado no exemplar, `revisor_id = NULL` e o artigo
  fica disponível para qualquer especialista assumir na fila
- Somente o **gestor** pode publicar (etapa 6)
- O botão **Revisar** só aparece para artigos em `registrado` ou `revisando`
- O formulário de decisão só aparece no modo `?modo=revisar`
