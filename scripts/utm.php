<?php
require_once __DIR__ . '/../config.php';

// Načtení dat z API pomocí cURL
$filter = $_GET['filter'] ?? 'day';
$data_url = "https://domybustehrad.cz/deight-statistics/scripts/get_date_range.php?range=$filter";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $data_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

// ✅ Ověříme, že `visitors` existují
$filteredVisitors = isset($data['visitors']) && is_array($data['visitors']) ? $data['visitors'] : [];

$utmStats = [];

// ✅ Používáme správně UTM parametry a zajišťujeme, že nikdy nejsou prázdné
if (!empty($filteredVisitors)) {
    foreach ($filteredVisitors as $visitor) {
        $utm_source = !empty($visitor['utm_source']) ? htmlspecialchars($visitor['utm_source']) : 'Nezadáno';
        $utm_medium = !empty($visitor['utm_medium']) ? htmlspecialchars($visitor['utm_medium']) : 'Nezadáno';
        $utm_campaign = !empty($visitor['utm_campaign']) ? htmlspecialchars($visitor['utm_campaign']) : 'Nezadáno';

        // Spojení UTM parametrů do jednoho klíče
        $utmKey = "$utm_source / $utm_medium / $utm_campaign";

        if (!isset($utmStats[$utmKey])) {
            $utmStats[$utmKey] = 0;
        }
        $utmStats[$utmKey]++;
    }

    arsort($utmStats);
}
?>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>UTM Zdroj / Médium / Kampaň</th>
            <th>Počet návštěv</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($utmStats)) { ?>
            <?php foreach (array_slice($utmStats, 0, 10) as $utm => $count) { ?>
                <tr>
                    <td><?php echo $utm; ?></td>
                    <td><?php echo $count; ?></td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="2">Žádná data k dispozici</td>
            </tr>
        <?php } ?>
    </tbody>
</table>
