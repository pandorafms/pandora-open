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

if (! check_acl($config['id_user'], 0, 'PM')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Agent Management'
    );
    include 'general/noaccess.php';
    return;
}

require_once $config['homedir'].'/include/functions_modules.php';

$data = [];
$data[0] = html_print_label_input_block(
    __('WMI query').' '.ui_print_help_icon('wmi_query_tab', true),
    html_print_input_text('snmp_oid', $snmp_oid, '', 25, 255, true)
);

$data[1] = html_print_label_input_block(
    __('Key string'),
    html_print_input_text('snmp_community', $snmp_community, '', 25, 255, true)
);

push_table_row($data, 'wmi_1');

$data = [];
$data[0] = html_print_label_input_block(
    __('Field number'),
    html_print_input_text('tcp_port', $tcp_port, '', 5, 25, true)
);

$data[1] = html_print_label_input_block(
    __('Namespace'),
    html_print_input_text('tcp_send', $tcp_send, '', 25, 255, true)
);

push_table_row($data, 'wmi_2');

$data = [];
$data[0] = html_print_label_input_block(
    __('Username'),
    html_print_input_text('plugin_user', $plugin_user, '', 15, 255, true)
);

$data[1] = html_print_label_input_block(
    __('Password'),
    html_print_input_password(
        'plugin_pass',
        $plugin_pass,
        '',
        25,
        255,
        true,
        false,
        false,
        '',
        'off',
        true
    )
);

push_table_row($data, 'wmi_3');

$data = [];
$data[0] = html_print_label_input_block(
    __('Post process'),
    html_print_extended_select_for_post_process(
        'post_process',
        $post_process,
        '',
        __('Empty'),
        '0',
        false,
        true,
        false,
        true
    )
);
$data[1] = '';
push_table_row($data, 'field_process');
