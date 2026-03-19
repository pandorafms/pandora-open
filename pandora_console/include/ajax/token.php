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
require_once $config['homedir'].'/include/class/JWTRepository.class.php';

$list_user_tokens = (bool) get_parameter('list_user_tokens');

// Tokens for api 2.0.
if ($list_user_tokens === true) {
    // Datatables offset, limit and order.
    $filter = get_parameter('filter', []);
    $page = (int) get_parameter('start', 0);
    $pageSize = (int) get_parameter('length', $config['block_size']);
    $orderBy = get_datatable_order(true);

    $sortField = ($orderBy['field'] ?? null);
    $sortDirection = ($orderBy['direction'] ?? null);

    try {
        ob_start();

        include_once $config['homedir'].'/include/functions_token.php';
        if (isset($filter['form_token_table_search_bt']) === true) {
            unset($filter['form_token_table_search_bt']);
        }

        $return = list_user_tokens(
            ($page / $pageSize),
            $pageSize,
            $sortField,
            strtoupper($sortDirection),
            $filter
        );

        if (empty($return['data']) === false) {
            // Format end of life date.
            $return['data'] = array_map(
                function ($item) use ($config) {
                    $itemArray = $item->toArray();

                    $sec = 'gusuarios';
                    

                    $edit_url = 'index.php?sec='.$sec;
                    $edit_url .= '&sec2=godmode/users/configure_token&pure=0';
                    $edit_url .= '&id_token='.$itemArray['idToken'];

                    $delete_url = 'index.php?sec='.$sec;
                    $delete_url .= '&sec2=godmode/users/token_list';
                    $delete_url .= '&pure=0&delete_token=1';
                    $delete_url .= '&id_token='.$itemArray['idToken'];

                    $itemArray['label'] = html_print_anchor(
                        [
                            'href'    => $edit_url,
                            'content' => $itemArray['label'],
                        ],
                        true
                    );

                    if (empty($itemArray['validity']) === true) {
                        $itemArray['validity'] = __('Never');
                    } else {
                        $itemArray['validity'] = date($config['date_format'], strtotime($itemArray['validity']));
                    }

                    if (empty($itemArray['lastUsage']) === true) {
                        $itemArray['lastUsage'] = __('Never');
                    } else {
                        $itemArray['lastUsage'] = human_time_comparation($itemArray['lastUsage']);
                    }

                    $itemArray['options'] = '<div class="table_action_buttons float-right">';
                    $itemArray['options'] .= html_print_anchor(
                        [
                            'href'    => $edit_url,
                            'content' => html_print_image(
                                'images/edit.svg',
                                true,
                                [
                                    'title' => __('Show'),
                                    'class' => 'main_menu_icon invert_filter',
                                ]
                            ),
                        ],
                        true
                    );
                    $itemArray['options'] .= html_print_anchor(
                        [
                            'href'    => $delete_url,
                            'onClick' => 'if (!confirm(\' '.__('Are you sure?').'\')) return false;',
                            'content' => html_print_image(
                                'images/delete.svg',
                                true,
                                [
                                    'title' => __('Delete'),
                                    'class' => 'invert_filter main_menu_icon',
                                ]
                            ),
                        ],
                        true
                    );
                    $itemArray['options'] .= '</div>';

                    return $itemArray;
                },
                $return['data']
            );
        }

        // Datatables format: RecordsTotal && recordsfiltered.
        echo json_encode(
            [
                'data'            => $return['data'],
                'recordsTotal'    => $return['paginationData']['totalRegisters'],
                'recordsFiltered' => $return['paginationData']['totalRegisters'],
            ]
        );
        // Capture output.
        $response = ob_get_clean();
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        return;
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

    return;
}
