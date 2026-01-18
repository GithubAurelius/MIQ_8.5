<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_SESSION)) session_start();
require_once $_SESSION['INI-PATH'];
require_once ENCRYPTION;

// =====================================================
// Upload Modul
// Standalone using in iframes, returns json
// Saves in db- or file storage
// =====================================================

$fcid = $_GET['fcid'] ?? "";
$fid = $_GET['fid'] ?? "";
$fg = $_REQUEST['fg'] ?? "";
$del = $_REQUEST['del'] ?? "";
$path = $_REQUEST['path'] ?? "";

if (!$fcid || !$fid || !$fg) exit;

// Konfiguration
$uploadDir = UPLOAD_BASE . UPLOAD_SUB_PATH; // kein Webzugriff!
$meta_info  = $uploadDir . $fcid . '_meta_info.json';
$metaFile  = $uploadDir . 'metadata.json';
$allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
$hmtl_allowed_mime_types = "image/*, application/pdf";

// Löschen
if ($del) {
    $file_to_del = UPLOAD_BASE . $path . $del;
    if (file_exists($file_to_del)) unlink($file_to_del);
    $deleted_str = 'DELETED:' . $del;
    $db->exec("UPDATE forms_{$fg} SET fcont = REPLACE(fcont, '{$del}', '{$deleted_str}') WHERE fcid={$fcid} AND fid={$fid}");
}

// Hilfsfunktionen
function loadMeta($f)
{
    global $db, $fg, $fcid, $fid;
    $db_a = get_query_data($db, "forms_{$fg}", "fcid={$fcid} AND fid={$fid}");
    return $db_a ? (json_decode($db_a[0]['fcont'], true) ?: []) : [];
}

function saveMeta($f, $data)
{
    global $db, $fg, $fcid, $fid;
    $meta_jsn = json_encode($data, JSON_UNESCAPED_SLASHES);
    file_put_contents($f, $meta_jsn);

    $fcont_quoted = $db->quote($meta_jsn);
    $db->exec("INSERT INTO forms_{$fg} (fcid, fid, fcont)
                VALUES ({$fcid}, {$fid}, {$fcont_quoted})
                ON DUPLICATE KEY UPDATE fcont = VALUES(fcont)");
}

$meta_file = loadMeta($meta_info);

// Upload-Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'upload') {
    $meta_file = loadMeta($meta_info);
    $results = [];
    foreach ($_FILES['files']['tmp_name'] ?? [] as $i => $tmp) {
        if ($_FILES['files']['error'][$i] !== UPLOAD_ERR_OK) continue;
        $mime = mime_content_type($tmp);
        if (!in_array($mime, $allowed_mime_types)) continue;

        $orig = basename($_FILES['files']['name'][$i]);
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        $hex = bin2hex(random_bytes(8)) . "." . $ext;

        list($targetFile, $fileName, $encodedName, $encrypted) = encrpyt_file(UPLOAD_BASE . UPLOAD_SUB_PATH, $orig, $tmp, SECRET);
        if (file_put_contents($targetFile, $encrypted)) {
            $meta_record = [
                "filePath" => UPLOAD_SUB_PATH,
                "fileNameEncoded" => $encodedName,
                "fileNameOriginal" => $fileName,
                "fileMime" => $mime,
                "fid" => $fid
            ];
            $meta_file[] = $meta_record;
            $results[] = $meta_record;
        }
    }
    saveMeta($meta_info, $meta_file);

    header('Content-Type: application/json');
    echo json_encode(["ok" => true, "records" => $results]);
    exit;
}

$meta_file = loadMeta($meta_info);
$images_found = count($meta_file) ? count($meta_file) : 0;
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <title>Multi-Upload Galerie (Server-serve)</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 10px;
        }

        #dropZone {
            border: 2px dashed #999;
            border-radius: 8px;
            padding: 5px;
            text-align: center;
            cursor: pointer;
            color: #555;
            transition: .3s;
        }

        #dropZone.hover {
            background: #f0f0f0;
            border-color: #666;
        }

        .thumb {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin: 3px;
            cursor: pointer;
            border-radius: 6px;
        }

        #thumbs {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .delete-btn {
            display: block;
            background-color: red;
            color: white;
            text-align: center;
            font-size: 11px;
            text-decoration: none;
            height: 14px;
            line-height: 14px;
            margin-left: 20px;
            width: 70px;
            position: absolute;
            bottom: 0;
            left: 0;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div id="dropZone">Dateien hierher ziehen oder klicken<br><small>(Mehrfachauswahl möglich)</small></div>
    <input type="file" id="fileInput" multiple accept="<?php echo $hmtl_allowed_mime_types ?>" hidden>
    <div id="thumbs"></div>

    <script>
        const fg = <?php echo json_encode($fg) ?>;
        const fcid = <?php echo json_encode($fcid) ?>;
        const fid = <?php echo json_encode($fid) ?>;
        const dropZone = document.getElementById("dropZone");
        const fileInput = document.getElementById("fileInput");
        const thumbs = document.getElementById("thumbs");
        const miq_path_php = <?php echo json_encode(MIQ_PATH_PHP) ?>;
        const miq_path = <?php echo json_encode(MIQ_PATH) ?>;

        // Drag & Drop (nur Desktop)
        dropZone.addEventListener("dragover", e => {
            e.preventDefault();
            dropZone.classList.add("hover");
        });
        dropZone.addEventListener("dragleave", () => dropZone.classList.remove("hover"));
        dropZone.addEventListener("drop", e => {
            e.preventDefault();
            dropZone.classList.remove("hover");
            uploadFiles(e.dataTransfer.files);
        });

        // Klick auf Drop-Zone öffnet File-Dialog
        dropZone.addEventListener("click", () => fileInput.click());

        // Datei-Input Change
        fileInput.addEventListener("change", e => uploadFiles(e.target.files));

        function uploadFiles(files) {
            if (!files || files.length === 0) return;

            const form = new FormData();
            for (let f of files) form.append("files[]", f);
            const url = `?action=upload&fg=${fg}&fcid=${fcid}&fid=${fid}`;

            fetch(url, {
                    method: "POST",
                    body: form,
                    credentials: "include",
                    keepalive: true
                })
                .then(r => r.json())
                .then(j => {
                    if (j.ok) renderEncryptedThumbs(j.records, true);
                })
                .catch(err => {
                    console.warn("Fetch fehlgeschlagen, nutze XHR", err);
                    const xhr = new XMLHttpRequest();
                    xhr.open("POST", url);
                    xhr.onload = () => {
                        try {
                            const data = JSON.parse(xhr.responseText);
                            if (data.ok) renderEncryptedThumbs(data.records, true);
                        } catch (e) {
                            console.error(e);
                        }
                    };
                    xhr.send(form);
                });

            const main_form = parent.document.getElementById('main_form');
            if (main_form) main_form.submit();
        }

        async function get_file_only(path, filename_c, c) {
            if (!filename_c.includes('DELETED')) try {
                const url = miq_path_php + `/parse_img.php?path=${encodeURIComponent(path)}&filename_c=${encodeURIComponent(filename_c)}&c=${c}`;
                const res = await fetch(url);
                if (!res.ok) throw new Error(`HTTP error ${res.status}`);
                const data = await res.json();
                if (!data.file_name) {
                    alert('Datei nicht gefunden!');
                    return null;
                }
                return data;
            } catch (err) {
                console.error(err);
                alert('Fehler beim Laden der Datei!');
                return null;
            }
        }

        function renderEncryptedThumbs(data, append = false) {
            if (!append) thumbs.innerHTML = "";
            Promise.all(data.map(d => get_file_only(d.filePath, d.fileNameEncoded, 1)))
                .then(results => {
                    results.forEach(image_obj => {
                        if (!image_obj) return;
                        const container = document.createElement("div");
                        container.className = "thumb-container";
                        container.style.position = "relative";
                        container.style.display = "inline-block";
                        container.style.margin = "5px";

                        const isPdf = /\.pdf/i.test(image_obj['file_name']);
                        const img = document.createElement("img");
                        img.className = "thumb";
                        img.src = isPdf ? miq_path + 'img/pdf.jpg' : image_obj['file_data'];
                        img.title = image_obj['file_name'];
                        img.style.border = '0.5px solid silver';
                        img.style.display = "block";
                        img.onclick = () => window.open(
                            miq_path_php + `parse_img.php?path=${encodeURIComponent(image_obj['file_path'])}&filename_c=${encodeURIComponent(image_obj['file_name_c'])}&c=1&show_image=1`, "_blank"
                        );

                        const deleteBtn = document.createElement("a");
                        deleteBtn.href = miq_path + `modules/uploads/uploads.php?fg=${fg}&fcid=${fcid}&fid=${fid}&del=${encodeURIComponent(image_obj['file_name_c'])}&path=${encodeURIComponent(image_obj['file_path'])}`;
                        deleteBtn.textContent = "Löschen";
                        deleteBtn.classList.add("delete-btn");
                        deleteBtn.onclick = e => {
                            if (!confirm("Bild wirklich löschen?")) e.preventDefault();
                        };

                        container.appendChild(img);
                        container.appendChild(deleteBtn);
                        thumbs.appendChild(container);
                    });
                });
        }

        // Initial
        renderEncryptedThumbs(<?php echo json_encode($meta_file) ?>);
        if (window.parent) window.parent.postMessage({
            type: "images_found",
            value: <?php echo json_encode($images_found) ?>
        }, "*");


       
    </script>
</body>

</html>