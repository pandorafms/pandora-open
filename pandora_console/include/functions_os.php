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

function os_agents_critical($id_os)
{
    global $config;

    $table = 'tagente';

    if (users_is_admin() === true) {
        return db_get_sql(
            sprintf(
                'SELECT COUNT(*)
                FROM %s
                WHERE %s.disabled=0 AND
                critical_count>0 AND id_os=%d',
                $table,
                $table,
                $id_os
            )
        );
    } else {
        $groups = array_keys(users_get_groups($config['id_user'], 'AR', false));

        return db_get_sql(
            sprintf(
                'SELECT COUNT(*)
                FROM %s
                WHERE %s.disabled=0 AND
                critical_count>0 AND
                id_os=%d AND id_grupo IN (%s)',
                $table,
                $table,
                $id_os,
                implode(',', $groups)
            )
        );
    }
}


// Get ok agents by using the status code in modules.
function os_agents_ok($id_os)
{
    global $config;

    $table = 'tagente';

    if (users_is_admin() === true) {
        return db_get_sql(
            sprintf(
                'SELECT COUNT(*)
                FROM %s
                WHERE %s.disabled=0 AND
                normal_count=total_count AND id_os=%d',
                $table,
                $table,
                $id_os
            )
        );
    } else {
        $groups = array_keys(users_get_groups($config['id_user'], 'AR', false));

        return db_get_sql(
            sprintf(
                'SELECT COUNT(*)
                FROM %s
                WHERE %s.disabled=0 AND
                normal_count=total_count AND
                id_os=%d AND id_grupo IN (%s)',
                $table,
                $table,
                $id_os,
                implode(',', $groups)
            )
        );
    }
}


// Get warning agents by using the status code in modules.
function os_agents_warning($id_os)
{
    global $config;

    $table = 'tagente';

    if (users_is_admin() === true) {
        return db_get_sql(
            sprintf(
                'SELECT COUNT(*)
                FROM %s
                WHERE %s.disabled=0 AND
                critical_count=0 AND warning_count>0
                AND id_os=%d',
                $table,
                $table,
                $id_os
            )
        );
    } else {
        $groups = array_keys(users_get_groups($config['id_user'], 'AR', false));

        return db_get_sql(
            sprintf(
                'SELECT COUNT(*)
                FROM %s
                WHERE %s.disabled=0 AND
                critical_count=0 AND warning_count>0 AND
                id_os=%d AND id_grupo IN (%s)',
                $table,
                $table,
                $id_os,
                implode(',', $groups)
            )
        );
    }
}


// Get unknown agents by using the status code in modules.
function os_agents_unknown($id_os)
{
    global $config;

    $table = 'tagente';

    if (users_is_admin() === true) {
        return db_get_sql(
            sprintf(
                'SELECT COUNT(*)
                FROM %s
                WHERE %s.disabled=0 AND
                critical_count=0 AND warning_count=0 AND
                unknown_count>0 AND id_os=%d',
                $table,
                $table,
                $id_os
            )
        );
    } else {
        $groups = array_keys(users_get_groups($config['id_user'], 'AR', false));

        return db_get_sql(
            sprintf(
                'SELECT COUNT(*)
                FROM %s
                WHERE %s.disabled=0 AND
                critical_count=0 AND warning_count=0 AND
                unknown_count>0 AND id_os=%d AND id_grupo IN (%s)',
                $table,
                $table,
                $id_os,
                implode(',', $groups)
            )
        );
    }
}


/**
 * Get total agents
 *
 * @param integer $id_os OS id.
 *
 * @return array|boolean
 */
function os_agents_total(int $id_os)
{
    global $config;

    $table = 'tagente';

    if (users_is_admin() === true) {
        return db_get_sql(
            sprintf(
                'SELECT COUNT(*)
                FROM %s
                WHERE %s.disabled=0 AND id_os=%d',
                $table,
                $table,
                $id_os
            )
        );
    } else {
        $groups = array_keys(users_get_groups($config['id_user'], 'AR', false));

        return db_get_sql(
            sprintf(
                'SELECT COUNT(*)
                FROM %s
                WHERE %s.disabled=0 AND id_os=%d AND id_grupo IN (%s)',
                $table,
                $table,
                $id_os,
                implode(',', $groups)
            )
        );
    }
}


// Get the name of a group given its id.
function os_get_name($id_os)
{
    return db_get_value('name', 'tconfig_os', 'id_os', (int) $id_os);
}


function os_get_os($hash=false)
{
    $result = [];
    $op_systems = db_get_all_rows_in_table('tconfig_os');
    if (empty($op_systems)) {
        $op_systems = [];
    }

    if ($hash) {
        foreach ($op_systems as $key => $value) {
            $result[$value['id_os']] = $value['name'];
        }
    } else {
        $result = $op_systems;
    }

    return $result;
}


function os_get_icon($id_os)
{
    return db_get_value('icon_name', 'tconfig_os', 'id_os', (int) $id_os);
}


/**
 * Transform the old icon url.
 *
 * @param string $url_icon Icon url .
 *
 * @return string
 */
function os_transform_url_icon($url_icon)
{
    $return = substr($url_icon, 0, strpos($url_icon, basename($url_icon)));
    switch (basename($url_icon)) {
        case 'android.png':
            $return .= 'android@os.svg';
        break;

        case 'so_mac.png':
            $return .= 'apple@os.svg';
        break;

        case 'so_cisco.png':
            $return .= 'cisco@os.svg';
        break;

        case 'so_aix.png':
            $return .= 'aix@os.svg';
        break;

        case 'so_win.png':
            $return .= 'windows@os.svg';
        break;

        case 'so_vmware.png':
            $return .= 'vmware@os.svg';
        break;

        case 'so_solaris.png':
            $return .= 'solaris@os.svg';
        break;

        case 'so_linux.png':
            $return .= 'linux@os.svg';
        break;

        case 'so_bsd.png':
            $return .= 'freebsd@os.svg';
        break;

        case 'so_cluster.png':
            $return .= 'cluster@os.svg';
        break;

        case 'so_other.png':
            $return .= 'other-OS@os.svg';
        break;

        case 'so_switch.png':
            $return .= 'switch@os.svg';
        break;

        case 'so_mainframe.png':
            $return .= 'mainframe@os.svg';
        break;

        case 'so_hpux.png':
        case 'server_hpux.png':
            $return .= 'HP@os.svg';
        break;

        case 'so_router.png':
        case 'router.png':
            $return .= 'routers@os.svg';
        break;

        case 'embedded.png':
            $return .= 'embedded@os.svg';
        break;

        case 'network.png':
            $return .= 'network-server@os.svg';
        break;

        case 'satellite.png':
            $return .= 'satellite@os.svg';
        break;

        default:
            $return = $url_icon;
        break;
    }

    return $return;
}
