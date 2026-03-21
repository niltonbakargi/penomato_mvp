<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /penomato_mvp/src/Views/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

require_once __DIR__ . '/../../config/banco_de_dados.php';

$usuario_id   = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Colaborador';

$sucesso = '';
$erro    = '';

// ================================================
// PROCESSAR ENVIO
// ================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_sugestao'])) {
    $assunto   = trim($_POST['assunto']   ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');

    if (empty($assunto) || empty($descricao)) {
        $erro = 'Preencha o assunto e a descrição antes de enviar.';
    } else {
        // Usa nome_cientifico p/ assunto e sugestao_caracteristicas p/ categoria
        $stmt = $pdo->prepare("
            INSERT INTO sugestoes_usuario (id_usuario, nome_cientifico, sugestao_caracteristicas, outras_sugestoes)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$usuario_id, $assunto, $categoria, $descricao]);
        $sucesso = 'Sugestão enviada com sucesso! A equipe vai analisar em breve.';
    }
}

// ================================================
// BUSCAR SUGESTÕES DO USUÁRIO
// ================================================
$stmt = $pdo->prepare("
    SELECT id, nome_cientifico AS assunto, sugestao_caracteristicas AS categoria,
           outras_sugestoes AS descricao, status_sugestao, resposta_gestor, data_envio
    FROM sugestoes_usuario
    WHERE id_usuario = ?
    ORDER BY data_envio DESC
");
$stmt->execute([$usuario_id]);
$minhas_sugestoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$labels_status = [
    'recebida'    => ['label' => 'Recebida',    'classe' => 'status-recebida'],
    'em_analise'  => ['label' => 'Em análise',  'classe' => 'status-analise'],
    'aprovada'    => ['label' => 'Aprovada',    'classe' => 'status-aprovada'],
    'rejeitada'   => ['label' => 'Rejeitada',   'classe' => 'status-rejeitada'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sugestões — Penomato</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
    <style>
        body {
            background: var(--cinza-100);
            min-height: 100vh;
            padding: var(--esp-8) var(--esp-5);
        }

        .container {
            max-width: 720px;
            margin: 0 auto;
        }

        .page-header {
            background: var(--cor-primaria);
            color: var(--branco);
            padding: var(--esp-6) var(--esp-8);
            border-radius: var(--raio-lg);
            margin-bottom: var(--esp-6);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: var(--esp-3);
        }
        .page-header h1 {
            font-size: var(--texto-xl);
            font-weight: var(--peso-semi);
            color: var(--branco);
        }
        .page-header p {
            font-size: var(--texto-sm);
            opacity: 0.8;
            margin-top: var(--esp-1);
        }

        .card {
            background: var(--branco);
            border-radius: var(--raio-lg);
            padding: var(--esp-8);
            box-shadow: var(--sombra-md);
            margin-bottom: var(--esp-6);
        }

        .card h2 {
            font-size: var(--texto-lg);
            font-weight: var(--peso-semi);
            color: var(--cor-primaria);
            margin-bottom: var(--esp-6);
            padding-bottom: var(--esp-3);
            border-bottom: 2px solid var(--cinza-100);
            display: flex;
            align-items: center;
            gap: var(--esp-2);
        }

        .campo { margin-bottom: var(--esp-5); }
        .campo label {
            display: block;
            font-size: var(--texto-sm);
            font-weight: var(--peso-semi);
            color: var(--cinza-700);
            margin-bottom: var(--esp-2);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .campo select,
        .campo input[type="text"],
        .campo textarea {
            width: 100%;
            border: 1.5px solid var(--cinza-300);
            border-radius: var(--raio-md);
            padding: var(--esp-3) var(--esp-4);
            font-size: var(--texto-md);
            color: var(--cinza-900);
            background: var(--cinza-50);
            transition: border-color var(--transicao);
            font-family: inherit;
        }
        .campo select:focus,
        .campo input:focus,
        .campo textarea:focus {
            outline: none;
            border-color: var(--cor-primaria);
            background: var(--branco);
        }
        .campo textarea { resize: vertical; min-height: 120px; line-height: 1.6; }

        .hint {
            font-size: var(--texto-xs);
            color: var(--cinza-400);
            margin-top: var(--esp-1);
        }

        .acoes {
            display: flex;
            gap: var(--esp-3);
            justify-content: flex-end;
            margin-top: var(--esp-6);
        }

        /* ── Lista de sugestões anteriores ── */
        .sugestao-item {
            border: 1.5px solid var(--cinza-200);
            border-radius: var(--raio-md);
            padding: var(--esp-5);
            margin-bottom: var(--esp-4);
        }
        .sugestao-item:last-child { margin-bottom: 0; }

        .sugestao-topo {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: var(--esp-3);
            margin-bottom: var(--esp-2);
            flex-wrap: wrap;
        }

        .sugestao-assunto {
            font-weight: var(--peso-semi);
            color: var(--cinza-900);
            font-size: var(--texto-md);
        }
        .sugestao-categoria {
            font-size: var(--texto-xs);
            color: var(--cinza-500);
            margin-top: var(--esp-1);
        }
        .sugestao-descricao {
            font-size: var(--texto-sm);
            color: var(--cinza-700);
            margin-top: var(--esp-3);
            line-height: 1.6;
        }
        .sugestao-data {
            font-size: var(--texto-xs);
            color: var(--cinza-400);
            margin-top: var(--esp-3);
        }

        .resposta-gestor {
            background: var(--sucesso-fundo);
            border-left: 3px solid var(--sucesso-cor);
            padding: var(--esp-3) var(--esp-4);
            border-radius: 0 var(--raio-sm) var(--raio-sm) 0;
            margin-top: var(--esp-4);
            font-size: var(--texto-sm);
            color: var(--sucesso-texto);
        }
        .resposta-gestor strong {
            display: block;
            font-size: var(--texto-xs);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: var(--esp-1);
        }

        /* ── Badges de status ── */
        .badge-status {
            display: inline-block;
            padding: var(--esp-1) var(--esp-3);
            border-radius: var(--raio-pill);
            font-size: var(--texto-xs);
            font-weight: var(--peso-semi);
            white-space: nowrap;
        }
        .status-recebida { background: var(--cinza-200);  color: var(--cinza-700); }
        .status-analise  { background: #fef9c3;           color: #854d0e; }
        .status-aprovada { background: var(--sucesso-fundo); color: var(--sucesso-texto); }
        .status-rejeitada{ background: var(--perigo-fundo); color: var(--perigo-texto); }

        .vazia {
            text-align: center;
            padding: var(--esp-10) var(--esp-4);
            color: var(--cinza-400);
            font-size: var(--texto-sm);
        }
        .vazia i { font-size: 2rem; display: block; margin-bottom: var(--esp-3); }
    </style>
</head>
<body>
<div class="container">

    <div class="page-header">
        <div>
            <h1>💡 Sugestões</h1>
            <p>Envie ideias e feedbacks para a equipe gestora e de desenvolvimento</p>
        </div>
        <a href="/penomato_mvp/src/Views/entrar_colaborador.php" class="btn btn-outline-branco">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <?php if ($sucesso): ?>
        <div class="alerta--sucesso"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($sucesso); ?></div>
    <?php endif; ?>
    <?php if ($erro): ?>
        <div class="alerta--erro"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erro); ?></div>
    <?php endif; ?>

    <!-- Formulário -->
    <div class="card">
        <h2><i class="fas fa-lightbulb"></i> Nova Sugestão</h2>
        <form method="POST">

            <div class="campo">
                <label>Categoria</label>
                <select name="categoria">
                    <option value="">— Selecione uma categoria —</option>
                    <option value="Funcionalidade" <?php echo ($_POST['categoria'] ?? '') == 'Funcionalidade' ? 'selected' : ''; ?>>💻 Funcionalidade do sistema</option>
                    <option value="Interface" <?php echo ($_POST['categoria'] ?? '') == 'Interface' ? 'selected' : ''; ?>>🎨 Interface / Usabilidade</option>
                    <option value="Dados botânicos" <?php echo ($_POST['categoria'] ?? '') == 'Dados botânicos' ? 'selected' : ''; ?>>🌿 Dados botânicos</option>
                    <option value="Fluxo de trabalho" <?php echo ($_POST['categoria'] ?? '') == 'Fluxo de trabalho' ? 'selected' : ''; ?>>🔄 Fluxo de trabalho</option>
                    <option value="Outro" <?php echo ($_POST['categoria'] ?? '') == 'Outro' ? 'selected' : ''; ?>>📌 Outro</option>
                </select>
            </div>

            <div class="campo">
                <label>Assunto <span style="color:var(--perigo-cor)">*</span></label>
                <input type="text" name="assunto" placeholder="Ex: Adicionar campo de floração por mês"
                       value="<?php echo htmlspecialchars($_POST['assunto'] ?? ''); ?>" maxlength="255">
            </div>

            <div class="campo">
                <label>Descrição <span style="color:var(--perigo-cor)">*</span></label>
                <textarea name="descricao" placeholder="Descreva sua sugestão com detalhes. Quanto mais contexto, melhor para a equipe avaliar."><?php echo htmlspecialchars($_POST['descricao'] ?? ''); ?></textarea>
                <p class="hint">Seja específico: o que você gostaria de ver, por que seria útil, como poderia funcionar.</p>
            </div>

            <div class="acoes">
                <button type="reset" class="btn btn-secundario">
                    <i class="fas fa-times"></i> Limpar
                </button>
                <button type="submit" name="enviar_sugestao" class="btn btn-primario">
                    <i class="fas fa-paper-plane"></i> Enviar Sugestão
                </button>
            </div>
        </form>
    </div>

    <!-- Histórico -->
    <div class="card">
        <h2><i class="fas fa-history"></i> Minhas Sugestões</h2>

        <?php if (empty($minhas_sugestoes)): ?>
            <div class="vazia">
                <i class="far fa-comment-dots"></i>
                Você ainda não enviou nenhuma sugestão.
            </div>
        <?php else: ?>
            <?php foreach ($minhas_sugestoes as $s):
                $st = $labels_status[$s['status_sugestao']] ?? ['label' => $s['status_sugestao'], 'classe' => 'status-recebida'];
            ?>
            <div class="sugestao-item">
                <div class="sugestao-topo">
                    <div>
                        <div class="sugestao-assunto"><?php echo htmlspecialchars($s['assunto']); ?></div>
                        <?php if ($s['categoria']): ?>
                            <div class="sugestao-categoria"><?php echo htmlspecialchars($s['categoria']); ?></div>
                        <?php endif; ?>
                    </div>
                    <span class="badge-status <?php echo $st['classe']; ?>"><?php echo $st['label']; ?></span>
                </div>

                <div class="sugestao-descricao"><?php echo nl2br(htmlspecialchars($s['descricao'])); ?></div>
                <div class="sugestao-data"><i class="far fa-clock"></i> <?php echo date('d/m/Y \à\s H:i', strtotime($s['data_envio'])); ?></div>

                <?php if (!empty($s['resposta_gestor'])): ?>
                <div class="resposta-gestor">
                    <strong><i class="fas fa-reply"></i> Resposta da equipe</strong>
                    <?php echo nl2br(htmlspecialchars($s['resposta_gestor'])); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
