# AVALIAÇÃO DO MÓDULO 4 — TESTES E GARANTIA DE QUALIDADE

**Projeto de Extensão:** Programa de Extensão UFMS Digital (95DX7.200525)
**Projeto de Software:** Penomato — Plataforma de Catalogação de Espécies Nativas do Cerrado
**Semestre letivo:** 1º semestre de 2026

---

## 1. Introdução

O Penomato é uma plataforma web de ciência cidadã desenvolvida em PHP e MySQL, com o objetivo de catalogar espécies nativas do Cerrado brasileiro. O sistema permite que colaboradores — estudantes de Engenharia Florestal, Biologia e áreas afins — contribuam com dados morfológicos, imagens e registros de campo de espécies nativas, com cada informação submetida passando obrigatoriamente por revisão e validação especializada antes de ser publicada no acervo público.

O projeto é desenvolvido em parceria entre a UFMS e o Departamento de Engenharia Florestal da UEMS, e encontra-se atualmente na fase de MVP (Produto Mínimo Viável). A plataforma está em construção ativa, com a perspectiva de ser utilizada pelos alunos da UEMS como primeiro grupo de teste real após a conclusão do MVP.

A natureza do sistema — dados científicos botânicos, múltiplos papéis de usuários, fluxo estruturado de validação e vocabulário controlado — impõe desafios particulares e relevantes para a aplicação de VV&T, que serão discutidos ao longo deste relatório.

---

## 2. Aplicação de VV&T no Projeto

### 2.1 Verificação

A verificação no Penomato ocorre em múltiplos níveis, combinando controles técnicos no código com controles estruturais no modelo de negócio.

**Verificação pelo modelo de dados**

O banco de dados utiliza tipos enumerados (ENUM) para todos os campos de características botânicas. Mais de 50 campos morfológicos — como forma da folha, tipo de fruto, simetria floral, textura do caule — aceitam exclusivamente valores do conjunto científico pré-definido. Qualquer tentativa de inserir um valor fora do conjunto é bloqueada diretamente pela camada de banco de dados, independentemente do que o formulário receba. Exemplos:

- `forma_folha`: Lanceolada, Linear, Elíptica, Ovada, Orbicular, Cordiforme, Espatulada, Sagitada, Reniforme, Obovada, Trilobada, Palmada, Lobada
- `parte_planta`: folha, flor, fruto, caule, semente, habito, exsicata_completa, detalhe
- `status` da espécie: sem_dados → dados_internet → descrita → registrada → em_revisao → revisada → publicado

Essa estrutura garante que dados mal formados nunca chegam ao banco, por mais que o usuário tente inserir valores arbitrários.

**Verificação de consistência do fluxo**

O ciclo de vida de cada espécie é controlado por um campo de status com progressão rigorosa. Nenhuma espécie pode ser publicada sem percorrer todas as etapas anteriores. O sistema bloqueia avanços indevidos em nível de aplicação: um exemplar de campo só pode ter imagens submetidas após aprovação do especialista; um artigo só pode ser publicado após revisão do gestor. Cada transição de status é registrada com data, hora e identificação do usuário responsável na tabela `historico_alteracoes`.

**Verificação de integridade de arquivos**

Imagens enviadas ao sistema passam por verificação de tipo MIME real (não apenas extensão do nome do arquivo) e por limite de tamanho (10–15 MB). Somente formatos JPEG e PNG são aceitos. Arquivos que não atendem a esses critérios são rejeitados antes de qualquer gravação.

**Verificação de acesso e autenticação**

Cada página do sistema é protegida por verificação de sessão ativa, com timeout de 30 minutos. O acesso a funcionalidades é restrito por papel de usuário (colaborador, revisor, gestor), verificado em cada controlador. Tentativas de login malsucedidas são bloqueadas após 5 falhas em 15 minutos.

---

### 2.2 Validação

A estratégia de validação do Penomato reconhece um princípio central: **a qualidade científica dos dados não pode ser garantida apenas por software**. Um valor pode ser tecnicamente válido (dentro do enum) mas botanicamente incorreto para aquela espécie específica. Por isso, o sistema foi projetado com validação humana especializada como etapa obrigatória e insubstituível.

**Validação pelo orientador/especialista (modelo de negócio)**

Todo dado inserido no sistema — seja importado de fontes científicas da internet, preenchido manualmente pelo colaborador ou sugerido por inteligência artificial — passa pela revisão de um especialista (revisor ou gestor) antes de ser publicado. Esse especialista é tipicamente um professor ou pesquisador com formação em botânica ou engenharia florestal.

O fluxo é o seguinte:
1. O colaborador insere ou importa os dados da espécie
2. O colaborador registra um exemplar de campo com localização e foto
3. O especialista revisa o exemplar — aprova ou rejeita com justificativa
4. Somente após aprovação o colaborador pode enviar imagens
5. O artigo científico gerado automaticamente passa por revisão editorial
6. O gestor realiza a aprovação final antes da publicação pública

Nenhuma espécie chega ao acervo público sem que um especialista tenha verificado os dados. Isso transforma a validação de uma etapa opcional em uma garantia estrutural do sistema.

**Validação de interface e normalização de dados**

O sistema conta com mecanismo de validação de interface para o formulário de características morfológicas. Quando um valor inserido — seja manualmente ou por integração com IA — não corresponde a nenhuma opção disponível no campo de seleção, o sistema detecta a inconsistência e apresenta ao usuário um modal de resolução, exibindo as opções válidas para escolha. O formulário não é submetido enquanto todos os campos com inconsistências não forem resolvidos. Isso impede que campos sejam silenciosamente deixados em branco ou preenchidos com dados inválidos.

**Contestação de dados**

Qualquer usuário do sistema pode acionar o mecanismo de contestação para sinalizar inconsistências em dados já registrados. A contestação registra o motivo, altera o status da espécie para `contestado` e notifica o gestor para investigação. Esse mecanismo cria um canal contínuo de validação colaborativa mesmo após a publicação.

---

### 2.3 Testes

**Fase atual: testes durante o desenvolvimento**

O MVP encontra-se em construção ativa. Neste momento, os testes são conduzidos pelo próprio programador, que simula sistematicamente o comportamento dos diferentes perfis de usuário ao longo de todo o fluxo: criação de conta, aprovação pelo gestor, escolha de espécie, importação de dados, registro de exemplar, envio de imagens, revisão pelo especialista e publicação.

Esse método — denominado na literatura como teste exploratório ou teste baseado em experiência — é adequado para a fase atual do projeto por ser rápido, flexível e capaz de identificar problemas de usabilidade além de defeitos técnicos. A cada nova funcionalidade desenvolvida, o fluxo completo é percorrido manualmente para detectar regressões.

Complementarmente, são realizados testes de limite e erro: envio de arquivos com formatos inválidos, tentativa de avanço de etapas fora de ordem, inserção de dados fora dos valores aceitos, comportamento com sessão expirada, e falha simulada de serviços externos (busca de imagens, integração com IA).

**Fase futura: teste de campo com usuários reais (UEMS)**

Após a conclusão do MVP, o sistema será utilizado pelos alunos do curso de Engenharia Florestal da UEMS como grupo piloto de teste. Esses alunos realizarão atividades reais de catalogação — inserindo espécies, enviando imagens de campo, preenchendo características morfológicas — sob orientação do professor parceiro.

Essa fase é estratégica: pela primeira vez o sistema será operado por usuários reais, com objetivos reais, em condições reais de uso. Os dados coletados nessa fase — erros encontrados, dificuldades de navegação, campos mal compreendidos, inconsistências nos dados inseridos — formarão a base empírica para o refinamento da plataforma.

**Fase de consolidação: solução definitiva**

A partir dos dados de uso coletados na fase piloto com os alunos da UEMS, será construída a versão definitiva do sistema. Essa versão incorporará:
- Correções identificadas no uso real
- Ajustes de usabilidade baseados no comportamento observado
- Expansão do vocabulário controlado conforme necessidade real dos colaboradores
- Possivelmente, testes automatizados para os fluxos mais críticos

---

## 3. Desafios na Aplicação de VV&T

### 3.1 Complexidade e natureza dos dados botânicos

Este é o desafio central e mais distintivo do Penomato em relação a sistemas de software convencionais.

A corretude de um dado botânico não é verificável por código. Um campo `forma_folha = "Lanceolada"` pode ser um valor tecnicamente válido dentro do enum e ainda assim estar incorreto para aquela espécie específica. A verificação de que a forma foliar descrita corresponde à morfologia real da espécie exige observação botânica especializada — algo que nenhum algoritmo substitui no estado atual da tecnologia.

Isso torna inviável a criação de testes automatizados que validem a qualidade científica do conteúdo. Testes automatizados podem verificar que o sistema aceita apenas os valores do conjunto pré-definido, mas não podem verificar se o valor correto foi escolhido. Essa limitação é estrutural ao domínio científico, não ao software.

A solução adotada foi incorporar a validação científica ao próprio modelo de negócio, tornando a revisão especializada uma etapa técnica obrigatória — não uma recomendação opcional.

### 3.2 Dificuldade de automação de testes

Além da questão do domínio científico, o Penomato apresenta características que tornam a automação de testes complexa:

- **Fluxo multi-etapa com estados**: o caminho completo de uma espécie envolve 7 etapas distintas, cada uma dependente do estado da anterior. Testes automatizados end-to-end precisariam simular múltiplos papéis de usuário em sequência.
- **Integração com serviços externos**: a busca automática de imagens consome APIs externas (iNaturalist, Wikimedia Commons) cujos dados mudam continuamente e cujo comportamento não é controlável em ambiente de teste.
- **Ambiente de hospedagem compartilhada**: o servidor de produção (Hostgator) opera em ambiente compartilhado com restrições de execução que dificultam a implementação de pipelines de CI/CD.
- **Volume reduzido de dados de teste**: na fase atual, o número de espécies e usuários reais ainda é pequeno, o que limita a detecção de problemas de escala.

### 3.3 Divergência entre ambientes

O ambiente de desenvolvimento (XAMPP/Windows local) e o ambiente de produção (Linux/hospedagem compartilhada) diferem em configurações relevantes: permissões de escrita em diretórios, comportamento de chamadas HTTP externas, timeouts de execução PHP e configurações de servidor Apache. Problemas que não aparecem em desenvolvimento podem surgir em produção, exigindo um ciclo adicional de verificação após cada deploy.

### 3.4 Vocabulário controlado em evolução

O conjunto de valores aceitos para as características botânicas foi definido com base na literatura científica atual, mas pode precisar de expansão conforme o sistema encontra espécies com morfologias não cobertas pelo vocabulário existente. Cada expansão exige revisão cuidadosa para manter a consistência com registros anteriores.

---

## 4. Estratégias para Superar Desafios

### 4.1 Validação científica incorporada ao fluxo

Para o desafio insolúvel da automação da qualidade científica, a estratégia é não tentar automatizar o que só um especialista pode julgar. O modelo de negócio foi desenhado de forma que a validação humana não é uma etapa opcional adicionada ao final — ela é uma barreira técnica no meio do fluxo. O software não permite publicação sem revisão especializada, e esse controle é implementado em nível de banco de dados e de aplicação.

### 4.2 Testes exploratórios estruturados por perfil

Na ausência de automação completa, os testes manuais seguem roteiros estruturados por perfil de usuário: o que um colaborador típico faz, o que um revisor faz, o que um gestor faz. Cada roteiro cobre o caminho feliz (fluxo sem erros) e os caminhos de erro (dados inválidos, etapas puladas, sessão expirada). Esse conjunto é executado antes de cada implantação em produção.

### 4.3 Defesa por camadas

O sistema implementa validação em camadas independentes:
1. **Interface**: campos de seleção com opções pré-definidas; modal de resolução para inconsistências
2. **Controlador PHP**: validação de tipo, tamanho e formato antes de qualquer operação
3. **Banco de dados**: ENUMs que bloqueiam valores inválidos a nível de storage
4. **Negócio**: fluxo de status que impede publicação sem aprovação

Uma falha em uma camada é contida pela seguinte, reduzindo a superfície de dados inválidos que podem chegar ao banco.

### 4.4 Fase piloto como teste de aceitação formal

O uso pelos alunos da UEMS será tratado como fase de teste de aceitação (UAT — User Acceptance Testing). O professor orientador atuará como observador, coletando sistematicamente:
- Erros técnicos encontrados pelos usuários
- Dificuldades de compreensão da interface
- Inconsistências nos dados inseridos
- Sugestões de melhoria

Esses dados formarão os requisitos de entrada para a versão definitiva do sistema, transformando o uso real em insumo de qualidade.

### 4.5 Automação planejada para fases futuras

Com a entrada do Núcleo de Práticas em Engenharia de Software da UFMS no projeto, está planejada a introdução gradual de:
- **Testes unitários PHPUnit** para validação dos controladores críticos
- **Testes de integração** cobrindo o fluxo colaborador → revisor → publicação
- **Pipeline de CI/CD** básico para execução automática de testes a cada push

---

## 5. Importância de VV&T para a Qualidade e Confiabilidade do Produto

No contexto do Penomato, qualidade e confiabilidade têm uma dimensão que vai além do software: os dados publicados pelo sistema são utilizados como referência científica. Um registro morfológico incorreto publicado pode propagar erro em pesquisas acadêmicas, laudos de licenciamento ambiental ou material didático utilizado em graduação.

Por isso, as atividades de VV&T cumprem três funções essenciais:

**Integridade científica dos dados:** O sistema garante que apenas dados revisados por especialista chegam ao acervo público. Isso não é uma promessa de interface — é uma restrição técnica implementada no fluxo. A VV&T verifica que essa restrição funciona corretamente e não pode ser contornada.

**Confiança do colaborador:** Quando o sistema detecta uma inconsistência e apresenta ao colaborador as opções corretas em vez de silenciosamente ignorar o problema, ele reforça a percepção de que a plataforma é séria e confiável. Colaboradores que confiam no sistema são mais propensos a contribuir com qualidade.

**Base para expansão:** O sistema foi projetado para escalar — do Cerrado para todos os biomas brasileiros, de uma para várias universidades. Dados confiáveis e bem estruturados desde o início são o fundamento que permite essa expansão sem comprometer a integridade do acervo existente.

---

## 6. Conclusão

A aplicação de VV&T no Penomato reflete uma decisão arquitetural fundamental: em um sistema cujo domínio é científico e cujos dados têm impacto real no conhecimento sobre biodiversidade, a garantia de qualidade não pode ser delegada inteiramente ao software. O modelo de negócio foi projetado para que a validação especializada seja estrutural, não opcional.

O MVP em construção adota testes exploratórios conduzidos pelo desenvolvedor, combinados com controles técnicos em múltiplas camadas. A fase piloto com os alunos da UEMS será o primeiro teste de aceitação real, cujos dados guiarão a construção da versão definitiva. Em fases posteriores, com a entrada do Núcleo de Práticas em Engenharia de Software da UFMS, a cobertura de testes automatizados será progressivamente ampliada.

O resultado esperado é um sistema em que cada espécie publicada representa um dado confiável — verificado tecnicamente pelo software, validado cientificamente pelo especialista, e testado operacionalmente pelo uso real.
