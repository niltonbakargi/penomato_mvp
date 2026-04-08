<?php
// ================================================
// PROCESSAR UPLOAD DE IMAGEM - VERSÃO LEGADO
// ================================================
// ATENÇÃO: Este arquivo é mantido para compatibilidade
// com o sistema antigo de upload. O novo sistema usa
// processar_upload_temporario.php e finalizar_upload_temporario.php
// ================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
ob_start();

require_once __DIR__ . '/../../config/app.php';
$servidor   = DB_HOST;
$usuario_db = DB_USER;
$senha_db   = DB_PASS;
$banco      = DB_NAME;

// ================================================
// CONFIGURAÇÕES DE UPLOAD
// ================================================
$pasta_upload = dirname(dirname(__DIR__)) . '/uploads/especies/';
$tamanho_maximo = 10 * 1024 * 1024; // 10MB
$formatos_permitidos = ['image/jpeg', 'image/png', 'image/jpg'];
$extensoes_permitidas = ['jpg', 'jpeg', 'png'];

// ================================================
// VERIFICAR SE USUÁRIO ESTÁ LOGADO
// ================================================
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../Views/auth/login.php?erro=" . urlencode("Faça login para enviar imagens."));
    exit;
}
$id_usuario = $_SESSION['usuario_id'];

// ================================================
// FUNÇÕES
// ================================================

/**
 * Cria a estrutura de pastas se não existir
 */
function criarPastasUpload($caminho) {
    if (!file_exists($caminho)) {
        mkdir($caminho, 0777, true);
        return true;
    }
    return true;
}

/**
 * Gera nome único para o arquivo
 */
function gerarNomeArquivo($especie_id, $parte, $extensao) {
    $timestamp = date('Ymd_His');
    $random = rand(100, 999);
    return "{$especie_id}_{$parte}_{$timestamp}_{$random}.{$extensao}";
}

/**
 * Valida a imagem enviada
 */
function validarImagem($arquivo, &$erro) {
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $erro = "Erro no upload do arquivo. Código: " . $arquivo['error'];
        return false;
    }
    
    if ($arquivo['size'] > $GLOBALS['tamanho_maximo']) {
        $erro = "Arquivo muito grande. Tamanho máximo: 10MB";
        return false;
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $arquivo['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $GLOBALS['formatos_permitidos'])) {
        $erro = "Formato de arquivo não permitido. Use JPG ou PNG.";
        return false;
    }
    
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    if (!in_array($extensao, $GLOBALS['extensoes_permitidas'])) {
        $erro = "Extensão não permitida. Use .jpg, .jpeg ou .png";
        return false;
    }
    
    return true;
}

/**
 * Redimensiona imagem se necessário (mantém proporção)
 */
function redimensionarImagem($caminho_origem, $caminho_destino, $largura_max = 1920, $altura_max = 1920) {
    $info = getimagesize($caminho_origem);
    
    if (!$info) {
        return false;
    }
    
    if ($info[0] <= $largura_max && $info[1] <= $altura_max) {
        return copy($caminho_origem, $caminho_destino);
    }
    
    switch ($info['mime']) {
        case 'image/jpeg':
            $imagem_origem = imagecreatefromjpeg($caminho_origem);
            break;
        case 'image/png':
            $imagem_origem = imagecreatefrompng($caminho_origem);
            break;
        default:
            return false;
    }
    
    $ratio_origem = $info[0] / $info[1];
    $ratio_destino = $largura_max / $altura_max;
    
    if ($ratio_origem > $ratio_destino) {
        $nova_largura = $largura_max;
        $nova_altura = $largura_max / $ratio_origem;
    } else {
        $nova_altura = $altura_max;
        $nova_largura = $altura_max * $ratio_origem;
    }
    
    $imagem_destino = imagecreatetruecolor($nova_largura, $nova_altura);
    
    if ($info['mime'] == 'image/png') {
        imagealphablending($imagem_destino, false);
        imagesavealpha($imagem_destino, true);
        $transparente = imagecolorallocatealpha($imagem_destino, 255, 255, 255, 127);
        imagefilledrectangle($imagem_destino, 0, 0, $nova_largura, $nova_altura, $transparente);
    }
    
    imagecopyresampled($imagem_destino, $imagem_origem, 0, 0, 0, 0, 
                       $nova_largura, $nova_altura, $info[0], $info[1]);
    
    switch ($info['mime']) {
        case 'image/jpeg':
            $resultado = imagejpeg($imagem_destino, $caminho_destino, 90);
            break;
        case 'image/png':
            $resultado = imagepng($imagem_destino, $caminho_destino, 9);
            break;
        default:
            $resultado = false;
    }
    
    imagedestroy($imagem_origem);
    imagedestroy($imagem_destino);
    
    return $resultado;
}

/**
 * Atualiza o status_imagens da espécie com base nas imagens cadastradas
 */
function atualizarStatusImagens($conexao, $especie_id) {
    $partes_obrigatorias = ['folha', 'flor', 'fruto', 'caule', 'habito'];
    $partes_completas = 0;
    
    foreach ($partes_obrigatorias as $parte) {
        $sql = "SELECT COUNT(*) as total FROM especies_imagens 
                WHERE especie_id = ? AND parte_planta = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("is", $especie_id, $parte);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $row = $resultado->fetch_assoc();
        
        if ($row['total'] > 0) {
            $partes_completas++;
        }
        $stmt->close();
    }
    
    if ($partes_completas == count($partes_obrigatorias)) {
        $novo_status = 'registrada';
    } else if ($partes_completas > 0) {
        $novo_status = 'internet';
    } else {
        $novo_status = 'sem_imagens';
    }
    
    $sql_update = "UPDATE especies_administrativo SET status_imagens = ? WHERE id = ?";
    $stmt_update = $conexao->prepare($sql_update);
    $stmt_update->bind_param("si", $novo_status, $especie_id);
    $stmt_update->execute();
    $stmt_update->close();
    
    return $novo_status;
}

// ================================================
// PROCESSAMENTO DO UPLOAD
// ================================================

$erros = [];
$sucessos = 0;
$especie_id = isset($_POST['especie_id']) ? (int)$_POST['especie_id'] : 0;
$parte_planta = isset($_POST['parte_planta']) ? $_POST['parte_planta'] : '';

// ================================================
// VALIDAÇÕES INICIAIS
// ================================================
if ($especie_id <= 0) {
    header("Location: ../Views/enviar_imagem.php?erro=" . urlencode("ID da espécie inválido."));
    exit;
}

if (empty($parte_planta)) {
    header("Location: ../Views/enviar_imagem.php?especie_id=" . $especie_id . "&erro=" . urlencode("Parte da planta não informada."));
    exit;
}

if (!isset($_FILES['imagens']) || empty($_FILES['imagens']['name'][0])) {
    header("Location: ../Views/enviar_imagem.php?especie_id=" . $especie_id . "&parte=" . urlencode($parte_planta) . "&erro=" . urlencode("Nenhuma imagem selecionada."));
    exit;
}

// ================================================
// DADOS DE METADADOS
// ================================================
$fonte_nome = $_POST['fonte_nome'] ?? null;
$fonte_url = $_POST['fonte_url'] ?? null;
$autor_imagem = $_POST['autor_imagem'] ?? null;
$licenca = $_POST['licenca'] ?? null;
$descricao = $_POST['descricao'] ?? null;

// ================================================
// CONECTAR AO BANCO
// ================================================
$conexao = new mysqli($servidor, $usuario_db, $senha_db, $banco);

if ($conexao->connect_error) {
    header("Location: ../Views/enviar_imagem.php?especie_id=" . $especie_id . "&parte=" . urlencode($parte_planta) . "&erro=" . urlencode("Erro de conexão com banco."));
    exit;
}

$conexao->set_charset("utf8mb4");

// ================================================
// CRIAR PASTA DE UPLOAD
// ================================================
$pasta_raiz = dirname(dirname(__DIR__)) . '/uploads/especies/';
$pasta_especie = $pasta_raiz . $especie_id . '/';
criarPastasUpload($pasta_especie);

// ================================================
// PROCESSAR CADA ARQUIVO
// ================================================
$total_arquivos = count($_FILES['imagens']['name']);

for ($i = 0; $i < $total_arquivos; $i++) {
    
    $arquivo = [
        'name' => $_FILES['imagens']['name'][$i],
        'type' => $_FILES['imagens']['type'][$i],
        'tmp_name' => $_FILES['imagens']['tmp_name'][$i],
        'error' => $_FILES['imagens']['error'][$i],
        'size' => $_FILES['imagens']['size'][$i]
    ];
    
    // Validar imagem
    $erro_validacao = '';
    if (!validarImagem($arquivo, $erro_validacao)) {
        $erros[] = "Arquivo {$arquivo['name']}: $erro_validacao";
        continue;
    }
    
    // Gerar nome do arquivo
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    $nome_arquivo = gerarNomeArquivo($especie_id, $parte_planta, $extensao);
    $caminho_completo = $pasta_especie . $nome_arquivo;
    $caminho_relativo = 'uploads/especies/' . $especie_id . '/' . $nome_arquivo;
    
    // Redimensionar e salvar
    if (redimensionarImagem($arquivo['tmp_name'], $caminho_completo)) {
        
        // Inserir no banco
        $sql_insert = "INSERT INTO especies_imagens (
            especie_id, parte_planta, caminho_imagem, nome_original,
            tamanho_bytes, mime_type, fonte_nome, fonte_url,
            autor_imagem, licenca, descricao, id_usuario_identificador,
            status_validacao, data_upload
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'aprovado', NOW())";
        
        $stmt = $conexao->prepare($sql_insert);
        $stmt->bind_param(
            "isssissssssi",
            $especie_id,
            $parte_planta,
            $caminho_relativo,
            $arquivo['name'],
            $arquivo['size'],
            $arquivo['type'],
            $fonte_nome,
            $fonte_url,
            $autor_imagem,
            $licenca,
            $descricao,
            $id_usuario
        );
        
        if ($stmt->execute()) {
            $sucessos++;
        } else {
            $erros[] = "Arquivo {$arquivo['name']}: Erro ao salvar no banco.";
            if (file_exists($caminho_completo)) {
                unlink($caminho_completo);
            }
        }
        $stmt->close();
        
    } else {
        $erros[] = "Arquivo {$arquivo['name']}: Erro ao processar imagem.";
    }
}

// ================================================
// ATUALIZAR STATUS DA ESPÉCIE
// ================================================
if ($sucessos > 0) {
    atualizarStatusImagens($conexao, $especie_id);
}

$conexao->close();

// ================================================
// REDIRECIONAMENTO
// ================================================

$mensagem = "$sucessos de $total_arquivos imagens de $parte_planta enviadas com sucesso.";
if (count($erros) > 0) {
    $mensagem .= " Erros: " . implode(" | ", $erros);
}

if ($sucessos > 0) {
    header("Location: ../Views/enviar_imagem.php?especie_id=" . $especie_id . "&parte=" . urlencode($parte_planta) . "&sucesso=" . urlencode($mensagem));
} else {
    header("Location: ../Views/enviar_imagem.php?especie_id=" . $especie_id . "&parte=" . urlencode($parte_planta) . "&erro=" . urlencode($mensagem));
}

ob_end_flush();
exit;