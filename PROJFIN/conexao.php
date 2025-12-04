<?php
// Conexão MySQL/MariaDB via mysqli – usado por login, cadastro, CRUD etc.

$host = "localhost";   // mesmo host que você usa no HeidiSQL
$user = "root";        // usuário do MariaDB
$pass = "";            // senha do usuário (se tiver, coloca aqui)
$db   = "piggmoneta";  // NOME DO SEU BANCO (já alinhado)

// Ativa exceptions do mysqli (bom pra achar erro rápido em desenvolvimento)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $con = new mysqli($host, $user, $pass, $db);
    $con->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Mensagem de erro simples + detalhe técnico
    echo "<h3>Erro ao conectar ao banco de dados</h3>";
    echo "<p>Verifique host, usuário, senha e nome do banco (<code>piggmoneta</code>).</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    exit;
}
?>
