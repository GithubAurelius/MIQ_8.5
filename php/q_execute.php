<?php
header('Content-Type: application/json');
session_start();
require_once $_SESSION['INI-PATH'];
require_once $_SESSION['FS_ROOT'].'forms/delete_rule.php';

// $query_str   = $_REQUEST['query_str'] ?? "";
$query_str = base64_decode($_REQUEST['query_str']);


$crypt_str = $_REQUEST['crypt_str'] ?? "";
if ($crypt_str){
    $crypt_a = json_decode(base64_decode($crypt_str), true);
    $crypt_a['table_name'] = simple_decrypt($crypt_a['table_name']);
    $crypt_a['table_key'] = simple_decrypt($crypt_a['table_key']);
    $query_str = str_replace('crypt_table',$crypt_a['table_name'],str_replace('crypt_key',$crypt_a['table_key'],$query_str));
}
// echo "<pre>"; echo print_r($crypt_a); echo "</pre>"; echo $query_str;

if ($query_str)
    try {
        del_chain( $db, $query_str);
        echo '{"OK":"data deleted"}';
    } catch (Exception $e) {
        echo '{"FEHLER":"im Hintergrundprozess"}';
    }

// TODO Vernünftige Übergabe
