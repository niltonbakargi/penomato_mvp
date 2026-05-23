<?php
// ============================================================
// batch_ia.php — Processamento em lote de todas as espécies
// - sem_dados       → chama IA + salva no BD + regenera artigo
// - dados_internet  → apenas regenera artigo (preserva dados revisados)
// DELETAR após uso.
// ============================================================
@set_time_limit(0);
@ini_set('max_execution_time', 0);
ini_set('output_buffering', 'off');
@ob_implicit_flush(true);

require_once __DIR__ . '/config/banco_de_dados.php';
require_once __DIR__ . '/src/helpers/gerador_artigo.php';

// ── Flush helper ──────────────────────────────────────────
function linha(string $msg): void {
    echo $msg . "\n";
    if (ob_get_level()) ob_flush();
    flush();
}

// ── ENUMs válidos (mesmos de buscar_dados_especie_ai.php) ─
$opcoes_validas = [
    'forma_folha'       => ['Lanceolada','Linear','Elíptica','Ovada','Orbicular','Cordiforme','Espatulada','Sagitada','Reniforme','Obovada','Trilobada','Palmada','Lobada'],
    'filotaxia_folha'   => ['Alterna','Oposta Simples','Oposta Decussada','Verticilada','Dística','Espiralada'],
    'tipo_folha'        => ['Simples','Composta'],
    'divisao_folha'     => ['Trifoliada','Digitada','Pinnada','Bipinnada','Tripinnada','Tetrapinnada'],
    'paridade_pinnacao' => ['Paripinnada','Imparipinnada'],
    'tamanho_folha'     => ['Microfilas (< 2 cm)','Nanofilas (2–7 cm)','Mesofilas (7–20 cm)','Macrófilas (20–50 cm)','Megafilas (> 50 cm)'],
    'textura_folha'     => ['Cartácea','Coriácea','Glabra','Membranácea','Pilosa','Pubescente','Rugosa','Suculenta','Tomentosa','Cerosa'],
    'margem_folha'      => ['Crenada','Dentada','Inteira','Lobada','Ondulada','Serreada','Serrilhada','Partida'],
    'venacao_folha'     => ['Curvinérvea','Dicotômica','Paralela','Peninérvea','Reticulada palmada','Reticulada pinada'],
    'cor_flores'        => ['Alaranjada','Amarela','Avermelhada','Azul','Branca','Esverdeada','Lilás','Púrpura','Rósea','Roxa','Vermelha','Vinácea'],
    'simetria_floral'   => ['Actinomorfa','Zigomorfa','Assimétrica'],
    'numero_petalas'    => ['3 pétalas','4 pétalas','5 pétalas','6 pétalas','Muitas pétalas','Ausentes'],
    'disposicao_flores' => ['Solitária','Capítulo','Cacho','Corimbo','Espádice','Espiga','Panícula','Umbela'],
    'aroma'             => ['Ausente','Suave','Forte','Desagradável','Adocicada','Cítrica'],
    'tamanho_flor'      => ['Muito pequena','Pequena','Média','Grande','Muito grande'],
    'tipo_fruto'        => ['Aquênio','Baga','Cápsula','Drupa','Folículo','Legume','Pixídio','Sâmara','Síliqua','Cariopse','Hespéridio','Pepo'],
    'tamanho_fruto'     => ['Minúsculo','Pequeno','Médio','Grande','Muito grande'],
    'cor_fruto'         => ['Alaranjado','Amarelo','Avermelhado','Branco','Esverdeado','Marrom','Preto','Roxo','Verde','Vináceo'],
    'textura_fruto'     => ['Lisa','Rugosa','Coriácea','Pubescente','Pilosa','Espinhosa','Cerosa','Tuberculada'],
    'dispersao_fruto'   => ['Anemocórica','Autocórica','Hidrocórica','Zoocórica','Mirmecocórica','Ornitocórica'],
    'aroma_fruto'       => ['Ausente','Suave','Forte','Adocicado','Cítrico','Desagradável'],
    'tipo_semente'      => ['Alada','Carnosa','Dura','Oleaginosa','Plumosa','Ruminada','Arilada'],
    'tamanho_semente'   => ['Minúscula','Muito pequena','Pequena','Média','Grande','Muito grande'],
    'cor_semente'       => ['Amarela','Branca','Castanha','Cinza','Marrom','Preta','Vermelha','Alaranjada'],
    'textura_semente'   => ['Lisa','Rugosa','Estriada','Pontuada','Foveolada','Reticulada','Tuberculada'],
    'quantidade_sementes' => ['1','2–3','4–10','11–50','> 50'],
    'tipo_caule'        => ['Tronco','Estipe','Colmo','Liana','Haste','Escapo'],
    'textura_caule'     => ['Lisa','Rugosa','Fissurada','Sulcada','Estriada','Escamosa','Suberosa','Aculeada','Cerosa'],
    'cor_caule'         => ['Acinzentado','Alaranjado','Avermelhado','Esbranquiçado','Esverdeado','Marrom','Pardacento'],
    'forma_caule'       => ['Cilíndrico','Quadrangular','Triangular','Achatado','Alado'],
    'modificacao_caule' => ['Cladódio','Estolão','Gavinha','Rizoma','Tubérculo','Bulbo','Sapopema'],
    'ramificacao_caule' => ['Monopodial','Simpodial','Dicotômica','Pseudodicotômica'],
    'possui_espinhos'   => ['Sim','Não'],
    'possui_latex'      => ['Sim','Não'],
    'possui_seiva'      => ['Sim','Não'],
    'possui_resina'     => ['Sim','Não'],
];

// ── Chamar a IA para um nome científico ──────────────────
function chamarIA(string $nome_cientifico, array $opcoes_validas): array {
    if (!defined('AI_PROVIDER') || !defined('AI_API_KEY') || AI_API_KEY === '') {
        return ['sucesso' => false, 'erro' => 'API de IA não configurada.'];
    }
    $provider = strtolower(AI_PROVIDER);
    $api_key  = AI_API_KEY;
    $model    = defined('AI_MODEL') ? AI_MODEL : null;

    $linhas_opcoes = '';
    foreach ($opcoes_validas as $campo => $opcoes) {
        $linhas_opcoes .= $campo . ': ' . implode(' | ', $opcoes) . "\n";
    }

    $prompt = <<<PROMPT
Você é um especialista em botânica sistemática. Preencha o JSON abaixo com as características botânicas da espécie indicada.

ESPÉCIE-ALVO: {$nome_cientifico}

REGRAS OBRIGATÓRIAS:
1. Responda APENAS com o JSON preenchido, sem texto antes ou depois, sem blocos markdown.
2. Escreva APENAS a string exata da opção escolhida para campos de seleção. NUNCA use valor fora da lista.
3. Campos múltiplos (sinonimos, nome_popular): separados por vírgula e espaço.
4. campo referencias: cada referência separada por \n.
5. Campos _ref: números separados por vírgula (ex: "1,3"). Se sem referência, use "".
6. "divisao_folha" só se "tipo_folha" = "Composta". "paridade_pinnacao" só se divisao for Pinnada/Bipinnada/Tripinnada.
7. Dados incertos: use "Não informado". NUNCA invente dados.
8. especie_id deve conter EXATAMENTE: {$nome_cientifico}

CAMPOS DE SELEÇÃO E SUAS OPÇÕES VÁLIDAS:
{$linhas_opcoes}
ESTRUTURA DO JSON DE SAÍDA:
{
  "especie_id": "{$nome_cientifico}",
  "nome_cientifico_completo": "", "nome_cientifico_completo_ref": "",
  "sinonimos": "", "sinonimos_ref": "",
  "nome_popular": "", "nome_popular_ref": "",
  "familia": "", "familia_ref": "",
  "forma_folha": "", "forma_folha_ref": "",
  "filotaxia_folha": "", "filotaxia_folha_ref": "",
  "tipo_folha": "", "tipo_folha_ref": "",
  "divisao_folha": "", "divisao_folha_ref": "",
  "paridade_pinnacao": "", "paridade_pinnacao_ref": "",
  "tamanho_folha": "", "tamanho_folha_ref": "",
  "textura_folha": "", "textura_folha_ref": "",
  "margem_folha": "", "margem_folha_ref": "",
  "venacao_folha": "", "venacao_folha_ref": "",
  "cor_flores": "", "cor_flores_ref": "",
  "simetria_floral": "", "simetria_floral_ref": "",
  "numero_petalas": "", "numero_petalas_ref": "",
  "disposicao_flores": "", "disposicao_flores_ref": "",
  "aroma": "", "aroma_ref": "",
  "tamanho_flor": "", "tamanho_flor_ref": "",
  "tipo_fruto": "", "tipo_fruto_ref": "",
  "tamanho_fruto": "", "tamanho_fruto_ref": "",
  "cor_fruto": "", "cor_fruto_ref": "",
  "textura_fruto": "", "textura_fruto_ref": "",
  "dispersao_fruto": "", "dispersao_fruto_ref": "",
  "aroma_fruto": "", "aroma_fruto_ref": "",
  "tipo_semente": "", "tipo_semente_ref": "",
  "tamanho_semente": "", "tamanho_semente_ref": "",
  "cor_semente": "", "cor_semente_ref": "",
  "textura_semente": "", "textura_semente_ref": "",
  "quantidade_sementes": "", "quantidade_sementes_ref": "",
  "tipo_caule": "", "tipo_caule_ref": "",
  "textura_caule": "", "textura_caule_ref": "",
  "cor_caule": "", "cor_caule_ref": "",
  "forma_caule": "", "forma_caule_ref": "",
  "modificacao_caule": "", "modificacao_caule_ref": "",
  "ramificacao_caule": "", "ramificacao_caule_ref": "",
  "possui_espinhos": "", "possui_espinhos_ref": "",
  "possui_latex": "", "possui_latex_ref": "",
  "possui_seiva": "", "possui_seiva_ref": "",
  "possui_resina": "", "possui_resina_ref": "",
  "referencias": ""
}
PROMPT;

    $resposta_texto = null;
    $erro_api       = null;

    if ($provider === 'claude') {
        $modelo_usado = $model ?? 'claude-opus-4-6';
        $payload = json_encode(['model' => $modelo_usado, 'max_tokens' => 4096, 'temperature' => 0.2,
            'messages' => [['role' => 'user', 'content' => $prompt]]]);
        $ch = curl_init('https://api.anthropic.com/v1/messages');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload, CURLOPT_TIMEOUT => 110,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json',
                'x-api-key: ' . $api_key, 'anthropic-version: 2023-06-01']]);
        $resp = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); $cerr = curl_error($ch); curl_close($ch);
        if ($cerr) { $erro_api = $cerr; }
        elseif ($code !== 200) { $erro_api = 'Claude HTTP ' . $code; }
        else { $d = json_decode($resp, true); $resposta_texto = $d['content'][0]['text'] ?? null; if (!$resposta_texto) $erro_api = 'Resposta vazia Claude'; }

    } elseif ($provider === 'openai') {
        $modelo_usado = $model ?? 'gpt-4o';
        $payload = json_encode(['model' => $modelo_usado, 'max_tokens' => 4096, 'temperature' => 0.2,
            'messages' => [['role' => 'system', 'content' => 'Especialista em botânica. Responda em JSON válido sem markdown.'],
                ['role' => 'user', 'content' => $prompt]]]);
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload, CURLOPT_TIMEOUT => 110,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $api_key]]);
        $resp = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); $cerr = curl_error($ch); curl_close($ch);
        if ($cerr) { $erro_api = $cerr; }
        elseif ($code !== 200) { $erro_api = 'OpenAI HTTP ' . $code; }
        else { $d = json_decode($resp, true); $resposta_texto = $d['choices'][0]['message']['content'] ?? null; if (!$resposta_texto) $erro_api = 'Resposta vazia OpenAI'; }

    } elseif ($provider === 'gemini') {
        $modelo_usado = $model ?? 'gemini-1.5-flash';
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $modelo_usado . ':generateContent?key=' . urlencode($api_key);
        $payload = json_encode(['contents' => [['parts' => [['text' => $prompt]]]], 'generationConfig' => ['temperature' => 0.2, 'maxOutputTokens' => 4096]]);
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_TIMEOUT => 110, CURLOPT_HTTPHEADER => ['Content-Type: application/json']]);
        $resp = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); $cerr = curl_error($ch); curl_close($ch);
        if ($cerr) { $erro_api = $cerr; }
        elseif ($code !== 200) { $erro_api = 'Gemini HTTP ' . $code; }
        else { $d = json_decode($resp, true); $resposta_texto = $d['candidates'][0]['content']['parts'][0]['text'] ?? null; if (!$resposta_texto) $erro_api = 'Resposta vazia Gemini'; }

    } elseif ($provider === 'deepseek') {
        $modelo_usado = $model ?: 'deepseek-chat';
        $payload = json_encode(['model' => $modelo_usado, 'max_tokens' => 4096, 'temperature' => 0.2,
            'messages' => [['role' => 'system', 'content' => 'Especialista em botânica. Responda em JSON válido sem markdown.'],
                ['role' => 'user', 'content' => $prompt]]], JSON_UNESCAPED_UNICODE);
        $ch = curl_init('https://api.deepseek.com/chat/completions');
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload, CURLOPT_TIMEOUT => 110, CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . $api_key]]);
        $resp = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); $cerr = curl_error($ch); curl_close($ch);
        if ($cerr) { $erro_api = $cerr; }
        elseif ($code !== 200) { $b = json_decode($resp, true); $erro_api = 'DeepSeek HTTP ' . $code . ': ' . ($b['error']['message'] ?? $resp); }
        else { $d = json_decode($resp, true); $resposta_texto = $d['choices'][0]['message']['content'] ?? null; if (!$resposta_texto) $erro_api = 'Resposta vazia DeepSeek'; }

    } else {
        return ['sucesso' => false, 'erro' => 'Provider não suportado: ' . $provider];
    }

    if ($erro_api) return ['sucesso' => false, 'erro' => $erro_api];

    // Limpar e parsear JSON
    $jl = trim($resposta_texto);
    $jl = preg_replace('/^```(?:json)?\s*/i', '', $jl);
    $jl = preg_replace('/\s*```$/', '', trim($jl));
    $jl = preg_replace_callback('/"(?:[^"\\\\]|\\\\.)*"/s', function($m) {
        $s = $m[0];
        $s = str_replace(["\r\n","\r","\n","\t"], ['\n','\n','\n','\t'], $s);
        return $s;
    }, $jl);

    $dados = json_decode($jl, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['sucesso' => false, 'erro' => 'JSON inválido: ' . json_last_error_msg()];
    }

    // Validar ENUMs — divergentes recebem a melhor sugestão automaticamente
    $campos_salvos = [];
    foreach ($dados as $campo => $valor) {
        $v = trim((string)$valor);
        if (str_ends_with($campo, '_ref') || !isset($opcoes_validas[$campo])) {
            $campos_salvos[$campo] = $v; continue;
        }
        if ($v === '' || $v === 'Não informado') { $campos_salvos[$campo] = ''; continue; }
        $match = null;
        foreach ($opcoes_validas[$campo] as $op) {
            if (strcasecmp($op, $v) === 0) { $match = $op; break; }
        }
        if ($match) {
            $campos_salvos[$campo] = $match;
        } else {
            // Usa melhor sugestão por similaridade
            $melhor = -1; $sug = $opcoes_validas[$campo][0];
            foreach ($opcoes_validas[$campo] as $op) {
                similar_text(mb_strtolower($v,'UTF-8'), mb_strtolower($op,'UTF-8'), $pct);
                if ($pct > $melhor) { $melhor = $pct; $sug = $op; }
            }
            $campos_salvos[$campo] = $sug; // melhor match automático
        }
    }

    return ['sucesso' => true, 'campos' => $campos_salvos];
}

// ── Salvar características no BD ─────────────────────────
function salvarCaracteristicas(PDO $pdo, int $especie_id, array $campos): void {
    $campos_permitidos = [
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
    foreach ($campos_permitidos as $col) {
        $dados[$col] = ($campos[$col] ?? '') ?: null;
    }

    $existe = $pdo->prepare("SELECT id FROM especies_caracteristicas WHERE especie_id = ?")->execute([$especie_id]);
    $existe = $pdo->prepare("SELECT id FROM especies_caracteristicas WHERE especie_id = ?");
    $existe->execute([$especie_id]);
    if ($existe->fetch()) {
        $sets = [];
        foreach ($campos_permitidos as $col) $sets[] = "`{$col}` = :{$col}";
        $sets[] = "data_atualizacao = NOW()";
        $pdo->prepare("UPDATE especies_caracteristicas SET " . implode(', ', $sets) . " WHERE especie_id = :especie_id")
            ->execute($dados);
    } else {
        $cols = implode(', ', array_map(fn($c) => "`{$c}`", array_keys($dados)));
        $phs  = ':' . implode(', :', array_keys($dados));
        $pdo->prepare("INSERT INTO especies_caracteristicas ({$cols}) VALUES ({$phs})")->execute($dados);
    }

    $pdo->prepare("UPDATE especies_administrativo SET status = 'dados_internet', data_ultima_atualizacao = NOW() WHERE id = ?")
        ->execute([$especie_id]);
}

// ── INÍCIO DO PROCESSAMENTO ───────────────────────────────
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Batch IA — Penomato</title>
<style>
body { font-family: monospace; background: #111; color: #eee; padding: 20px; }
.ok  { color: #4caf50; }
.err { color: #f44336; }
.inf { color: #90caf9; }
.warn{ color: #ffc107; }
h2   { color: #fff; }
</style>
</head>
<body>
<h2>🌿 Batch IA — Penomato</h2>
<pre>
<?php

$especies = $pdo->query(
    "SELECT id, nome_cientifico, status FROM especies_administrativo ORDER BY nome_cientifico"
)->fetchAll(PDO::FETCH_ASSOC);

$total     = count($especies);
$ai_ok     = 0;
$ai_erro   = 0;
$art_ok    = 0;
$art_erro  = 0;
$pulados   = 0;

linha("<span class='inf'>Total de espécies: {$total}</span>");
linha(str_repeat('─', 60));

foreach ($especies as $i => $esp) {
    $num    = $i + 1;
    $id     = (int)$esp['id'];
    $nome   = $esp['nome_cientifico'];
    $status = $esp['status'];

    linha("\n<span class='inf'>[{$num}/{$total}] {$nome} (status: {$status})</span>");

    // ── Busca IA apenas para sem_dados ────────────────────
    if ($status === 'sem_dados') {
        linha("  → Chamando IA...");
        $resultado = chamarIA($nome, $opcoes_validas);

        if (!$resultado['sucesso']) {
            linha("  <span class='err'>✗ IA falhou: {$resultado['erro']}</span>");
            $ai_erro++;
        } else {
            try {
                salvarCaracteristicas($pdo, $id, $resultado['campos']);
                linha("  <span class='ok'>✓ Dados salvos no BD</span>");
                $ai_ok++;
            } catch (Exception $e) {
                linha("  <span class='err'>✗ Erro ao salvar: " . htmlspecialchars($e->getMessage()) . "</span>");
                $ai_erro++;
            }
        }
    } else {
        linha("  → Já tem dados. Pulando busca IA.");
        $pulados++;
    }

    // ── Regenerar artigo para todas ───────────────────────
    try {
        regenerarArtigoEspecie($pdo, $id);
        linha("  <span class='ok'>✓ Artigo regenerado</span>");
        $art_ok++;
    } catch (Exception $e) {
        linha("  <span class='err'>✗ Erro no artigo: " . htmlspecialchars($e->getMessage()) . "</span>");
        $art_erro++;
    }
}

linha("\n" . str_repeat('─', 60));
linha("<span class='ok'>✅ Concluído!</span>");
linha("  IA buscada e salva : {$ai_ok}");
linha("  IA com erro        : {$ai_erro}");
linha("  Puladas (já tinham): {$pulados}");
linha("  Artigos regenerados: {$art_ok}");
linha("  Artigos com erro   : {$art_erro}");
linha("\n<span class='warn'>⚠️  DELETE este arquivo agora: batch_ia.php</span>");
?>
</pre>
</body>
</html>
