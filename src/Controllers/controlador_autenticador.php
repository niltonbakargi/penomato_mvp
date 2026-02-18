<?php
// controlador_autenticador.php
// Placeholder para entrada do autenticador

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /penomato_mvp/index.php');
    exit;
}

// Redirecionar para a view do autenticador
header('Location: /penomato_mvp/src/Views/entrada_autenticador.php');
exit;
?>