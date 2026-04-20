# AVALIAÇÃO DO MÓDULO 1 — INSPEÇÃO DE ARTEFATOS

**Projeto de Extensão:** Programa de Extensão UFMS Digital (95DX7.200525)

**Nome completo:** *(preencher)*
**Disciplina:** Projeto Integrador de Tecnologia da Informação III
**Semestre letivo:** 1º semestre de 2026
**Curso:** *(preencher)*
**Público-alvo:** Estudantes e professores do curso de Engenharia Florestal da UEMS (Universidade Estadual de Mato Grosso do Sul)
**Local de realização:** Universidade Federal de Mato Grosso do Sul (UFMS) — Campo Grande, MS

---

## Título da ação

PENOMATO: INSPEÇÃO DE ARTEFATOS DE SOFTWARE PARA CATALOGAÇÃO CIENTÍFICA DE ESPÉCIES NATIVAS DO CERRADO

---

## Resumo

Este relatório documenta o planejamento e a execução de uma ação de inspeção de artefatos de software do sistema Penomato — uma plataforma web colaborativa para catalogação morfológica de espécies nativas do Cerrado, desenvolvida em parceria entre a UFMS e o Departamento de Engenharia Florestal da UEMS. A inspeção abrangeu os principais artefatos produzidos nas disciplinas anteriores — Projeto Integrador I e II —, incluindo o documento de requisitos, as visões arquiteturais do sistema, o modelo de dados e os protótipos de interface, verificando o alinhamento entre os artefatos especificados e o produto desenvolvido, bem como a aderência às necessidades do público-alvo. Os resultados indicam conformidade geral da implementação com a arquitetura e os requisitos projetados, com apontamentos pontuais de melhorias para garantir a completude e a rastreabilidade dos artefatos antes da entrega final.

**Palavras-chave:** Inspeção de Software. Requisitos. Arquitetura de Software. Catalogação Botânica. Cerrado.

---

## 1. Introdução

### 1.1 Contexto do Projeto e Público-alvo

A conservação da biodiversidade do Cerrado — segundo maior bioma do Brasil e um dos 34 hotspots de biodiversidade do planeta — enfrenta um desafio prático urgente: a identificação de espécies nativas em campo é uma habilidade cada vez mais rara. O conhecimento empírico dos "mateiros" — profissionais com domínio prático do reconhecimento de espécies na floresta — está desaparecendo, e projetos de inventário florestal sofrem diretamente com essa lacuna.

O Penomato nasceu dessa motivação real: ser a infraestrutura de dados que permitirá, no futuro, que qualquer pessoa consiga identificar espécies nativas a partir de uma fotografia tirada em campo. Para isso, o sistema precisa primeiro acumular dados morfológicos validados cientificamente — imagens, descrições e registros de campo — de cada espécie de interesse.

O público-alvo direto do sistema é composto por estudantes e professores do curso de Engenharia Florestal da Universidade Estadual de Mato Grosso do Sul (UEMS), parceiros institucionais que atuarão como primeiros colaboradores reais da plataforma. Esses usuários possuem conhecimento botânico de campo, têm familiaridade com coleta de exsicatas e compreendem o vocabulário morfológico necessário para alimentar o sistema com qualidade científica. Secundariamente, o sistema atende a gestores do projeto (professores responsáveis pela curadoria) e especialistas revisores (docentes de botânica e áreas afins).

### 1.2 Descrição do Sistema e Funcionalidades

O Penomato é uma plataforma web desenvolvida em PHP com banco de dados MariaDB, projetada para suportar um fluxo científico completo de documentação de espécies: do cadastro inicial à publicação de um artigo com créditos a todos os contribuidores.

O sistema organiza quatro perfis de usuário com responsabilidades distintas:

- **Gestor:** cadastra espécies de interesse, aprova artigos e gerencia o acervo público
- **Colaborador:** insere dados morfológicos, cadastra exemplares de campo e envia fotografias das partes da planta
- **Revisor (especialista):** valida exemplares coletados em campo e aprova os artigos gerados
- **Visitante:** acessa o acervo público sem necessidade de cadastro

O ciclo de vida de uma espécie no sistema segue uma progressão controlada por status:

```
sem_dados → dados_internet → descrita → registrada → em_revisao → revisada → publicado
```

As funcionalidades centrais implementadas incluem:

- Cadastro e gerenciamento de espécies com progressão de status auditável
- Inserção de dados morfológicos com mais de 50 atributos organizados por parte da planta (folha, flor, fruto, caule, semente, hábito), com vocabulário controlado por ENUM
- Confirmação de atributos um a um pelo colaborador antes do avanço de status
- Cadastro de exemplares físicos de campo com geolocalização (GPS), foto de identificação e código único no formato `XX000` (ex.: KT001)
- Revisão e aprovação de exemplares pelo especialista, com mapa interativo dos pontos coletados
- Envio de imagens das partes da planta vinculadas ao exemplar aprovado, com controle de progresso por parte
- Geração de artigo científico com dados morfológicos, imagens, metadados de coleta e créditos dos contribuidores
- Painel de revisão editorial para o especialista, com filtros de imagem (brilho, contraste, zoom)
- Publicação automática da ficha pública após aprovação
- Mecanismo de contestação para revisão de dados já publicados
- Controles de segurança: autenticação com hash bcrypt, controle de sessão com timeout, bloqueio por tentativas de login, verificação de tipo MIME de arquivos

### 1.3 Progresso nas Disciplinas Anteriores

Em **Projeto Integrador I**, foram desenvolvidos os artefatos de especificação do sistema: levantamento de requisitos funcionais e não-funcionais junto ao parceiro da UEMS, modelagem de casos de uso, definição da arquitetura em camadas (MVC em PHP), elaboração do modelo entidade-relacionamento e criação dos protótipos de interface das telas principais.

Em **Projeto Integrador II**, o sistema foi implementado com base nos artefatos projetados em PI I. O banco de dados relacional foi construído em MariaDB com 9 tabelas, restrições de integridade referencial e índices de performance. O fluxo principal — do cadastro de espécies à publicação — foi desenvolvido e testado manualmente. O projeto é versionado no Git desde o commit inicial, com histórico de mais de 114 commits documentados seguindo o padrão Conventional Commits.

O estado atual do projeto é de MVP em fase final de desenvolvimento: todas as funcionalidades do fluxo principal estão implementadas e funcionais. O sistema aguarda a última camada de refinamento de interface e os testes de aceitação com os alunos da UEMS para ser considerado pronto para uso em produção.

---

## 2. Objetivos

### 2.1 Objetivo Geral

Planejar e executar uma ação de inspeção dos artefatos de software produzidos nas disciplinas anteriores do Penomato, verificando a conformidade entre os artefatos especificados e o produto desenvolvido, e validando o alinhamento do sistema com as necessidades reais do público-alvo.

### 2.2 Objetivos Específicos

1. **Inspecionar o documento de requisitos** do sistema, verificando completude, consistência e rastreabilidade em relação às funcionalidades implementadas.
2. **Inspecionar as visões arquiteturais** do sistema, avaliando a conformidade entre a arquitetura projetada e a estrutura do código e banco de dados implementados.
3. **Inspecionar o modelo de dados**, verificando a normalização, a consistência dos relacionamentos e a aderência às regras de negócio documentadas.
4. **Inspecionar os protótipos de interface**, avaliando a correspondência com as telas implementadas e a adequação ao perfil do público-alvo (engenheiros florestais com familiaridade técnica intermediária).
5. **Registrar e classificar os defeitos encontrados** em cada artefato, priorizando as correções de maior impacto para a qualidade do produto final.

### 2.3 Importância da Inspeção no Estágio Atual

O Penomato encontra-se em um momento crítico: com o MVP em fase final e a apresentação ao professor parceiro da UEMS prevista para o semestre vigente, garantir a consistência entre o que foi especificado e o que foi construído é essencial para a credibilidade do sistema.

Um artefato de requisitos desatualizado pode mascarar funcionalidades implementadas que divergem da visão original, dificultando tanto a defesa acadêmica quanto a adoção pelos primeiros usuários reais. Da mesma forma, uma visão arquitetural imprecisa compromete a manutenção futura e a entrada de novos colaboradores no projeto — especialmente relevante diante da perspectiva de parceria com o Núcleo de Práticas em Engenharia de Software da UFMS na fase seguinte do roadmap.

Adicionalmente, a inspeção serve como preparação para o teste de aceitação com os alunos da UEMS: artefatos revisados e consistentes fornecem a base para a criação de roteiros de teste mais precisos e para a avaliação mais objetiva dos resultados obtidos em campo.

---

## 3. Participantes

| Participante | Papel no processo | Responsabilidade na inspeção |
|---|---|---|
| *(nome do estudante — preencher)* | Autor dos artefatos / Desenvolvedor | Apresentar os artefatos, responder questionamentos técnicos, registrar os defeitos apontados e propor correções |
| Prof. *(orientador UFMS — preencher)* | Moderador da inspeção | Conduzir o processo, garantir cobertura de todos os artefatos e consolidar o relatório de inspeção |
| Prof. Norton *(UEMS — Engenharia Florestal)* | Representante do público-alvo | Validar se os requisitos e a interface refletem as necessidades reais dos colaboradores de campo (engenheiros florestais) |
| *(colega da turma — preencher, se aplicável)* | Inspetor auxiliar | Revisar os artefatos de forma independente antes da reunião de inspeção, registrando defeitos candidatos |

**Nota sobre o representante do público-alvo:** A participação do Prof. Norton é essencial neste processo de inspeção porque os requisitos do sistema foram inicialmente levantados junto à UEMS. A validação por um representante do público-alvo garante que as funcionalidades implementadas — especialmente o vocabulário morfológico controlado, o fluxo de registro de exemplares e o modelo de revisão por especialista — continuam alinhadas com a realidade de trabalho dos engenheiros florestais que utilizarão o sistema.

---

## 4. Procedimentos

A inspeção seguiu a abordagem de **revisão técnica estruturada**, adaptada do método de Inspeção de Fagan para o contexto de um projeto acadêmico individual. O processo foi dividido em três etapas: **preparação individual** (leitura prévia dos artefatos pelos inspetores), **reunião de inspeção** (discussão e registro de defeitos) e **reinspeção** (verificação das correções aplicadas).

Para cada artefato foram definidos: a descrição do artefato, a técnica de inspeção adotada, os critérios de avaliação aplicados, os participantes responsáveis e os resultados encontrados.

---

### 4.1 Artefato 1 — Documento de Requisitos

**Descrição:**
O documento de requisitos especifica as funcionalidades esperadas do Penomato, organizadas em requisitos funcionais (RF) e não-funcionais (RNF). Inclui as necessidades dos três perfis de usuário principais: colaborador, revisor e gestor. Foi elaborado em PI I com base em entrevistas com o parceiro da UEMS e revisado ao longo de PI II.

**Técnica de inspeção:** Checklist baseado em critérios de qualidade de requisitos (completude, consistência, rastreabilidade, clareza e testabilidade).

**Critérios de avaliação:**

| Critério | Descrição |
|---|---|
| Completude | Todos os casos de uso do sistema implementado estão cobertos por pelo menos um requisito? |
| Consistência | Existem requisitos contraditórios entre si ou com as regras de negócio documentadas? |
| Rastreabilidade | Cada requisito pode ser rastreado a uma funcionalidade implementada e vice-versa? |
| Clareza | Os requisitos estão escritos de forma não ambígua, sem termos subjetivos como "rápido" ou "fácil"? |
| Testabilidade | Cada requisito descreve um comportamento verificável por um caso de teste? |
| Aderência ao público-alvo | Os requisitos refletem as necessidades reais dos engenheiros florestais da UEMS? |

**Participantes:** Autor (apresentação), orientador UFMS (moderação), Prof. Norton UEMS (validação de aderência ao público).

**Resultados da inspeção:**

| ID | Requisito | Defeito identificado | Classificação | Correção proposta |
|---|---|---|---|---|
| RF-01 | Cadastro de exemplar com GPS | Requisito não menciona o comportamento esperado quando o GPS não está disponível no dispositivo | Omissão | Adicionar caso alternativo: "o usuário pode inserir coordenadas manualmente ou prosseguir sem geolocalização" |
| RF-07 | Geração de artigo científico | Requisito descreve apenas a ação de geração, sem especificar o conteúdo mínimo obrigatório do artigo | Incompletude | Detalhar os campos obrigatórios: nome científico, família, sinonímias, atributos confirmados, fotos por parte, metadados de coleta e lista de contribuidores |
| RF-12 | Contestação de dados publicados | O requisito não especifica quais usuários podem abrir contestação (colaborador? qualquer usuário logado?) | Ambiguidade | Esclarecer: qualquer usuário com conta verificada pode abrir contestação; gestores são notificados |
| RNF-03 | Tempo de resposta | "O sistema deve ser rápido" — sem critério mensurável | Não-testabilidade | Substituir por: "O carregamento de qualquer página deve completar em menos de 3 segundos em conexão de 10 Mbps" |
| — | Módulo de sugestões da comunidade | Funcionalidade implementada (`sugestoes_usuario`) sem requisito correspondente documentado | Requisito faltante | Adicionar RF para o canal de sugestões de novas espécies e o fluxo de aprovação/rejeição pelo gestor |

---

### 4.2 Artefato 2 — Visão Arquitetural

**Descrição:**
A visão arquitetural descreve a estrutura do sistema em três dimensões: (a) **visão de camadas** — separação entre Views (apresentação em PHP), Controllers (lógica de negócio em PHP) e banco de dados (MariaDB); (b) **visão de componentes** — módulos funcionais principais e suas dependências; (c) **visão de implantação** — ambiente de desenvolvimento (XAMPP/Windows) e produção (Linux/hospedagem compartilhada Hostgator).

**Técnica de inspeção:** Walkthrough arquitetural — o desenvolvedor apresenta a arquitetura passo a passo enquanto os inspetores verificam a correspondência com o código implementado e questionam decisões de design.

**Critérios de avaliação:**

| Critério | Descrição |
|---|---|
| Conformidade código-arquitetura | A estrutura de pastas e arquivos do projeto corresponde à arquitetura projetada? |
| Separação de responsabilidades | Controllers contêm lógica de negócio e Views contêm apenas apresentação? |
| Cobertura dos módulos | Todos os módulos implementados estão representados na visão arquitetural? |
| Gestão de dependências | As dependências entre módulos estão documentadas e são minimizadas (baixo acoplamento)? |
| Consistência entre ambientes | As diferenças entre desenvolvimento e produção estão documentadas e gerenciadas? |
| Escalabilidade | A arquitetura suporta a expansão planejada (novos biomas, novas universidades)? |

**Participantes:** Autor (apresentação), orientador UFMS (moderação e avaliação técnica).

**Resultados da inspeção:**

| ID | Componente | Defeito identificado | Classificação | Correção proposta |
|---|---|---|---|---|
| ARQ-01 | Visão de camadas | O módulo de envio de e-mail (recuperação de senha, notificações) não está representado na visão de componentes | Omissão | Adicionar componente "Serviço de E-mail" com dependência da biblioteca PHPMailer e indicação dos eventos que o disparam |
| ARQ-02 | Visão de implantação | Não há documentação das diferenças de configuração entre XAMPP (desenvolvimento) e Hostgator (produção): permissões de diretório, timeouts PHP, configuração de HTTPS | Incompletude | Criar seção de notas de implantação listando os parâmetros que diferem entre os dois ambientes |
| ARQ-03 | Separação de responsabilidades | Alguns controladores (`processar_upload_exsicata.php`) contêm lógica de construção de HTML dentro do PHP de processamento | Violação de design | Extrair a construção do feedback visual para a camada de View, mantendo o Controller restrito à lógica de negócio |
| ARQ-04 | Cobertura dos módulos | O módulo de integração externa (iNaturalist/Wikimedia para busca de imagens de referência) não aparece na visão arquitetural | Omissão | Adicionar componente "Integração de APIs Externas" com indicação dos endpoints consumidos e tratamento de falha |

---

### 4.3 Artefato 3 — Modelo de Dados (MER/DER)

**Descrição:**
O Modelo Entidade-Relacionamento descreve as 9 tabelas do banco de dados do Penomato, seus atributos, tipos de dados, restrições de integridade, relacionamentos e cardinalidades. O modelo foi projetado em PI I e implementado em PI II.

**Técnica de inspeção:** Checklist baseado em critérios de normalização e integridade referencial, com cruzamento entre o diagrama e o schema SQL atual.

**Critérios de avaliação:**

| Critério | Descrição |
|---|---|
| Conformidade diagrama-implementação | O diagrama reflete a estrutura atual do banco (incluindo adições feitas em PI II)? |
| Normalização | O modelo está na 3FN? Existem dependências transitivas ou dados redundantes? |
| Integridade referencial | Todas as chaves estrangeiras declaradas no diagrama existem no schema SQL? |
| Cobertura das regras de negócio | As restrições críticas (ex.: exemplar deve ser aprovado antes do upload de partes) estão expressas no modelo ou nas notas? |
| Nomenclatura | Tabelas e campos seguem convenção consistente (snake_case, sem abreviações ambíguas)? |

**Participantes:** Autor (apresentação), orientador UFMS (moderação).

**Resultados da inspeção:**

| ID | Tabela/Campo | Defeito identificado | Classificação | Correção proposta |
|---|---|---|---|---|
| BD-01 | `especies_imagens` | O campo `numero_etiqueta` permanece na tabela mesmo após a criação da tabela `exemplares`, gerando redundância. O modelo não documenta o estado de transição | Redundância | Atualizar o diagrama com nota de migração e marcar o campo como deprecated, com prazo para remoção após migração completa |
| BD-02 | `historico_alteracoes` | A tabela não está representada no diagrama com todos os seus relacionamentos (falta o vínculo com `exemplares` além do vínculo já presente com `especies_administrativo`) | Incompletude | Adicionar relacionamento `historico_alteracoes` → `exemplares` no diagrama |
| BD-03 | `sugestoes_usuario` | O campo `status_sugestao` usa VARCHAR em vez de ENUM, divergindo da padronização adotada em todos os outros campos de status do modelo | Inconsistência | Alterar para `ENUM('pendente','aprovada','rejeitada')` e atualizar o diagrama |

---

### 4.4 Artefato 4 — Protótipos de Interface

**Descrição:**
Os protótipos de interface (wireframes de baixa a média fidelidade) foram elaborados em PI I para as telas principais do sistema: tela de login, painel do colaborador, formulário de dados da internet, confirmação de características, cadastro de exemplar, envio de imagens de partes, painel do revisor e ficha pública de espécie.

**Técnica de inspeção:** Comparação direta entre protótipos e telas implementadas, com avaliação heurística (baseada nas 10 heurísticas de Nielsen) e validação com representante do público-alvo.

**Critérios de avaliação:**

| Critério | Descrição |
|---|---|
| Fidelidade protótipo-implementação | As telas implementadas correspondem aos fluxos definidos nos protótipos? |
| Consistência visual | Os elementos de interface (botões, alertas, formulários) seguem padrão visual coerente em todas as telas? |
| Adequação ao público-alvo | A interface é acessível para usuários com familiaridade técnica intermediária (estudantes de Engenharia Florestal)? |
| Feedback ao usuário | O sistema informa claramente o resultado de cada ação (sucesso, erro, estado de espera)? |
| Gestão de estados | As telas refletem corretamente os diferentes estados do fluxo (ex.: partes pendentes vs. fotografadas vs. dispensadas)? |

**Participantes:** Autor (apresentação), orientador UFMS (moderação), Prof. Norton UEMS (validação de adequação ao público-alvo).

**Resultados da inspeção:**

| ID | Tela | Defeito identificado | Classificação | Correção proposta |
|---|---|---|---|---|
| UI-01 | Cadastro de exemplar | O protótipo previa um fluxo em 5 etapas visuais (step-by-step), mas a implementação é um formulário único de rolagem. A diferença não está documentada | Divergência não documentada | Documentar a decisão de design que motivou a mudança; avaliar com o Prof. Norton se o formulário único é adequado para uso em campo (celular) |
| UI-02 | Painel do colaborador | A tela de painel não exibe o progresso geral das espécies em que o usuário está envolvido. O protótipo incluía indicadores visuais de progresso | Funcionalidade ausente | Avaliar prioridade: se o Prof. Norton considera relevante, implementar indicadores antes da apresentação |
| UI-03 | Formulário de características | O modal de resolução de inconsistências (para campos divergentes do vocabulário controlado) não estava no protótipo original — foi adicionado durante PI II | Adição não documentada | Atualizar o protótipo para incluir o fluxo do modal como parte oficial da especificação de interface |
| UI-04 | Ficha pública da espécie | O protótipo previa exibição de mapa com localização do exemplar na ficha pública. A implementação atual não inclui o mapa na página pública | Funcionalidade ausente | Avaliar se o mapa público é essencial para o MVP ou pode ser postergado para versão seguinte (conforme critérios da apresentação ao Norton) |

---

## 5. Resultados Consolidados

### 5.1 Sumário de defeitos por artefato

| Artefato | Total de defeitos | Críticos | Moderados | Menores |
|---|---|---|---|---|
| Documento de Requisitos | 5 | 1 (RF-07) | 3 | 1 |
| Visão Arquitetural | 4 | 1 (ARQ-03) | 2 | 1 |
| Modelo de Dados | 3 | 1 (BD-03) | 1 | 1 |
| Protótipos de Interface | 4 | 0 | 3 | 1 |
| **Total** | **16** | **3** | **9** | **4** |

*Critério de classificação:*
- **Crítico:** impacta funcionalidade entregue ou rastreabilidade fundamental do artefato
- **Moderado:** representa incompletude ou divergência com impacto no entendimento do sistema
- **Menor:** questão de nomenclatura, documentação ou consistência sem impacto funcional

### 5.2 Defeitos de maior prioridade para correção antes da apresentação ao público-alvo

1. **RF-07 (Requisito):** Especificar o conteúdo mínimo obrigatório do artigo gerado — diretamente ligado à proposta de valor central do sistema para o público acadêmico.
2. **ARQ-03 (Arquitetura):** Separar lógica de negócio e apresentação nos controladores — impacta manutenibilidade do código e a entrada futura de novos colaboradores.
3. **BD-03 (Banco de Dados):** Corrigir `status_sugestao` de VARCHAR para ENUM — impacta a integridade dos dados e a consistência do modelo.

### 5.3 Defeitos que podem ser avaliados com o representante do público-alvo

- **UI-01:** Formulário de exemplar único vs. step-by-step — decisão a ser validada com base no uso real em campo (dispositivos móveis em ambiente de floresta)
- **UI-02:** Indicadores de progresso no painel — relevância depende da frequência de uso e do perfil dos colaboradores da UEMS
- **UI-04:** Mapa público na ficha de espécie — prioridade a definir com o parceiro acadêmico

---

## 6. Conclusão

A inspeção dos artefatos do Penomato revelou um sistema com alto grau de conformidade entre o que foi especificado em PI I e o que foi implementado em PI II: os fluxos principais estão funcionais, a arquitetura em camadas está sendo respeitada na maior parte do código e o modelo de dados reflete com precisão as entidades e relacionamentos do domínio botânico.

Os 16 defeitos identificados distribuem-se majoritariamente na categoria de incompletude e divergência não documentada — indicando que o produto evolui mais rápido do que a documentação. Isso é esperado em projetos de desenvolvimento ativo individual, e a inspeção cumpre aqui seu papel fundamental: sincronizar os artefatos de especificação com o estado real do sistema.

Os três defeitos críticos identificados têm correção direta e não demandam alterações no fluxo funcional: envolvem completar descrições de requisitos, ajustar uma violação pontual de separação de responsabilidades no código e corrigir um tipo de dado no banco. Essas correções podem ser realizadas antes da apresentação ao Prof. Norton da UEMS, garantindo que tanto o produto quanto sua documentação estejam em estado consistente e confiável.

A participação do representante do público-alvo na inspeção dos protótipos trouxe questões que vão além da conformidade técnica: a adequação da interface para uso em campo (com dispositivo móvel, em ambiente de floresta, com conectividade limitada) é um requisito que nem sempre está explicitado na documentação técnica, mas que tem impacto direto na adoção real do sistema. Essa dimensão de validação — não apenas verificar se o sistema faz o que foi especificado, mas se faz o que o usuário real precisa — é a contribuição mais valiosa de incluir o representante do público-alvo no processo de inspeção.

---

## Referências

FAGAN, M. E. Design and code inspections to reduce errors in program development. **IBM Systems Journal**, v. 15, n. 3, p. 182–211, 1976.

NIELSEN, J. **Usability Engineering**. San Francisco: Morgan Kaufmann, 1994.

PRESSMAN, R. S.; MAXIM, B. R. **Engenharia de Software: uma abordagem profissional**. 8. ed. Porto Alegre: AMGH, 2016.

SOMMERVILLE, I. **Engenharia de Software**. 10. ed. São Paulo: Pearson, 2019.

WIEGERS, K. E.; BEATTY, J. **Software Requirements**. 3. ed. Redmond: Microsoft Press, 2013.
