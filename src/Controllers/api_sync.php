<?php
// ============================================================
// API DE BACKUP — endpoint seguro para sincronização local
// Chamado pelo script Python no PC do gestor
// ============================================================
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

header('Content-Type: application/json; charset=utf-8');

// ── Autenticação por token secreto ───────────────────────────
// Defina um token forte aqui e coloque o mesmo no script Python
define('BACKUP_TOKEN', 'penomato_backup_2026_' . md5(DB_PASS ?? 'token'));

// Aceita token via POST body (preferido), header ou query string (legado)
$token = $_POST['token']
      ?? $_SERVER['HTTP_X_BACKUP_TOKEN']
      ?? $_GET['token']
      ?? '';
if ($token !== BACKUP_TOKEN) {
    http_response_code(403);
    echo json_encode(['erro' => 'Token inválido']);
    exit;
}

$acao = $_GET['acao'] ?? '';

// ── AÇÃO: listar tabelas ─────────────────────────────────────
if ($acao === 'tabelas') {
    $tabelas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['tabelas' => $tabelas]);
    exit;
}

// ── AÇÃO: exportar uma tabela ────────────────────────────────
if ($acao === 'exportar') {
    $tabela = $_GET['tabela'] ?? '';

    // Valida que a tabela existe
    $tabelas_validas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array($tabela, $tabelas_validas, true)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Tabela inválida']);
        exit;
    }

    $create = $pdo->query("SHOW CREATE TABLE `$tabela`")->fetch(PDO::FETCH_NUM);
    $rows   = $pdo->query("SELECT * FROM `$tabela`")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'tabela'  => $tabela,
        'create'  => $create[1],
        'total'   => count($rows),
        'rows'    => $rows,
    ]);
    exit;
}

// ── AÇÃO: listar imagens ─────────────────────────────────────
if ($acao === 'listar_imagens') {
    $rows = $pdo->query("SELECT id, caminho_imagem FROM especies_imagens ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['imagens' => $rows]);
    exit;
}

// ── AÇÃO: baixar imagem individual ───────────────────────────
if ($acao === 'imagem') {
    $caminho = $_GET['caminho'] ?? '';

    // Segurança: só permite caminhos dentro de uploads/
    $caminho = ltrim(str_replace('..', '', $caminho), '/');
    if (!str_starts_with($caminho, 'uploads/')) {
        http_response_code(400);
        echo json_encode(['erro' => 'Caminho inválido']);
        exit;
    }

    $arquivo = realpath(__DIR__ . '/../../' . $caminho);
    $raiz    = realpath(__DIR__ . '/../../uploads/');

    if (!$arquivo || !$raiz || !str_starts_with($arquivo, $raiz) || !file_exists($arquivo)) {
        http_response_code(404);
        // Retorna JSON para o Python saber que não existe
        echo json_encode(['erro' => 'Arquivo não encontrado', 'caminho' => $caminho]);
        exit;
    }

    // Envia o arquivo binário
    header('Content-Type: ' . mime_content_type($arquivo));
    header('Content-Length: ' . filesize($arquivo));
    header('Content-Disposition: attachment; filename="' . basename($arquivo) . '"');
    ob_end_clean();
    readfile($arquivo);
    exit;
}

http_response_code(400);
echo json_encode(['erro' => 'Ação inválida. Use: tabelas, exportar, listar_imagens, imagem']);
