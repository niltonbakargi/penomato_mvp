<?php
// controlador_painel_revisor.php
// Local: C:\xampp\htdocs\penomato_mvp\src\Controladores\controlador_painel_revisor.php

// Iniciar sessão
session_start();

// Carregar configuração do banco
require_once __DIR__ . '/../../config/database.php';

class ControladorPainelRevisor {
    
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Página principal do painel
     */
    public function index() {
        // Verificar login
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /penomato_mvp/login.php');
            exit;
        }
        
        // Carregar a view
        include __DIR__ . '/../Visualizacoes/revisao/entrada_revisor.php';
    }
    
    /**
     * API: Listar espécies disponíveis para revisão
     */
    public function listarPendentesModal() {
        header('Content-Type: application/json');
        
        try {
            // Buscar espécies com status 'completo' e revisão 'aguardando'
            $sql = "SELECT 
                        id,
                        nome_cientifico,
                        nome_popular,
                        familia,
                        prioridade
                    FROM especies_administrativo 
                    WHERE status_caracteristicas = 'completo' 
                    AND status_revisao = 'aguardando'
                    ORDER BY 
                        CASE prioridade 
                            WHEN 'alta' THEN 1 
                            WHEN 'media' THEN 2 
                            WHEN 'baixa' THEN 3 
                        END
                    LIMIT 20";
            
            $result = $this->conn->query($sql);
            
            $especies = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $especies[] = $row;
                }
            }
            
            echo json_encode($especies);
            
        } catch (Exception $e) {
            echo json_encode(['erro' => $e->getMessage()]);
        }
    }
    
    /**
     * API: Iniciar nova revisão
     */
    public function iniciarRevisao() {
        header('Content-Type: application/json');
        
        $especie_id = $_POST['especie_id'] ?? 0;
        $usuario_id = $_SESSION['usuario_id'] ?? 0;
        
        if (!$especie_id || !$usuario_id) {
            echo json_encode(['erro' => 'Dados inválidos']);
            return;
        }
        
        // Atualizar status da espécie
        $sql = "UPDATE especies_administrativo 
                SET status_revisao = 'em_andamento', 
                    id_revisor_atual = ?,
                    data_revisao = CURDATE()
                WHERE id = ? 
                AND status_caracteristicas = 'completo' 
                AND status_revisao = 'aguardando'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ii', $usuario_id, $especie_id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode([
                'sucesso' => true, 
                'redirect' => "artigo_revisao.php?id=$especie_id"
            ]);
        } else {
            echo json_encode(['erro' => 'Não foi possível iniciar a revisão']);
        }
    }
}

// ROTEAMENTO SIMPLES
$controlador = new ControladorPainelRevisor();

if (isset($_GET['acao'])) {
    switch ($_GET['acao']) {
        case 'listar_pendentes_modal':
            $controlador->listarPendentesModal();
            break;
        case 'iniciar':
            $controlador->iniciarRevisao();
            break;
        default:
            $controlador->index();
    }
} else {
    $controlador->index();
}