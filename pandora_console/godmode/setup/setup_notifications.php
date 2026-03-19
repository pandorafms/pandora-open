<?php
/**
 * Pandora FMS OpenSource
 * Copyright (c) 2004-2025 Pandora FMS Community
 * https://pandorafms.org
 *
 * Este programa es software libre; puedes redistribuirlo y/o modificarlo bajo
 * los términos de la Licencia Pública General de GNU publicada por la Free
 * Software Foundation para la versión 2. Este programa se distribuye con la
 * esperanza de que sea útil, pero SIN NINGUNA GARANTÍA; ni siquiera con la
 * garantía implícita de COMERCIABILIDAD o IDONEIDAD PARA UN PROPÓSITO
 * PARTICULAR. Consulta la Licencia Pública General de GNU para más detalles.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation for version 2. This program is distributed in the hope that it will
 * be useful, but WITHOUT ANY WARRANTY; without any implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
 * Public License for more details.
 *
 * Эта программа является свободным программным обеспечением; вы можете
 * распространять и/или изменять её в соответствии с условиями Стандартной
 * общественной лицензии GNU (GPL), опубликованной Фондом свободного
 * программного обеспечения (Free Software Foundation) для версии 2. Эта
 * программа распространяется в надежде, что она будет полезной, НО БЕЗ
 * КАКИХ-ЛИБО ГАРАНТИЙ, даже без подразумеваемой гарантии КОММЕРЧЕСКОЙ
 * ПРИГОДНОСТИ или ПРИГОДНОСТИ ДЛЯ КОНКРЕТНОЙ ЦЕЛИ. Подробнее см. Стандартную
 * общественную лицензию GNU.
 *
 * Ce programme est un logiciel libre ; vous pouvez le redistribuer et/ou le
 * modifier selon les termes de la Licence Publique Générale GNU, publiée par
 * la Free Software Foundation pour la version 2. Ce programme est distribué
 * dans l'espoir qu'il sera utile, mais SANS AUCUNE GARANTIE, même sans la
 * garantie implicite de QUALITÉ MARCHANDE ou D'ADÉQUATION À UN USAGE
 * PARTICULIER. Consultez la Licence Publique Générale GNU pour plus de détails.
 *
 * このプログラムはフリーソフトウェアです。GNU一般公衆利用許諾書
 * （Free Software Foundationによって公開されたバージョン2）の条件の下で、
 * 自由に再配布および改変することができます。本プログラムは有用であることを
 * 願って配布されますが、いかなる保証もありません。商品性や特定目的への適合性の
 * 保証も含まれません。詳しくはGNU一般公衆利用許諾書をご覧ください。
 * ============================================================================
 */

global $config;

require_once $config['homedir'].'/include/functions_notifications.php';

check_login();

// AJAX actions.
$source = get_parameter('source', '');
$users = get_parameter('users', '');
$elements = get_parameter('elements', []);
$is_users = $users === 'users';
if (get_parameter('get_selection_two_ways_form', 0)) {
    $info_selec = ($is_users === true) ? notifications_get_user_source_not_configured($source) : notifications_get_group_source_not_configured($source);

    echo notifications_print_two_ways_select(
        $info_selec,
        $users,
        $source
    );
    return;
}

if (get_parameter('add_source_to_database', 0)) {
    $res = ($is_users) ? notifications_add_users_to_source($source, $elements) : notifications_add_group_to_source($source, $elements);
    $result = ['result' => $res];
    echo json_encode($result);
    return;
}

if (get_parameter('remove_source_on_database', 0)) {
    $res = ($is_users) ? notifications_remove_users_from_source($source, $elements) : notifications_remove_group_from_source($source, $elements);
    $result = ['result' => $res];
    echo json_encode($result);
    return;
}

if (get_parameter('update_config', 0)) {
    $element = (string) get_parameter('element', '');
    $value = (int) get_parameter('value', 0);
    $source = (string) get_parameter('source');

    // Update the label value.
    ob_clean();
    $res = false;
    switch ($element) {
        // All users has other action.
        case 'all_users':
            $res = ($value) ? notifications_add_group_to_source($source, [0]) : notifications_remove_group_from_source($source, [0]);
        break;

        case 'subtype':
            $data = explode('.', $source, 2);
            $source_id = $data[0];
            $subtype = $data[1];
            $source = notifications_get_all_sources(
                [ 'id' => $source_id ]
            );

            if ($source !== false && is_array($source[0]) === true) {
                $source = $source[0];

                $blacklist = json_decode($source['subtype_blacklist'], 1);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $blacklist = [];
                }

                if ((bool) $value === true) {
                    unset($blacklist[$subtype]);
                } else {
                    $blacklist[$subtype] = 1;
                }

                $source['subtype_blacklist'] = json_encode($blacklist, 1);
                $res = (bool) db_process_sql_update(
                    'tnotification_source',
                    ['subtype_blacklist' => $source['subtype_blacklist']],
                    ['id' => $source['id']]
                );
            }
        break;

        default:
            $res = (bool) db_process_sql_update(
                'tnotification_source',
                [$element => $value],
                ['id' => $source]
            );
        break;
    }

    echo json_encode(['result' => $res]);
    return;
}

if (get_parameter('check_new_notifications', 0)) {
    $last_id_ui = (int) get_parameter('last_id', 0);
    $counters = notifications_get_counters();
    if ((int) $last_id_ui === (int) $counters['last_id']) {
        echo json_encode(['has_new_notifications' => false]);
        return;
    }

    if (messages_get_count() == 0) {
        return;
    }

    $messages = messages_get_overview(
        'timestamp',
        'ASC',
        false,
        true,
        0,
        ['id_mensaje' => '>'.$last_id_ui]
    );
    if ($messages === false) {
        $messages = [];
    }

    // If there is new messages, get the info.
    echo json_encode(
        [
            'has_new_notifications' => true,
            'new_ball'              => base64_encode(
                notifications_print_ball(
                    $counters['notifications'],
                    $counters['last_id']
                )
            ),
            'new_notifications'     => array_map(
                function ($elem) {
                    $elem['full_url'] = messages_get_url($elem['id_mensaje']);
                    return $elem;
                },
                $messages
            ),
        ]
    );
    return;
}

if (get_parameter('mark_notification_as_read', 0)) {
    $message = (int) get_parameter('message', 0);
    messages_process_read($message);
    // TODO check read.
    $url = messages_get_url($message);
    // Return false if cannot get the URL.
    if ($url === false) {
        echo json_encode(['result' => false]);
        return;
    }

    // If there is new messages, get the info.
    echo json_encode(
        [
            'result' => true,
            'url'    => $url,
        ]
    );
    return;
}

if (get_parameter('mark_all_notification_as_read', 0)) {
    $unread_messages = db_get_all_rows_sql('SELECT id_mensaje FROM tnotification_user WHERE utimestamp_read is NULL');

    if ($unread_messages !== false) {
        foreach ($unread_messages as $messages) {
            messages_process_read($messages['id_mensaje']);
        }

        $result = true;
    } else {
        $result = false;
    }

    // If there is new messages, get the info.
    echo json_encode(
        ['result' => $result]
    );

    return;
}

if (get_parameter('get_notifications_dropdown', 0)) {
    echo notifications_print_dropdown();
    return;
}

if (get_parameter('get_notification', 0)) {
    $msg_id = get_parameter('id', 0);

    if ($msg_id > 0) {
        $msg = messages_get_message($msg_id);

        $msg['mensaje'] = io_safe_output($msg['mensaje']);
        $msg['subject'] = io_safe_output($msg['subject']);
        echo json_encode($msg);
    }

    return;
}

// Notification table. It is just a wrapper.
$table_content = new StdClass();
$table_content->data = [];
$table_content->width = '100%';
$table_content->id = 'notifications-wrapper';
$table_content->class = 'databox filters';
$table_content->size['name'] = '30%';

// Print each source configuration.
$table_content->data = array_map(
    function ($source) {
        return notifications_print_global_source_configuration($source);
    },
    notifications_get_all_sources()
);

html_print_table($table_content);

?>
<script>
// Get index of two ways element dialog.
function notifications_two_ways_element_get_dialog (id, source_id) {
    return 'global_config_notifications_dialog_add-' + id + '-' + source_id;
}

// Get index of two ways element form.
function notifications_two_ways_element_get_sufix (id, source_id) {
    return 'multi-' + id + '-' + source_id;
}

// Open a dialog with selector of source elements.
function add_source_dialog(users, source_id) {
    // Display the dialog
    var dialog_id = notifications_two_ways_element_get_dialog(users, source_id);
    // Clean id element.
    var previous_dialog = document.getElementById(dialog_id);
    if (previous_dialog !== null) previous_dialog.remove();
    // Create or recreate the content.
    var not_dialog = document.createElement('div');
    not_dialog.setAttribute(
        'class',
        'global_config_notifications_dialog_add_wrapper'
    );
    not_dialog.setAttribute('id', dialog_id);
    document.body.appendChild(not_dialog);
    $("#" + dialog_id).dialog({
        resizable: false,
        draggable: true,
        modal: true,
        dialogClass: "global_config_notifications_dialog_add_full",
        overlay: {
            opacity: 0.5,
            background: "black"
        },
        closeOnEscape: true,
        modal: true
    });

    jQuery.post ("ajax.php",
        {"page" : "godmode/setup/setup_notifications",
            "get_selection_two_ways_form" : 1,
            "users" : users,
            "source" : source_id
        },
        function (data, status) {
            not_dialog.innerHTML = data
        },
        "html"
    );
}

// Move from selected and not selected source elements.
function notifications_modify_two_ways_element (id, source_id, operation) {
    var index_sufix = notifications_two_ways_element_get_sufix (id, source_id);
    var start_id = operation === 'add' ? 'all-' : 'selected-';
    var end_id = operation !== 'add' ? 'all-' : 'selected-';
    var select = document.getElementById(
        start_id + index_sufix
    );
    var select_end = document.getElementById(
        end_id + index_sufix
    );
    for (var i = select.options.length - 1; i >= 0; i--) {
        if(select.options[i].selected){
            select_end.appendChild(select.options[i]);
        }
    }
}

// Add elements to database and close dialog
function notifications_add_source_element_to_database(id, source_id) {
    var index = 'selected-' +
        notifications_two_ways_element_get_sufix (id, source_id);
    var select = document.getElementById(index);
    var selected = [];
    for (var i = select.options.length - 1; i >= 0; i--) {
        selected.push(select.options[i].value);
    }
    jQuery.post ("ajax.php",
        {"page" : "godmode/setup/setup_notifications",
            "add_source_to_database" : 1,
            "users" : id,
            "source" : source_id,
            "elements": selected
        },
        function (data, status) {
            if (data.result) {
                // Append to other element
                var out_select = document.getElementById(
                    notifications_two_ways_element_get_sufix(id, source_id)
                );
                for (var i = select.options.length - 1; i >= 0; i--) {
                    out_select.appendChild(select.options[i]);
                }
                // Close the dialog
                $("#" + notifications_two_ways_element_get_dialog(
                    id,
                    source_id
                ))
                .dialog("close");
            } else {
                console.log("Cannot update element.");
            }
        },
        "json"
    );
}

// Add elements to database and remove it form main select
function remove_source_elements(id, source_id) {
    var index = notifications_two_ways_element_get_sufix(id, source_id);
    var select = document.getElementById(index);
    var selected = [];
    var selected_index = [];
    for (var i = select.options.length - 1; i >= 0; i--) {
        if(select.options[i].selected){
            selected.push(select.options[i].value);
            selected_index.push(i);
        }
    }
    jQuery.post ("ajax.php",
        {"page" : "godmode/setup/setup_notifications",
            "remove_source_on_database" : 1,
            "users" : id,
            "source" : source_id,
            "elements": selected
        },
        function (data, status) {
            if (data.result) {
                // Append to other element
                for (var i = 0; i < selected_index.length; i++) {
                    select.remove(selected_index[i]);
                }
            } else {
                console.log("Cannot delete elements.");
            }
        },
        "json"
    );
}

function notifications_handle_change_element(event) {
    event.preventDefault();
    var match = /nt-(.+)-(.*)/.exec(event.target.id);
    if (!match) {
        console.error(
            "Cannot handle change element. Id not valid: ", event.target.id
        );
        return;
    }
    var action = {source: match[1], bit: match[2]};
    var element = document.getElementById(event.target.id);
    if (element === null) {
        console.error(
            "Cannot get element. Id: ", event.target.id
        );
        return;
    }

    var value;
    switch (action.bit) {
        case 'enabled':
        case 'subtype':
        case 'also_mail':
        case 'user_editable':
        case 'all_users':
            value = element.checked ? 1 : 0;
            break;
        case 'max_postpone_time':
            value = element.value;
            break;
        default:
            console.error("Unregonized action", action.bit, '.');
            return;

    }
    jQuery.post ("ajax.php",
        {
            "page" : "godmode/setup/setup_notifications",
            "update_config" : 1,
            "source" : match[1],
            "element" : match[2],
            "value": value
        },
        function (data, status) {
            if (!data.result) {
                console.error("Error changing configuration in database.");
            } else {
                switch (action.bit) {
                    case 'enabled':
                    case 'subtype':
                    case 'also_mail':
                    case 'user_editable':
                    case 'all_users':
                        element.checked = !element.checked;
                        break;
                    case 'max_postpone_time':
                        value = element.value;
                        break;
                    default:
                        console.error(
                            "Unregonized action (insert on db)", action.bit, '.'
                        );
                        return;
                }
            }
        },
        "json"
    )
    .done(function(m){})
    .fail(function(xhr, textStatus, errorThrown){
        console.error(
            "Cannot change configuration in database. Server error.",
            xhr.responseText
        );
    });
}
(function(){
    // Add listener to all componentes marked
    var all_clickables = document.getElementsByClassName('elem-clickable');
    for (var i = 0; i < all_clickables.length; i++) {
        all_clickables[i].addEventListener(
            'click', notifications_handle_change_element, false
        );
    }
    var all_changes = document.getElementsByClassName('elem-changeable');
    for (var i = 0; i < all_changes.length; i++) {
        all_changes[i].addEventListener(
            'change', notifications_handle_change_element, false
        );
    }
})();
</script>
