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

$extra_title = __('WMI server module');

define('ID_NETWORK_COMPONENT_TYPE', 6);

if (empty($edit_module)) {
    // Function in module_manager_editor_common.php
    add_component_selection(ID_NETWORK_COMPONENT_TYPE);
} else {
    // TODO: Print network component if available
}

$data = [];

if ($ip_target == 'auto') {
    $ip_target = agents_get_address($id_agente);
}

$inputs = html_print_input_text(
    'ip_target',
    $ip_target,
    '',
    15,
    60,
    true,
    false,
    false,
    '',
    'mrgn_top_10px w100p'
);

$data[0] = html_print_label_input_block(
    __('Target IP').' <span class="help_icon_15px">'.ui_print_help_icon('wmi_module_tab', true),
    $inputs,
    [
        'label_class' => 'font-title-font',
        'div_class'   => 'w100p mrgn_right_20px',
    ]
);

$data[2] = html_print_label_input_block(
    __('Namespace').ui_print_help_tip(__('Optional. WMI namespace. If unsure leave blank.'), true),
    html_print_input_text(
        'tcp_send',
        $tcp_send,
        '',
        5,
        20,
        true,
        false,
        false,
        '',
        $classdisabledBecauseInPolicy.' mrgn_top_10px w100p'
    ),
    [
        'label_class' => 'font-title-font',
        'div_class'   => 'w100p mrgn_right_20px',
    ]
);
push_table_simple($data, 'target_ip');

$data = [];
$data[0] = html_print_label_input_block(
    __('Username'),
    html_print_input_text(
        'plugin_user',
        $plugin_user,
        '',
        15,
        60,
        true,
        false,
        false,
        '',
        $classdisabledBecauseInPolicy.' w100p'
    ),
    [
        'label_class' => 'font-title-font',
        'div_class'   => 'w100p display-grid mrgn_right_20px',
    ]
);

$data[2] = html_print_label_input_block(
    __('Password'),
    html_print_input_password(
        'plugin_pass',
        $plugin_pass,
        '',
        15,
        60,
        true,
        false,
        false,
        $classdisabledBecauseInPolicy.' w100p',
        'new-password',
        true
    ),
    [
        'label_class' => 'font-title-font',
        'div_class'   => 'w100p display-grid mrgn_right_20px',
    ]
);
$table_simple->rowclass['user_pass'] = 'w100p mrgn_top_10px';

push_table_simple($data, 'user_pass');

$data = [];
$data[0] = html_print_label_input_block(
    __('WMI query'),
    html_print_input_text(
        'snmp_oid',
        $snmp_oid,
        '',
        35,
        255,
        true,
        false,
        false,
        '',
        $classdisabledBecauseInPolicy
    ),
    [
        'label_class' => 'font-title-font',
        'div_class'   => 'w100p display-grid mrgn_right_20px',
    ]
);

$data[2] = html_print_label_input_block(
    __('Key string').ui_print_help_tip(__('Optional. Substring to look for in the WQL query result. The module returns 1 if found, 0 if not.'), true),
    html_print_input_text(
        'snmp_community',
        $snmp_community,
        '',
        20,
        60,
        true,
        false,
        false,
        '',
        $classdisabledBecauseInPolicy
    ),
    [
        'label_class' => 'font-title-font',
        'div_class'   => 'w100p display-grid mrgn_right_20px',
    ]
);
$table_simple->rowclass['wmi_query'] = 'w100p mrgn_top_10px';

push_table_simple($data, 'wmi_query');

$data = [];
$data[0] = html_print_label_input_block(
    __('Field number').ui_print_help_tip(__('Column number to retrieve from the WQL query result (starting from zero).'), true),
    html_print_input_text(
        'tcp_port',
        $tcp_port,
        '',
        5,
        15,
        true,
        false,
        false,
        '',
        $classdisabledBecauseInPolicy.' mrgn_right_20px'
    ),
    [
        'label_class' => 'font-title-font',
        'div_class'   => 'w50p display-grid',
    ]
);

$table_simple->rowclass['key_field'] = 'w100p mrgn_top_10px';

push_table_simple($data, 'key_field');
?>
<script type="text/javascript">
$(document).ready (function () {
    var custom_ip_target = "<?php echo $custom_ip_target; ?>";
    var ip_target = "<?php echo $ip_target; ?>";
    if(ip_target === 'custom'){
        $("#text-custom_ip_target").show();
    } else {
        $("#text-custom_ip_target").hide();
    }

    $('#ip_target').change(function() {
        if($(this).val() == 'custom') {
            $("#text-custom_ip_target").show();
        }
        else{
            $("#text-custom_ip_target").hide();
        }
    });
});

</script>
