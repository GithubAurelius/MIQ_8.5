<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once $_SESSION['INI-PATH'];

$template_file = "form_template.php";
$css = "form_base.css";
if (isset($_POST['template_file'])) $template_file = $_SESSION['template_file'] = $_POST['template_file'];
if (isset($_POST['css'])) $css = $_SESSION['css'] = $_POST['css'];
if (isset($_SESSION['template_file'])) $template_file = $_SESSION['template_file'];
if (isset($_SESSION['css'])) $css = $_SESSION['css'];

?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creator</title>
    <link rel="stylesheet" href="../../css/<?php echo $css ?>">
</head>


<body style='padding:10px;background-color:red'>


    <?php

    // function exportTableAsCSV(PDO $db, string $tableName): void
    // {
    //     $stmt = $db->query("SELECT * FROM \"$tableName\"");
    //     $file = fopen("{$tableName}.csv", 'w');

    //     $columns = array_keys($stmt->fetch(PDO::FETCH_ASSOC));
    //     fputcsv($file, $columns);

    //     $stmt->execute();
    //     while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //         fputcsv($file, $row);
    //     }

    //     fclose($file);
    // }

    function exportTableAsExcelCompatibleCSV(PDO $db, string $tableName): void
    {
        $stmt = $db->query("SELECT * FROM " . $tableName);
        $filePath = $_SESSION["FS_ROOT"] . $_SESSION["PROJECT_PATH"] . 'forms_def/' . "{$tableName}.csv";
        $file = fopen($filePath, 'w');

        // UTF-8 BOM für Excel
        fwrite($file, "\xEF\xBB\xBF");

        $firstRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$firstRow) {
            fclose($file);
            return;
        }

        // Spaltenüberschriften
        fputcsv($file, array_keys($firstRow), ';', '"', '\\');

        // Erste Zeile
        fputcsv($file, $firstRow, ';', '"', '\\');

        // Restliche Zeilen
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($file, $row, ';', '"', '\\');
        }

        fclose($file);
    }
    function build_form_table($db, $fg)
    {
        $querstr = "CREATE TABLE IF NOT EXISTS forms_" . $fg . " (fcid BIGINT, fid  INTEGER, muid BIGINT, fcont TEXT, usergroup TEXT, mts TEXT, PRIMARY KEY (fcid, fid));";
        $stmt = $db->prepare($querstr);
        $res = $stmt->execute();
    }

    function build_view($db, $unset_str, $fg)
    {
        // $querstr = "SELECT fid,in_view FROM forms_definition WHERE fg=" . $fg . ";";
        // $stmt = $db->prepare($querstr);
        // $stmt->execute();
        // $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // $in_view_a = [];
        // foreach ($res as $row) $in_view_a[$row['fid']] = $row['in_view'];

        // $querstr = "SELECT DISTINCT fid FROM forms_definition WHERE fg=" . $fg . ";";
        // $stmt = $db->prepare($querstr);
        // $stmt->execute();
        // $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // $fid_a = [];
        // foreach ($res as $row) $fid_a[$row['fid']] = $row['fid'];
        // $unset_a = explode(",", $unset_str);
        // foreach ($unset_a as $val) {
        //     if (isset($fid_a[$val])) unset($fid_a[$val]);
        // }
        // if (count($fid_a)) {
        //     $query_heplstr  = "MAX(CASE WHEN fid = 10 THEN fcont END) AS F_10,";
        //     $query_heplstr .= "MAX(CASE WHEN fid = 20 THEN fcont END) AS F_20,";
        //     foreach ($fid_a as $fid => $val) {
        //         if ($in_view_a[$fid] == 1) $query_heplstr .= "MAX(CASE WHEN fid = " . $val . " THEN fcont END) AS F_" . $val . ",";
        //     }
        //     $query_heplstr = substr($query_heplstr, 0, -1);
        //     $querstr = "DROP VIEW IF EXISTS forms_" . $fg . "_list;";
        //     $stmt = $db->prepare($querstr);
        //     $stmt->execute();
        //     $querstr = "CREATE VIEW forms_" . $fg . "_list AS SELECT fcid,";
        //     $querstr .= $query_heplstr;
        //     $querstr .= " FROM forms_" . $fg . " GROUP BY fcid;";
        //     // echo $querstr;
        //     $stmt = $db->prepare($querstr);
        //     $stmt->execute();
        // }
        // exportTableAsCSV($db, 'forms_definition');

    }

    function db_write($db, $db_a)
    {
        if ($db_a['fid']) {
            $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME); // "sqlite" oder "mysql"

            if ($driver === 'sqlite') {
                // SQLite-Version
                $stmt = $db->prepare("
            INSERT OR IGNORE INTO forms_definition 
            (fid, fg, ftype, fname, foptions, ftitle, mts) 
            VALUES (:fid, :fg, :ftype, :fname, :foptions, :ftitle, :mts)
        ");
                $stmt->bindValue(':fid', $db_a['fid']);
                $stmt->bindValue(':fg', $db_a['fg']);
                $stmt->bindValue(':ftype', $db_a['ftype']);
                $stmt->bindValue(':fname', $db_a['fname']);
                $stmt->bindValue(':foptions', $db_a['foptions']);
                $stmt->bindValue(':ftitle', $db_a['ftitle']);
                $stmt->bindValue(':mts', $db_a['mts']);
                $res = $stmt->execute();

                $stmt = $db->prepare("
            UPDATE forms_definition 
            SET fname=:fname, ftype=:ftype, foptions=:foptions, ftitle=:ftitle, fg=:fg, mts=:mts 
            WHERE fid=:fid AND fg=:fg
        ");
                $stmt->bindValue(':fid', $db_a['fid']);
                $stmt->bindValue(':fg', $db_a['fg']);
                $stmt->bindValue(':ftype', $db_a['ftype']);
                $stmt->bindValue(':fname', $db_a['fname']);
                $stmt->bindValue(':foptions', $db_a['foptions']);
                $stmt->bindValue(':ftitle', $db_a['ftitle']);
                $stmt->bindValue(':mts', $db_a['mts']);
                $res = $stmt->execute();
            } elseif ($driver === 'mysql') {
                // MySQL-Version
                $stmt = $db->prepare("
            INSERT IGNORE INTO forms_definition 
            (fid, fg, ftype, fname, foptions, ftitle, mts) 
            VALUES (:fid, :fg, :ftype, :fname, :foptions, :ftitle, :mts)
        ");
                $stmt->bindValue(':fid', $db_a['fid']);
                $stmt->bindValue(':fg', $db_a['fg']);
                $stmt->bindValue(':ftype', $db_a['ftype']);
                $stmt->bindValue(':fname', $db_a['fname']);
                $stmt->bindValue(':foptions', $db_a['foptions']);
                $stmt->bindValue(':ftitle', $db_a['ftitle']);
                $stmt->bindValue(':mts', $db_a['mts']);
                $res = $stmt->execute();

                $stmt = $db->prepare("
            UPDATE forms_definition 
            SET fname=:fname, ftype=:ftype, foptions=:foptions, ftitle=:ftitle, fg=:fg, mts=:mts 
            WHERE fid=:fid AND fg=:fg
        ");
                $stmt->bindValue(':fid', $db_a['fid']);
                $stmt->bindValue(':fg', $db_a['fg']);
                $stmt->bindValue(':ftype', $db_a['ftype']);
                $stmt->bindValue(':fname', $db_a['fname']);
                $stmt->bindValue(':foptions', $db_a['foptions']);
                $stmt->bindValue(':ftitle', $db_a['ftitle']);
                $stmt->bindValue(':mts', $db_a['mts']);
                $res = $stmt->execute();
            }
        }
    }

    function write_field_definition($db, $fg, $form_name, $file_str_a, $unset_str = "")
    {
        // vor dern Löschen forms_definition.csv backup
        if ($_POST['activate'] && $_POST['activate'] == 1) {
            $stmt = $db->prepare("DELETE FROM forms_definition WHERE fg=" . $fg . ";");
            $stmt->execute();
            // echo "DEF deleted";
        }
        $ts = date("Y-m-d H:i:s");
        foreach ($file_str_a as $line_key => $val_a) {
            // echo "<br>".$line_key;
            if ($line_key > 0) {
                $db_a = [];
                foreach ($val_a as $key => $val) {
                    $val_a[$key] = trim(chop($val_a[$key]));
                    $db_a['fid']        = ($val_a[0] == "") ? 0 : $val_a[0];
                    $db_a['fg']         = $fg;
                    $db_a['ftype']      = $val_a[1];
                    $db_a['foptions']   = $val_a[3];
                    $db_a['ftitle']     = $form_name;
                    $db_a['mts']        =  $ts;
                    $db_a['shortname']  = "";
                    if (preg_match('/`([^`]+)`/', $val_a[2], $matches)) {
                        $db_a['shortname'] = $matches[1];
                    }
                    $db_a['fname']      = $val_a[2];
                }
                // echo "<pre>"; echo print_r($db_a); echo "</pre>";
                db_write($db, $db_a);
            }
        }
        build_form_table($db, $fg);
        if (isset($_POST['view']) && $_POST['view']) build_view($db, $unset_str, $fg);
        if ($_POST['activate'] && $_POST['activate'] == 1) exportTableAsExcelCompatibleCSV($db, 'forms_definition');
    }

    function replace_blank_pattern($text)
    {
        return preg_replace_callback('/#(\d{1,2})#/', function ($matches) {
            $n = intval($matches[1]);
            if ($n >= 1 && $n <= 200) {
                return str_repeat("&nbsp;", $n);
            } else {
                return $matches[0]; // Ungültiges n, Originalmuster beibehalten
            }
        }, $text);
    }

    function replace_blank_pattern_tag($text)
    {
        return preg_replace_callback('/@(\d{1,2})@/', function ($matches) {
            $n = intval($matches[1]);
            if ($n >= 1 && $n <= 200) {
                return "";
            } else {
                return $matches[0]; // Ungültiges n, Originalmuster beibehalten
            }
        }, $text);
    }

    function extract_n($muster)
    {
        if (preg_match('/@(\d{1,2})@/', $muster, $matches)) {
            $n = intval($matches[1]);
            if ($n >= 1 && $n <= 200) {
                return $n;
            }
        }
        return null; // Gibt null zurück, wenn das Muster ungültig ist oder n außerhalb des Bereichs liegt
    }

    function build_element($_a)
    {
        global $template_file, $fg;
        if (!isset($_SESSION['col_type'])) $_SESSION['col_type'] = "col";
        if ($template_file == "form_template.php") {
            $tmp_str = "";
            $indent_str = "";
            $indent = extract_n($_a[2]);
            if ($indent) $indent_str = "style='padding-left:" . $indent . "px;'";
            // $_a[2] = str_replace("@ @", "&nbsp;", $_a[2]);

            if (preg_match('/`([^`]+)`/', $_a[2], $matches)) {
                $_a['shortname'] = $matches[1]; // $matches[0] wäre der gesamte gefundene Teil (`inhalt`), $matches[1] nur der Inhalt
                $_a[2] = str_replace($matches[0], '', $_a[2]);
            }
            $_a[2] = replace_blank_pattern_tag($_a[2]);
            $_a[2] = replace_blank_pattern($_a[2]);
            $_a[2] = str_replace("@semikolon@", ";", $_a[2]);
            $_a[2] = str_replace("@,@", ";", $_a[2]);
            $_a[2] = str_replace("@blank@", "&nbsp;", $_a[2]);
            $_a[2] = preg_replace('/\[.*?\]/', '', $_a[2]);
            $medic = "";
            if (substr_count($_a[2], '@MEDIC@')) {
                $medic = ' medic';
                $_a[2] = str_replace('@MEDIC@', '', $_a[2]);
            }
            $onetime = "";
            if (substr_count($_a[2], '@ONETIME@')) {
                $onetime = ' onetime';
                $_a[2] = str_replace('@ONETIME@', '', $_a[2]);
            }
            $show_hide = 'SH_' . $_a[0];
            $_a2_a = explode("|", $_a[2]);
            if (isset($_a[4]) && $_a[4] == 'required') $required = "required";
            else $required = "";
            $desc_0 = $_a2_a[0];
            $desc_1 = $_a2_a[1] ?? "";
            // echo "<pre>"; echo print_r($_a); echo "</pre>";
            $display_type = "block";

            switch ($_a[1]) {
                case "date_1br":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col' id='" . $show_hide . "_a'>
                        <div class='desc_f'>$desc_0</div>
                            <input $required type='date' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" placeholder='$desc_1'>
                        </div>
                    </div>";
                    break;
                case "text":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_a' id='" . $show_hide . "_a'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div>
                    </div>
                    <div class='col_b' id='" . $show_hide . "_b'>
                        <input data-fg='$fg' $required type='text' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" placeholder='$desc_1'>
                    </div>";
                    break;
                case "text_1br":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col' id='" . $show_hide . "_a'>
                        <span class='desc_f'>$desc_0</span>
                            <input data-fg='$fg' $required type='text' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" placeholder='$desc_1'>
                       
                    </div>";
                    break;
                case "select_1br":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col'  id='" . $show_hide . "_a'>
                        <div class='desc_f'>$desc_0</div>
                        <select $required  id='FF_$_a[0]' name='FF_$_a[0]'  onchange='follow_select(this)'>";
                    $opt_str_a = explode("|", $_a[3]);
                    if ($desc_1) $opt_str = "<option value=''>$desc_1</option>";
                    else $opt_str = "<option value=''></option>";
                    foreach ($opt_str_a as $val) $opt_str .= "<option value='$val' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '$val') echo 'selected'; ?>>$val</option>";
                    $tmp_str .= $opt_str . "</select>
                    </div>";
                    break;
                case "textarea_1br":
                    if ($_a[3]) $rows = $_a[3];
                    else $rows = 1;
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col' id='" . $show_hide . "_a'>
                        <div class='desc_f'>$desc_0</div>
                        <textarea $required id='FF_$_a[0]' name='FF_$_a[0]' style='height:" . ((int)$_a[3] * 24) . "px;'><?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?></textarea>
                    </div>";
                    break;
                case "textarea_1br_100":
                    if ($_a[3]) $rows = $_a[3];
                    else $rows = 1;
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_100' id='" . $show_hide . "_a' style='padding-left:5px'><div class='desc_f'>$desc_0</div>
                        <textarea $required id='FF_$_a[0]' name='FF_$_a[0]' style='height:" . ((int)$_a[3] * 24) . "px;'><?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?></textarea>
                    </div>";
                    break;
                case "col_type_switch";
                    $_SESSION['col_type'] = $_a[2];
                    break;
                case "fieldset":
                    $tmp_str .= "\n\t\t\t<fieldset><legend>$desc_0</legend><fs_cont/>\n\t\t\t</fieldset>";
                    break;
                case "start_fieldset":
                    if ($_a[1]) $id_set = " id='FS_" . $_a[0] . "'";
                    $tmp_str .= "\n\t\t\t<fieldset" . $id_set . "><legend>$desc_0</legend>";
                    break;
                case "stop_fieldset":
                    $tmp_str .= "\n\t\t\t</fieldset>";
                    break;
                case "start_log_block":
                    $tmp_str .= "\n\t\t\t
                        <div id='" . $_a[2] . "'>";
                    break;
                case "start_span_block":
                    $tmp_str .= "\n
                        <span id='FS_" . $_a[0] . "'>";
                    break;
                case "stop_span_block":
                    $tmp_str .= "\n
                        </span>";
                    break;
                case "stop_log_block":
                    $tmp_str .= "\n\t\t\t
                        </div>";
                    break;
                case "start_block":
                    $display_type = "block";
                    $tmp_str .= "\n\t\t\t<div class='col_100" . $medic . $onetime . "' ><div id='B_$_a[3]_$desc_0' class='block' style='display:none'>";
                    break;
                case "start_block_1":
                    $display_type = "flex";
                    $tmp_str .= "\n\t\t\t<div class='col_100" . $medic . $onetime . "'><div id='B_$_a[3]_$desc_0' class='block_1' style='display:none'>";
                    break;
                case "start_block_2":
                    $display_type = "flex";
                    $tmp_str .= "\n\t\t\t<div class='col_100" . $medic . $onetime . "'><div id='B_$_a[3]_$desc_0' class='block_2" . $medic . $onetime . "' style='display:none'>";
                    break;
                case "stop_block":
                    $tmp_str .= "\n\t\t\t</div></div><!--block-->";
                    break;
                case "start_row":
                    $tmp_str .= "\n\t\t\t\t<div class='row" . $medic . $onetime . "'>";
                    break;
                case "start_hidden_row":
                    $tmp_str .= "\n\t\t\t\t<div class='row' style='height:0;visibility: collapse;'>";
                    break;
                case "end_row":
                    $tmp_str .= "\n\t\t\t\t</div>";
                    break;
                case "new_row":
                    $tmp_str .= "\n\t\t\t\t</div>\n\t\t\t\t<div class='row'>";
                    break;
                case "newline":
                    $tmp_str .= "\n\t\t\t\t<br>";
                    break;
                case "info":
                    $tmp_str .= "\n\t\t\t\t\t<div class='" . $_SESSION['col_type'] . " infotext" . $medic . $onetime . "'>$desc_0</div>";
                    break;
                case "iframe":
                    $tmp_str .= "\n\t\t\t\t\t<div style='width:100%'>$desc_0</div>";
                    break;
                case "info_100":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col_100 infotext' " . $indent_str . " id='" . $show_hide . "'>$desc_0</div>";
                    break;
                case "info_100_r":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col_100 infotext" . $medic . $onetime . "' style='text-align:right'>$desc_0</div>";
                    break;
                case "logicblock_100":
                    $display_type = "block";
                    $tmp_str .= "\n\t\t\t<div class='col_100" . $medic . $onetime . "' ><div id='B_$_a[0]' class='block' style='display:none'>";
                    break;
                    break;
                case "idonly_100":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col_100' id='idonly_$_a[0]'></div>";
                    break;
                case "idblock_100":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col_100' id='idblock_$_a[0]'>";
                    break;
                case "info_b":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col infotext'><strong>$desc_0</strong></div>";
                    break;
                // case "text":
                //     $tmp_str .= "\n\t\t\t\t\t<div class='col'><div class='desc_f'>$desc_0</div><input placeholder='$desc_1' type='$_a[1]' id='FF_$_a[0]' name='FF_$_a[0]'></div>";
                //     break;
                case "text":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_a' id='" . $show_hide . "_a'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div>
                    </div>
                    <div class='col_b' id='" . $show_hide . "_b'>
                        <input data-fg='$fg' $required type='text' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" placeholder='$desc_1'>
                    </div>";
                    break;
                case "date_ab":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_a' id='" . $show_hide . "_a'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div>
                    </div>
                    <div class='col_b' id='" . $show_hide . "_b'>
                        <input data-fg='$fg' $required type='date' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" placeholder='$desc_1'>
                    </div>";
                    break;
                case "text_span":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_a' id='" . $show_hide . "_a'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div>
                    </div>
                    <div class='col_b' id='" . $show_hide . "_b'  style='display: flex; flex-wrap: nowrap;white-space: nowrap;'>
                        <input data-fg='$fg' $required type='text' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" placeholder='$desc_1'><span id='span_$_a[0]'></span>
                    </div>";
                    break;
                case "text_col":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div>
                    </div>
                    <div class='col'>
                        <input data-fg='$fg' $required type='text' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" placeholder='$desc_1'>
                    </div>";
                    break;
                case "text_r":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_a'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div>
                    </div>
                    <div class='col_b'>
                        <input readonly $required type='$_a[1]' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" placeholder='$desc_1'>
                    </div>";
                    break;
                case "number":
                    $opt_str_a = explode("|", $_a[3]);
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_a $onetime' id='" . $show_hide . "_a'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div>
                    </div>
                    <div class='col_b $onetime' id='" . $show_hide . "_b'>
                        <input $required type='$_a[1]' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" min='$opt_str_a[0]' max='$opt_str_a[1]' step='$opt_str_a[2]' placeholder='$desc_1'>
                    </div>";
                    break;
                case "number_col":
                    $opt_str_a = explode("|", $_a[3]);
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div>
                    </div>
                    <div class='col'>
                        <input $required type='number' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" min='$opt_str_a[0]' max='$opt_str_a[1]' step='$opt_str_a[2]' placeholder='$desc_1'>
                    </div>";
                    break;
                case "text_1":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col'>
                        <div class='desc_f'>$desc_0</div>
                            <input $required type='text' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" placeholder='$desc_1'>
                        </div>
                    </div>";
                    break;
                case "date_1_col_30":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_30'>
                        <div class='desc_f'>$desc_0</div>
                            <input $required type='date' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" placeholder='$desc_1'>
                        </div>
                    </div>";
                    break;
                case "date_1_col_70":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_50'>
                        <div class='desc_f'>$desc_0</div>
                            <input $required type='date' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" placeholder='$desc_1'>
                        </div>
                    </div>";
                    break;
                case "text_1_col_30":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_30'>
                        <div class='desc_f'>$desc_0</div>
                            <input $required type='text' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" placeholder='$desc_1'>
                        </div>
                    </div>";
                    break;
                case "text_25":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_25'>
                        <div class='desc_f_25'>$desc_0</div>
                            <input $required type='text' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" placeholder='$desc_1'>
                        </div>
                    </div>";
                    break;
                case "number_1":
                    $opt_str_a = explode("|", $_a[3]);
                    $tmp_str .= "\n\t\t\t\t\t<div class='col'><div class='desc_f'>$desc_0</div><input $required type='number' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" min='$opt_str_a[0]' max='$opt_str_a[1]' step='$opt_str_a[2]' placeholder='$desc_1'></div>";
                    break;
                case "number_1_col_30":
                    $opt_str_a = explode("|", $_a[3]);
                    $tmp_str .= "\n\t\t\t\t\t<div class='col_30'><div class='desc_f'>$desc_0</div><input $required type='number' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" min='$opt_str_a[0]' max='$opt_str_a[1]' step='$opt_str_a[2]' placeholder='$desc_1'></div>";
                    break;
                case "text_2":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col_one'><div class='desc_f'>$desc_0</div><input type='text' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\"></div>";
                    break;
                case "date":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col'><div class='desc_f'>$desc_0</div></div><div class='col'><input $required type='date' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" placeholder='$desc_1'></div>";
                    break;
                case "date_1":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col'><div class='desc_f'>$desc_0</div></div><div class='col' style='display: flex; flex-wrap: nowrap;white-space: nowrap;'><input $required type='date' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\"></div>";
                    break;
                case "date_2":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col'><div class='desc_f'>$desc_0</div><input $required type='date' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\"></div>";
                    break;
                case "date_tc":
                    // from_year, to_year, not_in_future, not_in_past, mode, locale
                    $opt_str_a = explode("|", $_a[3]);
                    // echo "<pre>"; echo print_r($opt_str_a); echo "</pre>"; 
                    if (!isset($opt_str_a[0]) || (isset($opt_str_a[0]) && $opt_str_a[0] == '')) $opt_str_a[0] = '';
                    if (!isset($opt_str_a[1]) || (isset($opt_str_a[1]) && $opt_str_a[1] == '')) $opt_str_a[1] = '';
                    if (!isset($opt_str_a[2]) || (isset($opt_str_a[2]) && $opt_str_a[2] == '')) $opt_str_a[2] = 'false';
                    if (!isset($opt_str_a[3]) || (isset($opt_str_a[3]) && $opt_str_a[3] == '')) $opt_str_a[3] = 'false';
                    if (!isset($opt_str_a[4]) || (isset($opt_str_a[4]) && $opt_str_a[4] == '')) $opt_str_a[4] = 'YMD';
                    if (!isset($opt_str_a[5]) || (isset($opt_str_a[5]) && $opt_str_a[5] == '')) $opt_str_a[5] = 'de';
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_a" . $medic . $onetime . "' id='" . $show_hide . "_a'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div>
                    </div>
                    <div class='col_b" . $medic . $onetime . "' style='display: flex; flex-wrap: nowrap; white-space: nowrap;justify-content: flex-end;' id='" . $show_hide . "_b'>
                        <select id='FF_$_a[0]_day_select' class='hidden' style='max-width:45px;'><option value=''>Tag wählen</option></select>
                        <select id='FF_$_a[0]_month_select' class='hidden' style='max-width:62px;'><option value=''>Monat wählen</option></select>
                        <select $required id='FF_$_a[0]_year_select' style='max-width:60px;'><option value=''>Jahr wählen</option></select>
                        <input type='hidden' placeholder='wählen' style='min-width:80px';' $required size='9' class='control_input' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\">
                    </div>
                    <script>multi_date('FF_$_a[0]','$opt_str_a[0]','$opt_str_a[1]',$opt_str_a[2],$opt_str_a[3],'$opt_str_a[4]','$opt_str_a[5]');</script>"; // onChange=\"check_date(this,'','today')\"
                    break;
                case "date_tc_25":
                    // from_year, to_year, not_in_future, not_in_past, mode, locale
                    $opt_str_a = explode("|", $_a[3]);
                    // echo "<pre>"; echo print_r($opt_str_a); echo "</pre>"; 
                    if (!isset($opt_str_a[0]) || (isset($opt_str_a[0]) && $opt_str_a[0] == '')) $opt_str_a[0] = '';
                    if (!isset($opt_str_a[1]) || (isset($opt_str_a[1]) && $opt_str_a[1] == '')) $opt_str_a[1] = '';
                    if (!isset($opt_str_a[2]) || (isset($opt_str_a[2]) && $opt_str_a[2] == '')) $opt_str_a[2] = 'false';
                    if (!isset($opt_str_a[3]) || (isset($opt_str_a[3]) && $opt_str_a[3] == '')) $opt_str_a[3] = 'false';
                    if (!isset($opt_str_a[4]) || (isset($opt_str_a[4]) && $opt_str_a[4] == '')) $opt_str_a[4] = 'YMD';
                    if (!isset($opt_str_a[5]) || (isset($opt_str_a[5]) && $opt_str_a[5] == '')) $opt_str_a[5] = 'de';
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_25'>
                        <div class='desc_f_25' " . $indent_str . ">$desc_0</div>
                        <div style='display: flex; flex-wrap: nowrap;white-space: nowrap;'>";
                    $tmp_str .= "<select id='FF_$_a[0]_day_select' class='hidden' style='max-width:45px;'><option value=''>Tag wählen</option></select>";
                    $tmp_str .= "<select id='FF_$_a[0]_month_select' class='hidden' style='max-width:45px;'><option value=''>Monat wählen</option></select>";
                    $tmp_str .= "<select $required id='FF_$_a[0]_year_select' style='max-width:60px;'><option value=''>Jahr wählen</option></select>";
                    $tmp_str .= "<input type='text' placeholder='wählen' style='min-width:80px';' $required size='12' class='control_input' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\">
                        </div>
                    </div>
                    <script>multi_date('FF_$_a[0]','$opt_str_a[0]','$opt_str_a[1]',$opt_str_a[2],$opt_str_a[3],'$opt_str_a[4]','$opt_str_a[5]');</script>"; // onChange=\"check_date(this,'','today')\"
                    break;
                case "date_tc_1":
                    // from_year, to_year, not_in_future, not_in_past, mode, locale
                    $opt_str_a = explode("|", $_a[3]);
                    // echo "<pre>"; echo print_r($opt_str_a); echo "</pre>"; 
                    if (!isset($opt_str_a[0]) || (isset($opt_str_a[0]) && $opt_str_a[0] == '')) $opt_str_a[0] = '';
                    if (!isset($opt_str_a[1]) || (isset($opt_str_a[1]) && $opt_str_a[1] == '')) $opt_str_a[1] = '';
                    if (!isset($opt_str_a[2]) || (isset($opt_str_a[2]) && $opt_str_a[2] == '')) $opt_str_a[2] = 'false';
                    if (!isset($opt_str_a[3]) || (isset($opt_str_a[3]) && $opt_str_a[3] == '')) $opt_str_a[3] = 'false';
                    if (!isset($opt_str_a[4]) || (isset($opt_str_a[4]) && $opt_str_a[4] == '')) $opt_str_a[4] = 'YMD';
                    if (!isset($opt_str_a[5]) || (isset($opt_str_a[5]) && $opt_str_a[5] == '')) $opt_str_a[5] = 'de';
                    $tmp_str .= "\n\t\t\t\t\t
                        <div class='desc_f' " . $indent_str . ">$desc_0</div><br>
                        <div class='col' style='display: flex; flex-wrap: nowrap;white-space: nowrap;'>
                            <select id='FF_$_a[0]_day_select' class='hidden' style='max-width:45px;'><option value=''>Tag wählen</option></select>
                            <select id='FF_$_a[0]_month_select' class='hidden' style='max-width:45px;'><option value=''>Monat wählen</option></select>
                            <select $required id='FF_$_a[0]_year_select' style='max-width:60px;'><option value=''>Jahr wählen</option></select>
                            <input type='hidden' placeholder='wählen' style='min-width:80px';' $required size='12' class='control_input' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\">
                        </div>
                    <script>multi_date('FF_$_a[0]','$opt_str_a[0]','$opt_str_a[1]',$opt_str_a[2],$opt_str_a[3],'$opt_str_a[4]','$opt_str_a[5]');</script>"; // onChange=\"check_date(this,'','today')\"
                    break;
                case "date_tc_1_col_30":
                    // from_year, to_year, not_in_future, not_in_past, mode, locale
                    $opt_str_a = explode("|", $_a[3]);
                    // echo "<pre>"; echo print_r($opt_str_a); echo "</pre>"; 
                    if (!isset($opt_str_a[0]) || (isset($opt_str_a[0]) && $opt_str_a[0] == '')) $opt_str_a[0] = '';
                    if (!isset($opt_str_a[1]) || (isset($opt_str_a[1]) && $opt_str_a[1] == '')) $opt_str_a[1] = '';
                    if (!isset($opt_str_a[2]) || (isset($opt_str_a[2]) && $opt_str_a[2] == '')) $opt_str_a[2] = 'false';
                    if (!isset($opt_str_a[3]) || (isset($opt_str_a[3]) && $opt_str_a[3] == '')) $opt_str_a[3] = 'false';
                    if (!isset($opt_str_a[4]) || (isset($opt_str_a[4]) && $opt_str_a[4] == '')) $opt_str_a[4] = 'YMD';
                    if (!isset($opt_str_a[5]) || (isset($opt_str_a[5]) && $opt_str_a[5] == '')) $opt_str_a[5] = 'de';
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_30'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div><br>
                        <div style='display: flex; flex-wrap: nowrap;white-space: nowrap;'>
                            <select id='FF_$_a[0]_day_select' class='hidden' style='max-width:45px;'><option value=''>Tag wählen</option></select>
                            <select id='FF_$_a[0]_month_select' class='hidden' style='max-width:45px;'><option value=''>Monat wählen</option></select>
                            <select $required id='FF_$_a[0]_year_select' style='max-width:60px;'><option value=''>Jahr wählen</option></select>
                            <input type='text' placeholder='wählen' style='min-width:80px';' $required size='12' class='control_input' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\">
                        </div>
                    </div>
                    <script>multi_date('FF_$_a[0]','$opt_str_a[0]','$opt_str_a[1]',$opt_str_a[2],$opt_str_a[3],'$opt_str_a[4]','$opt_str_a[5]');</script>"; // onChange=\"check_date(this,'','today')\"
                    break;
                case "date_tc_1_col_30_ohne_titel":
                    // from_year, to_year, not_in_future, not_in_past, mode, locale
                    $opt_str_a = explode("|", $_a[3]);
                    // echo "<pre>"; echo print_r($opt_str_a); echo "</pre>"; 
                    if (!isset($opt_str_a[0]) || (isset($opt_str_a[0]) && $opt_str_a[0] == '')) $opt_str_a[0] = '';
                    if (!isset($opt_str_a[1]) || (isset($opt_str_a[1]) && $opt_str_a[1] == '')) $opt_str_a[1] = '';
                    if (!isset($opt_str_a[2]) || (isset($opt_str_a[2]) && $opt_str_a[2] == '')) $opt_str_a[2] = 'false';
                    if (!isset($opt_str_a[3]) || (isset($opt_str_a[3]) && $opt_str_a[3] == '')) $opt_str_a[3] = 'false';
                    if (!isset($opt_str_a[4]) || (isset($opt_str_a[4]) && $opt_str_a[4] == '')) $opt_str_a[4] = 'YMD';
                    if (!isset($opt_str_a[5]) || (isset($opt_str_a[5]) && $opt_str_a[5] == '')) $opt_str_a[5] = 'de';
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_30'>
                        <div style='display: flex; flex-wrap: nowrap;white-space: nowrap;'>
                            <select id='FF_$_a[0]_day_select' class='hidden' style='max-width:45px;'><option value=''>Tag wählen</option></select>
                            <select id='FF_$_a[0]_month_select' class='hidden' style='max-width:45px;'><option value=''>Monat wählen</option></select>
                            <select $required id='FF_$_a[0]_year_select' style='max-width:60px;'><option value=''>Jahr wählen</option></select>
                            <input type='text' placeholder='wählen' style='min-width:80px';' $required size='12' class='control_input' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\">
                        </div>
                    </div>
                    <script>multi_date('FF_$_a[0]','$opt_str_a[0]','$opt_str_a[1]',$opt_str_a[2],$opt_str_a[3],'$opt_str_a[4]','$opt_str_a[5]');</script>"; // onChange=\"check_date(this,'','today')\"
                    break;
                case "date_tc_col":
                    // from_year, to_year, not_in_future, not_in_past, mode, locale
                    $opt_str_a = explode("|", $_a[3]);
                    // echo "<pre>"; echo print_r($opt_str_a); echo "</pre>"; 
                    if (!isset($opt_str_a[0]) || (isset($opt_str_a[0]) && $opt_str_a[0] == '')) $opt_str_a[0] = '';
                    if (!isset($opt_str_a[1]) || (isset($opt_str_a[1]) && $opt_str_a[1] == '')) $opt_str_a[1] = '';
                    if (!isset($opt_str_a[2]) || (isset($opt_str_a[2]) && $opt_str_a[2] == '')) $opt_str_a[2] = 'false';
                    if (!isset($opt_str_a[3]) || (isset($opt_str_a[3]) && $opt_str_a[3] == '')) $opt_str_a[3] = 'false';
                    if (!isset($opt_str_a[4]) || (isset($opt_str_a[4]) && $opt_str_a[4] == '')) $opt_str_a[4] = 'YMD';
                    if (!isset($opt_str_a[5]) || (isset($opt_str_a[5]) && $opt_str_a[5] == '')) $opt_str_a[5] = 'de';
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div>
                    </div>
                    <div class='col' style='display: flex; flex-wrap: nowrap;white-space: nowrap;'>
                        <select id='FF_$_a[0]_day_select' class='hidden' style='max-width:45px;'><option value=''>Tag wählen</option></select>
                        <select id='FF_$_a[0]_month_select' class='hidden' style='max-width:45px;'><option value=''>Monat wählen</option></select>
                        <select id='FF_$_a[0]_year_select' style='max-width:60px;'><option value=''>Jahr wählen</option></select>
                        <input type='text' placeholder='wählen' style='min-width:80px';' $required size='12' class='control_input' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\">
                    </div>
                    <script>multi_date('FF_$_a[0]','$opt_str_a[0]','$opt_str_a[1]',$opt_str_a[2],$opt_str_a[3],'$opt_str_a[4]','$opt_str_a[5]');</script>"; // onChange=\"check_date(this,'','today')\"
                    break;

                case "checkbox":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='" . $_SESSION['col_type'] . ".$medic.$onetime." . "'>
                        <table class='table-checkbox_1" . $medic . $onetime . "'>
                            <tr>
                                <td><input type='hidden' id='FF_$_a[0]' name='FF_$_a[0]' value='0'><input type='checkbox' id='FF_$_a[0]' name='FF_$_a[0]' value='1' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '1') echo 'checked'; ?>></td>
                                <td><div class='desc_f'>" . $desc_0 . "</div></td>
                            </tr>
                            </table>
                    </div>";
                    break;
                case "checkbox_1":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col_100" . ".$medic.$onetime." . "'><table class='table-checkbox_1'><tr><td><input type='hidden' id='FF_$_a[0]' name='FF_$_a[0]' value='0'><input type='checkbox' id='FF_$_a[0]' name='FF_$_a[0]' value='1' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '1') echo 'checked'; ?>></td><td><div class='desc_f'>" . $desc_0 . "</div></td></tr></table></div>";
                    break;
                case "checkbox_1_1":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_b" . $medic . $onetime . "' style='padding-left:40%' id='" . $show_hide . "_b'>
                        <table class='table-checkbox_1" . $medic . $onetime . "'>
                            <tr>
                                <td>
                                    <input type='hidden' id='FF_$_a[0]' name='FF_$_a[0]' value='0'><input type='checkbox' id='FF_$_a[0]' name='FF_$_a[0]' value='1' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '1') echo 'checked'; ?>>
                                </td>
                                <td>
                                    <div class='desc_f'>" . $desc_0 . "</div>
                                </td>
                            </tr>
                        </table>
                    </div>";
                    break;

                case "checkbox_base":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col" . $medic . $onetime . "' style='padding-left:40%' id='" . $show_hide . "_b'>
                        <div id='cbm_$_a[0]' style='float: right;'>
                            <input data-cb='$_a[0]' $required class='sim_hide' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo $" . "form_data_a[$_a[0]] ?? ''; ?>\"  onchange='follow_select(this)'>
                            <label class='custom-checkbox-wrapper'><span id='CB_$_a[0]' class='custom-checkbox'></span> <span class='custom-checkbox-label'>" . $desc_0 . "</span></label>
                        </div>  
                    </div>";
                    break;
                case "checkbox_base_100":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_100" . $medic . $onetime . "' id='" . $show_hide . "_b'>
                        <div id='cbm_$_a[0]'>
                            <input data-cb='$_a[0]' $required class='sim_hide' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo $" . "form_data_a[$_a[0]] ?? ''; ?>\"  onchange='follow_select(this)'>
                            <label class='custom-checkbox-wrapper'><span id='CB_$_a[0]' class='custom-checkbox'></span> <span class='custom-checkbox-label'>" . $desc_0 . "</span></label>
                        </div>  
                    </div>";
                    break;
                case "checkbox_base_60_indent":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_100" . $medic . $onetime . "' id='" . $show_hide . "_b' style='padding-left:40%' >
                        <div id='cbm_$_a[0]'>
                            <input data-cb='$_a[0]' $required class='sim_hide' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo $" . "form_data_a[$_a[0]] ?? ''; ?>\"  onchange='follow_select(this)'>
                            <label class='custom-checkbox-wrapper'><span id='CB_$_a[0]' class='custom-checkbox'></span> <span class='custom-checkbox-label'>" . $desc_0 . "</span></label>
                        </div>  
                    </div>";
                    break;
                case "checkbox_base_100_r":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_100" . $medic . $onetime . "'  id='" . $show_hide . "_b'>
                        <div id='cbm_$_a[0]' style='text-align:right;margin-right:7px'>
                            <input data-cb='$_a[0]' $required class='sim_hide' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo $" . "form_data_a[$_a[0]] ?? ''; ?>\"  onchange='follow_select(this)'>
                            <label class='custom-checkbox-wrapper'><span id='CB_$_a[0]' class='custom-checkbox'></span> <span class='custom-checkbox-label'>" . $desc_0 . "</span></label>
                        </div>  
                    </div>";
                    break;
                case "checkbox_base_30":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_30" . $medic . $onetime . "' id='" . $show_hide . "_b'>
                        <div id='cbm_$_a[0]'>
                            <input data-cb='$_a[0]' $required class='sim_hide' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo $" . "form_data_a[$_a[0]] ?? ''; ?>\"  onchange='follow_select(this)'>
                            <label class='custom-checkbox-wrapper'><span id='CB_$_a[0]' class='custom-checkbox'></span> <span class='custom-checkbox-label'>" . $desc_0 . "</span></label>
                        </div>  
                    </div>";
                    break;
                case "checkbox_base_60":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_60" . $medic . $onetime . "' id='" . $show_hide . "_b'>
                        <div id='cbm_$_a[0]'>
                            <input data-cb='$_a[0]' $required class='sim_hide' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo $" . "form_data_a[$_a[0]] ?? ''; ?>\"  onchange='follow_select(this)'>
                            <label class='custom-checkbox-wrapper'><span id='CB_$_a[0]' class='custom-checkbox'></span> <span class='custom-checkbox-label'>" . $desc_0 . "</span></label>
                        </div>  
                    </div>";
                    break;
                case "checkbox_base_30_c_l":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_30 " . $medic . $onetime . "' id='" . $show_hide . "_b'>
                        <div id='cbm_$_a[0]' class='bottomline'>
                            <center>
                            <input data-cb='$_a[0]' $required class='sim_hide' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo $" . "form_data_a[$_a[0]] ?? ''; ?>\"  onchange='follow_select(this)'>
                            <label class='custom-checkbox-wrapper'><span id='CB_$_a[0]' class='custom-checkbox'></span> <span class='custom-checkbox-label'>" . $desc_0 . "</span></label>
                            </center>
                        </div>  
                    </div>";
                    break;
                case "text_30":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_30' id='" . $show_hide . "_a'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div>
                    </div>
                    <div class='col_30' id='" . $show_hide . "_b'>
                        <input data-fg='$fg' $required type='text' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" placeholder='$desc_1'>
                    </div>";
                    break;
                case "text_0_60":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_60' id='" . $show_hide . "_b'>
                        <input data-fg='$fg' $required type='text' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" placeholder='$desc_1'>
                    </div>";
                    break;
                case "checkbox_base_100_r":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_100" . $medic . $onetime . "' style='text-align:right' id='" . $show_hide . "_b'>
                        <div id='cbm_$_a[0]'>
                            <input data-cb='$_a[0]' $required class='sim_hide' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo $" . "form_data_a[$_a[0]] ?? ''; ?>\"  onchange='follow_select(this)'>
                            <label class='custom-checkbox-wrapper'><span id='CB_$_a[0]' class='custom-checkbox'></span> <span class='custom-checkbox-label'>" . $desc_0 . "</span></label>
                        </div>  
                    </div>";
                    break;
                case "textarea":
                    if ($_a[3]) $rows = $_a[3];
                    else $rows = 1;
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col'><div class='desc_f'>$desc_0</div>
                        <textarea $required id='FF_$_a[0]' name='FF_$_a[0]' rows='$rows'><?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?></textarea>
                    </div>";
                    break;
                case "textarea_1":
                    if ($_a[3]) $rows = $_a[3];
                    else $rows = 1;
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_a" . $medic . $onetime . "' id='" . $show_hide . "_a'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div>
                    </div>
                    <div class='col_b" . $medic . $onetime . "' id='" . $show_hide . "_b'>
                        <textarea id='FF_$_a[0]' name='FF_$_a[0]' rows='$rows'><?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?></textarea>
                    </div>";
                    break;
                case "select_same": // Beschreibung und feld Nebeneinander
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_30' style='display: flex; flex-wrap: nowrap;white-space: nowrap;'>
                        <span id='SP_$_a[0]' class='desc_f' " . $indent_str . ">$desc_0</span>&nbsp;
                        <select $required id='FF_$_a[0]' name='FF_$_a[0]'  onchange='follow_select(this)'>";
                    $opt_str_a = explode("|", $_a[3]);
                    if ($desc_1) $opt_str = "<option value=''>$desc_1</option>";
                    else $opt_str = "<option value=''></option>";
                    foreach ($opt_str_a as $val) $opt_str .= "<option value='$val' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '$val') echo 'selected'; ?>>" . trim($val) . "</option>";
                    $tmp_str .= $opt_str . "
                        </select>
                    </div>";
                    break;
                case "date_same": // Beschreibung und feld Nebeneinander
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_30' style='display: flex; flex-wrap: nowrap;white-space: nowrap;'>
                        <span id='SP_$_a[0]' class='desc_f'>$desc_0</span>&nbsp;
                        <input $required type='date' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" placeholder='$desc_1'>
                    </div>";
                    break;
                case "select":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_a" . $medic . $onetime . "' id='" . $show_hide . "_a'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div>
                    </div>
                    <div class='col_b" . $medic . $onetime . "' id='" . $show_hide . "_b'>
                        <select $required id='FF_$_a[0]' name='FF_$_a[0]'  onchange='follow_select(this)'>";
                    $opt_str_a = explode("|", $_a[3]);
                    if ($desc_1) $opt_str = "<option value=''>$desc_1</option>";
                    else $opt_str = "<option value=''></option>";
                    foreach ($opt_str_a as $val) $opt_str .= "<option value='$val' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '$val') echo 'selected'; ?>>" . trim($val) . "</option>";
                    $tmp_str .= $opt_str . "
                        </select>
                    </div>";
                    break;
                case "radio_cb":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_a" . $medic . $onetime . "' id='" . $show_hide . "_a'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div>
                    </div>
                    <div class='col_b" . $medic . $onetime . "' id='" . $show_hide . "_b'  style='text-align:center'>
                        <div id='cbm_$_a[0]'>
                            <input data-rcb='$_a[0]' $required class='sim_hide' type='text' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo $" . "form_data_a[$_a[0]] ?? ''; ?>\"  onchange='follow_select(this)'>
                            <label class='custom-checkbox-wrapper'><span id='CB_$_a[0]_Ja' class='custom-checkbox'></span> <span class='custom-checkbox-label'>Ja</span></label>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <label class='custom-checkbox-wrapper'><span id='CB_$_a[0]_Nein' class='custom-checkbox'></span> <span class='custom-checkbox-label'>Nein</span></label>
                        </div>
                    </div>";
                    break;

                case "radio_cb_30":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_30" . $medic . $onetime . "' id='" . $show_hide . "_a'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div>
                    </div>
                    <div class='col_30" . $medic . $onetime . "' id='" . $show_hide . "_b'  style='text-align:center'>
                        <div id='cbm_$_a[0]'>
                            <input data-rcb='$_a[0]' $required class='sim_hide' type='text' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo $" . "form_data_a[$_a[0]] ?? ''; ?>\"  onchange='follow_select(this)'>
                            <label class='custom-checkbox-wrapper'><span id='CB_$_a[0]_Ja' class='custom-checkbox'></span> <span class='custom-checkbox-label'>Ja</span></label>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <label class='custom-checkbox-wrapper'><span id='CB_$_a[0]_Nein' class='custom-checkbox'></span> <span class='custom-checkbox-label'>Nein</span></label>
                        </div>
                    </div>";
                    break;
                case "date_30":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col_30'><input $required type='date' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" placeholder='$desc_1'></div>";
                    break;

                case "select_col":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col" . $medic . $onetime . "'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div>
                    </div>
                    <div class='col" . $medic . $onetime . "'>
                        <select $required id='FF_$_a[0]' name='FF_$_a[0]'  onchange='follow_select(this)'>";
                    $opt_str_a = explode("|", $_a[3]);
                    if ($desc_1) $opt_str = "<option value=''>$desc_1</option>";
                    else $opt_str = "<option value=''></option>";
                    foreach ($opt_str_a as $option) {
                        $val = $option;
                        if ($val == '<hr>') $val = '';
                        $opt_str .= "<option value='$val' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '$val') echo 'selected'; ?>>" . trim($option) . "</option>";
                    }
                    $tmp_str .= $opt_str . "</select>
                    </div>";
                    break;
                case "select_col_30_30_30":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_30'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div>
                    </div>
                    <div class='col_30' style='color:gray'>
                        <span id='isp_FF_$_a[0]'></span>
                    </div>
                    <div class='col_30'>
                        <select $required id='FF_$_a[0]' name='FF_$_a[0]'  onchange='follow_select(this)'>";
                    $opt_str_a = explode("|", $_a[3]);
                    if ($desc_1) $opt_str = "<option value=''>$desc_1</option>";
                    else $opt_str = "<option value=''></option>";
                    foreach ($opt_str_a as $option) {
                        $val = $option;
                        if ($val == '<hr>') $val = '';
                        $opt_str .= "<option value='$val' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '$val') echo 'selected'; ?>>" . trim($option) . "</option>";
                    }
                    $tmp_str .= $opt_str . "</select>
                    </div>";
                    break;
                case "select_col_30_30_30_text":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_30'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div>
                    </div>
                    <div class='col_30' style='color:gray'>
                        <span id='isp_FF_$_a[0]'></span>
                    </div>
                    <div class='col_30'>
                       <input data-fg='$fg' $required type='text' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" placeholder='$desc_1'>
                    </div>";
                    break;
                case "select_mt":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_a" . $medic . $onetime . "' id='" . $show_hide . "_a'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div>
                    </div>
                    <div class='col_b" . $medic . $onetime . "' id='" . $show_hide . "_b'>
                        <select id='mts_$_a[0]' name='mts_$_a[0]'>";
                    $opt_str_a = explode("|", $_a[3]);
                    if ($desc_1) $opt_str = "<option value=''>$desc_1</option>";
                    else $opt_str = "<option value=''></option>";
                    foreach ($opt_str_a as $val) $opt_str .= "<option value='$val' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '$val') echo 'selected'; ?>>" . trim($val) . "</option>";
                    $tmp_str .= $opt_str . "</select>
                        <input type='hidden' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\"><ul id='chosen_$_a[0]' class='cosen_select'></ul>
                        
                    </div>
                    ";
                    break;
                case "select_1":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col" . $medic . $onetime . "'>
                        <div class='desc_f'>$desc_0</div>
                        <select $required  id='FF_$_a[0]' name='FF_$_a[0]'  onchange='follow_select(this)'>";
                    $opt_str_a = explode("|", $_a[3]);
                    if ($desc_1) $opt_str = "<option value=''>$desc_1</option>";
                    else $opt_str = "<option value=''></option>";
                    foreach ($opt_str_a as $val) $opt_str .= "<option value='$val' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '$val') echo 'selected'; ?>>$val</option>";
                    $tmp_str .= $opt_str . "</select>
                    </div>";
                    break;
                case "select_1_col_30":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_30" . $medic . $onetime . "'>
                        <div class='desc_f'>$desc_0</div>
                            <select $required  id='FF_$_a[0]' name='FF_$_a[0]'  onchange='follow_select(this)'>";
                    $opt_str_a = explode("|", $_a[3]);
                    if ($desc_1) $opt_str = "<option value=''>$desc_1</option>";
                    else $opt_str = "<option value=''></option>";
                    foreach ($opt_str_a as $val) $opt_str .= "<option value='$val' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '$val') echo 'selected'; ?>>$val</option>";
                    $tmp_str .= $opt_str . "</select>
                        </div>
                    </div>";
                    break;
                case "select_1_col_70":
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_70" . $medic . $onetime . "'>
                        <div class='desc_f'>$desc_0</div>
                            <select $required  id='FF_$_a[0]' name='FF_$_a[0]'  onchange='follow_select(this)'>";
                    $opt_str_a = explode("|", $_a[3]);
                    if ($desc_1) $opt_str = "<option value=''>$desc_1</option>";
                    else $opt_str = "<option value=''></option>";
                    foreach ($opt_str_a as $val) $opt_str .= "<option value='$val' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '$val') echo 'selected'; ?>>$val</option>";
                    $tmp_str .= $opt_str . "</select>
                        </div>
                    </div>";
                    break;
                case "select_25":
                    $opt_str_a = explode("|", $_a[3]);
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_25" . $medic . $onetime . "'>
                        <div class='desc_f_25' style='margin-top:4px'>$desc_0</div>
                            <select style='max-width:110px;' id='FF_$_a[0]' name='FF_$_a[0]'  onchange='follow_select(this)'>";
                    $opt_str_a = explode("|", $_a[3]);
                    if ($desc_1) $opt_str = "<option value=''>$desc_1</option>";
                    else $opt_str = "<option value=''></option>";
                    foreach ($opt_str_a as $val) $opt_str .= "<option value='$val' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '$val') echo 'selected'; ?>>$val</option>";
                    $tmp_str .= $opt_str . "</select>
                        </div>
                    </div>";
                    break;
                case "select_25_d":
                    $opt_str_a = explode("|", $_a[3]);
                    $javcscript = "";
                    if ($opt_str_a[0] == 'simpleyear') {
                        $javcscript = "<script>fillYearSelect('FF_$_a[0]', '" . $opt_str_a[1] . "', '" . $opt_str_a[2] . "', " . $opt_str_a[3] . ", " . $opt_str_a[4] . ", <" . "?php echo $" . "form_data_a[$_a[0]] ?? ''; ?>);</script>";
                    }
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_25" . $medic . $onetime . "'>
                        <div class='desc_f_25' style='margin-top:4px'>$desc_0</div>
                            <select id='FF_$_a[0]' name='FF_$_a[0]'  onchange='follow_select(this)'></select>
                        </div>
                    </div>";
                    $tmp_str .= $javcscript;
                    break;
                case "select_2":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col'><div class='desc_f'>$desc_0</div><select id='FF_$_a[0]' name='FF_$_a[0]'  onchange='follow_select(this)'>";
                    $opt_str_a = explode("|", $_a[3]);
                    if ($desc_1) $opt_str = "<option value=''>$desc_1</option>";
                    else $opt_str = "<option value=''></option>";
                    foreach ($opt_str_a as $val) $opt_str .= "<option value='$val' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '$val') echo 'selected'; ?>>$val</option>";
                    $tmp_str .= $opt_str . "</select></div>";
                    break;
                case "text_222":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col'><div class='desc_f'>$desc_0</div><input data-fg='$fg' $required type='text' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" placeholder='$desc_1'></div>";
                    break;
                case "pair_numb_sel_1":
                    $opt_str_a = explode("|", $_a[3]);
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col_a'  id='" . $show_hide . "_a'>
                        <div class='desc_f' " . $indent_str . ">$desc_0</div>
                    </div>
                    <div class='col_b' style='display: flex; flex-wrap: nowrap;white-space: nowrap;' id='" . $show_hide . "_b'>
                        <input $required type='number' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\" min='$opt_str_a[0]' max='$opt_str_a[1]' step='$opt_str_a[2]' placeholder='$desc_1'>";
                    break;
                case "pair_text_sel_2":
                    $tmp_str .= "<select id='FF_$_a[0]' name='FF_$_a[0]'  onchange='follow_select(this)'>";
                    $opt_str_a = explode("|", $_a[3]);
                    if ($desc_1) $opt_str = "<option value=''>$desc_1</option>";
                    else $opt_str = "<option value=''></option>";
                    foreach ($opt_str_a as $val) $opt_str .= "<option value='$val' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '$val') echo 'selected'; ?>>$val</option>";
                    $tmp_str .= $opt_str . "</select></div>";
                    break;

                case "fileselect":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col'><div class='desc_f'>$_a[2]</div><select id='FF_$_a[0]' name='FF_$_a[0]' onchange='follow_select(this)'>";
                    $opt_str = "<option value=''></option>";
                    $opt_str .= "<" . "?php echo get_option_files($" . "db,'" . $_a[3] . "')?>";
                    $tmp_str .= $opt_str . "</select></div>";
                    break;
                case "radio":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col'><div class='desc_f' " . $indent_str . ">$desc_0</div></div><div class='col'>";
                    $radio_str_a = explode("|", $_a[3]);
                    $id_count = 0;
                    $radio_str = "";
                    foreach ($radio_str_a as $val) {
                        $val_val = str_replace("<br>", " ", $val);
                        $radio_str .= "<label for='FF_$_a[0]_" . ($id_count) . "' class='radio-wrapper'><input class='radio-input' type='radio' id='FF_$_a[0]_" . ($id_count++) . "' name='FF_$_a[0]' value='$val_val' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '$val_val') echo 'checked'; ?> onchange='follow_select(this)'><span class='radio-custom'></span>$val</label>";
                    }
                    $tmp_str .= $radio_str . "</div>";
                    break;
                case "radio_1":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col'><table class='table-70-30'><tr><td><div class='desc_f'>$desc_0</div></td><td>";
                    $radio_str_a = explode("|", $_a[3]);
                    $id_count = 0;
                    $radio_str = "";
                    foreach ($radio_str_a as $val) {
                        $val_val = str_replace("<br>", " ", $val);
                        $radio_str .= "<input type='radio' id='FF_$_a[0]_" . ($id_count++) . "' name='FF_$_a[0]' value='$val_val' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '$val_val') echo 'checked'; ?> onchange='follow_select(this)'> $val";
                    }
                    $tmp_str .= $radio_str . "</td></tr></table></div>";
                    break;
                case "file":
                    $tmp_str .= "\n\t\t\t\t\t
                        <div class='col' style='width:100%'>
                            <div class='desc_f'>$_a[2]</div>
                                <div id='src_$_a[0]'></div>
                                <div><input type='text' id='FF_" . $_a[0] . "' name='FF_" . $_a[0] . "' style='display:none' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\"></div>
                                <div id='da_$_a[0]' class='drop-area' data-drop-area><input type='file' id='FF_$_a[0]_upl' name='FF_$_a[0]_upl[]' multiple></div>
                                <button id='button_$_a[0]' type='button'  class='filedict_button'>anzeigen/ ausblenden</button><div id='info_$_a[0]' class='filedict_info_box' style='display: none;'>
                        </div>";
                    break;
                case 'visual_range':
                    $opt_str_a = explode("|", $_a[3]);
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col' style='width:100%' id='" . $show_hide . "'>
                        <div class='desc_f'>$_a[2]</div>
                        <div class='slider-container'>
                            <label for='$_a[0]_wertSchieberegler' class='slider-label start-label'>" . $opt_str_a[0] . "</label>
                            <input class='wertSchieberegler' type='range' id='$_a[0]_wertSchieberegler' min='0' max='10' value=0 step='1'>
                            <label for='$_a[0]_wertSchieberegler' class='slider-label end-label'>" . $opt_str_a[1] . "</label>
                            <span style='position: absolute;left:42.9%;background-color:yellow;color:green;padding.right:2px;' id='wertSchieberegler_display_$_a[0]'>0</span>
                        </div>
                    </div>
                    <input type='hidden' id='FF_" . $_a[0] . "' name='FF_" . $_a[0] . "' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\">
                    <script>
                        initSliderWithHiddenField('<?php echo $_a[0]; ?>', 10);
                    </script>";
                    break;
                case 'visual_range_100':
                    $opt_str_a = explode("|", $_a[3]);
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col' style='width:100%' id='" . $show_hide . "'>
                        <div class='desc_f'>$_a[2]</div>
                        <div class='slider-container'>
                            <label for='$_a[0]_wertSchieberegler' class='slider-label start-label'>" . $opt_str_a[0] . "</label>
                            <input class='wertSchieberegler' type='range' id='$_a[0]_wertSchieberegler' min='0' max='100' value=0 step='1'>
                            <label for='$_a[0]_wertSchieberegler' class='slider-label end-label'>" . $opt_str_a[1] . "</label>
                            <span style='position: absolute;left:42.9%;background-color:yellow;color:green;padding.right:2px;' id='wertSchieberegler_display_$_a[0]'>0</span>
                        </div>
                    </div>
                    <input type='hidden' id='FF_" . $_a[0] . "' name='FF_" . $_a[0] . "' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\">
                    <script>
                        initSliderWithHiddenField('<?php echo $_a[0]; ?>',100);
                    </script>";
                    break;
                case "thumb":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col'><div class='desc_f'>$_a[2]</div><div id='src_$_a[0]'></div><input type='text' id='FF_" . $_a[0] . "' name='FF_" . $_a[0] . "' style='display:none'></div><div id='da_$_a[0]' class='drop-area' data-drop-area><input type='file' id='FF_$_a[0]_upl' name='FF_$_a[0]_upl[]' multiple></div><button id='button_$_a[0]' type='button'  class='filedict_button'>anzeigen/ ausblenden</button><div id='info_$_a[0]' class='filedict_info_box' style='display: none;'></div></div>";
                    break;
                case "info_0":
                    $tmp_str .= "\n\t\t\t\t\t<div class='" . $_SESSION['col_type'] . $medic . $onetime . "' style='height:100%'><div class='desc_f'>$desc_0</div></div>";
                    break;
                case "info_30":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col_b" . $medic . $onetime . "' " . $indent_str . "><div class='desc_f'>$desc_0</div></div>";
                    break;
                case "info_60":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col_60" . $medic . $onetime . "' " . $indent_str . " id='" . $show_hide . "'><div class='desc_f'>$desc_0</div></div>";
                    break;
                case "info_30_c":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col_b " . $medic . $onetime . "' " . $indent_str . "><div class='desc_f'>$desc_0</div></div>";
                    break;
                case "info_30_c_l":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col_b bottomline" . $medic . $onetime . "' " . $indent_str . "><div class='desc_f'>$desc_0</div></div>";
                    break;
                case "info_wrapped":
                    $tmp_str .= "\n\t\t\t\t\t<div class='" . $medic . "' style='display:flex;flex-wrap: wrap;text-align:center;width:25%;max-width:25%''>$desc_0</div>";
                    break;
                case "start_col_wrap":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col_a" . $medic . $onetime . "' style='display: flex; flex-wrap: nowrap;white-space: nowrap;margin:0; padding:2px'>";
                    break;
                case "dbl_select_wrapped":
                    $tmp_str .= "
                    <div style='display:flex;flex-wrap: wrap;text-align:center;width:25%;max-width:25%'>
                        <select $required id='FF_$_a[0]' name='FF_$_a[0]'  onchange='follow_select(this)'>";
                    $opt_str_a = explode("|", $_a[3]);
                    if ($desc_1) $opt_str = "<option value=''>$desc_1</option>";
                    else $opt_str = "<option value=''></option>";
                    foreach ($opt_str_a as $val) $opt_str .= "<option value='$val' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '$val') echo 'selected'; ?>>" . trim($val) . "</option>";
                    $tmp_str .= $opt_str . "
                        </select>
                    </div>&nbsp;";
                    break;
                case "stop_col_wrap":
                    $tmp_str .= "</div>";
                    break;
                default:
            }
        } else {
            $tmp_str = "";
            $_a[2] = str_replace("&nbsp,", "&nbsp;", $_a[2]);
            $_a[2] = str_replace("@semikolon@", ";", $_a[2]);
            $_a[2] = preg_replace('/\[.*?\]/', '', $_a[2]);
            switch ($_a[1]) {
                case "fieldset":
                    $tmp_str .= "\n\t\t\t<fieldset ><legend>$_a[2]</legend><fs_cont/>\n\t\t\t</fieldset>";
                    break;
                case "start_fieldset":
                    $tmp_str .= "\n\t\t\t<fieldset><legend>$_a[2]</legend>";
                    break;
                case "stop_fieldset":
                    $tmp_str .= "\n\t\t\t</fieldset>";
                    break;
                case "start_block":
                    $tmp_str .= "\n\t\t\t<div id='B_$_a[3]_$_a[2]' class='col' style='display:none'>";
                    break;
                case "end_block":
                    $tmp_str .= "\n\t\t\t</div>";
                    break;
                case "start_row":
                    $tmp_str .= "\n\t\t\t\t<div class='row'>";
                    break;
                case "start_hidden_row":
                    $tmp_str .= "\n\t\t\t\t<div class='row' style='visibility: collapse;'>";
                    break;
                case "end_row":
                    $tmp_str .= "\n\t\t\t\t</div>";
                    break;
                case "new_row":
                    $tmp_str .= "\n\t\t\t\t</div>\n\t\t\t\t<div class='row'>";
                    break;
                case "newline":
                    $tmp_str .= "\n\t\t\t\t<br>";
                    break;
                case "info":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col infotext'>$_a[2]</div>";
                    break;
                case "text":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col'><div class='desc_f'>$_a[2]</div><input type='$_a[1]' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\"></div>";
                    break;
                // case "text":
                //     $tmp_str .= "\n\t\t\t\t\t<div class='col'><div class='desc_f'>$_a[2]</div><input type='$_a[1]' id='FF_$_a[0]' name='FF_$_a[0]' style='display:none'></div>";
                //     break;
                case "date":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col'><div class='desc_f'>$_a[2]</div><input type='$_a[1]' id='FF_$_a[0]' name='FF_$_a[0]' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\"></div>";
                    break;
                case "checkbox":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col'><div class='desc_f'>$_a[2]</div><input type='hidden' id='FF_$_a[0]' name='FF_$_a[0]' value='0'><input type='$_a[1]' id='FF_$_a[0]' name='FF_$_a[0]' value='1' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '1') echo 'checked'; ?>></div>";
                    break;
                case "checkbox_l":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col'><div class='desc_f'><input type='hidden' id='FF_$_a[0]' name='FF_$_a[0]' value='0'><input type='checkbox' id='FF_$_a[0]' name='FF_$_a[0]' value='1'> $_a[2]</div></div>";
                    break;
                case "textarea":
                    if ($_a[3]) $rows = $_a[3];
                    else $rows = 1;
                    $tmp_str .= "\n\t\t\t\t\t
                    <div class='col'>
                        <div class='desc_f'>$_a[2]</div>
                        <textarea id='FF_$_a[0]' name='FF_$_a[0]' rows='$rows'><?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?></textarea>
                    </div>";
                    break;

                case "select":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col'><div class='desc_f'>$_a[2]</div><select id='FF_$_a[0]' name='FF_$_a[0]' onchange='follow_select(this)'>";
                    $opt_str_a = explode("|", $_a[3]);
                    $opt_str = "<option value='' ></option>";
                    foreach ($opt_str_a as $val) $opt_str .= "<option value='$val' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '$val') echo 'selected'; ?>>$val</option>";
                    $tmp_str .= $opt_str . "</select></div>";
                    break;
                case "fileselect":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col'><div class='desc_f'>$_a[2]</div><select id='FF_$_a[0]' name='FF_$_a[0]' onchange='follow_select(this)'>";
                    $opt_str = "<option value=''></option>";
                    $opt_str .= "<" . "?php echo get_option_files($" . "db,'" . $_a[3] . "')?>";
                    $tmp_str .= $opt_str . "</select></div>";
                    break;
                case "radio":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col'><div class='desc_f'>$_a[2]</div>";
                    $radio_str_a = explode("|", $_a[3]);
                    $id_count = 0;
                    $radio_str = "";
                    foreach ($radio_str_a as $val) {
                        $val_val = str_replace("<br>", "", $val);
                        $radio_str .= "<input type='radio' id='FF_$_a[0]_" . ($id_count++) . "' name='FF_$_a[0]' value='$val_val' <?php if (($" . "form_data_a[$_a[0]] ?? '') == '$val_val') echo 'checked'; ?>> $val";
                    }
                    $tmp_str .= $radio_str . "</div>";
                    break;
                case "file":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col'><div class='desc_f'>$_a[2]</div><div id='src_$_a[0]'></div><input type='text' id='FF_" . $_a[0] . "' name='FF_" . $_a[0] . "' style='display:none' value=\"<?php echo htmlspecialchars($" . "form_data_a[$_a[0]] ?? ''); ?>\"></div><div id='da_$_a[0]' class='drop-area' data-drop-area><input type='file' id='FF_$_a[0]_upl' name='FF_$_a[0]_upl[]' multiple></div><button id='button_$_a[0]' type='button'  class='filedict_button'>anzeigen/ ausblenden</button><div id='info_$_a[0]' class='filedict_info_box' style='display: none;'></div></div>";
                    break;
                case "thumb":
                    $tmp_str .= "\n\t\t\t\t\t<div class='col'><div class='desc_f'>$_a[2]</div><div id='src_$_a[0]'></div><input type='text' id='FF_" . $_a[0] . "' name='FF_" . $_a[0] . "' style='display:none'></div><div id='da_$_a[0]' class='drop-area' data-drop-area><input type='file' id='FF_$_a[0]_upl' name='FF_$_a[0]_upl[]' multiple></div><button id='button_$_a[0]' type='button'  class='filedict_button'>anzeigen/ ausblenden</button><div id='info_$_a[0]' class='filedict_info_box' style='display: none;'></div></div>";
                    break;
                default:
            }
        }
        return $tmp_str;
    }

    function csvToArray(string $filename, string $delimiter = ';')
    {
        $data = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) $data[] = $row;
            fclose($handle);
        }
        return $data;
    }

    function parse_template($template_file, $file_str_a, $form_name, $path = "forms_test/")
    {
        $html_str = "";
        $tmp_str = "";

        foreach ($file_str_a as $row_a) {
            // if ($row_a[1] == 'fieldset') {
            //     if (trim($tmp_str)){
            //         $html_str = str_replace("<fs_cont/>", $tmp_str, $html_str); 
            //         $tmp_str = "";
            //     }    
            //     $html_str .= build_element($row_a);
            // } else {
            //     $tmp_str .= build_element($row_a);
            // }      
            $html_str .= build_element($row_a);
        }
        //$html_str = str_replace("<fs_cont/>", $tmp_str, $html_str); 
        $t_file = fopen($template_file, "r") or die("Unable to open file!");
        $template_str =  fread($t_file, filesize($template_file));
        fclose($t_file);
        $pattern = '/<creator>(.*?)<\/creator>/s';
        preg_match($pattern, $template_str, $matches);
        if (isset($matches[1])) {
            $html_str = str_replace($matches[1], $html_str, $template_str);
            $form_file = fopen(FORMPATH . $path . $form_name . ".php", "w") or die("Unable to open file!");
            fwrite($form_file, $html_str);
            fclose($form_file);
            // echo FORMPATH . $path . $form_name . ".php";
        } else {
            echo "Kein Creator-Bereich gefunden.";
        }
        return $html_str;
    }

    function list_dir($verzeichnis)
    {
        $list_a[] = "beispiel";
        if (is_dir($verzeichnis)) {
            $dateien = scandir($verzeichnis);
            foreach ($dateien as $datei) {
                if ($datei != "." && $datei != "..") {
                    if ($datei != "beispiel.csv") $list_a[] = str_replace(".csv", "", $datei);
                    // $pfad = $verzeichnis . "/" . $datei;
                    // if (is_dir($pfad)) {
                    //     echo "Verzeichnis: " . $pfad . "<br>";
                    //     list_dir($pfad); 
                    // } else {
                    //     echo "Datei: " . $pfad . "<br>";
                    // }
                }
            }
        } else {
            echo "Das angegebene Verzeichnis existiert nicht.";
        }
        return $list_a;
    }

    function str_to_array($file_str)
    {
        $zeilen = explode("\n", $file_str); // String in Zeilen aufteilen
        $file_str_a = [];
        foreach ($zeilen as $zeile) {
            if (trim($zeile) === '') {
                continue;
            }
            $spalten = explode(";", trim(chop($zeile)));
            $file_str_a[] = $spalten;
        }
        return  $file_str_a;
    }

    function clean_str($str_in)
    {
        $cleaned = preg_replace('/[^a-zA-Z0-9_-]/', '', $str_in);
        return $cleaned;
    }


    function get_table_columns($db, $tableName)
    {
        $columns = [];
        try {
            if (DB == "MariaDB") $query = "SELECT COLUMN_NAME as name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '" . $tableName . "';";
            else $query = "PRAGMA table_info($tableName)";
            $stmt = $db->query($query);
            $columnInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($columnInfo as $column) {
                $columns[] = $column['name'];
            }
        } catch (PDOException $e) {
            error_log("Error accessing database: " . $e->getMessage());
            return []; // Return empty array on error
        } finally {
        }
        return $columns;
    }



    // Get defined forms 
    define("FORMPATH", $_SESSION["FS_ROOT"] . $_SESSION["PROJECT_PATH"]);


    $example_str    = "id;type;name;\n;fieldset;Überschriften Block 1;\n1;text;Textfeld 1;\n2;radio;Eine-Auswahl-Boxen 1;Auswahl 1<br>|Auswahl 2<br>|Auswahl 3<br>\n3;text;Textfeld 2;\n4;textarea;Erweiterbares Textfeld;3\n5;checkbox;Checkbox 1;\n;fieldset;Überschriften Block 2;\n6;file;Upload-Feld 1;\n7;select;Select Feld;a|b|c|d|what ever\n8;date;Textfeld 5;\n9;text;Textfeld 6;\n10;checkbox;Checkbox 2;\n11;checkbox;Checkbox 3;\n;fieldset;Überschriften Block 2;\n12;radio;Eine-Auswahl-Boxen 2;Auswahl x|Auswahl y\n;info;Beliebiger Informationsstext - ruhig ausführlich - auch mit <strong>html-code fett</strong> oder <font style='color:red'>gefärbt</font>.;";
    $form_name      = "";
    $file_str       = "";

    $fg = $_REQUEST['fg'] ?? "";
    $forms_a = get_fcid_a(get_query_data($db, 'forms_99901', $query_add = 'fid=99901002 OR fid=99901001'));
    foreach ($forms_a as $key => $val)
        if ($val[99901001] == $fg) $form_name = $val[99901002];


    if ($_REQUEST) {

        $form_name = trim(chop($form_name));

        // WINBOX
        // echo "<script> try{ window.parent.testform.close();} catch {};</script>";
        # list($fields_a, $fid_a) = get_field_definiton($db);
        if ($fg != 0 && $fg != "") {
            if (file_exists(FORMPATH . "forms_def/$form_name.csv")) {
                $file_str = file_get_contents(FORMPATH . "forms_def/$form_name.csv");
            } else {
                echo "CSV File wurde angelegt";
                $file_str = file_put_contents(FORMPATH . "forms_def/$form_name.csv", "");
                //$file_str = file_put_contents(FORMPATH . "forms_def/$form_name.csv", "\xEF\xBB\xBF");
            }
        }
        if ($_POST && $form_name && $fg) {

            $file_str = $_REQUEST['code_test'] ?? "";
            $file_str = trim(chop($file_str));

            $file_str_a = str_to_array($file_str);
            write_field_definition($db, $fg, $form_name, $file_str_a, "");

            $form_file = fopen(FORMPATH . "forms_def/" . $form_name . ".csv", "w") or die("Unable to open file!");
            fwrite($form_file, trim(chop($file_str)));
            fclose($form_file);
            $html_str = parse_template($template_file, $file_str_a, $form_name);
            if (file_exists(FORMPATH . "forms_test/" . $form_name . ".php"))
                // WINBOX
                // echo "<script> 
                //         window.parent.window_boxes['" . $form_name . "']['url'] = 'forms_test/$form_name.php';        
                //         window.parent.testform = window.parent.window_box_main('" . $form_name . "', {}, true); 
                //     </script>";
                if (isset($_POST['activate']) && $_POST['activate']) {
                    $html_str = parse_template($template_file, $file_str_a, $form_name, "forms/");
                }
        }
    }

    if (empty($fg) || $fg == 0 || $fg == "") {
        echo "Nur Formulare sind konfigurierbar!";
        exit;
    }
    ?>

    <form name="create" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
        <div style='margin-bottom: 5px;display: flex; align-items: center; gap: 10px; white-space: nowrap;'>
            <strong>Form Name:</strong> <input type='text' name='form_name' value='<?php echo $form_name ?>'>
            <strong>Form Nr.:</strong> <input name='fg' id='fg' value='<?php echo $fg ?>' size='5'> &nbsp;&nbsp;&nbsp;
            <!--<strong>Bild View:</strong> <input name='view' id='view' size='1'> &nbsp;&nbsp;&nbsp; -->
            <strong>Activate:</strong> <input name='activate' size='1' value='1'> <input type="submit" value="Testen / Speichern">
        </div>
        <textarea name="code_test" id="code_test" width='100%' style='height:400px'><?php echo trim(chop($file_str)); ?></textarea>
        <div style='margin-bottom: 5px;display: flex; align-items: center; gap: 10px; white-space: nowrap;'><strong>Template:</strong> <input type='text' name='template_file' value='<?php echo $template_file ?>' size='50%'> <strong>CSS:</strong> <input size='50%' name='css' value='<?php echo $css ?>' size='5'></div>
    </form>
    <script>
        code_test = document.getElementById('code_test');
        if (code_test) {
            code_test.style.height = (window.innerHeight - 100) + 'px';
        }
    </script>
</body>

</html>