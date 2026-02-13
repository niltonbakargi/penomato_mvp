<?php
// artigo_revisao.php
// Local: C:\xampp\htdocs\penomato_mvp\src\Views\artigo_revisao.php

session_start();

// Usuário fixo para teste (ID 1)
$_SESSION['usuario_id'] = 1;
$_SESSION['usuario_nome'] = 'Dr. Norton';
$_SESSION['usuario_tipo'] = 'revisor';

// Verificar se tem ID da espécie
$especie_id = $_GET['id'] ?? 0;
if (!$especie_id) {
    header('Location: ../Controllers/controlador_painel_revisor.php');
    exit;
}

// Buscar dados da espécie - APENAS COLUNAS QUE CERTAMENTE EXISTEM
$conn = mysqli_connect("127.0.0.1", "root", "", "penomato");
if (!$conn) {
    die("Erro de conexão: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

// Buscar informações básicas da espécie - APENAS colunas básicas
$sql_especie = "SELECT 
                    id,
                    nome_cientifico
                FROM especies_administrativo 
                WHERE id = $especie_id";

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
    <title>Revisão: <?php echo $especie['nome_cientifico']; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f4f0;
            padding: 20px;
            color: #1e2e1e;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .back-link {
            display: inline-block;
            margin-bottom: 15px;
            color: #0b5e42;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .header {
            background: #0b5e42;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .header h1 {
            font-size: 1.8em;
            margin-bottom: 8px;
        }

        .header-meta {
            font-size: 0.95em;
            opacity: 0.9;
            line-height: 1.5;
        }

        .section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .section-title {
            font-size: 1.3em;
            color: #0b5e42;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .char-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .char-item {
            margin-bottom: 10px;
        }

        .char-label {
            font-weight: bold;
            font-size: 0.85em;
            color: #666;
            text-transform: uppercase;
        }

        .char-value {
            font-size: 1.1em;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .ref-link {
            background: #e9ecef;
            color: #0b5e42;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 0.75em;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }

        .ref-link:hover {
            background: #0b5e42;
            color: white;
        }

        .references {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .references h3 {
            color: #0b5e42;
            margin-bottom: 15px;
        }

        .ref-item {
            padding: 8px 0;
            border-bottom: 1px dashed #ddd;
            font-size: 0.9em;
        }

        .ref-number {
            display: inline-block;
            background: #0b5e42;
            color: white;
            width: 22px;
            height: 22px;
            text-align: center;
            border-radius: 4px;
            font-size: 0.8em;
            margin-right: 10px;
            line-height: 22px;
        }

        .decision-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 25px;
        }

        .decision-title {
            font-size: 1.2em;
            color: #0b5e42;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .radio-group {
            margin-bottom: 15px;
        }

        .radio-group label {
            margin-right: 25px;
            cursor: pointer;
        }

        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 15px 0;
            font-family: inherit;
            resize: vertical;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            margin-right: 10px;
            font-size: 1em;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .action-bar {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- LINK VOLTAR -->
        <a href="../Controllers/controlador_painel_revisor.php" class="back-link">← Voltar ao painel</a>

        <!-- CABEÇALHO -->
        <div class="header">
            <h1><?php echo htmlspecialchars($especie['nome_cientifico']); ?></h1>
            <div class="header-meta">
                <?php if (!empty($caracteristicas['familia'])): ?>
                    <div>🌿 Família: <?php echo htmlspecialchars($caracteristicas['familia']); ?></div>
                <?php endif; ?>
                <?php if (!empty($caracteristicas['nome_popular'])): ?>
                    <div>🌳 Nome popular: <?php echo htmlspecialchars($caracteristicas['nome_popular']); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- CARACTERÍSTICAS -->
        <?php if ($caracteristicas): ?>

            <!-- Nome científico completo -->
            <?php if (!empty($caracteristicas['nome_cientifico_completo'])): ?>
            <div class="section">
                <div class="section-title">📋 Nome Científico Completo</div>
                <div class="char-value">
                    <?php echo exibirCaracteristica(
                        $caracteristicas['nome_cientifico_completo'],
                        $caracteristicas['nome_cientifico_completo_ref'] ?? ''
                    ); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- FOLHA -->
            <?php 
            $tem_folha = !empty($caracteristicas['forma_folha']) || 
                         !empty($caracteristicas['filotaxia_folha']) || 
                         !empty($caracteristicas['tipo_folha']) ||
                         !empty($caracteristicas['margem_folha']) ||
                         !empty($caracteristicas['textura_folha']) ||
                         !empty($caracteristicas['venacao_folha']) ||
                         !empty($caracteristicas['tamanho_folha']);
            
            if ($tem_folha): 
            ?>
            <div class="section">
                <div class="section-title">🍃 Folha</div>
                <div class="char-grid">
                    <?php if (!empty($caracteristicas['forma_folha'])): ?>
                    <div class="char-item">
                        <div class="char-label">Forma</div>
                        <div class="char-value">
                            <?php echo exibirCaracteristica(
                                $caracteristicas['forma_folha'],
                                $caracteristicas['forma_folha_ref'] ?? ''
                            ); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($caracteristicas['filotaxia_folha'])): ?>
                    <div class="char-item">
                        <div class="char-label">Filotaxia</div>
                        <div class="char-value">
                            <?php echo exibirCaracteristica(
                                $caracteristicas['filotaxia_folha'],
                                $caracteristicas['filotaxia_folha_ref'] ?? ''
                            ); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($caracteristicas['tipo_folha'])): ?>
                    <div class="char-item">
                        <div class="char-label">Tipo</div>
                        <div class="char-value">
                            <?php echo exibirCaracteristica(
                                $caracteristicas['tipo_folha'],
                                $caracteristicas['tipo_folha_ref'] ?? ''
                            ); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($caracteristicas['margem_folha'])): ?>
                    <div class="char-item">
                        <div class="char-label">Margem</div>
                        <div class="char-value">
                            <?php echo exibirCaracteristica(
                                $caracteristicas['margem_folha'],
                                $caracteristicas['margem_folha_ref'] ?? ''
                            ); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- FLOR -->
            <?php 
            $tem_flor = !empty($caracteristicas['cor_flores']) || 
                        !empty($caracteristicas['simetria_floral']) || 
                        !empty($caracteristicas['numero_petalas']) ||
                        !empty($caracteristicas['aroma']);
            
            if ($tem_flor): 
            ?>
            <div class="section">
                <div class="section-title">🌸 Flor</div>
                <div class="char-grid">
                    <?php if (!empty($caracteristicas['cor_flores'])): ?>
                    <div class="char-item">
                        <div class="char-label">Cor</div>
                        <div class="char-value">
                            <?php echo exibirCaracteristica(
                                $caracteristicas['cor_flores'],
                                $caracteristicas['cor_flores_ref'] ?? ''
                            ); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($caracteristicas['aroma'])): ?>
                    <div class="char-item">
                        <div class="char-label">Aroma</div>
                        <div class="char-value">
                            <?php echo exibirCaracteristica(
                                $caracteristicas['aroma'],
                                $caracteristicas['aroma_ref'] ?? ''
                            ); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($caracteristicas['numero_petalas'])): ?>
                    <div class="char-item">
                        <div class="char-label">Nº pétalas</div>
                        <div class="char-value">
                            <?php echo exibirCaracteristica(
                                $caracteristicas['numero_petalas'],
                                $caracteristicas['numero_petalas_ref'] ?? ''
                            ); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- FRUTO -->
            <?php 
            $tem_fruto = !empty($caracteristicas['tipo_fruto']) || 
                         !empty($caracteristicas['dispersao_fruto']) ||
                         !empty($caracteristicas['cor_fruto']);
            
            if ($tem_fruto): 
            ?>
            <div class="section">
                <div class="section-title">🍎 Fruto</div>
                <div class="char-grid">
                    <?php if (!empty($caracteristicas['tipo_fruto'])): ?>
                    <div class="char-item">
                        <div class="char-label">Tipo</div>
                        <div class="char-value">
                            <?php echo exibirCaracteristica(
                                $caracteristicas['tipo_fruto'],
                                $caracteristicas['tipo_fruto_ref'] ?? ''
                            ); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($caracteristicas['dispersao_fruto'])): ?>
                    <div class="char-item">
                        <div class="char-label">Dispersão</div>
                        <div class="char-value">
                            <?php echo exibirCaracteristica(
                                $caracteristicas['dispersao_fruto'],
                                $caracteristicas['dispersao_fruto_ref'] ?? ''
                            ); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- SEMENTE -->
            <?php if (!empty($caracteristicas['tipo_semente'])): ?>
            <div class="section">
                <div class="section-title">🌱 Semente</div>
                <div class="char-grid">
                    <div class="char-item">
                        <div class="char-label">Tipo</div>
                        <div class="char-value">
                            <?php echo exibirCaracteristica(
                                $caracteristicas['tipo_semente'],
                                $caracteristicas['tipo_semente_ref'] ?? ''
                            ); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- CAULE -->
            <?php if (!empty($caracteristicas['tipo_caule'])): ?>
            <div class="section">
                <div class="section-title">🌿 Caule</div>
                <div class="char-grid">
                    <div class="char-item">
                        <div class="char-label">Tipo</div>
                        <div class="char-value">
                            <?php echo exibirCaracteristica(
                                $caracteristicas['tipo_caule'],
                                $caracteristicas['tipo_caule_ref'] ?? ''
                            ); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- OUTRAS CARACTERÍSTICAS -->
            <?php 
            $tem_outras = !empty($caracteristicas['possui_espinhos']) || 
                          !empty($caracteristicas['possui_latex']) ||
                          !empty($caracteristicas['possui_seiva']);
            
            if ($tem_outras): 
            ?>
            <div class="section">
                <div class="section-title">🔬 Outras Características</div>
                <div class="char-grid">
                    <?php if (!empty($caracteristicas['possui_espinhos'])): ?>
                    <div class="char-item">
                        <div class="char-label">Espinhos</div>
                        <div class="char-value">
                            <?php echo exibirCaracteristica(
                                $caracteristicas['possui_espinhos'],
                                $caracteristicas['possui_espinhos_ref'] ?? ''
                            ); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($caracteristicas['possui_latex'])): ?>
                    <div class="char-item">
                        <div class="char-label">Látex</div>
                        <div class="char-value">
                            <?php echo exibirCaracteristica(
                                $caracteristicas['possui_latex'],
                                $caracteristicas['possui_latex_ref'] ?? ''
                            ); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <p style="text-align: center; color: #666; padding: 30px;">Nenhuma característica cadastrada para esta espécie.</p>
        <?php endif; ?>

        <!-- REFERÊNCIAS -->
        <?php if (!empty($referencias)): ?>
        <div class="references">
            <h3>📚 Referências</h3>
            <?php foreach ($referencias as $num => $ref): ?>
                <div class="ref-item" id="ref-<?php echo $num; ?>">
                    <span class="ref-number"><?php echo $num; ?></span>
                    <?php echo htmlspecialchars($ref); ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- DECISÃO -->
        <div class="decision-box">
            <div class="decision-title">⚖️ Decisão da Revisão</div>
            
            <div class="radio-group">
                <label>
                    <input type="radio" name="decisao" value="aprovar"> ✅ Aprovar
                </label>
                <label>
                    <input type="radio" name="decisao" value="rejeitar"> ❌ Rejeitar
                </label>
            </div>

            <textarea id="observacoes" rows="3" placeholder="Observações (obrigatório se rejeitar)..."></textarea>

            <div class="action-bar">
                <button class="btn btn-success" onclick="enviarParecer()">ENVIAR PARECER</button>
                <button class="btn btn-secondary" onclick="window.location.href='../Controllers/controlador_painel_revisor.php'">CANCELAR</button>
            </div>
        </div>
    </div>

    <script>
        function abrirReferencia(num) {
            const target = document.getElementById('ref-' + num);
            if (target) {
                target.style.backgroundColor = '#fff3cd';
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => {
                    target.style.backgroundColor = '';
                }, 2000);
            }
        }

        function enviarParecer() {
            const decisao = document.querySelector('input[name="decisao"]:checked');
            if (!decisao) {
                alert('Selecione uma decisão');
                return;
            }
            
            const observacoes = document.getElementById('observacoes').value;
            
            if (decisao.value === 'rejeitar' && !observacoes.trim()) {
                alert('Observações são obrigatórias para rejeitar');
                return;
            }
            
            alert('Parecer enviado com sucesso!');
            window.location.href = '../Controllers/controlador_painel_revisor.php';
        }

        // Links de referência
        document.querySelectorAll('.ref-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const match = link.textContent.match(/\[(\d+)\]/);
                if (match) abrirReferencia(match[1]);
            });
        });
    </script>
</body>
</html>