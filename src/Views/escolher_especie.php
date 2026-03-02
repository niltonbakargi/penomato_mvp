<?php
// ================================================
// ESCOLHER ESPÉCIE - PRIMEIRA TELA DO FLUXO DE IMPORTAÇÃO
// Lista apenas espécies com status 'sem_dados'
// ================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
ob_start();

$servidor = "127.0.0.1";
$usuario_db = "root";
$senha_db = "";
$banco = "penomato";

// ================================================
// VERIFICAR SE USUÁRIO ESTÁ LOGADO
// ================================================
if (!isset($_SESSION['usuario_id'])) {
    $url_atual = urlencode($_SERVER['REQUEST_URI']);
    header("Location: ../Views/auth/login.php?redirect=" . $url_atual);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';

// ================================================
// CONECTAR AO BANCO E BUSCAR ESPÉCIES COM STATUS 'sem_dados'
// ================================================
$conexao = new mysqli($servidor, $usuario_db, $senha_db, $banco);

if ($conexao->connect_error) {
    die("Erro de conexão: " . $conexao->connect_error);
}

$conexao->set_charset("utf8mb4");

// ================================================
// CORREÇÃO: Removida a coluna 'familia' que não existe
// Buscar apenas id e nome_cientifico
// ================================================
$sql = "SELECT id, nome_cientifico 
        FROM especies_administrativo 
        WHERE status = 'sem_dados'
        ORDER BY nome_cientifico";

$resultado = $conexao->query($sql);
$especies = [];
$total_especies = 0;

if ($resultado && $resultado->num_rows > 0) {
    $total_especies = $resultado->num_rows;
    while ($linha = $resultado->fetch_assoc()) {
        $especies[] = [
            'id' => $linha['id'],
            'nome_cientifico' => $linha['nome_cientifico']
        ];
    }
}

$conexao->close();

// ================================================
// PROCESSAR SELEÇÃO DA ESPÉCIE
// ================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['especie_id'])) {
    
    $especie_id = (int)$_POST['especie_id'];
    
    // Validar se a espécie realmente existe e tem status 'sem_dados'
    $conexao = new mysqli($servidor, $usuario_db, $senha_db, $banco);
    $sql_check = "SELECT id, nome_cientifico, status FROM especies_administrativo WHERE id = ? AND status = 'sem_dados'";
    $stmt = $conexao->prepare($sql_check);
    $stmt->bind_param("i", $especie_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $especie = $resultado->fetch_assoc();
        
        // Gerar ID único para a sessão temporária
        $temp_id = uniqid('temp_', true);
        
        // Armazenar na sessão
        $_SESSION['importacao_temporaria'] = [
            'temp_id' => $temp_id,
            'especie_id' => $especie_id,
            'usuario_id' => $id_usuario,
            'status_inicial' => 'sem_dados',
            'imagens' => [],
            'dados' => [],
            'data_criacao' => time()
        ];
        
        // Redirecionar para upload de imagens
        header("Location: upload_imagens_internet.php?temp_id=" . urlencode($temp_id));
        exit;
        
    } else {
        $erro = "Espécie inválida ou não está disponível para importação.";
    }
    
    $conexao->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penomato - Escolher Espécie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f2e9;
            padding: 30px 20px;
            color: #2c3e50;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            position: relative;
        }

        /* Informações do usuário */
        .user-info {
            position: absolute;
            top: 20px;
            right: 20px;
            background: white;
            padding: 10px 25px;
            border-radius: 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
            z-index: 100;
        }
        
        .user-info i {
            color: #0b5e42;
            font-size: 1.2rem;
        }
        
        .user-name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .user-logout {
            color: #dc3545;
            text-decoration: none;
            font-size: 0.9rem;
            padding: 5px 10px;
            border-radius: 20px;
            transition: all 0.2s;
        }
        
        .user-logout:hover {
            background: #dc3545;
            color: white;
        }

        /* Cabeçalho */
        .header {
            background: white;
            padding: 30px 40px;
            border-radius: 12px 12px 0 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-bottom: 4px solid #0b5e42;
            margin-bottom: 5px;
        }

        .header h1 {
            color: #0b5e42;
            font-size: 2rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header .subtitle {
            color: #666;
            font-style: italic;
            margin-top: 10px;
            font-size: 0.95rem;
        }

        /* Card principal */
        .card {
            background: white;
            padding: 30px 40px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }

        /* Contador de espécies */
        .counter {
            display: inline-block;
            background-color: #0b5e42;
            color: white;
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 0.9rem;
            margin-left: 15px;
        }

        /* Campo de busca */
        .search-container {
            margin: 25px 0;
        }

        .search-box {
            display: flex;
            align-items: center;
            background-color: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 50px;
            padding: 5px 20px;
            transition: all 0.3s;
        }

        .search-box:focus-within {
            border-color: #0b5e42;
            box-shadow: 0 0 0 3px rgba(11,94,66,0.1);
        }

        .search-box i {
            color: #718096;
            font-size: 1.2rem;
        }

        .search-box input {
            flex: 1;
            padding: 15px;
            border: none;
            background: transparent;
            font-size: 1rem;
            outline: none;
        }

        .search-box input::placeholder {
            color: #a0aec0;
        }

        .search-clear {
            cursor: pointer;
            color: #718096;
            font-size: 1.2rem;
            display: none;
        }

        .search-clear:hover {
            color: #dc3545;
        }

        /* Lista de espécies */
        .especies-list {
            margin-top: 20px;
            max-height: 500px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .especie-card {
            background-color: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }

        .especie-card:hover {
            border-color: #0b5e42;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .especie-info h3 {
            font-size: 1.3rem;
            color: #0b5e42;
            margin-bottom: 5px;
            font-style: italic;
        }

        .especie-info p {
            color: #718096;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .especie-info p i {
            color: #0b5e42;
            font-size: 0.9rem;
        }

        .status-badge {
            background-color: #e6f7e6;
            color: #0b5e42;
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid #0b5e42;
        }

        .btn-select {
            background-color: #0b5e42;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-select:hover {
            background-color: #0a4c35;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(11,94,66,0.3);
        }

        .btn-select i {
            font-size: 0.9rem;
        }

        /* Mensagem de lista vazia */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background-color: #f8fafc;
            border-radius: 12px;
            border: 2px dashed #e2e8f0;
        }

        .empty-state i {
            font-size: 4rem;
            color: #a0aec0;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: #4a5568;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #718096;
            margin-bottom: 25px;
        }

        .btn-back {
            display: inline-block;
            background-color: #e2e8f0;
            color: #4a5568;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 40px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background-color: #cbd5e0;
        }

        /* Footer */
        .footer {
            text-align: center;
            color: #718096;
            font-size: 0.85rem;
            margin-top: 30px;
        }

        /* Alertas */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        @media (max-width: 768px) {
            .user-info {
                position: static;
                margin-bottom: 20px;
                justify-content: center;
            }
            
            .especie-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .btn-select {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- Informações do usuário logado -->
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span class="user-name"><?php echo htmlspecialchars($nome_usuario); ?></span>
            <a href="../Controllers/auth/logout_controlador.php" class="user-logout" onclick="return confirm('Deseja sair do sistema?')">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </div>
        
        <!-- Cabeçalho -->
        <div class="header">
            <h1>
                <span>🌱</span>
                PENOMATO • ESCOLHER ESPÉCIE
                <span class="counter"><?php echo $total_especies; ?> disponíveis</span>
            </h1>
            <div class="subtitle">
                Selecione uma espécie com status "sem dados" para iniciar a importação
            </div>
        </div>

        <!-- Card principal -->
        <div class="card">
            
            <!-- Mensagem de erro (se houver) -->
            <?php if (isset($erro)): ?>
                <div class="alert alert-danger">❌ <?php echo $erro; ?></div>
            <?php endif; ?>
            
            <!-- Campo de busca -->
            <div class="search-container">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Digite o nome científico para filtrar...">
                    <i class="fas fa-times search-clear" id="clearSearch" onclick="clearSearch()"></i>
                </div>
            </div>
            
            <!-- Lista de espécies -->
            <?php if ($total_especies > 0): ?>
                <div class="especies-list" id="especiesList">
                    <?php foreach ($especies as $especie): ?>
                        <div class="especie-card" data-nome="<?php echo strtolower(htmlspecialchars($especie['nome_cientifico'])); ?>">
                            <div class="especie-info">
                                <h3><?php echo htmlspecialchars($especie['nome_cientifico']); ?></h3>
                                <p>
                                    <i class="fas fa-leaf"></i>
                                    <span class="status-badge">SEM DADOS</span>
                                </p>
                            </div>
                            
                            <form method="POST" action="" style="margin: 0;">
                                <input type="hidden" name="especie_id" value="<?php echo $especie['id']; ?>">
                                <button type="submit" class="btn-select">
                                    <i class="fas fa-arrow-right"></i>
                                    SELECIONAR
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Nenhuma espécie disponível -->
                <div class="empty-state">
                    <i class="fas fa-tree"></i>
                    <h3>Nenhuma espécie disponível</h3>
                    <p>Não há espécies com status "sem dados" no momento.<br>Cadastre novas espécies ou aguarde.</p>
                    <a href="entrar_colaborador.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> VOLTAR AO PAINEL
                    </a>
                </div>
            <?php endif; ?>
            
        </div>

        <!-- Rodapé com instruções -->
        <div class="footer">
            <p>Penomato • Selecionando espécie para importação de dados e imagens</p>
            <p style="margin-top: 5px; font-size: 0.8rem;">Apenas espécies com status "sem dados" são exibidas</p>
        </div>
    </div>

    <script>
    // ================================================
    // FILTRO DE BUSCA EM TEMPO REAL
    // ================================================
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearch');
    const especiesList = document.getElementById('especiesList');
    const cards = document.querySelectorAll('.especie-card');
    
    function filterSpecies() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;
        
        cards.forEach(card => {
            const nome = card.dataset.nome;
            if (nome.includes(searchTerm)) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Mostrar/esconder botão de limpar
        if (searchTerm.length > 0) {
            clearBtn.style.display = 'inline-block';
        } else {
            clearBtn.style.display = 'none';
        }
        
        // Se não houver resultados visíveis, mostrar mensagem
        const emptyMessage = document.getElementById('emptySearchMessage');
        
        if (visibleCount === 0 && cards.length > 0) {
            if (!emptyMessage) {
                const msg = document.createElement('div');
                msg.id = 'emptySearchMessage';
                msg.className = 'empty-state';
                msg.style.marginTop = '20px';
                msg.innerHTML = `
                    <i class="fas fa-search"></i>
                    <h3>Nenhuma espécie encontrada</h3>
                    <p>Tente outro termo de busca</p>
                `;
                especiesList.appendChild(msg);
            }
        } else {
            if (emptyMessage) {
                emptyMessage.remove();
            }
        }
    }
    
    function clearSearch() {
        searchInput.value = '';
        filterSpecies();
        searchInput.focus();
    }
    
    searchInput.addEventListener('input', filterSpecies);
    
    // Inicializar estado do botão de limpar
    if (searchInput.value.length > 0) {
        clearBtn.style.display = 'inline-block';
    }
    </script>
</body>
</html>