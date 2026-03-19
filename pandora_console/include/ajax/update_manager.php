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

if ($method === 'draw') {
    // Datatables offset, limit and order.
    $filter = get_parameter('filter', []);
    $start = get_parameter('start', 0);
    $length = get_parameter('length', $config['block_size']);
    $orderBy = get_datatable_order(true);

    $sort_field = $orderBy['field'];
    $order = $orderBy['direction'];

    $pagination = '';
    if (isset($start) && $start > 0
        && isset($length) && $length >= 0
    ) {
        $pagination = sprintf(
            ' LIMIT %d OFFSET %d ',
            $start,
            $length
        );
    }

    try {
        ob_start();

        $fields = ['*'];
        $sql_filters = [];

        if (isset($filter['free_search']) === true
            && empty($filter['free_search']) === false
        ) {
            $sql_filters[] = sprintf(
                ' AND (`id_user` like "%%%s%%" OR `version` like "%%%s%%") ',
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
                    'version',
                    'type',
                    'id_user',
                    'utimestamp',
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
            FROM tupdate_journal
            WHERE 1=1
            %s
            %s
            %s',
            join(',', $fields),
            join(' ', $sql_filters),
            $order_by,
            $pagination
        );

        $return = db_get_all_rows_sql($sql);
        if ($return === false) {
            $data = [];
        } else {
            $data = $return;
        }

        // Retrieve counter.
        $count = db_get_value('count(*)', '('.$sql.') t');

        if ($data) {
            if ($config['prominent_time'] === 'timestamp') {
                $data = array_reduce(
                    $data,
                    function ($carry, $item) {
                        // Transforms array of arrays $data into an array
                        // of objects, making a post-process of certain fields.
                        $tmp = (object) $item;
                        date_default_timezone_set($user_timezone);
                        $title = human_time_comparation($tmp->utimestamp);
                        $tmp->utimestamp = '<span title="'.$title.'">'.modules_format_timestamp($tmp->utimestamp).'</span>';

                        $carry[] = $tmp;
                        return $carry;
                    }
                );
            } else if ($config['prominent_time'] === 'comparation') {
                $data = array_reduce(
                    $data,
                    function ($carry, $item) {
                        // Transforms array of arrays $data into an array
                        // of objects, making a post-process of certain fields.
                        $tmp = (object) $item;
                        date_default_timezone_set($user_timezone);
                        $title = modules_format_timestamp($tmp->utimestamp);
                        $tmp->utimestamp = '<span title="'.$title.'">'.human_time_comparation($tmp->utimestamp).'</span>';

                        $carry[] = $tmp;
                        return $carry;
                    }
                );
            } else if ($config['prominent_time'] === 'compact') {
                $data = array_reduce(
                    $data,
                    function ($carry, $item) {
                        // Transforms array of arrays $data into an array
                        // of objects, making a post-process of certain fields.
                        $tmp = (object) $item;
                        date_default_timezone_set($user_timezone);
                        $title = modules_format_timestamp($tmp->utimestamp);
                        $tmp->utimestamp = '<span title="'.$title.'">'.human_time_comparation($tmp->utimestamp, 'tiny').'</span>';

                        $carry[] = $tmp;
                        return $carry;
                    }
                );
            } else {
                $data = array_reduce(
                    $data,
                    function ($carry, $item) {
                        // Transforms array of arrays $data into an array
                        // of objects, making a post-process of certain fields.
                        $tmp = (object) $item;
                        date_default_timezone_set($user_timezone);
                        $title = modules_format_timestamp($tmp->utimestamp);
                        $tmp->utimestamp = '<span title="'.$title.'">'.human_time_comparation($tmp->utimestamp).'</span>';

                        $carry[] = $tmp;
                        return $carry;
                    }
                );
            }
        }

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
