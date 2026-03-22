<?php
// controlador_colaborador.php
// Placeholder para entrada do colaborador

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/index.php');
    exit;
}

// Redirecionar para a view do colaborador
header('Location: ' . APP_BASE . '/src/Views/entrar_colaborador.php');
exit;
?>