<?php
/**
 * REGENERAR ARTIGOS SALVOS
 * Acesse via browser (logado como gestor) para regenerar todos os artigos
 * existentes no banco usando o gerador de texto atual.
 * Delete este arquivo após o uso.
 */
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';
require_once __DIR__ . '/../helpers/gerador_artigo.php';

// Apenas gestores podem acessar esta ferramenta
if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? '') !== 'gestor') {
    http_response_code(403);
    die('Acesso negado. Apenas gestores podem usar esta ferramenta.');
}

// ── buscar todos os artigos existentes ──────────────────────────────────
$ids = $pdo->query("
    SELECT a.especie_id, e.nome_cientifico
    FROM artigos a
    INNER JOIN especies_administrativo e ON e.id = a.especie_id
    INNER JOIN especies_caracteristicas ec ON ec.especie_id = a.especie_id
")->fetchAll(PDO::FETCH_ASSOC);

$resultados = [];

foreach ($ids as $row) {
    $especie_id = (int)$row['especie_id'];
    regenerarArtigoEspecie($pdo, $especie_id);
    $resultados[] = [
        'id'   => $especie_id,
        'nome' => $row['nome_cientifico'],
        'ok'   => true,
        'msg'  => 'Atualizado',
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Regenerar Artigos</title>
<style>
body { font-family: sans-serif; padding: 30px; max-width: 700px; margin: auto; }
h1 { color: #0d4f35; margin-bottom: 20px; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 0.9em; }
th { background: #f5f5f5; }
.ok  { color: #155724; }
.err { color: #721c24; }
.aviso { background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 12px 16px; margin-top: 24px; font-size: 0.88em; }
</style>
</head>
<body>
<h1>Regeneração de Artigos</h1>
<table>
<tr><th>Espécie</th><th>Resultado</th></tr>
<?php foreach ($resultados as $r): ?>
<tr>
    <td><em><?php echo htmlspecialchars($r['nome'] ?? 'ID ' . $r['id']); ?></em></td>
    <td class="<?php echo $r['ok'] ? 'ok' : 'err'; ?>"><?php echo htmlspecialchars($r['msg']); ?></td>
</tr>
<?php endforeach; ?>
</table>

<div class="aviso">
    ⚠️ <strong>Delete este arquivo após o uso:</strong>
    <code>src/Controllers/regenerar_artigos.php</code>
</div>
</body>
</html>
