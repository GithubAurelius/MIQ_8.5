<?php
session_start();
# if (!isset($_SESSION['uid'])) 

//  echo "<script>
//             console.log('XXX');
//             //try { window.top.document.location.href = '/'; } catch (err) {}   
//             //try { document.location.href = '/'; } catch (err) {}    
//         </script>";

require_once $_SESSION['INI-PATH'];

function get_form_fid_def($db, $fg, $slq_add = "")
{
    $query = "SELECT fid,fname,shortname FROM forms_definition WHERE fg=:fg" . $slq_add . ";";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':fg', $fg);
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $desc_a = [];
    foreach ($res as $row) {
        if ($row['shortname'] != '') $desc_a["F_" . $row['fid']] = $row['shortname'];
        else $desc_a["F_" . $row['fid']] = htmlspecialchars($row['fname']); // 'name' enthält den Spaltennamen
    }
    
    return $desc_a;
}

$_SESSION['performance'] = [];
$_SESSION['temp_info']   = [];


$prepare_page = 1;

// check if document is standalone or window_box
$num = $_REQUEST['num'] ?? 0; 
$no_wbox = $_REQUEST['nowbox'] ?? 0;
$data_def_str = $_GET['data_def_str'] ?? "";
$query_global_str = $_GET['query_global_str'] ?? "";

// if ($query_global_str) $query_global_a = json_decode($query_global_str, true);
// else $query_global_a = [];


// '10,20,90,91,92,93,94,95,96,10005020';

$work_mode = $_REQUEST['work_mode'] ?? "E";

 
if ($work_mode) $work_mode_a = explode('-',$work_mode);
// echo "<pre>"; echo print_r($_REQUEST); echo "</pre>";exit;

if (empty($num) && empty($data_def_str) && empty($no_wbox)) {
    echo "Fehlerhafte Listen-Initialiserung"; 
    exit;
}

# ?nowbox=1&fg=10003&form=Patient&work_mode=A-E-D-N-F
# http://192.168.178.94/MIQ_8.3/modules/listing_form_native/listing_prepare.php?nowbox=1&fg=10003&form=Patient&work_mode=A-E-D-N-F&
# MIQ_PATH . $data_a[$key]['F_99901015'] . '?fg=' . $modul_fg . '&form=' . $form_name . '&limit='.$limit.'&work_mode=' . $work_mode . $url_query . '"};';

if ($num || $no_wbox){
    $data_def_a = [];
    $data_def_a['num'] = $num ?? 1000;
    $data_def_a['fg'] = $_REQUEST['fg'] ?? "";
    $data_def_a['table'] = 'forms_'.$data_def_a['fg'];
    $data_def_a['key'] = 'fcid';
    $data_def_a['fid_str'] = $_REQUEST['fid_str'] ?? "10,20"; // ,102600,102700
    $data_def_a['form_name'] = $_REQUEST['form_name'] ?? "";
    $data_def_a['query_global_a'] = $query_global_str ? json_decode($query_global_str, true) : [];
    $data_def_a['limit'] = $_REQUEST['limit'] ?? 10;;
    $data_def_a['desc_a'] = get_form_fid_def($db, $data_def_a['fg'], " AND fid IN (" . str_replace('fcid','-1',$data_def_a['fid_str']) . ")");
    $data_def_a['offset'] = '0';
    $data_def_a['count_all'] = 0;
    $data_def_a['quick_search_str'] = '';
    $data_def_a['order_a'] = [];
    $data_def_a['query_a'] = [];
    // $data_def_a['query_a'] = $query_a;
    $data_def_a['work_mode_a'] = $work_mode ? array_flip($work_mode_a) : [];
    $data_def_a['nowbox'] = $no_wbox;
} else if($data_def_str){
    $data_def_a = json_decode(base64_decode($_GET['data_def_str']), true);
    $data_def_a['offset'] = '0';
    $data_def_a['count_all'] = 0;
    $data_def_a['quick_search_str'] = '';
    $data_def_a['order_a'] = [];
    // $data_def_a['query_a'] = [];
}

// echo "<pre>"; echo print_r($_REQUEST); echo "</pre>";
// exit;

if (file_exists($_SESSION['FS_ROOT'] . $_SESSION['PROJECT_PATH'] . "forms/list_".$data_def_a['fg']."_defs.php"))
    require_once $_SESSION['FS_ROOT'] . $_SESSION['PROJECT_PATH'] . "forms/list_".$data_def_a['fg']."_defs.php";


// echo "<pre>"; echo print_r($data_def_a); echo "</pre>";exit;
$data_def_str = base64_encode(json_encode($data_def_a));
header('Location: listing_form.php?data_def_str='.$data_def_str);

