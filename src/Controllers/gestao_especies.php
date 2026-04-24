<?php
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

$status_validos     = ['sem_dados', 'dados_internet', 'identificado', 'registrada', 'em_revisao', 'revisada', 'contestado', 'publicado'];
$prioridades_validas = ['baixa', 'media', 'alta', 'urgente'];

// ================================================
// PROCESSAR AÇÕES (POST)
// ================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao       = $_POST['acao'] ?? '';
    $especie_id = (int)($_POST['especie_id'] ?? 0);

    $gestor_id = (int)$_SESSION['usuario_id'];

    if ($especie_id) {
        if ($acao === 'prioridade') {
            $prioridade_raw = $_POST['prioridade'] ?? '';
            $prioridade = in_array($prioridade_raw, $prioridades_validas) ? $prioridade_raw : 'media';
            $stmt_old = $pdo->prepare("SELECT prioridade FROM especies_administrativo WHERE id = ?");
            $stmt_old->execute([$especie_id]);
            $prioridade_antiga = $stmt_old->fetchColumn();
            $stmt = $pdo->prepare("UPDATE especies_administrativo SET prioridade = ? WHERE id = ?");
            $stmt->execute([$prioridade, $especie_id]);
            $pdo->prepare("
                INSERT INTO historico_alteracoes
                    (especie_id, id_usuario, tabela_afetada, campo_alterado, valor_anterior, valor_novo, tipo_acao)
                VALUES (?, ?, 'especies_administrativo', 'prioridade', ?, ?, 'edicao')
            ")->execute([$especie_id, $gestor_id, $prioridade_antiga, $prioridade]);

        } elseif ($acao === 'atribuir') {
            $atribuido_a = $_POST['atribuido_a'] ? (int)$_POST['atribuido_a'] : null;
            $stmt_old = $pdo->prepare("SELECT atribuido_a FROM especies_administrativo WHERE id = ?");
            $stmt_old->execute([$especie_id]);
            $atribuido_antigo = $stmt_old->fetchColumn() ?: null;
            $stmt = $pdo->prepare("UPDATE especies_administrativo SET atribuido_a = ? WHERE id = ?");
            $stmt->execute([$atribuido_a, $especie_id]);
            $pdo->prepare("
                INSERT INTO historico_alteracoes
                    (especie_id, id_usuario, tabela_afetada, campo_alterado, valor_anterior, valor_novo, tipo_acao)
                VALUES (?, ?, 'especies_administrativo', 'atribuido_a', ?, ?, 'edicao')
            ")->execute([$especie_id, $gestor_id, $atribuido_antigo, $atribuido_a]);
        }
    }

    // Redireciona de volta mantendo os filtros (valor validado para evitar header injection)
    $filtro_raw = $_POST['filtro_status'] ?? '';
    $filtro = in_array($filtro_raw, $status_validos) ? $filtro_raw : '';
    header("Location: gestao_especies.php" . ($filtro ? "?status=" . urlencode($filtro) : ''));
    exit;
}

// ================================================
// FILTROS
// ================================================
$filtro_status_raw = $_GET['status'] ?? '';
$filtro_status = in_array($filtro_status_raw, $status_validos) ? $filtro_status_raw : '';

$where = $filtro_status ? "WHERE e.status = ?" : "";
$params = $filtro_status ? [$filtro_status] : [];

// ================================================
// BUSCAR ESPÉCIES
// ================================================
$sql = "
    SELECT e.id, e.nome_cientifico, e.status, e.prioridade, e.atribuido_a,
           e.data_criacao, e.data_ultima_atualizacao,
           u.nome AS nome_atribuido
    FROM especies_administrativo e
    LEFT JOIN usuarios u ON u.id = e.atribuido_a
    $where
    ORDER BY
        FIELD(e.prioridade, 'urgente','alta','media','baixa'),
        e.nome_cientifico
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$especies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contagens por status
$contagens = $pdo->query("
    SELECT status, COUNT(*) as total FROM especies_administrativo GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

$total_geral = array_sum($contagens);

// Colaboradores para atribuição
$colaboradores = $pdo->query("
    SELECT id, nome FROM usuarios WHERE ativo = 1 ORDER BY nome
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Espécies — Penomato</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f4f0;
            color: #1e2e1e;
            padding: 24px 20px;
        }
        .container { max-width: 1300px; margin: 0 auto; }

        .header {
            background: var(--cor-primaria);
            color: white;
            padding: 18px 28px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header h1 { font-size: 1.3em; font-weight: 600; }
        .btn-voltar {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 8px 18px;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.88em;
        }
        .btn-voltar:hover { background: rgba(255,255,255,0.35); }

        /* Stats */
        .stats-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .stat-chip {
            background: white;
            border-radius: 20px;
            padding: 8px 18px;
            font-size: 0.85em;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            cursor: pointer;
            border: 2px solid transparent;
            text-decoration: none;
            color: #333;
            transition: all 0.15s;
        }
        .stat-chip:hover { border-color: var(--cor-primaria); }
        .stat-chip.ativo { border-color: var(--cor-primaria); background: #e8f5e9; color: var(--cor-primaria); font-weight: 600; }
        .stat-chip .num { font-weight: 700; color: var(--cor-primaria); }

        /* Tabela */
        .table-wrap {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        table { width: 100%; border-collapse: collapse; font-size: 0.88em; }
        th {
            background: #f7f9f7;
            padding: 12px 14px;
            text-align: left;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #e0e8e0;
            white-space: nowrap;
        }
        td { padding: 11px 14px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fafffe; }

        .nome-cientifico { font-style: italic; font-weight: 500; color: #1a3a28; }

        /* Status badge */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 0.8em;
            font-weight: 600;
            white-space: nowrap;
        }
        .badge-sem_dados    { background:#f0f0f0; color:#666; }
        .badge-dados_internet { background:#cfe2ff; color:#084298; }
        .badge-identificado { background:#d1ecf1; color:#0c5460; }
        .badge-registrada   { background:#d4edda; color:#155724; }
        .badge-em_revisao   { background:#fff3cd; color:#856404; }
        .badge-revisada     { background:#c3e6cb; color:#155724; }
        .badge-contestado   { background:#f8d7da; color:#721c24; }
        .badge-publicado    { background:var(--cor-primaria); color:white; }

        /* Prioridade inline select */
        .sel-prioridade, .sel-atribuir {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 4px 8px;
            font-size: 0.83em;
            background: white;
            cursor: pointer;
            width: 100%;
        }
        .sel-prioridade:focus, .sel-atribuir:focus { outline: none; border-color: var(--cor-primaria); }

        .prio-urgente { color: #b02a37; font-weight: 700; }
        .prio-alta    { color: #d97706; font-weight: 600; }
        .prio-media   { color: #555; }
        .prio-baixa   { color: #aaa; }

        .empty { text-align: center; padding: 40px; color: #aaa; font-size: 0.95em; }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1>🌿 Gestão de Espécies</h1>
        <a href="/penomato_mvp/src/Controllers/controlador_gestor.php" class="btn-voltar">← Voltar ao painel</a>
    </div>

    <!-- Filtros por status -->
    <div class="stats-bar">
        <a href="gestao_especies.php" class="stat-chip <?php echo !$filtro_status ? 'ativo' : ''; ?>">
            Todas <span class="num"><?php echo $total_geral; ?></span>
        </a>
        <?php
        $labels = [
            'sem_dados'      => 'Sem dados',
            'dados_internet' => 'Dados internet',
            'identificado'   => 'Identificado',
            'registrada'     => 'Registrada',
            'em_revisao'     => 'Em revisão',
            'revisada'       => 'Revisada',
            'contestado'     => 'Contestado',
            'publicado'      => 'Publicado',
        ];
        foreach ($labels as $key => $label):
            if (empty($contagens[$key])) continue;
        ?>
        <a href="gestao_especies.php?status=<?php echo $key; ?>"
           class="stat-chip <?php echo $filtro_status === $key ? 'ativo' : ''; ?>">
            <?php echo $label; ?> <span class="num"><?php echo $contagens[$key]; ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Tabela -->
    <div class="table-wrap">
        <?php if (empty($especies)): ?>
            <div class="empty">Nenhuma espécie encontrada.</div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome Científico</th>
                    <th>Status</th>
                    <th style="width:140px">Prioridade</th>
                    <th style="width:190px">Atribuído a</th>
                    <th>Última atualização</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($especies as $e): ?>
                <tr>
                    <td style="color:#aaa;font-size:0.8em;"><?php echo $e['id']; ?></td>
                    <td class="nome-cientifico"><?php echo htmlspecialchars($e['nome_cientifico']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $e['status']; ?>">
                            <?php echo $labels[$e['status']] ?? $e['status']; ?>
                        </span>
                    </td>

                    <!-- Prioridade -->
                    <td>
                        <form method="POST" action="gestao_especies.php">
                            <input type="hidden" name="acao" value="prioridade">
                            <input type="hidden" name="especie_id" value="<?php echo $e['id']; ?>">
                            <input type="hidden" name="filtro_status" value="<?php echo htmlspecialchars($filtro_status); ?>">
                            <select name="prioridade" class="sel-prioridade prio-<?php echo $e['prioridade']; ?>"
                                    onchange="this.form.submit()">
                                <option value="baixa"   <?php echo $e['prioridade']==='baixa'   ?'selected':''; ?>>⬇ Baixa</option>
                                <option value="media"   <?php echo $e['prioridade']==='media'   ?'selected':''; ?>>➡ Média</option>
                                <option value="alta"    <?php echo $e['prioridade']==='alta'    ?'selected':''; ?>>⬆ Alta</option>
                                <option value="urgente" <?php echo $e['prioridade']==='urgente' ?'selected':''; ?>>🔴 Urgente</option>
                            </select>
                        </form>
                    </td>

                    <!-- Atribuir colaborador -->
                    <td>
                        <form method="POST" action="gestao_especies.php">
                            <input type="hidden" name="acao" value="atribuir">
                            <input type="hidden" name="especie_id" value="<?php echo $e['id']; ?>">
                            <input type="hidden" name="filtro_status" value="<?php echo htmlspecialchars($filtro_status); ?>">
                            <select name="atribuido_a" class="sel-atribuir" onchange="this.form.submit()">
                                <option value="">— Ninguém —</option>
                                <?php foreach ($colaboradores as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"
                                        <?php echo $e['atribuido_a'] == $c['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($c['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>

                    <td style="color:#999;font-size:0.82em;white-space:nowrap;">
                        <?php echo $e['data_ultima_atualizacao'] ? date('d/m/Y H:i', strtotime($e['data_ultima_atualizacao'])) : '—'; ?>
                    </td>
                    <td>
                        <a href="gerenciar_especie.php?id=<?php echo $e['id']; ?>"
                           style="display:inline-flex;align-items:center;gap:5px;padding:5px 13px;background:#e8f5e9;color:var(--cor-primaria);border-radius:20px;font-size:.8em;font-weight:600;text-decoration:none;white-space:nowrap;border:1px solid #c8e6c9;transition:background .15s;"
                           onmouseover="this.style.background='#c8e6c9'" onmouseout="this.style.background='#e8f5e9'">
                            ⚙ Gerenciar
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
