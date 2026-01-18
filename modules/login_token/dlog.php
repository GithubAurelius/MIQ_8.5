<?php
session_start();
require $_SESSION['INI-PATH'];
if (!defined('MIQ_ROOT_PHP')) {
    define('MIQ_ROOT_PHP', "");
    exit;
}
require MIQ_ROOT . "modules/login_token/phpqrcode/qrlib.php";


function convert_html_to_plain_text($html)
{
    $html = str_ireplace(
        ['</p>', '</div>', '</ol>', '</ul>', '</h1>', '</h2>', '</h3>', '</h4>', '</h5>', '</h6>', '</li>', '<br>', '<br/>', '<br />'],
        "\n\n", // Zwei Zeilenumbrüche für Absätze, divs, Listenende, Überschriften
        $html
    );
    $html = str_ireplace(['<ul', '<ol', '<li'], "\n- ", $html);
    $text = strip_tags($html);
    $text = preg_replace("/\n{3,}/", "\n\n", $text);
    $text = trim($text);
    return $text;
}


function get_user_data($master_uid)
{
    global $db;
    $stmt = $db->prepare("SELECT * FROM user_miq WHERE master_uid = :master_uid");
    $stmt->execute([':master_uid' => $master_uid]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


function generateReadableToken(int $groups = 5, int $charsPerGroup = 4): string
{
    $allowed = str_split('ABCDEFGHJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz123456789'); // ohne 0, O, I, L
    $token = [];

    for ($i = 0; $i < $groups * $charsPerGroup; $i++) {
        $randomIndex = random_int(0, count($allowed) - 1);
        $token[] = $allowed[$randomIndex];
    }

    // In Gruppen mit Bindestrich aufteilen
    return implode('-', str_split(implode('', $token), $charsPerGroup));
}

function generate_login_token($master_uid, $param_str)
{
    global $db;
    // Sicheren Base64URL-Token generieren
    // $rawToken = random_bytes(32);
    // $token = rtrim(strtr(base64_encode($rawToken), '+/', '-_'), '=');

    $token = generateReadableToken(2, 5); // 5 Gruppen mit je 4 Zeichen

    // 2. Ablaufzeit berechnen (jetzt + 24 Stunden)
    $expiresAt = (new DateTime('+24 hours'))->format('Y-m-d H:i:s');
    // 3. In Datenbank einfügen
    $fcid_ts = date('YmdHis');
    $fcid = (int) ($fcid_ts . substr(microtime(true), 11, 2));
    $stmt = $db->prepare("
        INSERT INTO token_store (id, master_uid, token, expires_at, params, used)
        VALUES (:id,:master_uid, :token, :expires_at, :params, 0)
    ");
    echo "T-FCID:". $fcid;
    $stmt->execute([
        ':id'   => $fcid,
        ':master_uid'   => $master_uid,
        ':token'        => $token,
        ':params'       => $param_str,
        ':expires_at'   => $expiresAt
    ]);
    
    return $token;
}


$param_str = $_REQUEST['dlog'] ?? 0;
if ($param_str) { 
    $param_a = json_decode(urldecode(base64_decode($param_str)),true);
    $param_a['direct'] = 1;
    $param_a['user_group'] =  $_SESSION['user_group'];
    $param_str = base64_encode(urlencode(json_encode($param_a)));
    $masterUid = $param_a['pid'];
    $fcid  = $param_a['visite'];
} else exit;



// Token erzeugen
$send_mail = $_REQUEST['mail'] ?? 0;
$print = $_REQUEST['print'] ?? 0;

$token = generate_login_token($masterUid, $param_str);
$baseUrl = $_SESSION['WEBROOT'] . $_SESSION['PROJECT_PATH'];
$loginUrl = $baseUrl . 'login.php?t=' . urlencode($token);
echo "<br>Direct: ".$loginUrl."<br>";
$prepareUrl = $baseUrl . 'login_patient_refer.php?url=' . urlencode($loginUrl);
// echo $loginUrl;
echo "<input type='text' id='copy_input' size='120' value='".$prepareUrl."' readonly>";

# TEST
$param_str = urldecode(base64_decode($param_str));
$param_a = json_decode($param_str, true);
echo "<pre>"; echo print_r($param_a); echo "</pre>";


$qrcode_file = TEMP . 'qrcode/' . $masterUid . '_qrcode.png';
QRcode::png($prepareUrl, $qrcode_file, QR_ECLEVEL_L, 8, 1, true); // Generiert PNG
$dataUri = "data:image/png;base64," . base64_encode(file_get_contents($qrcode_file));

$html_str = "";
$html_str .= "<div class='qr-section'><center><h1>".$param_a['praxis_pid']."</h1><img src='".$dataUri."' alt='QR Code' style='width: 400px; height: 400px;'><h3>Pat.-Code: ".$param_a['ext_fcid']."</h3></center></div>";
    
// $html_str = file_get_contents($_SESSION['FS_ROOT'] . $_SESSION['PROJECT_PATH'] . 'forms/login_mail_template.html');
// $html_str = str_replace('[PROJECTNAME]', $_SESSION['PROJECTNAME'], $html_str);
// $html_str = str_replace('[BASEURL]', $baseUrl, $html_str);
// $html_str = str_replace('[USERNAME]', $user_a['login_name'], $html_str);
// $html_str = str_replace('[TOKEN]', $token, $html_str);
// $html_str = str_replace('[QRCODE]', $dataUri, $html_str);




echo $html_str;
if ($print) echo "<script>print();</script>";

?>
<script>
document.getElementById('copy_input').addEventListener('click', function() {
  this.select();
  document.execCommand('copy');
  

});

</script>";
