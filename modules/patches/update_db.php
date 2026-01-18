<?php

function test_table($db, $table)
{
    $filefound = 0;
    //$stmt = $db->prepare("SELECT * FROM ".$table." LIMIT 0,1");
    $stmt = $db->prepare("PRAGMA table_info(" . $table . ");");
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    echo print_r($res);
    echo "</pre>";
}

function alter_table($table, $col)
{
    global $db;
    $query = "PRAGMA table_info(" . $table . ");";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $cols_a = [];
    foreach ($res as $row) $cols_a[] = $row['name'];
    if (!in_array($col, $cols_a)) {
        // echo "Spalte 'login_name' existiert nicht, wird hinzugefügt.\n";
        $db->exec("ALTER TABLE " . $table . " ADD COLUMN " . $col . " TEXT;");
    }
}

function alter_forms_table()
{
    global $db;
    $table_a = [];
    $query = "SHOW TABLES;"; // MYSQL
    $query = "SELECT name FROM sqlite_master WHERE type='table' and name like 'forms_%' and name <> 'forms_definition';";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($res as $row) $table_a[] = $row['name'];
    foreach ($table_a as $key => $val) alter_table($val, 'usergroup');
}

if (DB == 'SQLite') {
    // alter_forms_table();
    // alter_table('user_miq', 'email');
    // alter_table('user_miq', 'usergroup');
    // alter_table('forms_definition', 'shortname');
    // alter_table('forms_definition', 'in_view');
    // alter_table('token_store', 'params');
    // alter_table('user_miq', 'init_token');
    // alter_table('user_miq', 'init_token_time');

    // $db->exec("CREATE TABLE IF NOT EXISTS token_store (id INTEGER PRIMARY KEY, master_uid INTEGER, token TEXT UNIQUE, expires_at DATETIME, used INTEGER DEFAULT 0)");

    // $db->exec("DROP VIEW IF EXISTS forms_2_plobview");
    // $db->exec("CREATE VIEW forms_2_plobview AS SELECT tb2.cid, tb2.laytype, tb2.color, tb2.layerId, tb1.fcid, tb1.F_110, tb1.F_120, tb1.F_150, tb1.F_1999, tb3.layName FROM _data_layer AS tb2 LEFT JOIN forms_2_list AS tb1 ON tb1.fcid = tb2.cid LEFT JOIN _data_def_layer AS tb3 ON tb2.layerId = tb3.layerId");
    $projekt = $_SESSION['PROJECTNAME'] ?? "";
    if ($projekt == 'CEDUR'){ 
        // echo "<pre>"; echo print_r($_SESSION); echo "</pre>";exit;
        // $db->exec("DELETE FROM forms_10010 WHERE fid=110905");
        // CREATE INDEX idx_forms_fid ON forms_10010(fid);
        // CREATE INDEX idx_forms_fcid_fid ON forms_10010(fcid, fid);
        // CREATE INDEX idx_forms_fid_90 ON forms_10010(fcid) WHERE fid = 90;
    }

}


