<?php
// Script de migração temporário — DELETAR após rodar
require_once __DIR__ . '/config/banco_de_dados.php';

$sqls = [
    "UPDATE `especies_caracteristicas` SET `tipo_caule` = NULL WHERE `tipo_caule` NOT IN ('Tronco','Estipe','Colmo','Liana','Haste','Escapo') AND `tipo_caule` IS NOT NULL",
    "UPDATE `especies_caracteristicas` SET `textura_caule` = 'Aculeada' WHERE `textura_caule` = 'Espinhosa'",
    "UPDATE `especies_caracteristicas` SET `cor_caule` = 'Acinzentado' WHERE `cor_caule` = 'Cinza'",
    "UPDATE `especies_caracteristicas` SET `cor_caule` = 'Esverdeado' WHERE `cor_caule` = 'Verde'",
    "UPDATE `especies_caracteristicas` SET `cor_caule` = NULL WHERE `cor_caule` NOT IN ('Marrom','Acinzentado','Avermelhado','Alaranjado','Esbranquiçado','Esverdeado','Pardacento') AND `cor_caule` IS NOT NULL",
    "UPDATE `especies_caracteristicas` SET `forma_caule` = NULL WHERE `forma_caule` NOT IN ('Cilíndrico','Quadrangular','Triangular','Achatado','Alado') AND `forma_caule` IS NOT NULL",
    "ALTER TABLE `especies_caracteristicas` MODIFY COLUMN `tipo_caule` ENUM('Tronco','Estipe','Colmo','Liana','Haste','Escapo') DEFAULT NULL",
    "ALTER TABLE `especies_caracteristicas` MODIFY COLUMN `textura_caule` ENUM('Lisa','Rugosa','Sulcada','Fissurada','Estriada','Escamosa','Suberosa','Aculeada','Cerosa') DEFAULT NULL",
    "ALTER TABLE `especies_caracteristicas` MODIFY COLUMN `cor_caule` ENUM('Marrom','Acinzentado','Avermelhado','Alaranjado','Esbranquiçado','Esverdeado','Pardacento') DEFAULT NULL",
    "ALTER TABLE `especies_caracteristicas` MODIFY COLUMN `forma_caule` ENUM('Cilíndrico','Quadrangular','Triangular','Achatado','Alado') DEFAULT NULL",
];

echo "<pre>";
foreach ($sqls as $sql) {
    try {
        $pdo->exec($sql);
        echo "✅ OK: " . htmlspecialchars(substr($sql, 0, 80)) . "...\n";
    } catch (PDOException $e) {
        echo "❌ ERRO: " . htmlspecialchars($e->getMessage()) . "\n    SQL: " . htmlspecialchars(substr($sql, 0, 80)) . "...\n";
    }
}
echo "\n✅ Migração concluída. DELETE este arquivo agora!\n";
echo "</pre>";
