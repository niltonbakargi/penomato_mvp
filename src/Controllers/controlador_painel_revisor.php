<?php
// controlador_painel_revisor.php
// Local: C:\xampp\htdocs\penomato_mvp\src\Controllers\controlador_painel_revisor.php
// VERSÃO ATUALIZADA - 15/02/2026
// Adaptado para nova estrutura do banco com status único

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
        include __DIR__ . '/../Views/entrada_revisor.php';
    }
    
    /**
     * API: Listar espécies disponíveis para revisão
     * Agora usa: status = 'registrada' (pronto para revisão)
     */
    public function listarPendentesModal() {
        header('Content-Type: application/json');
        
        try {
            // Buscar espécies com status 'registrada' (prontas para revisão)
            // e que ainda não foram revisadas (data_revisada IS NULL)
            $sql = "SELECT 
                        id,
                        nome_cientifico,
                        (SELECT nome_popular FROM especies_caracteristicas WHERE especie_id = e.id LIMIT 1) as nome_popular,
                        (SELECT familia FROM especies_caracteristicas WHERE especie_id = e.id LIMIT 1) as familia,
                        prioridade
                    FROM especies_administrativo e
                    WHERE status = 'registrada' 
                    AND data_revisada IS NULL
                    ORDER BY 
                        CASE prioridade 
                            WHEN 'urgente' THEN 1
                            WHEN 'alta' THEN 2 
                            WHEN 'media' THEN 3 
                            WHEN 'baixa' THEN 4
                            ELSE 5
                        END,
                        data_ultima_atualizacao ASC
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
     * Agora muda status para 'em_revisao' e registra quem está revisando
     */
    public function iniciarRevisao() {
        header('Content-Type: application/json');
        
        $especie_id = $_POST['especie_id'] ?? 0;
        $usuario_id = $_SESSION['usuario_id'] ?? 0;
        
        if (!$especie_id || !$usuario_id) {
            echo json_encode(['erro' => 'Dados inválidos']);
            return;
        }
        
        // Verificar se espécie ainda está disponível (status = 'registrada')
        $check = $this->conn->prepare("SELECT id FROM especies_administrativo 
                                       WHERE id = ? AND status = 'registrada' 
                                       AND data_revisada IS NULL");
        $check->bind_param('i', $especie_id);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['erro' => 'Espécie não está mais disponível para revisão']);
            return;
        }
        
        // Atualizar status da espécie para 'em_revisao'
        // Nota: Não preenchemos autor_revisada_id ainda (só quando finalizar)
        $sql = "UPDATE especies_administrativo 
                SET status = 'em_revisao',
                    data_ultima_atualizacao = NOW()
                WHERE id = ? 
                AND status = 'registrada'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $especie_id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            // Opcional: Registrar em log ou tabela auxiliar quem iniciou (para debug)
            // Por enquanto, só mudamos o status
            
            // REDIRECT para tela de revisão
            $redirect_url = "../Views/revisao.php?id=$especie_id";
            
            echo json_encode([
                'sucesso' => true, 
                'redirect' => $redirect_url
            ]);
        } else {
            echo json_encode(['erro' => 'Não foi possível iniciar a revisão']);
        }
    }
    
    /**
     * API: Finalizar revisão (aprovar)
     */
    public function aprovarRevisao() {
        header('Content-Type: application/json');
        
        $especie_id = $_POST['especie_id'] ?? 0;
        $usuario_id = $_SESSION['usuario_id'] ?? 0;
        $observacoes = $_POST['observacoes'] ?? null;
        
        if (!$especie_id || !$usuario_id) {
            echo json_encode(['erro' => 'Dados inválidos']);
            return;
        }
        
        // Atualizar para 'revisada' com autor e data
        $sql = "UPDATE especies_administrativo 
                SET status = 'revisada',
                    data_revisada = NOW(),
                    autor_revisada_id = ?,
                    observacoes = CONCAT(IFNULL(observacoes,''), '\n[REVISÃO APROVADA em ' , NOW() , ']: ', ?),
                    data_ultima_atualizacao = NOW()
                WHERE id = ? 
                AND status = 'em_revisao'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('isi', $usuario_id, $observacoes, $especie_id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Espécie aprovada com sucesso']);
        } else {
            echo json_encode(['erro' => 'Erro ao aprovar revisão']);
        }
    }
    
    /**
     * API: Finalizar revisão (contestar/rejeitar)
     */
    public function contestarRevisao() {
        header('Content-Type: application/json');
        
        $especie_id = $_POST['especie_id'] ?? 0;
        $usuario_id = $_SESSION['usuario_id'] ?? 0;
        $motivo = $_POST['motivo'] ?? '';
        
        if (!$especie_id || !$usuario_id || empty($motivo)) {
            echo json_encode(['erro' => 'Dados inválidos ou motivo não informado']);
            return;
        }
        
        // Atualizar para 'contestado' com autor, motivo e data
        $sql = "UPDATE especies_administrativo 
                SET status = 'contestado',
                    data_contestado = NOW(),
                    autor_contestado_id = ?,
                    motivo_contestado = ?,
                    data_ultima_atualizacao = NOW()
                WHERE id = ? 
                AND status = 'em_revisao'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('isi', $usuario_id, $motivo, $especie_id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['sucesso' => true, 'mensagem' => 'Espécie contestada com sucesso']);
        } else {
            echo json_encode(['erro' => 'Erro ao contestar espécie']);
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
        case 'aprovar':
            $controlador->aprovarRevisao();
            break;
        case 'contestar':
            $controlador->contestarRevisao();
            break;
        default:
            $controlador->index();
    }
} else {
    $controlador->index();
}
?>