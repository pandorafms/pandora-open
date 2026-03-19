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

// Check login and ACLs.
check_login();

if (!check_acl($config['id_user'], 0, 'PM') && !is_user_admin($config['id_user'])) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Categories Management'
    );
    include 'general/noaccess.php';
    return;
}

// Include functions code.
require_once $config['homedir'].'/include/functions_categories.php';

// Get parameters.
$delete = (int) get_parameter('delete_category', 0);
$search = (int) get_parameter('search_category', 0);
$category_name = (string) get_parameter('category_name', '');
$tab = (string) get_parameter('tab', 'list');

$sec = 'galertas';

$buttons = [
    'list' => [
        'active' => false,
        'text'   => '<a href="index.php?sec='.$sec.'&sec2=godmode/category/category&tab=list&pure='.(int) $config['pure'].'">'.html_print_image(
            'images/logs@svg.svg',
            true,
            [
                'title' => __('List categories'),
                'class' => 'main_menu_icon invert_filter',
            ]
        ).'</a>',
    ],
];

$buttons[$tab]['active'] = true;

ui_print_standard_header(
    __('Categories configuration'),
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
            'link'  => '',
            'label' => __('Module categories'),
        ],
    ]
);

// Two actions can performed in this page: search and delete categories
// Delete action: This will delete a category.
if ($delete != 0) {
    $return_delete = categories_delete_category($delete);
    if (!$return_delete) {
        db_pandora_audit(
            AUDIT_LOG_CATEGORY_MANAGEMENT,
            'Fail try to delete category #'.$delete
        );
        ui_print_error_message(__('Error deleting category'));
    } else {
        db_pandora_audit(
            AUDIT_LOG_CATEGORY_MANAGEMENT,
            'Delete category #'.$delete
        );
        ui_print_success_message(__('Successfully deleted category'));
    }
}

// Statements for pagination.
$url = ui_get_url_refresh();
$total_categories = categories_get_category_count();

$filter['offset'] = (int) get_parameter('offset');
$filter['limit'] = (int) $config['block_size'];
// Search action: This will filter the display category view.
$result = false;

$result = db_get_all_rows_filter(
    'tcategory',
    [
        'limit'  => $filter['limit'],
        'offset' => $filter['offset'],
    ]
);

// Display categories previously filtered or not.
$rowPair = true;
$iterator = 0;

if (empty($result) === false) {
    $table = new stdClass();
    $table->class = 'info_table';

    $table->data = [];
    $table->head = [];
    $table->align = [];
    $table->style = [];
    $table->style[0] = 'font-weight: bold; text-align:left';
    $table->style[1] = 'text-align:center; width: 100px;';
    $table->head[0] = __('Category name');
    $table->head[1] = __('Actions');

    foreach ($result as $category) {
        if ($rowPair) {
            $table->rowclass[$iterator] = 'rowPair';
        } else {
            $table->rowclass[$iterator] = 'rowOdd';
        }

        $rowPair = !$rowPair;
        $iterator++;

        $data = [];

            $data[0] = "<a href='index.php?sec=gmodules&sec2=godmode/category/edit_category&action=update&id_category=".$category['id'].'&pure='.(int) $config['pure']."'>".$category['name'].'</a>';

            $table->cellclass[][1] = 'table_action_buttons';
            $tableActionButtonsContent = [];
            $tableActionButtonsContent[] = html_print_anchor(
                [
                    'href'    => 'index.php?sec=gmodules&sec2=godmode/category/edit_category&action=update&id_category='.$category['id'].'&pure='.(int) $config['pure'],
                    'content' => html_print_image(
                        'images/edit.svg',
                        true,
                        [
                            'title' => __('Edit'),
                            'class' => 'main_menu_icon invert_filter',
                        ]
                    ),
                ],
                true
            );

            $tableActionButtonsContent[] = html_print_anchor(
                [
                    'href'    => 'index.php?sec=gmodules&sec2=godmode/category/category&delete_category='.$category['id'].'&pure='.(int) $config['pure'],
                    'onClick' => 'if (! confirm (\''.__('Are you sure?').'\')) return false',
                    'content' => html_print_image(
                        'images/delete.svg',
                        true,
                        [
                            'title' => __('Delete'),
                            'class' => 'main_menu_icon invert_filter',
                        ]
                    ),
                ],
                true
            );

            $data[1] = implode('', $tableActionButtonsContent);
        
        array_push($table->data, $data);
    }

    html_print_table($table);
    $tablePagination = ui_pagination($total_categories, $url, $offset, 0, true, 'offset', false);
} else {
    $tablePagination = '';
    // No categories available or selected.
    ui_print_info_message(['no_close' => true, 'message' => __('No categories found') ]);
}

// Form to add new categories or search categories.
$sec = 'gmodules';

echo '<form method="post" action="index.php?sec='.$sec.'&sec2=godmode/category/edit_category&action=new&pure='.(int) $config['pure'].'">';

html_print_input_hidden('create_category', '1', true);

html_print_action_buttons(
    html_print_submit_button(
        __('Create category'),
        'create_button',
        false,
        [ 'icon' => 'next' ],
        true
    ),
    [ 'right_content' => $tablePagination ]
);

echo '</form>';
