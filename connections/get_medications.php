<?php
require_once "db.php";
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$user_id = $_SESSION["user_id"];
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = intval($_GET['page'] ?? 1);
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    $query = "SELECT * FROM medicaments WHERE user_id = ?";
    $params = [$user_id];
    $types = "i";

    if (!empty($search)) {
        $query .= " AND (med_name LIKE ? OR med_brand LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= "ss";
    }

    $query .= " ORDER BY med_name, med_time LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($query);
    
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $medications = [];
    while ($med = $result->fetch_assoc()) {
        $medications[] = $med;
    }
    $stmt->close();

    $count_query = "SELECT COUNT(*) as total FROM medicaments WHERE user_id = ?";
    $count_params = [$user_id];
    $count_types = "i";

    if (!empty($search)) {
        $count_query .= " AND (med_name LIKE ? OR med_brand LIKE ?)";
        $count_params[] = $search_term;
        $count_params[] = $search_term;
        $count_types .= "ss";
    }

    $count_stmt = $conn->prepare($count_query);
    if ($count_params) {
        $count_stmt->bind_param($count_types, ...$count_params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total = $count_result->fetch_assoc()['total'];
    $count_stmt->close();

    $response = [
        'success' => true,
        'medications' => $medications,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    error_log("Erro em get_medications.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor'
    ]);
}

$conn->close();
exit;
?>