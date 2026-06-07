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
-- Estrutura da tabela `metas`
--

CREATE TABLE IF NOT EXISTS `metas` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `nome_meta` varchar(255) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `valor_guardado` decimal(10,2) NOT NULL DEFAULT '0.00',
  `descricao` text,
  `imagem_url` varchar(500) DEFAULT NULL,
  `usuario_id` bigint(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Extraindo dados da tabela `metas`
--

INSERT INTO `metas` (`id`, `nome_meta`, `valor_total`, `valor_guardado`, `descricao`, `imagem_url`, `usuario_id`) VALUES
(5, 'FinggerSpinner', '100.00', '20.00', 'Importante', '', 1),
(6, 'Pula Pula do GUGU', '500.00', '150.00', 'sonho', '', 17),
(7, 'Tralalero Doll', '1100.00', '5.00', 'Investimento', '', 19),
(8, '3213', '423.00', '43.00', '32', '', 23),
(10, '323', '321.00', '321.00', '312', '', 23);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
