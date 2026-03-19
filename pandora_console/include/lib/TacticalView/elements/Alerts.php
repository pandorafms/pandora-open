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

use PandoraFMS\TacticalView\Element;

/**
 * Alerts, this class contain all logic for this section.
 */
class Alerts extends Element
{


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->title = __('Alerts');
        $this->ajaxMethods = ['getUsers'];
        $this->ajaxMethods = [
            'getUsers',
            'getCurrentlyTriggered',
            'getActiveAlerts',
        ];
        $this->interval = 300000;
        $this->refreshConfig = [
            'triggered'          => [
                'id'     => 'currently-triggered',
                'method' => 'getCurrentlyTriggered',
            ],
            'active-correlation' => [
                'id'     => 'active-correlation',
                'method' => 'getActiveAlerts',
            ],
        ];
    }


    /**
     * Returns the html of currently triggered.
     *
     * @return string
     */
    public function getCurrentlyTriggered():string
    {
        $total = alerts_get_alerts(0, '', 'fired', -1, 'AR', true);
        return html_print_div(
            [
                'content' => format_numeric($total, 0),
                'class'   => 'text-l',
                'id'      => 'currently-triggered',
                'style'   => 'margin: 0px 10px 10px 10px;',
            ],
            true
        );
    }


    /**
     * Returns the html of active correlation.
     *
     * @return string
     */
    public function getActiveAlerts():string
    {
        $total = alerts_get_alerts(0, '', 'all', -1, 'AR', true, 0, true);
        return html_print_div(
            [
                'content' => format_numeric($total, 0),
                'class'   => 'text-l',
                'id'      => 'active-correlation',
                'style'   => 'margin: 0px 10px 10px 10px;',
            ],
            true
        );
    }


    /**
     * Return a datatable with de users lists.
     *
     * @return string
     */
    public function getDataTableUsers():string
    {
        $columns = [
            'id_user',
            'is_admin',
            'last_connect',
        ];

        $columnNames = [
            __('User'),
            __('Role'),
            __('Last seen'),
        ];

        return ui_print_datatable(
            [
                'id'                  => 'list_users',
                'class'               => 'info_table',
                'style'               => 'width: 90%',
                'dom_elements'        => 'tfp',
                'filter_main_class'   => 'box-flat white_table_graph fixed_filter_bar',
                'columns'             => $columns,
                'column_names'        => $columnNames,
                'ajax_url'            => $this->ajaxController,
                'ajax_data'           => [
                    'method' => 'getUsers',
                    'class'  => static::class,
                ],
                'order'               => [
                    'field'     => 'title',
                    'direction' => 'asc',
                ],
                'default_pagination'  => 10,
                'search_button_class' => 'sub filter float-right',
                'return'              => true,
            ]
        );
    }


    /**
     * Return all users for ajax.
     *
     * @return string
     */
    public function getUsers():string
    {
        global $config;

        $start  = get_parameter('start', 0);
        $length = get_parameter('length', $config['block_size']);
        $orderDatatable = get_datatable_order(true);
        $pagination = '';
        $order = '';

        try {
            ob_start();
            if (isset($orderDatatable)) {
                $order = sprintf(
                    ' ORDER BY %s %s',
                    $orderDatatable['field'],
                    $orderDatatable['direction']
                );
            }

            if (isset($length) && $length > 0
                && isset($start) && $start >= 0
            ) {
                $pagination = sprintf(
                    ' LIMIT %d OFFSET %d ',
                    $length,
                    $start
                );
            }

            $id_groups = array_keys(users_get_groups($config['id_user'], 'AR', false));
            if (in_array(0, $id_groups) === false) {
                foreach ($id_groups as $key => $id_group) {
                    if ((bool) check_acl_restricted_all($config['id_user'], $id_group, 'AR') === false) {
                        unset($id_groups[$key]);
                    }
                }
            }

            if (users_can_manage_group_all() === true) {
                $id_groups[] = 0;
            }

            $id_groups = implode(',', $id_groups);

            $sql = sprintf(
                'SELECT DISTINCT id_user, is_admin ,last_connect
                FROM tusuario u
                LEFT JOIN tusuario_perfil p ON p.id_usuario = u.id_user
                WHERE id_grupo IN ('.$id_groups.')
                GROUP BY id_user
                %s %s',
                $order,
                $pagination
            );

            $rows = db_process_sql($sql);

            foreach ($rows as $key => $row) {
                $rows[$key]['id_user'] = '<a href="index.php?sec=gusuarios&sec2=godmode/users/configure_user&edit_user=1&pure=0&id_user='.$row['id_user'].'">'.$row['id_user'].'</a>';
                if ((bool) $row['is_admin'] === true) {
                    $rows[$key]['is_admin'] = '<span class="admin">'.__('Admin').'</span>';
                } else {
                    $rows[$key]['is_admin'] = '<span class="user">'.__('User').'</span>';
                }

                if ($row['last_connect'] > 0) {
                    $rows[$key]['last_connect'] = ui_print_timestamp($row['last_connect'], true, ['prominent' => 'compact']);
                } else {
                    $rows[$key]['last_connect'] = __('Unknown');
                }
            }

            $sql_count = sprintf(
                'SELECT DISTINCT id_user, count(*) as total
                FROM tusuario u
                LEFT JOIN tusuario_perfil p ON p.id_usuario = u.id_user
                WHERE id_grupo IN ('.$id_groups.')
                %s',
                $order,
            );

            $total = db_process_sql($sql_count);

            // Capture output.
            $response = ob_get_clean();

            return json_encode(
                [
                    'data'            => $rows,
                    'recordsTotal'    => $total[0]['total'],
                    'recordsFiltered' => $total[0]['total'],
                ]
            );
        } catch (Exception $e) {
            return json_encode(['error' => $e->getMessage()]);
        }

        json_decode($response);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $response;
        } else {
            return json_encode(
                [
                    'success' => false,
                    'error'   => $response,
                ]
            );
        }
    }


    /**
     * Check if user can manager users.
     *
     * @return boolean
     */
    public function checkAclUserList():bool
    {
        global $config;
        $user_m = (bool) check_acl($config['id_user'], 0, 'UM');
        return $user_m;
    }


}
