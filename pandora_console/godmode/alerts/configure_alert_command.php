<?php
// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps

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
global $config;

require_once $config['homedir'].'/include/functions_alerts.php';
check_login();

if (! check_acl($config['id_user'], 0, 'PM')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Alert Management'
    );
    include 'general/noaccess.php';
    exit;
}

$update_command = (bool) get_parameter('update_command');
$id = (int) get_parameter('id');
$pure = get_parameter('pure', 0);
$alert = [];

// Header.

    ui_print_standard_header(
        __('Alerts'),
        'images/gm_alerts.png',
        false,
        '',
        true,
        [],
        [
            [
                'link'  => '',
                'label' => __('Configure alert command'),
            ],
        ]
    );


if ($id > 0) {
    $alert = alerts_get_alert_command($id);

    if ($alert['internal'] || !check_acl_restricted_all($config['id_user'], $alert['id_group'], 'PM')) {
        db_pandora_audit(
            AUDIT_LOG_ACL_VIOLATION,
            'Trying to access Alert Management'
        );
        include 'general/noaccess.php';
        exit;
    }
}

if ($update_command) {
    $alert = alerts_get_alert_command($id);

    $name = (string) get_parameter('name');
    $command = (string) get_parameter('command');
    $description = (string) get_parameter('description');
    $id_group = (string) get_parameter('id_group', 0);

    $fields_descriptions = [];
    $fields_values = [];
    $fields_hidden = [];
    $info_fields = '';
    $values = [];
    for ($i = 1; $i <= $config['max_macro_fields']; $i++) {
        $fields_descriptions[] = (string) get_parameter('field'.$i.'_description');
        $fields_values[] = (string) get_parameter('field'.$i.'_values');
        $fields_hidden[] = get_parameter('field'.$i.'_hide');
        $info_fields .= ' Field'.$i.': '.$fields_values[($i - 1)];
    }

    $values['fields_values'] = io_json_mb_encode($fields_values);
    $values['fields_descriptions'] = io_json_mb_encode($fields_descriptions);
    $values['fields_hidden'] = io_json_mb_encode($fields_hidden);

    $values['name'] = $name;
    $values['command'] = $command;
    $values['description'] = $description;
    $values['id_group'] = $id_group;

    // Check it the new name is used in the other command.
    $id_check = db_get_value('id', 'talert_commands', 'name', $name);
    if (($id_check != $id) && (!empty($id_check))) {
        $result = false;
    } else {
        $result = (bool) alerts_update_alert_command($id, $values);
        if ($result === true) {
            $info = '{"Name":"'.$name.'","Command":"'.$command.'","Description":"'.$description.' '.$info_fields.'"}';
            $alert['fields_values'] = io_json_mb_encode($fields_values);
            $alert['fields_descriptions'] = io_json_mb_encode($fields_descriptions);
            $alert['name'] = $name;
            $alert['command'] = $command;
            $alert['description'] = $description;
            $alert['id_group'] = $id_group;
            $alert['fields_hidden'] = io_json_mb_encode($fields_hidden);
        }
    }

    $auditMessage = ((bool) $result === true) ? 'Update alert command' : 'Fail to update alert command';

    db_pandora_audit(
        AUDIT_LOG_ALERT_MANAGEMENT,
        sprintf('%s #%s', $auditMessage, $id),
        false,
        false,
        $info
    );

    ui_print_result_message(
        $result,
        __('Successfully updated'),
        __('Could not be updated')
    );
}


$name = '';
$command = '';
$description = '';
$fields_descriptions = '';
$fields_values = '';
$id_group = 0;
if ($id) {
    if ($result === false) {
        $alert = alerts_get_alert_command($id);
    }

    $name = $alert['name'];
    $command = $alert['command'];
    $description = $alert['description'];
    $id_group = $alert['id_group'];
    $fields_descriptions = $alert['fields_descriptions'];
    $fields_values = $alert['fields_values'];
    $fields_hidden = $alert['fields_hidden'];
}

if (empty($fields_descriptions) === false) {
    $fields_descriptions = json_decode($fields_descriptions, true);
}

if (empty($fields_values) === false) {
    $fields_values = json_decode($fields_values, true);
}

if (empty($fields_hidden) === false) {
    $fields_hidden = json_decode($fields_hidden, true);
}

$table = new stdClass();
$table->width = '100%';
$table->class = 'databox filters filter-table-adv';

$table->style = [];

$table->size = [];
$table->size[0] = '45%';
$table->size[1] = '45%';
$table->size[2] = '10%';
$table->data = [];

$table->data[0][0] = html_print_label_input_block(
    __('Name'),
    html_print_input_text(
        'name',
        $name,
        '',
        35,
        255,
        true,
        false,
        false,
        '',
        '',
        '',
        '',
        false,
        '',
        '',
        '',
        false
    )
);

if (users_can_manage_group_all('LM') === true) {
    $return_all_group = true;
}

$table->data[0][1] = html_print_label_input_block(
    __('Group'),
    html_print_select_groups(
        false,
        'LM',
        $return_all_group,
        'id_group',
        $id_group,
        false,
        '',
        0,
        true,
        false,
        true,
        '',
        false
    )
);

$table->data[1][0] = html_print_label_input_block(
    __('Command'),
    html_print_textarea(
        'command',
        8,
        30,
        $command,
        '',
        true,
        '',
        false
    )
);

$return_all_group = false;


$table->data[1][1] = html_print_label_input_block(
    __('Description'),
    html_print_textarea(
        'description',
        8,
        30,
        $description,
        '',
        true,
        '',
        false
    )
);

for ($i = 1; $i <= $config['max_macro_fields']; $i++) {
    if (empty($fields_descriptions) === false) {
        $field_description = $fields_descriptions[($i - 1)];
    } else {
        $field_description = '';
    }

    $table->data['field'.$i][0] = html_print_label_input_block(
        sprintf(__('Field %s description'), $i),
        html_print_input_text(
            'field'.$i.'_description',
            $field_description,
            '',
            30,
            255,
            true,
            false,
            false,
            '',
            '',
            '',
            '',
            false,
            '',
            '',
            '',
            false
        )
    );

    if (empty($fields_values) === false) {
        $field_values = $fields_values[($i - 1)];
    } else {
        $field_values = '';
    }

    if (empty($fields_hidden) === false) {
        $selected = (bool) $fields_hidden[($i - 1)];
    } else {
        $selected = false;
    }

    $table->data['field'.$i][1] = html_print_label_input_block(
        sprintf(__('Field %s values'), $i).ui_print_help_tip(
            __('value1,tag1;value2,tag2;value3,tag3'),
            true
        ),
        html_print_input_text(
            'field'.$i.'_values',
            $field_values,
            '',
            55,
            1000,
            true,
            false,
            false,
            '',
            'field_value',
            '',
            '',
            false,
            '',
            '',
            '',
            false
        )
    );

    $table->data['field'.$i][2] = html_print_label_input_block(
        __('Hide'),
        html_print_checkbox_extended(
            'field'.$i.'_hide',
            1,
            $selected,
            false,
            'cursor: \'pointer\'',
            ['input_class' => 'hide_inputs'],
            true
        )
    );
}

echo '<form method="post" action="index.php?sec=galertas&sec2=godmode/alerts/alert_commands&pure='.$pure.'" class="max_floating_element_size">';
html_print_table($table);

if ($id) {
    html_print_input_hidden('id', $id);
    html_print_input_hidden('update_command', 1);
    $buttonSubmit = html_print_submit_button(
        __('Update'),
        'create',
        false,
        ['icon' => 'wand'],
        true
    );
} else {
    html_print_input_hidden('create_command', 1);
    $buttonSubmit = html_print_submit_button(
        __('Create'),
        'create',
        false,
        ['icon' => 'wand'],
        true
    );
}

html_print_action_buttons($buttonSubmit);

echo '</form>';

?>

<script type="text/javascript">
$(document).ready (function () {

    $(".hide_inputs").each(function(index) {
        var $input_in_row = $(this).closest('tr').find('.field_value');
        if($(this).is(':checked')) {
            $input_in_row.prop('style', '-webkit-text-security: disc; font-family: text-security-disc;');
        } else {
            $input_in_row.prop('style', '');
        }
    });
    
    $(".hide_inputs").click(function() {
        var $input_in_row = $(this).closest('tr').find('.field_value');
        if($(this).is(':checked')) {
            $input_in_row.prop('style', '-webkit-text-security: disc; font-family: text-security-disc;');
        } else {
            $input_in_row.prop('style', '');
        }
    });
});
</script>
