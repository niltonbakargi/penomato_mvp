<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

require_once __DIR__ . '/../../config/banco_de_dados.php';

$nome_usuario  = $_SESSION['usuario_nome']    ?? 'Colaborador';
$subtipo       = strtolower(trim($_SESSION['usuario_subtipo'] ?? ''));
$email_usuario = $_SESSION['usuario_email']   ?? '';
$usuario_id    = $_SESSION['usuario_id'];

// ================================================
// ESTATÍSTICAS PESSOAIS
// ================================================
$qtd_internet  = $pdo->prepare("SELECT COUNT(*) FROM especies_administrativo WHERE autor_dados_internet_id = ? AND status != 'sem_dados'");
$qtd_internet->execute([$usuario_id]);
$total_internet = $qtd_internet->fetchColumn();

$qtd_registrada = $pdo->prepare("SELECT COUNT(*) FROM especies_administrativo WHERE autor_registrada_id = ?");
$qtd_registrada->execute([$usuario_id]);
$total_registrada = $qtd_registrada->fetchColumn();

$qtd_revisada = $pdo->prepare("SELECT COUNT(*) FROM especies_administrativo WHERE autor_revisada_id = ?");
$qtd_revisada->execute([$usuario_id]);
$total_revisada = $qtd_revisada->fetchColumn();

// ================================================
// ESTADO DO MODAL DADOS DA INTERNET
// ================================================
$modal_aberto     = isset($_GET['modal']) && $_GET['modal'] === 'dados_internet';
$modal_especie_id = (int)($_GET['especie_id'] ?? 0);

$especie_modal    = null;
$tem_imagens      = false;
$tem_morfologicos = false;

if ($modal_especie_id > 0) {
    $stmt_modal = $pdo->prepare("SELECT nome_cientifico, status FROM especies_administrativo WHERE id = ?");
    $stmt_modal->execute([$modal_especie_id]);
    $especie_modal = $stmt_modal->fetch();
    if ($especie_modal) {
        $status_modal     = $especie_modal['status'];
        $tem_imagens      = in_array($status_modal, ['dados_internet', 'descrita', 'registrada', 'publicada']);
        $tem_morfologicos = in_array($status_modal, ['descrita', 'registrada', 'publicada']);
    }
}

// ================================================
// MAPA DE BOTÕES
// ================================================
$todos_botoes = [
    'dados_internet' => [
        'icon'  => '🌐',
        'label' => 'Dados da Internet',
        'desc'  => 'Insira imagens ou dados morfológicos via internet.',
        'modal' => true,
    ],
    'cadastrar_exemplar' => [
        'icon'  => '🌿',
        'label' => 'Cadastrar Exemplar',
        'desc'  => 'Registre um indivíduo de campo com localização e foto antes de enviar fotos das partes.',
        'link'  => '/penomato_mvp/src/Views/cadastrar_exemplar.php',
    ],
    'registrar_imagens' => [
        'icon'  => '📷',
        'label' => 'Registrar Imagens',
        'desc'  => 'Envie exsicatas digitais e fotos de habitat para o acervo científico.',
        'link'  => '/penomato_mvp/src/Views/enviar_imagem.php',
    ],
    'contestar' => [
        'icon'  => '⚠️',
        'label' => 'Contestar Informação',
        'desc'  => 'Sinalize inconsistências ou sugira correções em dados existentes.',
        'link'  => '/penomato_mvp/src/Controllers/contestar_informacao.php',
    ],
    'dev_tools' => [
        'icon'  => '⚙️',
        'label' => 'Ferramentas DEV',
        'desc'  => 'Kanban do projeto, backlog e histórico de desenvolvimento.',
        'link'  => '/penomato_mvp/penomato_kanban.html',
    ],
    'revisar_artigo' => [
        'icon'  => '📝',
        'label' => 'Revisar Artigos',
        'desc'  => 'Revise artigos gerados automaticamente — fila por status (rascunho → revisão → aprovado).',
        'link'  => '/penomato_mvp/src/Controllers/artigos_fila.php',
    ],
    'sugestoes' => [
        'icon'  => '💡',
        'label' => 'Sugestões',
        'desc'  => 'Envie ideias e feedbacks para a equipe gestora e de desenvolvimento.',
        'link'  => '/penomato_mvp/src/Views/sugestoes.php',
    ],
    'minhas_acoes' => [
        'icon'  => '↩',
        'label' => 'Minhas Ações',
        'desc'  => 'Desfaça ações em até 24h. Após o prazo, solicite ao gestor com justificativa.',
        'link'  => '/penomato_mvp/src/Controllers/minhas_acoes.php',
    ],
];

// Permissões por subtipo
$permissoes = [
    'identificador' => ['dados_internet', 'cadastrar_exemplar', 'registrar_imagens', 'contestar', 'sugestoes', 'minhas_acoes'],
    'dev'           => ['dados_internet', 'cadastrar_exemplar', 'registrar_imagens', 'contestar', 'dev_tools', 'sugestoes', 'minhas_acoes'],
    'especialista'  => ['dados_internet', 'cadastrar_exemplar', 'registrar_imagens', 'contestar', 'revisar_artigo', 'sugestoes', 'minhas_acoes'],
    'gestor'        => ['dados_internet', 'cadastrar_exemplar', 'registrar_imagens', 'contestar', 'revisar_artigo', 'dev_tools', 'sugestoes', 'minhas_acoes'],
];

// Gestor (por categoria ou subtipo) acessa tudo
$tipo_usuario = $_SESSION['usuario_tipo'] ?? '';
if ($tipo_usuario === 'gestor' || $subtipo === 'gestor') {
    $chaves_visiveis = array_keys($todos_botoes);
} else {
    $chaves_visiveis = $permissoes[$subtipo] ?? ['dados_internet', 'registrar_imagens'];
}

// Labels de exibição dos subtipos
$labels_subtipo = [
    'identificador' => 'Identificador',
    'dev'           => 'Desenvolvedor',
    'especialista'  => 'Especialista',
    'gestor'        => 'Gestor de Equipe',
];
$label_subtipo = $labels_subtipo[$subtipo] ?? ucfirst($subtipo ?: 'Colaborador');

$titulos_painel = [
    'identificador' => 'Painel do Identificador',
    'dev'           => 'Painel do Desenvolvedor',
    'especialista'  => 'Painel do Especialista',
    'gestor'        => 'Painel do Gestor',
];
$titulo_painel = $titulos_painel[$subtipo] ?? 'Painel do Colaborador';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_painel; ?> — Penomato</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha384-blOohCVdhjmtROpu8+CfTnUWham9nkX7P7OZQMst+RUnhtoY/9qemFAkIKOYxDI3" crossorigin="anonymous">
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
    <style>
        body {
            background: var(--cinza-100);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: var(--esp-8) var(--esp-5);
        }

        .header {
            background: var(--cor-primaria);
            color: var(--branco);
            padding: var(--esp-5) var(--esp-10);
            border-radius: var(--raio-lg);
            margin-bottom: var(--esp-4);
            text-align: center;
            width: 100%;
            max-width: 640px;
        }
        .header h1 { font-size: var(--texto-lg); font-weight: var(--peso-semi); color: var(--branco); }
        .header p  { font-size: var(--texto-sm); opacity: 0.8; margin-top: var(--esp-1); }

        .subtipo-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            border-radius: var(--raio-full);
            padding: var(--esp-1) var(--esp-3);
            font-size: var(--texto-xs);
            margin-top: var(--esp-2);
            letter-spacing: 0.03em;
        }

        .stats-bar {
            display: flex;
            gap: var(--esp-2);
            margin-bottom: var(--esp-5);
            width: 100%;
            max-width: 640px;
        }
        .stat-chip {
            flex: 1;
            background: var(--branco);
            border-radius: var(--raio-md);
            padding: var(--esp-3);
            text-align: center;
            box-shadow: var(--sombra-sm);
        }
        .stat-chip .num { font-size: var(--texto-2xl); font-weight: var(--peso-bold); color: var(--cor-primaria); line-height: 1; }
        .stat-chip .lbl { font-size: var(--texto-xs); color: var(--cinza-400); margin-top: var(--esp-1); }

        .btn-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--esp-4);
            width: 100%;
            max-width: 640px;
        }

        .action-btn {
            background: var(--branco);
            border: 2px solid var(--cor-primaria);
            border-radius: var(--raio-lg);
            padding: var(--esp-6) var(--esp-4);
            text-align: center;
            cursor: pointer;
            transition: var(--transicao);
            font-weight: var(--peso-semi);
            color: var(--cor-primaria);
            font-size: var(--texto-sm);
            text-decoration: none;
            display: block;
            position: relative;
        }
        .action-btn:hover {
            background: var(--cor-primaria);
            color: var(--branco);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(11,94,66,0.2);
        }
        .action-btn .icon { font-size: var(--texto-2xl); margin-bottom: var(--esp-2); display: block; }
        .action-btn .desc {
            font-size: var(--texto-xs);
            font-weight: var(--peso-normal);
            opacity: 0.7;
            margin-top: var(--esp-1);
            line-height: 1.35;
        }

        .action-btn.em-breve {
            border-color: var(--cinza-300);
            color: var(--cinza-400);
            cursor: default;
        }
        .action-btn.em-breve:hover {
            background: var(--branco);
            color: var(--cinza-400);
            transform: none;
            box-shadow: none;
        }
        .badge-breve {
            position: absolute;
            top: var(--esp-2);
            right: var(--esp-2);
            background: var(--cinza-200);
            color: var(--cinza-500);
            font-size: var(--texto-xs);
            padding: var(--esp-1) var(--esp-2);
            border-radius: var(--raio-sm);
            font-weight: var(--peso-semi);
        }

        .btn-sair {
            margin-top: var(--esp-6);
            background: none;
            border: none;
            color: var(--cinza-400);
            font-size: var(--texto-sm);
            cursor: pointer;
            text-decoration: underline;
        }
        .btn-sair:hover { color: var(--perigo-cor); }

        /* ── Modal Dados da Internet ── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.55);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: var(--esp-5);
        }
        .modal-overlay.aberto { display: flex; }

        .modal-dados {
            background: var(--branco);
            border-radius: var(--raio-lg);
            box-shadow: var(--sombra-lg);
            width: 100%;
            max-width: 480px;
            padding: var(--esp-8);
        }
        .modal-dados h2 {
            color: var(--cor-primaria);
            font-size: var(--texto-lg);
            font-weight: var(--peso-semi);
            margin-bottom: var(--esp-2);
        }
        .modal-subtitulo {
            font-size: var(--texto-sm);
            color: var(--cinza-500);
            margin-bottom: var(--esp-6);
        }
        .modal-especie-nome {
            font-size: var(--texto-sm);
            color: var(--cor-primaria);
            font-weight: var(--peso-semi);
            font-style: italic;
            background: var(--verde-50);
            border-radius: var(--raio-md);
            padding: var(--esp-2) var(--esp-4);
            margin-bottom: var(--esp-6);
        }

        .sub-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--esp-4);
        }
        .sub-card {
            background: var(--cinza-50);
            border: 2px solid var(--cor-primaria);
            border-radius: var(--raio-lg);
            padding: var(--esp-6) var(--esp-4);
            text-align: center;
            text-decoration: none;
            color: var(--cor-primaria);
            font-weight: var(--peso-semi);
            font-size: var(--texto-sm);
            transition: var(--transicao);
            display: block;
            position: relative;
        }
        .sub-card:hover {
            background: var(--cor-primaria);
            color: var(--branco);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(11,94,66,0.2);
        }
        .sub-card.bloqueado {
            border-color: var(--cinza-300);
            color: var(--cinza-400);
            cursor: not-allowed;
            background: var(--cinza-100);
        }
        .sub-card.bloqueado:hover {
            background: var(--cinza-100);
            color: var(--cinza-400);
            transform: none;
            box-shadow: none;
        }
        .sub-card.concluido {
            border-color: var(--sucesso-cor);
            background: var(--verde-50);
            color: var(--sucesso-cor);
        }
        .sub-card.concluido:hover {
            background: var(--sucesso-cor);
            color: var(--branco);
        }
        .sub-card .icon {
            font-size: var(--texto-2xl);
            display: block;
            margin-bottom: var(--esp-2);
        }
        .sub-card .desc {
            font-size: var(--texto-xs);
            font-weight: var(--peso-normal);
            opacity: 0.7;
            margin-top: var(--esp-1);
            line-height: 1.35;
        }
        .badge-check {
            position: absolute;
            top: var(--esp-2);
            right: var(--esp-2);
            font-size: var(--texto-sm);
            line-height: 1;
        }
        .badge-lock {
            position: absolute;
            top: var(--esp-2);
            right: var(--esp-2);
            font-size: var(--texto-sm);
            color: var(--cinza-400);
            line-height: 1;
        }

        /* ── Banner de conclusão ── */
        .modal-concluido {
            margin-top: var(--esp-5);
            background: var(--verde-50);
            border: 2px solid var(--sucesso-cor);
            border-radius: var(--raio-lg);
            padding: var(--esp-5);
            text-align: center;
        }
        .modal-concluido .concluido-icon { font-size: var(--texto-3xl); }
        .modal-concluido .concluido-text {
            font-size: var(--texto-sm);
            color: var(--sucesso-cor);
            font-weight: var(--peso-semi);
            margin: var(--esp-2) 0 var(--esp-4);
        }
        .btn-salvar-dados {
            display: inline-block;
            background: var(--sucesso-cor);
            color: var(--branco);
            padding: var(--esp-3) var(--esp-8);
            border-radius: var(--raio-full);
            font-weight: var(--peso-semi);
            font-size: var(--texto-sm);
            text-decoration: none;
            transition: var(--transicao);
        }
        .btn-salvar-dados:hover {
            background: var(--cor-primaria);
            transform: translateY(-1px);
        }

        .btn-fechar-modal {
            display: block;
            margin-top: var(--esp-5);
            text-align: center;
            background: none;
            border: none;
            color: var(--cinza-400);
            font-size: var(--texto-sm);
            cursor: pointer;
            text-decoration: underline;
            width: 100%;
        }
        .btn-fechar-modal:hover { color: var(--perigo-cor); }

        @media (max-width: 480px) {
            .btn-grid { grid-template-columns: 1fr; }
            .stats-bar { flex-wrap: wrap; }
            .sub-cards { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <?php if ($tipo_usuario === 'gestor' || $subtipo === 'gestor'): ?>
        <a href="/penomato_mvp/src/Controllers/controlador_gestor.php"
           style="display:inline-block;margin-bottom:12px;font-size:.85rem;color:var(--cor-primaria);text-decoration:none;font-weight:600;">
            ← Voltar ao painel do gestor
        </a>
    <?php endif; ?>

    <div class="header">
        <h1>🌿 <?php echo $titulo_painel; ?></h1>
        <p><?php echo htmlspecialchars($nome_usuario); ?></p>
        <?php if ($subtipo): ?>
            <span class="subtipo-badge"><?php echo htmlspecialchars($label_subtipo); ?></span>
        <?php endif; ?>
    </div>

    <!-- Stats pessoais -->
    <div class="stats-bar">
        <div class="stat-chip">
            <div class="num"><?php echo $total_internet; ?></div>
            <div class="lbl">Dados internet</div>
        </div>
        <div class="stat-chip">
            <div class="num"><?php echo $total_registrada; ?></div>
            <div class="lbl">Registradas</div>
        </div>
        <div class="stat-chip">
            <div class="num"><?php echo $total_revisada; ?></div>
            <div class="lbl">Revisadas</div>
        </div>
    </div>

    <!-- Botões conforme subtipo -->
    <div class="btn-grid">
        <?php foreach ($chaves_visiveis as $chave):
            $b = $todos_botoes[$chave];
            $breve = !empty($b['breve']);
            $modal = !empty($b['modal']);
        ?>
        <?php if ($modal): ?>
        <button type="button"
                class="action-btn"
                onclick="document.getElementById('modalDadosInternet').classList.add('aberto')">
            <span class="icon"><?php echo $b['icon']; ?></span>
            <?php echo htmlspecialchars($b['label']); ?>
            <div class="desc"><?php echo htmlspecialchars($b['desc']); ?></div>
        </button>
        <?php else: ?>
        <a href="<?php echo $breve ? '#' : $b['link']; ?>"
           class="action-btn<?php echo $breve ? ' em-breve' : ''; ?>"
           <?php echo $breve ? 'onclick="return false;"' : ''; ?>>
            <?php if ($breve): ?>
                <span class="badge-breve">Em breve</span>
            <?php endif; ?>
            <span class="icon"><?php echo $b['icon']; ?></span>
            <?php echo htmlspecialchars($b['label']); ?>
            <div class="desc"><?php echo htmlspecialchars($b['desc']); ?></div>
        </a>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <button class="btn-sair" onclick="window.location.href='/penomato_mvp/src/Controllers/auth/logout_controlador.php'">
        🚪 Sair
    </button>

    <!-- ── Modal: Dados da Internet ── -->
    <div class="modal-overlay" id="modalDadosInternet">
        <div class="modal-dados">
            <h2>🌐 Dados da Internet</h2>

            <?php if ($especie_modal): ?>
                <p class="modal-especie-nome">
                    📌 <?php echo htmlspecialchars($especie_modal['nome_cientifico']); ?>
                </p>
            <?php else: ?>
                <p class="modal-subtitulo">Escolha o que deseja inserir para esta espécie.</p>
            <?php endif; ?>

            <div class="sub-cards">

                <!-- Card Imagens: sempre disponível -->
                <a href="/penomato_mvp/src/Views/escolher_especie.php"
                   class="sub-card<?php echo $tem_imagens ? ' concluido' : ''; ?>">
                    <?php if ($tem_imagens): ?>
                        <span class="badge-check">✅</span>
                    <?php endif; ?>
                    <span class="icon">🖼️</span>
                    Imagens
                    <div class="desc">Importe e organize fotos da espécie via internet.</div>
                </a>

                <!-- Card Dados Morfológicos: bloqueado até ter imagens -->
                <?php
                $link_morf = '/penomato_mvp/src/Controllers/confirmar_caracteristicas.php?modo=confirmar';
                if ($modal_especie_id > 0 && $tem_imagens) {
                    $link_morf .= '&especie_id=' . $modal_especie_id;
                }
                $morf_bloqueado = !$tem_imagens;
                $morf_classes   = 'sub-card' . ($tem_morfologicos ? ' concluido' : ($morf_bloqueado ? ' bloqueado' : ''));
                ?>
                <a href="<?php echo $morf_bloqueado ? '#' : $link_morf; ?>"
                   class="<?php echo $morf_classes; ?>"
                   <?php echo $morf_bloqueado ? 'onclick="return false;" title="Insira as imagens primeiro"' : ''; ?>>
                    <?php if ($tem_morfologicos): ?>
                        <span class="badge-check">✅</span>
                    <?php elseif ($morf_bloqueado): ?>
                        <span class="badge-lock">🔒</span>
                    <?php endif; ?>
                    <span class="icon">📋</span>
                    Dados Morfológicos
                    <div class="desc">
                        <?php if ($morf_bloqueado): ?>
                            Disponível após inserir as imagens.
                        <?php else: ?>
                            Insira características botânicas com base em fontes científicas.
                        <?php endif; ?>
                    </div>
                </a>

            </div>

            <?php if ($tem_morfologicos): ?>
            <!-- Ambos concluídos -->
            <div class="modal-concluido">
                <div class="concluido-icon">🎉</div>
                <div class="concluido-text">Dados completos! Tudo salvo com sucesso.</div>
                <a href="/penomato_mvp/src/Views/entrar_colaborador.php" class="btn-salvar-dados">
                    Concluir
                </a>
            </div>
            <?php endif; ?>

            <button class="btn-fechar-modal"
                    onclick="document.getElementById('modalDadosInternet').classList.remove('aberto')">
                Cancelar
            </button>
        </div>
    </div>

    <script>
    // Auto-abrir modal quando redirecionado de volta ao painel
    <?php if ($modal_aberto): ?>
    document.getElementById('modalDadosInternet').classList.add('aberto');
    <?php endif; ?>

    // Fechar modal clicando fora
    document.getElementById('modalDadosInternet').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('aberto');
    });
    </script>

</body>
</html>
