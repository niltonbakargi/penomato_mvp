-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 08/04/2026 às 11:12
-- Versão do servidor: 8.0.45-36
-- Versão do PHP: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `b62a4147_penomato`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `artigos`
--

CREATE TABLE `artigos` (
  `id` int NOT NULL,
  `especie_id` int NOT NULL,
  `texto_html` longtext COLLATE utf8mb4_general_ci,
  `status` enum('rascunho','em_revisao','aprovado','publicado') COLLATE utf8mb4_general_ci DEFAULT 'rascunho',
  `gerado_em` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `revisado_por` int DEFAULT NULL,
  `data_revisao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `artigos`
--

INSERT INTO `artigos` (`id`, `especie_id`, `texto_html`, `status`, `gerado_em`, `atualizado_em`, `revisado_por`, `data_revisao`) VALUES
(1, 32, '<div class=\"artigo\"><h2 class=\"art-titulo\">Acca sellowiana (O.Berg) Burret<sup>1</sup></h2><p class=\"art-familia\"><strong>Família:</strong> Myrtaceae<sup>1</sup></p><p class=\"art-sinonimos\"><strong>Sinonímia:</strong> <em>Feijoa sellowiana O.Berg</em>, <em>Orthostemon sellowianus O.Berg</em>, <em>Feijoa obovata O.Berg</em><sup>1,2</sup></p><p class=\"art-nomes\"><strong>Nomes populares:</strong> goiabeira-serrana, feijoa, goiaba-serrana, goiaba-do-mato, goiaba-abacaxi<sup>2,3</sup></p><h3 class=\"art-secao\">Descrição</h3><p class=\"art-paragrafo\">Caule Ereto<sup>2,3</sup>, Lenhoso<sup>2,3</sup>, Cilíndrico<sup>2</sup>, diâmetro médio<sup>3</sup>, com coloração marrom<sup>3</sup>, textura rugosa<sup>3</sup>, ramificação simpodial<sup>2</sup>, estolão, desprovido de espinhos<sup>2</sup>, látex ausente<sup>2</sup>, resina ausente<sup>2</sup>.</p><p class=\"art-paragrafo\">Folhas Simples<sup>2,3</sup>, Oposta Decussada<sup>2,3</sup>, de forma elíptica<sup>2,3</sup>, textura coriácea<sup>2,3</sup>, margem inteira<sup>2,3</sup>, venação peninérvea<sup>2,3</sup>, tamanho nanofilos (2–7 cm)<sup>3</sup>.</p><p class=\"art-paragrafo\">Flores Isoladas<sup>2,3</sup>, Actinomorfa<sup>2</sup>, com 4 pétalas<sup>2,3</sup>, de coloração vermelhas<sup>2,3</sup>, tamanho média<sup>3</sup>, aroma sem cheiro<sup>3</sup>.</p><p class=\"art-paragrafo\">Fruto do tipo baga<sup>1,2,3</sup>, médio<sup>3</sup>, de coloração verde<sup>2,3</sup>, textura lisa<sup>3</sup>, aroma aroma suave<sup>3</sup>, dispersão zoocórica<sup>3</sup>.</p><p class=\"art-paragrafo\">Sementes Carnosa<sup>2,3</sup>, pequena<sup>3</sup>, de coloração marrom<sup>3</sup>, textura lisa<sup>3</sup>, muitas sementes por fruto<sup>2,3</sup>.</p><h3 class=\"art-secao\">Prancha Fotográfica</h3><div class=\"art-galeria\"><figure class=\"art-figura\"><img src=\"/penomato_mvp/uploads/exsicatas/32/habito_20260320_145134_524.png\" alt=\"habito\"><figcaption>Habito — https://www.inaturalist.org/people/johnfairlie (CC BY-NC 4.0)<br><small>Fonte: <a href=\"https://www.inaturalist.org/observations/275008879\" target=\"_blank\">inaturalist</a></small></figcaption></figure><figure class=\"art-figura\"><img src=\"/penomato_mvp/uploads/exsicatas/32/folha_20260320_144000_286.png\" alt=\"folha\"><figcaption>Folha — https://www.inaturalist.org/people/billpranty (CC BY-NC 4.0)<br><small>Fonte: <a href=\"https://www.inaturalist.org/observations/342676250\" target=\"_blank\">inaturalist</a></small></figcaption></figure><figure class=\"art-figura\"><img src=\"/penomato_mvp/uploads/exsicatas/32/flor_20260320_144102_429.png\" alt=\"flor\"><figcaption>Flor — https://www.inaturalist.org/people/rossettib95 (CC BY-NC 4.0)<br><small>Fonte: <a href=\"https://www.inaturalist.org/observations/341210397\" target=\"_blank\">inaturalist</a></small></figcaption></figure><figure class=\"art-figura\"><img src=\"/penomato_mvp/uploads/exsicatas/32/fruto_20260320_143750_467.png\" alt=\"fruto\"><figcaption>Fruto — https://www.inaturalist.org/people/kimlk59 (CC BY-NC 4.0)<br><small>Fonte: <a href=\"https://www.inaturalist.org/observations/343281747\" target=\"_blank\">inaturalist</a></small></figcaption></figure><figure class=\"art-figura\"><img src=\"/penomato_mvp/uploads/exsicatas/32/caule_20260320_145033_737.png\" alt=\"caule\"><figcaption>Caule — https://www.inaturalist.org/people/carina1122 (CC BY-NC 4.0)<br><small>Fonte: <a href=\"https://www.inaturalist.org/observations/256136178\" target=\"_blank\">inaturalist</a></small></figcaption></figure><figure class=\"art-figura\"><img src=\"/penomato_mvp/uploads/exsicatas/32/semente_20260320_144229_973.png\" alt=\"semente\"><figcaption>Semente — https://www.inaturalist.org/people/renacuajo3 (CC BY-NC 4.0)<br><small>Fonte: <a href=\"https://www.inaturalist.org/observations/338206865\" target=\"_blank\">inaturalist</a></small></figcaption></figure></div><h3 class=\"art-secao\">Referências</h3><ol class=\"art-refs\"><li id=\"ref-1\">BURRET, M. Myrtaceen-Studien. Notizblatt des Botanischen Gartens und Museums zu Berlin-Dahlem. Berlin: Botanischer Garten,</li><li id=\"ref-2\">LEGRAND, C. D.; KLEIN, R. M. Mirtáceas. In: REITZ, R. (ed.). Flora Ilustrada Catarinense. Itajaí: Herbário Barbosa Rodrigues,</li><li id=\"ref-3\">MATTOS, J. R. Fruteiras nativas do Brasil. Porto Alegre: Nobel, 1989.</li></ol></div><!-- .artigo -->', 'rascunho', '2026-03-20 14:08:06', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `especies_administrativo`
--

CREATE TABLE `especies_administrativo` (
  `id` int NOT NULL,
  `nome_cientifico` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('sem_dados','dados_internet','descrita','registrada','em_revisao','revisada','contestado','publicado') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `data_dados_internet` datetime DEFAULT NULL,
  `data_descrita` datetime DEFAULT NULL,
  `data_registrada` datetime DEFAULT NULL,
  `data_revisada` datetime DEFAULT NULL,
  `data_contestado` datetime DEFAULT NULL,
  `data_publicado` datetime DEFAULT NULL,
  `autor_dados_internet_id` int DEFAULT NULL,
  `autor_descrita_id` int DEFAULT NULL,
  `autor_registrada_id` int DEFAULT NULL,
  `autor_revisada_id` int DEFAULT NULL,
  `autor_contestado_id` int DEFAULT NULL,
  `autor_publicado_id` int DEFAULT NULL,
  `motivo_contestado` text COLLATE utf8mb4_general_ci,
  `data_primeiro_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_revisao` datetime DEFAULT NULL,
  `observacoes_revisao` text COLLATE utf8mb4_general_ci,
  `prioridade` enum('baixa','media','alta','urgente') COLLATE utf8mb4_general_ci DEFAULT 'media',
  `atribuido_a` int DEFAULT NULL,
  `data_criacao` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `versao_registro` int DEFAULT '1',
  `observacoes` text COLLATE utf8mb4_general_ci,
  `data_ultima_atualizacao` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `especies_administrativo`
--

INSERT INTO `especies_administrativo` (`id`, `nome_cientifico`, `status`, `data_dados_internet`, `data_descrita`, `data_registrada`, `data_revisada`, `data_contestado`, `data_publicado`, `autor_dados_internet_id`, `autor_descrita_id`, `autor_registrada_id`, `autor_revisada_id`, `autor_contestado_id`, `autor_publicado_id`, `motivo_contestado`, `data_primeiro_registro`, `data_revisao`, `observacoes_revisao`, `prioridade`, `atribuido_a`, `data_criacao`, `versao_registro`, `observacoes`, `data_ultima_atualizacao`) VALUES
(1, 'Persea americana', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(2, 'Anadenanthera colubrina', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(3, 'Anadenanthera macrocarpa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(4, 'Schinus terebinthifolia', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(5, 'Lithraea molleoides', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(6, 'Myracrodruon urundeuva', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(7, 'Oenocarpus bacaba', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(8, 'Oenocarpus distichus', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(9, 'Acrocomia aculeata', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(10, 'Mauritia flexuosa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(11, 'Peltophorum dubium', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(12, 'Vellozia squamata', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(13, 'Syngonanthus nitens', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(14, 'Terminalia argentea', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(15, 'Copernicia alba', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(16, 'Solanum paniculatum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(17, 'Jacaranda caroba', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(18, 'Roupala montana', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(19, 'Machaerium villosum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(20, 'Cedrela fissilis', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(21, 'Alchornea triplinervia', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(22, 'Mabea fistulifera', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(23, 'Vochysia divergens', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(24, 'Zygia racemosa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(25, 'Zygia latifoliolata', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(26, 'Cecropia pachystachya', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(27, 'Enterolobium gummiferum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(28, 'Pseudobombax longiflorum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(29, 'Dimorphandra mollis', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(30, 'Apuleia leiocarpa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(31, 'Cordia leucocephala', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(32, 'Acca sellowiana', 'dados_internet', '2026-03-20 10:08:06', NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-20 10:08:06'),
(33, 'Myrcianthes pungens', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(34, 'Casearia sylvestris', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(35, 'Cupania vernalis', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(36, 'Patagonula americana', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(37, 'Goupia glabra', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(38, 'Schizolobium parahyba', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(39, 'Schizolobium amazonicum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(40, 'Aspidosperma spruceanum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(41, 'Campomanesia spp.', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(42, 'Inga laurina', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(43, 'Inga edulis', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(44, 'Inga sessilis', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(45, 'Stryphnodendron adstringens', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(46, 'Handroanthus albus', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(47, 'Mezilaurus itauba', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(48, 'Jacaranda mimosifolia', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(49, 'Triplaris surinamensis', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(50, 'Hymenaea oblongifolia', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(51, 'Hymenaea intermedia', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(52, 'Hymenaea stigonocarpa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(53, 'Genipa americana', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(54, 'Cariniana legalis', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(55, 'Ziziphus joazeiro', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(56, 'Mimosa tenuiflora', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(57, 'Lafoensia pacari', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(58, 'Solanum lycocarpum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(59, 'Cordia glabrata', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(60, 'Cordia alliodora', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(61, 'Hancornia speciosa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(62, 'Laguncularia racemosa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(63, 'Calophyllum brasiliense', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(64, 'Diospyros hispida', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(65, 'Calycophyllum spruceanum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(66, 'Byrsonima crassifolia', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(67, 'Licania tomentosa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(68, 'Enterolobium contortisiliquum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(69, 'Bauhinia ungulata', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(70, 'Gallesia integrifolia', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(71, 'Caesalpinia leiostachya', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(72, 'Triplaris gardneriana', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(73, 'Alchornea glandulosa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(74, 'Libidibia ferrea', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(75, 'Piptadenia gonoacantha', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(76, 'Tapirira guianensis', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(77, 'Zanthoxylum rhoifolium', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(78, 'Caryocar brasiliense', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(79, 'Caryocar villosum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(80, 'Aspidosperma polyneuron', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(81, 'Podocarpus sellowii', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(82, 'Eugenia uniflora', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(83, 'Tibouchina granulosa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(84, 'Mimosa caesalpiniifolia', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(85, 'Pterodon spp.', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(86, 'Pterodon emarginatus', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(87, 'Senegalia polyphylla', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(88, 'Vitex cymosa', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(89, 'Vitex polygama', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(90, 'Atropha belladona', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(91, 'Dipteryx odorata', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(92, 'Ateleia glazioviana', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(93, 'Plathymenia reticulata', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(94, 'Bixa orellana', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(95, 'Bowdichia virgilioides', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(96, 'Eugenia pyriformis', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(97, 'Salacia crassifolia', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51'),
(98, 'Combretum lanceolatum', 'sem_dados', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 11:38:51', NULL, NULL, 'media', NULL, '2026-03-17 14:38:51', 1, NULL, '2026-03-17 11:38:51');

-- --------------------------------------------------------

--
-- Estrutura para tabela `especies_caracteristicas`
--

CREATE TABLE `especies_caracteristicas` (
  `id` int NOT NULL,
  `especie_id` int NOT NULL,
  `nome_cientifico_completo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sinonimos` text COLLATE utf8mb4_general_ci,
  `sinonimos_ref` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nome_popular` text COLLATE utf8mb4_general_ci,
  `nome_popular_ref` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `familia` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `forma_folha` enum('Lanceolada','Linear','Elíptica','Ovada','Orbicular','Cordiforme','Espatulada','Sagitada','Reniforme','Obovada','Trilobada','Palmada','Lobada') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `filotaxia_folha` enum('Alterna','Oposta Simples','Oposta Decussada','Verticilada','Dística','Espiralada') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tipo_folha` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tamanho_folha` enum('Microfilas (< 2 cm)','Nanofilas (2–7 cm)','Mesofilas (7–20 cm)','Macrófilas (20–50 cm)','Megafilas (> 50 cm)') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `textura_folha` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `margem_folha` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `venacao_folha` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cor_flores` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `simetria_floral` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `numero_petalas` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `disposicao_flores` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `aroma` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tamanho_flor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tipo_fruto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tamanho_fruto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cor_fruto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `textura_fruto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dispersao_fruto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `aroma_fruto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tipo_semente` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tamanho_semente` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cor_semente` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `textura_semente` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quantidade_sementes` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tipo_caule` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estrutura_caule` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `textura_caule` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cor_caule` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `forma_caule` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `modificacao_caule` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `diametro_caule` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `diametro_caule_ref` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramificacao_caule` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ramificacao_caule_ref` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `possui_espinhos` enum('Sim','Não') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `possui_latex` enum('Sim','Não') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `possui_seiva` enum('Sim','Não') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `possui_resina` enum('Sim','Não') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `referencias` longtext COLLATE utf8mb4_general_ci,
  `versao_dados` int DEFAULT '1',
  `data_cadastro_botanico` datetime DEFAULT CURRENT_TIMESTAMP,
  `familia_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `forma_folha_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `filotaxia_folha_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tipo_folha_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tamanho_folha_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `textura_folha_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `margem_folha_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `venacao_folha_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cor_flores_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `simetria_floral_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `numero_petalas_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `disposicao_flores_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `aroma_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tamanho_flor_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tipo_fruto_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tamanho_fruto_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cor_fruto_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `textura_fruto_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `dispersao_fruto_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `aroma_fruto_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tipo_semente_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tamanho_semente_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cor_semente_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `textura_semente_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quantidade_sementes_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tipo_caule_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estrutura_caule_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `textura_caule_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cor_caule_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `forma_caule_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `modificacao_caule_ref` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `possui_espinhos_ref` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `possui_latex_ref` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `possui_seiva_ref` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `possui_resina_ref` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nome_cientifico_completo_ref` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `especies_caracteristicas`
--

INSERT INTO `especies_caracteristicas` (`id`, `especie_id`, `nome_cientifico_completo`, `sinonimos`, `sinonimos_ref`, `nome_popular`, `nome_popular_ref`, `familia`, `forma_folha`, `filotaxia_folha`, `tipo_folha`, `tamanho_folha`, `textura_folha`, `margem_folha`, `venacao_folha`, `cor_flores`, `simetria_floral`, `numero_petalas`, `disposicao_flores`, `aroma`, `tamanho_flor`, `tipo_fruto`, `tamanho_fruto`, `cor_fruto`, `textura_fruto`, `dispersao_fruto`, `aroma_fruto`, `tipo_semente`, `tamanho_semente`, `cor_semente`, `textura_semente`, `quantidade_sementes`, `tipo_caule`, `estrutura_caule`, `textura_caule`, `cor_caule`, `forma_caule`, `modificacao_caule`, `diametro_caule`, `diametro_caule_ref`, `ramificacao_caule`, `ramificacao_caule_ref`, `possui_espinhos`, `possui_latex`, `possui_seiva`, `possui_resina`, `referencias`, `versao_dados`, `data_cadastro_botanico`, `familia_ref`, `forma_folha_ref`, `filotaxia_folha_ref`, `tipo_folha_ref`, `tamanho_folha_ref`, `textura_folha_ref`, `margem_folha_ref`, `venacao_folha_ref`, `cor_flores_ref`, `simetria_floral_ref`, `numero_petalas_ref`, `disposicao_flores_ref`, `aroma_ref`, `tamanho_flor_ref`, `tipo_fruto_ref`, `tamanho_fruto_ref`, `cor_fruto_ref`, `textura_fruto_ref`, `dispersao_fruto_ref`, `aroma_fruto_ref`, `tipo_semente_ref`, `tamanho_semente_ref`, `cor_semente_ref`, `textura_semente_ref`, `quantidade_sementes_ref`, `tipo_caule_ref`, `estrutura_caule_ref`, `textura_caule_ref`, `cor_caule_ref`, `forma_caule_ref`, `modificacao_caule_ref`, `possui_espinhos_ref`, `possui_latex_ref`, `possui_seiva_ref`, `possui_resina_ref`, `nome_cientifico_completo_ref`) VALUES
(1, 32, 'Acca sellowiana (O.Berg) Burret', 'Feijoa sellowiana O.Berg, Orthostemon sellowianus O.Berg, Feijoa obovata O.Berg', '1,2', 'goiabeira-serrana, feijoa, goiaba-serrana, goiaba-do-mato, goiaba-abacaxi', '2,3', 'Myrtaceae', 'Elíptica', 'Oposta Decussada', 'Simples', 'Nanofilas (2–7 cm)', 'Coriácea', 'Inteira', 'Peninérvea', 'Vermelhas', 'Actinomorfa', '4 pétalas', 'Isoladas', 'Sem cheiro', 'Média', 'Baga', 'Médio', 'Verde', 'Lisa', 'Zoocórica', 'Aroma suave', 'Carnosa', 'Pequena', 'Marrom', 'Lisa', 'Muitas', 'Ereto', 'Lenhoso', 'Rugosa', 'Marrom', 'Cilíndrico', 'Estolão', 'Médio', '3', 'Simpodial', '2', 'Não', 'Não', 'Sim', 'Não', '1. BURRET, M. Myrtaceen-Studien. Notizblatt des Botanischen Gartens und Museums zu Berlin-Dahlem. Berlin: Botanischer Garten, 1941.\r\n2. LEGRAND, C. D.; KLEIN, R. M. Mirtáceas. In: REITZ, R. (ed.). Flora Ilustrada Catarinense. Itajaí: Herbário Barbosa Rodrigues, 1969.\r\n3. MATTOS, J. R. Fruteiras nativas do Brasil. Porto Alegre: Nobel, 1989.', 1, '2026-03-20 10:08:06', '1', '2,3', '2,3', '2,3', '3', '2,3', '2,3', '2,3', '2,3', '2', '2,3', '2,3', '3', '3', '1,2,3', '3', '2,3', '3', '3', '3', '2,3', '3', '3', '3', '2,3', '2,3', '2,3', '3', '3', '2', NULL, '2', '2', '2', '2', '1');

-- --------------------------------------------------------

--
-- Estrutura para tabela `especies_imagens`
--

CREATE TABLE `especies_imagens` (
  `id` int NOT NULL,
  `especie_id` int NOT NULL,
  `exemplar_id` int DEFAULT NULL,
  `tipo_imagem` enum('provisoria','definitiva') COLLATE utf8mb4_general_ci NOT NULL,
  `origem` enum('internet','campo') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'internet',
  `numero_etiqueta` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `parte_planta` enum('folha','flor','fruto','caule','semente','habito','exsicata_completa','detalhe') COLLATE utf8mb4_general_ci NOT NULL,
  `caminho_imagem` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `nome_original` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tamanho_bytes` int DEFAULT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fonte_nome` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fonte_url` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `autor_imagem` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `licenca` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `principal` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = exibida no artigo; 0 = acervo para ML futuro',
  `descricao` text COLLATE utf8mb4_general_ci,
  `local_coleta` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `data_coleta` date DEFAULT NULL,
  `coletor_nome` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `coletor_id` int DEFAULT NULL,
  `metadados_exif` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `id_usuario_identificador` int NOT NULL,
  `id_usuario_validador` int DEFAULT NULL,
  `status_validacao` enum('pendente','aprovado','rejeitado') COLLATE utf8mb4_general_ci DEFAULT 'pendente',
  `data_validacao` date DEFAULT NULL,
  `motivo_rejeicao` text COLLATE utf8mb4_general_ci,
  `substituida_por` int DEFAULT NULL,
  `data_substituicao` date DEFAULT NULL,
  `data_upload` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `versao` int DEFAULT '1',
  `observacoes_internas` text COLLATE utf8mb4_general_ci
) ;

--
-- Despejando dados para a tabela `especies_imagens`
--

INSERT INTO `especies_imagens` (`id`, `especie_id`, `exemplar_id`, `tipo_imagem`, `origem`, `numero_etiqueta`, `parte_planta`, `caminho_imagem`, `nome_original`, `tamanho_bytes`, `mime_type`, `fonte_nome`, `fonte_url`, `autor_imagem`, `licenca`, `principal`, `descricao`, `local_coleta`, `data_coleta`, `coletor_nome`, `coletor_id`, `metadados_exif`, `id_usuario_identificador`, `id_usuario_validador`, `status_validacao`, `data_validacao`, `motivo_rejeicao`, `substituida_por`, `data_substituicao`, `data_upload`, `versao`, `observacoes_internas`) VALUES
(1, 32, NULL, 'provisoria', 'internet', NULL, 'fruto', 'uploads/exsicatas/32/fruto_20260320_143750_467.png', NULL, NULL, NULL, 'inaturalist', 'https://www.inaturalist.org/observations/343281747', 'https://www.inaturalist.org/people/kimlk59', 'CC BY-NC 4.0', 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'pendente', NULL, NULL, NULL, NULL, '2026-03-20 14:08:06', 1, NULL),
(2, 32, NULL, 'provisoria', 'internet', NULL, 'folha', 'uploads/exsicatas/32/folha_20260320_144000_286.png', NULL, NULL, NULL, 'inaturalist', 'https://www.inaturalist.org/observations/342676250', 'https://www.inaturalist.org/people/billpranty', 'CC BY-NC 4.0', 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'pendente', NULL, NULL, NULL, NULL, '2026-03-20 14:08:06', 1, NULL),
(3, 32, NULL, 'provisoria', 'internet', NULL, 'flor', 'uploads/exsicatas/32/flor_20260320_144102_429.png', NULL, NULL, NULL, 'inaturalist', 'https://www.inaturalist.org/observations/341210397', 'https://www.inaturalist.org/people/rossettib95', 'CC BY-NC 4.0', 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'pendente', NULL, NULL, NULL, NULL, '2026-03-20 14:08:06', 1, NULL),
(4, 32, NULL, 'provisoria', 'internet', NULL, 'semente', 'uploads/exsicatas/32/semente_20260320_144229_973.png', NULL, NULL, NULL, 'inaturalist', 'https://www.inaturalist.org/observations/338206865', 'https://www.inaturalist.org/people/renacuajo3', 'CC BY-NC 4.0', 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'pendente', NULL, NULL, NULL, NULL, '2026-03-20 14:08:06', 1, NULL),
(5, 32, NULL, 'provisoria', 'internet', NULL, 'caule', 'uploads/exsicatas/32/caule_20260320_145033_737.png', NULL, NULL, NULL, 'inaturalist', 'https://www.inaturalist.org/observations/256136178', 'https://www.inaturalist.org/people/carina1122', 'CC BY-NC 4.0', 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'pendente', NULL, NULL, NULL, NULL, '2026-03-20 14:08:06', 1, NULL),
(6, 32, NULL, 'provisoria', 'internet', NULL, 'habito', 'uploads/exsicatas/32/habito_20260320_145134_524.png', NULL, NULL, NULL, 'inaturalist', 'https://www.inaturalist.org/observations/275008879', 'https://www.inaturalist.org/people/johnfairlie', 'CC BY-NC 4.0', 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, 'pendente', NULL, NULL, NULL, NULL, '2026-03-20 14:08:06', 1, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `exemplares`
--

CREATE TABLE `exemplares` (
  `id` int NOT NULL,
  `codigo` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `especie_id` int NOT NULL,
  `numero_etiqueta` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto_identificacao` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `cidade` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `estado` char(2) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bioma` enum('Cerrado','Mata Atlântica','Pantanal','Caatinga','Amazônia','Pampa','Outro') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `descricao_local` text COLLATE utf8mb4_general_ci,
  `especialista_id` int NOT NULL,
  `cadastrado_por` int NOT NULL,
  `data_cadastro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('aguardando_revisao','aprovado','rejeitado') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'aguardando_revisao',
  `data_revisao` datetime DEFAULT NULL,
  `motivo_rejeicao` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `fila_aprovacao`
--

CREATE TABLE `fila_aprovacao` (
  `id` int NOT NULL,
  `tipo` enum('dados_internet','confirmacao','imagem','revisao','contestacao') COLLATE utf8mb4_general_ci NOT NULL,
  `subtipo` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `especie_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `descricao` text COLLATE utf8mb4_general_ci,
  `conteudo_atual` text COLLATE utf8mb4_general_ci,
  `conteudo_correto` text COLLATE utf8mb4_general_ci,
  `referencias` text COLLATE utf8mb4_general_ci,
  `observacoes` text COLLATE utf8mb4_general_ci,
  `status` enum('pendente','aprovado','rejeitado') COLLATE utf8mb4_general_ci DEFAULT 'pendente',
  `motivo_rejeicao` text COLLATE utf8mb4_general_ci,
  `data_submissao` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_decisao` datetime DEFAULT NULL,
  `gestor_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `historico_alteracoes`
--

CREATE TABLE `historico_alteracoes` (
  `id` int NOT NULL,
  `especie_id` int NOT NULL,
  `id_usuario` int NOT NULL,
  `tabela_afetada` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `campo_alterado` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `valor_anterior` text COLLATE utf8mb4_general_ci,
  `valor_novo` text COLLATE utf8mb4_general_ci,
  `tipo_acao` enum('insercao','edicao','revisao','contestacao','validacao','publicacao') COLLATE utf8mb4_general_ci NOT NULL,
  `justificativa` text COLLATE utf8mb4_general_ci,
  `data_alteracao` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `partes_dispensadas`
--

CREATE TABLE `partes_dispensadas` (
  `id` int NOT NULL,
  `especie_id` int NOT NULL,
  `parte_planta` enum('folha','flor','fruto','caule','semente','habito') COLLATE utf8mb4_general_ci NOT NULL,
  `motivo` text COLLATE utf8mb4_general_ci,
  `dispensado_por` int NOT NULL,
  `data_dispensa` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `sugestoes_usuario`
--

CREATE TABLE `sugestoes_usuario` (
  `id` int NOT NULL,
  `id_usuario` int NOT NULL,
  `nome_cientifico` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sugestao_caracteristicas` text COLLATE utf8mb4_general_ci,
  `outras_sugestoes` text COLLATE utf8mb4_general_ci,
  `status_sugestao` enum('recebida','em_analise','aprovada','rejeitada') COLLATE utf8mb4_general_ci DEFAULT 'recebida',
  `resposta_gestor` text COLLATE utf8mb4_general_ci,
  `data_envio` datetime DEFAULT CURRENT_TIMESTAMP,
  `data_avaliacao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `temp_imagens_candidatas`
--

CREATE TABLE `temp_imagens_candidatas` (
  `id` int NOT NULL,
  `especie_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `temp_id` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `parte_planta` enum('folha','flor','fruto','caule','semente','habito','exsicata_completa','detalhe') COLLATE utf8mb4_general_ci NOT NULL,
  `url_foto` varchar(2048) COLLATE utf8mb4_general_ci NOT NULL,
  `url_thumbnail` varchar(2048) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fonte` enum('inaturalist','wikimedia','gbif','outro') COLLATE utf8mb4_general_ci NOT NULL,
  `fonte_url` varchar(2048) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `fonte_nome` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_externo` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `autor` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `licenca` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `local_coleta` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `data_observacao` date DEFAULT NULL,
  `largura_px` int DEFAULT NULL,
  `altura_px` int DEFAULT NULL,
  `pontuacao` smallint DEFAULT '0',
  `status` enum('pendente','aprovado','rejeitado') COLLATE utf8mb4_general_ci DEFAULT 'pendente',
  `principal` tinyint(1) DEFAULT '0',
  `criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expira_em` datetime GENERATED ALWAYS AS ((`criado_em` + interval 24 hour)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tentativas_login`
--

CREATE TABLE `tentativas_login` (
  `id` int NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tentativas_login`
--

INSERT INTO `tentativas_login` (`id`, `ip`, `email`, `criado_em`) VALUES
(3, '170.150.242.163', 'niltonbakargi@penomato.app.br', '2026-03-24 10:17:23'),
(4, '170.150.242.163', 'niltonbakargi@penomato.app.br', '2026-03-24 10:19:06'),
(6, '170.83.98.71', 'niltonbakargi@penomato.app.br', '2026-03-24 18:17:38'),
(7, '170.83.98.71', 'niltonbakargi@penomato.app.br', '2026-03-24 18:17:43');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tokens_alteracao_email`
--

CREATE TABLE `tokens_alteracao_email` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `novo_email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_general_ci NOT NULL,
  `expira_em` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT '0',
  `criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tokens_recuperacao_senha`
--

CREATE TABLE `tokens_recuperacao_senha` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_general_ci NOT NULL,
  `expira_em` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT '0',
  `criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tokens_recuperacao_senha`
--

INSERT INTO `tokens_recuperacao_senha` (`id`, `usuario_id`, `token`, `expira_em`, `usado`, `criado_em`) VALUES
(1, 1, '2f77ec26e381e4fea2786645db822f8f09cf615bd8b94019f1665ffa53592087', '2026-03-25 10:20:32', 1, '2026-03-25 09:20:32'),
(2, 1, '614a77eb64a0c23889854451f23e9ed120ee410a11089297c83370e92c3f4800', '2026-03-25 10:41:39', 0, '2026-03-25 09:41:39');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tokens_verificacao_email`
--

CREATE TABLE `tokens_verificacao_email` (
  `id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_general_ci NOT NULL,
  `expira_em` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT '0',
  `criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tokens_verificacao_email`
--

INSERT INTO `tokens_verificacao_email` (`id`, `usuario_id`, `token`, `expira_em`, `usado`, `criado_em`) VALUES
(3, 4, '0f4aea84b46b98312301d085ff1137152e290be41d968af4de5a2218f6496b9c', '2026-03-26 08:12:31', 1, '2026-03-25 08:12:31');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `nome` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `senha_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `categoria` enum('gestor','colaborador','revisor','validador','visitante') COLLATE utf8mb4_general_ci DEFAULT 'visitante',
  `subtipo_colaborador` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_general_ci,
  `foto_perfil` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `instituicao` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lattes` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `orcid` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_verificacao` enum('pendente','verificado','bloqueado','aguardando_gestor') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'pendente',
  `ativo` tinyint(1) DEFAULT '1',
  `data_cadastro` datetime DEFAULT CURRENT_TIMESTAMP,
  `ultimo_acesso` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha_hash`, `categoria`, `subtipo_colaborador`, `bio`, `foto_perfil`, `instituicao`, `lattes`, `orcid`, `status_verificacao`, `ativo`, `data_cadastro`, `ultimo_acesso`) VALUES
(1, 'nilton dobes bakargi', 'nilton.bakargi@ufms.br', '$2y$10$uOmAOWhPui4X03BmJqiutOLdpg2xOlXmSoZjJm7L.h9bseiEOJvaG', 'gestor', 'gestor', NULL, NULL, 'auto', NULL, NULL, 'verificado', 1, '2026-03-17 15:23:53', '2026-04-08 10:18:07'),
(4, 'nbakargi', 'niltonbakargi@penomato.app.br', '$2y$10$xjK8HQnzMigqqkTWpxRhw.J3x1hkZ7iEBjT51XJnuLgnceSRqsf.u', 'colaborador', 'identificador', NULL, NULL, 'auto', NULL, NULL, 'verificado', 1, '2026-03-25 08:12:31', '2026-03-28 08:04:11');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `artigos`
--
ALTER TABLE `artigos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `especie_id` (`especie_id`),
  ADD KEY `revisado_por` (`revisado_por`);

--
-- Índices de tabela `especies_administrativo`
--
ALTER TABLE `especies_administrativo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome_cientifico` (`nome_cientifico`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_data_descrita` (`data_descrita`),
  ADD KEY `idx_data_registrada` (`data_registrada`),
  ADD KEY `idx_data_revisada` (`data_revisada`),
  ADD KEY `fk_atribuido` (`atribuido_a`);

--
-- Índices de tabela `especies_caracteristicas`
--
ALTER TABLE `especies_caracteristicas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_caracteristicas_especie` (`especie_id`),
  ADD KEY `idx_especies_caracteristicas_nome` (`nome_cientifico_completo`);

--
-- Índices de tabela `especies_imagens`
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
  ADD KEY `coletor_id` (`coletor_id`),
  ADD KEY `idx_exemplar` (`exemplar_id`);

--
-- Índices de tabela `exemplares`
--
ALTER TABLE `exemplares`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_codigo` (`codigo`),
  ADD KEY `idx_especie` (`especie_id`),
  ADD KEY `idx_especialista` (`especialista_id`),
  ADD KEY `idx_cadastrador` (`cadastrado_por`),
  ADD KEY `idx_status` (`status`);

--
-- Índices de tabela `fila_aprovacao`
--
ALTER TABLE `fila_aprovacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `especie_id` (`especie_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `gestor_id` (`gestor_id`);

--
-- Índices de tabela `historico_alteracoes`
--
ALTER TABLE `historico_alteracoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_historico_especie` (`especie_id`),
  ADD KEY `fk_historico_usuario` (`id_usuario`);

--
-- Índices de tabela `partes_dispensadas`
--
ALTER TABLE `partes_dispensadas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_especie_parte` (`especie_id`,`parte_planta`),
  ADD KEY `fk_dispensa_especie` (`especie_id`),
  ADD KEY `fk_dispensa_usuario` (`dispensado_por`);

--
-- Índices de tabela `sugestoes_usuario`
--
ALTER TABLE `sugestoes_usuario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sugestao_usuario` (`id_usuario`);

--
-- Índices de tabela `temp_imagens_candidatas`
--
ALTER TABLE `temp_imagens_candidatas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_temp_id` (`temp_id`),
  ADD KEY `idx_especie_parte` (`especie_id`,`parte_planta`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_expira_em` (`expira_em`),
  ADD KEY `fk_cand_usuario` (`usuario_id`);

--
-- Índices de tabela `tentativas_login`
--
ALTER TABLE `tentativas_login`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ip` (`ip`),
  ADD KEY `criado_em` (`criado_em`);

--
-- Índices de tabela `tokens_alteracao_email`
--
ALTER TABLE `tokens_alteracao_email`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `tokens_recuperacao_senha`
--
ALTER TABLE `tokens_recuperacao_senha`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `tokens_verificacao_email`
--
ALTER TABLE `tokens_verificacao_email`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_usuarios_categoria` (`categoria`),
  ADD KEY `idx_usuarios_status` (`status_verificacao`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `artigos`
--
ALTER TABLE `artigos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `especies_administrativo`
--
ALTER TABLE `especies_administrativo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT de tabela `especies_caracteristicas`
--
ALTER TABLE `especies_caracteristicas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `especies_imagens`
--
ALTER TABLE `especies_imagens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `exemplares`
--
ALTER TABLE `exemplares`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `fila_aprovacao`
--
ALTER TABLE `fila_aprovacao`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `historico_alteracoes`
--
ALTER TABLE `historico_alteracoes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `partes_dispensadas`
--
ALTER TABLE `partes_dispensadas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `sugestoes_usuario`
--
ALTER TABLE `sugestoes_usuario`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `temp_imagens_candidatas`
--
ALTER TABLE `temp_imagens_candidatas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tentativas_login`
--
ALTER TABLE `tentativas_login`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `tokens_alteracao_email`
--
ALTER TABLE `tokens_alteracao_email`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tokens_recuperacao_senha`
--
ALTER TABLE `tokens_recuperacao_senha`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `tokens_verificacao_email`
--
ALTER TABLE `tokens_verificacao_email`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `artigos`
--
ALTER TABLE `artigos`
  ADD CONSTRAINT `artigos_ibfk_1` FOREIGN KEY (`especie_id`) REFERENCES `especies_administrativo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `artigos_ibfk_2` FOREIGN KEY (`revisado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `especies_administrativo`
--
ALTER TABLE `especies_administrativo`
  ADD CONSTRAINT `fk_atribuido` FOREIGN KEY (`atribuido_a`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `especies_caracteristicas`
--
ALTER TABLE `especies_caracteristicas`
  ADD CONSTRAINT `fk_caracteristicas_especie` FOREIGN KEY (`especie_id`) REFERENCES `especies_administrativo` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `especies_imagens`
--
ALTER TABLE `especies_imagens`
  ADD CONSTRAINT `especies_imagens_ibfk_1` FOREIGN KEY (`especie_id`) REFERENCES `especies_administrativo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `especies_imagens_ibfk_2` FOREIGN KEY (`id_usuario_identificador`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `especies_imagens_ibfk_3` FOREIGN KEY (`id_usuario_validador`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `especies_imagens_ibfk_4` FOREIGN KEY (`coletor_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `especies_imagens_ibfk_5` FOREIGN KEY (`substituida_por`) REFERENCES `especies_imagens` (`id`),
  ADD CONSTRAINT `fk_imagem_exemplar` FOREIGN KEY (`exemplar_id`) REFERENCES `exemplares` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `exemplares`
--
ALTER TABLE `exemplares`
  ADD CONSTRAINT `fk_exemplar_cadastrador` FOREIGN KEY (`cadastrado_por`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_exemplar_especialista` FOREIGN KEY (`especialista_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `fk_exemplar_especie` FOREIGN KEY (`especie_id`) REFERENCES `especies_administrativo` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `fila_aprovacao`
--
ALTER TABLE `fila_aprovacao`
  ADD CONSTRAINT `fila_aprovacao_ibfk_1` FOREIGN KEY (`especie_id`) REFERENCES `especies_administrativo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fila_aprovacao_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fila_aprovacao_ibfk_3` FOREIGN KEY (`gestor_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `historico_alteracoes`
--
ALTER TABLE `historico_alteracoes`
  ADD CONSTRAINT `fk_historico_especie` FOREIGN KEY (`especie_id`) REFERENCES `especies_administrativo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_historico_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `partes_dispensadas`
--
ALTER TABLE `partes_dispensadas`
  ADD CONSTRAINT `fk_dispensa_especie` FOREIGN KEY (`especie_id`) REFERENCES `especies_administrativo` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_dispensa_usuario` FOREIGN KEY (`dispensado_por`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `sugestoes_usuario`
--
ALTER TABLE `sugestoes_usuario`
  ADD CONSTRAINT `fk_sugestao_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `temp_imagens_candidatas`
--
ALTER TABLE `temp_imagens_candidatas`
  ADD CONSTRAINT `fk_cand_especie` FOREIGN KEY (`especie_id`) REFERENCES `especies_administrativo` (`id`),
  ADD CONSTRAINT `fk_cand_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `tokens_alteracao_email`
--
ALTER TABLE `tokens_alteracao_email`
  ADD CONSTRAINT `fk_token_alt_email_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tokens_recuperacao_senha`
--
ALTER TABLE `tokens_recuperacao_senha`
  ADD CONSTRAINT `fk_token_recuperacao_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tokens_verificacao_email`
--
ALTER TABLE `tokens_verificacao_email`
  ADD CONSTRAINT `fk_token_verificacao_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
