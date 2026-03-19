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

if ((bool) check_acl($config['id_user'], 0, 'PM') === false) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Group Management'
    );
    include 'general/noaccess.php';

    return;
}

// Header.
ui_print_standard_header(
    __('Agents custom fields manager'),
    'images/custom_field.png',
    false,
    '',
    true,
    [],
    [
        [
            'link'  => '',
            'label' => __('Resources'),
        ],
        [
            'link'  => '',
            'label' => __('Custom fields'),
        ],
    ]
);

$create_field = (bool) get_parameter('create_field');
$update_field = (bool) get_parameter('update_field');
$delete_field = (bool) get_parameter('delete_field');
$id_field = (int) get_parameter('id_field', 0);
$name = (string) get_parameter('name', '');
$display_on_front = (int) get_parameter('display_on_front', 0);
$is_password_type = (int) get_parameter('is_password_type', 0);
$combo_values = (string) get_parameter('combo_values', '');
$combo_value_selected = (string) get_parameter('combo_value_selected', '');
$is_link_enabled = (bool) get_parameter('is_link_enabled', 0);

// Create field.
if ($create_field) {
    // Check if name field is empty.
    if ($name === '') {
        ui_print_error_message(__('The name must not be empty'));
    } else if ($name == db_get_value('name', 'tagent_custom_fields', 'name', $name)) {
        ui_print_error_message(__('The name must be unique'));
    } else {
        $result = db_process_sql_insert(
            'tagent_custom_fields',
            [
                'name'             => $name,
                'display_on_front' => $display_on_front,
                'is_password_type' => $is_password_type,
                'combo_values'     => $combo_values,
                'is_link_enabled'  => $is_link_enabled,
            ]
        );
        ui_print_success_message(__('Field successfully created'));
    }
}

// Update field.
if ($update_field) {
    // Check if name field is empty.
    if ($name !== '') {
        $values = [
            'name'             => $name,
            'display_on_front' => $display_on_front,
            'is_password_type' => $is_password_type,
            'combo_values'     => $combo_values,
            'is_link_enabled'  => $is_link_enabled,
        ];

        $result = db_process_sql_update('tagent_custom_fields', $values, ['id_field' => $id_field]);
    } else {
        $result = false;
    }

    if ($result !== false) {
        ui_print_success_message(__('Field successfully updated'));
    } else {
        ui_print_error_message(__('There was a problem modifying field'));
    }
}

// Delete field.
if ($delete_field) {
    $result = db_process_sql_delete(
        'tagent_custom_fields',
        ['id_field' => $id_field]
    );

    if (!$result) {
        ui_print_error_message(__('There was a problem deleting field'));
    } else {
        ui_print_success_message(__('Field successfully deleted'));
    }
}

// Prepare pagination.
$offset = (int) get_parameter('offset');
$limit = $config['block_size'];
$count_fields = db_get_value('count(*)', 'tagent_custom_fields');

$fields = db_get_all_rows_filter(
    'tagent_custom_fields',
    [
        'limit'  => $limit,
        'offset' => $offset,
    ]
);

$table = new stdClass();
$table->class = 'info_table';
if ($fields) {
    $table->head = [];
    $table->head[0] = __('ID');
    $table->head[1] = __('Field');
    $table->head[2] = __('Display on front').ui_print_help_tip(__('The fields with display on front enabled will be displayed into the agent details'), true);
    $table->head[3] = __('Actions');
    $table->align = [];
    $table->align[0] = 'left';
    $table->align[2] = 'left';
    $table->align[3] = 'left';
    $table->size[3] = '8%';
    $table->data = [];
} else {
    include_once $config['homedir'].'/general/first_task/fields_manager.php';
    return;
}

if ($fields === false) {
    $fields = [];
}


foreach ($fields as $field) {
    $data[0] = $field['id_field'];
    $data[1] = $field['name'];

    $data[2] = html_print_image(
        ((bool) $field['display_on_front'] === true) ? 'images/validate.svg' : 'images/fail@svg.svg',
        true,
        ['class' => 'main_menu_icon invert_filter']
    );

    $table->cellclass[][3] = 'table_action_buttons';
    $tableActionButtons = [];
    $tableActionButtons[] = html_print_anchor(
        [
            'href'    => 'index.php?sec=gagente&sec2=godmode/agentes/configure_field&id_field='.$field['id_field'],
            'content' => html_print_image(
                'images/edit.svg',
                true,
                [
                    'title' => __('Edit'),
                    'class' => 'main_menu_icon invert_filter',
                ]
            ),
        ],
        true
    );

    $tableActionButtons[] = html_print_anchor(
        [
            'href'    => 'index.php?sec=gagente&sec2=godmode/agentes/fields_manager&delete_field=1&id_field='.$field['id_field'],
            'content' => html_print_image(
                'images/delete.svg',
                true,
                [
                    'title' => __('Delete'),
                    'class' => 'main_menu_icon invert_filter',
                ]
            ),
            'onClick' => 'if (!confirm(\' '.__('Are you sure?').'\')) return false;',
        ],
        true
    );

    $data[3] = implode('', $tableActionButtons);

    array_push($table->data, $data);
}

$tablePagination = '';
if ($fields) {
    html_print_table($table);
    $tablePagination = ui_pagination($count_fields, false, $offset, 0, true, 'offset', false);
}

echo '<form method="POST" action="index.php?sec=gagente&sec2=godmode/agentes/configure_field">';
html_print_action_buttons(
    html_print_submit_button(
        __('Create field'),
        'crt',
        false,
        [ 'icon' => 'next' ],
        true
    ),
    [
        'type'          => 'form_action',
        'right_content' => $tablePagination,
    ],
);
echo '</form>';
