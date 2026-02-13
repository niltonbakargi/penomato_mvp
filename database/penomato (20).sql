-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 12-Fev-2026 às 23:18
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
(1, 'Persea americana', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(2, 'Andira spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(3, 'Anadenanthera colubrina', 'completo', 'sem_imagens', 'identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', '2026-02-12 10:11:36', NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-12 10:11:36'),
(4, 'Anadenanthera macrocarpa', 'completo', 'sem_imagens', 'identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', '2026-02-12 10:13:28', NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-12 10:13:28'),
(5, 'Annona spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(6, 'Schinus terebinthifolius', 'completo', 'sem_imagens', 'identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', '2026-02-12 10:18:15', NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-12 10:18:15'),
(7, 'Lithraea molleoides', 'completo', 'sem_imagens', 'identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', '2026-02-12 10:20:26', NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-12 10:20:26'),
(8, 'Myracrodruon urundeuva', 'completo', 'sem_imagens', 'identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', '2026-02-12 10:22:26', NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-12 10:22:26'),
(9, 'Oenocarpus bacaba', 'completo', 'sem_imagens', 'identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', '2026-02-12 10:23:53', NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-12 10:23:53'),
(10, 'Oenocarpus distichus', 'completo', 'sem_imagens', 'identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', '2026-02-12 10:24:56', NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-12 10:24:56'),
(11, 'Acrocomia aculeata', 'completo', 'sem_imagens', 'identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', '2026-02-12 10:06:44', NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-12 10:06:44'),
(12, 'Mauritia flexuosa', 'completo', 'sem_imagens', 'identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', '2026-02-12 10:27:05', NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-12 10:27:05'),
(13, 'Vochysia spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(14, 'Peltophorum dubium', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(15, 'Vellozia squamata', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(16, 'Syngonanthus nitens', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(17, 'Terminalia argentea', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(18, 'Copernicia alba', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(19, 'Solanum paniculatum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(20, 'Jacaranda caroba', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(21, 'Roupala montana', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(22, 'Machaerium villosum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(23, 'Cedrela fissilis', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(24, 'Alchornea triplinervia', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(25, 'Mabea fistulifera', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(26, 'Philodendron spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(27, 'Copaifera spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(28, 'Vochysia divergens', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(29, 'Zygia racemosa', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(30, 'Zygia latifoliolata', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(31, 'Byrsonima spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(32, 'Cecropia spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(33, 'Cecropia pachystachya', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(34, 'Enterolobium gummiferum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(35, 'Pseudobombax longiflorum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(36, 'Dimorphandra mollis', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(37, 'Ficus spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(38, 'Apuleia leiocarpa', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(39, 'Cordia leucocephala', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(40, 'Acca sellowiana', 'completo', 'sem_imagens', 'identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', '2026-02-12 09:07:34', NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-12 09:07:34'),
(41, 'Myrcianthes pungens', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(42, 'Casearia sylvestris', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(43, 'Cupania vernalis', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(44, 'Patagonula americana', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(45, 'Goupia glabra', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(46, 'Schizolobium parahyba', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(47, 'Schizolobium amazonicum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(48, 'Aspidosperma spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(49, 'Aspidosperma spruceanum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(50, 'Campomanesia spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(51, 'Inga spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(52, 'Inga laurina', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(53, 'Inga edulis', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(54, 'Inga sessilis', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(55, 'Stryphnodendron adstringens', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(56, 'Tabebuia spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(57, 'Handroanthus albus', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(58, 'Mezilaurus itauba', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(59, 'Jacaranda mimosifolia', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(60, 'Triplaris surinamensis', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(61, 'Hymenaea spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(62, 'Hymenaea oblongifolia', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(63, 'Hymenaea intermedia', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(64, 'Hymenaea stigonocarpa', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(65, 'Genipa americana', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(66, 'Cariniana legalis', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(67, 'Ziziphus joazeiro', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(68, 'Mimosa tenuiflora', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(69, 'Lafoensia pacari', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(70, 'Solanum lycocarpum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(71, 'Cordia glabrata', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(72, 'Cordia alliodora', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(73, 'Hancornia speciosa', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(74, 'Laguncularia racemosa', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(75, 'Calophyllum brasiliense', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(76, 'Diospyros hispida', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(77, 'Calycophyllum spruceanum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(78, 'Byrsonima crassifolia', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(79, 'Myrciaria spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(80, 'Licania tomentosa', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(81, 'Enterolobium contortisiliquum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(82, 'Bauhinia spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(83, 'Bauhinia ungulata', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(84, 'Gallesia integrifolia', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(85, 'Caesalpinia leiostachya', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(86, 'Triplaris gardneriana', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(87, 'Alchornea glandulosa', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(88, 'Caesalpinia ferrea', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(89, 'Piptadenia gonoacantha', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(90, 'Tapirira guianensis', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(91, 'Zanthoxylum rhoifolium', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(92, 'Qualea spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(93, 'Caryocar brasiliense', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(94, 'Caryocar villosum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(95, 'Aspidosperma polyneuron', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(96, 'Podocarpus sellowii', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(97, 'Eugenia uniflora', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(98, 'Tibouchina granulosa', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(99, 'Mimosa caesalpiniifolia', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(100, 'Pterodon spp.', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(101, 'Pterodon emarginatus', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(102, 'Acacia polyphylla', 'sem_dados', 'parcial', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-12 18:02:38'),
(103, 'Enterolobium timbouva', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(104, 'Vitex cymosa', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(105, 'Vitex polygama', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(106, 'Atropha belladona', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(107, 'Dipteryx odorata', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(108, 'Ateleia glazioviana', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(109, 'Plathymenia reticulata', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(110, 'Bixa orellana', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(111, 'Bowdichia virgilioides', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(112, 'Eugenia pyriformis', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(113, 'Salacia crassifolia', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(114, 'Combretum lanceolatum', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(115, 'Vernonia polyanthes', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(116, 'Scoparia dulcis', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40'),
(117, 'Sterculia striata', 'sem_dados', 'sem_imagens', 'nao_identificada', NULL, NULL, NULL, '2026-02-09 15:32:40', NULL, NULL, NULL, NULL, NULL, NULL, 'media', 1, NULL, '2026-02-09 15:32:40');

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

--
-- Extraindo dados da tabela `especies_caracteristicas`
--

INSERT INTO `especies_caracteristicas` (`id`, `especie_id`, `nome_cientifico_completo`, `nome_popular`, `familia`, `forma_folha`, `filotaxia_folha`, `tipo_folha`, `tamanho_folha`, `textura_folha`, `margem_folha`, `venacao_folha`, `cor_flores`, `simetria_floral`, `numero_petalas`, `disposicao_flores`, `aroma`, `tamanho_flor`, `tipo_fruto`, `tamanho_fruto`, `cor_fruto`, `textura_fruto`, `dispersao_fruto`, `aroma_fruto`, `tipo_semente`, `tamanho_semente`, `cor_semente`, `textura_semente`, `quantidade_sementes`, `tipo_caule`, `estrutura_caule`, `textura_caule`, `cor_caule`, `forma_caule`, `modificacao_caule`, `diametro_caule`, `ramificacao_caule`, `possui_espinhos`, `possui_latex`, `possui_seiva`, `possui_resina`, `referencias`, `versao_dados`, `data_cadastro_botanico`, `familia_ref`, `forma_folha_ref`, `filotaxia_folha_ref`, `tipo_folha_ref`, `tamanho_folha_ref`, `textura_folha_ref`, `margem_folha_ref`, `venacao_folha_ref`, `cor_flores_ref`, `simetria_floral_ref`, `numero_petalas_ref`, `disposicao_flores_ref`, `aroma_ref`, `tamanho_flor_ref`, `tipo_fruto_ref`, `tamanho_fruto_ref`, `cor_fruto_ref`, `textura_fruto_ref`, `dispersao_fruto_ref`, `aroma_fruto_ref`, `tipo_semente_ref`, `tamanho_semente_ref`, `cor_semente_ref`, `textura_semente_ref`, `quantidade_sementes_ref`, `tipo_caule_ref`, `estrutura_caule_ref`, `textura_caule_ref`, `cor_caule_ref`, `forma_caule_ref`, `modificacao_caule_ref`, `possui_espinhos_ref`, `possui_latex_ref`, `possui_seiva_ref`, `possui_resina_ref`, `nome_cientifico_completo_ref`) VALUES
(1, 40, 'Acca sellowiana (O. Berg) Burret', 'Goiabeira-serrana, Goiabeira-da-serra, Feijoa, Pineapple-guava', 'Myrtaceae', 'Elíptica', 'Oposta', 'Simples', 'Nanofilos (2–7 cm)', 'Coriácea', 'Inteira', 'Peninérvea', 'Rosadas', 'Actinomorfa (simetria radial)', '4 pétalas', 'Isoladas', 'Aroma suave', 'Média', 'Baga', 'Médio (2–5 cm)', 'Verde', 'Lisa', 'Zoocórica (por animais)', 'Aroma forte', 'Dura', 'Pequena (< 5 mm)', 'Marrom', 'Lisa', 'Muitas (> 5)', 'Ereto', 'Lenhoso', 'Lisa', 'Marrom', 'Cilíndrico', NULL, 'Médio (1–5 cm)', 'Simpodial', 'Não', 'Não', 'Sim', 'Não', '1. LORENZI, H. et al. Árvores Brasileiras: manual de identificação e cultivo de plantas arbóreas nativas do Brasil. Nova Odessa: Instituto Plantarum, 2003. v.1.\n2. BACKES, P.; IRGANG, B. Árvores do Sul: guia de identificação e interesse ecológico. Porto Alegre: Instituto Souza Cruz, 2002.\n3. FLORA DO BRASIL 2020. Acca sellowiana. Jardim Botânico do Rio de Janeiro.', 1, '2026-02-12 09:07:34', '1', '2', '2', '2', '2', '1', '2', '2', '1', '3', '1', '1', '2', '1', '1', '1', '2', '2', '3', '2', '2', '1', '2', '2', '1', '2', '2', '2', '2', '2', '2', '2', '3', '3', '3', '1,2'),
(2, 11, 'Acrocomia aculeata (Jacq.) Lodd. ex Mart.', 'Macaúba, Bocaiuva, Coco-de-espinho, Macaíba, Macajuba, Coco-baboso, Macaúva, Mucajá, Boicaiuva, Chiclete-de-baiano, Chiclete cuiabano', 'Arecaceae', 'Pinada', 'Alterna', 'Composta pinnada', 'Macrófilos (20–50 cm)', 'Coriácea', 'Inteira', 'Paralela', 'Amarelas', 'Actinomorfa (simetria radial)', '3 pétalas', 'Inflorescência (cacho, espiga, capítulo, umbela)', 'Aroma forte', 'Pequena', 'Drupa', 'Médio (2–5 cm)', 'Amarelo', 'Lisa', 'Zoocórica (por animais)', 'Aroma suave', 'Dura', 'Média (5–10 mm)', 'Marrom', 'Lisa', 'Uma', 'Ereto', 'Lenhoso', 'Espinhosa', 'Cinza', 'Cilíndrico', 'Espinhos', 'Médio (1–5 cm)', 'Monopodial', 'Sim', 'Não', 'Sim', 'Não', '1. LORENZI, H. et al. Palmeiras no Brasil: Exóticas e Nativas. Nova Odessa: Instituto Plantarum, 1996.\n2. EMBRAPA. Macaúba (Acrocomia aculeata). Agência de Informação Tecnológica - Agroenergia, 2021.\n3. Horto Botânico do Museu Nacional/UFRJ. Acrocomia aculeata (Jacq.) Lodd. Disponível em: http://www.museunacional.ufrj.br/hortobotanico/Palmeiras/acrocomiaaculeata.html\n4. Jardineiro.net. Macaúba – Acrocomia aculeata. Disponível em: https://www.jardineiro.net/plantas/macauba-acrocomia-aculeata.html\n5. Cerratinga. Macaúba (Acrocomia aculeata). Disponível em: https://www.cerratinga.org.br/especies/macauba/\n6. Rewilding Brazil. Acrocomia aculeata - Macaúba. Disponível em: https://www.rewilding-brazil.org/especie/acrocomia-aculeata/\n7. Parque Estadual do Cantão. Acrocomia aculeata. Disponível em: https://ulbra-to.br/cantao/2012/06/25/Acrocomia-aculeata\n8. Flora e Funga do Brasil. Acrocomia aculeata (Jacq.) Lodd. ex Mart. Jardim Botânico do Rio de Janeiro. Disponível em: http://floradobrasil.jbrj.gov.br/', 1, '2026-02-12 10:06:44', '3,7,8', '3,4', '4', '3,4', '4,5', '3', '3', '4', '3,4', '3', '3', '3,4', '4', '4', '1,3,4', '3,6', '3', '3', '6', '4', '3', '3', '3', '3', '3,4,6', '1,4', '2,4', '1,3,4', '4', '4', '1,3,4', '1,3,4', '3,4', '3', '3,4', '1,3,8'),
(3, 3, 'Anadenanthera colubrina (Vell.) Brenan', 'Angico, Angico-branco, Angico-vermelho, Angico-preto, Angico-liso, Angico-coco, Angico-escuro, Angico-cambuí, Angico-do-campo, Arapiraca, Cambuí, Cambuí-angico, Cambuí-vermelho, Cauvi, Curupaí, Curupaíba, Monjoleiro, Aperta-ruão, Jurema-preta, Paricá, Goma-de-angico', 'Fabaceae', 'Bipinada', 'Alterna', 'Composta bipinada', 'Macrófilos (20–50 cm)', 'Glabra', 'Inteira', 'Reticulada Pinnada', 'Brancas', 'Actinomorfa (simetria radial)', '5 pétalas', 'Inflorescência (cacho, espiga, capítulo, umbela)', 'Aroma suave', 'Pequena', 'Legume', 'Grande (> 5 cm)', 'Marrom', 'Lisa', 'Autocórica (pelo próprio fruto)', 'Sem cheiro', 'Dura', 'Média (5–10 mm)', 'Marrom', 'Lisa', 'Poucas (2–5)', 'Ereto', 'Lenhoso', 'Rugosa', 'Marrom', 'Cilíndrico', 'Espinhos', 'Médio (1–5 cm)', 'Simpodial', 'Sim', 'Não', 'Sim', 'Sim', '1. LORENZI, H. Árvores Brasileiras: manual de identificação e cultivo de plantas arbóreas nativas do Brasil. 5. ed. Nova Odessa: Instituto Plantarum, 2008. v.1.\n2. PAREYN, F. G. C.; ARAÚJO, E. de L.; DRUMOND, M. A. Anadenanthera colubrina: Angico. Embrapa, 2018.\n3. Wikipédia. Anadenanthera colubrina. Disponível em: https://pt.wikipedia.org/wiki/Anadenanthera_colubrina\n4. Projeto Caatinga - UFERSA. ANGICO – Anadenanthera colubrina. Disponível em: https://projetocaatinga.ufersa.edu.br/en-angico/', 1, '2026-02-12 10:11:36', '1,3,4', '2,3,4', '4', '1,3,4', '1', '1', '4', '4', '1,3', '4', '3', '1,4', '1', '1', '1,3', '1,3', '1,3', '3', '4', '4', '3', '1', '1,3', '3', '1,3', '2', '2', '1,2', '1', '2', '2,3', '2,3', '4', '2,3', '3,4', '1,2,4'),
(4, 4, 'Anadenanthera macrocarpa (Benth.) Brenan', 'Angico-preto, Angico, Curupay, Curupay-atã, Cebil, Cebil colorado, Angico-vermelho, Angico-liso, Arapiraca, Black Angico, Angico-do-cerrado', 'Fabaceae', 'Bipinada', 'Alterna', 'Composta bipinada', 'Mesofilos (7–20 cm)', 'Glabra', 'Inteira', 'Reticulada Pinnada', 'Creme', 'Actinomorfa (simetria radial)', '5 pétalas', 'Inflorescência (cacho, espiga, capítulo, umbela)', 'Sem cheiro', 'Pequena', 'Legume', 'Grande (> 5 cm)', 'Marrom', 'Lisa', 'Autocórica (pelo próprio fruto)', 'Sem cheiro', 'Dura', 'Média (5–10 mm)', 'Marrom', 'Lisa', 'Muitas (> 5)', 'Ereto', 'Lenhoso', 'Lisa', 'Marrom', 'Cilíndrico', NULL, 'Grosso (> 5 cm)', 'Angulosa', 'Não', 'Não', 'Sim', 'Não', '1. KEW SCIENCE. Anadenanthera macrocarpa (Benth.) Brenan. Plants of the World Online. Disponível em: https://powo.science.kew.org/taxon/urn:lsid:ipni.org:names:12267-2\n2. WIKIPÉDIA. Anadenanthera colubrina var. cebil. Disponível em: https://en.wikipedia.org/wiki/Anadenanthera_colubrina_var._cebil\n3. USDA FOREST SERVICE. Anadenanthera macrocarpa - Wood Technology Transfer Fact Sheet. Disponível em: https://www.fpl.fs.usda.gov/documnts/TechSheets/Chudnoff/TropAmerican/htmlDocs_tropamerican/anadenanmacrocarp.html\n4. 百度百科. 大果阿那豆木. Disponível em: https://baike.baidu.com/item/大果阿那豆木\n5. EMBRAPA. PIRES, I. E.; NASCIMENTO, C. E. de S. Anadenanthera macrocarpa (Benth.) Brenan. In: FAO. Databook on endangered tree and shrub species and provenances. Rome, 1986. p. 54-49.\n6. EMBRAPA SEMIÁRIDO. Anadenanthera macrocarpa - Angico. Disponível em: https://www.embrapa.br', 1, '2026-02-12 10:13:28', '1,3,5', '2', '2', '2,5', '2', '2,5', '5', '5', '2', '2', '2', '2', '5', '2', '2,5', '2', '2', '2', '5', '5', '2', '2', '2', '2', '2', '5,6', '5', '6,8', '3,5', '5', '6,8', '6,8', '5', '5', '5', '1,2,4'),
(5, 6, 'Schinus terebinthifolia Raddi', 'Aroeira-vermelha, Aroeira-mansa, Aroeira-pimenteira, Pimenta-rosa, Brazilian pepper-tree, Christmasberry, Florida holly, Faux poivrier, Copal, Aroeira, Warui, Wilelaiki, Naniohilo, Pimenteira-do-Brasil, Aroeira-da-praia, Coração-de-bugre, Fruto-de-sabiá', 'Anacardiaceae', 'Pinada', 'Alterna', 'Composta pinnada', 'Mesofilos (7–20 cm)', 'Glabra', 'Serrada', 'Reticulada Pinnada', 'Brancas', 'Actinomorfa (simetria radial)', '5 pétalas', 'Inflorescência (cacho, espiga, capítulo, umbela)', 'Aroma forte', 'Pequena', 'Drupa', 'Pequeno (< 2 cm)', 'Vermelho', 'Lisa', 'Zoocórica (por animais)', 'Aroma forte', 'Dura', 'Pequena (< 5 mm)', 'Marrom', 'Lisa', 'Uma', 'Ereto', 'Lenhoso', 'Lisa', 'Cinza', 'Cilíndrico', NULL, 'Médio (1–5 cm)', 'Simpodial', 'Não', 'Sim', 'Sim', 'Sim', '1. VIRGINIA TECH DENDROLOGY. Schinus terebinthifolius - Brazilian peppertree. Disponível em: https://dendro.cnre.vt.edu/dendrology/syllabus/factsheet.cfm?ID=704\n2. CIFOR-ICRAF. Schinus terebinthifolius. Agroforestree Database v.4.0. Disponível em: https://apps.worldagroforestry.org/treedb2/speciesprofile.php/AFTPDFS?Spid=18217\n3. INTEGRATED TAXONOMIC INFORMATION SYSTEM (ITIS). Schinus terebinthifolius Raddi. Taxonomic Serial No.: 28812. Disponível em: https://www.itis.gov/servlet/SingleRpt/SingleRpt?search_topic=TSN&search_value=28812\n4. US FOREST SERVICE. Schinus terebinthifolius. Fire Effects Information System (FEIS). Disponível em: https://www.fs.usda.gov/database/feis/plants/shrub/schter/all.html\n5. MISSOURI BOTANICAL GARDEN. Schinus terebinthifolius - Brazilian peppertree. Plant Finder. Disponível em: https://www.missouribotanicalgarden.org/PlantFinder/PlantFinderDetails.aspx?kempercode=e921\n6. TEXAS INVASIVE SPECIES INSTITUTE. Brazilian pepper-tree (Schinus terebinthifolius). Disponível em: https://tsusinvasives.org/home/database/schinus-terebinthifolius\n7. TOPTROPICALS. Schinus terebinthifolius - Brazilian pepper-tree. Tropical Plant Encyclopedia. Disponível em: https://toptropicals.com/catalog/uid/schinus_terebinthifolius.htm\n8. IUCN/SSC INVISIVE SPECIES SPECIALIST GROUP. Schinus terebinthifolius. Global Invasive Species Database. Disponível em: http://www.iucngisd.org/gisd/species.php?sc=22\n9. LUMS BIODIVERSITY. Schinus terebinthifolia G. Raddi. Disponível em: https://biodiversity.lums.edu.pk/lums-biodiversity/plants/schinus-terebinthifolia-g-raddi\n10. PLANTNET NSW. Schinus terebinthifolia Raddi. New South Wales Flora Online. Disponível em: https://plantnet.rbgsyd.nsw.gov.au/cgi-bin/NSWfl.pl?page=nswfl&lvl=sp&name=Schinus~terebinthifolia', 1, '2026-02-12 10:18:15', '3,7,9', '4,9', '4,9', '1,2,7', '1,7', '2,4', '1,5', '9', '1,2,5', '8', '1,8', '2,8', '1,5,7', '1,8', '2,8,9', '1,6', '1,2,5', '2,5', '6,8', '1,2', '5', '2,8', '5', '5', '8,9', '2,9', '7', '1,5', '1', '5', '', '5,7', '5,9', '5,9', '2,8', '3,7,10'),
(6, 7, 'Lithraea molleoides (Vell.) Engl.', 'Aruera, Molle de beber, Molle dulce, Chicha, Chichita, Aguaribay, Aguaraibá, Molle, Aroeira-brava, Bugreiro, Pau-de-bugre, Aroeirinha, Cambuí, Coalha, Coração-de-bugre', 'Anacardiaceae', 'Lanceolada', 'Alterna', 'Composta pinnada', 'Mesofilos (7–20 cm)', 'Glabra', 'Inteira', 'Reticulada Pinnada', 'Amarelas', 'Actinomorfa (simetria radial)', '5 pétalas', 'Inflorescência (cacho, espiga, capítulo, umbela)', 'Sem cheiro', 'Pequena', 'Drupa', 'Pequeno (< 2 cm)', 'Branco', 'Lisa', 'Zoocórica (por animais)', 'Aroma suave', 'Dura', 'Pequena (< 5 mm)', 'Preta', 'Lisa', 'Uma', 'Ereto', 'Lenhoso', 'Rugosa', 'Cinza', 'Cilíndrico', NULL, 'Médio (1–5 cm)', 'Simpodial', 'Não', 'Sim', 'Sim', 'Não', '1. EYNARD, C.; CALVIÑO, A.; ASHWORTH, L. Cultivo de Plantas Nativas: propagación y viverismo de especies de Argentina central. 1. ed. Córdoba: Editorial de la UNC, 2017.\n2. SHIMIZU, M. T. et al. Essential oil of Lithraea molleoides (Vell.): chemical composition and antimicrobial activity. Brazilian Journal of Microbiology, v. 37, n. 4, p. 556-560, 2006.\n3. WORLD FLORA DATABASE. Lithraea molleoides (Vell.) Engl. Disponível em: https://www.worldfloradb.net\n4. BACKES, P.; IRGANG, B. Mata Atlântica: as árvores e a paisagem. Porto Alegre: Paisagem do Sul, 2004. p. 100.\n5. LA NACIÓN. Molle de beber - Lithraea molleoides. Banco de Plantas Nativas. Disponível em: https://nativas.lanacion.com.ar\n6. MEDINA, M.; DEMAIO, P. Árboles nativos de Córdoba. Suplemento Aquí Vivimos, 5. ed. Córdoba, Argentina.\n7. SISTEMA DE INFORMACIÓN DE BIODIVERSIDAD (SIB). Lithraea molleoides (molle de beber). Parques Nacionales, Argentina. Disponível em: https://sib.gob.ar/especies/lithraea-molleoides\n8. JARDÍN BOTÁNICO DE MONTEVIDEO. Lithraea molleoides - Aruera. Disponível em: https://jardinbotanico.montevideo.gub.uy/node/96\n9. CARMELLO-GUERREIRO, S. M.; PAOLI, A. A. S. Anatomy of the pericarp and seed-coat of Lithraea molleoides (Vell.) Engl. (Anacardiaceae) with taxonomic notes. Brazilian Archives of Biology and Technology, v. 48, n. 4, p. 599-610, 2005.', 1, '2026-02-12 10:20:26', '1,5,7', '1,7', '7,8', '1,7', '1,7', '1,8', '7,8', '1,7', '5,7', '7', '7', '1,7', '5', '1,7', '7,8,9', '1,8', '1,7', '8,9', '5,7', '1', '8,9', '8', '8', '8,9', '7,8', '5', '5', '1,7', '1,7', '7', '7,8', '7,8', '1,2', '2,5', '5,7', '1,2,7'),
(7, 8, 'Myracrodruon urundeuva Allemão', 'Aroeira, Aroeira-do-sertão, Urundeúva, Aroeira-preta, Aroeira-do-campo, Aroeira-da-serra, Almecega, Arindeuva, Aroeira-d\'água, Aroeira-do-cerrado, Uriunduba, Urindeúva', 'Anacardiaceae', 'Oblonga', 'Alterna', 'Composta pinnada', 'Macrófilos (20–50 cm)', 'Glabra', 'Serrada', 'Reticulada Pinnada', 'Amarelo-alaranjada', 'Actinomorfa (simetria radial)', '5 pétalas', 'Inflorescência (cacho, espiga, capítulo, umbela)', 'Aroma forte', 'Pequena', 'Drupa', 'Pequeno (< 2 cm)', 'Marrom', 'Lisa', 'Anemocórica (pelo vento)', 'Sem cheiro', 'Alada', 'Pequena (< 5 mm)', 'Amarela', 'Rugosa', 'Uma', 'Ereto', 'Lenhoso', 'Rugosa', 'Castanho-escuro', 'Cilíndrico', NULL, 'Grosso (> 5 cm)', 'Simpodial', 'Não', 'Não', 'Sim', 'Sim', '1. CENTRO NACIONAL DE CONSERVAÇÃO DA FLORA (CNCFlora). Myracrodruon urundeuva Allemão. In: Lista Vermelha da Flora Brasileira. Jardim Botânico do Rio de Janeiro, 2012. Disponível em: http://cncflora.jbrj.gov.br/portal/pt-br/profile/Myracrodruon%20urundeuva\n2. CÂMARA DOS DEPUTADOS. Aroeira - Myracrodruon urundeuva Allemão. Bosque dos Constituintes. Brasília, DF. Disponível em: https://www2.camara.leg.br/a-camara/estruturaadm/gestao-na-camara-dos-deputados/responsabilidade-social-e-ambiental/ecocamara/recursos/bosque-dos-constituintes/flora-local/as-20-especies-originais/aroeira\n3. PAREYN, F. G. C. et al. Myracrodruon urundeuva: Aroeira. In: Espécies Nativas da Caatinga. Embrapa/APT, 2018. Disponível em: http://www.infoteca.cnptia.embrapa.br/infoteca/handle/doc/1103471\n4. WIKIPÉDIA. Myracrodruon urundeuva. Disponível em: https://pt.wikipedia.org/wiki/Myracrodruon_urundeuva\n5. ESALQ/USP. Aroeira - Myracrodruon urundeuva Allemão. Madeira de Lei. Disponível em: http://www.esalq.usp.br/trilhas/lei/lei10.htm\n6. EMBRAPA PANTANAL. Myracrodruon urundeuva (Engl.) Fr.All. Plantas do Pantanal. Disponível em: https://www.cpap.embrapa.br/plantas/ficha.php?especie=Myracrodruon+urundeuva+(Engl.)+Fr.All.\n7. FLORA E FUNGA DO BRASIL. Myracrodruon urundeuva M.Allemão. Jardim Botânico do Rio de Janeiro. Disponível em: https://floradobrasil.jbrj.gov.br\n8. MATOS, M. V. S. Estudo químico e avaliação dos potenciais antitumoral e antibacteriano dos exudatos de Myracrodruon urundeuva. Trabalho de Conclusão de Curso. UFMS, 2024. Disponível em: https://repositorio.ufms.br/handle/123456789/8848', 1, '2026-02-12 10:22:26', '1,2,5,6', '5', '2', '2,3,5', '3', '5', '2', '5', '5', '3', '3', '2,3', '2,5,6', '2,3', '3,5', '3,5', '3', '3', '2,6', '2', '2', '3', '2', '2', '3,6', '3,6', '2,3', '1,2', '2,3', '5', '', '2', '1,6', '1', '2,8', '1,2,4,7'),
(8, 9, 'Oenocarpus bacaba Mart.', 'Bacaba, Bacaba-açu, Bacaba-verdadeira, Bacaba-de-leque, Bacaba-do-azeite, Bacabaí, Bacabão, Bacaba-vermelha, Bacabina, Bacabinha, Bacabeira, Tandi-bacaba, Yandi-bacaba, Ungurahui, Camon, Koemboe, Manoco, Punáma, Turu palm', 'Arecaceae', 'Pinada', 'Alterna', 'Composta pinnada', 'Macrófilos (20–50 cm)', 'Glabra', 'Inteira', 'Paralela', 'Amarelo-claro', 'Actinomorfa (simetria radial)', '3 pétalas', 'Inflorescência (cacho, espiga, capítulo, umbela)', 'Aroma suave', 'Pequena', 'Drupa', 'Pequeno (< 2 cm)', 'Roxo', 'Lisa', 'Zoocórica (por animais)', 'Aroma suave', 'Dura', 'Pequena (< 5 mm)', 'Marrom', 'Fibrosa', 'Uma', 'Ereto', 'Lenhoso', 'Lisa', 'Cinza', 'Cilíndrico', NULL, 'Médio (1–5 cm)', 'Monopodial', 'Não', 'Não', 'Sim', 'Não', '1. WIKIPÉDIA. Bacaba – Oenocarpus bacaba. Disponível em: https://pt.wikipedia.org/wiki/Bacaba\n2. EMBRAPA. Bacaba (Oenocarpus bacaba Mart.). Portal Embrapa. Disponível em: https://www.embrapa.br\n3. QUEIROZ, M. S. M.; BIANCO, R. Morfologia e desenvolvimento germinativo de Oenocarpus bacaba mart. (arecaceae) da Amazônia Ocidental. Revista Árvore, v. 33, n. 6, p. 1037-1042, 2009.\n4. WIKIPÉDIA. Oenocarpus bacaba. Disponível em: https://en.wikipedia.org/wiki/Oenocarpus_bacaba\n5. UNIVERSIDADE FEDERAL DO AMAZONAS. Bacaba – Flora Econômica. Disponível em: https://florestas.ufam.edu.br/floraeconomica/especies/bacaba/\n6. TODA FRUTA. Bacaba. Disponível em: https://www.todafruta.com.br/bacaba/\n7. PLANTS OF THE WORLD ONLINE. Oenocarpus bacaba Mart. Royal Botanic Gardens, Kew. Disponível em: https://powo.science.kew.org/taxon/urn:lsid:ipni.org:names:668509-1', 1, '2026-02-12 10:23:53', '1,4,6', '4', '4', '1,4', '4', '4', '4', '4', '5', '4', '4', '4,5', '1,4', '5', '1,4,6', '1,4', '1,4', '1,6', '4,5', '4', '5', '4', '5', '5', '4,5', '2,6', '2,5', '1,6', '5', '1,5', '', '1,2', '4', '4', '4', '1,4,7'),
(9, 10, 'Oenocarpus distichus Mart.', 'Bacaba-de-leque, Bacaba-de-azeite, Bacaba branca, Bacaba palm, Pataua, White bacaba, Bacaba de abanico', 'Arecaceae', 'Pinada', 'Dística', 'Composta pinnada', 'Macrófilos (20–50 cm)', 'Glabra', 'Inteira', 'Paralela', 'Amarelo-claro', 'Actinomorfa (simetria radial)', '3 pétalas', 'Inflorescência (cacho, espiga, capítulo, umbela)', 'Aroma suave', 'Pequena', 'Drupa', 'Pequeno (< 2 cm)', 'Roxo', 'Lisa', 'Zoocórica (por animais)', 'Aroma suave', 'Dura', 'Pequena (< 5 mm)', 'Marrom', 'Lisa', 'Uma', 'Ereto', 'Lenhoso', 'Lisa', 'Cinza', 'Cilíndrico', NULL, 'Médio (1–5 cm)', 'Monopodial', 'Não', 'Não', 'Sim', 'Não', '1. GOVAERTS, R. et al. World Checklist of Arecaceae – Oenocarpus distichus. Royal Botanic Gardens, Kew, 2004.\n2. SILVA, S. F. da; OLIVEIRA, M. do S. P. de. Caracterização molecular preliminar em acessos de bacaba-de-azeite (Oenocarpus distichus). Embrapa Amazônia Oriental, 2013.\n3. FLORA E FUNGA DO BRASIL. Oenocarpus distichus Mart. Jardim Botânico do Rio de Janeiro. Disponível em: http://floradobrasil.jbrj.gov.br\n4. WIKIPÉDIA. Oenocarpus distichus. Disponível em: https://en.wikipedia.org/wiki/Oenocarpus_distichus\n5. PLANTS FOR A FUTURE (PFAF). Oenocarpus distichus - Mart. Disponível em: https://pfaf.org/user/Plant.aspx?LatinName=Oenocarpus_distichus\n6. CLAY, J.; SAMPAIO, P. T. B.; CLEMENT, C. R. Biodiversidade amazônica: exemplos e estratégias de utilização. Manaus: INPA/SEBRAE, 2000. p. 72.', 1, '2026-02-12 10:24:56', '1,3,6', '2,4', '2,4', '1,4', '4', '4', '4', '4', '5', '4', '4', '4,5', '6', '5', '1,6', '6', '4', '6', '4,5', '6', '6', '6', '6', '6', '4,5', '2,6', '2,6', '4', '4', '4', '', '4', '4', '4', '4', '1,3,4'),
(10, 12, 'Mauritia flexuosa L.f.', 'Buriti, Miriti, Muriti, Aguaje, Moriche, Canangucho, Morete, Ité, Ita, Caranday-guazu, Palma real, Boriti, Guaish, Mirisi, Achu, Ite palm, Wine palm, Aeta palm, Eita palm, Tibisiri, Mochila, Dauri, Diwita, Eú', 'Arecaceae', 'Orbicular', 'Alterna', 'Composta Palmada', 'Megafilas (> 50 cm)', 'Coriácea', 'Serrada', 'Paralela', 'Creme', 'Actinomorfa (simetria radial)', '3 pétalas', 'Inflorescência (cacho, espiga, capítulo, umbela)', 'Sem cheiro', 'Pequena', 'Drupa', 'Médio (2–5 cm)', 'Marrom', 'Coriácea', 'Hidrocórica (pela água)', 'Sem cheiro', 'Dura', 'Média (5–10 mm)', 'Marrom', 'Fibrosa', 'Uma', 'Ereto', 'Lenhoso', 'Lisa', 'Cinza', 'Cilíndrico', NULL, 'Grosso (> 5 cm)', 'Monopodial', 'Não', 'Não', 'Sim', 'Não', '1. NATIONAL PARKS BOARD SINGAPORE. Mauritia flexuosa L.f. Flora & Fauna Web, 2024. Disponível em: https://www.nparks.gov.sg/florafaunaweb/flora/7/7/7798\n2. WIKIPÉDIA. Mauritia flexuosa. Disponível em: https://en.wikipedia.org/wiki/Mauritia_flexuosa\n3. PLANTS FOR A FUTURE (PFAF). Mauritia flexuosa - L.f. Disponível em: https://pfaf.org/user/Plant.aspx?LatinName=Mauritia+flexuosa\n4. ECOPORT. Mauritia flexuosa. FAO, 1993. Disponível em: http://icppgr.ecoport.org/ep?Plant=7639\n5. INTEGRATED TAXONOMIC INFORMATION SYSTEM (ITIS). Mauritia flexuosa L. f. Taxonomic Serial No.: 506729. Disponível em: https://www.itis.gov/servlet/SingleRpt/SingleRpt?search_topic=TSN&search_value=506729\n6. VIRAPONGSE, A. et al. Ecology, livelihoods, and management of Mauritia flexuosa. ScienceDirect, 2017. Disponível em: https://www.sciencedirect.com/topics/agricultural-and-biological-sciences/mauritia-flexuosa\n7. KEW GARDENS. Mauritia flexuosa L.f. - Herbarium Catalogue Specimen. Royal Botanic Gardens, Kew, 1849.\n8. PLANTS OF THE WORLD ONLINE. Mauritia flexuosa L.f. Royal Botanic Gardens, Kew. Disponível em: https://powo.science.kew.org/taxon/urn:lsid:ipni.org:names:668158-1', 1, '2026-02-12 10:27:05', '1,3,5,6', '1,6', '6', '1,6', '1,6', '6', '1', '6', '1,6', '6', '1,6', '1,6', '3', '1,6', '1,3,6', '1,3', '1,3', '1,6', '2', '3', '1,3', '3', '1', '6', '1,2,6', '1,6', '3,6', '1', '1', '1,6', '1,6', '1', '1,6', '2,3', '3', '1,3,5,8');

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

--
-- Extraindo dados da tabela `imagens_especies`
--

INSERT INTO `imagens_especies` (`id`, `especie_id`, `parte`, `caminho_imagem`, `descricao`, `localizacao`, `data_coleta`, `observacoes`, `metadados`, `id_usuario_identificador`, `id_usuario_confirmador`, `status_validacao`, `data_upload`) VALUES
(2, 102, 'folha', 'uploads/exsicatas/102/102_folha_20260212_230238_606.jpeg', 'folha frontal', 'morro paxixi', '2026-02-12', 'teste', NULL, 1, NULL, 'pendente', '2026-02-12 18:02:38');

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
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha_hash`, `categoria`, `subtipo_colaborador`, `bio`, `status_verificacao`, `ativo`, `data_cadastro`) VALUES
(1, 'João Silva', 'joao@email.com', 'e10adc3949ba59abbe56e057f20f883e', 'colaborador', 'identificador', NULL, 'verificado', 1, '2026-02-12 18:02:29');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `historico_alteracoes`
--
ALTER TABLE `historico_alteracoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `imagens_especies`
--
ALTER TABLE `imagens_especies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `sugestoes_usuario`
--
ALTER TABLE `sugestoes_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
