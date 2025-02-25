<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

$range = $_GET['range'] ?? 'day';
$today = new DateTime();

switch ($range) {
    case 'week':
        $start_date = (clone $today)->modify('-6 days');
        break;
    case 'month':
        $start_date = (clone $today)->modify('-29 days');
        break;
    case 'day':
    default:
        $start_date = clone $today;
        break;
}

// Funkce pro načtení XML dat
function loadXMLData($file) {
    if (!file_exists($file)) {
        return [];
    }
    $xml = simplexml_load_file($file);
    return $xml ? $xml : [];
}

// Načtení dat
$visitors = loadXMLData(VISITORS_FILE);
$events = loadXMLData(EVENTS_FILE);

$filtered_visitors = [];
$filtered_events = [];

// Filtrování návštěvníků podle časového rozsahu
foreach ($visitors->visitor as $visitor) {
    $visit_date = DateTime::createFromFormat('Y-m-d H:i:s', (string)$visitor->timestamp);
    if (!$visit_date) {
        continue; // Pokud se nepodaří převést datum, přeskočíme záznam
    }
    if ($visit_date >= $start_date && $visit_date <= $today) {
        $filtered_visitors[] = [
            'timestamp' => $visit_date->format('Y-m-d H:i:s'),
            'visitor_id' => (string)$visitor->visitor_id,
            'referer' => (string)$visitor->referer,
            'user_agent' => (string)$visitor->user_agent
        ];
    }
}

// Filtrování událostí podle časového rozsahu
foreach ($events->event as $event) {
    $event_date = DateTime::createFromFormat(DateTime::ATOM, (string)$event->timestamp);
    if (!$event_date) {
        continue; // Pokud se nepodaří převést datum, přeskočíme záznam
    }
    if ($event_date >= $start_date && $event_date <= $today) {
        $filtered_events[] = [
            'timestamp' => $event_date->format('Y-m-d H:i:s'),
            'visitor_id' => (string)$event->visitor_id,
            'element' => (string)$event->element,
            'page' => (string)$event->page,
            'referrer' => (string)$event->referrer
        ];
    }
}

// Výstup v JSON formátu
echo json_encode([
    'start_date' => $start_date->format('Y-m-d'),
    'end_date' => $today->format('Y-m-d'),
    'visitors' => $filtered_visitors,
    'events' => $filtered_events
]);
exit;
