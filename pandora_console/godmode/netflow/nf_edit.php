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

require_once $config['homedir'].'/include/functions_ui.php';
require_once $config['homedir'].'/include/functions_db.php';
require_once $config['homedir'].'/include/functions_netflow.php';

check_login();

if (! check_acl($config['id_user'], 0, 'AW')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access event viewer'
    );
    include 'general/noaccess.php';
    return;
}

$pure = get_parameter('pure', 0);

// Header.
ui_print_standard_header(
    __('Manage Filters'),
    'images/gm_netflow.png',
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
            'label' => __('Netflow filters'),
        ],
    ]
);

$is_windows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
if ($is_windows === true) {
    ui_print_error_message(__('Not supported in Windows systems'));
}

$delete = (bool) get_parameter('delete');
$multiple_delete = (bool) get_parameter('multiple_delete', 0);
$id = (int) get_parameter('id');
$name = (string) get_parameter('name');

if ($id > 0) {
    $filter_group = db_get_value('id_group', 'tnetflow_filter', 'id_sg', $id);

    if (!check_acl_restricted_all($config['id_user'], $filter_group, 'AW')) {
        db_pandora_audit(
            AUDIT_LOG_ACL_VIOLATION,
            'Trying to access events filter editor'
        );
        include 'general/noaccess.php';
        return;
    }
}

if ($delete) {
    $id_filter = db_get_value('id_name', 'tnetflow_filter', 'id_sg', $id);
    $result = db_process_sql_delete(
        'tnetflow_filter',
        ['id_sg' => $id]
    );

    $result2 = db_process_sql_delete(
        'tnetflow_report_content',
        ['id_filter' => $id_filter]
    );

    if ($result !== false) {
        $result = true;
    } else {
        $result = false;
    }

    ui_print_result_message(
        $result,
        __('Successfully deleted'),
        __('Not deleted. Error deleting data')
    );
}

if ($multiple_delete) {
    $ids = (array) get_parameter('delete_multiple', []);

    foreach ($ids as $id) {
        $id_filter = db_get_value('id_name', 'tnetflow_filter', 'id_sg', $id);
        $result = db_process_sql_delete(
            'tnetflow_filter',
            ['id_sg' => $id]
        );

        $result2 = db_process_sql_delete(
            'tnetflow_report_content',
            ['id_filter' => $id_filter]
        );

        if ($result === false) {
            break;
        }
    }

    if ($result !== false) {
        $result = true;
    } else {
        $result = false;
    }

    ui_print_result_message(
        $result,
        __('Successfully deleted'),
        __('Not deleted. Error deleting data')
    );
}

$own_info = get_user_info($config['id_user']);
// Get group list that user has access
$groups_user = users_get_groups($config['id_user'], 'AW', $own_info['is_admin'], true);
$sql = 'SELECT *
	FROM tnetflow_filter
	WHERE id_group IN (0, '.implode(',', array_keys($groups_user)).')';
$filters = db_get_all_rows_sql($sql);
if ($filters === false) {
    $filters = [];
}

$table = new stdClass();
$table->width = '100%';
$table->class = 'info_table';

$table->head = [];
$table->head[0] = html_print_checkbox('all_delete', 0, false, true, false);
$table->head[1] = __('Name');
$table->head[2] = __('Group');
$table->head[3] = __('Action');
$table->style = [];
$table->style[1] = 'font-weight: bold';
$table->align = [];

$table->size = [];
$table->size[0] = '10px';
$table->size[1] = '60%';
$table->size[2] = '30%';
$table->size[3] = '80px';
$table->data = [];

$total_filters = db_get_all_rows_filter('tnetflow_filter', false, 'COUNT(*) AS total');
$total_filters = $total_filters[0]['total'];

// ui_pagination ($total_filters, $url);
foreach ($filters as $filter) {
    $data = [];

    $data[0] = '';

    if (check_acl_restricted_all($config['id_user'], $filter['id_group'], 'AW')) {
        $data[0] = html_print_checkbox_extended('delete_multiple[]', $filter['id_sg'], false, false, '', 'class="check_delete"', true);
        $data[1] = '<a href="'.$config['homeurl'].'index.php?sec=netf&sec2=godmode/netflow/nf_edit_form&id='.$filter['id_sg'].'&pure='.$pure.'">'.$filter['id_name'].'</a>';
    } else {
        $data[1] = $filter['id_name'];
    }


    $data[2] = ui_print_group_icon($filter['id_group'], true);
    $data[3] = '';

    if (check_acl_restricted_all($config['id_user'], $filter['id_group'], 'AW')) {
        $table->cellclass[][3] = 'table_action_buttons';
        $data[3] = '<a onclick="if(confirm(\''.__('Are you sure?').'\')) return true; else return false;" href="'.$config['homeurl'].'index.php?sec=netf&sec2=godmode/netflow/nf_edit&delete=1&id='.$filter['id_sg'].'&offset=0&pure='.$pure.'">';
        $data[3] .= html_print_image('images/delete.svg', true, ['title' => __('Delete'), 'class' => 'main_menu_icon invert_filter']);
        $data[3] .= '</a>';
    }

    array_push($table->data, $data);
}

$buttons = html_print_submit_button(
    __('Create filter'),
    'crt',
    false,
    ['icon' => 'wand'],
    true
);

if (empty($filters) === false) {
    echo '<form id="multiple_delete" method="POST" action="'.$config['homeurl'].'index.php?sec=netf&sec2=godmode/netflow/nf_edit&pure='.$pure.'">';
    html_print_input_hidden('multiple_delete', 1);
    html_print_table($table);
    echo '</form>';
    $buttons .= html_print_submit_button(__('Delete'), 'delete_btn', false, ['icon' => 'delete', 'mode' => 'secondary', 'form' => 'multiple_delete'], true);
} else {
    ui_print_info_message(['no_close' => true, 'message' => __('There are no defined filters') ]);
}

echo '<form method="post" action="'.$config['homeurl'].'index.php?sec=netf&sec2=godmode/netflow/nf_edit_form&pure='.$pure.'">';
html_print_action_buttons(
    $buttons
);
echo '</form>';

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
