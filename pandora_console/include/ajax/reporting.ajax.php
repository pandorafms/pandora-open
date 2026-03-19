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
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
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

// Login check
check_login();

if (! check_acl($config['id_user'], 0, 'RW')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access report builder'
    );
    include 'general/noaccess.php';
    exit;
}

$get_log_agents = (bool) get_parameter('get_log_agents', 0);
$get_agents = (bool) get_parameter('get_agents', 0);
$agents_id = get_parameter('id_agents', []);
$source = get_parameter('source', 0);
$group = get_parameter('group', 0);
$delete_sla_item = get_parameter('delete_sla_item', 0);
$delete_general_item = get_parameter('delete_general_item', 0);
$get_custom_sql = get_parameter('get_custom_sql', 0);
$add_sla = get_parameter('add_sla', 0);
$add_general = get_parameter('add_general', 0);
$id = get_parameter('id', 0);
$truncate_text = get_parameter('truncate_text', 0);
$change_custom_fields_macros_report = (bool) get_parameter(
    'change_custom_fields_macros_report',
    0
);

if ($delete_sla_item) {
    $result = db_process_sql_delete('treport_content_sla_combined', ['id' => (int) $id]);

    $data['correct'] = 1;
    if ($result === false) {
        $data['correct'] = 0;
    }

    echo json_encode($data);
    return;
}

if ($delete_general_item) {
    $result = db_process_sql_delete('treport_content_item', ['id' => (int) $id]);

    $data['correct'] = 1;
    if ($result === false) {
        $data['correct'] = 0;
    }

    echo json_encode($data);
    return;
}

if ($add_sla) {
    $id_module = get_parameter('id_module', 0);
    $sla_limit = get_parameter('sla_limit', 0);
    $sla_max = get_parameter('sla_max', 0);
    $sla_min = get_parameter('sla_min', 0);
    $server_id = (int) get_parameter('server_id', 0);
    $id_module_failover = (int) get_parameter('id_module_failover', 0);

    $id_service = (int) get_parameter('id_service');
    if (empty($id_module) && !empty($id_service)) {
        $id_module = $id_service;
    }

    if (empty($connection)) {
        $connection = [];
        $connection['server_name'] = '';
    }

    $result = db_process_sql_insert(
        'treport_content_sla_combined',
        [
            'id_report_content'        => $id,
            'id_agent_module'          => $id_module,
            'id_agent_module_failover' => $id_module_failover,
            'sla_max'                  => $sla_max,
            'sla_min'                  => $sla_min,
            'sla_limit'                => $sla_limit,
            'server_name'              => $connection['server_name'],
        ]
    );

    if ($result === false) {
        $data['correct'] = 0;
    } else {
        $data['correct'] = 1;
        $data['id'] = $result;
    }

    echo json_encode($data);
    return;
}

if ($add_general) {
    $id_module = get_parameter('id_module', 0);
    $id_server = (int) get_parameter('id_server', 0);
    $operation = get_parameter('operation', '');
    $id_module_failover = (int) get_parameter('id_module_failover', 0);

    if (empty($connection)) {
        $connection = [];
        $connection['server_name'] = '';
    }

    $result = db_process_sql_insert(
        'treport_content_item',
        [
            'id_report_content'        => $id,
            'id_agent_module'          => $id_module,
            'server_name'              => $connection['server_name'],
            'operation'                => $operation,
            'id_agent_module_failover' => $id_module_failover,
        ]
    );

    if ($result === false) {
        $data['correct'] = 0;
    } else {
        $data['correct'] = 1;
        $data['id'] = $result;
    }

    echo json_encode($data);
    return;
}

if ($get_custom_sql) {
    switch ($config['dbtype']) {
        case 'mysql':
            $sql = db_get_value_filter('`sql`', 'treport_custom_sql', ['id' => $id]);
        break;

        case 'postgresql':
            $sql = db_get_value_filter('"sql"', 'treport_custom_sql', ['id' => $id]);
        break;

        case 'oracle':
            $sql = db_get_value_filter('sql', 'treport_custom_sql', ['id' => $id]);
        break;
    }

    if ($sql === false) {
        $data['correct'] = 0;
    } else {
        $data['correct'] = 1;
        $data['sql'] = $sql;
    }

    echo json_encode($data);
    return;
}

if ($truncate_text) {
    $text = get_parameter('text', '');
    return ui_print_truncate_text($text, GENERIC_SIZE_TEXT, true, false);
}

if ($change_custom_fields_macros_report === true) {
    include_once $config['homedir'].'/include/functions_reports.php';
    $macro_type = get_parameter('macro_type', '');
    $macro_id = get_parameter('macro_id', 0);

    $macro = [
        'name'  => '',
        'type'  => $macro_type,
        'value' => '',
    ];
    $custom_fields = custom_fields_macros_report($macro, $macro_id);
    $custom_field_draw = '';
    if (empty($custom_fields) === false) {
        foreach ($custom_fields as $key => $value) {
            $custom_field_draw .= $value;
        }
    }

    echo $custom_field_draw;
    return;
}

if ($get_agents === true) {
    $agents_id = str_replace('&quot;', '"', $agents_id);

    try {
        $agents_id = json_decode($agents_id, true);
    } catch (Exception $e) {
        $data['correct'] = 0;
        echo json_encode($data);
        return;
    }

    $agents = agents_get_agents_selected($group);

    if (isset($agents) && is_array($agents)) {
        foreach ($agents as $key => $value) {
            $select_agents[$key] = $value;
        }
    }

    if ((empty($select_agents)) || $select_agents == -1) {
        $agents = [];
    }

    $agents_selected = [];
    if (is_array($agents_id) === true || is_object($agents_id) === true) {
        foreach ($select_agents as $key => $a) {
            if (in_array((string) $key, $agents_id) === true) {
                $agents_selected[$key] = $key;
            }
        }
    }

    $data['select_agents'] = $select_agents;
    $data['agents_selected'] = $agents_selected;

    $data['correct'] = 1;

    if ($result === false) {
        $data['correct'] = 0;
    }

    echo json_encode($data);

    return;
}

if ($get_log_agents === true) {
    $agents_id = str_replace('&quot;', '', $agents_id);

    try {
        $agents_id = json_decode($agents_id, true);
    } catch (Exception $e) {
        $data['correct'] = 0;
        echo json_encode($data);
        return;
    }

    if ($source) {
        $sql_log_report = 'SELECT id_agente, alias
                FROM tagente';
    } else {
        $sql_log_report = 'SELECT id_agente, alias
                FROM tagente';
    }

    $all_agent_log = db_get_all_rows_sql($sql_log_report);

    if (isset($all_agent_log) && is_array($all_agent_log)) {
        foreach ($all_agent_log as $key => $value) {
            $select_agents[$value['id_agente']] = $value['alias'];
        }
    }

    if ((empty($select_agents)) || $select_agents == -1) {
        $agents = [];
    }

    $agents_selected = [];
    if (is_array($agents_id) === true || is_object($agents_id) === true) {
        foreach ($select_agents as $key => $a) {
            if (in_array((string) $key, $agents_id)) {
                $agents_selected[$key] = $key;
            }
        }
    }

    $data['select_agents'] = $select_agents;
    $data['agents_selected'] = $agents_selected;

    $data['correct'] = 1;

    if ($result === false) {
        $data['correct'] = 0;
    }

    echo json_encode($data);

    return;
}
