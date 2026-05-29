<?php
// ============================================================
// BANCO DE MATRIZES — REGISTRAR NOVA MATRIZ
// ============================================================

require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../../Controllers/auth/verificar_acesso.php';

// Exige login
if (!estaLogado()) {
    header('Location: /penomato_mvp/src/Views/auth/login.php');
    exit;
}

$titulo_pagina = 'Registrar Matriz — Banco de Matrizes';

$nomes_populares = [
    'Angico', 'Araticum', 'Aroeira', 'Buriti', 'Cagaita', 'Cedro',
    'Copaíba', 'Embaúba', 'Gameleira', 'Gonçalo-alves', 'Ipê amarelo',
    'Ipê branco', 'Ipê rosa', 'Ipê roxo', 'Jatobá', 'Jacarandá',
    'Lixeira', 'Murici', 'Paineira', 'Pequi', 'Peroba', 'Sobrasil',
    'Sucupira branca', 'Sucupira preta', 'Tamboril', 'Tingui', 'Vinhático',
];

include __DIR__ . '/../includes/cabecalho.php';
?>

<style>
    .registrar-wrap {
        max-width: 640px;
        margin: 30px auto 60px;
        padding: 0 16px;
    }

    .registrar-header {
        text-align: center;
        margin-bottom: 32px;
    }

    .registrar-header h2 {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--cinza-800);
    }

    .registrar-header p {
        color: var(--cinza-600);
        font-size: 0.95rem;
    }

    .card-secao {
        background: white;
        border-radius: 16px;
        padding: 28px;
        margin-bottom: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    }

    .card-secao h5 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--cor-primaria);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-secao h5 i {
        width: 30px;
        height: 30px;
        background: var(--verde-100);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
    }

    /* Upload de foto */
    .upload-area {
        border: 2px dashed var(--cinza-300);
        border-radius: 12px;
        padding: 32px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: var(--cinza-50);
    }

    .upload-area:hover, .upload-area.drag-over {
        border-color: var(--cor-primaria);
        background: var(--verde-100);
    }

    .upload-area i {
        font-size: 2.5rem;
        color: var(--cinza-400);
        margin-bottom: 12px;
    }

    .upload-area p {
        color: var(--cinza-600);
        margin: 0;
        font-size: 0.95rem;
    }

    .upload-area small {
        color: var(--cinza-400);
        font-size: 0.8rem;
    }

    #preview-foto {
        display: none;
        width: 100%;
        max-height: 300px;
        object-fit: cover;
        border-radius: 10px;
        margin-top: 12px;
    }

    /* GPS */
    .gps-status {
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    .gps-status.aguardando {
        background: var(--cinza-100);
        color: var(--cinza-600);
    }

    .gps-status.capturado {
        background: var(--sucesso-fundo);
        color: var(--sucesso-texto);
    }

    .gps-status.erro {
        background: var(--perigo-fundo);
        color: var(--perigo-texto);
    }

    .btn-gps {
        background: var(--cor-primaria);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 0.95rem;
    }

    .btn-gps:hover {
        background: var(--cor-primaria-hover);
    }

    .campos-manuais {
        display: none;
        gap: 12px;
    }

    .campos-manuais.visivel {
        display: flex;
    }

    .link-manual {
        text-align: center;
        margin-top: 10px;
    }

    .link-manual a {
        font-size: 0.85rem;
        color: var(--cinza-400);
        cursor: pointer;
        text-decoration: underline;
    }

    /* Autocomplete espécie */
    .autocomplete-wrap {
        position: relative;
    }

    .autocomplete-lista {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid var(--cinza-200);
        border-radius: 10px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        z-index: 100;
        max-height: 220px;
        overflow-y: auto;
        display: none;
    }

    .autocomplete-item {
        padding: 10px 16px;
        cursor: pointer;
        font-size: 0.9rem;
        transition: background 0.2s;
    }

    .autocomplete-item:hover {
        background: var(--verde-100);
        color: var(--cor-primaria);
    }

    .autocomplete-item em {
        font-style: normal;
        font-weight: 600;
        color: var(--cor-primaria);
    }

    /* Botão enviar */
    .btn-registrar {
        background: var(--cor-primaria);
        color: white;
        border: none;
        padding: 16px;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 700;
        width: 100%;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .btn-registrar:hover {
        background: var(--cor-primaria-hover);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(11,94,66,0.3);
    }

    .btn-registrar:disabled {
        background: var(--cinza-300);
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .form-label {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--cinza-700);
        margin-bottom: 6px;
    }

    .form-control, .form-select {
        border-radius: 10px;
        border-color: var(--cinza-200);
        padding: 10px 14px;
        font-size: 0.95rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--cor-primaria);
        box-shadow: 0 0 0 3px rgba(11,94,66,0.1);
    }
</style>

<div class="registrar-wrap">

    <div class="registrar-header">
        <h2><i class="fas fa-plus-circle text-success me-2"></i>Registrar Matriz</h2>
        <p>Preencha o que souber. GPS e foto são obrigatórios.</p>
    </div>

    <form action="/penomato_mvp/src/Controllers/matrizes/processar_registro.php"
          method="POST" enctype="multipart/form-data" id="form-matriz">

        <!-- Foto geral -->
        <div class="card-secao">
            <h5><i class="fas fa-camera"></i> Foto da Árvore</h5>

            <div class="upload-area" id="upload-area" onclick="document.getElementById('foto_geral').click()">
                <i class="fas fa-camera"></i>
                <p>Toque para fotografar ou selecionar imagem</p>
                <small>JPG ou PNG — máximo 10 MB</small>
            </div>
            <input type="file" id="foto_geral" name="foto_geral" accept="image/*" capture="environment"
                   style="display:none" required>
            <img id="preview-foto" src="" alt="Preview">
        </div>

        <!-- Localização -->
        <div class="card-secao">
            <h5><i class="fas fa-map-marker-alt"></i> Localização</h5>

            <div class="gps-status aguardando" id="gps-status">
                <i class="fas fa-circle-notch fa-spin"></i>
                <span>GPS ainda não capturado</span>
            </div>

            <button type="button" class="btn-gps" id="btn-captar-gps">
                <i class="fas fa-crosshairs"></i> Captar Minha Localização
            </button>

            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">

            <div class="link-manual">
                <a onclick="toggleManual()">Inserir coordenadas manualmente</a>
            </div>

            <div class="campos-manuais mt-3" id="campos-manuais">
                <div class="flex-fill">
                    <label class="form-label">Latitude</label>
                    <input type="number" step="0.00000001" class="form-control" id="lat-manual"
                           placeholder="-20.12345678">
                </div>
                <div class="flex-fill">
                    <label class="form-label">Longitude</label>
                    <input type="number" step="0.00000001" class="form-control" id="lon-manual"
                           placeholder="-54.87654321">
                </div>
            </div>
        </div>

        <!-- Espécie -->
        <div class="card-secao">
            <h5><i class="fas fa-leaf"></i> Identificação</h5>

            <div class="mb-3">
                <label class="form-label">Nome científico</label>
                <div class="autocomplete-wrap">
                    <input type="text" id="especie-busca" class="form-control"
                           placeholder="Ex: Handroanthus impetiginosus" autocomplete="off">
                    <input type="hidden" name="especie_nome" id="especie_nome">
                    <div class="autocomplete-lista" id="autocomplete-lista"></div>
                </div>
                <small class="text-muted">Busca na base REFLORA — opcional</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Nome popular</label>
                <select class="form-select" name="especie_nome_popular" id="select-popular">
                    <option value="">Selecionar...</option>
                    <?php foreach ($nomes_populares as $nome): ?>
                        <option value="<?php echo htmlspecialchars($nome); ?>">
                            <?php echo htmlspecialchars($nome); ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="__outro__">Outro (digitar abaixo)</option>
                </select>
            </div>

            <div class="mb-0" id="campo-outro" style="display:none">
                <label class="form-label">Nome popular (outro)</label>
                <input type="text" class="form-control" id="nome-popular-outro"
                       placeholder="Digite o nome popular">
            </div>

            <small class="text-muted d-block mt-2">
                Não sabe o nome? Deixe em branco e a comunidade pode ajudar nos comentários.
            </small>
        </div>

        <!-- Observações -->
        <div class="card-secao">
            <h5><i class="fas fa-comment-alt"></i> Observações</h5>
            <textarea class="form-control" name="observacoes" rows="3"
                      placeholder="Ex: Copa muito densa, frutos maduros em setembro, árvore próxima ao ribeirão..."></textarea>
        </div>

        <button type="submit" class="btn-registrar" id="btn-enviar">
            <i class="fas fa-map-marker-alt"></i> Registrar Matriz
        </button>

    </form>

    <div class="text-center mt-3">
        <a href="/penomato_mvp/src/Views/matrizes/index.php" class="text-muted small">
            <i class="fas fa-arrow-left me-1"></i> Cancelar
        </a>
    </div>

</div>

<script>
// ── Preview de foto ──────────────────────────────────────────
document.getElementById('foto_geral').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        const preview = document.getElementById('preview-foto');
        preview.src = e.target.result;
        preview.style.display = 'block';
        document.getElementById('upload-area').style.display = 'none';
    };
    reader.readAsDataURL(file);
});

// Drag and drop
const uploadArea = document.getElementById('upload-area');
uploadArea.addEventListener('dragover', e => { e.preventDefault(); uploadArea.classList.add('drag-over'); });
uploadArea.addEventListener('dragleave', () => uploadArea.classList.remove('drag-over'));
uploadArea.addEventListener('drop', e => {
    e.preventDefault();
    uploadArea.classList.remove('drag-over');
    const input = document.getElementById('foto_geral');
    input.files = e.dataTransfer.files;
    input.dispatchEvent(new Event('change'));
});

// ── GPS ──────────────────────────────────────────────────────
document.getElementById('btn-captar-gps').addEventListener('click', function () {
    const status = document.getElementById('gps-status');
    status.className = 'gps-status aguardando';
    status.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Captando localização...</span>';

    if (!navigator.geolocation) {
        status.className = 'gps-status erro';
        status.innerHTML = '<i class="fas fa-times-circle"></i> <span>GPS não disponível neste dispositivo.</span>';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        pos => {
            const lat = pos.coords.latitude.toFixed(8);
            const lon = pos.coords.longitude.toFixed(8);
            document.getElementById('latitude').value  = lat;
            document.getElementById('longitude').value = lon;
            status.className = 'gps-status capturado';
            status.innerHTML = `<i class="fas fa-check-circle"></i> <span>Localização capturada: ${lat}, ${lon}</span>`;
        },
        err => {
            status.className = 'gps-status erro';
            status.innerHTML = '<i class="fas fa-exclamation-circle"></i> <span>Não foi possível obter a localização. Tente o modo manual.</span>';
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
});

function toggleManual() {
    const div = document.getElementById('campos-manuais');
    div.classList.toggle('visivel');
}

// Sincronizar campos manuais com os hidden
['lat-manual', 'lon-manual'].forEach(id => {
    document.getElementById(id).addEventListener('input', function () {
        const lat = document.getElementById('lat-manual').value;
        const lon = document.getElementById('lon-manual').value;
        if (lat && lon) {
            document.getElementById('latitude').value  = lat;
            document.getElementById('longitude').value = lon;
            const status = document.getElementById('gps-status');
            status.className = 'gps-status capturado';
            status.innerHTML = `<i class="fas fa-check-circle"></i> <span>Coordenadas manuais: ${lat}, ${lon}</span>`;
        }
    });
});

// ── Autocomplete REFLORA ──────────────────────────────────────
let debounceTimer;
const inputBusca = document.getElementById('especie-busca');
const lista = document.getElementById('autocomplete-lista');

inputBusca.addEventListener('input', function () {
    const q = this.value.trim();
    document.getElementById('especie_nome').value = q;

    clearTimeout(debounceTimer);
    if (q.length < 3) { lista.style.display = 'none'; return; }

    debounceTimer = setTimeout(() => {
        fetch(`/penomato_mvp/src/Controllers/matrizes/buscar_especie.php?q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(dados => {
                lista.innerHTML = '';
                if (!dados.length) { lista.style.display = 'none'; return; }
                dados.forEach(item => {
                    const li = document.createElement('div');
                    li.className = 'autocomplete-item';
                    const destaque = item.nome.replace(new RegExp(`(${q})`, 'gi'), '<em>$1</em>');
                    li.innerHTML = destaque;
                    li.addEventListener('click', () => {
                        inputBusca.value = item.nome;
                        document.getElementById('especie_nome').value = item.nome;
                        lista.style.display = 'none';
                    });
                    lista.appendChild(li);
                });
                lista.style.display = 'block';
            });
    }, 300);
});

document.addEventListener('click', e => {
    if (!e.target.closest('.autocomplete-wrap')) lista.style.display = 'none';
});

// ── Nome popular "outro" ──────────────────────────────────────
document.getElementById('select-popular').addEventListener('change', function () {
    const outro = document.getElementById('campo-outro');
    outro.style.display = this.value === '__outro__' ? 'block' : 'none';
});

// Antes de submeter, pega valor do campo livre se "outro" selecionado
document.getElementById('form-matriz').addEventListener('submit', function (e) {
    const sel = document.getElementById('select-popular');
    if (sel.value === '__outro__') {
        sel.value = document.getElementById('nome-popular-outro').value.trim();
    }

    if (!document.getElementById('latitude').value || !document.getElementById('longitude').value) {
        e.preventDefault();
        alert('Por favor, capture o GPS antes de registrar.');
        return;
    }
});
</script>

<?php include __DIR__ . '/../includes/rodape.php'; ?>
