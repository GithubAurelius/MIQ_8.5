<?php
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['uid'])) header("Location: /");
require_once $_SESSION['INI-PATH'];

$fcid = $_REQUEST['fcid'] ?? "";

if ($fcid) {
    $fcont_a = get_query_data($db, 'forms_10110', $query_add = 'fcid=' . $fcid . ' AND fid=52');
    if (count($fcont_a)) {
        $all_file_a = json_decode($fcont_a[0]['fcont'], true);
        // echo "<pre>"; echo print_r($all_file_a); echo "</pre>";// if (!substr_count($file, 'DELETED')) $file = "PDF";
        $file_a = $all_file_a[count($all_file_a)-1];
        // echo "<pre>"; echo print_r($file_a); echo "</pre>";
        if (!substr_count($file_a['fileNameEncoded'], 'DELETED')){
            $url = MIQ_PATH_PHP . "parse_img.php?path=" . urlencode($file_a['filePath']) . "&filename_c=" . urlencode($file_a['fileNameEncoded']) . "&c=1&show_image=1";
            header("Location: " . $url);
        } else echo "<h1>Die zuletzt eingestellte Datei wurde gelöscht!</h1>";
    } else echo "<h1>Keine Datei gefunden!</h1>";
} else echo "<h1>Keine Datei gefunden!</h1>";
