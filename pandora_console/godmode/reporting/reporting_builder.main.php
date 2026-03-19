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

// Login check.
check_login();

if (! check_acl($config['id_user'], 0, 'RW')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access report builder'
    );
    include 'general/noaccess.php';
    exit;
}

require_once $config['homedir'].'/include/functions_users.php';

$groups = users_get_groups();

switch ($action) {
    default:
    case 'new':
        $actionButtons = html_print_submit_button(
            __('Create'),
            'add',
            false,
            [ 'icon' => 'next' ],
            true
        );
        $hiddenFieldAction = 'save';
    break;
    case 'update':
    case 'edit':
        $actionButtons = html_print_submit_button(
            __('Update'),
            'edit',
            false,
            [ 'icon' => 'next' ],
            true
        );
        $hiddenFieldAction = 'update';
    break;
}

$table = new stdClass();
$table->width = '100%';
$table->id = 'add_alert_table';
$table->head = [];
$table->data = [];
$table->size = [];


$table->class = 'filter-table-adv databox';
$table->size[0] = '50%';
$table->size[1] = '50%';
$table->size[2] = '50%';
$table->size[3] = '50%';


$write_groups = users_get_groups_for_select(
    false,
    'AR',
    true,
    true,
    false,
    'id_grupo'
);

$table->data[0][0] = html_print_label_input_block(
    __('Name'),
    html_print_input_text(
        'name',
        $reportName,
        __('Name'),
        false,
        100,
        true,
        false,
        true
    )
);


// If the report group is not among the
// RW groups (special permission) we add it.
if (isset($write_groups[$idGroupReport]) === false && $idGroupReport) {
    $write_groups[$idGroupReport] = groups_get_name($idGroupReport);
}

$return_all_group = false;

if (users_can_manage_group_all('RW') === true) {
    $return_all_group = true;
}


$table->data[0][1] = html_print_label_input_block(
    __('Group'),
    html_print_input(
        [
            'type'           => 'select_groups',
            'id_user'        => $config['id_user'],
            'privilege'      => 'AR',
            'returnAllGroup' => $return_all_group,
            'name'           => 'id_group',
            'selected'       => $idGroupReport,
            'script'         => '',
            'nothing'        => '',
            'nothing_value'  => '',
            'return'         => true,
            'required'       => true,
        ]
    )
);


$table->colspan[1][0] = 2;
$table->data[1][0] = html_print_label_input_block(
    __('Description'),
    html_print_textarea(
        'description',
        2,
        1,
        $description,
        '',
        true
    )
);


if ($report_id_user == $config['id_user']
    || is_user_admin($config['id_user'])
) {
    // S/he is the creator of report (or admin) and s/he can change the access.
    $type_access = [
        'group_view' => __('Only the group can view the report'),
        'group_edit' => __('The next group can edit the report'),
        'user_edit'  => __('Only the user and admin user can edit the report'),
    ];
    $table->data[2][0] = html_print_label_input_block(
        __('Write Access').ui_print_help_tip(
            __('For example, you want a report that the people of "All" groups can see but you want to edit only for you or your group.'),
            true
        ),
        html_print_select(
            $type_access,
            'type_access',
            $type_access_selected,
            'change_type_access(this)',
            '',
            0,
            true
        )
    );

    $options['div_class'] = 'invisible_important';
    $options['div_id'] = 'group_edit';
    if ($type_access_selected == 'group_edit') {
        $options['div_class'] = '';
    }

    $table->data[3][0] = html_print_label_input_block(
        __('Write Access Group'),
        html_print_select_groups(
            false,
            'RW',
            false,
            'id_group_edit',
            $id_group_edit,
            false,
            '',
            '',
            true
        ),
        $options
    );
}


echo '<form class="" method="post">';
html_print_table($table);

echo '<div class="action-buttons" style="width: '.$table->width.'">';
html_print_action_buttons($actionButtons, ['type' => 'form_action']);
html_print_input_hidden('action', $hiddenFieldAction);
html_print_input_hidden('id_report', $idReport);
echo '</div></form>';
?>
<script type="text/javascript">
    function change_type_access(select_item) {
        if ($(select_item).val() == "group_edit") {
            $("#group_edit").removeClass('invisible_important');
        } else {
            $("#group_edit").addClass('invisible_important');
        }

    }
</script>
