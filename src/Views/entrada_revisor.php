<?php
// ================================================
// CONEXÃO E BUSCA NO BANCO - VERSÃO MÍNIMA
// ================================================

// Configurações do banco
$servidor = "127.0.0.1";
$usuario = "root";
$senha = "";
$banco = "penomato";

// Variáveis para as espécies
$especies_disponiveis = [];
$mensagem_erro = '';

// Tentar conectar e buscar dados
$conexao = mysqli_connect($servidor, $usuario, $senha, $banco);

if (!$conexao) {
    $mensagem_erro = 'Erro: Não foi possível conectar ao banco de dados';
} else {
    mysqli_set_charset($conexao, "utf8mb4");
    
    // Buscar espécies - APENAS COLUNAS QUE CERTAMENTE EXISTEM
    $sql = "SELECT 
                id,
                nome_cientifico,
                prioridade
            FROM especies_administrativo 
            WHERE status_caracteristicas = 'completo' 
            AND status_revisao = 'aguardando'
            ORDER BY 
                CASE prioridade 
                    WHEN 'alta' THEN 1 
                    WHEN 'media' THEN 2 
                    WHEN 'baixa' THEN 3 
                END,
                nome_cientifico
            LIMIT 50";
    
    $resultado = mysqli_query($conexao, $sql);
    
    if (!$resultado) {
        $mensagem_erro = 'Erro na consulta: ' . mysqli_error($conexao);
    } else {
        while ($linha = mysqli_fetch_assoc($resultado)) {
            $especies_disponiveis[] = $linha;
        }
        mysqli_free_result($resultado);
    }
    
    mysqli_close($conexao);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Revisor - Penomato</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f0;
            padding: 30px;
            color: #1e2e1e;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: #0b5e42;
            color: white;
            padding: 20px 30px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 1.5em;
        }

        .user-badge {
            background-color: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 40px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            background-color: #0b5e42;
            color: white;
        }

        .btn-secondary {
            background-color: #e9ecef;
            color: #1e2e1e;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8em;
        }

        .actions-bar {
            background: white;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 25px;
            display: flex;
            gap: 10px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-size: 1.2em;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            color: #6c757d;
        }

        .modal-body {
            padding: 20px;
            overflow-y: auto;
            max-height: calc(80vh - 70px);
        }

        .modal-list-item {
            padding: 12px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-list-item:hover {
            background-color: #f8f9fa;
            border-color: #0b5e42;
        }

        .empty-message {
            text-align: center;
            padding: 30px;
            color: #6c757d;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.7em;
            font-weight: 600;
            margin-left: 5px;
        }

        .badge-alta {
            background-color: #dc3545;
            color: white;
        }

        .badge-media {
            background-color: #ffc107;
            color: #1e2e1e;
        }

        .badge-baixa {
            background-color: #28a745;
            color: white;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Painel do Revisor</h1>
            <div class="user-badge">Dr. Norton · UEMS</div>
        </div>

        <div class="actions-bar">
            <button class="btn" onclick="abrirModal()">➕ NOVA REVISÃO</button>
            <button class="btn btn-secondary">📂 CONTINUAR</button>
        </div>

        <div style="background: white; border-radius: 10px; padding: 40px; text-align: center; color: #6c757d;">
            <p style="font-size: 1.2em; margin-bottom: 10px;">Nenhuma espécie disponível</p>
            <p>Clique em "NOVA REVISÃO" para começar</p>
        </div>

        <!-- MODAL -->
        <div id="modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Iniciar Nova Revisão</h3>
                    <button class="modal-close" onclick="fecharModal()">✕</button>
                </div>
                <div class="modal-body">
                    <input type="text" id="buscaEspecie" placeholder="Buscar espécie..." onkeyup="filtrarEspecies()">
                    
                    <div id="listaEspecies">
                        <?php if (!empty($mensagem_erro)): ?>
                            <div class="empty-message"><?php echo $mensagem_erro; ?></div>
                        <?php elseif (empty($especies_disponiveis)): ?>
                            <div class="empty-message">Nenhuma espécie disponível para revisão</div>
                        <?php else: ?>
                            <?php foreach ($especies_disponiveis as $esp): 
                                $badgeClass = 'badge-media';
                                $badgeText = 'MÉDIA';
                                
                                if ($esp['prioridade'] === 'alta') {
                                    $badgeClass = 'badge-alta';
                                    $badgeText = 'ALTA';
                                } else if ($esp['prioridade'] === 'baixa') {
                                    $badgeClass = 'badge-baixa';
                                    $badgeText = 'BAIXA';
                                }
                            ?>
                                <div class="modal-list-item" data-nome="<?php echo strtolower(htmlspecialchars($esp['nome_cientifico'])); ?>">
                                    <div>
                                        <strong><i><?php echo htmlspecialchars($esp['nome_cientifico']); ?></i></strong>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo $badgeText; ?></span>
                                    </div>
                                    <button class="btn btn-sm" onclick="iniciarRevisao(<?php echo $esp['id']; ?>)">Selecionar</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function abrirModal() {
            document.getElementById('modal').classList.add('active');
        }

        function fecharModal() {
            document.getElementById('modal').classList.remove('active');
        }

        function filtrarEspecies() {
            const termo = document.getElementById('buscaEspecie').value.toLowerCase();
            const itens = document.querySelectorAll('.modal-list-item');
            
            itens.forEach(item => {
                const texto = item.getAttribute('data-nome') || '';
                if (texto.includes(termo) || termo === '') {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function iniciarRevisao(id) {
            window.location.href = 'artigo_revisao.php?id=' + id;
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') fecharModal();
        });
    </script>
</body>
</html>