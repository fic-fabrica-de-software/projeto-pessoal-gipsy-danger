<?php
require_once "db.php";
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $med_id = intval($_POST['med_id'] ?? 0);
    $user_id = $_SESSION["user_id"];

    if ($med_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit;
    }

    try {
        $check_stmt = $conn->prepare("SELECT med_name FROM medicaments WHERE med_id = ? AND user_id = ?");
        $check_stmt->bind_param("ii", $med_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Medicamento não encontrado']);
            exit;
        }
        
        $med_data = $check_result->fetch_assoc();
        $med_name = $med_data['med_name'];

        $delete_stmt = $conn->prepare("DELETE FROM medicaments WHERE med_id = ?");
        $delete_stmt->bind_param("i", $med_id);
        
        if ($delete_stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => "Medicamento '{$med_name}' excluído com sucesso!"
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir medicamento']);
        }
        
        $delete_stmt->close();
        
    } catch (Exception $e) {
        error_log("Erro em delete_med.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erro no sistema']);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}

$conn->close();
exit;
?>