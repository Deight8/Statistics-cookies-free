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

// ✅ Ověříme, že `visitors` existují a obsahují `source`
$filteredVisitors = isset($data['visitors']) && is_array($data['visitors']) ? $data['visitors'] : [];

$acquisitionSources = [];

// ✅ Používáme správně `source`
if (!empty($filteredVisitors)) {
    foreach ($filteredVisitors as $visitor) {
        $source = !empty($visitor['source']) ? htmlspecialchars($visitor['source']) : 'Přímá návštěva';
        if (!isset($acquisitionSources[$source])) {
            $acquisitionSources[$source] = 0;
        }
        $acquisitionSources[$source]++;
    }

    arsort($acquisitionSources);
}
?>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Zdroj návštěvy</th>
            <th>Počet návštěv</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($acquisitionSources)) { ?>
            <?php foreach (array_slice($acquisitionSources, 0, 10) as $source => $count) { ?>
                <tr>
                    <td><?php echo $source; ?></td>
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
