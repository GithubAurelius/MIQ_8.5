<?php
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



$layerId = $_GET['layerid'] ?? '';
if (!$layerId){
    echo json_encode(['status' => 'error', 'message' => 'Keine Layer-ID übergeben.']);
    exit;
}

$query_fields_str ="fcid,layType,layName,layCoor,layColor";
$geo_a = get_query_data($db, 'geo_layer', $query_add = "layType!='plan_profile' AND layType!='object_profile' AND layType!='objectlayer' AND layType!='image_rot' AND layerId=".$layerId);
$query_fields_str ="";


if (!$geo_a) {
    echo json_encode(['status' => 'error', 'message' => 'Keine JSON-Daten empfangen.']);
    exit;
}

// Erfolgsantwort senden
header('Content-Type: application/json');
echo json_encode($geo_a);


