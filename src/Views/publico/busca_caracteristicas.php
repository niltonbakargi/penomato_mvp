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
        'familia' => '=',
        
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
                c.id,
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f0f2f5;
            padding: 30px 20px;
            color: #1a2634;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        h1 {
            color: #0b5e42;
            font-weight: 600;
            border-bottom: 3px solid #0b5e42;
            padding-bottom: 15px;
            display: inline-block;
        }
        
        .btn-voltar {
            background: #e2e8f0;
            color: #1e293b;
            padding: 10px 20px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-voltar:hover {
            background: #cbd5e1;
        }
        
        .form-busca {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
            margin-bottom: 30px;
        }
        
        .secao {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 6px solid #0b5e42;
        }
        
        .secao h2 {
            font-size: 1.4rem;
            color: #0b5e42;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .grid-filtros {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .filtro-item {
            display: flex;
            flex-direction: column;
        }
        
        .filtro-item label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 6px;
        }
        
        select, input[type="text"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.2s;
            background-color: white;
        }
        
        select:hover, input[type="text"]:hover {
            border-color: #94a3b8;
        }
        
        select:focus, input[type="text"]:focus {
            outline: none;
            border-color: #0b5e42;
            box-shadow: 0 0 0 3px rgba(11,94,66,0.1);
        }
        
        .acoes-busca {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 14px 32px;
            border: none;
            border-radius: 40px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-buscar {
            background: #0b5e42;
            color: white;
            box-shadow: 0 4px 12px rgba(11,94,66,0.3);
        }
        
        .btn-buscar:hover {
            background: #0a4c35;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(11,94,66,0.4);
        }
        
        .btn-limpar {
            background: #e2e8f0;
            color: #1e293b;
        }
        
        .btn-limpar:hover {
            background: #cbd5e1;
        }
        
        .resultados {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }
        
        .contador {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .badge-total {
            background: #0b5e42;
            color: white;
            padding: 10px 24px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .btn-mostrar {
            background: #2563eb;
            color: white;
            padding: 12px 28px;
            border: none;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-mostrar:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }
        
        .sem-resultados {
            text-align: center;
            padding: 50px 20px;
            background: #fef2f2;
            border-radius: 16px;
            color: #991b1b;
        }
        
        .sem-resultados p {
            margin: 10px 0;
            font-size: 1.1rem;
        }
        
        .sugestao {
            color: #4b5563;
            font-style: italic;
            margin-top: 15px;
        }
        
        .lista-especies {
            list-style: none;
            margin-top: 20px;
        }
        
        .especie-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            transition: background 0.2s;
        }
        
        .especie-item:hover {
            background: #f8fafc;
        }
        
        .especie-info {
            flex: 1;
        }
        
        .especie-nome {
            font-size: 1.1rem;
            font-weight: 600;
            color: #0b5e42;
        }
        
        .especie-detalhes {
            font-size: 0.9rem;
            color: #4b5563;
            margin-top: 4px;
        }
        
        .especie-link {
            background: #e2e8f0;
            padding: 8px 20px;
            border-radius: 30px;
            color: #1e293b;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .especie-link:hover {
            background: #0b5e42;
            color: white;
        }
        
        .paginacao {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        
        .page-link {
            padding: 10px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            text-decoration: none;
            color: #1e293b;
            font-weight: 500;
            transition: all 0.2s;
            background: white;
            cursor: pointer;
        }
        
        .page-link:hover {
            background: #0b5e42;
            color: white;
            border-color: #0b5e42;
        }
        
        .page-link.ativa {
            background: #0b5e42;
            color: white;
            border-color: #0b5e42;
        }
        
        .page-disabled {
            padding: 10px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            color: #94a3b8;
            background: #f1f5f9;
        }
        
        .erro-mensagem {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc2626;
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
        
        <form method="POST" action="" class="form-busca">
            
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
                            <option value="Oval" <?php echo (isset($_POST['forma_folha']) && $_POST['forma_folha'] == 'Oval') ? 'selected' : ''; ?>>Oval</option>
                            <option value="Orbicular" <?php echo (isset($_POST['forma_folha']) && $_POST['forma_folha'] == 'Orbicular') ? 'selected' : ''; ?>>Orbicular</option>
                            <option value="Cordiforme" <?php echo (isset($_POST['forma_folha']) && $_POST['forma_folha'] == 'Cordiforme') ? 'selected' : ''; ?>>Cordiforme</option>
                            <option value="Obovada" <?php echo (isset($_POST['forma_folha']) && $_POST['forma_folha'] == 'Obovada') ? 'selected' : ''; ?>>Obovada</option>
                            <option value="Trilobada" <?php echo (isset($_POST['forma_folha']) && $_POST['forma_folha'] == 'Trilobada') ? 'selected' : ''; ?>>Trilobada</option>
                        </select>
                    </div>
                    
                    <div class="filtro-item">
                        <label>Filotaxia</label>
                        <select name="filotaxia_folha">
                            <option value="todos" <?php echo (!isset($_POST['filotaxia_folha']) || $_POST['filotaxia_folha'] == 'todos') ? 'selected' : ''; ?>>Todas</option>
                            <option value="Alterna" <?php echo (isset($_POST['filotaxia_folha']) && $_POST['filotaxia_folha'] == 'Alterna') ? 'selected' : ''; ?>>Alterna</option>
                            <option value="Oposta" <?php echo (isset($_POST['filotaxia_folha']) && $_POST['filotaxia_folha'] == 'Oposta') ? 'selected' : ''; ?>>Oposta</option>
                            <option value="Verticilada" <?php echo (isset($_POST['filotaxia_folha']) && $_POST['filotaxia_folha'] == 'Verticilada') ? 'selected' : ''; ?>>Verticilada</option>
                        </select>
                    </div>
                    
                    <div class="filtro-item">
                        <label>Tipo</label>
                        <select name="tipo_folha">
                            <option value="todos" <?php echo (!isset($_POST['tipo_folha']) || $_POST['tipo_folha'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="Simples" <?php echo (isset($_POST['tipo_folha']) && $_POST['tipo_folha'] == 'Simples') ? 'selected' : ''; ?>>Simples</option>
                            <option value="Composta" <?php echo (isset($_POST['tipo_folha']) && $_POST['tipo_folha'] == 'Composta') ? 'selected' : ''; ?>>Composta</option>
                        </select>
                    </div>
                    
                    <div class="filtro-item">
                        <label>Tamanho</label>
                        <select name="tamanho_folha">
                            <option value="todos" <?php echo (!isset($_POST['tamanho_folha']) || $_POST['tamanho_folha'] == 'todos') ? 'selected' : ''; ?>>Todos</option>
                            <option value="Microfilos (< 2 cm)" <?php echo (isset($_POST['tamanho_folha']) && $_POST['tamanho_folha'] == 'Microfilos (< 2 cm)') ? 'selected' : ''; ?>>Microfilos (&lt; 2 cm)</option>
                            <option value="Nanofilos (2–7 cm)" <?php echo (isset($_POST['tamanho_folha']) && $_POST['tamanho_folha'] == 'Nanofilos (2–7 cm)') ? 'selected' : ''; ?>>Nanofilos (2–7 cm)</option>
                            <option value="Mesofilos (7–20 cm)" <?php echo (isset($_POST['tamanho_folha']) && $_POST['tamanho_folha'] == 'Mesofilos (7–20 cm)') ? 'selected' : ''; ?>>Mesofilos (7–20 cm)</option>
                            <option value="Macrófilos (20–50 cm)" <?php echo (isset($_POST['tamanho_folha']) && $_POST['tamanho_folha'] == 'Macrófilos (20–50 cm)') ? 'selected' : ''; ?>>Macrófilos (20–50 cm)</option>
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
                            <option value="Legume" <?php echo (isset($_POST['tipo_fruto']) && $_POST['tipo_fruto'] == 'Legume') ? 'selected' : ''; ?>>Legume</option>
                            <option value="Aquênio" <?php echo (isset($_POST['tipo_fruto']) && $_POST['tipo_fruto'] == 'Aquênio') ? 'selected' : ''; ?>>Aquênio</option>
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
                </div>
            </div>
            
            <div class="acoes-busca">
                <button type="submit" name="buscar" value="1" class="btn btn-buscar">
                    🔎 CONTAR ESPÉCIES
                </button>
                <button type="button" class="btn btn-limpar" onclick="window.location.href='/penomato_mvp/src/Views/publico/busca_caracteristicas.php'">
                    🧹 LIMPAR FILTROS
                </button>
            </div>
        </form>
        
        <?php if ($filtros_aplicados): ?>
        <div class="resultados">
            
            <div class="contador">
                <span class="badge-total">
                    <?php echo $total_encontrado; ?> espécie<?php echo $total_encontrado != 1 ? 's' : ''; ?> encontrada<?php echo $total_encontrado != 1 ? 's' : ''; ?>
                </span>
                
                <?php if ($total_encontrado > 0): ?>
                <form method="POST" action="" style="display: inline;" id="formMostrarLista">
                    <?php
                    foreach ($_POST as $key => $value) {
                        if ($key !== 'buscar' && $key !== 'pagina' && $value !== '' && $value !== 'todos') {
                            echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                        }
                    }
                    ?>
                    <input type="hidden" name="buscar" value="1">
                    <input type="hidden" name="mostrar_lista" value="1">
                    <button type="submit" class="btn-mostrar">
                        📋 MOSTRAR LISTA (100 por página)
                    </button>
                </form>
                <?php endif; ?>
            </div>
            
            <?php if ($total_encontrado === 0): ?>
            <div class="sem-resultados">
                <p style="font-size: 2rem; margin-bottom: 10px;">😕</p>
                <p><strong>Nenhuma espécie encontrada</strong></p>
                <p>Tente remover alguns filtros ou usar termos mais gerais.</p>
                <p class="sugestao">💡 Exemplo: busque apenas por "Folha Lanceolada"</p>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_POST['mostrar_lista']) && $total_encontrado > 0): ?>
                
                <?php if (count($especies_encontradas) > 0): ?>
                <ul class="lista-especies">
                    <?php foreach ($especies_encontradas as $especie): ?>
                    <li class="especie-item">
                        <div class="especie-info">
                            <div class="especie-nome">
                                <?php echo htmlspecialchars($especie['nome_cientifico']); ?>
                            </div>
                            <?php if (!empty($especie['nome_popular']) || !empty($especie['familia'])): ?>
                            <div class="especie-detalhes">
                                <?php 
                                $detalhes = [];
                                if (!empty($especie['nome_popular'])) {
                                    $detalhes[] = 'Popular: ' . htmlspecialchars($especie['nome_popular']);
                                }
                                if (!empty($especie['familia'])) {
                                    $detalhes[] = 'Família: ' . htmlspecialchars($especie['familia']);
                                }
                                echo implode(' · ', $detalhes);
                                ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <a href="/penomato_mvp/src/Views/publico/especie_detalhes.php?id=<?php echo $especie['id']; ?>" class="especie-link">
                            Ver detalhes →
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                
                <?php if ($total_encontrado > 100): 
                    $total_paginas = ceil($total_encontrado / 100);
                    $max_paginas_mostradas = 10;
                    $inicio_paginacao = max(1, $pagina_atual - floor($max_paginas_mostradas / 2));
                    $fim_paginacao = min($total_paginas, $inicio_paginacao + $max_paginas_mostradas - 1);
                ?>
                <div class="paginacao">
                    <?php if ($pagina_atual > 1): ?>
                    <form method="POST" action="" style="display: inline;">
                        <?php foreach ($_POST as $key => $value): ?>
                            <?php if ($key !== 'pagina'): ?>
                            <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <input type="hidden" name="pagina" value="1">
                        <input type="hidden" name="buscar" value="1">
                        <input type="hidden" name="mostrar_lista" value="1">
                        <button type="submit" class="page-link">«</button>
                    </form>
                    <?php else: ?>
                    <span class="page-disabled">«</span>
                    <?php endif; ?>
                    
                    <?php for ($i = $inicio_paginacao; $i <= $fim_paginacao; $i++): ?>
                        <?php if ($i == $pagina_atual): ?>
                            <span class="page-link ativa"><?php echo $i; ?></span>
                        <?php else: ?>
                        <form method="POST" action="" style="display: inline;">
                            <?php foreach ($_POST as $key => $value): ?>
                                <?php if ($key !== 'pagina'): ?>
                                <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <input type="hidden" name="pagina" value="<?php echo $i; ?>">
                            <input type="hidden" name="buscar" value="1">
                            <input type="hidden" name="mostrar_lista" value="1">
                            <button type="submit" class="page-link"><?php echo $i; ?></button>
                        </form>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($pagina_atual < $total_paginas): ?>
                    <form method="POST" action="" style="display: inline;">
                        <?php foreach ($_POST as $key => $value): ?>
                            <?php if ($key !== 'pagina'): ?>
                            <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <input type="hidden" name="pagina" value="<?php echo $total_paginas; ?>">
                        <input type="hidden" name="buscar" value="1">
                        <input type="hidden" name="mostrar_lista" value="1">
                        <button type="submit" class="page-link">»</button>
                    </form>
                    <?php else: ?>
                    <span class="page-disabled">»</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <p style="text-align: center; padding: 30px; color: #4b5563;">
                    Nenhuma espécie para exibir nesta página.
                </p>
                <?php endif; ?>
            <?php endif; ?>
            
        </div>
        <?php endif; ?>
        
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($filtros_aplicados): ?>
            setTimeout(function() {
                const resultados = document.querySelector('.resultados');
                if (resultados) {
                    resultados.scrollIntoView({ behavior: 'smooth' });
                }
            }, 100);
            <?php endif; ?>
        });
    </script>
</body>
</html>