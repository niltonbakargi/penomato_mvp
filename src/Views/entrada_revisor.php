<?php
// entrada_revisor.php
// MVP - View integrada com o controlador

// O controlador já iniciou sessão e definiu:
// $usuario_nome, $usuario_instituicao
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Revisor - Penomato</title>
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
    <style>
        body {
            background-color: #f0f4f0;
            padding: 30px;
            color: #1e2e1e;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        
        .header {
            background: var(--cor-primaria);
            color: var(--branco);
            padding: 20px 30px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { font-size: 1.5em; }
        .user-badge {
            background-color: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 40px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            background-color: var(--cor-primaria);
            color: var(--branco);
        }
        .btn-secondary {
            background-color: #e9ecef;
            color: #1e2e1e;
        }
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8em;
        }
        .btn-outline {
            background: var(--branco);
            border: 1px solid var(--cor-primaria);
            color: var(--cor-primaria);
        }
        
        .actions-bar {
            background: var(--branco);
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 25px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .main-content {
            background: var(--branco);
            border-radius: 10px;
            padding: 30px;
        }
        
        .revisoes-lista {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .revisao-item {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .revisao-item:hover {
            background-color: var(--cinza-50);
            border-color: var(--cor-primaria);
        }
        
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.7em;
            font-weight: 600;
            margin-left: 5px;
        }
        .badge-alta { background-color: var(--perigo-cor); color: var(--branco); }
        .badge-media { background-color: var(--aviso-cor); color: #1e2e1e; }
        .badge-baixa { background-color: var(--sucesso-cor); color: var(--branco); }
        
        .empty-message {
            text-align: center;
            padding: 40px;
            color: var(--cinza-500);
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .modal.active { display: flex; }
        .modal-content {
            background: var(--branco);
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            color: var(--cinza-500);
        }
        .modal-body {
            padding: 20px;
            overflow-y: auto;
        }
        .modal-list-item {
            padding: 12px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-list-item:hover {
            background-color: var(--cinza-50);
            border-color: var(--cor-primaria);
        }
        
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
        }
        
        .loading {
            text-align: center;
            padding: 30px;
            color: var(--cinza-500);
        }
        
        .info-text {
            font-size: 0.9em;
            color: var(--cinza-500);
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER com usuário dinâmico -->
        <div class="header">
            <h1>🔍 Painel do Revisor</h1>
            <div class="user-badge"><?php echo htmlspecialchars($usuario_nome); ?> · <?php echo htmlspecialchars($usuario_instituicao ?: 'Penomato'); ?></div>
        </div>

        <?php if (!empty($_GET['sucesso'])): ?>
        <div class="alert" style="background:#d4edda;border:1px solid #c3e6cb;color:#155724;padding:12px 18px;border-radius:8px;margin-bottom:16px;">
            ✅ <?php echo htmlspecialchars($_GET['sucesso']); ?>
        </div>
        <?php elseif (!empty($_GET['erro'])): ?>
        <div class="alert" style="background:#f8d7da;border:1px solid #f5c6cb;color:#721c24;padding:12px 18px;border-radius:8px;margin-bottom:16px;">
            ⚠️ <?php echo htmlspecialchars($_GET['erro']); ?>
        </div>
        <?php endif; ?>

        <!-- BARRA DE AÇÕES -->
        <div class="actions-bar">
            <button class="btn" onclick="abrirModalNovaRevisao()">➕ NOVA REVISÃO</button>
            <button class="btn btn-secondary" onclick="carregarRevisoesAndamento()">📂 CONTINUAR</button>
            <a href="/penomato_mvp/src/Views/revisor/revisar_exemplar.php" class="btn btn-outline">🔬 Revisar Exemplares</a>
            <a href="/penomato_mvp/src/Views/revisor/mapa_exemplares.php" class="btn btn-outline">🗺️ Mapa de Exemplares</a>
        </div>

        <!-- CONTEÚDO PRINCIPAL -->
        <div class="main-content" id="mainContent">
            <div class="empty-message">
                <p style="font-size: 1.2em; margin-bottom: 10px;">Nenhuma revisão selecionada</p>
                <p>Clique em "NOVA REVISÃO" para começar ou "CONTINUAR" para ver suas revisões em andamento</p>
            </div>
        </div>
    </div>

    <!-- MODAL - NOVA REVISÃO -->
    <div id="modalNovaRevisao" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Iniciar Nova Revisão</h3>
                <button class="modal-close" onclick="fecharModal()">✕</button>
            </div>
            <div class="modal-body">
                <input type="text" id="buscaEspecie" placeholder="Buscar espécie..." onkeyup="filtrarModal()">
                <div id="modalLista" class="loading">Carregando espécies...</div>
            </div>
        </div>
    </div>

    <script>
        // ============================================
        // VARIÁVEIS GLOBAIS
        // ============================================
        let especiesPendentes = [];
        let revisoesAndamento = [];

        // ============================================
        // MODAL - NOVA REVISÃO
        // ============================================
        function abrirModalNovaRevisao() {
            document.getElementById('modalNovaRevisao').classList.add('active');
            carregarEspeciesPendentes();
        }

        function fecharModal() {
            document.getElementById('modalNovaRevisao').classList.remove('active');
        }

        // Carregar espécies via API
        function carregarEspeciesPendentes() {
            const lista = document.getElementById('modalLista');
            lista.innerHTML = '<div class="loading">Carregando espécies...</div>';
            
            fetch('/penomato_mvp/src/Controllers/controlador_painel_revisor.php?acao=listar_pendentes')
                .then(response => response.json())
                .then(data => {
                    if (data.erro) {
                        lista.innerHTML = '<div class="empty-message">Erro ao carregar</div>';
                        return;
                    }
                    
                    especiesPendentes = data;
                    
                    if (especiesPendentes.length === 0) {
                        lista.innerHTML = '<div class="empty-message">Nenhuma espécie disponível para revisão</div>';
                        return;
                    }
                    
                    let html = '';
                    especiesPendentes.forEach(esp => {
                        let badgeClass = 'badge-media';
                        let badgeText = 'MÉDIA';
                        
                        if (esp.prioridade === 'alta') {
                            badgeClass = 'badge-alta';
                            badgeText = 'ALTA';
                        } else if (esp.prioridade === 'baixa') {
                            badgeClass = 'badge-baixa';
                            badgeText = 'BAIXA';
                        }
                        
                        html += `
                            <div class="modal-list-item" data-nome="${esp.nome_cientifico.toLowerCase()}">
                                <div>
                                    <strong><i>${esp.nome_cientifico}</i></strong>
                                    <span class="badge ${badgeClass}">${badgeText}</span>
                                    ${esp.nome_popular ? '<br><small>' + esp.nome_popular + '</small>' : ''}
                                </div>
                                <button class="btn btn-sm" onclick="iniciarRevisao(${esp.id})">Selecionar</button>
                            </div>
                        `;
                    });
                    
                    lista.innerHTML = html;
                })
                .catch(() => {
                    lista.innerHTML = '<div class="empty-message">Erro ao conectar com servidor</div>';
                });
        }

        // Filtrar modal em tempo real
        function filtrarModal() {
            const termo = document.getElementById('buscaEspecie').value.toLowerCase();
            const itens = document.querySelectorAll('#modalLista .modal-list-item');
            
            itens.forEach(item => {
                const texto = item.getAttribute('data-nome') || '';
                if (texto.includes(termo) || termo === '') {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Iniciar revisão (chama API)
        function iniciarRevisao(id) {
            fetch('/penomato_mvp/src/Controllers/controlador_painel_revisor.php?acao=iniciar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'especie_id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.erro) {
                    alert(data.erro);
                } else if (data.redirect) {
                    window.location.href = data.redirect;
                }
            })
            .catch(() => {
                alert('Erro ao iniciar revisão');
            });
        }

        // ============================================
        // CONTINUAR - Revisões em Andamento
        // ============================================
        function carregarRevisoesAndamento() {
            const main = document.getElementById('mainContent');
            main.innerHTML = '<div class="loading">Carregando revisões...</div>';
            
            fetch('/penomato_mvp/src/Controllers/controlador_painel_revisor.php?acao=listar_andamento')
                .then(response => response.json())
                .then(data => {
                    if (data.erro) {
                        main.innerHTML = '<div class="empty-message">Erro ao carregar</div>';
                        return;
                    }
                    
                    revisoesAndamento = data;
                    
                    if (revisoesAndamento.length === 0) {
                        main.innerHTML = `
                            <div class="empty-message">
                                <p style="font-size: 1.2em; margin-bottom: 10px;">Nenhuma revisão em andamento</p>
                                <p>Clique em "NOVA REVISÃO" para começar</p>
                            </div>
                        `;
                        return;
                    }
                    
                    let html = '<h3 style="margin-bottom: 20px;">📂 Revisões em Andamento</h3>';
                    html += '<div class="revisoes-lista">';
                    
                    revisoesAndamento.forEach(rev => {
                        let badgeClass = 'badge-media';
                        let badgeText = 'MÉDIA';
                        
                        if (rev.prioridade === 'alta') {
                            badgeClass = 'badge-alta';
                            badgeText = 'ALTA';
                        } else if (rev.prioridade === 'baixa') {
                            badgeClass = 'badge-baixa';
                            badgeText = 'BAIXA';
                        }
                        
                        const dataInicio = rev.data_inicio ? new Date(rev.data_inicio).toLocaleDateString() : 'Data desconhecida';
                        
                        html += `
                            <div class="revisao-item">
                                <div>
                                    <strong><i>${rev.nome_cientifico}</i></strong>
                                    <span class="badge ${badgeClass}">${badgeText}</span>
                                    ${rev.nome_popular ? '<br><small>' + rev.nome_popular + '</small>' : ''}
                                    <div class="info-text">Iniciada em: ${dataInicio}</div>
                                </div>
                                <button class="btn btn-sm btn-outline" onclick="continuarRevisao(${rev.id})">Continuar</button>
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                    main.innerHTML = html;
                })
                .catch(() => {
                    main.innerHTML = '<div class="empty-message">Erro ao conectar com servidor</div>';
                });
        }

        function continuarRevisao(id) {
            window.location.href = 'artigo_revisao.php?id=' + id;
        }

        // ============================================
        // UTILITÁRIOS
        // ============================================
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') fecharModal();
        });

        // Fechar modal clicando fora
        document.getElementById('modalNovaRevisao').addEventListener('click', function(e) {
            if (e.target === this) fecharModal();
        });
    </script>
</body>
</html>