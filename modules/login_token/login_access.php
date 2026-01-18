<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require $_SESSION['INI-PATH'];
if (!defined('MIQ_ROOT_PHP')) {
    define('MIQ_ROOT_PHP', "");
    exit;
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



$print = $_REQUEST['print'] ?? 0;
$masterUid = $_REQUEST['key'] ?? 0;
$pass = $_REQUEST['pass'] ?? "";
// $send_mail = $_REQUEST['mail'] ?? 0;
// if ($send_mail) $send_mail = base64_decode($send_mail);



if (!$pass) {
    $random_pass = generateReadableToken(2,4);
    echo $random_pass;
    echo "<br><br><a href='".$_SERVER['PHP_SELF']."?key=".$masterUid."&pass=".$random_pass."'>SEND</a>";
}
if (!$pass) exit;

$pass_md5 = md5($pass);

$stmt = $db->prepare("UPDATE user_miq SET login_pass=:pass WHERE master_uid = :master_uid");
$stmt->execute([':master_uid' => $masterUid, ':pass' => $pass_md5]);
$stmt->execute();

$baseUrl = $_SESSION['WEBROOT'] . $_SESSION['PROJECT_PATH'];


$user_a = get_user_data($masterUid);
$send_mail = $user_a['email'];


// echo show_a($user_a);

$html_str = file_get_contents($_SESSION['FS_ROOT'] . $_SESSION['PROJECT_PATH'] . 'forms/login_mail_template.html');
$html_str = str_replace('[PROJECTNAME]', $_SESSION['PROJECTNAME'], $html_str);
$html_str = str_replace('[BASEURL]', $baseUrl, $html_str);
$html_str = str_replace('[USERNAME]', $user_a['login_name'], $html_str);
$html_str = str_replace('[TOKEN]', $pass, $html_str);



require MIQ_ROOT . 'modules/PHPMailer-master/src/Exception.php';
require MIQ_ROOT . 'modules/PHPMailer-master/src/PHPMailer.php';
require MIQ_ROOT . 'modules/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo $html_str;



$mail = new PHPMailer(true); // 'true' aktiviert Exceptions für bessere Fehlerbehandlung


if (!$print) {
    if (!$send_mail) echo "<strong><font color='red'>E-Mail ist noch nicht im System erfasst!</font></strong><br>";
    else {
        try {
            // DEBUG
            // $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER; // oder DEBUG_CLIENT, DEBUG_CONNECTION
            // $mail->Debugoutput = 'html'; // Für Browser-Ausgabe
            // Server-Einstellungen (Beispiel: Gmail SMTP)
            // Ersetzen Sie die Platzhalter mit Ihren tatsächlichen SMTP-Detail
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host       = 'smtp.strato.de';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_SESSION['SERVICE_MAIL'];
            $mail->Password   = $_SESSION['SERVICE_PASS'];
            //$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->SMTPSecure = "tls";
            $mail->Port       = 587;

            // Empfänger
            $mail->setFrom($_SESSION['SERVICE_MAIL'], $_SESSION['PROJECTNAME'] . ' Service Team');
            $mail->addAddress($send_mail, '');
            $mail->addBCC('mdueffelmeyer@caelare.de');


            // Inhalt
            $mail->isHTML(true);
            $mail->Subject = 'Zugangsdaten: ' . explode(' ',$_SESSION['PROJECTNAME'])[0] . ' Dokumentationssystem';
            $html_str .= "<br><br>P.S.: Bitte Antworten Sie nicht an diese E-Mail-Adresse. Wenden Sie sich stattdessen an die Person oder Institution, die den Zugang zum System veranlasst hat.";
            $mail->Body    = $html_str;
            $mail->AltBody = convert_html_to_plain_text($html_str);

            $mail->send();
            echo "<br><br><strong><font color='green'>E-Mail wurde erfolgreich gesendet!</font></strong><br>";
        } catch (Exception $e) {
            echo "<strong><font color='red'>E-Mail konnte nicht gesendet werden. PHPMailer Error: {$mail->ErrorInfo}!</font></strong><br>";
        }
    }
}



