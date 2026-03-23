<?php
require_once __DIR__ . '/config/app.php';

echo "APP_ENV: " . APP_ENV . "<br>";
echo "DB_HOST: " . DB_HOST . "<br>";
echo "DB_NAME: " . DB_NAME . "<br>";
echo "DB_USER: " . DB_USER . "<br>";
echo "<br>";

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<b style='color:green'>Conexão OK!</b>";
} catch (PDOException $e) {
    echo "<b style='color:red'>Erro: " . $e->getMessage() . "</b>";
}
