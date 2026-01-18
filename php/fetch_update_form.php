<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['m_uid'])) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'SESSION gone'
    ]);
    exit;
}

require_once $_SESSION['INI-PATH'];

$fcid   = $_REQUEST['fcid'] ?? 0;
$fg     = $_REQUEST['fg'] ?? 0;
$table  = "forms_" . $fg;

$fid    = $_REQUEST['fid'] ?? 0;
$fid    = str_replace('FF_', '', $fid);

$fcont  = $_REQUEST['fcont'] ?? "";
if ($fcont !== null) {
    $fcont = trim($fcont);
}

$status = ins_or_rep_form('', $db, $fg, $fcid, '', $fid, $fcont);

    // // Verifizieren: den neuen Wert zurückliefern
    // $stmt = $db->prepare("SELECT * FROM $table WHERE fcid=:fcid AND fid=:fid");
    // $stmt->execute([':fcid' => $fcid, ':fid' => $fid]);
    // $row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($status) 
    echo json_encode([
        "status"  => "ok",
        "message" => "saved ", //  $row ? 'Gespeichert' : 'Gelöscht',
        "data"    => $fid." ".$fcont
    ]);
else 
    echo json_encode([
        "status"  => "error",
        "message" => "DB-error: " . $e->getMessage(),
        "data"  => ""
    ]);

