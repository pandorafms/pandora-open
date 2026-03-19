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


$file = [];
$file['name'] = '';
$file['description'] = '';
$file['hash'] = '';
$file['groups'] = [];
if (isset($file_id) && $file_id > 0) {
    $file = files_repo_get_files(['id' => $file_id]);
    if (empty($file)) {
        $file_id = 0;
    } else {
        $file = $file[$file_id];
    }
}

$table = new stdClass();
$table->width = '100%';
$table->class = 'databox filters filter-table-adv';
$table->size[0] = '50%';
$table->size[1] = '50%';
$table->data = [];

// GROUPS.
$groups = groups_get_all();
// Add the All group to the beginning to be always the first.
// Use this instead array_unshift to keep the array keys.
$groups = ([0 => __('All')] + $groups);
$groups_selected = [];
foreach ($groups as $id => $name) {
    if (in_array($id, $file['groups'])) {
        $groups_selected[] = $id;
    }
}

$row = [];
$row[0] = html_print_label_input_block(
    __('Groups'),
    html_print_select_groups(
        // Id_user.
        false,
        // Privilege.
        'AR',
        // ReturnAllGroup.
        true,
        // Name.
        'groups[]',
        // Selected.
        $groups_selected,
        // Script.
        '',
        // Nothing.
        '',
        // Nothing_value.
        0,
        // Return.
        true,
        // Multiple.
        true
    )
);

// DESCRIPTION.
$row[1] = html_print_label_input_block(
    __('Description').ui_print_help_tip(__('Only 200 characters are permitted'), true),
    html_print_textarea(
        'description',
        4,
        20,
        $file['description'],
        'class="file_repo_description" style="min-height: 60px; max-height: 60px;"',
        true
    )
);
$table->data[] = $row;

// FILE and SUBMIT BUTTON.
$row = [];
// Public checkbox.
$checkbox = html_print_checkbox('public', 1, (bool) !empty($file['hash']), true);
$style = 'class="inline padding-2-10"';

$row[0] = __('File');
if ($file_id > 0) {
    $submit_button = html_print_submit_button(
        __('Update'),
        'submit',
        false,
        ['icon' => 'wand'],
        true
    );

    $row[0] = html_print_label_input_block(
        __('File'),
        $file['name']
    );

    $row[1] = html_print_label_input_block(
        __('Public link'),
        $checkbox.html_print_input_hidden(
            'file_id',
            $file_id,
            true
        ).html_print_input_hidden(
            'update_file',
            1,
            true
        )
    );
} else {
    $submit_button = html_print_submit_button(
        __('Add'),
        'submit',
        false,
        ['icon' => 'wand'],
        true
    );

    $row[0] = html_print_label_input_block(
        __('File'),
        html_print_input_file(
            'upfile',
            true
        )
    );

    $row[1] = html_print_label_input_block(
        __('Public link'),
        $checkbox.html_print_input_hidden(
            'add_file',
            1,
            true
        )
    );
}



$table->data[] = $row;

$url = ui_get_full_url('index.php?sec=extensions&sec2=godmode/files_repo/files_repo');
echo '<form method="post" action="'.$url.'" enctype="multipart/form-data">';
html_print_table($table);
html_print_action_buttons($submit_button);
echo '</form>';

?>

<script language="javascript" type="text/javascript">

    $(document).ready (function () {

        var all_enabled = $(".chkb_all").prop("checked");
        if (all_enabled) {
            $(".chkb_group").prop("checked", false);
            $(".chkb_group").prop("disabled", true);
        }

        $(".chkb_all").click(function () {
            all_enabled = $(".chkb_all").prop("checked");
            if (all_enabled) {
                $(".chkb_group").prop("checked", false);
                $(".chkb_group").prop("disabled", true);
            } else {
                $(".chkb_group").prop("disabled", false);
            }
        });

    });

</script>