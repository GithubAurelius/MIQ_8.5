<?php
session_start();

ob_start();
// include $_SESSION['FS_ROOT'].'MIQ_8.3/modules/login_token/login_token.php?key=20250620120639'; 
$html_content = file_get_contents('http://localhost/MIQ_8.3/modules/login_token/login_token.php?key=20250620120639');
// Den gesamten Ausgabepuffer in eine Variable speichern
$html_string = ob_get_clean();


// Für Testzwecke können Sie den HTML-String ausgeben:
echo $html_string;