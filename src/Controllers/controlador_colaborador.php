<?php
// controlador_colaborador.php
// Placeholder para entrada do colaborador

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /penomato_mvp/index.php');
    exit;
}

// Redirecionar para a view do colaborador
header('Location: /penomato_mvp/src/Views/entrar_colaborador.php');
exit;
?>