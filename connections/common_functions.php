<?php

function validate_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function validate_time($time) {
    return preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/', $time);
}

function get_weekday_name($weekday) {
    $days = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
    return $days[$weekday] ?? 'Desconhecido';
}

function get_weekday_names_from_string($weekdays_string) {
    $days_map = [
        '0' => 'Domingo',
        '1' => 'Segunda', 
        '2' => 'Terça',
        '3' => 'Quarta',
        '4' => 'Quinta',
        '5' => 'Sexta',
        '6' => 'Sábado'
    ];
    
    $days_array = explode(',', $weekdays_string);
    $names = [];
    
    foreach ($days_array as $day) {
        if (isset($days_map[$day])) {
            $names[] = $days_map[$day];
        }
    }
    
    return implode(', ', $names);
}

function is_medication_for_today($weekdays_string) {
    $today_weekday = date('w');
    $days_array = explode(',', $weekdays_string);
    return in_array($today_weekday, $days_array);
}

function calculate_remaining_days($remaining, $frequency) {
    $days_per_unit = 1;
    
    switch($frequency) {
        case 'diario':
            $days_per_unit = 1;
            break;
        case 'alternado':
            $days_per_unit = 2;
            break;
        case 'semanal':
            $days_per_unit = 7;
            break;
        case 'mensal':
            $days_per_unit = 30;
            break;
    }
    
    return $remaining * $days_per_unit;
}
?>