<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=UTF-8');

$visitorsFile = __DIR__ . '/visitors.xml';
$eventsFile = __DIR__ . '/events.xml';

// Funkce pro načtení nebo opravu XML souboru
function loadOrFixXML($file, $rootElement) {
    if (!file_exists($file) || filesize($file) == 0) {
        $xml = new SimpleXMLElement("<$rootElement></$rootElement>");
        $xml->asXML($file);
    } else {
        $xml = simplexml_load_file($file);
        if ($xml === false) {
            $xml = new SimpleXMLElement("<$rootElement></$rootElement>");
            $xml->asXML($file);
        }
    }
    return $xml;
}

// Načtení souborů nebo jejich oprava
$visitorsXML = loadOrFixXML($visitorsFile, "visitors");
$eventsXML = loadOrFixXML($eventsFile, "events");

// ✅ **Zpracování POST požadavku (kliknutí na událost)**
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!empty($data['element']) && !empty($data['page'])) {
        $event = $eventsXML->addChild('event');
        $event->addChild('visitor_id', hash('sha256', $_SERVER['REMOTE_ADDR']));
        $event->addChild('element', htmlspecialchars($data['element']));
        $event->addChild('page', htmlspecialchars($data['page']));
        $event->addChild('referrer', htmlspecialchars($_SERVER['HTTP_REFERER'] ?? 'Přímá návštěva'));
        $event->addChild('utm_source', htmlspecialchars($data['utm_source'] ?? ''));
        $event->addChild('utm_medium', htmlspecialchars($data['utm_medium'] ?? ''));
        $event->addChild('utm_campaign', htmlspecialchars($data['utm_campaign'] ?? ''));
        $event->addChild('timestamp', date('c'));
        $eventsXML->asXML($eventsFile);
        echo json_encode(["status" => "success", "message" => "Událost zaznamenána."]);
        exit;
    }

    echo json_encode(["status" => "error", "message" => "Neplatná data."]);
    exit;
}

// ✅ **Zpracování GET požadavku (návštěva stránky)**
$visitor_id = hash('sha256', $_SERVER['REMOTE_ADDR']);
$screen_width = $_GET['w'] ?? '0';
$screen_height = $_GET['h'] ?? '0';
$screen_resolution = $screen_width . 'x' . $screen_height;

// ✅ **Oprava: Zajištění, že `source` je vždy správně definován**
$http_referer = $_SERVER['HTTP_REFERER'] ?? '';
$js_referer = $_GET['js_referer'] ?? '';
$source = !empty($js_referer) ? $js_referer : (!empty($http_referer) ? $http_referer : 'Přímá návštěva');

// ✅ **Oprava: `language` nesmí být prázdné**
$language = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'cs';

// ✅ **Vytvoření pole pro data návštěvníka**
$visitorData = [
    'visitor_id' => $visitor_id,
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'referer' => htmlspecialchars($http_referer),
    'source' => htmlspecialchars($source),
    'language' => htmlspecialchars($language),
    'screen_resolution' => $screen_resolution,
    'timestamp' => date('Y-m-d H:i:s')
];

// ✅ **Oprava: Kontrola duplicity musí zahrnovat `source`, `referer` a `screen_resolution`**
$duplicate = false;
foreach ($visitorsXML->visitor as $existingVisitor) {
    if (
        (string)$existingVisitor->visitor_id === $visitor_id &&
        (string)$existingVisitor->screen_resolution === $screen_resolution &&
        (string)$existingVisitor->source === $source &&
        (string)$existingVisitor->referer === $http_referer
    ) {
        $lastTimestamp = strtotime((string)$existingVisitor->timestamp);
        if (time() - $lastTimestamp < 300) { // 300 sekund = 5 minut
            $duplicate = true;
            break;
        }
    }
}

// ✅ **Pokud návštěvník není duplicitní, přidáme ho do `visitors.xml`**
if (!$duplicate) {
    $entry = $visitorsXML->addChild('visitor');
    foreach ($visitorData as $key => $value) {
        $entry->addChild($key, htmlspecialchars($value));
    }
    $visitorsXML->asXML($visitorsFile);
}

echo json_encode(["status" => "success", "message" => "Návštěva zaznamenána."]);
exit;
