<?php
// ── Datenbankverbindung ──────────────────────────────────────────────────────
// Hier deine eigenen Zugangsdaten eintragen
$host     = "localhost";
$user     = "root";       // z. B. "root" bei XAMPP
$password = "";           // z. B. "" bei XAMPP
$database = "meine_db";   // Name deiner Datenbank

$conn = mysqli_connect($host, $user, $password, $database);

// Verbindung prüfen – wenn fehlgeschlagen, Fehlermeldung ausgeben und stoppen
if (!$conn) {
    die("Verbindung fehlgeschlagen: " . mysqli_connect_error());
}

// ── Tabelle erstellen (falls sie noch nicht existiert) ───────────────────────
// CREATE TABLE IF NOT EXISTS erstellt die Tabelle nur beim ersten Aufruf
$createTable = "
    CREATE TABLE IF NOT EXISTS monatsumsatz (
        id     INT PRIMARY KEY,   -- 0 = Jänner, 1 = Februar, ... 11 = Dezember
        monat  VARCHAR(20),       -- Name des Monats als Text
        umsatz INT,               -- Umsatz als Ganzzahl
        kosten INT                -- Kosten als Ganzzahl (Aufgabe 4)
    )
";
mysqli_query($conn, $createTable);

// ── Tabelle leeren ───────────────────────────────────────────────────────────
// TRUNCATE löscht alle Zeilen – so kann seed.php mehrfach aufgerufen werden
mysqli_query($conn, "TRUNCATE TABLE monatsumsatz");

// ── Monatsnamen ──────────────────────────────────────────────────────────────
$monate = [
    0  => "Jänner",
    1  => "Februar",
    2  => "März",
    3  => "April",
    4  => "Mai",
    5  => "Juni",
    6  => "Juli",
    7  => "August",
    8  => "September",
    9  => "Oktober",
    10 => "November",
    11 => "Dezember"
];

// ── Für jeden Monat einen Zufallswert einfügen ───────────────────────────────
foreach ($monate as $id => $name) {
    $umsatz = rand(1000, 10000); // Zufallszahl zwischen 1.000 und 10.000
    $kosten = rand(1000, 10000); // Zufallszahl für Kosten (Aufgabe 4)

    // Prepared Statement – schützt vor SQL-Injection
    $stmt = mysqli_prepare($conn, "INSERT INTO monatsumsatz (id, monat, umsatz, kosten) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isii", $id, $name, $umsatz, $kosten);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// ── Fertig ───────────────────────────────────────────────────────────────────
echo "✅ Daten erfolgreich eingefügt! Alle 12 Monate wurden mit Zufallswerten befüllt.";

mysqli_close($conn);
?>
