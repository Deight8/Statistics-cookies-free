<?php
$graphData = [];
foreach ($filteredVisitors as $visitor) {
    $hour = date('H', strtotime($visitor['timestamp']));
    if (!isset($graphData[$hour])) {
        $graphData[$hour] = 0;
    }
    $graphData[$hour]++;
}
?>
<canvas id="visitorChart"></canvas>
<script>
    let ctx = document.getElementById('visitorChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_keys($graphData)); ?>,
            datasets: [{
                label: 'Počet návštěv za hodinu',
                data: <?php echo json_encode(array_values($graphData)); ?>,
                borderColor: 'blue',
                fill: false
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { title: { display: true, text: 'Hodina' } },
                y: { title: { display: true, text: 'Počet návštěv' } }
            }
        }
    });
</script>
