<?php
session_start();
require_once $_SESSION['INI-PATH'];
require_once MIQ_ROOT_PHP."listings_common.php";


define("DB_PATH_SOURCE", MIQ_DATA.$_SESSION["PROJECT"]."/db/old_plob.sqlite3");

// Quelldatenbank öffnen

$sourceDb = new PDO('sqlite:'.DB_PATH_SOURCE);
$sourceDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Zieldatenbank öffnen
$targetDb = new PDO('sqlite:'.DB_PATH);
$targetDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Tabelle und Daten auslesen
$sourceTable = 'forms_3'; // Name der Tabelle in der Quell-DB
$targetTable = 'forms_3'; // Name der Tabelle in der Ziel-DB

// Daten aus der Quelltabelle holen
$data = $sourceDb->query("SELECT * FROM $sourceTable")->fetchAll(PDO::FETCH_ASSOC);

// Prüfen ob Daten vorhanden sind
if ($data) {
    // Erste Zeile nehmen um Spaltennamen zu bekommen
    $columns = array_keys($data[0]);
    $columnsList = implode(', ', $columns);
    $placeholders = ':' . implode(', :', $columns);

    // Prepared Statement für Insert in Ziel-DB vorbereiten
    $stmt = $targetDb->prepare("INSERT INTO $targetTable ($columnsList) VALUES ($placeholders)");

    // Alle Datensätze übertragen
    foreach ($data as $row) {
        $stmt->execute($row);
    }

    echo "Daten erfolgreich kopiert!";
} else {
    echo "Keine Daten gefunden.";
}
?>
