/*
Hier alles was mit den Object-Layern in Bezug steht.
*/

async function fetchGeoLayer(layerId) {
    try {
        const url = `${miq_path_php}fetch_geodata_get_layer.php?layerid=${encodeURIComponent(layerId)}`;

        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP-Fehler! Status: ${response.status}`);
        }

        const data = await response.json();
        return data; // JSON-Objekt zurück
    } catch (error) {
        console.error("Fehler beim Laden des Geo-Layers:", error);
        return null;
    }
}

function createNewDrawLayer(layerName) {
    const newFG = L.featureGroup();

    // Meta-Daten anhängen (wie bei dir)
    newId = get_fcid();
    newFG.fcid = newId;
    newFG.lay_name = layerName;

    // Zu overlayDraws Objekt hinzufügen
    overlayDraws[newId] = newFG;
    overlayDraws_control.addOverlay(newFG, layerName);
    // newFG.addTo(map);
    assignDrawsControlIDs(overlayDraws_control, overlayDraws);

    const geo_layer_jsn = {
        fcid: newId,
        layType: "objectlayer",
        layerId: newId,
        layName: layerName,
    };
    console.log(geo_layer_jsn);
    fetchDataAndUpdateGeoLayer(geo_layer_jsn);
}

// === Control-Box: Felder mit ID versehen und Namen überblenden
function assignDrawsControlIDs(overlayDraws_control, overlayDraws) {
    const container = overlayDraws_control._container;
    const labels = container.querySelectorAll('label');
    const keys = Object.keys(overlayDraws);
    let act_span = null;

    labels.forEach((label, index) => {
        const key = keys[index]; // z. B. "1234"
        const checkbox = label.querySelector('input.leaflet-control-layers-selector');
        const textSpan = label.querySelector('span > span');
        if (checkbox) checkbox.id = `draw-${key}-checkbox`;

        if (textSpan) {
            textSpan.id = `draw-${key}-label`;
            textSpan.classList.add('overlay-label-text');
        }

        // Labeltext setzen
        act_span = document.getElementById(`draw-${key}-label`);
        if (act_span && Object.hasOwn(overlayDraws, key)) {
            act_span.innerText = overlayDraws[key].lay_name || key;
        }
    });

    // Labels alphabetisch sortieren
    const overlaysList = container.querySelector('.leaflet-control-layers-overlays');
    if (overlaysList) {
        // Labels in Array umwandeln
        const labelArray = Array.from(overlaysList.querySelectorAll('label'));

        // Sortieren nach Labeltext (ä,ö,ü korrekt mit 'de')
        labelArray.sort((a, b) => {
            const nameA = a.querySelector('span > span')?.innerText?.trim() ?? "";
            const nameB = b.querySelector('span > span')?.innerText?.trim() ?? "";
            return nameA.localeCompare(nameB, 'de', {
                sensitivity: 'base'
            });
        });

        // Neu in sortierter Reihenfolge anhängen
        labelArray.forEach(label => overlaysList.appendChild(label));
    }
}

// === Aktivierung des Hervorhebens aktiver Layer
function highlightActiveOverlay(name) {
    const container = overlayDraws_control.getContainer();
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
            replacedLabel = overlayDraws[name]['lay_name'];
        } catch (err) { }
        if (lbl.textContent.trim() === name || lbl.textContent.trim() === replacedLabel) {
            lbl.style.color = "red";
            lbl.style.fontWeight = "bold";
            lbl.style.fontSize = "13px";
        }
    });
}

// === Tooltip Aktivierung
function show_hide_tooltips(objectlayer, state) {
    if (objectlayer)
        objectlayer.eachLayer(layer => {
            const tooltip = layer.getTooltip();
            if (tooltip) {
                const el = tooltip.getElement();
                if (el)
                    if (state) el.style.display = 'block';
                    else el.style.display = 'none';
            }
        });
}

// === Funktion für Farbauswahl
function colorToRgba(color, alphaOverride = 1) {
    // Mapping für Farb-Namen
    const namedColors = {
        yellow: "#FFFF00",
        red: "#FF0000",
        black: "#000000",
        green: "#008000",
        gray: "#808080",
        magenta: "#FF00FF",
        orange: "#FFA500",
        blue: "#0000FF",
        pink: "#FFC0CB",
        white: "#FFFFFF"
    };

    // Wenn es ein bekannter Name ist, in Hex umwandeln
    if (namedColors[color.toLowerCase()]) {
        color = namedColors[color.toLowerCase()];
    }
    // Führendes # entfernen
    color = color.replace(/^#/, '');
    let r, g, b, a = 1;

    if (color.length === 8) { // RRGGBBAA
        r = parseInt(color.slice(0, 2), 16);
        g = parseInt(color.slice(2, 4), 16);
        b = parseInt(color.slice(4, 6), 16);
        a = parseInt(color.slice(6, 8), 16) / 255;
    } else if (color.length === 6) { // RRGGBB
        r = parseInt(color.slice(0, 2), 16);
        g = parseInt(color.slice(2, 4), 16);
        b = parseInt(color.slice(4, 6), 16);
    } else if (color.length === 3) { // RGB
        r = parseInt(color[0] + color[0], 16);
        g = parseInt(color[1] + color[1], 16);
        b = parseInt(color[2] + color[2], 16);
    } else {
        r = 128;
        g = 128;
        b = 128;
        a = 1;
    }

    // Alpha anwenden / überschreiben
    a = a * alphaOverride;

    return `rgba(${r},${g},${b},${a})`;
}

// === Funktion Farbauswahl: Rechtsklick-Event an Layer anhängen
function enableRightClickColor(layer) {
    layer.on("contextmenu", function (e) {
        contextTargetLayer = layer;
        // Menü an Mausposition setzen
        colorMenu.style.left = e.originalEvent.pageX + "px";
        colorMenu.style.top = e.originalEvent.pageY + "px";
        colorMenu.style.display = "block";
    });
}

// === Layer On-Click Funktion - zentrale Steuerung des Events
function layer_on_click(layer) {
    // console.log("Full layer object:", layer); // für Debug
    // console.log("FCID:", layer.fcid);
    layType = identify_lay_type(layer);

    if (typeof layer.getLatLng === "function") {
        // Marker, Circle, CircleMarker
        coords = layer.getLatLng();
    } else if (typeof layer.getLatLngs === "function") {
        // Polygon, Polyline, Rectangle
        coords = layer.getLatLngs();
    } else {
        coords = null;
    }
    // console.log(coords);
    //console.log("Layer-Typ:" + layType);
    // console.log("Farbe:", layer.options.color);

    status_span.textContent = '(Objekt) CID: ' + layer.fcid;

    if (layType != 'other') {
        url = webroot + 'forms/Dokumentation.php?fg=10100&fcid=' + layer.fcid;
        if (is_mobile)
            window.open(url, "mobile_doc", "");
        else
            if (geo_form_iframe) geo_form_iframe.src = url;
    }
}

// === Hilfsfunktion zum Abruf des zuletzt genutzen Layers
function getLastClickedOverlay() {
    return lastClickedOverlay;
}

// === Hilfsfunktion zur Umwandlung des LatLang in ein Array
function latLngToArray(obj) {
    if (!obj) throw new Error("Ungültiges LatLng-Objekt oder Array");

    // Einzelnes LatLng-Objekt
    if (obj.lat !== undefined && obj.lng !== undefined) {
        return [obj.lat, obj.lng];
    }

    // Array von Objekten oder verschachtelten Arrays
    if (Array.isArray(obj)) {
        return obj.map(item => {
            if (item.lat !== undefined && item.lng !== undefined) {
                return [item.lat, item.lng];
            } else if (Array.isArray(item)) {
                // Array von LatLngs → flach konvertieren
                return item.map(ll => [ll.lat, ll.lng]);
            } else {
                throw new Error("Ungültiges LatLng-Objekt im Array");
            }
        });
    }

    throw new Error("Ungültiges LatLng-Objekt oder Array");
}

// === evtl. löschen
function latLngsToArray(coords) {
    // if (!coords) return [];
    // if (Array.isArray(coords[0])) {
    //     return coords.map(ring => ring.map(ll => [ll.lat, ll.lng]));
    // }
    // return coords.map(ll => [ll.lat, ll.lng]);
}

// === Hilfsfunktion zur Identifzierung des Layer-Typs
function identify_lay_type(layer) {
    if (layer instanceof L.Marker) {
        return 'marker';
    } else if (layer instanceof L.Circle) { // reihenfolge cirlce und circleMarker wichtig
        return 'circle';
    } else if (layer instanceof L.CircleMarker) {
        return 'circleMarker';
    } else if (layer instanceof L.Rectangle) {
        return 'rectangle';
    } else if (layer instanceof L.Polygon) {
        return 'polygon';
    } else if (layer instanceof L.Polyline) {
        return 'polyline';
    } else return 'other';
}

// === Auslösen der Aktualieserung der Daten nach Zeichnen, Editieren oderLöschen
function update_object_layer(layer, lastClickedOverlay, mode = 'draw') {
    layer_id = lastClickedOverlay.fcid;
    layer_fcid = layer.fcid;

    if (mode == 'delete') {
        const del_json = {
            fcid: layer_fcid,
            'delete': 1
        };
        fetchDataAndUpdateGeoLayer(del_json);
        return 0;
    }

    layType = identify_lay_type(layer);

    layCoor = '';
    layer_radius = '';
    layer_color = color_choice.value;
    if (layer._latlng) layCoor = latLngToArray(layer._latlng);
    if (layer._latlngs) layCoor = latLngToArray(layer._latlngs);


    if (layType == 'marker') layer_color = '';
    if (layType == 'circle') layCoor.push(layer.options.radius);

    if (mode == 'edit')
        if (layType == 'rectangle') layType = 'polygon'; // because of rotation

    if (layType === 'polyline') { // TODO: 1 - Wichtig das ist nur ein Workaround weil Polyline so [[[ ]]] statt so [[]] gepeichert
        // Prüfen, ob layCoor dreidimensional ist
        if (Array.isArray(layCoor) &&
            layCoor.length > 0 &&
            Array.isArray(layCoor[0]) &&
            Array.isArray(layCoor[0][0])) {
            // Eine Ebene flach machen
            layCoor = layCoor.flat();
        }
    } 

    const geo_layer_jsn = {
        fcid: layer_fcid,
        layType: layType,
        layerId: layer_id,
        layCoor: JSON.stringify(layCoor),
        layColor: layer_color
    };
    // console.log(geo_layer_jsn);
    fetchDataAndUpdateGeoLayer(geo_layer_jsn);
}

// === Definitionen für Pin (Marker)
var redIcon = new L.Icon({
    iconUrl: 'img_plob/marker-icon-2x-red.png',
    shadowUrl: 'img_plob/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

var blackIcon = new L.Icon({
    iconUrl: 'img_plob/marker-icon-2x-black.png',
    shadowUrl: 'img_plob/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

// === Get img-layer data from php
const overlayLayer = {};
const overlayDraws = {};

// === Verabeitung der eingelesenen Objectlayer
Object.entries(all_object_layer_obj).forEach(([key, item]) => {
    overlayLayer[key] = L.featureGroup();
    overlayLayer[key]['fcid'] = item['fcid'];
    overlayLayer[key]['lay_name'] = item['layName'];
    overlayLayer[key]['layer_id'] = item['layerId'];
    overlayLayer[key]['loaded'] = 0;
    // LayerControl Overlay mit layName als Label
    const uniqueKey = item['fcid'];
    // overlayImgs dienen als Referenz auf die imgLayers, damit der name verwendet werden kann
    overlayDraws[uniqueKey] = overlayLayer[key];
});

// === Add empty layer to map
const overlayDraws_control = L.control.layers(null, overlayDraws, {
    position: 'bottomright',
    collapsed: false
}).addTo(map);

// === Neuer Layer Button und Ffunction
const addDrawLayerBtn = document.getElementById("addDrawLayerBtn");
if (addDrawLayerBtn) {
    addDrawLayerBtn.addEventListener("click", function () {
        const name = document.getElementById("layerNameInput").value || "Neuer Layer";
        createNewDrawLayer(name);
    });
}

// === OpenClose Button für container und zugehörige Funktionen
const container_br = overlayDraws_control._container;
if (is_mobile) container_br.style.minWidth = (map._size['x'] - 40) + 'px';

const toggleBtn_br = L.DomUtil.create('button', 'leaflet-control-collapse-btn', container_br);
// toggleBtn_br.innerHTML = '⇅'; 
toggleBtn_br.style.display = 'block';
toggleBtn_br.style.width = '100%';
toggleBtn_br.style.marginTop = '5px';
toggleBtn_br.style.height = '10px';
toggleBtn_br.style.cursor = 'pointer';

// === Klick-Event für Button
L.DomEvent.on(toggleBtn_br, 'click', function (e) {
    L.DomEvent.stopPropagation(e); // klick nur für Button
    L.DomEvent.preventDefault(e);

    if (container_br.classList.contains('leaflet-control-layers-expanded')) {
        container_br.classList.remove('leaflet-control-layers-expanded');
        container_br.classList.add('leaflet-control-layers-collapsed');
    } else {
        container_br.classList.remove('leaflet-control-layers-collapsed');
        container_br.classList.add('leaflet-control-layers-expanded');
    }
});

// === Control-Box Anspassungen aktivieren
assignDrawsControlIDs(overlayDraws_control, overlayDraws);

// === Definiton und Initialiserung der Drawlayer
L.EditToolbar.Delete.include({
    removeAllLayers: false
});
L.drawLocal = {
    draw: {
        toolbar: {
            // #TODO: this should be reorganized where actions are nested in actions
            // ex: actions.undo  or actions.cancel
            actions: {
                title: 'Zeichnen abbrechen',
                text: 'abbrechen'
            },
            finish: {
                title: 'Zeichnen beenden',
                text: 'beenden'
            },
            undo: {
                title: 'Zeichung rückgängig machen',
                text: 'zurück'
            },
            buttons: {
                polyline: 'Linie zeichnen',
                polygon: 'Polygon zeichnen',
                rectangle: 'Rechteck zeichnen',
                circle: 'Kreis zeichnen',
                marker: 'Marker setzen',
                circlemarker: 'Markierung setzen'
            }
        },
        handlers: {
            circle: {
                tooltip: {
                    start: 'Klicken und gedrückt halten zum anpassen.',
                },
                radius: 'Radius: '
            },
            circlemarker: {
                tooltip: {
                    start: 'Markierung setzen.'
                }
            },
            marker: {
                tooltip: {
                    start: 'Markierung setzen.'
                }
            },
            polygon: {
                tooltip: {
                    start: 'Klicken zum Starten.',
                    cont: 'Klicken zum Zwischenpunkt setzen.',
                    end: '(Doppel-)Klick schliesst das Polygon.'
                }
            },
            polyline: {
                error: '<strong>Fehler:</strong> Überkreuzungen sind untersagt!',
                tooltip: {
                    // start: 'Klicken zum Starten.',
                    // cont: 'Klicken zum Zwischenpunkt setzen.',
                    // end: '(Doppel-)Klick auf den letzten Punkt zum Beenden der Linie.'
                }
            },
            rectangle: {
                tooltip: {
                    start: 'Klicken und gedrückt halten zum anpassen.'
                }
            },
            simpleshape: {
                tooltip: {
                    end: 'Maus loslassen zum Beenden.'
                }
            }
        }
    },
    edit: {
        toolbar: {
            actions: {
                save: {
                    title: 'Änderungen speichern',
                    text: 'Speichern'
                },
                cancel: {
                    title: 'Editieren abbrechen, alle Änderungen verwerfen',
                    text: 'Abbrechen'
                },
                clearAll: {
                    title: 'Achtung! Alles wird sofort ohne Bestätigung gelöscht!',
                    text: 'ALLES LÖSCHEN'
                }
            },
            buttons: {
                edit: 'Editieren',
                editDisabled: 'Keine Layer zum Editieren eingebelendet',
                remove: 'Löschen',
                removeDisabled: 'Keine Layer zum Löschen eingebelendet'
            }
        },
        handlers: {
            edit: {
                tooltip: {
                    // text: 'Zum Ändern Ecken verschieben.',
                    // subtext: 'Abbrechen klicken, falls vertan.'
                }
            },
            remove: {
                tooltip: {
                    // text: 'Auswählen zum Löschen.'
                }
            }
        }
    }
};

// === Initialisierung Lyer
let editableLayer = L.featureGroup();
let drawControl = new L.Control.Draw({});

// === Initialisierung zuletzt genutzer Layer
let currentlyHighlighted = null;

// === Intilsierung Dokumentationsformular
const geo_form_iframe = document.getElementById('geo_form_iframe');

// === Intilsierung für Farbauswahl mit Rechtsklick
const colorMenu = document.getElementById("colorMenu");
let contextTargetLayer = null; // Layer, der rechtsgeklickt wurde

// === Eventlisterner Farbauswahl mit Rechtsklick
document.querySelectorAll(".color-swatch").forEach(swatch => {
    swatch.addEventListener("click", function () {
        const col = this.dataset.color;
        if (!contextTargetLayer) return;
        // Polygon / Polyline
        if (contextTargetLayer.setStyle) {
            contextTargetLayer.setStyle({
                color: col,
                fillColor: col
            });
        }
        // Marker
        if (contextTargetLayer instanceof L.Marker) {
            const newIcon = new L.Icon({
                iconUrl: contextTargetLayer.options.icon.options.iconUrl,
                iconSize: contextTargetLayer.options.icon.options.iconSize,
                iconAnchor: contextTargetLayer.options.icon.options.iconAnchor,
                className: "",
            });
            // Farbe kann man z.B. über CSS-Border oder farbige SVG lösen
        }
        // console.log("COL:", contextTargetLayer.fcid, col);
        const geo_layer_jsn = {
            fcid: contextTargetLayer.fcid,
            layColor: col
        };
        // console.log(geo_layer_jsn);
        fetchDataAndUpdateGeoLayer(geo_layer_jsn);
        colorMenu.style.display = "none";
    });
});

// === Farbmenü schliessen bei Ckick an andere Stelle
map.getContainer().addEventListener("click", () => {
    colorMenu.style.display = "none";
});

/************************************************/
/**** Zentrale Steuerung der MAP-Aktivitäten ****/
/************************************************/

// === Konturctur für Draws
const drawConstructors = {
    marker: L.Draw.Marker,
    polygon: L.Draw.Polygon,
    rectangle: L.Draw.Rectangle,
    polyline: L.Draw.Polyline,
    circle: L.Draw.Circle,
    circlemarker: L.Draw.CircleMarker
};

// === Layer-Gruppe (Overlay) einfügen
map.on("overlayadd", function (e) {
    // not for overlayImgs
    const isOverlayImg = Object.values(overlayImgs).includes(e.layer);
    if (isOverlayImg) return;

    lastClickedOverlay = e.layer;
    lastClickedName = e.name;
    highlightActiveOverlay(e.name);
    status_span.innerText = "(CID:" + e.name + ") Draw-Layer aktiviert!";
    editableLayer = e.layer;
    if (drawControl) {
        map.removeControl(drawControl);
    }
    drawControl = new L.Control.Draw({
        edit: {
            featureGroup: editableLayer,
            remove: true
        },
        draw: {
            polygon: true,
            polyline: true,
            rectangle: true,
            circle: true,
            marker: true,
            circlemarker: true // ACHTUNG hier andere Schreibweise als layer
        }
    });
    map.addControl(drawControl);

    const layerId = e.layer.fcid;
    const layerLabel_checkbox = document.getElementById('layerLabel');

    fetchGeoLayer(layerId).then(layerData => {
        // Optimierungsbedarf
        if (layerData) {
            let layCoor_a = null;
            let latlngs = null;
            for (i = 0; i < layerData.length; i++) {
                fcid = layerData[i]['fcid'].toString();
                layType = layerData[i]['layType'];
                layName = layerData[i]['layName'];
                layColor = layerData[i]['layColor'];
                layCoor_a = JSON.parse(layerData[i]['layCoor']);

                if (layType == 'marker') {
                    lay_obj = L.marker(layCoor_a, {
                        color: layColor,
                        weight: 1,
                        icon: blackIcon
                    });
                    lay_obj.addTo(e.layer);

                }

                if (layType == 'circleMarker') {
                    lay_obj = L.circleMarker(layCoor_a, {
                        color: layColor,
                        weight: 0.5,
                    });
                    lay_obj.addTo(e.layer);
                }

                if (layType == 'circle') {
                    lay_obj = L.circle([layCoor_a[0], layCoor_a[1]], {
                        radius: layCoor_a[2],
                        color: layColor,
                        weight: 0.5,
                    });
                    lay_obj.addTo(e.layer);
                }

                if (layType == 'rectangle') {
                    lay_obj = L.rectangle([layCoor_a], {
                        color: layColor,
                        weight: 0.5
                    });
                    lay_obj.addTo(e.layer);
                }

                if (layType == 'polygon') {
                    latlngs = layCoor_a.map(ring => ring.map(coord => L.latLng(coord[0], coord[1])));
                    lay_obj = L.polygon(latlngs, {
                        color: layColor,
                        weight: 0.5
                    });
                    lay_obj.addTo(e.layer);
                }

                if (layType == 'polyline') {
                    lay_obj = L.polyline([layCoor_a], {
                        color: layColor,
                        weight: 0.5
                    });
                    lay_obj.addTo(e.layer);
                }

                lay_obj.fcid = fcid;
                lay_obj.on('click', function (evt) {
                    const target = evt.target;
                    layer_on_click(target);
                });
                enableRightClickColor(lay_obj);

                // achtung tooltips werden nur unsichtbar gestellt, d.h. sie MÜSSSEN anfangs geladen werden.
                if (layName) {

                    lay_obj.bindTooltip(layName, {
                        permanent: true,
                        direction: 'center',
                        className: 'toolTipLabel', //  ' + layerElementA['color'] + '_TTL', // .red_TTL {background-color: rgb(238 27 27 / 50%);}
                        offset: [0, -17]
                    });

                    const tooltipEl = lay_obj.getTooltip().getElement();
                    tooltipEl.style.backgroundColor = colorToRgba(layColor, 0.3);
                    // tooltipEl.style.border = "1px solid " + layColor;       
                }
                if (layerLabel_checkbox.checked) show_hide_tooltips(lastClickedOverlay, 1);
                else show_hide_tooltips(lastClickedOverlay, 0);
            }

        }
    });

    e.layer['loaded'] = 0;
    layer_on_click(e.layer);
    // status_span.textContent = '(CID:' + e.layer.fcid + ')' + ' Objekt-Layer eingeblendet';
});

// === Event: Overlay deaktiviert 
map.on("overlayremove", function (e) {
    // not for overlayImgs
    const isOverlayImg = Object.values(overlayImgs).includes(e.layer);
    if (isOverlayImg) return;

    lastClickedOverlay = e.layer;
    lastClickedName = e.name;
    highlightActiveOverlay(""); // alle Farben zurücksetzen
    status_span.innerText = "(CID:" + e.name + ") Draw-Layer ausgeblendet!";
    if (drawControl) {
        map.removeControl(drawControl);
    }
    // clear layer an set status
    e.layer.clearLayers();
    e.layer['loaded'] = 0;
    status_span.textContent = '(CID:' + e.layer.fcid + ')' + ' Objekt-Layer ausgeblendet';
});

// === Zeichnen
map.on(L.Draw.Event.DRAWSTART, function (e) {
    lastDrawTool = e.layerType;
    console.log("Letztes Werkzeug:", lastDrawTool);
});

// === Löschen
map.on(L.Draw.Event.DELETED, function (event) {
    const obj_layers = event.layers; // Achtung ist eine layer-gruppe!
    obj_layers.eachLayer(function (lay_obj) {
        update_object_layer(lay_obj, lastClickedOverlay, 'delete');
    });

});

// === Editieren
map.on(L.Draw.Event.EDITED, function (event) {
    const obj_layers = event.layers; // Achtung ist eine layer-gruppe!
    obj_layers.eachLayer(function (lay_obj) {
        update_object_layer(lay_obj, lastClickedOverlay, 'edit');
        // console.log(lay_obj);
    });
});

// === Variable für zuletzt geklickten Layer
let lastClickedOverlay = null;
let lastClickedName = null;

// === Variable  power_paint
let lastDrawTool = null;

// === Wenn fertig gezeichnet
map.on(L.Draw.Event.CREATED, function (event) {
    const lay_obj = event.layer;
    lay_obj.options.color = color_choice.value;
    lay_obj.options.weight = 1;
    // const fcid = generatePinId();
    const fcid = get_fcid();

    lay_obj.fcid = fcid;
    lay_obj.on('click', function (evt) {
        const target = evt.target;
        layer_on_click(target);
    });

    const power_paint_state = document.getElementById('power_paint')?.checked ?? false;
    if (power_paint_state)
        if (lastDrawTool) { // reactivate layer
            const drawer = new drawConstructors[lastDrawTool](map, drawControl.options.draw[lastDrawTool]);
            drawer.enable();
        }

    enableRightClickColor(lay_obj); // Farbauswahl
    if (lastClickedOverlay) {
        lastClickedOverlay.addLayer(lay_obj);
        update_object_layer(lay_obj, lastClickedOverlay);
    } else {
        // layer_on_click.addTo(map); // wenn man das möchte :-)
        // könnte man zu demozwecken machen, wenn man diese nicht speichert
    }

});