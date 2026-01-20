<?php

date_default_timezone_set('Europe/Berlin');

define('SECRET_KEY', 'Caelare2020_MarcDueffelmeyer_MIQ_8.x');
define('CIPHER_METHOD', 'aes-256-cbc');

define("DEBUG", 0);
define("MIQ", $_SESSION["MIQ"]);
define("VER", "1");

define("PROJECT_PATH", $_SESSION['WEBROOT'] . $_SESSION['PROJECT_PATH']);

define("DB", "MariaDB");
// define("DB", "SQLite");

define("MIQ_ROOT", $_SESSION["FS_ROOT"] . MIQ . "/");
define("MIQ_ROOT_PHP", MIQ_ROOT . "php/");

define("MIQ_PATH", $_SESSION["WEBROOT"] . MIQ . "/");
define("MIQ_PATH_PHP", MIQ_PATH . "php/");
define("ENCRYPTION", MIQ_ROOT_PHP . "encryption.php");
define("MIQ_DATA", $_SESSION["DATAROOT"]);

if (DB == "MariaDB") {
    $db = new PDO("mysql:host=" . $_SESSION['DB_HOST'] . ";dbname=" . $_SESSION['DB_NAME'], $_SESSION['DB_USER'], $_SESSION['DB_PASS']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db_audit = new PDO("mysql:host=" . $_SESSION['DB_HOST'] . ";dbname=" . $_SESSION['DB_NAME'] . "_audit", $_SESSION['DB_USER'], $_SESSION['DB_PASS']);
    $db_audit->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $_SESSION['DB_type'] = 'Multiuser';
} else {
    define("DB_PATH", MIQ_DATA . $_SESSION["PROJECT"] . "/db/" . $_SESSION["DB_MAIN"]);
    define("DB_PLOB_PATH", MIQ_DATA . $_SESSION["PROJECT"] . "/db/" . $_SESSION["DB_MAIN"]);
    $db = new PDO("sqlite:" . DB_PATH);
    $_SESSION['DB_type'] = 'Singleuser';
}

// TODO possibly recheck only for files with upload usage
define("UPLOAD_BASE", MIQ_DATA . $_SESSION["PROJECT"] . "/uploads/");
define("UPLOAD_SUB_PATH", date("Y_m") . "/");
define("TEMP", $_SESSION["FS_ROOT"] . $_SESSION["PROJECT_PATH"] . "temp/");
define("PLOG", $_SESSION["FS_ROOT"] . $_SESSION["PROJECT_PATH"] . "temp/PLOG/");
define("EXPORT", $_SESSION["FS_ROOT"] . $_SESSION["PROJECT_PATH"] . "temp/export/");
define("STATS", $_SESSION["FS_ROOT"] . $_SESSION["PROJECT_PATH"] . "temp/statistics/");
define("TEMP_WEB", $_SESSION["WEBROOT"] . $_SESSION["PROJECT_PATH"] . "temp/");

function debug($debug_xstr, $show = 0)
{
    global $debug_str;
    if (DEBUG) $debug_str .= $debug_xstr;
    if ($show) echo "<div id='debug'>" . $debug_str . "</div>";
}

function check_path_change($dir)
{
    $path_now = str_replace('\\', '/', $dir) . '/';
    $path_logged_in = $_SESSION['FS_ROOT'] . $_SESSION['PROJECT_PATH'];
    if ($path_now != $path_logged_in) {
        echo "Unerlaubter Systemwechseln ohne Login von " . $path_logged_in . " nach " . $path_now . ".<br>Sie werden abgemeldet!";
        echo "<br><br><a href='" . $_SESSION['WEBROOT'] . $_SESSION['PROJECT_PATH'] . "login.php'>Anmelden</a>";
        session_destroy();
        exit;
    }
}

function simple_encrypt($plaintext)
{
    $iv_length = openssl_cipher_iv_length(CIPHER_METHOD);
    $iv = openssl_random_pseudo_bytes($iv_length);
    $ciphertext = openssl_encrypt($plaintext, CIPHER_METHOD, SECRET_KEY, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $ciphertext);
}

function simple_decrypt($encrypted_data)
{
    $data = base64_decode($encrypted_data);
    $iv_length = openssl_cipher_iv_length(CIPHER_METHOD);
    $iv = substr($data, 0, $iv_length);
    $ciphertext = substr($data, $iv_length);
    return openssl_decrypt($ciphertext, CIPHER_METHOD, SECRET_KEY, OPENSSL_RAW_DATA, $iv);
}

function get_query_data($db, $table, $query_add = '')
{
    $form_data_a = [];
    $query = "SELECT * FROM " . $table . " WHERE " . $query_add;
    // echo $query;
    $stmt = $db->prepare($query);
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $res;
}

function get_fcid_a($temp_a)
{
    $fcid_a = [];
    foreach ($temp_a as $key => $fid_a)
        $fcid_a[$fid_a['fcid']][$fid_a['fid']] = $fid_a['fcont'];
    return $fcid_a;
}

function get_form_data($db, $fg, $fcid, $query_add = '')
{
    $form_data_a = [];
    $query = "SELECT * FROM forms_$fg WHERE fcid=:fcid " . $query_add;
    $stmt = $db->prepare($query);
    $stmt->bindValue(':fcid', $fcid);
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($res as $row) {
        $form_data_a[$row["fid"]] = trim($row["fcont"] ?? "");
    }
    return $form_data_a;
}

function show_a($a)
{
    return '<pre>' . var_export($a, true) . '</pre>';
}

function ins_or_rep_form($ts, $db, $fg, $fcid, $muid, $fid, $fcont)
{
    global $db_audit, $old_form_data_a;
    // Timestamp aktualisieren
    $ts = date("Y-m-d H:i:s");
    
    // Benutzer-ID sicher ermitteln
    if (!$muid) $muid = $_SESSION['uid'] ?? null;
    if (!$muid) $muid = $_SESSION['m_uid'] ?? null;

    $usergroup = $_SESSION['user_group'] ?? 0;

    // Email-Feld NICHT speichern
    if ($fid == 10003040) {
        return 1;
    }
    
    // Leere Inhalte als NULL speichern
    $fcont = (trim($fcont ?? '') === '') ? null : $fcont;

    try {

        /* ----------------------------------------------------------
         * INSERT OR UPDATE in forms_$fg
         * ---------------------------------------------------------- */
        $sql_main = "
            INSERT INTO forms_$fg (fcid, muid, fid, fcont, usergroup, mts)
            VALUES (:fcid, :muid, :fid, :fcont, :usergroup, :mts)
            ON DUPLICATE KEY UPDATE
                muid = VALUES(muid),
                fcont = VALUES(fcont),
                usergroup = VALUES(usergroup),
                mts = VALUES(mts)
        ";
        $stmt = $db->prepare($sql_main);
        $stmt->execute([
            ':fcid'      => $fcid,
            ':muid'      => $muid,
            ':fid'       => $fid,
            ':fcont'     => $fcont,
            ':usergroup' => $usergroup,
            ':mts'       => $ts
        ]);
        
        /* ----------------------------------------------------------
         * Audit-Log nur schreiben wenn Wert geändert wurde
         * ---------------------------------------------------------- */
        $old_fcont = $old_form_data_a[$fid] ?? null;
        if ($fcont !== $old_fcont && $fid != 100 && $fid != 101 && $fid != 102) {
            $sql_audit = "
                INSERT INTO forms_audit (fg, fcid, muid, fid, fcont, usergroup, mts)
                VALUES (:fg, :fcid, :muid, :fid, :fcont, :usergroup, :mts)
            ";
            $stmt_a = $db_audit->prepare($sql_audit);
            $stmt_a->execute([
                ':fg'        => $fg,
                ':fcid'      => $fcid,
                ':muid'      => $muid,
                ':fid'       => $fid,
                ':fcont'     => $fcont,
                ':usergroup' => $usergroup,
                ':mts'       => $ts
            ]);
        }
        return 1;
    } catch (Exception $e) {
        // FEHLERMELDUNG zurückgeben (nicht 0)
        return "ERROR: " . $e->getMessage();
    }
}

if (!isset($_SESSION['user_group'])) $_SESSION['user_group'] = 0;
