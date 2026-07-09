<?php
// ============================================================
// ADICIONAR IMAGENS DA INTERNET — por espécie e parte da planta
// ============================================================
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$usuario_id   = (int)$_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';

$especie_id = isset($_GET['especie_id']) ? (int)$_GET['especie_id'] : 0;

// ── Espécies disponíveis ──────────────────────────────────────────────────────
$especies = $pdo->query("
    SELECT id, nome_cientifico, status
    FROM especies_administrativo
    ORDER BY nome_cientifico
")->fetchAll();

// ── Partes ───────────────────────────────────────────────────────────────────
$partes_config = [
    'folha'            => ['icone' => '🍃', 'nome' => 'Folha'],
    'flor'             => ['icone' => '🌸', 'nome' => 'Flor'],
    'fruto'            => ['icone' => '🍎', 'nome' => 'Fruto'],
    'caule'            => ['icone' => '🌿', 'nome' => 'Caule'],
    'semente'          => ['icone' => '🌱', 'nome' => 'Semente'],
    'habito'           => ['icone' => '🌳', 'nome' => 'Hábito'],
    'exsicata_completa'=> ['icone' => '📋', 'nome' => 'Exsicata completa'],
    'detalhe'          => ['icone' => '🔍', 'nome' => 'Detalhe'],
];

// ── Dados da espécie e contagem de imagens ────────────────────────────────────
$especie          = null;
$contagem_partes  = [];
$fotos_internet   = [];

if ($especie_id > 0) {
    $stmt = $pdo->prepare("SELECT id, nome_cientifico, status FROM especies_administrativo WHERE id = ?");
    $stmt->execute([$especie_id]);
    $especie = $stmt->fetch();

    if ($especie) {
        $stmt = $pdo->prepare("
            SELECT parte_planta, COUNT(*) AS total
            FROM especies_imagens
            WHERE especie_id = ? AND origem = 'internet'
            GROUP BY parte_planta
        ");
        $stmt->execute([$especie_id]);
        $contagem_partes = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $stmt = $pdo->prepare("
            SELECT id, parte_planta, caminho_imagem, fonte_nome, fonte_url,
                   autor_imagem, licenca, status_validacao, data_upload
            FROM especies_imagens
            WHERE especie_id = ? AND origem = 'internet'
            ORDER BY parte_planta, data_upload DESC
        ");
        $stmt->execute([$especie_id]);
        $fotos_internet = $stmt->fetchAll();
    }
}

$status_labels = [
    'sem_dados'      => ['label' => 'Sem dados',      'cor' => '#94a3b8'],
    'dados_internet' => ['label' => 'Dados internet', 'cor' => '#3b82f6'],
    'descrita'       => ['label' => 'Identificada',   'cor' => '#8b5cf6'],
    'registrada'     => ['label' => 'Registrada',     'cor' => '#f59e0b'],
    'em_revisao'     => ['label' => 'Em revisão',     'cor' => '#ef4444'],
    'revisada'       => ['label' => 'Revisada',       'cor' => '#10b981'],
    'publicado'      => ['label' => 'Publicada',      'cor' => 'var(--cor-primaria)'],
];

$mensagem_sucesso = isset($_GET['sucesso']) ? urldecode($_GET['sucesso']) : '';
$mensagem_erro    = isset($_GET['erro'])    ? urldecode($_GET['erro'])    : '';
$parte_ativa      = isset($_GET['parte'])   ? $_GET['parte']              : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imagens da Internet — Penomato</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha384-blOohCVdhjmtROpu8+CfTnUWham9nkX7P7OZQMst+RUnhtoY/9qemFAkIKOYxDI3" crossorigin="anonymous">
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
    <style>
        body { background: var(--cinza-50); color: var(--cinza-800); padding: var(--esp-8) var(--esp-5); }
        .container { max-width: 1100px; margin: 0 auto; }

        /* Cabeçalho */
        .cabecalho {
            background: white; padding: 26px 36px; border-radius: 12px 12px 0 0;
            border-bottom: 4px solid var(--cor-primaria);
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 12px;
        }
        .cabecalho h1 { color: var(--cor-primaria); font-size: 1.7rem; font-weight: 600; }
        .cabecalho .sub { color: var(--cinza-500); font-style: italic; font-size: .88rem; margin-top: 3px; }
        .user-pill { background: var(--cinza-50); padding: 7px 16px; border-radius: 40px; display: flex; align-items: center; gap: 9px; font-size: .88rem; }
        .user-pill i { color: var(--cor-primaria); }
        .btn-sair { color: var(--perigo-cor); text-decoration: none; padding: 3px 8px; border-radius: 20px; transition: .2s; }
        .btn-sair:hover { background: var(--perigo-cor); color: white; }

        /* Card */
        .card-principal { background: white; padding: 30px 36px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 15px rgba(0,0,0,.05); }

        /* Seletor */
        .seletor-box { background: var(--cinza-50); border: 2px solid var(--cinza-200); border-radius: 10px; padding: 20px 24px; margin-bottom: 22px; }
        .seletor-box h3 { color: var(--cor-primaria); margin-bottom: 12px; font-size: .95rem; font-weight: 700; }
        .seletor-row { display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; }
        .seletor-row .fg { flex: 1; min-width: 240px; }
        .seletor-row label { font-weight: 600; font-size: .875rem; margin-bottom: 6px; display: block; }
        .seletor-row select { width: 100%; padding: 10px 14px; border: 2px solid var(--cinza-200); border-radius: 8px; font-size: .95rem; }
        .seletor-row select:focus { outline: none; border-color: var(--cor-primaria); }
        .btn-carregar { background: var(--cor-primaria); color: white; border: none; padding: 10px 24px; border-radius: 40px; font-weight: 600; cursor: pointer; white-space: nowrap; transition: .2s; }
        .btn-carregar:hover { background: var(--cor-primaria-hover); }

        /* Alertas */
        .alerta { padding: 13px 16px; border-radius: 8px; margin-bottom: 18px; display: flex; align-items: flex-start; gap: 10px; font-size: .9rem; }
        .alerta-success { background: var(--sucesso-fundo); color: var(--sucesso-texto); border-left: 4px solid var(--sucesso-cor); }
        .alerta-danger  { background: var(--perigo-fundo);  color: var(--perigo-texto);  border-left: 4px solid var(--perigo-cor); }

        /* Banner espécie */
        .especie-banner { background: var(--verde-50); border-radius: 10px; padding: 16px 22px; margin-bottom: 22px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .especie-nome { font-size: 1.25rem; font-weight: 600; color: var(--cor-primaria); font-style: italic; }
        .badge-status { padding: 4px 12px; border-radius: 20px; font-size: .75rem; font-weight: 700; color: white; }

        /* Partes */
        .partes-titulo { font-size: .95rem; font-weight: 700; color: var(--cinza-700); margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
        .partes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(155px, 1fr)); gap: 12px; margin-bottom: 32px; }
        .parte-card { border-radius: 10px; border: 2px solid var(--cinza-200); padding: 18px 12px; text-align: center; cursor: pointer; transition: .2s; background: #fafafa; }
        .parte-card:hover { border-color: var(--cor-primaria); box-shadow: 0 4px 10px rgba(0,0,0,.08); transform: translateY(-2px); }
        .parte-card.tem-fotos { border-color: #3b82f6; background: #eff6ff; }
        .parte-icone { font-size: 1.9rem; margin-bottom: 7px; }
        .parte-nome  { font-weight: 700; font-size: .88rem; margin-bottom: 6px; }
        .parte-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: .7rem; font-weight: 700; }
        .badge-vazio  { background: #e2e8f0; color: #555; }
        .badge-tem    { background: #dbeafe; color: #1e40af; }
        .parte-total  { font-size: .75rem; color: var(--cinza-400); margin-top: 3px; }

        /* Galeria */
        .galeria-titulo { font-size: .95rem; font-weight: 700; color: var(--cinza-700); margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
        .fotos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px; }
        .foto-card { border: 2px solid var(--cinza-200); border-radius: 8px; overflow: hidden; background: white; transition: .2s; }
        .foto-card:hover { box-shadow: 0 4px 10px rgba(0,0,0,.1); }
        .foto-preview { height: 130px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: var(--cinza-50); }
        .foto-preview img { width: 100%; height: 100%; object-fit: cover; }
        .foto-info { padding: 10px 12px; }
        .foto-parte { font-weight: 700; color: var(--cor-primaria); font-size: .83rem; }
        .foto-meta  { font-size: .76rem; color: var(--cinza-500); margin-top: 3px; line-height: 1.4; }
        .foto-val   { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: .68rem; font-weight: 700; margin-top: 5px; }
        .val-pendente  { background: #fff3cd; color: #856404; }
        .val-aprovado  { background: #d4edda; color: #155724; }
        .val-rejeitado { background: #f8d7da; color: #721c24; }

        /* Placeholder */
        .placeholder-box { text-align: center; padding: 56px 20px; background: var(--cinza-50); border-radius: 10px; color: #718096; }
        .placeholder-box i { font-size: 3.2rem; color: #cbd5e0; margin-bottom: 14px; display: block; }

        /* Rodapé */
        .rodape-botoes { display: flex; justify-content: center; margin-top: 32px; }
        .btn-voltar { background: var(--cinza-500); color: white; text-decoration: none; padding: 11px 28px; border-radius: 40px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: .2s; }
        .btn-voltar:hover { background: #5a6268; color: white; }

        /* Modal upload */
        .overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.55); z-index: 1000; align-items: center; justify-content: center; }
        .overlay.aberto { display: flex; }
        .modal-upload { background: white; border-radius: 12px; padding: 30px 34px; width: 100%; max-width: 500px; box-shadow: 0 20px 40px rgba(0,0,0,.2); position: relative; max-height: 90vh; overflow-y: auto; }
        .modal-upload h3 { color: var(--cor-primaria); margin-bottom: 20px; font-size: 1.1rem; }
        .modal-fechar { position: absolute; top: 14px; right: 18px; background: none; border: none; font-size: 1.4rem; color: #999; cursor: pointer; }
        .modal-fechar:hover { color: #333; }
        .campo { margin-bottom: 15px; }
        .campo label { display: block; font-weight: 600; font-size: .875rem; margin-bottom: 6px; color: #2d3748; }
        .campo label .req { color: var(--perigo-cor); }
        .campo input, .campo select, .campo textarea { width: 100%; padding: 9px 13px; border: 2px solid var(--cinza-200); border-radius: 8px; font-size: .93rem; }
        .campo input:focus, .campo select:focus, .campo textarea:focus { outline: none; border-color: var(--cor-primaria); }
        .campo textarea { resize: vertical; min-height: 65px; }
        .drop-zone { border: 2px dashed #c3d4e0; border-radius: 8px; padding: 26px 16px; text-align: center; cursor: pointer; transition: .2s; background: #fafcfe; }
        .drop-zone:hover, .drop-zone.sobre { border-color: var(--cor-primaria); background: var(--verde-50); }
        .drop-zone i { font-size: 1.9rem; color: #aaa; display: block; margin-bottom: 7px; }
        .drop-zone p { font-size: .83rem; color: var(--cinza-500); margin: 0; }
        .drop-zone .arq-nome { font-size: .83rem; color: var(--cor-primaria); font-weight: 600; margin-top: 7px; }
        #input-arquivo { display: none; }
        .btn-enviar { width: 100%; background: var(--cor-primaria); color: white; border: none; padding: 12px; border-radius: 8px; font-size: .97rem; font-weight: 700; cursor: pointer; margin-top: 6px; transition: .2s; }
        .btn-enviar:hover { background: var(--cor-primaria-hover); }
        .btn-enviar:disabled { opacity: .6; cursor: not-allowed; }

        @media (max-width: 600px) {
            .cabecalho { padding: 18px 20px; }
            .card-principal { padding: 20px 16px; }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="cabecalho">
        <div>
            <h1>🌐 Imagens da Internet</h1>
            <div class="sub">Adicione imagens de fontes externas ao acervo por espécie e parte da planta</div>
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
                <?php $sc = $status_labels[$especie['status']] ?? ['label' => $especie['status'], 'cor' => '#888']; ?>
                <span class="badge-status" style="background:<?= $sc['cor'] ?>"><?= $sc['label'] ?></span>
            </div>

            <!-- PASSO 2: Selecionar parte -->
            <div class="partes-titulo">
                <i class="fas fa-th-large" style="color:var(--cor-primaria)"></i>
                Passo 2 — Clique na parte para adicionar imagens
            </div>
            <div class="partes-grid">
                <?php foreach ($partes_config as $key => $cfg):
                    $total = (int)($contagem_partes[$key] ?? 0);
                    $cls   = $total > 0 ? 'tem-fotos' : '';
                ?>
                <div class="parte-card <?= $cls ?>"
                     onclick="abrirModal('<?= $key ?>', '<?= addslashes($cfg['nome']) ?>')">
                    <div class="parte-icone"><?= $cfg['icone'] ?></div>
                    <div class="parte-nome"><?= $cfg['nome'] ?></div>
                    <?php if ($total > 0): ?>
                        <span class="parte-badge badge-tem"><?= $total ?> foto<?= $total > 1 ? 's' : '' ?></span>
                        <div class="parte-total">+ adicionar</div>
                    <?php else: ?>
                        <span class="parte-badge badge-vazio">Nenhuma ainda</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Galeria de imagens já adicionadas -->
            <?php if (count($fotos_internet) > 0): ?>
                <div class="galeria-titulo">
                    <i class="fas fa-images" style="color:var(--cor-primaria)"></i>
                    Imagens registradas (<?= count($fotos_internet) ?>)
                </div>
                <div class="fotos-grid">
                    <?php foreach ($fotos_internet as $foto):
                        $cfg2 = $partes_config[$foto['parte_planta']] ?? ['icone' => '📷', 'nome' => $foto['parte_planta']];
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
                                <?php if ($foto['fonte_nome']): ?>
                                    <i class="fas fa-globe"></i>
                                    <?php if ($foto['fonte_url']): ?>
                                        <a href="<?= htmlspecialchars($foto['fonte_url']) ?>" target="_blank" rel="noopener"
                                           style="color:inherit"><?= htmlspecialchars($foto['fonte_nome']) ?></a>
                                    <?php else: ?>
                                        <?= htmlspecialchars($foto['fonte_nome']) ?>
                                    <?php endif; ?>
                                    <br>
                                <?php endif; ?>
                                <?php if ($foto['autor_imagem']): ?>
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($foto['autor_imagem']) ?><br>
                                <?php endif; ?>
                                <?php if ($foto['licenca']): ?>
                                    <i class="fas fa-balance-scale"></i> <?= htmlspecialchars($foto['licenca']) ?>
                                <?php endif; ?>
                            </div>
                            <span class="foto-val val-<?= $foto['status_validacao'] ?>">
                                <?= $foto['status_validacao'] === 'pendente' ? '⏳ Pendente'
                                    : ($foto['status_validacao'] === 'aprovado' ? '✅ Aprovado' : '❌ Rejeitado') ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="placeholder-box">
                    <i class="fas fa-images"></i>
                    <p>Nenhuma imagem da internet registrada para esta espécie.<br>Clique em uma parte acima para começar.</p>
                </div>
            <?php endif; ?>

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
        <h3 id="modal-titulo">🌐 Adicionar imagem da internet</h3>

        <form method="POST"
              action="/penomato_mvp/src/Controllers/processar_adicionar_imagem_internet.php"
              enctype="multipart/form-data" id="form-upload">
            <input type="hidden" name="especie_id"   value="<?= $especie_id ?>">
            <input type="hidden" name="parte_planta" id="input-parte">

            <div class="campo">
                <label>Parte da planta</label>
                <input type="text" id="display-parte" disabled style="background:#f0f0f0;color:#555">
            </div>
            <div class="campo">
                <label for="fonte_nome">Fonte / Site <span class="req">*</span></label>
                <input type="text" name="fonte_nome" id="fonte_nome"
                       placeholder="Ex: Flora e Funga do Brasil, Plantnet, iNaturalist..."
                       required>
            </div>
            <div class="campo">
                <label for="fonte_url">URL da imagem</label>
                <input type="url" name="fonte_url" id="fonte_url"
                       placeholder="https://...">
            </div>
            <div class="campo">
                <label for="autor_imagem">Autor da imagem</label>
                <input type="text" name="autor_imagem" id="autor_imagem"
                       placeholder="Nome do fotógrafo ou instituição">
            </div>
            <div class="campo">
                <label for="licenca">Licença</label>
                <select name="licenca" id="licenca">
                    <option value="Desconhecida">Desconhecida</option>
                    <option value="CC BY 4.0">CC BY 4.0</option>
                    <option value="CC BY-NC 4.0">CC BY-NC 4.0</option>
                    <option value="CC BY-SA 4.0">CC BY-SA 4.0</option>
                    <option value="CC BY-NC-SA 4.0">CC BY-NC-SA 4.0</option>
                    <option value="Domínio público">Domínio público</option>
                    <option value="Uso educacional">Uso educacional</option>
                </select>
            </div>
            <div class="campo">
                <label for="descricao">Observações</label>
                <textarea name="descricao" id="descricao"
                          placeholder="Descrição, contexto, ângulo, condições..."></textarea>
            </div>
            <div class="campo">
                <label>Imagem <span class="req">*</span></label>
                <div class="drop-zone" id="drop-zone"
                     onclick="document.getElementById('input-arquivo').click()">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Clique ou arraste a imagem<br><small style="color:#aaa">JPG / PNG — máx 10 MB</small></p>
                    <div class="arq-nome" id="arq-nome" style="display:none"></div>
                </div>
                <input type="file" name="imagens[]" id="input-arquivo"
                       accept="image/jpeg,image/jpg,image/png" multiple required>
            </div>
            <button type="submit" class="btn-enviar" id="btn-enviar">
                <i class="fas fa-upload"></i> Enviar imagem
            </button>
        </form>
    </div>
</div>

<script>
function abrirModal(parte, nome) {
    document.getElementById('input-parte').value   = parte;
    document.getElementById('display-parte').value = nome;
    document.getElementById('modal-titulo').textContent = '🌐 Adicionar imagem — ' + nome;
    document.getElementById('overlay').classList.add('aberto');
    document.getElementById('fonte_nome').focus();
}
function fecharModal() { document.getElementById('overlay').classList.remove('aberto'); }
function fecharFora(e) { if (e.target === document.getElementById('overlay')) fecharModal(); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') fecharModal(); });

const dz = document.getElementById('drop-zone');
const ia = document.getElementById('input-arquivo');
const an = document.getElementById('arq-nome');

ia.addEventListener('change', () => mostrarArqs(ia.files));
if (dz) {
    dz.addEventListener('dragover',  e => { e.preventDefault(); dz.classList.add('sobre'); });
    dz.addEventListener('dragleave', () => dz.classList.remove('sobre'));
    dz.addEventListener('drop', e => {
        e.preventDefault(); dz.classList.remove('sobre');
        if (e.dataTransfer.files.length) {
            ia.files = e.dataTransfer.files;
            mostrarArqs(e.dataTransfer.files);
        }
    });
}
function mostrarArqs(files) {
    if (!files.length) return;
    const nomes = Array.from(files).map(f =>
        f.name + ' (' + (f.size / 1024 / 1024).toFixed(1) + ' MB)'
    ).join(', ');
    an.textContent = '✅ ' + nomes;
    an.style.display = 'block';
}

document.getElementById('form-upload').addEventListener('submit', e => {
    if (!ia.files[0]) { e.preventDefault(); alert('Selecione ao menos uma imagem.'); return; }
    const btn = document.getElementById('btn-enviar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
});
</script>
</body>
</html>
