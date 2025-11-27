<?php
// connections/check_stock_alerts.php
require_once "db.php";
require_once "common_functions.php";

function checkStockAlerts($user_id = null) {
    global $conn;
    
    $where_condition = "m.med_is_active = TRUE AND m.med_remaining > 0";
    $params = [];
    $types = "";
    
    if ($user_id) {
        $where_condition .= " AND m.user_id = ?";
        $params[] = $user_id;
        $types .= "i";
    }
    
    // Buscar medicamentos que precisam de alerta
    $stmt = $conn->prepare("
        SELECT 
            m.med_id,
            m.med_name,
            m.med_remaining,
            m.med_alert_days,
            m.med_acquisition_type,
            m.med_last_alert_date,
            m.user_id,
            (SELECT COUNT(*) FROM stock_alerts sa 
             WHERE sa.med_id = m.med_id AND sa.is_read = FALSE 
             AND DATE(sa.created_at) = CURDATE()) as today_alerts
        FROM medicaments m
        WHERE $where_condition
    ");
    
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $alerts_created = 0;
    
    while ($med = $result->fetch_assoc()) {
        // Calcular dias restantes baseado na frequência (simplificado)
        $remaining_days = $med['med_remaining']; // Aqui poderia ser mais complexo baseado na frequência
        
        // Verificar se precisa de alerta
        if ($remaining_days <= $med['med_alert_days'] && $med['today_alerts'] == 0) {
            createStockAlert($med);
            $alerts_created++;
            
            // Atualizar data do último alerta
            updateLastAlertDate($med['med_id']);
        }
    }
    
    $stmt->close();
    return $alerts_created;
}

function createStockAlert($medication) {
    global $conn;
    
    $alert_type = 'low_stock';
    $days_remaining = $medication['med_remaining'];
    $acquisition_type = $medication['med_acquisition_type'] == 'manipulado' ? 'manipular' : 'comprar';
    
    if ($days_remaining <= 0) {
        $alert_type = 'out_of_stock';
        $message = "🚨 ESTOQUE ESGOTADO: {$medication['med_name']} - É necessário $acquisition_type mais.";
    } else if ($days_remaining <= 3) {
        $message = "⚠️ ESTOQUE CRÍTICO: {$medication['med_name']} - Apenas {$days_remaining} dias restantes. É urgente $acquisition_type mais.";
    } else {
        $message = "📢 ALERTA DE ESTOQUE: {$medication['med_name']} - {$days_remaining} dias restantes. Lembre-se de $acquisition_type mais.";
    }
    
    $stmt = $conn->prepare("
        INSERT INTO stock_alerts (med_id, user_id, alert_type, alert_message) 
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->bind_param("iiss", 
        $medication['med_id'], 
        $medication['user_id'], 
        $alert_type, 
        $message
    );
    
    $stmt->execute();
    $stmt->close();
}

function updateLastAlertDate($med_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        UPDATE medicaments 
        SET med_last_alert_date = CURDATE() 
        WHERE med_id = ?
    ");
    
    $stmt->bind_param("i", $med_id);
    $stmt->execute();
    $stmt->close();
}

// Executar verificação de alertas
if (isset($_GET['check_alerts']) && $_GET['check_alerts'] == 'true') {
    session_start();
    
    if (!isset($_SESSION["conected"]) || $_SESSION["conected"] !== true) {
        echo json_encode(['success' => false, 'message' => 'Não autorizado']);
        exit;
    }
    
    $user_id = $_SESSION["user_id"];
    $alerts_created = checkStockAlerts($user_id);
    
    echo json_encode([
        'success' => true,
        'alerts_created' => $alerts_created,
        'message' => $alerts_created > 0 ? "{$alerts_created} alertas criados" : "Nenhum alerta necessário"
    ]);
}
?>