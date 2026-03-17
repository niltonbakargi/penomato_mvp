<?php
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /penomato_mvp/src/Views/auth/login.php');
    exit;
}

// Parâmetros de filtro
$dias_sem_dados  = (int)($_GET['dias_sem_dados']  ?? 30);
$dias_inativo    = (int)($_GET['dias_inativo']    ?? 30);
$limite_contrib  = (int)($_GET['limite_contrib']  ?? 30);

// ================================================
// 1. ÚLTIMAS CONTRIBUIÇÕES
// ================================================
$ultimas_contrib = $pdo->prepare("
    SELECT h.tipo_acao, h.tabela_afetada, h.campo_alterado, h.data_alteracao,
           u.nome AS usuario_nome, u.categoria AS usuario_categoria,
           e.nome_cientifico
    FROM historico_alteracoes h
    JOIN usuarios u ON u.id = h.id_usuario
    JOIN especies_administrativo e ON e.id = h.especie_id
    ORDER BY h.data_alteracao DESC
    LIMIT ?
");
$ultimas_contrib->execute([$limite_contrib]);
$contribuicoes = $ultimas_contrib->fetchAll(PDO::FETCH_ASSOC);

// Também incluir uploads recentes (imagens)
$uploads_recentes = $pdo->prepare("
    SELECT i.data_upload, i.parte_planta, i.status_validacao,
           u.nome AS usuario_nome, u.categoria AS usuario_categoria,
           e.nome_cientifico
    FROM especies_imagens i
    JOIN usuarios u ON u.id = i.id_usuario_identificador
    JOIN especies_administrativo e ON e.id = i.especie_id
    ORDER BY i.data_upload DESC
    LIMIT ?
");
$uploads_recentes->execute([$limite_contrib]);
$uploads = $uploads_recentes->fetchAll(PDO::FETCH_ASSOC);

// ================================================
// 2. ESPÉCIES SEM DADOS HÁ MAIS DE X DIAS
// ================================================
$especies_paradas = $pdo->prepare("
    SELECT id, nome_cientifico, status, prioridade, data_ultima_atualizacao,
           DATEDIFF(NOW(), data_ultima_atualizacao) AS dias_parada,
           atribuido_a
    FROM especies_administrativo
    WHERE status IN ('sem_dados', 'dados_internet', 'identificado')
      AND data_ultima_atualizacao < DATE_SUB(NOW(), INTERVAL ? DAY)
    ORDER BY dias_parada DESC, prioridade DESC
");
$especies_paradas->execute([$dias_sem_dados]);
$paradas = $especies_paradas->fetchAll(PDO::FETCH_ASSOC);

// Nomes dos atribuídos
$nomes_atribuidos = [];
if ($paradas) {
    $ids = array_filter(array_column($paradas, 'atribuido_a'));
    if ($ids) {
        $in = implode(',', array_map('intval', $ids));
        $nomes_atribuidos = $pdo->query("SELECT id, nome FROM usuarios WHERE id IN ($in)")
                               ->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}

// ================================================
// 3. MEMBROS INATIVOS
// ================================================
$membros_inativos = $pdo->prepare("
    SELECT id, nome, email, categoria, ultimo_acesso, data_cadastro,
           CASE
               WHEN ultimo_acesso IS NULL THEN DATEDIFF(NOW(), data_cadastro)
               ELSE DATEDIFF(NOW(), ultimo_acesso)
           END AS dias_sem_acesso
    FROM usuarios
    WHERE ativo = 1
      AND status_verificacao = 'verificado'
      AND (ultimo_acesso IS NULL OR ultimo_acesso < DATE_SUB(NOW(), INTERVAL ? DAY))
    ORDER BY dias_sem_acesso DESC
");
$membros_inativos->execute([$dias_inativo]);
$inativos = $membros_inativos->fetchAll(PDO::FETCH_ASSOC);

// ================================================
// RÓTULOS
// ================================================
$labels_acao = [
    'insercao'    => 'Inserção',
    'edicao'      => 'Edição',
    'revisao'     => 'Revisão',
    'contestacao' => 'Contestação',
    'validacao'   => 'Validação',
    'publicacao'  => 'Publicação',
];
$labels_status = [
    'sem_dados'      => 'Sem dados',
    'dados_internet' => 'Dados internet',
    'identificado'   => 'Identificado',
    'registrada'     => 'Registrada',
    'em_revisao'     => 'Em revisão',
    'revisada'       => 'Revisada',
    'contestado'     => 'Contestado',
    'publicado'      => 'Publicado',
];
$labels_prio = [
    'urgente' => '🔴 Urgente',
    'alta'    => '⬆ Alta',
    'media'   => '➡ Média',
    'baixa'   => '⬇ Baixa',
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoramento — Penomato</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f4f0;
            color: #1e2e1e;
            padding: 24px 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }

        .header {
            background: #0b5e42;
            color: white;
            padding: 18px 28px;
            border-radius: 10px;
            margin-bottom: 24px;
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

        /* Filtros */
        .filtros-bar {
            background: white;
            border-radius: 10px;
            padding: 14px 20px;
            margin-bottom: 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            display: flex;
            gap: 24px;
            align-items: center;
            flex-wrap: wrap;
        }
        .filtros-bar label {
            font-size: 0.85em;
            font-weight: 600;
            color: #555;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .filtros-bar input[type=number] {
            width: 70px;
            padding: 5px 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 0.88em;
        }
        .filtros-bar input[type=number]:focus { outline: none; border-color: #0b5e42; }
        .btn-filtrar {
            background: #0b5e42;
            color: white;
            border: none;
            padding: 7px 20px;
            border-radius: 5px;
            font-size: 0.88em;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-filtrar:hover { background: #094d36; }

        /* Seções */
        .secao {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            margin-bottom: 28px;
            overflow: hidden;
        }
        .secao-header {
            background: #f7f9f7;
            padding: 14px 20px;
            border-bottom: 2px solid #e0e8e0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .secao-header h2 {
            font-size: 1em;
            font-weight: 700;
            color: #0b5e42;
        }
        .secao-header .count {
            background: #0b5e42;
            color: white;
            border-radius: 10px;
            padding: 2px 10px;
            font-size: 0.82em;
            font-weight: 700;
        }
        .secao-header .count.warn { background: #d97706; }
        .secao-header .count.danger { background: #b02a37; }

        /* Tabs contribuições */
        .tabs {
            display: flex;
            border-bottom: 1px solid #e0e8e0;
        }
        .tab {
            padding: 10px 20px;
            font-size: 0.87em;
            font-weight: 600;
            color: #888;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
        }
        .tab.ativo { color: #0b5e42; border-bottom-color: #0b5e42; }

        .tab-content { display: none; }
        .tab-content.ativo { display: block; }

        /* Tabelas */
        table { width: 100%; border-collapse: collapse; font-size: 0.87em; }
        th {
            padding: 11px 14px;
            text-align: left;
            font-weight: 600;
            color: #666;
            white-space: nowrap;
            border-bottom: 1px solid #eee;
        }
        td { padding: 10px 14px; border-bottom: 1px solid #f3f3f3; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fafffe; }

        .nome-cientifico { font-style: italic; font-weight: 500; color: #1a3a28; }

        .badge {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 9px;
            font-size: 0.78em;
            font-weight: 600;
            white-space: nowrap;
        }
        .badge-insercao    { background: #d4edda; color: #155724; }
        .badge-edicao      { background: #cfe2ff; color: #084298; }
        .badge-revisao     { background: #fff3cd; color: #856404; }
        .badge-contestacao { background: #f8d7da; color: #721c24; }
        .badge-validacao   { background: #d1ecf1; color: #0c5460; }
        .badge-publicacao  { background: #0b5e42; color: white; }
        .badge-upload      { background: #e2d9f3; color: #6f42c1; }

        .badge-sem_dados      { background:#f0f0f0; color:#666; }
        .badge-dados_internet { background:#cfe2ff; color:#084298; }
        .badge-identificado   { background:#d1ecf1; color:#0c5460; }

        .prio-urgente { color: #b02a37; font-weight: 700; }
        .prio-alta    { color: #d97706; font-weight: 600; }
        .prio-media   { color: #555; }
        .prio-baixa   { color: #aaa; }

        .dias-alerta { color: #b02a37; font-weight: 700; }
        .dias-aviso  { color: #d97706; font-weight: 600; }
        .dias-ok     { color: #888; }

        .empty { text-align: center; padding: 30px; color: #bbb; font-size: 0.92em; }

        .tempo { color: #999; font-size: 0.82em; white-space: nowrap; }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1>📡 Monitoramento</h1>
        <a href="/penomato_mvp/src/Controllers/controlador_gestor.php" class="btn-voltar">← Voltar ao painel</a>
    </div>

    <!-- Filtros -->
    <form method="GET" action="monitoramento.php">
        <div class="filtros-bar">
            <label>
                Últimas contribuições:
                <input type="number" name="limite_contrib" value="<?php echo $limite_contrib; ?>" min="5" max="200"> registros
            </label>
            <label>
                Espécies paradas há mais de:
                <input type="number" name="dias_sem_dados" value="<?php echo $dias_sem_dados; ?>" min="1"> dias
            </label>
            <label>
                Membros inativos há mais de:
                <input type="number" name="dias_inativo" value="<?php echo $dias_inativo; ?>" min="1"> dias
            </label>
            <button type="submit" class="btn-filtrar">Atualizar</button>
        </div>
    </form>

    <!-- ══════════════════════════════════════════ -->
    <!-- 1. ÚLTIMAS CONTRIBUIÇÕES                   -->
    <!-- ══════════════════════════════════════════ -->
    <div class="secao">
        <div class="secao-header">
            <h2>📝 Últimas Contribuições</h2>
            <span class="count"><?php echo count($contribuicoes) + count($uploads); ?></span>
        </div>

        <div class="tabs">
            <div class="tab ativo" onclick="trocarTab('tab-historico', this)">
                Histórico de alterações (<?php echo count($contribuicoes); ?>)
            </div>
            <div class="tab" onclick="trocarTab('tab-uploads', this)">
                Uploads de imagens (<?php echo count($uploads); ?>)
            </div>
        </div>

        <!-- Tab: Histórico -->
        <div class="tab-content ativo" id="tab-historico">
            <?php if (empty($contribuicoes)): ?>
                <div class="empty">Nenhum registro encontrado.</div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Quando</th>
                        <th>Colaborador</th>
                        <th>Categoria</th>
                        <th>Ação</th>
                        <th>Espécie</th>
                        <th>Campo</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($contribuicoes as $c): ?>
                    <tr>
                        <td class="tempo"><?php echo date('d/m/Y H:i', strtotime($c['data_alteracao'])); ?></td>
                        <td><?php echo htmlspecialchars($c['usuario_nome']); ?></td>
                        <td><span style="color:#888;font-size:0.85em;"><?php echo $c['usuario_categoria']; ?></span></td>
                        <td><span class="badge badge-<?php echo $c['tipo_acao']; ?>"><?php echo $labels_acao[$c['tipo_acao']] ?? $c['tipo_acao']; ?></span></td>
                        <td class="nome-cientifico"><?php echo htmlspecialchars($c['nome_cientifico']); ?></td>
                        <td style="color:#aaa;font-size:0.82em;"><?php echo htmlspecialchars($c['campo_alterado'] ?? '—'); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Tab: Uploads -->
        <div class="tab-content" id="tab-uploads">
            <?php if (empty($uploads)): ?>
                <div class="empty">Nenhum upload encontrado.</div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Quando</th>
                        <th>Colaborador</th>
                        <th>Categoria</th>
                        <th>Tipo</th>
                        <th>Espécie</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($uploads as $u): ?>
                    <tr>
                        <td class="tempo"><?php echo date('d/m/Y H:i', strtotime($u['data_upload'])); ?></td>
                        <td><?php echo htmlspecialchars($u['usuario_nome']); ?></td>
                        <td><span style="color:#888;font-size:0.85em;"><?php echo $u['usuario_categoria']; ?></span></td>
                        <td><span class="badge badge-upload"><?php echo htmlspecialchars($u['parte_planta']); ?></span></td>
                        <td class="nome-cientifico"><?php echo htmlspecialchars($u['nome_cientifico']); ?></td>
                        <td style="font-size:0.83em;color:#666;"><?php echo $u['status_validacao']; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══════════════════════════════════════════ -->
    <!-- 2. ESPÉCIES PARADAS                        -->
    <!-- ══════════════════════════════════════════ -->
    <?php
    $count_paradas = count($paradas);
    $count_class = $count_paradas > 10 ? 'danger' : ($count_paradas > 3 ? 'warn' : '');
    ?>
    <div class="secao">
        <div class="secao-header">
            <h2>⏳ Espécies sem atualização há mais de <?php echo $dias_sem_dados; ?> dias</h2>
            <span class="count <?php echo $count_class; ?>"><?php echo $count_paradas; ?></span>
        </div>

        <?php if (empty($paradas)): ?>
            <div class="empty">Nenhuma espécie parada nesse período. ✓</div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Espécie</th>
                    <th>Status</th>
                    <th>Prioridade</th>
                    <th>Atribuído a</th>
                    <th>Última atualização</th>
                    <th>Parada há</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($paradas as $p): ?>
                <?php
                $dias = (int)$p['dias_parada'];
                $dias_class = $dias > 90 ? 'dias-alerta' : ($dias > 30 ? 'dias-aviso' : 'dias-ok');
                ?>
                <tr>
                    <td class="nome-cientifico"><?php echo htmlspecialchars($p['nome_cientifico']); ?></td>
                    <td><span class="badge badge-<?php echo $p['status']; ?>"><?php echo $labels_status[$p['status']] ?? $p['status']; ?></span></td>
                    <td class="prio-<?php echo $p['prioridade']; ?>"><?php echo $labels_prio[$p['prioridade']] ?? $p['prioridade']; ?></td>
                    <td style="color:#666;font-size:0.87em;"><?php echo $p['atribuido_a'] ? htmlspecialchars($nomes_atribuidos[$p['atribuido_a']] ?? '—') : '—'; ?></td>
                    <td class="tempo"><?php echo $p['data_ultima_atualizacao'] ? date('d/m/Y', strtotime($p['data_ultima_atualizacao'])) : '—'; ?></td>
                    <td class="<?php echo $dias_class; ?>"><?php echo $dias; ?> dias</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- ══════════════════════════════════════════ -->
    <!-- 3. MEMBROS INATIVOS                        -->
    <!-- ══════════════════════════════════════════ -->
    <?php
    $count_inativos = count($inativos);
    $inativo_class = $count_inativos > 5 ? 'warn' : '';
    ?>
    <div class="secao">
        <div class="secao-header">
            <h2>👤 Membros sem acesso há mais de <?php echo $dias_inativo; ?> dias</h2>
            <span class="count <?php echo $inativo_class; ?>"><?php echo $count_inativos; ?></span>
        </div>

        <?php if (empty($inativos)): ?>
            <div class="empty">Nenhum membro inativo nesse período. ✓</div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Categoria</th>
                    <th>Último acesso</th>
                    <th>Inativo há</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($inativos as $m): ?>
                <?php
                $dias = (int)$m['dias_sem_acesso'];
                $dias_class = $dias > 90 ? 'dias-alerta' : ($dias > 30 ? 'dias-aviso' : 'dias-ok');
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($m['nome']); ?></td>
                    <td style="color:#888;font-size:0.85em;"><?php echo htmlspecialchars($m['email']); ?></td>
                    <td style="color:#888;font-size:0.85em;"><?php echo $m['categoria']; ?></td>
                    <td class="tempo">
                        <?php echo $m['ultimo_acesso']
                            ? date('d/m/Y H:i', strtotime($m['ultimo_acesso']))
                            : '<span style="color:#bbb">Nunca acessou</span>'; ?>
                    </td>
                    <td class="<?php echo $dias_class; ?>"><?php echo $dias; ?> dias</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>

<script>
function trocarTab(idAlvo, el) {
    const secao = el.closest('.secao');
    secao.querySelectorAll('.tab').forEach(t => t.classList.remove('ativo'));
    secao.querySelectorAll('.tab-content').forEach(c => c.classList.remove('ativo'));
    el.classList.add('ativo');
    document.getElementById(idAlvo).classList.add('ativo');
}
</script>
</body>
</html>
