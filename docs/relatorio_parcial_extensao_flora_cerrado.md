# RELATÓRIO PARCIAL DA AÇÃO DE EXTENSÃO

**Projeto de Extensão:** Programa de Extensão UFMS Digital (95DX7.200525)

**Nome completo:** [PREENCHER — nome completo do estudante]
**Disciplina:** Análise Organizacional e Soluções Tecnológicas
**Semestre letivo:** [PREENCHER — ex: 2026/1]
**Curso:** Tecnologia da Informação

---

## MÓDULO DE CONSULTA À NOMENCLATURA BOTÂNICA DO CERRADO: DESENVOLVIMENTO E DISPONIBILIZAÇÃO PÚBLICA DE FERRAMENTA WEB INTEGRADA AO SISTEMA PENOMATO

---

## Resumo

Este relatório descreve o desenvolvimento e disponibilização pública do módulo **Flora do Cerrado**, integrado ao sistema web Penomato, como ação de extensão vinculada ao Programa UFMS Digital. A ação consistiu na importação, tratamento e disponibilização em interface web acessível de dados nomenclaturais e distribucionais de **12.161 espécies vegetais nativas do Cerrado** e **19.053 registros de sinônimos nomenclaturais**, provenientes da lista oficial do REFLORA — Flora e Funga do Brasil 2020, publicada pelo Jardim Botânico do Rio de Janeiro (JBRJ) sob licença CC-BY. A metodologia envolveu o desenvolvimento de scripts de importação em Python, modelagem de banco de dados relacional em MySQL, e construção de interface web em PHP com foco em usabilidade e acesso sem necessidade de cadastro. O público-alvo abrange estudantes de graduação em ciências biológicas e engenharia florestal, pesquisadores, técnicos de campo e qualquer cidadão com interesse na flora do bioma. O resultado principal é uma ferramenta de consulta pública que, a partir de qualquer nome — científico, popular ou sinônimo —, apresenta a nomenclatura válida atualizada, dados de distribuição geográfica, endemismo e formas de vida, conectando o usuário aos registros de campo disponíveis no sistema Penomato.

**Palavras-chave:** Flora do Cerrado. Nomenclatura botânica. Extensão universitária. REFLORA. Acesso aberto.

---

## 1. Introdução

O Cerrado brasileiro é reconhecido como um dos 36 hotspots mundiais de biodiversidade, com aproximadamente 12.356 espécies vegetais nativas, das quais cerca de 4.400 são endêmicas — existentes exclusivamente nesse bioma (Myers et al., 2000; Flora e Funga do Brasil, 2023). Apesar disso, o Cerrado é também o bioma que mais perdeu cobertura original nas últimas décadas: estima-se que apenas 19,8% da vegetação nativa permaneça intacta (MapBiomas, 2023). A pressão sobre essa biodiversidade contrasta com a baixa capacidade social de identificar, nomear e reconhecer as espécies que compõem esse patrimônio natural.

Um dos obstáculos mais práticos enfrentados por estudantes, técnicos de campo e profissionais de engenharia florestal é a confusão nomenclatural decorrente da existência de **sinônimos botânicos** — nomes antigos ou inválidos que continuam circulando em manuais, relatórios e conversas informais, enquanto a nomenclatura válida se atualiza continuamente segundo o Código Internacional de Nomenclatura para Algas, Fungos e Plantas. Uma espécie como *Copaifera langsdorffii* Desf. pode ser encontrada em literatura mais antiga sob dezenas de combinações e sinônimos — e não há ferramenta de consulta rápida em português, de acesso livre e sem necessidade de cadastro, que resolva essa dúvida com dados atualizados.

A ação de extensão aqui relatada propõe responder a essa lacuna com uma solução tecnológica concreta: o **módulo Flora do Cerrado**, integrado ao sistema Penomato, desenvolvido como parte do Programa UFMS Digital — Análise Organizacional e Soluções Tecnológicas.

O Penomato é um sistema web colaborativo para documentação de exsicatas e descrições fitomorfológicas de espécies nativas, desenvolvido como Trabalho de Conclusão de Curso em Tecnologia da Informação pela UFMS, em parceria com o Departamento de Botânica do curso de Engenharia Florestal da Universidade Estadual de Mato Grosso do Sul (UEMS). O módulo Flora do Cerrado estende o Penomato com uma camada pública de acesso aberto ao conhecimento nomenclatural oficial, democratizando uma base de dados governamental — o REFLORA/JBRJ — para um público muito mais amplo do que o de especialistas.

**Objetivo geral:** disponibilizar publicamente, em interface web acessível e sem necessidade de cadastro, uma ferramenta de consulta à nomenclatura botânica válida das espécies do Cerrado, baseada nos dados oficiais do REFLORA — Flora e Funga do Brasil 2020 (JBRJ).

**Objetivos específicos:**
- Importar, tratar e estruturar em banco de dados relacional as listas de espécies do Cerrado e seus respectivos sinônimos nomenclaturais, provenientes do REFLORA/JBRJ;
- Desenvolver interface web pública que permita busca por nome científico, nome popular ou sinônimo, retornando a nomenclatura válida atualizada;
- Apresentar para cada nome aceito: família, formas de vida, distribuição estadual (com destaque para Mato Grosso do Sul), endemismo e lista de sinônimos;
- Quando o termo buscado corresponder a um sinônimo, alertar o usuário e redirecionar para o nome aceito correspondente;
- Conectar cada nome aceito aos registros de campo disponíveis no sistema Penomato, estimulando o ciclo educação → pesquisa → documentação.

O **público-alvo** da ação inclui: (a) estudantes de graduação e pós-graduação em ciências biológicas, engenharia florestal e áreas afins; (b) profissionais e técnicos que atuam em inventários florestais, licenciamento ambiental e gestão de unidades de conservação; (c) educadores ambientais e professores do ensino básico e médio; (d) qualquer cidadão com acesso à internet e interesse na flora do Cerrado.

---

## 2. Metodologia

O desenvolvimento do módulo seguiu uma metodologia iterativa de prototipagem e refinamento, com três etapas principais:

### 2.1 Levantamento e tratamento dos dados

Os dados utilizados provêm de duas fontes primárias do REFLORA — Flora e Funga do Brasil 2020, publicadas pelo Jardim Botânico do Rio de Janeiro (JBRJ) sob licença Creative Commons CC-BY:

- **Lista de angiospermas do Cerrado** (`angiospermsdatabase.xlsx`): base com espécies de plantas com flor, incluindo campos de família, gênero, nome científico, autor, origem, endemismo, formas de vida, distribuição estadual (UFs) e domínio fitogeográfico.
- **Lista de gimnospermas do Cerrado** (`gymnospermsdatabase.xlsx`): base complementar para espécies sem flor (pinheiros, ciprestes e afins presentes no Cerrado).

Ambas as bases foram filtradas para incluir apenas espécies com `dom_fitogeografico` contendo "Cerrado", resultando em **12.161 espécies aceitas** para o bioma.

Para os sinônimos, foi necessário solucionar um problema de modelagem dos dados: no REFLORA, registros de sinônimos não possuem o campo `dom_fitogeografico` preenchido, pois são registros nomenclaturais — não distribucionais. Isso significa que filtrar sinônimos pelo domínio fitogeográfico excluiria todos eles. A solução adotada foi um critério indireto: **importar um sinônimo se, e somente se, seu `nome_aceito` correspondente estiver presente na base de espécies do Cerrado**. Esse critério elevou a cobertura de sinônimos de 2.140 para **19.053 registros**.

### 2.2 Modelagem e implementação do banco de dados

Foram criadas duas tabelas no banco MySQL do sistema Penomato:

**`flora_brasil_plantas`** — espécies aceitas do Cerrado:
- Campos: `id`, `grupo` (Angiospermas/Gimnospermas), `familia`, `genero`, `nome_cientifico`, `autor`, `origem`, `endemica`, `formas_vida`, `distr_uf`, `dom_fitogeografico`, `nomes_vernaculares`
- Índices: FULLTEXT em `nome_cientifico` e `nomes_vernaculares` para busca rápida por texto
- Volume: 12.161 registros

**`flora_brasil_sinonimos`** — sinônimos nomenclaturais:
- Campos: `id`, `sinonimo`, `autor`, `familia`, `nome_aceito`, `tipo` (heterotípico / homotípico / basônimo)
- Índices: FULLTEXT em `sinonimo` e índice convencional em `nome_aceito`
- Volume: 19.053 registros

A importação foi automatizada via scripts Python (`importar_flora_brasil.py` e `importar_flora_sinonimos.py`), que leem os arquivos `.xlsx` originais, aplicam os filtros e inserem os dados via conexão MySQL/PyMySQL.

### 2.3 Desenvolvimento da interface web

A interface foi desenvolvida em PHP, integrada ao framework MVC do Penomato, e projetada com foco em redução de fricção para o usuário. As principais decisões de design foram:

- **Busca unificada:** um único campo de pesquisa aceita qualquer tipo de nome, sem que o usuário precise saber de antemão se é um nome aceito ou sinônimo;
- **Landing page com exemplos clicáveis:** quando nenhuma busca está ativa, a página apresenta exemplos de espécies conhecidas (como o pequi, o ipê-amarelo e a copaíba) como botões clicáveis — reduzindo a barreira de entrada para usuários não especialistas;
- **Ficha do nome aceito:** apresenta família, formas de vida, nomes populares, distribuição estadual com destaque visual para MS, endemismo, lista de sinônimos e link direto para registros de campo no Penomato;
- **Card de sinônimo:** alerta visual claro com nome riscado, tipo de sinonímia descrito em linguagem simples e botão de redirecionamento para o nome aceito.

---

## 3. Atividades Desenvolvidas

### 3.1 Fase 1 — Análise e aquisição dos dados (semana 1)

- Identificação das bases públicas do REFLORA/JBRJ como fonte primária;
- Download dos arquivos `.xlsx` de angiospermas e gimnospermas do Cerrado;
- Análise exploratória dos dados: estrutura de colunas, valores ausentes, inconsistências de codificação (caracteres especiais em nomes científicos);
- Definição do critério de filtragem por `dom_fitogeografico = 'Cerrado'` para espécies aceitas.

### 3.2 Fase 2 — Modelagem e implementação do banco (semana 1–2)

- Criação dos scripts SQL de definição das tabelas (`criar_tabela_flora_brasil.sql` e `criar_tabela_flora_sinonimos.sql`) com índices otimizados para busca por texto;
- Desenvolvimento e execução dos scripts Python de importação;
- Identificação do bug no critério de importação de sinônimos (filtro por domínio fitogeográfico exclui todos os sinônimos) e correção com critério indireto por `nome_aceito`;
- Validação dos dados importados: contagem de registros, verificação de ausência de duplicatas, teste de buscas de controle.

### 3.3 Fase 3 — Desenvolvimento da primeira versão da interface (semana 2)

- Construção da página `flora_cerrado.php` com filtros por família e forma de vida, tabela paginada e gráficos estatísticos (Chart.js);
- Integração ao navbar e à home do Penomato com botão de acesso;
- Testes de usabilidade informais com usuários do grupo de pesquisa parceiro.

### 3.4 Fase 4 — Redesenho como ferramenta de consulta nomenclatural (semana 2–3)

- A avaliação da primeira versão revelou que a interface tabular com gráficos não atendia à principal demanda dos usuários: **resolver rapidamente uma dúvida de nomenclatura**;
- A interface foi completamente redesenhada com foco em busca e resolução de sinonímia;
- Implementação da landing page com exemplos, ficha de nome aceito e card de sinônimo;
- Revisão e melhoria dos critérios de busca por nome popular (campo `nomes_vernaculares`).

### 3.5 Evidências técnicas do desenvolvimento

O desenvolvimento é rastreável por controle de versão Git, com os seguintes commits principais:

| Commit | Descrição |
|---|---|
| `ac7628e` | `feat: adiciona módulo público Flora do Cerrado` |
| `5f17554` | `refactor: redesenha Flora do Cerrado como ferramenta de consulta de nomes` |
| `fb48fef` | `fix: corrige filtro de sinônimos — usa nomes aceitos do Cerrado como referência` |

---

## 4. Resultados Alcançados

### 4.1 Resultados quantitativos

| Indicador | Valor |
|---|---|
| Espécies do Cerrado disponíveis na ferramenta | 12.161 |
| Sinônimos nomenclaturais cobertos | 19.053 |
| Grupos taxonômicos incluídos | Angiospermas e Gimnospermas |
| Acesso necessário para uso | Nenhum (público, sem cadastro) |
| Licença dos dados | CC-BY (JBRJ/REFLORA) |

### 4.2 Resultados qualitativos

**Democratização do acesso ao conhecimento oficial:** os dados do REFLORA/JBRJ são públicos, mas estão disponíveis em formato bruto (planilhas) ou em interface técnica voltada a especialistas (floradobrasil.jbrj.gov.br). O módulo desenvolvido apresenta os mesmos dados em interface simples, em português acessível, sem exigir qualquer cadastro ou conhecimento prévio de sistemas botânicos.

**Resolução de um problema real de campo:** a confusão entre nomes aceitos e sinônimos é um problema documentado em inventários florestais, laudos de licenciamento e trabalhos de conclusão de curso. Um estudante de engenharia florestal que busca *Bowdichia virgilioides* H.B.K. (sucupira-preta) encontra imediatamente sua validade nomenclatural, família, distribuição em MS e os sinônimos *Amerimnum virgilioides* e *Ferreirea spectabilis* — informação que antes exigiria consulta ao portal do JBRJ com conhecimento prévio da interface.

**Integração entre banco nomenclatural e dados de campo:** o link "ver registros no Penomato" cria uma ponte direta entre o conhecimento nominal (o nome da planta) e o conhecimento observacional (registros fotográficos de exemplares reais coletados em campo). Essa integração reforça o ciclo educação → pesquisa → documentação que é a proposta central do Penomato como plataforma de extensão universitária.

**Aprendizagens técnicas:** o desenvolvimento exigiu habilidades integradas de análise de dados, modelagem relacional, programação em Python para ETL, desenvolvimento web em PHP e tomada de decisão sobre design de interface — correspondendo diretamente às competências desenvolvidas na disciplina de Análise Organizacional e Soluções Tecnológicas.

### 4.3 Próximos passos

- Aplicação do módulo como recurso didático em aulas de Dendrologia e Fitossociologia na UEMS, com coleta de feedback dos alunos;
- Adição de fotografias de referência por espécie (integração com Wikimedia Commons via API);
- Mapa interativo de distribuição estadual (Leaflet.js);
- Indicação de status de ameaça (Lista Vermelha MMA / IUCN) para espécies ameaçadas;
- Inclusão de briófitas, pteridófitas e fungos do Cerrado (bases complementares do REFLORA).

---

## 5. Referências

FLORA E FUNGA DO BRASIL. **Flora e Funga do Brasil 2020**. Jardim Botânico do Rio de Janeiro, 2023. Disponível em: https://floradobrasil.jbrj.gov.br. Acesso em: 15 abr. 2026.

MAPBIOMAS. **Relatório Anual do Desmatamento no Brasil — Coleção 8**. MapBiomas, 2023. Disponível em: https://mapbiomas.org. Acesso em: 15 abr. 2026.

MYERS, N. et al. Biodiversity hotspots for conservation priorities. **Nature**, v. 403, n. 6772, p. 853–858, 2000.

REFLORA. **Lista de Espécies da Flora do Brasil — REFLORA**. Jardim Botânico do Rio de Janeiro (JBRJ), 2020. Licença Creative Commons CC-BY. Disponível em: https://reflora.jbrj.gov.br. Acesso em: 15 abr. 2026.

TURLAND, N. J. et al. (ed.). **International Code of Nomenclature for algae, fungi, and plants (Shenzhen Code)**. Koeltz Botanical Books, 2018. (Regnum Vegetabile 159). Disponível em: https://www.iapt-taxon.org/nomen/main.php. Acesso em: 15 abr. 2026.
