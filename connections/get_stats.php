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
    $stats = [
        'total_meds' => 0,
        'upcoming_apps' => 0, 
        'total_doctors' => 0,
        'today_taken' => 0,
        'today_meds' => 0
    ];

    $stmt1 = $conn->prepare("SELECT COUNT(*) as total FROM medicaments WHERE user_id = ?");
    $stmt1->bind_param("i", $user_id);
    $stmt1->execute();
    $result1 = $stmt1->get_result();
    if ($row = $result1->fetch_assoc()) {
        $stats['total_meds'] = $row['total'];
    }
    $stmt1->close();

    $stmt2 = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE user_id = ? AND appointment_date >= ?");
    $stmt2->bind_param("is", $user_id, $today);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    if ($row = $result2->fetch_assoc()) {
        $stats['upcoming_apps'] = $row['total'];
    }
    $stmt2->close();

    $stmt3 = $conn->prepare("SELECT COUNT(*) as total FROM doctors WHERE user_id = ?");
    $stmt3->bind_param("i", $user_id);
    $stmt3->execute();
    $result3 = $stmt3->get_result();
    if ($row = $result3->fetch_assoc()) {
        $stats['total_doctors'] = $row['total'];
    }
    $stmt3->close();

    $stmt4 = $conn->prepare("SELECT COUNT(*) as total FROM medication_history WHERE user_id = ? AND taken_date = ? AND status = 'taken'");
    $stmt4->bind_param("is", $user_id, $today);
    $stmt4->execute();
    $result4 = $stmt4->get_result();
    if ($row = $result4->fetch_assoc()) {
        $stats['today_taken'] = $row['total'];
    }
    $stmt4->close();

    $stmt5 = $conn->prepare("SELECT COUNT(*) as total FROM medicaments WHERE user_id = ? AND FIND_IN_SET(?, med_weekdays)");
    $stmt5->bind_param("ii", $user_id, $today_weekday);
    $stmt5->execute();
    $result5 = $stmt5->get_result();
    if ($row = $result5->fetch_assoc()) {
        $stats['today_meds'] = $row['total'];
    }
    $stmt5->close();

    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);

} catch (Exception $e) {
    error_log("Erro em get_stats.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar estatísticas'
    ]);
}

$conn->close();
?>