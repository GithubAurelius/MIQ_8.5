<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
header('Content-Type: application/json');
session_start();

function upsert_geo_layer(PDO $pdo, array $data)
{
    // Liste der Spalten, die geändert werden dürfen
    $validFields = [
        'filePath',
        'fileNameC',
        'fileName',
        'fileType',
        'layType',
        'layerId',
        'layCoor',
        'layOpacity',
        'layName',
        'layText',
        'layColor'
    ];

    if (!isset($data['fcid'])) {
        throw new Exception("FCID fehlt – Update/Insert nicht möglich");
    }

    $fcid = $data['fcid'];

    // Felder filtern → nur gültige Spalten verwenden
    $updateFields = [];
    $params = [];

    foreach ($validFields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "`$field` = :$field";
            $params[$field] = $data[$field];
        }
    }

    if (empty($updateFields)) {
        throw new Exception("Keine gültigen Felder zum Aktualisieren/Einfügen gefunden.");
    }

    // --- UPDATE versuchen ---
    $sqlUpdate = "UPDATE geo_layer SET " . implode(", ", $updateFields) . " WHERE fcid = :fcid";
    $params['fcid'] = $fcid;

    $stmt = $pdo->prepare($sqlUpdate);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        return "updated"; // Datensatz aktualisiert
    }

    // --- kein Update → INSERT ---
    $insertFields = ['fcid'];
    $insertPlaceholders = [':fcid'];
    $insertParams = ['fcid' => $fcid];

    foreach ($validFields as $field) {
        if (isset($data[$field])) {
            $insertFields[] = "`$field`";
            $insertPlaceholders[] = ":$field";
            $insertParams[$field] = $data[$field];
        }
    }

    $sqlInsert = "INSERT INTO geo_layer (" . implode(", ", $insertFields) . ") VALUES (" . implode(", ", $insertPlaceholders) . ")";
    $stmt = $pdo->prepare($sqlInsert);
    $stmt->execute($insertParams);

    $sqlLayer = "INSERT INTO forms_10120 (fcid, fid, fcont, muid, usergroup) VALUES (".$fcid.", 10120001, '".$data['layName']."', ".$_SESSION['m_uid'].", ".$_SESSION['user_group'].") ON DUPLICATE KEY UPDATE fcont = VALUES(fcont);";
    $stmt = $pdo->prepare($sqlLayer);
    $stmt->execute(); // TODO: PREPARE verbessern

    return "inserted"; // Datensatz eingefügt
}

if (!isset($_SESSION['m_uid'])) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'SESSION gone'
    ]);
    exit;
}

require_once $_SESSION['INI-PATH'];

// test db update 
// $json_data = '{"fcid":2222,"filePath":"2023_11/","fileNameC":"IlpJXzQ2NV9GRkNfNV9HUl9IS19FNF8wMDAxX0dfcC5wbmcix1x1x1xh_9w5LE2egLhfJ5Gyc5uwQ==","fileName":"ZI_465_FFC_5_GR_HK_E4_0001_G_p.png","fileType":"image/png","layerId":0,"layCoor":"[[48.02484,5.82275],[47.97891,10.15849],[46.00841,5.77494]]","layOpacity":0.3,"layName":"","layText":"","layColor":""}';
// $json_data = '{"fcid":2025111119583694,"delete":1}';
// $json_data = '{"fcid":2025111202062699,"layType":"objectlayer","layerId":2025111202062699,"layName":"eee"}';
// echo $json_data;
// $geo_jsn_data_a = json_decode($json_data, true);
// echo "<pre>"; echo print_r($geo_jsn_data_a); echo "</pre>";
// echo $updatedRows = update_geo_layer($db, $geo_jsn_data_a);
// exit;


$json_data = file_get_contents('php://input');
// file_put_contents("error.txt", $json_data); // in MIQ_PATH_PHP
$geo_jsn_data_a = json_decode($json_data, true);



if ($geo_jsn_data_a === null) {
    header('Content-Type: application/json', true, 400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Ungültige JSON-Daten empfangen.']);
    exit;
}


$delete_geo_data = $geo_jsn_data_a['delete'] ?? 0;
if ($delete_geo_data) {
    $sqlUpdate = "DELETE FROM geo_layer WHERE fcid = :fcid";
    $params['fcid'] = $geo_jsn_data_a['fcid'];
    $stmt = $db->prepare($sqlUpdate);
    $stmt->execute($params);
    $fcid = "(CID:".$geo_jsn_data_a['fcid'].") Entfernung" ?? "KEINE";
} else {
    $updatedRows = upsert_geo_layer($db, $geo_jsn_data_a);
    $fcid = "(CID:".$geo_jsn_data_a['fcid'].") Update" ?? "KEINE";
}

// Erfolgsantwort senden
header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'message' => $fcid.' Geodaten.']); //  . $json_data


