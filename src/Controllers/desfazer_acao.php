<?php
// ================================================
// DESFAZER AÇÃO — reverte ação do usuário (até 1 dia)
// ou envia solicitação ao gestor se prazo vencido
// ================================================
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

$usuario_id = (int)$_SESSION['usuario_id'];
$acao       = $_POST['acao'] ?? '';
$hist_id    = (int)($_POST['hist_id'] ?? 0);
$redirect   = APP_BASE . '/src/Controllers/minhas_acoes.php';

if (!$hist_id) {
    header("Location: $redirect?erro=" . urlencode('Ação inválida.'));
    exit;
}

// Carregar o registro do histórico (somente do próprio usuário)
$stmt = $pdo->prepare("
    SELECT h.*, e.nome_cientifico
    FROM historico_alteracoes h
    JOIN especies_administrativo e ON e.id = h.especie_id
    WHERE h.id = ? AND h.id_usuario = ? AND h.revertida = 0
");
$stmt->execute([$hist_id, $usuario_id]);
$hist = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$hist) {
    header("Location: $redirect?erro=" . urlencode('Ação não encontrada ou já desfeita.'));
    exit;
}

$dentro_prazo = (strtotime($hist['data_alteracao']) > strtotime('-1 day'));

// ── SOLICITAR AO GESTOR (fora do prazo) ──────────────────────────
if ($acao === 'solicitar') {
    $justificativa = trim($_POST['justificativa'] ?? '');
    if (!$justificativa) {
        header("Location: $redirect?erro=" . urlencode('Justificativa obrigatória.'));
        exit;
    }
    $pdo->prepare("
        INSERT INTO fila_aprovacao
            (tipo, especie_id, usuario_id, descricao, observacoes)
        VALUES ('contestacao', ?, ?, ?, ?)
    ")->execute([
        $hist['especie_id'],
        $usuario_id,
        'Solicitação de desfazer ação: ' . $hist['tipo_acao'] . ' em ' . $hist['campo_alterado'],
        $justificativa
    ]);
    header("Location: $redirect?ok=" . urlencode('Solicitação enviada ao gestor.'));
    exit;
}

// ── DESFAZER (dentro do prazo) ───────────────────────────────────
if ($acao === 'desfazer' && $dentro_prazo) {

    $especie_id = (int)$hist['especie_id'];
    $extras     = $hist['dados_extras'] ? json_decode($hist['dados_extras'], true) : [];

    // 1. Upload de imagem → remove imagem
    if ($hist['tabela_afetada'] === 'especies_imagens' && isset($extras['imagem_id'])) {
        $imagem_id = (int)$extras['imagem_id'];
        $stmt = $pdo->prepare("SELECT caminho_imagem FROM especies_imagens WHERE id = ? AND especie_id = ?");
        $stmt->execute([$imagem_id, $especie_id]);
        $img = $stmt->fetch();
        if ($img) {
            $arquivo = __DIR__ . '/../../../' . $img['caminho_imagem'];
            if (file_exists($arquivo)) unlink($arquivo);
            $pdo->prepare("DELETE FROM especies_imagens WHERE id = ?")->execute([$imagem_id]);
        }
        $pdo->prepare("UPDATE especies_administrativo SET data_ultima_atualizacao = NOW() WHERE id = ?")
            ->execute([$especie_id]);
    }

    // 2. Inserção de dados da internet → apaga dados + artigo + volta sem_dados
    elseif ($hist['tabela_afetada'] === 'especies_caracteristicas' && $hist['tipo_acao'] === 'insercao') {
        $pdo->prepare("DELETE FROM especies_caracteristicas WHERE especie_id = ?")->execute([$especie_id]);
        $pdo->prepare("DELETE FROM artigos WHERE especie_id = ?")->execute([$especie_id]);
        // Imagens de internet também
        $stmt = $pdo->prepare("SELECT caminho_imagem FROM especies_imagens WHERE especie_id = ? AND origem = 'internet'");
        $stmt->execute([$especie_id]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $caminho) {
            $arquivo = __DIR__ . '/../../../' . $caminho;
            if (file_exists($arquivo)) unlink($arquivo);
        }
        $pdo->prepare("DELETE FROM especies_imagens WHERE especie_id = ? AND origem = 'internet'")->execute([$especie_id]);
        $pdo->prepare("UPDATE especies_administrativo SET status = 'sem_dados', data_ultima_atualizacao = NOW() WHERE id = ?")
            ->execute([$especie_id]);
    }

    // 3. Confirmação de dados (descrita) → volta para dados_internet
    elseif ($hist['campo_alterado'] === 'status' && $hist['valor_novo'] === 'descrita') {
        $pdo->prepare("
            UPDATE especies_administrativo
            SET status = 'dados_internet', data_ultima_atualizacao = NOW()
            WHERE id = ?
        ")->execute([$especie_id]);
    }

    // 4. Aprovação (publicado) → volta para em_revisao, artigo volta rascunho
    elseif ($hist['campo_alterado'] === 'status' && $hist['valor_novo'] === 'publicado' && $hist['tipo_acao'] === 'revisao') {
        $pdo->prepare("
            UPDATE especies_administrativo
            SET status = 'em_revisao', data_ultima_atualizacao = NOW()
            WHERE id = ?
        ")->execute([$especie_id]);
        $pdo->prepare("
            UPDATE artigos SET status = 'rascunho', atualizado_em = NOW()
            WHERE especie_id = ?
        ")->execute([$especie_id]);
    }

    // 5. Contestação → volta para em_revisao
    elseif ($hist['campo_alterado'] === 'status' && $hist['valor_novo'] === 'contestado') {
        $pdo->prepare("
            UPDATE especies_administrativo
            SET status = 'em_revisao', data_ultima_atualizacao = NOW()
            WHERE id = ?
        ")->execute([$especie_id]);
    }

    // Marcar como revertida
    $pdo->prepare("UPDATE historico_alteracoes SET revertida = 1 WHERE id = ?")
        ->execute([$hist_id]);

    header("Location: $redirect?ok=" . urlencode('Ação desfeita com sucesso.'));
    exit;
}

header("Location: $redirect?erro=" . urlencode('Não foi possível desfazer esta ação.'));
exit;
