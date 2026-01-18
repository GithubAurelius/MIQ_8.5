<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_SESSION)) session_start();
require_once $_SESSION['INI-PATH'];
require_once ENCRYPTION;

// Hilfsfunktionen
function loadMeta()
{
    global $db, $fg, $fcid, $fid;
    $db_a = get_query_data($db, "forms_{$fg}", "fcid={$fcid} AND fid={$fid}");
    return $db_a ? (json_decode($db_a[0]['fcont'], true) ?: []) : [];
}

function saveMeta($data)
{
    global $db, $fg, $fcid, $fid;
    $meta_jsn = json_encode($data, JSON_UNESCAPED_SLASHES);
    $fcont_quoted = $db->quote($meta_jsn);
    $db->exec("INSERT INTO forms_{$fg} (fcid, fid, fcont)
                VALUES ({$fcid}, {$fid}, {$fcont_quoted})
                ON DUPLICATE KEY UPDATE fcont = VALUES(fcont)");
}

// TEST: http://zi_mh.local/MIQ_8.5/modules/uploads/upload.php?fcid=2025111312135829&fg=10100&fid=51


$fcid   = $_REQUEST['fcid'] ?? ""; // 2025111312135829;
$fid    = $_REQUEST['fid'] ?? "";  // 51;
$fg     = $_REQUEST['fg'] ?? ""; // 10100; 
if (!$fcid || !$fid || !$fg) exit;

$del    = $_REQUEST['del'] ?? "";
$path   = $_REQUEST['path'] ?? "";
if ($del) {
    $file_to_del = UPLOAD_BASE . $path . $del;
    if (file_exists($file_to_del)) unlink($file_to_del);
    // $db->exec("UPDATE forms_{$fg} WHERE fcid={$fcid} AND fid={$fid}");
    $deleted_str = 'DELETED:' . $del;
    $db->exec("UPDATE forms_{$fg} SET fcont = REPLACE(fcont, '{$del}', '{$deleted_str}') WHERE fcid={$fcid} AND fid={$fid}");
    header("Location: " . $_SERVER['PHP_SELF'] . "?fcid=$fcid&fg=$fg&fid=$fid");
}

// Konfiguration
$uploadDir = UPLOAD_BASE . UPLOAD_SUB_PATH; // kein Webzugriff!
$allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
$hmtl_allowed_mime_types = "image/*, application/pdf";

$meta_file_a = loadMeta();
$images_found = count($meta_file_a) ? count($meta_file_a) : 0;

$uploadDir = UPLOAD_BASE . UPLOAD_SUB_PATH;
$message = '';

// Erstelle das Upload-Verzeichnis, falls es nicht existiert
if (!is_dir($uploadDir)) {
    // Wenn das Verzeichnis nicht existiert, versuchen Sie es zu erstellen
    if (!mkdir($uploadDir, 0777, true)) {
        $message = '<span style="color:red;">Fehler: Das Upload-Verzeichnis konnte nicht erstellt werden.</span>';
    }
}

$upload_done = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files']) && empty($message)) {

    $uploadedCount = 0;
    $results = [];

    // Iteriere über alle hochgeladenen Dateien
    foreach ($_FILES['files']['tmp_name'] as $i => $tmp) {
        $mime = mime_content_type($tmp);
        if (!in_array($mime, $allowed_mime_types)) continue;

        if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {

            $originalName = basename($_FILES['files']['name'][$i]);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $hexName = bin2hex(random_bytes(16)) . "." . $ext;
            $targetPath =  $uploadDir . $hexName;
            list($targetFile, $fileName, $encodedName, $encrypted) = encrpyt_file(UPLOAD_BASE . UPLOAD_SUB_PATH, $originalName, $tmp, SECRET);
            if (file_put_contents($targetFile, $encrypted)) {
                // ins_or_rep_filedict($ts, $db, ++$did, $muid, $fcid, $fid, UPLOAD_SUB_PATH, $fileName, $encodedName);
                // $file_add_a[] = $did;
                $meta_record = [
                    "filePath" => UPLOAD_SUB_PATH,
                    "fileNameEncoded" => $encodedName,
                    "fileNameOriginal" => $fileName,
                    "fileMime" => $mime,
                    "fid" => $fid
                ];
                $meta_file_a[] = $meta_record;
                $results[] = '<span style="color:green;">✔ Erfolgreich (hochgeladen und verschlüsselt): ' . htmlspecialchars($originalName) . '</span>';
                $uploadedCount++;
            } else {
                $results[] = '<span style="color:red;">✖ Fehler beim Verschieben: ' . htmlspecialchars($originalName) . '</span>';
            }
            $upload_done = 1;
        } elseif ($_FILES['files']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
            $results[] = '<span style="color:red;">✖ Upload-Fehler ' . $_FILES['files']['error'][$i] . ': ' . htmlspecialchars($_FILES['files']['name'][$i]) . '</span>';
        }
    }

    if ($uploadedCount > 0 || !empty($results)) {
        $message = '<h3>Upload-Ergebnisse:</h3>' . implode('<br>', $results);
        saveMeta($meta_file_a);
    } else {
        $message = '<span style="color:orange;">Hinweis: Bitte wählen Sie eine Datei aus.</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            color: dimgray;
            font-family: sans-serif;
            margin: 3px;
        }

        .message-box {
            display: none;
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        #thumbs {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .thumb-container {
            position: relative;
            display: inline-block;
            margin: 5px;
        }

        .thumb {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin: 3px;
            cursor: pointer;
            border-radius: 6px;
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
            cursor: pointer;
        }

        /* DRAG AND DROP */
        .upload-form {
            margin: 3px auto;
            padding: 5px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }

        .drop-zone {
            display: block;
            border: 1px dashed #d1d5db;
            border-radius: 8px;
            background: #f9fafb;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease;
            padding: 3px 0 3px 0;
        }

        .drop-zone:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }

        .drop-zone.dragover {
            background: #eef2f7;
            border-color: #6b7280;
        }

        .drop-text {
            font-size: 15px;
            color: gray;
        }

        .drop-text span {
            font-size: 13px;
            color: gray;
        }

        #fileInput {
            display: none;
        }

        .upload-btn {
            display: none;
            /* für autoupload */
            margin-top: 5px;
            width: 100%;
            padding: 5px 5px;
            border-radius: 10px;
            border: 1px solid #d1d5db;
            background: #f9fafb;
            font-size: 15px;
            color: gray;
            cursor: pointer;
            transition: background 0.2s ease, border 0.2s ease;
        }

        .upload-btn:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }

        .file-info {
            display: none;
            /* für autoupload */
            margin-top: 5px;
            font-size: 13px;
            color: silver;
            text-align: center;
            line-height: 1.5;
        }


        /* Responsive */
        /* @media (max-width: 480px) {
            .upload-form {
                padding: 24px 16px;
            }

            .drop-zone {
                padding: 36px 16px;
            }
        } */
    </style>

</head>

<body>
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" enctype="multipart/form-data" class="upload-form">
        <input name='fcid' type='hidden' value='<?= $fcid ?>'>
        <input name='fg' type='hidden' value='<?= $fg ?>'>
        <input name='fid' type='hidden' value='<?= $fid ?>'>
        <label for="fileInput" class="drop-zone" id="dropZone">
            <div class="drop-content">
                <div class="drop-text">
                    Dateien hier ablegen<br>
                    <span>oder klicken zum Auswählen</span>
                </div>
            </div>
            <input type="file"
                id="fileInput"
                name="files[]"
                multiple
                accept="<?= $hmtl_allowed_mime_types ?>"
                required>
        </label>
        <div class="file-info" id="fileInfo">
            Keine Dateien ausgewählt
        </div>
        <button type="submit" class="upload-btn">
            Dateien hochladen
        </button>
    </form>


    <?php if (!empty($message)): ?>
        <div class="message-box">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($_FILES): ?>
        <script>
            // parent.window.document.main_form.submit();
        </script>
    <?php endif ?>


    <?php
    // Metadaatn erneut laden
    $meta_file_a = loadMeta();
    // echo show_a($meta_file_a);

    // Parameter für parse_img.php und Einbinden
    $from_file = 1;
    $incldb = 0;
    require_once MIQ_ROOT_PHP . "parse_img.php";

    echo "<div id='thumbs'>";
    foreach ($meta_file_a as $key => $file_a) {
        $crypted = 1;
        $path = $file_a['filePath'];
        $filename_c = $file_a['fileNameEncoded'];
        $file_nori = $file_a['fileNameOriginal'];
        if (!substr_count($filename_c, 'DELETED:')) {
            $img_a = parse_img($path, $filename_c, 1);
            if (substr_count(strtolower($img_a['file_name']), 'pdf')  || substr_count(strtolower($img_a['file_type']), 'pdf')) {
                echo "<div class='thumb-container'>
                        <img src='" . MIQ_PATH . "img/pdf.jpg' class='thumb' title='" . $img_a['file_name'] . "'
                            onclick = \"window.open('" . MIQ_PATH_PHP . "parse_img.php?path=" . urlencode($path) . "&filename_c=" . urlencode($filename_c) . "&c=1&show_image=1')\">";
                if (isset($_SESSION['rl']['Trajan'])) echo "<a class='delete-btn' 
                            onclick = \"if (confirm('Bild wirklich löschen?')) document.location.href='" . MIQ_PATH . "modules/uploads/upload.php?fg=" . $fg . "&fcid=" . $fcid . "&fid=" . $fid . "&del=" . urlencode($img_a['file_name_c']) . "&path=" . urlencode($img_a['file_path']) . "'\">Löschen
                        </a>
                    </div>";
            } else {
                echo "<div class='thumb-container'>
                        <img src='" . $img_a['file_data'] . "' class='thumb' title='" . $img_a['file_name'] . "' 
                            onclick = \"window.open('" . MIQ_PATH_PHP . "parse_img.php?path=" . urlencode($path) . "&filename_c=" . urlencode($filename_c) . "&c=1&show_image=1')\">";
                if (isset($_SESSION['rl']['Trajan'])) echo "<a class='delete-btn'
                            onclick = \"if (confirm('Bild wirklich löschen?')) document.location.href='" . MIQ_PATH . "modules/uploads/upload.php?fg=" . $fg . "&fcid=" . $fcid . "&fid=" . $fid . "&del=" . urlencode($img_a['file_name_c']) . "&path=" . urlencode($img_a['file_path']) . "'\">Löschen
                        </a>
                    </div>";
            }
        }
    }
    echo "</div>";

    ?>

    <script>
        const form = document.querySelector(".upload-form");
        const dropZone = document.getElementById("dropZone");
        const fileInput = document.getElementById("fileInput");
        const fileInfo = document.getElementById("fileInfo");

        let uploading = false; // für auto-upload

        function submitForm() { // für auto-upload
            if (uploading) return;
            uploading = true;
            form.submit();
        }

        function updateFileInfo(files) {
            if (!files || files.length === 0) {
                fileInfo.textContent = "Keine Dateien ausgewählt";
                return;
            }

            // bei manuellem upload mit vorheriger Anzeige
            // let html = `${files.length} Datei${files.length > 1 ? "en" : ""} ausgewählt`;
            // Liste anzeigen
            // html += "<ul>";
            // for (const file of files) {
            //     html += `<li>${file.name}</li>`;
            // }
            // html += "</ul>";
            //fileInfo.innerHTML = html;

            // auto-upload
            fileInfo.textContent = `${files.length} Datei${files.length > 1 ? "en" : ""} wird hochgeladen …`;
        }

        ["dragenter", "dragover"].forEach(event => {
            dropZone.addEventListener(event, e => {
                e.preventDefault();
                dropZone.classList.add("dragover");
            });
        });

        ["dragleave", "drop"].forEach(event => {
            dropZone.addEventListener(event, e => {
                e.preventDefault();
                dropZone.classList.remove("dragover");
            });
        });

        dropZone.addEventListener("drop", e => {
            fileInput.files = e.dataTransfer.files;
            updateFileInfo(fileInput.files);
        });

        fileInput.addEventListener("change", () => {
            updateFileInfo(fileInput.files);
        });


        // Klick-Auswahl für auto-upload 
        fileInput.addEventListener("change", () => {
            if (fileInput.files.length === 0) return;
            updateFileInfo(fileInput.files);
            submitForm();
        });

        // Listener Klick-Auswahl für auto-upload 
        dropZone.addEventListener("drop", e => {
            fileInput.files = e.dataTransfer.files;

            if (fileInput.files.length === 0) return;

            updateFileInfo(fileInput.files);
            submitForm();
        });

        // Justieren der frame-height mit signal an parent
        const images_found = <?php echo json_encode($images_found) ?>;
        if (window.parent) {
            window.parent.postMessage({
                type: "images_found",
                value: images_found
            }, "*");
        }

        const parent_main_form = parent.window.document.main_form;
        if (<?= json_encode($upload_done) ?? 0 ?>)
            if (parent_main_form) parent_main_form.submit();
    </script>
</body>

</html>