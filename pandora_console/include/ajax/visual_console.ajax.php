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

require_once 'include/functions_visual_map.php';
$id_visual_console = get_parameter('id_visual_console', null);

// Login check.
check_login();

// Fix: IW was the old ACL to check for report editing, now is RW
if (! check_acl($config['id_user'], 0, 'VR')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access report builder'
    );
    include 'general/noaccess.php';
    exit;
}


// Fix ajax to avoid include the file, 'functions_graph.php'.
$ajax = true;

$render_map = (bool) get_parameter('render_map', false);
$graph_javascript = (bool) get_parameter('graph_javascript', false);
$force_remote_check = (bool) get_parameter('force_remote_check', false);
$update_maintanance_mode = (bool) get_parameter('update_maintanance_mode', false);
$load_css_cv = (bool) get_parameter('load_css_cv', false);
$update_grid_style = (bool) get_parameter('update_grid_style', false);

if ($render_map) {
    $width = (int) get_parameter('width', '400');
    $height = (int) get_parameter('height', '400');
    $keep_aspect_ratio = (bool) get_parameter('keep_aspect_ratio');

    visual_map_print_visual_map(
        $id_visual_console,
        true,
        true,
        $width,
        $height,
        '',
        false,
        $graph_javascript,
        $keep_aspect_ratio
    );
    return;
}

if ($force_remote_check) {
    $id_layout = (int) get_parameter('id_layout', false);
    $data = db_get_all_rows_sql(
        sprintf(
            'SELECT id_agent FROM tlayout_data WHERE id_layout = %d AND id_agent <> 0',
            $id_layout
        )
    );

    if (empty($data)) {
        echo '0';
    } else {
        $ids = [];
        foreach ($data as $key => $value) {
            $ids[] = $value['id_agent'];
        }

        $sql = sprintf(
            'UPDATE `tagente_modulo` SET flag = 1 WHERE `id_agente` IN (%s)',
            implode(',', $ids)
        );

        $result = db_process_sql($sql);
        if ($result) {
            echo true;
        } else {
            echo '0';
        }
    }

    return;
}

if ($load_css_cv === true) {
    $uniq = get_parameter('uniq', 0);
    $ratio = get_parameter('ratio', 0);
    return;
}

if ($update_maintanance_mode === true) {
    $idVisualConsole = (int) get_parameter('idVisualConsole', 0);
    $mode = (bool) get_parameter('mode', false);

    $values = [];
    if ($mode === true) {
        $values['maintenance_mode'] = json_encode(
            [
                'user'      => $config['id_user'],
                'timestamp' => time(),
            ]
        );
    } else {
        $values['maintenance_mode'] = null;
    }

    $result = db_process_sql_update(
        'tlayout',
        $values,
        ['id' => $idVisualConsole]
    );

    echo json_encode(['result' => $result]);
    return;
}

if ($update_grid_style === true) {
    $idVisualConsole = (int) get_parameter('idVisualConsole', 0);
    $color = get_parameter('color', '#CCC');
    $size = get_parameter('size', '10');

    $values = [];
    $values['grid_color'] = $color;
    $values['grid_size'] = $size;

    $result = db_process_sql_update(
        'tlayout',
        $values,
        ['id' => $idVisualConsole]
    );

    echo json_encode(['result' => $result]);
    return;
}
