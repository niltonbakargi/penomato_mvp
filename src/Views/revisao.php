<?php
// revisao.php
// Local: C:\xampp\htdocs\penomato_mvp\src\Views\revisao.php

session_start();

// Verificar login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: /penomato_mvp/login.php');
    exit;
}

// Verificar se tem ID da espécie
$especie_id = $_GET['id'] ?? 0;
if (!$especie_id) {
    header('Location: ../Controladores/controlador_painel_revisor.php');
    exit;
}

// Carregar configuração do banco
require_once __DIR__ . '/../../config/database.php';

// Buscar dados da espécie
$conn = mysqli_connect("127.0.0.1", "root", "", "penomato");
if (!$conn) {
    die("Erro de conexão: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

// Buscar informações básicas da espécie
$sql_especie = "SELECT 
                    ea.id,
                    ea.nome_cientifico,
                    ea.nome_popular,
                    ea.familia,
                    ea.prioridade,
                    ea.status_caracteristicas,
                    ea.status_revisao,
                    u.nome as identificador_nome,
                    u.instituicao as identificador_instituicao,
                    ea.data_criacao
                FROM especies_administrativo ea
                LEFT JOIN usuarios u ON ea.id_identificador_atual = u.id
                WHERE ea.id = $especie_id";

$result_especie = mysqli_query($conn, $sql_especie);
$especie = mysqli_fetch_assoc($result_especie);

if (!$especie) {
    die("Espécie não encontrada");
}

// Buscar características da espécie
$sql_caracteristicas = "SELECT * FROM especies_caracteristicas WHERE especie_id = $especie_id";
$result_caracteristicas = mysqli_query($conn, $sql_caracteristicas);
$caracteristicas = mysqli_fetch_assoc($result_caracteristicas);

// Processar referências
$referencias = [];
if ($caracteristicas && !empty($caracteristicas['referencias'])) {
    $refs = explode("\n", $caracteristicas['referencias']);
    foreach ($refs as $index => $ref) {
        $referencias[$index + 1] = trim($ref);
    }
}

mysqli_close($conn);

// Função auxiliar para exibir características com referências
function exibirCaracteristica($valor, $ref) {
    if (empty($valor)) return '';
    
    $html = htmlspecialchars($valor);
    if (!empty($ref)) {
        $refs = explode(',', $ref);
        foreach ($refs as $r) {
            $r = trim($r);
            if (!empty($r)) {
                $html .= " <span class='ref-link' onclick='abrirReferencia($r)'>[$r]</span>";
            }
        }
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisão: <?php echo $especie['nome_cientifico']; ?> - Penomato</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f0;
            padding: 30px;
            line-height: 1.5;
            color: #1e2e1e;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,40,0,0.1);
            overflow: hidden;
        }

        .back-link {
            display: inline-block;
            margin: 15px 0 0 30px;
            color: #0b5e42;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .header {
            background: #0b5e42;
            color: white;
            padding: 20px 30px;
            border-bottom: 3px solid #ffc107;
        }

        .header h1 {
            font-size: 1.8em;
            margin-bottom: 5px;
        }

        .header h1 i {
            font-style: italic;
        }

        .header-meta {
            display: flex;
            gap: 20px;
            font-size: 0.9em;
            opacity: 0.9;
            margin-top: 5px;
            flex-wrap: wrap;
        }

        .badge {
            background: #ffc107;
            color: #1e2e1e;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
            margin-left: 10px;
        }

        .action-bar {
            background: #f8f9fa;
            padding: 15px 30px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #0b5e42;
            color: white;
        }

        .btn-primary:hover {
            background: #0a4c35;
        }

        .btn-secondary {
            background: #e9ecef;
            color: #1e2e1e;
        }

        .btn-secondary:hover {
            background: #dee2e6;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .main-content {
            padding: 30px;
        }

        .section {
            margin-bottom: 30px;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 20px;
        }

        .section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-size: 1.3em;
            color: #0b5e42;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title .icon {
            font-size: 1.2em;
        }

        .char-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .char-item {
            display: flex;
            flex-direction: column;
        }

        .char-label {
            font-size: 0.8em;
            color: #6c757d;
            text-transform: uppercase;
        }

        .char-value {
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            flex-wrap: wrap;
        }

        .ref-link {
            background: #e9ecef;
            color: #0b5e42;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 0.7em;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            display: inline-block;
        }

        .ref-link:hover {
            background: #0b5e42;
            color: white;
        }

        .attention {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px 15px;
            margin-top: 10px;
            border-radius: 0 5px 5px 0;
            font-size: 0.9em;
        }

        .attention strong {
            color: #856404;
            display: block;
            margin-bottom: 3px;
        }

        .decision-panel {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .decision-title {
            font-weight: 600;
            color: #0b5e42;
            margin-bottom: 15px;
        }

        .decision-option {
            margin-bottom: 10px;
        }

        .decision-option input {
            margin-right: 8px;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            margin: 15px 0;
            font-family: inherit;
            resize: vertical;
        }

        .decision-actions {
            display: flex;
            gap: 10px;
        }

        .references {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .references h4 {
            color: #0b5e42;
            margin-bottom: 10px;
        }

        .ref-item {
            font-size: 0.85em;
            padding: 5px 0;
            border-bottom: 1px dashed #dee2e6;
        }

        .ref-number {
            display: inline-block;
            background: #0b5e42;
            color: white;
            width: 20px;
            height: 20px;
            text-align: center;
            border-radius: 4px;
            font-size: 0.7em;
            margin-right: 8px;
            line-height: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- LINK VOLTAR -->
        <a href="../Controladores/controlador_painel_revisor.php" class="back-link">← Voltar ao painel</a>

        <!-- CABEÇALHO -->
        <div class="header">
            <h1>
                <i><?php echo htmlspecialchars($especie['nome_cientifico']); ?></i>
                <?php if (!empty($especie['familia'])): ?>
                    <span class="badge"><?php echo htmlspecialchars($especie['familia']); ?></span>
                <?php endif; ?>
            </h1>
            <div class="header-meta">
                <?php if (!empty($especie['nome_popular'])): ?>
                    <span>🌳 <?php echo htmlspecialchars($especie['nome_popular']); ?></span>
                <?php endif; ?>
                <span>📅 <?php echo date('d/m/Y', strtotime($especie['data_criacao'])); ?></span>
                <span>👤 <?php echo htmlspecialchars($especie['identificador_nome'] ?? 'Não informado'); ?></span>
            </div>
        </div>

        <!-- BARRA DE AÇÕES -->
        <div class="action-bar">
            <button class="btn btn-primary" onclick="enviarParecer('aprovar')">✅ APROVAR</button>
            <button class="btn btn-secondary" onclick="enviarParecer('corrigir')">📝 CORREÇÕES</button>
            <button class="btn btn-danger" onclick="enviarParecer('rejeitar')">✗ REJEITAR</button>
            <button class="btn btn-secondary" style="margin-left: auto;" onclick="salvarRascunho()">💾 RASCUNHO</button>
        </div>

        <!-- CONTEÚDO PRINCIPAL -->
        <div class="main-content">
            
            <!-- INFORMAÇÕES GERAIS -->
            <?php if ($caracteristicas): ?>
                
                <!-- Nome científico e família -->
                <div class="section">
                    <div class="section-title">
                        <span class="icon">📋</span> Informações Gerais
                    </div>
                    <div class="char-grid">
                        <?php if (!empty($caracteristicas['nome_cientifico_completo'])): ?>
                        <div class="char-item">
                            <span class="char-label">Nome científico completo</span>
                            <span class="char-value">
                                <?php echo exibirCaracteristica(
                                    $caracteristicas['nome_cientifico_completo'],
                                    $caracteristicas['nome_cientifico_completo_ref'] ?? ''
                                ); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($caracteristicas['familia'])): ?>
                        <div class="char-item">
                            <span class="char-label">Família</span>
                            <span class="char-value">
                                <?php echo exibirCaracteristica(
                                    $caracteristicas['familia'],
                                    $caracteristicas['familia_ref'] ?? ''
                                ); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- FOLHA -->
                <?php if (!empty($caracteristicas['forma_folha']) || !empty($caracteristicas['filotaxia_folha']) || !empty($caracteristicas['tipo_folha'])): ?>
                <div class="section">
                    <div class="section-title">
                        <span class="icon">🍃</span> Folha
                    </div>
                    <div class="char-grid">
                        <?php if (!empty($caracteristicas['forma_folha'])): ?>
                        <div class="char-item">
                            <span class="char-label">Forma</span>
                            <span class="char-value">
                                <?php echo exibirCaracteristica(
                                    $caracteristicas['forma_folha'],
                                    $caracteristicas['forma_folha_ref'] ?? ''
                                ); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($caracteristicas['filotaxia_folha'])): ?>
                        <div class="char-item">
                            <span class="char-label">Filotaxia</span>
                            <span class="char-value">
                                <?php echo exibirCaracteristica(
                                    $caracteristicas['filotaxia_folha'],
                                    $caracteristicas['filotaxia_folha_ref'] ?? ''
                                ); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($caracteristicas['tipo_folha'])): ?>
                        <div class="char-item">
                            <span class="char-label">Tipo</span>
                            <span class="char-value">
                                <?php echo exibirCaracteristica(
                                    $caracteristicas['tipo_folha'],
                                    $caracteristicas['tipo_folha_ref'] ?? ''
                                ); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($caracteristicas['margem_folha'])): ?>
                        <div class="char-item">
                            <span class="char-label">Margem</span>
                            <span class="char-value">
                                <?php echo exibirCaracteristica(
                                    $caracteristicas['margem_folha'],
                                    $caracteristicas['margem_folha_ref'] ?? ''
                                ); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- FLOR -->
                <?php if (!empty($caracteristicas['cor_flores']) || !empty($caracteristicas['aroma'])): ?>
                <div class="section">
                    <div class="section-title">
                        <span class="icon">🌸</span> Flor
                    </div>
                    <div class="char-grid">
                        <?php if (!empty($caracteristicas['cor_flores'])): ?>
                        <div class="char-item">
                            <span class="char-label">Cor</span>
                            <span class="char-value">
                                <?php echo exibirCaracteristica(
                                    $caracteristicas['cor_flores'],
                                    $caracteristicas['cor_flores_ref'] ?? ''
                                ); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($caracteristicas['aroma'])): ?>
                        <div class="char-item">
                            <span class="char-label">Aroma</span>
                            <span class="char-value">
                                <?php echo exibirCaracteristica(
                                    $caracteristicas['aroma'],
                                    $caracteristicas['aroma_ref'] ?? ''
                                ); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($caracteristicas['aroma_ref']) && strpos($caracteristicas['aroma_ref'], '3') !== false): ?>
                    <div class="attention">
                        <strong>⚠️ Ponto de atenção</strong>
                        Aroma tem referência [3] (fonte secundária). Verificar.
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- FRUTO -->
                <?php if (!empty($caracteristicas['tipo_fruto']) || !empty($caracteristicas['dispersao_fruto'])): ?>
                <div class="section">
                    <div class="section-title">
                        <span class="icon">🍎</span> Fruto
                    </div>
                    <div class="char-grid">
                        <?php if (!empty($caracteristicas['tipo_fruto'])): ?>
                        <div class="char-item">
                            <span class="char-label">Tipo</span>
                            <span class="char-value">
                                <?php echo exibirCaracteristica(
                                    $caracteristicas['tipo_fruto'],
                                    $caracteristicas['tipo_fruto_ref'] ?? ''
                                ); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($caracteristicas['dispersao_fruto'])): ?>
                        <div class="char-item">
                            <span class="char-label">Dispersão</span>
                            <span class="char-value">
                                <?php echo exibirCaracteristica(
                                    $caracteristicas['dispersao_fruto'],
                                    $caracteristicas['dispersao_fruto_ref'] ?? ''
                                ); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($caracteristicas['dispersao_fruto_ref']) && strpos($caracteristicas['dispersao_fruto_ref'], '2') !== false): ?>
                    <div class="attention">
                        <strong>⚠️ Ponto de atenção</strong>
                        Dispersão referência [2] (Wikipédia).
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <p style="text-align: center; color: #6c757d; padding: 30px;">
                    Nenhuma característica cadastrada para esta espécie.
                </p>
            <?php endif; ?>

            <!-- DECISÃO DO REVISOR -->
            <div class="decision-panel">
                <div class="decision-title">⚖️ Decisão da Revisão</div>
                
                <form id="formDecisao" onsubmit="return false;">
                    <div class="decision-option">
                        <input type="radio" name="decisao" id="aprovar" value="aprovar"> 
                        <label for="aprovar"><strong>✅ Aprovar</strong> - dados consistentes</label>
                    </div>
                    <div class="decision-option">
                        <input type="radio" name="decisao" id="corrigir" value="corrigir"> 
                        <label for="corrigir"><strong>📝 Corrigir</strong> - sugestões abaixo</label>
                    </div>
                    <div class="decision-option">
                        <input type="radio" name="decisao" id="rejeitar" value="rejeitar"> 
                        <label for="rejeitar"><strong>✗ Rejeitar</strong> - precisa refazer</label>
                    </div>

                    <textarea id="observacoes" rows="3" placeholder="Observações (obrigatório se rejeitar)..."></textarea>

                    <div class="decision-actions">
                        <button type="button" class="btn btn-primary" style="flex: 2;" onclick="enviarParecer()">ENVIAR PARECER</button>
                        <button type="button" class="btn btn-secondary" style="flex: 1;" onclick="salvarRascunho()">SALVAR</button>
                    </div>
                </form>
            </div>

            <!-- REFERÊNCIAS -->
            <?php if (!empty($referencias)): ?>
            <div class="references">
                <h4>📚 Referências</h4>
                <?php foreach ($referencias as $num => $ref): ?>
                    <div class="ref-item">
                        <span class="ref-number"><?php echo $num; ?></span>
                        <?php echo htmlspecialchars($ref); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function abrirReferencia(num) {
            // Destacar a referência na lista
            const refs = document.querySelectorAll('.ref-item');
            refs.forEach(ref => {
                ref.style.backgroundColor = '';
            });
            
            const target = Array.from(refs).find(r => 
                r.querySelector('.ref-number')?.textContent == num
            );
            
            if (target) {
                target.style.backgroundColor = '#fff3cd';
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                alert('Referência ' + num + ' não encontrada na lista');
            }
        }

        function enviarParecer() {
            const decisao = document.querySelector('input[name="decisao"]:checked');
            if (!decisao) {
                alert('Selecione uma decisão (Aprovar, Corrigir ou Rejeitar)');
                return;
            }
            
            const observacoes = document.getElementById('observacoes').value;
            
            if (decisao.value === 'rejeitar' && !observacoes.trim()) {
                alert('Observações são obrigatórias para rejeitar');
                return;
            }
            
            // Aqui você faria uma requisição AJAX para salvar
            alert('Parecer enviado com sucesso!\nDecisão: ' + decisao.value);
            
            // Redirecionar de volta ao painel
            window.location.href = '../Controladores/controlador_painel_revisor.php';
        }

        function salvarRascunho() {
            const decisao = document.querySelector('input[name="decisao"]:checked')?.value || 'sem decisão';
            const observacoes = document.getElementById('observacoes').value;
            
            alert('Rascunho salvo!\n' + (decisao !== 'sem decisão' ? 'Decisão: ' + decisao : ''));
        }

        // Links de referência
        document.querySelectorAll('.ref-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const match = link.textContent.match(/\[(\d+)\]/);
                if (match) {
                    abrirReferencia(match[1]);
                }
            });
        });
    </script>
</body>
</html>