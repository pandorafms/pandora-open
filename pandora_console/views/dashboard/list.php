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

ui_require_css_file('dashboards');


ui_print_standard_header(
    __('Dashboards'),
    '',
    false,
    '',
    true,
    [],
    [
        [
            'link'  => '',
            'label' => __('Dashboards'),
        ],
    ]
);

if (isset($resultDelete) === true) {
    \ui_print_result_message(
        $resultDelete,
        __('Successfully deleted'),
        __('Could not be deleted')
    );
}

if (isset($resultCopy) === true) {
    \ui_print_result_message(
        $resultCopy,
        __('Successfully duplicate'),
        __('Could not be duplicate')
    );
}

if (empty($dashboards) === true) {
    ui_print_info_message(
        [
            'no_close' => true,
            'message'  => __('There are no dashboards defined.'),
        ]
    );
} else {
    $id_table = 'dashboards_list';
    $columns = [
        'name',
        'cells',
        'groups',
        'favorite',
        'full_screen',
    ];

    $column_names = [
        __('Name'),
        __('Cells'),
        __('Group'),
        __('Favorite'),
        __('Full screen'),
    ];
    if ($manageDashboards === 1) {
        $columns[] = 'copy';
        $columns[] = 'delete';
        $column_names[] = __('Copy');
        $column_names[] = __('Delete');
    }

    ui_print_datatable(
        [
            'id'                  => $id_table,
            'class'               => 'info_table',
            'style'               => 'width: 100%',
            'columns'             => $columns,
            'column_names'        => $column_names,
            'ajax_url'            => 'include/ajax/dashboard.ajax',
            'ajax_data'           => [
                'method'           => 'draw',
                'urlDashboard'     => $urlDashboard,
                'manageDashboards' => $manageDashboards,
            ],
            'default_pagination'  => $config['block_size'],
            'no_sortable_columns' => [
                4,
                5,
                6,
            ],
            'order'               => [
                'field'     => 'name',
                'direction' => 'desc',
            ],
            'search_button_class' => 'sub filter float-right',
            'form'                => [
                'inputs' => [
                    [
                        'label' => __('Name'),
                        'type'  => 'text',
                        'class' => 'w80p',
                        'id'    => 'free_search',
                        'name'  => 'free_search',
                    ],
                    [
                        'label' => __('Group'),
                        'type'  => 'select_groups',
                        'id'    => 'group',
                        'name'  => 'group',
                    ],
                ],
            ],
            'filter_main_class'   => 'box-flat white_table_graph fixed_filter_bar',
            'csv'                 => false,
        ]
    );
}

$input_button = '';
if ($writeDashboards === 1) {
    $text = __('Create a new dashboard');

    // Button for display modal options dashboard.
    $onclick = 'show_option_dialog('.json_encode(
        [
            'title'      => $text,
            'btn_text'   => __('Ok'),
            'btn_cancel' => __('Cancel'),
            'url'        => $ajaxController,
            'url_ajax'   => ui_get_full_url('ajax.php'),
        ]
    );
    $onclick .= ')';

    $input_button = html_print_button(
        __('New dashboard'),
        '',
        false,
        $onclick,
        ['icon' => 'add'],
        true
    );

    if (isset($output) === false) {
        $output = '<div>';
    }

    $output .= '</div>';

    echo $output;

    // Div for modal update dashboard.
    echo '<div id="modal-update-dashboard" class="invisible"></div>';
}

if (isset($tablePagination) === false) {
    $tablePagination = '';
}

html_print_action_buttons(
    $input_button,
    [
        'type'          => 'form_action',
        'right_content' => $tablePagination,
    ]
);

ui_require_javascript_file('pandora_dashboards');
