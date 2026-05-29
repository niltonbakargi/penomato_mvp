<?php
// ============================================================
// BUSCA NOME CIENTÍFICO A PARTIR DO NOME POPULAR — REFLORA
// ============================================================

require_once __DIR__ . '/../../../config/banco_de_dados.php';

header('Content-Type: application/json; charset=utf-8');

$popular = trim($_GET['popular'] ?? '');

if (empty($popular)) {
    echo json_encode(['nome' => null]);
    exit;
}

$termo = '%' . $popular . '%';

$resultado = buscarUm(
    "SELECT nome_cientifico
     FROM flora_brasil_plantas
     WHERE nomes_vernaculares LIKE ?
     ORDER BY nome_cientifico
     LIMIT 1",
    [$termo]
);

echo json_encode(['nome' => $resultado ? $resultado['nome_cientifico'] : null]);
