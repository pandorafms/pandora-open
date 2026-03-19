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

require_once $config['homedir'].'/include/functions_visual_map.php';
// Breadcrumb.
require_once $config['homedir'].'/include/class/HTML.class.php';
ui_require_css_file('discovery');
// ACL for the general permission.
$vconsoles_read   = (bool) check_acl($config['id_user'], 0, 'VR');
$vconsoles_write  = (bool) check_acl($config['id_user'], 0, 'VW');
$vconsoles_manage = (bool) check_acl($config['id_user'], 0, 'VM');

if ($vconsoles_read === false && $vconsoles_write === false && $vconsoles_manage === false) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access map builder'
    );
    include 'general/noaccess.php';
    exit;
}

    $url_visual_console                 = 'index.php?sec=network&sec2=godmode/reporting/map_builder';
    $url_visual_console_favorite        = 'index.php?sec=network&sec2=godmode/reporting/visual_console_favorite';


$buttons = [];

$buttons['visual_console'] = [
    'active' => false,
    'text'   => '<a href="'.$url_visual_console.'">'.html_print_image(
        'images/logs@svg.svg',
        true,
        [
            'title' => __('Visual Console List'),
            'class' => 'main_menu_icon invert_filter',
        ]
    ).'</a>',
];

$buttons['visual_console_favorite'] = [
    'active' => true,
    'text'   => '<a href="'.$url_visual_console_favorite.'">'.html_print_image(
        'images/star@svg.svg',
        true,
        [
            'title' => __('Visual Favourite Console'),
            'class' => 'main_menu_icon invert_filter',
        ]
    ).'</a>',
];

ui_print_standard_header(
    __('Favourite Visual Console'),
    'images/op_reporting.png',
    false,
    '',
    true,
    $buttons,
    [
        [
            'link'  => '',
            'label' => __('Topology maps'),
        ],
        [
            'link'  => '',
            'label' => __('Visual console'),
        ],
    ]
);

$search    = (string) get_parameter('search', '');
$ag_group  = (int) get_parameter('ag_group', 0);
$recursion = (int) get_parameter('recursion', 0);

$returnAllGroups = 0;
$filters = [];
if (empty($search) === false) {
    $filters['name'] = io_safe_input($search);
}

if ($ag_group > 0) {
    $ag_groups = [];
    $ag_groups = (array) $ag_group;
    if ($recursion) {
        $ag_groups = groups_get_children_ids($ag_group, true);
    }
} else if ($own_info['is_admin']) {
    $returnAllGroups = 1;
}

if ($ag_group) {
    $filters['group'] = array_flip($ag_groups);
}

$own_info = get_user_info($config['id_user']);
if (!$own_info['is_admin'] && !check_acl($config['id_user'], 0, 'AW')) {
    $return_all_group = false;
} else {
    $return_all_group = true;
}

$filterTable = new stdClass();
$filterTable->id = 'visual_console_favorite_filter';
$filterTable->class = 'filter-table-adv';
$filterTable->width = '100%';
$filterTable->size = [];
$filterTable->size[0] = '33%';
$filterTable->size[1] = '33%';

$filterTable->data = [];

$filterTable->data[0][] = html_print_label_input_block(
    __('Search'),
    html_print_input_text('search', $search, '', 50, 255, true)
);

$filterTable->data[0][] = html_print_label_input_block(
    __('Group'),
    html_print_select_groups(false, 'AR', $return_all_group, 'ag_group', $ag_group, '', '', 0, true, false, true, '', false)
);

$filterTable->data[0][] = html_print_label_input_block(
    __('Group Recursion'),
    html_print_checkbox_switch('recursion', 1, $recursion, true, false, '')
);

    $actionUrl = 'index.php?sec=network&amp;sec2=godmode/reporting/visual_console_favorite';


// exit;
$searchForm = '<form method="POST" action="'.$actionUrl.'">';
$searchForm .= html_print_table($filterTable, true);
$searchForm .= html_print_div(
    [
        'class'   => 'action-buttons',
        'content' => html_print_submit_button(
            __('Filter'),
            'search_visual_console',
            false,
            [
                'icon' => 'search',
                'mode' => 'mini',
            ],
            true
        ),
    ],
    true
);
$searchForm .= '</form>';

ui_toggle(
    $searchForm,
    '<span class="subsection_header_title">'.__('Filters').'</span>',
    'filter_form',
    '',
    true,
    false,
    '',
    'white-box-content',
    'box-flat white_table_graph fixed_filter_bar'
);

$favorite_array = visual_map_get_user_layouts(
    $config['id_user'],
    false,
    $filters,
    $returnAllGroups,
    true
);

echo "<div id='is_favourite'>";
if ($favorite_array == false) {
    ui_print_info_message(__('No favourite consoles defined'));
} else {
    echo "<ul class='container'>";
    foreach ($favorite_array as $favorite_k => $favourite_v) {

            $url = 'index.php?sec=network&sec2=operation/visual_console/render_view&id='.$favourite_v['id'];
        

        echo "<a href='".$url."' title='".io_safe_output($favourite_v['name'])."' alt='".io_safe_output($favourite_v['name'])."'><li>";
        echo "<div class='icon_img'>";
            echo html_print_image(
                'images/'.groups_get_icon($favourite_v['id_group']),
                true,
                ['style' => '']
            );
            echo '</div>';
            echo "<div class='text'>";
            echo io_safe_output($favourite_v['name']);
            echo '</div>';
        echo '</li></a>';
    }

    echo '</ul>';
}

echo '</div>';
