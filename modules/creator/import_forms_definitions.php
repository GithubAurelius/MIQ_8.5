<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once $_SESSION['INI-PATH'];

function importCSVToTable(PDO $db, string $tableName, string $csvFile): array|bool {
    if (!file_exists($csvFile)) return false;

    $handle = fopen($csvFile, 'r');
    if (!$handle) return false;

    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") rewind($handle);

    $header = fgetcsv($handle, 0, ';');
    if (!$header || !in_array('fid', $header) || !in_array('fg', $header)) {
        fclose($handle);
        return false;
    }

    $rows = [];
    $fidFgMap = [];
    $duplicates = [];

    while (($row = fgetcsv($handle, 0, ';')) !== false) {
        // echo "<pre>"; echo print_r($row); echo "</pre>";
        if ($row[1]){
            $assoc = array_combine($header, $row);
            $key = $assoc['fid'] . '||' . $assoc['fg'];

            if (isset($fidFgMap[$key])) {
                echo "<br>".$fidFgMap[$key];
                $duplicates[] = $key;
            } else {
                $fidFgMap[$key] = true;
                $rows[] = $assoc;
            }
        }
    }

    fclose($handle);

    if (!empty($duplicates)) {
        return array_unique($duplicates); // Duplikate in der CSV
    }

    $checkStmt = $db->prepare("SELECT COUNT(*) FROM \"$tableName\" WHERE fid = :fid AND fg = :fg");
    $updateStmt = $db->prepare("UPDATE \"$tableName\" SET shortname = :shortname, in_view = :in_view WHERE fid = :fid AND fg = :fg");

    foreach ($rows as $row) {
        $checkStmt->execute([
            ':fid' => $row['fid'],
            ':fg'  => $row['fg']
        ]);

        if ($checkStmt->fetchColumn() > 0) {
            $updateStmt->execute([
                ':shortname' => $row['shortname'],
                ':in_view'   => $row['in_view'],
                ':fid'       => $row['fid'],
                ':fg'        => $row['fg']
            ]);
        }
        // Kein Insert, falls Kombination nicht existiert
    }

    return true;
}

$result = importCSVToTable($db, 'forms_definition', $_SESSION["FS_ROOT"] . $_SESSION["PROJECT_PATH"].'forms_def/forms_definition_import.csv');

if ($result === true) {
    echo "Update erfolgreich.";
} elseif (is_array($result)) {
    echo "<pre>"; echo print_r($result); echo "</pre>";
    echo "Abbruch wegen doppelter Kombinationen aus 'fid' und 'fg':\n";
    foreach ($result as $dup) {
        echo "- $dup\n";
    }
} else {
    echo "Import fehlgeschlagen.";
}