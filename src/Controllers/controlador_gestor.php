<?php
// controlador_gestor.php
// MVP - Painel do gestor com navegação entre perfis

session_start();

// Carregar configuração do banco
require_once __DIR__ . '/../../config/banco_de_dados.php';
require_once __DIR__ . '/../../config/email.php';

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
    // Valores padrão em caso de erro
    $total_especies = 12;
    $em_revisao = 3;
    $validadas = 5;
    $total_usuarios = 8;
}

// ================================================
// PROCESSAR INSERÇÃO DE ESPÉCIES DE INTERESSE
// ================================================
$msg_especies = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inserir_especies'])) {
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

// ================================================
// PROCESSAR ACEITAR MEMBRO
// ================================================
$msg_aceitar = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aceitar_membro'])) {
    $membro_id  = (int)($_POST['membro_aceitar_id'] ?? 0);
    $motivacao  = trim($_POST['motivacao_aceitar'] ?? '');
    $categoria  = trim($_POST['categoria_aceitar'] ?? 'colaborador');

    if ($membro_id) {
        // Buscar dados do membro antes de atualizar
        $stmt_m = $pdo->prepare("SELECT email, nome, status_verificacao FROM usuarios WHERE id = ?");
        $stmt_m->execute([$membro_id]);
        $membro = $stmt_m->fetch(PDO::FETCH_ASSOC);

        // Só aprova quem confirmou o e-mail
        if (!$membro || $membro['status_verificacao'] !== 'aguardando_gestor') {
            $msg_aceitar[] = ['tipo' => 'err', 'texto' => 'Este membro ainda não confirmou o e-mail. A aprovação só é possível após a confirmação.'];
        } else {
        $stmt = $pdo->prepare("UPDATE usuarios SET status_verificacao = 'verificado', ativo = 1, categoria = ? WHERE id = ?");
        $stmt->execute([$categoria, $membro_id]);
        error_log(sprintf(
            '[GESTOR_AUDIT] aceitar_membro | gestor_id=%d | membro_id=%d | nome=%s | categoria=%s | ip=%s',
            $gestor_id, $membro_id, $membro['nome'] ?? 'desconhecido', $categoria, $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ));
        $msg_aceitar[] = ['tipo' => 'ok', 'texto' => "Membro aceito com sucesso." . ($motivacao ? " Motivo: $motivacao" : '')];
        $total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();

        // Notificar o membro
        if ($membro) {
            $conteudo_email = "
                <p>Olá, <strong>" . htmlspecialchars($membro['nome']) . "</strong>!</p>
                <p>Seu cadastro no <strong>Penomato</strong> foi <strong style='color:#0b5e42;'>ACEITO</strong>!</p>
                <p>Sua conta foi ativada como <strong>" . htmlspecialchars(ucfirst($categoria)) . "</strong> e você já pode acessar a plataforma.</p>"
                . ($motivacao ? "<p><strong>Observações:</strong> " . htmlspecialchars($motivacao) . "</p>" : "")
                . "<p style='margin-top:20px;'>
                    <a href='" . APP_URL . "/src/Views/auth/login.php'
                       style='background:#0b5e42;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:600;'>
                        Acessar a plataforma
                    </a>
                </p>";
            enviarEmail($membro['email'], 'Cadastro aceito — Penomato', templateEmail('Bem-vindo ao Penomato!', $conteudo_email));
        }
        } // fim else aguardando_gestor
    } else {
        $msg_aceitar[] = ['tipo' => 'err', 'texto' => 'Selecione um membro.'];
    }
}

// ================================================
// PROCESSAR EXCLUIR MEMBRO
// ================================================
$msg_excluir = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_membro'])) {
    $membro_id = (int)($_POST['membro_excluir_id'] ?? 0);
    $motivacao = trim($_POST['motivacao_excluir'] ?? '');

    if ($membro_id && $membro_id != ($_SESSION['usuario_id'] ?? 0)) {
        // Buscar dados do membro ANTES de deletar
        $stmt_m = $pdo->prepare("SELECT email, nome FROM usuarios WHERE id = ?");
        $stmt_m->execute([$membro_id]);
        $membro_excluir = $stmt_m->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$membro_id]);
        error_log(sprintf(
            '[GESTOR_AUDIT] excluir_membro | gestor_id=%d | membro_id=%d | nome=%s | email=%s | motivo=%s | ip=%s',
            $gestor_id, $membro_id,
            $membro_excluir['nome']  ?? 'desconhecido',
            $membro_excluir['email'] ?? 'desconhecido',
            $motivacao ?: '(sem motivo)',
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ));
        $msg_excluir[] = ['tipo' => 'ok', 'texto' => "Membro removido." . ($motivacao ? " Motivo: $motivacao" : '')];
        $total_usuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();

        // Notificar o membro removido
        if ($membro_excluir) {
            $conteudo_email = "
                <p>Olá, <strong>" . htmlspecialchars($membro_excluir['nome']) . "</strong>.</p>
                <p>Informamos que seu acesso ao <strong>Penomato</strong> foi <strong style='color:#dc3545;'>removido</strong>.</p>"
                . ($motivacao ? "<p><strong>Motivo:</strong> " . htmlspecialchars($motivacao) . "</p>" : "")
                . "<p>Para mais informações, entre em contato com a equipe gestora.</p>";
            enviarEmail($membro_excluir['email'], 'Acesso removido — Penomato', templateEmail('Notificação de remoção', $conteudo_email));
        }
    } else {
        $msg_excluir[] = ['tipo' => 'err', 'texto' => 'Selecione um membro válido (você não pode excluir a si mesmo).'];
    }
}

// ================================================
// BUSCAR LISTA DE MEMBROS PARA OS FORMULÁRIOS
// ================================================
$membros_pendentes  = $pdo->query("SELECT id, nome, email, categoria, status_verificacao, data_cadastro FROM usuarios WHERE status_verificacao = 'aguardando_gestor' ORDER BY data_cadastro DESC")->fetchAll(PDO::FETCH_ASSOC);
$membros_ativos     = $pdo->query("SELECT id, nome, email, categoria FROM usuarios WHERE ativo = 1 AND status_verificacao = 'verificado' ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$acoes_pendentes    = (int)$pdo->query("SELECT COUNT(*) FROM fila_aprovacao WHERE status = 'pendente'")->fetchColumn();

// Carregar a view
include __DIR__ . '/../Views/entrada_gestor.php';
?>