<!-- 
Hier werden die zentralen Includes zu JavaScript und CSS abgelegt
Übergreifende (globale) Variablen und Funkitonen können hier auch bereits
definiert werden. 
-->

<!-- leaflet -->
<link rel="stylesheet" href="src_thirdParty/leaflet/leaflet.css" />
<script src="src_thirdParty/leaflet/leaflet.js"></script>
<!-- image rotate -->
<script src="src_thirdParty/leafletAdds/Leaflet.ImageOverlay.Rotated.js"></script>
<!--fullscreen -->
<link rel="stylesheet" href="src_thirdParty/leafletAdds/Control.FullScreen.css" />
<script src="src_thirdParty/leafletAdds/Control.FullScreen.js"></script>
<!-- bearing and rotate -->
<script src="src_thirdParty/leaflet-rotate-master/dist/leaflet-rotate.js"></script>
<!-- polyline -->
<script src="src_thirdParty/Leaflet.PolylineMeasure-master/Leaflet.PolylineMeasure.js"></script>
<!-- DRAW: MAIN -->
<link rel="stylesheet" href="src_thirdParty/leaflet.draw/src/leaflet.draw.css" />
<script src="src_thirdParty/leaflet.draw/src/Leaflet.draw.js"></script>
<script src="src_thirdParty/leaflet.draw/src/Leaflet.Draw.Event.js"></script>
<script src="src_thirdParty/leaflet.draw/src/ext/GeometryUtil.js"></script>
<!-- DRAW: Toolbar -->
<script src="src_thirdParty/leaflet.draw/src/Control.Draw.js"></script>
<script src="src_thirdParty/leaflet.draw/src/Toolbar.js"></script>
<script src="src_thirdParty/leaflet.draw/src/Tooltip.js"></script>
<script src="src_thirdParty/leaflet.draw/src/draw/DrawToolbar.js"></script>
<!-- DRAW: draw -->
<script src="src_thirdParty/leaflet.draw/src/draw/handler/Draw.Feature.js"></script>
<script src="src_thirdParty/leaflet.draw/src/draw/handler/Draw.SimpleShape.js"></script>
<script src="src_thirdParty/leaflet.draw/src/draw/handler/Draw.Polyline.js"></script>
<script src="src_thirdParty/leaflet.draw/src/draw/handler/Draw.Marker.js"></script>
<script src="src_thirdParty/leaflet.draw/src/draw/handler/Draw.Circle.js"></script>
<script src="src_thirdParty/leaflet.draw/src/draw/handler/Draw.CircleMarker.js"></script>
<script src="src_thirdParty/leaflet.draw/src/draw/handler/Draw.Polygon.js"></script>
<script src="src_thirdParty/leaflet.draw/src/draw/handler/Draw.Rectangle.js"></script>
<script src="src_thirdParty/leaflet.draw/src/ext/LineUtil.Intersect.js"></script>
<script src="src_thirdParty/leaflet.draw/src/ext/Polygon.Intersect.js"></script>
<script src="src_thirdParty/leaflet.draw/src/ext/Polyline.Intersect.js"></script>
<!-- DRAW: edit & delete -->
<script src="src_thirdParty/leaflet.draw/src/edit/EditToolbar.js"></script>
<script src="src_thirdParty/leaflet.draw/src/ext/LatLngUtil.js"></script>
<script src="src_thirdParty/leaflet.draw/src/ext/TouchEvents.js"></script>
<script src="src_thirdParty/leaflet.draw/src/edit/handler/EditToolbar.Edit.js"></script>
<script src="src_thirdParty/leaflet.draw/src/edit/handler/EditToolbar.Delete.js"></script>
<script src="src_thirdParty/leaflet.draw/src/edit/handler/Edit.Poly.js"></script>
<script src="src_thirdParty/leaflet.draw/src/edit/handler/Edit.SimpleShape.js"></script>
<script src="src_thirdParty/leaflet.draw/src/edit/handler/Edit.Rectangle.js"></script>
<script src="src_thirdParty/leaflet.draw/src/edit/handler/Edit.Marker.js"></script>
<script src="src_thirdParty/leaflet.draw/src/edit/handler/Edit.CircleMarker.js"></script>
<script src="src_thirdParty/leaflet.draw/src/edit/handler/Edit.Circle.js"></script>
<!-- rotate rectangle -->
<!-- <script src="src_thirdParty/Leaflet.draw.rotate-main/src/js/L.Path.Transform.js"></script> -->
<!-- <script src="src_thirdParty/Leaflet.draw.rotate-main/src/js/Edit.Rectangle.Rotate.js"></script> -->

<script src="src_thirdParty/Leaflet.draw.rotate-main/dist/leaflet-draw-rotate.js"></script>
<script src="src_thirdParty/Leaflet.draw.rotate-main/dist/Edit.Rectangle.Rotate.js"></script>
<!-- screenshooter -->
<script src="src_thirdParty/screenshoter/leaflet-simple-map-screenshoter.js"></script>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<!-- <script src="src_thirdParty/Leaflet.Editable-master/src/Leaflet.Editable.js"></script>
<script src="src_thirdParty/Leaflet.Editable.Drag-master/dist/L.Editable.Drag.js"></script>
<script src="src_thirdParty/Leaflet.Editable.Drag-master/dist/L.Editable.Drag-src.js"></script>
<script src="src_thirdParty/Leaflet.Path.Drag-master/src/Path.Drag.js"></script>
<script src="src_thirdParty/Leaflet.Path.Transform-master/src/header.js"></script>
<script src="src_thirdParty/Leaflet.Path.Transform-master/src/Util.js"></script>
<script src="src_thirdParty/Leaflet.Path.Transform-master/src/Matrix.js"></script>
<script src="src_thirdParty/Leaflet.Path.Transform-master/src/Path.Transform.js"></script> -->

<style>
    html,
    body,
    #map {
        height: 100%;
        margin: 0;
        font-family: arial;
    }

    #ui_tools {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.9);
        padding: 10px;
        border: solid 0.5px dimgray;
        border-radius: 4px;
        font-family: sans-serif;
        line-height: 1.4;
    }

    #ui_tools label {
        display: block;
        margin-top: 4px;
    }

    /* Standard-Kreuz für die ganze Karte */
    #map {
        cursor: crosshair;
    }

    /* Hand-Cursor für draggable Marker */
    .leaflet-marker-draggable {
        cursor: grab;
        /* Hand zum Greifen */
    }

    .leaflet-marker-draggable:active {
        cursor: grabbing;
        /* Hand beim Ziehen */
    }

    /* Optional: beim Überfahren des Bildes */
    .rotated-image:hover {
        cursor: grab;
    }

    #tools_table td {
        border-bottom: solid silver 0.5px;
        padding-top: 3px;
        padding-bottom: 8px;
    }

    .tools_desc {
        vertical-align: top;
        padding-right: 15px;
    }

    #img_slider_control {
        position: absolute;
        left: 50%;
        top: 10px;
        transform: translateX(-50%);
        z-index: 1000;
        background: rgba(255, 255, 255, 0.3);
        color: black;
        padding: 5px;
        padding-bottom: 10px;
        border-radius: 4px;
        box-shadow: 0 1px 5px rgba(0, 0, 0, 0.65);
    }

    #img_slider_control .tools_slider {
        background: black;
    }

    #img_slider_control .tools_slider::-webkit-slider-thumb {
        background: black;
    }

    .tools_slider {
        -webkit-appearance: none;
        width: 150px;
        height: 4px;
        background: #dddddd;
        border-radius: 4px;
    }

    /* 1. Schieberegler (Thumb) anpassen */
    .tools_slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 18px;
        height: 18px;
        background: #000000ff;
        cursor: pointer;
        border-radius: 50%;
        margin-top: 0;
    }

    .leaflet-control-layers {
        max-height: 45vh;
        max-width: 30vw;

    }

    /* Zielt auf den Scroll-Bereich innerhalb des Containers, um Scrollen zu aktivieren */
    .leaflet-control-layers-list {
        max-height: 43vh;
        overflow-y: auto;
        padding-right: 10px;
    }

    .overlay-label-text {
        display: inline-block;
        max-width: 240px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
    }

    /* kompass-symbol ausblenden */
    .leaflet-control-rotate {
        display: none !important;
    }

    .leaflet-control-scale {
        background: transparent;
        border-radius: 4px;
        /* padding: 2px 6px; */
        font-size: 12px;
        color: #333;
    }

    .leaflet-control-attribution {
        display: none !important;
    }

    .material-icons {
        padding-top: 4px;
    }

    .text_only_label {
        color: red;
        font-weight: bold;
        font-size: 14px;
        background: transparent;
        border-radius: 4px;
        padding: 2px 4px;
        white-space: nowrap;
        pointer-events: none;
        /* verhindert, dass es klickbar ist */
    }

    #status_info {
        font-size: 12px;
        position: absolute;
        border: solid dimgray 1px;
        padding: 3px;
        border-radius: 6px;
        left: 45%;
        bottom: 10px;
        z-index: 1000;
        background-color: rgba(250, 250, 250, 0.45)
    }

    .mini_text {
        font-size: 9px;
    }

    #colorMenu {
        position: absolute;
        display: none;
        background: white;
        padding: 6px;
        border: 1px solid #aaa;
        border-radius: 5px;
        z-index: 9999;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
    }

    .color-swatch {
        width: 24px;
        height: 24px;
        border-radius: 4px;
        margin: 4px;
        cursor: pointer;
        display: inline-block;
        border: 1px solid #555;
    }

    .input-group {
        display: flex;
        /* kein interner Abstand zwischen den Elementen ist */
        gap: 0;
        /* bündig mit der Ober- und Unterseite abschließen */
        align-items: center;
        /* dass die Gruppe die volle Breite der TD nutzt */
        width: 100%;
    }

    .input-group input {
        /* Lässt das Input-Feld den gesamten verfügbaren Platz ausfüllen */
        /* Wächst, um den gesamten Platz vor dem Button einzunehmen */
        flex-grow: 1;
        /* entfernt jeden Standard-Margin/Padding des Browsers, der Abstand verursachen könnte */
        margin: 0;
        padding: 5px;
        /* das Input-Feld nicht verkleinert wird */
        min-width: 0;
    }

    .input-group button {
        /* Stellt sicher, dass der Button keinen eigenen Rand hat */
        margin: 0;
        padding: 3px 3px;
    }

    .toolTipLabel {
        background-color: silver;
        color: white;
        border: solid 0;
        position: absolute;
        padding-top: 0;
        margin: 0;
        height: 11px;
        font-size: 11px;
    }

    .link_span{
        /* color: darkgreen; */
        font-weight: bold;
    }
</style>

<script>
    // Global Helper
    function get_fcid() {
        // getSafeBigIntTimestamp
        const date = new Date();
        date.setHours(date.getHours() + 2);
        // Basis: YYYYMMDDHHMMSS
        const YYYY = date.getFullYear();
        const MM = String(date.getMonth() + 1).padStart(2, '0');
        const DD = String(date.getDate()).padStart(2, '0');
        const HH = String(date.getHours()).padStart(2, '0');
        const mm = String(date.getMinutes()).padStart(2, '0');
        const ss = String(date.getSeconds()).padStart(2, '0');
        const base = `${YYYY}${MM}${DD}${HH}${mm}${ss}`;
        // Millisekunden (2 Ziffern) + Zufallszahl (optional) für Eindeutigkeit
        const ms = String(date.getMilliseconds()).padStart(3, '0').slice(0, 2);
        const timestampStr = `${base}${ms}`;
        // const random = String(Math.floor(Math.random() * 100)).padStart(2, '0'); // 2 Ziffern Zufall
        // const timestampStr = `${base}${ms}${random}`; // z.B. 202511071423151287
        // return BigInt(timestampStr);
        return parseInt(timestampStr)
    }

    function toggle_display(id, prev = 'block') {
        const toolsDiv = document.getElementById(id);
        if (toolsDiv.style.display === 'none' || toolsDiv.style.display === '') {
            toolsDiv.style.display = prev;
        } else {
            toolsDiv.style.display = 'none';
        }
    }

    function checkMobileMode(mediaQuery) {
        if (mediaQuery.matches) return true;
        else return false;
    }
    
    const mobileMedia = window.matchMedia("(max-width: 767px)");
    // mobileMedia.addEventListener('change', checkMobileMode); // optional
    const is_mobile = checkMobileMode(mobileMedia);
    

    // Optionale GEO-Karten    
    // === Karte ===
    const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    });

    const osmHot = L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
        maxNativeZoom: 19,
        maxZoom: 30,
        attribution: '&copy; OpenStreetMap contributors, Humanitarian'
    });

    const googleMaps = L.tileLayer('https://www.google.cn/maps/vt?lyrs=s@189&gl=cn&x={x}&y={y}&z={z}', {
        maxNativeZoom: 21,
        maxZoom: 30,
        attribution: 'google'
    });

    const openTopoMap = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
        maxZoom: 17,
        attribution: 'Map data: &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, <a href="http://viewfinderpanoramas.org">SRTM</a> | Map style: &copy; <a href="https://opentopomap.org">OpenTopoMap</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA</a>)'
    });

    const esriImagery = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
    });

    const cycle = L.tileLayer('https://{s}.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    });

    const oepnv = L.tileLayer('https://tile.memomaps.de/tilegen/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    });

    const rails = L.tileLayer('https://{s}.tiles.openrailwaymap.org/standard/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    });

    const emptyLayer = L.layerGroup();
    const baseMaps = {
        "Keine Karte": emptyLayer,
        "Topographisch": openTopoMap,
        "Fahrrad": cycle,
        "ÖPNV": oepnv,
        "Zugverkehr": rails,
        "Openstreetmap": osmHot,
        "Satellitenkarte": esriImagery,
        "Google Maps": googleMaps
    };

    zoomControl = <?php echo $add_on_a['zoom'] ?>;
</script>