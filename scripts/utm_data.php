<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

$file = __DIR__ . '/../visitors.xml';
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

$utmStats = array_fill_keys($timeLabels, 0);

if (!file_exists($file) || filesize($file) == 0) {
    echo json_encode([
        "labels" => array_keys($utmStats),
        "values" => array_values($utmStats),
        "label" => "Počet návštěv z UTM kampaní",
        "xTitle" => ($filter === 'day' ? 'Hodiny' : 'Dny'),
        "yTitle" => "Počet návštěv"
    ]);
    exit;
}

$xml = simplexml_load_file($file);
if (!$xml) {
    echo json_encode([
        "labels" => array_keys($utmStats),
        "values" => array_values($utmStats),
        "label" => "Počet návštěv z UTM kampaní",
        "xTitle" => ($filter === 'day' ? 'Hodiny' : 'Dny'),
        "yTitle" => "Počet návštěv"
    ]);
    exit;
}

$visitors = json_decode(json_encode($xml), true);
$visitors['visitor'] = $visitors['visitor'] ?? [];

foreach ($visitors['visitor'] as $visitor) {
    if (empty($visitor['utm_source']) || empty($visitor['utm_medium']) || empty($visitor['utm_campaign'])) {
        continue;
    }

    $timestamp = strtotime($visitor['timestamp']);
    $timeKey = ($filter === 'day') ? date('H:00', $timestamp) : date('Y-m-d', $timestamp);

    if (isset($utmStats[$timeKey])) {
        $utmStats[$timeKey]++;
    }
}

echo json_encode([
    "labels" => array_keys($utmStats),
    "values" => array_values($utmStats),
    "label" => "Počet návštěv z UTM kampaní",
    "xTitle" => ($filter === 'day' ? 'Hodiny' : 'Dny'),
    "yTitle" => "Počet návštěv"
]);
exit;
?>
