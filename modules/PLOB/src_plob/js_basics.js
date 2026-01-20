/*
Hier werden alle Grundfunktionen integriert, die erst nach den Standard HTML-Objekten starten 
können. Unser Philisophie ist ein möglichst langer Verzicht auf Blöcke in
     document.addEventListener('DOMContentLoaded', function() {})
um sequenzielle und interative Prozesse verständichlich zu halten. 
*/

if (is_mobile) {
    geo_form.remove();
}


const map = new L.Map('map', {
    zoomControl: zoomControl,
    // dragging: !L.Browser.mobile,
    center: centerOfObject,
    maxZoom: 28,
    minZoom: planMinzoom,
    zoom: startZoom,
    bearing:planBearing,
    rotate: true,
    fullscreenControl: true,
    fullscreenControlOptions: {
        position: 'topleft'
    }
});
window.parent_map = map; 

if (add_on_a['scale']) {
    L.control.scale({
        position: 'bottomright',
        maxWidth: 300,
        imperial: false,
        metric: true,
        updateWhenIdle: true // performance save
    }).addTo(map);
}

if (add_on_a['polylineMeasure']) {
    let polylineMeasure = L.control.polylineMeasure({
        position: 'topcenter2',
        unit: 'kilometres',
        position: 'bottomleft',
        clearMeasurementsOnStop: false,
        showClearControl: true
    })
    polylineMeasure.addTo(map);
    const controlContainer = polylineMeasure.getContainer();
    document.getElementById('myMeasureDiv').appendChild(controlContainer);
}

if (add_on_a['mapRotation']) {
    // Slider für Map-Rotation
    const slider = document.getElementById('rotateSlider');
    const bearingVal = document.getElementById('bearingVal');
    slider.addEventListener('input', () => {
        const bearing = Number(slider.value);
        bearingVal.textContent = `${bearing}°`;
        // leaflet-rotated setzt die Kartenrotation
        map.setBearing(bearing);
    })
}

// hier werden einige custom-buttons integriert, der erste ab start
map.whenReady(() => {
    const zoomContainer = document.querySelector('.leaflet-control');

    if (add_on_a['showTools']) {
        // custom button
        const tools_btn = L.DomUtil.create('a', 'leaflet-control-zoom-custom', zoomContainer);
        tools_btn.innerHTML = '📐'; // Symbol oder Text
        tools_btn.href = '#';
        tools_btn.title = 'Tools ein-/ ausblenden';
        L.DomEvent.on(tools_btn, 'click', (e) => {
            L.DomEvent.stopPropagation(e);
            L.DomEvent.preventDefault(e);
            toggle_display('ui_tools')
        });
    }
});


let screenshot_btn = null; // globale Variable für den Button
function toggleScreenshotBtn(ckb) {
    const zoomContainer = document.querySelector('.leaflet-control');

    if (!add_on_a['screenShots']) return;

    if (ckb.checked) {
        // Button nur erstellen, wenn er noch nicht existiert
        if (!screenshot_btn) {
            screenshot_btn = L.DomUtil.create('a', 'leaflet-control-screenshot-custom', zoomContainer);
            screenshot_btn.innerHTML = '<i class="material-icons">photo_camera</i>';
            screenshot_btn.href = '#';
            screenshot_btn.title = 'Temporäre Screenshots';

            const screenShotHandler = (e) => {
                catch_screen();
            };
            L.DomEvent.on(screenshot_btn, 'click', screenShotHandler);
        }
    } else {
        // Button entfernen, wenn Checkbox abgewählt
        if (screenshot_btn) {
            screenshot_btn.remove();
            screenshot_btn = null;
            L.DomEvent.on(screenshot_btn, 'click', screenShotHandler);
        }
    }
}


// const imgFixingEndBtnHandler = (e) => {
//                 markerTL.remove();
//                 markerTR.remove();
//                 markerBL.remove();
//                 L.DomEvent.off(imgFixSave_btn, 'click', imgSaveFixingBtnHandler);
//                 imgFixSave_btn.remove();
//                 L.DomEvent.off(imgFixSave_btn, 'click', imgFixingEndBtnHandler);
//                 imgFixStop_btn.remove();
//             };
//             


/* MODULE: my position */
function myPosition() {
    const options = {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0,
    };
    let locationMarker = null;

    function success(pos) {
        const crd = pos.coords;
        const latlng = [crd.latitude, crd.longitude]; // Array für Leaflet Koordinaten
        map.flyTo(latlng, 15, { // Zoomstufe 21 ist oft zu hoch, 15 ist ein guter Start
            animate: true,
            duration: 1.75
        });
        let mark_place = false;
        if (mark_place) {
            if (locationMarker) {
                locationMarker.setLatLng(latlng);
            } else {
                ocationMarker = L.marker(latlng).addTo(map)
                    .bindPopup("Dein Standort").openPopup();
            }
        }
    }

    function error(err) {
        console.warn(`ERROR(${err.code}): ${err.message}`);
    }
    navigator.geolocation.getCurrentPosition(success, error, options);
}



// Funktion zum Erzeugen von Textkomentaren ohne PIN evtl. für Image-Layer
// map.on('click', function(e) {
//     const latlng = e.latlng;

//     // 1) Marker setzen
//     const marker = L.marker(latlng).addTo(imgLayer);

//     // 2) Popup mit Input anzeigen
//     const popupContent = document.createElement('div');
//     const input = document.createElement('input');
//     input.type = 'text';
//     input.placeholder = 'Kommentar eingeben';
//     const btn = document.createElement('button');
//     btn.innerText = 'Speichern';
//     popupContent.appendChild(input);
//     popupContent.appendChild(btn);

//     marker.bindPopup(popupContent).openPopup();

//     // 3) Klick auf Speichern
//     btn.addEventListener('click', () => {
//         const text = input.value.trim();
//         if (!text) return alert('Bitte Text eingeben');

//         // Marker entfernen
//         imgLayer.removeLayer(marker);

//         // Text als DivIcon hinzufügen
//         const textIcon = L.divIcon({
//             className: 'text_only_label',
//             html: text,
//             interactive: false
//         });
//         L.marker(latlng, {
//             icon: textIcon
//         }).addTo(map);
//     });
// });