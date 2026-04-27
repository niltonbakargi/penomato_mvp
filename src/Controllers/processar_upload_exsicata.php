<?php
// ============================================================
// PROCESSAR UPLOAD DE EXSICATA (FOTO DE CAMPO)
// ============================================================
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';
require_once __DIR__ . '/../helpers/gerador_artigo.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE . '/src/Views/enviar_imagem.php');
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

$redirect = APP_BASE . "/src/Views/enviar_imagem.php?especie_id={$especie_id}&exemplar_id={$exemplar_id}";

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

// Formato e validade da data (YYYY-MM-DD, não pode ser futura)
$data_obj = DateTime::createFromFormat('Y-m-d', $data_coleta);
if (!$data_obj || $data_obj->format('Y-m-d') !== $data_coleta) {
    header("Location: {$redirect}&erro=" . urlencode('Data de coleta inválida. Use o formato AAAA-MM-DD.'));
    exit;
}
if ($data_obj > new DateTime('today')) {
    header("Location: {$redirect}&erro=" . urlencode('Data de coleta não pode ser no futuro.'));
    exit;
}

// Comprimentos máximos
if (mb_strlen($observacoes) > 1000) {
    header("Location: {$redirect}&erro=" . urlencode('Observações muito longas. Máximo: 1000 caracteres.'));
    exit;
}
if (mb_strlen($licenca) > 100) {
    $licenca = 'Privado';
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
    SELECT id, codigo, numero_etiqueta, especialista_id
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
    mkdir($pasta, 0755, true);
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

    // Registrar no histórico (com dados_extras para permitir desfazer)
    $imagem_id = $pdo->lastInsertId();
    $pdo->prepare("
        INSERT INTO historico_alteracoes
            (especie_id, id_usuario, tabela_afetada, campo_alterado, valor_novo, tipo_acao, dados_extras)
        VALUES (?, ?, 'especies_imagens', 'parte_planta', ?, 'insercao', ?)
    ")->execute([$especie_id, $usuario_id, $parte_planta,
        json_encode(['imagem_id' => (int)$imagem_id, 'caminho' => $caminho_rel])
    ]);

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

    // ── Se todas as partes prontas E artigo já confirmado → registrado ───────
    $artigo_registrado = false;
    if ($todas_prontas) {
        $revisor_id = ($exemplar['especialista_id'] ?? 0) ?: null;
        $rows = $pdo->prepare("
            UPDATE artigos
            SET status = 'registrado',
                data_registrado = NOW(),
                revisor_id = ?,
                atualizado_em = NOW()
            WHERE especie_id = ? AND status = 'confirmado'
        ");
        $rows->execute([$revisor_id, $especie_id]);
        $artigo_registrado = $rows->rowCount() > 0;
    }

    $pdo->commit();

    regenerarArtigoEspecie($pdo, $especie_id);

    // ── Notificar especialista quando artigo avança para registrado ───────────
    if ($artigo_registrado) {
        try {
            require_once __DIR__ . '/../../config/email.php';
            $stmt_esp = $pdo->prepare("
                SELECT nome_cientifico FROM especies_administrativo WHERE id = ? LIMIT 1
            ");
            $stmt_esp->execute([$especie_id]);
            $nome_especie = $stmt_esp->fetchColumn() ?: "ID $especie_id";

            $link = APP_BASE . '/src/Controllers/artigos_fila.php';

            $revisor_id_notif = ($exemplar['especialista_id'] ?? 0) ?: null;

            if ($revisor_id_notif) {
                $stmt_rev = $pdo->prepare("SELECT nome, email FROM usuarios WHERE id = ? AND ativo = 1 LIMIT 1");
                $stmt_rev->execute([$revisor_id_notif]);
                $rev = $stmt_rev->fetch();
                if ($rev) {
                    $corpo = templateEmail('Artigo pronto para revisão', "
                        <p>Olá, <strong>" . htmlspecialchars($rev['nome']) . "</strong>!</p>
                        <p>O artigo da espécie <em>" . htmlspecialchars($nome_especie) . "</em>
                        está com dados confirmados e imagens registradas.</p>
                        <p>Ele está aguardando sua revisão.</p>
                        <p style='text-align:center;margin:24px 0;'>
                            <a href='{$link}'
                               style='background:#0b5e42;color:#fff;padding:12px 28px;border-radius:8px;
                                      text-decoration:none;font-weight:bold;display:inline-block;'>
                                Acessar fila de revisão
                            </a>
                        </p>
                    ");
                    enviarEmail($rev['email'], "Penomato — {$nome_especie} pronto para revisão", $corpo);
                }
            } else {
                // Sem especialista: notificar todos
                $todos = $pdo->query(
                    "SELECT nome, email FROM usuarios
                     WHERE categoria = 'revisor' AND ativo = 1 AND status_verificacao = 'verificado'"
                )->fetchAll();
                foreach ($todos as $rev) {
                    $corpo = templateEmail('Artigo disponível para revisão', "
                        <p>Olá, <strong>" . htmlspecialchars($rev['nome']) . "</strong>!</p>
                        <p>O artigo da espécie <em>" . htmlspecialchars($nome_especie) . "</em>
                        está pronto e <strong>sem orientador definido</strong>.</p>
                        <p>Qualquer especialista pode assumir a revisão.</p>
                        <p style='text-align:center;margin:24px 0;'>
                            <a href='{$link}'
                               style='background:#0b5e42;color:#fff;padding:12px 28px;border-radius:8px;
                                      text-decoration:none;font-weight:bold;display:inline-block;'>
                                Acessar fila de revisão
                            </a>
                        </p>
                    ");
                    enviarEmail($rev['email'], "Penomato — {$nome_especie} disponível para revisão", $corpo);
                }
            }
        } catch (Exception $e) {
            error_log('Aviso: falha ao notificar especialista: ' . $e->getMessage());
        }
    }

    // ── Montar mensagem de retorno ────────────────────────────────────────────
    $msg = ucfirst($parte_planta) . ' enviada com sucesso!';

    if ($artigo_registrado) {
        $msg .= ' Todas as partes completas — artigo enviado para revisão do especialista!';
    } elseif ($todas_prontas) {
        $msg .= ' Todas as partes foram fotografadas. Para enviar ao especialista, confirme os dados da internet primeiro.';
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
