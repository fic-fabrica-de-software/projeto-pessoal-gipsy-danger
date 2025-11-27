<?php
require "db.php";
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");

    $stmt = $conn->prepare("SELECT user_id, user_name, user_password_hash FROM users WHERE user_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $dados = $resultado->fetch_assoc();

        if (password_verify($password, $dados["user_password_hash"])) {
            $_SESSION["user_name"] = $dados["user_name"];
            $_SESSION["user_id"] = $dados["user_id"];
            $_SESSION["user_email"] = $email;
            $_SESSION["conected"] = true;
            header("Location: ../pages/home.php");
            exit;
        } else {
            $_SESSION['error'] = "E-mail ou senha inválidos.";
            header("Location: ../index.php");
        }
    } else {
        $_SESSION['error'] = "E-mail ou senha inválidos.";
        header("Location: ../index.php");
    }
}
?>