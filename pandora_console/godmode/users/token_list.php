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

// Load global vars.
global $config;

check_login();

require_once $config['homedir'].'/include/functions_token.php';
require_once $config['homedir'].'/include/functions_users.php';
$tab = get_parameter('tab', 'token');
$pure = get_parameter('pure', 0);

// Header.

    user_print_header($pure, $tab);
    $sec = 'gusuarios';


$edit_url = 'index.php?sec='.$sec;
$edit_url .= '&sec2=godmode/users/configure_token';
$edit_url .= '&pure='.$pure;

$delete_token = (bool) get_parameter('delete_token');
$create_token = (bool) get_parameter('create_token');
$update_token = (bool) get_parameter('update_token');
$id_token = (int) get_parameter('id_token');

// Token deletion.
if ($delete_token === true) {
    try {
        delete_user_token($id_token);
        ui_print_success_message(__('Successfully deleted'));
    } catch (\Exception $e) {
        ui_print_error_message(
            __('There was a problem deleting token, %s', $e->getMessage())
        );
    }
}

$tokenMsg = '';
if ($create_token === true || $update_token === true) {
    $label = get_parameter('label', null);
    $idUser = get_parameter('idUser', $config['id_user']);

    $expirationDate = get_parameter('date-expiration', null);
    $expirationTime = get_parameter('time-expiration', null);
    $validity = null;

    if (empty($expirationDate) === false) {
        $validity = $expirationDate;
        if (empty($expirationTime) === false) {
            $validity .= ' '.$expirationTime;
        } else {
            $validity .= ' 23:59:59';
        }
    }

    $values = [
        'idUser'   => $idUser,
        'label'    => $label,
        'validity' => $validity,
    ];

    // Create token.
    if ($create_token === true) {
        try {
            $token = create_user_token($values);
            $smgInfo = __('This code will appear only once, please keep it in a safe place');
            $smgInfo .= '.</br>';
            $smgInfo .= __('If you lose the code, you will only able to delete it and create a new one');
            $smgInfo .= '.</br></br>';
            $smgInfo .= '<i>';
            $smgInfo .= $token['token'];
            $smgInfo .= '</i>';
            $tokenMsg = ui_print_info_message($smgInfo, '', true);
            ui_print_success_message(__('Successfully created'));
        } catch (\Exception $e) {
            ui_print_error_message(
                __('There was a problem creating this token, %s', $e->getMessage())
            );
        }
    }

    // Update token.
    if ($update_token === true) {
        try {
            $token = update_user_token($id_token, $values);
            ui_print_success_message(__('Successfully updated'));
        } catch (\Exception $e) {
            ui_print_error_message(
                __('There was a problem updating this token, %s', $e->getMessage())
            );
        }
    }
}

try {
    $columns = [
        'label',
        'idUser',
        'validity',
        'lastUsage',
        'options',
    ];

    $column_names = [
        __('Label'),
        __('For user'),
        __('Expiration'),
        __('Last usage'),
        [
            'text'  => __('Options'),
            'class' => 'w20px table_action_buttons',
        ],
    ];

    $user_users = [$config['id_user'] => get_user_fullname($config['id_user'])];
    if ((bool) users_is_admin() === true) {
        $user_users = users_get_user_users(
            $config['id_user'],
            'AR',
            true
        );
        $user_users[0] = __('Any');
    }

    $tableId = 'token_table';
    // Load datatables user interface.
    ui_print_datatable(
        [
            'id'                  => $tableId,
            'class'               => 'info_table',
            'style'               => 'width: 100%',
            'columns'             => $columns,
            'column_names'        => $column_names,
            'ajax_url'            => 'include/ajax/token',
            'ajax_data'           => ['list_user_tokens' => 1],
            'extra_html'          => $tokenMsg,
            'no_sortable_columns' => [ -1 ],
            'order'               => [
                'field'     => 'label',
                'direction' => 'asc',
            ],
            'search_button_class' => 'sub filter float-right',
            'form'                => [
                'inputs' => [
                    [
                        'label' => __('Free search'),
                        'type'  => 'text',
                        'id'    => 'freeSearch',
                        'name'  => 'freeSearch',
                    ],
                    [
                        'label'    => __('User'),
                        'type'     => 'select',
                        'fields'   => $user_users,
                        'selected' => $config['id_user'],
                        'id'       => 'idUser',
                        'name'     => 'idUser',
                    ],
                ],
            ],
            'filter_main_class'   => 'box-flat white_table_graph fixed_filter_bar',
            'dom_elements'        => 'lftp',
        ]
    );
} catch (Exception $e) {
    echo $e->getMessage();
}

echo '<form method="post" action="'.$edit_url.'">';
html_print_action_buttons(
    html_print_submit_button(
        __('Create Token'),
        'crt',
        false,
        ['icon' => 'next'],
        true
    ),
    [
        'type'  => 'data_table',
        'class' => 'fixed_action_buttons',
    ]
);
echo '</form>';
