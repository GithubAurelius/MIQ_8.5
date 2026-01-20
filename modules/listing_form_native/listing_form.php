<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function navigate_bar($data_def_a, $sort_fid, $sort_dir, $quick_search_str, $select_filter_a = [])
{
    // $ret_str = "<div style='display: flex;flex: 1;'><div>";
    $ret_str = "<div style='display: grid;grid-template-columns: auto 1fr;'><div>";
    if (isset($data_def_a['work_mode_a']['A'])) { // forward_form(list_type, key_n, key_v, form_name, num, param_a = {})
        // $click_str = "window.parent.forward_form('form', 'fcid', -1, '" . $data_def_a['form_name'] . "',1)";
        $doc_url = $_SESSION['PROJECT_PATH'] . "forms/" . $data_def_a['form_name'] . ".php?fg=" . $data_def_a['fg'] . "&fcid=-1";
        $doc_name = $data_def_a['form_name'];
        // TODO: add as event listener and integrate param_str
        $json_data = '{"num": "555", "title": "' . $doc_name . '", "url": "' . $doc_url . '"}';
        $js_call = "window.top.winbox_url('" . $json_data . "')";
        $click_str = htmlspecialchars($js_call, ENT_QUOTES, 'UTF-8');
        $ret_str .= "<a class='datanav' onclick=\"" . $click_str . "\">➕</a>&nbsp;&nbsp;&nbsp;&nbsp;";
    }
    $ret_str .= "<a title='Ansicht zurücksetzen' class='datanav' onclick=\"window.location.href='listing_prepare.php?data_def_str=." . base64_encode(json_encode($data_def_a)) . "'\">🔰</a>";
    $ret_str .= "<a title='Ansicht aktualisieren (Filter bleiben erhalten)' class='datanav' onclick=\"window.location.reload()\">🔄</a>";

    $ret_str .= "&nbsp;&nbsp;&nbsp;&nbsp;
        <a class='datanav' id='DATANAV_BB'>⏮️</a>
        <a class='datanav' id='DATANAV_B'>◀️</a>
        <a class='datanav' id='DATANAV_F'>▶️</a>
        <a class='datanav' id='DATANAV_FF'>⏭️</a>
        <span id='DATA_INFO' style='color:gray'></span>";
    $ret_str .= "<span id='dist_nav' style='padding-left:100px'></span>";
    if (isset($data_def_a['work_mode_a']['F'])) $ret_str .= "<a  title='Detailsuche mit Feld-Filter' class='datanav' id='filter_button'>🔍</a>";
    $ret_str .= "
        <span>&nbsp;<input placeholder='Schnellsuche' style='border-radius:3px;background-color:transparent;border: solid silver 1px;width:90px' type='text' id='quick_search' name='quick_search' value=\"" . $quick_search_str . "\"></span>
        <span>&nbsp;&nbsp;" . get_select_str("sort_fid", $data_def_a['desc_a'], $sort_fid, "Sortierung", $select_filter_a) . " " . get_select_str("sort_dir", array("ASC" => "A-Z", "DESC" => "Z-A", "ASC*" => "A-Z*", "DESC*" => "Z-A*"), $sort_dir, "↑↓") . "</span>";
    $ret_str .= "</div>";
    return $ret_str;
}

function build_native_table($table_a, $data_def_a, $nav_bar, $show_fcid = 0)
{
    $start = microtime(true);
    if ($show_fcid) $data_def_a['fid_str'] = 'fcid,' . $data_def_a['fid_str'];
    $fid_to_show = explode(',', $data_def_a['fid_str']);
    // echo "<pre>"; echo print_r($fid_to_show); echo "</pre>";
    $tbl_str = "
    <table id='cases'> 
        <thead style='position:sticky; top:0; box-shadow: 0 0.5px 0 dimgray;'>";
    if ($nav_bar) $tbl_str .= "
            <tr><th class='header-main' id='th_navbar' style='display:none;border:0;'>" . $nav_bar . "</th></tr>";
    $tbl_str .= "
            <tr>
                <th valign='top' id='tools' style='width:0;border:0'></th>";
    foreach ($fid_to_show as $fid) $tbl_str .=  "
        <th id='F_" . $fid . "' style='border:0;border-left:solid silver 1px'>
            <label class='filter_label'>    
                <input class='filter_search' id='S_$fid' type='text'>
            </label>
        " . ($data_def_a['desc_a']['F_' . $fid] ?? 'F_' . $fid) . "
        </th>";
    $tbl_str .=  "
            </tr>
        </thead>
        <tbody>";
    foreach ($table_a as $fcid => $fcid2fid_a) {
        $tbl_str .=  "
            <tr id='FCID_" . $fcid . "'>
                <td class='action desc'><div style='white-space: nowrap;'></td>";
        foreach ($fid_to_show as $fid) {
            $fcont = $table_a[$fcid][$fid] ?? "";
            $tbl_str .=  "
                <td>" . $fcont . "</td>";
        }
        $tbl_str .=  "
            </tr>";
    }
    $tbl_str .=  "
    </table>";
    $end = microtime(true);
    $_SESSION['performance']['build_native_table'] = number_format($end - $start, 6, '.', '');
    return $tbl_str;
}

function get_table_data($fcid_a, $data_def_a)
{
    $start = microtime(true);
    $fid_to_show = explode(',', "fcid," . $data_def_a['fid_str']);
    $table_a = [];
    foreach ($fcid_a as $fcid => $fid_a) {
        $table_a[$fcid] = [];
        foreach ($fid_a as $fid => $table_row) {
            $table_a[$fcid][$fid] = "";
            foreach ($table_row as $row_col => $val) {
                if (in_array('fcid', $fid_to_show)) $table_a[$fcid]['fcid'] = $fcid;
                if ($row_col == 'fcont') $table_a[$fcid][$fid] = $val;
            }
        }
    }
    $end = microtime(true);
    $_SESSION['performance']['get_table_data'] = number_format($end - $start, 6, '.', '');
    return $table_a;
}

function build_fcid_a($temp_a)
{
    $start = microtime(true);
    $fcid_a = [];
    $_a = $temp_a[0];
    $sort_a = $temp_a[1];
    foreach ($_a as $item) $fcid_a[$item['fcid']][$item['fid']] = $item;
    $fcid_sorted_a = [];
    foreach ($sort_a as $fcid) $fcid_sorted_a[$fcid] =  $fcid_a[$fcid];
    $fcid_a = $fcid_sorted_a;
    $end = microtime(true);
    $_SESSION['performance']['build_fcid_a'] = number_format($end - $start, 6, '.', '');
    return $fcid_a;
}

function q_execute($db, $query_str, $bind_a, $mode = PDO::FETCH_ASSOC)
{
    // $_SESSION['performance']['q_execute_query'] = $query_str;
    // echo "<br><br>".$query_str;
    $start = microtime(true);
    $temp_a = [];
    $stmt = $db->prepare($query_str);
    if ($bind_a)
        foreach ($bind_a as $bindname => $bindval) $stmt->bindValue($bindname, $bindval);
    $stmt->execute();
    $temp_a = $stmt->fetchAll($mode);
    $unique_fcid_a = [];
    $count = 0;
    if ($mode == PDO::FETCH_ASSOC) {
        foreach ($temp_a as $key => $val_a) {
            $unique_fcid_a[$val_a['fcid']] = 1;
            $count = count($unique_fcid_a);
        }
    } else $count = count($temp_a);
    $end = microtime(true);
    $_SESSION['performance'][$query_str] = number_format($end - $start, 6, '.', '');
    return [$temp_a, array_keys($unique_fcid_a), $count];
}

function navigate($current_offset, $count_all, $limit_lines, $action)
{
    // Maximaler Seitenindex (0-basiert)
    $max_page = max(floor(($count_all - 1) / $limit_lines), 0);

    switch ($action) {
        case 'BB': // Erste Seite
            return 0;
        case 'B':  // Eine Seite zurück
            return max($current_offset - $limit_lines, 0);
        case 'F':  // Eine Seite vor
            return min($current_offset + $limit_lines, $max_page * $limit_lines);
        case 'FF': // Letzte Seite
            return $max_page * $limit_lines;
        default:
            return $current_offset; // wenn ungültig, alten Offset behalten
    }
}

function get_data_by_native_method($db, $data_def_a, $usergroup, $query_a, $order_a, $limit_str = "")
{
    $table = $data_def_a['table'];
    $fid_str = $data_def_a['fid_str'];
    if ($limit_str) $limit_str = "LIMIT " . $data_def_a['offset'] . ", " . $data_def_a['limit'];
    $quick_search_str = $data_def_a['quick_search_str'];

    $start = microtime(true);
    if (!$fid_str) $fid_in_add = "";
    else {
        $fid_in_add = "AND fid IN (" . $fid_str . ")";
    }
    $fcid_a = [];
    $fcid_str = "";

    if ($quick_search_str) {
        $query_str = "SELECT fcid FROM " . $table . " WHERE usergroup=" . $usergroup . " " . $fid_in_add . " AND fcont like :quick_search_str";
        // echo $query_str;
        $bind_a[':quick_search_str'] = '%' . $quick_search_str . '%';
        $temp_a = q_execute($db, $query_str, $bind_a, PDO::FETCH_COLUMN);
        $fcid_a = $temp_a[0];
        if ($temp_a[2] == 0)
            return [[], [], 0];
    }

    // $query_a = [];
    if ($query_a) {
        $i = 0;
        foreach ($query_a as $val_a) {
            if (str_starts_with($val_a[2], '!=')) {
                $val_a[1] = '!=';
                $val_a[2] = ltrim($val_a[2], '!=');
            }
            // Version um auch leere Felder bei fid-Suche anzuzeigen - sehr langsam!
            // if ($val_a[1] == '!=') $compare = "SELECT fcid FROM " . $table . " WHERE fid= " . $val_a[0] . " AND fcont = '".$val_a[2]."'" ;
            if ($val_a[1] == '!=') $compare = "fcont <>'" . $val_a[2] . "'";
            else $compare = "fcont like '" . $val_a[2] . "'";
            // TODO: >,< etc.
            if ($val_a[0] == 'fcid') $query_str = "SELECT fcid FROM " . $table . " WHERE usergroup=" . $usergroup . " AND fcid like '" . $val_a[2] . "'";
            // Version um auch leere Felder bei fid-Suche anzuzeigen - sehr langsam!
            // elseif ($val_a[1] == '!=') $query_str = "SELECT fcid FROM " . $table . " WHERE usergroup=" . $usergroup . " AND fcid NOT IN (".$compare.")";
            else $query_str = "SELECT fcid FROM " . $table . " WHERE usergroup=" . $usergroup . " AND fid= " . $val_a[0] . " AND (" . $compare . ")";
            // echo "<br>".$query_str;
            $temp_a = q_execute($db, $query_str, [], PDO::FETCH_COLUMN);;
            if ($temp_a[2] == 0)
                return [[], [], 0];
            if ($i == 0 && count($fcid_a) == 0) $fcid_a = $temp_a[0];
            else $fcid_a = array_values(array_intersect($fcid_a, $temp_a[0]));
            $i++;
            // echo "<br>".count($fcid_a);
        }
        if (count($fcid_a) == 0) return [[], [], 0];
    }

    if ($fcid_a) $fcid_str = implode(',', $fcid_a);
    if (!$fcid_str) $fcid_in_add = "";
    else {
        $fcid_in_add = "AND fcid IN (" . $fcid_str . ")";
    }

    // $order_a = [];
    if ($order_a) {
        $sort_a = [];
        $query_str = "SELECT fcid FROM " . $table . " WHERE usergroup=" . $usergroup . " " . $fcid_in_add . " AND fid = " . $order_a[0] . " ORDER BY fcont" . $order_a[2] . " " . $order_a[1] . " " . $limit_str;
        // echo $query_str;
        $temp_a = q_execute($db, $query_str, [], PDO::FETCH_COLUMN);
        $sort_a = $temp_a[0];
        $query_str = "SELECT fcid,fid,fcont FROM " . $table . " WHERE usergroup=" . $usergroup . " " . $fcid_in_add . " " . $fid_in_add;
        $temp_a = q_execute($db, $query_str, []);
        if ($sort_a) $temp_a[1] = $sort_a;
    } else {
        $query_str = "SELECT t.fcid,t.fid,t.fcont FROM " . $table . " AS t JOIN (SELECT DISTINCT fcid FROM " . $table . " WHERE usergroup=" . $usergroup . " " . $fcid_in_add . " ORDER BY fcid DESC " . $limit_str . ") AS limited ON limited.fcid = t.fcid " . $fid_in_add . " ORDER BY t.fcid DESC";
        $temp_a = q_execute($db, $query_str, []);
    }

    $end = microtime(true);
    $_SESSION['performance']['native'] = number_format($end - $start, 6, '.', '');
    // echo "<pre>"; echo print_r($temp_a); echo "</pre>";
    return $temp_a;
}

function build_order_a($sort_fid, $sort_dir)
{
    $order_a[0] = ltrim($sort_fid, 'F_');
    $order_a[2] = '';
    if (substr_count($sort_dir, '*')) {
        $order_a[2] = '+0';
        $sort_dir = rtrim($sort_dir, '*');
    }
    $order_a[1] = $sort_dir;
    // echo "<br><pre>"; echo print_r($order_a); echo "</pre>";
    return $order_a;
}

function get_select_str($id, $desc_a, $val, $start_option, $select_filter_a = [])
{
    asort($desc_a);
    $select_s = "<option value=''>" . $start_option . "</option>";
    foreach ($desc_a as $temp_fid => $def) {
        $selected = ($val == $temp_fid) ? "selected" : "";
        if (!in_array($def, $select_filter_a)) $select_s .= "<option value='$temp_fid' $selected>$def</option>";
    }
    $width_css = '';
    if ($start_option == '↑↓') $width_css = "width:40px;";
    if ($start_option == 'Sortierung') $width_css = "width:85px;";
    $select_s = "<select title='* = numerische Sortierung' style='border-radius:3px;background-color:transparent;border: solid silver 1px;color:#969696;height:18px;" . $width_css . "' id='$id' name='$id'>" . $select_s . "</select>";
    return $select_s;
}

// examples für query and order
// $query_a[] = [96, '==', 'weiblich'];
// $query_a[] = [95, '==', 'Morbus Crohn'];
// $query_a[] = [92, '', 'G6303'];
// $order_a = [91, 'ASC', '+0'];

session_start();
$_SESSION['performance'] = [];
$_SESSION['temp_info'] = [];
$show_fcid = 1;

// error_reporting(E_ALL); ini_set('display_errors', 1);
require_once $_SESSION['INI-PATH'];


echo "<!DOCTYPE html><html lang='de'><head><meta charset='UTF-8'>";
echo "<link rel='stylesheet' href='" . MIQ_PATH . "css/listings.css?RAND=" . random_bytes(5) . "'>";
echo "</head><body>";

// check if document is standalone or window_box
$data_def_str = $_GET['data_def_str'] ?? "";
$data_def_a = json_decode(base64_decode($_GET['data_def_str']), true);
// echo "<pre>"; echo print_r($data_def_a); echo "</pre>";

$create  = isset($data_def_a['work_mode_a']['C']) ? 1 : 0;
$has_navbar = isset($data_def_a['work_mode_a']['N']) ? 1 : 0;
$data_edit  = isset($data_def_a['work_mode_a']['E']) ? 1 : 0;
$data_edit_frame  = isset($data_def_a['work_mode_a']['EF']) ? 1 : 0;

// $data_delete = isset($data_def_a['work_mode_a']['D']) ? 1 : 0;
$work_mode_a = $data_def_a['work_mode_a'] ?? [];
$data_delete = 0;
$data_delete_num = 0; // wir als Paramter workmode übergeben D0 D2 etc. DO ist im Basisfenster
foreach ($work_mode_a as $key => $val) {
    if (preg_match('/^D(\d*)$/', $key, $m)) {
        $data_delete = 1;
        $data_delete_num = $m[1] === '' ? 0 : (int)$m[1];
        break;
    }
}



$data_add = isset($data_def_a['work_mode_a']['A']) ? 1 : 0;
$data_filter = isset($data_def_a['work_mode_a']['F']) ? 1 : 0;

$plan_fly = isset($data_def_a['work_mode_a']['P']) ? 1 : 0;

// $param_str = $_REQUEST['param_str'] ?? "";
// if ($param_str) {
//     $param_str_decoded = urldecode(base64_decode($param_str));
//     $param_a = json_decode($param_str_decoded, true);
//     echo "<pre>"; echo print_r($param_a); echo "</pre>";
//     if (isset($param_a['sqlstr'])) $sqlstr = $param_a['sqlstr'];
// }



# get offset for limit
$data_def_a['offset']  = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

# get filter/ query-data 
$query_a = [];
$query_str = $_GET['filter_fields'] ?? "";
if ($query_str) {
    $query_a = json_decode($query_str, true);
}


if (!empty($data_def_a['query_global_a'])) $query_a = array_values(array_merge($query_a, $data_def_a['query_global_a']));;


# get order data
$order_a = [];
$sort_fid = $_GET['sort_fid'] ?? "";
$sort_dir = $_GET['sort_dir'] ?? "";
if ($sort_fid && $sort_dir) {
    $data_def_a['order_a'] = build_order_a($sort_fid, $sort_dir);
    $order_a = $data_def_a['order_a'];
}

# get quicksearch data
$quick_search_str = $_GET['quick_search'] ?? "";
if ($quick_search_str) $quick_search_str = trim(chop($quick_search_str));

# Reload if new filter (first part)
# we reset if the query of filter changes, because of new data and count
if (
    $data_def_a['quick_search_str'] != $quick_search_str
    || $data_def_a['query_a'] != $query_a
) {
    $data_def_a['quick_search_str'] = $quick_search_str;
    $data_def_a['query_a'] = $query_a;
    $data_def_a['offset'] = 0;
    $data_def_a['count_all'] = 0;
    $_SESSION['temp_info']['DEF_NEW_quick_search_str'] = $quick_search_str;
}

# get data
$temp_a = get_data_by_native_method($db, $data_def_a, $_SESSION['user_group'], $query_a, $order_a, "LIMIT");
$fcid_a = build_fcid_a($temp_a);
// echo "<pre>"; echo print_r($fcid_a); echo "</pre>";

# forbidden sorting for collumns not showed
$select_filter_a = [];
$columns_showed_a = explode(',', $data_def_a['fid_str']);
$select_filter_a = $data_def_a['desc_a'];
foreach ($columns_showed_a as $fid) unset($select_filter_a['F_' . $fid]);

# transform data für table-display
$table_a = get_table_data($fcid_a, $data_def_a);
// echo "<pre>"; echo print_r($table_a); echo "</pre>";

# if there ist something important
if (file_exists($_SESSION['FS_ROOT'] . $_SESSION['PROJECT_PATH'] . "forms/list_" . $data_def_a['fg'] . "_defs.php"))
    require_once $_SESSION['FS_ROOT'] . $_SESSION['PROJECT_PATH'] . "forms/list_" . $data_def_a['fg'] . "_defs.php";

# build nav-bar and final table-str
$nav_bar = ($has_navbar) ? navigate_bar($data_def_a, $sort_fid, $sort_dir, $quick_search_str, $select_filter_a) : "";
$table_str = build_native_table($table_a, $data_def_a, $nav_bar, $show_fcid);


# show html
echo "<div id='wrap_1' class='wrap'>";
echo $table_str;
echo "</div>";

# Reload if new filter (second part), finally count data, first run alway need two Walkthrougs
if (!$data_def_a['count_all']) {
    $data_def_a['count_all'] = get_data_by_native_method($db, $data_def_a, $_SESSION['user_group'], $query_a, $order_a)[2];
    $_SESSION['temp_info']['DEF_NEW_count'] = $data_def_a['count_all'];
}

// echo "<pre>"; echo print_r($data_def_a); echo "</pre>";

# $data_def_str setzen und info ausgeben
$pos_from = $data_def_a['offset']  + 1;
$post_to = min($data_def_a['offset']  +  $data_def_a['limit'], $data_def_a['count_all']);
$data_def_str = base64_encode(json_encode($data_def_a));
$data_info_span = "&nbsp;&nbsp;&nbsp;" . $pos_from . " - " . $post_to . " von " . $data_def_a['count_all'] . "&nbsp;&nbsp;&nbsp;<input type='hidden' value='" . $data_def_str . "'>";


$crypt_a['table_name'] = simple_encrypt($data_def_a['table']);
$crypt_a['table_key'] = simple_encrypt($data_def_a['key']);
$crypt_str = base64_encode(json_encode($crypt_a));



$start = microtime(true);
?>

<script>
    function get_wrapper(wrapper_id) {
        return document.getElementById(wrapper_id);
    }

    function eL_data_navigation(w) {
        w.querySelectorAll("[id^='DATANAV_']").forEach(btn => {
            btn.addEventListener("click", () => {
                const action = btn.id.split('_')[1];
                current_offset = navigate(current_offset, count_all, limit_lines, action);
                const p = url_params();
                window.location.href = window.location.pathname + '?' + p.toString();
            });
        });
    }

    function navigate(current_offset, count_all, limit_lines, action) {
        // Maximaler Seitenindex (0-basiert)
        const maxPage = Math.max(Math.floor((count_all - 1) / limit_lines), 0);
        switch (action) {
            case 'BB': // Erste Seite
                return 0;
            case 'B': // Eine Seite zurück
                return Math.max(current_offset - limit_lines, 0);
            case 'F': // Eine Seite vor
                return Math.min(current_offset + limit_lines, maxPage * limit_lines);
            case 'FF': // Letzte Seite
                return maxPage * limit_lines;
            default:
                return current_offset; // ungültige Aktion -> Offset bleibt
        }
    }

    function url_params() {
        const url_params = new URLSearchParams({
            offset: current_offset,
            quick_search: quick_search.value,
            sort_fid: sort_fid.value,
            sort_dir: sort_dir.value,
            filter_fields: filter_fields_str,
            data_def_str: data_def_str
        });
        return url_params
    }

    function eL_hover_tr(tbl_id) {
        const tabelle = w.querySelector('#cases');
        if (tabelle) {
            try {
                const tbody = tabelle.querySelector('tbody');
                const rows = tbody.querySelectorAll('tr');
                rows.forEach(row => {
                    row.addEventListener('mouseenter', () => {
                        row.classList.add('highlight');
                    });
                    row.addEventListener('mouseleave', () => {
                        row.classList.remove('highlight');
                    });
                });
            } catch (error) {}
        }
    }

    function eL_table_sort(tbl_id) {
        const tabelle = w.querySelector('#cases');
        if (tabelle) {
            const kopfzeile = tabelle.querySelector('thead');
            kopfzeile.addEventListener('dblclick', (event) => {
                const th = event.target.closest('th');
                if (!th) return; // Kein <th> angeklickt
                const spaltenIndex = Array.from(th.parentNode.children).indexOf(th);
                let sortDir = th.dataset.sortDir || '0';
                sortDir = sortDir === '0' ? '1' : '0'; // Richtung umschalten
                th.dataset.sortDir = sortDir;
                const tbody = tabelle.querySelector('tbody');
                const zeilen = Array.from(tbody.querySelectorAll('tr'));
                zeilen.sort((a, b) => {
                    const aWert = a.children[spaltenIndex].textContent;
                    const bWert = b.children[spaltenIndex].textContent;
                    let aNum = '';
                    let bNum = '';
                    // Versuche, die Werte als Zahlen zu vergleichen
                    if ((aWert.split('-').length - 1) == 2)
                        aNum = NaN;
                    else
                        aNum = parseFloat(aWert);
                    if ((bWert.split('-').length - 1) == 2)
                        bNum = NaN;
                    else
                        bNum = parseFloat(bWert);
                    if (!isNaN(aNum) && !isNaN(bNum)) {
                        return sortDir === '0' ? aNum - bNum : bNum - aNum;
                    }
                    // Ansonsten vergleiche als Text
                    return sortDir === '0' ?
                        aWert.localeCompare(bWert) :
                        bWert.localeCompare(aWert);
                });
                // Tabelle neu aufbauen
                tbody.innerHTML = '';
                zeilen.forEach(zeile => tbody.appendChild(zeile));
            });
        }
    }

    function eL_quicksearch(w) {
        if (quick_search) {
            quick_search.addEventListener("keydown", function(event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    const p = url_params();
                    window.location.href = window.location.pathname + '?' + p.toString();
                }
            });
        }
    }

    function updateFilterFields(w) {
        const filter_fields_a = [];
        filter_fields_a.length = 0;
        w.querySelectorAll("input[id^='S_']").forEach(el => {
            if (el.value.trim() !== "") {
                filter_fields_a.push([el.id.split('_')[1], '', el.value.trim()]);
            }
        });
        filter_fields_str = JSON.stringify(filter_fields_a);
        if (filter_fields_str != '[]') show_filter(w);
        // console.log(filter_fields_str);
    }

    function show_filter(w) {
        const filter_fields = w.querySelectorAll('input[type="text"][id*="S_"]');
        filter_fields.forEach(field => {
            field.style.display = 'inline';
        });
    }

    function eL_filter_fields(w) {
        w.querySelectorAll("input[id^='S_']").forEach(el => {
            el.addEventListener("input", () => updateFilterFields(w));
            el.addEventListener("keydown", function(event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    const p = url_params();
                    window.location.href = window.location.pathname + '?' + p.toString();
                }
            });
        });
    }

    function eL_activate_filter(w, button_id) {
        const button = w.querySelector("#" + button_id);
        if (button)
            button.addEventListener('click', function() {
                show_filter(w)
            });
    }

    function eL_add_button(w, button_text, button_id_prefix, col, clickHandler) {
        const dataRows = w.querySelectorAll('tr[id^="FCID_"]');
        dataRows.forEach(row => {
            const fcid = row.id.replace('FCID_', '');
            const actionCell = row.querySelector('.action');
            const newButton = document.createElement('button');
            newButton.id = `${button_id_prefix}_${fcid}`;
            newButton.textContent = button_text;
            newButton.classList.add(`small_button`);
            newButton.style.backgroundColor = col;
            newButton.addEventListener('click', () => {
                clickHandler(fcid, button_text, button_id_prefix, col);
            });
            if (actionCell) {
                actionCell.appendChild(newButton);
            }
        });
    }

    async function aS_fetch_q_execute(query_str) {
        const url = '<?php echo MIQ_PATH_PHP ?>q_execute.php';
        const params = new URLSearchParams({
            query_str: query_str,
            crypt_str: crypt_str
        });
        try {
            console.log(`${url}?${params.toString()}`);
            const response = await fetch(`${url}?${params.toString()}`);
            // console.log(response)
            if (!response.ok) {
                throw new Error(`HTTP-Fehler! Status: ${response.status}`);
            }
            const data = await response.json();
            // console.log(`OK: ${data}`);
            window.location.reload();
            return data;
        } catch (error) {
            console.error('Fehler beim Form-Fetch-Aufruf:', error);
            throw error;
        }
    }

    function delete_data(w, file, fcid, delete_data_num = 0) {

        function reverseNumberString(numberString) {
            const charArray = numberString.split('');
            const reversedArray = charArray.reverse();
            const reversedString = reversedArray.join('');
            return reversedString;
        }

        function generateAnyCharCode(delete_data_num = 5) {
            const characters = '0123456789';
            let result = '';
            for (let i = 0; i < delete_data_num; i++) {
                result += characters.charAt(Math.floor(Math.random() * characters.length));
            }
            return result;
        }

        temp_pass = generateAnyCharCode(delete_data_num);
        // console.log("w", temp_pass, fcid);
        if (delete_data_num)
            if (prompt(`Bitte >>${reverseNumberString(temp_pass)}<< in umgekehrter Reihenfolge eingeben:`) === temp_pass) {
                query_str = 'DELETE FROM crypt_table WHERE crypt_key=' + fcid;
                aS_fetch_q_execute(btoa(query_str));
            }
    }

    function fix_navbar_in_first_row(w) {
        const table = w.querySelector('#cases');
        const firstHeaderCell = table.querySelector("thead tr:first-child th");
        const secondHeaderRow = table.querySelector("thead tr:nth-child(2)");

        if (firstHeaderCell && secondHeaderRow) {
            const colCount = secondHeaderRow.querySelectorAll("th").length;
            firstHeaderCell.setAttribute("colspan", colCount);
        }
    }

    function hide_column(header_id) {
        const headerCell = document.getElementById(header_id);
        if (!headerCell) {
            console.error(`Header-ID '${header_id}' nicht gefunden.`);
            return;
        }
        const columnIndex = headerCell.cellIndex;
        headerCell.style.display = 'none';
        const table = headerCell.closest('table');
        if (!table) {
            console.error('Keine übergeordnete Tabelle gefunden.');
            return;
        }
        const allRows = table.querySelectorAll('tr');
        allRows.forEach(row => {
            const cellToHide = row.children[columnIndex];
            if (cellToHide) {
                cellToHide.style.display = 'none';
            }
        });
    }

    function eL_frameedit_button(num, fg, js_table_key, js_form, filename, param_str) {
        const buttonList = document.querySelectorAll(`button[id^="${num}_frameedit"]`);
        const frame_to_use = findIframeByPartialId('_visite_work');
        // console.log(frame_to_use);
        if (frame_to_use) {
            // console.log(frame_to_use.src);
            // console.log(frame_to_use.id);
            const name_file = filename + 'forms/' + js_form + '.php';
            if (buttonList.length === 0) {
                // console.warn(`Keine Buttons mit ID-Präfix "${num}_edit" gefunden.`);
                return;
            }
            buttonList.forEach(button => {
                button.addEventListener('click', function() {
                    const js_row_key = this.id.split('_')[2];
                    let new_src = name_file + '?fg=' + fg + '&' + js_table_key + '=' + js_row_key + '&param_str=' + param_str;
                    // console.log(new_src);
                    frame_to_use.src = new_src; //  + ' &param_str=' + btoa(encodeURIComponent(param_str)
                });
            });
        }
    }

    function eL_edit_table(miq_php_path, edit_table, subtable = 1) {
        document.querySelector('#cases').addEventListener('dblclick', function(e) {
            const td = e.target.closest('td');
            if (!td || td.tagName === 'TH' || td.closest('thead')) return;

            const columnIndex = td.cellIndex;
            const headerRow = this.querySelector('thead').rows[subtable];
            const headerCell = headerRow ? headerRow.cells[columnIndex] : null;
            const fid = headerCell ? headerCell.id.replace('F_', '') : '';
            // Abbruch bei gewissen Spalten
            const ignoreIds = ['', 'fcid', 'tools', '90', 'F_fcid'];
            if (ignoreIds.includes(fid)) return;
            // Verhindert das Leeren, wenn bereits editiert wird
            if (td.querySelector('textarea')) return;
            const fcid = td.parentElement.id.replace('FCID_', '');
            const originalContent = td.innerText;
            const textarea = document.createElement('textarea');
            textarea.value = originalContent;
            textarea.style.width = "100%";
            textarea.style.height = td.offsetHeight + "px";
            console.log("FID:", fid);
            td.innerHTML = '';
            td.appendChild(textarea);
            textarea.focus();

            textarea.addEventListener('blur', function() {
                const newValue = this.value;
                td.innerText = newValue;

                // Nur senden, wenn sich wirklich etwas geändert hat
                if (newValue !== originalContent) {
                    const formData = new FormData();
                    formData.append('table', edit_table); // Oder dynamisch ermitteln
                    formData.append('fcid', fcid);
                    formData.append('fid', fid);
                    formData.append('newValue', newValue);

                    fetch(miq_php_path + 'fetch_update_list_content.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                td.style.backgroundColor = 'yellow'; // Erfolg markieren
                                // console.log("DB Update erfolgreich für FCID:", fcid);
                            } else {
                                alert("Fehler beim Speichern: " + data.message);
                                td.style.backgroundColor = 'red';
                            }
                        })
                        .catch(error => {
                            console.error('Fehler im Table-Edit:', error);
                            td.style.backgroundColor = 'red';
                        });
                }
            });

            textarea.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.blur();
                }
            });
        });
    }

    // globals

    const project_path = <?php echo json_encode(PROJECT_PATH) ?>;
    const miq_php_path = <?php echo json_encode(MIQ_PATH_PHP) ?>;
    const fg = <?php echo json_encode($data_def_a['fg'] ?? "") ?>;
    const allow_table_edit = <?php echo json_encode(isset($_SESSION['rl']['tableedit'])) ?>;

    const num = <?php echo json_encode($data_def_a['num'] ?? 888888) ?>;
    const w = get_wrapper('wrap_1');
    const quick_search = w.querySelector("#" + 'quick_search'); // url_param
    const sort_fid = w.querySelector("#" + 'sort_fid'); // url_param
    const sort_dir = w.querySelector("#" + 'sort_dir'); // url_param
    var filter_fields_str = ""; // url_param 
    // globals - uses php
    const th_navbar = w.querySelector("#" + 'th_navbar'); // url_param


    const data_def_str = <?php echo json_encode($data_def_str) ?>; // url_param
    var current_offset = parseInt(<?php echo json_encode($data_def_a['offset'] ?? 0) ?>); // url_param
    const count_all = parseInt(<?php echo json_encode($data_def_a['count_all']) ?>);
    const limit_lines = parseInt(<?php echo json_encode($data_def_a['limit']) ?>);

    const create = <?php echo json_encode($create) ?>;
    const data_edit = <?php echo json_encode($data_edit) ?>;
    const data_edit_frame = <?php echo json_encode($data_edit_frame) ?>;
    const data_delete = <?php echo json_encode($data_delete) ?>;
    const delete_data_num = <?php echo json_encode($data_delete_num) ?>;
    const data_add = <?php echo json_encode($data_add) ?>;
    const data_filter = <?php echo json_encode($data_filter) ?>;
    const plan_fly = <?php echo json_encode($plan_fly ?? "") ?>;

    const data_def_a = JSON.parse(atob(data_def_str));

    // crypt delte-sql
    const crypt_str = <?php echo json_encode($crypt_str) ?>;


    // activate quicksearch
    eL_quicksearch(w);

    // load and activate filter fields
    if (data_filter) {
        const filter_a = JSON.parse('<?php echo json_encode($query_a) ?>');
        for (let [num, filter_details] of Object.entries(filter_a)) {
            let search_field = w.querySelector("#" + 'S_' + filter_details[0]);
            if (search_field) search_field.value = filter_details[2];
        }
        eL_activate_filter(w, 'filter_button');
        eL_filter_fields(w);
        updateFilterFields(w); // INIT
    }


    // set colspan for navbar/ late display because of load-delay
    if (th_navbar) {
        th_navbar.style.display = '';
        fix_navbar_in_first_row(w);
    }

    // load data navigation
    eL_data_navigation(w);

    // fill info spann 
    const data_info_span = w.querySelector("#" + 'DATA_INFO');
    if (data_info_span)
        data_info_span.innerHTML = <?php echo json_encode($data_info_span) ?>;


    // list features
    eL_hover_tr();
    eL_table_sort();

    // console.log(data_def_a['form_name']);
    // console.table(window.top.win_boxes2form);
    if (data_edit)
        eL_add_button(w, '✎', 'E', '#7BCCB5', (fcid) => {
            if (data_def_a['no_wbox']) window.open(project_path + 'forms/' + data_def_a['form_name'] + '.php?fg=' + data_def_a['fg'] + '&fcid=' + fcid);
            else {
                let data_winbox = {};
                data_winbox['num'] = window.top.win_boxes2form[data_def_a['form_name']] ?? 555;
                data_winbox['title'] = data_def_a['form_name'];
                data_winbox['url'] = project_path + 'forms/' + data_def_a['form_name'] + '.php?fg=' + data_def_a['fg'] + '&fcid=' + fcid;
                window.top.winbox_url(JSON.stringify(data_winbox));
            }
            // window.top.forward_form('form', 'fcid', fcid, data_def_a['form_name'], num);
        });

    if (data_edit_frame)
        eL_add_button(w, '✎', 'E', '#7BCCB5', (fcid) => {
            parent.window.location.href = project_path + 'forms/' + data_def_a['form_name'] + '.php?fg=' + data_def_a['fg'] + '&fcid=' + fcid;
        });

    if (data_delete)
        eL_add_button(w, '✖', 'D', 'red', (fcid) => {
            delete_data(w, 'del_patient', fcid, delete_data_num);
        });

    if (plan_fly) {
        eL_add_button(w, '✈️', 'P', 'white', (fcid) => {
            const url = `<?php echo MIQ_PATH_PHP ?>fetch_geodata_get_fcid.php?fcid=${fcid}`;
            fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('HTTP-Fehler: ' + response.status);
                    return response.json();
                })
                .then(data => {
                    // Prüfen, ob eine Fehlermeldung vom Server vorliegt
                    if (data.status && data.status === "error") {
                        console.warn('Server liefert keine Daten:', data.message);
                        return; // Keine weiteren Schritte
                    }
                    const coordData = data[0]['layCoor'];

                    // Funktion: erstes [x, y] extrahieren
                    function extractFirstPair(d) {
                        if (!d) return null;
                        let parsed = (typeof d === 'string') ? JSON.parse(d) : d;

                        function findCoords(arr) {
                            if (Array.isArray(arr)) {
                                if (arr.length === 2 && typeof arr[0] === 'number' && typeof arr[1] === 'number') {
                                    return arr;
                                }
                                for (let item of arr) {
                                    const result = findCoords(item);
                                    if (result) return result;
                                }
                            }
                            return null;
                        }
                        return findCoords(parsed);
                    }

                    const extracted_first_pair = extractFirstPair(coordData);
                    if (!extracted_first_pair) {
                        console.warn('Keine Koordinaten gefunden.');
                        return;
                    }
                    // console.log('Erstes Koordinatenpaar:', extracted_first_pair);
                    // Leaflet-Karte fliegen lassen
                    if (parent.window.parent_map) {
                        parent.window.parent_map.flyTo(extracted_first_pair, 23, {
                            animate: true,
                            duration: 1.75
                        });
                    } else {
                        console.error('Leaflet map im Parent nicht gefunden!');
                    }

                })
                .catch(err => console.error('Fetch oder Parsing fehlgeschlagen:', err));
        });


    }

    if (create) {
        const th_fg = document.getElementById('F_99901001');
        const colIndex_fg = Array.from(th_fg.parentNode.children).indexOf(th_fg);
        const create_elements = document.querySelectorAll('[id^="C_"]');
        eL_add_button(w, '✂', 'C', '#3024d5ff', (fcid) => {
            const this_button = document.getElementById('C_' + fcid);
            const currentCell = this_button.closest('td');
            const currentRow = currentCell.closest('tr');
            const targetCell = currentRow.children[colIndex_fg];
            const fg = targetCell ? targetCell.textContent.trim() : null;
            // console.log(fg);
            window.open(<?php echo json_encode(MIQ_PATH . 'modules/creator/create.php?fg=') ?> + fg);
        });
        // display_only_forms();
    }

    // function display_only_forms() {
    //     const th_type = document.getElementById('F_99901000');
    //     const colIndex_type = Array.from(th_type.parentNode.children).indexOf(th_type);
    //     const create_elements = document.querySelectorAll('[id^="C_"]');
    //     create_elements.forEach(el => {
    //         // console.log(el.id, el.textContent); 
    //         const this_button = document.getElementById(el.id);
    //         const currentCell = this_button.closest('td'); 
    //         const currentRow = currentCell.closest('tr'); 
    //         const targetCell = currentRow.children[colIndex_type];
    //         const value = targetCell ? targetCell.textContent.trim() : null;
    //         if (value != 'form') this_button.style.display = 'None';
    //     });
    // }

    if (<?php echo json_encode(isset($_SESSION['rl']['showfcid']) ? 0 : 1) ?>) hide_column('F_fcid');

    let parentWinboxExist = 0;
    if (window.top && typeof window.top.findParentWinboxDiv === "function") parentWinboxExist = 1;
    if (parentWinboxExist) { // wenn nicht im ifranme
        const parentWinbox = window.top.findParentWinboxDiv(window);
        if (parentWinbox && data_info_span) { // Beachte das im Workmode N eingeschaltet sein muss für Navigationsleiste
            parentWinbox.querySelector('.wb-title').textContent = parentWinbox.querySelector('.wb-title').textContent.split(':')[0] + ':' + data_info_span.textContent;
            data_info_span.innerHTML = ''; // nur leer wenn in BOX - in eigenem Fenster anzeigen
        }

        // Position für Sitzung speichern 
        const outer_wb = window.top.findClosestWinboxFromIframe(window.frameElement);
        window.addEventListener('pagehide', () => {
            window.top.set_last_winbox_state(num, outer_wb);
        });
    }

    if (allow_table_edit) eL_edit_table(miq_php_path, 'forms_' + fg);
</script>

<?php



$end = microtime(true);
$_SESSION['performance']['javascript'] = number_format($end - $start, 6, '.', '');
$_SESSION['performance']['all'] = array_sum($_SESSION['performance']);



echo "<div style='display:none;background-color:lightgray; width:70%; overflow-wrap: break-word; overflow: auto;'>";
echo "<pre>";
echo print_r($_SESSION['temp_info']);
echo "</pre>";
echo "<pre>";
echo print_r($data_def_a);
echo "</pre>";


echo "<pre>";
echo print_r($_GET);
echo "</pre>";

echo "<pre>";
echo print_r($_SESSION['performance']);
echo "</pre>";
echo "</div>";

echo "</body></html>";


?>