<?php
// ============================================================
// MAPA DE EXEMPLARES — ESPECIALISTA
// ============================================================
session_start();
require_once __DIR__ . '/../../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /penomato_mvp/src/Views/auth/login.php');
    exit;
}

$usuario_id = (int)$_SESSION['usuario_id'];

// ── Buscar todos os exemplares deste especialista ────────────────────────────
$stmt = $pdo->prepare("
    SELECT
        e.id, e.codigo, e.status,
        e.cidade, e.estado, e.bioma,
        e.latitude, e.longitude,
        e.numero_etiqueta, e.data_cadastro,
        e.motivo_rejeicao,
        ea.nome_cientifico AS especie_nome,
        ea.id AS especie_id,
        u.nome AS colaborador_nome,
        e.foto_identificacao
    FROM exemplares e
    JOIN especies_administrativo ea ON ea.id = e.especie_id
    JOIN usuarios u ON u.id = e.cadastrado_por
    WHERE e.especialista_id = ?
    ORDER BY e.data_cadastro DESC
");
$stmt->execute([$usuario_id]);
$exemplares = $stmt->fetchAll();

// Separar por status
$aguardando = array_filter($exemplares, fn($x) => $x['status'] === 'aguardando_revisao');
$aprovados  = array_filter($exemplares, fn($x) => $x['status'] === 'aprovado');
$rejeitados = array_filter($exemplares, fn($x) => $x['status'] === 'rejeitado');

// Montar JSON para o mapa (apenas os que têm coordenadas)
$markers = [];
foreach ($exemplares as $ex) {
    if ($ex['latitude'] === null || $ex['longitude'] === null) continue;
    $foto_url = $ex['foto_identificacao']
        ? '/penomato_mvp/' . $ex['foto_identificacao']
        : null;
    $markers[] = [
        'id'        => $ex['id'],
        'codigo'    => $ex['codigo'],
        'status'    => $ex['status'],
        'especie'   => $ex['especie_nome'],
        'especie_id'=> $ex['especie_id'],
        'cidade'    => $ex['cidade'],
        'estado'    => $ex['estado'],
        'bioma'     => $ex['bioma'],
        'colaborador' => $ex['colaborador_nome'],
        'lat'       => (float)$ex['latitude'],
        'lng'       => (float)$ex['longitude'],
        'foto'      => $foto_url,
        'data'      => date('d/m/Y', strtotime($ex['data_cadastro'])),
    ];
}
$markersJson = json_encode($markers, JSON_UNESCAPED_UNICODE);

$statusLabel = ['aguardando_revisao' => 'Aguardando', 'aprovado' => 'Aprovado', 'rejeitado' => 'Rejeitado'];
$statusBadge = ['aguardando_revisao' => 'warning', 'aprovado' => 'success', 'rejeitado' => 'danger'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mapa de Exemplares — Penomato</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
<style>
  body { background: var(--cinza-50); }
  #mapa-principal { height: 480px; border-radius: 12px; z-index: 0; }
  .stat-card { border-radius: 12px; border: none; }
  .exemplar-row { cursor: default; transition: background .15s; }
  .exemplar-row:hover { background: #f0f7ff; }
  .badge-aguardando { background: var(--aviso-cor); color: #000; }
  .badge-aprovado    { background: #198754; }
  .badge-rejeitado   { background: var(--perigo-cor); }
  .legend-dot { display:inline-block; width:14px; height:14px; border-radius:50%; margin-right:6px; vertical-align:middle; }
  .leaflet-popup-content { font-size: .85rem; min-width: 180px; }
  .popup-foto { width:100%; height:100px; object-fit:cover; border-radius:6px; margin-bottom:6px; }
</style>
</head>
<body>

<?php include __DIR__ . '/../partials/navbar.php'; ?>

<div class="container-fluid py-4" style="max-width:1200px">

  <!-- Cabeçalho -->
  <div class="d-flex align-items-center gap-3 mb-4">
    <a href="/penomato_mvp/src/Views/revisor/revisar_exemplar.php" class="btn btn-outline-secondary btn-sm">
      <i class="fa fa-arrow-left"></i>
    </a>
    <div>
      <h4 class="mb-0"><i class="fa fa-map-marked-alt text-primary me-2"></i>Mapa dos Exemplares</h4>
      <small class="text-muted">Todos os exemplares atribuídos a você</small>
    </div>
  </div>

  <!-- Cards de resumo -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card stat-card text-center py-3 shadow-sm">
        <div class="fs-2 fw-bold text-primary"><?= count($exemplares) ?></div>
        <div class="small text-muted">Total</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card stat-card text-center py-3 shadow-sm border-warning">
        <div class="fs-2 fw-bold text-warning"><?= count($aguardando) ?></div>
        <div class="small text-muted">Aguardando</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card stat-card text-center py-3 shadow-sm border-success">
        <div class="fs-2 fw-bold text-success"><?= count($aprovados) ?></div>
        <div class="small text-muted">Aprovados</div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card stat-card text-center py-3 shadow-sm border-danger">
        <div class="fs-2 fw-bold text-danger"><?= count($rejeitados) ?></div>
        <div class="small text-muted">Rejeitados</div>
      </div>
    </div>
  </div>

  <!-- Mapa principal -->
  <div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span><i class="fa fa-globe-americas me-2"></i>Distribuição geográfica</span>
      <div class="d-flex gap-3 small">
        <span><span class="legend-dot" style="background:#f59e0b"></span>Aguardando</span>
        <span><span class="legend-dot" style="background:#22c55e"></span>Aprovado</span>
        <span><span class="legend-dot" style="background:#ef4444"></span>Rejeitado</span>
      </div>
    </div>
    <div class="card-body p-2">
      <?php if (count($markers) === 0): ?>
        <div class="text-center text-muted py-5">
          <i class="fa fa-map-marked-alt fa-3x mb-2 opacity-25"></i>
          <p>Nenhum exemplar com coordenadas GPS cadastrado.</p>
        </div>
      <?php else: ?>
        <div id="mapa-principal"></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Tabela de exemplares -->
  <div class="card shadow-sm">
    <div class="card-header">
      <i class="fa fa-list me-2"></i>Lista de Exemplares
      <span class="badge bg-secondary ms-2"><?= count($exemplares) ?></span>
    </div>
    <?php if (empty($exemplares)): ?>
      <div class="card-body text-center text-muted py-5">
        <i class="fa fa-inbox fa-3x mb-2 opacity-25"></i>
        <p>Nenhum exemplar atribuído a você ainda.</p>
      </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Código</th>
            <th>Espécie</th>
            <th>Local</th>
            <th>Bioma</th>
            <th>Colaborador</th>
            <th>Cadastro</th>
            <th>Status</th>
            <th>Ação</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($exemplares as $ex): ?>
          <tr class="exemplar-row"
              data-id="<?= $ex['id'] ?>"
              data-lat="<?= $ex['latitude'] ?>"
              data-lng="<?= $ex['longitude'] ?>">
            <td>
              <code><?= htmlspecialchars($ex['codigo']) ?></code>
              <?php if ($ex['numero_etiqueta']): ?>
                <br><small class="text-muted"><?= htmlspecialchars($ex['numero_etiqueta']) ?></small>
              <?php endif; ?>
            </td>
            <td>
              <em class="small"><?= htmlspecialchars($ex['especie_nome']) ?></em>
            </td>
            <td class="small">
              <?= htmlspecialchars($ex['cidade']) ?>, <?= htmlspecialchars($ex['estado']) ?>
            </td>
            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($ex['bioma']) ?></span></td>
            <td class="small"><?= htmlspecialchars($ex['colaborador_nome']) ?></td>
            <td class="small"><?= date('d/m/Y', strtotime($ex['data_cadastro'])) ?></td>
            <td>
              <span class="badge badge-<?= $ex['status'] ?>">
                <?= $statusLabel[$ex['status']] ?>
              </span>
            </td>
            <td>
              <?php if ($ex['latitude'] && $ex['longitude']): ?>
                <button class="btn btn-outline-primary btn-sm btn-localizar"
                        data-lat="<?= $ex['latitude'] ?>"
                        data-lng="<?= $ex['longitude'] ?>"
                        data-id="<?= $ex['id'] ?>"
                        title="Ver no mapa">
                  <i class="fa fa-crosshairs"></i>
                </button>
              <?php endif; ?>
              <?php if ($ex['status'] === 'aguardando_revisao'): ?>
                <a href="/penomato_mvp/src/Views/revisor/revisar_exemplar.php?filtro=aguardando_revisao#exemplar-<?= $ex['id'] ?>"
                   class="btn btn-warning btn-sm" title="Revisar">
                  <i class="fa fa-eye"></i>
                </a>
              <?php endif; ?>
            </td>
          </tr>
          <?php if ($ex['status'] === 'rejeitado' && $ex['motivo_rejeicao']): ?>
          <tr class="table-danger">
            <td colspan="8" class="small ps-4 py-1">
              <i class="fa fa-comment-slash me-1 text-danger"></i>
              <strong>Motivo da rejeição:</strong> <?= htmlspecialchars($ex['motivo_rejeicao']) ?>
            </td>
          </tr>
          <?php endif; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

</div><!-- /container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const markers = <?= $markersJson ?>;

const corStatus = {
    aguardando_revisao: '#f59e0b',
    aprovado:           '#22c55e',
    rejeitado:          '#ef4444'
};

const labelStatus = {
    aguardando_revisao: 'Aguardando',
    aprovado:           'Aprovado',
    rejeitado:          'Rejeitado'
};

let mapa = null;
const leafletMarkers = {};

if (markers.length > 0) {
    mapa = L.map('mapa-principal');

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18
    }).addTo(mapa);

    const bounds = [];

    markers.forEach(m => {
        const cor = corStatus[m.status] || '#6b7280';

        const icone = L.divIcon({
            className: '',
            html: `<div style="
                width:18px; height:18px; border-radius:50%;
                background:${cor}; border:2px solid #fff;
                box-shadow:0 1px 4px rgba(0,0,0,.4);">
            </div>`,
            iconSize: [18, 18],
            iconAnchor: [9, 9],
            popupAnchor: [0, -10]
        });

        let popupHtml = '';
        if (m.foto) {
            popupHtml += `<img src="${m.foto}" class="popup-foto" alt="Identificação">`;
        }
        popupHtml += `<strong>${m.codigo}</strong>
            <span style="
                display:inline-block; padding:1px 6px; border-radius:10px;
                background:${cor}; color:${m.status==='aguardando_revisao'?'#000':'#fff'};
                font-size:.75rem; margin-left:4px;">
                ${labelStatus[m.status]}
            </span><br>
            <em style="font-size:.8rem">${m.especie}</em><br>
            <i class="fa fa-map-pin"></i> ${m.cidade}, ${m.estado}<br>
            <i class="fa fa-leaf"></i> ${m.bioma}<br>
            <i class="fa fa-user"></i> ${m.colaborador}<br>
            <i class="fa fa-calendar"></i> ${m.data}`;

        if (m.status === 'aguardando_revisao') {
            popupHtml += `<br><a href="/penomato_mvp/src/Views/revisor/revisar_exemplar.php?filtro=aguardando_revisao#exemplar-${m.id}"
                class="btn btn-warning btn-sm mt-2 w-100">
                <i class="fa fa-eye"></i> Revisar
            </a>`;
        }

        const marker = L.marker([m.lat, m.lng], { icon: icone })
            .addTo(mapa)
            .bindPopup(popupHtml);

        leafletMarkers[m.id] = marker;
        bounds.push([m.lat, m.lng]);
    });

    if (bounds.length === 1) {
        mapa.setView(bounds[0], 12);
    } else {
        mapa.fitBounds(bounds, { padding: [40, 40] });
    }
}

// Botões "localizar" na tabela
document.querySelectorAll('.btn-localizar').forEach(btn => {
    btn.addEventListener('click', () => {
        const lat = parseFloat(btn.dataset.lat);
        const lng = parseFloat(btn.dataset.lng);
        const id  = parseInt(btn.dataset.id);
        if (!mapa) return;
        mapa.setView([lat, lng], 14, { animate: true });
        if (leafletMarkers[id]) leafletMarkers[id].openPopup();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});
</script>
</body>
</html>
