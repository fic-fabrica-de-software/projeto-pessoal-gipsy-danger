<?php
// connections/get_notifications.php
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
    // Buscar notificações não lidas + últimas 5 notificações
    $notif_stmt = $conn->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY is_read ASC, created_at DESC 
        LIMIT 5
    ");
    $notif_stmt->bind_param("i", $user_id);
    $notif_stmt->execute();
    $notif_result = $notif_stmt->get_result();
    
    $notifications = [];
    while ($notification = $notif_result->fetch_assoc()) {
        $notifications[] = $notification;
    }
    $notif_stmt->close();

    echo json_encode([
        'success' => true,
        'notifications' => $notifications
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar notificações: ' . $e->getMessage()
    ]);
}

$conn->close();
?>