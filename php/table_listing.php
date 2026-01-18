<?php

// --- INPUTS ---
$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
$offset = max(0, $page * $limit);

// global search
$global = isset($_GET['global']) ? trim($_GET['global']) : '';

// sorting
$sortable = $col_def_a;
$order_by = $_GET['orderby'] ?? $key_name;
if (!in_array($order_by, $sortable, true)) {
    $order_by = $key_name;
}
$dir = strtoupper($_GET['dir'] ?? 'DESC');
$dir = ($dir === 'ASC') ? 'ASC' : 'DESC';

// column filters
$cols = $col_def_a;
$where = [];
$params = [];

foreach ($cols as $c) {
    if (!empty($_GET['f_' . $c])) {
        $where[] = "$c LIKE :$c";
        $params[":$c"] = "%" . $_GET['f_' . $c] . "%";
    }
}

// global search across all columns
if ($global !== '') {
    $gparts = [];
    foreach ($cols as $c) {
        $gparts[] = "$c LIKE :g";
    }
    $where[] = "(" . implode(" OR ", $gparts) . ")";
    $params[':g'] = "%$global%";
}

$wsql = $where ? ("WHERE " . implode(" AND ", $where)) : '';

// fetch total count based on filters
$count = $db->prepare("SELECT COUNT(*) FROM " . $main_table . " $wsql");
foreach ($params as $k => $v) $count->bindValue($k, $v);
$count->execute();
$total = $count->fetchColumn();

// fetch rows
$sql = "SELECT * FROM " . $main_table . " $wsql ORDER BY $order_by $dir LIMIT :limit OFFSET :offset";
$stmt = $db->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Data Browser</title>
    <link rel='stylesheet' href='<?php echo MIQ_PATH . "css/listings.css?RAND=" . random_bytes(5) ?>'>
</head>

<body>
    <form method="get" id="filterForm">
        <input name='limit' value='<?= $limit ?>' type='hidden'>
        <div id="wrap_1" class='wrap'>
            <table id="cases">
                <thead style='position:sticky; top:0; box-shadow: 0 0.5px 0 dimgray;'>
                    <tr>
                        <!-- NAVBAR AND TOOLS -->
                        <th colspan='100%' class='header-main' id='th_navbar' style='border:0;'>
                            <a class='datanav hidden' onclick='' id='add_data'>➕</a>&nbsp;&nbsp;&nbsp;&nbsp;
                            <?php $lastpage = floor(($total - 1) / $limit); ?>
                            <a class='datanav' href="#" onclick="window.location.href='<?php echo $_SERVER['PHP_SELF'] ?>?limit=<?= $limit ?>'">🔰</a>
                            <a class='datanav' href="#" onclick="window.location.reload()">🔄</a>&nbsp;&nbsp;&nbsp;&nbsp;
                            <a class='datanav' href="?<?= http_build_query(array_merge($_GET, ['page' => 0])) ?>">⏮️</a>
                            <a class='datanav' href="?<?= http_build_query(array_merge($_GET, ['page' => max(0, $page - 1)])) ?>">◀️</a>
                            <a class='datanav' href="?<?= http_build_query(array_merge($_GET, ['page' => min($lastpage, $page + 1)])) ?>">▶️</a>
                            <a class='datanav' href="?<?= http_build_query(array_merge($_GET, ['page' => $lastpage])) ?>">⏭️</a>
                            <?php $data_info_span = " " . ($page * $limit + 1) . " - " . ($page * $limit + $limit) . " von " . $total; ?>
                            <span id='DATA_INFO' style='font-weight:normal;color:gray'><?php echo $data_info_span ?></span>
                            <span id='dist_nav' style='padding-left:100px'></span>
                            <button class='datanav' id='filter_button'>🔍</button>
                            <input placeholder='Schnellsuche' type="text" name="global" value="<?= htmlspecialchars($global ?? '') ?>" form="filterForm" style='border-radius:3px;background-color:transparent;border: solid silver 1px;width:90px'>
                            <select id="orderby" name="orderby" form="filterForm" style='border-radius:3px;background-color:transparent;border: solid silver 1px;color:#969696;'>
                                <option value=''>Sortierung</option>
                                <?php foreach ($sortable as $s): ?>
                                    <option value="<?= $s ?>" <?= $s == $order_by ? 'selected' : '' ?>><?= $field_name_a[$s] ?? $s ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select id="dir" name="dir" form="filterForm" style='border-radius:3px;background-color:transparent;border: solid silver 1px;color:#969696;'>
                                <option value="ASC" <?= $dir == 'ASC' ? 'selected' : '' ?>>A-Z</option>
                                <option value="DESC" <?= $dir == 'DESC' ? 'selected' : '' ?>>Z-A</option>
                            </select>
                            <!-- <button type="submit" form="filterForm">Anwenden</button> -->
                        </th>
                    </tr>
                    <!-- TABLE HEADER -->
                    <tr>
                        <th id='key_head' class='hidden'></th>
                        <?php foreach ($cols as $c): ?>
                            <th><?= $field_name_a[$c] ?? $c ?><br>
                                <input class='filter_search' id='S_<?= $c ?>' type="text" name="f_<?= $c ?>" value="<?= htmlspecialchars($_GET['f_' . $c] ?? '') ?>" style="width:90%">
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <?php foreach ($rows as $r): ?>
                    <tr data-key="<?= $r[$key_name] ?>">
                        <td class='action desc hidden' id='key_<?= $r[$key_name] ?>'>
                            <div style='white-space: nowrap;'>
                        </td>
                        <?php foreach ($cols as $c): ?>
                            <td data-col="<?= $c ?>"><?= htmlspecialchars($r[$c] ?? '') ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </form>

    <script>
        function show_filter(w) {
            const filter_fields = w.querySelectorAll('input[type="text"][id*="S_"]');
            filter_fields.forEach(field => {
                field.style.display = 'inline';
            });
        }

        function eL_table_edit() {
            document.addEventListener('DOMContentLoaded', function() {
                const table = document.getElementById('cases');
                table.addEventListener('dblclick', function(e) {
                    const td = e.target.closest('td');
                    if (!td) return;
                    const col = td.getAttribute('data-col');
                    if (col === key_name) return; // Schlüssel nicht editierbar

                    if (!td.querySelector('textarea')) {
                        const value = td.textContent;
                        td.innerHTML = `<textarea>${value}</textarea>`;
                        const textarea = td.querySelector('textarea');
                        textarea.focus();

                        textarea.addEventListener('input', function() {
                            textarea.classList.add('in_work');
                        });

                        textarea.addEventListener('blur', function() {
                            if (!textarea.classList.contains('in_work')) {
                                td.textContent = value; // unverändert
                                return;
                            }

                            // AJAX request to save
                            const key_val = td.parentElement.getAttribute('data-key');
                            const newVal = textarea.value;

                            fetch('table_update.php', { // Datei im Moduleordner
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        key_name: key_name,
                                        key_val: key_val,
                                        column: col,
                                        value: newVal,
                                        main_table: <?php echo json_encode($main_table) ?>,
                                        col_def_list_base64: <?php echo json_encode($col_def_list_base64) ?>
                                    })
                                }).then(res => res.json())
                                .then(data => {
                                    if (data.success) {
                                        td.textContent = newVal;
                                        td.classList.add('changed');
                                    } else {
                                        alert('Fehler beim Speichern!');
                                        td.textContent = value;
                                    }
                                }).catch(err => {
                                    alert('Fehler beim Speichern!');
                                    td.textContent = value;
                                });
                        });
                    }
                });
            });
        }

        function eL_send_form_by_enter() {
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' || event.keyCode === 13) {
                    event.preventDefault();
                    const form = document.getElementById('filterForm');
                    if (form) {
                        form.submit();
                    }
                }
            });
        }

        function updatePadding() {
            const dist_nav = document.getElementById('dist_nav');
            if (dist_nav) {
                dist_nav.style.paddingLeft = (window.innerWidth - 500) + 'px';
            }
        }

        function eL_activate_filter(w, button_id) {
            const button = w.querySelector("#" + button_id);
            if (button)
                button.addEventListener('click', function() {
                    event.preventDefault();
                    show_filter(w)
                });
        }

        function eL_hover_tr(tbl_id) {
            const tabelle = w.querySelector('#cases');
            if (tabelle) {
                try {
                    const tbody = tabelle.querySelector('tbody');
                    const rows = tbody.querySelectorAll('tr');
                    rows.forEach(row => {
                        row.addEventListener('mouseenter', () => {
                            row.classList.add('highlight');
                        });
                        row.addEventListener('mouseleave', () => {
                            row.classList.remove('highlight');
                        });
                    });
                } catch (error) {}
            }
        }

        function eL_table_sort(tbl_id) {
            const tabelle = w.querySelector('#cases');
            if (tabelle) {
                const kopfzeile = tabelle.querySelector('thead');
                kopfzeile.addEventListener('dblclick', (event) => {
                    const th = event.target.closest('th');
                    if (!th) return; // Kein <th> angeklickt
                    const spaltenIndex = Array.from(th.parentNode.children).indexOf(th);
                    let sortDir = th.dataset.sortDir || '0';
                    sortDir = sortDir === '0' ? '1' : '0'; // Richtung umschalten
                    th.dataset.sortDir = sortDir;
                    const tbody = tabelle.querySelector('tbody');
                    const zeilen = Array.from(tbody.querySelectorAll('tr'));
                    zeilen.sort((a, b) => {
                        const aWert = a.children[spaltenIndex].textContent;
                        const bWert = b.children[spaltenIndex].textContent;
                        let aNum = '';
                        let bNum = '';
                        // Versuche, die Werte als Zahlen zu vergleichen
                        if ((aWert.split('-').length - 1) == 2)
                            aNum = NaN;
                        else
                            aNum = parseFloat(aWert);
                        if ((bWert.split('-').length - 1) == 2)
                            bNum = NaN;
                        else
                            bNum = parseFloat(bWert);
                        if (!isNaN(aNum) && !isNaN(bNum)) {
                            return sortDir === '0' ? aNum - bNum : bNum - aNum;
                        }
                        // Ansonsten vergleiche als Text
                        return sortDir === '0' ?
                            aWert.localeCompare(bWert) :
                            bWert.localeCompare(aWert);
                    });
                    // Tabelle neu aufbauen
                    tbody.innerHTML = '';
                    zeilen.forEach(zeile => tbody.appendChild(zeile));
                });
            }
        }

        function eL_send_form_by_selects() {
            function submitFilterForm() {
                const form = document.getElementById('filterForm');
                if (form) {
                    form.submit();
                } else {
                    console.error("Fehler: Formular mit der ID 'filterForm' wurde nicht gefunden.");
                }
            }

            const orderbySelect = document.getElementById('orderby');
            const dirSelect = document.getElementById('dir');
            const form = document.getElementById('filterForm');

            if (orderbySelect) {
                orderbySelect.addEventListener('change', submitFilterForm);
            }

            if (dirSelect) {
                dirSelect.addEventListener('change', submitFilterForm);
            }
        }

        const w = document.getElementById('wrap_1');
        const key_name = <?php echo json_encode($key_name) ?>;
        const data_info_span = w.querySelector("#" + 'DATA_INFO');
        if (data_info_span)
            data_info_span.innerHTML = <?php echo json_encode($data_info_span) ?>;

        const parentWinbox = window.top.findParentWinboxDiv(window);
        if (parentWinbox && data_info_span) { // Beachte das im Workmode N eingeschaltet sein muss für Navigationsleiste
            parentWinbox.querySelector('.wb-title').textContent = parentWinbox.querySelector('.wb-title').textContent.split(':')[0] + ':' + data_info_span.textContent;
            data_info_span.innerHTML = ''; // nur leer wenn in BOX - in eigenem Fenster anzeigen
        }


        updatePadding();
        eL_send_form_by_selects(); 
        eL_send_form_by_enter();
        eL_table_edit();
        eL_hover_tr('cases');
        eL_activate_filter(w, 'filter_button');
        eL_table_sort('cases');
    </script>

</body>

</html>