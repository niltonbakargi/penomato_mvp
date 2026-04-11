<?php
// ============================================================
// BUSCAR CARACTERÍSTICAS VIA IA
// Chamado via AJAX POST por inserir_dados_internet.php.
// Chama a API configurada (Claude, OpenAI ou Gemini),
// retorna o JSON com as características morfológicas.
// ============================================================

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/app.php';

// ============================================================
// AUTENTICAÇÃO
// ============================================================
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado.']);
    exit;
}

// ============================================================
// INPUTS
// ============================================================
$nome_cientifico = trim($_POST['nome_cientifico'] ?? '');

if (empty($nome_cientifico)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Nome científico não informado.']);
    exit;
}

// ============================================================
// VERIFICAR CONFIGURAÇÃO DA API
// ============================================================
if (!defined('AI_PROVIDER') || !defined('AI_API_KEY') || AI_API_KEY === '') {
    echo json_encode(['sucesso' => false, 'erro' => 'API de IA não configurada. Adicione AI_PROVIDER e AI_API_KEY em config/producao.php.']);
    exit;
}

$provider = strtolower(AI_PROVIDER); // 'claude', 'openai' ou 'gemini'
$api_key  = AI_API_KEY;
$model    = defined('AI_MODEL') ? AI_MODEL : null;

// ============================================================
// PROMPT (mesmo usado na seção manual da página)
// ============================================================
$prompt = <<<PROMPT
Você é um especialista em botânica sistemática. Preencha o JSON abaixo com as características botânicas da espécie indicada.

ESPÉCIE-ALVO: {$nome_cientifico}

REGRAS OBRIGATÓRIAS — leia antes de responder:

1. FORMATO DE SAÍDA: Responda APENAS com o JSON preenchido, sem nenhum texto antes ou depois, sem blocos de código markdown (sem ```json), sem comentários.

2. ESTRUTURA: O JSON de saída deve ser PLANO (flat), com todos os campos no mesmo nível.

3. CAMPOS DE SELEÇÃO: Escreva APENAS a string exata da opção escolhida. NUNCA use valor fora da lista.

4. CAMPOS MÚLTIPLOS (sinonimos, nome_popular): String com valores separados por vírgula e espaço.

5. CAMPO referencias: String com cada referência separada por \n, no formato: N. SOBRENOME, Nome. Título. Local: Editora, Ano.

6. CAMPOS _ref: String com números separados por vírgula (ex: "1,3"). Se sem referência, use "".

7. CAMPOS OPCIONAIS INAPLICÁVEIS: Use string vazia "".

8. DADOS AUSENTES: Se não puder ser confirmado com segurança, use "Não informado" para campos de seleção e "" para campos livres. NUNCA invente dados.

9. especie_id deve conter EXATAMENTE: {$nome_cientifico}

---

CAMPOS DE SELEÇÃO E SUAS OPÇÕES VÁLIDAS:

forma_folha: Acicular | Cordiforme | Elíptica | Lanceolada | Linear | Lobada | Obovada | Orbicular | Ovada | Palmada | Reniforme | Sagitada | Trifoliada
filotaxia_folha: Alterna | Alterna dística | Alterna espiralada | Oposta | Oposta decussada | Verticilada
tipo_folha: Simples | Composta bipinada | Composta digitada | Composta imparipinada | Composta paripinada | Composta pinnada | Composta trifoliada | Composta tripinada
tamanho_folha: Microfila | Nanofila | Mesofila | Macrofila | Megafila
textura_folha: Cartácea | Coriácea | Glabra | Membranácea | Pilosa | Pubescente | Rugosa | Suculenta | Tomentosa | Cerosa
margem_folha: Crenada | Dentada | Inteira | Lobada | Ondulada | Serreada | Serrilhada | Partida
venacao_folha: Curvinérvea | Dicotômica | Paralela | Peninérvea | Reticulada palmada | Reticulada pinada
cor_flores: Alaranjada | Amarela | Avermelhada | Azul | Branca | Esverdeada | Lilás | Púrpura | Rósea | Roxa | Vermelha | Vinácea
simetria_floral: Actinomorfa | Zigomorfa | Assimétrica
numero_petalas: 3 pétalas | 4 pétalas | 5 pétalas | 6 pétalas | Muitas pétalas | Ausentes
disposicao_flores: Solitária | Capítulo | Cacho | Corimbo | Espádice | Espiga | Panícula | Umbela
aroma: Ausente | Suave | Forte | Desagradável | Adocicada | Cítrica
tamanho_flor: Muito pequena | Pequena | Média | Grande | Muito grande
tipo_fruto: Aquênio | Baga | Cápsula | Drupa | Folículo | Legume | Pixídio | Sâmara | Síliqua | Cariopse | Hespéridio | Pepo
tamanho_fruto: Minúsculo | Pequeno | Médio | Grande | Muito grande
cor_fruto: Alaranjado | Amarelo | Avermelhado | Branco | Esverdeado | Marrom | Preto | Roxo | Verde | Vináceo
textura_fruto: Lisa | Rugosa | Coriácea | Pubescente | Pilosa | Espinhosa | Cerosa | Tuberculada
dispersao_fruto: Anemocórica | Autocórica | Hidrocórica | Zoocórica | Mirmecocórica | Ornitocórica
aroma_fruto: Ausente | Suave | Forte | Adocicado | Cítrico | Desagradável
tipo_semente: Alada | Carnosa | Dura | Oleaginosa | Plumosa | Ruminada | Arilada
tamanho_semente: Minúscula | Muito pequena | Pequena | Média | Grande | Muito grande
cor_semente: Amarela | Branca | Castanha | Cinza | Marrom | Preta | Vermelha | Alaranjada
textura_semente: Lisa | Rugosa | Estriada | Pontuada | Foveolada | Reticulada | Tuberculada
quantidade_sementes: 1 | 2–3 | 4–10 | 11–50 | > 50
tipo_caule: Ereto | Prostrado | Escandente | Trepador | Rastejante | Subterrâneo
estrutura_caule: Herbáceo | Lenhoso | Suculento | Sublenhoso
textura_caule: Lisa | Rugosa | Fissurada | Sulcada | Estriada | Escamosa | Suberosa | Aculeada
cor_caule: Acinzentado | Alaranjado | Avermelhado | Esbranquiçado | Esverdeado | Marrom | Pardacento
forma_caule: Cilíndrico | Quadrangular | Triangular | Achatado | Alado | Irregular
modificacao_caule: Cladódio | Espinho | Estolão | Gavinha | Rizoma | Tubérculo | Bulbo
diametro_caule: Capilar | Delgado | Fino | Médio | Grosso | Muito grosso
ramificacao_caule: Monopodial | Simpodial | Dicotômica | Pseudodicotômica
possui_espinhos: Sim | Não | Não informado
possui_latex: Sim | Não | Não informado
possui_seiva: Sim | Não | Não informado
possui_resina: Sim | Não | Não informado

---

ESTRUTURA DO JSON DE SAÍDA (preencha todos os campos):

{
  "especie_id": "{$nome_cientifico}",
  "nome_cientifico_completo": "",
  "nome_cientifico_completo_ref": "",
  "sinonimos": "",
  "sinonimos_ref": "",
  "nome_popular": "",
  "nome_popular_ref": "",
  "familia": "",
  "familia_ref": "",
  "forma_folha": "",
  "forma_folha_ref": "",
  "filotaxia_folha": "",
  "filotaxia_folha_ref": "",
  "tipo_folha": "",
  "tipo_folha_ref": "",
  "tamanho_folha": "",
  "tamanho_folha_ref": "",
  "textura_folha": "",
  "textura_folha_ref": "",
  "margem_folha": "",
  "margem_folha_ref": "",
  "venacao_folha": "",
  "venacao_folha_ref": "",
  "cor_flores": "",
  "cor_flores_ref": "",
  "simetria_floral": "",
  "simetria_floral_ref": "",
  "numero_petalas": "",
  "numero_petalas_ref": "",
  "disposicao_flores": "",
  "disposicao_flores_ref": "",
  "aroma": "",
  "aroma_ref": "",
  "tamanho_flor": "",
  "tamanho_flor_ref": "",
  "tipo_fruto": "",
  "tipo_fruto_ref": "",
  "tamanho_fruto": "",
  "tamanho_fruto_ref": "",
  "cor_fruto": "",
  "cor_fruto_ref": "",
  "textura_fruto": "",
  "textura_fruto_ref": "",
  "dispersao_fruto": "",
  "dispersao_fruto_ref": "",
  "aroma_fruto": "",
  "aroma_fruto_ref": "",
  "tipo_semente": "",
  "tipo_semente_ref": "",
  "tamanho_semente": "",
  "tamanho_semente_ref": "",
  "cor_semente": "",
  "cor_semente_ref": "",
  "textura_semente": "",
  "textura_semente_ref": "",
  "quantidade_sementes": "",
  "quantidade_sementes_ref": "",
  "tipo_caule": "",
  "tipo_caule_ref": "",
  "estrutura_caule": "",
  "estrutura_caule_ref": "",
  "textura_caule": "",
  "textura_caule_ref": "",
  "cor_caule": "",
  "cor_caule_ref": "",
  "forma_caule": "",
  "forma_caule_ref": "",
  "modificacao_caule": "",
  "modificacao_caule_ref": "",
  "diametro_caule": "",
  "diametro_caule_ref": "",
  "ramificacao_caule": "",
  "ramificacao_caule_ref": "",
  "possui_espinhos": "",
  "possui_espinhos_ref": "",
  "possui_latex": "",
  "possui_latex_ref": "",
  "possui_seiva": "",
  "possui_seiva_ref": "",
  "possui_resina": "",
  "possui_resina_ref": "",
  "referencias": ""
}
PROMPT;

// ============================================================
// CHAMAR A API CONFIGURADA
// ============================================================
$resposta_texto = null;
$erro_api       = null;

if ($provider === 'claude') {

    // --- Anthropic Claude ---
    $modelo = $model ?? 'claude-opus-4-6';
    $payload = json_encode([
        'model'      => $modelo,
        'max_tokens' => 4096,
        'messages'   => [
            ['role' => 'user', 'content' => $prompt]
        ],
    ]);

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 55,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-api-key: ' . $api_key,
            'anthropic-version: 2023-06-01',
        ],
    ]);
    $resp      = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err  = curl_error($ch);
    curl_close($ch);

    if ($curl_err) {
        $erro_api = 'Erro de conexão: ' . $curl_err;
    } elseif ($http_code !== 200) {
        $erro_api = 'Claude retornou HTTP ' . $http_code . ': ' . $resp;
    } else {
        $data = json_decode($resp, true);
        $resposta_texto = $data['content'][0]['text'] ?? null;
        if (!$resposta_texto) $erro_api = 'Resposta inesperada da Claude API.';
    }

} elseif ($provider === 'openai') {

    // --- OpenAI ---
    $modelo = $model ?? 'gpt-4o';
    $payload = json_encode([
        'model'    => $modelo,
        'messages' => [
            ['role' => 'system', 'content' => 'Você é um especialista em botânica sistemática. Responda sempre em JSON válido, sem markdown.'],
            ['role' => 'user',   'content' => $prompt],
        ],
        'max_tokens'  => 4096,
        'temperature' => 0.2,
    ]);

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 55,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key,
        ],
    ]);
    $resp      = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err  = curl_error($ch);
    curl_close($ch);

    if ($curl_err) {
        $erro_api = 'Erro de conexão: ' . $curl_err;
    } elseif ($http_code !== 200) {
        $erro_api = 'OpenAI retornou HTTP ' . $http_code . ': ' . $resp;
    } else {
        $data = json_decode($resp, true);
        $resposta_texto = $data['choices'][0]['message']['content'] ?? null;
        if (!$resposta_texto) $erro_api = 'Resposta inesperada da OpenAI API.';
    }

} elseif ($provider === 'gemini') {

    // --- Google Gemini ---
    $modelo = $model ?? 'gemini-1.5-flash';
    $url    = 'https://generativelanguage.googleapis.com/v1beta/models/' . $modelo . ':generateContent?key=' . urlencode($api_key);
    $payload = json_encode([
        'contents' => [
            ['parts' => [['text' => $prompt]]]
        ],
        'generationConfig' => [
            'temperature'     => 0.2,
            'maxOutputTokens' => 4096,
        ],
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 55,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    ]);
    $resp      = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err  = curl_error($ch);
    curl_close($ch);

    if ($curl_err) {
        $erro_api = 'Erro de conexão: ' . $curl_err;
    } elseif ($http_code !== 200) {
        $erro_api = 'Gemini retornou HTTP ' . $http_code . ': ' . $resp;
    } else {
        $data = json_decode($resp, true);
        $resposta_texto = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$resposta_texto) $erro_api = 'Resposta inesperada da Gemini API.';
    }

} else {
    $erro_api = 'Provider não suportado: "' . $provider . '". Use claude, openai ou gemini.';
}

if ($erro_api) {
    error_log('[Penomato] buscar_caracteristicas_ia erro: ' . $erro_api);
    echo json_encode(['sucesso' => false, 'erro' => $erro_api]);
    exit;
}

// ============================================================
// LIMPAR E VALIDAR O JSON RETORNADO
// ============================================================
// Remove eventuais blocos markdown que o modelo possa ter adicionado
$json_limpo = trim($resposta_texto);
$json_limpo = preg_replace('/^```(?:json)?\s*/i', '', $json_limpo);
$json_limpo = preg_replace('/\s*```$/', '', $json_limpo);
$json_limpo = trim($json_limpo);

$dados = json_decode($json_limpo, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('[Penomato] buscar_caracteristicas_ia JSON inválido: ' . $json_limpo);
    echo json_encode(['sucesso' => false, 'erro' => 'A IA retornou um JSON inválido. Tente novamente.', 'raw' => $json_limpo]);
    exit;
}

// ============================================================
// SUCESSO
// ============================================================
echo json_encode([
    'sucesso'   => true,
    'provider'  => $provider,
    'modelo'    => $modelo ?? $model,
    'json_dados' => $dados,
]);
