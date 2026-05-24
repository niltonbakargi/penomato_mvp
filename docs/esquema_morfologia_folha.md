# Esquema de Morfologia da Folha — Penomato

Documento de referência para o sistema de campos de folha implementado no BD e nos formulários.

---

## Campos e ENUMs

### 1. Forma (`forma_folha`)
> Contorno geral do limbo foliar

| Valor | — |
|---|---|
| Lanceolada | Linear |
| Elíptica | Ovada |
| Orbicular | Cordiforme |
| Espatulada | Sagitada |
| Reniforme | Obovada |
| Trilobada | Palmada |
| Lobada | — |

---

### 2. Filotaxia (`filotaxia_folha`)
> Disposição das folhas no caule

| Valor |
|---|
| Alterna |
| Oposta Simples |
| Oposta Decussada |
| Verticilada |
| Dística |
| Espiralada |

---

### 3. Tipo (`tipo_folha`)
> Simples ou composta — aciona a cascata de divisão

| Valor |
|---|
| Simples |
| Composta |

---

### 4. Divisão (`divisao_folha`)
> **Visível apenas quando** `tipo_folha = Composta`

| Valor | Aciona paridade? | Obs. margem |
|---|---|---|
| Trifoliada | Não | refere-se ao **folíolo** |
| Digitada | Não | refere-se ao **folíolo** |
| Pinnada | **Sim** | refere-se ao **folíolo** |
| Bipinnada | **Sim** | refere-se ao **folíolulo** |
| Tripinnada | **Sim** | refere-se ao **folíolulo** |
| Tetrapinnada | Não | refere-se ao **folíolulo** |

---

### 5. Paridade da Pinação (`paridade_pinnacao`)
> **Visível apenas quando** `divisao_folha ∈ {Pinnada, Bipinnada, Tripinnada}`

| Valor |
|---|
| Paripinnada |
| Imparipinnada |

---

### 6. Tamanho (`tamanho_folha`)
> Classificação pelo comprimento do limbo

| Valor |
|---|
| Microfilas (< 2 cm) |
| Nanofilas (2–7 cm) |
| Mesofilas (7–20 cm) |
| Macrófilas (20–50 cm) |
| Megafilas (> 50 cm) |

---

### 7. Textura (`textura_folha`)
> Consistência e superfície do limbo

| Valor |
|---|
| Coriácea |
| Cartácea |
| Membranácea |
| Suculenta |
| Pilosa |
| Glabra |
| Rugosa |
| Cerosa |

---

### 8. Margem (`margem_folha`)
> **Atenção:** em folha composta, refere-se ao folíolo ou folíolulo (ver tabela de divisão)

| Valor |
|---|
| Inteira |
| Serrada |
| Dentada |
| Crenada |
| Ondulada |
| Lobada |
| Partida |
| Revoluta |
| Involuta |

---

### 9. Venação (`venacao_folha`)
> Padrão de nervuras do limbo

| Valor |
|---|
| Reticulada Pinnada |
| Reticulada Palmada |
| Paralela |
| Peninérvea |
| Dicotômica |
| Curvinérvea |

---

## Lógica de Cascata (JavaScript)

```
tipo_folha
  └── [Composta] → exibe divisao_folha
                      ├── [Pinnada]     → exibe paridade_pinnacao
                      ├── [Bipinnada]   → exibe paridade_pinnacao
                      ├── [Tripinnada]  → exibe paridade_pinnacao
                      ├── [Tetrapinnada]→ não exibe paridade
                      ├── [Trifoliada]  → não exibe paridade
                      └── [Digitada]    → não exibe paridade
```

### Caixa de observação da margem

Aparece abaixo do campo `margem_folha` quando `tipo_folha = Composta`:

| Divisão | Mensagem |
|---|---|
| Bipinnada / Tripinnada / Tetrapinnada | *"a margem se refere ao **folíolulo** (menor subdivisão)"* |
| Pinnada / Trifoliada / Digitada | *"a margem se refere ao **folíolo**"* |
| *(nenhuma divisão selecionada)* | oculto |

### IDs HTML dos grupos ocultáveis

| ID | Contém |
|---|---|
| `grp-divisao-folha` | select `divisao_folha` + ref |
| `grp-paridade-pinnacao` | select `paridade_pinnacao` + ref |
| `obs-margem-composta` | div com `obs-margem-texto` |

### dispatchEvent após preenchimento via IA

Após a IA preencher os campos, é necessário disparar `change` na ordem:

```javascript
['tipo_folha', 'divisao_folha'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.dispatchEvent(new Event('change'));
});
```

---

## Fonte de verdade

| Arquivo | Papel |
|---|---|
| `src/Config/enums_caracteristicas.php` | Array PHP com todos os valores válidos |
| `src/Config/vocabulario_botanico.php` | Mapeamento valor → texto para artigo científico |
| `database/penomato (42).sql` | ENUM do banco de produção |

---

## Arquivos que implementam a cascata

| Arquivo | Tipo |
|---|---|
| `src/Controllers/inserir_dados_internet.php` | Formulário de importação (colaborador) |
| `src/Controllers/confirmar_caracteristicas.php` | Formulário de confirmação (gestor/revisor) |
