<?php

$fg = 1;
// $db->exec("DROP TABLE IF EXISTS forms_$fg");
$db->exec("CREATE TABLE IF NOT EXISTS forms_$fg (
      fcid INTEGER,
      fid  INTEGER,
      muid INTEGER,
      fcont TEXT,
      mts DATETIME DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (fcid, fid)
      );
");

// $db->exec("DROP TABLE IF EXISTS filedict");
$db->exec("CREATE TABLE IF NOT EXISTS filedict (
	did	INTEGER,
	muid	INTEGER,
	fcid	TEXT,
	fid	INTEGER,
	dfolder	TEXT,
	dfilename	TEXT,
	dcryptname	TEXT,
	dcomment	TEXT,
	ddate1	TEXT,
	mts	DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(did)
      );
");

// $db->exec("DROP TABLE IF EXISTS forms_definition");
$db->exec("CREATE TABLE  IF NOT EXISTS forms_definition (
      fid INTEGER,
      fg INTEGER,
      ftype TEXT,
      fname TEXT,
	  foptions TEXT,
	  ftitle TEXT,
	  mts DATETIME DEFAULT CURRENT_TIMESTAMP
    );
");

// $db->exec("DROP TABLE IF EXISTS user_miq");
$db->exec("CREATE TABLE IF NOT EXISTS user_miq (
	master_uid	INTEGER,
	muid	INTEGER,
	login_name	TEXT,
	login_pass	TEXT,
	mts	DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(master_uid)
      );
");