-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           10.4.24-MariaDB - mariadb.org binary distribution
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Copiando estrutura do banco de dados para piggmoneta1
CREATE DATABASE IF NOT EXISTS `piggmoneta1` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `piggmoneta1`;

-- Copiando estrutura para tabela piggmoneta1.categoria
CREATE TABLE IF NOT EXISTS `categoria` (
  `id_cat` int(11) NOT NULL AUTO_INCREMENT,
  `nome_cat` varchar(50) DEFAULT NULL,
  `desc_cat` varchar(50) DEFAULT NULL,
  `tipo` char(1) DEFAULT NULL,
  PRIMARY KEY (`id_cat`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela piggmoneta1.despesa
CREATE TABLE IF NOT EXISTS `despesa` (
  `id_desp` int(11) NOT NULL AUTO_INCREMENT,
  `nome_desp` varchar(50) DEFAULT NULL,
  `valor_desp` float DEFAULT NULL,
  `valor_pgto` float DEFAULT NULL,
  `dt_venc` date DEFAULT NULL,
  `id_pessoa` int(11) NOT NULL,
  `id_cat` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_desp`),
  KEY `id_pessoa` (`id_pessoa`),
  KEY `id_cat` (`id_cat`),
  CONSTRAINT `despesa_ibfk_1` FOREIGN KEY (`id_pessoa`) REFERENCES `pessoa` (`id_pessoa`),
  CONSTRAINT `despesa_ibfk_2` FOREIGN KEY (`id_cat`) REFERENCES `categoria` (`id_cat`)
) ENGINE=InnoDB AUTO_INCREMENT=1001 DEFAULT CHARSET=utf8mb4;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela piggmoneta1.pessoa
CREATE TABLE IF NOT EXISTS `pessoa` (
  `id_pessoa` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  `usuario` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `sexo` char(1) DEFAULT NULL,
  `dt_nasc` date DEFAULT NULL,
  PRIMARY KEY (`id_pessoa`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

-- Exportação de dados foi desmarcado.

-- Copiando estrutura para tabela piggmoneta1.receita
CREATE TABLE IF NOT EXISTS `receita` (
  `id_rec` int(11) NOT NULL AUTO_INCREMENT,
  `titulo_rec` varchar(50) DEFAULT NULL,
  `valor_rec` float DEFAULT NULL,
  `dt_recebido` date DEFAULT NULL,
  `id_pessoa` int(11) NOT NULL,
  PRIMARY KEY (`id_rec`),
  KEY `id_pessoa` (`id_pessoa`),
  CONSTRAINT `receita_ibfk_1` FOREIGN KEY (`id_pessoa`) REFERENCES `pessoa` (`id_pessoa`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- Exportação de dados foi desmarcado.

-- --------------------------------------------------------
-- Atualizações: novos campos de usuário e tabelas para competências
-- --------------------------------------------------------
-- Adiciona colunas à tabela `pessoa` para suportar login/controle
ALTER TABLE `pessoa`
  ADD COLUMN `role` varchar(20) DEFAULT 'user',
  ADD COLUMN `is_active` tinyint(1) NOT NULL DEFAULT 1,
  ADD COLUMN `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  ADD COLUMN `last_login` datetime DEFAULT NULL;

-- Tabela de competências (período/mês) para armazenar snapshots por usuário
CREATE TABLE IF NOT EXISTS `competencia` (
  `id_comp` int(11) NOT NULL AUTO_INCREMENT,
  `yyyy_mm` varchar(7) NOT NULL,
  `id_pessoa` int(11) NOT NULL,
  `renda` float DEFAULT NULL,
  `meta` float DEFAULT NULL,
  `observacoes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_comp`),
  UNIQUE KEY `uniq_comp_pessoa` (`yyyy_mm`,`id_pessoa`),
  KEY `id_pessoa` (`id_pessoa`),
  CONSTRAINT `competencia_ibfk_1` FOREIGN KEY (`id_pessoa`) REFERENCES `pessoa` (`id_pessoa`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de lançamentos genéricos (receitas e despesas vinculadas à competência)
CREATE TABLE IF NOT EXISTS `lancamento` (
  `id_lanc` int(11) NOT NULL AUTO_INCREMENT,
  `id_comp` int(11) NOT NULL,
  `tipo` enum('receita','despesa') NOT NULL,
  `descricao` varchar(150) DEFAULT NULL,
  `valor` float DEFAULT 0,
  `id_cat` int(11) DEFAULT NULL,
  `data` date DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_lanc`),
  KEY `id_comp` (`id_comp`),
  KEY `id_cat` (`id_cat`),
  CONSTRAINT `lancamento_ibfk_1` FOREIGN KEY (`id_comp`) REFERENCES `competencia` (`id_comp`) ON DELETE CASCADE,
  CONSTRAINT `lancamento_ibfk_2` FOREIGN KEY (`id_cat`) REFERENCES `categoria` (`id_cat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Nota: as tabelas `receita` e `despesa` originais permanecem para compatibilidade,
-- mas a aplicação pode migrar dados para `competencia` + `lancamento` se desejado.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
