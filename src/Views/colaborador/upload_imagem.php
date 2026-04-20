<?php
// Redireciona para o fluxo atual de envio de fotos de campo
require_once __DIR__ . '/../../../config/app.php';
header('Location: ' . APP_BASE . '/src/Views/enviar_imagem.php');
exit;
