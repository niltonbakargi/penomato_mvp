<?php
// Diagnóstico temporário — REMOVER após corrigir o problema
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo '<pre>';
echo '=== PHP VERSION ==='.PHP_EOL;
echo PHP_VERSION . PHP_EOL . PHP_EOL;

echo '=== HTTP_HOST ==='.PHP_EOL;
echo ($_SERVER['HTTP_HOST'] ?? 'n/a') . PHP_EOL . PHP_EOL;

echo '=== APP.PHP ==='.PHP_EOL;
$app = __DIR__ . '/config/app.php';
echo (file_exists($app) ? 'EXISTE' : 'NAO ENCONTRADO') . PHP_EOL . PHP_EOL;

echo '=== PRODUCAO.PHP ==='.PHP_EOL;
$prod = __DIR__ . '/config/producao.php';
echo (file_exists($prod) ? 'EXISTE' : 'NAO ENCONTRADO') . PHP_EOL . PHP_EOL;

echo '=== CARREGANDO APP.PHP ==='.PHP_EOL;
require_once $app;
echo 'APP_ENV: ' . APP_ENV . PHP_EOL;
echo 'APP_BASE: "' . APP_BASE . '"' . PHP_EOL . PHP_EOL;

echo '=== TESTANDO CONEXAO BD ==='.PHP_EOL;
echo 'DB_HOST: ' . DB_HOST . PHP_EOL;
echo 'DB_NAME: ' . DB_NAME . PHP_EOL;
echo 'DB_USER: ' . DB_USER . PHP_EOL;

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo_test = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo 'CONEXAO: OK' . PHP_EOL;
    $v = $pdo_test->query("SELECT VERSION()")->fetchColumn();
    echo 'MYSQL VERSION: ' . $v . PHP_EOL;
} catch (Exception $e) {
    echo 'ERRO: ' . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . '=== SESSION ==='.PHP_EOL;
session_start();
echo 'Session start: OK' . PHP_EOL;

echo PHP_EOL . '=== DONE ==='.PHP_EOL;
echo '</pre>';
