-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 08-Fev-2026 às 13:36
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
  `status_caracteristicas` enum('sem_dados','parcial','completo','revisado','validado') DEFAULT 'sem_dados',
  `status_imagens` enum('sem_imagens','parcial','completo','revisado','validado') DEFAULT 'sem_imagens',
  `status_identificacao` enum('nao_identificada','em_identificacao','identificada','em_revisao','revisada','publicada','contestado') DEFAULT 'nao_identificada',
  `identificadores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`identificadores`)),
  `revisores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`revisores`)),
  `contestadores` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`contestadores`)),
  `data_primeiro_registro` datetime DEFAULT current_timestamp(),
  `data_identificacao` datetime DEFAULT NULL,
  `data_revisao` datetime DEFAULT NULL,
  `data_contestacao` datetime DEFAULT NULL,
  `data_resposta_contestacao` datetime DEFAULT NULL,
  `data_publicacao` datetime DEFAULT NULL,
  `resultado_contestacao` enum('procedente','improcedente','parcial') DEFAULT NULL,
  `prioridade` enum('baixa','media','alta','urgente') DEFAULT 'media',
  `versao_registro` int(11) DEFAULT 1,
  `observacoes` text DEFAULT NULL,
  `data_ultima_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `especies_administrativo`
--

INSERT INTO `especies_administrativo` (`id`, `nome_cientifico`, `status_caracteristicas`, `status_imagens`, `status_identificacao`, `identificadores`, `revisores`, `contestadores`, `data_primeiro_registro`, `data_identificacao`, `data_revisao`, `data_contestacao`, `data_resposta_contestacao`, `data_publicacao`, `resultado_contestacao`, `prioridade`, `versao_registro`, `observacoes`, `data_ultima_atualizacao`) VALUES
(1, 'Persea americana', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(2, 'Andira spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(3, 'Anadenanthera colubrina', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(4, 'Anadenanthera macrocarpa', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(5, 'Annona spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(6, 'Schinus terebinthifolius', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(7, 'Lithraea molleoides', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(8, 'Myracrodruon urundeuva', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(9, 'Oenocarpus bacaba', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(10, 'Oenocarpus distichus', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(11, 'Acrocomia aculeata', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(12, 'Mauritia flexuosa', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(13, 'Vochysia spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(14, 'Peltophorum dubium', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(15, 'Vellozia squamata', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(16, 'Syngonanthus nitens', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(17, 'Terminalia argentea', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(18, 'Copernicia alba', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(19, 'Solanum paniculatum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(20, 'Jacaranda caroba', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(21, 'Roupala montana', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(22, 'Machaerium villosum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(23, 'Cedrela fissilis', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(24, 'Alchornea triplinervia', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(25, 'Mabea fistulifera', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(26, 'Philodendron spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(27, 'Copaifera spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(28, 'Vochysia divergens', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(29, 'Zygia racemosa', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(30, 'Zygia latifoliolata', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(31, 'Byrsonima spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(32, 'Cecropia spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(33, 'Cecropia pachystachya', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(34, 'Enterolobium gummiferum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(35, 'Pseudobombax longiflorum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(36, 'Dimorphandra mollis', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(37, 'Ficus spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(38, 'Apuleia leiocarpa', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(39, 'Cordia leucocephala', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(40, 'Acca sellowiana', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(41, 'Myrcianthes pungens', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(42, 'Casearia sylvestris', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(43, 'Cupania vernalis', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(44, 'Patagonula americana', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(45, 'Goupia glabra', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(46, 'Schizolobium parahyba', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(47, 'Schizolobium amazonicum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(48, 'Aspidosperma spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(49, 'Aspidosperma spruceanum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(50, 'Campomanesia spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(51, 'Inga spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(52, 'Inga laurina', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(53, 'Inga edulis', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(54, 'Inga sessilis', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(55, 'Stryphnodendron adstringens', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(56, 'Tabebuia spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(57, 'Handroanthus albus', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(58, 'Mezilaurus itauba', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(59, 'Jacaranda mimosifolia', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(60, 'Triplaris surinamensis', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(61, 'Hymenaea spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(62, 'Hymenaea oblongifolia', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(63, 'Hymenaea intermedia', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(64, 'Hymenaea stigonocarpa', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(65, 'Genipa americana', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(66, 'Cariniana legalis', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(67, 'Ziziphus joazeiro', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(68, 'Mimosa tenuiflora', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(69, 'Lafoensia pacari', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(70, 'Solanum lycocarpum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(71, 'Cordia glabrata', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(72, 'Cordia alliodora', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(73, 'Hancornia speciosa', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(74, 'Laguncularia racemosa', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(75, 'Calophyllum brasiliense', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(76, 'Diospyros hispida', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(77, 'Calycophyllum spruceanum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(78, 'Byrsonima crassifolia', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(79, 'Myrciaria spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(80, 'Licania tomentosa', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(81, 'Enterolobium contortisiliquum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(82, 'Bauhinia spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(83, 'Bauhinia ungulata', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(84, 'Gallesia integrifolia', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(85, 'Caesalpinia leiostachya', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(86, 'Triplaris gardneriana', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(87, 'Alchornea glandulosa', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(88, 'Caesalpinia ferrea', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(89, 'Piptadenia gonoacantha', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(90, 'Tapirira guianensis', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(91, 'Zanthoxylum rhoifolium', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(92, 'Qualea spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(93, 'Caryocar brasiliense', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(94, 'Caryocar villosum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(95, 'Aspidosperma polyneuron', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(96, 'Podocarpus sellowii', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(97, 'Eugenia uniflora', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(98, 'Tibouchina granulosa', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(99, 'Mimosa caesalpiniifolia', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(100, 'Pterodon spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(101, 'Pterodon emarginatus', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(102, 'Acacia polyphylla', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-08 08:31:56'),
(103, 'Enterolobium timbouva', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(104, 'Vitex cymosa', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(105, 'Vitex polygama', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(106, 'Atropha belladona', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(107, 'Dipteryx odorata', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(108, 'Ateleia glazioviana', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(109, 'Plathymenia reticulata', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(110, 'Bixa orellana', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(111, 'Bowdichia virgilioides', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(112, 'Eugenia pyriformis', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(113, 'Salacia crassifolia', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(114, 'Combretum lanceolatum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(115, 'Vernonia polyanthes', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(116, 'Scoparia dulcis', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18'),
(117, 'Sterculia striata', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-07 15:08:18', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-07 15:08:18');

-- --------------------------------------------------------

--
-- Estrutura da tabela `especies_caracteristicas`
--

CREATE TABLE `especies_caracteristicas` (
  `id` int(11) NOT NULL,
  `especie_id` int(11) NOT NULL,
  `nome_popular` varchar(255) DEFAULT NULL,
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
  `referencias` text DEFAULT NULL,
  `versao_dados` int(11) DEFAULT 1,
  `data_cadastro_botanico` datetime DEFAULT current_timestamp()
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
  `data_cadastro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `especies_administrativo`
--
ALTER TABLE `especies_administrativo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome_cientifico` (`nome_cientifico`);

--
-- Índices para tabela `especies_caracteristicas`
--
ALTER TABLE `especies_caracteristicas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_caracteristicas_especie` (`especie_id`);

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
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `especies_administrativo`
--
ALTER TABLE `especies_administrativo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

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
