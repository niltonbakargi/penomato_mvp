<?php
/**
 * PÁGINA DE PERFIL DO USUÁRIO - PENOMATO MVP
 * 
 * Exibe os dados do usuário logado, suas estatísticas,
 * contribuições recentes e atividades no sistema.
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
protegerPagina('Faça login para acessar seu perfil.');

// ============================================================
// CONFIGURAÇÕES DA PÁGINA
// ============================================================

$titulo_pagina = "Meu Perfil - Penomato";
$descricao_pagina = "Visualize e gerencie seu perfil no Penomato";
$mostrar_breadcrumb = true;
$breadcrumb_itens = [
    ['nome' => 'Perfil', 'url' => ''],
    ['nome' => 'Meu Perfil']
];

// ============================================================
// BUSCAR DADOS DO USUÁRIO
// ============================================================

$usuario_id = getIdUsuario();

// Buscar dados completos do usuário
$usuario = buscarUm(
    "SELECT * FROM usuarios WHERE id = :id",
    [':id' => $usuario_id]
);

// Buscar estatísticas do usuário
$estatisticas = [
    'especies_cadastradas' => buscarUm(
        "SELECT COUNT(*) as total FROM especies_caracteristicas WHERE autor_dados_internet_id = :id",
        [':id' => $usuario_id]
    )['total'] ?? 0,
    
    'imagens_enviadas' => buscarUm(
        "SELECT COUNT(*) as total FROM imagens_especies WHERE id_usuario_identificador = :id",
        [':id' => $usuario_id]
    )['total'] ?? 0,
    
    'revisoes_feitas' => buscarUm(
        "SELECT COUNT(*) as total FROM especies_administrativo WHERE revisor_id = :id AND data_revisada IS NOT NULL",
        [':id' => $usuario_id]
    )['total'] ?? 0,
    
    'imagens_validadas' => buscarUm(
        "SELECT COUNT(*) as total FROM imagens_especies WHERE id_usuario_confirmador = :id",
        [':id' => $usuario_id]
    )['total'] ?? 0,
    
    'dias_como_membro' => $usuario ? floor((time() - strtotime($usuario['data_cadastro'])) / (60 * 60 * 24)) : 0
];

// Buscar últimas contribuições
$ultimas_especies = buscarTodos(
    "SELECT ec.*, ea.nome_cientifico, ea.status 
     FROM especies_caracteristicas ec
     JOIN especies_administrativo ea ON ec.especie_id = ea.id
     WHERE ec.autor_dados_internet_id = :id
     ORDER BY ec.data_criacao DESC
     LIMIT 5",
    [':id' => $usuario_id]
);

$ultimas_imagens = buscarTodos(
    "SELECT ie.*, ea.nome_cientifico 
     FROM imagens_especies ie
     JOIN especies_administrativo ea ON ie.especie_id = ea.id
     WHERE ie.id_usuario_identificador = :id
     ORDER BY ie.data_upload DESC
     LIMIT 5",
    [':id' => $usuario_id]
);

// Buscar notificações não lidas
$notificacoes = buscarTodos(
    "SELECT * FROM notificacoes 
     WHERE usuario_id = :id AND lida = 0 
     ORDER BY data_criacao DESC",
    [':id' => $usuario_id]
);

// ============================================================
// INCLUIR CABEÇALHO
// ============================================================

require_once __DIR__ . '/../includes/cabecalho.php';
?>

<!-- ============================================================ -->
<!-- CONTEÚDO PRINCIPAL -->
<!-- ============================================================ -->

<div class="container-fluid py-4">
    <div class="row">
        <!-- ================================================== -->
        <!-- COLUNA ESQUERDA - PERFIL E MENU -->
        <!-- ================================================== -->
        
        <div class="col-lg-4 col-xl-3 mb-4">
            <!-- Card do Perfil -->
            <div class="card profile-card mb-4">
                <div class="profile-header">
                    <div class="profile-cover"></div>
                    
                    <div class="profile-avatar">
                        <?php if ($usuario && file_exists(__DIR__ . '/../../../uploads/fotos_perfil/' . $usuario_id . '.jpg')): ?>
                            <img src="/penomato_mvp/uploads/fotos_perfil/<?php echo $usuario_id; ?>.jpg" 
                                 alt="<?php echo htmlspecialchars($usuario['nome']); ?>">
                        <?php elseif ($usuario && file_exists(__DIR__ . '/../../../uploads/fotos_perfil/' . $usuario_id . '.png')): ?>
                            <img src="/penomato_mvp/uploads/fotos_perfil/<?php echo $usuario_id; ?>.png" 
                                 alt="<?php echo htmlspecialchars($usuario['nome']); ?>">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <?php echo strtoupper(substr($usuario['nome'] ?? 'U', 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <a href="/penomato_mvp/src/Views/usuario/editar_perfil.php" 
                           class="avatar-edit" 
                           title="Editar foto">
                            <i class="fas fa-camera"></i>
                        </a>
                    </div>
                    
                    <div class="profile-info">
                        <h2 class="profile-name"><?php echo htmlspecialchars($usuario['nome'] ?? 'Usuário'); ?></h2>
                        <p class="profile-email">
                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($usuario['email'] ?? ''); ?>
                        </p>
                        
                        <div class="profile-badges">
                            <span class="badge-type">
                                <i class="fas fa-user-tag"></i> 
                                <?php echo traduzirTipo($usuario['tipo'] ?? ''); ?>
                            </span>
                            
                            <?php if (!empty($usuario['subtipo_colaborador'])): ?>
                                <span class="badge-subtype">
                                    <i class="fas fa-user-cog"></i> 
                                    <?php echo traduzirSubtipo($usuario['subtipo_colaborador']); ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($usuario['instituicao'])): ?>
                                <span class="badge-institution">
                                    <i class="fas fa-university"></i> 
                                    <?php echo htmlspecialchars($usuario['instituicao']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $estatisticas['dias_como_membro']; ?></span>
                        <span class="stat-label">Dias como membro</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $estatisticas['especies_cadastradas']; ?></span>
                        <span class="stat-label">Espécies</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $estatisticas['imagens_enviadas']; ?></span>
                        <span class="stat-label">Imagens</span>
                    </div>
                </div>
                
                <div class="profile-actions">
                    <a href="/penomato_mvp/src/Views/usuario/editar_perfil.php" class="btn-profile">
                        <i class="fas fa-edit"></i> Editar Perfil
                    </a>
                    <a href="/penomato_mvp/src/Views/usuario/alterar_senha.php" class="btn-profile">
                        <i class="fas fa-key"></i> Alterar Senha
                    </a>
                </div>
                
                <?php if (!empty($usuario['lattes']) || !empty($usuario['orcid'])): ?>
                <div class="profile-links">
                    <h5>Links Acadêmicos</h5>
                    <?php if (!empty($usuario['lattes'])): ?>
                        <a href="<?php echo htmlspecialchars($usuario['lattes']); ?>" target="_blank" class="link-item">
                            <i class="fas fa-id-card"></i> Currículo Lattes
                        </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($usuario['orcid'])): ?>
                        <a href="https://orcid.org/<?php echo htmlspecialchars($usuario['orcid']); ?>" target="_blank" class="link-item">
                            <i class="fab fa-orcid"></i> ORCID: <?php echo htmlspecialchars($usuario['orcid']); ?>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="profile-footer">
                    <small>
                        <i class="fas fa-calendar-alt"></i> Membro desde: 
                        <?php echo date('d/m/Y', strtotime($usuario['data_cadastro'])); ?>
                    </small>
                    <br>
                    <small>
                        <i class="fas fa-clock"></i> Último acesso: 
                        <?php echo $usuario['ultimo_acesso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acesso'])) : 'Primeiro acesso'; ?>
                    </small>
                </div>
            </div>
            
            <!-- Card de Notificações -->
            <?php if (!empty($notificacoes)): ?>
            <div class="card notifications-card mb-4">
                <div class="card-header">
                    <h5>
                        <i class="fas fa-bell"></i> Notificações
                        <span class="badge bg-danger ms-2"><?php echo count($notificacoes); ?></span>
                    </h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($notificacoes as $notif): ?>
                    <div class="list-group-item notification-item">
                        <div class="notification-icon">
                            <?php
                            switch ($notif['tipo']) {
                                case 'revisao':
                                    echo '<i class="fas fa-check-circle text-success"></i>';
                                    break;
                                case 'imagem':
                                    echo '<i class="fas fa-camera text-info"></i>';
                                    break;
                                case 'comentario':
                                    echo '<i class="fas fa-comment text-warning"></i>';
                                    break;
                                default:
                                    echo '<i class="fas fa-info-circle text-primary"></i>';
                            }
                            ?>
                        </div>
                        <div class="notification-content">
                            <p><?php echo htmlspecialchars($notif['mensagem']); ?></p>
                            <small><?php echo timeAgo($notif['data_criacao']); ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer text-center">
                    <a href="#" class="text-decoration-none">Ver todas</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- ================================================== -->
        <!-- COLUNA CENTRAL - ESTATÍSTICAS E ATIVIDADES -->
        <!-- ================================================== -->
        
        <div class="col-lg-8 col-xl-6 mb-4">
            <!-- Cards de Estatísticas -->
            <div class="row stats-cards">
                <div class="col-sm-6 col-xl-3 mb-3">
                    <div class="stat-card stat-card-especies">
                        <div class="stat-card-icon">
                            <i class="fas fa-tree"></i>
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-value"><?php echo $estatisticas['especies_cadastradas']; ?></span>
                            <span class="stat-card-label">Espécies</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-sm-6 col-xl-3 mb-3">
                    <div class="stat-card stat-card-imagens">
                        <div class="stat-card-icon">
                            <i class="fas fa-camera"></i>
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-value"><?php echo $estatisticas['imagens_enviadas']; ?></span>
                            <span class="stat-card-label">Imagens</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-sm-6 col-xl-3 mb-3">
                    <div class="stat-card stat-card-revisoes">
                        <div class="stat-card-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-value"><?php echo $estatisticas['revisoes_feitas']; ?></span>
                            <span class="stat-card-label">Revisões</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-sm-6 col-xl-3 mb-3">
                    <div class="stat-card stat-card-validacoes">
                        <div class="stat-card-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-value"><?php echo $estatisticas['imagens_validadas']; ?></span>
                            <span class="stat-card-label">Validações</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gráfico de Atividades (placeholder) -->
            <div class="card activity-chart-card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-chart-line"></i> Atividade Recente</h5>
                </div>
                <div class="card-body text-center py-5">
                    <i class="fas fa-chart-bar fa-4x text-muted mb-3"></i>
                    <p class="text-muted">Gráfico de atividades em desenvolvimento</p>
                    <small>Em breve: visualização das suas contribuições ao longo do tempo</small>
                </div>
            </div>
            
            <!-- Últimas Espécies Cadastradas -->
            <div class="card recent-contributions mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-leaf"></i> Últimas Espécies Cadastradas</h5>
                    <a href="/penomato_mvp/src/Views/usuario/minhas_contribuicoes.php?tipo=especies" class="btn-sm btn-outline">
                        Ver todas
                    </a>
                </div>
                
                <?php if (empty($ultimas_especies)): ?>
                    <div class="card-body text-center py-4">
                        <i class="fas fa-seedling fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Você ainda não cadastrou nenhuma espécie.</p>
                        <a href="/penomato_mvp/src/Views/colaborador/cadastrar_caracteristicas.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Cadastrar Primeira Espécie
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Espécie</th>
                                    <th>Data</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimas_especies as $especie): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($especie['nome_cientifico']); ?></strong>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($especie['data_criacao'])); ?></td>
                                    <td>
                                        <?php echo badgeStatus($especie['status']); ?>
                                    </td>
                                    <td>
                                        <a href="/penomato_mvp/src/Views/publico/especie_detalhes.php?id=<?php echo $especie['especie_id']; ?>" 
                                           class="btn-icon" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($especie['status'] == 'sem_dados' || $especie['status'] == 'dados_internet'): ?>
                                        <a href="/penomato_mvp/src/Views/colaborador/editar_caracteristicas.php?id=<?php echo $especie['id']; ?>" 
                                           class="btn-icon" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Últimas Imagens Enviadas -->
            <div class="card recent-images mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-images"></i> Últimas Imagens Enviadas</h5>
                    <a href="/penomato_mvp/src/Views/usuario/minhas_contribuicoes.php?tipo=imagens" class="btn-sm btn-outline">
                        Ver todas
                    </a>
                </div>
                
                <?php if (empty($ultimas_imagens)): ?>
                    <div class="card-body text-center py-4">
                        <i class="fas fa-image fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Você ainda não enviou nenhuma imagem.</p>
                        <a href="/penomato_mvp/src/Views/colaborador/upload_imagem.php" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Enviar Primeira Imagem
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row g-3 p-3">
                        <?php foreach ($ultimas_imagens as $imagem): ?>
                        <div class="col-md-4 col-6">
                            <div class="image-card">
                                <div class="image-preview">
                                    <?php if (file_exists(__DIR__ . '/../../../uploads/exsicatas/' . $imagem['especie_id'] . '/' . basename($imagem['caminho_imagem']))): ?>
                                        <img src="/penomato_mvp/uploads/exsicatas/<?php echo $imagem['especie_id']; ?>/<?php echo basename($imagem['caminho_imagem']); ?>" 
                                             alt="<?php echo htmlspecialchars($imagem['descricao'] ?? 'Imagem'); ?>"
                                             class="img-fluid">
                                    <?php else: ?>
                                        <div class="image-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="image-overlay">
                                        <span class="badge-part"><?php echo ucfirst($imagem['parte']); ?></span>
                                        <?php echo badgeValidacao($imagem['status_validacao']); ?>
                                    </div>
                                </div>
                                <div class="image-info">
                                    <small class="text-truncate d-block">
                                        <?php echo htmlspecialchars($imagem['nome_cientifico']); ?>
                                    </small>
                                    <small class="text-muted">
                                        <?php echo date('d/m/Y', strtotime($imagem['data_upload'])); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- ================================================== -->
        <!-- COLUNA DIREITA - ATALHOS E INFORMAÇÕES -->
        <!-- ================================================== -->
        
        <div class="col-xl-3 mb-4">
            <!-- Card de Acesso Rápido -->
            <div class="card quick-access-card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-rocket"></i> Acesso Rápido</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="/penomato_mvp/src/Views/colaborador/cadastrar_caracteristicas.php" class="list-group-item">
                        <i class="fas fa-plus-circle text-success"></i>
                        <span>Nova Espécie</span>
                        <i class="fas fa-chevron-right ms-auto"></i>
                    </a>
                    
                    <a href="/penomato_mvp/src/Views/colaborador/upload_imagem.php" class="list-group-item">
                        <i class="fas fa-camera text-info"></i>
                        <span>Upload de Imagens</span>
                        <i class="fas fa-chevron-right ms-auto"></i>
                    </a>
                    
                    <?php if (in_array(getTipoUsuario(), ['revisor', 'gestor'])): ?>
                    <a href="/penomato_mvp/src/Views/revisor/painel_revisor.php" class="list-group-item">
                        <i class="fas fa-check-circle text-warning"></i>
                        <span>Revisões Pendentes</span>
                        <span class="badge bg-danger rounded-pill ms-auto"><?php echo contarPendenciasRevisao(); ?></span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if (in_array(getTipoUsuario(), ['validador', 'gestor'])): ?>
                    <a href="/penomato_mvp/src/Views/validador/painel_validador.php" class="list-group-item">
                        <i class="fas fa-star text-primary"></i>
                        <span>Validações</span>
                        <span class="badge bg-danger rounded-pill ms-auto"><?php echo contarPendenciasValidacao(); ?></span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Card de Progresso -->
            <div class="card progress-card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-tasks"></i> Seu Progresso</h5>
                </div>
                <div class="card-body">
                    <div class="progress-item">
                        <div class="progress-label">
                            <span>Meta de espécies</span>
                            <span><?php echo $estatisticas['especies_cadastradas']; ?>/10</span>
                        </div>
                        <div class="progress">
                            <?php $percentual = min(100, ($estatisticas['especies_cadastradas'] / 10) * 100); ?>
                            <div class="progress-bar bg-success" 
                                 style="width: <?php echo $percentual; ?>%;"
                                 role="progressbar"
                                 aria-valuenow="<?php echo $percentual; ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                    
                    <div class="progress-item mt-3">
                        <div class="progress-label">
                            <span>Taxa de aprovação</span>
                            <span>85%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-info" style="width: 85%;"></div>
                        </div>
                    </div>
                    
                    <div class="progress-item mt-3">
                        <div class="progress-label">
                            <span>Nível de contribuidor</span>
                            <span>Bronze</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" style="width: 30%;"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Card de Conquistas -->
            <div class="card achievements-card">
                <div class="card-header">
                    <h5><i class="fas fa-trophy"></i> Conquistas</h5>
                </div>
                <div class="card-body">
                    <div class="achievement-item unlocked">
                        <i class="fas fa-certificate text-warning"></i>
                        <span>Primeira espécie cadastrada</span>
                    </div>
                    
                    <div class="achievement-item unlocked">
                        <i class="fas fa-certificate text-warning"></i>
                        <span>Primeira imagem enviada</span>
                    </div>
                    
                    <?php if ($estatisticas['especies_cadastradas'] >= 5): ?>
                    <div class="achievement-item unlocked">
                        <i class="fas fa-award text-primary"></i>
                        <span>5 espécies cadastradas</span>
                    </div>
                    <?php else: ?>
                    <div class="achievement-item locked">
                        <i class="fas fa-lock"></i>
                        <span>5 espécies cadastradas</span>
                        <small class="text-muted">(faltam <?php echo 5 - $estatisticas['especies_cadastradas']; ?>)</small>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($estatisticas['imagens_enviadas'] >= 10): ?>
                    <div class="achievement-item unlocked">
                        <i class="fas fa-award text-primary"></i>
                        <span>10 imagens enviadas</span>
                    </div>
                    <?php else: ?>
                    <div class="achievement-item locked">
                        <i class="fas fa-lock"></i>
                        <span>10 imagens enviadas</span>
                        <small class="text-muted">(faltam <?php echo 10 - $estatisticas['imagens_enviadas']; ?>)</small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- ESTILOS CSS ESPECÍFICOS -->
<!-- ============================================================ -->

<style>
    /* Card de Perfil */
    .profile-card {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .profile-header {
        position: relative;
    }
    
    .profile-cover {
        height: 100px;
        background: linear-gradient(135deg, #0b5e42 0%, #1a7a5a 100%);
    }
    
    .profile-avatar {
        position: relative;
        width: 100px;
        height: 100px;
        margin: -50px auto 0;
        z-index: 2;
    }
    
    .profile-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        object-fit: cover;
    }
    
    .avatar-placeholder {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: #0b5e42;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 600;
        border: 4px solid white;
    }
    
    .avatar-edit {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 32px;
        height: 32px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0b5e42;
        text-decoration: none;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        transition: all 0.3s;
    }
    
    .avatar-edit:hover {
        background: #0b5e42;
        color: white;
        transform: scale(1.1);
    }
    
    .profile-info {
        text-align: center;
        padding: 15px 20px;
    }
    
    .profile-name {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0 0 5px;
        color: #333;
    }
    
    .profile-email {
        color: #666;
        margin-bottom: 15px;
        font-size: 0.95rem;
    }
    
    .profile-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
    }
    
    .badge-type, .badge-subtype, .badge-institution {
        padding: 5px 12px;
        border-radius: 30px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .badge-type {
        background: #0b5e42;
        color: white;
    }
    
    .badge-subtype {
        background: #e8f4f8;
        color: #0b5e42;
    }
    
    .badge-institution {
        background: #f0f0f0;
        color: #666;
    }
    
    .profile-stats {
        display: flex;
        border-top: 1px solid #e0e0e0;
        border-bottom: 1px solid #e0e0e0;
        padding: 15px 0;
    }
    
    .stat-item {
        flex: 1;
        text-align: center;
    }
    
    .stat-value {
        display: block;
        font-size: 1.5rem;
        font-weight: 700;
        color: #0b5e42;
        line-height: 1.2;
    }
    
    .stat-label {
        font-size: 0.8rem;
        color: #666;
    }
    
    .profile-actions {
        padding: 20px;
        display: flex;
        gap: 10px;
    }
    
    .btn-profile {
        flex: 1;
        padding: 8px;
        border: 1px solid #0b5e42;
        border-radius: 8px;
        color: #0b5e42;
        text-decoration: none;
        text-align: center;
        font-size: 0.9rem;
        transition: all 0.3s;
    }
    
    .btn-profile:hover {
        background: #0b5e42;
        color: white;
    }
    
    .profile-links {
        padding: 0 20px 20px;
    }
    
    .profile-links h5 {
        font-size: 1rem;
        margin-bottom: 10px;
        color: #333;
    }
    
    .link-item {
        display: block;
        padding: 8px 12px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 5px;
        color: #333;
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.3s;
    }
    
    .link-item:hover {
        background: #e8f4f8;
        color: #0b5e42;
        transform: translateX(3px);
    }
    
    .profile-footer {
        padding: 15px 20px;
        background: #f8f9fa;
        font-size: 0.8rem;
        color: #666;
    }
    
    /* Cards de Estatísticas */
    .stats-cards {
        margin-bottom: 20px;
    }
    
    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        transition: transform 0.3s;
    }
    
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .stat-card-especies .stat-card-icon {
        background: rgba(11, 94, 66, 0.1);
        color: #0b5e42;
    }
    
    .stat-card-imagens .stat-card-icon {
        background: rgba(23, 162, 184, 0.1);
        color: #17a2b8;
    }
    
    .stat-card-revisoes .stat-card-icon {
        background: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }
    
    .stat-card-validacoes .stat-card-icon {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    
    .stat-card-icon {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
    }
    
    .stat-card-content {
        flex: 1;
    }
    
    .stat-card-value {
        display: block;
        font-size: 1.8rem;
        font-weight: 700;
        color: #333;
        line-height: 1.2;
    }
    
    .stat-card-label {
        font-size: 0.9rem;
        color: #666;
    }
    
    /* Notificações */
    .notification-item {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 12px 15px;
    }
    
    .notification-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
    }
    
    .notification-content {
        flex: 1;
    }
    
    .notification-content p {
        margin: 0 0 3px;
        font-size: 0.9rem;
    }
    
    .notification-content small {
        color: #999;
        font-size: 0.75rem;
    }
    
    /* Acesso Rápido */
    .quick-access-card .list-group-item {
        display: flex;
        align-items: center;
        gap: 12px;
        border: none;
        padding: 12px 20px;
        transition: all 0.3s;
    }
    
    .quick-access-card .list-group-item:hover {
        background: #f8f9fa;
        padding-left: 25px;
    }
    
    .quick-access-card .list-group-item i:first-child {
        width: 20px;
        font-size: 1.1rem;
    }
    
    /* Imagens */
    .image-card {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    
    .image-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }
    
    .image-preview {
        position: relative;
        height: 120px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .image-placeholder {
        color: #ccc;
        font-size: 2rem;
    }
    
    .image-overlay {
        position: absolute;
        top: 5px;
        left: 5px;
        right: 5px;
        display: flex;
        justify-content: space-between;
    }
    
    .badge-part {
        background: rgba(0,0,0,0.6);
        color: white;
        padding: 2px 8px;
        border-radius: 15px;
        font-size: 0.7rem;
    }
    
    .image-info {
        padding: 8px;
        background: white;
    }
    
    /* Conquistas */
    .achievement-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .achievement-item:last-child {
        border-bottom: none;
    }
    
    .achievement-item.unlocked i {
        color: #ffc107;
    }
    
    .achievement-item.locked {
        opacity: 0.5;
    }
    
    .achievement-item i {
        width: 25px;
        font-size: 1.2rem;
    }
    
    .achievement-item small {
        margin-left: auto;
    }
    
    /* Botões de ação */
    .btn-icon {
        display: inline-block;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #f8f9fa;
        color: #666;
        text-align: center;
        line-height: 32px;
        transition: all 0.3s;
    }
    
    .btn-icon:hover {
        background: #0b5e42;
        color: white;
    }
    
    .btn-sm-outline {
        padding: 4px 12px;
        border: 1px solid #0b5e42;
        border-radius: 20px;
        color: #0b5e42;
        text-decoration: none;
        font-size: 0.85rem;
        transition: all 0.3s;
    }
    
    .btn-sm-outline:hover {
        background: #0b5e42;
        color: white;
    }
</style>

<!-- ============================================================ -->
<!-- FUNÇÕES AUXILIARES -->
<!-- ============================================================ -->

<?php
/**
 * Traduz o tipo de usuário
 */
function traduzirTipo($tipo) {
    $traducoes = [
        'gestor' => 'Gestor',
        'colaborador' => 'Colaborador',
        'revisor' => 'Revisor',
        'validador' => 'Validador',
        'visitante' => 'Visitante'
    ];
    return $traducoes[$tipo] ?? $tipo;
}

/**
 * Traduz o subtipo do colaborador
 */
function traduzirSubtipo($subtipo) {
    $traducoes = [
        'identificador' => 'Identificador',
        'coletor' => 'Coletor',
        'fotografo' => 'Fotógrafo'
    ];
    return $traducoes[$subtipo] ?? $subtipo;
}

/**
 * Retorna badge HTML para status
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
    return "<span class='badge $classe'>" . ucfirst(str_replace('_', ' ', $status)) . "</span>";
}

/**
 * Retorna badge HTML para validação de imagem
 */
function badgeValidacao($status) {
    $badges = [
        'pendente' => '<span class="badge bg-warning">Pendente</span>',
        'validado' => '<span class="badge bg-success">Validado</span>',
        'rejeitado' => '<span class="badge bg-danger">Rejeitado</span>'
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">Desconhecido</span>';
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

/**
 * Conta pendências de revisão (placeholder)
 */
function contarPendenciasRevisao() {
    return 3; // Exemplo
}

/**
 * Conta pendências de validação (placeholder)
 */
function contarPendenciasValidacao() {
    return 2; // Exemplo
}
?>

<!-- ============================================================ -->
<!-- INCLUIR RODAPÉ -->
<!-- ============================================================ -->

<?php require_once __DIR__ . '/../includes/rodape.php'; ?>