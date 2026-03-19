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

function extension_uploader_extensions()
{
    global $config;

    if (!check_acl($config['id_user'], 0, 'PM')) {
        db_pandora_audit(
            AUDIT_LOG_ACL_VIOLATION,
            'Trying to access Group Management'
        );
        include 'general/noaccess.php';

        return;
    }

    // Header.
    ui_print_standard_header(
        __('Extensions'),
        'images/extensions.png',
        false,
        '',
        true,
        [],
        [
            [
                'link'  => '',
                'label' => __('Admin tools'),
            ],
            [
                'link'  => '',
                'label' => __('Extension manager'),
            ],
            [
                'link'  => '',
                'label' => __('Uploader extension'),
            ],
        ]
    );

    $upload = (bool) get_parameter('upload', 0);

    if ($upload) {
        $error = $_FILES['extension']['error'];

        if ($error == 0) {
            $zip = new ZipArchive;

            $tmpName = $_FILES['extension']['tmp_name'];

            $pathname = $config['homedir'].'/'.EXTENSIONS_DIR.'/';

            if ($zip->open($tmpName) === true) {
                $result = $zip->extractTo($pathname);
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }

        if ($result) {
            db_pandora_audit(
                AUDIT_LOG_EXTENSION_MANAGER,
                'Upload extension '.$_FILES['extension']['name']
            );
        }

        ui_print_result_message(
            $result,
            __('Success to upload extension'),
            __('Fail to upload extension')
        );
    }

    $table = new stdClass();

    $table->width = '100%';
    $table->class = 'databox filters filter-table-adv';
    $table->size[0] = '20%';
    $table->size[1] = '20%';
    $table->size[2] = '60%';
    $table->data = [];

    $table->data[0][0] = html_print_label_input_block(
        __('Upload extension').ui_print_help_tip(__('Upload the extension as a zip file.'), true),
        html_print_input_file(
            'extension',
            true,
            [
                'required' => true,
                'accept'   => '.zip',
            ]
        )
    );

    $table->data[0][1] = '';
    $table->data[0][2] = '';

    echo "<form method='post' enctype='multipart/form-data'>";
    html_print_table($table);
    html_print_input_hidden('upload', 1);
    html_print_action_buttons(
        html_print_submit_button(
            __('Upload'),
            'submit',
            false,
            ['icon' => 'wand'],
            true
        )
    );
    echo '</form>';
}


extensions_add_godmode_menu_option(__('Extension uploader'), 'PM', null, null, 'v1r1');
extensions_add_godmode_function('extension_uploader_extensions');
