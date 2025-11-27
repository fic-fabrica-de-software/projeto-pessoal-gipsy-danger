<?php
// connections/get_stats.php (corrigido)
require_once "db.php";
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$user_id = $_SESSION["user_id"];
$today = date('Y-m-d');
$today_weekday = date('w');

try {
    // Estatísticas básicas - CORRIGIDAS
    $stats_stmt = $conn->prepare("
        SELECT 
            (SELECT COUNT(*) FROM medicaments WHERE user_id = ?) as total_meds,
            (SELECT COUNT(*) FROM appointments WHERE user_id = ? AND appointment_date >= ?) as upcoming_apps,
            (SELECT COUNT(*) FROM doctors WHERE user_id = ?) as total_doctors,
            (SELECT COUNT(*) FROM medication_history WHERE user_id = ? AND taken_date = ? AND status = 'taken') as today_taken,
            (SELECT COUNT(*) FROM medicaments WHERE user_id = ? AND FIND_IN_SET(?, med_weekdays)) as today_meds
    ");
    $stats_stmt->bind_param("iisiiii", 
        $user_id, $user_id, $today, $user_id, $user_id, $today, $user_id, $today_weekday);
    $stats_stmt->execute();
    $stats_result = $stats_stmt->get_result();
    $stats = $stats_result->fetch_assoc();
    $stats_stmt->close();

    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar estatísticas: ' . $e->getMessage()
    ]);
}

$conn->close();
?>