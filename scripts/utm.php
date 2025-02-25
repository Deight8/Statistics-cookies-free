<?php
$utmStats = [];

foreach ($filteredVisitors as $visitor) {
    // Zkontrolujeme, zda UTM parametry existují
    $utm_source = $visitor['utm_source'] ?? 'unknown';
    $utm_medium = $visitor['utm_medium'] ?? 'unknown';
    $utm_campaign = $visitor['utm_campaign'] ?? 'unknown';

    // Spojení UTM parametrů do jednoho klíče
    $utmKey = $utm_source . ' / ' . $utm_medium . ' / ' . $utm_campaign;

    if (!isset($utmStats[$utmKey])) {
        $utmStats[$utmKey] = 0;
    }
    $utmStats[$utmKey]++;
}

arsort($utmStats);
?>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>UTM Zdroj / Médium / Kampaň</th>
            <th>Počet návštěv</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach (array_slice($utmStats, 0, 10) as $utm => $count) { ?>
            <tr>
                <td><?php echo htmlspecialchars($utm); ?></td>
                <td><?php echo $count; ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
