<?php
// connections/get_upcoming_appointments.php
require_once "db.php";
require_once "common_functions.php";
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$user_id = $_SESSION["user_id"];
$today = date('Y-m-d');
$next_week = date('Y-m-d', strtotime('+7 days'));

try {
    $app_stmt = $conn->prepare("
        SELECT a.*, d.doctor_name, d.doctor_specialty 
        FROM appointments a 
        LEFT JOIN doctors d ON a.doctor_id = d.doctor_id 
        WHERE a.user_id = ? AND a.appointment_date BETWEEN ? AND ? 
        ORDER BY a.appointment_date, a.appointment_time 
        LIMIT 10
    ");
    $app_stmt->bind_param("iss", $user_id, $today, $next_week);
    $app_stmt->execute();
    $app_result = $app_stmt->get_result();
    
    $appointments = [];
    while ($appointment = $app_result->fetch_assoc()) {
        $appointments[] = $appointment;
    }
    $app_stmt->close();

    echo json_encode([
        'success' => true,
        'appointments' => $appointments
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar consultas: ' . $e->getMessage()
    ]);
}

$conn->close();
?>