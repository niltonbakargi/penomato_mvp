# Atividade — Identificação de Problemas e Análise de Requisitos

## 1. Identificação e Definição do Problema

O problema central que motivou o desenvolvimento do Penomato é a **dificuldade de identificação de espécies vegetais nativas em campo por profissionais de Engenharia Florestal**.

Durante décadas, esse desafio era superado com o auxílio dos chamados *mateiros* — profissionais com conhecimento empírico profundo da flora local, capazes de identificar centenas de espécies pela forma da folha, textura do caule ou cheiro da casca. Essa geração está desaparecendo, e o conhecimento que carregavam não foi sistematizado nem transferido.

**Causas identificadas:**

- Ausência de bases de dados digitais com imagens e descrições morfológicas validadas das espécies nativas do Cerrado
- Falta de incentivo estruturado para que pesquisadores e estudantes de Engenharia Florestal contribuam com documentação científica de qualidade
- Processo de documentação botânica fragmentado: dados morfológicos, fotografias de campo e revisão especializada ocorrem em etapas desconectadas e sem rastreabilidade
- Inexistência de um padrão digital equivalente à exsicata física que vincule dados, imagens e autoria de forma auditável

**Impactos:**

- Inventários florestais realizados com identificação imprecisa ou incompleta
- Projetos de recuperação de áreas degradadas comprometidos pela falta de referência de espécies nativas
- Produção científica sobre a flora do Cerrado subutilizada e inacessível ao público
- Incapacidade de treinar modelos de inteligência artificial para identificação automática por falta de dados de qualidade

---

## 2. Definição dos Requisitos da Solução

### Requisitos Funcionais

| # | Requisito |
|---|-----------|
| RF01 | O sistema deve permitir que o gestor cadastre espécies de interesse com nome científico e popular |
| RF02 | O sistema deve permitir que colaboradores insiram dados morfológicos de referência obtidos da internet |
| RF03 | O sistema deve exigir a confirmação atributo por atributo antes de considerar uma espécie "Identificada" |
| RF04 | O sistema deve permitir o upload de fotografias das partes da planta (folha, flor, fruto, caule, semente, hábito) vinculadas a um indivíduo físico identificado por etiqueta |
| RF05 | O sistema deve gerar automaticamente um artigo científico estruturado quando a espécie estiver Identificada e Registrada |
| RF06 | O sistema deve disponibilizar uma fila de revisão para que o especialista aprove ou rejeite artigos com parecer |
| RF07 | O sistema deve publicar automaticamente a ficha da espécie após aprovação do especialista |
| RF08 | O sistema deve permitir contestação de identificações publicadas com registro de motivo |
| RF09 | O sistema deve enviar notificações por e-mail nos eventos de cadastro, aprovação, rejeição e recuperação de senha |
| RF10 | O sistema deve oferecer busca por características morfológicas para consulta pública das fichas |

### Requisitos Não Funcionais

| # | Requisito | Categoria |
|---|-----------|-----------|
| RNF01 | O sistema deve ser acessível via navegador sem instalação de software adicional | Portabilidade |
| RNF02 | O sistema deve ser responsivo para uso em dispositivos móveis em campo | Usabilidade |
| RNF03 | Apenas usuários autenticados e com e-mail confirmado podem contribuir com dados | Segurança |
| RNF04 | O acesso de colaboradores e especialistas deve depender de aprovação do gestor | Segurança |
| RNF05 | O sistema deve ser implementado com tecnologias de código aberto e baixo custo operacional | Viabilidade |
| RNF06 | As imagens enviadas devem ser armazenadas com metadados de autoria, data e licença | Rastreabilidade |
| RNF07 | A interface deve seguir padrão visual consistente definido por design tokens globais | Manutenibilidade |

---

## 3. Planejamento da Solução

A solução adotada foi o **Penomato MVP** — sistema web colaborativo desenvolvido em PHP com banco de dados MySQL, que estrutura todo o ciclo de documentação científica em um único fluxo rastreável:

```
Gestor cadastra espécie
       ↓
Colaborador insere dados morfológicos
       ↓
Colaborador confirma atributos (100% obrigatório)     ←→     Colaborador registra fotos das exsicatas
       ↓                                                               ↓
                    [Espécie Identificada + Registrada]
                                   ↓
                        Geração do artigo científico
                                   ↓
                       Revisão pelo especialista
                                   ↓
                         Publicação pública
```

A escolha por produção científica como incentivo à colaboração — com crédito registrado para cada contribuidor — foi o mecanismo que viabilizou o engajamento voluntário dos profissionais, resolvendo o problema de motivação identificado na pesquisa com usuários.
