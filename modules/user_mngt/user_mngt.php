<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_SESSION)) session_start();
require_once $_SESSION['INI-PATH'];

$key_name = "master_uid";
$main_table = 'user_miq';
$col_def_list = "muid,login_name,login_pass,rights,email,usergroup,consent"; // ,last_pwchange,mts
$col_def_list_base64 = base64_encode($col_def_list);

$field_name_a = [];
$field_name_a['login_name'] = "Benutzername";


$col_def_list = $key_name . "," . $col_def_list;
$col_def_a = explode(',', $col_def_list);


// --- CONFIG ---
$limit = $_GET['limit'] ?? 10; // rows per page

require_once "../../php/table_listing.php";

require_once "user_mngt_add_end.php";