<?php
// ============================================================
// BACKUP COMPLETO — Banco de dados + uploads/exsicatas
// Gera um .zip para download imediato
// Acesso restrito a gestores
// ============================================================

session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

// ── Verificar que é gestor ───────────────────────────────────
$stmt = $pdo->prepare("SELECT categoria FROM usuarios WHERE id = ? LIMIT 1");
$stmt->execute([(int)$_SESSION['usuario_id']]);
$cat = $stmt->fetchColumn();
if ($cat !== 'gestor') {
    http_response_code(403);
    die('Acesso restrito.');
}

// ── Verificar extensão ZipArchive ────────────────────────────
if (!class_exists('ZipArchive')) {
    die('Extensão ZipArchive não disponível neste servidor.');
}

set_time_limit(300);
ini_set('memory_limit', '256M');

$data    = date('Y-m-d_H-i');
$zipName = "backup_penomato_{$data}.zip";
$zipTemp = sys_get_temp_dir() . '/' . $zipName;

$zip = new ZipArchive();
if ($zip->open($zipTemp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die('Não foi possível criar o arquivo de backup.');
}

// ============================================================
// 1. EXPORTAR BANCO DE DADOS VIA PDO
// ============================================================
$sql_dump = "-- Penomato backup gerado em " . date('Y-m-d H:i:s') . "\n";
$sql_dump .= "SET NAMES utf8mb4;\n";
$sql_dump .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

$tabelas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tabelas as $tabela) {
    // Estrutura
    $create = $pdo->query("SHOW CREATE TABLE `$tabela`")->fetch(PDO::FETCH_NUM);
    $sql_dump .= "DROP TABLE IF EXISTS `$tabela`;\n";
    $sql_dump .= $create[1] . ";\n\n";

    // Dados
    $rows = $pdo->query("SELECT * FROM `$tabela`")->fetchAll(PDO::FETCH_ASSOC);
    if ($rows) {
        $colunas = '`' . implode('`, `', array_keys($rows[0])) . '`';
        $sql_dump .= "INSERT INTO `$tabela` ($colunas) VALUES\n";
        $vals = [];
        foreach ($rows as $row) {
            $escapados = array_map(function($v) use ($pdo) {
                if ($v === null) return 'NULL';
                return $pdo->quote($v);
            }, array_values($row));
            $vals[] = '(' . implode(', ', $escapados) . ')';
        }
        $sql_dump .= implode(",\n", $vals) . ";\n\n";
    }
}

$sql_dump .= "SET FOREIGN_KEY_CHECKS = 1;\n";

$zip->addFromString("banco_penomato_{$data}.sql", $sql_dump);

// ============================================================
// 2. INCLUIR ARQUIVOS DE UPLOADS
// ============================================================
$dir_uploads = realpath(__DIR__ . '/../../uploads/');

if ($dir_uploads && is_dir($dir_uploads)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir_uploads, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $caminho_real     = $file->getRealPath();
            $caminho_relativo = 'uploads/' . str_replace('\\', '/', substr($caminho_real, strlen($dir_uploads) + 1));
            $zip->addFile($caminho_real, $caminho_relativo);
        }
    }
}

$zip->close();

// ============================================================
// 3. ENVIAR PARA DOWNLOAD
// ============================================================
if (!file_exists($zipTemp)) {
    die('Erro ao gerar o arquivo de backup.');
}

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipName . '"');
header('Content-Length: ' . filesize($zipTemp));
header('Pragma: no-cache');
header('Expires: 0');

readfile($zipTemp);
@unlink($zipTemp);
exit;
