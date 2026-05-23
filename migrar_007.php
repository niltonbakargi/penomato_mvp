<?php
// Script de migração temporário — DELETAR após rodar
require_once __DIR__ . '/config/banco_de_dados.php';

$sqls = [
    // cor_flores: plural → singular
    "UPDATE `especies_caracteristicas` SET `cor_flores` = 'Branca'     WHERE `cor_flores` = 'Brancas'",
    "UPDATE `especies_caracteristicas` SET `cor_flores` = 'Amarela'    WHERE `cor_flores` = 'Amarelas'",
    "UPDATE `especies_caracteristicas` SET `cor_flores` = 'Vermelha'   WHERE `cor_flores` = 'Vermelhas'",
    "UPDATE `especies_caracteristicas` SET `cor_flores` = 'Rósea'      WHERE `cor_flores` = 'Rosadas'",
    "UPDATE `especies_caracteristicas` SET `cor_flores` = 'Roxa'       WHERE `cor_flores` = 'Roxas'",
    "UPDATE `especies_caracteristicas` SET `cor_flores` = 'Azul'       WHERE `cor_flores` = 'Azuis'",
    "UPDATE `especies_caracteristicas` SET `cor_flores` = 'Alaranjada' WHERE `cor_flores` = 'Laranjas'",
    "UPDATE `especies_caracteristicas` SET `cor_flores` = 'Esverdeada' WHERE `cor_flores` = 'Verdes'",
    "UPDATE `especies_caracteristicas` SET `cor_flores` = NULL WHERE `cor_flores` NOT IN ('Alaranjada','Amarela','Avermelhada','Azul','Branca','Esverdeada','Lilás','Púrpura','Rósea','Roxa','Vermelha','Vinácea') AND `cor_flores` IS NOT NULL",
    // disposicao_flores
    "UPDATE `especies_caracteristicas` SET `disposicao_flores` = 'Solitária' WHERE `disposicao_flores` = 'Isoladas'",
    "UPDATE `especies_caracteristicas` SET `disposicao_flores` = NULL WHERE `disposicao_flores` NOT IN ('Solitária','Capítulo','Cacho','Corimbo','Espádice','Espiga','Panícula','Umbela') AND `disposicao_flores` IS NOT NULL",
    // aroma
    "UPDATE `especies_caracteristicas` SET `aroma` = 'Ausente'      WHERE `aroma` = 'Sem cheiro'",
    "UPDATE `especies_caracteristicas` SET `aroma` = 'Suave'        WHERE `aroma` = 'Aroma suave'",
    "UPDATE `especies_caracteristicas` SET `aroma` = 'Forte'        WHERE `aroma` = 'Aroma forte'",
    "UPDATE `especies_caracteristicas` SET `aroma` = 'Desagradável' WHERE `aroma` = 'Aroma desagradável'",
    "UPDATE `especies_caracteristicas` SET `aroma` = NULL WHERE `aroma` NOT IN ('Ausente','Suave','Forte','Desagradável','Adocicada','Cítrica') AND `aroma` IS NOT NULL",
    // aroma_fruto
    "UPDATE `especies_caracteristicas` SET `aroma_fruto` = 'Ausente'      WHERE `aroma_fruto` = 'Sem cheiro'",
    "UPDATE `especies_caracteristicas` SET `aroma_fruto` = 'Suave'        WHERE `aroma_fruto` = 'Aroma suave'",
    "UPDATE `especies_caracteristicas` SET `aroma_fruto` = 'Forte'        WHERE `aroma_fruto` = 'Aroma forte'",
    "UPDATE `especies_caracteristicas` SET `aroma_fruto` = 'Desagradável' WHERE `aroma_fruto` = 'Aroma desagradável'",
    "UPDATE `especies_caracteristicas` SET `aroma_fruto` = NULL WHERE `aroma_fruto` NOT IN ('Ausente','Suave','Forte','Adocicado','Cítrico','Desagradável') AND `aroma_fruto` IS NOT NULL",
    // numero_petalas
    "UPDATE `especies_caracteristicas` SET `numero_petalas` = NULL WHERE `numero_petalas` NOT IN ('3 pétalas','4 pétalas','5 pétalas','6 pétalas','Muitas pétalas','Ausentes') AND `numero_petalas` IS NOT NULL",
    // ALTER ENUMs
    "ALTER TABLE `especies_caracteristicas` MODIFY COLUMN `cor_flores` ENUM('Alaranjada','Amarela','Avermelhada','Azul','Branca','Esverdeada','Lilás','Púrpura','Rósea','Roxa','Vermelha','Vinácea') DEFAULT NULL",
    "ALTER TABLE `especies_caracteristicas` MODIFY COLUMN `disposicao_flores` ENUM('Solitária','Capítulo','Cacho','Corimbo','Espádice','Espiga','Panícula','Umbela') DEFAULT NULL",
    "ALTER TABLE `especies_caracteristicas` MODIFY COLUMN `aroma` ENUM('Ausente','Suave','Forte','Desagradável','Adocicada','Cítrica') DEFAULT NULL",
    "ALTER TABLE `especies_caracteristicas` MODIFY COLUMN `aroma_fruto` ENUM('Ausente','Suave','Forte','Adocicado','Cítrico','Desagradável') DEFAULT NULL",
    "ALTER TABLE `especies_caracteristicas` MODIFY COLUMN `numero_petalas` ENUM('3 pétalas','4 pétalas','5 pétalas','6 pétalas','Muitas pétalas','Ausentes') DEFAULT NULL",
];

echo "<pre>";
foreach ($sqls as $sql) {
    try {
        $pdo->exec($sql);
        echo "✅ OK: " . htmlspecialchars(substr($sql, 0, 90)) . "...\n";
    } catch (PDOException $e) {
        echo "❌ ERRO: " . htmlspecialchars($e->getMessage()) . "\n    SQL: " . htmlspecialchars(substr($sql, 0, 90)) . "...\n";
    }
}
echo "\n✅ Migração concluída. DELETE este arquivo agora!\n";
echo "</pre>";
