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
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
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

$networkmap = get_parameter('networkmap', false);

global $config;

require_once $config['homedir'].'/include/class/NetworkMap.class.php';


if ($networkmap) {
    $networkmap_id = get_parameter('networkmap_id', 0);
    $dashboard = get_parameter('dashboard', 0);
    $size = get_parameter('size', []);
    $x_offset = get_parameter('x_offset', 0);
    $y_offset = get_parameter('y_offset', 0);
    $zoom_dash = get_parameter('zoom_dash', 0.5);

    // Dashboard mode.
    $ignore_acl = (bool) get_parameter('ignore_acl', 0);

    $networkmap = db_get_row_filter('tmap', ['id' => $networkmap_id]);

    if ($ignore_acl === false) {
        // ACL for the network map.
        $networkmap_read = check_acl($config['id_user'], $networkmap['id_group'], 'MR');
        $networkmap_write = check_acl($config['id_user'], $networkmap['id_group'], 'MW');
        $networkmap_manage = check_acl($config['id_user'], $networkmap['id_group'], 'MM');

        if (!$networkmap_read && !$networkmap_write && !$networkmap_manage) {
            db_pandora_audit(
                AUDIT_LOG_ACL_VIOLATION,
                'Trying to access networkmap'
            );
            include 'general/noaccess.php';

            return;
        }
    }

    ob_start();

    if ($networkmap['generation_method'] == LAYOUT_RADIAL_DYNAMIC) {
        $data['name'] = '<a href="index.php?'.'sec=network&'.'sec2=operation/agentes/networkmap.dinamic&'.'activeTab=radial_dynamic&'.'id_networkmap='.$networkmap['id'].'">'.$networkmap['name'].'</a>';
        global $id_networkmap;
        $id_networkmap = $networkmap['id'];
        $tab = 'radial_dynamic';
        if (empty($size) === false) {
            if ($size['width'] > $size['height']) {
                $width = $size['height'];
                $height = ($size['height'] - 10);
            } else {
                $width = $size['width'];
                $height = ($size['width'] + 50);
            }
        }

        include_once 'operation/agentes/networkmap.dinamic.php';
    } else {
        $map = new NetworkMap(
            [
                'id_map'      => $networkmap_id,
                'widget'      => 1,
                'pure'        => 1,
                'no_popup'    => 1,
                'map_options' => [
                    'x_offs' => $x_offset,
                    'y_offs' => $y_offset,
                    'z_dash' => $zoom_dash,
                ],


            ]
        );

        $map->printMap(false, $ignore_acl);
    }

    $return = ob_get_clean();

    echo $return;

    return;
}
