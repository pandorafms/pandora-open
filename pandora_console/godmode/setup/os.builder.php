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

if (! check_acl($config['id_user'], 0, 'PM') && ! is_user_admin($config['id_user'])) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Setup Management'
    );
    include 'general/noaccess.php';
    return;
}

if ($idOS > 0) {
    $os = db_get_row_filter('tconfig_os', ['id_os' => $idOS]);
    $name = $os['name'];
    $description = $os['description'];
    $icon = $os['icon_name'];
} else {
    $name = io_safe_input(strip_tags(trim(io_safe_output((string) get_parameter('name')))));
    $description = io_safe_input(strip_tags(io_safe_output((string) get_parameter('description'))));
    $icon = get_parameter('icon', 'os@svg.svg');
}

$icon_upload = get_parameter('icon_upload', null);

$message = '';

switch ($action) {
    case 'edit':
        if ($idOS > 0) {
            $actionHidden = 'update';
            $textButton = __('Update');
            $classButton = ['icon' => 'wand'];
        } else {
            $actionHidden = 'save';
            $textButton = __('Create');
            $classButton = ['icon' => 'next'];
        }
    break;

    case 'save':
        if ($icon_upload !== null && $icon_upload['name'] !== '') {
            if (isset($_FILES['icon_upload']) === true) {
                $file_name = $_FILES['icon_upload']['name'];
                $file_tmp = $_FILES['icon_upload']['tmp_name'];
                $file_type = $_FILES['icon_upload']['type'];
                $file_ext = strtolower(end(explode('.', $_FILES['icon_upload']['name'])));

                $allowed_extensions = [
                    'jpeg',
                    'jpg',
                    'png',
                    'svg',
                ];

                $tab = 'manage_os';

                if (in_array($file_ext, $allowed_extensions) === false) {
                    $message = 9;
                } else if (exif_imagetype($file_tmp) === false && $file_ext !== 'svg') {
                    $message = 10;
                } else {
                    $message = 8;

                    $file_uploaded = move_uploaded_file($file_tmp, $config['homedir'].'/images/os_icons/'.$file_name);

                    if ($file_uploaded !== true) {
                        $message = 10;
                    }
                }
            }
        } else {
            $values = [];
            $values['name'] = $name;
            $values['description'] = $description;

            if (($icon !== 0) && ($icon != '')) {
                $values['icon_name'] = $icon;
            }

            $resultOrId = false;
            if ($name != '') {
                $resultOrId = db_process_sql_insert('tconfig_os', $values);
            }

            if ($resultOrId === false) {
                $message = 2;
                $tab = 'manage_os';
                $actionHidden = 'save';
                $textButton = __('Create');
                $classButton = ['icon' => 'wand'];
            } else {
                $tab = 'manage_os';
                $message = 1;
            }
        }

            header('Location:'.$config['homeurl'].'index.php?sec=gsetup&sec2=godmode/setup/os&tab='.$tab.'&message='.$message);
        
    break;

    case 'update':
        if ($icon_upload !== null && $icon_upload['name'] !== '') {
            if (isset($_FILES['icon_upload']) === true) {
                $file_name = $_FILES['icon_upload']['name'];
                $file_tmp = $_FILES['icon_upload']['tmp_name'];
                $file_type = $_FILES['icon_upload']['type'];
                $file_ext = strtolower(end(explode('.', $_FILES['icon_upload']['name'])));

                $allowed_extensions = [
                    'jpeg',
                    'jpg',
                    'png',
                    'svg',
                ];

                $tab = 'manage_os';

                if (in_array($file_ext, $allowed_extensions) === false) {
                    $message = 9;
                } else if (exif_imagetype($file_tmp) === false) {
                    $message = 10;
                } else {
                    $message = 8;
                    $file_uploaded = move_uploaded_file($file_tmp, $config['homedir'].'/images/os_icons/'.$file_name);

                    if ($file_uploaded !== true) {
                        $message = 10;
                    }
                }
            }
        } else {
            $name = io_safe_input(strip_tags(trim(io_safe_output((string) get_parameter('name')))));
            $description = io_safe_input(strip_tags(io_safe_output((string) get_parameter('description'))));
            $icon = get_parameter('icon', 0);

            $values = [];
            $values['name'] = $name;
            $values['description'] = $description;
            

            if (($icon !== 0) && ($icon != '')) {
                $values['icon_name'] = $icon;
            }

            $result = false;
            if ($name != '') {
                $result = db_process_sql_update('tconfig_os', $values, ['id_os' => $idOS]);
            }

            if ($result !== false) {
                $message = 3;
                $tab = 'manage_os';
            } else {
                $message = 4;
                $tab = 'builder';
                $os = db_get_row_filter('tconfig_os', ['id_os' => $idOS]);
                $name = $os['name'];
            }

            $actionHidden = 'update';
            $textButton = __('Update');
            $classButton = ['icon' => 'wand'];
        }

            header('Location:'.$config['homeurl'].'index.php?sec=gsetup&sec2=godmode/setup/os&tab='.$tab.'&message='.$message);
        
    break;

    case 'delete':
        $sql = 'SELECT COUNT(id_os) AS count FROM tagente WHERE id_os = '.$idOS;
        $count = db_get_all_rows_sql($sql);
        $count = $count[0]['count'];

        if ($count > 0) {
            $message = 5;
        } else {
            $result = (bool) db_process_sql_delete('tconfig_os', ['id_os' => $idOS]);
            if ($result) {
                $message = 6;
            } else {
                $message = 7;
            }
        }

            header('Location:'.$config['homeurl'].'index.php?sec=gsetup&sec2=godmode/setup/os&tab='.$tab.'&message='.$message);
        
    break;

    default:
    case 'new':
        $actionHidden = 'save';
        $textButton = __('Create');
        $classButton = ['icon' => 'next'];
    break;
}

$icons = get_list_os_icons_dir();

$iconData = [];
$iconData[] = html_print_select(
    $icons,
    'icon',
    $icon,
    'show_icon_OS();',
    '',
    0,
    true
);
$iconData[] = html_print_div(
    [
        'id'      => 'icon_image',
        'class'   => 'invert_filter main_menu_icon',
        'style'   => 'margin-left: 10px',
        'content' => ui_print_os_icon($idOS, false, true),
    ],
    true
);

echo '<form id="form_setup" method="post" enctype="multipart/form-data">';
$table = new stdClass();
$table->width = '100%';
$table->class = 'databox filter-table-adv';

$table->data[0][] = html_print_label_input_block(
    __('Name'),
    html_print_input_text('name', $name, __('Name'), 20, 30, true, false, true, '', 'w250px')
);

$table->data[0][] = html_print_label_input_block(
    __('Icon'),
    html_print_div(
        [
            'class'   => 'flex-row-center',
            'content' => implode('', $iconData),
        ],
        true
    )
);

$table->data[1][] = html_print_label_input_block(
    __('Description'),
    html_print_textarea('description', 5, 20, $description, '', true, 'w250px')
);

$table->data[1][] = html_print_label_input_block(
    '',
    html_print_input_file('icon_upload', true, ['caption' => __('Upload icon')], 'form_setup')
);

html_print_table($table);

html_print_input_hidden('id_os', $idOS);
html_print_input_hidden('action', $actionHidden);

html_print_action_buttons(
    html_print_submit_button($textButton, 'update_button', false, $classButton, true),
    ['type' => 'form_action']
);

echo '</form>';

$id_message = get_parameter('id_message', 0);

if ($id_message !== 0) {
    switch ($id_message) {
        case 8:
            echo ui_print_success_message(__('Icon successfuly uploaded'), '', true);
        break;

        case 9:
            echo ui_print_error_message(__('File must be of type JPG, JPEG, PNG or SVG'), '', true);
        break;

        case 10:
            echo ui_print_error_message(__('An error ocurrered to upload icon'), '', true);
        break;

        default:
            // Nothing to do.
        break;
    }
}


function get_list_os_icons_dir()
{
    global $config;

    $return = [];

    $items = scandir($config['homedir'].'/images/');

    foreach ($items as $item) {
        if (strstr($item, '@os.svg')) {
            $return[$item] = $item;
        }
    }

    $items2 = scandir($config['homedir'].'/images/os_icons');

    foreach ($items2 as $item2) {
        if (strstr($item2, '_small.png') || strstr($item2, '_small.gif')
            || strstr($item2, '_small.jpg')
        ) {
            continue;
        }

        if (strstr($item2, '.png') || strstr($item2, '.gif')
            || strstr($item2, '.jpg')
        ) {
            $return[$item2] = $item2;
        }
    }

    $return['os@svg.svg'] = __('None');

    return $return;
}


?>
<script type="text/javascript">

function show_icon_OS() {
    var extension = $("#icon").val().split('.').pop();

    var params = [];
    params.push("get_image_path=1");
    if (extension !== 'svg') {
        params.push('img_src=images/os_icons/' + $("#icon").val());
    } else {
        params.push('img_src=images/' + $("#icon").val());
    }
    params.push("page=include/ajax/skins.ajax");
    jQuery.ajax ({
        data: params.join ("&"),
        type: 'POST',
        url: action="ajax.php",
        async: false,
        timeout: 10000,
        success: function (data) {
            $("#icon_image").html(data);
        }
    });
}
</script>