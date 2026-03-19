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
global $config;

require_once 'include/functions_ui.php';

check_login();

$update = (bool) get_parameter('update');

$table = new stdClass();
$table->width = '100%';
$table->class = 'databox filter-table-adv';

$table->data = [];

$table->data[0][] = html_print_label_input_block(
    __('Data storage path'),
    html_print_input_text('sflow_name_dir', $config['sflow_name_dir'], false, 50, 200, true)
);

$table->data[0][] = html_print_label_input_block(
    __('Daemon interval'),
    html_print_input_text('sflow_interval', $config['sflow_interval'], false, 50, 200, true)
);

$table->data[1][] = html_print_label_input_block(
    __('Daemon binary path'),
    html_print_input_text('sflow_daemon', $config['sflow_daemon'], false, 50, 200, true)
);

$table->data[1][] = html_print_label_input_block(
    __('Nfdump binary path'),
    html_print_input_text('sflow_nfdump', $config['sflow_nfdump'], false, 50, 200, true)
);

$table->data[2][] = html_print_label_input_block(
    __('Nfexpire binary path'),
    html_print_input_text('sflow_nfexpire', $config['sflow_nfexpire'], false, 50, 200, true)
);

$table->data[2][] = html_print_label_input_block(
    __('Maximum chart resolution'),
    html_print_input_text('sflow_max_resolution', $config['sflow_max_resolution'], false, 50, 200, true)
);

$table->data[3][] = html_print_label_input_block(
    __('Disable custom live view filters'),
    html_print_checkbox_switch('sflow_disable_custom_lvfilters', 1, $config['sflow_disable_custom_lvfilters'], true)
);

$table->data[3][] = html_print_label_input_block(
    __('Sflow max lifetime'),
    html_print_input_text('sflow_max_lifetime', $config['sflow_max_lifetime'], false, 50, 200, true)
);

$onclick = "if (!confirm('".__('Warning').'. '.__('IP address resolution can take a lot of time')."')) return false;";
$table->data[4][] = html_print_label_input_block(
    __('Name resolution for IP address'),
    html_print_checkbox_switch_extended('sflow_get_ip_hostname', 1, $config['sflow_get_ip_hostname'], false, $onclick, '', true)
);

echo '<form class="max_floating_element_size" id="sflow_setup" method="post">';
html_print_table($table);
html_print_input_hidden('update_config', 1);
html_print_action_buttons(
    html_print_submit_button(
        __('Update'),
        'upd_button',
        false,
        ['icon' => 'update'],
        true
    )
);
echo '</form>';
?>
<script>
    $("input[name=sflow_name_dir]").on("input", function() {
        $(this).val($(this).val().replace(/[^a-z0-9]/gi, ""));
    });
</script>