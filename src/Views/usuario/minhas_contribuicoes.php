<?php
/**
 * PÁGINA DE MINHAS CONTRIBUIÇÕES - PENOMATO MVP
 * 
 * Exibe o histórico completo de todas as contribuições do usuário:
 * espécies cadastradas, imagens enviadas, revisões realizadas e validações.
 * 
 * @package Penomato
 * @author Equipe Penomato
 * @version 1.0
 */

// ============================================================
// INICIALIZAÇÃO
// ============================================================

// Incluir verificação de acesso
require_once __DIR__ . '/../../Controllers/auth/verificar_acesso.php';

// Proteger página (só logados)
protegerPagina('Faça login para ver suas contribuições.');

// ============================================================
// CONFIGURAÇÕES DA PÁGINA
// ============================================================

$titulo_pagina = "Minhas Contribuições - Penomato";
$descricao_pagina = "Histórico completo de suas contribuições no Penomato";
$mostrar_breadcrumb = true;
$breadcrumb_itens = [
    ['nome' => 'Perfil', 'url' => '/penomato_mvp/src/Views/usuario/meu_perfil.php'],
    ['nome' => 'Minhas Contribuições']
];

// ============================================================
// PARÂMETROS DE FILTRO E PAGINAÇÃO
// ============================================================

$usuario_id = getIdUsuario();
$tipo_filtro = $_GET['tipo'] ?? 'todas';
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$itens_por_pagina = 15;
$offset = ($pagina_atual - 1) * $itens_por_pagina;

// ============================================================
// BUSCAR ESTATÍSTICAS GERAIS
// ============================================================

$estatisticas = [
    'especies' => buscarUm(
        "SELECT COUNT(*) as total FROM especies_caracteristicas WHERE autor_dados_internet_id = :id",
        [':id' => $usuario_id]
    )['total'] ?? 0,
    
    'imagens' => buscarUm(
        "SELECT COUNT(*) as total FROM imagens_especies WHERE id_usuario_identificador = :id",
        [':id' => $usuario_id]
    )['total'] ?? 0,
    
    'revisoes' => buscarUm(
        "SELECT COUNT(*) as total FROM especies_administrativo WHERE revisor_id = :id AND data_revisada IS NOT NULL",
        [':id' => $usuario_id]
    )['total'] ?? 0,
    
    'validacoes' => buscarUm(
        "SELECT COUNT(*) as total FROM imagens_especies WHERE id_usuario_confirmador = :id",
        [':id' => $usuario_id]
    )['total'] ?? 0
];

// ============================================================
// BUSCAR CONTRIBUIÇÕES POR TIPO
// ============================================================

$contribuicoes = [];
$total_registros = 0;

switch ($tipo_filtro) {
    case 'especies':
        // Buscar espécies cadastradas
        $contribuicoes = buscarTodos(
            "SELECT 
                ec.id,
                ec.especie_id,
                ec.data_criacao,
                ea.nome_cientifico,
                ea.nome_popular,
                ea.status,
                'especie' as tipo,
                NULL as parte,
                NULL as status_validacao,
                NULL as caminho_imagem
             FROM especies_caracteristicas ec
             JOIN especies_administrativo ea ON ec.especie_id = ea.id
             WHERE ec.autor_dados_internet_id = :id
             ORDER BY ec.data_criacao DESC
             LIMIT :limit OFFSET :offset",
            [
                ':id' => $usuario_id,
                ':limit' => $itens_por_pagina,
                ':offset' => $offset
            ]
        );
        
        $total_registros = $estatisticas['especies'];
        break;
        
    case 'imagens':
        // Buscar imagens enviadas
        $contribuicoes = buscarTodos(
            "SELECT 
                ie.id,
                ie.especie_id,
                ie.data_upload as data_criacao,
                ea.nome_cientifico,
                ea.nome_popular,
                ie.parte,
                ie.status_validacao,
                ie.caminho_imagem,
                'imagem' as tipo,
                NULL as status
             FROM imagens_especies ie
             JOIN especies_administrativo ea ON ie.especie_id = ea.id
             WHERE ie.id_usuario_identificador = :id
             ORDER BY ie.data_upload DESC
             LIMIT :limit OFFSET :offset",
            [
                ':id' => $usuario_id,
                ':limit' => $itens_por_pagina,
                ':offset' => $offset
            ]
        );
        
        $total_registros = $estatisticas['imagens'];
        break;
        
    case 'revisoes':
        // Buscar revisões realizadas
        $contribuicoes = buscarTodos(
            "SELECT 
                ea.id as especie_id,
                ea.nome_cientifico,
                ea.nome_popular,
                ea.status,
                ea.data_revisada as data_criacao,
                'revisao' as tipo,
                NULL as parte,
                NULL as status_validacao,
                NULL as caminho_imagem
             FROM especies_administrativo ea
             WHERE ea.revisor_id = :id AND ea.data_revisada IS NOT NULL
             ORDER BY ea.data_revisada DESC
             LIMIT :limit OFFSET :offset",
            [
                ':id' => $usuario_id,
                ':limit' => $itens_por_pagina,
                ':offset' => $offset
            ]
        );
        
        $total_registros = $estatisticas['revisoes'];
        break;
        
    case 'validacoes':
        // Buscar validações de imagens
        $contribuicoes = buscarTodos(
            "SELECT 
                ie.id,
                ie.especie_id,
                ie.data_validacao as data_criacao,
                ea.nome_cientifico,
                ea.nome_popular,
                ie.parte,
                ie.status_validacao,
                ie.caminho_imagem,
                'validacao' as tipo
             FROM imagens_especies ie
             JOIN especies_administrativo ea ON ie.especie_id = ea.id
             WHERE ie.id_usuario_confirmador = :id
             ORDER BY ie.data_validacao DESC
             LIMIT :limit OFFSET :offset",
            [
                ':id' => $usuario_id,
                ':limit' => $itens_por_pagina,
                ':offset' => $offset
            ]
        );
        
        $total_registros = $estatisticas['validacoes'];
        break;
        
    default: // 'todas'
        // Buscar todas as contribuições (UNION das queries)
        $sql = "
            (SELECT 
                ec.id,
                ec.especie_id,
                ec.data_criacao,
                ea.nome_cientifico,
                ea.nome_popular,
                ea.status,
                'especie' as tipo,
                NULL as parte,
                NULL as status_validacao,
                NULL as caminho_imagem
             FROM especies_caracteristicas ec
             JOIN especies_administrativo ea ON ec.especie_id = ea.id
             WHERE ec.autor_dados_internet_id = :id)
             
             UNION ALL
             
            (SELECT 
                ie.id,
                ie.especie_id,
                ie.data_upload as data_criacao,
                ea.nome_cientifico,
                ea.nome_popular,
                NULL as status,
                'imagem' as tipo,
                ie.parte,
                ie.status_validacao,
                ie.caminho_imagem
             FROM imagens_especies ie
             JOIN especies_administrativo ea ON ie.especie_id = ea.id
             WHERE ie.id_usuario_identificador = :id)
             
             UNION ALL
             
            (SELECT 
                ea.id as id,
                ea.id as especie_id,
                ea.data_revisada as data_criacao,
                ea.nome_cientifico,
                ea.nome_popular,
                ea.status,
                'revisao' as tipo,
                NULL as parte,
                NULL as status_validacao,
                NULL as caminho_imagem
             FROM especies_administrativo ea
             WHERE ea.revisor_id = :id AND ea.data_revisada IS NOT NULL)
             
             ORDER BY data_criacao DESC
             LIMIT :limit OFFSET :offset
        ";
        
        $contribuicoes = buscarTodos($sql, [
            ':id' => $usuario_id,
            ':limit' => $itens_por_pagina,
            ':offset' => $offset
        ]);
        
        // Calcular total para 'todas' é mais complexo, vamos somar os individuais
        $total_registros = array_sum($estatisticas);
        break;
}

// ============================================================
// CALCULAR TOTAL DE PÁGINAS
// ============================================================

$total_paginas = ceil($total_registros / $itens_por_pagina);

// ============================================================
// INCLUIR CABEÇALHO
// ============================================================

require_once __DIR__ . '/../includes/cabecalho.php';
?>

<!-- ============================================================ -->
<!-- CONTEÚDO PRINCIPAL -->
<!-- ============================================================ -->

<div class="container-fluid py-4">
    
    <!-- ================================================== -->
    <!-- CABEÇALHO DA PÁGINA -->
    <!-- ================================================== -->
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2">
                <i class="fas fa-history me-2" style="color: var(--cor-primaria);"></i>
                Minhas Contribuições
            </h1>
            <p class="text-muted">
                Acompanhe todo o seu histórico de atividades no Penomato
            </p>
        </div>
        
        <div>
            <a href="/penomato_mvp/src/Views/usuario/meu_perfil.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Voltar ao Perfil
            </a>
        </div>
    </div>
    
    <!-- ================================================== -->
    <!-- CARDS DE ESTATÍSTICAS -->
    <!-- ================================================== -->
    
    <div class="row stats-cards mb-4">
        <div class="col-md-3 mb-3">
            <a href="?tipo=especies" class="text-decoration-none">
                <div class="stat-card <?php echo $tipo_filtro == 'especies' ? 'active' : ''; ?>">
                    <div class="stat-card-icon">
                        <i class="fas fa-tree"></i>
                    </div>
                    <div class="stat-card-content">
                        <span class="stat-card-value"><?php echo $estatisticas['especies']; ?></span>
                        <span class="stat-card-label">Espécies</span>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-3 mb-3">
            <a href="?tipo=imagens" class="text-decoration-none">
                <div class="stat-card <?php echo $tipo_filtro == 'imagens' ? 'active' : ''; ?>">
                    <div class="stat-card-icon">
                        <i class="fas fa-camera"></i>
                    </div>
                    <div class="stat-card-content">
                        <span class="stat-card-value"><?php echo $estatisticas['imagens']; ?></span>
                        <span class="stat-card-label">Imagens</span>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-3 mb-3">
            <a href="?tipo=revisoes" class="text-decoration-none">
                <div class="stat-card <?php echo $tipo_filtro == 'revisoes' ? 'active' : ''; ?>">
                    <div class="stat-card-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-card-content">
                        <span class="stat-card-value"><?php echo $estatisticas['revisoes']; ?></span>
                        <span class="stat-card-label">Revisões</span>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-md-3 mb-3">
            <a href="?tipo=validacoes" class="text-decoration-none">
                <div class="stat-card <?php echo $tipo_filtro == 'validacoes' ? 'active' : ''; ?>">
                    <div class="stat-card-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-card-content">
                        <span class="stat-card-value"><?php echo $estatisticas['validacoes']; ?></span>
                        <span class="stat-card-label">Validações</span>
                    </div>
                </div>
            </a>
        </div>
    </div>
    
    <!-- ================================================== -->
    <!-- FILTROS E BUSCA -->
    <!-- ================================================== -->
    
    <div class="card filters-card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="btn-group" role="group">
                        <a href="?tipo=todas" class="btn <?php echo $tipo_filtro == 'todas' ? 'btn-success' : 'btn-outline-success'; ?>">
                            <i class="fas fa-globe me-2"></i>Todas
                        </a>
                        <a href="?tipo=especies" class="btn <?php echo $tipo_filtro == 'especies' ? 'btn-success' : 'btn-outline-success'; ?>">
                            <i class="fas fa-tree me-2"></i>Espécies
                        </a>
                        <a href="?tipo=imagens" class="btn <?php echo $tipo_filtro == 'imagens' ? 'btn-success' : 'btn-outline-success'; ?>">
                            <i class="fas fa-camera me-2"></i>Imagens
                        </a>
                        <a href="?tipo=revisoes" class="btn <?php echo $tipo_filtro == 'revisoes' ? 'btn-success' : 'btn-outline-success'; ?>">
                            <i class="fas fa-check-circle me-2"></i>Revisões
                        </a>
                        <a href="?tipo=validacoes" class="btn <?php echo $tipo_filtro == 'validacoes' ? 'btn-success' : 'btn-outline-success'; ?>">
                            <i class="fas fa-star me-2"></i>Validações
                        </a>
                    </div>
                </div>
                
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <span class="text-muted">
                        <i class="fas fa-list me-1"></i>
                        Total: <strong><?php echo $total_registros; ?></strong> contribuições
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- ================================================== -->
    <!-- LISTA DE CONTRIBUIÇÕES -->
    <!-- ================================================== -->
    
    <?php if (empty($contribuicoes)): ?>
        
        <!-- Estado vazio -->
        <div class="card empty-state">
            <div class="card-body text-center py-5">
                <div class="empty-icon mb-4">
                    <i class="fas fa-leaf fa-4x text-muted"></i>
                </div>
                <h4>Nenhuma contribuição encontrada</h4>
                <p class="text-muted mb-4">
                    <?php if ($tipo_filtro == 'todas'): ?>
                        Você ainda não fez nenhuma contribuição no Penomato.
                        Comece agora mesmo!
                    <?php elseif ($tipo_filtro == 'especies'): ?>
                        Você ainda não cadastrou nenhuma espécie.
                    <?php elseif ($tipo_filtro == 'imagens'): ?>
                        Você ainda não enviou nenhuma imagem.
                    <?php elseif ($tipo_filtro == 'revisoes'): ?>
                        Você ainda não realizou nenhuma revisão.
                    <?php elseif ($tipo_filtro == 'validacoes'): ?>
                        Você ainda não validou nenhuma imagem.
                    <?php endif; ?>
                </p>
                
                <div class="action-buttons">
                    <a href="/penomato_mvp/src/Views/colaborador/cadastrar_caracteristicas.php" class="btn btn-success me-2">
                        <i class="fas fa-plus-circle me-2"></i>Cadastrar Espécie
                    </a>
                    <a href="/penomato_mvp/src/Views/colaborador/upload_imagem.php" class="btn btn-outline-success">
                        <i class="fas fa-upload me-2"></i>Enviar Imagem
                    </a>
                </div>
            </div>
        </div>
        
    <?php else: ?>
        
        <!-- Tabela de contribuições -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Espécie</th>
                            <th>Detalhes</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contribuicoes as $item): ?>
                        <tr>
                            <!-- Tipo com ícone -->
                            <td>
                                <?php if ($item['tipo'] == 'especie'): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-tree me-1"></i> Espécie
                                    </span>
                                <?php elseif ($item['tipo'] == 'imagem'): ?>
                                    <span class="badge bg-info">
                                        <i class="fas fa-camera me-1"></i> Imagem
                                    </span>
                                <?php elseif ($item['tipo'] == 'revisao'): ?>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-check-circle me-1"></i> Revisão
                                    </span>
                                <?php elseif ($item['tipo'] == 'validacao'): ?>
                                    <span class="badge bg-primary">
                                        <i class="fas fa-star me-1"></i> Validação
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Espécie -->
                            <td>
                                <strong><?php echo htmlspecialchars($item['nome_cientifico']); ?></strong>
                                <?php if (!empty($item['nome_popular'])): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($item['nome_popular']); ?></small>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Detalhes específicos -->
                            <td>
                                <?php if ($item['tipo'] == 'imagem' || $item['tipo'] == 'validacao'): ?>
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-leaf me-1"></i> <?php echo ucfirst($item['parte']); ?>
                                    </span>
                                <?php elseif ($item['tipo'] == 'revisao'): ?>
                                    <span class="text-muted">Revisão realizada</span>
                                <?php else: ?>
                                    <span class="text-muted">Cadastro completo</span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Status -->
                            <td>
                                <?php if ($item['tipo'] == 'especie'): ?>
                                    <?php echo badgeStatus($item['status']); ?>
                                <?php elseif ($item['tipo'] == 'imagem'): ?>
                                    <?php echo badgeValidacao($item['status_validacao']); ?>
                                <?php elseif ($item['tipo'] == 'validacao'): ?>
                                    <span class="badge bg-success">Validado</span>
                                <?php else: ?>
                                    <?php echo badgeStatus($item['status']); ?>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Data -->
                            <td>
                                <?php echo date('d/m/Y H:i', strtotime($item['data_criacao'])); ?>
                                <br>
                                <small class="text-muted"><?php echo timeAgo($item['data_criacao']); ?></small>
                            </td>
                            
                            <!-- Ações -->
                            <td>
                                <?php if ($item['tipo'] == 'especie'): ?>
                                    <a href="/penomato_mvp/src/Views/publico/especie_detalhes.php?id=<?php echo $item['especie_id']; ?>" 
                                       class="btn-icon" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                <?php elseif ($item['tipo'] == 'imagem'): ?>
                                    <a href="#" onclick="verImagem(<?php echo $item['id']; ?>)" 
                                       class="btn-icon" title="Visualizar imagem">
                                        <i class="fas fa-image"></i>
                                    </a>
                                <?php elseif ($item['tipo'] == 'revisao'): ?>
                                    <a href="/penomato_mvp/src/Views/revisor/artigo_revisao.php?id=<?php echo $item['especie_id']; ?>" 
                                       class="btn-icon" title="Ver revisão">
                                        <i class="fas fa-search"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <?php if ($total_paginas > 1): ?>
            <div class="card-footer">
                <nav aria-label="Navegação de páginas">
                    <ul class="pagination justify-content-center mb-0">
                        <!-- Botão anterior -->
                        <li class="page-item <?php echo $pagina_atual <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?tipo=<?php echo $tipo_filtro; ?>&pagina=<?php echo $pagina_atual - 1; ?>" tabindex="-1">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        
                        <!-- Páginas -->
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <?php if ($i == 1 || $i == $total_paginas || abs($i - $pagina_atual) <= 2): ?>
                                <li class="page-item <?php echo $i == $pagina_atual ? 'active' : ''; ?>">
                                    <a class="page-link" href="?tipo=<?php echo $tipo_filtro; ?>&pagina=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php elseif ($i == 2 && $pagina_atual > 4): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php elseif ($i == $total_paginas - 1 && $pagina_atual < $total_paginas - 3): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <!-- Botão próximo -->
                        <li class="page-item <?php echo $pagina_atual >= $total_paginas ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?tipo=<?php echo $tipo_filtro; ?>&pagina=<?php echo $pagina_atual + 1; ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
        
    <?php endif; ?>
    
    <!-- ================================================== -->
    <!-- GRÁFICO DE ATIVIDADES (OPCIONAL) -->
    <!-- ================================================== -->
    
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card activity-summary">
                <div class="card-header">
                    <h5><i class="fas fa-chart-pie me-2"></i>Resumo por Tipo</h5>
                </div>
                <div class="card-body">
                    <canvas id="graficoContribuicoes" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card timeline-summary">
                <div class="card-header">
                    <h5><i class="fas fa-calendar-alt me-2"></i>Atividade por Mês</h5>
                </div>
                <div class="card-body">
                    <canvas id="graficoLinha" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL PARA VISUALIZAR IMAGEM -->
<!-- ============================================================ -->

<div class="modal fade" id="modalImagem" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-image me-2"></i>Visualizar Imagem
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" id="modalImagemConteudo">
                <!-- Conteúdo carregado via AJAX -->
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- ESTILOS CSS ESPECÍFICOS -->
<!-- ============================================================ -->

<style>
    /* Cards de estatísticas */
    .stat-card {
        background: var(--branco);
        border-radius: 15px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        transition: all 0.3s;
        border: 2px solid transparent;
    }
    
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .stat-card.active {
        border-color: var(--cor-primaria);
        background: var(--verde-50);
    }
    
    .stat-card-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        background: rgba(11, 94, 66, 0.1);
        color: var(--cor-primaria);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .stat-card-content {
        flex: 1;
    }
    
    .stat-card-value {
        display: block;
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--cinza-800);
        line-height: 1.2;
    }
    
    .stat-card-label {
        font-size: 0.85rem;
        color: var(--cinza-500);
    }
    
    /* Card de filtros */
    .filters-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    }
    
    .btn-group .btn {
        border-radius: 30px !important;
        margin: 0 2px;
    }
    
    /* Estado vazio */
    .empty-state {
        border: 2px dashed var(--cinza-200);
        border-radius: 20px;
        background: var(--cinza-50);
    }
    
    .empty-icon {
        color: #ccc;
    }
    
    /* Tabela */
    .table th {
        background: var(--cinza-50);
        color: var(--cinza-800);
        font-weight: 600;
        font-size: 0.9rem;
        border-top: none;
    }
    
    .table td {
        vertical-align: middle;
        padding: 15px 10px;
    }
    
    .btn-icon {
        display: inline-block;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: var(--cinza-50);
        color: var(--cinza-500);
        text-align: center;
        line-height: 32px;
        transition: all 0.3s;
        margin: 0 2px;
    }
    
    .btn-icon:hover {
        background: var(--cor-primaria);
        color: var(--branco);
    }
    
    /* Paginação */
    .pagination .page-link {
        color: var(--cor-primaria);
        border: none;
        margin: 0 3px;
        border-radius: 8px !important;
    }
    
    .pagination .page-item.active .page-link {
        background: var(--cor-primaria);
        color: var(--branco);
    }
    
    .pagination .page-item.disabled .page-link {
        color: #ccc;
    }
    
    /* Responsividade */
    @media (max-width: 768px) {
        .btn-group {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        
        .btn-group .btn {
            flex: 1;
            font-size: 0.8rem;
            padding: 8px 5px;
        }
        
        .table {
            font-size: 0.85rem;
        }
    }
</style>

<!-- ============================================================ -->
<!-- SCRIPTS -->
<!-- ============================================================ -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // ============================================================
    // FUNÇÃO: VISUALIZAR IMAGEM
    // ============================================================
    
    function verImagem(imagemId) {
        const modal = new bootstrap.Modal(document.getElementById('modalImagem'));
        const conteudo = document.getElementById('modalImagemConteudo');
        
        // Carregar imagem via AJAX
        fetch(`/penomato_mvp/src/Controllers/imagem/buscar_imagem.php?id=${imagemId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    conteudo.innerHTML = `
                        <img src="${data.caminho}" class="img-fluid" alt="Imagem">
                        <div class="mt-3 text-start">
                            <p><strong>Espécie:</strong> ${data.especie}</p>
                            <p><strong>Parte:</strong> ${data.parte}</p>
                            <p><strong>Descrição:</strong> ${data.descricao || 'Sem descrição'}</p>
                        </div>
                    `;
                } else {
                    conteudo.innerHTML = '<p class="text-danger">Erro ao carregar imagem.</p>';
                }
            })
            .catch(() => {
                conteudo.innerHTML = '<p class="text-danger">Erro ao carregar imagem.</p>';
            });
        
        modal.show();
    }
    
    // ============================================================
    // GRÁFICO DE PIZZA
    // ============================================================
    
    const ctxPizza = document.getElementById('graficoContribuicoes').getContext('2d');
    new Chart(ctxPizza, {
        type: 'pie',
        data: {
            labels: ['Espécies', 'Imagens', 'Revisões', 'Validações'],
            datasets: [{
                data: [
                    <?php echo $estatisticas['especies']; ?>,
                    <?php echo $estatisticas['imagens']; ?>,
                    <?php echo $estatisticas['revisoes']; ?>,
                    <?php echo $estatisticas['validacoes']; ?>
                ],
                backgroundColor: [
                    '#28a745',
                    '#17a2b8',
                    '#ffc107',
                    '#007bff'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // ============================================================
    // GRÁFICO DE LINHA (ATIVIDADE POR MÊS)
    // ============================================================
    
    // Buscar dados de atividade por mês
    fetch(`/penomato_mvp/src/Controllers/usuario/atividade_mensal.php?id=<?php echo $usuario_id; ?>`)
        .then(response => response.json())
        .then(data => {
            const ctxLinha = document.getElementById('graficoLinha').getContext('2d');
            new Chart(ctxLinha, {
                type: 'line',
                data: {
                    labels: data.meses,
                    datasets: [{
                        label: 'Contribuições',
                        data: data.valores,
                        borderColor: 'var(--cor-primaria)',
                        backgroundColor: 'rgba(11, 94, 66, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        });
</script>

<!-- ============================================================ -->
<!-- FUNÇÕES AUXILIARES PHP -->
<!-- ============================================================ -->

<?php
/**
 * Retorna badge HTML para status da espécie
 */
function badgeStatus($status) {
    $classes = [
        'sem_dados' => 'bg-secondary',
        'dados_internet' => 'bg-info',
        'descrita' => 'bg-primary',
        'registrada' => 'bg-warning',
        'em_revisao' => 'bg-info',
        'revisada' => 'bg-success',
        'contestado' => 'bg-danger',
        'publicado' => 'bg-success'
    ];
    
    $classe = $classes[$status] ?? 'bg-secondary';
    $status_texto = ucfirst(str_replace('_', ' ', $status));
    
    return "<span class='badge $classe'>$status_texto</span>";
}

/**
 * Retorna badge HTML para validação de imagem
 */
function badgeValidacao($status) {
    switch ($status) {
        case 'pendente':
            return '<span class="badge bg-warning">Pendente</span>';
        case 'validado':
            return '<span class="badge bg-success">Validado</span>';
        case 'rejeitado':
            return '<span class="badge bg-danger">Rejeitado</span>';
        default:
            return '<span class="badge bg-secondary">Desconhecido</span>';
    }
}

/**
 * Formata tempo relativo (ex: "há 2 dias")
 */
function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return 'agora mesmo';
    if ($diff < 3600) return 'há ' . floor($diff/60) . ' min';
    if ($diff < 86400) return 'há ' . floor($diff/3600) . ' h';
    if ($diff < 2592000) return 'há ' . floor($diff/86400) . ' dias';
    return date('d/m/Y', $time);
}
?>

<!-- ============================================================ -->
<!-- INCLUIR RODAPÉ -->
<!-- ============================================================ -->

<?php require_once __DIR__ . '/../includes/rodape.php'; ?>