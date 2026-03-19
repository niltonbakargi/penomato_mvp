# Diagrama de Relacionamentos — Penomato

> **Como visualizar:** Cole o bloco abaixo em [https://mermaid.live](https://mermaid.live) para exportar como PNG ou SVG,
> ou instale a extensão **"Markdown Preview Mermaid Support"** no VS Code para ver direto no editor.

---

## Visão geral do sistema

```mermaid
graph TD
    subgraph USUARIOS["👤 USUÁRIOS"]
        G["Gestor"]
        C["Colaborador"]
        E["Especialista / Revisor"]
    end

    subgraph ESPECIES["🌿 ESPÉCIE"]
        SP["Espécie\n(especies_administrativo)"]
    end

    subgraph MORFOLOGIA["📋 DADOS MORFOLÓGICOS"]
        AM["Atributos\nFitomorfológicos\n(campos de especie)"]
        PD["Partes Dispensadas\n(partes_dispensadas)"]
    end

    subgraph EXEMPLARES["🌱 EXEMPLARES DE CAMPO"]
        EX["Exemplar\n(exemplares)"]
    end

    subgraph IMAGENS["📷 IMAGENS"]
        II["Imagem Internet\n(especies_imagens\norigem=internet)"]
        IC["Imagem Campo\n(especies_imagens\norigem=campo)"]
    end

    subgraph FLUXO["📄 FLUXO DE PUBLICAÇÃO"]
        FA["Fila de Aprovação\n(fila_aprovacao)"]
        HA["Histórico\n(historico_alteracoes)"]
    end

    %% Gestor
    G -->|"cadastra"| SP
    G -->|"autoriza dispensa"| PD
    G -->|"dispara geração\ndo artigo"| FA

    %% Colaborador
    C -->|"insere dados\nmorfológicos"| AM
    C -->|"confirma atributo\na atributo"| AM
    C -->|"cadastra"| EX
    C -->|"envia foto\nde referência"| II
    C -->|"envia foto\nde campo"| IC

    %% Especialista
    E -->|"aprova /\nrejeita"| EX
    E -->|"revisa e\npublica"| FA

    %% Espécie → filhos
    SP -->|"1 : N"| AM
    SP -->|"1 : N"| EX
    SP -->|"1 : N"| II
    SP -->|"1 : N"| PD
    SP -->|"1 : N"| FA
    SP -->|"1 : N"| HA

    %% Exemplar → Imagem
    EX -->|"1 : N\n(após aprovação)"| IC

    %% Tudo → Histórico
    AM -->|"registra alteração"| HA
    EX -->|"registra alteração"| HA
    IC -->|"registra alteração"| HA
    II -->|"registra alteração"| HA

    %% Estilos
    style G fill:#4f86c6,color:#fff,stroke:none
    style C fill:#4f86c6,color:#fff,stroke:none
    style E fill:#4f86c6,color:#fff,stroke:none
    style SP fill:#2e7d32,color:#fff,stroke:none
    style AM fill:#558b2f,color:#fff,stroke:none
    style PD fill:#558b2f,color:#fff,stroke:none
    style EX fill:#f57f17,color:#fff,stroke:none
    style II fill:#6a1b9a,color:#fff,stroke:none
    style IC fill:#6a1b9a,color:#fff,stroke:none
    style FA fill:#c62828,color:#fff,stroke:none
    style HA fill:#37474f,color:#fff,stroke:none
```

---

## Fluxo de estados da espécie

```mermaid
stateDiagram-v2
    direction LR

    [*] --> sem_dados : Gestor cadastra espécie

    sem_dados --> dados_internet : Colaborador insere\natributos morfológicos

    dados_internet --> descrita : Todos os atributos\nconfirmados pelo colaborador

    dados_internet --> registrada : Todas as partes\nfotografadas (via exemplar)

    descrita --> em_revisao : Gestor/colaborador\ngera o artigo

    registrada --> em_revisao : Gestor/colaborador\ngera o artigo

    em_revisao --> revisada : Especialista\naprova o artigo

    em_revisao --> dados_internet : Especialista\nrejeita — volta\npara correção

    revisada --> publicado : Publicação\nautomática

    publicado --> em_contestacao : Contestação\naberta

    em_contestacao --> em_revisao : Contestação aceita\nnova revisão

    em_contestacao --> publicado : Contestação\nrecusada

    note right of descrita
        Caminho A:
        confirmar atributos
        fitomorfológicos
    end note

    note right of registrada
        Caminho B:
        cadastrar exemplar
        + fotografar partes
    end note

    note right of em_revisao
        Exige AMBOS:
        descrita + registrada
    end note
```

---

## Fluxo de estados do exemplar

```mermaid
stateDiagram-v2
    direction LR

    [*] --> aguardando_revisao : Colaborador\ncadastra exemplar

    aguardando_revisao --> aprovado : Especialista\naprova

    aguardando_revisao --> rejeitado : Especialista\nrejeita com motivo

    rejeitado --> aguardando_revisao : Colaborador\ncorrige e resubmete

    aprovado --> [*] : Fotos de partes\nliberadas

    note right of aguardando_revisao
        Fotos de partes
        BLOQUEADAS
    end note

    note right of aprovado
        Fotos de partes
        LIBERADAS
    end note
```

---

## Relacionamento entre tabelas (modelo relacional simplificado)

```mermaid
graph LR
    U["USUARIOS\n──────\nPK id\nnome\nemail\ncategoria"]

    SP["ESPECIES_ADMINISTRATIVO\n──────\nPK id\nFK gestor_id\nFK autor_descrita_id\nFK autor_registrada_id\nnome_cientifico\nstatus\ndata_descrita\ndata_registrada"]

    EX["EXEMPLARES\n──────\nPK id\nFK especie_id\nFK especialista_id\nFK cadastrado_por\ncodigo (UK)\nlatitude / longitude\ncidade / estado / bioma\nstatus"]

    EI["ESPECIES_IMAGENS\n──────\nPK id\nFK especie_id\nFK exemplar_id (nullable)\nFK coletor_id\nparte_planta\norigem\ncaminho_imagem\ndata_coleta"]

    PD["PARTES_DISPENSADAS\n──────\nPK id\nFK especie_id\nFK dispensado_por\nparte_planta\nmotivo"]

    HA["HISTORICO_ALTERACOES\n──────\nPK id\nFK especie_id\nFK id_usuario\ntabela_afetada\ncampo_alterado\nvalor_novo\ntipo_acao"]

    FA["FILA_APROVACAO\n──────\nPK id\nFK especie_id\nFK solicitado_por\nFK resolvido_por\ntipo_acao\nstatus"]

    U -->|"1 : N\ngestor_id"| SP
    U -->|"1 : N\nespecialista_id"| EX
    U -->|"1 : N\ncadastrado_por"| EX
    U -->|"1 : N\ncoletor_id"| EI
    U -->|"1 : N\ndispensado_por"| PD
    U -->|"1 : N\nid_usuario"| HA
    U -->|"1 : N\nsolicitado_por"| FA

    SP -->|"1 : N\nespecie_id"| EX
    SP -->|"1 : N\nespecie_id"| EI
    SP -->|"1 : N\nespecie_id"| PD
    SP -->|"1 : N\nespecie_id"| HA
    SP -->|"1 : N\nespecie_id"| FA

    EX -->|"1 : N\nexemplar_id"| EI

    style U fill:#1565c0,color:#fff,stroke:none
    style SP fill:#2e7d32,color:#fff,stroke:none
    style EX fill:#e65100,color:#fff,stroke:none
    style EI fill:#6a1b9a,color:#fff,stroke:none
    style PD fill:#558b2f,color:#fff,stroke:none
    style HA fill:#37474f,color:#fff,stroke:none
    style FA fill:#b71c1c,color:#fff,stroke:none
```

---

## Resumo das cardinalidades

| Tabela origem | Tabela destino | Cardinalidade | Coluna FK |
|---|---|---|---|
| USUARIOS | ESPECIES_ADMINISTRATIVO | 1 : N | gestor_id |
| USUARIOS | ESPECIES_ADMINISTRATIVO | 1 : N | autor_descrita_id |
| USUARIOS | ESPECIES_ADMINISTRATIVO | 1 : N | autor_registrada_id |
| USUARIOS | EXEMPLARES | 1 : N | especialista_id |
| USUARIOS | EXEMPLARES | 1 : N | cadastrado_por |
| USUARIOS | ESPECIES_IMAGENS | 1 : N | coletor_id |
| USUARIOS | PARTES_DISPENSADAS | 1 : N | dispensado_por |
| USUARIOS | HISTORICO_ALTERACOES | 1 : N | id_usuario |
| USUARIOS | FILA_APROVACAO | 1 : N | solicitado_por |
| ESPECIES_ADMINISTRATIVO | EXEMPLARES | 1 : N | especie_id |
| ESPECIES_ADMINISTRATIVO | ESPECIES_IMAGENS | 1 : N | especie_id |
| ESPECIES_ADMINISTRATIVO | PARTES_DISPENSADAS | 1 : N | especie_id |
| ESPECIES_ADMINISTRATIVO | HISTORICO_ALTERACOES | 1 : N | especie_id |
| ESPECIES_ADMINISTRATIVO | FILA_APROVACAO | 1 : N | especie_id |
| EXEMPLARES | ESPECIES_IMAGENS | 1 : N (nullable) | exemplar_id |
