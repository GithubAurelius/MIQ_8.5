<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_SESSION)) session_start();
require_once $_SESSION['INI-PATH'];

header('Content-Type: application/json');

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!isset($data['key_name']) || !isset($data['main_table']) || empty($data['main_table'])) {
    echo json_encode(['success' => false, 'message' => 'Fehlende oder ungültige Daten.']);
    exit;
}

$keyName = $data['key_name'];
$tableName = $data['main_table'];

$fcid_ts = date('YmdHis');
$fcid = (int) ($fcid_ts . substr(microtime(true), 11, 2));

$key_val = $fcid;


try {
    $sql = "INSERT IGNORE INTO " . $tableName . " (" . $keyName . ") 
            VALUES (:key_val)";
    $stmt = $db->prepare($sql);
    $stmt->execute([':key_val' => $key_val]);
    $affectedRows = $stmt->rowCount();
    if ($affectedRows > 0) {
        $success = true;
        $message = 'Eintrag erfolgreich hinzugefügt.';
    } else {
        $success = false;
        $message = 'Eintrag wurde ignoriert (Schlüssel existiert bereits).';
    }

    echo json_encode(['success' => $success, 'message' => $message]);
} catch (PDOException $e) {
    error_log("DB-FEHLER IN table_insert: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Datenbankfehler aufgetreten.',
        'error_detail' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Sonstige FEHLER IN table_insert: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Ein allgemeiner Fehler ist aufgetreten.',
        'error_detail' => $e->getMessage()
    ]);
}
