<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once $_SESSION['INI-PATH'];

function build_pivot_view($db, $fg, $field_str, $view_name)
{
    $fid_a = explode(',', $field_str);
    if (count($fid_a)) {
        $query_heplstr  = "MAX(CASE WHEN fid = 10 THEN fcont END) AS F_10,";
        $query_heplstr .= "MAX(CASE WHEN fid = 20 THEN fcont END) AS F_20,";
        $query_heplstr .= "MAX(CASE WHEN fid = 90 THEN fcont END) AS F_90,";
        foreach ($fid_a as $fid => $val) {
            $query_heplstr .= "MAX(CASE WHEN fid = " . $val . " THEN fcont END) AS F_" . $val . ",";
        }
        $query_heplstr = substr($query_heplstr, 0, -1);
        $querstr = "DROP VIEW IF EXISTS forms_" . $fg . "_" . $view_name;
        $stmt = $db->prepare($querstr);
        $stmt->execute();
        $querstr = "CREATE VIEW forms_" . $fg . "_" . $view_name . " AS SELECT fcid,";
        $querstr .= $query_heplstr;
        $querstr .= " FROM forms_" . $fg . " GROUP BY fcid;";
        // echo $querstr;
        $stmt = $db->prepare($querstr);
        $stmt->execute();
    }
    

}
 
build_pivot_view($db, 10010, '109001,109002,109003,109004,109005,109006,109007,109008', 'scores');
build_pivot_view($db, 10010, '110200,110500,110600,110700,111000,111100,110511', 'labor');
build_pivot_view($db, 10010, '102000,103700,110905,104800,115700,115800,116000', 'examination');
build_pivot_view($db, 10010, '102300,102200,102600,102700,102705,102800,102815,108500', 'questionaire');


