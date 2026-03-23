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
$qtd_internet  = $pdo->prepare("SELECT COUNT(*) FROM especies_administrativo WHERE autor_dados_internet_id = ?");
$qtd_internet->execute([$usuario_id]);
$total_internet = $qtd_internet->fetchColumn();

$qtd_registrada = $pdo->prepare("SELECT COUNT(*) FROM especies_administrativo WHERE autor_registrada_id = ?");
$qtd_registrada->execute([$usuario_id]);
$total_registrada = $qtd_registrada->fetchColumn();

$qtd_revisada = $pdo->prepare("SELECT COUNT(*) FROM especies_administrativo WHERE autor_revisada_id = ?");
$qtd_revisada->execute([$usuario_id]);
$total_revisada = $qtd_revisada->fetchColumn();

// ================================================
// MAPA DE BOTÕES
// ================================================
$todos_botoes = [
    'dados_internet' => [
        'icon'  => '🌐',
        'label' => 'Dados da Internet',
        'desc'  => 'Importe dados científicos via JSON (Flora do Brasil, Lorenzi, etc.)',
        'link'  => '/penomato_mvp/src/Views/escolher_especie.php',
    ],
    'confirmar' => [
        'icon'  => '✅',
        'label' => 'Confirmar Identificação',
        'desc'  => 'Verifique e confirme as informações vindas da internet antes de registrá-las.',
        'link'  => '/penomato_mvp/src/Controllers/confirmar_caracteristicas.php',
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
];

// Permissões por subtipo
$permissoes = [
    'identificador' => ['dados_internet', 'confirmar', 'cadastrar_exemplar', 'registrar_imagens', 'sugestoes'],
    'dev'           => ['dados_internet', 'dev_tools', 'sugestoes'],
    'especialista'  => ['dados_internet', 'confirmar', 'cadastrar_exemplar', 'registrar_imagens', 'contestar', 'revisar_artigo', 'sugestoes'],
    'gestor'        => ['dados_internet', 'confirmar', 'cadastrar_exemplar', 'registrar_imagens', 'contestar', 'revisar_artigo', 'dev_tools', 'sugestoes'],
];

// Gestor (por categoria ou subtipo) acessa tudo
$tipo_usuario = $_SESSION['usuario_tipo'] ?? '';
if ($tipo_usuario === 'gestor' || $subtipo === 'gestor') {
    $chaves_visiveis = array_keys($todos_botoes);
} else {
    $chaves_visiveis = $permissoes[$subtipo] ?? ['dados_internet', 'confirmar', 'registrar_imagens'];
}

// Labels de exibição dos subtipos
$labels_subtipo = [
    'identificador' => 'Identificador',
    'dev'           => 'Desenvolvedor',
    'especialista'  => 'Especialista',
    'gestor'        => 'Gestor de Equipe',
];
$label_subtipo = $labels_subtipo[$subtipo] ?? ucfirst($subtipo ?: 'Colaborador');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Colaborador — Penomato</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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

        @media (max-width: 480px) {
            .btn-grid { grid-template-columns: 1fr; }
            .stats-bar { flex-wrap: wrap; }
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>🌿 Painel do Colaborador</h1>
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
        ?>
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
        <?php endforeach; ?>
    </div>

    <button class="btn-sair" onclick="window.location.href='/penomato_mvp/src/Controllers/auth/logout_controlador.php'">
        🚪 Sair
    </button>

</body>
</html>
