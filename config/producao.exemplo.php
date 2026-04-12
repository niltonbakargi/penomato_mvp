<?php
// ============================================================
// config/producao.exemplo.php — MODELO para produção
// ============================================================
// Copie este arquivo para config/producao.php e preencha
// com as credenciais reais da Hostinger.
// NUNCA commite o producao.php — ele está no .gitignore.
// ============================================================

define('APP_ENV',  'prod');
define('APP_URL',  'https://penomato.app.br');
define('APP_BASE', '');                           // raiz do domínio, sem subfolder

// Credenciais MySQL da Hostinger
// (painel Hostinger → Banco de Dados → Detalhes)
define('DB_HOST',    'SEU_HOST_AQUI');            // ex: mysql.hostinger.com
define('DB_NAME',    'SEU_BANCO_AQUI');           // ex: u123456789_penomato
define('DB_USER',    'SEU_USUARIO_AQUI');         // ex: u123456789_admin
define('DB_PASS',    'SUA_SENHA_AQUI');
define('DB_CHARSET', 'utf8mb4');

// ── IA ───────────────────────────────────────────────────────
// Escolha o provedor: 'claude', 'openai', 'gemini' ou 'deepseek'
// Deixe AI_PROVIDER vazio ('') para desativar o botão de IA.
define('AI_PROVIDER', 'claude');          // 'claude' | 'openai' | 'gemini' | 'deepseek' | ''
define('AI_API_KEY',  'sk-ant-api03-SUACHAVEAQUI');
define('AI_MODEL',    '');                // deixe '' para usar o padrão de cada provider
                                          // deepseek: 'deepseek-chat' | 'deepseek-reasoner'
