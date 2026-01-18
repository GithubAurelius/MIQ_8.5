<?php
    session_start();
    require_once $_SESSION['INI-PATH'];
   
    $ts = date("Y-m-d H:i:s");
    $fcont  = trim(chop($_REQUEST["fcont"]));
    $muid = $_SESSION['uid'];
    $usergroup = $_SESSION['user_group'];
    
    $table = $_REQUEST["table"];
    $fcid   = $_REQUEST["key_v"];
    $fid    = $_REQUEST["col"];
    
    try {
        if ($fcont){
            $stmt = $db->prepare("INSERT OR IGNORE INTO $table (fcid,muid,fid,fcont,usergroup,mts) VALUES (:fcid,:muid,:fid,:fcont,:usergroup,:mts)");
            $stmt->bindValue(':fcid', $fcid);
            $stmt->bindValue(':muid', $muid);
            $stmt->bindValue(':fid', $fid);
            $stmt->bindValue(':fcont', $fcont);
            $stmt->bindValue(':mts', $ts);
            $stmt->bindValue(':usergroup', $usergroup);
            $res = $stmt->execute();
            $stmt = $db->prepare("UPDATE $table SET muid=:muid,fcont=:fcont, usergroup=:usergroup, mts=:mts WHERE fcid=:fcid AND fid=:fid");
            $stmt->bindValue(':muid', $muid);
            $stmt->bindValue(':fcont', $fcont);
            $stmt->bindValue(':mts', $ts);
            $stmt->bindValue(':fcid', $fcid);
            $stmt->bindValue(':fid', $fid);
            $stmt->bindValue(':usergroup', $usergroup);
            $res = $stmt->execute();
        } else {
            $stmt = $db->prepare("DELETE FROM $table WHERE fcid=:fcid AND fid=:fid");
            $stmt->bindValue(':fcid', $fcid);
            $stmt->bindValue(':fid', $fid);
            $res = $stmt->execute();
        }
        echo print_r($res);
    } catch(Exception $e) {
        echo print_r($res);
    }
