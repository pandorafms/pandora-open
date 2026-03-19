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

if (! check_acl($config['id_user'], 0, 'PM')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Group Management'
    );
    include 'general/noaccess.php';
    return;
}

$id_field = (int) get_parameter('id_field', 0);
$name = (string) get_parameter('name', '');
$display_on_front = (bool) get_parameter('display_on_front', 0);
$is_password_type = (bool) get_parameter('is_password_type', 0);
$is_combo_enable = (bool) get_parameter('is_combo_enable', 0);
$combo_values = (string) get_parameter('combo_values', '');
$is_link_enabled = (bool) get_parameter('is_link_enabled', 0);

// Header.
if ($id_field) {
    $field = db_get_row_filter('tagent_custom_fields', ['id_field' => $id_field]);
    $name = $field['name'];
    $display_on_front = $field['display_on_front'];
    $is_password_type = $field['is_password_type'];
    $combo_values = $field['combo_values'] ? $field['combo_values'] : '';
    $is_combo_enable = (isset($config['is_combo_enable']) === true) ? $config['is_combo_enable'] : false;
    $is_link_enabled = $field['is_link_enabled'];
    $header_title = __('Update agent custom field');
} else {
    $header_title = __('Create agent custom field');
}

ui_print_standard_header(
    $header_title,
    'images/custom_field.png',
    false,
    '',
    true,
    [],
    [
        [
            'link'  => 'index.php?sec=gagente&sec2=godmode/agentes/fields_manager',
            'label' => __('Resources'),
        ],
        [
            'link'  => 'index.php?sec=gagente&sec2=godmode/agentes/fields_manager',
            'label' => __('Custom field'),
        ],
        [
            'link'  => '',
            'label' => __('Edit'),
        ],
    ]
);

echo "<div id='message_set_password'  title='".__('Agent Custom Fields Information')."' class='invisible'>";
echo "<p class='center bolder'>".__('You cannot set the Password type until you clear the combo values and click on update button.').'</p>';
echo '</div>';

echo "<div id='message_set_combo'  title='".__('Agent Custom Fields Information')."' class='invisible'>";
echo "<p class='center bolder'>".__('You cannot unset the enable combo until you clear the combo values and click on update.').'</p>';
echo '</div>';

echo "<div id='message_no_set_password'  title='".__('Agent Custom Fields Information')."' class='invisible'>";
echo "<p class='center bolder'>".__('If you select Enabled combo the Password type will be disabled.').'</p>';
echo '</div>';

echo "<div id='message_no_set_combo'  title='".__('Agent Custom Fields Information')."' class='invisible'>";
echo "<p class='center bolder'>".__('If you select Passord type the Enabled combo will be disabled.').'</p>';
echo '</div>';

$table = new stdClass();
$table->class = 'databox filter-table-adv';
$table->id = 'configure_field';
$table->width = '100%';
$table->size = [];
$table->size[0] = '50%';
$table->size[1] = '50%';

$table->data = [];

$table->data[0][0] = html_print_label_input_block(
    __('Name'),
    html_print_input_text(
        'name',
        $name,
        '',
        35,
        100,
        true
    )
);

$table->data[0][1] = html_print_label_input_block(
    __('Display on front').ui_print_help_tip(
        __('The fields with display on front enabled will be displayed into the agent details'),
        true
    ),
    html_print_checkbox_switch(
        'display_on_front',
        1,
        $display_on_front,
        true
    )
);

$table->data[1][0] = html_print_label_input_block(
    __('Link type'),
    html_print_checkbox_switch_extended(
        'is_link_enabled',
        1,
        $is_link_enabled,
        false,
        '',
        '',
        true
    )
);

$table->data[2][0] = html_print_label_input_block(
    __('Pass type').ui_print_help_tip(
        __('The fields with pass type enabled will be displayed like html input type pass in html'),
        true
    ),
    html_print_checkbox_switch(
        'is_password_type',
        1,
        $is_password_type,
        true
    )
);

if (isset($config['is_combo_enable']) === false) {
    $config['is_combo_enable'] = false;
}

$table->data[2][1] = html_print_label_input_block(
    __('Enabled combo'),
    html_print_checkbox_switch_extended(
        'is_combo_enable',
        0,
        $config['is_combo_enable'],
        false,
        '',
        '',
        true
    )
);

$table->data[3][0] = html_print_label_input_block(
    __('Combo values').ui_print_help_tip(
        __('Set values separated by comma'),
        true
    ),
    html_print_textarea(
        'combo_values',
        3,
        65,
        io_safe_output($combo_values),
        '',
        true
    )
);

echo '<form class="max_floating_element_size" name="field" method="post" action="index.php?sec=gagente&sec2=godmode/agentes/fields_manager">';
html_print_table($table);

if ($id_field > 0) {
    html_print_input_hidden('update_field', 1);
    html_print_input_hidden('id_field', $id_field);
    $buttonCaption = __('Update');
    $buttonName = 'updbutton';
} else {
    html_print_input_hidden('create_field', 1);
    $buttonCaption = __('Create');
    $buttonName = 'crtbutton';
}

$actionButtons = [];
$actionButtons[] = html_print_submit_button(
    $buttonCaption,
    $buttonName,
    false,
    [ 'icon' => 'wand' ],
    true
);
$actionButtons[] = html_print_go_back_button(
    'index.php?sec=gagente&sec2=godmode/agentes/fields_manager',
    ['button_class' => ''],
    true
);

html_print_action_buttons(
    implode('', $actionButtons),
    ['type' => 'form_action'],
);

echo '</form>';
?>

<script>
$(document).ready (function () {
    if($('input[type=hidden][name=update_field]').val() == 1 && $('#textarea_combo_values').val() != ''){
        $('input[type=checkbox][name=is_combo_enable]').prop('checked', true);
        $('#configure_field-3').show();

        $('input[type=checkbox][name=is_password_type]').change(function (e) {
            dialog_message("#message_set_password");
            $('input[type=checkbox][name=is_password_type]').prop('checked', false);
            $('input[type=checkbox][name=is_combo_enable]').prop('checked', true);
            $('#configure_field-3').show();
            e.preventDefault();
        });

        $('input[type=checkbox][name=is_combo_enable]').change(function (e) {
            if($('#textarea_combo_values').val() != '' &&  $('input[type=checkbox][name=is_combo_enable]').prop('checked', true)){
                dialog_message("#message_set_combo");
                $('input[type=checkbox][name=is_combo_enable]').prop('checked', true);
                $('#configure_field-3').show();
                e.preventDefault();
            }
        });
    } else {
        $('#configure_field-3').hide();
    }
   
    if ($('input[type=checkbox][name=is_link_enabled]').is(":checked") === true) {
        $('#configure_field-2').hide();
    } else {
        $('#configure_field-2').show();
    }

    $('input[type=checkbox][name=is_link_enabled]').change(function () {
        if( $('input[type=checkbox][name=is_link_enabled]').prop('checked') ){
            $('#configure_field-2').hide();
            $('#configure_field-3').hide();
        } else{
            $('#configure_field-2').show();
            if($('input[type=checkbox][name=is_combo_enable]').prop('checked') === true) {
                $('#configure_field-3').show();
            }
        }
    });
    
    $('input[type=checkbox][name=is_combo_enable]').change(function () {
        if( $('input[type=checkbox][name=is_combo_enable]').prop('checked') ){
          $('#configure_field-3').show();
          dialog_message("#message_no_set_password");
          $('#configure_field-1').hide();
          $('#configure_field-2-0').hide();
        }
        else{
          $('#configure_field-3').hide();
          $('#configure_field-1').show();
          $('#configure_field-2-0').show();
        }
    });
    $('input[type=checkbox][name=is_password_type]').change(function () {
        if( $('input[type=checkbox][name=is_password_type]').prop('checked')){
            $('#configure_field-1').hide();
            dialog_message("#message_no_set_combo");
            $('#configure_field-3').hide();
            $('#configure_field-2-1').hide();
        }
        else{
            if($('input[type=checkbox][name=is_combo_enable]').prop('checked') === true) {
                $('#configure_field-3').show();
            }
            $('#configure_field-1').show();
            $('#configure_field-2-1').show();
        }
    });
});

function dialog_message(message_id) {
  $(message_id)
    .css("display", "inline")
    .dialog({
      modal: true,
      show: "blind",
      hide: "blind",
      width: "400px",
      buttons: {
        Close: function() {
          $(this).dialog("close");
        }
      }
    });
}

</script>
