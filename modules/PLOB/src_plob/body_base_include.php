    <!-- HTML Basis-Elemente -->
    <div id="map"></div>
    <div id='status_info'>
        <span id='status_span'></span>
    </div>
    <div id='img_slider_control' style='display:none'>
        <input class='tools_slider' id="opacity" type="range" min="0" max="1" value="0.5" step="0.01">
        <span id="opVal">0.5</span>
    </div>

    <style>
        #geo_form {
            z-index: 999;
            position: absolute;
            background-color: white;
            border: solid silver 0;
            border-radius: 6px;
            position: absolute;
            left: 80px;
            top: 13px;
            width: 35%;
            height: 90%;
            padding: 5px;
            padding-top: 0;
            box-sizing: border-box;
            transition: height 0.15s ease;
            /* sanftes Ein-/Ausklappen */
        }

        .div_iframe_header {
            padding-top: 8px;
            font-size: 11px;
            text-align: center;
            background-color: white;
            color: silver;
            height: 20px;
            position: abolute;
            top: 0;
            cursor: pointer;
        }

        .div_iframe_content {
            padding: 0;
            height: 100%;
            width: 100%;
            box-sizing: border-box;
        }

        .collapsed {
            max-height: 34px;
        }

        #search_list {
            z-index: 999;
            position: absolute;
            background-color: white;
            border: solid silver 0;
            border-radius: 6px;
            position: absolute;
            right: 325px;
            top: 13px;
            width: 40%;
            height: 30%;
            padding: 5px;
            padding-top: 0;
            box-sizing: border-box;
            transition: height 0.15s ease;
            /* sanftes Ein-/Ausklappen */
        }
    </style>
    <div id="geo_form">
        <div id='geo_form_click' class="div_iframe_header" title='Doppelick zum ein-/ ausklappen, Maus halten zum ziehen'>Dokumenationsbereich</div>
        <div class="div_iframe_content">
            <hr>
            <iframe id='geo_form_iframe' style='width:100%;height:97%;border:0' src=''></iframe>
        </div>
    </div>

    <div id="search_list">
        <div id='search_list_click'  class="div_iframe_header" title='Doppelick zum ein-/ ausklappen, Maus halten zum ziehen'>Suche</div>
        <div class="div_iframe_content">
            <hr>
            <iframe id='search_list_iframe' style='width:100%;height:97%;border:0' src='<?= $_SESSION['WEBROOT'].$_SESSION['MIQ']?>/modules/listing_form_native/listing_prepare.php?nowbox=1&limit=100&fg=10100&form=Patient&fid_str=110,120,501&work_mode=P-N-F'></iframe>
        </div>
    </div>


    <div id="ui_tools" style='position:absolute;left:80px;display:none'>
        <form id="ui_tools_form" method="POST" action="<?= $_SERVER['PHP_SELF'] ?>">
            <table id='tools_table'>
                <?php if ($add_on_a['profile_image']): ?>
                    <tr>
                        <td class='tools_profile_image'>Plan-Profil</td>
                        <td>
                            <div class='input-group'>
                                <select id='profile_image' name='profile_image'></select>
                            </div>
                        </td>
                    </tr>
                <?php endif ?>
                <?php if ($add_on_a['profile_layer']): ?>
                    <tr>
                        <td class='tools_profile_layer'>Layer-Profil</td>
                        <td>
                            <div class='input-group'>
                                <select id='profile_layer' name='profile_layer'></select>
                            </div>
                        </td>
                    </tr>
                <?php endif ?>
                <tr>
                    <td class='tools_doc'>Dokumentation</td>
                    <td><input disabled type='checkbox' id='form_doc' checked></td>
                </tr>
                <?php if ($add_on_a['fixing']): ?>
                    <tr>
                        <td class='tools_desc'>Karten Fixierung</td>
                        <td><input type='checkbox' id='fixing'></td>
                    </tr>

                <?php endif ?>
                <?php if ($add_on_a['coloring']): ?>
                    <tr>
                        <td class='tools_desc'>Objekt-Farbe (ohne Pins)</td>
                        <td><select id='color_choice'>
                                <option value='black'>black</option>
                                <option value='blue'>blue</option>
                                <option value='magenta'>magenta</option>
                                <option value='orange'>orange</option>
                            </select></td>
                    </tr>
                <?php endif ?>
                <tr>
                    <td class='tools_desc'>Tooltip-Label ein/ auschalten </td>
                    <td>
                        <div id="layerLabel_tooltips">
                            <label><input type="checkbox" id="layerLabel" onclick="this.checked ? show_hide_tooltips(lastClickedOverlay,1) : show_hide_tooltips(lastClickedOverlay,0);"> </label>
                        </div>
                    </td>
                </tr>
                <?php if ($add_on_a['powerPaint']): ?>
                    <tr>
                        <td class='tools_desc'>Power Paint</td>
                        <td><input type='checkbox' id='power_paint'></td>
                    </tr>
                <?php endif ?>
                <?php if ($add_on_a['mapRotation']): ?>
                    <tr>
                        <td>Rotation Karte <button title='Zurücksetzten auf Arbeitsausrichtung' onclick='map.setBearing(planBearing)'>🧭</button></td>
                        <td>
                            <label>
                                <input class='tools_slider' id="rotateSlider" type="range" min="0" max="360" step="1" value="0">
                                <span id="bearingVal">0°</span>
                            </label>
                        </td>
                    </tr>
                <?php endif ?>
                <?php if ($add_on_a['moveImage']): ?>
                    <tr>
                        <td class='tools_desc'>Drehung Bild</td>
                        <td>
                            <label>
                                <input class='tools_slider' id="ang" type="range" min="0" max="360" value="0" step="1">
                                <span id="val">0°</span>
                            </label>
                        </td>
                    </tr>
                <?php endif ?>
                <tr>
                    <td class='tools_desc'>Werkzeuge</td>
                    <td>
                        <table>
                            <tr>
                                <?php if ($add_on_a['polylineMeasure']): ?>
                                    <td style='border-bottom: solid 0'>
                                        <div id="myMeasureDiv"></div>
                                    </td>
                                <?php endif ?>
                                <?php if ($add_on_a['myPosition']): ?>
                                    <td style='vertical-align:top; border-bottom: solid 0;width:100%'>
                                        <div class="leaflet-bar leaflet-control">
                                            <a onclick="myPosition()" class="leaflet-bar-part" href="#" title="Home" role="button" id="myButtonA">🏠</a>
                                        </div>
                                    </td>
                                <?php endif ?>
                            </tr>
                            <?php if ($add_on_a['createLayer']): ?>
                                <tr>
                                    <td colspan='2' style='vertical-align:top; border-bottom: solid 0;'>
                                        <div class='input-group'>
                                            <input id="layerNameInput" placeholder="Objectlayer erstellen">
                                            <button id="addDrawLayerBtn">💾</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif ?>
                        </table>
                    </td>
                </tr>
                <?php if ($add_on_a['screenShots']): ?>
                    <tr>
                        <td class='tools_desc'>Screenshots </td>
                        <td>aktivieren <input onclick="toggleScreenshotBtn(this)" type='checkbox' id='screenshots_activate'> anzeigen <input onclick="toggle_display('screenshot_container')" type='checkbox' id='screenshots_show'></td>
                    </tr>
                <?php endif ?>
                <tr>
                    <td class='tools_desc'>Alternative Karten </td>
                    <td>
                        <div id="layerCheckboxes">
                            <label><input type="checkbox" id="base_layer_1" onclick="this.checked ? show_hide_control_layer_elem('Topographisch', 1) : show_hide_control_layer_elem('Topographisch', 0)"> Topographischer Layer</label>
                            <label><input type="checkbox" id="base_layer_2" onclick="this.checked ? show_hide_control_layer_elem('Fahrrad', 1) : show_hide_control_layer_elem('Fahrrad', 0)"> Fahrrad Layer</label>
                            <label><input type="checkbox" id="base_layer_3" onclick="this.checked ? show_hide_control_layer_elem('ÖPNV', 1) : show_hide_control_layer_elem('ÖPNV', 0)"> ÖPNV Layer</label>
                            <label><input type="checkbox" id="base_layer_4" onclick="this.checked ? show_hide_control_layer_elem('Zugverkehr', 1) : show_hide_control_layer_elem('Zugverkehr', 0)"> Zugverkehr Layer</label>
                            <label><input type="checkbox" id="base_layer_5" onclick="this.checked ? show_hide_control_layer_elem('Satellitenkarte', 1) : show_hide_control_layer_elem('Satellitenkarte', 0)"> Satellitenkarte Layer</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class='tools_desc'>Abmelden </td>
                    <td>

                        <div class="leaflet-bar leaflet-control">
                            <a onclick="document.location.href='<?= $_SESSION['WEBROOT'] ?>login.php'" class="leaflet-bar-part" href="#" title="Home" role="button" id="myButtonA">🛑</a>
                        </div>

                    </td>
                </tr>
            </table>
        </form>
    </div>

    <div id="colorMenu">
        <div class="color-swatch" data-color="#0000ff" style="background:#0000ff;"></div>
        <div class="color-swatch" data-color="#7fd6ffff" style="background:#7fd6ffff;"></div>
        <div class="color-swatch" data-color="#0ee4cfff" style="background:#0ee4cfff;"></div>
        <div class="color-swatch" data-color="#8A2BE2" style="background:#8A2BE2;"></div>
        <div class="color-swatch" data-color="#FF00FF" style="background:#FF00FF;"></div>
        <br>
        <div class="color-swatch" data-color="#D3D3D3" style="background:#D3D3D3;"></div>
        <div class="color-swatch" data-color="#5C4033" style="background:#5C4033;"></div>
        <div class="color-swatch" data-color="#c40000ff" style="background:#c40000ff;"></div>
        <div class="color-swatch" data-color="#ffb43cff" style="background:#ffb43cff;"></div>
        <div class="color-swatch" data-color="#04935cff" style="background:#04935cff;"></div>
        <hr>
        <div class="color-swatch" data-color="#ffffff" style="background:#ffffff;"></div>
        <div class="color-swatch" data-color="#000000" style="background:#000000;"></div>
        <div class="color-swatch" data-color="#ff0000" style="background:#ff0000;"></div>
        <div class="color-swatch" data-color="#ffff00" style="background:#ffff00;"></div>
        <div class="color-swatch" data-color="#00ff00" style="background:#00ff00;"></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {

            function set_div_movable(div_id) {
                const draggableElement = document.getElementById(div_id);
                let isDragging = false;
                let offsetX, offsetY;

                draggableElement.addEventListener('mousedown', (e) => {

                    // Schutz gegen Blocken von Feldern: Wenn das geklickte Element ein <SELECT>, <BUTTON> oder <INPUT> ist, abbrechen 
                    const tagName = e.target.tagName;
                    if (tagName === 'SELECT' || tagName === 'BUTTON' || tagName === 'INPUT' || tagName === 'TEXTAREA') {
                        return; // Drag-Vorgang nicht starten
                    }
                    // ---------------------------------------------------------------------------------------------
                    isDragging = true;
                    offsetX = e.clientX - draggableElement.getBoundingClientRect().left;
                    offsetY = e.clientY - draggableElement.getBoundingClientRect().top;
                    e.preventDefault();
                    document.addEventListener('mousemove', onMouseMove);
                });

                function onMouseMove(e) {
                    if (!isDragging) return;

                    let newLeft = e.clientX - offsetX;
                    let newTop = e.clientY - offsetY;
                    draggableElement.style.left = newLeft + 'px';
                    draggableElement.style.top = newTop + 'px';
                }

                document.addEventListener('mouseup', () => {
                    if (isDragging) {
                        isDragging = false;
                        document.removeEventListener('mousemove', onMouseMove);
                    }
                });
            }

            function toggle_collapse_div(elementId, triggerNow = false) {
                const element = document.getElementById(elementId);
                const contentElement = element ? element.querySelector('.div_iframe_content') : null;
                if (!element || !contentElement) {
                    return;
                }

                const performToggle = (arg, event) => {
                    const willBeCollapsed = !element.classList.contains('collapsed');
                    if (willBeCollapsed) {
                        contentElement.style.display = 'none';
                        element.classList.add('collapsed');
                    } else {
                        element.classList.remove('collapsed');
                        setTimeout(() => {
                            contentElement.style.display = 'block';
                        }, 300);
                    }
                    // TODO hier wird die Höhe des inneren Iframes berechnet. Das ist
                    // noch nicht optimal, insbesondere ist arg ungenutzt.
                    // Bei kleinen Auflösungen ist der fixe Paramter -60 störend und müsste
                    // dynmisch berechnet werden.
                    let iframe = null;
                    if (event) { // Abfragen ob event basiert oder initial
                        if (event.srcElement.id == 'search_list_click')
                            iframe = document.getElementById('search_list_iframe');
                        if (event.srcElement.id == 'geo_form_click')
                            iframe = document.getElementById('geo_form_iframe');
                        if (iframe) iframe.style.height = element.offsetHeight-60 + 'px';
                    }
                    
                };

                element.addEventListener('dblclick', (e) => performToggle('dblclick', e));
                if (triggerNow) { // Start/ Initialiserung
                    performToggle();
                }
            }

            // Anwendung der Move- und Doppelclicks aud DIV
            set_div_movable('ui_tools');
            if (!is_mobile) set_div_movable('geo_form');
            if (!is_mobile) set_div_movable('search_list');
            toggle_collapse_div('geo_form', 1);
            toggle_collapse_div('search_list', 1);

            // Dieser Teil klappt das Forular beim ersten Laden einens Layers aus
            const iframeElement = document.getElementById('geo_form_iframe');
            const targetDivId = 'geo_form';
            if (iframeElement) {
                iframeElement.addEventListener('load', () => {
                    if (iframeElement.src && iframeElement.src !== '' && iframeElement.src !== 'about:blank') {
                        const targetDiv = document.getElementById(targetDivId);
                        targetDiv.classList.remove('collapsed');
                        targetDiv.querySelector('.div_iframe_content').style.display = 'block';
                        if (targetDiv && !targetDiv.classList.contains('collapsed')) {}
                    }
                });
            }

            const all_profile_layer_jsn = <?= json_encode($all_profile_layer_jsn) ?>;

            function fillSelectsFromJson(jsonData) {
                const profileLayerSelect = document.getElementById('profile_layer');
                const profileImagesSelect = document.getElementById('profile_image');

                if (!profileLayerSelect || !profileImagesSelect) {
                    // console.error("Mindestens eines der Select-Elemente ('profile_layer' oder 'profile_image') wurde nicht gefunden.");
                    return;
                }

                // profileLayerSelect.innerHTML = '';
                // profileImagesSelect.innerHTML = '';

                const emptyOptionLayer = document.createElement('option');
                emptyOptionLayer.value = '';
                emptyOptionLayer.textContent = '';
                profileLayerSelect.appendChild(emptyOptionLayer);

                const emptyOptionImage = document.createElement('option'); // <--- NEU
                emptyOptionImage.value = '';
                emptyOptionImage.textContent = '';
                profileImagesSelect.appendChild(emptyOptionImage);

                jsonData.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.fcid;
                    if (item.layType === 'object_profile') {
                        option.textContent = item.layName || 'Standardprofil';
                        profileLayerSelect.appendChild(option);

                    } else if (item.layType === 'plan_profile') {
                        option.textContent = item.layName;
                        profileImagesSelect.appendChild(option);
                    }
                });
            }
            try { // weil selects rechte-abhängig sind
                const jsonArray = JSON.parse(all_profile_layer_jsn);
                fillSelectsFromJson(jsonArray);
            } catch (e) {
                console.error("Fehler beim Parsen der JSON-Daten:", e);
                return;
            }


            const selectElement_layer = document.getElementById('profile_layer');
            const selectElement_image = document.getElementById('profile_image');
            const formElement = document.getElementById('ui_tools_form');

            if (selectElement_layer && formElement) {
                selectElement_layer.addEventListener('change', () => {
                    formElement.submit();
                });
            }

            if (selectElement_image && formElement) {
                selectElement_image.addEventListener('change', () => {
                    formElement.submit();
                });
            }

            const session_profile_layer_value = <?= json_encode($_SESSION['profile_layer'] ?? "") ?>;
            const session_profile_image_value = <?= json_encode($_SESSION['profile_image'] ?? "") ?>;

            function setSelectedValue(selectId, valueToSelect) {
                const selectElement = document.getElementById(selectId);
                if (selectElement && valueToSelect !== null && valueToSelect !== '') {
                    selectElement.value = valueToSelect;
                }
            }

            setSelectedValue('profile_layer', session_profile_layer_value);
            setSelectedValue('profile_image', session_profile_image_value);


        });
    </script>