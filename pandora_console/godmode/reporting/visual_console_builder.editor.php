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

// Login check.
check_login();

// Visual console required.
if (empty($visualConsole) === true) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access report builder'
    );
    include 'general/noaccess.php';
    exit;
}

ui_require_css_file('visual_maps');

// ACL for the existing visual console.
if (isset($vconsole_write) === false) {
    $vconsole_write = check_acl($config['id_user'], $visualConsole['id_group'], 'VW');
}

if (isset($vconsole_manage) === false) {
    $vconsole_manage = check_acl($config['id_user'], $visualConsole['id_group'], 'VM');
}

if ((bool) $vconsole_write === false && (bool) $vconsole_manage === false) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access report builder'
    );
    include 'general/noaccess.php';
    exit;
}



require_once $config['homedir'].'/include/functions_visual_map.php';
require_once $config['homedir'].'/include/functions_visual_map_editor.php';
visual_map_editor_print_toolbox();

$background = $visualConsole['background'];
$widthBackground = $visualConsole['width'];
$heightBackground = $visualConsole['height'];

$layoutDatas = db_get_all_rows_field_filter(
    'tlayout_data',
    'id_layout',
    $idVisualConsole
);
if ($layoutDatas === false) {
    $layoutDatas = [];
}

visual_map_editor_print_hack_translate_strings();
visual_map_editor_print_item_palette($visualConsole['id'], $background);

    echo '<div id="frame_view" class="frame_view_node">';


echo '<div id="background" class="" style="top:0px;
margin: 0px auto;border: 1px lightgray solid; width: '.$widthBackground.'px; height: '.$heightBackground.'px;background-color: '.$visualConsole['background_color'].';z-index:0;">';
echo "<div id='background_grid'
	style='position: absolute; display: none; overflow: hidden;
	background: url(".ui_get_full_url('images/console/background/white_boxed.jpg', false, false, false).');
	background-repeat: repeat; width: '.$widthBackground.'px; height: '.$heightBackground."px;'></div>";


// Print the layout datas from the DB.
foreach ($layoutDatas as $layoutData) {
    $layoutData['status_calculated'] = visual_map_get_status_element($layoutData);

    // Pending delete and disable modules must be ignored.
    $delete_pending_module = db_get_value(
        'delete_pending',
        'tagente_modulo',
        'id_agente_modulo',
        $layoutData['id_agente_modulo']
    );
    $disabled_module = db_get_value(
        'disabled',
        'tagente_modulo',
        'id_agente_modulo',
        $layoutData['id_agente_modulo']
    );

    if ($delete_pending_module == 1 || $disabled_module == 1) {
        continue;
    }

    switch ($layoutData['type']) {
        case NETWORK_LINK:
        case LINE_ITEM:
            visual_map_print_user_line_handles($layoutData);
            visual_map_print_user_lines($layoutData);
        break;

        default:
            visual_map_print_item(
                'write',
                $layoutData,
                null,
                true,
                false,
                false
            );
        break;
    }




    html_print_input_hidden(
        'status_'.$layoutData['id'],
        $layoutData['status_calculated']
    );
}

echo "<img class='vc_bg_image' id='background_img' src='".''.'images/console/background/'.$background."' width='100%' height='100%' />";

echo '</div>';
echo '</div>';

html_print_input_hidden('background_width', $widthBackground);
html_print_input_hidden('background_height', $heightBackground);

$backgroundSizes = getimagesize(
    $config['homedir'].'/images/console/background/'.$background
);
html_print_input_hidden('background_original_width', $backgroundSizes[0]);
html_print_input_hidden('background_original_height', $backgroundSizes[1]);
html_print_input_hidden('id_visual_console', $visualConsole['id']);
html_print_input_hidden('message_size', __('Min allowed size is 1024x768'));


// Loading dialog.
echo "<div id='loading_in_progress_dialog' class='invisible center' title='".__('Action in progress')."'>".__('Loading in progress').'<br />'.html_print_image('images/spinner.gif', true).'</div>';

echo "<div id='saving_in_progress_dialog' class='invisible center' title='".__('Action in progress')."'>".__('Saving in progress').'<br />'.html_print_image('images/spinner.gif', true).'</div>';

echo "<div id='delete_in_progress_dialog' class='invisible center' title='".__('Action in progress')."'>".__('Deletion in progress').'<br />'.html_print_image('images/spinner.gif', true).'</div>';

// CSS.
ui_require_css_file('color-picker', 'include/styles/js/');
ui_require_css_file('jquery-ui.min', 'include/styles/js/');
ui_require_jquery_file('jquery-ui_custom');

// Javascript.
ui_require_jquery_file('colorpicker');
ui_require_javascript_file('wz_jsgraphics');
ui_require_javascript_file('pandora_visual_console');
ui_require_javascript_file('visual_console_builder.editor', 'godmode/reporting/');
ui_require_javascript_file('tinymce', 'vendor/tinymce/tinymce/');

// Javascript file for base 64 encoding of label parameter.
ui_require_javascript_file('encode_decode_base64');
?>
<style type="text/css">
.ui-resizable-handle {
    background: transparent !important;
    border: transparent !important;
}
</style>
<script type="text/javascript">
    id_visual_console = <?php echo $visualConsole['id']; ?>;
    visual_map_main();
    defineTinyMCE('#text-label');

    $('.item img').each(function(){

        if($(this).css('float')=='left' || $(this).css('float')=='right'){

        $(this).css('margin-top',(parseInt($(this).parent().css('height'))/2-parseInt($(this).css('height'))/2)+'px');
        $(this).css('margin-left','');
        }
        else{
            $(this).css('margin-left',(parseInt($(this).parent().css('width'))/2-parseInt($(this).css('width'))/2)+'px');
            $(this).css('margin-top','');
        }

    });

    $('#process_value').change(function(){
        if($(this).val() == 0){
            $('#period_row').css('display','none');
        }
        else{
            $('#period_row').css('display','');
        }
    });

</script>
