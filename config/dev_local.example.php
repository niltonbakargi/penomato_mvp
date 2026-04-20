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
