<?php

function clean_input($string)
{
    $string = preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $string);
    $string = trim($string);
    return $string;
}

function check_token($db, $token)
{
    $query = "SELECT * FROM token_store WHERE token = :token";
    $stmt = $db->prepare($query);
    $stmt->execute([':token' => $token]);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result_a = [];
    $t_a = [];
    if (count($res) > 0) {
        $t_a = $res[0];
        if ($t_a['used'] == 1) $result_a = [0, 'used'];
        else if ($t_a['expires_at'] < date('Y-m-d H:i:s')) $result_a = [0, 'elapsed'];
        else {
            $result_a = [1, $t_a['params']];
        }
    } else $result_a = [0, 'not found/ invalid'];
    // echo "<pre>"; echo print_r($t_a); echo "</pre>";
    return $result_a;
}

function security_check_login($user, $pass)
{
    $u = 0;
    $p = 0;
    if ($user && $pass) {
        if (strlen($user) < 50)
            $u = filter_var($user, FILTER_VALIDATE_EMAIL);
        $u = htmlspecialchars($u, ENT_QUOTES, 'UTF-8');
        if (strlen($pass) >= 8) {
            $p = htmlspecialchars($pass, ENT_QUOTES, 'UTF-8');
            $p = md5($p);
        }
    }
    return (array($u, $p));
}

function Get_User_Ip()
{
    $IP = false;
    if (getenv('HTTP_CLIENT_IP')) {
        $IP = getenv('HTTP_CLIENT_IP');
    } else if (getenv('HTTP_X_FORWARDED_FOR')) {
        $IP = getenv('HTTP_X_FORWARDED_FOR');
    } else if (getenv('HTTP_X_FORWARDED')) {
        $IP = getenv('HTTP_X_FORWARDED');
    } else if (getenv('HTTP_FORWARDED_FOR')) {
        $IP = getenv('HTTP_FORWARDED_FOR');
    } else if (getenv('HTTP_FORWARDED')) {
        $IP = getenv('HTTP_FORWARDED');
    } else if (getenv('REMOTE_ADDR')) {
        $IP = getenv('REMOTE_ADDR');
    }
    //If HTTP_X_FORWARDED_FOR == server ip
    if ((($IP) && ($IP == getenv('SERVER_ADDR')) && (getenv('REMOTE_ADDR')) || (!filter_var($IP, FILTER_VALIDATE_IP)))) {
        $IP = getenv('REMOTE_ADDR');
    }
    if ($IP) {
        if (!filter_var($IP, FILTER_VALIDATE_IP)) {
            $IP = false;
        }
    } else {
        $IP = false;
    }
    return $IP;
}

function logged_in_settings($db, $uid)
{
    $_SESSION['uid'] = $uid;
    if ($_SESSION['uid'] < 10) $_SESSION['readonly'] = 0;
    $_SESSION['username'] = $_SESSION['user_name'];
    $_SESSION['userIp'] = Get_User_Ip();
    // $_SESSION['session_timeout'] = 7200;
    // $lifetime = 2592000;
    // Setzt die Cookie-Parameter:
    // 1. Lebensdauer des Cookies
    // 2. Pfad, für den der Cookie gültig ist
    // 3. Domain, für die der Cookie gültig ist (kann leer gelassen werden)
    // 4. Secure-Flag (nur HTTPS)
    // 5. HttpOnly-Flag (schützt vor XSS)
    // session_set_cookie_params($lifetime, '/', null, true, true);
    // $_SESSION['startLog'] = date('Y-m-d H:i:s', $_SESSION['last_activity']);
    $_SESSION['startLog'] = date('Y-m-d H:i:s');
    $stmt = $db->prepare("REPLACE INTO user_miq_log (muid, logged_in, logged_out, ip_address) VALUES ('" . $_SESSION['uid'] . "', '" . $_SESSION['startLog'] . "', '" . date('Y-m-d H:i:s') . "', '" . $_SESSION['userIp'] . "')");
    $stmt->execute();
}

function logged_in_failed($db, $failname)
{
    $_SESSION['userIp'] = Get_User_Ip();
    $stmt = $db->prepare("REPLACE INTO user_miq_log_fail (username, ip_address) VALUES (:username,  '" . $_SESSION['userIp'] . "')");
    $stmt->bindParam(':username', $failname);
    $stmt->execute();
}

function check_login($db, $post_a)
{
    $_SESSION['logged_in'] = 0;
    if (trim($post_a['username'] ?? "") == "" || trim($post_a['password'] ?? "") == "") return 0;
    $list_a = [];
    $user = strtolower(clean_input($post_a['username']));
    $pass = clean_input($post_a['password']);
    // first Check if the user send token
    // check_token($db, $pass);
    // $stmt = $db->prepare("SELECT * FROM token_store WHERE token = :pass");
    // $stmt->bindParam(':pass', $pass);
    // $stmt->execute();
    // $result = $stmt->fetch(PDO::FETCH_ASSOC);
    // if ($result) echo $result['master_uid'];
    // list($user, $pass) = security_check_login($user, $pass);
    $stmt = $db->prepare("SELECT * FROM user_miq WHERE LOWER(login_name) = :login_name");
    $stmt->bindParam(':login_name', $user);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $temp_pass = $result['login_pass'] ?? '';
    if (
        password_verify($pass, $temp_pass)
        || $temp_pass === md5($pass) // Fallback für MD5 erschlüsselte Passwörter
        || $temp_pass === $pass // für Initialzugriff 
    ) {
        $_SESSION['m_uid'] = $result['master_uid'];
        $_SESSION['user_name'] = $result['login_name'];
        $_SESSION['user_group'] = $result['usergroup'];
        $_SESSION['last_activity'] = time();
        $_SESSION['password_expired'] = 0; // TODO: CHECK continuous
        $_SESSION['logged_in'] = 1;
        if ($result['rights']) {
            $group_a = explode(",", $result['rights']);
            foreach ($group_a as $rights_to_group_a) {
                $rights_to_group = explode("-", $rights_to_group_a);
                $_SESSION['rl'][$rights_to_group[0]] = $rights_to_group[1] ?? 0;;
            }
        }
        if (!isset($_SESSION['rl'])) $_SESSION['logged_in'] = 0;
        if ($pass == $temp_pass) {  
            $_SESSION['logged_in'] = 1;
            $_SESSION['password_expired'] = 1;
        }
    } else {
        $fail_login = $_POST['username'] ?? "-";
        $fail_pass = $_POST['password'] ?? "-";
        $fail_login = htmlspecialchars($fail_login, ENT_QUOTES, 'UTF-8');
        $fail_pass = htmlspecialchars($fail_pass, ENT_QUOTES, 'UTF-8');
        logged_in_failed($db, $fail_login.' @x@ ' .$fail_pass);
    }
    
    
}

$message = "";

$token = $_REQUEST['t'] ?? 0;
$tl =  $_REQUEST['tl'] ?? 0; 

if ($token) {
    echo "TOKEN invalid!";
    $token_answer_a = check_token($db, $token);
    if ($token_answer_a) {
        $param_str = $token_answer_a[1];
        if ($param_str) {
            $param_a = json_decode(urldecode(base64_decode($param_str)), true);
            $goto_index = $param_a['direct'] ?? 0;
            if ($goto_index) {
                $_SESSION['logged_in'] = 1;
                $_SESSION['temp_token'] = $token; // wird gesetzt um beim Energiesparmodus der tablets neu zu verbinden
                logged_in_settings($db, $param_a['pid']);
                $_SESSION['temp_params_a'] = $param_a;
                //echo "<pre>"; echo print_r($_SESSION); echo "</pre>"; 
                header('Location: index.php');
            }
        } else exit;
    } else exit;
}

elseif ($tl){
    $tl_str = base64_decode($tl);
    $tmp_a = json_decode($tl_str, true);    
    check_login($db, $tmp_a);
    if ($_SESSION['logged_in']) {
        logged_in_settings($db, $_SESSION['m_uid']);
        header('Location: index.php');
    } else $message .= "<font style='color:red'>Benutzername oder Passwort fehlerhaft oder unzureichende Rechte!</font>";
} elseif ($_POST) {
    // $result = check_token($db, clean_input($_POST['password']));
    // if ($result == 'used') $message = "<font style='color:red'>Token/ QR-Code wurde bereits benutzt.</font><br>";
    // else if ($result == 'elapsed') $message = "<font style='color:red'>Token/ QR-Code  ist abgelaufen.</font><br>";
    // else if ($result == 'not found/ invalid') $message = "<font style='color:red'>Token/ QR-Code  nicht gefunden oder ungültig.</font><br>";
    // else if ($result) header("Location: " . MIQ_PATH . "modules/change_userdata/change_userdata.php");

    check_login($db, $_POST);
    if ($_SESSION['logged_in']) {
        logged_in_settings($db, $_SESSION['m_uid']);
        header('Location: index.php');
    } else $message .= "<font style='color:red'>Benutzername oder Passwort fehlerhaft oder unzureichende Rechte!</font>";
}
