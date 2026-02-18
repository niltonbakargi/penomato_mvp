<?php
// controlador_gestor.php
// MVP - Painel do gestor com navegação entre perfis

session_start();

// Carregar configuração do banco
require_once __DIR__ . '/../../config/banco_de_dados.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /penomato_mvp/src/Views/auth/login.php');
    exit;
}

// Dados do usuário logado
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Gestor';
$usuario_instituicao = $_SESSION['usuario_instituicao'] ?? 'Penomato';

// Buscar estatísticas básicas
try {
    // Total de espécies
    $stmt = $pdo->query("SELECT COUNT(*) FROM especies_administrativo");
    $total_especies = $stmt->fetchColumn();
    
    // Espécies em revisão
    $stmt = $pdo->query("SELECT COUNT(*) FROM especies_administrativo WHERE status = 'em_revisao'");
    $em_revisao = $stmt->fetchColumn();
    
    // Espécies validadas
    $stmt = $pdo->query("SELECT COUNT(*) FROM especies_administrativo WHERE status IN ('revisada', 'publicado')");
    $validadas = $stmt->fetchColumn();
    
    // Total de usuários
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
    $total_usuarios = $stmt->fetchColumn();
    
} catch (Exception $e) {
    // Valores padrão em caso de erro
    $total_especies = 12;
    $em_revisao = 3;
    $validadas = 5;
    $total_usuarios = 8;
}

// Carregar a view
include __DIR__ . '/../Views/entrada_gestor.php';
?>