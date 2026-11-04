<?php
// ================================================
// GERAR FOLHA QUADRICULADA PARA EXSICATA DIGITAL
// Penomato · Protocolo de Fotodocumentação
// Tamanhos: A4, A3, A2, A1, A0
// ================================================

require_once __DIR__ . '/../../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$tamanhos_validos = ['A4', 'A3', 'A2', 'A1', 'A0'];
$tamanho = strtoupper($_GET['tamanho'] ?? 'A4');
if (!in_array($tamanho, $tamanhos_validos)) $tamanho = 'A4';

// Dimensões em mm · espaçamento da grade · margem · altura do rodapé de rótulo
$config = [
    'A4' => ['w' => 210,  'h' => 297,  'grid' => 10,  'margin' => 8,  'footer' => 38],
    'A3' => ['w' => 297,  'h' => 420,  'grid' => 10,  'margin' => 8,  'footer' => 38],
    'A2' => ['w' => 420,  'h' => 594,  'grid' => 10,  'margin' => 10, 'footer' => 45],
    'A1' => ['w' => 594,  'h' => 841,  'grid' => 50,  'margin' => 12, 'footer' => 55],
    'A0' => ['w' => 841,  'h' => 1189, 'grid' => 100, 'margin' => 15, 'footer' => 70],
];

$c      = $config[$tamanho];
$w      = $c['w'];
$h      = $c['h'];
$grid   = $c['grid'];
$margin = $c['margin'];
$fh     = $c['footer'];  // altura do rodapé em mm

// Área disponível para a grade
$area_w = $w - 2 * $margin;
$area_h = $h - 2 * $margin - $fh;

// Número exato de células
$cols = (int) floor($area_w / $grid);
$rows = (int) floor($area_h / $grid);

// Dimensões reais da grade (pode ser ligeiramente menor que a área)
$grade_w = $cols * $grid;
$grade_h = $rows * $grid;

// Tamanho da fonte escala com o papel
$font_base  = ($tamanho === 'A4' || $tamanho === 'A3') ? 7  : ($tamanho === 'A2' ? 9  : 12);
$font_label = ($tamanho === 'A4' || $tamanho === 'A3') ? 5  : ($tamanho === 'A2' ? 7  : 9);
$border_px  = ($tamanho === 'A4' || $tamanho === 'A3') ? '0.3px' : ($tamanho === 'A2' ? '0.4px' : '0.5px');

// Cor da borda da grade
$cor_grade = '#b0b8c4';

// Gerar linhas da tabela de grade
$linhas_grade = '';
for ($r = 0; $r < $rows; $r++) {
    $linhas_grade .= '<tr>';
    for ($ci = 0; $ci < $cols; $ci++) {
        $linhas_grade .= '<td></td>';
    }
    $linhas_grade .= '</tr>';
}

// Escala legível para exibir no rodapé
$escala_txt = $grid >= 100 ? ($grid / 10) . ' cm por quadrado'
            : ($grid >= 10  ? ($grid / 10) . ' cm por quadrado'
            : $grid . ' mm por quadrado');

ob_start();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<style>

@page {
    size: <?= $tamanho ?> portrait;
    margin: 0;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    width: <?= $w ?>mm;
    height: <?= $h ?>mm;
    background: #fff;
    font-family: Arial, Helvetica, sans-serif;
}

.pagina {
    width: <?= $w ?>mm;
    height: <?= $h ?>mm;
    padding: <?= $margin ?>mm;
    display: block;
}

/* ── Grade quadriculada ── */
.grade-wrapper {
    width: <?= $grade_w ?>mm;
    height: <?= $grade_h ?>mm;
    border: 1px solid <?= $cor_grade ?>;
    overflow: hidden;
}

.grade {
    width: <?= $grade_w ?>mm;
    height: <?= $grade_h ?>mm;
    border-collapse: collapse;
    table-layout: fixed;
}

.grade td {
    width: <?= $grid ?>mm;
    height: <?= $grid ?>mm;
    border: <?= $border_px ?> solid <?= $cor_grade ?>;
    padding: 0;
}

/* ── Separador entre grade e rodapé ── */
.separador {
    margin-top: <?= $margin ?>mm;
    border-top: 0.8px solid #334155;
    margin-bottom: 2mm;
}

/* ── Rodapé de rótulo ── */
.rodape {
    width: 100%;
}

.rodape-topo {
    display: table;
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 2mm;
}

.rodape-topo td {
    display: table-cell;
    border: 0.5px solid #334155;
    padding: 1.5mm 2mm;
    vertical-align: top;
}

.campo-label {
    font-size: <?= $font_label ?>pt;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: 1mm;
}

.campo-linha {
    display: block;
    border-bottom: 0.5px solid #94a3b8;
    height: <?= $font_base + 2 ?>pt;
    width: 100%;
}

/* ── Assinatura Penomato ── */
.assinatura {
    display: table;
    width: 100%;
    margin-top: 1.5mm;
}

.assinatura-nome {
    display: table-cell;
    font-size: <?= $font_base ?>pt;
    font-weight: bold;
    color: #0b5e42;
    letter-spacing: 2px;
    text-transform: uppercase;
    vertical-align: middle;
    width: 30%;
}

.assinatura-sub {
    display: table-cell;
    font-size: <?= $font_label ?>pt;
    color: #94a3b8;
    vertical-align: middle;
    padding-left: 2mm;
    width: 50%;
}

.assinatura-escala {
    display: table-cell;
    font-size: <?= $font_label ?>pt;
    color: #334155;
    vertical-align: middle;
    text-align: right;
    width: 20%;
}

</style>
</head>
<body>
<div class="pagina">

    <!-- Grade quadriculada -->
    <div class="grade-wrapper">
        <table class="grade">
            <tbody>
                <?= $linhas_grade ?>
            </tbody>
        </table>
    </div>

    <!-- Separador -->
    <div class="separador"></div>

    <!-- Rodapé: campos de rótulo -->
    <div class="rodape">

        <!-- Linha 1: Código · Espécie · Parte da Planta -->
        <table class="rodape-topo">
            <tr>
                <td style="width:22%">
                    <span class="campo-label">Código do Exemplar</span>
                    <span class="campo-linha"></span>
                </td>
                <td style="width:50%">
                    <span class="campo-label">Espécie</span>
                    <span class="campo-linha"></span>
                </td>
                <td style="width:28%">
                    <span class="campo-label">Parte da Planta</span>
                    <span class="campo-linha"></span>
                </td>
            </tr>
        </table>

        <!-- Linha 2: Data · Coletor · Local de Coleta -->
        <table class="rodape-topo">
            <tr>
                <td style="width:22%">
                    <span class="campo-label">Data de Coleta</span>
                    <span class="campo-linha"></span>
                </td>
                <td style="width:30%">
                    <span class="campo-label">Coletor</span>
                    <span class="campo-linha"></span>
                </td>
                <td style="width:48%">
                    <span class="campo-label">Local de Coleta</span>
                    <span class="campo-linha"></span>
                </td>
            </tr>
        </table>

        <!-- Assinatura Penomato + escala -->
        <div class="assinatura">
            <span class="assinatura-nome">Penomato</span>
            <span class="assinatura-sub">Plataforma Colaborativa de Documentação Botânica do Cerrado</span>
            <span class="assinatura-escala"><?= $tamanho ?> · <?= htmlspecialchars($escala_txt) ?></span>
        </div>

    </div>

</div>
</body>
</html>
<?php
$html_pdf = ob_get_clean();

// ── Configurar DomPDF ─────────────────────────
$options = new Options();
$options->set('isRemoteEnabled', false);
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'helvetica');
$options->set('logOutputFile', '');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html_pdf, 'UTF-8');
$dompdf->setPaper(strtolower($tamanho), 'portrait');
$dompdf->render();

$nome_arquivo = 'Penomato_Folha_Quadriculada_' . $tamanho . '.pdf';
$dompdf->stream($nome_arquivo, ['Attachment' => 1]);
