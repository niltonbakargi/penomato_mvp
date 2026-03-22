<?php
/**
 * CONTROLADOR DE CADASTRO - VERSÃO SIMPLIFICADA
 */

// ============================================================
// INICIALIZAÇÃO
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ativar exibição de erros (remova em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/verificar_acesso.php';

// ============================================================
// VERIFICAR SE JÁ ESTÁ LOGADO
// ============================================================
if (estaLogado()) {
    header('Location: ' . APP_BASE . '/perfil');
    exit;
}

// ============================================================
// VERIFICAR SE VEIO POR POST
// ============================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE . '/cadastro');
    exit;
}

// ============================================================
// RECEBER DADOS
// ============================================================
$nome = trim($_POST['nome'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$confirmar_email = strtolower(trim($_POST['confirmar_email'] ?? ''));
$senha = $_POST['senha'] ?? '';
$confirmar_senha = $_POST['confirmar_senha'] ?? '';
$tipo_form   = $_POST['tipo']        ?? '';
$instituicao = trim($_POST['instituicao'] ?? '');

// Mapear tipo do formulário → categoria do banco + subtipo
$mapa_perfis = [
    'identificador' => ['categoria' => 'colaborador', 'subtipo' => 'identificador'],
    'especialista'  => ['categoria' => 'revisor',     'subtipo' => 'especialista'],
    'gestor'        => ['categoria' => 'gestor',       'subtipo' => 'gestor'],
    'dev'           => ['categoria' => 'colaborador',  'subtipo' => 'dev'],
];
$perfil   = $mapa_perfis[$tipo_form] ?? null;
$categoria = $perfil['categoria'] ?? '';
$subtipo   = $perfil['subtipo']   ?? null;

// Guardar dados na sessão para repopular o formulário
$_SESSION['dados_cadastro'] = [
    'nome'           => $nome,
    'email'          => $email,
    'confirmar_email'=> $confirmar_email,
    'tipo'           => $tipo_form,
    'instituicao'    => $instituicao,
];

// ============================================================
// VALIDAÇÕES BÁSICAS
// ============================================================
$erros = [];

if (empty($nome) || strlen($nome) < 3) {
    $erros[] = "Nome completo é obrigatório (mínimo 3 caracteres).";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erros[] = "E-mail válido é obrigatório.";
} elseif ($email !== $confirmar_email) {
    $erros[] = "E-mails não conferem.";
}

if (empty($senha)) {
    $erros[] = "Senha é obrigatória.";
} elseif ($senha !== $confirmar_senha) {
    $erros[] = "Senhas não conferem.";
}

if (!$perfil) {
    $erros[] = "Perfil de atuação é obrigatório.";
}

// ============================================================
// VERIFICAR SE EMAIL JÁ EXISTE
// ============================================================
if (empty($erros)) {
    $existe = buscarUm(
        "SELECT id FROM usuarios WHERE email = :email",
        [':email' => $email]
    );
    
    if ($existe) {
        $erros[] = "Este e-mail já está cadastrado.";
    }
}

// ============================================================
// SE HOUVER ERROS, VOLTAR
// ============================================================
if (!empty($erros)) {
    $_SESSION['mensagem_erro'] = implode('<br>', $erros);
    header('Location: ' . APP_BASE . '/cadastro');
    exit;
}

// ============================================================
// INSERIR USUÁRIO NO BANCO
// ============================================================
try {
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    
    $dados_insert = [
        'nome'               => $nome,
        'email'              => $email,
        'senha_hash'         => $senha_hash,
        'categoria'          => $categoria,
        'subtipo_colaborador' => $subtipo,
        'instituicao'        => $instituicao ?: null,
        'status_verificacao' => 'verificado',
        'ativo'              => 1,
        'data_cadastro'      => date('Y-m-d H:i:s'),
    ];
    
    $usuario_id = inserir('usuarios', $dados_insert);
    
    if (!$usuario_id) {
        throw new Exception("Erro ao inserir usuário.");
    }

    // ============================================================
    // SUCESSO - REDIRECIONAR PARA LOGIN
    // ============================================================
    $_SESSION['mensagem_sucesso'] = "Cadastro realizado com sucesso! Faça login para continuar.";
    unset($_SESSION['dados_cadastro']);

    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
    
} catch (Exception $e) {
    error_log("Erro no cadastro: " . $e->getMessage());
    $_SESSION['mensagem_erro'] = "Erro ao realizar cadastro. Tente novamente.";
    header('Location: ' . APP_BASE . '/cadastro');
    exit;
}
?>