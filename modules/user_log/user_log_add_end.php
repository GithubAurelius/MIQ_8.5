<script>
const user_a = <?= json_encode($user_a)?>;


const muidCells = document.querySelectorAll('td[data-col="muid"]');

muidCells.forEach(cell => {
    // 2. Den aktuellen Textinhalt der Zelle (die ID) abrufen
    const muidKey = cell.textContent.trim();
    
    // 3. Prüfen, ob dieser Key im 'user'-Objekt existiert
    if (user_a.hasOwnProperty(muidKey)) {
        
        // 4. Den Inhalt der Zelle durch den Wert aus dem Objekt ersetzen
        cell.textContent = muidKey +' (' +  user_a[muidKey] + ')';
        
        // Optional: Die Zelle kann die ID als data-Attribut behalten, falls nötig
        // cell.dataset.originalMuid = muidKey;
        
    } else {
        // Falls die ID nicht gefunden wurde (optional)
        cell.textContent = muidKey;
        // Optional: Den Inhalt mit einem Platzhalter überschreiben
        // cell.textContent = "Unbekannter Benutzer"; 
    }
});



// Konfigurierte Zeitgrenzen in Millisekunden
const FIVE_MINUTES_MS = 5 * 60 * 1000;
const FIFTEEN_MINUTES_MS = 15 * 60 * 1000;
const THIRTY_MINUTES_MS = 30 * 60 * 1000;

function styleLogoutCells() {
    // 1. Aktuelle Zeit abrufen (als Millisekunden)
    const now = new Date().getTime();
    
    // 2. Alle relevanten Zellen auswählen
    const logoutCells = document.querySelectorAll('td[data-col="logged_out"]');

    logoutCells.forEach(cell => {
        const timestampString = cell.textContent.trim();
        
        // Prüfen, ob der Zeitstempel gültig ist
        if (!timestampString) {
            return; // Leere Zellen ignorieren
        }

        // 3. Zeitstempel in ein Date-Objekt umwandeln
        // ACHTUNG: Das Format 'YYYY-MM-DD HH:MM:SS' funktioniert direkt in den meisten Browsern.
        const logoutTime = new Date(timestampString).getTime();
        
        // 4. Prüfen, ob der Zeitstempel ungültig ist (NaN)
        if (isNaN(logoutTime)) {
            console.warn(`Ungültiger Zeitstempel gefunden: ${timestampString}`);
            return;
        }

        // 5. Zeitdifferenz berechnen (in Millisekunden)
        const diffMs = now - logoutTime;
        
        // 6. Nur Datensätze des heutigen Tages berücksichtigen (oder gestern/morgen, falls die Differenz zu groß ist)
        const isToday = new Date(logoutTime).toDateString() === new Date().toDateString();

        // 7. Styling anwenden
        let color = '';

        if (isToday) {
            if (diffMs < FIVE_MINUTES_MS) {
                // Jünger als 5 Minuten (sehr aktuell)
                color = 'red'; 
            } else if (diffMs < FIFTEEN_MINUTES_MS) {
                // Jünger als 15 Minuten
                color = 'yellow';
            } else if (diffMs < THIRTY_MINUTES_MS) {
                // Jünger als 30 Minuten
                color = 'green';
            } else {
                // Älter als 30 Minuten, aber noch heute
                color = 'lightgray';
            }
        }
        
        // Farbe setzen
        cell.style.backgroundColor = color;
    });
}

// Funktion einmal beim Laden der Seite ausführen
styleLogoutCells();

// Optional: Führen Sie die Funktion regelmäßig aus, um die Farben automatisch zu aktualisieren
// setInterval(styleLogoutCells, 60000); // z.B. jede Minute

// Init
    const wb_width = 2/5;
    const wb_height = 0.3;
    const winbox_num = 2;
    const outer_wb_add = window.top.findClosestWinboxFromIframe(window.frameElement);
    outer_wb_add.winbox.setBackground("red");





</script>