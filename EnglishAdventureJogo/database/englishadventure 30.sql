-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 30/11/2025 às 06:17
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
(90, 'victor', 'fase1_atividade1', 0, 1, '2025-11-30 02:51:06'),
(91, 'victor', 'fase1_atividade2', 0, 1, '2025-11-30 02:51:08'),
(92, 'victor', 'fase1_atividade3', 0, 1, '2025-11-30 02:51:09');

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
(11, 7, 1, 0, '2025-11-30 02:51:28'),
(12, 7, 2, 0, '2025-11-30 02:51:39'),
(13, 7, 3, 0, '2025-11-30 02:51:50'),
(14, 7, 4, 0, '2025-11-30 02:51:59'),
(15, 7, 5, 0, '2025-11-30 02:52:10'),
(16, 7, 6, 0, '2025-11-30 02:52:28'),
(17, 7, 7, 0, '2025-11-30 02:52:46'),
(18, 7, 8, 1, '2025-11-30 03:21:14'),
(19, 7, 9, 3, '2025-11-30 03:47:58'),
(20, 7, 10, 1, '2025-11-30 03:45:17');

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

--
-- Despejando dados para a tabela `fase_estrelas2`
--

INSERT INTO `fase_estrelas2` (`id`, `usuario_id`, `fase`, `estrelas`, `dataRegistro`) VALUES
(8, 7, 1, 3, '2025-11-30 04:30:56'),
(9, 7, 2, 2, '2025-11-30 04:14:31'),
(10, 7, 3, 1, '2025-11-30 03:44:19'),
(11, 7, 4, 1, '2025-11-30 03:44:24'),
(12, 7, 5, 1, '2025-11-30 03:20:45'),
(13, 7, 6, 1, '2025-11-30 03:21:02'),
(14, 7, 7, 1, '2025-11-30 03:21:09'),
(15, 7, 10, 2, '2025-11-30 03:49:57');

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
(1, 7, 1, 0, '2025-11-30 04:59:39'),
(2, 7, 2, 1, '2025-11-30 04:59:41'),
(3, 7, 3, 1, '2025-11-30 04:59:45'),
(4, 7, 4, 3, '2025-11-30 05:05:44'),
(5, 7, 5, 2, '2025-11-30 04:59:59'),
(6, 7, 6, 2, '2025-11-30 05:00:03'),
(7, 7, 7, 2, '2025-11-30 05:00:05'),
(8, 7, 8, 3, '2025-11-30 05:00:11'),
(9, 7, 9, 3, '2025-11-30 05:06:56'),
(10, 7, 10, 1, '2025-11-30 05:06:29');

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
(5, 'victor', 0, 3, 1, 1, 0, 22);

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
(56, 7, 'victor', 1, 'jogo2_fase1', 'afirmativa', 'choice', 1, 3, '2025-11-30 02:51:28', '2025-11-30 03:41:52'),
(57, 7, 'victor', 2, 'jogo2_fase2', 'afirmativa', 'choice', 1, 3, '2025-11-30 02:51:39', '2025-11-30 03:41:54'),
(58, 7, 'victor', 3, 'jogo2_fase3', 'afirmativa', 'speaking', 1, 3, '2025-11-30 02:51:49', '2025-11-30 03:42:01'),
(59, 7, 'victor', 4, 'jogo2_fase4', 'afirmativa', 'writing', 1, 3, '2025-11-30 02:51:59', '2025-11-30 03:42:05'),
(60, 7, 'victor', 5, 'jogo2_fase5', 'afirmativa', 'listening', 1, 3, '2025-11-30 02:52:09', '2025-11-30 03:42:09'),
(61, 7, 'victor', 6, 'jogo2_fase6', 'afirmativa', 'listening', 1, 3, '2025-11-30 02:52:27', '2025-11-30 03:42:12'),
(62, 7, 'victor', 7, 'jogo2_fase7', 'afirmativa', 'choice', 1, 3, '2025-11-30 02:52:46', '2025-11-30 03:42:14'),
(63, 7, 'victor', 8, 'jogo2_fase8', 'afirmativa', 'choice', 1, 3, '2025-11-30 02:52:51', '2025-11-30 03:42:19'),
(64, 7, 'victor', 9, 'jogo2_fase9', 'afirmativa', 'choice', 1, 3, '2025-11-30 02:53:01', '2025-11-30 03:42:22'),
(65, 7, 'victor', 10, 'jogo2_fase10', 'afirmativa', 'speaking', 1, 3, '2025-11-30 02:53:06', '2025-11-30 03:42:29'),
(66, 7, 'victor', 1, 'jogo3_fase1', 'negativa', 'listening', 1, 8, '2025-11-30 03:19:41', '2025-11-30 04:15:00'),
(67, 7, 'victor', 2, 'jogo3_fase2', 'negativa', 'choice', 1, 5, '2025-11-30 03:20:23', '2025-11-30 04:14:32'),
(68, 7, 'victor', 3, 'jogo3_fase3', 'negativa', 'choice', 1, 3, '2025-11-30 03:20:28', '2025-11-30 03:47:03'),
(69, 7, 'victor', 4, 'jogo3_fase4', 'negativa', 'writing', 1, 3, '2025-11-30 03:20:38', '2025-11-30 03:47:08'),
(70, 7, 'victor', 5, 'jogo3_fase5', 'negativa', 'choice', 1, 3, '2025-11-30 03:20:45', '2025-11-30 03:47:11'),
(71, 7, 'victor', 6, 'jogo3_fase6', 'negativa', 'listening', 1, 3, '2025-11-30 03:21:02', '2025-11-30 03:47:15'),
(72, 7, 'victor', 7, 'jogo3_fase7', 'negativa', 'listening', 1, 3, '2025-11-30 03:21:08', '2025-11-30 03:47:19'),
(73, 7, 'victor', 8, 'jogo3_fase8', 'negativa', 'speaking', 0, 3, '2025-11-30 03:21:14', '2025-11-30 03:47:30'),
(74, 7, 'victor', 9, 'jogo3_fase9', 'negativa', 'speaking', 1, 3, '2025-11-30 03:21:25', '2025-11-30 03:47:58'),
(75, 7, 'victor', 10, 'jogo3_fase10', 'negativa', 'speaking', 1, 4, '2025-11-30 03:24:32', '2025-11-30 03:49:56'),
(76, 7, 'victor', 1, 'jogo4_fase1', 'interrogativa', 'listening', 1, 8, '2025-11-30 04:10:14', '2025-11-30 04:59:38'),
(77, 7, 'victor', 2, 'jogo4_fase2', 'interrogativa', 'reading', 1, 3, '2025-11-30 04:44:30', '2025-11-30 04:59:40'),
(78, 7, 'victor', 3, 'jogo4_fase3', 'afirmativa', 'writing', 1, 3, '2025-11-30 04:44:43', '2025-11-30 04:59:45'),
(79, 7, 'victor', 4, 'jogo4_fase4', 'interrogativa', 'writing', 1, 10, '2025-11-30 04:44:58', '2025-11-30 05:05:44'),
(80, 7, 'victor', 5, 'jogo4_fase5', 'interrogativa', 'speaking', 1, 3, '2025-11-30 04:45:11', '2025-11-30 04:59:58'),
(81, 7, 'victor', 6, 'jogo4_fase6', 'interrogativa', 'reading', 1, 3, '2025-11-30 04:45:20', '2025-11-30 05:00:03'),
(82, 7, 'victor', 7, 'jogo4_fase7', 'interrogativa', 'reading', 1, 3, '2025-11-30 04:45:26', '2025-11-30 05:00:05'),
(83, 7, 'victor', 8, 'jogo4_fase8', 'interrogativa', 'speaking', 1, 3, '2025-11-30 04:45:34', '2025-11-30 05:00:11'),
(84, 7, 'victor', 9, 'jogo4_fase9', 'interrogativa', 'speaking', 1, 5, '2025-11-30 04:45:41', '2025-11-30 05:06:56'),
(85, 7, 'victor', 4, 'jogo4_fase10', 'interrogativa', 'writing', 1, 4, '2025-11-30 04:46:49', '2025-11-30 05:06:29');

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
(249, 7, 'victor', 11, 11, 9, 9, 10, 9, 8, 7, 3, 3, 6, 6, 5, 5, 8, 8, '2025-11-30 05:06:56');

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
(7, 'victor', 'victorrocha0223@gmail.com', '$2y$10$3ppWz666rjn9M7Fwl8mIBeYpyE2QBUnh1UH6..J7D3EwA6tutUfeK', NULL, '2025-11-28 19:02:03');

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
(7, 'victor', 1, 1, 1, 1, 1, 1, 1, 1, 9, 5, 22, '2025-11-30 03:47:58');

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
(18, 7, 'victor', 1, 8, 4, 4, 4, 4, 4, 0, 0, 8, 40, '2025-11-30 04:30:56');

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
(11, 7, 'victor', 0, 0, 0, 10, 0, 0, 0, 0, 5, 5, 15, '2025-11-30 05:06:56');

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
-- Índices de tabela `estrelas`
--
ALTER TABLE `estrelas`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT de tabela `estrelas`
--
ALTER TABLE `estrelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT de tabela `fase_estrelas`
--
ALTER TABLE `fase_estrelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de tabela `fase_estrelas2`
--
ALTER TABLE `fase_estrelas2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de tabela `fase_estrelas3`
--
ALTER TABLE `fase_estrelas3`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `jogo`
--
ALTER TABLE `jogo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `progresso_detalhado`
--
ALTER TABLE `progresso_detalhado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT de tabela `resumo_progresso`
--
ALTER TABLE `resumo_progresso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=362;

--
-- AUTO_INCREMENT de tabela `tokens_recuperacao`
--
ALTER TABLE `tokens_recuperacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `xp_jogo2`
--
ALTER TABLE `xp_jogo2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `xp_jogo3`
--
ALTER TABLE `xp_jogo3`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restrições para tabelas despejadas
--

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
