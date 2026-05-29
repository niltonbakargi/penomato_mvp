<?php
// ============================================================
// BANCO DE MATRIZES — FICHA DA MATRIZ
// ============================================================

require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../../Controllers/auth/verificar_acesso.php';

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: /penomato_mvp/src/Views/matrizes/mapa.php');
    exit;
}

$matriz = buscarUm(
    "SELECT m.*, u.nome AS nome_cadastrador
     FROM matrizes m
     JOIN usuarios u ON u.id = m.cadastrado_por
     WHERE m.id = ? AND m.status = 'ativo'",
    [$id]
);

if (!$matriz) {
    header('Location: /penomato_mvp/src/Views/matrizes/mapa.php');
    exit;
}

$fotos_partes = buscarTodos(
    "SELECT mf.*, u.nome AS nome_enviador
     FROM matrizes_fotos mf
     JOIN usuarios u ON u.id = mf.enviada_por
     WHERE mf.matriz_id = ?
     ORDER BY mf.parte, mf.data_envio",
    [$id]
);

$comentarios = buscarTodos(
    "SELECT mc.*, u.nome AS nome_usuario
     FROM matrizes_comentarios mc
     JOIN usuarios u ON u.id = mc.usuario_id
     WHERE mc.matriz_id = ?
     ORDER BY mc.data ASC",
    [$id]
);

// Agrupa fotos por parte
$fotos_por_parte = [];
foreach ($fotos_partes as $f) {
    $fotos_por_parte[$f['parte']][] = $f;
}

$partes = ['folha', 'flor', 'fruto', 'casca', 'semente'];
$icones_parte = [
    'folha'   => 'fas fa-leaf',
    'flor'    => 'fas fa-spa',
    'fruto'   => 'fas fa-apple-alt',
    'casca'   => 'fas fa-tree',
    'semente' => 'fas fa-seedling',
];

$nome_exibir = $matriz['especie_nome'] ?: $matriz['especie_nome_popular'] ?: 'Espécie não identificada';
$titulo_pagina = "Matriz {$matriz['codigo']} — {$nome_exibir}";

include __DIR__ . '/../includes/cabecalho.php';
?>

<style>
    .ficha-wrap {
        max-width: 720px;
        margin: 30px auto 60px;
        padding: 0 16px;
    }

    .ficha-foto-principal {
        width: 100%;
        max-height: 380px;
        object-fit: cover;
        border-radius: 16px;
        margin-bottom: 20px;
    }

    .ficha-codigo {
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--cinza-400);
        margin-bottom: 4px;
    }

    .ficha-nome {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--cinza-800);
        margin-bottom: 4px;
    }

    .ficha-nome-cientifico {
        font-size: 1rem;
        font-style: italic;
        color: var(--cinza-600);
        margin-bottom: 16px;
    }

    .ficha-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 24px;
    }

    .ficha-meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        color: var(--cinza-600);
        background: var(--cinza-100);
        padding: 6px 12px;
        border-radius: 20px;
    }

    .ficha-meta-item i {
        color: var(--cor-primaria);
    }

    /* Seções */
    .card-secao {
        background: white;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    }

    .card-secao h5 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--cor-primaria);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Mapa */
    #mapa-ficha {
        height: 240px;
        border-radius: 12px;
        overflow: hidden;
    }

    /* Fotos de partes */
    .partes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 12px;
    }

    .parte-card {
        border-radius: 12px;
        overflow: hidden;
        background: var(--cinza-100);
        text-align: center;
    }

    .parte-card img {
        width: 100%;
        height: 110px;
        object-fit: cover;
        cursor: pointer;
        transition: transform 0.3s;
    }

    .parte-card img:hover {
        transform: scale(1.05);
    }

    .parte-card .parte-label {
        padding: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--cinza-600);
        text-transform: capitalize;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    .parte-vazia {
        border: 2px dashed var(--cinza-300);
    }

    .parte-vazia .parte-placeholder {
        height: 110px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--cinza-300);
        font-size: 1.8rem;
    }

    /* Botão adicionar foto de parte */
    .btn-add-parte {
        background: var(--verde-100);
        color: var(--cor-primaria);
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-add-parte:hover {
        background: var(--cor-primaria);
        color: white;
    }

    /* Comentários */
    .comentario-item {
        padding: 14px 0;
        border-bottom: 1px solid var(--cinza-100);
    }

    .comentario-item:last-child {
        border-bottom: none;
    }

    .comentario-autor {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--cinza-800);
    }

    .comentario-data {
        font-size: 0.78rem;
        color: var(--cinza-400);
        margin-left: 8px;
    }

    .comentario-texto {
        margin-top: 4px;
        font-size: 0.9rem;
        color: var(--cinza-700);
        line-height: 1.5;
    }

    .form-comentario textarea {
        border-radius: 10px;
        border-color: var(--cinza-200);
        font-size: 0.9rem;
        resize: none;
    }

    .form-comentario textarea:focus {
        border-color: var(--cor-primaria);
        box-shadow: 0 0 0 3px rgba(11,94,66,0.1);
    }

    .btn-comentar {
        background: var(--cor-primaria);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-comentar:hover {
        background: var(--cor-primaria-hover);
    }

    /* Lightbox simples */
    .lightbox-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.9);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        cursor: zoom-out;
    }

    .lightbox-overlay.ativo {
        display: flex;
    }

    .lightbox-overlay img {
        max-width: 95vw;
        max-height: 90vh;
        border-radius: 8px;
        object-fit: contain;
    }
</style>

<!-- Lightbox -->
<div class="lightbox-overlay" id="lightbox" onclick="fecharLightbox()">
    <img src="" id="lightbox-img" alt="">
</div>

<div class="ficha-wrap">

    <!-- Foto principal -->
    <img src="/penomato_mvp/<?php echo htmlspecialchars($matriz['foto_geral']); ?>"
         alt="Foto da matriz <?php echo htmlspecialchars($matriz['codigo']); ?>"
         class="ficha-foto-principal"
         onclick="abrirLightbox(this.src)">

    <!-- Identificação -->
    <div class="ficha-codigo">Matriz <?php echo htmlspecialchars($matriz['codigo']); ?></div>
    <div class="ficha-nome">
        <?php echo htmlspecialchars($matriz['especie_nome_popular'] ?: ($matriz['especie_nome'] ?: 'Espécie não identificada')); ?>
    </div>
    <?php if ($matriz['especie_nome']): ?>
    <div class="ficha-nome-cientifico"><?php echo htmlspecialchars($matriz['especie_nome']); ?></div>
    <?php endif; ?>

    <div class="ficha-meta">
        <div class="ficha-meta-item">
            <i class="fas fa-user"></i>
            <?php echo htmlspecialchars($matriz['nome_cadastrador']); ?>
        </div>
        <div class="ficha-meta-item">
            <i class="fas fa-calendar"></i>
            <?php echo date('d/m/Y', strtotime($matriz['data_cadastro'])); ?>
        </div>
        <div class="ficha-meta-item">
            <i class="fas fa-map-marker-alt"></i>
            <?php echo number_format($matriz['latitude'], 6); ?>, <?php echo number_format($matriz['longitude'], 6); ?>
        </div>
    </div>

    <?php if ($matriz['observacoes']): ?>
    <div class="card-secao mb-3" style="background: var(--verde-50); border: 1px solid var(--verde-100);">
        <p class="mb-0" style="font-size:0.95rem; color: var(--cinza-700);">
            <i class="fas fa-quote-left text-success me-2"></i>
            <?php echo nl2br(htmlspecialchars($matriz['observacoes'])); ?>
        </p>
    </div>
    <?php endif; ?>

    <!-- Mapa -->
    <div class="card-secao">
        <h5><i class="fas fa-map"></i> Localização</h5>
        <div id="mapa-ficha"></div>
        <p class="mt-2 mb-0 text-muted small text-center">
            <a href="https://maps.google.com/?q=<?php echo $matriz['latitude']; ?>,<?php echo $matriz['longitude']; ?>"
               target="_blank" class="text-success">
                <i class="fas fa-external-link-alt me-1"></i>Abrir no Google Maps
            </a>
        </p>
    </div>

    <!-- Fotos de partes -->
    <div class="card-secao">
        <h5><i class="fas fa-images"></i> Fotos das Partes</h5>
        <div class="partes-grid">
            <?php foreach ($partes as $parte): ?>
                <?php if (!empty($fotos_por_parte[$parte])): ?>
                    <?php $foto = $fotos_por_parte[$parte][0]; ?>
                    <div class="parte-card">
                        <img src="/penomato_mvp/<?php echo htmlspecialchars($foto['caminho_foto']); ?>"
                             alt="<?php echo ucfirst($parte); ?>"
                             onclick="abrirLightbox(this.src)">
                        <div class="parte-label">
                            <i class="<?php echo $icones_parte[$parte]; ?>"></i>
                            <?php echo ucfirst($parte); ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="parte-card parte-vazia">
                        <div class="parte-placeholder">
                            <i class="<?php echo $icones_parte[$parte]; ?>"></i>
                        </div>
                        <div class="parte-label" style="color:var(--cinza-300)">
                            <?php echo ucfirst($parte); ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <?php if (estaLogado()): ?>
        <div class="mt-3">
            <a href="/penomato_mvp/src/Views/matrizes/adicionar_foto.php?id=<?php echo $id; ?>"
               class="btn-add-parte">
                <i class="fas fa-plus"></i> Adicionar foto de parte
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Comentários -->
    <div class="card-secao">
        <h5><i class="fas fa-comments"></i> Comentários (<?php echo count($comentarios); ?>)</h5>

        <?php if (empty($comentarios)): ?>
            <p class="text-muted small mb-3">Nenhum comentário ainda. Sabe o nome desta árvore? Comente!</p>
        <?php else: ?>
            <?php foreach ($comentarios as $c): ?>
            <div class="comentario-item">
                <div>
                    <span class="comentario-autor"><?php echo htmlspecialchars($c['nome_usuario']); ?></span>
                    <span class="comentario-data"><?php echo date('d/m/Y H:i', strtotime($c['data'])); ?></span>
                </div>
                <div class="comentario-texto">
                    <?php echo nl2br(htmlspecialchars($c['texto'])); ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (estaLogado()): ?>
        <form action="/penomato_mvp/src/Controllers/matrizes/processar_comentario.php"
              method="POST" class="form-comentario mt-3">
            <input type="hidden" name="matriz_id" value="<?php echo $id; ?>">
            <textarea class="form-control mb-2" name="texto" rows="2"
                      placeholder="Escreva um comentário ou ajude a identificar esta espécie..." required></textarea>
            <button type="submit" class="btn-comentar">
                <i class="fas fa-paper-plane me-2"></i>Comentar
            </button>
        </form>
        <?php else: ?>
        <p class="text-muted small mt-3">
            <a href="/penomato_mvp/src/Views/auth/login.php">Faça login</a> para comentar.
        </p>
        <?php endif; ?>
    </div>

    <div class="text-center">
        <a href="/penomato_mvp/src/Views/matrizes/mapa.php" class="text-muted small">
            <i class="fas fa-arrow-left me-1"></i> Ver todas as matrizes no mapa
        </a>
    </div>

</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ── Mapa ──────────────────────────────────────────────────────
const lat = <?php echo $matriz['latitude']; ?>;
const lon = <?php echo $matriz['longitude']; ?>;

const mapa = L.map('mapa-ficha').setView([lat, lon], 16);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(mapa);

L.marker([lat, lon]).addTo(mapa)
    .bindPopup('<strong><?php echo htmlspecialchars(addslashes($nome_exibir)); ?></strong><br>Matriz <?php echo $matriz['codigo']; ?>')
    .openPopup();

// ── Lightbox ──────────────────────────────────────────────────
function abrirLightbox(src) {
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox').classList.add('ativo');
}

function fecharLightbox() {
    document.getElementById('lightbox').classList.remove('ativo');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') fecharLightbox();
});
</script>

<?php include __DIR__ . '/../includes/rodape.php'; ?>
