<?php
/**
 * PÁGINA DE EDIÇÃO DE PERFIL - PENOMATO MVP
 * 
 * Permite que o usuário edite seus dados pessoais,
 * troque a foto de perfil e atualize informações acadêmicas.
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
protegerPagina('Faça login para editar seu perfil.');

// ============================================================
// CONFIGURAÇÕES DA PÁGINA
// ============================================================

$titulo_pagina = "Editar Perfil - Penomato";
$descricao_pagina = "Atualize suas informações pessoais e acadêmicas";
$mostrar_breadcrumb = true;
$breadcrumb_itens = [
    ['nome' => 'Perfil', 'url' => '/penomato_mvp/src/Views/usuario/meu_perfil.php'],
    ['nome' => 'Editar Perfil']
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

// Se não encontrar o usuário, redirecionar
if (!$usuario) {
    $_SESSION['mensagem_erro'] = "Usuário não encontrado.";
    header('Location: /penomato_mvp/src/Views/auth/login.php');
    exit;
}

// ============================================================
// PEGAR MENSAGENS DA SESSÃO
// ============================================================

$mensagens = getMensagens();

// Pegar dados da última tentativa (se houver)
$dados_tentativa = $_SESSION['dados_edicao'] ?? [];
unset($_SESSION['dados_edicao']);

// ============================================================
// INCLUIR CABEÇALHO
// ============================================================

require_once __DIR__ . '/../includes/cabecalho.php';
?>

<!-- ============================================================ -->
<!-- CONTEÚDO PRINCIPAL -->
<!-- ============================================================ -->

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            
            <!-- ================================================== -->
            <!-- CARD PRINCIPAL DE EDIÇÃO -->
            <!-- ================================================== -->
            
            <div class="card edit-profile-card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>Editar Perfil
                    </h4>
                    <p class="text-muted mb-0 small">
                        Atualize suas informações pessoais e acadêmicas
                    </p>
                </div>
                
                <div class="card-body">
                    
                    <!-- ============================================== -->
                    <!-- MENSAGENS DE FEEDBACK -->
                    <!-- ============================================== -->
                    
                    <?php if (!empty($mensagens['sucesso'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo $mensagens['sucesso']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($mensagens['erro'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo $mensagens['erro']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- ============================================== -->
                    <!-- FORMULÁRIO DE EDIÇÃO -->
                    <!-- ============================================== -->
                    
                    <form action="/penomato_mvp/src/Controllers/usuario/atualizar_perfil_controlador.php" 
                          method="POST" 
                          id="formEditarPerfil"
                          enctype="multipart/form-data"
                          novalidate>
                        
                        <!-- Token CSRF (segurança) -->
                        <input type="hidden" name="csrf_token" value="<?php echo gerarCsrfToken(); ?>">
                        <input type="hidden" name="usuario_id" value="<?php echo $usuario_id; ?>">
                        
                        <!-- ========================================== -->
                        <!-- SEÇÃO 1: FOTO DE PERFIL -->
                        <!-- ========================================== -->
                        
                        <div class="section-title">
                            <i class="fas fa-camera"></i>
                            <span>Foto de Perfil</span>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-3 text-center">
                                <!-- Preview da foto atual -->
                                <div class="profile-photo-preview">
                                    <?php if (file_exists(__DIR__ . '/../../../uploads/fotos_perfil/' . $usuario_id . '.jpg')): ?>
                                        <img src="/penomato_mvp/uploads/fotos_perfil/<?php echo $usuario_id; ?>.jpg?t=<?php echo time(); ?>" 
                                             alt="Foto de perfil atual"
                                             id="fotoPreview"
                                             class="img-fluid rounded-circle"
                                             style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #0b5e42;">
                                    <?php elseif (file_exists(__DIR__ . '/../../../uploads/fotos_perfil/' . $usuario_id . '.png')): ?>
                                        <img src="/penomato_mvp/uploads/fotos_perfil/<?php echo $usuario_id; ?>.png?t=<?php echo time(); ?>" 
                                             alt="Foto de perfil atual"
                                             id="fotoPreview"
                                             class="img-fluid rounded-circle"
                                             style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #0b5e42;">
                                    <?php else: ?>
                                        <div class="avatar-placeholder" id="fotoPreviewPlaceholder">
                                            <?php echo strtoupper(substr($usuario['nome'] ?? 'U', 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-9">
                                <div class="mb-3">
                                    <label for="foto" class="form-label">
                                        <i class="fas fa-cloud-upload-alt me-2"></i>Alterar foto
                                    </label>
                                    <input type="file" 
                                           class="form-control" 
                                           id="foto" 
                                           name="foto" 
                                           accept="image/jpeg,image/png,image/gif">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle"></i> 
                                        Formatos: JPG, PNG, GIF. Tamanho máximo: 2MB. 
                                        A imagem será redimensionada para 300x300.
                                    </div>
                                </div>
                                
                                <?php if (file_exists(__DIR__ . '/../../../uploads/fotos_perfil/' . $usuario_id . '.jpg') || 
                                          file_exists(__DIR__ . '/../../../uploads/fotos_perfil/' . $usuario_id . '.png')): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remover_foto" id="remover_foto">
                                    <label class="form-check-label text-danger" for="remover_foto">
                                        <i class="fas fa-trash-alt me-1"></i> Remover foto atual
                                    </label>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- ========================================== -->
                        <!-- SEÇÃO 2: INFORMAÇÕES PESSOAIS -->
                        <!-- ========================================== -->
                        
                        <div class="section-title">
                            <i class="fas fa-user"></i>
                            <span>Informações Pessoais</span>
                        </div>
                        
                        <div class="row">
                            <!-- Nome Completo -->
                            <div class="col-md-12 mb-3">
                                <label for="nome" class="form-label">
                                    <i class="fas fa-user me-2"></i>Nome Completo *
                                </label>
                                <input type="text" 
                                       class="form-control <?php echo isset($dados_tentativa['erros']['nome']) ? 'is-invalid' : ''; ?>" 
                                       id="nome" 
                                       name="nome" 
                                       value="<?php echo htmlspecialchars($dados_tentativa['nome'] ?? $usuario['nome']); ?>"
                                       required>
                                <?php if (isset($dados_tentativa['erros']['nome'])): ?>
                                    <div class="invalid-feedback"><?php echo $dados_tentativa['erros']['nome']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Email (apenas leitura) -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>E-mail
                                </label>
                                <input type="email" 
                                       class="form-control bg-light" 
                                       id="email" 
                                       value="<?php echo htmlspecialchars($usuario['email']); ?>" 
                                       readonly>
                                <div class="form-text">
                                    <i class="fas fa-lock"></i> 
                                    O e-mail não pode ser alterado. 
                                    <a href="/penomato_mvp/src/Views/auth/solicitar_alteracao_email.php">Solicitar alteração</a>
                                </div>
                            </div>
                            
                            <!-- Tipo de Usuário (apenas leitura) -->
                            <div class="col-md-6 mb-3">
                                <label for="tipo" class="form-label">
                                    <i class="fas fa-user-tag me-2"></i>Tipo de Perfil
                                </label>
                                <input type="text" 
                                       class="form-control bg-light" 
                                       id="tipo" 
                                       value="<?php echo traduzirTipo($usuario['tipo']); ?><?php echo $usuario['subtipo_colaborador'] ? ' - ' . traduzirSubtipo($usuario['subtipo_colaborador']) : ''; ?>" 
                                       readonly>
                                <div class="form-text">
                                    <i class="fas fa-lock"></i> 
                                    O tipo de perfil é definido no cadastro.
                                </div>
                            </div>
                        </div>
                        
                        <!-- ========================================== -->
                        <!-- SEÇÃO 3: INFORMAÇÕES ACADÊMICAS -->
                        <!-- ========================================== -->
                        
                        <div class="section-title">
                            <i class="fas fa-graduation-cap"></i>
                            <span>Informações Acadêmicas</span>
                        </div>
                        
                        <div class="row">
                            <!-- Instituição -->
                            <div class="col-md-6 mb-3">
                                <label for="instituicao" class="form-label">
                                    <i class="fas fa-university me-2"></i>Instituição
                                </label>
                                <input type="text" 
                                       class="form-control <?php echo isset($dados_tentativa['erros']['instituicao']) ? 'is-invalid' : ''; ?>" 
                                       id="instituicao" 
                                       name="instituicao" 
                                       value="<?php echo htmlspecialchars($dados_tentativa['instituicao'] ?? $usuario['instituicao']); ?>"
                                       placeholder="Ex: UEMS, UFMS, UNESP...">
                                <?php if (isset($dados_tentativa['erros']['instituicao'])): ?>
                                    <div class="invalid-feedback"><?php echo $dados_tentativa['erros']['instituicao']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Lattes -->
                            <div class="col-md-6 mb-3">
                                <label for="lattes" class="form-label">
                                    <i class="fas fa-id-card me-2"></i>Currículo Lattes
                                </label>
                                <input type="url" 
                                       class="form-control <?php echo isset($dados_tentativa['erros']['lattes']) ? 'is-invalid' : ''; ?>" 
                                       id="lattes" 
                                       name="lattes" 
                                       value="<?php echo htmlspecialchars($dados_tentativa['lattes'] ?? $usuario['lattes']); ?>"
                                       placeholder="http://lattes.cnpq.br/...">
                                <?php if (isset($dados_tentativa['erros']['lattes'])): ?>
                                    <div class="invalid-feedback"><?php echo $dados_tentativa['erros']['lattes']; ?></div>
                                <?php endif; ?>
                                <div class="form-text">
                                    Link completo do seu currículo Lattes
                                </div>
                            </div>
                            
                            <!-- ORCID -->
                            <div class="col-md-6 mb-3">
                                <label for="orcid" class="form-label">
                                    <i class="fab fa-orcid me-2"></i>ORCID
                                </label>
                                <input type="text" 
                                       class="form-control <?php echo isset($dados_tentativa['erros']['orcid']) ? 'is-invalid' : ''; ?>" 
                                       id="orcid" 
                                       name="orcid" 
                                       value="<?php echo htmlspecialchars($dados_tentativa['orcid'] ?? $usuario['orcid']); ?>"
                                       placeholder="0000-0002-1825-0097"
                                       maxlength="19">
                                <?php if (isset($dados_tentativa['erros']['orcid'])): ?>
                                    <div class="invalid-feedback"><?php echo $dados_tentativa['erros']['orcid']; ?></div>
                                <?php endif; ?>
                                <div class="form-text">
                                    Formato: 0000-0002-1825-0097
                                </div>
                            </div>
                        </div>
                        
                        <!-- ========================================== -->
                        <!-- SEÇÃO 4: PREFERÊNCIAS -->
                        <!-- ========================================== -->
                        
                        <div class="section-title">
                            <i class="fas fa-sliders-h"></i>
                            <span>Preferências</span>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="receber_notificacoes" id="receber_notificacoes" 
                                           <?php echo ($usuario['receber_notificacoes'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="receber_notificacoes">
                                        Receber notificações por e-mail
                                    </label>
                                </div>
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="perfil_publico" id="perfil_publico"
                                           <?php echo ($usuario['perfil_publico'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="perfil_publico">
                                        Tornar meu perfil público (visível para outros usuários)
                                    </label>
                                </div>
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="receber_newsletter" id="receber_newsletter"
                                           <?php echo ($usuario['receber_newsletter'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="receber_newsletter">
                                        Receber newsletter do Penomato
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- ========================================== -->
                        <!-- BOTÕES DE AÇÃO -->
                        <!-- ========================================== -->
                        
                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="/penomato_mvp/src/Views/usuario/meu_perfil.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Voltar ao Perfil
                            </a>
                            
                            <div>
                                <a href="/penomato_mvp/src/Views/usuario/alterar_senha.php" class="btn btn-outline-warning me-2">
                                    <i class="fas fa-key me-2"></i>Alterar Senha
                                </a>
                                
                                <button type="submit" class="btn btn-success" id="btnSalvar">
                                    <span id="btnText"><i class="fas fa-save me-2"></i>Salvar Alterações</span>
                                    <span id="btnLoading" style="display: none;">
                                        <span class="spinner-border spinner-border-sm" role="status"></span>
                                        Salvando...
                                    </span>
                                </button>
                            </div>
                        </div>
                        
                    </form>
                </div>
            </div>
            
            <!-- ================================================== -->
            <!-- CARD DE ZONA DE PERIGO (EXCLUIR CONTA) -->
            <!-- ================================================== -->
            
            <div class="card danger-zone-card mt-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Zona de Perigo</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="fw-bold">Excluir minha conta</h6>
                            <p class="text-muted small mb-0">
                                Esta ação é irreversível. Todos os seus dados serão removidos permanentemente,
                                incluindo suas contribuições, imagens e informações pessoais.
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalExcluirConta">
                                <i class="fas fa-trash-alt me-2"></i>Excluir Conta
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL DE CONFIRMAÇÃO - EXCLUIR CONTA -->
<!-- ============================================================ -->

<div class="modal fade" id="modalExcluirConta" tabindex="-1" aria-labelledby="modalExcluirContaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalExcluirContaLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Exclusão de Conta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-trash-alt fa-4x text-danger"></i>
                </div>
                
                <p class="fw-bold">ATENÇÃO: Esta ação é IRREVERSÍVEL!</p>
                
                <p>Ao confirmar a exclusão:</p>
                <ul>
                    <li>Sua conta será permanentemente desativada</li>
                    <li>Suas informações pessoais serão removidas</li>
                    <li>Suas contribuições (espécies, imagens) serão anonimizadas</li>
                    <li>Você não poderá recuperar seus dados</li>
                </ul>
                
                <p class="text-muted small">
                    Se você tem certeza que deseja prosseguir, digite 
                    <strong class="text-danger">EXCLUIR</strong> no campo abaixo.
                </p>
                
                <input type="text" class="form-control" id="confirmarExclusao" placeholder="Digite EXCLUIR">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
                <a href="/penomato_mvp/src/Controllers/usuario/excluir_conta_controlador.php" 
                   class="btn btn-danger" 
                   id="btnConfirmarExclusao"
                   onclick="return confirmarExclusao(event)">
                    <i class="fas fa-trash-alt me-2"></i>Sim, excluir permanentemente
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- ESTILOS CSS ESPECÍFICOS -->
<!-- ============================================================ -->

<style>
    /* Card principal de edição */
    .edit-profile-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .edit-profile-card .card-header {
        background: linear-gradient(135deg, #0b5e42 0%, #1a7a5a 100%);
        color: white;
        padding: 25px 30px;
        border: none;
    }
    
    .edit-profile-card .card-header h4 {
        font-weight: 700;
    }
    
    .edit-profile-card .card-header p {
        color: rgba(255,255,255,0.9);
    }
    
    .edit-profile-card .card-body {
        padding: 30px;
    }
    
    /* Títulos das seções */
    .section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 30px 0 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e0e0e0;
    }
    
    .section-title i {
        font-size: 1.5rem;
        color: #0b5e42;
        background: rgba(11, 94, 66, 0.1);
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .section-title span {
        font-size: 1.2rem;
        font-weight: 600;
        color: #333;
    }
    
    /* Preview da foto */
    .profile-photo-preview {
        margin-bottom: 15px;
    }
    
    .avatar-placeholder {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: #0b5e42;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
        font-weight: 600;
        border: 4px solid #0b5e42;
        margin: 0 auto;
    }
    
    /* Formulário */
    .form-control, .form-select {
        border-radius: 10px;
        border: 2px solid #e0e0e0;
        padding: 10px 15px;
        transition: all 0.3s;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #0b5e42;
        box-shadow: 0 0 0 3px rgba(11, 94, 66, 0.1);
    }
    
    .form-control.is-invalid {
        border-color: #dc3545;
        background-image: none;
    }
    
    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }
    
    .form-label i {
        color: #0b5e42;
    }
    
    /* Botões */
    .btn-success {
        background: #0b5e42;
        border: none;
        border-radius: 10px;
        padding: 10px 25px;
        font-weight: 600;
    }
    
    .btn-success:hover {
        background: #0a4e36;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(11, 94, 66, 0.3);
    }
    
    .btn-outline-secondary {
        border-radius: 10px;
        padding: 10px 25px;
        font-weight: 500;
    }
    
    .btn-outline-warning {
        border-radius: 10px;
        padding: 10px 25px;
        font-weight: 500;
    }
    
    /* Zona de perigo */
    .danger-zone-card {
        border: 1px solid #dc3545;
        border-radius: 15px;
        overflow: hidden;
    }
    
    .danger-zone-card .card-header {
        border-bottom: none;
    }
    
    /* Responsividade */
    @media (max-width: 768px) {
        .edit-profile-card .card-body {
            padding: 20px;
        }
        
        .profile-photo-preview {
            margin-bottom: 20px;
        }
        
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 15px;
        }
        
        .d-flex.justify-content-between > div {
            width: 100%;
            display: flex;
            gap: 10px;
        }
        
        .d-flex.justify-content-between .btn {
            flex: 1;
        }
    }
</style>

<!-- ============================================================ -->
<!-- SCRIPTS -->
<!-- ============================================================ -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
    // ============================================================
    // VARIÁVEIS GLOBAIS
    // ============================================================
    
    const form = document.getElementById('formEditarPerfil');
    const btnSalvar = document.getElementById('btnSalvar');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    const fotoInput = document.getElementById('foto');
    const fotoPreview = document.getElementById('fotoPreview');
    const fotoPreviewPlaceholder = document.getElementById('fotoPreviewPlaceholder');
    
    // ============================================================
    // MÁSCARAS
    // ============================================================
    
    $(document).ready(function() {
        // Máscara para ORCID
        $('#orcid').mask('0000-0000-0000-0000', {
            placeholder: '0000-0002-1825-0097'
        });
    });
    
    // ============================================================
    // PREVIEW DA FOTO
    // ============================================================
    
    fotoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        
        if (file) {
            // Validar tamanho (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('A foto deve ter no máximo 2MB.');
                this.value = '';
                return;
            }
            
            // Validar tipo
            if (!['image/jpeg', 'image/png', 'image/gif'].includes(file.type)) {
                alert('Formato não permitido. Use JPG, PNG ou GIF.');
                this.value = '';
                return;
            }
            
            // Preview
            const reader = new FileReader();
            
            reader.onload = function(e) {
                if (fotoPreview) {
                    fotoPreview.src = e.target.result;
                } else if (fotoPreviewPlaceholder) {
                    // Substituir placeholder por imagem
                    const parent = fotoPreviewPlaceholder.parentNode;
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Preview da nova foto';
                    img.className = 'img-fluid rounded-circle';
                    img.style.width = '150px';
                    img.style.height = '150px';
                    img.style.objectFit = 'cover';
                    img.style.border = '4px solid #0b5e42';
                    img.id = 'fotoPreview';
                    parent.replaceChild(img, fotoPreviewPlaceholder);
                }
            };
            
            reader.readAsDataURL(file);
        }
    });
    
    // ============================================================
    // VALIDAÇÃO DO FORMULÁRIO
    // ============================================================
    
    function validarFormulario() {
        let valido = true;
        
        // Validar nome
        const nome = document.getElementById('nome');
        if (!nome.value.trim()) {
            nome.classList.add('is-invalid');
            valido = false;
        } else {
            nome.classList.remove('is-invalid');
        }
        
        // Validar Lattes (se preenchido)
        const lattes = document.getElementById('lattes');
        if (lattes.value && !lattes.value.includes('lattes.cnpq.br')) {
            lattes.classList.add('is-invalid');
            valido = false;
        } else {
            lattes.classList.remove('is-invalid');
        }
        
        return valido;
    }
    
    // ============================================================
    // MOSTRAR LOADING
    // ============================================================
    
    function mostrarLoading() {
        btnSalvar.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-block';
    }
    
    // ============================================================
    // EVENTO DE SUBMIT
    // ============================================================
    
    form.addEventListener('submit', function(e) {
        if (!validarFormulario()) {
            e.preventDefault();
            return false;
        }
        
        mostrarLoading();
        return true;
    });
    
    // ============================================================
    // CONFIRMAÇÃO DE EXCLUSÃO
    // ============================================================
    
    function confirmarExclusao(event) {
        const confirmacao = document.getElementById('confirmarExclusao').value;
        
        if (confirmacao !== 'EXCLUIR') {
            event.preventDefault();
            alert('Digite EXCLUIR no campo de confirmação.');
            return false;
        }
        
        return confirm('Esta ação é irreversível. Tem certeza que deseja excluir sua conta permanentemente?');
    }
    
    // ============================================================
    // REMOVER ERROS AO DIGITAR
    // ============================================================
    
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });
</script>

<!-- ============================================================ -->
<!-- FUNÇÕES AUXILIARES PHP -->
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
 * Gera token CSRF para segurança
 */
function gerarCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
?>

<!-- ============================================================ -->
<!-- INCLUIR RODAPÉ -->
<!-- ============================================================ -->

<?php require_once __DIR__ . '/../includes/rodape.php'; ?>