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

global $config;

if (users_is_admin($config['id_user']) === false) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access extensions list'
    );
    include 'general/noaccess.php';
    exit;
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
            'label' => __('Defined extensions'),
        ],
    ]
);

if (count($config['extensions']) == 0) {
    $extensions = extensions_get_extension_info();
    if (empty($extensions)) {
        echo '<h3>'.__('There are no extensions defined').'</h3>';
        return;
    }
}

$delete = get_parameter('delete', '');
$enabled = get_parameter('enabled', '');
$disabled = get_parameter('disabled', '');


if ($delete != '') {
    if (!file_exists($config['homedir'].'/extensions/ext_backup')) {
        mkdir($config['homedir'].'/extensions/ext_backup');
    }

    $source = $config['homedir'].'/extensions/'.$delete;
    $endFile = $config['homedir'].'/extensions/ext_backup/'.$delete;

    rename($source, $endFile);

    ?>
    <script type="text/javascript">
    $(document).ready(function() {
            var href = location.href.replace(/delete=.*/g, "");
            location = href;
        }
    );
    </script>
    <?php
}


if ($enabled != '') {
    $endFile = $config['homedir'].'/extensions/'.$enabled;
    $source = $config['homedir'].'/extensions/disabled/'.$enabled;

    rename($source, $endFile);
    ?>
    <script type="text/javascript">
    $(document).ready(function() {
            var href = location.href.replace(/&enabled=.*/g, "");
            location = href;
        }
    );
    </script>
    <?php
}

if ($disabled != '') {
    if (!file_exists($config['homedir'].'/extensions/disabled')) {
        mkdir($config['homedir'].'/extensions/disabled');
    }

    $source = $config['homedir'].'/extensions/'.$disabled;
    $endFile = $config['homedir'].'/extensions/disabled/'.$disabled;


    rename($source, $endFile);
    ?>
    <script type="text/javascript">
    $(document).ready(function() {
            var href = location.href
            href = href.replace(/&disabled=.*/g, "");
            location = href;
        }
    );
    </script>
    <?php
}

$extensions = extensions_get_extension_info();

$table = new StdClass;
$table->width = '100%';

$table->head = [];
$table->head[] = __('File');
$table->head[] = __('Version');
$table->head[] = __('Godmode Function');
$table->head[] = __('Godmode Menu');
$table->head[] = __('Operation Menu');
$table->head[] = __('Operation Function');
$table->head[] = __('Login Function');
$table->head[] = __('Agent operation tab');
$table->head[] = __('Agent godmode tab');
$table->head[] = __('Operation');

$table->class = 'info_table';

$table->align = [];
$table->align[] = 'left';
$table->align[] = 'center';
$table->align[] = 'center';
$table->align[] = 'center';
$table->align[] = 'center';
$table->align[] = 'center';
$table->align[] = 'center';
$table->align[] = 'center';
$table->align[] = 'center';
$table->align[] = 'center';

$table->data = [];
foreach ($extensions as $file => $extension) {
    $data = [];

    $on = html_print_image('images/dot_green.png', true);
    $off = html_print_image('images/dot_red.png', true);
    if (!$extension['enabled']) {
        $on = html_print_image('images/dot_green.disabled.png', true);
        $off = html_print_image('images/dot_red.disabled.png', true);
        $data[] = '<i class="grey">'.$file.'</i>';

        // Get version of this extensions
        if (isset($config['extensions'][$file]['operation_menu']) === true) {
            $data[] = $config['extensions'][$file]['operation_menu']['version'];
        } else if (isset($config['extensions'][$file]['godmode_menu']) === true) {
            $data[] = $config['extensions'][$file]['godmode_menu']['version'];
        } else if (isset($config['extensions'][$file]['extension_ope_tab']) === true) {
            $data[] = $config['extensions'][$file]['extension_ope_tab']['version'];
        } else if (isset($config['extensions'][$file]['extension_god_tab']) === true) {
            $data[] = $config['extensions'][$file]['extension_god_tab']['version'];
        } else {
            $data[] = __('N/A');
        }
    } else {
        $data[] = $file;

        // Get version of this extension
        if ($config['extensions'][$file]['operation_menu']) {
            $data[] = $config['extensions'][$file]['operation_menu']['version'];
        } else if ($config['extensions'][$file]['godmode_menu']) {
            $data[] = $config['extensions'][$file]['godmode_menu']['version'];
        } else if (isset($config['extensions'][$file]['extension_ope_tab'])) {
            $data[] = $config['extensions'][$file]['extension_ope_tab']['version'];
        } else if (isset($config['extensions'][$file]['extension_god_tab']) === true) {
            $data[] = $config['extensions'][$file]['extension_god_tab']['version'];
        } else {
            $data[] = __('N/A');
        }
    }

    $data[] = $off;

    if ($extension['godmode_function']) {
        $data[] = $on;
    } else {
        $data[] = $off;
    }

    if ($extension['godmode_menu']) {
        $data[] = $on;
    } else {
        $data[] = $off;
    }

    if ($extension['operation_menu']) {
        $data[] = $on;
    } else {
        $data[] = $off;
    }

    if ($extension['operation_function']) {
        $data[] = $on;
    } else {
        $data[] = $off;
    }

    if ($extension['login_function']) {
        $data[] = $on;
    } else {
        $data[] = $off;
    }

    if ($extension['extension_ope_tab']) {
        $data[] = $on;
    } else {
        $data[] = $off;
    }

    if ($extension['extension_god_tab']) {
        $data[] = $on;
    } else {
        $data[] = $off;
    }

    // Avoid to delete or disabled update_manager
    if ($file != 'update_manager.php') {
        $table->cellclass[][10] = 'table_action_buttons';
        if (!$extension['enabled']) {
            $data[] = html_print_menu_button(
                [
                    'href'    => 'index.php?sec=godmode/extensions&amp;sec2=godmode/extensions&delete='.$file,
                    'image'   => 'images/cross.disabled.png',
                    'title'   => __('Delete'),
                    'onClick' => 'if (!confirm(\''.__('Are you sure?').'\')) return false;',
                ],
                true
            ).html_print_menu_button(
                [
                    'href'  => 'index.php?sec=godmode/extensions&amp;sec2=godmode/extensions&enabled='.$file,
                    'image' => 'images/lightbulb_off.png',
                    'title' => __('Enable'),
                ],
                true
            );
        } else {
            $data[] = html_print_menu_button(
                [
                    'href'    => 'index.php?sec=godmode/extensions&amp;sec2=godmode/extensions&delete='.$file,
                    'image'   => 'images/delete.svg',
                    'class'   => 'main_menu_icon invert_filter',
                    'title'   => __('Delete'),
                    'onClick' => 'if (!confirm(\''.__('Are you sure?').'\')) return false;',
                ],
                true
            ).html_print_menu_button(
                [
                    'href'  => 'index.php?sec=godmode/extensions&amp;sec2=godmode/extensions&disabled='.$file,
                    'image' => 'images/lightbulb.png',
                    'title' => __('Disable'),
                ],
                true
            );
        }
    } else {
        $data[] = '';
    }

    $table->data[] = $data;
}

html_print_table($table);

