# Segurança Implementada — Penomato MVP

**Projeto de Extensão:** Programa de Extensão UFMS Digital (95DX7.200525)
**Projeto de Software:** Penomato — Plataforma de Catalogação de Espécies Nativas do Cerrado
**Referência:** Plano de Desenvolvimento Seguro — Módulo 3 (Boas Práticas em Serviços Web)

---

## Visão Geral

Este documento registra as práticas de segurança efetivamente implementadas no código do Penomato MVP, com referência às práticas descritas no Plano de Desenvolvimento Seguro. Para cada item: o que foi feito, quais arquivos foram alterados e por que a mudança é relevante para a segurança da aplicação.

---

## 1. Cabeçalhos HTTP de Segurança (Prática 9)

**Arquivo:** `.htaccess`

### O que foi implementado

Seis cabeçalhos HTTP de segurança adicionados globalmente via `mod_headers`, aplicados a todas as respostas do servidor.

| Cabeçalho | Valor | Proteção |
|---|---|---|
| `X-Frame-Options` | `DENY` | Impede que qualquer página do Penomato seja carregada dentro de um `<iframe>` em outro site (proteção contra clickjacking) |
| `X-Content-Type-Options` | `nosniff` | Impede que o navegador interprete um arquivo `.jpg` como JavaScript — especialmente relevante no módulo de upload de imagens |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Ao clicar em links externos (referências bibliográficas, por exemplo), o navegador envia apenas o domínio de origem, não a URL completa com parâmetros |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains` | Força HTTPS por 1 ano. Ativo apenas quando a conexão já é HTTPS (`env=HTTPS`), portanto não interfere no ambiente de desenvolvimento local |
| `Permissions-Policy` | geolocalização e câmera liberadas para `self`; demais bloqueados | Restringe o acesso a APIs sensíveis do navegador. Geolocalização e câmera são usadas no cadastro de exemplares; microfone, pagamento, USB e sensores de orientação não têm uso no sistema |
| `Content-Security-Policy` | diretivas por tipo de recurso | Controla de quais domínios o navegador pode carregar scripts, estilos, fontes e imagens |

### Content Security Policy — detalhamento

O CSP foi construído após mapeamento de todos os recursos externos reais do projeto:

```
script-src  → cdn.jsdelivr.net (Bootstrap, Chart.js, exifr)
              unpkg.com (Leaflet)
              code.jquery.com
              cdnjs.cloudflare.com

style-src   → cdn.jsdelivr.net, cdnjs.cloudflare.com
              fonts.googleapis.com, unpkg.com

font-src    → fonts.googleapis.com, fonts.gstatic.com
              cdnjs.cloudflare.com (Font Awesome)

img-src     → tile.openstreetmap.org (tiles do mapa Leaflet)
              data: (imagens base64 do perfil do usuário)

connect-src → apenas 'self' (sem requisições externas via fetch/XHR)
frame-ancestors → 'none' (nenhum iframe no projeto)
form-action → 'self' (formulários só podem ser enviados para o próprio domínio)
object-src  → 'none' (bloqueia Flash e plugins legados)
base-uri    → 'self' (impede injeção de <base href> por atacante)
```

**Limitação conhecida:** `unsafe-inline` é necessário em `script-src` e `style-src` porque o projeto utiliza dezenas de blocos `<script>` e `<style>` inline nas views. Remover `unsafe-inline` exige mover todo esse código para arquivos externos — identificado como trabalho futuro.

### Proteção adicional de arquivos sensíveis

Além dos cabeçalhos, foram adicionadas ao `.htaccess`:

- Bloqueio de acesso direto a arquivos `.sql`, `.log`, `.env`, `.json`, `.md`, `.gitignore`, `.lock` (retorna 403)
- Bloqueio de acesso direto à pasta `config/` via URL

---

## 2. Redirecionamento HTTP → HTTPS (Prática 14)

**Arquivo:** `.htaccess`

### O que foi implementado

Redirecionamento permanente (301) de HTTP para HTTPS, com duas regras:

**Regra 1 — Canonicalização de domínio:**
`www.penomato.app.br` redireciona para `penomato.app.br`. Sem isso, os dois endereços são domínios distintos para o navegador, gerando cookies de sessão separados e conteúdo duplicado.

**Regra 2 — Forçar HTTPS com suporte a proxy reverso:**

```apache
RewriteCond %{HTTP_HOST} ^penomato\.app\.br$ [NC]
RewriteCond %{HTTPS} off
RewriteCond %{HTTP:X-Forwarded-Proto} !https
RewriteRule ^ https://penomato.app.br%{REQUEST_URI} [R=301,L]
```

A terceira condição (`X-Forwarded-Proto`) é necessária porque a Hostgator utiliza um proxy reverso que termina o SSL e repassa a requisição ao Apache como HTTP puro. Sem essa condição, `%{HTTPS}` seria sempre `off` mesmo em conexões seguras, causando loop infinito de redirecionamento.

**Ambiente de desenvolvimento:** ambas as regras verificam o host `penomato.app.br` antes de agir. Em `localhost` nenhuma regra dispara.

---

## 3. Gerenciamento Seguro de Sessões (Prática 7)

**Arquivos:**
- `src/Controllers/auth/verificar_acesso.php`
- `src/Controllers/auth/login_controlador.php`

### O que foi implementado

**3.1 Configuração segura do cookie de sessão**

Substituição do `session_start()` direto por inicialização com `session_set_cookie_params()` em ambos os arquivos:

```php
$cookie_seguro = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

ini_set('session.use_strict_mode', '1');

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => $cookie_seguro,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();
```

| Parâmetro | Efeito |
|---|---|
| `httponly => true` | Cookie inacessível via `document.cookie` — bloqueia roubo de sessão por XSS |
| `secure => $cookie_seguro` | Cookie só trafega em HTTPS. Em dev (HTTP/XAMPP) fica `false` automaticamente |
| `samesite => 'Lax'` | Cookie não é enviado em requisições cross-site iniciadas por terceiros — camada extra de proteção CSRF |
| `lifetime => 0` | Cookie expira ao fechar o navegador — sessão não persiste em computadores compartilhados |
| `use_strict_mode = 1` | Servidor rejeita IDs de sessão não gerados por ele mesmo — bloqueia session fixation por URL |

**3.2 Regeneração de ID após login**

Adicionado `session_regenerate_id(true)` no `login_controlador.php`, posicionado após limpar as tentativas de login e imediatamente antes de popular `$_SESSION`:

```php
// Login bem-sucedido: limpar tentativas falhas deste IP
executarQuery("DELETE FROM tentativas_login WHERE ip = :ip", [':ip' => $ip]);

// Regenerar ID de sessão para evitar session fixation
session_regenerate_id(true);

// Criar sessão
$_SESSION['usuario_id'] = $usuario['id'];
// ...
```

O parâmetro `true` apaga o arquivo de sessão antigo no servidor. Sem ele, o ID anterior permanece válido por um intervalo de tempo, deixando uma janela para session fixation.

---

## 4. Permissões de Diretório de Upload (Prática 8)

**Arquivos corrigidos:**
- `src/Controllers/processar_cadastro_exemplar.php`
- `src/Controllers/processar_upload_exsicata.php`
- `src/Controllers/processar_upload_imagem.php`
- `src/Controllers/upload_imagem_controlador.php`

### O que foi implementado

Substituição de `0777` por `0755` em todas as chamadas `mkdir()` que criam diretórios de upload.

```php
// Antes
mkdir($pasta, 0777, true);

// Depois
mkdir($pasta, 0755, true);
```

### Por que 0777 é perigoso

Em servidor Linux compartilhado (Hostgator), `0777` significa que qualquer processo do servidor — não só o PHP do Penomato — pode escrever na pasta. Se outro site hospedado no mesmo servidor for comprometido, o atacante pode depositar um web shell PHP dentro da pasta de uploads do Penomato e executá-lo via URL.

Com `0755`, apenas o usuário dono do processo PHP pode escrever. Outros processos podem listar e ler, mas não criar arquivos.

**Arquivos já corretos (não alterados):**
- `config/email.php` — pasta `logs/` já usava `0755`
- `src/Controllers/processar_upload_temporario.php` — já usava `0755`
- `src/Controllers/salvar_atribuicoes.php` — já usava `0755`

---

## 5. Privilégio Mínimo no Banco de Dados (Prática 15)

**Arquivo:** `database/grants_producao.sql`

### O que foi implementado

Criação de script SQL com os `GRANT` exatos para o usuário de aplicação em produção, seguindo o princípio do menor privilégio. O ambiente de desenvolvimento usa `root` sem senha (XAMPP local); em produção o usuário `penomato_app` deve ter apenas os privilégios listados abaixo.

### Tabela de privilégios por tabela

| Tabela | SELECT | INSERT | UPDATE | DELETE | Motivo |
|--------|--------|--------|--------|--------|--------|
| `usuarios` | ✓ | ✓ | ✓ | ✓ | CRUD completo (cadastro, exclusão de membros) |
| `especies_administrativo` | ✓ | ✓ | ✓ | ✓ | Gestor pode excluir espécies |
| `especies_caracteristicas` | ✓ | ✓ | ✓ | ✓ | Edição de características |
| `especies_imagens` | ✓ | ✓ | ✓ | ✓ | Upload e exclusão de imagens |
| `exemplares` | ✓ | ✓ | ✓ | ✓ | Cadastro e revisão de exemplares |
| `fila_aprovacao` | ✓ | ✓ | ✓ | ✓ | Fluxo de aprovação de ações |
| `partes_dispensadas` | ✓ | ✓ | ✓ | ✓ | Controle de partes opcionais |
| `sugestoes_usuario` | ✓ | ✓ | ✓ | ✓ | Sugestões dos colaboradores |
| `temp_imagens_candidatas` | ✓ | ✓ | ✓ | ✓ | Imagens temporárias da IA |
| `tentativas_login` | ✓ | ✓ | ✓ | ✓ | Rate limiting de login |
| `tokens_*` (3 tabelas) | ✓ | ✓ | ✓ | ✓ | Tokens de e-mail, senha, alteração |
| `artigos` | ✓ | ✓ | ✓ | ✓ | CRUD de artigos científicos |
| `flora_brasil_plantas` | ✓ | — | — | — | Dados de referência (somente leitura) |
| `flora_brasil_sinonimos` | ✓ | — | — | — | Dados de referência (somente leitura) |
| **`historico_alteracoes`** | ✓ | ✓ | **—** | **—** | **Trilha de auditoria imutável** |

### Decisão de design: `historico_alteracoes` imutável

`UPDATE` e `DELETE` são intencionalmente negados na tabela de auditoria. A aplicação apenas registra novos eventos (`INSERT`) e os consulta (`SELECT`). Isso garante que nenhuma sequência de comandos PHP — seja por bug ou por exploração de injeção SQL — consiga apagar ou alterar evidências de ações passadas.

**Sobre `ON DELETE CASCADE`:** quando uma espécie é excluída, o MySQL apaga automaticamente seus registros em `historico_alteracoes` via FK cascade. Esse processo é interno ao storage engine e não exige que o usuário da aplicação tenha `DELETE` na tabela filha.

### Privilégios explicitamente negados

O usuário `penomato_app` **não tem** e **não deve ter**:

- `CREATE`, `DROP`, `ALTER`, `TRUNCATE` — nenhuma DDL
- `GRANT OPTION` — não pode delegar permissões
- `FILE`, `SUPER`, `PROCESS`, `RELOAD` — nenhum privilégio administrativo
- Acesso a qualquer banco além de `penomato`

### Instruções de aplicação em produção

```sql
-- Verificar grants aplicados (executar como root):
SHOW GRANTS FOR 'penomato_app'@'localhost';
```

Em hospedagem compartilhada (Hostinger/Hostgator), o nome do usuário e do banco terão prefixo do painel (ex: `u123456789_app`, `u123456789_penomato`). Substituir os nomes no arquivo `database/grants_producao.sql` antes de executar.

---

## Status das Práticas do Plano

| # | Prática | Status |
|---|---|---|
| P1 | Prepared Statements (SQL Injection) | Já implementado desde o início |
| P2 | Validação de entrada | Parcial — ENUMs e limites implementados nos controllers principais |
| P3 | Proteção XSS / htmlspecialchars | Implementado — cobertura completa nas views |
| P4 | Tokens CSRF | Parcial — pendente cobertura completa |
| P5 | RBAC no servidor | Já implementado desde o início |
| P6 | Armazenamento seguro de senhas | Já implementado desde o início |
| **P7** | **Gerenciamento seguro de sessões** | **Implementado** |
| P8 | Validação de upload de arquivos | Parcial — MIME validado; permissões corrigidas |
| **P9** | **Cabeçalhos HTTP de segurança** | **Implementado** |
| P10 | Rate limiting no login | Já implementado desde o início |
| P11 | Tratamento seguro de erros e logs | Já implementado desde o início |
| P12 | Trilha de auditoria | Implementado — historico_alteracoes + error_log [GESTOR_AUDIT] |
| P13 | Política de senhas | Implementado — mínimo 8 caracteres no cadastro |
| **P14** | **Criptografia em trânsito (HTTPS)** | **Implementado** |
| **P15** | **Menor privilégio no banco** | **Documentado — `database/grants_producao.sql`** |
| P16 | Verificação de dependências | Pendente |
| P17 | Proteção contra path traversal | Implementado — realpath()+str_starts_with() em todos os unlink() |
| P18 | SRI nas libs de CDN | Implementado — integrity+crossorigin em 41 tags CDN |
| P19 | Checklist de code review | Pendente |
