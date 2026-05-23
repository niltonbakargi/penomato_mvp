<?php
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php'); exit;
}
$stmt = $pdo->prepare("SELECT categoria FROM usuarios WHERE id = ? LIMIT 1");
$stmt->execute([(int)$_SESSION['usuario_id']]);
if ($stmt->fetchColumn() !== 'gestor') { http_response_code(403); die('Acesso restrito.'); }

// Contagens para exibir
$total_especies = $pdo->query("SELECT COUNT(*) FROM especies_administrativo")->fetchColumn();
$total_imagens  = $pdo->query("SELECT COUNT(*) FROM especies_imagens")->fetchColumn();

// Verificar quantos arquivos existem de facto em disco
$dir_uploads     = __DIR__ . '/../../uploads/';
$tamanho_uploads = 0;
$arquivos_disco  = 0;
if (is_dir($dir_uploads)) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir_uploads, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($it as $f) { if ($f->isFile()) { $tamanho_uploads += $f->getSize(); $arquivos_disco++; } }
}
$tamanho_fmt = $tamanho_uploads > 1048576
    ? round($tamanho_uploads / 1048576, 1) . ' MB'
    : round($tamanho_uploads / 1024, 1) . ' KB';

// Verificar quantos caminhos do BD existem em disco
$raiz = realpath(__DIR__ . '/../../');
$rows_img = $pdo->query("SELECT caminho_imagem FROM especies_imagens")->fetchAll(PDO::FETCH_COLUMN);
$existem_disco = 0;
$sample_path   = '';
foreach ($rows_img as $cam) {
    $full = $raiz . '/' . $cam;
    if (file_exists($full)) $existem_disco++;
    if (!$sample_path) $sample_path = $cam; // pega primeiro caminho como amostra
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup — Penomato</title>
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
    <style>
        body { background:#f0f4f0; font-family:'Segoe UI',sans-serif; padding:32px 20px; color:#1e2e1e; }
        .container { max-width:600px; margin:0 auto; }
        .header { background:var(--cor-primaria); color:#fff; padding:18px 28px; border-radius:10px; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; }
        .header h1 { font-size:1.2em; font-weight:600; }
        .btn-voltar { background:rgba(255,255,255,.2); color:#fff; border:none; padding:7px 16px; border-radius:20px; cursor:pointer; text-decoration:none; font-size:.85em; }
        .btn-voltar:hover { background:rgba(255,255,255,.35); }

        .info-card { background:#fff; border-radius:10px; padding:20px 24px; margin-bottom:20px; box-shadow:0 2px 8px rgba(0,0,0,.07); display:flex; gap:32px; }
        .info-item { text-align:center; }
        .info-item .num { font-size:1.6em; font-weight:700; color:var(--cor-primaria); }
        .info-item .label { font-size:.78em; color:#888; margin-top:2px; }

        .etapa { background:#fff; border-radius:10px; padding:22px 24px; margin-bottom:16px; box-shadow:0 2px 8px rgba(0,0,0,.07); }
        .etapa-header { display:flex; align-items:center; gap:12px; margin-bottom:12px; }
        .etapa-num { width:32px; height:32px; border-radius:50%; background:var(--cor-primaria); color:#fff; font-weight:700; display:flex; align-items:center; justify-content:center; font-size:.9em; flex-shrink:0; }
        .etapa h2 { font-size:1em; font-weight:600; margin:0; }
        .etapa p { font-size:.85em; color:#666; margin:0 0 14px 44px; }

        .btn-etapa { display:inline-flex; align-items:center; gap:8px; background:var(--cor-primaria); color:#fff; border:none; padding:10px 22px; border-radius:8px; cursor:pointer; font-size:.88em; font-weight:600; text-decoration:none; margin-left:44px; transition:background .15s; }
        .btn-etapa:hover { background:var(--cor-primaria-hover); }
        .btn-etapa:disabled { background:#aaa; cursor:not-allowed; }

        .status { margin-left:44px; margin-top:10px; font-size:.83em; padding:8px 12px; border-radius:6px; display:none; }
        .status.ok  { background:#d4edda; color:#155724; display:block; }
        .status.err { background:#f8d7da; color:#721c24; display:block; }
        .status.run { background:#cce5ff; color:#004085; display:block; }

        .aviso { background:#fff3cd; color:#856404; border-radius:8px; padding:12px 16px; font-size:.83em; margin-bottom:20px; }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1>💾 Backup do Sistema</h1>
        <a href="/penomato_mvp/src/Controllers/controlador_gestor.php" class="btn-voltar">← Voltar</a>
    </div>

    <div class="info-card">
        <div class="info-item">
            <div class="num"><?= $total_especies ?></div>
            <div class="label">Espécies</div>
        </div>
        <div class="info-item">
            <div class="num"><?= $total_imagens ?></div>
            <div class="label">Registros no banco</div>
        </div>
        <div class="info-item">
            <div class="num" style="color:<?= $existem_disco > 0 ? 'var(--cor-primaria)' : '#c0392b' ?>"><?= $existem_disco ?></div>
            <div class="label">Arquivos em disco</div>
        </div>
        <div class="info-item">
            <div class="num"><?= $tamanho_fmt ?></div>
            <div class="label">Tamanho</div>
        </div>
    </div>
    <?php if ($existem_disco === 0 && $total_imagens > 0): ?>
    <div class="aviso" style="background:#f8d7da;color:#721c24;">
        ⚠️ O banco tem <?= $total_imagens ?> registros de imagens mas <strong>nenhum arquivo existe em disco</strong>.<br>
        Caminho esperado (exemplo): <code><?= htmlspecialchars($sample_path) ?></code>
    </div>
    <?php endif; ?>

    <div class="aviso">
        💡 Faça o backup em duas etapas. Guarde os dois arquivos juntos — são necessários para uma restauração completa.
    </div>

    <!-- ETAPA 1 -->
    <div class="etapa">
        <div class="etapa-header">
            <div class="etapa-num">1</div>
            <h2>Banco de dados</h2>
        </div>
        <p>Exporta todas as tabelas em formato SQL. Inclui espécies, características, artigos, usuários e histórico.</p>
        <button class="btn-etapa" id="btn-banco" onclick="baixarBanco()">⬇ Baixar banco.sql</button>
        <div class="status" id="status-banco"></div>
    </div>

    <!-- ETAPA 2 -->
    <div class="etapa">
        <div class="etapa-header">
            <div class="etapa-num">2</div>
            <h2>Imagens</h2>
        </div>
        <p>Compacta todos os arquivos de imagem em um .zip. Tamanho atual em disco: <strong><?= $tamanho_fmt ?></strong>.</p>
        <button class="btn-etapa" id="btn-imgs" onclick="baixarImagens()"><?= $tamanho_uploads === 0 ? '⬇ Baixar imagens.zip (vazio)' : '⬇ Baixar imagens.zip' ?></button>
        <div class="status" id="status-imgs"></div>
    </div>

</div>

<script>
function setStatus(id, tipo, msg) {
    var el = document.getElementById(id);
    el.className = 'status ' + tipo;
    el.textContent = msg;
}

function baixarBanco() {
    var btn = document.getElementById('btn-banco');
    btn.disabled = true;
    setStatus('status-banco', 'run', '⏳ Gerando exportação do banco…');

    fetch('/penomato_mvp/src/Controllers/backup_banco.php')
        .then(function(res) {
            if (!res.ok) return res.text().then(function(t) { throw new Error(t); });
            return res.blob();
        })
        .then(function(blob) {
            var url  = URL.createObjectURL(blob);
            var a    = document.createElement('a');
            var data = new Date().toISOString().slice(0,10);
            a.href     = url;
            a.download = 'banco_penomato_' + data + '.sql';
            a.click();
            URL.revokeObjectURL(url);
            btn.disabled = false;
            setStatus('status-banco', 'ok', '✅ Arquivo baixado com sucesso.');
        })
        .catch(function(err) {
            btn.disabled = false;
            setStatus('status-banco', 'err', '❌ Erro: ' + err.message);
        });
}

function baixarImagens() {
    var btn = document.getElementById('btn-imgs');
    btn.disabled = true;
    setStatus('status-imgs', 'run', '⏳ Compactando imagens…');

    fetch('/penomato_mvp/src/Controllers/backup_imagens.php')
        .then(function(res) {
            if (res.status === 204) {
                btn.disabled = false;
                setStatus('status-imgs', 'ok', '⚠️ Nenhuma imagem encontrada em disco. Nada para baixar.');
                return null;
            }
            if (!res.ok) return res.text().then(function(t) { throw new Error(t); });
            return res.blob();
        })
        .then(function(blob) {
            if (!blob) return;
            var url  = URL.createObjectURL(blob);
            var a    = document.createElement('a');
            var data = new Date().toISOString().slice(0,10);
            a.href     = url;
            a.download = 'imagens_penomato_' + data + '.zip';
            a.click();
            URL.revokeObjectURL(url);
            btn.disabled = false;
            setStatus('status-imgs', 'ok', '✅ Arquivo baixado com sucesso.');
        })
        .catch(function(err) {
            btn.disabled = false;
            setStatus('status-imgs', 'err', '❌ Erro: ' + err.message);
        });
}
</script>
</body>
</html>
