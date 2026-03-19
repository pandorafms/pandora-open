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

if (isset($preview) === false) {
    $preview = '';
}

if (isset($id) === false) {
    $id = '';
}

$output = '';
$output .= '<script>var idTips = ['.$id.'];</script>';
$output .= '<div class="window">';
$output .= '<div class="tips_header">';
$output .= '<p class="title">'.__('Hello! These are the tips of the day.').'</p>';
$output .= '<p>'.html_print_checkbox(
    'show_tips_startup',
    true,
    true,
    true,
    false,
    'show_tips_startup(this)',
    false,
    '',
    ($preview === true) ? '' : 'checkbox_tips_startup'
).'&nbsp;'.__('Show usage tips at startup').'</p>';
$output .= '</div>';
$output .= '<div class="carousel '.((empty($files) === true && empty($files64) === true) ? 'invisible' : '').'">';
$output .= '<div class="images">';

if ($files !== false) {
    if ($preview === true) {
        foreach ($files as $key => $file) {
            $output .= html_print_image($file, true, ['class' => 'main_menu_icon']);
        }
    } else {
        foreach ($files as $key => $file) {
            $output .= html_print_image($file['path'].$file['filename'], true, ['class' => 'main_menu_icon']);
        }
    }
}

if (isset($files64) === true) {
    if ($files64 !== false) {
        foreach ($files64 as $key => $file) {
            $output .= '<img src="'.$file.'" />';
        }
    }
}

$output .= '</div>';
$output .= '</div>';

$output .= '<div class="description">';
$output .= '<h2 id="title_tip">'.$title.'</h2>';
$output .= '<p id="text_tip">';
$output .= $text;
$output .= '</p>';
$disabled_class = 'disabled_button';
$disabled = true;
if (empty($url) === false && $url !== '') {
    $disabled_class = '';
    $disabled = false;
}

$output .= '</div>';

$output .= '<div class="ui-dialog-buttonset">';
$output .= '<a href="'.$url.'" class="" target="_blank" id="url_tip">';
$output .= html_print_button(
    __('Learn more'),
    'learn_more',
    $disabled,
    '',
    ['class' => 'secondary mini '.$disabled_class],
    true
);
$output .= '</a>';
$output .= '<div class="counter-tips">';

$output .= html_print_image('images/arrow-left-grey.png', true, ['class' => 'arrow_counter', 'onclick' => 'previous_tip()']);
$output .= html_print_image('images/arrow-right-grey.png', true, ['class' => 'arrow_counter', 'onclick' => 'next_tip()']);
$output .= html_print_input_hidden('tip_position', 0, true);
$output .= '</div>';
if ($preview === true) {
    $output .= html_print_button(
        __('Close'),
        'close_dialog',
        false,
        '',
        [
            'onclick' => 'close_dialog()',
            'class'   => 'mini',
        ],
        true
    );
} else {
    $output .= html_print_button(
        __('Close'),
        'close_dialog',
        false,
        '',
        [
            'onclick' => 'close_dialog()',
            'class'   => ($totalTips === '1') ? 'mini hide-button' : 'mini',
        ],
        true
    );
}

$output .= '</div>';
$output .= '</div>';
echo $output;
