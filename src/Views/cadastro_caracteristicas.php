<?php
// ================================================
// CADASTRO DE CARACTERÍSTICAS - CONTROLADOR
// VERSÃO CORRIGIDA E OTIMIZADA
// ================================================

// Iniciar sessão
session_start();

// ================================================
// FUNÇÕES AUXILIARES
// ================================================

/**
 * Verifica se o usuário está logado (proteção básica)
 */
function estaLogado() {
    return isset($_SESSION['usuario_id']);
}

// ================================================
// VERIFICAR AUTENTICAÇÃO
// ================================================
if (!estaLogado()) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

require_once __DIR__ . '/../../config/banco_de_dados.php';

// Variáveis de controle
$mensagem_erro = '';

// ================================================
// PROCESSAR FORMULÁRIO
// ================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (empty($_POST['especie_id'])) {
        $mensagem_erro = "Selecione uma espécie para cadastrar as características.";
    } else {

        $especie_id = (int)$_POST['especie_id'];

        $campos = [
            'especie_id', 'nome_cientifico_completo', 'nome_cientifico_completo_ref',
            'sinonimos', 'sinonimos_ref', 'nome_popular', 'nome_popular_ref',
            'familia', 'familia_ref',
            'forma_folha', 'forma_folha_ref', 'filotaxia_folha', 'filotaxia_folha_ref',
            'tipo_folha', 'tipo_folha_ref', 'tamanho_folha', 'tamanho_folha_ref',
            'textura_folha', 'textura_folha_ref', 'margem_folha', 'margem_folha_ref',
            'venacao_folha', 'venacao_folha_ref',
            'cor_flores', 'cor_flores_ref', 'simetria_floral', 'simetria_floral_ref',
            'numero_petalas', 'numero_petalas_ref', 'tamanho_flor', 'tamanho_flor_ref',
            'disposicao_flores', 'disposicao_flores_ref', 'aroma', 'aroma_ref',
            'tipo_fruto', 'tipo_fruto_ref', 'tamanho_fruto', 'tamanho_fruto_ref',
            'cor_fruto', 'cor_fruto_ref', 'textura_fruto', 'textura_fruto_ref',
            'dispersao_fruto', 'dispersao_fruto_ref', 'aroma_fruto', 'aroma_fruto_ref',
            'tipo_semente', 'tipo_semente_ref', 'tamanho_semente', 'tamanho_semente_ref',
            'cor_semente', 'cor_semente_ref', 'textura_semente', 'textura_semente_ref',
            'quantidade_sementes', 'quantidade_sementes_ref',
            'tipo_caule', 'tipo_caule_ref', 'estrutura_caule', 'estrutura_caule_ref',
            'textura_caule', 'textura_caule_ref', 'cor_caule', 'cor_caule_ref',
            'forma_caule', 'forma_caule_ref', 'modificacao_caule', 'modificacao_caule_ref',
            'diametro_caule', 'diametro_caule_ref', 'ramificacao_caule', 'ramificacao_caule_ref',
            'possui_espinhos', 'possui_espinhos_ref', 'possui_latex', 'possui_latex_ref',
            'possui_seiva', 'possui_seiva_ref', 'possui_resina', 'possui_resina_ref',
            'referencias',
        ];

        $dados = ['especie_id' => $especie_id];
        foreach ($campos as $campo) {
            if ($campo !== 'especie_id') {
                $dados[$campo] = trim($_POST[$campo] ?? '');
            }
        }

        try {
            $stmt_ck = $pdo->prepare("SELECT COUNT(*) FROM especies_caracteristicas WHERE especie_id = ?");
            $stmt_ck->execute([$especie_id]);
            $ja_existe = (int)$stmt_ck->fetchColumn() > 0;

            if ($ja_existe) {
                $sets = [];
                foreach (array_keys($dados) as $col) {
                    if ($col !== 'especie_id') $sets[] = "$col = :$col";
                }
                $sets[] = "data_atualizacao = NOW()";
                $pdo->prepare(
                    "UPDATE especies_caracteristicas SET " . implode(', ', $sets) . " WHERE especie_id = :especie_id"
                )->execute($dados);
            } else {
                $cols = implode(', ', array_keys($dados));
                $phs  = ':' . implode(', :', array_keys($dados));
                $pdo->prepare(
                    "INSERT INTO especies_caracteristicas ($cols) VALUES ($phs)"
                )->execute($dados);
            }

            $pdo->prepare(
                "UPDATE especies_administrativo SET status = 'dados_internet', data_ultima_atualizacao = NOW() WHERE id = ?"
            )->execute([$especie_id]);

            header("Location: sucesso_cadastro.php?id=$especie_id");
            exit;

        } catch (PDOException $e) {
            $mensagem_erro = "Erro ao salvar: " . $e->getMessage();
        }
    }
}

// ================================================
// SE HOUVER ERRO, MOSTRA MENSAGEM
// ================================================
if (!empty($mensagem_erro)) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erro no Cadastro</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: linear-gradient(135deg, var(--cor-primaria) 0%, #1a7a5a 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0;
            }
            .card {
                background: white;
                border-radius: 15px;
                padding: 40px;
                max-width: 500px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                text-align: center;
            }
            .error {
                color: #dc3545;
                font-size: 1.2rem;
                margin: 20px 0;
            }
            .btn {
                background: var(--cor-primaria);
                color: white;
                padding: 12px 30px;
                border: none;
                border-radius: 40px;
                font-size: 1.1rem;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                margin-top: 20px;
            }
            .btn:hover {
                background: #0a4c35;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <h2 style="color: var(--cor-primaria);">❌ Erro no Cadastro</h2>
            <div class="error"><?php echo htmlspecialchars($mensagem_erro, ENT_QUOTES, 'UTF-8'); ?></div>
            <a href="javascript:history.back()" class="btn">← Voltar e corrigir</a>
        </div>
    </body>
    </html>
    <?php
}
?>