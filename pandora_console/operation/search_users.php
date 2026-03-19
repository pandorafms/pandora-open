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

require_once $config['homedir'].'/include/functions_profile.php';
require_once $config['homedir'].'/include/functions_users.php';
require_once $config['homedir'].'/include/functions_groups.php';

$searchUsers = check_acl($config['id_user'], 0, 'UM');

if (!$users || !$searchUsers) {
    echo "<br><div class='nf'>".__('Zero results found')."</div>\n";
} else {
    $table = new stdClass();
    $table->cellpadding = 4;
    $table->cellspacing = 4;
    $table->width = '98%';
    $table->class = 'databox';

    $table->align = [];
    $table->align[4] = 'center';

    $table->headstyle = [];
    $table->headstyle[0] = 'text-align: left';
    $table->headstyle[1] = 'text-align: left';
    $table->headstyle[2] = 'text-align: left';
    $table->headstyle[3] = 'text-align: left';
    $table->headstyle[4] = 'text-align: center';
    $table->headstyle[5] = 'text-align: left';

    $table->head = [];
    $table->head[0] = __('User ID').' '.'<a href="index.php?search_category=users&keywords='.$config['search_keywords'].'&head_search_keywords=abc&offset='.$offset.'&sort_field=id_user&sort=up">'.html_print_image('images/sort_up.png', true, ['style' => $selectUserIDUp]).'</a>'.'<a href="index.php?search_category=users&keywords='.$config['search_keywords'].'&head_search_keywords=abc&offset='.$offset.'&sort_field=id_user&sort=down">'.html_print_image('images/sort_down.png', true, ['style' => $selectUserIDDown]).'</a>';
    $table->head[1] = __('Name').' '.'<a href="index.php?search_category=users&keywords='.$config['search_keywords'].'&head_search_keywords=abc&offset='.$offset.'&sort_field=name&sort=up">'.html_print_image('images/sort_up.png', true, ['style' => $selectNameUp]).'</a>'.'<a href="index.php?search_category=users&keywords='.$config['search_keywords'].'&head_search_keywords=abc&offset='.$offset.'&sort_field=name&sort=down">'.html_print_image('images/sort_down.png', true, ['style' => $selectNameDown]).'</a>';
    $table->head[2] = __('Email').' '.'<a href="index.php?search_category=users&keywords='.$config['search_keywords'].'&head_search_keywords=abc&offset='.$offset.'&sort_field=email&sort=up">'.html_print_image('images/sort_up.png', true, ['style' => $selectEmailUp]).'</a>'.'<a href="index.php?search_category=users&keywords='.$config['search_keywords'].'&head_search_keywords=abc&offset='.$offset.'&sort_field=email&sort=down">'.html_print_image('images/sort_down.png', true, ['style' => $selectEmailDown]).'</a>';
    $table->head[3] = __('Last contact').' '.'<a href="index.php?search_category=users&keywords='.$config['search_keywords'].'&head_search_keywords=abc&offset='.$offset.'&sort_field=last_contact&sort=up">'.html_print_image('images/sort_up.png', true, ['style' => $selectLastContactUp]).'</a>'.'<a href="index.php?search_category=users&keywords='.$config['search_keywords'].'&head_search_keywords=abc&offset='.$offset.'&sort_field=last_contact&sort=down">'.html_print_image('images/sort_down.png', true, ['style' => $selectLastContactDown]).'</a>';
    $table->head[4] = __('Profile').' '.'<a href="index.php?search_category=users&keywords='.$config['search_keywords'].'&head_search_keywords=abc&offset='.$offset.'&sort_field=profile&sort=up">'.html_print_image('images/sort_up.png', true, ['style' => $selectProfileUp]).'</a>'.'<a href="index.php?search_category=users&keywords='.$config['search_keywords'].'&head_search_keywords=abc&offset='.$offset.'&sort_field=profile&sort=down">'.html_print_image('images/sort_down.png', true, ['style' => $selectProfileDown]).'</a>';
    $table->head[5] = __('Description');



    $table->data = [];

    foreach ($users as $user) {
        $userIDCell = "<a href='?sec=gusuarios&sec2=godmode/users/configure_user&id=".$user['id_user']."'>".$user['id_user'].'</a>';

        if ($user['is_admin']) {
            $profileCell = html_print_image(
                'images/user_suit.png',
                true,
                [
                    'alt'   => __('Admin'),
                    'title' => __('Administrator'),
                ]
            ).'&nbsp;';
        } else {
            $profileCell = html_print_image(
                'images/user_green.png',
                true,
                [
                    'alt'   => __('User'),
                    'title' => __('Standard User'),
                    'class' => 'invert_filter',
                ]
            ).'&nbsp;';
        }

        $result = db_get_all_rows_field_filter('tusuario_perfil', 'id_usuario', $user['id_user']);
        if ($result !== false) {
            foreach ($result as $row) {
                $text_tip .= profile_get_name($row['id_perfil']);
                $text_tip .= ' / ';
                $text_tip .= groups_get_name($row['id_grupo']);
                $text_tip .= '<br />';
            }
        } else {
            $text_tip .= __('The user doesn\'t have any assigned profile/group');
        }

        $profileCell .= ui_print_help_tip($text_tip, true);

        array_push(
            $table->data,
            [
                $userIDCell,
                $user['fullname'],
                "<a href='mailto:".$user['email']."'>".$user['email'].'</a>',
                ui_print_timestamp($user['last_connect'], true),
                $profileCell,
                $user['comments'],
            ]
        );
    }

    echo '<br />';
    // ui_pagination($totalUsers);
    html_print_table($table);
    unset($table);
    ui_pagination($totalUsers);
}
