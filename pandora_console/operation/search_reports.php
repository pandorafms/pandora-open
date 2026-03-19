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

require_once 'include/functions_reports.php';


$linkReport = false;
$searchReports = check_acl($config['id_user'], 0, 'RR');

$linkReport = true;

if ($reports === false || !$searchReports) {
        echo "<br><div class='nf'>".__('Zero results found')."</div>\n";
} else {
    $table = new stdClass();
    $table->cellpadding = 4;
    $table->cellspacing = 4;
    $table->width = '98%';
    $table->class = 'databox';

    $table->head = [];
    $table->align = [];
    $table->headstyle = [];
    $table->style = [];

    $table->align[2] = 'left';
    $table->align[3] = 'left';
    $table->align[4] = 'left';
    $table->data = [];
    $table->head[0] = __('Report name');
    $table->head[1] = __('Description');
    $table->head[2] = __('HTML');
    $table->head[3] = __('XML');
    $table->size[0] = '50%';
    $table->size[1] = '20%';
    $table->headstyle[0] = 'text-align: left';
    $table->headstyle[1] = 'text-align: left';
    $table->size[2] = '2%';
    $table->headstyle[2] = 'min-width: 35px;text-align: left;';
    $table->size[3] = '2%';
    $table->headstyle[3] = 'min-width: 35px;text-align: left;';
    $table->size[4] = '2%';
    $table->headstyle[4] = 'min-width: 35px;text-align: left;';

    $table->head = [];
    $table->head[0] = __('Report name');
    $table->head[1] = __('Description');
    $table->head[2] = __('HTML');
    $table->head[3] = __('XML');


    $table->data = [];
    foreach ($reports as $report) {
        if ($linkReport) {
            $reportstring = "<a href='?sec=reporting&sec2=godmode/reporting/reporting_builder&action=edit&id_report=".$report['id_report']."' title='".__('Edit')."'>".$report['name'].'</a>';
        } else {
            $reportstring = $report['name'];
        }

        $data = [
            $reportstring,
            $report['description'],
            '<a href="index.php?sec=reporting&sec2=operation/reporting/reporting_viewer&id='.$report['id_report'].'">'.html_print_image('images/reporting.png', true).'</a>',
            '<a href="ajax.php?page=operation/reporting/reporting_xml&id='.$report['id_report'].'">'.html_print_image('images/xml.png', true).'</a>',
        ];

        array_push($table->data, $data);
    }

    echo '<br />';
    ui_pagination($totalReports);
    html_print_table($table);
    unset($table);
    ui_pagination($totalReports);
}
