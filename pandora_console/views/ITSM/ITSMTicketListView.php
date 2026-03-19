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

// Includes.
require_once $config['homedir'].'/include/class/HTML.class.php';

global $config;

// Header tabs.
ui_print_standard_header(
    __('ITSM Tickets'),
    '',
    false,
    'ITSM_tab',
    false,
    $headerTabs,
    [
        [
            'link'  => 'index.php?sec=ITSM&sec2=operation/ITSM/itsm',
            'label' => __('ITSM'),
        ],
        [
            'link'  => 'index.php?sec=ITSM&sec2=operation/ITSM/itsm&operation=list',
            'label' => __('ITSM Tickets'),
        ],
    ]
);

if (empty($error) === false) {
    ui_print_error_message($error);
}

if (empty($successfullyMsg) === false) {
    ui_print_success_message($successfullyMsg);
}

try {
    $columns = [
        'idIncidence',
        'title',
        'groupCompany',
        'statusResolution',
        'priority',
        'updateDate',
        'startDate',
        'idCreator',
        'owner',
        'operation',
    ];

    $column_names = [
        __('ID'),
        __('Title'),
        __('Group').'/'.__('Company'),
        __('Status').'/'.__('Resolution'),
        __('Priority'),
        __('Updated'),
        __('Started'),
        __('Creator'),
        __('Owner'),
        [
            'text'  => __('Op.'),
            'class' => 'table_action_buttons w90px',
        ],
    ];

    ui_print_datatable(
        [
            'id'                  => 'itms_list_tickets',
            'class'               => 'info_table',
            'style'               => 'width: 99%',
            'columns'             => $columns,
            'column_names'        => $column_names,
            'ajax_url'            => $ajaxController,
            'ajax_data'           => ['method' => 'getListTickets'],
            'no_sortable_columns' => [
                2,
                3,
                -1,
            ],
            'order'               => [
                'field'     => 'updateDate',
                'direction' => 'desc',
            ],
            'search_button_class' => 'sub filter float-right',
            'form'                => [
                'inputs' => [
                    [
                        'label' => __('Free search'),
                        'type'  => 'text',
                        'id'    => 'string',
                        'name'  => 'string',
                    ],
                    [
                        'label'         => __('Status'),
                        'type'          => 'select',
                        'name'          => 'status',
                        'fields'        => $status,
                        'nothing'       => __('Any'),
                        'nothing_value' => null,
                    ],
                    [
                        'label'         => __('Priorities'),
                        'type'          => 'select',
                        'name'          => 'priority',
                        'fields'        => $priorities,
                        'nothing'       => __('Any'),
                        'nothing_value' => null,
                    ],
                    [
                        'label'         => __('Group'),
                        'type'          => 'select',
                        'name'          => 'idGroup',
                        'fields'        => $groups,
                        'nothing'       => __('Any'),
                        'nothing_value' => null,
                    ],
                    [
                        'label'         => __('Creation date'),
                        'type'          => 'interval',
                        'name'          => 'fromDate',
                        'value'         => 0,
                        'nothing'       => __('Any'),
                        'nothing_value' => 0,
                    ],
                ],
            ],
            'filter_main_class'   => 'box-flat white_table_graph fixed_filter_bar ',
        ]
    );
} catch (Exception $e) {
    echo $e->getMessage();
}

$input_button = '<form method="post" action="index.php?sec=manageTickets&sec2=operation/ITSM/itsm&operation=edit">';
$input_button .= html_print_submit_button(
    __('Create'),
    '',
    false,
    ['icon' => 'next'],
    true
);
$input_button .= '</form>';

html_print_action_buttons($input_button);
