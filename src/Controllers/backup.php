<?php
// ============================================================
// BACKUP COMPLETO — Banco de dados + uploads/exsicatas
// ============================================================
ob_start(); // captura qualquer output/warning acidental

session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

// Função para abortar com mensagem legível
function abort(string $msg): void {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Erro no backup: ' . $msg;
    exit;
}

// ── Autenticação ─────────────────────────────────────────────
if (!isset($_SESSION['usuario_id'])) {
    ob_end_clean();
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT categoria FROM usuarios WHERE id = ? LIMIT 1");
$stmt->execute([(int)$_SESSION['usuario_id']]);
if ($stmt->fetchColumn() !== 'gestor') {
    abort('Acesso restrito a gestores.');
}

// ── Pré-requisitos ───────────────────────────────────────────
if (!class_exists('ZipArchive')) {
    abort('Extensão ZipArchive não disponível neste servidor.');
}

set_time_limit(300);
ini_set('memory_limit', '256M');

// ── Arquivo temporário dentro do projeto (Hostgator) ─────────
$data    = date('Y-m-d_H-i');
$zipName = "backup_penomato_{$data}.zip";
$zipTemp = __DIR__ . '/../../uploads/' . $zipName;

$zip = new ZipArchive();
if ($zip->open($zipTemp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    abort('Não foi possível criar o arquivo zip em ' . $zipTemp);
}

// ============================================================
// 1. EXPORTAR BANCO VIA PDO
// ============================================================
try {
    $sql_dump  = "-- Penomato backup gerado em " . date('Y-m-d H:i:s') . "\n";
    $sql_dump .= "SET NAMES utf8mb4;\n";
    $sql_dump .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    $tabelas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tabelas as $tabela) {
        $create    = $pdo->query("SHOW CREATE TABLE `$tabela`")->fetch(PDO::FETCH_NUM);
        $sql_dump .= "DROP TABLE IF EXISTS `$tabela`;\n";
        $sql_dump .= $create[1] . ";\n\n";

        $rows = $pdo->query("SELECT * FROM `$tabela`")->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {
            $colunas   = '`' . implode('`, `', array_keys($rows[0])) . '`';
            $sql_dump .= "INSERT INTO `$tabela` ($colunas) VALUES\n";
            $vals = [];
            foreach ($rows as $row) {
                $esc    = array_map(fn($v) => $v === null ? 'NULL' : $pdo->quote($v), array_values($row));
                $vals[] = '(' . implode(', ', $esc) . ')';
            }
            $sql_dump .= implode(",\n", $vals) . ";\n\n";
        }
    }

    $sql_dump .= "SET FOREIGN_KEY_CHECKS = 1;\n";
    $zip->addFromString("banco_penomato_{$data}.sql", $sql_dump);

} catch (Exception $e) {
    $zip->close();
    @unlink($zipTemp);
    abort('Falha ao exportar banco: ' . $e->getMessage());
}

// ============================================================
// 2. ARQUIVOS DE UPLOADS
// ============================================================
$dir_uploads = realpath(__DIR__ . '/../../uploads/');
if ($dir_uploads && is_dir($dir_uploads)) {
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir_uploads, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($it as $file) {
        if (!$file->isFile()) continue;
        $real = $file->getRealPath();
        // não incluir o próprio zip que estamos gerando
        if ($real === realpath($zipTemp)) continue;
        $rel = 'uploads/' . str_replace('\\', '/', substr($real, strlen($dir_uploads) + 1));
        $zip->addFile($real, $rel);
    }
}

$zip->close();

if (!file_exists($zipTemp)) {
    abort('Arquivo zip não encontrado após geração.');
}

// ============================================================
// 3. DOWNLOAD
// ============================================================
ob_end_clean(); // limpa qualquer output antes de enviar o arquivo

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipName . '"');
header('Content-Length: ' . filesize($zipTemp));
header('Pragma: no-cache');
header('Expires: 0');

readfile($zipTemp);
@unlink($zipTemp);
exit;
