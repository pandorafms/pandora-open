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
 * @subpackage Migration
 */


function migration_open_networkmaps()
{
    global $config;

    include_once $config['homedir'].'/include/functions_maps.php';

    $old_networkmaps_open = db_get_all_rows_in_table('tnetwork_map');

    foreach ($old_networkmaps_open as $old_netw_open) {
        $new_networkmap = [];

        $new_networkmap['name'] = io_safe_output($old_netw_open['name']);
        $new_networkmap['id_user'] = $old_netw_open['id_user'];
        $new_networkmap['id_group'] = $old_netw_open['store_group'];
        $new_networkmap['source_period'] = MAP_REFRESH_TIME;

        switch ($old_netw_open['type']) {
            case 'radial_dynamic':
                $new_networkmap['type'] = MAP_TYPE_NETWORKMAP;
                $new_networkmap['subtype'] = MAP_SUBTYPE_RADIAL_DYNAMIC;
            break;

            case 'policies':
                $new_networkmap['type'] = MAP_TYPE_NETWORKMAP;
                $new_networkmap['subtype'] = MAP_SUBTYPE_POLICIES;
            break;

            case 'groups':
                $new_networkmap['type'] = MAP_TYPE_NETWORKMAP;
                $new_networkmap['subtype'] = MAP_SUBTYPE_GROUPS;
            break;

            case 'topology':
                $new_networkmap['type'] = MAP_TYPE_NETWORKMAP;
                $new_networkmap['subtype'] = MAP_SUBTYPE_TOPOLOGY;
            break;
        }

        // ---- Source -------------------------------------------------
        $new_networkmap['source'] = MAP_SOURCE_GROUP;
        $new_networkmap['source_data'] = $old_netw_open['id_group'];

        switch ($old_netw_open['layout']) {
            case 'radial':
                $new_networkmap['generation_method'] = MAP_GENERATION_RADIAL;
            break;
        }

        // ---- Filter -------------------------------------------------
        $filter = [];

        $filter['id_tag'] = 0;
        if ($old_netw_open['id_tag']) {
            $filter['id_tag'] = 1;
        }

        $filter['text'] = $old_netw_open['text_filter'];
        $filter['show_pandora_nodes'] = 0;

        switch ($old_netw_open['depth']) {
            case 'agents':
                $filter['show_modules'] = 0;
                $filter['show_agents'] = 1;
            break;

            case 'all':
                $filter['show_modules'] = 0;
                $filter['show_agents'] = 1;
            break;

            case 'groups':
                $filter['show_modules'] = 0;
                $filter['show_agents'] = 0;
            break;
        }

        $filter['only_modules_with_alerts'] = 0;
        if ($old_netw_open['only_modules_with_alerts']) {
            $filter['only_modules_with_alerts'] = 1;
        }

        $filter['show_module_group'] = 0;
        if ($old_netw_open['show_modulegroup']) {
            $filter['show_module_group'] = 1;
        }

        $filter['module_group'] = 0;
        if ($old_netw_open['id_module_group']) {
            $filter['module_group'] = 1;
        }

        $filter['only_policy_modules'] = 0;
        $filter['only_snmp_modules'] = 0;
        if ($old_netw_open['show_snmp_modules']) {
            $filter['only_snmp_modules'] = 1;
        }

        $new_networkmap['filter'] = json_encode($filter);
        // -------------------------------------------------------------
        maps_save_map($new_networkmap);
    }
}
