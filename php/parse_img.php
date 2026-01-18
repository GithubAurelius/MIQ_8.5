<?php
// header('Content-Type: application/json');
if (!isset($_SESSION)) session_start();

if (!isset($_SESSION['m_uid'])) { 
    echo json_encode([
        'status'  => 'error',
        'message' => 'SESSION gone'
    ]);
    if (!isset($_SESSION['uid'])) header("Location: /");
    exit;
} else require_once $_SESSION['INI-PATH'];

$show_image = $_GET['show_image'] ?? 0;

// Parameter aus GET holen
if (empty($from_file)) {
    $path       = $_GET['path'] ?? '';
    $filename_c = $_GET['filename_c'] ?? '';
    $incldb      = $_GET['incldb'] ?? 0;
    $crypted    = $_GET['c'] ?? '';
}


$layOpacity = '0.1';
$layName    = '';
$layCoor    = '';
if ($incldb) {
    $geo_a = get_query_data($db, 'geo_layer', $query_add = 'fcid=' . $incldb);
    if ($geo_a) {
        $layOpacity = $geo_a[0]['layOpacity'] ?? 0.5;
        $layName = $geo_a[0]['layName'] ?? '';
        $layCoor = $geo_a[0]['layCoor'] ?? '';
    }
}

function parse_img($path, $filename_c, $crypted = 0)
{
    global $layOpacity, $layName, $layCoor;
    $file_path = UPLOAD_BASE . $path . $filename_c;
    if (!file_exists($file_path))
        return [
            'file_type' => '',
            'file_path' => $path,
            'file_name_c' => $filename_c,
            'file_name' => '',
            'lay_opacity' => 0,
            'lay_name' => '',
            'lay_coor' => '',
            'file_data' => ''
        ];

    // file als binären Stream einlesen
    if ($crypted) {
        require_once ENCRYPTION;
        $filesStream_a = decrpyt_file(UPLOAD_BASE . $path, $filename_c, SECRET);
        $file_data = $filesStream_a[2];
        $filename = $filesStream_a[1];
        // $filesStream_a[0] ist MIME-Type: application/octet-stream (unstrukturiert);
    } else {
        $file_data = file_get_contents($file_path);
        $filename = $filename_c;
    }

    // MIME-Type aus dem Stream bestimmen
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_buffer($finfo, $file_data);
    finfo_close($finfo);

    // base64 kodieren
    $base64 = base64_encode($file_data);

    // data-URI erzeugen
    $data_uri = "data:$mime;base64,$base64";

    $original_filename = $filename ?? "";
    if ($original_filename)
        $original_filename = str_replace('"', '', $filename);

    return [
        'file_type' => $mime,
        'file_path' => $path,
        'file_name_c' => $filename_c,
        'file_name' => $original_filename,
        'lay_opacity' => $layOpacity,
        'lay_name' => $layName,
        'lay_coor' => $layCoor,
        'file_data' => $data_uri
    ];
}


if ($show_image) { // Bild anzeigen z.B. browser aufruf
    $image_a = parse_img($path, $filename_c, $crypted);

    // Prüfen, ob es ein PDF ist
    if (substr_count($image_a['file_name'], '.pdf')) {
        // PDF im Browser anzeigen (streamen)
        $file_path = UPLOAD_BASE . $image_a['file_path'] . $image_a['file_name_c'];
        if ($crypted) {
            require_once ENCRYPTION;
            $file_data_arr = decrpyt_file(UPLOAD_BASE . $image_a['file_path'], $image_a['file_name_c'], SECRET);
            $file_path_tmp = tempnam(sys_get_temp_dir(), 'pdf_');
            file_put_contents($file_path_tmp, $file_data_arr[2]);
            $file_path = $file_path_tmp;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $image_a['file_name'] . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        if ($crypted) unlink($file_path); // temporäre Datei löschen
        exit;
    } else {
        // Bilder wie bisher
        echo "<center>
                <span style='font-family:arial'>Zum Download auf das Bild klicken.<br>
                <span style='font-size:9px'>{$image_a['file_name']}</span></span><br>
                <a href='{$image_a['file_data']}' download='{$image_a['file_name']}'><br>
                    <img style='height:90%;object-fit: contain;' src='{$image_a['file_data']}'/>
                </a>
              </center>";
    }
    exit;
}
if (empty($from_file)) // Json exportieren
    echo json_encode(parse_img($path, $filename_c, $crypted));

// $from_file = true, dann kann hier parse_img(...) aufgerufen werden bzw. irgendwo nach einbinden dieser Datei - sowie bei show_image