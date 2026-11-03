<?php
// controlador_gestor.php
// MVP - Painel do gestor com navegação entre perfis

session_start();

// Carregar configuração do banco
require_once __DIR__ . '/../../config/banco_de_dados.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

// Dados do usuário logado
$gestor_id    = (int)$_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Gestor';
$usuario_instituicao = $_SESSION['usuario_instituicao'] ?? 'Penomato';

// Buscar estatísticas básicas
try {
    // Total de espécies
    $stmt = $pdo->query("SELECT COUNT(*) FROM especies_administrativo");
    $total_especies = $stmt->fetchColumn();
    
    // Espécies em revisão
    $stmt = $pdo->query("SELECT COUNT(*) FROM especies_administrativo WHERE status = 'em_revisao'");
    $em_revisao = $stmt->fetchColumn();
    
    // Espécies validadas
    $stmt = $pdo->query("SELECT COUNT(*) FROM especies_administrativo WHERE status IN ('revisada', 'publicado')");
    $validadas = $stmt->fetchColumn();
    
    // Total de usuários
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
    $total_usuarios = $stmt->fetchColumn();
    
} catch (Exception $e) {
    error_log("controlador_gestor: falha ao buscar estatísticas — " . $e->getMessage());
    $total_especies = 0;
    $em_revisao     = 0;
    $validadas      = 0;
    $total_usuarios = 0;
}

// ── CSRF ────────────────────────────────────────
if (empty($_SESSION['csrf_gestor_inserir'])) {
    $_SESSION['csrf_gestor_inserir'] = bin2hex(random_bytes(32));
}

// ================================================
// PROCESSAR INSERÇÃO DE ESPÉCIES DE INTERESSE
// ================================================
$msg_especies = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inserir_especies'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_gestor_inserir'] ?? '', $csrf)) {
        $msg_especies[] = ['tipo' => 'err', 'texto' => 'Requisição inválida. Tente novamente.'];
        goto fim_post_gestor;
    }
    $_SESSION['csrf_gestor_inserir'] = bin2hex(random_bytes(32));
    $linhas = explode("\n", $_POST['lista_especies'] ?? '');
    $adicionadas = 0;
    $duplicadas  = 0;
    $erros       = 0;

    $stmt_check  = $pdo->prepare("SELECT id FROM especies_administrativo WHERE nome_cientifico = ?");
    $stmt_insert = $pdo->prepare("INSERT INTO especies_administrativo (nome_cientifico, status) VALUES (?, 'sem_dados')");

    foreach ($linhas as $linha) {
        $nome = trim($linha);
        if ($nome === '') continue;

        $stmt_check->execute([$nome]);
        if ($stmt_check->fetchColumn()) {
            $duplicadas++;
        } else {
            try {
                $stmt_insert->execute([$nome]);
                $adicionadas++;
            } catch (Exception $e) {
                $erros++;
            }
        }
    }

    if ($adicionadas > 0) {
        error_log(sprintf(
            '[GESTOR_AUDIT] inserir_especies | gestor_id=%d | adicionadas=%d | duplicadas=%d | ip=%s',
            $gestor_id, $adicionadas, $duplicadas, $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ));
        $msg_especies[] = ['tipo' => 'ok', 'texto' => "$adicionadas espécie(s) inserida(s) com sucesso."];
    }
    if ($duplicadas  > 0) $msg_especies[] = ['tipo' => 'warn', 'texto' => "$duplicadas já existia(m) no banco e foram ignorada(s)."];
    if ($erros       > 0) $msg_especies[] = ['tipo' => 'err',  'texto' => "$erros erro(s) ao inserir."];

    // Atualiza contador
    $stmt = $pdo->query("SELECT COUNT(*) FROM especies_administrativo");
    $total_especies = $stmt->fetchColumn();
}
fim_post_gestor:

// ================================================
// CONTADORES PARA BADGES
// ================================================
$membros_pendentes  = $pdo->query("SELECT id FROM usuarios WHERE status_verificacao = 'aguardando_gestor'")->fetchAll(PDO::FETCH_ASSOC);
$acoes_pendentes    = (int)$pdo->query("SELECT COUNT(*) FROM fila_aprovacao WHERE status = 'pendente'")->fetchColumn();
$artigos_pendentes  = (int)$pdo->query("SELECT COUNT(*) FROM artigos WHERE status IN ('registrado','revisando','revisado')")->fetchColumn();

// Carregar a view
include __DIR__ . '/../Views/entrada_gestor.php';
?>