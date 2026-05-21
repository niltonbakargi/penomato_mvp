<?php
// ================================================
// GESTÃO DE ARTIGOS — VISÃO DO GESTOR
// Todos os artigos, agrupados por orientador
// ================================================
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'gestor') {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

$gestor_id = (int)$_SESSION['usuario_id'];

// ================================================
// POST: atribuir orientador
// ================================================
$msg = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'atribuir') {
    $especie_id   = (int)($_POST['especie_id']   ?? 0);
    $orientador_id = (int)($_POST['orientador_id'] ?? 0);

    if ($especie_id && $orientador_id) {
        $stmt = $pdo->prepare("UPDATE artigos SET revisor_id = ? WHERE especie_id = ?");
        $stmt->execute([$orientador_id, $especie_id]);
        $msg = ['tipo' => 'ok', 'texto' => 'Orientador atribuído com sucesso.'];
    } else {
        $msg = ['tipo' => 'err', 'texto' => 'Selecione um orientador válido.'];
    }
}

// ================================================
// FILTROS
// ================================================
$filtro_orientador = isset($_GET['orientador']) ? (int)$_GET['orientador'] : -1; // -1 = todos
$filtro_status     = $_GET['status'] ?? '';
$status_validos    = ['rascunho', 'confirmado', 'registrado', 'revisando', 'revisado', 'publicado'];

// ================================================
// RESUMO GERAL
// ================================================
try {
    $resumo = $pdo->query("
        SELECT
            COUNT(*)                                              AS total,
            SUM(revisor_id IS NULL)                              AS sem_orientador,
            SUM(status IN ('registrado','revisando','revisado')) AS pendentes,
            SUM(status = 'publicado')                            AS publicados
        FROM artigos
    ")->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $resumo = ['total' => 0, 'sem_orientador' => 0, 'pendentes' => 0, 'publicados' => 0];
}

// ================================================
// LISTA DE ESPECIALISTAS (para filtro e atribuição)
// ================================================
try {
    $especialistas = $pdo->query("
        SELECT id, nome
        FROM usuarios
        WHERE ativo = 1
          AND status_verificacao = 'verificado'
          AND tipo IN ('revisor','gestor')
          OR (tipo = 'colaborador' AND subtipo IN ('especialista','gestor'))
        ORDER BY nome
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // subtipo pode ser coluna diferente — tenta query mais simples
    try {
        $especialistas = $pdo->query("
            SELECT id, nome FROM usuarios
            WHERE ativo = 1 AND status_verificacao = 'verificado'
            ORDER BY nome
        ")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e2) {
        $especialistas = [];
    }
}

// ================================================
// BUSCAR ARTIGOS
// ================================================
try {
    $where_parts = [];
    $params      = [];

    if ($filtro_orientador === 0) {
        $where_parts[] = 'a.revisor_id IS NULL';
    } elseif ($filtro_orientador > 0) {
        $where_parts[] = 'a.revisor_id = ?';
        $params[]      = $filtro_orientador;
    }

    if ($filtro_status && in_array($filtro_status, $status_validos)) {
        $where_parts[] = 'a.status = ?';
        $params[]      = $filtro_status;
    }

    $where = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';

    $stmt = $pdo->prepare("
        SELECT
            a.especie_id,
            a.status,
            a.gerado_em,
            a.atualizado_em,
            e.nome_cientifico,
            e.prioridade,
            c.nome_popular,
            c.familia,
            r.id   AS orientador_id,
            r.nome AS orientador_nome,
            u.nome AS revisado_por_nome
        FROM artigos a
        INNER JOIN especies_administrativo e ON a.especie_id = e.id
        LEFT JOIN  especies_caracteristicas c ON c.especie_id = e.id
        LEFT JOIN  usuarios r ON r.id = a.revisor_id
        LEFT JOIN  usuarios u ON u.id = a.revisado_por
        {$where}
        GROUP BY a.especie_id
        ORDER BY
            (a.revisor_id IS NULL) DESC,
            CASE a.status
                WHEN 'registrado' THEN 1
                WHEN 'revisando'  THEN 2
                WHEN 'revisado'   THEN 3
                WHEN 'confirmado' THEN 4
                WHEN 'rascunho'   THEN 5
                WHEN 'publicado'  THEN 6
            END,
            a.gerado_em DESC
    ");
    $stmt->execute($params);
    $artigos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $artigos = [];
}

// ================================================
// LABELS
// ================================================
$info_status = [
    'rascunho'   => ['label' => 'Rascunho',    'classe' => 'st-rascunho'],
    'confirmado' => ['label' => 'Confirmado',  'classe' => 'st-confirmado'],
    'registrado' => ['label' => 'Registrado',  'classe' => 'st-registrado'],
    'revisando'  => ['label' => 'Em revisão',  'classe' => 'st-revisando'],
    'revisado'   => ['label' => 'Revisado',    'classe' => 'st-revisado'],
    'publicado'  => ['label' => 'Publicado',   'classe' => 'st-publicado'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Artigos · Penomato</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha384-blOohCVdhjmtROpu8+CfTnUWham9nkX7P7OZQMst+RUnhtoY/9qemFAkIKOYxDI3" crossorigin="anonymous">
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
    <style>
        body { background: var(--cinza-100); padding: var(--esp-8) var(--esp-5); }
        .container { max-width: 1100px; margin: 0 auto; }

        /* ── Header ── */
        .page-header {
            background: var(--cor-primaria); color: var(--branco);
            padding: var(--esp-6) var(--esp-8); border-radius: var(--raio-lg);
            margin-bottom: var(--esp-6);
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: var(--esp-3);
        }
        .page-header h1 { font-size: var(--texto-xl); font-weight: var(--peso-semi); color: var(--branco); }
        .page-header p  { font-size: var(--texto-sm); opacity: 0.8; margin-top: var(--esp-1); }

        /* ── Cards de resumo ── */
        .resumo-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: var(--esp-4);
            margin-bottom: var(--esp-6);
        }
        .resumo-card {
            background: var(--branco); border-radius: var(--raio-lg);
            padding: var(--esp-5) var(--esp-6); box-shadow: var(--sombra-sm);
            border-left: 4px solid var(--cinza-300);
            text-decoration: none; display: block; transition: var(--transicao);
        }
        .resumo-card:hover { box-shadow: var(--sombra-md); transform: translateY(-1px); text-decoration: none; }
        .resumo-card.alerta { border-left-color: #f59e0b; }
        .resumo-card.pendente { border-left-color: var(--cor-primaria); }
        .resumo-card.publicado { border-left-color: #1e40af; }
        .resumo-card .num { font-size: 2rem; font-weight: var(--peso-bold); color: var(--cinza-900); line-height: 1; }
        .resumo-card .label { font-size: var(--texto-xs); color: var(--cinza-500); margin-top: var(--esp-1); text-transform: uppercase; letter-spacing: 0.05em; }
        .resumo-card.alerta .num  { color: #92400e; }
        .resumo-card.pendente .num { color: var(--cor-primaria); }
        .resumo-card.publicado .num { color: #1e40af; }

        /* ── Filtros ── */
        .filtros {
            background: var(--branco); border-radius: var(--raio-lg);
            padding: var(--esp-4) var(--esp-6); box-shadow: var(--sombra-sm);
            margin-bottom: var(--esp-5);
            display: flex; gap: var(--esp-4); flex-wrap: wrap; align-items: flex-end;
        }
        .filtros label { font-size: var(--texto-xs); font-weight: var(--peso-semi); color: var(--cinza-600); display: block; margin-bottom: var(--esp-1); }
        .filtros select {
            padding: var(--esp-2) var(--esp-4); border-radius: var(--raio-md);
            border: 1px solid var(--cinza-300); font-size: var(--texto-sm);
            color: var(--cinza-800); background: var(--branco); cursor: pointer;
            min-width: 180px;
        }
        .filtros select:focus { outline: none; border-color: var(--cor-primaria); }
        .btn-filtrar {
            padding: var(--esp-2) var(--esp-5); background: var(--cor-primaria);
            color: var(--branco); border: none; border-radius: var(--raio-md);
            font-size: var(--texto-sm); font-weight: var(--peso-semi);
            cursor: pointer; transition: var(--transicao);
        }
        .btn-filtrar:hover { background: var(--cor-primaria-hover); }
        .btn-limpar {
            padding: var(--esp-2) var(--esp-4); background: none;
            color: var(--cinza-500); border: 1px solid var(--cinza-300);
            border-radius: var(--raio-md); font-size: var(--texto-sm);
            cursor: pointer; text-decoration: none; transition: var(--transicao);
        }
        .btn-limpar:hover { background: var(--cinza-100); color: var(--cinza-700); text-decoration: none; }

        /* ── Mensagem ── */
        .msg-ok  { background: var(--sucesso-fundo); color: var(--sucesso-texto); border: 1px solid #c3e6cb; border-radius: var(--raio-md); padding: var(--esp-3) var(--esp-5); margin-bottom: var(--esp-4); font-size: var(--texto-sm); }
        .msg-err { background: var(--perigo-fundo);  color: var(--perigo-texto);  border: 1px solid #f5c6cb; border-radius: var(--raio-md); padding: var(--esp-3) var(--esp-5); margin-bottom: var(--esp-4); font-size: var(--texto-sm); }

        /* ── Tabela ── */
        .card { background: var(--branco); border-radius: var(--raio-lg); box-shadow: var(--sombra-md); overflow: hidden; }
        .card-header {
            padding: var(--esp-4) var(--esp-6); border-bottom: 1px solid var(--cinza-100);
            display: flex; align-items: center; justify-content: space-between;
        }
        .card-header span { font-size: var(--texto-sm); color: var(--cinza-500); }

        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: var(--cinza-50); padding: var(--esp-3) var(--esp-5);
            text-align: left; font-size: var(--texto-xs); font-weight: var(--peso-bold);
            text-transform: uppercase; letter-spacing: 0.05em;
            color: var(--cinza-500); border-bottom: 1px solid var(--cinza-200);
        }
        tbody tr { border-bottom: 1px solid var(--cinza-100); transition: background var(--transicao); }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--cinza-50); }
        tbody tr.sem-orientador { background: #fffbeb; }
        tbody tr.sem-orientador:hover { background: #fef3c7; }
        td { padding: var(--esp-4) var(--esp-5); vertical-align: middle; }

        .especie-nome { font-weight: var(--peso-semi); color: var(--cinza-900); font-style: italic; font-size: var(--texto-sm); }
        .especie-sub  { font-size: var(--texto-xs); color: var(--cinza-500); margin-top: 2px; }

        /* orientador */
        .orientador-nome { font-size: var(--texto-sm); font-weight: var(--peso-semi); color: var(--cinza-800); }
        .orientador-vazio {
            display: inline-flex; align-items: center; gap: var(--esp-1);
            font-size: var(--texto-xs); color: #92400e;
            background: #fef3c7; border: 1px solid #fcd34d;
            border-radius: var(--raio-pill); padding: 2px var(--esp-3);
        }

        /* badges */
        .badge {
            display: inline-block; padding: 2px var(--esp-3);
            border-radius: var(--raio-pill); font-size: var(--texto-xs);
            font-weight: var(--peso-semi); white-space: nowrap;
        }
        .st-rascunho   { background: var(--cinza-200);     color: var(--cinza-700); }
        .st-confirmado { background: #e0f2fe;              color: #0369a1; }
        .st-registrado { background: #fef9c3;              color: #854d0e; }
        .st-revisando  { background: #ffedd5;              color: #9a3412; }
        .st-revisado   { background: var(--sucesso-fundo); color: var(--sucesso-texto); }
        .st-publicado  { background: #dbeafe;              color: #1e40af; }

        /* botões */
        .btns-acao { display: flex; gap: var(--esp-2); flex-wrap: nowrap; }
        .btn-acao {
            padding: var(--esp-2) var(--esp-3); border-radius: var(--raio-md);
            font-size: var(--texto-xs); font-weight: var(--peso-semi);
            text-decoration: none; white-space: nowrap;
            transition: var(--transicao); display: inline-block;
            border: none; cursor: pointer; font-family: inherit;
        }
        .btn-ver      { background: var(--cinza-100); color: var(--cinza-700); border: 1px solid var(--cinza-300); }
        .btn-ver:hover { background: var(--cinza-200); color: var(--cinza-800); text-decoration: none; }
        .btn-atribuir { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .btn-atribuir:hover { background: #fde68a; color: #78350f; text-decoration: none; }
        .btn-publicar { background: #1e40af; color: var(--branco); }
        .btn-publicar:hover { background: #1e3a8a; color: var(--branco); text-decoration: none; }

        .data-cell { font-size: var(--texto-xs); color: var(--cinza-500); white-space: nowrap; }

        .vazia { text-align: center; padding: var(--esp-16) var(--esp-5); color: var(--cinza-400); }
        .vazia i { font-size: 2.5rem; display: block; margin-bottom: var(--esp-3); }

        /* ── Modal atribuir ── */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.45); z-index: 1000;
            align-items: center; justify-content: center; padding: 20px;
        }
        .modal-overlay.ativo { display: flex; }
        .modal {
            background: var(--branco); border-radius: var(--raio-lg);
            padding: var(--esp-8); width: 100%; max-width: 420px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .modal h3 { color: var(--cor-primaria); margin-bottom: var(--esp-5); font-size: var(--texto-lg); }
        .modal label { display: block; font-size: var(--texto-xs); font-weight: var(--peso-semi); color: var(--cinza-600); margin-bottom: var(--esp-1); }
        .modal select {
            width: 100%; padding: var(--esp-3) var(--esp-4);
            border: 1px solid var(--cinza-300); border-radius: var(--raio-md);
            font-size: var(--texto-sm); margin-bottom: var(--esp-5); font-family: inherit;
        }
        .modal select:focus { outline: none; border-color: var(--cor-primaria); }
        .modal-footer { display: flex; gap: var(--esp-3); justify-content: flex-end; }
        .btn-confirm {
            background: var(--cor-primaria); color: var(--branco);
            border: none; border-radius: var(--raio-md);
            padding: var(--esp-3) var(--esp-6); font-weight: var(--peso-semi);
            cursor: pointer; font-size: var(--texto-sm); font-family: inherit;
        }
        .btn-confirm:hover { background: var(--cor-primaria-hover); }
        .btn-cancel {
            background: none; color: var(--cinza-500); border: 1px solid var(--cinza-300);
            border-radius: var(--raio-md); padding: var(--esp-3) var(--esp-5);
            cursor: pointer; font-size: var(--texto-sm); font-family: inherit;
        }
        .btn-cancel:hover { background: var(--cinza-100); }
        .modal-especie-info {
            background: var(--cinza-50); border-radius: var(--raio-md);
            padding: var(--esp-3) var(--esp-4); margin-bottom: var(--esp-5);
            font-size: var(--texto-sm); color: var(--cinza-700); font-style: italic;
        }

        @media (max-width: 768px) {
            .resumo-grid { grid-template-columns: repeat(2, 1fr); }
            .col-md { display: none; }
        }
        @media (max-width: 480px) {
            .resumo-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="page-header">
        <div>
            <h1><i class="fas fa-file-alt"></i> Gestão de Artigos</h1>
            <p>Todos os artigos do sistema — orientadores e status</p>
        </div>
        <div style="display:flex;gap:var(--esp-3);flex-wrap:wrap;align-items:center;">
            <a href="/penomato_mvp/src/Controllers/admin_regenerar_artigos.php"
               class="btn btn-outline-branco"
               onclick="return confirm('Regenerar o HTML de todos os artigos com os dados atuais do banco?')"
               title="Regera o texto de todos os artigos com os dados mais recentes das espécies">
                <i class="fas fa-rotate"></i> Regenerar artigos
            </a>
            <a href="/penomato_mvp/src/Controllers/controlador_gestor.php" class="btn btn-outline-branco">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <?php if ($msg): ?>
    <div class="msg-<?php echo $msg['tipo']; ?>">
        <i class="fas fa-<?php echo $msg['tipo'] === 'ok' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <?php echo htmlspecialchars($msg['texto']); ?>
    </div>
    <?php endif; ?>

    <!-- Resumo -->
    <div class="resumo-grid">
        <a href="?" class="resumo-card">
            <div class="num"><?php echo (int)$resumo['total']; ?></div>
            <div class="label">Total de artigos</div>
        </a>
        <a href="?orientador=0" class="resumo-card alerta">
            <div class="num"><?php echo (int)$resumo['sem_orientador']; ?></div>
            <div class="label">Sem orientador</div>
        </a>
        <a href="?status=registrado" class="resumo-card pendente">
            <div class="num"><?php echo (int)$resumo['pendentes']; ?></div>
            <div class="label">Pendentes</div>
        </a>
        <a href="?status=publicado" class="resumo-card publicado">
            <div class="num"><?php echo (int)$resumo['publicados']; ?></div>
            <div class="label">Publicados</div>
        </a>
    </div>

    <!-- Filtros -->
    <form method="GET" class="filtros">
        <div>
            <label>Orientador</label>
            <select name="orientador">
                <option value="-1" <?php echo $filtro_orientador === -1 ? 'selected' : ''; ?>>Todos</option>
                <option value="0"  <?php echo $filtro_orientador === 0  ? 'selected' : ''; ?>>— Sem orientador —</option>
                <?php foreach ($especialistas as $esp): ?>
                <option value="<?php echo $esp['id']; ?>"
                    <?php echo $filtro_orientador === (int)$esp['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($esp['nome']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Status</label>
            <select name="status">
                <option value="">Todos</option>
                <option value="rascunho"   <?php echo $filtro_status === 'rascunho'   ? 'selected' : ''; ?>>Rascunho</option>
                <option value="confirmado" <?php echo $filtro_status === 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                <option value="registrado" <?php echo $filtro_status === 'registrado' ? 'selected' : ''; ?>>Registrado</option>
                <option value="revisando"  <?php echo $filtro_status === 'revisando'  ? 'selected' : ''; ?>>Em revisão</option>
                <option value="revisado"   <?php echo $filtro_status === 'revisado'   ? 'selected' : ''; ?>>Revisado</option>
                <option value="publicado"  <?php echo $filtro_status === 'publicado'  ? 'selected' : ''; ?>>Publicado</option>
            </select>
        </div>
        <button type="submit" class="btn-filtrar"><i class="fas fa-filter"></i> Filtrar</button>
        <a href="?" class="btn-limpar"><i class="fas fa-times"></i> Limpar</a>
    </form>

    <!-- Tabela -->
    <div class="card">
        <div class="card-header">
            <strong><?php echo count($artigos); ?> artigo(s)</strong>
            <?php if ($filtro_orientador === 0): ?>
                <span style="color:#92400e;"><i class="fas fa-exclamation-triangle"></i> Exibindo artigos sem orientador</span>
            <?php elseif ($filtro_orientador > 0): ?>
                <span>Filtrado por orientador</span>
            <?php endif; ?>
        </div>

        <?php if (empty($artigos)): ?>
        <div class="vazia">
            <i class="fas fa-check-circle" style="color:var(--sucesso-cor)"></i>
            Nenhum artigo encontrado com estes filtros.
        </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Espécie</th>
                    <th>Orientador</th>
                    <th>Status</th>
                    <th class="col-md">Atualizado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($artigos as $a):
                $st       = $info_status[$a['status']] ?? ['label' => $a['status'], 'classe' => ''];
                $sem_ori  = empty($a['orientador_id']);
            ?>
            <tr<?php echo $sem_ori ? ' class="sem-orientador"' : ''; ?>>
                <td>
                    <div class="especie-nome"><?php echo htmlspecialchars($a['nome_cientifico']); ?></div>
                    <?php if ($a['nome_popular']): ?>
                    <div class="especie-sub"><?php echo htmlspecialchars($a['nome_popular']); ?></div>
                    <?php endif; ?>
                    <?php if ($a['familia']): ?>
                    <div class="especie-sub"><?php echo htmlspecialchars($a['familia']); ?></div>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($sem_ori): ?>
                        <span class="orientador-vazio"><i class="fas fa-exclamation-triangle"></i> Sem orientador</span>
                    <?php else: ?>
                        <div class="orientador-nome"><?php echo htmlspecialchars($a['orientador_nome']); ?></div>
                        <?php if ($a['revisado_por_nome'] && $a['revisado_por_nome'] !== $a['orientador_nome']): ?>
                        <div class="especie-sub">Revisado por: <?php echo htmlspecialchars($a['revisado_por_nome']); ?></div>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge <?php echo $st['classe']; ?>"><?php echo $st['label']; ?></span>
                </td>
                <td class="col-md data-cell">
                    <?php echo $a['atualizado_em']
                        ? date('d/m/Y', strtotime($a['atualizado_em']))
                        : date('d/m/Y', strtotime($a['gerado_em'])); ?>
                </td>
                <td>
                    <div class="btns-acao">
                        <a href="/penomato_mvp/src/Views/artigo_revisao.php?id=<?php echo $a['especie_id']; ?>&modo=ver"
                           class="btn-acao btn-ver">
                            <i class="fas fa-eye"></i> Ver
                        </a>

                        <?php if ($sem_ori || true): // Gestor pode sempre reatribuir ?>
                        <button type="button"
                                class="btn-acao btn-atribuir"
                                onclick="abrirAtribuir(<?php echo $a['especie_id']; ?>, '<?php echo htmlspecialchars($a['nome_cientifico'], ENT_QUOTES); ?>')">
                            <i class="fas fa-user-tag"></i> <?php echo $sem_ori ? 'Atribuir' : 'Reatribuir'; ?>
                        </button>
                        <?php endif; ?>

                        <?php if ($a['status'] === 'revisado'): ?>
                        <form method="POST"
                              action="/penomato_mvp/src/Controllers/controlador_painel_revisor.php"
                              style="display:inline;"
                              onsubmit="return confirm('Publicar o artigo de <?php echo htmlspecialchars($a['nome_cientifico'], ENT_QUOTES); ?>?')">
                            <input type="hidden" name="acao"       value="publicar">
                            <input type="hidden" name="especie_id" value="<?php echo $a['especie_id']; ?>">
                            <button type="submit" class="btn-acao btn-publicar">
                                <i class="fas fa-globe"></i> Publicar
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>

<!-- Modal: Atribuir orientador -->
<div class="modal-overlay" id="modal-atribuir">
    <div class="modal">
        <h3><i class="fas fa-user-tag"></i> Atribuir Orientador</h3>
        <div class="modal-especie-info" id="modal-especie-nome"></div>
        <form method="POST">
            <input type="hidden" name="acao"       value="atribuir">
            <input type="hidden" name="especie_id" id="modal-especie-id">
            <label>Selecione o orientador</label>
            <select name="orientador_id" required>
                <option value="">— escolha —</option>
                <?php foreach ($especialistas as $esp): ?>
                <option value="<?php echo $esp['id']; ?>">
                    <?php echo htmlspecialchars($esp['nome']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="fecharAtribuir()">Cancelar</button>
                <button type="submit" class="btn-confirm">Confirmar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirAtribuir(especieId, especieNome) {
    document.getElementById('modal-especie-id').value   = especieId;
    document.getElementById('modal-especie-nome').textContent = especieNome;
    document.getElementById('modal-atribuir').classList.add('ativo');
}
function fecharAtribuir() {
    document.getElementById('modal-atribuir').classList.remove('ativo');
}
document.getElementById('modal-atribuir').addEventListener('click', function(e) {
    if (e.target === this) fecharAtribuir();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') fecharAtribuir();
});
</script>
</body>
</html>
