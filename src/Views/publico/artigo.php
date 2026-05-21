<?php
// ================================================
// ARTIGO CIENTÍFICO PÚBLICO
// Exibe o artigo publicado de uma espécie
// ================================================

session_start();
require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../../helpers/autores_artigo.php';

$especie_id = (int)($_GET['id'] ?? 0);
if (!$especie_id) {
    header('Location: ' . APP_BASE . '/src/Views/publico/busca_caracteristicas.php');
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT a.texto_html, a.gerado_em, a.atualizado_em, a.status AS artigo_status,
               e.nome_cientifico, e.prioridade,
               e.data_publicado, e.autor_publicado_id,
               up.nome AS nome_publicador, up.instituicao AS inst_publicador,
               c.familia, c.nome_popular, c.sinonimos,
               uc.nome AS nome_colaborador, uc.instituicao AS inst_colaborador
        FROM artigos a
        INNER JOIN especies_administrativo e ON e.id = a.especie_id
        LEFT JOIN especies_caracteristicas c ON c.especie_id = e.id
        LEFT JOIN usuarios up ON up.id = e.autor_publicado_id
        LEFT JOIN usuarios uc ON uc.id = e.autor_dados_internet_id
        WHERE a.especie_id = ?
        LIMIT 1
    ");
    $stmt->execute([$especie_id]);
    $artigo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$artigo) {
        header('Location: ' . APP_BASE . '/src/Views/publico/busca_caracteristicas.php');
        exit;
    }

} catch (Exception $e) {
    error_log('Erro ao carregar artigo público: ' . $e->getMessage());
    header('Location: ' . APP_BASE . '/src/Views/publico/busca_caracteristicas.php');
    exit;
}

$data_pub = $artigo['data_publicado']
    ? date('d/m/Y', strtotime($artigo['data_publicado']))
    : '—';

// ── Contribuidores com números de ação ──
$legenda_acoes = [
    1 => 'Adicionou dados da internet',
    2 => 'Confirmou os dados morfológicos',
    3 => 'Registrou as imagens',
    4 => 'Fez a revisão científica',
    5 => 'Publicou',
];
$papel_para_num = [
    'Compilação de dados'   => 1,
    'Validação morfológica' => 2,
    'Coleta de campo'       => 3,
    'Revisão científica'    => 4,
    'Gestão editorial'      => 5,
];
$autores_raw = montarAutoresArtigo($pdo, $especie_id);
$acoes_usadas = [];
foreach ($autores_raw as &$autor) {
    $nums = [];
    foreach ($autor['papeis'] as $papel) {
        foreach ($papel_para_num as $chave => $num) {
            if (str_starts_with($papel, $chave)) { $nums[] = $num; break; }
        }
    }
    sort($nums);
    $autor['numeros'] = $nums;
    foreach ($nums as $n) $acoes_usadas[$n] = true;
}
unset($autor);
ksort($acoes_usadas);

$artigo_status_info = [
    'rascunho'   => ['label' => 'Rascunho',   'cor' => '#64748b', 'aviso' => 'Este artigo é um rascunho preliminar gerado por IA — ainda não foi revisado por especialista.'],
    'em_revisao' => ['label' => 'Em revisão',  'cor' => '#7c3aed', 'aviso' => 'Este artigo está em processo de revisão por um especialista.'],
    'aprovado'   => ['label' => 'Aprovado',    'cor' => '#0891b2', 'aviso' => 'Artigo aprovado pelo especialista — aguardando publicação oficial.'],
    'publicado'  => ['label' => 'Publicado',   'cor' => '#0b5e42', 'aviso' => ''],
];
$ast_info = $artigo_status_info[$artigo['artigo_status']] ?? $artigo_status_info['rascunho'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($artigo['nome_cientifico']) ?> — Penomato</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha384-blOohCVdhjmtROpu8+CfTnUWham9nkX7P7OZQMst+RUnhtoY/9qemFAkIKOYxDI3" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= APP_BASE ?>/assets/css/estilo.css">
    <style>
        body { background: #f0f4f1; color: #1a2634; margin: 0; }

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
        .header-logo {
            font-size: 1.1rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
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
            font-size: .875rem;
            font-weight: 600;
            transition: background .2s;
        }
        .btn-voltar:hover { background: rgba(255,255,255,.28); }

        /* ── HERO ── */
        .hero {
            background: linear-gradient(135deg, var(--cor-primaria) 0%, #0d7a56 100%);
            color: white;
            padding: 48px 24px 36px;
            text-align: center;
        }
        .hero-label {
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            opacity: .75;
            margin-bottom: 12px;
        }
        .hero-nome {
            font-size: clamp(1.6rem, 4vw, 2.4rem);
            font-style: italic;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .hero-familia {
            font-size: .95rem;
            opacity: .85;
            margin-bottom: 20px;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,.2);
            border: 1px solid rgba(255,255,255,.35);
            border-radius: 30px;
            padding: 5px 16px;
            font-size: .8rem;
            font-weight: 600;
        }

        /* ── LAYOUT PRINCIPAL ── */
        .page-wrap {
            max-width: 860px;
            margin: 0 auto;
            padding: 32px 20px 60px;
        }

        /* ── CARD ARTIGO ── */
        .card-artigo {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
            padding: 40px 48px;
            margin-bottom: 24px;
        }

        /* imagens do artigo maiores na página pública */
        .card-artigo .art-figura img {
            width: 180px;
            height: 130px;
        }
        .card-artigo .art-figura figcaption { max-width: 180px; }

        /* ── CARD CRÉDITOS ── */
        .card-creditos {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
            padding: 24px 32px;
            margin-bottom: 24px;
        }
        .creditos-titulo {
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .creditos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        .credito-item {}
        .credito-papel {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #94a3b8;
            margin-bottom: 2px;
        }
        .credito-nome {
            font-size: .9rem;
            font-weight: 600;
            color: #1e293b;
        }
        .credito-inst {
            font-size: .8rem;
            color: #64748b;
        }

        /* ── AÇÕES ── */
        .acoes {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 8px;
        }
        .btn-acao {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 20px;
            border-radius: 30px;
            font-size: .875rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: .2s;
            text-decoration: none;
        }
        .btn-imprimir {
            background: var(--cor-primaria);
            color: white;
        }
        .btn-imprimir:hover { background: var(--cor-primaria-hover); }
        .btn-busca {
            background: #f1f5f9;
            color: #475569;
        }
        .btn-busca:hover { background: #e2e8f0; }

        /* ── AUTORES NO HERO ── */
        .hero-autores {
            margin: 14px 0 4px;
            font-size: .9rem;
            opacity: .92;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: baseline;
            gap: 4px 10px;
        }
        .hero-autores-sep { opacity: .5; }
        .hero-autor-nome  { font-weight: 600; }
        .hero-autor-nums  {
            font-size: .7em;
            font-weight: 700;
            vertical-align: super;
            letter-spacing: .03em;
        }

        /* ── CRÉDITOS NOVO FORMATO ── */
        .creditos-pessoas {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 24px;
            margin-bottom: 16px;
        }
        .credito-pessoa {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .credito-pessoa-nome {
            font-size: .9rem;
            font-weight: 600;
            color: #1e293b;
        }
        .credito-pessoa-nums {
            display: flex;
            gap: 3px;
        }
        .credito-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--cor-primaria);
            color: white;
            font-size: .68rem;
            font-weight: 700;
            cursor: default;
        }
        .creditos-legenda {
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px 24px;
        }
        .legenda-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: .78rem;
            color: #64748b;
        }
        .legenda-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #475569;
            font-size: .68rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        /* ── DATA PUBLICAÇÃO ── */
        .pub-meta {
            font-size: .8rem;
            color: #94a3b8;
            text-align: center;
            margin-top: 8px;
        }

        /* ── BLOCO ABNT (só visível na impressão) ── */
        .print-cabecalho {
            display: none;
        }

        /* ════════════════════════════════════════
           ABNT NBR 6022:2018 — @media print
           A4 · Times New Roman 12pt · 1,5 esp.
           Margens: 3cm sup/esq · 2cm inf/dir
        ════════════════════════════════════════ */
        @media print {

            /* Página A4 com margens ABNT */
            @page {
                size: A4 portrait;
                margin: 3cm 2cm 2cm 3cm;
            }

            /* Reset geral */
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }

            body {
                background: white !important;
                color: #000 !important;
                font-family: 'Times New Roman', Times, serif !important;
                font-size: 12pt !important;
                line-height: 1.5 !important;
                margin: 0 !important;
            }

            /* Ocultar elementos de navegação/web */
            .header,
            .hero,
            .acoes,
            .pub-meta,
            .card-creditos,
            .art-autores { display: none !important; }

            /* Remover card visual */
            .page-wrap {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .card-artigo {
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            /* Exibir bloco de título ABNT */
            .print-cabecalho {
                display: block !important;
                text-align: center;
                margin-bottom: 24pt;
            }

            .print-titulo {
                font-family: 'Times New Roman', Times, serif;
                font-size: 14pt;
                font-weight: bold;
                font-style: italic;
                text-align: center;
                text-transform: uppercase;
                margin-bottom: 18pt;
                line-height: 1.3;
            }

            .print-autores {
                font-size: 11pt;
                text-align: center;
                margin-bottom: 4pt;
                line-height: 1.4;
            }

            .print-inst {
                font-size: 10pt;
                text-align: center;
                color: #333;
                margin-bottom: 16pt;
                font-style: italic;
            }

            .print-linha {
                border: none;
                border-top: 1px solid #000;
                margin: 16pt 0;
            }

            .print-data {
                font-size: 10pt;
                text-align: right;
                color: #444;
                margin-bottom: 24pt;
                font-style: italic;
            }

            /* ── Artigo: tipografia ABNT ── */
            .artigo {
                font-family: 'Times New Roman', Times, serif !important;
                font-size: 12pt !important;
                line-height: 1.5 !important;
                color: #000 !important;
            }

            /* Título da espécie (h2) */
            .art-titulo {
                font-size: 14pt !important;
                font-weight: bold !important;
                font-style: italic !important;
                text-align: center !important;
                color: #000 !important;
                margin-bottom: 6pt !important;
                text-transform: none !important;
            }

            /* Família, sinônimos, nomes populares */
            .art-familia,
            .art-sinonimos,
            .art-nomes {
                font-size: 11pt !important;
                text-align: center !important;
                color: #000 !important;
                margin-bottom: 4pt !important;
            }

            /* Seções (h3): numeradas, negrito, maiúsculas */
            .art-secao {
                font-family: 'Times New Roman', Times, serif !important;
                font-size: 12pt !important;
                font-weight: bold !important;
                text-transform: uppercase !important;
                color: #000 !important;
                border-bottom: none !important;
                margin: 18pt 0 6pt !important;
                page-break-after: avoid !important;
            }

            /* Parágrafos: recuo ABNT, justificado */
            .art-paragrafo {
                font-size: 12pt !important;
                line-height: 1.5 !important;
                text-align: justify !important;
                text-indent: 1.25cm !important;
                margin-bottom: 0 !important;
                color: #000 !important;
            }

            /* Referências sobrescritas */
            .art-paragrafo sup {
                font-size: 8pt !important;
                color: #000 !important;
            }

            /* ── Prancha fotográfica ── */
            .art-galeria {
                display: block !important;
                columns: 3 !important;
                column-gap: 12pt !important;
                margin: 12pt 0 !important;
            }

            .art-figura {
                display: inline-block !important;
                width: 100% !important;
                text-align: center !important;
                margin-bottom: 12pt !important;
                page-break-inside: avoid !important;
            }

            .art-figura img {
                width: 100% !important;
                max-width: 140pt !important;
                height: auto !important;
                border: 1px solid #ccc !important;
                display: block !important;
                margin: 0 auto !important;
            }

            /* Legenda: ABNT — abaixo da figura, 10pt */
            .art-figura figcaption {
                font-size: 10pt !important;
                color: #000 !important;
                text-align: center !important;
                max-width: 100% !important;
                margin-top: 4pt !important;
                font-style: italic !important;
            }

            /* ── Referências: espaço simples, 6pt entre itens ── */
            .art-refs {
                font-size: 11pt !important;
                line-height: 1.2 !important;
                color: #000 !important;
                padding-left: 0 !important;
                list-style-position: inside !important;
            }

            .art-refs li {
                margin-bottom: 6pt !important;
                text-align: justify !important;
            }

            /* Controle de quebra de página */
            .art-galeria { page-break-inside: avoid; }
            .art-refs    { page-break-before: auto; }
            h2, h3       { page-break-after: avoid; }
            p            { orphans: 3; widows: 3; }
        }

        @media (max-width: 640px) {
            .card-artigo { padding: 24px 20px; }
            .card-creditos { padding: 20px; }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header class="header">
    <a href="<?= APP_BASE ?>/" class="header-logo">
        <i class="fas fa-leaf"></i> Penomato
    </a>
    <a href="javascript:history.back()" class="btn-voltar">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</header>

<!-- HERO -->
<div class="hero">
    <div class="hero-label">Artigo Científico</div>
    <div class="hero-nome"><?= htmlspecialchars($artigo['nome_cientifico']) ?></div>
    <?php if ($artigo['familia']): ?>
        <div class="hero-familia">Família <?= htmlspecialchars($artigo['familia']) ?></div>
    <?php endif; ?>

    <?php if ($autores_raw): ?>
    <div class="hero-autores">
        <?php foreach ($autores_raw as $i => $a): ?>
            <?php if ($i > 0): ?><span class="hero-autores-sep">·</span><?php endif; ?>
            <span class="hero-autor-nome"><?= htmlspecialchars($a['nome']) ?></span>
            <?php if ($a['numeros']): ?>
            <sup class="hero-autor-nums"><?= implode(',', $a['numeros']) ?></sup>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="hero-badge" style="background:<?= $ast_info['cor'] ?>; border-color:<?= $ast_info['cor'] ?>;">
        <i class="fas fa-circle-dot"></i> <?= htmlspecialchars($ast_info['label']) ?>
        <?php if ($artigo['artigo_status'] === 'publicado'): ?>
         · <?= $data_pub ?>
        <?php endif; ?>
    </div>
    <?php if ($ast_info['aviso']): ?>
    <div style="margin-top:14px; background:rgba(0,0,0,.25); border-radius:8px; padding:10px 18px; font-size:.82rem; max-width:600px;">
        <i class="fas fa-circle-info"></i> <?= htmlspecialchars($ast_info['aviso']) ?>
    </div>
    <?php endif; ?>
</div>

<!-- CONTEÚDO -->
<div class="page-wrap">

    <!-- Ações -->
    <div class="acoes">
        <button class="btn-acao btn-imprimir" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir / PDF
        </button>
        <a href="<?= APP_BASE ?>/src/Views/publico/busca_caracteristicas.php" class="btn-acao btn-busca">
            <i class="fas fa-search"></i> Nova busca
        </a>
    </div>

    <!-- Artigo -->
    <div class="card-artigo">

        <!-- Bloco de título ABNT — oculto na web, visível na impressão -->
        <div class="print-cabecalho">
            <div class="print-titulo"><?= htmlspecialchars($artigo['nome_cientifico']) ?></div>

            <?php
            $autores_print = [];
            if (!empty($artigo['nome_colaborador'])) $autores_print[] = $artigo['nome_colaborador'];
            if (!empty($artigo['nome_publicador']))  $autores_print[] = $artigo['nome_publicador'];
            ?>
            <?php if ($autores_print): ?>
            <div class="print-autores"><?= htmlspecialchars(implode(' · ', $autores_print)) ?></div>
            <?php endif; ?>

            <?php
            $insts_print = array_filter(array_unique([
                $artigo['inst_colaborador'] ?? '',
                $artigo['inst_publicador']  ?? '',
            ]));
            ?>
            <?php if ($insts_print): ?>
            <div class="print-inst"><?= htmlspecialchars(implode(' / ', $insts_print)) ?></div>
            <?php endif; ?>

            <hr class="print-linha">
            <div class="print-data">Publicado em: <?= $data_pub ?> · Penomato — UFMS / UEMS</div>
        </div>

        <?= $artigo['texto_html'] ?>
    </div>

    <!-- Créditos -->
    <div class="card-creditos">
        <div class="creditos-titulo"><i class="fas fa-users"></i> Contribuições</div>

        <?php if ($autores_raw): ?>
        <!-- Contribuidores: nome + números -->
        <div class="creditos-pessoas">
            <?php foreach ($autores_raw as $a): ?>
            <div class="credito-pessoa">
                <span class="credito-pessoa-nome"><?= htmlspecialchars($a['nome']) ?></span>
                <?php if ($a['numeros']): ?>
                <span class="credito-pessoa-nums">
                    <?php foreach ($a['numeros'] as $n): ?>
                    <span class="credito-num" title="<?= htmlspecialchars($legenda_acoes[$n]) ?>"><?= $n ?></span>
                    <?php endforeach; ?>
                </span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <div class="credito-pessoa">
                <span class="credito-pessoa-nome" style="color:#94a3b8;">Penomato — UFMS / UEMS</span>
            </div>
        </div>

        <!-- Legenda das ações -->
        <?php if ($acoes_usadas): ?>
        <div class="creditos-legenda">
            <?php foreach ($acoes_usadas as $n => $_): ?>
            <div class="legenda-item">
                <span class="legenda-num"><?= $n ?></span>
                <span class="legenda-txt"><?= htmlspecialchars($legenda_acoes[$n]) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="pub-meta">
        Publicado em <?= $data_pub ?> · Penomato — Plataforma de Documentação do Cerrado
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── 1. Adiciona id em cada <li> da lista de referências ──
    var refs = document.querySelectorAll('.art-refs li');
    refs.forEach(function (li, i) {
        var n = i + 1;
        li.id = 'ref-' + n;
        li.style.scrollMarginTop = '80px';

        // Detecta URLs no texto e transforma em link clicável
        li.innerHTML = li.innerHTML.replace(
            /(https?:\/\/[^\s<"]+)/g,
            '<a href="$1" target="_blank" rel="noopener" class="ref-url">$1</a>'
        );
    });

    // ── 2. Transforma <sup class="art-ref"> em links clicáveis ──
    // O gerador produz: <sup class="art-ref">[1,2]</sup> — remove colchetes antes de processar
    document.querySelectorAll('.card-artigo sup.art-ref').forEach(function (sup) {
        var texto = sup.textContent.trim().replace(/[\[\]]/g, '');
        // Pode ser "2" ou "2,4" ou "1,2,3"
        var numeros = texto.split(',').map(function (n) { return n.trim(); });
        var links = numeros.map(function (n) {
            var ref = document.getElementById('ref-' + n);
            if (ref) {
                // Referência existe — link âncora
                return '<a href="#ref-' + n + '" class="sup-link" title="Ver referência ' + n + '">[' + n + ']</a>';
            }
            return '[' + n + ']';
        });
        sup.innerHTML = links.join(',');
    });

});
</script>

<style>
.sup-link {
    color: var(--cor-primaria);
    text-decoration: none;
    font-weight: 700;
    font-size: .75em;
    vertical-align: super;
    border-bottom: 1px dotted var(--cor-primaria);
    transition: color .15s;
}
.sup-link:hover { color: var(--cor-primaria-hover); }

.ref-url {
    color: var(--cor-primaria);
    word-break: break-all;
    font-size: .9em;
}
.ref-url:hover { text-decoration: underline; }

/* Destaque ao rolar até a referência */
.art-refs li:target {
    background: #f0fdf4;
    border-left: 3px solid var(--cor-primaria);
    padding-left: 10px;
    border-radius: 4px;
    transition: background .3s;
}
</style>
</body>
</html>
