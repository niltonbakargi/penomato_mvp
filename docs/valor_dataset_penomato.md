# Valor do Dataset do Penomato para IA de Identificação de Plantas

## O problema do REFLORA para treinamento de IA

O REFLORA reúne centenas de milhares de exsicatas digitalizadas de herbários brasileiros. Para catalogação e pesquisa taxonômica tradicional, esse acervo é relevante. Para treinar modelos de identificação de plantas no campo, é praticamente inútil.

O motivo é um problema clássico de **domain gap**: as exsicatas são plantas prensadas, distorcidas, descoloridas e bidimensionais. Um modelo treinado nessas imagens aprende a reconhecer exsicata — não planta viva em campo aberto.

---

## O que o Penomato gera que o REFLORA não tem

| Penomato | Exsicata do REFLORA |
|---|---|
| Foto de cada parte como aparece na planta viva | Planta prensada, distorcida, descolorida |
| Todas as partes do mesmo indivíduo | Frequentemente incompleto |
| Indivíduo vivo, remonitorável ao longo do tempo | Espécime morto, único momento no tempo |
| Label estruturado: espécie + parte + atributos morfológicos confirmados | Apenas identificação de espécie |
| Revisado por especialista | Qualidade variável |
| Sem deformação de passagem do tempo | Deteriora com o tempo |

---

## Por que isso importa para IA

Modelos como PlantNet, iNaturalist AI e iniciativas similares precisam exatamente desse tipo de dado: **fotos de campo, por parte da planta, rotuladas com espécie confirmada**. O Cerrado tem pouquíssima cobertura com essa qualidade.

O dataset do Penomato é estruturado para treinamento desde a origem:

- **Rótulo por parte:** folha, flor, fruto, caule, semente, hábito — cada foto sabe o que mostra
- **Rótulo de espécie:** confirmado por especialista, não por votação popular ou algoritmo
- **Indivíduo rastreável:** todas as partes vêm do mesmo indivíduo físico identificado por etiqueta
- **Atributos morfológicos:** além da imagem, dados estruturados de forma, cor, textura, dimensão

---

## O que isso significa para o projeto

O banco de imagens do Penomato não é subproduto do fluxo científico — **é o produto principal de valor a longo prazo**.

Cada espécie cadastrada de ponta a ponta representa:
1. Um registro científico publicável (artigo / ficha técnica)
2. Um conjunto de treinamento rotulado e validado para IA de campo

---

## Por que espécies já conhecidas e sem exsicata física

Para espécies já descritas e com literatura consolidada, a exsicata física não acrescenta valor científico. O que faltava era o **registro fotográfico de campo de qualidade**, com todas as partes, do mesmo indivíduo, confirmado por especialista.

O Penomato preenche exatamente esse vazio — com custo ecológico zero: sem coleta destrutiva, sem papel, sem transporte, sem infraestrutura de herbário, sem risco de perda por incêndio ou deterioração.

O indivíduo permanece vivo, localizado, rastreável — e pode ser remonitorado conforme a planta muda ao longo das estações.

---

## Visão de longo prazo

Com dados suficientes por bioma, o dataset do Penomato viabiliza:

- Modelos de identificação de plantas no campo por bioma (Cerrado → Amazônia → Mata Atlântica → Pantanal → Caatinga → Pampa)
- Identificação multi-parte combinada: não "1 foto → espécie", mas conjunto de partes comparado com espécies do bioma local
- Aplicativos de campo para engenheiros florestais, ecólogos, estudantes e cidadãos
