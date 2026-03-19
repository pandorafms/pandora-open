<?PHP

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

if (! check_acl($config['id_user'], 0, 'PM') && ! is_user_admin($config['id_user'])) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Link Management'
    );
    include 'general/noaccess.php';
    exit;
}

// Header
ui_print_standard_header(
    __('Admin tools'),
    'images/extensions.png',
    false,
    '',
    true,
    [],
    [
        [
            'link'  => '',
            'label' => __('Link management'),
        ],
    ]
);


if (isset($_POST['create'])) {
    // If create
    $name = get_parameter_post('name');
    $link = get_parameter_post('link');

    $result = false;
    if ($name != '') {
        $result = db_process_sql_insert('tlink', ['name' => $name, 'link' => $link]);
    }

    if (! $result) {
        ui_print_error_message(__('There was a problem creating link'));
    } else {
        $id_link = $result;
        ui_print_result_message(
            $id_link,
            __('Successfully created'),
            __('Could not be created')
        );
    }
}

if (isset($_POST['update'])) {
    // if update
    $id_link = io_safe_input($_POST['id_link']);
    $name = io_safe_input($_POST['name']);
    $link = io_safe_input($_POST['link']);

    $result = false;
    if ($name != '') {
        $result = db_process_sql_update('tlink', ['name' => $name, 'link' => $link], ['id_link' => $id_link]);
    }

    if (! $result) {
        ui_print_error_message(__('There was a problem modifying link'));
    } else {
        ui_print_success_message(__('Successfully updated'));
    }
}

if (isset($_GET['borrar'])) {
    // if delete
    $id_link = io_safe_input($_GET['borrar']);

    $result = db_process_sql_delete('tlink', ['id_link' => $id_link]);

    if (! $result) {
        ui_print_error_message(__('There was a problem deleting link'));
    } else {
        ui_print_success_message(__('Successfully deleted'));
    }
}

// Main form view for Links edit
if ((isset($_GET['form_add'])) or (isset($_GET['form_edit']))) {
    if (isset($_GET['form_edit'])) {
        $creation_mode = 0;
            $id_link = io_safe_input($_GET['id_link']);

            $row = db_get_row('tlink', 'id_link', $id_link);

        if ($row !== false) {
            $nombre = $row['name'];
            $link = $row['link'];
        } else {
            ui_print_error_message(__('Name error'));
        }
    } else {
        // form_add
        $creation_mode = 1;
        $nombre = '';
        $link = '';
    }

    echo '<form name="ilink" method="post" action="index.php?sec=gsetup&sec2=godmode/setup/links">';
    echo '<table class="databox filters filter-table-adv max_floating_element_size" cellpadding="4" cellspacing="4" width="100%">';
    if ($creation_mode == 1) {
        echo "<input type='hidden' name='create' value='1'>";
    } else {
        echo "<input type='hidden' name='update' value='1'>";
    }

    echo "<input type='hidden' name='id_link' value='";
    if (isset($id_link)) {
        echo $id_link;
    }

    echo "'>";
    echo '<tr>';
    echo '<td class="w50p">';
    echo html_print_label_input_block(
        __('Link name'),
        html_print_input_text(
            'name',
            $nombre,
            '',
            50,
            255,
            true,
            false,
            true,
            '',
            'text_input'
        )
    );
    echo '</td>';
    echo '<td class="w50p">';
    echo html_print_label_input_block(
        __('Link'),
        html_print_input_text(
            'link',
            $link,
            '',
            50,
            255,
            true,
            false,
            true,
            '',
            'text_input'
        )
    );
    echo '</td></tr>';
    echo '</table>';
    if (isset($_GET['form_add']) === true) {
        $actionForPerform = __('Create');
        $iconForPerform = 'wand';
    } else {
        $actionForPerform = __('Update');
        $iconForPerform = 'update';
    }

    html_print_action_buttons(
        html_print_submit_button(
            $actionForPerform,
            'crtbutton',
            false,
            [ 'icon' => $iconForPerform ],
            true
        )
    );

    echo '</td></tr></table></form>';
} else {
    // Main list view for Links editor.
    $rows = db_get_all_fields_in_table('tlink', '', '', 'name');
    if ($rows === false) {
        $rows = [];
    }

    if (empty($rows)) {
        ui_print_info_message(['no_close' => true, 'message' => __("There isn't links") ]);
    } else {
        echo "<table cellpadding='0' cellspacing='0' class='info_table w100p'>";
        echo '<thead><tr>';
        echo "<th width='180px'>".__('Link name').'</th>';
        echo "<th width='10px'>".__('Delete').'</th>';
        echo '</tr></thead>';

        $color = 1;
        foreach ($rows as $row) {
            if ($color == 1) {
                $tdcolor = 'datos';
                $color = 0;
            } else {
                $tdcolor = 'datos2';
                $color = 1;
            }

            echo "<tr><td class='$tdcolor'><b><a href='index.php?sec=gsetup&sec2=godmode/setup/links&form_edit=1&id_link=".$row['id_link']."'>".$row['name'].'</a></b></td>';
            echo '<td class="'.$tdcolor.' table_action_buttons"><a href="index.php?sec=gsetup&sec2=godmode/setup/links&id_link='.$row['id_link'].'&borrar='.$row['id_link'].'" onClick="if (!confirm(\' '.__('Are you sure?').'\')) return false;">'.html_print_image(
                'images/delete.svg',
                true,
                [
                    'class' => 'invert_filter main_menu_icon',
                    'title' => __('Delete'),
                ]
            ).'</a></td></tr>';
        }

        echo '</table>';
    }

    echo "<table width='100%'>";
    echo "<tr><td align='right'>";
    echo "<form method='post' action='index.php?sec=gsetup&sec2=godmode/setup/links&form_add=1'>";

    html_print_action_buttons(
        html_print_submit_button(
            __('Add'),
            'form_add',
            false,
            [ 'icon' => 'wand' ],
            true
        )
    );

    echo '</form></table>';
}
