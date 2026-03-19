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

check_login();

if (! check_acl($config['id_user'], 0, 'PM')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Group Management'
    );
    include 'general/noaccess.php';
    return;
}

if (is_ajax() === true) {
    $get_group_json = (bool) get_parameter('get_group_json');
    $get_group_agents = (bool) get_parameter('get_group_agents');

    if ($get_group_json === true) {
        $id_group = (int) get_parameter('id_group');

        if (! check_acl($config['id_user'], $id_group, 'AR')) {
            db_pandora_audit(
                AUDIT_LOG_ACL_VIOLATION,
                'Trying to access Alert Management'
            );
            echo json_encode(false);
            return;
        }

        $group = db_get_row('tmodule_group', 'id_mg', $id_group);

        echo json_encode($group);
        return;
    }

    return;
}

    // Header.
    ui_print_standard_header(
        __('Module groups list'),
        'images/module_group.png',
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
                'label' => __('Module groups'),
            ],
        ]
    );

$create_group = (bool) get_parameter('create_group');
$update_group = (bool) get_parameter('update_group');
$delete_group = (bool) get_parameter('delete_group');

// Create group.
if ($create_group === true) {
    $name = (string) get_parameter('name');
    $icon = (string) get_parameter('icon');
    $id_parent = (int) get_parameter('id_parent');
    $alerts_disabled = (bool) get_parameter('alerts_disabled');
    $custom_id = (string) get_parameter('custom_id');
    $check = db_get_value('name', 'tmodule_group', 'name', $name);

    if ($name) {
        if (!$check) {
            $result = db_process_sql_insert(
                'tmodule_group',
                ['name' => $name]
            );

            if ($result) {
                ui_print_success_message(__('Group successfully created'));
            } else {
                ui_print_error_message(
                    __('There was a problem creating group')
                );
            }
        } else {
            ui_print_error_message(
                __('Each module group must have a different name')
            );
        }
    } else {
        ui_print_error_message(__('Module group must have a name'));
    }
}

// Update group.
if ($update_group === true) {
    $id_group = (int) get_parameter('id_group');
    $name = (string) get_parameter('name');
    $icon = (string) get_parameter('icon');
    $id_parent = (int) get_parameter('id_parent');
    $alerts_enabled = (bool) get_parameter('alerts_enabled');
    $custom_id = (string) get_parameter('custom_id');
    $check = db_get_value('name', 'tmodule_group', 'name', $name);
    $subcheck = db_get_value('name', 'tmodule_group', 'id_mg', $id_group);

    if ($name) {
        if ($check === false || strcasecmp($subcheck, $name) === 0) {
            $result = db_process_sql_update(
                'tmodule_group',
                ['name' => $name],
                ['id_mg' => $id_group]
            );

            if ($result !== false) {
                ui_print_success_message(__('Group successfully updated'));
            } else {
                ui_print_error_message(
                    __('There was a problem modifying group')
                );
            }
        } else {
            ui_print_error_message(
                __('Each module group must have a different name')
            );
        }
    } else {
        ui_print_error_message(__('Module group must have a name'));
    }
}

// Delete group.
if ($delete_group === true) {
    $id_group = (int) get_parameter('id_group');

    $result = db_process_sql_delete('tmodule_group', ['id_mg' => $id_group]);

    if ((bool) $result === true) {
        $result = db_process_sql_update(
            'tagente_modulo',
            ['id_module_group' => 0],
            ['id_module_group' => $id_group]
        );
        db_process_sql_update(
            'tcontainer_item',
            ['id_module_group' => 0],
            ['id_module_group' => $id_group]
        );
        db_process_sql_update(
            'tnetwork_component',
            ['id_module_group' => 0],
            ['id_module_group' => $id_group]
        );
        db_process_sql_update(
            'treport_content',
            ['id_module_group' => 0],
            ['id_module_group' => $id_group]
        );
        db_process_sql_update(
            'tnetwork_map',
            ['id_module_group' => 0],
            ['id_module_group' => $id_group]
        );
        db_process_sql_update(
            'tlocal_component',
            ['id_module_group' => 0],
            ['id_module_group' => $id_group]
        );
        db_process_sql_update(
            'treport_content_template',
            ['id_module_group' => 0],
            ['id_module_group' => $id_group]
        );

        // A group with no modules can be deleted,
        // to avoid a message error then do the follwing.
        if ($result !== false) {
            $result = true;
        }
    }

    ui_print_result_message(
        $result,
        __('Group successfully deleted'),
        __('There was a problem deleting group')
    );
}

// Prepare pagination.
$total_groups = db_get_num_rows('SELECT * FROM tmodule_group');
$url = ui_get_url_refresh(['offset' => false]);
$offset = (int) get_parameter('offset', 0);

$sql = 'SELECT *
    FROM tmodule_group
    ORDER BY name ASC
    LIMIT '.$offset.', '.$config['block_size'];

$groups = db_get_all_rows_sql($sql);

$table = new stdClass();
$table->class = 'info_table';

if (empty($groups) === false) {
    $table->head = [];
    $table->head[0] = __('ID');
    $table->head[1] = __('Name');
    $table->head[2] = __('Delete');

    $table->size[0] = '5%';

    $table->align = [];
    $table->align[1] = 'left';
    $table->align[2] = 'left';
    $table->size[2] = '5%';

    $table->data = [];
    $offset_delete = ($offset >= $total_groups - 1) ? ($offset - $config['block_size']) : $offset;
    foreach ($groups as $id_group) {
        $data = [];
        $data[0] = $id_group['id_mg'];

        $data[1] = '<strong><a href="index.php?sec=gmodules&sec2=godmode/groups/configure_modu_group&id_group='.$id_group['id_mg'].'&offset='.$offset.'">'.ui_print_truncate_text($id_group['name'], GENERIC_SIZE_TEXT).'</a></strong>';

        $table->cellclass[][2] = 'table_action_buttons';
        $data[2] = '<a href="index.php?sec=gmodules&sec2=godmode/groups/modu_group_list&id_group='.$id_group['id_mg'].'&delete_group=1&offset='.$offset_delete.'" onClick="if (!confirm(\' '.__('Are you sure?').'\')) return false;">'.html_print_image('images/delete.svg', true, ['class' => 'main_menu_icon invert_filter']).'</a>';

        array_push($table->data, $data);
    }

    html_print_table($table);
    $tablePagination = ui_pagination($total_groups, $url, $offset, 0, true, 'offset', false);
} else {
    ui_print_info_message(
        [
            'no_close' => true,
            'message'  => __('There are no defined module groups'),
        ]
    );
}

echo '<form method="post" action="index.php?sec=gmodules&sec2=godmode/groups/configure_modu_group">';
html_print_action_buttons(
    html_print_submit_button(
        __('Create module group'),
        'crt',
        false,
        [ 'icon' => 'next' ],
        true
    ),
    [
        'type'          => 'form_action',
        'right_content' => $tablePagination,
    ]
);
echo '</form>';
