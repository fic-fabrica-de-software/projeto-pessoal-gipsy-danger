<?php
require_once "db.php";
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION["conected"])) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$user_id = $_SESSION["user_id"];

try {
    $query = "SELECT * FROM appointments WHERE user_id = ? LIMIT 3";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments = [];
    while ($app = $result->fetch_assoc()) {
        $appointments[] = $app;
    }
    
    echo json_encode(['success' => true, 'appointments' => $appointments]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}

$conn->close();
?>