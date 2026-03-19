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

check_login();

// Include functions code.
require_once $config['homedir'].'/include/functions_categories.php';

if (! check_acl($config['id_user'], 0, 'PM') && ! is_user_admin($config['id_user'])) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Edit Category'
    );
    include 'general/noaccess.php';

    return;
}

// Get parameters
$action = (string) get_parameter('action', '');
$id_category = (int) get_parameter('id_category', 0);
$update_category = (int) get_parameter('update_category', 0);
$create_category = (int) get_parameter('create_category', 0);
$name_category = (string) get_parameter('name_category', '');
$tab = (string) get_parameter('tab', 'list');
// Main URL.
$mainUrl = 'index.php?sec=gagente&sec2=godmode/category/category';
$sec = 'gmodules';

$buttons = [
    'list' => [
        'active' => false,
        'text'   => html_print_anchor(
            [
                'href'    => 'index.php?sec='.$sec.'&sec2=godmode/category/category&tab=list&pure='.(int) $config['pure'],
                'content' => html_print_image(
                    'images/logs@svg.svg',
                    true,
                    [
                        'title' => __('List categories'),
                        'class' => 'main_menu_icon invert_filter',
                    ]
                ),
            ],
            true
        ),
    ],
];

$buttons[$tab]['active'] = false;

// Header.

    // Header.
    ui_print_standard_header(
        __('Manage category'),
        'images/gm_modules.png',
        false,
        '',
        true,
        $buttons,
        [
            [
                'link'  => '',
                'label' => __('Resources'),
            ],
            [
                'link'  => $mainUrl,
                'label' => __('Module categories'),
            ],
        ]
    );



// Two actions can performed in this page: update and create categories
// Update category: update an existing category
if ($update_category && $id_category != 0) {
    $values = [];
    $values['name'] = $name_category;

    $result = false;
    if ($values['name'] != '') {
        $result = db_process_sql_update('tcategory', $values, ['id' => $id_category]);
    }

    if ($result === false) {
        db_pandora_audit(
            AUDIT_LOG_CATEGORY_MANAGEMENT,
            'Fail try to update category #'.$id_category
        );
        ui_print_error_message(__('Error updating category'));
    } else {
        db_pandora_audit(
            AUDIT_LOG_CATEGORY_MANAGEMENT,
            'Update category #'.$id_category
        );
        ui_print_success_message(__('Successfully updated category'));
    }
}

// Create category: creates a new category.
if ($create_category) {
    $return_create = true;

    $values = [];
    $values['name'] = $name_category;

    // DB insert.
    $return_create = false;
    if ($values['name'] != '') {
        $return_create = db_process_sql_insert('tcategory', $values);
    }

    if ($return_create === false) {
        db_pandora_audit(
            AUDIT_LOG_CATEGORY_MANAGEMENT,
            'Fail try to create category'
        );
        ui_print_error_message(__('Error creating category'));
        $action = 'new';
        // If create action ends successfully then current action is update.
    } else {
        db_pandora_audit(
            AUDIT_LOG_CATEGORY_MANAGEMENT,
            'Create category #'.$return_create
        );
        ui_print_success_message(__('Successfully created category'));
        $id_category = $return_create;
        $action = 'update';
    }
}

// Form fields are filled here
// Get results when update action is performed.
if ($action === 'update' && $id_category != 0) {
    $result_category = db_get_row_filter('tcategory', ['id' => $id_category]);
    $name_category = $result_category['name'];
} //end if
else {
    $name_category = '';
}

// Create/Update category form.
echo '<form method="post" action="index.php?sec='.$sec.'&sec2=godmode/category/edit_category&action='.$action.'&id_category='.$id_category.'&pure='.(int) $config['pure'].'" enctype="multipart/form-data">';

$table = new stdClass();
$table->id = 'edit_catagory_table';
$table->class = 'databox';

$table->head = [];


$table->data = [];

$table->data[0][0] = __('Name');
$table->data[1][0] = html_print_input_text('name_category', $name_category, '', 50, 255, true);

html_print_table($table);

if ($action === 'update') {
    html_print_input_hidden('update_category', 1);
    $buttonCaption = __('Update');
    $buttonName = 'update_button';
    $buttonIcon = 'update';
} else if ($action === 'new') {
    html_print_input_hidden('create_category', 1);
    $buttonCaption = __('Create');
    $buttonName = 'create_button';
    $buttonIcon = 'next';
}

$actionButtons = [];
$actionButtons[] = html_print_submit_button(
    $buttonCaption,
    $buttonName,
    false,
    [ 'icon' => $buttonIcon ],
    true
);
$actionButtons[] = html_print_go_back_button(
    $mainUrl,
    ['button_class' => ''],
    true
);

html_print_action_buttons(
    implode('', $actionButtons),
    [ 'type' => 'form_action' ]
);

echo '</form>';
