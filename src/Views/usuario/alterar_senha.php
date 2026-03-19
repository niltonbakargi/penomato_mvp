<?php
/**
 * PÁGINA DE ALTERAÇÃO DE SENHA - PENOMATO MVP
 * 
 * Permite que o usuário altere sua senha, com validações
 * de segurança e confirmação.
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
protegerPagina('Faça login para alterar sua senha.');

// ============================================================
// CONFIGURAÇÕES DA PÁGINA
// ============================================================

$titulo_pagina = "Alterar Senha - Penomato";
$descricao_pagina = "Altere sua senha de acesso ao sistema";
$mostrar_breadcrumb = true;
$breadcrumb_itens = [
    ['nome' => 'Perfil', 'url' => '/penomato_mvp/src/Views/usuario/meu_perfil.php'],
    ['nome' => 'Alterar Senha']
];

// ============================================================
// BUSCAR DADOS DO USUÁRIO
// ============================================================

$usuario_id = getIdUsuario();
$usuario_nome = getNomeUsuario();

// ============================================================
// PEGAR MENSAGENS DA SESSÃO
// ============================================================

$mensagens = getMensagens();

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
        <div class="col-lg-8 col-xl-6">
            
            <!-- ================================================== -->
            <!-- CARD DE ALTERAÇÃO DE SENHA -->
            <!-- ================================================== -->
            
            <div class="card change-password-card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-key me-2"></i>Alterar Senha
                    </h4>
                    <p class="text-muted mb-0 small">
                        Escolha uma senha forte e segura para proteger sua conta
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
                    
                    <?php if (!empty($mensagens['alerta'])): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo $mensagens['alerta']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- ============================================== -->
                    <!-- INFORMAÇÕES DE SEGURANÇA -->
                    <!-- ============================================== -->
                    
                    <div class="security-info mb-4">
                        <div class="security-info-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="security-info-text">
                            <h5>Dicas para uma senha forte:</h5>
                            <ul class="mb-0">
                                <li><i class="fas fa-check-circle text-success me-2"></i>Use pelo menos 8 caracteres</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Inclua letras maiúsculas e minúsculas</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Adicione números e símbolos</li>
                                <li><i class="fas fa-check-circle text-success me-2"></i>Evite palavras comuns ou datas pessoais</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- ============================================== -->
                    <!-- FORMULÁRIO DE ALTERAÇÃO DE SENHA -->
                    <!-- ============================================== -->
                    
                    <form action="/penomato_mvp/src/Controllers/usuario/alterar_senha_controlador.php" 
                          method="POST" 
                          id="formAlterarSenha"
                          novalidate>
                        
                        <!-- Token CSRF (segurança) -->
                        <input type="hidden" name="csrf_token" value="<?php echo gerarCsrfToken(); ?>">
                        
                        <!-- ========================================== -->
                        <!-- SENHA ATUAL -->
                        <!-- ========================================== -->
                        
                        <div class="mb-4">
                            <label for="senha_atual" class="form-label">
                                <i class="fas fa-lock me-2"></i>Senha Atual *
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-key"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="senha_atual" 
                                       name="senha_atual" 
                                       placeholder="Digite sua senha atual"
                                       required
                                       autofocus>
                                <span class="input-group-text senha-toggle" 
                                      onclick="toggleSenha('senha_atual', 'toggleIconAtual')"
                                      title="Mostrar/Esconder senha">
                                    <i class="fas fa-eye" id="toggleIconAtual"></i>
                                </span>
                            </div>
                            <div class="invalid-feedback" id="senhaAtualError"></div>
                        </div>
                        
                        <!-- ========================================== -->
                        <!-- NOVA SENHA -->
                        <!-- ========================================== -->
                        
                        <div class="mb-4">
                            <label for="nova_senha" class="form-label">
                                <i class="fas fa-lock me-2"></i>Nova Senha *
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-key"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="nova_senha" 
                                       name="nova_senha" 
                                       placeholder="Mínimo 8 caracteres"
                                       required>
                                <span class="input-group-text senha-toggle" 
                                      onclick="toggleSenha('nova_senha', 'toggleIconNova')"
                                      title="Mostrar/Esconder senha">
                                    <i class="fas fa-eye" id="toggleIconNova"></i>
                                </span>
                            </div>
                            
                            <!-- Medidor de força da senha -->
                            <div class="password-strength mt-2">
                                <div class="password-strength-bar" id="strengthBar"></div>
                            </div>
                            <div class="password-strength-text" id="strengthText"></div>
                            
                            <!-- Requisitos da senha -->
                            <div class="password-requirements mt-2" id="passwordRequirements">
                                <div class="requirement" id="reqLength">
                                    <i class="fas fa-times-circle text-danger"></i> Pelo menos 8 caracteres
                                </div>
                                <div class="requirement" id="reqLower">
                                    <i class="fas fa-times-circle text-danger"></i> Pelo menos 1 letra minúscula
                                </div>
                                <div class="requirement" id="reqUpper">
                                    <i class="fas fa-times-circle text-danger"></i> Pelo menos 1 letra maiúscula
                                </div>
                                <div class="requirement" id="reqNumber">
                                    <i class="fas fa-times-circle text-danger"></i> Pelo menos 1 número
                                </div>
                                <div class="requirement" id="reqSpecial">
                                    <i class="fas fa-times-circle text-danger"></i> Pelo menos 1 caractere especial (!@#$%^&*)
                                </div>
                            </div>
                            
                            <div class="invalid-feedback" id="novaSenhaError"></div>
                        </div>
                        
                        <!-- ========================================== -->
                        <!-- CONFIRMAR NOVA SENHA -->
                        <!-- ========================================== -->
                        
                        <div class="mb-4">
                            <label for="confirmar_senha" class="form-label">
                                <i class="fas fa-lock me-2"></i>Confirmar Nova Senha *
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-key"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="confirmar_senha" 
                                       name="confirmar_senha" 
                                       placeholder="Digite a nova senha novamente"
                                       required>
                                <span class="input-group-text senha-toggle" 
                                      onclick="toggleSenha('confirmar_senha', 'toggleIconConfirmar')"
                                      title="Mostrar/Esconder senha">
                                    <i class="fas fa-eye" id="toggleIconConfirmar"></i>
                                </span>
                            </div>
                            <div class="invalid-feedback" id="confirmarSenhaError"></div>
                            <small class="text-muted" id="senhaMatch"></small>
                        </div>
                        
                        <!-- ========================================== -->
                        <!-- AVISOS DE SEGURANÇA -->
                        <!-- ========================================== -->
                        
                        <div class="security-warning mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            Após alterar sua senha, você será redirecionado para o login para se autenticar novamente.
                        </div>
                        
                        <!-- ========================================== -->
                        <!-- BOTÕES DE AÇÃO -->
                        <!-- ========================================== -->
                        
                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="/penomato_mvp/src/Views/usuario/meu_perfil.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Voltar ao Perfil
                            </a>
                            
                            <button type="submit" class="btn btn-primary" id="btnAlterar">
                                <span id="btnText"><i class="fas fa-save me-2"></i>Alterar Senha</span>
                                <span id="btnLoading" style="display: none;">
                                    <span class="spinner-border spinner-border-sm" role="status"></span>
                                    Alterando...
                                </span>
                            </button>
                        </div>
                        
                    </form>
                </div>
                
                <!-- Rodapé do card com link para recuperação -->
                <div class="card-footer text-center">
                    <p class="mb-0">
                        <i class="fas fa-question-circle me-1"></i>
                        Esqueceu sua senha? 
                        <a href="/penomato_mvp/src/Views/auth/recuperar_senha.php" class="text-decoration-none">
                            Clique aqui para recuperar
                        </a>
                    </p>
                </div>
            </div>
            
            <!-- ================================================== -->
            <!-- CARD DE ÚLTIMOS ACESSOS (OPCIONAL) -->
            <!-- ================================================== -->
            
            <div class="card last-logins-card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Últimos Acessos
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">
                        <i class="fas fa-shield-alt me-1"></i>
                        Monitore os acessos à sua conta. Caso reconheça algum acesso suspeito, altere sua senha imediatamente.
                    </p>
                    
                    <?php
                    // Buscar últimos acessos do usuário
                    $ultimos_acessos = buscarTodos(
                        "SELECT * FROM logs_acesso 
                         WHERE usuario_id = :id 
                         ORDER BY data_acesso DESC 
                         LIMIT 5",
                        [':id' => $usuario_id]
                    );
                    ?>
                    
                    <?php if (empty($ultimos_acessos)): ?>
                        <p class="text-muted text-center py-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Nenhum registro de acesso encontrado.
                        </p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Data/Hora</th>
                                        <th>IP</th>
                                        <th>Dispositivo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimos_acessos as $acesso): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($acesso['data_acesso'])); ?></td>
                                        <td><?php echo $acesso['ip']; ?></td>
                                        <td>
                                            <?php 
                                            $agente = $acesso['user_agent'] ?? '';
                                            if (strpos($agente, 'Mobile') !== false) {
                                                echo '<i class="fas fa-mobile-alt me-1"></i> Mobile';
                                            } elseif (strpos($agente, 'Windows') !== false) {
                                                echo '<i class="fab fa-windows me-1"></i> Windows';
                                            } elseif (strpos($agente, 'Mac') !== false) {
                                                echo '<i class="fab fa-apple me-1"></i> macOS';
                                            } elseif (strpos($agente, 'Linux') !== false) {
                                                echo '<i class="fab fa-linux me-1"></i> Linux';
                                            } else {
                                                echo '<i class="fas fa-globe me-1"></i> Desconhecido';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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
    /* Card principal */
    .change-password-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .change-password-card .card-header {
        background: linear-gradient(135deg, var(--cor-primaria) 0%, var(--verde-600) 100%);
        color: white;
        padding: 25px 30px;
        border: none;
    }
    
    .change-password-card .card-header h4 {
        font-weight: 700;
    }
    
    .change-password-card .card-header p {
        color: rgba(255,255,255,0.9);
    }
    
    .change-password-card .card-body {
        padding: 30px;
    }
    
    .change-password-card .card-footer {
        background: var(--cinza-50);
        border-top: 1px solid #e0e0e0;
        padding: 15px 30px;
    }
    
    /* Informações de segurança */
    .security-info {
        display: flex;
        gap: 15px;
        background: #e8f4f8;
        border-radius: 15px;
        padding: 20px;
        border-left: 4px solid var(--cor-primaria);
    }
    
    .security-info-icon {
        font-size: 2rem;
        color: var(--cor-primaria);
    }
    
    .security-info-text h5 {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 10px;
        color: var(--cinza-800);
    }
    
    .security-info-text ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .security-info-text li {
        font-size: 0.9rem;
        color: var(--cinza-600);
        margin-bottom: 5px;
    }
    
    /* Aviso de segurança */
    .security-warning {
        background: var(--aviso-fundo);
        border: 1px solid #ffeeba;
        border-radius: 10px;
        padding: 12px 15px;
        color: var(--aviso-texto);
        font-size: 0.9rem;
        display: flex;
        align-items: center;
    }
    
    .security-warning i {
        font-size: 1.2rem;
    }
    
    /* Formulário */
    .form-control {
        border-radius: 10px;
        border: 2px solid var(--cinza-200);
        padding: 10px 15px;
        transition: all 0.3s;
        height: 45px;
    }
    
    .form-control:focus {
        border-color: var(--cor-primaria);
        box-shadow: 0 0 0 3px rgba(11, 94, 66, 0.1);
    }
    
    .form-control.is-invalid {
        border-color: #dc3545;
        background-image: none;
    }
    
    .input-group-text {
        background: var(--cinza-50);
        border: 2px solid var(--cinza-200);
        border-radius: 10px;
        color: var(--cinza-500);
    }
    
    .senha-toggle {
        cursor: pointer;
        transition: color 0.3s;
    }
    
    .senha-toggle:hover {
        color: var(--cor-primaria);
    }
    
    .form-label {
        font-weight: 600;
        color: var(--cinza-800);
        margin-bottom: 5px;
    }
    
    .form-label i {
        color: var(--cor-primaria);
    }
    
    /* Medidor de força da senha */
    .password-strength {
        height: 5px;
        border-radius: 5px;
        background: #e0e0e0;
        overflow: hidden;
    }
    
    .password-strength-bar {
        height: 100%;
        width: 0%;
        transition: all 0.3s;
    }
    
    .password-strength-text {
        font-size: 0.8rem;
        margin-top: 5px;
        text-align: right;
    }
    
    .strength-weak {
        background: var(--perigo-cor);
        width: 33.33%;
    }
    
    .strength-medium {
        background: var(--aviso-cor);
        width: 66.66%;
    }
    
    .strength-strong {
        background: var(--sucesso-cor);
        width: 100%;
    }
    
    /* Requisitos da senha */
    .password-requirements {
        background: var(--cinza-50);
        border-radius: 10px;
        padding: 12px 15px;
        font-size: 0.85rem;
    }
    
    .requirement {
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .requirement i {
        width: 18px;
    }
    
    .requirement.met {
        color: var(--sucesso-cor);
    }
    
    .requirement.met i {
        color: var(--sucesso-cor);
    }
    
    .requirement:last-child {
        margin-bottom: 0;
    }
    
    /* Botões */
    .btn-primary {
        background: var(--cor-primaria);
        border: none;
        border-radius: 10px;
        padding: 10px 25px;
        font-weight: 600;
    }
    
    .btn-primary:hover {
        background: var(--cor-primaria-hover);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(11, 94, 66, 0.3);
    }
    
    .btn-outline-secondary {
        border-radius: 10px;
        padding: 10px 25px;
        font-weight: 500;
    }
    
    /* Card de últimos acessos */
    .last-logins-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    }
    
    .last-logins-card .card-header {
        background: white;
        border-bottom: 1px solid #e0e0e0;
        padding: 15px 20px;
    }
    
    .last-logins-card .card-header h5 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--cinza-800);
    }
    
    .last-logins-card .table {
        font-size: 0.85rem;
    }
    
    /* Responsividade */
    @media (max-width: 768px) {
        .change-password-card .card-body {
            padding: 20px;
        }
        
        .security-info {
            flex-direction: column;
            text-align: center;
        }
        
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 15px;
        }
        
        .d-flex.justify-content-between .btn {
            width: 100%;
        }
    }
</style>

<!-- ============================================================ -->
<!-- SCRIPTS -->
<!-- ============================================================ -->

<script>
    // ============================================================
    // VARIÁVEIS GLOBAIS
    // ============================================================
    
    const form = document.getElementById('formAlterarSenha');
    const senhaAtual = document.getElementById('senha_atual');
    const novaSenha = document.getElementById('nova_senha');
    const confirmarSenha = document.getElementById('confirmar_senha');
    const btnAlterar = document.getElementById('btnAlterar');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    
    // ============================================================
    // FUNÇÃO: MOSTRAR/ESCONDER SENHA
    // ============================================================
    
    function toggleSenha(campoId, iconeId) {
        const campo = document.getElementById(campoId);
        const icone = document.getElementById(iconeId);
        
        if (campo.type === 'password') {
            campo.type = 'text';
            icone.classList.remove('fa-eye');
            icone.classList.add('fa-eye-slash');
        } else {
            campo.type = 'password';
            icone.classList.remove('fa-eye-slash');
            icone.classList.add('fa-eye');
        }
    }
    
    // ============================================================
    // FUNÇÃO: VERIFICAR FORÇA DA SENHA
    // ============================================================
    
    function verificarForcaSenha(senha) {
        let forca = 0;
        
        if (senha.length >= 8) forca += 1;
        if (senha.match(/[a-z]+/)) forca += 1;
        if (senha.match(/[A-Z]+/)) forca += 1;
        if (senha.match(/[0-9]+/)) forca += 1;
        if (senha.match(/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/)) forca += 1;
        
        return forca;
    }
    
    // ============================================================
    // FUNÇÃO: ATUALIZAR REQUISITOS DA SENHA
    // ============================================================
    
    function atualizarRequisitos(senha) {
        // Comprimento
        const reqLength = document.getElementById('reqLength');
        if (senha.length >= 8) {
            reqLength.classList.add('met');
            reqLength.innerHTML = '<i class="fas fa-check-circle text-success"></i> Pelo menos 8 caracteres';
        } else {
            reqLength.classList.remove('met');
            reqLength.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Pelo menos 8 caracteres';
        }
        
        // Letra minúscula
        const reqLower = document.getElementById('reqLower');
        if (senha.match(/[a-z]+/)) {
            reqLower.classList.add('met');
            reqLower.innerHTML = '<i class="fas fa-check-circle text-success"></i> Pelo menos 1 letra minúscula';
        } else {
            reqLower.classList.remove('met');
            reqLower.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Pelo menos 1 letra minúscula';
        }
        
        // Letra maiúscula
        const reqUpper = document.getElementById('reqUpper');
        if (senha.match(/[A-Z]+/)) {
            reqUpper.classList.add('met');
            reqUpper.innerHTML = '<i class="fas fa-check-circle text-success"></i> Pelo menos 1 letra maiúscula';
        } else {
            reqUpper.classList.remove('met');
            reqUpper.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Pelo menos 1 letra maiúscula';
        }
        
        // Número
        const reqNumber = document.getElementById('reqNumber');
        if (senha.match(/[0-9]+/)) {
            reqNumber.classList.add('met');
            reqNumber.innerHTML = '<i class="fas fa-check-circle text-success"></i> Pelo menos 1 número';
        } else {
            reqNumber.classList.remove('met');
            reqNumber.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Pelo menos 1 número';
        }
        
        // Caractere especial
        const reqSpecial = document.getElementById('reqSpecial');
        if (senha.match(/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+/)) {
            reqSpecial.classList.add('met');
            reqSpecial.innerHTML = '<i class="fas fa-check-circle text-success"></i> Pelo menos 1 caractere especial';
        } else {
            reqSpecial.classList.remove('met');
            reqSpecial.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Pelo menos 1 caractere especial';
        }
    }
    
    // ============================================================
    // FUNÇÃO: ATUALIZAR BARRA DE FORÇA DA SENHA
    // ============================================================
    
    function atualizarForcaSenha() {
        const senha = novaSenha.value;
        const forca = verificarForcaSenha(senha);
        const barra = document.getElementById('strengthBar');
        const texto = document.getElementById('strengthText');
        
        barra.className = 'password-strength-bar';
        
        if (senha.length === 0) {
            barra.style.width = '0%';
            texto.textContent = '';
        } else if (forca <= 2) {
            barra.classList.add('strength-weak');
            texto.textContent = 'Força: Fraca';
            texto.style.color = 'var(--perigo-cor)';
        } else if (forca <= 4) {
            barra.classList.add('strength-medium');
            texto.textContent = 'Força: Média';
            texto.style.color = 'var(--aviso-cor)';
        } else {
            barra.classList.add('strength-strong');
            texto.textContent = 'Força: Forte';
            texto.style.color = 'var(--sucesso-cor)';
        }
        
        atualizarRequisitos(senha);
    }
    
    novaSenha.addEventListener('input', atualizarForcaSenha);
    
    // ============================================================
    // FUNÇÃO: VERIFICAR SE SENHAS CONFEREM
    // ============================================================
    
    function verificarSenhas() {
        const senha = novaSenha.value;
        const confirmar = confirmarSenha.value;
        const feedback = document.getElementById('senhaMatch');
        
        if (confirmar.length > 0) {
            if (senha === confirmar) {
                feedback.innerHTML = '<i class="fas fa-check-circle text-success"></i> Senhas conferem';
                feedback.style.color = 'var(--sucesso-cor)';
            } else {
                feedback.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Senhas não conferem';
                feedback.style.color = 'var(--perigo-cor)';
            }
        } else {
            feedback.innerHTML = '';
        }
    }
    
    novaSenha.addEventListener('input', verificarSenhas);
    confirmarSenha.addEventListener('input', verificarSenhas);
    
    // ============================================================
    // FUNÇÃO: VALIDAR FORMULÁRIO
    // ============================================================
    
    function validarFormulario() {
        let valido = true;
        
        // Validar senha atual
        if (!senhaAtual.value) {
            senhaAtual.classList.add('is-invalid');
            document.getElementById('senhaAtualError').textContent = 'Senha atual é obrigatória.';
            valido = false;
        } else {
            senhaAtual.classList.remove('is-invalid');
        }
        
        // Validar nova senha
        if (!novaSenha.value) {
            novaSenha.classList.add('is-invalid');
            document.getElementById('novaSenhaError').textContent = 'Nova senha é obrigatória.';
            valido = false;
        } else if (novaSenha.value.length < 8) {
            novaSenha.classList.add('is-invalid');
            document.getElementById('novaSenhaError').textContent = 'A senha deve ter pelo menos 8 caracteres.';
            valido = false;
        } else {
            novaSenha.classList.remove('is-invalid');
        }
        
        // Validar confirmação
        if (!confirmarSenha.value) {
            confirmarSenha.classList.add('is-invalid');
            document.getElementById('confirmarSenhaError').textContent = 'Confirmação de senha é obrigatória.';
            valido = false;
        } else if (novaSenha.value !== confirmarSenha.value) {
            confirmarSenha.classList.add('is-invalid');
            document.getElementById('confirmarSenhaError').textContent = 'As senhas não conferem.';
            valido = false;
        } else {
            confirmarSenha.classList.remove('is-invalid');
        }
        
        return valido;
    }
    
    // ============================================================
    // FUNÇÃO: MOSTRAR LOADING
    // ============================================================
    
    function mostrarLoading() {
        btnAlterar.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-block';
    }
    
    // ============================================================
    // EVENTO: SUBMIT DO FORMULÁRIO
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
    // REMOVER ERROS AO DIGITAR
    // ============================================================
    
    senhaAtual.addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
    
    novaSenha.addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
    
    confirmarSenha.addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
</script>

<!-- ============================================================ -->
<!-- FUNÇÕES AUXILIARES PHP -->
<!-- ============================================================ -->

<?php
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