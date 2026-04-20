# Módulo Flora do Cerrado — Histórico e Subsídios para Atividade de Extensão

**Data de implementação:** 15 de abril de 2026
**Fonte dos dados:** REFLORA — Flora e Funga do Brasil 2020, Jardim Botânico do Rio de Janeiro (JBRJ), licença CC-BY
**Contexto:** Ação de extensão — UFMS Digital: Análise Organizacional e Soluções Tecnológicas

---

## O que foi construído

### Visão geral

Um módulo público integrado ao Penomato que funciona como **consultor de nomes botânicos do Cerrado**. Qualquer pessoa — aluno, engenheiro florestal, pesquisador ou curioso — digita um nome (científico, popular ou sinônimo) e descobre o nome aceito com todas as informações nomenclaturais, além de poder acessar registros reais de campo no Penomato.

---

## Histórico de desenvolvimento (commits)

### Commit 1 — `feat: adiciona módulo público Flora do Cerrado` (ac7628e)

Primeira versão do módulo. Incluía:

- Nova página `flora_cerrado.php` com filtros por família e forma de vida, cards de estatísticas gerais, gráficos interativos (Chart.js: top famílias e formas de vida) e tabela paginada das espécies
- Tabela `flora_brasil_plantas` no banco de dados (12.161 espécies do Cerrado)
- Script Python `importar_flora_brasil.py` para importação automatizada dos dados REFLORA (angiospermsdatabase + gymnospermsdatabase)
- Botão "Flora do Cerrado" adicionado na home (`index.php`)
- Item "Flora do Cerrado" adicionado no navbar (`cabecalho.php`)

### Commit 2 — `refactor: redesenha Flora do Cerrado como ferramenta de consulta de nomes` (5f17554)

Após reflexão, percebemos que gráficos e tabela genérica não eram o diferencial real. O maior valor para o usuário era **resolver dúvidas sobre nomenclatura botânica** — especialmente a confusão entre nomes válidos e sinônimos. Interface completamente redesenhada:

**O que foi removido:**
- Gráficos, filtros avançados e tabela paginada

**O que foi construído:**
- Busca central por nome científico, nome popular ou sinônimo
- **Ficha do nome aceito:** família, formas de vida, nomes populares, distribuição estadual (MS destacado), endemismo, lista de sinônimos e link direto para registros no Penomato
- **Card de sinônimo:** alerta visual claro, nome riscado, tipo de sinonímia (heterotípico / homotípico / basônimo) e redirecionamento para o nome aceito
- **Landing page** com exemplos clicáveis quando nenhuma busca está ativa (reduz fricção de entrada)

**Banco de dados adicionado:**
- Tabela `flora_brasil_sinonimos` para armazenar sinônimos nomenclaturais
- Script Python `importar_flora_sinonimos.py` para importação

Resultado inicial: 2.140 sinônimos importados (critério inicial ainda com bug — ver commit 3).

### Commit 3 — `fix: corrige filtro de sinônimos` (fb48fef)

**Problema identificado:** sinônimos no REFLORA não têm o campo `dom_fitogeografico` preenchido — são registros nomenclaturais, não distribucionais. O filtro `WHERE dominio = 'Cerrado'` excluía todos eles.

**Solução:** novo critério de importação — um sinônimo é importado se seu `nome_aceito` existe na base de espécies do Cerrado (`flora_brasil_plantas`).

**Resultado:** cobertura saltou de **2.140 para 19.053 sinônimos**.

---

## Estrutura técnica resultante

| Componente | Descrição |
|---|---|
| `src/Views/publico/flora_cerrado.php` | Interface pública do módulo |
| `database/criar_tabela_flora_brasil.sql` | Tabela de espécies (12.161 espécies) |
| `database/criar_tabela_flora_sinonimos.sql` | Tabela de sinônimos (19.053 registros) |
| `scripts/importar_flora_brasil.py` | Importação angiospermsdatabase + gymnospermsdatabase |
| `scripts/importar_flora_sinonimos.py` | Importação de sinônimos com critério correto |

---

## Por que isso é relevante para extensão

1. **Acesso aberto ao conhecimento científico:** dados do JBRJ (CC-BY) tornados acessíveis em interface simples e responsiva, sem exigir cadastro
2. **Resolução de um problema real do campo:** engenheiros florestais e estudantes frequentemente erram na nomenclatura por desconhecer sinônimos — o módulo resolve isso em segundos
3. **Integração com dados de campo:** o link "ver no Penomato" conecta o nome botânico a exsicatas reais coletadas no Cerrado, criando um ciclo de educação → pesquisa → documentação
4. **Alcance potencial:** qualquer pessoa com acesso à internet pode usar, sem dependência de softwares especializados como Flora do Brasil online ou INCT-Herbário Virtual
5. **Base para atividades didáticas:** pode ser usado em aulas de botânica, dendrologia e fitossociologia como ferramenta de consulta durante exercícios de identificação

---

## Ideias de enriquecimento futuro

- Adicionar fotos de referência por espécie (integração com Flickr/WikiCommons via API)
- Exportar ficha da espécie em PDF para uso em relatórios de campo
- Mapa de distribuição estadual interativo (Leaflet)
- Contador de quantos exemplares daquela espécie existem no Penomato
- Modo comparação: colocar dois nomes lado a lado para ver relação nomenclatural
- Histórico de buscas para usuários logados (auxilia pesquisadores recorrentes)
- Indicar se a espécie tem status de ameaça (Lista Vermelha MMA / IUCN)
