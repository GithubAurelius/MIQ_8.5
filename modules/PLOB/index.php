<?php
if (!isset($_SESSION)) session_start();
require_once $_SESSION['INI-PATH'];

// Options 
$add_on_a = [
    "showTools" => 1,
    "zoom" => "false",
    "scale" => 1,
    "polylineMeasure" => 1,
    "mapRotation" => 1,
    "powerPaint" => 1,
    "screenShots" => 1,
    "myPosition" => 1,
    "moveImage" => 0,
    "fixing" => 1,
    "geomap_collapse" => 0,
    "coloring" => 1,
    "profile_layer" => 1,
    "profile_image" => 1,
    "createLayer" => 1,
];

if (1) { // TEST-AREA

    // Test image-parser (incl. crypt)
    // $from_file = true;
    // $path = "2023_11/";
    // $filename_c = 'test.png';
    // $filename_c = 'IlpJXzQ2NV9GRkNfOF9HUl9IS19FNF8wMDAzX0dfcC5wbmcix1x1x1xozU-es3xKpxyST_ydjwChg==';
    // $crypted = '1';
    // require  MIQ_ROOT_PHP."parse_img.php";
    // $image_a = parse_img($path, $filename_c, $crypted);
    // echo "<pre>"; echo print_r($image_a); echo "</pre>";
    // echo "<img width='400' src='{$image_a['file_data']}'>"; 
}

function sortArrayByLayName(array &$array): bool
{   // Macht was der name sagt
    return usort($array, function ($a, $b) {
        $nameA = (string) ($a['layName'] ?? '');
        $nameB = (string) ($b['layName'] ?? '');
        return strnatcasecmp($nameA, $nameB);
    });
}

// === Profilsteuerung 

// Besondere Rechte für PLOB-Only User, insbes. Einschränkung Profilauswahl
if (isset($_SESSION['rl']['plobonly'])) {
    $add_on_a['profile_image'] = 0;
    $add_on_a['profile_layer'] = 0;
    $add_on_a['fixing']        = 0;
    $add_on_a['powerPaint']    = 0;
    $add_on_a['createLayer']   = 0;
    $add_on_a['coloring']      = 0;
    $_SESSION['profile_image']  = "";
    $_SESSION['profile_layer']  = "";
}

// POSTS
if ($_POST['profile_image'] ?? "")
    $_SESSION['profile_image'] = $_POST['profile_image'];

if ($_POST['profile_layer'] ?? "")
    $_SESSION['profile_layer'] = $_POST['profile_layer'];

// Einlesen der Profile
$profile_image_str = "";
$profile_layer_str = "";
$all_profile_layer_a = [];
$all_profiles_src = get_query_data($db, 'geo_layer', "layType like '%_profile'");
foreach ($all_profiles_src as $key => $profile_layer_a) {
    unset($profile_layer_a['layerId']);
    unset($profile_layer_a['layCoor']);
    unset($profile_layer_a['fileNameC']);
    unset($profile_layer_a['layText']);
    unset($profile_layer_a['filePath']);
    unset($profile_layer_a['fileName']);
    unset($profile_layer_a['fileNameC']);
    unset($profile_layer_a['fileType']);
    unset($profile_layer_a['layColor']);
    unset($profile_layer_a['layOpacity']);
    unset($profile_layer_a['mts']);
    // Hier werden die Standardprofile für PLOB-Only User gesetzt, damit wird Zugriff nur auf diese begrenzt
    if (isset($_SESSION['rl']['plobonly'])) {
        if ($profile_layer_a['layType'] == 'plan_profile' && strtolower($profile_layer_a['layName']) == strtolower($_SESSION['user_name']))
            $_SESSION['profile_image']  = $profile_layer_a['fcid'];
        if ($profile_layer_a['layType'] == 'object_profile' && strtolower($profile_layer_a['layName']) == strtolower($_SESSION['user_name']))
            $_SESSION['profile_layer']  = $profile_layer_a['fcid'];
    }
    if (($_SESSION['profile_image'] ?? "") == $profile_layer_a['fcid']) $profile_image_str = $profile_layer_a['layCollect'];
    if (($_SESSION['profile_layer'] ?? "") == $profile_layer_a['fcid']) $profile_layer_str = $profile_layer_a['layCollect'];
    unset($profile_layer_a['muid']);
    unset($profile_layer_a['usergroup']);
    unset($profile_layer_a['layCollect']);
    $all_profile_layer_a[$profile_layer_a['fcid']] =  $profile_layer_a;
    unset($all_profiles_src[$key]);
}
if (count($all_profile_layer_a)) sortArrayByLayName($all_profile_layer_a);
$all_profile_layer_jsn = json_encode($all_profile_layer_a);

// Filter-FCID-Strings erstellen für den nachfolgenden Aufbau der Control-Layer für Images und Objekte
if ($profile_image_str) $profile_image_str = " AND fcid IN (" . $profile_image_str . ")";
if ($profile_layer_str) $profile_layer_str = " AND fcid IN (" . $profile_layer_str . ")";

// Noch male ein Abfang PLOB-Only User, die wieder abgemeldet werden, wenn kein Profil entsprechenden des Logins exstiert
if (isset($_SESSION['rl']['plobonly'])) {
    if (!$_SESSION['profile_image'] || !$_SESSION['profile_layer']) {
        echo "<script>
            alert('Für Sie wurde noch keine Profil eingerichtet!\\n\\nWenden Sie sich bitte an die Person, die Ihnen die Zugangsdaten übergeben hat.');
            document.location.href='" . $_SESSION['WEBROOT'] . "login.php';
            </script>";
    }
}

// === Einlesen der Image-layer 
$all_img_layer_a = [];
$all_img_layer_a_source = get_query_data($db, 'geo_layer', "layType='image_rot'" . $profile_image_str);
// change id to fcid
foreach ($all_img_layer_a_source as $key => $img_layer_a) {
    unset($img_layer_a['layColor']);
    unset($img_layer_a['layerId']);
    unset($img_layer_a['mts']);
    $all_img_layer_a[$img_layer_a['fcid']] =  $img_layer_a;
    unset($all_img_layer_a_source[$key]);
}
$all_image_layer_jsn = json_encode($all_img_layer_a);

// === Einlesen der Image-layer 
$all_object_layer_a = [];
$all_object_layer_a_source = get_query_data($db, 'geo_layer', "layType='objectlayer'" . $profile_layer_str);
foreach ($all_object_layer_a_source as $key => $obj_layer_a) {
    unset($obj_layer_a['layText']);
    unset($obj_layer_a['filePath']);
    unset($obj_layer_a['fileName']);
    unset($obj_layer_a['fileNameC']);
    unset($obj_layer_a['fileType']);
    unset($obj_layer_a['layOpacity']);
    unset($obj_layer_a['layCoor']);
    unset($obj_layer_a['mts']);
    $all_object_layer_a[$obj_layer_a['fcid']] =  $obj_layer_a;
    unset($all_object_layer_a_source[$key]);
}
if (count($all_object_layer_a)) sortArrayByLayName($all_object_layer_a);
$all_object_layer_jsn = json_encode($all_object_layer_a);

?>
<!doctype html>
<html lang="de">

<head>
    <meta charset="utf-8" />
    <title>PlOb</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <!-- Einbindung der essentiellen Scripte und übergeordneten Variablen und Konstanten -->
    <?php require_once "src_plob/head_include_area.php"; ?>
</head>


<body>
    <!-- HTML-Elemente   -->
    <?php require_once "src_plob/body_base_include.php"; ?>

    <script>
        // === Globale Variablen für Initialisierung
        // const centerOfObject = [52.389976, 13.063427];
        // var tl = [52.390161794, 13.0629664552];
        // var tr = [52.390251823, 13.0637550246];
        // var bl = [52.389659262, 13.0630093706];
        // const planBearing = 10.2;
        // const planMinzoom = 12;
        // const startZoom = 20;
        const plobCoord = JSON.parse('<?php echo $_SESSION["PLOB_COORD"]; ?>');
        const centerOfObject = plobCoord['centerOfObject'];
        var tl = plobCoord['tl'];
        var tr = plobCoord['tr'];
        var bl = plobCoord['bl'];
        const planBearing = plobCoord['planBearing'];
        const planMinzoom = plobCoord['planMinzoom'];
        const startZoom = plobCoord['startZoom'];
        const init_fixing = "[tl,tr,bl]";
        
        const add_on_a = <?php echo json_encode($add_on_a) ?>;
        const status_span = document.getElementById('status_span');
        const color_choice = document.getElementById('color_choice');
        const miq_path_php = <?php echo json_encode(MIQ_PATH_PHP) ?>;

        // === JS basics: map and plugins
        <?php require_once "src_plob/js_basics.js"; ?>

        // === Global vars needed map- and html-prebuilds 
        const leftControlContainer = document.querySelector('.leaflet-control');

        // === Sendet Geodaten asynchron per POST an den Server und verarbeitet die Antwort.
        async function fetchDataAndUpdateGeoLayer(geo_jsn) {
            const url = '<?php echo MIQ_PATH_PHP ?>fetch_geodata_update.php';
            let user_erro_info_code = 0;
            try {
                const json_payload = JSON.stringify(geo_jsn);
                // Fetch-Aufruf mit POST-Methode und JSON-Body
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: json_payload // send data to body
                });
                // check network status
                if (!response.ok) {
                    console.error(`🚫 Kein Zugriff auf fetch-forms. HTTP Status: ${response.status}`);
                    user_erro_info_code = '0031';
                    throw new Error('HTTP-Verbindungsfehler');
                }
                // parse answer
                const data = await response.json();
                // check data.status
                if (data.status === 'ok') {
                    // console.log('Erfolg:', data.message);
                    status_span.innerHTML = '✅ ' + data.message;
                    // console.log(data);
                    return data;
                }
            } catch (error) {
                console.error('Fehler beim Form-Fetch-Aufruf (Netzwerk oder Parsing):', error);
                // throw error; 
            }
        }

        // === JS Image fixing: Inlcuding loading Image-layer 
        // Code kann optimiert werden, indem man imgLayers, overlayImgs und all_image_layer_obj anwendungsoptimiert, hier sind aktuell Redundanzen
        const all_image_layer_obj = JSON.parse('<?php echo $all_image_layer_jsn; ?>');
        <?php require_once "src_plob/js_image_layer.js"; ?>

        // === JS Draw-layer 
        // Code kann optimiert werden, indem man overlayLayer, overlayDraws und all_object_layer_obj anwendungsoptimiert, hier sind aktuell Redundanzen
        const all_object_layer_obj = JSON.parse('<?php echo $all_object_layer_jsn; ?>');

        // === JS object layer: Inlcuding some belongig buttons 
        <?php require_once "src_plob/js_object_layer.js"; ?>
        const webroot = <?= json_encode($_SESSION['WEBROOT']) ?>;
    </script>

    <?php
    if ($add_on_a['screenShots']) require_once "src_plob/tools_screenshot_container.php";
    require_once '../../../session.php';
    ?>
</body>

</html>