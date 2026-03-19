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

check_login();

if (is_ajax()) {
    $get_os_icon = (bool) get_parameter('get_os_icon');
    $select_timezone = get_parameter('select_timezone', 0);

    if ($get_os_icon) {
        $id_os = (int) get_parameter('id_os');
        ui_print_os_icon($id_os, false);
        return;
    }

    if ($select_timezone) {
        $zone = get_parameter('zone');

        $timezones = timezone_identifiers_list();
        foreach ($timezones as $timezone_key => $timezone) {
            if (strpos($timezone, $zone) === false) {
                unset($timezones[$timezone_key]);
            }
        }

        echo json_encode($timezones);
    }

    return;
}


if (! check_acl($config['id_user'], 0, 'PM') && ! is_user_admin($config['id_user'])) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Setup Management'
    );
    include 'general/noaccess.php';
    return;
}

// Gets section to jump to another section.
$section = (string) get_parameter('section', 'general');

$buttons = [];
$menu_tabs = [];

// Draws header.
$buttons['general'] = [
    'active' => false,
    'text'   => '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&amp;section=general').'">'.html_print_image(
        'images/setup.png',
        true,
        [
            'title' => __('General setup'),
            'class' => 'invert_filter',

        ]
    ).'</a>',
];
$menu_tab_url = '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&section=general').'">'.__('General setup').'</a>';
array_push($menu_tabs, $menu_tab_url);

$buttons['auth'] = [
    'active' => false,
    'text'   => '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&amp;section=auth').'">'.html_print_image(
        'images/key.png',
        true,
        [
            'title' => __('Authentication'),
            'class' => 'invert_filter',
        ]
    ).'</a>',
];
$menu_tab_url = '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&amp;section=auth').'">'.__('Authentication').'</a>';
array_push($menu_tabs, $menu_tab_url);

$buttons['perf'] = [
    'active' => false,
    'text'   => '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&amp;section=perf').'">'.html_print_image(
        'images/performance.png',
        true,
        [
            'title' => __('Performance'),
            'class' => 'invert_filter',
        ]
    ).'</a>',
];
$menu_tab_url = '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&amp;section=perf').'">'.__('Performance').'</a>';
array_push($menu_tabs, $menu_tab_url);

$buttons['vis'] = [
    'active' => false,
    'text'   => '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&amp;section=vis').'">'.html_print_image(
        'images/chart.png',
        true,
        [
            'title' => __('Visual styles'),
            'class' => 'invert_filter',
        ]
    ).'</a>',
];
$menu_tab_url = '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&amp;section=vis').'">'.__('Visual styles').'</a>';
array_push($menu_tabs, $menu_tab_url);

if (check_acl($config['id_user'], 0, 'AW')) {
    if ($config['activate_netflow']) {
        $buttons['net'] = [
            'active' => false,
            'text'   => '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&amp;section=net').'">'.html_print_image(
                'images/op_netflow.png',
                true,
                [
                    'title' => __('Netflow'),
                    'class' => 'invert_filter',
                ]
            ).'</a>',
        ];
        $menu_tab_url = '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&amp;section=net').'">'.__('Netflow').'</a>';
        array_push($menu_tabs, $menu_tab_url);
    }

    if ($config['activate_sflow']) {
        $buttons['sflow'] = [
            'active' => false,
            'text'   => '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&amp;section=sflow').'">'.html_print_image(
                'images/op_recon.png',
                true,
                [
                    'title' => __('Sflow'),
                    'class' => 'invert_filter',
                ]
            ).'</a>',
        ];
        $menu_tab_url = '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&amp;section=sflow').'">'.__('Sflow').'</a>';
        array_push($menu_tabs, $menu_tab_url);
    }
}

// FIXME: Not definitive icon
$buttons['notifications'] = [
    'active' => false,
    'text'   => '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&section=notifications').'">'.html_print_image(
        'images/alerts_template.png',
        true,
        [
            'title' => __('Notifications'),
            'class' => 'invert_filter',
        ]
    ).'</a>',
];
$menu_tab_url = '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&section=notifications').'">'.__('Notifications').'</a>';
array_push($menu_tabs, $menu_tab_url);

$buttons['quickshell'] = [
    'active' => false,
    'text'   => '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&section=quickshell').'">'.html_print_image(
        'images/websocket_small.png',
        true,
        [
            'title' => __('QuickShell'),
            'class' => 'invert_filter',
        ]
    ).'</a>',
];
$menu_tab_url = '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&section=quickshell').'">'.__('QuickShell').'</a>';
array_push($menu_tabs, $menu_tab_url);

$buttons['external_tools'] = [
    'active' => false,
    'text'   => '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&section=external_tools').'">'.html_print_image('images/nettool.png', true, ['title' => __('External Tools'), 'class' => 'invert_filter']).'</a>',
];
$menu_tab_url = '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&section=external_tools').'">'.__('External Tools').'</a>';
array_push($menu_tabs, $menu_tab_url);

$buttons['welcome_tips'] = [
    'active' => false,
    'text'   => '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&section=welcome_tips').'">'.html_print_image('images/inventory.png', true, ['title' => __('Welcome tips'), 'class' => 'invert_filter']).'</a>',
];
$menu_tab_url = '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&section=welcome_tips').'">'.__('Welcome tips').'</a>';
array_push($menu_tabs, $menu_tab_url);

$buttons['demo_data'] = [
    'active' => false,
    'text'   => '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&section=demo_data').'">'.html_print_image('images/demo_data.png', true, ['title' => __('Demo data'), 'class' => 'invert_filter']).'</a>',
];
$menu_tab_url = '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&section=demo_data').'">'.__('Demo data').'</a>';
array_push($menu_tabs, $menu_tab_url);

if ($config['activate_gis']) {
    $buttons['gis'] = [
        'active' => false,
        'text'   => '<a href="'.ui_get_full_url('index.php?sec=general&sec2=godmode/setup/setup&section=gis').'">'.html_print_image(
            'images/gis_tab.png',
            true,
            [
                'title' => __('GIS Map connection'),
                'class' => 'invert_filter',
            ]
        ).'</a>',
    ];
    $menu_tab_url = '<a href="'.ui_get_full_url('index.php?sec=gsetup&sec2=godmode/setup/setup&section=gis').'">'.__('GIS Map connection').'</a>';
    array_push($menu_tabs, $menu_tab_url);
}

$help_header = '';

switch ($section) {
    case 'general':
        $buttons['general']['active'] = true;
        $subpage = __('General setup');
        $help_header = 'setup_general_tab';
    break;

    case 'auth':
        $buttons['auth']['active'] = true;
        $subpage = __('Authentication');
    break;

    case 'perf':
        $buttons['perf']['active'] = true;
        $subpage = __('Performance');
        $help_header = '';
    break;

    case 'vis':
        $buttons['vis']['active'] = true;
        $subpage = __('Visual styles');
    break;

    case 'net':
        $buttons['net']['active'] = true;
        $subpage = __('Netflow');
        $help_header = 'setup_netflow_tab';
    break;

    case 'sflow':
        $buttons['sflow']['active'] = true;
        $subpage = __('Sflow');
        $help_header = 'setup_flow_tab';
    break;

    case 'module_library':
        $buttons['module_library']['active'] = true;
        $subpage = __('Module Library');
        $help_header = 'setup_module_library_tab';
    break;

    case 'gis':
        $buttons['gis']['active'] = true;
        $subpage = __('Map conections GIS');
    break;

    case 'notifications':
        $buttons['notifications']['active'] = true;
        $subpage = __('Notifications');
    break;

    case 'quickshell':
        $buttons['quickshell']['active'] = true;
        $subpage = __('QuickShell');
        $help_header = 'quickshell_settings';
    break;

    case 'external_tools':
        $buttons['external_tools']['active'] = true;
        $subpage = __('External Tools');
        $help_header = '';
    break;

    case 'welcome_tips':
        $view = get_parameter('view', '');
        $title = __('Welcome tips');
        if ($view === 'create') {
            $title = __('Create tip');
        } else if ($view === 'edit') {
            $title = __('Edit tip');
        }

        $buttons['welcome_tips']['active'] = true;
        $subpage = $title;
        $help_header = '';
    break;

    case 'demo_data':
        $buttons['demo_data']['active'] = true;
        $subpage = __('Demo data');
        $help_header = '';
    break;

    case 'hist_db':
        $buttons['hist_db']['active'] = true;
        $subpage = __('Historical database');
        $help_header = '';
    break;

    case 'pass':
        $buttons['pass']['active'] = true;
        $subpage = __('Password policies');
        $help_header = '';
    break;

    case 'log':
        $buttons['log']['active'] = true;
        $subpage = __('Log Collector');
        $help_header = 'opensearch_installation';
    break;

    default:
        $subpage = '';
        // Default.
    break;
}

$dots = dot_tab($menu_tabs);

// Header.
ui_print_standard_header(
    $subpage,
    '',
    false,
    $help_header,
    true,
    $buttons,
    [
        [
            'link'  => '',
            'label' => __('Setup'),
        ],
        [
            'link'  => '',
            'label' => $subpage,
        ],
    ],
    [],
    $dots
);

if (isset($config['error_config_update_config'])) {
    if ($config['error_config_update_config']['correct'] == false) {
        ui_print_error_message($config['error_config_update_config']['message']);
    } else {
        ui_print_success_message(__('Correct update the setup options'));
    }

    if (isset($config['error_config_update_config']['errors']) === true) {
        if (is_array($config['error_config_update_config']['errors']) === true) {
            foreach ($config['error_config_update_config']['errors'] as $msg) {
                ui_print_error_message($msg);
            }
        }
    }

    if (isset($config['error_config_update_config']['warnings']) === true) {
        if (is_array($config['error_config_update_config']['warnings']) === true) {
            foreach ($config['error_config_update_config']['warnings'] as $msg) {
                ui_print_warning_message($msg);
            }
        }
    }

    unset($config['error_config_update_config']);
}

switch ($section) {
    case 'general':
        include_once $config['homedir'].'/godmode/setup/setup_general.php';
    break;

    case 'auth':
        include_once $config['homedir'].'/godmode/setup/setup_auth.php';
    break;

    case 'perf':
        include_once $config['homedir'].'/godmode/setup/performance.php';
    break;

    case 'net':
        include_once $config['homedir'].'/godmode/setup/setup_netflow.php';
    break;

    case 'sflow':
        include_once $config['homedir'].'/godmode/setup/setup_sflow.php';
    break;

    case 'vis':
        include_once $config['homedir'].'/godmode/setup/setup_visuals.php';
    break;

    case 'gis':
        include_once $config['homedir'].'/godmode/setup/gis.php';
    break;

    case 'notifications':
        include_once $config['homedir'].'/godmode/setup/setup_notifications.php';
    break;

    case 'quickshell':
        include_once $config['homedir'].'/godmode/setup/setup_quickshell.php';
    break;

    case 'external_tools':
        include_once $config['homedir'].'/godmode/setup/setup_external_tools.php';
    break;

    case 'welcome_tips':
        include_once $config['homedir'].'/godmode/setup/welcome_tips.php';
    break;

    case 'demo_data':
        include_once $config['homedir'].'/godmode/setup/demo.php';
    break;

    default:
    break;
}
