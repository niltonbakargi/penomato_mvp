<?php
// ============================================================
// BANCO DE MATRIZES — MAPA PÚBLICO
// ============================================================

$titulo_pagina    = 'Mapa de Matrizes — Banco de Matrizes Florestais';
$descricao_pagina = 'Mapa colaborativo de matrizes florestais do Cerrado';

require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../../Controllers/auth/verificar_acesso.php';

// Todas as matrizes ativas com dados do usuário
$matrizes = buscarTodos(
    "SELECT m.id, m.codigo, m.especie_nome, m.especie_nome_popular,
            m.latitude, m.longitude, m.foto_geral, m.data_cadastro,
            u.nome AS cadastrador
     FROM matrizes m
     JOIN usuarios u ON u.id = m.cadastrado_por
     WHERE m.status = 'ativo'
     ORDER BY m.data_cadastro DESC"
);

// Lista de espécies únicas para o filtro
$especies = [];
foreach ($matrizes as $m) {
    $nome = $m['especie_nome_popular'] ?: $m['especie_nome'];
    if ($nome && !in_array($nome, $especies)) {
        $especies[] = $nome;
    }
}
sort($especies);

include __DIR__ . '/../includes/cabecalho.php';
?>

<style>
    body { padding-top: 80px; }

    .mapa-layout {
        display: flex;
        height: calc(100vh - 80px);
        overflow: hidden;
    }

    /* Painel lateral */
    .mapa-painel {
        width: 340px;
        min-width: 280px;
        background: white;
        border-right: 1px solid var(--cinza-200);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .painel-header {
        padding: 20px 20px 0;
        border-bottom: 1px solid var(--cinza-100);
        padding-bottom: 16px;
    }

    .painel-header h4 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--cinza-800);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .painel-header h4 i {
        color: var(--cor-primaria);
    }

    .filtros {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .filtros select,
    .filtros input {
        border-radius: 8px;
        border: 1px solid var(--cinza-200);
        padding: 8px 12px;
        font-size: 0.85rem;
        width: 100%;
    }

    .filtros select:focus,
    .filtros input:focus {
        border-color: var(--cor-primaria);
        outline: none;
        box-shadow: 0 0 0 2px rgba(11,94,66,0.1);
    }

    .btn-proximas {
        background: var(--verde-100);
        color: var(--cor-primaria);
        border: none;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .btn-proximas:hover {
        background: var(--cor-primaria);
        color: white;
    }

    /* Lista de matrizes */
    .painel-lista {
        flex: 1;
        overflow-y: auto;
        padding: 12px 12px 0;
    }

    .matriz-item {
        display: flex;
        gap: 10px;
        padding: 10px;
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.2s;
        margin-bottom: 6px;
        text-decoration: none;
        color: inherit;
        border: 1px solid transparent;
    }

    .matriz-item:hover, .matriz-item.ativo {
        background: var(--verde-100);
        border-color: var(--cor-primaria);
        color: inherit;
    }

    .matriz-thumb {
        width: 56px;
        height: 56px;
        border-radius: 8px;
        object-fit: cover;
        flex-shrink: 0;
    }

    .matriz-info strong {
        font-size: 0.9rem;
        color: var(--cinza-800);
        display: block;
        line-height: 1.3;
    }

    .matriz-info span {
        font-size: 0.78rem;
        color: var(--cinza-500);
        font-style: italic;
    }

    .matriz-info small {
        font-size: 0.75rem;
        color: var(--cinza-400);
        display: block;
        margin-top: 2px;
    }

    .painel-rodape {
        padding: 12px 16px;
        border-top: 1px solid var(--cinza-100);
        font-size: 0.78rem;
        color: var(--cinza-400);
        text-align: center;
    }

    /* Mapa */
    #mapa-principal {
        flex: 1;
        z-index: 1;
    }

    /* Botão nova matriz flutuante */
    .btn-nova-flutuante {
        position: absolute;
        bottom: 30px;
        right: 20px;
        z-index: 1000;
        background: var(--cor-primaria);
        color: white;
        border: none;
        padding: 14px 22px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.95rem;
        box-shadow: 0 6px 20px rgba(11,94,66,0.4);
        cursor: pointer;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s;
    }

    .btn-nova-flutuante:hover {
        background: var(--cor-primaria-hover);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(11,94,66,0.45);
    }

    .contador-filtro {
        padding: 6px 12px;
        font-size: 0.8rem;
        color: var(--cinza-500);
        text-align: right;
    }

    /* Mobile */
    @media (max-width: 768px) {
        .mapa-layout { flex-direction: column; height: auto; }
        .mapa-painel { width: 100%; height: 280px; border-right: none; border-bottom: 1px solid var(--cinza-200); }
        #mapa-principal { height: calc(100vh - 360px); min-height: 300px; }
        .btn-nova-flutuante { bottom: 16px; right: 16px; }
    }
</style>

<div class="mapa-layout" style="position:relative;">

    <!-- Painel lateral -->
    <div class="mapa-painel">
        <div class="painel-header">
            <h4><i class="fas fa-tree"></i> Banco de Matrizes</h4>
            <div class="filtros">
                <select id="filtro-especie" onchange="aplicarFiltros()">
                    <option value="">Todas as espécies</option>
                    <?php foreach ($especies as $esp): ?>
                        <option value="<?php echo htmlspecialchars($esp); ?>">
                            <?php echo htmlspecialchars($esp); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="btn-proximas" onclick="filtrarProximas()">
                    <i class="fas fa-crosshairs"></i> Matrizes próximas de mim
                </button>
            </div>
        </div>

        <div class="contador-filtro" id="contador">
            <?php echo count($matrizes); ?> matriz<?php echo count($matrizes) !== 1 ? 'es' : ''; ?>
        </div>

        <div class="painel-lista" id="lista-matrizes">
            <?php foreach ($matrizes as $m):
                $nome = $m['especie_nome_popular'] ?: ($m['especie_nome'] ?: 'Espécie não identificada');
                $nome_cient = ($m['especie_nome'] && $m['especie_nome_popular']) ? $m['especie_nome'] : '';
            ?>
            <a href="/penomato_mvp/src/Views/matrizes/ficha.php?id=<?php echo $m['id']; ?>"
               class="matriz-item"
               data-id="<?php echo $m['id']; ?>"
               data-especie="<?php echo htmlspecialchars($m['especie_nome_popular'] ?: $m['especie_nome'] ?: ''); ?>"
               data-lat="<?php echo $m['latitude']; ?>"
               data-lon="<?php echo $m['longitude']; ?>"
               onclick="focarMatriz(event, <?php echo $m['id']; ?>, <?php echo $m['latitude']; ?>, <?php echo $m['longitude']; ?>)">
                <img src="/penomato_mvp/<?php echo htmlspecialchars($m['foto_geral']); ?>"
                     alt="<?php echo htmlspecialchars($nome); ?>"
                     class="matriz-thumb">
                <div class="matriz-info">
                    <strong><?php echo htmlspecialchars($nome); ?></strong>
                    <?php if ($nome_cient): ?>
                    <span><?php echo htmlspecialchars($nome_cient); ?></span>
                    <?php endif; ?>
                    <small>
                        <i class="fas fa-tag me-1"></i><?php echo $m['codigo']; ?>
                        &nbsp;·&nbsp;
                        <?php echo date('d/m/Y', strtotime($m['data_cadastro'])); ?>
                    </small>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="painel-rodape">
            <a href="/penomato_mvp/src/Views/matrizes/index.php">
                <i class="fas fa-arrow-left me-1"></i> Voltar ao módulo
            </a>
        </div>
    </div>

    <!-- Mapa -->
    <div id="mapa-principal"></div>

    <!-- Botão flutuante -->
    <?php if (estaLogado()): ?>
    <a href="/penomato_mvp/src/Views/matrizes/registrar.php" class="btn-nova-flutuante">
        <i class="fas fa-plus"></i> Nova Matriz
    </a>
    <?php else: ?>
    <a href="/penomato_mvp/src/Views/auth/login.php" class="btn-nova-flutuante">
        <i class="fas fa-plus"></i> Nova Matriz
    </a>
    <?php endif; ?>

</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
const dadosMatrizes = <?php echo json_encode($matrizes); ?>;

// ── Mapa ──────────────────────────────────────────────────────
const mapa = L.map('mapa-principal').setView([-20.5, -54.6], 7);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(mapa);

const marcadores = {};

dadosMatrizes.forEach(m => {
    const nome = m.especie_nome_popular || m.especie_nome || 'Espécie não identificada';
    const marker = L.marker([parseFloat(m.latitude), parseFloat(m.longitude)]);

    marker.bindPopup(`
        <div style="min-width:180px">
            <img src="/penomato_mvp/${m.foto_geral}" style="width:100%;height:100px;object-fit:cover;border-radius:6px;margin-bottom:8px">
            <strong style="display:block;font-size:0.95rem">${nome}</strong>
            ${m.especie_nome && m.especie_nome_popular ? `<em style="font-size:0.8rem;color:#666">${m.especie_nome}</em><br>` : ''}
            <small style="color:#888">${m.codigo} · ${m.cadastrador}</small><br>
            <a href="/penomato_mvp/src/Views/matrizes/ficha.php?id=${m.id}"
               style="display:inline-block;margin-top:8px;background:#0b5e42;color:white;padding:5px 14px;border-radius:6px;font-size:0.8rem;text-decoration:none">
               Ver ficha
            </a>
        </div>
    `);

    marker.addTo(mapa);
    marcadores[m.id] = marker;
});

// ── Filtro por espécie ────────────────────────────────────────
function aplicarFiltros() {
    const especieSel = document.getElementById('filtro-especie').value.toLowerCase();
    const itens = document.querySelectorAll('.matriz-item');
    let visiveis = 0;

    itens.forEach(item => {
        const especie = (item.dataset.especie || '').toLowerCase();
        const ok = !especieSel || especie === especieSel;
        item.style.display = ok ? 'flex' : 'none';
        const mid = parseInt(item.dataset.id);
        if (marcadores[mid]) {
            if (ok) {
                marcadores[mid].addTo(mapa);
                visiveis++;
            } else {
                mapa.removeLayer(marcadores[mid]);
            }
        }
    });

    document.getElementById('contador').textContent =
        `${visiveis} matriz${visiveis !== 1 ? 'es' : ''}`;
}

// ── Focar no mapa ao clicar no item da lista ──────────────────
function focarMatriz(e, id, lat, lon) {
    e.preventDefault();
    mapa.setView([lat, lon], 16, { animate: true });
    if (marcadores[id]) marcadores[id].openPopup();
    document.querySelectorAll('.matriz-item').forEach(el => el.classList.remove('ativo'));
    document.querySelector(`.matriz-item[data-id="${id}"]`)?.classList.add('ativo');
}

// ── Matrizes próximas ─────────────────────────────────────────
function filtrarProximas() {
    if (!navigator.geolocation) {
        alert('GPS não disponível neste dispositivo.');
        return;
    }
    navigator.geolocation.getCurrentPosition(pos => {
        const lat = pos.coords.latitude;
        const lon = pos.coords.longitude;

        // Marcador da posição atual
        L.circleMarker([lat, lon], {
            radius: 10, color: '#0066ff', fillColor: '#0066ff', fillOpacity: 0.5
        }).addTo(mapa).bindPopup('Você está aqui').openPopup();

        mapa.setView([lat, lon], 13, { animate: true });

        // Ordena por distância
        const itens = Array.from(document.querySelectorAll('.matriz-item'));
        itens.sort((a, b) => {
            const da = distancia(lat, lon, parseFloat(a.dataset.lat), parseFloat(a.dataset.lon));
            const db = distancia(lat, lon, parseFloat(b.dataset.lat), parseFloat(b.dataset.lon));
            return da - db;
        });

        const lista = document.getElementById('lista-matrizes');
        itens.forEach(el => lista.appendChild(el));
    });
}

function distancia(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2)**2 + Math.cos(lat1*Math.PI/180) * Math.cos(lat2*Math.PI/180) * Math.sin(dLon/2)**2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
}
</script>

</body>
</html>
