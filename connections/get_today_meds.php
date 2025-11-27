<?php
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
    $med_stmt = $conn->prepare("
        SELECT 
            m.*, 
            d.doctor_name,
            CASE 
                WHEN mh.med_id IS NOT NULL THEN 'confirmed'
                ELSE 'pending'
            END as status,
            mh.taken_at
        FROM medicaments m 
        LEFT JOIN doctors d ON m.doctor_id = d.doctor_id 
        LEFT JOIN medication_history mh ON m.med_id = mh.med_id AND mh.taken_date = ? AND mh.user_id = ?
        WHERE m.user_id = ? AND FIND_IN_SET(?, m.med_weekdays)
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