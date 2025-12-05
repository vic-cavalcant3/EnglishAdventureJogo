-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 02/12/2025 às 01:14
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `englishadventure`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `cristais_conquistados`
--

CREATE TABLE `cristais_conquistados` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `cristal_tipo` varchar(50) NOT NULL COMMENT 'Ex: cristal_replay',
  `data_conquista` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `estrelas`
--

CREATE TABLE `estrelas` (
  `id` int(11) NOT NULL,
  `nomeAluno` varchar(100) NOT NULL,
  `atividade` varchar(100) DEFAULT NULL,
  `total_estrelas` int(11) DEFAULT 0,
  `acertou` int(11) NOT NULL,
  `dataRegistro` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `estrelas`
--

INSERT INTO `estrelas` (`id`, `nomeAluno`, `atividade`, `total_estrelas`, `acertou`, `dataRegistro`) VALUES
(112, 'Victor cavalcante', 'fase1_atividade1', 1, 1, '2025-12-01 22:34:46'),
(113, 'Victor cavalcante', 'fase1_atividade2', 1, 1, '2025-12-01 22:34:58'),
(114, 'Victor cavalcante', 'fase1_atividade3', 1, 1, '2025-12-01 22:35:01');

-- --------------------------------------------------------

--
-- Estrutura para tabela `fases_jogadas`
--

CREATE TABLE `fases_jogadas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `jogo` int(11) NOT NULL COMMENT '1=Learning, 2=Jogo2, 3=Jogo3, 4=Jogo4',
  `fase` int(11) NOT NULL COMMENT 'Número da fase (1-10)',
  `primeira_vez` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultima_jogada` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `fases_jogadas`
--

INSERT INTO `fases_jogadas` (`id`, `usuario_id`, `jogo`, `fase`, `primeira_vez`, `ultima_jogada`) VALUES
(126, 8, 1, 3, '2025-12-01 22:34:41', '2025-12-01 22:34:41'),
(127, 8, 1, 4, '2025-12-01 22:34:56', '2025-12-01 22:34:56'),
(128, 8, 1, 5, '2025-12-01 22:34:59', '2025-12-01 22:35:19'),
(131, 8, 2, 1, '2025-12-01 22:35:23', '2025-12-01 22:35:23'),
(132, 8, 2, 2, '2025-12-01 22:35:27', '2025-12-01 22:35:27'),
(133, 8, 2, 3, '2025-12-01 22:35:31', '2025-12-01 22:35:31'),
(134, 8, 2, 4, '2025-12-01 22:35:39', '2025-12-01 22:35:39'),
(135, 8, 2, 5, '2025-12-01 22:35:44', '2025-12-01 22:35:44'),
(136, 8, 2, 6, '2025-12-01 22:35:49', '2025-12-01 22:35:49'),
(137, 8, 2, 7, '2025-12-01 22:35:53', '2025-12-01 22:35:53'),
(138, 8, 2, 8, '2025-12-01 22:35:56', '2025-12-01 22:35:56'),
(139, 8, 2, 9, '2025-12-01 22:36:02', '2025-12-01 22:36:02'),
(140, 8, 2, 10, '2025-12-01 22:36:05', '2025-12-01 22:36:05'),
(141, 8, 3, 1, '2025-12-01 22:36:16', '2025-12-01 22:36:16'),
(142, 8, 3, 2, '2025-12-01 22:36:21', '2025-12-01 22:36:21'),
(143, 8, 3, 3, '2025-12-01 22:36:24', '2025-12-01 22:36:24'),
(144, 8, 3, 4, '2025-12-01 22:36:27', '2025-12-01 22:36:27'),
(145, 8, 3, 5, '2025-12-01 22:36:32', '2025-12-01 22:36:32'),
(146, 8, 3, 6, '2025-12-01 22:37:33', '2025-12-01 22:37:33'),
(147, 8, 3, 7, '2025-12-01 22:37:36', '2025-12-01 22:37:36'),
(148, 8, 3, 8, '2025-12-01 22:37:42', '2025-12-01 22:37:42'),
(149, 8, 3, 9, '2025-12-01 22:37:49', '2025-12-01 22:37:49'),
(150, 8, 3, 10, '2025-12-01 22:37:56', '2025-12-01 22:37:56');

-- --------------------------------------------------------

--
-- Estrutura para tabela `fase_estrelas`
--

CREATE TABLE `fase_estrelas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fase` int(11) NOT NULL,
  `estrelas` int(11) NOT NULL DEFAULT 0,
  `data_conquista` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `fase_estrelas`
--

INSERT INTO `fase_estrelas` (`id`, `usuario_id`, `fase`, `estrelas`, `data_conquista`) VALUES
(41, 8, 1, 0, '2025-12-01 22:35:27'),
(42, 8, 2, 0, '2025-12-01 22:35:31'),
(43, 8, 3, 0, '2025-12-01 22:35:38'),
(44, 8, 4, 0, '2025-12-01 22:35:44'),
(45, 8, 5, 0, '2025-12-01 22:35:49'),
(46, 8, 6, 0, '2025-12-01 22:35:53'),
(47, 8, 7, 0, '2025-12-01 22:35:56'),
(48, 8, 8, 0, '2025-12-01 22:36:01'),
(49, 8, 9, 0, '2025-12-01 22:36:05'),
(50, 8, 10, 0, '2025-12-01 22:36:10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `fase_estrelas2`
--

CREATE TABLE `fase_estrelas2` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fase` int(11) NOT NULL,
  `estrelas` int(11) DEFAULT 0,
  `dataRegistro` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `fase_estrelas3`
--

CREATE TABLE `fase_estrelas3` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fase` int(11) NOT NULL,
  `estrelas` int(11) DEFAULT 0,
  `dataRegistro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `fase_estrelas3`
--

INSERT INTO `fase_estrelas3` (`id`, `usuario_id`, `fase`, `estrelas`, `dataRegistro`) VALUES
(12, 8, 1, 0, '2025-12-01 22:41:19'),
(13, 8, 2, 1, '2025-12-01 22:41:24'),
(14, 8, 3, 1, '2025-12-01 22:41:28'),
(15, 8, 4, 1, '2025-12-01 22:41:37'),
(16, 8, 5, 2, '2025-12-01 22:41:47'),
(17, 8, 6, 2, '2025-12-01 22:41:53'),
(18, 8, 7, 2, '2025-12-01 22:41:55'),
(19, 8, 8, 2, '2025-12-01 22:45:45'),
(20, 8, 9, 3, '2025-12-01 22:46:28'),
(21, 8, 10, 3, '2025-12-01 22:46:32');

-- --------------------------------------------------------

--
-- Estrutura para tabela `jogo`
--

CREATE TABLE `jogo` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `xp` int(11) DEFAULT 0,
  `estrelas` int(11) DEFAULT 0,
  `pagina_atual` int(11) DEFAULT 1,
  `fase_atual` int(11) DEFAULT 1,
  `xp_jogo3` int(11) DEFAULT 0,
  `xp_total` int(11) DEFAULT 0 COMMENT 'Soma do XP dos 3 jogos'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `jogo`
--

INSERT INTO `jogo` (`id`, `nome`, `xp`, `estrelas`, `pagina_atual`, `fase_atual`, `xp_jogo3`, `xp_total`) VALUES
(11, 'Victor cavalcante', 0, 3, 1, 1, 0, 8);

-- --------------------------------------------------------

--
-- Estrutura para tabela `progresso_detalhado`
--

CREATE TABLE `progresso_detalhado` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nomeAluno` varchar(100) NOT NULL,
  `fase` int(11) NOT NULL,
  `atividade` varchar(100) NOT NULL,
  `tipo_gramatica` enum('afirmativa','interrogativa','negativa') NOT NULL,
  `tipo_habilidade` enum('speaking','reading','listening','writing','choice','writing') NOT NULL,
  `acertou` tinyint(1) DEFAULT 0,
  `tentativas` int(11) DEFAULT 1,
  `dataRegistro` timestamp NOT NULL DEFAULT current_timestamp(),
  `dataUltimaAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `progresso_detalhado`
--

INSERT INTO `progresso_detalhado` (`id`, `usuario_id`, `nomeAluno`, `fase`, `atividade`, `tipo_gramatica`, `tipo_habilidade`, `acertou`, `tentativas`, `dataRegistro`, `dataUltimaAtualizacao`) VALUES
(86, 8, 'Victor cavalcante', 1, 'jogo2_fase1', 'afirmativa', 'choice', 1, 1, '2025-12-01 22:35:26', '2025-12-01 22:35:26'),
(87, 8, 'Victor cavalcante', 2, 'jogo2_fase2', 'afirmativa', 'choice', 0, 1, '2025-12-01 22:35:29', '2025-12-01 22:35:29'),
(88, 8, 'Victor cavalcante', 3, 'jogo2_fase3', 'afirmativa', 'speaking', 0, 1, '2025-12-01 22:35:38', '2025-12-01 22:35:38'),
(89, 8, 'Victor cavalcante', 4, 'jogo2_fase4', 'afirmativa', 'writing', 1, 1, '2025-12-01 22:35:44', '2025-12-01 22:35:44'),
(90, 8, 'Victor cavalcante', 5, 'jogo2_fase5', 'afirmativa', 'listening', 1, 1, '2025-12-01 22:35:49', '2025-12-01 22:35:49'),
(91, 8, 'Victor cavalcante', 6, 'jogo2_fase6', 'afirmativa', 'listening', 1, 1, '2025-12-01 22:35:52', '2025-12-01 22:35:52'),
(92, 8, 'Victor cavalcante', 7, 'jogo2_fase7', 'afirmativa', 'choice', 1, 1, '2025-12-01 22:35:55', '2025-12-01 22:35:55'),
(93, 8, 'Victor cavalcante', 8, 'jogo2_fase8', 'afirmativa', 'choice', 1, 1, '2025-12-01 22:36:01', '2025-12-01 22:36:01'),
(94, 8, 'Victor cavalcante', 9, 'jogo2_fase9', 'afirmativa', 'choice', 1, 1, '2025-12-01 22:36:04', '2025-12-01 22:36:04'),
(95, 8, 'Victor cavalcante', 10, 'jogo2_fase10', 'afirmativa', 'speaking', 1, 1, '2025-12-01 22:36:10', '2025-12-01 22:36:10'),
(96, 8, 'Victor cavalcante', 1, 'jogo3_fase1', 'negativa', 'choice', 1, 1, '2025-12-01 22:36:20', '2025-12-01 22:36:20'),
(97, 8, 'Victor cavalcante', 2, 'jogo3_fase2', 'negativa', 'choice', 1, 1, '2025-12-01 22:36:24', '2025-12-01 22:36:24'),
(98, 8, 'Victor cavalcante', 3, 'jogo3_fase3', 'negativa', 'choice', 1, 1, '2025-12-01 22:36:27', '2025-12-01 22:36:27'),
(99, 8, 'Victor cavalcante', 4, 'jogo3_fase4', 'negativa', 'writing', 0, 1, '2025-12-01 22:36:31', '2025-12-01 22:36:31'),
(100, 8, 'Victor cavalcante', 5, 'jogo3_fase5', 'negativa', 'choice', 1, 1, '2025-12-01 22:37:32', '2025-12-01 22:37:32'),
(101, 8, 'Victor cavalcante', 6, 'jogo3_fase6', 'negativa', 'listening', 1, 1, '2025-12-01 22:37:36', '2025-12-01 22:37:36'),
(102, 8, 'Victor cavalcante', 7, 'jogo3_fase7', 'negativa', 'listening', 1, 1, '2025-12-01 22:37:41', '2025-12-01 22:37:41'),
(103, 8, 'Victor cavalcante', 8, 'jogo3_fase8', 'negativa', 'speaking', 1, 1, '2025-12-01 22:37:48', '2025-12-01 22:37:48'),
(104, 8, 'Victor cavalcante', 9, 'jogo3_fase9', 'negativa', 'speaking', 0, 1, '2025-12-01 22:37:55', '2025-12-01 22:37:55'),
(105, 8, 'Victor cavalcante', 10, 'jogo3_fase10', 'negativa', 'speaking', 1, 1, '2025-12-01 22:38:10', '2025-12-01 22:38:10'),
(106, 8, 'Victor cavalcante', 1, 'jogo4_fase1', 'interrogativa', 'listening', 1, 1, '2025-12-01 22:41:19', '2025-12-01 22:41:19'),
(107, 8, 'Victor cavalcante', 2, 'jogo4_fase2', 'interrogativa', 'reading', 1, 1, '2025-12-01 22:41:23', '2025-12-01 22:41:23'),
(108, 8, 'Victor cavalcante', 3, 'jogo4_fase3', 'afirmativa', 'writing', 1, 1, '2025-12-01 22:41:29', '2025-12-01 22:41:29'),
(109, 8, 'Victor cavalcante', 4, 'jogo4_fase4', 'interrogativa', 'writing', 1, 1, '2025-12-01 22:41:37', '2025-12-01 22:41:37'),
(110, 8, 'Victor cavalcante', 5, 'jogo4_fase5', 'interrogativa', 'speaking', 1, 1, '2025-12-01 22:41:47', '2025-12-01 22:41:47'),
(111, 8, 'Victor cavalcante', 6, 'jogo4_fase6', 'interrogativa', 'reading', 1, 1, '2025-12-01 22:41:52', '2025-12-01 22:41:52'),
(112, 8, 'Victor cavalcante', 7, 'jogo4_fase7', 'interrogativa', 'reading', 1, 1, '2025-12-01 22:41:55', '2025-12-01 22:41:55'),
(113, 8, 'Victor cavalcante', 8, 'jogo4_fase8', 'interrogativa', 'speaking', 1, 2, '2025-12-01 22:42:04', '2025-12-01 22:45:45'),
(114, 8, 'Victor cavalcante', 9, 'jogo4_fase9', 'interrogativa', 'speaking', 1, 1, '2025-12-01 22:46:28', '2025-12-01 22:46:28'),
(115, 8, 'Victor cavalcante', 10, 'jogo4_fase10', 'interrogativa', 'writing', 1, 1, '2025-12-01 22:46:32', '2025-12-01 22:46:32');

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `progresso_usuarios`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `progresso_usuarios` (
`nomeAluno` varchar(100)
,`atividades_completas` bigint(21)
,`total_acertos` decimal(32,0)
,`ultima_atividade` timestamp
);

-- --------------------------------------------------------

--
-- Estrutura para tabela `resumo_progresso`
--

CREATE TABLE `resumo_progresso` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nomeAluno` varchar(100) NOT NULL,
  `afirmativa_total` int(11) DEFAULT 0,
  `afirmativa_acertos` int(11) DEFAULT 0,
  `interrogativa_total` int(11) DEFAULT 0,
  `interrogativa_acertos` int(11) DEFAULT 0,
  `negativa_total` int(11) DEFAULT 0,
  `negativa_acertos` int(11) DEFAULT 0,
  `speaking_total` int(11) DEFAULT 0,
  `speaking_acertos` int(11) DEFAULT 0,
  `reading_total` int(11) DEFAULT 0,
  `reading_acertos` int(11) DEFAULT 0,
  `listening_total` int(11) DEFAULT 0,
  `listening_acertos` int(11) DEFAULT 0,
  `writing_total` int(11) DEFAULT 0,
  `writing_acertos` int(11) DEFAULT 0,
  `choice_total` int(11) DEFAULT 0,
  `choice_acertos` int(11) DEFAULT 0,
  `dataAtualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `resumo_progresso`
--

INSERT INTO `resumo_progresso` (`id`, `usuario_id`, `nomeAluno`, `afirmativa_total`, `afirmativa_acertos`, `interrogativa_total`, `interrogativa_acertos`, `negativa_total`, `negativa_acertos`, `speaking_total`, `speaking_acertos`, `reading_total`, `reading_acertos`, `listening_total`, `listening_acertos`, `writing_total`, `writing_acertos`, `choice_total`, `choice_acertos`, `dataAtualizacao`) VALUES
(464, 8, 'Victor cavalcante', 11, 9, 9, 9, 10, 8, 8, 6, 3, 3, 5, 5, 5, 4, 9, 8, '2025-12-01 22:46:32');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tokens_recuperacao`
--

CREATE TABLE `tokens_recuperacao` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(6) NOT NULL,
  `expiracao` datetime NOT NULL,
  `usado` tinyint(1) DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `telefone`, `data_cadastro`) VALUES
(8, 'Victor cavalcante', 'victorrocha0223@gmail.com', '$2y$10$iNZYYjnEKPr34S42d5iXPOU.W7C7uUYHRrHByqrLzQJSG0Tw1.pdy', NULL, '2025-12-01 21:49:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `xp_jogo1`
--

CREATE TABLE `xp_jogo1` (
  `usuario_id` int(11) NOT NULL,
  `nomeAluno` varchar(100) DEFAULT NULL,
  `fase1_xp` int(11) DEFAULT 0,
  `fase2_xp` int(11) DEFAULT 0,
  `fase3_xp` int(11) DEFAULT 0,
  `fase4_xp` int(11) DEFAULT 0,
  `fase5_xp` int(11) DEFAULT 0,
  `fase6_xp` int(11) DEFAULT 0,
  `fase7_xp` int(11) DEFAULT 0,
  `fase8_xp` int(11) DEFAULT 0,
  `fase9_xp` int(11) DEFAULT 0,
  `fase10_xp` int(11) DEFAULT 0,
  `total_xp` int(11) DEFAULT 0,
  `dataRegistro` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `xp_jogo1`
--

INSERT INTO `xp_jogo1` (`usuario_id`, `nomeAluno`, `fase1_xp`, `fase2_xp`, `fase3_xp`, `fase4_xp`, `fase5_xp`, `fase6_xp`, `fase7_xp`, `fase8_xp`, `fase9_xp`, `fase10_xp`, `total_xp`, `dataRegistro`) VALUES
(8, 'Victor cavalcante', 1, 0, 0, 1, 1, 1, 1, 1, 1, 1, 8, '2025-12-01 22:36:10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `xp_jogo2`
--

CREATE TABLE `xp_jogo2` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nomeAluno` varchar(100) NOT NULL,
  `fase1_xp` int(11) DEFAULT 0,
  `fase2_xp` int(11) DEFAULT 0,
  `fase3_xp` int(11) DEFAULT 0,
  `fase4_xp` int(11) DEFAULT 0,
  `fase5_xp` int(11) DEFAULT 0,
  `fase6_xp` int(11) DEFAULT 0,
  `fase7_xp` int(11) DEFAULT 0,
  `fase8_xp` int(11) DEFAULT 0,
  `fase9_xp` int(11) DEFAULT 0,
  `fase10_xp` int(11) DEFAULT 0,
  `total_xp` int(11) DEFAULT 0,
  `dataRegistro` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `xp_jogo2`
--

INSERT INTO `xp_jogo2` (`id`, `usuario_id`, `nomeAluno`, `fase1_xp`, `fase2_xp`, `fase3_xp`, `fase4_xp`, `fase5_xp`, `fase6_xp`, `fase7_xp`, `fase8_xp`, `fase9_xp`, `fase10_xp`, `total_xp`, `dataRegistro`) VALUES
(25, 8, 'Victor cavalcante', 4, 4, 4, 0, 4, 4, 4, 4, 0, 4, 24, '2025-12-01 22:38:09');

-- --------------------------------------------------------

--
-- Estrutura para tabela `xp_jogo3`
--

CREATE TABLE `xp_jogo3` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `nomeAluno` varchar(100) NOT NULL,
  `fase1_xp` int(11) DEFAULT 0,
  `fase2_xp` int(11) DEFAULT 0,
  `fase3_xp` int(11) DEFAULT 0,
  `fase4_xp` int(11) DEFAULT 0,
  `fase5_xp` int(11) DEFAULT 0,
  `fase6_xp` int(11) DEFAULT 0,
  `fase7_xp` int(11) DEFAULT 0,
  `fase8_xp` int(11) DEFAULT 0,
  `fase9_xp` int(11) NOT NULL,
  `fase10_xp` int(11) NOT NULL,
  `total_xp` int(11) DEFAULT 0,
  `dataRegistro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `xp_jogo3`
--

INSERT INTO `xp_jogo3` (`id`, `usuario_id`, `nomeAluno`, `fase1_xp`, `fase2_xp`, `fase3_xp`, `fase4_xp`, `fase5_xp`, `fase6_xp`, `fase7_xp`, `fase8_xp`, `fase9_xp`, `fase10_xp`, `total_xp`, `dataRegistro`) VALUES
(13, 8, 'Victor cavalcante', 5, 5, 5, 5, 5, 5, 5, 5, 5, 5, 45, '2025-12-01 22:46:32');

-- --------------------------------------------------------

--
-- Estrutura para view `progresso_usuarios`
--
DROP TABLE IF EXISTS `progresso_usuarios`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `progresso_usuarios`  AS SELECT `estrelas`.`nomeAluno` AS `nomeAluno`, count(distinct `estrelas`.`atividade`) AS `atividades_completas`, sum(`estrelas`.`acertou`) AS `total_acertos`, max(`estrelas`.`dataRegistro`) AS `ultima_atividade` FROM `estrelas` GROUP BY `estrelas`.`nomeAluno` ;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `cristais_conquistados`
--
ALTER TABLE `cristais_conquistados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_cristal` (`usuario_id`,`cristal_tipo`);

--
-- Índices de tabela `estrelas`
--
ALTER TABLE `estrelas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `fases_jogadas`
--
ALTER TABLE `fases_jogadas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_jogo_fase` (`usuario_id`,`jogo`,`fase`);

--
-- Índices de tabela `fase_estrelas`
--
ALTER TABLE `fase_estrelas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_fase` (`usuario_id`,`fase`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `fase_estrelas2`
--
ALTER TABLE `fase_estrelas2`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_fase` (`usuario_id`,`fase`);

--
-- Índices de tabela `fase_estrelas3`
--
ALTER TABLE `fase_estrelas3`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`,`fase`);

--
-- Índices de tabela `jogo`
--
ALTER TABLE `jogo`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `progresso_detalhado`
--
ALTER TABLE `progresso_detalhado`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_atividade` (`usuario_id`,`atividade`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `resumo_progresso`
--
ALTER TABLE `resumo_progresso`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `tokens_recuperacao`
--
ALTER TABLE `tokens_recuperacao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `xp_jogo1`
--
ALTER TABLE `xp_jogo1`
  ADD PRIMARY KEY (`usuario_id`);

--
-- Índices de tabela `xp_jogo2`
--
ALTER TABLE `xp_jogo2`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `xp_jogo3`
--
ALTER TABLE `xp_jogo3`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario` (`usuario_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `cristais_conquistados`
--
ALTER TABLE `cristais_conquistados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `estrelas`
--
ALTER TABLE `estrelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT de tabela `fases_jogadas`
--
ALTER TABLE `fases_jogadas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT de tabela `fase_estrelas`
--
ALTER TABLE `fase_estrelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de tabela `fase_estrelas2`
--
ALTER TABLE `fase_estrelas2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de tabela `fase_estrelas3`
--
ALTER TABLE `fase_estrelas3`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de tabela `jogo`
--
ALTER TABLE `jogo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `progresso_detalhado`
--
ALTER TABLE `progresso_detalhado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT de tabela `resumo_progresso`
--
ALTER TABLE `resumo_progresso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=495;

--
-- AUTO_INCREMENT de tabela `tokens_recuperacao`
--
ALTER TABLE `tokens_recuperacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `xp_jogo2`
--
ALTER TABLE `xp_jogo2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de tabela `xp_jogo3`
--
ALTER TABLE `xp_jogo3`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `cristais_conquistados`
--
ALTER TABLE `cristais_conquistados`
  ADD CONSTRAINT `cristais_conquistados_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `fases_jogadas`
--
ALTER TABLE `fases_jogadas`
  ADD CONSTRAINT `fases_jogadas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `fase_estrelas`
--
ALTER TABLE `fase_estrelas`
  ADD CONSTRAINT `fase_estrelas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `fase_estrelas2`
--
ALTER TABLE `fase_estrelas2`
  ADD CONSTRAINT `fase_estrelas2_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `progresso_detalhado`
--
ALTER TABLE `progresso_detalhado`
  ADD CONSTRAINT `progresso_detalhado_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `resumo_progresso`
--
ALTER TABLE `resumo_progresso`
  ADD CONSTRAINT `resumo_progresso_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `tokens_recuperacao`
--
ALTER TABLE `tokens_recuperacao`
  ADD CONSTRAINT `tokens_recuperacao_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `xp_jogo1`
--
ALTER TABLE `xp_jogo1`
  ADD CONSTRAINT `xp_jogo1_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `xp_jogo2`
--
ALTER TABLE `xp_jogo2`
  ADD CONSTRAINT `xp_jogo2_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
