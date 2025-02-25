<?php
$pageVisits = [];

foreach ($filteredVisitors as $visitor) {
    // Zkontrolujeme, zda existuje referer a není prázdný
    $page = !empty($visitor['referer']) ? $visitor['referer'] : 'Neznámá stránka';

    if (!isset($pageVisits[$page])) {
        $pageVisits[$page] = 0;
    }
    $pageVisits[$page]++;
}

arsort($pageVisits);
?>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Stránka</th>
            <th>Počet návštěv</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach (array_slice($pageVisits, 0, 10) as $page => $count) { ?>
            <tr>
                <td><?php echo htmlspecialchars($page); ?></td>
                <td><?php echo $count; ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
