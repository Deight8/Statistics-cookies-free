<?php
header('Content-Type: application/json');

// Debugging – vypíšeme všechny HTTP hlavičky pro kontrolu
file_put_contents('debug_event.log', "HEADERS:\n" . print_r(getallheaders(), true) . "\n", FILE_APPEND);

// Načteme příchozí JSON
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Debugging – uložíme, co bylo přijato
file_put_contents('debug_event.log', "RAW JSON INPUT:\n" . $input . "\n\nDecoded:\n" . print_r($data, true), FILE_APPEND);

// Pokud JSON není platný, vrátíme chybu
if (!$data || !isset($data['element']) || !isset($data['page'])) {
    echo json_encode(["error" => "Invalid event data.", "received" => $data]);
    exit;
}

// Ochrana identity - hashování IP
function getAnonymizedIP($ip) {
    $salt = "random_salt_string";
    return hash('sha256', $ip . $salt);
}

$file = '../events.xml';

// **Pokud XML neexistuje, vytvoříme ho**
if (!file_exists($file)) {
    $xml = new SimpleXMLElement('<events></events>');
} else {
    $xml = simplexml_load_file($file);
}

// **Přidání nové události**
$eventData = [
    'visitor_id' => getAnonymizedIP($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
    'element' => htmlspecialchars($data['element']),
    'page' => htmlspecialchars($data['page']),
    'referrer' => htmlspecialchars($data['referrer'] ?? ''),
    'utm_source' => htmlspecialchars($data['utm_source'] ?? ''),
    'utm_medium' => htmlspecialchars($data['utm_medium'] ?? ''),
    'utm_campaign' => htmlspecialchars($data['utm_campaign'] ?? ''),
    'timestamp' => $data['timestamp']
];

$entry = $xml->addChild('event');
foreach ($eventData as $key => $value) {
    $entry->addChild($key, $value);
}

// **Uložíme změny do XML**
$xml->asXML($file);
echo json_encode(["success" => "Event saved."]);
?>
