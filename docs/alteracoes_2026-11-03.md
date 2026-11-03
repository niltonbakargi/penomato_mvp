# Registro de Alterações — 03/11/2026

Sessão de revisão de segurança e qualidade de código do Penomato MVP.
Todas as alterações foram commitadas e publicadas no branch `main`.

---

## Resumo Executivo

| Categoria | Qtd de commits | Arquivos alterados |
|---|---|---|
| Segurança (controle de acesso) | 4 | 10 |
| Segurança (validação de entrada) | 4 | 6 |
| Segurança (tokens e criptografia) | 5 | 31 |
| Correções de bugs funcionais | 3 | 6 |
| Refatoração e limpeza | 5 | 80+ |
| Banco de dados (análise + correções) | 2 | 3 |
| Consistência de ENUMs | 1 | 2 |
| **Total** | **24** | **~140** |

---

## Commits do Dia (ordem cronológica)

### 1. `97953960` — chore: remove menção ao orientador no rodapé
**Arquivos:** `index.php`, `src/Views/includes/rodape.php`

Remoção de texto de rodapé desatualizado com menção ao orientador.

---

### 2. `7d65cbcd` — fix: corrigir busca por características que nunca exibia resultados
**Arquivo:** `src/Views/publico/busca_caracteristicas.php`

Corrigida condição de busca que impedia exibição de resultados.

---

### 3. `341c380f` — chore: remover gerar_artigo.php (deprecated)
**Arquivo:** `src/Controllers/gerar_artigo.php` (417 linhas removidas)

Remoção de controller depreciado que apenas redirecionava para `artigos_fila`. Código morto confirmado.

---

### 4. `a7cc7f77` — refactor: unificar busca por características em fluxo único
**Arquivos removidos:** `src/Controllers/busca_caracteristicas.php` (990 linhas), `src/Views/publico/busca_caracteristicas.php` (186 linhas)

Eliminação de duplicação — a busca passou a usar um único fluxo centralizado.

---

### 5. `4e6cf2b0` — refactor: substituir /penomato_mvp/ hardcoded por APP_BASE em 54 arquivos
**54 arquivos de Views e Controllers**

Substituição sistemática de caminhos relativos hardcoded por `APP_BASE` para permitir deploy em qualquer subdiretório. Inclui criação de `python_services/main.py` inicial e documentação em `docs/`.

---

### 6. `815c18ac` — fix: substituir $GLOBALS['_art_obs'] por contexto estático
**Arquivo:** `src/helpers/gerador_artigo.php`

**Problema:** A função `gerarHtmlArtigoRascunho()` dependia de `$GLOBALS['_art_obs']` para injetar observações de campo nos artigos. Variável global frágil, invisível para quem chama a função.

**Solução:** Criada função `_art_obs_ctx(array $map = null)` com estado estático interno. A função armazena o mapa de observações quando chamada com argumento, e retorna o mapa armazenado quando chamada sem argumento.

```php
// Antes
$GLOBALS['_art_obs'] = $obs_map;
gerarHtmlArtigoRascunho(...);

// Depois
_art_obs_ctx($obs_map);
gerarHtmlArtigoRascunho(...);
```

---

### 7. `6fb78056` — refactor: derivar whitelist de campos _obs a partir de enums_caracteristicas.php
**Arquivo:** `src/Controllers/confirmar_caracteristicas.php`

A whitelist do handler `salvar_obs` passou a ser derivada dinamicamente de `enums_caracteristicas.php` (fonte de verdade) em vez de uma lista hardcoded separada, eliminando risco de dessincronização.

---

### 8. `3c6efa31` — fix: corrigir caminho do require em salvar_obs (../../ → ../)
**Arquivo:** `src/Controllers/confirmar_caracteristicas.php`

**Bug crítico introduzido no commit anterior:** O path `../../Config/enums_caracteristicas.php` estava incorreto. `__DIR__` é `src/Controllers`, portanto o caminho correto é `../Config/enums_caracteristicas.php`. O caminho errado apontava para um diretório inexistente, tornando toda a whitelist vazia.

---

### 9. `2b035533` — fix: corrigir 3 vulnerabilidades de segurança
**Arquivos:** `api_sync.php`, `config/dev_local.example.php`, `src/Controllers/processar_upload_imagem.php`

**a) api_sync.php — token derivado de senha do banco**
- **Antes:** `$token = 'penomato_backup_2026_' . md5(DB_PASS)` — token previsível e dependente da senha do banco.
- **Depois:** Token lido de `BACKUP_SECRET`, constante configurada manualmente em `config/producao.php`. Se não configurada, endpoint retorna 503.

**b) processar_upload_imagem.php — display_errors e falta de whitelist**
- Removido `ini_set('display_errors', 1)` hardcoded (vaza stack traces em produção).
- Adicionada validação de `$parte_planta` contra whitelist ENUM antes de salvar no banco.

**c) config/dev_local.example.php**
- Documentada a constante `BACKUP_SECRET` com instrução de geração segura.

---

### 10. `3b51a84d` — refactor: unificar labels/whitelist e corrigir ENUMs de flor e caule
**Arquivos:** `src/Config/enums_caracteristicas.php`, `src/Config/vocabulario_botanico.php`, `src/Controllers/confirmar_caracteristicas.php`

**a) Whitelist unificada em confirmar_caracteristicas.php**
- Array `$_cc_labels` extraído para o topo do arquivo, compartilhado pelos handlers `buscar_ref_campo` e `buscar_atributo_multi`.
- `require` trocado por `require_once` em todas as 4 ocorrências de carregamento de `enums_caracteristicas.php`.

**b) Correção de ENUMs em enums_caracteristicas.php**
- `tamanho_flor`: adicionado valor `'Grande'` (ausente na definição original).
- `modificacao_caule`: removido `'Espinhos'` (conflito com `possui_espinhos`); adicionados `'Gavinha'`, `'Bulbo'`, `'Sapopema'`, `'Nenhuma'`.

**c) vocabulario_botanico.php sincronizado**
- Mapeamentos PT-BR adicionados para os novos valores de ENUM.

---

### 11. `4719034f` — fix: adicionar verificação de role em 4 controllers sem controle de acesso
**Arquivos:** `aprovacao_acoes.php`, `controlador_painel_revisor.php`, `gerenciar_especie.php`, `gestao_especies.php`

**Problema:** Todos os 4 controllers verificavam apenas `isset($_SESSION['usuario_id'])` (login), sem verificar o tipo de usuário. Qualquer colaborador autenticado podia acessar funcionalidades restritas a gestor/revisor.

| Arquivo | Antes | Depois |
|---|---|---|
| `gerenciar_especie.php` | login-only | gestor |
| `aprovacao_acoes.php` | login-only | gestor |
| `gestao_especies.php` | login-only | gestor |
| `controlador_painel_revisor.php` | login-only | revisor ou gestor |

---

### 12. `c0c489f5` — fix: corrigir open redirect no login e remover display_errors hardcoded
**Arquivos:** `login_controlador.php`, `cadastro_controlador.php`, `finalizar_upload_temporario.php`, `inserir_dados_internet.php`, `escolher_especie.php`, `sucesso_cadastro.php`, `sucesso_importacao.php`

**a) Open redirect em login_controlador.php**
- **Antes:** `header('Location: ' . $redirect)` sem validação — qualquer URL externa poderia ser injetada via `?redirect=https://evil.com`.
- **Depois:** Validação estrita — só aceita caminhos que começam com `/` e não contêm `://`.

```php
if (!empty($redirect) && str_starts_with($redirect, '/') && strpos($redirect, '://') === false) {
    header('Location: ' . $redirect);
}
```

**b) `ini_set('display_errors', 1)` removido de 6 arquivos**
- Vaza stack traces, nomes de arquivos e estrutura de diretórios em produção.

---

### 13. `bd5c9520` — fix: corrigir geração de artigo sem obs, debug logging e die() legado
**Arquivos:** `controlador_gestor.php`, `finalizar_upload_temporario.php`, `inserir_dados_internet.php`

**a) finalizar_upload_temporario.php — geração de artigo sem observações**
- **Problema:** `gerarHtmlArtigoRascunho()` era chamada sem carregar as observações de campo via `_art_obs_ctx()`, gerando artigos incompletos.
- **Solução:** Adicionada busca das observações e chamada de `_art_obs_ctx($obs_map)` antes de gerar o artigo.

**b) finalizar_upload_temporario.php — 20+ logs de debug removidos**
- Removidos logs que registravam SQLs completos, endereços de e-mail e dados internos.
- Mantidos apenas logs de erro com prefixo contextual `[Penomato]`.

**c) inserir_dados_internet.php — die() sem redirect**
- `die("Você não tem permissão...")` trocado por `header('Location: ...')` + `exit`.

**d) controlador_gestor.php — estatísticas hardcoded**
- Fallback com valores fictícios (`12`, `3`, `5`, `8`) substituído por zeros com `error_log` em caso de falha real.

---

### 14. `de515b75` — fix: itens baixos — require_once, auth FastAPI, binários no git
**Arquivos:** `.gitignore`, `python_services/main.py`, `confirmar_caracteristicas.php`; remoção de binários

**a) python_services/main.py — autenticação dos endpoints**
- **Problema:** Endpoints `/embed` e `/search` estavam completamente abertos; qualquer requisição HTTP podia gravar ou consultar vetores DINOv2.
- **Solução:** Adicionada autenticação via `X-API-Key` com `secrets.compare_digest()`. A variável de ambiente `PENOMATO_API_KEY` é obrigatória na inicialização. Endpoints `/health` e `/stats` permanecem públicos.

```python
_API_KEY = os.environ.get("PENOMATO_API_KEY", "")
if not _API_KEY:
    raise RuntimeError("PENOMATO_API_KEY não definida.")
```

**b) confirmar_caracteristicas.php — require → require_once**
- Substituído `require` por `require_once` para carregar `enums_caracteristicas.php` (evita re-execução acidental).

**c) .gitignore — binários grandes adicionados**
- Adicionadas extensões `*.xlsx`, `*.xls`, `*.csv` para evitar rastrear arquivos grandes.
- Removidos do tracking: `angiospermsdatabase.xlsx` (24 MB), `gymnospermsdatabase.xlsx`, `mapeamento_estrutura.csv`.

---

### 15. `b443be82` — fix: restringir acesso a páginas administrativas por role
**Arquivos:** `relatorio_colaboradores.php`, `monitoramento.php`, `processar_revisao_exemplar.php`, `backup_painel.php`

**a) relatorio_colaboradores.php e monitoramento.php — login-only → gestor**
- Ambos expunham dados sensíveis (emails, último acesso, histórico de atividades) para qualquer colaborador autenticado.
- Adicionada verificação `$_SESSION['usuario_tipo'] !== 'gestor'` com resposta 403.

**b) processar_revisao_exemplar.php — login-only → revisor/gestor**
- Endpoint POST de revisão de exemplar passou a exigir role revisor ou gestor.

**c) backup_painel.php — token desatualizado**
- Token exibido na interface foi corrigido: antes calculava `md5(DB_PASS)` (padrão antigo), agora lê `BACKUP_SECRET` (alinhado com `api_sync.php`).

---

### 16. `3f54254b` — fix: blindar limpar_importacao contra destruição de dados alheios
**Arquivos:** `limpar_importacao_especie.php`, `processar_upload_temporario.php`

**a) limpar_importacao_especie.php — path traversal de dados**
- **Problema grave:** `especie_id` era lido diretamente de `$_POST`. Qualquer colaborador autenticado podia enviar um POST com `especie_id` arbitrário e apagar **todas** as imagens em disco, características, artigos rascunho e resetar o status da espécie para `sem_dados`.
- **Solução:** `especie_id` agora é lido **exclusivamente** da sessão `importacao_temporaria`, com verificação de que `usuario_id` da sessão bate com o usuário logado. Se a sessão não estiver presente ou pertencer a outro usuário, redireciona imediatamente.

```php
// Antes (vulnerável)
$especie_id = (int)($_POST['especie_id'] ?? 0);

// Depois (seguro)
if (!isset($_SESSION['importacao_temporaria']['especie_id']) ||
    (int)($_SESSION['importacao_temporaria']['usuario_id'] ?? 0) !== (int)$_SESSION['usuario_id']
) {
    $_SESSION['erro_importacao'] = 'Sessão de importação inválida.';
    header('Location: ../Views/escolher_especie.php');
    exit;
}
$especie_id = (int)$_SESSION['importacao_temporaria']['especie_id'];
```

**b) processar_upload_temporario.php — error_reporting hardcoded**
- Removido `error_reporting(E_ALL)` que podia vazar informações em produção.

---

### 17. `ef5d4355` — fix: validar parte_planta e habilitar SSL em chamadas DeepSeek
**Arquivos:** `processar_adicionar_imagem_internet.php`, `buscar_dados_especie_ai.php`, `confirmar_caracteristicas.php`

**a) processar_adicionar_imagem_internet.php — path traversal via parte_planta**
- **Problema:** `$parte_planta` era aceito do POST sem validação e usado diretamente no nome do arquivo:
  ```php
  $nome_arquivo = $especie_id . '_' . $parte_planta . '_' . date('Ymd_His') . '...' . $ext;
  $caminho_abs  = $pasta_especie . $nome_arquivo;
  move_uploaded_file($tmp, $caminho_abs);
  ```
  Um valor como `../../config/banco_de_dados` podia escrever fora do diretório de uploads.
- **Solução:** Adicionada whitelist ENUM antes de qualquer uso do valor:
  ```php
  $partes_validas = ['folha','flor','fruto','caule','semente','habito','exsicata_completa','detalhe'];
  if (!in_array($parte_planta, $partes_validas, true)) { ... exit; }
  ```

**b) SSL desabilitado em chamadas DeepSeek (4 ocorrências)**
- `CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false` estava hardcoded em todos os blocos DeepSeek de `buscar_dados_especie_ai.php` (1x) e `confirmar_caracteristicas.php` (3x).
- Os outros providers (Claude, OpenAI, Gemini) verificavam SSL corretamente. `buscar_imagens_automatico.php` já usava o padrão correto (falso só em dev).
- Todos os 4 blocos corrigidos para `CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2`.

---

### 18. `b3d51b2` — fix: comparação constant-time em tokens e remover GET token da API
**Arquivos:** `api_sync.php`, `alterar_senha_controlador.php`, `atualizar_perfil_controlador.php`

**a) api_sync.php — timing attack e token em query string**
- Comparação `$token !== BACKUP_TOKEN` substituída por `!hash_equals(BACKUP_TOKEN, $token)` — previne enumeração do token via diferença de tempo de resposta.
- Removido fallback `$_GET['token']` que expunha o `BACKUP_SECRET` em logs de acesso do servidor, HTTP Referer e histórico do navegador.

**b) alterar_senha_controlador.php e atualizar_perfil_controlador.php**
- Comparações de token CSRF com `!==` substituídas por `hash_equals()` para consistência com o restante do sistema.

---

---

### 19. `0977307` — docs: análise completa do banco de produção e script de correções
**Arquivos:** `docs/analise_banco_producao_2026-11-03.md`, `database/correcoes_producao_2026-11-03.sql`

Análise completa do dump de produção `b62a4147_penomato (10).sql` (MySQL 8.0.46, phpMyAdmin 5.2.3).
Banco restaurado localmente via `mysql.exe` e correções aplicadas localmente e em produção via phpMyAdmin.

**Problemas encontrados e corrigidos:**

| # | Severidade | Problema | Correção |
|---|---|---|---|
| 1 | ALTO | 11 FKs → `usuarios(id)` com `ON DELETE RESTRICT` implícito | `ON DELETE SET NULL` em todas |
| 2 | MÉDIO | `fila_aprovacao` sem índice em `status` | `ADD INDEX idx_status` |
| 3 | MÉDIO | `especies_administrativo`: 6 colunas `autor_*_id` sem índice | 6 índices adicionados |
| 4 | MÉDIO | `tentativas_login`: índices separados em `ip` e `criado_em` | Substituído por índice composto `(ip, criado_em)` |
| 5 | MÉDIO | `flora_brasil_plantas` sem índice em `nome_cientifico` | `ADD INDEX idx_nome_cientifico` |
| 6 | MÉDIO | Colações mistas (`general_ci` vs `unicode_ci`) | Documentado — requer janela de manutenção |
| 7 | BAIXO | ENUM `'validador'` em `usuarios.categoria` sem handler no código | Removido do ENUM |
| 8 | BAIXO | Colunas NOT NULL incompatíveis com `ON DELETE SET NULL` | `MODIFY ... DEFAULT NULL` em 9 colunas |

Também identificado: divergência de SGBD (dev MariaDB 10.4 vs prod MySQL 8.0).

---

### 20. `a049b72` — fix: corrigir ENUMs de especies_caracteristicas e busca por características
**Arquivos:** `src/Views/publico/busca_caracteristicas.php`, `database/correcoes_producao_2026-11-03.sql`

Auditoria de consistência entre DB, `enums_caracteristicas.php` e `busca_caracteristicas.php`.

**Divergências encontradas (3 camadas):**

**Camada 1 — `busca_caracteristicas.php` com valores hardcoded errados (buscas sempre retornavam zero):**
| Campo | Problema | Correção |
|---|---|---|
| `cor_flores` | 8 valores pluralizados e errados (`Brancas`, `Laranjas`...) | 12 valores corretos do ENUM |
| `disposicao_flores` | `Isoladas` / `Inflorescência` (inventados) | 8 valores corretos: `Solitária`, `Capítulo`, `Cacho`... |
| `aroma` | `Sem cheiro`, `Aroma suave`... | `Ausente`, `Suave`, `Forte`, `Desagradável`, `Adocicada`, `Cítrica` |
| `aroma_fruto` | Mesmo padrão errado | Corrigido com gênero adequado (`Adocicado`, `Cítrico`) |
| `numero_petalas` | Faltavam `6 pétalas` e `Ausentes` | Adicionados |
| `modificacao_caule` | `Espinhos` (valor antigo) | `Gavinha`, `Bulbo`, `Sapopema`, `Nenhuma` |

**Camada 2 — banco desatualizado em relação ao PHP:**
- `modificacao_caule`: DB tinha `Espinhos`, PHP queria `Gavinha/Bulbo/Sapopema/Nenhuma` — 5 registros com `Espinhos` zerados, ENUM atualizado
- `tamanho_flor`: DB não tinha `Grande`, PHP tinha — ENUM atualizado

---

### 22. `82ca96d` — fix: proteção CSRF em 4 formulários, exposição de exceções e headers HTTP

**Arquivos:** `config/app.php`, `src/Controllers/confirmar_caracteristicas.php`, `src/Views/cadastro_caracteristicas.php`, `src/Views/escolher_especie.php`, `src/Views/matrizes/gerenciar_matrizes.php`, `src/Views/sugestoes.php`

**a) CSRF adicionado em 4 formulários POST sem proteção**

| Arquivo | Token de sessão | Observação |
|---|---|---|
| `sugestoes.php` | `csrf_sugestao` | Formulário de envio de sugestão |
| `gerenciar_matrizes.php` | `csrf_remover_matriz` | Formulário de remoção de matriz (soft delete) |
| `cadastro_caracteristicas.php` | `csrf_cadastro_caract` | Controlador sem form próprio — proteção server-side |
| `escolher_especie.php` | `csrf_escolher_especie` | Formulário modal de seleção de espécie/orientador |

Padrão aplicado em todos: geração com `bin2hex(random_bytes(32))`, validação com `hash_equals()`, rotação após uso bem-sucedido, `htmlspecialchars(..., ENT_QUOTES)` no hidden input.

**b) `confirmar_caracteristicas.php` — mensagens de exceção expostas ao cliente**
- `catch (Exception $e) { echo json_encode(['erro' => $e->getMessage()]); }` nas ações `salvar_campo` e `salvar_obs` revelavam detalhes do banco ao navegador
- Corrigido: mensagem de erro genérica para o cliente + `error_log` preserva o detalhe para diagnóstico do servidor

**c) `config/app.php` — headers HTTP de segurança globais**
- Adicionados em todas as respostas (antes do output):
  - `X-Frame-Options: SAMEORIGIN` — previne clickjacking
  - `X-Content-Type-Options: nosniff` — previne MIME sniffing
  - `Referrer-Policy: strict-origin-when-cross-origin` — limita dados no Referer

---

### 24. `031ac08` — security: CSRF em controlador_painel_revisor e 4 views dependentes

**Arquivos:** `controlador_painel_revisor.php`, `artigo_revisao.php`, `artigos_fila.php`, `gestao_artigos_gestor.php`, `entrada_revisor.php`

Token de sessão: `csrf_painel_revisor`

| Ação | Origem | Tipo |
|---|---|---|
| `aprovar` / `contestar` | `artigo_revisao.php` | form HTML |
| `publicar` | `artigos_fila.php` | form HTML |
| `publicar` | `gestao_artigos_gestor.php` | form HTML |
| `iniciar` | `entrada_revisor.php` | fetch AJAX |

Para o AJAX: token gerado no PHP, exposto como `const CSRF_TOKEN` em JS (dentro do `<script>`), enviado no body do fetch. O controlador valida e rotaciona o token após uso.

---

### 23. `193df32` — security: CSRF em 9 controllers MEDIUM + correções XSS/output encoding

**Arquivos:** 17 arquivos (ver Mapa abaixo)

**a) CSRF adicionado em 9 grupos de arquivos (prioridade MEDIUM)**

| Arquivo(s) | Token de sessão | Formulários protegidos |
|---|---|---|
| `gestao_especies.php` | `csrf_gestao_especies` | 2 forms inline (prioridade / atribuir colaborador) |
| `gestao_artigos_gestor.php` | `csrf_gestao_artigos` | Modal de atribuição de orientador |
| `contestar_informacao.php` | `csrf_contestar` | Form de nova contestação |
| `minhas_acoes.php` + `desfazer_acao.php` | `csrf_desfazer` | Desfazer ação + solicitar ao gestor |
| `ficha.php` + `processar_comentario.php` | `csrf_comentario_matriz` | Comentário em ficha de matriz |
| `adicionar_foto.php` + `processar_foto_parte.php` | `csrf_foto_parte` | Upload de foto de parte da planta |
| `cadastrar_exemplar.php` + `processar_cadastro_exemplar.php` | `csrf_cadastro_exemplar` | Cadastro de exemplar |
| `enviar_imagem.php` + `processar_upload_exsicata.php` | `csrf_upload_exsicata` | Upload de exsicata/foto de campo |

Padrão aplicado: geração `bin2hex(random_bytes(32))` na view, validação `hash_equals()` no controller, rotação pós-uso, `htmlspecialchars(..., ENT_QUOTES)` no hidden input.

**b) XSS — `artigo_revisao.php` (campos `_ref`)**
- Linhas 969–983: `$carac['possui_espinhos_ref']`, `$carac['possui_latex_ref']`, `$carac['possui_espinhos']`, `$carac['possui_latex']` foram escapados com `htmlspecialchars()` (incluindo o atributo `data-ref` com `ENT_QUOTES`).

**c) Output encoding — 3 pontos adicionais (LOW)**
- `alterar_senha.php:337` — `$acesso['ip']` agora escapado com `htmlspecialchars()`
- `cabecalho.php:591` — breadcrumb `$item['url']` e `$item['nome']` agora escapados
- `solicitar_alteracao_email.php:147` — token CSRF existente sem `ENT_QUOTES`; corrigido

---

### 21. `975cd6f` + `0832e46` — fix: SSRF, controlador legado e GPS
**Arquivos:** `src/Controllers/salvar_atribuicoes.php`, `src/Controllers/upload_imagem_controlador.php`, `src/Controllers/matrizes/processar_registro.php`

**a) SSRF em `salvar_atribuicoes.php`**
- `$url_foto` vinha do POST JSON sem nenhuma validação de URL — servidor podia ser usado para buscar recursos internos
- Adicionada validação completa:
  - `filter_var(FILTER_VALIDATE_URL)` para validar formato
  - Whitelist de esquemas (`http`, `https`)
  - Bloqueio de IPs internos: `localhost`, `127.x`, `10.x`, `192.168.x`, `172.16-31.x`, `169.254.x`, `::1`

```php
$esquema = strtolower(parse_url($url_foto, PHP_URL_SCHEME) ?? '');
if (!in_array($esquema, ['http', 'https'], true)) { ... continue; }
$host = strtolower(parse_url($url_foto, PHP_URL_HOST) ?? '');
if (preg_match('/^127\.|^10\.|^192\.168\.|.../', $host)) { ... continue; }
```

**b) `upload_imagem_controlador.php` — controlador legado desativado**
- Arquivo marcado `⚠️ LEGADO - NÃO USADO NO NOVO FLUXO` com zero referências no código
- Continha 15+ `error_log` vazando `print_r($_POST)` e `print_r($_FILES)` nos logs do servidor
- Substituído por `http_response_code(410); exit;`

**c) `processar_registro.php` — GPS sem validação numérica**
- `$lat` e `$lon` eram apenas verificados como não-vazios
- Corrigido com `floatval()` e range check `-90/90` e `-180/180`

```php
// Antes
$lat = $_POST['latitude'] ?? '';
if (empty($lat)) { ... }

// Depois
$lat = floatval($_POST['latitude'] ?? '');
if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) { ... }
```

---

## Mapa de Arquivos Alterados

### Controllers
| Arquivo | Tipo de alteração |
|---|---|
| `confirmar_caracteristicas.php` | refactor + security (whitelist, require_once, SSL, exceções) |
| `processar_upload_imagem.php` | security (whitelist parte_planta, display_errors) |
| `processar_adicionar_imagem_internet.php` | security (whitelist parte_planta) |
| `processar_upload_temporario.php` | security (error_reporting) |
| `limpar_importacao_especie.php` | security (especie_id via sessão apenas) |
| `finalizar_upload_temporario.php` | bugfix (obs artigo, debug logs, display_errors) |
| `inserir_dados_internet.php` | bugfix (die → redirect, display_errors) |
| `gerenciar_especie.php` | security (role gestor + CSRF csrf_gerenciar_especie) |
| `aprovacao_acoes.php` | security (role gestor + CSRF csrf_aprovacao_acoes) |
| `gestao_especies.php` | security (role gestor + CSRF csrf_gestao_especies) |
| `gestao_artigos_gestor.php` | security (CSRF csrf_gestao_artigos) |
| `contestar_informacao.php` | security (CSRF csrf_contestar) |
| `desfazer_acao.php` | security (CSRF csrf_desfazer) |
| `minhas_acoes.php` | security (CSRF csrf_desfazer) |
| `matrizes/processar_comentario.php` | security (CSRF csrf_comentario_matriz) |
| `matrizes/processar_foto_parte.php` | security (CSRF csrf_foto_parte) |
| `processar_cadastro_exemplar.php` | security (CSRF csrf_cadastro_exemplar) |
| `processar_upload_exsicata.php` | security (CSRF csrf_upload_exsicata) |
| `processar_revisao_exemplar.php` | security (role revisor/gestor + CSRF csrf_revisao_exemplar) |
| `gerenciar_membros.php` | security (CSRF csrf_gerenciar_membros) |
| `controlador_painel_revisor.php` | security (role revisor/gestor + CSRF csrf_painel_revisor) |
| `relatorio_colaboradores.php` | security (role gestor) |
| `monitoramento.php` | security (role gestor) |
| `backup_painel.php` | security (token BACKUP_SECRET) |
| `controlador_gestor.php` | bugfix (stats hardcoded → zeros) |
| `buscar_dados_especie_ai.php` | security (SSL DeepSeek) |
| `artigos_fila.php` | refactor (APP_BASE) + security (CSRF csrf_painel_revisor) |
| `busca_caracteristicas.php` | removido (deprecado) |
| `gerar_artigo.php` | removido (deprecado) |
| `salvar_atribuicoes.php` | security (SSRF — validação de URL) |
| `upload_imagem_controlador.php` | security (desativado — 410 Gone) |
| `matrizes/processar_registro.php` | security (validação GPS numérica) |

### Auth Controllers
| Arquivo | Tipo de alteração |
|---|---|
| `auth/login_controlador.php` | security (open redirect) |
| `auth/cadastro_controlador.php` | security (display_errors) |
| `usuario/alterar_senha_controlador.php` | security (hash_equals CSRF) |
| `usuario/atualizar_perfil_controlador.php` | security (hash_equals CSRF) |

### Helpers
| Arquivo | Tipo de alteração |
|---|---|
| `helpers/gerador_artigo.php` | bugfix ($GLOBALS → contexto estático) |

### Config
| Arquivo | Tipo de alteração |
|---|---|
| `src/Config/enums_caracteristicas.php` | bugfix (ENUMs tamanho_flor, modificacao_caule) |
| `src/Config/vocabulario_botanico.php` | sync (novos valores ENUM → PT-BR) |
| `config/dev_local.example.php` | docs (BACKUP_SECRET) |
| `config/app.php` | security (X-Frame-Options, X-Content-Type-Options, Referrer-Policy) |

### Raiz / Infra
| Arquivo | Tipo de alteração |
|---|---|
| `api_sync.php` | security (BACKUP_SECRET, hash_equals, sem GET token) |
| `python_services/main.py` | security (X-API-Key em /embed e /search) |
| `.gitignore` | chore (xlsx, xls, csv) |
| `angiospermsdatabase.xlsx` | removido do tracking |
| `gymnospermsdatabase.xlsx` | removido do tracking |
| `mapeamento_estrutura.csv` | removido do tracking |

### Banco de Dados
| Arquivo | Tipo de alteração |
|---|---|
| `docs/analise_banco_producao_2026-11-03.md` | docs (relatório completo análise prod) |
| `database/correcoes_producao_2026-11-03.sql` | infra (script SQL para phpMyAdmin prod) |

### Views
| Arquivo | Tipo de alteração |
|---|---|
| `src/Views/publico/busca_caracteristicas.php` | bugfix (6 selects com ENUMs errados corrigidos) |
| `src/Views/sugestoes.php` | security (CSRF csrf_sugestao) |
| `src/Views/escolher_especie.php` | security (CSRF csrf_escolher_especie) |
| `src/Views/cadastro_caracteristicas.php` | security (CSRF csrf_cadastro_caract) |
| `src/Views/matrizes/gerenciar_matrizes.php` | security (CSRF csrf_remover_matriz) |
| `src/Views/matrizes/ficha.php` | security (CSRF csrf_comentario_matriz) |
| `src/Views/matrizes/adicionar_foto.php` | security (CSRF csrf_foto_parte) |
| `src/Views/cadastrar_exemplar.php` | security (CSRF csrf_cadastro_exemplar) |
| `src/Views/enviar_imagem.php` | security (CSRF csrf_upload_exsicata) |
| `src/Views/revisor/revisar_exemplar.php` | security (CSRF csrf_revisao_exemplar) |
| `src/Views/entrada_revisor.php` | security (CSRF csrf_painel_revisor via JS) |
| `src/Views/artigo_revisao.php` | security (XSS — escapar campos _ref + CSRF csrf_painel_revisor) |
| `src/Views/auth/solicitar_alteracao_email.php` | security (ENT_QUOTES no token CSRF) |
| `src/Views/includes/cabecalho.php` | security (XSS — escapar breadcrumb url/nome) |
| `src/Views/usuario/alterar_senha.php` | security (XSS — escapar IP de acesso) |

---

## Pendências Documentadas

Os itens abaixo foram identificados durante a sessão mas não corrigidos (por serem de design intencional ou exigirem decisão do time):

1. **`confirmar_caracteristicas.php` — ownership de edição** — qualquer colaborador autenticado pode editar características de qualquer espécie sem cheque de ownership. Comportamento colaborativo intencional, mas sem rastreio de quem editou o quê por espécie atribuída.

2. **CSRF em requisições AJAX** — As ações AJAX de `confirmar_caracteristicas.php` (`salvar_campo`, `salvar_obs`, `salvar_referencias`, `buscar_ref_campo`, `buscar_atributo_multi`) não verificam CSRF. O endpoint `iniciar` de `controlador_painel_revisor.php` foi corrigido (commit 24). Mitigação parcial via `SameSite=Lax`. CSRF completo em `confirmar_caracteristicas.php` exige meta-tag com token ou header `X-CSRF-Token` no frontend.

4. **CSP (Content-Security-Policy)** — não adicionado. Requer levantamento de todos os CDNs, scripts inline e estilos usados no projeto antes de implementar sem quebrar páginas.

5. **Colações mistas no banco** — `general_ci` vs `unicode_ci` em tabelas diferentes. Documentado em `analise_banco_producao_2026-11-03.md`; requer janela de manutenção para migração.

## Ações Necessárias no Servidor

Estas configurações precisam ser feitas manualmente — não estão no código:

1. **`BACKUP_SECRET`** — Adicionar em `config/producao.php` e `config/dev_local.php`:
   ```php
   define('BACKUP_SECRET', 'cole_aqui_valor_gerado_com_bin2hex_random_bytes_24');
   // Gerar: php -r "echo bin2hex(random_bytes(24));"
   ```

2. **`PENOMATO_API_KEY`** — Definir antes de iniciar o serviço Python:
   ```bash
   export PENOMATO_API_KEY=$(python -c "import secrets; print(secrets.token_hex(32))")
   python_services/venv/Scripts/uvicorn python_services.main:app --port 8000 --reload
   ```
