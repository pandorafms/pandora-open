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

global $config;

// Check user credentials
check_login();

if (! check_acl($config['id_user'], 0, 'PM') && ! check_acl($config['id_user'], 0, 'AW')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Inventory Module Management'
    );
    include 'general/noaccess.php';
    return;
}


// Header

    $sec = 'gmodules';
    ui_print_standard_header(
        __('Module management'),
        'images/op_inventory.png',
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

// Initialize variables
$id_module_inventory = (int) get_parameter('id_module_inventory');

$script_mode = 1;

// Updating
if ($id_module_inventory) {
    $row = db_get_row(
        'tmodule_inventory',
        'id_module_inventory',
        $id_module_inventory
    );

    if (!empty($row)) {
        $name = $row['name'];
        $description = $row['description'];
        $id_os = $row['id_os'];
        $interpreter = $row['interpreter'];
        $code = $row['code'];
        $data_format = $row['data_format'];
        $block_mode = $row['block_mode'];
        $script_path = $row['script_path'];
        $script_mode = $row['script_mode'];
    } else {
        ui_print_error_message(__('Inventory module error'));
        include 'general/footer.php';
        return;
    }

    // New module
} else {
    $name = '';
    $description = '';
    $id_os = 1;
    $interpreter = '';
    $code = '';
    $data_format = '';
    $block_mode = 0;
}

if ($id_os == null) {
    $disabled = true;
} else {
    $disabled = false;
}

$table = new stdClass();
$table->width = '100%';
$table->class = 'databox filter-table-adv';
$table->style = [];
$table->style[0] = 'width: 50%';
$table->style[1] = 'width: 50%';
$table->data = [];

$table->data[0][] = html_print_label_input_block(
    __('Name'),
    html_print_input_text(
        'name',
        $name,
        '',
        45,
        100,
        true,
        $disabled
    )
);

$table->data[0][] = html_print_label_input_block(
    __('Description'),
    html_print_input_text(
        'description',
        $description,
        '',
        60,
        500,
        true
    )
);

$table->data[1][] = html_print_label_input_block(
    __('OS'),
    html_print_select_from_sql(
        'SELECT id_os, name FROM tconfig_os ORDER BY name',
        'id_os',
        $id_os,
        '',
        '',
        '',
        $return = true
    )
);

$table->data[1][] = html_print_label_input_block(
    __('Interpreter'),
    html_print_input_text(
        'interpreter',
        $interpreter,
        '',
        25,
        100,
        true
    ).ui_print_input_placeholder(
        __('Left blank for the LOCAL inventory modules'),
        true
    )
);

$table->data[2][] = html_print_label_input_block(
    __('Format'),
    html_print_input_text(
        'format',
        $data_format,
        '',
        50,
        100,
        true
    ).ui_print_input_placeholder(
        __('Separate fields with').' '.SEPARATOR_COLUMN,
        true
    )
);

$table->data[2][] = html_print_label_input_block(
    __('Block Mode'),
    html_print_checkbox_switch(
        'block_mode',
        1,
        $block_mode,
        true
    )
);

$radioButtons = [];
$radioButtons[] = html_print_radio_button('script_mode', 1, __('Script mode'), $script_mode, true);
$radioButtons[] = html_print_radio_button('script_mode', 2, __('Use inline code'), $script_mode, true);

$table->data[3][] = html_print_label_input_block(
    __('Script mode'),
    html_print_div(
        [
            'class'   => 'switch_radio_button',
            'content' => implode('', $radioButtons),
        ],
        true
    )
);

$table->colspan[4][0] = 2;

$table->data[4][0] = html_print_label_input_block(
    __('Script path'),
    html_print_input_text(
        'script_path',
        $script_path,
        '',
        50,
        1000,
        true
    ),
    ['div_class' => 'script_path_inventory_modules']
);

$table->data[4][0] .= html_print_label_input_block(
    __('Code'),
    html_print_textarea(
        'code',
        25,
        80,
        base64_decode($code),
        '',
        true
    ).ui_print_input_placeholder(
        __("Here is placed the script for the REMOTE inventory modules Local inventory modules don't use this field").SEPARATOR_COLUMN,
        true
    ),
    ['div_class' => 'code_inventory_modules']
);

echo '<form name="inventorymodule" id="inventorymodule_form" class="max_floating_element_size" method="post" 
	action="index.php?sec='.$sec.'&sec2=godmode/modules/manage_inventory_modules">';

html_print_table($table);
if ($id_module_inventory) {
    html_print_input_hidden('update_module_inventory', 1);
    html_print_input_hidden('id_module_inventory', $id_module_inventory);
    $buttonCaption = __('Update');
    $buttonIcon = 'update';
} else {
    html_print_input_hidden('create_module_inventory', 1);
    $buttonCaption = __('Create');
    $buttonIcon = 'wand';
}

$actionButtons = '';
$actionButtons = html_print_submit_button(
    $buttonCaption,
    'submit',
    false,
    ['icon' => $buttonIcon],
    true
);
$actionButtons .= html_print_go_back_button(
    'index.php?sec=gmodules&sec2=godmode/modules/manage_inventory_modules',
    ['button_class' => ''],
    true
);

html_print_action_buttons($actionButtons);
echo '</form>';

?>

<script type="text/javascript">
    $(document).ready (function () {
        var mode = <?php echo $script_mode; ?>;

        if (mode == 1) {
            $('.script_path_inventory_modules').show();
            $('.code_inventory_modules').hide();
        } else {
            $('.code_inventory_modules').show();
            $('.script_path_inventory_modules').hide();
        }

        $('input[type=radio][name=script_mode]').change(function() {
            if (this.value == 1) {
                $('.script_path_inventory_modules').show();
                $('.code_inventory_modules').hide();
            }
            else if (this.value == 2) {
                $('.code_inventory_modules').show();
                $('.script_path_inventory_modules').hide();
            }
        });
    });
</script>
