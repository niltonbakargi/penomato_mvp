<?php
// ============================================================
// LER GPS DO EXIF DA FOTO — endpoint AJAX
// Recebe a foto via POST, lê o EXIF server-side e retorna JSON
// ============================================================
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'erro' => 'Não autenticado']);
    exit;
}

if (empty($_FILES['foto']['tmp_name']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['ok' => false, 'erro' => 'Nenhum arquivo recebido']);
    exit;
}

if (!function_exists('exif_read_data')) {
    echo json_encode(['ok' => false, 'erro' => 'exif não disponível no servidor']);
    exit;
}

$exif = @exif_read_data($_FILES['foto']['tmp_name']);

if (!$exif || !isset($exif['GPSLatitude'], $exif['GPSLongitude'])) {
    echo json_encode(['ok' => false, 'erro' => 'GPS não encontrado no EXIF']);
    exit;
}

function fracaoParaDecimal(string $valor): float {
    $partes = explode('/', $valor);
    return count($partes) === 2 ? (float)$partes[0] / max(1, (float)$partes[1]) : (float)$valor;
}

function grausParaDecimal(array $arr): float {
    return fracaoParaDecimal($arr[0])
         + fracaoParaDecimal($arr[1]) / 60
         + fracaoParaDecimal($arr[2]) / 3600;
}

$lat = grausParaDecimal($exif['GPSLatitude']);
$lng = grausParaDecimal($exif['GPSLongitude']);

if (($exif['GPSLatitudeRef']  ?? 'N') === 'S') $lat *= -1;
if (($exif['GPSLongitudeRef'] ?? 'E') === 'W') $lng *= -1;

echo json_encode(['ok' => true, 'lat' => $lat, 'lng' => $lng]);
exit;
