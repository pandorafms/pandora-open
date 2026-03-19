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

require_once $config['homedir'].'/include/functions_profile.php';
require_once $config['homedir'].'/include/functions_users.php';
require_once $config['homedir'].'/include/functions_groups.php';
require_once $config['homedir'].'/include/functions_visual_map.php';

$meta = false;

$id = get_parameter_get('id', $config['id_user']);
// ID given as parameter.
$status = get_parameter('status', -1);
// Flag to print action status message.
$user_info = get_user_info($id);
$id = $user_info['id_user'];
// This is done in case there are problems
// with uppercase/lowercase (MySQL auth has that problem).
if ((!check_acl($config['id_user'], users_get_groups($id), 'UM'))
    && ($id != $config['id_user'])
) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to view a user without privileges'
    );
    include 'general/noaccess.php';
    exit;
}

// If current user is editing himself or if the user has UM (User Management)
// rights on any groups the user is part of AND the authorization scheme
// allows for users/admins to update info.
if (($config['id_user'] == $id
    || check_acl($config['id_user'], users_get_groups($id), 'UM'))
    && $config['user_can_update_info']
) {
    $view_mode = false;
} else {
    $view_mode = true;
}

$urls = [];

    $urls['main'] = 'index.php?sec=gusuarios&sec2=godmode/users/user_list';
    $urls['notifications'] = 'index.php?sec=workspace&amp;sec2=operation/users/user_edit_notifications';
    $buttons = [];

    if ((bool) check_acl($config['id_user'], 0, 'PM') === true) {
        $buttons = [
            'main'          => [
                'active' => $_GET['sec2'] === 'godmode/users/user_list&tab=user&pure=0',
                'text'   => "<a href='{$urls['main']}'>".html_print_image(
                    'images/user.svg',
                    true,
                    [
                        'title' => __('User management'),
                        'class' => 'main_menu_icon invert_filter',
                    ]
                ).'</a>',
            ],
            'notifications' => [
                'active' => $_GET['sec2'] === 'operation/users/user_edit_notifications',
                'text'   => "<a href='{$urls['notifications']}'>".html_print_image(
                    'images/alert@svg.svg',
                    true,
                    [
                        'title' => __('User notifications'),
                        'class' => 'main_menu_icon invert_filter',
                    ]
                ).'</a>',
            ],
        ];
    } else {
        $buttons = [
            'notifications' => [
                'active' => $_GET['sec2'] === 'operation/users/user_edit_notifications',
                'text'   => "<a href='{$urls['notifications']}'>".html_print_image(
                    'images/alert@svg.svg',
                    true,
                    [
                        'title' => __('User notifications'),
                        'class' => 'main_menu_icon invert_filter',
                    ]
                ).'</a>',
            ],
        ];
    }

    $tab_name = 'User Management';

    $helpers = '';
    if ($_GET['sec2'] === 'operation/users/user_edit_notifications') {
        $helpers = 'user_edit_notifications';
        $tab_name = 'User Notifications';
    }

    // Header.
    ui_print_standard_header(
        $headerTitle,
        'images/user.png',
        false,
        $helpers,
        false,
        $buttons,
        [
            [
                'link'  => '',
                'label' => __('Workspace'),
            ],
            [
                'link'  => '',
                'label' => __('Edit user'),
            ],
        ]
    );

