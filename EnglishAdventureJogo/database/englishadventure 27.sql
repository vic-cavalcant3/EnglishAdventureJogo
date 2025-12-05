-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 27/11/2025 às 20:50
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
(69, 'vic', 'fase1_atividade1', 0, 1, '2025-11-14 14:34:46'),
(70, 'vic', 'fase1_atividade2', 0, 1, '2025-11-14 14:34:48'),
(71, 'vic', 'fase1_atividade3', 0, 1, '2025-11-14 14:34:49'),
(81, 'Ryan de Oliveira', 'fase1_atividade1', 0, 0, '2025-11-14 17:53:33'),
(82, 'Ryan de Oliveira', 'fase1_atividade2', 0, 0, '2025-11-14 17:53:34'),
(83, 'Ryan de Oliveira', 'fase1_atividade3', 0, 1, '2025-11-14 17:53:36'),
(84, 'manuelas', 'fase1_atividade1', 0, 1, '2025-11-14 17:58:13'),
(85, 'manuelas', 'fase1_atividade2', 0, 0, '2025-11-14 17:58:18'),
(86, 'manuelas', 'fase1_atividade3', 0, 1, '2025-11-14 17:58:23');

-- --------------------------------------------------------

--
-- Estrutura para tabela `jogo`
--

CREATE TABLE `jogo` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `xp` int(11) DEFAULT 0,
  `xp_total` int(11) DEFAULT 0,
  `estrelas` int(11) DEFAULT 0,
  `pagina_atual` int(11) DEFAULT 1,
  `fase_atual` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `jogo`
--

INSERT INTO `jogo` (`id`, `nome`, `xp`, `xp_total`, `estrelas`, `pagina_atual`, `fase_atual`) VALUES
(2, 'vic', 0, 2, 1, 1, 1),
(3, 'Ryan de Oliveira', 0, 0, 1, 1, 1),
(4, 'manuelas', 0, 0, 2, 1, 1);

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
(2, 'vic', 'victorrocha0223@gmail.com', '$2y$10$R.MnOzjaAWvy0lN4eFkQ2.IVI1s64xvuIXBB7cKuq3tMnP5Yr/4L.', NULL, '2025-11-13 17:40:23'),
(3, 'Sofia Viana', 'sofiavitorino@gmail.com', '$2y$10$Qg/.LBqEsJTq1VWmh8cmhuBJhikFy5dsUh.FCwKWP6zLf5p755K0K', NULL, '2025-11-14 16:38:34'),
(5, 'Ryan de Oliveira', 'ryanoliversantos0503@gmail.com', '$2y$10$zxdAnFzRvuH/ZCxkIpJRXO8VCR7mi90Gg2MbCyxHDDhbNwlT3AhY.', NULL, '2025-11-14 17:36:24'),
(6, 'manuelas', 'manu.lopes.costaa@gmail.com', '$2y$10$l.EHO2643LZpdx/VhYfeZehrvO2Tf9hf8fMx7GTJnKbVWoe5Q3fqu', NULL, '2025-11-14 17:57:31');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `estrelas`
--
ALTER TABLE `estrelas`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `jogo`
--
ALTER TABLE `jogo`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `estrelas`
--
ALTER TABLE `estrelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT de tabela `jogo`
--
ALTER TABLE `jogo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `tokens_recuperacao`
--
ALTER TABLE `tokens_recuperacao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `tokens_recuperacao`
--
ALTER TABLE `tokens_recuperacao`
  ADD CONSTRAINT `tokens_recuperacao_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
