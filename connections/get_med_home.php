<?php
require_once('db.php');

$sql = "SELECT med_id, med_name, med_dosage, med_type, med_time
        FROM medicaments WHERE user_id = '{$_SESSION['user_id']}' AND med_time >= CURTIME() 
        AND med_weekday = WEEKDAY(CURDATE())
        ORDER BY med_time";

$resultado = $conn->query($sql);
if ($resultado && $resultado->num_rows >= 1) {
    $medicamento = $resultado->fetch_all(MYSQLI_ASSOC);
} else {
    echo "<tr><td class='text-center' colspan='4'>Nenhum medicamento programado para hoje!</td></tr>";
}

$resultado->free();
?>