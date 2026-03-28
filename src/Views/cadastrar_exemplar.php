<?php
// ============================================================
// CADASTRAR EXEMPLAR
// ============================================================
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$usuario_id   = (int)$_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';

// ── Espécies disponíveis ──────────────────────────────────────────────────────
$especies = $pdo->query("
    SELECT id, nome_cientifico
    FROM especies_administrativo
    WHERE status NOT IN ('publicado')
    ORDER BY nome_cientifico
")->fetchAll();

// ── Especialistas disponíveis ─────────────────────────────────────────────────
$especialistas = $pdo->query("
    SELECT id, nome, categoria, instituicao
    FROM usuarios
    WHERE categoria IN ('revisor','gestor')
      AND status_verificacao = 'verificado'
      AND ativo = 1
    ORDER BY nome
")->fetchAll();

// ── Espécie pré-selecionada via GET ───────────────────────────────────────────
$especie_pre = isset($_GET['especie_id']) ? (int)$_GET['especie_id'] : 0;

$mensagem_sucesso = isset($_GET['sucesso']) ? urldecode($_GET['sucesso']) : '';
$mensagem_erro    = isset($_GET['erro'])    ? urldecode($_GET['erro'])    : '';

// ── Pré-visualização do próximo código ───────────────────────────────────────
$stmt_prox = $pdo->query("SELECT MAX(CAST(SUBSTRING(codigo, 3) AS UNSIGNED)) FROM exemplares WHERE codigo REGEXP '^PN[0-9]{3}$'");
$proximo_num = (int)$stmt_prox->fetchColumn() + 1;
if ($proximo_num > 999) $proximo_num = 999;
$proximo_codigo = 'PN' . str_pad($proximo_num, 3, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Exemplar — Penomato</title>

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <!-- leitor de GPS do EXIF (parser binário próprio, sem dependências) -->
    <script src="/penomato_mvp/assets/js/exif_gps.js"></script>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">

    <style>
        body {
            background: var(--cinza-50);
            color: var(--cinza-800);
            padding: var(--esp-8) var(--esp-5);
        }
        .container { max-width: 860px; margin: 0 auto; }

        /* Cabeçalho */
        .cabecalho {
            background: white;
            padding: 26px 36px;
            border-radius: 12px 12px 0 0;
            border-bottom: 4px solid var(--cor-primaria);
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 12px;
        }
        .cabecalho h1 { color: var(--cor-primaria); font-size: 1.7rem; font-weight: 600; }
        .cabecalho .sub { color: var(--cinza-500); font-style: italic; font-size: .88rem; margin-top: 3px; }
        .user-pill {
            background: #f8f9fa; padding: 7px 16px; border-radius: 40px;
            display: flex; align-items: center; gap: 9px; font-size: .88rem;
            box-shadow: 0 2px 5px rgba(0,0,0,.07);
        }
        .user-pill i { color: var(--cor-primaria); }
        .btn-sair { color: var(--perigo-cor); text-decoration: none; padding: 3px 8px; border-radius: 20px; transition: .2s; }
        .btn-sair:hover { background: var(--perigo-cor); color: white; }

        /* Card */
        .card-form {
            background: white;
            padding: 36px;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,.05);
        }

        /* Seções */
        .secao {
            border: 2px solid #e8edf2;
            border-radius: 10px;
            margin-bottom: 28px;
            overflow: hidden;
        }
        .secao-titulo {
            background: var(--cor-primaria);
            color: white;
            padding: 12px 20px;
            font-weight: 700;
            font-size: .95rem;
            display: flex; align-items: center; gap: 10px;
        }
        .secao-corpo { padding: 22px 24px; }

        /* Campos */
        .campo { margin-bottom: 18px; }
        .campo:last-child { margin-bottom: 0; }
        .campo label {
            display: block; font-weight: 600; font-size: .875rem;
            margin-bottom: 7px; color: var(--cinza-800);
        }
        .campo label .req { color: var(--perigo-cor); }
        .campo input, .campo select, .campo textarea {
            width: 100%; padding: 10px 14px;
            border: 2px solid var(--cinza-200); border-radius: 8px; font-size: .95rem;
            transition: border-color .2s;
        }
        .campo input:focus, .campo select:focus, .campo textarea:focus {
            outline: none; border-color: var(--cor-primaria);
        }
        .campo textarea { resize: vertical; min-height: 75px; }
        .campo .hint { font-size: .78rem; color: var(--cinza-400); margin-top: 5px; }

        /* Grid 2 colunas */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 580px) { .grid-2 { grid-template-columns: 1fr; } }

        /* Mapa */
        #mapa {
            height: 340px;
            border-radius: 8px;
            border: 2px solid var(--cinza-200);
            margin-bottom: 14px;
            z-index: 1;
        }
        .btn-gps {
            background: var(--cor-primaria); color: white; border: none;
            padding: 9px 20px; border-radius: 8px; font-weight: 600;
            font-size: .875rem; cursor: pointer; display: inline-flex;
            align-items: center; gap: 8px; margin-bottom: 16px; transition: .2s;
        }
        .btn-gps:hover { background: var(--cor-primaria-hover); }
        .btn-gps:disabled { opacity: .6; cursor: not-allowed; }
        .coords-row { display: flex; gap: 12px; }
        .coords-row .campo { flex: 1; }

        /* Drop zone foto */
        .drop-zone {
            border: 2px dashed #c3d4e0; border-radius: 8px;
            padding: 32px 20px; text-align: center; cursor: pointer;
            transition: .2s; background: #fafcfe; position: relative;
        }
        .drop-zone:hover, .drop-zone.sobre { border-color: var(--cor-primaria); background: var(--verde-50); }
        .drop-zone i { font-size: 2.2rem; color: #b0c4d0; display: block; margin-bottom: 10px; }
        .drop-zone p { font-size: .85rem; color: var(--cinza-500); margin: 0; }
        .drop-zone .arquivo-info {
            margin-top: 10px; font-size: .85rem;
            color: var(--cor-primaria); font-weight: 600; display: none;
        }
        #input-foto { display: none; }
        #preview-foto {
            width: 100%; max-height: 220px; object-fit: cover;
            border-radius: 6px; margin-top: 12px; display: none;
        }

        /* Especialista card */
        .esp-card {
            border: 2px solid var(--cinza-200); border-radius: 8px;
            padding: 14px 16px; cursor: pointer; transition: .2s;
            display: flex; align-items: center; gap: 14px;
        }
        .esp-card:hover { border-color: var(--cor-primaria); background: var(--verde-50); }
        .esp-card.selecionado { border-color: var(--cor-primaria); background: var(--verde-50); }
        .esp-avatar {
            width: 42px; height: 42px; border-radius: 50%;
            background: var(--cor-primaria); color: white;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; font-weight: 700; flex-shrink: 0;
        }
        .esp-nome { font-weight: 700; font-size: .9rem; }
        .esp-cat { font-size: .78rem; color: var(--cinza-400); }
        .esp-radio { margin-left: auto; accent-color: var(--cor-primaria); width: 18px; height: 18px; }

        /* Alertas */
        .alerta {
            padding: 13px 16px; border-radius: 8px; margin-bottom: 20px;
            display: flex; align-items: flex-start; gap: 10px; font-size: .9rem;
        }
        .alerta-success { background: var(--sucesso-fundo); color: var(--sucesso-texto); border-left: 4px solid var(--sucesso-cor); }
        .alerta-danger  { background: var(--perigo-fundo); color: var(--perigo-texto); border-left: 4px solid var(--perigo-cor); }
        .alerta-warning { background: var(--aviso-fundo); color: var(--aviso-texto); border-left: 4px solid var(--aviso-cor); }

        /* Botões rodapé */
        .rodape { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; flex-wrap: wrap; gap: 12px; }
        .btn-salvar {
            background: var(--cor-primaria); color: white; border: none;
            padding: 13px 36px; border-radius: 40px; font-size: 1rem;
            font-weight: 700; cursor: pointer; display: inline-flex;
            align-items: center; gap: 10px; transition: .2s;
        }
        .btn-salvar:hover { background: var(--cor-primaria-hover); }
        .btn-salvar:disabled { opacity: .6; cursor: not-allowed; }
        .btn-voltar {
            background: var(--cinza-500); color: white; text-decoration: none;
            padding: 12px 26px; border-radius: 40px; font-weight: 600;
            display: inline-flex; align-items: center; gap: 8px; transition: .2s;
        }
        .btn-voltar:hover { background: #5a6268; color: white; }
    </style>
</head>
<body>
<div class="container">

    <!-- Cabeçalho -->
    <div class="cabecalho">
        <div>
            <h1>🌿 Cadastrar Exemplar</h1>
            <div class="sub">Registre um indivíduo de campo antes de enviar fotos das partes</div>
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

    <div class="card-form">

        <!-- Mensagens -->
        <?php if ($mensagem_sucesso): ?>
            <div class="alerta alerta-success">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($mensagem_sucesso) ?></span>
            </div>
        <?php endif; ?>
        <?php if ($mensagem_erro): ?>
            <div class="alerta alerta-danger">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($mensagem_erro) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST"
              action="/penomato_mvp/src/Controllers/processar_cadastro_exemplar.php"
              enctype="multipart/form-data"
              id="form-exemplar">

            <!-- ── SEÇÃO 1: Espécie ──────────────────────────────────── -->
            <div class="secao">
                <div class="secao-titulo">
                    <i class="fas fa-leaf"></i> 1. Espécie
                </div>
                <div class="secao-corpo">

                    <!-- Identificador gerado automaticamente -->
                    <div class="campo" style="margin-bottom: var(--esp-6);">
                        <label style="text-transform:uppercase;font-size:var(--texto-xs);letter-spacing:.05em;font-weight:var(--peso-bold);color:var(--cinza-600);">
                            Identificador do Exemplar
                        </label>
                        <div style="display:flex;align-items:center;gap:var(--esp-3);margin-top:var(--esp-2);">
                            <span style="font-size:2rem;font-weight:var(--peso-bold);font-family:monospace;
                                         color:var(--cor-primaria);background:var(--cinza-50);
                                         border:2px solid var(--cor-primaria);border-radius:var(--raio-md);
                                         padding:var(--esp-2) var(--esp-6);letter-spacing:.15em;">
                                <?php echo htmlspecialchars($proximo_codigo); ?>
                            </span>
                            <span style="font-size:var(--texto-xs);color:var(--cinza-500);line-height:1.4;">
                                Gerado automaticamente<br>pelo sistema em sequência.
                            </span>
                        </div>
                    </div>
                    <div class="campo">
                        <label for="especie_id">
                            Nome científico <span class="req">*</span>
                        </label>
                        <select name="especie_id" id="especie_id" required>
                            <option value="">— selecione a espécie —</option>
                            <?php foreach ($especies as $esp): ?>
                                <option value="<?= $esp['id'] ?>"
                                    <?= $especie_pre == $esp['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($esp['nome_cientifico']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="numero_etiqueta">
                            Número da etiqueta física
                        </label>
                        <input type="text" name="numero_etiqueta" id="numero_etiqueta"
                               placeholder="Ex: 47  —  número pregado na planta"
                               maxlength="50">
                        <div class="hint">Etiqueta de alumínio fixada no exemplar em campo.</div>
                    </div>
                    <div class="campo">
                        <label>Foto de identificação</label>
                        <p style="font-size:.88rem;color:#555;margin-bottom:10px;margin-top:2px;">
                            Foto geral do exemplar tirada no momento do cadastro em campo.
                            Serve para o especialista confirmar que o espécime corresponde à espécie declarada.
                        </p>

                        <!-- Botões de captura -->
                        <div style="display:flex;gap:10px;margin-bottom:12px;flex-wrap:wrap;">
                            <button type="button" onclick="abrirCamera()"
                                    style="flex:1;min-width:160px;padding:12px 16px;background:var(--cor-primaria);
                                           color:white;border:none;border-radius:8px;font-weight:600;
                                           font-size:.9rem;cursor:pointer;display:flex;align-items:center;
                                           justify-content:center;gap:8px;">
                                <i class="fas fa-camera"></i> Tirar foto agora
                                <small style="font-weight:400;opacity:.85;display:block;font-size:.72rem;">
                                    usa localização atual
                                </small>
                            </button>
                            <button type="button" onclick="abrirGaleria()"
                                    style="flex:1;min-width:160px;padding:12px 16px;background:var(--cinza-200);
                                           color:var(--cinza-800);border:none;border-radius:8px;font-weight:600;
                                           font-size:.9rem;cursor:pointer;display:flex;align-items:center;
                                           justify-content:center;gap:8px;">
                                <i class="fas fa-images"></i> Escolher da galeria
                                <small style="font-weight:400;opacity:.7;display:block;font-size:.72rem;">
                                    captura GPS da foto
                                </small>
                            </button>
                        </div>

                        <!-- Input único — capture trocado via JS -->
                        <input type="file" name="foto_identificacao" id="input-foto"
                               accept="image/*" style="display:none">

                        <div class="arquivo-info" id="arquivo-info" style="margin-bottom:8px;"></div>
                        <img id="preview-foto" src="" alt="Preview"
                             style="width:100%;max-height:220px;object-fit:cover;border-radius:6px;display:none;">
                    </div>
                </div>
            </div>

            <!-- GPS feedback da foto -->
            <div id="gps-foto-aviso" style="display:none;margin-bottom:6px;"></div>

            <!-- ── SEÇÃO 2: Localização ─────────────────────────────── -->
            <div class="secao">
                <div class="secao-titulo">
                    <i class="fas fa-map-marker-alt"></i> 2. Localização
                </div>
                <div class="secao-corpo">

                    <button type="button" class="btn-gps" id="btn-gps" onclick="capturarGPS()">
                        <i class="fas fa-crosshairs"></i> Usar minha localização atual
                    </button>

                    <!-- Mapa Leaflet -->
                    <div id="mapa"></div>

                    <!-- Coordenadas -->
                    <div class="coords-row">
                        <div class="campo">
                            <label for="latitude">Latitude</label>
                            <input type="number" name="latitude" id="latitude"
                                   step="0.00000001" min="-90" max="90"
                                   placeholder="-20.4697">
                        </div>
                        <div class="campo">
                            <label for="longitude">Longitude</label>
                            <input type="number" name="longitude" id="longitude"
                                   step="0.00000001" min="-180" max="180"
                                   placeholder="-54.6201">
                        </div>
                    </div>

                    <!-- Cidade / Estado / Bioma -->
                    <div class="grid-2">
                        <div class="campo">
                            <label for="cidade">Cidade <span class="req">*</span></label>
                            <input type="text" name="cidade" id="cidade"
                                   placeholder="Ex: Campo Grande" maxlength="150" required>
                        </div>
                        <div class="campo">
                            <label for="estado">Estado <span class="req">*</span></label>
                            <select name="estado" id="estado" required>
                                <option value="">— UF —</option>
                                <?php
                                $ufs = ['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA',
                                        'MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN',
                                        'RO','RR','RS','SC','SE','SP','TO'];
                                foreach ($ufs as $uf): ?>
                                    <option value="<?= $uf ?>"><?= $uf ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="campo">
                        <label for="bioma">Bioma <span class="req">*</span></label>
                        <select name="bioma" id="bioma" required>
                            <option value="">— selecione —</option>
                            <option>Cerrado</option>
                            <option>Mata Atlântica</option>
                            <option>Pantanal</option>
                            <option>Caatinga</option>
                            <option>Amazônia</option>
                            <option>Pampa</option>
                            <option>Outro</option>
                        </select>
                    </div>

                    <div class="campo">
                        <label for="descricao_local">Descrição do local</label>
                        <textarea name="descricao_local" id="descricao_local"
                                  placeholder="Ex: Margem da trilha principal, próximo ao córrego, sombra parcial..."></textarea>
                    </div>
                </div>
            </div>

            <!-- ── SEÇÃO 3: Especialista orientador ────────────────── -->
            <div class="secao">
                <div class="secao-titulo">
                    <i class="fas fa-user-tie"></i> 3. Especialista orientador
                </div>
                <div class="secao-corpo">
                    <p style="font-size:.88rem;color:#555;margin-bottom:16px;">
                        O especialista selecionado será notificado para revisar este exemplar
                        e, posteriormente, revisar o artigo gerado para esta espécie.
                    </p>

                    <?php if (empty($especialistas)): ?>
                        <div class="alerta alerta-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Nenhum especialista cadastrado ainda. Peça ao gestor para cadastrar
                            um usuário com categoria <strong>Revisor</strong> antes de continuar.</span>
                        </div>
                        <input type="hidden" name="especialista_id" value="">
                    <?php else: ?>
                        <input type="hidden" name="especialista_id" id="especialista_id" value="">
                        <div style="display:flex;flex-direction:column;gap:10px;" id="lista-especialistas">
                            <?php foreach ($especialistas as $esp): ?>
                                <label class="esp-card" id="card-<?= $esp['id'] ?>"
                                       onclick="selecionarEsp(<?= $esp['id'] ?>)">
                                    <div class="esp-avatar">
                                        <?= strtoupper(substr($esp['nome'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="esp-nome"><?= htmlspecialchars($esp['nome']) ?></div>
                                        <div class="esp-cat">
                                            <?= ucfirst($esp['categoria']) ?>
                                            <?= $esp['instituicao'] ? ' · ' . htmlspecialchars($esp['instituicao']) : '' ?>
                                        </div>
                                    </div>
                                    <input type="radio" name="_esp_radio"
                                           value="<?= $esp['id'] ?>" class="esp-radio">
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Rodapé -->
            <div class="rodape">
                <a href="/penomato_mvp/src/Views/entrar_colaborador.php" class="btn-voltar">
                    <i class="fas fa-arrow-left"></i> Voltar ao Painel
                </a>
                <button type="submit" class="btn-salvar" id="btn-salvar">
                    <i class="fas fa-save"></i> Cadastrar Exemplar
                </button>
            </div>

        </form>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ── MAPA ───────────────────────────────────────────────────────────────────────
const centro_brasil = [-15.7801, -47.9292]; // Brasília como ponto inicial
const mapa = L.map('mapa').setView(centro_brasil, 4);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
    maxZoom: 19
}).addTo(mapa);

let marcador = null;

function colocarMarcador(lat, lng) {
    if (marcador) mapa.removeLayer(marcador);
    marcador = L.marker([lat, lng], { draggable: true }).addTo(mapa);
    marcador.on('dragend', () => {
        const pos = marcador.getLatLng();
        document.getElementById('latitude').value  = pos.lat.toFixed(8);
        document.getElementById('longitude').value = pos.lng.toFixed(8);
    });
    document.getElementById('latitude').value  = lat.toFixed(8);
    document.getElementById('longitude').value = lng.toFixed(8);
}

// Clique no mapa planta marcador
mapa.on('click', e => {
    colocarMarcador(e.latlng.lat, e.latlng.lng);
});

// Atualizar mapa ao digitar coordenadas manualmente
['latitude','longitude'].forEach(id => {
    document.getElementById(id).addEventListener('change', () => {
        const lat = parseFloat(document.getElementById('latitude').value);
        const lng = parseFloat(document.getElementById('longitude').value);
        if (!isNaN(lat) && !isNaN(lng)) {
            colocarMarcador(lat, lng);
            mapa.setView([lat, lng], 15);
        }
    });
});

// ── GPS ────────────────────────────────────────────────────────────────────────
function capturarGPS() {
    const btn = document.getElementById('btn-gps');
    if (!navigator.geolocation) {
        alert('Geolocalização não suportada neste navegador.');
        return;
    }
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Obtendo localização...';

    navigator.geolocation.getCurrentPosition(
        pos => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            colocarMarcador(lat, lng);
            mapa.setView([lat, lng], 17);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Localização obtida';
        },
        err => {
            alert('Não foi possível obter a localização: ' + err.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-crosshairs"></i> Usar minha localização atual';
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}

// ── FOTO DE IDENTIFICAÇÃO ──────────────────────────────────────────────────────
const arqInfo    = document.getElementById('arquivo-info');
const previewImg = document.getElementById('preview-foto');
const inputFoto  = document.getElementById('input-foto');

// Câmera direta (capture="environment") → tenta GPS do EXIF da foto fresca
let _tentarExif = false;

function abrirCamera() {
    // Dispara geolocalização imediatamente — não depende do arquivo para ter as coordenadas
    _tentarExif = false;
    inputFoto.removeAttribute('capture');
    inputFoto.value = '';
    tentarGeolocalizacao(document.getElementById('gps-foto-aviso'));
    inputFoto.click();
}

function abrirGaleria() {
    _tentarExif = true; // galeria preserva EXIF → lê GPS da foto
    inputFoto.removeAttribute('capture');
    inputFoto.value = '';
    inputFoto.click();
}

inputFoto.addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    processarFoto(file, _tentarExif);
});

async function processarFoto(file, tentarExif) {
    arqInfo.textContent = '✅ ' + file.name + ' (' + (file.size/1024/1024).toFixed(1) + ' MB)';
    arqInfo.style.display = 'block';

    const reader = new FileReader();
    reader.onload = e => { previewImg.src = e.target.result; previewImg.style.display = 'block'; };
    reader.readAsDataURL(file);

    const aviso = document.getElementById('gps-foto-aviso');

    if (tentarExif) {
        aviso.innerHTML = '<div class="alerta alerta-warning" style="margin:0;">'
            + '<i class="fas fa-spinner fa-spin"></i> Lendo GPS da foto...</div>';
        aviso.style.display = 'block';

        // 1. EXIF no browser
        try {
            const gps = await lerGpsExif(file);
            if (gps) { aplicarCoordenadas(gps.lat, gps.lng, aviso, 'foto'); return; }
        } catch (_) {}

        // 2. EXIF no servidor
        try {
            const fd = new FormData();
            fd.append('foto', file);
            const json = await fetch('/penomato_mvp/src/Controllers/ler_exif_gps.php',
                { method: 'POST', body: fd }).then(r => r.json());
            if (json.ok) { aplicarCoordenadas(json.lat, json.lng, aviso, 'foto'); return; }
        } catch (_) {}
    }

    // Geolocalização já foi disparada em abrirCamera() — não duplica
    if (tentarExif) tentarGeolocalizacao(aviso);
}

function aplicarCoordenadas(lat, lng, aviso, origem) {
    document.getElementById('latitude').value  = lat.toFixed(8);
    document.getElementById('longitude').value = lng.toFixed(8);
    colocarMarcador(lat, lng);
    mapa.setView([lat, lng], 17);
    aviso.innerHTML = '<div class="alerta alerta-success" style="margin:0;">'
        + '<i class="fas fa-satellite-dish"></i>'
        + ' <span>Coordenadas extraídas da ' + origem + ': '
        + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</span></div>';
    aviso.style.display = 'block';
}

function tentarGeolocalizacao(aviso) {
    if (!navigator.geolocation) {
        aviso.innerHTML = '<div class="alerta alerta-warning" style="margin:0;">'
            + '<i class="fas fa-exclamation-triangle"></i>'
            + ' <span>GPS não disponível. Ajuste o marcador no mapa manualmente.</span></div>';
        aviso.style.display = 'block';
        return;
    }
    aviso.innerHTML = '<div class="alerta alerta-warning" style="margin:0;">'
        + '<i class="fas fa-spinner fa-spin"></i>'
        + ' <span>GPS não encontrado na foto. Obtendo localização do dispositivo...</span></div>';
    aviso.style.display = 'block';

    const btn = document.getElementById('btn-gps');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Obtendo localização...';

    navigator.geolocation.getCurrentPosition(
        pos => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            colocarMarcador(lat, lng);
            mapa.setView([lat, lng], 17);
            document.getElementById('latitude').value  = lat.toFixed(8);
            document.getElementById('longitude').value = lng.toFixed(8);
            aviso.innerHTML = '<div class="alerta alerta-success" style="margin:0;">'
                + '<i class="fas fa-satellite-dish"></i>'
                + ' <span>Localização obtida pelo dispositivo: '
                + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</span></div>';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Localização obtida';
        },
        err => {
            console.error('geolocation erro:', err);
            aviso.innerHTML = '<div class="alerta alerta-warning" style="margin:0;">'
                + '<i class="fas fa-exclamation-triangle"></i>'
                + ' <span>Não foi possível obter o GPS. Ajuste o marcador no mapa manualmente.</span></div>';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-crosshairs"></i> Usar minha localização atual';
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}

// ── ESPECIALISTA ───────────────────────────────────────────────────────────────
function selecionarEsp(id) {
    document.getElementById('especialista_id').value = id;
    document.querySelectorAll('.esp-card').forEach(c => c.classList.remove('selecionado'));
    const card = document.getElementById('card-' + id);
    if (card) {
        card.classList.add('selecionado');
        card.querySelector('input[type=radio]').checked = true;
    }
}

// ── VALIDAÇÃO ANTES DE ENVIAR ──────────────────────────────────────────────────
document.getElementById('form-exemplar').addEventListener('submit', function(e) {
    const espId  = document.getElementById('especie_id').value;
    const espHid = document.getElementById('especialista_id');

    if (!espId) {
        e.preventDefault();
        alert('Selecione a espécie.');
        return;
    }
    if (espHid && !espHid.value) {
        e.preventDefault();
        alert('Selecione um especialista orientador.');
        return;
    }

    const btn = document.getElementById('btn-salvar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
});
</script>
</body>
</html>
