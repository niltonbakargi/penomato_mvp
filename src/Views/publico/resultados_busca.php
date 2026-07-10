<?php
// ================================================
// RESULTADOS DA BUSCA — lista + carrossel de imagens
// ================================================

session_start();
require_once __DIR__ . '/../../../config/banco_de_dados.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE . '/src/Views/publico/busca_caracteristicas.php');
    exit;
}

// ── WHERE dinâmico ───────────────────────────────
$campos_like = ['nome_cientifico_completo', 'nome_popular', 'familia'];
$todos_campos = [
    'nome_cientifico_completo', 'nome_popular', 'familia',
    'forma_folha', 'filotaxia_folha', 'tipo_folha', 'divisao_folha', 'paridade_pinnacao',
    'tamanho_folha', 'textura_folha', 'margem_folha', 'venacao_folha',
    'cor_flores', 'simetria_floral', 'numero_petalas', 'tamanho_flor', 'disposicao_flores', 'aroma',
    'tipo_fruto', 'tamanho_fruto', 'cor_fruto', 'textura_fruto', 'dispersao_fruto', 'aroma_fruto',
    'tipo_semente', 'tamanho_semente', 'cor_semente', 'textura_semente', 'quantidade_sementes',
    'tipo_caule', 'textura_caule', 'cor_caule', 'forma_caule', 'modificacao_caule',
    'ramificacao_caule', 'possui_espinhos', 'possui_latex'
];

$condicoes  = ["e.status != 'sem_dados'"];
$parametros = [];

foreach ($todos_campos as $campo) {
    $val = isset($_POST[$campo]) ? trim($_POST[$campo]) : '';
    if ($val === '' || $val === 'todos') continue;
    if (in_array($campo, $campos_like)) {
        $condicoes[] = "c.$campo LIKE ?";
        $parametros[] = '%' . $val . '%';
    } else {
        $condicoes[] = "c.$campo = ?";
        $parametros[] = $val;
    }
}

$where_sql = 'WHERE ' . implode(' AND ', $condicoes);

// ── Query espécies ───────────────────────────────
$sql = "SELECT e.id, c.nome_cientifico_completo, c.nome_popular, c.familia
        FROM especies_caracteristicas c
        INNER JOIN especies_administrativo e ON c.especie_id = e.id
        $where_sql
        ORDER BY c.nome_cientifico_completo
        LIMIT 200";

$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);
$especies = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total    = count($especies);

// ── Query imagens ────────────────────────────────
$slides = ['folha' => [], 'flor' => [], 'fruto' => [], 'caule' => [], 'semente' => []];

if ($total > 0) {
    $ids          = array_column($especies, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt_img = $pdo->prepare(
        "SELECT especie_id, parte_planta, caminho_imagem
         FROM especies_imagens
         WHERE especie_id IN ($placeholders)
           AND parte_planta IN ('folha','flor','fruto','caule','semente')
         ORDER BY especie_id, data_upload ASC"
    );
    $stmt_img->execute($ids);

    $raiz_fisica    = __DIR__ . '/../../../';
    $imgs_por_esp   = [];
    foreach ($stmt_img->fetchAll(PDO::FETCH_ASSOC) as $img) {
        if (!file_exists($raiz_fisica . $img['caminho_imagem'])) continue;
        $imgs_por_esp[$img['especie_id']][$img['parte_planta']][] = '/penomato_mvp/' . $img['caminho_imagem'];
    }

    // Monta slides na ordem das espécies da lista
    $nome_por_id = [];
    foreach ($especies as $esp) {
        $nome_por_id[$esp['id']] = $esp['nome_cientifico_completo'] ?: $esp['nome_popular'] ?: '—';
    }

    foreach ($ids as $eid) {
        foreach (array_keys($slides) as $parte) {
            $urls = $imgs_por_esp[$eid][$parte] ?? [];
            if (empty($urls)) continue;
            $slides[$parte][] = ['nome' => $nome_por_id[$eid], 'imgs' => $urls, 'id' => $eid];
        }
    }
}

$j_slides = json_encode($slides, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados da Busca — Penomato</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha384-blOohCVdhjmtROpu8+CfTnUWham9nkX7P7OZQMst+RUnhtoY/9qemFAkIKOYxDI3" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= APP_BASE ?>/assets/css/estilo.css">
    <style>
        body { background: #f5f0ea; padding: 0; margin: 0; font-family: 'DM Sans', sans-serif; }

        /* ── Topo ── */
        .topo {
            background: var(--cor-primaria);
            padding: 14px 28px;
            display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
            position: sticky; top: 0; z-index: 50;
        }
        .topo-titulo { color: white; font-size: 1rem; font-weight: 700; flex: 1; }
        .topo-total {
            background: rgba(255,255,255,.18); color: white;
            padding: 4px 14px; border-radius: 20px; font-size: .82rem; font-weight: 600;
        }
        .btn-voltar {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,.15); color: white; text-decoration: none;
            padding: 7px 16px; border-radius: 30px; font-size: .875rem; font-weight: 600;
            transition: background .2s;
        }
        .btn-voltar:hover { background: rgba(255,255,255,.28); color: white; text-decoration: none; }

        /* ── Wrapper ── */
        .wrapper { max-width: 860px; margin: 0 auto; padding: 24px 20px 80px; }

        /* ── Carrossel ── */
        .carrossel {
            background: #1a1a1a;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 12px;
            box-shadow: 0 6px 28px rgba(0,0,0,.25);
        }

        /* placeholder inicial (antes de clicar em parte) */
        .carr-placeholder {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 12px; color: #444; font-size: .95rem; text-align: center;
            padding: 40px 20px; min-height: 200px;
        }
        .carr-placeholder i { font-size: 2.8rem; color: #2a6648; opacity: .5; }
        .carr-placeholder p { margin: 0; }

        .carr-stage {
            position: relative;
            height: 360px;
            display: flex; align-items: center; justify-content: center;
            background: #111;
        }

        /* imagem única */
        #carr-img {
            max-width: 100%; max-height: 100%;
            object-fit: contain; display: block;
            transition: opacity .2s;
        }
        #carr-img.trocando { opacity: 0; }

        .carr-vazio {
            display: none;
            flex-direction: column; align-items: center; justify-content: center;
            gap: 12px; color: #555; font-size: .95rem; text-align: center;
            padding: 40px 20px;
        }
        .carr-vazio i { font-size: 2.5rem; color: #333; }

        .carr-nav {
            position: absolute; top: 50%; transform: translateY(-50%);
            background: rgba(255,255,255,.12); border: none; color: white;
            width: 44px; height: 44px; border-radius: 50%;
            font-size: 1.1rem; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background .2s; z-index: 2;
        }
        .carr-nav:hover { background: rgba(255,255,255,.28); }
        .carr-nav.prev { left: 14px; }
        .carr-nav.next { right: 14px; }
        .carr-nav:disabled { opacity: .25; cursor: default; }
        .carr-nav[style*="none"] { display: none !important; }

        .carr-rodape {
            padding: 12px 20px 14px;
            display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        }
        .carr-nome {
            flex: 1;
            font-style: italic; font-weight: 600; font-size: 1rem;
            color: white; min-width: 0;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .carr-link {
            font-size: .78rem; color: rgba(255,255,255,.6);
            text-decoration: none; padding: 4px 10px; border-radius: 20px;
            border: 1px solid rgba(255,255,255,.2);
            transition: background .2s, color .2s; white-space: nowrap; flex-shrink: 0;
        }
        .carr-link:hover { background: rgba(255,255,255,.15); color: white; text-decoration: none; }
        .carr-contador {
            font-size: .78rem; color: rgba(255,255,255,.45); flex-shrink: 0;
        }

        /* ── Botões de parte ── */
        .partes {
            display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px;
        }
        .parte-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 18px; border-radius: 30px; border: 2px solid #ccc;
            background: white; color: #555; font-size: .88rem; font-weight: 600;
            cursor: pointer; transition: all .18s;
        }
        .parte-btn:hover { border-color: var(--cor-primaria); color: var(--cor-primaria); }
        .parte-btn.ativo {
            background: var(--cor-primaria); border-color: var(--cor-primaria);
            color: white; box-shadow: 0 3px 10px rgba(11,94,66,.3);
        }

        /* ── Lista ── */
        .lista-titulo {
            font-size: .72rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .1em; color: #999; margin-bottom: 10px;
        }
        .lista { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 2px; }

        .item {
            background: white; border-radius: 8px;
            display: flex; align-items: center; gap: 14px;
            padding: 13px 18px; text-decoration: none; color: inherit;
            transition: box-shadow .15s, transform .15s;
            box-shadow: 0 1px 3px rgba(0,0,0,.07);
        }
        .item:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,.13);
            transform: translateY(-1px); text-decoration: none; color: inherit;
        }
        .item-num { font-size: .74rem; color: #bbb; min-width: 26px; text-align: right; flex-shrink: 0; }
        .item-info { flex: 1; min-width: 0; }
        .item-cientifico {
            font-style: italic; font-size: .97rem; font-weight: 600;
            color: var(--cor-primaria);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .item-popular {
            font-size: .8rem; color: #777; margin-top: 2px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .item-familia {
            font-size: .72rem; color: #aaa; text-transform: uppercase;
            letter-spacing: .05em; flex-shrink: 0;
        }
        .item-seta { color: #ccc; font-size: .78rem; flex-shrink: 0; }
        .item:hover .item-seta { color: var(--cor-primaria); }

        .sem-resultado {
            text-align: center; padding: 60px 20px; background: white;
            border-radius: 12px; color: #888;
        }
        .sem-resultado i { font-size: 2.5rem; color: #ddd; margin-bottom: 16px; display: block; }

        @media (max-width: 560px) {
            .carr-stage { height: 220px; }
            .item-familia { display: none; }
            .topo { padding: 10px 12px; gap: 8px; }
            .topo-titulo { font-size: .85rem; }
            .wrapper { padding: 14px 10px 60px; }
            .partes { gap: 6px; }
            .parte-btn { padding: 7px 13px; font-size: .82rem; }
            .carr-rodape { padding: 10px 14px 12px; gap: 8px; }
        }
        @media (max-width: 400px) {
            .topo-titulo { display: none; }
            .carr-stage { height: 190px; }
            .parte-btn { padding: 6px 11px; font-size: .78rem; }
        }
    </style>
</head>
<body>
<div class="topo">
    <a href="<?= APP_BASE ?>/src/Views/publico/busca_caracteristicas.php" class="btn-voltar">
        <i class="fa-solid fa-arrow-left"></i> Nova Busca
    </a>
    <span class="topo-titulo"><i class="fa-solid fa-leaf"></i> Resultados da Busca</span>
    <?php if ($total > 0): ?>
    <span class="topo-total"><?= $total ?> espécie<?= $total !== 1 ? 's' : '' ?></span>
    <?php endif; ?>
</div>

<div class="wrapper">

<?php if ($total === 0): ?>
    <div class="sem-resultado">
        <i class="fa-solid fa-magnifying-glass"></i>
        <p>Nenhuma espécie encontrada com esses filtros.</p>
        <a href="<?= APP_BASE ?>/src/Views/publico/busca_caracteristicas.php" class="btn-voltar" style="display:inline-flex;margin-top:12px;background:var(--cor-primaria);">
            Tentar novamente
        </a>
    </div>
<?php else: ?>

    <!-- ── Botões de parte ───────────────────────── -->
    <div class="partes">
        <button class="parte-btn" data-parte="folha"   onclick="setParte(this)">🍃 Folha</button>
        <button class="parte-btn" data-parte="flor"    onclick="setParte(this)">🌸 Flor</button>
        <button class="parte-btn" data-parte="fruto"   onclick="setParte(this)">🍎 Fruto</button>
        <button class="parte-btn" data-parte="caule"   onclick="setParte(this)">🌿 Caule</button>
        <button class="parte-btn" data-parte="semente" onclick="setParte(this)">🌱 Semente</button>
    </div>

    <!-- ── Carrossel ─────────────────────────────── -->
    <div class="carrossel" id="carrossel">
        <div class="carr-stage" id="carr-stage">
            <!-- placeholder inicial -->
            <div class="carr-placeholder" id="carr-placeholder">
                <i class="fa-solid fa-seedling"></i>
                <p>Selecione uma parte da planta acima<br>para visualizar as imagens</p>
            </div>

            <button class="carr-nav prev" id="btn-prev" onclick="navImg(-1)" style="display:none">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <img id="carr-img" src="" alt="" style="display:none">
            <div class="carr-vazio" id="carr-vazio"></div>
            <button class="carr-nav next" id="btn-next" onclick="navImg(1)" style="display:none">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>

        <div class="carr-rodape" id="carr-rodape" style="display:none">
            <span class="carr-nome" id="carr-nome"></span>
            <a class="carr-link" id="carr-link" href="#">Ver ficha completa →</a>
            <span class="carr-contador" id="carr-contador"></span>
        </div>
    </div>

    <!-- ── Lista de espécies ─────────────────────── -->
    <p class="lista-titulo">Todas as espécies encontradas</p>
    <ul class="lista">
        <?php foreach ($especies as $i => $esp): ?>
        <li>
            <a class="item" href="<?= APP_BASE ?>/src/Views/publico/especie_detalhes.php?id=<?= (int)$esp['id'] ?>">
                <span class="item-num"><?= $i + 1 ?></span>
                <span class="item-info">
                    <div class="item-cientifico"><?= htmlspecialchars($esp['nome_cientifico_completo'] ?: '—') ?></div>
                    <?php if (!empty($esp['nome_popular'])): ?>
                    <div class="item-popular"><?= htmlspecialchars($esp['nome_popular']) ?></div>
                    <?php endif; ?>
                </span>
                <?php if (!empty($esp['familia'])): ?>
                <span class="item-familia"><?= htmlspecialchars($esp['familia']) ?></span>
                <?php endif; ?>
                <i class="fa-solid fa-chevron-right item-seta"></i>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>

<?php endif; ?>
</div>

<script>
const SLIDES   = <?= $j_slides ?>;
const BASE     = '<?= APP_BASE ?>';
let parteAtual = null;
let idxEsp     = 0;   // espécie atual
let idxImg     = 0;   // imagem dentro da espécie atual

// Auto-seleciona a primeira parte que tem imagens ao carregar
document.addEventListener('DOMContentLoaded', function() {
    const ordem = ['folha', 'flor', 'fruto', 'caule', 'semente'];
    for (const p of ordem) {
        if (SLIDES[p] && SLIDES[p].length > 0) {
            const btn = document.querySelector('[data-parte="' + p + '"]');
            if (btn) { setParte(btn); break; }
        }
    }
});

// Total de imagens de todas as espécies para a parte
function totalImgs(lista) {
    return lista.reduce((s, e) => s + e.imgs.length, 0);
}

// Posição global (1-based) da imagem atual
function posGlobal(lista) {
    let pos = 0;
    for (let i = 0; i < idxEsp; i++) pos += lista[i].imgs.length;
    return pos + idxImg + 1;
}

function setParte(btn) {
    document.querySelectorAll('.parte-btn').forEach(b => b.classList.remove('ativo'));
    btn.classList.add('ativo');
    parteAtual = btn.dataset.parte;
    idxEsp = 0;
    idxImg = 0;
    renderCarr();
}

function navImg(dir) {
    const lista = SLIDES[parteAtual] || [];
    if (!lista.length) return;

    idxImg += dir;

    // Passou do fim das imagens da espécie → próxima espécie
    if (idxImg >= lista[idxEsp].imgs.length) {
        idxEsp = (idxEsp + 1) % lista.length;
        idxImg = 0;
    }
    // Voltou antes do início → espécie anterior, última imagem
    else if (idxImg < 0) {
        idxEsp = (idxEsp - 1 + lista.length) % lista.length;
        idxImg = lista[idxEsp].imgs.length - 1;
    }

    renderCarr();
}

// ── Touch swipe no carrossel ─────────────────────────────────
let _txStart = 0;
const carrStage = document.getElementById('carr-stage');
if (carrStage) {
    carrStage.addEventListener('touchstart', function(e) {
        _txStart = e.touches[0].clientX;
    }, { passive: true });
    carrStage.addEventListener('touchend', function(e) {
        const diff = _txStart - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 40) navImg(diff > 0 ? 1 : -1);
    }, { passive: true });
}

function renderCarr() {
    const placeholder = document.getElementById('carr-placeholder');
    const carrImg     = document.getElementById('carr-img');
    const vazio       = document.getElementById('carr-vazio');
    const rodape      = document.getElementById('carr-rodape');
    const nome        = document.getElementById('carr-nome');
    const contador    = document.getElementById('carr-contador');
    const link        = document.getElementById('carr-link');
    const btnPrev     = document.getElementById('btn-prev');
    const btnNext     = document.getElementById('btn-next');

    if (!parteAtual) {
        placeholder.style.display = 'flex';
        carrImg.style.display     = 'none';
        vazio.style.display       = 'none';
        rodape.style.display      = 'none';
        btnPrev.style.display     = 'none';
        btnNext.style.display     = 'none';
        return;
    }

    const lista = SLIDES[parteAtual] || [];
    placeholder.style.display = 'none';

    if (!lista.length) {
        carrImg.style.display = 'none';
        vazio.style.display   = 'flex';
        vazio.innerHTML       = '<i class="fa-regular fa-image" style="font-size:2rem;color:#333;margin-bottom:8px"></i>Nenhuma imagem disponível para esta parte';
        rodape.style.display  = 'none';
        btnPrev.style.display = 'none';
        btnNext.style.display = 'none';
        return;
    }

    const esp  = lista[idxEsp];
    const url  = esp.imgs[idxImg];
    const tot  = totalImgs(lista);

    // Troca suave
    carrImg.classList.add('trocando');
    setTimeout(() => {
        carrImg.src = url;
        carrImg.alt = esp.nome;
        carrImg.classList.remove('trocando');
    }, 120);

    carrImg.style.display = 'block';
    vazio.style.display   = 'none';
    rodape.style.display  = 'flex';
    btnPrev.style.display = tot > 1 ? 'flex' : 'none';
    btnNext.style.display = tot > 1 ? 'flex' : 'none';

    nome.textContent     = esp.nome;
    contador.textContent = posGlobal(lista) + ' / ' + tot;
    link.href            = BASE + '/src/Views/publico/especie_detalhes.php?id=' + esp.id;
}
</script>
</body>
</html>
