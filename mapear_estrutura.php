<?php
// mapear_estrutura.php
// Coloque este arquivo na RAIZ do projeto (penomato_mvp/)

class MapeadorEstrutura {
    private $diretorioRaiz;
    private $resultado = [];
    private $estatisticas = [
        'total_pastas' => 0,
        'total_arquivos' => 0,
        'total_tamanho' => 0,
        'extensoes' => [],
        'tipos_arquivo' => []
    ];
    
    public function __construct($diretorioRaiz = __DIR__) {
        $this->diretorioRaiz = $diretorioRaiz;
    }
    
    public function mapear() {
        echo "🔍 MAPEANDO ESTRUTURA DO PROJETO\n";
        echo "==================================\n\n";
        echo "📁 Raiz: " . $this->diretorioRaiz . "\n\n";
        
        $this->varreduraRecursiva($this->diretorioRaiz);
        $this->exibirArvore();
        $this->exibirEstatisticas();
        $this->gerarRelatorioJSON();
        $this->gerarRelatorioCSV();
        $this->sugestoesNacionalizacao();
    }
    
    private function varreduraRecursiva($diretorio, $nivel = 0) {
        $itens = scandir($diretorio);
        $this->estatisticas['total_pastas']++;
        
        foreach ($itens as $item) {
            if ($item == '.' || $item == '..' || $item == 'mapear_estrutura.php') {
                continue;
            }
            
            $caminho = $diretorio . DIRECTORY_SEPARATOR . $item;
            $relativo = str_replace($this->diretorioRaiz . DIRECTORY_SEPARATOR, '', $caminho);
            
            if (is_dir($caminho)) {
                $this->resultado[] = [
                    'tipo' => '📁',
                    'nivel' => $nivel,
                    'nome' => $item,
                    'caminho' => $relativo,
                    'tamanho' => $this->formatarTamanho($this->tamanhoPasta($caminho)),
                    'modificado' => date('d/m/Y H:i', filemtime($caminho))
                ];
                $this->varreduraRecursiva($caminho, $nivel + 1);
            } else {
                $tamanho = filesize($caminho);
                $extensao = pathinfo($item, PATHINFO_EXTENSION);
                
                $this->estatisticas['total_arquivos']++;
                $this->estatisticas['total_tamanho'] += $tamanho;
                
                if (!isset($this->estatisticas['extensoes'][$extensao])) {
                    $this->estatisticas['extensoes'][$extensao] = 0;
                }
                $this->estatisticas['extensoes'][$extensao]++;
                
                $tipo = $this->identificarTipoArquivo($extensao);
                if (!isset($this->estatisticas['tipos_arquivo'][$tipo])) {
                    $this->estatisticas['tipos_arquivo'][$tipo] = 0;
                }
                $this->estatisticas['tipos_arquivo'][$tipo]++;
                
                $this->resultado[] = [
                    'tipo' => '📄',
                    'nivel' => $nivel,
                    'nome' => $item,
                    'caminho' => $relativo,
                    'extensao' => $extensao,
                    'tamanho' => $this->formatarTamanho($tamanho),
                    'modificado' => date('d/m/Y H:i', filemtime($caminho))
                ];
            }
        }
    }
    
    private function identificarTipoArquivo($extensao) {
        $mapa = [
            'php' => 'Código PHP',
            'html' => 'HTML',
            'css' => 'CSS',
            'js' => 'JavaScript',
            'json' => 'JSON',
            'sql' => 'SQL',
            'txt' => 'Texto',
            'md' => 'Markdown',
            'jpg' => 'Imagem',
            'jpeg' => 'Imagem',
            'png' => 'Imagem',
            'gif' => 'Imagem',
            'svg' => 'Imagem',
            'pdf' => 'PDF',
            'zip' => 'Compactado',
            'rar' => 'Compactado',
            'log' => 'Log'
        ];
        
        return isset($mapa[$extensao]) ? $mapa[$extensao] : 'Outro';
    }
    
    private function tamanhoPasta($pasta) {
        $tamanho = 0;
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pasta)) as $arquivo) {
            if ($arquivo->isFile()) {
                $tamanho += $arquivo->getSize();
            }
        }
        return $tamanho;
    }
    
    private function formatarTamanho($bytes) {
        $unidades = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($unidades) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $unidades[$i];
    }
    
    private function exibirArvore() {
        echo "📂 ESTRUTURA DE PASTAS E ARQUIVOS\n";
        echo "----------------------------------\n\n";
        
        $ultimoNivel = -1;
        foreach ($this->resultado as $item) {
            if ($item['nivel'] > $ultimoNivel) {
                // Entrando em subpasta
            } elseif ($item['nivel'] < $ultimoNivel) {
                // Saindo de subpasta
            }
            
            $indent = str_repeat('  ', $item['nivel']);
            $icone = $item['tipo'];
            $info = isset($item['extensao']) ? " [{$item['extensao']}]" : "";
            
            echo $indent . $icone . " " . $item['nome'] . $info . "\n";
            echo $indent . "   📍 " . $item['caminho'] . "\n";
            echo $indent . "   📦 " . $item['tamanho'] . "  🕒 " . $item['modificado'] . "\n\n";
            
            $ultimoNivel = $item['nivel'];
        }
    }
    
    private function exibirEstatisticas() {
        echo "\n📊 ESTATÍSTICAS GERAIS\n";
        echo "----------------------\n";
        echo "📁 Pastas: " . $this->estatisticas['total_pastas'] . "\n";
        echo "📄 Arquivos: " . $this->estatisticas['total_arquivos'] . "\n";
        echo "💾 Tamanho total: " . $this->formatarTamanho($this->estatisticas['total_tamanho']) . "\n\n";
        
        echo "🔤 ARQUIVOS POR EXTENSÃO:\n";
        arsort($this->estatisticas['extensoes']);
        foreach ($this->estatisticas['extensoes'] as $ext => $qtd) {
            $extVisivel = $ext ?: '(sem extensão)';
            echo "   .$extVisivel: $qtd arquivos\n";
        }
        
        echo "\n📋 ARQUIVOS POR TIPO:\n";
        foreach ($this->estatisticas['tipos_arquivo'] as $tipo => $qtd) {
            echo "   $tipo: $qtd\n";
        }
    }
    
    private function gerarRelatorioJSON() {
        $relatorio = [
            'data_mapeamento' => date('Y-m-d H:i:s'),
            'diretorio_raiz' => $this->diretorioRaiz,
            'estatisticas' => $this->estatisticas,
            'estrutura' => $this->resultado
        ];
        
        $json = json_encode($relatorio, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents('mapeamento_estrutura.json', $json);
        echo "\n📄 Relatório JSON gerado: mapeamento_estrutura.json\n";
    }
    
    private function gerarRelatorioCSV() {
        $csv = "Tipo,Nível,Nome,Caminho,Extensão,Tamanho,Modificado\n";
        
        foreach ($this->resultado as $item) {
            $linha = [
                $item['tipo'] == '📁' ? 'PASTA' : 'ARQUIVO',
                $item['nivel'],
                $item['nome'],
                $item['caminho'],
                isset($item['extensao']) ? $item['extensao'] : '-',
                $item['tamanho'],
                $item['modificado']
            ];
            $csv .= implode(',', $linha) . "\n";
        }
        
        file_put_contents('mapeamento_estrutura.csv', $csv);
        echo "📄 Relatório CSV gerado: mapeamento_estrutura.csv\n";
    }
    
    private function sugestoesNacionalizacao() {
        echo "\n🎯 SUGESTÕES PARA NACIONALIZAÇÃO\n";
        echo "--------------------------------\n";
        
        // Identificar arquivos com nomes em inglês
        $arquivosPHP = array_filter($this->resultado, function($item) {
            return isset($item['extensao']) && $item['extensao'] == 'php';
        });
        
        $sugestoes = [];
        $padroesIngles = [
            'controller' => 'controlador',
            'view' => 'visao',
            'model' => 'modelo',
            'index' => 'inicio',
            'login' => 'entrar',
            'logout' => 'sair',
            'register' => 'registrar',
            'profile' => 'perfil',
            'config' => 'configuracao',
            'upload' => 'upload', // mantém
            'download' => 'baixar',
            'search' => 'buscar',
            'list' => 'listar',
            'edit' => 'editar',
            'delete' => 'excluir',
            'update' => 'atualizar',
            'insert' => 'inserir',
            'cadastro' => 'cadastro', // já em PT
            'busca' => 'busca', // já em PT
            'sucesso' => 'sucesso', // já em PT
            'detalhes' => 'detalhes', // já em PT
        ];
        
        foreach ($arquivosPHP as $arquivo) {
            $nomeBase = pathinfo($arquivo['nome'], PATHINFO_FILENAME);
            $nomeLower = strtolower($nomeBase);
            
            foreach ($padroesIngles as $ingles => $portugues) {
                if (strpos($nomeLower, $ingles) !== false && $ingles != $portugues) {
                    if (!isset($sugestoes[$arquivo['caminho']])) {
                        $novoNome = str_ireplace($ingles, $portugues, $nomeBase) . '.php';
                        $sugestoes[$arquivo['caminho']] = [
                            'atual' => $arquivo['nome'],
                            'sugestao' => $novoNome,
                            'caminho' => dirname($arquivo['caminho']) . '/' . $novoNome
                        ];
                    }
                }
            }
        }
        
        if (!empty($sugestoes)) {
            echo "Arquivos PHP que podem ser renomeados para português:\n\n";
            foreach ($sugestoes as $sugestao) {
                echo "   📄 " . $sugestao['atual'] . "\n";
                echo "     → " . $sugestao['sugestao'] . "\n";
                echo "     📍 " . $sugestao['caminho'] . "\n\n";
            }
        } else {
            echo "✓ Nenhum arquivo com nome em inglês detectado!\n";
        }
        
        echo "\n📌 RECOMENDAÇÕES:\n";
        echo "   1. Revise o relatório JSON para análise detalhada\n";
        echo "   2. Verifique o CSV para planejar a renomeação\n";
        echo "   3. Faça backup antes de qualquer alteração\n";
        echo "   4. Execute o script de nacionalização depois\n";
    }
}

// Executar o mapeador
$mapeador = new MapeadorEstrutura();
$mapeador->mapear();

echo "\n✅ MAPEAMENTO CONCLUÍDO!\n";