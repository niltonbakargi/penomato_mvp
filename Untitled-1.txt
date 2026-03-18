# ==============================================================================
#  PENOMATO — Cria pasta /docs, arquivos e faz commit
#  Rodar de dentro de: C:\xampp\htdocs\penomato_mvp
#  Comando: powershell -ExecutionPolicy Bypass -File criar_docs.ps1
# ==============================================================================

$ErrorActionPreference = "Stop"
$ROOT = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $ROOT

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "   PENOMATO — Criando /docs e commit    " -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

# ── VERIFICA GIT ────────────────────────────────────────────────────────────

if (-not (Test-Path ".git")) {
    Write-Host "[ERRO] Pasta .git nao encontrada." -ForegroundColor Red
    Write-Host "       Certifique-se de estar em: C:\xampp\htdocs\penomato_mvp" -ForegroundColor Yellow
    Read-Host "Pressione Enter para sair"
    exit 1
}
Write-Host "[OK] Repositorio Git encontrado em: $ROOT" -ForegroundColor Cyan

# ── CRIA PASTAS ─────────────────────────────────────────────────────────────

$pastas = @("docs", "docs\arquitetura", "docs\tcc", "docs\assets")
foreach ($p in $pastas) {
    if (-not (Test-Path $p)) {
        New-Item -ItemType Directory -Path $p | Out-Null
        Write-Host "[PASTA] Criada: $p" -ForegroundColor Yellow
    }
}

# ── FUNCAO AUXILIAR ─────────────────────────────────────────────────────────

function Salvar($caminho, $conteudo) {
    Set-Content -Path $caminho -Value $conteudo -Encoding UTF8
    Write-Host "[ARQUIVO] $caminho" -ForegroundColor Green
}

# ════════════════════════════════════════════════════════════════════════════
#  SKILL.md
# ════════════════════════════════════════════════════════════════════════════

Salvar "docs\SKILL.md" @'
# SKILL — PENOMATO MVP
> **Como usar em nova conversa com o Claude:**
> "Leia o SKILL.md: https://raw.githubusercontent.com/niltonbakargi/penomato_mvp/main/docs/SKILL.md
>  Preciso de ajuda para: [seu objetivo]"

---

## PROJETO

**Nome:** Penomato
**Tipo:** Plataforma colaborativa de documentacao botanica + educacao ambiental
**Repo:** https://github.com/niltonbakargi/penomato_mvp
**Versao:** MVP 1.0

### Problema
Nao faltam dados botanicos — falta estruturacao, padronizacao e rastreabilidade.
Dependencia de IA "caixa-preta" sem explicabilidade cientifica.

### Solucao
Artigos morfologicos estruturados por especie:
- 50+ atributos morfologicos (folha, flor, fruto, caule, semente)
- Exsicatas digitais padronizadas por parte da planta
- Referencias cientificas numeradas POR CARACTERISTICA (diferencial unico)
- Validacao por pares com historico rastreavel

### Visao de longo prazo
MVP hoje     → dados estruturados + validacao UEMS
Medio prazo  → IA explicavel treinada com dados confiaveis
Longo prazo  → jogos educativos tipo Pokemon GO para especies florestais
               restauracao florestal gamificada
               Enciclopedia Viva — 46.097 especies brasileiras

---

## DESENVOLVEDOR

- Nilton Bakargi
- Formacao: Educacao Fisica + Engenharia Florestal + TI (UFMS, ultimo semestre)
- Servidor da Secretaria de Educacao do Estado de MS
- TCC / Projeto Integrador UFMS — prazo: junho/2026 ou novembro/2026
- Parceria: UEMS — Prof. Dr. Norton (Eng. Florestal) — alunos ja onboard
- O Penomato une as 3 areas: e essencialmente um app de educacao ambiental

---

## STACK

| Camada    | Tecnologia                            |
|-----------|---------------------------------------|
| Backend   | PHP 8.2 (padrao MVC)                  |
| Banco     | MySQL 8.0                             |
| Frontend  | HTML5 + CSS3 + JS + Bootstrap 5       |
| Imagens   | PHP GD (resize auto para 1920px)      |
| Servidor  | XAMPP — C:\xampp\htdocs\penomato_mvp\ |
| Versao    | Git / GitHub                          |

---

## ESTRUTURA DE PASTAS

```
penomato_mvp/
├── src/
│   ├── Controllers/
│   │   ├── auth/                  login, logout, cadastro, verificar_acesso
│   │   ├── usuario/               perfil, senha
│   │   ├── confirmar_caracteristicas.php
│   │   ├── cadastro_caracteristicas.php
│   │   ├── upload_imagem_controller.php
│   │   ├── inserir_dados_internet.php
│   │   ├── finalizar_upload_temporario.php
│   │   └── controlador_painel_revisor.php
│   └── Views/
│       ├── auth/                  login, cadastro
│       ├── usuario/               perfil, editar, senha, contribuicoes
│       ├── includes/              cabecalho, rodape
│       ├── entrada_colaborador.php
│       ├── busca_caracteristicas.php
│       ├── especie_detalhes.php
│       ├── artigo_revisao.php
│       └── entrada_revisor.php
├── config/banco_de_dados.php
├── uploads/exsicatas/{especie_id}/{timestamp}_{parte}_{arquivo}
├── uploads/fotos_perfil/
├── tmp/especies/                  JSONs das 12 especies
├── docs/                          Esta pasta
├── index.php
└── .htaccess
```

---

## BANCO DE DADOS

### especies_administrativo — controle de fluxo
```
id, nome_cientifico, nome_popular, familia, prioridade
status ENUM('sem_dados','dados_internet','descrita','registrada',
            'em_revisao','revisada','contestado','publicado')
autor_dados_internet_id, autor_descrita_id, id_revisor_atual, id_validador_atual
data_dados_internet, data_descrita, data_registrada, data_revisada, data_publicado
```

### especies_caracteristicas — 70+ campos morfologicos
```
Secoes: basico, sinonimos, folha, flor, fruto, semente, caule, outras, referencias
Padrao de campo: atributo + atributo_ref  (ex: forma_folha + forma_folha_ref)
```

### imagens_especies — exsicatas digitais
```
id, especie_id
parte ENUM('folha','flor','fruto','caule','semente','habito','outros')
caminho_imagem, descricao, localizacao, data_coleta, observacoes
status_validacao ENUM('pendente','validado','rejeitado')
id_usuario_identificador, id_usuario_confirmador
```

### usuarios
```
id, nome, email, senha_hash
categoria ENUM('gestor','colaborador','revisor','validador','visitante')
subtipo_colaborador ENUM('identificador','coletor','fotografo')
instituicao, lattes, ORCID, ativo
```

---

## FLUXO DE STATUS

```
sem_dados
   → dados_internet   (colaborador importa de fontes online)
   → descrita         (colaborador preenche formulario completo)
   → registrada       (colaborador sobe imagens de todas as partes)
   → em_revisao       (revisor abre para analise)
   → revisada ✅      (revisor aprova)
   → contestado ⚠️    (revisor rejeita → volta para descrita)
   → publicado 🏆     (validador confirma cientificamente)
```

---

## MODULOS

| # | Modulo                          | Status         |
|---|---------------------------------|----------------|
| 1 | Autenticacao / sessao           | Codificado     |
| 2 | Cadastro de caracteristicas     | Codificado     |
| 3 | Upload de imagens (exsicatas)   | Codificado     |
| 4 | Importacao via JSON / internet  | Codificado     |
| 5 | Busca avancada (filtros AND)    | Codificado     |
| 6 | Ficha detalhada da especie      | Codificado     |
| 7 | Painel do revisor               | Codificado     |
| 8 | Perfil do usuario               | Codificado     |
| 9 | Painel do validador             | PENDENTE 🔴    |
|10 | Dashboard gestor                | PENDENTE 🔴    |

ATENCAO: nenhum modulo testado com fluxo end-to-end completo ainda.

---

## ESPECIES CARREGADAS (12)

Acca sellowiana (Goiabeira-serrana) · Acrocomia aculeata (Macauba)
Anadenanthera colubrina (Angico) · Anadenanthera macrocarpa (Angico-preto)
Schinus terebinthifolia (Aroeira-vermelha) · Lithraea molleoides (Aruera)
Myracrodruon urundeuva (Aroeira-do-sertao) · Oenocarpus bacaba (Bacaba)
Oenocarpus distichus (Bacaba-de-leque) · Mauritia flexuosa (Buriti)
Peltophorum dubium (Canafistula) · Vellozia squamata (Canela-de-ema)

---

## PRIORIDADES ATUAIS

CRITICAS:
- [ ] Salvar decisao do revisor no banco (aprovar/rejeitar)
- [ ] Atualizar status da especie apos decisao do revisor
- [ ] Corrigir usuario fixo ID 1 para $_SESSION['usuario_id']
- [ ] Proteger todas as paginas com verificacao de sessao
- [ ] Importar as 12 especies via JSON e verificar dados
- [ ] Criar 1 especie com fluxo completo testado
- [ ] Verificar requisitos formais TCC UFMS

ALTAS:
- [ ] Galeria + lightbox na ficha da especie
- [ ] Painel do validador
- [ ] Historico de revisoes por especie
- [ ] Recuperacao de senha por email

---

## DOCUMENTOS DESTA PASTA

| Arquivo                          | Conteudo                              |
|----------------------------------|---------------------------------------|
| SKILL.md                         | Este arquivo — contexto para o Claude |
| kanban.html                      | Board kanban interativo (drag & drop) |
| checklist.html                   | Checklist simples das 32 tarefas      |
| projeto_executivo.docx           | Documento Word completo               |
| arquitetura/banco_de_dados.md    | SQLs completos das 4 tabelas          |
| arquitetura/fluxo_sistema.md     | Fluxo de trabalho e permissoes        |
| tcc/estrutura_tcc.md             | Estrutura e argumentos para a banca   |

---

## IDENTIDADE VISUAL

Cor primaria: #0b5e42 (verde institucional)
Badges: sem_dados ⬜ dados_internet 🔵 descrita 🟡 registrada 🟠
        em_revisao 🔍 revisada ✅ contestado ⚠️ publicado 🏆
Partes: 🍃 folha  🌸 flor  🍎 fruto  🌿 caule  🌱 semente  🌳 geral

---
Skill v1.0 — Penomato MVP — Marco/2026
'@

# ════════════════════════════════════════════════════════════════════════════
#  docs/README.md
# ════════════════════════════════════════════════════════════════════════════

Salvar "docs\README.md" @'
# /docs — Documentacao do Penomato MVP

## Como usar o SKILL com o Claude

Cole no inicio de qualquer nova conversa:

```
Leia o SKILL.md do meu projeto:
https://raw.githubusercontent.com/niltonbakargi/penomato_mvp/main/docs/SKILL.md

Preciso de ajuda para: [seu objetivo aqui]
```

Exemplos de objetivos:
- "Salvar a decisao do revisor no banco"
- "Criar o painel do validador"
- "Escrever a introducao do TCC"
- "Corrigir o sistema de sessoes PHP"

## Arquivos

| Arquivo                       | Descricao                          |
|-------------------------------|------------------------------------|
| SKILL.md                      | Contexto completo para o Claude    |
| kanban.html                   | Kanban interativo das tarefas      |
| checklist.html                | Checklist simples                  |
| projeto_executivo.docx        | Documento Word do projeto          |
| arquitetura/banco_de_dados.md | SQLs das tabelas                   |
| arquitetura/fluxo_sistema.md  | Fluxo de status e permissoes       |
| tcc/estrutura_tcc.md          | Estrutura para a banca             |
'@

# ════════════════════════════════════════════════════════════════════════════
#  arquitetura/banco_de_dados.md
# ════════════════════════════════════════════════════════════════════════════

Salvar "docs\arquitetura\banco_de_dados.md" @'
# Banco de Dados — Penomato MVP

## Diagrama

```
usuarios (1) ──── (N) especies_administrativo (1) ──── (1) especies_caracteristicas
                            │ (1)
                            └── (N) imagens_especies
```

## SQL Completo

### usuarios
```sql
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    categoria ENUM('gestor','colaborador','revisor','validador','visitante') NOT NULL,
    subtipo_colaborador ENUM('identificador','coletor','fotografo') NULL,
    instituicao VARCHAR(255),
    lattes VARCHAR(255),
    ORCID VARCHAR(50),
    foto_perfil VARCHAR(500) NULL,
    ativo BOOLEAN DEFAULT TRUE,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso TIMESTAMP NULL
);
```

### especies_administrativo
```sql
CREATE TABLE especies_administrativo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_cientifico VARCHAR(255) NOT NULL,
    nome_popular VARCHAR(255) NULL,
    familia VARCHAR(100) NULL,
    prioridade TINYINT DEFAULT 3,
    status ENUM('sem_dados','dados_internet','descrita','registrada',
                'em_revisao','revisada','contestado','publicado') DEFAULT 'sem_dados',
    autor_dados_internet_id INT NULL,
    autor_descrita_id INT NULL,
    autor_registrada_id INT NULL,
    id_revisor_atual INT NULL,
    id_validador_atual INT NULL,
    identificadores_historico TEXT NULL,
    revisores_historico TEXT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    data_dados_internet DATE NULL,
    data_descrita DATE NULL,
    data_registrada DATE NULL,
    data_revisada DATE NULL,
    data_publicado DATE NULL,
    FOREIGN KEY (autor_dados_internet_id) REFERENCES usuarios(id),
    FOREIGN KEY (autor_descrita_id) REFERENCES usuarios(id),
    FOREIGN KEY (id_revisor_atual) REFERENCES usuarios(id),
    FOREIGN KEY (id_validador_atual) REFERENCES usuarios(id)
);
```

### especies_caracteristicas
```sql
CREATE TABLE especies_caracteristicas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    especie_id INT NOT NULL,
    -- BASICO
    nome_cientifico_completo VARCHAR(255), nome_cientifico_completo_ref VARCHAR(255),
    sinonimos TEXT,                         sinonimos_ref VARCHAR(255),
    nome_popular TEXT,                      nome_popular_ref VARCHAR(255),
    familia VARCHAR(100),                   familia_ref VARCHAR(255),
    -- FOLHA
    forma_folha TEXT,     forma_folha_ref VARCHAR(255),
    filotaxia TEXT,       filotaxia_ref VARCHAR(255),
    tipo_folha TEXT,      tipo_folha_ref VARCHAR(255),
    tamanho_folha TEXT,   tamanho_folha_ref VARCHAR(255),
    textura_folha TEXT,   textura_folha_ref VARCHAR(255),
    margem_folha TEXT,    margem_folha_ref VARCHAR(255),
    venacao_folha TEXT,   venacao_folha_ref VARCHAR(255),
    -- FLOR
    cor_flores TEXT,      cor_flores_ref VARCHAR(255),
    simetria_floral TEXT, simetria_floral_ref VARCHAR(255),
    numero_petalas TEXT,  numero_petalas_ref VARCHAR(255),
    disposicao_flor TEXT, disposicao_flor_ref VARCHAR(255),
    aroma_flor TEXT,      aroma_flor_ref VARCHAR(255),
    tamanho_flor TEXT,    tamanho_flor_ref VARCHAR(255),
    -- FRUTO
    tipo_fruto TEXT,      tipo_fruto_ref VARCHAR(255),
    tamanho_fruto TEXT,   tamanho_fruto_ref VARCHAR(255),
    cor_fruto TEXT,       cor_fruto_ref VARCHAR(255),
    textura_fruto TEXT,   textura_fruto_ref VARCHAR(255),
    dispersao_fruto TEXT, dispersao_fruto_ref VARCHAR(255),
    aroma_fruto TEXT,     aroma_fruto_ref VARCHAR(255),
    -- SEMENTE
    tipo_semente TEXT,       tipo_semente_ref VARCHAR(255),
    tamanho_semente TEXT,    tamanho_semente_ref VARCHAR(255),
    cor_semente TEXT,        cor_semente_ref VARCHAR(255),
    textura_semente TEXT,    textura_semente_ref VARCHAR(255),
    quantidade_semente TEXT, quantidade_semente_ref VARCHAR(255),
    -- CAULE
    tipo_caule TEXT,        tipo_caule_ref VARCHAR(255),
    estrutura_caule TEXT,   estrutura_caule_ref VARCHAR(255),
    textura_caule TEXT,     textura_caule_ref VARCHAR(255),
    cor_caule TEXT,         cor_caule_ref VARCHAR(255),
    forma_caule TEXT,       forma_caule_ref VARCHAR(255),
    modificacao_caule TEXT, modificacao_caule_ref VARCHAR(255),
    diametro_caule TEXT,    diametro_caule_ref VARCHAR(255),
    ramificacao_caule TEXT, ramificacao_caule_ref VARCHAR(255),
    -- OUTRAS
    possui_espinhos ENUM('Sim','Nao','Nao informado') DEFAULT 'Nao informado',
    possui_espinhos_ref VARCHAR(255),
    possui_latex ENUM('Sim','Nao','Nao informado') DEFAULT 'Nao informado',
    possui_latex_ref VARCHAR(255),
    possui_seiva ENUM('Sim','Nao','Nao informado') DEFAULT 'Nao informado',
    possui_seiva_ref VARCHAR(255),
    possui_resina ENUM('Sim','Nao','Nao informado') DEFAULT 'Nao informado',
    possui_resina_ref VARCHAR(255),
    -- REFERENCIAS
    referencias TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (especie_id) REFERENCES especies_administrativo(id),
    UNIQUE KEY unique_especie (especie_id)
);
```

### imagens_especies
```sql
CREATE TABLE imagens_especies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    especie_id INT NOT NULL,
    parte ENUM('folha','flor','fruto','caule','semente','habito','outros') NOT NULL,
    subparte VARCHAR(100),
    caminho_imagem VARCHAR(500) NOT NULL,
    nome_original VARCHAR(255),
    tamanho_bytes INT,
    mime_type VARCHAR(100),
    descricao TEXT,
    localizacao VARCHAR(255),
    data_coleta DATE,
    observacoes TEXT,
    id_usuario_identificador INT NOT NULL,
    id_usuario_confirmador INT NULL,
    status_validacao ENUM('pendente','validado','rejeitado') DEFAULT 'pendente',
    data_validacao DATE NULL,
    motivo_rejeicao TEXT NULL,
    data_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (especie_id) REFERENCES especies_administrativo(id),
    FOREIGN KEY (id_usuario_identificador) REFERENCES usuarios(id),
    FOREIGN KEY (id_usuario_confirmador) REFERENCES usuarios(id)
);
```

## Scripts utilitarios

```sql
-- Reset para testes (preserva estrutura)
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM imagens_especies;
DELETE FROM especies_caracteristicas;
UPDATE especies_administrativo SET status = 'sem_dados';
SET FOREIGN_KEY_CHECKS = 1;

-- Relatorio de progresso
SELECT status, COUNT(*) as total FROM especies_administrativo GROUP BY status;

-- Especies com fluxo completo
SELECT ea.nome_cientifico, ea.status, ec.referencias,
       COUNT(ie.id) as total_imagens
FROM especies_administrativo ea
LEFT JOIN especies_caracteristicas ec ON ea.id = ec.especie_id
LEFT JOIN imagens_especies ie ON ea.id = ie.especie_id
GROUP BY ea.id ORDER BY ea.status;
```
'@

# ════════════════════════════════════════════════════════════════════════════
#  arquitetura/fluxo_sistema.md
# ════════════════════════════════════════════════════════════════════════════

Salvar "docs\arquitetura\fluxo_sistema.md" @'
# Fluxo do Sistema — Penomato MVP

## Ciclo de vida de uma especie

```
[sem_dados]
    ↓  colaborador importa dados de fontes (Flora do Brasil, Lorenzi, etc.)
[dados_internet]
    ↓  colaborador preenche formulario morfologico completo
[descrita]
    ↓  colaborador faz upload de imagens de todas as partes
[registrada]
    ↓  revisor abre o artigo para analise
[em_revisao]
    ↓  APROVA                       ↓  REJEITA + justificativa
[revisada]                      [contestado] → volta para [descrita]
    ↓  validador confirma
[publicado] 🏆
```

## Perfis e permissoes

| Perfil              | Acoes permitidas                                          |
|---------------------|-----------------------------------------------------------|
| gestor              | Cadastrar especies, gerenciar usuarios, ver todos paineis |
| colaborador/identif.| Preencher caracteristicas, importar, subir imagens        |
| colaborador/coletor | Subir imagens de campo com metadados                      |
| colaborador/fotog.  | Subir fotos padronizadas                                  |
| revisor             | Revisar artigos, aprovar ou rejeitar com parecer          |
| validador           | Validacao cientifica final                                |
| visitante           | Busca e visualizacao de especies publicadas               |

## Organizacao das imagens

```
uploads/exsicatas/{especie_id}/{timestamp}_{parte}_{nome_original}.jpg
```
- Resize automatico para 1920px mantendo proporcao
- Formatos aceitos: JPG, PNG (max 10MB)
- Metadados em imagens_especies: localizacao, data_coleta, observacoes

## URLs principais

```
/index.php
/src/Views/auth/login.php
/src/Views/entrada_colaborador.php
/src/Controllers/confirmar_caracteristicas.php
/src/Views/upload_imagem_views.php
/src/Views/busca_caracteristicas.php
/src/Views/especie_detalhes.php?id=X
/src/Views/entrada_revisor.php
/src/Views/artigo_revisao.php?id=X
```
'@

# ════════════════════════════════════════════════════════════════════════════
#  tcc/estrutura_tcc.md
# ════════════════════════════════════════════════════════════════════════════

Salvar "docs\tcc\estrutura_tcc.md" @'
# TCC / Projeto Integrador UFMS — Penomato

## Titulos sugeridos

Principal:
"Penomato: Plataforma Colaborativa para Documentacao Morfologica de Especies
 Florestais do Cerrado como Ferramenta de Educacao Ambiental"

Alternativo:
"Penomato: Sistema Web para Estruturacao e Validacao Colaborativa do
 Conhecimento Botanico com Foco no Cerrado Brasileiro"

---

## Estrutura do documento

### 1. Introducao
- Biodiversidade brasileira: 46.097 especies e o problema da desestruturacao
- Justificativa pessoal: convergencia de Ed. Fisica + Eng. Florestal + TI
- O Penomato como ferramenta de educacao ambiental ativa (nao passiva)
- Parceria UEMS como evidencia de relevancia real
- Objetivos geral e especificos

### 2. Referencial Teorico
- Educacao ambiental e aprendizagem ativa por meio da tecnologia
- Documentacao botanica: exsicatas fisicas e digitais
- Sistemas colaborativos de producao de conhecimento (Wikipedia, iNaturalist)
- Identificacao por atributos explicitos vs. IA opaca
- Biodiversidade do Cerrado: importancia e ameacas

### 3. Metodologia
- Desenvolvimento agil iterativo (sem Scrum formal)
- Parceria com UEMS como validacao do problema e do produto
- Stack tecnologica e justificativa das escolhas (PHP, MySQL, Bootstrap)
- Arquitetura MVC e modelagem do banco de dados
- Fluxo de trabalho cientifico com status auditavel

### 4. Desenvolvimento
- Modelagem do banco de dados (4 tabelas normalizadas)
- Sistema de referencias numeradas por caracteristica (diferencial principal)
- Modulos implementados com descricao tecnica
- Fluxo de validacao por pares (identificador → revisor → validador)
- Decisoes de UX: badges de status, organizacao por parte, busca por atributos

### 5. Resultados
- 12 especies documentadas com dados estruturados
- Parceria UEMS: alunos envolvidos, especies cadastradas, feedback
- Demonstracao do fluxo completo (ao menos 1 especie publicada)
- Comparacao com plataformas similares

### 6. Discussao
- Contribuicoes para educacao ambiental formal e nao-formal
- Potencial de expansao: 12 → 200 → 1.000 → 46.097 especies
- Limitacoes do MVP e trabalhos futuros
- Visao de longo prazo: IA explicavel + jogos educativos

### 7. Conclusao
- Sintese das contribuicoes tecnicas e educacionais
- Alinhamento com as 3 areas de formacao do autor
- Impacto potencial para conservacao da biodiversidade brasileira

### 8. Referencias

---

## Argumentos-chave para a banca

P: "Por que PHP e nao Python/Node?"
R: PHP roda em qualquer hospedagem compartilhada incluindo servidores da UEMS,
   nao exige configuracao de ambiente, e o foco era entregar valor, nao tecnologia.

P: "Como garante a qualidade dos dados?"
R: Referencias numeradas por caracteristica + revisao em 2 etapas (revisor +
   validador) + historico rastreavel de quem cadastrou cada dado e quando.

P: "Como escala para 46 mil especies?"
R: Arquitetura ja preparada: banco normalizado, status automatizado, sistema
   colaborativo que suporta multiplos usuarios em paralelo. Com 5 universidades
   parceiras, 1.000 especies/ano e alcancavel.

P: "O que diferencia do iNaturalist ou SpeciesLink?"
R: Unico sistema com: (1) artigo morfologico estruturado como produto final,
   (2) referencias rastreadas por caracteristica individual, (3) fluxo de
   validacao auditavel por especialistas com historico completo.

P: "Qual a relacao com educacao ambiental?"
R: Alunos aprendem botanica FAZENDO — documentam especies reais, aplicam metodo
   cientifico, aprendem a citar fontes. E aprendizagem ativa, nao passiva.
   A ferramenta de busca por caracteristicas e usada diretamente em sala de aula.

---

## Cronograma (formatura junho/2026)

| Periodo     | Foco                                                    |
|-------------|---------------------------------------------------------|
| Marco/2026  | Fechar modulos criticos + fluxo completo testado        |
| Abril/2026  | Dados reais UEMS, testes com alunos, ajustes de UX      |
| Maio/2026   | Escrita do TCC (metodologia, desenvolvimento, resultados)|
| Junho/2026  | Revisao final, preparacao da banca, entrega             |
'@

# ════════════════════════════════════════════════════════════════════════════
#  COPIA ARQUIVOS HTML/DOCX SE EXISTIREM NA PASTA DO PROJETO
# ════════════════════════════════════════════════════════════════════════════

Write-Host ""
Write-Host "Verificando arquivos gerados (kanban, checklist, docx)..." -ForegroundColor Cyan

$copias = @(
    @{ src = "penomato_kanban.html";              dst = "docs\kanban.html" },
    @{ src = "penomato_mvp_checklist.html";       dst = "docs\checklist.html" },
    @{ src = "Penomato_Projeto_Executivo.docx";   dst = "docs\projeto_executivo.docx" },
    # tambem tenta nomes com espaco (como o Claude salva as vezes)
    @{ src = "penomato kanban.html";              dst = "docs\kanban.html" },
    @{ src = "penomato mvp checklist.html";       dst = "docs\checklist.html" },
    @{ src = "Penomato Projeto Executivo.docx";   dst = "docs\projeto_executivo.docx" }
)

foreach ($c in $copias) {
    if ((Test-Path $c.src) -and (-not (Test-Path $c.dst))) {
        Copy-Item $c.src $c.dst -Force
        Write-Host "[COPIADO] $($c.src) -> $($c.dst)" -ForegroundColor Yellow
    }
}

# ════════════════════════════════════════════════════════════════════════════
#  GIT: ADD → COMMIT → PUSH
# ════════════════════════════════════════════════════════════════════════════

Write-Host ""
Write-Host "Fazendo commit no GitHub..." -ForegroundColor Cyan
Write-Host ""

$data = Get-Date -Format "dd/MM/yyyy HH:mm"

try {
    git add docs/
    git status --short

    $changed = git diff --cached --name-only
    if (-not $changed) {
        Write-Host "[INFO] Nenhuma mudanca para commitar." -ForegroundColor Gray
    } else {
        git commit -m "docs: cria estrutura /docs com SKILL.md, arquitetura e TCC [$data]"
        Write-Host ""
        git push origin main
        Write-Host ""
        Write-Host "[OK] Commit e push realizados com sucesso!" -ForegroundColor Green
    }
} catch {
    Write-Host ""
    Write-Host "[AVISO] Problema no git. Rode manualmente:" -ForegroundColor Yellow
    Write-Host "  git add docs/" -ForegroundColor White
    Write-Host "  git commit -m 'docs: adiciona SKILL.md e documentacao'" -ForegroundColor White
    Write-Host "  git push origin main" -ForegroundColor White
}

# ════════════════════════════════════════════════════════════════════════════
#  RESUMO FINAL
# ════════════════════════════════════════════════════════════════════════════

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "   PRONTO!                              " -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Arquivos criados em: $ROOT\docs\" -ForegroundColor White
Write-Host ""
Write-Host "Para usar em nova conversa com o Claude:" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Leia o SKILL.md do meu projeto:" -ForegroundColor White
Write-Host "  https://raw.githubusercontent.com/niltonbakargi/penomato_mvp/main/docs/SKILL.md" -ForegroundColor Yellow
Write-Host "  Preciso de ajuda para: [seu objetivo]" -ForegroundColor White
Write-Host ""

Read-Host "Pressione Enter para fechar"