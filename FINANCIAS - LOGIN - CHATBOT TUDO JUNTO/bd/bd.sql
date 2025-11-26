-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           10.4.32-MariaDB - mariadb.org binary distribution
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              12.13.0.7147
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Copiando estrutura do banco de dados para piggmoneta
CREATE DATABASE IF NOT EXISTS `piggmoneta` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `piggmoneta`;

-- Copiando estrutura para tabela piggmoneta.categoria
CREATE TABLE IF NOT EXISTS `categoria` (
  `id_cat` int(11) NOT NULL AUTO_INCREMENT,
  `nome_cat` varchar(50) DEFAULT NULL,
  `desc_cat` varchar(50) DEFAULT NULL,
  `tipo` char(1) DEFAULT NULL,
  PRIMARY KEY (`id_cat`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela piggmoneta.categoria: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela piggmoneta.despesa
CREATE TABLE IF NOT EXISTS `despesa` (
  `id_desp` int(11) NOT NULL AUTO_INCREMENT,
  `nome_desp` varchar(50) DEFAULT NULL,
  `valor_desp` float DEFAULT NULL,
  `valor_pgto` float DEFAULT NULL,
  `dt_venc` date DEFAULT NULL,
  `id_pessoa` int(11) DEFAULT NULL,
  `id_cat` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_desp`),
  KEY `id_pessoa` (`id_pessoa`),
  KEY `id_cat` (`id_cat`),
  CONSTRAINT `despesa_ibfk_1` FOREIGN KEY (`id_pessoa`) REFERENCES `pessoa` (`id_pessoa`),
  CONSTRAINT `despesa_ibfk_2` FOREIGN KEY (`id_cat`) REFERENCES `categoria` (`id_cat`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela piggmoneta.despesa: ~0 rows (aproximadamente)

-- Copiando estrutura para tabela piggmoneta.pessoa
CREATE TABLE IF NOT EXISTS `pessoa` (
  `id_pessoa` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  `usuario` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `sexo` char(1) DEFAULT NULL,
  `dt_nasc` date DEFAULT NULL,
  PRIMARY KEY (`id_pessoa`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela piggmoneta.pessoa: ~3 rows (aproximadamente)
INSERT INTO `pessoa` (`id_pessoa`, `nome`, `usuario`, `email`, `senha`, `sexo`, `dt_nasc`) VALUES
	(104, 'YAN', 'YAN A.', 'yan2@gmail.com', '$2y$10$A6a.4IBIlKWdUyY.shCCTe50UEa26f/6rWzIYwXdhBn8k00AHEayG', 'M', '2025-11-21'),
	(105, 'TETEUS', 'MAT', 'matheus@gmail.com', '$2y$10$wUoot8coVb0DGdIiK0MbqeaOmC9uBYg7J9KQw36NZGQ3H/8dC9.sq', 'F', '2005-06-18'),
	(106, 'LUIZ', 'LUIZ', 'luiz@gmail.com', '$2y$10$BOpm3bVZWwAeFQJbhBue0usXtlNYpeC2NZ/eWYz8pyGJGbExh9Ofm', 'M', '2025-11-23');

-- Copiando estrutura para tabela piggmoneta.receita
CREATE TABLE IF NOT EXISTS `receita` (
  `id_rec` int(11) NOT NULL AUTO_INCREMENT,
  `titulo_rec` varchar(50) DEFAULT NULL,
  `valor_rec` float DEFAULT NULL,
  `dt_recebido` date DEFAULT NULL,
  `id_pessoa` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_rec`),
  KEY `id_pessoa` (`id_pessoa`),
  CONSTRAINT `receita_ibfk_1` FOREIGN KEY (`id_pessoa`) REFERENCES `pessoa` (`id_pessoa`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela piggmoneta.receita: ~1 rows (aproximadamente)
INSERT INTO `receita` (`id_rec`, `titulo_rec`, `valor_rec`, `dt_recebido`, `id_pessoa`) VALUES
	(1, 'Renda mensal 2025-11', 4500, '2025-11-01', NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
