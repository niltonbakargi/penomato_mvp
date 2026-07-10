<?php
// ================================================
// GERAR PDF DO ARTIGO CIENTÍFICO — PENOMATO
// Usa DOMPDF v3 · Formato ABNT NBR 6022:2018
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

// ── Autores ────────────────────────────────────
$autores_raw = montarAutoresArtigo($pdo, $especie_id);
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

// ── Processar HTML do artigo ──────────────────
$raiz = realpath(__DIR__ . '/../../../');

// 1. Converter caminhos de imagem /penomato_mvp/... → caminho absoluto do filesystem
$texto_html = preg_replace_callback(
    '/src="\/penomato_mvp\/([^"]+)"/',
    function ($m) use ($raiz) {
        $sep  = DIRECTORY_SEPARATOR;
        $abs  = $raiz . $sep . str_replace('/', $sep, $m[1]);
        return file_exists($abs)
            ? 'src="' . str_replace('\\', '/', $abs) . '"'
            : 'src=""';
    },
    $artigo['texto_html']
);

// 2. Converter galeria flex → tabela 3 colunas (DOMPDF não suporta flex)
$texto_html = preg_replace_callback(
    '/<div class="art-galeria">(.*?)<\/div>/s',
    function ($m) {
        preg_match_all('/<figure class="art-figura">(.*?)<\/figure>/s', $m[1], $figs);
        if (empty($figs[0])) return $m[0];
        $rows = array_chunk($figs[0], 3);
        $tbl = '<table class="galeria-table"><tbody>';
        foreach ($rows as $row) {
            $tbl .= '<tr>';
            foreach ($row as $fig) {
                $tbl .= '<td class="galeria-cell">' . $fig . '</td>';
            }
            for ($i = count($row); $i < 3; $i++) {
                $tbl .= '<td class="galeria-cell"></td>';
            }
            $tbl .= '</tr>';
        }
        return $tbl . '</tbody></table>';
    },
    $texto_html
);

// 3. Remover links (<a>) — DOMPDF os exibe mas podem confundir no PDF
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
.pdf-cabecalho {
    text-align: center;
    margin-bottom: 10pt;
}

.pdf-logo-nome {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 13pt;
    font-weight: bold;
    color: #0b5e42;
    letter-spacing: 4px;
    text-transform: uppercase;
}

.pdf-logo-sub {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 8pt;
    color: #475569;
    margin-top: 2pt;
}

.pdf-linha-verde {
    border: none;
    border-top: 2px solid #0b5e42;
    margin: 10pt 0 18pt 0;
}

/* ══ AVISO STATUS ══════════════════════════════ */
.aviso-status {
    background: #fff3cd;
    border: 1px solid #f59e0b;
    border-left: 4px solid #f59e0b;
    padding: 7pt 10pt;
    margin-bottom: 16pt;
    font-size: 10pt;
    font-family: Arial, Helvetica, sans-serif;
    color: #856404;
}

/* ══ BLOCO ABNT ════════════════════════════════ */
.abnt-titulo {
    font-family: 'Times New Roman', Times, serif;
    font-size: 14pt;
    font-weight: bold;
    font-style: italic;
    text-align: center;
    margin-bottom: 8pt;
    line-height: 1.3;
    text-transform: uppercase;
}

.abnt-autores {
    font-size: 11pt;
    text-align: center;
    margin-bottom: 3pt;
    font-family: 'Times New Roman', Times, serif;
}

.abnt-inst {
    font-size: 10pt;
    text-align: center;
    color: #333;
    font-style: italic;
    margin-bottom: 14pt;
    font-family: 'Times New Roman', Times, serif;
}

.abnt-linha {
    border: none;
    border-top: 1px solid #000;
    margin: 12pt 0;
}

.abnt-data {
    font-size: 9pt;
    text-align: right;
    color: #555;
    font-style: italic;
    margin-bottom: 22pt;
}

/* ══ CORPO DO ARTIGO ═══════════════════════════ */
.artigo {
    font-family: 'Times New Roman', Times, serif;
    font-size: 12pt;
    line-height: 1.5;
    color: #000;
}

/* Ocultar cabeçalho já repetido no bloco ABNT */
.art-titulo,
.art-familia,
.art-sinonimos,
.art-nomes,
.art-autores {
    display: none;
}

.art-secao {
    font-family: 'Times New Roman', Times, serif;
    font-size: 12pt;
    font-weight: bold;
    text-transform: uppercase;
    border: none;
    padding: 0;
    margin: 18pt 0 6pt 0;
    page-break-after: avoid;
    color: #000;
}

.art-paragrafo {
    font-size: 12pt;
    line-height: 1.5;
    text-align: justify;
    text-indent: 1.25cm;
    margin: 0;
    color: #000;
}

.art-paragrafo sup,
.art-ref {
    font-size: 8pt;
    color: #000;
    vertical-align: super;
}

/* ══ GALERIA FOTOGRÁFICA (tabela) ════════════ */
.galeria-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 8pt 6pt;
    margin: 10pt 0;
}

.galeria-cell {
    width: 33%;
    text-align: center;
    vertical-align: top;
}

.art-figura {
    display: block;
    text-align: center;
}

.art-figura img {
    max-width: 130px;
    max-height: 100px;
    border: 1px solid #ccc;
    display: block;
    margin: 0 auto;
}

.art-figura figcaption,
.art-figura-titulo {
    font-size: 7.5pt;
    color: #555;
    margin-top: 3pt;
    font-style: italic;
    line-height: 1.3;
    text-align: center;
    font-family: 'Times New Roman', Times, serif;
}

/* ══ REFERÊNCIAS ════════════════════════════════ */
.art-refs {
    font-size: 11pt;
    line-height: 1.5;
    padding-left: 16pt;
    margin: 0;
    color: #000;
}

.art-refs li {
    margin-bottom: 5pt;
}

/* ══ RODAPÉ DO DOCUMENTO ════════════════════════ */
.pdf-rodape {
    margin-top: 28pt;
    border-top: 1px solid #cbd5e1;
    padding-top: 7pt;
    text-align: center;
    font-size: 8.5pt;
    color: #64748b;
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

<!-- ── Bloco ABNT: título, autores, data ── -->
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

<!-- ── Corpo do artigo ── -->
<div class="artigo">
    <?= $texto_html ?>
</div>

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
$options->set('isRemoteEnabled', false);       // imagens via filesystem (mais seguro)
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

// Attachment = 0 → abre no browser | 1 → força download
$modo_download = isset($_GET['download']) ? 1 : 0;
$dompdf->stream($nome_arquivo, ['Attachment' => $modo_download]);
