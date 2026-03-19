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

if (check_acl($id_user, 0, 'ER')) {
    $groups = users_get_groups($id_user, 'ER');
} else if (check_acl($id_user, 0, 'EW')) {
    $groups = users_get_groups($id_user, 'EW');
} else if (check_acl($id_user, 0, 'EM')) {
    $groups = users_get_groups($id_user, 'EM');
}

$propagate = db_get_value('propagate', 'tgrupo', 'id_grupo', $id_group);

if ($id_group > 0) {
    $filter_resume['groups'] = $id_group;
    if ($propagate) {
        $childrens_ids = [$id_group];

        $childrens = groups_get_children($id_group, null, true);

        if (!empty($childrens)) {
            foreach ($childrens as $child) {
                $childrens_ids[] = (int) $child['id_grupo'];
            }
        }
    } else {
        $childrens_ids = [];
    }
} else {
    $childrens_ids = array_keys($groups);
}

if (!isset($date_from)) {
    $date_from = '';
}

if (!isset($date_to)) {
    $date_to = '';
}

if (($date_from === '') && ($date_to === '')) {
    if ($event_view_hr > 0) {
        $filter_resume['hours_max'] = $event_view_hr;
        $unixtime = (get_system_time() - ($event_view_hr * SECONDS_1HOUR));
        $sql_post .= ' AND (utimestamp > '.$unixtime.')';
    }
} else {
    // Some of this values will have the user's timezone,
    // so we need to reverse it to the system's timezone
    // before using it into the db.
    $fixed_offset = get_fixed_offset();

    if (!empty($date_from)) {
        if (empty($time_from)) {
            $time_from = '00:00:00';
        }

        $utimestamp_from = (strtotime($date_from.' '.$time_from) - $fixed_offset);
        $filter_resume['time_from'] = date(DATE_FORMAT.' '.TIME_FORMAT, $utimestamp_from);
        $sql_post .= ' AND (utimestamp >= '.$utimestamp_from.')';
    }

    if (!empty($date_to)) {
        if (empty($time_to)) {
            $time_to = '23:59:59';
        }

        $utimestamp_to = (strtotime($date_to.' '.$time_to) - $fixed_offset);
        $filter_resume['time_to'] = date(DATE_FORMAT.' '.TIME_FORMAT, $utimestamp_to);
        $sql_post .= ' AND (utimestamp <= '.$utimestamp_to.')';
    }
}

// Group selection.
if ($id_group > 0 && in_array($id_group, array_keys($groups))) {
    if ($propagate) {
        $childrens_str = implode(',', $childrens_ids);
        $sql_post .= " AND (id_grupo IN ($childrens_str)";

        $sql_post .= ')';
    } else {
        // If a group is selected and it's in the groups allowed.
        $sql_post .= " AND (id_grupo = $id_group";

        $sql_post .= ')';
    }
} else {
    if (!users_is_admin() && !users_can_manage_group_all('ER')) {
            $sql_post .= sprintf(
                ' AND (id_grupo IN (%s)) ',
                implode(',', array_keys($groups))
            );
    }
}

// Skip system messages if user is not PM.
if (!check_acl($id_user, 0, 'PM')) {
    $sql_post .= ' AND id_grupo != 0';
}

switch ($status) {
    case 0:
    case 1:
    case 2:
        $filter_resume['status'] = $status;
        $sql_post .= ' AND estado = '.$status;
    break;

    case 3:
        $filter_resume['status'] = $status;
        $sql_post .= ' AND (estado = 0 OR estado = 2)';
    break;
}

/*
 * Never use things like this.
 *
 * $events_wi_cdata = db_get_all_rows_sql('SELECT id_evento,custom_data from tevento WHERE custom_data != ""');
 * $count_events = 0;
 * $events_wi_cdata_id = 'OR id_evento IN (';
 * if ($events_wi_cdata === false) {
 *     $events_wi_cdata = [];
 * }
 *
 * foreach ($events_wi_cdata as $key => $value) {
 *     $needle = base64_decode($value['custom_data']);
 *     if (($needle != '') && ($search != '')) {
 *         if (strpos(strtolower($needle), strtolower($search)) != false) {
 *             $events_wi_cdata_id .= $value['id_evento'];
 *             $count_events++;
 *         }
 *     }
 *
 *     if ($value !== end($events_wi_cdata) && $count_events > 0) {
 *         $events_wi_cdata_id .= ',';
 *         $events_wi_cdata_id = str_replace(',,', ',', $events_wi_cdata_id);
 *     }
 * }
 *
 * $events_wi_cdata_id .= ')';
 *
 * $events_wi_cdata_id = str_replace(',)', ')', $events_wi_cdata_id);
 *
 * if ($count_events == 0) {
 *     $events_wi_cdata_id = '';
 * }
 */

if ($search != '') {
    $filter_resume['free_search'] = $search;
    $sql_post .= " AND (evento LIKE '%".$search."%' OR id_evento LIKE '%$search%' )";
}

if ($event_type != '') {
    $filter_resume['event_type'] = $event_type;
    // If normal, warning, could be several (going_up_warning, going_down_warning... too complex
    // for the user so for him is presented only "warning, critical and normal"
    if ($event_type == 'warning' || $event_type == 'critical' || $event_type == 'normal') {
        $sql_post .= " AND event_type LIKE '%$event_type%' ";
    } else if ($event_type == 'not_normal') {
        $sql_post .= " AND (event_type LIKE '%warning%' OR event_type LIKE '%critical%' OR event_type LIKE '%unknown%') ";
    } else if ($event_type != 'all') {
        $sql_post .= " AND event_type = '".$event_type."'";
    }
}

if ($severity != -1) {
    $filter_resume['severity'] = $severity;
    switch ($severity) {
        case EVENT_CRIT_WARNING_OR_CRITICAL:
            $sql_post .= '
				AND (criticity = '.EVENT_CRIT_WARNING.' OR 
					criticity = '.EVENT_CRIT_CRITICAL.')';
        break;

        case EVENT_CRIT_OR_NORMAL:
            $sql_post .= '
				AND (criticity = '.EVENT_CRIT_NORMAL.' OR 
					criticity = '.EVENT_CRIT_CRITICAL.')';
        break;

        case EVENT_CRIT_NOT_NORMAL:
            $sql_post .= ' AND criticity != '.EVENT_CRIT_NORMAL;
        break;

        default:
            $sql_post .= " AND criticity = $severity";
        break;
    }
}

if ($id_extra != '') {
    $sql_post .= " AND id_extra LIKE '%$id_extra%'";
}

if ($source != '') {
    $sql_post .= " AND source LIKE '%$source%'";
}

switch ($id_agent) {
    case 0:
    break;

    case -1:
        // Agent doesnt exist. No results will returned.
        $sql_post .= ' AND 1 = 0';
    break;

    default:
        $filter_resume['agent'] = $id_agent;
        $sql_post .= ' AND id_agente = '.$id_agent;
    break;
}

if (!empty($text_module)) {
    $filter_resume['module'] = $text_module;
    $sql_post .= " AND id_agentmodule IN (
            SELECT id_agente_modulo
            FROM tagente_modulo
            WHERE nombre = '$text_module'
        )";
}

if ($id_user_ack != '0') {
    $filter_resume['user_ack'] = $id_user_ack;
    $sql_post .= " AND id_usuario = '".$id_user_ack."'";
}

// Search by tag.
if (!empty($tag_with)) {
    if (!users_is_admin()) {
        $user_tags = array_flip(tags_get_tags_for_module_search());
        if ($user_tags != null) {
            foreach ($tag_with as $id_tag) {
                if (!array_search($id_tag, $user_tags)) {
                    return false;
                }
            }
        }
    }

    $sql_post .= ' AND ( ';
    $first = true;
    $filter_resume['tag_inc'] = $tag_with;
    foreach ($tag_with as $id_tag) {
        if ($first) {
            $sql_post .= ' ( ';
            $first = false;
        } else {
            $sql_post .= ' AND ( ';
        }

        $sql_post .= "tags LIKE '".tags_get_name($id_tag)."'";
        $sql_post .= ' OR ';
        $sql_post .= "tags LIKE '".tags_get_name($id_tag).",%'";
        $sql_post .= ' OR ';
        $sql_post .= "tags LIKE '%,".tags_get_name($id_tag)."'";
        $sql_post .= ' OR ';
        $sql_post .= "tags LIKE '%,".tags_get_name($id_tag).",%'";
        $sql_post .= ' ) ';
    }

    $sql_post .= ' ) ';
}

if (!empty($tag_without)) {
    $sql_post .= ' AND ( ';
    $first = true;
    $filter_resume['tag_no_inc'] = $tag_without;
    foreach ($tag_without as $id_tag) {
        if ($first) {
            $first = false;
        } else {
            $sql_post .= ' AND ';
        }

        $sql_post .= "tags NOT LIKE '%".tags_get_name($id_tag)."%'";
    }

    $sql_post .= ' ) ';
}

// Filter/Only alerts.
if (isset($filter_only_alert)) {
    if ($filter_only_alert == 0) {
        $filter_resume['alerts'] = $filter_only_alert;
        $sql_post .= " AND event_type NOT LIKE '%alert%'";
    } else if ($filter_only_alert == 1) {
        $filter_resume['alerts'] = $filter_only_alert;
        $sql_post .= " AND event_type LIKE '%alert%'";
    }
}

// Tags ACLS.
if ($id_group > 0 && in_array($id_group, array_keys($groups))) {
    $group_array = (array) $id_group;
} else {
    $group_array = array_keys($groups);
}

if (check_acl($id_user, 0, 'ER')) {
    $tags_acls_condition = tags_get_acl_tags(
        $id_user,
        $group_array,
        'ER',
        'event_condition',
        'AND',
        '',
        false,
        [],
        true
    );
    // FORCE CHECK SQL "(TAG = tag1 AND id_grupo = 1)".
} else if (check_acl($id_user, 0, 'EW')) {
    $tags_acls_condition = tags_get_acl_tags(
        $id_user,
        $group_array,
        'EW',
        'event_condition',
        'AND',
        '',
        false,
        [],
        true
    );
    // FORCE CHECK SQL "(TAG = tag1 AND id_grupo = 1)".
} else if (check_acl($id_user, 0, 'EM')) {
    $tags_acls_condition = tags_get_acl_tags(
        $id_user,
        $group_array,
        'EM',
        'event_condition',
        'AND',
        '',
        false,
        [],
        true
    );
    // FORCE CHECK SQL "(TAG = tag1 AND id_grupo = 1)".
}

if (($tags_acls_condition != ERR_WRONG_PARAMETERS) && ($tags_acls_condition != ERR_ACL) && ($tags_acls_condition != -110000)) {
    $sql_post .= $tags_acls_condition;
}

$sql_post .= ' AND 1 = 0';
