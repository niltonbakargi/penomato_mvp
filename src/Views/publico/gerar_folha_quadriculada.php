<?php
// ================================================
// GERAR FOLHA QUADRICULADA PARA EXSICATA DIGITAL
// Penomato · Protocolo de Fotodocumentação
// ================================================

require_once __DIR__ . '/../../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$tamanhos_validos = ['A4', 'A3', 'A2', 'A1', 'A0'];
$tamanho = strtoupper($_GET['tamanho'] ?? 'A4');
if (!in_array($tamanho, $tamanhos_validos)) $tamanho = 'A4';

// Dimensões em mm · espaçamento da grade · margem · caixa da etiqueta (cols × rows)
$config = [
    //          w     h    grid  margin  lbl_cols  lbl_rows
    'A4' => ['w'=>210, 'h'=>297, 'grid'=>10, 'margin'=>8, 'lc'=>7,  'lr'=>4 ],
    'A3' => ['w'=>297, 'h'=>420, 'grid'=>10, 'margin'=>8, 'lc'=>10, 'lr'=>5 ],
    'A2' => ['w'=>420, 'h'=>594, 'grid'=>10, 'margin'=>10,'lc'=>12, 'lr'=>7 ],
    'A1' => ['w'=>594, 'h'=>841, 'grid'=>50, 'margin'=>12,'lc'=>3,  'lr'=>2 ],
    'A0' => ['w'=>841, 'h'=>1189,'grid'=>100,'margin'=>15,'lc'=>2,  'lr'=>2 ],
];

$c      = $config[$tamanho];
$w      = $c['w'];
$h      = $c['h'];
$grid   = $c['grid'];
$margin = $c['margin'];
$lc     = $c['lc'];   // colunas da caixa de etiqueta
$lr     = $c['lr'];   // linhas da caixa de etiqueta

// Grade preenche toda a área útil
$area_w = $w - 2 * $margin;
$area_h = $h - 2 * $margin;

$cols = (int) floor($area_w / $grid);
$rows = (int) floor($area_h / $grid);

// Garantir que a caixa não seja maior que a grade
$lc = min($lc, $cols - 1);
$lr = min($lr, $rows - 1);

// Linha e coluna onde começa a caixa de etiqueta (canto inferior direito)
$label_start_row = $rows - $lr;
$label_start_col = $cols - $lc;

// Espessura da borda da grade por tamanho
$border = ($tamanho === 'A4' || $tamanho === 'A3') ? '0.3px' : ($tamanho === 'A2' ? '0.4px' : '0.5px');
$cor_grade = '#b0b8c4';

// ── Gerar linhas da tabela ──────────────────────────────────────────
$linhas_grade = '';
for ($r = 0; $r < $rows; $r++) {
    $linhas_grade .= '<tr>';
    for ($ci = 0; $ci < $cols; $ci++) {

        // Canto superior esquerdo da caixa de etiqueta → célula mesclada
        if ($r === $label_start_row && $ci === $label_start_col) {
            $linhas_grade .= '<td class="caixa-etiqueta" colspan="' . $lc . '" rowspan="' . $lr . '"></td>';
            continue;
        }

        // Células dentro da área da caixa (já cobertas pelo rowspan/colspan)
        if ($r >= $label_start_row && $ci >= $label_start_col) {
            continue;
        }

        $linhas_grade .= '<td></td>';
    }
    $linhas_grade .= '</tr>';
}

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
}

.pagina {
    width: <?= $w ?>mm;
    height: <?= $h ?>mm;
    padding: <?= $margin ?>mm;
}

.grade {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.grade td {
    width: <?= $grid ?>mm;
    height: <?= $grid ?>mm;
    border: <?= $border ?> solid <?= $cor_grade ?>;
    padding: 0;
}

/* Caixa de etiqueta: branca, sem grade interna, borda escura */
.caixa-etiqueta {
    background: #ffffff !important;
    border: 1px solid #334155 !important;
    padding: 0 !important;
    vertical-align: top;
}

</style>
</head>
<body>
<div class="pagina">
    <table class="grade">
        <tbody>
            <?= $linhas_grade ?>
        </tbody>
    </table>
</div>
</body>
</html>
<?php
$html_pdf = ob_get_clean();

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
