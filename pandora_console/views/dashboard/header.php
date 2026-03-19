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

// Button for display full screen mode.
use PandoraFMS\Dashboard\Manager;
global $config;

if (empty($dashboardId)) {
    foreach ($dashboards as $key => $layout) {
        $hash_compare = Manager::generatePublicHash($key);
        if (hash_equals($hash, $hash_compare)) {
            $dashboardId = $key;
            break;
        }
    }
}

$queryFull = [
    'dashboardId' => $dashboardId,
    'refr'        => $refr,
    'pure'        => 1,
];
$urlFull = $url.'&'.http_build_query($queryFull);
$fullscreen['text'] = '<a id="full_screen_link" href="'.$urlFull.'">';
$fullscreen['text'] .= html_print_image(
    'images/fullscreen@svg.svg',
    true,
    [
        'title' => __('Full screen mode'),
        'style' => 'margin-top: 5px',
        'class' => 'main_menu_icon invert_filter',
    ]
);
$fullscreen['text'] .= '</a>';

// Button for display normal screen mode.
$queryNormal = ['dashboardId' => $dashboardId];
$urlNormal = $url.'&'.http_build_query($queryNormal);
$normalscreen['text'] = '<a href="'.$urlNormal.'">';
$normalscreen['text'] .= html_print_image(
    'images/exit_fullscreen@svg.svg',
    true,
    [
        'title' => __('Back to normal mode'),
        'class' => 'main_menu_icon invert_filter',
    ]
);
$normalscreen['text'] .= '</a>';

// Button for display modal options dashboard.
$options['text'] = '<a href="#" onclick=\'';
$options['text'] .= 'show_option_dialog('.json_encode(
    [
        'title'       => __('Update Dashboard'),
        'btn_text'    => __('Ok'),
        'btn_cancel'  => __('Cancel'),
        'url'         => $ajaxController,
        'url_ajax'    => ui_get_full_url('ajax.php'),
        'dashboardId' => $dashboardId,
    ]
);
$options['text'] .= ')\'>';
$options['text'] .= html_print_image(
    'images/configuration@svg.svg',
    true,
    [
        'title' => __('Options'),
        'style' => 'margin-top: 5px',
        'class' => 'main_menu_icon invert_filter',
    ]
);
$options['text'] .= '</a>';

// Button for back to list dashboards.
$back_to_dashboard_list['text'] = '<a href="'.$url.'">';
$back_to_dashboard_list['text'] .= html_print_image(
    'images/logs@svg.svg',
    true,
    [
        'title' => __('Back to dashboards list'),
        'style' => 'margin-top: 5px',
        'class' => 'main_menu_icon invert_filter',
    ]
);
$back_to_dashboard_list['text'] .= '</a>';

$slides['text'] = '<a href="#" onclick=\'';
$slides['text'] .= 'formSlides('.json_encode(
    [
        'title'       => __('Slides'),
        'btn_text'    => __('Ok'),
        'btn_cancel'  => __('Cancel'),
        'url'         => $ajaxController,
        'url_ajax'    => ui_get_full_url('ajax.php'),
        'dashboardId' => $dashboardId,
    ]
);
$slides['text'] .= ')\'>';

$slides['text'] .= html_print_image(
    'images/images.png',
    true,
    [
        'title' => __('Slides mode'),
        'style' => 'margin-top: 5px',
        'class' => 'main_menu_icon invert_filter',
    ]
);
$slides['text'] .= '</a>';

// Public Url.
$queryPublic = [
    'hash'         => $hash,
    'creator_user' => $config['id_user'],
    'pure'         => 1,
];
$publicUrl = ui_get_full_url(
    'operation/dashboard/public_dashboard.php?'.http_build_query($queryPublic)
);
$publiclink['text'] = '<a id="public_link" href="'.$publicUrl.'" target="_blank">';
$publiclink['text'] .= html_print_image(
    'images/item-icon.svg',
    true,
    [
        'title' => __('Show link to public dashboard'),
        'style' => 'margin-top: 5px',
        'class' => 'main_menu_icon invert_filter',
    ]
);
$publiclink['text'] .= '</a>';

// Check if it is a public dashboard.
$public_dashboard_hash = get_parameter('hash', false);

// Refresh selector time dashboards.
if ($public_dashboard_hash !== false) {
    $urlRefresh = $publicUrl;
} else {
    $queryRefresh = [
        'dashboardId' => $dashboardId,
        'pure'        => 1,
    ];
    $urlRefresh = $url.'&'.http_build_query($queryRefresh);
}

$comboRefreshCountdown['text'] = '<div class="dashboard-countdown display_in"></div>';
$comboRefresh['text'] = '<form id="refr-form" method="post" class="mrgn_top_13px"  action="'.$urlRefresh.'">';
$comboRefresh['text'] .= __('Refresh').':';
$comboRefresh['text'] .= html_print_select(
    \get_refresh_time_array(),
    'refr',
    $refr,
    '',
    '',
    0,
    true,
    false,
    false,
    '',
    false,
    'margin-top: 3px;'
);
$comboRefresh['text'] .= '</form>';

// Select all dashboard view user.
$queryCombo = [
    'pure' => $config['pure'],
];
$urlCombo = $url.'&'.http_build_query($queryCombo);
$combo_dashboard['text'] = '<form id="form-select-dashboard" name="query_sel" method="post" action="'.$urlCombo.'">';
$combo_dashboard['text'] .= html_print_select(
    $dashboards,
    'dashboardId',
    $dashboardId,
    'this.form.submit();',
    '',
    0,
    true,
    false,
    true,
    'select-dashboard-width',
    false,
    ''
);
$combo_dashboard['text'] .= '</form>';

// Edit mode.
$enable_disable['text'] = html_print_div(
    [
        'style'   => 'margin-top: 10px;',
        'content' => html_print_checkbox_switch(
            'edit-mode',
            1,
            false,
            true
        ),
    ],
    true
);

// New Widget.
$newWidget['text'] = '<a href="#" id="add-widget" class="invisible_important">';
$newWidget['text'] .= html_print_image(
    'images/plus@svg.svg',
    true,
    [
        'title' => __('Add Cell'),
        'class' => 'main_menu_icon invert_filter',
        'style' => 'margin-top:5px;',
    ]
);
$newWidget['text'] .= '</a>';

if (isset($config['public_dashboard']) === true
    && (bool) $config['public_dashboard'] === true
) {
    $buttons = [
        'combo_refresh_one_dashboard' => $comboRefresh,
        'combo_refresh_countdown'     => $comboRefreshCountdown,
    ];
} else if ($config['pure']) {
    if (check_acl_restricted_all($config['id_user'], $dashboardGroup, 'RW') === 0) {
        $buttons = [
            'back_to_dashboard_list'      => $back_to_dashboard_list,
            'normalscreen'                => $normalscreen,
            'combo_refresh_one_dashboard' => $comboRefresh,
            'slides'                      => $slides,
            'combo_refresh_countdown'     => $comboRefreshCountdown,
        ];
    } else {
        if ($publicLink === true) {
            $buttons = [
                'combo_refresh_one_dashboard' => $comboRefresh,
                'combo_refresh_countdown'     => $comboRefreshCountdown,
            ];
        } else {
            $buttons = [
                'back_to_dashboard_list'      => $back_to_dashboard_list,
                'save_layout'                 => $save_layout_dashboard,
                'normalscreen'                => $normalscreen,
                'combo_refresh_one_dashboard' => $comboRefresh,
                'slides'                      => $slides,
                'options'                     => $options,
                'combo_refresh_countdown'     => $comboRefreshCountdown,
            ];
        }
    }
} else {
    if ($dashboardUser !== $config['id_user'] && check_acl_restricted_all($config['id_user'], $dashboardGroup, 'RW') === 0) {
        $buttons = [
            'back_to_dashboard_list' => $back_to_dashboard_list,
            'fullscreen'             => $fullscreen,
            'slides'                 => $slides,
            'public_link'            => $publiclink,
            'combo_dashboard'        => $combo_dashboard,
            'newWidget'              => $newWidget,
        ];
    } else {
        $buttons = [
            'enable_disable'         => $enable_disable,
            'back_to_dashboard_list' => $back_to_dashboard_list,
            'fullscreen'             => $fullscreen,
            'slides'                 => $slides,
            'public_link'            => $publiclink,
            'combo_dashboard'        => $combo_dashboard,
            'options'                => $options,
            'newWidget'              => $newWidget,
        ];
    }
}

if ($config['pure'] === false) {
    ui_print_standard_header(
        $dashboardName,
        '',
        false,
        '',
        true,
        $buttons,
        [
            [
                'link'  => '',
                'label' => __('Dashboard'),
            ],
        ],
        [
            'id_element' => $dashboardId,
            'url'        => 'operation/dashboard/dashboard&dashboardId='.$dashboardId,
            'label'      => $dashboardName,
            'section'    => 'Dashboard_',
        ]
    );
} else {
    $output = '<div id="dashboard-controls">';
    foreach ($buttons as $key => $value) {
        $output .= '<div>';
        $output .= $value['text'];
        $output .= '</div>';
    }

    $output .= '</div>';
    echo $output;
}
