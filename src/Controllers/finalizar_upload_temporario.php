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
function artRef(string $refs): string {
    $r = trim($refs);
    return $r !== '' ? '<sup>' . htmlspecialchars($r) . '</sup>' : '';
}
function artVal(?string $v, string $fb = ''): string {
    return trim($v ?? '') !== '' ? trim($v) : $fb;
}
function artListar(array $itens, string $sep = ', '): string {
    $p = [];
    foreach ($itens as $it) {
        $t = trim($it['texto'] ?? '');
        if ($t === '' || strtolower($t) === 'não informado') continue;
        $p[] = $t . artRef($it['ref'] ?? '');
    }
    return implode($sep, $p);
}

function gerarHtmlArtigoRascunho(array $adm, array $c, array $imgs): string {
    $h = fn($s) => htmlspecialchars((string)($s ?? ''));
    ob_start();

    echo '<div class="artigo">';

    // Cabeçalho
    $titulo = artVal($c['nome_cientifico_completo'], $adm['nome_cientifico']);
    echo '<h2 class="art-titulo">' . $h($titulo) . artRef($c['nome_cientifico_completo_ref'] ?? '') . '</h2>';
    if (artVal($c['familia']))
        echo '<p class="art-familia"><strong>Família:</strong> ' . $h($c['familia']) . artRef($c['familia_ref'] ?? '') . '</p>';
    if (!empty($c['sinonimos'])) {
        $sins = implode(', ', array_map(fn($s) => '<em>' . $h(trim($s)) . '</em>', explode(',', $c['sinonimos'])));
        echo '<p class="art-sinonimos"><strong>Sinonímia:</strong> ' . $sins . artRef($c['sinonimos_ref'] ?? '') . '</p>';
    }
    if (!empty($c['nome_popular']))
        echo '<p class="art-nomes"><strong>Nomes populares:</strong> ' . $h($c['nome_popular']) . artRef($c['nome_popular_ref'] ?? '') . '</p>';

    echo '<h3 class="art-secao">Descrição</h3>';

    // Caule
    $cb = artListar([
        ['texto' => artVal($c['tipo_caule']),        'ref' => $c['tipo_caule_ref'] ?? ''],
        ['texto' => artVal($c['estrutura_caule']),   'ref' => $c['estrutura_caule_ref'] ?? ''],
        ['texto' => artVal($c['forma_caule']),       'ref' => $c['forma_caule_ref'] ?? ''],
        ['texto' => artVal($c['diametro_caule'])  ? 'diâmetro ' . strtolower($c['diametro_caule'])  : '', 'ref' => $c['diametro_caule_ref'] ?? ''],
    ]);
    $ex = array_filter([
        artVal($c['cor_caule'])         ? 'coloração '   . strtolower($c['cor_caule'])         . artRef($c['cor_caule_ref'] ?? '')         : '',
        artVal($c['textura_caule'])     ? 'textura '     . strtolower($c['textura_caule'])     . artRef($c['textura_caule_ref'] ?? '')     : '',
        artVal($c['ramificacao_caule']) ? 'ramificação ' . strtolower($c['ramificacao_caule']) . artRef($c['ramificacao_caule_ref'] ?? '') : '',
        artVal($c['modificacao_caule']) ? strtolower($c['modificacao_caule'])                  . artRef($c['modificacao_caule_ref'] ?? '') : '',
    ]);
    $esp = strtolower(artVal($c['possui_espinhos'], 'Não')) === 'não' ? 'desprovido de espinhos' . artRef($c['possui_espinhos_ref'] ?? '') : 'com espinhos' . artRef($c['possui_espinhos_ref'] ?? '');
    $lat = strtolower(artVal($c['possui_latex'],    'Não')) === 'não' ? 'látex ausente'          . artRef($c['possui_latex_ref'] ?? '')    : 'com látex'   . artRef($c['possui_latex_ref'] ?? '');
    $res = strtolower(artVal($c['possui_resina'],   'Não')) === 'não' ? 'resina ausente'         . artRef($c['possui_resina_ref'] ?? '')   : 'com resina'  . artRef($c['possui_resina_ref'] ?? '');
    echo '<p class="art-paragrafo">Caule ' . $cb . ($ex ? ', com ' . implode(', ', $ex) : '') . ', ' . implode(', ', array_filter([$esp, $lat, $res])) . '.</p>';

    // Folhas
    $fl = artListar([
        ['texto' => artVal($c['tipo_folha']),       'ref' => $c['tipo_folha_ref'] ?? ''],
        ['texto' => artVal($c['filotaxia_folha']),  'ref' => $c['filotaxia_folha_ref'] ?? ''],
        ['texto' => artVal($c['forma_folha'])    ? 'de forma '  . strtolower($c['forma_folha'])    : '', 'ref' => $c['forma_folha_ref'] ?? ''],
        ['texto' => artVal($c['textura_folha'])  ? 'textura '   . strtolower($c['textura_folha'])  : '', 'ref' => $c['textura_folha_ref'] ?? ''],
        ['texto' => artVal($c['margem_folha'])   ? 'margem '    . strtolower($c['margem_folha'])   : '', 'ref' => $c['margem_folha_ref'] ?? ''],
        ['texto' => artVal($c['venacao_folha'])  ? 'venação '   . strtolower($c['venacao_folha'])  : '', 'ref' => $c['venacao_folha_ref'] ?? ''],
        ['texto' => artVal($c['tamanho_folha'])  ? 'tamanho '   . strtolower($c['tamanho_folha'])  : '', 'ref' => $c['tamanho_folha_ref'] ?? ''],
    ]);
    if ($fl) echo '<p class="art-paragrafo">Folhas ' . $fl . '.</p>';

    // Flores
    $fr = artListar([
        ['texto' => artVal($c['disposicao_flores']), 'ref' => $c['disposicao_flores_ref'] ?? ''],
        ['texto' => artVal($c['simetria_floral']),   'ref' => $c['simetria_floral_ref'] ?? ''],
        ['texto' => artVal($c['numero_petalas'])  ? 'com '          . strtolower($c['numero_petalas'])  : '', 'ref' => $c['numero_petalas_ref'] ?? ''],
        ['texto' => artVal($c['cor_flores'])      ? 'de coloração ' . strtolower($c['cor_flores'])      : '', 'ref' => $c['cor_flores_ref'] ?? ''],
        ['texto' => artVal($c['tamanho_flor'])    ? 'tamanho '      . strtolower($c['tamanho_flor'])    : '', 'ref' => $c['tamanho_flor_ref'] ?? ''],
        ['texto' => artVal($c['aroma'])           ? 'aroma '        . strtolower($c['aroma'])           : '', 'ref' => $c['aroma_ref'] ?? ''],
    ]);
    if ($fr) echo '<p class="art-paragrafo">Flores ' . $fr . '.</p>';

    // Frutos
    $ft = artVal($c['tipo_fruto']) ? strtolower($c['tipo_fruto']) . artRef($c['tipo_fruto_ref'] ?? '') : '';
    $fp = implode(', ', array_filter([
        artVal($c['tamanho_fruto'])   ? strtolower($c['tamanho_fruto'])                    . artRef($c['tamanho_fruto_ref'] ?? '')   : '',
        artVal($c['cor_fruto'])       ? 'de coloração ' . strtolower($c['cor_fruto'])      . artRef($c['cor_fruto_ref'] ?? '')       : '',
        artVal($c['textura_fruto'])   ? 'textura '      . strtolower($c['textura_fruto'])  . artRef($c['textura_fruto_ref'] ?? '')   : '',
        artVal($c['aroma_fruto'])     ? 'aroma '        . strtolower($c['aroma_fruto'])    . artRef($c['aroma_fruto_ref'] ?? '')     : '',
        artVal($c['dispersao_fruto']) ? 'dispersão '    . strtolower($c['dispersao_fruto']). artRef($c['dispersao_fruto_ref'] ?? '') : '',
    ]));
    if ($ft || $fp) echo '<p class="art-paragrafo">Fruto do tipo ' . $ft . ($fp ? ', ' . $fp : '') . '.</p>';

    // Sementes
    $se = artListar([
        ['texto' => artVal($c['tipo_semente']),       'ref' => $c['tipo_semente_ref'] ?? ''],
        ['texto' => artVal($c['tamanho_semente'])   ? strtolower($c['tamanho_semente'])                          : '', 'ref' => $c['tamanho_semente_ref'] ?? ''],
        ['texto' => artVal($c['cor_semente'])       ? 'de coloração ' . strtolower($c['cor_semente'])            : '', 'ref' => $c['cor_semente_ref'] ?? ''],
        ['texto' => artVal($c['textura_semente'])   ? 'textura '      . strtolower($c['textura_semente'])        : '', 'ref' => $c['textura_semente_ref'] ?? ''],
        ['texto' => artVal($c['quantidade_sementes'])? strtolower($c['quantidade_sementes']) . ' sementes por fruto' : '', 'ref' => $c['quantidade_sementes_ref'] ?? ''],
    ]);
    if ($se) echo '<p class="art-paragrafo">Sementes ' . $se . '.</p>';

    // Prancha fotográfica
    if ($imgs) {
        echo '<h3 class="art-secao">Prancha Fotográfica</h3><div class="art-galeria">';
        foreach ($imgs as $img) {
            echo '<figure class="art-figura">';
            echo '<img src="/penomato_mvp/' . htmlspecialchars($img['caminho_imagem']) . '" alt="' . $h($img['parte_planta']) . '">';
            echo '<figcaption>' . ucfirst($h($img['parte_planta']));
            if (!empty($img['autor_imagem'])) echo ' — ' . $h($img['autor_imagem']);
            if (!empty($img['licenca']))      echo ' (' . $h($img['licenca']) . ')';
            if (!empty($img['fonte_nome'])) {
                $link = !empty($img['fonte_url'])
                    ? '<a href="' . $h($img['fonte_url']) . '" target="_blank">' . $h($img['fonte_nome']) . '</a>'
                    : $h($img['fonte_nome']);
                echo '<br><small>Fonte: ' . $link . '</small>';
            }
            echo '</figcaption></figure>';
        }
        echo '</div>';
    }

    // Referências
    $refs_txt = trim($c['referencias'] ?? '');
    if ($refs_txt !== '') {
        $refs_map = [];
        foreach (preg_split('/(?=\n?\d+\.\s)/', $refs_txt, -1, PREG_SPLIT_NO_EMPTY) as $pt) {
            if (preg_match('/^(\d+)\.\s+(.+)$/s', trim($pt), $m)) $refs_map[(int)$m[1]] = trim($m[2]);
        }
        if ($refs_map) {
            ksort($refs_map);
            echo '<h3 class="art-secao">Referências</h3><ol class="art-refs">';
            foreach ($refs_map as $n => $t) echo '<li id="ref-' . $n . '">' . htmlspecialchars($t) . '</li>';
            echo '</ol>';
        }
    }

    echo '</div><!-- .artigo -->';
    return ob_get_clean();
}

require_once __DIR__ . '/../../config/app.php';
$servidor   = DB_HOST;
$usuario_db = DB_USER;
$senha_db   = DB_PASS;
$banco      = DB_NAME;

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

$dados_temporarios = $_SESSION['importacao_temporaria'];
$especie_id = $dados_temporarios['especie_id'];
$dados_caracteristicas = $dados_temporarios['dados'] ?? [];
$imagens_temporarias = $dados_temporarios['imagens'] ?? [];

// ================================================
// LOG INICIAL - DIAGNÓSTICO
// ================================================
error_log("========== INICIANDO FINALIZAÇÃO ==========");
error_log("Temp ID: " . $temp_id);
error_log("Espécie ID: " . $especie_id);
error_log("Total imagens na sessão: " . count($imagens_temporarias));
error_log("Total campos características: " . count($dados_caracteristicas));

// ================================================
// VERIFICAR SE O USUÁRIO DA SESSÃO É O MESMO DA IMPORTAÇÃO
// ================================================
if (!isset($dados_temporarios['usuario_id']) || $dados_temporarios['usuario_id'] != $id_usuario) {
    error_log("ERRO: Usuário sem permissão");
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode("Você não tem permissão para finalizar esta importação."));
    exit;
}

// ================================================
// VERIFICAR SE HÁ IMAGENS PARA SALVAR
// ================================================
if (empty($imagens_temporarias)) {
    error_log("ERRO: Nenhuma imagem para salvar");
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode("Não há imagens para salvar."));
    exit;
}

// ================================================
// VERIFICAR PARTES OBRIGATÓRIAS
// ================================================
$partes_obrigatorias = ['folha', 'flor', 'fruto', 'caule', 'habito'];
$semente_ausente     = !empty($dados_temporarios['semente_ausente']);

$partes_com_imagem = [];
foreach ($imagens_temporarias as $img) {
    $partes_com_imagem[$img['parte_planta']] = true;
}

$partes_faltando = [];
foreach ($partes_obrigatorias as $parte) {
    if (empty($partes_com_imagem[$parte])) {
        $partes_faltando[] = ucfirst($parte);
    }
}

if (!empty($partes_faltando)) {
    $msg = 'Imagens obrigatórias ausentes: ' . implode(', ', $partes_faltando) . '. Todas as partes obrigatórias precisam de ao menos uma imagem.';
    error_log("ERRO: Partes sem imagem: " . implode(', ', $partes_faltando));
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode($msg));
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
// VERIFICAR PASTAS
// ================================================
$pasta_temp = dirname(dirname(__DIR__)) . '/uploads/temp/' . $temp_id . '/';
$pasta_definitiva = dirname(dirname(__DIR__)) . '/uploads/exsicatas/' . $especie_id . '/';

error_log("Pasta temp: " . $pasta_temp);
error_log("Pasta definitiva: " . $pasta_definitiva);

// Verificar se pasta temporária existe
if (!file_exists($pasta_temp)) {
    error_log("ERRO: Pasta temporária não encontrada");
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode("Pasta temporária não encontrada."));
    exit;
}

// Listar arquivos na pasta temp
$arquivos_temp = scandir($pasta_temp);
error_log("Arquivos na pasta temp: " . implode(", ", $arquivos_temp));

// ================================================
// VERIFICAR ARQUIVOS ANTES DE COMEÇAR
// ================================================
$imagens_validas = [];
foreach ($imagens_temporarias as $i => $img) {
    $nome_arquivo = basename($img['caminho_temporario']);
    $caminho_completo = $pasta_temp . $nome_arquivo;
    
    error_log("Verificando imagem $i - Parte: " . $img['parte_planta']);
    error_log("  Nome: " . $nome_arquivo);
    error_log("  Caminho: " . $caminho_completo);
    error_log("  Existe? " . (file_exists($caminho_completo) ? "SIM" : "NÃO"));
    
    if (file_exists($caminho_completo)) {
        $imagens_validas[] = $img;
    } else {
        error_log("  REMOVIDA: Imagem fantasma - " . $nome_arquivo);
    }
}

$imagens_temporarias = $imagens_validas;
$_SESSION['importacao_temporaria']['imagens'] = $imagens_validas;

error_log("Imagens válidas após verificação: " . count($imagens_temporarias));

// Verificar se ainda há imagens após limpeza
if (empty($imagens_temporarias)) {
    error_log("ERRO: Nenhuma imagem válida encontrada");
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode("Nenhuma imagem válida encontrada para salvar."));
    exit;
}

// ================================================
// CONECTAR AO BANCO
// ================================================
$conexao = new mysqli($servidor, $usuario_db, $senha_db, $banco);

if ($conexao->connect_error) {
    error_log("ERRO: Falha na conexão com banco");
    die("Erro de conexão: " . $conexao->connect_error);
}

$conexao->set_charset("utf8mb4");
error_log("Conectado ao banco com sucesso");

// ================================================
// INICIAR TRANSAÇÃO
// ================================================
$conexao->begin_transaction();
error_log("Transação iniciada");

try {
    
    // ================================================
    // 1. SALVAR DADOS DAS CARACTERÍSTICAS
    // ================================================
    error_log("--- Salvando características ---");
    
    // Verificar se já existem características para esta espécie
    $sql_check = "SELECT id FROM especies_caracteristicas WHERE especie_id = ?";
    $stmt_check = $conexao->prepare($sql_check);
    $stmt_check->bind_param("i", $especie_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    $ja_existe = $stmt_check->num_rows > 0;
    $stmt_check->close();
    
    error_log("Características já existem? " . ($ja_existe ? "SIM" : "NÃO"));
    
    if ($ja_existe) {
        // UPDATE - características já existem
        $sql = "UPDATE especies_caracteristicas SET ";
        $sets = [];
        $tipos = "";
        $valores = [];
        
        foreach ($dados_caracteristicas as $campo => $valor) {
            if ($campo != 'especie_id') {
                $sets[] = "$campo = ?";
                $valores[] = $valor;
                $tipos .= "s";
            }
        }
        
        $sql .= implode(", ", $sets);
        $sql .= " WHERE especie_id = ?";
        $valores[] = $especie_id;
        $tipos .= "i";
        
        error_log("SQL UPDATE: " . $sql);
        
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param($tipos, ...$valores);
        $stmt->execute();
        $stmt->close();
        
        error_log("UPDATE realizado com sucesso");
        
    } else {
        // INSERT - novas características
        $colunas = implode(", ", array_keys($dados_caracteristicas));
        $placeholders = implode(", ", array_fill(0, count($dados_caracteristicas), "?"));
        $sql = "INSERT INTO especies_caracteristicas ($colunas) VALUES ($placeholders)";
        
        $tipos = str_repeat("s", count($dados_caracteristicas));
        $valores = array_values($dados_caracteristicas);
        
        error_log("SQL INSERT: " . $sql);
        
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param($tipos, ...$valores);
        $stmt->execute();
        $stmt->close();
        
        error_log("INSERT realizado com sucesso");
    }
    
    // ================================================
    // 2. ATUALIZAR STATUS DA ESPÉCIE (dados_internet)
    // ================================================
    error_log("--- Atualizando status da espécie ---");
    
    $sql_status = "UPDATE especies_administrativo 
                   SET status = 'dados_internet',
                       autor_dados_internet_id = ?,
                       data_dados_internet = NOW()
                   WHERE id = ?";
    $stmt_status = $conexao->prepare($sql_status);
    $stmt_status->bind_param("ii", $id_usuario, $especie_id);
    $stmt_status->execute();
    $stmt_status->close();
    
    error_log("Status atualizado para 'dados_internet'");
    
    // ================================================
    // 3. SALVAR IMAGENS (MOVER DA PASTA TEMP PARA DEFINITIVA)
    // ================================================
    error_log("--- Processando imagens ---");
    
    // Criar pasta definitiva se não existir
    if (!file_exists($pasta_definitiva)) {
        mkdir($pasta_definitiva, 0777, true);
        error_log("Pasta definitiva criada: " . $pasta_definitiva);
    }
    
    $imagens_salvas = 0;
    $imagens_falhas = [];
    
    foreach ($imagens_temporarias as $i => $img) {
        
        $nome_arquivo = basename($img['caminho_temporario']);
        $caminho_temp = $pasta_temp . $nome_arquivo;
        $caminho_definitivo = $pasta_definitiva . $nome_arquivo;
        $caminho_relativo = 'uploads/exsicatas/' . $especie_id . '/' . $nome_arquivo;
        
        error_log("--- Imagem $i ---");
        error_log("Parte: " . $img['parte_planta']);
        error_log("Nome: " . $nome_arquivo);
        error_log("Temp: " . $caminho_temp);
        error_log("Definitivo: " . $caminho_definitivo);
        error_log("Arquivo existe? " . (file_exists($caminho_temp) ? "SIM" : "NÃO"));
        error_log("Tamanho: " . (file_exists($caminho_temp) ? filesize($caminho_temp) . " bytes" : "N/A"));
        
        // Mover arquivo
        if (rename($caminho_temp, $caminho_definitivo)) {
            error_log("RENAME SUCESSO: Arquivo movido");
            
            // Verificar se o arquivo foi movido corretamente
            if (file_exists($caminho_definitivo)) {
                error_log("Arquivo confirmado no destino: " . $caminho_definitivo);
            } else {
                error_log("ALERTA: Arquivo não encontrado no destino após rename");
            }
            
            // ================================================
            // INSERT NA TABELA especies_imagens
            // ================================================
            $sql_insert = "INSERT INTO especies_imagens (
                especie_id,
                parte_planta,
                caminho_imagem,
                id_usuario_identificador,
                data_upload,
                descricao,
                fonte_nome,
                fonte_url,
                autor_imagem,
                licenca,
                status_validacao
            ) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, 'aprovado')";
            
            $stmt = $conexao->prepare($sql_insert);
            $stmt->bind_param(
                "ississsss",
                $especie_id,
                $img['parte_planta'],
                $caminho_relativo,
                $id_usuario,
                $img['descricao'],
                $img['fonte_nome'],
                $img['fonte_url'],
                $img['autor_imagem'],
                $img['licenca']
            );
            
            if ($stmt->execute()) {
                $imagens_salvas++;
                error_log("INSERT SUCESSO: ID " . $stmt->insert_id);
            } else {
                error_log("INSERT FALHOU: " . $stmt->error);
                $imagens_falhas[] = $nome_arquivo . " (erro banco: " . $stmt->error . ")";
            }
            $stmt->close();
            
        } else {
            $erro_rename = error_get_last();
            error_log("RENAME FALHOU: " . ($erro_rename['message'] ?? 'Erro desconhecido'));
            $imagens_falhas[] = $nome_arquivo . " (erro mover arquivo)";
        }
    }
    
    error_log("--- Resumo imagens ---");
    error_log("Salvas com sucesso: " . $imagens_salvas);
    if (!empty($imagens_falhas)) {
        error_log("Falhas: " . implode(", ", $imagens_falhas));
    }
    
    // ================================================
    // COMMIT - TUDO OK
    // ================================================
    $conexao->commit();
    error_log("COMMIT realizado com sucesso");

    // ================================================
    // 4. GERAR ARTIGO RASCUNHO AUTOMATICAMENTE
    // ================================================
    error_log("--- Gerando artigo rascunho ---");
    try {
        $stmt_c = $conexao->prepare("SELECT * FROM especies_caracteristicas WHERE especie_id = ? LIMIT 1");
        $stmt_c->bind_param("i", $especie_id);
        $stmt_c->execute();
        $c_art = $stmt_c->get_result()->fetch_assoc();
        $stmt_c->close();

        $stmt_a = $conexao->prepare("SELECT * FROM especies_administrativo WHERE id = ? LIMIT 1");
        $stmt_a->bind_param("i", $especie_id);
        $stmt_a->execute();
        $adm_art = $stmt_a->get_result()->fetch_assoc();
        $stmt_a->close();

        $stmt_i = $conexao->prepare("
            SELECT parte_planta, caminho_imagem, autor_imagem, licenca, fonte_nome, fonte_url
            FROM especies_imagens
            WHERE especie_id = ?
            ORDER BY FIELD(parte_planta,'habito','folha','flor','fruto','caule','semente')
        ");
        $stmt_i->bind_param("i", $especie_id);
        $stmt_i->execute();
        $res_i  = $stmt_i->get_result();
        $imgs_art = [];
        while ($img = $res_i->fetch_assoc()) $imgs_art[] = $img;
        $stmt_i->close();

        if ($c_art && $adm_art) {
            $html_art = gerarHtmlArtigoRascunho($adm_art, $c_art, $imgs_art);

            $stmt_art = $conexao->prepare("
                INSERT INTO artigos (especie_id, texto_html, status, gerado_em)
                VALUES (?, ?, 'rascunho', NOW())
                ON DUPLICATE KEY UPDATE texto_html = VALUES(texto_html), atualizado_em = NOW(), status = 'rascunho'
            ");
            $stmt_art->bind_param("is", $especie_id, $html_art);
            $stmt_art->execute();
            $stmt_art->close();
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
        $stmt_nome = $conexao->prepare("SELECT nome_cientifico FROM especies_administrativo WHERE id = ? LIMIT 1");
        $stmt_nome->bind_param("i", $especie_id);
        $stmt_nome->execute();
        $nome_especie = $stmt_nome->get_result()->fetch_row()[0] ?? "ID $especie_id";
        $stmt_nome->close();

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
            $stmt_or = $conexao->prepare("SELECT nome, email FROM usuarios WHERE id = ? AND ativo = 1 LIMIT 1");
            $stmt_or->bind_param("i", $orientador_id);
            $stmt_or->execute();
            $or = $stmt_or->get_result()->fetch_assoc();
            $stmt_or->close();

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
            $res_todos = $conexao->query(
                "SELECT nome, email FROM usuarios
                 WHERE categoria IN ('revisor') AND ativo = 1
                   AND status_verificacao = 'verificado'"
            );
            $enviados = 0;
            while ($esp = $res_todos->fetch_assoc()) {
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
    // 7. LIMPAR PASTA TEMPORÁRIA
    // ================================================
    // ================================================
    if (file_exists($pasta_temp)) {
        $itens = scandir($pasta_temp);
        foreach ($itens as $item) {
            if ($item == '.' || $item == '..') continue;
            $caminho_item = $pasta_temp . $item;
            if (is_file($caminho_item)) {
                unlink($caminho_item);
                error_log("Arquivo temporário removido: " . $item);
            }
        }
        rmdir($pasta_temp);
        error_log("Pasta temporária removida: " . $pasta_temp);
    }
    
    // ================================================
    // 8. LIMPAR SESSÃO TEMPORÁRIA
    // ================================================
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
    $conexao->rollback();
    
    $erro = "Erro ao salvar dados: " . $e->getMessage();
    error_log("ERRO NA TRANSAÇÃO: " . $erro);
    error_log("========== FINALIZAÇÃO FALHOU ==========");
    
    header("Location: ../Views/upload_imagens_internet.php?temp_id=" . urlencode($temp_id) . "&erro=" . urlencode($erro));
    exit;
}

$conexao->close();
ob_end_flush();
exit;
?>