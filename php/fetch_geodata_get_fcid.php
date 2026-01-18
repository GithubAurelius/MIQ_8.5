<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['m_uid'])) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'SESSION gone'
    ]);
    exit;
}

require_once $_SESSION['INI-PATH'];



$fcid = $_GET['fcid'] ?? '';
if (!$fcid){
    echo json_encode(['status' => 'error', 'message' => 'Keine Layer-ID übergeben.']);
    exit;
}

$query_fields_str ="fcid,layType,layName,layCoor,layColor";
$geo_a = get_query_data($db, 'geo_layer', $query_add = "fcid=".$fcid);
$query_fields_str ="";


if (!$geo_a) {
    echo json_encode(['status' => 'error', 'message' => 'Keine JSON-Daten empfangen.']);
    exit;
}

// Erfolgsantwort senden
header('Content-Type: application/json');
echo json_encode($geo_a);


