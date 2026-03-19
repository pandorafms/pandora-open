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

require_once $config['homedir'].'/include/functions_groups.php';
require_once $config['homedir'].'/include/functions_tags.php';
require_once $config['homedir'].'/include/class/Tree.class.php';
require_once $config['homedir'].'/include/class/TreeGroup.class.php';


function groupview_plain_groups($groups)
{
    $group_result = [];
    foreach ($groups as $group) {
        $plain_child = [];
        if (!empty($group['children'])) {
            $plain_child = groupview_plain_groups($group['children']);
            unset($group['children']);
        }

        $group_result[] = $group;
        $group_result = array_merge($group_result, $plain_child);
    }

    return $group_result;
}


function groupview_get_modules_counters($groups_ids=false)
{
    if (empty($groups_ids)) {
        return [];
    }

    $groups_ids = implode(',', $groups_ids);
    $table = 'tagente';
    $table_sec = 'tagent_secondary_group';

    $fields = [
        'g',
        'SUM(module_normal) AS total_module_normal',
        'SUM(module_critical) AS total_module_critical',
        'SUM(module_warning) AS total_module_warning',
        'SUM(module_unknown) AS total_module_unknown',
        'SUM(module_not_init) AS total_module_not_init',
        'SUM(module_alerts) AS total_module_alerts',
        'SUM(module_total) AS total_module',
    ];

    $fields_impl = implode(',', $fields);
    $sql = "SELECT $fields_impl FROM
	(
		SELECT SUM(ta.normal_count) AS module_normal,
			SUM(ta.critical_count) AS module_critical,
			SUM(ta.warning_count) AS module_warning,
			SUM(ta.unknown_count) AS module_unknown,
			SUM(ta.notinit_count) AS module_not_init,
			SUM(ta.fired_count) AS module_alerts,
			SUM(ta.total_count) AS module_total,
			ta.id_grupo AS g
		FROM $table ta
		WHERE ta.id_grupo IN ($groups_ids)
		AND ta.disabled = 0
		GROUP BY ta.id_grupo
		UNION ALL
		SELECT SUM(ta.normal_count) AS module_normal,
			SUM(ta.critical_count) AS module_critical,
			SUM(ta.warning_count) AS module_warning,
			SUM(ta.unknown_count) AS module_unknown,
			SUM(ta.notinit_count) AS module_not_init,
			SUM(ta.fired_count) AS module_alerts,
			SUM(ta.total_count) AS module_total,
			tasg.id_group AS g
		FROM $table ta
		INNER JOIN $table_sec tasg
			ON ta.id_agente = tasg.id_agent
		WHERE tasg.id_group IN ($groups_ids)
        AND ta.disabled = 0
		GROUP BY tasg.id_group
	) x GROUP BY g";
    $data = db_get_all_rows_sql($sql);
    return $data;
}


function groupview_get_all_counters($tree_group)
{
    $all_name = __('All');
    $group_acl = $tree_group->getGroupAclCondition();
    $table = 'tagente';
    $table_sec = 'tagent_secondary_group';
    $sql = "SELECT SUM(ta.critical_count) AS _monitors_critical_,
			SUM(ta.warning_count) AS _monitors_warning_,
			SUM(ta.unknown_count) AS _monitors_unknown_,
			SUM(ta.notinit_count) AS _monitors_not_init_,
			SUM(ta.normal_count) AS _monitors_ok_,
			SUM(ta.total_count) AS _monitor_checks_,
			SUM(ta.fired_count) AS _monitors_alerts_fired_,
			SUM(IF(ta.critical_count > 0, 1, 0)) AS _agents_critical_,
			SUM(IF(ta.critical_count = 0 AND ta.warning_count > 0, 1, 0)) AS _agents_warning_,
			SUM(IF(ta.critical_count = 0 AND ta.warning_count = 0 AND ta.unknown_count > 0, 1, 0)) AS _agents_unknown_,
			SUM(IF(ta.total_count = ta.notinit_count, 1, 0)) AS _agents_not_init_,
			SUM(IF(ta.total_count = ta.normal_count AND ta.total_count <> ta.notinit_count, 1, 0)) AS _agents_ok_,
			COUNT(ta.id_agente) AS _total_agents_,
			'$all_name' AS _name_,
			0 AS _id_,
			'' AS _icon_
		FROM $table ta
		WHERE ta.disabled = 0
			AND ta.id_agente IN (
				SELECT ta.id_agente FROM $table ta
				LEFT JOIN $table_sec tasg
					ON ta.id_agente = tasg.id_agent
				WHERE ta.disabled = 0 
					$group_acl
				GROUP BY ta.id_agente
			)
	";
    $data = db_get_row_sql($sql);
    $data['_monitor_not_normal_'] = ($data['_monitor_checks_'] - $data['_monitors_ok_']);
    return $data;
}


function groupview_get_groups_list($id_user=false, $access='AR', $is_not_paginated=false)
{
    global $config;
    if ($id_user == false) {
        $id_user = $config['id_user'];
    }

    $tree_group = new TreeGroup('group', 'group');
    $tree_group->setPropagateCounters(false);
    $tree_group->setFilter(
        [
            'searchAgent'           => '',
            'statusAgent'           => AGENT_STATUS_ALL,
            'searchModule'          => '',
            'statusModule'          => -1,
            'groupID'               => 0,
            'tagID'                 => 0,
            'show_not_init_agents'  => 1,
            'show_not_init_modules' => 1,
        ]
    );
    $info = $tree_group->getArray();
    $info = groupview_plain_groups($info);
    $counter = count($info);

    $offset = get_parameter('offset', 0);
    $groups_view = $is_not_paginated ? $info : array_slice($info, $offset, $config['block_size']);
    $agents_counters = array_reduce(
        $groups_view,
        function ($carry, $item) {
            $carry[$item['id']] = $item;
            return $carry;
        },
        []
    );

    $modules_counters = groupview_get_modules_counters(array_keys($agents_counters));
    $modules_counters = array_reduce(
        $modules_counters,
        function ($carry, $item) {
            $carry[$item['g']] = $item;
            return $carry;
        },
        []
    );

    $list = [];

    foreach ($agents_counters as $id_group => $agent_counter) {
        $list[$id_group]['_name_'] = $agent_counter['name'];
        $list[$id_group]['_id_'] = $agent_counter['id'];
        $list[$id_group]['_iconImg_'] = $agent_counter['icon'];

        $list[$id_group]['_agents_critical_'] = $agent_counter['counters']['critical'];
        $list[$id_group]['_agents_warning_'] = $agent_counter['counters']['warning'];
        $list[$id_group]['_agents_unknown_'] = $agent_counter['counters']['unknown'];
        $list[$id_group]['_agents_not_init_'] = $agent_counter['counters']['not_init'];
        $list[$id_group]['_agents_ok_'] = $agent_counter['counters']['ok'];
        $list[$id_group]['_total_agents_'] = $agent_counter['counters']['total'];

        $list[$id_group]['_monitors_critical_'] = (int) $modules_counters[$id_group]['total_module_critical'];
        $list[$id_group]['_monitors_warning_'] = (int) $modules_counters[$id_group]['total_module_warning'];
        $list[$id_group]['_monitors_unknown_'] = (int) $modules_counters[$id_group]['total_module_unknown'];
        $list[$id_group]['_monitors_not_init_'] = (int) $modules_counters[$id_group]['total_module_not_init'];
        $list[$id_group]['_monitors_ok_'] = (int) $modules_counters[$id_group]['total_module_normal'];
        $list[$id_group]['_monitor_checks_'] = (int) $modules_counters[$id_group]['total_module'];
        $list[$id_group]['_monitor_not_normal_'] = ($modules_counters[$id_group]['total_module'] - $modules_counters[$id_group]['total_module_normal']);
        $list[$id_group]['_monitors_alerts_fired_'] = (int) $modules_counters[$id_group]['total_module_alerts'];
    }

    array_unshift($list, groupview_get_all_counters($tree_group));
    return [
        'groups'  => $list,
        'counter' => $counter,
    ];
}


function get_recursive_groups_heatmap($parent_group, $acl)
{
    if ($parent_group['counter'] > 0) {
        foreach ($parent_group['groups'] as $group_key => $group_value) {
            if ((int) $group_value['_id_'] === 0) {
                continue;
            }

            $childrens = groups_get_children($group_value['_id_'], true, $acl, false);
            if (empty($childrens) === false) {
                foreach ($childrens as $children) {
                    $children_status = groups_get_status($children['id_grupo']);
                    $parent_group['groups'][$group_key]['_monitor_checks_']++;
                    switch ($children_status) {
                        case AGENT_STATUS_CRITICAL:
                            $parent_group['groups'][$group_key]['_monitors_critical_']++;
                        break;

                        case AGENT_STATUS_WARNING:
                            $parent_group['groups'][$group_key]['_monitors_warning_']++;
                        break;

                        case AGENT_STATUS_UNKNOWN:
                            $parent_group['groups'][$group_key]['_monitors_unknown_']++;
                        break;

                        case AGENT_STATUS_NORMAL:
                        default:
                            $parent_group['groups'][$group_key]['_monitors_ok_']++;
                        break;
                    }
                }
            }
        }
    }

    return $parent_group;
}
