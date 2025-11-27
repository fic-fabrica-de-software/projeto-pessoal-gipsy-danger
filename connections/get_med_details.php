<?php
require_once "db.php";
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$med_id = intval($_GET['med_id'] ?? 0);
$user_id = $_SESSION["user_id"];

if ($med_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM medicaments WHERE med_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $med_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Medicamento não encontrado']);
        exit;
    }
    
    $medication = $result->fetch_assoc();
    
    if ($medication['doctor_id']) {
        $doctor_stmt = $conn->prepare("SELECT doctor_name, doctor_specialty, doctor_phone, doctor_email FROM doctors WHERE doctor_id = ?");
        $doctor_stmt->bind_param("i", $medication['doctor_id']);
        $doctor_stmt->execute();
        $doctor_result = $doctor_stmt->get_result();
        
        if ($doctor_result->num_rows > 0) {
            $doctor_data = $doctor_result->fetch_assoc();
            $medication['doctor_name'] = $doctor_data['doctor_name'];
            $medication['doctor_specialty'] = $doctor_data['doctor_specialty'];
            $medication['doctor_phone'] = $doctor_data['doctor_phone'];
            $medication['doctor_email'] = $doctor_data['doctor_email'];
        }
        $doctor_stmt->close();
    }
    
    $weekdays_map = [
        '0' => 'Domingo',
        '1' => 'Segunda-feira',
        '2' => 'Terça-feira', 
        '3' => 'Quarta-feira',
        '4' => 'Quinta-feira',
        '5' => 'Sexta-feira',
        '6' => 'Sábado'
    ];
    
    $weekdays_array = explode(',', $medication['med_weekdays']);
    $medication['weekdays_names'] = array_map(function($day) use ($weekdays_map) {
        return $weekdays_map[$day] ?? 'Desconhecido';
    }, $weekdays_array);
    
    echo json_encode([
        'success' => true,
        'medication' => $medication
    ]);
    
    $stmt->close();

} catch (Exception $e) {
    error_log("Erro em get_med_details.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao carregar detalhes'
    ]);
}

$conn->close();
exit;
?>