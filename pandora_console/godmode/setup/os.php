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

if (! check_acl($config['id_user'], 0, 'PM') && ! is_user_admin($config['id_user'])) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Setup Management'
    );
    include 'general/noaccess.php';
    return;
}

$action = get_parameter('action', '');
$idOS = get_parameter('id_os', 0);
$id_message = get_parameter('message', 0);

    $tab = get_parameter('tab', 'manage_os');

$buttons = [];

$buttons['manage_os'] = [
    'active' => false,
    'text'   => '<a href="index.php?sec=gsetup&sec2=godmode/setup/os&tab=manage_os">'.html_print_image(
        'images/os@svg.svg',
        true,
        [
            'title' => __('Manage OS types'),
            'class' => 'invert_filter main_menu_icon',
        ]
    ).'</a>',
];

$buttons['manage_version'] = [
    'active' => false,
    'text'   => '<a href="index.php?sec=gsetup&sec2=godmode/setup/os&tab=manage_version">'.html_print_image(
        'images/os_version@svg.svg',
        true,
        [
            'title' => __('Manage version expiration dates'),
            'class' => 'invert_filter main_menu_icon',
        ]
    ).'</a>',
];

$buttons[$tab]['active'] = true;

switch ($tab) {
    case 'builder':
        $headerTitle = __('Edit OS');
    break;

    case 'manage_os':
        $id_os = get_parameter('id_os', '');
        if ($id_os !== '') {
            $headerTitle = __('Edit OS');
        } else {
            $headerTitle = __('Create OS');
        }
    break;

    case 'list':
        if ($action === 'edit') {
            $headerTitle = __('Edit OS');
        } else {
            $headerTitle = __('List of Operating Systems');
        }
    break;

    case 'manage_version':
        if ($action === 'edit') {
            $headerTitle = __('Edit OS version expiration date');
        } else {
            $headerTitle = __('List of version expiration dates');
        }
    break;

    default:
        // Default.
    break;
}

    // Header.
    ui_print_standard_header(
        $headerTitle,
        '',
        false,
        '',
        true,
        $buttons,
        [
            [
                'link'  => '',
                'label' => __('Servers'),
            ],
            [
                'link'  => '',
                'label' => __('Edit OS'),
            ],
        ]
    );


if (empty($id_message) === false) {
    switch ($id_message) {
        case 1:
            echo ui_print_success_message(__('Success creating OS'), '', true);
        break;

        case 2:
            echo ui_print_error_message(__('Fail creating OS'), '', true);
        break;

        case 3:
            echo ui_print_success_message(__('Success updating OS'), '', true);
        break;

        case 4:
            echo ui_print_error_message(__('Error updating OS'), '', true);
        break;

        case 5:
            echo ui_print_error_message(__('There are agents with this OS.'), '', true);
        break;

        case 6:
            echo ui_print_success_message(__('Success deleting'), '', true);
        break;

        case 7:
            echo ui_print_error_message(__('Error deleting'), '', true);
        break;

        case 8:
            header('Location: index.php?sec=gagente&sec2=godmode/setup/os&tab=manage_os&action=edit&id_message=8');
        break;

        case 9:
            header('Location: index.php?sec=gagente&sec2=godmode/setup/os&tab=manage_os&action=edit&id_message=9');
        break;

        case 10:
            header('Location: index.php?sec=gagente&sec2=godmode/setup/os&tab=manage_os&action=edit&id_message=10');
        break;

        default:
            // Default.
        break;
    }
}

switch ($tab) {
    case 'manage_os':
    case 'list':
        if (in_array($action, ['edit', 'save', 'update'])) {
            include_once $config['homedir'].'/godmode/setup/os.builder.php';
        } else {
            include_once $config['homedir'].'/godmode/setup/os.list.php';
        }
    break;

    case 'manage_version':
        if (in_array($action, ['edit', 'save', 'update'])) {
            include_once $config['homedir'].'/godmode/setup/os_version.builder.php';
        } else {
            include_once $config['homedir'].'/godmode/setup/os_version.list.php';
        }
    break;

    default:
        // Default.
    break;
}
