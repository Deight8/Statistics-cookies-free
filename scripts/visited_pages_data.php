<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

$file = __DIR__ . '/../visitors.xml';
$filter = $_GET['filter'] ?? 'day';

// Vytvoření seznamu všech hodin/dní podle filtru
$timeLabels = [];
if ($filter === 'day') {
    for ($i = 0; $i < 24; $i++) {
        $timeLabels[] = sprintf('%02d:00', $i); // 00:00, 01:00 ... 23:00
    }
} elseif ($filter === 'week') {
    for ($i = 6; $i >= 0; $i--) { // Posledních 7 dní
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

// Inicializujeme pole pro statistiku s nulovými hodnotami
$visitStats = array_fill_keys($timeLabels, 0);

if (!file_exists($file) || filesize($file) == 0) {
    echo json_encode([
        "labels" => array_keys($visitStats),
        "values" => array_values($visitStats),
        "label" => "Počet návštěv podle času",
        "xTitle" => ($filter === 'day' ? 'Hodiny' : 'Dny'),
        "yTitle" => "Počet návštěv"
    ]);
    exit;
}

$xml = simplexml_load_file($file);
if (!$xml) {
    echo json_encode([
        "labels" => array_keys($visitStats),
        "values" => array_values($visitStats),
        "label" => "Počet návštěv podle času",
        "xTitle" => ($filter === 'day' ? 'Hodiny' : 'Dny'),
        "yTitle" => "Počet návštěv"
    ]);
    exit;
}

$visitors = json_decode(json_encode($xml), true);
if (isset($visitors['visitor']) && !isset($visitors['visitor'][0])) {
    $visitors['visitor'] = [$visitors['visitor']];
} elseif (!isset($visitors['visitor'])) {
    $visitors['visitor'] = [];
}

foreach ($visitors['visitor'] as $visitor) {
    if (!isset($visitor['timestamp'])) {
        continue;
    }

    $timestamp = strtotime($visitor['timestamp']);
    $timeKey = ($filter === 'day') ? date('H:00', $timestamp) : date('Y-m-d', $timestamp);

    if (isset($visitStats[$timeKey])) {
        $visitStats[$timeKey]++;
    }
}

// Výstup jako JSON
echo json_encode([
    "labels" => array_keys($visitStats),
    "values" => array_values($visitStats),
    "label" => "Počet návštěv podle času",
    "xTitle" => ($filter === 'day' ? 'Hodiny' : 'Dny'),
    "yTitle" => "Počet návštěv"
]);
exit;
?>
