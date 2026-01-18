<script>
    async function fetchDataAndUpdateForm(fcid, fg, fid, fcont) {
        const url = '<?php echo MIQ_PATH_PHP ?>fetch_update_form.php';
        const params = new URLSearchParams({
            fcid,
            fg,
            fid,
            fcont
        });

        let user_erro_info_code = 0;
        try {
            const response = await fetch(`${url}?${params.toString()}`);
            if (!response.ok) {
                console.error('🚫 Kein Zugriff auf fetch-forms');
                user_erro_info_code = '001';
                // throw new Error(`HTTP-Fehler! Status: ${response.status}`);
            }

            const data = await response.json();

            if (data.status === 'ok') {
                // console.log('✅ Erfolg:', data.message);
                // console.log(data);
            }

            if (data.status !== 'ok') {
                user_erro_info_code = '002';
                console.error('🚫 Update fehlgeschlagen:', data.message);
                if (fid != 100) alert('Achtung Verbindungsfehler - schwache oder gestörte Netzwerkverbindung (' + user_erro_info_code + ')!');
                const temp_token = <?php echo json_encode(urlencode($temp_token ?? '')) ?>;
                if (temp_token) window.location.href = '../login.php?t=' + temp_token;
                else window.location.href = '../login.php';
                // throw new Error(data.message);
            }

            return data;
        } catch (error) {
            console.error('Fehler beim Form-Fetch-Aufruf:', error);
            // throw error;
        }
    }

    function field_in_group_validation(triggerFieldId, trigger_a, dependentGroupIds, mode, defaultValue = '') {

        const triggerField = document.getElementById('FF_' + triggerFieldId);

        if (triggerField) {

            triggerField.addEventListener('change', () => {
                apply_conditional_validation();
            });
            // initial check in load
            apply_conditional_validation();
        }

        function apply_conditional_validation() {
            let not_empty_condition = false;
            if (trigger_a[0] === '@NOTEMPTY@' && triggerField.value !== '') not_empty_condition = true;
            // console.log(trigger_a); console.log(not_empty_condition + " " + triggerField.value) ;
            if (trigger_a.indexOf(triggerField.value) !== -1 || not_empty_condition) {
                if (mode == 'one') filled_correct = check_group_for_atleast_one_value(dependentGroupIds, defaultValue);
                if (mode == 'all') filled_correct = check_group_for_all_values(dependentGroupIds, defaultValue)
                // console.log(triggerField.id + " " + trigger_a + " " +filled_correct);
                if (filled_correct) {
                    dependentGroupIds.forEach(id => {
                        const field = document.getElementById('FF_' + id);
                        const mts_field = document.getElementById('mts_' + id);
                        const checkbox_marker = document.getElementById('cbm_' + field.id.split('_')[1]);

                        if (field) {
                            field.style.backgroundColor = '';
                            triggerField.style.backgroundColor = '';
                            if (mts_field) mts_field.style.backgroundColor = '';
                            if (checkbox_marker) checkbox_marker.style.backgroundColor = "";
                            error_a['FF_' + id] = 0;
                        }
                    });
                } else {
                    if (mode == 'one') {
                        dependentGroupIds.forEach(id => {
                            const field = document.getElementById('FF_' + id);
                            const mts_field = document.getElementById('mts_' + id);
                            const checkbox_marker = document.getElementById('cbm_' + field.id.split('_')[1]);
                            if (field.type !== 'radio') {
                                field.style.backgroundColor = '#fdd6d6';
                                triggerField.style.backgroundColor = '#fdd6d6';
                                if (mts_field) mts_field.style.backgroundColor = '#fdd6d6';
                                if (checkbox_marker) checkbox_marker.style.backgroundColor = "#fdd6d6";
                                error_a['FF_' + id] = 1;
                            } else {
                                const currentLabel = field.closest('div');
                                currentLabel.style.backgroundColor = '#fdd6d6';
                                triggerField.style.backgroundColor = '#fdd6d6';
                                if (mts_field) mts_field.style.backgroundColor = '#fdd6d6';
                                error_a['FF_' + id] = 1;
                            }
                        });
                    }
                    if (mode == 'all') {
                        dependentGroupIds.forEach(id => {
                            const field = document.getElementById('FF_' + id);
                            const checkbox_marker = document.getElementById('cbm_' + field.id.split('_')[1]);
                            if (field.type !== 'radio') {
                                field.style.backgroundColor = '#fdd6d6';
                                triggerField.style.backgroundColor = '#fdd6d6';
                                if (checkbox_marker) checkbox_marker.style.backgroundColor = "#fdd6d6";
                                error_a['FF_' + id] = 1;
                            }
                            if (field.value !== '') {
                                check_if_empty(field);
                            }
                        });
                    }
                }
            } else {
                // trigger not set
                dependentGroupIds.forEach(id => {
                    const field = document.getElementById('FF_' + id);
                    if (field) {
                        field.style.backgroundColor = '';
                        error_a['FF_' + id] = 0;
                        //triggerField.style.backgroundColor = '';
                    }
                });
            }
        }

        function check_group_for_all_values(ids, defaultValue = '') {
            for (const id of ids) {
                const field = document.getElementById('FF_' + id);
                //if (is_element_really_visible(field)) {
                if (!field || field.value.trim() === '') {
                    return false;
                }
                // }
            }
            return true;
        }

        function check_group_for_atleast_one_value(ids, defaultValue = '') {
            for (const id of ids) {
                let field = document.getElementById('FF_' + id);
                const mts_field = document.getElementById('mts_' + id);
                if (field.type === 'hidden') {
                    // indentify checkboxes
                    let inputs = document.querySelectorAll('input[name="FF_' + id + '"]');
                    if (inputs.length === 2) {
                        inputs.forEach(input => {
                            if (input.type === 'checkbox') field = input;
                        });
                    }
                }
                if (!field) continue;
                // if (is_element_really_visible(field) || mts_field) {

                if (!defaultValue) {
                    if (field.value && field.value.trim() !== '') return true;
                } else {
                    if (has_at_least_one_default_val(ids) && field.value && field.value.trim() !== '') return true;
                }

                // }
            }
            return false; // Keines der Felder hat einen Wert
        }

        function has_at_least_one_default_val(groupIds) {
            return groupIds.some(id => {
                const fieldId = 'FF_' + id;
                const field = document.getElementById(fieldId);

                if (field) {
                    // Spezialbehandlung für Radio-Buttons: Finde den selektierten Wert der Gruppe
                    if (field.type === 'radio') {
                        const selectedRadioInGroup = document.querySelector(`input[name="${field.name}"]:checked`);
                        return selectedRadioInGroup && selectedRadioInGroup.value === 'Ja';
                    }
                    // Für alle anderen Feldtypen (Text, Select etc.) direkt den Wert prüfen
                    return field.value === 'Ja';
                }
                return false; // Feld nicht gefunden oder nicht relevant
            });
        }

        const validationListener = apply_conditional_validation;
        dependentGroupIds.forEach(id => {
            let field = document.getElementById('FF_' + id);
            const mts_field = document.getElementById('mts_' + id);
            if (mts_field) {
                field = mts_field;
            }
            if (field) {
                field.addEventListener('input', validationListener);
                field.addEventListener('change', validationListener);
            }
        });

        // event Listener for each field in group
        // dependentGroupIds.forEach(id => {
        //     let field = document.getElementById('FF_' + id);
        //     const mts_field = document.getElementById('mts_' + id);
        //     if (mts_field) {
        //         // console.log('Listener from ' + field.id + " to "+ mts_field.id);
        //         field = mts_field;
        //     }
        //     if (field) {
        //         field.addEventListener('input', () => { // 'input' für sofortige Reaktion
        //             apply_conditional_validation();
        //         });
        //         field.addEventListener('change', () => { // 'change' als Fallback
        //             apply_conditional_validation();
        //         });
        //     }
        // });
    }




    var ff_error;
    if (document.getElementById('FF_100'))
        ff_error = document.getElementById('FF_100');
    else
        ff_error = document.getElementById('errors');


    const block_divs = document.querySelectorAll('div[id^="B_"]');
    block_divs.forEach(div => {
        b_id = div.id.split('_')[1];
        if (b_id) trigger = document.getElementById('FF_' + b_id);
        if (trigger) follow_select(trigger);
    });
</script>