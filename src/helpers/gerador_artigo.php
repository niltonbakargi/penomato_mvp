<?php
// ============================================================
// HELPER: Geração e regeneração do artigo científico
// Usado por: finalizar_upload_temporario, confirmar_caracteristicas,
//            processar_upload_exsicata, controlador_painel_revisor
// ============================================================

require_once __DIR__ . '/autores_artigo.php';

// ── Vocabulário botânico ─────────────────────────────────────

function _art_vocab(): array {
    static $v = null;
    if ($v === null) $v = require __DIR__ . '/../Config/vocabulario_botanico.php';
    return $v;
}

/** Resolve valor no vocabulário; retorna null se ausente ou vazio */
function _art_v(string $atrib, ?string $campo): ?string {
    if ($campo === null || $campo === '') return null;
    $v = _art_vocab();
    return $v[$atrib][$campo] ?? null;
}

/** Junta lista com vírgulas e "e" no último item */
function _art_lista(array $itens): string {
    $itens = array_values(array_filter($itens, fn($i) => $i !== null && $i !== ''));
    if (!$itens) return '';
    if (count($itens) === 1) return $itens[0];
    $ultimo = array_pop($itens);
    return implode(', ', $itens) . ' e ' . $ultimo;
}

// ── Parágrafos por parte da planta ───────────────────────────

function _art_caule(array $c): ?string {
    $tipo = _art_v('tipo_caule', $c['tipo_caule'] ?? '');
    if (!$tipo) return null;
    $comp = array_filter([
        _art_v('forma_caule',       $c['forma_caule']       ?? ''),
        _art_v('textura_caule',     $c['textura_caule']     ?? ''),
        _art_v('cor_caule',         $c['cor_caule']         ?? ''),
        _art_v('ramificacao_caule', $c['ramificacao_caule'] ?? ''),
        _art_v('modificacao_caule', $c['modificacao_caule'] ?? ''),
    ]);
    $frase = 'O caule é ' . $tipo;
    if ($comp) $frase .= ', ' . _art_lista(array_values($comp));
    return $frase . '.';
}

function _art_folha(array $c): ?string {
    $tipo  = _art_v('tipo_folha',  $c['tipo_folha']  ?? '');
    $forma = _art_v('forma_folha', $c['forma_folha'] ?? '');
    $nucleo = array_filter([$tipo, $forma]);
    if (!$nucleo) return null;
    $frase = 'As folhas são ' . _art_lista(array_values($nucleo));
    $comp = array_filter([
        _art_v('textura_folha',   $c['textura_folha']   ?? ''),
        _art_v('margem_folha',    $c['margem_folha']    ?? ''),
        _art_v('venacao_folha',   $c['venacao_folha']   ?? ''),
        _art_v('filotaxia_folha', $c['filotaxia_folha'] ?? ''),
        _art_v('tamanho_folha',   $c['tamanho_folha']   ?? ''),
    ]);
    if ($comp) $frase .= ', ' . _art_lista(array_values($comp));
    return $frase . '.';
}

function _art_flor(array $c): ?string {
    $cor      = _art_v('cor_flores',      $c['cor_flores']      ?? '');
    $simetria = _art_v('simetria_floral', $c['simetria_floral'] ?? '');
    $nucleo = array_filter([$cor, $simetria]);
    if (!$nucleo) return null;
    $frase = 'As flores são ' . _art_lista(array_values($nucleo));
    $comp = array_filter([
        _art_v('numero_petalas',    $c['numero_petalas']    ?? ''),
        _art_v('disposicao_flores', $c['disposicao_flores'] ?? ''),
        _art_v('tamanho_flor',      $c['tamanho_flor']      ?? ''),
        _art_v('aroma',             $c['aroma']             ?? ''),
    ]);
    if ($comp) $frase .= ', ' . _art_lista(array_values($comp));
    return $frase . '.';
}

function _art_fruto(array $c): ?string {
    $tipo = _art_v('tipo_fruto', $c['tipo_fruto'] ?? '');
    if (!$tipo) return null;
    $frase = 'Os frutos são ' . $tipo;
    $comp = array_filter([
        _art_v('tamanho_fruto',   $c['tamanho_fruto']   ?? ''),
        _art_v('cor_fruto',       $c['cor_fruto']       ?? ''),
        _art_v('textura_fruto',   $c['textura_fruto']   ?? ''),
        _art_v('aroma_fruto',     $c['aroma_fruto']     ?? ''),
        _art_v('dispersao_fruto', $c['dispersao_fruto'] ?? ''),
    ]);
    if ($comp) $frase .= ', ' . _art_lista(array_values($comp));
    return $frase . '.';
}

function _art_semente(array $c): ?string {
    $tipo    = _art_v('tipo_semente',    $c['tipo_semente']    ?? '');
    $tamanho = _art_v('tamanho_semente', $c['tamanho_semente'] ?? '');
    $nucleo  = array_filter([$tipo, $tamanho]);
    if (!$nucleo) return null;
    $frase = 'As sementes são ' . _art_lista(array_values($nucleo));
    $comp = array_filter([
        _art_v('cor_semente',         $c['cor_semente']         ?? ''),
        _art_v('textura_semente',     $c['textura_semente']     ?? ''),
        _art_v('quantidade_sementes', $c['quantidade_sementes'] ?? ''),
    ]);
    if ($comp) $frase .= ', ' . _art_lista(array_values($comp));
    return $frase . '.';
}

function _art_outros(array $c): ?string {
    $itens = array_filter([
        _art_v('possui_espinhos', $c['possui_espinhos'] ?? ''),
        _art_v('possui_latex',    $c['possui_latex']    ?? ''),
        _art_v('possui_seiva',    $c['possui_seiva']    ?? ''),
        _art_v('possui_resina',   $c['possui_resina']   ?? ''),
    ]);
    if (!$itens) return null;
    return 'A espécie ' . _art_lista(array_values($itens)) . '.';
}

// ── Gerador principal ────────────────────────────────────────

function gerarHtmlArtigoRascunho(array $adm, array $c, array $imgs, PDO $pdo): string {
    $h = fn($s) => htmlspecialchars((string)($s ?? ''));
    ob_start();

    echo '<div class="artigo">';

    // ── Cabeçalho taxonômico
    $titulo = trim($c['nome_cientifico_completo'] ?? '') ?: $adm['nome_cientifico'];
    echo '<h2 class="art-titulo">' . $h($titulo) . '</h2>';

    if (trim($c['familia'] ?? '') !== '')
        echo '<p class="art-familia"><strong>Família:</strong> ' . $h($c['familia']) . '</p>';

    if (!empty($c['sinonimos'])) {
        $sins = implode(', ', array_map(
            fn($s) => '<em>' . $h(trim($s)) . '</em>',
            explode(',', $c['sinonimos'])
        ));
        echo '<p class="art-sinonimos"><strong>Sinonímia:</strong> ' . $sins . '</p>';
    }

    if (!empty($c['nome_popular']))
        echo '<p class="art-nomes"><strong>Nomes populares:</strong> ' . $h($c['nome_popular']) . '</p>';

    // ── Autores/contribuidores (acumulados conforme ações realizadas)
    $autores = montarAutoresArtigo($pdo, (int)$adm['id']);
    if ($autores) {
        $nomes = implode('; ', array_map(fn($a) => $h($a['nome']), $autores));
        echo '<p class="art-autores">' . $nomes . '</p>';
    }

    // ── Descrição
    echo '<h3 class="art-secao">Descrição</h3>';

    foreach (array_filter([
        _art_caule($c),
        _art_folha($c),
        _art_flor($c),
        _art_fruto($c),
        _art_semente($c),
        _art_outros($c),
    ]) as $paragrafo) {
        echo '<p class="art-paragrafo">' . $h($paragrafo) . '</p>';
    }

    // ── Prancha fotográfica
    if ($imgs) {
        echo '<h3 class="art-secao">Prancha Fotográfica</h3><div class="art-galeria">';
        foreach ($imgs as $img) {
            echo '<figure class="art-figura">';
            echo '<img src="/penomato_mvp/' . $h($img['caminho_imagem']) . '" alt="' . $h($img['parte_planta']) . '">';
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

    // ── Referências bibliográficas
    $refs_txt = trim($c['referencias'] ?? '');
    if ($refs_txt !== '') {
        $refs_map = [];
        foreach (preg_split('/(?=\n?\d+\.\s)/', $refs_txt, -1, PREG_SPLIT_NO_EMPTY) as $pt) {
            if (preg_match('/^(\d+)\.\s+(.+)$/s', trim($pt), $m))
                $refs_map[(int)$m[1]] = trim($m[2]);
        }
        if ($refs_map) {
            ksort($refs_map);
            echo '<h3 class="art-secao">Referências</h3><ol class="art-refs">';
            foreach ($refs_map as $n => $t)
                echo '<li id="ref-' . $n . '">' . $h($t) . '</li>';
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
