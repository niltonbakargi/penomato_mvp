<?php
// preencher_e_enviar.php
// Envia dados diretamente via POST sem mostrar formulário

// 1. Carregar dados do JSON
$jsonFile = __DIR__ . '/especies json/Acacia Polyfylla.json';
$jsonContent = file_get_contents($jsonFile);
$dados = json_decode($jsonContent, true);

// 2. Buscar ID da espécie no banco
require_once __DIR__ . '/../config/database.php';
$config = require __DIR__ . '/../config/database.php';

$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}",
    $config['username'],
    $config['password']
);

$stmt = $pdo->prepare("SELECT id FROM especies_administrativo WHERE nome_cientifico LIKE ?");
$stmt->execute(['%Acacia%polyphylla%']);
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resultado) {
    die("Espécie não encontrada no banco!");
}

$especie_id = $resultado['id'];

// 3. Preparar dados para envio POST
$postData = [
    'especie_id' => $especie_id,
    'nome_popular' => $dados['especie']['nome_popular'] ?? '',
    'familia' => $dados['especie']['familia'] ?? '',
    'forma_folha' => $dados['folhas']['forma'] ?? '',
    'filotaxia_folha' => $dados['folhas']['filotaxia'] ?? '',
    'tipo_folha' => $dados['folhas']['tipo'] ?? '',
    'tamanho_folha' => $dados['folhas']['tamanho'] ?? '',
    'textura_folha' => $dados['folhas']['textura'] ?? '',
    'margem_folha' => $dados['folhas']['margem'] ?? '',
    'venacao_folha' => $dados['folhas']['venacao'] ?? '',
    'cor_flores' => $dados['flores']['cor'] ?? '',
    'simetria_floral' => $dados['flores']['simetria'] ?? '',
    'numero_petalas' => $dados['flores']['numero_petalas'] ?? '',
    'disposicao_flores' => $dados['flores']['disposicao'] ?? '',
    'aroma' => $dados['flores']['aroma'] ?? '',
    'tamanho_flor' => $dados['flores']['tamanho_flor'] ?? '',
    'tipo_fruto' => $dados['frutos']['tipo'] ?? '',
    'tamanho_fruto' => $dados['frutos']['tamanho'] ?? '',
    'cor_fruto' => $dados['frutos']['cor'] ?? '',
    'textura_fruto' => $dados['frutos']['textura'] ?? '',
    'dispersao_fruto' => $dados['frutos']['dispersao'] ?? '',
    'aroma_fruto' => $dados['frutos']['aroma'] ?? '',
    'tipo_semente' => $dados['sementes']['tipo'] ?? '',
    'tamanho_semente' => $dados['sementes']['tamanho'] ?? '',
    'cor_semente' => $dados['sementes']['cor'] ?? '',
    'textura_semente' => $dados['sementes']['textura'] ?? '',
    'quantidade_sementes' => $dados['sementes']['quantidade'] ?? '',
    'tipo_caule' => $dados['caule']['tipo'] ?? '',
    'estrutura_caule' => $dados['caule']['estrutura'] ?? '',
    'textura_caule' => $dados['caule']['textura'] ?? '',
    'cor_caule' => $dados['caule']['cor'] ?? '',
    'forma_caule' => $dados['caule']['forma'] ?? '',
    'modificacao_caule' => $dados['caule']['modificacao'] ?? '',
    'diametro_caule' => $dados['caule']['diametro'] ?? '',
    'ramificacao_caule' => $dados['caule']['ramificacao'] ?? '',
    'possui_espinhos' => $dados['outras_caracteristicas']['possui_espinhos'] ?? '',
    'possui_latex' => $dados['outras_caracteristicas']['possui_latex'] ?? '',
    'possui_seiva' => $dados['outras_caracteristicas']['possui_seiva'] ?? '',
    'possui_resina' => $dados['outras_caracteristicas']['possui_resina'] ?? '',
    'referencias' => is_array($dados['referencias'] ?? null) ? implode("\n", $dados['referencias']) : ($dados['referencias'] ?? '')
];

// 4. Enviar via cURL
$url = 'http://localhost/penomato_mvp/public/caracteristicas/salvar';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Seguir redirecionamento

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 5. Mostrar resultado
echo "<h1>Resultado do Envio Automático</h1>";
echo "<p><strong>Status HTTP:</strong> $httpCode</p>";
echo "<p><strong>Espécie ID:</strong> {$postData['especie_id']}</p>";

if ($httpCode == 302 || $httpCode == 200) {
    echo "<p style='color: green;'>✅ Dados enviados com sucesso!</p>";
    echo "<p>Você deve ser redirecionado para a página de sucesso.</p>";
    echo "<p><a href='http://localhost/penomato_mvp/public/caracteristicas/sucesso?id={$especie_id}'>Ver página de sucesso</a></p>";
} else {
    echo "<p style='color: red;'>❌ Erro ao enviar dados. Status: $httpCode</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}

// 6. Mostrar dados enviados
echo "<h3>Dados Enviados:</h3>";
echo "<pre>" . print_r($postData, true) . "</pre>";
?>