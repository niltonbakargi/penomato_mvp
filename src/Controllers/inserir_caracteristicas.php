<?php
// ================================================
// CONEXÃO E BUSCA NO BANCO - FAZER ANTES DO HTML
// ================================================

// Iniciar buffer de saída para evitar problemas
ob_start();

// Configurações do banco
$servidor = "127.0.0.1";
$usuario = "root";
$senha = "";
$banco = "penomato";

// Variáveis para armazenar as opções
$opcoes_especies = '';
$mensagem_erro = '';

// Tentar conectar e buscar dados
$conexao = mysqli_connect($servidor, $usuario, $senha, $banco);

if (!$conexao) {
    $mensagem_erro = 'Erro: Não foi possível conectar ao banco de dados';
} else {
    mysqli_set_charset($conexao, "utf8mb4");
    
    $sql = "SELECT id, nome_cientifico 
            FROM especies_administrativo 
            WHERE status_caracteristicas = 'sem_dados'
            ORDER BY nome_cientifico";
    
    $resultado = mysqli_query($conexao, $sql);
    
    if (!$resultado) {
        $mensagem_erro = 'Erro na consulta ao banco de dados';
    } else {
        $encontrou_especies = false;
        
        if (mysqli_num_rows($resultado) > 0) {
            $encontrou_especies = true;
            while ($linha = mysqli_fetch_assoc($resultado)) {
                $id_especie = htmlspecialchars($linha['id']);
                $nome_cientifico = htmlspecialchars($linha['nome_cientifico']);
                $opcoes_especies .= "<option value=\"{$id_especie}\">{$nome_cientifico}</option>\n";
            }
        }
        
        mysqli_free_result($resultado);
    }
    
    mysqli_close($conexao);
}

// Limpar buffer
ob_end_clean();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro Completo de Características</title>

  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f9f9f9;
      margin: 0;
      padding: 20px;
    }
    h1 {
      text-align: center;
      color: #333;
    }
    form {
      max-width: 800px;
      margin: 0 auto;
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .input-group {
      display: flex;
      align-items: flex-start;
      margin-bottom: 15px;
    }
    .main-input {
      flex: 2;
    }
    .ref-input {
      flex: 1;
      margin-left: 10px;
    }
    label {
      display: block;
      font-weight: bold;
      margin-top: 10px;
    }
    select, input[type="text"] {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    button.submit-button {
      width: 100%;
      margin-top: 20px;
      background: #28a745;
      color: #fff;
      padding: 15px;
      border: none;
      border-radius: 4px;
      font-size: 18px;
      cursor: pointer;
    }
    button.submit-button:hover {
      background: #218838;
    }
    .error-message {
      color: red;
      font-size: 12px;
      margin-top: 5px;
      padding: 5px;
      background-color: #ffe6e6;
      border-radius: 4px;
    }
  </style>
</head>

<body>

<h1>Cadastro Completo de Características</h1>

<form action="../Views/cadastro_caracteristicas.php" method="post">

  <!-- Espécie / Nome Científico -->
  <div class="input-group">
    <div class="main-input">
      <label for="especie_id">Espécie (Nome Científico)</label>
      <select id="especie_id" name="especie_id" required>
        <option value="" disabled selected>
          Selecione uma espécie para cadastrar características
        </option>
        
        <?php
        // Exibir as opções das espécies (já processadas)
        if (!empty($opcoes_especies)) {
            echo $opcoes_especies;
        } elseif (!empty($mensagem_erro)) {
            echo '<option value="" disabled style="color: red;">' . $mensagem_erro . '</option>';
        } else {
            echo '<option value="" disabled>Nenhuma espécie precisa de cadastro no momento</option>';
        }
        ?>
      </select>
      
      <!-- Mensagem de erro se houver -->
      <?php if (!empty($mensagem_erro)): ?>
        <div class="error-message">
          <?php echo $mensagem_erro; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
    
<!-- Nome Popular -->
<div class="input-group">
  <div class="main-input">
    <label for="nome_popular">Nome Popular:</label>
    <input type="text" id="nome_popular" name="nome_popular" placeholder="Digite o nome popular da espécie">
  </div>
  <div class="ref-input">
    <label for="nome_popular_ref">Referência:</label>
    <input type="text" id="nome_popular_ref" name="nome_popular_ref" placeholder="Nº da referência">
  </div>
</div>

<!-- Família -->
<div class="input-group">
  <div class="main-input">
    <label for="familia">Família:</label>
    <input type="text" id="familia" name="familia" placeholder="Digite a família da espécie">
  </div>
  <div class="ref-input">
    <label for="familia_ref">Referência:</label>
    <input type="text" id="familia_ref" name="familia_ref" placeholder="Nº da referência">
  </div>
</div>

    <!-- Seção: Características da Folha -->
    <h3>Características da Folha</h3>
    
    <!-- Forma -->
    <div class="input-group">
      <div class="main-input">
        <label for="forma_folha">Forma:</label>
        <select id="forma_folha" name="forma_folha">
          <option value="" disabled selected>Selecione forma da Folha</option>
          <option value="Lanceolada">Lanceolada</option>
          <option value="Linear">Linear</option>
          <option value="Elíptica">Elíptica</option>
          <option value="Oval">Oval</option>
          <option value="Orbicular">Orbicular</option>
          <option value="Cordiforme">Cordiforme</option>
          <option value="Espatulada">Espatulada</option>
          <option value="Sagitada">Sagitada</option>
          <option value="Reniforme">Reniforme</option>
          <option value="Obovada">Obovada</option>
          <option value="Trilobada">Trilobada</option>
          <option value="Palmada">Palmada</option>
          <option value="Lobada">Lobada</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="forma_folha_ref">Referência:</label>
        <input type="text" id="forma_folha_ref" name="forma_folha_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Filotaxia -->
    <div class="input-group">
      <div class="main-input">
        <label for="filotaxia_folha">Filotaxia:</label>
        <select id="filotaxia_folha" name="filotaxia_folha">
          <option value="" disabled selected>Selecione filotaxia da Folha</option>
          <option value="Alterna">Alterna</option>
          <option value="Oposta Simples">Oposta Simples</option>
          <option value="Oposta Decussada">Oposta Decussada</option>
          <option value="Verticilada">Verticilada</option>
          <option value="Rosetada">Rosetada</option>
          <option value="Dística">Dística</option>
          <option value="Espiralada">Espiralada</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="filotaxia_folha_ref">Referência:</label>
        <input type="text" id="filotaxia_folha_ref" name="filotaxia_folha_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Tipo -->
    <div class="input-group">
      <div class="main-input">
        <label for="tipo_folha">Tipo:</label>
        <select id="tipo_folha" name="tipo_folha">
          <option value="" disabled selected>Selecione tipo da Folha</option>
          <option value="Simples">Simples</option>
          <option value="Composta pinnada">Composta pinnada</option>
          <option value="Composta bipinada">Composta bipinada</option>
          <option value="Composta tripinada">Composta tripinada</option>
          <option value="Composta tetrapinada">Composta tetrapinada</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="tipo_folha_ref">Referência:</label>
        <input type="text" id="tipo_folha_ref" name="tipo_folha_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Tamanho -->
    <div class="input-group">
      <div class="main-input">
        <label for="tamanho_folha">Tamanho:</label>
        <select id="tamanho_folha" name="tamanho_folha">
          <option value="" disabled selected>Selecione tamanho da Folha</option>
          <option value="Microfilos (< 2 cm)">Microfilos (&lt; 2 cm)</option>
          <option value="Nanofilos (2–7 cm)">Nanofilos (2–7 cm)</option>
          <option value="Mesofilos (7–20 cm)">Mesofilos (7–20 cm)</option>
          <option value="Macrófilos (20–50 cm)">Macrófilos (20–50 cm)</option>
          <option value="Megafilas (> 50 cm)">Megafilas (&gt; 50 cm)</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="tamanho_folha_ref">Referência:</label>
        <input type="text" id="tamanho_folha_ref" name="tamanho_folha_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Textura -->
    <div class="input-group">
      <div class="main-input">
        <label for="textura_folha">Textura:</label>
        <select id="textura_folha" name="textura_folha">
          <option value="" disabled selected>Selecione textura da Folha</option>
          <option value="Coriácea">Coriácea</option>
          <option value="Cartácea">Cartácea</option>
          <option value="Membranácea">Membranácea</option>
          <option value="Súcuba">Súcuba</option>
          <option value="Pilosas">Pilosas</option>
          <option value="Glabra">Glabra</option>
          <option value="Rugosa">Rugosa</option>
          <option value="Cerosa">Cerosa</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="textura_folha_ref">Referência:</label>
        <input type="text" id="textura_folha_ref" name="textura_folha_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Margem -->
    <div class="input-group">
      <div class="main-input">
        <label for="margem_folha">Margem:</label>
        <select id="margem_folha" name="margem_folha">
          <option value="" disabled selected>Selecione margem da Folha</option>
          <option value="Inteira">Inteira</option>
          <option value="Serrada">Serrada</option>
          <option value="Dentada">Dentada</option>
          <option value="Crenada">Crenada</option>
          <option value="Ondulada">Ondulada</option>
          <option value="Lobada">Lobada</option>
          <option value="Partida">Partida</option>
          <option value="Revoluta">Revoluta</option>
          <option value="Involuta">Involuta</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="margem_folha_ref">Referência:</label>
        <input type="text" id="margem_folha_ref" name="margem_folha_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Venação -->
    <div class="input-group">
      <div class="main-input">
        <label for="venacao_folha">Venação:</label>
        <select id="venacao_folha" name="venacao_folha">
          <option value="" disabled selected>Selecione venação da Folha</option>
          <option value="Reticulada Pinnada">Reticulada Pinnada</option>
          <option value="Reticulada Palmada">Reticulada Palmada</option>
          <option value="Paralela">Paralela</option>
          <option value="Peninérvea">Peninérvea</option>
          <option value="Dicotômica">Dicotômica</option>
          <option value="Curvinérvea">Curvinérvea</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="venacao_folha_ref">Referência:</label>
        <input type="text" id="venacao_folha_ref" name="venacao_folha_ref" placeholder="Nº da referência">
      </div>
    </div>

    <!-- Seção: Características das Flores -->
    <h3>Características das Flores</h3>
    
    <!-- Cor das Flores -->
    <div class="input-group">
      <div class="main-input">
        <label for="cor_flores">Cor das Flores:</label>
        <select id="cor_flores" name="cor_flores">
          <option value="" disabled selected>Selecione a cor das flores</option>
          <option value="Brancas">Brancas</option>
          <option value="Amarelas">Amarelas</option>
          <option value="Vermelhas">Vermelhas</option>
          <option value="Rosadas">Rosadas</option>
          <option value="Roxas">Roxas</option>
          <option value="Azuis">Azuis</option>
          <option value="Laranjas">Laranjas</option>
          <option value="Verdes">Verdes</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="cor_flores_ref">Referência:</label>
        <input type="text" id="cor_flores_ref" name="cor_flores_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Simetria Floral -->
    <div class="input-group">
      <div class="main-input">
        <label for="simetria_floral">Simetria Floral:</label>
        <select id="simetria_floral" name="simetria_floral">
          <option value="" disabled selected>Selecione a simetria floral</option>
          <option value="Actinomorfa">Actinomorfa (simetria radial)</option>
          <option value="Zigomorfa">Zigomorfa (simetria bilateral)</option>
          <option value="Assimétrica">Assimétrica (sem simetria)</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="simetria_floral_ref">Referência:</label>
        <input type="text" id="simetria_floral_ref" name="simetria_floral_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Número de Pétalas -->
    <div class="input-group">
      <div class="main-input">
        <label for="numero_petalas">Número de Pétalas:</label>
        <select id="numero_petalas" name="numero_petalas">
          <option value="" disabled selected>Selecione o número de pétalas</option>
          <option value="3_petalas">3 pétalas</option>
          <option value="4_petalas">4 pétalas</option>
          <option value="5_petalas">5 pétalas</option>
          <option value="Muitas_petalas">Muitas pétalas</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="numero_petalas_ref">Referência:</label>
        <input type="text" id="numero_petalas_ref" name="numero_petalas_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Tamanho das Flores -->
    <div class="input-group">
      <div class="main-input">
        <label for="tamanho_flores">Tamanho das Flores:</label>
        <select id="tamanho_flores" name="tamanho_flores">
          <option value="" disabled selected>Selecione o tamanho das flores</option>
          <option value="Pequenas">Pequenas (&lt; 1 cm)</option>
          <option value="Medias">Médias (1–5 cm)</option>
          <option value="Grandes">Grandes (&gt; 5 cm)</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="tamanho_flores_ref">Referência:</label>
        <input type="text" id="tamanho_flores_ref" name="tamanho_flores_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Disposição das Flores -->
    <div class="input-group">
      <div class="main-input">
        <label for="disposicao_flores">Disposição das Flores:</label>
        <select id="disposicao_flores" name="disposicao_flores">
          <option value="" disabled selected>Selecione a disposição das flores</option>
          <option value="Isoladas">Isoladas</option>
          <option value="Inflorescencia">Inflorescência (cacho, espiga, capítulo, umbela)</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="disposicao_flores_ref">Referência:</label>
        <input type="text" id="disposicao_flores_ref" name="disposicao_flores_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Aroma das Flores -->
    <div class="input-group">
      <div class="main-input">
        <label for="aroma">Aroma:</label>
        <select id="aroma" name="aroma">
          <option value="" disabled selected>Selecione o aroma das flores</option>
          <option value="Sem_cheiro">Sem cheiro</option>
          <option value="Aroma_suave">Aroma suave</option>
          <option value="Aroma_forte">Aroma forte</option>
          <option value="Aroma_desagradavel">Aroma desagradável</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="aroma_ref">Referência:</label>
        <input type="text" id="aroma_ref" name="aroma_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Tamanho da Flor -->
    <div class="input-group">
      <div class="main-input">
        <label for="tamanho_flor">Tamanho:</label>
        <select id="tamanho_flor" name="tamanho_flor">
          <option value="" disabled selected>Selecione Tamanho da Flor</option>
          <option value="Pequena">Pequena</option>
          <option value="Média">Média</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="tamanho_flor_ref">Referência:</label>
        <input type="text" id="tamanho_flor_ref" name="tamanho_flor_ref" placeholder="Nº da referência">
      </div>
    </div>

    <!-- Seção: Características dos Frutos -->
    <h3>Características dos Frutos</h3>
    
    <!-- Tipo de Fruto -->
    <div class="input-group">
      <div class="main-input">
        <label for="tipo_fruto">Tipo de Fruto:</label>
        <select id="tipo_fruto" name="tipo_fruto" required>
          <option value="" disabled selected>Selecione o tipo de fruto</option>
          <option value="Baga">Baga</option>
          <option value="Drupa">Drupa</option>
          <option value="Cápsula">Cápsula</option>
          <option value="Folículo">Folículo</option>
          <option value="Legume">Legume</option>
          <option value="Síliqua">Síliqua</option>
          <option value="Aquênio">Aquênio</option>
          <option value="Sâmara">Sâmara</option>
          <option value="Cariopse">Cariopse</option>
          <option value="Pixídio">Pixídio</option>
          <option value="Hespéridio">Hespéridio</option>
          <option value="Pepo">Pepo</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="tipo_fruto_ref">Referência:</label>
        <input type="text" id="tipo_fruto_ref" name="tipo_fruto_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Tamanho do Fruto -->
    <div class="input-group">
      <div class="main-input">
        <label for="tamanho_fruto">Tamanho do Fruto:</label>
        <select id="tamanho_fruto" name="tamanho_fruto" required>
          <option value="" disabled selected>Selecione o tamanho do fruto</option>
          <option value="Pequeno">Pequeno (&lt; 2 cm)</option>
          <option value="Médio">Médio (2–5 cm)</option>
          <option value="Grande">Grande (&gt; 5 cm)</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="tamanho_fruto_ref">Referência:</label>
        <input type="text" id="tamanho_fruto_ref" name="tamanho_fruto_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Cor do Fruto -->
    <div class="input-group">
      <div class="main-input">
        <label for="cor_fruto">Cor do Fruto:</label>
        <select id="cor_fruto" name="cor_fruto" required>
          <option value="" disabled selected>Selecione a cor do fruto</option>
          <option value="Verde">Verde</option>
          <option value="Amarelo">Amarelo</option>
          <option value="Vermelho">Vermelho</option>
          <option value="Roxo">Roxo</option>
          <option value="Laranja">Laranja</option>
          <option value="Marrom">Marrom</option>
          <option value="Preto">Preto</option>
          <option value="Branco">Branco</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="cor_fruto_ref">Referência:</label>
        <input type="text" id="cor_fruto_ref" name="cor_fruto_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Textura do Fruto -->
    <div class="input-group">
      <div class="main-input">
        <label for="textura_fruto">Textura do Fruto:</label>
        <select id="textura_fruto" name="textura_fruto" required>
          <option value="" disabled selected>Selecione a textura do fruto</option>
          <option value="Lisa">Lisa</option>
          <option value="Rugosa">Rugosa</option>
          <option value="Coriácea">Coriácea</option>
          <option value="Peluda">Peluda</option>
          <option value="Espinhosa">Espinhosa</option>
          <option value="Cerosa">Cerosa</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="textura_fruto_ref">Referência:</label>
        <input type="text" id="textura_fruto_ref" name="textura_fruto_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Tipo de Dispersão -->
    <div class="input-group">
      <div class="main-input">
        <label for="dispersao_fruto">Tipo de Dispersão:</label>
        <select id="dispersao_fruto" name="dispersao_fruto" required>
          <option value="" disabled selected>Selecione o tipo de dispersão</option>
          <option value="Zoocórica">Zoocórica (por animais)</option>
          <option value="Anemocórica">Anemocórica (pelo vento)</option>
          <option value="Hidrocórica">Hidrocórica (pela água)</option>
          <option value="Autocórica">Autocórica (pelo próprio fruto)</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="dispersao_fruto_ref">Referência:</label>
        <input type="text" id="dispersao_fruto_ref" name="dispersao_fruto_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Aroma do Fruto -->
    <div class="input-group">
      <div class="main-input">
        <label for="aroma_fruto">Aroma:</label>
        <select id="aroma_fruto" name="aroma_fruto" required>
          <option value="" disabled selected>Selecione o aroma do fruto</option>
          <option value="Sem cheiro">Sem cheiro</option>
          <option value="Aroma suave">Aroma suave</option>
          <option value="Aroma forte">Aroma forte</option>
          <option value="Aroma desagradável">Aroma desagradável</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="aroma_fruto_ref">Referência:</label>
        <input type="text" id="aroma_fruto_ref" name="aroma_fruto_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Seção: Características das Sementes -->
    <h3>Características das Sementes</h3>
    
    <!-- Tipo de Semente -->
    <div class="input-group">
      <div class="main-input">
        <label for="tipo_semente">Tipo de Semente:</label>
        <select id="tipo_semente" name="tipo_semente" required>
          <option value="" disabled selected>Selecione o tipo de semente</option>
          <option value="Alada">Alada</option>
          <option value="Carnosa">Carnosa</option>
          <option value="Dura">Dura</option>
          <option value="Oleosa">Oleosa</option>
          <option value="Peluda">Peluda</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="tipo_semente_ref">Referência:</label>
        <input type="text" id="tipo_semente_ref" name="tipo_semente_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Tamanho da Semente -->
    <div class="input-group">
      <div class="main-input">
        <label for="tamanho_semente">Tamanho da Semente:</label>
        <select id="tamanho_semente" name="tamanho_semente" required>
          <option value="" disabled selected>Selecione o tamanho da semente</option>
          <option value="Pequena">Pequena (&lt; 5 mm)</option>
          <option value="Média">Média (5–10 mm)</option>
          <option value="Grande">Grande (&gt; 10 mm)</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="tamanho_semente_ref">Referência:</label>
        <input type="text" id="tamanho_semente_ref" name="tamanho_semente_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Cor da Semente -->
    <div class="input-group">
      <div class="main-input">
        <label for="cor_semente">Cor da Semente:</label>
        <select id="cor_semente" name="cor_semente" required>
          <option value="" disabled selected>Selecione a cor da semente</option>
          <option value="Preta">Preta</option>
          <option value="Marrom">Marrom</option>
          <option value="Branca">Branca</option>
          <option value="Amarela">Amarela</option>
          <option value="Verde">Verde</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="cor_semente_ref">Referência:</label>
        <input type="text" id="cor_semente_ref" name="cor_semente_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Textura da Semente -->
    <div class="input-group">
      <div class="main-input">
        <label for="textura_semente">Textura da Semente:</label>
        <select id="textura_semente" name="textura_semente" required>
          <option value="" disabled selected>Selecione a textura da semente</option>
          <option value="Lisa">Lisa</option>
          <option value="Rugosa">Rugosa</option>
          <option value="Estriada">Estriada</option>
          <option value="Cerosa">Cerosa</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="textura_semente_ref">Referência:</label>
        <input type="text" id="textura_semente_ref" name="textura_semente_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Quantidade de Sementes por Fruto -->
    <div class="input-group">
      <div class="main-input">
        <label for="quantidade_sementes">Quantidade de Sementes por Fruto:</label>
        <select id="quantidade_sementes" name="quantidade_sementes" required>
          <option value="" disabled selected>Selecione a quantidade de sementes</option>
          <option value="Uma">Uma</option>
          <option value="Poucas">Poucas (2–5)</option>
          <option value="Muitas">Muitas (&gt; 5)</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="quantidade_sementes_ref">Referência:</label>
        <input type="text" id="quantidade_sementes_ref" name="quantidade_sementes_ref" placeholder="Nº da referência">
      </div>
    </div>

    <!-- Seção: Características do Caule -->
    <h3>Características do Caule</h3>
    
    <!-- Tipo de Caule -->
    <div class="input-group">
      <div class="main-input">
        <label for="tipo_caule">Tipo de Caule:</label>
        <select id="tipo_caule" name="tipo_caule" required>
          <option value="" disabled selected>Selecione o tipo de caule</option>
          <option value="Ereto">Ereto</option>
          <option value="Prostrado">Prostrado</option>
          <option value="Rastejante">Rastejante</option>
          <option value="Trepador">Trepador</option>
          <option value="Subterrâneo">Subterrâneo</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="tipo_caule_ref">Referência:</label>
        <input type="text" id="tipo_caule_ref" name="tipo_caule_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Estrutura do Caule -->
    <div class="input-group">
      <div class="main-input">
        <label for="estrutura_caule">Estrutura do Caule:</label>
        <select id="estrutura_caule" name="estrutura_caule" required>
          <option value="" disabled selected>Selecione a estrutura do caule</option>
          <option value="Lenhoso">Lenhoso</option>
          <option value="Herbáceo">Herbáceo</option>
          <option value="Suculento">Suculento</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="estrutura_caule_ref">Referência:</label>
        <input type="text" id="estrutura_caule_ref" name="estrutura_caule_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Textura do Caule -->
    <div class="input-group">
      <div class="main-input">
        <label for="textura_caule">Textura do Caule:</label>
        <select id="textura_caule" name="textura_caule" required>
          <option value="" disabled selected>Selecione a textura do caule</option>
          <option value="Lisa">Lisa</option>
          <option value="Rugosa">Rugosa</option>
          <option value="Sulcada">Sulcada</option>
          <option value="Fissurada">Fissurada</option>
          <option value="Cerosa">Cerosa</option>
          <option value="Espinhosa">Espinhosa</option>
          <option value="Suberosa">Suberosa</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="textura_caule_ref">Referência:</label>
        <input type="text" id="textura_caule_ref" name="textura_caule_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Cor do Caule -->
    <div class="input-group">
      <div class="main-input">
        <label for="cor_caule">Cor do Caule:</label>
        <select id="cor_caule" name="cor_caule" required>
          <option value="" disabled selected>Selecione a cor do caule</option>
          <option value="Marrom">Marrom</option>
          <option value="Verde">Verde</option>
          <option value="Cinza">Cinza</option>
          <option value="Avermelhado">Avermelhado</option>
          <option value="Alaranjado">Alaranjado</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="cor_caule_ref">Referência:</label>
        <input type="text" id="cor_caule_ref" name="cor_caule_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Forma do Caule -->
    <div class="input-group">
      <div class="main-input">
        <label for="forma_caule">Forma do Caule:</label>
        <select id="forma_caule" name="forma_caule" required>
          <option value="" disabled selected>Selecione a forma do caule</option>
          <option value="Cilíndrico">Cilíndrico</option>
          <option value="Quadrangular">Quadrangular</option>
          <option value="Achatado">Achatado</option>
          <option value="Irregular">Irregular</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="forma_caule_ref">Referência:</label>
        <input type="text" id="forma_caule_ref" name="forma_caule_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Modificações do Caule -->
    <div class="input-group">
      <div class="main-input">
        <label for="modificacao_caule">Modificações do Caule:</label>
        <select id="modificacao_caule" name="modificacao_caule" required>
          <option value="" disabled selected>Selecione a modificação do caule</option>
          <option value="Estolão">Estolão</option>
          <option value="Cladódio">Cladódio</option>
          <option value="Rizoma">Rizoma</option>
          <option value="Tubérculo">Tubérculo</option>
          <option value="Espinhos">Espinhos</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="modificacao_caule_ref">Referência:</label>
        <input type="text" id="modificacao_caule_ref" name="modificacao_caule_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Diâmetro do Caule -->
    <div class="input-group">
      <div class="main-input">
        <label for="diametro_caule">Diâmetro do Caule:</label>
        <select id="diametro_caule" name="diametro_caule" required>
          <option value="" disabled selected>Selecione o diâmetro do caule</option>
          <option value="Fino">Fino (&lt; 1 cm)</option>
          <option value="Médio">Médio (1–5 cm)</option>
          <option value="Grosso">Grosso (&gt; 5 cm)</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="diametro_caule_ref">Referência:</label>
        <input type="text" id="diametro_caule_ref" name="diametro_caule_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Ramificação do Caule -->
    <div class="input-group">
      <div class="main-input">
        <label for="ramificacao_caule">Ramificação do Caule:</label>
        <select id="ramificacao_caule" name="ramificacao_caule" required>
          <option value="" disabled selected>Selecione o tipo de ramificação</option>
          <option value="Dicotômica">Dicotômica</option>
          <option value="Monopodial">Monopodial</option>
          <option value="Simpodial">Simpodial</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="ramificacao_caule_ref">Referência:</label>
        <input type="text" id="ramificacao_caule_ref" name="ramificacao_caule_ref" placeholder="Nº da referência">
      </div>
    </div>
        <!-- Seção: Outras Características -->
    <h3>Outras Características</h3>
    
    <!-- Possui Espinhos -->
    <div class="input-group">
      <div class="main-input">
        <label for="possui_espinhos">Possui Espinhos?</label>
        <select id="possui_espinhos" name="possui_espinhos">
          <option value="" selected>Selecione</option>
          <option value="Sim">Sim</option>
          <option value="Não">Não</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="possui_espinhos_ref">Referência:</label>
        <input type="text" id="possui_espinhos_ref" name="possui_espinhos_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Possui Látex -->
    <div class="input-group">
      <div class="main-input">
        <label for="possui_latex">Possui Látex?</label>
        <select id="possui_latex" name="possui_latex">
          <option value="" selected>Selecione</option>
          <option value="Sim">Sim</option>
          <option value="Não">Não</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="possui_latex_ref">Referência:</label>
        <input type="text" id="possui_latex_ref" name="possui_latex_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Possui Seiva -->
    <div class="input-group">
      <div class="main-input">
        <label for="possui_seiva">Possui Seiva?</label>
        <select id="possui_seiva" name="possui_seiva">
          <option value="" selected>Selecione</option>
          <option value="Sim">Sim</option>
          <option value="Não">Não</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="possui_seiva_ref">Referência:</label>
        <input type="text" id="possui_seiva_ref" name="possui_seiva_ref" placeholder="Nº da referência">
      </div>
    </div>
    
    <!-- Possui Resina -->
    <div class="input-group">
      <div class="main-input">
        <label for="possui_resina">Possui Resina?</label>
        <select id="possui_resina" name="possui_resina">
          <option value="" selected>Selecione</option>
          <option value="Sim">Sim</option>
          <option value="Não">Não</option>
        </select>
      </div>
      <div class="ref-input">
        <label for="possui_resina_ref">Referência:</label>
        <input type="text" id="possui_resina_ref" name="possui_resina_ref" placeholder="Nº da referência">
      </div>
    </div>

        <!-- Seção: Referências -->
        <h3>Referências</h3>
        <div class="input-group">
          <div class="main-input">
            <label for="referencias">Adicione as Referências Completas:</label>
            <textarea id="referencias" name="referencias" rows="10" placeholder="Digite aqui as referências utilizadas, uma por linha ou separadas por vírgula..."></textarea>
          </div>
        </div>
        <!-- Botão de Envio -->
    <button type="submit" class="submit-button">Salvar Características</button>
  </form>
</body>
</html>