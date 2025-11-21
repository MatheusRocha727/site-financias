<?php
// Conexão MySQL (mysqli) - arquivo usado por login/cadastro
$host = "localhost";
$user = "root";
$pass = "";
$db   = "piggmoneta"; // ajuste para o nome do seu banco, ex: 'financias' se for o caso

// Habilita exceptions para mysqli em modo de desenvolvimento (ajuste/remova em produção)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $con = new mysqli($host, $user, $pass, $db);
    $con->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Mensagem amigável + mensagem técnica para debug
    echo "<h3>Erro ao conectar ao banco de dados</h3>";
    echo "<p>Verifique as credenciais em <code>yan/conexao.php</code> e se o servidor MySQL está ativo.</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    exit;
}
?>
