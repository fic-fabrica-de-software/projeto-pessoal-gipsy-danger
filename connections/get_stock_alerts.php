<?php
require_once "db.php";
require_once "common_functions.php";
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$user_id = $_SESSION["user_id"];

try {
    $alerts_stmt = $conn->prepare("
        SELECT 
            sa.*,
            m.med_name,
            m.med_acquisition_type
        FROM stock_alerts sa
        JOIN medicaments m ON sa.med_id = m.med_id
        WHERE sa.user_id = ? AND sa.is_read = FALSE
        ORDER BY sa.created_at DESC
        LIMIT 10
    ");
    $alerts_stmt->bind_param("i", $user_id);
    $alerts_stmt->execute();
    $alerts_result = $alerts_stmt->get_result();
    
    $alerts = [];
    while ($alert = $alerts_result->fetch_assoc()) {
        $alerts[] = $alert;
    }
    $alerts_stmt->close();

    echo json_encode([
        'success' => true,
        'alerts' => $alerts
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar alertas: ' . $e->getMessage()
    ]);
}

$conn->close();
?>