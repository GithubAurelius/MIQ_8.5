<?php
session_start();
require_once $_SESSION['INI-PATH'];

// error_reporting(E_ALL);
// ini_set('display_errors', 0);

header('Content-Type: application/json');

$ts = date("Y-m-d H:i:s");
$usergroup = $_SESSION['user_group'];

// Parameter aus dem Request (POST empfohlen)
$table = $_POST["table"] ?? '';
$fcont = $_POST["newValue"] ?? '';
$fcid  = $_POST["fcid"] ?? '';
$fid   = $_POST["fid"] ?? ''; // Das ist deine "fid" (Spalten-ID)

// Validierung: Nur bestimmte Tabellen erlauben (Sicherheit!)
// Prüfen, ob der Tabellenname mit 'forms_' beginnt
if (!str_starts_with($table, 'forms_')) {
    echo json_encode(["status" => "error", "message" => "Ungültige Tabellenstruktur"]);
    exit;
}

try {
    // fcont ist die Spalte für den Inhalt, fcid und fid identifizieren die Zeile
    $sql = "UPDATE `$table` 
            SET fcont = :fcont, usergroup = :usergroup, mts = :mts 
            WHERE fcid = :fcid AND fid = :fid";
            
    $stmt = $db->prepare($sql);
    $stmt->bindValue(":fcont", $fcont);
    $stmt->bindValue(":usergroup", $usergroup);
    $stmt->bindValue(":mts", $ts);
    $stmt->bindValue(":fcid", $fcid);
    $stmt->bindValue(":fid", $fid);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "newValue" => $fcont]);
    } else {
        echo json_encode(["status" => "error", "message" => "Update fehlgeschlagen"]);
    }
} catch(Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}