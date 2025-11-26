<?php
require "db.php";

session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $email = trim($_POST["email"] ?? "");
    if (preg_match('/^.{6,26}$/', (trim($_POST["password"] ?? "")))) {
        $password = password_hash(trim($_POST["password"] ?? ""), PASSWORD_BCRYPT);

        $checkEmailStmt = $conn->prepare("SELECT user_email FROM users WHERE user_email = ?");
        $checkEmailStmt->bind_param("s", $email);
        $checkEmailStmt->execute();
        $checkEmailStmt->store_result();

        if ($checkEmailStmt->num_rows > 0) {
            $error = "E-mail já cadastrado.";
            header("Location: ../pages/register_user.php");
        } else {
            $stmt = $conn->prepare("INSERT INTO users(user_name, user_email, user_password_hash) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $password);
            if ($stmt->execute()) {
                $_SESSION["user_name"] = $dados["user_name"];
                $_SESSION["user_id"] = $dados["pk_user"];
                $_SESSION["conected"] = true;
                header("Location: ../pages/home.php");
                exit;
            } else {
                $error = "Erro ao cadastrar usuário.";
                header("Location: ../pages/register_user.php");
            }
        }
    } else {
        $error = "A senha deve conter entre 6 e 26 caracteres alfabéticos.";
        header("Location: ../pages/register_user.php");
    }
}