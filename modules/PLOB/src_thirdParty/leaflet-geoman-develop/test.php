<!DOCTYPE>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Leaflet Geometry Management</title>



    <link rel="stylesheet" href="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.css" />
    <style>
        #map {
            height: 180px;
        }
    </style>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script src="https://unpkg.com/@geoman-io/leaflet-geoman-free@latest/dist/leaflet-geoman.min.js"></script>

</head>

<body>
    <div style="height: 700px" id="map"></div>

    <script>
        var centerOfObject = [49.492054111, 8.466557111];
        var map = L.map('map').setView(centerOfObject, 13);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        map.pm.addControls({
            position: 'topleft'
        });

        map.on('pm:create', function(e) {
            var layer = e.layer;
            var feature = layer.toGeoJSON(12);
            // console.log(feature);   

        });
    </script>


</body>

</html>