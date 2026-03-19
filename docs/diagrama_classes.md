# Diagrama de Classes — Penomato

> **Como visualizar:** Cole o bloco abaixo em [https://mermaid.live](https://mermaid.live) para exportar como PNG ou SVG,
> ou instale a extensão **"Markdown Preview Mermaid Support"** no VS Code para ver direto no editor.

---

```mermaid
classDiagram
    direction TB

    %% ══════════════════════════════════════════
    %% ENUMERAÇÕES
    %% ══════════════════════════════════════════

    class CategoriaUsuario {
        <<enumeration>>
        gestor
        colaborador
        revisor
    }

    class StatusEspecie {
        <<enumeration>>
        sem_dados
        dados_internet
        descrita
        registrada
        em_revisao
        revisada
        publicado
        em_contestacao
    }

    class StatusExemplar {
        <<enumeration>>
        aguardando_revisao
        aprovado
        rejeitado
    }

    class PartePlanta {
        <<enumeration>>
        folha
        flor
        fruto
        caule
        semente
        habito
    }

    class OrigemImagem {
        <<enumeration>>
        internet
        campo
    }

    class TipoAcaoHistorico {
        <<enumeration>>
        insercao
        edicao
        validacao
        contestacao
        exclusao
    }

    %% ══════════════════════════════════════════
    %% ENTIDADES PRINCIPAIS
    %% ══════════════════════════════════════════

    class Usuario {
        +int id
        +string nome
        +string email
        +string senha_hash
        +CategoriaUsuario categoria
        +datetime data_cadastro
        +bool ativo
    }

    class Especie {
        +int id
        +string nome_cientifico
        +string nome_popular
        +string familia
        +StatusEspecie status
        +int gestor_id
        +int autor_descrita_id
        +int autor_registrada_id
        +datetime data_criacao
        +datetime data_descrita
        +datetime data_registrada
        +string descricao_habito
        +string descricao_folha
        +string descricao_flor
        +string descricao_fruto
        +string descricao_caule
        +string descricao_semente
        +string distribuicao_geografica
        +text sinonimias
        +text referencias
    }

    class Exemplar {
        +int id
        +string codigo
        +int especie_id
        +string numero_etiqueta
        +string foto_identificacao
        +decimal latitude
        +decimal longitude
        +string cidade
        +char estado
        +string bioma
        +text descricao_local
        +int especialista_id
        +int cadastrado_por
        +datetime data_cadastro
        +StatusExemplar status
        +datetime data_revisao
        +text motivo_rejeicao
        +gerarCodigo() string
        +aprovar() void
        +rejeitar(motivo) void
    }

    class EspecieImagem {
        +int id
        +int especie_id
        +int exemplar_id
        +PartePlanta parte_planta
        +OrigemImagem origem
        +string caminho_imagem
        +string nome_original
        +int tamanho_bytes
        +string mime_type
        +string licenca
        +date data_coleta
        +string coletor_nome
        +int coletor_id
        +string numero_etiqueta
        +text observacoes_internas
        +string status_validacao
        +datetime data_upload
    }

    class PartesDispensadas {
        +int id
        +int especie_id
        +PartePlanta parte_planta
        +text motivo
        +int dispensado_por
        +datetime data_dispensa
    }

    class HistoricoAlteracoes {
        +int id
        +int especie_id
        +int id_usuario
        +string tabela_afetada
        +string campo_alterado
        +text valor_anterior
        +text valor_novo
        +text justificativa
        +TipoAcaoHistorico tipo_acao
        +datetime data_alteracao
    }

    class FilaAprovacao {
        +int id
        +int especie_id
        +string tipo_acao
        +int solicitado_por
        +string status
        +text observacao
        +datetime data_solicitacao
        +datetime data_resolucao
        +int resolvido_por
    }

    %% ══════════════════════════════════════════
    %% RELACIONAMENTOS
    %% ══════════════════════════════════════════

    %% Usuario → Especie
    Usuario "1" --> "0..*" Especie : gerencia >

    %% Especie → Exemplar
    Especie "1" *-- "0..*" Exemplar : contém >

    %% Usuario → Exemplar
    Usuario "1" --> "0..*" Exemplar : especialista de >
    Usuario "1" --> "0..*" Exemplar : cadastra >

    %% Especie → EspecieImagem
    Especie "1" *-- "0..*" EspecieImagem : possui >

    %% Exemplar → EspecieImagem
    Exemplar "1" --> "0..*" EspecieImagem : vincula >

    %% Usuario → EspecieImagem
    Usuario "1" --> "0..*" EspecieImagem : envia >

    %% Especie → PartesDispensadas
    Especie "1" *-- "0..*" PartesDispensadas : dispensa >

    %% Usuario → PartesDispensadas
    Usuario "1" --> "0..*" PartesDispensadas : autoriza >

    %% Especie → HistoricoAlteracoes
    Especie "1" *-- "0..*" HistoricoAlteracoes : registra >

    %% Usuario → HistoricoAlteracoes
    Usuario "1" --> "0..*" HistoricoAlteracoes : realiza >

    %% Especie → FilaAprovacao
    Especie "1" *-- "0..*" FilaAprovacao : aguarda >

    %% Usuario → FilaAprovacao
    Usuario "1" --> "0..*" FilaAprovacao : solicita >

    %% Enumerações vinculadas
    Usuario ..> CategoriaUsuario : usa
    Especie ..> StatusEspecie : usa
    Exemplar ..> StatusExemplar : usa
    EspecieImagem ..> PartePlanta : usa
    EspecieImagem ..> OrigemImagem : usa
    PartesDispensadas ..> PartePlanta : usa
    HistoricoAlteracoes ..> TipoAcaoHistorico : usa
```

---

## Legenda dos relacionamentos

| Notação | Significado |
|---|---|
| `*--` (composição) | A entidade filha não existe sem a pai — ex: Exemplar não existe sem Espécie |
| `-->` (associação) | Referência entre entidades independentes — ex: Usuario referenciado como especialista |
| `..>` (dependência) | Classe usa a enumeração como tipo de atributo |
| `"1"` e `"0..*"` | Multiplicidades: um para zero ou muitos |

---

## Descrição das classes

### Usuario
Representa qualquer pessoa cadastrada no sistema. O papel (`categoria`) determina o que o usuário pode fazer: o **gestor** cadastra espécies e autoriza dispensas de partes; o **colaborador** insere dados morfológicos e fotografias; o **revisor/especialista** aprova exemplares e artigos.

### Especie
Entidade central do sistema. Representa uma espécie vegetal de interesse científico. Concentra os atributos fitomorfológicos descritivos (hábito, folha, flor, fruto, caule, semente) e controla o status de progresso ao longo do fluxo de documentação.

### Exemplar
Representa um indivíduo físico específico da espécie, localizado no campo. É identificado por um código único gerado automaticamente (ex: KT001) e por uma etiqueta de alumínio pregada na planta. Precisa ser aprovado pelo especialista antes que qualquer fotografia de parte seja aceita pelo sistema.

### EspecieImagem
Registra cada fotografia enviada ao sistema, tanto de referência (origem internet) quanto de campo (origem campo, vinculada a exemplar aprovado). Armazena metadados completos de coleta: coletor, data, etiqueta, parte fotografada e licença de uso.

### PartesDispensadas
Registra formalmente as partes da planta que não estão disponíveis para fotografia (ex: espécie sem frutos na época da coleta). A dispensa é autorizada pelo gestor e computa para a verificação de completude do exemplar.

### HistoricoAlteracoes
Tabela de auditoria completa. Registra toda alteração relevante feita no sistema: inserção de dados, validações, contestações. Garante rastreabilidade total de quem fez o quê e quando.

### FilaAprovacao
Controla ações que precisam de aprovação formal — como a contestação de uma identificação ou a solicitação de geração de artigo. Permite que especialistas e gestores resolvam pendências em sua própria janela de tempo.

---

## Regras de negócio refletidas no modelo

1. `Exemplar.status` deve ser `aprovado` para que `EspecieImagem` possa ser vinculada via `exemplar_id`
2. `Especie.status = 'descrita'` exige que todos os atributos morfológicos estejam confirmados
3. `Especie.status = 'registrada'` exige que todas as `PartePlanta` estejam em `EspecieImagem` (origem campo) ou em `PartesDispensadas`, para aquela espécie
4. A geração do artigo exige `data_descrita IS NOT NULL` **e** `data_registrada IS NOT NULL` em `Especie`
5. `HistoricoAlteracoes` é populado automaticamente por todas as operações de escrita — nunca pelo usuário diretamente
