<?php
session_start();
require "conexao.php";

// Opcional: ligar erros na tela durante desenvolvimento
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = strtoupper(trim($_POST['usuario'] ?? ''));
    $senha   = $_POST['senha'] ?? '';

    if ($usuario === '' || $senha === '') {
        echo "<script>alert('Preencha usuário e senha.');</script>";
    } else {
        $sql = $con->prepare("SELECT * FROM usuarios WHERE usuario = ?");
        $sql->bind_param("s", $usuario);
        $sql->execute();
        $result = $sql->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($senha, $user['senha'])) {
                $_SESSION['id_usuario'] = $user['id'];
                // login ok: guarda na sessão
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['cargo']   = $user['cargo'] ?? 'usuario';

                header("Location: financias.php");
                exit;
            } else {
                echo "<script>alert('Senha incorreta!');</script>";
            }
        } else {
            echo "<script>alert('Usuário não encontrado!');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PigMoneta</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="WhatsApp Image 2025-11-03 at 19.12.50.jpeg" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
          crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
            crossorigin="anonymous"></script>
</head>

<body>
<div class="wrapper">
    <div class="login-box">
        <!-- IMPORTANTE: action aponta para o próprio login.php -->
        <form action="login.php" method="POST">
            <h2>INSIRA SUA CONTA</h2>

            <div class="input-box">
                <input type="text" name="usuario" required>
                <label>Usuário</label>
            </div>

            <div class="input-box">
                <input type="password" name="senha" required>
                <label>Senha</label>
            </div>

            <button type="submit" class="btn">Entrar</button><br>

            <div>
                <center><a href="esqueceu_senha.php">Esqueceu a senha?</a><br><br></center>
            </div>

            <div class="registre-link">
                <center><h6>Crie sua conta</h6></center>
                <br>
                <button type="button" class="btn" onclick="location.href='cadastrar.php'">Cadastre-se</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
