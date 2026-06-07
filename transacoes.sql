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
-- Estrutura da tabela `transacoes`
--

CREATE TABLE IF NOT EXISTS `transacoes` (
  `codigo` bigint(11) NOT NULL AUTO_INCREMENT,
  `data_reg` date NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `categoria` varchar(20) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1780702989380 ;

--
-- Extraindo dados da tabela `transacoes`
--

INSERT INTO `transacoes` (`codigo`, `data_reg`, `descricao`, `categoria`, `tipo`, `valor`, `usuario_id`) VALUES
(1779149739524, '2026-02-12', 'Comida', 'Alimentação', 'Renda', '350.00', NULL),
(1779149763777, '2026-04-29', 'Uber', 'Transporte', 'Gasto', '225.00', NULL),
(1779155481931, '2026-05-06', 'Sonegar', 'Impostos', 'Renda', '500.00', NULL),
(1779324330692, '2026-05-06', 'TeleCurso', 'Educação', 'Renda', '750.00', NULL),
(1779324354579, '2026-04-27', 'Poupança', 'Emergência', 'Renda', '95.00', NULL),
(1779324385955, '2026-04-28', 'Agiota', 'Despesa', 'Gasto', '320.00', NULL),
(1780357452617, '2026-06-02', '99', 'Transporte', 'Renda', '150.00', NULL),
(1780366797820, '2026-06-02', 'Carro', 'Transporte', 'Renda', '150.00', 17),
(1780366821976, '2026-06-05', 'curso', 'Educação', 'Gasto', '50.00', 17),
(1780366910582, '2026-02-03', 'Pensão', 'Alimentação', 'Gasto', '200.00', 15),
(1780366945923, '2026-06-02', 'Sonegar', 'Impostos', 'Renda', '500.00', 15),
(1780366972758, '2026-03-12', 'Blaze', 'Emergência', 'Gasto', '120.00', 15),
(1780367129272, '2026-06-02', 'FIFA 2026', 'Emergência', 'Gasto', '500.00', 18),
(1780367154666, '2026-04-15', 'Tigrinho', 'Lazer', 'Renda', '2500.00', 18),
(1780367174007, '2026-06-04', 'Pipoca', 'Alimentação', 'Gasto', '14.00', 18),
(1780702258140, '2026-06-10', 'Body Splash', 'Lazer', 'Renda', '850.00', 14),
(1780702989379, '2026-06-02', 'dasdsa', 'Despesa', 'Gasto', '3213.00', 22);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
