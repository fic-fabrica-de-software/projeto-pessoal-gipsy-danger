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
?>