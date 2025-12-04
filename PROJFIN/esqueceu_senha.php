<?php
session_start();
require 'conexao.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = strtoupper(trim($_POST['nome'] ?? ''));
    $email = trim($_POST['email'] ?? '');
    $nascimento = trim($_POST['nascimento'] ?? ''); // esperado YYYY-MM-DD

    if ($nome === '' || $email === '' || $nascimento === '') {
        $message = 'Preencha nome completo, e-mail e data de nascimento.';
    } else {
        // procurar usuário pelo e-mail (assincronia evita vazamento de info)
        $stmt = $con->prepare('SELECT * FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $dbNome = trim($row['nome'] ?? '');
            $dbNascimento = trim($row['data_nascimento'] ?? $row['data_nasc'] ?? $row['nascimento'] ?? $row['dt_nascimento'] ?? '');

            // normaliza datas para comparar
            $inputDt = $nascimento;
            $dbDt = '';
            if (!empty($dbNascimento)) {
                $ts = strtotime($dbNascimento);
                if ($ts !== false) $dbDt = date('Y-m-d', $ts);
            }

            // compara nome (case-insensitive) e datas (YYYY-MM-DD)
            if (strcasecmp($dbNome, $nome) === 0 && $dbDt !== '' && $dbDt === $inputDt) {
                // garante tabela de resets
                $create = "CREATE TABLE IF NOT EXISTS password_resets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    token VARCHAR(128) NOT NULL,
                    expires_at DATETIME NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
                $con->query($create);

                // gera token e insere
                $token = bin2hex(random_bytes(16));
                $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hora
                $ins = $con->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)');
                $uid = (int)$row['id'];
                $ins->bind_param('iss', $uid, $token, $expires);
                $ins->execute();

                // redireciona para nova_senha.php com token
                $host = $_SERVER['HTTP_HOST'];
                $path = rtrim(dirname($_SERVER['PHP_SELF']), '\\/');
                $redir = "http://{$host}{$path}/nova_senha.php?token={$token}";
                header('Location: ' . $redir);
                exit;
            } else {
                $message = 'Os dados não conferem com nossos registros.';
            }
        } else {
            $message = 'Os dados não conferem com nossos registros.';
        }
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Esqueceu sua senha</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Recuperar acesso</h5>
            <p class="text-muted">Informe seu nome completo, e-mail e data de nascimento para validar sua identidade.</p>

            <?php if ($message): ?>
              <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="POST">
              <div class="mb-3">
                <label class="form-label">Nome completo</label>
                <input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Data de nascimento</label>
                <input type="date" name="nascimento" class="form-control" value="<?php echo htmlspecialchars($_POST['nascimento'] ?? ''); ?>" required>
              </div>

              <div class="d-flex justify-content-between align-items-center">
                <a href="login.php" class="btn btn-link">Voltar ao login</a>
                <button class="btn btn-primary">Verificar</button>
              </div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
