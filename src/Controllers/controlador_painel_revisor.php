<?php
// controlador_painel_revisor.php
// MVP - Versão simplificada e integrada

// Iniciar sessão
session_start();

// Carregar configuração do banco (PDO)
require_once __DIR__ . '/../../config/banco_de_dados.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /penomato_mvp/index.php');
    exit;
}

// Guardar dados do usuário logado
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Revisor';
$usuario_instituicao = $_SESSION['usuario_instituicao'] ?? '';

// Processar ações via GET/POST
$acao = $_GET['acao'] ?? '';

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