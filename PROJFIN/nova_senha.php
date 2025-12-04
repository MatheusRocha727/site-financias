<?php
session_start();
require 'conexao.php';

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $senha2 = $_POST['senha2'] ?? '';
    if ($senha === '' || $senha2 === '') {
        $message = 'Preencha as senhas.';
    } elseif ($senha !== $senha2) {
        $message = 'As senhas não conferem.';
    } else {
        $stmt = $con->prepare('SELECT id, user_id, expires_at FROM password_resets WHERE token = ? LIMIT 1');
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (strtotime($row['expires_at']) < time()) {
                $message = 'Token expirado. Solicite um novo pedido de recuperação.';
            } else {
                $userId = (int)$row['user_id'];
                $hash = password_hash($senha, PASSWORD_DEFAULT);
                $up = $con->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');
                $up->bind_param('si', $hash, $userId);
                $up->execute();

                // remove tokens antigos para este usuário
                $del = $con->prepare('DELETE FROM password_resets WHERE user_id = ?');
                $del->bind_param('i', $userId);
                $del->execute();

                $message = 'Senha redefinida com sucesso. <a href="login.php">Entrar</a>';
            }
        } else {
            $message = 'Token inválido.';
        }
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Nova senha</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Defina nova senha</h5>
            <?php if ($message): ?>
              <div class="alert alert-info"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if (empty($message) || $_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
              <form method="POST">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="mb-3">
                  <label class="form-label">Nova senha</label>
                  <input type="password" name="senha" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Confirme a nova senha</label>
                  <input type="password" name="senha2" class="form-control" required>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                  <a href="login.php" class="btn btn-link">Voltar ao login</a>
                  <button class="btn btn-success">Salvar nova senha</button>
                </div>
              </form>
            <?php endif; ?>

          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
