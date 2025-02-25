<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$filter = $_GET['filter'] ?? 'day';

// Funkce pro získání správného rozsahu dat
function getDateRange($filter) {
    $today = date('Y-m-d');
    if ($filter === 'week') {
        $start = date('Y-m-d', strtotime('monday this week'));
        $end = date('Y-m-d', strtotime('sunday this week'));
        return "$start – $end";
    } elseif ($filter === 'month') {
        $start = date('Y-m-01');
        $end = date('Y-m-t');
        return "$start – $end";
    }
    return $today;
}

$dateRange = getDateRange($filter);
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiky návštěv</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        #sidebar {
            width: 250px;
            background: #f8f9fa;
            padding: 20px;
            border-right: 1px solid #ddd;
        }
        #content {
            flex-grow: 1;
            padding: 20px;
        }
        .menu-item {
            display: block;
            padding: 10px;
            margin: 5px 0;
            background: #007bff;
            color: white;
            text-align: center;
            border-radius: 5px;
            cursor: pointer;
        }
        .menu-item:hover {
            background: #0056b3;
        }
        .menu-item.active {
            background: #0056b3;
        }
    </style>
</head>
<body>
    
    <!-- Navigační menu -->
    <div id="sidebar">
        <h4>Statistiky</h4>
        <div class="menu-item" onclick="loadStatistics('visited_pages')">Navštívené stránky</div>
        <div class="menu-item" onclick="loadStatistics('acquisition')">Akvizice</div>
        <div class="menu-item" onclick="loadStatistics('utm')">UTM Parametry</div>
        <div class="menu-item" onclick="loadStatistics('events')">Události</div>
    </div>

    <!-- Hlavní obsah -->
    <div id="content">
        <h2 id="pageTitle" class="mb-1">Statistiky</h2>
        <p id="dateRange" class="text-muted"><?php echo ucfirst($filter); ?> (<?php echo $dateRange; ?>)</p>

        <!-- Tlačítka pro změnu filtru -->
        <div class="mb-3">
            <a href="javascript:void(0);" class="btn btn-primary" onclick="changeFilter('day')">Dnes</a>
            <a href="javascript:void(0);" class="btn btn-secondary" onclick="changeFilter('week')">Tento týden</a>
            <a href="javascript:void(0);" class="btn btn-success" onclick="changeFilter('month')">Tento měsíc</a>
        </div>

        <!-- Graf -->
        <canvas id="visitorChart" class="mb-4" style="height: 300px; max-height: 300px;"></canvas>

        <!-- Dynamicky načítaná tabulka -->
        <div id="statisticsContent">
            <p>Vyberte statistiku z levého menu.</p>
        </div>
    </div>

    <script>
        let chart;
        let currentFilter = "<?php echo $filter; ?>";
        let activeStat = "visited_pages";

        function loadStatistics(statType) {
            activeStat = statType;
            document.getElementById("pageTitle").innerText = getStatTitle(statType);
            updateDateRange();

            document.querySelectorAll(".menu-item").forEach(el => el.classList.remove("active"));
            document.querySelector(`[onclick="loadStatistics('${statType}')"]`).classList.add("active");

            let dataUrl = `scripts/${statType}_data.php?filter=${currentFilter}`;
            console.log("📡 Načítání dat z:", dataUrl);

            fetch(dataUrl)
                .then(response => response.json())
                .then(data => {
                    console.log("📊 Odpověď serveru:", data);
                    if (!data.labels || !data.values || data.labels.length === 0) {
                        console.error("❌ Chybí potřebná data:", data);
                        return;
                    }
                    
                    let ctx = document.getElementById('visitorChart');
                    if (!ctx) {
                        console.error("❌ Chyba: `<canvas>` pro graf nebyl nalezen.");
                        return;
                    }
                    
                    let ctx2D = ctx.getContext('2d');
                    if (chart) { chart.destroy(); }
                    
                    chart = new Chart(ctx2D, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: data.label,
                                data: data.values,
                                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: { title: { display: true, text: data.xTitle } },
                                y: { title: { display: true, text: data.yTitle } }
                            }
                        }
                    });
                    console.log("✅ Graf úspěšně načten.");
                })
                .catch(error => console.error("❌ Chyba při načítání grafu:", error));

            fetch(`scripts/${statType}.php?filter=${currentFilter}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById("statisticsContent").innerHTML = html;
                })
                .catch(error => console.error("❌ Chyba při načítání statistik:", error));
        }

        function changeFilter(filter) {
            currentFilter = filter;
            updateDateRange();
            loadStatistics(activeStat);
        }

        function updateDateRange() {
            fetch(`scripts/get_date_range.php?filter=${currentFilter}`)
                .then(response => response.text())
                .then(dateRange => {
                    document.getElementById("dateRange").innerText = `${currentFilter.charAt(0).toUpperCase() + currentFilter.slice(1)} (${dateRange})`;
                })
                .catch(error => console.error("❌ Chyba při načítání rozsahu dat:", error));
        }

        function getStatTitle(stat) {
            switch (stat) {
                case 'visited_pages': return "Navštívené stránky";
                case 'acquisition': return "Akvizice";
                case 'utm': return "UTM Parametry";
                case 'events': return "Události";
                default: return "Statistiky";
            }
        }
    </script>

</body>
</html>
