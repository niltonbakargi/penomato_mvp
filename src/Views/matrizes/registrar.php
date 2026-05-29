<?php
// ============================================================
// BANCO DE MATRIZES — REGISTRAR NOVA MATRIZ
// ============================================================

require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../../Controllers/auth/verificar_acesso.php';

if (!estaLogado()) {
    header('Location: /penomato_mvp/src/Views/auth/login.php?redirect=' . urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
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

    .registrar-header {
        text-align: center;
        margin-bottom: 28px;
    }

    .registrar-header h2 {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--cinza-800);
    }

    .registrar-header p {
        color: var(--cinza-600);
        font-size: 0.9rem;
    }

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
        width: 24px;
        height: 24px;
        background: var(--cor-primaria);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    /* ── Fotos ── */
    .fotos-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 10px;
    }

    .foto-slot {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        aspect-ratio: 1;
        cursor: pointer;
    }

    .foto-slot input[type="file"] {
        display: none;
    }

    .foto-slot-inner {
        width: 100%;
        height: 100%;
        border: 2px dashed var(--cinza-300);
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: var(--cinza-50);
        transition: all 0.25s;
        padding: 8px;
        text-align: center;
    }

    .foto-slot-inner:hover {
        border-color: var(--cor-primaria);
        background: var(--verde-100);
    }

    .foto-slot.obrigatorio .foto-slot-inner {
        border-color: var(--cor-primaria);
        border-style: solid;
        border-width: 2px;
        background: var(--verde-50);
    }

    .foto-slot-inner i {
        font-size: 1.6rem;
        color: var(--cinza-400);
    }

    .foto-slot.obrigatorio .foto-slot-inner i {
        color: var(--cor-primaria);
    }

    .foto-slot-inner span {
        font-size: 0.72rem;
        font-weight: 600;
        color: var(--cinza-500);
        line-height: 1.2;
    }

    .foto-slot.obrigatorio .foto-slot-inner span {
        color: var(--cor-primaria);
    }

    .foto-slot-inner small {
        font-size: 0.65rem;
        color: var(--cinza-400);
    }

    /* Preview da foto no slot */
    .foto-preview {
        display: none;
        position: absolute;
        inset: 0;
        border-radius: 12px;
        overflow: hidden;
    }

    .foto-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .foto-preview .remover-foto {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 22px;
        height: 22px;
        background: rgba(220,53,69,0.85);
        color: white;
        border: none;
        border-radius: 50%;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .foto-slot.tem-foto .foto-slot-inner {
        display: none;
    }

    .foto-slot.tem-foto .foto-preview {
        display: block;
    }

    /* ── GPS ── */
    .gps-status {
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    .gps-status.aguardando { background: var(--cinza-100); color: var(--cinza-600); }
    .gps-status.capturado  { background: var(--sucesso-fundo); color: var(--sucesso-texto); }
    .gps-status.erro       { background: var(--perigo-fundo);  color: var(--perigo-texto); }

    .btn-gps {
        background: var(--cor-primaria);
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 600;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        cursor: pointer;
        transition: background 0.2s;
        font-size: 0.95rem;
    }

    .btn-gps:hover { background: var(--cor-primaria-hover); }

    /* ── Espécie ── */
    .autocomplete-wrap { position: relative; }

    .autocomplete-lista {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid var(--cinza-200);
        border-radius: 10px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        z-index: 200;
        max-height: 200px;
        overflow-y: auto;
        display: none;
    }

    .autocomplete-item {
        padding: 10px 16px;
        cursor: pointer;
        font-size: 0.88rem;
        transition: background 0.15s;
    }

    .autocomplete-item:hover { background: var(--verde-100); color: var(--cor-primaria); }
    .autocomplete-item em { font-style: normal; font-weight: 700; color: var(--cor-primaria); }

    .form-label { font-weight: 600; font-size: 0.88rem; color: var(--cinza-700); margin-bottom: 5px; }

    .form-control, .form-select {
        border-radius: 10px;
        border-color: var(--cinza-200);
        padding: 10px 14px;
        font-size: 0.92rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--cor-primaria);
        box-shadow: 0 0 0 3px rgba(11,94,66,0.1);
    }

    .badge-obrigatorio {
        font-size: 0.7rem;
        background: var(--perigo-fundo);
        color: var(--perigo-texto);
        padding: 2px 8px;
        border-radius: 10px;
        font-weight: 600;
        margin-left: 6px;
    }

    /* ── Botão enviar ── */
    .btn-registrar {
        background: var(--cor-primaria);
        color: white;
        border: none;
        padding: 16px;
        border-radius: 12px;
        font-size: 1.05rem;
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

    @media (max-width: 480px) {
        .fotos-grid { grid-template-columns: 1fr 1fr; }
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
                <span class="badge-obrigatorio ms-1">Árvore inteira obrigatória</span>
            </h5>

            <div class="fotos-grid">
                <?php foreach ($partes_fotos as $parte => $cfg): ?>
                <div class="foto-slot <?php echo $cfg['obrigatorio'] ? 'obrigatorio' : ''; ?>"
                     id="slot-<?php echo $parte; ?>"
                     onclick="document.getElementById('foto_<?php echo $parte; ?>').click()">

                    <input type="file"
                           id="foto_<?php echo $parte; ?>"
                           name="foto_<?php echo $parte; ?>"
                           accept="image/*"
                           capture="environment"
                           <?php echo $cfg['obrigatorio'] ? 'required' : ''; ?>>

                    <div class="foto-slot-inner">
                        <i class="fas <?php echo $cfg['icone']; ?>"></i>
                        <span><?php echo $cfg['label']; ?></span>
                        <?php if ($cfg['obrigatorio']): ?>
                            <small>obrigatória</small>
                        <?php endif; ?>
                    </div>

                    <div class="foto-preview" id="preview-<?php echo $parte; ?>">
                        <img src="" alt="<?php echo $cfg['label']; ?>" id="img-<?php echo $parte; ?>">
                        <button type="button" class="remover-foto"
                                onclick="removerFoto(event, '<?php echo $parte; ?>')"
                                title="Remover foto">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 2. Localização -->
        <div class="card-secao">
            <h5>
                <span class="num">2</span>
                Localização
                <span class="badge-obrigatorio ms-1">obrigatória</span>
            </h5>

            <div class="gps-status aguardando" id="gps-status">
                <i class="fas fa-map-marker-alt"></i>
                <span>GPS ainda não capturado</span>
            </div>

            <button type="button" class="btn-gps" id="btn-captar-gps">
                <i class="fas fa-crosshairs"></i> Captar Minha Localização
            </button>

            <input type="hidden" id="latitude"  name="latitude">
            <input type="hidden" id="longitude" name="longitude">

            <div class="text-center mt-2">
                <a style="font-size:0.82rem; color:var(--cinza-400); cursor:pointer; text-decoration:underline"
                   onclick="toggleManual()">Inserir coordenadas manualmente</a>
            </div>

            <div id="campos-manuais" style="display:none" class="row g-2 mt-1">
                <div class="col-6">
                    <label class="form-label">Latitude</label>
                    <input type="number" step="0.00000001" class="form-control" id="lat-manual"
                           placeholder="-20.4697">
                </div>
                <div class="col-6">
                    <label class="form-label">Longitude</label>
                    <input type="number" step="0.00000001" class="form-control" id="lon-manual"
                           placeholder="-54.6201">
                </div>
            </div>
        </div>

        <!-- 3. Identificação -->
        <div class="card-secao">
            <h5><span class="num">3</span> Identificação <small class="text-muted fw-normal ms-1">(opcional)</small></h5>

            <div class="mb-3">
                <label class="form-label">Nome popular</label>
                <select class="form-select" id="select-popular" name="especie_nome_popular">
                    <option value="">Selecionar...</option>
                    <?php foreach ($nomes_populares as $nome): ?>
                        <option value="<?php echo $nome === 'Outro...' ? '' : htmlspecialchars($nome); ?>"
                                <?php echo $nome === 'Outro...' ? 'data-outro="1"' : ''; ?>>
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
                    <small class="text-muted fw-normal">— preenchido automaticamente ou busque</small>
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
            <h5><span class="num">4</span> Observações <small class="text-muted fw-normal ms-1">(opcional)</small></h5>
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

<script>
// ── Preview de foto por slot ─────────────────────────────────
<?php foreach (array_keys($partes_fotos) as $parte): ?>
document.getElementById('foto_<?php echo $parte; ?>').addEventListener('change', function () {
    mostrarPreview('<?php echo $parte; ?>', this);
});
<?php endforeach; ?>

function mostrarPreview(parte, input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('img-' + parte).src = e.target.result;
        document.getElementById('slot-' + parte).classList.add('tem-foto');
    };
    reader.readAsDataURL(input.files[0]);
}

function removerFoto(e, parte) {
    e.stopPropagation();
    document.getElementById('foto_' + parte).value = '';
    document.getElementById('img-' + parte).src = '';
    document.getElementById('slot-' + parte).classList.remove('tem-foto');
}

// ── GPS ──────────────────────────────────────────────────────
document.getElementById('btn-captar-gps').addEventListener('click', function () {
    const status = document.getElementById('gps-status');
    status.className = 'gps-status aguardando';
    status.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Captando localização...</span>';

    if (!navigator.geolocation) {
        status.className = 'gps-status erro';
        status.innerHTML = '<i class="fas fa-times-circle"></i> <span>GPS não disponível.</span>';
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
        () => {
            status.className = 'gps-status erro';
            status.innerHTML = '<i class="fas fa-exclamation-circle"></i> <span>Não foi possível capturar. Use o modo manual.</span>';
        },
        { enableHighAccuracy: true, timeout: 12000 }
    );
});

function toggleManual() {
    const div = document.getElementById('campos-manuais');
    div.style.display = div.style.display === 'none' ? 'flex' : 'none';
}

document.getElementById('lat-manual').addEventListener('input', sincronizarManual);
document.getElementById('lon-manual').addEventListener('input', sincronizarManual);

function sincronizarManual() {
    const lat = document.getElementById('lat-manual').value;
    const lon = document.getElementById('lon-manual').value;
    if (lat && lon) {
        document.getElementById('latitude').value  = lat;
        document.getElementById('longitude').value = lon;
        const status = document.getElementById('gps-status');
        status.className = 'gps-status capturado';
        status.innerHTML = `<i class="fas fa-check-circle"></i> <span>Coordenadas manuais: ${lat}, ${lon}</span>`;
    }
}

// ── Nome popular → científico (REFLORA) ──────────────────────
document.getElementById('select-popular').addEventListener('change', function () {
    const opcao = this.options[this.selectedIndex];
    const outro = document.getElementById('campo-popular-outro');

    if (opcao.dataset.outro) {
        outro.style.display = 'block';
        return;
    }
    outro.style.display = 'none';

    const nomePopular = this.value;
    if (!nomePopular) return;

    fetch(`/penomato_mvp/src/Controllers/matrizes/buscar_cientifico.php?popular=${encodeURIComponent(nomePopular)}`)
        .then(r => r.json())
        .then(dados => {
            if (dados.nome) {
                document.getElementById('especie-busca').value = dados.nome;
            }
        });
});

// Campo "outro" → limpar científico
document.getElementById('nome-popular-outro').addEventListener('input', function () {
    document.getElementById('select-popular').value = '';
});

// ── Autocomplete científico ───────────────────────────────────
let debounce;
const inputBusca = document.getElementById('especie-busca');
const lista = document.getElementById('autocomplete-lista');

inputBusca.addEventListener('input', function () {
    const q = this.value.trim();
    clearTimeout(debounce);
    if (q.length < 3) { lista.style.display = 'none'; return; }

    debounce = setTimeout(() => {
        fetch(`/penomato_mvp/src/Controllers/matrizes/buscar_especie.php?q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(dados => {
                lista.innerHTML = '';
                if (!dados.length) { lista.style.display = 'none'; return; }
                dados.forEach(item => {
                    const li = document.createElement('div');
                    li.className = 'autocomplete-item';
                    li.innerHTML = item.nome.replace(new RegExp(`(${q})`, 'gi'), '<em>$1</em>');
                    li.addEventListener('click', () => {
                        inputBusca.value = item.nome;
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

// ── Validação antes de submeter ───────────────────────────────
document.getElementById('form-matriz').addEventListener('submit', function (e) {
    // Aplica nome popular "outro" se necessário
    const sel = document.getElementById('select-popular');
    if (!sel.value) {
        const outro = document.getElementById('nome-popular-outro').value.trim();
        if (outro) sel.value = outro;
    }

    if (!document.getElementById('latitude').value || !document.getElementById('longitude').value) {
        e.preventDefault();
        alert('Capture o GPS antes de registrar.');
        return;
    }
});
</script>

<?php include __DIR__ . '/../includes/rodape.php'; ?>
