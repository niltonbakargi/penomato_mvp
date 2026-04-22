<?php
// ================================================
// ARTIGO CIENTÍFICO PÚBLICO
// Exibe o artigo publicado de uma espécie
// ================================================

session_start();
require_once __DIR__ . '/../../../config/banco_de_dados.php';

$especie_id = (int)($_GET['id'] ?? 0);
if (!$especie_id) {
    header('Location: ' . APP_BASE . '/src/Views/publico/busca_caracteristicas.php');
    exit;
}

try {
    // Artigo publicado
    $stmt = $pdo->prepare("
        SELECT a.texto_html, a.gerado_em, a.atualizado_em,
               e.nome_cientifico, e.prioridade,
               e.data_publicado, e.autor_publicado_id,
               up.nome AS nome_publicador, up.instituicao AS inst_publicador,
               c.familia, c.nome_popular, c.sinonimos,
               uc.nome AS nome_colaborador, uc.instituicao AS inst_colaborador
        FROM artigos a
        INNER JOIN especies_administrativo e ON e.id = a.especie_id
        LEFT JOIN especies_caracteristicas c ON c.especie_id = e.id
        LEFT JOIN usuarios up ON up.id = e.autor_publicado_id
        LEFT JOIN usuarios uc ON uc.id = e.autor_dados_internet_id
        WHERE a.especie_id = ? AND a.status = 'publicado'
        LIMIT 1
    ");
    $stmt->execute([$especie_id]);
    $artigo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$artigo) {
        header('Location: ' . APP_BASE . '/src/Views/publico/busca_caracteristicas.php');
        exit;
    }

} catch (Exception $e) {
    error_log('Erro ao carregar artigo público: ' . $e->getMessage());
    header('Location: ' . APP_BASE . '/src/Views/publico/busca_caracteristicas.php');
    exit;
}

$data_pub = $artigo['data_publicado']
    ? date('d/m/Y', strtotime($artigo['data_publicado']))
    : '—';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($artigo['nome_cientifico']) ?> — Penomato</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_BASE ?>/assets/css/estilo.css">
    <style>
        body { background: #f0f4f1; color: #1a2634; margin: 0; }

        /* ── HEADER ── */
        .header {
            height: 56px;
            background: var(--cor-primaria);
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            gap: 16px;
        }
        .header-logo {
            font-size: 1.1rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-voltar {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,.15);
            color: white;
            text-decoration: none;
            padding: 7px 16px;
            border-radius: 30px;
            font-size: .875rem;
            font-weight: 600;
            transition: background .2s;
        }
        .btn-voltar:hover { background: rgba(255,255,255,.28); }

        /* ── HERO ── */
        .hero {
            background: linear-gradient(135deg, var(--cor-primaria) 0%, #0d7a56 100%);
            color: white;
            padding: 48px 24px 36px;
            text-align: center;
        }
        .hero-label {
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            opacity: .75;
            margin-bottom: 12px;
        }
        .hero-nome {
            font-size: clamp(1.6rem, 4vw, 2.4rem);
            font-style: italic;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .hero-familia {
            font-size: .95rem;
            opacity: .85;
            margin-bottom: 20px;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,.2);
            border: 1px solid rgba(255,255,255,.35);
            border-radius: 30px;
            padding: 5px 16px;
            font-size: .8rem;
            font-weight: 600;
        }

        /* ── LAYOUT PRINCIPAL ── */
        .page-wrap {
            max-width: 860px;
            margin: 0 auto;
            padding: 32px 20px 60px;
        }

        /* ── CARD ARTIGO ── */
        .card-artigo {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
            padding: 40px 48px;
            margin-bottom: 24px;
        }

        /* imagens do artigo maiores na página pública */
        .card-artigo .art-figura img {
            width: 180px;
            height: 130px;
        }
        .card-artigo .art-figura figcaption { max-width: 180px; }

        /* ── CARD CRÉDITOS ── */
        .card-creditos {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
            padding: 24px 32px;
            margin-bottom: 24px;
        }
        .creditos-titulo {
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .creditos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        .credito-item {}
        .credito-papel {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #94a3b8;
            margin-bottom: 2px;
        }
        .credito-nome {
            font-size: .9rem;
            font-weight: 600;
            color: #1e293b;
        }
        .credito-inst {
            font-size: .8rem;
            color: #64748b;
        }

        /* ── AÇÕES ── */
        .acoes {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 8px;
        }
        .btn-acao {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 20px;
            border-radius: 30px;
            font-size: .875rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: .2s;
            text-decoration: none;
        }
        .btn-imprimir {
            background: var(--cor-primaria);
            color: white;
        }
        .btn-imprimir:hover { background: var(--cor-primaria-hover); }
        .btn-busca {
            background: #f1f5f9;
            color: #475569;
        }
        .btn-busca:hover { background: #e2e8f0; }

        /* ── DATA PUBLICAÇÃO ── */
        .pub-meta {
            font-size: .8rem;
            color: #94a3b8;
            text-align: center;
            margin-top: 8px;
        }

        @media print {
            .header, .acoes, .pub-meta, .card-creditos { display: none; }
            .card-artigo { box-shadow: none; padding: 0; }
            .page-wrap { padding: 0; }
            body { background: white; }
        }

        @media (max-width: 640px) {
            .card-artigo { padding: 24px 20px; }
            .card-creditos { padding: 20px; }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header class="header">
    <a href="<?= APP_BASE ?>/" class="header-logo">
        <i class="fas fa-leaf"></i> Penomato
    </a>
    <a href="javascript:history.back()" class="btn-voltar">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</header>

<!-- HERO -->
<div class="hero">
    <div class="hero-label">Artigo Científico Publicado</div>
    <div class="hero-nome"><?= htmlspecialchars($artigo['nome_cientifico']) ?></div>
    <?php if ($artigo['familia']): ?>
        <div class="hero-familia">Família <?= htmlspecialchars($artigo['familia']) ?></div>
    <?php endif; ?>
    <div class="hero-badge">
        <i class="fas fa-book-open"></i> Publicado em <?= $data_pub ?>
    </div>
</div>

<!-- CONTEÚDO -->
<div class="page-wrap">

    <!-- Ações -->
    <div class="acoes">
        <button class="btn-acao btn-imprimir" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir / PDF
        </button>
        <a href="<?= APP_BASE ?>/src/Views/publico/busca_caracteristicas.php" class="btn-acao btn-busca">
            <i class="fas fa-search"></i> Nova busca
        </a>
    </div>

    <!-- Artigo -->
    <div class="card-artigo">
        <?= $artigo['texto_html'] ?>
    </div>

    <!-- Créditos -->
    <div class="card-creditos">
        <div class="creditos-titulo"><i class="fas fa-users"></i> Créditos</div>
        <div class="creditos-grid">
            <?php if (!empty($artigo['nome_colaborador'])): ?>
            <div class="credito-item">
                <div class="credito-papel">Colaborador</div>
                <div class="credito-nome"><?= htmlspecialchars($artigo['nome_colaborador']) ?></div>
                <?php if (!empty($artigo['inst_colaborador'])): ?>
                <div class="credito-inst"><?= htmlspecialchars($artigo['inst_colaborador']) ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($artigo['nome_publicador'])): ?>
            <div class="credito-item">
                <div class="credito-papel">Especialista Revisor</div>
                <div class="credito-nome"><?= htmlspecialchars($artigo['nome_publicador']) ?></div>
                <?php if (!empty($artigo['inst_publicador'])): ?>
                <div class="credito-inst"><?= htmlspecialchars($artigo['inst_publicador']) ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="credito-item">
                <div class="credito-papel">Plataforma</div>
                <div class="credito-nome">Penomato MVP</div>
                <div class="credito-inst">UFMS / UEMS — Cerrado</div>
            </div>
        </div>
    </div>

    <div class="pub-meta">
        Publicado em <?= $data_pub ?> · Penomato — Plataforma de Documentação do Cerrado
    </div>

</div>
</body>
</html>
