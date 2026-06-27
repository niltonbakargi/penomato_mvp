<?php
// ================================================
// RESULTADOS DA BUSCA — lista de espécies
// ================================================

session_start();
require_once __DIR__ . '/../../../config/banco_de_dados.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_BASE . '/src/Views/publico/busca_caracteristicas.php');
    exit;
}

// ================================================
// MONTAR WHERE (mesma lógica de especie_detalhes)
// ================================================

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
        body { background: #f5f0ea; padding: 0; margin: 0; }

        .topo {
            background: var(--cor-primaria);
            padding: 16px 28px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .topo-titulo {
            color: white;
            font-size: 1rem;
            font-weight: 700;
            flex: 1;
        }
        .topo-total {
            background: rgba(255,255,255,.18);
            color: white;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: .82rem;
            font-weight: 600;
        }
        .btn-voltar {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,.15); color: white;
            text-decoration: none; padding: 7px 16px;
            border-radius: 30px; font-size: .875rem; font-weight: 600;
            transition: background .2s;
        }
        .btn-voltar:hover { background: rgba(255,255,255,.28); color: white; text-decoration: none; }

        .wrapper { max-width: 860px; margin: 32px auto; padding: 0 20px 80px; }

        .filtro-ativo {
            background: #fffbe6;
            border: 1px solid #f0d070;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: .82rem;
            color: #7a5f00;
            margin-bottom: 20px;
        }

        .lista { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 2px; }

        .item {
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 20px;
            text-decoration: none;
            color: inherit;
            transition: box-shadow .15s, transform .15s;
            box-shadow: 0 1px 3px rgba(0,0,0,.07);
        }
        .item:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,.13);
            transform: translateY(-1px);
            text-decoration: none;
            color: inherit;
        }
        .item-num {
            font-size: .75rem;
            color: #aaa;
            min-width: 28px;
            text-align: right;
            flex-shrink: 0;
        }
        .item-info { flex: 1; min-width: 0; }
        .item-cientifico {
            font-style: italic;
            font-size: 1rem;
            font-weight: 600;
            color: var(--cor-primaria);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .item-popular {
            font-size: .82rem;
            color: #666;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .item-familia {
            font-size: .75rem;
            color: #999;
            text-transform: uppercase;
            letter-spacing: .06em;
            flex-shrink: 0;
        }
        .item-seta {
            color: #ccc;
            font-size: .8rem;
            flex-shrink: 0;
        }
        .item:hover .item-seta { color: var(--cor-primaria); }

        .sem-resultado {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            color: #888;
        }
        .sem-resultado i { font-size: 2.5rem; color: #ddd; margin-bottom: 16px; display: block; }

        @media (max-width: 560px) {
            .item-familia { display: none; }
            .topo { padding: 12px 16px; }
            .wrapper { margin-top: 20px; padding: 0 12px 60px; }
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
    <span class="topo-total"><?= $total ?> espécie<?= $total !== 1 ? 's' : '' ?> encontrada<?= $total !== 1 ? 's' : '' ?></span>
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
</body>
</html>
