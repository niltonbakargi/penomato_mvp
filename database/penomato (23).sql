-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 17-Fev-2026 às 22:16
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `penomato`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `especies_administrativo`
--

CREATE TABLE `especies_administrativo` (
  `id` int(11) NOT NULL,
  `nome_cientifico` varchar(255) NOT NULL,
  `status` enum('sem_dados','dados_internet','descrita','registrada','em_revisao','revisada','contestado','publicado') NOT NULL DEFAULT 'sem_dados',
  `data_dados_internet` datetime DEFAULT NULL,
  `data_descrita` datetime DEFAULT NULL,
  `data_registrada` datetime DEFAULT NULL,
  `data_revisada` datetime DEFAULT NULL,
  `data_contestado` datetime DEFAULT NULL,
  `data_publicado` datetime DEFAULT NULL,
  `autor_dados_internet_id` int(11) DEFAULT NULL,
  `autor_descrita_id` int(11) DEFAULT NULL,
  `autor_registrada_id` int(11) DEFAULT NULL,
  `autor_revisada_id` int(11) DEFAULT NULL,
  `autor_contestado_id` int(11) DEFAULT NULL,
  `autor_publicado_id` int(11) DEFAULT NULL,
  `motivo_contestado` text DEFAULT NULL,
  `data_primeiro_registro` datetime DEFAULT current_timestamp(),
  `data_revisao` datetime DEFAULT NULL,
  `observacoes_revisao` text DEFAULT NULL,
  `prioridade` enum('baixa','media','alta','urgente') DEFAULT 'media',
  `versao_registro` int(11) DEFAULT 1,
  `observacoes` text DEFAULT NULL,
  `data_ultima_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `especies_caracteristicas`
--

CREATE TABLE `especies_caracteristicas` (
  `id` int(11) NOT NULL,
  `especie_id` int(11) NOT NULL,
  `nome_cientifico_completo` varchar(255) DEFAULT NULL,
  `nome_popular` text DEFAULT NULL,
  `familia` varchar(255) DEFAULT NULL,
  `forma_folha` varchar(255) DEFAULT NULL,
  `filotaxia_folha` varchar(255) DEFAULT NULL,
  `tipo_folha` varchar(255) DEFAULT NULL,
  `tamanho_folha` varchar(255) DEFAULT NULL,
  `textura_folha` varchar(255) DEFAULT NULL,
  `margem_folha` varchar(255) DEFAULT NULL,
  `venacao_folha` varchar(255) DEFAULT NULL,
  `cor_flores` varchar(255) DEFAULT NULL,
  `simetria_floral` varchar(255) DEFAULT NULL,
  `numero_petalas` varchar(255) DEFAULT NULL,
  `disposicao_flores` varchar(255) DEFAULT NULL,
  `aroma` varchar(255) DEFAULT NULL,
  `tamanho_flor` varchar(255) DEFAULT NULL,
  `tipo_fruto` varchar(255) DEFAULT NULL,
  `tamanho_fruto` varchar(255) DEFAULT NULL,
  `cor_fruto` varchar(255) DEFAULT NULL,
  `textura_fruto` varchar(255) DEFAULT NULL,
  `dispersao_fruto` varchar(255) DEFAULT NULL,
  `aroma_fruto` varchar(255) DEFAULT NULL,
  `tipo_semente` varchar(255) DEFAULT NULL,
  `tamanho_semente` varchar(255) DEFAULT NULL,
  `cor_semente` varchar(255) DEFAULT NULL,
  `textura_semente` varchar(255) DEFAULT NULL,
  `quantidade_sementes` varchar(255) DEFAULT NULL,
  `tipo_caule` varchar(255) DEFAULT NULL,
  `estrutura_caule` varchar(255) DEFAULT NULL,
  `textura_caule` varchar(255) DEFAULT NULL,
  `cor_caule` varchar(255) DEFAULT NULL,
  `forma_caule` varchar(255) DEFAULT NULL,
  `modificacao_caule` varchar(255) DEFAULT NULL,
  `diametro_caule` varchar(255) DEFAULT NULL,
  `ramificacao_caule` varchar(255) DEFAULT NULL,
  `possui_espinhos` enum('Sim','Não') DEFAULT NULL,
  `possui_latex` enum('Sim','Não') DEFAULT NULL,
  `possui_seiva` enum('Sim','Não') DEFAULT NULL,
  `possui_resina` enum('Sim','Não') DEFAULT NULL,
  `referencias` longtext DEFAULT NULL,
  `versao_dados` int(11) DEFAULT 1,
  `data_cadastro_botanico` datetime DEFAULT current_timestamp(),
  `familia_ref` varchar(100) DEFAULT NULL,
  `forma_folha_ref` varchar(100) DEFAULT NULL,
  `filotaxia_folha_ref` varchar(100) DEFAULT NULL,
  `tipo_folha_ref` varchar(100) DEFAULT NULL,
  `tamanho_folha_ref` varchar(100) DEFAULT NULL,
  `textura_folha_ref` varchar(100) DEFAULT NULL,
  `margem_folha_ref` varchar(100) DEFAULT NULL,
  `venacao_folha_ref` varchar(100) DEFAULT NULL,
  `cor_flores_ref` varchar(100) DEFAULT NULL,
  `simetria_floral_ref` varchar(100) DEFAULT NULL,
  `numero_petalas_ref` varchar(100) DEFAULT NULL,
  `disposicao_flores_ref` varchar(100) DEFAULT NULL,
  `aroma_ref` varchar(100) DEFAULT NULL,
  `tamanho_flor_ref` varchar(100) DEFAULT NULL,
  `tipo_fruto_ref` varchar(100) DEFAULT NULL,
  `tamanho_fruto_ref` varchar(100) DEFAULT NULL,
  `cor_fruto_ref` varchar(100) DEFAULT NULL,
  `textura_fruto_ref` varchar(100) DEFAULT NULL,
  `dispersao_fruto_ref` varchar(100) DEFAULT NULL,
  `aroma_fruto_ref` varchar(100) DEFAULT NULL,
  `tipo_semente_ref` varchar(100) DEFAULT NULL,
  `tamanho_semente_ref` varchar(100) DEFAULT NULL,
  `cor_semente_ref` varchar(100) DEFAULT NULL,
  `textura_semente_ref` varchar(100) DEFAULT NULL,
  `quantidade_sementes_ref` varchar(100) DEFAULT NULL,
  `tipo_caule_ref` varchar(100) DEFAULT NULL,
  `estrutura_caule_ref` varchar(100) DEFAULT NULL,
  `textura_caule_ref` varchar(100) DEFAULT NULL,
  `cor_caule_ref` varchar(100) DEFAULT NULL,
  `forma_caule_ref` varchar(100) DEFAULT NULL,
  `modificacao_caule_ref` varchar(100) DEFAULT NULL,
  `possui_espinhos_ref` varchar(50) DEFAULT NULL,
  `possui_latex_ref` varchar(50) DEFAULT NULL,
  `possui_seiva_ref` varchar(50) DEFAULT NULL,
  `possui_resina_ref` varchar(50) DEFAULT NULL,
  `nome_cientifico_completo_ref` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `historico_alteracoes`
--

CREATE TABLE `historico_alteracoes` (
  `id` int(11) NOT NULL,
  `especie_id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tabela_afetada` varchar(100) NOT NULL,
  `campo_alterado` varchar(100) DEFAULT NULL,
  `valor_anterior` text DEFAULT NULL,
  `valor_novo` text DEFAULT NULL,
  `tipo_acao` enum('insercao','edicao','revisao','contestacao','validacao','publicacao') NOT NULL,
  `justificativa` text DEFAULT NULL,
  `data_alteracao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `imagens_especies`
--

CREATE TABLE `imagens_especies` (
  `id` int(11) NOT NULL,
  `especie_id` int(11) NOT NULL,
  `parte` enum('folha','flor','fruto','semente','caule','geral') NOT NULL,
  `caminho_imagem` varchar(255) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `localizacao` varchar(255) DEFAULT NULL,
  `data_coleta` date DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `metadados` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadados`)),
  `id_usuario_identificador` int(11) NOT NULL,
  `id_usuario_confirmador` int(11) DEFAULT NULL,
  `status_validacao` enum('pendente','confirmado','rejeitado') DEFAULT 'pendente',
  `data_upload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `sugestoes_usuario`
--

CREATE TABLE `sugestoes_usuario` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nome_cientifico` varchar(255) DEFAULT NULL,
  `sugestao_caracteristicas` text DEFAULT NULL,
  `outras_sugestoes` text DEFAULT NULL,
  `status_sugestao` enum('recebida','em_analise','aprovada','rejeitada') DEFAULT 'recebida',
  `resposta_gestor` text DEFAULT NULL,
  `data_envio` datetime DEFAULT current_timestamp(),
  `data_avaliacao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `categoria` enum('gestor','colaborador','revisor','validador','visitante') DEFAULT 'visitante',
  `subtipo_colaborador` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `status_verificacao` enum('pendente','verificado','bloqueado') DEFAULT 'pendente',
  `ativo` tinyint(1) DEFAULT 1,
  `data_cadastro` datetime DEFAULT current_timestamp(),
  `ultimo_acesso` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `especies_administrativo`
--
ALTER TABLE `especies_administrativo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome_cientifico` (`nome_cientifico`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_data_descrita` (`data_descrita`),
  ADD KEY `idx_data_registrada` (`data_registrada`),
  ADD KEY `idx_data_revisada` (`data_revisada`);

--
-- Índices para tabela `especies_caracteristicas`
--
ALTER TABLE `especies_caracteristicas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_caracteristicas_especie` (`especie_id`),
  ADD KEY `idx_especies_caracteristicas_nome` (`nome_cientifico_completo`);

--
-- Índices para tabela `historico_alteracoes`
--
ALTER TABLE `historico_alteracoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_historico_especie` (`especie_id`),
  ADD KEY `fk_historico_usuario` (`id_usuario`);

--
-- Índices para tabela `imagens_especies`
--
ALTER TABLE `imagens_especies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_imagem_especie` (`especie_id`),
  ADD KEY `fk_imagem_usuario` (`id_usuario_identificador`);

--
-- Índices para tabela `sugestoes_usuario`
--
ALTER TABLE `sugestoes_usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sugestao_usuario` (`id_usuario`);

--
-- Índices para tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_usuarios_categoria` (`categoria`),
  ADD KEY `idx_usuarios_status` (`status_verificacao`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `especies_administrativo`
--
ALTER TABLE `especies_administrativo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `especies_caracteristicas`
--
ALTER TABLE `especies_caracteristicas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `historico_alteracoes`
--
ALTER TABLE `historico_alteracoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `imagens_especies`
--
ALTER TABLE `imagens_especies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `sugestoes_usuario`
--
ALTER TABLE `sugestoes_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `especies_caracteristicas`
--
ALTER TABLE `especies_caracteristicas`
  ADD CONSTRAINT `fk_caracteristicas_especie` FOREIGN KEY (`especie_id`) REFERENCES `especies_administrativo` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `historico_alteracoes`
--
ALTER TABLE `historico_alteracoes`
  ADD CONSTRAINT `fk_historico_especie` FOREIGN KEY (`especie_id`) REFERENCES `especies_administrativo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_historico_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Limitadores para a tabela `imagens_especies`
--
ALTER TABLE `imagens_especies`
  ADD CONSTRAINT `fk_imagem_especie` FOREIGN KEY (`especie_id`) REFERENCES `especies_administrativo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_imagem_usuario` FOREIGN KEY (`id_usuario_identificador`) REFERENCES `usuarios` (`id`);

--
-- Limitadores para a tabela `sugestoes_usuario`
--
ALTER TABLE `sugestoes_usuario`
  ADD CONSTRAINT `fk_sugestao_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
