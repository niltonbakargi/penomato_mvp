<?php
// ================================================
// Script de uso único: regenera todos os artigos
// Acesso: apenas administradores
// ================================================
session_start();
require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../helpers/gerador_artigo.php';

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'gestor') {
    http_response_code(403);
    die('Acesso negado.');
}

$ids = $pdo->query("SELECT especie_id FROM artigos ORDER BY especie_id")->fetchAll(PDO::FETCH_COLUMN);

$ok  = 0;
$err = 0;
foreach ($ids as $id) {
    try {
        regenerarArtigoEspecie($pdo, (int)$id);
        $ok++;
    } catch (Exception $e) {
        $err++;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Regeneração de artigos — Penomato</title>
    <link rel="stylesheet" href="<?= APP_BASE ?>/assets/css/estilo.css">
</head>
<body style="padding:40px; font-family:sans-serif;">
    <h2>Regeneração de artigos</h2>
    <p>Total processado: <strong><?= $ok + $err ?></strong></p>
    <p style="color:green;">Atualizados com sucesso: <strong><?= $ok ?></strong></p>
    <?php if ($err): ?>
    <p style="color:red;">Com erro: <strong><?= $err ?></strong></p>
    <?php endif; ?>
    <br>
    <a href="<?= APP_BASE ?>/src/Controllers/controlador_gestor.php">← Voltar ao painel</a>
</body>
</html>
