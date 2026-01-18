<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_SESSION)) session_start();
require_once $_SESSION['INI-PATH'];

// JSON input auslesen
$input = json_decode(file_get_contents('php://input'), true);
header('Content-Type: application/json');

if (!isset($input['key_name'], $input['key_val'], $input['column'], $input['value'], $input['main_table'], $input['col_def_list_base64'])) {
    echo json_encode(['success' => false, 'msg' => 'Ungültige Daten']);
    exit;
}

$key_name = $input['key_name'];
$key_val = intval($input['key_val']);
$column = $input['column'];
$value = $input['value'];
$main_table = $input['main_table'];
$col_def_list = base64_decode($input['col_def_list_base64']);
$allowedCols = $col_def_a = explode(',', $col_def_list);
// erlaubte Spalten zum Update
// $allowedCols = ["muid","usergroup","layerId","layType","layCoor","layName","layText","filePath","fileName","fileNameC","fileType","layColor","layOpacity","mts"];

if (!in_array($column, $allowedCols, true)) {
    echo json_encode(['success' => false, 'msg' => 'Spalte nicht erlaubt']);
    exit;
}

try {
    $sql = "UPDATE ".$main_table." SET `$column` = :value WHERE ".$key_name." = :key_val";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':value', $value);
    $stmt->bindValue(':key_val', $key_val, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
}