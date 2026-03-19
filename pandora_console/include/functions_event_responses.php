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


/**
 * @package    Include
 * @subpackage Event Responses
 */


/**
 * Get all event responses with all values that user can access
 *
 * @return array With all table values
 */
function event_responses_get_responses()
{
    global $config;
    $filter = [];

    // Apply a filter if user cannot see all groups
    if (!users_can_manage_group_all()) {
        $id_groups = array_keys(users_get_groups(false, 'PM'));
        $filter = ['id_group' => $id_groups];
    }

    return db_get_all_rows_filter('tevent_response', $filter);
}


/**
 * Validate the responses data to store in database
 *
 * @param array (by reference) Array with values to validate and modify
 */
function event_responses_validate_data(&$values)
{
    if ($values['type'] != 'command') {
        $values['server_to_exec'] = 0;
    }

    if ($values['new_window'] == 1) {
        $values['modal_width'] = 0;
        $values['modal_height'] = 0;
    }
}


/**
 * Create an event response
 *
 * @param array With all event response data
 *
 * @return True if successful insertion
 */
function event_responses_create_response($values)
{
    event_responses_validate_data($values);
    return db_process_sql_insert('tevent_response', $values);
}


/**
 * Update an event response
 *
 * @param array With all event response data
 *
 * @return True if successful insertion
 */
function event_responses_update_response($response_id, $values)
{
    event_responses_validate_data($values);
    return db_process_sql_update(
        'tevent_response',
        $values,
        ['id' => $response_id]
    );
}
