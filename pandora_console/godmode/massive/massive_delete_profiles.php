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

// Begin.
check_login();

if (! check_acl($config['id_user'], 0, 'UM')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access massive profile deletion'
    );
    include 'general/noaccess.php';
    return;
}

require_once 'include/functions_agents.php';
require_once 'include/functions_alerts.php';
require_once $config['homedir'].'/include/functions_profile.php';

if (is_ajax()) {
    $get_users = (bool) get_parameter('get_users');

    if ($get_users) {
        $id_group = get_parameter('id_group');
        $id_profile = get_parameter('id_profile');
        $get_all_groups = get_parameter('get_all_groups', '0');

        if ($get_all_groups !== '0') {
            $profile_data = db_get_all_rows_sql(
                'SELECT *
                    FROM tusuario_perfil
                    WHERE `id_perfil` = "'.$id_profile[0].'"
                    GROUP BY id_usuario'
            );
        } else {
            if (strlen($id_profile[0]) > 0 && strlen($id_group[0]) > 0) {
                $profile_data = db_get_all_rows_filter(
                    'tusuario_perfil',
                    [
                        'id_perfil' => $id_profile[0],
                        'id_grupo'  => $id_group[0],
                    ]
                );
            }
        }

        if (!users_is_admin()) {
            foreach ($profile_data as $user => $values) {
                if (users_is_admin($values['id_usuario'])) {
                    unset($profile_data[$user]);
                }
            }
        }

        echo json_encode(index_array($profile_data, 'id_up', 'id_usuario'));
        return;
    }

    return;
}

$delete_profiles = (int) get_parameter('delete_profiles');

if ($delete_profiles) {
    $profiles_id = get_parameter('profiles_id', -1);
    $groups_id = get_parameter('groups_id', -1);
    $users = get_parameter('users_id', -1);

    if ($profiles_id == -1 || $groups_id == -1 || $users == -1) {
        $result = false;
    } else {
        foreach ($users as $user) {
            db_pandora_audit(
                AUDIT_LOG_USER_MANAGEMENT,
                'Deleted profile for user '.io_safe_input($user)
            );

            $result = profile_delete_user_profile_group($user, $profiles_id[0], $groups_id[0]);
        }
    }

    $info = [
        'Profiles' => implode(',', $profiles_id),
        'Groups'   => implode(',', $groups_id),
        'Users'    => implode(',', $users),
    ];

    if ($result) {
        db_pandora_audit(
            AUDIT_LOG_MASSIVE_MANAGEMENT,
            'Delete profile ',
            false,
            false,
            json_encode($info)
        );
    } else {
        db_pandora_audit(
            AUDIT_LOG_MASSIVE_MANAGEMENT,
            'Fail try to delete profile',
            false,
            false,
            json_encode($info)
        );
    }

    ui_print_result_message(
        $result,
        __('Profiles deleted successfully'),
        __('Profiles cannot be deleted')
    );
}

if ($table !== null) {
    html_print_table($table);
}

unset($table);

$table = new stdClass();
$table->width = '100%';
$table->class = 'databox filters';
$table->data = [];
$table->head = [];
$table->align = [];
$table->style = [];

$table->head[0] = __('Profile name');
$table->head[1] = __('Group');
$table->head[2] = __('Users');
$table->align[2] = 'center';
$table->size[0] = '34%';
$table->size[1] = '33%';
$table->size[2] = '33%';

$data = [];
$data[0] = '<form method="post" id="form_profiles" action="index.php?sec=gmassive&sec2=godmode/massive/massive_operations&tab=massive_users&option=delete_profiles">';
$display_all_group = true;
if (check_acl($config['id_user'], 0, 'PM')) {
    $data[0] .= html_print_select(
        profile_get_profiles(),
        'profiles_id[]',
        '',
        '',
        '',
        '',
        true,
        false,
        false,
        '',
        false,
        'width: 100%'
    );
} else {
    $group_um = users_get_groups_UM($config['id_user']);
    if (!isset($group_um[0])) {
        $display_all_group = false;
    }

    $data[0] .= html_print_select(
        profile_get_profiles(
            [
                'pandora_management' => '<> 1',
                'db_management'      => '<> 1',
            ]
        ),
        'profiles_id[]',
        '',
        '',
        '',
        '',
        true,
        false,
        false,
        '',
        false,
        'width: 100%'
    );
}

$data[1] = html_print_select_groups(
    $config['id_user'],
    'UM',
    $display_all_group,
    'groups_id[]',
    '',
    '',
    '',
    '',
    true,
    false,
    false,
    '',
    false,
    'width: 100%'
);
$data[2] = '<span id="users_loading" class="invisible">';
$data[2] .= html_print_image('images/spinner.png', true);
$data[2] .= '</span>';
$users_profiles = '';
$users_order = [
    'field' => 'id_user',
    'order' => 'ASC',
];

$data[2] .= html_print_select(
    [],
    'users_id[]',
    '',
    '',
    '',
    '',
    true,
    true,
    true,
    '',
    false,
    'width: 100%'
);

array_push($table->data, $data);
$table->data[1][0] = '';
$table->data[1][1] = html_print_label_input_block(
    __('Show all groups'),
    html_print_checkbox_switch(
        'get_all_groups',
        1,
        $get_all_groups,
        true,
        false,
        '',
        false,
        ' float-right'
    ),
    ['div_class' => 'center_align']
);

html_print_table($table);

attachActionButton('delete_profiles', 'delete', $table->width, false, $SelectAction);

echo '</form>';

unset($table);

// TODO: Change to iu_print_error system.
echo '<h3 class="error invisible" id="message"> </h3>';

ui_require_jquery_file('form');
ui_require_jquery_file('pandora.controls');
?>

<script type="text/javascript">
/* <![CDATA[ */
$(document).ready (function () {
    update_users();

    function update_users() {
        var $select = $("#users_id").disable ();
        $("#users_loading").show ();
        $("option", $select).remove ();

        jQuery.post ("ajax.php",
            {"page" : "godmode/massive/massive_delete_profiles",
            "get_users" : 1,
            "id_group[]" : $("#groups_id").val(),
            "id_profile[]" : $("#profiles_id").val(),
            "get_all_groups" : $('#checkbox-get_all_groups').is(':checked') ? 1 : 0
            },
            function (data, status) {
                options = "";
                jQuery.each (data, function (id, value) {
                    options += "<option value=\""+value+"\">"+value+"</option>";
                });
                $("#users_id").append (options);
                $("#users_loading").hide ();
                $select.enable ();
            },
            "json"
        );
    }

    $("#groups_id").change (function () {
        update_users();
    });

    $("#profiles_id").change (function () {
        update_users();
    });

    $("#checkbox-get_all_groups").change (function () {
        update_users();
    });
});
/* ]]> */
</script>
