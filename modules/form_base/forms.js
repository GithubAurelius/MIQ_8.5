// BTB ONLY ENDE
function eL_radio_uncheck() {
    const radioButtons = document.querySelectorAll('input[type="radio"]');
    radioButtons.forEach(radioButton => {
        radioButton.addEventListener('dblclick', function () {
            if (this.checked) {
                this.checked = false;
            }
        });
    });
}

function eL_input_change() {
    const inputElements = document.querySelectorAll('form input, form select, form textarea');
    inputElements.forEach(inputElement => {
        inputElement.addEventListener('change', function () {
            this.style.backgroundColor = 'lightgreen';
            //document.getElementById("CID").value = this.id + " "  + this.value;  
        });
    });
}

function eL_form_submit() {
    const formular = document.getElementById('main_form');
    if (document.getElementById("fcid").value != "")
        formular.addEventListener('submit', function (event) {
            event.preventDefault(); // Verhindert das Standard-Submit-Verhalten
            // const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            // checkboxes.forEach(checkbox => {
            //     if (!checkbox.checked) {
            //         // Verstecktes Input-Feld mit dem Wert 0
            //         const hiddenField = document.createElement('input');
            //         hiddenField.type = 'hidden';
            //         hiddenField.name = checkbox.name;
            //         hiddenField.value = '0';
            //         formular.appendChild(hiddenField);
            //     }
            // });

            const radioGroups = {};
            const radioButtons = document.querySelectorAll('input[type="radio"]');
            radioButtons.forEach(radioButton => {
                const groupName = radioButton.name;
                if (!radioGroups[groupName]) {
                    radioGroups[groupName] = [];
                }
                radioGroups[groupName].push(radioButton);
            });
            for (const groupName in radioGroups) {
                let isChecked = false;
                radioGroups[groupName].forEach(radioButton => {
                    if (radioButton.checked) {
                        isChecked = true;
                    }
                });
                if (!isChecked) {
                    const hiddenField = document.createElement('input');
                    hiddenField.type = 'hidden';
                    hiddenField.name = groupName;
                    hiddenField.value = '';
                    formular.appendChild(hiddenField);
                }
            }

            formular.submit();
        });
}

function eL_uploads() {
    const uploadFields = document.querySelectorAll('[data-drop-area]'); // Elemente mit data-drop-area-Attribut
    uploadFields.forEach(uploadField => {
        const dropArea = uploadField; // Drop-Area
        //const fileInputId = dropArea.getAttribute('data-file-input'); // Hole die ID des zugehörigen File-Inputs
        const fileInputId = dropArea.querySelector('input[type="file"]').id;
        const fileInput = document.getElementById(fileInputId); // Zuordnung zum File-Input

        if (!fileInput) {
            console.error(`Kein File-Input mit der ID "${fileInputId}" gefunden.`);
            return;
        }

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Verhindere das Standardverhalten beim Ziehen von Dateien
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        // Hervorhebung der Drop-Area beim Ziehen
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => dropArea.classList.add('hover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => dropArea.classList.remove('hover'), false);
        });

        // Dateien beim Ablegen verarbeiten
        dropArea.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            fileInput.files = files; // Dateien in das File-Input einfügen
        });
    });
}

function eL_upload_info_button() {
    const infoBox_buttons = document.querySelectorAll('.filedict_button');
    infoBox_buttons.forEach(button => {
        const infoButton = document.getElementById(button.id);
        const infoBox = document.getElementById('info_' + button.id.split("_")[1]);
        infoButton.addEventListener('click', () => {
            if (infoBox.style.display === 'none') {
                infoBox.style.display = 'block';
            } else {
                infoBox.style.display = 'none';
            }
        });
    });
}

function fetch_uploads(path, file_str, fid) {
    fetch(path + 'fetch_get_uploads.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'file_str=' + file_str + '&fid=' + fid
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Netzwerkantwort fehlerhaft');
            }
            return response.text(); // Oder response.json() falls JSON 
        })
        .then(data => {
            // console.log('Executed:', data);
            document.getElementById('info_' + fid).innerHTML = data;
        })
        .catch(error => {
            console.error('Fehler:', error);
        });
}

// BTB ENDE

function showMessageBox(newMessage) {
    const messageBoxWrapper = document.getElementById('messageBoxWrapper');
    const messageElement = document.getElementById('message');
    if (messageElement) {
        messageElement.innerHTML = newMessage;
    }
    if (messageBoxWrapper) {
        messageBoxWrapper.classList.remove('hidden');
    }
}

// Funktion zum Schließen der Message-Box
function closeMessageBox() {
    const messageBoxWrapper = document.getElementById('messageBoxWrapper');
    if (messageBoxWrapper) {
        // console.log(messageBoxWrapper);
        messageBoxWrapper.classList.add('hidden');
    }
}

function getOwnIframeElement() {
    if (window.parent && window.parent !== window) {
        const iframes = window.parent.document.getElementsByTagName('iframe');
        for (const iframe of iframes) {
            if (iframe.contentWindow === window) {
                return iframe; // ← Das ist der iframe, in dem du läufst
            }
        }
    }
    return null;
}

function normalize(str) {
    return str
        .toLowerCase()
        .replace(/ä/g, "ae")
        .replace(/ö/g, "oe")
        .replace(/ü/g, "ue")
        .replace(/ß/g, "ss")
        .replace(/\s+/g, "-");
}

function getCid() {
    // let date = new Date();
    // date.setHours(date.getHours() + 1); 
    // return parseInt(date.toISOString().split('.')[0].replace(/[^0-9]/g, '')); // .split('.')[0] Micros
    let date = new Date();
    date.setHours(date.getHours() + 2);
    let base = date.toISOString().split('.')[0].replace(/[^0-9]/g, '');
    let ms = String(date.getMilliseconds()).padStart(3, '0').slice(0, 2);
    // console.log(base + ' ' + ms);
    return (base + ms); // parseInt
}

function getElementTypeByName(name) {
    const element = document.querySelector(`[name="${name}"]`);
    if (element) {
        return element.type || element.tagName;
    } else {
        return null;
    }
}

function get_field_ids(path) {
    const elements = document.querySelectorAll('.filedict_button'); // Elemente mit der Klasse "form-field" selektieren
    elements.forEach(element => {
        let fieldId = element.id.replace('button_', '');
        const file_str = document.getElementById('FF_' + fieldId).value;
        fetch_uploads(path, file_str, fieldId);
    });
}

function follow_select(elem) {
    const fnr = elem.id.split('_')[1]
    const block_begin = 'B_' + fnr;

    const alleDivs = document.querySelectorAll('div[id^=' + block_begin + ']');
    const auswahl = elem.value.trim();
    alleDivs.forEach(div => {
        const id = div.id;
        // console.log(div.className + " " + id + " " + auswahl + " " + id.includes(auswahl));
        if (auswahl && id.includes(auswahl)) {
            // console.log("show " + id);
            if (div.className == 'block') div.style.display = 'block';
            else div.style.display = 'flex';
            // div.scrollIntoView({
            //     behavior: "smooth",
            //     block: "end"
            // });
        } else {
            div.style.display = "none";
        }
    });
}

function check_date(inputId, from_year, to_year, not_in_future, not_in_past, mode, locale) {
    const input = document.getElementById(inputId);
    if (!input) return;
    updateStatusLabel('status', '', 'warning');
    input.style.backgroundColor = '';

    const original = input.value.trim();
    const today = new Date();
    let year, month = 1,
        day = 1;


    const error = (msg) => {
        // alert(msg);
        updateStatusLabel('status', msg, 'warning');
        input.style.backgroundColor = '#fdd6d6ff';
        input.value = "";
    };

    const isNumeric = (str) => /^\d+$/.test(str);

    // Teilerkennung basierend auf locale
    let parts;
    if (locale === "de") {
        parts = original.split(".");
    } else {
        parts = original.split("-");
    }

    // Lokale Umwandlung: DE → JJJJ-MM-TT
    let normParts = [...parts];

    if (locale === "de") {
        if (parts.length === 3) {
            // TT.MM.JJJJ → JJJJ-MM-TT
            normParts = [parts[2], parts[1], parts[0]];
        } else if (parts.length === 2) {
            // MM.JJJJ → JJJJ-MM
            normParts = [parts[1], parts[0]];
        }
    }

    // Datumskomponenten extrahieren
    if (normParts.length === 1 && isNumeric(normParts[0])) {
        year = parseInt(normParts[0]);
    } else if (normParts.length === 2 && isNumeric(normParts[0]) && isNumeric(normParts[1])) {
        year = parseInt(normParts[0]);
        month = parseInt(normParts[1]);
    } else if (normParts.length === 3 && isNumeric(normParts[0]) && isNumeric(normParts[1]) && isNumeric(normParts[2])) {
        year = parseInt(normParts[0]);
        month = parseInt(normParts[1]);
        day = parseInt(normParts[2]);
    } else {
        return error("Ungültiges Datum oder Datumsformat!");
    }

    // Prüfe Mindestfelder laut Modus
    const fieldCount = normParts.length;
    switch (mode) {
        case "Y":
            if (fieldCount !== 1) return error("Nur Jahr erlaubt.");
            break;
        case "YM":
            if (fieldCount < 2) return error("Bitte Jahr und Monat eingeben.");
            break;
        case "YMD":
            if (fieldCount < 3) return error("Bitte vollständiges Datum eingeben.");
            break;
        case "Y(M)":
            if (fieldCount < 1 || fieldCount > 2) return error("Bitte Jahr oder Jahr+Monat eingeben.");
            break;
        case "Y(MD)":
            if (fieldCount < 1 || fieldCount > 3) return error("Bitte Jahr oder Jahr+Monat oder Jahr+Monat+Tag eingeben.");
            break;
        default:
            return error("Ungültiger Modus.");
    }

    // Prüfe gültiges Datum
    if (month < 1 || month > 12) return error("Ungültiger Monat.");
    if (fieldCount === 3) {
        const testDate = new Date(year, month - 1, day);
        if (
            testDate.getFullYear() !== year ||
            testDate.getMonth() + 1 !== month ||
            testDate.getDate() !== day
        ) {
            return error("Ungültiges Datum.");
        }
    }

    // Vergleich mit aktuellem Datum (angepasst je nach Detailtiefe)
    const compareDate = new Date(year, month - 1, day);
    const inputFields = normParts.length;

    if (inputFields === 1) {
        if (isNaN(input.value)) return error("Keine numerischer Wert!");
        if (input.value < from_year || input.value > to_year) error("Nicht im Datumsbereich!");
    }

    const isFuture = (() => {
        if (inputFields === 1) {
            return year > today.getFullYear();
        } else if (inputFields === 2) {
            return year > today.getFullYear() ||
                (year === today.getFullYear() && month > (today.getMonth() + 1));
        } else {
            return compareDate > today;
        }
    })();

    const isPast = (() => {
        ;
        if (inputFields === 1) {
            return year < today.getFullYear();
        } else if (inputFields === 2) {
            return year < today.getFullYear() ||
                (year === today.getFullYear() && month < (today.getMonth() + 1));
        } else {
            return compareDate < new Date(today.getFullYear(), today.getMonth(), today.getDate());
        }
    })();

    if (not_in_future && isFuture) {
        return error("Datum darf nicht in der Zukunft liegen.");
    }
    if (not_in_past && isPast) {
        return error("Datum darf nicht in der Vergangenheit liegen.");
    }
}

function multi_date(field_id, from_year, to_year, not_in_future, not_in_past, mode, locale) {
    const hidden_date_field = document.getElementById(field_id);
    const year_select = document.getElementById(field_id + '_year_select');
    const month_select = document.getElementById(field_id + '_month_select');
    const day_select = document.getElementById(field_id + '_day_select');

    const heute = new Date();
    const this_year = heute.getFullYear();

    if (from_year === '') from_year = 1900;
    else if (from_year === 'this_year') from_year = this_year;
    if (to_year === '') to_year = this_year;
    else if (to_year === 'this_year') to_year = this_year;
    else if (to_year === 'next_year') to_year = this_year + 1;

    // 1. Jahre einfüllen
    year_select.innerHTML = '<option value="">Jahr</option>';
    for (let jahr = to_year; jahr >= from_year; jahr--) {
        const option = document.createElement('option');
        option.value = jahr;
        option.textContent = jahr;
        year_select.appendChild(option);
    }

    const monate = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

    function fillMonths() {
        month_select.innerHTML = '<option value="">Monat wählen</option>';
        monate.forEach((m) => {
            const option = document.createElement('option');
            option.value = m;
            option.textContent = m;
            month_select.appendChild(option);
        });
    }

    function fillDays(year, month) {
        day_select.innerHTML = '<option value="">Tag wählen</option>';
        const daysInMonth = new Date(year, month, 0).getDate();
        for (let tag = 1; tag <= daysInMonth; tag++) {
            const option = document.createElement('option');
            option.value = tag;
            option.textContent = tag;
            day_select.appendChild(option);
        }
    }

    // 2. Initialisierung aus hidden field
    const hidden_val = hidden_date_field.value.trim();
    if (!hidden_val) {
        year_select.value = "";
        month_select.innerHTML = '<option value="">Monat wählen</option>';
        day_select.innerHTML = '<option value="">Tag wählen</option>';
        month_select.classList.add('hidden');
        day_select.classList.add('hidden');
    } else {
        const parts = hidden_val.split('.');
        if (parts.length === 1) {
            year_select.value = parts[0];
        } else if (parts.length === 2) {
            month_select.classList.remove('hidden');
            fillMonths();
            year_select.value = parts[1];
            month_select.value = parseInt(parts[0], 10);
        } else if (parts.length === 3) {
            month_select.classList.remove('hidden');
            day_select.classList.remove('hidden');
            fillMonths();
            year_select.value = parts[2];
            month_select.value = parseInt(parts[1], 10);
            fillDays(parseInt(parts[2], 10), parseInt(parts[1], 10));
            day_select.value = parseInt(parts[0], 10);
        }
    }

    // 3. Listener: Jahr -> Monat
    if (mode !== 'Y') {
        year_select.addEventListener('change', () => {
            month_select.innerHTML = '<option value="">Monat wählen</option>';
            day_select.innerHTML = '<option value="">Tag wählen</option>';
            day_select.classList.add('hidden');
            hidden_date_field.value = "";

            if (year_select.value) {
                fillMonths();
                month_select.classList.remove('hidden');
            } else {
                month_select.classList.add('hidden');
            }
        });

        // 4. Listener: Monat -> Tag
        if (mode !== 'Y(M)') {
            month_select.addEventListener('change', () => {
                day_select.innerHTML = '<option value="">Tag wählen</option>';
                hidden_date_field.value = "";

                if (month_select.value && year_select.value) {
                    fillDays(parseInt(year_select.value), parseInt(month_select.value));
                    day_select.classList.remove('hidden');
                } else {
                    day_select.classList.add('hidden');
                }
            });
        }
    }

    // 5. Schreiben ins hidden Feld
    function aktualisiereVerstecktesFeld() {
        const jahr = year_select.value;
        const monat = month_select.value;
        const tag = day_select.value;

        let datumString = "";
        if (jahr && monat && tag) {
            datumString = `${String(tag).padStart(2, '0')}.${String(monat).padStart(2, '0')}.${jahr}`;
        } else if (jahr && monat) {
            datumString = `${String(monat).padStart(2, '0')}.${jahr}`;
        } else if (jahr) {
            datumString = `${jahr}`;
        }

        // if (datumString) {
        hidden_date_field.value = datumString;
        hidden_date_field.dispatchEvent(new Event('change'));
        //}

        check_date(field_id, from_year, to_year, not_in_future, not_in_past, mode, locale);
    }

    year_select.addEventListener('change', aktualisiereVerstecktesFeld);
    month_select.addEventListener('change', aktualisiereVerstecktesFeld);
    day_select.addEventListener('change', aktualisiereVerstecktesFeld);

    hidden_date_field.addEventListener('change', () => {
        check_date(field_id, from_year, to_year, not_in_future, not_in_past, mode, locale);
    });
}

function initSliderWithHiddenField(sliderIdBase, max = 10) {
    const slider = document.getElementById(`${sliderIdBase}_wertSchieberegler`);
    const textField = document.getElementById(`FF_${sliderIdBase}`);
    const sliderDisplay = document.getElementById(`wertSchieberegler_display_${sliderIdBase}`);

    if (!slider || !textField) {
        console.error(`Slider oder Textfeld für ${sliderIdBase} nicht gefunden.`);
        return;
    }

    const updateTextFieldFromSlider = () => {
        const sliderValue = slider.value;
        textField.value = sliderValue;
        const event = new Event('change', { bubbles: true });
        textField.dispatchEvent(event);
        if (sliderDisplay) sliderDisplay.textContent = sliderValue;
        error_a[textField.id] = 0;
        errors = error_a_sum(error_a);
    };

    const updateSliderFromTextField = () => {
        let textValue = parseInt(textField.value, 0);
        if (isNaN(textValue) || textValue < 0 || textValue > max) {
            textValue = "";
        }
        slider.value = textValue;
        textField.value = textValue;
        if (sliderDisplay) sliderDisplay.textContent = textValue;
    };

    // Initialisieren
    slider.addEventListener('input', updateTextFieldFromSlider);
    textField.addEventListener('change', updateSliderFromTextField);

    if (textField.value !== '') {
        updateSliderFromTextField();
    } else {
        slider.value = "";
        sliderDisplay.textContent = "";
    }
}

function block_color(element) {
    if (element) {
        element.style.backgroundColor = '#f9fae1';
        const legendElement = element.querySelector('legend');
        if (legendElement) {
            legendElement.style.backgroundColor = '#f9fae1';
        } else {
            // console.warn("Fieldset mit ID " + element.id + " nicht gefunden.");
        }
        // element.style.display = 'none';
        // element.style.display = 'flex';
    }
}

function check_if_empty(inputElement) {
    const checkbox_marker = document.getElementById('cbm_' + inputElement.id.split('_')[1]);
    if (inputElement.value.trim() == '') {
        inputElement.style.backgroundColor = "#fdd6d6";
        if (checkbox_marker) checkbox_marker.style.backgroundColor = "#fdd6d6";
        error_a[inputElement.id] = 1;
    } else {
        inputElement.style.backgroundColor = "";
        if (checkbox_marker) checkbox_marker.style.backgroundColor = "";
        error_a[inputElement.id] = 0;
    }
    return error_a[inputElement.id];
}

function eL_check_required_fields() {
    const requiredInputs = document.querySelectorAll('input[required], select[required], textarea[required]');
    requiredInputs.forEach(input => {
        if (is_element_really_visible(input)) {
            input.addEventListener('input', () => {
                check_if_empty(input);
            });
            input.addEventListener('change', () => {
                check_if_empty(input);
            });
            check_if_empty(input); // init
        }
    });
}

function eL_check_numbers() {
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('keydown', function (e) { // Verhindere Eingabe von e, E, +, -
            if (["e", "E", "+", "-"].includes(e.key)) {
                e.preventDefault();
            }
        });

        input.addEventListener('change', function () {
            // Leere Eingaben sind erlaubt
            if (this.value.trim() === "") {
                this.setCustomValidity("");
                return;
            }

            const value = parseFloat(this.value);
            if (this.max === 'this_year') {
                currentYear = new Date().getFullYear();
                this.setAttribute('max', currentYear);
            }

            let min = this.min !== "" ? parseFloat(this.min) : null;
            let max = this.max !== "" ? parseFloat(this.max) : null;

            if (isNaN(value)) {
                this.value = "";
                this.setCustomValidity("Bitte eine gültige Zahl!");
            } else if ((min !== null && value < min) || (max !== null && value > max)) {
                this.value = "";
                this.setCustomValidity(`Zahl muss zwischen ${this.min} und ${this.max} liegen.`);
            } else {
                this.setCustomValidity("");
            }
            this.reportValidity();
        });
    });
}

function c_info() {
    const filteredObject = {};
    for (const key in error_a) {
        if (error_a.hasOwnProperty(key)) {
            if (error_a[key] !== 0) {
                filteredObject[key] = error_a[key]; // wenn der Wert 1 ist
            }
        }
    }
    return filteredObject;
}

function error_a_sum(obj) {
    return Object.entries(obj).reduce((sum, [key, value]) => key === "FF_0" ? sum : sum + value, 0);
}

function set_message(errors) {
    // c_info();
    let show_error = "";
    if (errors < 10) show_error = '(' + errors + ')';
    if (errors) updateStatusLabel('status', 'Bitte ergänzen Sie die markierten Felder! ' + show_error, 'warning');
    else updateStatusLabel('status', 'Bogen vollständig, vielen Dank!', 'success');
}

function updateStatusLabel(labelId, text, type) {
    ;
    const statusLabel = document.getElementById(labelId);
    if (!statusLabel) {
        console.warn(`Label mit der ID '${labelId}' wurde nicht gefunden.`);
        return;
    }
    statusLabel.classList.remove('success', 'warning', 'error', 'info');
    if (text === '') {
        statusLabel.textContent = ''; // Text entfernen
    } else {
        if (type) {
            statusLabel.classList.add(type);
        }
        statusLabel.textContent = text;
    }
}

function eL_load_select_logic() {
    document.querySelectorAll('select').forEach(select => {
        select.addEventListener('change', function () {
            if (this.value !== "") {
                this.style.color = "black";
            } else {
                this.style.color = ""; // Standardfarbe zurücksetzen
            }

        });
        follow_select(select);
    });
}

function is_element_really_visible(element) {
    if (!element || element.nodeType !== 1) {
        return false;
    }
    let currentElement = element;
    while (currentElement) {
        const computedStyle = window.getComputedStyle(currentElement);
        if (computedStyle.display === 'none') {
            return false;
        }
        currentElement = currentElement.parentElement;
    }
    return true;
}

function compare_dates(id1, id2, comp = '<=') {
    const el1 = document.getElementById(id1);
    const el2 = document.getElementById(id2);
    let lastChanged = null;

    function extractMonthYear(value) {
        const regex = /^(\d{1,2})\.(\d{4})$/;
        const match = value.match(regex);
        if (match) {
            return {
                monat: parseInt(match[1], 10),
                jahr: parseInt(match[2], 10)
            };
        }
        return null;
    }

    function resetField(el, id) {
        el.value = '';
        const y = document.getElementById(id + '_year_select');
        const m = document.getElementById(id + '_month_select');
        if (y) y.value = '';
        if (m) {
            m.value = '';
            // m.style.display = 'none';
        }
        const event = new Event('change', {
            bubbles: true
        });
        // console.log("DEL:" + el.id);
        el.dispatchEvent(event);
    }

    function validate() {
        const v1 = el1.value.trim();
        const v2 = el2.value.trim();
        if (!v1 || !v2) return;

        const d1 = extractMonthYear(v1);
        const d2 = extractMonthYear(v2);

        let val1, val2;

        // Fall 1: Beide im Format mm.jjjj → Monat berücksichtigen
        if (d1 && d2) {
            val1 = d1.jahr * 100 + d1.monat;
            val2 = d2.jahr * 100 + d2.monat;
        }
        // Fall 2: Nur einer mit Monat → Nur Jahresvergleich!
        else {
            const y1 = d1 ? d1.jahr : parseInt(v1);
            const y2 = d2 ? d2.jahr : parseInt(v2);
            if (isNaN(y1) || isNaN(y2)) return;
            val1 = y1;
            val2 = y2;
        }

        if (isNaN(val1) || isNaN(val2)) return;

        if (comp === '<=' && val1 > val2) {
            alert('Startdatum muss vor Enddatum liegen!');
            if (lastChanged === el1) {
                resetField(el1, id1);
            } else if (lastChanged === el2) {
                resetField(el2, id2);
            }
        }
    }

    el1.addEventListener('change', () => {
        lastChanged = el1;
        validate();
    });

    el2.addEventListener('change', () => {
        lastChanged = el2;
        validate();
    });
}

function hide_header() {
    const header = document.getElementById('header');
    const subHeader = document.getElementById('sub_header');
    header.style.display = 'none';
    subHeader.style.top = '0px';
    document.getElementById('main_tab').style.marginTop = '20px';
}

function showHeader() {
    const header = document.getElementById('header');
    const subHeader = document.getElementById('subHeader');
    header.style.display = 'block';
    subHeader.style.top = '50px';
    document.getElementById('content').style.marginTop = '100px';
}

function lock_form() {
    const layer = document.getElementById('lock-layer');
    if (layer) {
        layer.classList.remove('lock-layer-hidden');
        layer.classList.add('lock-layer-activ');
        // console.log("Formular gesperrt.");
    }
}

function entspfree_form() {
    const layer = document.getElementById('lock-layer');
    if (layer) {
        layer.classList.remove('lock-layer-activ');
        layer.classList.add('lock-layer-hidden');
        // console.log("Formular entsperrt.");
    }
}

function fillYearSelect(inputId, from_year, to_year, not_in_future = false, not_in_past = false, selected_value = null) {
    const select = document.getElementById(inputId);
    if (!select) {
        console.warn("Element mit ID '" + inputId + "' nicht gefunden.");
        return;
    }

    const thisYear = new Date().getFullYear();
    const replacements = {
        "this_year": thisYear,
        "last_year": thisYear - 1,
        "next_year": thisYear + 1
    };

    if (typeof from_year === "string" && replacements[from_year]) from_year = replacements[from_year];
    if (typeof to_year === "string" && replacements[to_year]) to_year = replacements[to_year];

    from_year = parseInt(from_year, 10);
    to_year = parseInt(to_year, 10);

    if (isNaN(from_year) || isNaN(to_year) || from_year > to_year) {
        console.error("Ungültige Jahresgrenzen.");
        return;
    }

    if (not_in_future && to_year > thisYear) to_year = thisYear;
    if (not_in_past && from_year < thisYear) from_year = thisYear;

    select.innerHTML = "";

    // Optional: Erste leere Option hinzufügen
    const emptyOption = document.createElement("option");
    emptyOption.value = "";
    emptyOption.textContent = "Bitte Jahr wählen";
    select.appendChild(emptyOption);

    //for (let year = from_year; year <= to_year; year++) {
    for (let year = to_year; year >= from_year; year--) {
        const opt = document.createElement("option");
        opt.value = year;
        opt.textContent = year;
        if (selected_value && parseInt(selected_value) === year) {
            opt.selected = true;
        }
        select.appendChild(opt);
    }
}

function getDynamicValues(data_tag) {
    const values = [];
    const attributeName = data_tag.slice(1, -1);
    const elements = document.querySelectorAll(data_tag);
    elements.forEach(element => {
        const value = element.getAttribute(attributeName);
        if (value !== null) {
            values.push(value);
        }
    });
    return values;
}

function setupCheckboxGroup(index) {
    const ff = document.getElementById(`FF_${index}`);
    const cbJa = document.getElementById(`CB_${index}_Ja`);
    const cbNein = document.getElementById(`CB_${index}_Nein`);

    function updateCheckbox(value) {
        let val_changed = 1;
        if (ff.value === value) val_changed = 0;

        ff.value = value;
        cbJa.style.backgroundColor = (value === 'Ja') ? 'dimgray' : 'white';
        cbNein.style.backgroundColor = (value === 'Nein') ? 'dimgray' : 'white';
        if (val_changed) {
            const event = new Event('change', {
                bubbles: true
            });
            ff.dispatchEvent(event);
        }
    }

    updateCheckbox(ff.value);
    cbJa.addEventListener('click', () => updateCheckbox('Ja'));
    cbNein.addEventListener('click', () => updateCheckbox('Nein'));
}

function setupCheckbox(index) {
    const ff = document.getElementById(`FF_${index}`);
    const cb = document.getElementById(`CB_${index}`);

    function updateVisual(value) {
        cb.style.backgroundColor = (value === '1') ? 'dimgray' : 'white';
    }

    function toggleCheckbox() {
        ff.value = (ff.value === '1') ? '' : '1';
        updateVisual(ff.value);

        const event = new Event('change', {
            bubbles: true
        });
        ff.dispatchEvent(event);
    }

    // Initialzustand setzen
    if (ff.value !== '1') ff.value = '';
    updateVisual(ff.value);

    cb.addEventListener('click', toggleCheckbox);
}

async function callLog(fcid, fg, fid, fcont) {
    try {
        // const fullPath = window.location.href;
        // console.log(fullPath); 
        const response = await fetch('/fetch_log.php?fcid=' + fcid + '&fg=' + fg + '&fid=' + fid + '&fcont=' + fcont);
        const data = await response.json();   // oder .text(), wenn kein JSON zurückkommt
        // console.log(data);
    } catch (error) {
        console.error('Fehler:', error);
    }
}


function background_field_action(verbose_a = [0, 0]) {

    function handleElementChange(event) {
        const element = event.target;
        const fid = element.id.split('_')[1];
        const ext_fid = element.id.split('_')[2];
        callLog(fcid, fg, fid, element.value);
        if (!ext_fid) { // ignore date_tc
            // console.log(`FCID: ${fcid} FG ${fg} FID: ${fid} EXTFID  ${ext_fid} VAL: ${element.value}`);
            fetchDataAndUpdateForm(fcid, fg, fid, element.value);
            try {
                filteredObject = c_info();
                fetchDataAndUpdateForm(fcid, fg, 100, JSON.stringify(filteredObject));
            } catch (err) {
                console.error(`❌ Fehler in Funktionsaufruf errorwriting`, err);
            }
            errors = error_a_sum(error_a);
            const objectLength = Object.keys(filteredObject).length;
            if (verbose_a[0]) 
                if (objectLength > 0) console.log(filteredObject);
            if (verbose_a[1]) 
                console.log('Errors:' + error_a_sum(error_a));
            set_message(errors);  
        }
    }

    const fcid = document.getElementById('fcid').value;
    const fg = document.getElementById('fg').value;
    const fgElements = document.querySelectorAll('[id^="FF_"]');
    fgElements.forEach(element => {
        element.addEventListener('change', handleElementChange);
    });
    // init
    errors = error_a_sum(error_a);
    if (errors) set_message(errors); // no complete message at start
}

function watchObject(obj, callback) {
    return new Proxy(obj, {
        set(target, prop, value) {
            const oldValue = target[prop];
            target[prop] = value;

            // Nur triggern, wenn sich der Wert wirklich geändert hat
            if (oldValue !== value) {
                callback(target, prop, value);
            }
            return true;
        }
    });
}

function playBeep() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = ctx.createOscillator();
        const gainNode = ctx.createGain();

        oscillator.type = "sine";   // Sinus-Ton
        oscillator.frequency.setValueAtTime(880, ctx.currentTime); // Tonhöhe in Hz (880 = A5)

        oscillator.connect(gainNode);
        gainNode.connect(ctx.destination);

        oscillator.start();

        // Nach 200 ms leiser machen und stoppen
        gainNode.gain.exponentialRampToValueAtTime(
            0.00001, ctx.currentTime + 0.2
        );
        oscillator.stop(ctx.currentTime + 0.2);
    } catch (e) {
        console.error("AudioContext konnte nicht gestartet werden:", e);
    }
}

const safeGet = (id, warn = true) => {
    const el = document.getElementById(id);
    if (!el && warn) console.warn(`⚠ Element mit ID "${id}" nicht gefunden.`);
    return el || null;
};

const safeAddListener = (el, type, handler) => {
    if (el) {
        el.addEventListener(type, handler);
    } else {
        console.warn(`⚠ Listener auf "${type}" nicht angehängt – Element fehlt.`);
    }
};

const safeCall = (fn, ...args) => {
    if (typeof fn === 'function') {
        try {
            return fn(...args);
        } catch (err) {
            console.error(`❌ Fehler in Funktionsaufruf ${fn.name || 'anonymous'}:`, err);
        }
    } else {
        // Name und Herkunft protokollieren
        const caller = (new Error()).stack
            ?.split('\n')[2] // zweite Zeile im Stack = Aufrufer
            ?.trim()
            ?.replace(/^at\s+/, '');
        console.warn(`⚠ Funktionsaufruf übersprungen – ${fn} ist keine Funktion. Aufgerufen von: ${caller}`);
    }
};

function get_ts() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0'); // Monate sind 0-basiert (0=Januar)
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const formattedDateTime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    return formattedDateTime;
}

function activate_multi_selects() {

    document.querySelectorAll('select[id^="mts_"]').forEach(select => {
        const idSuffix = select.id.replace('mts_', '');
        const hiddenInput = document.getElementById('FF_' + idSuffix);
        const list = document.getElementById('chosen_' + idSuffix);
        let selectedItems = [];

        if (!hiddenInput || !list) return; // Wenn eines fehlt, überspringen

        // Initial gespeicherte Werte laden
        const saved = hiddenInput.value.trim();
        if (saved) {

            selectedItems = saved.split(',').map(s => s.trim()).filter(Boolean);
            updateList();
        }

        // Auswahl-Handler
        select.addEventListener('change', () => {
            const selectedValue = select.value;
            if (!selectedValue) return;

            const index = selectedItems.indexOf(selectedValue);
            if (index === -1) {
                selectedItems.push(selectedValue);
            } else {
                selectedItems.splice(index, 1);
            }

            updateList();

            // Event triggern, falls etwas darauf lauscht
            const event = new Event('change', {
                bubbles: true
            });
            hiddenInput.dispatchEvent(event);

            // Auswahl zurücksetzen
            select.value = '';
        });

        // Hilfsfunktion zum Aktualisieren der Liste & Hidden-Feldes
        function updateList() {
            list.innerHTML = '';
            if (selectedItems.length > 1 && selectedItems.includes("Nein")) {
                selectedItems = selectedItems.filter(item => item.trim() !== "Nein");
            }
            selectedItems.forEach(item => {
                const li = document.createElement('li');
                li.textContent = item;
                // Optional: Klick zum Entfernen
                // li.addEventListener('click', () => {
                //     selectedItems = selectedItems.filter(i => i !== item);
                //     updateList();
                // });
                list.appendChild(li);
            });
            if (selectedItems.length > 2 && selectedItems.includes("Nein")) {
                selectedItems = selectedItems.filter(item => item.trim() !== "Nein");
            }
            hiddenInput.value = selectedItems.join(',');
            // console.log(hiddenInput.value);
        }
    });
}


var errors = 0;
var error_a = {};

document.addEventListener('DOMContentLoaded', (event) => {

    // const allRcbValues = getDynamicValues('[data-rcb]');
    // for (const value of allRcbValues) {
    //     setupCheckboxGroup(parseInt(value));
    // }

    // const allCbValues = getDynamicValues('[data-cb]');
    // for (const value of allCbValues) {
    //     setupCheckbox(parseInt(value));
    // }

    // Checkboxen
    getDynamicValues?.('[data-rcb]')?.forEach(val => safeCall(setupCheckboxGroup, parseInt(val)));
    getDynamicValues?.('[data-cb]')?.forEach(val => safeCall(setupCheckbox, parseInt(val)));



});