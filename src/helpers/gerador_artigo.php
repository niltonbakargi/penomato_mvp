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

/** Resolve valor no vocabulário e retorna <strong>valor</strong><sup>[N]</sup> */
function _art_vr(string $atrib, ?string $campo, string $ref = ''): ?string {
    $valor = _art_v($atrib, $campo);
    if ($valor === null) return null;
    $out = '<strong>' . htmlspecialchars($valor) . '</strong>';
    $ref = trim($ref);
    if ($ref !== '') {
        $nums = array_unique(array_filter(array_map('intval', explode(',', $ref))));
        sort($nums);
        if ($nums) $out .= '<sup class="art-ref">[' . implode(',', $nums) . ']</sup>';
    }
    return $out;
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
    $tipo = _art_vr('tipo_caule', $c['tipo_caule'] ?? '', $c['tipo_caule_ref'] ?? '');
    if (!$tipo) return null;
    $comp = array_filter([
        _art_vr('forma_caule',       $c['forma_caule']       ?? '', $c['forma_caule_ref']       ?? ''),
        _art_vr('textura_caule',     $c['textura_caule']     ?? '', $c['textura_caule_ref']     ?? ''),
        _art_vr('cor_caule',         $c['cor_caule']         ?? '', $c['cor_caule_ref']         ?? ''),
        _art_vr('ramificacao_caule', $c['ramificacao_caule'] ?? '', $c['ramificacao_caule_ref'] ?? ''),
        _art_vr('modificacao_caule', $c['modificacao_caule'] ?? '', $c['modificacao_caule_ref'] ?? ''),
    ]);
    $frase = 'O caule é ' . $tipo;
    if ($comp) $frase .= ', ' . _art_lista(array_values($comp));
    return $frase . '.';
}

function _art_folha(array $c): ?string {
    $tipo_val     = $c['tipo_folha']        ?? '';
    $divisao_val  = $c['divisao_folha']     ?? '';
    $paridade_val = $c['paridade_pinnacao'] ?? '';

    // Funde refs dos três campos para um único <sup>
    $ref_combinada = implode(';', array_filter([
        $c['tipo_folha_ref']        ?? '',
        $c['divisao_folha_ref']     ?? '',
        $c['paridade_pinnacao_ref'] ?? '',
    ]));

    $tipo = null;
    if ($tipo_val === 'Simples') {
        $tipo = _art_vr('tipo_folha', 'Simples', $ref_combinada);
    } elseif ($tipo_val === 'Composta') {
        if ($paridade_val) {
            // paridade é mais específica: "compostas paripinadas"
            $paridade_texto = _art_vr('paridade_pinnacao', $paridade_val, $ref_combinada);
            $tipo = $paridade_texto ? 'compostas ' . $paridade_texto : null;
        } elseif ($divisao_val) {
            $divisao_texto = _art_vr('divisao_folha', $divisao_val, $ref_combinada);
            $tipo = $divisao_texto ? 'compostas ' . $divisao_texto : _art_vr('tipo_folha', 'Composta', $ref_combinada);
        } else {
            $tipo = _art_vr('tipo_folha', 'Composta', $ref_combinada);
        }
    }

    $forma = _art_vr('forma_folha', $c['forma_folha'] ?? '', $c['forma_folha_ref'] ?? '');
    $nucleo = array_filter([$tipo, $forma]);
    if (!$nucleo) return null;
    $frase = 'As folhas são ' . _art_lista(array_values($nucleo));
    $comp = array_filter([
        _art_vr('textura_folha',   $c['textura_folha']   ?? '', $c['textura_folha_ref']   ?? ''),
        _art_vr('margem_folha',    $c['margem_folha']    ?? '', $c['margem_folha_ref']    ?? ''),
        _art_vr('venacao_folha',   $c['venacao_folha']   ?? '', $c['venacao_folha_ref']   ?? ''),
        _art_vr('filotaxia_folha', $c['filotaxia_folha'] ?? '', $c['filotaxia_folha_ref'] ?? ''),
        _art_vr('tamanho_folha',   $c['tamanho_folha']   ?? '', $c['tamanho_folha_ref']   ?? ''),
    ]);
    if ($comp) $frase .= ', ' . _art_lista(array_values($comp));
    return $frase . '.';
}

function _art_flor(array $c): ?string {
    $cor      = _art_vr('cor_flores',      $c['cor_flores']      ?? '', $c['cor_flores_ref']      ?? '');
    $simetria = _art_vr('simetria_floral', $c['simetria_floral'] ?? '', $c['simetria_floral_ref'] ?? '');
    $nucleo = array_filter([$cor, $simetria]);
    if (!$nucleo) return null;
    $frase = 'As flores são ' . _art_lista(array_values($nucleo));
    $comp = array_filter([
        _art_vr('numero_petalas',    $c['numero_petalas']    ?? '', $c['numero_petalas_ref']    ?? ''),
        _art_vr('disposicao_flores', $c['disposicao_flores'] ?? '', $c['disposicao_flores_ref'] ?? ''),
        _art_vr('tamanho_flor',      $c['tamanho_flor']      ?? '', $c['tamanho_flor_ref']      ?? ''),
        _art_vr('aroma',             $c['aroma']             ?? '', $c['aroma_ref']             ?? ''),
    ]);
    if ($comp) $frase .= ', ' . _art_lista(array_values($comp));
    return $frase . '.';
}

function _art_fruto(array $c): ?string {
    $tipo = _art_vr('tipo_fruto', $c['tipo_fruto'] ?? '', $c['tipo_fruto_ref'] ?? '');
    if (!$tipo) return null;
    $frase = 'Os frutos são ' . $tipo;
    $comp = array_filter([
        _art_vr('tamanho_fruto',   $c['tamanho_fruto']   ?? '', $c['tamanho_fruto_ref']   ?? ''),
        _art_vr('cor_fruto',       $c['cor_fruto']       ?? '', $c['cor_fruto_ref']       ?? ''),
        _art_vr('textura_fruto',   $c['textura_fruto']   ?? '', $c['textura_fruto_ref']   ?? ''),
        _art_vr('aroma_fruto',     $c['aroma_fruto']     ?? '', $c['aroma_fruto_ref']     ?? ''),
        _art_vr('dispersao_fruto', $c['dispersao_fruto'] ?? '', $c['dispersao_fruto_ref'] ?? ''),
    ]);
    if ($comp) $frase .= ', ' . _art_lista(array_values($comp));
    return $frase . '.';
}

function _art_semente(array $c): ?string {
    $tipo    = _art_vr('tipo_semente',    $c['tipo_semente']    ?? '', $c['tipo_semente_ref']    ?? '');
    $tamanho = _art_vr('tamanho_semente', $c['tamanho_semente'] ?? '', $c['tamanho_semente_ref'] ?? '');
    $nucleo  = array_filter([$tipo, $tamanho]);
    if (!$nucleo) return null;
    $frase = 'As sementes são ' . _art_lista(array_values($nucleo));
    $comp = array_filter([
        _art_vr('cor_semente',         $c['cor_semente']         ?? '', $c['cor_semente_ref']         ?? ''),
        _art_vr('textura_semente',     $c['textura_semente']     ?? '', $c['textura_semente_ref']     ?? ''),
        _art_vr('quantidade_sementes', $c['quantidade_sementes'] ?? '', $c['quantidade_sementes_ref'] ?? ''),
    ]);
    if ($comp) $frase .= ', ' . _art_lista(array_values($comp));
    return $frase . '.';
}

function _art_outros(array $c): ?string {
    $itens = array_filter([
        _art_vr('possui_espinhos', $c['possui_espinhos'] ?? '', $c['possui_espinhos_ref'] ?? ''),
        _art_vr('possui_latex',    $c['possui_latex']    ?? '', $c['possui_latex_ref']    ?? ''),
        _art_vr('possui_seiva',    $c['possui_seiva']    ?? '', $c['possui_seiva_ref']    ?? ''),
        _art_vr('possui_resina',   $c['possui_resina']   ?? '', $c['possui_resina_ref']   ?? ''),
    ]);
    if (!$itens) return null;
    return 'A espécie ' . _art_lista(array_values($itens)) . '.';
}

// ── Helper: coleta refs de uma parte e retorna <sup> ─────────

function _art_sup_refs(array $c, array $campos): string {
    $nums = [];
    foreach ($campos as $campo) {
        $r = trim($c[$campo . '_ref'] ?? '');
        if ($r === '') continue;
        foreach (explode(',', $r) as $n) {
            $n = (int)trim($n);
            if ($n > 0) $nums[$n] = true;
        }
    }
    if (!$nums) return '';
    ksort($nums);
    return '<sup class="art-ref">[' . implode(',', array_keys($nums)) . ']</sup>';
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

    // Os parágrafos já retornam HTML com <strong> e <sup> inline — não escapar
    foreach (array_filter([
        _art_caule($c),
        _art_folha($c),
        _art_flor($c),
        _art_fruto($c),
        _art_semente($c),
        _art_outros($c),
    ]) as $paragrafo) {
        echo '<p class="art-paragrafo">' . $paragrafo . '</p>';
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

        $existe_artigo = $pdo->prepare("SELECT id FROM artigos WHERE especie_id = ? LIMIT 1");
        $existe_artigo->execute([$especie_id]);
        if ($existe_artigo->fetch()) {
            $pdo->prepare("UPDATE artigos SET texto_html = ?, atualizado_em = NOW() WHERE especie_id = ?")
                ->execute([$html, $especie_id]);
        } else {
            $pdo->prepare("INSERT INTO artigos (especie_id, texto_html, status, criado_em, atualizado_em) VALUES (?, ?, 'rascunho', NOW(), NOW())")
                ->execute([$especie_id, $html]);
        }

        error_log("regenerarArtigoEspecie: artigo ID={$especie_id} atualizado (" . strlen($html) . " bytes)");
    } catch (Exception $e) {
        error_log("regenerarArtigoEspecie: erro ID={$especie_id} — " . $e->getMessage());
    }
}
