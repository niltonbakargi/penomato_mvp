<?php
// entrada_gestor.php - View do gestor
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Gestor - Penomato</title>
    <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
    <style>
        body {
            background-color: #f0f4f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
            color: #1e2e1e;
        }

        .header {
            background: var(--cor-primaria);
            color: var(--branco);
            padding: 20px 40px;
            border-radius: 12px;
            margin-bottom: 40px;
            text-align: center;
            width: 100%;
            max-width: 600px;
        }
        .header h1 { font-size: 1.4em; font-weight: 600; }
        .header p { font-size: 0.9em; opacity: 0.8; margin-top: 4px; }

        .btn-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            width: 100%;
            max-width: 600px;
        }

        .action-btn {
            background: var(--branco);
            border: 2px solid var(--cor-primaria);
            border-radius: 12px;
            padding: 28px 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 600;
            color: var(--cor-primaria);
            font-size: 0.95em;
        }
        .action-btn:hover {
            background: var(--cor-primaria);
            color: var(--branco);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(11,94,66,0.2);
        }
        .action-btn .icon { font-size: 2em; margin-bottom: 10px; display: block; }

        .action-btn.danger { border-color: var(--perigo-cor); color: var(--perigo-cor); }
        .action-btn.danger:hover { background: var(--perigo-cor); color: var(--branco); }

        .btn-sair {
            margin-top: 30px;
            background: none;
            border: none;
            color: var(--cinza-400);
            font-size: 0.9em;
            cursor: pointer;
            text-decoration: underline;
        }
        .btn-sair:hover { color: var(--perigo-cor); }

        /* ── MODAL ── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-overlay.ativo { display: flex; }

        .modal {
            background: var(--branco);
            border-radius: 14px;
            padding: 30px;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .modal h2 {
            color: var(--cor-primaria);
            margin-bottom: 20px;
            font-size: 1.2em;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0ede8;
        }
        .modal h2.danger { color: var(--perigo-cor); border-color: #f5c6cb; }

        .modal label {
            display: block;
            font-size: 0.87em;
            font-weight: 600;
            color: var(--cinza-600);
            margin-bottom: 5px;
        }
        .modal select,
        .modal textarea,
        .modal input[type=text] {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 0.92em;
            font-family: inherit;
            margin-bottom: 14px;
        }
        .modal select:focus,
        .modal textarea:focus,
        .modal input[type=text]:focus { outline: none; border-color: var(--cor-primaria); }
        .modal textarea { resize: vertical; min-height: 80px; }

        .modal .pendente-lista {
            background: #f9f9f9;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 14px;
            font-size: 0.83em;
            color: var(--cinza-600);
        }
        .modal .pendente-lista span {
            display: inline-block;
            background: var(--aviso-fundo);
            border-radius: 4px;
            padding: 2px 8px;
            margin: 2px;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 6px;
        }
        .btn-confirm {
            background: var(--cor-primaria); color: var(--branco); border: none;
            border-radius: 6px; padding: 10px 24px;
            font-weight: 600; cursor: pointer; font-size: 0.92em;
        }
        .btn-confirm:hover { background: var(--cor-primaria-hover); }
        .btn-confirm.danger { background: var(--perigo-cor); }
        .btn-confirm.danger:hover { background: #b02a37; }
        .btn-cancel {
            background: none; color: var(--cinza-500); border: 1px solid #ccc;
            border-radius: 6px; padding: 10px 20px;
            cursor: pointer; font-size: 0.92em;
        }
        .btn-cancel:hover { background: #f0f0f0; }

        .msg-ok   { background:var(--sucesso-fundo); color:var(--sucesso-texto); border:1px solid #c3e6cb; border-radius:6px; padding:10px 14px; margin-bottom:12px; font-size:0.9em; }
        .msg-warn { background:var(--aviso-fundo); color:var(--aviso-texto); border:1px solid #ffeeba; border-radius:6px; padding:10px 14px; margin-bottom:12px; font-size:0.9em; }
        .msg-err  { background:var(--perigo-fundo); color:var(--perigo-texto); border:1px solid #f5c6cb; border-radius:6px; padding:10px 14px; margin-bottom:12px; font-size:0.9em; }

        @media (max-width: 480px) {
            .btn-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>📊 Painel do Gestor</h1>
        <p><?php echo htmlspecialchars($usuario_nome); ?> · <?php echo htmlspecialchars($usuario_instituicao); ?></p>
    </div>

    <div class="btn-grid">
        <div class="action-btn" onclick="abrirModal('modal-aceitar')">
            <span class="icon">✅</span>
            Aceitar Membro
        </div>
        <div class="action-btn danger" onclick="abrirModal('modal-excluir')">
            <span class="icon">🗑️</span>
            Excluir Membro
        </div>
        <div class="action-btn" onclick="abrirModal('modal-especies')">
            <span class="icon">🌿</span>
            Adicionar Espécies de Interesse
        </div>
        <div class="action-btn" onclick="window.location.href='/penomato_mvp/src/Controllers/gestao_especies.php'">
            <span class="icon">📋</span>
            Gestão de Espécies
        </div>
        <div class="action-btn" onclick="window.location.href='/penomato_mvp/src/Controllers/aprovacao_acoes.php'">
            <span class="icon">✅</span>
            Aprovação de Ações
        </div>
        <div class="action-btn" onclick="window.location.href='/penomato_mvp/src/Controllers/monitoramento.php'">
            <span class="icon">📡</span>
            Monitoramento
        </div>
        <div class="action-btn" onclick="window.location.href='/penomato_mvp/src/Controllers/relatorio_colaboradores.php'">
            <span class="icon">📊</span>
            Relatório de Colaboradores
        </div>
        <div class="action-btn" onclick="window.location.href='/penomato_mvp/src/Views/entrar_colaborador.php'">
            <span class="icon">👤</span>
            Perfil Colaborador
        </div>
    </div>

    <button class="btn-sair" onclick="window.location.href='/penomato_mvp/src/Controllers/auth/logout_controlador.php'">
        🚪 Sair
    </button>

    <!-- ══════════════════════════════════════════ -->
    <!-- MODAL: ACEITAR MEMBRO                      -->
    <!-- ══════════════════════════════════════════ -->
    <div class="modal-overlay" id="modal-aceitar">
        <div class="modal">
            <h2>✅ Aceitar Membro</h2>

            <?php if (!empty($msg_aceitar)): foreach ($msg_aceitar as $m): ?>
                <div class="msg-<?php echo $m['tipo']; ?>"><?php echo htmlspecialchars($m['texto']); ?></div>
            <?php endforeach; endif; ?>

            <?php if (!empty($membros_pendentes)): ?>
                <div class="pendente-lista">
                    Aguardando aprovação:
                    <?php foreach ($membros_pendentes as $p): ?>
                        <span><?php echo htmlspecialchars($p['nome']); ?></span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="font-size:0.85em;color:#888;margin-bottom:14px;">Nenhum membro pendente no momento.</p>
            <?php endif; ?>

            <form method="POST" action="/penomato_mvp/src/Controllers/controlador_gestor.php">
                <label>Membro</label>
                <select name="membro_aceitar_id" required>
                    <option value="">Selecione...</option>
                    <?php foreach ($membros_pendentes as $u): ?>
                        <option value="<?php echo $u['id']; ?>">
                            <?php echo htmlspecialchars($u['nome']); ?> — <?php echo htmlspecialchars($u['email']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Categoria</label>
                <select name="categoria_aceitar">
                    <option value="colaborador">Colaborador</option>
                    <option value="revisor">Revisor</option>
                    <option value="validador">Validador</option>
                    <option value="gestor">Gestor</option>
                </select>

                <label>Motivação / Observação</label>
                <textarea name="motivacao_aceitar" placeholder="Ex: aprovado por critérios do edital XYZ..."></textarea>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="fecharModal('modal-aceitar')">Cancelar</button>
                    <button type="submit" name="aceitar_membro" value="1" class="btn-confirm">✅ Aceitar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════════════════════════════════════════ -->
    <!-- MODAL: EXCLUIR MEMBRO                      -->
    <!-- ══════════════════════════════════════════ -->
    <div class="modal-overlay" id="modal-excluir">
        <div class="modal">
            <h2 class="danger">🗑️ Excluir Membro</h2>

            <?php if (!empty($msg_excluir)): foreach ($msg_excluir as $m): ?>
                <div class="msg-<?php echo $m['tipo']; ?>"><?php echo htmlspecialchars($m['texto']); ?></div>
            <?php endforeach; endif; ?>

            <form method="POST" action="/penomato_mvp/src/Controllers/controlador_gestor.php"
                  onsubmit="return confirm('Tem certeza que deseja excluir este membro permanentemente?')">
                <label>Membro</label>
                <select name="membro_excluir_id" required>
                    <option value="">Selecione...</option>
                    <?php foreach (array_merge($membros_ativos, $membros_pendentes) as $u): ?>
                        <option value="<?php echo $u['id']; ?>">
                            <?php echo htmlspecialchars($u['nome']); ?> — <?php echo htmlspecialchars($u['email']); ?> (<?php echo $u['categoria']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Motivação / Justificativa</label>
                <textarea name="motivacao_excluir" placeholder="Ex: inatividade, violação de conduta..."></textarea>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="fecharModal('modal-excluir')">Cancelar</button>
                    <button type="submit" name="excluir_membro" value="1" class="btn-confirm danger">🗑️ Excluir</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════════════════════════════════════════ -->
    <!-- MODAL: ADICIONAR ESPÉCIES                  -->
    <!-- ══════════════════════════════════════════ -->
    <div class="modal-overlay" id="modal-especies">
        <div class="modal">
            <h2>🌿 Adicionar Espécies de Interesse</h2>

            <?php if (!empty($msg_especies)): foreach ($msg_especies as $m): ?>
                <div class="msg-<?php echo $m['tipo']; ?>"><?php echo htmlspecialchars($m['texto']); ?></div>
            <?php endforeach; endif; ?>

            <form method="POST" action="/penomato_mvp/src/Controllers/controlador_gestor.php">
                <label>Uma ou várias espécies (uma por linha)</label>
                <textarea name="lista_especies" rows="8" placeholder="Acca sellowiana
Euterpe oleracea
Handroanthus impetiginosus"></textarea>
                <small style="color:#888;font-size:0.82em;display:block;margin-top:-10px;margin-bottom:14px;">
                    Espécies já cadastradas serão ignoradas automaticamente.
                </small>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="fecharModal('modal-especies')">Cancelar</button>
                    <button type="submit" name="inserir_especies" value="1" class="btn-confirm">➕ Inserir</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function abrirModal(id) {
        document.getElementById(id).classList.add('ativo');
    }
    function fecharModal(id) {
        document.getElementById(id).classList.remove('ativo');
    }
    // Fechar clicando fora do modal
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) fecharModal(overlay.id);
        });
    });

    // Abrir modal automaticamente se houver mensagem de retorno
    <?php if (!empty($msg_aceitar)): ?>
        abrirModal('modal-aceitar');
    <?php endif; ?>
    <?php if (!empty($msg_excluir)): ?>
        abrirModal('modal-excluir');
    <?php endif; ?>
    <?php if (!empty($msg_especies)): ?>
        abrirModal('modal-especies');
    <?php endif; ?>
    </script>
</body>
</html>
