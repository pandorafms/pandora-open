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

// Begin.
global $config;

// Check user credentials
check_login();

$id = (int) get_parameter('id_networkmap', 0);

$new_empty_networkmap = (bool) get_parameter('new_empty_networkmap', false);
$edit_networkmap = (bool) get_parameter('edit_networkmap', false);

$not_found = false;

if (empty($id)) {
    $new_empty_networkmap = true;
    $edit_networkmap = false;
}

if ($new_empty_networkmap) {
    $name = '';
    $id_group = 0;
    $node_radius = 40;
    $description = '';
}

if ($edit_networkmap) {

    $disabled_source = true;

    $values = db_get_row('tmap', 'id', $id);

    $not_found = false;
    if ($values === false) {
        $not_found = true;
    } else {
        $id_group = $values['id_group'];

        // ACL for the network map
        // $networkmap_read = check_acl ($config['id_user'], $id_group, "MR");
        $networkmap_write = check_acl($config['id_user'], $id_group, 'MW');
        $networkmap_manage = check_acl($config['id_user'], $id_group, 'MM');

        if (!$networkmap_write && !$networkmap_manage) {
            db_pandora_audit(
                AUDIT_LOG_ACL_VIOLATION,
                'Trying to access networkmap'
            );
            include 'general/noaccess.php';
            return;
        }

        $name = io_safe_output($values['name']);

        $description = $values['description'];

        $filter = json_decode($values['filter'], true);

        $node_radius = $filter['node_radius'];
    }
}

// Header.
ui_print_standard_header(
    __('Empty Network maps editor'),
    'images/bricks.png',
    false,
    '',
    false,
    [],
    [
        [
            'link'  => '',
            'label' => __('Topology maps'),
        ],
        [
            'link'  => '',
            'label' => __('Networkmap'),
        ],
    ]
);


if ($not_found) {
    ui_print_error_message(__('Not found networkmap.'));
} else {
    $table = new StdClass();
    $table->id = 'form_editor';

    $table->width = '100%';
    $table->class = 'databox filter-table-adv';

    $table->style = [];
    $table->style[0] = 'width: 50%';
    $table->data = [];

    $table->data[0][] = html_print_label_input_block(
        __('Name'),
        html_print_input_text(
            'name',
            $name,
            '',
            30,
            100,
            true
        ),
        [ 'div_class' => 'w50p' ]
    );

    $table->data[1][] = html_print_label_input_block(
        __('Group'),
        html_print_select_groups(
            false,
            'AR',
            true,
            'id_group',
            $id_group,
            '',
            '',
            0,
            true
        ),
        [ 'div_class' => 'w50p' ]
    );

    $table->data[2][] = html_print_label_input_block(
        __('Node radius'),
        html_print_input_text(
            'node_radius',
            $node_radius,
            '',
            2,
            10,
            true
        ),
        [ 'div_class' => 'w50p' ]
    );

    $table->data[3][] = html_print_label_input_block(
        __('Description'),
        html_print_textarea(
            'description',
            7,
            25,
            $description,
            '',
            true
        )
    );

    echo '<form method="post" action="index.php?sec=network&amp;sec2=operation/agentes/pandora_networkmap">';

    html_print_table($table);

    if ($new_empty_networkmap) {
        html_print_input_hidden('save_empty_networkmap', 1);
        $titleButton = __('Save networkmap');
    }

    if ($edit_networkmap) {
        html_print_input_hidden('id_networkmap', $id);
        html_print_input_hidden('update_empty_networkmap', 1);
        $titleButton = __('Update networkmap');
    }

    html_print_action_buttons(
        html_print_submit_button(
            $titleButton,
            'crt',
            false,
            ['icon' => 'next'],
            true
        )
    );

    echo '</form>';
}
