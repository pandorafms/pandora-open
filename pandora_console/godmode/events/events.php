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

// Load global vars.
global $config;

check_login();



if (!check_acl($config['id_user'], 0, 'EW') && !check_acl($config['id_user'], 0, 'EM') && ! check_acl($config['id_user'], 0, 'PM')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access event manage'
    );
    include 'general/noaccess.php';
    return;
}

// Gets section to jump to another section
$section = (string) get_parameter('section', 'filter');

// Draws header
if (check_acl($config['id_user'], 0, 'EW') || check_acl($config['id_user'], 0, 'EM')) {
    $buttons['view'] = [
        'active'    => false,
        'text'      => '<a href="index.php?sec=eventos&sec2=operation/events/events&amp;pure='.$config['pure'].'">'.html_print_image(
            'images/event.svg',
            true,
            [
                'title' => __('Event list'),
                'class' => 'invert_filter main_menu_icon',
            ]
        ).'</a>',
        'operation' => true,
    ];

    $buttons['filter'] = [
        'active' => false,
        'text'   => '<a href="index.php?sec=eventos&sec2=godmode/events/events&amp;section=filter&amp;pure='.$config['pure'].'">'.html_print_image(
            'images/filters@svg.svg',
            true,
            [
                'title' => __('Filter list'),
                'class' => 'invert_filter main_menu_icon',
            ]
        ).'</a>',
    ];
}

if (check_acl($config['id_user'], 0, 'PM')) {
    $buttons['responses'] = [
        'active' => false,
        'text'   => '<a href="index.php?sec=eventos&sec2=godmode/events/events&amp;section=responses&amp;pure='.$config['pure'].'">'.html_print_image(
            'images/responses.svg',
            true,
            [
                'title' => __('Event responses'),
                'class' => 'invert_filter main_menu_icon',
            ]
        ).'</a>',
    ];

    $buttons['fields'] = [
        'active' => false,
        'text'   => '<a href="index.php?sec=eventos&sec2=godmode/events/events&amp;section=fields&amp;pure='.$config['pure'].'">'.html_print_image(
            'images/edit_columns@svg.svg',
            true,
            [
                'title' => __('Custom columns'),
                'class' => 'invert_filter main_menu_icon',
            ]
        ).'</a>',
    ];
}

switch ($section) {
    case 'filter':
        $buttons['filter']['active'] = true;
        $subpage = __('Filters');
    break;

    case 'fields':
        $buttons['fields']['active'] = true;
        $subpage = __('Custom columns');
    break;

    case 'responses':
        $buttons['responses']['active'] = true;
        $subpage = __('Responses');
    break;

    case 'view':
        $buttons['view']['active'] = true;
    break;

    default:
        $buttons['filter']['active'] = true;
        $subpage = __('Filters');
    break;
}

ui_print_standard_header(
    $subpage,
    'images/gm_events.png',
    false,
    '',
    true,
    (array) $buttons,
    [
        [
            'link'  => '',
            'label' => __('Configuration'),
        ],
        [
            'link'  => '',
            'label' => __('Events'),
        ],
    ]
);


require_once $config['homedir'].'/include/functions_events.php';


switch ($section) {
    case 'edit_filter':
        include_once $config['homedir'].'/godmode/events/event_edit_filter.php';
    break;

    case 'filter':
        include_once $config['homedir'].'/godmode/events/event_filter.php';
    break;

    case 'fields':
        include_once $config['homedir'].'/godmode/events/custom_events.php';
    break;

    case 'responses':
        include_once $config['homedir'].'/godmode/events/event_responses.php';
    break;
}
