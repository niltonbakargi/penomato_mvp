<?php
/**
 * VERIFICAÇÃO DE ACESSO - PENOMATO MVP
 * 
 * Funções para proteger páginas, verificar permissões,
 * gerenciar sessão e controlar acesso baseado em tipos de usuário.
 * 
 * @package Penomato
 * @author Equipe Penomato
 * @version 1.0
 */

// ============================================================
// INICIALIZAÇÃO DA SESSÃO
// ============================================================

// Iniciar sessão com configuração segura de cookie
if (session_status() === PHP_SESSION_NONE) {
    // Cookie seguro só em HTTPS (produção); em dev (HTTP/XAMPP) usa false
    $cookie_seguro = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

    ini_set('session.use_strict_mode', '1'); // Rejeita IDs de sessão não gerados pelo servidor

    session_set_cookie_params([
        'lifetime' => 0,              // Expira ao fechar o navegador
        'path'     => '/',
        'domain'   => '',
        'secure'   => $cookie_seguro, // HTTPS-only em produção
        'httponly' => true,           // Inacessível via JavaScript (bloqueia roubo via XSS)
        'samesite' => 'Lax',         // Proteção CSRF básica sem quebrar links externos
    ]);

    session_start();
}

// ============================================================
// CONSTANTES DE TEMPO
// ============================================================

define('TEMPO_EXPIRACAO', 1800); // 30 minutos em segundos
define('TEMPO_ALERTA', 300);      // 5 minutos para alerta de expiração

// ============================================================
// FUNÇÕES DE VERIFICAÇÃO DE LOGIN
// ============================================================

/**
 * Verifica se o usuário está logado
 * 
 * @return bool True se estiver logado, false caso contrário
 */
function estaLogado() {
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Verifica se o usuário está logado e com sessão válida (não expirada)
 * 
 * @return bool True se a sessão for válida
 */
function sessaoValida() {
    if (!estaLogado()) {
        return false;
    }
    
    // Verificar se a sessão expirou por inatividade
    if (isset($_SESSION['login_time'])) {
        $tempo_inativo = time() - $_SESSION['login_time'];
        
        if ($tempo_inativo > TEMPO_EXPIRACAO) {
            // Sessão expirada - fazer logout automático
            require_once __DIR__ . '/logout_controlador.php';
            fazerLogout(false); // false = não redirecionar ainda
            return false;
        }
        
        // Atualizar tempo de atividade (rolagem da sessão)
        $_SESSION['login_time'] = time();
    }
    
    return true;
}

/**
 * Retorna o ID do usuário logado
 * 
 * @return int|null ID do usuário ou null se não logado
 */
function getIdUsuario() {
    return $_SESSION['usuario_id'] ?? null;
}

/**
 * Retorna o nome do usuário logado
 * 
 * @return string Nome do usuário ou 'Visitante'
 */
function getNomeUsuario() {
    return $_SESSION['usuario_nome'] ?? 'Visitante';
}

/**
 * Retorna o email do usuário logado
 * 
 * @return string|null Email ou null
 */
function getEmailUsuario() {
    return $_SESSION['usuario_email'] ?? null;
}

/**
 * Retorna o tipo do usuário logado
 * 
 * @return string Tipo ou 'visitante'
 */
function getTipoUsuario() {
    return $_SESSION['usuario_tipo'] ?? 'visitante';
}

/**
 * Retorna o subtipo do usuário (se for colaborador)
 * 
 * @return string Subtipo ou vazio
 */
function getSubtipoUsuario() {
    return $_SESSION['usuario_subtipo'] ?? '';
}

/**
 * Retorna a instituição do usuário
 * 
 * @return string Instituição ou 'Não informada'
 */
function getInstituicaoUsuario() {
    return $_SESSION['usuario_instituicao'] ?? 'Não informada';
}

// ============================================================
// FUNÇÕES DE PROTEÇÃO DE PÁGINAS
// ============================================================

/**
 * Protege uma página - redireciona para login se não estiver logado
 * 
 * @param string $mensagem Mensagem opcional para exibir
 * @return void
 */
function protegerPagina($mensagem = 'Faça login para acessar esta página.') {
    if (!sessaoValida()) {
        // Salvar a URL que o usuário tentou acessar
        $_SESSION['url_destino'] = $_SERVER['REQUEST_URI'];
        
        // Salvar mensagem
        $_SESSION['mensagem_erro'] = $mensagem;
        
        // Redirecionar para login
        header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
        exit;
    }
}

/**
 * Protege uma página para visitantes - se estiver logado, redireciona
 * Útil para páginas de login/cadastro
 * 
 * @param string $destino URL para redirecionar se logado
 * @return void
 */
function protegerPaginaVisitante($destino = null) {
    if ($destino === null) $destino = APP_BASE . '/src/Views/usuario/meu_perfil.php';
    if (sessaoValida()) {
        header("Location: $destino");
        exit;
    }
}

// ============================================================
// FUNÇÕES DE VERIFICAÇÃO DE PERMISSÕES
// ============================================================

/**
 * Verifica se o usuário tem um tipo específico
 * 
 * @param string|array $tipos Tipo(s) permitido(s)
 * @return bool True se tiver permissão
 */
function usuarioTemTipo($tipos) {
    if (!estaLogado()) {
        return false;
    }
    
    $tipo_usuario = $_SESSION['usuario_tipo'] ?? '';
    
    if (is_string($tipos)) {
        return $tipo_usuario === $tipos;
    }
    
    return in_array($tipo_usuario, $tipos);
}

/**
 * Verifica se o usuário é colaborador com subtipo específico
 * 
 * @param string|array $subtipos Subtipo(s) permitido(s)
 * @return bool True se for colaborador do subtipo
 */
function usuarioTemSubtipo($subtipos) {
    if (!estaLogado() || $_SESSION['usuario_tipo'] !== 'colaborador') {
        return false;
    }
    
    $subtipo_usuario = $_SESSION['usuario_subtipo'] ?? '';
    
    if (is_string($subtipos)) {
        return $subtipo_usuario === $subtipos;
    }
    
    return in_array($subtipo_usuario, $subtipos);
}

/**
 * Verifica se o usuário é o proprietário de um recurso
 * 
 * @param int $id_recurso ID do recurso (espécie, imagem, etc)
 * @param string $tabela Tabela onde buscar
 * @param string $coluna_usuario Nome da coluna que guarda o ID do usuário
 * @return bool True se for o proprietário
 */
function usuarioEhProprietario($id_recurso, $tabela, $coluna_usuario = 'autor_dados_internet_id') {
    if (!estaLogado()) {
        return false;
    }
    
    require_once __DIR__ . '/../../../config/banco_de_dados.php';
    
    $dono = buscarUm(
        "SELECT $coluna_usuario FROM $tabela WHERE id = :id",
        [':id' => $id_recurso]
    );
    
    return $dono && $dono[$coluna_usuario] == $_SESSION['usuario_id'];
}

// ============================================================
// FUNÇÕES DE REDIRECIONAMENTO POR PERMISSÃO
// ============================================================

/**
 * Redireciona se não tiver o tipo necessário
 * 
 * @param string|array $tipos Tipo(s) permitido(s)
 * @param string $mensagem Mensagem de erro
 * @return void
 */
function permitirApenas($tipos, $mensagem = 'Acesso negado. Você não tem permissão para acessar esta página.') {
    if (!usuarioTemTipo($tipos)) {
        $_SESSION['mensagem_erro'] = $mensagem;
        header('Location: ' . APP_BASE . '/src/Views/publico/busca_caracteristicas.php');
        exit;
    }
}

/**
 * Redireciona se não for colaborador com subtipo específico
 * 
 * @param string|array $subtipos Subtipo(s) permitido(s)
 * @param string $mensagem Mensagem de erro
 * @return void
 */
function permitirApenasSubtipo($subtipos, $mensagem = 'Acesso negado. Você não tem permissão para esta ação.') {
    if (!usuarioTemSubtipo($subtipos)) {
        $_SESSION['mensagem_erro'] = $mensagem;
        header('Location: ' . APP_BASE . '/src/Views/publico/busca_caracteristicas.php');
        exit;
    }
}

// ============================================================
// FUNÇÕES DE MENU E NAVEGAÇÃO
// ============================================================

/**
 * Retorna o menu apropriado baseado no tipo de usuário
 * 
 * @return array Itens do menu
 */
function getMenuPorTipo() {
    $menu = [
        'publico' => [
            ['nome' => 'Buscar Espécies', 'url' => '/penomato_mvp/src/Views/publico/busca_caracteristicas.php', 'icone' => '🔍'],
            ['nome' => 'Sobre', 'url' => '/penomato_mvp/src/Views/publico/sobre.php', 'icone' => 'ℹ️'],
            ['nome' => 'Contato', 'url' => '/penomato_mvp/src/Views/publico/contato.php', 'icone' => '📧']
        ]
    ];
    
    if (!estaLogado()) {
        $menu['publico'][] = ['nome' => 'Login', 'url' => '/penomato_mvp/src/Views/auth/login.php', 'icone' => '🔐'];
        $menu['publico'][] = ['nome' => 'Cadastro', 'url' => '/penomato_mvp/src/Views/auth/cadastro.php', 'icone' => '📝'];
        return $menu['publico'];
    }
    
    // Menu base para todos logados
    $menu_logado = [
        ['nome' => 'Meu Perfil', 'url' => '/penomato_mvp/src/Views/usuario/meu_perfil.php', 'icone' => '👤'],
        ['nome' => 'Minhas Contribuições', 'url' => '/penomato_mvp/src/Views/usuario/minhas_contribuicoes.php', 'icone' => '📊'],
        ['nome' => 'Buscar Espécies', 'url' => '/penomato_mvp/src/Views/publico/busca_caracteristicas.php', 'icone' => '🔍'],
    ];
    
    // Adicionar itens por tipo
    switch ($_SESSION['usuario_tipo']) {
        case 'gestor':
            $menu_logado[] = ['nome' => 'Painel Gestor', 'url' => '/penomato_mvp/src/Views/gestor/painel_gestor.php', 'icone' => '📋'];
            $menu_logado[] = ['nome' => 'Gerenciar Usuários', 'url' => '/penomato_mvp/src/Views/gestor/gerenciar_usuarios.php', 'icone' => '👥'];
            $menu_logado[] = ['nome' => 'Relatórios', 'url' => '/penomato_mvp/src/Views/gestor/relatorios.php', 'icone' => '📈'];
            break;
            
        case 'revisor':
            $menu_logado[] = ['nome' => 'Painel Revisor', 'url' => '/penomato_mvp/src/Views/revisor/painel_revisor.php', 'icone' => '✅'];
            break;
            
        case 'colaborador':
            $menu_logado[] = ['nome' => 'Cadastrar Espécie', 'url' => '/penomato_mvp/src/Views/colaborador/cadastrar_caracteristicas.php', 'icone' => '📝'];
            $menu_logado[] = ['nome' => 'Upload Imagens', 'url' => '/penomato_mvp/src/Views/colaborador/upload_imagem.php', 'icone' => '📸'];
            break;
    }
    
    // Sempre adicionar Sair no final
    $menu_logado[] = ['nome' => 'Sair', 'url' => '/penomato_mvp/src/Controllers/auth/logout_controlador.php', 'icone' => '🚪'];
    
    return $menu_logado;
}

// ============================================================
// FUNÇÕES DE UTILIDADES DA SESSÃO
// ============================================================

/**
 * Define uma mensagem de sucesso na sessão
 * 
 * @param string $mensagem Mensagem de sucesso
 * @return void
 */
function mensagemSucesso($mensagem) {
    $_SESSION['mensagem_sucesso'] = $mensagem;
}

/**
 * Define uma mensagem de erro na sessão
 * 
 * @param string $mensagem Mensagem de erro
 * @return void
 */
function mensagemErro($mensagem) {
    $_SESSION['mensagem_erro'] = $mensagem;
}

/**
 * Define uma mensagem de alerta na sessão
 * 
 * @param string $mensagem Mensagem de alerta
 * @return void
 */
function mensagemAlerta($mensagem) {
    $_SESSION['mensagem_alerta'] = $mensagem;
}

/**
 * Retorna e limpa as mensagens da sessão
 * 
 * @return array Array com 'sucesso', 'erro', 'alerta'
 */
function getMensagens() {
    $mensagens = [
        'sucesso' => $_SESSION['mensagem_sucesso'] ?? '',
        'erro' => $_SESSION['mensagem_erro'] ?? '',
        'alerta' => $_SESSION['mensagem_alerta'] ?? ''
    ];
    
    // Limpar mensagens
    unset($_SESSION['mensagem_sucesso']);
    unset($_SESSION['mensagem_erro']);
    unset($_SESSION['mensagem_alerta']);
    
    return $mensagens;
}

/**
 * Verifica se a sessão está perto de expirar
 * 
 * @return bool True se faltam menos de 5 minutos
 */
function sessaoPertoDeExpirar() {
    if (!isset($_SESSION['login_time'])) {
        return false;
    }
    
    $tempo_restante = TEMPO_EXPIRACAO - (time() - $_SESSION['login_time']);
    return $tempo_restante < TEMPO_ALERTA && $tempo_restante > 0;
}

/**
 * Retorna o tempo restante da sessão em minutos
 * 
 * @return int Minutos restantes
 */
function tempoRestanteSessao() {
    if (!isset($_SESSION['login_time'])) {
        return 0;
    }
    
    $tempo_restante = TEMPO_EXPIRACAO - (time() - $_SESSION['login_time']);
    return max(0, floor($tempo_restante / 60));
}

// ============================================================
// EXEMPLOS DE USO (COMENTADOS)
// ============================================================

/*
// Exemplo 1: Proteger página do revisor
require_once 'verificar_acesso.php';
protegerPagina();
permitirApenas(['revisor', 'gestor']);

// Exemplo 2: Verificar se é o proprietário antes de editar
if (!usuarioEhProprietario($_GET['id'], 'especies_caracteristicas')) {
    mensagemErro('Você só pode editar suas próprias contribuições.');
    header('Location: ../publico/busca_caracteristicas.php');
    exit;
}

// Exemplo 3: Menu dinâmico
$menu = getMenuPorTipo();
foreach ($menu as $item) {
    echo "<a href='{$item['url']}'>{$item['icone']} {$item['nome']}</a>";
}

// Exemplo 4: Mostrar alerta de expiração
if (sessaoPertoDeExpirar()) {
    echo "<div class='alerta'>Sua sessão expira em " . tempoRestanteSessao() . " minutos.</div>";
}
*/

// ============================================================
// FIM DO ARQUIVO
// ============================================================
?>