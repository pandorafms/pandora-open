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

$mode = get_parameter('mode', 'list');
$action = get_parameter('action');

switch ($action) {
    case 'create_response':
        $values = [];
        $values['name'] = get_parameter('name');
        $values['description'] = get_parameter('description');
        $values['target'] = get_parameter('target');
        $values['type'] = get_parameter('type');
        $values['id_group'] = get_parameter('id_group', 0);
        $values['modal_width'] = get_parameter('modal_width');
        $values['modal_height'] = get_parameter('modal_height');
        $values['new_window'] = get_parameter('new_window');
        $values['params'] = get_parameter('params');
        $values['display_command'] = get_parameter('display_command');
        $values['server_to_exec'] = get_parameter('server_to_exec');
        $values['command_timeout'] = get_parameter('command_timeout', 90);

        $result = event_responses_create_response($values);

        if ($result) {
            ui_print_success_message(__('Response added succesfully'));
        } else {
            ui_print_error_message(__('Response cannot be added'));
        }
    break;

    case 'update_response':
        $values = [];
        $values['name'] = get_parameter('name');
        $values['description'] = get_parameter('description');
        $values['target'] = get_parameter('target');
        $values['type'] = get_parameter('type');
        $values['id_group'] = get_parameter('id_group', 0);
        $values['modal_width'] = get_parameter('modal_width');
        $values['modal_height'] = get_parameter('modal_height');
        $values['new_window'] = get_parameter('new_window');
        $values['params'] = get_parameter('params');
        $values['display_command'] = get_parameter('display_command');
        $values['server_to_exec'] = get_parameter('server_to_exec');
        $response_id = get_parameter('id_response', 0);
        $values['command_timeout'] = get_parameter('command_timeout', '90');


        $result = event_responses_update_response($response_id, $values);

        if ($result) {
            ui_print_success_message(__('Response updated succesfully'));
        } else {
            ui_print_error_message(__('Response cannot be updated'));
        }
    break;

    case 'delete_response':
        $response_id = get_parameter('id_response', 0);

        $result = db_process_sql_delete('tevent_response', ['id' => $response_id]);

        if ($result) {
            ui_print_success_message(__('Response deleted succesfully'));
        } else {
            ui_print_error_message(__('Response cannot be deleted'));
        }
    break;
}

switch ($mode) {
    case 'list':
        include 'event_responses.list.php';
    break;

    case 'editor':
        include 'event_responses.editor.php';
    break;
}
