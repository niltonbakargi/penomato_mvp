<?php
/**
 * VOCABULÁRIO BOTÂNICO — Penomato
 *
 * Mapeia cada valor predefinido do BD para sua forma textual em português,
 * pronta para ser inserida nas frases do artigo científico.
 *
 * Estrutura: $VOCABULARIO['atributo']['ValorBD'] = 'forma textual'
 *
 * Regras de concordância adotadas:
 *   - folha / flor / semente → feminino plural
 *   - fruto                  → masculino plural
 *   - caule                  → masculino singular
 */

return [

    // ═══════════════════════════════════════════════
    // FOLHA
    // ═══════════════════════════════════════════════

    'forma_folha' => [
        'Lanceolada'       => 'lanceoladas',
        'Linear'           => 'lineares',
        'Elíptica'         => 'elípticas',
        'Ovada'            => 'ovadas',
        'Orbicular'        => 'orbiculares',
        'Cordiforme'       => 'cordiformes',
        'Espatulada'       => 'espatuladas',
        'Sagitada'         => 'sagitadas',
        'Reniforme'        => 'reniformes',
        'Obovada'          => 'obovadas',
        'Trilobada'        => 'trilobadas',
        'Palmada'          => 'palmadas',
        'Lobada'           => 'lobadas',
    ],

    'filotaxia_folha' => [
        'Alterna'           => 'dispostas de modo alterno',
        'Oposta Simples'    => 'dispostas de modo oposto simples',
        'Oposta Decussada'  => 'dispostas de modo oposto decussado',
        'Verticilada'       => 'verticiladas',
        'Dística'           => 'dísticas',
        'Espiralada'        => 'dispostas em espiral',
    ],

    'tipo_folha' => [
        'Simples'              => 'simples',
        'Composta pinnada'     => 'compostas pinadas',
        'Composta bipinada'    => 'compostas bipinadas',
        'Composta tripinada'   => 'compostas tripinadas',
        'Composta tetrapinada' => 'compostas tetrapinadas',
    ],

    'tamanho_folha' => [
        'Microfilas (< 2 cm)'  => 'microfilas, com menos de 2 cm de comprimento',
        'Nanofilas (2–7 cm)'   => 'nanofilas, entre 2 e 7 cm de comprimento',
        'Mesofilas (7–20 cm)'  => 'mesofilas, entre 7 e 20 cm de comprimento',
        'Macrófilas (20–50 cm)'=> 'macrófilas, entre 20 e 50 cm de comprimento',
        'Megafilas (> 50 cm)'  => 'megafilas, com mais de 50 cm de comprimento',
    ],

    'textura_folha' => [
        'Coriácea'    => 'de textura coriácea',
        'Cartácea'    => 'de textura cartácea',
        'Membranácea' => 'de textura membranácea',
        'Suculenta'   => 'suculentas',
        'Pilosa'      => 'pilosas',
        'Glabra'      => 'glabras',
        'Rugosa'      => 'rugosas',
        'Cerosa'      => 'cerosas',
    ],

    'margem_folha' => [
        'Inteira'  => 'com margem inteira',
        'Serrada'  => 'com margem serrada',
        'Dentada'  => 'com margem dentada',
        'Crenada'  => 'com margem crenada',
        'Ondulada' => 'com margem ondulada',
        'Lobada'   => 'com margem lobada',
        'Partida'  => 'com margem partida',
        'Revoluta' => 'com margem revoluta',
        'Involuta' => 'com margem involuta',
    ],

    'venacao_folha' => [
        'Reticulada Pinnada'  => 'venação reticulada pinada',
        'Reticulada Palmada'  => 'venação reticulada palmada',
        'Paralela'            => 'venação paralela',
        'Peninérvea'          => 'venação peninérvea',
        'Dicotômica'          => 'venação dicotômica',
        'Curvinérvea'         => 'venação curvinérvea',
    ],

    // ═══════════════════════════════════════════════
    // FLOR
    // ═══════════════════════════════════════════════

    'cor_flores' => [
        'Brancas'   => 'brancas',
        'Amarelas'  => 'amarelas',
        'Vermelhas' => 'vermelhas',
        'Rosadas'   => 'rosadas',
        'Roxas'     => 'roxas',
        'Azuis'     => 'azuis',
        'Laranjas'  => 'alaranjadas',
        'Verdes'    => 'esverdeadas',
    ],

    'simetria_floral' => [
        'Actinomorfa' => 'de simetria actinomorfa',
        'Zigomorfa'   => 'de simetria zigomorfa',
        'Assimétrica' => 'assimétricas',
    ],

    'numero_petalas' => [
        '3 pétalas'    => 'com três pétalas',
        '4 pétalas'    => 'com quatro pétalas',
        '5 pétalas'    => 'com cinco pétalas',
        'Muitas pétalas' => 'com numerosas pétalas',
    ],

    'disposicao_flores' => [
        'Isoladas'       => 'solitárias',
        'Inflorescência' => 'reunidas em inflorescências',
    ],

    'aroma' => [
        'Sem cheiro'       => null,
        'Aroma suave'      => 'levemente perfumadas',
        'Aroma forte'      => 'intensamente perfumadas',
        'Aroma desagradável' => 'com odor desagradável',
    ],

    'tamanho_flor' => [
        'Pequena' => 'de pequeno porte',
        'Média'   => 'de porte médio',
    ],

    // ═══════════════════════════════════════════════
    // FRUTO
    // ═══════════════════════════════════════════════

    'tipo_fruto' => [
        'Baga'       => 'bagas',
        'Drupa'      => 'drupas',
        'Cápsula'    => 'cápsulas',
        'Folículo'   => 'folículos',
        'Legume'     => 'legumes',
        'Síliqua'    => 'síliquas',
        'Aquênio'    => 'aquênios',
        'Sâmara'     => 'sâmaras',
        'Cariopse'   => 'cariopses',
        'Pixídio'    => 'pixídios',
        'Hespéridio' => 'hespéridios',
        'Pepo'       => 'pepos',
    ],

    'tamanho_fruto' => [
        'Pequeno' => 'de pequenas dimensões',
        'Médio'   => 'de dimensões médias',
        'Grande'  => 'de grandes dimensões',
    ],

    'cor_fruto' => [
        'Verde'    => 'de coloração verde',
        'Amarelo'  => 'de coloração amarela',
        'Vermelho' => 'de coloração vermelha',
        'Roxo'     => 'de coloração roxa',
        'Laranja'  => 'de coloração alaranjada',
        'Marrom'   => 'de coloração marrom',
        'Preto'    => 'de coloração negra',
        'Branco'   => 'de coloração branca',
    ],

    'textura_fruto' => [
        'Lisa'      => 'de superfície lisa',
        'Rugosa'    => 'de superfície rugosa',
        'Coriácea'  => 'de superfície coriácea',
        'Peluda'    => 'de superfície pilosa',
        'Espinhosa' => 'de superfície espinhosa',
        'Cerosa'    => 'de superfície cerosa',
    ],

    'dispersao_fruto' => [
        'Zoocórica'   => 'dispersos por animais (zoocoria)',
        'Anemocórica' => 'dispersos pelo vento (anemocoria)',
        'Hidrocórica' => 'dispersos pela água (hidrocoria)',
        'Autocórica'  => 'dispersos pela própria planta (autocoria)',
    ],

    'aroma_fruto' => [
        'Sem cheiro'        => null,
        'Aroma suave'       => 'levemente aromáticos',
        'Aroma forte'       => 'intensamente aromáticos',
        'Aroma desagradável'=> 'de odor desagradável',
    ],

    // ═══════════════════════════════════════════════
    // SEMENTE
    // ═══════════════════════════════════════════════

    'tipo_semente' => [
        'Alada'   => 'aladas',
        'Carnosa' => 'carnosas',
        'Dura'    => 'de tegumento duro',
        'Oleosa'  => 'oleosas',
        'Peluda'  => 'pilosas',
    ],

    'tamanho_semente' => [
        'Pequena' => 'de pequenas dimensões',
        'Média'   => 'de dimensões médias',
        'Grande'  => 'de grandes dimensões',
    ],

    'cor_semente' => [
        'Preta'  => 'de coloração negra',
        'Marrom' => 'de coloração marrom',
        'Branca' => 'de coloração branca',
        'Amarela'=> 'de coloração amarela',
        'Verde'  => 'de coloração verde',
    ],

    'textura_semente' => [
        'Lisa'    => 'de superfície lisa',
        'Rugosa'  => 'de superfície rugosa',
        'Estriada'=> 'de superfície estriada',
        'Cerosa'  => 'de superfície cerosa',
    ],

    'quantidade_sementes' => [
        'Uma'    => 'com uma semente por fruto',
        'Poucas' => 'com poucas sementes por fruto',
        'Muitas' => 'com numerosas sementes por fruto',
    ],

    // ═══════════════════════════════════════════════
    // CAULE
    // ═══════════════════════════════════════════════

    'tipo_caule' => [
        'Ereto'       => 'ereto',
        'Prostrado'   => 'prostrado',
        'Rastejante'  => 'rastejante',
        'Trepador'    => 'trepador',
        'Subterrâneo' => 'subterrâneo',
    ],

    'estrutura_caule' => [
        'Lenhoso'   => 'de estrutura lenhosa',
        'Herbáceo'  => 'de estrutura herbácea',
        'Suculento' => 'de estrutura suculenta',
    ],

    'textura_caule' => [
        'Lisa'      => 'de superfície lisa',
        'Rugosa'    => 'de superfície rugosa',
        'Sulcada'   => 'sulcado',
        'Fissurada' => 'com casca fissurada',
        'Cerosa'    => 'de superfície cerosa',
        'Espinhosa' => 'com superfície espinhosa',
        'Suberosa'  => 'com casca suberosa',
    ],

    'cor_caule' => [
        'Marrom'      => 'de coloração marrom',
        'Verde'       => 'de coloração verde',
        'Cinza'       => 'de coloração cinza',
        'Avermelhado' => 'de coloração avermelhada',
        'Alaranjado'  => 'de coloração alaranjada',
    ],

    'forma_caule' => [
        'Cilíndrico'   => 'de secção cilíndrica',
        'Quadrangular' => 'de secção quadrangular',
        'Achatado'     => 'achatado',
        'Irregular'    => 'de forma irregular',
    ],

    'modificacao_caule' => [
        'Estolão'   => 'apresentando estolões',
        'Cladódio'  => 'modificado em cladódio',
        'Rizoma'    => 'com rizoma',
        'Tubérculo' => 'com tubérculo',
        'Espinhos'  => 'armado com espinhos',
    ],

    'diametro_caule' => [
        'Fino'   => 'de diâmetro reduzido',
        'Médio'  => 'de diâmetro mediano',
        'Grosso' => 'de grande diâmetro',
    ],

    'ramificacao_caule' => [
        'Dicotômica' => 'com ramificação dicotômica',
        'Monopodial' => 'com ramificação monopodial',
        'Simpodial'  => 'com ramificação simpodial',
    ],

    // ═══════════════════════════════════════════════
    // CARACTERÍSTICAS GERAIS
    // ═══════════════════════════════════════════════

    'possui_espinhos' => [
        'Sim' => 'apresenta espinhos',
        'Não' => 'não apresenta espinhos',
    ],

    'possui_latex' => [
        'Sim' => 'produz látex',
        'Não' => 'não produz látex',
    ],

    'possui_seiva' => [
        'Sim' => 'produz seiva',
        'Não' => null,
    ],

    'possui_resina' => [
        'Sim' => 'produz resina',
        'Não' => null,
    ],

];
