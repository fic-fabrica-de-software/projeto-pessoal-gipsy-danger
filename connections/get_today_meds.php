<?php
// connections/get_today_meds.php
require_once "db.php";
require_once "common_functions.php";
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$user_id = $_SESSION["user_id"];
$today_weekday = date('w'); // 0=Dom, 1=Seg, etc.
$today = date('Y-m-d');

try {
    // Buscar medicamentos de hoje com status
    $med_stmt = $conn->prepare("
        SELECT 
            m.*, 
            d.doctor_name,
            COALESCE(mh.status, 'pending') as today_status,
            mh.notes as today_notes
        FROM medicaments m 
        LEFT JOIN doctors d ON m.doctor_id = d.doctor_id 
        LEFT JOIN medication_history mh ON m.med_id = mh.med_id AND mh.taken_date = ? AND mh.user_id = ?
        WHERE m.user_id = ? AND m.med_weekday = ? 
        ORDER BY m.med_time
    ");
    $med_stmt->bind_param("siii", $today, $user_id, $user_id, $today_weekday);
    $med_stmt->execute();
    $med_result = $med_stmt->get_result();
    
    $medications = [];
    while ($med = $med_result->fetch_assoc()) {
        $medications[] = $med;
    }
    $med_stmt->close();

    echo json_encode([
        'success' => true,
        'medications' => $medications
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar medicamentos: ' . $e->getMessage()
    ]);
}

$conn->close();
?>