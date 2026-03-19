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

if (! check_acl($config['id_user'], 0, 'PM') && ! check_acl($config['id_user'], 0, 'AW')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access SNMO Groups Management'
    );
    include 'general/noaccess.php';
    exit;
}

require_once $config['homedir'].'/include/functions_network_components.php';

$id = (int) get_parameter('id');
$sec = 'gmodules';

if ($id) {
    $group = network_components_get_group($id);
    $name = $group['name'];
    $parent = $group['parent'];
} else {
    $name = '';
    $parent = '';
}

$table = new stdClass();
$table->class = 'databox';



$table->style = [];
$table->style[0] = 'width: 0';
$table->style[1] = 'width: 0';

$table->data = [];
$table->data[0][0] = __('Name');
$table->data[0][1] = __('Parent');
$table->data[1][0] = html_print_input_text('name', $name, '', 0, 255, true, false, false, '', 'w100p');
$table->data[1][1] = html_print_select(
    network_components_get_groups(),
    'parent',
    $parent,
    false,
    __('None'),
    0,
    true,
    false,
    false
);

$manageNcGroupsUrl = 'index.php?sec='.$sec.'&sec2=godmode/modules/manage_nc_groups';

echo '<form method="post" action="'.$manageNcGroupsUrl.'">';
html_print_table($table);

if ($id) {
    html_print_input_hidden('update', 1);
    html_print_input_hidden('id', $id);
    $actionButtonTitle = __('Update');
} else {
    html_print_input_hidden('create', 1);
    $actionButtonTitle = __('Create');
}

$actionButtons = [];

$actionButtons[] = html_print_submit_button(
    $actionButtonTitle,
    'crt',
    false,
    ['icon' => 'wand'],
    true
);

$actionButtons[] = html_print_go_back_button(
    $manageNcGroupsUrl,
    ['button_class' => ''],
    true
);

html_print_action_buttons(
    implode('', $actionButtons),
    [ 'type' => 'form_action']
);

echo '</form>';
