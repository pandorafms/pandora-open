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

check_login();

if (! check_acl($config['id_user'], 0, 'PM')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access MIB uploader'
    );
    include 'general/noaccess.php';
    return;
}

require_once 'include/functions_filemanager.php';

// Header.
ui_print_standard_header(
    __('MIB uploader'),
    'images/op_snmp.png',
    false,
    '',
    false,
    [],
    [
        [
            'link'  => '',
            'label' => __('Monitoring'),
        ],
        [
            'link'  => '',
            'label' => __('SNMP'),
        ],
    ]
);


if (isset($config['filemanager']['message'])) {
    echo $config['filemanager']['message'];
    $config['filemanager']['message'] = null;
}

$directory = (string) get_parameter('directory');
$directory = str_replace('\\', '/', $directory);

// Add custom directories here.
$fallback_directory = SNMP_DIR_MIBS;

if (empty($directory) === true) {
    $directory = $fallback_directory;
} else {
    $directory = str_replace('\\', '/', $directory);
    $directory = filemanager_safe_directory($directory, $fallback_directory);
}

$real_directory = realpath($config['homedir'].'/'.$directory);

ui_print_info_message(__('MIB files will be installed on the system. Please note that a MIB may depend on other MIB. To customize trap definitions use the SNMP trap editor.'));

$upload_file_or_zip = (bool) get_parameter('upload_file_or_zip');
$create_text_file = (bool) get_parameter('create_text_file');

$default_real_directory = realpath($config['homedir'].'/'.$fallback_directory);

if ($upload_file_or_zip === true) {
    upload_file($upload_file_or_zip, $default_real_directory, $real_directory, ['mib', 'zip']);
}

if ($create_text_file === true) {
    create_text_file($default_real_directory, $real_directory);
}

filemanager_file_explorer(
    $real_directory,
    $directory,
    'index.php?sec=snmpconsole&sec2=operation/snmpconsole/snmp_mib_uploader',
    SNMP_DIR_MIBS,
    false,
    false,
    '',
    false,
    '',
    false,
    [
        'all'               => true,
        'denyCreateText'    => true,
        'allowCreateFolder' => true,
    ]
);
