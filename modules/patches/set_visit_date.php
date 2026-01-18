<?php
if (!isset($_SESSION)) session_start();
$_SESSION['last_activity'] = time();
require_once $_SESSION['INI-PATH'];
require_once(MIQ_ROOT_PHP . 'session_check.php');

function ins_or_rep_form($ts, $db, $fg, $fcid, $muid, $usergroup, $fid, $fcont)
{
    if (!$ts) $ts = date("Y-m-d H:i:s");
    if (!$muid) $muid = $_SESSION['uid'];
    if (DB == 'SQLite') {
        try {
            // echo "<br>".$fcid." ".$fid." ".$fcont." ".$fg." ".$muid." ".$ts;
            if (trim($fcont) || $fcont == 0) {
                $stmt = $db->prepare("INSERT OR IGNORE INTO forms_$fg (fcid,muid,fid,fcont,usergroup,mts) VALUES (:fcid,:muid,:fid,:fcont,:usergroup,:mts)");
                $stmt->bindValue(':fcid', $fcid);
                $stmt->bindValue(':muid', $muid);
                $stmt->bindValue(':fid', $fid);
                $stmt->bindValue(':fcont', $fcont);
                $stmt->bindValue(':mts', $ts);
                $stmt->bindValue(':usergroup', $usergroup);
                $res = $stmt->execute();
                $stmt = $db->prepare("UPDATE forms_$fg SET muid=:muid,fcont=:fcont,usergroup=:usergroup, mts=:mts WHERE fcid=:fcid AND fid=:fid AND fcont <> :fcont");
                $stmt->bindValue(':muid', $muid);
                $stmt->bindValue(':fcont', $fcont);
                $stmt->bindValue(':mts', $ts);
                $stmt->bindValue(':fcid', $fcid);
                $stmt->bindValue(':fid', $fid);
                $stmt->bindValue(':usergroup', $usergroup);
                $res = $stmt->execute();
            } else {
                $stmt = $db->prepare("DELETE FROM forms_$fg WHERE fcid=:fcid AND fid=:fid");
                $stmt->bindValue(':fcid', $fcid);
                $stmt->bindValue(':fid', $fid);
                $res = $stmt->execute();
            }
            // echo print_r($res);
        } catch (Exception $e) {
            echo print_r(value: 'FEHLER in der Datenverarbeitung '); //  . $e->getMessage()
        }
    }
    if (DB == 'MariaDB') {
        try {
            if (trim($fcont) || $fcont == 0) {
                // Direktes Insert oder Update in einem Schritt
                $sql = "
                    INSERT INTO forms_$fg (fcid, muid, fid, fcont, usergroup, mts)
                    VALUES (:fcid, :muid, :fid, :fcont, :usergroup, :mts)
                    ON DUPLICATE KEY UPDATE
                        muid = VALUES(muid),
                        fcont = VALUES(fcont),
                        usergroup = VALUES(usergroup),
                        mts = VALUES(mts)
                ";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':fcid', $fcid);
                $stmt->bindValue(':muid', $muid);
                $stmt->bindValue(':fid', $fid);
                $stmt->bindValue(':fcont', $fcont);
                $stmt->bindValue(':mts', $ts);
                $stmt->bindValue(':usergroup', $usergroup);
                $res = $stmt->execute();
            } else {
                // Löschen, falls kein Inhalt
                $stmt = $db->prepare("DELETE FROM forms_$fg WHERE fcid = :fcid AND fid = :fid");
                $stmt->bindValue(':fcid', $fcid);
                $stmt->bindValue(':fid', $fid);
                $res = $stmt->execute();
            }

            //echo print_r($res);
        } catch (Exception $e) {
            echo print_r(value: 'FEHLER in der Datenverarbeitung '); //  . $e->getMessage()
        }
    }
}

function get_visits($db, $pid, $this_fcid){ // identify first visit for one-time-fields
    $query = "SELECT fcid FROM forms_10005_list WHERE F_90=:pid ORDER BY fcid;";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':pid', $pid);
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_COLUMN); 
    return $res;
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

$befragung_a = q_execute($db, "Select DISTINCT fcid FROM forms_10010", PDO::FETCH_COLUMN);
echo "<br>Count befragung_a: ".$befragung_a[1];
$befragung_a = array_flip($befragung_a[0]);

$visite_a = q_execute($db, "Select fcid,fcont FROM forms_10005 WHERE fid=10005020");
echo "<br>Count befragung_a: ".$visite_a[1];
$visite_a = $visite_a[0];
// echo "<pre>"; echo print_r($visite_a); echo "</pre>";
foreach ($visite_a as $key => $val_a) { 
    if (array_key_exists($val_a['fcid'], $befragung_a)) {
        echo ".";// .$val_a['fcont'];
        ins_or_rep_form("", $db, 10010, $val_a['fcid'], -9, 1, 10005020, $val_a['fcont']);
    }
}
echo "... done!";
