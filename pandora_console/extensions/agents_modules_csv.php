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

include_once __DIR__.'/../include/config.php';
include_once __DIR__.'/../include/functions_agents.php';
include_once __DIR__.'/../include/functions_reporting.php';
include_once __DIR__.'/../include/functions_modules.php';
include_once __DIR__.'/../include/functions_users.php';


check_login();

// ACL Check.
if (! check_acl($config['id_user'], 0, 'AR')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Agent view (Grouped)'
    );
    include 'general/noaccess.php';
    exit;
}


$get_agents_module_csv = get_parameter('get_agents_module_csv', 0);


if ($get_agents_module_csv === '1') {
    // ***************************************************
    // Header output
    // ***************************************************
    $config['ignore_callback'] = true;
    while (@ob_end_clean()) {
    }

    $filename = 'agents_module_view_'.date('Ymd').'-'.date('His');

    // Set cookie for download control.
    setDownloadCookieToken();

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'.$filename.'.csv"');
    // ***************************************************
    // Data processing
    // ***************************************************
    echo pack('C*', 0xEF, 0xBB, 0xBF);

    $json_filters = get_parameter('filters', '');

    $filters = json_decode(
        base64_decode(
            get_parameter('filters', '')
        ),
        true
    );

    $results = export_agents_module_csv($filters);

    $divider = $config['csv_divider'];
    $dataend = PHP_EOL;

    $header_fields = [
        __('Agent'),
        __('Module'),
        __('Data'),
    ];

    $out_csv = '';
    foreach ($header_fields as $key => $value) {
        $out_csv .= $value.$divider;
    }

    $out_csv .= "\n";

    foreach ($results as $result) {
        foreach ($result as $key => $value) {
            if (preg_match('/Linux/i', $_SERVER['HTTP_USER_AGENT'])) {
                $value = preg_replace(
                    '/\s+/',
                    ' ',
                    io_safe_output($value)
                );
            } else {
                $value = mb_convert_encoding(
                    preg_replace(
                        '/\s+/',
                        '',
                        io_safe_output($value)
                    ),
                    'UTF-16LE',
                    'UTF-8'
                );
            }

            $out_csv .= $value.$divider;
        }

        $out_csv .= "\n";
    }

    echo io_safe_output($out_csv);

    exit;
}
