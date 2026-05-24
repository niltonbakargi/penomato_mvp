<?php
/**
 * ENUMS DE CARACTERÍSTICAS — Penomato
 *
 * FONTE DE VERDADE ÚNICA para todos os valores aceitos nas colunas ENUM
 * da tabela `especies_caracteristicas`. Sincronizado com o banco de produção.
 *
 * Todos os arquivos que precisam validar ou exibir opções de características
 * devem importar este arquivo:
 *
 *   $ENUMS = require_once __DIR__ . '/../Config/enums_caracteristicas.php';
 *
 * Para gerar options HTML a partir de um campo:
 *   foreach ($ENUMS['campo'] as $v) echo "<option>$v</option>";
 */

return [

    // ── FOLHA ─────────────────────────────────────────────────────────────
    'forma_folha' => [
        'Lanceolada','Linear','Elíptica','Ovada','Orbicular',
        'Cordiforme','Espatulada','Sagitada','Reniforme','Obovada',
        'Trilobada','Palmada','Lobada',
    ],
    'filotaxia_folha' => [
        'Alterna','Oposta Simples','Oposta Decussada',
        'Verticilada','Dística','Espiralada',
    ],
    'tipo_folha' => ['Simples','Composta'],
    'divisao_folha' => [
        'Trifoliada','Digitada','Pinnada','Bipinnada','Tripinnada','Tetrapinnada',
    ],
    'paridade_pinnacao' => ['Paripinnada','Imparipinnada'],
    'tamanho_folha' => [
        'Microfilas (< 2 cm)','Nanofilas (2–7 cm)',
        'Mesofilas (7–20 cm)','Macrófilas (20–50 cm)','Megafilas (> 50 cm)',
    ],
    'textura_folha' => [
        'Coriácea','Cartácea','Membranácea','Suculenta',
        'Pilosa','Glabra','Rugosa','Cerosa',
    ],
    'margem_folha' => [
        'Inteira','Serrada','Dentada','Crenada',
        'Ondulada','Lobada','Partida','Revoluta','Involuta',
    ],
    'venacao_folha' => [
        'Reticulada Pinnada','Reticulada Palmada',
        'Paralela','Peninérvea','Dicotômica','Curvinérvea',
    ],

    // ── FLOR ──────────────────────────────────────────────────────────────
    'cor_flores' => [
        'Alaranjada','Amarela','Avermelhada','Azul','Branca',
        'Esverdeada','Lilás','Púrpura','Rósea','Roxa','Vermelha','Vinácea',
    ],
    'simetria_floral' => ['Actinomorfa','Zigomorfa','Assimétrica'],
    'numero_petalas' => [
        '3 pétalas','4 pétalas','5 pétalas','6 pétalas','Muitas pétalas','Ausentes',
    ],
    'disposicao_flores' => [
        'Solitária','Capítulo','Cacho','Corimbo','Espádice','Espiga','Panícula','Umbela',
    ],
    'aroma' => ['Ausente','Suave','Forte','Desagradável','Adocicada','Cítrica'],
    'tamanho_flor' => ['Pequena','Média'],

    // ── FRUTO ─────────────────────────────────────────────────────────────
    'tipo_fruto' => [
        'Baga','Drupa','Cápsula','Folículo','Legume','Síliqua',
        'Aquênio','Sâmara','Cariopse','Pixídio','Hespéridio','Pepo',
    ],
    'tamanho_fruto' => ['Pequeno','Médio','Grande'],
    'cor_fruto'     => ['Verde','Amarelo','Vermelho','Roxo','Laranja','Marrom','Preto','Branco'],
    'textura_fruto' => ['Lisa','Rugosa','Coriácea','Peluda','Espinhosa','Cerosa'],
    'dispersao_fruto' => ['Zoocórica','Anemocórica','Hidrocórica','Autocórica'],
    'aroma_fruto'   => ['Ausente','Suave','Forte','Adocicado','Cítrico','Desagradável'],

    // ── SEMENTE ───────────────────────────────────────────────────────────
    'tipo_semente'  => ['Alada','Carnosa','Dura','Oleaginosa','Plumosa','Ruminada','Arilada'],
    'tamanho_semente' => ['Pequena','Média','Grande'],
    'cor_semente'   => ['Preta','Marrom','Branca','Amarela','Verde'],
    'textura_semente' => ['Lisa','Rugosa','Estriada','Cerosa'],
    'quantidade_sementes' => ['Uma','Poucas','Muitas'],

    // ── CAULE ─────────────────────────────────────────────────────────────
    'tipo_caule'    => ['Tronco','Estipe','Colmo','Liana','Haste','Escapo'],
    'textura_caule' => [
        'Lisa','Rugosa','Sulcada','Fissurada',
        'Estriada','Escamosa','Suberosa','Aculeada','Cerosa',
    ],
    'cor_caule' => [
        'Marrom','Acinzentado','Avermelhado','Alaranjado',
        'Esbranquiçado','Esverdeado','Pardacento',
    ],
    'forma_caule' => ['Cilíndrico','Quadrangular','Triangular','Achatado','Alado'],
    'modificacao_caule' => ['Estolão','Cladódio','Rizoma','Tubérculo','Espinhos'],
    'ramificacao_caule' => ['Dicotômica','Monopodial','Simpodial'],

    // ── GERAIS ────────────────────────────────────────────────────────────
    'possui_espinhos' => ['Sim','Não'],
    'possui_latex'    => ['Sim','Não'],
    'possui_seiva'    => ['Sim','Não'],
    'possui_resina'   => ['Sim','Não'],

];
