<?php
    session_start();
    require_once $_SESSION['INI-PATH'];
    
    $ts = date("Y-m-d H:i:s");
    $fcont  = trim(chop($_REQUEST["fcont"]));
    $muid = $_SESSION['uid'];
    $table = $_REQUEST["table"];
    
    $key_val = $_REQUEST["key_v"];
    $key_name = $_REQUEST["key_n"];
    $col    = $_REQUEST["col"];

    // if ($col == "login_pass") $fcont = md5($fcont);
    if ($col == "login_pass")  $fcont = password_hash($fcont, PASSWORD_DEFAULT);

    try {
        $stmt = $db->prepare("UPDATE $table SET $col=:fcont, mts=:mts WHERE $key_name=:$key_name");
        $stmt->bindValue(":$key_name", $key_val);
        $stmt->bindValue(":fcont", $fcont);
        $stmt->bindValue(":mts", $ts);
        $res = $stmt->execute();
        // echo print_r("OK_".$fcont."_");
    } catch(Exception $e) {
        echo print_r($res);
    }
