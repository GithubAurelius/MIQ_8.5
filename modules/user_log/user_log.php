<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_SESSION)) session_start();
require_once $_SESSION['INI-PATH'];

$key_name = "muid";
$main_table = 'user_miq_log';
$col_def_list = "logged_in,logged_out,ip_address,mts";
$col_def_list_base64 = base64_encode($col_def_list);

$field_name_a = [];
$field_name_a['login_name'] = "Benutzername";


$col_def_list = $key_name . "," . $col_def_list;
$col_def_a = explode(',', $col_def_list);


// --- CONFIG ---
$limit = $_GET['limit'] ?? 10; // rows per page

$user_a = [];
$query = "SELECT master_uid, login_name FROM user_miq";
$stmt = $db->prepare($query);
$stmt->execute();
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($res as $key => $val) $user_a[$val['master_uid']] = $val['login_name'] ?? 'NONE';


require_once "../../php/table_listing.php";

require_once "user_log_add_end.php";
