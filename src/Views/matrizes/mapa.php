<?php
// ============================================================
// BANCO DE MATRIZES — LISTA E MAPA
// ============================================================

$titulo_pagina    = 'Banco de Matrizes — Penomato';
$descricao_pagina = 'Matrizes florestais do Cerrado mapeadas colaborativamente';

require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../../Controllers/auth/verificar_acesso.php';

$matrizes = buscarTodos(
    "SELECT m.id, m.codigo, m.especie_nome, m.especie_nome_popular,
            m.latitude, m.longitude, m.foto_geral, m.data_cadastro,
            u.nome AS cadastrador
     FROM matrizes m
     JOIN usuarios u ON u.id = m.cadastrado_por
     WHERE m.status = 'ativo'
     ORDER BY m.data_cadastro DESC"
);

include __DIR__ . '/../includes/cabecalho.php';
?>

<style>
/* ── reset de padding do body (navbar fixo) ── */
body { padding-top: 80px; }

/* ── wrapper geral ── */
.matrizes-page {
    max-width: 700px;
    margin: 0 auto;
    padding: 16px 12px 100px;   /* espaço para o botão flutuante */
}

/* ── cabeçalho da página ── */
.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}

.page-header h4 {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--cinza-800);
    margin: 0;
}

.page-header a {
    font-size: 0.82rem;
    color: var(--cinza-400);
    text-decoration: none;
}

/* ── busca / filtro ── */
.filtro-wrap {
    position: relative;
    margin-bottom: 14px;
}

.filtro-wrap i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--cinza-400);
    font-size: 0.95rem;
}

.filtro-input {
    width: 100%;
    padding: 11px 14px 11px 38px;
    border: 1.5px solid var(--cinza-200);
    border-radius: 12px;
    font-size: 0.95rem;
    outline: none;
    background: white;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    transition: border-color 0.2s;
}

.filtro-input:focus {
    border-color: var(--cor-primaria);
    box-shadow: 0 0 0 3px rgba(11,94,66,0.08);
}

/* ── contador ── */
.contador {
    font-size: 0.8rem;
    color: var(--cinza-400);
    margin-bottom: 10px;
}

/* ── lista de matrizes ── */
.matriz-card {
    display: flex;
    align-items: center;
    gap: 12px;
    background: white;
    border-radius: 14px;
    padding: 12px;
    margin-bottom: 10px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.07);
    text-decoration: none;
    color: inherit;
    transition: box-shadow 0.2s;
    border: 1.5px solid transparent;
}

.matriz-card:active {
    box-shadow: 0 3px 12px rgba(0,0,0,0.12);
    border-color: var(--cinza-200);
}

.matriz-thumb {
    width: 64px;
    height: 64px;
    border-radius: 10px;
    object-fit: cover;
    flex-shrink: 0;
    background: var(--cinza-100);
}

.matriz-info {
    flex: 1;
    min-width: 0;
}

.matriz-nome {
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--cinza-800);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.matriz-cientifico {
    font-size: 0.78rem;
    font-style: italic;
    color: var(--cinza-500);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.matriz-meta {
    font-size: 0.75rem;
    color: var(--cinza-400);
    margin-top: 3px;
}

.btn-mini-mapa {
    flex-shrink: 0;
    background: var(--verde-100);
    color: var(--cor-primaria);
    border: none;
    border-radius: 8px;
    padding: 7px 10px;
    font-size: 0.75rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 3px;
    transition: background 0.2s;
    text-decoration: none;
    white-space: nowrap;
}

.btn-mini-mapa i { font-size: 1rem; }

.btn-mini-mapa:hover,
.btn-mini-mapa:active {
    background: var(--cor-primaria);
    color: white;
}

/* ── estado vazio ── */
.lista-vazia {
    text-align: center;
    padding: 50px 20px;
    color: var(--cinza-400);
}

.lista-vazia i { font-size: 3rem; margin-bottom: 12px; }

/* ── botão flutuante "Ver todas no mapa" ── */
.btn-flutuante {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--cor-primaria);
    color: white;
    border: none;
    padding: 14px 28px;
    border-radius: 50px;
    font-weight: 700;
    font-size: 0.95rem;
    box-shadow: 0 6px 20px rgba(11,94,66,0.4);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 500;
    white-space: nowrap;
    transition: all 0.2s;
}

.btn-flutuante:hover { background: var(--cor-primaria-hover); transform: translateX(-50%) translateY(-2px); }

/* ── tela de mapa (overlay fullscreen) ── */
.mapa-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 900;
    flex-direction: column;
    background: white;
}

.mapa-overlay.ativo { display: flex; }

.mapa-topbar {
    background: var(--cor-primaria);
    color: white;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}

.mapa-topbar button {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 36px; height: 36px;
    border-radius: 50%;
    font-size: 1rem;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.mapa-topbar span {
    font-weight: 700;
    font-size: 1rem;
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#mapa-div { flex: 1; }

/* ── botão nova matriz ── */
.btn-nova-lista {
    display: block;
    background: white;
    border: 2px solid var(--cor-primaria);
    color: var(--cor-primaria);
    border-radius: 12px;
    padding: 13px;
    text-align: center;
    font-weight: 700;
    font-size: 0.95rem;
    text-decoration: none;
    margin-bottom: 16px;
    transition: all 0.2s;
}

.btn-nova-lista:hover {
    background: var(--cor-primaria);
    color: white;
}
</style>

<!-- ── TELA PRINCIPAL: LISTA ── -->
<div class="matrizes-page">

    <div class="page-header">
        <h4><i class="fas fa-tree text-success me-2"></i>Banco de Matrizes</h4>
        <a href="/penomato_mvp/src/Views/matrizes/index.php">
            <i class="fas fa-arrow-left me-1"></i>Voltar
        </a>
    </div>

    <?php if (estaLogado()): ?>
    <a href="/penomato_mvp/src/Views/matrizes/registrar.php" class="btn-nova-lista">
        <i class="fas fa-plus me-2"></i>Registrar nova matriz
    </a>
    <?php else: ?>
    <a href="/penomato_mvp/src/Views/auth/login.php" class="btn-nova-lista">
        <i class="fas fa-plus me-2"></i>Entrar para registrar
    </a>
    <?php endif; ?>

    <!-- Filtro -->
    <div class="filtro-wrap">
        <i class="fas fa-search"></i>
        <input type="text"
               id="filtro-input"
               class="filtro-input"
               placeholder="Buscar por espécie, nome popular ou científico..."
               autocomplete="off"
               oninput="filtrar()">
    </div>

    <div class="contador" id="contador">
        <?php echo count($matrizes); ?> matriz<?php echo count($matrizes) !== 1 ? 'es' : ''; ?> registrada<?php echo count($matrizes) !== 1 ? 's' : ''; ?>
    </div>

    <!-- Lista -->
    <div id="lista">
        <?php if (empty($matrizes)): ?>
        <div class="lista-vazia">
            <i class="fas fa-tree d-block"></i>
            Nenhuma matriz registrada ainda.<br>Seja o primeiro a registrar!
        </div>
        <?php else: ?>
        <?php foreach ($matrizes as $m):
            $nome    = $m['especie_nome_popular'] ?: ($m['especie_nome'] ?: 'Espécie não identificada');
            $cient   = ($m['especie_nome'] && $m['especie_nome_popular']) ? $m['especie_nome'] : '';
            $busca   = strtolower(($m['especie_nome_popular'] ?? '') . ' ' . ($m['especie_nome'] ?? ''));
        ?>
        <div class="matriz-card-wrap"
             data-busca="<?php echo htmlspecialchars($busca); ?>">
            <div class="matriz-card">
                <!-- clique na foto/info vai para ficha -->
                <a href="/penomato_mvp/src/Views/matrizes/ficha.php?id=<?php echo $m['id']; ?>"
                   style="display:flex;align-items:center;gap:12px;flex:1;min-width:0;text-decoration:none;color:inherit;">
                    <img src="/penomato_mvp/<?php echo htmlspecialchars($m['foto_geral']); ?>"
                         alt="<?php echo htmlspecialchars($nome); ?>"
                         class="matriz-thumb"
                         loading="lazy">
                    <div class="matriz-info">
                        <div class="matriz-nome"><?php echo htmlspecialchars($nome); ?></div>
                        <?php if ($cient): ?>
                        <div class="matriz-cientifico"><?php echo htmlspecialchars($cient); ?></div>
                        <?php endif; ?>
                        <div class="matriz-meta">
                            <i class="fas fa-tag me-1"></i><?php echo $m['codigo']; ?>
                            &nbsp;·&nbsp;
                            <?php echo date('d/m/Y', strtotime($m['data_cadastro'])); ?>
                        </div>
                    </div>
                </a>
                <!-- botão ver no mapa -->
                <button class="btn-mini-mapa"
                        onclick="abrirMapaUnico(<?php echo $m['id']; ?>, <?php echo $m['latitude']; ?>, <?php echo $m['longitude']; ?>, '<?php echo addslashes(htmlspecialchars($nome)); ?>', '<?php echo $m['codigo']; ?>', '/penomato_mvp/<?php echo addslashes($m['foto_geral']); ?>')">
                    <i class="fas fa-map-marker-alt"></i>
                    Mapa
                </button>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="lista-vazia" id="sem-resultado" style="display:none">
        <i class="fas fa-search d-block"></i>
        Nenhuma matriz encontrada para esse termo.
    </div>

</div>

<!-- botão flutuante -->
<button class="btn-flutuante" onclick="abrirMapaTodas()">
    <i class="fas fa-map-marked-alt"></i> Ver todas no mapa
</button>

<!-- ── OVERLAY DE MAPA ── -->
<div class="mapa-overlay" id="mapa-overlay">
    <div class="mapa-topbar">
        <button onclick="fecharMapa()" title="Voltar à lista">
            <i class="fas fa-arrow-left"></i>
        </button>
        <span id="mapa-titulo">Todas as matrizes</span>
    </div>
    <div id="mapa-div"></div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
const DADOS = <?php echo json_encode(array_map(function($m) {
    return [
        'id'      => $m['id'],
        'nome'    => $m['especie_nome_popular'] ?: ($m['especie_nome'] ?: 'Espécie não identificada'),
        'cient'   => $m['especie_nome'] ?? '',
        'codigo'  => $m['codigo'],
        'lat'     => (float)$m['latitude'],
        'lon'     => (float)$m['longitude'],
        'foto'    => $m['foto_geral'],
        'data'    => date('d/m/Y', strtotime($m['data_cadastro'])),
    ];
}, $matrizes)); ?>;

let mapa = null;
let marcadores = [];

function iniciarMapa() {
    if (mapa) return;
    mapa = L.map('mapa-div');
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(mapa);
}

function limparMarcadores() {
    marcadores.forEach(function(m) { mapa.removeLayer(m); });
    marcadores = [];
}

function popupHTML(d) {
    return '<div style="min-width:160px">'
        + '<img src="/penomato_mvp/' + d.foto + '" style="width:100%;height:90px;object-fit:cover;border-radius:6px;margin-bottom:6px">'
        + '<strong style="display:block;font-size:0.9rem">' + d.nome + '</strong>'
        + (d.cient && d.cient !== d.nome ? '<em style="font-size:0.75rem;color:#666">' + d.cient + '</em><br>' : '')
        + '<small style="color:#999">' + d.codigo + ' · ' + d.data + '</small><br>'
        + '<a href="/penomato_mvp/src/Views/matrizes/ficha.php?id=' + d.id + '" '
        + 'style="display:inline-block;margin-top:6px;background:#0b5e42;color:white;padding:4px 12px;border-radius:6px;font-size:0.78rem;text-decoration:none">'
        + 'Ver ficha</a>'
        + '</div>';
}

// Abre mapa com TODAS as matrizes visíveis na lista
function abrirMapaTodas() {
    document.getElementById('mapa-overlay').classList.add('ativo');
    document.getElementById('mapa-titulo').textContent = 'Todas as matrizes';
    iniciarMapa();
    limparMarcadores();

    // só as que estão visíveis (após filtro)
    const visiveis = Array.from(document.querySelectorAll('.matriz-card-wrap'))
        .filter(function(el) { return el.style.display !== 'none'; })
        .map(function(el) { return parseInt(el.dataset.id || 0); });

    const alvo = DADOS.filter(function(d) {
        return visiveis.length === 0 || visiveis.includes(d.id);
    });

    if (!alvo.length) return;

    const bounds = [];
    alvo.forEach(function(d) {
        const mk = L.marker([d.lat, d.lon]).bindPopup(popupHTML(d));
        mk.addTo(mapa);
        marcadores.push(mk);
        bounds.push([d.lat, d.lon]);
    });

    mapa.fitBounds(bounds, { padding: [24, 24] });
    setTimeout(function() { mapa.invalidateSize(); }, 100);
}

// Abre mapa centralizado em UMA matriz
function abrirMapaUnico(id, lat, lon, nome, codigo, foto) {
    document.getElementById('mapa-overlay').classList.add('ativo');
    document.getElementById('mapa-titulo').textContent = nome;
    iniciarMapa();
    limparMarcadores();

    const d = DADOS.find(function(x) { return x.id === id; }) || { id, nome, lat, lon, foto, codigo, cient: '', data: '' };
    const mk = L.marker([lat, lon]).bindPopup(popupHTML(d));
    mk.addTo(mapa);
    marcadores.push(mk);

    setTimeout(function() {
        mapa.invalidateSize();
        mapa.setView([lat, lon], 16);
        mk.openPopup();
    }, 100);
}

function fecharMapa() {
    document.getElementById('mapa-overlay').classList.remove('ativo');
}

// Fechar mapa com botão voltar do navegador
window.addEventListener('popstate', fecharMapa);

// ── Filtro ────────────────────────────────────────────────────
function filtrar() {
    const q = document.getElementById('filtro-input').value.trim().toLowerCase();
    const cards = document.querySelectorAll('.matriz-card-wrap');
    let visiveis = 0;

    cards.forEach(function(el) {
        const busca = el.dataset.busca || '';
        const ok = !q || busca.includes(q);
        el.style.display = ok ? '' : 'none';
        if (ok) visiveis++;
    });

    const total = cards.length;
    document.getElementById('contador').textContent =
        visiveis + (visiveis !== total ? ' de ' + total : '') +
        ' matriz' + (visiveis !== 1 ? 'es' : '');

    document.getElementById('sem-resultado').style.display = visiveis === 0 ? 'block' : 'none';
}

// adiciona data-id aos wraps para o filtro de "todas"
document.querySelectorAll('.matriz-card-wrap').forEach(function(el, i) {
    el.dataset.id = DADOS[i] ? DADOS[i].id : 0;
});
</script>

<?php include __DIR__ . '/../includes/rodape.php'; ?>
