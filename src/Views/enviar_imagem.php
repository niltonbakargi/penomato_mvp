<?php
// ============================================================
// ENVIO DE EXSICATAS — FOTOS DE CAMPO
// ============================================================
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /penomato_mvp/src/Views/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$usuario_id   = (int)$_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';

$especie_id  = isset($_GET['especie_id'])  ? (int)$_GET['especie_id']  : 0;
$exemplar_id = isset($_GET['exemplar_id']) ? (int)$_GET['exemplar_id'] : 0;

// ── Espécies disponíveis ──────────────────────────────────────────────────────
$especies = $pdo->query("
    SELECT id, nome_cientifico, status
    FROM especies_administrativo
    WHERE status NOT IN ('publicado')
    ORDER BY nome_cientifico
")->fetchAll();

// ── Dados da espécie selecionada ──────────────────────────────────────────────
$especie    = null;
$exemplares = [];
$exemplar   = null;

$partes_config = [
    'folha'   => ['icone' => '🍃', 'nome' => 'Folha'],
    'flor'    => ['icone' => '🌸', 'nome' => 'Flor'],
    'fruto'   => ['icone' => '🍎', 'nome' => 'Fruto'],
    'caule'   => ['icone' => '🌿', 'nome' => 'Caule'],
    'semente' => ['icone' => '🌱', 'nome' => 'Semente'],
    'habito'  => ['icone' => '🌳', 'nome' => 'Hábito'],
];

$partes_status = [];
$notificacao   = null;
$fotos_campo   = [];

if ($especie_id > 0) {

    // Dados da espécie
    $stmt = $pdo->prepare("
        SELECT id, nome_cientifico, status, data_descrita, data_registrada
        FROM especies_administrativo WHERE id = ?
    ");
    $stmt->execute([$especie_id]);
    $especie = $stmt->fetch();

    if ($especie) {

        // Exemplares desta espécie (todos os status)
        $stmt = $pdo->prepare("
            SELECT ex.id, ex.codigo, ex.status, ex.numero_etiqueta,
                   ex.cidade, ex.estado, ex.bioma, ex.data_cadastro,
                   ex.foto_identificacao, ex.motivo_rejeicao,
                   u.nome AS especialista_nome
            FROM exemplares ex
            JOIN usuarios u ON u.id = ex.especialista_id
            WHERE ex.especie_id = ?
            ORDER BY
                CASE ex.status
                    WHEN 'aprovado'           THEN 1
                    WHEN 'aguardando_revisao' THEN 2
                    WHEN 'rejeitado'          THEN 3
                END,
                ex.data_cadastro DESC
        ");
        $stmt->execute([$especie_id]);
        $exemplares = $stmt->fetchAll();

        // ── Exemplar selecionado ──────────────────────────────────────────────
        if ($exemplar_id > 0) {
            foreach ($exemplares as $ex) {
                if ($ex['id'] === $exemplar_id) { $exemplar = $ex; break; }
            }

            if ($exemplar && $exemplar['status'] === 'aprovado') {

                // Fotos de campo por parte deste exemplar
                $stmt = $pdo->prepare("
                    SELECT parte_planta, COUNT(*) as total
                    FROM especies_imagens
                    WHERE exemplar_id = ? AND origem = 'campo'
                    GROUP BY parte_planta
                ");
                $stmt->execute([$exemplar_id]);
                $contagem = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                // Partes dispensadas (por espécie)
                $stmt = $pdo->prepare("
                    SELECT parte_planta, motivo FROM partes_dispensadas
                    WHERE especie_id = ?
                ");
                $stmt->execute([$especie_id]);
                $dispensadas = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

                foreach ($partes_config as $key => $_) {
                    if (isset($dispensadas[$key])) {
                        $partes_status[$key] = ['status' => 'dispensada', 'total' => 0, 'motivo' => $dispensadas[$key]];
                    } elseif (!empty($contagem[$key])) {
                        $partes_status[$key] = ['status' => 'fotografada', 'total' => (int)$contagem[$key]];
                    } else {
                        $partes_status[$key] = ['status' => 'pendente', 'total' => 0];
                    }
                }

                $todas_completas = !in_array('pendente', array_column($partes_status, 'status'));
                $identificada    = !empty($especie['data_descrita']);

                if ($todas_completas && !$identificada) {
                    $notificacao = ['tipo' => 'warning',
                        'msg' => 'Todas as partes foram fotografadas. Para gerar o artigo, os atributos da internet ainda precisam ser confirmados.'];
                } elseif ($todas_completas && $identificada) {
                    $notificacao = ['tipo' => 'success',
                        'msg' => 'Exemplar completo e espécie identificada — o artigo pode ser gerado!'];
                } elseif ($identificada) {
                    $pendentes = array_keys(array_filter($partes_status, fn($p) => $p['status'] === 'pendente'));
                    $notificacao = ['tipo' => 'info',
                        'msg' => 'Espécie identificada. Partes pendentes: ' . implode(', ', array_map('ucfirst', $pendentes)) . '.'];
                }

                // Fotos para galeria
                $stmt = $pdo->prepare("
                    SELECT id, parte_planta, caminho_imagem,
                           data_coleta, coletor_nome, licenca,
                           status_validacao, data_upload
                    FROM especies_imagens
                    WHERE exemplar_id = ? AND origem = 'campo'
                    ORDER BY parte_planta, data_upload DESC
                ");
                $stmt->execute([$exemplar_id]);
                $fotos_campo = $stmt->fetchAll();
            }
        }
    }
}

$status_labels = [
    'sem_dados'      => ['label' => 'Sem dados',      'cor' => '#94a3b8'],
    'dados_internet' => ['label' => 'Dados internet', 'cor' => '#3b82f6'],
    'descrita'       => ['label' => 'Identificada',   'cor' => '#8b5cf6'],
    'registrada'     => ['label' => 'Registrada',     'cor' => '#f59e0b'],
    'em_revisao'     => ['label' => 'Em revisão',     'cor' => '#ef4444'],
    'revisada'       => ['label' => 'Revisada',       'cor' => '#10b981'],
    'publicado'      => ['label' => 'Publicada',      'cor' => '#0b5e42'],
];

$mensagem_sucesso = isset($_GET['sucesso']) ? urldecode($_GET['sucesso']) : '';
$mensagem_erro    = isset($_GET['erro'])    ? urldecode($_GET['erro'])    : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exsicatas — Penomato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --verde:        #0b5e42;
            --verde-escuro: #0a4c35;
            --verde-claro:  #e8f5e9;
            --fundo:        #f5f2e9;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; background:var(--fundo); color:#2c3e50; padding:30px 20px; }
        .container { max-width:1100px; margin:0 auto; }

        /* Cabeçalho */
        .cabecalho {
            background:white; padding:26px 36px; border-radius:12px 12px 0 0;
            border-bottom:4px solid var(--verde);
            display:flex; justify-content:space-between; align-items:center;
            flex-wrap:wrap; gap:12px;
        }
        .cabecalho h1 { color:var(--verde); font-size:1.7rem; font-weight:600; }
        .cabecalho .sub { color:#666; font-style:italic; font-size:.88rem; margin-top:3px; }
        .user-pill { background:#f8f9fa; padding:7px 16px; border-radius:40px; display:flex; align-items:center; gap:9px; font-size:.88rem; }
        .user-pill i { color:var(--verde); }
        .btn-sair { color:#dc3545; text-decoration:none; padding:3px 8px; border-radius:20px; transition:.2s; }
        .btn-sair:hover { background:#dc3545; color:white; }

        /* Card */
        .card-principal { background:white; padding:30px 36px; border-radius:0 0 12px 12px; box-shadow:0 4px 15px rgba(0,0,0,.05); }

        /* Seletor */
        .seletor-box { background:#f8fafc; border:2px solid #e2e8f0; border-radius:10px; padding:20px 24px; margin-bottom:22px; }
        .seletor-box h3 { color:var(--verde); margin-bottom:12px; font-size:.95rem; font-weight:700; }
        .seletor-row { display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; }
        .seletor-row .fg { flex:1; min-width:240px; }
        .seletor-row label { font-weight:600; font-size:.875rem; margin-bottom:6px; display:block; }
        .seletor-row select { width:100%; padding:10px 14px; border:2px solid #e2e8f0; border-radius:8px; font-size:.95rem; }
        .seletor-row select:focus { outline:none; border-color:var(--verde); }
        .btn-carregar { background:var(--verde); color:white; border:none; padding:10px 24px; border-radius:40px; font-weight:600; cursor:pointer; white-space:nowrap; transition:.2s; }
        .btn-carregar:hover { background:var(--verde-escuro); }

        /* Alertas */
        .alerta { padding:13px 16px; border-radius:8px; margin-bottom:18px; display:flex; align-items:flex-start; gap:10px; font-size:.9rem; }
        .alerta-success { background:#d4edda; color:#155724; border-left:4px solid #28a745; }
        .alerta-danger  { background:#f8d7da; color:#721c24; border-left:4px solid #dc3545; }
        .alerta-warning { background:#fff3cd; color:#856404; border-left:4px solid #ffc107; }
        .alerta-info    { background:#d1ecf1; color:#0c5460; border-left:4px solid #17a2b8; }

        /* Banner espécie */
        .especie-banner { background:var(--verde-claro); border-radius:10px; padding:16px 22px; margin-bottom:22px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; }
        .especie-nome { font-size:1.25rem; font-weight:600; color:var(--verde); font-style:italic; }
        .badge-status { padding:4px 12px; border-radius:20px; font-size:.75rem; font-weight:700; color:white; }

        /* ── PASSO 2: Seleção de exemplar ─── */
        .exemplares-titulo { font-size:.95rem; font-weight:700; color:#444; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
        .exemplares-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:14px; margin-bottom:28px; }

        .ex-card {
            border:2px solid #e2e8f0; border-radius:10px; overflow:hidden;
            cursor:pointer; transition:.2s; text-decoration:none; color:inherit; display:block;
        }
        .ex-card:hover { border-color:var(--verde); box-shadow:0 4px 10px rgba(0,0,0,.08); }
        .ex-card.selecionado { border-color:var(--verde); box-shadow:0 0 0 3px rgba(11,94,66,.2); }
        .ex-card.aguardando  { border-left:4px solid #f59e0b; cursor:default; opacity:.8; }
        .ex-card.rejeitado   { border-left:4px solid #dc3545; cursor:default; opacity:.7; }

        .ex-foto { height:100px; background:#f0f0f0; overflow:hidden; }
        .ex-foto img { width:100%; height:100%; object-fit:cover; }
        .ex-foto-placeholder { height:100px; background:#f8fafc; display:flex; align-items:center; justify-content:center; color:#ccc; font-size:1.8rem; }

        .ex-body { padding:12px 14px; }
        .ex-codigo { font-family:'Courier New',monospace; font-size:1.1rem; font-weight:900; color:var(--verde); letter-spacing:2px; }
        .ex-local { font-size:.78rem; color:#888; margin-top:3px; }
        .ex-pill { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.7rem; font-weight:700; color:white; margin-top:6px; }
        .pill-ap { background:var(--verde); }
        .pill-ag { background:#f59e0b; }
        .pill-rj { background:#dc3545; }

        .btn-novo-exemplar {
            border:2px dashed #c3d4e0; border-radius:10px; padding:24px 14px;
            text-align:center; cursor:pointer; text-decoration:none; color:#888;
            display:flex; flex-direction:column; align-items:center; gap:8px;
            transition:.2s; background:#fafcfe;
        }
        .btn-novo-exemplar:hover { border-color:var(--verde); color:var(--verde); background:var(--verde-claro); }
        .btn-novo-exemplar i { font-size:1.8rem; }
        .btn-novo-exemplar span { font-size:.85rem; font-weight:600; }

        /* Exemplar selecionado — banner */
        .exemplar-banner {
            background:#fff8e6; border:2px solid #f59e0b; border-radius:10px;
            padding:14px 20px; margin-bottom:20px;
            display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;
        }
        .exemplar-banner .ex-cod { font-family:'Courier New',monospace; font-size:1.2rem; font-weight:900; color:#92400e; letter-spacing:2px; }
        .exemplar-banner .ex-det { font-size:.85rem; color:#666; }
        .btn-trocar { background:none; border:1px solid #d0c090; padding:5px 14px; border-radius:20px; font-size:.8rem; color:#666; cursor:pointer; text-decoration:none; transition:.2s; }
        .btn-trocar:hover { background:#f0e8d0; color:#333; }

        /* Partes */
        .partes-titulo { font-size:.95rem; font-weight:700; color:#444; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
        .partes-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(155px,1fr)); gap:12px; margin-bottom:32px; }
        .parte-card { border-radius:10px; border:2px solid #e2e8f0; padding:18px 12px; text-align:center; cursor:pointer; transition:.2s; background:#fafafa; }
        .parte-card:hover { border-color:var(--verde); box-shadow:0 4px 10px rgba(0,0,0,.08); transform:translateY(-2px); }
        .parte-card.fotografada { border-color:#28a745; background:#f0fff4; }
        .parte-card.dispensada  { border-color:#ffc107; background:#fffdf0; opacity:.8; cursor:default; }
        .parte-icone { font-size:1.9rem; margin-bottom:7px; }
        .parte-nome  { font-weight:700; font-size:.88rem; margin-bottom:6px; }
        .parte-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.7rem; font-weight:700; }
        .badge-pendente    { background:#e2e8f0; color:#555; }
        .badge-fotografada { background:#d4edda; color:#155724; }
        .badge-dispensada  { background:#fff3cd; color:#856404; }
        .parte-total { font-size:.75rem; color:#888; margin-top:3px; }

        /* Filtros imagem */
        .filtros-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:13px 16px; margin-bottom:16px; }
        .filtros-box h5 { font-size:.83rem; font-weight:700; color:#555; margin-bottom:9px; }
        .filtros-row { display:flex; gap:14px; flex-wrap:wrap; align-items:center; }
        .filtro-item { display:flex; flex-direction:column; gap:3px; min-width:120px; }
        .filtro-item label { font-size:.73rem; color:#666; font-weight:600; }
        .filtro-item input[type=range] { width:100%; accent-color:var(--verde); }
        .btn-rst { background:none; border:1px solid #c0c0c0; padding:4px 12px; border-radius:20px; font-size:.76rem; cursor:pointer; color:#555; transition:.2s; }
        .btn-rst:hover { background:#eee; }

        /* Galeria */
        .galeria-titulo { font-size:.95rem; font-weight:700; color:#444; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
        .fotos-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:14px; }
        .foto-card { border:2px solid #e2e8f0; border-radius:8px; overflow:hidden; background:white; transition:.2s; }
        .foto-card:hover { box-shadow:0 4px 10px rgba(0,0,0,.1); }
        .foto-preview { height:130px; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#f8fafc; }
        .foto-preview img { width:100%; height:100%; object-fit:cover; transition:filter .15s; }
        .foto-info { padding:10px 12px; }
        .foto-parte { font-weight:700; color:var(--verde); font-size:.83rem; }
        .foto-meta  { font-size:.76rem; color:#666; margin-top:3px; line-height:1.4; }
        .foto-val   { display:inline-block; padding:2px 8px; border-radius:10px; font-size:.68rem; font-weight:700; margin-top:5px; }
        .val-pendente  { background:#fff3cd; color:#856404; }
        .val-aprovado  { background:#d4edda; color:#155724; }
        .val-rejeitado { background:#f8d7da; color:#721c24; }

        /* Placeholder */
        .placeholder-box { text-align:center; padding:56px 20px; background:#f8fafc; border-radius:10px; color:#718096; }
        .placeholder-box i { font-size:3.2rem; color:#cbd5e0; margin-bottom:14px; display:block; }

        /* Rodapé */
        .rodape-botoes { display:flex; justify-content:center; margin-top:32px; }
        .btn-voltar { background:#6c757d; color:white; text-decoration:none; padding:11px 28px; border-radius:40px; font-weight:600; display:inline-flex; align-items:center; gap:8px; transition:.2s; }
        .btn-voltar:hover { background:#5a6268; color:white; }

        /* Modal upload */
        .overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:1000; align-items:center; justify-content:center; }
        .overlay.aberto { display:flex; }
        .modal-upload { background:white; border-radius:12px; padding:30px 34px; width:100%; max-width:480px; box-shadow:0 20px 40px rgba(0,0,0,.2); position:relative; max-height:90vh; overflow-y:auto; }
        .modal-upload h3 { color:var(--verde); margin-bottom:20px; font-size:1.1rem; }
        .modal-fechar { position:absolute; top:14px; right:18px; background:none; border:none; font-size:1.4rem; color:#999; cursor:pointer; }
        .modal-fechar:hover { color:#333; }
        .campo { margin-bottom:15px; }
        .campo label { display:block; font-weight:600; font-size:.875rem; margin-bottom:6px; color:#2d3748; }
        .campo label .req { color:#dc3545; }
        .campo input, .campo select, .campo textarea { width:100%; padding:9px 13px; border:2px solid #e2e8f0; border-radius:8px; font-size:.93rem; }
        .campo input:focus, .campo select:focus, .campo textarea:focus { outline:none; border-color:var(--verde); }
        .campo textarea { resize:vertical; min-height:65px; }
        .drop-zone { border:2px dashed #c3d4e0; border-radius:8px; padding:26px 16px; text-align:center; cursor:pointer; transition:.2s; background:#fafcfe; }
        .drop-zone:hover,.drop-zone.sobre { border-color:var(--verde); background:var(--verde-claro); }
        .drop-zone i { font-size:1.9rem; color:#aaa; display:block; margin-bottom:7px; }
        .drop-zone p { font-size:.83rem; color:#666; margin:0; }
        .drop-zone .arq-nome { font-size:.83rem; color:var(--verde); font-weight:600; margin-top:7px; display:none; }
        #input-arquivo { display:none; }
        .btn-enviar { width:100%; background:var(--verde); color:white; border:none; padding:12px; border-radius:8px; font-size:.97rem; font-weight:700; cursor:pointer; margin-top:6px; transition:.2s; }
        .btn-enviar:hover { background:var(--verde-escuro); }
        .btn-enviar:disabled { opacity:.6; cursor:not-allowed; }

        /* Chip do exemplar no modal */
        .exemplar-chip { background:#fff8e6; border:1px solid #f59e0b; border-radius:8px; padding:8px 14px; margin-bottom:16px; font-size:.85rem; color:#92400e; display:flex; align-items:center; gap:8px; }
        .exemplar-chip strong { font-family:'Courier New',monospace; font-size:1rem; letter-spacing:1px; }
    </style>
</head>
<body>
<div class="container">

    <div class="cabecalho">
        <div>
            <h1>📸 Exsicatas</h1>
            <div class="sub">Registro fotográfico de campo por partes do exemplar</div>
        </div>
        <div class="user-pill">
            <i class="fas fa-user-circle"></i>
            <span><?= htmlspecialchars($usuario_nome) ?></span>
            <a href="/penomato_mvp/src/Controllers/auth/logout_controlador.php"
               class="btn-sair" onclick="return confirm('Deseja sair?')">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>

    <div class="card-principal">

        <?php if ($mensagem_sucesso): ?>
            <div class="alerta alerta-success"><i class="fas fa-check-circle"></i><span><?= htmlspecialchars($mensagem_sucesso) ?></span></div>
        <?php endif; ?>
        <?php if ($mensagem_erro): ?>
            <div class="alerta alerta-danger"><i class="fas fa-exclamation-circle"></i><span><?= htmlspecialchars($mensagem_erro) ?></span></div>
        <?php endif; ?>

        <!-- PASSO 1: Selecionar espécie -->
        <div class="seletor-box">
            <h3><i class="fas fa-tree" style="margin-right:6px"></i> Passo 1 — Selecione a espécie</h3>
            <form class="seletor-row" method="GET" action="">
                <div class="fg">
                    <label>Espécie</label>
                    <select name="especie_id" required>
                        <option value="">— selecione —</option>
                        <?php foreach ($especies as $esp): ?>
                            <option value="<?= $esp['id'] ?>"
                                <?= $especie_id == $esp['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($esp['nome_cientifico']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-carregar">
                    <i class="fas fa-arrow-right"></i> Carregar
                </button>
            </form>
        </div>

        <?php if ($especie): ?>

            <!-- Banner da espécie -->
            <div class="especie-banner">
                <span class="especie-nome"><?= htmlspecialchars($especie['nome_cientifico']) ?></span>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <?php $sc = $status_labels[$especie['status']] ?? ['label'=>$especie['status'],'cor'=>'#888']; ?>
                    <span class="badge-status" style="background:<?= $sc['cor'] ?>"><?= $sc['label'] ?></span>
                    <?php if ($especie['data_descrita']): ?>
                        <span class="badge-status" style="background:#8b5cf6">✓ Identificada</span>
                    <?php endif; ?>
                    <?php if ($especie['data_registrada']): ?>
                        <span class="badge-status" style="background:#f59e0b">✓ Registrada</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!$exemplar_id || !$exemplar || $exemplar['status'] !== 'aprovado'): ?>

            <!-- PASSO 2: Selecionar exemplar -->
            <div class="exemplares-titulo">
                <i class="fas fa-seedling" style="color:var(--verde)"></i>
                Passo 2 — Selecione o exemplar de campo
            </div>

            <div class="exemplares-grid">

                <?php foreach ($exemplares as $ex):
                    $st = $ex['status'];
                    $cls = $st === 'aprovado' ? '' : ($st === 'aguardando_revisao' ? 'aguardando' : 'rejeitado');
                    $pill_lbl = $st === 'aprovado' ? ['cls'=>'pill-ap','txt'=>'✅ Aprovado'] :
                               ($st === 'aguardando_revisao' ? ['cls'=>'pill-ag','txt'=>'⏳ Aguardando'] :
                                                               ['cls'=>'pill-rj','txt'=>'❌ Rejeitado']);
                ?>
                <?php if ($st === 'aprovado'): ?>
                    <a href="?especie_id=<?= $especie_id ?>&exemplar_id=<?= $ex['id'] ?>"
                       class="ex-card <?= $especie_id && $exemplar_id == $ex['id'] ? 'selecionado' : '' ?>">
                <?php else: ?>
                    <div class="ex-card <?= $cls ?>"
                         title="<?= $st === 'rejeitado' ? htmlspecialchars($ex['motivo_rejeicao'] ?? '') : 'Aguardando aprovação do especialista' ?>">
                <?php endif; ?>

                    <?php if ($ex['foto_identificacao']): ?>
                        <div class="ex-foto">
                            <img src="/penomato_mvp/<?= htmlspecialchars($ex['foto_identificacao']) ?>"
                                 alt="Exemplar <?= htmlspecialchars($ex['codigo']) ?>"
                                 onerror="this.parentElement.className='ex-foto-placeholder';this.parentElement.innerHTML='<i class=fas fa-image></i>'">
                        </div>
                    <?php else: ?>
                        <div class="ex-foto-placeholder"><i class="fas fa-image"></i></div>
                    <?php endif; ?>

                    <div class="ex-body">
                        <div class="ex-codigo"><?= htmlspecialchars($ex['codigo']) ?></div>
                        <div class="ex-local">
                            <?= htmlspecialchars($ex['cidade']) ?> / <?= htmlspecialchars($ex['estado']) ?>
                            <?= $ex['numero_etiqueta'] ? ' · Etiq. ' . htmlspecialchars($ex['numero_etiqueta']) : '' ?>
                        </div>
                        <span class="ex-pill <?= $pill_lbl['cls'] ?>"><?= $pill_lbl['txt'] ?></span>
                    </div>

                <?php if ($st === 'aprovado'): ?></a><?php else: ?></div><?php endif; ?>
                <?php endforeach; ?>

                <!-- Botão novo exemplar -->
                <a href="/penomato_mvp/src/Views/cadastrar_exemplar.php?especie_id=<?= $especie_id ?>"
                   class="btn-novo-exemplar">
                    <i class="fas fa-plus-circle"></i>
                    <span>Cadastrar novo exemplar</span>
                </a>

            </div>

            <?php if (empty($exemplares)): ?>
                <div class="alerta alerta-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Nenhum exemplar cadastrado para esta espécie. Cadastre um exemplar antes de enviar fotos.</span>
                </div>
            <?php elseif (!array_filter($exemplares, fn($e) => $e['status'] === 'aprovado')): ?>
                <div class="alerta alerta-info">
                    <i class="fas fa-info-circle"></i>
                    <span>Os exemplares cadastrados ainda aguardam revisão do especialista. Assim que aprovados, será possível enviar fotos das partes.</span>
                </div>
            <?php endif; ?>

            <?php else: ?>
            <!-- ════════════════════════════════════════════════════
                 PASSO 3: Exemplar selecionado — enviar partes
            ═══════════════════════════════════════════════════════ -->

            <!-- Banner exemplar selecionado -->
            <div class="exemplar-banner">
                <div>
                    <div style="font-size:.78rem;font-weight:700;color:#888;text-transform:uppercase;margin-bottom:3px;">Exemplar selecionado</div>
                    <span class="ex-cod"><?= htmlspecialchars($exemplar['codigo']) ?></span>
                    <div class="ex-det">
                        <?= htmlspecialchars($exemplar['cidade']) ?> / <?= htmlspecialchars($exemplar['estado']) ?>
                        <?= $exemplar['numero_etiqueta'] ? ' · Etiq. ' . htmlspecialchars($exemplar['numero_etiqueta']) : '' ?>
                        · Orient.: <?= htmlspecialchars($exemplar['especialista_nome']) ?>
                    </div>
                </div>
                <a href="?especie_id=<?= $especie_id ?>" class="btn-trocar">
                    <i class="fas fa-exchange-alt"></i> Trocar exemplar
                </a>
            </div>

            <!-- Notificação de status -->
            <?php if ($notificacao): ?>
                <div class="alerta alerta-<?= $notificacao['tipo'] ?>">
                    <i class="fas fa-<?= $notificacao['tipo']==='success' ? 'check-circle' : ($notificacao['tipo']==='warning' ? 'exclamation-triangle' : 'info-circle') ?>"></i>
                    <span><?= htmlspecialchars($notificacao['msg']) ?></span>
                </div>
            <?php endif; ?>

            <!-- Partes -->
            <div class="partes-titulo">
                <i class="fas fa-th-large" style="color:var(--verde)"></i>
                Passo 3 — Clique na parte para enviar foto
            </div>
            <div class="partes-grid">
                <?php foreach ($partes_config as $key => $cfg):
                    $ps = $partes_status[$key]; $cls = $ps['status'];
                ?>
                <div class="parte-card <?= $cls ?>"
                     <?= $cls !== 'dispensada' ? "onclick=\"abrirModal('{$key}','{$cfg['nome']}')\""  : '' ?>
                     title="<?= $cls==='dispensada' ? 'Dispensada: ' . htmlspecialchars($ps['motivo']??'') : 'Enviar foto de '.$cfg['nome'] ?>">
                    <div class="parte-icone"><?= $cfg['icone'] ?></div>
                    <div class="parte-nome"><?= $cfg['nome'] ?></div>
                    <?php if ($cls === 'fotografada'): ?>
                        <span class="parte-badge badge-fotografada"><?= $ps['total'] ?> foto<?= $ps['total']>1?'s':'' ?></span>
                        <div class="parte-total">+ adicionar</div>
                    <?php elseif ($cls === 'dispensada'): ?>
                        <span class="parte-badge badge-dispensada">Dispensada</span>
                    <?php else: ?>
                        <span class="parte-badge badge-pendente">Pendente</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Galeria -->
            <?php if (count($fotos_campo) > 0): ?>
                <div class="filtros-box">
                    <h5><i class="fas fa-sliders-h" style="margin-right:5px"></i>Filtros de visualização</h5>
                    <div class="filtros-row">
                        <div class="filtro-item"><label>Brilho <span id="v-b">100%</span></label><input type="range" id="f-b" min="30" max="200" value="100" oninput="apFiltros()"></div>
                        <div class="filtro-item"><label>Contraste <span id="v-c">100%</span></label><input type="range" id="f-c" min="30" max="250" value="100" oninput="apFiltros()"></div>
                        <div class="filtro-item"><label>Saturação <span id="v-s">100%</span></label><input type="range" id="f-s" min="0" max="300" value="100" oninput="apFiltros()"></div>
                        <button class="btn-rst" onclick="rstFiltros()"><i class="fas fa-undo"></i> Resetar</button>
                    </div>
                </div>
                <div class="galeria-titulo"><i class="fas fa-images" style="color:var(--verde)"></i>Fotos registradas (<?= count($fotos_campo) ?>)</div>
                <div class="fotos-grid" id="galeria">
                    <?php foreach ($fotos_campo as $foto):
                        $cfg2 = $partes_config[$foto['parte_planta']] ?? ['icone'=>'📷','nome'=>$foto['parte_planta']];
                    ?>
                    <div class="foto-card">
                        <div class="foto-preview">
                            <img src="/penomato_mvp/<?= htmlspecialchars($foto['caminho_imagem']) ?>"
                                 alt="<?= $cfg2['nome'] ?>"
                                 onerror="this.parentElement.innerHTML='<span style=color:#aaa;font-size:2rem>🖼️</span>'">
                        </div>
                        <div class="foto-info">
                            <div class="foto-parte"><?= $cfg2['icone'] ?> <?= $cfg2['nome'] ?></div>
                            <div class="foto-meta">
                                <?php if ($foto['data_coleta']): ?><i class="fas fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($foto['data_coleta'])) ?><br><?php endif; ?>
                                <?php if ($foto['coletor_nome']): ?><i class="fas fa-user"></i> <?= htmlspecialchars($foto['coletor_nome']) ?><?php endif; ?>
                            </div>
                            <span class="foto-val val-<?= $foto['status_validacao'] ?>">
                                <?= $foto['status_validacao']==='pendente'?'⏳ Pendente':($foto['status_validacao']==='aprovado'?'✅ Aprovado':'❌ Rejeitado') ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="placeholder-box">
                    <i class="fas fa-camera"></i>
                    <p>Nenhuma foto registrada para este exemplar.<br>Clique em uma parte acima para começar.</p>
                </div>
            <?php endif; ?>

            <?php endif; // fim passo 3 ?>

        <?php else: ?>
            <div class="placeholder-box">
                <i class="fas fa-leaf"></i>
                <p>Selecione uma espécie para começar.</p>
            </div>
        <?php endif; ?>

        <div class="rodape-botoes">
            <a href="/penomato_mvp/src/Views/entrar_colaborador.php" class="btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar ao Painel
            </a>
        </div>

    </div>
</div>

<!-- Modal de upload -->
<div class="overlay" id="overlay" onclick="fecharFora(event)">
    <div class="modal-upload">
        <button class="modal-fechar" onclick="fecharModal()">&times;</button>
        <h3 id="modal-titulo">📸 Enviar foto</h3>

        <!-- Chip do exemplar -->
        <div class="exemplar-chip">
            <i class="fas fa-seedling"></i>
            Exemplar <strong><?= $exemplar ? htmlspecialchars($exemplar['codigo']) : '' ?></strong>
            <?= $exemplar ? '· ' . htmlspecialchars($exemplar['cidade']) . '/' . htmlspecialchars($exemplar['estado']) : '' ?>
        </div>

        <form method="POST"
              action="/penomato_mvp/src/Controllers/processar_upload_exsicata.php"
              enctype="multipart/form-data" id="form-upload">
            <input type="hidden" name="especie_id"   value="<?= $especie_id ?>">
            <input type="hidden" name="exemplar_id"  value="<?= $exemplar_id ?>">
            <input type="hidden" name="parte_planta" id="input-parte">

            <div class="campo">
                <label>Parte da planta</label>
                <input type="text" id="display-parte" disabled style="background:#f0f0f0;color:#555">
            </div>
            <div class="campo">
                <label for="data_coleta">Data da coleta <span class="req">*</span></label>
                <input type="date" name="data_coleta" id="data_coleta" max="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="campo">
                <label for="licenca">Licença</label>
                <select name="licenca" id="licenca">
                    <option value="Privado">Privado (uso interno)</option>
                    <option value="CC BY 4.0">CC BY 4.0</option>
                    <option value="CC BY-NC 4.0">CC BY-NC 4.0</option>
                    <option value="CC BY-SA 4.0">CC BY-SA 4.0</option>
                </select>
            </div>
            <div class="campo">
                <label for="obs">Observações</label>
                <textarea name="observacoes" id="obs" placeholder="Face adaxial, régua ao lado, condições de luz..."></textarea>
            </div>
            <div class="campo">
                <label>Foto <span class="req">*</span></label>
                <div class="drop-zone" id="drop-zone" onclick="document.getElementById('input-arquivo').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Clique ou arraste<br><small style="color:#aaa">JPG / PNG — máx 15 MB</small></p>
                    <div class="arq-nome" id="arq-nome"></div>
                </div>
                <input type="file" name="imagem" id="input-arquivo" accept="image/jpeg,image/jpg,image/png" required>
            </div>
            <button type="submit" class="btn-enviar" id="btn-enviar">
                <i class="fas fa-upload"></i> Enviar foto
            </button>
        </form>
    </div>
</div>

<script>
function abrirModal(parte, nome) {
    document.getElementById('input-parte').value   = parte;
    document.getElementById('display-parte').value = nome;
    document.getElementById('modal-titulo').textContent = '📸 Enviar foto — ' + nome;
    document.getElementById('overlay').classList.add('aberto');
    document.getElementById('data_coleta').focus();
}
function fecharModal() { document.getElementById('overlay').classList.remove('aberto'); }
function fecharFora(e) { if (e.target===document.getElementById('overlay')) fecharModal(); }
document.addEventListener('keydown', e => { if (e.key==='Escape') fecharModal(); });

const dz = document.getElementById('drop-zone');
const ia = document.getElementById('input-arquivo');
const an = document.getElementById('arq-nome');
if (ia) ia.addEventListener('change', () => mostrarArq(ia.files[0]));
if (dz) {
    dz.addEventListener('dragover',  e => { e.preventDefault(); dz.classList.add('sobre'); });
    dz.addEventListener('dragleave', () => dz.classList.remove('sobre'));
    dz.addEventListener('drop', e => {
        e.preventDefault(); dz.classList.remove('sobre');
        if (e.dataTransfer.files[0]) { ia.files = e.dataTransfer.files; mostrarArq(e.dataTransfer.files[0]); }
    });
}
function mostrarArq(f) {
    if (!f) return;
    an.textContent = '✅ ' + f.name + ' (' + (f.size/1024/1024).toFixed(1) + ' MB)';
    an.style.display = 'block';
}
const formUp = document.getElementById('form-upload');
if (formUp) formUp.addEventListener('submit', e => {
    if (!ia.files[0]) { e.preventDefault(); alert('Selecione uma imagem.'); return; }
    const btn = document.getElementById('btn-enviar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
});

function apFiltros() {
    const b = document.getElementById('f-b').value;
    const c = document.getElementById('f-c').value;
    const s = document.getElementById('f-s').value;
    document.getElementById('v-b').textContent = b + '%';
    document.getElementById('v-c').textContent = c + '%';
    document.getElementById('v-s').textContent = s + '%';
    document.querySelectorAll('#galeria .foto-preview img')
            .forEach(img => img.style.filter = `brightness(${b}%) contrast(${c}%) saturate(${s}%)`);
}
function rstFiltros() {
    ['f-b','f-c','f-s'].forEach(id => document.getElementById(id).value = 100);
    ['v-b','v-c','v-s'].forEach(id => document.getElementById(id).textContent = '100%');
    document.querySelectorAll('#galeria .foto-preview img').forEach(img => img.style.filter = '');
}
</script>
</body>
</html>
