<?php
/**
 * Agent Remote Configuration view.
 *
 * @category   Agent editor/ builder.
 * @package    Pandora FMS
 * @subpackage Enterprise.
 * @version    2.0.0
 * @license    See below
 *
 *    ______                 ___                    _______ _______ ________
 * |   __ \.-----.--.--.--|  |.-----.----.-----. |    ___|   |   |     __|
 * |    __/|  _  |     |  _  ||  _  |   _|  _  | |    ___|       |__     |
 * |___|   |___._|__|__|_____||_____|__| |___._| |___|   |__|_|__|_______|
 *
 * ============================================================================
 * Copyright (c) 2005-2023 Pandora FMS
 * Please see https://pandoraopen.io/ for full contribution list
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation for version 2.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * ============================================================================
 */

// Begin.
global $config;

global $agent_md5;
global $id_agente;
global $nombre_agente;

include_once('include/functions_agents.php');

if ((bool) check_acl($config['id_user'], 0, 'AW') === false) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access agent manager'
    );
    include 'general/noaccess.php';
    return;
}

require_once 'include/functions_network_components.php';

// Get encoding from config.
function get_config_encoding($agent_config)
{
    if (empty($agent_config)) {
        return false;
    }

    $nencodings = preg_match_all('/^\s*encoding\s+(.*?)\s*$/m', $agent_config, $encodings);

    if ($nencodings !== false && $nencodings > 0) {
        return $encodings[1][($nencodings - 1)];
    }

    return 'UTF-8';
    // agent's default
}


// Saves the configuration and the md5 hash
function save_config($agent_config, $id_agent=false)
{
    global $nombre_agente;

    if (empty($agent_config) === true) {
        return false;
    }

    global $agent_md5, $config;

    $alias = db_get_value('alias', 'tagente', 'nombre', $nombre_agente);
    db_pandora_audit(
        AUDIT_LOG_AGENT_REMOTE_MANAGEMENT,
        'Update remote config for agent '.io_safe_output($alias)
    );

    $encoding = get_config_encoding($agent_config);

    // Convert to config file encoding.
    if ($encoding !== false) {
        $converted_agent_config = mb_convert_encoding($agent_config, $encoding, 'UTF-8');
        if ($converted_agent_config !== false) {
            $agent_config = $converted_agent_config;
        }
    }

    if ($id_agent !== false) {
        $files = config_agents_get_agent_config_filenames($id_agent);
    } else {
        $files = [];
        $files['conf'] = $config['remote_config'].'/conf/'.$agent_md5.'.conf';
        $files['md5'] = $config['remote_config'].'/md5/'.$agent_md5.'.md5';
    }

    // Save configuration.
    $result = file_put_contents($files['conf'], $agent_config);

    if ($result === false) {
        return false;
    }

    // Save configuration md5.
    $result = file_put_contents($files['md5'], md5($agent_config));

    return (bool) $result;
}


// Update configuration.
$update_remote_conf = (bool) get_parameter('update_remote_conf');
if ($update_remote_conf === true) {
    $agent_config = isset($_POST['agent_config']) ? $_POST['agent_config'] : '';
    $agent_config = str_replace("\r", '', $agent_config);

    $result = save_config($agent_config, $id_agente);

    ui_print_result_message(
        $result,
        __('Successfully updated'),
        __('Could not be updated')
    );
}

// Read configuration file.
$files = config_agents_get_agent_config_filenames($id_agente);
$file_name = $files['conf'];

if (is_readable($file_name) === false) {
    ui_print_error_message(__('Error: The conf file of agent is not readble.'));
    $agent_config = '';
} else {
    if (is_writable($file_name) === false) {
        ui_print_error_message(__('Error: The conf file of agent is not writable.'));
    }

    $agent_config = file_get_contents($file_name);
    $encoding = get_config_encoding($agent_config);

    if ($encoding !== false) {
        $agent_config_utf8 = mb_convert_encoding($agent_config, 'UTF-8', $encoding);

        if ($agent_config_utf8 !== false) {
            $agent_config = $agent_config_utf8;
        }
    }
}

require_once $config['homedir'].'/include/functions_modules.php';

$id_os = db_get_value('id_os', 'tagente', 'id_agente', $id_agente);
$all_group_ids = db_get_all_rows_filter(
    'tlocal_component',
    false,
    'DISTINCT(id_network_component_group)'
);
if ($all_group_ids === false) {
    $all_group_ids = [];
}

$group_ids = [];
foreach ($all_group_ids as $group) {
    array_push($group_ids, $group['id_network_component_group']);
}

$network_component_group = isset($group_ids[0]) ? $group_ids[0] : 0;

// Add module table.
$tableAddModule = new StdClass;
$tableAddModule->id = 'add_module_table';
$tableAddModule->width = '100%';
$tableAddModule->class = 'filters dialog_table_form';
$tableAddModule->size = [];

$tableAddModule->size[0] = '25%';
$tableAddModule->size[1] = '75%';

$tableAddModule->data = [];

$tableAddModule->data[0][0] = __('Group');
$tableAddModule->data[0][1] = html_print_select(
    network_components_get_groups(0),
    'network_component_group',
    '',
    '',
    '',
    0,
    true,
    false,
    false,
    'w220px'
);
$tableAddModule->data[1][0] = __('Module');
$tableAddModule->data[1][1] = html_print_select(
    $components,
    'local_component',
    '',
    '',
    '',
    0,
    true
);
$tableAddModule->colspan[2][0] = '2';
$tableAddModule->data[2][0] = '<span id="no_component" class="hidden_block error font_11">'.__('No module was found').'</span>';
$tableAddModule->data[2][0] .= html_print_image(
    'images/spinner.gif',
    true,
    [
        'id'    => 'component_loading',
        'class' => 'invisible',
    ]
);

$submitButtons = html_print_submit_button(
    __('Update'),
    'upd',
    false,
    [ 'icon' => 'wand' ],
    true
);

$submitButtons .= html_print_button(
    __('Add module'),
    'add_module',
    false,
    'add_module_dialog()',
    [
        'icon' => 'add',
        'mode' => 'secondary',
    ],
    true
);

$submitButtons .= html_print_button(
    __('Delete remote configuration'),
    'delete_remote_config',
    false,
    'delete_remote_configuration('.$id_agente.')',
    [
        'icon' => 'delete',
        'mode' => 'secondary',
    ],
    true
);

echo '<form name="update" method="post">';
html_print_div(
    [
        'class'   => '',
        'style'   => 'margin: 20px 20px 0',
        'content' => html_print_textarea('agent_config', 30, 0, $agent_config, '', true, 'w100p bg-white'),
    ]
);
html_print_action_buttons(
    $submitButtons,
    ['type' => 'data_table']
);

$agent_name = agents_get_name($id_agente);
$agent_name = io_safe_output($agent_name);
$agent_md5 = md5($agent_name, false);
html_print_input_hidden('disk_conf', $agent_md5);
html_print_input_hidden('update_remote_conf', 1);

echo '</form>';

$modalAddModule = '<form name="add_local_component" method="post">';
$modalAddModule .= html_print_table($tableAddModule, true);
$modalAddModule .= html_print_div(
    [
        'class'   => 'action-buttons',
        'content' => html_print_submit_button(
            __('Add'),
            'add_module_dialog',
            false,
            [
                'icon' => 'add',
                'mode' => 'secondary mini',
            ],
            true
        ),
    ],
    true
);
$modalAddModule .= '</form>';

html_print_div(
    [
        'id'      => 'modal',
        'style'   => 'display: none',
        'content' => $modalAddModule,
    ]
);

?>
<script type="text/javascript">
/* <![CDATA[ */

function delete_remote_configuration($id_agente) {
    confirmDialog({
        title: "<?php echo __('Warning'); ?>",
        message: "<?php echo __('Delete this conf file implies that for restore you must reactive remote config in the local agent.<br><br>Are you sure?'); ?>",
        onAccept: function() {
            window.location.assign('index.php?sec=estado&sec2=godmode/agentes/configurar_agente&tab=main&id_agente='+$id_agente+'&delete_conf_file=1');
        }
    });
}

function add_module_dialog() {
    $('#modal')
    .dialog({
        title: '<?php echo __('Add Modules'); ?>',
        resizable: true,
        draggable: true,
        modal: true,
        close: false,
        height: 245,
        width: 480,
        overlay: {
            opacity: 0.5,
            background: "black"
        }
    })
    .show();
}

$(document).ready (function () {
    $("#network_component_group").pandoraSelectLocalComponentGroup ({
        id_os: <?php echo $id_os; ?>
    });

    $('#button-add_module_dialog').click(function(){
        $('#modal').dialog("close");
    });
});
/* ]]> */
</script>
