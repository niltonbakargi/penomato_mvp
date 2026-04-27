<?php
// controlador_painel_revisor.php
// MVP - Versão simplificada e integrada

// Iniciar sessão
session_start();

// Carregar configuração do banco (PDO)
require_once __DIR__ . '/../../config/banco_de_dados.php';
require_once __DIR__ . '/../helpers/gerador_artigo.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/index.php');
    exit;
}

// Guardar dados do usuário logado
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Revisor';
$usuario_instituicao = $_SESSION['usuario_instituicao'] ?? '';
$usuario_tipo = $_SESSION['usuario_tipo'] ?? '';

// Processar ações via GET/POST
$acao = $_GET['acao'] ?? '';

// ============================================
// PROCESSAR DECISÃO DE REVISÃO (APROVAR / CONTESTAR)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['acao'] ?? '', ['aprovar', 'contestar'])) {

    require_once __DIR__ . '/../../config/email.php';

    $acao_decisao = $_POST['acao'];
    $especie_id   = (int)($_POST['especie_id'] ?? 0);
    $motivo       = trim($_POST['motivo'] ?? '');
    $usuario_id   = (int)$_SESSION['usuario_id'];

    $url_revisao = APP_BASE . '/src/Views/artigo_revisao.php?id=' . $especie_id;
    $url_painel  = APP_BASE . '/src/Controllers/controlador_painel_revisor.php';

    if (!$especie_id) {
        header('Location: ' . $url_painel . '?erro=' . urlencode('ID de espécie inválido.'));
        exit;
    }

    if ($acao_decisao === 'contestar' && $motivo === '') {
        header('Location: ' . $url_revisao . '&erro=' . urlencode('O motivo é obrigatório para contestar.'));
        exit;
    }

    try {
        // Busca a espécie e o colaborador autor — verifica que o artigo está em 'revisando'
        $stmt = $pdo->prepare("
            SELECT e.nome_cientifico, e.autor_dados_internet_id,
                   u.nome AS colaborador_nome, u.email AS colaborador_email
            FROM especies_administrativo e
            INNER JOIN artigos a ON a.especie_id = e.id AND a.status = 'revisando'
            LEFT JOIN  usuarios u ON u.id = e.autor_dados_internet_id
            WHERE e.id = ?
        ");
        $stmt->execute([$especie_id]);
        $especie = $stmt->fetch();

        if (!$especie) {
            header('Location: ' . $url_painel . '?erro=' . urlencode('Artigo não encontrado ou não está em revisão.'));
            exit;
        }

        if ($acao_decisao === 'aprovar') {

            // Artigo avança para 'revisado'
            $pdo->prepare("
                UPDATE artigos
                SET status      = 'revisado',
                    data_revisado = NOW(),
                    revisado_por  = ?,
                    atualizado_em = NOW()
                WHERE especie_id = ? AND status = 'revisando'
            ")->execute([$usuario_id, $especie_id]);

            // Espécie: registra data e autor da revisão
            $pdo->prepare("
                UPDATE especies_administrativo
                SET data_revisada        = NOW(),
                    autor_revisada_id    = ?,
                    observacoes_revisao  = ?,
                    data_ultima_atualizacao = NOW()
                WHERE id = ?
            ")->execute([$usuario_id, $motivo ?: null, $especie_id]);

            // Histórico
            $pdo->prepare("
                INSERT INTO historico_alteracoes
                    (especie_id, id_usuario, tabela_afetada, campo_alterado, valor_anterior, valor_novo, tipo_acao)
                VALUES (?, ?, 'artigos', 'status', 'revisando', 'revisado', 'revisao')
            ")->execute([$especie_id, $usuario_id]);

            if (!empty($especie['colaborador_email'])) {
                $corpo = "<p>Olá, <strong>" . htmlspecialchars($especie['colaborador_nome']) . "</strong>!</p>
                    <p>O artigo da espécie <em>" . htmlspecialchars($especie['nome_cientifico']) . "</em>
                    foi <strong style='color:#0b5e42;'>APROVADO</strong> pelo especialista e está aguardando publicação.</p>"
                    . ($motivo ? "<p><strong>Observações:</strong> " . htmlspecialchars($motivo) . "</p>" : "");
                enviarEmail(
                    $especie['colaborador_email'],
                    'Artigo aprovado — Penomato',
                    templateEmail('Artigo aprovado pelo especialista', $corpo)
                );
            }

            regenerarArtigoEspecie($pdo, $especie_id);

            header('Location: ' . $url_painel . '?sucesso=' . urlencode('"' . $especie['nome_cientifico'] . '" revisada e aprovada!'));

        } else {

            // Artigo volta para 'registrado' — colaborador precisa corrigir e reenviar
            $pdo->prepare("
                UPDATE artigos
                SET status        = 'registrado',
                    atualizado_em = NOW()
                WHERE especie_id = ? AND status = 'revisando'
            ")->execute([$especie_id]);

            // Espécie: registra contestação
            $pdo->prepare("
                UPDATE especies_administrativo
                SET status               = 'contestado',
                    data_contestado      = NOW(),
                    autor_contestado_id  = ?,
                    motivo_contestado    = ?,
                    data_ultima_atualizacao = NOW()
                WHERE id = ?
            ")->execute([$usuario_id, $motivo, $especie_id]);

            // Histórico
            $pdo->prepare("
                INSERT INTO historico_alteracoes
                    (especie_id, id_usuario, tabela_afetada, campo_alterado, valor_anterior, valor_novo, tipo_acao)
                VALUES (?, ?, 'artigos', 'status', 'revisando', 'registrado', 'contestacao')
            ")->execute([$especie_id, $usuario_id]);

            if (!empty($especie['colaborador_email'])) {
                $corpo = "<p>Olá, <strong>" . htmlspecialchars($especie['colaborador_nome']) . "</strong>!</p>
                    <p>O artigo da espécie <em>" . htmlspecialchars($especie['nome_cientifico']) . "</em>
                    foi <strong style='color:#c0392b;'>CONTESTADO</strong> pelo especialista.</p>
                    <p><strong>Motivo:</strong> " . htmlspecialchars($motivo) . "</p>
                    <p>Por favor, corrija os dados ou imagens e o artigo voltará para revisão automaticamente.</p>";
                enviarEmail(
                    $especie['colaborador_email'],
                    'Artigo contestado — Penomato',
                    templateEmail('Artigo requer correções', $corpo)
                );
            }

            header('Location: ' . $url_painel . '?sucesso=' . urlencode('"' . $especie['nome_cientifico'] . '" contestada. Colaborador notificado.'));
        }

    } catch (Exception $e) {
        error_log('Erro na decisão de revisão: ' . $e->getMessage());
        header('Location: ' . $url_revisao . '?erro=' . urlencode('Erro interno. Tente novamente.'));
    }
    exit;
}

// ============================================
// PUBLICAR ARTIGO (apenas gestor)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'publicar') {

    if ($usuario_tipo !== 'gestor') {
        header('Location: ' . APP_BASE . '/src/Controllers/artigos_fila.php?erro=' . urlencode('Apenas gestores podem publicar artigos.'));
        exit;
    }

    require_once __DIR__ . '/../../config/email.php';

    $especie_id = (int)($_POST['especie_id'] ?? 0);
    $url_fila   = APP_BASE . '/src/Controllers/artigos_fila.php';

    if (!$especie_id) {
        header('Location: ' . $url_fila . '?erro=' . urlencode('ID inválido.'));
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT e.nome_cientifico, e.autor_dados_internet_id,
                   u.nome AS colaborador_nome, u.email AS colaborador_email
            FROM especies_administrativo e
            INNER JOIN artigos a ON a.especie_id = e.id AND a.status = 'revisado'
            LEFT JOIN  usuarios u ON u.id = e.autor_dados_internet_id
            WHERE e.id = ?
        ");
        $stmt->execute([$especie_id]);
        $especie = $stmt->fetch();

        if (!$especie) {
            header('Location: ' . $url_fila . '?erro=' . urlencode('Artigo não encontrado ou não está revisado.'));
            exit;
        }

        $usuario_id = (int)$_SESSION['usuario_id'];

        $pdo->prepare("
            UPDATE artigos
            SET status = 'publicado', atualizado_em = NOW()
            WHERE especie_id = ? AND status = 'revisado'
        ")->execute([$especie_id]);

        $pdo->prepare("
            UPDATE especies_administrativo
            SET status              = 'publicado',
                data_publicado      = NOW(),
                autor_publicado_id  = ?,
                data_ultima_atualizacao = NOW()
            WHERE id = ?
        ")->execute([$usuario_id, $especie_id]);

        $pdo->prepare("
            INSERT INTO historico_alteracoes
                (especie_id, id_usuario, tabela_afetada, campo_alterado, valor_anterior, valor_novo, tipo_acao)
            VALUES (?, ?, 'artigos', 'status', 'revisado', 'publicado', 'publicacao')
        ")->execute([$especie_id, $usuario_id]);

        if (!empty($especie['colaborador_email'])) {
            $corpo = "<p>Olá, <strong>" . htmlspecialchars($especie['colaborador_nome']) . "</strong>!</p>
                <p>O artigo da espécie <em>" . htmlspecialchars($especie['nome_cientifico']) . "</em>
                foi <strong style='color:#1e40af;'>PUBLICADO</strong> no Penomato.</p>
                <p>Os dados já estão disponíveis publicamente na plataforma.</p>";
            enviarEmail(
                $especie['colaborador_email'],
                'Artigo publicado — Penomato',
                templateEmail('Artigo publicado com sucesso', $corpo)
            );
        }

        regenerarArtigoEspecie($pdo, $especie_id);

        header('Location: ' . $url_fila . '?sucesso=' . urlencode('"' . $especie['nome_cientifico'] . '" publicada com sucesso!'));

    } catch (Exception $e) {
        error_log('Erro ao publicar artigo: ' . $e->getMessage());
        header('Location: ' . $url_fila . '?erro=' . urlencode('Erro interno. Tente novamente.'));
    }
    exit;
}

// ============================================
// API: Listar espécies pendentes (para o modal)
// ============================================
if ($acao === 'listar_pendentes') {
    header('Content-Type: application/json');
    
    try {
        $sql = "SELECT 
                    e.id,
                    e.nome_cientifico,
                    e.prioridade,
                    (SELECT nome_popular FROM especies_caracteristicas WHERE especie_id = e.id LIMIT 1) as nome_popular
                FROM especies_administrativo e
                WHERE e.status = 'registrada' 
                AND e.data_revisada IS NULL
                ORDER BY 
                    CASE e.prioridade 
                        WHEN 'urgente' THEN 1
                        WHEN 'alta' THEN 2 
                        WHEN 'media' THEN 3 
                        WHEN 'baixa' THEN 4
                        ELSE 5
                    END,
                    e.nome_cientifico
                LIMIT 30";
        
        $stmt = $pdo->query($sql);
        $especies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($especies);
        
    } catch (Exception $e) {
        echo json_encode(['erro' => 'Erro ao carregar espécies']);
    }
    exit;
}

// ============================================
// API: Iniciar nova revisão
// ============================================
if ($acao === 'iniciar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $especie_id = $_POST['especie_id'] ?? 0;
    $usuario_id = $_SESSION['usuario_id'] ?? 0;
    
    if (!$especie_id || !$usuario_id) {
        echo json_encode(['erro' => 'Dados inválidos']);
        exit;
    }
    
    try {
        // Verificar se ainda está disponível
        $check = $pdo->prepare("SELECT id FROM especies_administrativo 
                               WHERE id = ? AND status = 'registrada'");
        $check->execute([$especie_id]);
        
        if ($check->rowCount() === 0) {
            echo json_encode(['erro' => 'Espécie não disponível']);
            exit;
        }
        
        // Mudar status para 'em_revisao'
        $sql = "UPDATE especies_administrativo 
                SET status = 'em_revisao',
                    data_ultima_atualizacao = NOW()
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$especie_id]);
        
        echo json_encode([
            'sucesso' => true,
            'redirect' => '/penomato_mvp/src/Views/artigo_revisao.php?id=' . $especie_id
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['erro' => 'Erro ao iniciar revisão']);
    }
    exit;
}

// ============================================
// API: Listar revisões em andamento (CONTINUAR)
// ============================================
if ($acao === 'listar_andamento') {
    header('Content-Type: application/json');
    
    $usuario_id = $_SESSION['usuario_id'] ?? 0;
    
    try {
        // Buscar espécies em revisão pelo usuário atual
        // Como não temos campo específico, buscamos por status 'em_revisao'
        // e ordenamos pelas mais recentes
        $sql = "SELECT 
                    e.id,
                    e.nome_cientifico,
                    e.prioridade,
                    e.data_ultima_atualizacao as data_inicio,
                    (SELECT nome_popular FROM especies_caracteristicas WHERE especie_id = e.id LIMIT 1) as nome_popular
                FROM especies_administrativo e
                WHERE e.status = 'em_revisao'
                ORDER BY e.data_ultima_atualizacao DESC
                LIMIT 20";
        
        $stmt = $pdo->query($sql);
        $revisoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($revisoes);
        
    } catch (Exception $e) {
        echo json_encode(['erro' => 'Erro ao carregar revisões']);
    }
    exit;
}

// ============================================
// PÁGINA PRINCIPAL - Carregar a View
// ============================================
include __DIR__ . '/../Views/entrada_revisor.php';
?>