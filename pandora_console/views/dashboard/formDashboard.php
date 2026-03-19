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

if (empty($arrayDashboard) === true) {
    $arrayDashboard['name'] = 'Default';
    $arrayDashboard['id_user'] = '';
    $private = 0;
    $arrayDashboard['id_group'] = null;
    $arrayDashboard['active'] = 0;
    $arrayDashboard['cells_slideshow'] = 0;
} else {
    $private = 1;
    if (empty($arrayDashboard['id_user']) === true) {
        $private = 0;
    }
}

$return_all_group = false;

if (users_can_manage_group_all('RW') === true) {
    $return_all_group = true;
}

$dataQuery = ['dashboardId' => $dashboardId];

$url = ui_get_full_url(
    'index.php?sec=reporting&sec2=operation/dashboard/dashboard'
);

$url .= '&'.http_build_query($dataQuery);
$form = [
    'id'       => 'form-update-dashboard',
    'action'   => $url,
    'onsubmit' => 'return false;',
    'class'    => 'filter-list-adv',
    'enctype'  => 'multipart/form-data',
    'method'   => 'POST',
];

$inputs = [
    [
        'arguments' => [
            'type'  => 'hidden',
            'name'  => 'dashboardId',
            'value' => $dashboardId,
        ],
    ],
    [
        'label'     => __('Name'),
        'arguments' => [
            'type'      => 'text',
            'name'      => 'name',
            'value'     => $arrayDashboard['name'],
            'size'      => '',
            'maxlength' => 35,
        ],
    ],
    [
        'block_id'      => 'group_form',
        'direct'        => 1,
        'hidden'        => $private,
        'block_content' => [
            [
                'label'     => __('Group'),
                'arguments' => [
                    'name'           => 'id_group',
                    'id'             => 'id_group',
                    'type'           => 'select_groups',
                    'returnAllGroup' => $return_all_group,
                    'selected'       => $arrayDashboard['id_group'],
                    'return'         => true,
                    'required'       => true,
                ],
            ],
        ],
    ],
    [
        'label'     => __('Date range'),
        'arguments' => [
            'name'     => 'date_range',
            'id'       => 'date_range',
            'type'     => 'switch',
            'value'    => $arrayDashboard['date_range'],
            'onchange' => 'handle_date_range(this)',
        ],
    ],
    [
        'label'     => __('Select range'),
        'style'     => 'display: none;',
        'class'     => 'row_date_range',
        'arguments' => [
            'name'      => 'range',
            'id'        => 'range',
            'selected'  => ($arrayDashboard['date_from'] === '0' && $arrayDashboard['date_to'] === '0') ? 300 : 'chose_range',
            'type'      => 'date_range',
            'date_init' => date('Y/m/d', $arrayDashboard['date_from']),
            'time_init' => date('H:i:s', $arrayDashboard['date_from']),
            'date_end'  => date('Y/m/d', $arrayDashboard['date_to']),
            'time_end'  => date('H:i:s', $arrayDashboard['date_to']),
        ],
    ],
    [
        'block_id'      => 'private',
        'direct'        => 1,
        'block_content' => [
            [
                'label'     => __('Private'),
                'arguments' => [
                    'name'    => 'private',
                    'id'      => 'private',
                    'type'    => 'switch',
                    'value'   => $private,
                    'onclick' => 'showGroup()',
                ],
            ],
        ],
    ],
    [
        'label'     => __('Favourite'),
        'arguments' => [
            'name'  => 'favourite',
            'id'    => 'favourite',
            'type'  => 'switch',
            'value' => $arrayDashboard['active'],
        ],
    ],
];

HTML::printForm(
    [
        'form'   => $form,
        'inputs' => $inputs,
    ]
);

?>

<script>
function handle_date_range(element){
    if(element.checked) {
        $(".row_date_range").show();
        var def_state_range = $('#range_range').is(':visible');
        var def_state_default = $('#range_default').is(':visible');
        var def_state_extend = $('#range_extend').is(':visible');
        if (
            def_state_range === false
            && def_state_default === false
            && def_state_extend === false
            && $('#range').val() !== 'chose_range'
        ) {
            $('#range_default').show();
        } else if ($('#range').val() === 'chose_range') {
            $('#range_range').show();
        }
    } else {
        $(".row_date_range").hide();
    }
}
var date_range = $("#date_range")[0];
handle_date_range(date_range);
</script>