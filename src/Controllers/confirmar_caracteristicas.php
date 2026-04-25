<?php
// ================================================
// AJAX: retorna dados da espécie como JSON
// (deve vir PRIMEIRO — sem session, sem output extra)
// ================================================
if (isset($_GET['acao']) && $_GET['acao'] === 'dados') {
    require_once __DIR__ . '/../../config/banco_de_dados.php';
    header('Content-Type: application/json; charset=utf-8');
    $especie_id = (int)($_GET['especie_id'] ?? 0);
    if ($especie_id > 0) {
        $dados = buscarUm(
            "SELECT ec.*, ea.status AS status_especie
             FROM especies_caracteristicas ec
             JOIN especies_administrativo ea ON ea.id = ec.especie_id
             WHERE ec.especie_id = :id",
            [':id' => $especie_id]
        );
        echo json_encode($dados ?: null);
    } else {
        echo json_encode(null);
    }
    exit;
}

if (session_status() === PHP_SESSION_NONE) session_start();

// ================================================
// POST: salva características e atualiza status
// ================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../config/banco_de_dados.php';

    $especie_id = (int)($_POST['especie_id'] ?? 0);
    if (!$especie_id) {
        $_SESSION['msg_erro'] = 'Nenhuma espécie selecionada.';
        header('Location: confirmar_caracteristicas.php');
        exit;
    }

    $campos = [
        'nome_cientifico_completo','nome_cientifico_completo_ref',
        'sinonimos','sinonimos_ref',
        'nome_popular','nome_popular_ref',
        'familia','familia_ref',
        'forma_folha','forma_folha_ref',
        'filotaxia_folha','filotaxia_folha_ref',
        'tipo_folha','tipo_folha_ref',
        'tamanho_folha','tamanho_folha_ref',
        'textura_folha','textura_folha_ref',
        'margem_folha','margem_folha_ref',
        'venacao_folha','venacao_folha_ref',
        'cor_flores','cor_flores_ref',
        'simetria_floral','simetria_floral_ref',
        'numero_petalas','numero_petalas_ref',
        'disposicao_flores','disposicao_flores_ref',
        'aroma','aroma_ref',
        'tamanho_flor','tamanho_flor_ref',
        'tipo_fruto','tipo_fruto_ref',
        'tamanho_fruto','tamanho_fruto_ref',
        'cor_fruto','cor_fruto_ref',
        'textura_fruto','textura_fruto_ref',
        'dispersao_fruto','dispersao_fruto_ref',
        'aroma_fruto','aroma_fruto_ref',
        'tipo_semente','tipo_semente_ref',
        'tamanho_semente','tamanho_semente_ref',
        'cor_semente','cor_semente_ref',
        'textura_semente','textura_semente_ref',
        'quantidade_sementes','quantidade_sementes_ref',
        'tipo_caule','tipo_caule_ref',
        'estrutura_caule','estrutura_caule_ref',
        'textura_caule','textura_caule_ref',
        'cor_caule','cor_caule_ref',
        'forma_caule','forma_caule_ref',
        'modificacao_caule','modificacao_caule_ref',
        'diametro_caule','diametro_caule_ref',
        'ramificacao_caule','ramificacao_caule_ref',
        'possui_espinhos','possui_espinhos_ref',
        'possui_latex','possui_latex_ref',
        'possui_seiva','possui_seiva_ref',
        'possui_resina','possui_resina_ref',
        'referencias',
    ];

    $dados = ['especie_id' => $especie_id];
    foreach ($campos as $c) {
        $v = trim($_POST[$c] ?? '');
        $dados[$c] = $v !== '' ? $v : null;
    }

    // ── Validação de ENUMs ────────────────────────────────────────────────────
    $enums_validos = [
        'forma_folha'     => ['Lanceolada','Linear','Elíptica','Ovada','Orbicular','Cordiforme','Espatulada','Sagitada','Reniforme','Obovada','Trilobada','Palmada','Lobada'],
        'filotaxia_folha' => ['Alterna','Oposta Simples','Oposta Decussada','Verticilada','Dística','Espiralada'],
        'tamanho_folha'   => ['Microfilas (< 2 cm)','Nanofilas (2–7 cm)','Mesofilas (7–20 cm)','Macrófilas (20–50 cm)','Megafilas (> 50 cm)'],
        'possui_espinhos' => ['Sim','Não'],
        'possui_latex'    => ['Sim','Não'],
        'possui_seiva'    => ['Sim','Não'],
        'possui_resina'   => ['Sim','Não'],
    ];
    foreach ($enums_validos as $campo => $validos) {
        if ($dados[$campo] !== null && !in_array($dados[$campo], $validos, true)) {
            $dados[$campo] = null;
        }
    }

    // ── Limites de tamanho (alinhados com o schema do banco) ─────────────────
    $limites = [
        // text — capados para evitar abuso
        'sinonimos'    => 5000, 'nome_popular' => 5000,
        'nome_cientifico_completo_ref' => 5000, 'referencias' => 10000,
        // varchar(255)
        'nome_cientifico_completo' => 255, 'familia' => 255,
        'sinonimos_ref' => 255, 'nome_popular_ref' => 255,
        'tipo_folha' => 255, 'textura_folha' => 255, 'margem_folha' => 255,
        'venacao_folha' => 255, 'cor_flores' => 255, 'simetria_floral' => 255,
        'numero_petalas' => 255, 'disposicao_flores' => 255, 'aroma' => 255,
        'tamanho_flor' => 255, 'tipo_fruto' => 255, 'tamanho_fruto' => 255,
        'cor_fruto' => 255, 'textura_fruto' => 255, 'dispersao_fruto' => 255,
        'aroma_fruto' => 255, 'tipo_semente' => 255, 'tamanho_semente' => 255,
        'cor_semente' => 255, 'textura_semente' => 255, 'quantidade_sementes' => 255,
        'tipo_caule' => 255, 'estrutura_caule' => 255, 'textura_caule' => 255,
        'cor_caule' => 255, 'forma_caule' => 255, 'modificacao_caule' => 255,
        'diametro_caule' => 255, 'ramificacao_caule' => 255,
        // varchar(100)
        'familia_ref' => 100, 'forma_folha_ref' => 100, 'filotaxia_folha_ref' => 100,
        'tipo_folha_ref' => 100, 'tamanho_folha_ref' => 100, 'textura_folha_ref' => 100,
        'margem_folha_ref' => 100, 'venacao_folha_ref' => 100, 'cor_flores_ref' => 100,
        'simetria_floral_ref' => 100, 'numero_petalas_ref' => 100,
        'disposicao_flores_ref' => 100, 'aroma_ref' => 100, 'tamanho_flor_ref' => 100,
        'tipo_fruto_ref' => 100, 'tamanho_fruto_ref' => 100, 'cor_fruto_ref' => 100,
        'textura_fruto_ref' => 100, 'dispersao_fruto_ref' => 100, 'aroma_fruto_ref' => 100,
        'tipo_semente_ref' => 100, 'tamanho_semente_ref' => 100, 'cor_semente_ref' => 100,
        'textura_semente_ref' => 100, 'quantidade_sementes_ref' => 100,
        'tipo_caule_ref' => 100, 'estrutura_caule_ref' => 100, 'textura_caule_ref' => 100,
        'cor_caule_ref' => 100, 'forma_caule_ref' => 100, 'modificacao_caule_ref' => 100,
        'diametro_caule_ref' => 100, 'ramificacao_caule_ref' => 100,
        // varchar(50)
        'possui_espinhos_ref' => 50, 'possui_latex_ref' => 50,
        'possui_seiva_ref' => 50, 'possui_resina_ref' => 50,
    ];
    foreach ($limites as $campo => $max) {
        if (isset($dados[$campo]) && $dados[$campo] !== null && mb_strlen($dados[$campo]) > $max) {
            $dados[$campo] = mb_substr($dados[$campo], 0, $max);
        }
    }

    try {
        iniciarTransacao();

        $existe = buscarUm(
            "SELECT id FROM especies_caracteristicas WHERE especie_id = :id",
            [':id' => $especie_id]
        );

        if ($existe) {
            $dados_update = $dados;
            unset($dados_update['especie_id']);
            atualizar(
                'especies_caracteristicas',
                $dados_update,
                'especie_id = :especie_id',
                [':especie_id' => $especie_id]
            );
        } else {
            inserir('especies_caracteristicas', $dados);
        }

        $autor_id = $_SESSION['usuario_id'] ?? null;
        atualizar(
            'especies_administrativo',
            [
                'status'          => 'descrita',
                'data_descrita'   => date('Y-m-d H:i:s'),
                'autor_descrita_id' => $autor_id,
            ],
            'id = :id',
            [':id' => $especie_id]
        );

        // Registrar no histórico para permitir desfazer
        $pdo->prepare("
            INSERT INTO historico_alteracoes
                (especie_id, id_usuario, tabela_afetada, campo_alterado, valor_anterior, valor_novo, tipo_acao)
            VALUES (?, ?, 'especies_administrativo', 'status', 'dados_internet', 'descrita', 'edicao')
        ")->execute([$especie_id, $autor_id]);

        // Avançar artigo para 'confirmado' (apenas se ainda estiver em rascunho)
        $pdo->prepare("
            UPDATE artigos
            SET status = 'confirmado', data_confirmado = NOW(), atualizado_em = NOW()
            WHERE especie_id = ? AND status = 'rascunho'
        ")->execute([$especie_id]);

        confirmarTransacao();

        $_SESSION['msg_sucesso'] = 'Identificação confirmada e dados salvos com sucesso!';
        header('Location: confirmar_caracteristicas.php');
        exit;

    } catch (Exception $e) {
        reverterTransacao();
        error_log('Erro ao confirmar identificação: ' . $e->getMessage());
        $_SESSION['msg_erro'] = 'Erro ao salvar. Tente novamente.';
        header('Location: confirmar_caracteristicas.php');
        exit;
    }
}


// ================================================
// CARREGAR LISTA DE ESPÉCIES
// ================================================
ob_start();
require_once __DIR__ . '/../../config/banco_de_dados.php';

$especies        = [];
$mensagem_erro   = '';

try {
    $especies = buscarTodos(
        "SELECT id, nome_cientifico, status
         FROM especies_administrativo
         WHERE status IN ('sem_dados', 'dados_internet')
         ORDER BY
             CASE status
                 WHEN 'dados_internet' THEN 1
                 WHEN 'sem_dados'      THEN 2
             END,
             nome_cientifico"
    );
} catch (Exception $e) {
    $mensagem_erro = 'Erro ao conectar ao banco de dados.';
}
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
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      background: #f0f4f0;
      margin: 0;
      padding: 24px 16px;
      color: #1e2e1e;
    }

    .page-header {
      max-width: 860px;
      margin: 0 auto 20px;
      background: var(--cor-primaria);
      color: #fff;
      padding: 18px 24px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 10px;
    }
    .page-header h1 { margin: 0; font-size: 1.25em; }
    .back-link {
      color: rgba(255,255,255,0.85);
      text-decoration: none;
      font-size: 0.9em;
    }
    .back-link:hover { color: #fff; text-decoration: underline; }

    form {
      max-width: 860px;
      margin: 0 auto;
      background: #fff;
      padding: 24px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }

    /* ── status badge ── */
    #status-especie {
      display: none;
      margin-top: 8px;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.82em;
      font-weight: 600;
      width: fit-content;
    }
    #status-especie.tem-dados {
      display: block;
      background: #d1fadf;
      color: var(--cor-primaria);
    }
    #status-especie.sem-dados-status {
      display: block;
      background: #fff3cd;
      color: #856404;
    }

    /* ── section headers ── */
    .section-title {
      background: var(--cor-primaria);
      color: #fff;
      padding: 9px 14px;
      margin: 22px 0 12px;
      border-radius: 6px;
      font-size: 0.95em;
      font-weight: 600;
    }

    /* ── field rows ── */
    .input-group {
      display: flex;
      gap: 12px;
      margin-bottom: 14px;
      align-items: flex-start;
    }
    .main-input { flex: 2; }
    .ref-col    { flex: 1; }

    label {
      display: block;
      font-weight: 600;
      font-size: 0.88em;
      margin-bottom: 4px;
      color: #2d3d2d;
    }
    label .subtext {
      font-weight: normal;
      font-size: 0.9em;
      color: #666;
    }
    .info-text {
      font-size: 0.78em;
      color: #666;
      margin-top: 3px;
    }

    select, input[type="text"], textarea {
      width: 100%;
      padding: 7px 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 0.9em;
      transition: border-color 0.15s;
    }
    select:focus, input[type="text"]:focus, textarea:focus {
      border-color: var(--cor-primaria);
      outline: none;
    }
    textarea { resize: vertical; }

    /* ── filled-from-internet highlight ── */
    .auto-filled {
      border-color: var(--cor-primaria) !important;
      background: #f0faf5;
    }

    /* ── reference wrapper ── */
    .ref-wrapper {
      display: flex;
      gap: 5px;
      align-items: center;
    }
    .ref-wrapper input { flex: 1; }
    .confirm-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: 5px;
      border: 2px solid #ccc;
      background: #f8f9fa;
      color: #ccc;
      font-size: 1.1em;
      font-weight: 700;
      flex-shrink: 0;
      cursor: pointer;
      transition: background 0.15s, border-color 0.15s, color 0.15s;
      padding: 0;
      line-height: 1;
      user-select: none;
    }
    .confirm-btn:hover {
      border-color: var(--cor-primaria);
      color: var(--cor-primaria);
    }
    .confirm-btn.confirmed {
      background: var(--cor-primaria);
      border-color: var(--cor-primaria);
      color: #fff;
    }

    /* ── submit ── */
    .submit-btn {
      width: 100%;
      margin-top: 22px;
      background: var(--cor-primaria);
      color: #fff;
      padding: 14px;
      border: none;
      border-radius: 6px;
      font-size: 1.05em;
      font-weight: 700;
      cursor: pointer;
      transition: background 0.15s;
    }
    .submit-btn:hover { background: #084d36; }

    .error-msg {
      color: #c0392b;
      background: #ffe8e8;
      border-radius: 4px;
      padding: 6px 10px;
      font-size: 0.85em;
      margin-top: 6px;
    }

    @media (max-width: 600px) {
      .input-group { flex-direction: column; }
      .ref-col { width: 100%; }
    }
  </style>
</head>
<body>

<div class="page-header">
  <h1>🔍 Confirmar Identificação de Espécie</h1>
  <a class="back-link" href="/penomato_mvp/src/Views/entrar_colaborador.php">← Voltar ao painel</a>
</div>

<?php if (!empty($_SESSION['msg_sucesso'])): ?>
  <div style="max-width:860px;margin:0 auto 16px;padding:12px 18px;background:#d1fadf;color:var(--cor-primaria);border-radius:8px;font-weight:600;">
    ✅ <?php echo htmlspecialchars($_SESSION['msg_sucesso']); unset($_SESSION['msg_sucesso']); ?>
  </div>
<?php elseif (!empty($_SESSION['msg_erro'])): ?>
  <div style="max-width:860px;margin:0 auto 16px;padding:12px 18px;background:#ffe8e8;color:#c0392b;border-radius:8px;font-weight:600;">
    ⚠️ <?php echo htmlspecialchars($_SESSION['msg_erro']); unset($_SESSION['msg_erro']); ?>
  </div>
<?php endif; ?>

<form action="confirmar_caracteristicas.php" method="post">

  <!-- ── SELEÇÃO DE ESPÉCIE ── -->
  <div class="input-group">
    <div class="main-input">
      <label for="especie_id">Espécie (Nome Científico)</label>
      <select id="especie_id" name="especie_id" required>
        <option value="" disabled selected>Selecione uma espécie…</option>

        <?php if (!empty($mensagem_erro)): ?>
          <option value="" disabled style="color:red;"><?php echo htmlspecialchars($mensagem_erro); ?></option>

        <?php elseif (empty($especies)): ?>
          <option value="" disabled>Nenhuma espécie disponível para verificação.</option>

        <?php else: ?>
          <?php
          // Separar os dois grupos para exibir com optgroup
          $com_dados = array_filter($especies, fn($e) => $e['status'] === 'dados_internet');
          $sem_dados  = array_filter($especies, fn($e) => $e['status'] === 'sem_dados');
          ?>

          <?php if ($com_dados): ?>
            <optgroup label="⚡ Com dados da internet (verificar)">
              <?php foreach ($com_dados as $e): ?>
                <option value="<?php echo $e['id']; ?>" data-status="dados_internet">
                  <?php echo htmlspecialchars($e['nome_cientifico']); ?>
                </option>
              <?php endforeach; ?>
            </optgroup>
          <?php endif; ?>

          <?php if ($sem_dados): ?>
            <optgroup label="📋 Sem dados (preencher manualmente)">
              <?php foreach ($sem_dados as $e): ?>
                <option value="<?php echo $e['id']; ?>" data-status="sem_dados">
                  <?php echo htmlspecialchars($e['nome_cientifico']); ?>
                </option>
              <?php endforeach; ?>
            </optgroup>
          <?php endif; ?>
        <?php endif; ?>
      </select>

      <div id="status-especie"></div>
    </div>
  </div>

  <!-- ── NOME CIENTÍFICO COMPLETO ── -->
  <div class="input-group">
    <div class="main-input">
      <label for="nome_cientifico_completo">Nome Científico Completo</label>
      <input type="text" id="nome_cientifico_completo" name="nome_cientifico_completo"
             placeholder="Ex: Acca sellowiana (O. Berg) Burret">
    </div>
    <div class="ref-col">
      <label for="nome_cientifico_completo_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="nome_cientifico_completo_ref" name="nome_cientifico_completo_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <!-- ── SINÔNIMOS ── -->
  <div class="input-group">
    <div class="main-input">
      <label for="sinonimos">Sinônimos <span class="subtext">(nomes científicos antigos)</span></label>
      <input type="text" id="sinonimos" name="sinonimos"
             placeholder="Ex: Acacia colubrina, Mimosa colubrina">
      <div class="info-text">Separados por vírgula</div>
    </div>
    <div class="ref-col">
      <label for="sinonimos_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="sinonimos_ref" name="sinonimos_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <!-- ── NOME POPULAR ── -->
  <div class="input-group">
    <div class="main-input">
      <label for="nome_popular">Nome Popular</label>
      <input type="text" id="nome_popular" name="nome_popular" placeholder="Nome popular da espécie">
    </div>
    <div class="ref-col">
      <label for="nome_popular_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="nome_popular_ref" name="nome_popular_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <!-- ── FAMÍLIA ── -->
  <div class="input-group">
    <div class="main-input">
      <label for="familia">Família</label>
      <input type="text" id="familia" name="familia" placeholder="Família botânica">
    </div>
    <div class="ref-col">
      <label for="familia_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="familia_ref" name="familia_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <!-- ════════════════════════════════ FOLHA ════════════════════════════════ -->
  <div class="section-title">🍃 Características da Folha</div>

  <div class="input-group">
    <div class="main-input">
      <label for="forma_folha">Forma</label>
      <select id="forma_folha" name="forma_folha">
        <option value="" disabled selected>Selecione…</option>
        <option>Lanceolada</option><option>Linear</option><option>Elíptica</option>
        <option>Ovada</option><option>Orbicular</option><option>Cordiforme</option>
        <option>Espatulada</option><option>Sagitada</option><option>Reniforme</option>
        <option>Obovada</option><option>Trilobada</option><option>Palmada</option>
        <option>Lobada</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="forma_folha_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="forma_folha_ref" name="forma_folha_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="filotaxia_folha">Filotaxia</label>
      <select id="filotaxia_folha" name="filotaxia_folha">
        <option value="" disabled selected>Selecione…</option>
        <option>Alterna</option><option>Oposta Simples</option><option>Oposta Decussada</option>
        <option>Verticilada</option><option>Dística</option>
        <option>Espiralada</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="filotaxia_folha_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="filotaxia_folha_ref" name="filotaxia_folha_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="tipo_folha">Tipo</label>
      <select id="tipo_folha" name="tipo_folha">
        <option value="" disabled selected>Selecione…</option>
        <option>Simples</option><option>Composta pinnada</option>
        <option>Composta bipinada</option><option>Composta tripinada</option>
        <option>Composta tetrapinada</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="tipo_folha_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="tipo_folha_ref" name="tipo_folha_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
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
    <div class="ref-col">
      <label for="tamanho_folha_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="tamanho_folha_ref" name="tamanho_folha_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="textura_folha">Textura</label>
      <select id="textura_folha" name="textura_folha">
        <option value="" disabled selected>Selecione…</option>
        <option>Coriácea</option><option>Cartácea</option><option>Membranácea</option>
        <option>Suculenta</option><option>Pilosa</option><option>Glabra</option>
        <option>Rugosa</option><option>Cerosa</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="textura_folha_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="textura_folha_ref" name="textura_folha_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="margem_folha">Margem</label>
      <select id="margem_folha" name="margem_folha">
        <option value="" disabled selected>Selecione…</option>
        <option>Inteira</option><option>Serrada</option><option>Dentada</option>
        <option>Crenada</option><option>Ondulada</option><option>Lobada</option>
        <option>Partida</option><option>Revoluta</option><option>Involuta</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="margem_folha_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="margem_folha_ref" name="margem_folha_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="venacao_folha">Venação</label>
      <select id="venacao_folha" name="venacao_folha">
        <option value="" disabled selected>Selecione…</option>
        <option>Reticulada Pinnada</option><option>Reticulada Palmada</option>
        <option>Paralela</option><option>Peninérvea</option>
        <option>Dicotômica</option><option>Curvinérvea</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="venacao_folha_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="venacao_folha_ref" name="venacao_folha_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <!-- ════════════════════════════════ FLORES ════════════════════════════════ -->
  <div class="section-title">🌸 Características das Flores</div>

  <div class="input-group">
    <div class="main-input">
      <label for="cor_flores">Cor das Flores</label>
      <select id="cor_flores" name="cor_flores">
        <option value="" disabled selected>Selecione…</option>
        <option>Brancas</option><option>Amarelas</option><option>Vermelhas</option>
        <option>Rosadas</option><option>Roxas</option><option>Azuis</option>
        <option>Laranjas</option><option>Verdes</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="cor_flores_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="cor_flores_ref" name="cor_flores_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="simetria_floral">Simetria Floral</label>
      <select id="simetria_floral" name="simetria_floral">
        <option value="" disabled selected>Selecione…</option>
        <option value="Actinomorfa">Actinomorfa (simetria radial)</option>
        <option value="Zigomorfa">Zigomorfa (simetria bilateral)</option>
        <option value="Assimétrica">Assimétrica (sem simetria)</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="simetria_floral_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="simetria_floral_ref" name="simetria_floral_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="numero_petalas">Número de Pétalas</label>
      <select id="numero_petalas" name="numero_petalas">
        <option value="" disabled selected>Selecione…</option>
        <option value="3 pétalas">3 pétalas</option>
        <option value="4 pétalas">4 pétalas</option>
        <option value="5 pétalas">5 pétalas</option>
        <option value="Muitas pétalas">Muitas pétalas</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="numero_petalas_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="numero_petalas_ref" name="numero_petalas_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="disposicao_flores">Disposição das Flores</label>
      <select id="disposicao_flores" name="disposicao_flores">
        <option value="" disabled selected>Selecione…</option>
        <option>Isoladas</option>
        <option value="Inflorescência">Inflorescência (cacho, espiga, capítulo, umbela)</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="disposicao_flores_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="disposicao_flores_ref" name="disposicao_flores_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="aroma">Aroma das Flores</label>
      <select id="aroma" name="aroma">
        <option value="" disabled selected>Selecione…</option>
        <option value="Sem cheiro">Sem cheiro</option>
        <option value="Aroma suave">Aroma suave</option>
        <option value="Aroma forte">Aroma forte</option>
        <option value="Aroma desagradável">Aroma desagradável</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="aroma_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="aroma_ref" name="aroma_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="tamanho_flor">Tamanho da Flor</label>
      <select id="tamanho_flor" name="tamanho_flor">
        <option value="" disabled selected>Selecione…</option>
        <option>Pequena</option><option>Média</option><option>Grande</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="tamanho_flor_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="tamanho_flor_ref" name="tamanho_flor_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <!-- ════════════════════════════════ FRUTOS ════════════════════════════════ -->
  <div class="section-title">🍎 Características dos Frutos</div>

  <div class="input-group">
    <div class="main-input">
      <label for="tipo_fruto">Tipo de Fruto</label>
      <select id="tipo_fruto" name="tipo_fruto">
        <option value="" disabled selected>Selecione…</option>
        <option>Baga</option><option>Drupa</option><option>Cápsula</option>
        <option>Folículo</option><option>Legume</option><option>Síliqua</option>
        <option>Aquênio</option><option>Sâmara</option><option>Cariopse</option>
        <option>Pixídio</option><option>Hespéridio</option><option>Pepo</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="tipo_fruto_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="tipo_fruto_ref" name="tipo_fruto_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="tamanho_fruto">Tamanho do Fruto</label>
      <select id="tamanho_fruto" name="tamanho_fruto">
        <option value="" disabled selected>Selecione…</option>
        <option value="Pequeno">Pequeno (&lt; 2 cm)</option>
        <option value="Médio">Médio (2–5 cm)</option>
        <option value="Grande">Grande (&gt; 5 cm)</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="tamanho_fruto_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="tamanho_fruto_ref" name="tamanho_fruto_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="cor_fruto">Cor do Fruto</label>
      <select id="cor_fruto" name="cor_fruto">
        <option value="" disabled selected>Selecione…</option>
        <option>Verde</option><option>Amarelo</option><option>Vermelho</option>
        <option>Roxo</option><option>Laranja</option><option>Marrom</option>
        <option>Preto</option><option>Branco</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="cor_fruto_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="cor_fruto_ref" name="cor_fruto_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="textura_fruto">Textura do Fruto</label>
      <select id="textura_fruto" name="textura_fruto">
        <option value="" disabled selected>Selecione…</option>
        <option>Lisa</option><option>Rugosa</option><option>Coriácea</option>
        <option>Peluda</option><option>Espinhosa</option><option>Cerosa</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="textura_fruto_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="textura_fruto_ref" name="textura_fruto_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="dispersao_fruto">Tipo de Dispersão</label>
      <select id="dispersao_fruto" name="dispersao_fruto">
        <option value="" disabled selected>Selecione…</option>
        <option value="Zoocórica">Zoocórica (por animais)</option>
        <option value="Anemocórica">Anemocórica (pelo vento)</option>
        <option value="Hidrocórica">Hidrocórica (pela água)</option>
        <option value="Autocórica">Autocórica (pelo próprio fruto)</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="dispersao_fruto_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="dispersao_fruto_ref" name="dispersao_fruto_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="aroma_fruto">Aroma do Fruto</label>
      <select id="aroma_fruto" name="aroma_fruto">
        <option value="" disabled selected>Selecione…</option>
        <option value="Sem cheiro">Sem cheiro</option>
        <option value="Aroma suave">Aroma suave</option>
        <option value="Aroma forte">Aroma forte</option>
        <option value="Aroma desagradável">Aroma desagradável</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="aroma_fruto_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="aroma_fruto_ref" name="aroma_fruto_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <!-- ════════════════════════════════ SEMENTES ════════════════════════════════ -->
  <div class="section-title">🌱 Características das Sementes</div>

  <div class="input-group">
    <div class="main-input">
      <label for="tipo_semente">Tipo de Semente</label>
      <select id="tipo_semente" name="tipo_semente">
        <option value="" disabled selected>Selecione…</option>
        <option>Alada</option><option>Carnosa</option><option>Dura</option>
        <option>Oleosa</option><option>Peluda</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="tipo_semente_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="tipo_semente_ref" name="tipo_semente_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="tamanho_semente">Tamanho da Semente</label>
      <select id="tamanho_semente" name="tamanho_semente">
        <option value="" disabled selected>Selecione…</option>
        <option value="Pequena">Pequena (&lt; 5 mm)</option>
        <option value="Média">Média (5–10 mm)</option>
        <option value="Grande">Grande (&gt; 10 mm)</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="tamanho_semente_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="tamanho_semente_ref" name="tamanho_semente_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="cor_semente">Cor da Semente</label>
      <select id="cor_semente" name="cor_semente">
        <option value="" disabled selected>Selecione…</option>
        <option>Preta</option><option>Marrom</option><option>Branca</option>
        <option>Amarela</option><option>Verde</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="cor_semente_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="cor_semente_ref" name="cor_semente_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="textura_semente">Textura da Semente</label>
      <select id="textura_semente" name="textura_semente">
        <option value="" disabled selected>Selecione…</option>
        <option>Lisa</option><option>Rugosa</option><option>Estriada</option><option>Cerosa</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="textura_semente_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="textura_semente_ref" name="textura_semente_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="quantidade_sementes">Quantidade de Sementes por Fruto</label>
      <select id="quantidade_sementes" name="quantidade_sementes">
        <option value="" disabled selected>Selecione…</option>
        <option>Uma</option>
        <option value="Poucas">Poucas (2–5)</option>
        <option value="Muitas">Muitas (&gt; 5)</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="quantidade_sementes_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="quantidade_sementes_ref" name="quantidade_sementes_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <!-- ════════════════════════════════ CAULE ════════════════════════════════ -->
  <div class="section-title">🌿 Características do Caule</div>

  <div class="input-group">
    <div class="main-input">
      <label for="tipo_caule">Tipo de Caule</label>
      <select id="tipo_caule" name="tipo_caule">
        <option value="" disabled selected>Selecione…</option>
        <option>Ereto</option><option>Prostrado</option><option>Rastejante</option>
        <option>Trepador</option><option>Subterrâneo</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="tipo_caule_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="tipo_caule_ref" name="tipo_caule_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="estrutura_caule">Estrutura do Caule</label>
      <select id="estrutura_caule" name="estrutura_caule">
        <option value="" disabled selected>Selecione…</option>
        <option>Lenhoso</option><option>Herbáceo</option><option>Suculento</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="estrutura_caule_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="estrutura_caule_ref" name="estrutura_caule_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="textura_caule">Textura do Caule</label>
      <select id="textura_caule" name="textura_caule">
        <option value="" disabled selected>Selecione…</option>
        <option>Lisa</option><option>Rugosa</option><option>Sulcada</option>
        <option>Fissurada</option><option>Cerosa</option><option>Espinhosa</option>
        <option>Suberosa</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="textura_caule_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="textura_caule_ref" name="textura_caule_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="cor_caule">Cor do Caule</label>
      <select id="cor_caule" name="cor_caule">
        <option value="" disabled selected>Selecione…</option>
        <option>Marrom</option><option>Verde</option><option>Cinza</option>
        <option>Avermelhado</option><option>Alaranjado</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="cor_caule_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="cor_caule_ref" name="cor_caule_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="forma_caule">Forma do Caule</label>
      <select id="forma_caule" name="forma_caule">
        <option value="" disabled selected>Selecione…</option>
        <option>Cilíndrico</option><option>Quadrangular</option>
        <option>Achatado</option><option>Irregular</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="forma_caule_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="forma_caule_ref" name="forma_caule_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="modificacao_caule">Modificações do Caule</label>
      <select id="modificacao_caule" name="modificacao_caule">
        <option value="" disabled selected>Selecione…</option>
        <option>Estolão</option><option>Cladódio</option><option>Rizoma</option>
        <option>Tubérculo</option><option>Espinhos</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="modificacao_caule_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="modificacao_caule_ref" name="modificacao_caule_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="diametro_caule">Diâmetro do Caule</label>
      <select id="diametro_caule" name="diametro_caule">
        <option value="" disabled selected>Selecione…</option>
        <option value="Fino">Fino (&lt; 1 cm)</option>
        <option value="Médio">Médio (1–5 cm)</option>
        <option value="Grosso">Grosso (&gt; 5 cm)</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="diametro_caule_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="diametro_caule_ref" name="diametro_caule_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="ramificacao_caule">Ramificação do Caule</label>
      <select id="ramificacao_caule" name="ramificacao_caule">
        <option value="" disabled selected>Selecione…</option>
        <option>Dicotômica</option><option>Monopodial</option><option>Simpodial</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="ramificacao_caule_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="ramificacao_caule_ref" name="ramificacao_caule_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <!-- ════════════════════════════════ OUTRAS ════════════════════════════════ -->
  <div class="section-title">⚡ Outras Características</div>

  <div class="input-group">
    <div class="main-input">
      <label for="possui_espinhos">Possui Espinhos?</label>
      <select id="possui_espinhos" name="possui_espinhos">
        <option value="">Selecione…</option>
        <option>Sim</option><option>Não</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="possui_espinhos_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="possui_espinhos_ref" name="possui_espinhos_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="possui_latex">Possui Látex?</label>
      <select id="possui_latex" name="possui_latex">
        <option value="">Selecione…</option>
        <option>Sim</option><option>Não</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="possui_latex_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="possui_latex_ref" name="possui_latex_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="possui_seiva">Possui Seiva?</label>
      <select id="possui_seiva" name="possui_seiva">
        <option value="">Selecione…</option>
        <option>Sim</option><option>Não</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="possui_seiva_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="possui_seiva_ref" name="possui_seiva_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <div class="input-group">
    <div class="main-input">
      <label for="possui_resina">Possui Resina?</label>
      <select id="possui_resina" name="possui_resina">
        <option value="">Selecione…</option>
        <option>Sim</option><option>Não</option>
      </select>
    </div>
    <div class="ref-col">
      <label for="possui_resina_ref">Referência</label>
      <div class="ref-wrapper">
        <input type="text" id="possui_resina_ref" name="possui_resina_ref" placeholder="URL ou nº">
        <button type="button" class="confirm-btn" title="Marcar como confirmado">✓</button>
      </div>
    </div>
  </div>

  <!-- ════════════════════════════════ REFERÊNCIAS ════════════════════════════════ -->
  <div class="section-title">📚 Referências</div>
  <div class="input-group">
    <div class="main-input">
      <label for="referencias">Lista Completa de Referências</label>
      <textarea id="referencias" name="referencias" rows="8"
        placeholder="1. Lorenzi, H. (2002). Árvores Brasileiras Vol.1&#10;2. https://floradobrasil.jbrj.gov.br/&#10;3. Coleta de campo - 2025"></textarea>
      <div class="info-text">Use [1], [2]… nos campos de referência para referenciar esta lista</div>
    </div>
  </div>

  <div id="aviso-confirmacao" style="text-align:center;margin-top:18px;font-size:0.88em;color:#856404;display:none;">
    ⚠️ Marque todos os campos como confirmados (✓) para habilitar o envio.
  </div>
  <button type="submit" id="btn-confirmar" class="submit-btn" disabled
          style="opacity:0.45;cursor:not-allowed;margin-top:8px;">
    ✅ Confirmar Identificação
  </button>
</form>

<script>
// ── Habilita/desabilita o botão de envio ──────────────────────────────────
function verificarProgresso() {
  const total     = document.querySelectorAll('.confirm-btn').length;
  const marcados  = document.querySelectorAll('.confirm-btn.confirmed').length;
  const btn       = document.getElementById('btn-confirmar');
  const aviso     = document.getElementById('aviso-confirmacao');
  const completo  = (marcados === total) && total > 0;

  btn.disabled      = !completo;
  btn.style.opacity = completo ? '1' : '0.45';
  btn.style.cursor  = completo ? 'pointer' : 'not-allowed';
  aviso.style.display = (!completo && total > 0) ? 'block' : 'none';
}

// ── Toggle de confirmação: clique alterna entre confirmado/não confirmado ─
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.confirm-btn');
  if (btn) {
    btn.classList.toggle('confirmed');
    verificarProgresso();
  }
});

// ── Preenche um campo (select ou input) com valor ─────────────────────────
function preencherCampo(id, valor) {
  const el = document.getElementById(id);
  if (!el || valor === null || valor === undefined || valor === '') return;
  const v = String(valor).trim();
  if (!v) return;

  if (el.tagName === 'SELECT') {
    const opts = Array.from(el.options);
    // 1. value exato
    let match = opts.find(o => o.value === v);
    // 2. texto exato
    if (!match) match = opts.find(o => o.text.trim() === v);
    // 3. value começa com o valor (para opções como "Actinomorfa (simetria radial)")
    if (!match) match = opts.find(o => o.value.startsWith(v) || v.startsWith(o.value));
    // 4. texto começa com o valor
    if (!match) match = opts.find(o => o.text.trim().startsWith(v) || v.startsWith(o.text.trim()));
    if (match) {
      el.value = match.value;
      el.classList.add('auto-filled');
    }
  } else {
    el.value = v;
    el.classList.add('auto-filled');
  }
}

// ── Limpa todos os campos do formulário ────────────────────────────────────
function limparFormulario() {
  const form = document.querySelector('form');
  // Inputs e textareas
  form.querySelectorAll('input[type="text"], textarea').forEach(el => {
    el.value = '';
    el.classList.remove('auto-filled');
  });
  // Selects (exceto especie_id)
  form.querySelectorAll('select:not(#especie_id)').forEach(el => {
    el.value = '';
    el.classList.remove('auto-filled');
    // Reposiciona para o placeholder (disabled selected)
    if (el.options[0] && el.options[0].disabled) el.selectedIndex = 0;
  });
  // Reset confirm buttons
  document.querySelectorAll('.confirm-btn').forEach(btn => btn.classList.remove('confirmed'));
  verificarProgresso();
}

// ── Handler do dropdown de espécie ────────────────────────────────────────
document.getElementById('especie_id').addEventListener('change', function () {
  const especieId = this.value;
  const badge     = document.getElementById('status-especie');

  limparFormulario();

  badge.textContent = '🔄 Buscando dados…';
  badge.className   = 'tem-dados';

  fetch('confirmar_caracteristicas.php?acao=dados&especie_id=' + encodeURIComponent(especieId))
    .then(function(r) { return r.json(); })
    .then(function(dados) {
      if (!dados) {
        badge.textContent = '📋 Nenhum dado cadastrado para esta espécie. Preencha manualmente.';
        badge.className   = 'sem-dados-status';
        return;
      }

      // Preencher campos principais
      [
        'nome_cientifico_completo','sinonimos','nome_popular','familia',
        'forma_folha','filotaxia_folha','tipo_folha','tamanho_folha',
        'textura_folha','margem_folha','venacao_folha',
        'cor_flores','simetria_floral','numero_petalas',
        'disposicao_flores','aroma','tamanho_flor',
        'tipo_fruto','tamanho_fruto','cor_fruto','textura_fruto',
        'dispersao_fruto','aroma_fruto',
        'tipo_semente','tamanho_semente','cor_semente',
        'textura_semente','quantidade_sementes',
        'tipo_caule','estrutura_caule','textura_caule','cor_caule',
        'forma_caule','modificacao_caule','diametro_caule','ramificacao_caule',
        'possui_espinhos','possui_latex','possui_seiva','possui_resina',
        'referencias'
      ].forEach(function(c) { preencherCampo(c, dados[c]); });

      // Preencher refs
      [
        'nome_cientifico_completo_ref','sinonimos_ref','nome_popular_ref','familia_ref',
        'forma_folha_ref','filotaxia_folha_ref','tipo_folha_ref','tamanho_folha_ref',
        'textura_folha_ref','margem_folha_ref','venacao_folha_ref',
        'cor_flores_ref','simetria_floral_ref','numero_petalas_ref',
        'disposicao_flores_ref','aroma_ref','tamanho_flor_ref',
        'tipo_fruto_ref','tamanho_fruto_ref','cor_fruto_ref','textura_fruto_ref',
        'dispersao_fruto_ref','aroma_fruto_ref',
        'tipo_semente_ref','tamanho_semente_ref','cor_semente_ref',
        'textura_semente_ref','quantidade_sementes_ref',
        'tipo_caule_ref','estrutura_caule_ref','textura_caule_ref','cor_caule_ref',
        'forma_caule_ref','modificacao_caule_ref','diametro_caule_ref','ramificacao_caule_ref',
        'possui_espinhos_ref','possui_latex_ref','possui_seiva_ref','possui_resina_ref'
      ].forEach(function(refId) { preencherCampo(refId, dados[refId]); });

      badge.textContent = '✅ Formulário preenchido com dados da internet. Verifique cada campo.';
      badge.className   = 'tem-dados';
    })
    .catch(function(err) {
      console.error('Erro AJAX:', err);
      badge.textContent = '⚠️ Erro ao buscar dados. Preencha manualmente.';
      badge.className   = 'sem-dados-status';
    });
});
</script>
</body>
</html>
