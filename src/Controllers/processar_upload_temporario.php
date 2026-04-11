<?php
// ================================================
// PROCESSAR UPLOAD DE IMAGEM DA INTERNET
// Aceita arquivo físico OU imagem colada (base64).
// Salva diretamente em especies_imagens — sem pasta temporária.
// ================================================

session_start();
ob_start();

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/banco_de_dados.php';

$tamanho_maximo       = 10 * 1024 * 1024; // 10 MB
$formatos_permitidos  = ['image/jpeg', 'image/png', 'image/jpg'];
$extensoes_permitidas = ['jpg', 'jpeg', 'png'];

// ================================================
// AUTENTICAÇÃO
// ================================================
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../Views/auth/login.php?erro=" . urlencode("Faça login para continuar."));
    exit;
}
$id_usuario = (int)$_SESSION['usuario_id'];

// ================================================
// VALIDAR SESSÃO DE IMPORTAÇÃO
// ================================================
$temp_id = $_POST['temp_id'] ?? '';

if (
    empty($temp_id) ||
    !isset($_SESSION['importacao_temporaria']) ||
    $_SESSION['importacao_temporaria']['temp_id']         !== $temp_id ||
    (int)$_SESSION['importacao_temporaria']['usuario_id'] !== $id_usuario
) {
    header("Location: ../Views/upload_imagens_internet.php?erro=" . urlencode("Sessão inválida ou expirada."));
    exit;
}

$especie_id = (int)$_SESSION['importacao_temporaria']['especie_id'];

// ================================================
// VALIDAR PARTE DA PLANTA
// ================================================
$parte_planta   = $_POST['parte_planta'] ?? '';
$partes_validas = ['folha','flor','fruto','caule','semente','habito','exsicata_completa','detalhe'];

if (!in_array($parte_planta, $partes_validas, true)) {
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode("Parte da planta inválida."));
    exit;
}

// ================================================
// METADADOS
// ================================================
$fonte_nome   = $_POST['fonte_nome']   ?? null;
$fonte_url    = $_POST['fonte_url']    ?? null;
$autor_imagem = $_POST['autor_imagem'] ?? null;
$licenca      = $_POST['licenca']      ?? null;
$descricao    = $_POST['descricao']    ?? null;

// ================================================
// PREPARAR DIRETÓRIO DE DESTINO
// ================================================
$dir_upload   = dirname(dirname(__DIR__)) . '/uploads/exsicatas/' . $especie_id . '/';
$dir_relativo = 'uploads/exsicatas/' . $especie_id . '/';

if (!is_dir($dir_upload)) {
    mkdir($dir_upload, 0755, true);
}

// ================================================
// FUNÇÕES AUXILIARES
// ================================================
function validarImagem($arquivo, &$erro) {
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $erro = "Erro no upload (código " . $arquivo['error'] . ").";
        return false;
    }
    if ($arquivo['size'] > $GLOBALS['tamanho_maximo']) {
        $erro = "Arquivo muito grande. Máximo: 10 MB.";
        return false;
    }
    $finfo     = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $arquivo['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime_type, $GLOBALS['formatos_permitidos'])) {
        $erro = "Formato não permitido. Use JPG ou PNG.";
        return false;
    }
    $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $GLOBALS['extensoes_permitidas'])) {
        $erro = "Extensão não permitida. Use .jpg, .jpeg ou .png.";
        return false;
    }
    return true;
}

function inserirImagem(
    $pdo, $especie_id, $parte_planta, $dir_upload, $dir_relativo,
    $nome_arquivo, $conteudo_ou_tmp, $tamanho, $mime_type,
    $fonte_nome, $fonte_url, $autor_imagem, $licenca, $descricao, $id_usuario,
    $eh_arquivo_tmp = false
) {
    $caminho_completo = $dir_upload . $nome_arquivo;
    $caminho_relativo = $dir_relativo . $nome_arquivo;

    if ($eh_arquivo_tmp) {
        $ok = move_uploaded_file($conteudo_ou_tmp, $caminho_completo);
    } else {
        $ok = (file_put_contents($caminho_completo, $conteudo_ou_tmp) !== false);
    }

    if (!$ok) return false;

    $stmt = $pdo->prepare("
        INSERT INTO especies_imagens
            (especie_id, tipo_imagem, origem, parte_planta,
             caminho_imagem, nome_original, tamanho_bytes, mime_type,
             descricao, fonte_nome, fonte_url, autor_imagem, licenca,
             id_usuario_identificador, status_validacao)
        VALUES
            (?, 'provisoria', 'upload', ?,
             ?, ?, ?, ?,
             ?, ?, ?, ?, ?,
             ?, 'aprovado')
    ");

    $stmt->execute([
        $especie_id, $parte_planta,
        $caminho_relativo, $nome_arquivo, $tamanho, $mime_type,
        $descricao, $fonte_nome, $fonte_url, $autor_imagem, $licenca,
        $id_usuario,
    ]);

    return true;
}

// ================================================
// PROCESSAR UPLOAD
// ================================================
$erros    = [];
$sucessos = 0;

if (isset($_FILES['imagens']) && !empty($_FILES['imagens']['name'][0])) {

    // --- CASO 1: arquivo(s) físico(s) ---
    $total = count($_FILES['imagens']['name']);

    for ($i = 0; $i < $total; $i++) {
        $arquivo = [
            'name'     => $_FILES['imagens']['name'][$i],
            'type'     => $_FILES['imagens']['type'][$i],
            'tmp_name' => $_FILES['imagens']['tmp_name'][$i],
            'error'    => $_FILES['imagens']['error'][$i],
            'size'     => $_FILES['imagens']['size'][$i],
        ];

        $erro_val = '';
        if (!validarImagem($arquivo, $erro_val)) {
            $erros[] = $arquivo['name'] . ': ' . $erro_val;
            continue;
        }

        $ext          = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        $nome_arquivo = $parte_planta . '_' . date('Ymd_His') . '_' . rand(100, 999) . '.' . $ext;

        $ok = inserirImagem(
            $pdo, $especie_id, $parte_planta, $dir_upload, $dir_relativo,
            $nome_arquivo, $arquivo['tmp_name'], $arquivo['size'], $arquivo['type'],
            $fonte_nome, $fonte_url, $autor_imagem, $licenca, $descricao, $id_usuario,
            true
        );

        $ok ? $sucessos++ : $erros[] = $arquivo['name'] . ': falha ao salvar.';
    }

} elseif (!empty($_POST['imagem_base64'])) {

    // --- CASO 2: imagem colada (base64) ---
    $b64 = $_POST['imagem_base64'];

    if (!preg_match('/^data:image\/(\w+);base64,/', $b64, $m)) {
        $erros[] = "Formato base64 inválido.";
    } else {
        $tipo_img = $m[1];
        $dados    = base64_decode(substr($b64, strpos($b64, ',') + 1));

        if ($dados === false || !in_array('image/' . $tipo_img, $formatos_permitidos)) {
            $erros[] = "Imagem inválida ou formato não suportado.";
        } elseif (strlen($dados) > $tamanho_maximo) {
            $erros[] = "Imagem muito grande (máximo 10 MB).";
        } else {
            $ext          = ($tipo_img === 'jpeg') ? 'jpg' : $tipo_img;
            $nome_arquivo = $parte_planta . '_' . date('Ymd_His') . '_' . rand(100, 999) . '.' . $ext;

            $ok = inserirImagem(
                $pdo, $especie_id, $parte_planta, $dir_upload, $dir_relativo,
                $nome_arquivo, $dados, strlen($dados), 'image/' . $tipo_img,
                $fonte_nome, $fonte_url, $autor_imagem, $licenca, $descricao, $id_usuario,
                false
            );

            $ok ? $sucessos++ : $erros[] = "Falha ao salvar imagem colada.";
        }
    }

} else {
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&parte=" . urlencode($parte_planta) . "&erro=" . urlencode("Nenhuma imagem selecionada."));
    exit;
}

// ================================================
// REDIRECIONAMENTO
// ================================================
if ($sucessos > 0) {
    $msg   = "$sucessos imagem(ns) salva(s).";
    if ($erros) $msg .= ' Erros: ' . implode(' | ', $erros);
    $param = $erros ? 'erro' : 'sucesso';
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&$param=" . urlencode($msg));
} else {
    $msg = $erros ? implode(' | ', $erros) : 'Nenhuma imagem salva.';
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&parte=" . urlencode($parte_planta) . "&erro=" . urlencode($msg));
}

ob_end_flush();
exit;
