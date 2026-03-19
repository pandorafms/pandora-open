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
// Prepare header and breadcrums.
$i = 0;
$bc = [];
$extra = '&op='.$wizard->operation;

if ($wizard->id !== null) {
    $extra .= '&id='.$wizard->id;
}

$bc[] = [
    'link'     => $wizard->parentUrl,
    'label'    => __('Cluster list'),
    'selected' => false,
];

$labels = $wizard->getLabels();
foreach ($labels as $key => $label) {
    $bc[] = [
        'link'     => $wizard->url.(($key >= 0) ? $extra.'&page='.$key : ''),
        'label'    => __($label),
        'selected' => ($wizard->page == $key),
    ];
}

$wizard->prepareBreadcrum($bc);

$header_str = __(ucfirst($wizard->getOperation())).' ';
$header_str .= (($cluster->name() !== null) ? $cluster->name() : __('cluster '));
$header_str .= ' &raquo; '.__($labels[$wizard->page]);

// Header.
$buttons = [];

$main_page = '<a href="'.$wizard->parentUrl.'">';
$main_page .= html_print_image(
    'images/logs@svg.svg',
    true,
    [
        'title' => __('Cluster list'),
        'class' => 'main_menu_icon invert_filter',
    ]
);
$main_page .= '</a>';

$buttons = [
    [
        'active' => false,
        'text'   => $main_page,
    ],
];

if ($cluster !== null) {
    if ($cluster->id() !== null) {
        $view = '<a href="'.$wizard->parentUrl.'&op=view&id='.$cluster->id().'">';
        $view .= html_print_image(
            'images/details.svg',
            true,
            [
                'title' => __('View this cluster'),
                'class' => 'main_menu_icon invert_filter',
            ]
        );
        $view .= '</a>';

        $buttons[] = [
            'active' => false,
            'text'   => $view,
        ];
    }
}

ui_print_page_header(
    $header_str,
    '',
    false,
    'cluster_view',
    true,
    // Buttons.
    $buttons,
    false,
    '',
    GENERIC_SIZE_TEXT,
    '',
    $wizard->printHeader(true)
);

// Check if any error ocurred.
if (empty($wizard->errMessages) === false) {
    foreach ($wizard->errMessages as $msg) {
        ui_print_error_message(__($msg));
    }
}

$buttons_input = '';
if (empty($form) === false) {
    // Print form (prepared in ClusterWizard).
    $submit = $form['submit-external-input'];
    unset($form['submit-external-input']);

    unset($bc[0]);
    $wizard->printSteps($bc);

    HTML::printForm($form, false, ($wizard->page < 6));
    $buttons_input .= HTML::printInput($submit);
}

// Print always go back button.
$buttons_input .= HTML::printForm($wizard->getGoBackForm(), true);

html_print_action_buttons($buttons_input);
