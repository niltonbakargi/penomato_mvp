# Diagrama Entidade-Relacionamento — Penomato

> **Como visualizar:** Cole o bloco abaixo em [https://mermaid.live](https://mermaid.live) para exportar como PNG ou SVG,
> ou instale a extensão **"Markdown Preview Mermaid Support"** no VS Code para ver direto no editor.

---

```mermaid
erDiagram

    USUARIOS {
        int         id                  PK
        varchar     nome
        varchar     email               UK
        varchar     senha_hash
        enum        categoria           "gestor | colaborador | revisor"
        datetime    data_cadastro
        tinyint     ativo
    }

    ESPECIES_ADMINISTRATIVO {
        int         id                  PK
        int         gestor_id           FK
        int         autor_descrita_id   FK
        int         autor_registrada_id FK
        varchar     nome_cientifico
        varchar     nome_popular
        varchar     familia
        enum        status              "sem_dados | dados_internet | descrita | registrada | em_revisao | revisada | publicado | em_contestacao"
        text        descricao_habito
        text        descricao_folha
        text        descricao_flor
        text        descricao_fruto
        text        descricao_caule
        text        descricao_semente
        text        distribuicao_geografica
        text        sinonimias
        text        referencias
        datetime    data_criacao
        datetime    data_descrita
        datetime    data_registrada
    }

    EXEMPLARES {
        int         id                  PK
        int         especie_id          FK
        int         especialista_id     FK
        int         cadastrado_por      FK
        varchar     codigo              UK  "ex: KT001"
        varchar     numero_etiqueta
        varchar     foto_identificacao
        decimal     latitude
        decimal     longitude
        varchar     cidade
        char        estado
        varchar     bioma
        text        descricao_local
        enum        status              "aguardando_revisao | aprovado | rejeitado"
        datetime    data_cadastro
        datetime    data_revisao
        text        motivo_rejeicao
    }

    ESPECIES_IMAGENS {
        int         id                  PK
        int         especie_id          FK
        int         exemplar_id         FK  "nullable"
        int         coletor_id          FK
        enum        parte_planta        "folha | flor | fruto | caule | semente | habito"
        enum        origem              "internet | campo"
        varchar     caminho_imagem
        varchar     nome_original
        int         tamanho_bytes
        varchar     mime_type
        varchar     licenca
        date        data_coleta
        varchar     coletor_nome
        varchar     numero_etiqueta
        text        observacoes_internas
        varchar     status_validacao
        datetime    data_upload
    }

    PARTES_DISPENSADAS {
        int         id                  PK
        int         especie_id          FK
        int         dispensado_por      FK
        enum        parte_planta        "folha | flor | fruto | caule | semente | habito"
        text        motivo
        datetime    data_dispensa
    }

    HISTORICO_ALTERACOES {
        int         id                  PK
        int         especie_id          FK
        int         id_usuario          FK
        varchar     tabela_afetada
        varchar     campo_alterado
        text        valor_anterior
        text        valor_novo
        text        justificativa
        enum        tipo_acao           "insercao | edicao | validacao | contestacao | exclusao"
        datetime    data_alteracao
    }

    FILA_APROVACAO {
        int         id                  PK
        int         especie_id          FK
        int         solicitado_por      FK
        int         resolvido_por       FK  "nullable"
        varchar     tipo_acao
        enum        status              "pendente | aprovado | rejeitado"
        text        observacao
        datetime    data_solicitacao
        datetime    data_resolucao
    }

    %% ══════════════════════════════════════════
    %% RELACIONAMENTOS
    %% ══════════════════════════════════════════

    USUARIOS ||--o{ ESPECIES_ADMINISTRATIVO       : "gerencia (gestor_id)"
    USUARIOS ||--o{ ESPECIES_ADMINISTRATIVO       : "descreve (autor_descrita_id)"
    USUARIOS ||--o{ ESPECIES_ADMINISTRATIVO       : "registra (autor_registrada_id)"

    ESPECIES_ADMINISTRATIVO ||--o{ EXEMPLARES     : "possui"
    USUARIOS ||--o{ EXEMPLARES                    : "orienta (especialista_id)"
    USUARIOS ||--o{ EXEMPLARES                    : "cadastra (cadastrado_por)"

    ESPECIES_ADMINISTRATIVO ||--o{ ESPECIES_IMAGENS   : "contém"
    EXEMPLARES |o--o{ ESPECIES_IMAGENS            : "vincula (campo)"
    USUARIOS ||--o{ ESPECIES_IMAGENS              : "envia (coletor_id)"

    ESPECIES_ADMINISTRATIVO ||--o{ PARTES_DISPENSADAS : "dispensa"
    USUARIOS ||--o{ PARTES_DISPENSADAS            : "autoriza (dispensado_por)"

    ESPECIES_ADMINISTRATIVO ||--o{ HISTORICO_ALTERACOES : "auditado em"
    USUARIOS ||--o{ HISTORICO_ALTERACOES          : "realiza"

    ESPECIES_ADMINISTRATIVO ||--o{ FILA_APROVACAO : "aguarda"
    USUARIOS ||--o{ FILA_APROVACAO                : "solicita"
    USUARIOS |o--o{ FILA_APROVACAO                : "resolve"
```

---

## Legenda de cardinalidade

| Notação Mermaid | Leitura |
|---|---|
| `\|\|` | Exatamente um (obrigatório) |
| `\|o` | Zero ou um (opcional) |
| `o{` | Zero ou muitos |
| `\|{` | Um ou muitos |

---

## Dicionário de dados

### USUARIOS
Centraliza todos os usuários do sistema. O campo `categoria` define o papel e as permissões: o **gestor** coordena as demandas; o **colaborador** produz dados e fotografias; o **revisor** valida exemplares e artigos.

### ESPECIES_ADMINISTRATIVO
Entidade central. Representa uma espécie vegetal de interesse. Contém os atributos fitomorfológicos descritivos por parte da planta e controla o ciclo de vida da documentação pelo campo `status`. Os campos `data_descrita` e `data_registrada` funcionam como marcos: apenas quando ambos estão preenchidos o artigo pode ser gerado.

### EXEMPLARES
Representa um indivíduo físico de campo. Identificado por código único alfanumérico gerado automaticamente (ex: `KT001`) e por etiqueta de alumínio pregada na planta. Precisa ser aprovado pelo especialista (`status = 'aprovado'`) antes de receber fotos de partes. O georeferenciamento (latitude/longitude) é metadado científico de localidade de coleta.

### ESPECIES_IMAGENS
Armazena toda fotografia do sistema, tanto de referência internet quanto de campo. A coluna `exemplar_id` é **nullable**: é nula para imagens de internet, e obrigatoriamente preenchida para imagens de campo (`origem = 'campo'`). Garante que cada foto de campo esteja vinculada a um indivíduo físico aprovado.

### PARTES_DISPENSADAS
Registra formalmente as partes vegetais indisponíveis para fotografia (ex: espécie sem flores fora da época de floração). A dispensa é autorizada pelo gestor e é computada junto com as fotos na verificação de completude — quando todas as partes estão fotografadas **ou** dispensadas, a espécie avança para `registrada`.

### HISTORICO_ALTERACOES
Tabela de auditoria. Toda operação de escrita relevante no sistema gera um registro aqui — sem exceção. Garante rastreabilidade completa: quem inseriu, quem corrigiu, quem validou, quem contestou, e quando.

### FILA_APROVACAO
Gerencia ações que precisam de aprovação assíncrona, como contestações de identificação. Permite que especialistas e gestores resolvam pendências em sua própria janela de tempo, sem bloquear o fluxo de outros colaboradores.

---

## Regras de integridade refletidas no modelo

| Regra | Onde está no modelo |
|---|---|
| Foto de campo exige exemplar aprovado | `ESPECIES_IMAGENS.exemplar_id FK → EXEMPLARES.id` + validação no controller |
| Espécie "descrita" exige 100% dos atributos confirmados | `ESPECIES_ADMINISTRATIVO.data_descrita` só é preenchida quando confirmação é completa |
| Espécie "registrada" exige todas as partes cobertas | Verificação via JOIN entre `ESPECIES_IMAGENS` e `PARTES_DISPENSADAS` |
| Artigo só é gerado com ambas as condições | `data_descrita IS NOT NULL AND data_registrada IS NOT NULL` |
| Exemplar deletado não destrói as fotos | `ESPECIES_IMAGENS.exemplar_id ON DELETE SET NULL` |
| Espécie deletada destrói seus exemplares | `EXEMPLARES.especie_id ON DELETE CASCADE` |
