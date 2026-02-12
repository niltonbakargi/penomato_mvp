<?php
// ================================================
// CONTROLLER DE UPLOAD DE IMAGENS
// Processa o envio das imagens e salva no banco
// ================================================

session_start();
ob_start();

// Configurações do banco
$servidor = "127.0.0.1";
$usuario = "root";
$senha = "";
$banco = "penomato";

// Configurações de upload
$pasta_upload = dirname(dirname(__DIR__)) . '/uploads/exsicatas/';
$tamanho_maximo = 10 * 1024 * 1024; // 10MB
$formatos_permitidos = ['image/jpeg', 'image/png', 'image/jpg'];
$extensoes_permitidas = ['jpg', 'jpeg', 'png'];

// ================================================
// FUNÇÕES
// ================================================

/**
 * Cria a estrutura de pastas se não existir
 */
function criarPastasUpload($caminho) {
    if (!file_exists($caminho)) {
        mkdir($caminho, 0777, true);
        error_log("Pasta criada: " . $caminho);
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
    // Verifica se houve erro no upload
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $erro = "Erro no upload do arquivo. Código: " . $arquivo['error'];
        return false;
    }
    
    // Verifica tamanho
    if ($arquivo['size'] > $GLOBALS['tamanho_maximo']) {
        $erro = "Arquivo muito grande. Tamanho máximo: 10MB";
        return false;
    }
    
    // Verifica tipo MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $arquivo['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $GLOBALS['formatos_permitidos'])) {
        $erro = "Formato de arquivo não permitido. Use JPG ou PNG.";
        return false;
    }
    
    // Verifica extensão
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
    
    // Se já está no tamanho adequado, apenas copia
    if ($info[0] <= $largura_max && $info[1] <= $altura_max) {
        return copy($caminho_origem, $caminho_destino);
    }
    
    // Redimensiona
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
    
    // Mantém transparência para PNG
    if ($info['mime'] == 'image/png') {
        imagealphablending($imagem_destino, false);
        imagesavealpha($imagem_destino, true);
        $transparente = imagecolorallocatealpha($imagem_destino, 255, 255, 255, 127);
        imagefilledrectangle($imagem_destino, 0, 0, $nova_largura, $nova_altura, $transparente);
    }
    
    imagecopyresampled($imagem_destino, $imagem_origem, 0, 0, 0, 0, 
                       $nova_largura, $nova_altura, $info[0], $info[1]);
    
    // Salva imagem
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
 * Atualiza o status_imagens na tabela especies_administrativo
 */
function atualizarStatusImagens($conexao, $especie_id) {
    // Define partes obrigatórias
    $partes_obrigatorias = ['folha', 'flor', 'fruto', 'caule', 'habito'];
    $total_obrigatorias = count($partes_obrigatorias);
    $partes_validadas = 0;
    
    // Verifica cada parte obrigatória
    foreach ($partes_obrigatorias as $parte) {
        $sql = "SELECT COUNT(*) as total 
                FROM imagens_especies 
                WHERE especie_id = ? AND parte = ? AND status_validacao = 'validado'";
        
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "is", $especie_id, $parte);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $dados = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);
        
        if ($dados['total'] > 0) {
            $partes_validadas++;
        }
    }
    
    // Verifica se tem pelo menos UMA imagem (qualquer status)
    $sql_qualquer = "SELECT COUNT(*) as total FROM imagens_especies WHERE especie_id = ?";
    $stmt = mysqli_prepare($conexao, $sql_qualquer);
    mysqli_stmt_bind_param($stmt, "i", $especie_id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $dados_qualquer = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);
    
    // Define novo status
    if ($dados_qualquer['total'] == 0) {
        $novo_status = 'sem_imagens';
    } elseif ($partes_validadas >= $total_obrigatorias) {
        $novo_status = 'completo';
    } else {
        $novo_status = 'parcial';
    }
    
    // Atualiza a espécie
    $sql_update = "UPDATE especies_administrativo 
                   SET status_imagens = ? 
                   WHERE id = ?";
    
    $stmt = mysqli_prepare($conexao, $sql_update);
    mysqli_stmt_bind_param($stmt, "si", $novo_status, $especie_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $novo_status;
}

// ================================================
// PROCESSAMENTO DO UPLOAD
// ================================================

// Inicializa variáveis
$erro = '';
$sucesso = '';

// Log para depuração
error_log("=== INÍCIO DO PROCESSAMENTO DE UPLOAD ===");
error_log("Método: " . $_SERVER['REQUEST_METHOD']);
error_log("POST: " . print_r($_POST, true));
error_log("FILES: " . print_r($_FILES, true));

// Verifica se é POST e tem arquivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagem'])) {
    
    // Valida campos obrigatórios
    if (empty($_POST['especie_id'])) {
        $erro = 'ID da espécie não informado.';
        error_log("ERRO: especie_id vazio");
    } elseif (empty($_POST['parte'])) {
        $erro = 'Parte da planta não informada.';
        error_log("ERRO: parte vazia");
    } elseif (empty($_POST['descricao'])) {
        $erro = 'Legenda descritiva é obrigatória.';
        error_log("ERRO: descricao vazia");
    } elseif (empty($_POST['id_usuario_identificador'])) {
        $erro = 'Usuário identificador não informado.';
        error_log("ERRO: id_usuario_identificador vazio");
    } else {
        
        $especie_id = (int)$_POST['especie_id'];
        $parte = $_POST['parte'];
        $descricao = trim($_POST['descricao']);
        $id_usuario = (int)$_POST['id_usuario_identificador'];
        $localizacao = !empty($_POST['localizacao']) ? $_POST['localizacao'] : null;
        $data_coleta = !empty($_POST['data_coleta']) ? $_POST['data_coleta'] : null;
        $observacoes = !empty($_POST['observacoes']) ? $_POST['observacoes'] : null;
        
        error_log("Processando: especie_id={$especie_id}, parte={$parte}, usuario={$id_usuario}");
        
        // Conecta ao banco
        $conexao = mysqli_connect($servidor, $usuario, $senha, $banco);
        
        if (!$conexao) {
            $erro = 'Erro ao conectar ao banco de dados.';
            error_log("ERRO: Falha na conexão com BD");
        } else {
            mysqli_set_charset($conexao, "utf8mb4");
            
            // Valida se a espécie existe
            $sql_check = "SELECT id, nome_cientifico FROM especies_administrativo WHERE id = ?";
            $stmt = mysqli_prepare($conexao, $sql_check);
            mysqli_stmt_bind_param($stmt, "i", $especie_id);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);
            $especie = mysqli_fetch_assoc($resultado);
            mysqli_stmt_close($stmt);
            
            if (!$especie) {
                $erro = 'Espécie não encontrada.';
                error_log("ERRO: Espécie ID {$especie_id} não encontrada");
            } else {
                
                // Valida a imagem
                $arquivo = $_FILES['imagem'];
                
                if (validarImagem($arquivo, $erro)) {
                    
                    // Cria pasta de upload
                    $pasta_raiz = dirname(dirname(__DIR__)) . '/uploads/exsicatas/';
                    $pasta_especie = $pasta_raiz . $especie_id . '/';
                    
                    error_log("Criando pasta: " . $pasta_especie);
                    criarPastasUpload($pasta_especie);
                    
                    // Gera nome do arquivo
                    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
                    $nome_arquivo = gerarNomeArquivo($especie_id, $parte, $extensao);
                    $caminho_completo = $pasta_especie . $nome_arquivo;
                    $caminho_relativo = 'uploads/exsicatas/' . $especie_id . '/' . $nome_arquivo;
                    
                    error_log("Salvando imagem em: " . $caminho_completo);
                    
                    // Redimensiona e salva a imagem
                    if (redimensionarImagem($arquivo['tmp_name'], $caminho_completo)) {
                        
                        // Verifica se as colunas existem na tabela
                        $sql_check_col = "SHOW COLUMNS FROM imagens_especies LIKE 'descricao'";
                        $check_result = mysqli_query($conexao, $sql_check_col);
                        $tem_descricao = mysqli_num_rows($check_result) > 0;
                        
                        if ($tem_descricao) {
                            // Insere no banco com os campos novos
                            $sql_insert = "INSERT INTO imagens_especies (
                                especie_id,
                                parte,
                                caminho_imagem,
                                id_usuario_identificador,
                                status_validacao,
                                data_upload,
                                descricao,
                                localizacao,
                                data_coleta,
                                observacoes
                            ) VALUES (?, ?, ?, ?, 'pendente', NOW(), ?, ?, ?, ?)";
                            
                            $stmt = mysqli_prepare($conexao, $sql_insert);
                            mysqli_stmt_bind_param($stmt, 
                                "ississss", 
                                $especie_id,
                                $parte,
                                $caminho_relativo,
                                $id_usuario,
                                $descricao,
                                $localizacao,
                                $data_coleta,
                                $observacoes
                            );
                        } else {
                            // Insere no banco sem os campos novos
                            $sql_insert = "INSERT INTO imagens_especies (
                                especie_id,
                                parte,
                                caminho_imagem,
                                id_usuario_identificador,
                                status_validacao,
                                data_upload
                            ) VALUES (?, ?, ?, ?, 'pendente', NOW())";
                            
                            $stmt = mysqli_prepare($conexao, $sql_insert);
                            mysqli_stmt_bind_param($stmt, 
                                "issi", 
                                $especie_id,
                                $parte,
                                $caminho_relativo,
                                $id_usuario
                            );
                        }
                        
                        if (mysqli_stmt_execute($stmt)) {
                            $imagem_id = mysqli_insert_id($conexao);
                            
                            // Atualiza status_imagens da espécie
                            $novo_status = atualizarStatusImagens($conexao, $especie_id);
                            
                            $sucesso = "Imagem enviada com sucesso! ID: {$imagem_id}";
                            error_log("SUCESSO: Imagem {$imagem_id} salva. Novo status da espécie: {$novo_status}");
                            
                        } else {
                            $erro = "Erro ao salvar dados no banco: " . mysqli_error($conexao);
                            error_log("ERRO BD: " . mysqli_error($conexao));
                            
                            // Remove arquivo se falhou no banco
                            if (file_exists($caminho_completo)) {
                                unlink($caminho_completo);
                                error_log("Arquivo removido: " . $caminho_completo);
                            }
                        }
                        mysqli_stmt_close($stmt);
                        
                    } else {
                        $erro = "Erro ao processar a imagem.";
                        error_log("ERRO: Falha ao redimensionar/salvar imagem");
                    }
                } else {
                    error_log("ERRO validação: " . $erro);
                }
            }
            mysqli_close($conexao);
        }
    }
} else {
    $erro = 'Requisição inválida. Nenhum arquivo enviado.';
    error_log("ERRO: Requisição inválida ou nenhum arquivo");
}

error_log("=== FIM DO PROCESSAMENTO ===");

// ================================================
// REDIRECIONAMENTO E FEEDBACK
// ================================================

// Guarda mensagens na sessão
$_SESSION['mensagem_sucesso'] = $sucesso;
$_SESSION['mensagem_erro'] = $erro;

// Redireciona de volta para a página de upload
$redirect = "../Views/upload_imagem_views.php";
if (!empty($_POST['especie_id'])) {
    $redirect .= "?especie_id=" . $_POST['especie_id'];
}

error_log("Redirecionando para: " . $redirect);
header("Location: {$redirect}");
ob_end_clean();
exit;