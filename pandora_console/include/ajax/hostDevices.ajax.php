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

$get_explanation = (bool) get_parameter('get_explanation', 0);
$get_recon_script_macros = get_parameter('get_recon_script_macros', 0);

if ($get_explanation) {
    $id = (int) get_parameter('id', 0);

    $explanation = db_get_value(
        'description',
        'trecon_script',
        'id_recon_script',
        $id
    );

    echo io_safe_output($explanation);

    return;
}

if ($get_recon_script_macros) {
    $id_recon_script = (int) get_parameter('id');
    $id_recon_task = (int) get_parameter('id_rt');

    if (!empty($id_recon_task) && empty($id_recon_script)) {
        $recon_script_macros = db_get_value(
            'macros',
            'trecon_task',
            'id_rt',
            $id_recon_task
        );
    } else if (!empty($id_recon_task)) {
        $recon_task_id_rs = (int) db_get_value(
            'id_recon_script',
            'trecon_task',
            'id_rt',
            $id_recon_task
        );

        if ($id_recon_script == $recon_task_id_rs) {
            $recon_script_macros = db_get_value(
                'macros',
                'trecon_task',
                'id_rt',
                $id_recon_task
            );
        } else {
            $recon_script_macros = db_get_value(
                'macros',
                'trecon_script',
                'id_recon_script',
                $id_recon_script
            );
        }
    } else if (!empty($id_recon_script)) {
        $recon_script_macros = db_get_value(
            'macros',
            'trecon_script',
            'id_recon_script',
            $id_recon_script
        );
    } else {
        $recon_script_macros = [];
    }

    $macros = [];
    $macros['base64'] = base64_encode($recon_script_macros);
    $macros['array'] = json_decode($recon_script_macros, true);

    echo io_json_mb_encode($macros);
    return;
}
