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

// Begin.
if (! isset($config['id_user'])) {
    return;
}

use PandoraFMS\Dashboard\Manager;

require_once 'include/functions_menu.php';
require_once $config['homedir'].'/include/functions_visual_map.php';
require_once 'include/class/MenuItem.class.php';
$menu_operation = [];
$menu_operation['class'] = 'operation';

$access_console_node = !is_reporting_console_node();
if ($access_console_node === true) {
    // Agent read, Server read.
    if (check_acl($config['id_user'], 0, 'AR')) {
        // View agents.
        $menu_operation['estado']['text'] = __('Monitoring');
        $menu_operation['estado']['sec2'] = 'operation/agentes/tactical';
        $menu_operation['estado']['refr'] = 0;
        $menu_operation['estado']['id'] = 'oper-agents';

        $sub = [];
        $sub['view']['text'] = __('Views');
        $sub['view']['id'] = 'Views';
        $sub['view']['type'] = 'direct';
        $sub['view']['subtype'] = 'nolink';
        $sub['view']['refr'] = 0;

        $sub2 = [];

        $sub2['operation/agentes/tactical']['text'] = __('Tactical view');
        $sub2['operation/agentes/tactical']['refr'] = 0;

        $sub2['operation/agentes/group_view']['text'] = __('Group view');
        $sub2['operation/agentes/group_view']['refr'] = 0;

        $sub2['operation/tree']['text'] = __('Tree view');
        $sub2['operation/tree']['refr'] = 0;

        $sub2['operation/agentes/estado_agente']['text'] = __('Agent detail');
        $sub2['operation/agentes/estado_agente']['refr'] = 0;
        $sub2['operation/agentes/estado_agente']['subsecs'] = ['operation/agentes/ver_agente'];

        $sub2['operation/agentes/status_monitor']['text'] = __('Monitor detail');
        $sub2['operation/agentes/status_monitor']['refr'] = 0;

        $sub2['operation/agentes/interface_view']['text'] = __('Interface view');
        $sub2['operation/agentes/interface_view']['refr'] = 0;

        $sub2['operation/agentes/alerts_status']['text'] = __('Alert detail');
        $sub2['operation/agentes/alerts_status']['refr'] = 0;

        $sub2['operation/heatmap']['text'] = __('Heatmap view');
        $sub2['operation/heatmap']['refr'] = 0;

        $sub['view']['sub2'] = $sub2;

        if (check_acl($config['id_user'], 0, 'AR') || check_acl($config['id_user'], 0, 'AW')) {
            $sub['operation/inventory/inventory']['text'] = __('Inventory');
            $sub['operation/inventory/inventory']['id'] = 'Inventory';
            $sub['operation/inventory/inventory']['refr'] = 0;
        }

        $sub['operation/custom_fields/custom_fields_view']['text'] = __('Custom fields view');
        $sub['operation/custom_fields/custom_fields_view']['id'] = 'Custom fields view';
        $sub['operation/custom_fields/custom_fields_view']['refr'] = 0;

        if ($config['activate_netflow'] || $config['activate_sflow']) {
            $sub['network_traffic'] = [
                'text'    => __('Network'),
                'id'      => 'Network',
                'type'    => 'direct',
                'subtype' => 'nolink',
                'refr'    => 0,
            ];
            $netflow_sub = [
                'operation/netflow/netflow_explorer'  => [
                    'text' => __('Netflow explorer'),
                    'id'   => 'Netflow explorer',
                ],
                'operation/netflow/nf_live_view'      => [
                    'text' => __('Netflow Live View'),
                    'id'   => 'Netflow Live View',
                ],
                'operation/network/network_usage_map' => [
                    'text' => __('Network usage map'),
                    'id'   => 'Network usage map',
                ],
            ];
            $sub['network_traffic']['sub2'] = $netflow_sub;
        }

        // End of view agents.
    }

    // SNMP Console.
    $sub2 = [];
    if (check_acl($config['id_user'], 0, 'AR') || check_acl($config['id_user'], 0, 'AW')) {
        $sub2['operation/snmpconsole/snmp_view']['text'] = __('SNMP console');
        $sub2['operation/snmpconsole/snmp_browser']['text'] = __('SNMP browser');
    }

    if (check_acl($config['id_user'], 0, 'PM')) {
        $sub2['operation/snmpconsole/snmp_mib_uploader']['text'] = __('MIB uploader');
    }

    if (check_acl($config['id_user'], 0, 'LW') || check_acl($config['id_user'], 0, 'LM')) {
        $sub2['godmode/snmpconsole/snmp_filters']['text'] = __('SNMP filters');
        $sub2['godmode/snmpconsole/snmp_trap_generator']['text'] = __('SNMP trap generator');
    }

    if (empty($sub2) === false) {
        $sub['snmpconsole']['sub2'] = $sub2;
        $sub['snmpconsole']['text'] = __('SNMP');
        $sub['snmpconsole']['id'] = 'SNMP';
        $sub['snmpconsole']['refr'] = 0;
        $sub['snmpconsole']['type'] = 'direct';
        $sub['snmpconsole']['subtype'] = 'nolink';
    }

    if (check_acl($config['id_user'], 0, 'AR')) {
        $sub['operation/cluster/cluster']['text'] = __('Cluster View');
        $sub['operation/cluster/cluster']['id'] = 'cluster';
        $sub['operation/cluster/cluster']['refr'] = 0;
    }

    if (!empty($sub)) {
        $menu_operation['estado']['text'] = __('Monitoring');
        $menu_operation['estado']['sec2'] = 'operation/agentes/tactical';
        $menu_operation['estado']['refr'] = 0;
        $menu_operation['estado']['id'] = 'oper-agents';
        $menu_operation['estado']['sub'] = $sub;
    }

    // Start network view.
    $sub = [];
    if (check_acl($config['id_user'], 0, 'MR') || check_acl($config['id_user'], 0, 'MW') || check_acl($config['id_user'], 0, 'MM')) {
        $sub['operation/agentes/pandora_networkmap']['text'] = __('Network map');
        $sub['operation/agentes/pandora_networkmap']['id'] = 'Network_map';
        $sub['operation/agentes/pandora_networkmap']['refr'] = 0;
    }


    if (check_acl($config['id_user'], 0, 'VR') || check_acl($config['id_user'], 0, 'VW') || check_acl($config['id_user'], 0, 'VM')) {
        $url_visual_console = '';
        if (!isset($config['vc_favourite_view']) || $config['vc_favourite_view'] == 0) {
            // Visual console.
            $sub['godmode/reporting/map_builder']['text'] = __('Visual console');
            $sub['godmode/reporting/map_builder']['id'] = 'Visual_console';
            $sub['godmode/reporting/map_builder']['type'] = 'direct';
            $sub['godmode/reporting/map_builder']['subtype'] = 'nolink';
            $url_visual_console = 'godmode/reporting/map_builder';
        } else {
            // Visual console favorite.
            $sub['godmode/reporting/visual_console_favorite']['text'] = __('Visual console');
            $sub['godmode/reporting/visual_console_favorite']['id'] = 'Visual_console';
            $sub['godmode/reporting/visual_console_favorite']['type'] = 'direct';
            $sub['godmode/reporting/visual_console_favorite']['subtype'] = 'nolink';
            $url_visual_console = 'godmode/reporting/visual_console_favorite';
        }

        if ($config['vc_menu_items'] != 0) {
            // Set godomode path.
            if (!isset($config['vc_favourite_view']) || $config['vc_favourite_view'] == 0) {
                $sub['godmode/reporting/map_builder']['subsecs'] = [
                    'godmode/reporting/map_builder',
                    'godmode/reporting/visual_console_builder',
                ];
            } else {
                $sub['godmode/reporting/visual_console_favorite']['subsecs'] = [
                    'godmode/reporting/map_builder',
                    'godmode/reporting/visual_console_builder',
                ];
            }

            // $layouts = db_get_all_rows_in_table ('tlayout', 'name');
            $own_info = get_user_info($config['id_user']);
            $returnAllGroups = 0;
            if ($own_info['is_admin']) {
                $returnAllGroups = 1;
            }

            $layouts = visual_map_get_user_layouts($config['id_user'], false, false, $returnAllGroups, true);
            $sub2 = [];

            $sub2[$url_visual_console] = [
                'text'  => __('Visual console list'),
                'title' => __('Visual console list'),
                'refr'  => 0,
            ];

            if ($layouts === false) {
                $layouts = [];
            } else {
                $id = (int) get_parameter('id', -1);
                $delete_layout = (bool) get_parameter('delete_layout');

                if ($delete_layout === true) {
                    $id_layout = (int) get_parameter('id_layout');
                    unset($layouts[$id_layout]);
                }

                $break_max_console = false;
                $max = $config['vc_menu_items'];
                $i = 0;
                foreach ($layouts as $layout) {
                    $i++;
                    if ($i > $max) {
                        $break_max_console = true;
                        break;
                    }

                    $name = io_safe_output($layout['name']);

                    $sub2['operation/visual_console/render_view&id='.$layout['id']]['text'] = ui_print_truncate_text($name, MENU_SIZE_TEXT, false, true, false);
                    $sub2['operation/visual_console/render_view&id='.$layout['id']]['id'] = mb_substr($name, 0, 19);
                    $sub2['operation/visual_console/render_view&id='.$layout['id']]['title'] = $name;
                    if (!empty($config['vc_refr'])) {
                        $sub2['operation/visual_console/render_view&id='.$layout['id']]['refr'] = $config['vc_refr'];
                    } else if (((int) get_parameter('refr', 0)) > 0) {
                        $sub2['operation/visual_console/render_view&id='.$layout['id']]['refr'] = (int) get_parameter('refr', 0);
                    } else {
                        $sub2['operation/visual_console/render_view&id='.$layout['id']]['refr'] = 0;
                    }
                }

                if ($break_max_console) {
                    $sub2['godmode/reporting/visual_console_favorite']['text']  = __('Show more').' >';
                    $sub2['godmode/reporting/visual_console_favorite']['id']    = 'visual_favourite_console';
                    $sub2['godmode/reporting/visual_console_favorite']['title'] = __('Show more');
                    $sub2['godmode/reporting/visual_console_favorite']['refr']  = 0;
                }

                if (!empty($sub2)) {
                    if (!isset($config['vc_favourite_view']) || $config['vc_favourite_view'] == 0) {
                        $sub['godmode/reporting/map_builder']['sub2'] = $sub2;
                    } else {
                        $sub['godmode/reporting/visual_console_favorite']['sub2'] = $sub2;
                    }
                }
            }
        }
    }

    if (check_acl($config['id_user'], 0, 'MR') || check_acl($config['id_user'], 0, 'MW') || check_acl($config['id_user'], 0, 'MM')) {
        // INI GIS Maps.
        if ($config['activate_gis']) {
            $sub['gismaps']['text'] = __('GIS Maps');
            $sub['gismaps']['id'] = 'GIS_Maps';
            $sub['gismaps']['type'] = 'direct';
            $sub['gismaps']['subtype'] = 'nolink';
            $sub2 = [];
            $sub2['operation/gis_maps/gis_map']['text'] = __('List of Gis maps');
            $sub2['operation/gis_maps/gis_map']['id'] = 'List of Gis maps';
            $gisMaps = db_get_all_rows_in_table('tgis_map', 'map_name');
            if ($gisMaps === false) {
                $gisMaps = [];
            }

            $id = (int) get_parameter('id', -1);

            $own_info = get_user_info($config['id_user']);
            if ($own_info['is_admin'] || check_acl($config['id_user'], 0, 'PM')) {
                $own_groups = array_keys(users_get_groups($config['id_user'], 'MR'));
            } else {
                $own_groups = array_keys(users_get_groups($config['id_user'], 'MR', false));
            }

            foreach ($gisMaps as $gisMap) {
                $is_in_group = in_array($gisMap['group_id'], $own_groups);
                if (!$is_in_group) {
                    continue;
                }

                $sub2['operation/gis_maps/render_view&map_id='.$gisMap['id_tgis_map']]['text'] = ui_print_truncate_text(io_safe_output($gisMap['map_name']), MENU_SIZE_TEXT, false, true, false);
                $sub2['operation/gis_maps/render_view&map_id='.$gisMap['id_tgis_map']]['id'] = mb_substr(io_safe_output($gisMap['map_name']), 0, 15);
                $sub2['operation/gis_maps/render_view&map_id='.$gisMap['id_tgis_map']]['title'] = io_safe_output($gisMap['map_name']);
                $sub2['operation/gis_maps/render_view&map_id='.$gisMap['id_tgis_map']]['refr'] = 0;
            }

            $sub['gismaps']['sub2'] = $sub2;
        }

        // END GIS Maps.
    }

    if (!empty($sub)) {
        $menu_operation['network']['text'] = __('Topology maps');
        $menu_operation['network']['sec2'] = 'operation/agentes/networkmap_list';
        $menu_operation['network']['refr'] = 0;
        $menu_operation['network']['id'] = 'oper-networkconsole';
        $menu_operation['network']['sub'] = $sub;
    }

    // End networkview.
    // Reports read.
    if (check_acl($config['id_user'], 0, 'RR') || check_acl($config['id_user'], 0, 'RW') || check_acl($config['id_user'], 0, 'RM')) {
        // Reporting.
        $menu_operation['reporting']['text'] = __('Reporting');
        $menu_operation['reporting']['sec2'] = 'godmode/reporting/reporting_builder';
        $menu_operation['reporting']['id'] = 'oper-reporting';
        $menu_operation['reporting']['refr'] = 300;

        $sub = [];

        $sub['custom_report']['text'] = __('Custom Reports');
        $sub['custom_report']['id'] = 'Custom_reporting';
        $sub['custom_report']['type'] = 'direct';
        $sub['custom_report']['subtype'] = 'nolink';
        $sub['custom_report']['refr'] = 0;

        $sub2 = [];
        $sub2['godmode/reporting/reporting_builder']['text'] = __('Reports');
        $sub2['godmode/reporting/reporting_builder&tab=template&action=list_template']['text'] = __('Templates');

        $sub['custom_report']['sub2'] = $sub2;


        $sub['godmode/reporting/graphs']['text'] = __('Custom graphs');
        $sub['godmode/reporting/graphs']['id'] = 'Custom_graphs';
        // Set godomode path.
        $sub['godmode/reporting/graphs']['subsecs'] = [
            'operation/reporting/graph_viewer',
            'godmode/reporting/graph_builder',
        ];


        // Graph analytics.
        $sub['operation/reporting/graph_analytics']['text'] = __('Graph analytics');
        $sub['operation/reporting/graph_analytics']['id'] = 'Graph_analytics';


        if (check_acl($config['id_user'], 0, 'RR')
            || check_acl($config['id_user'], 0, 'RW')
            || check_acl($config['id_user'], 0, 'RM')
        ) {
            $sub['operation/dashboard/dashboard']['text'] = __('Dashboard');
            $sub['operation/dashboard/dashboard']['id'] = 'Dashboard';
            $sub['operation/dashboard/dashboard']['refr'] = 0;
            $sub['operation/dashboard/dashboard']['subsecs'] = ['operation/dashboard/dashboard'];
            $sub['operation/dashboard/dashboard']['type'] = 'direct';
            $sub['operation/dashboard/dashboard']['subtype'] = 'nolink';

            $dashboards = Manager::getDashboards(-1, -1, true);

            $sub2 = [];
            $sub2['operation/dashboard/dashboard'] = [
                'text'  => __('Dashboard list'),
                'title' => __('Dashboard list'),
            ];
            foreach ($dashboards as $dashboard) {
                $name = io_safe_output($dashboard['name']);

                $sub2['operation/dashboard/dashboard&dashboardId='.$dashboard['id']] = [
                    'text'  => ui_print_truncate_text($name, MENU_SIZE_TEXT, false, true, false),
                    'title' => $name,
                ];
            }

            if (empty($sub2) === false) {
                $sub['operation/dashboard/dashboard']['sub2'] = $sub2;
            }
        }

        $menu_operation['reporting']['sub'] = $sub;
        // End reporting.
    }

    // Events reading.
    if (check_acl($config['id_user'], 0, 'ER')
        || check_acl($config['id_user'], 0, 'EW')
        || check_acl($config['id_user'], 0, 'EM')
    ) {
        // Events.
        $menu_operation['eventos']['text'] = __('Events');
        $menu_operation['eventos']['refr'] = 0;
        $menu_operation['eventos']['sec2'] = 'operation/events/events';
        $menu_operation['eventos']['id'] = 'oper-events';

        $sub = [];
        $sub['operation/events/events']['text'] = __('View events');
        $sub['operation/events/events']['id'] = 'View_events';
        $sub['operation/events/events']['pages'] = ['godmode/events/events'];

        // If ip doesn't is in list of allowed IP, isn't show this options.
        include_once 'include/functions_api.php';
        if (isInACL($_SERVER['REMOTE_ADDR'])) {
            $pss = get_user_info($config['id_user']);
            $hashup = md5($config['id_user'].$pss['password']);

            $user_filter = db_get_row_sql(
                sprintf(
                    'SELECT f.id_filter, f.id_name
                FROM tevent_filter f
                INNER JOIN tusuario u
                    ON u.default_event_filter=f.id_filter
                WHERE u.id_user = "%s" ',
                    $config['id_user']
                )
            );
            if ($user_filter !== false) {
                $user_event_filter = events_get_event_filter($user_filter['id_filter']);
            } else {
                // Default.
                $user_event_filter = [
                    'status'        => EVENT_NO_VALIDATED,
                    'event_view_hr' => $config['event_view_hr'],
                    'group_rep'     => EVENT_GROUP_REP_EVENTS,
                    'tag_with'      => [],
                    'tag_without'   => [],
                    'history'       => false,
                ];
            }

            $fb64 = base64_encode(json_encode($user_event_filter));

            // RSS.
            $sub['operation/events/events_rss.php?user='.$config['id_user'].'&amp;hashup='.$hashup.'&fb64='.$fb64]['text'] = __('RSS');
            $sub['operation/events/events_rss.php?user='.$config['id_user'].'&amp;hashup='.$hashup.'&fb64='.$fb64]['id'] = 'RSS';
            $sub['operation/events/events_rss.php?user='.$config['id_user'].'&amp;hashup='.$hashup.'&fb64='.$fb64]['type'] = 'direct';
        }

            $urlSound = 'include/sounds/';
        

        // Acoustic console.
        $data_sound = base64_encode(
            json_encode(
                [
                    'title'        => __('Acoustic console'),
                    'start'        => __('Start'),
                    'stop'         => __('Stop'),
                    'noAlert'      => __('No alert'),
                    'silenceAlarm' => __('Silence alarm'),
                    'url'          => ui_get_full_url('ajax.php'),
                    'page'         => 'include/ajax/events',
                    'urlSound'     => $urlSound,
                ]
            )
        );

        $javascript = 'javascript: openSoundEventModal(`'.$data_sound.'`);';
        $sub[$javascript]['text'] = __('Acoustic console');
        $sub[$javascript]['id'] = 'Acoustic console Modal';
        $sub[$javascript]['type'] = 'direct';

        echo '<div id="modal-sound" style="display:none;"></div>';
        echo '<div id="modal-asteroids" style="display:none;"></div>';

        ui_require_javascript_file('pandora_events');

        $menu_operation['eventos']['sub'] = $sub;
    }
}

$favorite_menu = db_get_all_rows_sql(
    sprintf(
        'SELECT id_element, url, label, section
        FROM tfavmenu_user
        WHERE id_user = "%s"
        ORDER BY section DESC',
        $config['id_user']
    )
);
// Favorite.
if ($favorite_menu !== false) {
    $menu_operation['favorite']['text'] = __('Favorite');
    $menu_operation['favorite']['id'] = 'fav-menu';

    $section = '';
    $sub = [];
    $sub2 = [];
    foreach ($favorite_menu as $key => $row) {
        if ($row['section'] !== $section) {
            $section = $row['section'];
            $sub2 = [];
        }

        $sub[$section]['text'] = __(str_replace('_', ' ', $section));
        $sub[$section]['type'] = 'direct';
        $sub[$section]['subtype'] = 'nolink';
        $sub[$section]['id'] = $row['section'].'-fav-menu';

        $sub2[$row['url']]['text'] = io_safe_output($row['label']);
        $sub[$section]['sub2'] = $sub2;
    }

    $menu_operation['favorite']['sub'] = $sub;
}


// Links.
$rows = db_get_all_rows_in_table('tlink', 'name');
// $rows = [];
if (!empty($rows)) {
    $menu_operation['links']['text'] = __('Links');
    $menu_operation['links']['sec2'] = '';
    $menu_operation['links']['id'] = 'god-links';

    $traslations = [
        'Get support'         => __('Get support'),
        'Report a bug'        => __('Report a bug'),
        'Suggest new feature' => __('Suggest new feature'),
    ];

    $sub = [];
    hd($rows, true);
    foreach ($rows as $row) {
        $sub[$row['link']]['text'] = (empty($traslations[$row['name']]) === false)
            ? $traslations[$row['name']]
            : __($row['name']);
        $sub[$row['link']]['id'] = $row['name'];
        $sub[$row['link']]['type'] = 'direct';
        $sub[$row['link']]['subtype'] = 'new_blank';
        hd($row['link'], true);
    }

    $menu_operation['links']['sub'] = $sub;
}



// Workspace.
$menu_operation['workspace']['text'] = __('Workspace');
$menu_operation['workspace']['sec2'] = 'operation/users/user_edit';
$menu_operation['workspace']['id'] = 'oper-users';

// ANY user can view him/herself !
// Users.
$query_paramameters_user = '&edit_user=1&pure=0';

$sub = [];
$sub['godmode/users/configure_user'.$query_paramameters_user]['text'] = __('Edit my user');
$sub['godmode/users/configure_user'.$query_paramameters_user]['id'] = 'Edit_my_user';
$sub['godmode/users/configure_user'.$query_paramameters_user]['refr'] = 0;

// Users.
$sub['operation/users/user_edit_notifications']['text'] = __('Configure user notifications');
$sub['operation/users/user_edit_notifications']['id'] = 'Configure_user_notifications';
$sub['operation/users/user_edit_notifications']['refr'] = 0;

if ($access_console_node === true) {
    // Messages.
    $sub['message_list']['text'] = __('Messages');
    $sub['message_list']['id'] = 'Messages';
    $sub['message_list']['refr'] = 0;
    $sub['message_list']['type'] = 'direct';
    $sub['message_list']['subtype'] = 'nolink';
    $sub2 = [];
    $sub2['operation/messages/message_list']['text'] = __('Messages List');
    $sub2['operation/messages/message_edit&new_msg=1']['text'] = __('New message');

    $sub['message_list']['sub2'] = $sub2;
}

$menu_operation['workspace']['sub'] = $sub;

if ($access_console_node === true) {
    // Extensions menu additions.
    if (is_array($config['extensions'])) {
        foreach ($config['extensions'] as $extension) {
            // If no operation_menu is a godmode extension.
            if ($extension['operation_menu'] == '') {
                continue;
            }

            // Check the ACL for this user.
            if (! check_acl($config['id_user'], 0, $extension['operation_menu']['acl'])) {
                continue;
            }

            $extension_menu = $extension['operation_menu'];
            if ($extension['operation_menu']['name'] == 'Matrix'
                && (!check_acl($config['id_user'], 0, 'ER')
                || !check_acl($config['id_user'], 0, 'EW')
                || !check_acl($config['id_user'], 0, 'EM'))
            ) {
                continue;
            }

            if (array_key_exists('fatherId', $extension_menu)) {
                // Check that extension father ID exists previously on the menu.
                if ((strlen($extension_menu['fatherId']) > 0)) {
                    if (array_key_exists('subfatherId', $extension_menu) && empty($extension_menu['subfatherId']) === false) {
                        if ((strlen($extension_menu['subfatherId']) > 0)) {
                            $menu_operation[$extension_menu['fatherId']]['sub'][$extension_menu['subfatherId']]['sub2'][$extension_menu['sec2']]['text'] = __($extension_menu['name']);
                            $menu_operation[$extension_menu['fatherId']]['sub'][$extension_menu['subfatherId']]['sub2'][$extension_menu['sec2']]['id'] = str_replace(' ', '_', $extension_menu['name']);
                            $menu_operation[$extension_menu['fatherId']]['sub'][$extension_menu['subfatherId']]['sub2'][$extension_menu['sec2']]['refr'] = 0;
                            $menu_operation[$extension_menu['fatherId']]['sub'][$extension_menu['subfatherId']]['sub2'][$extension_menu['sec2']]['icon'] = $extension_menu['icon'];
                            $menu_operation[$extension_menu['fatherId']]['sub'][$extension_menu['subfatherId']]['sub2'][$extension_menu['sec2']]['sec'] = 'extensions';
                            $menu_operation[$extension_menu['fatherId']]['sub'][$extension_menu['subfatherId']]['sub2'][$extension_menu['sec2']]['extension'] = true;
                            $menu_operation[$extension_menu['fatherId']]['hasExtensions'] = true;
                        } else {
                            $menu_operation[$extension_menu['fatherId']]['sub'][$extension_menu['sec2']]['text'] = __($extension_menu['name']);
                            $menu_operation[$extension_menu['fatherId']]['sub'][$extension_menu['sec2']]['id'] = str_replace(' ', '_', $extension_menu['name']);
                            $menu_operation[$extension_menu['fatherId']]['sub'][$extension_menu['sec2']]['refr'] = 0;
                            $menu_operation[$extension_menu['fatherId']]['sub'][$extension_menu['sec2']]['icon'] = $extension_menu['icon'];
                            $menu_operation[$extension_menu['fatherId']]['sub'][$extension_menu['sec2']]['sec'] = 'extensions';
                            $menu_operation[$extension_menu['fatherId']]['sub'][$extension_menu['sec2']]['extension'] = true;
                            $menu_operation[$extension_menu['fatherId']]['hasExtensions'] = true;
                        }
                    } else {
                        $menu_operation[$extension_menu['fatherId']]['sub'][$extension_menu['sec2']]['text'] = __($extension_menu['name']);
                        $menu_operation[$extension_menu['fatherId']]['sub'][$extension_menu['sec2']]['id'] = str_replace(' ', '_', $extension_menu['name']);
                        $menu_operation[$extension_menu['fatherId']]['sub'][$extension_menu['sec2']]['refr'] = 0;
                        $menu_operation[$extension_menu['fatherId']]['sub'][$extension_menu['sec2']]['icon'] = $extension_menu['icon'];
                        $menu_operation[$extension_menu['fatherId']]['sub'][$extension_menu['sec2']]['sec'] = 'extensions';
                        $menu_operation[$extension_menu['fatherId']]['sub'][$extension_menu['sec2']]['extension'] = true;
                        $menu_operation[$extension_menu['fatherId']]['hasExtensions'] = true;
                    }
                }
            }
        }
    }
}

$menu_operation['about_operation']['text'] = __('About');
$menu_operation['about_operation']['id'] = 'about_operation';

// Save operation menu array to use in operation/extensions.php view.
$operation_menu_array = $menu_operation;


if (!$config['pure']) {
    menu_print_menu($menu_operation, true);
}
