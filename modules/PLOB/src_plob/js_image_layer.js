/*
Hier alles was mit den Image-Layern in Bezug steht.
*/

function show_hide_control_layer_elem(id, state) {
    const elem_radio = document.getElementById(`basemap-${id}-radio`);
    const elem_label = document.getElementById(`basemap-${id}-label`);
    const parent_spann = elem_radio.parentElement;
    if (state) {
        parent_spann.style.height = '22px';
        elem_radio.style.visibility = 'visible';
        elem_label.style.visibility = 'visible';
    } else { elem_radio.style.visibility = 'hidden';
        elem_label.style.visibility = 'hidden';
        parent_spann.style.display = 'block';
        parent_spann.style.height = '0';
    }
}
  
async function get_file(path, filename_c, fcid, c) { 
    try {
        const url = miq_path_php + `/parse_img.php?incldb=${fcid}&path=${encodeURIComponent(path)}&filename_c=${encodeURIComponent(filename_c)}&c=${c}`;
        // console.log(url);
        const res = await fetch(url);
        if (!res.ok) throw new Error(`HTTP error ${res.status}`);
        const data = await res.json();
        if (!data.file_name) {
            alert('Datei nicht gefunden!');
            return null;
        }
        status_span.textContent = '(CID:' + fcid + ')' + " Image-Layer geladen ...";
        return data;
    } catch (err) {
        console.error(err);
        alert('Fehler beim Laden der Datei!');
        return null;
    }
}

// === Get img-layer data from php
const imgLayers = {}; // layer for overlayImages
const overlayImgs = {}; // the overlayImages 

// === Create empty layer
Object.entries(all_image_layer_obj).forEach(([key, item]) => {
    imgLayers[key] = L.featureGroup();
    imgLayers[key]['fcid'] = item['fcid'];
    imgLayers[key]['file_path'] = item['filePath'];
    imgLayers[key]['file_name_c'] = item['fileNameC'];
    imgLayers[key]['file_name'] = item['fileName'];
    imgLayers[key]['lay_name'] = item['layName'];
    imgLayers[key]['lay_text'] = item['layText']; // Hilfe, hier PDF für existierenden PDFs zum Bild für rechte Mausclicks 
    imgLayers[key]['loaded'] = 0;
    // LayerControl Overlay mit layName als Label
    const uniqueKey = item['fcid'];
    // console.table(imgLayers[key]);
    // overlayImgs dienen als Referenz auf die imgLayers, damit der name verwendet werden kann
    overlayImgs[uniqueKey] = imgLayers[key];
});





// === Add empty layer to map
const geomap_control = L.control.layers(baseMaps, overlayImgs, {
    collapsed: false,
    position: 'topright'
}).addTo(map);


// OpenClose Button für container
const container_tr = geomap_control._container;
if (is_mobile) container_tr.style.minWidth = (map._size['x']-80) + 'px';
const toggleBtn_tr = L.DomUtil.create('button', 'leaflet-control-collapse-btn', container_tr);
// toggleBtn_tr.innerHTML = '⇅'; 
toggleBtn_tr.style.display = 'block';
toggleBtn_tr.style.width = '100%';
toggleBtn_tr.style.marginTop = '5px';
toggleBtn_tr.style.height = '10px';
toggleBtn_tr.style.cursor = 'pointer';

// Klick-Event für Button
L.DomEvent.on(toggleBtn_tr, 'click', function (e) {
    L.DomEvent.stopPropagation(e); // klick nur für Button
    L.DomEvent.preventDefault(e);

    if (container_tr.classList.contains('leaflet-control-layers-expanded')) {
        container_tr.classList.remove('leaflet-control-layers-expanded');
        container_tr.classList.add('leaflet-control-layers-collapsed');
    } else {
        container_tr.classList.remove('leaflet-control-layers-collapsed');
        container_tr.classList.add('leaflet-control-layers-expanded');
    }
});

function assignBaseMapIDs(baseMapsControl, baseMaps) {
    const container = baseMapsControl._container;
    if (!container) return;

    // Alle BaseMap-Labels im Control
    const labels = container.querySelectorAll('.leaflet-control-layers-base label');

    Object.keys(baseMaps).forEach((key, index) => {
        const label = labels[index];
        if (!label) return;

        // Radio-Input
        const radio = label.querySelector('input.leaflet-control-layers-selector');
        if (radio) {
            radio.id = `basemap-${key}-radio`;
        }

        // Span-Text
        const span = label.querySelector('span > span');
        if (span) {
            span.id = `basemap-${key}-label`;
            span.title = key; // Tooltip mit Layer-Namen
        }
    });
}
assignBaseMapIDs(geomap_control, baseMaps);

show_hide_control_layer_elem('Topographisch', 0)
show_hide_control_layer_elem('Fahrrad', 0)
show_hide_control_layer_elem('ÖPNV', 0)
show_hide_control_layer_elem('Zugverkehr', 0)
show_hide_control_layer_elem('Satellitenkarte', 0)

function assignOverlayControlIDs(overlayImgs_control, overlayImgs) {
    const container = overlayImgs_control._container || document;
    const labels = container.querySelectorAll('.leaflet-control-layers-overlays label');
    const keys = Object.keys(overlayImgs);

    labels.forEach((label, index) => {
        const key = keys[index];
        const checkbox = label.querySelector('input.leaflet-control-layers-selector');
        const textSpan = label.querySelector('span > span');
        let act_span = null;

        if (checkbox) checkbox.id = `overlay-${key}-checkbox`;
        if (textSpan) {
            textSpan.id = `overlay-${key}-label`;
            textSpan.classList.add('overlay-label-text'); 
        }

        // Labeltext & Tooltip setzen
        act_span = document.getElementById(`overlay-${key}-label`);
        if (act_span && Object.hasOwn(overlayImgs, key)) {
            const text = overlayImgs[key].lay_name || overlayImgs[key].file_name || key;
            if (overlayImgs[key].lay_text=='PDF') act_span.classList.add('link_span');
            act_span.innerText = text;
            act_span.title = text; // Tooltip mit vollem Namen
        }
    });

    //  Alphabetisch sortieren
    const overlaysList = container.querySelector('.leaflet-control-layers-overlays');
    if (overlaysList) {
        const labelArray = Array.from(overlaysList.querySelectorAll('label'));

        labelArray.sort((a, b) => {
            const nameA = a.querySelector('span > span')?.innerText?.trim() ?? "";
            const nameB = b.querySelector('span > span')?.innerText?.trim() ?? "";
            return nameA.localeCompare(nameB, 'de', { sensitivity: 'base' });
        });

        labelArray.forEach(label => overlaysList.appendChild(label));
    }
}

assignOverlayControlIDs(overlayImgs, overlayImgs);

// // Alle Label-Elemente innerhalb dieses Containers
//         const containerImg = geomap_control._container;
//         const labelsImg = containerImg.querySelectorAll('label');
//         labelsImg.forEach((label, i) => {
//             console.log(label);
//             const textSpanImg = label.querySelector('span > span');
//             if (!textSpanImg) return;
//             const itemImg = Object.values(all_image_layer_obj)[i];
//             console.log(itemImg);
//             if (textSpanImg) {
//                 textSpanImg.innerText = Object.values(all_image_layer_obj)[i].layName;
//                 //if (!textSpan.innerText) textSpan.innerText = Object.values(all_image_layer_obj)[i].fileName;
//             }
//         });    


// // === Rename labels if layName ist set; else view [SETLABEL] => [SETLABEL_IF_EMPTY] 
// const labels = document.querySelectorAll('.leaflet-control-layers-overlays label');
// labels.forEach((label, i) => {
//     // Das zweite <span> innerhalb des ersten <span> auswählen
//     const textSpan = label.querySelector('span > span');
//     if (textSpan) {
//         textSpan.innerText = Object.values(all_image_layer_obj)[i].layName;
//         if (!textSpan.innerText) textSpan.innerText = Object.values(all_image_layer_obj)[i].fileName;
//     }
// });

// === Global marker-date for save
var global_ids_a = []; // marker-ids for indentify

// Remove Global Marker
function remove_fixing_marker() {
    for (const layer_to_delete_id of global_ids_a) {
        const layer_to_delete = map._layers[layer_to_delete_id];
        if (layer_to_delete) {
            map.removeLayer(layer_to_delete);
        }
    }
}

// === Global slider, 
const sliderOpacity = document.getElementById('opacity');
const sliderOpacityVal = document.getElementById('opVal');
const img_slider_control = document.getElementById('img_slider_control');
let opacityChangeHandler = null;
// Show, set slider vals and create listener 
function create_opacity_slider(rotatedImage, image_obj) {
    img_slider_control.style.display = 'block';
    opacityChangeHandler = (e) => {
        const op = Number(e.target.value);
        rotatedImage.setOpacity(op);
        sliderOpacityVal.textContent = op.toFixed(2);
        sliderOpacityVal.textContent = sliderOpacityVal.textContent;
        img_op = op.toFixed(2);
    };
    sliderOpacity.addEventListener('input', opacityChangeHandler);
}
// Remove slider and listener 
function remove_opacity_slider() {
    sliderOpacity.removeEventListener('input', opacityChangeHandler);
    if (img_slider_control)
        img_slider_control.style.display = 'none';
}

// === Global fixing-save-button, 
let imgFixSave_btn = null;
let imgSaveFixingBtnHandler = null;
// Create save-button and listener
function create_image_save_button(sliderOpacity, image_obj) {
    // === Custom button for activate fixing
    imgFixSave_btn = L.DomUtil.create('a', 'leaflet-control-zoom-custom', leftControlContainer);
    imgFixSave_btn.innerHTML = '💾'; // Symbol oder Text
    imgFixSave_btn.href = '#';
    imgFixSave_btn.title = 'Speichern';
    imgSaveFixingBtnHandler = (e) => {
        const imgFixCoor_jsn = JSON.stringify([
            tl,
            tr,
            bl
        ]);

        const geo_layer_jsn = {
            fcid: image_obj['fcid'].toString(),
            filePath: image_obj['file_path'],
            fileNameC: image_obj['file_name_c'],
            // fileName: image_obj['file_name'],
            fileType: image_obj['file_type'],
            laytype: image_obj['lay_type'],
            layerId: 0,
            layCoor: imgFixCoor_jsn,
            layOpacity: parseFloat(sliderOpacity.value) ? parseFloat(sliderOpacity.value) : 0.1,
            layName: image_obj['lay_name'],
            layText: '',
            layColor: ''
        };
        // console.log(geo_layer_jsn);
        fetchDataAndUpdateGeoLayer(geo_layer_jsn);
    };
    L.DomEvent.on(imgFixSave_btn, 'click', imgSaveFixingBtnHandler);

}
// Remove save-button and listener
function remove_save_button() {
    if (imgFixSave_btn && imgSaveFixingBtnHandler) {
        L.DomEvent.off(imgFixSave_btn, 'click', imgSaveFixingBtnHandler);
        imgFixSave_btn.remove();
        imgFixSave_btn = null;
        imgSaveFixingBtnHandler = null;
    }
}

// === Global end-of-fixing-button, 
let imgFixStop_btn = null;
let imgFixingEndBtnHandler = null;
// Create end-of-fixing-button and listener, this button also kills objects and listener itself 
function create_stop_fixing_button() {
    imgFixStop_btn = L.DomUtil.create('a', 'leaflet-control-zoom-custom', leftControlContainer);
    imgFixStop_btn.innerHTML = '🛑';
    imgFixStop_btn.href = '#';
    imgFixStop_btn.title = 'Bild-Fixing beenden';
    imgFixingEndBtnHandler = (e) => {
        remove_opacity_slider();
        remove_fixing_marker();
        remove_save_button();
        L.DomEvent.off(imgFixStop_btn, 'click', imgFixingEndBtnHandler);
        imgFixStop_btn.remove();
    };
    L.DomEvent.on(imgFixStop_btn, 'click', imgFixingEndBtnHandler);


}
// Remove end-of-fixing-button and listener
function remove_stop_fixin_button() {
    if (imgFixStop_btn && imgFixingEndBtnHandler) {
        L.DomEvent.off(imgFixStop_btn, 'click', imgFixingEndBtnHandler);
        imgFixStop_btn.remove();
        imgFixStop_btn = null;
        imgFixingEndBtnHandler = null;
    }
}

// === Main function for image-fixing-procedure
function image_fixing(rotatedImage, image_obj) {

    function updateMarkers() {
        markerTL.setLatLng(topLeft);
        markerTR.setLatLng(topRight);
        markerBL.setLatLng(bottomLeft);
        tl = [topLeft.lat, topLeft.lng];
        tr = [topRight.lat, topRight.lng];
        bl = [bottomLeft.lat, bottomLeft.lng];
        // Tooltip aktualisieren: Koordinaten anzeigen (auf 5 Nachkommastellen)
        // markerTL.setTooltipContent(`TL: ${topLeft.lat}, ${topLeft.lng}`);
        // markerTR.setTooltipContent(`TR: ${topRight.lat}, ${topRight.lng}`);
        // markerBL.setTooltipContent(`BL: ${bottomLeft.lat}, ${bottomLeft.lng}`);
        status_span.innerHTML = `<span class='mini_text'>TL: ${topLeft.lat}, ${topLeft.lng}<br>TR: ${topRight.lat}, ${topRight.lng}<br>BL: ${bottomLeft.lat}, ${bottomLeft.lng}</span>`;
        if (add_on_a['moveImage']) {
            const c = computeCenter([topLeft, topRight, bottomLeft]);
            markerCenter.setLatLng(c);
        }
    }

    function updateOverlay() {
        rotatedImage.reposition(topLeft, topRight, bottomLeft);
        updateMarkers();
    }

    function bindMarker(marker, corner) {
        marker.on('drag', e => {
            if (corner === 'TL') topLeft = e.target.getLatLng();
            if (corner === 'TR') topRight = e.target.getLatLng();
            if (corner === 'BL') bottomLeft = e.target.getLatLng();
            updateOverlay();
        });
    }

    // remove old marker if changed layer
    remove_fixing_marker();

    // never allow zero-val image-layer
    const init_layer_opacity = image_obj['lay_opacity'] ? image_obj['lay_opacity'] : 0.1;
    sliderOpacity.value = init_layer_opacity;
    sliderOpacityVal.textContent = init_layer_opacity;

    // Init fixing point as tl, tr, bl for better reading
    tl = image_obj['lay_coor'][0];
    tr = image_obj['lay_coor'][1];
    bl = image_obj['lay_coor'][2];
    let topLeft = L.latLng(...tl);
    let topRight = L.latLng(...tr);
    let bottomLeft = L.latLng(...bl);

    // ===  Create marker set and bind to image coords 
    const markerOpts = {
        draggable: true,
        autoPan: true,
        icon: redIcon
    };
    const markerTL = L.marker(topLeft, markerOpts).addTo(map); // .bindTooltip(`TL ${topLeft.lat}, ${topLeft.lng}`, {permanent: true, offset: [0, -15]});
    global_ids_a.push(markerTL._leaflet_id); // update globals
    const markerTR = L.marker(topRight, markerOpts).addTo(map); //.bindTooltip(`TR ${topRight.lat}, ${topRight.lng}`, {permanent: true, offset: [0, -15]});
    global_ids_a.push(markerTR._leaflet_id);
    const markerBL = L.marker(bottomLeft, markerOpts).addTo(map) //.bindTooltip(`BL ${bottomLeft.lat}, ${bottomLeft.lng}`, {permanent: true, offset: [0, -15]});
    global_ids_a.push(markerBL._leaflet_id);
    // bind marker to coord-vals
    bindMarker(markerTL, 'TL');
    bindMarker(markerTR, 'TR');
    bindMarker(markerBL, 'BL');
    // remove old objects and listeners and add and create new ones
    remove_opacity_slider();
    create_opacity_slider(rotatedImage, image_obj);
    remove_save_button();
    create_image_save_button(sliderOpacity, image_obj);
    remove_stop_fixin_button()
    create_stop_fixing_button();

}

function highlightActiveImageOverlay(name) {
    const container = geomap_control.getContainer();
    // console.log(name);
    // Set standard for all labels 
    const labels = container.querySelectorAll("label");
    let elem = null;
    labels.forEach(lbl => {
        lbl.style.color = "";
        lbl.style.fontWeight = "";
        lbl.style.backgroundColor = "white";
        lbl.style.fontSize = "13px";
    });
    // Highligt labels
    labels.forEach(lbl => {
        let replacedLabel = "";
        try {
            replacedLabel = overlayImgs[name]['lay_name'];
        } catch (err) {}
        if (lbl.textContent.trim() === name || lbl.textContent.trim() === replacedLabel) {
            lbl.style.color = "black";
            lbl.style.fontWeight = "bold";
            lbl.style.fontSize = "13px";
        }
    });
}

map.on('overlayadd', function (e) {
    // only overlayImgs
    const isOverlayImg = Object.values(overlayImgs).includes(e.layer);
    if (!isOverlayImg) return;

    lastClickedImageOverlay = e.layer;
    lastClickedImageName = e.name;
    highlightActiveImageOverlay(e.name);


    const layer = e.layer; // Das LayerGroup-Objekt
    if (!layer['loaded']) {
        (async () => {
            // get image info and data stream - only if needed for active image-layer
            const image_obj = await get_file(layer.file_path, layer.file_name_c, layer['fcid'], 1);
            // this delivers: file_type,file_path,file_name_c,file_name,lay_opacity,lay_name,lay_coor
            // and most important: file_data
            // use of all_image_layer_obj[layer['fcid']][...] is possible if no edit is available
            if (image_obj) {
                img_coords = image_obj['lay_coor'];
                if (!img_coords) img_coords = init_fixing;
                image_obj['lay_coor'] = JSON.parse(img_coords); // Achtung! Typ vonn'lay_coor' von string auf array geändert
                image_obj['fcid'] = layer['fcid'];

                // Finaly set image to empty layer
                const rotatedImage = L.imageOverlay.rotated(
                    image_obj.file_data,
                    image_obj.lay_coor[0],
                    image_obj.lay_coor[1],
                    image_obj.lay_coor[2], {
                    opacity: image_obj['lay_opacity'],
                    interactive: false
                }
                );
                rotatedImage.addTo(layer);

                // Update lay_name or rename onclick if no lay_name [SETLABEL_IF_EMPTY] => [SETLABEL] 
                const layer_checkbox_spann = document.getElementById(`overlay-${layer['fcid']}-label`);
                layer_checkbox_spann.innerText = image_obj['lay_name'];
                if (!layer_checkbox_spann.innerText)
                    layer_checkbox_spann.innerText = image_obj['file_name'];

                // Set status of layer and activate fixing
                layer['loaded'] = 1;
                const fixing = document.getElementById('fixing');
                if (fixing.checked == true) {
                    image_fixing(rotatedImage, image_obj);
                }
            }
        })();
    }
});

map.on('overlayremove', function (e) {
    // only overlayImgs
    const isOverlayImg = Object.values(overlayImgs).includes(e.layer);
    if (!isOverlayImg) return;

    lastClickedImageOverlay = e.layer;
    lastClickedImageName = e.name;
    highlightActiveImageOverlay("");

    // remove all layer an object, kill listener
    remove_opacity_slider();
    remove_fixing_marker();
    remove_save_button();
    remove_stop_fixin_button();
    // clear layer an set status
    e.layer.clearLayers();
    e.layer['loaded'] = 0;
    status_span.textContent = '(CID:' + e.layer.fcid + ')' + ' Image-Layer ausgeblendet';
});

if (add_on_a['geomap_collapse']) geomap_control.collapse();




const allOverlaySpans = document.querySelectorAll('span[id^="overlay-"][id$="-label"]');
allOverlaySpans.forEach(spanElement => {
    spanElement.addEventListener('contextmenu', (event) => {
        event.preventDefault(); 
        const fullId = spanElement.id;
        const match = fullId.match(/overlay-(\d+)-label/); 
        let extractedNumber = null;
        if (match && match.length > 1) {
            extractedNumber = match[1];
        }
        if (extractedNumber) {
           window.open(`src_plob/_helper_forwarder.php?fcid=${extractedNumber}`);
        } else {
            console.warn(`Nummer konnte aus der ID ${fullId} nicht extrahiert werden.`);
        }
    });
});





// // === Move image-layer TODO: Build global objects and listener
    // const moveFactor = 1; // Verschieben langsamer
    // function computeCenter(latlngs) {
    //     const pts = latlngs.map(ll => map.latLngToLayerPoint(ll));
    //     const sx = pts.reduce((s, p) => s + p.x, 0) / pts.length;
    //     const sy = pts.reduce((s, p) => s + p.y, 0) / pts.length;
    //     return map.layerPointToLatLng(L.point(sx, sy));
    // }

    // const centerLatLng = computeCenter([L.latLng(...tl), L.latLng(...tr), L.latLng(...bl)]);
    // const centerIcon = L.divIcon({
    //     className: 'center-icon',
    //     html: '<div style="width:14px;height:14px;border-radius:50%;background:blue;border:2px solid white;"></div>',
    //     iconAnchor: [7, 7]
    // });
    // const markerCenter = L.marker(centerLatLng, {
    //     draggable: true,
    //     icon: centerIcon
    // })
    //     .addTo(map)
    //     .bindTooltip('Maus Langsam bewegen', {
    //         permanent: true,
    //         offset: [0, -15]
    //     });

    // // === Verschiebung des gesamten Bildes ===
    // let moveStartCenter = null;
    // markerCenter.on('dragstart', () => moveStartCenter = computeCenter([topLeft, topRight, bottomLeft]));
    // markerCenter.on('drag', e => {
    //     const newCenter = e.target.getLatLng();
    //     const startPt = map.latLngToLayerPoint(moveStartCenter);
    //     const newPt = map.latLngToLayerPoint(newCenter);
    //     const dx = (newPt.x - startPt.x) * moveFactor;
    //     const dy = (newPt.y - startPt.y) * moveFactor;

    //     function moveLatLng(ll) {
    //         const p = map.latLngToLayerPoint(ll);
    //         return map.layerPointToLatLng(L.point(p.x + dx, p.y + dy));
    //     }
    //     topLeft = moveLatLng(topLeft);
    //     topRight = moveLatLng(topRight);
    //     bottomLeft = moveLatLng(bottomLeft);
    //     updateOverlay();
    // });
    // updateOverlay();


    
// --- Rotation des image-Layers TODO: Globaliesierung slider, Objekte und Listener

// const rotateFactor = 1; // Rotation langsamer
// const sliderAngle = document.getElementById('ang');
// const valAngle = document.getElementById('val');

// function rotateLatLngs(centerLatLng, latlngArray, angleDeg) {
//     const angle = angleDeg * Math.PI / 180 * rotateFactor;
//     const c = map.latLngToLayerPoint(centerLatLng);
//     return latlngArray.map(ll => {
//         const p = map.latLngToLayerPoint(ll);
//         const dx = p.x - c.x;
//         const dy = p.y - c.y;
//         const x2 = dx * Math.cos(angle) - dy * Math.sin(angle);
//         const y2 = dx * Math.sin(angle) + dy * Math.cos(angle);
//         const p2 = L.point(c.x + x2, c.y + y2);
//         return map.layerPointToLatLng(p2);
//     });
// }

// const originalTL = topLeft;
// const originalTR = topRight;
// const originalBL = bottomLeft;

// function applyRotation(angleDeg) {
//     valAngle.textContent = angleDeg.toFixed(1) + '°';
//     const corners = [originalTL, originalTR, originalBL]; // immer vom Original aus
//     const center = computeCenter(corners);
//     const [newTL, newTR, newBL] = rotateLatLngs(center, corners, angleDeg);
//     topLeft = newTL;
//     topRight = newTR;
//     bottomLeft = newBL;
//     updateOverlay();
// }

// sliderAngle.addEventListener('input', e => applyRotation(Number(e.target.value)));
// map.on('zoomend moveend', () => applyRotation(Number(sliderAngle.value)));
