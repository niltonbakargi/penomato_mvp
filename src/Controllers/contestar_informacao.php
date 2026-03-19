<?php
session_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /penomato_mvp/src/Views/auth/login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$msgs = [];

// ================================================
// PROCESSAR ENVIO
// ================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $especie_id       = (int)($_POST['especie_id'] ?? 0);
    $subtipo          = $_POST['subtipo'] ?? '';
    $conteudo_atual   = trim($_POST['conteudo_atual'] ?? '');
    $conteudo_correto = trim($_POST['conteudo_correto'] ?? '');
    $referencias      = trim($_POST['referencias'] ?? '');
    $observacoes      = trim($_POST['observacoes'] ?? '');

    $subtipos_validos = ['contestar_informacao', 'contestar_imagem', 'erro_sistema', 'outros'];

    if (!$especie_id) {
        $msgs[] = ['tipo' => 'err', 'texto' => 'Selecione uma espécie.'];
    } elseif (!in_array($subtipo, $subtipos_validos)) {
        $msgs[] = ['tipo' => 'err', 'texto' => 'Selecione o tipo de contestação.'];
    } elseif (!$conteudo_atual) {
        $msgs[] = ['tipo' => 'err', 'texto' => 'Descreva o problema encontrado.'];
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO fila_aprovacao
                (tipo, subtipo, especie_id, usuario_id, descricao,
                 conteudo_atual, conteudo_correto, referencias, observacoes)
            VALUES
                ('contestacao', ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $subtipo, $especie_id, $usuario_id,
            "Contestação: $subtipo",
            $conteudo_atual, $conteudo_correto, $referencias, $observacoes
        ]);
        $msgs[] = ['tipo' => 'ok', 'texto' => 'Contestação enviada. O gestor será notificado para análise.'];
    }
}

// ================================================
// DADOS
// ================================================
$especies = $pdo->query("
    SELECT id, nome_cientifico, status FROM especies_administrativo
    ORDER BY nome_cientifico
")->fetchAll(PDO::FETCH_ASSOC);

// Espécie selecionada (para pré-preencher)
$especie_selecionada = null;
$especie_id_sel = (int)($_GET['especie_id'] ?? $_POST['especie_id'] ?? 0);
if ($especie_id_sel) {
    foreach ($especies as $e) {
        if ($e['id'] === $especie_id_sel) { $especie_selecionada = $e; break; }
    }
}

// Imagens da espécie selecionada
$imagens = [];
if ($especie_id_sel) {
    $stmt = $pdo->prepare("
        SELECT id, parte_planta, caminho_imagem, autor_imagem, licenca, data_upload
        FROM especies_imagens WHERE especie_id = ? ORDER BY parte_planta
    ");
    $stmt->execute([$especie_id_sel]);
    $imagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Características da espécie selecionada
$caracteristicas = null;
if ($especie_id_sel) {
    $stmt = $pdo->prepare("SELECT * FROM especies_caracteristicas WHERE especie_id = ? LIMIT 1");
    $stmt->execute([$especie_id_sel]);
    $caracteristicas = $stmt->fetch(PDO::FETCH_ASSOC);
}

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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contestar Informação — Penomato</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f4f0;
            color: #1e2e1e;
            padding: 24px 20px;
        }
        .container { max-width: 960px; margin: 0 auto; }

        .header {
            background: var(--cor-primaria);
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

        .layout { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start; }
        @media (max-width: 720px) { .layout { grid-template-columns: 1fr; } }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            overflow: hidden;
        }
        .card-header {
            background: #f7f9f7;
            padding: 13px 18px;
            border-bottom: 2px solid #e0e8e0;
            font-size: 0.92em;
            font-weight: 700;
            color: var(--cor-primaria);
        }
        .card-body { padding: 18px; }

        label {
            display: block;
            font-size: 0.83em;
            font-weight: 700;
            color: #555;
            margin-bottom: 5px;
            margin-top: 14px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        label:first-child { margin-top: 0; }

        select, textarea, input[type=text] {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #d0d8d0;
            border-radius: 6px;
            font-size: 0.9em;
            font-family: inherit;
            color: #1e2e1e;
            background: white;
        }
        select:focus, textarea:focus, input[type=text]:focus {
            outline: none;
            border-color: var(--cor-primaria);
        }
        textarea { resize: vertical; min-height: 90px; line-height: 1.5; }

        .tipo-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 0;
        }
        .tipo-btn {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.85em;
            font-weight: 600;
            color: #555;
            cursor: pointer;
            background: white;
            text-align: center;
            transition: all 0.15s;
        }
        .tipo-btn:hover { border-color: var(--cor-primaria); color: var(--cor-primaria); }
        .tipo-btn.selecionado { border-color: var(--cor-primaria); background: #e8f5e9; color: var(--cor-primaria); }
        .tipo-btn .icon { font-size: 1.3em; display: block; margin-bottom: 4px; }

        #subtipo-hidden { display: none; }

        .btn-enviar {
            width: 100%;
            background: var(--cor-primaria);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.95em;
            font-weight: 700;
            cursor: pointer;
            margin-top: 18px;
        }
        .btn-enviar:hover { background: #094d36; }

        .msg-ok  { background:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:6px; padding:10px 14px; margin-bottom:14px; font-size:0.9em; }
        .msg-err { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; border-radius:6px; padding:10px 14px; margin-bottom:14px; font-size:0.9em; }

        /* Painel de detalhes da espécie */
        .especie-info {
            font-size: 0.85em;
            color: #555;
            line-height: 1.6;
        }
        .especie-info .nome { font-style: italic; font-weight: 700; color: #1a3a28; font-size: 1em; }
        .badge-status {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 9px;
            font-size: 0.78em;
            font-weight: 600;
            background: #e0ebe0;
            color: var(--cor-primaria);
            margin-left: 6px;
        }

        /* Galeria de imagens */
        .galeria {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-top: 10px;
        }
        .galeria-item {
            border: 2px solid transparent;
            border-radius: 7px;
            overflow: hidden;
            cursor: pointer;
            transition: border-color 0.15s;
            position: relative;
        }
        .galeria-item:hover { border-color: var(--cor-primaria); }
        .galeria-item.selecionada { border-color: var(--cor-primaria); box-shadow: 0 0 0 2px var(--cor-primaria); }
        .galeria-item img { width: 100%; height: 72px; object-fit: cover; display: block; }
        .galeria-item .parte {
            background: rgba(0,0,0,0.55);
            color: white;
            font-size: 0.68em;
            text-align: center;
            padding: 2px 4px;
        }
        .galeria-vazia { font-size: 0.83em; color: #bbb; padding: 10px 0; }

        /* Campos de características resumidos */
        .carac-lista {
            font-size: 0.82em;
            color: #555;
            line-height: 1.8;
            max-height: 220px;
            overflow-y: auto;
        }
        .carac-lista .campo { display: flex; gap: 6px; }
        .carac-lista .chave { color: #999; min-width: 140px; font-size: 0.9em; }
        .carac-lista .valor { color: #1a3a28; font-weight: 500; }
        .carac-vazia { font-size: 0.83em; color: #bbb; }

        .empty-state { text-align: center; padding: 30px; color: #bbb; font-size: 0.9em; }

        /* Histórico de contestações do usuário */
        .historico-item {
            font-size: 0.83em;
            border-bottom: 1px solid #f0f0f0;
            padding: 10px 0;
            color: #555;
        }
        .historico-item:last-child { border-bottom: none; }
        .historico-item .esp { font-style: italic; font-weight: 600; color: #1a3a28; }
        .historico-item .st-pendente  { color: #d97706; font-weight: 600; }
        .historico-item .st-aprovado  { color: var(--cor-primaria); font-weight: 600; }
        .historico-item .st-rejeitado { color: #dc3545; font-weight: 600; }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1>⚠️ Contestar Informação</h1>
        <a href="/penomato_mvp/src/Views/entrar_colaborador.php" class="btn-voltar">← Voltar</a>
    </div>

    <div class="layout">

        <!-- ══ COLUNA ESQUERDA: FORMULÁRIO ══ -->
        <div>
            <div class="card">
                <div class="card-header">📋 Nova Contestação</div>
                <div class="card-body">

                    <?php foreach ($msgs as $m): ?>
                        <div class="msg-<?php echo $m['tipo']; ?>"><?php echo htmlspecialchars($m['texto']); ?></div>
                    <?php endforeach; ?>

                    <form method="POST" action="contestar_informacao.php" id="form-contestacao">
                        <input type="hidden" name="subtipo" id="subtipo-hidden">

                        <label>Espécie</label>
                        <select name="especie_id" id="sel-especie" onchange="carregarEspecie(this.value)" required>
                            <option value="">Selecione a espécie...</option>
                            <?php foreach ($especies as $e): ?>
                                <option value="<?php echo $e['id']; ?>"
                                    <?php echo $e['id'] === $especie_id_sel ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($e['nome_cientifico']); ?>
                                    (<?php echo $labels_status[$e['status']] ?? $e['status']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <label style="margin-top:16px;">Tipo de Contestação</label>
                        <div class="tipo-grid">
                            <button type="button" class="tipo-btn" data-val="contestar_informacao"
                                    onclick="selecionarTipo(this)">
                                <span class="icon">📄</span>
                                Contestar Informação
                            </button>
                            <button type="button" class="tipo-btn" data-val="contestar_imagem"
                                    onclick="selecionarTipo(this)">
                                <span class="icon">🖼️</span>
                                Contestar Imagem
                            </button>
                            <button type="button" class="tipo-btn" data-val="erro_sistema"
                                    onclick="selecionarTipo(this)">
                                <span class="icon">🔧</span>
                                Erro de Sistema
                            </button>
                            <button type="button" class="tipo-btn" data-val="outros"
                                    onclick="selecionarTipo(this)">
                                <span class="icon">💬</span>
                                Outros
                            </button>
                        </div>

                        <label>O que está incorreto / problema encontrado</label>
                        <textarea name="conteudo_atual"
                                  placeholder="Descreva o que está errado ou inconsistente. Ex: 'O tipo de fruto está classificado como drupa, mas deveria ser aquênio segundo Flora do Brasil 2020.'"
                                  required><?php echo htmlspecialchars($_POST['conteudo_atual'] ?? ''); ?></textarea>

                        <label>O que deveria estar correto</label>
                        <textarea name="conteudo_correto"
                                  placeholder="Descreva a informação correta. Ex: 'Fruto aquênio, com 3–5 mm de comprimento, superfície rugosa...'">
<?php echo htmlspecialchars($_POST['conteudo_correto'] ?? ''); ?></textarea>

                        <label>Referências</label>
                        <textarea name="referencias" style="min-height:60px;"
                                  placeholder="Ex: Flora do Brasil 2020. Disponível em: floradobrasil.jbrj.gov.br&#10;Lorenzi, H. (2008). Árvores Brasileiras, vol. 1, p. 312.">
<?php echo htmlspecialchars($_POST['referencias'] ?? ''); ?></textarea>

                        <label>Observações adicionais</label>
                        <textarea name="observacoes" style="min-height:60px;"
                                  placeholder="Contexto extra, links úteis, nome do coletor, data de observação em campo...">
<?php echo htmlspecialchars($_POST['observacoes'] ?? ''); ?></textarea>

                        <button type="submit" class="btn-enviar">⚠️ Enviar Contestação</button>
                    </form>
                </div>
            </div>

            <!-- Histórico do usuário -->
            <?php
            $historico = $pdo->prepare("
                SELECT f.subtipo, f.status, f.data_submissao, e.nome_cientifico
                FROM fila_aprovacao f
                JOIN especies_administrativo e ON e.id = f.especie_id
                WHERE f.tipo = 'contestacao' AND f.usuario_id = ?
                ORDER BY f.data_submissao DESC
                LIMIT 10
            ");
            $historico->execute([$usuario_id]);
            $hist_rows = $historico->fetchAll(PDO::FETCH_ASSOC);
            $labels_subtipo = [
                'contestar_informacao' => 'Informação',
                'contestar_imagem'     => 'Imagem',
                'erro_sistema'         => 'Erro de sistema',
                'outros'               => 'Outros',
            ];
            ?>
            <?php if ($hist_rows): ?>
            <div class="card" style="margin-top:16px;">
                <div class="card-header">🕐 Minhas contestações</div>
                <div class="card-body" style="padding:10px 18px;">
                    <?php foreach ($hist_rows as $h): ?>
                    <div class="historico-item">
                        <span class="esp"><?php echo htmlspecialchars($h['nome_cientifico']); ?></span>
                        — <?php echo $labels_subtipo[$h['subtipo']] ?? $h['subtipo']; ?>
                        <span class="st-<?php echo $h['status']; ?>">
                            · <?php echo ucfirst($h['status']); ?>
                        </span>
                        <span style="color:#ccc;font-size:0.9em;">
                            · <?php echo date('d/m/Y', strtotime($h['data_submissao'])); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ══ COLUNA DIREITA: DETALHES DA ESPÉCIE ══ -->
        <div>

            <!-- Info da espécie -->
            <div class="card" id="card-especie">
                <div class="card-header">🌿 Dados da espécie selecionada</div>
                <div class="card-body">
                    <?php if ($especie_selecionada): ?>
                    <div class="especie-info">
                        <div class="nome"><?php echo htmlspecialchars($especie_selecionada['nome_cientifico']); ?></div>
                        <div style="margin-top:4px;">
                            Status:
                            <span class="badge-status">
                                <?php echo $labels_status[$especie_selecionada['status']] ?? $especie_selecionada['status']; ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($caracteristicas): ?>
                    <div style="margin-top:14px;">
                        <div style="font-size:0.8em;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;">Características cadastradas</div>
                        <div class="carac-lista">
                            <?php
                            $campos_exibir = [
                                'familia'         => 'Família',
                                'forma_folha'     => 'Forma da folha',
                                'tipo_folha'      => 'Tipo de folha',
                                'cor_flores'      => 'Cor das flores',
                                'tipo_fruto'      => 'Tipo de fruto',
                                'cor_fruto'       => 'Cor do fruto',
                                'tipo_caule'      => 'Tipo de caule',
                                'tipo_semente'    => 'Tipo de semente',
                                'cor_semente'     => 'Cor da semente',
                                'nome_popular'    => 'Nome popular',
                            ];
                            $tem = false;
                            foreach ($campos_exibir as $campo => $label):
                                if (!empty($caracteristicas[$campo])):
                                    $tem = true;
                            ?>
                                <div class="campo">
                                    <span class="chave"><?php echo $label; ?></span>
                                    <span class="valor"><?php echo htmlspecialchars($caracteristicas[$campo]); ?></span>
                                </div>
                            <?php endif; endforeach;
                            if (!$tem): ?>
                                <div class="carac-vazia">Nenhuma característica cadastrada ainda.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php else: ?>
                        <div class="carac-vazia" style="margin-top:10px;">Nenhuma característica cadastrada.</div>
                    <?php endif; ?>

                    <?php else: ?>
                    <div class="empty-state">Selecione uma espécie para ver os dados.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Galeria de imagens -->
            <div class="card" style="margin-top:16px;">
                <div class="card-header">🖼️ Imagens da espécie</div>
                <div class="card-body">
                    <?php if ($imagens): ?>
                    <div class="galeria" id="galeria">
                        <?php foreach ($imagens as $img): ?>
                        <div class="galeria-item" onclick="selecionarImagem(this, '<?php echo $img['id']; ?>', '<?php echo htmlspecialchars($img['parte_planta']); ?>')">
                            <img src="/penomato_mvp/<?php echo htmlspecialchars($img['caminho_imagem']); ?>"
                                 onerror="this.src='/penomato_mvp/assets/img-placeholder.png'"
                                 alt="<?php echo htmlspecialchars($img['parte_planta']); ?>">
                            <div class="parte"><?php echo htmlspecialchars($img['parte_planta']); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="img-selecionada-info" style="margin-top:10px;font-size:0.82em;color:#888;display:none;">
                        Imagem selecionada: <strong id="img-sel-nome"></strong>
                        <input type="hidden" name="imagem_id" id="imagem_id_input" form="form-contestacao">
                    </div>
                    <?php elseif ($especie_id_sel): ?>
                        <div class="galeria-vazia">Nenhuma imagem cadastrada para esta espécie.</div>
                    <?php else: ?>
                        <div class="empty-state">Selecione uma espécie para ver as imagens.</div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
function selecionarTipo(btn) {
    document.querySelectorAll('.tipo-btn').forEach(b => b.classList.remove('selecionado'));
    btn.classList.add('selecionado');
    document.getElementById('subtipo-hidden').value = btn.dataset.val;
}

function selecionarImagem(el, id, nome) {
    document.querySelectorAll('.galeria-item').forEach(i => i.classList.remove('selecionada'));
    el.classList.add('selecionada');
    document.getElementById('img-sel-nome').textContent = nome;
    document.getElementById('imagem_id_input').value = id;
    document.getElementById('img-selecionada-info').style.display = 'block';
    // Se o tipo imagem ainda não estiver selecionado, seleciona automaticamente
    const tipoAtual = document.getElementById('subtipo-hidden').value;
    if (!tipoAtual || tipoAtual === '') {
        const btnImg = document.querySelector('[data-val="contestar_imagem"]');
        selecionarTipo(btnImg);
    }
}

function carregarEspecie(id) {
    if (id) {
        window.location.href = 'contestar_informacao.php?especie_id=' + id;
    }
}

// Restaurar tipo selecionado no POST
<?php if (!empty($_POST['subtipo'])): ?>
const btnRestore = document.querySelector('[data-val="<?php echo htmlspecialchars($_POST['subtipo']); ?>"]');
if (btnRestore) selecionarTipo(btnRestore);
<?php endif; ?>
</script>
</body>
</html>
