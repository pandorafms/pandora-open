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
 * @subpackage ExportServer
 */


/**
 * Gets all export servers out of the database
 *
 * @param (bool) $active Whether or not to exclude inactive servers (defaults to 1 => no inactive servers)
 *
 * @return (array) An array of server information (similar to server_info) but without the other servers
 **/
function exportserver_get_exportservers($active=1)
{
    $query = 'SELECT * FROM tserver WHERE export_server = 1';
    $return = [];

    if ($active == 1) {
        $servers = db_get_all_rows_sql($query.' AND status = 1');
    } else {
        $servers = db_get_all_rows_sql($query);
    }

    if (empty($servers)) {
        return $return;
    }

    foreach ($servers as $server) {
        $return[$server['id_server']] = $server;
    }

    return $return;
}


/**
 * Gets a specific piece of info on the export servers table (defaults to name)
 *
 * @param (bool)   $active (bool) Whether or not to exclude inactive servers (defaults to 1 => no inactive servers)
 * @param (string) $row    What row to select from the server info table
 *
 * @return (array) An array of server information (similar to exportserver_get_exportservers) but without the extra data
 **/
function exportserver_get_info($active=1, $row='name')
{
    $exportservers = exportserver_get_exportservers();
    $return = [];

    foreach ($exportservers as $server_id => $server_info) {
        $return[$server_id] = $server_info[$row];
    }

    return $return;
}


/**
 * Get the name of an exporting server
 *
 * @param integer $id_server Server id
 *
 * @return string The name of given server.
 */
function exportserver_get_name($id_server)
{
    return (string) db_get_value('name', 'tserver_export', 'id', (int) $id_server);
}
