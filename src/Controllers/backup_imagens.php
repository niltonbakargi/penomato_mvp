<?php
// ============================================================
// BACKUP — imagens (uploads/exsicatas) como .zip
// ============================================================
ob_start();
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) { http_response_code(403); echo 'Sem acesso.'; exit; }
$stmt = $pdo->prepare("SELECT categoria FROM usuarios WHERE id = ? LIMIT 1");
$stmt->execute([(int)$_SESSION['usuario_id']]);
if ($stmt->fetchColumn() !== 'gestor') { http_response_code(403); echo 'Acesso restrito.'; exit; }

if (!class_exists('ZipArchive')) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'ZipArchive não disponível neste servidor.';
    exit;
}

set_time_limit(300);
ini_set('memory_limit', '256M');

$data    = date('Y-m-d_H-i');
$zipName = 'imagens_penomato_' . $data . '.zip';
$zipTemp = __DIR__ . '/../../uploads/' . $zipName;

try {
    $zip = new ZipArchive();
    if ($zip->open($zipTemp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new Exception('Não foi possível criar o zip.');
    }

    $dir = realpath(__DIR__ . '/../../uploads/');
    $total = 0;

    if ($dir && is_dir($dir)) {
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        foreach ($it as $file) {
            if (!$file->isFile()) continue;
            $real = $file->getRealPath();
            if ($real === realpath($zipTemp)) continue;
            $rel  = 'uploads/' . str_replace('\\', '/', substr($real, strlen($dir) + 1));
            $zip->addFile($real, $rel);
            $total++;
        }
    }

    $zip->close();

    if ($total === 0) {
        @unlink($zipTemp);
        ob_end_clean();
        http_response_code(204);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'sem_imagens';
        exit;
    }

    ob_end_clean();
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipName . '"');
    header('Content-Length: ' . filesize($zipTemp));
    header('Pragma: no-cache');
    readfile($zipTemp);
    @unlink($zipTemp);

} catch (Exception $e) {
    @unlink($zipTemp);
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Erro: ' . $e->getMessage();
}
exit;
