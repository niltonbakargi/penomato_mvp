<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisão: Mauritia flexuosa (Buriti)</title>
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
            line-height: 1.6;
            color: #1e2e1e;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,40,0,0.15);
            overflow: hidden;
        }

        /* Cabeçalho */
        .header {
            background: linear-gradient(135deg, #0b5e42 0%, #1a7a5a 100%);
            color: white;
            padding: 25px 35px;
            border-bottom: 4px solid #ffc107;
        }

        .header h1 {
            font-size: 2.2em;
            margin-bottom: 8px;
            font-weight: 600;
            letter-spacing: -0.5px;
        }

        .header h1 i {
            font-style: italic;
            font-weight: 400;
            opacity: 0.9;
        }

        .header-meta {
            display: flex;
            gap: 30px;
            margin-top: 12px;
            font-size: 0.95em;
            opacity: 0.9;
        }

        .header-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .badge {
            background-color: #ffc107;
            color: #1e2e1e;
            padding: 4px 12px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.8em;
            display: inline-block;
            margin-left: 12px;
            vertical-align: middle;
        }

        /* Barra de progresso da revisão */
        .revision-progress {
            background-color: #f8f9fa;
            padding: 20px 35px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .progress-stats {
            display: flex;
            gap: 25px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 1.8em;
            font-weight: 700;
            color: #0b5e42;
            line-height: 1;
        }

        .stat-label {
            font-size: 0.8em;
            text-transform: uppercase;
            color: #6c757d;
            letter-spacing: 0.5px;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 0.95em;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #0b5e42;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0a4c35;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(11,94,66,0.3);
        }

        .btn-secondary {
            background-color: #e9ecef;
            color: #1e2e1e;
        }

        .btn-secondary:hover {
            background-color: #dee2e6;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        /* Conteúdo principal - duas colunas */
        .main-content {
            display: flex;
            min-height: 600px;
        }

        /* Coluna esquerda - Ficha da espécie */
        .species-card {
            flex: 2;
            padding: 30px;
            background: white;
            border-right: 1px solid #e9ecef;
            overflow-y: auto;
            max-height: 800px;
        }

        /* Seções da ficha */
        .section {
            margin-bottom: 32px;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 24px;
        }

        .section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-size: 1.4em;
            color: #0b5e42;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }

        .section-title .icon {
            font-size: 1.3em;
        }

        .characteristic-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px 25px;
        }

        .characteristic-item {
            display: flex;
            flex-direction: column;
        }

        .characteristic-label {
            font-size: 0.8em;
            text-transform: uppercase;
            color: #6c757d;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }

        .characteristic-value {
            font-weight: 500;
            color: #1e2e1e;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .ref-links {
            display: inline-flex;
            gap: 4px;
            margin-left: 6px;
        }

        .ref-link {
            background-color: #e9ecef;
            color: #0b5e42;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.75em;
            text-decoration: none;
            font-weight: 600;
        }

        .ref-link:hover {
            background-color: #0b5e42;
            color: white;
        }

        .attention-point {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px 16px;
            margin-top: 12px;
            border-radius: 0 8px 8px 0;
            font-size: 0.9em;
        }

        .attention-point strong {
            color: #856404;
            display: block;
            margin-bottom: 4px;
        }

        /* Coluna direita - Painel do revisor */
        .review-panel {
            flex: 1;
            background-color: #f8f9fa;
            padding: 30px;
            border-left: 1px solid #dee2e6;
            overflow-y: auto;
            max-height: 800px;
        }

        .panel-title {
            font-size: 1.2em;
            color: #1e2e1e;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0b5e42;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .reference-section {
            background: white;
            border-radius: 8px;
            padding: 18px;
            margin-bottom: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .reference-title {
            font-weight: 600;
            color: #0b5e42;
            margin-bottom: 12px;
            font-size: 1em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .reference-list {
            list-style: none;
        }

        .reference-item {
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px dashed #dee2e6;
            font-size: 0.85em;
        }

        .reference-item:last-child {
            border-bottom: none;
        }

        .ref-number {
            display: inline-block;
            background-color: #0b5e42;
            color: white;
            width: 22px;
            height: 22px;
            text-align: center;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 600;
            margin-right: 8px;
            line-height: 22px;
        }

        .ref-link-btn {
            display: inline-block;
            margin-left: 8px;
            color: #0b5e42;
            text-decoration: none;
            font-size: 0.85em;
        }

        .ref-link-btn:hover {
            text-decoration: underline;
        }

        /* Checklist do revisor */
        .checklist {
            background: white;
            border-radius: 8px;
            padding: 18px;
            margin-bottom: 25px;
        }

        .checklist-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .checklist-item:last-child {
            border-bottom: none;
        }

        .checklist-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .checklist-item label {
            flex: 1;
            cursor: pointer;
        }

        .status-badge {
            font-size: 0.75em;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        .status-ok {
            background-color: #d4edda;
            color: #155724;
        }

        .status-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        /* Área de decisão */
        .decision-box {
            background: white;
            border-radius: 8px;
            padding: 18px;
            margin-top: 20px;
        }

        .decision-option {
            margin-bottom: 12px;
        }

        .decision-option input[type="radio"] {
            margin-right: 8px;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            margin: 10px 0;
            font-family: inherit;
            resize: vertical;
        }

        .final-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <h1>
                <i>Mauritia flexuosa</i> L.f. <span class="badge">Arecaceae</span>
            </h1>
            <div class="header-meta">
                <span>🌳 Nomes populares: Buriti, Miriti, Aguaje, Moriche (+18)</span>
                <span>📅 Submetido: 12/03/2024 por João Silva (UEMS)</span>
                <span>⏳ Aguardando revisão há 3 dias</span>
            </div>
        </div>

        <!-- PROGRESSO -->
        <div class="revision-progress">
            <div class="progress-stats">
                <div class="stat-item">
                    <div class="stat-value">47</div>
                    <div class="stat-label">características</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">8</div>
                    <div class="stat-label">referências</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">3</div>
                    <div class="stat-label">pontos de atenção</div>
                </div>
            </div>
            <div class="action-buttons">
                <button class="btn btn-secondary" onclick="window.print()">🖨️ Exportar PDF</button>
                <button class="btn btn-secondary">🔗 Compartilhar</button>
            </div>
        </div>

        <!-- CONTEÚDO PRINCIPAL: 2 COLUNAS -->
        <div class="main-content">
            <!-- COLUNA ESQUERDA: FICHA DA ESPÉCIE -->
            <div class="species-card">
                <!-- SEÇÃO: INFORMAÇÕES GERAIS -->
                <div class="section">
                    <div class="section-title">
                        <span class="icon">📋</span> Informações Gerais
                    </div>
                    <div class="characteristic-grid">
                        <div class="characteristic-item">
                            <span class="characteristic-label">Nome científico completo</span>
                            <span class="characteristic-value">
                                Mauritia flexuosa L.f.
                                <span class="ref-links">
                                    <a href="#" class="ref-link" onclick="abrirReferencia(1)">[1]</a>
                                    <a href="#" class="ref-link" onclick="abrirReferencia(3)">[3]</a>
                                    <a href="#" class="ref-link" onclick="abrirReferencia(5)">[5]</a>
                                    <a href="#" class="ref-link" onclick="abrirReferencia(8)">[8]</a>
                                </span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Família</span>
                            <span class="characteristic-value">
                                Arecaceae
                                <span class="ref-links">
                                    <a href="#" class="ref-link">[1]</a>
                                    <a href="#" class="ref-link">[3]</a>
                                    <a href="#" class="ref-link">[5]</a>
                                    <a href="#" class="ref-link">[6]</a>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- SEÇÃO: FOLHA -->
                <div class="section">
                    <div class="section-title">
                        <span class="icon">🍃</span> Folha
                    </div>
                    <div class="characteristic-grid">
                        <div class="characteristic-item">
                            <span class="characteristic-label">Forma</span>
                            <span class="characteristic-value">
                                Orbicular
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Filotaxia</span>
                            <span class="characteristic-value">
                                Alterna
                                <span class="ref-links"><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Tipo</span>
                            <span class="characteristic-value">
                                Costapalmada
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Tamanho</span>
                            <span class="characteristic-value">
                                Megafila (>50 cm)
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Textura</span>
                            <span class="characteristic-value">
                                Coriácea
                                <span class="ref-links"><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Margem</span>
                            <span class="characteristic-value">
                                Serrada
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Venação</span>
                            <span class="characteristic-value">
                                Paralela
                                <span class="ref-links"><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- SEÇÃO: FLOR -->
                <div class="section">
                    <div class="section-title">
                        <span class="icon">🌸</span> Flor
                    </div>
                    <div class="characteristic-grid">
                        <div class="characteristic-item">
                            <span class="characteristic-label">Cor</span>
                            <span class="characteristic-value">
                                Creme
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Simetria</span>
                            <span class="characteristic-value">
                                Actinomorfa
                                <span class="ref-links"><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Nº pétalas</span>
                            <span class="characteristic-value">
                                3
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Disposição</span>
                            <span class="characteristic-value">
                                Inflorescência
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Aroma</span>
                            <span class="characteristic-value">
                                Sem cheiro
                                <span class="ref-links"><a href="#" class="ref-link">[3]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Tamanho</span>
                            <span class="characteristic-value">
                                Pequena
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                    </div>

                    <!-- PONTO DE ATENÇÃO (gerado automaticamente) -->
                    <div class="attention-point">
                        <strong>⚠️ Ponto de atenção - Aroma</strong>
                        A característica "Sem cheiro" tem como única referência [3] (Plants For A Future - fonte secundária). 
                        Recomenda-se verificar em literatura especializada se a ausência de aroma é confirmada para a espécie.
                    </div>
                </div>

                <!-- SEÇÃO: FRUTO -->
                <div class="section">
                    <div class="section-title">
                        <span class="icon">🍎</span> Fruto
                    </div>
                    <div class="characteristic-grid">
                        <div class="characteristic-item">
                            <span class="characteristic-label">Tipo</span>
                            <span class="characteristic-value">
                                Drupa
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a><a href="#" class="ref-link">[3]</a><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Tamanho</span>
                            <span class="characteristic-value">
                                Médio (2-5 cm)
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a><a href="#" class="ref-link">[3]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Cor</span>
                            <span class="characteristic-value">
                                Marrom
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a><a href="#" class="ref-link">[3]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Textura</span>
                            <span class="characteristic-value">
                                Coriácea
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Dispersão</span>
                            <span class="characteristic-value">
                                Hidrocórica
                                <span class="ref-links"><a href="#" class="ref-link">[2]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Aroma</span>
                            <span class="characteristic-value">
                                Sem cheiro
                                <span class="ref-links"><a href="#" class="ref-link">[3]</a></span>
                            </span>
                        </div>
                    </div>

                    <!-- PONTO DE ATENÇÃO -->
                    <div class="attention-point">
                        <strong>⚠️ Ponto de atenção - Dispersão</strong>
                        A característica "Hidrocórica" tem como referência [2] (Wikipédia). 
                        Recomenda-se verificar em fonte primária (ex: artigo científico sobre dispersão de palmeiras).
                    </div>
                </div>

                <!-- SEÇÃO: SEMENTE -->
                <div class="section">
                    <div class="section-title">
                        <span class="icon">🌱</span> Semente
                    </div>
                    <div class="characteristic-grid">
                        <div class="characteristic-item">
                            <span class="characteristic-label">Tipo</span>
                            <span class="characteristic-value">
                                Dura
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a><a href="#" class="ref-link">[3]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Tamanho</span>
                            <span class="characteristic-value">
                                Média (5-10 mm)
                                <span class="ref-links"><a href="#" class="ref-link">[3]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Cor</span>
                            <span class="characteristic-value">
                                Marrom
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Textura</span>
                            <span class="characteristic-value">
                                Fibrosa
                                <span class="ref-links"><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Quantidade</span>
                            <span class="characteristic-value">
                                Uma por fruto
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a><a href="#" class="ref-link">[2]</a><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- SEÇÃO: CAULE -->
                <div class="section">
                    <div class="section-title">
                        <span class="icon">🌿</span> Caule
                    </div>
                    <div class="characteristic-grid">
                        <div class="characteristic-item">
                            <span class="characteristic-label">Tipo</span>
                            <span class="characteristic-value">
                                Ereto
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Estrutura</span>
                            <span class="characteristic-value">
                                Lenhoso
                                <span class="ref-links"><a href="#" class="ref-link">[3]</a><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Textura</span>
                            <span class="characteristic-value">
                                Lisa
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Cor</span>
                            <span class="characteristic-value">
                                Cinza
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Forma</span>
                            <span class="characteristic-value">
                                Cilíndrico
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Diâmetro</span>
                            <span class="characteristic-value">
                                Grosso (>5 cm)
                                <span class="ref-links"><a href="#" class="ref-link">[3]</a><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Ramificação</span>
                            <span class="characteristic-value">
                                Monopodial
                                <span class="ref-links"><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- SEÇÃO: OUTRAS CARACTERÍSTICAS -->
                <div class="section">
                    <div class="section-title">
                        <span class="icon">🔬</span> Outras Características
                    </div>
                    <div class="characteristic-grid">
                        <div class="characteristic-item">
                            <span class="characteristic-label">Espinhos</span>
                            <span class="characteristic-value">
                                Não
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Látex</span>
                            <span class="characteristic-value">
                                Não
                                <span class="ref-links"><a href="#" class="ref-link">[1]</a><a href="#" class="ref-link">[6]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Seiva</span>
                            <span class="characteristic-value">
                                Sim
                                <span class="ref-links"><a href="#" class="ref-link">[2]</a><a href="#" class="ref-link">[3]</a></span>
                            </span>
                        </div>
                        <div class="characteristic-item">
                            <span class="characteristic-label">Resina</span>
                            <span class="characteristic-value">
                                Não
                                <span class="ref-links"><a href="#" class="ref-link">[3]</a></span>
                            </span>
                        </div>
                    </div>

                    <!-- PONTO DE ATENÇÃO -->
                    <div class="attention-point">
                        <strong>⚠️ Ponto de atenção - Seiva</strong>
                        "Com seiva" é uma característica muito genérica. Quase todas as plantas têm seiva.
                        Seria relevante especificar se há produção em quantidade significativa ou usos conhecidos.
                        Referências: [2] Wikipédia, [3] PFAF.
                    </div>
                </div>
            </div>

            <!-- COLUNA DIREITA: PAINEL DO REVISOR -->
            <div class="review-panel">
                <div class="panel-title">
                    <span>📌</span> Painel do Revisor
                </div>

                <!-- REFERÊNCIAS -->
                <div class="reference-section">
                    <div class="reference-title">📚 Referências (8)</div>
                    <ul class="reference-list">
                        <li class="reference-item">
                            <span class="ref-number">1</span>
                            <strong>Flora & Fauna Web</strong> (Singapore)
                            <a href="#" class="ref-link-btn" target="_blank">🔗 Acessar</a>
                            <br><small>NATIONAL PARKS BOARD SINGAPORE. Mauritia flexuosa L.f., 2024.</small>
                        </li>
                        <li class="reference-item">
                            <span class="ref-number">2</span>
                            <strong>Wikipédia</strong> 
                            <span class="status-badge status-warning">fonte secundária</span>
                            <a href="#" class="ref-link-btn" target="_blank">🔗 Acessar</a>
                            <br><small>Mauritia flexuosa. Disponível em: https://en.wikipedia.org/wiki/Mauritia_flexuosa</small>
                        </li>
                        <li class="reference-item">
                            <span class="ref-number">3</span>
                            <strong>Plants For A Future (PFAF)</strong>
                            <span class="status-badge status-warning">fonte secundária</span>
                            <a href="#" class="ref-link-btn" target="_blank">🔗 Acessar</a>
                            <br><small>Mauritia flexuosa - L.f.</small>
                        </li>
                        <li class="reference-item">
                            <span class="ref-number">4</span>
                            <strong>ECOPORT/FAO</strong>
                            <a href="#" class="ref-link-btn" target="_blank">🔗 Acessar</a>
                            <br><small>Mauritia flexuosa. FAO, 1993.</small>
                        </li>
                        <li class="reference-item">
                            <span class="ref-number">5</span>
                            <strong>ITIS</strong>
                            <a href="#" class="ref-link-btn" target="_blank">🔗 Acessar</a>
                            <br><small>Integrated Taxonomic Information System</small>
                        </li>
                        <li class="reference-item">
                            <span class="ref-number">6</span>
                            <strong>Virapongse et al. (2017)</strong>
                            <span class="status-badge status-ok">fonte primária</span>
                            <a href="#" class="ref-link-btn" target="_blank">🔗 Acessar</a>
                            <br><small>Ecology, livelihoods, and management of Mauritia flexuosa. ScienceDirect.</small>
                        </li>
                        <li class="reference-item">
                            <span class="ref-number">7</span>
                            <strong>Kew Gardens</strong>
                            <span class="status-badge status-ok">fonte primária</span>
                            <a href="#" class="ref-link-btn" target="_blank">🔗 Acessar</a>
                            <br><small>Herbarium Catalogue Specimen, 1849.</small>
                        </li>
                        <li class="reference-item">
                            <span class="ref-number">8</span>
                            <strong>Plants of the World Online</strong>
                            <span class="status-badge status-ok">fonte primária</span>
                            <a href="#" class="ref-link-btn" target="_blank">🔗 Acessar</a>
                            <br><small>Royal Botanic Gardens, Kew.</small>
                        </li>
                    </ul>
                </div>

                <!-- CHECKLIST DO REVISOR -->
                <div class="checklist">
                    <div class="reference-title">✅ Checklist de Verificação</div>
                    
                    <div class="checklist-item">
                        <input type="checkbox" id="check1">
                        <label for="check1">Nomenclatura e família - fontes consistentes [1,3,5,6,8]</label>
                    </div>
                    
                    <div class="checklist-item">
                        <input type="checkbox" id="check2">
                        <label for="check2">Folha - todas características referenciadas</label>
                    </div>
                    
                    <div class="checklist-item">
                        <input type="checkbox" id="check3">
                        <label for="check3">Flor - verificar aroma (ref. única [3])</label>
                        <span class="status-badge status-warning">atenção</span>
                    </div>
                    
                    <div class="checklist-item">
                        <input type="checkbox" id="check4">
                        <label for="check4">Fruto - verificar dispersão (ref. [2] - Wikipédia)</label>
                        <span class="status-badge status-warning">atenção</span>
                    </div>
                    
                    <div class="checklist-item">
                        <input type="checkbox" id="check5">
                        <label for="check5">Semente - OK</label>
                    </div>
                    
                    <div class="checklist-item">
                        <input type="checkbox" id="check6">
                        <label for="check6">Caule - OK</label>
                    </div>
                    
                    <div class="checklist-item">
                        <input type="checkbox" id="check7">
                        <label for="check7">Outras - "Seiva" muito genérico (ref. [2,3])</label>
                        <span class="status-badge status-warning">atenção</span>
                    </div>
                    
                    <div class="checklist-item">
                        <input type="checkbox" id="check8">
                        <label for="check8">Todas as referências estão acessíveis?</label>
                    </div>
                </div>

                <!-- IMAGENS ANEXADAS -->
                <div class="reference-section">
                    <div class="reference-title">🖼️ Imagens Anexadas (3)</div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px;">
                        <div style="width: 70px; height: 70px; background: #dee2e6; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 1.5em;">🍃</div>
                        <div style="width: 70px; height: 70px; background: #dee2e6; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 1.5em;">🌸</div>
                        <div style="width: 70px; height: 70px; background: #dee2e6; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 1.5em;">🍎</div>
                        <div style="width: 70px; height: 70px; background: #f8f9fa; border: 2px dashed #0b5e42; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 1.2em; color: #0b5e42;">+</div>
                    </div>
                    <p style="margin-top: 12px; font-size: 0.85em;">
                        <strong>Status imagens:</strong> 
                        <span style="color: #0b5e42;">✅ Folha</span> · 
                        <span style="color: #0b5e42;">✅ Flor</span> · 
                        <span style="color: #0b5e42;">✅ Fruto</span> · 
                        <span style="color: #ffc107;">🟡 Caule (pendente)</span>
                    </p>
                </div>

                <!-- DECISÃO FINAL -->
                <div class="decision-box">
                    <div class="reference-title">⚖️ Decisão da Revisão</div>
                    
                    <div class="decision-option">
                        <input type="radio" name="decisao" id="aprovar" value="aprovar">
                        <label for="aprovar"><strong>✅ Aprovar</strong> - dados consistentes com as fontes</label>
                    </div>
                    
                    <div class="decision-option">
                        <input type="radio" name="decisao" id="aprovar_correcoes" value="aprovar_correcoes">
                        <label for="aprovar_correcoes"><strong>📝 Aprovar com correções</strong> - sugestões abaixo</label>
                    </div>
                    
                    <div class="decision-option">
                        <input type="radio" name="decisao" id="rejeitar" value="rejeitar">
                        <label for="rejeitar"><strong>✗ Rejeitar</strong> - necessita revisão substancial</label>
                    </div>
                    
                    <textarea rows="5" placeholder="Observações para o identificador (obrigatório se rejeitar ou sugerir correções)..."></textarea>
                    
                    <div class="final-actions">
                        <button class="btn btn-primary" style="flex: 2;">ENVIAR PARECER</button>
                        <button class="btn btn-secondary" style="flex: 1;">SALVAR RASCUNHO</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function abrirReferencia(num) {
            // Função para abrir modal com preview da referência
            alert(`Abrir referência ${num} em nova aba ou modal com preview`);
            // Na versão final: abrir modal com iframe ou link
        }
    </script>
</body>
</html>