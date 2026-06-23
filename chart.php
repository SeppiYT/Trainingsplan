<?php
// ── Datenbankverbindung ──────────────────────────────────────────────────────
// Gleiche Zugangsdaten wie in seed.php
$host     = "localhost";
$user     = "root";
$password = "";
$database = "meine_db";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Verbindung fehlgeschlagen: " . mysqli_connect_error());
}

// ── Daten aus der Datenbank abfragen ─────────────────────────────────────────
// ORDER BY id → sortiert nach Monat (Jänner zuerst)
$result = mysqli_query($conn, "SELECT monat, umsatz, kosten FROM monatsumsatz ORDER BY id");

// ── Ergebnisse in PHP-Arrays aufteilen ───────────────────────────────────────
$monate  = []; // Array für die Monatsnamen  → wird zu den X-Achsen-Labels
$umsaetze = []; // Array für die Umsatzwerte  → erste Linie im Chart
$kosten   = []; // Array für die Kostenwerte  → zweite Linie im Chart

// Jede Zeile der Datenbank durchgehen und in die Arrays einfügen
while ($row = mysqli_fetch_assoc($result)) {
    $monate[]   = $row["monat"];
    $umsaetze[] = $row["umsatz"];
    $kosten[]   = $row["kosten"];
}

mysqli_close($conn);

// ── Arrays als JSON kodieren ──────────────────────────────────────────────────
// json_encode() wandelt PHP-Arrays in JavaScript-kompatibles JSON um
// Das JSON wird direkt in den JavaScript-Code eingebettet (siehe unten)
$monateJson   = json_encode($monate);
$umsaetzeJson = json_encode($umsaetze);
$kostenJson   = json_encode($kosten);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monatsumsatz Chart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            padding: 40px;
        }
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 80%;
            max-width: 900px;
        }
    </style>
</head>
<body>

<div class="chart-container">
    <canvas id="myChart"></canvas>
</div>

<!-- Chart.js von CDN laden -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // PHP-Werte werden beim Laden der Seite direkt eingebettet
    // Das funktioniert, weil JSON eine Teilmenge von JavaScript-Syntax ist
    const monate   = <?= $monateJson ?>;    // z. B. ["Jänner","Februar",...]
    const umsaetze = <?= $umsaetzeJson ?>;  // z. B. [4200, 7800, ...]
    const kosten   = <?= $kostenJson ?>;    // z. B. [3100, 5200, ...]

    const ctx = document.getElementById('myChart');

    new Chart(ctx, {
        type: 'line', // Line Chart statt Bar Chart
        data: {
            labels: monate, // X-Achse: Monatsnamen
            datasets: [
                {
                    // ── Erste Linie: Umsatz ──────────────────────────────
                    label: 'Umsatz (€)',
                    data: umsaetze,
                    borderColor: 'rgba(54, 162, 235, 1)',          // Blaue Linie
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',    // Halbtransparente Fläche darunter
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)', // Blaue Punkte
                    borderWidth: 2,
                    tension: 0.3, // Leicht geschwungene Linie (0 = gerade, 1 = sehr rund)
                    fill: true    // Fläche unter der Linie einfärben
                },
                {
                    // ── Zweite Linie: Kosten (Aufgabe 4) ────────────────
                    label: 'Kosten (€)',
                    data: kosten,
                    borderColor: 'rgba(255, 99, 132, 1)',           // Rote Linie
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',     // Halbtransparente Fläche darunter
                    pointBackgroundColor: 'rgba(255, 99, 132, 1)',  // Rote Punkte
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            plugins: {
                // ── Titel über dem Chart ──────────────────────────────────
                title: {
                    display: true,                  // Titel einschalten
                    text: 'Monatsumsatz & Kosten',  // Titeltext
                    font: { size: 20 },
                    color: '#333'
                },
                legend: {
                    display: true // Legende (Erklärung der Linien) anzeigen
                }
            },
            scales: {
                y: {
                    beginAtZero: true, // Y-Achse beginnt bei 0
                    ticks: {
                        // Zahlen mit € und Tausender-Punkt formatieren, z. B. "5.000 €"
                        callback: value => value.toLocaleString('de-AT') + ' €'
                    }
                }
            }
        }
    });
</script>

</body>
</html>
