<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}
require_once __DIR__ . '/../../../config/banco_de_dados.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acervo de Imagens — Penomato</title>
    <link rel="stylesheet" href="<?= APP_BASE ?>/assets/css/estilo.css">
    <style>
        body {
            background: var(--cinza-100);
            min-height: 100vh;
            padding: var(--esp-8) var(--esp-5);
        }

        .container {
            max-width: 720px;
            margin: 0 auto;
        }

        .voltar {
            display: inline-flex;
            align-items: center;
            gap: var(--esp-2);
            color: var(--cor-primaria);
            text-decoration: none;
            font-size: var(--texto-sm);
            font-weight: var(--peso-semi);
            margin-bottom: var(--esp-6);
        }
        .voltar:hover { text-decoration: underline; }

        /* ── Cabeçalho ── */
        .page-header {
            background: var(--cor-primaria);
            color: var(--branco);
            border-radius: var(--raio-lg);
            padding: var(--esp-6) var(--esp-8);
            margin-bottom: var(--esp-6);
        }
        .page-header h1 {
            font-size: var(--texto-xl);
            font-weight: var(--peso-semi);
            color: var(--branco);
            margin-bottom: var(--esp-1);
        }
        .page-header p {
            font-size: var(--texto-sm);
            opacity: .8;
        }

        /* ── Seções ── */
        .secao {
            background: var(--branco);
            border-radius: var(--raio-lg);
            padding: var(--esp-6) var(--esp-6);
            margin-bottom: var(--esp-5);
            box-shadow: var(--sombra-sm);
        }
        .secao-titulo {
            font-size: var(--texto-base);
            font-weight: var(--peso-semi);
            color: var(--cor-primaria);
            margin-bottom: var(--esp-4);
            display: flex;
            align-items: center;
            gap: var(--esp-2);
        }
        .secao-titulo::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--cinza-200);
            margin-left: var(--esp-2);
        }

        /* ── Botões de ação principais ── */
        .acoes-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--esp-4);
        }
        .acao-btn {
            border: 2px solid var(--cor-primaria);
            border-radius: var(--raio-lg);
            padding: var(--esp-6) var(--esp-4);
            text-align: center;
            text-decoration: none;
            color: var(--cor-primaria);
            font-weight: var(--peso-semi);
            font-size: var(--texto-sm);
            transition: var(--transicao);
            display: block;
        }
        .acao-btn:hover {
            background: var(--cor-primaria);
            color: var(--branco);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(11,94,66,.2);
        }
        .acao-btn .icon { font-size: var(--texto-3xl); display: block; margin-bottom: var(--esp-2); }
        .acao-btn .desc {
            font-size: var(--texto-xs);
            font-weight: var(--peso-normal);
            opacity: .7;
            margin-top: var(--esp-1);
            line-height: 1.4;
        }

        /* ── Protocolo ── */
        .protocolo-lista {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: var(--esp-3);
        }
        .protocolo-lista li {
            display: flex;
            align-items: flex-start;
            gap: var(--esp-3);
            font-size: var(--texto-sm);
            color: var(--cinza-700);
            line-height: 1.5;
        }
        .protocolo-lista .num {
            background: var(--cor-primaria);
            color: var(--branco);
            font-size: var(--texto-xs);
            font-weight: var(--peso-bold);
            border-radius: var(--raio-full);
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* ── Downloads ── */
        .downloads-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: var(--esp-3);
        }
        .download-card {
            border: 1.5px solid var(--cinza-200);
            border-radius: var(--raio-md);
            padding: var(--esp-3) var(--esp-2);
            text-align: center;
            text-decoration: none;
            color: var(--cinza-700);
            transition: var(--transicao);
            display: block;
        }
        .download-card:hover {
            border-color: var(--cor-primaria);
            color: var(--cor-primaria);
            background: #f0fdf4;
            transform: translateY(-1px);
        }
        .download-card .tamanho {
            font-size: var(--texto-lg);
            font-weight: var(--peso-bold);
            display: block;
            margin-bottom: var(--esp-1);
        }
        .download-card .grade-info {
            font-size: var(--texto-xs);
            color: var(--cinza-400);
            display: block;
            margin-bottom: var(--esp-2);
        }
        .download-card .uso {
            font-size: 10px;
            color: var(--cinza-400);
            line-height: 1.3;
        }
        .download-card .icone-pdf {
            font-size: 1.1rem;
            display: block;
            margin-top: var(--esp-2);
            color: var(--cor-primaria);
        }

        /* ── Cartão de cores ── */
        .cartao-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--esp-5);
        }
        .cartao-bloco h3 {
            font-size: var(--texto-sm);
            font-weight: var(--peso-semi);
            color: var(--cinza-700);
            margin-bottom: var(--esp-2);
        }
        .cartao-bloco p, .cartao-bloco li {
            font-size: var(--texto-sm);
            color: var(--cinza-500);
            line-height: 1.6;
        }
        .cartao-bloco ul {
            list-style: none;
            padding: 0;
        }
        .cartao-bloco ul li::before {
            content: '→ ';
            color: var(--cor-primaria);
        }

        .aviso-info {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 4px solid var(--cor-primaria);
            border-radius: var(--raio-md);
            padding: var(--esp-3) var(--esp-4);
            font-size: var(--texto-sm);
            color: var(--cinza-600);
            margin-top: var(--esp-4);
            line-height: 1.5;
        }

        @media (max-width: 540px) {
            .acoes-grid { grid-template-columns: 1fr; }
            .downloads-grid { grid-template-columns: repeat(3, 1fr); }
            .cartao-info { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="container">

    <a href="<?= APP_BASE ?>/src/Views/entrar_colaborador.php" class="voltar">
        ← Voltar ao painel
    </a>

    <!-- Cabeçalho -->
    <div class="page-header">
        <h1>📷 Acervo de Imagens</h1>
        <p>Contribua com o banco de dados visual científico do Cerrado.</p>
    </div>

    <!-- ── Seção 1: O que deseja fazer ── -->
    <div class="secao">
        <div class="secao-titulo">Como deseja contribuir?</div>
        <div class="acoes-grid">

            <a href="<?= APP_BASE ?>/src/Views/adicionar_imagens_internet.php" class="acao-btn">
                <span class="icon">🌐</span>
                Imagens da Internet
                <div class="desc">Adicione imagens com fonte, URL e licença de uso.</div>
            </a>

            <a href="<?= APP_BASE ?>/src/Views/enviar_imagem.php" class="acao-btn">
                <span class="icon">🌿</span>
                Registrar Exsicata Digital
                <div class="desc">Fotos de campo por partes do exemplar, seguindo o protocolo.</div>
            </a>

        </div>
    </div>

    <!-- ── Seção 2: Protocolo de fotodocumentação ── -->
    <div class="secao">
        <div class="secao-titulo">📋 Protocolo de Fotodocumentação</div>
        <p style="font-size:var(--texto-sm);color:var(--cinza-500);margin-bottom:var(--esp-4);line-height:1.6;">
            Para que as imagens de campo tenham valor científico e possam ser usadas futuramente
            em identificação por visão computacional, siga o protocolo abaixo em todas as fotos de exsicata.
        </p>
        <ul class="protocolo-lista">
            <li>
                <span class="num">1</span>
                <span>Fotografe sempre sobre a <strong>folha quadriculada</strong> do tamanho adequado à amostra.</span>
            </li>
            <li>
                <span class="num">2</span>
                <span>Posicione a amostra <strong>plana e sem dobras</strong>, ocupando a maior parte da folha.</span>
            </li>
            <li>
                <span class="num">3</span>
                <span>Use <strong>iluminação difusa</strong> — luz natural à sombra, sem sombras duras sobre a amostra.</span>
            </li>
            <li>
                <span class="num">4</span>
                <span>Fotografe cada <strong>parte da planta separadamente</strong>: folha, flor, fruto, caule, hábito.</span>
            </li>
            <li>
                <span class="num">5</span>
                <span>Se disponível, posicione o <strong>cartão de cores</strong> ao lado da amostra em cada foto.</span>
            </li>
            <li>
                <span class="num">6</span>
                <span>Preencha o <strong>rótulo da folha</strong> (código, espécie, parte, data, coletor, local) antes de fotografar.</span>
            </li>
        </ul>
    </div>

    <!-- ── Seção 3: Folhas quadriculadas ── -->
    <div class="secao">
        <div class="secao-titulo">📐 Folhas Quadriculadas para Download</div>
        <p style="font-size:var(--texto-sm);color:var(--cinza-500);margin-bottom:var(--esp-5);line-height:1.6;">
            Imprima em casa no tamanho certo para cada amostra. O quadriculado serve como escala de referência
            na foto — cada quadrado tem tamanho fixo e conhecido.
        </p>

        <div class="downloads-grid">

            <a href="<?= APP_BASE ?>/src/Views/publico/gerar_folha_quadriculada.php?tamanho=A4"
               class="download-card" target="_blank">
                <span class="tamanho">A4</span>
                <span class="grade-info">grade 1 cm</span>
                <span class="uso">Sementes, flores, detalhes pequenos</span>
                <span class="icone-pdf">⬇ PDF</span>
            </a>

            <a href="<?= APP_BASE ?>/src/Views/publico/gerar_folha_quadriculada.php?tamanho=A3"
               class="download-card" target="_blank">
                <span class="tamanho">A3</span>
                <span class="grade-info">grade 1 cm</span>
                <span class="uso">Folhas simples, ramos pequenos</span>
                <span class="icone-pdf">⬇ PDF</span>
            </a>

            <a href="<?= APP_BASE ?>/src/Views/publico/gerar_folha_quadriculada.php?tamanho=A2"
               class="download-card" target="_blank">
                <span class="tamanho">A2</span>
                <span class="grade-info">grade 1 cm</span>
                <span class="uso">Folhas compostas, ramos grandes</span>
                <span class="icone-pdf">⬇ PDF</span>
            </a>

            <a href="<?= APP_BASE ?>/src/Views/publico/gerar_folha_quadriculada.php?tamanho=A1"
               class="download-card" target="_blank">
                <span class="tamanho">A1</span>
                <span class="grade-info">grade 5 cm</span>
                <span class="uso">Arbustos, partes grandes</span>
                <span class="icone-pdf">⬇ PDF</span>
            </a>

            <a href="<?= APP_BASE ?>/src/Views/publico/gerar_folha_quadriculada.php?tamanho=A0"
               class="download-card" target="_blank">
                <span class="tamanho">A0</span>
                <span class="grade-info">grade 10 cm</span>
                <span class="uso">Hábito, palmeiras — plote em gráfica</span>
                <span class="icone-pdf">⬇ PDF</span>
            </a>

        </div>

        <div class="aviso-info">
            💡 <strong>Dica:</strong> Para A4 e A3 qualquer impressora doméstica serve.
            Para A2 e maiores, leve o arquivo a uma gráfica ou papelaria com plotter.
            O rótulo na parte inferior da folha deve ser preenchido à mão antes de fotografar.
        </div>
    </div>

    <!-- ── Seção 4: Cartão de cores ── -->
    <div class="secao">
        <div class="secao-titulo">🎨 Cartão de Cores de Referência</div>
        <div class="cartao-info">

            <div class="cartao-bloco">
                <h3>Para que serve?</h3>
                <p>
                    O cartão de cores calibrado aparece em cada foto junto à amostra.
                    Ele permite que o sistema corrija variações de iluminação entre fotos tiradas
                    em condições diferentes — câmeras, horários e locais distintos.
                    Essencial para análise de cor por inteligência artificial no futuro.
                </p>
            </div>

            <div class="cartao-bloco">
                <h3>Como obter</h3>
                <ul>
                    <li>Leve o arquivo PDF a uma gráfica</li>
                    <li>Peça impressão com perfil ICC calibrado (prova de cor)</li>
                    <li>Custo estimado: R$ 10–30 por unidade</li>
                    <li>Plastifique para durabilidade em campo</li>
                </ul>
            </div>

        </div>

        <div class="aviso-info" style="margin-top:var(--esp-4);">
            ⏳ <strong>Em breve:</strong> O arquivo do cartão de cores estará disponível para download aqui.
            Por enquanto, fotografe sempre com a folha quadriculada — a escala é a prioridade.
        </div>
    </div>

</div>
</body>
</html>
