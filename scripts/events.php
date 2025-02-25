<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$file = __DIR__ . '/../events.xml';

// Pokud soubor neexistuje nebo je prázdný, zobrazíme informaci a ukončíme jen tento skript
if (!file_exists($file) || filesize($file) == 0) {
    echo "<p>Žádné události nejsou k dispozici.</p>";
    return; // Tím zajistíme, že se zbytek stránky načte správně
}

// Načtení XML s kontrolou
$xml = simplexml_load_file($file);
if (!$xml) {
    echo "<p>Chyba při načítání událostí.</p>";
    return;
}

$events = json_decode(json_encode($xml), true);

// Oprava: Pokud existuje pouze jedna událost, převedeme ji na pole
if (isset($events['event']) && !isset($events['event'][0])) {
    $events['event'] = [$events['event']];
} elseif (!isset($events['event'])) {
    $events['event'] = [];
}

// Debug výpis – ověř, zda se události načítají správně
echo "<!-- Počet načtených událostí: " . count($events['event']) . " -->";

// Pokud nejsou žádné události, zobrazíme jen zprávu a necháme stránku pokračovat
if (empty($events['event'])) {
    echo "<p>Žádné události nejsou k dispozici.</p>";
    return;
}

// Počítání událostí
$eventStats = [];
foreach ($events['event'] as $event) {
    if (!isset($event['element']) || !isset($event['page'])) {
        continue; // Pokud některá klíčová data chybí, přeskočíme
    }

    $source = $event['referrer'] ?? 'Přímá návštěva';
    $utm = [];
    if (!empty($event['utm_source'])) $utm[] = htmlspecialchars($event['utm_source']);
    if (!empty($event['utm_medium'])) $utm[] = htmlspecialchars($event['utm_medium']);
    if (!empty($event['utm_campaign'])) $utm[] = htmlspecialchars($event['utm_campaign']);
    $utmText = !empty($utm) ? implode(' / ', $utm) : 'Žádná kampaň';

    $key = "<strong>" . htmlspecialchars($event['element']) . "</strong><br>"
        . "<small>Stránka: " . htmlspecialchars($event['page']) . "</small><br>"
        . "<small>Zdroj: " . htmlspecialchars($source) . "</small><br>"
        . "<small>UTM: " . htmlspecialchars($utmText) . "</small>";

    if (!isset($eventStats[$key])) {
        $eventStats[$key] = 0;
    }
    $eventStats[$key]++;
}

arsort($eventStats);
$topEvents = array_slice($eventStats, 0, 10, true);
?>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Událost</th>
            <th>Počet kliknutí</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($topEvents)): ?>
            <tr><td colspan="2">Žádné události nejsou k dispozici.</td></tr>
        <?php else: ?>
            <?php foreach ($topEvents as $event => $count) { ?>
                <tr>
                    <td><?php echo $event; ?></td>
                    <td><?php echo $count; ?></td>
                </tr>
            <?php } ?>
        <?php endif; ?>
    </tbody>
</table>
