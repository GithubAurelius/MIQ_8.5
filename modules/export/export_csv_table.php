
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once $_SESSION['INI-PATH'];

function export_forms_pivot_csv($db, $outfile = "pivot_export.csv") {

    $rows = $db->query("SELECT fcid, fid, fcont FROM forms_10100")->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) return false;

    // Alle Spalten (fid)
    $fids = [];
    foreach ($rows as $r) $fids[$r['fid']] = true;
    $columns = array_keys($fids);

    // Pivot: fcid → fid → fcont
    $pivot = [];
    foreach ($rows as $r) {
        $pivot[$r['fcid']][$r['fid']] = $r['fcont'];
    }

    // CSV erzeugen
    $fp = fopen($outfile, 'w');
    fwrite($fp, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Kopf
    fputcsv($fp, array_merge(['fcid'], $columns), ';', '"', "\\");

    // Zeilen
    foreach ($pivot as $fcid => $vals) {
        $row = [$fcid];
        foreach ($columns as $fid) {
            $row[] = $vals[$fid] ?? '';
        }
        fputcsv($fp, $row, ';', '"', "\\");
    }

    fclose($fp);
    return $outfile;
}


define('TEMP_PATH_SRV', $_SESSION['FS_ROOT'].'temp/');
# define('TEMP_PATH_SRV', '/var/www/zi_mh.local/temp/');
# echo TEMP_PATH_SRV;
$file = TEMP_PATH_SRV . 'forms_pivot.csv';   // echter Serverpfad
export_forms_pivot_csv($db, $file);
echo "<h1><a href='" . TEMP_WEB . "forms_pivot.csv'>Download</a></h1>"; 