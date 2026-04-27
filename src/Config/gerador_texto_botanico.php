<?php
/**
 * GERADOR DE TEXTO BOTÂNICO — Penomato
 *
 * Recebe os dados de características ($c) e o vocabulário ($v)
 * e retorna parágrafos em prosa corrida com concordância de
 * gênero e número em português.
 */

/**
 * Junta uma lista de termos com vírgulas e "e" no último:
 * ['a', 'b', 'c'] → "a, b e c"
 */
function implodir_lista(array $itens): string {
    $itens = array_values(array_filter($itens, fn($i) => $i !== null && $i !== ''));
    if (count($itens) === 0) return '';
    if (count($itens) === 1) return $itens[0];
    $ultimo = array_pop($itens);
    return implode(', ', $itens) . ' e ' . $ultimo;
}

/**
 * Resolve um atributo no vocabulário.
 * Retorna a forma textual ou null se o valor for vazio ou
 * mapeado para null (ex: "Sem cheiro").
 */
function v(array $v, string $atrib, string $campo): ?string {
    $val = $campo ?? '';
    if ($val === '' || $val === null) return null;
    return $v[$atrib][$val] ?? null;
}

// ═══════════════════════════════════════════════════════
// FOLHA
// ═══════════════════════════════════════════════════════

function texto_folha(array $c, array $v): ?string {
    // Núcleo: "As folhas são {tipo} {forma}"
    $tipo  = v($v, 'tipo_folha',  $c['tipo_folha']  ?? '');
    $forma = v($v, 'forma_folha', $c['forma_folha'] ?? '');

    $nucleo = array_filter([$tipo, $forma]);
    if (empty($nucleo)) return null;

    $frase = 'As folhas são ' . implodir_lista(array_values($nucleo));

    // Complementos secundários
    $comp = array_filter([
        v($v, 'textura_folha',   $c['textura_folha']   ?? ''),
        v($v, 'margem_folha',    $c['margem_folha']    ?? ''),
        v($v, 'venacao_folha',   $c['venacao_folha']   ?? ''),
        v($v, 'filotaxia_folha', $c['filotaxia_folha'] ?? ''),
        v($v, 'tamanho_folha',   $c['tamanho_folha']   ?? ''),
    ]);

    if (!empty($comp)) {
        $frase .= ', ' . implodir_lista(array_values($comp));
    }

    return $frase . '.';
}

// ═══════════════════════════════════════════════════════
// FLOR
// ═══════════════════════════════════════════════════════

function texto_flor(array $c, array $v): ?string {
    $cor        = v($v, 'cor_flores',      $c['cor_flores']      ?? '');
    $simetria   = v($v, 'simetria_floral', $c['simetria_floral'] ?? '');
    $petalas    = v($v, 'numero_petalas',  $c['numero_petalas']  ?? '');
    $disposicao = v($v, 'disposicao_flores', $c['disposicao_flores'] ?? '');
    $tamanho    = v($v, 'tamanho_flor',    $c['tamanho_flor']    ?? '');
    $aroma      = v($v, 'aroma',           $c['aroma']           ?? '');

    $nucleo = array_filter([$cor, $simetria]);
    if (empty($nucleo)) return null;

    $frase = 'As flores são ' . implodir_lista(array_values($nucleo));

    $comp = array_filter([$petalas, $disposicao, $tamanho, $aroma]);
    if (!empty($comp)) {
        $frase .= ', ' . implodir_lista(array_values($comp));
    }

    return $frase . '.';
}

// ═══════════════════════════════════════════════════════
// FRUTO
// ═══════════════════════════════════════════════════════

function texto_fruto(array $c, array $v): ?string {
    $tipo     = v($v, 'tipo_fruto',    $c['tipo_fruto']    ?? '');
    $tamanho  = v($v, 'tamanho_fruto', $c['tamanho_fruto'] ?? '');
    $cor      = v($v, 'cor_fruto',     $c['cor_fruto']     ?? '');
    $textura  = v($v, 'textura_fruto', $c['textura_fruto'] ?? '');
    $aroma    = v($v, 'aroma_fruto',   $c['aroma_fruto']   ?? '');
    $dispersao= v($v, 'dispersao_fruto', $c['dispersao_fruto'] ?? '');

    if (!$tipo) return null;

    $frase = 'Os frutos são ' . $tipo;

    $comp = array_filter([$tamanho, $cor, $textura, $aroma, $dispersao]);
    if (!empty($comp)) {
        $frase .= ', ' . implodir_lista(array_values($comp));
    }

    return $frase . '.';
}

// ═══════════════════════════════════════════════════════
// SEMENTE
// ═══════════════════════════════════════════════════════

function texto_semente(array $c, array $v): ?string {
    $tipo       = v($v, 'tipo_semente',      $c['tipo_semente']      ?? '');
    $tamanho    = v($v, 'tamanho_semente',   $c['tamanho_semente']   ?? '');
    $cor        = v($v, 'cor_semente',       $c['cor_semente']       ?? '');
    $textura    = v($v, 'textura_semente',   $c['textura_semente']   ?? '');
    $quantidade = v($v, 'quantidade_sementes', $c['quantidade_sementes'] ?? '');

    $nucleo = array_filter([$tipo, $tamanho]);
    if (empty($nucleo)) return null;

    $frase = 'As sementes são ' . implodir_lista(array_values($nucleo));

    $comp = array_filter([$cor, $textura, $quantidade]);
    if (!empty($comp)) {
        $frase .= ', ' . implodir_lista(array_values($comp));
    }

    return $frase . '.';
}

// ═══════════════════════════════════════════════════════
// CAULE
// ═══════════════════════════════════════════════════════

function texto_caule(array $c, array $v): ?string {
    $tipo        = v($v, 'tipo_caule',       $c['tipo_caule']       ?? '');
    $forma       = v($v, 'forma_caule',      $c['forma_caule']      ?? '');
    $textura     = v($v, 'textura_caule',    $c['textura_caule']    ?? '');
    $cor         = v($v, 'cor_caule',        $c['cor_caule']        ?? '');
    $ramificacao = v($v, 'ramificacao_caule',$c['ramificacao_caule']?? '');
    $modificacao = v($v, 'modificacao_caule',$c['modificacao_caule']?? '');

    if (!$tipo) return null;

    $frase = 'O caule é ' . $tipo;

    $comp = array_filter([$forma, $textura, $cor, $ramificacao, $modificacao]);
    if (!empty($comp)) {
        $frase .= ', ' . implodir_lista(array_values($comp));
    }

    return $frase . '.';
}

// ═══════════════════════════════════════════════════════
// CARACTERÍSTICAS GERAIS (espinhos, látex, seiva, resina)
// ═══════════════════════════════════════════════════════

function texto_outros(array $c, array $v): ?string {
    $itens = array_filter([
        v($v, 'possui_espinhos', $c['possui_espinhos'] ?? ''),
        v($v, 'possui_latex',    $c['possui_latex']    ?? ''),
        v($v, 'possui_seiva',    $c['possui_seiva']    ?? ''),
        v($v, 'possui_resina',   $c['possui_resina']   ?? ''),
    ]);

    if (empty($itens)) return null;

    return 'A espécie ' . implodir_lista(array_values($itens)) . '.';
}

// ═══════════════════════════════════════════════════════
// ENTRADA PRINCIPAL — gera todos os parágrafos
// ═══════════════════════════════════════════════════════

/**
 * Retorna array com os parágrafos gerados, indexados por parte.
 * Partes sem dados retornam null e são omitidas.
 */
function gerar_paragrafos(array $c): array {
    $v = require __DIR__ . '/vocabulario_botanico.php';

    return array_filter([
        'folha'   => texto_folha($c, $v),
        'flor'    => texto_flor($c, $v),
        'fruto'   => texto_fruto($c, $v),
        'semente' => texto_semente($c, $v),
        'caule'   => texto_caule($c, $v),
        'outros'  => texto_outros($c, $v),
    ]);
}
