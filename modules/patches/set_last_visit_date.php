<?php
if (!isset($_SESSION)) session_start();
$_SESSION['last_activity'] = time();
require_once $_SESSION['INI-PATH'];
require_once(MIQ_ROOT_PHP . 'session_check.php');

function get_visits($db, $pid, $this_fcid){ // identify first visit for one-time-fields
    $query = "SELECT fcid FROM forms_10005_list WHERE F_90=:pid ORDER BY fcid;";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':pid', $pid);
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_COLUMN); 
    return $res;
}

function is_last_visit($visit_a, $this_fcid){ 
    if (!count($visit_a))  return 1;
    else if ($visit_a[count($visit_a)-1] == $this_fcid) return 1;
    else return 0;
}

function q_execute($db, $query_str, $mode = PDO::FETCH_ASSOC){
    # echo "<br>".$query_str;
    $temp_a = [];
    $stmt = $db->prepare($query_str);
    $start = microtime(true);
    $stmt->execute();
    $end = microtime(true);
    $temp_a = $stmt->fetchAll($mode);
    $unique_fcid_a = [];
    $count = 0;
    if ($mode == PDO::FETCH_ASSOC){
        foreach ($temp_a as $key => $val_a) {
            $unique_fcid_a[$val_a['fcid']] = 1;
            $count = count($unique_fcid_a);
        }
    } else $count = count($temp_a);
    # echo "<br><b>". $count." Datensätze im Ergbnis</b>";
    # echo "<br>Query dauer: " .  number_format($end - $start, 6, '.', '') . " Sekunden\n";
    return [$temp_a, $count, array_keys($unique_fcid_a)];
}

$patient_a = q_execute($db, "Select DISTINCT fcid FROM forms_10003", $mode = PDO::FETCH_COLUMN);
foreach ($patient_a[0] as $fcid) { 
    $visite_a = [];
    $visite_date = "";
    $visite_a = q_execute($db, "Select fcid FROM forms_10005 WHERE fid=90 AND fcont=".$fcid, $mode = PDO::FETCH_COLUMN); 
    // echo "<br>$fcid<pre>"; echo print_r($visite_a[0]); echo "</pre>";
    $max_fcid = max($visite_a[0]);
    $visite_date = q_execute($db, "Select fcont FROM forms_10005 WHERE fcid=". $max_fcid." AND fid=10005020", $mode = PDO::FETCH_COLUMN)[0][0]; 
    echo "<br>".$fcid." ".$visite_date;
    ins_or_rep_form("", $db, 10003, $fcid, -99, 1, 10005020, $visite_date);
}

