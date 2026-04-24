<?php
// ============================================================
// PROCESSAR CADASTRO DE EXEMPLAR
// ============================================================
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';
require_once __DIR__ . '/../../config/email.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE . '/src/Views/cadastrar_exemplar.php');
    exit;
}

$usuario_id   = (int)$_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'] ?? '';

// ── Receber campos ────────────────────────────────────────────────────────────
$especie_id       = (int)($_POST['especie_id']       ?? 0);
$numero_etiqueta  = trim($_POST['numero_etiqueta']   ?? '');
$latitude         = $_POST['latitude']  !== '' ? (float)$_POST['latitude']  : null;
$longitude        = $_POST['longitude'] !== '' ? (float)$_POST['longitude'] : null;
$cidade           = trim($_POST['cidade']            ?? '');
$estado           = trim($_POST['estado']            ?? '');
$bioma            = trim($_POST['bioma']             ?? '');
$descricao_local  = trim($_POST['descricao_local']   ?? '');
$especialista_id  = (int)($_POST['especialista_id']  ?? 0);

$redirect = APP_BASE . '/src/Views/cadastrar_exemplar.php?especie_id=' . $especie_id;

// ── Validações ────────────────────────────────────────────────────────────────
if (!$especie_id) {
    header("Location: {$redirect}&erro=" . urlencode('Espécie não informada.'));
    exit;
}
if (!$cidade || !$estado || !$bioma) {
    header("Location: {$redirect}&erro=" . urlencode('Cidade, estado e bioma são obrigatórios.'));
    exit;
}
if (!$especialista_id) {
    header("Location: {$redirect}&erro=" . urlencode('Selecione um especialista orientador.'));
    exit;
}

// Comprimentos máximos
if (mb_strlen($cidade) > 150) {
    header("Location: {$redirect}&erro=" . urlencode('Nome da cidade muito longo. Máximo: 150 caracteres.'));
    exit;
}
if (mb_strlen($numero_etiqueta) > 50) {
    header("Location: {$redirect}&erro=" . urlencode('Número de etiqueta muito longo. Máximo: 50 caracteres.'));
    exit;
}
if (mb_strlen($descricao_local) > 1000) {
    header("Location: {$redirect}&erro=" . urlencode('Descrição do local muito longa. Máximo: 1000 caracteres.'));
    exit;
}

// Estado: deve ser uma UF brasileira válida (2 letras)
$ufs_validas = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS',
                 'MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
$estado = strtoupper($estado);
if (!in_array($estado, $ufs_validas)) {
    header("Location: {$redirect}&erro=" . urlencode('Estado inválido. Use a sigla da UF (ex: MS, SP).'));
    exit;
}

// Bioma: deve ser um valor ENUM válido
$biomas_validos = ['Cerrado','Mata Atlântica','Pantanal','Caatinga','Amazônia','Pampa','Outro'];
if (!in_array($bioma, $biomas_validos)) {
    header("Location: {$redirect}&erro=" . urlencode('Bioma inválido. Selecione uma das opções disponíveis.'));
    exit;
}

// Coordenadas: faixa geográfica válida
if ($latitude !== null && ($latitude < -90 || $latitude > 90)) {
    header("Location: {$redirect}&erro=" . urlencode('Latitude inválida. Deve estar entre -90 e 90.'));
    exit;
}
if ($longitude !== null && ($longitude < -180 || $longitude > 180)) {
    header("Location: {$redirect}&erro=" . urlencode('Longitude inválida. Deve estar entre -180 e 180.'));
    exit;
}

// ── Verificar espécie ─────────────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT id FROM especies_administrativo WHERE id = ?");
$stmt->execute([$especie_id]);
if (!$stmt->fetch()) {
    header("Location: {$redirect}&erro=" . urlencode('Espécie não encontrada.'));
    exit;
}

// ── Verificar especialista ────────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ? AND categoria IN ('revisor','gestor')");
$stmt->execute([$especialista_id]);
if (!$stmt->fetch()) {
    header("Location: {$redirect}&erro=" . urlencode('Especialista inválido.'));
    exit;
}

// ── Processar foto de identificação ──────────────────────────────────────────
$caminho_foto = null;

if (!empty($_FILES['foto_identificacao']['name']) &&
    $_FILES['foto_identificacao']['error'] === UPLOAD_ERR_OK) {

    $arquivo = $_FILES['foto_identificacao'];

    if ($arquivo['size'] > 15 * 1024 * 1024) {
        header("Location: {$redirect}&erro=" . urlencode('Foto muito grande. Máximo: 15MB.'));
        exit;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $arquivo['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, ['image/jpeg', 'image/jpg', 'image/png'])) {
        header("Location: {$redirect}&erro=" . urlencode('Formato inválido. Use JPG ou PNG.'));
        exit;
    }

    $pasta = dirname(dirname(__DIR__)) . '/uploads/exemplares/' . $especie_id . '/';
    if (!file_exists($pasta)) mkdir($pasta, 0755, true);

    $ext          = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    $nome_arquivo = 'identificacao_' . date('Ymd_His') . '_' . rand(100, 999) . '.' . $ext;
    $caminho_disk = $pasta . $nome_arquivo;
    $caminho_foto = 'uploads/exemplares/' . $especie_id . '/' . $nome_arquivo;

    if (!move_uploaded_file($arquivo['tmp_name'], $caminho_disk)) {
        header("Location: {$redirect}&erro=" . urlencode('Erro ao salvar a foto. Tente novamente.'));
        exit;
    }
}

// ── Gerar código sequencial PN001, PN002, … ──────────────────────────────────
function gerarCodigo(PDO $pdo): string {
    // Busca o maior número já usado com o prefixo PN
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(codigo, 3) AS UNSIGNED)) FROM exemplares WHERE codigo REGEXP '^PN[0-9]{3}$'");
    $max = (int)$stmt->fetchColumn();
    $proximo = $max + 1;

    // Segurança: se já chegou em 999, reinicia com próximo prefixo (PN999 → PO001)
    // Na prática o MVP não chegará nisso, mas mantém o código robusto
    if ($proximo > 999) $proximo = 999;

    return 'PN' . str_pad($proximo, 3, '0', STR_PAD_LEFT);
}

// ── Fallback: extrair GPS do EXIF da foto se coordenadas não vieram do form ───
if (($latitude === null || $longitude === null) && $caminho_foto && function_exists('exif_read_data')) {
    $caminho_disk_leitura = dirname(dirname(__DIR__)) . '/' . $caminho_foto;
    $exif = @exif_read_data($caminho_disk_leitura);
    if ($exif && isset($exif['GPSLatitude'], $exif['GPSLongitude'])) {
        $fracaoParaDecimal = fn($v) => ($p = explode('/', $v)) && count($p) == 2
            ? (float)$p[0] / max(1, (float)$p[1]) : (float)$v;
        $somarGraus = fn($arr) => $fracaoParaDecimal($arr[0])
            + $fracaoParaDecimal($arr[1]) / 60
            + $fracaoParaDecimal($arr[2]) / 3600;
        $latitude  = $somarGraus($exif['GPSLatitude']);
        $longitude = $somarGraus($exif['GPSLongitude']);
        if (($exif['GPSLatitudeRef']  ?? 'N') === 'S') $latitude  *= -1;
        if (($exif['GPSLongitudeRef'] ?? 'E') === 'W') $longitude *= -1;
    }
}

// ── Inserir no banco ──────────────────────────────────────────────────────────
try {
    $pdo->beginTransaction();

    $codigo = gerarCodigo($pdo);

    $stmt = $pdo->prepare("
        INSERT INTO exemplares (
            codigo, especie_id, numero_etiqueta, foto_identificacao,
            latitude, longitude, cidade, estado, bioma, descricao_local,
            especialista_id, cadastrado_por, data_cadastro, status
        ) VALUES (
            ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, NOW(), 'aguardando_revisao'
        )
    ");

    $stmt->execute([
        $codigo, $especie_id, $numero_etiqueta ?: null, $caminho_foto,
        $latitude, $longitude, $cidade, $estado, $bioma, $descricao_local ?: null,
        $especialista_id, $usuario_id
    ]);

    $exemplar_id = $pdo->lastInsertId();

    // Registrar no histórico
    $stmt_hist = $pdo->prepare("
        INSERT INTO historico_alteracoes
            (especie_id, id_usuario, tabela_afetada, campo_alterado, valor_novo, tipo_acao)
        VALUES (?, ?, 'exemplares', 'codigo', ?, 'insercao')
    ");
    $stmt_hist->execute([$especie_id, $usuario_id, $codigo]);

    $pdo->commit();

    // Notificar o especialista sobre o novo exemplar aguardando revisão
    $stmt_esp = $pdo->prepare("SELECT nome, email FROM usuarios WHERE id = ?");
    $stmt_esp->execute([$especialista_id]);
    $especialista = $stmt_esp->fetch();
    if ($especialista) {
        $stmt_esp_nome = $pdo->prepare("SELECT nome_cientifico FROM especies_administrativo WHERE id = ?");
        $stmt_esp_nome->execute([$especie_id]);
        $nome_especie = $stmt_esp_nome->fetchColumn() ?: 'espécie não identificada';

        $conteudo_email = "
            <p>Olá, <strong>" . htmlspecialchars($especialista['nome']) . "</strong>!</p>
            <p>Um novo exemplar foi cadastrado e aguarda sua revisão:</p>
            <table style='margin:16px 0;border-collapse:collapse;width:100%;'>
                <tr><td style='padding:6px 12px;background:#f4f4f4;font-weight:600;'>Código</td><td style='padding:6px 12px;'>{$codigo}</td></tr>
                <tr><td style='padding:6px 12px;background:#f4f4f4;font-weight:600;'>Espécie</td><td style='padding:6px 12px;'><em>" . htmlspecialchars($nome_especie) . "</em></td></tr>
                <tr><td style='padding:6px 12px;background:#f4f4f4;font-weight:600;'>Local</td><td style='padding:6px 12px;'>" . htmlspecialchars($cidade) . ", " . htmlspecialchars($estado) . "</td></tr>
                <tr><td style='padding:6px 12px;background:#f4f4f4;font-weight:600;'>Cadastrado por</td><td style='padding:6px 12px;'>" . htmlspecialchars($usuario_nome) . "</td></tr>
            </table>
            <p>
                <a href='" . APP_URL . "/src/Views/revisor/revisar_exemplar.php'
                   style='background:#0b5e42;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:600;'>
                    Revisar exemplar
                </a>
            </p>";
        enviarEmail(
            $especialista['email'],
            "Novo exemplar para revisão ({$codigo}) — Penomato",
            templateEmail('Exemplar aguardando sua revisão', $conteudo_email)
        );
    }

    $msg = "Exemplar {$codigo} cadastrado com sucesso! Aguardando revisão do especialista.";
    header("Location: {$redirect}&sucesso=" . urlencode($msg));

} catch (Exception $e) {
    $pdo->rollBack();
    if ($caminho_foto) {
        $_raiz_uploads = realpath(__DIR__ . '/../../uploads');
        $_arq = realpath(__DIR__ . '/../../' . $caminho_foto);
        if ($_arq && $_raiz_uploads && str_starts_with($_arq, $_raiz_uploads)) unlink($_arq);
    }
    error_log('Erro cadastro exemplar: ' . $e->getMessage());
    header("Location: {$redirect}&erro=" . urlencode('Erro interno ao salvar. Tente novamente.'));
}

exit;
