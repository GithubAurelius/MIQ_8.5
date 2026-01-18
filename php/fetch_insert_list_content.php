<?php
    session_start();
    require_once $_SESSION['INI-PATH'];

    $muid = $_SESSION['uid'];
    $data = base64_decode($_REQUEST["data"]);
    $data_a = json_decode($data, true);
    // echo "<pre>"; echo print_r($data_a); echo "</pre>";
    
    $col_str = "";
    $val_str = "";
    $col_str = implode(",",array_keys($data_a['cols']));
    $val_str = implode(",:",array_keys($data_a['cols']));
    
    $ts = date("Y-m-d H:i:s");
    try {
        $query_str = "REPLACE INTO ".$data_a["table"]." (".$col_str.",muid,mts) VALUES(:".$val_str.",:muid,:mts)";
        $stmt = $db->prepare($query_str);
        foreach ($data_a['cols'] as $key => $val) $stmt->bindValue(":$key", base64_decode($val));
        $stmt->bindValue(":muid", $muid);
        $stmt->bindValue(":mts", $ts);
        $res = $stmt->execute();
        // echo print_r("OK_".$fcont."_");
    } catch(Exception $e) {
        echo print_r($res);
    }
