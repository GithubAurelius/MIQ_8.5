<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once $_SESSION['INI-PATH'];

// Mindestlängen für Passwörter
$min_length_old = 8;
$min_length_new = 10;

$error = '';
$success = '';

// Funktion zum Überprüfen des alten Passworts
function verifyPassword($db, $master_uid, $old_password)
{
    $stmt = $db->prepare("SELECT login_pass FROM user_miq WHERE master_uid = :master_uid");
    $stmt->bindParam(':master_uid', $master_uid);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (
        $result && (
            password_verify($old_password, $result['login_pass'])
            || md5($old_password) === $result['login_pass'] // Fallback für MD5 erschlüsselte Passwörter

        )
    ) {
        return true;
    }
    return false;
}

// Funktion zum Überprüfen des Initialpassworts
function verifyInitPassword($db, $master_uid, $old_password)
{
    $stmt = $db->prepare("SELECT login_pass FROM user_miq WHERE master_uid = :master_uid");
    $stmt->bindParam(':master_uid', $master_uid);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result && ($old_password) === $result['login_pass']) {
        return true;
    }
    return false;
}

// Funktion zum Aktualisieren des Passworts
function updatePassword($db, $master_uid, $new_password_hash)
{
    $stmt = $db->prepare("UPDATE user_miq SET login_pass = :new_password WHERE master_uid = :master_uid");
    $stmt->bindParam(':new_password', $new_password_hash);
    $stmt->bindParam(':master_uid', $master_uid);
    return $stmt->execute();
}

if (!isset($_SESSION['m_uid'])) {
    $error = 'Sie sind nicht angemeldet.';
}

// Verarbeitung des Formulars
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Stellen Sie sicher, dass der Benutzer angemeldet ist und die master_uid in der Session existiert

    $master_uid = $_SESSION['m_uid'];
    if (!isset($_SESSION['token']) || trim(chop($_SESSION['token'])) == "") $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $error = "";
    if (!isset($_SESSION['token']) || trim(chop($_SESSION['token'])) == "") {
        if (strlen($old_password) < $min_length_old) {
            $error .= 'Das alte Passwort muss mindestens ' . $min_length_old . ' Zeichen lang sein.';
        }
        if ($_SESSION['password_expired']) {
            if (!verifyInitPassword($db, $master_uid, $old_password)) {
                if ($error) $error .= '<br>';
                $error .= 'Das Initial-Passwort ist falsch.';
            }
        } else {
            if (!verifyPassword($db, $master_uid, $old_password)) {
                if ($error) $error .= '<br>';
                $error .= 'Das alte Passwort ist falsch.';
            }
        }
        if ($old_password === $new_password) {
            if ($error) $error .= '<br>';
            $error .= 'Das neue Passwort darf nicht identisch mit dem alten Passwort sein.';
        }
    }
    if (strlen($new_password) < $min_length_new) {
        if ($error) $error .= '<br>';
        $error .= 'Das neue Passwort muss mindestens ' . $min_length_new . ' Zeichen lang sein.';
    }
    if ($new_password !== $confirm_password) {
        if ($error) $error .= '<br>';
        $error .= 'Die neuen Passwörter stimmen nicht überein.';
    }
    if (!preg_match('/[A-Z]/', $new_password)) {
        if ($error) $error .= '<br>';
        $error .= 'Das neue Passwort muss mindestens einen Großbuchstaben enthalten.';
    }
    if (!preg_match('/[a-z]/', $new_password)) {
        if ($error) $error .= '<br>';
        $error .= 'Das neue Passwort muss mindestens einen Kleinbuchstaben enthalten.';
    }
    if (!preg_match('/[\W_]/', $new_password)) {
        if ($error) $error .= '<br>';
        $error .= 'Das neue Passwort muss mindestens ein Sonderzeichen enthalten.';
    }
    if (!$error) {
        // Passwort hashen und in der Datenbank aktualisieren
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        if (updatePassword($db, $master_uid, $new_password_hash)) {
            if (isset($_SESSION['token']))
                $db->exec("UPDATE token_store SET used=1 WHERE master_uid = $master_uid AND token = '{$_SESSION['token']}'");
            $success = "Das Passwort wurde erfolgreich geändert.<br><br>Bitte melden Sie sich hier (erneut) an: <strong><a href='" . $_SESSION['WEBROOT'] . $_SESSION['PROJECT_PATH'] . "login.php'>" . $_SESSION['WEBROOT'] . $_SESSION['PROJECT_PATH'] . "login.php</a></strong>";
        } else {
            $error = 'Fehler beim Aktualisieren des Passworts.';
        }
    }
};
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passwort speichern</title>
    <link rel="stylesheet" href="<?php echo MIQ_PATH ?>/css/passwordchange.css">
</head>
<body>
    <?php if (isset($_SESSION['m_uid']) && $_SESSION['m_uid'] != "") { ?>
        <table>
            <tr>
                <td>
                    <?php if ($error): ?>
                        <div class="error"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="success"><?php echo $success; ?></div>
                    <?php else: ?>
                </td>
            </tr>
            <tr>
                <td>
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                        <h2>
                            <center>Passwort Änderung</center>
                        </h2>
                        <?php if (!isset($_SESSION['token']) || trim(chop($_SESSION['token'])) == "") { ?>
                            <div class="form-group">
                                <label for="old_password">Altes Passwort oder Initial-Passwort:</label>
                                <div class="password-wrapper">
                                    <input type="password" id="old_password" name="old_password" required>
                                </div>
                            </div>
                        <?php } ?>
                        <div class="form-group">
                            <label for="new_password">Ihr neues Passwort:</label>
                            <div class="password-wrapper">
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Neues Passwort bestätigen:</label>
                            <div class="password-wrapper">
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        <button type="submit">Passwort ändern</button>
                    </form>
                </td>
            </tr>
        <?php endif; ?>
        </table>
    <?php } ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Alle Wrapper-Elemente für die Passwortfelder finden
        const passwordWrappers = document.querySelectorAll('.password-wrapper');
        passwordWrappers.forEach(wrapper => {
            const passwordInput = wrapper.querySelector('input[type="password"]');
            if (passwordInput) {
                // 1. Umschalt-Schaltfläche erstellen (mit Emoji als Symbol)
                const toggleButton = document.createElement('button');
                toggleButton.setAttribute('type', 'button');
                toggleButton.setAttribute('class', 'toggle-password');
                // Für bessere Barrierefreiheit (Screenreader)
                toggleButton.setAttribute('aria-label', 'Passwort anzeigen'); 
                toggleButton.setAttribute('aria-pressed', 'false');
                toggleButton.innerHTML = '👁️'; // Auge-Symbol
                // Schaltfläche zum Wrapper hinzufügen
                wrapper.appendChild(toggleButton);
                // 2. Klick-Ereignis-Handler hinzufügen
                toggleButton.addEventListener('click', function() {
                    // Überprüfen, welchen Typ das Feld gerade hat
                    const isPasswordHidden = passwordInput.type === 'password';

                    if (isPasswordHidden) {
                        // Zeigen: type='text'
                        passwordInput.type = 'text';
                        toggleButton.innerHTML = '🙈'; // Auge-Symbol wechseln
                        toggleButton.setAttribute('aria-label', 'Passwort verstecken');
                        toggleButton.setAttribute('aria-pressed', 'true');
                    } else {
                        // Verstecken: type='password'
                        passwordInput.type = 'password';
                        toggleButton.innerHTML = '👁️'; // Auge-Symbol zurück
                        toggleButton.setAttribute('aria-label', 'Passwort anzeigen');
                        toggleButton.setAttribute('aria-pressed', 'false');
                    }
                });
            }
        });
    });
</script>
</body>
</html>