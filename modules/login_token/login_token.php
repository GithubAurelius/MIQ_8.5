<?php
error_reporting(E_ALL); ini_set('display_errors', 1);
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

function generate_login_token($master_uid)
{
    global $db;
    // 1. Sicheren Base64URL-Token generieren
    // $rawToken = random_bytes(32);
    // $token = rtrim(strtr(base64_encode($rawToken), '+/', '-_'), '=');

    $token = generateReadableToken(2, 5); // 5 Gruppen mit je 4 Zeichen

    // 2. Ablaufzeit berechnen (jetzt + 24 Stunden)
    $expiresAt = (new DateTime('+24 hours'))->format('Y-m-d H:i:s');
    // 3. In Datenbank einfügen
    $stmt = $db->prepare("
        INSERT INTO token_store (id,master_uid, token, expires_at, used)
        VALUES (:id,:master_uid, :token, :expires_at, 0)
    ");
    $fcid_ts = date('YmdHis');
    $fcid = (int) ($fcid_ts . substr(microtime(true), 11, 2));
    $stmt->execute([
        ':id'    => $fcid,
        ':master_uid'    => $master_uid,
        ':token'      => $token,
        ':expires_at' => $expiresAt,
    ]);
    // 4. Token zurückgeben
    return $token;
}


// Token erzeugen
$masterUid = $_REQUEST['key'] ?? 0;
$send_mail = $_REQUEST['mail'] ?? 0;
$print = $_REQUEST['print'] ?? 0;
if ($send_mail) $send_mail = base64_decode($send_mail);
$token = generate_login_token($masterUid);
$baseUrl = $_SESSION['WEBROOT'] . $_SESSION['PROJECT_PATH'];
// $loginUrl = $baseUrl . '?t=' . urlencode($token);

$user_a = get_user_data($masterUid);
$tmp_a['username'] = $user_a['login_name'];
$tmp_a['password'] = $user_a['login_pass'];
$json_tmp_str = json_encode($tmp_a);
$full_url = $baseUrl."login.php?tl=".base64_encode($json_tmp_str);

$qrcode_file = $_SESSION["FS_ROOT"] . $_SESSION["PROJECT_PATH"] . 'temp/' . $masterUid . 'qrcode.png';
QRcode::png("$full_url", $qrcode_file, QR_ECLEVEL_L, 8, 1, true); // Generiert PNG
$dataUri = "data:image/png;base64," . base64_encode(file_get_contents($qrcode_file));



// echo "<pre>"; echo print_r($user_a); echo "</pre>"; 

$html_str = file_get_contents($_SESSION['FS_ROOT'] . $_SESSION['PROJECT_PATH'] . 'forms/login_mail_template.html');
$html_str = str_replace('[PROJECTNAME]', $_SESSION['PROJECTNAME'], $html_str);
# $html_str = str_replace('[BASEURL]', $baseUrl, $html_str);


$html_str = str_replace('[BASEURL]', $baseUrl, $html_str, $html_str);
$html_str = str_replace('[FULLURL]', $full_url, $html_str, $html_str);
$html_str = str_replace('[USERNAME]', $user_a['login_name'], $html_str);
$html_str = str_replace('[TOKEN]', $user_a['login_pass'], $html_str);
$html_str = str_replace('[QRCODE]', $dataUri, $html_str);

if (strlen($user_a['login_pass']) >11) {
    echo "Der Patient hat bereits seine Zugangsdaten erhalten und auch das Initial-Passwort geändert!<br><br>Damit der Patient wieder Zugang erhält, 
    <ul>
        <li>senden Sie ihm entweder eine Zugangs-E-Mail oder</li>
        <li>teilen Sie dem Patienten seinen Benutzernamen (CEDUR-Nummer) und den Zugang (https://cedur.online/) mit, damit er sein Passwort neu vergeben kann. 
    </ul>";
} else {
    echo $html_str;
    echo "<script>print()</script>";
}