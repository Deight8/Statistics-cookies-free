<?php
$acquisitionSources = [];

foreach ($filteredVisitors as $visitor) {
    $source = !empty($visitor['source']) ? $visitor['source'] : 'Přímá návštěva';

    if (!isset($acquisitionSources[$source])) {
        $acquisitionSources[$source] = 0;
    }
    $acquisitionSources[$source]++;
}

arsort($acquisitionSources);
?>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Zdroj návštěvy</th>
            <th>Počet návštěv</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach (array_slice($acquisitionSources, 0, 10) as $source => $count) { ?>
            <tr>
                <td><?php echo htmlspecialchars($source); ?></td>
                <td><?php echo $count; ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
