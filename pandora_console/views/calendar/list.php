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

\ui_require_css_file('wizard');

// Header.
ui_print_standard_header(
    __('Alerts'),
    'images/gm_alerts.png',
    false,
    'alert_special_days',
    true,
    $tabs,
    [
        [
            'link'  => '',
            'label' => __('Alerts'),
        ],
        [
            'link'  => '',
            'label' => __('Special days'),
        ],
    ]
);

if (empty($message) === false) {
    echo $message;
}

// Datatables list.
try {
    $columns = [
        [
            'text'  => 'id',
            'class' => 'invisible',
        ],
        'name',
        'id_group',
        'description',
        [
            'text'  => 'options',
            'class' => 'w150px table_action_buttons',
        ],
    ];

    $column_names = [
        __('ID'),
        __('Name'),
        __('Group'),
        __('Description'),
        __('Options'),
    ];

    $tableId = 'calendar_list';
    // Load datatables user interface.
    ui_print_datatable(
        [
            'id'                  => $tableId,
            'class'               => 'info_table',
            'style'               => 'width: 100%',
            'columns'             => $columns,
            'column_names'        => $column_names,
            'ajax_url'            => $ajax_url,
            'ajax_data'           => ['method' => 'drawListCalendar'],
            'no_sortable_columns' => [-1],
            'order'               => [
                'field'     => 'id',
                'direction' => 'asc',
            ],
            'search_button_class' => 'sub filter float-right',
            'form'                => [
                'inputs' => [
                    [
                        'label' => __('Free search'),
                        'type'  => 'text',
                        'class' => 'w25p',
                        'id'    => 'free_search',
                        'name'  => 'free_search',
                    ],
                ],
            ],
            'filter_main_class'   => 'box-flat white_table_graph fixed_filter_bar',
            'dom_elements'        => 'lftpB',
        ]
    );
} catch (Exception $e) {
    echo $e->getMessage();
}

if ((bool) check_acl($config['id_user'], 0, 'LM') === true) {
    $form_create = HTML::printForm(
        [
            'form'   => [
                'action' => $url.'&op=edit',
                'method' => 'POST',
            ],
            'inputs' => [
                [
                    'arguments' => [
                        'name'       => 'button',
                        'label'      => __('Create'),
                        'type'       => 'submit',
                        'attributes' => ['icon' => 'wand'],
                    ],
                ],
            ],
        ],
        true
    );
    html_print_action_buttons($form_create);
}
