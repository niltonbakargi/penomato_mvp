<?php
// ============================================================
// REVISAR EXEMPLARES — PAINEL DO ESPECIALISTA
// ============================================================
session_start();
require_once __DIR__ . '/../../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /penomato_mvp/src/Views/auth/login.php');
    exit;
}

$usuario_id   = (int)$_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Especialista';

// ── Filtro de status ──────────────────────────────────────────────────────────
$filtro = $_GET['filtro'] ?? 'aguardando_revisao';
$filtros_validos = ['aguardando_revisao', 'aprovado', 'rejeitado', 'todos'];
if (!in_array($filtro, $filtros_validos)) $filtro = 'aguardando_revisao';

$where_status = $filtro === 'todos' ? '' : "AND ex.status = ?";
$params = $filtro === 'todos' ? [$usuario_id] : [$usuario_id, $filtro];

// ── Buscar exemplares do especialista ─────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT
        ex.id, ex.codigo, ex.status, ex.numero_etiqueta,
        ex.foto_identificacao, ex.latitude, ex.longitude,
        ex.cidade, ex.estado, ex.bioma, ex.descricao_local,
        ex.data_cadastro, ex.data_revisao, ex.motivo_rejeicao,
        ea.nome_cientifico,
        u.nome AS cadastrado_por_nome
    FROM exemplares ex
    JOIN especies_administrativo ea ON ea.id = ex.especie_id
    JOIN usuarios u ON u.id = ex.cadastrado_por
    WHERE ex.especialista_id = ?
    {$where_status}
    ORDER BY
        CASE ex.status
            WHEN 'aguardando_revisao' THEN 1
            WHEN 'rejeitado'          THEN 2
            WHEN 'aprovado'           THEN 3
        END,
        ex.data_cadastro DESC
");
$stmt->execute($params);
$exemplares = $stmt->fetchAll();

// ── Contagens para badges dos filtros ────────────────────────────────────────
$stmt_cnt = $pdo->prepare("
    SELECT status, COUNT(*) as total
    FROM exemplares
    WHERE especialista_id = ?
    GROUP BY status
");
$stmt_cnt->execute([$usuario_id]);
$contagens = ['aguardando_revisao' => 0, 'aprovado' => 0, 'rejeitado' => 0];
foreach ($stmt_cnt->fetchAll() as $row) {
    $contagens[$row['status']] = (int)$row['total'];
}
$total_todos = array_sum($contagens);

$mensagem_sucesso = isset($_GET['sucesso']) ? urldecode($_GET['sucesso']) : '';
$mensagem_erro    = isset($_GET['erro'])    ? urldecode($_GET['erro'])    : '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar Exemplares — Penomato</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">

    <style>
        body {
            background: var(--cinza-50); color: var(--cinza-800); padding: 30px 20px;
        }
        .container { max-width: 1100px; margin: 0 auto; }

        /* Cabeçalho */
        .cabecalho {
            background: var(--branco); padding: 26px 36px;
            border-radius: 12px 12px 0 0; border-bottom: 4px solid var(--cor-primaria);
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 12px;
        }
        .cabecalho h1 { color: var(--cor-primaria); font-size: 1.7rem; font-weight: 600; }
        .cabecalho .sub { color: var(--cinza-500); font-style: italic; font-size: .88rem; margin-top: 3px; }
        .user-pill {
            background: var(--cinza-50); padding: 7px 16px; border-radius: 40px;
            display: flex; align-items: center; gap: 9px; font-size: .88rem;
        }
        .user-pill i { color: var(--cor-primaria); }
        .btn-sair { color: var(--perigo-cor); text-decoration: none; padding: 3px 8px; border-radius: 20px; transition: .2s; }
        .btn-sair:hover { background: var(--perigo-cor); color: var(--branco); }

        /* Card principal */
        .card-principal {
            background: var(--branco); padding: 30px 36px;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,.05);
        }

        /* Alertas */
        .alerta {
            padding: 13px 16px; border-radius: 8px; margin-bottom: 20px;
            display: flex; align-items: flex-start; gap: 10px; font-size: .9rem;
        }
        .alerta--sucesso { background: var(--sucesso-fundo); color: var(--sucesso-texto); border-left: 4px solid var(--sucesso-cor); }
        .alerta--perigo { background: var(--perigo-fundo); color: var(--perigo-texto); border-left: 4px solid var(--perigo-cor); }

        /* Filtros */
        .filtros {
            display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 26px;
        }
        .btn-filtro {
            padding: 8px 20px; border-radius: 30px; border: 2px solid var(--cinza-200);
            background: var(--branco); font-size: .875rem; font-weight: 600;
            cursor: pointer; text-decoration: none; color: #555;
            display: inline-flex; align-items: center; gap: 8px; transition: .2s;
        }
        .btn-filtro:hover { border-color: var(--cor-primaria); color: var(--cor-primaria); }
        .btn-filtro.ativo { background: var(--cor-primaria); border-color: var(--cor-primaria); color: var(--branco); }
        .cnt-badge {
            background: rgba(255,255,255,.3); padding: 1px 7px;
            border-radius: 10px; font-size: .75rem;
        }
        .btn-filtro:not(.ativo) .cnt-badge { background: #f0f0f0; color: var(--cinza-500); }

        /* Mapa geral */
        .mapa-geral-wrap { margin-bottom: 28px; }
        .mapa-geral-wrap h4 {
            font-size: .95rem; font-weight: 700; color: var(--cinza-600);
            margin-bottom: 12px; display: flex; align-items: center; gap: 8px;
        }
        #mapa-geral {
            height: 280px; border-radius: 10px;
            border: 2px solid var(--cinza-200); z-index: 1;
        }

        /* Lista de exemplares */
        .exemplar-card {
            border: 2px solid #e8edf2; border-radius: 10px;
            margin-bottom: 18px; overflow: hidden; transition: .2s;
        }
        .exemplar-card:hover { border-color: #c0d0de; box-shadow: 0 4px 12px rgba(0,0,0,.07); }
        .exemplar-card.aguardando { border-left: 5px solid var(--aviso-cor); }
        .exemplar-card.aprovado   { border-left: 5px solid var(--cor-primaria); }
        .exemplar-card.rejeitado  { border-left: 5px solid var(--perigo-cor); }

        .exemplar-header {
            padding: 14px 20px; background: var(--cinza-50);
            border-bottom: 1px solid #e8edf2;
            display: flex; justify-content: space-between;
            align-items: center; flex-wrap: wrap; gap: 10px;
        }
        .exemplar-codigo {
            font-family: 'Courier New', monospace; font-size: 1.3rem;
            font-weight: 900; color: var(--cor-primaria); letter-spacing: 2px;
        }
        .exemplar-especie { font-style: italic; font-size: .92rem; color: var(--cinza-600); margin-top: 2px; }
        .status-pill {
            padding: 5px 14px; border-radius: 20px;
            font-size: .78rem; font-weight: 700; color: var(--branco);
        }
        .pill-aguardando { background: var(--aviso-cor); }
        .pill-aprovado   { background: var(--cor-primaria); }
        .pill-rejeitado  { background: var(--perigo-cor); }

        .exemplar-corpo {
            padding: 20px;
            display: grid; grid-template-columns: 180px 1fr;
            gap: 20px;
        }
        @media (max-width: 640px) {
            .exemplar-corpo { grid-template-columns: 1fr; }
        }

        /* Foto de identificação */
        .foto-id {
            width: 100%; height: 160px; object-fit: cover;
            border-radius: 8px; border: 2px solid var(--cinza-200);
        }
        .foto-placeholder {
            width: 100%; height: 160px; border-radius: 8px;
            border: 2px dashed #d0d8e0; background: var(--cinza-50);
            display: flex; align-items: center; justify-content: center;
            color: var(--cinza-400); font-size: 2rem;
        }

        /* Metadados */
        .meta-grid {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 10px 20px;
        }
        @media (max-width: 500px) { .meta-grid { grid-template-columns: 1fr; } }
        .meta-item label { font-size: .72rem; font-weight: 700; color: var(--cinza-400); text-transform: uppercase; }
        .meta-item span  { font-size: .9rem; color: #2c3e50; display: block; margin-top: 2px; }

        /* Mini mapa */
        .mini-mapa {
            height: 120px; border-radius: 8px;
            border: 2px solid var(--cinza-200); margin-top: 12px; z-index: 1;
        }

        /* Área de ações */
        .exemplar-acoes {
            padding: 16px 20px; background: var(--cinza-50);
            border-top: 1px solid #e8edf2;
            display: flex; align-items: flex-start; gap: 12px; flex-wrap: wrap;
        }
        .btn-aprovar {
            background: var(--cor-primaria); color: var(--branco); border: none;
            padding: 10px 24px; border-radius: 30px; font-weight: 700;
            font-size: .9rem; cursor: pointer; display: inline-flex;
            align-items: center; gap: 8px; transition: .2s;
        }
        .btn-aprovar:hover { background: var(--cor-primaria-hover); }

        .rejeitar-wrap { flex: 1; min-width: 260px; }
        .rejeitar-wrap textarea {
            width: 100%; padding: 9px 12px; border: 2px solid var(--cinza-200);
            border-radius: 8px; font-size: .875rem; resize: none;
            min-height: 60px; margin-bottom: 8px;
        }
        .rejeitar-wrap textarea:focus { outline: none; border-color: var(--perigo-cor); }
        .btn-rejeitar {
            background: var(--perigo-cor); color: var(--branco); border: none;
            padding: 9px 20px; border-radius: 30px; font-weight: 700;
            font-size: .875rem; cursor: pointer; display: inline-flex;
            align-items: center; gap: 7px; transition: .2s;
        }
        .btn-rejeitar:hover { background: #b02a37; }

        .motivo-rejeicao {
            background: var(--perigo-fundo); border-left: 3px solid var(--perigo-cor);
            padding: 10px 14px; border-radius: 6px; font-size: .875rem;
            color: var(--perigo-texto); margin-top: 4px;
        }

        /* Placeholder sem exemplares */
        .placeholder {
            text-align: center; padding: 60px 20px;
            background: var(--cinza-50); border-radius: 10px; color: var(--cinza-500);
        }
        .placeholder i { font-size: 3.5rem; color: var(--cinza-300); margin-bottom: 16px; display: block; }

        /* Rodapé */
        .rodape { display: flex; justify-content: center; margin-top: 30px; }
        .btn-voltar {
            background: var(--cinza-500); color: var(--branco); text-decoration: none;
            padding: 11px 28px; border-radius: 40px; font-weight: 600;
            display: inline-flex; align-items: center; gap: 8px; transition: .2s;
        }
        .btn-voltar:hover { background: var(--cinza-600); color: var(--branco); }
    </style>
</head>
<body>
<div class="container">

    <!-- Cabeçalho -->
    <div class="cabecalho">
        <div>
            <h1>🔬 Revisar Exemplares</h1>
            <div class="sub">Exemplares aguardando sua avaliação antes do registro fotográfico</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="/penomato_mvp/src/Views/revisor/mapa_exemplares.php"
               class="btn btn-outline-primary btn-sm"
               title="Ver mapa de todos os exemplares">
                <i class="fas fa-map-marked-alt"></i> Mapa
            </a>
            <div class="user-pill">
                <i class="fas fa-user-circle"></i>
                <span><?= htmlspecialchars($usuario_nome) ?></span>
                <a href="/penomato_mvp/src/Controllers/auth/logout_controlador.php"
                   class="btn-sair" onclick="return confirm('Deseja sair?')">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="card-principal">

        <!-- Mensagens -->
        <?php if ($mensagem_sucesso): ?>
            <div class="alerta alerta--sucesso">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($mensagem_sucesso) ?></span>
            </div>
        <?php endif; ?>
        <?php if ($mensagem_erro): ?>
            <div class="alerta alerta--perigo">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($mensagem_erro) ?></span>
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="filtros">
            <?php
            $tabs = [
                'aguardando_revisao' => ['label' => 'Aguardando', 'icon' => 'fa-clock',       'cnt' => $contagens['aguardando_revisao']],
                'aprovado'           => ['label' => 'Aprovados',  'icon' => 'fa-check-circle', 'cnt' => $contagens['aprovado']],
                'rejeitado'          => ['label' => 'Rejeitados', 'icon' => 'fa-times-circle', 'cnt' => $contagens['rejeitado']],
                'todos'              => ['label' => 'Todos',      'icon' => 'fa-list',          'cnt' => $total_todos],
            ];
            foreach ($tabs as $key => $tab): ?>
                <a href="?filtro=<?= $key ?>"
                   class="btn-filtro <?= $filtro === $key ? 'ativo' : '' ?>">
                    <i class="fas <?= $tab['icon'] ?>"></i>
                    <?= $tab['label'] ?>
                    <span class="cnt-badge"><?= $tab['cnt'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Mapa geral com todos os exemplares -->
        <?php
        $com_geo = array_filter($exemplares, fn($e) => $e['latitude'] && $e['longitude']);
        if (count($com_geo) > 0):
        ?>
        <div class="mapa-geral-wrap">
            <h4><i class="fas fa-map" style="color:var(--cor-primaria)"></i>
                Localização dos exemplares
            </h4>
            <div id="mapa-geral"></div>
        </div>
        <?php endif; ?>

        <!-- Lista de exemplares -->
        <?php if (empty($exemplares)): ?>
            <div class="placeholder">
                <i class="fas fa-search"></i>
                <p>Nenhum exemplar <?= $filtro === 'todos' ? 'encontrado' : 'com este status' ?>.</p>
            </div>
        <?php else: ?>
            <?php foreach ($exemplares as $ex):
                $st_class = [
                    'aguardando_revisao' => 'aguardando',
                    'aprovado'           => 'aprovado',
                    'rejeitado'          => 'rejeitado',
                ][$ex['status']] ?? 'aguardando';

                $pill_class = [
                    'aguardando_revisao' => 'pill-aguardando',
                    'aprovado'           => 'pill-aprovado',
                    'rejeitado'          => 'pill-rejeitado',
                ][$ex['status']] ?? 'pill-aguardando';

                $st_label = [
                    'aguardando_revisao' => '⏳ Aguardando revisão',
                    'aprovado'           => '✅ Aprovado',
                    'rejeitado'          => '❌ Rejeitado',
                ][$ex['status']] ?? $ex['status'];
            ?>
            <div class="exemplar-card <?= $st_class ?>">

                <!-- Header do card -->
                <div class="exemplar-header">
                    <div>
                        <div class="exemplar-codigo"><?= htmlspecialchars($ex['codigo']) ?></div>
                        <div class="exemplar-especie"><?= htmlspecialchars($ex['nome_cientifico']) ?></div>
                    </div>
                    <span class="status-pill <?= $pill_class ?>"><?= $st_label ?></span>
                </div>

                <!-- Corpo do card -->
                <div class="exemplar-corpo">

                    <!-- Foto -->
                    <div>
                        <?php if ($ex['foto_identificacao']): ?>
                            <img src="/penomato_mvp/<?= htmlspecialchars($ex['foto_identificacao']) ?>"
                                 class="foto-id" alt="Foto de identificação"
                                 onerror="this.parentElement.innerHTML='<div class=foto-placeholder><i class=fas fa-image></i></div>'">
                        <?php else: ?>
                            <div class="foto-placeholder"><i class="fas fa-image"></i></div>
                        <?php endif; ?>
                    </div>

                    <!-- Metadados -->
                    <div>
                        <div class="meta-grid">
                            <div class="meta-item">
                                <label><i class="fas fa-tag"></i> Etiqueta</label>
                                <span><?= $ex['numero_etiqueta'] ? htmlspecialchars($ex['numero_etiqueta']) : '—' ?></span>
                            </div>
                            <div class="meta-item">
                                <label><i class="fas fa-user"></i> Cadastrado por</label>
                                <span><?= htmlspecialchars($ex['cadastrado_por_nome']) ?></span>
                            </div>
                            <div class="meta-item">
                                <label><i class="fas fa-map-marker-alt"></i> Local</label>
                                <span><?= htmlspecialchars($ex['cidade']) ?> / <?= htmlspecialchars($ex['estado']) ?></span>
                            </div>
                            <div class="meta-item">
                                <label><i class="fas fa-tree"></i> Bioma</label>
                                <span><?= htmlspecialchars($ex['bioma'] ?? '—') ?></span>
                            </div>
                            <div class="meta-item">
                                <label><i class="fas fa-calendar"></i> Cadastrado em</label>
                                <span><?= date('d/m/Y', strtotime($ex['data_cadastro'])) ?></span>
                            </div>
                            <?php if ($ex['latitude'] && $ex['longitude']): ?>
                            <div class="meta-item">
                                <label><i class="fas fa-crosshairs"></i> GPS</label>
                                <span style="font-size:.8rem;font-family:monospace">
                                    <?= number_format($ex['latitude'], 5) ?>,
                                    <?= number_format($ex['longitude'], 5) ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($ex['descricao_local']): ?>
                            <div style="margin-top:12px;font-size:.875rem;color:#555;
                                        background:#f8fafc;padding:10px;border-radius:6px;">
                                <i class="fas fa-info-circle" style="color:#999"></i>
                                <?= htmlspecialchars($ex['descricao_local']) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Mini mapa individual -->
                        <?php if ($ex['latitude'] && $ex['longitude']): ?>
                            <div class="mini-mapa" id="mini-<?= $ex['id'] ?>"></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Ações (só para aguardando) -->
                <?php if ($ex['status'] === 'aguardando_revisao'): ?>
                <div class="exemplar-acoes">

                    <!-- Aprovar -->
                    <form method="POST"
                          action="/penomato_mvp/src/Controllers/processar_revisao_exemplar.php"
                          onsubmit="return confirm('Confirma a aprovação do exemplar <?= $ex['codigo'] ?>?')">
                        <input type="hidden" name="exemplar_id" value="<?= $ex['id'] ?>">
                        <input type="hidden" name="acao" value="aprovar">
                        <input type="hidden" name="filtro" value="<?= $filtro ?>">
                        <button type="submit" class="btn-aprovar">
                            <i class="fas fa-check"></i> Aprovar exemplar
                        </button>
                    </form>

                    <!-- Rejeitar -->
                    <div class="rejeitar-wrap">
                        <textarea id="motivo-<?= $ex['id'] ?>"
                                  placeholder="Descreva o motivo da rejeição antes de rejeitar..."></textarea>
                        <form method="POST"
                              action="/penomato_mvp/src/Controllers/processar_revisao_exemplar.php"
                              id="form-rejeitar-<?= $ex['id'] ?>">
                            <input type="hidden" name="exemplar_id" value="<?= $ex['id'] ?>">
                            <input type="hidden" name="acao" value="rejeitar">
                            <input type="hidden" name="filtro" value="<?= $filtro ?>">
                            <input type="hidden" name="motivo_rejeicao"
                                   id="hidden-motivo-<?= $ex['id'] ?>">
                            <button type="button" class="btn-rejeitar"
                                    onclick="submitRejeitar(<?= $ex['id'] ?>)">
                                <i class="fas fa-times"></i> Rejeitar
                            </button>
                        </form>
                    </div>
                </div>

                <?php elseif ($ex['status'] === 'rejeitado' && $ex['motivo_rejeicao']): ?>
                <div class="exemplar-acoes">
                    <div class="motivo-rejeicao" style="width:100%">
                        <strong><i class="fas fa-comment-alt"></i> Motivo da rejeição:</strong><br>
                        <?= htmlspecialchars($ex['motivo_rejeicao']) ?>
                        <?php if ($ex['data_revisao']): ?>
                            <span style="float:right;font-size:.78rem;color:#999">
                                <?= date('d/m/Y', strtotime($ex['data_revisao'])) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Rodapé -->
        <div class="rodape">
            <a href="/penomato_mvp/src/Views/entrada_revisor.php" class="btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar ao Painel
            </a>
        </div>

    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ── MAPA GERAL ────────────────────────────────────────────────────────────────
const exemplares = <?= json_encode(array_map(fn($e) => [
    'id'       => $e['id'],
    'codigo'   => $e['codigo'],
    'especie'  => $e['nome_cientifico'],
    'status'   => $e['status'],
    'lat'      => $e['latitude'] ? (float)$e['latitude'] : null,
    'lng'      => $e['longitude'] ? (float)$e['longitude'] : null,
    'cidade'   => $e['cidade'],
    'estado'   => $e['estado'],
    'foto'     => $e['foto_identificacao'],
], $exemplares)) ?>;

const corStatus = {
    aguardando_revisao: '#f59e0b',
    aprovado:           '#0b5e42',
    rejeitado:          '#dc3545'
};

const comGeo = exemplares.filter(e => e.lat && e.lng);

if (comGeo.length > 0 && document.getElementById('mapa-geral')) {
    const mapaGeral = L.map('mapa-geral');
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap', maxZoom: 19
    }).addTo(mapaGeral);

    const bounds = [];

    comGeo.forEach(ex => {
        const cor  = corStatus[ex.status] || '#888';
        const icone = L.divIcon({
            className: '',
            html: `<div style="
                width:34px;height:34px;border-radius:50%;
                background:${cor};border:3px solid white;
                box-shadow:0 2px 6px rgba(0,0,0,.35);
                display:flex;align-items:center;justify-content:center;
                color:white;font-weight:900;font-size:.65rem;
                font-family:monospace;letter-spacing:1px;
            ">${ex.codigo}</div>`,
            iconSize: [34, 34], iconAnchor: [17, 17]
        });

        const marcador = L.marker([ex.lat, ex.lng], { icon: icone }).addTo(mapaGeral);
        const fotoHtml = ex.foto
            ? `<img src="/penomato_mvp/${ex.foto}"
                    style="width:100%;height:80px;object-fit:cover;border-radius:4px;margin-bottom:6px">`
            : '';
        marcador.bindPopup(`
            ${fotoHtml}
            <strong style="font-family:monospace;font-size:1rem">${ex.codigo}</strong><br>
            <em style="font-size:.85rem">${ex.especie}</em><br>
            <small>${ex.cidade} / ${ex.estado}</small>
        `);

        bounds.push([ex.lat, ex.lng]);
    });

    if (bounds.length === 1) {
        mapaGeral.setView(bounds[0], 14);
    } else {
        mapaGeral.fitBounds(bounds, { padding: [30, 30] });
    }
}

// ── MINI MAPAS INDIVIDUAIS ────────────────────────────────────────────────────
exemplares.forEach(ex => {
    if (!ex.lat || !ex.lng) return;
    const el = document.getElementById('mini-' + ex.id);
    if (!el) return;

    const mini = L.map(el, {
        zoomControl: false, dragging: false,
        scrollWheelZoom: false, doubleClickZoom: false
    }).setView([ex.lat, ex.lng], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '', maxZoom: 19
    }).addTo(mini);

    L.circleMarker([ex.lat, ex.lng], {
        radius: 7, fillColor: corStatus[ex.status] || '#888',
        color: 'white', weight: 2, fillOpacity: 1
    }).addTo(mini);
});

// ── REJEITAR ──────────────────────────────────────────────────────────────────
function submitRejeitar(id) {
    const motivo = document.getElementById('motivo-' + id).value.trim();
    if (!motivo) {
        alert('Descreva o motivo da rejeição antes de confirmar.');
        document.getElementById('motivo-' + id).focus();
        return;
    }
    if (!confirm('Confirma a rejeição do exemplar?')) return;
    document.getElementById('hidden-motivo-' + id).value = motivo;
    document.getElementById('form-rejeitar-' + id).submit();
}
</script>
</body>
</html>
