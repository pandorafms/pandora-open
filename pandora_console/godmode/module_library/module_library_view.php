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

if (! check_acl($config['id_user'], 0, 'AR')) {
    // Doesn't have access to this page.
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Module Library View'
    );
    include 'general/noaccess.php';
    exit;
}

$buttons['categories'] = [
    'active' => false,
    'text'   => '<a href="index.php?sec=gmodule_library&sec2=godmode/module_library/module_library_view&tab=categories">'.html_print_image('images/logs@svg.svg', true, ['title' => __('Categories'), 'class' => 'main_menu_icon invert_filter']).'</a>',
];

$buttons['view'] = [
    'active' => false,
    'text'   => '<a href="index.php?sec=gmodule_library&sec2=godmode/module_library/module_library_view">'.html_print_image('images/see-details@svg.svg', true, ['title' => __('View'), 'class' => 'main_menu_icon invert_filter']).'</a>',
];


$tab = get_parameter('tab', 'view');
if ($tab !== 'search_module') {
    $buttons[$tab]['active'] = true;
}

$headerTitle = ($tab === 'categories') ? __('Categories') : __('Main view');

// Header.
ui_print_standard_header(
    $headerTitle,
    '',
    false,
    'module_library',
    true,
    $buttons,
    []
);

// Styles.
ui_require_css_file('module_library');


// Get params.
$page = get_parameter('page', '1');
$search = get_parameter('search', '');
$id_cat = get_parameter('id_cat', '');

// Show error messages.
echo '<div id="show_errors_library"></div>';

echo '<div id="module_library_main">';

$sidebar_library = '
<div class="sidebar_library">
    <h3>'.__('Search').'</h3>
        <input id="search_module" name="search_module" placeholder="Search module" type="text" class="search_input"/>
    <h3>'.__('Categories').'</h3>
    <div id="categories_sidebar"><ul></ul></div>
</div>
';

switch ($tab) {
    case 'search_module':
        echo '<div class="content_library">';
            echo '<div id="search_title_result"><h2>'.__('Search').': </h2></div>';
            echo '<div id="search_result" class="result_string-'.$search.'"></div>';
            echo '<div id="pagination_library" class="page-'.$page.'"></div>';
            echo '<div id="modal_library"></div>';
        echo '</div>';
        echo $sidebar_library;
    break;

    case 'categories':
        if ($id_cat != '') {
            echo '<div class="content_library">';
                echo '<div id="category_title_result"><h2>'.__('Category').': </h2></div>';
                echo '<div id="category_result" class="result_category-'.$id_cat.'"></div>';
                echo '<div id="pagination_library" class="page-'.$page.'"></div>';
                echo '<div id="modal_library"></div>';
            echo '</div>';
            echo $sidebar_library;
        } else {
            echo '<div id="categories_library">';
            echo '</div>';
        }
    break;

    default:
        echo '<div id="library_main">';
        echo '<span></span>';
        echo '<p></p>';
        echo '<div id="library_main_content">';
        // Show 9 categories.
        for ($i = 1; $i <= 9; $i++) {
            echo '<div class="library_main_category"></div>';
        }

        echo '</div>';
        echo '<button name="view_all" class="sub next">
              <a class="category_link"href="index.php?sec=gmodule_library&sec2=godmode/module_library/module_library_view&tab=categories">'.__('View all categories').'</a>
              </button>';
        echo '</div>';
        echo $sidebar_library;
    break;
}

echo '</div>';

?>
<script>
var more_details = '<?php echo __('More details'); ?>';
var total_modules_text = '<?php echo __('Total modules'); ?>';
var view_web = '<?php echo __('View in Module Library'); ?>';
var empty_result = '<?php echo __('No module found'); ?>';
var error_get_token = '<?php echo __('Problem with authentication. Check your internet connection'); ?>';
var invalid_user = '<?php echo __('Invalid username or password'); ?>';
var error_main = '<?php echo __('Error loading Module Library'); ?>';
var error_category = '<?php echo __('Error loading category'); ?>';
var error_categories = '<?php echo __('Error loading categories'); ?>';
var error_no_category = '<?php echo __('There is no such category'); ?>';
var error_search = '<?php echo __('Error loading results'); ?>';
var token = null;
</script>

<?php

ui_require_javascript_file('module_library');

