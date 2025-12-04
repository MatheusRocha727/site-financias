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

-- Copiando estrutura para tabela piggmoneta.chat_log
CREATE TABLE IF NOT EXISTS `chat_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `pergunta` text NOT NULL,
  `resposta` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `chat_log_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela piggmoneta.chat_log: ~1 rows (aproximadamente)
INSERT INTO `chat_log` (`id`, `id_usuario`, `pergunta`, `resposta`, `created_at`) VALUES
	(1, 2, 'poderia me explicar sobre compra de cotas de fundos imobiliarios?', 'Claro! A compra de cotas de fundos imobiliários (FIIs) é uma forma de investimento em imóveis por meio do mercado financeiro. Vou explicar como funciona:\n\n---\n\n### **O que são Fundos Imobiliários?**\nFundos de Investimentos Imobiliários (FIIs) são fundos que reúnem recursos de diversos investidores com o objetivo de investir em ativos relacionados ao mercado imobiliário. Esses ativos podem incluir:\n- Imóveis físicos (shoppings, escritórios, galpões logísticos, hospitais, etc.).\n- Títulos de dívida imobiliária, como Certificados de Recebíveis Imobiliários (CRIs) e Letras de Crédito Imobiliário (LCIs).\n\nCada investidor compra cotas do fundo, tornando-se um cotista. Assim, ele participa proporcionalmente dos lucros e prejuízos do fundo.\n\n---\n\n### **Como funciona a compra de cotas?**\n1. **Escolha do FII**:\n   - Antes de comprar as cotas, você precisa escolher o fundo no qual deseja investir. Existem diversos tipos de FIIs, como:\n     - Fundos de renda (que geram ganhos mensais com aluguéis).\n     - Fundos de desenvolvimento (focados na construção e vendas de imóveis).\n     - Fundos de papel (que investem em títulos de dívida imobiliária).\n\n2. **Abertura de conta em uma corretora**:\n   - Para adquirir cotas, é necessário ter conta em uma corretora de valores. É por meio dela que você terá acesso ao mercado de FIIs.\n\n3. **Compra na Bolsa de Valores**:\n   - As cotas dos FIIs são negociadas na Bolsa de Valores (B3, no Brasil), de forma semelhante às ações. Cada cota tem um preço que varia conforme a oferta e demanda do mercado.\n   - Você pode enviar uma ordem de compra pela corretora, indicando o número de cotas e o preço máximo que está disposto a pagar.\n\n4. **Recebimento de rendimentos**:\n   - A maioria dos FIIs distribui rendimentos mensais aos cotistas. Esses rendimentos vêm, geralmente, dos aluguéis dos imóveis ou dos juros dos títulos imobiliários. \n   - No Brasil, os rendimentos de FIIs para pessoas físicas são isentos de Imposto de Renda, desde que o investidor cumpra algumas condições.\n\n---\n\n### **Vantagens de investir em FIIs**:\n- **Renda passiva**: A possibilidade de receber rendimentos mensais.\n- **Acessibilidade**: É possível investir com pouco dinheiro, já que muitas cotas têm preços acessíveis.\n- **Diversificação**: Investir em FIIs permite diversificar sua carteira com ativos imobiliários, reduzindo riscos.\n- **Liquidez**: Você pode comprar e vender cotas na Bolsa de Valores, diferente de imóveis físicos que podem demorar para serem vendidos.\n\n---\n\n### **Riscos e pontos de atenção**:\n- **Oscilações no valor das cotas**: O preço das cotas pode variar no mercado, o que pode gerar prejuízo se você vender por um valor menor do que comprou.\n- **Riscos específicos do fundo**: Por exemplo, vacância dos imóveis (quando eles ficam desocupados) ou inadimplência dos inquilinos.\n- **Diversificação**: Não invista em um único fundo. É importante diversificar entre diferentes tipos de FIIs para reduzir riscos.\n\n---\n\n### **Impostos**:\n- **Rendimentos**: Como mencionado, os rendimentos mensais são isentos de Imposto de Renda para pessoas físicas, desde que o fundo seja listado na Bolsa e tenha mais de 50 cotistas.\n- **Venda de cotas**: Se você vender cotas com lucro, deverá pagar 20% de Imposto de Renda sobre o ganho de capital.\n\n---\n\nInvestir em FIIs é uma ótima forma de entrar no mercado imobiliário com menos burocracia e maior liquidez. Porém, como em qualquer investimento, é essencial estudar os fundos e avaliar os riscos antes de aplicar o dinheiro. Se precisar de mais detalhes ou ajuda para começar, é só perguntar!', '2025-11-27 08:54:58');

-- Copiando estrutura para tabela piggmoneta.orcamento_despesa
CREATE TABLE IF NOT EXISTS `orcamento_despesa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `competencia` char(7) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `descricao` varchar(100) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `orcamento_despesa_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela piggmoneta.orcamento_despesa: ~25 rows (aproximadamente)
INSERT INTO `orcamento_despesa` (`id`, `id_usuario`, `competencia`, `tipo`, `descricao`, `valor`, `created_at`) VALUES
	(6, 2, '2025-01', 'Fixa', 'Aluguel', 10.00, '2025-11-27 05:56:28'),
	(7, 2, '2025-01', 'Fixa', 'Luz', 10.00, '2025-11-27 05:56:28'),
	(8, 2, '2025-01', 'Fixa', 'Água', 10.00, '2025-11-27 05:56:28'),
	(9, 2, '2025-01', 'Variável', 'Mercado', 10.00, '2025-11-27 05:56:28'),
	(10, 2, '2025-01', 'Variável', 'Transporte', 10.00, '2025-11-27 05:56:28'),
	(26, 2, '2025-02', 'Fixa', 'Aluguel', 10.00, '2025-11-27 06:25:26'),
	(27, 2, '2025-02', 'Fixa', 'Luz', 10.00, '2025-11-27 06:25:26'),
	(28, 2, '2025-02', 'Fixa', 'Água', 10.00, '2025-11-27 06:25:26'),
	(29, 2, '2025-02', 'Variável', 'Mercado', 10.00, '2025-11-27 06:25:26'),
	(30, 2, '2025-02', 'Variável', 'Transporte', 10.00, '2025-11-27 06:25:26'),
	(36, 2, '2025-11', 'Fixa', 'Aluguel', 10.00, '2025-11-27 08:30:36'),
	(37, 2, '2025-11', 'Fixa', 'Luz', 10.00, '2025-11-27 08:30:36'),
	(38, 2, '2025-11', 'Fixa', 'Água', 10.00, '2025-11-27 08:30:36'),
	(39, 2, '2025-11', 'Variável', 'Mercado', 10.00, '2025-11-27 08:30:36'),
	(40, 2, '2025-11', 'Variável', 'Transporte', 10.00, '2025-11-27 08:30:36'),
	(41, 2, '2025-03', 'Fixa', 'Aluguel', 10.00, '2025-11-27 08:39:56'),
	(42, 2, '2025-03', 'Fixa', 'Luz', 10.00, '2025-11-27 08:39:56'),
	(43, 2, '2025-03', 'Fixa', 'Água', 10.00, '2025-11-27 08:39:56'),
	(44, 2, '2025-03', 'Variável', 'Mercado', 10.00, '2025-11-27 08:39:56'),
	(45, 2, '2025-03', 'Variável', 'Transporte', 10.00, '2025-11-27 08:39:57'),
	(46, 2, '2025-04', 'Fixa', 'Aluguel', 10.00, '2025-11-27 08:40:19'),
	(47, 2, '2025-04', 'Fixa', 'Luz', 10.00, '2025-11-27 08:40:19'),
	(48, 2, '2025-04', 'Fixa', 'Água', 10.00, '2025-11-27 08:40:19'),
	(49, 2, '2025-04', 'Variável', 'Mercado', 10.00, '2025-11-27 08:40:19'),
	(50, 2, '2025-04', 'Variável', 'Transporte', 10.00, '2025-11-27 08:40:19');

-- Copiando estrutura para tabela piggmoneta.orcamento_receita
CREATE TABLE IF NOT EXISTS `orcamento_receita` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `competencia` char(7) NOT NULL,
  `renda` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `orcamento_receita_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela piggmoneta.orcamento_receita: ~5 rows (aproximadamente)
INSERT INTO `orcamento_receita` (`id`, `id_usuario`, `competencia`, `renda`, `created_at`) VALUES
	(3, 2, '2025-01', 1.00, '2025-11-27 05:56:28'),
	(7, 2, '2025-02', 1.00, '2025-11-27 06:25:26'),
	(9, 2, '2025-11', 1500.00, '2025-11-27 08:30:36'),
	(10, 2, '2025-03', 1.00, '2025-11-27 08:39:56'),
	(11, 2, '2025-04', 1.00, '2025-11-27 08:40:19');

-- Copiando estrutura para tabela piggmoneta.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `data_nasc` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Copiando dados para a tabela piggmoneta.usuarios: ~2 rows (aproximadamente)
INSERT INTO `usuarios` (`id`, `nome`, `usuario`, `email`, `senha`, `data_nasc`) VALUES
	(2, 'YAN', 'YAN', 'yan@gmail.com', '$2y$10$VMDS6oYfaS6VEMnJjUTuD.rEHlgv5rjkkE9KOL012TGVpNHmEN/.C', '0000-00-00'),
	(3, 'MATHEUS MACHADO', 'TETEUS', 'matehus.r@gmail.com', '$2y$10$uHzKvpxGJps.dtJCG0S0TuMK8V5coh7C7uu8Li6Rd5/UmnzMFRexu', '2007-11-22');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
