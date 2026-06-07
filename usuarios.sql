-- phpMyAdmin SQL Dump
-- version 4.0.4.2
-- http://www.phpmyadmin.net
--
-- Máquina: localhost
-- Data de Criação: 07-Jun-2026 às 05:21
-- Versão do servidor: 5.6.13
-- versão do PHP: 5.4.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de Dados: `p12semteste_paginicial`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `data_nascimento` varchar(20) DEFAULT NULL,
  `idade` int(11) NOT NULL,
  `ocupacao` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `data_nascimento`, `idade`, `ocupacao`, `created_at`) VALUES
(14, 'Carlos', 'carlos@gmail.com', '1234', '1987-10-25', 0, 'Ocupado', '2026-05-17 20:05:14'),
(15, 'Gustavo', 'gustavo@gmail.com', '123', '2026-06-02', 0, 'teste', '2026-06-01 22:49:45'),
(16, 'claudio', 'claudio@gmail.com', '12345', '2026-06-04', 0, 'ocupado', '2026-06-01 22:52:06'),
(17, 'testador', 'testador@gmail.com', '1234', '2026-06-04', 0, 'teste', '2026-06-01 23:08:39'),
(18, 'Carlos', 'carlinhos@gmail.com', '123', '2026-06-04', 0, 'Gooner', '2026-06-01 23:24:43'),
(19, 'Guilherme', 'godzila@gmail.com', '123', '2026-06-03', 0, 'Freak', '2026-06-04 14:48:17'),
(20, 'Manuel', 'caneta@gmail.com', '456', '2026-06-03', 0, 'cantor', '2026-06-04 15:01:38'),
(21, 'te', 'te@gmail.com', '111', '2026-06-26', 0, '', '2026-06-04 15:22:19'),
(22, 'Bluezão', 'blue@gmail.com', 'blue', '2026-03-12', 0, 'Ator', '2026-06-05 20:40:56'),
(23, 'confirma', 'confirma@gmail.com', 'confirma', '2026-06-09', 0, 'confirmado', '2026-06-05 21:35:40');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
