<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

$file = __DIR__ . '/../events.xml';
$filter = $_GET['filter'] ?? 'day';

// Vytvoření seznamu všech časových úseků podle filtru
$timeLabels = [];
if ($filter === 'day') {
    for ($i = 0; $i < 24; $i++) {
        $timeLabels[] = sprintf('%02d:00', $i);
    }
} elseif ($filter === 'week') {
    for ($i = 6; $i >= 0; $i--) {
        $timeLabels[] = date('Y-m-d', strtotime("-$i days"));
    }
} elseif ($filter === 'month') {
    $currentYear = date('Y');
    $currentMonth = date('m');
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
    
    for ($i = 1; $i <= $daysInMonth; $i++) {
        $timeLabels[] = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $i);
    }
}

$eventStats = array_fill_keys($timeLabels, 0);

echo json_encode([
    "labels" => array_keys($eventStats),
    "values" => array_values($eventStats),
    "label" => "Počet kliknutí na události",
    "xTitle" => ($filter === 'day' ? 'Hodiny' : 'Dny'),
    "yTitle" => "Počet kliknutí"
]);
exit;
?>
