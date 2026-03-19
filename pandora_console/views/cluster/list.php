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

// Header.
ui_print_standard_header(
    __('Cluster view'),
    'images/chart.png',
    false,
    '',
    true,
    [],
    [
        [
            'link'  => '',
            'label' => __('Monitoring'),
        ],
        [
            'link'  => '',
            'label' => __('Clusters'),
        ],
    ]
);

if (empty($message) === false) {
    echo $message;
}

// Datatables list.
try {
    $columns = [
        'name',
        'description',
        'group',
        'type',
        'nodes',
        'known_status',
        [
            'text'  => 'options',
            'class' => 'table_action_buttons',
        ],
    ];

    $column_names = [
        __('Name'),
        __('Description'),
        __('Group'),
        __('Type'),
        __('Nodes'),
        __('Status'),
        __('Options'),
    ];

    $tableId = 'clusters';

    // Load datatables user interface.
    ui_print_datatable(
        [
            'id'                  => $tableId,
            'class'               => 'info_table',
            'style'               => 'width: 100%',
            'columns'             => $columns,
            'column_names'        => $column_names,
            'ajax_url'            => $model->ajaxController,
            'ajax_data'           => ['method' => 'draw'],
            'no_sortable_columns' => [-1],
            'order'               => [
                'field'     => 'known_status',
                'direction' => 'asc',
            ],
            'search_button_class' => 'sub filter float-right',
            'form'                => [
                'inputs' => [
                    [
                        'label'          => __('Filter group'),
                        'name'           => 'id_group',
                        'returnAllGroup' => true,
                        'privilege'      => 'AR',
                        'type'           => 'select_groups',
                        'return'         => true,
                        'size'           => '250px',
                    ],
                    [
                        'label' => __('Free search'),
                        'type'  => 'text',
                        'class' => 'mw250px',
                        'id'    => 'free_search',
                        'name'  => 'free_search',
                    ],
                ],
            ],
            'filter_main_class'   => 'box-flat white_table_graph fixed_filter_bar',
        ]
    );
} catch (Exception $e) {
    echo $e->getMessage();
}

$buttons = [];
if (check_acl($config['id_user'], 0, 'AW')) {
    $buttons[] = html_print_submit_button(
        __('New cluster'),
        'submit',
        false,
        [
            'class' => 'sub ok',
            'icon'  => 'next',
        ],
        true
    );
}

echo '<form action="'.ui_get_full_url($model->url.'&op=new').'" method="POST">';
html_print_action_buttons(
    implode('', $buttons),
    ['type' => 'form_action']
);
echo '</form>';
