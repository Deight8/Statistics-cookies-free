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

$pageVisits = [];

// ✅ Používáme správně `referer`, pokud není, nastavíme "Přímá návštěva"
if (!empty($filteredVisitors)) {
    foreach ($filteredVisitors as $visitor) {
        $page = !empty($visitor['referer']) ? htmlspecialchars($visitor['referer']) : 'Přímá návštěva';

        if (!isset($pageVisits[$page])) {
            $pageVisits[$page] = 0;
        }
        $pageVisits[$page]++;
    }

    arsort($pageVisits);
}
?>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Stránka</th>
            <th>Počet návštěv</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($pageVisits)) { ?>
            <?php foreach (array_slice($pageVisits, 0, 10) as $page => $count) { ?>
                <tr>
                    <td><?php echo $page; ?></td>
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
