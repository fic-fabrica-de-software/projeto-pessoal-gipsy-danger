<?php
require_once "db.php";
require_once "common_functions.php";
session_start();

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] !== true) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $appointment_date = ($_POST["appointment_date"] ?? '');
    $appointment_time = ($_POST["appointment_time"] ?? '');
    $appointment_type = ($_POST["appointment_type"] ?? '');
    $appointment_location = ($_POST["appointment_location"] ?? '');
    $appointment_notes = ($_POST["appointment_notes"] ?? '');
    $doctor_id = !empty($_POST["doctor_id"]) ? intval($_POST["doctor_id"]) : null;
    $user_id = $_SESSION["user_id"];

    // Validações
    if (empty($appointment_date) || empty($appointment_time)) {
        $_SESSION['error'] = "Data e horário são obrigatórios.";
        header("Location: ../pages/appointments.php");
        exit;
    }

    if (!validate_date($appointment_date)) {
        $_SESSION['error'] = "Data inválida.";
        header("Location: ../pages/appointments.php");
        exit;
    }

    if (!validate_time($appointment_time)) {
        $_SESSION['error'] = "Horário inválido.";
        header("Location: ../pages/appointments.php");
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO appointments 
            (appointment_date, appointment_time, appointment_type, appointment_location, 
             appointment_notes, doctor_id, user_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("sssssii", 
            $appointment_date, $appointment_time, $appointment_type, 
            $appointment_location, $appointment_notes, $doctor_id, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Consulta agendada com sucesso!";
        } else {
            $_SESSION['error'] = "Erro ao agendar consulta.";
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Erro no sistema: " . $e->getMessage();
    }
    
    header("Location: ../pages/appointments.php");
    exit;
}
?>