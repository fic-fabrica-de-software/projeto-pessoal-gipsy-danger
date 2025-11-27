<?php
require_once "db.php";
require_once "common_functions.php";
session_start();

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] !== true) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $doctor_name = ($_POST["doctor_name"] ?? '');
    $doctor_specialty = ($_POST["doctor_specialty"] ?? '');
    $doctor_phone = ($_POST["doctor_phone"] ?? '');
    $doctor_email = ($_POST["doctor_email"] ?? '');
    $doctor_address = ($_POST["doctor_address"] ?? '');
    $user_id = $_SESSION["user_id"];

    if (empty($doctor_name)) {
        $_SESSION['error'] = "Nome do médico é obrigatório.";
        header("Location: ../pages/doctors.php");
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO doctors 
            (doctor_name, doctor_specialty, doctor_phone, doctor_email, doctor_address, user_id) 
            VALUES (?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("sssssi", 
            $doctor_name, $doctor_specialty, $doctor_phone, 
            $doctor_email, $doctor_address, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Médico cadastrado com sucesso!";
        } else {
            $_SESSION['error'] = "Erro ao cadastrar médico. Verifique se já não existe um médico com este nome.";
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Erro no sistema: " . $e->getMessage();
    }
    
    header("Location: ../pages/doctors.php");
    exit;
}
?>