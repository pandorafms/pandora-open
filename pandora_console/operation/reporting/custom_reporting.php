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

check_login();

require_once 'include/functions_reports.php';

// Header
ui_print_page_header(__('Reporting').' &raquo;  '.__('Custom reporting'), 'images/reporting.png', false, '', false, '');

$reports = reports_get_reports();

if (count($reports) == 0) {
    echo "<div class='nf'>".__('There are no defined reportings').'</div>';
    return;
}

$table->width = '98%';
$table->head = [];
$table->head[0] = __('Report name');
$table->head[1] = __('Description');
$table->head[2] = __('HTML');
$table->head[3] = __('XML');

$table->align = [];
$table->align[2] = 'center';
$table->align[3] = 'center';
$table->align[4] = 'center';
$table->align[5] = 'center';
$table->data = [];

foreach ($reports as $report) {
    if ($report['private'] && ($report['id_user'] != $config['id_user'] && ! is_user_admin($config['id_user']))) {
        continue;
    }

    $data = [];

    $data[0] = $report['name'];
    $data[1] = $report['description'];
    $data[2] = '<a href="index.php?sec=reporting&sec2=operation/reporting/reporting_viewer&id='.$report['id_report'].'">'.html_print_image('images/reporting.png', true).'</a>';
    $data[3] = '<a href="ajax.php?page=operation/reporting/reporting_xml&id='.$report['id_report'].'">'.html_print_image('images/database_lightning.png', true).'</a>';
    array_push($table->data, $data);
}

html_print_table($table);
