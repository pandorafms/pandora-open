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
ui_require_css_file('tree');
ui_require_css_file('fixed-bottom-box');

// Load global vars.
global $config;

check_login();

require_once $config['homedir'].'/include/functions_groups.php';
require_once $config['homedir'].'/include/functions_agents.php';
require_once $config['homedir'].'/include/functions_users.php';



if (is_ajax() === true) {
    if ((bool) check_acl($config['id_user'], 0, 'AR') === false) {
        db_pandora_audit(
            AUDIT_LOG_ACL_VIOLATION,
            'Trying to access Group Management'
        );
        include 'general/noaccess.php';
        return;
    }

    $get_group_json = (bool) get_parameter('get_group_json');
    $get_group_agents = (bool) get_parameter('get_group_agents');
    $get_is_disabled = (bool) get_parameter('get_is_disabled');

    if ($get_group_json === true) {
        $id_group = (int) get_parameter('id_group');

        if ($id_group === 0 || $id_group === -1) {
            $group = [
                'id_grupo'  => 0,
                'nombre'    => 'None',
                'icon'      => 'world@svg.svg',
                'parent'    => 0,
                'disabled'  => 0,
                'custom_id' => null,
            ];
            echo json_encode($group);
            return;
        }

        if ((bool) check_acl($config['id_user'], $id_group, 'AR') === false) {
            db_pandora_audit(
                AUDIT_LOG_ACL_VIOLATION,
                'Trying to access Alert Management'
            );
            echo json_encode(false);
            return;
        }

        $group = db_get_row('tgrupo', 'id_grupo', $id_group);
        if (str_contains($group['icon'], '.png') === true) {
            $group['folder'] = 'groups_small/';
        }

        echo json_encode($group);
        return;
    }

    if ($get_group_agents === true) {
        ob_clean();
        $id_group = (int) get_parameter('id_group');
        $id_os = (int) get_parameter('id_os', 0);
        $disabled = (int) get_parameter('disabled', 0);
        $search = (string) get_parameter('search', '');
        $recursion = (int) get_parameter('recursion', 0);
        $privilege = (string) get_parameter('privilege', '');
        $all_agents = (int) get_parameter('all_agents', 0);
        // Is is possible add keys prefix to avoid auto sorting in
        // js object conversion.
        $keys_prefix = (string) get_parameter('keys_prefix', '');
        // This attr is for the operation "bulk alert accions add", it controls
        // the query that take the agents from db.
        $add_alert_bulk_op = get_parameter('add_alert_bulk_op', false);
        // Ids of agents to be include in the SQL clause as id_agent IN ().
        $filter_agents_json = (string) get_parameter('filter_agents_json', '');
        $status_agents = (int) get_parameter('status_agents', AGENT_STATUS_ALL);
        $os_agent = (int) get_parameter('os_agent', 0);
        $os_agent_version = (string) get_parameter('os_agent_version', '');
        // Juanma (22/05/2014) Fix: If setted remove void agents from result
        // (by default and for compatibility show void agents).
        $show_void_agents = (int) get_parameter('show_void_agents', 1);
        $serialized = (bool) get_parameter('serialized', false);
        $serialized_separator = (string) get_parameter(
            'serialized_separator',
            '|'
        );
        $force_serialized = (bool) get_parameter('force_serialized', false);
        $nodes = (array) get_parameter('nodes', []);

        if ((bool) check_acl($config['id_user'], $id_group, 'AR') === false) {
            db_pandora_audit(
                AUDIT_LOG_ACL_VIOLATION,
                'Trying to access Alert Management'
            );
            echo json_encode(false);
            return;
        }

        if (https_is_running() === true) {
            header('Content-type: application/json');
        }

        if ($filter_agents_json != '') {
            $filter['id_agente'] = json_decode(
                io_safe_output($filter_agents_json),
                true
            );
        }

        if ($all_agents) {
            $filter['all_agents'] = true;
        } else {
            $filter['disabled'] = $disabled;
        }

        if ($search != '') {
            $filter['aliasRegex'] = $search;
        }

        if ($status_agents != AGENT_STATUS_ALL) {
            $filter['status'] = $status_agents;
        }

        if ($os_agent !== 0) {
            $filter['id_os'] = $os_agent;
        }

        if ($os_agent_version !== '') {
            $filter['os_version'] = $os_agent_version;
        }

        if ($id_os !== 0) {
            $filter['id_os'] = $id_os;
        }

        $_sql_post = ' 1=1 ';
        if ($show_void_agents == 0) {
            $_sql_post .= ' AND id_agente IN (SELECT a.id_agente FROM tagente a, tagente_modulo b WHERE a.id_agente=b.id_agente AND b.delete_pending=0) AND \'1\'';
            $filter[$_sql_post] = '1';
        }

        

        $id_groups_get_agents = $id_group;
        if ($id_group == 0 && $privilege != '') {
            $groups = users_get_groups($config['id_user'], $privilege, false);
            // If group ID doesn't matter and $privilege is specified
            // (like 'AW'), retruns all agents that current user has $privilege
            // privilege for.
            $id_groups_get_agents = array_keys($groups);
        }

        $agents = agents_get_group_agents(
            $id_groups_get_agents,
            $filter,
            'none',
            false,
            $recursion,
            $serialized_separator,
            $add_alert_bulk_op,
            $force_serialized
        );

        $agents_aux = [];
        foreach ($agents as $key => $value) {
            if (empty($search) === true) {
                $agents_aux[$key] = $value;
            } else if (preg_match('/'.$search.'/', io_safe_output($value)) === true) {
                $agents_aux[$key] = $value;
            }
        }

        $agents = $agents_aux;

        $agents_disabled = [];
        // Add keys prefix.
        if ($keys_prefix !== '') {
            foreach ($agents as $k => $v) {
                $agents[$keys_prefix.$k] = $v;
                unset($agents[$k]);
                if ($all_agents) {
                    // Unserialize to get the status.
                    if ($serialized
                        && $force_serialized
                    ) {
                        $agent_info = explode($serialized_separator, $k);
                        $agent_disabled = db_get_value_filter(
                            'disabled',
                            'tagente',
                            ['id_agente' => $agent_info[1]]
                        );
                    } else {
                        $agent_disabled = db_get_value_filter(
                            'disabled',
                            'tagente',
                            ['id_agente' => $k]
                        );
                    }

                    $agents_disabled[$keys_prefix.$k] = $agent_disabled;
                }
            }
        }

        if ($all_agents) {
            $all_agents_array = [];
            $all_agents_array['agents'] = $agents;
            $all_agents_array['agents_disabled'] = $agents_disabled;

            $agents = $all_agents_array;
        }

        echo json_encode($agents);
        return;
    }

    if ($get_is_disabled === true) {
        $index = get_parameter('id_agent');

        $agent_disabled = db_get_value_filter(
            'disabled',
            'tagente',
            ['id_agente' => $index]
        );

        $return['disabled'] = $agent_disabled;
        $return['id_agent'] = $index;

        echo json_encode($return);
        return;
    }

    return;
}

$tab = (string) get_parameter('tab', 'groups');

if ($tab !== 'credbox'
    && (bool) check_acl($config['id_user'], 0, 'PM') === false
    && (bool) check_acl($config['id_user'], 0, 'AW') === false
) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Group Management'
    );
    include 'general/noaccess.php';
    return;
} else if ($tab === 'credbox'
    && (bool) check_acl($config['id_user'], 0, 'UM') === false
    && (bool) check_acl($config['id_user'], 0, 'PM') === false
) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Credential Store'
    );
    include 'general/noaccess.php';
    return;
}

$sec = 'gagente';
$url_credbox  = 'index.php?sec=gmodules&sec2=godmode/groups/group_list&tab=credbox';
$url_tree  = 'index.php?sec='.$sec.'&sec2=godmode/groups/group_list&tab=tree';
$url_groups = 'index.php?sec='.$sec.'&sec2=godmode/groups/group_list&tab=groups';

$buttons['tree'] = [
    'active' => false,
    'text'   => '<a href="'.$url_tree.'">'.html_print_image(
        'images/snmp-trap@svg.svg',
        true,
        [
            'title' => __('Tree Group view'),
            'class' => 'main_menu_icon invert_filter',
        ]
    ).'</a>',
];

$buttons['groups'] = [
    'active' => false,
    'text'   => '<a href="'.$url_groups.'">'.html_print_image(
        'images/groups@svg.svg',
        true,
        [
            'title' => __('Group view'),
            'class' => 'main_menu_icon invert_filter',
        ]
    ).'</a>',
];

$buttons['credbox'] = [
    'active' => false,
    'text'   => '<a href="'.$url_credbox.'">'.html_print_image(
        'images/key.png',
        true,
        [
            'title' => __('Credential Store'),
            'class' => 'main_menu_icon invert_filter',
        ]
    ).'</a>',
];

$title = __('Groups defined in %s', get_product_name());
// Marks correct tab.
switch ($tab) {
    case 'tree':
        $buttons['tree']['active'] = true;
        $title .= sprintf(' &raquo; %s', __('Tree view'));
    break;

    case 'credbox':
        $buttons['credbox']['active'] = true;
        $title = __('Credential store');
    break;

    case 'groups':
    default:
        $buttons['groups']['active'] = true;
        $title .= sprintf(' &raquo; %s', __('Table view'));
    break;
}

// Header.
ui_print_standard_header(
    $title,
    'images/group.png',
    false,
    '',
    false,
    $buttons,
    [
        [
            'link'  => '',
            'label' => __('Profiles'),
        ],
        [
            'link'  => '',
            'label' => __('Manage agents group'),
        ],
    ]
);


$is_management_allowed = true;

// Load credential store view before parse list-tree forms.
if ($tab == 'credbox') {
    include_once __DIR__.'/credential_store.php';
    // Stop script.
    return;
}

$create_group = (bool) get_parameter('create_group');
$update_group = (bool) get_parameter('update_group');
$delete_group = (bool) get_parameter('delete_group');
$pure = get_parameter('pure', 0);

// Create group.
if ($create_group === true
    && ((bool) check_acl($config['id_user'], 0, 'PM') === true)
) {
    $name = (string) get_parameter('name');
    $icon = (string) get_parameter('icon');
    $id_parent = (int) get_parameter('id_parent');
    $group_pass = (string) get_parameter('group_pass');
    $alerts_disabled = (bool) get_parameter_switch('alerts_enabled', 0);
    $custom_id = (string) get_parameter('custom_id');
    $description = (string) get_parameter('description');
    $contact = (string) get_parameter('contact');
    $other = (string) get_parameter('other');
    $max_agents = (int) get_parameter('max_agents', 0);
    $check = db_get_value('nombre', 'tgrupo', 'nombre', $name);
    $propagate = (bool) get_parameter('propagate');

    $aviable_name = true;
    if (preg_match('/script/i', $name)) {
        $aviable_name = false;
    }

    // Check if name field is empty.
    if ($name != '') {
        if (!$check) {
            if ($aviable_name === true) {
                $values = [
                    'nombre'      => $name,
                    'icon'        => $icon,
                    'parent'      => $id_parent,
                    'disabled'    => !$alerts_disabled,
                    'custom_id'   => $custom_id,
                    'description' => $description,
                    'contact'     => $contact,
                    'propagate'   => $propagate,
                    'other'       => $other,
                    'password'    => io_safe_input($group_pass),
                    'max_agents'  => $max_agents,
                ];

                $result = db_process_sql_insert('tgrupo', $values);
            }

            if ($result) {
                ui_print_success_message(__('Group successfully created'));
            } else {
                ui_print_error_message(__('There was a problem creating group'));
            }
        } else {
            ui_print_error_message(__('Each group must have a different name'));
        }
    } else {
        ui_print_error_message(__('Group must have a name'));
    }
}

// Update group.
if ($update_group === true) {
    $id_group = (int) get_parameter('id_group');
    $name = (string) get_parameter('name');
    $icon = (string) get_parameter('icon');
    $id_parent = (int) get_parameter('id_parent');
    $description = (string) get_parameter('description');
    $group_pass = (string) get_parameter('group_pass');
    $alerts_enabled = (bool) get_parameter_switch('alerts_enabled', 0);
    $custom_id = (string) get_parameter('custom_id');
    $propagate = (bool) get_parameter('propagate');
    $description = (string) get_parameter('description');
    $contact = (string) get_parameter('contact');
    $other = (string) get_parameter('other');
    $max_agents = (int) get_parameter('max_agents', 0);

    $aviable_name = true;
    if (preg_match('/script/i', $name)) {
        $aviable_name = false;
    }

    // Check if group name is unique.
    $check = db_get_value_filter(
        'nombre',
        'tgrupo',
        [
            'nombre'   => $name,
            'id_grupo' => $id_group,
        ],
        'AND NOT'
    );

    // Check if name field is empty.
    if ($name != '') {
        if (!$check) {
            if ($aviable_name === true) {
                $values = [
                    'nombre'      => $name,
                    'icon'        => $icon,
                    'parent'      => ($id_parent == -1) ? 0 : $id_parent,
                    'disabled'    => !$alerts_enabled,
                    'custom_id'   => $custom_id,
                    'description' => $description,
                    'contact'     => $contact,
                    'propagate'   => $propagate,
                    'other'       => $other,
                    'password'    => io_safe_input($group_pass),
                    'max_agents'  => $max_agents,
                ];

                $result = db_process_sql_update(
                    'tgrupo',
                    $values,
                    ['id_grupo' => $id_group]
                );
            }

            if ($result) {
                ui_update_name_fav_element($id_group, 'Groups', $name);
                ui_print_success_message(__('Group successfully updated'));
            } else {
                ui_print_error_message(__('There was a problem modifying group'));
            }
        } else {
            ui_print_error_message(__('Each group must have a different name'));
        }
    } else {
        ui_print_error_message(__('Group must have a name'));
    }
}

$id_group = (int) get_parameter('id_group');

// Delete group.
if ($delete_group === true
    && ((bool) check_acl($config['id_user'], $id_group, 'PM') === true)
) {
    $usedGroup = groups_check_used($id_group);

    if (!$usedGroup['return']) {
        $group = db_get_row_filter(
            'tgrupo',
            ['id_grupo' => $id_group]
        );

        db_process_sql_update(
            'tgrupo',
            ['parent' => $group['parent']],
            ['parent' => $id_group]
        );

        $result = db_process_sql_delete(
            'tgroup_stat',
            ['id_group' => $id_group]
        );

        $result = db_process_sql_delete(
            'tgrupo',
            ['id_grupo' => $id_group]
        );

        $result_user_profile = db_process_sql_delete(
            'tusuario_perfil',
            ['id_grupo' => $id_group]
        );

        if ($result && (!$usedGroup['return'])) {
            db_process_sql_delete(
                'tfavmenu_user',
                [
                    'id_element' => $id_group,
                    'section'    => 'Groups',
                    'id_user'    => $config['id_user'],
                ]
            );
            ui_print_success_message(__('Group successfully deleted'));
        } else {
            ui_print_error_message(
                __('There was a problem deleting group')
            );
        }
    } else {
        ui_print_error_message(
            sprintf(
                __('The group is not empty. It is use in %s.'),
                implode(', ', $usedGroup['tables'])
            )
        );
    }
}

// Credential store is loaded previously in this document to avoid
// process group tree - list forms.
ui_print_spinner(__('Loading'));
if ($tab == 'tree') {
    /*
     * Group tree view.
     */

    $table = new stdClass();
    $table->width = '100%';
    $table->class = 'databox filters filter-table-adv';
    

    $search_group_string = get_parameter('search_group_string', '');
    $show_full_hirearchy = get_parameter('show_full_hirearchy', 0);

    $table->data = [];
    $table->head = [];
    $table->style = [];

    $table->style[0] = 'width: 50%;';
    $table->style[1] = 'width: 50%;';

    $table->data[0][0] = html_print_label_input_block(
        __('Search group'),
        html_print_input_text(
            'search_group_string',
            $search_group_string,
            '',
            25,
            255,
            true,
            false,
            false,
            '',
        )
    );

    $table->data[0][1] = html_print_label_input_block(
        __('Show full hierarchy'),
        html_print_checkbox_switch_extended(
            'show_full_hirearchy',
            1,
            $show_full_hirearchy,
            false,
            '',
            '',
            true
        )
    );

    $table->data[0][2] .= '&nbsp;';
    $table->data[0][2] .= html_print_submit_button(
        __('Filter'),
        'filter',
        false,
        [
            'class' => 'float-right',
            'icon'  => 'search',
        ],
        true
    );

    $form = "<form method='post' action=''>";
    $form .= html_print_table($table, true);
    $form .= '</form>';

    ui_toggle(
        $form,
        '<span class="subsection_header_title">'.__('Filters').'</span>',
        'filter_form',
        '',
        true,
        false,
        '',
        'white-box-content',
        'box-flat white_table_graph fixed_filter_bar'
    );


    echo "<div id='tree-controller-recipient'></div>";
    if (users_can_manage_group_all('AR') === false) {
        $user_groups_acl = users_get_groups(false, 'AR');
        $groups_acl = implode('","', $user_groups_acl);
        if (empty($groups_acl) === true) {
            $is_management_allowed = false;
        }
    }

    html_print_div(
        [
            'content' => ui_print_info_message(
                [
                    'no_close' => true,
                    'message'  => __('There are no defined groups'),
                ],
                '',
                true
            ),
            'class'   => 'invisible',
            'id'      => 'message-tree-info',
        ]
    );
} else {
    /*
     * Group list view.
     */

    $acl = '';
    $search_name = '';
    $offset = (int) get_parameter('offset', 0);
    $search = (string) get_parameter('search', '');
    $block_size = $config['block_size'];

    $tablePagination = '';

    if (empty($search) === false) {
        $search_name = 'AND t.nombre LIKE "%'.$search.'%"';
    }

    if (users_can_manage_group_all('AR') === false) {
        $user_groups_acl = users_get_groups(false, 'AR');
        $groups_acl = implode('","', $user_groups_acl);
        if (empty($groups_acl) === true) {
            return ui_print_info_message(
                [
                    'no_close' => true,
                    'message'  => __('There are no defined groups'),
                ]
            );
        }

        $acl = 'AND t.nombre IN ("'.$groups_acl.'")';
    }

    $form = "<form method='post' action=''>";
        $form .= "<table class='filter-table-adv' width='100%'>";
            $form .= '<tr><td>'.html_print_label_input_block(
                __('Search'),
                html_print_input_text(
                    'search',
                    $search,
                    '',
                    30,
                    30,
                    true
                )
            );
            $form .= '</td>';
            $form .= '</tr>';
        $form .= '</table>';
        $buttons = html_print_submit_button(
            __('Filter'),
            'find',
            false,
            [
                'icon' => 'search',
                'mode' => 'mini',
            ],
            true
        );

        $form .= html_print_div(
            [
                'class'   => 'action-buttons',
                'content' => $buttons,
            ],
            true
        );
    $form .= '</form>';



    ui_toggle(
        $form,
        '<span class="subsection_header_title">'.__('Filters').'</span>',
        'filter_form',
        '',
        true,
        false,
        '',
        'white-box-content',
        'box-flat white_table_graph fixed_filter_bar'
    );

    

    $groups_sql = sprintf(
        'SELECT t.*,
			p.nombre  AS parent_name,
			IF(t.parent=p.id_grupo, 1, 0) AS has_child
		 FROM tgrupo t
		 LEFT JOIN tgrupo p
			ON t.parent=p.id_grupo
		 WHERE 1=1
		 %s
         %s
		ORDER BY nombre
        LIMIT %d, %d',
        $acl,
        $search_name,
        $offset,
        $block_size
    );

    $groups = db_get_all_rows_sql($groups_sql);

    if (empty($groups) === false) {
        // Count all groups for pagination only saw user and filters.
        $groups_sql_count = sprintf(
            'SELECT count(*)
			FROM tgrupo t
			WHERE 1=1
            %s
            %s',
            $acl,
            $search_name
        );
        $groups_count = db_get_value_sql($groups_sql_count);

        $table = new StdClass();
        $table->width = '100%';
        $table->class = 'info_table';
        $table->headstyle = [];
        $table->head = [];
        $table->head[0] = __('ID');
        $table->headstyle[0] = 'min-width: 100px;';
        $table->head[1] = __('Name');
        $table->headstyle[1] = 'min-width: 100px;';
        $table->head[2] = __('Icon');
        $table->headstyle[2] = 'min-width: 100px;';
        $table->head[3] = __('Alerts');
        $table->headstyle[3] = 'min-width: 100px;';
        $table->head[4] = __('Parent');
        $table->headstyle[4] = 'min-width: 100px;';
        $table->head[5] = __('Description');
        $table->headstyle[5] = 'min-width: 100px;';
        if ($is_management_allowed === true) {
            $table->head[6] = __('Actions');
            $table->headstyle[6] = 'min-width: 100px;';
        }

        $table->align = [];
        $table->align[0] = 'left';
        $table->align[2] = 'left';
        if ($is_management_allowed === true) {
            $table->align[6] = 'left';
        }

        $table->size[0] = '3%';
        $table->size[5] = '30%';
        if ($is_management_allowed === true) {
            $table->size[6] = '5%';
        }

        $table->data = [];

        foreach ($groups as $key => $group) {
            $url_edit = 'index.php?sec=gagente&sec2=godmode/groups/configure_group&id_group='.$group['id_grupo'];

                $url_delete = 'index.php?sec=gagente&sec2=godmode/groups/group_list&delete_group=1&id_group='.$group['id_grupo'];
            

            $table->data[$key][0] = $group['id_grupo'];
            if ($is_management_allowed === true) {
                $table->data[$key][1] = '<a href="'.$url_edit.'">'.$group['nombre'].'</a>';
            } else {
                $table->data[$key][1] = $group['nombre'];
            }

            if ($group['icon'] != '') {
                $extension = pathinfo($group['icon'], PATHINFO_EXTENSION);
                if (empty($extension) === true) {
                    $group['icon'] .= '.png';
                }

                if (empty($extension) === true || $extension === 'png') {
                    $path = 'images/groups_small/'.$group['icon'];
                } else {
                    $path = 'images/'.$group['icon'];
                }

                $table->data[$key][2] = html_print_image(
                    $path,
                    true,
                    [
                        'style' => '',
                        'class' => 'bot main_menu_icon invert_filter',
                        'alt'   => io_safe_input($group['nombre']),
                        'title' => io_safe_input($group['nombre']),
                    ],
                    false,
                    false,
                    false,
                    true
                );
            } else {
                $table->data[$key][2] = '';
            }


            // Reporting_get_group_stats.
            $table->data[$key][3] = ($group['disabled'] === '1') ? __('Disabled') : __('Enabled');
            $table->data[$key][4] = $group['parent_name'];
            $table->data[$key][5] = $group['description'];
            if ($is_management_allowed === true) {
                $table->cellclass[$key][6] = 'table_action_buttons';
                $table->data[$key][6] = '<a href="'.$url_edit.'">'.html_print_image(
                    'images/edit.svg',
                    true,
                    [
                        'alt'   => __('Edit'),
                        'title' => __('Edit'),
                        'class' => 'main_menu_icon invert_filter',
                    ]
                ).'</a>';

                    $confirm_message = __('Are you sure?');
                

                if ($group['has_child']) {
                    $confirm_message = __('The child groups will be updated to use the parent id of the deleted group').'. '.$confirm_message;
                }

                $table->data[$key][6] .= '<a href="'.$url_delete.'" onClick="event.preventDefault(); return preprocessDeletion('.$group['id_grupo'].', \''.$url_delete.'\',\''.$confirm_message.'\');">'.html_print_image(
                    'images/delete.svg',
                    true,
                    [
                        'alt'   => __('Delete'),
                        'title' => __('Delete'),
                        'class' => 'main_menu_icon invert_filter',
                    ]
                ).'</a>';
            }
        }

        html_print_table($table);
        $tablePagination = ui_pagination(
            $groups_count,
            false,
            $offset,
            $block_size,
            true,
            'offset',
            false,
            ''
        );
    } else {
        ui_print_info_message(
            [
                'no_close' => true,
                'message'  => __('There are no defined groups'),
            ]
        );
    }
}

$button_form = '';
if ($is_management_allowed === true
    && (bool) check_acl($config['id_user'], 0, 'PM') === true
) {
    $button_form = '<form method="post" action="index.php?sec='.$sec.'&sec2=godmode/groups/configure_group">';
    $button_form .= html_print_submit_button(
        __('Create group'),
        'crt',
        false,
        ['icon' => 'next'],
        true
    );
    $button_form .= '</form>';
}


html_print_action_buttons(
    $button_form,
    [
        'type'          => 'data_table',
        'class'         => 'fixed_action_buttons',
        'right_content' => $tablePagination,
    ]
);


ui_require_javascript_file('TreeController', 'include/javascript/tree/');

$tab = 'group_edition';

?>


<script type="text/javascript" src="include/javascript/fixed-bottom-box.js"></script>

<script type="text/javascript">

    let show_full_hirearchy = "<?php echo (isset($show_full_hirearchy) === true) ? $show_full_hirearchy : ''; ?>";
    let search_group_string = "<?php echo (isset($search_group_string) === true) ? $search_group_string : ''; ?>";

    $('#checkbox-show_full_hirearchy').on("change", function() { 
        if (show_full_hirearchy == 1) {
            show_full_hirearchy = 0;
        } else {
            show_full_hirearchy = 1;
        }
    });

    $('#button-filter').on('click', function(event) {
        event.preventDefault();

        load_tree(show_full_hirearchy);
    });

    window.addEventListener('load', function() {
        load_tree(show_full_hirearchy);
    });


    

    function load_tree(show_full_hirearchy) {
        var treeController = TreeController.getController();
        treeController.meta = <?php echo 0; ?>;

        if (typeof treeController.recipient != 'undefined' && treeController.recipient.length > 0)
                treeController.recipient.empty();

            showSpinner();

            var parameters = {};
            parameters['page'] = "include/ajax/tree.ajax";
            parameters['getChildren'] = 1;
            parameters['type'] = "<?php echo $tab; ?>";
            parameters['filter'] = {};
            parameters['filter']['searchHirearchy'] = show_full_hirearchy;
            parameters['filter']['searchGroup'] = $('#text-search_group_string').val();

            $.ajax({
                type: "POST",
                url: "<?php echo ui_get_full_url('ajax.php', false, false, false); ?>",
                data: parameters,
                success: function(data) {
                    if (data.success) {
                        hideSpinner();

                        treeController.init({
                            recipient: $("div#tree-controller-recipient"),
                            page: parameters['page'],
                            emptyMessage: $("#message-tree-info").html(),
                            foundMessage: "<?php echo __('Found groups'); ?>",
                            tree: data.tree,
                            baseURL: "<?php echo ui_get_full_url(false, false, false, false); ?>",
                            ajaxURL: "<?php echo ui_get_full_url('ajax.php', false, false, false); ?>",
                            filter: parameters['filter']
                        });
                    }
                },
                dataType: "json"
            });
    }

    function preprocessDeletion(group_id, delete_URL, confirm_text) {
        var parameters = {};
        parameters['page'] = 'include/ajax/group';
        parameters['method'] = 'checkGroupIsLinkedToElement';
        parameters['group_id'] = group_id;
        parameters['table_name'] = 'tusuario_perfil';
        parameters['field_name'] = 'id_grupo';

        $.ajax({
                type: "POST",
                url: "<?php echo ui_get_full_url('ajax.php', false, false, false); ?>",
                data: parameters,
                success: function(data) {
                    if (data.result == '1') {
                        confirmDialog({
                            title: '<?php echo __('Are you sure?'); ?>',
                            message: '<?php echo __('There are user profiles assigned to this group which will be deleted. Note that a user with no associated profiles will not be able to log in. Please ensure you want to proceed with the deletion.'); ?>',
                            onAccept: function() {
                                window.location.assign(delete_URL);
                            }
                        });
                    } else {
                        if (!confirm(confirm_text)) {
                            return false;
                        } else {
                            window.location.assign(delete_URL);
                        }
                    }
                },
                dataType: "json"
            });

            return true;
        }
    
</script>
