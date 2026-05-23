<?php
// ============================================================
// BACKUP — apenas o banco de dados (download .sql)
// ============================================================
ob_start();
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) { http_response_code(403); echo 'Sem acesso.'; exit; }
$stmt = $pdo->prepare("SELECT categoria FROM usuarios WHERE id = ? LIMIT 1");
$stmt->execute([(int)$_SESSION['usuario_id']]);
if ($stmt->fetchColumn() !== 'gestor') { http_response_code(403); echo 'Acesso restrito.'; exit; }

set_time_limit(120);
ini_set('memory_limit', '256M');

try {
    $data = date('Y-m-d_H-i');
    $sql  = "-- Penomato backup gerado em " . date('Y-m-d H:i:s') . "\n";
    $sql .= "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n";

    $tabelas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tabelas as $tabela) {
        $create = $pdo->query("SHOW CREATE TABLE `$tabela`")->fetch(PDO::FETCH_NUM);
        $sql .= "DROP TABLE IF EXISTS `$tabela`;\n" . $create[1] . ";\n\n";

        $rows = $pdo->query("SELECT * FROM `$tabela`")->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {
            $cols  = '`' . implode('`, `', array_keys($rows[0])) . '`';
            $vals  = [];
            foreach ($rows as $row) {
                $esc   = array_map(function($v) use ($pdo) { return $v === null ? 'NULL' : $pdo->quote($v); }, array_values($row));
                $vals[] = '(' . implode(', ', $esc) . ')';
            }
            $sql .= "INSERT INTO `$tabela` ($cols) VALUES\n" . implode(",\n", $vals) . ";\n\n";
        }
    }

    $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

    ob_end_clean();
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="banco_penomato_' . $data . '.sql"');
    header('Content-Length: ' . strlen($sql));
    header('Pragma: no-cache');
    echo $sql;

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Erro: ' . $e->getMessage();
}
exit;
