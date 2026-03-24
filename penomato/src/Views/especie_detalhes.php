<?php
// ================================================
// DETALHES COMPLETOS DA ESPÉCIE
// ================================================

session_start();
ob_start();

// Configurações do banco
$servidor = "127.0.0.1";
$usuario = "root";
$senha = "";
$banco = "penomato";

// ================================================
// BUSCAR DADOS DA ESPÉCIE
// ================================================

$especie = null;
$erro = null;
$id_especie = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_especie <= 0) {
    $erro = 'ID de espécie inválido';
} else {
    $conexao = mysqli_connect($servidor, $usuario, $senha, $banco);
    
    if (!$conexao) {
        $erro = 'Erro ao conectar ao banco de dados';
    } else {
        mysqli_set_charset($conexao, "utf8mb4");
        
        $sql = "SELECT * FROM especies_caracteristicas WHERE id = ?";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id_especie);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($resultado) > 0) {
            $especie = mysqli_fetch_assoc($resultado);
        } else {
            $erro = 'Espécie não encontrada';
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($conexao);
    }
}

ob_end_clean();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $especie ? htmlspecialchars($especie['nome_cientifico']) : 'Espécie não encontrada'; ?> - Penomato</title>
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
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header-bar {
            background: white;
            border-radius: 16px;
            padding: 25px 30px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .titulo-principal {
            color: var(--cor-primaria);
            font-size: 1.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .btn-voltar {
            background: #e2e8f0;
            color: #1e293b;
            padding: 12px 28px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .btn-voltar:hover {
            background: #cbd5e1;
            transform: translateY(-2px);
        }
        
        .card-erro {
            background: white;
            border-radius: 16px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }
        
        .card-erro h2 {
            color: #991b1b;
            font-size: 2rem;
            margin-bottom: 20px;
        }
        
        .card-erro p {
            color: #4b5563;
            margin-bottom: 30px;
            font-size: 1.2rem;
        }
        
        .ficha-especie {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
        }
        
        .card-identificacao {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
            border-left: 8px solid var(--cor-primaria);
        }
        
        .nome-cientifico {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--cor-primaria);
            margin-bottom: 10px;
            line-height: 1.2;
        }
        
        .nome-popular {
            font-size: 1.4rem;
            color: #2c3e50;
            margin-bottom: 8px;
            font-style: italic;
        }
        
        .familia {
            font-size: 1.2rem;
            color: #4b5563;
            margin-bottom: 5px;
        }
        
        .grid-caracteristicas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .card-categoria {
            background: white;
            border-radius: 16px;
            padding: 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #edf2f7;
        }
        
        .card-categoria:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.08);
        }
        
        .categoria-titulo {
            background: linear-gradient(135deg, var(--cor-primaria) 0%, #0a4c35 100%);
            color: white;
            padding: 18px 22px;
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .categoria-conteudo {
            padding: 22px;
        }
        
        .tabela-caracteristicas {
            width: 100%;
            border-collapse: collapse;
        }
        
        .tabela-caracteristicas tr {
            border-bottom: 1px solid #edf2f7;
        }
        
        .tabela-caracteristicas tr:last-child {
            border-bottom: none;
        }
        
        .tabela-caracteristicas td {
            padding: 12px 8px;
            vertical-align: top;
        }
        
        .tabela-caracteristicas td:first-child {
            font-weight: 600;
            color: #2c3e50;
            width: 40%;
            background-color: #f8fafc;
            border-radius: 8px 0 0 8px;
            padding-left: 16px;
        }
        
        .tabela-caracteristicas td:last-child {
            color: #1e293b;
            width: 60%;
            padding-right: 16px;
        }
        
        .valor-nao-informado {
            color: #94a3b8;
            font-style: italic;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .referencia-badge {
            display: inline-block;
            background: #e2e8f0;
            color: #475569;
            padding: 4px 12px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 10px;
            letter-spacing: 0.5px;
        }
        
        .card-referencias {
            background: white;
            border-radius: 16px;
            padding: 25px 30px;
            margin-top: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            border: 1px solid #edf2f7;
        }
        
        .card-referencias h3 {
            color: var(--cor-primaria);
            font-size: 1.3rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .referencias-texto {
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            color: #334155;
            white-space: pre-line;
            line-height: 1.6;
            font-size: 0.95rem;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #64748b;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .breadcrumb a {
            color: var(--cor-primaria);
            text-decoration: none;
            font-weight: 500;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .grid-caracteristicas {
                grid-template-columns: 1fr;
            }
            
            .nome-cientifico {
                font-size: 1.6rem;
            }
            
            .header-bar {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .tabela-caracteristicas td {
                display: block;
                width: 100%;
            }
            
            .tabela-caracteristicas td:first-child {
                width: 100%;
                border-radius: 8px 8px 0 0;
            }
            
            .tabela-caracteristicas td:last-child {
                width: 100%;
                padding-left: 16px;
                padding-bottom: 16px;
            }
        }
        
        .icone {
            font-size: 1.2em;
        }
        
        .divisor {
            height: 2px;
            background: linear-gradient(90deg, var(--cor-primaria) 0%, rgba(11,94,66,0.1) 100%);
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- BREADCRUMB CORRIGIDO -->
        <div class="breadcrumb">
            <a href="../Controllers/busca_caracteristicas.php">Busca de Espécies</a>
            <span>›</span>
            <span style="color: var(--cor-primaria); font-weight: 600;">
                <?php echo $especie ? htmlspecialchars($especie['nome_cientifico']) : 'Detalhes'; ?>
            </span>
        </div>
        
        <?php if ($erro): ?>
            <div class="card-erro">
                <div style="font-size: 4rem; margin-bottom: 20px;">🌿</div>
                <h2><?php echo htmlspecialchars($erro); ?></h2>
                <p>Não foi possível carregar as informações desta espécie.</p>
                <a href="../Controllers/busca_caracteristicas.php" class="btn-voltar" style="display: inline-block;">
                    ← Voltar para a busca
                </a>
            </div>
            
        <?php elseif ($especie): ?>
            
            <div class="header-bar">
                <div class="titulo-principal">
                    <span>📋</span> Ficha Técnica da Espécie
                </div>
                <a href="../Controllers/busca_caracteristicas.php" class="btn-voltar">
                    ← Voltar para a busca
                </a>
            </div>
            
            <div class="ficha-especie">
                
                <div class="card-identificacao">
                    <div class="nome-cientifico">
                        <?php echo htmlspecialchars($especie['nome_cientifico'] ?? 'Não informado'); ?>
                    </div>
                    
                    <?php if (!empty($especie['nome_cientifico_completo'])): ?>
                    <div style="color: #4b5563; margin-bottom: 15px; font-size: 1.1rem;">
                        <strong>Completo:</strong> <?php echo htmlspecialchars($especie['nome_cientifico_completo']); ?>
                        <?php if (!empty($especie['nome_cientifico_completo_ref'])): ?>
                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['nome_cientifico_completo_ref']); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($especie['nome_popular'])): ?>
                    <div class="nome-popular">
                        🌳 <?php echo htmlspecialchars($especie['nome_popular']); ?>
                        <?php if (!empty($especie['nome_popular_ref'])): ?>
                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['nome_popular_ref']); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($especie['familia'])): ?>
                    <div class="familia">
                        <strong>Família:</strong> <?php echo htmlspecialchars($especie['familia']); ?>
                        <?php if (!empty($especie['familia_ref'])): ?>
                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['familia_ref']); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="grid-caracteristicas">
                    
                    <!-- FOLHA -->
                    <div class="card-categoria">
                        <div class="categoria-titulo">
                            <span style="font-size: 1.6rem;">🍃</span> Folha
                        </div>
                        <div class="categoria-conteudo">
                            <table class="tabela-caracteristicas">
                                <?php if (!empty($especie['forma_folha'])): ?>
                                <tr>
                                    <td>Forma</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['forma_folha']); ?>
                                        <?php if (!empty($especie['forma_folha_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['forma_folha_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['filotaxia_folha'])): ?>
                                <tr>
                                    <td>Filotaxia</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['filotaxia_folha']); ?>
                                        <?php if (!empty($especie['filotaxia_folha_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['filotaxia_folha_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['tipo_folha'])): ?>
                                <tr>
                                    <td>Tipo</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['tipo_folha']); ?>
                                        <?php if (!empty($especie['tipo_folha_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['tipo_folha_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['tamanho_folha'])): ?>
                                <tr>
                                    <td>Tamanho</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['tamanho_folha']); ?>
                                        <?php if (!empty($especie['tamanho_folha_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['tamanho_folha_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['textura_folha'])): ?>
                                <tr>
                                    <td>Textura</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['textura_folha']); ?>
                                        <?php if (!empty($especie['textura_folha_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['textura_folha_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['margem_folha'])): ?>
                                <tr>
                                    <td>Margem</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['margem_folha']); ?>
                                        <?php if (!empty($especie['margem_folha_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['margem_folha_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['venacao_folha'])): ?>
                                <tr>
                                    <td>Venação</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['venacao_folha']); ?>
                                        <?php if (!empty($especie['venacao_folha_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['venacao_folha_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php
                                $tem_folha = !empty($especie['forma_folha']) || !empty($especie['filotaxia_folha']) || 
                                            !empty($especie['tipo_folha']) || !empty($especie['tamanho_folha']) ||
                                            !empty($especie['textura_folha']) || !empty($especie['margem_folha']) ||
                                            !empty($especie['venacao_folha']);
                                
                                if (!$tem_folha):
                                ?>
                                <tr>
                                    <td colspan="2" style="text-align: center; color: #94a3b8; padding: 20px;">
                                        ⚠️ Nenhuma característica de folha cadastrada
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    
                    <!-- FLORES -->
                    <div class="card-categoria">
                        <div class="categoria-titulo">
                            <span style="font-size: 1.6rem;">🌸</span> Flores
                        </div>
                        <div class="categoria-conteudo">
                            <table class="tabela-caracteristicas">
                                <?php if (!empty($especie['cor_flores'])): ?>
                                <tr>
                                    <td>Cor</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['cor_flores']); ?>
                                        <?php if (!empty($especie['cor_flores_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['cor_flores_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['simetria_floral'])): ?>
                                <tr>
                                    <td>Simetria</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['simetria_floral']); ?>
                                        <?php if (!empty($especie['simetria_floral_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['simetria_floral_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['numero_petalas'])): ?>
                                <tr>
                                    <td>Nº Pétalas</td>
                                    <td>
                                        <?php 
                                        $petalas = str_replace('_', ' ', $especie['numero_petalas']);
                                        echo htmlspecialchars($petalas); 
                                        ?>
                                        <?php if (!empty($especie['numero_petalas_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['numero_petalas_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['tamanho_flores'])): ?>
                                <tr>
                                    <td>Tamanho</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['tamanho_flores']); ?>
                                        <?php if (!empty($especie['tamanho_flores_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['tamanho_flores_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['disposicao_flores'])): ?>
                                <tr>
                                    <td>Disposição</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['disposicao_flores']); ?>
                                        <?php if (!empty($especie['disposicao_flores_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['disposicao_flores_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['aroma'])): ?>
                                <tr>
                                    <td>Aroma</td>
                                    <td>
                                        <?php 
                                        $aroma = str_replace('_', ' ', $especie['aroma']);
                                        echo htmlspecialchars($aroma); 
                                        ?>
                                        <?php if (!empty($especie['aroma_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['aroma_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php
                                $tem_flor = !empty($especie['cor_flores']) || !empty($especie['simetria_floral']) || 
                                           !empty($especie['numero_petalas']) || !empty($especie['tamanho_flores']) ||
                                           !empty($especie['disposicao_flores']) || !empty($especie['aroma']);
                                
                                if (!$tem_flor):
                                ?>
                                <tr>
                                    <td colspan="2" style="text-align: center; color: #94a3b8; padding: 20px;">
                                        ⚠️ Nenhuma característica de flor cadastrada
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    
                    <!-- FRUTOS -->
                    <div class="card-categoria">
                        <div class="categoria-titulo">
                            <span style="font-size: 1.6rem;">🍎</span> Frutos
                        </div>
                        <div class="categoria-conteudo">
                            <table class="tabela-caracteristicas">
                                <?php if (!empty($especie['tipo_fruto'])): ?>
                                <tr>
                                    <td>Tipo</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['tipo_fruto']); ?>
                                        <?php if (!empty($especie['tipo_fruto_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['tipo_fruto_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['tamanho_fruto'])): ?>
                                <tr>
                                    <td>Tamanho</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['tamanho_fruto']); ?>
                                        <?php if (!empty($especie['tamanho_fruto_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['tamanho_fruto_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['cor_fruto'])): ?>
                                <tr>
                                    <td>Cor</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['cor_fruto']); ?>
                                        <?php if (!empty($especie['cor_fruto_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['cor_fruto_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['textura_fruto'])): ?>
                                <tr>
                                    <td>Textura</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['textura_fruto']); ?>
                                        <?php if (!empty($especie['textura_fruto_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['textura_fruto_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['dispersao_fruto'])): ?>
                                <tr>
                                    <td>Dispersão</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['dispersao_fruto']); ?>
                                        <?php if (!empty($especie['dispersao_fruto_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['dispersao_fruto_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['aroma_fruto'])): ?>
                                <tr>
                                    <td>Aroma</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['aroma_fruto']); ?>
                                        <?php if (!empty($especie['aroma_fruto_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['aroma_fruto_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php
                                $tem_fruto = !empty($especie['tipo_fruto']) || !empty($especie['tamanho_fruto']) || 
                                            !empty($especie['cor_fruto']) || !empty($especie['textura_fruto']) ||
                                            !empty($especie['dispersao_fruto']) || !empty($especie['aroma_fruto']);
                                
                                if (!$tem_fruto):
                                ?>
                                <tr>
                                    <td colspan="2" style="text-align: center; color: #94a3b8; padding: 20px;">
                                        ⚠️ Nenhuma característica de fruto cadastrada
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    
                    <!-- SEMENTES -->
                    <div class="card-categoria">
                        <div class="categoria-titulo">
                            <span style="font-size: 1.6rem;">🌱</span> Sementes
                        </div>
                        <div class="categoria-conteudo">
                            <table class="tabela-caracteristicas">
                                <?php if (!empty($especie['tipo_semente'])): ?>
                                <tr>
                                    <td>Tipo</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['tipo_semente']); ?>
                                        <?php if (!empty($especie['tipo_semente_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['tipo_semente_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['tamanho_semente'])): ?>
                                <tr>
                                    <td>Tamanho</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['tamanho_semente']); ?>
                                        <?php if (!empty($especie['tamanho_semente_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['tamanho_semente_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['cor_semente'])): ?>
                                <tr>
                                    <td>Cor</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['cor_semente']); ?>
                                        <?php if (!empty($especie['cor_semente_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['cor_semente_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['textura_semente'])): ?>
                                <tr>
                                    <td>Textura</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['textura_semente']); ?>
                                        <?php if (!empty($especie['textura_semente_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['textura_semente_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['quantidade_sementes'])): ?>
                                <tr>
                                    <td>Quantidade/Fruto</td>
                                    <td>
                                        <?php 
                                        $qtd = str_replace('_', ' ', $especie['quantidade_sementes']);
                                        echo htmlspecialchars($qtd); 
                                        ?>
                                        <?php if (!empty($especie['quantidade_sementes_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['quantidade_sementes_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php
                                $tem_semente = !empty($especie['tipo_semente']) || !empty($especie['tamanho_semente']) || 
                                              !empty($especie['cor_semente']) || !empty($especie['textura_semente']) ||
                                              !empty($especie['quantidade_sementes']);
                                
                                if (!$tem_semente):
                                ?>
                                <tr>
                                    <td colspan="2" style="text-align: center; color: #94a3b8; padding: 20px;">
                                        ⚠️ Nenhuma característica de semente cadastrada
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    
                    <!-- CAULE -->
                    <div class="card-categoria">
                        <div class="categoria-titulo">
                            <span style="font-size: 1.6rem;">🌿</span> Caule
                        </div>
                        <div class="categoria-conteudo">
                            <table class="tabela-caracteristicas">
                                <?php if (!empty($especie['tipo_caule'])): ?>
                                <tr>
                                    <td>Tipo</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['tipo_caule']); ?>
                                        <?php if (!empty($especie['tipo_caule_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['tipo_caule_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['estrutura_caule'])): ?>
                                <tr>
                                    <td>Estrutura</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['estrutura_caule']); ?>
                                        <?php if (!empty($especie['estrutura_caule_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['estrutura_caule_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['textura_caule'])): ?>
                                <tr>
                                    <td>Textura</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['textura_caule']); ?>
                                        <?php if (!empty($especie['textura_caule_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['textura_caule_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['cor_caule'])): ?>
                                <tr>
                                    <td>Cor</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['cor_caule']); ?>
                                        <?php if (!empty($especie['cor_caule_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['cor_caule_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['forma_caule'])): ?>
                                <tr>
                                    <td>Forma</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['forma_caule']); ?>
                                        <?php if (!empty($especie['forma_caule_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['forma_caule_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['modificacao_caule'])): ?>
                                <tr>
                                    <td>Modificação</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['modificacao_caule']); ?>
                                        <?php if (!empty($especie['modificacao_caule_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['modificacao_caule_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['diametro_caule'])): ?>
                                <tr>
                                    <td>Diâmetro</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['diametro_caule']); ?>
                                        <?php if (!empty($especie['diametro_caule_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['diametro_caule_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['ramificacao_caule'])): ?>
                                <tr>
                                    <td>Ramificação</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['ramificacao_caule']); ?>
                                        <?php if (!empty($especie['ramificacao_caule_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['ramificacao_caule_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php
                                $tem_caule = !empty($especie['tipo_caule']) || !empty($especie['estrutura_caule']) || 
                                            !empty($especie['textura_caule']) || !empty($especie['cor_caule']) ||
                                            !empty($especie['forma_caule']) || !empty($especie['modificacao_caule']) ||
                                            !empty($especie['diametro_caule']) || !empty($especie['ramificacao_caule']);
                                
                                if (!$tem_caule):
                                ?>
                                <tr>
                                    <td colspan="2" style="text-align: center; color: #94a3b8; padding: 20px;">
                                        ⚠️ Nenhuma característica de caule cadastrada
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                    
                    <!-- OUTRAS CARACTERÍSTICAS -->
                    <div class="card-categoria">
                        <div class="categoria-titulo">
                            <span style="font-size: 1.6rem;">⚡</span> Outras
                        </div>
                        <div class="categoria-conteudo">
                            <table class="tabela-caracteristicas">
                                <?php if (!empty($especie['possui_espinhos'])): ?>
                                <tr>
                                    <td>Espinhos</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['possui_espinhos']); ?>
                                        <?php if (!empty($especie['possui_espinhos_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['possui_espinhos_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['possui_latex'])): ?>
                                <tr>
                                    <td>Látex</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['possui_latex']); ?>
                                        <?php if (!empty($especie['possui_latex_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['possui_latex_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['possui_seiva'])): ?>
                                <tr>
                                    <td>Seiva</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['possui_seiva']); ?>
                                        <?php if (!empty($especie['possui_seiva_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['possui_seiva_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php if (!empty($especie['possui_resina'])): ?>
                                <tr>
                                    <td>Resina</td>
                                    <td>
                                        <?php echo htmlspecialchars($especie['possui_resina']); ?>
                                        <?php if (!empty($especie['possui_resina_ref'])): ?>
                                            <span class="referencia-badge">REF: <?php echo htmlspecialchars($especie['possui_resina_ref']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php
                                $tem_outras = !empty($especie['possui_espinhos']) || !empty($especie['possui_latex']) || 
                                             !empty($especie['possui_seiva']) || !empty($especie['possui_resina']);
                                
                                if (!$tem_outras):
                                ?>
                                <tr>
                                    <td colspan="2" style="text-align: center; color: #94a3b8; padding: 20px;">
                                        ⚠️ Nenhuma outra característica cadastrada
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($especie['referencias'])): ?>
                <div class="card-referencias">
                    <h3>
                        <span style="font-size: 1.4rem;">📚</span> 
                        Referências Bibliográficas
                    </h3>
                    <div class="referencias-texto">
                        <?php echo nl2br(htmlspecialchars($especie['referencias'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div style="margin-top: 20px; text-align: right; color: #94a3b8; font-size: 0.85rem;">
                    ID da espécie: <?php echo $especie['id']; ?> • 
                    Última atualização: <?php echo isset($especie['data_atualizacao']) ? date('d/m/Y', strtotime($especie['data_atualizacao'])) : 'Não informada'; ?>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
    
    <script>
        document.title = '<?php echo $especie ? addslashes($especie["nome_cientifico"]) : "Espécie não encontrada"; ?> - Penomato';
    </script>
</body>
</html>