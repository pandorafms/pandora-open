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

// Includes.
require_once $config['homedir'].'/include/class/HTML.class.php';

global $config;


/**
 * Draw chart.
 *
 * @param string $title Title.
 * @param array  $data  Data for chart.
 *
 * @return string Output.
 */
function draw_graph(string $title, ?array $data): string
{
    global $config;
    if (is_array($data) === false) {
        return 'N/A';
    }

    $water_mark = [];

    $output = '<div class="white_box pdd_15px">';
    $output .= '<span class="breadcrumbs-title">'.$title.'</span>';
    $labels = array_keys($data);
    $options = [
        'width'     => 320,
        'height'    => 200,
        'waterMark' => $water_mark,
        'legend'    => [
            'display'  => true,
            'position' => 'right',
            'align'    => 'center',
        ],
        'labels'    => $labels,
    ];

    $output .= '<div style="width:inherit;margin: 0 auto;">';
    $output .= pie_graph(
        array_values($data),
        $options
    );
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}


// Header tabs.
ui_print_standard_header(
    __('ITSM Dashboard'),
    '',
    false,
    'ITSM_tab',
    false,
    $headerTabs,
    [
        [
            'link'  => 'index.php?sec=ITSM&sec2=operation/ITSM/itsm',
            'label' => __('ITSM'),
        ],
        [
            'link'  => 'index.php?sec=ITSM&sec2=operation/ITSM/itsm',
            'label' => __('ITSM Dashboard'),
        ],
    ]
);

if (empty($error) === false) {
    ui_print_error_message($error);
}

if (empty($incidencesByStatus) === true) {
    ui_print_info_message(
        [
            'no_close' => true,
            'message'  => __('Not found incidences'),
        ]
    );
} else {
    $output = '<div class="container-statistics">';
    $output .= draw_graph(__('Incidents by status'), $incidencesByStatus);
    $output .= draw_graph(__('Incidents by priority'), $incidencesByPriorities);
    $output .= draw_graph(__('Incidents by group'), $incidencesByGroups);
    $output .= draw_graph(__('Incidents by user'), $incidencesByOwners);
    $output .= '</div>';
    echo $output;
}
