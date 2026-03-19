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

// Extras required.
\ui_require_css_file('wizard');

// Header.
ui_print_standard_header(
    __('Alerts'),
    'images/gm_alerts.png',
    false,
    'alert_special_days',
    true,
    $tabs,
    [
        [
            'link'  => '',
            'label' => __('Alerts'),
        ],
        [
            'link'  => '',
            'label' => __('Special days'),
        ],
    ]
);

if (empty($message) === false) {
    echo $message;
}

$return_all_group = false;

if (users_can_manage_group_all('LM') === true) {
    $return_all_group = true;
}

$inputs = [];

// Name.
$inputs[] = [
    'label'     => __('Name'),
    'arguments' => [
        'type'     => 'text',
        'name'     => 'name',
        'required' => true,
        'value'    => $calendar->name(),
    ],
];

// Group.
$inputs[] = [
    'label'     => __('Group'),
    'arguments' => [
        'type'           => 'select_groups',
        'returnAllGroup' => $return_all_group,
        'name'           => 'id_group',
        'selected'       => $calendar->id_group(),
        'required'       => true,
    ],
];

// Description.
$inputs[] = [
    'label'     => __('Description'),
    'arguments' => [
        'type'     => 'textarea',
        'name'     => 'description',
        'required' => false,
        'value'    => $calendar->description(),
        'rows'     => 50,
        'columns'  => 30,
    ],
    'class'     => 'w100p',
];

$button_create = '';
    // Submit.
    html_print_action_buttons(
        html_print_submit_button(
            (($create === true) ? __('Create') : __('Update')),
            'button',
            false,
            [
                'icon' => 'wand',
                'form' => 'create_specia_days',
            ],
            true
        )
    );

// Print form.
HTML::printForm(
    [
        'form'   => [
            'action' => $url.'&op=edit&action=save&id='.$calendar->id(),
            'method' => 'POST',
            'id'     => 'create_specia_days',
            'class'  => 'aaaa',
        ],
        'inputs' => $inputs,
    ],
    false,
    true
);
