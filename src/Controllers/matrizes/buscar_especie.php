<?php
// ============================================================
// AUTOCOMPLETE REFLORA — BANCO DE MATRIZES
// ============================================================

require_once __DIR__ . '/../../../config/banco_de_dados.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 3) {
    echo json_encode([]);
    exit;
}

$termo = '%' . $q . '%';

$resultados = buscarTodos(
    "SELECT nome_cientifico AS nome
     FROM flora_brasil_plantas
     WHERE nome_cientifico LIKE ?
     ORDER BY nome_cientifico
     LIMIT 10",
    [$termo]
);

echo json_encode($resultados);
