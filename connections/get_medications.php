<?php
// connections/get_medications.php (versão simplificada)
require_once "db.php";
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$user_id = $_SESSION["user_id"];
$filter = $_GET['filter'] ?? 'all';
$page = intval($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    // Query base
    $query = "
        SELECT 
            m.*, 
            d.doctor_name,
            d.doctor_specialty,
            (SELECT COUNT(*) FROM medication_history mh WHERE mh.med_id = m.med_id AND mh.status = 'taken') as times_taken
        FROM medicaments m 
        LEFT JOIN doctors d ON m.doctor_id = d.doctor_id 
        WHERE m.user_id = ?
    ";
    
    $params = [$user_id];
    $types = "i";

    if ($filter === 'week') {
    $today_weekday = date('w');
    $week_condition = "";
    for ($i = 0; $i < 7; $i++) {
        $weekday = ($today_weekday + $i) % 7;
        if ($i > 0) $week_condition .= " OR ";
        $week_condition .= "FIND_IN_SET(?, m.med_weekdays)";
        $params[] = $weekday;
        $types .= "i";
    }
    $query .= " AND ($week_condition)";
}

    $query .= " ORDER BY m.med_name, m.med_time LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    $med_stmt = $conn->prepare($query);
    $med_stmt->bind_param($types, ...$params);
    $med_stmt->execute();
    $med_result = $med_stmt->get_result();
    
    $medications = [];
    while ($med = $med_result->fetch_assoc()) {
        // Aplicar filtro de semana no PHP se necessário
        if ($filter === 'week') {
            $today_weekday = date('w');
            $weekdays = explode(',', $med['med_weekdays']);
            $hasWeekday = false;
            for ($i = 0; $i < 7; $i++) {
                $check_day = ($today_weekday + $i) % 7;
                if (in_array($check_day, $weekdays)) {
                    $hasWeekday = true;
                    break;
                }
            }
            if ($hasWeekday) {
                $medications[] = $med;
            }
        } else {
            $medications[] = $med;
        }
    }
    $med_stmt->close();

    // Contagem total (sem filtro de semana no PHP para não complicar)
    $count_query = "SELECT COUNT(*) as total FROM medicaments m WHERE m.user_id = ?";
    if ($filter === 'today') {
        $today_weekday = date('w');
        $count_query .= " AND FIND_IN_SET(?, m.med_weekdays)";
    }
    
    $count_stmt = $conn->prepare($count_query);
    if ($filter === 'today') {
        $count_stmt->bind_param("ii", $user_id, $today_weekday);
    } else {
        $count_stmt->bind_param("i", $user_id);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total = $count_result->fetch_assoc()['total'];
    $count_stmt->close();

    echo json_encode([
        'success' => true,
        'medications' => $medications,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar medicamentos: ' . $e->getMessage()
    ]);
}

$conn->close();
?>