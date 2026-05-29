<?php
// ============================================================
// BANCO DE MATRIZES — ADICIONAR FOTO DE PARTE
// ============================================================

require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../../Controllers/auth/verificar_acesso.php';

if (!estaLogado()) {
    header('Location: /penomato_mvp/src/Views/auth/login.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
$matriz = buscarUm(
    "SELECT id, codigo, especie_nome, especie_nome_popular FROM matrizes WHERE id = ? AND status = 'ativo'",
    [$id]
);

if (!$matriz) {
    header('Location: /penomato_mvp/src/Views/matrizes/mapa.php');
    exit;
}

$nome_exibir = $matriz['especie_nome_popular'] ?: ($matriz['especie_nome'] ?: 'Espécie não identificada');
$titulo_pagina = "Adicionar Foto — Matriz {$matriz['codigo']}";
$partes = ['folha', 'flor', 'fruto', 'casca', 'semente'];

include __DIR__ . '/../includes/cabecalho.php';
?>

<style>
    .adicionar-wrap {
        max-width: 540px;
        margin: 30px auto 60px;
        padding: 0 16px;
    }

    .card-secao {
        background: white;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    }

    .upload-area {
        border: 2px dashed var(--cinza-300);
        border-radius: 12px;
        padding: 32px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: var(--cinza-50);
    }

    .upload-area:hover {
        border-color: var(--cor-primaria);
        background: var(--verde-100);
    }

    .upload-area i { font-size: 2.5rem; color: var(--cinza-400); margin-bottom: 12px; }
    .upload-area p { color: var(--cinza-600); margin: 0; font-size: 0.95rem; }

    #preview-parte {
        display: none;
        width: 100%;
        max-height: 250px;
        object-fit: cover;
        border-radius: 10px;
        margin-top: 12px;
    }

    .form-label { font-weight: 600; font-size: 0.9rem; color: var(--cinza-700); }
    .form-select, .form-control { border-radius: 10px; border-color: var(--cinza-200); padding: 10px 14px; }
    .form-select:focus, .form-control:focus {
        border-color: var(--cor-primaria);
        box-shadow: 0 0 0 3px rgba(11,94,66,0.1);
    }

    .btn-enviar {
        background: var(--cor-primaria);
        color: white;
        border: none;
        padding: 14px;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 700;
        width: 100%;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-enviar:hover {
        background: var(--cor-primaria-hover);
        transform: translateY(-2px);
    }
</style>

<div class="adicionar-wrap">

    <div class="text-center mb-4">
        <h2 style="font-size:1.5rem; font-weight:700; color:var(--cinza-800)">
            <i class="fas fa-camera text-success me-2"></i>Adicionar Foto de Parte
        </h2>
        <p class="text-muted small">
            Matriz <strong><?php echo htmlspecialchars($matriz['codigo']); ?></strong>
            — <?php echo htmlspecialchars($nome_exibir); ?>
        </p>
    </div>

    <div class="card-secao">
        <form action="/penomato_mvp/src/Controllers/matrizes/processar_foto_parte.php"
              method="POST" enctype="multipart/form-data">
            <input type="hidden" name="matriz_id" value="<?php echo $id; ?>">

            <div class="mb-3">
                <label class="form-label">Parte da planta</label>
                <select class="form-select" name="parte" required>
                    <option value="">Selecionar...</option>
                    <?php foreach ($partes as $p): ?>
                        <option value="<?php echo $p; ?>"><?php echo ucfirst($p); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label">Foto</label>
                <div class="upload-area" onclick="document.getElementById('foto_parte').click()">
                    <i class="fas fa-camera"></i>
                    <p>Toque para fotografar ou selecionar</p>
                </div>
                <input type="file" id="foto_parte" name="foto_parte"
                       accept="image/*" capture="environment"
                       style="display:none" required>
                <img id="preview-parte" src="" alt="Preview">
            </div>

            <button type="submit" class="btn-enviar">
                <i class="fas fa-upload me-2"></i>Enviar Foto
            </button>
        </form>
    </div>

    <div class="text-center mt-3">
        <a href="/penomato_mvp/src/Views/matrizes/ficha.php?id=<?php echo $id; ?>"
           class="text-muted small">
            <i class="fas fa-arrow-left me-1"></i> Voltar à ficha
        </a>
    </div>

</div>

<script>
document.getElementById('foto_parte').addEventListener('change', function () {
    const reader = new FileReader();
    reader.onload = e => {
        const preview = document.getElementById('preview-parte');
        preview.src = e.target.result;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(this.files[0]);
});
</script>

<?php include __DIR__ . '/../includes/rodape.php'; ?>
