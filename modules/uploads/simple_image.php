<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_SESSION)) session_start();
require_once $_SESSION['INI-PATH'];
require_once ENCRYPTION;
// =====================================================
// Bildanzeige aus Nicht-Webverzeichnis
// =====================================================

// Konfiguration
$uploadDir = UPLOAD_BASE.UPLOAD_SUB_PATH; // kein Webzugriff!
$name = basename($_GET['name'] ?? '');
$thumb = isset($_GET['thumb']);
$file = realpath("$uploadDir/$name");

if (!$name || !$file || strpos($file, realpath($uploadDir)) !== 0 || !is_file($file)) {
    http_response_code(404);
    exit("Not found");
}

// Prüfe MIME
$mime = mime_content_type($file);
header("Content-Type: $mime");

// Für "Thumbnails" – einfach verkleinern per CSS, kein echtes Resize nötig,
// aber wir senden Cache-Control-Header
header("Cache-Control: public, max-age=3600");

// Lies Datei
readfile($file);
