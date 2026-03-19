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

// Load global vars
global $config;

check_login();

if (! check_acl($config['id_user'], 0, 'PM') && ! is_user_admin($config['id_user'])) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Visual Setup Management'
    );
    include 'general/noaccess.php';
    return;
}

require_once 'include/functions_gis.php';

ui_require_javascript_file('openlayers.pandora');

$action = get_parameter('action');

switch ($action) {
    case 'save_edit_map_connection':
        if (!$errorfill) {
            ui_print_success_message(__('Successfully updated'));
        } else {
            ui_print_error_message(__('Could not be updated'));
        }
    break;

    case 'save_map_connection':
        if (!$errorfill) {
            ui_print_success_message(__('Successfully created'));
        } else {
            ui_print_error_message(__('Could not be created'));
        }
    break;

    case 'delete_connection':
        $idConnectionMap = get_parameter('id_connection_map');

        $result = gis_delete_map_connection($idConnectionMap);

        if ($result === false) {
            ui_print_error_message(__('Could not be deleted'));
        } else {
            ui_print_success_message(__('Successfully deleted'));
        }
    break;
}

$table = new stdClass();
$table->class = 'info_table';
$table->width = '100%';
$table->head[0] = __('Map connection name');
$table->head[1] = __('Group');
$table->head[3] = __('Delete');

$table->align[1] = 'left';
$table->align[2] = 'left';
$table->align[3] = 'left';

$mapsConnections = db_get_all_rows_in_table('tgis_map_connection', 'conection_name');

$table->data = [];

if ($mapsConnections !== false) {
    foreach ($mapsConnections as $mapsConnection) {
        $table->data[] = [
            '<a href="index.php?sec=gsetup&sec2=godmode/setup/gis_step_2&amp;action=edit_connection_map&amp;id_connection_map='.$mapsConnection['id_tmap_connection'].'">'.$mapsConnection['conection_name'].'</a>',
            ui_print_group_icon($mapsConnection['group_id'], true),
            '<a href="index.php?sec=gsetup&sec2=godmode/setup/setup&amp;section=gis&amp;id_connection_map='.$mapsConnection['id_tmap_connection'].'&amp;action=delete_connection"
				onClick="javascript: if (!confirm(\''.__('Do you wan delete this connection?').'\')) return false;">'.html_print_image('images/delete.svg', true, ['class' => 'invert_filter main_menu_icon']).'</a>',
        ];
        $table->cellclass[][2] = 'table_action_buttons';
    }
}

html_print_table($table);

echo '<div class="action-buttons" style="width: '.$table->width.'">';
echo '<form action="index.php?sec=gsetup&sec2=godmode/setup/gis_step_2" method="post">';
html_print_input_hidden('action', 'create_connection_map');
html_print_action_buttons(
    html_print_submit_button(
        __('Create'),
        '',
        false,
        ['icon' => 'wand'],
        true
    )
);
echo '</form>';
echo '</div>';
