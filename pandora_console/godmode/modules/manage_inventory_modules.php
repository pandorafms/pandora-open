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
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
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

// Load global variables
global $config;

// Check user credentials.
check_login();

if (! check_acl($config['id_user'], 0, 'PM') && ! check_acl($config['id_user'], 0, 'AW')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Inventory Module Management'
    );
    include 'general/noaccess.php';
    return;
}

require_once $config['homedir'].'/include/functions_inventory.php';

// Header.

    $sec = 'gmodules';

    ui_print_standard_header(
        __('Inventory modules'),
        'images/hardware-software-component@svg.svg',
        false,
        '',
        true,
        [],
        [
            [
                'link'  => '',
                'label' => __('Configuration'),
            ],
            [
                'link'  => '',
                'label' => __('Inventory modules'),
            ],
        ]
    );


$is_windows = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN';
if ($is_windows) {
    ui_print_error_message(__('Not supported in Windows systems'));
}

// Initialize variables.
$offset = (int) get_parameter('offset');
$create_module_inventory = (bool) get_parameter('create_module_inventory');
$update_module_inventory = (bool) get_parameter('update_module_inventory');
$delete_inventory_module = (int) get_parameter('delete_inventory_module');
$multiple_delete = (bool) get_parameter('multiple_delete', 0);
$id_module_inventory = (int) get_parameter('id_module_inventory');
$name = (string) get_parameter('name');
$description = (string) get_parameter('description');
$id_os = (int) get_parameter('id_os');
if ($id_os == 0) {
    $id_os = 'NULL';
}

$interpreter = (string) get_parameter('interpreter');
$script_mode = (string) get_parameter('script_mode');
$code = (string) get_parameter('code');
$code = base64_encode(str_replace("\r", '', html_entity_decode($code, ENT_QUOTES)));
$format = (string) get_parameter('format');
$block_mode = (int) get_parameter('block_mode', 0);
$script_path = (string) get_parameter('script_path');

// Create inventory module.
if ($create_module_inventory === true) {
    $values = [
        'name'        => $name,
        'description' => $description,
        'id_os'       => $id_os,
        'interpreter' => $interpreter,
        'code'        => $code,
        'data_format' => $format,
        'block_mode'  => $block_mode,
        'script_mode' => $script_mode,
        'script_path' => $script_path,
    ];

    $result = (bool) inventory_create_inventory_module($values);

    $auditMessage = ((bool) $result === true) ? sprintf('Create inventory module #%s', $result) : 'Fail try to create inventory module';
    db_pandora_audit(
        AUDIT_LOG_MODULE_MANAGEMENT,
        $auditMessage
    );

    ui_print_result_message(
        (bool) $result,
        __('Successfully created inventory module'),
        __('Error creating inventory module')
    );

    // Update inventory module.
} else if ($update_module_inventory === true) {
    $values = [
        'name'        => $name,
        'description' => $description,
        'id_os'       => $id_os,
        'interpreter' => $interpreter,
        'code'        => $code,
        'data_format' => $format,
        'block_mode'  => $block_mode,
        'script_mode' => $script_mode,
        'script_path' => $script_path,
    ];

    $result = inventory_update_inventory_module($id_module_inventory, $values);

    $auditMessage = ((bool) $result === true) ? 'Update inventory module' : 'Fail try to update inventory module';
    db_pandora_audit(
        AUDIT_LOG_MODULE_MANAGEMENT,
        sprintf('%s #%s', $auditMessage, $id_module_inventory)
    );

    ui_print_result_message(
        (bool) $result,
        __('Successfully updated inventory module'),
        __('Error updating inventory module')
    );

    // Delete inventory module.
} else if ((bool) $delete_inventory_module === true) {
    $result = db_process_sql_delete(
        'tmodule_inventory',
        ['id_module_inventory' => $delete_inventory_module]
    );

    $auditMessage = ((bool) $result === true) ? 'Delete inventory module' : 'Fail try to delete inventory module';
    db_pandora_audit(
        AUDIT_LOG_MODULE_MANAGEMENT,
        sprintf('%s #%s', $auditMessage, $id_module_inventory)
    );

    ui_print_result_message(
        (bool) $result,
        __('Successfully deleted inventory module'),
        __('Error deleting inventory module')
    );

    
} else if ($multiple_delete) {
    $ids = (array) get_parameter('delete_multiple', []);

    foreach ($ids as $id) {
        $result = db_process_sql_delete('tmodule_inventory', ['id_module_inventory' => $id]);

        if ($result === false) {
            break;
        }
    }

    if ($result !== false) {
        $result = true;
    } else {
        $result = false;
    }

    $str_ids = implode(',', $ids);
    $auditMessage = ($result === true) ? 'Multiple delete inventory module' : 'Fail try to delete inventory module';
    db_pandora_audit(
        AUDIT_LOG_MODULE_MANAGEMENT,
        sprintf('%s :%s', $auditMessage, $str_ids)
    );

    ui_print_result_message(
        $result,
        __('Successfully multiple deleted'),
        __('Not deleted. Error deleting multiple data')
    );

    $id = 0;

    
}

$total_modules = db_get_sql('SELECT COUNT(*) FROM tmodule_inventory');

$table = new stdClass();
$table->styleTable = 'margin: 10px 10px 0; width: -webkit-fill-available; width: -moz-available';
$table->class = 'info_table';
$table->size = [];
$table->size[0] = '140px';
$table->align = [];
$table->align[2] = 'left';
$table->align[4] = 'left';
$table->data = [];
$table->head = [];
$table->head[0] = __('Name');
$table->head[1] = __('Description');
$table->head[2] = __('OS');
$table->head[3] = __('Interpreter');

if ($management_allowed === true) {
    $table->head[4] = __('Action').html_print_checkbox('all_delete', 0, false, true, false);
    $table->size[4] = '80px';
}

$result = inventory_get_modules_list($offset);

if ($result === false) {
    ui_print_info_message(['no_close' => true, 'message' => __('No inventory modules defined') ]);
} else {
    $status = '';
    $begin = true;
    while ($row = array_shift($result)) {
        $data = [];
        $begin = false;
        if ($management_allowed === true) {
            $data[0] = '<strong><a href="index.php?sec='.$sec.'&sec2=godmode/modules/manage_inventory_modules_form&id_module_inventory='.$row['id_module_inventory'].'">'.$row['name'].'</a></strong>';
        } else {
            $data[0] = '<strong>'.$row['name'].'</strong>';
        }

        $data[1] = $row['description'];
        if ($row['os_name'] == null) {
            $data[2] = html_print_image('images/agents@svg.svg', true, ['border' => '0', 'alt' => __('Agent'), 'title' => __('Agent'), 'height' => '18', 'class' => 'invert_filter main_menu_icon']);
        } else {
            $data[2] = html_print_div(
                [
                    'class'   => 'invert_filter main_menu_icon',
                    'content' => ui_print_os_icon($row['id_os'], false, true),
                ],
                true
            );
        }

        if ($row['interpreter'] == '') {
            $data[3] = __('Local module');
        } else {
            $data[3] = __('Remote/Local');
        }

        if ($management_allowed === true) {
            // Update module.
            $data[4] = '<div class="table_action_buttons">';
            $data[4] .= '<a href="index.php?sec='.$sec.'&sec2=godmode/modules/manage_inventory_modules_form&id_module_inventory='.$row['id_module_inventory'].'">';
            $data[4] .= html_print_image('images/edit.svg', true, ['border' => '0', 'title' => __('Update'), 'class' => 'main_menu_icon invert_filter']).'</b></a>';

            // Delete module.
            $data[4] .= '<a href="index.php?sec='.$sec.'&sec2=godmode/modules/manage_inventory_modules&delete_inventory_module='.$row['id_module_inventory'].'" onClick="if (!confirm(\''.__('Are you sure?').'\')) return false;">';
            $data[4] .= html_print_image('images/delete.svg', true, ['border' => '0', 'title' => __('Delete'), 'class' => 'main_menu_icon invert_filter']);
            $data[4] .= '</b></a>&nbsp;&nbsp;';
            $data[4] .= html_print_checkbox_extended('delete_multiple[]', $row['id_module_inventory'], false, false, '', 'class="check_delete"', true);
            $data[4] .= '</div>';
        }

        array_push($table->data, $data);
    }

    echo '<form id="form_delete" method="POST" action="index.php?sec='.$sec.'&sec2=godmode/modules/manage_inventory_modules">';
    html_print_input_hidden('multiple_delete', 1);
    html_print_table($table);
    echo '</form>';

    echo '<form id="form_create" method="post" action="index.php?sec='.$sec.'&sec2=godmode/modules/manage_inventory_modules_form">';
    echo html_print_input_hidden('create_module_inventory', 1);
    echo '<form>';

    $tablePagination = ui_pagination(
        $total_modules,
        'index.php?sec='.$sec.'&sec2=godmode/modules/manage_inventory_modules',
        $offset,
        0,
        true,
        'offset',
        false
    );

    $actionButtons = '';

    if ($management_allowed === true) {
        $actionButtons .= html_print_submit_button(
            __('Create'),
            'crt',
            false,
            [
                'icon' => 'wand',
                'form' => 'form_create',
            ],
            true
        );

        $actionButtons .= html_print_submit_button(
            __('Delete'),
            'delete_btn',
            false,
            [
                'icon' => 'delete',
                'mode' => 'secondary',
                'form' => 'form_delete',
            ],
            true
        );
    }

    html_print_action_buttons(
        $actionButtons,
        [
            'type'          => 'form_action',
            'right_content' => $tablePagination,
        ],
        false
    );
}



?>
<script type="text/javascript">
    $( document ).ready(function() {
        $('[id^=checkbox-all_delete]').change(function() {
            if ($("input[name=all_delete]").prop("checked")) {
                $(".custom_checkbox_input").prop("checked", true);
            }
            else {
                $(".custom_checkbox_input").prop("checked", false);
            }
        });
    });
</script>
