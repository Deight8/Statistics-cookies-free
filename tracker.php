<?php
// Vypnutí zobrazování chyb pro bezpečnost
ini_set('display_errors', 0);
error_reporting(0);

// Cesty k XML souborům
$visitorsFile = __DIR__ . '/visitors.xml';
$eventsFile = __DIR__ . '/events.xml';

// Pokud byl požadavek přes AJAX (event), zpracujeme ho
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if ($data && isset($data['element']) && isset($data['page'])) {
        if (!file_exists($eventsFile)) {
            $xml = new SimpleXMLElement('<events></events>');
        } else {
            $xml = simplexml_load_file($eventsFile);
            if (!$xml) exit(1);
        }

        $event = $xml->addChild('event');
        $event->addChild('visitor_id', hash('sha256', $_SERVER['REMOTE_ADDR']));
        $event->addChild('element', htmlspecialchars($data['element']));
        $event->addChild('page', htmlspecialchars($data['page']));
        $event->addChild('referrer', htmlspecialchars($_SERVER['HTTP_REFERER'] ?? 'Přímá návštěva'));
        $event->addChild('utm_source', htmlspecialchars($data['utm_source'] ?? ''));
        $event->addChild('utm_medium', htmlspecialchars($data['utm_medium'] ?? ''));
        $event->addChild('utm_campaign', htmlspecialchars($data['utm_campaign'] ?? ''));
        $event->addChild('timestamp', date('c'));

        $xml->asXML($eventsFile);
        exit(0);
    }
}

// Pokud byl požadavek normální (návštěva), uložíme ji do `visitors.xml`
$visitorData = [
    'visitor_id' => hash('sha256', $_SERVER['REMOTE_ADDR']),
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'referer' => $_SERVER['HTTP_REFERER'] ?? 'direct',
    'source' => $_GET['js_referer'] ?? ($_SERVER['HTTP_REFERER'] ?? 'Přímá návštěva'),
    'language' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'unknown',
    'screen_resolution' => ($_GET['w'] ?? 'unknown') . 'x' . ($_GET['h'] ?? 'unknown'),
    'timestamp' => date('Y-m-d H:i:s')
];

if (!file_exists($visitorsFile)) {
    $xml = new SimpleXMLElement('<visitors></visitors>');
} else {
    $xml = simplexml_load_file($visitorsFile);
    if (!$xml) exit(1);
}

$entry = $xml->addChild('visitor');
foreach ($visitorData as $key => $value) {
    $entry->addChild($key, $value);
}

$xml->asXML($visitorsFile);

// Vrácení JavaScriptu do stránky
header('Content-Type: application/javascript');
echo <<<JS
document.addEventListener("DOMContentLoaded", function() {
    let referer = document.referrer ? encodeURIComponent(document.referrer) : "Přímá návštěva";
    let url = "/deight-statistics/tracker.php?w=" + screen.width + "&h=" + screen.height + "&js_referer=" + referer;

    // Sledujeme návštěvu
    fetch(url).then(response => console.log("✅ Návštěva zaznamenána: " + referer));

    // Pokud existují prvky `data-track`, sledujeme kliknutí
    if (document.querySelector("[data-track]")) {
        document.body.addEventListener("click", function(event) {
            let target = event.target.closest("[data-track]");
            if (!target) return;

            let eventData = {
                element: target.dataset.track,
                page: window.location.href,
                referrer: document.referrer || "Přímá návštěva",
                utm_source: new URLSearchParams(window.location.search).get("utm_source") || "",
                utm_medium: new URLSearchParams(window.location.search).get("utm_medium") || "",
                utm_campaign: new URLSearchParams(window.location.search).get("utm_campaign") || "",
                timestamp: new Date().toISOString()
            };

            fetch("/deight-statistics/tracker.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(eventData)
            }).then(response => response.json())
            .then(data => console.log("✅ Událost zaznamenána:", data))
            .catch(error => console.error("❌ Chyba při odesílání události:", error));
        });
    }
});
JS;
