<?php
// ============================================================
// src\Controllers\buscar_dados_especie_ai.php
// BUSCAR DADOS DA ESPÉCIE VIA IA — endpoint AJAX
// Recebe temp_id via POST, valida sessão, busca nome científico
// no BD, chama a API de IA configurada, valida os campos
// retornados contra os enums do sistema e devolve:
//   campos_validos     → preenchidos direto na planilha
//   campos_divergentes → precisam de revisão no modal
// ============================================================

@set_time_limit(60);
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/banco_de_dados.php';

// ============================================================
// AUTENTICAÇÃO
// ============================================================
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado.']);
    exit;
}

// ============================================================
// VALIDAR SESSÃO TEMPORÁRIA VIA temp_id
// ============================================================
$temp_id = trim($_POST['temp_id'] ?? '');

if (
    empty($temp_id) ||
    !isset($_SESSION['importacao_temporaria']) ||
    $_SESSION['importacao_temporaria']['temp_id'] !== $temp_id ||
    $_SESSION['importacao_temporaria']['usuario_id'] != $_SESSION['usuario_id']
) {
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão inválida ou expirada.']);
    exit;
}

$especie_id = $_SESSION['importacao_temporaria']['especie_id'];

// ============================================================
// BUSCAR NOME CIENTÍFICO NO BANCO
// ============================================================
$stmt = $pdo->prepare("SELECT nome_cientifico FROM especies_administrativo WHERE id = ?");
$stmt->execute([$especie_id]);
$row = $stmt->fetch();

$nome_cientifico = $row['nome_cientifico'] ?? '';
if (empty($nome_cientifico)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Espécie não encontrada no banco de dados.']);
    exit;
}

// ============================================================
// VERIFICAR CONFIGURAÇÃO DA API
// ============================================================
if (!defined('AI_PROVIDER') || !defined('AI_API_KEY') || AI_API_KEY === '') {
    echo json_encode(['sucesso' => false, 'erro' => 'API de IA não configurada. Adicione AI_PROVIDER e AI_API_KEY em config/producao.php.']);
    exit;
}

$provider = strtolower(AI_PROVIDER);
$api_key  = AI_API_KEY;
$model    = defined('AI_MODEL') ? AI_MODEL : null;

// ============================================================
// ENUMS VÁLIDOS DO SISTEMA (fonte da verdade)
// Estes valores são os aceitos pelo formulário e pelo banco.
// O prompt abaixo é gerado a partir desta mesma lista.
// ============================================================
$opcoes_validas = [
    'forma_folha'       => ['Acicular','Cordiforme','Elíptica','Lanceolada','Linear','Lobada','Obovada','Orbicular','Ovada','Palmada','Reniforme','Sagitada','Trifoliada'],
    'filotaxia_folha'   => ['Alterna','Oposta Simples','Oposta Decussada','Verticilada','Dística','Espiralada'],
    'tipo_folha'        => ['Simples','Composta bipinada','Composta digitada','Composta imparipinada','Composta paripinada','Composta pinnada','Composta trifoliada','Composta tripinada'],
    'tamanho_folha'     => ['Microfila','Nanofila','Mesofila','Macrofila','Megafila'],
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
    'tipo_caule'        => ['Ereto','Prostrado','Escandente','Trepador','Rastejante','Subterrâneo'],
    'estrutura_caule'   => ['Herbáceo','Lenhoso','Suculento','Sublenhoso'],
    'textura_caule'     => ['Lisa','Rugosa','Fissurada','Sulcada','Estriada','Escamosa','Suberosa','Aculeada'],
    'cor_caule'         => ['Acinzentado','Alaranjado','Avermelhado','Esbranquiçado','Esverdeado','Marrom','Pardacento'],
    'forma_caule'       => ['Cilíndrico','Quadrangular','Triangular','Achatado','Alado','Irregular'],
    'modificacao_caule' => ['Cladódio','Espinho','Estolão','Gavinha','Rizoma','Tubérculo','Bulbo'],
    'diametro_caule'    => ['Capilar','Delgado','Fino','Médio','Grosso','Muito grosso'],
    'ramificacao_caule' => ['Monopodial','Simpodial','Dicotômica','Pseudodicotômica'],
    'possui_espinhos'   => ['Sim','Não','Não informado'],
    'possui_latex'      => ['Sim','Não','Não informado'],
    'possui_seiva'      => ['Sim','Não','Não informado'],
    'possui_resina'     => ['Sim','Não','Não informado'],
];

// Gera a string de opções para o prompt a partir do array acima
$linhas_opcoes = '';
foreach ($opcoes_validas as $campo => $opcoes) {
    $linhas_opcoes .= $campo . ': ' . implode(' | ', $opcoes) . "\n";
}

// ============================================================
// PROMPT
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

{$linhas_opcoes}
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
$modelo_usado   = null;

if ($provider === 'claude') {

    $modelo_usado = $model ?? 'claude-opus-4-6';
    $payload = json_encode([
        'model'      => $modelo_usado,
        'max_tokens' => 4096,
        'messages'   => [['role' => 'user', 'content' => $prompt]],
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
        $erro_api = 'Claude retornou HTTP ' . $http_code . '.';
    } else {
        $data = json_decode($resp, true);
        $resposta_texto = $data['content'][0]['text'] ?? null;
        if (!$resposta_texto) $erro_api = 'Resposta inesperada da Claude API.';
    }

} elseif ($provider === 'openai') {

    $modelo_usado = $model ?? 'gpt-4o';
    $payload = json_encode([
        'model'       => $modelo_usado,
        'messages'    => [
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
        $erro_api = 'OpenAI retornou HTTP ' . $http_code . '.';
    } else {
        $data = json_decode($resp, true);
        $resposta_texto = $data['choices'][0]['message']['content'] ?? null;
        if (!$resposta_texto) $erro_api = 'Resposta inesperada da OpenAI API.';
    }

} elseif ($provider === 'gemini') {

    $modelo_usado = $model ?? 'gemini-1.5-flash';
    $url     = 'https://generativelanguage.googleapis.com/v1beta/models/' . $modelo_usado . ':generateContent?key=' . urlencode($api_key);
    $payload = json_encode([
        'contents'         => [['parts' => [['text' => $prompt]]]],
        'generationConfig' => ['temperature' => 0.2, 'maxOutputTokens' => 4096],
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
        $erro_api = 'Gemini retornou HTTP ' . $http_code . '.';
    } else {
        $data = json_decode($resp, true);
        $resposta_texto = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$resposta_texto) $erro_api = 'Resposta inesperada da Gemini API.';
    }

} elseif ($provider === 'deepseek') {

    $modelo_usado = $model ?: 'deepseek-chat';
    $payload = json_encode([
        'model'       => $modelo_usado,
        'messages'    => [
            ['role' => 'system', 'content' => 'Você é um especialista em botânica sistemática. Responda sempre em JSON válido, sem markdown.'],
            ['role' => 'user',   'content' => $prompt],
        ],
        'max_tokens'  => 4096,
        'temperature' => 0.2,
    ], JSON_UNESCAPED_UNICODE);

    if ($payload === false) {
        $erro_api = 'Erro ao serializar payload: ' . json_last_error_msg();
    } else {
        $ch = curl_init('https://api.deepseek.com/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 110,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
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
            $body = json_decode($resp, true);
            $detalhe = $body['error']['message'] ?? $resp;
            $erro_api = 'DeepSeek HTTP ' . $http_code . ': ' . $detalhe;
        } else {
            $data = json_decode($resp, true);
            $resposta_texto = $data['choices'][0]['message']['content'] ?? null;
            if (!$resposta_texto) $erro_api = 'Resposta inesperada da DeepSeek API.';
        }
    }

} else {
    $erro_api = 'Provider não suportado: "' . $provider . '". Use claude, openai, gemini ou deepseek.';
}

if ($erro_api) {
    error_log('[Penomato] buscar_dados_especie_ai erro: ' . $erro_api);
    echo json_encode(['sucesso' => false, 'erro' => $erro_api]);
    exit;
}

// ============================================================
// LIMPAR E PARSEAR O JSON RETORNADO
// ============================================================
$json_limpo = trim($resposta_texto);
// Remove blocos markdown
$json_limpo = preg_replace('/^```(?:json)?\s*/i', '', $json_limpo);
$json_limpo = preg_replace('/\s*```$/', '', $json_limpo);
$json_limpo = trim($json_limpo);
// Substitui quebras de linha e tabs literais dentro de valores de string JSON
// (controle inválido em JSON — DeepSeek às vezes retorna isso no campo referencias)
$json_limpo = preg_replace_callback(
    '/"(?:[^"\\\\]|\\\\.)*"/s',
    function ($m) {
        $s = $m[0];
        $s = str_replace("\r\n", '\n', $s);
        $s = str_replace("\r",   '\n', $s);
        $s = str_replace("\n",   '\n', $s);
        $s = str_replace("\t",   '\t', $s);
        return $s;
    },
    $json_limpo
);

$dados = json_decode($json_limpo, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'sucesso' => false,
        'erro'    => 'JSON inválido: ' . json_last_error_msg(),
        'raw'     => mb_substr($json_limpo, 0, 500),
    ]);
    exit;
}

// ============================================================
// VALIDAR CAMPOS CONTRA OS ENUMS E SEPARAR DIVERGENTES
// ============================================================
$campos_validos     = [];
$campos_divergentes = [];

foreach ($dados as $campo => $valor) {
    $valor_str = trim((string)$valor);

    // Campos _ref e campos livres passam direto
    if (str_ends_with($campo, '_ref') || !isset($opcoes_validas[$campo])) {
        $campos_validos[$campo] = $valor_str;
        continue;
    }

    // Valor vazio ou "Não informado" — aceita direto
    if ($valor_str === '' || $valor_str === 'Não informado') {
        $campos_validos[$campo] = $valor_str;
        continue;
    }

    $opcoes      = $opcoes_validas[$campo];
    $match_exato = null;

    foreach ($opcoes as $opcao) {
        if (strcasecmp($opcao, $valor_str) === 0) {
            $match_exato = $opcao; // usa capitalização canônica
            break;
        }
    }

    if ($match_exato !== null) {
        $campos_validos[$campo] = $match_exato;
    } else {
        // Calcula melhor sugestão por similaridade de string
        $melhor_score = -1;
        $sugestao     = $opcoes[0];
        foreach ($opcoes as $opcao) {
            similar_text(
                mb_strtolower($valor_str, 'UTF-8'),
                mb_strtolower($opcao, 'UTF-8'),
                $pct
            );
            if ($pct > $melhor_score) {
                $melhor_score = $pct;
                $sugestao     = $opcao;
            }
        }

        $campos_divergentes[] = [
            'campo'    => $campo,
            'valor_ia' => $valor_str,
            'sugestao' => $sugestao,
            'opcoes'   => $opcoes,
        ];
    }
}

// ============================================================
// RETORNO
// ============================================================
echo json_encode([
    'sucesso'            => true,
    'provider'           => $provider,
    'modelo'             => $modelo_usado,
    'campos_validos'     => $campos_validos,
    'campos_divergentes' => $campos_divergentes,
]);
