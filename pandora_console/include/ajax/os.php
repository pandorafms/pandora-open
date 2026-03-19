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
global $config;

check_login();
if ((bool) check_acl($config['id_user'], 0, 'PM') === false
    && (bool) is_user_admin($config['id_user']) === false
) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Setup Management'
    );
    include 'general/noaccess.php';
    return;
}

$method = get_parameter('method', null);

if ($method === 'deleteOS') {
    global $config;

    $id_os = get_parameter('id_os', null);

    if (empty($id_os) === true || $id_os < 16) {
        echo json_encode(['deleted' => false]);
        return;
    }

    if (db_process_sql_delete(
        'tconfig_os',
        ['id_os' => $id_os]
    ) === false
    ) {
        echo json_encode(['deleted' => false]);
    } else {
        echo json_encode(
            [
                'deleted'     => true,
                'url_message' => 6,
            ]
        );
    }
}

if ($method === 'deleteOSVersion') {
    global $config;

    $id_os_version = get_parameter('id_os_version', null);

    if (empty($id_os_version) === true || $id_os_version < 1) {
        echo json_encode(['deleted' => false]);
    }

    if (db_process_sql_delete(
        'tconfig_os_version',
        ['id_os_version' => $id_os_version]
    ) === false
    ) {
        echo json_encode(['deleted' => false]);
    } else {
        echo json_encode(['deleted' => true]);
    }
}

if ($method === 'drawOSTable') {
    // Datatables offset, limit and order.
    $filter = get_parameter('filter', []);
    $start = get_parameter('start', 0);
    $length = get_parameter('length', $config['block_size']);
    $orderBy = get_datatable_order(true);

    $sort_field = $orderBy['field'];
    $order = $orderBy['direction'];

    $pagination = '';

    $pagination = sprintf(
        ' LIMIT %d OFFSET %d ',
        $length,
        $start
    );

    try {
        ob_start();

        $fields = ['*'];
        $sql_filters = [];

        if (isset($filter['free_search']) === true
            && empty($filter['free_search']) === false
        ) {
            $sql_filters[] = sprintf(
                ' AND (`name` like "%%%s%%" OR `description` like "%%%s%%") ',
                $filter['free_search'],
                $filter['free_search']
            );
        }

        if (isset($order) === true) {
            $dir = 'asc';
            if ($order == 'desc') {
                $dir = 'desc';
            };

            if (in_array(
                $sort_field,
                [
                    'id_os',
                    'name',
                    'description',
                ]
            ) === true
            ) {
                $order_by = sprintf(
                    'ORDER BY `%s` %s',
                    $sort_field,
                    $dir
                );
            }
        }

        // Retrieve data.
        $sql = sprintf(
            'SELECT %s
            FROM tconfig_os
            WHERE 1=1
            %s
            %s
            %s',
            join(',', $fields),
            join(' ', $sql_filters),
            $order_by,
            $pagination
        );

        $count_sql = sprintf(
            'SELECT id_os
            FROM tconfig_os
            WHERE 1=1
            %s',
            join(' ', $sql_filters)
        );

        $return = db_get_all_rows_sql($sql);
        if ($return === false) {
            $data = [];
        } else {
            $data = $return;
        }

        $data = array_map(
            function ($item) {
                $item['icon_img'] = ui_print_os_icon($item['id_os'], false, true);

                $osNameUrl = 'index.php?sec=gsetup&sec2=godmode/setup/os&action=edit&tab=manage_os&id_os='.$item['id_os'];
                

                $item['name'] = html_print_anchor(
                    [
                        'href'    => $osNameUrl,
                        'content' => $item['name'],
                    ],
                    true
                );

                $item['description'] = ui_print_truncate_text(
                    $item['description'],
                    'description',
                    true,
                    true
                );

                $item['enable_delete'] = false;

                if ($item['id_os'] > 16) {
                    $item['enable_delete'] = true;
                }

                return $item;
            },
            $data
        );

        // Retrieve counter.
        $count = db_get_value('count(*)', '('.$count_sql.') t');

        // Datatables format: RecordsTotal && recordsfiltered.
        echo json_encode(
            [
                'data'            => $data,
                'recordsTotal'    => $count,
                'recordsFiltered' => $count,
            ]
        );
        // Capture output.
        $response = ob_get_clean();
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }

    // If not valid, show error with issue.
    json_decode($response);
    if (json_last_error() == JSON_ERROR_NONE) {
        // If valid dump.
        echo $response;
    } else {
        echo json_encode(
            ['error' => $response]
        );
    }

    exit;
}

if ($method === 'drawOSVersionTable') {
    // Datatables offset, limit and order.
    $filter = get_parameter('filter', []);
    $start = get_parameter('start', 0);
    $length = get_parameter('length', $config['block_size']);
    $orderBy = get_datatable_order(true);

    $sort_field = $orderBy['field'];
    $order = $orderBy['direction'];

    $pagination = '';

    $pagination = sprintf(
        ' LIMIT %d OFFSET %d ',
        $length,
        $start
    );

    try {
        ob_start();

        $fields = ['*'];
        $sql_filters = [];

        if (isset($filter['free_search']) === true
            && empty($filter['free_search']) === false
        ) {
            $sql_filters[] = sprintf(
                ' AND (`product` like "%%%s%%" OR `version` like "%%%s%%") ',
                $filter['free_search'],
                $filter['free_search']
            );
        }

        if (isset($order) === true) {
            $dir = 'asc';
            if ($order == 'desc') {
                $dir = 'desc';
            };

            if (in_array(
                $sort_field,
                [
                    'product',
                    'version',
                    'end_of_support',
                ]
            ) === true
            ) {
                $order_by = sprintf(
                    'ORDER BY `%s` %s',
                    $sort_field,
                    $dir
                );
            }
        }

        // Retrieve data.
        $sql = sprintf(
            'SELECT %s
            FROM tconfig_os_version
            WHERE 1=1
            %s
            %s
            %s',
            join(',', $fields),
            join(' ', $sql_filters),
            $order_by,
            $pagination
        );

        $count_sql = sprintf(
            'SELECT id_os_version
            FROM tconfig_os_version
            WHERE 1=1
            %s',
            join(' ', $sql_filters)
        );

        $return = db_get_all_rows_sql($sql);

        if ($return === false) {
            $data = [];
        } else {
            // Format end of life date.
            $return = array_map(
                function ($item) {
                    $date_string = date_w_fixed_tz($item['end_of_support']);
                    $timestamp = strtotime($date_string);
                    $date_without_time = date('F j, Y', $timestamp);
                    $item['end_of_support'] = $date_without_time;
                    return $item;
                },
                $return
            );

            $data = $return;
        }

        // Retrieve counter.
        $count = db_get_value('count(*)', '('.$count_sql.') t');

        // Datatables format: RecordsTotal && recordsfiltered.
        echo json_encode(
            [
                'data'            => $data,
                'recordsTotal'    => $count,
                'recordsFiltered' => $count,
            ]
        );
        // Capture output.
        $response = ob_get_clean();
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }

    // If not valid, show error with issue.
    json_decode($response);
    if (json_last_error() == JSON_ERROR_NONE) {
        // If valid dump.
        echo $response;
    } else {
        echo json_encode(
            ['error' => $response]
        );
    }

    exit;
}
