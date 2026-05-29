<?php
// ============================================================
// BANCO DE MATRIZES FLORESTAIS — PÁGINA INICIAL DO MÓDULO
// ============================================================

$titulo_pagina    = 'Banco de Matrizes Florestais — Penomato';
$descricao_pagina = 'Mapeamento colaborativo de matrizes florestais do Cerrado';

require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../../Controllers/auth/verificar_acesso.php';

// Contador rápido de matrizes
$total = buscarUm("SELECT COUNT(*) AS total FROM matrizes WHERE status = 'ativo'");
$total_matrizes = $total ? $total['total'] : 0;

include __DIR__ . '/../includes/cabecalho.php';
?>

<style>
    .matrizes-hero {
        background: linear-gradient(135deg, #1a4731 0%, var(--verde-600) 100%);
        padding: 60px 20px 50px;
        text-align: center;
        color: white;
    }

    .matrizes-hero .icone-modulo {
        font-size: 4.5rem;
        background: rgba(255,255,255,0.15);
        width: 110px;
        height: 110px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        border: 3px solid rgba(255,255,255,0.4);
    }

    .matrizes-hero h1 {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .matrizes-hero p {
        font-size: 1.05rem;
        opacity: 0.9;
        max-width: 540px;
        margin: 0 auto 20px;
        line-height: 1.6;
    }

    .badge-contador {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        padding: 6px 20px;
        border-radius: 40px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .botoes-modulo {
        max-width: 480px;
        margin: 50px auto 0;
        padding: 0 20px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .btn-modulo {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 22px 28px;
        border-radius: 16px;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 1.1rem;
        font-weight: 600;
        text-align: left;
    }

    .btn-modulo .icone-btn {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .btn-modulo .texto-btn strong {
        display: block;
        font-size: 1.05rem;
    }

    .btn-modulo .texto-btn span {
        font-size: 0.85rem;
        font-weight: 400;
        opacity: 0.8;
    }

    .btn-nova-matriz {
        background: var(--cor-primaria);
        color: white;
        box-shadow: 0 8px 24px rgba(11,94,66,0.35);
    }

    .btn-nova-matriz .icone-btn {
        background: rgba(255,255,255,0.2);
        color: white;
    }

    .btn-nova-matriz:hover {
        background: var(--cor-primaria-hover);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(11,94,66,0.4);
    }

    .btn-ver-mapa {
        background: white;
        color: var(--cinza-800);
        border: 2px solid var(--cinza-200);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .btn-ver-mapa .icone-btn {
        background: var(--verde-100);
        color: var(--cor-primaria);
    }

    .btn-ver-mapa:hover {
        border-color: var(--cor-primaria);
        color: var(--cinza-800);
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }

    .btn-sair {
        background: var(--cinza-100);
        color: var(--cinza-600);
        text-align: center;
        justify-content: center;
        padding: 14px;
        font-size: 0.95rem;
    }

    .btn-sair:hover {
        background: var(--cinza-200);
        color: var(--cinza-800);
        transform: none;
    }

    .aviso-login {
        background: var(--aviso-fundo);
        color: var(--aviso-texto);
        border-radius: 12px;
        padding: 14px 20px;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 10px;
        max-width: 480px;
        margin: 0 auto;
    }

    .aviso-legal {
        max-width: 480px;
        margin: 30px auto 50px;
        padding: 0 20px;
        font-size: 0.8rem;
        color: var(--cinza-400);
        text-align: center;
        line-height: 1.5;
    }
</style>

<div class="matrizes-hero">
    <div class="icone-modulo">
        <i class="fas fa-tree"></i>
    </div>
    <h1>Banco de Matrizes</h1>
    <p>Mapeie e encontre árvores nativas do Cerrado para coleta de sementes e material propagativo.</p>
    <div class="badge-contador">
        <i class="fas fa-map-marker-alt me-2"></i>
        <?php echo number_format($total_matrizes); ?> matriz<?php echo $total_matrizes !== 1 ? 'es' : ''; ?> registrada<?php echo $total_matrizes !== 1 ? 's' : ''; ?>
    </div>
</div>

<div class="botoes-modulo">

    <?php
        $url_atual = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                   . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $url_login_matrizes = '/penomato_mvp/src/Views/auth/login.php?redirect=' . urlencode($url_atual);
    ?>
    <?php if (estaLogado()): ?>
        <a href="/penomato_mvp/src/Views/matrizes/registrar.php" class="btn-modulo btn-nova-matriz">
            <div class="icone-btn"><i class="fas fa-plus"></i></div>
            <div class="texto-btn">
                <strong>Nova Matriz</strong>
                <span>Registrar uma árvore no campo</span>
            </div>
        </a>
    <?php else: ?>
        <a href="<?php echo htmlspecialchars($url_login_matrizes); ?>" class="btn-modulo btn-nova-matriz">
            <div class="icone-btn"><i class="fas fa-plus"></i></div>
            <div class="texto-btn">
                <strong>Nova Matriz</strong>
                <span>Faça login para registrar</span>
            </div>
        </a>
    <?php endif; ?>

    <a href="/penomato_mvp/src/Views/matrizes/mapa.php" class="btn-modulo btn-ver-mapa">
        <div class="icone-btn"><i class="fas fa-map-marked-alt"></i></div>
        <div class="texto-btn">
            <strong>Ver no Mapa</strong>
            <span>Encontrar matrizes próximas</span>
        </div>
    </a>

    <?php if (!estaLogado()): ?>
    <div class="aviso-login mt-2">
        <i class="fas fa-info-circle"></i>
        Para registrar matrizes, <a href="<?php echo htmlspecialchars($url_login_matrizes); ?>" class="fw-bold">faça login</a> ou <a href="/penomato_mvp/src/Views/auth/cadastro.php" class="fw-bold">crie uma conta</a>.
    </div>
    <?php endif; ?>

    <a href="/penomato_mvp/" class="btn-modulo btn-sair mt-2">
        <i class="fas fa-arrow-left me-2"></i> Voltar ao Penomato
    </a>

</div>

<p class="aviso-legal">
    A coleta de sementes em áreas públicas deve observar a legislação municipal vigente.
    Em propriedade privada, obtenha autorização do proprietário.
    Espécies ameaçadas exigem autorização do IBAMA via SISBIO.
</p>

<?php include __DIR__ . '/../includes/rodape.php'; ?>
