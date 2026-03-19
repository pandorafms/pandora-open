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

// Load global vars.
global $config;

check_login();

require_once $config['homedir'].'/include/functions_profile.php';
require_once $config['homedir'].'/include/functions_users.php';
require_once $config['homedir'].'/include/functions_groups.php';

if (! check_acl($config['id_user'], 0, 'PM')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access User Management'
    );
    include 'general/noaccess.php';
    exit;
}
$tab = get_parameter('tab', 'profile');
$pure = get_parameter('pure', 0);

// Header.

    user_print_header($pure, $tab);
    $sec = 'gusuarios';


$delete_profile = (bool) get_parameter('delete_profile');
$create_profile = (bool) get_parameter('create_profile');
$update_profile = (bool) get_parameter('update_profile');
$id_profile = (int) get_parameter('id');

// Profile deletion.
if ($delete_profile === true) {
    // Delete profile.
    $profile = db_get_row('tperfil', 'id_perfil', $id_profile);
    $ret = profile_delete_profile_and_clean_users($id_profile);
    if ($ret === false) {
        ui_print_error_message(__('There was a problem deleting the profile'));
    } else {
        db_pandora_audit(
            AUDIT_LOG_USER_MANAGEMENT,
            'Delete profile '.io_safe_output($profile['name'])
        );
        ui_print_success_message(__('Successfully deleted'));
    }

    $id_profile = 0;
}

// Store the variables when create or update.
if (($create_profile === true || $update_profile === true)) {
    $name = get_parameter('name');

    // Agents.
    $agent_view = (bool) get_parameter('agent_view');
    $agent_edit = (bool) get_parameter('agent_edit');
    $agent_disable = (bool) get_parameter('agent_disable');

    // Alerts.
    $alert_edit = (bool) get_parameter('alert_edit');
    $alert_management = (bool) get_parameter('alert_management');

    // Users.
    $user_management = (bool) get_parameter('user_management');

    // DB.
    $db_management = (bool) get_parameter('db_management');

    // Pandora.
    $pandora_management = (bool) get_parameter('pandora_management');

    // Events.
    $event_view = (bool) get_parameter('event_view');
    $event_edit = (bool) get_parameter('event_edit');
    $event_management = (bool) get_parameter('event_management');

    // Reports.
    $report_view = (bool) get_parameter('report_view');
    $report_edit = (bool) get_parameter('report_edit');
    $report_management = (bool) get_parameter('report_management');

    // Network maps.
    $map_view = (bool) get_parameter('map_view');
    $map_edit = (bool) get_parameter('map_edit');
    $map_management = (bool) get_parameter('map_management');

    // Visual console.
    $vconsole_view = (bool) get_parameter('vconsole_view');
    $vconsole_edit = (bool) get_parameter('vconsole_edit');
    $vconsole_management = (bool) get_parameter('vconsole_management');

    // NCM.
    $network_config_view = (bool) get_parameter('network_config_view');
    $network_config_edit = (bool) get_parameter('network_config_edit');
    $network_config_management = (bool) get_parameter('network_config_management');

    $values = [
        'name'                      => $name,
        'agent_view'                => $agent_view,
        'agent_edit'                => $agent_edit,
        'agent_disable'             => $agent_disable,
        'alert_edit'                => $alert_edit,
        'alert_management'          => $alert_management,
        'user_management'           => $user_management,
        'db_management'             => $db_management,
        'event_view'                => $event_view,
        'event_edit'                => $event_edit,
        'event_management'          => $event_management,
        'report_view'               => $report_view,
        'report_edit'               => $report_edit,
        'report_management'         => $report_management,
        'map_view'                  => $map_view,
        'map_edit'                  => $map_edit,
        'map_management'            => $map_management,
        'vconsole_view'             => $vconsole_view,
        'vconsole_edit'             => $vconsole_edit,
        'vconsole_management'       => $vconsole_management,
        'network_config_view'       => $network_config_view,
        'network_config_edit'       => $network_config_edit,
        'network_config_management' => $network_config_management,
        'pandora_management'        => $pandora_management,
    ];
}

// Update profile.
if ($update_profile === true) {
    if (empty($name) === false) {
        $ret = db_process_sql_update('tperfil', $values, ['id_perfil' => $id_profile]);
        if ($ret !== false) {
            $info = '{"Name":"'.$name.'",
				"Agent view":"'.$agent_view.'",
				"Agent edit":"'.$agent_edit.'",
				"Agent disable":"'.$agent_disable.'",
				"Alert edit":"'.$alert_edit.'",
				"Alert management":"'.$alert_management.'",
				"User management":"'.$user_management.'",
				"DB management":"'.$db_management.'",
				"Event view":"'.$event_view.'",
				"Event edit":"'.$event_edit.'",
				"Event management":"'.$event_management.'",
				"Report view":"'.$report_view.'",
				"Report edit":"'.$report_edit.'",
				"Report management":"'.$report_management.'",
				"Network map view":"'.$map_view.'",
				"Network map edit":"'.$map_edit.'",
				"Network map management":"'.$map_management.'",
				"Visual console view":"'.$vconsole_view.'",
				"Visual console edit":"'.$vconsole_edit.'",
				"Visual console management":"'.$vconsole_management.'",
                "NCM view":"'.$network_config_view.'",
				"NCM edit":"'.$network_config_edit.'",
				"NCM management":"'.$network_config_management.'",
				"'.get_product_name().' Management":"'.$pandora_management.'"}';

            db_pandora_audit(
                AUDIT_LOG_USER_MANAGEMENT,
                'Update profile '.io_safe_output($name),
                false,
                false,
                $info
            );

            ui_print_success_message(__('Successfully updated'));
        } else {
            ui_print_error_message(__('There was a problem updating this profile'));
        }
    } else {
        ui_print_error_message(__('Profile name cannot be empty'));
    }

    $id_profile = 0;
}

// Create profile.
if ($create_profile === true) {
    if (empty($name) === false) {
        $ret = db_process_sql_insert('tperfil', $values);

        if ($ret !== false) {
            ui_print_success_message(__('Successfully created'));
            $info = '{"Name":"'.$name.'",
				"Agent view":"'.$agent_view.'",
				"Agent edit":"'.$agent_edit.'",
				"Agent disable":"'.$agent_disable.'",
				"Alert edit":"'.$alert_edit.'",
				"Alert management":"'.$alert_management.'",
				"User management":"'.$user_management.'",
				"DB management":"'.$db_management.'",
				"Event view":"'.$event_view.'",
				"Event edit":"'.$event_edit.'",
				"Event management":"'.$event_management.'",
				"Report view":"'.$report_view.'",
				"Report edit":"'.$report_edit.'",
				"Report management":"'.$report_management.'",
				"Network map view":"'.$map_view.'",
				"Network map edit":"'.$map_edit.'",
				"Network map management":"'.$map_management.'",
				"Visual console view":"'.$vconsole_view.'",
				"Visual console edit":"'.$vconsole_edit.'",
				"Visual console management":"'.$vconsole_management.'",
                "NCM view":"'.$network_config_view.'",
				"NCM edit":"'.$network_config_edit.'",
				"NCM management":"'.$network_config_management.'",
				"'.get_product_name().' Management":"'.$pandora_management.'"}';

            db_pandora_audit(
                AUDIT_LOG_USER_MANAGEMENT,
                'Created profile '.io_safe_output($name),
                false,
                false,
                $info
            );
        } else {
            ui_print_error_message(__('There was a problem creating this profile'));
        }
    } else {
        ui_print_error_message(__('There was a problem creating this profile'));
    }

    $id_profile = 0;
}

$table = new stdClass();
$table->cellpadding = 0;
$table->cellspacing = 0;
$table->styleTable = 'margin: 10px';
$table->class = 'info_table profile_list';

$table->head = [];
$table->data = [];
$table->size = [];
$table->align = [];

$table->head['profiles'] = __('Profiles');

$table->head['AR'] = '<span title="'.__('View Agents').'">'.'AR'.'</span>';
$table->head['AW'] = '<span title="'.__('Edit Agents').'">'.'AW'.'</span>';
$table->head['AD'] = '<span title="'.__('Disable Agents').'">'.'AD'.'</span>';
$table->head['LW'] = '<span title="'.__('Edit Alerts').'">'.'LW'.'</span>';
$table->head['LM'] = '<span title="'.__('Manage Alerts').'">'.'LM'.'</span>';
$table->head['UM'] = '<span title="'.__('User Management').'">'.'UM'.'</span>';
$table->head['DM'] = '<span title="'.__('Database Management').'">'.'DM'.'</span>';
$table->head['ER'] = '<span title="'.__('View Events').'">'.'ER'.'</span>';
$table->head['EW'] = '<span title="'.__('Edit Events').'">'.'EW'.'</span>';
$table->head['EM'] = '<span title="'.__('Manage Events').'">'.'EM'.'</span>';
$table->head['RR'] = '<span title="'.__('View Reports').'">'.'RR'.'</span>';
$table->head['RW'] = '<span title="'.__('Edit Reports').'">'.'RW'.'</span>';
$table->head['RM'] = '<span title="'.__('Manage Reports').'">'.'RM'.'</span>';
$table->head['MR'] = '<span title="'.__('View Network Maps').'">'.'MR'.'</span>';
$table->head['MW'] = '<span title="'.__('Edit Network Maps').'">'.'MW'.'</span>';
$table->head['MM'] = '<span title="'.__('Manage Network Maps').'">'.'MM'.'</span>';
$table->head['VR'] = '<span title="'.__('View Visual Consoles').'">'.'VR'.'</span>';
$table->head['VW'] = '<span title="'.__('Edit Visual Consoles').'">'.'VW'.'</span>';
$table->head['VM'] = '<span title="'.__('Manage Visual Consoles').'">'.'VM'.'</span>';
$table->head['NR'] = '<span title="'.__('View NCM Data').'">'.'NR'.'</span>';
$table->head['NW'] = '<span title="'.__('Operate NCM').'">'.'NW'.'</span>';
$table->head['NM'] = '<span title="'.__('Manage NCM').'">'.'NM'.'</span>';
$table->head['PM'] = '<span title="'.__('Pandora Administration').'">'.'PM'.'</span>';

$table->head['operations'] = '<span title="Operations">'.__('Op.').'</span>';

$table->align = array_fill(1, 11, 'center');

$table->size['profiles'] = '150px';
$table->size['AR'] = '10px';
$table->size['AW'] = '10px';
$table->size['AD'] = '10px';
$table->size['LW'] = '10px';
$table->size['LM'] = '10px';
$table->size['UM'] = '10px';
$table->size['DM'] = '10px';
$table->size['ER'] = '10px';
$table->size['EW'] = '10px';
$table->size['EM'] = '10px';
$table->size['RR'] = '10px';
$table->size['RW'] = '10px';
$table->size['RM'] = '10px';
$table->size['MR'] = '10px';
$table->size['MW'] = '10px';
$table->size['MM'] = '10px';
$table->size['VR'] = '10px';
$table->size['VW'] = '10px';
$table->size['VM'] = '10px';
$table->size['NR'] = '10px';
$table->size['NW'] = '10px';
$table->size['NM'] = '10px';
$table->size['PM'] = '10px';
$table->size['operations'] = '6%';


$profiles = db_get_all_rows_in_table('tperfil');
if ($profiles === false) {
    $profiles = [];
}

$img = html_print_image(
    'images/validate.svg',
    true,
    [
        'border' => 0,
        'class'  => 'invert_filter main_menu_icon',
    ]
);

foreach ($profiles as $profile) {

    $data['profiles'] = '<a href="index.php?sec='.$sec.'&amp;sec2=godmode/users/configure_profile&id='.$profile['id_perfil'].'&pure='.$pure.'">';
    $data['profiles'] .= $profile['name'];
    $data['profiles'] .= '</a>';

    $data['AR'] = (empty($profile['agent_view']) === false) ? $img : '';
    $data['AW'] = (empty($profile['agent_edit']) === false) ? $img : '';
    $data['AD'] = (empty($profile['agent_disable']) === false) ? $img : '';
    $data['LW'] = (empty($profile['alert_edit']) === false) ? $img : '';
    $data['LM'] = (empty($profile['alert_management']) === false) ? $img : '';
    $data['UM'] = (empty($profile['user_management']) === false) ? $img : '';
    $data['DM'] = (empty($profile['db_management']) === false) ? $img : '';
    $data['ER'] = (empty($profile['event_view']) === false) ? $img : '';
    $data['EW'] = (empty($profile['event_edit']) === false) ? $img : '';
    $data['EM'] = (empty($profile['event_management']) === false) ? $img : '';
    $data['RR'] = (empty($profile['report_view']) === false) ? $img : '';
    $data['RW'] = (empty($profile['report_edit']) === false) ? $img : '';
    $data['RM'] = (empty($profile['report_management']) === false) ? $img : '';
    $data['MR'] = (empty($profile['map_view']) === false) ? $img : '';
    $data['MW'] = (empty($profile['map_edit']) === false) ? $img : '';
    $data['MM'] = (empty($profile['map_management']) === false) ? $img : '';
    $data['VR'] = (empty($profile['vconsole_view']) === false) ? $img : '';
    $data['VW'] = (empty($profile['vconsole_edit']) === false) ? $img : '';
    $data['VM'] = (empty($profile['vconsole_management']) === false) ? $img : '';
    $data['NR'] = (empty($profile['network_config_view']) === false) ? $img : '';
    $data['NW'] = (empty($profile['network_config_edit']) === false) ? $img : '';
    $data['NM'] = (empty($profile['network_config_management']) === false) ? $img : '';
    $data['PM'] = (empty($profile['pandora_management']) === false) ? $img : '';
    $table->cellclass[]['operations'] = 'table_action_buttons';

    $data['operations'] = '<a href="index.php?sec='.$sec.'&amp;sec2=godmode/users/configure_profile&id='.$profile['id_perfil'].'&pure='.$pure.'">'.html_print_image(
        'images/edit.svg',
        true,
        [
            'title' => __('Edit'),
            'class' => 'invert_filter main_menu_icon',
        ]
    ).'</a>';
    if ((bool) check_acl($config['id_user'], 0, 'PM') === true || (bool) users_is_admin() === true) {
        $data['operations'] .= html_print_anchor(
            [
                'href'    => 'index.php?sec='.$sec.'&sec2=godmode/users/profile_list&delete_profile=1&id='.$profile['id_perfil'].'&pure='.$pure,
                'onClick' => 'if (!confirm(\' '.__('Are you sure?').'\')) return false;',
                'content' => html_print_image(
                    'images/delete.svg',
                    true,
                    [
                        'title' => __('Delete'),
                        'class' => 'invert_filter main_menu_icon',
                    ]
                ),
            ],
            true
        );
    }

    array_push($table->data, $data);
}

if (isset($data) === true) {
    html_print_table($table);
} else {
    echo "<div class='nf'>".__('There are no defined profiles').'</div>';
}

echo '<form method="post" action="index.php?sec='.$sec.'&sec2=godmode/users/configure_profile&pure='.$pure.'">';
html_print_input_hidden('new_profile', 1);
html_print_action_buttons(
    html_print_submit_button(
        __('Create profile'),
        'crt',
        false,
        [ 'icon' => 'next' ],
        true
    ),
    [
        'type'  => 'data_table',
        'class' => 'fixed_action_buttons',
    ]
);
echo '</form>';

unset($table);
