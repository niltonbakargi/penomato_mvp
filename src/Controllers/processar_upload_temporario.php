<?php
// ================================================
// PROCESSAR UPLOAD TEMPORÁRIO DE IMAGENS
// VERSÃO MODIFICADA - Aceita arquivo OU base64 (imagem colada)
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
$tamanho_maximo = 10 * 1024 * 1024; // 10MB
$formatos_permitidos = ['image/jpeg', 'image/png', 'image/jpg'];
$extensoes_permitidas = ['jpg', 'jpeg', 'png'];

// ================================================
// VERIFICAR SE USUÁRIO ESTÁ LOGADO
// ================================================
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../Views/auth/login.php?erro=" . urlencode("Faça login para continuar."));
    exit;
}
$id_usuario = $_SESSION['usuario_id'];

// ================================================
// VERIFICAR SESSÃO TEMPORÁRIA
// ================================================
$temp_id = isset($_POST['temp_id']) ? $_POST['temp_id'] : '';

if (empty($temp_id) || !isset($_SESSION['importacao_temporaria']) || $_SESSION['importacao_temporaria']['temp_id'] !== $temp_id) {
    header("Location: ../Views/upload_imagens_internet.php?erro=" . urlencode("Sessão temporária inválida ou expirada."));
    exit;
}

// Verificar se o usuário da sessão é o mesmo que iniciou a importação
if ($_SESSION['importacao_temporaria']['usuario_id'] != $id_usuario) {
    header("Location: ../Views/upload_imagens_internet.php?erro=" . urlencode("Você não tem permissão para modificar esta importação."));
    exit;
}

// ================================================
// VALIDAR CAMPOS OBRIGATÓRIOS
// ================================================
if (empty($_POST['parte_planta'])) {
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode("Parte da planta não informada."));
    exit;
}

$parte_planta = $_POST['parte_planta'];

// ================================================
// DADOS DE METADADOS
// ================================================
$fonte_nome = $_POST['fonte_nome'] ?? null;
$fonte_url = $_POST['fonte_url'] ?? null;
$autor_imagem = $_POST['autor_imagem'] ?? null;
$licenca = $_POST['licenca'] ?? null;
$descricao = $_POST['descricao'] ?? null;

// ================================================
// FUNÇÕES DE VALIDAÇÃO
// ================================================
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

// ================================================
// FUNÇÕES DE ARQUIVO
// ================================================
function criarPastaTemporaria($temp_id) {
    $pasta_temp = dirname(dirname(__DIR__)) . '/uploads/temp/' . $temp_id . '/';
    if (!file_exists($pasta_temp)) {
        mkdir($pasta_temp, 0777, true);
    }
    return $pasta_temp;
}

function gerarNomeArquivoTemp($temp_id, $parte, $extensao) {
    $timestamp = date('Ymd_His');
    $random = rand(100, 999);
    return "{$parte}_{$timestamp}_{$random}.{$extensao}";
}

// ================================================
// PROCESSAR ARQUIVOS
// ================================================
$erros = [];
$sucessos = 0;

// Inicializar array de imagens na sessão se não existir
if (!isset($_SESSION['importacao_temporaria']['imagens'])) {
    $_SESSION['importacao_temporaria']['imagens'] = [];
}

// ================================================
// VERIFICAR SE VEIO ARQUIVO OU BASE64
// ================================================
if (isset($_FILES['imagens']) && !empty($_FILES['imagens']['name'][0])) {
    // ================================================
    // CASO 1: UPLOAD DE ARQUIVOS
    // ================================================
    $total_arquivos = count($_FILES['imagens']['name']);
    
    // Criar pasta temporária
    $pasta_temp = criarPastaTemporaria($temp_id);
    
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
        $nome_arquivo = gerarNomeArquivoTemp($temp_id, $parte_planta, $extensao);
        $caminho_completo = $pasta_temp . $nome_arquivo;
        $caminho_relativo = 'uploads/temp/' . $temp_id . '/' . $nome_arquivo;
        
        // Mover arquivo para pasta temporária
        if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
            
            // Adicionar à sessão
            $imagem_info = [
                'nome_original' => $arquivo['name'],
                'caminho_temporario' => $caminho_relativo,
                'parte_planta' => $parte_planta,
                'tamanho_bytes' => $arquivo['size'],
                'mime_type' => $arquivo['type'],
                'fonte_nome' => $fonte_nome,
                'fonte_url' => $fonte_url,
                'autor_imagem' => $autor_imagem,
                'licenca' => $licenca,
                'descricao' => $descricao,
                'usuario_id' => $id_usuario,
                'data_upload_temp' => time()
            ];
            
            $_SESSION['importacao_temporaria']['imagens'][] = $imagem_info;
            $sucessos++;
            
        } else {
            $erros[] = "Arquivo {$arquivo['name']}: Erro ao salvar arquivo.";
        }
    }
    
    $mensagem = "$sucessos de $total_arquivos imagens adicionadas à sessão temporária.";
    
} elseif (isset($_POST['imagem_base64']) && !empty($_POST['imagem_base64'])) {
    // ================================================
    // CASO 2: IMAGEM COLADA (BASE64)
    // ================================================
    $imagem_base64 = $_POST['imagem_base64'];
    
    // Extrair os dados da base64
    if (preg_match('/^data:image\/(\w+);base64,/', $imagem_base64, $matches)) {
        $tipo_imagem = $matches[1]; // jpeg, png, etc.
        $base64_sem_cabecalho = substr($imagem_base64, strpos($imagem_base64, ',') + 1);
        $dados_imagem = base64_decode($base64_sem_cabecalho);
        
        if ($dados_imagem === false) {
            $erros[] = "Erro ao decodificar imagem base64.";
        } else {
            // Validar tipo
            if (!in_array('image/' . $tipo_imagem, $GLOBALS['formatos_permitidos'])) {
                $erros[] = "Formato de imagem não permitido. Use JPG ou PNG.";
            } else {
                // Validar tamanho
                $tamanho = strlen($dados_imagem);
                if ($tamanho > $GLOBALS['tamanho_maximo']) {
                    $erros[] = "Imagem muito grande. Tamanho máximo: 10MB";
                } else {
                    
                    // Criar pasta temporária
                    $pasta_temp = criarPastaTemporaria($temp_id);
                    
                    // Gerar nome do arquivo
                    $extensao = $tipo_imagem == 'jpeg' ? 'jpg' : $tipo_imagem;
                    $nome_arquivo = gerarNomeArquivoTemp($temp_id, $parte_planta, $extensao);
                    $caminho_completo = $pasta_temp . $nome_arquivo;
                    $caminho_relativo = 'uploads/temp/' . $temp_id . '/' . $nome_arquivo;
                    
                    // Salvar arquivo
                    if (file_put_contents($caminho_completo, $dados_imagem)) {
                        
                        // Adicionar à sessão
                        $imagem_info = [
                            'nome_original' => 'imagem_colada_' . date('Ymd_His') . '.' . $extensao,
                            'caminho_temporario' => $caminho_relativo,
                            'parte_planta' => $parte_planta,
                            'tamanho_bytes' => $tamanho,
                            'mime_type' => 'image/' . $tipo_imagem,
                            'fonte_nome' => $fonte_nome,
                            'fonte_url' => $fonte_url,
                            'autor_imagem' => $autor_imagem,
                            'licenca' => $licenca,
                            'descricao' => $descricao,
                            'usuario_id' => $id_usuario,
                            'data_upload_temp' => time(),
                            'colado' => true
                        ];
                        
                        $_SESSION['importacao_temporaria']['imagens'][] = $imagem_info;
                        $sucessos++;
                        
                    } else {
                        $erros[] = "Erro ao salvar imagem colada.";
                    }
                }
            }
        }
    } else {
        $erros[] = "Formato de imagem base64 inválido.";
    }
    
    $mensagem = "$sucessos imagem(ns) colada(s) adicionada(s) à sessão temporária.";
    
} else {
    // ================================================
    // CASO 3: NENHUMA IMAGEM
    // ================================================
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&parte=" . urlencode($parte_planta) . "&erro=" . urlencode("Nenhuma imagem selecionada ou colada."));
    exit;
}

// ================================================
// ATUALIZAR TIMESTAMP DA SESSÃO
// ================================================
if ($sucessos > 0) {
    $_SESSION['importacao_temporaria']['ultima_atualizacao'] = time();
}

// ================================================
// REDIRECIONAMENTO
// ================================================
if ($sucessos > 0) {
    if (count($erros) > 0) {
        $mensagem .= " Erros: " . implode(" | ", $erros);
        header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&parte=" . urlencode($parte_planta) . "&erro=" . urlencode($mensagem));
    } else {
        header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&sucesso=" . urlencode($mensagem));
    }
} else {
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&parte=" . urlencode($parte_planta) . "&erro=" . urlencode($mensagem));
}

ob_end_flush();
exit;
?>