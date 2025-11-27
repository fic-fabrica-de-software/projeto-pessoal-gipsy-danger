<?php
// connections/create_med.php (versão corrigida)
require_once "db.php";
require_once "common_functions.php";
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION["conected"]) || $_SESSION["conected"] !== true) {
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Coletar e sanitizar dados
    $med_nome = ($_POST["med_nome"] ?? '');
    $med_brand = ($_POST["med_brand"] ?? '');
    $med_dosage = floatval($_POST["med_dosage"] ?? 0);
    $med_type = ($_POST["med_type"] ?? '');
    $med_milligram = floatval($_POST["med_milligram"] ?? 0);
    $med_milligram_unit = ($_POST["med_milligram_unit"] ?? '');
    $med_time = ($_POST["med_time"] ?? '');
    
    // Dias da semana (array) - agora vamos armazenar como string
    $med_weekdays = isset($_POST["med_weekday"]) ? $_POST["med_weekday"] : [];
    $med_frequency = ($_POST["med_frequency"] ?? 'diario');
    
    // Informações de estoque
    $med_acquisition_type = ($_POST["med_acquisition_type"] ?? 'comprado');
    $med_remaining = !empty($_POST["med_remaining"]) ? intval($_POST["med_remaining"]) : 0;
    $med_alert_days = !empty($_POST["med_alert_days"]) ? intval($_POST["med_alert_days"]) : 7;
    
    // Datas
    $med_begindate = !empty($_POST["med_begindate"]) ? $_POST["med_begindate"] : null;
    $med_enddate = !empty($_POST["med_enddate"]) ? $_POST["med_enddate"] : null;
    $med_expirydate = !empty($_POST["med_expirydate"]) ? $_POST["med_expirydate"] : null;
    
    // Informações adicionais
    $med_price = !empty($_POST["med_price"]) ? floatval($_POST["med_price"]) : null;
    $med_place_purchase = ($_POST["med_place_purchase"] ?? '');
    $med_notes = ($_POST["med_notes"] ?? '');
    $doctor_id = !empty($_POST["doctor_id"]) ? intval($_POST["doctor_id"]) : null;
    
    $user_id = $_SESSION["user_id"];

    // Validar dados obrigatórios
    if (empty($med_nome) || empty($med_dosage) || empty($med_time) || empty($med_weekdays)) {
        echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
        exit;
    }

    if (!validate_time($med_time)) {
        echo json_encode(['success' => false, 'message' => 'Horário inválido.']);
        exit;
    }

    // Validar datas
    if ($med_begindate && !validate_date($med_begindate)) {
        echo json_encode(['success' => false, 'message' => 'Data de início inválida.']);
        exit;
    }

    if ($med_enddate && !validate_date($med_enddate)) {
        echo json_encode(['success' => false, 'message' => 'Data de término inválida.']);
        exit;
    }

    if ($med_expirydate && !validate_date($med_expirydate)) {
        echo json_encode(['success' => false, 'message' => 'Data de validade inválida.']);
        exit;
    }

    try {
        // Converter array de dias para string (ex: "1,3,5" para Seg, Qua, Sex)
        $weekdays_string = implode(',', $med_weekdays);
        
        // Inserir UM ÚNICO registro
        $stmt = $conn->prepare("INSERT INTO medicaments 
            (med_name, med_brand, med_dosage, med_type, med_milligram, med_milligram_unit, 
             med_time, med_weekdays, med_frequency, med_begindate, med_enddate, med_expirydate,
             med_acquisition_type, med_remaining, med_alert_days, med_price, med_place_purchase, med_notes,
             doctor_id, user_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("ssdssdsssssssiidssii", 
            $med_nome, $med_brand, $med_dosage, $med_type, 
            $med_milligram, $med_milligram_unit, $med_time, 
            $weekdays_string, $med_frequency, $med_begindate, $med_enddate, $med_expirydate,
            $med_acquisition_type, $med_remaining, $med_alert_days, $med_price, $med_place_purchase, $med_notes,
            $doctor_id, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => "Medicamento adicionado com sucesso!"
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => "Erro ao adicionar medicamento: " . $stmt->error
            ]);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Erro no sistema: ' . $e->getMessage()
        ]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}

$conn->close();
?>