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
require_once $config['homedir'].'/include/functions_users.php';
require_once $config['homedir'].'/include/functions_reporting.php';

$searchAgents = get_parameter('search_agents', 0);
$stringSearchSQL = get_parameter('stringSearchSQL');
$order = get_datatable_order(true);
if (empty($order)) {
    $order = [];
}

$totalAgents = 0;
switch ($order['field']) {
    case 'comentarios':
        $order['field'] = 't1.comentarios';
    break;

    case 'os':
        $order['field'] = 't1.id_os';
    break;

    case 'interval':
        $order['field'] = 't1.intervalo';
    break;

    case 'group_icon':
        $order['field'] = 't1.id_grupo';
    break;

    case 'module':
        $order['field'] = 'id_agente';
    break;

    case 'status':
        $order['field'] = 'id_agente';
    break;

    case 'alert':
        $order['field'] = 'id_agente';
    break;

    case 'last_contact':
        $order['field'] = 't1.ultimo_contacto';
    break;

    case 'agent':
    default:
        $order['field'] = 't1.alias';
    break;
}

$agents = false;
if ($searchAgents) {
    $userGroups = users_get_groups($config['id_user'], 'AR', false);
    $id_userGroups = array_keys($userGroups);

    $stringSearchSQL = str_replace('&amp;', '&', $stringSearchSQL);
    $stringSearchSQL = str_replace('&#92;', '\\', $stringSearchSQL);
    $sql = "SELECT DISTINCT taddress_agent.id_agent FROM taddress
		INNER JOIN taddress_agent ON
		taddress.id_a = taddress_agent.id_a
		WHERE LOWER(REPLACE(taddress.ip, '&#x20;', ' ')) LIKE LOWER('$stringSearchSQL')";

        $id = db_get_all_rows_sql($sql);
    if ($id != '') {
        $aux = $id[0]['id_agent'];
        $search_sql = " LOWER(REPLACE(t1.nombre, '&#x20;', ' ')) LIKE LOWER('".$stringSearchSQL."') OR
            LOWER(REPLACE(t2.nombre, '&#x20;', ' ')) LIKE LOWER('".$stringSearchSQL."') OR
            LOWER(REPLACE(t1.alias, '&#x20;', ' ')) LIKE LOWER('".$stringSearchSQL."') OR
            LOWER(REPLACE(t1.comentarios, '&#x20;', ' ')) LIKE LOWER('".$stringSearchSQL."') OR
            t1.id_agente =".$aux;

        $idCount = count($id);

        if ($idCount >= 2) {
            for ($i = 1; $i < $idCount; $i++) {
                $aux = $id[$i]['id_agent'];
                $search_sql .= " OR t1.id_agente = $aux";
            }
        }
    } else {
        $search_sql = " LOWER(REPLACE(t1.nombre, '&#x20;', ' ')) LIKE LOWER('".$stringSearchSQL."') OR
            LOWER(REPLACE(t2.nombre, '&#x20;', ' ')) LIKE LOWER('".$stringSearchSQL."') OR
            LOWER(REPLACE(t1.direccion, '&#x20;', ' ')) LIKE LOWER('".$stringSearchSQL."') OR
            LOWER(REPLACE(t1.comentarios, '&#x20;', ' ')) LIKE LOWER('".$stringSearchSQL."') OR
            LOWER(REPLACE(t1.alias, '&#x20;', ' ')) LIKE LOWER('".$stringSearchSQL."')";
    }

    $sql = "
        FROM tagente t1 LEFT JOIN tagent_secondary_group tasg
            ON t1.id_agente = tasg.id_agent
			INNER JOIN tgrupo t2
				ON t2.id_grupo = t1.id_grupo
		WHERE (
				1 = (
					SELECT is_admin
					FROM tusuario
					WHERE id_user = '".$config['id_user']."'
				)
				OR (
                    t1.id_grupo IN (".implode(',', $id_userGroups).')
                    OR tasg.id_group IN ('.implode(',', $id_userGroups).")
                )
                OR 0 IN (
					SELECT id_grupo
					FROM tusuario_perfil
					WHERE id_usuario = '".$config['id_user']."'
						AND id_perfil IN (
							SELECT id_perfil
							FROM tperfil WHERE agent_view = 1
						)
					)
			)
			AND (
				".$search_sql.'
			)
    ';

    $select = 'SELECT DISTINCT(t1.id_agente), t1.ultimo_contacto, t1.nombre, t1.comentarios, t1.id_os, t1.intervalo, t1.id_grupo, t1.disabled, t1.alias, t1.quiet';
    if (is_array($order)) {
        // Datatables offset, limit.
        $start = get_parameter('start', 0);
        $length = get_parameter(
            'length',
            $config['block_size']
        );
        $limit = ' ORDER BY '.$order['field'].' '.$order['direction'].' LIMIT '.$length.' OFFSET '.$start;
    }

    $query = $select.$sql;

    $query .= $limit;

    $agents = db_process_sql($query);
    if (empty($agents)) {
        $agents = [];
    }

    $count_agents_main = 0;
    if ($only_count) {
        $count_agents_main = count($agents);
    }

    if ($agents !== false) {
        $totalAgents = db_get_value_sql(
            'SELECT COUNT(DISTINCT id_agente) AS agent_count '.$sql
        );
    }

    foreach ($agents as $key => $agent) {
        $agent_quiet = '';
        if ((bool) $agent['quiet'] === true) {
            $agent_quiet = html_print_image(
                'images/dot_blue.png',
                true,
                [
                    'border' => '0',
                    'title'  => __('Quiet'),
                    'alt'    => '',
                    'class'  => 'mrgn_lft_5px',
                ]
            );
        }

        if ($agent['disabled']) {
            $agents[$key]['agent'] = '<em><a style href=index.php?sec=estado&sec2=operation/agentes/ver_agente&id_agente='.$agent['id_agente'].'
            title="'.$agent['id_agente'].'"><b><span style>'.ucfirst(strtolower($agent['alias'])).'</span></b></a>'.ui_print_help_tip(__('Disabled'), true).'</em>'.$agent_quiet;
        } else {
            $agents[$key]['agent'] = '<a style href=index.php?sec=estado&sec2=operation/agentes/ver_agente&id_agente='.$agent['id_agente'].'
            title='.$agent['nombre'].'><b><span style>'.ucfirst(strtolower($agent['alias'])).'</span></b></a>'.$agent_quiet;
        }

        $agents[$key]['os'] = ui_print_os_icon($agent['id_os'], false, true);
        $agents[$key]['interval'] = human_time_description_raw($agent['intervalo'], false, 'tiny');
        $agents[$key]['group_icon'] = ui_print_group_icon($agent['id_grupo'], true);

        $agent_info = reporting_get_agent_module_info($agent['id_agente']);
        $modulesCell = reporting_tiny_stats($agent_info, true);

        $agents[$key]['module'] = $modulesCell;
        $agents[$key]['status'] = $agent_info['status_img'];
        $agents[$key]['alert'] = $agent_info['alert_img'];

        $last_time = time_w_fixed_tz($agent['ultimo_contacto']);
        $now = get_system_time();
        $diferencia = ($now - $last_time);
        $time = ui_print_timestamp($last_time, true);
        $time_style = $time;
        if ($diferencia > ($agent['intervalo'] * 2)) {
            $time_style = '<b><span class="color_ff0">'.$time.'</span></b>';
        }

        $agents[$key]['last_contact'] = $time_style;
    }

    // RecordsTotal && recordsfiltered resultados totales.
    echo json_encode(
        [
            'data'            => ($agents ?? []),
            'recordsTotal'    => $totalAgents,
            'recordsFiltered' => $totalAgents,
        ]
    );
}
