<?php
// ============================================================
// config/app.php — Configuração central por ambiente
// ============================================================
// Detecta automaticamente dev (XAMPP) vs produção (Hostinger).
// Em produção, carrega config/producao.php (gitignored).
// ============================================================

$host_atual = $_SERVER['HTTP_HOST'] ?? 'localhost';
$is_prod    = ($host_atual === 'penomato.app.br' || $host_atual === 'www.penomato.app.br');

if ($is_prod) {

    // ── PRODUÇÃO ─────────────────────────────────────────────
    $config_prod = __DIR__ . '/producao.php';

    if (!file_exists($config_prod)) {
        error_log('[Penomato] ERRO CRÍTICO: config/producao.php não encontrado.');
        http_response_code(500);
        die('<h2>Erro de configuração do servidor.</h2>');
    }

    require_once $config_prod;

    // Corrige links hardcoded /penomato_mvp/ → / em toda a saída HTML
    ob_start(function ($buffer) {
        return str_replace('/penomato_mvp/', '/', $buffer);
    });

    // Erros: apenas log, nunca exibir ao usuário
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);

} else {

    // ── DESENVOLVIMENTO (XAMPP) ───────────────────────────────
    define('APP_ENV',  'dev');
    define('APP_URL',  'http://localhost/penomato_mvp');
    define('APP_BASE', '/penomato_mvp');          // prefixo de todas as URLs

    define('DB_HOST',    'localhost');
    define('DB_NAME',    'penomato');
    define('DB_USER',    'root');
    define('DB_PASS',    '');
    define('DB_CHARSET', 'utf8mb4');

    // Erros visíveis em dev
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    // ── IA (dev) ─────────────────────────────────────────────
    // Chaves ficam em config/dev_local.php (gitignored).
    // Copie config/dev_local.example.php para começar.
    $dev_local = __DIR__ . '/dev_local.php';
    if (file_exists($dev_local)) {
        require_once $dev_local;
    } else {
        define('AI_PROVIDER', '');
        define('AI_API_KEY',  '');
        define('AI_MODEL',    '');
    }
}
