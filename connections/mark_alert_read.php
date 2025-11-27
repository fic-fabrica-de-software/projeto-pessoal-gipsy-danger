<?php
require_once "db.php";
require_once "common_functions.php";
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $alert_id = intval($_POST['alert_id'] ?? 0);
    $user_id = $_SESSION["user_id"];

    if ($alert_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit;
    }

    try {
        $check_stmt = $conn->prepare("
            SELECT alert_id FROM stock_alerts 
            WHERE alert_id = ? AND user_id = ?
        ");
        $check_stmt->bind_param("ii", $alert_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Alerta não encontrado']);
            exit;
        }

        $update_stmt = $conn->prepare("UPDATE stock_alerts SET is_read = TRUE WHERE alert_id = ?");
        $update_stmt->bind_param("i", $alert_id);
        
        if ($update_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Alerta marcado como lido']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao marcar alerta como lido']);
        }
        
        $update_stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro no sistema: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}

$conn->close();
?>