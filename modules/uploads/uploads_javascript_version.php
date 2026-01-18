<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_SESSION)) session_start();
require_once $_SESSION['INI-PATH'];
require_once ENCRYPTION;
// =====================================================
// Upload Modul
// Sstandalone using in iframes, returns json
// Saves in db- or file storage
// =====================================================

// TEST: https://zi-mh.miqdoc.de/MIQ_8.5/modules/uploads/uploads.php?fg=10100&fid=51&fcid=2025120923183898

// $fcid = 111111;
// $fid = 50;
// $fg = 10100;

$fcid = $_GET['fcid'] ?? "";
$fid = $_GET['fid'] ?? "";
$fg = $_REQUEST['fg'] ?? "";

$del = $_REQUEST['del'] ?? "";
$path = $_REQUEST['path'] ?? "";

if (!$fcid) exit;
if (!$fid)  exit;
if (!$fg) exit;

// Konfiguration
$uploadDir = UPLOAD_BASE . UPLOAD_SUB_PATH; // kein Webzugriff!
$meta_info  = $uploadDir . $fcid . '_meta_info.json';

$metaFile  = $uploadDir . 'metadata.json';
// $maxFileSize = 100 * 1024 * 1024; // 10 MB
$allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
$hmtl_allowed_mime_types = "image/*, application/pdf";

if ($del) {
    $file_to_del = UPLOAD_BASE . $path . $del;
    if (file_exists($file_to_del)) unlink($file_to_del);
    // $db->exec("UPDATE forms_{$fg} WHERE fcid={$fcid} AND fid={$fid}");
    $deleted_str = 'DELETED:' . $del;
    $db->exec("UPDATE forms_{$fg} SET fcont = REPLACE(fcont, '{$del}', '{$deleted_str}') WHERE fcid={$fcid} AND fid={$fid}");
}


// Hilfsfunktionen
function loadMeta($f)
{
    global $db, $fg, $fcid, $fid;
    $db_a = get_query_data($db, "forms_{$fg}", "fcid={$fcid} AND fid={$fid}");
    return $db_a ? (json_decode($db_a[0]['fcont'], true) ?: []) : [];
    // return file_exists($f) ? (json_decode(file_get_contents($f), true) ?: []) : []; // file_version
}

function saveMeta($f, $data)
{
    global $db, $fg, $fcid, $fid;
    // $meta_jsn = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); // better reading in files
    $meta_jsn = json_encode($data, JSON_UNESCAPED_SLASHES);
    file_put_contents($f, $meta_jsn);

    $fcont_quoted = $db->quote($meta_jsn);
    $db->exec("INSERT INTO forms_{$fg} (fcid, fid, fcont)
                VALUES ({$fcid}, {$fid}, {$fcont_quoted})
                ON DUPLICATE KEY UPDATE fcont = VALUES(fcont)");
}

$meta_file = loadMeta($meta_info); // echo "METAFILE:"; echo "<pre>"; echo print_r($meta_file); echo "</pre>";


// Upload-Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'upload') {
    // $meta = loadMeta($metaFile); // uncrypted version
    $meta_file = loadMeta($meta_info);


    $results = [];
    foreach ($_FILES['files']['tmp_name'] ?? [] as $i => $tmp) {
        if ($_FILES['files']['error'][$i] !== UPLOAD_ERR_OK) continue;
        $mime = mime_content_type($tmp);
        if (!in_array($mime, $allowed_mime_types)) continue;

        $orig = basename($_FILES['files']['name'][$i]);
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        $hex = bin2hex(random_bytes(8)) . "." . $ext;
        $target = $uploadDir . $hex;
        

        if (file_put_contents($targetFile, $encrypted)) {
            debug("Die Datei $fileName wurde erfolgreich hochgeladen, verschlüsselt und als $encodedName gespeichert.<br>");
            // ins_or_rep_filedict($ts, $db, ++$did, $muid, $fcid, $fid, UPLOAD_SUB_PATH, $fileName, $encodedName);
            // $file_add_a[] = $did;
            $meta_record = [
                "filePath" => UPLOAD_SUB_PATH,
                "fileNameEncoded" => $encodedName,
                "fileNameOriginal" => $fileName,
                "fileMime" => $mime,
                "fid" => $fid
            ];
            $meta_file[] = $meta_record;
            $results[] = $meta_record;
        } else {
            debug("Fehler beim Hochladen der Datei $fileName.<br>");
        }

        /*** uncrypted version ***/
        // move_uploaded_file($tmp, $target);
        // chmod($target, 0644);
        // $rec = [
        //     "path" => $target,
        //     "originalName" => $orig,
        //     "hexName" => $hex,
        //     "uploadedAt" => date('c')
        // ];
        // $meta[] = $rec;
        // $results[] = $rec;
    }

    saveMeta($meta_info, $meta_file);

    // saveMeta($metaFile, $meta); // uncrypted version
    header('Content-Type: application/json');
    echo json_encode(["ok" => true, "records" => $results]);
    exit;
}

// Metadaten laden
// $meta = loadMeta($metaFile); // uncrypted version
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

        dropZone.addEventListener("click", () => fileInput.click());
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
        fileInput.addEventListener("change", e => uploadFiles(e.target.files));

        function uploadFiles(files) {
            const form = new FormData();
            for (let f of files) form.append("files[]", f);
            const url = `?action=upload&fg=${fg}&fcid=${fcid}&fid=${fid}`;
            fetch(url, {
                    method: "POST",
                    body: form,
                    credentials: "include", // wichtig, wenn Sessions genutzt werden
                    keepalive: true         // verhindert Abbruch bei Navigation
                })
                .then(r => r.json())
                .then(j => {
                    if (j.ok) renderEncryptedThumbs(j.records, true);
                });
            const main_form = parent.document.getElementById('main_form');  
            if (main_form) main_form.submit();
        }

        // function renderThumbs(data, append = false) {
        //     if (!append) thumbs.innerHTML = "";
        //     data.forEach(d => {
        //         const img = document.createElement("img");
        //         img.className = "thumb";
        //         img.src = "simple_image.php?name=" + encodeURIComponent(d.hexName) + "&thumb=1";
        //         img.title = d.originalName;
        //         img.onclick = () => window.open("simple_image.php?name=" + encodeURIComponent(d.hexName));
        //         thumbs.appendChild(img);
        //     });
        // }

        async function get_file_only(path, filename_c, c) { // copy from get_file in js_image_layer.js
            if (!filename_c.includes('DELETED'))
                try {
                    const url = miq_path_php + `/parse_img.php?path=${encodeURIComponent(path)}&filename_c=${encodeURIComponent(filename_c)}&c=${c}`;
                    // console.log(url);
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
            Promise.all(
                data.map(d => get_file_only(d.filePath, d.fileNameEncoded, 1))
            ).then(results => {
                results.forEach(image_obj => {
                    if (!image_obj) return;

                    // Container für Thumbnail + Button
                    const container = document.createElement("div");
                    container.className = "thumb-container";
                    container.style.position = "relative";
                    container.style.display = "inline-block";
                    container.style.margin = "5px";

                    // Bild
                    const isPdf = /\.pdf/i.test(image_obj['file_name']);
                    const img = document.createElement("img");
                    img.className = "thumb";
                    if (isPdf) img.src = miq_path + 'img/pdf.jpg';
                    else img.src = image_obj['file_data'];
                    img.title = image_obj['file_name'];
                    img.style.border = '0.5px solid silver';
                    img.style.display = "block"; // damit Button darunter klebt
                    img.onclick = () => window.open(
                        miq_path_php + `parse_img.php?path=${encodeURIComponent(image_obj['file_path'])}&filename_c=${encodeURIComponent(image_obj['file_name_c'])}&c=1&show_image=1`,
                        "_blank"
                    );
                    
                    // console.log(miq_path + `modules/uploads/uploads.php?fg=${fg}&fcid=${fcid}&fid=${fid}&del=${encodeURIComponent(image_obj['file_name_c'])}&path=${encodeURIComponent(image_obj['file_path'])}`);
                    // Lösch-Button als Link
                    const deleteBtn = document.createElement("a");
                    deleteBtn.href = miq_path + `modules/uploads/uploads.php?fg=${fg}&fcid=${fcid}&fid=${fid}&del=${encodeURIComponent(image_obj['file_name_c'])}&path=${encodeURIComponent(image_obj['file_path'])}`;
                    deleteBtn.textContent = "Löschen";
                    deleteBtn.classList.add("delete-btn");
                    // deleteBtn.style.display = "block";
                    // deleteBtn.style.backgroundColor = "red";
                    // deleteBtn.style.color = "white";
                    // deleteBtn.style.textAlign = "center";
                    // deleteBtn.style.fontSize = "11px";
                    // deleteBtn.style.textDecoration = "none";
                    // deleteBtn.style.height = "14px";
                    // deleteBtn.style.lineHeight = "14px"; // Text zentrieren
                    // deleteBtn.style.marginLeft = "20px";
                    // deleteBtn.style.width = "70px"; // passt sich der Breite des Thumbnails an
                    // deleteBtn.style.position = "absolute";
                    // deleteBtn.style.bottom = "0";
                    // deleteBtn.style.left = "0";
                    // deleteBtn.style.borderRadius = "4px";
                    deleteBtn.onclick = function(e) {
                        if (!confirm("Bild wirklich löschen?")) {
                            e.preventDefault(); // verhindert das Öffnen des Links
                        }
                    };
                    container.appendChild(img);
                    container.appendChild(deleteBtn);
                    thumbs.appendChild(container);
                });
            });
        }

        // Bestehende Einträge anzeigen
        // renderThumbs(< ?= json_encode($meta) ?>);
        renderEncryptedThumbs(<?php echo json_encode($meta_file) ?>);

        const images_found = <?php echo json_encode($images_found) ?>;
        if (window.parent) {
            window.parent.postMessage({
                type: "images_found",
                value: images_found
            }, "*");
        }
    </script>
</body>

</html>