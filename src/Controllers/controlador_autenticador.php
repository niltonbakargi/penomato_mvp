<?php
// controlador_autenticador.php
// Placeholder para entrada do autenticador

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/index.php');
    exit;
}

// Redirecionar para a view do autenticador
header('Location: ' . APP_BASE . '/src/Views/entrada_autenticador.php');
exit;
?>