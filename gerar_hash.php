<?php
// Crie em: C:\xampp\htdocs\penomato_mvp\gerar_hash.php

echo "<h1>🔐 Gerador de Hash de Senha - Penomato</h1>";

// Defina a senha que você quer para o Nilton
$senha = '123456'; // ALTERE PARA A SENHA DESEJADA

// Gera o hash seguro
$hash = password_hash($senha, PASSWORD_DEFAULT);

echo "<p><strong>Senha escolhida:</strong> " . $senha . "</p>";
echo "<p><strong>Hash gerado:</strong></p>";
echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>" . $hash . "</pre>";

echo "<p><strong>SQL para atualizar:</strong></p>";
echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
echo "UPDATE usuarios \n";
echo "SET senha_hash = '" . $hash . "' \n";
echo "WHERE id = 1;";
echo "</pre>";

echo "<p><strong>🔴 IMPORTANTE:</strong> Apague este arquivo após usar!</p>";
?>