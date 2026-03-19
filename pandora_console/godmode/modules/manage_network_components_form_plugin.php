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

check_login();

$data = [];
$data[0] = html_print_label_input_block(
    __('Plugin'),
    html_print_select_from_sql(
        'SELECT id, name FROM tplugin ORDER BY name',
        'id_plugin',
        $id_plugin,
        'javascript: load_plugin_macros_fields(\'network_component-macro\')',
        __('None'),
        0,
        true,
        false,
        false,
        false,
        'width: 100%;'
    ).html_print_input_hidden('macros', base64_encode($macros), true)
    // Store the macros in base64 into a hidden control to move between pages.
);

$data[1] = html_print_label_input_block(
    __('Post process'),
    html_print_extended_select_for_post_process(
        'post_process',
        $post_process,
        '',
        __('Empty'),
        '0',
        false,
        true,
        false,
        true
    )
);

push_table_row($data, 'plugin_1');

// A hidden "model row" to clone it from javascript to add fields dynamicly.
$data = [];
$data[0] = html_print_label_input_block(
    __('macro_desc').ui_print_help_tip('macro_help', true),
    html_print_input_text('macro_name', 'macro_value', '', 100, 1024, true)
);

$table->colspan['macro_field'][0] = 2;
$table->rowstyle['macro_field'] = 'display:none';

push_table_row($data, 'macro_field');

// If there are $macros, we create the form fields.
if (!empty($macros)) {
    $macros = json_decode($macros, true);

    foreach ($macros as $k => $m) {
        $data = [];
        $macro_label = $m['desc'];
        if (!empty($m['help'])) {
            $macro_label .= ui_print_help_tip($m['help'], true);
        }

        if ($m['hide'] == 1) {
            $macro_input = html_print_input_text(
                $m['macro'],
                io_output_password($m['value']),
                '',
                100,
                1024,
                true,
                false,
                '',
                'w50p'
            );
        } else {
            $macro_input = html_print_input_text(
                $m['macro'],
                $m['value'],
                '',
                100,
                1024,
                true,
                false,
                false,
                '',
                'w50p'
            );
        }

        $data[0] = html_print_label_input_block(
            $macro_label,
            $macro_input
        );

        $table->colspan['macro'.$m['macro']][0] = 2;
        $table->rowclass['macro'.$m['macro']] = 'macro_field';

        push_table_row($data, 'macro'.$m['macro']);
    }
}
