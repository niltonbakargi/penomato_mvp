<?php
// ============================================================
// PROCESSAR REVISÃO DE EXEMPLAR
// ============================================================
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';
require_once __DIR__ . '/../../config/email.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE . '/src/Views/revisor/revisar_exemplar.php');
    exit;
}

$usuario_id  = (int)$_SESSION['usuario_id'];
$exemplar_id = (int)($_POST['exemplar_id'] ?? 0);
$acao        = trim($_POST['acao']         ?? '');
$motivo      = trim($_POST['motivo_rejeicao'] ?? '');
$filtro      = in_array($_POST['filtro'] ?? '', ['aguardando_revisao','aprovado','rejeitado','todos'])
               ? $_POST['filtro'] : 'aguardando_revisao';

$redirect = APP_BASE . '/src/Views/revisor/revisar_exemplar.php?filtro=' . $filtro;

// ── Validações básicas ────────────────────────────────────────────────────────
if (!$exemplar_id || !in_array($acao, ['aprovar', 'rejeitar'])) {
    header("Location: {$redirect}&erro=" . urlencode('Requisição inválida.'));
    exit;
}
if ($acao === 'rejeitar' && !$motivo) {
    header("Location: {$redirect}&erro=" . urlencode('Informe o motivo da rejeição.'));
    exit;
}

// ── Verificar que o exemplar pertence a este especialista ─────────────────────
$stmt = $pdo->prepare("
    SELECT id, codigo, especie_id, status
    FROM exemplares
    WHERE id = ? AND especialista_id = ?
");
$stmt->execute([$exemplar_id, $usuario_id]);
$exemplar = $stmt->fetch();

if (!$exemplar) {
    header("Location: {$redirect}&erro=" . urlencode('Exemplar não encontrado ou sem permissão.'));
    exit;
}
if ($exemplar['status'] !== 'aguardando_revisao') {
    header("Location: {$redirect}&erro=" . urlencode('Este exemplar já foi revisado.'));
    exit;
}

// ── Aplicar decisão ───────────────────────────────────────────────────────────
try {
    $pdo->beginTransaction();

    if ($acao === 'aprovar') {
        $stmt = $pdo->prepare("
            UPDATE exemplares
            SET status = 'aprovado', data_revisao = NOW(), motivo_rejeicao = NULL
            WHERE id = ?
        ");
        $stmt->execute([$exemplar_id]);

        $stmt_hist = $pdo->prepare("
            INSERT INTO historico_alteracoes
                (especie_id, id_usuario, tabela_afetada, campo_alterado, valor_novo, tipo_acao)
            VALUES (?, ?, 'exemplares', 'status', 'aprovado', 'validacao')
        ");
        $stmt_hist->execute([$exemplar['especie_id'], $usuario_id]);

        $pdo->commit();

        // Notificar quem cadastrou o exemplar
        $stmt_cad = $pdo->prepare("SELECT u.email, u.nome FROM usuarios u JOIN exemplares e ON e.cadastrado_por = u.id WHERE e.id = ?");
        $stmt_cad->execute([$exemplar_id]);
        $cadastrador = $stmt_cad->fetch();
        if ($cadastrador) {
            $conteudo_email = "
                <p>Olá, <strong>" . htmlspecialchars($cadastrador['nome']) . "</strong>!</p>
                <p>Seu exemplar <strong>{$exemplar['codigo']}</strong> foi <strong style='color:#0b5e42;'>APROVADO</strong> pelo especialista.</p>
                <p>Os colaboradores já podem enviar fotos das partes do exemplar. Acesse a plataforma para acompanhar.</p>";
            enviarEmail(
                $cadastrador['email'],
                'Exemplar aprovado — Penomato',
                templateEmail('Exemplar aprovado na revisão', $conteudo_email)
            );
        }

        $msg = "Exemplar {$exemplar['codigo']} aprovado. Os colaboradores já podem enviar fotos das partes.";
        header("Location: {$redirect}&sucesso=" . urlencode($msg));

    } else {
        $stmt = $pdo->prepare("
            UPDATE exemplares
            SET status = 'rejeitado', data_revisao = NOW(), motivo_rejeicao = ?
            WHERE id = ?
        ");
        $stmt->execute([$motivo, $exemplar_id]);

        $stmt_hist = $pdo->prepare("
            INSERT INTO historico_alteracoes
                (especie_id, id_usuario, tabela_afetada, campo_alterado,
                 valor_novo, justificativa, tipo_acao)
            VALUES (?, ?, 'exemplares', 'status', 'rejeitado', ?, 'contestacao')
        ");
        $stmt_hist->execute([$exemplar['especie_id'], $usuario_id, $motivo]);

        $pdo->commit();

        // Notificar quem cadastrou o exemplar
        $stmt_cad = $pdo->prepare("SELECT u.email, u.nome FROM usuarios u JOIN exemplares e ON e.cadastrado_por = u.id WHERE e.id = ?");
        $stmt_cad->execute([$exemplar_id]);
        $cadastrador = $stmt_cad->fetch();
        if ($cadastrador) {
            $conteudo_email = "
                <p>Olá, <strong>" . htmlspecialchars($cadastrador['nome']) . "</strong>!</p>
                <p>Seu exemplar <strong>{$exemplar['codigo']}</strong> foi <strong style='color:#dc3545;'>REJEITADO</strong> na revisão.</p>
                <p><strong>Motivo:</strong> " . htmlspecialchars($motivo) . "</p>
                <p>Corrija os problemas apontados e recadastre o exemplar se necessário.</p>";
            enviarEmail(
                $cadastrador['email'],
                'Exemplar rejeitado — Penomato',
                templateEmail('Exemplar rejeitado na revisão', $conteudo_email)
            );
        }

        $msg = "Exemplar {$exemplar['codigo']} rejeitado. O colaborador será informado do motivo.";
        header("Location: {$redirect}&sucesso=" . urlencode($msg));
    }

} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Erro revisão exemplar: ' . $e->getMessage());
    header("Location: {$redirect}&erro=" . urlencode('Erro interno. Tente novamente.'));
}

exit;
