<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <?php
    session_start();
    require "conexao.php";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $usuario = strtoupper(trim($_POST['usuario'] ?? ''));
        $senha   = $_POST['senha'] ?? '';

        if ($usuario === '' || $senha === '') {
            echo "<script>alert('Preencha usuário e senha'); window.location.href='login.php';</script>";
            exit;
        }

        // Buscar usuário na tabela 'pessoa'
        $sql = $con->prepare("SELECT * FROM pessoa WHERE usuario = ? LIMIT 1");
        $sql->bind_param("s", $usuario);
        $sql->execute();
        $result = $sql->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // checar se conta ativa
            if (isset($user['is_active']) && intval($user['is_active']) === 0) {
                echo "<script>alert('Conta inativa. Contate o administrador.'); window.location.href='login.php';</script>";
                exit;
            }

            // Verificar senha (assumindo hash com password_hash)
            if (password_verify($senha, $user['senha'])) {
                // Atualizar último login
                $upd = $con->prepare("UPDATE pessoa SET last_login = NOW() WHERE id_pessoa = ?");
                $upd->bind_param('i', $user['id_pessoa']);
                $upd->execute();

                // Sessão
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['id_pessoa'] = $user['id_pessoa'];
                $_SESSION['role'] = $user['role'] ?? 'user';

                header("Location: ../site_finan_chatbot/financias.php");
                exit;
            } else {
                echo "<script>alert('Senha incorreta!'); window.location.href='login.php';</script>";
                exit;
            }
        } else {
            echo "<script>alert('Usuário não encontrado!'); window.location.href='cadastrar.php';</script>";
            exit;
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - Finanças</title>
        <link rel="stylesheet" href="style.css">
        <link rel="shortcut icon" href="WhatsApp Image 2025-11-03 at 19.12.50.jpeg" type="image/x-icon">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    </head>
    <body>
        <div class="wrapper">
            <div class="login-box">
                <form action="" method="POST">
                    <h2>INSIRA SUA CONTA</h2>

                    <div class="input-box">
                        <input type="text" name="usuario" required>
                        <label>Usuário</label>
                    </div>

                    <div class="input-box">
                        <input type="password" name="senha" required>
                        <label>Senha</label>
                    </div>

                    <div class="remember-forgot">
                        <label><input type="checkbox" id="label"> Lembrar de mim </label><br>
                    </div>

                        <button type="submit" class="btn">Entrar</button><br>

                        <div>
                            <center><a href="#">Esqueceu a senha?</a><br><br></center>
                        </div>

                        <div class="registre-link">
                           <center><h6>Criar conta</h6></center>
                            <br>
                            <button type="button" class="btn" onclick="location.href='cadastrar.php'">Cadastre-se</button>
                        </div>
            </div>
        </div>

                </form>

    </body>

    </html>
