# Diagramas de Ideação — Banco de Matrizes Florestais (BMF)

Módulo colaborativo do Penomato para mapeamento de indivíduos arbóreos com potencial para coleta de material propagativo (sementes, estacas, propágulos).

---

## 1. Diagrama de Contexto

Problema central e valor gerado pelo módulo.

```mermaid
graph TD
    PROB["🌳 Problema\nEngenheiro florestal não sabe\nonde encontrar sementes\nde uma espécie específica agora"]
    SOL["📍 Solução — BMF\nMapa colaborativo de matrizes\nregistradas por qualquer pessoa\ncom smartphone"]
    VAL["✅ Valor gerado\nQualquer pessoa consulta:\n'Onde acho sementes de\naroeira hoje?'"]

    PROB --> SOL --> VAL

    SOL --> C1["Dados georreferenciados\npor espécie"]
    SOL --> C2["Fotos das partes\nda planta"]
    SOL --> C3["Histórico de\ncadastramentos"]
    SOL --> C4["Identificação automática\nvia REFLORA"]
```

---

## 2. Diagrama de Atores e Casos de Uso

Quem interage com o sistema e o que cada ator pode fazer.

```mermaid
graph LR
    V(["👤 Visitante\nsem login"])
    C(["👤 Colaborador\ncom login"])
    G(["👤 Gestor"])
    REFLORA(["🌐 REFLORA / JBRJ\nAPI externa"])

    subgraph BMF ["Banco de Matrizes Florestais"]
        UC1["Ver lista de matrizes"]
        UC2["Ver mapa interativo"]
        UC3["Ver ficha de uma matriz"]
        UC4["Registrar nova matriz"]
        UC5["Enviar foto de parte"]
        UC6["Filtrar por espécie"]
        UC7["Remover matriz\n(soft-delete)"]
        UC8["Buscar nome científico\npor nome popular"]
        UC9["Autocomplete de\nnome científico"]
    end

    V --> UC1
    V --> UC2
    V --> UC3
    V --> UC6

    C --> UC1
    C --> UC2
    C --> UC3
    C --> UC4
    C --> UC5
    C --> UC6

    G --> UC1
    G --> UC2
    G --> UC3
    G --> UC4
    G --> UC5
    G --> UC6
    G --> UC7

    UC8 --> REFLORA
    UC9 --> REFLORA
```

---

## 3. Fluxo do Usuário — Registrar uma Matriz

Jornada completa do colaborador pelo celular no campo.

```mermaid
flowchart TD
    START([Colaborador no campo\nidentifica árvore de interesse])
    LOGIN{Tem sessão\nativa?}
    REDIR["Redireciona para login\n?redirect=matrizes/registrar"]
    FORM["Abre formulário de registro\n(mobile-first)"]

    GPS{GPS\ndisponível?}
    GPSCAP["Captura lat/lon\nautomaticamente"]
    GPSMANUAL["Usuário digita\nlat/lon manualmente"]

    ESPECIE["Digita nome popular\n↓ AJAX → REFLORA\n↓ Preenche nome científico"]

    FOTO["Tira foto geral\n(obrigatória)\n+ até 5 partes opcionais\nfolha · flor · fruto · caule · semente"]

    VALID{Foto geral\npresente?}
    ERRO["Alerta JS:\n'Foto geral obrigatória'"]

    SALVA["Controller salva:\n• Matriz com código MT###\n• Fotos em /uploads/matrizes/\n• Registro no banco"]

    FIM(["Matriz registrada\nAparece no mapa e na lista"])

    START --> LOGIN
    LOGIN -- Não --> REDIR --> FORM
    LOGIN -- Sim --> FORM
    FORM --> GPS
    GPS -- Sim --> GPSCAP --> ESPECIE
    GPS -- Não --> GPSMANUAL --> ESPECIE
    ESPECIE --> FOTO --> VALID
    VALID -- Não --> ERRO --> FOTO
    VALID -- Sim --> SALVA --> FIM
```

---

## 4. Fluxo do Usuário — Consultar o Mapa

Jornada do visitante ou colaborador buscando matrizes sem login.

```mermaid
flowchart TD
    START([Usuário acessa\npenomato.app.br/matrizes])
    LIST["Exibe lista de cards\nthumb · nome · código · data"]
    FILTER["Filtra por nome popular\nou científico (client-side)"]
    MAPA["Clica 'Ver todas no mapa'\nou 'Mapa' em um card"]
    OVERLAY["Mapa fullscreen abre\n(overlay position:fixed)\nLeaflet lazy-init"]
    PIN["Marcadores: pin SVG de árvore\npor espécie registrada"]
    FICHA["Clica no pin\n→ Abre ficha da matriz"]
    DETALHES["Ficha pública:\nnome · código · fotos\npartes · data · cadastrador"]
    FOTO{Quer adicionar\nfoto de parte?}
    LOGIN["Redireciona para login\n?redirect=matrizes/adicionar_foto"]
    ADD["Adiciona foto de parte\nà matriz existente"]
    FIM([Contribuição registrada])

    START --> LIST
    LIST --> FILTER
    LIST --> MAPA
    FILTER --> MAPA
    MAPA --> OVERLAY --> PIN --> FICHA --> DETALHES
    DETALHES --> FOTO
    FOTO -- Não --> FIM
    FOTO -- Sim, sem login --> LOGIN --> ADD --> FIM
    FOTO -- Sim, com login --> ADD --> FIM
```

---

## 5. Modelo de Dados (ER)

Estrutura das três tabelas do módulo e seus relacionamentos.

```mermaid
erDiagram
    usuarios {
        int id PK
        string nome
        string email
        string tipo
    }

    matrizes {
        int id PK
        varchar codigo "MT001, MT002…"
        varchar especie_nome "nome científico"
        varchar especie_nome_popular
        decimal latitude
        decimal longitude
        varchar foto_geral
        text observacoes
        int cadastrado_por FK
        datetime data_cadastro
        enum status "ativo | inativo"
    }

    matrizes_fotos {
        int id PK
        int matriz_id FK
        enum parte "folha | flor | fruto | caule | semente"
        varchar caminho_foto
        int enviada_por FK
        datetime data_envio
    }

    matrizes_comentarios {
        int id PK
        int matriz_id FK
        int usuario_id FK
        text texto
        datetime data
    }

    usuarios ||--o{ matrizes : "cadastra"
    usuarios ||--o{ matrizes_fotos : "envia"
    usuarios ||--o{ matrizes_comentarios : "comenta"
    matrizes ||--o{ matrizes_fotos : "tem"
    matrizes ||--o{ matrizes_comentarios : "recebe"
```

---

## 6. Arquitetura do Sistema

Camadas técnicas do módulo e integrações externas.

```mermaid
graph TB
    subgraph MOBILE ["📱 Cliente (Browser / Mobile)"]
        UI["Interface HTML/CSS\nBootstrap 5 · tokens CSS"]
        JS["JavaScript\nGPS · FileReader · Leaflet · filtros"]
    end

    subgraph SERVER ["🖥️ Servidor (PHP / XAMPP)"]
        VIEWS["Views PHP\nindex · registrar · mapa\nficha · adicionar_foto · gerenciar"]
        CTRL["Controllers PHP\nprocessar_registro\nprocessar_foto_parte\nbuscar_cientifico\nbuscar_especie"]
        AUTH["Auth\nSessão PHP\n?redirect= pós-login"]
        UPLOAD["Upload\n/uploads/matrizes/\n{codigo}_{parte}_{timestamp}.jpg"]
    end

    subgraph BD ["🗄️ Banco de Dados (MySQL)"]
        T1[("matrizes")]
        T2[("matrizes_fotos")]
        T3[("matrizes_comentarios")]
        T4[("usuarios")]
    end

    subgraph EXT ["🌐 APIs Externas"]
        REFLORA["REFLORA / JBRJ\nnome popular → científico\nautocomplete científico"]
    end

    UI --> VIEWS
    JS --> CTRL
    VIEWS --> CTRL
    CTRL --> BD
    CTRL --> UPLOAD
    AUTH --> VIEWS
    CTRL --> REFLORA
```
