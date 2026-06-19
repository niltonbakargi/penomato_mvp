<?php
// ============================================================
// GERENCIAR MATRIZES — PAINEL DO GESTOR
// ============================================================

require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../../Controllers/auth/verificar_acesso.php';

if (!estaLogado() || getTipoUsuario() !== 'gestor') {
    header('Location: /penomato_mvp/src/Views/auth/login.php');
    exit;
}

// ── Processa remoção ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remover_id'])) {
    $id     = intval($_POST['remover_id']);
    $motivo = trim($_POST['motivo'] ?? '');

    if ($id > 0) {
        // Marca como inativo (soft delete — preserva dados)
        atualizar('matrizes', ['status' => 'inativo'], 'id = :id', [':id' => $id]);

        error_log(sprintf(
            '[GESTOR_AUDIT] remover_matriz | gestor_id=%d | matriz_id=%d | motivo=%s | ip=%s',
            $_SESSION['usuario_id'], $id, $motivo, $_SERVER['REMOTE_ADDR'] ?? ''
        ));

        mensagemSucesso("Matriz removida do mapa com sucesso.");
    }

    header('Location: /penomato_mvp/src/Views/matrizes/gerenciar_matrizes.php');
    exit;
}

// ── Busca matrizes ────────────────────────────────────────────
$busca = trim($_GET['q'] ?? '');
$termo = '%' . $busca . '%';

$matrizes = buscarTodos(
    "SELECT m.*, u.nome AS cadastrador
     FROM matrizes m
     JOIN usuarios u ON u.id = m.cadastrado_por
     WHERE m.status = 'ativo'
       AND (m.especie_nome LIKE ? OR m.especie_nome_popular LIKE ? OR m.codigo LIKE ? OR u.nome LIKE ?)
     ORDER BY m.data_cadastro DESC",
    [$termo, $termo, $termo, $termo]
);

$titulo_pagina = 'Gerenciar Matrizes — Gestor';
include __DIR__ . '/../includes/cabecalho.php';
?>

<style>
    .gerenciar-wrap {
        max-width: 800px;
        margin: 30px auto 60px;
        padding: 0 16px;
    }

    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 10px;
    }

    .page-header h2 {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--cinza-800);
        margin: 0;
    }

    .filtro-wrap {
        position: relative;
        margin-bottom: 16px;
    }

    .filtro-wrap i {
        position: absolute;
        left: 14px; top: 50%;
        transform: translateY(-50%);
        color: var(--cinza-400);
    }

    .filtro-input {
        width: 100%;
        padding: 11px 14px 11px 38px;
        border: 1.5px solid var(--cinza-200);
        border-radius: 12px;
        font-size: 0.95rem;
        outline: none;
    }

    .filtro-input:focus {
        border-color: var(--cor-primaria);
        box-shadow: 0 0 0 3px rgba(11,94,66,0.08);
    }

    .contador {
        font-size: 0.82rem;
        color: var(--cinza-400);
        margin-bottom: 12px;
    }

    /* ── Card de matriz ── */
    .matriz-row {
        display: flex;
        align-items: center;
        gap: 14px;
        background: white;
        border-radius: 14px;
        padding: 12px 14px;
        margin-bottom: 10px;
        box-shadow: 0 1px 6px rgba(0,0,0,0.07);
    }

    .matriz-thumb {
        width: 60px;
        height: 60px;
        border-radius: 10px;
        object-fit: cover;
        flex-shrink: 0;
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
    }

    .matriz-meta {
        font-size: 0.75rem;
        color: var(--cinza-400);
        margin-top: 2px;
    }

    .btn-remover {
        flex-shrink: 0;
        background: var(--perigo-fundo);
        color: var(--perigo-texto);
        border: none;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 0.8rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .btn-remover:hover {
        background: var(--perigo-cor);
        color: white;
    }

    /* ── Modal de confirmação ── */
    .modal-fundo {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .modal-fundo.ativo { display: flex; }

    .modal-box {
        background: white;
        border-radius: 16px;
        padding: 28px;
        max-width: 420px;
        width: 100%;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }

    .modal-box h5 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--cinza-800);
        margin-bottom: 6px;
    }

    .modal-box p {
        font-size: 0.9rem;
        color: var(--cinza-600);
        margin-bottom: 16px;
    }

    .modal-thumb {
        width: 100%;
        height: 140px;
        object-fit: cover;
        border-radius: 10px;
        margin-bottom: 16px;
    }

    .modal-box textarea {
        width: 100%;
        border: 1.5px solid var(--cinza-200);
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 0.9rem;
        resize: none;
        margin-bottom: 16px;
    }

    .modal-box textarea:focus {
        border-color: var(--perigo-cor);
        outline: none;
    }

    .modal-btns {
        display: flex;
        gap: 10px;
    }

    .btn-cancelar {
        flex: 1;
        padding: 12px;
        border-radius: 10px;
        border: 1.5px solid var(--cinza-200);
        background: white;
        color: var(--cinza-600);
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-cancelar:hover { background: var(--cinza-100); }

    .btn-confirmar-remover {
        flex: 1;
        padding: 12px;
        border-radius: 10px;
        border: none;
        background: var(--perigo-cor);
        color: white;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.2s;
    }

    .btn-confirmar-remover:hover { background: #b91c2c; }

    .lista-vazia {
        text-align: center;
        padding: 50px 20px;
        color: var(--cinza-400);
    }

    .lista-vazia i { font-size: 2.5rem; margin-bottom: 12px; display: block; }
</style>

<!-- Modal de confirmação -->
<div class="modal-fundo" id="modal-remover">
    <div class="modal-box">
        <img src="" id="modal-foto" alt="" class="modal-thumb">
        <h5 id="modal-nome">Remover matriz</h5>
        <p>Esta matriz será removida do mapa. Os dados ficam registrados no banco para auditoria.</p>

        <form method="POST">
            <input type="hidden" name="remover_id" id="modal-id">
            <textarea name="motivo" rows="2"
                      placeholder="Motivo da remoção (opcional)..."></textarea>
            <div class="modal-btns">
                <button type="button" class="btn-cancelar" onclick="fecharModal()">
                    Cancelar
                </button>
                <button type="submit" class="btn-confirmar-remover">
                    <i class="fas fa-trash me-1"></i> Remover
                </button>
            </div>
        </form>
    </div>
</div>

<div class="gerenciar-wrap">

    <div class="page-header">
        <h2><i class="fas fa-tree text-success me-2"></i>Gerenciar Matrizes</h2>
        <a href="/penomato_mvp/src/Controllers/controlador_gestor.php"
           class="text-muted small text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i>Painel do Gestor
        </a>
    </div>

    <!-- Filtro -->
    <form method="GET" class="filtro-wrap">
        <i class="fas fa-search"></i>
        <input type="text"
               name="q"
               value="<?php echo htmlspecialchars($busca); ?>"
               class="filtro-input"
               placeholder="Buscar por espécie, código ou cadastrador...">
    </form>

    <div class="contador">
        <?php echo count($matrizes); ?> matriz<?php echo count($matrizes) !== 1 ? 'es' : ''; ?> ativa<?php echo count($matrizes) !== 1 ? 's' : ''; ?>
        <?php if ($busca): ?>
            para "<strong><?php echo htmlspecialchars($busca); ?></strong>"
        <?php endif; ?>
    </div>

    <?php if (empty($matrizes)): ?>
    <div class="lista-vazia">
        <i class="fas fa-tree"></i>
        <?php echo $busca ? 'Nenhuma matriz encontrada.' : 'Nenhuma matriz registrada ainda.'; ?>
    </div>
    <?php else: ?>
        <?php foreach ($matrizes as $m):
            $nome  = $m['especie_nome_popular'] ?: ($m['especie_nome'] ?: 'Espécie não identificada');
            $cient = ($m['especie_nome'] && $m['especie_nome_popular']) ? $m['especie_nome'] : '';
        ?>
        <div class="matriz-row">
            <a href="/penomato_mvp/src/Views/matrizes/ficha.php?id=<?php echo $m['id']; ?>">
                <img src="/penomato_mvp/<?php echo htmlspecialchars($m['foto_geral']); ?>"
                     alt="<?php echo htmlspecialchars($nome); ?>"
                     class="matriz-thumb">
            </a>
            <div class="matriz-info">
                <div class="matriz-nome"><?php echo htmlspecialchars($nome); ?></div>
                <?php if ($cient): ?>
                <div class="matriz-cientifico"><?php echo htmlspecialchars($cient); ?></div>
                <?php endif; ?>
                <div class="matriz-meta">
                    <i class="fas fa-tag me-1"></i><?php echo $m['codigo']; ?>
                    &nbsp;·&nbsp;
                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($m['cadastrador']); ?>
                    &nbsp;·&nbsp;
                    <?php echo date('d/m/Y', strtotime($m['data_cadastro'])); ?>
                </div>
            </div>
            <button class="btn-remover"
                    onclick="abrirModal(
                        <?php echo $m['id']; ?>,
                        '<?php echo addslashes(htmlspecialchars($nome)); ?>',
                        '/penomato_mvp/<?php echo addslashes($m['foto_geral']); ?>'
                    )">
                <i class="fas fa-trash"></i> Remover
            </button>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<script>
function abrirModal(id, nome, foto) {
    document.getElementById('modal-id').value   = id;
    document.getElementById('modal-nome').textContent = 'Remover: ' + nome;
    document.getElementById('modal-foto').src   = foto;
    document.getElementById('modal-remover').classList.add('ativo');
}

function fecharModal() {
    document.getElementById('modal-remover').classList.remove('ativo');
}

document.getElementById('modal-remover').addEventListener('click', function (e) {
    if (e.target === this) fecharModal();
});
</script>

<?php include __DIR__ . '/../includes/rodape.php'; ?>
