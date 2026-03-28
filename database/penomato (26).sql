-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 20-Fev-2026 às 12:51
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
  `data_criacao` timestamp NULL DEFAULT current_timestamp(),
  `versao_registro` int(11) DEFAULT 1,
  `observacoes` text DEFAULT NULL,
  `data_ultima_atualizacao` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `especies_administrativo`
--

INSERT INTO `especies_administrativo` (`id`, `nome_cientifico`, `status`, `data_dados_internet`, `data_descrita`, `data_registrada`, `data_revisada`, `data_contestado`, `data_publicado`, `autor_dados_internet_id`, `autor_descrita_id`, `autor_registrada_id`, `autor_revisada_id`, `autor_contestado_id`, `autor_publicado_id`, `motivo_contestado`, `data_primeiro_registro`, `data_revisao`, `observacoes_revisao`, `prioridade`, `data_criacao`, `versao_registro`, `observacoes`, `data_ultima_atualizacao`) VALUES
(1, 'Persea americana', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(2, 'Andira spp.', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(3, 'Anadenanthera colubrina', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-20 07:14:07'),
(4, 'Anadenanthera macrocarpa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-20 07:14:07'),
(5, 'Annona spp.', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(6, 'Schinus terebinthifolius', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-20 07:14:07'),
(7, 'Lithraea molleoides', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-20 07:14:07'),
(8, 'Myracrodruon urundeuva', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-20 07:14:07'),
(9, 'Oenocarpus bacaba', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-20 07:14:07'),
(10, 'Oenocarpus distichus', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-20 07:14:07'),
(11, 'Acrocomia aculeata', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-20 07:14:07'),
(12, 'Mauritia flexuosa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-20 07:14:07'),
(13, 'Vochysia spp.', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(14, 'Peltophorum dubium', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(15, 'Vellozia squamata', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(16, 'Syngonanthus nitens', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(17, 'Terminalia argentea', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(18, 'Copernicia alba', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(19, 'Solanum paniculatum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(20, 'Jacaranda caroba', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(21, 'Roupala montana', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(22, 'Machaerium villosum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(23, 'Cedrela fissilis', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(24, 'Alchornea triplinervia', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(25, 'Mabea fistulifera', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(26, 'Philodendron spp.', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(27, 'Copaifera spp.', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(28, 'Vochysia divergens', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(29, 'Zygia racemosa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(30, 'Zygia latifoliolata', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(31, 'Byrsonima spp.', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(32, 'Cecropia spp.', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(33, 'Cecropia pachystachya', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(34, 'Enterolobium gummiferum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(35, 'Pseudobombax longiflorum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(36, 'Dimorphandra mollis', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(37, 'Ficus spp.', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(38, 'Apuleia leiocarpa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(39, 'Cordia leucocephala', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(40, 'Acca sellowiana', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-20 07:14:07'),
(41, 'Myrcianthes pungens', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(42, 'Casearia sylvestris', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(43, 'Cupania vernalis', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(44, 'Patagonula americana', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(45, 'Goupia glabra', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(46, 'Schizolobium parahyba', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(47, 'Schizolobium amazonicum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(48, 'Aspidosperma spp.', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(49, 'Aspidosperma spruceanum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(50, 'Campomanesia spp.', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(51, 'Inga spp.', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(52, 'Inga laurina', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(53, 'Inga edulis', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(54, 'Inga sessilis', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(55, 'Stryphnodendron adstringens', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(56, 'Tabebuia spp.', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(57, 'Handroanthus albus', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(58, 'Mezilaurus itauba', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(59, 'Jacaranda mimosifolia', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(60, 'Triplaris surinamensis', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(61, 'Hymenaea spp.', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(62, 'Hymenaea oblongifolia', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(63, 'Hymenaea intermedia', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(64, 'Hymenaea stigonocarpa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(65, 'Genipa americana', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(66, 'Cariniana legalis', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(67, 'Ziziphus joazeiro', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(68, 'Mimosa tenuiflora', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(69, 'Lafoensia pacari', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(70, 'Solanum lycocarpum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(71, 'Cordia glabrata', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(72, 'Cordia alliodora', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(73, 'Hancornia speciosa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(74, 'Laguncularia racemosa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(75, 'Calophyllum brasiliense', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(76, 'Diospyros hispida', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(77, 'Calycophyllum spruceanum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(78, 'Byrsonima crassifolia', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(79, 'Myrciaria spp.', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(80, 'Licania tomentosa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(81, 'Enterolobium contortisiliquum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(82, 'Bauhinia spp.', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(83, 'Bauhinia ungulata', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(84, 'Gallesia integrifolia', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(85, 'Caesalpinia leiostachya', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(86, 'Triplaris gardneriana', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(87, 'Alchornea glandulosa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(88, 'Caesalpinia ferrea', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(89, 'Piptadenia gonoacantha', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(90, 'Tapirira guianensis', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(91, 'Zanthoxylum rhoifolium', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(92, 'Qualea spp.', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(93, 'Caryocar brasiliense', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(94, 'Caryocar villosum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(95, 'Aspidosperma polyneuron', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(96, 'Podocarpus sellowii', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(97, 'Eugenia uniflora', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(98, 'Tibouchina granulosa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(99, 'Mimosa caesalpiniifolia', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(100, 'Pterodon spp.', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(101, 'Pterodon emarginatus', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(102, 'Acacia polyphylla', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-12 18:02:38'),
(103, 'Enterolobium timbouva', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(104, 'Vitex cymosa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(105, 'Vitex polygama', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(106, 'Atropha belladona', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(107, 'Dipteryx odorata', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(108, 'Ateleia glazioviana', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(109, 'Plathymenia reticulata', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(110, 'Bixa orellana', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(111, 'Bowdichia virgilioides', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(112, 'Eugenia pyriformis', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(113, 'Salacia crassifolia', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(114, 'Combretum lanceolatum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(115, 'Vernonia polyanthes', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(116, 'Scoparia dulcis', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40'),
(117, 'Sterculia striata', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 11:00:58', NULL, NULL, 'media', '2026-02-09 19:32:40', 1, NULL, '2026-02-09 15:32:40');

-- --------------------------------------------------------

--
-- Estrutura da tabela `especies_caracteristicas`
--

CREATE TABLE `especies_caracteristicas` (
  `id` int(11) NOT NULL,
  `especie_id` int(11) NOT NULL,
  `nome_cientifico_completo` varchar(255) DEFAULT NULL,
  `sinonimos` text DEFAULT NULL,
  `sinonimos_ref` varchar(255) DEFAULT NULL,
  `nome_popular` text DEFAULT NULL,
  `nome_popular_ref` varchar(255) DEFAULT NULL,
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
  `diametro_caule_ref` varchar(255) DEFAULT NULL,
  `ramificacao_caule` varchar(255) DEFAULT NULL,
  `ramificacao_caule_ref` varchar(255) DEFAULT NULL,
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
-- Estrutura da tabela `especies_imagens`
--

CREATE TABLE `especies_imagens` (
  `id` int(11) NOT NULL,
  `especie_id` int(11) NOT NULL,
  `tipo_imagem` enum('provisoria','definitiva') NOT NULL,
  `parte_planta` enum('folha','flor','fruto','caule','semente','habito','exsicata_completa','detalhe') NOT NULL,
  `caminho_imagem` varchar(500) NOT NULL,
  `nome_original` varchar(255) DEFAULT NULL,
  `tamanho_bytes` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `fonte_nome` varchar(255) DEFAULT NULL,
  `fonte_url` varchar(500) DEFAULT NULL,
  `autor_imagem` varchar(255) DEFAULT NULL,
  `licenca` varchar(100) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `local_coleta` varchar(255) DEFAULT NULL,
  `data_coleta` date DEFAULT NULL,
  `coletor_nome` varchar(255) DEFAULT NULL,
  `coletor_id` int(11) DEFAULT NULL,
  `metadados_exif` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadados_exif`)),
  `id_usuario_identificador` int(11) NOT NULL,
  `id_usuario_validador` int(11) DEFAULT NULL,
  `status_validacao` enum('pendente','aprovado','rejeitado') DEFAULT 'pendente',
  `data_validacao` date DEFAULT NULL,
  `motivo_rejeicao` text DEFAULT NULL,
  `substituida_por` int(11) DEFAULT NULL,
  `data_substituicao` date DEFAULT NULL,
  `data_upload` timestamp NOT NULL DEFAULT current_timestamp(),
  `versao` int(11) DEFAULT 1,
  `observacoes_internas` text DEFAULT NULL
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
  `categoria` enum('gestor','colaborador','revisor','visitante') DEFAULT 'visitante',
  `subtipo_colaborador` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `status_verificacao` enum('pendente','verificado','bloqueado') DEFAULT 'pendente',
  `ativo` tinyint(1) DEFAULT 1,
  `data_cadastro` datetime DEFAULT current_timestamp(),
  `ultimo_acesso` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha_hash`, `categoria`, `subtipo_colaborador`, `bio`, `status_verificacao`, `ativo`, `data_cadastro`, `ultimo_acesso`) VALUES
(1, 'nilton dobes bakargi', 'nilton.bakargi@ufms.br', '$2y$10$4V6cqKfUMakvnDlkvQivn.7qmbWsPr/zQk3pTix8actr7mc5Bscmq', 'gestor', '', NULL, 'pendente', 1, '2026-02-17 22:23:06', '2026-02-18 09:12:02');

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
-- Índices para tabela `especies_imagens`
--
ALTER TABLE `especies_imagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_especie` (`especie_id`),
  ADD KEY `idx_tipo` (`tipo_imagem`),
  ADD KEY `idx_parte` (`parte_planta`),
  ADD KEY `idx_status` (`status_validacao`),
  ADD KEY `idx_substituicao` (`substituida_por`),
  ADD KEY `id_usuario_identificador` (`id_usuario_identificador`),
  ADD KEY `id_usuario_validador` (`id_usuario_validador`),
  ADD KEY `coletor_id` (`coletor_id`);

--
-- Índices para tabela `historico_alteracoes`
--
ALTER TABLE `historico_alteracoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_historico_especie` (`especie_id`),
  ADD KEY `fk_historico_usuario` (`id_usuario`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT de tabela `especies_caracteristicas`
--
ALTER TABLE `especies_caracteristicas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `especies_imagens`
--
ALTER TABLE `especies_imagens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `historico_alteracoes`
--
ALTER TABLE `historico_alteracoes`
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
-- Limitadores para a tabela `especies_imagens`
--
ALTER TABLE `especies_imagens`
  ADD CONSTRAINT `especies_imagens_ibfk_1` FOREIGN KEY (`especie_id`) REFERENCES `especies_administrativo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `especies_imagens_ibfk_2` FOREIGN KEY (`id_usuario_identificador`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `especies_imagens_ibfk_3` FOREIGN KEY (`id_usuario_validador`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `especies_imagens_ibfk_4` FOREIGN KEY (`coletor_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `especies_imagens_ibfk_5` FOREIGN KEY (`substituida_por`) REFERENCES `especies_imagens` (`id`);

--
-- Limitadores para a tabela `historico_alteracoes`
--
ALTER TABLE `historico_alteracoes`
  ADD CONSTRAINT `fk_historico_especie` FOREIGN KEY (`especie_id`) REFERENCES `especies_administrativo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_historico_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Limitadores para a tabela `sugestoes_usuario`
--
ALTER TABLE `sugestoes_usuario`
  ADD CONSTRAINT `fk_sugestao_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
