<?php
// connections/confirm_med.php
require_once "db.php";
require_once "common_functions.php";
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents('php://input'), true);
    $med_id = intval($input['med_id'] ?? 0);
    $status = ($input['status'] ?? 'taken');
    $notes = ($input['notes'] ?? '');
    
    $user_id = $_SESSION["user_id"];
    $today = date('Y-m-d');
    $now = date('Y-m-d H:i:s');

    if ($med_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID do medicamento inválido']);
        exit;
    }

    try {
        // Verificar se o medicamento pertence ao usuário
        $check_stmt = $conn->prepare("SELECT med_id FROM medicaments WHERE med_id = ? AND user_id = ?");
        $check_stmt->bind_param("ii", $med_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Medicamento não encontrado']);
            exit;
        }

        // Verificar se já foi confirmado hoje
        $history_stmt = $conn->prepare("SELECT history_id FROM medication_history WHERE med_id = ? AND user_id = ? AND taken_date = ?");
        $history_stmt->bind_param("iis", $med_id, $user_id, $today);
        $history_stmt->execute();
        $history_result = $history_stmt->get_result();
        
        if ($history_result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Medicamento já confirmado para hoje']);
            exit;
        }

        // Registrar no histórico
        $insert_stmt = $conn->prepare("INSERT INTO medication_history (med_id, user_id, taken_at, taken_date, status, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("iissss", $med_id, $user_id, $now, $today, $status, $notes);
        
        if ($insert_stmt->execute()) {
            // Atualizar quantidade restante se necessário
            if ($status === 'taken') {
                $update_stmt = $conn->prepare("UPDATE medicaments SET med_remaining = med_remaining - 1 WHERE med_id = ? AND med_remaining > 0");
                $update_stmt->bind_param("i", $med_id);
                $update_stmt->execute();
                $update_stmt->close();
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Medicamento confirmado com sucesso!',
                'history_id' => $insert_stmt->insert_id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao registrar confirmação']);
        }
        
        $insert_stmt->close();
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro no sistema: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}

$conn->close();
?>