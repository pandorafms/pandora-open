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

// Load files.
require_once '../../include/config.php';
require_once '../../include/functions.php';
global $config;

$user_language = get_user_language($config['id_user']);
$l10n = null;
if (file_exists('../languages/'.$user_language.'.mo') === true) {
    $cf = new CachedFileReader('../languages/'.$user_language.'.mo');
    $l10n = new gettext_reader($cf);
    $l10n->load_tables();
}

// Get data.
$type = (string) get_parameter('type', 'csv');

$data = (string) get_parameter('data');
$data = strip_tags(io_safe_output($data));
$data = json_decode(io_safe_output($data), true);

$default_filename = 'data_exported - '.date($config['date_format']);
$filename = (string) get_parameter('filename', $default_filename);
$filename = io_safe_output($filename);

// Set cookie for download control.
setDownloadCookieToken();

/*
 * $data = array(
 *   'head' => array(<column>,<column>,...,<column>),
 *   'data' => array(
 *     array(<data>,<data>,...,<data>),
 *     array(<data>,<data>,...,<data>),
 *     ...,
 *     array(<data>,<data>,...,<data>),
 *   )
 * );
 */

$output_csv = function ($data, $filename) {
    global $config;

    $separator = (string) $config['csv_divider'];

    $excel_encoding = (bool) get_parameter('excel_encoding', false);

    // CSV Output.
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="'.$filename.'.csv"');

    // BOM.
    if ($excel_encoding === false) {
        echo pack('C*', 0xEF, 0xBB, 0xBF);
    }

    // Header
    // Item / data.
    foreach ($data as $items) {
        if (isset($items['head']) === false
            || isset($items['data']) === false
        ) {
            throw new Exception(__('An error occured exporting the data'));
        }

        // Get key for item value.
        $value_key = array_search('value', $items['head']);

        $head_line = implode($separator, $items['head']);
        echo $head_line."\n";
        foreach ($items['data'] as $item) {
             // Find value and replace csv decimal separator.
            $item[$value_key] = csv_format_numeric($item[$value_key]);

            $item = str_replace('--> '.__('Selected'), '', $item);
            $line = implode($separator, $item);

            if ($excel_encoding === true) {
                echo mb_convert_encoding($line, 'UTF-16LE', 'UTF-8')."\n";
            } else {
                echo $line."\n";
            }
        }
    }
};

/*
 * $data = array(
 *   array(
 *     'key' => <value>,
 *     'key' => <value>,
 *     ...,
 *     'key' => <value>
 *   ),
 *   array(
 *     'key' => <value>,
 *     'key' => <value>,
 *     ...,
 *     'key' => <value>
 *   ),
 *   ...,
 *   array(
 *     'key' => <value>,
 *     'key' => <value>,
 *     ...,
 *     'key' => <value>
 *   )
 * );
 */

$output_json = function ($data, $filename) {
    // JSON Output.
    header('Content-Type: application/json; charset=UTF-8');
    header('Content-Disposition: attachment; filename="'.$filename.'.json"');

    if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
        $json = json_encode($data, JSON_PRETTY_PRINT);
    } else {
        $json = json_encode($data);
    }

    if ($json !== false) {
        echo $json;
    }
};

try {
    if (empty($data) === true) {
        throw new Exception(__('An error occured exporting the data'));
    }

    ob_end_clean();

    switch ($type) {
        case 'json':
            $output_json($data, $filename);
        break;

        case 'csv':
        default:
            $output_csv($data, $filename);
        break;
    }
} catch (Exception $e) {
    die($e->getMessage());
}

exit;
