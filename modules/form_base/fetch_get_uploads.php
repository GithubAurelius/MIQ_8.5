<?php
    session_start();
    require_once $_SESSION['INI-PATH'];
 
    $fg = 1;
    $muid     = $_SESSION["uid"];
    $fid      = $_REQUEST["fid"];
    $file_str = $_REQUEST["file_str"];
    $file_str = implode(',', array_filter(explode(',', $file_str))); // clean up string
    

    try {
        $tmp_str = "";
        $img_str = "";
        $stmt = $db->prepare("SELECT * FROM filedict WHERE did IN (".$file_str .") AND fid=".$fid." ORDER BY mts DESC");
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($res as $row) {
            if (substr_count(strtolower($row["dfilename"]),'.pdf')){
                if (!$tmp_str) $tmp_str .= "<tr><th colspan='2' class='realylightgray'><strong>Dokumente</strong></th></tr>";
                $tmp_str .= "<tr><td>".$row["mts"]."</td><td><a href='".MIQ_PATH."modules/show_file/show_file.php?FE=".urlencode($row["dcryptname"])."&FOL=".urlencode(base64_encode($row["dfolder"]))."&DBFN=".urlencode(base64_encode($row["dfilename"]))."' target='_BLANK'>".$row["dfilename"]."</a></td><tr>";
            } else {
                if (!$img_str) $img_str .= "<div class='realylightgray'><strong>Bilder</strong></div><div class='thumbnail-container'>";
                $ts_a = explode(" ", $row["mts"]); 
                $img_str .= "<div>".$ts_a[0]."<br>".$ts_a[1]."<br><a href='".MIQ_PATH."modules/show_file/show_file.php?FE=".urlencode($row["dcryptname"])."&FOL=".urlencode(base64_encode($row["dfolder"]))."&DBFN=".urlencode(base64_encode($row["dfilename"]))."' target='_BLANK'>";
                $img_str .= "<img title='".$row['dfilename']."' src='".MIQ_PATH_PHP."parse_img.php?C=1&P=".$row['dfolder']."&I=".$row['dcryptname']."'/>";
                $img_str .= "</a></div>";
            }
        }
        echo "<table class='small_table'>".$tmp_str."</table>".$img_str."</div>";
        // echo print_r($res);
    } catch(Exception $e) {
        echo print_r($res);
    }
