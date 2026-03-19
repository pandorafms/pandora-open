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

require_once $config['homedir'].'/include/functions_servers.php';
require_once $config['homedir'].'/include/functions_graph.php';

check_login();

if (! check_acl($config['id_user'], 0, 'PM') && ((bool) check_acl($config['id_user'], 0, 'AW') === true && $_GET['server_remote'] === null)) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Server Management'
    );
    include 'general/noaccess.php';
    exit;
}

if (isset($_GET['server']) === true) {
    $id_server = get_parameter_get('server');
    $title = __('Update').' ';
    $sql = sprintf('SELECT name, ip_address, description, server_type, exec_proxy, port FROM tserver WHERE id_server = %d', $id_server);
    $row = db_get_row_sql($sql);

    switch ($row['server_type']) {
        case SERVER_TYPE_DATA:
            $title .= __('Data server').' ID: '.$id_server;
        break;

        case SERVER_TYPE_NETWORK:
            $title .= __('Network server').' ID: '.$id_server;
        break;

        case SERVER_TYPE_SNMP:
            $title .= __('SNMP Trap server').' ID: '.$id_server;
        break;

        case SERVER_TYPE_DISCOVERY:
            $title .= __('Discovery server').' ID: '.$id_server;
        break;

        case SERVER_TYPE_PLUGIN:
            $title .= __('Plugin server').' ID: '.$id_server;
        break;

        case SERVER_TYPE_PREDICTION:
            $title .= __('Prediction server').' ID: '.$id_server;
        break;

        case SERVER_TYPE_WMI:
            $title .= __('WMI server').' ID: '.$id_server;
        break;

        case SERVER_TYPE_EXPORT:
            $title .= __('Export server').' ID: '.$id_server;
            $id_modulo = 0;
        break;

        case SERVER_TYPE_INVENTORY:
            $title .= __('Inventory server').' ID: '.$id_server;
        break;

        case SERVER_TYPE_WEB:
            $title .= __('Web server').' ID: '.$id_server;
        break;

        case SERVER_TYPE_EVENT:
            $title .= __('Event server').' ID: '.$id_server;
        break;

        case SERVER_TYPE_CORRELATION:
            $title .= __('Correlation server').' ID: '.$id_server;
        break;

        case SERVER_TYPE_MAINFRAME:
            $title .= __('Mainframe server').' ID: '.$id_server;
        break;

        case SERVER_TYPE_SYNC:
            $title .= __('Sync server').' ID: '.$id_server;
        break;

        case SERVER_TYPE_WUX:
            $title .= __('Wux server').' ID: '.$id_server;
        break;

        case SERVER_TYPE_SYSLOG:
            $title .= __('Log server').' ID: '.$id_server;
        break;

        case SERVER_TYPE_NCM:
            $title .= __('NCM server').' ID: '.$id_server;
        break;

        case SERVER_TYPE_AUTOPROVISION:
            $title .= __('Autoprovision server').' ID: '.$id_server;
        break;

        case SERVER_TYPE_MIGRATION:
            $title .= __('Migration server').' ID: '.$id_server;
        break;

        case SERVER_TYPE_ALERT:
            $title .= __('Alert server').' ID: '.$id_server;
        break;

        case SERVER_TYPE_NETFLOW:
            $title .= __('Netflow server').' ID: '.$id_server;
        break;

        case SERVER_TYPE_MADE:
            $title .= __('MADE server').' ID: '.$id_server;
        break;

        default:
            $title = __('Update server').' ID: '.$id_server;
        break;
    }

    // Headers.
    ui_print_standard_header(
        $title,
        'images/gm_servers.png',
        false,
        '',
        true,
        [],
        [
            [
                'link'  => '',
                'label' => __('Servers'),
            ],
            [
                'link'  => 'index.php?sec=gservers&sec2=godmode/servers/modificar_server',
                'label' => __('%s servers', get_product_name()),
            ],
        ]
    );

    echo '<form name="servers" method="POST" action="index.php?sec=gservers&sec2=godmode/servers/modificar_server&update=1">';
    html_print_input_hidden('server', $id_server);

    $server_type = __('Standard');
    if ($row['server_type'] == 13) {
        $server_type = __('Satellite');
    }

    $exec_server_enable = __('No');
    if ($row['exec_proxy'] == 1) {
        $exec_server_enable = __('Yes');
    }

    $table = new stdClass();

    $table->cellpadding = 4;
    $table->cellspacing = 4;
    $table->class = 'databox';
    $table->id = 'server_update_form';

    $table->data[] = [
        __('Name'),
        $row['name'],
    ];
    $table->data[] = [
        __('IP Address'),
        html_print_input_text('address', $row['ip_address'], '', 50, 0, true),
    ];
    $table->data[] = [
        __('Description'),
        html_print_input_text('description', $row['description'], '', 50, 0, true),
    ];

    html_print_table($table);

    $actionButtons = [];
    $actionButtons[] = html_print_submit_button(
        __('Update'),
        '',
        false,
        [ 'icon' => 'update' ],
        true
    );

    $actionButtons[] = html_print_go_back_button(
        'index.php?sec=gservers&sec2=godmode/servers/modificar_server',
        ['button_class' => ''],
        true
    );

    html_print_action_buttons(
        implode('', $actionButtons),
        ['type' => 'form_action'],
    );

    echo '</form>';

    if ((int) $row['server_type'] === 13) {
        html_print_div(
            [
                'class'   => 'mrgn_top_20px',
                'content' => ui_toggle($content, __('Credential boxes'), '', 'toggle_credential', false, true),
            ],
        );
    }
} else if (isset($_GET['server_remote']) === true) {
    // Headers.
    $id_server = get_parameter_get('server_remote');
    $ext = get_parameter('ext', '');
    $tab = get_parameter('tab', 'standard_editor');
    $advanced_editor = true;

    $server_type = (int) db_get_value(
        'server_type',
        'tserver',
        'id_server',
        $id_server
    );

    $buttons = [];

    // Buttons.
    if ((bool) check_acl($config['id_user'], 0, 'PM') === true) {
        $buttons = [
            'standard_editor' => [
                'active' => false,
                'text'   => '<a href="index.php?sec=gservers&sec2=godmode/servers/modificar_server&server_remote='.$id_server.'&ext='.$ext.'&tab=standard_editor&pure='.$pure.'">'.html_print_image('images/list.png', true, ['title' => __('Standard editor')]).'</a>',
            ],
            'advanced_editor' => [
                'active' => false,
                'text'   => '<a href="index.php?sec=gservers&sec2=godmode/servers/modificar_server&server_remote='.$id_server.'&ext='.$ext.'&tab=advanced_editor&pure='.$pure.'">'.html_print_image('images/pen.png', true, ['title' => __('Advanced editor')]).'</a>',
            ],
        ];
    }

    $buttons[$tab]['active'] = true;

        ui_print_standard_header(
            __('Remote Configuration'),
            'images/gm_servers.png',
            false,
            'servers',
            true,
            $buttons,
            [
                [
                    'link'  => '',
                    'label' => __('Servers'),
                ],
                [
                    'link'  => 'index.php?sec=gservers&sec2=godmode/servers/modificar_server',
                    'label' => __('%s servers', get_product_name()),
                ],
            ]
        );
    


    if ($tab === 'standard_editor') {
        $advanced_editor = false;

        if ($server_type === 13) {
            echo "<table cellpadding='4' cellspacing='4' class='databox filters margin-bottom-10 max_floating_element_size filter-table-adv'>
            <tr>";
            echo '<td class="w100p">';
            echo html_print_label_input_block(
                __('Dynamic search'),
                html_print_input_text(
                    'search_config_token',
                    $search,
                    '',
                    12,
                    255,
                    true,
                    false,
                    false,
                    '',
                    'w400px'
                )
            );
            echo '</td>';
            echo '</tr></table>';
        }
    }
} else {
    // Header.
    ui_print_standard_header(
        __('%s servers', get_product_name()),
        'images/gm_servers.png',
        false,
        '',
        true,
        [],
        [
            [
                'link'  => '',
                'label' => __('Servers'),
            ],
            [
                'link'  => '',
                'label' => __('Manage Servers'),
            ],
        ]
    );

    // Reset module count.
    if (isset($_GET['server_reset_counts'])) {
        $reslt = db_process_sql('UPDATE tagente SET update_module_count=1, update_alert_count=1');

        if ($result === false) {
            ui_print_error_message(__('Unsuccessfull action'));
        } else {
            ui_print_success_message(__('Successfully action'));
        }
    }

    if (isset($_GET['delete'])) {
        $id_server = get_parameter_get('server_del');

        $result = db_process_sql_delete('tserver', ['id_server' => $id_server]);

        if ($result !== false) {
             ui_print_success_message(__('Server deleted successfully'));
        } else {
            ui_print_error_message(__('There was a problem deleting the server'));
        }
    } else if (isset($_GET['update'])) {
        $address = trim(io_safe_output(get_parameter_post('address')), ' ');
        $description = trim(get_parameter_post('description'), '&#x20;');
        $id_server = get_parameter_post('server');
        $exec_proxy = get_parameter_post('exec_proxy');
        $port = get_parameter_post('port');

        $port_number = empty($port) ? 0 : $port;

        $values = [
            'ip_address'  => $address,
            'description' => $description,
            'exec_proxy'  => $exec_proxy,
            'port'        => $port_number,
        ];
        $result = db_process_sql_update('tserver', $values, ['id_server' => $id_server]);
        if ($result !== false) {
            ui_print_success_message(__('Server updated successfully'));
        } else {
            ui_print_error_message(__('There was a problem updating the server'));
        }
    } else if (isset($_GET['delete_conf_file'])) {
        $correct = false;
        $id_server = get_parameter('id_server');
        $ext = get_parameter('ext', '');
        $server_md5 = md5(io_safe_output(servers_get_name($id_server, 'none').$ext), false);

        if (file_exists($config['remote_config'].'/md5/'.$server_md5.'.srv.md5')) {
            // Server remote configuration editor.
            $file_name = $config['remote_config'].'/conf/'.$server_md5.'.srv.conf';
            $correct = @unlink($file_name);

            $file_name = $config['remote_config'].'/md5/'.$server_md5.'.srv.md5';
            $correct = @unlink($file_name);
        }

        ui_print_result_message(
            $correct,
            __('Conf file deleted successfully'),
            __('Could not delete conf file')
        );
    }


    $tiny = false;
    include $config['homedir'].'/godmode/servers/servers.build_table.php';
}
?>
