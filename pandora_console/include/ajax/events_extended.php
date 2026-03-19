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

require_once 'include/functions_events.php';
global $config;

// Check ACLs.
if (is_user_admin($config['id_user']) === true) {
    // Do nothing if you're admin, you get full access.
    $allowed = true;
} else if ($config['id_user'] == $event['owner_user']) {
    // Do nothing if you're the owner user, you get access.
    $allowed = true;
} else if ($event['id_grupo'] == 0) {
    // If the event has access to all groups, you get access.
    $allowed = true;
} else {
    // Get your groups.
    $groups = users_get_groups($config['id_user'], 'ER');

    if (in_array($event['id_grupo'], array_keys($groups))) {
        // If event group is among the groups of the user, you get access.
        $__ignored_line = true;
    } else {
        // If all the access types fail, abort.
        $allowed = false;
    }
}

if ($allowed === false) {
    echo 'Access denied';
    exit;
}

$id_event = get_parameter('id_event', null);
$get_extended_info = get_parameter('get_extended_info', 0);


if ($get_extended_info == 1) {
    if (isset($id_event) === false) {
        echo 'Internal error. Invalid event.';
        exit;
    }

    $extended_info = events_get_extended_events($id_event);

    $table = new StdClass();
    //
    // Details.
    //
    $table->width = '100%';
    $table->data = [];
    $table->head = [];
    $table->cellspacing = 2;
    $table->cellpadding = 2;
    $table->class = 'table_modal_alternate';

    $output = [];
    $output[] = '<b>'.__('Timestamp').'</b>';
    $output[] = '<b>'.__('Description').'</b>';
    $table->data[] = $output;

    foreach ($extended_info as $data) {
        $output = [];
        $output[] = date('Y/m/d H:i:s', $data['utimestamp']);
        $output[] = io_safe_output($data['description']);
        $table->data[] = $output;
    }

    html_print_table($table);
}
