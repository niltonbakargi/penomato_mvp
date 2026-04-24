# Checklist de Code Review — Penomato MVP

**Projeto de Extensão:** Programa de Extensão UFMS Digital (95DX7.200525)
**Projeto de Software:** Penomato — Plataforma de Catalogação de Espécies Nativas do Cerrado
**Referência:** Plano de Desenvolvimento Seguro — Módulo 3 (Boas Práticas em Serviços Web)

---

## Como usar

Este checklist deve ser aplicado a **todo Pull Request ou alteração direta no branch `main`** que envolva arquivos PHP, SQL ou de configuração. Marque cada item como:

- `[x]` — verificado e OK
- `[~]` — verificado, não se aplica a esta mudança
- Deixe em branco e adicione comentário se encontrar problema

---

## 1. Autenticação e Controle de Acesso

- [ ] Todo controller que exige login chama `verificarAcesso()` ou `estaLogado()` **antes** de qualquer lógica de negócio
- [ ] Páginas restritas a gestor verificam `$_SESSION['usuario_categoria'] === 'gestor'` no servidor — não apenas escondem o link na view
- [ ] Páginas restritas a revisor verificam `$_SESSION['usuario_categoria'] === 'revisor'`
- [ ] Após verificação de acesso, o ID do usuário vem **sempre** de `$_SESSION['usuario_id']`, nunca de `$_POST` ou `$_GET`
- [ ] Um colaborador não consegue acessar nem modificar dados de outro colaborador (testar com IDs diferentes na URL)

---

## 2. Injeção de SQL

- [ ] Toda query que usa variável externa usa **prepared statement** (`$pdo->prepare()` + `execute()`) — nunca concatenação de string
- [ ] As funções auxiliares `buscarUm()`, `buscarTodos()`, `inserir()`, `atualizar()` de `banco_de_dados.php` são usadas onde aplicável
- [ ] Nenhuma query monta cláusula `WHERE` ou `ORDER BY` com valor direto de `$_GET`/`$_POST` sem validar contra lista branca
- [ ] Filtros de status e prioridade validam contra array de valores válidos antes de usar na query (ex: `in_array($status, $status_validos)`)

---

## 3. XSS — Cross-Site Scripting

- [ ] **Todo** valor de `$_GET`, `$_POST` ou variável de sessão ecoado em HTML está dentro de `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`
- [ ] Mensagens de erro e sucesso vindas de `$_SESSION['mensagem_erro']` / `$_SESSION['mensagem_sucesso']` são escapadas na view
- [ ] Parâmetros passados via URL (ex: mensagem de erro em redirect) que são exibidos na view são escapados
- [ ] Conteúdo de banco de dados exibido em HTML é escapado (ex: `htmlspecialchars($especie['nome_cientifico'])`)
- [ ] Não há uso de `echo $_GET[...]` ou `echo $_POST[...]` direto, sem filtro

---

## 4. Upload de Arquivos

- [ ] Tipo MIME é validado no servidor com `finfo_file()` contra lista de tipos permitidos — não confia em `$_FILES['type']`
- [ ] Extensão do arquivo é validada contra lista branca (`jpg`, `jpeg`, `png`, `webp` para imagens; `pdf` para exsicatas)
- [ ] Nome do arquivo salvo é **gerado pelo sistema** (ex: `uniqid()` + extensão) — nunca usa o nome original enviado pelo usuário
- [ ] Diretório de upload criado com `mkdir($pasta, 0755, true)` — nunca `0777`
- [ ] A pasta de uploads não está dentro de um diretório servido pelo PHP sem proteção — arquivos enviados não são executáveis pelo servidor

---

## 5. Path Traversal

- [ ] Todo `unlink()` que recebe caminho do banco de dados ou de variável usa `realpath()` e valida com `str_starts_with($path_real, $raiz_uploads)`
- [ ] Padrão obrigatório antes de qualquer `unlink()`:
  ```php
  $raiz = realpath(__DIR__ . '/../../uploads');
  $real = realpath($arquivo_path);
  if ($real && str_starts_with($real, $raiz . DIRECTORY_SEPARATOR)) {
      unlink($real);
  }
  ```
- [ ] Nenhum `unlink()`, `file_get_contents()` ou `include()` usa diretamente valor de `$_GET`/`$_POST`

---

## 6. Gerenciamento de Sessão

- [ ] Após login bem-sucedido, `session_regenerate_id(true)` é chamado antes de popular `$_SESSION`
- [ ] Cookie de sessão usa `httponly: true`, `samesite: 'Lax'`, e `secure: true` em produção
- [ ] Após logout, `session_destroy()` é chamado e o cookie é invalidado
- [ ] Nenhuma informação sensível (senha, token completo) é armazenada em `$_SESSION`

---

## 7. Validação de Entrada

- [ ] Campos com valores fixos (status de espécie, prioridade, bioma, categoria de usuário) são validados contra array de valores válidos no controller
- [ ] Campos de texto têm limite de tamanho aplicado antes de inserir no banco (compatível com `VARCHAR` definido no schema)
- [ ] E-mail é validado com `filter_var($email, FILTER_VALIDATE_EMAIL)`
- [ ] Senhas novas têm mínimo de 8 caracteres verificado no servidor
- [ ] IDs recebidos por POST/GET são convertidos para `int` com `(int)$_POST['id']` antes de usar

---

## 8. CSRF

- [ ] Formulários que executam ações destrutivas (excluir membro, excluir espécie, excluir imagem) incluem token CSRF
- [ ] O token é verificado no controller antes de processar a ação
- [ ] Token é gerado por sessão (`$_SESSION['csrf_token'] = bin2hex(random_bytes(32))`) e validado com `hash_equals()`

> **Nota:** proteção CSRF completa ainda está pendente no MVP. Ao implementar novos formulários destrutivos, incluir token desde o início.

---

## 9. Trilha de Auditoria

- [ ] Ações de **gestor** que alteram dados (aceitar/rejeitar membro, excluir membro, inserir espécie) registram `error_log()` com prefixo `[GESTOR_AUDIT]` incluindo: `gestor_id`, IDs afetados, nome/email e IP
- [ ] Ações de **edição de espécie** (status, prioridade, atribuição, exclusão de imagem) inserem registro em `historico_alteracoes` com valor anterior e novo
- [ ] Ações de reversão registram nova entrada em `historico_alteracoes` com `justificativa='revert_gestor'` — **nunca** deletam registros antigos
- [ ] Nenhum novo código faz `DELETE FROM historico_alteracoes` diretamente

---

## 10. Tratamento de Erros

- [ ] Em produção (`APP_ENV !== 'dev'`), nenhuma mensagem de exceção ou stack trace é exibida ao usuário
- [ ] Erros técnicos são registrados com `error_log()` e o usuário vê mensagem genérica
- [ ] Blocos `catch` não ficam vazios — pelo menos `error_log()` ou redirect com mensagem de erro
- [ ] Credenciais, tokens ou detalhes internos não aparecem em mensagens de erro exibidas na tela

---

## 11. Dependências de CDN

- [ ] Todo `<script src="...cdn...">` e `<link href="...cdn...">` incluem atributos `integrity="sha384-..."` e `crossorigin="anonymous"`
- [ ] Versões de bibliotecas são fixas (ex: `bootstrap@5.3.0`, `chart.js@4.4.1`) — nunca `@latest` ou sem versão
- [ ] Ao atualizar versão de biblioteca CDN: recomputar o hash SHA-384 com Python (`hashlib.sha384`) e atualizar o atributo `integrity`
- [ ] Google Fonts é exceção documentada: URL gerada dinamicamente, SRI não aplicável

---

## 12. Configuração e Infraestrutura

- [ ] `config/producao.php` está no `.gitignore` e nunca foi commitado
- [ ] `config/dev_local.php` está no `.gitignore` e nunca foi commitado
- [ ] Novos arquivos de configuração com segredos seguem o mesmo padrão (gitignored + exemplo sem valores reais)
- [ ] Nenhuma chave de API, senha ou token aparece no diff
- [ ] `display_errors` está `off` em produção (verificar `config/app.php`)

---

## 13. Banco de Dados

- [ ] Novas tabelas adicionadas ao `database/grants_producao.sql` com o nível de privilégio correto (ver seção de referência abaixo)
- [ ] Tabelas de auditoria novas recebem apenas `SELECT, INSERT` — nunca `UPDATE` ou `DELETE`
- [ ] Tabelas de referência somente leitura recebem apenas `SELECT`
- [ ] Novas colunas com valores fixos usam tipo `ENUM` no schema SQL (ou validação por lista branca no PHP se ENUM não for viável)

### Referência rápida de privilégios

| Tipo de tabela | Privilégios |
|---|---|
| Dados da aplicação (CRUD normal) | `SELECT, INSERT, UPDATE, DELETE` |
| Dados de referência (importados, só leitura) | `SELECT` |
| Trilha de auditoria (imutável) | `SELECT, INSERT` |

---

## Resumo final antes de aprovar

- [ ] Todos os itens aplicáveis acima foram verificados
- [ ] Nenhum `var_dump()`, `print_r()` ou `die()` de debug foi deixado no código
- [ ] A mudança foi testada localmente (XAMPP) nos fluxos afetados
- [ ] Se a mudança afeta o banco, o schema em `database/` foi atualizado
