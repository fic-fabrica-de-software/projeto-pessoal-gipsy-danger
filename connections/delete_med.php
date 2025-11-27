<?php
// connections/delete_med.php
require_once "db.php";
require_once "common_functions.php";
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] !== true) {
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
        // Verificar se o medicamento pertence ao usuário
        $check_stmt = $conn->prepare("SELECT med_id FROM medicaments WHERE med_id = ? AND user_id = ?");
        $check_stmt->bind_param("ii", $med_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Medicamento não encontrado']);
            exit;
        }

        // Deletar (usando transação para garantir integridade)
        $conn->begin_transaction();

        // Deletar histórico primeiro (devido às foreign keys)
        $history_stmt = $conn->prepare("DELETE FROM medication_history WHERE med_id = ?");
        $history_stmt->bind_param("i", $med_id);
        $history_stmt->execute();
        $history_stmt->close();

        // Deletar medicamento
        $delete_stmt = $conn->prepare("DELETE FROM medicaments WHERE med_id = ?");
        $delete_stmt->bind_param("i", $med_id);
        
        if ($delete_stmt->execute()) {
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Medicamento excluído com sucesso']);
        } else {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir medicamento']);
        }
        
        $delete_stmt->close();
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Erro no sistema: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}

$conn->close();
?>