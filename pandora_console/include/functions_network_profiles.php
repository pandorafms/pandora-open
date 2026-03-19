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


/**
 * @package    Include
 * @subpackage Network profiles
 */


/**
 * Get a network profile.
 *
 * @param int Profile id to get.
 * @param array Extra filter.
 * @param array Fields to get.
 *
 * @return Profile with the given id. False if not available or readable.
 */
function network_profiles_get_network_profile($id_network_profile, $filter=false, $fields=false)
{
    global $config;

    $id_network_profile = safe_int($id_network_profile);
    if (empty($id_network_profile)) {
        return false;
    }

    if (! is_array($filter)) {
        $filter = [];
    }

    $filter['id_np'] = $id_network_profile;

    return @db_get_row_filter('tnetwork_profile', $filter, $fields);
}


/**
 * Deletes a network_profile.
 *
 * @param int Network profile id to be deleted.
 *
 * @return boolean True if deleted, false otherwise.
 */
function network_profiles_delete_network_profile($id_network_profile)
{
    $id_network_profile = safe_int($id_network_profile);
    if (empty($id_network_profile)) {
        return false;
    }

    $profile = network_profiles_get_network_profile($id_network_profile);
    if ($profile === false) {
        return false;
    }

    return @db_process_sql_delete(
        'tnetwork_profile',
        ['id_np' => $id_network_profile]
    );
}


/**
 * Get a network profile name.
 *
 * @param int Id network profile
 *
 * @return string Name of the given network profile.
 */
function network_profiles_get_name($id_network_profile)
{
    return (string) db_get_value('name', 'tnetwork_profile', 'id_np', $id_network_profile);
}
