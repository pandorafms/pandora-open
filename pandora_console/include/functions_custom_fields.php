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
 * Returns custom field all or 1.
 *
 * @param integer custom_field_id id.
 * @param bool Prepare for select or return all rows.
 *
 * @return array custom fields data.
 */
function get_custom_fields($custom_field_id=false, $select=true, $display_on_front=false)
{
    $fields = ($select) ? ' tcf.id_field, tcf.name ' : ' tcf.* ';

    $where = ($custom_field_id) ? ' WHERE tcf.id_field ='.$custom_field_id : ' WHERE 1=1';

    $display = ($display_on_front) ? ' AND tcf.display_on_front = 1' : '';

    $result_array = [];

        $sql = sprintf(
            'SELECT
			%s
			FROM tagent_custom_fields tcf
			%s
			%s
			',
            $fields,
            $where,
            $display
        );

        $result = db_get_all_rows_sql($sql);

        if (isset($result) && is_array($result)) {
            foreach ($result as $key => $value) {
                if ($select) {
                    $result_array[$value['name']] = $value['name'];
                } else {
                    $result_array[$value['name']] = $value;
                }
            }
        }
    

    return $result_array;
}


/**
 * Returns custom field data.
 *
 * @param integer $custom_field_name Custom_field_id id.
 *
 * @return array custom fields data.
 */
function get_custom_fields_data($custom_field_name)
{
    if (!isset($custom_field_name)) {
        return false;
    }

        $sql = sprintf(
            "SELECT tcf.id_field, tcf.name, tcd.description
			FROM tagent_custom_fields tcf
			INNER JOIN tagent_custom_data tcd
				ON tcf.id_field = tcd.id_field
			INNER JOIN tagente ta
				ON tcd.id_agent = ta.id_agente
			WHERE tcd.description <> ''
				AND tcf.name = '%s'
			GROUP BY tcf.id_field, tcd.description",
            $custom_field_name
        );

        $result = db_get_all_rows_sql($sql);
        if (isset($result) && is_array($result)) {
            foreach ($result as $k => $v) {
                $array_result[$v['description']] = $v['description'];
            }
        }
    

    return $array_result;
}


/**
 * Function for custom field view return all conter for agents
 *
 * @param  array $filters Params for search.
 * @return void False or array.
 */
function agent_counters_custom_fields($filters)
{
    // Filter by status agent.
    $and_status = '';

    $agent_state_normal = 0;
    $agent_state_critical = 0;
    $agent_state_warning = 0;
    $agent_state_unknown = 0;
    $agent_state_notinit = 0;
    $agent_state_total = 0;
    if (is_array($filters['id_status'])) {
        if (!in_array(-1, $filters['id_status'])) {
            if (!in_array(AGENT_MODULE_STATUS_NOT_NORMAL, $filters['id_status'])) {
                if (count($filters['id_status']) > 0) {
                    $and_status = ' AND ( ';
                    foreach ($filters['id_status'] as $key => $value) {
                        $and_status .= ($key != 0) ? ' OR ' : ' ';
                        switch ($value) {
                            default:
                            case AGENT_STATUS_NORMAL:
                                $agent_state_normal = agents_get_status_clause(AGENT_STATUS_NORMAL);
                                $and_status .= agents_get_status_clause(AGENT_STATUS_NORMAL);
                            break;
                            case AGENT_STATUS_CRITICAL:
                                $and_status .= agents_get_status_clause(AGENT_STATUS_CRITICAL);
                                $agent_state_critical = agents_get_status_clause(AGENT_STATUS_CRITICAL);
                            break;

                            case AGENT_STATUS_WARNING:
                                $and_status .= agents_get_status_clause(AGENT_STATUS_WARNING);
                                $agent_state_warning = agents_get_status_clause(AGENT_STATUS_WARNING);
                            break;

                            case AGENT_STATUS_UNKNOWN:
                                $and_status .= agents_get_status_clause(AGENT_STATUS_UNKNOWN);
                                $agent_state_unknown = agents_get_status_clause(AGENT_STATUS_UNKNOWN);
                            break;

                            case AGENT_STATUS_NOT_INIT:
                                $and_status .= agents_get_status_clause(AGENT_STATUS_NOT_INIT);
                                $agent_state_notinit = agents_get_status_clause(AGENT_STATUS_NOT_INIT);
                            break;
                        }
                    }

                    $and_status .= ' ) ';
                    $agent_state_total = agents_get_status_clause(AGENT_STATUS_ALL);
                }
            } else {
                $and_status = ' AND (
					( ta.critical_count > 0 )
					OR ( ta.critical_count = 0 AND ta.warning_count > 0 )
					OR ( ta.critical_count = 0 AND ta.warning_count = 0 AND ta.unknown_count > 0 )
					OR ( ta.total_count = ta.notinit_count )
				) ';

                $agent_state_critical = agents_get_status_clause(AGENT_STATUS_CRITICAL);
                $agent_state_warning = agents_get_status_clause(AGENT_STATUS_WARNING);
                $agent_state_unknown = agents_get_status_clause(AGENT_STATUS_UNKNOWN);
                $agent_state_notinit = agents_get_status_clause(AGENT_STATUS_NOT_INIT);
                $agent_state_total = agents_get_status_clause(AGENT_STATUS_ALL);
            }
        } else {
            $agent_state_normal = agents_get_status_clause(AGENT_STATUS_NORMAL);
            $agent_state_critical = agents_get_status_clause(AGENT_STATUS_CRITICAL);
            $agent_state_warning = agents_get_status_clause(AGENT_STATUS_WARNING);
            $agent_state_unknown = agents_get_status_clause(AGENT_STATUS_UNKNOWN);
            $agent_state_notinit = agents_get_status_clause(AGENT_STATUS_NOT_INIT);
            $agent_state_total = agents_get_status_clause(AGENT_STATUS_ALL);
        }
    }

    if (isset($groups_and) === false) {
        $groups_and = '';
    }

    // Filter by status module.
    $empty_agents_count = "UNION ALL
		SELECT ta.id_agente,
			0 AS c_m_total,
			0 AS mm_normal,
			0 AS mm_critical,
			0 AS mm_warning,
			0 AS mm_unknown,
			0 AS mm_not_init,
			0 AS mm_total
		FROM tagente ta
		LEFT JOIN tagent_secondary_group tasg
			ON ta.id_agente = tasg.id_agent
		WHERE ta.disabled = 0
			AND ta.total_count = 0
		$groups_and";

    $and_module_status = '';
    if (is_array($filters['module_status'])) {
        if (!in_array(-1, $filters['module_status'])) {
            $empty_agents_count = '';
            if (!in_array(AGENT_MODULE_STATUS_NOT_NORMAL, $filters['module_status'])) {
                if (count($filters['module_status']) > 0) {
                    $and_module_status = ' AND ( ';
                    foreach ($filters['module_status'] as $key => $value) {
                        $and_module_status .= ($key != 0) ? ' OR (' : ' (';
                        switch ($value) {
                            default:
                            case AGENT_STATUS_NORMAL:
                                $and_module_status .= ' tae.estado = 0 OR tae.estado = 300 ) ';
                            break;
                            case AGENT_STATUS_CRITICAL:
                                $and_module_status .= ' tae.estado = 1 OR tae.estado = 100 ) ';
                            break;

                            case AGENT_STATUS_WARNING:
                                $and_module_status .= ' tae.estado = 2 OR tae.estado = 200 ) ';
                            break;

                            case AGENT_STATUS_UNKNOWN:
                                $and_module_status .= ' tae.estado = 3 ) ';
                            break;

                            case AGENT_STATUS_NOT_INIT:
                                $and_module_status .= ' tae.estado = 4 OR tae.estado = 5 ) ';
                            break;
                        }
                    }

                    $and_module_status .= ' ) ';
                }
            } else {
                // Not normal.
                $and_module_status = 'AND tae.estado <> 0 AND tae.estado <> 300 ';
                $empty_agents_count = '';
            }
        }
    }

    // Filters module.
    if ($filters['module_search']) {
        $and_module_search = 'AND tam.nombre LIKE "%'.$filters['module_search'].'%"';
        $empty_agents_count = '';
    }

    // Filter group and check ACL groups.
    $groups_and = '';
    if (!users_can_manage_group_all('AR')) {
        if ($filters['group']) {
            $user_groups = array_keys(users_get_groups());
            $id_groups = implode(', ', $user_groups);
            $groups_and = " AND (ta.id_grupo IN ($id_groups) OR tasg.id_group IN($id_groups))";
        }
    }

    if ($filters['group']) {
        // Recursion check acl.
        if ($filters['recursion']) {
            $recursion_groups = groups_get_children_ids($filters['group'], true);
            if (!users_can_manage_group_all('AR')) {
                if (isset($user_groups) && is_array($user_groups)) {
                    $groups_intersect = array_intersect($user_groups, $recursion_groups);
                    if (isset($groups_intersect) && is_array($groups_intersect)) {
                        $groups_intersect = implode(', ', $groups_intersect);
                        $groups_and = " AND (ta.id_grupo IN ($groups_intersect) OR tasg.id_group IN($groups_intersect))";
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                $recursion_groups = implode(', ', $recursion_groups);
                $groups_and = " AND (ta.id_grupo IN ($recursion_groups) OR tasg.id_group IN($recursion_groups))";
            }
        } else {
            $groups_and = ' AND (ta.id_grupo ='.$filters['group'].' OR tasg.id_group ='.$filters['group'].')';
        }
    }

    // Filter custom data.
    $custom_data_and = '';
    if (isset($filters['id_custom_fields_data']) === true
        && is_array($filters['id_custom_fields_data']) === true
    ) {
        if (!in_array(-1, $filters['id_custom_fields_data'])) {
            $custom_data_array = implode("', '", $filters['id_custom_fields_data']);
            $custom_data_and = "AND tcd.description IN ('".$custom_data_array."')";
        }
    }

    // Filter custom name.
    $custom_field_name = $filters['id_custom_fields'];

        $result_custom_data = [];
        $data = [];

        if (isset($and_module_search) === false) {
            $and_module_search = '';
        }

        $query = sprintf(
            "SELECT tcd.description AS name_data,
                SUM(IF($agent_state_total, 1, 0)) AS a_agents,
                SUM(IF($agent_state_critical, 1, 0)) AS a_critical,
                SUM(IF($agent_state_warning, 1, 0)) AS a_warning,
                SUM(IF($agent_state_unknown, 1, 0)) AS a_unknown,
                SUM(IF($agent_state_normal, 1, 0)) AS a_normal,
                SUM(IF($agent_state_notinit, 1, 0)) AS a_not_init,
                SUM(tagent_counters.mm_normal) AS m_normal,
                SUM(tagent_counters.mm_critical) AS m_critical,
                SUM(tagent_counters.mm_warning) AS m_warning,
                SUM(tagent_counters.mm_unknown) AS m_unknown,
                SUM(tagent_counters.mm_not_init) AS m_not_init,
                SUM(tagent_counters.mm_total) AS m_total
            FROM tagent_custom_data tcd
            INNER JOIN tagent_custom_fields tcf
                ON tcd.id_field = tcf.id_field
            INNER JOIN (
                SELECT ta.id_agente,
                    ta.total_count AS c_m_total,
                    SUM( IF(tae.estado = 0, 1, 0) ) AS mm_normal,
                    SUM( IF(tae.estado = 1, 1, 0) ) AS mm_critical,
                    SUM( IF(tae.estado = 2, 1, 0) ) AS mm_warning,
                    SUM( IF(tae.estado = 3, 1, 0) ) AS mm_unknown,
                    SUM( IF(tae.estado = 4 OR tae.estado = 5, 1, 0) ) AS mm_not_init,
                    COUNT(tam.id_agente_modulo) AS mm_total
                FROM tagente ta
                LEFT JOIN tagent_secondary_group tasg
                    ON ta.id_agente = tasg.id_agent
                INNER JOIN tagente_modulo tam
                    ON ta.id_agente = tam.id_agente
                INNER JOIN tagente_estado tae
                    ON tam.id_agente = tae.id_agente
                    AND tam.id_agente_modulo = tae.id_agente_modulo
                WHERE ta.disabled = 0
                    AND tam.disabled = 0
                    %s
                    %s
                    %s
                    %s
                GROUP by ta.id_agente
                    %s
            ) AS tagent_counters
                ON tcd.id_agent = tagent_counters.id_agente
            INNER JOIN tagente ta
                ON ta.id_agente = tagent_counters.id_agente
            WHERE tcf.name = '%s'
                AND tcd.description <> ''
                %s
            GROUP BY tcd.description",
            $groups_and,
            $and_status,
            $and_module_search,
            $and_module_status,
            $empty_agents_count,
            $custom_field_name,
            $custom_data_and
        );

        $result_custom_data[] = db_get_all_rows_sql($query);

        if (isset($server_data) === false) {
            $server_data = [];
            $server_data['id'] = '';
        }

        $query_data = sprintf(
            "SELECT
                tcd.description,
                ta.id_agente,
                %d AS id_server,
                (CASE
                    WHEN ta.critical_count > 0
                        THEN 1
                    WHEN ta.critical_count = 0
                        AND ta.warning_count > 0
                        THEN 2
                    WHEN ta.critical_count = 0
                        AND ta.warning_count = 0
                        AND ta.unknown_count > 0
                        THEN 3
                    WHEN ta.critical_count = 0
                        AND ta.warning_count = 0
                        AND ta.unknown_count = 0
                        AND ta.notinit_count <> ta.total_count
                        THEN 0
                    WHEN ta.total_count = ta.notinit_count
                        THEN 5
                    ELSE 0
                END) AS `status`,
                ta.critical_count,
                ta.warning_count,
                ta.unknown_count,
                ta.notinit_count,
                ta.normal_count,
                ta.total_count
            FROM tagente ta
            LEFT JOIN tagent_secondary_group tasg
                ON ta.id_agente = tasg.id_agent
            INNER JOIN tagente_modulo tam
                ON ta.id_agente = tam.id_agente
            INNER JOIN tagente_estado tae
                ON tam.id_agente = tae.id_agente
                AND tam.id_agente_modulo = tae.id_agente_modulo
            INNER JOIN tagent_custom_data tcd
                ON tcd.id_agent = ta.id_agente
            INNER JOIN tagent_custom_fields tcf
                ON tcd.id_field = tcf.id_field
            WHERE ta.disabled = 0
                AND tcf.name = '%s'
                AND tcd.description <> ''
                AND tam.disabled = 0
                %s
                %s
                %s
                %s
                %s
                GROUP BY ta.id_agente
            ",
            $server_data['id'],
            $custom_field_name,
            $custom_data_and,
            $groups_and,
            $and_status,
            $and_module_search,
            $and_module_status
        );

        $node_result = db_get_all_rows_sql($query_data);
        ;
        if (empty($node_result)) {
            $node_result = [];
        }

        $data = array_merge($data, $node_result);
        $final_result = [];
        $array_data = [];
        if (isset($result_custom_data) && is_array($result_custom_data)) {
            // Initialize counters.
            $final_result['counters_total'] = [
                't_m_normal'   => 0,
                't_m_critical' => 0,
                't_m_warning'  => 0,
                't_m_unknown'  => 0,
                't_m_not_init' => 0,
                't_m_alerts'   => 0,
                't_m_total'    => 0,
                't_a_critical' => 0,
                't_a_warning'  => 0,
                't_a_unknown'  => 0,
                't_a_normal'   => 0,
                't_a_not_init' => 0,
                't_a_agents'   => 0,
            ];
            foreach ($result_custom_data as $k => $nodo) {
                if (isset($nodo) && is_array($nodo)) {
                    foreach ($nodo as $key => $value) {
                        // Sum counters total.
                        $final_result['counters_total']['t_m_normal'] += $value['m_normal'];
                        $final_result['counters_total']['t_m_critical'] += $value['m_critical'];
                        $final_result['counters_total']['t_m_warning'] += $value['m_warning'];
                        $final_result['counters_total']['t_m_unknown'] += $value['m_unknown'];
                        $final_result['counters_total']['t_m_not_init'] += $value['m_not_init'];
                        $final_result['counters_total']['t_m_alerts'] += $value['m_alerts'];
                        $final_result['counters_total']['t_m_total'] += $value['m_total'];
                        $final_result['counters_total']['t_a_critical'] += $value['a_critical'];
                        $final_result['counters_total']['t_a_warning'] += $value['a_warning'];
                        $final_result['counters_total']['t_a_unknown'] += $value['a_unknown'];
                        $final_result['counters_total']['t_a_normal'] += $value['a_normal'];
                        $final_result['counters_total']['t_a_not_init'] += $value['a_not_init'];
                        $final_result['counters_total']['t_a_agents'] += $value['a_agents'];

                        // Sum counters for data.
                        $array_data[$value['name_data']]['m_normal'] += $value['m_normal'];
                        $array_data[$value['name_data']]['m_critical'] += $value['m_critical'];
                        $array_data[$value['name_data']]['m_warning'] += $value['m_warning'];
                        $array_data[$value['name_data']]['m_unknown'] += $value['m_unknown'];
                        $array_data[$value['name_data']]['m_not_init'] += $value['m_not_init'];
                        $array_data[$value['name_data']]['m_alerts'] += $value['m_alerts'];
                        $array_data[$value['name_data']]['m_total'] += $value['m_total'];
                        $array_data[$value['name_data']]['a_critical'] += $value['a_critical'];
                        $array_data[$value['name_data']]['a_warning'] += $value['a_warning'];
                        $array_data[$value['name_data']]['a_unknown'] += $value['a_unknown'];
                        $array_data[$value['name_data']]['a_normal'] += $value['a_normal'];
                        $array_data[$value['name_data']]['a_not_init'] += $value['a_not_init'];
                        $array_data[$value['name_data']]['a_agents'] += $value['a_agents'];
                    }
                }
            }

            $final_result['counters_name'] = $array_data;
        }

        $final_result['indexed_descriptions'] = $data;
    

    return $final_result;
}


function get_filters_custom_fields_view($id=0, $for_select=false, $name='')
{
    // Filter group and check ACL groups.
    $groups_and = '';
    if (!users_can_manage_group_all()) {
        $user_groups = array_keys(users_get_groups(false, 'AR', false));
        $id_groups = implode(', ', $user_groups);
        $groups_and = " AND (group_search IN ($id_groups)) ";
    }

    if ($for_select) {
        $query = 'SELECT id, `name` FROM tagent_custom_fields_filter WHERE 1=1'.$groups_and;
        $rs = db_get_all_rows_sql($query);
        if (isset($rs) && is_array($rs)) {
            foreach ($rs as $key => $value) {
                $result[$value['id']] = $value['name'];
            }
        } else {
            $result = false;
        }
    } else {
        $query = 'SELECT * FROM tagent_custom_fields_filter WHERE 1=1'.$groups_and;

        if ($id) {
            $query .= ' AND id = '.$id;
        }

        if ($name) {
            $query .= " AND `name` = '".$name."'";
        }

        $result = db_get_all_rows_sql($query);
    }

    return $result;
}


function get_group_filter_custom_field_view($id)
{
    if (isset($id)) {
        $res = db_get_row_filter('tagent_custom_fields_filter', ['id' => $id]);
        return $res;
    }

    return false;
}


/**
 * Function for print counters agents or modules.
 *
 * @param array  $status_array Array need value, image, title, color, counter.
 * @param string $id_form      Id form default value ''.
 * @param string $id_input     Id input default value ''.
 *
 * @return array Return html print div container counters.
 */
function print_counters_cfv(
    array $status_array,
    string $id_form='',
    string $id_input=''
) {
    $html_result = '<form class = "cfv_status_agent" id="'.$id_form.'">';
    foreach ($status_array as $key => $value) {
        $checked = ($value['checked'] === 1) ? 'checked=true' : '';
        $disabled = ($value['counter'] === 0) ? 'disabled=true' : '';

        $html_result .= '<input id="lists_'.$id_input.'['.$key.']" '.$checked.' '.$disabled.' type="checkbox" name="lists_'.$id_input.'['.$key.']" />';
        $html_result .= '<label for="lists_'.$id_input.'['.$key.']" style="background-color:'.$value['color'].';">';
        $html_result .= html_print_image(
            $value['image'],
            true,
            ['title' => $value['title']]
        );
        $html_result .= $value['counter'];
        $html_result .= '</label>';
    }

    $html_result .= '</form>';
    return $html_result;
}
