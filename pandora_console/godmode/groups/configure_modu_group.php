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

check_login();

if (! check_acl($config['id_user'], 0, 'PM')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Group Management2'
    );
    include 'general/noaccess.php';
    return;
}

    // Header
    ui_print_standard_header(
        __('Module group management'),
        'images/module_group.png',
        false,
        '',
        true,
        [],
        [
            [
                'link'  => '',
                'label' => __('Resources'),
            ],
            [
                'link'  => '',
                'label' => __('Module groups'),
            ],
        ]
    );


// Init vars
$icon = '';
$name = '';
$id_parent = 0;
$alerts_disabled = 0;
$custom_id = '';

$create_group = (bool) get_parameter('create_group');
$id_group = (int) get_parameter('id_group');
$offset = (int) get_parameter('offset', 0);

if ($id_group) {
    $group = db_get_row('tmodule_group', 'id_mg', $id_group);
    if ($group) {
        $name = $group['name'];
    } else {
        ui_print_error_message(__('There was a problem loading group'));
        echo '</table>';
        echo '</div>';
        echo '<div id="both">&nbsp;</div>';
        echo '</div>';
        echo '<div id="foot">';
        // include 'general/footer.php';
        echo '</div>';
        echo '</div>';
        exit;
    }
}

$table = new stdClass();
$table->class = 'databox';
$table->style[0] = 'font-weight: bold';
$table->data = [];
$table->data[0][0] = __('Name');
$table->data[1][0] = html_print_input_text('name', $name, '', 35, 100, true);


echo '</span>';

    $formUrl = 'index.php?sec=gmodules&sec2=godmode/groups/modu_group_list&offset='.$offset;


echo '<form name="grupo" method="POST" action="'.$formUrl.'">';
html_print_table($table);

if ($id_group) {
    html_print_input_hidden('update_group', 1);
    html_print_input_hidden('id_group', $id_group);
    $actionButtonTitle = __('Update');
    $actionButtonName = 'updbutton';
} else {
    $actionButtonTitle = __('Create');
    $actionButtonName = 'crtbutton';
    html_print_input_hidden('create_group', 1);
}

$actionButtons = [];

$actionButtons[] = html_print_submit_button(
    $actionButtonTitle,
    $actionButtonName,
    false,
    ['icon' => 'wand'],
    true
);

$actionButtons[] = html_print_go_back_button(
    ui_get_full_url('index.php?sec=gmodules&sec2=godmode/groups/modu_group_list'),
    ['button_class' => ''],
    true
);

html_print_action_buttons(
    implode('', $actionButtons),
    ['type' => 'form_action']
);
echo '</form>';
