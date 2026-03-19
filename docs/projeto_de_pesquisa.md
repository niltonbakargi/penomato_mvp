# PROJETO DE PESQUISA

---

**Título:**
**Penomato: Sistema Web Colaborativo para Documentação Digital de Exsicatas e Geração de Artigos Científicos sobre Espécies Vegetais**

---

**Autor(es):** _[nome(s) do(s) autor(es)]_
**Orientador:** _[nome do orientador]_
**Instituição:** _[nome da instituição]_
**Curso / Programa:** _[nome do curso ou programa de pós-graduação]_
**Data:** Março de 2026

---

## SUMÁRIO

1. Introdução
2. Problema de Pesquisa
3. Justificativa
4. Objetivos
5. Referencial Teórico
6. Metodologia
7. Resultados Esperados
8. Cronograma
9. Referências

---

## 1. INTRODUÇÃO

A documentação de espécies vegetais é uma atividade fundamental para a conservação da biodiversidade, para a pesquisa taxonômica e para a produção do conhecimento botânico. Historicamente, essa documentação se dá por meio da coleta e herborização de exsicatas — amostras de plantas prensadas, secas e montadas em cartões — armazenadas em herbários físicos distribuídos por instituições de pesquisa ao redor do mundo.

Com o avanço das tecnologias de informação e comunicação, surgiram iniciativas de digitalização de acervos herborizados, como o SpeciesLink, o GBIF (Global Biodiversity Information Facility) e o sistema JABOT da rede JBRJ. No entanto, tais iniciativas concentram-se majoritariamente na digitalização do que já existe em papel, e não na construção de fluxos digitais nativos desde a coleta em campo.

No contexto de grupos de pesquisa em botânica com recursos limitados — especialmente em universidades do interior do Brasil —, há uma lacuna entre a coleta de campo e a produção de documentação científica publicável. Colaboradores (alunos de graduação e pós-graduação) coletam dados e fotografias em campo, mas o processo de organização, validação, vinculação ao indivíduo físico e produção do texto científico final é fragmentado, informal e dependente de ferramentas genéricas como planilhas, grupos de mensagens e e-mail.

Este projeto propõe o desenvolvimento e a avaliação do **Penomato**, um sistema web colaborativo que integra o ciclo completo de documentação de espécies vegetais: do cadastro da demanda pelo gestor, passando pela inserção de dados de referência, registro fotográfico de campo vinculado a exemplares físicos identificados, até a geração e publicação de artigos científicos estruturados, com revisão por especialista.

---

## 2. PROBLEMA DE PESQUISA

Como um sistema web colaborativo pode estruturar e automatizar o fluxo de documentação digital de espécies vegetais — desde o cadastro de exemplares de campo até a publicação de artigos científicos —, reduzindo a fragmentação do processo e garantindo a rastreabilidade e integridade dos dados produzidos por equipes distribuídas?

---

## 3. JUSTIFICATIVA

### 3.1 O Cerrado como contexto urgente

O Cerrado é o segundo maior bioma brasileiro, ocupando aproximadamente 2 milhões de km² e abrangendo grande parte do estado de Mato Grosso do Sul. É reconhecido internacionalmente como um dos 36 hotspots mundiais de biodiversidade, concentrando cerca de 12.356 espécies vegetais nativas, das quais aproximadamente 4.400 são endêmicas — ou seja, não existem em nenhum outro lugar do planeta (Myers et al., 2000; Flora e Funga do Brasil, 2023).

Apesar dessa riqueza excepcional, o Cerrado é também o bioma brasileiro que mais perdeu cobertura original nas últimas décadas. Estima-se que apenas 19,8% de sua vegetação nativa permaneça intacta (MapBiomas, 2023). A velocidade de conversão do habitat — para pastagens, monoculturas e expansão urbana — supera em muito o ritmo da documentação científica das espécies que o habitam. Numerosas espécies correm o risco de desaparecer antes mesmo de serem adequadamente descritas e registradas pela ciência.

Nesse cenário, ferramentas que acelerem e organizem o processo de documentação botânica têm impacto direto sobre a capacidade da comunidade científica de conhecer, proteger e divulgar a biodiversidade do Cerrado.

### 3.2 A lacuna entre o conhecimento científico e a educação ambiental

Um problema frequentemente negligenciado não está apenas na falta de documentação científica, mas na inacessibilidade do conhecimento que já existe. Descrições fitomorfológicas — que tratam da forma, estrutura e características das partes vegetais — são produzidas em linguagem técnica, publicadas em periódicos de acesso restrito e raramente chegam ao público não especializado: estudantes do ensino básico, educadores ambientais, técnicos agrícolas, gestores de unidades de conservação e cidadãos em geral.

O resultado é uma paradoxo: o Brasil possui a flora mais diversa do mundo, mas a maioria de sua população não consegue nomear, identificar ou compreender a morfologia das espécies que crescem em seu entorno. A educação ambiental, para ser efetiva, precisa de conteúdo acessível, visual e referenciado — e esse conteúdo sistematicamente falta para a flora do Cerrado.

O Penomato é projetado desde sua concepção para preencher essa lacuna. O sistema não apenas organiza o fluxo interno de produção científica, mas tem como horizonte de desenvolvimento tornar-se uma **plataforma pública de educação ambiental**, onde fichas de espécies com descrições fitomorfológicas validadas por especialistas, fotografias de campo organizadas por parte da planta e metadados de localização sejam acessíveis de forma livre e compreensível a qualquer pessoa com acesso à internet.

### 3.3 Lacuna no processo de documentação em grupos de pesquisa

O processo de documentação botânica em grupos de pesquisa universitários — especialmente em instituições com recursos limitados, como é o caso de grande parte das universidades públicas brasileiras do interior — apresenta problemas recorrentes que comprometem tanto a qualidade quanto a velocidade da produção científica:

- **Perda de rastreabilidade:** fotografias de partes da planta (folha, flor, fruto, caule, semente, hábito) são registradas sem vínculo explícito ao indivíduo físico que as originou. Quando diferentes alunos fotografam a mesma espécie em momentos distintos, não há como garantir que as imagens de uma mesma coleção são do mesmo espécime, o que compromete a validade científica do registro;
- **Fragmentação da colaboração:** dados morfológicos, imagens e metadados de coleta são produzidos por pessoas diferentes, em momentos diferentes, por meio de ferramentas genéricas como planilhas eletrônicas, grupos de aplicativos de mensagens e e-mail. Não há um repositório único que centralize, versione e atribua autoria a cada contribuição;
- **Ausência de fluxo de validação formal:** não há etapa estruturada de revisão dos dados entre a coleta e a produção do texto científico. Erros de identificação são frequentemente percebidos apenas no momento da escrita ou, pior, após a publicação;
- **Retrabalho na produção textual:** cada artigo é escrito do zero, sem aproveitamento sistemático dos dados já organizados durante o processo de coleta e confirmação, gerando esforço redundante e inconsistências entre versões.

Esses problemas não são exclusivos de um grupo específico: refletem a ausência de ferramentas projetadas para o fluxo de trabalho real de equipes de botânica de campo em formação acadêmica.

### 3.4 Contexto institucional e parceria interdisciplinar

Este trabalho se insere em um contexto de colaboração entre o curso de Tecnologia em _[nome do curso]_ da Universidade Federal de Mato Grosso do Sul (UFMS) e o Departamento de Botânica do curso de Engenharia Florestal da Universidade Estadual de Mato Grosso do Sul (UEMS), instituição com atuação reconhecida na pesquisa e conservação da flora do Cerrado sul-mato-grossense.

Essa parceria interdisciplinar é em si uma contribuição relevante do trabalho: demonstra que a tecnologia da informação pode produzir artefatos de software com impacto direto em domínios científicos distintos, respondendo a demandas reais identificadas por especialistas da área. O desenvolvimento do Penomato parte de necessidades concretas observadas na rotina de trabalho do grupo parceiro, e sua avaliação será realizada com os mesmos usuários que motivaram sua concepção.

### 3.5 Insuficiência das soluções existentes

As principais plataformas digitais voltadas à flora brasileira — como o SpeciesLink (CRIA), o GBIF e o Sistema de Informação sobre a Biodiversidade Brasileira (SiBBr) — foram projetadas para a **interoperabilidade de dados já existentes**: recebem registros de herbários já processados e os tornam consultáveis de forma padronizada. Não se propõem a mediar o processo de produção desses dados.

Ferramentas de gestão de herbários como o BRAHMS (Oxford) e o Specify (iDigBio) são sistemas complexos, instalados localmente, com curva de aprendizado elevada e voltados a herbários institucionais consolidados — não a grupos de pesquisa em formação que precisam de agilidade e colaboração remota.

O Penomato ocupa um espaço distinto: é um sistema web colaborativo nativo, acessível por navegador sem instalação, projetado para conduzir equipes distribuídas desde o cadastro da demanda pelo gestor até a publicação da ficha pública da espécie, com cada etapa validada e rastreada. Sua arquitetura orientada a fluxo de trabalho e seu foco na produção de conteúdo educativo acessível constituem diferenciais não atendidos pelas soluções disponíveis.

### 3.6 Contribuição da Tecnologia da Informação

Do ponto de vista da área de T.I., este trabalho contribui com:

- A aplicação de conceitos de engenharia de software (modelagem de fluxo de trabalho, controle de estados, arquitetura MVC, segurança em aplicações web) a um domínio científico real e socialmente relevante;
- O desenvolvimento de um modelo de dados original para documentação fotográfica vinculada a exemplares físicos georeferenciados;
- A avaliação empírica da usabilidade do sistema com usuários reais, contribuindo para a literatura de Interação Humano-Computador aplicada a sistemas científicos colaborativos;
- A demonstração de que tecnologias abertas e de baixo custo (PHP, MySQL, Leaflet.js, OpenStreetMap) são suficientes para construir soluções com impacto científico e educacional relevante em contextos universitários com infraestrutura limitada.

---

## 4. OBJETIVOS

### 4.1 Objetivo Geral

Desenvolver um sistema web colaborativo — denominado Penomato — para documentação digital de exsicatas e descrições fitomorfológicas de espécies vegetais nativas do Cerrado, integrando o fluxo de produção científica desde o cadastro de exemplares de campo até a publicação de fichas de espécies acessíveis como recurso de educação ambiental.

### 4.2 Objetivos Específicos

1. **Modelar e implementar** uma base de dados relacional que vincule registros fotográficos de partes vegetais a exemplares físicos individualizados e georeferenciados, garantindo a rastreabilidade e integridade científica de cada coleção;

2. **Desenvolver** fluxos de trabalho colaborativos com controle de estados e permissões diferenciadas por papel de usuário — gestor, colaborador e especialista —, organizando as etapas de inserção, validação e publicação dos dados de cada espécie;

3. **Implementar** o módulo de cadastro, aprovação e visualização geográfica de exemplares, permitindo que especialistas revisem e aprovem registros de campo antes que fotos das partes vegetais sejam vinculadas ao sistema;

4. **Construir** o módulo de confirmação de atributos fitomorfológicos, possibilitando que colaboradores verifiquem e corrijam, atributo a atributo, as características morfológicas inseridas a partir de fontes de referência;

5. **Desenvolver** o mecanismo de geração automatizada de artigos científicos estruturados a partir dos dados validados, compondo descrições fitomorfológicas com metadados de coleta, galeria de exsicatas e lista de contribuidores;

6. **Implementar** o fluxo de revisão especializada e publicação das fichas de espécies, com histórico de alterações, rastreabilidade de decisões e contestação pós-publicação;

7. **Projetar** a camada pública do sistema como recurso de educação ambiental, tornando as fichas de espécies do Cerrado acessíveis à consulta livre, com linguagem e apresentação adequadas ao público não especializado.

---

## 5. REFERENCIAL TEÓRICO

### 5.1 Exsicatas e Herbários Digitais

A exsicata é o registro físico fundamental da botânica sistemática. Consiste em um espécime vegetal coletado, prensado, seco e montado em cartão, acompanhado de etiqueta com dados de coleta (coletor, data, localidade, coordenadas, número de coleção). Herbários físicos funcionam como repositórios permanentes desses registros, servindo de base para estudos taxonômicos, filogenéticos e de distribuição geográfica.

A digitalização de herbários avançou consideravelmente nas últimas décadas. Iniciativas como o GBIF (Global Biodiversity Information Facility) e o SpeciesLink (CRIA, Brasil) consolidaram padrões de dados abertos para registros de ocorrência, baseados no formato Darwin Core. No entanto, o foco dessas plataformas é a interoperabilidade de dados já existentes, não a construção do fluxo de produção desses dados.

A exsicata digital proposta neste trabalho preserva os metadados essenciais da exsicata tradicional (coletor, data, localidade, coordenadas, número de etiqueta) e acrescenta o registro fotográfico sistemático de cada parte da planta, vinculado ao indivíduo físico específico por meio de um código único gerado pelo sistema.

### 5.2 Sistemas Colaborativos de Informação Científica

Sistemas colaborativos de informação (CSCW — Computer-Supported Cooperative Work) são projetados para mediar o trabalho de grupos distribuídos que compartilham um objetivo comum. Em contextos científicos, esses sistemas precisam equilibrar abertura à contribuição com rigor na validação dos dados produzidos (Olson & Olson, 2000).

O Penomato adota um modelo de colaboração assíncrona e assimétrica: contribuidores produzem dados em ritmos e momentos distintos; especialistas revisam em janelas de tempo próprias; o gestor coordena a demanda. A rastreabilidade de quem produziu o quê e quando é um requisito central do sistema, atendido pelo módulo de histórico de alterações.

### 5.3 Fluxos de Trabalho e Controle de Status

A modelagem de fluxos de trabalho (workflow) é um campo estabelecido da engenharia de software e sistemas de informação. O modelo de estados finitos é amplamente utilizado para representar o ciclo de vida de documentos e entidades em sistemas colaborativos (van der Aalst, 2011). No Penomato, tanto a espécie quanto o exemplar possuem máquinas de estado explícitas que governam quais operações estão disponíveis em cada momento, prevenindo inconsistências nos dados.

### 5.4 Georeferenciamento e Visualização Cartográfica Web

O uso de coordenadas geográficas como metadado de coleta é prática consolidada em botânica de campo. A integração de mapas interativos em sistemas web, viabilizada por bibliotecas como o Leaflet.js em conjunto com bases cartográficas abertas como o OpenStreetMap, permite visualizar a distribuição espacial dos exemplares registrados sem dependência de APIs comerciais (Leaflet, 2023).

O georeferenciamento dos exemplares no Penomato cumpre dupla função: serve como dado científico de localidade de coleta e viabiliza futuros incrementos como análise de distribuição geográfica e verificação de sobreposição de coletas.

### 5.5 Geração Automatizada de Documentos Científicos

A geração de texto científico a partir de dados estruturados é uma área em expansão, especialmente em bioinformática e taxonomia computacional. Sistemas como o DELTA (DEscription Language for TAxonomy) e o Scratchpads (Natural History Museum, Londres) exploram a geração de descrições morfológicas a partir de matrizes de caracteres. O Penomato adota abordagem similar, mais simples e focada: utiliza os atributos morfológicos validados pelo especialista como fonte estruturada para composição do texto do artigo, com template pré-definido ajustável pelo revisor.

---

## 6. METODOLOGIA

### 6.1 Natureza e Paradigma da Pesquisa

Esta pesquisa é de natureza aplicada, com abordagem quali-quantitativa. O paradigma metodológico adotado é a **Design Science Research (DSR)**, proposta por Hevner et al. (2004) como framework adequado para pesquisas em Sistemas de Informação que produzem artefatos tecnológicos como contribuição científica.

Na DSR, o artefato — neste caso o sistema Penomato — não é apenas o produto do trabalho, mas o objeto de investigação. A pesquisa se organiza em torno de três ciclos interdependentes (Hevner, 2007):

- **Ciclo de relevância:** o problema é extraído do contexto real de uso — neste trabalho, a rotina de um grupo de pesquisa botânica em parceria com a UEMS — e os requisitos do sistema são definidos a partir dessa necessidade concreta;
- **Ciclo de rigor:** o desenvolvimento apoia-se em conhecimentos estabelecidos da engenharia de software, modelagem de dados, sistemas colaborativos e interação humano-computador;
- **Ciclo de design:** o artefato é construído, avaliado em contexto real e refinado iterativamente com base nos resultados da avaliação.

A DSR exige que o artefato seja avaliado em condições de uso real, o que neste trabalho se dará por meio de testes de usabilidade com os usuários do grupo de pesquisa parceiro.

### 6.2 Etapas da Pesquisa

O trabalho é organizado em quatro etapas sequenciais, descritas a seguir.

#### Etapa 1 — Levantamento de requisitos

O levantamento de requisitos foi conduzido em colaboração com docentes e discentes do curso de Engenharia Florestal da UEMS, por meio de reuniões de elicitação e observação da rotina de trabalho do grupo de pesquisa. Essa etapa identificou os atores do sistema (gestor, colaborador e especialista), os fluxos de trabalho existentes e suas limitações, e os requisitos funcionais e não funcionais que orientaram o projeto do sistema.

Os requisitos foram organizados em histórias de usuário e validados com os stakeholders antes do início do desenvolvimento.

#### Etapa 2 — Projeto e desenvolvimento do sistema

O desenvolvimento segue o modelo **iterativo e incremental**, com entregas organizadas em módulos funcionais. A arquitetura adotada é o padrão **MVC (Model-View-Controller)**, que separa a lógica de negócio da apresentação e do acesso a dados, favorecendo a manutenibilidade e a evolução do sistema.

Os módulos foram desenvolvidos na seguinte ordem, respeitando as dependências entre eles:

**Módulo 1 — Autenticação e papéis de usuário**
Controle de sessão, login, cadastro de usuários e separação de permissões por categoria (gestor, colaborador, especialista). Base sobre a qual todos os demais módulos operam.

**Módulo 2 — Gestão de espécies**
Cadastro de espécies de interesse pelo gestor, com nome científico, nome popular e atribuição de colaborador responsável. Controle de status da espécie ao longo de todo o ciclo de documentação.

**Módulo 3 — Inserção de dados morfológicos**
Formulário colaborativo para preenchimento de atributos fitomorfológicos (folha, flor, fruto, caule, semente, hábito, distribuição, sinonímias, referências) a partir de fontes de referência. Dados inseridos com aprovação automática, registrados com autoria e data.

**Módulo 4 — Confirmação de atributos (identificação)**
Interface de revisão atributo a atributo, na qual colaboradores confirmam ou corrigem cada característica morfológica inserida. A espécie somente avança para o status "identificada" quando todos os atributos forem confirmados ou corrigidos — sem exceção.

**Módulo 5 — Cadastro e revisão de exemplares**
Cadastro de exemplares físicos de campo com localização georeferenciada (latitude/longitude capturadas via GPS do navegador ou inseridas manualmente), foto de identificação do indivíduo, número de etiqueta física e vinculação a especialista orientador. O sistema gera automaticamente um código único para cada exemplar (formato alfanumérico). O especialista revisa e aprova ou rejeita o exemplar antes que qualquer foto de parte seja aceita, garantindo a integridade da coleção.

**Módulo 6 — Registro fotográfico de exsicatas por partes**
Upload incremental de fotografias de cada parte da planta (folha, flor, fruto, caule, semente, hábito), obrigatoriamente vinculadas a um exemplar aprovado. O sistema monitora a completude por exemplar e, quando todas as partes estiverem fotografadas ou formalmente dispensadas pelo gestor, atualiza automaticamente o status da espécie para "registrada".

**Módulo 7 — Geração e revisão do artigo científico**
Geração automatizada de artigo estruturado a partir dos dados validados: descrição fitomorfológica por parte, metadados dos exemplares, galeria de exsicatas, referências e créditos dos contribuidores. O artigo é encaminhado para fila de revisão do especialista, que aprova com parecer ou rejeita com motivo registrado. A aprovação dispara a publicação automática.

**Módulo 8 — Publicação pública e contestação**
Ficha pública da espécie, acessível sem autenticação, com descrição fitomorfológica, galeria de exsicatas, mapa de ocorrência e créditos — projetada como recurso de educação ambiental. Mecanismo de contestação pós-publicação com registro de motivo, revisão pelo especialista e possibilidade de reedição.

**Stack tecnológico adotado:**

| Camada | Tecnologia | Justificativa |
|---|---|---|
| Backend | PHP 8.2 com PDO | Amplamente suportado em hospedagens compartilhadas; sem dependência de frameworks pesados |
| Banco de dados | MySQL / MariaDB 10.4 | Padrão de mercado, disponível no XAMPP e na maioria dos servidores institucionais |
| Frontend | HTML5, CSS3, Bootstrap 5.3, JavaScript ES6 | Responsividade nativa, sem dependência de build tools |
| Mapas | Leaflet.js + OpenStreetMap | Solução de código aberto, sem custo de API, funciona offline em redes institucionais |
| Servidor local | XAMPP (Apache) | Ambiente de desenvolvimento idêntico ao de produção em servidores compartilhados |
| Controle de versão | Git | Rastreabilidade do desenvolvimento e facilidade de colaboração |

A escolha por tecnologias consolidadas, abertas e de baixo custo operacional reflete diretamente o objetivo de que o sistema seja implantável em instituições públicas de ensino e pesquisa sem necessidade de infraestrutura especializada ou licenças comerciais.

#### Etapa 3 — Avaliação de usabilidade

Após a conclusão do MVP, o sistema será avaliado com usuários reais pertencentes ao público-alvo definido: colaboradores (alunos de graduação e pós-graduação em Engenharia Florestal) e especialistas (docentes com atuação em botânica do Cerrado), recrutados junto à UEMS.

A avaliação combinará dois instrumentos complementares:

**a) Teste de usabilidade com protocolo think-aloud**
Os participantes realizarão tarefas representativas dos fluxos principais do sistema — cadastrar um exemplar, enviar fotos de partes, revisar um exemplar e consultar a ficha pública de uma espécie — enquanto verbalizam seus pensamentos em voz alta. As sessões serão registradas e analisadas para identificação de problemas de usabilidade, pontos de confusão e sugestões emergentes.

**b) Questionário SUS (System Usability Scale)**
Ao final de cada sessão, os participantes responderão ao questionário SUS (Brooke, 1996), composto por 10 afirmações em escala Likert de 5 pontos. O SUS é um instrumento validado e amplamente utilizado na literatura de IHC, que produz um escore entre 0 e 100, interpretável em faixas qualitativas (inaceitável, marginal, bom, excelente). Escores acima de 68 são considerados acima da média para sistemas em geral (Bangor et al., 2008).

O número mínimo de participantes por perfil será de 5, conforme referencial de Nielsen (1993), que demonstra que esse número é suficiente para identificar aproximadamente 85% dos problemas de usabilidade em testes com protocolo think-aloud.

#### Etapa 4 — Análise, refinamento e documentação

Os dados quantitativos do SUS serão analisados com estatística descritiva (média, desvio padrão, distribuição dos escores por item). Os dados qualitativos das sessões think-aloud serão submetidos à análise de conteúdo temática, organizando os achados em categorias de problemas por módulo e por perfil de usuário.

Os resultados orientarão uma iteração de refinamento do sistema, corrigindo os problemas identificados antes da versão final entregue como produto do TCC. Após o refinamento, o sistema será documentado com especificação de requisitos, diagrama entidade-relacionamento, diagramas de fluxo de trabalho e manual de implantação.

---

## 7. RESULTADOS ESPERADOS

Ao final da pesquisa, espera-se:

1. **Artefato de software** — o sistema Penomato em versão MVP funcional, com todos os módulos do ciclo de documentação implementados e testados;
2. **Modelo de dados** — esquema de banco de dados documentado para exsicatas digitais colaborativas com rastreabilidade por exemplar;
3. **Avaliação empírica** — relatório de usabilidade com índice SUS e análise qualitativa das sessões de teste;
4. **Documentação técnica** — especificação de requisitos, diagramas de fluxo e manual de implantação;
5. **Artigo científico** — relato do desenvolvimento e avaliação do sistema, submetido a periódico ou conferência da área de Sistemas de Informação ou Informática na Educação.

Como resultado secundário, espera-se que o sistema possa ser adotado pelo grupo de pesquisa parceiro como ferramenta real de trabalho, validando sua utilidade além do contexto acadêmico.

---

## 8. CRONOGRAMA

| Etapa | Descrição | Mês |
|---|---|---|
| 1 | Revisão bibliográfica e refinamento do referencial teórico | 1–2 |
| 2 | Especificação de requisitos e modelagem do banco de dados | 1–2 |
| 3 | Desenvolvimento — Módulos 1 e 2 (espécies e dados morfológicos) | 2–3 |
| 4 | Desenvolvimento — Módulo 3 (exemplares e revisão) | 3–4 |
| 5 | Desenvolvimento — Módulos 4 e 5 (fotos e artigos) | 4–5 |
| 6 | Desenvolvimento — Módulo 6 (publicação e contestação) | 5–6 |
| 7 | Testes internos e refinamento | 6–7 |
| 8 | Avaliação de usabilidade com usuários reais | 7–8 |
| 9 | Análise dos resultados e iteração de refinamento | 8–9 |
| 10 | Escrita do artigo científico e documentação final | 9–11 |
| 11 | Revisão final, defesa e submissão do artigo | 11–12 |

---

## 9. REFERÊNCIAS

_[A ser completado conforme as referências citadas no texto]_

FLORA E FUNGA DO BRASIL. Jardim Botânico do Rio de Janeiro. Disponível em: http://floradobrasil.jbrj.gov.br. Acesso em: mar. 2026.

GBIF — Global Biodiversity Information Facility. Disponível em: https://www.gbif.org. Acesso em: mar. 2026.

HEVNER, A. R. et al. Design science in information systems research. **MIS Quarterly**, v. 28, n. 1, p. 75–105, 2004.

LEAFLET. An open-source JavaScript library for mobile-friendly interactive maps. Disponível em: https://leafletjs.com. Acesso em: mar. 2026.

OLSON, G. M.; OLSON, J. S. Distance matters. **Human-Computer Interaction**, v. 15, n. 2–3, p. 139–178, 2000.

VAN DER AALST, W. M. P. **Process Mining: Discovery, Conformance and Enhancement of Business Processes**. Berlin: Springer, 2011.

WOHLIN, C. et al. **Experimentation in Software Engineering**. Berlin: Springer, 2012.

_[Adicionar referências sobre: Darwin Core, SpeciesLink/CRIA, SUS (Brooke 1996), herbários digitais, taxonomia computacional]_

---

**Documento em construção — versão 0.1 — março/2026**
