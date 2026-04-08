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

require_once __DIR__ . '/../../config/app.php';
$servidor   = DB_HOST;
$usuario_db = DB_USER;
$senha_db   = DB_PASS;
$banco      = DB_NAME;

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
// BUSCAR ESPECIALISTAS (revisores ativos)
// ================================================
$sql_esp = "SELECT id, nome, subtipo_colaborador
            FROM usuarios
            WHERE categoria IN ('revisor')
              AND ativo = 1
              AND status_verificacao = 'verificado'
            ORDER BY nome";
$res_esp = $conexao->query($sql_esp);
$especialistas = [];
if ($res_esp) {
    while ($e = $res_esp->fetch_assoc()) $especialistas[] = $e;
}

// ================================================
// BUSCAR ESPÉCIES COM STATUS 'sem_dados'
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

    $especie_id   = (int)$_POST['especie_id'];
    $orientador_id = (int)($_POST['orientador_id'] ?? 0); // 0 = sem orientação

    // Validar se a espécie realmente existe e tem status 'sem_dados'
    $conexao2 = new mysqli($servidor, $usuario_db, $senha_db, $banco);
    $conexao2->set_charset("utf8mb4");
    $stmt = $conexao2->prepare("SELECT id, nome_cientifico FROM especies_administrativo WHERE id = ? AND status = 'sem_dados'");
    $stmt->bind_param("i", $especie_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $especie = $resultado->fetch_assoc();
        $stmt->close();

        // Registrar orientador em atribuido_a (NULL se sem orientação)
        $attr_val = $orientador_id > 0 ? $orientador_id : null;
        $stmt2 = $conexao2->prepare("UPDATE especies_administrativo SET atribuido_a = ? WHERE id = ?");
        $stmt2->bind_param("ii", $attr_val, $especie_id);
        $stmt2->execute();
        $stmt2->close();
        $conexao2->close();

        // Gerar ID único para a sessão temporária
        $temp_id = uniqid('temp_', true);

        // Armazenar na sessão
        $_SESSION['importacao_temporaria'] = [
            'temp_id'       => $temp_id,
            'especie_id'    => $especie_id,
            'usuario_id'    => $id_usuario,
            'orientador_id' => $orientador_id,
            'status_inicial'=> 'sem_dados',
            'imagens'       => [],
            'dados'         => [],
            'data_criacao'  => time()
        ];

        // Redirecionar para upload de imagens
        header("Location: upload_imagens_internet.php?temp_id=" . urlencode($temp_id));
        exit;

    } else {
        $stmt->close();
        $conexao2->close();
        $erro = "Espécie inválida ou não está disponível para importação.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penomato - Escolher Espécie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
    <style>
        body { background-color: var(--cinza-50); padding: var(--esp-8) var(--esp-5); }
        .container { max-width: 900px; margin: 0 auto; position: relative; }

        .user-info {
            position: absolute; top: var(--esp-5); right: var(--esp-5);
            background: var(--branco); padding: var(--esp-2) var(--esp-6);
            border-radius: var(--raio-full); box-shadow: var(--sombra-md);
            display: flex; align-items: center; gap: var(--esp-4); z-index: 100;
        }
        .user-info i { color: var(--cor-primaria); }
        .user-name { font-weight: var(--peso-semi); }
        .user-logout { color: var(--perigo-cor); text-decoration: none; font-size: var(--texto-sm); padding: var(--esp-1) var(--esp-2); border-radius: var(--raio-full); transition: var(--transicao); }
        .user-logout:hover { background: var(--perigo-cor); color: var(--branco); }

        .header {
            background: var(--branco); padding: var(--esp-8) var(--esp-10);
            border-radius: var(--raio-lg) var(--raio-lg) 0 0;
            box-shadow: var(--sombra-sm); border-bottom: 4px solid var(--cor-primaria); margin-bottom: var(--esp-1);
        }
        .header h1 { color: var(--cor-primaria); font-size: var(--texto-3xl); font-weight: var(--peso-medio); display: flex; align-items: center; gap: var(--esp-2); }
        .header .subtitle { color: var(--cinza-600); font-style: italic; margin-top: var(--esp-2); font-size: var(--texto-sm); }

        .card { background: var(--branco); padding: var(--esp-8) var(--esp-10); box-shadow: var(--sombra-sm); margin-bottom: var(--esp-5); }

        .counter { display: inline-block; background-color: var(--cor-primaria); color: var(--branco); padding: var(--esp-1) var(--esp-4); border-radius: var(--raio-full); font-size: var(--texto-sm); margin-left: var(--esp-4); }

        .search-container { margin: var(--esp-6) 0; }
        .search-box { display: flex; align-items: center; background-color: var(--cinza-50); border: 2px solid var(--cinza-200); border-radius: var(--raio-full); padding: var(--esp-1) var(--esp-5); transition: var(--transicao); }
        .search-box:focus-within { border-color: var(--cor-primaria); box-shadow: var(--sombra-foco); }
        .search-box i { color: var(--cinza-400); }
        .search-box input { flex: 1; padding: var(--esp-4); border: none; background: transparent; font-size: var(--texto-md); outline: none; }
        .search-box input::placeholder { color: var(--cinza-400); }
        .search-clear { cursor: pointer; color: var(--cinza-400); display: none; }
        .search-clear:hover { color: var(--perigo-cor); }

        .especies-list { margin-top: var(--esp-5); max-height: 500px; overflow-y: auto; padding-right: var(--esp-2); }
        .especie-card { background-color: var(--cinza-50); border: 2px solid var(--cinza-200); border-radius: var(--raio-lg); padding: var(--esp-5); margin-bottom: var(--esp-4); display: flex; justify-content: space-between; align-items: center; transition: var(--transicao); }
        .especie-card:hover { border-color: var(--cor-primaria); transform: translateY(-2px); box-shadow: var(--sombra-md); }
        .especie-info h3 { font-size: var(--texto-xl); color: var(--cor-primaria); margin-bottom: var(--esp-1); font-style: italic; }
        .especie-info p { color: var(--cinza-400); font-size: var(--texto-sm); display: flex; align-items: center; gap: var(--esp-1); }
        .especie-info p i { color: var(--cor-primaria); font-size: var(--texto-xs); }

        .status-badge { background-color: var(--verde-50); color: var(--cor-primaria); padding: var(--esp-1) var(--esp-3); border-radius: var(--raio-full); font-size: var(--texto-xs); font-weight: var(--peso-semi); border: 1px solid var(--cor-primaria); }

        .btn-select { background-color: var(--cor-primaria); color: var(--branco); border: none; padding: var(--esp-2) var(--esp-6); border-radius: var(--raio-full); font-weight: var(--peso-semi); cursor: pointer; transition: var(--transicao); display: flex; align-items: center; gap: var(--esp-2); }
        .btn-select:hover { background-color: var(--cor-primaria-hover); transform: translateY(-2px); box-shadow: var(--sombra-md); }

        .empty-state { text-align: center; padding: var(--esp-16) var(--esp-5); background-color: var(--cinza-50); border-radius: var(--raio-lg); border: 2px dashed var(--cinza-200); }
        .empty-state i { font-size: var(--texto-4xl); color: var(--cinza-400); margin-bottom: var(--esp-5); }
        .empty-state h3 { font-size: var(--texto-2xl); color: var(--cinza-600); margin-bottom: var(--esp-2); }
        .empty-state p { color: var(--cinza-500); margin-bottom: var(--esp-6); }

        .btn-back { display: inline-block; background-color: var(--cinza-200); color: var(--cinza-600); text-decoration: none; padding: var(--esp-3) var(--esp-8); border-radius: var(--raio-full); font-weight: var(--peso-semi); transition: var(--transicao); }
        .btn-back:hover { background-color: var(--cinza-300); }

        .footer { text-align: center; color: var(--cinza-500); font-size: var(--texto-xs); margin-top: var(--esp-8); }

        @media (max-width: 768px) {
            .user-info { position: static; margin-bottom: var(--esp-5); justify-content: center; }
            .especie-card { flex-direction: column; align-items: flex-start; gap: var(--esp-4); }
            .btn-select { width: 100%; justify-content: center; }
        }

        /* ── Modal orientador ── */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.5); z-index: 1000;
            align-items: center; justify-content: center;
        }
        .modal-overlay.aberto { display: flex; }
        .modal {
            background: var(--branco); border-radius: var(--raio-lg);
            box-shadow: var(--sombra-lg); width: 100%; max-width: 520px;
            max-height: 90vh; overflow-y: auto; padding: var(--esp-8);
        }
        .modal h2 {
            color: var(--cor-primaria); font-size: var(--texto-xl);
            margin-bottom: var(--esp-2);
        }
        .modal .especie-alvo {
            font-style: italic; color: var(--cinza-600);
            font-size: var(--texto-sm); margin-bottom: var(--esp-6);
            padding-bottom: var(--esp-4); border-bottom: 1px solid var(--cinza-200);
        }
        .orientador-opcao {
            display: flex; align-items: center; gap: var(--esp-4);
            padding: var(--esp-4) var(--esp-5); border-radius: var(--raio-md);
            border: 2px solid var(--cinza-200); margin-bottom: var(--esp-3);
            cursor: pointer; transition: var(--transicao);
        }
        .orientador-opcao:hover { border-color: var(--cor-primaria); background: var(--verde-50); }
        .orientador-opcao input[type="radio"] { accent-color: var(--cor-primaria); width: 18px; height: 18px; flex-shrink: 0; }
        .orientador-opcao .opt-nome { font-weight: var(--peso-semi); color: var(--cinza-800); }
        .orientador-opcao .opt-sub  { font-size: var(--texto-xs); color: var(--cinza-500); }
        .orientador-opcao.sem-orientacao { border-style: dashed; }
        .modal-acoes {
            display: flex; gap: var(--esp-4); justify-content: flex-end;
            margin-top: var(--esp-6); padding-top: var(--esp-5);
            border-top: 1px solid var(--cinza-200);
        }
        .btn-cancelar {
            background: var(--cinza-200); color: var(--cinza-700);
            border: none; padding: var(--esp-3) var(--esp-8);
            border-radius: var(--raio-full); font-weight: var(--peso-semi);
            cursor: pointer; transition: var(--transicao);
        }
        .btn-cancelar:hover { background: var(--cinza-300); }
        .btn-confirmar {
            background: var(--cor-primaria); color: var(--branco);
            border: none; padding: var(--esp-3) var(--esp-8);
            border-radius: var(--raio-full); font-weight: var(--peso-semi);
            cursor: pointer; transition: var(--transicao);
        }
        .btn-confirmar:hover { background: var(--cor-primaria-hover); }
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
                <div class="alerta--perigo">❌ <?php echo $erro; ?></div>
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
                            
                            <button type="button" class="btn-select"
                                onclick="abrirModal(<?php echo $especie['id']; ?>, '<?php echo addslashes(htmlspecialchars($especie['nome_cientifico'])); ?>')">
                                <i class="fas fa-arrow-right"></i>
                                SELECIONAR
                            </button>
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

        <!-- ── MODAL: escolha do orientador ── -->
        <div class="modal-overlay" id="modalOrientador">
            <div class="modal">
                <h2><i class="fas fa-user-tie"></i> Escolha um orientador</h2>
                <div class="especie-alvo" id="modalEspecieNome"></div>

                <form method="POST" action="" id="formOrientador">
                    <input type="hidden" name="especie_id" id="modalEspecieId">
                    <input type="hidden" name="orientador_id" id="inputOrientadorId" value="0">

                    <!-- Opção: sem orientação -->
                    <label class="orientador-opcao sem-orientacao">
                        <input type="radio" name="orientador_radio" value="0" checked
                               onchange="document.getElementById('inputOrientadorId').value=0">
                        <div>
                            <div class="opt-nome">🌐 Sem orientação</div>
                            <div class="opt-sub">Qualquer especialista poderá assumir esta espécie</div>
                        </div>
                    </label>

                    <?php foreach ($especialistas as $esp): ?>
                    <label class="orientador-opcao">
                        <input type="radio" name="orientador_radio" value="<?php echo $esp['id']; ?>"
                               onchange="document.getElementById('inputOrientadorId').value=<?php echo $esp['id']; ?>">
                        <div>
                            <div class="opt-nome"><?php echo htmlspecialchars($esp['nome']); ?></div>
                            <?php if (!empty($esp['subtipo_colaborador'])): ?>
                            <div class="opt-sub"><?php echo htmlspecialchars($esp['subtipo_colaborador']); ?></div>
                            <?php endif; ?>
                        </div>
                    </label>
                    <?php endforeach; ?>

                    <?php if (empty($especialistas)): ?>
                    <p style="color:var(--cinza-500); font-size:var(--texto-sm); padding:var(--esp-4);">
                        Nenhum especialista cadastrado ainda. A espécie ficará sem orientação.
                    </p>
                    <?php endif; ?>

                    <div class="modal-acoes">
                        <button type="button" class="btn-cancelar" onclick="fecharModal()">Cancelar</button>
                        <button type="submit" class="btn-confirmar">
                            <i class="fas fa-check"></i> Confirmar e continuar
                        </button>
                    </div>
                </form>
            </div>
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

    // ── Modal orientador ──────────────────────────────
    function abrirModal(especieId, especieNome) {
        document.getElementById('modalEspecieId').value   = especieId;
        document.getElementById('inputOrientadorId').value = 0;
        document.getElementById('modalEspecieNome').textContent = especieNome;

        // Reset radio para "sem orientação"
        document.querySelectorAll('input[name="orientador_radio"]').forEach(r => {
            r.checked = (r.value === '0');
        });

        document.getElementById('modalOrientador').classList.add('aberto');
    }

    function fecharModal() {
        document.getElementById('modalOrientador').classList.remove('aberto');
    }

    // Fechar clicando fora do modal
    document.getElementById('modalOrientador').addEventListener('click', function(e) {
        if (e.target === this) fecharModal();
    });
    </script>
</body>
</html>