-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 22-Dez-2025 às 00:59
-- Versão do servidor: 9.1.0
-- versão do PHP: 8.4.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `lojavirtual`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `categoria`
--

DROP TABLE IF EXISTS `categoria`;
CREATE TABLE IF NOT EXISTS `categoria` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(252) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `categorias`
--

DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int NOT NULL AUTO_INCREMENT,
  `categoria` varchar(252) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  `modelo` varchar(252) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `logs`
--

DROP TABLE IF EXISTS `logs`;
CREATE TABLE IF NOT EXISTS `logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `acao` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detalhes` text COLLATE utf8mb4_unicode_ci,
  `data` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `acao`, `detalhes`, `data`) VALUES
(1, NULL, 'Usuário editado', 'ID: ', '2025-12-19 23:49:18'),
(2, NULL, 'Usuário editado', 'ID: ', '2025-12-21 16:19:07');

-- --------------------------------------------------------

--
-- Estrutura da tabela `pedidos_senha`
--

DROP TABLE IF EXISTS `pedidos_senha`;
CREATE TABLE IF NOT EXISTS `pedidos_senha` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_origem` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_pedido` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `perfil_permissao`
--

DROP TABLE IF EXISTS `perfil_permissao`;
CREATE TABLE IF NOT EXISTS `perfil_permissao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `perfil_id` int NOT NULL,
  `permissao_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `perfil_id` (`perfil_id`),
  KEY `permissao_id` (`permissao_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `perfis`
--

DROP TABLE IF EXISTS `perfis`;
CREATE TABLE IF NOT EXISTS `perfis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `perfis`
--

INSERT INTO `perfis` (`id`, `nome`, `descricao`) VALUES
(1, 'Administrador', 'Acesso total ao sistema');

-- --------------------------------------------------------

--
-- Estrutura da tabela `permissoes`
--

DROP TABLE IF EXISTS `permissoes`;
CREATE TABLE IF NOT EXISTS `permissoes` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `permissoes`
--

INSERT INTO `permissoes` (`id`, `codigo`, `descricao`) VALUES
(1, 'admin.usuarios.ver', 'Ver utilizadores'),
(2, 'admin.usuarios.criar', 'Criar utilizadores'),
(3, 'admin.usuarios.editar', 'Editar utilizadores'),
(4, 'admin.usuarios.permissoes', 'Gerir permissões de utilizadores'),
(5, 'admin.usuarios.logs', 'Ver logs de utilizadores'),
(6, 'admin.usuarios.eliminar', 'Eliminar utilizadores'),
(7, 'admin.perfis.ver', 'Ver perfis'),
(8, 'admin.perfis.criar', 'Criar perfis'),
(9, 'admin.perfis.editar', 'Editar perfis'),
(10, 'admin.perfis.permissoes', 'Gerir permissões de perfis'),
(11, 'admin.perfis.eliminar', 'Eliminar perfis'),
(12, 'admin.permissoes.ver', 'Ver permissões'),
(13, 'admin.permissoes.criar', 'Criar permissões'),
(14, 'admin.permissoes.editar', 'Editar permissões'),
(15, 'admin.permissoes.eliminar', 'Eliminar permissões');

-- --------------------------------------------------------

--
-- Estrutura da tabela `produto`
--

DROP TABLE IF EXISTS `produto`;
CREATE TABLE IF NOT EXISTS `produto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `descricao` varchar(252) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` int NOT NULL,
  `modelo` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `produto`
--

INSERT INTO `produto` (`id`, `descricao`, `status`, `modelo`) VALUES
(1, 'Cal;a', 1, 12),
(45, 'modelo M', 0, 2);

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `apelido` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mail` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` tinyint NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `cadastrado_em` datetime NOT NULL,
  `estado` tinyint NOT NULL DEFAULT '1',
  `cidade` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nif` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reset_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_expira` datetime DEFAULT NULL,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mail` (`mail`),
  UNIQUE KEY `uq_nif` (`nif`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `apelido`, `mail`, `email`, `level`, `status`, `cadastrado_em`, `estado`, `cidade`, `senha`, `nif`, `reset_token`, `reset_expira`, `criado_em`, `atualizado_em`) VALUES
(3, 'TA', 'Anferalta', 'ta@ta.com', 'ta@ta.com', 3, 1, '0000-00-00 00:00:00', 1, 'Lisboa', '$2y$12$aD/DklWIM9iUNuvqg7XQN.3jEkZELp4bbtHI32tuX/F8JMP0vBVHS', '238444414', NULL, NULL, '2025-12-14 17:54:13', '2025-12-16 22:13:57'),
(2, 'Katy', 'ka', 'ka@ka.com', 'ka@ka.com', 3, 1, '0000-00-00 00:00:00', 1, 'Lubango', '$2y$12$phslrdtcONTrQyly4uXooOODpJX.TX3hprtYLJh8EHCGs6g32.RxO', '100000', NULL, NULL, '2025-12-14 17:51:10', '2025-12-16 22:13:57'),
(4, 'Gisa', 'Caetas', 'gi@gi.com', 'gi@gi.com', 2, 1, '0000-00-00 00:00:00', 1, 'Sequele', '$2y$12$QvClrYiaBpwLUtVB78sen.64ZTDKhHFQeDPs59omxmWibO0oevvbe', '238444415', NULL, NULL, '2025-12-14 17:55:05', '2025-12-16 22:13:57'),
(5, 'Cris', 'Ca', 'cris@cris.com', 'cris@cris.com', 1, 1, '0000-00-00 00:00:00', 1, 'Camama', '$2y$12$QfdZFHTnKPfqT67Afm0mHuUXAXJrtpgB4Nwi.7a7JwHTE9heTQTd6', '01010101', NULL, NULL, '2025-12-14 17:55:51', '2025-12-16 22:13:57'),
(6, 'Ima', 'caetas', 'ima@ima.com', 'ima@ima.com', 1, 1, '0000-00-00 00:00:00', 1, 'Caiscais', '$2y$12$Z4Dkn3ck0cMt1qbSmVMet.GzZMcEEGOGU5AOK9.xiFCfNjTt9oqZm', '400000000', NULL, NULL, '2025-12-14 21:12:22', '2025-12-16 22:13:57'),
(7, 'Tony', 'caetas', 'ton@ton.com', 'ton@ton.com', 2, 1, '0000-00-00 00:00:00', 1, 'Caiscais', '$2y$12$Whz3Cxtv6cIZiYm8JU.CaOdnguMloDnzDUK92QwEu.qYZ/JiVUBVG', '238444417', NULL, NULL, '2025-12-14 21:26:01', '2025-12-16 22:13:57');

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizadores`
--

DROP TABLE IF EXISTS `utilizadores`;
CREATE TABLE IF NOT EXISTS `utilizadores` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT '1',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perfil_id` int UNSIGNED DEFAULT NULL,
  `ultimo_login` datetime DEFAULT NULL,
  `criado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `atualizado_em` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `utilizador_permissao`
--

DROP TABLE IF EXISTS `utilizador_permissao`;
CREATE TABLE IF NOT EXISTS `utilizador_permissao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilizador_id` int NOT NULL,
  `permissao_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `utilizador_id` (`utilizador_id`),
  KEY `permissao_id` (`permissao_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
