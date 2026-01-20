<style>
    #screenshot_container {
        background-color: white;
        z-index: 1000;
        position: absolute;
        left: 20px;
        bottom: 20px;
        padding-right: 15px;
    }

    .screenshot_container {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        background-color: #f5f5f5;
        box-shadow: 0 2px 4px rgba(52, 52, 52, 0.1);
        max-width: 80%;
        overflow-y: hidden;
        white-space: nowrap;
        display: none;
        /* display: flex;  */
    }

    .screenshot_container img {
        max-width: 100%;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 5px;
        /* margin-bottom: 15px; */
    }
</style>
<div id='screenshot_container' class='screenshot_container'></div>
<script>
    function catch_screen() {

        function dataURIToBlob(dataURI) {
            // Hilfsfunktion: Konvertiert Base64 zu einem Blob-Objekt 
            // Die Bilder werden ohne den Umweg über die dataURI aus 
            // Browser-Sicherheitsgründen nicht angezeigt 
            // Teilt den Data URI in Mime-Type und Base64-Daten
            var parts = dataURI.split(';base64,');
            var mimeType = parts[0].split(':')[1];
            var byteString = atob(parts[1]);
            var ab = new ArrayBuffer(byteString.length);
            var ia = new Uint8Array(ab);

            // Füllt den ArrayBuffer mit den Daten
            for (var i = 0; i < byteString.length; i++) {
                ia[i] = byteString.charCodeAt(i);
            }

            // Erstellt und gibt das Blob-Objekt zurück
            return new Blob([ab], {
                type: mimeType
            });
        }

        simpleMapScreenshoter.takeScreen('image', {
            caption: function() {
                return ''
            }
        }).then(image => {
            var link = document.createElement('a');
            var dataURL = image;
            link.href = '#';
            link.target = '_blank';

            var img = document.createElement('img');
            img.src = dataURL;
            img.width = 400;
            img.id = 'screenShotPic';
            link.appendChild(img);

            L.DomEvent.on(link, 'click', function(e) {
                L.DomEvent.preventDefault(e);
                var blob = dataURIToBlob(dataURL); // Konvertiere die Data URL in einen Blob
                var fileURL = URL.createObjectURL(blob); // Erstelle eine temporäre Blob URL
                var newWindow = window.open(fileURL, '_blank');
                // Nach dem Öffnen die temporäre Blob URL freigeben (Wichtig für die Performance!)
                // Das Freigeben muss nach kurzer Verzögerung erfolgen, damit der Browser Zeit hat, 
                // die URL zu laden.
                if (newWindow) {
                    newWindow.onload = function() {
                        URL.revokeObjectURL(fileURL);
                    };
                } else {
                    URL.revokeObjectURL(fileURL);
                }
            });

            const container = document.getElementById('screenshot_container');
            // container.innerHTML = ''; // wenn wir nur ein Bild haben wollen 
            container.appendChild(link);

        }).catch(e => {
            alert(e.toString())
        })
    }

    let pluginOptions = {
        cropImageByInnerWH: true, // crop blank opacity from image borders
        hidden: true, // hide screen icon
        preventDownload: false, // prevent download on button click
        domtoimageOptions: {}, // see options for dom-to-image
        position: 'topleft', // position of take screen icon
        screenName: 'screen', // string or function
        hideElementsWithSelectors: ['.leaflet-control-container'], // by default hide map controls All els must be child of _map._container
        mimeType: 'image/png', // jpeg 
        caption: 'MIQ', // string or function, added caption to bottom of screen
        captionFontSize: 15,
        captionFont: 'Arial',
        captionColor: 'black',
        captionBgColor: 'white',
        captionOffset: 5,
        // callback for manually edit map if have warn: "May be map size very big on that zoom level, we have error"
        // and screenshot not created
        onPixelDataFail: async function({
            node,
            plugin,
            error,
            mapPane,
            domtoimageOptions
        }) {
            // Solutions:
            // decrease size of map
            // or decrease zoom level
            // or remove elements with big distanses
            // and after that return image in Promise - plugin._getPixelDataOfNormalMap
            return plugin._getPixelDataOfNormalMap(domtoimageOptions)
        }
    }
    
    var simpleMapScreenshoter = L.simpleMapScreenshoter(pluginOptions).addTo(map)

    // Evtl. Workaround Performance Problem bei Zoom
    // if (map.getZoom() > SOME_MAX_ZOOM) {
    //     map.setZoom(SOME_MAX_ZOOM);
    // }
    // oder


    // onPixelDataFail: async function({
    //     node,
    //     plugin,
    //     error,
    //     mapPane,
    //     domtoimageOptions
    // }) {
    //     return plugin._getPixelDataOfNormalMap(domtoimageOptions)
    // }

    // CHATGTP: Wenn du willst, kann ich eine Code-Modifikation für dein 
    // Projekt schreiben, bei der vor dem Screenshot automatisch geprüft wird, 
    // ob die Zoom-Stufe „zu hoch“ ist, und entsprechend der Zoom reduziert 
    // oder der Screenshot mit einer Warnung verweigert wird. Möchtest du das?
</script>