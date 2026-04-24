<?php
/**
 * CONTROLADOR DE ATUALIZAÇÃO DE PERFIL - PENOMATO MVP
 * 
 * Processa o formulário de edição de perfil, valida os dados,
 * faz upload de nova foto (se houver), remove foto (se solicitado)
 * e atualiza as informações do usuário no banco de dados.
 * 
 * @package Penomato
 * @author Equipe Penomato
 * @version 1.0
 */

// ============================================================
// INICIALIZAÇÃO
// ============================================================

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir conexão com o banco de dados
require_once __DIR__ . '/../../../config/banco_de_dados.php';

// Incluir funções de verificação de acesso
require_once __DIR__ . '/../auth/verificar_acesso.php';

// ============================================================
// VERIFICAR AUTENTICAÇÃO
// ============================================================

if (!estaLogado()) {
    $_SESSION['mensagem_erro'] = "Faça login para editar seu perfil.";
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

// ============================================================
// VERIFICAR SE VEIO POR POST
// ============================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE . '/src/Views/usuario/meu_perfil.php');
    exit;
}

// ============================================================
// VALIDAR TOKEN CSRF
// ============================================================

if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
    $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    
    $_SESSION['mensagem_erro'] = "Erro de segurança. Tente novamente.";
    header('Location: ' . APP_BASE . '/src/Views/usuario/editar_perfil.php');
    exit;
}

// ============================================================
// CONFIGURAÇÕES
// ============================================================

define('FOTO_MAX_SIZE', 2 * 1024 * 1024); // 2MB
define('FOTO_WIDTH', 300); // Largura máxima da foto
define('FOTO_HEIGHT', 300); // Altura máxima da foto
define('UPLOAD_DIR', __DIR__ . '/../../../uploads/fotos_perfil/');

// Criar diretório de upload se não existir
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// ============================================================
// DADOS DO USUÁRIO
// ============================================================

$usuario_id = $_SESSION['usuario_id'];

// Buscar dados atuais do usuário
$usuario_atual = buscarUm(
    "SELECT * FROM usuarios WHERE id = :id",
    [':id' => $usuario_id]
);

if (!$usuario_atual) {
    $_SESSION['mensagem_erro'] = "Usuário não encontrado.";
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

// ============================================================
// RECEBER E SANITIZAR DADOS
// ============================================================

$dados = [
    'nome' => trim($_POST['nome'] ?? ''),
    'instituicao' => trim($_POST['instituicao'] ?? ''),
    'lattes' => trim($_POST['lattes'] ?? ''),
    'orcid' => trim($_POST['orcid'] ?? ''),
    'receber_notificacoes' => isset($_POST['receber_notificacoes']) ? 1 : 0,
    'perfil_publico' => isset($_POST['perfil_publico']) ? 1 : 0,
    'receber_newsletter' => isset($_POST['receber_newsletter']) ? 1 : 0,
    'remover_foto' => isset($_POST['remover_foto']) ? true : false
];

// Guardar dados na sessão para preencher em caso de erro
$_SESSION['dados_edicao'] = $dados;

// ============================================================
// VALIDAÇÕES
// ============================================================

$erros = [];

// 1. Validar nome
if (empty($dados['nome'])) {
    $erros['nome'] = "O nome completo é obrigatório.";
} elseif (strlen($dados['nome']) < 3) {
    $erros['nome'] = "O nome deve ter pelo menos 3 caracteres.";
} elseif (strlen($dados['nome']) > 255) {
    $erros['nome'] = "O nome é muito longo (máximo 255 caracteres).";
}

// 2. Validar Lattes (se informado)
if (!empty($dados['lattes'])) {
    if (!filter_var($dados['lattes'], FILTER_VALIDATE_URL)) {
        $erros['lattes'] = "O link do Lattes deve ser uma URL válida.";
    } elseif (!preg_match('/lattes\.cnpq\.br/', $dados['lattes'])) {
        $erros['lattes'] = "O link do Lattes deve ser do domínio lattes.cnpq.br";
    }
}

// 3. Validar ORCID (se informado)
if (!empty($dados['orcid'])) {
    // Remover formatação para validar apenas números
    $orcid_limpo = preg_replace('/[^0-9X]/', '', strtoupper($dados['orcid']));
    
    // ORCID tem 16 dígitos (4 grupos de 4) mais um dígito verificador
    if (!preg_match('/^[0-9]{4}[0-9]{4}[0-9]{4}[0-9]{3}[0-9X]$/', $orcid_limpo)) {
        $erros['orcid'] = "ORCID inválido. Use o formato 0000-0002-1825-0097";
    } else {
        // Validar dígito verificador
        if (!validarDigitoORCID($orcid_limpo)) {
            $erros['orcid'] = "ORCID inválido (dígito verificador incorreto).";
        } else {
            // Formatar ORCID corretamente
            $dados['orcid'] = substr($orcid_limpo, 0, 4) . '-' .
                              substr($orcid_limpo, 4, 4) . '-' .
                              substr($orcid_limpo, 8, 4) . '-' .
                              substr($orcid_limpo, 12, 4);
        }
    }
}

// Se houver erros, redirecionar de volta
if (!empty($erros)) {
    $_SESSION['dados_edicao']['erros'] = $erros;
    $_SESSION['mensagem_erro'] = "Corrija os erros no formulário.";
    header('Location: ' . APP_BASE . '/src/Views/usuario/editar_perfil.php');
    exit;
}

// ============================================================
// PROCESSAR FOTO (SE HOUVER UPLOAD)
// ============================================================

$foto_processada = false;
$foto_nome = $usuario_atual['foto_perfil']; // Manter foto atual por padrão

// Verificar se foi enviada uma nova foto
if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
    $foto = $_FILES['foto'];
    
    // Verificar se houve erro no upload
    if ($foto['error'] !== UPLOAD_ERR_OK) {
        $erros_upload = [
            UPLOAD_ERR_INI_SIZE => 'O arquivo excede o tamanho máximo permitido pelo servidor.',
            UPLOAD_ERR_FORM_SIZE => 'O arquivo excede o tamanho máximo do formulário.',
            UPLOAD_ERR_PARTIAL => 'O upload foi apenas parcialmente concluído.',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado.',
            UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária ausente.',
            UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever o arquivo em disco.',
            UPLOAD_ERR_EXTENSION => 'Upload interrompido por extensão.'
        ];
        $erro_foto = $erros_upload[$foto['error']] ?? 'Erro desconhecido no upload.';
        
        $_SESSION['mensagem_erro'] = $erro_foto;
        header('Location: ' . APP_BASE . '/src/Views/usuario/editar_perfil.php');
        exit;
    }
    
    // Verificar tamanho
    if ($foto['size'] > FOTO_MAX_SIZE) {
        $_SESSION['mensagem_erro'] = "A foto deve ter no máximo 2MB.";
        header('Location: ' . APP_BASE . '/src/Views/usuario/editar_perfil.php');
        exit;
    }
    
    // Verificar tipo
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $foto['tmp_name']);
    finfo_close($finfo);
    
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
    
    if (!in_array($mime_type, $tipos_permitidos)) {
        $_SESSION['mensagem_erro'] = "Formato não permitido. Use JPG, PNG ou GIF.";
        header('Location: ' . APP_BASE . '/src/Views/usuario/editar_perfil.php');
        exit;
    }
    
    // Processar upload
    try {
        // Determinar extensão
        $extensao = match($mime_type) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            default => 'jpg'
        };
        
        $foto_nome_temp = 'temp_' . uniqid() . '.' . $extensao;
        $caminho_temp = UPLOAD_DIR . $foto_nome_temp;
        
        // Mover arquivo temporário
        if (!move_uploaded_file($foto['tmp_name'], $caminho_temp)) {
            throw new Exception("Erro ao salvar a foto.");
        }
        
        // Redimensionar imagem
        list($largura_original, $altura_original) = getimagesize($caminho_temp);
        
        if ($largura_original > FOTO_WIDTH || $altura_original > FOTO_HEIGHT) {
            // Calcular proporção
            $proporcao = min(FOTO_WIDTH / $largura_original, FOTO_HEIGHT / $altura_original);
            $nova_largura = round($largura_original * $proporcao);
            $nova_altura = round($altura_original * $proporcao);
            
            // Criar imagem redimensionada
            switch ($extensao) {
                case 'jpg':
                    $imagem_original = imagecreatefromjpeg($caminho_temp);
                    $imagem_nova = imagecreatetruecolor($nova_largura, $nova_altura);
                    imagecopyresampled($imagem_nova, $imagem_original, 0, 0, 0, 0, $nova_largura, $nova_altura, $largura_original, $altura_original);
                    imagejpeg($imagem_nova, $caminho_temp, 90);
                    break;
                    
                case 'png':
                    $imagem_original = imagecreatefrompng($caminho_temp);
                    $imagem_nova = imagecreatetruecolor($nova_largura, $nova_altura);
                    imagealphablending($imagem_nova, false);
                    imagesavealpha($imagem_nova, true);
                    imagecopyresampled($imagem_nova, $imagem_original, 0, 0, 0, 0, $nova_largura, $nova_altura, $largura_original, $altura_original);
                    imagepng($imagem_nova, $caminho_temp, 9);
                    break;
                    
                case 'gif':
                    $imagem_original = imagecreatefromgif($caminho_temp);
                    $imagem_nova = imagecreatetruecolor($nova_largura, $nova_altura);
                    imagecopyresampled($imagem_nova, $imagem_original, 0, 0, 0, 0, $nova_largura, $nova_altura, $largura_original, $altura_original);
                    imagegif($imagem_nova, $caminho_temp);
                    break;
            }
            
            if (isset($imagem_original)) imagedestroy($imagem_original);
            if (isset($imagem_nova)) imagedestroy($imagem_nova);
        }
        
        // Nome final da foto (baseado no ID do usuário)
        $foto_nova = $usuario_id . '.' . $extensao;
        $caminho_final = UPLOAD_DIR . $foto_nova;
        
        // Remover foto antiga se existir
        if ($usuario_atual['foto_perfil']) {
            $_dir = realpath(UPLOAD_DIR);
            $_arq = realpath(UPLOAD_DIR . $usuario_atual['foto_perfil']);
            if ($_arq && $_dir && str_starts_with($_arq, $_dir)) unlink($_arq);
        }
        
        // Renomear arquivo temporário para o final
        rename($caminho_temp, $caminho_final);
        
        $foto_processada = true;
        $foto_nome = $foto_nova;
        
    } catch (Exception $e) {
        error_log("Erro no upload da foto: " . $e->getMessage());
        if (isset($caminho_temp) && file_exists($caminho_temp)) {
            unlink($caminho_temp);
        }
        $_SESSION['mensagem_erro'] = "Erro ao processar a foto. Tente novamente.";
        header('Location: ' . APP_BASE . '/src/Views/usuario/editar_perfil.php');
        exit;
    }
}

// ============================================================
// REMOVER FOTO (SE SOLICITADO)
// ============================================================

if ($dados['remover_foto'] && !$foto_processada) {
    if ($usuario_atual['foto_perfil']) {
        $_dir = realpath(UPLOAD_DIR);
        $_arq = realpath(UPLOAD_DIR . $usuario_atual['foto_perfil']);
        if ($_arq && $_dir && str_starts_with($_arq, $_dir)) unlink($_arq);
        $foto_nome = null;
    }
}

// ============================================================
// ATUALIZAR DADOS NO BANCO
// ============================================================

try {
    // Iniciar transação
    iniciarTransacao();
    
    // Preparar dados para atualização
    $dados_update = [
        'nome' => $dados['nome'],
        'instituicao' => $dados['instituicao'] ?: null,
        'lattes' => $dados['lattes'] ?: null,
        'orcid' => $dados['orcid'] ?: null,
        'foto_perfil' => $foto_nome,
        'receber_notificacoes' => $dados['receber_notificacoes'],
        'perfil_publico' => $dados['perfil_publico'],
        'receber_newsletter' => $dados['receber_newsletter']
    ];
    
    // Atualizar no banco
    $linhas_afetadas = atualizar(
        'usuarios',
        $dados_update,
        'id = :id',
        [':id' => $usuario_id]
    );
    
    if ($linhas_afetadas === false) {
        throw new Exception("Erro ao atualizar usuário no banco de dados.");
    }
    
    // ============================================================
    // ATUALIZAR SESSÃO COM NOVOS DADOS
    // ============================================================
    
    $_SESSION['usuario_nome'] = $dados['nome'];
    if ($foto_nome) {
        $_SESSION['usuario_foto'] = $foto_nome;
    }
    
    // ============================================================
    // REGISTRAR LOG
    // ============================================================
    
    error_log("Perfil atualizado: ID {$usuario_id} - IP: " . $_SERVER['REMOTE_ADDR']);
    
    // Confirmar transação
    confirmarTransacao();
    
    // ============================================================
    // SUCESSO - REDIRECIONAR PARA PERFIL
    // ============================================================
    
    $_SESSION['mensagem_sucesso'] = "Perfil atualizado com sucesso!";
    
    // Limpar dados de edição da sessão
    unset($_SESSION['dados_edicao']);
    
    header('Location: ' . APP_BASE . '/src/Views/usuario/meu_perfil.php');
    exit;
    
} catch (Exception $e) {
    // Reverter transação em caso de erro
    reverterTransacao();
    
    // Log do erro
    error_log("Erro na atualização do perfil: " . $e->getMessage());
    
    // Se fez upload da foto, apagar arquivo
    if ($foto_processada && isset($caminho_final) && file_exists($caminho_final)) {
        unlink($caminho_final);
    }
    
    $_SESSION['mensagem_erro'] = "Erro ao atualizar perfil. Por favor, tente novamente.";
    header('Location: ' . APP_BASE . '/src/Views/usuario/editar_perfil.php');
    exit;
}

// ============================================================
// FUNÇÕES AUXILIARES
// ============================================================

/**
 * Valida o dígito verificador do ORCID
 * (Algoritmo ISO 7064, modificado para ORCID)
 * 
 * @param string $orcid ORCID sem formatação (16 caracteres)
 * @return bool True se o dígito verificador for válido
 */
function validarDigitoORCID($orcid) {
    if (strlen($orcid) !== 16) {
        return false;
    }
    
    $total = 0;
    for ($i = 0; $i < 15; $i++) {
        $total = ($total + intval($orcid[$i])) * 2;
    }
    $resto = $total % 11;
    $digito_calculado = (12 - $resto) % 11;
    $digito_calculado = $digito_calculado == 10 ? 'X' : (string)$digito_calculado;
    
    return $orcid[15] === $digito_calculado;
}

/**
 * Função para limpar diretório de uploads (manutenção)
 */
function limparArquivosTemporarios() {
    $arquivos = glob(UPLOAD_DIR . 'temp_*');
    foreach ($arquivos as $arquivo) {
        if (is_file($arquivo) && time() - filemtime($arquivo) > 3600) {
            unlink($arquivo);
        }
    }
}

// Limpar arquivos temporários antigos (executar ocasionalmente)
if (rand(1, 100) === 1) { // 1% de chance a cada requisição
    limparArquivosTemporarios();
}

// ============================================================
// FIM DO ARQUIVO
// ============================================================
?>