<?php
// ============================================================
// BANCO DE MATRIZES — REGISTRAR NOVA MATRIZ
// ============================================================

require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../../Controllers/auth/verificar_acesso.php';

if (!estaLogado()) {
    $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
         . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('Location: /penomato_mvp/src/Views/auth/login.php?redirect=' . urlencode($url));
    exit;
}

$titulo_pagina = 'Registrar Matriz — Banco de Matrizes';

$nomes_populares = [
    'Angico', 'Araticum', 'Aroeira', 'Buriti', 'Cagaita', 'Cedro',
    'Copaíba', 'Embaúba', 'Gameleira', 'Gonçalo-alves', 'Ipê amarelo',
    'Ipê branco', 'Ipê rosa', 'Ipê roxo', 'Jatobá', 'Jacarandá',
    'Lixeira', 'Murici', 'Paineira', 'Pequi', 'Peroba', 'Sobrasil',
    'Sucupira branca', 'Sucupira preta', 'Tamboril', 'Tingui', 'Vinhático',
    'Outro...',
];

$partes_fotos = [
    'geral'   => ['label' => 'Árvore inteira', 'icone' => 'fa-tree',       'obrigatorio' => true],
    'folha'   => ['label' => 'Folha',           'icone' => 'fa-leaf',       'obrigatorio' => false],
    'flor'    => ['label' => 'Flor',            'icone' => 'fa-spa',        'obrigatorio' => false],
    'fruto'   => ['label' => 'Fruto',           'icone' => 'fa-apple-alt',  'obrigatorio' => false],
    'caule'   => ['label' => 'Caule / Casca',   'icone' => 'fa-grip-lines', 'obrigatorio' => false],
    'semente' => ['label' => 'Semente',         'icone' => 'fa-seedling',   'obrigatorio' => false],
];

include __DIR__ . '/../includes/cabecalho.php';
?>

<style>
    .registrar-wrap {
        max-width: 660px;
        margin: 30px auto 60px;
        padding: 0 16px;
    }

    .registrar-header { text-align: center; margin-bottom: 28px; }
    .registrar-header h2 { font-size: 1.6rem; font-weight: 700; color: var(--cinza-800); }
    .registrar-header p  { color: var(--cinza-600); font-size: 0.9rem; }

    .card-secao {
        background: white;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 18px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    }

    .card-secao h5 {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--cor-primaria);
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .card-secao h5 .num {
        width: 24px; height: 24px;
        background: var(--cor-primaria);
        color: white; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.75rem; font-weight: 700; flex-shrink: 0;
    }

    .badge-obrig {
        font-size: 0.68rem;
        background: var(--perigo-fundo); color: var(--perigo-texto);
        padding: 2px 8px; border-radius: 10px; font-weight: 600;
    }

    /* ── grade de fotos ── */
    .fotos-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }

    /* o input fica escondido, o label é o card clicável */
    .foto-input { display: none; }

    .foto-label {
        display: block;
        position: relative;
        aspect-ratio: 1 / 1;
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
    }

    /* estado vazio */
    .foto-vazio {
        position: absolute;
        inset: 0;
        border: 2px dashed var(--cinza-300);
        border-radius: 12px;
        background: var(--cinza-50);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 8px;
        text-align: center;
        transition: border-color 0.2s, background 0.2s;
    }

    .foto-label:hover .foto-vazio,
    .foto-label:active .foto-vazio {
        border-color: var(--cor-primaria);
        background: var(--verde-100);
    }

    .foto-vazio i    { font-size: 1.6rem; color: var(--cinza-400); }
    .foto-vazio span { font-size: 0.72rem; font-weight: 600; color: var(--cinza-500); line-height: 1.2; }
    .foto-vazio small{ font-size: 0.62rem; color: var(--cinza-400); }

    /* obrigatório */
    .foto-label.obrig .foto-vazio {
        border-style: solid;
        border-color: var(--cor-primaria);
        background: var(--verde-50);
    }
    .foto-label.obrig .foto-vazio i    { color: var(--cor-primaria); }
    .foto-label.obrig .foto-vazio span { color: var(--cor-primaria); }

    /* estado com foto — imagem preenche o card */
    .foto-preview-img {
        display: none;
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 12px;
    }

    .foto-label.tem-foto .foto-vazio      { display: none; }
    .foto-label.tem-foto .foto-preview-img{ display: block; }

    /* botão remover */
    .btn-remover {
        display: none;
        position: absolute;
        top: 5px; right: 5px;
        width: 24px; height: 24px;
        background: rgba(220,53,69,0.88);
        color: white; border: none;
        border-radius: 50%;
        font-size: 0.65rem;
        align-items: center; justify-content: center;
        cursor: pointer;
        z-index: 10;
        line-height: 1;
    }

    .foto-label.tem-foto .btn-remover { display: flex; }

    /* ── GPS ── */
    .gps-status {
        padding: 12px 16px; border-radius: 10px;
        font-size: 0.9rem; display: flex; align-items: center; gap: 10px;
        margin-bottom: 12px;
    }
    .gps-status.aguardando { background: var(--cinza-100);    color: var(--cinza-600); }
    .gps-status.capturado  { background: var(--sucesso-fundo);color: var(--sucesso-texto); }
    .gps-status.erro       { background: var(--perigo-fundo); color: var(--perigo-texto); }

    /* ── opções de localização ── */
    .opcoes-loc {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        margin-bottom: 4px;
    }

    .btn-loc {
        background: var(--cinza-100);
        color: var(--cinza-600);
        border: 2px solid var(--cinza-200);
        border-radius: 10px;
        padding: 12px 6px;
        font-size: 0.78rem;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        transition: all 0.2s;
        text-align: center;
        line-height: 1.2;
    }

    .btn-loc i { font-size: 1.15rem; }

    .btn-loc:hover {
        border-color: var(--cor-primaria);
        color: var(--cor-primaria);
        background: var(--verde-50);
    }

    .btn-loc.ativo {
        border-color: var(--cor-primaria);
        background: var(--verde-100);
        color: var(--cor-primaria);
    }

    /* ── mapa picker ── */
    #mapa-picker-wrap {
        margin-top: 12px;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid var(--cinza-200);
    }

    #mapa-picker { height: 260px; }

    .mapa-picker-dica {
        background: var(--cinza-50);
        padding: 7px 12px;
        font-size: 0.76rem;
        color: var(--cinza-500);
        text-align: center;
    }

    /* ── autocomplete ── */
    .autocomplete-wrap { position: relative; }

    .autocomplete-lista {
        position: absolute; top: 100%; left: 0; right: 0;
        background: white; border: 1px solid var(--cinza-200);
        border-radius: 10px; box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        z-index: 200; max-height: 200px; overflow-y: auto; display: none;
    }

    .autocomplete-item {
        padding: 10px 16px; cursor: pointer;
        font-size: 0.88rem; transition: background 0.15s;
    }
    .autocomplete-item:hover { background: var(--verde-100); color: var(--cor-primaria); }
    .autocomplete-item em    { font-style: normal; font-weight: 700; color: var(--cor-primaria); }

    .form-label { font-weight: 600; font-size: 0.88rem; color: var(--cinza-700); margin-bottom: 5px; }

    .form-control, .form-select {
        border-radius: 10px; border-color: var(--cinza-200);
        padding: 10px 14px; font-size: 0.92rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--cor-primaria);
        box-shadow: 0 0 0 3px rgba(11,94,66,0.1);
    }

    /* ── botão registrar ── */
    .btn-registrar {
        background: var(--cor-primaria); color: white;
        border: none; padding: 16px; border-radius: 12px;
        font-size: 1.05rem; font-weight: 700; width: 100%;
        cursor: pointer; transition: all 0.3s;
        display: flex; align-items: center; justify-content: center; gap: 12px;
    }
    .btn-registrar:hover {
        background: var(--cor-primaria-hover);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(11,94,66,0.3);
    }

    @media (max-width: 480px) {
        .fotos-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>

<div class="registrar-wrap">

    <div class="registrar-header">
        <h2><i class="fas fa-plus-circle text-success me-2"></i>Registrar Matriz</h2>
        <p>Foto da árvore inteira e GPS são obrigatórios. Os demais campos são opcionais.</p>
    </div>

    <form action="/penomato_mvp/src/Controllers/matrizes/processar_registro.php"
          method="POST" enctype="multipart/form-data" id="form-matriz">

        <!-- 1. Fotos -->
        <div class="card-secao">
            <h5>
                <span class="num">1</span>
                Fotos
                <span class="badge-obrig ms-1">árvore inteira obrigatória</span>
            </h5>

            <div class="fotos-grid">
                <?php foreach ($partes_fotos as $parte => $cfg): ?>

                <!-- input fora do label — evita conflito de clique -->
                <input type="file"
                       id="foto_<?php echo $parte; ?>"
                       name="foto_<?php echo $parte; ?>"
                       accept="image/*"
                       class="foto-input">

                <label class="foto-label <?php echo $cfg['obrigatorio'] ? 'obrig' : ''; ?>"
                       for="foto_<?php echo $parte; ?>"
                       id="label-<?php echo $parte; ?>">

                    <div class="foto-vazio">
                        <i class="fas <?php echo $cfg['icone']; ?>"></i>
                        <span><?php echo $cfg['label']; ?></span>
                        <?php if ($cfg['obrigatorio']): ?>
                            <small>obrigatória</small>
                        <?php endif; ?>
                    </div>

                    <img class="foto-preview-img"
                         id="img-<?php echo $parte; ?>"
                         src="" alt="<?php echo $cfg['label']; ?>">

                    <button type="button"
                            class="btn-remover"
                            id="remover-<?php echo $parte; ?>"
                            title="Remover foto">
                        <i class="fas fa-times"></i>
                    </button>

                </label>

                <?php endforeach; ?>
            </div>
        </div>

        <!-- 2. Localização -->
        <div class="card-secao">
            <h5>
                <span class="num">2</span>
                Localização
                <span class="badge-obrig ms-1">obrigatória</span>
            </h5>

            <div class="gps-status aguardando" id="gps-status">
                <i class="fas fa-map-marker-alt"></i>
                <span>Localização não definida</span>
            </div>

            <input type="hidden" id="latitude"  name="latitude">
            <input type="hidden" id="longitude" name="longitude">

            <div class="opcoes-loc">
                <button type="button" class="btn-loc" id="btn-usar-gps">
                    <i class="fas fa-crosshairs"></i> GPS do Celular
                </button>
                <button type="button" class="btn-loc" id="btn-usar-mapa">
                    <i class="fas fa-map-marked-alt"></i> Apontar no Mapa
                </button>
                <button type="button" class="btn-loc" id="btn-usar-imagem">
                    <i class="fas fa-camera"></i> Imagem
                </button>
                <button type="button" class="btn-loc" id="btn-usar-manual">
                    <i class="fas fa-keyboard"></i> Manual
                </button>
            </div>

            <!-- Input oculto para leitura de EXIF -->
            <input type="file" id="input-exif-imagem" accept="image/*" style="display:none">


            <!-- Mapa picker -->
            <div id="mapa-picker-wrap" style="display:none">
                <div id="mapa-picker"></div>
                <div class="mapa-picker-dica">
                    <i class="fas fa-hand-pointer me-1"></i>
                    Toque ou clique no mapa para marcar a árvore. O marcador é arrastável.
                </div>
            </div>

            <!-- Campos manuais -->
            <div id="campos-manuais" style="display:none" class="row g-2 mt-2">
                <div class="col-6">
                    <label class="form-label">Latitude</label>
                    <input type="number" step="0.00000001" class="form-control"
                           id="lat-manual" placeholder="-20.4697">
                </div>
                <div class="col-6">
                    <label class="form-label">Longitude</label>
                    <input type="number" step="0.00000001" class="form-control"
                           id="lon-manual" placeholder="-54.6201">
                </div>
            </div>
        </div>

        <!-- 3. Identificação -->
        <div class="card-secao">
            <h5>
                <span class="num">3</span>
                Identificação
                <small class="text-muted fw-normal ms-1">(opcional)</small>
            </h5>

            <div class="mb-3">
                <label class="form-label">Nome popular</label>
                <select class="form-select" id="select-popular" name="especie_nome_popular">
                    <option value="">Selecionar...</option>
                    <?php foreach ($nomes_populares as $nome): ?>
                        <option value="<?php echo $nome === 'Outro...' ? '__outro__' : htmlspecialchars($nome); ?>">
                            <?php echo htmlspecialchars($nome); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3" id="campo-popular-outro" style="display:none">
                <label class="form-label">Nome popular (outro)</label>
                <input type="text" class="form-control" id="nome-popular-outro"
                       placeholder="Digite o nome popular">
            </div>

            <div class="mb-0">
                <label class="form-label">
                    Nome científico
                    <small class="text-muted fw-normal">— preenchido pelo REFLORA ao escolher o popular</small>
                </label>
                <div class="autocomplete-wrap">
                    <input type="text" id="especie-busca" class="form-control"
                           name="especie_nome"
                           placeholder="Ex: Handroanthus impetiginosus"
                           autocomplete="off">
                    <div class="autocomplete-lista" id="autocomplete-lista"></div>
                </div>
            </div>
        </div>

        <!-- 4. Observações -->
        <div class="card-secao">
            <h5>
                <span class="num">4</span>
                Observações
                <small class="text-muted fw-normal ms-1">(opcional)</small>
            </h5>
            <textarea class="form-control" name="observacoes" rows="3"
                      placeholder="Ex: Copa densa, frutos maduros em setembro, próxima ao córrego..."></textarea>
        </div>

        <button type="submit" class="btn-registrar" id="btn-enviar">
            <i class="fas fa-map-marker-alt"></i> Registrar Matriz
        </button>

    </form>

    <div class="text-center mt-3 mb-4">
        <a href="/penomato_mvp/src/Views/matrizes/index.php" class="text-muted small">
            <i class="fas fa-arrow-left me-1"></i> Cancelar
        </a>
    </div>

</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// ── Preview de foto — cada input tem seu listener ─────────────
<?php foreach (array_keys($partes_fotos) as $parte): ?>
(function () {
    const input  = document.getElementById('foto_<?php echo $parte; ?>');
    const label  = document.getElementById('label-<?php echo $parte; ?>');
    const img    = document.getElementById('img-<?php echo $parte; ?>');
    const btnRem = document.getElementById('remover-<?php echo $parte; ?>');

    input.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            img.src = e.target.result;
            label.classList.add('tem-foto');
        };
        reader.readAsDataURL(file);
    });

    btnRem.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();   // não abre o seletor ao clicar em ×
        input.value = '';
        img.src = '';
        label.classList.remove('tem-foto');
    });
}());
<?php endforeach; ?>

// ── Localização: estado central ───────────────────────────────
var mapaPickerInstance = null;
var mapaPickerMarker   = null;

function definirLocalizacao(lat, lon, fonte) {
    lat = parseFloat(lat).toFixed(8);
    lon = parseFloat(lon).toFixed(8);
    document.getElementById('latitude').value  = lat;
    document.getElementById('longitude').value = lon;

    var icones = {
        'GPS do celular': 'fa-crosshairs',
        'EXIF da foto':   'fa-camera',
        'mapa':           'fa-map-marked-alt',
        'manual':         'fa-keyboard',
    };
    var icone = icones[fonte] || 'fa-check-circle';

    var status = document.getElementById('gps-status');
    status.className = 'gps-status capturado';
    status.innerHTML = '<i class="fas ' + icone + '"></i> <span>' +
        parseFloat(lat).toFixed(6) + ', ' + parseFloat(lon).toFixed(6) +
        ' <small style="opacity:.7">(' + fonte + ')</small></span>';

    // Sincroniza marcador do mapa se estiver aberto
    if (mapaPickerInstance && mapaPickerMarker) {
        mapaPickerMarker.setLatLng([parseFloat(lat), parseFloat(lon)]);
    }
}

// ── GPS do celular ────────────────────────────────────────────
document.getElementById('btn-usar-gps').addEventListener('click', function () {
    ativarModo('gps');
    var status = document.getElementById('gps-status');
    status.className = 'gps-status aguardando';
    status.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Captando localização...</span>';

    if (!navigator.geolocation) {
        status.className = 'gps-status erro';
        status.innerHTML = '<i class="fas fa-times-circle"></i> <span>GPS não disponível neste dispositivo.</span>';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function (pos) {
            definirLocalizacao(pos.coords.latitude, pos.coords.longitude, 'GPS do celular');
        },
        function () {
            status.className = 'gps-status erro';
            status.innerHTML = '<i class="fas fa-exclamation-circle"></i> <span>Não foi possível capturar. Tente outra opção.</span>';
        },
        { enableHighAccuracy: true, timeout: 12000 }
    );
});

// ── Mapa picker ───────────────────────────────────────────────
document.getElementById('btn-usar-mapa').addEventListener('click', function () {
    var aberto = ativarModo('mapa');
    if (!aberto) return;

    if (!mapaPickerInstance) {
        // Cerrado como centro padrão; usa coordenada existente se houver
        var latI = parseFloat(document.getElementById('latitude').value)  || -15.7801;
        var lonI = parseFloat(document.getElementById('longitude').value) || -47.9292;

        mapaPickerInstance = L.map('mapa-picker').setView([latI, lonI], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(mapaPickerInstance);

        // Marcador inicial se já há coordenada
        if (document.getElementById('latitude').value) {
            mapaPickerMarker = L.marker([latI, lonI], { draggable: true })
                .addTo(mapaPickerInstance);
            mapaPickerMarker.on('dragend', function () {
                var p = mapaPickerMarker.getLatLng();
                definirLocalizacao(p.lat, p.lng, 'mapa');
            });
        }

        // Clique no mapa → posiciona / move marcador
        mapaPickerInstance.on('click', function (e) {
            if (mapaPickerMarker) {
                mapaPickerMarker.setLatLng(e.latlng);
            } else {
                mapaPickerMarker = L.marker(e.latlng, { draggable: true })
                    .addTo(mapaPickerInstance);
                mapaPickerMarker.on('dragend', function () {
                    var p = mapaPickerMarker.getLatLng();
                    definirLocalizacao(p.lat, p.lng, 'mapa');
                });
            }
            definirLocalizacao(e.latlng.lat, e.latlng.lng, 'mapa');
        });
    }

    // Leaflet precisa recalcular tamanho após elemento ficar visível
    setTimeout(function () { mapaPickerInstance.invalidateSize(); }, 50);
});

// ── Manual ────────────────────────────────────────────────────
document.getElementById('btn-usar-manual').addEventListener('click', function () {
    ativarModo('manual');
});

document.getElementById('lat-manual').addEventListener('input', sincronizarManual);
document.getElementById('lon-manual').addEventListener('input', sincronizarManual);

function sincronizarManual() {
    var lat = document.getElementById('lat-manual').value;
    var lon = document.getElementById('lon-manual').value;
    if (!lat || !lon) return;
    definirLocalizacao(lat, lon, 'manual');
}

// ── Gerencia modos (toggle) ───────────────────────────────────
function ativarModo(modo) {
    var mapWrap   = document.getElementById('mapa-picker-wrap');
    var manualDiv = document.getElementById('campos-manuais');
    var btnGps    = document.getElementById('btn-usar-gps');
    var btnMapa   = document.getElementById('btn-usar-mapa');
    var btnImagem = document.getElementById('btn-usar-imagem');
    var btnManual = document.getElementById('btn-usar-manual');
    var todosBtn  = [btnGps, btnMapa, btnImagem, btnManual];

    var jaAtivo = (modo === 'gps'    && btnGps.classList.contains('ativo'))
               || (modo === 'mapa'   && btnMapa.classList.contains('ativo'))
               || (modo === 'imagem' && btnImagem.classList.contains('ativo'))
               || (modo === 'manual' && btnManual.classList.contains('ativo'));

    todosBtn.forEach(function (b) { b.classList.remove('ativo'); });
    mapWrap.style.display   = 'none';
    manualDiv.style.display = 'none';

    if (jaAtivo) return false;

    if (modo === 'gps') {
        btnGps.classList.add('ativo');
    } else if (modo === 'mapa') {
        btnMapa.classList.add('ativo');
        mapWrap.style.display = 'block';
        return true;
    } else if (modo === 'imagem') {
        btnImagem.classList.add('ativo');
    } else if (modo === 'manual') {
        btnManual.classList.add('ativo');
        manualDiv.style.display = 'flex';
    }
    return false;
}

// ── Parser EXIF/GPS puro — sem dependência de CDN ────────────
function lerGPSdaImagem(file) {
    return new Promise(function (resolve) {
        var reader = new FileReader();
        reader.onerror = function () { resolve(null); };
        reader.onload  = function (e) {
            try { resolve(parseJpegGPS(e.target.result)); }
            catch (_) { resolve(null); }
        };
        reader.readAsArrayBuffer(file);
    });
}

function parseJpegGPS(buffer) {
    var view = new DataView(buffer);
    if (view.getUint16(0, false) !== 0xFFD8) return null;

    var offset = 2;
    while (offset < buffer.byteLength - 2) {
        if (view.getUint8(offset) !== 0xFF) break;
        var marker = view.getUint16(offset, false);
        var segLen  = view.getUint16(offset + 2, false);

        if (marker === 0xFFE1 &&
            view.getUint32(offset + 4, false) === 0x45786966 &&
            view.getUint16(offset + 8, false) === 0x0000) {
            return parseTiffGPS(buffer, offset + 10);
        }
        offset += 2 + segLen;
    }
    return null;
}

function parseTiffGPS(buffer, tiffBase) {
    var view  = new DataView(buffer);
    var magic = view.getUint16(tiffBase, false);
    if (magic !== 0x4949 && magic !== 0x4D4D) return null;
    var le = magic === 0x4949;

    function u16(o) { return view.getUint16(o, le); }
    function u32(o) { return view.getUint32(o, le); }

    var ifd0   = tiffBase + u32(tiffBase + 4);
    var nEntry = u16(ifd0);
    var gpsOff = null;

    for (var i = 0; i < nEntry; i++) {
        var e = ifd0 + 2 + i * 12;
        if (u16(e) === 0x8825) { gpsOff = tiffBase + u32(e + 8); break; }
    }
    if (!gpsOff) return null;

    var nGps = u16(gpsOff);
    var lat = null, lon = null, latRef = 'N', lonRef = 'E';

    for (var j = 0; j < nGps; j++) {
        var g   = gpsOff + 2 + j * 12;
        var tag = u16(g);
        if (tag === 0x01) { latRef = String.fromCharCode(view.getUint8(g + 8)) || 'N'; }
        else if (tag === 0x03) { lonRef = String.fromCharCode(view.getUint8(g + 8)) || 'E'; }
        else if (tag === 0x02) { lat = rationalDeg(view, tiffBase + u32(g + 8), le); }
        else if (tag === 0x04) { lon = rationalDeg(view, tiffBase + u32(g + 8), le); }
    }

    // Dados zerados = GPS foi apagado por app de compartilhamento
    if (lat === null || lon === null || isNaN(lat) || isNaN(lon)) return null;
    return {
        latitude:  latRef === 'S' ? -lat : lat,
        longitude: lonRef === 'W' ? -lon : lon
    };
}

function rationalDeg(view, off, le) {
    function rat(o) {
        var n = view.getUint32(o,     le);
        var d = view.getUint32(o + 4, le);
        if (!d) return NaN; // denominador zero = dado inválido/apagado
        return n / d;
    }
    var deg = rat(off), min = rat(off + 8), sec = rat(off + 16);
    if (isNaN(deg) || isNaN(min) || isNaN(sec)) return NaN;
    return deg + min / 60 + sec / 3600;
}

// ── Botão Imagem: abre seletor e lê GPS do binário ───────────
document.getElementById('btn-usar-imagem').addEventListener('click', function () {
    ativarModo('imagem');
    document.getElementById('input-exif-imagem').click();
});

document.getElementById('input-exif-imagem').addEventListener('change', function () {
    var file = this.files && this.files[0];
    if (!file) return;

    var btnImagem = document.getElementById('btn-usar-imagem');
    var status    = document.getElementById('gps-status');
    btnImagem.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Lendo...';

    lerGPSdaImagem(file).then(function (gps) {
        btnImagem.innerHTML = '<i class="fas fa-camera"></i> Imagem';
        if (gps) {
            definirLocalizacao(gps.latitude, gps.longitude, 'EXIF da foto');
        } else {
            btnImagem.classList.remove('ativo');
            status.className = 'gps-status erro';
            status.innerHTML = '<i class="fas fa-exclamation-circle"></i> '
                + '<span>GPS não encontrado nesta foto. '
                + 'WhatsApp, Telegram e outros apps removem o GPS ao compartilhar. '
                + 'Use a foto original da câmera ou outra opção de localização.</span>';
        }
    });

    this.value = ''; // permite reselecionar a mesma imagem
});

// ── Nome popular → científico ─────────────────────────────────
document.getElementById('select-popular').addEventListener('change', function () {
    var val   = this.value;
    var outro = document.getElementById('campo-popular-outro');

    if (val === '__outro__') {
        outro.style.display = 'block';
        return;
    }
    outro.style.display = 'none';

    if (!val) return;

    fetch('/penomato_mvp/src/Controllers/matrizes/buscar_cientifico.php?popular=' + encodeURIComponent(val))
        .then(function (r) { return r.json(); })
        .then(function (d) {
            if (d.nome) document.getElementById('especie-busca').value = d.nome;
        });
});

// ── Autocomplete científico ───────────────────────────────────
var debounce;
var inputBusca = document.getElementById('especie-busca');
var lista = document.getElementById('autocomplete-lista');

inputBusca.addEventListener('input', function () {
    var q = this.value.trim();
    clearTimeout(debounce);
    if (q.length < 3) { lista.style.display = 'none'; return; }

    debounce = setTimeout(function () {
        fetch('/penomato_mvp/src/Controllers/matrizes/buscar_especie.php?q=' + encodeURIComponent(q))
            .then(function (r) { return r.json(); })
            .then(function (dados) {
                lista.innerHTML = '';
                if (!dados.length) { lista.style.display = 'none'; return; }
                dados.forEach(function (item) {
                    var li = document.createElement('div');
                    li.className = 'autocomplete-item';
                    li.innerHTML = item.nome.replace(new RegExp('(' + q + ')', 'gi'), '<em>$1</em>');
                    li.addEventListener('click', function () {
                        inputBusca.value = item.nome;
                        lista.style.display = 'none';
                    });
                    lista.appendChild(li);
                });
                lista.style.display = 'block';
            });
    }, 300);
});

document.addEventListener('click', function (e) {
    if (!e.target.closest('.autocomplete-wrap')) lista.style.display = 'none';
});

// ── Validação no submit ───────────────────────────────────────
document.getElementById('form-matriz').addEventListener('submit', function (e) {
    // Aplica nome popular "outro"
    var sel = document.getElementById('select-popular');
    if (sel.value === '__outro__') {
        sel.value = document.getElementById('nome-popular-outro').value.trim();
    }

    // Verifica foto geral
    var fotoGeral = document.getElementById('foto_geral');
    if (!fotoGeral.files || !fotoGeral.files[0]) {
        e.preventDefault();
        alert('A foto da árvore inteira é obrigatória.');
        return;
    }

    // Verifica GPS
    if (!document.getElementById('latitude').value || !document.getElementById('longitude').value) {
        e.preventDefault();
        alert('Capture o GPS antes de registrar.');
        return;
    }
});
</script>

<?php include __DIR__ . '/../includes/rodape.php'; ?>
