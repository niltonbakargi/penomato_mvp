<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /penomato_mvp/src/Views/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
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
        'label' => 'Revisar Artigo',
        'desc'  => 'Revise e aprove artigos científicos antes da publicação.',
        'link'  => '#em-breve',
        'breve' => true,
    ],
];

// Permissões por subtipo
$permissoes = [
    'identificador' => ['dados_internet', 'confirmar', 'registrar_imagens'],
    'dev'           => ['dados_internet', 'dev_tools'],
    'especialista'  => ['dados_internet', 'confirmar', 'registrar_imagens', 'contestar', 'revisar_artigo'],
    'gestor'        => ['dados_internet', 'confirmar', 'registrar_imagens', 'contestar', 'revisar_artigo', 'dev_tools'],
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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f4f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
            color: #1e2e1e;
        }

        .header {
            background: #0b5e42;
            color: white;
            padding: 20px 40px;
            border-radius: 12px;
            margin-bottom: 16px;
            text-align: center;
            width: 100%;
            max-width: 640px;
        }
        .header h1 { font-size: 1.4em; font-weight: 600; }
        .header p  { font-size: 0.88em; opacity: 0.8; margin-top: 4px; }

        /* Badge de subtipo */
        .subtipo-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            padding: 3px 12px;
            font-size: 0.8em;
            margin-top: 8px;
            letter-spacing: 0.03em;
        }

        /* Stats rápidas */
        .stats-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            width: 100%;
            max-width: 640px;
        }
        .stat-chip {
            flex: 1;
            background: white;
            border-radius: 10px;
            padding: 12px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }
        .stat-chip .num { font-size: 1.5em; font-weight: 700; color: #0b5e42; line-height: 1; }
        .stat-chip .lbl { font-size: 0.72em; color: #999; margin-top: 3px; }

        /* Grade de botões */
        .btn-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            width: 100%;
            max-width: 640px;
        }

        .action-btn {
            background: white;
            border: 2px solid #0b5e42;
            border-radius: 12px;
            padding: 22px 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 600;
            color: #0b5e42;
            font-size: 0.9em;
            text-decoration: none;
            display: block;
            position: relative;
        }
        .action-btn:hover {
            background: #0b5e42;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(11,94,66,0.2);
        }
        .action-btn .icon { font-size: 1.8em; margin-bottom: 8px; display: block; }
        .action-btn .desc {
            font-size: 0.78em;
            font-weight: 400;
            opacity: 0.7;
            margin-top: 4px;
            line-height: 1.35;
        }

        .action-btn.em-breve {
            border-color: #ccc;
            color: #aaa;
            cursor: default;
        }
        .action-btn.em-breve:hover {
            background: white;
            color: #aaa;
            transform: none;
            box-shadow: none;
        }
        .badge-breve {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #e0e0e0;
            color: #888;
            font-size: 0.68em;
            padding: 2px 7px;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-sair {
            margin-top: 24px;
            background: none;
            border: none;
            color: #999;
            font-size: 0.9em;
            cursor: pointer;
            text-decoration: underline;
        }
        .btn-sair:hover { color: #dc3545; }

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
