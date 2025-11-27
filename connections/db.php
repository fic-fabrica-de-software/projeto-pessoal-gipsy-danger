<?php
// conexao.php
// Conexão simples ao MySQL usando mysqli (XAMPP). Ajuste usuário/senha se necessário.

$host     = "localhost:3307";
$usuario  = "root";
$senha    = "";
$banco    = "medset";

// Cria a conexão
$conn = new mysqli($host, $usuario, $senha, $banco);

// Verifica erro de conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$stmt=$conn->prepare("ALTER TABLE users AUTO_INCREMENT = 1");
$stmt->execute();
$stmt=$conn->prepare("ALTER TABLE medicaments AUTO_INCREMENT = 1");
$stmt->execute();

$conn->set_charset("utf8");

date_default_timezone_set("America/Sao_Paulo");