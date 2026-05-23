<?php
// ============================================================
// confirmar_caracteristicas.php
// ============================================================

// ── AJAX: dados da espécie ──────────────────────────────────
if (isset($_GET['acao']) && $_GET['acao'] === 'dados') {
    if (session_status() === PHP_SESSION_NONE) session_start();
    header('Content-Type: application/json; charset=utf-8');
    if (!isset($_SESSION['usuario_id'])) { echo json_encode(null); exit; }
    require_once __DIR__ . '/../../config/banco_de_dados.php';
    $id = (int)($_GET['especie_id'] ?? 0);
    if ($id > 0) {
        $dados = buscarUm(
            "SELECT ec.*, ea.status AS status_especie
             FROM especies_caracteristicas ec
             JOIN especies_administrativo ea ON ea.id = ec.especie_id
             WHERE ec.especie_id = :id",
            [':id' => $id]
        );
        if ($dados) {
            $obs_rows = buscarTodos(
                "SELECT campo, observacao FROM especies_caracteristicas_obs WHERE especie_id = :id",
                [':id' => $id]
            ) ?? [];
            $obs_map = [];
            foreach ($obs_rows as $o) $obs_map[$o['campo']] = $o['observacao'];
            $dados['_obs'] = $obs_map;
        }
        echo json_encode($dados ?: null);
    } else {
        echo json_encode(null);
    }
    exit;
}

// ── AJAX: salvar um campo (auto-save no ✓) ─────────────────
if (isset($_POST['acao']) && $_POST['acao'] === 'salvar_campo') {
    if (session_status() === PHP_SESSION_NONE) session_start();
    header('Content-Type: application/json; charset=utf-8');
    if (!isset($_SESSION['usuario_id'])) { echo json_encode(['ok' => false]); exit; }
    require_once __DIR__ . '/../../config/banco_de_dados.php';

    $especie_id  = (int)($_POST['especie_id'] ?? 0);
    $campo       = trim($_POST['campo'] ?? '');
    $valor       = trim($_POST['valor'] ?? '');
    $ref         = trim($_POST['ref'] ?? '');
    $referencias = trim($_POST['referencias'] ?? '');

    $whitelist = [
        'nome_cientifico_completo','sinonimos','nome_popular','familia',
        'forma_folha','filotaxia_folha','tipo_folha','divisao_folha','paridade_pinnacao',
        'tamanho_folha','textura_folha',
        'margem_folha','venacao_folha','cor_flores','simetria_floral','numero_petalas',
        'disposicao_flores','aroma','tamanho_flor','tipo_fruto','tamanho_fruto',
        'cor_fruto','textura_fruto','dispersao_fruto','aroma_fruto','tipo_semente',
        'tamanho_semente','cor_semente','textura_semente','quantidade_sementes',
        'tipo_caule','textura_caule','cor_caule','forma_caule','modificacao_caule',
        'ramificacao_caule','possui_espinhos','possui_latex','possui_seiva','possui_resina',
    ];

    if (!$especie_id || !in_array($campo, $whitelist, true)) {
        echo json_encode(['ok' => false, 'erro' => 'Campo inválido.']); exit;
    }

    $cr = $campo . '_ref';
    try {
        $existe = buscarUm("SELECT id FROM especies_caracteristicas WHERE especie_id = :id", [':id' => $especie_id]);
        if ($existe) {
            $pdo->prepare("UPDATE especies_caracteristicas SET `{$campo}` = ?, `{$cr}` = ?, referencias = ? WHERE especie_id = ?")
                ->execute([$valor ?: null, $ref ?: null, $referencias ?: null, $especie_id]);
        } else {
            $pdo->prepare("INSERT INTO especies_caracteristicas (especie_id, `{$campo}`, `{$cr}`, referencias) VALUES (?,?,?,?)")
                ->execute([$especie_id, $valor ?: null, $ref ?: null, $referencias ?: null]);
        }
        echo json_encode(['ok' => true]);
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
    }
    exit;
}

// ── AJAX: salvar lista de referências ──────────────────────
if (isset($_POST['acao']) && $_POST['acao'] === 'salvar_referencias') {
    if (session_status() === PHP_SESSION_NONE) session_start();
    header('Content-Type: application/json; charset=utf-8');
    if (!isset($_SESSION['usuario_id'])) { echo json_encode(['ok' => false]); exit; }
    require_once __DIR__ . '/../../config/banco_de_dados.php';
    $especie_id  = (int)($_POST['especie_id'] ?? 0);
    $referencias = trim($_POST['referencias'] ?? '');
    if (!$especie_id) { echo json_encode(['ok' => false]); exit; }
    try {
        $existe = buscarUm("SELECT id FROM especies_caracteristicas WHERE especie_id = :id", [':id' => $especie_id]);
        if ($existe) {
            $pdo->prepare("UPDATE especies_caracteristicas SET referencias = ? WHERE especie_id = ?")->execute([$referencias ?: null, $especie_id]);
        } else {
            $pdo->prepare("INSERT INTO especies_caracteristicas (especie_id, referencias) VALUES (?,?)")->execute([$especie_id, $referencias ?: null]);
        }
        echo json_encode(['ok' => true]);
    } catch (Exception $e) {
        echo json_encode(['ok' => false]);
    }
    exit;
}

// ── AJAX: salvar observação de um campo ────────────────────
if (isset($_POST['acao']) && $_POST['acao'] === 'salvar_obs') {
    if (session_status() === PHP_SESSION_NONE) session_start();
    header('Content-Type: application/json; charset=utf-8');
    if (!isset($_SESSION['usuario_id'])) { echo json_encode(['ok' => false]); exit; }
    require_once __DIR__ . '/../../config/banco_de_dados.php';

    $especie_id = (int)($_POST['especie_id'] ?? 0);
    $campo      = trim($_POST['campo'] ?? '');
    $observacao = trim($_POST['observacao'] ?? '');

    $whitelist_obs = [
        'nome_cientifico_completo','sinonimos','nome_popular','familia',
        'forma_folha','filotaxia_folha','tipo_folha','divisao_folha','paridade_pinnacao',
        'tamanho_folha','textura_folha',
        'margem_folha','venacao_folha','cor_flores','simetria_floral','numero_petalas',
        'disposicao_flores','aroma','tamanho_flor','tipo_fruto','tamanho_fruto',
        'cor_fruto','textura_fruto','dispersao_fruto','aroma_fruto','tipo_semente',
        'tamanho_semente','cor_semente','textura_semente','quantidade_sementes',
        'tipo_caule','textura_caule','cor_caule','forma_caule','modificacao_caule',
        'ramificacao_caule','possui_espinhos','possui_latex','possui_seiva','possui_resina',
    ];

    if (!$especie_id || !in_array($campo, $whitelist_obs, true)) {
        echo json_encode(['ok' => false, 'erro' => 'Campo inválido.']); exit;
    }

    try {
        if ($observacao === '') {
            $pdo->prepare("DELETE FROM especies_caracteristicas_obs WHERE especie_id = ? AND campo = ?")
                ->execute([$especie_id, $campo]);
        } else {
            $pdo->prepare("INSERT INTO especies_caracteristicas_obs (especie_id, campo, observacao)
                           VALUES (?, ?, ?)
                           ON DUPLICATE KEY UPDATE observacao = VALUES(observacao), atualizado_em = CURRENT_TIMESTAMP")
                ->execute([$especie_id, $campo, $observacao]);
        }
        echo json_encode(['ok' => true]);
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'erro' => $e->getMessage()]);
    }
    exit;
}

// ── AJAX: buscar referência por campo via IA ───────────────
if (isset($_POST['acao']) && $_POST['acao'] === 'buscar_ref_campo') {
    @set_time_limit(120);
    if (session_status() === PHP_SESSION_NONE) session_start();
    header('Content-Type: application/json; charset=utf-8');
    if (!isset($_SESSION['usuario_id'])) { echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado.']); exit; }
    require_once __DIR__ . '/../../config/banco_de_dados.php';

    $especie_id   = (int)($_POST['especie_id'] ?? 0);
    $campo        = trim($_POST['campo'] ?? '');
    $valor        = trim($_POST['valor'] ?? '');
    $refs_json    = trim($_POST['referencias_existentes'] ?? '');
    $refs_existentes = [];
    if ($refs_json) {
        $decoded = json_decode($refs_json, true);
        if (is_array($decoded)) $refs_existentes = $decoded;
    }

    $labels = [
        'nome_cientifico_completo' => 'Nome Científico Completo',
        'sinonimos' => 'Sinônimos', 'nome_popular' => 'Nome Popular', 'familia' => 'Família',
        'forma_folha' => 'Forma da Folha', 'filotaxia_folha' => 'Filotaxia da Folha',
        'tipo_folha' => 'Tipo de Folha', 'divisao_folha' => 'Divisão da Folha',
        'paridade_pinnacao' => 'Paridade da Pinação', 'tamanho_folha' => 'Tamanho da Folha',
        'textura_folha' => 'Textura da Folha', 'margem_folha' => 'Margem da Folha',
        'venacao_folha' => 'Venação da Folha', 'cor_flores' => 'Cor das Flores',
        'simetria_floral' => 'Simetria Floral', 'numero_petalas' => 'Número de Pétalas',
        'disposicao_flores' => 'Disposição das Flores', 'aroma' => 'Aroma das Flores',
        'tamanho_flor' => 'Tamanho da Flor', 'tipo_fruto' => 'Tipo de Fruto',
        'tamanho_fruto' => 'Tamanho do Fruto', 'cor_fruto' => 'Cor do Fruto',
        'textura_fruto' => 'Textura do Fruto', 'dispersao_fruto' => 'Dispersão do Fruto',
        'aroma_fruto' => 'Aroma do Fruto', 'tipo_semente' => 'Tipo de Semente',
        'tamanho_semente' => 'Tamanho da Semente', 'cor_semente' => 'Cor da Semente',
        'textura_semente' => 'Textura da Semente',
        'quantidade_sementes' => 'Quantidade de Sementes por Fruto',
        'tipo_caule' => 'Tipo de Caule', 'textura_caule' => 'Textura do Caule',
        'cor_caule' => 'Cor do Caule', 'forma_caule' => 'Forma do Caule',
        'modificacao_caule' => 'Modificação do Caule', 'ramificacao_caule' => 'Ramificação do Caule',
        'possui_espinhos' => 'Possui Espinhos', 'possui_latex' => 'Possui Látex',
        'possui_seiva' => 'Possui Seiva', 'possui_resina' => 'Possui Resina',
    ];

    if (!$especie_id || !isset($labels[$campo])) {
        echo json_encode(['sucesso' => false, 'erro' => 'Parâmetros inválidos.']); exit;
    }

    $stmt = $pdo->prepare("SELECT nome_cientifico FROM especies_administrativo WHERE id = ?");
    $stmt->execute([$especie_id]);
    $nome = $stmt->fetchColumn();
    if (!$nome) { echo json_encode(['sucesso' => false, 'erro' => 'Espécie não encontrada.']); exit; }

    if (!defined('AI_PROVIDER') || !defined('AI_API_KEY') || AI_API_KEY === '') {
        echo json_encode(['sucesso' => false, 'erro' => 'API de IA não configurada.']); exit;
    }

    $label = $labels[$campo];

    // Contexto extra por campo — esclarece à IA distinções que ela tende a confundir
    $contexto_margem = '';
    if ($campo === 'modificacao_caule') {
        $contexto_margem = "\nObservação importante: neste sistema, \"Modificação do caule\" refere-se exclusivamente "
            . "a estruturas derivadas do caule com função adaptada: estolão, cladódio, rizoma, tubérculo, gavinha, bulbo, sapopema. "
            . "Estipe, colmo e tronco são TIPOS de caule, não modificações — portanto NÃO devem ser considerados ao avaliar este campo. "
            . "Se a espécie não apresenta nenhuma das estruturas acima, o valor \"Nenhuma\" é botanicamente correto.";
    } elseif ($campo === 'margem_folha') {
        $tipo_folha    = trim($_POST['tipo_folha']        ?? '');
        $divisao_folha = trim($_POST['divisao_folha']     ?? '');
        $divisoes_foliolo = ['Bipinnada','Tripinnada','Tetrapinnada'];
        $divisoes_foliolo_simples = ['Pinnada','Trifoliada','Digitada'];
        if ($tipo_folha === 'Composta' && in_array($divisao_folha, $divisoes_foliolo)) {
            $contexto_margem = "\nObservação importante: esta espécie possui folha composta {$divisao_folha}. "
                . "A margem informada refere-se ao FOLÍOLULO (a menor subdivisão da folha), não à folha inteira. "
                . "Considere isso ao validar o valor e ao buscar referências.";
        } elseif ($tipo_folha === 'Composta' && in_array($divisao_folha, $divisoes_foliolo_simples)) {
            $contexto_margem = "\nObservação importante: esta espécie possui folha composta {$divisao_folha}. "
                . "A margem informada refere-se ao FOLÍOLO, não à folha inteira. "
                . "Considere isso ao validar o valor e ao buscar referências.";
        }
    }

    // Monta bloco de referências já cadastradas para o prompt
    $bloco_refs = '';
    if (!empty($refs_existentes)) {
        $bloco_refs = "\n\nReferências já cadastradas nesta espécie:\n";
        foreach ($refs_existentes as $r) {
            $idx   = (int)($r['idx']   ?? 0);
            $texto = trim($r['texto']  ?? '');
            if ($idx > 0 && $texto !== '') {
                $bloco_refs .= "[{$idx}] {$texto}\n";
            }
        }
        $bloco_refs .= "\nREGRA OBRIGATÓRIA: se qualquer uma dessas referências já confirma o valor informado, "
            . "você DEVE retornar seu número em \"ref_existente_idx\" e deixar \"referencia\" e \"url\" como strings vazias. "
            . "NÃO sugira uma referência nova se uma das listadas já serve.";
    }

    $prompt = "Você é um especialista em botânica sistemática.\n"
        . "Espécie: {$nome}\nAtributo: \"{$label}\"\nValor informado: \"{$valor}\"\n"
        . $contexto_margem
        . $bloco_refs . "\n\n"
        . "1. Verifique se este valor é botanicamente correto para esta espécie.\n"
        . "2. Se uma referência já cadastrada (acima) confirma este valor, use \"ref_existente_idx\".\n"
        . "3. Caso contrário, forneça UMA referência nova (REFLORA, Lorenzi, Flora do Brasil, artigos científicos).\n\n"
        . "Responda APENAS com JSON válido, sem markdown:\n"
        . "{\"valido\":true,\"observacao\":\"justificativa breve em português\","
        . "\"ref_existente_idx\":null,\"referencia\":\"AUTOR. Título. Local, Ano.\",\"url\":\"https://... ou vazio\"}";

    $provider = strtolower(AI_PROVIDER);
    $api_key  = AI_API_KEY;
    $model    = defined('AI_MODEL') ? AI_MODEL : null;
    $texto    = null;
    $erro_api = null;

    if ($provider === 'claude') {
        $payload = json_encode(['model' => $model ?? 'claude-sonnet-4-6', 'max_tokens' => 512, 'temperature' => 0.2,
            'messages' => [['role' => 'user', 'content' => $prompt]]]);
        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 55, CURLOPT_HTTPHEADER => ['Content-Type: application/json',
            'x-api-key: ' . $api_key, 'anthropic-version: 2023-06-01']]);
        $r = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        if ($code !== 200) { $erro_api = 'Claude HTTP ' . $code; } else { $d = json_decode($r, true); $texto = $d['content'][0]['text'] ?? null; }

    } elseif ($provider === 'openai') {
        $payload = json_encode(['model' => $model ?? 'gpt-4o', 'max_tokens' => 512, 'temperature' => 0.2,
            'messages' => [['role' => 'system', 'content' => 'Responda em JSON válido.'], ['role' => 'user', 'content' => $prompt]]]);
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 55, CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $api_key]]);
        $r = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        if ($code !== 200) { $erro_api = 'OpenAI HTTP ' . $code; } else { $d = json_decode($r, true); $texto = $d['choices'][0]['message']['content'] ?? null; }

    } elseif ($provider === 'gemini') {
        $m = $model ?? 'gemini-1.5-flash';
        $payload = json_encode(['contents' => [['parts' => [['text' => $prompt]]]], 'generationConfig' => ['temperature' => 0.2, 'maxOutputTokens' => 512]]);
        $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/' . $m . ':generateContent?key=' . urlencode($api_key));
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 55, CURLOPT_HTTPHEADER => ['Content-Type: application/json']]);
        $r = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        if ($code !== 200) { $erro_api = 'Gemini HTTP ' . $code; } else { $d = json_decode($r, true); $texto = $d['candidates'][0]['content']['parts'][0]['text'] ?? null; }

    } elseif ($provider === 'deepseek') {
        $payload = json_encode(['model' => $model ?: 'deepseek-chat', 'max_tokens' => 512, 'temperature' => 0.2,
            'messages' => [['role' => 'system', 'content' => 'Responda em JSON válido.'], ['role' => 'user', 'content' => $prompt]]],
            JSON_UNESCAPED_UNICODE);
        $ch = curl_init('https://api.deepseek.com/chat/completions');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 110, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $api_key]]);
        $r = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        if ($code !== 200) { $erro_api = 'DeepSeek HTTP ' . $code; } else { $d = json_decode($r, true); $texto = $d['choices'][0]['message']['content'] ?? null; }

    } else {
        $erro_api = 'Provider não suportado.';
    }

    if ($erro_api || !$texto) { echo json_encode(['sucesso' => false, 'erro' => $erro_api ?? 'Sem resposta da IA.']); exit; }

    $jl = trim(preg_replace(['/^```(?:json)?\s*/i', '/\s*```$/'], '', trim($texto)));
    $ia = json_decode($jl, true);
    if (json_last_error() !== JSON_ERROR_NONE) { echo json_encode(['sucesso' => false, 'erro' => 'JSON inválido da IA.']); exit; }

    $ref_existente_idx = null;
    if (isset($ia['ref_existente_idx']) && is_numeric($ia['ref_existente_idx']) && (int)$ia['ref_existente_idx'] > 0) {
        $ref_existente_idx = (int)$ia['ref_existente_idx'];
    }

    echo json_encode([
        'sucesso'           => true,
        'valido'            => (bool)($ia['valido'] ?? true),
        'observacao'        => $ia['observacao'] ?? '',
        'ref_existente_idx' => $ref_existente_idx,
        'referencia'        => $ia['referencia'] ?? '',
        'url'               => $ia['url'] ?? '',
    ]);
    exit;
}

// ── Session + POST final ───────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['acao'])) {
    require_once __DIR__ . '/../../config/banco_de_dados.php';
    require_once __DIR__ . '/../helpers/gerador_artigo.php';

    $especie_id = (int)($_POST['especie_id'] ?? 0);
    if (!$especie_id) {
        $_SESSION['msg_erro'] = 'Nenhuma espécie selecionada.';
        header('Location: confirmar_caracteristicas.php'); exit;
    }

    $campos = [
        'nome_cientifico_completo','nome_cientifico_completo_ref','sinonimos','sinonimos_ref',
        'nome_popular','nome_popular_ref','familia','familia_ref',
        'forma_folha','forma_folha_ref','filotaxia_folha','filotaxia_folha_ref',
        'tipo_folha','tipo_folha_ref','divisao_folha','divisao_folha_ref',
        'paridade_pinnacao','paridade_pinnacao_ref','tamanho_folha','tamanho_folha_ref',
        'textura_folha','textura_folha_ref','margem_folha','margem_folha_ref',
        'venacao_folha','venacao_folha_ref','cor_flores','cor_flores_ref',
        'simetria_floral','simetria_floral_ref','numero_petalas','numero_petalas_ref',
        'disposicao_flores','disposicao_flores_ref','aroma','aroma_ref',
        'tamanho_flor','tamanho_flor_ref','tipo_fruto','tipo_fruto_ref',
        'tamanho_fruto','tamanho_fruto_ref','cor_fruto','cor_fruto_ref',
        'textura_fruto','textura_fruto_ref','dispersao_fruto','dispersao_fruto_ref',
        'aroma_fruto','aroma_fruto_ref','tipo_semente','tipo_semente_ref',
        'tamanho_semente','tamanho_semente_ref','cor_semente','cor_semente_ref',
        'textura_semente','textura_semente_ref','quantidade_sementes','quantidade_sementes_ref',
        'tipo_caule','tipo_caule_ref','textura_caule','textura_caule_ref',
        'cor_caule','cor_caule_ref','forma_caule','forma_caule_ref',
        'modificacao_caule','modificacao_caule_ref','ramificacao_caule','ramificacao_caule_ref',
        'possui_espinhos','possui_espinhos_ref','possui_latex','possui_latex_ref',
        'possui_seiva','possui_seiva_ref','possui_resina','possui_resina_ref','referencias',
    ];

    $dados = ['especie_id' => $especie_id];
    foreach ($campos as $c) {
        $v = trim($_POST[$c] ?? '');
        $dados[$c] = $v !== '' ? $v : null;
    }

    $enums_validos = [
        'forma_folha'     => ['Lanceolada','Linear','Elíptica','Ovada','Orbicular','Cordiforme','Espatulada','Sagitada','Reniforme','Obovada','Trilobada','Palmada','Pinada','Lobada'],
        'filotaxia_folha' => ['Alterna','Oposta Simples','Oposta Decussada','Verticilada','Dística','Espiralada'],
        'tamanho_folha'   => ['Microfilas (< 2 cm)','Nanofilas (2–7 cm)','Mesofilas (7–20 cm)','Macrófilas (20–50 cm)','Megafilas (> 50 cm)'],
        'possui_espinhos' => ['Sim','Não'], 'possui_latex' => ['Sim','Não'],
        'possui_seiva'    => ['Sim','Não'], 'possui_resina' => ['Sim','Não'],
    ];
    foreach ($enums_validos as $c => $v) {
        if ($dados[$c] !== null && !in_array($dados[$c], $v, true)) $dados[$c] = null;
    }

    try {
        iniciarTransacao();
        $existe = buscarUm("SELECT id FROM especies_caracteristicas WHERE especie_id = :id", [':id' => $especie_id]);
        if ($existe) {
            $upd = $dados; unset($upd['especie_id']);
            atualizar('especies_caracteristicas', $upd, 'especie_id = :especie_id', [':especie_id' => $especie_id]);
        } else {
            inserir('especies_caracteristicas', $dados);
        }
        $autor_id = $_SESSION['usuario_id'] ?? null;
        atualizar('especies_administrativo',
            ['status' => 'descrita', 'data_descrita' => date('Y-m-d H:i:s'), 'autor_descrita_id' => $autor_id],
            'id = :id', [':id' => $especie_id]);
        $pdo->prepare("INSERT INTO historico_alteracoes (especie_id, id_usuario, tabela_afetada, campo_alterado, valor_anterior, valor_novo, tipo_acao) VALUES (?,?,'especies_administrativo','status','dados_internet','descrita','edicao')")
            ->execute([$especie_id, $autor_id]);
        $pdo->prepare("UPDATE artigos SET status='confirmado', data_confirmado=NOW(), atualizado_em=NOW() WHERE especie_id=? AND status='rascunho'")
            ->execute([$especie_id]);
        confirmarTransacao();
        regenerarArtigoEspecie($pdo, $especie_id);
        $_SESSION['msg_sucesso'] = 'Identificação confirmada e dados salvos com sucesso!';
        header('Location: confirmar_caracteristicas.php'); exit;
    } catch (Exception $e) {
        reverterTransacao();
        error_log('Erro confirmar_caracteristicas: ' . $e->getMessage());
        $_SESSION['msg_erro'] = 'Erro ao salvar. Tente novamente.';
        header('Location: confirmar_caracteristicas.php'); exit;
    }
}

// ── Carrega lista de espécies ──────────────────────────────
ob_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';
$especies = [];
$mensagem_erro = '';
try {
    $especies = buscarTodos(
        "SELECT id, nome_cientifico, status FROM especies_administrativo
         WHERE status IN ('sem_dados','dados_internet')
         ORDER BY CASE status WHEN 'dados_internet' THEN 1 WHEN 'sem_dados' THEN 2 END, nome_cientifico"
    );
} catch (Exception $e) { $mensagem_erro = 'Erro ao conectar ao banco.'; }
ob_end_clean();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Confirmar Identificação - Penomato</title>
  <link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f4f0; margin: 0; padding: 24px 16px; color: #1e2e1e; }

    .page-header {
      max-width: 900px; margin: 0 auto 20px;
      background: var(--cor-primaria); color: #fff;
      padding: 18px 24px; border-radius: 10px;
      display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
    }
    .page-header h1 { margin: 0; font-size: 1.25em; }
    .back-link { color: rgba(255,255,255,0.85); text-decoration: none; font-size: 0.9em; }
    .back-link:hover { color: #fff; text-decoration: underline; }

    form { max-width: 900px; margin: 0 auto; }

    /* ── species selector card ── */
    .card {
      background: #fff; padding: 20px 24px; border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08); margin-bottom: 16px;
    }

    #status-especie {
      display: none; margin-top: 8px; padding: 6px 12px; border-radius: 20px;
      font-size: 0.82em; font-weight: 600; width: fit-content;
    }
    #status-especie.tem-dados  { display: block; background: #d1fadf; color: var(--cor-primaria); }
    #status-especie.sem-dados-status { display: block; background: #fff3cd; color: #856404; }

    /* ── progress bar ── */
    .progress-wrap { display: flex; align-items: center; gap: 12px; margin-top: 14px; }
    .progress-bar { flex: 1; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden; }
    .progress-fill { height: 100%; background: var(--cor-primaria); border-radius: 4px; transition: width 0.3s; width: 0%; }
    .progress-label { font-size: 0.82em; color: #666; white-space: nowrap; }

    /* ── reference manager ── */
    .ref-manager { display: none; }
    .section-title {
      background: var(--cor-primaria); color: #fff;
      padding: 9px 14px; margin: 0 0 14px; border-radius: 6px;
      font-size: 0.95em; font-weight: 600;
    }
    .ref-list { display: flex; flex-direction: column; gap: 6px; margin-bottom: 12px; }
    .ref-item {
      display: flex; align-items: baseline; gap: 8px;
      padding: 8px 12px; background: #f8fafc; border-radius: 6px;
      border: 1px solid #e2e8f0;
    }
    .ref-num { font-weight: 700; color: var(--cor-primaria); font-size: 0.85em; white-space: nowrap; flex-shrink: 0; }
    .ref-text { flex: 1; font-size: 0.88em; color: #2d3d2d; word-break: break-word; }
    .btn-del-ref {
      background: none; border: none; color: #aaa; cursor: pointer;
      font-size: 1em; padding: 2px 6px; border-radius: 4px; flex-shrink: 0;
      transition: color 0.15s, background 0.15s;
    }
    .btn-del-ref:hover { color: #dc3545; background: #ffe8e8; }
    .ref-add-row { display: flex; gap: 8px; }
    .ref-add-row input {
      flex: 1; padding: 7px 10px; border: 1px solid #ccc; border-radius: 5px;
      font-size: 0.9em;
    }
    .ref-add-row input:focus { border-color: var(--cor-primaria); outline: none; }
    .btn-add-ref {
      padding: 7px 16px; background: var(--cor-primaria); color: #fff;
      border: none; border-radius: 5px; cursor: pointer; font-size: 0.9em; white-space: nowrap;
    }
    .btn-add-ref:hover { background: var(--cor-primaria-hover); }

    /* ── field sections ── */
    .form-sections { display: none; }

    /* ── field row ── */
    .field-row {
      display: flex; gap: 10px; align-items: flex-start;
      margin-bottom: 12px; padding-bottom: 12px;
      border-bottom: 1px solid #f0f0f0;
    }
    .field-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    .field-main { flex: 3; min-width: 0; }
    .field-refs { flex: 2; min-width: 0; }
    .field-confirm { flex-shrink: 0; padding-top: 22px; }

    label {
      display: block; font-weight: 600; font-size: 0.88em;
      margin-bottom: 4px; color: #2d3d2d;
    }
    label .subtext { font-weight: normal; font-size: 0.9em; color: #666; }

    select, input[type="text"] {
      width: 100%; padding: 7px 10px; border: 1px solid #ccc;
      border-radius: 5px; font-size: 0.9em; transition: border-color 0.15s;
    }
    select:focus, input[type="text"]:focus { border-color: var(--cor-primaria); outline: none; }
    .auto-filled { border-color: var(--cor-primaria) !important; background: #f0faf5; }

    /* ── ref badges area ── */
    .ref-badges-wrap { display: flex; flex-wrap: wrap; align-items: center; gap: 4px; min-height: 32px; }
    .ref-badges { display: flex; flex-wrap: wrap; gap: 4px; }
    .ref-badge {
      display: inline-flex; align-items: center; justify-content: center;
      min-width: 28px; height: 22px; padding: 0 6px;
      background: #e8f5e9; color: var(--cor-primaria);
      border: 1px solid var(--cor-primaria); border-radius: 11px;
      font-size: 0.78em; font-weight: 700; text-decoration: none;
    }
    .ref-badge.link { cursor: pointer; }
    .ref-badge.link:hover { background: var(--cor-primaria); color: #fff; }
    .ref-empty { font-size: 0.78em; color: #aaa; font-style: italic; }

    .btn-buscar-ref {
      background: none; border: 1px solid #ccc; border-radius: 5px;
      padding: 4px 8px; cursor: pointer; font-size: 0.9em; color: #555;
      transition: all 0.15s; margin-left: 4px;
    }
    .btn-buscar-ref:hover { border-color: var(--cor-primaria); color: var(--cor-primaria); background: #f0faf5; }
    .btn-buscar-ref:disabled { opacity: 0.5; cursor: not-allowed; }

    /* ── obs button ── */
    .obs-btn {
      background: none; border: 1px solid #ccc; border-radius: 5px;
      padding: 4px 7px; cursor: pointer; font-size: 0.85em; color: #888;
      transition: all 0.15s; margin-left: 4px; opacity: 0.55;
    }
    .obs-btn:hover { border-color: #f59e0b; color: #b45309; background: #fffbeb; opacity: 1; }
    .obs-btn.has-obs { opacity: 1; border-color: #f59e0b; background: #fffbeb; color: #b45309; }
    .obs-row { padding: 4px 0 10px; }
    .obs-textarea {
      width: 100%; box-sizing: border-box;
      border: 1px solid #d1d5db; border-radius: 4px;
      padding: 8px 10px; font-size: 0.85em; color: #374151;
      resize: vertical; min-height: 54px; font-style: italic;
      background: #fffdf5;
    }
    .obs-textarea:focus { outline: none; border-color: #f59e0b; }

    /* ── confirm button ── */
    .confirm-btn {
      display: inline-flex; align-items: center; justify-content: center;
      width: 34px; height: 34px; border-radius: 6px;
      border: 2px solid #ccc; background: #f8f9fa; color: #ccc;
      font-size: 1em; font-weight: 700; cursor: pointer;
      transition: all 0.15s;
    }
    .confirm-btn:hover { border-color: var(--cor-primaria); color: var(--cor-primaria); }
    .confirm-btn.confirmed { background: var(--cor-primaria); border-color: var(--cor-primaria); color: #fff; }
    .confirm-btn.saving { background: #fff3cd; border-color: #ffc107; color: #856404; }

    /* ── IA result panel ── */
    .ia-result {
      max-width: 900px; margin: -8px auto 12px;
      background: #f8fafc; border: 1px solid #d1e7dd;
      border-left: 4px solid var(--cor-primaria);
      border-radius: 0 0 8px 8px; padding: 14px 16px;
      position: relative;
    }
    .ia-result.warn { border-left-color: #ffc107; }
    .btn-fechar-result {
      position: absolute; top: 8px; right: 10px;
      background: none; border: none; cursor: pointer;
      font-size: 1.1em; color: #aaa;
    }
    .btn-fechar-result:hover { color: #dc3545; }
    .ia-validacao { font-size: 0.88em; margin-bottom: 8px; }
    .ia-validacao.ok { color: #155724; }
    .ia-validacao.warn { color: #856404; }
    .ia-ref-texto { font-size: 0.85em; color: #4a5568; font-style: italic; margin-bottom: 10px; }
    .ia-acoes { display: flex; gap: 8px; }
    .btn-aceitar-ref {
      padding: 5px 14px; background: var(--cor-primaria); color: #fff;
      border: none; border-radius: 20px; cursor: pointer; font-size: 0.82em; font-weight: 600;
    }
    .btn-aceitar-ref:hover { background: var(--cor-primaria-hover); }
    .btn-rejeitar-ref {
      padding: 5px 14px; background: #e2e8f0; color: #2d3748;
      border: none; border-radius: 20px; cursor: pointer; font-size: 0.82em;
    }
    .btn-aceitar-obs {
      padding: 5px 14px; background: #fff3cd; color: #856404;
      border: 1px solid #ffc107; border-radius: 20px; cursor: pointer; font-size: 0.82em; font-weight: 600;
    }
    .btn-aceitar-obs:hover { background: #ffc107; color: #3d2b00; }
    .obs-confirm-area {
      margin-top: 10px; padding-top: 10px; border-top: 1px dashed #ffc107;
    }
    .obs-confirm-area textarea {
      width: 100%; box-sizing: border-box; padding: 8px 10px;
      border: 1px solid #ffc107; border-radius: 6px; font-size: 0.85em;
      color: #374151; background: #fffdf5; resize: vertical; min-height: 60px; font-style: italic;
    }
    .obs-confirm-area textarea:focus { outline: none; border-color: #f59e0b; }
    .obs-confirm-area .obs-confirm-actions { display: flex; gap: 8px; margin-top: 6px; }
    .btn-confirmar-obs {
      padding: 5px 14px; background: #f59e0b; color: #fff;
      border: none; border-radius: 20px; cursor: pointer; font-size: 0.82em; font-weight: 600;
    }
    .btn-confirmar-obs:hover { background: #d97706; }
    .ia-err { color: #856404; font-size: 0.88em; }

    /* ── submit area ── */
    .submit-area { text-align: center; margin-top: 10px; }
    #aviso-confirmacao { font-size: 0.85em; color: #856404; margin-bottom: 10px; display: none; }
    .submit-btn {
      width: 100%; background: var(--cor-primaria); color: #fff;
      padding: 14px; border: none; border-radius: 6px;
      font-size: 1.05em; font-weight: 700; cursor: pointer; transition: background 0.15s;
    }
    .submit-btn:hover:not(:disabled) { background: var(--cor-primaria-hover); }
    .submit-btn:disabled { opacity: 0.45; cursor: not-allowed; }

    /* ── toast ── */
    #toast {
      position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
      background: #2d3748; color: #fff; padding: 10px 20px; border-radius: 20px;
      font-size: 0.88em; opacity: 0; transition: opacity 0.3s; pointer-events: none; z-index: 9999;
    }

    @media (max-width: 600px) {
      .field-row { flex-direction: column; }
      .field-confirm { padding-top: 0; }
    }
  </style>
</head>
<body>

<div class="page-header">
  <h1>🔍 Confirmar Identificação de Espécie</h1>
  <a class="back-link" href="/penomato_mvp/src/Views/entrar_colaborador.php">← Voltar ao painel</a>
</div>

<?php if (!empty($_SESSION['msg_sucesso'])): ?>
  <div style="max-width:900px;margin:0 auto 16px;padding:12px 18px;background:#d1fadf;color:var(--cor-primaria);border-radius:8px;font-weight:600;">
    ✅ <?php echo htmlspecialchars($_SESSION['msg_sucesso']); unset($_SESSION['msg_sucesso']); ?>
  </div>
<?php elseif (!empty($_SESSION['msg_erro'])): ?>
  <div style="max-width:900px;margin:0 auto 16px;padding:12px 18px;background:#ffe8e8;color:#c0392b;border-radius:8px;font-weight:600;">
    ⚠️ <?php echo htmlspecialchars($_SESSION['msg_erro']); unset($_SESSION['msg_erro']); ?>
  </div>
<?php endif; ?>

<form action="confirmar_caracteristicas.php" method="post" id="form-principal">

  <!-- ── SELEÇÃO DE ESPÉCIE ── -->
  <div class="card">
    <label for="especie_id">Espécie (Nome Científico)</label>
    <select id="especie_id" name="especie_id" required>
      <option value="" disabled selected>Selecione uma espécie…</option>
      <?php if (!empty($mensagem_erro)): ?>
        <option value="" disabled style="color:red;"><?php echo htmlspecialchars($mensagem_erro); ?></option>
      <?php elseif (empty($especies)): ?>
        <option value="" disabled>Nenhuma espécie disponível para verificação.</option>
      <?php else:
        $com_dados = array_filter($especies, fn($e) => $e['status'] === 'dados_internet');
        $sem_dados  = array_filter($especies, fn($e) => $e['status'] === 'sem_dados');
        if ($com_dados): ?>
          <optgroup label="⚡ Com dados da internet (verificar)">
            <?php foreach ($com_dados as $e): ?>
              <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['nome_cientifico']); ?></option>
            <?php endforeach; ?>
          </optgroup>
        <?php endif;
        if ($sem_dados): ?>
          <optgroup label="📋 Sem dados (preencher manualmente)">
            <?php foreach ($sem_dados as $e): ?>
              <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['nome_cientifico']); ?></option>
            <?php endforeach; ?>
          </optgroup>
        <?php endif;
      endif; ?>
    </select>
    <div id="status-especie"></div>
    <div class="progress-wrap" id="progress-wrap" style="display:none">
      <div class="progress-bar"><div class="progress-fill" id="progress-fill"></div></div>
      <span class="progress-label" id="progress-label">0 / 38 confirmados</span>
    </div>
  </div>

  <!-- ── GERENCIADOR DE REFERÊNCIAS ── -->
  <div class="card ref-manager" id="ref-manager">
    <div class="section-title">📚 Referências</div>
    <div class="ref-list" id="ref-list"></div>
    <div class="ref-add-row">
      <input type="text" id="ref-new-input" placeholder="Cole a URL ou escreva a citação (ex: LORENZI, H. Árvores Brasileiras. 2002.)">
      <button type="button" id="btn-add-ref" class="btn-add-ref">+ Adicionar</button>
    </div>
    <input type="hidden" id="referencias" name="referencias">
  </div>

  <!-- ══════════════════════════════════════════════════════
       SEÇÕES DE ATRIBUTOS
  ══════════════════════════════════════════════════════ -->
  <div class="form-sections" id="form-sections">

    <!-- ── IDENTIFICAÇÃO BÁSICA ── -->
    <div class="card">
      <div class="section-title">📌 Identificação Básica</div>

      <div class="field-row" data-campo="nome_cientifico_completo">
        <div class="field-main">
          <label for="nome_cientifico_completo">Nome Científico Completo</label>
          <input type="text" id="nome_cientifico_completo" name="nome_cientifico_completo" placeholder="Ex: Mauritia flexuosa L.f.">
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_nome_cientifico_completo"></div>
            <button type="button" class="btn-buscar-ref" data-campo="nome_cientifico_completo" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="nome_cientifico_completo_ref" name="nome_cientifico_completo_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="nome_cientifico_completo" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="sinonimos">
        <div class="field-main">
          <label for="sinonimos">Sinônimos <span class="subtext">(separados por vírgula)</span></label>
          <input type="text" id="sinonimos" name="sinonimos" placeholder="Ex: Mauritia vinifera Mart.">
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_sinonimos"></div>
            <button type="button" class="btn-buscar-ref" data-campo="sinonimos" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="sinonimos_ref" name="sinonimos_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="sinonimos" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="nome_popular">
        <div class="field-main">
          <label for="nome_popular">Nome Popular</label>
          <input type="text" id="nome_popular" name="nome_popular" placeholder="Ex: Buriti">
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_nome_popular"></div>
            <button type="button" class="btn-buscar-ref" data-campo="nome_popular" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="nome_popular_ref" name="nome_popular_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="nome_popular" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="familia">
        <div class="field-main">
          <label for="familia">Família</label>
          <input type="text" id="familia" name="familia" placeholder="Ex: Arecaceae">
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_familia"></div>
            <button type="button" class="btn-buscar-ref" data-campo="familia" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="familia_ref" name="familia_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="familia" title="Confirmar">✓</button>
        </div>
      </div>
    </div>

    <!-- ── FOLHA ── -->
    <div class="card">
      <div class="section-title">🍃 Características da Folha</div>

      <div class="field-row" data-campo="forma_folha">
        <div class="field-main">
          <label for="forma_folha">Forma</label>
          <select id="forma_folha" name="forma_folha">
            <option value="" disabled selected>Selecione…</option>
            <option>Lanceolada</option><option>Linear</option><option>Elíptica</option>
            <option>Ovada</option><option>Orbicular</option><option>Cordiforme</option>
            <option>Espatulada</option><option>Sagitada</option><option>Reniforme</option>
            <option>Obovada</option><option>Trilobada</option><option>Palmada</option><option>Pinada</option><option>Lobada</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_forma_folha"></div>
            <button type="button" class="btn-buscar-ref" data-campo="forma_folha" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="forma_folha_ref" name="forma_folha_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="forma_folha" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="filotaxia_folha">
        <div class="field-main">
          <label for="filotaxia_folha">Filotaxia</label>
          <select id="filotaxia_folha" name="filotaxia_folha">
            <option value="" disabled selected>Selecione…</option>
            <option>Alterna</option><option>Oposta Simples</option><option>Oposta Decussada</option>
            <option>Verticilada</option><option>Dística</option><option>Espiralada</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_filotaxia_folha"></div>
            <button type="button" class="btn-buscar-ref" data-campo="filotaxia_folha" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="filotaxia_folha_ref" name="filotaxia_folha_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="filotaxia_folha" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="tipo_folha">
        <div class="field-main">
          <label for="tipo_folha">Tipo</label>
          <select id="tipo_folha" name="tipo_folha">
            <option value="" disabled selected>Selecione…</option>
            <option>Simples</option>
            <option>Composta</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_tipo_folha"></div>
            <button type="button" class="btn-buscar-ref" data-campo="tipo_folha" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="tipo_folha_ref" name="tipo_folha_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="tipo_folha" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="divisao_folha" id="row-divisao-folha" style="display:none">
        <div class="field-main">
          <label for="divisao_folha">Divisão</label>
          <select id="divisao_folha" name="divisao_folha">
            <option value="" disabled selected>Selecione…</option>
            <option>Trifoliada</option>
            <option>Digitada</option>
            <option>Pinnada</option>
            <option>Bipinnada</option>
            <option>Tripinnada</option>
            <option>Tetrapinnada</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_divisao_folha"></div>
            <button type="button" class="btn-buscar-ref" data-campo="divisao_folha" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="divisao_folha_ref" name="divisao_folha_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="divisao_folha" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="paridade_pinnacao" id="row-paridade-pinnacao" style="display:none">
        <div class="field-main">
          <label for="paridade_pinnacao">Paridade</label>
          <select id="paridade_pinnacao" name="paridade_pinnacao">
            <option value="" disabled selected>Selecione…</option>
            <option>Paripinnada</option>
            <option>Imparipinnada</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_paridade_pinnacao"></div>
            <button type="button" class="btn-buscar-ref" data-campo="paridade_pinnacao" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="paridade_pinnacao_ref" name="paridade_pinnacao_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="paridade_pinnacao" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="tamanho_folha">
        <div class="field-main">
          <label for="tamanho_folha">Tamanho</label>
          <select id="tamanho_folha" name="tamanho_folha">
            <option value="" disabled selected>Selecione…</option>
            <option value="Microfilas (< 2 cm)">Microfilas (&lt; 2 cm)</option>
            <option value="Nanofilas (2–7 cm)">Nanofilas (2–7 cm)</option>
            <option value="Mesofilas (7–20 cm)">Mesofilas (7–20 cm)</option>
            <option value="Macrófilas (20–50 cm)">Macrófilas (20–50 cm)</option>
            <option value="Megafilas (> 50 cm)">Megafilas (&gt; 50 cm)</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_tamanho_folha"></div>
            <button type="button" class="btn-buscar-ref" data-campo="tamanho_folha" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="tamanho_folha_ref" name="tamanho_folha_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="tamanho_folha" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="textura_folha">
        <div class="field-main">
          <label for="textura_folha">Textura</label>
          <select id="textura_folha" name="textura_folha">
            <option value="" disabled selected>Selecione…</option>
            <option>Cartácea</option><option>Coriácea</option><option>Glabra</option>
            <option>Membranácea</option><option>Pilosa</option><option>Pubescente</option>
            <option>Rugosa</option><option>Suculenta</option><option>Tomentosa</option><option>Cerosa</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_textura_folha"></div>
            <button type="button" class="btn-buscar-ref" data-campo="textura_folha" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="textura_folha_ref" name="textura_folha_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="textura_folha" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="margem_folha">
        <div class="field-main">
          <label for="margem_folha">Margem</label>
          <select id="margem_folha" name="margem_folha">
            <option value="" disabled selected>Selecione…</option>
            <option>Crenada</option><option>Dentada</option><option>Inteira</option>
            <option>Lobada</option><option>Ondulada</option><option>Serreada</option>
            <option>Serrilhada</option><option>Partida</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_margem_folha"></div>
            <button type="button" class="btn-buscar-ref" data-campo="margem_folha" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="margem_folha_ref" name="margem_folha_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="margem_folha" title="Confirmar">✓</button>
        </div>
      </div>

      <div id="obs-margem-composta" style="display:none; margin: -4px 0 10px; padding: 8px 12px; background: #fffbeb; border-left: 3px solid #f59e0b; border-radius: 0 6px 6px 0; font-size: .82rem; color: #78350f;">
        <strong>Observação:</strong> <span id="obs-margem-texto"></span>
      </div>

      <div class="field-row" data-campo="venacao_folha">
        <div class="field-main">
          <label for="venacao_folha">Venação</label>
          <select id="venacao_folha" name="venacao_folha">
            <option value="" disabled selected>Selecione…</option>
            <option>Curvinérvea</option><option>Dicotômica</option><option>Paralela</option>
            <option>Peninérvea</option><option>Reticulada palmada</option><option>Reticulada pinada</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_venacao_folha"></div>
            <button type="button" class="btn-buscar-ref" data-campo="venacao_folha" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="venacao_folha_ref" name="venacao_folha_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="venacao_folha" title="Confirmar">✓</button>
        </div>
      </div>
    </div>

    <!-- ── FLORES ── -->
    <div class="card">
      <div class="section-title">🌸 Características das Flores</div>

      <div class="field-row" data-campo="cor_flores">
        <div class="field-main">
          <label for="cor_flores">Cor</label>
          <select id="cor_flores" name="cor_flores">
            <option value="" disabled selected>Selecione…</option>
            <option>Alaranjada</option><option>Amarela</option><option>Avermelhada</option>
            <option>Azul</option><option>Branca</option><option>Esverdeada</option>
            <option>Lilás</option><option>Púrpura</option><option>Rósea</option>
            <option>Roxa</option><option>Vermelha</option><option>Vinácea</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_cor_flores"></div>
            <button type="button" class="btn-buscar-ref" data-campo="cor_flores" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="cor_flores_ref" name="cor_flores_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="cor_flores" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="simetria_floral">
        <div class="field-main">
          <label for="simetria_floral">Simetria Floral</label>
          <select id="simetria_floral" name="simetria_floral">
            <option value="" disabled selected>Selecione…</option>
            <option value="Actinomorfa">Actinomorfa (simetria radial)</option>
            <option value="Zigomorfa">Zigomorfa (simetria bilateral)</option>
            <option value="Assimétrica">Assimétrica (sem simetria)</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_simetria_floral"></div>
            <button type="button" class="btn-buscar-ref" data-campo="simetria_floral" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="simetria_floral_ref" name="simetria_floral_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="simetria_floral" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="numero_petalas">
        <div class="field-main">
          <label for="numero_petalas">Número de Pétalas</label>
          <select id="numero_petalas" name="numero_petalas">
            <option value="" disabled selected>Selecione…</option>
            <option>3 pétalas</option><option>4 pétalas</option><option>5 pétalas</option>
            <option>6 pétalas</option><option>Muitas pétalas</option><option>Ausentes</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_numero_petalas"></div>
            <button type="button" class="btn-buscar-ref" data-campo="numero_petalas" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="numero_petalas_ref" name="numero_petalas_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="numero_petalas" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="disposicao_flores">
        <div class="field-main">
          <label for="disposicao_flores">Disposição</label>
          <select id="disposicao_flores" name="disposicao_flores">
            <option value="" disabled selected>Selecione…</option>
            <option>Solitária</option><option>Capítulo</option><option>Cacho</option>
            <option>Corimbo</option><option>Espádice</option><option>Espiga</option>
            <option>Panícula</option><option>Umbela</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_disposicao_flores"></div>
            <button type="button" class="btn-buscar-ref" data-campo="disposicao_flores" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="disposicao_flores_ref" name="disposicao_flores_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="disposicao_flores" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="aroma">
        <div class="field-main">
          <label for="aroma">Aroma</label>
          <select id="aroma" name="aroma">
            <option value="" disabled selected>Selecione…</option>
            <option>Ausente</option><option>Suave</option><option>Forte</option>
            <option>Desagradável</option><option>Adocicada</option><option>Cítrica</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_aroma"></div>
            <button type="button" class="btn-buscar-ref" data-campo="aroma" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="aroma_ref" name="aroma_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="aroma" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="tamanho_flor">
        <div class="field-main">
          <label for="tamanho_flor">Tamanho da Flor</label>
          <select id="tamanho_flor" name="tamanho_flor">
            <option value="" disabled selected>Selecione…</option>
            <option>Muito pequena</option><option>Pequena</option><option>Média</option>
            <option>Grande</option><option>Muito grande</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_tamanho_flor"></div>
            <button type="button" class="btn-buscar-ref" data-campo="tamanho_flor" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="tamanho_flor_ref" name="tamanho_flor_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="tamanho_flor" title="Confirmar">✓</button>
        </div>
      </div>
    </div>

    <!-- ── FRUTOS ── -->
    <div class="card">
      <div class="section-title">🍎 Características dos Frutos</div>

      <div class="field-row" data-campo="tipo_fruto">
        <div class="field-main">
          <label for="tipo_fruto">Tipo</label>
          <select id="tipo_fruto" name="tipo_fruto">
            <option value="" disabled selected>Selecione…</option>
            <option>Aquênio</option><option>Baga</option><option>Cápsula</option>
            <option>Drupa</option><option>Folículo</option><option>Legume</option>
            <option>Pixídio</option><option>Sâmara</option><option>Síliqua</option>
            <option>Cariopse</option><option>Hespéridio</option><option>Pepo</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_tipo_fruto"></div>
            <button type="button" class="btn-buscar-ref" data-campo="tipo_fruto" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="tipo_fruto_ref" name="tipo_fruto_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="tipo_fruto" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="tamanho_fruto">
        <div class="field-main">
          <label for="tamanho_fruto">Tamanho</label>
          <select id="tamanho_fruto" name="tamanho_fruto">
            <option value="" disabled selected>Selecione…</option>
            <option>Minúsculo</option><option>Pequeno</option><option>Médio</option>
            <option>Grande</option><option>Muito grande</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_tamanho_fruto"></div>
            <button type="button" class="btn-buscar-ref" data-campo="tamanho_fruto" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="tamanho_fruto_ref" name="tamanho_fruto_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="tamanho_fruto" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="cor_fruto">
        <div class="field-main">
          <label for="cor_fruto">Cor</label>
          <select id="cor_fruto" name="cor_fruto">
            <option value="" disabled selected>Selecione…</option>
            <option>Alaranjado</option><option>Amarelo</option><option>Avermelhado</option>
            <option>Branco</option><option>Esverdeado</option><option>Marrom</option>
            <option>Preto</option><option>Roxo</option><option>Verde</option><option>Vináceo</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_cor_fruto"></div>
            <button type="button" class="btn-buscar-ref" data-campo="cor_fruto" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="cor_fruto_ref" name="cor_fruto_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="cor_fruto" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="textura_fruto">
        <div class="field-main">
          <label for="textura_fruto">Textura</label>
          <select id="textura_fruto" name="textura_fruto">
            <option value="" disabled selected>Selecione…</option>
            <option>Lisa</option><option>Rugosa</option><option>Coriácea</option>
            <option>Pubescente</option><option>Pilosa</option><option>Espinhosa</option>
            <option>Cerosa</option><option>Tuberculada</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_textura_fruto"></div>
            <button type="button" class="btn-buscar-ref" data-campo="textura_fruto" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="textura_fruto_ref" name="textura_fruto_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="textura_fruto" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="dispersao_fruto">
        <div class="field-main">
          <label for="dispersao_fruto">Dispersão</label>
          <select id="dispersao_fruto" name="dispersao_fruto">
            <option value="" disabled selected>Selecione…</option>
            <option>Anemocórica</option><option>Autocórica</option><option>Hidrocórica</option>
            <option>Zoocórica</option><option>Mirmecocórica</option><option>Ornitocórica</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_dispersao_fruto"></div>
            <button type="button" class="btn-buscar-ref" data-campo="dispersao_fruto" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="dispersao_fruto_ref" name="dispersao_fruto_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="dispersao_fruto" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="aroma_fruto">
        <div class="field-main">
          <label for="aroma_fruto">Aroma</label>
          <select id="aroma_fruto" name="aroma_fruto">
            <option value="" disabled selected>Selecione…</option>
            <option>Ausente</option><option>Suave</option><option>Forte</option>
            <option>Adocicado</option><option>Cítrico</option><option>Desagradável</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_aroma_fruto"></div>
            <button type="button" class="btn-buscar-ref" data-campo="aroma_fruto" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="aroma_fruto_ref" name="aroma_fruto_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="aroma_fruto" title="Confirmar">✓</button>
        </div>
      </div>
    </div>

    <!-- ── SEMENTES ── -->
    <div class="card">
      <div class="section-title">🌱 Características das Sementes</div>

      <div class="field-row" data-campo="tipo_semente">
        <div class="field-main">
          <label for="tipo_semente">Tipo</label>
          <select id="tipo_semente" name="tipo_semente">
            <option value="" disabled selected>Selecione…</option>
            <option>Alada</option><option>Carnosa</option><option>Dura</option>
            <option>Oleaginosa</option><option>Plumosa</option><option>Ruminada</option><option>Arilada</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_tipo_semente"></div>
            <button type="button" class="btn-buscar-ref" data-campo="tipo_semente" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="tipo_semente_ref" name="tipo_semente_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="tipo_semente" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="tamanho_semente">
        <div class="field-main">
          <label for="tamanho_semente">Tamanho</label>
          <select id="tamanho_semente" name="tamanho_semente">
            <option value="" disabled selected>Selecione…</option>
            <option>Minúscula</option><option>Muito pequena</option><option>Pequena</option>
            <option>Média</option><option>Grande</option><option>Muito grande</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_tamanho_semente"></div>
            <button type="button" class="btn-buscar-ref" data-campo="tamanho_semente" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="tamanho_semente_ref" name="tamanho_semente_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="tamanho_semente" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="cor_semente">
        <div class="field-main">
          <label for="cor_semente">Cor</label>
          <select id="cor_semente" name="cor_semente">
            <option value="" disabled selected>Selecione…</option>
            <option>Amarela</option><option>Branca</option><option>Castanha</option>
            <option>Cinza</option><option>Marrom</option><option>Preta</option>
            <option>Vermelha</option><option>Alaranjada</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_cor_semente"></div>
            <button type="button" class="btn-buscar-ref" data-campo="cor_semente" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="cor_semente_ref" name="cor_semente_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="cor_semente" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="textura_semente">
        <div class="field-main">
          <label for="textura_semente">Textura</label>
          <select id="textura_semente" name="textura_semente">
            <option value="" disabled selected>Selecione…</option>
            <option>Lisa</option><option>Rugosa</option><option>Estriada</option>
            <option>Pontuada</option><option>Foveolada</option><option>Reticulada</option><option>Tuberculada</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_textura_semente"></div>
            <button type="button" class="btn-buscar-ref" data-campo="textura_semente" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="textura_semente_ref" name="textura_semente_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="textura_semente" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="quantidade_sementes">
        <div class="field-main">
          <label for="quantidade_sementes">Quantidade por Fruto</label>
          <select id="quantidade_sementes" name="quantidade_sementes">
            <option value="" disabled selected>Selecione…</option>
            <option>1</option><option>2–3</option><option>4–10</option>
            <option>11–50</option><option value="> 50">&gt; 50</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_quantidade_sementes"></div>
            <button type="button" class="btn-buscar-ref" data-campo="quantidade_sementes" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="quantidade_sementes_ref" name="quantidade_sementes_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="quantidade_sementes" title="Confirmar">✓</button>
        </div>
      </div>
    </div>

    <!-- ── CAULE ── -->
    <div class="card">
      <div class="section-title">🌿 Características do Caule</div>

      <div class="field-row" data-campo="tipo_caule">
        <div class="field-main">
          <label for="tipo_caule">Tipo</label>
          <select id="tipo_caule" name="tipo_caule">
            <option value="" disabled selected>Selecione…</option>
            <option>Tronco</option><option>Estipe</option><option>Colmo</option>
            <option>Liana</option><option>Haste</option><option>Escapo</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_tipo_caule"></div>
            <button type="button" class="btn-buscar-ref" data-campo="tipo_caule" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="tipo_caule_ref" name="tipo_caule_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="tipo_caule" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="textura_caule">
        <div class="field-main">
          <label for="textura_caule">Textura</label>
          <select id="textura_caule" name="textura_caule">
            <option value="" disabled selected>Selecione…</option>
            <option>Lisa</option><option>Rugosa</option><option>Fissurada</option>
            <option>Sulcada</option><option>Estriada</option><option>Escamosa</option>
            <option>Suberosa</option><option>Aculeada</option><option>Cerosa</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_textura_caule"></div>
            <button type="button" class="btn-buscar-ref" data-campo="textura_caule" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="textura_caule_ref" name="textura_caule_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="textura_caule" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="cor_caule">
        <div class="field-main">
          <label for="cor_caule">Cor</label>
          <select id="cor_caule" name="cor_caule">
            <option value="" disabled selected>Selecione…</option>
            <option>Acinzentado</option><option>Alaranjado</option><option>Avermelhado</option>
            <option>Esbranquiçado</option><option>Esverdeado</option><option>Marrom</option><option>Pardacento</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_cor_caule"></div>
            <button type="button" class="btn-buscar-ref" data-campo="cor_caule" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="cor_caule_ref" name="cor_caule_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="cor_caule" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="forma_caule">
        <div class="field-main">
          <label for="forma_caule">Forma</label>
          <select id="forma_caule" name="forma_caule">
            <option value="" disabled selected>Selecione…</option>
            <option>Cilíndrico</option><option>Quadrangular</option>
            <option>Triangular</option><option>Achatado</option><option>Alado</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_forma_caule"></div>
            <button type="button" class="btn-buscar-ref" data-campo="forma_caule" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="forma_caule_ref" name="forma_caule_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="forma_caule" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="modificacao_caule">
        <div class="field-main">
          <label for="modificacao_caule">Modificação</label>
          <select id="modificacao_caule" name="modificacao_caule">
            <option value="" disabled selected>Selecione…</option>
            <option>Nenhuma</option>
            <option>Cladódio</option><option>Estolão</option><option>Gavinha</option>
            <option>Rizoma</option><option>Tubérculo</option><option>Bulbo</option><option>Sapopema</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_modificacao_caule"></div>
            <button type="button" class="btn-buscar-ref" data-campo="modificacao_caule" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="modificacao_caule_ref" name="modificacao_caule_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="modificacao_caule" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="ramificacao_caule">
        <div class="field-main">
          <label for="ramificacao_caule">Ramificação</label>
          <select id="ramificacao_caule" name="ramificacao_caule">
            <option value="" disabled selected>Selecione…</option>
            <option>Monopodial</option><option>Simpodial</option>
            <option>Dicotômica</option><option>Pseudodicotômica</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_ramificacao_caule"></div>
            <button type="button" class="btn-buscar-ref" data-campo="ramificacao_caule" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="ramificacao_caule_ref" name="ramificacao_caule_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="ramificacao_caule" title="Confirmar">✓</button>
        </div>
      </div>
    </div>

    <!-- ── OUTRAS ── -->
    <div class="card">
      <div class="section-title">⚡ Outras Características</div>

      <div class="field-row" data-campo="possui_espinhos">
        <div class="field-main">
          <label for="possui_espinhos">Possui Espinhos?</label>
          <select id="possui_espinhos" name="possui_espinhos">
            <option value="">Selecione…</option>
            <option>Sim</option><option>Não</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_possui_espinhos"></div>
            <button type="button" class="btn-buscar-ref" data-campo="possui_espinhos" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="possui_espinhos_ref" name="possui_espinhos_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="possui_espinhos" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="possui_latex">
        <div class="field-main">
          <label for="possui_latex">Possui Látex?</label>
          <select id="possui_latex" name="possui_latex">
            <option value="">Selecione…</option>
            <option>Sim</option><option>Não</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_possui_latex"></div>
            <button type="button" class="btn-buscar-ref" data-campo="possui_latex" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="possui_latex_ref" name="possui_latex_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="possui_latex" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="possui_seiva">
        <div class="field-main">
          <label for="possui_seiva">Possui Seiva?</label>
          <select id="possui_seiva" name="possui_seiva">
            <option value="">Selecione…</option>
            <option>Sim</option><option>Não</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_possui_seiva"></div>
            <button type="button" class="btn-buscar-ref" data-campo="possui_seiva" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="possui_seiva_ref" name="possui_seiva_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="possui_seiva" title="Confirmar">✓</button>
        </div>
      </div>

      <div class="field-row" data-campo="possui_resina">
        <div class="field-main">
          <label for="possui_resina">Possui Resina?</label>
          <select id="possui_resina" name="possui_resina">
            <option value="">Selecione…</option>
            <option>Sim</option><option>Não</option>
          </select>
        </div>
        <div class="field-refs">
          <label>Refs</label>
          <div class="ref-badges-wrap">
            <div class="ref-badges" id="badges_possui_resina"></div>
            <button type="button" class="btn-buscar-ref" data-campo="possui_resina" title="Buscar referência via IA">🔍</button>
          </div>
          <input type="hidden" id="possui_resina_ref" name="possui_resina_ref">
        </div>
        <div class="field-confirm">
          <button type="button" class="confirm-btn" data-campo="possui_resina" title="Confirmar">✓</button>
        </div>
      </div>
    </div>

    <!-- ── SUBMIT ── -->
    <div class="card submit-area">
      <div id="aviso-confirmacao">⚠️ Confirme todos os campos (✓) para habilitar o envio.</div>
      <button type="submit" id="btn-confirmar" class="submit-btn" disabled>
        ✅ Confirmar Identificação
      </button>
    </div>

  </div><!-- /form-sections -->
</form>

<div id="toast"></div>

<script>
// ============================================================
// ESTADO GLOBAL
// ============================================================
var _refs      = [];   // array 0-indexed de textos de referência
var _especieId = 0;
var _obs       = {};   // mapa campo → texto de observação
var _saveRefTimer = null;

// ============================================================
// REFERÊNCIAS — gerenciamento
// ============================================================
function getRefsText() { return _refs.join('\n'); }

function buildRefManager(refsText) {
    _refs = refsText ? refsText.split('\n').map(r => r.trim()).filter(r => r) : [];
    renderRefList();
}

function renderRefList() {
    var list = document.getElementById('ref-list');
    list.innerHTML = '';
    if (_refs.length === 0) {
        list.innerHTML = '<p style="color:#aaa;font-size:0.85em;margin:0 0 8px">Nenhuma referência ainda. Adicione abaixo ou use o botão 🔍 em cada campo.</p>';
    } else {
        _refs.forEach(function(r, i) {
            var div = document.createElement('div');
            div.className = 'ref-item';
            div.dataset.idx = i;
            var isUrl = /^https?:\/\//.test(r);
            var textoHtml = isUrl
                ? '<a href="' + escAttr(r) + '" target="_blank" class="ref-text" style="color:var(--cor-primaria)">' + escHtml(r) + '</a>'
                : '<span class="ref-text">' + escHtml(r) + '</span>';
            div.innerHTML = '<span class="ref-num">[' + (i + 1) + ']</span>'
                + textoHtml
                + '<button type="button" class="btn-del-ref" data-idx="' + i + '" title="Excluir referência">✕</button>';
            list.appendChild(div);
        });
    }
    document.getElementById('referencias').value = getRefsText();
    updateAllBadges();
}

function addRef(text) {
    if (!text.trim()) return;
    var norm = text.trim();
    var existing = _refs.findIndex(function(r) { return r.trim() === norm; });
    if (existing >= 0) { showToast('Referência já cadastrada como [' + (existing + 1) + '].'); return; }
    _refs.push(norm);
    renderRefList();
    autoSaveRefs();
}

function deleteRef(idx) {
    _refs.splice(idx, 1);
    // Atualizar todos os inputs _ref: remover idx+1, decrementar maiores
    document.querySelectorAll('input[type=hidden][id$="_ref"]').forEach(function(inp) {
        if (!inp.id || inp.id === 'referencias') return;
        var nums = inp.value.split(',').map(function(n) { return parseInt(n.trim()); }).filter(function(n) { return !isNaN(n) && n > 0; });
        var updated = nums.filter(function(n) { return n !== idx + 1; }).map(function(n) { return n > idx + 1 ? n - 1 : n; });
        inp.value = updated.join(',');
    });
    renderRefList();
    autoSaveRefs();
}

function autoSaveRefs() {
    clearTimeout(_saveRefTimer);
    _saveRefTimer = setTimeout(function() {
        if (!_especieId) return;
        fetch('confirmar_caracteristicas.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ acao: 'salvar_referencias', especie_id: _especieId, referencias: getRefsText() })
        });
    }, 800);
}

// ============================================================
// BADGES
// ============================================================
function buildBadges(campo, refValue) {
    var container = document.getElementById('badges_' + campo);
    if (!container) return;
    container.innerHTML = '';
    if (!refValue) return;
    var nums = refValue.split(',').map(function(n) { return parseInt(n.trim()); }).filter(function(n) { return !isNaN(n) && n > 0; });
    if (nums.length === 0) return;
    nums.forEach(function(n) {
        var ref = _refs[n - 1] || '';
        var isUrl = /^https?:\/\//.test(ref);
        var badge;
        if (isUrl) {
            badge = document.createElement('a');
            badge.href = ref;
            badge.target = '_blank';
            badge.title = ref;
            badge.className = 'ref-badge link';
        } else {
            badge = document.createElement('span');
            badge.className = 'ref-badge';
            if (ref) badge.title = ref;
        }
        badge.textContent = '[' + n + ']';
        container.appendChild(badge);
    });
}

function updateAllBadges() {
    document.querySelectorAll('input[type=hidden][id$="_ref"]').forEach(function(inp) {
        if (!inp.id || inp.id === 'referencias') return;
        var campo = inp.id.replace(/_ref$/, '');
        buildBadges(campo, inp.value);
    });
}

// ============================================================
// SALVAR CAMPO (auto-save no ✓)
// ============================================================
function saveField(campo) {
    if (!_especieId) return Promise.resolve({ ok: false });
    var el    = document.getElementById(campo);
    var refEl = document.getElementById(campo + '_ref');
    if (!el) return Promise.resolve({ ok: false });
    return fetch('confirmar_caracteristicas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            acao: 'salvar_campo',
            especie_id: _especieId,
            campo: campo,
            valor: el.value || '',
            ref: refEl ? refEl.value : '',
            referencias: getRefsText()
        })
    }).then(function(r) { return r.json(); });
}

function handleConfirm(btn) {
    var campo = btn.dataset.campo;
    if (btn.classList.contains('confirmed')) {
        btn.classList.remove('confirmed');
        checkProgress();
        return;
    }
    btn.classList.add('saving');
    btn.textContent = '⏳';
    saveField(campo).then(function(res) {
        btn.classList.remove('saving');
        btn.textContent = '✓';
        if (res && res.ok) {
            btn.classList.add('confirmed');
        } else {
            showToast('Erro ao salvar. Tente novamente.');
        }
        checkProgress();
    }).catch(function() {
        btn.classList.remove('saving');
        btn.textContent = '✓';
        checkProgress();
    });
}

// ============================================================
// PROGRESSO
// ============================================================
function checkProgress() {
    var total  = document.querySelectorAll('.confirm-btn').length;
    var done   = document.querySelectorAll('.confirm-btn.confirmed').length;
    var btn    = document.getElementById('btn-confirmar');
    var aviso  = document.getElementById('aviso-confirmacao');
    var fill   = document.getElementById('progress-fill');
    var label  = document.getElementById('progress-label');

    btn.disabled      = (done < total);
    btn.style.opacity = done >= total ? '1' : '0.45';
    aviso.style.display = (done > 0 && done < total) ? 'block' : 'none';

    if (fill)  fill.style.width  = (total > 0 ? Math.round(done / total * 100) : 0) + '%';
    if (label) label.textContent = done + ' / ' + total + ' confirmados';
}

// ============================================================
// BUSCA DE REFERÊNCIA VIA IA
// ============================================================
function searchRefCampo(campo) {
    if (!_especieId) { showToast('Selecione uma espécie primeiro.'); return; }
    var el = document.getElementById(campo);
    var valor = el ? el.value : '';
    if (!valor) { showToast('Selecione um valor antes de buscar referência.'); return; }

    var btn = document.querySelector('.btn-buscar-ref[data-campo="' + campo + '"]');
    if (btn) { btn.disabled = true; btn.textContent = '⏳'; }

    // Remove resultado anterior deste campo
    var prev = document.getElementById('ia_result_' + campo);
    if (prev) prev.remove();

    // Envia referências já cadastradas para a IA verificar antes de sugerir nova
    var refsPayload = _refs.map(function(r, i) { return { idx: i + 1, texto: r }; });

    // Se for margem da folha, envia também o tipo de folha para contextualizar a IA
    var params = {
        acao: 'buscar_ref_campo',
        especie_id: _especieId,
        campo: campo,
        valor: valor,
        referencias_existentes: JSON.stringify(refsPayload)
    };
    if (campo === 'margem_folha') {
        var tipoFolhaEl    = document.getElementById('tipo_folha');
        var divisaoFolhaEl = document.getElementById('divisao_folha');
        if (tipoFolhaEl    && tipoFolhaEl.value)    params.tipo_folha    = tipoFolhaEl.value;
        if (divisaoFolhaEl && divisaoFolhaEl.value) params.divisao_folha = divisaoFolhaEl.value;
    }

    fetch('confirmar_caracteristicas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(params)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (btn) { btn.disabled = false; btn.textContent = '🔍'; }
        showRefResult(campo, res);
    })
    .catch(function() {
        if (btn) { btn.disabled = false; btn.textContent = '🔍'; }
        showToast('Erro de rede ao buscar referência.');
    });
}

function showRefResult(campo, res) {
    var fieldRow = document.querySelector('.field-row[data-campo="' + campo + '"]');
    if (!fieldRow) return;

    var div = document.createElement('div');
    div.id = 'ia_result_' + campo;
    div.className = 'ia-result' + (!res.sucesso || !res.valido ? ' warn' : '');

    if (!res.sucesso) {
        div.innerHTML = '<span class="ia-err">⚠️ ' + escHtml(res.erro) + '</span>'
            + '<button type="button" class="btn-fechar-result" onclick="this.parentElement.remove()">✕</button>';
    } else {
        var icone = res.valido ? '✅' : '⚠️';
        var texto = res.valido ? 'Valor confirmado' : 'Atenção — valor questionado';
        var acoesHtml;
        if (res.ref_existente_idx) {
            acoesHtml = '<div class="ia-ref-texto">Referência [' + res.ref_existente_idx + '] já confirma este valor.</div>'
                + '<div class="ia-acoes">'
                + '<button type="button" class="btn-aceitar-ref" onclick="usarRefExistente(\'' + campo + '\',' + res.ref_existente_idx + ', this)">✔ Usar [' + res.ref_existente_idx + ']</button>'
                + '<button type="button" class="btn-rejeitar-ref" onclick="this.closest(\'.ia-result\').remove()">✖ Ignorar</button>'
                + '</div>';
        } else {
            var btnObs = !res.valido
                ? '<button type="button" class="btn-aceitar-obs" onclick="abrirAceitarComObs(\'' + campo + '\', this)">✏ Aceitar com observação</button>'
                : '';
            acoesHtml = '<div class="ia-ref-texto">' + escHtml(res.referencia) + '</div>'
                + '<div class="ia-acoes">'
                + '<button type="button" class="btn-aceitar-ref" onclick="aceitarRef(\'' + campo + '\', this)">✔ Aceitar referência</button>'
                + btnObs
                + '<button type="button" class="btn-rejeitar-ref" onclick="this.closest(\'.ia-result\').remove()">✖ Rejeitar</button>'
                + '</div>';
        }
        div.innerHTML = '<button type="button" class="btn-fechar-result" onclick="this.parentElement.remove()">✕</button>'
            + '<div class="ia-validacao ' + (res.valido ? 'ok' : 'warn') + '">' + icone + ' <strong>' + texto + '</strong>'
            + (res.observacao ? ' — ' + escHtml(res.observacao) : '') + '</div>'
            + acoesHtml;
        div.dataset.referencia  = res.referencia  || '';
        div.dataset.url         = res.url         || '';
        div.dataset.observacao  = res.observacao  || '';
    }

    fieldRow.after(div);
}

function _autoConfirmarCampo(campo, panel) {
    // Só confirma se o painel não tem classe 'warn' (= IA validou positivamente)
    if (panel.classList.contains('warn')) return;
    var confirmBtn = document.querySelector('.confirm-btn[data-campo="' + campo + '"]');
    if (confirmBtn && !confirmBtn.classList.contains('confirmed')) {
        handleConfirm(confirmBtn);
    }
}

function aceitarRef(campo, btn) {
    var panel = btn.closest('.ia-result');
    var url   = (panel.dataset.url || '').trim();
    var cit   = (panel.dataset.referencia || '').trim();
    var texto = (url && /^https?:\/\//.test(url)) ? url : cit;
    if (!texto) { panel.remove(); return; }

    var existingIdx = _refs.findIndex(function(r) { return r.trim() === texto.trim(); });
    var newIdx;
    if (existingIdx >= 0) {
        newIdx = existingIdx + 1; // reutiliza índice existente
    } else {
        _refs.push(texto);
        newIdx = _refs.length;
        renderRefList();
        autoSaveRefs();
    }

    var refEl = document.getElementById(campo + '_ref');
    if (refEl) {
        var parts = refEl.value
            ? refEl.value.split(',').map(function(n) { return n.trim(); }).filter(function(n) { return n && /^\d+$/.test(n); })
            : [];
        if (parts.indexOf(String(newIdx)) === -1) parts.push(String(newIdx));
        refEl.value = parts.join(',');
        buildBadges(campo, refEl.value);
    }

    _autoConfirmarCampo(campo, panel);
    panel.remove();
    showToast(existingIdx >= 0 ? 'Referência [' + newIdx + '] já existente — reutilizada.' : 'Referência [' + newIdx + '] adicionada.');
}

function usarRefExistente(campo, idx, btn) {
    var panel = btn.closest('.ia-result');
    var refEl = document.getElementById(campo + '_ref');
    if (refEl) {
        var parts = refEl.value
            ? refEl.value.split(',').map(function(n) { return n.trim(); }).filter(function(n) { return n && /^\d+$/.test(n); })
            : [];
        if (parts.indexOf(String(idx)) === -1) parts.push(String(idx));
        refEl.value = parts.join(',');
        buildBadges(campo, refEl.value);
    }
    autoSaveRefs();
    _autoConfirmarCampo(campo, panel);
    panel.remove();
    showToast('Campo vinculado à referência [' + idx + '].');
}

function abrirAceitarComObs(campo, btn) {
    var panel = btn.closest('.ia-result');
    // Evita abrir duas vezes
    if (panel.querySelector('.obs-confirm-area')) return;

    var obsTexto = (panel.dataset.observacao || '').trim();

    var area = document.createElement('div');
    area.className = 'obs-confirm-area';
    area.innerHTML = '<textarea placeholder="Edite ou confirme a observação da IA…">'
        + escHtml(obsTexto) + '</textarea>'
        + '<div class="obs-confirm-actions">'
        + '<button type="button" class="btn-confirmar-obs" onclick="confirmarComObs(\'' + campo + '\', this)">✔ Confirmar observação</button>'
        + '<button type="button" class="btn-rejeitar-ref" onclick="this.closest(\'.obs-confirm-area\').remove()">✖ Cancelar</button>'
        + '</div>';
    panel.appendChild(area);
    area.querySelector('textarea').focus();
}

function confirmarComObs(campo, btn) {
    var panel  = btn.closest('.ia-result');
    var area   = btn.closest('.obs-confirm-area');
    var texto  = area.querySelector('textarea').value.trim();

    // 1. Salva observação
    if (texto) {
        _obs[campo] = texto;
        var obsBtn = document.querySelector('.obs-btn[data-campo="' + campo + '"]');
        if (obsBtn) obsBtn.classList.add('has-obs');
        fetch('confirmar_caracteristicas.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ acao: 'salvar_obs', especie_id: _especieId, campo: campo, observacao: texto })
        });
        // Atualiza textarea de obs se já estiver visível
        var ta = document.querySelector('#obs_row_' + campo + ' textarea');
        if (ta) ta.value = texto;
    }

    // 2. Adiciona a referência (sem duplicar)
    var url  = (panel.dataset.url || '').trim();
    var cit  = (panel.dataset.referencia || '').trim();
    var ref  = (url && /^https?:\/\//.test(url)) ? url : cit;
    if (ref) {
        var existingRefIdx = _refs.findIndex(function(r) { return r.trim() === ref.trim(); });
        var newIdx;
        if (existingRefIdx >= 0) {
            newIdx = existingRefIdx + 1;
        } else {
            _refs.push(ref);
            newIdx = _refs.length;
        }
        var refEl = document.getElementById(campo + '_ref');
        if (refEl) {
            var parts = refEl.value
                ? refEl.value.split(',').map(function(n) { return n.trim(); }).filter(function(n) { return n && /^\d+$/.test(n); })
                : [];
            if (parts.indexOf(String(newIdx)) === -1) parts.push(String(newIdx));
            refEl.value = parts.join(',');
            buildBadges(campo, refEl.value);
        }
        renderRefList();
        autoSaveRefs();
    }

    // 3. Confirma o campo
    var confirmBtn = document.querySelector('.confirm-btn[data-campo="' + campo + '"]');
    if (confirmBtn && !confirmBtn.classList.contains('confirmed')) {
        handleConfirm(confirmBtn);
    }

    panel.remove();
    showToast('Observação salva e campo confirmado.');
}

// ============================================================
// PREENCHER CAMPOS
// ============================================================
function preencherCampo(id, valor) {
    var el = document.getElementById(id);
    if (!el || valor === null || valor === undefined || valor === '') return;
    var v = String(valor).trim();
    if (!v) return;
    if (el.tagName === 'SELECT') {
        var opts = Array.from(el.options);
        var match = opts.find(function(o) { return o.value === v; })
            || opts.find(function(o) { return o.text.trim() === v; })
            || opts.find(function(o) { return o.value.startsWith(v) || v.startsWith(o.value); })
            || opts.find(function(o) { return o.text.trim().startsWith(v) || v.startsWith(o.text.trim()); });
        if (match) { el.value = match.value; el.classList.add('auto-filled'); }
    } else {
        el.value = v;
        el.classList.add('auto-filled');
    }
}

// ============================================================
// CASCATA: tipo_folha → divisao_folha → paridade_pinnacao
// ============================================================
(function () {
    var divPinadas   = ['Pinnada','Bipinnada','Tripinnada'];
    var tipoSel      = document.getElementById('tipo_folha');
    var divisaoSel   = document.getElementById('divisao_folha');
    var paridadeSel  = document.getElementById('paridade_pinnacao');
    var rowDivisao   = document.getElementById('row-divisao-folha');
    var rowParidade  = document.getElementById('row-paridade-pinnacao');

    function atualizarCascata() {
        var tipo    = tipoSel    ? tipoSel.value    : '';
        var divisao = divisaoSel ? divisaoSel.value : '';

        if (tipo === 'Composta') {
            rowDivisao.style.display = '';
        } else {
            rowDivisao.style.display = 'none';
            if (divisaoSel)  divisaoSel.value  = '';
            if (paridadeSel) paridadeSel.value = '';
            rowParidade.style.display = 'none';
        }

        if (divPinadas.indexOf(divisao) !== -1) {
            rowParidade.style.display = '';
        } else {
            rowParidade.style.display = 'none';
            if (paridadeSel) paridadeSel.value = '';
        }
    }

    if (tipoSel)    tipoSel.addEventListener('change', atualizarCascata);
    if (divisaoSel) divisaoSel.addEventListener('change', atualizarCascata);
    atualizarCascata();
})();

// ============================================================
// OBSERVAÇÃO: MARGEM EM FOLHAS COMPOSTAS
// ============================================================
(function () {
    var tipoSel    = document.getElementById('tipo_folha');
    var divisaoSel = document.getElementById('divisao_folha');
    var obs        = document.getElementById('obs-margem-composta');
    var txt        = document.getElementById('obs-margem-texto');

    var msgFoliolo   = 'Em folhas compostas a margem se refere ao <strong>folíolulo</strong> (a menor subdivisão da folha).';
    var msgFoliolo_s = 'Em folhas compostas a margem se refere ao <strong>folíolo</strong>.';
    var divFoliolo   = ['Bipinnada','Tripinnada','Tetrapinnada'];
    var divFolioloS  = ['Pinnada','Trifoliada','Digitada'];

    function atualizar() {
        var tipo    = tipoSel    ? tipoSel.value    : '';
        var divisao = divisaoSel ? divisaoSel.value : '';
        if (tipo !== 'Composta') { obs.style.display = 'none'; return; }
        if (divFoliolo.indexOf(divisao) !== -1) {
            txt.innerHTML = msgFoliolo; obs.style.display = 'block';
        } else if (divFolioloS.indexOf(divisao) !== -1) {
            txt.innerHTML = msgFoliolo_s; obs.style.display = 'block';
        } else {
            obs.style.display = 'none';
        }
    }

    if (tipoSel)    tipoSel.addEventListener('change', atualizar);
    if (divisaoSel) divisaoSel.addEventListener('change', atualizar);
    atualizar();
})();

// ============================================================
// CARREGAR DADOS DA ESPÉCIE
// ============================================================
var _allCampos = [
    'nome_cientifico_completo','sinonimos','nome_popular','familia',
    'forma_folha','filotaxia_folha','tipo_folha','divisao_folha','paridade_pinnacao',
    'tamanho_folha','textura_folha',
    'margem_folha','venacao_folha','cor_flores','simetria_floral','numero_petalas',
    'disposicao_flores','aroma','tamanho_flor','tipo_fruto','tamanho_fruto',
    'cor_fruto','textura_fruto','dispersao_fruto','aroma_fruto','tipo_semente',
    'tamanho_semente','cor_semente','textura_semente','quantidade_sementes',
    'tipo_caule','textura_caule','cor_caule','forma_caule','modificacao_caule',
    'ramificacao_caule','possui_espinhos','possui_latex','possui_seiva','possui_resina'
];

function loadEspecieData(especieId) {
    _especieId = parseInt(especieId);

    // Resetar estado
    document.querySelectorAll('.confirm-btn').forEach(function(b) { b.classList.remove('confirmed'); });
    document.querySelectorAll('input[type=hidden][id$="_ref"]').forEach(function(inp) { inp.value = ''; });
    document.querySelectorAll('.ref-badges').forEach(function(c) { c.innerHTML = ''; });
    document.querySelectorAll('.ia-result').forEach(function(p) { p.remove(); });
    _allCampos.forEach(function(c) {
        var el = document.getElementById(c);
        if (!el) return;
        el.value = '';
        el.classList.remove('auto-filled');
        if (el.tagName === 'SELECT' && el.options[0] && el.options[0].disabled) el.selectedIndex = 0;
    });

    var badge = document.getElementById('status-especie');
    badge.textContent = '🔄 Buscando dados…';
    badge.className = 'tem-dados';

    document.getElementById('ref-manager').style.display = 'block';
    document.getElementById('form-sections').style.display = 'block';
    document.getElementById('progress-wrap').style.display = 'flex';
    buildRefManager('');
    checkProgress();

    fetch('confirmar_caracteristicas.php?acao=dados&especie_id=' + encodeURIComponent(especieId))
        .then(function(r) { return r.json(); })
        .then(function(dados) {
            if (!dados) {
                badge.textContent = '📋 Nenhum dado cadastrado. Preencha e confirme campo a campo.';
                badge.className = 'sem-dados-status';
                return;
            }
            _allCampos.forEach(function(c) { preencherCampo(c, dados[c]); });
            _allCampos.forEach(function(c) {
                var refEl = document.getElementById(c + '_ref');
                if (refEl && dados[c + '_ref']) refEl.value = dados[c + '_ref'];
            });
            buildRefManager(dados.referencias || '');
            _obs = dados._obs || {};
            injetarBotoesObs();
            badge.textContent = '✅ Dados carregados. Verifique cada campo e clique ✓ para confirmar.';
            badge.className = 'tem-dados';
            checkProgress();
        })
        .catch(function() {
            badge.textContent = '⚠️ Erro ao buscar dados.';
            badge.className = 'sem-dados-status';
        });
}

// ============================================================
// OBSERVAÇÕES POR CAMPO
// ============================================================
function injetarBotoesObs() {
    document.querySelectorAll('.field-row[data-campo]').forEach(function(row) {
        var campo = row.dataset.campo;
        if (row.querySelector('.obs-btn')) return; // já injetado
        var confirmDiv = row.querySelector('.field-confirm');
        if (!confirmDiv) return;
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'obs-btn' + (_obs[campo] ? ' has-obs' : '');
        btn.dataset.campo = campo;
        btn.title = 'Observação sobre este atributo';
        btn.textContent = '💬';
        btn.addEventListener('click', function() { toggleObs(campo); });
        confirmDiv.appendChild(btn);
    });
}

function toggleObs(campo) {
    var rowId = 'obs_row_' + campo;
    var existing = document.getElementById(rowId);
    if (existing) {
        existing.style.display = existing.style.display === 'none' ? '' : 'none';
        if (existing.style.display !== 'none') existing.querySelector('textarea').focus();
        return;
    }
    var fieldRow = document.querySelector('.field-row[data-campo="' + campo + '"]');
    if (!fieldRow) return;
    var div = document.createElement('div');
    div.id = rowId;
    div.className = 'obs-row';
    var ta = document.createElement('textarea');
    ta.className = 'obs-textarea';
    ta.dataset.campo = campo;
    ta.placeholder = 'Observação sobre este atributo (aparecerá entre parênteses no artigo)…';
    ta.value = _obs[campo] || '';
    ta.addEventListener('blur', function() { salvarObs(campo, this.value); });
    div.appendChild(ta);
    fieldRow.after(div);
    ta.focus();
}

function salvarObs(campo, texto) {
    texto = texto.trim();
    _obs[campo] = texto;
    var btn = document.querySelector('.obs-btn[data-campo="' + campo + '"]');
    if (btn) btn.classList.toggle('has-obs', !!texto);
    fetch('confirmar_caracteristicas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ acao: 'salvar_obs', especie_id: _especieId, campo: campo, observacao: texto })
    });
}

// ============================================================
// UTILITÁRIOS
// ============================================================
function showToast(msg) {
    var t = document.getElementById('toast');
    t.textContent = msg;
    t.style.opacity = '1';
    clearTimeout(t._timer);
    t._timer = setTimeout(function() { t.style.opacity = '0'; }, 3000);
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escAttr(str) {
    return String(str).replace(/"/g,'&quot;');
}

// ============================================================
// EVENT LISTENERS
// ============================================================
document.getElementById('especie_id').addEventListener('change', function() {
    if (this.value) loadEspecieData(this.value);
});

document.addEventListener('click', function(e) {
    var confBtn = e.target.closest('.confirm-btn');
    if (confBtn) { handleConfirm(confBtn); return; }

    var searchBtn = e.target.closest('.btn-buscar-ref');
    if (searchBtn) { searchRefCampo(searchBtn.dataset.campo); return; }

    var delBtn = e.target.closest('.btn-del-ref');
    if (delBtn) {
        if (confirm('Excluir referência [' + (parseInt(delBtn.dataset.idx) + 1) + ']?\nOs campos que a referenciam serão atualizados.')) {
            deleteRef(parseInt(delBtn.dataset.idx));
        }
        return;
    }
});

document.getElementById('btn-add-ref').addEventListener('click', function() {
    var inp = document.getElementById('ref-new-input');
    if (inp.value.trim()) { addRef(inp.value.trim()); inp.value = ''; }
});

document.getElementById('ref-new-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btn-add-ref').click(); }
});

checkProgress();
</script>
</body>
</html>
