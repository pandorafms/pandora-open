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

$action = get_parameter('action', 'new');
$id_os_version = get_parameter('id_os_version', 0);

    $tab = get_parameter('tab', 'list');


if ($id_os_version) {
    $os_version = db_get_row_filter('tconfig_os_version', ['id_os_version' => $id_os_version]);
    $product = $os_version['product'];
    $version = $os_version['version'];
    $end_of_life_date = $os_version['end_of_life_date'];
} else {
    $product = io_safe_input(strip_tags(io_safe_output((string) get_parameter('product'))));
    $version = io_safe_input(strip_tags(io_safe_output((string) get_parameter('version'))));
    $end_of_life_date = get_parameter('end_of_life_date', 0);
}

$message = '';
    switch ($action) {
        case 'edit':
            $action_hidden = 'update';
            $text_button = __('Update');
            $class_button = ['icon' => 'wand'];
        break;

        case 'save':
            $values = [];
            $values['product'] = $product;
            $values['version'] = $version;
            $values['end_of_life_date'] = $end_of_life_date;

            $result_or_id = false;
            if ($product !== '') {
                $result_or_id = db_process_sql_insert('tconfig_os_version', $values);
            }

            if ($result_or_id === false) {
                $message = 2;
                $tab = 'builder';
                $actionHidden = 'save';
                $textButton = __('Create');
                $classButton = ['icon' => 'wand'];
            } else {
                $tab = 'list';
                $message = 1;
            }

                header('Location:'.$config['homeurl'].'index.php?sec=gsetup&sec2=godmode/setup/os&tab='.$tab.'&message='.$message);
            
        break;

        case 'update':
            $product = io_safe_input(strip_tags(io_safe_output((string) get_parameter('product'))));
            $version = io_safe_input(strip_tags(io_safe_output((string) get_parameter('version'))));
            $end_of_life_date = get_parameter('end_of_life_date', 0);

            $values = [];
            $values['product'] = $product;
            $values['version'] = $version;

            $result = false;
            $result = db_process_sql_update('tconfig_os_version', $values, ['id_os' => $id_os_version]);

            if ($result !== false) {
                $message = 3;
                $tab = 'list';
            } else {
                $message = 4;
                $tab = 'builder';
                $os = db_get_row_filter('tconfig_os', ['id_os' => $idOS]);
                $name = $os['name'];
            }

            $actionHidden = 'update';
            $textButton = __('Update');
            $classButton = ['icon' => 'wand'];

                header('Location:'.$config['homeurl'].'index.php?sec=gsetup&sec2=godmode/setup/os_version&tab='.$tab.'&message='.$message);
            
        break;

        case 'delete':
            $sql = 'SELECT COUNT(id_os) AS count FROM tagente WHERE id_os = '.$idOS;
            $count = db_get_all_rows_sql($sql);
            $count = $count[0]['count'];

            if ($count > 0) {
                $message = 5;
            } else {
                $result = (bool) db_process_sql_delete('tconfig_os', ['id_os' => $idOS]);
                if ($result) {
                    $message = 6;
                } else {
                    $message = 7;
                }
            }

                header('Location:'.$config['homeurl'].'index.php?sec=gsetup&sec2=godmode/setup/os&tab='.$tab.'&message='.$message);
            
        break;

        default:
        case 'new':
            $actionHidden = 'save';
            $textButton = __('Create');
            $classButton = ['icon' => 'next'];
        break;
    }

$buttons = [];
$buttons['list'] = [
    'active' => false,
    'text'   => '<a href="index.php?sec=gsetup&sec2=godmode/setup/os&tab=list">'.html_print_image(
        'images/logs@svg.svg',
        true,
        [
            'title' => __('List OS'),
            'class' => 'invert_filter main_menu_icon',
        ]
    ).'</a>',
];

    $buttons['builder'] = [
        'active' => false,
        'text'   => '<a href="index.php?sec=gsetup&sec2=godmode/setup/os&tab=builder">'.html_print_image(
            'images/edit.svg',
            true,
            [
                'title' => __('Builder OS'),
                'class' => 'invert_filter main_menu_icon',
            ]
        ).'</a>',
    ];

    $buttons['version_exp_date_editor'] = [
        'active' => false,
        'text'   => '<a href="index.php?sec=gsetup&sec2=godmode/setup/os&tab=manage_version">'.html_print_image(
            'images/edit.svg',
            true,
            [
                'title' => __('Version expiration date editor'),
                'class' => 'invert_filter main_menu_icon',
            ]
        ).'</a>',
    ];

$buttons[$tab]['active'] = true;

switch ($tab) {
    case 'builder':
        $headerTitle = __('Edit OS');
    break;

    case 'manage_version':
        $headerTitle = __('Version expiration date editor');
    break;

    case 'list':
        $headerTitle = __('List of Operating Systems');
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

        default:
            // Default.
        break;
    }
}

require_once $config['homedir'].'/godmode/setup/os_version.list.php';
