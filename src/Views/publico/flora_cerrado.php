<?php
// ============================================================
// FLORA DO CERRADO — consulta de nomes (REFLORA/JBRJ)
// ============================================================

$titulo_pagina    = 'Flora do Cerrado — Consulta de Nomes';
$descricao_pagina = 'Consulte nomes científicos, nomes populares e sinônimos de espécies do Cerrado';

require_once __DIR__ . '/../../../config/banco_de_dados.php';
require_once __DIR__ . '/../../Controllers/auth/verificar_acesso.php';

$busca = trim($_GET['q'] ?? '');
$resultados_plantas  = [];
$resultados_sinonimos = [];

if ($busca) {
    $termo = "%{$busca}%";

    // Busca em nomes aceitos (nome científico ou popular)
    $stmt = $pdo->prepare(
        "SELECT *,
            CASE WHEN nome_cientifico LIKE ? THEN 0
                 WHEN nomes_vernaculares LIKE ? THEN 1
                 ELSE 2 END AS relevancia
         FROM flora_brasil_plantas
         WHERE nome_cientifico LIKE ? OR nomes_vernaculares LIKE ?
         ORDER BY relevancia, nome_cientifico
         LIMIT 20"
    );
    $stmt->execute([$termo, $termo, $termo, $termo]);
    $resultados_plantas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Busca em sinônimos
    $stmt = $pdo->prepare(
        "SELECT * FROM flora_brasil_sinonimos
         WHERE sinonimo LIKE ?
         ORDER BY sinonimo
         LIMIT 10"
    );
    $stmt->execute([$termo]);
    $resultados_sinonimos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Busca sinônimos de um nome aceito (para exibir na ficha)
function buscarSinonimosDeAceito(PDO $pdo, string $nome): array {
    $stmt = $pdo->prepare(
        "SELECT sinonimo, tipo FROM flora_brasil_sinonimos
         WHERE nome_aceito = ?
         ORDER BY sinonimo"
    );
    $stmt->execute([$nome]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$tem_resultados = !empty($resultados_plantas) || !empty($resultados_sinonimos);

include __DIR__ . '/../includes/cabecalho.php';
?>

<style>
    /* ── Layout geral ── */
    .flora-wrapper {
        min-height: 70vh;
        padding-bottom: var(--esp-14);
    }

    /* ── Hero / barra de busca ── */
    .busca-hero {
        background: linear-gradient(135deg, var(--cor-primaria) 0%, var(--verde-600) 100%);
        padding: var(--esp-12) var(--esp-4) var(--esp-10);
        text-align: center;
    }
    .busca-hero h1 {
        color: var(--branco);
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: var(--esp-2);
    }
    .busca-hero .subtitulo {
        color: rgba(255,255,255,.8);
        font-size: 1rem;
        margin-bottom: var(--esp-6);
    }
    .campo-busca {
        display: flex;
        max-width: 620px;
        margin: 0 auto;
        background: var(--branco);
        border-radius: var(--raio-pill);
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(0,0,0,.2);
    }
    .campo-busca input {
        flex: 1;
        border: none;
        outline: none;
        padding: var(--esp-4) var(--esp-5);
        font-size: 1rem;
        color: var(--cinza-800);
        background: transparent;
    }
    .campo-busca button {
        background: var(--cor-primaria);
        color: var(--branco);
        border: none;
        padding: var(--esp-4) var(--esp-6);
        font-weight: var(--peso-semi);
        cursor: pointer;
        font-size: .95rem;
        transition: background var(--transicao);
        border-radius: 0 var(--raio-pill) var(--raio-pill) 0;
    }
    .campo-busca button:hover { background: var(--cor-primaria-hover); }
    .busca-hint {
        margin-top: var(--esp-3);
        color: rgba(255,255,255,.6);
        font-size: .82rem;
    }
    .busca-hint span {
        background: rgba(255,255,255,.12);
        border-radius: var(--raio-pill);
        padding: 2px 10px;
        margin: 0 3px;
        cursor: pointer;
        transition: background var(--transicao);
    }
    .busca-hint span:hover { background: rgba(255,255,255,.22); }

    /* ── Área de resultados ── */
    .resultados-area {
        max-width: 760px;
        margin: var(--esp-8) auto 0;
        padding: 0 var(--esp-4);
    }
    .resultados-header {
        font-size: .85rem;
        color: var(--cinza-500);
        margin-bottom: var(--esp-5);
        border-bottom: 1px solid var(--cinza-100);
        padding-bottom: var(--esp-3);
    }

    /* ── Card de nome aceito ── */
    .card-especie {
        background: var(--branco);
        border-radius: var(--raio-lg);
        box-shadow: var(--sombra-sm);
        padding: var(--esp-6);
        margin-bottom: var(--esp-5);
        border-left: 4px solid var(--cor-primaria);
        animation: fadeInUp .25s ease-out;
    }
    @keyframes fadeInUp {
        from { opacity:0; transform: translateY(8px); }
        to   { opacity:1; transform: translateY(0); }
    }
    .esp-topo {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: var(--esp-2);
        margin-bottom: var(--esp-3);
    }
    .esp-grupo-familia {
        font-size: .78rem;
        color: var(--cinza-500);
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .badge-ms {
        background: var(--sucesso-fundo);
        color: var(--sucesso-texto);
        font-size: .75rem;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: var(--raio-pill);
    }
    .esp-nome-cientifico {
        font-style: italic;
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--cinza-900);
        line-height: 1.3;
        margin-bottom: var(--esp-1);
    }
    .esp-autor {
        font-style: normal;
        font-weight: 400;
        font-size: .9rem;
        color: var(--cinza-500);
    }
    .esp-secao {
        margin-top: var(--esp-4);
        padding-top: var(--esp-4);
        border-top: 1px solid var(--cinza-100);
    }
    .esp-secao-label {
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--cinza-400);
        margin-bottom: var(--esp-2);
    }
    .nomes-populares {
        display: flex;
        flex-wrap: wrap;
        gap: var(--esp-2);
    }
    .tag-popular {
        background: var(--verde-50);
        color: var(--cor-primaria);
        border-radius: var(--raio-pill);
        padding: var(--esp-1) var(--esp-3);
        font-size: .88rem;
        font-weight: 500;
    }
    .sinonimos-lista {
        display: flex;
        flex-wrap: wrap;
        gap: var(--esp-2);
    }
    .tag-sinonimo {
        background: var(--cinza-100);
        color: var(--cinza-600);
        border-radius: var(--raio-pill);
        padding: var(--esp-1) var(--esp-3);
        font-size: .82rem;
        font-style: italic;
    }
    .formas-lista {
        display: flex;
        flex-wrap: wrap;
        gap: var(--esp-2);
    }
    .tag-forma {
        background: var(--cinza-50);
        color: var(--cinza-600);
        border: 1px solid var(--cinza-200);
        border-radius: var(--raio-pill);
        padding: var(--esp-1) var(--esp-3);
        font-size: .82rem;
    }
    .esp-ufs {
        font-size: .82rem;
        color: var(--cinza-500);
        line-height: 1.6;
    }
    .uf-destaque {
        font-weight: 700;
        color: var(--cor-primaria);
    }
    .esp-rodape {
        margin-top: var(--esp-5);
        padding-top: var(--esp-4);
        border-top: 1px solid var(--cinza-100);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: var(--esp-3);
    }
    .esp-origem {
        font-size: .8rem;
        color: var(--cinza-400);
    }
    .btn-penomato {
        background: var(--cor-primaria);
        color: var(--branco);
        border-radius: var(--raio-pill);
        padding: var(--esp-2) var(--esp-5);
        text-decoration: none;
        font-size: .85rem;
        font-weight: var(--peso-semi);
        transition: background var(--transicao);
        display: inline-flex;
        align-items: center;
        gap: var(--esp-2);
    }
    .btn-penomato:hover { background: var(--cor-primaria-hover); color: var(--branco); }

    /* ── Card de sinônimo ── */
    .card-sinonimo {
        background: var(--aviso-fundo);
        border-radius: var(--raio-lg);
        padding: var(--esp-5) var(--esp-6);
        margin-bottom: var(--esp-5);
        border-left: 4px solid var(--aviso-cor);
        animation: fadeInUp .25s ease-out;
    }
    .sin-badge {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--aviso-texto);
        margin-bottom: var(--esp-2);
        display: flex;
        align-items: center;
        gap: var(--esp-2);
    }
    .sin-nome-invalido {
        font-style: italic;
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--cinza-700);
        text-decoration: line-through;
        text-decoration-color: var(--aviso-cor);
        margin-bottom: var(--esp-2);
    }
    .sin-descricao { font-size: .88rem; color: var(--cinza-600); margin-bottom: var(--esp-3); }
    .sin-tipo-badge {
        font-size: .75rem;
        background: rgba(0,0,0,.07);
        border-radius: var(--raio-pill);
        padding: 1px 8px;
        margin-left: var(--esp-2);
        color: var(--aviso-texto);
    }
    .btn-ver-aceito {
        display: inline-flex;
        align-items: center;
        gap: var(--esp-2);
        background: var(--branco);
        border: 2px solid var(--aviso-cor);
        color: var(--aviso-texto);
        border-radius: var(--raio-pill);
        padding: var(--esp-2) var(--esp-5);
        font-weight: var(--peso-semi);
        font-size: .88rem;
        text-decoration: none;
        transition: all var(--transicao);
    }
    .btn-ver-aceito:hover {
        background: var(--aviso-cor);
        color: var(--branco);
    }

    /* ── Nenhum resultado ── */
    .nao-encontrado {
        text-align: center;
        padding: var(--esp-14) var(--esp-6);
        color: var(--cinza-400);
    }
    .nao-encontrado i { font-size: 3rem; margin-bottom: var(--esp-4); display: block; }
    .nao-encontrado p { font-size: 1.05rem; margin-bottom: var(--esp-2); }
    .nao-encontrado small { font-size: .85rem; }

    /* ── Landing (sem busca) ── */
    .landing-info {
        max-width: 680px;
        margin: var(--esp-10) auto 0;
        padding: 0 var(--esp-4);
        text-align: center;
    }
    .landing-info h2 { font-size: 1rem; font-weight: 600; color: var(--cinza-600); margin-bottom: var(--esp-5); }
    .landing-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: var(--esp-4);
        margin-bottom: var(--esp-8);
    }
    .landing-card {
        background: var(--branco);
        border-radius: var(--raio-lg);
        padding: var(--esp-5) var(--esp-4);
        box-shadow: var(--sombra-sm);
        text-align: center;
    }
    .landing-card i { font-size: 1.8rem; color: var(--cor-primaria); margin-bottom: var(--esp-3); display: block; }
    .landing-card strong { display: block; color: var(--cinza-800); margin-bottom: var(--esp-1); font-size: .95rem; }
    .landing-card span { font-size: .82rem; color: var(--cinza-500); }
    .fonte-credito {
        font-size: .78rem;
        color: var(--cinza-400);
        margin-top: var(--esp-8);
    }
</style>

<div class="flora-wrapper">

    <!-- Hero com busca -->
    <div class="busca-hero">
        <h1><i class="fas fa-seedling me-2"></i>Flora do Cerrado</h1>
        <p class="subtitulo">Consulte nomes científicos, populares e sinônimos</p>

        <form method="GET" action="">
            <div class="campo-busca">
                <input type="text" name="q"
                       placeholder="Nome científico, popular ou sinônimo..."
                       value="<?php echo htmlspecialchars($busca); ?>"
                       autofocus>
                <button type="submit"><i class="fas fa-search me-1"></i>Buscar</button>
            </div>
        </form>

        <p class="busca-hint">
            Ex:
            <span onclick="document.querySelector('[name=q]').value='pequi';this.closest('form').submit()">pequi</span>
            <span onclick="document.querySelector('[name=q]').value='Caryocar brasiliense';this.closest('form').submit()">Caryocar brasiliense</span>
            <span onclick="document.querySelector('[name=q]').value='ipê';this.closest('form').submit()">ipê</span>
        </p>
    </div>

    <?php if ($busca): ?>
    <!-- ── Resultados ── -->
    <div class="resultados-area">

        <?php if (!$tem_resultados): ?>

            <div class="nao-encontrado">
                <i class="fas fa-leaf"></i>
                <p>Nenhum resultado para <strong>"<?php echo htmlspecialchars($busca); ?>"</strong></p>
                <small>Tente um nome diferente ou verifique a ortografia.</small>
            </div>

        <?php else: ?>

            <div class="resultados-header">
                <?php
                $total_r = count($resultados_plantas) + count($resultados_sinonimos);
                echo $total_r . ' resultado' . ($total_r !== 1 ? 's' : '') . ' para "' . htmlspecialchars($busca) . '"';
                ?>
            </div>

            <?php
            // ── Sinônimos encontrados ──────────────────────────────
            foreach ($resultados_sinonimos as $sin):
                $tipo_label = match($sin['tipo'] ?? '') {
                    'heterotipico' => 'Sinônimo heterotípico',
                    'homotipico'   => 'Sinônimo homotípico',
                    'basiônimo', 'basinimo' => 'Basônimo',
                    default        => 'Sinônimo',
                };
            ?>
            <div class="card-sinonimo">
                <div class="sin-badge">
                    <i class="fas fa-exclamation-triangle"></i>
                    Nome desatualizado
                    <?php if ($sin['tipo']): ?>
                        <span class="sin-tipo-badge"><?php echo $tipo_label; ?></span>
                    <?php endif; ?>
                </div>
                <div class="sin-nome-invalido"><?php echo htmlspecialchars($sin['sinonimo']); ?></div>
                <div class="sin-descricao">
                    Este nome não é mais válido. O nome aceito atualmente é:
                </div>
                <a href="?q=<?php echo urlencode($sin['nome_aceito']); ?>" class="btn-ver-aceito">
                    <i class="fas fa-arrow-right"></i>
                    <?php echo htmlspecialchars($sin['nome_aceito']); ?>
                </a>
            </div>
            <?php endforeach; ?>

            <?php
            // ── Nomes aceitos encontrados ─────────────────────────
            foreach ($resultados_plantas as $p):
                $sinonimos_da_especie = buscarSinonimosDeAceito($pdo, $p['nome_cientifico']);
                $ocorre_ms = $p['distr_uf'] && str_contains($p['distr_uf'], 'MS');
                $nomes_pop = $p['nomes_vernaculares']
                    ? array_map('trim', preg_split('/[;,]/', $p['nomes_vernaculares']))
                    : [];
                $formas = $p['formas_vida']
                    ? array_map('trim', preg_split('/[;,]/', $p['formas_vida']))
                    : [];
                // Destaca UF MS nos estados
                $ufs_display = '';
                if ($p['distr_uf']) {
                    $ufs = explode(';', $p['distr_uf']);
                    $ufs_html = array_map(function($uf) {
                        $uf = trim($uf);
                        return $uf === 'MS'
                            ? '<span class="uf-destaque">MS</span>'
                            : htmlspecialchars($uf);
                    }, $ufs);
                    $ufs_display = implode('; ', $ufs_html);
                }
            ?>
            <div class="card-especie">

                <div class="esp-topo">
                    <span class="esp-grupo-familia">
                        <?php echo htmlspecialchars($p['grupo']); ?>
                        &middot; <?php echo htmlspecialchars($p['familia']); ?>
                    </span>
                    <?php if ($ocorre_ms): ?>
                        <span class="badge-ms"><i class="fas fa-map-marker-alt me-1"></i>Ocorre em MS</span>
                    <?php endif; ?>
                </div>

                <div class="esp-nome-cientifico">
                    <?php echo htmlspecialchars($p['nome_cientifico']); ?>
                    <span class="esp-autor"><?php echo htmlspecialchars($p['autor'] ?? ''); ?></span>
                </div>

                <?php if (!empty($nomes_pop)): ?>
                <div class="esp-secao">
                    <div class="esp-secao-label"><i class="fas fa-tag me-1"></i>Nomes populares</div>
                    <div class="nomes-populares">
                        <?php foreach ($nomes_pop as $np): ?>
                            <?php if ($np): ?>
                            <span class="tag-popular"><?php echo htmlspecialchars($np); ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($formas)): ?>
                <div class="esp-secao">
                    <div class="esp-secao-label"><i class="fas fa-tree me-1"></i>Formas de vida</div>
                    <div class="formas-lista">
                        <?php foreach ($formas as $f): ?>
                            <?php if ($f): ?>
                            <span class="tag-forma"><?php echo htmlspecialchars($f); ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($sinonimos_da_especie)): ?>
                <div class="esp-secao">
                    <div class="esp-secao-label"><i class="fas fa-code-branch me-1"></i>Sinônimos (<?php echo count($sinonimos_da_especie); ?>)</div>
                    <div class="sinonimos-lista">
                        <?php foreach ($sinonimos_da_especie as $s): ?>
                        <span class="tag-sinonimo"><?php echo htmlspecialchars($s['sinonimo']); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($ufs_display): ?>
                <div class="esp-secao">
                    <div class="esp-secao-label"><i class="fas fa-map me-1"></i>Distribuição</div>
                    <div class="esp-ufs"><?php echo $ufs_display; ?></div>
                </div>
                <?php endif; ?>

                <div class="esp-rodape">
                    <span class="esp-origem">
                        <?php echo htmlspecialchars($p['origem'] ?? ''); ?>
                        <?php if ($p['endemica'] === 'é endêmica do Brasil'): ?>
                            · <strong style="color:var(--cor-primaria)">Endêmica do Brasil</strong>
                        <?php endif; ?>
                    </span>
                    <a href="/penomato_mvp/src/Views/publico/busca_caracteristicas.php?nome_cientifico_completo=<?php echo urlencode($p['nome_cientifico']); ?>"
                       class="btn-penomato">
                        <i class="fas fa-leaf"></i> Ver exemplares no Penomato
                    </a>
                </div>

            </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>

    <?php else: ?>
    <!-- ── Landing (sem busca ativa) ── -->
    <div class="landing-info">
        <h2>O que você pode consultar</h2>
        <div class="landing-cards">
            <div class="landing-card">
                <i class="fas fa-flask"></i>
                <strong>Nome científico</strong>
                <span>Busque por gênero e espécie</span>
            </div>
            <div class="landing-card">
                <i class="fas fa-tag"></i>
                <strong>Nome popular</strong>
                <span>Pequi, ipê, buriti e outros</span>
            </div>
            <div class="landing-card">
                <i class="fas fa-code-branch"></i>
                <strong>Sinônimos</strong>
                <span>Nomes antigos que ainda circulam</span>
            </div>
        </div>
        <p class="fonte-credito">
            Base <strong>REFLORA — Flora e Funga do Brasil 2020</strong> &middot;
            Jardim Botânico do Rio de Janeiro (JBRJ) &middot; CC-BY &middot;
            12.161 espécies · 2.140 sinônimos
        </p>
    </div>
    <?php endif; ?>

</div><!-- /flora-wrapper -->

<?php include __DIR__ . '/../includes/rodape.php'; ?>
