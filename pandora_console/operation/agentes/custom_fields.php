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

require_once 'include/functions_agents.php';

check_login();

$id_agente = get_parameter_get('id_agente', -1);

if ($id_agente === -1) {
    ui_print_error_message(__('There was a problem loading agent'));
    return;
}

// All groups is calculated in ver_agente.php. Avoid to calculate it again
if (!isset($all_groups)) {
    $all_groups = agents_get_all_groups_agent($idAgent, $id_group);
}

if (! check_acl_one_of_groups($config['id_user'], $all_groups, 'AR') && ! check_acl($config['id_user'], 0, 'AW')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Agent General Information'
    );
    include_once 'general/noaccess.php';
    return;
}

$all_customs_fields = (bool) check_acl_one_of_groups(
    $config['id_user'],
    $all_groups,
    'AW'
);

if ($all_customs_fields) {
    $fields = db_get_all_rows_filter('tagent_custom_fields');
} else {
    $fields = db_get_all_rows_filter(
        'tagent_custom_fields',
        ['display_on_front' => 1]
    );
}

if ($fields === false) {
    $fields = [];
    ui_print_empty_data(__('No fields defined'));
} else {
    $table = new stdClass();
    $table->width = '100%';
    $table->class = 'info_table';
    $table->head = [];
    $table->head[0] = __('Field');
    $table->size[0] = '20%';
    $table->head[1] = __('Display on front').ui_print_help_tip(__('The fields with display on front enabled will be displayed into the agent details'), true);
    $table->size[1] = '20%';
    $table->head[2] = __('Description');
    $table->align = [];
    $table->align[1] = 'left';
    $table->align[2] = 'left';
    $table->data = [];

    foreach ($fields as $field) {
        $data[0] = '<b>'.$field['name'].'</b>';

        if ($field['display_on_front']) {
            $data[1] = html_print_image('images/validate.svg', true, ['class' => 'invert_filter main_menu_icon']);
        } else {
            $data[1] = html_print_image('images/delete.svg', true, ['class' => 'invert_filter main_menu_icon']);
        }

        $custom_value = db_get_all_rows_sql(
            'select tagent_custom_data.description,tagent_custom_fields.is_password_type from tagent_custom_fields 
		INNER JOIN tagent_custom_data ON tagent_custom_fields.id_field = tagent_custom_data.id_field where tagent_custom_fields.id_field = '.$field['id_field'].' and tagent_custom_data.id_agent = '.$id_agente
        );

        if ($custom_value[0]['description'] === false || $custom_value[0]['description'] == '') {
            $custom_value[0]['description'] = '<i>-'.__('empty').'-</i>';
        } else {
            $custom_value[0]['description'] = ui_bbcode_to_html($custom_value[0]['description']);
        }

        if ($custom_value[0]['is_password_type']) {
            $data[2] = '&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;';
        } else {
            $data[2] = $custom_value[0]['description'];
        }

        array_push($table->data, $data);
    }

    html_print_table($table);
}
