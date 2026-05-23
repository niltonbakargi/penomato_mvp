<?php
// ============================================================
// batch_ia.php — Processar espécies com IA uma a uma
// ============================================================
require_once __DIR__ . '/../../config/banco_de_dados.php';
require_once __DIR__ . '/../helpers/gerador_artigo.php';
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_BASE . '/src/Views/auth/login.php');
    exit;
}

// ============================================================
// AJAX: processar uma espécie
// ============================================================
if (isset($_POST['acao']) && $_POST['acao'] === 'processar') {
    @set_time_limit(120);
    header('Content-Type: application/json; charset=utf-8');

    $especie_id = (int)($_POST['especie_id'] ?? 0);
    if (!$especie_id) { echo json_encode(['ok' => false, 'erro' => 'ID inválido']); exit; }

    $row = buscarUm("SELECT nome_cientifico, status FROM especies_administrativo WHERE id = :id", [':id' => $especie_id]);
    if (!$row) { echo json_encode(['ok' => false, 'erro' => 'Espécie não encontrada']); exit; }

    $nome   = $row['nome_cientifico'];
    $status = $row['status'];
    $etapas = [];

    // ── Busca IA para todas as espécies ──────────────────
    if (true) {
        if (!defined('AI_PROVIDER') || !defined('AI_API_KEY') || AI_API_KEY === '') {
            echo json_encode(['ok' => false, 'erro' => 'API de IA não configurada.']); exit;
        }

        $opcoes_validas = [
            'forma_folha'         => ['Lanceolada','Linear','Elíptica','Ovada','Orbicular','Cordiforme','Espatulada','Sagitada','Reniforme','Obovada','Trilobada','Palmada','Lobada'],
            'filotaxia_folha'     => ['Alterna','Oposta Simples','Oposta Decussada','Verticilada','Dística','Espiralada'],
            'tipo_folha'          => ['Simples','Composta'],
            'divisao_folha'       => ['Trifoliada','Digitada','Pinnada','Bipinnada','Tripinnada','Tetrapinnada'],
            'paridade_pinnacao'   => ['Paripinnada','Imparipinnada'],
            'tamanho_folha'       => ['Microfilas (< 2 cm)','Nanofilas (2–7 cm)','Mesofilas (7–20 cm)','Macrófilas (20–50 cm)','Megafilas (> 50 cm)'],
            'textura_folha'       => ['Cartácea','Coriácea','Glabra','Membranácea','Pilosa','Pubescente','Rugosa','Suculenta','Tomentosa','Cerosa'],
            'margem_folha'        => ['Crenada','Dentada','Inteira','Lobada','Ondulada','Serreada','Serrilhada','Partida'],
            'venacao_folha'       => ['Curvinérvea','Dicotômica','Paralela','Peninérvea','Reticulada palmada','Reticulada pinada'],
            'cor_flores'          => ['Alaranjada','Amarela','Avermelhada','Azul','Branca','Esverdeada','Lilás','Púrpura','Rósea','Roxa','Vermelha','Vinácea'],
            'simetria_floral'     => ['Actinomorfa','Zigomorfa','Assimétrica'],
            'numero_petalas'      => ['3 pétalas','4 pétalas','5 pétalas','6 pétalas','Muitas pétalas','Ausentes'],
            'disposicao_flores'   => ['Solitária','Capítulo','Cacho','Corimbo','Espádice','Espiga','Panícula','Umbela'],
            'aroma'               => ['Ausente','Suave','Forte','Desagradável','Adocicada','Cítrica'],
            'tamanho_flor'        => ['Muito pequena','Pequena','Média','Grande','Muito grande'],
            'tipo_fruto'          => ['Aquênio','Baga','Cápsula','Drupa','Folículo','Legume','Pixídio','Sâmara','Síliqua','Cariopse','Hespéridio','Pepo'],
            'tamanho_fruto'       => ['Minúsculo','Pequeno','Médio','Grande','Muito grande'],
            'cor_fruto'           => ['Alaranjado','Amarelo','Avermelhado','Branco','Esverdeado','Marrom','Preto','Roxo','Verde','Vináceo'],
            'textura_fruto'       => ['Lisa','Rugosa','Coriácea','Pubescente','Pilosa','Espinhosa','Cerosa','Tuberculada'],
            'dispersao_fruto'     => ['Anemocórica','Autocórica','Hidrocórica','Zoocórica','Mirmecocórica','Ornitocórica'],
            'aroma_fruto'         => ['Ausente','Suave','Forte','Adocicado','Cítrico','Desagradável'],
            'tipo_semente'        => ['Alada','Carnosa','Dura','Oleaginosa','Plumosa','Ruminada','Arilada'],
            'tamanho_semente'     => ['Minúscula','Muito pequena','Pequena','Média','Grande','Muito grande'],
            'cor_semente'         => ['Amarela','Branca','Castanha','Cinza','Marrom','Preta','Vermelha','Alaranjada'],
            'textura_semente'     => ['Lisa','Rugosa','Estriada','Pontuada','Foveolada','Reticulada','Tuberculada'],
            'quantidade_sementes' => ['1','2–3','4–10','11–50','> 50'],
            'tipo_caule'          => ['Tronco','Estipe','Colmo','Liana','Haste','Escapo'],
            'textura_caule'       => ['Lisa','Rugosa','Fissurada','Sulcada','Estriada','Escamosa','Suberosa','Aculeada','Cerosa'],
            'cor_caule'           => ['Acinzentado','Alaranjado','Avermelhado','Esbranquiçado','Esverdeado','Marrom','Pardacento'],
            'forma_caule'         => ['Cilíndrico','Quadrangular','Triangular','Achatado','Alado'],
            'modificacao_caule'   => ['Cladódio','Estolão','Gavinha','Rizoma','Tubérculo','Bulbo','Sapopema'],
            'ramificacao_caule'   => ['Monopodial','Simpodial','Dicotômica','Pseudodicotômica'],
            'possui_espinhos'     => ['Sim','Não'],
            'possui_latex'        => ['Sim','Não'],
            'possui_seiva'        => ['Sim','Não'],
            'possui_resina'       => ['Sim','Não'],
        ];

        $linhas_opcoes = '';
        foreach ($opcoes_validas as $c => $ops) $linhas_opcoes .= $c . ': ' . implode(' | ', $ops) . "\n";

        $prompt = "Você é especialista em botânica sistemática. Preencha o JSON com as características de:\n\nESPÉCIE: {$nome}\n\nREGRAS:\n1. Responda APENAS com o JSON, sem markdown.\n2. Use APENAS valores exatos das listas abaixo.\n3. divisao_folha só se tipo_folha=Composta. paridade_pinnacao só se divisao for Pinnada/Bipinnada/Tripinnada.\n4. Dados incertos: \"Não informado\". NUNCA invente.\n5. especie_id = \"{$nome}\"\n\nOPÇÕES VÁLIDAS:\n{$linhas_opcoes}\nJSON DE SAÍDA:\n{\n  \"especie_id\":\"{$nome}\",\n  \"nome_cientifico_completo\":\"\",\"nome_cientifico_completo_ref\":\"\",\n  \"sinonimos\":\"\",\"sinonimos_ref\":\"\",\n  \"nome_popular\":\"\",\"nome_popular_ref\":\"\",\n  \"familia\":\"\",\"familia_ref\":\"\",\n  \"forma_folha\":\"\",\"forma_folha_ref\":\"\",\n  \"filotaxia_folha\":\"\",\"filotaxia_folha_ref\":\"\",\n  \"tipo_folha\":\"\",\"tipo_folha_ref\":\"\",\n  \"divisao_folha\":\"\",\"divisao_folha_ref\":\"\",\n  \"paridade_pinnacao\":\"\",\"paridade_pinnacao_ref\":\"\",\n  \"tamanho_folha\":\"\",\"tamanho_folha_ref\":\"\",\n  \"textura_folha\":\"\",\"textura_folha_ref\":\"\",\n  \"margem_folha\":\"\",\"margem_folha_ref\":\"\",\n  \"venacao_folha\":\"\",\"venacao_folha_ref\":\"\",\n  \"cor_flores\":\"\",\"cor_flores_ref\":\"\",\n  \"simetria_floral\":\"\",\"simetria_floral_ref\":\"\",\n  \"numero_petalas\":\"\",\"numero_petalas_ref\":\"\",\n  \"disposicao_flores\":\"\",\"disposicao_flores_ref\":\"\",\n  \"aroma\":\"\",\"aroma_ref\":\"\",\n  \"tamanho_flor\":\"\",\"tamanho_flor_ref\":\"\",\n  \"tipo_fruto\":\"\",\"tipo_fruto_ref\":\"\",\n  \"tamanho_fruto\":\"\",\"tamanho_fruto_ref\":\"\",\n  \"cor_fruto\":\"\",\"cor_fruto_ref\":\"\",\n  \"textura_fruto\":\"\",\"textura_fruto_ref\":\"\",\n  \"dispersao_fruto\":\"\",\"dispersao_fruto_ref\":\"\",\n  \"aroma_fruto\":\"\",\"aroma_fruto_ref\":\"\",\n  \"tipo_semente\":\"\",\"tipo_semente_ref\":\"\",\n  \"tamanho_semente\":\"\",\"tamanho_semente_ref\":\"\",\n  \"cor_semente\":\"\",\"cor_semente_ref\":\"\",\n  \"textura_semente\":\"\",\"textura_semente_ref\":\"\",\n  \"quantidade_sementes\":\"\",\"quantidade_sementes_ref\":\"\",\n  \"tipo_caule\":\"\",\"tipo_caule_ref\":\"\",\n  \"textura_caule\":\"\",\"textura_caule_ref\":\"\",\n  \"cor_caule\":\"\",\"cor_caule_ref\":\"\",\n  \"forma_caule\":\"\",\"forma_caule_ref\":\"\",\n  \"modificacao_caule\":\"\",\"modificacao_caule_ref\":\"\",\n  \"ramificacao_caule\":\"\",\"ramificacao_caule_ref\":\"\",\n  \"possui_espinhos\":\"\",\"possui_espinhos_ref\":\"\",\n  \"possui_latex\":\"\",\"possui_latex_ref\":\"\",\n  \"possui_seiva\":\"\",\"possui_seiva_ref\":\"\",\n  \"possui_resina\":\"\",\"possui_resina_ref\":\"\",\n  \"referencias\":\"\"\n}";

        // Chamada API
        $provider = strtolower(AI_PROVIDER);
        $api_key  = AI_API_KEY;
        $model    = defined('AI_MODEL') ? AI_MODEL : null;
        $resposta_texto = null; $erro_api = null;

        if ($provider === 'claude') {
            $pl = json_encode(['model' => $model ?? 'claude-opus-4-6', 'max_tokens' => 4096, 'temperature' => 0.2, 'messages' => [['role' => 'user', 'content' => $prompt]]]);
            $ch = curl_init('https://api.anthropic.com/v1/messages');
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$pl,CURLOPT_TIMEOUT=>110,CURLOPT_HTTPHEADER=>['Content-Type: application/json','x-api-key: '.$api_key,'anthropic-version: 2023-06-01']]);
            $r = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); $ce = curl_error($ch); curl_close($ch);
            if ($ce) $erro_api=$ce; elseif ($code!==200) $erro_api='Claude HTTP '.$code;
            else { $d=json_decode($r,true); $resposta_texto=$d['content'][0]['text']??null; if(!$resposta_texto) $erro_api='Resposta vazia'; }
        } elseif ($provider === 'openai') {
            $pl = json_encode(['model'=>$model??'gpt-4o','max_tokens'=>4096,'temperature'=>0.2,'messages'=>[['role'=>'system','content'=>'Especialista botânica. JSON sem markdown.'],['role'=>'user','content'=>$prompt]]]);
            $ch = curl_init('https://api.openai.com/v1/chat/completions');
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$pl,CURLOPT_TIMEOUT=>110,CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.$api_key]]);
            $r = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); $ce = curl_error($ch); curl_close($ch);
            if ($ce) $erro_api=$ce; elseif ($code!==200) $erro_api='OpenAI HTTP '.$code;
            else { $d=json_decode($r,true); $resposta_texto=$d['choices'][0]['message']['content']??null; if(!$resposta_texto) $erro_api='Resposta vazia'; }
        } elseif ($provider === 'gemini') {
            $mu = $model??'gemini-1.5-flash';
            $pl = json_encode(['contents'=>[['parts'=>[['text'=>$prompt]]]],'generationConfig'=>['temperature'=>0.2,'maxOutputTokens'=>4096]]);
            $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/'.$mu.':generateContent?key='.urlencode($api_key));
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$pl,CURLOPT_TIMEOUT=>110,CURLOPT_HTTPHEADER=>['Content-Type: application/json']]);
            $r = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); $ce = curl_error($ch); curl_close($ch);
            if ($ce) $erro_api=$ce; elseif ($code!==200) $erro_api='Gemini HTTP '.$code;
            else { $d=json_decode($r,true); $resposta_texto=$d['candidates'][0]['content']['parts'][0]['text']??null; if(!$resposta_texto) $erro_api='Resposta vazia'; }
        } elseif ($provider === 'deepseek') {
            $pl = json_encode(['model'=>$model?:'deepseek-chat','max_tokens'=>4096,'temperature'=>0.2,'messages'=>[['role'=>'system','content'=>'Especialista botânica. JSON sem markdown.'],['role'=>'user','content'=>$prompt]]],JSON_UNESCAPED_UNICODE);
            $ch = curl_init('https://api.deepseek.com/chat/completions');
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$pl,CURLOPT_TIMEOUT=>110,CURLOPT_CONNECTTIMEOUT=>10,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_SSL_VERIFYHOST=>false,CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.$api_key]]);
            $r = curl_exec($ch); $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); $ce = curl_error($ch); curl_close($ch);
            if ($ce) $erro_api=$ce; elseif ($code!==200) { $b=json_decode($r,true); $erro_api='DeepSeek HTTP '.$code.': '.($b['error']['message']??''); }
            else { $d=json_decode($r,true); $resposta_texto=$d['choices'][0]['message']['content']??null; if(!$resposta_texto) $erro_api='Resposta vazia'; }
        } else {
            echo json_encode(['ok'=>false,'erro'=>'Provider não suportado: '.AI_PROVIDER]); exit;
        }

        if ($erro_api) { echo json_encode(['ok'=>false,'erro'=>$erro_api]); exit; }

        // Limpar JSON
        $jl = trim($resposta_texto);
        $jl = preg_replace('/^```(?:json)?\s*/i','',$jl);
        $jl = preg_replace('/\s*```$/','',trim($jl));
        $jl = preg_replace_callback('/"(?:[^"\\\\]|\\\\.)*"/s', function($m) {
            return str_replace(["\r\n","\r","\n","\t"],['\n','\n','\n','\t'],$m[0]);
        }, $jl);
        $dados = json_decode($jl, true);
        if (json_last_error() !== JSON_ERROR_NONE) { echo json_encode(['ok'=>false,'erro'=>'JSON inválido: '.json_last_error_msg()]); exit; }

        // Validar ENUMs — usa melhor match automático para divergentes
        $campos_salvos = [];
        foreach ($dados as $campo => $valor) {
            $v = trim((string)$valor);
            if (str_ends_with($campo,'_ref') || !isset($opcoes_validas[$campo])) { $campos_salvos[$campo]=$v; continue; }
            if ($v===''||$v==='Não informado') { $campos_salvos[$campo]=''; continue; }
            $match=null;
            foreach ($opcoes_validas[$campo] as $op) if (strcasecmp($op,$v)===0) { $match=$op; break; }
            if ($match) { $campos_salvos[$campo]=$match; }
            else {
                $best=-1; $sug=$opcoes_validas[$campo][0];
                foreach ($opcoes_validas[$campo] as $op) { similar_text(mb_strtolower($v,'UTF-8'),mb_strtolower($op,'UTF-8'),$pct); if($pct>$best){$best=$pct;$sug=$op;} }
                $campos_salvos[$campo]=$sug;
            }
        }

        // Salvar no BD
        $cols_permitidas = ['nome_cientifico_completo','nome_cientifico_completo_ref','sinonimos','sinonimos_ref','nome_popular','nome_popular_ref','familia','familia_ref','forma_folha','forma_folha_ref','filotaxia_folha','filotaxia_folha_ref','tipo_folha','tipo_folha_ref','divisao_folha','divisao_folha_ref','paridade_pinnacao','paridade_pinnacao_ref','tamanho_folha','tamanho_folha_ref','textura_folha','textura_folha_ref','margem_folha','margem_folha_ref','venacao_folha','venacao_folha_ref','cor_flores','cor_flores_ref','simetria_floral','simetria_floral_ref','numero_petalas','numero_petalas_ref','disposicao_flores','disposicao_flores_ref','aroma','aroma_ref','tamanho_flor','tamanho_flor_ref','tipo_fruto','tipo_fruto_ref','tamanho_fruto','tamanho_fruto_ref','cor_fruto','cor_fruto_ref','textura_fruto','textura_fruto_ref','dispersao_fruto','dispersao_fruto_ref','aroma_fruto','aroma_fruto_ref','tipo_semente','tipo_semente_ref','tamanho_semente','tamanho_semente_ref','cor_semente','cor_semente_ref','textura_semente','textura_semente_ref','quantidade_sementes','quantidade_sementes_ref','tipo_caule','tipo_caule_ref','textura_caule','textura_caule_ref','cor_caule','cor_caule_ref','forma_caule','forma_caule_ref','modificacao_caule','modificacao_caule_ref','ramificacao_caule','ramificacao_caule_ref','possui_espinhos','possui_espinhos_ref','possui_latex','possui_latex_ref','possui_seiva','possui_seiva_ref','possui_resina','possui_resina_ref','referencias'];
        $bind = ['especie_id' => $especie_id];
        foreach ($cols_permitidas as $col) $bind[$col] = ($campos_salvos[$col]??'')?:null;

        $existe = buscarUm("SELECT id FROM especies_caracteristicas WHERE especie_id = :id", [':id' => $especie_id]);
        if ($existe) {
            $sets = array_map(fn($c) => "`{$c}` = :{$c}", $cols_permitidas);
            $sets[] = 'data_atualizacao = NOW()';
            $pdo->prepare("UPDATE especies_caracteristicas SET ".implode(', ',$sets)." WHERE especie_id = :especie_id")->execute($bind);
        } else {
            $cols = '`especie_id`, ' . implode(', ', array_map(fn($c)=>"`{$c}`", $cols_permitidas));
            $phs  = ':especie_id, :' . implode(', :', $cols_permitidas);
            $pdo->prepare("INSERT INTO especies_caracteristicas ({$cols}) VALUES ({$phs})")->execute($bind);
        }
        $pdo->prepare("UPDATE especies_administrativo SET status='dados_internet', data_ultima_atualizacao=NOW() WHERE id=?")->execute([$especie_id]);
        $etapas[] = 'IA: dados salvos';
    }

    // Regenerar artigo
    try {
        regenerarArtigoEspecie($pdo, $especie_id);
        $etapas[] = 'Artigo: regenerado';
    } catch (Exception $e) {
        $etapas[] = 'Artigo: erro — ' . $e->getMessage();
    }

    echo json_encode(['ok' => true, 'etapas' => $etapas]);
    exit;
}

// ============================================================
// PÁGINA HTML
// ============================================================
$especies = buscarTodos("SELECT id, nome_cientifico, status FROM especies_administrativo ORDER BY nome_cientifico") ?? [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Batch IA — Penomato</title>
<link rel="stylesheet" href="/penomato_mvp/assets/css/estilo.css">
<style>
body { font-family: 'Segoe UI', sans-serif; background: #f0f4f0; padding: 24px 16px; }
.page-wrap { max-width: 860px; margin: 0 auto; }
h1 { color: var(--cor-primaria); margin-bottom: 4px; }
.sub { color: #666; margin-bottom: 24px; font-size: 0.95em; }
table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
th { background: var(--cor-primaria); color: #fff; padding: 12px 14px; text-align: left; font-size: 0.9em; }
td { padding: 10px 14px; border-bottom: 1px solid #eee; font-size: 0.9em; vertical-align: middle; }
tr:last-child td { border-bottom: none; }
.badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 0.8em; font-weight: 600; }
.badge-sem    { background: #fff3cd; color: #856404; }
.badge-dados  { background: #d1fadf; color: #166534; }
.btn-proc { background: var(--cor-primaria); color: #fff; border: none; border-radius: 20px; padding: 6px 16px; font-size: 0.85em; cursor: pointer; }
.btn-proc:disabled { opacity: 0.5; cursor: not-allowed; }
.status-cell { font-size: 0.82em; color: #555; min-width: 180px; }
.ok  { color: #166534; }
.err { color: #dc3545; }
</style>
</head>
<body>
<div class="page-wrap">
  <h1>🤖 Batch IA</h1>
  <p class="sub">Clique em <strong>Processar</strong> para cada espécie. A IA busca os dados, sobrescreve o BD e regenera o artigo.</p>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Nome Científico</th>
        <th>Status</th>
        <th>Ação</th>
        <th>Resultado</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($especies as $i => $e): ?>
      <tr id="row-<?php echo $e['id']; ?>">
        <td><?php echo $i + 1; ?></td>
        <td><?php echo htmlspecialchars($e['nome_cientifico']); ?></td>
        <td>
          <span class="badge <?php echo $e['status']==='sem_dados' ? 'badge-sem' : 'badge-dados'; ?>">
            <?php echo htmlspecialchars($e['status']); ?>
          </span>
        </td>
        <td>
          <button class="btn-proc" onclick="processar(<?php echo $e['id']; ?>, this)">▶ Processar</button>
        </td>
        <td class="status-cell" id="res-<?php echo $e['id']; ?>">—</td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
function processar(id, btn) {
    btn.disabled = true;
    btn.textContent = '⏳ Aguarde…';
    var res = document.getElementById('res-' + id);
    res.textContent = 'Processando…';
    res.className = 'status-cell';

    var fd = new FormData();
    fd.append('acao', 'processar');
    fd.append('especie_id', id);

    fetch('batch_ia.php', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(resp) {
        btn.textContent = '✓ Feito';
        if (resp.ok) {
            res.innerHTML = '<span class="ok">✅ ' + resp.etapas.join(' | ') + '</span>';
        } else {
            btn.disabled = false;
            btn.textContent = '↺ Tentar novamente';
            res.innerHTML = '<span class="err">✗ ' + resp.erro + '</span>';
        }
    })
    .catch(function(err) {
        btn.disabled = false;
        btn.textContent = '↺ Tentar novamente';
        res.innerHTML = '<span class="err">✗ Erro de rede</span>';
    });
}
</script>
</body>
</html>
