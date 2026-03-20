<?php
// ============================================================
// PROCESSAR UPLOAD DE EXSICATA (FOTO DE CAMPO)
// ============================================================
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /penomato_mvp/src/Views/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /penomato_mvp/src/Views/enviar_imagem.php');
    exit;
}

$usuario_id  = (int)$_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'] ?? '';

$especie_id   = (int)($_POST['especie_id']   ?? 0);
$exemplar_id  = (int)($_POST['exemplar_id']  ?? 0);
$parte_planta = trim($_POST['parte_planta'] ?? '');
$data_coleta  = trim($_POST['data_coleta']  ?? '');
$observacoes  = trim($_POST['observacoes']  ?? '');
$licenca      = trim($_POST['licenca']      ?? 'Privado');

$redirect = "/penomato_mvp/src/Views/enviar_imagem.php?especie_id={$especie_id}&exemplar_id={$exemplar_id}";

// ── Validações básicas ────────────────────────────────────────────────────────
$partes_validas = ['folha', 'flor', 'fruto', 'caule', 'semente', 'habito'];

if (!$especie_id) {
    header("Location: {$redirect}&erro=" . urlencode('Espécie não informada.'));
    exit;
}
if (!in_array($parte_planta, $partes_validas)) {
    header("Location: {$redirect}&erro=" . urlencode('Parte da planta inválida.'));
    exit;
}
if (!$exemplar_id) {
    header("Location: {$redirect}&erro=" . urlencode('Exemplar não informado.'));
    exit;
}
if (!$data_coleta) {
    header("Location: {$redirect}&erro=" . urlencode('Data da coleta é obrigatória.'));
    exit;
}

// ── Validação do arquivo ──────────────────────────────────────────────────────
if (empty($_FILES['imagem']) || $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
    header("Location: {$redirect}&erro=" . urlencode('Nenhum arquivo enviado ou erro no upload.'));
    exit;
}

$arquivo = $_FILES['imagem'];

if ($arquivo['size'] > 15 * 1024 * 1024) {
    header("Location: {$redirect}&erro=" . urlencode('Arquivo muito grande. Máximo: 15MB.'));
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $arquivo['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, ['image/jpeg', 'image/jpg', 'image/png'])) {
    header("Location: {$redirect}&erro=" . urlencode('Formato inválido. Use JPG ou PNG.'));
    exit;
}

// ── Verificar espécie existe ──────────────────────────────────────────────────
$stmt = $pdo->prepare("SELECT id, nome_cientifico FROM especies_administrativo WHERE id = ?");
$stmt->execute([$especie_id]);
$especie = $stmt->fetch();

if (!$especie) {
    header("Location: {$redirect}&erro=" . urlencode('Espécie não encontrada.'));
    exit;
}

// ── Verificar exemplar aprovado ───────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT id, codigo, numero_etiqueta
    FROM exemplares
    WHERE id = ? AND especie_id = ? AND status = 'aprovado'
");
$stmt->execute([$exemplar_id, $especie_id]);
$exemplar = $stmt->fetch();

if (!$exemplar) {
    header("Location: {$redirect}&erro=" . urlencode('Exemplar não encontrado ou não aprovado.'));
    exit;
}

// ── Salvar arquivo em disco ───────────────────────────────────────────────────
$pasta = dirname(dirname(__DIR__)) . '/uploads/exsicatas/' . $especie_id . '/';
if (!file_exists($pasta)) {
    mkdir($pasta, 0777, true);
}

$ext           = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
$nome_arquivo  = $parte_planta . '_' . date('Ymd_His') . '_' . rand(100, 999) . '.' . $ext;
$caminho_disco = $pasta . $nome_arquivo;
$caminho_rel   = 'uploads/exsicatas/' . $especie_id . '/' . $nome_arquivo;

if (!move_uploaded_file($arquivo['tmp_name'], $caminho_disco)) {
    header("Location: {$redirect}&erro=" . urlencode('Erro ao salvar o arquivo no servidor.'));
    exit;
}

// ── Inserir no banco ──────────────────────────────────────────────────────────
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO especies_imagens (
            especie_id, exemplar_id, tipo_imagem, origem, parte_planta,
            caminho_imagem, nome_original, tamanho_bytes, mime_type,
            licenca, data_coleta, coletor_nome, coletor_id,
            id_usuario_identificador, numero_etiqueta,
            observacoes_internas, status_validacao, data_upload
        ) VALUES (
            ?, ?, 'provisoria', 'campo', ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?,
            ?, 'aprovado', NOW()
        )
    ");

    $stmt->execute([
        $especie_id, $exemplar_id, $parte_planta,
        $caminho_rel, $arquivo['name'], $arquivo['size'], $mime,
        $licenca, $data_coleta, $usuario_nome, $usuario_id,
        $usuario_id, $exemplar['numero_etiqueta'],
        $observacoes ?: null
    ]);

    // Registrar no histórico
    $stmt_hist = $pdo->prepare("
        INSERT INTO historico_alteracoes
            (especie_id, id_usuario, tabela_afetada, campo_alterado, valor_novo, tipo_acao)
        VALUES (?, ?, 'especies_imagens', 'parte_planta', ?, 'insercao')
    ");
    $stmt_hist->execute([$especie_id, $usuario_id, $parte_planta]);

    // ── Verificar se todas as partes estão completas → REGISTRADA ────────────
    $partes_todas = ['folha', 'flor', 'fruto', 'caule', 'semente', 'habito'];

    $stmt_fotos = $pdo->prepare("
        SELECT DISTINCT parte_planta
        FROM especies_imagens
        WHERE especie_id = ? AND exemplar_id = ? AND origem = 'campo'
    ");
    $stmt_fotos->execute([$especie_id, $exemplar_id]);
    $fotografadas = $stmt_fotos->fetchAll(PDO::FETCH_COLUMN);

    $stmt_disp = $pdo->prepare("
        SELECT parte_planta FROM partes_dispensadas WHERE especie_id = ?
    ");
    $stmt_disp->execute([$especie_id]);
    $dispensadas = $stmt_disp->fetchAll(PDO::FETCH_COLUMN);

    $completas    = array_unique(array_merge($fotografadas, $dispensadas));
    $faltando     = array_diff($partes_todas, $completas);
    $todas_prontas = count($faltando) === 0;

    if ($todas_prontas) {
        $stmt_reg = $pdo->prepare("
            UPDATE especies_administrativo
            SET
                data_registrada     = COALESCE(data_registrada, NOW()),
                autor_registrada_id = COALESCE(autor_registrada_id, ?),
                status = CASE
                    WHEN status IN ('sem_dados','dados_internet','descrita')
                    THEN 'registrada'
                    ELSE status
                END
            WHERE id = ?
        ");
        $stmt_reg->execute([$usuario_id, $especie_id]);
    }

    $pdo->commit();

    // ── Montar mensagem de retorno ────────────────────────────────────────────
    $msg = ucfirst($parte_planta) . ' enviada com sucesso!';

    if ($todas_prontas) {
        // Verificar se também está identificada
        $stmt_chk = $pdo->prepare("SELECT data_descrita FROM especies_administrativo WHERE id = ?");
        $stmt_chk->execute([$especie_id]);
        $chk = $stmt_chk->fetch();

        if ($chk['data_descrita']) {
            $msg .= ' Todas as partes completas e espécie identificada — o artigo pode ser gerado!';
        } else {
            $msg .= ' Todas as partes foram fotografadas. Para gerar o artigo, confirme os atributos da internet.';
        }
    }

    header("Location: {$redirect}&sucesso=" . urlencode($msg));

} catch (Exception $e) {
    $pdo->rollBack();
    if (file_exists($caminho_disco)) {
        unlink($caminho_disco);
    }
    error_log('Erro upload exsicata: ' . $e->getMessage());
    header("Location: {$redirect}&erro=" . urlencode('Erro interno ao salvar. Tente novamente.'));
}

exit;
