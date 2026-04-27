<?php
// ============================================================
// HELPER: Geração e regeneração do artigo científico
// Usado por: finalizar_upload_temporario, confirmar_caracteristicas,
//            processar_upload_exsicata, controlador_painel_revisor
// ============================================================

require_once __DIR__ . '/autores_artigo.php';
require_once __DIR__ . '/../Config/gerador_texto_botanico.php';

// ── Funções auxiliares de formatação ────────────────────────

function artRef(string $refs): string {
    $r = trim($refs);
    return $r !== '' ? '<sup>' . htmlspecialchars($r) . '</sup>' : '';
}

function artVal(?string $v, string $fb = ''): string {
    return trim($v ?? '') !== '' ? trim($v) : $fb;
}

function artListar(array $itens, string $sep = ', '): string {
    $p = [];
    foreach ($itens as $it) {
        $t = trim($it['texto'] ?? '');
        if ($t === '' || strtolower($t) === 'não informado') continue;
        $p[] = $t . artRef($it['ref'] ?? '');
    }
    return implode($sep, $p);
}

// ── Gerador principal ────────────────────────────────────────

function gerarHtmlArtigoRascunho(array $adm, array $c, array $imgs, PDO $pdo): string {
    $h = fn($s) => htmlspecialchars((string)($s ?? ''));
    ob_start();

    echo '<div class="artigo">';

    // Cabeçalho taxonômico
    $titulo = artVal($c['nome_cientifico_completo'], $adm['nome_cientifico']);
    echo '<h2 class="art-titulo">' . $h($titulo) . artRef($c['nome_cientifico_completo_ref'] ?? '') . '</h2>';

    if (artVal($c['familia']))
        echo '<p class="art-familia"><strong>Família:</strong> ' . $h($c['familia']) . artRef($c['familia_ref'] ?? '') . '</p>';

    if (!empty($c['sinonimos'])) {
        $sins = implode(', ', array_map(fn($s) => '<em>' . $h(trim($s)) . '</em>', explode(',', $c['sinonimos'])));
        echo '<p class="art-sinonimos"><strong>Sinonímia:</strong> ' . $sins . artRef($c['sinonimos_ref'] ?? '') . '</p>';
    }

    if (!empty($c['nome_popular']))
        echo '<p class="art-nomes"><strong>Nomes populares:</strong> ' . $h($c['nome_popular']) . artRef($c['nome_popular_ref'] ?? '') . '</p>';

    // Autores/contribuidores
    $autores_bloco = montarAutoresArtigo($pdo, (int)$adm['id']);
    if ($autores_bloco) {
        $nomes = implode('; ', array_map(fn($a) => $h($a['nome']), $autores_bloco));
        echo '<p class="art-autores">' . $nomes . '</p>';
    }

    echo '<h3 class="art-secao">Descrição</h3>';

    // Caule
    $cb = artListar([
        ['texto' => artVal($c['tipo_caule']),  'ref' => $c['tipo_caule_ref'] ?? ''],
        ['texto' => artVal($c['forma_caule']), 'ref' => $c['forma_caule_ref'] ?? ''],
    ]);
    $ex = array_filter([
        artVal($c['textura_caule'])     ? strtolower($c['textura_caule'])     . artRef($c['textura_caule_ref'] ?? '')     : '',
        artVal($c['cor_caule'])         ? 'coloração ' . strtolower($c['cor_caule'])         . artRef($c['cor_caule_ref'] ?? '')         : '',
        artVal($c['ramificacao_caule']) ? strtolower($c['ramificacao_caule']) . artRef($c['ramificacao_caule_ref'] ?? '') : '',
        artVal($c['modificacao_caule']) ? strtolower($c['modificacao_caule']) . artRef($c['modificacao_caule_ref'] ?? '') : '',
    ]);
    $esp = strtolower(artVal($c['possui_espinhos'], 'Não')) === 'não' ? 'desprovido de espinhos' . artRef($c['possui_espinhos_ref'] ?? '') : 'com espinhos' . artRef($c['possui_espinhos_ref'] ?? '');
    $lat = strtolower(artVal($c['possui_latex'],    'Não')) === 'não' ? 'látex ausente'          . artRef($c['possui_latex_ref'] ?? '')    : 'com látex'   . artRef($c['possui_latex_ref'] ?? '');
    $res = strtolower(artVal($c['possui_resina'],   'Não')) === 'não' ? 'resina ausente'         . artRef($c['possui_resina_ref'] ?? '')   : 'com resina'  . artRef($c['possui_resina_ref'] ?? '');
    echo '<p class="art-paragrafo">Caule ' . $cb . ($ex ? ', ' . implode(', ', $ex) : '') . ', ' . implode(', ', array_filter([$esp, $lat, $res])) . '.</p>';

    // Folhas
    $fl = artListar([
        ['texto' => artVal($c['tipo_folha']),       'ref' => $c['tipo_folha_ref'] ?? ''],
        ['texto' => artVal($c['filotaxia_folha']),  'ref' => $c['filotaxia_folha_ref'] ?? ''],
        ['texto' => artVal($c['forma_folha'])    ? 'de forma '  . strtolower($c['forma_folha'])    : '', 'ref' => $c['forma_folha_ref'] ?? ''],
        ['texto' => artVal($c['textura_folha'])  ? 'textura '   . strtolower($c['textura_folha'])  : '', 'ref' => $c['textura_folha_ref'] ?? ''],
        ['texto' => artVal($c['margem_folha'])   ? 'margem '    . strtolower($c['margem_folha'])   : '', 'ref' => $c['margem_folha_ref'] ?? ''],
        ['texto' => artVal($c['venacao_folha'])  ? 'venação '   . strtolower($c['venacao_folha'])  : '', 'ref' => $c['venacao_folha_ref'] ?? ''],
        ['texto' => artVal($c['tamanho_folha'])  ? 'tamanho '   . strtolower($c['tamanho_folha'])  : '', 'ref' => $c['tamanho_folha_ref'] ?? ''],
    ]);
    if ($fl) echo '<p class="art-paragrafo">Folhas ' . $fl . '.</p>';

    // Flores
    $fr = artListar([
        ['texto' => artVal($c['disposicao_flores']), 'ref' => $c['disposicao_flores_ref'] ?? ''],
        ['texto' => artVal($c['simetria_floral']),   'ref' => $c['simetria_floral_ref'] ?? ''],
        ['texto' => artVal($c['numero_petalas'])  ? 'com '          . strtolower($c['numero_petalas'])  : '', 'ref' => $c['numero_petalas_ref'] ?? ''],
        ['texto' => artVal($c['cor_flores'])      ? 'de coloração ' . strtolower($c['cor_flores'])      : '', 'ref' => $c['cor_flores_ref'] ?? ''],
        ['texto' => artVal($c['tamanho_flor'])    ? 'tamanho '      . strtolower($c['tamanho_flor'])    : '', 'ref' => $c['tamanho_flor_ref'] ?? ''],
        ['texto' => artVal($c['aroma'])           ? 'aroma '        . strtolower($c['aroma'])           : '', 'ref' => $c['aroma_ref'] ?? ''],
    ]);
    if ($fr) echo '<p class="art-paragrafo">Flores ' . $fr . '.</p>';

    // Frutos
    $ft = artVal($c['tipo_fruto']) ? strtolower($c['tipo_fruto']) . artRef($c['tipo_fruto_ref'] ?? '') : '';
    $fp = implode(', ', array_filter([
        artVal($c['tamanho_fruto'])   ? strtolower($c['tamanho_fruto'])                    . artRef($c['tamanho_fruto_ref'] ?? '')   : '',
        artVal($c['cor_fruto'])       ? 'de coloração ' . strtolower($c['cor_fruto'])      . artRef($c['cor_fruto_ref'] ?? '')       : '',
        artVal($c['textura_fruto'])   ? 'textura '      . strtolower($c['textura_fruto'])  . artRef($c['textura_fruto_ref'] ?? '')   : '',
        artVal($c['aroma_fruto'])     ? 'aroma '        . strtolower($c['aroma_fruto'])    . artRef($c['aroma_fruto_ref'] ?? '')     : '',
        artVal($c['dispersao_fruto']) ? 'dispersão '    . strtolower($c['dispersao_fruto']). artRef($c['dispersao_fruto_ref'] ?? '') : '',
    ]));
    if ($ft || $fp) echo '<p class="art-paragrafo">Fruto do tipo ' . $ft . ($fp ? ', ' . $fp : '') . '.</p>';

    // Sementes
    $se = artListar([
        ['texto' => artVal($c['tipo_semente']),       'ref' => $c['tipo_semente_ref'] ?? ''],
        ['texto' => artVal($c['tamanho_semente'])   ? strtolower($c['tamanho_semente'])                          : '', 'ref' => $c['tamanho_semente_ref'] ?? ''],
        ['texto' => artVal($c['cor_semente'])       ? 'de coloração ' . strtolower($c['cor_semente'])            : '', 'ref' => $c['cor_semente_ref'] ?? ''],
        ['texto' => artVal($c['textura_semente'])   ? 'textura '      . strtolower($c['textura_semente'])        : '', 'ref' => $c['textura_semente_ref'] ?? ''],
        ['texto' => artVal($c['quantidade_sementes'])? strtolower($c['quantidade_sementes']) . ' sementes por fruto' : '', 'ref' => $c['quantidade_sementes_ref'] ?? ''],
    ]);
    if ($se) echo '<p class="art-paragrafo">Sementes ' . $se . '.</p>';

    // Prancha fotográfica
    if ($imgs) {
        echo '<h3 class="art-secao">Prancha Fotográfica</h3><div class="art-galeria">';
        foreach ($imgs as $img) {
            echo '<figure class="art-figura">';
            echo '<img src="/penomato_mvp/' . htmlspecialchars($img['caminho_imagem']) . '" alt="' . $h($img['parte_planta']) . '">';
            echo '<figcaption>' . ucfirst($h($img['parte_planta']));
            if (!empty($img['autor_imagem'])) echo ' — ' . $h($img['autor_imagem']);
            if (!empty($img['licenca']))      echo ' (' . $h($img['licenca']) . ')';
            if (!empty($img['fonte_nome'])) {
                $link = !empty($img['fonte_url'])
                    ? '<a href="' . $h($img['fonte_url']) . '" target="_blank">' . $h($img['fonte_nome']) . '</a>'
                    : $h($img['fonte_nome']);
                echo '<br><small>Fonte: ' . $link . '</small>';
            }
            echo '</figcaption></figure>';
        }
        echo '</div>';
    }

    // Referências
    $refs_txt = trim($c['referencias'] ?? '');
    if ($refs_txt !== '') {
        $refs_map = [];
        foreach (preg_split('/(?=\n?\d+\.\s)/', $refs_txt, -1, PREG_SPLIT_NO_EMPTY) as $pt) {
            if (preg_match('/^(\d+)\.\s+(.+)$/s', trim($pt), $m)) $refs_map[(int)$m[1]] = trim($m[2]);
        }
        if ($refs_map) {
            ksort($refs_map);
            echo '<h3 class="art-secao">Referências</h3><ol class="art-refs">';
            foreach ($refs_map as $n => $t) echo '<li id="ref-' . $n . '">' . htmlspecialchars($t) . '</li>';
            echo '</ol>';
        }
    }

    echo '</div><!-- .artigo -->';
    return ob_get_clean();
}

// ── Regeneração automática ───────────────────────────────────

/**
 * Busca os dados atuais da espécie, regera o HTML do artigo
 * e salva na tabela artigos. Chamado a cada mudança de status.
 * Silencioso em caso de erro (não crítico).
 */
function regenerarArtigoEspecie(PDO $pdo, int $especie_id): void {
    try {
        $stmt = $pdo->prepare("SELECT * FROM especies_administrativo WHERE id = ? LIMIT 1");
        $stmt->execute([$especie_id]);
        $adm = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT * FROM especies_caracteristicas WHERE especie_id = ? LIMIT 1");
        $stmt->execute([$especie_id]);
        $c = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $stmt = $pdo->prepare("
            SELECT parte_planta, caminho_imagem, autor_imagem, licenca, fonte_nome, fonte_url
            FROM especies_imagens
            WHERE especie_id = ?
            ORDER BY FIELD(parte_planta,'habito','folha','flor','fruto','caule','semente','exsicata_completa','detalhe')
        ");
        $stmt->execute([$especie_id]);
        $imgs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$adm || !$c) return;

        $html = gerarHtmlArtigoRascunho($adm, $c, $imgs, $pdo);

        $pdo->prepare("
            UPDATE artigos SET texto_html = ?, atualizado_em = NOW() WHERE especie_id = ?
        ")->execute([$html, $especie_id]);

        error_log("regenerarArtigoEspecie: artigo ID={$especie_id} atualizado (" . strlen($html) . " bytes)");
    } catch (Exception $e) {
        error_log("regenerarArtigoEspecie: erro ID={$especie_id} — " . $e->getMessage());
    }
}
