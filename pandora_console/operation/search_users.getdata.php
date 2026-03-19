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
if (!$searchUsers) {
    $totalUsers = 0;
    return;
}

$selectUserIDUp = '';
$selectUserIDDown = '';
$selectNameUp = '';
$selectNameDown = '';
$selectEmailUp = '';
$selectEmailDown = '';
$selectLastContactUp = '';
$selectLastContactDown = '';
$selectProfileUp = '';
$selectProfileDown = '';

switch ($sortField) {
    case 'id_user':
        switch ($sort) {
            case 'up':
                $selectUserIDUp = $selected;
                $order = [
                    'field' => 'id_user',
                    'order' => 'ASC',
                ];
            break;

            case 'down':
                $selectUserIDDown = $selected;
                $order = [
                    'field' => 'id_user',
                    'order' => 'DESC',
                ];
            break;
        }
    break;

    case 'name':
        switch ($sort) {
            case 'up':
                $selectNameUp = $selected;
                $order = [
                    'field' => 'fullname',
                    'order' => 'ASC',
                ];
            break;

            case 'down':
                $selectNameDown = $selected;
                $order = [
                    'field' => 'fullname',
                    'order' => 'DESC',
                ];
            break;
        }
    break;

    case 'email':
        switch ($sort) {
            case 'up':
                $selectLastContactUp = $selected;
                $order = [
                    'field' => 'email',
                    'order' => 'ASC',
                ];
            break;

            case 'down':
                $selectEmailDown = $selected;
                $order = [
                    'field' => 'email',
                    'order' => 'DESC',
                ];
            break;
        }
    break;

    case 'last_contact':
        switch ($sort) {
            case 'up':
                $selectLastContactUp = $selected;
                $order = [
                    'field' => 'last_connect',
                    'order' => 'ASC',
                ];
            break;

            case 'down':
                $selectLastContactDown = $selected;
                $order = [
                    'field' => 'last_connect',
                    'order' => 'DESC',
                ];
            break;
        }
    break;

    case 'last_contact':
        switch ($sort) {
            case 'up':
                $selectLastContactUp = $selected;
                $order = [
                    'field' => 'last_connect',
                    'order' => 'ASC',
                ];
            break;

            case 'down':
                $selectLastContactDown = $selected;
                $order = [
                    'field' => 'last_connect',
                    'order' => 'DESC',
                ];
            break;
        }
    break;

    case 'profile':
        switch ($sort) {
            case 'up':
                $selectProfileUp = $selected;
                $order = [
                    'field' => 'is_admin',
                    'order' => 'ASC',
                ];
            break;

            case 'down':
                $selectProfileDown = $selected;
                $order = [
                    'field' => 'is_admin',
                    'order' => 'DESC',
                ];
            break;
        }
    break;

    default:
        $selectUserIDUp = $selected;
        $selectUserIDDown = '';
        $selectNameUp = '';
        $selectNameDown = '';
        $selectEmailUp = '';
        $selectEmailDown = '';
        $selectLastContactUp = '';
        $selectLastContactDown = '';
        $selectProfileUp = '';
        $selectProfileDown = '';

        $order = [
            'field' => 'id_user',
            'order' => 'ASC',
        ];
    break;
}

if ($searchUsers) {
    switch ($config['dbtype']) {
        case 'mysql':
        case 'postgresql':
            $sql = "SELECT id_user, fullname, firstname, lastname, middlename, email, last_connect, is_admin, comments FROM tusuario
				WHERE REPLACE(fullname, '&#x20;', ' ')  LIKE '%".$stringSearchSQL."%' OR
					REPLACE(id_user, '&#x20;', ' ')  LIKE '%".$stringSearchSQL."%' OR
					REPLACE(firstname, '&#x20;', ' ')  LIKE '%".$stringSearchSQL."%' OR
					REPLACE(lastname, '&#x20;', ' ')  LIKE '%".$stringSearchSQL."%' OR
					REPLACE(middlename, '&#x20;', ' ')  LIKE '%".$stringSearchSQL."%' OR
					REPLACE(email, '&#x20;', ' ')  LIKE '%".$stringSearchSQL."%'
				ORDER BY ".$order['field'].' '.$order['order'];
        break;

        case 'oracle':
            $sql = "SELECT id_user, fullname, firstname, lastname, middlename, email, last_connect, is_admin, comments FROM tusuario
				WHERE upper(REPLACE(fullname, '&#x20;', ' ') ) LIKE '%".strtolower($stringSearchSQL)."%' OR
					upper(REPLACE(id_user, '&#x20;', ' ') ) LIKE '%".strtolower($stringSearchSQL)."%' OR
					upper(REPLACE(firstname, '&#x20;', ' ') ) LIKE '%".strtolower($stringSearchSQL)."%' OR
					upper(REPLACE(lastname, '&#x20;', ' ') ) LIKE '%".strtolower($stringSearchSQL)."%' OR
					upper(REPLACE(middlename, '&#x20;', ' ') ) LIKE '%".strtolower($stringSearchSQL)."%' OR
					upper(REPLACE(email, '&#x20;', ' ') ) LIKE '%".strtolower($stringSearchSQL)."%'
					ORDER BY ".$order['field'].' '.$order['order'];
        break;
    }

    switch ($config['dbtype']) {
        case 'mysql':
        case 'postgresql':
            $sql .= ' LIMIT '.$config['block_size'].' OFFSET '.get_parameter('offset', 0);
        break;

        case 'oracle':
            $set = [];
            $set['limit'] = $config['block_size'];
            $set['offset'] = (int) get_parameter('offset');

            $sql = oracle_recode_query($sql, $set);
        break;
    }

    $users = db_process_sql($sql);

    if ($users !== false) {
        // Check ACLs
        $users_id = [];
        foreach ($users as $key => $user) {
            $user_can_manage_all = users_can_manage_group_all('UM');

            $user_groups = users_get_groups(
                $user['id_user'],
                false,
                $user_can_manage_all
            );

            // Get group IDs.
            $user_groups = array_keys($user_groups);

            if (check_acl_one_of_groups($config['id_user'], $user_groups, 'UM') === false
                && $config['id_user'] != $user['id_user']
                || (users_is_admin($config['id_user']) === false
                && users_is_admin($user['id_user']) === true)
            ) {
                unset($users[$key]);
            } else {
                $users_id[] = $user['id_user'];
            }
        }

        if ($only_count) {
            $totalUsers = count($users);
            unset($users);
        }
    } else {
        $totalUsers = 0;
    }
}
