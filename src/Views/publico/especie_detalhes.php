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
    'forma_folha', 'filotaxia_folha', 'tipo_folha', 'tamanho_folha', 'textura_folha', 'margem_folha', 'venacao_folha',
    'cor_flores', 'simetria_floral', 'numero_petalas', 'tamanho_flor', 'disposicao_flores', 'aroma',
    'tipo_fruto', 'tamanho_fruto', 'cor_fruto', 'textura_fruto', 'dispersao_fruto', 'aroma_fruto',
    'tipo_semente', 'tamanho_semente', 'cor_semente', 'textura_semente', 'quantidade_sementes',
    'tipo_caule', 'estrutura_caule', 'textura_caule', 'cor_caule', 'forma_caule', 'modificacao_caule',
    'diametro_caule', 'ramificacao_caule', 'possui_espinhos', 'possui_latex'
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
            c.forma_folha, c.filotaxia_folha, c.tipo_folha, c.tamanho_folha, c.textura_folha, c.margem_folha, c.venacao_folha,
            c.cor_flores, c.simetria_floral, c.numero_petalas, c.tamanho_flor, c.disposicao_flores, c.aroma,
            c.tipo_fruto, c.tamanho_fruto, c.cor_fruto, c.textura_fruto, c.dispersao_fruto, c.aroma_fruto,
            c.tipo_semente, c.tamanho_semente, c.cor_semente, c.textura_semente, c.quantidade_sementes,
            c.tipo_caule, c.estrutura_caule, c.textura_caule, c.cor_caule, c.forma_caule, c.modificacao_caule,
            c.diametro_caule, c.ramificacao_caule,
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
            'Estrutura'   => $r['estrutura_caule'],
            'Textura'     => $r['textura_caule'],
            'Cor'         => $r['cor_caule'],
            'Forma'       => $r['forma_caule'],
            'Diâmetro'    => $r['diametro_caule'],
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
    <title>Resultado da Busca — Penomato</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
    <style>
        body {
            background: #f0f2f5;
            color: #1a2634;
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

        /* ── PRANCHA FOTOGRÁFICA (override do HTML salvo no banco) ── */
        .art-galeria {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 20px !important;
            flex-wrap: unset !important;
            margin: 16px 0 !important;
        }
        .art-figura {
            border: 1px solid #ddd !important;
            border-radius: 8px !important;
            overflow: hidden !important;
            background: #fafafa !important;
            text-align: left !important;
            flex: unset !important;
        }
        .art-figura img {
            width: 100% !important;
            height: auto !important;
            object-fit: contain !important;
            border-radius: 0 !important;
            display: block !important;
        }
        .art-figura figcaption {
            padding: 6px 10px 8px !important;
            font-size: 0.78em !important;
            color: #555 !important;
            line-height: 1.5 !important;
            max-width: unset !important;
        }
        @media (max-width: 600px) {
            .art-galeria { grid-template-columns: 1fr !important; }
        }

        /* ── AUTORES DO ARTIGO ── */
        .art-autores {
            font-size: 0.82em;
            color: #555;
            margin: 4px 0 14px;
            padding: 6px 10px;
            background: #f4f8f4;
            border-left: 3px solid var(--cor-primaria);
            border-radius: 4px;
            line-height: 1.6;
        }
        .art-autores-label {
            font-weight: 600;
            color: var(--cor-primaria);
        }
        .art-autores-inst {
            color: #777;
        }
        .art-autores-papel {
            color: #888;
            font-style: italic;
        }

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
            padding: 32px 36px;
            font-family: Georgia, 'Times New Roman', serif;
            line-height: 1.7;
        }
        .artigo-sem-dados {
            text-align: center;
            padding: 40px 20px;
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

<!-- HEADER -->
<div class="header">
    <div class="header-left">
        <span class="header-logo">🌿 Penomato</span>
        <span class="header-count"><?= $total ?> espécie(s) encontrada(s)</span>
    </div>
    <a href="/penomato_mvp/src/Views/publico/busca_caracteristicas.php" class="btn-voltar">
        <i class="fas fa-arrow-left"></i> Voltar à busca
    </a>
</div>

<!-- LAYOUT: sidebar + painel -->
<div class="layout">
    <div class="sidebar">
        <div class="sidebar-header">Espécies encontradas</div>
        <?php foreach ($especies as $idx => $esp): ?>
        <button class="especie-item<?= $idx === 0 ? ' ativo' : '' ?>" onclick="selecionarEspecie(<?= $idx ?>)">
            <span class="status-dot <?= htmlspecialchars($esp['status']) ?>"></span>
            <span class="especie-item-texto">
                <span class="especie-item-nome"><?= htmlspecialchars($esp['nome']) ?></span>
                <?php if ($esp['nome_popular']): ?>
                <span class="especie-item-popular"><?= htmlspecialchars($esp['nome_popular']) ?></span>
                <?php endif; ?>
            </span>
        </button>
        <?php endforeach; ?>
    </div>

    <div class="painel" id="painel">
        <!-- status banner -->
        <div id="status-banner"></div>
        <!-- artigo nome -->
        <div class="artigo-nome" id="artigo-nome"></div>
        <!-- artigo meta badges -->
        <div class="artigo-meta" id="artigo-meta"></div>
        <!-- btn artigo -->
        <div id="btn-artigo-wrapper" style="margin: 12px 0 4px;"></div>

        <!-- CARROSSEL -->
        <div class="carrossel-wrapper">
            <button class="nav-btn nav-prev" onclick="navCarrossel(-1)"><i class="fas fa-chevron-left"></i></button>
            <div class="carrossel-tela" id="carrossel-tela">
                <div id="carrossel-conteudo" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;"></div>
            </div>
            <button class="nav-btn nav-next" onclick="navCarrossel(1)"><i class="fas fa-chevron-right"></i></button>
            <div class="carrossel-footer">
                <div class="partes-btns" id="partes-btns">
                    <button class="parte-btn" onclick="trocarParte('folha')">🍃 Folha</button>
                    <button class="parte-btn" onclick="trocarParte('flor')">🌸 Flor</button>
                    <button class="parte-btn" onclick="trocarParte('fruto')">🍎 Fruto</button>
                    <button class="parte-btn" onclick="trocarParte('caule')">🌿 Caule</button>
                    <button class="parte-btn" onclick="trocarParte('semente')">🌱 Semente</button>
                    <button class="parte-btn" onclick="trocarParte('habito')">🌳 Hábito</button>
                    <button class="parte-btn" onclick="trocarParte('exsicata_completa')">📋 Exsicata</button>
                    <button class="parte-btn" onclick="trocarParte('detalhe')">🔍 Detalhe</button>
                </div>
                <span class="carrossel-counter" id="carrossel-counter">— / —</span>
            </div>
        </div>

        <!-- BTN MAPA -->
        <button class="btn-mapa" onclick="toggleMapa()">
            <i class="fas fa-map-marked-alt"></i> Ver no mapa
        </button>

        <!-- ABAS -->
        <div class="artigo-tabs">
            <button class="artigo-tab ativo" id="tab-artigo" onclick="trocarTab('artigo')">
                <i class="fas fa-book-open"></i> Artigo Científico
            </button>
            <button class="artigo-tab" id="tab-atributos" onclick="trocarTab('atributos')">
                <i class="fas fa-list"></i> Atributos
            </button>
        </div>

        <!-- PIPELINE DE STATUS -->
        <div id="artigo-pipeline"></div>

        <!-- AVISO contextual -->
        <div id="caract-aviso"></div>

        <!-- ARTIGO PREVIEW (aba artigo) -->
        <div class="artigo-preview-card" id="artigo-preview"></div>

        <!-- CARACTERÍSTICAS (aba atributos) -->
        <div class="caract-container" id="caract-container" style="display:none;"></div>
    </div>
</div>

<!-- MAPA (oculto por padrão) -->
<div class="mapa-secao" id="mapa-secao">
    <div class="mapa-titulo" id="mapa-titulo">📍 Exemplares</div>
    <div id="mapa-leaflet"></div>
</div>


<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const ESPECIES   = <?= $j_especies ?>;
const IMAGENS    = <?= $j_imagens ?>;
const EXEMPLARES = <?= $j_exemplares ?>;
const PARTES     = ['folha', 'flor', 'fruto', 'caule', 'semente', 'habito', 'exsicata_completa', 'detalhe'];

let especieAtual   = 0;
let parteCarrossel = 'folha';
let idxCarrossel   = 0;
let parteBrowse    = 'folha';
let mapaLeaflet    = null;
let mapaIniciado   = false;
let marcadores     = [];

// ── INIT ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    selecionarEspecie(0);
    renderBrowse();
});

// ── SIDEBAR ───────────────────────────────────────
function selecionarEspecie(idx) {
    especieAtual = idx;

    document.querySelectorAll('.especie-item').forEach(function (el, i) {
        el.classList.toggle('ativo', i === idx);
    });

    renderArtigo(ESPECIES[idx]);
    esconderMapa();
}

// ── MAPA DE STATUS ────────────────────────────────
var STATUS_LABEL = {
    publicado:      '✅ Publicado',
    revisada:       '🔵 Revisada',
    em_revisao:     '🟣 Em revisão',
    registrada:     '🔷 Registrada',
    descrita:       '🟡 Descrita',
    dados_internet: '⚪ Dados de internet',
    contestado:     '🔴 Contestado',
    sem_dados:      '⬜ Sem dados',
};

// ── ARTIGO ────────────────────────────────────────
function renderArtigo(esp) {
    // Status banner
    var statusLabel = STATUS_LABEL[esp.status] || esp.status;
    document.getElementById('status-banner').innerHTML =
        '<span class="status-banner ' + esc(esp.status) + '">' + statusLabel + '</span>';

    document.getElementById('artigo-nome').textContent = esp.nome;

    var meta = '';
    if (esp.familia)      meta += '<span class="badge badge-familia">' + esc(esp.familia) + '</span>';
    if (esp.nome_popular) meta += '<span class="badge badge-popular">' + esc(esp.nome_popular) + '</span>';
    if (esp.sinonimos) {
        esp.sinonimos.split(/[;,]/).forEach(function (s) {
            s = s.trim();
            if (s) meta += '<span class="badge badge-sinonimo">' + esc(s) + '</span>';
        });
    }
    document.getElementById('artigo-meta').innerHTML = meta;

    // Badge do artigo
    var ARTIGO_ICONS  = { rascunho:'fa-file-pen', escrito:'fa-file-alt', revisado:'fa-file-circle-check', publicado:'fa-book-open' };
    var ARTIGO_LABELS = { rascunho:'Rascunho', escrito:'Escrito', revisado:'Revisado', publicado:'Publicado' };
    var btnWrapper = document.getElementById('btn-artigo-wrapper');
    if (esp.artigo_html) {
        var icon  = ARTIGO_ICONS[esp.artigo_status]  || 'fa-file-alt';
        var label = ARTIGO_LABELS[esp.artigo_status] || 'Artigo';
        if (esp.artigo_status === 'publicado') {
            btnWrapper.innerHTML = '<a href="<?= APP_BASE ?>/src/Views/publico/artigo.php?id=' + esp.id + '" class="btn-artigo ativo"><i class="fas ' + icon + '"></i> Abrir Artigo</a>';
        } else {
            btnWrapper.innerHTML = '<span class="btn-artigo ativo" title="Aguardando publicação"><i class="fas ' + icon + '"></i> Artigo: ' + label + '</span>';
        }
    } else {
        btnWrapper.innerHTML = '<span class="btn-artigo desativado"><i class="fas fa-lock"></i> Sem artigo</span>';
    }

    // Determinar primeira parte disponível para o carrossel
    var espId = esp.id;
    var imgs  = IMAGENS[espId] || {};
    var partInicial = null;
    for (var i = 0; i < PARTES.length; i++) {
        if (imgs[PARTES[i]] && imgs[PARTES[i]].length > 0) {
            partInicial = PARTES[i];
            break;
        }
    }
    parteCarrossel = partInicial || 'folha';
    idxCarrossel   = 0;

    renderCarrossel();

    // Pipeline de status
    renderPipeline(esp.status, esp.artigo_status);

    // Aviso contextual por status
    var AVISO_STATUS = {
        'dados_internet': 'Dados preliminares de fontes externas — aguardando verificação científica.',
        'descrita':       'Espécie descrita — aguardando registro de exemplar em campo.',
        'registrada':     'Exemplar registrado — aguardando revisão por especialista.',
        'em_revisao':     'Em processo de revisão por especialista.',
        'revisada':       'Revisada pelo especialista — aguardando publicação.',
        'contestado':     'Informações desta espécie estão sendo revisadas após contestação.'
    };
    var aviso = '';
    if (esp.status !== 'publicado' && AVISO_STATUS[esp.status]) {
        aviso = '<div class="dados-internet-aviso">'
              + '<i class="fas fa-exclamation-triangle" style="flex-shrink:0;margin-top:2px;"></i>'
              + '<span>' + AVISO_STATUS[esp.status] + '</span>'
              + '</div>';
    }
    document.getElementById('caract-aviso').innerHTML = aviso;

    // ── Renderizar artigo no preview card ──
    var preview = document.getElementById('artigo-preview');
    if (esp.artigo_html) {
        preview.innerHTML = esp.artigo_html;

        // Injetar bloco de autores após art-nomes (ou art-titulo como fallback)
        if (esp.autores && esp.autores.length > 0) {
            var autoresHtml = '<div class="art-autores">';
            autoresHtml += '<span class="art-autores-label">Autores:</span> ';
            var partes = esp.autores.map(function(a) {
                var txt = esc(a.nome);
                if (a.inst) txt += ' <span class="art-autores-inst">(' + esc(a.inst) + ')</span>';
                txt += ' <span class="art-autores-papel">[' + esc(a.papel) + ']</span>';
                return txt;
            });
            autoresHtml += partes.join(' &nbsp;·&nbsp; ');
            autoresHtml += '</div>';
            var alvo = preview.querySelector('.art-nomes') || preview.querySelector('.art-titulo');
            if (alvo && alvo.parentNode) {
                var divA = document.createElement('div');
                divA.innerHTML = autoresHtml;
                alvo.parentNode.insertBefore(divA.firstChild, alvo.nextSibling);
            }
        }
    } else {
        preview.innerHTML = '<div class="artigo-sem-dados">'
            + '<i class="fas fa-hourglass-half"></i>'
            + '<p>O artigo será gerado automaticamente após o upload de dados e imagens.</p>'
            + '</div>';
    }

    // ── Renderizar características na aba atributos ──
    renderCaracteristicas(esp);

    // Garantir que a aba ativa está correta
    trocarTab(tabAtiva);
}

// ── PIPELINE DE STATUS ─────────────────────────────
function renderPipeline(statusEspecie, statusArtigo) {
    var passos = [
        { key: 'dados_internet', label: 'Dados',     icon: '1' },
        { key: 'registrada',     label: 'Exemplar',  icon: '2' },
        { key: 'em_revisao',     label: 'Revisão',   icon: '3' },
        { key: 'publicado',      label: 'Publicado', icon: '✓' },
    ];
    var ordem = ['sem_dados','dados_internet','descrita','registrada','em_revisao','revisada','contestado','publicado'];
    var idxAtual = ordem.indexOf(statusEspecie);

    var html = '<div class="artigo-pipeline">';
    passos.forEach(function(p, i) {
        var idxPasso = ordem.indexOf(p.key);
        var cls = '';
        if (idxAtual > idxPasso) cls = 'feito';
        else if (statusEspecie === p.key || (p.key === 'em_revisao' && statusEspecie === 'revisada') || (p.key === 'publicado' && statusEspecie === 'publicado')) cls = 'feito';
        else if (
            (p.key === 'dados_internet' && statusEspecie === 'dados_internet') ||
            (p.key === 'registrada'     && (statusEspecie === 'registrada' || statusEspecie === 'descrita')) ||
            (p.key === 'em_revisao'     && (statusEspecie === 'em_revisao' || statusEspecie === 'contestado')) ||
            (p.key === 'publicado'      && statusEspecie === 'publicado')
        ) cls = 'atual';
        html += '<div class="pipeline-step ' + cls + '">';
        html += '<div class="pipeline-dot">' + (cls === 'feito' ? '✓' : p.icon) + '</div>';
        html += '<div class="pipeline-label">' + p.label + '</div>';
        html += '</div>';
    });
    html += '</div>';
    document.getElementById('artigo-pipeline').innerHTML = html;
}

// ── TROCAR ABA ─────────────────────────────────────
var tabAtiva = 'artigo';
function trocarTab(qual) {
    tabAtiva = qual;
    document.getElementById('artigo-preview').style.display  = qual === 'artigo'    ? '' : 'none';
    document.getElementById('caract-container').style.display = qual === 'atributos' ? '' : 'none';
    document.getElementById('tab-artigo').classList.toggle('ativo',    qual === 'artigo');
    document.getElementById('tab-atributos').classList.toggle('ativo', qual === 'atributos');
}

// ── CARROSSEL ─────────────────────────────────────
function renderCarrossel() {
    var esp   = ESPECIES[especieAtual];
    var espId = esp.id;
    var imgs  = (IMAGENS[espId] && IMAGENS[espId][parteCarrossel]) ? IMAGENS[espId][parteCarrossel] : [];

    if (imgs.length === 0) {
        idxCarrossel = 0;
    } else {
        idxCarrossel = ((idxCarrossel % imgs.length) + imgs.length) % imgs.length;
    }

    var conteudo = document.getElementById('carrossel-conteudo');
    if (imgs.length > 0) {
        var img = imgs[idxCarrossel];
        var credito = '';
        if (img.autor || img.fonte) {
            credito = '<div class="img-credito">'
                    + '<i class="fas fa-camera"></i>';
            if (img.autor) credito += ' ' + esc(img.autor);
            if (img.fonte && img.fonte_url) {
                credito += ' · <a href="' + esc(img.fonte_url) + '" target="_blank" rel="noopener">' + esc(img.fonte) + '</a>';
            } else if (img.fonte) {
                credito += ' · ' + esc(img.fonte);
            }
            if (img.licenca) credito += ' <span style="opacity:.7">(' + esc(img.licenca) + ')</span>';
            credito += '</div>';
        }
        conteudo.innerHTML = '<div class="img-credito-wrapper" style="width:100%;height:100%;position:relative;display:flex;align-items:center;justify-content:center;">'
            + '<img src="' + esc(img.url) + '" alt="Imagem de ' + esc(parteCarrossel) + '" style="max-width:100%;max-height:340px;object-fit:contain;">'
            + credito
            + '</div>';
    } else {
        conteudo.innerHTML = '<div class="carrossel-placeholder"><i class="fas fa-image"></i><span>Sem imagem para esta parte</span></div>';
    }

    var counter = imgs.length > 0 ? (idxCarrossel + 1) + '/' + imgs.length : '—/—';
    document.getElementById('carrossel-counter').textContent = counter;

    // Atualizar estado dos botões de parte
    document.querySelectorAll('.parte-btn').forEach(function (btn) {
        var m = btn.getAttribute('onclick').match(/'([^']+)'/);
        if (m) btn.classList.toggle('ativo', m[1] === parteCarrossel);
    });
}

function navCarrossel(dir) {
    var espId = ESPECIES[especieAtual].id;
    var imgs  = (IMAGENS[espId] && IMAGENS[espId][parteCarrossel]) ? IMAGENS[espId][parteCarrossel] : [];

    var novoIdx = idxCarrossel + dir;

    // Ainda dentro da mesma parte
    if (novoIdx >= 0 && novoIdx < imgs.length) {
        idxCarrossel = novoIdx;
        renderCarrossel();
        return;
    }

    // Passou do limite — vai para a parte anterior/próxima com imagens
    var partesComImagem = PARTES.filter(function(p) {
        return IMAGENS[espId] && IMAGENS[espId][p] && IMAGENS[espId][p].length > 0;
    });
    if (partesComImagem.length === 0) return;

    var idxParte = partesComImagem.indexOf(parteCarrossel);
    var novaParte = partesComImagem[(idxParte + dir + partesComImagem.length) % partesComImagem.length];

    parteCarrossel = novaParte;
    var novasImgs  = IMAGENS[espId][novaParte];
    idxCarrossel   = dir > 0 ? 0 : novasImgs.length - 1;
    renderCarrossel();
}

function trocarParte(parte) {
    parteCarrossel = parte;
    idxCarrossel   = 0;
    renderCarrossel();
}

// ── CARACTERÍSTICAS ───────────────────────────────
function renderCaracteristicas(esp) {
    var secoes = [
        { chave: 'folha',   titulo: '🍃 Folha',   dados: esp.folha   },
        { chave: 'flor',    titulo: '🌸 Flor',    dados: esp.flor    },
        { chave: 'fruto',   titulo: '🍎 Fruto',   dados: esp.fruto   },
        { chave: 'semente', titulo: '🌱 Semente', dados: esp.semente },
        { chave: 'caule',   titulo: '🌿 Caule',   dados: esp.caule   },
        { chave: 'outras',  titulo: '⚡ Outras',   dados: esp.outras  },
    ];

    var html = '';
    secoes.forEach(function (s) {
        if (!s.dados || Object.keys(s.dados).length === 0) return;
        var items = '';
        Object.keys(s.dados).forEach(function (label) {
            var val = s.dados[label];
            if (val === null || val === undefined || val === '') return;
            items += '<div class="caract-item">'
                   +   '<div class="caract-label">' + esc(label) + '</div>'
                   +   '<div class="caract-valor">' + esc(String(val)) + '</div>'
                   + '</div>';
        });
        if (!items) return;
        html += '<div class="caract-secao">'
              +   '<div class="caract-header aberto" onclick="toggleSecao(this)">'
              +     '<span>' + s.titulo + '</span>'
              +     '<i class="fas fa-chevron-down toggle-icon"></i>'
              +   '</div>'
              +   '<div class="caract-grid">' + items + '</div>'
              + '</div>';
    });

    if (esp.referencias && esp.referencias.trim()) {
        html += '<div class="caract-secao">'
              +   '<div class="caract-header aberto" onclick="toggleSecao(this)">'
              +     '<span>📚 Referências</span>'
              +     '<i class="fas fa-chevron-down toggle-icon"></i>'
              +   '</div>'
              +   '<div class="referencias-texto">' + esc(esp.referencias) + '</div>'
              + '</div>';
    }

    document.getElementById('caract-container').innerHTML = html;
}

function toggleSecao(header) {
    var grid = header.nextElementSibling;
    header.classList.toggle('aberto');
    grid.style.display = header.classList.contains('aberto') ? '' : 'none';
}

// ── BROWSE ────────────────────────────────────────
function selecionarBrowse(parte) {
    parteBrowse = parte;
    document.querySelectorAll('.browse-parte-btn').forEach(function (btn) {
        var m = btn.getAttribute('onclick').match(/'([^']+)'/);
        if (m) btn.classList.toggle('ativo', m[1] === parte);
    });
    renderBrowse();
}

function renderBrowse() {
    var trilha = document.getElementById('browse-trilha');
    var html   = '';

    ESPECIES.forEach(function (esp, idx) {
        var espId = esp.id;
        var imgs  = (IMAGENS[espId] && IMAGENS[espId][parteBrowse]) ? IMAGENS[espId][parteBrowse] : [];
        var imgHtml;
        if (imgs.length > 0) {
            imgHtml = '<img class="browse-card-img" src="' + esc(imgs[0].url) + '" alt="' + esc(esp.nome) + '">';
        } else {
            imgHtml = '<div class="browse-card-placeholder"><i class="fas fa-image"></i></div>';
        }
        html += '<div class="browse-card" onclick="selecionarEspecie(' + idx + '); document.getElementById(\'painel\').scrollIntoView({behavior:\'smooth\'})">'
              +   imgHtml
              +   '<div class="browse-card-nome">' + esc(esp.nome) + '</div>'
              + '</div>';
    });

    trilha.innerHTML = html;
}

// ── MAPA ──────────────────────────────────────────
function toggleMapa() {
    var secao = document.getElementById('mapa-secao');
    if (secao.classList.contains('visivel')) {
        esconderMapa();
    } else {
        secao.classList.add('visivel');
        iniciarMapa();
        secao.scrollIntoView({ behavior: 'smooth' });
    }
}

function iniciarMapa() {
    var esp   = ESPECIES[especieAtual];
    var espId = esp.id;
    var exs   = EXEMPLARES[espId] || [];

    if (!mapaIniciado) {
        mapaLeaflet = L.map('mapa-leaflet').setView([-20, -55], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18
        }).addTo(mapaLeaflet);
        mapaIniciado = true;
    }

    // Limpar marcadores anteriores
    marcadores.forEach(function (m) { mapaLeaflet.removeLayer(m); });
    marcadores = [];

    var bounds = [];
    exs.forEach(function (ex) {
        var popup = '<strong>' + esc(ex.codigo || '') + '</strong>';
        if (ex.cidade || ex.estado) {
            popup += '<br>' + esc((ex.cidade || '') + (ex.estado ? ', ' + ex.estado : ''));
        }
        var m = L.marker([parseFloat(ex.lat), parseFloat(ex.lng)]).bindPopup(popup).addTo(mapaLeaflet);
        marcadores.push(m);
        bounds.push([parseFloat(ex.lat), parseFloat(ex.lng)]);
    });

    if (bounds.length > 0) {
        mapaLeaflet.fitBounds(bounds, { padding: [40, 40] });
    }

    document.getElementById('mapa-titulo').textContent = '📍 Exemplares — ' + esp.nome;
}

function esconderMapa() {
    document.getElementById('mapa-secao').classList.remove('visivel');
}

// ── UTIL ──────────────────────────────────────────
function esc(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
</script>
</body>
</html>
