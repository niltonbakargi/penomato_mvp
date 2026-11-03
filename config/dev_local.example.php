<?php
// ============================================================
// config/dev_local.example.php — EXEMPLO de configuração local
// Copie este arquivo para config/dev_local.php e preencha.
// dev_local.php está no .gitignore — nunca será commitado.
// ============================================================

// ── IA ───────────────────────────────────────────────────────
// Providers disponíveis: 'deepseek', 'claude', 'openai', 'gemini'
define('AI_PROVIDER', 'deepseek');
define('AI_API_KEY',  'sk-COLE_SUA_CHAVE_AQUI');
define('AI_MODEL',    '');   // '' usa o padrão de cada provider

// ── Backup API ───────────────────────────────────────────────
// Token de autenticação para api_sync.php. Gere um valor forte
// (ex: php -r "echo bin2hex(random_bytes(24));") e use o mesmo
// no script Python de sincronização local.
define('BACKUP_SECRET', 'COLE_UM_TOKEN_FORTE_AQUI');

// ── SMTP (dev — use Mailtrap: mailtrap.io) ────────────────────
define('SMTP_HOST',       'sandbox.smtp.mailtrap.io');
define('SMTP_PORT',       587);
define('SMTP_USER',       'COLE_SEU_USER_MAILTRAP');
define('SMTP_PASS',       'COLE_SUA_SENHA_MAILTRAP');
define('SMTP_ENCRYPTION', 'tls');
