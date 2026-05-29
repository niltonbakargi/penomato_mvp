<?php
// ============================================================
// PROCESSAR COMENTÁRIO DE MATRIZ
// ============================================================

require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../auth/verificar_acesso.php';

if (!estaLogado()) {
    header('Location: /penomato_mvp/src/Views/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /penomato_mvp/src/Views/matrizes/mapa.php');
    exit;
}

$matriz_id = intval($_POST['matriz_id'] ?? 0);
$texto     = trim($_POST['texto'] ?? '');

if (!$matriz_id || empty($texto)) {
    setMensagem('erro', 'Comentário inválido.');
    header("Location: /penomato_mvp/src/Views/matrizes/ficha.php?id={$matriz_id}");
    exit;
}

// Verifica se a matriz existe
$matriz = buscarUm("SELECT id FROM matrizes WHERE id = ? AND status = 'ativo'", [$matriz_id]);
if (!$matriz) {
    header('Location: /penomato_mvp/src/Views/matrizes/mapa.php');
    exit;
}

inserir('matrizes_comentarios', [
    'matriz_id'  => $matriz_id,
    'usuario_id' => $_SESSION['usuario_id'],
    'texto'      => $texto,
]);

setMensagem('sucesso', 'Comentário enviado.');
header("Location: /penomato_mvp/src/Views/matrizes/ficha.php?id={$matriz_id}");
exit;
