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

// Load global vars.
global $config;

check_login();

if (! check_acl($config['id_user'], 0, 'PM')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access module management'
    );
    include 'general/noaccess.php';
    exit;
}

// Header.
ui_print_standard_header(
    __('Defined module types'),
    'images/module_group.png',
    false,
    '',
    true,
    [],
    [
        [
            'link'  => '',
            'label' => __('Resources'),
        ],
        [
            'link'  => '',
            'label' => __('Module types'),
        ],
    ]
);


$update_module = (bool) get_parameter_post('update_module');

// Update
if ($update_module === true) {
    $name = get_parameter_post('name');
    $id_type = get_parameter_post('id_type');
    $description = get_parameter_post('description');
    $icon = get_parameter_post('icon');
    $category = get_parameter_post('category');

    $values = [
        'descripcion' => $description,
        'categoria'   => $category,
        'nombre'      => $name,
        'icon'        => $icon,
    ];

    $result = db_process_sql_update('ttipo_modulo', $values, ['id_tipo' => $id_type]);

    if (! $result) {
        ui_print_error_message(__('Problem modifying module'));
    } else {
        ui_print_success_message(__('Module updated successfully'));
    }
}

$table = new stdClass();
$table->id = 'module_type_list';
$table->class = 'info_table';
$table->size = [];
$table->size[0] = '5%';
$table->size[1] = '5%';
$table->head = [];
$table->head[0] = __('ID');
$table->head[1] = __('Icon');
$table->head[2] = __('Name');
$table->head[3] = __('Description');

$table->data = [];

$rows = db_get_all_rows_sql('SELECT * FROM ttipo_modulo ORDER BY id_tipo');
if ($rows === false) {
    $rows = [];
}

foreach ($rows as $row) {
    $data[0] = $row['id_tipo'];
    $data[1] = html_print_image('images/'.$row['icon'], true, ['class' => 'main_menu_icon invert_filter']);
    $data[2] = $row['nombre'];
    $data[3] = $row['descripcion'];

    array_push($table->data, $data);
}

html_print_table($table);
// $tablePagination = ui_pagination($total_groups, $url, $offset, 0, true, 'offset', false);
