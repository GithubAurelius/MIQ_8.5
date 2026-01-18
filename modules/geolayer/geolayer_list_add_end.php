<script>
    function show_action_buttons() {
        const table = document.getElementById('cases');
        if (table) {
            const all_table_head_cells = table.querySelectorAll('th');
            all_table_head_cells.forEach(cell => {
                const cellId = cell.id || '';
                if (cellId.startsWith('key_')) {
                    cell.style.display = 'block';
                }
            });
            const all_table_td_cells = table.querySelectorAll('td');
            all_table_td_cells.forEach(cell => {
                const cellId = cell.id || '';
                if (cellId.startsWith('key_')) {
                    cell.style.display = 'block';
                }
            });
        } else {
            console.warn("Tabelle mit der ID 'cases' wurde nicht gefunden.");
        }
    }

    function eL_add_button(w, button_text, button_id_prefix, col, clickHandler) {
        const dataRows = w.querySelectorAll('tr[data-key]');
        dataRows.forEach(row => {
            const fcid = row.getAttribute('data-key');
            const actionCell = row.querySelector('.action');
            const newButton = document.createElement('button');
            newButton.id = `${button_id_prefix}_${fcid}`;
            newButton.textContent = button_text;
            newButton.classList.add(`small_button`);
            newButton.style.backgroundColor = col;
            newButton.addEventListener('click', () => {
                clickHandler(fcid, button_text, button_id_prefix, col);
            });
            if (actionCell) {
                actionCell.appendChild(newButton);
            }
        });
    }

    show_action_buttons();

    eL_add_button(w, '🔍', 'E', '#f3f5c2ff', (fcid) => {
        alert('Folgt ...');
    });


    // Position für Sitzung speichern 
    num = <?= json_decode($num) ?>;
    const outer_wb = window.top.findClosestWinboxFromIframe(window.frameElement);
    window.onunload = function() {
        window.top.set_last_winbox_state(num, outer_wb);
    };
</script>