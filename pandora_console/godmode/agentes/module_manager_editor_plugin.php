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


global $module;

$macros = $module['macros'];

$disabledBecauseInPolicy = false;
$disabledTextBecauseInPolicy = '';
$classdisabledBecauseInPolicy = '';
$page = get_parameter('page', '');
if (strstr($page, 'policy_modules') === false) {
    $disabledBecauseInPolicy = false;

    if ($disabledBecauseInPolicy) {
        $disabledTextBecauseInPolicy = 'readonly = "readonly"';
        $classdisabledBecauseInPolicy = 'readonly';
    }
}

define('ID_NETWORK_COMPONENT_TYPE', 4);

if (empty($edit_module)) {
    // Function in module_manager_editor_common.php
    add_component_selection(ID_NETWORK_COMPONENT_TYPE);
} else {
    // TODO: Print network component if available
}

$extra_title = __('Plugin server module');

$data = [];
$data[0] = __('Plugin').ui_print_help_icon('plugin_macros', true);
$data[1] = html_print_select_from_sql(
    'SELECT id, name FROM tplugin ORDER BY name',
    'id_plugin',
    $id_plugin,
    'changePluginSelect();',
    __('None'),
    0,
    true,
    false,
    false,
    $disabledBecauseInPolicy
);
// Store the macros in base64 into a hidden control to move between pages
$data[1] .= html_print_input_hidden(
    'macros',
    (isset($macros) === true) ? base64_encode($macros) : '',
    true
);

$table_simple->colspan['plugin_1'][2] = 2;

if (!empty($id_plugin)) {
    $preload = db_get_sql("SELECT description FROM tplugin WHERE id = $id_plugin");
    $preload = io_safe_output($preload);
    $preload = str_replace("\n", '<br>', $preload);
} else {
    $preload = '';
}

$data[2] = '<span class="normal" id="plugin_description">'.$preload.'</span>';

push_table_simple($data, 'plugin_1');

// A hidden "model row" to clone it from javascript to add fields dynamicly
$data = [];
$data[0] = 'macro_desc';
$data[0] .= ui_print_help_tip('macro_help', true);
$data[1] = html_print_input_text('macro_name[]', 'macro_value', '', 100, 1024, true);
$table_simple->colspan['macro_field'][1] = 3;
$table_simple->rowstyle['macro_field'] = 'display:none';

push_table_simple($data, 'macro_field');

$password_fields = [];

// If there are $macros, we create the form fields
if (!empty($macros)) {
    $macros = json_decode(io_safe_output($macros), true);
    foreach ($macros as $k => $m) {
        $data = [];
        $data[0] = $m['desc'];
        if (!empty($m['help'])) {
            $data[0] .= ui_print_help_tip($m['help'], true);
        }

        $m_hide = false;
        if (isset($m['hide'])) {
            $m_hide = $m['hide'];
        }

        if ($m_hide) {
            $data[1] = html_print_input_password(
                $m['macro'],
                io_output_password($m['value']),
                '',
                100,
                1024,
                true,
                false,
                false,
                '',
                'off',
                true
            );
            array_push($password_fields, $m);
        } else {
            $data[1] = html_print_input_text(
                $m['macro'],
                $m['value'],
                '',
                100,
                1024,
                true,
                false,
                false,
                '',
                $classdisabledBecauseInPolicy
            );
        }

        $table_simple->colspan['macro'.$m['macro']][1] = 3;
        $table_simple->rowclass['macro'.$m['macro']] = 'macro_field';

        push_table_simple($data, 'macro'.$m['macro']);
    }
}

// Add input password values with js to hide it in browser inspector.
foreach ($password_fields as $k => $p) {
    echo "
        <script>
            $(document).ready(() => {
                $('input[name=\"".$p['macro']."\"]').val('".$p['value']."');
            });
        </script>
    ";
}

?>
<script type="text/javascript">
    function changePluginSelect() {
        if (typeof flag_load_plugin_component !== 'undefined' && flag_load_plugin_component) {
            flag_load_plugin_component = false;
            
            return;
        }

        const moduleId = <?php echo $id_agent_module; ?>;

        load_plugin_description($("#id_plugin").val());

        load_plugin_macros_fields('simple-macro', moduleId, 0);

        forced_title_callback();

        $('select#id_plugin').select2('close');
    }

    $(document).ready(function () {
        if ($("#id_plugin").val() !== 0) {
            changePluginSelect();
        }
    });
</script>
