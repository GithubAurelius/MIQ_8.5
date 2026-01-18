<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_SESSION)) session_start();
require_once $_SESSION['INI-PATH'];

$num = $_GET['num'] ?? 1000;

$key_name = "fcid";
$main_table = 'geo_layer';
$col_def_list ="muid,usergroup,layerId,layType,layCoor,layName,layText,filePath,fileName,fileNameC,fileType,layColor,layOpacity,mts";
$col_def_list_base64 = base64_encode($col_def_list);


$field_name_a = [];
$field_name_a['login_name'] = "Benutzername";


$col_def_list = $key_name . "," . $col_def_list;
$col_def_a = explode(',', $col_def_list);


// --- CONFIG ---
$limit = $_GET['limit'] ?? 10; // rows per page

require_once "../../php/table_listing.php";

require_once "geolayer_list_add_end.php";