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

if (is_ajax()) {
    if (! check_acl($config['id_user'], 0, 'ER')) {
        db_pandora_audit(
            AUDIT_LOG_ACL_VIOLATION,
            'Trying to access event viewer'
        );
        return;
    }

    $get_last_events = (bool) get_parameter('get_last_events');
    if ($get_last_events) {
        include_once 'include/functions_io.php';
        include_once 'include/functions_tags.php';

        $limit = (int) get_parameter('limit', 5);

        $tags_condition = tags_get_acl_tags($config['id_user'], 0, 'ER', 'event_condition', 'AND');

        $filter = "estado <> 1 $tags_condition";

        $sql = sprintf(
            'SELECT id_agente, evento, utimestamp
						FROM tevento
						LEFT JOIN tagent_secondary_group 
						ON tevento.id_agente = tagent_secondary_group.id_agent
						WHERE %s
						ORDER BY utimestamp DESC LIMIT %d',
            $filter,
            $limit
        );

        $result = db_get_all_rows_sql($sql);

        $events = [];
        if (! empty($result)) {
            foreach ($result as $key => $value) {
                $event = [];
                $event['agent'] = (empty($value['id_agente'])) ? 'System' : agents_get_name($value['id_agente']);
                $event['text'] = io_safe_output($value['evento']);
                $event['date'] = date(io_safe_output($config['date_format']), $value['utimestamp']);
                $events[] = $event;
            }
        } else {
            sleep(5);
        }

        echo json_encode($events);
        return;
    }

    return;
}
