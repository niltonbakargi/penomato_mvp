<?php
// ================================================
// RESULTADO DA BUSCA POR CARACTERÍSTICAS
// ================================================

session_start();
require_once __DIR__ . '/../../../config/banco_de_dados.php';

// Apenas aceita POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE . '/src/Views/publico/busca_caracteristicas.php');
    exit;
}

// ================================================
// MONTAR WHERE DINAMICAMENTE
// ================================================

$campos_like = ['nome_cientifico_completo', 'nome_popular', 'familia'];
$todos_campos = [
    'nome_cientifico_completo', 'nome_popular', 'familia',
    'forma_folha', 'filotaxia_folha', 'tipo_folha', 'divisao_folha', 'paridade_pinnacao',
    'tamanho_folha', 'textura_folha', 'margem_folha', 'venacao_folha',
    'cor_flores', 'simetria_floral', 'numero_petalas', 'tamanho_flor', 'disposicao_flores', 'aroma',
    'tipo_fruto', 'tamanho_fruto', 'cor_fruto', 'textura_fruto', 'dispersao_fruto', 'aroma_fruto',
    'tipo_semente', 'tamanho_semente', 'cor_semente', 'textura_semente', 'quantidade_sementes',
    'tipo_caule', 'textura_caule', 'cor_caule', 'forma_caule', 'modificacao_caule',
    'ramificacao_caule', 'possui_espinhos', 'possui_latex'
];

// Mostrar todas as espécies com ao menos dados_internet
$condicoes = ["e.status != 'sem_dados'"];
$parametros = [];

foreach ($todos_campos as $campo) {
    $val = isset($_POST[$campo]) ? trim($_POST[$campo]) : '';
    if ($val === '' || $val === 'todos') continue;
    if (in_array($campo, $campos_like)) {
        $condicoes[] = "c.$campo LIKE ?";
        $parametros[] = '%' . $val . '%';
    } else {
        $condicoes[] = "c.$campo = ?";
        $parametros[] = $val;
    }
}

$where_sql = 'WHERE ' . implode(' AND ', $condicoes);

// ================================================
// QUERY PRINCIPAL — espécies com características
// ================================================

$sql = "SELECT
            e.id,
            e.nome_cientifico,
            e.status,
            c.nome_cientifico_completo,
            c.nome_popular,
            c.familia,
            c.sinonimos,
            c.referencias,
            c.forma_folha, c.filotaxia_folha, c.tipo_folha, c.divisao_folha, c.paridade_pinnacao,
            c.tamanho_folha, c.textura_folha, c.margem_folha, c.venacao_folha,
            c.cor_flores, c.simetria_floral, c.numero_petalas, c.tamanho_flor, c.disposicao_flores, c.aroma,
            c.tipo_fruto, c.tamanho_fruto, c.cor_fruto, c.textura_fruto, c.dispersao_fruto, c.aroma_fruto,
            c.tipo_semente, c.tamanho_semente, c.cor_semente, c.textura_semente, c.quantidade_sementes,
            c.tipo_caule, c.textura_caule, c.cor_caule, c.forma_caule, c.modificacao_caule,
            c.ramificacao_caule,
            c.possui_espinhos, c.possui_latex, c.possui_seiva, c.possui_resina
        FROM especies_caracteristicas c
        INNER JOIN especies_administrativo e ON c.especie_id = e.id
        $where_sql
        ORDER BY c.nome_cientifico_completo
        LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    header('Location: ' . APP_BASE . '/src/Views/publico/busca_caracteristicas.php?sem_resultado=1');
    exit;
}

// ================================================
// MONTAR ARRAY $especies
// ================================================

$especies = [];
$ids = [];

foreach ($rows as $r) {
    $esp = [
        'id'          => $r['id'],
        'nome'        => !empty($r['nome_cientifico_completo']) ? $r['nome_cientifico_completo'] : $r['nome_cientifico'],
        'nome_popular'=> $r['nome_popular'] ?? '',
        'familia'     => $r['familia'] ?? '',
        'sinonimos'   => $r['sinonimos'] ?? '',
        'referencias' => $r['referencias'] ?? '',
        'status'      => $r['status'],
        'folha' => array_filter([
            'Forma'     => $r['forma_folha'],
            'Filotaxia' => $r['filotaxia_folha'],
            'Tipo'      => $r['tipo_folha'],
            'Divisão'   => $r['divisao_folha'],
            'Paridade'  => $r['paridade_pinnacao'],
            'Tamanho'   => $r['tamanho_folha'],
            'Textura'   => $r['textura_folha'],
            'Margem'    => $r['margem_folha'],
            'Venação'   => $r['venacao_folha'],
        ]),
        'flor' => array_filter([
            'Cor'        => $r['cor_flores'],
            'Simetria'   => $r['simetria_floral'],
            'Pétalas'    => $r['numero_petalas'],
            'Tamanho'    => $r['tamanho_flor'],
            'Disposição' => $r['disposicao_flores'],
            'Aroma'      => $r['aroma'],
        ]),
        'fruto' => array_filter([
            'Tipo'      => $r['tipo_fruto'],
            'Tamanho'   => $r['tamanho_fruto'],
            'Cor'       => $r['cor_fruto'],
            'Textura'   => $r['textura_fruto'],
            'Dispersão' => $r['dispersao_fruto'],
            'Aroma'     => $r['aroma_fruto'],
        ]),
        'semente' => array_filter([
            'Tipo'       => $r['tipo_semente'],
            'Tamanho'    => $r['tamanho_semente'],
            'Cor'        => $r['cor_semente'],
            'Textura'    => $r['textura_semente'],
            'Quantidade' => $r['quantidade_sementes'],
        ]),
        'caule' => array_filter([
            'Tipo'        => $r['tipo_caule'],
            'Textura'     => $r['textura_caule'],
            'Cor'         => $r['cor_caule'],
            'Forma'       => $r['forma_caule'],
            'Ramificação' => $r['ramificacao_caule'],
            'Modificação' => $r['modificacao_caule'],
        ]),
        'outras' => array_filter([
            'Espinhos' => $r['possui_espinhos'],
            'Látex'    => $r['possui_latex'],
            'Seiva'    => $r['possui_seiva'],
            'Resina'   => $r['possui_resina'],
        ]),
    ];
    $especies[] = $esp;
    $ids[] = (int)$r['id'];
}

$total = count($especies);

// ================================================
// QUERY IMAGENS
// ================================================

$imagens = [];
if (!empty($ids)) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt_img = $pdo->prepare("SELECT especie_id, parte_planta, caminho_imagem,
            fonte_nome, fonte_url, autor_imagem, licenca
        FROM especies_imagens
        WHERE especie_id IN ($placeholders)
        ORDER BY especie_id, data_upload DESC");
    $stmt_img->execute($ids);
    $raiz_fisica = __DIR__ . '/../../../';
    foreach ($stmt_img->fetchAll(PDO::FETCH_ASSOC) as $img) {
        // Só exibe imagens cujo arquivo existe em disco
        if (!file_exists($raiz_fisica . $img['caminho_imagem'])) continue;
        $imagens[$img['especie_id']][$img['parte_planta']][] = [
            'url'       => '/penomato_mvp/' . $img['caminho_imagem'],
            'fonte'     => $img['fonte_nome']    ?? '',
            'fonte_url' => $img['fonte_url']     ?? '',
            'autor'     => $img['autor_imagem']  ?? '',
            'licenca'   => $img['licenca']       ?? '',
        ];
    }
}

// ================================================
// QUERY EXEMPLARES
// ================================================

$exemplares = [];
try {
    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt_ex = $pdo->prepare("SELECT especie_id, latitude, longitude, cidade, estado, codigo
            FROM exemplares
            WHERE especie_id IN ($placeholders)
              AND latitude IS NOT NULL
              AND longitude IS NOT NULL");
        $stmt_ex->execute($ids);
        foreach ($stmt_ex->fetchAll(PDO::FETCH_ASSOC) as $ex) {
            $exemplares[$ex['especie_id']][] = [
                'lat'    => $ex['latitude'],
                'lng'    => $ex['longitude'],
                'cidade' => $ex['cidade'],
                'estado' => $ex['estado'],
                'codigo' => $ex['codigo'],
            ];
        }
    }
} catch (Exception $e) {
    // Tabela exemplares pode não existir ainda
    $exemplares = [];
}

// ================================================
// QUERY ARTIGOS
// ================================================
$artigos_map = [];
if (!empty($ids)) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt_art = $pdo->prepare("SELECT especie_id, texto_html, status FROM artigos WHERE especie_id IN ($placeholders)");
    $stmt_art->execute($ids);
    foreach ($stmt_art->fetchAll(PDO::FETCH_ASSOC) as $art) {
        $artigos_map[$art['especie_id']] = ['html' => $art['texto_html'], 'status' => $art['status']];
    }
}
foreach ($especies as &$esp) {
    $art = $artigos_map[$esp['id']] ?? null;
    $esp['artigo_html']   = $art ? $art['html']   : null;
    $esp['artigo_status'] = $art ? $art['status']  : null;
}
unset($esp);

// ================================================
// QUERY AUTORES (identificadores + orientador)
// ================================================
$autores_map = [];
if (!empty($ids)) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt_aut = $pdo->prepare("
        SELECT
            ea.id AS especie_id,
            u1.nome        AS nome_identificador,
            u1.instituicao AS inst_identificador,
            u2.nome        AS nome_orientador,
            u2.instituicao AS inst_orientador,
            u2.subtipo_colaborador AS subtipo_orientador
        FROM especies_administrativo ea
        LEFT JOIN usuarios u1 ON u1.id = ea.autor_dados_internet_id
        LEFT JOIN usuarios u2 ON u2.id = ea.atribuido_a
        WHERE ea.id IN ($placeholders)
    ");
    $stmt_aut->execute($ids);
    foreach ($stmt_aut->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $autores = [];
        if (!empty($row['nome_identificador'])) {
            $autores[] = [
                'nome'  => $row['nome_identificador'],
                'inst'  => $row['inst_identificador'] ?? '',
                'papel' => 'Identificador',
            ];
        }
        if (!empty($row['nome_orientador'])) {
            $autores[] = [
                'nome'  => $row['nome_orientador'],
                'inst'  => $row['inst_orientador'] ?? '',
                'papel' => 'Especialista',
            ];
        }
        $autores_map[$row['especie_id']] = $autores;
    }
}
foreach ($especies as &$esp) {
    $esp['autores'] = $autores_map[$esp['id']] ?? [];
}
unset($esp);

$j_especies   = json_encode(array_values($especies), JSON_UNESCAPED_UNICODE);
$j_imagens    = json_encode($imagens, JSON_UNESCAPED_UNICODE);
$j_exemplares = json_encode($exemplares, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espécies — Penomato</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha384-blOohCVdhjmtROpu8+CfTnUWham9nkX7P7OZQMst+RUnhtoY/9qemFAkIKOYxDI3" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= APP_BASE ?>/assets/css/estilo.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,400;1,700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f5f0ea;
            color: #1a1a1a;
            margin: 0;
            font-family: 'DM Sans', sans-serif;
        }

        /* ── HEADER ── */
        .header {
            height: 56px;
            background: var(--cor-primaria);
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
        }
        .header-logo { font-size: 1.1rem; font-weight: 700; color: white; display: flex; align-items: center; gap: 8px; }
        .header-count { font-size: .85rem; color: rgba(255,255,255,.75); }
        .btn-voltar {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,.15); color: white;
            text-decoration: none; padding: 7px 16px;
            border-radius: 30px; font-size: .875rem; font-weight: 600;
            transition: background .2s;
        }
        .btn-voltar:hover { background: rgba(255,255,255,.28); }

        /* ── WRAPPER PRINCIPAL ── */
        .paginas { max-width: 1060px; margin: 0 auto; padding: 32px 20px 80px; display: flex; flex-direction: column; gap: 56px; }

        /* ── FICHA (cada espécie = uma página) ── */
        .ficha {
            background: white;
            border-radius: 4px;
            box-shadow: 0 4px 24px rgba(0,0,0,.13), 0 1px 4px rgba(0,0,0,.07);
            overflow: hidden;
            border-top: 5px solid var(--cor-primaria);
            position: relative;
        }
        /* Efeito de páginas empilhadas atrás da ficha */
        .ficha::before,
        .ficha::after {
            content: '';
            position: absolute;
            left: 6px; right: 6px; bottom: -6px;
            height: 100%;
            background: #e8e2d9;
            border-radius: 4px;
            z-index: -1;
            box-shadow: 0 4px 12px rgba(0,0,0,.08);
        }
        .ficha::after {
            left: 12px; right: 12px; bottom: -11px;
            background: #ddd7cd;
        }

        /* Cabeçalho da ficha */
        .ficha-cab {
            padding: 24px 32px 18px;
            border-bottom: 1px solid #e2e8f0;
        }
        .ficha-familia {
            font-family: 'DM Sans', sans-serif;
            font-size: .72rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: .14em;
            color: #a0876a; margin-bottom: 6px;
        }
        .ficha-popular {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem; font-weight: 800;
            color: #1a1a1a; line-height: 1.15; margin-bottom: 6px;
        }
        .ficha-cientifico {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem; font-style: italic;
            color: var(--cor-primaria); margin-bottom: 10px;
            display: flex; align-items: center; gap: 8px;
        }
        .btn-falar {
            background: none; border: none; cursor: pointer;
            color: var(--cor-primaria); opacity: 0.55;
            font-size: 0.85rem; padding: 2px 4px;
            transition: opacity .2s;
            flex-shrink: 0;
        }
        .btn-falar:hover { opacity: 1; }
        .btn-falar.falando { opacity: 1; color: #e07b00; animation: pulsar .6s infinite alternate; }
        @keyframes pulsar { from { opacity: .6; } to { opacity: 1; } }
        .ficha-status {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 3px 12px; border-radius: 20px;
            font-size: .75rem; font-weight: 700;
        }
        .ficha-meta-linha {
            display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
            margin-top: 10px;
        }
        .ficha-autor {
            font-size: .8rem; color: #64748b;
            display: flex; align-items: center; gap: 5px;
        }
        .st-publicado      { background: #dcfce7; color: #15803d; }
        .st-revisada       { background: #dbeafe; color: #1d4ed8; }
        .st-em_revisao     { background: #ede9fe; color: #6d28d9; }
        .st-registrada     { background: #cffafe; color: #0e7490; }
        .st-descrita       { background: #fef3c7; color: #92400e; }
        .st-dados_internet { background: #f1f5f9; color: #475569; }
        .st-contestado     { background: #fee2e2; color: #991b1b; }

        /* Corpo: coluna única */
        .ficha-corpo {
            display: flex;
            flex-direction: column;
        }

        /* ── TEXTO DO ARTIGO ── */
        .ficha-attrs {
            padding: 24px 32px;
            border-bottom: 1px solid #e2e8f0;
        }
        .attr-grupo { }
        .attr-grupo-titulo {
            font-size: .68rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: .1em;
            color: #94a3b8; margin-bottom: 8px;
            display: flex; align-items: center; gap: 6px;
        }
        .attr-linha {
            display: flex; align-items: baseline; gap: 8px;
            padding: 5px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .attr-label {
            font-size: .75rem; color: #94a3b8;
            font-weight: 700; min-width: 90px; flex-shrink: 0;
        }
        .attr-val { font-size: .88rem; color: #1a2634; font-weight: 500; }
        .ficha-sem-attrs {
            color: #94a3b8; font-size: .9rem; font-style: italic;
            padding: 12px 0;
        }

        /* Artigo dentro da ficha: tipografia limpa, sem títulos duplos */
        .ficha-artigo-texto { overflow-y: auto; max-height: 600px; }
        .ficha-artigo-texto .art-titulo,
        .ficha-artigo-texto .art-familia,
        .ficha-artigo-texto .art-sinonimos,
        .ficha-artigo-texto .art-nomes    { display: none; } /* já estão no cabeçalho da ficha */
        .ficha-artigo-texto .art-secao {
            font-family: 'Playfair Display', serif;
            font-size: 1rem; font-weight: 700; font-style: italic;
            color: var(--cor-primaria); margin: 22px 0 8px;
            border-bottom: 1px solid #e5ddd4; padding-bottom: 4px;
            letter-spacing: 0;
        }
        .ficha-artigo-texto .art-paragrafo {
            font-family: 'DM Sans', sans-serif;
            font-size: .92rem; line-height: 1.8;
            color: #2c2c2c; text-align: justify;
            text-indent: 1.2em; margin-bottom: 0;
        }
        .ficha-artigo-texto .art-galeria,
        .ficha-artigo-texto .art-refs,
        .ficha-artigo-texto .art-autores,
        .ficha-artigo-texto style       { display: none; }

        /* ── GALERIA: linha por parte ── */
        .ficha-imgs {
            background: #f0ebe3;
            padding: 20px 32px;
            display: flex; flex-direction: column; gap: 16px;
        }

        /* Galeria de miniaturas por parte */
        .galeria { display: flex; flex-direction: column; gap: 10px; }
        .galeria-parte { }
        .galeria-parte-label {
            font-family: 'DM Sans', sans-serif;
            font-size: .65rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .12em;
            color: #a0876a; margin-bottom: 5px;
        }
        .galeria-thumbs { display: flex; flex-wrap: nowrap; gap: 8px; }
        .thumb {
            width: 140px; height: 110px; border-radius: 8px;
            overflow: hidden; cursor: zoom-in;
            border: 2px solid transparent;
            transition: border-color .15s, transform .15s;
            flex-shrink: 0;
        }
        .thumb:hover { border-color: var(--cor-primaria); transform: scale(1.03); }
        .thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }

        /* ── RODAPÉ DA FICHA ── */
        .ficha-rodape {
            padding: 14px 32px;
            border-top: 1px solid #e5ddd4;
            background: #f0ebe3;
            display: flex; align-items: center; justify-content: flex-end;
            gap: 12px;
        }
        .btn-artigo-completo {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--cor-primaria); color: white;
            text-decoration: none; padding: 9px 22px;
            border-radius: 30px; font-weight: 700; font-size: .875rem;
            transition: background .2s;
        }
        .btn-artigo-completo:hover { background: var(--cor-primaria-hover); }
        .btn-sem-artigo { font-size: .8rem; color: #94a3b8; font-style: italic; }
        .art-st-rascunho   { background: #64748b; }
        .art-st-rascunho:hover { background: #475569; }
        .art-st-revisao    { background: #7c3aed; }
        .art-st-revisao:hover  { background: #6d28d9; }
        .art-st-aprovado   { background: #0891b2; }
        .art-st-aprovado:hover { background: #0e7490; }
        .art-st-publicado  { background: var(--cor-primaria); }
        .art-st-publicado:hover { background: var(--cor-primaria-hover); }

        /* ── LIGHTBOX ── */
        .lightbox {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.93); z-index: 9999;
            align-items: center; justify-content: center;
            flex-direction: column; padding: 20px;
        }
        .lightbox.ativo { display: flex; }
        .lightbox img {
            max-width: 92vw; max-height: 84vh;
            object-fit: contain; border-radius: 6px;
        }
        .lightbox-credito {
            color: #cbd5e1; font-size: .78rem;
            margin-top: 12px; text-align: center; line-height: 1.6;
        }
        .lightbox-fechar {
            position: absolute; top: 16px; right: 20px;
            color: white; font-size: 1.8rem; cursor: pointer;
            background: none; border: none; line-height: 1;
            opacity: .7; transition: opacity .2s;
        }
        .lightbox-fechar:hover { opacity: 1; }

        /* ── RESPONSIVE ── */
        @media (max-width: 640px) {
            .ficha-cab, .ficha-attrs, .ficha-imgs { padding-left: 16px; padding-right: 16px; }
            .ficha-popular { font-size: 1.4rem; }
            .thumb { width: 100px; height: 80px; }
        }

        /* ── HEADER ── */
        .header {
            height: 56px;
            background: var(--cor-primaria);
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            gap: 16px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 0;
        }

        .header-logo {
            font-size: 1.25rem;
            font-weight: 700;
            color: white;
            white-space: nowrap;
        }

        .header-count {
            font-size: .9rem;
            color: rgba(255,255,255,.85);
            white-space: nowrap;
        }

        .btn-voltar {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,.15);
            color: white;
            text-decoration: none;
            padding: 7px 16px;
            border-radius: 30px;
            font-size: .9rem;
            font-weight: 600;
            white-space: nowrap;
            transition: background .2s;
        }

        .btn-voltar:hover {
            background: rgba(255,255,255,.28);
        }

        /* ── LAYOUT ── */
        .layout {
            display: flex;
            align-items: flex-start;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: 280px;
            flex-shrink: 0;
            position: sticky;
            top: 56px;
            height: calc(100vh - 56px);
            overflow-y: auto;
            background: white;
            border-right: 1px solid var(--cinza-200);
        }

        .sidebar-header {
            padding: 16px 20px;
            font-weight: 700;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #64748b;
            border-bottom: 1px solid var(--cinza-200);
            position: sticky;
            top: 0;
            background: white;
            z-index: 1;
        }

        .especie-item {
            display: block;
            width: 100%;
            text-align: left;
            padding: 12px 20px;
            border: none;
            background: none;
            cursor: pointer;
            font-size: .88rem;
            font-style: italic;
            color: #1a2634;
            border-left: 4px solid transparent;
            transition: background .15s, border-color .15s;
        }

        .especie-item:hover {
            background: var(--cinza-50);
        }

        .especie-item.ativo {
            background: var(--verde-50);
            border-left-color: var(--cor-primaria);
            color: var(--cor-primaria);
            font-weight: 600;
        }

        /* ── PAINEL ARTIGO ── */
        .painel {
            flex: 1;
            padding: 28px 32px;
            min-width: 0;
        }

        .artigo-nome {
            font-size: 1.8rem;
            font-style: italic;
            color: var(--cor-primaria);
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .artigo-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
        }

        .badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 30px;
            font-size: .82rem;
            font-weight: 600;
        }

        .badge-familia {
            background: var(--verde-50);
            color: var(--cor-primaria-hover);
        }

        .badge-popular {
            background: #e0f2fe;
            color: #075985;
        }

        .badge-sinonimo {
            background: #f3e8ff;
            color: #6b21a8;
            font-weight: 400;
            font-style: italic;
        }

        /* ── CARROSSEL ── */
        .carrossel-wrapper {
            position: relative;
            max-width: 100%;
            margin-bottom: 16px;
        }

        .carrossel-tela {
            background: #1a2634;
            border-radius: 12px;
            aspect-ratio: 16/9;
            max-height: 340px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .carrossel-tela img {
            max-width: 100%;
            max-height: 340px;
            object-fit: contain;
        }

        .carrossel-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            color: rgba(255,255,255,.35);
        }

        .carrossel-placeholder i {
            font-size: 2.5rem;
        }

        .carrossel-placeholder span {
            font-size: .85rem;
        }

        .nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: none;
            background: rgba(0,0,0,.6);
            color: white;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            transition: background .2s;
        }

        .nav-btn:hover {
            background: rgba(0,0,0,.85);
        }

        .nav-prev { left: 8px; }
        .nav-next { right: 8px; }

        .carrossel-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 10px;
            flex-wrap: wrap;
            gap: 8px;
        }

        .partes-btns {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .parte-btn {
            padding: 5px 14px;
            border-radius: 30px;
            border: 2px solid var(--cinza-300);
            background: white;
            font-size: .8rem;
            cursor: pointer;
            font-weight: 600;
            transition: all .15s;
        }

        .parte-btn:hover {
            border-color: var(--cor-primaria);
            color: var(--cor-primaria);
        }

        .parte-btn.ativo {
            background: var(--cor-primaria);
            border-color: var(--cor-primaria);
            color: white;
        }

        .carrossel-counter {
            font-size: .82rem;
            color: #64748b;
            white-space: nowrap;
        }

        /* ── BTN MAPA ── */
        .btn-mapa {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #2563eb;
            color: white;
            padding: 10px 22px;
            border-radius: 30px;
            border: none;
            font-size: .95rem;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 24px;
            transition: background .2s;
        }

        .btn-mapa:hover {
            background: #1d4ed8;
        }

        /* ══════════════════════════════════════════
           CARD DO ARTIGO — tipografia e layout
        ══════════════════════════════════════════ */

        /* Título da espécie: centralizado, escuro, científico */
        .artigo-preview-card .art-titulo {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 1.35rem;
            font-weight: 700;
            font-style: italic;
            color: #1a2634;
            text-align: center;
            margin-bottom: 6px;
        }

        /* Família, sinônimos, nomes populares: centralizados */
        .artigo-preview-card .art-familia,
        .artigo-preview-card .art-sinonimos,
        .artigo-preview-card .art-nomes {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 0.9rem;
            color: #475569;
            text-align: center;
            margin-bottom: 4px;
        }

        /* Seções: Georgia, verde, linha separadora */
        .artigo-preview-card .art-secao {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--cor-primaria);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin: 28px 0 10px;
            border-bottom: 1px solid var(--cinza-200);
            padding-bottom: 5px;
        }

        /* Parágrafos: recuo, justificado, serif */
        .artigo-preview-card .art-paragrafo {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 0.95rem;
            line-height: 1.85;
            text-align: justify;
            text-indent: 1.5em;
            color: #1e293b;
            margin-bottom: 0;
        }
        .artigo-preview-card .art-paragrafo sup {
            font-size: 0.7rem;
            color: var(--cor-primaria);
            font-family: var(--fonte-principal);
        }

        /* Prancha fotográfica: grid 2 colunas */
        .artigo-preview-card .art-galeria {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 16px !important;
            margin: 16px 0 !important;
        }
        .artigo-preview-card .art-figura {
            border: 1px solid var(--cinza-200) !important;
            border-radius: 8px !important;
            overflow: hidden !important;
            background: #fafafa !important;
            text-align: left !important;
        }
        .artigo-preview-card .art-figura-titulo {
            font-family: var(--fonte-principal);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            padding: 8px 10px 4px;
        }
        .artigo-preview-card .art-figura img {
            width: 100% !important;
            height: auto !important;
            object-fit: contain !important;
            border-radius: 0 !important;
            display: block !important;
        }
        .artigo-preview-card .art-figura figcaption {
            font-family: var(--fonte-principal) !important;
            font-size: 0.75rem !important;
            font-style: italic;
            color: #64748b !important;
            padding: 6px 10px 10px !important;
            line-height: 1.5 !important;
        }
        @media (max-width: 600px) {
            .artigo-preview-card .art-galeria { grid-template-columns: 1fr !important; }
        }

        /* Referências */
        .artigo-preview-card .art-refs {
            font-family: Georgia, 'Times New Roman', serif !important;
            font-size: 0.83rem !important;
            color: #475569 !important;
            line-height: 1.7 !important;
            padding-left: 20px !important;
        }
        .artigo-preview-card .art-refs li {
            margin-bottom: 5px !important;
        }

        /* Autores: bloco discreto abaixo do cabeçalho */
        .art-autores {
            font-family: var(--fonte-principal);
            font-size: 0.8rem;
            color: #64748b;
            margin: 10px 0 20px;
            padding: 8px 14px;
            background: #f8fafc;
            border: 1px solid var(--cinza-200);
            border-radius: 6px;
            line-height: 1.6;
            text-align: center;
        }
        .art-autores-label { font-weight: 700; color: #475569; }
        .art-autores-inst  { color: #94a3b8; }
        .art-autores-papel { font-style: italic; color: #94a3b8; }

        /* ── ABAS ARTIGO / ATRIBUTOS ── */
        .artigo-tabs {
            display: flex;
            gap: 4px;
            margin-bottom: 16px;
            border-bottom: 2px solid var(--cinza-200);
        }
        .artigo-tab {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 9px 20px;
            font-size: .88rem;
            font-weight: 700;
            border: none;
            background: none;
            color: #64748b;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: color .15s, border-color .15s;
        }
        .artigo-tab:hover { color: var(--cor-primaria); }
        .artigo-tab.ativo {
            color: var(--cor-primaria);
            border-bottom-color: var(--cor-primaria);
        }

        /* ── PIPELINE DE STATUS ── */
        .artigo-pipeline {
            display: flex;
            align-items: center;
            gap: 0;
            margin-bottom: 20px;
            overflow-x: auto;
            padding: 12px 16px;
            background: #f8fafc;
            border: 1px solid var(--cinza-200);
            border-radius: 10px;
            font-size: .78rem;
        }
        .pipeline-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            flex: 1;
            min-width: 70px;
            position: relative;
        }
        .pipeline-step::after {
            content: '';
            position: absolute;
            top: 12px;
            left: calc(50% + 14px);
            right: calc(-50% + 14px);
            height: 2px;
            background: var(--cinza-200);
        }
        .pipeline-step:last-child::after { display: none; }
        .pipeline-dot {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--cinza-200);
            border: 2px solid var(--cinza-200);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .65rem;
            color: #94a3b8;
            font-weight: 700;
            z-index: 1;
            transition: all .2s;
        }
        .pipeline-step.feito .pipeline-dot {
            background: var(--cor-primaria);
            border-color: var(--cor-primaria);
            color: white;
        }
        .pipeline-step.atual .pipeline-dot {
            background: white;
            border-color: var(--cor-primaria);
            color: var(--cor-primaria);
            box-shadow: 0 0 0 3px rgba(11,94,66,.15);
        }
        .pipeline-step::after {
            background: var(--cinza-200);
        }
        .pipeline-step.feito::after {
            background: var(--cor-primaria);
        }
        .pipeline-label {
            font-size: .7rem;
            color: #94a3b8;
            text-align: center;
            font-weight: 600;
            white-space: nowrap;
        }
        .pipeline-step.feito .pipeline-label,
        .pipeline-step.atual .pipeline-label {
            color: var(--cor-primaria);
        }

        /* ── CARD DO ARTIGO ── */
        .artigo-preview-card {
            background: white;
            border: 1px solid var(--cinza-200);
            border-radius: 12px;
            padding: 40px 48px;
            font-family: Georgia, 'Times New Roman', serif;
            line-height: 1.85;
        }
        @media (max-width: 640px) {
            .artigo-preview-card { padding: 24px 20px; }
        }
        .artigo-sem-dados {
            text-align: center;
            padding: 48px 20px;
            color: #94a3b8;
            font-family: var(--fonte-principal);
        }
        .artigo-sem-dados i { font-size: 2rem; margin-bottom: 12px; display: block; }

        /* ── CARACTERÍSTICAS ── */
        .caract-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .caract-secao {
            border: 1px solid var(--cinza-200);
            border-radius: 10px;
            overflow: hidden;
        }

        .caract-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: var(--cinza-50);
            border-left: 4px solid var(--cor-primaria);
            cursor: pointer;
            user-select: none;
        }

        .caract-header span {
            font-weight: 700;
            font-size: .92rem;
            color: var(--cor-primaria);
            flex: 1;
        }

        .caract-header i.toggle-icon {
            font-size: .8rem;
            color: #94a3b8;
            transition: transform .2s;
        }

        .caract-header.aberto i.toggle-icon {
            transform: rotate(180deg);
        }

        .caract-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0;
        }

        .caract-item {
            padding: 10px 16px;
            border-top: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
        }

        .caract-label {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #94a3b8;
            font-weight: 700;
            margin-bottom: 3px;
        }

        .caract-valor {
            font-size: .92rem;
            color: #1a2634;
            font-weight: 500;
        }

        .referencias-texto {
            padding: 12px 16px;
            font-size: .88rem;
            color: #4b5563;
            line-height: 1.6;
            font-style: italic;
        }

        /* ── MAPA SECAO ── */
        .mapa-secao {
            display: none;
            border-top: 3px solid #2563eb;
            background: white;
        }

        .mapa-secao.visivel {
            display: block;
        }

        .mapa-titulo {
            padding: 14px 24px;
            font-weight: 700;
            font-size: 1rem;
            color: #1e3a8a;
            background: #eff6ff;
        }

        #mapa-leaflet {
            height: 400px;
            width: 100%;
        }

        /* ── BROWSE SECAO ── */
        .browse-secao {
            background: white;
            border-top: 3px solid var(--cor-primaria);
            padding: 24px 32px;
        }

        .browse-titulo {
            font-size: 1rem;
            font-weight: 700;
            color: var(--cor-primaria);
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .browse-partes {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 18px;
        }

        .browse-parte-btn {
            padding: 7px 18px;
            border-radius: 30px;
            border: 2px solid var(--cinza-200);
            background: white;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s;
        }

        .browse-parte-btn:hover {
            border-color: var(--cor-primaria);
            color: var(--cor-primaria);
        }

        .browse-parte-btn.ativo {
            background: var(--cor-primaria);
            border-color: var(--cor-primaria);
            color: white;
        }

        .browse-trilha {
            display: flex;
            gap: 12px;
            overflow-x: auto;
            padding-bottom: 10px;
            scrollbar-width: thin;
            scrollbar-color: var(--cor-primaria) #e2e8f0;
        }

        .browse-trilha::-webkit-scrollbar {
            height: 5px;
        }

        .browse-trilha::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 3px;
        }

        .browse-trilha::-webkit-scrollbar-thumb {
            background: var(--cor-primaria);
            border-radius: 3px;
        }

        .browse-card {
            flex-shrink: 0;
            width: 180px;
            background: var(--cinza-50);
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color .15s, box-shadow .15s;
        }

        .browse-card:hover {
            border-color: var(--cor-primaria);
            box-shadow: 0 4px 12px rgba(11,94,66,.15);
        }

        .browse-card-img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            display: block;
        }

        .browse-card-placeholder {
            width: 100%;
            height: 120px;
            background: #1a2634;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,.25);
            font-size: 1.8rem;
        }

        .browse-card-nome {
            padding: 8px 10px;
            font-style: italic;
            font-size: .78rem;
            color: #374151;
            line-height: 1.4;
        }

        /* ── STATUS BADGES (sidebar) ── */
        .status-dot {
            display: inline-block;
            width: 8px; height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
            margin-top: 4px;
        }
        .status-dot.publicado      { background: #16a34a; }
        .status-dot.revisada       { background: #2563eb; }
        .status-dot.em_revisao     { background: #7c3aed; }
        .status-dot.registrada     { background: #0891b2; }
        .status-dot.descrita       { background: #d97706; }
        .status-dot.dados_internet { background: #9ca3af; }
        .status-dot.contestado     { background: #dc2626; }

        .especie-item {
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .especie-item-texto { flex: 1; min-width: 0; text-align: left; }
        .especie-item-nome { display: block; font-style: italic; font-weight: 600; font-size: .88rem; color: #1e293b; }
        .especie-item-popular { display: block; font-size: .75rem; color: #64748b; margin-top: 1px; }

        /* ── STATUS BANNER no painel ── */
        .status-banner {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 700;
            margin-bottom: 14px;
        }
        .status-banner.publicado      { background: #dcfce7; color: #15803d; }
        .status-banner.revisada       { background: #dbeafe; color: #1d4ed8; }
        .status-banner.em_revisao     { background: #ede9fe; color: #6d28d9; }
        .status-banner.registrada     { background: #cffafe; color: #0e7490; }
        .status-banner.descrita       { background: #fef3c7; color: #92400e; }
        .status-banner.dados_internet { background: #f1f5f9; color: #475569; }
        .status-banner.contestado     { background: #fee2e2; color: #991b1b; }

        /* ── BTN ARTIGO ── */
        .btn-artigo {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            border-radius: 30px;
            font-weight: 700;
            font-size: .9rem;
            border: none;
            cursor: pointer;
            transition: .2s;
            margin-bottom: 20px;
        }
        .btn-artigo.ativo {
            background: var(--cor-primaria);
            color: white;
        }
        .btn-artigo.ativo:hover { background: var(--cor-primaria-hover); }
        .btn-artigo.desativado {
            background: #e2e8f0;
            color: #94a3b8;
            cursor: not-allowed;
        }

        /* ── PAINEL DADOS DE INTERNET ── */
        .dados-internet-aviso {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: .85rem;
            color: #78350f;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .form-caract-secao {
            border: 1px solid var(--cinza-200);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 14px;
        }
        .form-caract-header {
            background: var(--cinza-50);
            padding: 10px 16px;
            font-weight: 700;
            font-size: .88rem;
            color: #475569;
            border-bottom: 1px solid var(--cinza-200);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-caract-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        }
        .form-caract-item {
            padding: 10px 16px;
            border-right: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
        }
        .form-caract-label {
            font-size: .72rem;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 700;
            letter-spacing: .3px;
        }
        .form-caract-valor {
            font-size: .9rem;
            color: #334155;
            margin-top: 2px;
        }

        /* ── CRÉDITO DE IMAGEM ── */
        .img-credito-wrapper { position: relative; pointer-events: none; }
        .img-credito-wrapper img, .img-credito { pointer-events: auto; }
        .img-credito {
            background: rgba(0,0,0,.65);
            color: #e2e8f0;
            font-size: .72rem;
            padding: 5px 12px;
            position: absolute;
            bottom: 0; left: 0; right: 0;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .img-credito a { color: #93c5fd; text-decoration: none; }
        .img-credito a:hover { text-decoration: underline; }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .layout {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                border-right: none;
                border-bottom: 1px solid var(--cinza-200);
                display: flex;
                overflow-x: auto;
                overflow-y: visible;
            }

            .sidebar-header {
                display: none;
            }

            .especie-item {
                white-space: nowrap;
                border-left: none;
                border-bottom: 4px solid transparent;
                padding: 14px 16px;
            }

            .especie-item.ativo {
                border-left: none;
                border-bottom-color: var(--cor-primaria);
            }

            .painel {
                padding: 20px 16px;
            }

            .browse-secao {
                padding: 20px 16px;
            }
        }
    </style>
</head>
<body>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox" onclick="fecharLightbox(event)">
    <button class="lightbox-fechar" onclick="fecharLightbox(null)">&times;</button>
    <img id="lightbox-img" src="" alt="">
    <div class="lightbox-credito" id="lightbox-credito"></div>
</div>

<!-- HEADER -->
<div class="header">
    <div style="display:flex;align-items:center;gap:16px;">
        <span class="header-logo"><i class="fas fa-leaf"></i> Penomato</span>
        <span class="header-count"><?= $total ?> espécie(s) encontrada(s)</span>
    </div>
    <a href="<?= APP_BASE ?>/src/Views/publico/busca_caracteristicas.php" class="btn-voltar">
        <i class="fas fa-arrow-left"></i> Voltar à busca
    </a>
</div>

<!-- FICHAS: uma por espécie -->
<div class="paginas">
<?php

$partes_info = [
    'habito'           => 'Hábito',
    'folha'            => 'Folha',
    'flor'             => 'Flor',
    'fruto'            => 'Fruto',
    'caule'            => 'Caule',
    'semente'          => 'Semente',
    'exsicata_completa'=> 'Exsicata',
    'detalhe'          => 'Detalhe',
];

$secoes_attr = [
    '🍃 Folha'   => 'folha',
    '🌸 Flor'    => 'flor',
    '🍎 Fruto'   => 'fruto',
    '🌱 Semente' => 'semente',
    '🌿 Caule'   => 'caule',
    '⚡ Outras'  => 'outras',
];

$status_label = [
    'publicado'      => 'Publicado',
    'revisada'       => 'Revisada',
    'em_revisao'     => 'Em revisão',
    'registrada'     => 'Registrada',
    'descrita'       => 'Descrita',
    'dados_internet' => 'Dados de internet',
    'contestado'     => 'Contestado',
    'sem_dados'      => 'Sem dados',
];

foreach ($especies as $esp):
    $espId   = $esp['id'];
    $imgs_esp = $imagens[$espId] ?? [];

    // Foto principal: habito primeiro, senão qualquer parte disponível
    $foto_principal = null;
    $foto_credito   = '';
    foreach (['habito','folha','flor','fruto','caule','semente','exsicata_completa','detalhe'] as $p) {
        if (!empty($imgs_esp[$p])) {
            $img0 = $imgs_esp[$p][0];
            $foto_principal = $img0['url'];
            $credito_parts  = [];
            if ($img0['autor'])  $credito_parts[] = htmlspecialchars($img0['autor']);
            if ($img0['fonte'])  $credito_parts[] = htmlspecialchars($img0['fonte']);
            if ($img0['licenca']) $credito_parts[] = '(' . htmlspecialchars($img0['licenca']) . ')';
            $foto_credito = implode(' · ', $credito_parts);
            break;
        }
    }

    // Status CSS class
    $st_cls = 'st-' . ($esp['status'] ?? 'sem_dados');
    $st_lbl = $status_label[$esp['status'] ?? ''] ?? ($esp['status'] ?? '');
?>
<div class="ficha">

    <!-- Cabeçalho -->
    <div class="ficha-cab">
        <?php if ($esp['familia']): ?>
        <div class="ficha-familia"><?= htmlspecialchars($esp['familia']) ?></div>
        <?php endif; ?>
        <?php if ($esp['nome_popular']): ?>
        <div class="ficha-popular"><?= htmlspecialchars(strtoupper($esp['nome_popular'])) ?></div>
        <?php endif; ?>
        <div class="ficha-cientifico">
            <?= htmlspecialchars($esp['nome']) ?>
            <button class="btn-falar" onclick="falarNome(this)" data-nome="<?= htmlspecialchars($esp['nome']) ?>" title="Ouvir pronúncia" aria-label="Ouvir pronúncia do nome científico">
                <i class="fa-solid fa-volume-high"></i>
            </button>
        </div>
        <div class="ficha-meta-linha">
            <?php if ($esp['status'] && $esp['status'] !== 'sem_dados'): ?>
            <span class="ficha-status <?= $st_cls ?>"><?= htmlspecialchars($st_lbl) ?></span>
            <?php endif; ?>
            <?php if (!empty($esp['autores'])): ?>
            <span class="ficha-autor">
                <i class="fas fa-user"></i>
                <?= htmlspecialchars($esp['autores'][0]['nome']) ?>
            </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Corpo: atributos + imagens -->
    <div class="ficha-corpo">

        <!-- Texto do artigo -->
        <div class="ficha-attrs ficha-artigo-texto">
        <?php if (!empty($esp['artigo_html'])):
            $html_ficha = $esp['artigo_html'];
            $html_ficha = str_replace('<h3 class="art-secao">Prancha Fotográfica</h3>', '', $html_ficha);
            $html_ficha = str_replace('<h3 class="art-secao">Referências</h3>', '', $html_ficha);
            $html_ficha = preg_replace('/<sup class="art-ref">\[[\d,]+\]<\/sup>/', '', $html_ficha);
            echo $html_ficha;
        else: ?>
            <p class="ficha-sem-attrs">Artigo ainda não gerado para esta espécie.</p>
        <?php endif; ?>
        </div>

        <!-- Imagens -->
        <div class="ficha-imgs">
            <!-- Galeria por parte -->
            <?php
            $tem_galeria = false;
            foreach ($partes_info as $parte => $parte_label) {
                if (!empty($imgs_esp[$parte])) { $tem_galeria = true; break; }
            }
            ?>
            <?php if ($tem_galeria): ?>
            <div class="galeria">
            <?php foreach ($partes_info as $parte => $parte_label):
                if (empty($imgs_esp[$parte])) continue;
            ?>
                <div class="galeria-parte">
                    <div class="galeria-parte-label"><?= htmlspecialchars($parte_label) ?></div>
                    <div class="galeria-thumbs">
                    <?php foreach (array_slice($imgs_esp[$parte], 0, 5) as $img):
                        $cred_parts = [];
                        if ($img['autor'])  $cred_parts[] = addslashes($img['autor']);
                        if ($img['fonte'])  $cred_parts[] = addslashes($img['fonte']);
                        if ($img['licenca']) $cred_parts[] = '(' . addslashes($img['licenca']) . ')';
                        $cred_str = implode(' · ', $cred_parts);
                    ?>
                        <div class="thumb" onclick="abrirLightbox('<?= addslashes($img['url']) ?>', '<?= $cred_str ?>')">
                            <img src="<?= htmlspecialchars($img['url']) ?>" alt="<?= htmlspecialchars($parte_label) ?>" loading="lazy">
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

    </div><!-- /ficha-corpo -->

    <!-- Rodapé -->
    <div class="ficha-rodape">
        <?php
        $artigo_status_label = [
            'rascunho'   => ['label' => 'Rascunho',   'icon' => 'fa-file-pen',          'cls' => 'art-st-rascunho'],
            'em_revisao' => ['label' => 'Em revisão',  'icon' => 'fa-magnifying-glass',  'cls' => 'art-st-revisao'],
            'aprovado'   => ['label' => 'Aprovado',    'icon' => 'fa-file-circle-check', 'cls' => 'art-st-aprovado'],
            'publicado'  => ['label' => 'Publicado',   'icon' => 'fa-book-open',         'cls' => 'art-st-publicado'],
        ];
        $ast = $artigo_status_label[$esp['artigo_status']] ?? null;
        ?>
        <?php if (!empty($esp['artigo_html']) && $ast): ?>
        <a href="<?= APP_BASE ?>/src/Views/publico/artigo.php?id=<?= $espId ?>" class="btn-artigo-completo <?= $ast['cls'] ?>">
            <i class="fas <?= $ast['icon'] ?>"></i> Ver artigo — <?= $ast['label'] ?>
        </a>
        <?php else: ?>
        <span class="btn-sem-artigo">Artigo ainda não gerado</span>
        <?php endif; ?>
    </div>

</div><!-- /ficha -->
<?php endforeach; ?>
</div><!-- /paginas -->

<script>
function abrirLightbox(url, credito) {
    document.getElementById('lightbox-img').src = url;
    document.getElementById('lightbox-credito').textContent = credito || '';
    document.getElementById('lightbox').classList.add('ativo');
    document.body.style.overflow = 'hidden';
}
function fecharLightbox(e) {
    if (e && e.target !== document.getElementById('lightbox') && !e.target.classList.contains('lightbox-fechar')) return;
    document.getElementById('lightbox').classList.remove('ativo');
    document.getElementById('lightbox-img').src = '';
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') fecharLightbox(null);
});

function falarNome(btn) {
    if (!window.speechSynthesis) return;
    speechSynthesis.cancel();
    const nome = btn.dataset.nome;
    const u = new SpeechSynthesisUtterance(nome);
    u.lang = 'it-IT';
    u.rate = 0.82;
    btn.classList.add('falando');
    u.onend = u.onerror = () => btn.classList.remove('falando');
    speechSynthesis.speak(u);
}
</script>
</body>
</html>
