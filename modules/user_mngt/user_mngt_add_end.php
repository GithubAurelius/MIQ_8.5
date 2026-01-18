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

    setTimeout(function() { // Verzögerung für Seitenaufbau nach Insert
        window.scrollTo(0, 0);
    }, 200);

    const insert_key_name = 'master_uid';
    const insert_table_name = 'user_miq';

    const add_data = document.getElementById('add_data');
    add_data.style.display = 'inline';
    if (add_data) {
        add_data.addEventListener('click', function() {
            fetch('table_insert.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        key_name: insert_key_name,
                        main_table: insert_table_name,
                    })
                }).then(res => res.json())
                .then(data => {
                    if (data.success) {
                        setTimeout(function() {
                            window.location.reload();
                        }, 100);
                    } else {
                        console.log("error");
                    }
                }).catch(err => {
                    console.log("error");
                });
        });
    }




    show_action_buttons();

    eL_add_button(w, '✉', 'M', 'lightblue', (fcid) => {
        window.open(<?= json_encode(MIQ_PATH) ?> + 'modules/login_token/login_access.php?key=' + fcid + '&pass=');
    });
</script>