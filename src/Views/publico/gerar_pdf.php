<?php
// ================================================
// GERAR PDF DO ARTIGO CIENTÍFICO — PENOMATO
// Usa DOMPDF v3 · Formato ABNT NBR 6022:2018
// Prancha botânica em seção dedicada (estilo revista)
// ================================================

session_start();
require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../../helpers/autores_artigo.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$especie_id = (int)($_GET['id'] ?? 0);
if (!$especie_id) {
    header('Location: ' . APP_BASE . '/src/Views/publico/busca_caracteristicas.php');
    exit;
}

// ── Buscar artigo ─────────────────────────────
try {
    $stmt = $pdo->prepare("
        SELECT a.texto_html, a.status AS artigo_status,
               e.nome_cientifico, e.data_publicado,
               c.familia, c.nome_popular, c.sinonimos, c.nome_cientifico_completo
        FROM artigos a
        INNER JOIN especies_administrativo e ON e.id = a.especie_id
        LEFT JOIN especies_caracteristicas c ON c.especie_id = e.id
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
    header('Location: ' . APP_BASE . '/src/Views/publico/busca_caracteristicas.php');
    exit;
}

// ── Buscar imagens do banco (prancha botânica) ─
$raiz = realpath(__DIR__ . '/../../../');

$partes_label = [
    'habito'            => 'Hábito',
    'folha'             => 'Folha',
    'flor'              => 'Flor',
    'fruto'             => 'Fruto',
    'caule'             => 'Caule',
    'semente'           => 'Semente',
    'exsicata_completa' => 'Exsicata',
    'detalhe'           => 'Detalhe',
];

$imagens_por_parte = [];
try {
    $stmt_img = $pdo->prepare("
        SELECT parte_planta, caminho_imagem, fonte_nome, fonte_url, autor_imagem, licenca
        FROM especies_imagens
        WHERE especie_id = ?
        ORDER BY FIELD(parte_planta,'habito','folha','flor','fruto','caule','semente','exsicata_completa','detalhe'), data_upload ASC
    ");
    $stmt_img->execute([$especie_id]);
    foreach ($stmt_img->fetchAll(PDO::FETCH_ASSOC) as $img) {
        $abs = $raiz . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $img['caminho_imagem']);
        if (!file_exists($abs)) continue;
        $img['abs_path'] = str_replace('\\', '/', $abs);
        $imagens_por_parte[$img['parte_planta']][] = $img;
    }
} catch (Exception $e) {
    // sem imagens no banco — prancha não será exibida
}

// ── Autores ────────────────────────────────────
$autores_raw   = montarAutoresArtigo($pdo, $especie_id);
$nomes_autores = array_map(fn($a) => $a['nome'], $autores_raw);
$insts_autores = array_unique(array_filter(array_map(
    fn($a) => $a['instituicao'] ?? '', $autores_raw
)));
$autores_str = implode('; ', $nomes_autores);
$insts_str   = implode('; ', $insts_autores);

// ── Metadados ─────────────────────────────────
$nome_cientifico = $artigo['nome_cientifico_completo'] ?: $artigo['nome_cientifico'];
$data_pub = $artigo['data_publicado']
    ? date('d/m/Y', strtotime($artigo['data_publicado']))
    : date('d/m/Y');

// ── Processar HTML do artigo (texto limpo) ────
// 1. Converter caminhos de imagem (caso alguma esteja no texto)
$texto_html = preg_replace_callback(
    '/src="\/penomato_mvp\/([^"]+)"/',
    function ($m) use ($raiz) {
        $abs = $raiz . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $m[1]);
        return file_exists($abs)
            ? 'src="' . str_replace('\\', '/', $abs) . '"'
            : 'src=""';
    },
    $artigo['texto_html']
);

// 2. Remover a seção "Prancha Fotográfica" do texto
//    (será exibida como prancha dedicada no final)
$texto_html = str_replace('<h3 class="art-secao">Prancha Fotográfica</h3>', '', $texto_html);
$texto_html = preg_replace('/<div class="art-galeria">.*?<\/div>/s', '', $texto_html);

// 3. Remover links (<a>)
$texto_html = preg_replace('/<a\s[^>]*>(.*?)<\/a>/s', '$1', $texto_html);

// ── Aviso de status ────────────────────────────
$aviso_html = '';
if ($artigo['artigo_status'] !== 'publicado') {
    $avisos = [
        'rascunho'   => 'Rascunho preliminar gerado por IA — ainda não revisado por especialista.',
        'em_revisao' => 'Em revisão — artigo em processo de revisão científica.',
        'aprovado'   => 'Aprovado pelo especialista — aguardando publicação oficial.',
    ];
    $msg = $avisos[$artigo['artigo_status']] ?? '';
    if ($msg) {
        $aviso_html = '<div class="aviso-status">&#9888; ' . htmlspecialchars($msg) . '</div>';
    }
}

// ── Montar HTML da Prancha Botânica ───────────
function buildPrancha(array $imagens_por_parte, array $partes_label): string
{
    if (empty($imagens_por_parte)) return '';

    $html  = '<div class="prancha">';
    $html .= '<div class="prancha-cabecalho">';
    $html .= '<div class="prancha-titulo">Prancha Fotográfica</div>';
    $html .= '<hr class="prancha-linha">';
    $html .= '</div>';

    foreach ($partes_label as $parte => $label) {
        if (empty($imagens_por_parte[$parte])) continue;
        $imgs = $imagens_por_parte[$parte];

        // Cada parte começa numa nova página com título no topo
        $html .= '<div class="parte-pagina">';
        $html .= '<table class="parte-header"><tr>';
        $html .= '<td class="parte-header-linha"></td>';
        $html .= '<td class="parte-header-nome">' . htmlspecialchars(strtoupper($label)) . '</td>';
        $html .= '<td class="parte-header-linha"></td>';
        $html .= '</tr></table>';

        // Imagens em pares (2 por linha)
        $pares = array_chunk($imgs, 2);
        $html .= '<table class="parte-imgs"><tbody>';
        foreach ($pares as $par) {
            $html .= '<tr>';
            foreach ($par as $img) {
                $cred = [];
                if (!empty($img['autor_imagem'])) $cred[] = '© ' . $img['autor_imagem'];
                if (!empty($img['fonte_nome']))   $cred[] = $img['fonte_nome'];
                if (!empty($img['licenca']))      $cred[] = $img['licenca'];
                $cred_txt = htmlspecialchars(implode(' · ', $cred));

                $html .= '<td class="parte-img-cell">';
                $html .= '<img src="' . htmlspecialchars($img['abs_path']) . '" alt="' . htmlspecialchars($label) . '">';
                if ($cred_txt) {
                    $html .= '<div class="parte-img-credito">' . $cred_txt . '</div>';
                }
                $html .= '</td>';
            }
            // célula vazia se número ímpar
            if (count($par) < 2) {
                $html .= '<td class="parte-img-cell"></td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        $html .= '</div>'; // /parte-pagina
    }

    $html .= '</div>';
    return $html;
}

$prancha_html = buildPrancha($imagens_por_parte, $partes_label);

// ── Montar HTML completo do PDF ────────────────
ob_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<style>

/* ══ PÁGINA A4 ═══════════════════════════════ */
@page {
    size: A4 portrait;
    margin: 3cm 2cm 2cm 3cm;
}

body {
    font-family: 'Times New Roman', Times, serif;
    font-size: 12pt;
    line-height: 1.5;
    color: #000;
    background: #fff;
}

/* ══ CABEÇALHO PENOMATO ═══════════════════════ */
.pdf-cabecalho { text-align: center; margin-bottom: 10pt; }

.pdf-logo-nome {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 13pt; font-weight: bold;
    color: #0b5e42; letter-spacing: 4px; text-transform: uppercase;
}

.pdf-logo-sub {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 8pt; color: #475569; margin-top: 2pt;
}

.pdf-linha-verde {
    border: none; border-top: 2px solid #0b5e42;
    margin: 10pt 0 18pt 0;
}

/* ══ AVISO STATUS ══════════════════════════════ */
.aviso-status {
    background: #fff3cd; border: 1px solid #f59e0b;
    border-left: 4px solid #f59e0b; padding: 7pt 10pt;
    margin-bottom: 16pt; font-size: 10pt;
    font-family: Arial, Helvetica, sans-serif; color: #856404;
}

/* ══ BLOCO ABNT ════════════════════════════════ */
.abnt-titulo {
    font-family: 'Times New Roman', Times, serif;
    font-size: 14pt; font-weight: bold; font-style: italic;
    text-align: center; margin-bottom: 8pt;
    line-height: 1.3; text-transform: uppercase;
}

.abnt-autores {
    font-size: 11pt; text-align: center; margin-bottom: 3pt;
    font-family: 'Times New Roman', Times, serif;
}

.abnt-inst {
    font-size: 10pt; text-align: center; color: #333;
    font-style: italic; margin-bottom: 14pt;
    font-family: 'Times New Roman', Times, serif;
}

.abnt-linha { border: none; border-top: 1px solid #000; margin: 12pt 0; }

.abnt-data {
    font-size: 9pt; text-align: right; color: #555;
    font-style: italic; margin-bottom: 22pt;
}

/* ══ CORPO DO ARTIGO ═══════════════════════════ */
.artigo {
    font-family: 'Times New Roman', Times, serif;
    font-size: 12pt; line-height: 1.5; color: #000;
}

.art-titulo, .art-familia, .art-sinonimos,
.art-nomes, .art-autores { display: none; }

.art-secao {
    font-family: 'Times New Roman', Times, serif;
    font-size: 12pt; font-weight: bold; text-transform: uppercase;
    border: none; padding: 0; margin: 18pt 0 6pt 0;
    page-break-after: avoid; color: #000;
}

.art-paragrafo {
    font-size: 12pt; line-height: 1.5; text-align: justify;
    text-indent: 1.25cm; margin: 0; color: #000;
}

.art-paragrafo sup, .art-ref {
    font-size: 8pt; color: #000; vertical-align: super;
}

/* ══ REFERÊNCIAS ════════════════════════════════ */
.art-refs {
    font-size: 11pt; line-height: 1.5;
    padding-left: 16pt; margin: 0; color: #000;
}
.art-refs li { margin-bottom: 5pt; }

/* ══ PRANCHA BOTÂNICA ════════════════════════ */
.prancha {
    page-break-before: always;
}

.prancha-cabecalho { text-align: center; margin-bottom: 18pt; }

.prancha-titulo {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11pt; font-weight: bold;
    color: #0b5e42; letter-spacing: 3px;
    text-transform: uppercase;
}

.prancha-linha {
    border: none; border-top: 2px solid #0b5e42;
    margin: 8pt 0 0 0;
}

/* Nova página por parte da planta */
.parte-pagina {
    page-break-before: always;
}

/* Cabeçalho de cada parte (linha ━━ NOME ━━) */
.parte-header {
    width: 100%;
    border-collapse: collapse;
    margin: 0 0 18pt 0;
}

.parte-header-linha {
    border-top: 2px solid #0b5e42;
    width: 35%;
}

.parte-header-nome {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 14pt; font-weight: bold;
    letter-spacing: 5px; color: #0b5e42;
    text-align: center;
    padding: 0 14pt;
    white-space: nowrap;
}

/* Grid de imagens (2 colunas) */
.parte-imgs {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 6pt;
    page-break-inside: avoid;
}

.parte-img-cell {
    width: 50%;
    text-align: center;
    vertical-align: top;
    padding: 0 8pt 12pt 8pt;
}

.parte-img-cell img {
    max-width: 240px;
    max-height: 185px;
    display: block;
    margin: 0 auto;
    border: 1px solid #ddd;
}

.parte-img-credito {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 6.5pt; color: #666;
    text-align: center; margin-top: 4pt;
    line-height: 1.4; font-style: italic;
}

/* ══ RODAPÉ DO DOCUMENTO ════════════════════════ */
.pdf-rodape {
    margin-top: 28pt; border-top: 1px solid #cbd5e1;
    padding-top: 7pt; text-align: center;
    font-size: 8.5pt; color: #64748b;
    font-family: Arial, Helvetica, sans-serif;
}

</style>
</head>
<body>

<?php echo $aviso_html; ?>

<!-- ── Cabeçalho Penomato ── -->
<div class="pdf-cabecalho">
    <div class="pdf-logo-nome">Penomato</div>
    <div class="pdf-logo-sub">Plataforma Colaborativa de Documentação Botânica &middot; penomato.app.br</div>
</div>
<hr class="pdf-linha-verde">

<!-- ── Bloco ABNT ── -->
<p class="abnt-titulo"><?= htmlspecialchars($nome_cientifico) ?></p>

<?php if ($autores_str): ?>
<p class="abnt-autores"><?= htmlspecialchars($autores_str) ?></p>
<?php endif; ?>

<?php if ($insts_str): ?>
<p class="abnt-inst"><?= htmlspecialchars($insts_str) ?></p>
<?php endif; ?>

<hr class="abnt-linha">
<p class="abnt-data">
    Publicado em: <?= $data_pub ?> &nbsp;&middot;&nbsp; Penomato &mdash; penomato.app.br
</p>

<!-- ── Corpo do artigo (texto limpo, sem imagens) ── -->
<div class="artigo">
    <?= $texto_html ?>
</div>

<!-- ── Prancha botânica (nova página, estilo revista) ── -->
<?= $prancha_html ?>

<!-- ── Rodapé ── -->
<div class="pdf-rodape">
    Penomato &middot; penomato.app.br &middot; Documentação Científica Colaborativa do Cerrado Brasileiro<br>
    PDF gerado em <?= date('d/m/Y \à\s H:i') ?>
</div>

</body>
</html>
<?php
$html_pdf = ob_get_clean();

// ── Configurar e rodar DOMPDF ─────────────────
$options = new Options();
$options->set('isRemoteEnabled', false);
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'serif');
$options->set('chroot', [$raiz, sys_get_temp_dir()]);
$options->set('logOutputFile', '');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html_pdf, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// ── Streaming do PDF ──────────────────────────
$nome_arquivo = 'Penomato_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $nome_cientifico) . '.pdf';

$modo_download = isset($_GET['download']) ? 1 : 0;
$dompdf->stream($nome_arquivo, ['Attachment' => $modo_download]);
