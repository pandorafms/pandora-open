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

// ACL Check.
check_login();
if (check_acl($config['id_user'], 0, 'PM') === false) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access to Files repository'
    );
    include 'general/noaccess.php';
    return;
}

$tab = get_parameter('tab', '');

$url = 'index.php?sec=extensions&sec2=godmode/files_repo/files_repo';

// Header tabs.
$godmode['text'] = '<a href="'.$url.'&tab=configuration">';
$godmode['text'] .= html_print_image(
    'images/configuration@svg.svg',
    true,
    [
        'title' => __('Administration view'),
        'class' => 'main_menu_icon invert_filter',
    ]
);
$godmode['text'] .= '</a>';
$godmode['godmode'] = 1;

$operation['text'] = '<a href="'.$url.'">';
$operation['text'] .= html_print_image(
    'images/see-details@svg.svg',
    true,
    [
        'title' => __('Operation view'),
        'class' => 'main_menu_icon invert_filter',
    ]
);
$operation['text'] .= '</a>';
$operation['operation'] = 1;

$operation['active'] = 1;
$godmode['active'] = 0;
if ($tab === 'configuration') {
    $godmode['active'] = 1;
    $operation['active'] = 0;
}

$onheader = [
    'godmode'   => $godmode,
    'operation' => $operation,
];

// Header.
ui_print_standard_header(
    __('Extensions'),
    'images/extensions.png',
    false,
    '',
    true,
    $onheader,
    [
        [
            'link'  => '',
            'label' => __('Tools'),
        ],
        [
            'link'  => '',
            'label' => __('Files repository'),
        ],
    ]
);

require_once __DIR__.'/../../include/functions_files_repository.php';

// Directory files_repo check.
if (files_repo_check_directory() === false) {
    return;
}

$server_content_length = 0;
if (isset($_SERVER['CONTENT_LENGTH'])) {
    $server_content_length = $_SERVER['CONTENT_LENGTH'];
}

// Check for an anoying error that causes the $_POST and $_FILES arrays.
// were empty if the file is larger than the post_max_size.
if (intval($server_content_length) > 0 && empty($_POST)) {
    ui_print_error_message(
        __('Problem uploading. Please check this PHP runtime variable values: <pre>  post_max_size (currently '.ini_get('post_max_size').')</pre>')
    );
}

// GET and POST parameters.
$file_id = (int) get_parameter('file_id');
$add_file = (bool) get_parameter('add_file');
$update_file = (bool) get_parameter('update_file');
$delete_file = (bool) get_parameter('delete');

// File add or update.
if ($add_file === true || ($update_file === true && $file_id > 0)) {
    $groups = get_parameter('groups', []);
    $public = (bool) get_parameter('public');
    $description = io_safe_output((string) get_parameter('description'));
    if (mb_strlen($description, 'UTF-8') > 200) {
        $description = mb_substr($description, 0, 200, 'UTF-8');
    }

    $description = io_safe_input($description);

    if ($add_file === true) {
        $result = files_repo_add_file('upfile', $description, $groups, $public);
    } else if ($update_file === true) {
        $result = files_repo_update_file($file_id, $description, $groups, $public);
        $file_id = 0;
    }

    if ($result['status'] == false) {
        ui_print_error_message($result['message']);
    } else {
        if ($add_file === true) {
            ui_print_success_message(__('Successfully created'));
        } else if ($update_file === true) {
            ui_print_success_message(__('Successfully updated'));
        }
    }
}

// File delete.
if ($delete_file === true && $file_id > 0) {
    $result = files_repo_delete_file($file_id);
    if ($result !== -1) {
        ui_print_result_message($result, __('Successfully deleted'), __('Could not be deleted'));
    }

    $file_id = 0;
}

$operation['active'] = 1;
if ($tab === 'configuration') {
    include_once __DIR__.'/files_repo_form.php';
} else {
    include_once __DIR__.'/files_repo_list.php';
}
