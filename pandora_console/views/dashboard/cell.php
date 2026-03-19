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

$output = '';
if ($redraw === false) {
    $output .= '<div>';
    $output .= '<div id="widget-'.$cellData['id'].'" class="grid-stack-item-content">';
}

$output .= '<div class="header-widget">';
$output .= '<div>';
if (isset($cellData['options']) === true) {
    $options = json_decode($cellData['options'], true);
} else {
    $options = [];
}

if ($cellData['id_widget'] !== '0') {
    $output .= $options['title'];
} else {
    $output .= __('New widget');
}

$output .= '</div>';
$output .= '<div class="header-options">';
if ($manageDashboards !== 0 || $writeDashboards !== 0) {
    if ((int) $cellData['id_widget'] !== 0) {
        $count_options = count(json_decode($cellData['options'], true));
        $invisible = '';
        if ($count_options <= 2 && $options['copy'] == 0) {
            $invisible = 'invisible';
        }

        $output .= '<a id="copy-widget-'.$cellData['id'].'" class="'.$invisible.'" >';
        $output .= html_print_image(
            'images/copy.svg',
            true,
            [
                'width' => '16px',
                'title' => __('Copy widget'),
                'class' => 'invert_filter',
            ]
        );
        $output .= '</a> ';

        $output .= '<a id="configure-widget-'.$cellData['id'].'" class="">';
        $widget_description = db_get_value_sql('SELECT description FROM twidget WHERE id ='.$cellData['id_widget']);
        $output .= html_print_input_hidden('widget_name_'.$cellData['id'], $widget_description, true);
        $output .= html_print_input_hidden('widget_id_'.$cellData['id'], $cellData['id_widget'], true);
        $output .= html_print_image(
            'images/configuration@svg.svg',
            true,
            [
                'width' => '16px',
                'title' => __('Configure widget'),
                'class' => 'invert_filter',
            ]
        );
        $output .= '</a> ';
    }

    $output .= '<a id="delete-widget-'.$cellData['id'].'" class="">';
    $output .= html_print_image(
        'images/delete.svg',
        true,
        [
            'width' => '16px',
            'title' => __('Delete widget'),
            'class' => 'invert_filter',
        ]
    );
    $output .= '</a>';
}

$output .= '</div>';
$output .= '</div>';
if (empty($options['background']) === true) {
    if ($config['style'] === 'pandora') {
        $options['background'] = '#ffffff';
    }

    if ($config['style'] === 'pandora_black') {
        $options['background'] = '#222222';
    }
} else if ($options['background'] === '#ffffff'
    && $config['style'] === 'pandora_black'
) {
    $options['background'] = '#222222';
} else if ($options['background'] === '#222222'
    && $config['style'] === 'pandora'
) {
    $options['background'] = '#ffffff';
}

if ((int) $cellData['id_widget'] !== 0) {
    $style = 'style="background-color:'.$options['background'].';"';
    $output .= '<div class="content-widget" '.$style.'>';
} else {
    $output .= '<div class="content-widget">';
}

$output .= '</div>';

if ($redraw === false) {
    $output .= '</div>';
    $output .= '</div>';
}

echo $output;
