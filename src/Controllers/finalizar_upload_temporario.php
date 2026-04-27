<?php
// ================================================
// FINALIZAR UPLOAD TEMPORÁRIO - SALVAR TUDO NO BANCO
// ================================================

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
ob_start();

// ================================================
// FUNÇÕES PARA GERAÇÃO AUTOMÁTICA DO ARTIGO
// ================================================
require_once __DIR__ . '/../helpers/gerador_artigo.php';

require_once __DIR__ . '/../../config/banco_de_dados.php';

// ================================================
// VERIFICAR SE USUÁRIO ESTÁ LOGADO
// ================================================
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../Views/auth/login.php?erro=" . urlencode("Faça login para finalizar a importação."));
    exit;
}
$id_usuario = $_SESSION['usuario_id'];
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';

// ================================================
// VERIFICAR SESSÃO TEMPORÁRIA
// ================================================
$temp_id = isset($_GET['temp_id']) ? $_GET['temp_id'] : '';

if (empty($temp_id) || !isset($_SESSION['importacao_temporaria']) || $_SESSION['importacao_temporaria']['temp_id'] !== $temp_id) {
    header("Location: ../Views/upload_imagens_internet.php?erro=" . urlencode("Sessão temporária inválida ou expirada."));
    exit;
}

$dados_temporarios     = $_SESSION['importacao_temporaria'];
$especie_id            = $dados_temporarios['especie_id'];
$dados_caracteristicas = $dados_temporarios['dados'] ?? [];

// ================================================
// LOG INICIAL
// ================================================
error_log("========== INICIANDO FINALIZAÇÃO ==========");
error_log("Temp ID: " . $temp_id);
error_log("Espécie ID: " . $especie_id);
error_log("Total campos características: " . count($dados_caracteristicas));

// ================================================
// VERIFICAR PERMISSÃO
// ================================================
if (!isset($dados_temporarios['usuario_id']) || $dados_temporarios['usuario_id'] != $id_usuario) {
    error_log("ERRO: Usuário sem permissão");
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode("Você não tem permissão para finalizar esta importação."));
    exit;
}

// ================================================
// VERIFICAR SE HÁ DADOS PARA SALVAR
// ================================================
if (empty($dados_caracteristicas)) {
    error_log("ERRO: Nenhum dado para salvar");
    header("Location: ../Controllers/inserir_dados_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode("Você precisa colar os dados morfológicos primeiro."));
    exit;
}

// ================================================
// VERIFICAR PARTES OBRIGATÓRIAS (via banco de dados)
// ================================================
// As imagens já foram salvas em especies_imagens durante o upload.
// Aqui só consultamos para confirmar que as partes obrigatórias estão presentes.
// A verificação de partes ocorre após conectar ao banco (abaixo).

// ================================================
// INICIAR TRANSAÇÃO
// ================================================
error_log("Conectado ao banco com sucesso");
$pdo->beginTransaction();
error_log("Transação iniciada");

try {
    
    // ================================================
    // 1. SALVAR DADOS DAS CARACTERÍSTICAS
    // ================================================
    error_log("--- Salvando características ---");

    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM especies_caracteristicas WHERE especie_id = ?");
    $stmt_check->execute([$especie_id]);
    $ja_existe = (int) $stmt_check->fetchColumn() > 0;

    error_log("Características já existem? " . ($ja_existe ? "SIM" : "NÃO"));

    if ($ja_existe) {
        $sets    = [];
        $valores = [];
        foreach ($dados_caracteristicas as $campo => $valor) {
            if ($campo !== 'especie_id') {
                $sets[]    = "$campo = ?";
                $valores[] = $valor;
            }
        }
        $valores[] = $especie_id;
        $sql = "UPDATE especies_caracteristicas SET " . implode(', ', $sets) . " WHERE especie_id = ?";
        error_log("SQL UPDATE: " . $sql);
        $pdo->prepare($sql)->execute($valores);
        error_log("UPDATE realizado com sucesso");
    } else {
        $colunas      = implode(', ', array_keys($dados_caracteristicas));
        $placeholders = implode(', ', array_fill(0, count($dados_caracteristicas), '?'));
        $sql = "INSERT INTO especies_caracteristicas ($colunas) VALUES ($placeholders)";
        error_log("SQL INSERT: " . $sql);
        $pdo->prepare($sql)->execute(array_values($dados_caracteristicas));
        error_log("INSERT realizado com sucesso");
    }

    // ================================================
    // 2. ATUALIZAR STATUS DA ESPÉCIE (dados_internet)
    // ================================================
    error_log("--- Atualizando status da espécie ---");
    $pdo->prepare(
        "UPDATE especies_administrativo
         SET status = 'dados_internet', autor_dados_internet_id = ?, data_dados_internet = NOW()
         WHERE id = ?"
    )->execute([$id_usuario, $especie_id]);
    error_log("Status atualizado para 'dados_internet'");

    // ================================================
    // 3. (IMAGENS JÁ SALVAS) — verificar partes obrigatórias no BD
    // ================================================
    $partes_obrigatorias = ['folha', 'flor', 'fruto', 'caule', 'habito'];

    $stmt_partes = $pdo->prepare(
        "SELECT DISTINCT parte_planta FROM especies_imagens
         WHERE especie_id = ? AND status_validacao = 'aprovado'"
    );
    $stmt_partes->execute([$especie_id]);
    $partes_com_imagem = array_column($stmt_partes->fetchAll(), null, 'parte_planta');

    $partes_faltando = [];
    foreach ($partes_obrigatorias as $parte) {
        if (empty($partes_com_imagem[$parte])) {
            $partes_faltando[] = ucfirst($parte);
        }
    }

    if (!empty($partes_faltando)) {
        $msg = 'Imagens obrigatórias ausentes: ' . implode(', ', $partes_faltando) . '. Adicione ao menos uma imagem para cada parte obrigatória.';
        error_log("ERRO: Partes sem imagem: " . implode(', ', $partes_faltando));
        $pdo->rollBack();
        header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode($msg));
        exit;
    }

    error_log("Partes obrigatórias confirmadas no banco");

    // ================================================
    // COMMIT - TUDO OK
    // ================================================
    $pdo->commit();
    error_log("COMMIT realizado com sucesso");

    // Registrar no histórico para permitir desfazer
    $pdo->prepare("
        INSERT INTO historico_alteracoes
            (especie_id, id_usuario, tabela_afetada, campo_alterado, valor_anterior, valor_novo, tipo_acao)
        VALUES (?, ?, 'especies_caracteristicas', 'status', 'sem_dados', 'dados_internet', 'insercao')
    ")->execute([$especie_id, $id_usuario]);

    // ================================================
    // 4. GERAR ARTIGO RASCUNHO AUTOMATICAMENTE
    // ================================================
    error_log("--- Gerando artigo rascunho ---");
    try {
        $stmt_c = $pdo->prepare("SELECT * FROM especies_caracteristicas WHERE especie_id = ? LIMIT 1");
        $stmt_c->execute([$especie_id]);
        $c_art = $stmt_c->fetch();

        $stmt_a = $pdo->prepare("SELECT * FROM especies_administrativo WHERE id = ? LIMIT 1");
        $stmt_a->execute([$especie_id]);
        $adm_art = $stmt_a->fetch();

        $stmt_i = $pdo->prepare("
            SELECT parte_planta, caminho_imagem, autor_imagem, licenca, fonte_nome, fonte_url
            FROM especies_imagens
            WHERE especie_id = ?
            ORDER BY FIELD(parte_planta,'habito','folha','flor','fruto','caule','semente')
        ");
        $stmt_i->execute([$especie_id]);
        $imgs_art = $stmt_i->fetchAll();

        if ($c_art && $adm_art) {
            $html_art = gerarHtmlArtigoRascunho($adm_art, $c_art, $imgs_art, $pdo);

            $pdo->prepare("
                INSERT INTO artigos (especie_id, texto_html, status, gerado_em)
                VALUES (?, ?, 'rascunho', NOW())
                ON DUPLICATE KEY UPDATE texto_html = VALUES(texto_html), atualizado_em = NOW(), status = 'rascunho'
            ")->execute([$especie_id, $html_art]);
            error_log("Artigo rascunho gerado: " . strlen($html_art) . " bytes");
        } else {
            error_log("Artigo não gerado: características ou dados administrativos ausentes");
        }
    } catch (Exception $e) {
        error_log("Aviso: Falha ao gerar artigo rascunho: " . $e->getMessage());
        // Não crítico — dados já foram salvos com sucesso
    }

    // ================================================
    // 5. NOTIFICAR ORIENTADOR(ES) POR E-MAIL
    // ================================================
    error_log("--- Enviando notificações ---");
    try {
        require_once __DIR__ . '/../../config/email.php';

        $orientador_id = (int)($dados_temporarios['orientador_id'] ?? 0);

        // Buscar nome da espécie
        $stmt_nome = $pdo->prepare("SELECT nome_cientifico FROM especies_administrativo WHERE id = ? LIMIT 1");
        $stmt_nome->execute([$especie_id]);
        $nome_especie = $stmt_nome->fetchColumn() ?: "ID $especie_id";

        $link = APP_URL . '/src/Controllers/artigos_fila.php';

        $conteudo_email = "
            <p>Olá,</p>
            <p>Um novo conjunto de dados e imagens foi importado para a espécie:</p>
            <p style='font-style:italic; font-size:17px; color:#0b5e42; font-weight:bold;'>{$nome_especie}</p>
            <p>O artigo foi gerado automaticamente como rascunho e aguarda sua revisão.</p>
            <p style='text-align:center; margin:24px 0;'>
                <a href='{$link}'
                   style='background:#0b5e42;color:#fff;padding:12px 28px;border-radius:8px;
                          text-decoration:none;font-weight:bold;display:inline-block;'>
                    Acessar fila de revisão
                </a>
            </p>
            <p style='font-size:13px; color:#888;'>Importado por: <strong>" . htmlspecialchars($nome_usuario) . "</strong></p>
        ";

        if ($orientador_id > 0) {
            // Notificar orientador específico
            $stmt_or = $pdo->prepare("SELECT nome, email FROM usuarios WHERE id = ? AND ativo = 1 LIMIT 1");
            $stmt_or->execute([$orientador_id]);
            $or = $stmt_or->fetch();

            if ($or) {
                $corpo = templateEmail("Nova espécie aguarda revisão", "
                    <p>Olá, <strong>" . htmlspecialchars($or['nome']) . "</strong>!</p>
                    <p>Você foi indicado como <strong>orientador</strong> desta espécie.</p>
                    {$conteudo_email}
                ");
                enviarEmail($or['email'], "Penomato — {$nome_especie} aguarda sua revisão", $corpo);
                error_log("E-mail enviado para orientador: " . $or['email']);
            }
        } else {
            // Sem orientação: notificar todos os especialistas
            $res_todos = $pdo->query(
                "SELECT nome, email FROM usuarios
                 WHERE categoria IN ('revisor') AND ativo = 1
                   AND status_verificacao = 'verificado'"
            )->fetchAll();
            $enviados = 0;
            foreach ($res_todos as $esp) {
                $corpo = templateEmail("Nova espécie disponível para orientação", "
                    <p>Olá, <strong>" . htmlspecialchars($esp['nome']) . "</strong>!</p>
                    <p>Uma nova espécie foi importada <strong>sem orientador definido</strong>.
                       Qualquer especialista pode assumir a revisão.</p>
                    {$conteudo_email}
                ");
                enviarEmail($esp['email'], "Penomato — {$nome_especie} sem orientador (disponível)", $corpo);
                $enviados++;
            }
            error_log("E-mails enviados para {$enviados} especialistas (sem orientação)");
        }
    } catch (Exception $e) {
        error_log("Aviso: Falha ao enviar notificações: " . $e->getMessage());
        // Não crítico — dados já salvos
    }

    // ================================================
    // 7. CONTAR IMAGENS SALVAS E LIMPAR SESSÃO
    // ================================================
    $stmt_count = $pdo->prepare(
        "SELECT COUNT(*) FROM especies_imagens WHERE especie_id = ? AND status_validacao = 'aprovado'"
    );
    $stmt_count->execute([$especie_id]);
    $imagens_salvas = (int) $stmt_count->fetchColumn();

    unset($_SESSION['importacao_temporaria']);
    error_log("Sessão temporária limpa");
    error_log("========== FINALIZAÇÃO CONCLUÍDA COM SUCESSO ==========");

    // ================================================
    // REDIRECIONAR PARA PÁGINA DE SUCESSO
    // ================================================
    header("Location: ../Views/sucesso_importacao.php?especie_id=" . $especie_id . "&imagens=" . $imagens_salvas);
    exit;
    
} catch (Exception $e) {
    // ================================================
    // ROLLBACK EM CASO DE ERRO
    // ================================================
    $pdo->rollBack();
    
    $erro = "Erro ao salvar dados: " . $e->getMessage();
    error_log("ERRO NA TRANSAÇÃO: " . $erro);
    error_log("========== FINALIZAÇÃO FALHOU ==========");
    
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode($erro));
    exit;
}

ob_end_flush();
exit;
?>