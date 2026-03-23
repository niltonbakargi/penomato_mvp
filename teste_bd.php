<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/banco_de_dados.php';

echo "DB_HOST: " . DB_HOST . "<br>";
echo "DB_NAME: " . DB_NAME . "<br>";
echo "DB_USER: " . DB_USER . "<br>";

try {
    $pdo->query("SELECT 1");
    echo "<b style='color:green'>Conexão OK!</b>";
} catch (Exception $e) {
    echo "<b style='color:red'>Erro: " . $e->getMessage() . "</b>";
}
