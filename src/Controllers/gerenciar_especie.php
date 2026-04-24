<?php
// ================================================
// GERENCIAR ESPÉCIE — gestor pode alterar status,
// dados, imagens e reverter ações
// ================================================
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

$usuario_id = (int)$_SESSION['usuario_id'];
$especie_id = (int)($_GET['id'] ?? $_POST['especie_id'] ?? 0);

if (!$especie_id) {
    header('Location: gestao_especies.php');
    exit;
}

$msg = null;

// Diretório raiz de uploads — usado para verificação de path traversal
$_raiz_uploads = realpath(__DIR__ . '/../../uploads');

// ================================================
// PROCESSAR AÇÕES POST
// ================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    // ── Mudar status ──────────────────────────────
    if ($acao === 'mudar_status') {
        $novo_status = $_POST['novo_status'] ?? '';
        $status_validos = ['sem_dados','dados_internet','descrita','registrada','em_revisao','revisada','contestado','publicado'];
        if (in_array($novo_status, $status_validos)) {
            $stmt = $pdo->prepare("SELECT status FROM especies_administrativo WHERE id = ?");
            $stmt->execute([$especie_id]);
            $status_anterior = $stmt->fetchColumn();

            if ($status_anterior !== $novo_status) {
                $pdo->prepare("UPDATE especies_administrativo SET status = ?, data_ultima_atualizacao = NOW() WHERE id = ?")
                    ->execute([$novo_status, $especie_id]);

                // Grava no histórico para permitir reverter
                $pdo->prepare("
                    INSERT INTO historico_alteracoes
                        (especie_id, id_usuario, tabela_afetada, campo_alterado, valor_anterior, valor_novo, tipo_acao)
                    VALUES (?, ?, 'especies_administrativo', 'status', ?, ?, 'edicao')
                ")->execute([$especie_id, $usuario_id, $status_anterior, $novo_status]);

                // Voltando para sem_dados: apaga tudo que não devia existir
                if ($novo_status === 'sem_dados') {
                    $pdo->prepare("DELETE FROM especies_caracteristicas WHERE especie_id = ?")->execute([$especie_id]);
                    $pdo->prepare("DELETE FROM artigos WHERE especie_id = ?")->execute([$especie_id]);
                    $stmt_imgs = $pdo->prepare("SELECT caminho_imagem FROM especies_imagens WHERE especie_id = ?");
                    $stmt_imgs->execute([$especie_id]);
                    foreach ($stmt_imgs->fetchAll(PDO::FETCH_COLUMN) as $caminho) {
                        $_arq = realpath(__DIR__ . '/../../' . $caminho);
                        if ($_arq && $_raiz_uploads && str_starts_with($_arq, $_raiz_uploads)) unlink($_arq);
                    }
                    $pdo->prepare("DELETE FROM especies_imagens WHERE especie_id = ?")->execute([$especie_id]);
                    $msg = ['tipo' => 'ok', 'texto' => 'Status revertido para sem_dados. Todos os dados, imagens e artigo foram removidos.'];
                } else {
                    $msg = ['tipo' => 'ok', 'texto' => "Status alterado para «$novo_status»."];
                }
            }
        }
    }

    // ── Excluir imagem ────────────────────────────
    elseif ($acao === 'excluir_imagem') {
        $imagem_id = (int)($_POST['imagem_id'] ?? 0);
        if ($imagem_id) {
            $stmt = $pdo->prepare("SELECT caminho_imagem FROM especies_imagens WHERE id = ? AND especie_id = ?");
            $stmt->execute([$imagem_id, $especie_id]);
            $img = $stmt->fetch();
            if ($img) {
                $_arq = realpath(__DIR__ . '/../../' . $img['caminho_imagem']);
                if ($_arq && $_raiz_uploads && str_starts_with($_arq, $_raiz_uploads)) unlink($_arq);
                $pdo->prepare("DELETE FROM especies_imagens WHERE id = ?")->execute([$imagem_id]);
                $pdo->prepare("
                    INSERT INTO historico_alteracoes
                        (especie_id, id_usuario, tabela_afetada, campo_alterado, valor_anterior, tipo_acao)
                    VALUES (?, ?, 'especies_imagens', 'caminho_imagem', ?, 'edicao')
                ")->execute([$especie_id, $usuario_id, $img['caminho_imagem']]);
                $msg = ['tipo' => 'ok', 'texto' => 'Imagem removida.'];
            }
        }
    }

    // ── Excluir dados morfológicos ────────────────
    elseif ($acao === 'excluir_dados') {
        $pdo->prepare("DELETE FROM especies_caracteristicas WHERE especie_id = ?")->execute([$especie_id]);
        // Artigo associado também perde sentido
        $pdo->prepare("DELETE FROM artigos WHERE especie_id = ?")->execute([$especie_id]);
        // Regride o status
        $pdo->prepare("UPDATE especies_administrativo SET status = 'sem_dados', data_ultima_atualizacao = NOW() WHERE id = ?")
            ->execute([$especie_id]);
        $pdo->prepare("
            INSERT INTO historico_alteracoes
                (especie_id, id_usuario, tabela_afetada, campo_alterado, valor_anterior, valor_novo, tipo_acao)
            VALUES (?, ?, 'especies_caracteristicas', NULL, 'dados_existentes', NULL, 'edicao')
        ")->execute([$especie_id, $usuario_id]);
        $msg = ['tipo' => 'ok', 'texto' => 'Dados morfológicos e artigo excluídos. Status revertido para sem_dados.'];
    }

    // ── Excluir todas as imagens ──────────────────
    elseif ($acao === 'excluir_imagens') {
        $stmt = $pdo->prepare("SELECT caminho_imagem FROM especies_imagens WHERE especie_id = ?");
        $stmt->execute([$especie_id]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $caminho) {
            $_arq = realpath(__DIR__ . '/../../' . $caminho);
            if ($_arq && $_raiz_uploads && str_starts_with($_arq, $_raiz_uploads)) unlink($_arq);
        }
        $pdo->prepare("DELETE FROM especies_imagens WHERE especie_id = ?")->execute([$especie_id]);
        $pdo->prepare("
            INSERT INTO historico_alteracoes
                (especie_id, id_usuario, tabela_afetada, campo_alterado, tipo_acao)
            VALUES (?, ?, 'especies_imagens', 'todas_imagens', 'edicao')
        ")->execute([$especie_id, $usuario_id]);
        $msg = ['tipo' => 'ok', 'texto' => 'Todas as imagens foram excluídas.'];
    }

    // ── Reverter último status ────────────────────
    elseif ($acao === 'reverter_status') {
        $stmt = $pdo->prepare("
            SELECT valor_anterior, valor_novo FROM historico_alteracoes
            WHERE especie_id = ? AND campo_alterado = 'status'
            ORDER BY data_alteracao DESC LIMIT 1
        ");
        $stmt->execute([$especie_id]);
        $row_hist = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row_hist && $row_hist['valor_anterior'] !== null) {
            $anterior = $row_hist['valor_anterior'];
            $pdo->prepare("UPDATE especies_administrativo SET status = ?, data_ultima_atualizacao = NOW() WHERE id = ?")
                ->execute([$anterior, $especie_id]);
            // Registra o revert sem apagar a evidência anterior
            $pdo->prepare("
                INSERT INTO historico_alteracoes
                    (especie_id, id_usuario, tabela_afetada, campo_alterado, valor_anterior, valor_novo, tipo_acao, justificativa)
                VALUES (?, ?, 'especies_administrativo', 'status', ?, ?, 'edicao', 'revert_gestor')
            ")->execute([$especie_id, $usuario_id, $row_hist['valor_novo'], $anterior]);
            $msg = ['tipo' => 'ok', 'texto' => "Status revertido para «$anterior»."];
        } else {
            $msg = ['tipo' => 'erro', 'texto' => 'Nenhuma alteração de status encontrada para reverter.'];
        }
    }

    // ── Excluir espécie completa ──────────────────
    elseif ($acao === 'excluir_tudo') {
        // Registra antes do delete (CASCADE apagaria qualquer entrada no histórico)
        $stmt_nome = $pdo->prepare("SELECT nome_cientifico, status FROM especies_administrativo WHERE id = ?");
        $stmt_nome->execute([$especie_id]);
        $esp_dados = $stmt_nome->fetch(PDO::FETCH_ASSOC);
        error_log(sprintf(
            '[GESTOR_AUDIT] excluir_especie | gestor_id=%d | especie_id=%d | nome=%s | status=%s | ip=%s',
            $usuario_id, $especie_id,
            $esp_dados['nome_cientifico'] ?? 'desconhecido',
            $esp_dados['status'] ?? 'desconhecido',
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ));
        // Arquivos físicos
        $stmt = $pdo->prepare("SELECT caminho_imagem FROM especies_imagens WHERE especie_id = ?");
        $stmt->execute([$especie_id]);
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $caminho) {
            $_arq = realpath(__DIR__ . '/../../' . $caminho);
            if ($_arq && $_raiz_uploads && str_starts_with($_arq, $_raiz_uploads)) unlink($_arq);
        }
        // ON DELETE CASCADE cuida das tabelas filhas
        $pdo->prepare("DELETE FROM especies_administrativo WHERE id = ?")->execute([$especie_id]);
        header('Location: gestao_especies.php?msg=excluido');
        exit;
    }

    // Redireciona para evitar resubmit
    header("Location: gerenciar_especie.php?id=$especie_id" . ($msg ? '&ok=1' : ''));
    exit;
}

// ================================================
// CARREGAR DADOS DA ESPÉCIE
// ================================================
$stmt = $pdo->prepare("
    SELECT e.*, u.nome AS nome_atribuido
    FROM especies_administrativo e
    LEFT JOIN usuarios u ON u.id = e.atribuido_a
    WHERE e.id = ?
");
$stmt->execute([$especie_id]);
$especie = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$especie) { header('Location: gestao_especies.php'); exit; }

// Dados morfológicos
$stmt = $pdo->prepare("SELECT * FROM especies_caracteristicas WHERE especie_id = ? LIMIT 1");
$stmt->execute([$especie_id]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

// Imagens
$stmt = $pdo->prepare("
    SELECT id, parte_planta, caminho_imagem, origem, data_upload, autor_imagem
    FROM especies_imagens WHERE especie_id = ?
    ORDER BY parte_planta, data_upload DESC
");
$stmt->execute([$especie_id]);
$imagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Histórico (últimas 10)
$stmt = $pdo->prepare("
    SELECT h.*, u.nome AS nome_usuario
    FROM historico_alteracoes h
    LEFT JOIN usuarios u ON u.id = h.id_usuario
    WHERE h.especie_id = ?
    ORDER BY h.data_alteracao DESC
    LIMIT 10
");
$stmt->execute([$especie_id]);
$historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tem histórico de status para reverter?
$stmt = $pdo->prepare("
    SELECT valor_anterior FROM historico_alteracoes
    WHERE especie_id = ? AND campo_alterado = 'status'
    ORDER BY data_alteracao DESC LIMIT 1
");
$stmt->execute([$especie_id]);
$pode_reverter = $stmt->fetchColumn();

$status_labels = [
    'sem_dados'      => 'Sem dados',
    'dados_internet' => 'Dados internet',
    'descrita'       => 'Confirmado',
    'registrada'     => 'Registrada',
    'em_revisao'     => 'Em revisão',
    'revisada'       => 'Revisada',
    'contestado'     => 'Contestado',
    'publicado'      => 'Publicado',
];

$campos_display = [
    'Folha'    => ['forma_folha','filotaxia_folha','tipo_folha','tamanho_folha','textura_folha','margem_folha','venacao_folha'],
    'Flor'     => ['cor_flores','simetria_floral','numero_petalas','disposicao_flores','tamanho_flor','aroma'],
    'Fruto'    => ['tipo_fruto','tamanho_fruto','cor_fruto','textura_fruto','dispersao_fruto','aroma_fruto'],
    'Semente'  => ['tipo_semente','tamanho_semente','cor_semente','textura_semente','quantidade_sementes'],
    'Caule'    => ['tipo_caule','estrutura_caule','textura_caule','cor_caule','forma_caule','diametro_caule','ramificacao_caule','modificacao_caule'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar — <?= htmlspecialchars($especie['nome_cientifico']) ?></title>
    <link rel="stylesheet" href="<?= APP_BASE ?>/assets/css/estilo.css">
    <style>
        body { background: #f0f4f0; padding: 24px 20px; }
        .container { max-width: 1000px; margin: 0 auto; }

        /* Header */
        .pg-header {
            background: var(--cor-primaria);
            color: white;
            padding: 18px 28px;
            border-radius: 10px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .pg-header h1 { font-size: 1.1rem; font-weight: 700; font-style: italic; }
        .pg-header .sub { font-size: .8rem; opacity: .8; margin-top: 3px; }
        .btn-voltar {
            background: rgba(255,255,255,.18);
            color: white;
            text-decoration: none;
            padding: 7px 18px;
            border-radius: 30px;
            font-size: .85rem;
            font-weight: 600;
            white-space: nowrap;
            transition: background .2s;
        }
        .btn-voltar:hover { background: rgba(255,255,255,.32); }

        /* Alertas */
        .alerta {
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: .9rem;
            font-weight: 600;
        }
        .alerta-ok   { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alerta-erro { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        /* Seções */
        .secao {
            background: white;
            border-radius: 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,.07);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .secao-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            border-bottom: 1px solid #e8f0e8;
            background: #f7faf7;
        }
        .secao-titulo {
            font-weight: 700;
            font-size: .95rem;
            color: var(--cor-primaria);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .secao-body { padding: 20px; }

        /* Status */
        .status-form { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .status-form select {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: .9rem;
            background: white;
            flex: 1;
            min-width: 180px;
        }
        .badge-status {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: .82rem;
            font-weight: 700;
        }
        .st-sem_dados      { background:#f0f0f0; color:#666; }
        .st-dados_internet { background:#cfe2ff; color:#084298; }
        .st-descrita       { background:#d1ecf1; color:#0c5460; }
        .st-registrada     { background:#d4edda; color:#155724; }
        .st-em_revisao     { background:#fff3cd; color:#856404; }
        .st-revisada       { background:#c3e6cb; color:#155724; }
        .st-contestado     { background:#f8d7da; color:#721c24; }
        .st-publicado      { background:var(--cor-primaria); color:white; }

        /* Dados morfológicos */
        .dados-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 12px;
        }
        .dados-grupo { }
        .dados-grupo-titulo {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #94a3b8;
            margin-bottom: 6px;
        }
        .dado-item {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px solid #f1f5f1;
            font-size: .83rem;
        }
        .dado-label { color: #64748b; }
        .dado-valor { font-weight: 600; color: #1e293b; text-align: right; max-width: 60%; }
        .sem-dados-msg { color: #94a3b8; font-size: .9rem; font-style: italic; }

        /* Imagens */
        .imgs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 12px;
        }
        .img-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            background: #fafafa;
            position: relative;
        }
        .img-card img {
            width: 100%;
            height: 110px;
            object-fit: cover;
            display: block;
        }
        .img-info {
            padding: 6px 8px;
            font-size: .72rem;
            color: #64748b;
        }
        .img-parte {
            font-weight: 700;
            font-size: .75rem;
            color: #334155;
            text-transform: capitalize;
        }
        .img-origem {
            background: #f1f5f9;
            border-radius: 4px;
            padding: 1px 5px;
            font-size: .68rem;
        }
        .btn-excluir-img {
            position: absolute;
            top: 6px;
            right: 6px;
            background: #b91c1c;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            font-size: .85rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s;
            box-shadow: 0 1px 4px rgba(0,0,0,.3);
        }
        .btn-excluir-img:hover { background: #7f1d1d; }

        /* Botões de ação — alto contraste (acessível para daltonismo) */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 20px;
            border-radius: 8px;
            font-size: .88rem;
            font-weight: 700;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: .15s;
            letter-spacing: .01em;
        }
        .btn-primario   { background: var(--cor-primaria); color: #fff; }
        .btn-primario:hover { background: var(--cor-primaria-hover); }
        .btn-secundario { background: #334155; color: #fff; }
        .btn-secundario:hover { background: #1e293b; }
        .btn-aviso      { background: #b45309; color: #fff; }
        .btn-aviso:hover { background: #92400e; }
        .btn-perigo     { background: #b91c1c; color: #fff; }
        .btn-perigo:hover { background: #991b1b; }

        /* Zona de perigo */
        .zona-perigo { border: 1px solid #fca5a5; border-radius: 10px; overflow: hidden; }
        .zona-perigo .secao-header { background: #fff1f2; border-bottom-color: #fca5a5; }
        .zona-perigo .secao-titulo { color: #991b1b; }
        .perigo-acoes { display: flex; gap: 12px; flex-wrap: wrap; padding: 20px; }
        .perigo-desc { font-size: .78rem; color: #64748b; margin-top: 4px; }

        /* Histórico */
        .hist-item {
            display: flex;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f1;
            font-size: .82rem;
        }
        .hist-item:last-child { border-bottom: none; }
        .hist-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--cor-primaria);
            margin-top: 5px;
            flex-shrink: 0;
        }
        .hist-data { color: #94a3b8; white-space: nowrap; }
        .hist-usuario { font-weight: 600; color: #334155; }
        .hist-detalhe { color: #64748b; }
        .sem-hist { color: #94a3b8; font-size: .85rem; font-style: italic; }
    </style>
</head>
<body>
<div class="container">

    <!-- Header -->
    <div class="pg-header">
        <div>
            <div class="pg-header h1" style="font-size:1.1rem;font-weight:700;font-style:italic;">
                <?= htmlspecialchars($especie['nome_cientifico']) ?>
            </div>
            <div class="sub">
                ID #<?= $especie_id ?> &nbsp;·&nbsp;
                <span class="badge-status st-<?= $especie['status'] ?>">
                    <?= $status_labels[$especie['status']] ?? $especie['status'] ?>
                </span>
            </div>
        </div>
        <a href="gestao_especies.php" class="btn-voltar">← Gestão de Espécies</a>
    </div>

    <?php if (isset($_GET['ok'])): ?>
        <?php
        // Mensagem salva na sessão antes do redirect
        $txt = $_SESSION['msg_gerenciar'] ?? '';
        unset($_SESSION['msg_gerenciar']);
        ?>
    <?php endif; ?>

    <!-- ══ 1. MUDAR STATUS ══════════════════════════════════════════ -->
    <div class="secao">
        <div class="secao-header">
            <div class="secao-titulo">🔄 Status da Espécie</div>
        </div>
        <div class="secao-body">
            <form method="POST" class="status-form">
                <input type="hidden" name="especie_id" value="<?= $especie_id ?>">
                <input type="hidden" name="acao" value="mudar_status">
                <select name="novo_status">
                    <?php foreach ($status_labels as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $especie['status'] === $val ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primario">Aplicar</button>
            </form>
        </div>
    </div>

    <!-- ══ 2. DADOS MORFOLÓGICOS ════════════════════════════════════ -->
    <div class="secao">
        <div class="secao-header">
            <div class="secao-titulo">🌿 Dados Morfológicos</div>
            <div style="display:flex;gap:8px;">
                <?php if ($dados): ?>
                <a href="confirmar_caracteristicas.php?especie_id=<?= $especie_id ?>" class="btn btn-secundario">
                    ✏️ Editar
                </a>
                <form method="POST" onsubmit="return confirm('Excluir todos os dados morfológicos e o artigo? O status voltará para sem_dados.');">
                    <input type="hidden" name="especie_id" value="<?= $especie_id ?>">
                    <input type="hidden" name="acao" value="excluir_dados">
                    <button type="submit" class="btn btn-perigo">🗑 Excluir dados</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <div class="secao-body">
            <?php if (!$dados): ?>
                <p class="sem-dados-msg">Nenhum dado morfológico cadastrado para esta espécie.</p>
            <?php else: ?>
                <div class="dados-grid">
                    <?php foreach ($campos_display as $grupo => $campos): ?>
                    <?php
                        $tem = false;
                        foreach ($campos as $c) { if (!empty($dados[$c])) { $tem = true; break; } }
                        if (!$tem) continue;
                    ?>
                    <div class="dados-grupo">
                        <div class="dados-grupo-titulo"><?= $grupo ?></div>
                        <?php foreach ($campos as $campo): ?>
                            <?php if (empty($dados[$campo])) continue; ?>
                            <div class="dado-item">
                                <span class="dado-label"><?= ucfirst(str_replace('_', ' ', str_replace("_{$grupo}", '', $campo))) ?></span>
                                <span class="dado-valor"><?= htmlspecialchars($dados[$campo]) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>

                    <?php if (!empty($dados['possui_espinhos']) || !empty($dados['possui_latex'])): ?>
                    <div class="dados-grupo">
                        <div class="dados-grupo-titulo">Características</div>
                        <?php if (!empty($dados['possui_espinhos'])): ?>
                        <div class="dado-item">
                            <span class="dado-label">Espinhos</span>
                            <span class="dado-valor"><?= htmlspecialchars($dados['possui_espinhos']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($dados['possui_latex'])): ?>
                        <div class="dado-item">
                            <span class="dado-label">Látex</span>
                            <span class="dado-valor"><?= htmlspecialchars($dados['possui_latex']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══ 3. IMAGENS ═══════════════════════════════════════════════ -->
    <div class="secao">
        <div class="secao-header">
            <div class="secao-titulo">🖼 Imagens (<?= count($imagens) ?>)</div>
            <?php if ($imagens): ?>
            <form method="POST" onsubmit="return confirm('Excluir TODAS as imagens desta espécie?');">
                <input type="hidden" name="especie_id" value="<?= $especie_id ?>">
                <input type="hidden" name="acao" value="excluir_imagens">
                <button type="submit" class="btn btn-perigo">🗑 Excluir todas</button>
            </form>
            <?php endif; ?>
        </div>
        <div class="secao-body">
            <?php if (!$imagens): ?>
                <p class="sem-dados-msg">Nenhuma imagem cadastrada.</p>
            <?php else: ?>
                <div class="imgs-grid">
                    <?php foreach ($imagens as $img): ?>
                    <div class="img-card">
                        <img src="<?= APP_BASE ?>/<?= htmlspecialchars($img['caminho_imagem']) ?>"
                             alt="<?= htmlspecialchars($img['parte_planta']) ?>"
                             onerror="this.src='<?= APP_BASE ?>/assets/img/placeholder.png'">
                        <form method="POST" onsubmit="return confirm('Remover esta imagem?');" style="position:absolute;top:6px;right:6px;">
                            <input type="hidden" name="especie_id" value="<?= $especie_id ?>">
                            <input type="hidden" name="acao" value="excluir_imagem">
                            <input type="hidden" name="imagem_id" value="<?= $img['id'] ?>">
                            <button type="submit" class="btn-excluir-img" title="Remover imagem">✕</button>
                        </form>
                        <div class="img-info">
                            <div class="img-parte"><?= htmlspecialchars($img['parte_planta']) ?></div>
                            <span class="img-origem"><?= htmlspecialchars($img['origem']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══ 4. ZONA DE PERIGO ════════════════════════════════════════ -->
    <div class="secao zona-perigo">
        <div class="secao-header">
            <div class="secao-titulo">⚠️ Zona de Perigo</div>
        </div>
        <div class="perigo-acoes">

            <!-- Reverter status -->
            <?php if ($pode_reverter): ?>
            <div>
                <form method="POST" onsubmit="return confirm('Reverter para o status anterior: «<?= htmlspecialchars($pode_reverter) ?>»?');">
                    <input type="hidden" name="especie_id" value="<?= $especie_id ?>">
                    <input type="hidden" name="acao" value="reverter_status">
                    <button type="submit" class="btn btn-aviso">↩ Reverter status → «<?= $status_labels[$pode_reverter] ?? $pode_reverter ?>»</button>
                </form>
                <div class="perigo-desc">Desfaz a última alteração de status registrada aqui.</div>
            </div>
            <?php else: ?>
            <div>
                <button class="btn btn-aviso" disabled style="opacity:.45;">↩ Sem status para reverter</button>
                <div class="perigo-desc">Nenhuma alteração de status registrada nesta sessão.</div>
            </div>
            <?php endif; ?>

            <!-- Excluir tudo -->
            <div>
                <form method="POST" onsubmit="return confirm('ATENÇÃO: isso apagará permanentemente a espécie, todos os dados, imagens e histórico. Confirma?');">
                    <input type="hidden" name="especie_id" value="<?= $especie_id ?>">
                    <input type="hidden" name="acao" value="excluir_tudo">
                    <button type="submit" class="btn btn-perigo">💥 Excluir espécie completa</button>
                </form>
                <div class="perigo-desc">Remove a espécie e todos os dados associados. Irreversível.</div>
            </div>

        </div>
    </div>

    <!-- ══ 5. HISTÓRICO ═════════════════════════════════════════════ -->
    <div class="secao">
        <div class="secao-header">
            <div class="secao-titulo">📋 Histórico de Alterações</div>
        </div>
        <div class="secao-body">
            <?php if (!$historico): ?>
                <p class="sem-hist">Nenhuma alteração registrada ainda.</p>
            <?php else: ?>
                <?php foreach ($historico as $h): ?>
                <div class="hist-item">
                    <div class="hist-dot"></div>
                    <div style="flex:1;">
                        <span class="hist-usuario"><?= htmlspecialchars($h['nome_usuario'] ?? 'Sistema') ?></span>
                        <span class="hist-detalhe">
                            · <?= htmlspecialchars($h['tipo_acao']) ?>
                            <?php if ($h['campo_alterado'] === 'status'): ?>
                                status: <strong><?= htmlspecialchars($h['valor_anterior']) ?></strong>
                                → <strong><?= htmlspecialchars($h['valor_novo']) ?></strong>
                            <?php elseif ($h['campo_alterado']): ?>
                                · <?= htmlspecialchars($h['campo_alterado']) ?>
                            <?php endif; ?>
                        </span>
                        <div class="hist-data"><?= date('d/m/Y H:i', strtotime($h['data_alteracao'])) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>
</body>
</html>
