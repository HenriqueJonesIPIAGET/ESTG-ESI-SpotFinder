-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 17-Jun-2026 às 19:22
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
-- Banco de dados: `db_spot`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `utilizador_id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL DEFAULT 1,
  `valor_total` decimal(10,2) NOT NULL,
  `data_compra` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `compras`
--

INSERT INTO `compras` (`id`, `utilizador_id`, `evento_id`, `quantidade`, `valor_total`, `data_compra`) VALUES
(1, 1, 5, 1, 5.00, '2026-06-17 17:16:56'),
(2, 1, 3, 1, 15.00, '2026-06-17 17:20:15'),
(3, 1, 4, 1, 0.00, '2026-06-17 17:20:17');

-- --------------------------------------------------------

--
-- Estrutura da tabela `eventos`
--

CREATE TABLE `eventos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `data_evento` varchar(100) NOT NULL,
  `localizacao` varchar(200) NOT NULL,
  `preco` decimal(10,2) NOT NULL DEFAULT 0.00,
  `imagem_url` varchar(255) DEFAULT NULL,
  `criado_em` datetime DEFAULT current_timestamp(),
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `eventos`
--

INSERT INTO `eventos` (`id`, `titulo`, `categoria`, `data_evento`, `localizacao`, `preco`, `imagem_url`, `criado_em`, `latitude`, `longitude`) VALUES
(1, 'Festival NOS Alive 2026', 'Festival', '10-12 Jul 2026', 'Passeio Marítimo de Algés, Lisboa', 89.00, 'https://images.unsplash.com/photo-1533174072545...', '2026-06-17 16:07:30', 38.70980000, -9.23580000),
(2, 'Rock in Rio Lisboa', 'Concerto', '15 Jun 2026', 'Parque da Bela Vista, Lisboa', 75.00, 'https://images.unsplash.com/photo-1540039155733...', '2026-06-17 16:07:30', 38.74670000, -9.15290000),
(3, 'Exposição Ibérica de Gaming e MMOs', 'Exposição', '12-14 Ago 2026', 'FIL - Parque das Nações, Lisboa', 15.00, 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=600&q=80', '2026-06-17 16:25:32', 38.76940000, -9.09450000),
(4, 'Meetup de Programação Web', 'Exposição', '20 Set 2026', 'Campus Universitário, Almada', 0.00, 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=600&q=80', '2026-06-17 16:25:32', 38.66090000, -9.19360000),
(5, 'Festival Automóvel: Eletrónica e Motores', 'Festival', '05-06 Set 2026', 'Parque de Feiras, Vendas Novas', 5.00, 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=600&q=80', '2026-06-17 16:25:32', 38.67750000, -8.45580000),
(6, 'Feira da Gastronomia Tradicional: Bochechas e Caldos', 'Gastronomia', '23-25 Out 2026', 'Praça do Giraldo, Évora', 12.50, 'https://images.unsplash.com/photo-1547592180-85f173990554?w=600&q=80', '2026-06-17 16:25:32', 38.57140000, -7.91350000),
(7, 'Noite de Stand-up Comedy', 'Teatro', '18 Jul 2026', 'Teatro Garcia de Resende, Évora', 18.00, 'https://images.unsplash.com/photo-1585699324551-f6c309eedeca?w=600&q=80', '2026-06-17 16:25:32', 38.57250000, -7.91000000),
(8, 'Concerto Sinfónico de Verão', 'Concerto', '30 Jul 2026', 'Castelo de São Jorge, Lisboa', 22.00, 'https://images.unsplash.com/photo-1549834125-82d3c48159a3?w=600&q=80', '2026-06-17 16:25:32', 38.71390000, -9.13350000);

-- --------------------------------------------------------

--
-- Estrutura da tabela `favoritos`
--

CREATE TABLE `favoritos` (
  `id` int(11) NOT NULL,
  `utilizador_id` int(11) NOT NULL,
  `evento_id` int(11) NOT NULL,
  `adicionado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `favoritos`
--

INSERT INTO `favoritos` (`id`, `utilizador_id`, `evento_id`, `adicionado_em`) VALUES
(9, 1, 5, '2026-06-17 17:01:49'),
(12, 1, 3, '2026-06-17 17:49:38');

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizadores`
--

CREATE TABLE `utilizadores` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `data_registo` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `utilizadores`
--

INSERT INTO `utilizadores` (`id`, `nome`, `email`, `password`, `data_registo`) VALUES
(1, 'Dinis Soares', 'dinissoares991@gmail.com', '$2y$10$JmaglvtwPi3wLWnIZL7NhOncGpI.R9C0usmHBM8cJJfrtTsjbyd3C', '2026-06-17 16:49:57');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilizador_id` (`utilizador_id`),
  ADD KEY `evento_id` (`evento_id`);

--
-- Índices para tabela `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `favoritos`
--
ALTER TABLE `favoritos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilizador_id` (`utilizador_id`),
  ADD KEY `evento_id` (`evento_id`);

--
-- Índices para tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `favoritos`
--
ALTER TABLE `favoritos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `utilizadores`
--
ALTER TABLE `utilizadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizadores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `compras_ibfk_2` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `favoritos`
--
ALTER TABLE `favoritos`
  ADD CONSTRAINT `favoritos_ibfk_1` FOREIGN KEY (`utilizador_id`) REFERENCES `utilizadores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favoritos_ibfk_2` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
