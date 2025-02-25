<?php
// Zajistíme, že výstup bude HTML, ne JSON
header('Content-Type: text/html; charset=UTF-8');

require_once __DIR__ . '/config.php';

// Debugging: Zobrazíme Content-Type
header_remove("Content-Type"); // Pro jistotu odstraníme předchozí hlavičky
header('Content-Type: text/html; charset=UTF-8');

$range = $_GET['range'] ?? 'day';
$data_url = "scripts/get_date_range.php?range=$range";

echo "<!-- Debug: data_url = $data_url -->"; // Ověříme, že se hodnota nastavuje správně
?>


<?php
require_once __DIR__ . '/config.php';

$range = $_GET['range'] ?? 'day';
$data_url = "scripts/get_date_range.php?range=$range";
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiky návštěvnosti</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="container mt-4">

    <h1 class="mb-4">Statistiky návštěvnosti</h1>

    <!-- Výběr rozsahu dat -->
    <div class="mb-3">
        <label for="rangeSelect" class="form-label">Zvolte období:</label>
        <select id="rangeSelect" class="form-select">
            <option value="day" <?= ($range == 'day') ? 'selected' : '' ?>>Dnes</option>
            <option value="week" <?= ($range == 'week') ? 'selected' : '' ?>>Týden</option>
            <option value="month" <?= ($range == 'month') ? 'selected' : '' ?>>Měsíc</option>
        </select>
    </div>

    <!-- Graf návštěvnosti -->
    <div class="card mb-4">
        <div class="card-header">Graf návštěvnosti</div>
        <div class="card-body">
            <canvas id="visitsChart"></canvas>
        </div>
    </div>

    <!-- Tabulka návštěvníků -->
    <div class="card mb-4">
        <div class="card-header">Seznam návštěvníků</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>IP Hash</th>
                        <th>Referer</th>
                        <th>User-Agent</th>
                    </tr>
                </thead>
                <tbody id="visitorsTable">
                    <tr><td colspan="4">Načítání dat...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tabulka událostí -->
    <div class="card">
        <div class="card-header">Události na webu</div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Datum</th>
                        <th>Element</th>
                        <th>Stránka</th>
                        <th>Referer</th>
                    </tr>
                </thead>
                <tbody id="eventsTable">
                    <tr><td colspan="4">Načítání dat...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const rangeSelect = document.getElementById("rangeSelect");
            rangeSelect.addEventListener("change", function () {
                window.location.href = "?range=" + this.value;
            });

            // Načtení dat
            fetch("<?= $data_url ?>")
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert("Chyba: " + data.error);
                        return;
                    }

                    // Zpracování návštěv pro graf
                    const visitsByDate = {};
                    data.visitors.forEach(visit => {
                        const date = visit.timestamp.split(" ")[0]; // Pouze datum
                        visitsByDate[date] = (visitsByDate[date] || 0) + 1;
                    });

                    // Vykreslení grafu
                    const ctx = document.getElementById('visitsChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: Object.keys(visitsByDate),
                            datasets: [{
                                label: 'Počet návštěv',
                                data: Object.values(visitsByDate),
                                borderColor: 'blue',
                                fill: false
                            }]
                        }
                    });

                    // Vyplnění tabulky návštěvníků
                    const visitorsTable = document.getElementById("visitorsTable");
                    visitorsTable.innerHTML = data.visitors.length ? "" : "<tr><td colspan='4'>Žádná data</td></tr>";
                    data.visitors.forEach(visit => {
                        const row = `<tr>
                            <td>${visit.timestamp}</td>
                            <td>${visit.visitor_id}</td>
                            <td>${visit.referer || '-'}</td>
                            <td>${visit.user_agent}</td>
                        </tr>`;
                        visitorsTable.innerHTML += row;
                    });

                    // Vyplnění tabulky událostí
                    const eventsTable = document.getElementById("eventsTable");
                    eventsTable.innerHTML = data.events.length ? "" : "<tr><td colspan='4'>Žádná data</td></tr>";
                    data.events.forEach(event => {
                        const row = `<tr>
                            <td>${event.timestamp}</td>
                            <td>${event.element}</td>
                            <td>${event.page}</td>
                            <td>${event.referrer || '-'}</td>
                        </tr>`;
                        eventsTable.innerHTML += row;
                    });

                })
                .catch(error => console.error("Chyba při načítání dat:", error));
        });
    </script>

</body>
</html>
