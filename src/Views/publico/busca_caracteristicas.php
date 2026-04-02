<?php
// ================================================
// BUSCA DE ESPÉCIES POR CARACTERÍSTICAS - VERSÃO CORRIGIDA
// ================================================

session_start();

// Carregar configuração do banco (PDO)
require_once __DIR__ . '/../../../config/banco_de_dados.php';

// ================================================
// FUNÇÕES AUXILIARES
// ================================================

/**
 * Monta a query WHERE dinamicamente baseada nos filtros preenchidos
 * Usando os nomes de campos CORRETOS da tabela especies_caracteristicas
 */
function montarWhere($dados_busca) {
    $condicoes = [];
    $parametros = [];
    
    // MAPEAMENTO CORRETO - Baseado na tabela real
    $campos_busca = [
        // Identificação
        'nome_cientifico_completo' => 'LIKE',
        'nome_popular' => 'LIKE',
        'familia' => 'LIKE',
        
        // Folha (campos OK)
        'forma_folha' => '=',
        'filotaxia_folha' => '=',
        'tipo_folha' => '=',
        'tamanho_folha' => '=',
        'textura_folha' => '=',
        'margem_folha' => '=',
        'venacao_folha' => '=',

        // Flores
        'cor_flores' => '=',
        'simetria_floral' => '=',
        'numero_petalas' => '=',
        'tamanho_flor' => '=',
        'disposicao_flores' => '=',
        'aroma' => '=',

        // Frutos
        'tipo_fruto' => '=',
        'tamanho_fruto' => '=',
        'cor_fruto' => '=',
        'textura_fruto' => '=',
        'dispersao_fruto' => '=',
        'aroma_fruto' => '=',

        // Sementes
        'tipo_semente' => '=',
        'tamanho_semente' => '=',
        'cor_semente' => '=',
        'textura_semente' => '=',
        'quantidade_sementes' => '=',
        
        // Caule (OK)
        'tipo_caule' => '=',
        'estrutura_caule' => '=',
        'textura_caule' => '=',
        'cor_caule' => '=',
        'forma_caule' => '=',
        'modificacao_caule' => '=',
        'diametro_caule' => '=',
        'ramificacao_caule' => '=',
        
        // Outras (OK)
        'possui_espinhos' => '=',
        'possui_latex' => '=',
        'possui_seiva' => '=',
        'possui_resina' => '='
    ];
    
    foreach ($campos_busca as $campo => $operador) {
        if (isset($dados_busca[$campo]) && trim($dados_busca[$campo]) !== '' && $dados_busca[$campo] !== 'todos') {
            
            if ($operador === 'LIKE') {
                $condicoes[] = "c.$campo LIKE ?";
                $parametros[] = '%' . $dados_busca[$campo] . '%';
            } else {
                $condicoes[] = "c.$campo = ?";
                $parametros[] = $dados_busca[$campo];
            }
        }
    }
    
    return [
        'condicoes' => $condicoes,
        'parametros' => $parametros
    ];
}

/**
 * Conta total de espécies que atendem aos filtros
 */
function contarEspecies($pdo, $where_info) {
    $sql = "SELECT COUNT(DISTINCT c.id) as total 
            FROM especies_caracteristicas c
            INNER JOIN especies_administrativo e ON c.especie_id = e.id";
    
    if (!empty($where_info['condicoes'])) {
        $sql .= " WHERE " . implode(" AND ", $where_info['condicoes']);
    }
    
    $stmt = $pdo->prepare($sql);
    
    if (!empty($where_info['parametros'])) {
        $stmt->execute($where_info['parametros']);
    } else {
        $stmt->execute();
    }
    
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    return $resultado['total'] ?? 0;
}

/**
 * Busca espécies paginadas
 */
function buscarEspecies($pdo, $where_info, $pagina, $limite = 100) {
    $offset = ($pagina - 1) * $limite;
    
    $sql = "SELECT
                c.especie_id,
                c.nome_cientifico_completo as nome_cientifico,
                c.nome_popular,
                c.familia
            FROM especies_caracteristicas c
            INNER JOIN especies_administrativo e ON c.especie_id = e.id";
    
    if (!empty($where_info['condicoes'])) {
        $sql .= " WHERE " . implode(" AND ", $where_info['condicoes']);
    }
    
    $sql .= " ORDER BY c.nome_cientifico_completo LIMIT ? OFFSET ?";
    
    $stmt = $pdo->prepare($sql);
    
    $parametros = $where_info['parametros'];
    $parametros[] = $limite;
    $parametros[] = $offset;
    
    $stmt->execute($parametros);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ================================================
// PROCESSAR A BUSCA
// ================================================

$total_encontrado = null;
$especies_encontradas = [];
$pagina_atual = 1;
$filtros_aplicados = false;
$mensagem_busca = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
    
    $filtros_aplicados = true;
    $pagina_atual = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
    
    // Remove campos vazios e 'todos'
    $dados_busca = array_filter($_POST, function($valor) {
        return $valor !== '' && $valor !== 'todos';
    });
    
    try {
        $where_info = montarWhere($dados_busca);
        $total_encontrado = contarEspecies($pdo, $where_info);
        
        if ($total_encontrado > 0 && isset($_POST['mostrar_lista'])) {
            $especies_encontradas = buscarEspecies($pdo, $where_info, $pagina_atual, 100);
        }
    } catch (Exception $e) {
        $mensagem_busca = 'Erro na busca: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Busca de Espécies - Penomato</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
    <style>
        /* ── Página ── */
        body { padding: 30px 20px; }

        .container { max-width: 1200px; margin: 0 auto; }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--esp-8);
            flex-wrap: wrap;
            gap: var(--esp-4);
        }

        h1 {
            color: var(--cor-primaria);
            font-weight: var(--peso-semi);
            border-bottom: 3px solid var(--cor-primaria);
            padding-bottom: var(--esp-4);
            display: inline-block;
        }

        /* btn-voltar já definido no estilo.css — sobrescrita de cor para página clara */
        .btn-voltar {
            background: var(--cinza-200);
            color: var(--cinza-900);
            padding: var(--esp-2) var(--esp-5);
            border-radius: var(--raio-pill);
            font-weight: var(--peso-semi);
        }
        .btn-voltar:hover { background: var(--cinza-300); color: var(--cinza-900); }

        /* ── Formulário ── */
        .form-busca {
            background: var(--branco);
            border-radius: var(--raio-lg);
            padding: var(--esp-8);
            box-shadow: var(--sombra-lg);
            margin-bottom: var(--esp-8);
        }

        .secao {
            background: var(--cinza-50);
            border-radius: var(--raio-md);
            padding: var(--esp-5);
            margin-bottom: var(--esp-6);
            border-left: 6px solid var(--cor-primaria);
        }

        .secao h2 {
            font-size: var(--texto-xl);
            color: var(--cor-primaria);
            margin-bottom: var(--esp-5);
            font-weight: var(--peso-medio);
            display: flex;
            align-items: center;
            gap: var(--esp-2);
        }

        .grid-filtros {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: var(--esp-5);
        }

        .filtro-item { display: flex; flex-direction: column; }

        .filtro-item label {
            font-size: var(--texto-sm);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: var(--peso-bold);
            color: var(--cinza-800);
            margin-bottom: var(--esp-1);
        }

        select:hover, input[type="text"]:hover { border-color: var(--cinza-400); }

        /* ── Ações ── */
        .acoes-busca {
            display: flex;
            gap: var(--esp-4);
            justify-content: center;
            margin-top: var(--esp-5);
            flex-wrap: wrap;
        }

        .btn-buscar {
            background: var(--cor-primaria);
            color: var(--branco);
            padding: var(--esp-3) var(--esp-8);
            border: none;
            border-radius: var(--raio-pill);
            font-size: var(--texto-md);
            font-weight: var(--peso-semi);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: var(--esp-2);
            transition: background var(--transicao), transform var(--transicao), box-shadow var(--transicao);
            box-shadow: 0 4px 12px rgba(11,94,66,0.3);
        }
        .btn-buscar:hover {
            background: var(--cor-primaria-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(11,94,66,0.4);
        }

        .btn-limpar {
            background: var(--cinza-200);
            color: var(--cinza-900);
            padding: var(--esp-3) var(--esp-8);
            border: none;
            border-radius: var(--raio-pill);
            font-size: var(--texto-md);
            font-weight: var(--peso-semi);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: var(--esp-2);
            transition: background var(--transicao);
        }
        .btn-limpar:hover { background: var(--cinza-300); }

        /* ── Resultados ── */
        .resultados {
            background: var(--branco);
            border-radius: var(--raio-lg);
            padding: var(--esp-8);
            box-shadow: var(--sombra-lg);
        }

        .contador {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--esp-6);
            flex-wrap: wrap;
            gap: var(--esp-4);
        }

        .badge-total {
            background: var(--cor-primaria);
            color: var(--branco);
            padding: var(--esp-2) var(--esp-6);
            border-radius: var(--raio-pill);
            font-weight: var(--peso-semi);
            font-size: var(--texto-lg);
        }

        .sem-resultados {
            text-align: center;
            padding: var(--esp-12) var(--esp-5);
            background: var(--perigo-fundo);
            border-radius: var(--raio-lg);
            color: var(--perigo-texto);
        }
        .sem-resultados p { margin: var(--esp-2) 0; font-size: var(--texto-md); }
        .sugestao { color: var(--cinza-600); font-style: italic; margin-top: var(--esp-4); }

        /* ── Lista de espécies ── */
        .lista-especies { list-style: none; margin-top: var(--esp-5); }

        .especie-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--esp-4) var(--esp-5);
            border-bottom: 1px solid var(--cinza-200);
            transition: background var(--transicao);
        }
        .especie-item:hover { background: var(--cinza-50); }

        .especie-info { flex: 1; }

        .especie-nome {
            font-size: var(--texto-md);
            font-weight: var(--peso-semi);
            color: var(--cor-primaria);
            font-style: italic;
        }

        .especie-detalhes {
            font-size: var(--texto-sm);
            color: var(--cinza-600);
            margin-top: var(--esp-1);
        }

        .especie-link {
            background: var(--cinza-200);
            padding: var(--esp-2) var(--esp-5);
            border-radius: var(--raio-pill);
            color: var(--cinza-900);
            text-decoration: none;
            font-weight: var(--peso-medio);
            font-size: var(--texto-sm);
            transition: background var(--transicao), color var(--transicao);
            white-space: nowrap;
        }
        .especie-link:hover { background: var(--cor-primaria); color: var(--branco); text-decoration: none; }

        /* ── Paginação ── */
        .paginacao {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: var(--esp-2);
            margin-top: var(--esp-10);
            flex-wrap: wrap;
        }

        .page-link {
            padding: var(--esp-2) var(--esp-4);
            border: 2px solid var(--cinza-200);
            border-radius: var(--raio-md);
            text-decoration: none;
            color: var(--cinza-800);
            font-weight: var(--peso-medio);
            font-size: var(--texto-sm);
            transition: background var(--transicao), color var(--transicao), border-color var(--transicao);
            background: var(--branco);
            cursor: pointer;
        }
        .page-link:hover,
        .page-link.ativa {
            background: var(--cor-primaria);
            color: var(--branco);
            border-color: var(--cor-primaria);
            text-decoration: none;
        }

        .page-disabled {
            padding: var(--esp-2) var(--esp-4);
            border: 2px solid var(--cinza-200);
            border-radius: var(--raio-md);
            color: var(--cinza-400);
            background: var(--cinza-100);
            font-size: var(--texto-sm);
        }

        /* ── Mensagens ── */
        .erro-mensagem {
            background: var(--perigo-fundo);
            color: var(--perigo-texto);
            padding: var(--esp-4);
            border-radius: var(--raio-md);
            margin-bottom: var(--esp-5);
            border-left: 4px solid var(--perigo-cor);
            font-size: var(--texto-sm);
        }

        /* ── Barra sticky de pesquisa ── */
        .barra-sticky {
            position: sticky;
            top: 0;
            z-index: 200;
            background: var(--branco);
            border-bottom: 2px solid var(--cinza-200);
            padding: var(--esp-3) var(--esp-6);
            margin: calc(-1 * var(--esp-8)) calc(-1 * var(--esp-8)) var(--esp-6) calc(-1 * var(--esp-8));
            border-radius: var(--raio-lg) var(--raio-lg) 0 0;
            display: flex;
            align-items: center;
            gap: var(--esp-4);
            flex-wrap: wrap;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .barra-sticky .barra-titulo {
            flex: 1;
            font-size: var(--texto-md);
            font-weight: var(--peso-semi);
            color: var(--cinza-700);
            white-space: nowrap;
        }

        @media (max-width: 600px) {
            body { padding: var(--esp-4) var(--esp-3); }
            .form-busca, .resultados { padding: var(--esp-5); }
            .btn-buscar, .btn-limpar { width: 100%; justify-content: center; }
            .barra-sticky { margin: calc(-1 * var(--esp-5)) calc(-1 * var(--esp-5)) var(--esp-5) calc(-1 * var(--esp-5)); padding: var(--esp-3) var(--esp-4); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Busca de Espécies</h1>
            <a href="/penomato_mvp/index.php" class="btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar ao Início
            </a>
        </div>
        
        <?php if ($mensagem_busca): ?>
        <div class="erro-mensagem">
            <?php echo htmlspecialchars($mensagem_busca); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['sem_resultado'])): ?>
        <div class="alerta--aviso">
            <i class="fas fa-exclamation-triangle"></i>
            Nenhuma espécie publicada encontrada com esses filtros. Tente critérios mais amplos.
        </div>
        <?php endif; ?>

        <form method="POST" action="/penomato_mvp/src/Views/publico/especie_detalhes.php" class="form-busca">

            <!-- Barra sticky de ação -->
            <div class="barra-sticky">
                <span class="barra-titulo"><i class="fas fa-sliders-h"></i> Filtros de características</span>
                <button type="submit" class="btn btn-buscar">
                    🔎 BUSCAR ESPÉCIES
                </button>
                <button type="button" class="btn btn-limpar" onclick="window.location.href='/penomato_mvp/src/Views/publico/busca_caracteristicas.php'">
                    🧹 LIMPAR
                </button>
            </div>

            <!-- Busca por nome -->
            <div class="secao">
                <h2>📌 Nome da Espécie</h2>
                <div class="grid-filtros">
                    <div class="filtro-item">
                        <label>Nome Científico Completo</label>
                        <input type="text" name="nome_cientifico_completo" placeholder="Digite parte do nome científico..." 
                               value="<?php echo isset($_POST['nome_cientifico_completo']) ? htmlspecialchars($_POST['nome_cientifico_completo']) : ''; ?>">
                    </div>
                    <div class="filtro-item">
                        <label>Nome Popular</label>
                        <input type="text" name="nome_popular" placeholder="Digite o nome popular..." 
                               value="<?php echo isset($_POST['nome_popular']) ? htmlspecialchars($_POST['nome_popular']) : ''; ?>">
                    </div>
                    <div class="filtro-item">
                        <label>Família</label>
                        <input type="text" name="familia" placeholder="Digite a família..." 
                               value="<?php echo isset($_POST['familia']) ? htmlspecialchars($_POST['familia']) : ''; ?>">
                    </div>
                </div>
            </div>
            
            <!-- CARACTERÍSTICAS DA FOLHA -->
            <div class="secao">
                <h2>🍃 Folha</h2>
                <div class="grid-filtros">
                    <div class="filtro-item">
                        <label>Forma</label>
                        <select name="forma_folha">
                            <option value="todos" <?php echo (!isset($_POST['forma_folha']) || $_POST['forma_folha'] == 'todos') ? 'selected' : ''; ?>>Todas as formas</option>
                            <option value="Lanceolada" <?php echo (isset($_POST['forma_folha']) && $_POST['forma_folha'] == 'Lanceolada') ? 'selected' : ''; ?>>Lanceolada</option>
                            <option value="Linear" <?php echo (isset($_POST['forma_folha']) && $_POST['forma_folha'] == 'Linear') ? 'selected' : ''; ?>>Linear</option>
                            <option value="Elíptica" <?php echo (isset($_POST['forma_folha']) && $_POST['forma_folha'] == 'Elíptica') ? 'selected' : ''; ?>>Elíptica</option>
                            <option value="Ovada" <?php echo (isset($_POST['forma_folha']) && $_POST['forma_folha'] == 'Ovada') ? 'selected' : ''; ?>>Ovada</option>
                            <option value="Orbicular" <?php echo (isset($_POST['forma_folha']) && $_POST['forma_folha'] == 'Orbicular') ? 'selected' : ''; ?>>Orbicular</option>
                            <option value="Cordiforme" <?php echo (isset($_POST['forma_folha']) && $_POST['forma_folha'] == 'Cordiforme') ? 'selected' : ''; ?>>Cordiforme</option>
                            <option value="Espatulada" <?php echo (isset($_POST['forma_folha']) && $_POST['forma_folha'] == 'Espatulada') ? 'selected' : ''; ?>>Espatulada</option>
                            <option value="Sagitada" <?php echo (isset($_POST['forma_folha']) && $_POST['forma_folha'] == 'Sagitada') ? 'selected' : ''; ?>>Sagitada</option>
                            <option value="Reniforme" <?php echo (isset($_POST['forma_folha']) && $_POST['forma_folha'] == 'Reniforme') ? 'selected' : ''; ?>>Reniforme</option>
                            <option value="Obovada" <?php echo (isset($_POST['forma_folha']) && $_POST['forma_folha'] == 'Obovada') ? 'selected' : ''; ?>>Obovada</option>
                            <option value="Trilobada" <?php echo (isset($_POST['forma_folha']) && $_POST['forma_folha'] == 'Trilobada') ? 'selected' : ''; ?>>Trilobada</option>
                            <option value="Palmada" <?php echo (isset($_POST['forma_folha']) && $_POST['forma_folha'] == 'Palmada') ? 'selected' : ''; ?>>Palmada</option>
                            <option value="Lobada" <?php echo (isset($_POST['forma_folha']) && $_POST['forma_folha'] == 'Lobada') ? 'selected' : ''; ?>>Lobada</option>
                        </select>
                    </div>

                    <div class="filtro-item">
                        <label>Filotaxia</label>
                        <select name="filotaxia_folha">
                            <option value="todos" <?php echo (!isset($_POST['filotaxia_folha']) || $_POST['filotaxia_folha'] == 'todos') ? 'selected' : ''; ?>>Todas</option>
                            <option value="Alterna" <?php echo (isset($_POST['filotaxia_folha']) && $_POST['filotaxia_folha'] == 'Alterna') ? 'selected' : ''; ?>>Alterna</option>
                            <option value="Oposta Simples" <?php echo (isset($_POST['filotaxia_folha']) && $_POST['filotaxia_folha'] == 'Oposta Simples') ? 'selected' : ''; ?>>Oposta Simples</option>
                            <option value="Oposta Decussada" <?php echo (isset($_POST['filotaxia_folha']) && $_POST['filotaxia_folha'] == 'Oposta Decussada') ? 'selected' : ''; ?>>Oposta Decussada</option>
                            <option value="Verticilada" <?php echo (isset($_POST['filotaxia_folha']) && $_POST['filotaxia_folha'] == 'Verticilada') ? 'selected' : ''; ?>>Verticilada</option>
                            <option value="Dística" <?php echo (isset($_POST['filotaxia_folha']) && $_POST['filotaxia_folha'] == 'Dística') ? 'selected' : ''; ?>>Dística</option>
                            <option value="Espiralada" <?php echo (isset($_POST['filotaxia_folha']) && $_POST['filotaxia_folha'] == 'Espiralada') ? 'selected' : ''; ?>>Espiralada</option>
                        </select>
                    </div>
                    
                    <div class="filtro-item">
                        <label>Tipo</label>
                        <select name="tipo_folha">
                            <option value="todos" <?php echo (!isset($_POST['tipo_folha']) || $_POST['tipo_folha'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="Simples" <?php echo (isset($_POST['tipo_folha']) && $_POST['tipo_folha'] == 'Simples') ? 'selected' : ''; ?>>Simples</option>
                            <option value="Composta pinnada" <?php echo (isset($_POST['tipo_folha']) && $_POST['tipo_folha'] == 'Composta pinnada') ? 'selected' : ''; ?>>Composta pinnada</option>
                            <option value="Composta bipinada" <?php echo (isset($_POST['tipo_folha']) && $_POST['tipo_folha'] == 'Composta bipinada') ? 'selected' : ''; ?>>Composta bipinada</option>
                            <option value="Composta tripinada" <?php echo (isset($_POST['tipo_folha']) && $_POST['tipo_folha'] == 'Composta tripinada') ? 'selected' : ''; ?>>Composta tripinada</option>
                            <option value="Composta tetrapinada" <?php echo (isset($_POST['tipo_folha']) && $_POST['tipo_folha'] == 'Composta tetrapinada') ? 'selected' : ''; ?>>Composta tetrapinada</option>
                        </select>
                    </div>
                    
                    <div class="filtro-item">
                        <label>Tamanho</label>
                        <select name="tamanho_folha">
                            <option value="todos" <?php echo (!isset($_POST['tamanho_folha']) || $_POST['tamanho_folha'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="Microfilas (< 2 cm)" <?php echo (isset($_POST['tamanho_folha']) && $_POST['tamanho_folha'] == 'Microfilas (< 2 cm)') ? 'selected' : ''; ?>>Microfilas (&lt; 2 cm)</option>
                            <option value="Nanofilas (2–7 cm)" <?php echo (isset($_POST['tamanho_folha']) && $_POST['tamanho_folha'] == 'Nanofilas (2–7 cm)') ? 'selected' : ''; ?>>Nanofilas (2–7 cm)</option>
                            <option value="Mesofilas (7–20 cm)" <?php echo (isset($_POST['tamanho_folha']) && $_POST['tamanho_folha'] == 'Mesofilas (7–20 cm)') ? 'selected' : ''; ?>>Mesofilas (7–20 cm)</option>
                            <option value="Macrófilas (20–50 cm)" <?php echo (isset($_POST['tamanho_folha']) && $_POST['tamanho_folha'] == 'Macrófilas (20–50 cm)') ? 'selected' : ''; ?>>Macrófilas (20–50 cm)</option>
                            <option value="Megafilas (> 50 cm)" <?php echo (isset($_POST['tamanho_folha']) && $_POST['tamanho_folha'] == 'Megafilas (> 50 cm)') ? 'selected' : ''; ?>>Megafilas (&gt; 50 cm)</option>
                        </select>
                    </div>
                    
                    <div class="filtro-item">
                        <label>Textura</label>
                        <select name="textura_folha">
                            <option value="todos" <?php echo (!isset($_POST['textura_folha']) || $_POST['textura_folha'] == 'todos') ? 'selected' : ''; ?>>Todas</option>
                            <option value="Coriácea" <?php echo (isset($_POST['textura_folha']) && $_POST['textura_folha'] == 'Coriácea') ? 'selected' : ''; ?>>Coriácea</option>
                            <option value="Cartácea" <?php echo (isset($_POST['textura_folha']) && $_POST['textura_folha'] == 'Cartácea') ? 'selected' : ''; ?>>Cartácea</option>
                            <option value="Membranácea" <?php echo (isset($_POST['textura_folha']) && $_POST['textura_folha'] == 'Membranácea') ? 'selected' : ''; ?>>Membranácea</option>
                            <option value="Suculenta" <?php echo (isset($_POST['textura_folha']) && $_POST['textura_folha'] == 'Suculenta') ? 'selected' : ''; ?>>Suculenta</option>
                            <option value="Pilosa" <?php echo (isset($_POST['textura_folha']) && $_POST['textura_folha'] == 'Pilosa') ? 'selected' : ''; ?>>Pilosa</option>
                            <option value="Glabra" <?php echo (isset($_POST['textura_folha']) && $_POST['textura_folha'] == 'Glabra') ? 'selected' : ''; ?>>Glabra</option>
                            <option value="Rugosa" <?php echo (isset($_POST['textura_folha']) && $_POST['textura_folha'] == 'Rugosa') ? 'selected' : ''; ?>>Rugosa</option>
                            <option value="Cerosa" <?php echo (isset($_POST['textura_folha']) && $_POST['textura_folha'] == 'Cerosa') ? 'selected' : ''; ?>>Cerosa</option>
                        </select>
                    </div>
                    
                    <div class="filtro-item">
                        <label>Margem</label>
                        <select name="margem_folha">
                            <option value="todos" <?php echo (!isset($_POST['margem_folha']) || $_POST['margem_folha'] == 'todos') ? 'selected' : ''; ?>>Todas</option>
                            <option value="Inteira" <?php echo (isset($_POST['margem_folha']) && $_POST['margem_folha'] == 'Inteira') ? 'selected' : ''; ?>>Inteira</option>
                            <option value="Serrada" <?php echo (isset($_POST['margem_folha']) && $_POST['margem_folha'] == 'Serrada') ? 'selected' : ''; ?>>Serrada</option>
                            <option value="Dentada" <?php echo (isset($_POST['margem_folha']) && $_POST['margem_folha'] == 'Dentada') ? 'selected' : ''; ?>>Dentada</option>
                            <option value="Crenada" <?php echo (isset($_POST['margem_folha']) && $_POST['margem_folha'] == 'Crenada') ? 'selected' : ''; ?>>Crenada</option>
                            <option value="Ondulada" <?php echo (isset($_POST['margem_folha']) && $_POST['margem_folha'] == 'Ondulada') ? 'selected' : ''; ?>>Ondulada</option>
                            <option value="Lobada" <?php echo (isset($_POST['margem_folha']) && $_POST['margem_folha'] == 'Lobada') ? 'selected' : ''; ?>>Lobada</option>
                            <option value="Partida" <?php echo (isset($_POST['margem_folha']) && $_POST['margem_folha'] == 'Partida') ? 'selected' : ''; ?>>Partida</option>
                            <option value="Revoluta" <?php echo (isset($_POST['margem_folha']) && $_POST['margem_folha'] == 'Revoluta') ? 'selected' : ''; ?>>Revoluta</option>
                            <option value="Involuta" <?php echo (isset($_POST['margem_folha']) && $_POST['margem_folha'] == 'Involuta') ? 'selected' : ''; ?>>Involuta</option>
                        </select>
                    </div>

                    <div class="filtro-item">
                        <label>Venação</label>
                        <select name="venacao_folha">
                            <option value="todos" <?php echo (!isset($_POST['venacao_folha']) || $_POST['venacao_folha'] == 'todos') ? 'selected' : ''; ?>>Todas</option>
                            <option value="Reticulada Pinnada" <?php echo (isset($_POST['venacao_folha']) && $_POST['venacao_folha'] == 'Reticulada Pinnada') ? 'selected' : ''; ?>>Reticulada Pinnada</option>
                            <option value="Reticulada Palmada" <?php echo (isset($_POST['venacao_folha']) && $_POST['venacao_folha'] == 'Reticulada Palmada') ? 'selected' : ''; ?>>Reticulada Palmada</option>
                            <option value="Paralela" <?php echo (isset($_POST['venacao_folha']) && $_POST['venacao_folha'] == 'Paralela') ? 'selected' : ''; ?>>Paralela</option>
                            <option value="Peninérvea" <?php echo (isset($_POST['venacao_folha']) && $_POST['venacao_folha'] == 'Peninérvea') ? 'selected' : ''; ?>>Peninérvea</option>
                            <option value="Dicotômica" <?php echo (isset($_POST['venacao_folha']) && $_POST['venacao_folha'] == 'Dicotômica') ? 'selected' : ''; ?>>Dicotômica</option>
                            <option value="Curvinérvea" <?php echo (isset($_POST['venacao_folha']) && $_POST['venacao_folha'] == 'Curvinérvea') ? 'selected' : ''; ?>>Curvinérvea</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- CARACTERÍSTICAS DAS FLORES (CORRIGIDAS) -->
            <div class="secao">
                <h2>🌸 Flores</h2>
                <div class="grid-filtros">
                    <div class="filtro-item">
                        <label>Cor</label>
                        <select name="cor_flores">
                            <option value="todos" <?php echo (!isset($_POST['cor_flores']) || $_POST['cor_flores'] == 'todos') ? 'selected' : ''; ?>>Todas as cores</option>
                            <option value="Brancas" <?php echo (isset($_POST['cor_flores']) && $_POST['cor_flores'] == 'Brancas') ? 'selected' : ''; ?>>Brancas</option>
                            <option value="Amarelas" <?php echo (isset($_POST['cor_flores']) && $_POST['cor_flores'] == 'Amarelas') ? 'selected' : ''; ?>>Amarelas</option>
                            <option value="Vermelhas" <?php echo (isset($_POST['cor_flores']) && $_POST['cor_flores'] == 'Vermelhas') ? 'selected' : ''; ?>>Vermelhas</option>
                            <option value="Rosadas" <?php echo (isset($_POST['cor_flores']) && $_POST['cor_flores'] == 'Rosadas') ? 'selected' : ''; ?>>Rosadas</option>
                            <option value="Roxas" <?php echo (isset($_POST['cor_flores']) && $_POST['cor_flores'] == 'Roxas') ? 'selected' : ''; ?>>Roxas</option>
                            <option value="Azuis" <?php echo (isset($_POST['cor_flores']) && $_POST['cor_flores'] == 'Azuis') ? 'selected' : ''; ?>>Azuis</option>
                            <option value="Laranjas" <?php echo (isset($_POST['cor_flores']) && $_POST['cor_flores'] == 'Laranjas') ? 'selected' : ''; ?>>Laranjas</option>
                            <option value="Verdes" <?php echo (isset($_POST['cor_flores']) && $_POST['cor_flores'] == 'Verdes') ? 'selected' : ''; ?>>Verdes</option>
                        </select>
                    </div>
                    
                    <div class="filtro-item">
                        <label>Simetria</label>
                        <select name="simetria_floral">
                            <option value="todos" <?php echo (!isset($_POST['simetria_floral']) || $_POST['simetria_floral'] == 'todos') ? 'selected' : ''; ?>>Todas</option>
                            <option value="Actinomorfa" <?php echo (isset($_POST['simetria_floral']) && $_POST['simetria_floral'] == 'Actinomorfa') ? 'selected' : ''; ?>>Actinomorfa</option>
                            <option value="Zigomorfa" <?php echo (isset($_POST['simetria_floral']) && $_POST['simetria_floral'] == 'Zigomorfa') ? 'selected' : ''; ?>>Zigomorfa</option>
                            <option value="Assimétrica" <?php echo (isset($_POST['simetria_floral']) && $_POST['simetria_floral'] == 'Assimétrica') ? 'selected' : ''; ?>>Assimétrica</option>
                        </select>
                    </div>
                    
                    <div class="filtro-item">
                        <label>Nº Pétalas</label>
                        <select name="numero_petalas">
                            <option value="todos" <?php echo (!isset($_POST['numero_petalas']) || $_POST['numero_petalas'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="3 pétalas" <?php echo (isset($_POST['numero_petalas']) && $_POST['numero_petalas'] == '3 pétalas') ? 'selected' : ''; ?>>3 pétalas</option>
                            <option value="4 pétalas" <?php echo (isset($_POST['numero_petalas']) && $_POST['numero_petalas'] == '4 pétalas') ? 'selected' : ''; ?>>4 pétalas</option>
                            <option value="5 pétalas" <?php echo (isset($_POST['numero_petalas']) && $_POST['numero_petalas'] == '5 pétalas') ? 'selected' : ''; ?>>5 pétalas</option>
                            <option value="Muitas pétalas" <?php echo (isset($_POST['numero_petalas']) && $_POST['numero_petalas'] == 'Muitas pétalas') ? 'selected' : ''; ?>>Muitas pétalas</option>
                        </select>
                    </div>
                    
                    <div class="filtro-item">
                        <label>Tamanho</label>
                        <select name="tamanho_flor">
                            <option value="todos" <?php echo (!isset($_POST['tamanho_flor']) || $_POST['tamanho_flor'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="Pequena" <?php echo (isset($_POST['tamanho_flor']) && $_POST['tamanho_flor'] == 'Pequena') ? 'selected' : ''; ?>>Pequena</option>
                            <option value="Média" <?php echo (isset($_POST['tamanho_flor']) && $_POST['tamanho_flor'] == 'Média') ? 'selected' : ''; ?>>Média</option>
                        </select>
                    </div>
                    
                    <div class="filtro-item">
                        <label>Disposição</label>
                        <select name="disposicao_flores">
                            <option value="todos" <?php echo (!isset($_POST['disposicao_flores']) || $_POST['disposicao_flores'] == 'todos') ? 'selected' : ''; ?>>Todas</option>
                            <option value="Isoladas" <?php echo (isset($_POST['disposicao_flores']) && $_POST['disposicao_flores'] == 'Isoladas') ? 'selected' : ''; ?>>Isoladas</option>
                            <option value="Inflorescência" <?php echo (isset($_POST['disposicao_flores']) && $_POST['disposicao_flores'] == 'Inflorescência') ? 'selected' : ''; ?>>Inflorescência</option>
                        </select>
                    </div>
                    
                    <div class="filtro-item">
                        <label>Aroma</label>
                        <select name="aroma">
                            <option value="todos" <?php echo (!isset($_POST['aroma']) || $_POST['aroma'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="Sem cheiro" <?php echo (isset($_POST['aroma']) && $_POST['aroma'] == 'Sem cheiro') ? 'selected' : ''; ?>>Sem cheiro</option>
                            <option value="Aroma suave" <?php echo (isset($_POST['aroma']) && $_POST['aroma'] == 'Aroma suave') ? 'selected' : ''; ?>>Aroma suave</option>
                            <option value="Aroma forte" <?php echo (isset($_POST['aroma']) && $_POST['aroma'] == 'Aroma forte') ? 'selected' : ''; ?>>Aroma forte</option>
                            <option value="Aroma desagradável" <?php echo (isset($_POST['aroma']) && $_POST['aroma'] == 'Aroma desagradável') ? 'selected' : ''; ?>>Aroma desagradável</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- CARACTERÍSTICAS DOS FRUTOS (OK) -->
            <div class="secao">
                <h2>🍎 Frutos</h2>
                <div class="grid-filtros">
                    <div class="filtro-item">
                        <label>Tipo</label>
                        <select name="tipo_fruto">
                            <option value="todos" <?php echo (!isset($_POST['tipo_fruto']) || $_POST['tipo_fruto'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="Baga" <?php echo (isset($_POST['tipo_fruto']) && $_POST['tipo_fruto'] == 'Baga') ? 'selected' : ''; ?>>Baga</option>
                            <option value="Drupa" <?php echo (isset($_POST['tipo_fruto']) && $_POST['tipo_fruto'] == 'Drupa') ? 'selected' : ''; ?>>Drupa</option>
                            <option value="Cápsula" <?php echo (isset($_POST['tipo_fruto']) && $_POST['tipo_fruto'] == 'Cápsula') ? 'selected' : ''; ?>>Cápsula</option>
                            <option value="Folículo" <?php echo (isset($_POST['tipo_fruto']) && $_POST['tipo_fruto'] == 'Folículo') ? 'selected' : ''; ?>>Folículo</option>
                            <option value="Legume" <?php echo (isset($_POST['tipo_fruto']) && $_POST['tipo_fruto'] == 'Legume') ? 'selected' : ''; ?>>Legume</option>
                            <option value="Síliqua" <?php echo (isset($_POST['tipo_fruto']) && $_POST['tipo_fruto'] == 'Síliqua') ? 'selected' : ''; ?>>Síliqua</option>
                            <option value="Aquênio" <?php echo (isset($_POST['tipo_fruto']) && $_POST['tipo_fruto'] == 'Aquênio') ? 'selected' : ''; ?>>Aquênio</option>
                            <option value="Sâmara" <?php echo (isset($_POST['tipo_fruto']) && $_POST['tipo_fruto'] == 'Sâmara') ? 'selected' : ''; ?>>Sâmara</option>
                            <option value="Cariopse" <?php echo (isset($_POST['tipo_fruto']) && $_POST['tipo_fruto'] == 'Cariopse') ? 'selected' : ''; ?>>Cariopse</option>
                            <option value="Pixídio" <?php echo (isset($_POST['tipo_fruto']) && $_POST['tipo_fruto'] == 'Pixídio') ? 'selected' : ''; ?>>Pixídio</option>
                            <option value="Hespéridio" <?php echo (isset($_POST['tipo_fruto']) && $_POST['tipo_fruto'] == 'Hespéridio') ? 'selected' : ''; ?>>Hespéridio</option>
                            <option value="Pepo" <?php echo (isset($_POST['tipo_fruto']) && $_POST['tipo_fruto'] == 'Pepo') ? 'selected' : ''; ?>>Pepo</option>
                        </select>
                    </div>
                    
                    <div class="filtro-item">
                        <label>Cor</label>
                        <select name="cor_fruto">
                            <option value="todos" <?php echo (!isset($_POST['cor_fruto']) || $_POST['cor_fruto'] == 'todos') ? 'selected' : ''; ?>>Todas</option>
                            <option value="Verde" <?php echo (isset($_POST['cor_fruto']) && $_POST['cor_fruto'] == 'Verde') ? 'selected' : ''; ?>>Verde</option>
                            <option value="Amarelo" <?php echo (isset($_POST['cor_fruto']) && $_POST['cor_fruto'] == 'Amarelo') ? 'selected' : ''; ?>>Amarelo</option>
                            <option value="Vermelho" <?php echo (isset($_POST['cor_fruto']) && $_POST['cor_fruto'] == 'Vermelho') ? 'selected' : ''; ?>>Vermelho</option>
                            <option value="Roxo" <?php echo (isset($_POST['cor_fruto']) && $_POST['cor_fruto'] == 'Roxo') ? 'selected' : ''; ?>>Roxo</option>
                            <option value="Laranja" <?php echo (isset($_POST['cor_fruto']) && $_POST['cor_fruto'] == 'Laranja') ? 'selected' : ''; ?>>Laranja</option>
                            <option value="Marrom" <?php echo (isset($_POST['cor_fruto']) && $_POST['cor_fruto'] == 'Marrom') ? 'selected' : ''; ?>>Marrom</option>
                            <option value="Preto" <?php echo (isset($_POST['cor_fruto']) && $_POST['cor_fruto'] == 'Preto') ? 'selected' : ''; ?>>Preto</option>
                            <option value="Branco" <?php echo (isset($_POST['cor_fruto']) && $_POST['cor_fruto'] == 'Branco') ? 'selected' : ''; ?>>Branco</option>
                        </select>
                    </div>

                    <div class="filtro-item">
                        <label>Tamanho</label>
                        <select name="tamanho_fruto">
                            <option value="todos" <?php echo (!isset($_POST['tamanho_fruto']) || $_POST['tamanho_fruto'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="Pequeno" <?php echo (isset($_POST['tamanho_fruto']) && $_POST['tamanho_fruto'] == 'Pequeno') ? 'selected' : ''; ?>>Pequeno</option>
                            <option value="Médio" <?php echo (isset($_POST['tamanho_fruto']) && $_POST['tamanho_fruto'] == 'Médio') ? 'selected' : ''; ?>>Médio</option>
                            <option value="Grande" <?php echo (isset($_POST['tamanho_fruto']) && $_POST['tamanho_fruto'] == 'Grande') ? 'selected' : ''; ?>>Grande</option>
                        </select>
                    </div>

                    <div class="filtro-item">
                        <label>Textura</label>
                        <select name="textura_fruto">
                            <option value="todos" <?php echo (!isset($_POST['textura_fruto']) || $_POST['textura_fruto'] == 'todos') ? 'selected' : ''; ?>>Todas</option>
                            <option value="Lisa" <?php echo (isset($_POST['textura_fruto']) && $_POST['textura_fruto'] == 'Lisa') ? 'selected' : ''; ?>>Lisa</option>
                            <option value="Rugosa" <?php echo (isset($_POST['textura_fruto']) && $_POST['textura_fruto'] == 'Rugosa') ? 'selected' : ''; ?>>Rugosa</option>
                            <option value="Coriácea" <?php echo (isset($_POST['textura_fruto']) && $_POST['textura_fruto'] == 'Coriácea') ? 'selected' : ''; ?>>Coriácea</option>
                            <option value="Peluda" <?php echo (isset($_POST['textura_fruto']) && $_POST['textura_fruto'] == 'Peluda') ? 'selected' : ''; ?>>Peluda</option>
                            <option value="Espinhosa" <?php echo (isset($_POST['textura_fruto']) && $_POST['textura_fruto'] == 'Espinhosa') ? 'selected' : ''; ?>>Espinhosa</option>
                            <option value="Cerosa" <?php echo (isset($_POST['textura_fruto']) && $_POST['textura_fruto'] == 'Cerosa') ? 'selected' : ''; ?>>Cerosa</option>
                        </select>
                    </div>

                    <div class="filtro-item">
                        <label>Dispersão</label>
                        <select name="dispersao_fruto">
                            <option value="todos" <?php echo (!isset($_POST['dispersao_fruto']) || $_POST['dispersao_fruto'] == 'todos') ? 'selected' : ''; ?>>Todas</option>
                            <option value="Zoocórica" <?php echo (isset($_POST['dispersao_fruto']) && $_POST['dispersao_fruto'] == 'Zoocórica') ? 'selected' : ''; ?>>Zoocórica (animais)</option>
                            <option value="Anemocórica" <?php echo (isset($_POST['dispersao_fruto']) && $_POST['dispersao_fruto'] == 'Anemocórica') ? 'selected' : ''; ?>>Anemocórica (vento)</option>
                            <option value="Hidrocórica" <?php echo (isset($_POST['dispersao_fruto']) && $_POST['dispersao_fruto'] == 'Hidrocórica') ? 'selected' : ''; ?>>Hidrocórica (água)</option>
                            <option value="Autocórica" <?php echo (isset($_POST['dispersao_fruto']) && $_POST['dispersao_fruto'] == 'Autocórica') ? 'selected' : ''; ?>>Autocórica (própria planta)</option>
                        </select>
                    </div>

                    <div class="filtro-item">
                        <label>Aroma do fruto</label>
                        <select name="aroma_fruto">
                            <option value="todos" <?php echo (!isset($_POST['aroma_fruto']) || $_POST['aroma_fruto'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="Sem cheiro" <?php echo (isset($_POST['aroma_fruto']) && $_POST['aroma_fruto'] == 'Sem cheiro') ? 'selected' : ''; ?>>Sem cheiro</option>
                            <option value="Aroma suave" <?php echo (isset($_POST['aroma_fruto']) && $_POST['aroma_fruto'] == 'Aroma suave') ? 'selected' : ''; ?>>Aroma suave</option>
                            <option value="Aroma forte" <?php echo (isset($_POST['aroma_fruto']) && $_POST['aroma_fruto'] == 'Aroma forte') ? 'selected' : ''; ?>>Aroma forte</option>
                            <option value="Aroma desagradável" <?php echo (isset($_POST['aroma_fruto']) && $_POST['aroma_fruto'] == 'Aroma desagradável') ? 'selected' : ''; ?>>Aroma desagradável</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- CARACTERÍSTICAS DO CAULE -->
            <div class="secao">
                <h2>🌿 Caule</h2>
                <div class="grid-filtros">
                    <div class="filtro-item">
                        <label>Tipo</label>
                        <select name="tipo_caule">
                            <option value="todos" <?php echo (!isset($_POST['tipo_caule']) || $_POST['tipo_caule'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="Ereto" <?php echo (isset($_POST['tipo_caule']) && $_POST['tipo_caule'] == 'Ereto') ? 'selected' : ''; ?>>Ereto</option>
                            <option value="Prostrado" <?php echo (isset($_POST['tipo_caule']) && $_POST['tipo_caule'] == 'Prostrado') ? 'selected' : ''; ?>>Prostrado</option>
                            <option value="Trepador" <?php echo (isset($_POST['tipo_caule']) && $_POST['tipo_caule'] == 'Trepador') ? 'selected' : ''; ?>>Trepador</option>
                            <option value="Rastejante" <?php echo (isset($_POST['tipo_caule']) && $_POST['tipo_caule'] == 'Rastejante') ? 'selected' : ''; ?>>Rastejante</option>
                            <option value="Subterrâneo" <?php echo (isset($_POST['tipo_caule']) && $_POST['tipo_caule'] == 'Subterrâneo') ? 'selected' : ''; ?>>Subterrâneo</option>
                        </select>
                    </div>
                    <div class="filtro-item">
                        <label>Estrutura</label>
                        <select name="estrutura_caule">
                            <option value="todos" <?php echo (!isset($_POST['estrutura_caule']) || $_POST['estrutura_caule'] == 'todos') ? 'selected' : ''; ?>>Todas</option>
                            <option value="Lenhoso" <?php echo (isset($_POST['estrutura_caule']) && $_POST['estrutura_caule'] == 'Lenhoso') ? 'selected' : ''; ?>>Lenhoso</option>
                            <option value="Herbáceo" <?php echo (isset($_POST['estrutura_caule']) && $_POST['estrutura_caule'] == 'Herbáceo') ? 'selected' : ''; ?>>Herbáceo</option>
                            <option value="Suculento" <?php echo (isset($_POST['estrutura_caule']) && $_POST['estrutura_caule'] == 'Suculento') ? 'selected' : ''; ?>>Suculento</option>
                        </select>
                    </div>
                    <div class="filtro-item">
                        <label>Textura</label>
                        <select name="textura_caule">
                            <option value="todos" <?php echo (!isset($_POST['textura_caule']) || $_POST['textura_caule'] == 'todos') ? 'selected' : ''; ?>>Todas</option>
                            <option value="Lisa" <?php echo (isset($_POST['textura_caule']) && $_POST['textura_caule'] == 'Lisa') ? 'selected' : ''; ?>>Lisa</option>
                            <option value="Rugosa" <?php echo (isset($_POST['textura_caule']) && $_POST['textura_caule'] == 'Rugosa') ? 'selected' : ''; ?>>Rugosa</option>
                            <option value="Sulcada" <?php echo (isset($_POST['textura_caule']) && $_POST['textura_caule'] == 'Sulcada') ? 'selected' : ''; ?>>Sulcada</option>
                            <option value="Fissurada" <?php echo (isset($_POST['textura_caule']) && $_POST['textura_caule'] == 'Fissurada') ? 'selected' : ''; ?>>Fissurada</option>
                            <option value="Cerosa" <?php echo (isset($_POST['textura_caule']) && $_POST['textura_caule'] == 'Cerosa') ? 'selected' : ''; ?>>Cerosa</option>
                            <option value="Espinhosa" <?php echo (isset($_POST['textura_caule']) && $_POST['textura_caule'] == 'Espinhosa') ? 'selected' : ''; ?>>Espinhosa</option>
                            <option value="Suberosa" <?php echo (isset($_POST['textura_caule']) && $_POST['textura_caule'] == 'Suberosa') ? 'selected' : ''; ?>>Suberosa</option>
                        </select>
                    </div>
                    <div class="filtro-item">
                        <label>Cor</label>
                        <select name="cor_caule">
                            <option value="todos" <?php echo (!isset($_POST['cor_caule']) || $_POST['cor_caule'] == 'todos') ? 'selected' : ''; ?>>Todas</option>
                            <option value="Marrom" <?php echo (isset($_POST['cor_caule']) && $_POST['cor_caule'] == 'Marrom') ? 'selected' : ''; ?>>Marrom</option>
                            <option value="Verde" <?php echo (isset($_POST['cor_caule']) && $_POST['cor_caule'] == 'Verde') ? 'selected' : ''; ?>>Verde</option>
                            <option value="Cinza" <?php echo (isset($_POST['cor_caule']) && $_POST['cor_caule'] == 'Cinza') ? 'selected' : ''; ?>>Cinza</option>
                            <option value="Avermelhado" <?php echo (isset($_POST['cor_caule']) && $_POST['cor_caule'] == 'Avermelhado') ? 'selected' : ''; ?>>Avermelhado</option>
                            <option value="Alaranjado" <?php echo (isset($_POST['cor_caule']) && $_POST['cor_caule'] == 'Alaranjado') ? 'selected' : ''; ?>>Alaranjado</option>
                        </select>
                    </div>
                    <div class="filtro-item">
                        <label>Forma</label>
                        <select name="forma_caule">
                            <option value="todos" <?php echo (!isset($_POST['forma_caule']) || $_POST['forma_caule'] == 'todos') ? 'selected' : ''; ?>>Todas</option>
                            <option value="Cilíndrico" <?php echo (isset($_POST['forma_caule']) && $_POST['forma_caule'] == 'Cilíndrico') ? 'selected' : ''; ?>>Cilíndrico</option>
                            <option value="Quadrangular" <?php echo (isset($_POST['forma_caule']) && $_POST['forma_caule'] == 'Quadrangular') ? 'selected' : ''; ?>>Quadrangular</option>
                            <option value="Achatado" <?php echo (isset($_POST['forma_caule']) && $_POST['forma_caule'] == 'Achatado') ? 'selected' : ''; ?>>Achatado</option>
                            <option value="Irregular" <?php echo (isset($_POST['forma_caule']) && $_POST['forma_caule'] == 'Irregular') ? 'selected' : ''; ?>>Irregular</option>
                        </select>
                    </div>
                    <div class="filtro-item">
                        <label>Diâmetro</label>
                        <select name="diametro_caule">
                            <option value="todos" <?php echo (!isset($_POST['diametro_caule']) || $_POST['diametro_caule'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="Fino" <?php echo (isset($_POST['diametro_caule']) && $_POST['diametro_caule'] == 'Fino') ? 'selected' : ''; ?>>Fino</option>
                            <option value="Médio" <?php echo (isset($_POST['diametro_caule']) && $_POST['diametro_caule'] == 'Médio') ? 'selected' : ''; ?>>Médio</option>
                            <option value="Grosso" <?php echo (isset($_POST['diametro_caule']) && $_POST['diametro_caule'] == 'Grosso') ? 'selected' : ''; ?>>Grosso</option>
                        </select>
                    </div>
                    <div class="filtro-item">
                        <label>Ramificação</label>
                        <select name="ramificacao_caule">
                            <option value="todos" <?php echo (!isset($_POST['ramificacao_caule']) || $_POST['ramificacao_caule'] == 'todos') ? 'selected' : ''; ?>>Todas</option>
                            <option value="Dicotômica" <?php echo (isset($_POST['ramificacao_caule']) && $_POST['ramificacao_caule'] == 'Dicotômica') ? 'selected' : ''; ?>>Dicotômica</option>
                            <option value="Monopodial" <?php echo (isset($_POST['ramificacao_caule']) && $_POST['ramificacao_caule'] == 'Monopodial') ? 'selected' : ''; ?>>Monopodial</option>
                            <option value="Simpodial" <?php echo (isset($_POST['ramificacao_caule']) && $_POST['ramificacao_caule'] == 'Simpodial') ? 'selected' : ''; ?>>Simpodial</option>
                        </select>
                    </div>
                    <div class="filtro-item">
                        <label>Modificação</label>
                        <select name="modificacao_caule">
                            <option value="todos" <?php echo (!isset($_POST['modificacao_caule']) || $_POST['modificacao_caule'] == 'todos') ? 'selected' : ''; ?>>Todas</option>
                            <option value="Estolão" <?php echo (isset($_POST['modificacao_caule']) && $_POST['modificacao_caule'] == 'Estolão') ? 'selected' : ''; ?>>Estolão</option>
                            <option value="Cladódio" <?php echo (isset($_POST['modificacao_caule']) && $_POST['modificacao_caule'] == 'Cladódio') ? 'selected' : ''; ?>>Cladódio</option>
                            <option value="Rizoma" <?php echo (isset($_POST['modificacao_caule']) && $_POST['modificacao_caule'] == 'Rizoma') ? 'selected' : ''; ?>>Rizoma</option>
                            <option value="Tubérculo" <?php echo (isset($_POST['modificacao_caule']) && $_POST['modificacao_caule'] == 'Tubérculo') ? 'selected' : ''; ?>>Tubérculo</option>
                            <option value="Espinhos" <?php echo (isset($_POST['modificacao_caule']) && $_POST['modificacao_caule'] == 'Espinhos') ? 'selected' : ''; ?>>Espinhos</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- CARACTERÍSTICAS DA SEMENTE -->
            <div class="secao">
                <h2>🌱 Semente</h2>
                <div class="grid-filtros">
                    <div class="filtro-item">
                        <label>Tipo</label>
                        <select name="tipo_semente">
                            <option value="todos" <?php echo (!isset($_POST['tipo_semente']) || $_POST['tipo_semente'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="Alada" <?php echo (isset($_POST['tipo_semente']) && $_POST['tipo_semente'] == 'Alada') ? 'selected' : ''; ?>>Alada</option>
                            <option value="Carnosa" <?php echo (isset($_POST['tipo_semente']) && $_POST['tipo_semente'] == 'Carnosa') ? 'selected' : ''; ?>>Carnosa</option>
                            <option value="Dura" <?php echo (isset($_POST['tipo_semente']) && $_POST['tipo_semente'] == 'Dura') ? 'selected' : ''; ?>>Dura</option>
                            <option value="Oleosa" <?php echo (isset($_POST['tipo_semente']) && $_POST['tipo_semente'] == 'Oleosa') ? 'selected' : ''; ?>>Oleosa</option>
                            <option value="Peluda" <?php echo (isset($_POST['tipo_semente']) && $_POST['tipo_semente'] == 'Peluda') ? 'selected' : ''; ?>>Peluda</option>
                        </select>
                    </div>
                    <div class="filtro-item">
                        <label>Tamanho</label>
                        <select name="tamanho_semente">
                            <option value="todos" <?php echo (!isset($_POST['tamanho_semente']) || $_POST['tamanho_semente'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="Pequena" <?php echo (isset($_POST['tamanho_semente']) && $_POST['tamanho_semente'] == 'Pequena') ? 'selected' : ''; ?>>Pequena (&lt; 5 mm)</option>
                            <option value="Média" <?php echo (isset($_POST['tamanho_semente']) && $_POST['tamanho_semente'] == 'Média') ? 'selected' : ''; ?>>Média</option>
                            <option value="Grande" <?php echo (isset($_POST['tamanho_semente']) && $_POST['tamanho_semente'] == 'Grande') ? 'selected' : ''; ?>>Grande (&gt; 10 mm)</option>
                        </select>
                    </div>
                    <div class="filtro-item">
                        <label>Cor</label>
                        <select name="cor_semente">
                            <option value="todos" <?php echo (!isset($_POST['cor_semente']) || $_POST['cor_semente'] == 'todos') ? 'selected' : ''; ?>>Todas</option>
                            <option value="Preta" <?php echo (isset($_POST['cor_semente']) && $_POST['cor_semente'] == 'Preta') ? 'selected' : ''; ?>>Preta</option>
                            <option value="Marrom" <?php echo (isset($_POST['cor_semente']) && $_POST['cor_semente'] == 'Marrom') ? 'selected' : ''; ?>>Marrom</option>
                            <option value="Branca" <?php echo (isset($_POST['cor_semente']) && $_POST['cor_semente'] == 'Branca') ? 'selected' : ''; ?>>Branca</option>
                            <option value="Amarela" <?php echo (isset($_POST['cor_semente']) && $_POST['cor_semente'] == 'Amarela') ? 'selected' : ''; ?>>Amarela</option>
                            <option value="Verde" <?php echo (isset($_POST['cor_semente']) && $_POST['cor_semente'] == 'Verde') ? 'selected' : ''; ?>>Verde</option>
                        </select>
                    </div>
                    <div class="filtro-item">
                        <label>Textura</label>
                        <select name="textura_semente">
                            <option value="todos" <?php echo (!isset($_POST['textura_semente']) || $_POST['textura_semente'] == 'todos') ? 'selected' : ''; ?>>Todas</option>
                            <option value="Lisa" <?php echo (isset($_POST['textura_semente']) && $_POST['textura_semente'] == 'Lisa') ? 'selected' : ''; ?>>Lisa</option>
                            <option value="Rugosa" <?php echo (isset($_POST['textura_semente']) && $_POST['textura_semente'] == 'Rugosa') ? 'selected' : ''; ?>>Rugosa</option>
                            <option value="Estriada" <?php echo (isset($_POST['textura_semente']) && $_POST['textura_semente'] == 'Estriada') ? 'selected' : ''; ?>>Estriada</option>
                            <option value="Cerosa" <?php echo (isset($_POST['textura_semente']) && $_POST['textura_semente'] == 'Cerosa') ? 'selected' : ''; ?>>Cerosa</option>
                        </select>
                    </div>
                    <div class="filtro-item">
                        <label>Quantidade por fruto</label>
                        <select name="quantidade_sementes">
                            <option value="todos" <?php echo (!isset($_POST['quantidade_sementes']) || $_POST['quantidade_sementes'] == 'todos') ? 'selected' : ''; ?>>Todas</option>
                            <option value="Uma" <?php echo (isset($_POST['quantidade_sementes']) && $_POST['quantidade_sementes'] == 'Uma') ? 'selected' : ''; ?>>Uma</option>
                            <option value="Poucas" <?php echo (isset($_POST['quantidade_sementes']) && $_POST['quantidade_sementes'] == 'Poucas') ? 'selected' : ''; ?>>Poucas</option>
                            <option value="Muitas" <?php echo (isset($_POST['quantidade_sementes']) && $_POST['quantidade_sementes'] == 'Muitas') ? 'selected' : ''; ?>>Muitas</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- OUTRAS CARACTERÍSTICAS -->
            <div class="secao">
                <h2>⚡ Outras</h2>
                <div class="grid-filtros">
                    <div class="filtro-item">
                        <label>Possui Espinhos?</label>
                        <select name="possui_espinhos">
                            <option value="todos" <?php echo (!isset($_POST['possui_espinhos']) || $_POST['possui_espinhos'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="Sim" <?php echo (isset($_POST['possui_espinhos']) && $_POST['possui_espinhos'] == 'Sim') ? 'selected' : ''; ?>>Sim</option>
                            <option value="Não" <?php echo (isset($_POST['possui_espinhos']) && $_POST['possui_espinhos'] == 'Não') ? 'selected' : ''; ?>>Não</option>
                        </select>
                    </div>
                    
                    <div class="filtro-item">
                        <label>Possui Látex?</label>
                        <select name="possui_latex">
                            <option value="todos" <?php echo (!isset($_POST['possui_latex']) || $_POST['possui_latex'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="Sim" <?php echo (isset($_POST['possui_latex']) && $_POST['possui_latex'] == 'Sim') ? 'selected' : ''; ?>>Sim</option>
                            <option value="Não" <?php echo (isset($_POST['possui_latex']) && $_POST['possui_latex'] == 'Não') ? 'selected' : ''; ?>>Não</option>
                        </select>
                    </div>

                    <div class="filtro-item">
                        <label>Possui Seiva?</label>
                        <select name="possui_seiva">
                            <option value="todos" <?php echo (!isset($_POST['possui_seiva']) || $_POST['possui_seiva'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="Sim" <?php echo (isset($_POST['possui_seiva']) && $_POST['possui_seiva'] == 'Sim') ? 'selected' : ''; ?>>Sim</option>
                            <option value="Não" <?php echo (isset($_POST['possui_seiva']) && $_POST['possui_seiva'] == 'Não') ? 'selected' : ''; ?>>Não</option>
                        </select>
                    </div>

                    <div class="filtro-item">
                        <label>Possui Resina?</label>
                        <select name="possui_resina">
                            <option value="todos" <?php echo (!isset($_POST['possui_resina']) || $_POST['possui_resina'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="Sim" <?php echo (isset($_POST['possui_resina']) && $_POST['possui_resina'] == 'Sim') ? 'selected' : ''; ?>>Sim</option>
                            <option value="Não" <?php echo (isset($_POST['possui_resina']) && $_POST['possui_resina'] == 'Não') ? 'selected' : ''; ?>>Não</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Botões também ao final para conveniência -->
            <div class="acoes-busca">
                <button type="submit" class="btn btn-buscar">
                    🔎 BUSCAR ESPÉCIES
                </button>
                <button type="button" class="btn btn-limpar" onclick="window.location.href='/penomato_mvp/src/Views/publico/busca_caracteristicas.php'">
                    🧹 LIMPAR FILTROS
                </button>
            </div>
        </form>
        
        
    </div>
    
</body>
</html>