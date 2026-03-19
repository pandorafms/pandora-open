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

require_once $config['homedir'].'/include/functions_event_responses.php';

check_login();

if (! check_acl($config['id_user'], 0, 'PM')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Group Management'
    );
    include 'general/noaccess.php';
    return;
}

$event_responses = event_responses_get_responses();

if (empty($event_responses)) {
    ui_print_info_message(['no_close' => true, 'message' => __('No responses found') ]);
    $event_responses = [];
    return;
}

$table = new stdClass();
$table->class = 'info_table';
$table->styleTable = 'margin: 10px 10px 0';
$table->cellpadding = 0;
$table->cellspacing = 0;

$table->size = [];
$table->size[0] = '200px';
$table->size[2] = '100px';
$table->size[3] = '70px';

$table->style[2] = 'text-align:left;';

$table->head[0] = __('Name');
$table->head[1] = __('Description');
$table->head[2] = __('Group');
$table->head[3] = __('Actions');

$table->data = [];

foreach ($event_responses as $response) {
    if (!check_acl_restricted_all($config['id_user'], $response['id_group'], 'PM')) {
        continue;
    }

    if ((isset($config['ITSM_enabled']) === false || (bool) $config['ITSM_enabled'] === false)
        && $response['name'] === 'Create&#x20;ticket&#x20;in&#x20;Pandora&#x20;ITSM&#x20;from&#x20;event'
    ) {
        continue;
    }

    $data = [];
    $data[0] = '<a href="index.php?sec=geventos&sec2=godmode/events/events&section=responses&mode=editor&id_response='.$response['id'].'&amp;pure='.$config['pure'].'">'.$response['name'].'</a>';
    $data[1] = $response['description'];
    $data[2] = ui_print_group_icon($response['id_group'], true);
    $table->cellclass[][3] = 'table_action_buttons';
    $data[3] = html_print_anchor(
        [
            'href'    => 'index.php?sec=geventos&sec2=godmode/events/events&section=responses&action=delete_response&id_response='.$response['id'].'&amp;pure='.$config['pure'],
            'content' => html_print_image(
                'images/delete.svg',
                true,
                [
                    'title' => __('Delete'),
                    'class' => 'invert_filter main_menu_icon',
                ]
            ),
        ],
        true
    );

    $data[3] .= html_print_anchor(
        [
            'href'    => 'index.php?sec=geventos&sec2=godmode/events/events&section=responses&mode=editor&id_response='.$response['id'].'&amp;pure='.$config['pure'],
            'content' => html_print_image(
                'images/edit.svg',
                true,
                [
                    'title' => __('Edit'),
                    'class' => 'invert_filter main_menu_icon',
                ]
            ),
        ],
        true
    );
    $table->data[] = $data;
}

html_print_table($table);


echo '<form method="post" action="index.php?sec=geventos&sec2=godmode/events/events&section=responses&mode=editor&amp;pure='.$config['pure'].'">';
html_print_action_buttons(
    html_print_submit_button(
        __('Create response'),
        'create_response_button',
        false,
        ['icon' => 'wand'],
        true
    ),
    ['type' => 'form_action']
);
echo '</form>';
