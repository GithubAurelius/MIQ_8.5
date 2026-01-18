<?php

require_once ENCRYPTION;

function get_option_files($db, $fid)
{
    // $ret_str = "";
    // $opt_a = [];
    // $querstr = "SELECT did,dfilename,dcomment FROM filedict WHERE fid=" . $fid . " ORDER BY dfilename;";
    // $stmt = $db->prepare($querstr);
    // $stmt->execute();
    // $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // foreach ($res as $row) {
    //     $opt_a[$row['did']]['dfilename'] = $row['dfilename'];
    //     $opt_a[$row['did']]['dcomment'] = $row['dcomment'];
    // }
    // foreach ($opt_a as $did => $val_a) $ret_str .= "<option value='" . $did . "'>" . ($val_a['dcomment'] != "" ? $val_a['dcomment'] : $val_a['dfilename']) . "</option>";
    // return $ret_str;
}

function get_form_field($db, $fcid, $fg, $fid)
{
    $query = "SELECT fcont FROM forms_" . $fg . " WHERE fid=" . $fid . " AND fcid=" . $fcid;
    // echo $query;
    $stmt = $db->prepare($query);
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $groesse = $res[0]['fcont'] ?? 0;
    return $groesse;
}

function message_box()
{
    $temp_str = "<div id='messageBoxWrapper' class='hidden'>
    <div class='message-box'>
        <span class='close-button' onclick='closeMessageBox()'>&times;</span>
        <h3>Wichtige Information</h3>
        <p id='message'>xxx</p>
    </div>
    </div>";
    return $temp_str;
}



$fcid = "";
$num = $_REQUEST['num'] ?? 0;
$form_name = $_REQUEST['form_name'] ?? "";
$muid = $_SESSION['uid'];
$param_str = $_REQUEST['param_str'] ?? "";
$opener_num = $_REQUEST['opener_num'] ?? 0;
$fg = $_REQUEST['fg'] ?? 0;
$status = "";

// echo "<pre>"; echo print_r($_REQUEST); echo "</pre>";
// echo "<br><br><br>NUM:".$num;

if ($_POST) {
    $ts = date("Y-m-d H:i:s");
    $fcid = $_POST["fcid"];
    $fg = $_POST["fg"];
    // $param_str = $_POST['param_str'] ?? "";
    $old_form_data_a = [];
    if (!empty($fcid) && $fcid > 0) $old_form_data_a = get_form_data($db, $fg, $fcid);
    $db->beginTransaction();
    $db_audit->beginTransaction();
    foreach ($_POST as $fid => $val) {
        $is_int = is_numeric($fcid) && (int) $fcid == $fcid; // if( preg_match('/^[0-9]+$/', $_GET['id'] )  echo "is int";
        $tA = explode("_", $fid);
        if (!empty($fcid) && $is_int && $tA[0] == "FF")
            ins_or_rep_form($ts, $db, $fg, $fcid, $muid, $tA[1], $val);
    }
    $db->commit();
    $db_audit->commit();
    $did = (int) date("YmdHisv");
    foreach ($_FILES as $fid => $file_in_fid) {
        $tA = explode("_", $fid);
        // Abfrage vorschieben für maximal 999 Dateien
        $file_add_a = [];
        foreach ($_FILES[$fid]['tmp_name'] as $key => $tmpName) {
            $fileName = basename($_FILES[$fid]['name'][$key]);
            if ($fileName) {
                list($targetFile, $fileName, $encodedName, $encrypted) = encrpyt_file(UPLOAD_BASE . UPLOAD_SUB_PATH, $fileName, $tmpName, SECRET);
                if (file_put_contents($targetFile, $encrypted)) {
                    debug("Die Datei $fileName wurde erfolgreich hochgeladen, verschlüsselt und als $encodedName gespeichert.<br>");
                    // ins_or_rep_filedict($ts, $db, ++$did, $muid, $fcid, $tA[1], UPLOAD_SUB_PATH, $fileName, $encodedName);
                    $file_add_a[] = $did;
                } else {
                    debug("Fehler beim Hochladen der Datei $fileName.<br>");
                }
            }
        }
        if ($file_add_a) {
            if ($_POST["FF_" . $tA[1]]) $field_str = $_POST["FF_" . $tA[1]] . "," . implode(",", $file_add_a);
            else  $field_str = implode(",", $file_add_a);
            ins_or_rep_form($ts, $db, $fg, $fcid, $muid, $tA[1], $field_str);
        }
    }
} elseif (isset($_REQUEST["fcid"])) {
    $fcid = $_REQUEST["fcid"];
    if ($fcid == -1) {
        $fcid_ts = date('YmdHis');
        $fcid = (int) ($fcid_ts . substr(microtime(true), 11, 2));
    }
    $is_int = is_numeric($fcid) && (int) $fcid == $fcid;
    if (!$is_int) $fcid = "";
}


$header_info = "ID: " . $fcid;

// if (!$param_str) $param_str = $_REQUEST['param_str'] ?? "";
if ($param_str) {
    $param_str = urldecode(base64_decode($param_str));
    $param_a = json_decode($param_str, true);
    // echo "<br><br><pre>FS:"; echo print_r($param_a); echo "</pre>";

}

// This overwrite everything to call the form - direct acces via index_params
if ($_SESSION['overwrite_navigation'] ?? 0) {
    $fg = $_SESSION['temp_fg'];
    $fcid = $_SESSION['temp_fcid'];
}

$form_data_a = [];
if (!empty($fcid) && $fcid > 0) $form_data_a = get_form_data($db, $fg, $fcid);

// echo "<pre>"; echo print_r($form_data_a); echo "</pre>";
// no echo content, no scripting!!!

$temp_token = $_SESSION['temp_token'] ?? "";
?>

<script>
    // check if needed here: async function fetchDataAndUpdateForm(fcid, fg, fid, fcont) 
</script>