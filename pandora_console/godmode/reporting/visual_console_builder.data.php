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

check_login();

if (empty($idVisualConsole) === true) {
    // ACL for the a new visual console.
    if (isset($vconsole_write) === false) {
        $vconsole_write = check_acl($config['id_user'], 0, 'VW');
    }

    if (isset($vconsole_manage) === false) {
        $vconsole_manage = check_acl($config['id_user'], 0, 'VM');
    }
} else {
    // ACL for the existing visual console.
    if (isset($vconsole_write) === false) {
        $vconsole_write = check_acl($config['id_user'], $idGroup, 'VW');
    }

    if (isset($vconsole_manage) === false) {
        $vconsole_manage = check_acl($config['id_user'], $idGroup, 'VM');
    }
}

if (!$vconsole_write && !$vconsole_manage) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access report builder'
    );
    include 'general/noaccess.php';
    exit;
}

require_once $config['homedir'].'/include/functions_visual_map.php';
require_once $config['homedir'].'/include/functions_users.php';

$pure = get_parameter('pure', 0);

switch ($action) {
    case 'new':

            echo "<form id='back' method='post' action='index.php?sec=network&sec2=godmode/reporting/visual_console_builder&tab=".$activeTab."' enctype='multipart/form-data'>";
            html_print_input_hidden('action', 'save');
        
    break;

    case 'update':
    case 'save':

            echo "<form id='back' method='post' action='index.php?sec=network&sec2=godmode/reporting/visual_console_builder&tab=".$activeTab.'&id_visual_console='.$idVisualConsole."' enctype='multipart/form-data'>";
            html_print_input_hidden('action', 'update');
        
    break;

    case 'edit':
    default:

            $formAction = 'index.php?sec=network&sec2=godmode/reporting/visual_console_builder&tab='.$activeTab.'&id_visual_console='.$idVisualConsole;
            $formHidden = html_print_input_hidden('action', 'update', true);
        
    break;
}


$return_all_group = false;

if (users_can_manage_group_all('RW') === true) {
    $return_all_group = true;
}

$backgrounds_list = list_files(
    $config['homedir'].'/images/console/background/',
    'jpg',
    1,
    0
);
$backgrounds_list = array_merge(
    $backgrounds_list,
    list_files($config['homedir'].'/images/console/background/', 'png', 1, 0)
);

$backgroundPreviewImages = [];
if ($action === 'new') {
    $backgroundPreviewImages[] = html_print_image('', true, ['id' => 'imagen2', 'class' => 'invisible']);
} else {

        $backgroundPreviewImages[] = html_print_image('images/console/background/'.$background, true, ['id' => 'imagen2', 'style' => 'width: 230px']);
    
}

$backgroundPreviewImages[] = html_print_image('', true, ['id' => 'imagen', 'class' => 'invisible']);

if (isset($formAction) === false) {
    $formAction = 'POST';
}

if (isset($formHidden) === false) {
    $formHidden = '';
}

// Form.
echo '<form id="back" class="max_floating_element_size" method="POST" action="'.$formAction.'" enctype="multipart/form-data">';
echo $formHidden;

$table = new stdClass();
$table->width = '100%';

$table->class = 'databox filter-table-adv';
$table->size = [];
$table->size[0] = '50%';
$table->size[1] = '50%';

$table->data = [];

$table->colspan[-1][0] = 2;
$table->data[-1][0] = '<div class="section_table_title">'.__('Create visual console').'</div>';

$table->data[0][] = html_print_label_input_block(
    __('Name'),
    html_print_input_text(
        'name',
        io_safe_output($visualConsoleName),
        '',
        80,
        100,
        true
    )
);

$table->data[0][] = html_print_label_input_block(
    __('Group'),
    html_print_input(
        [
            'type'           => 'select_groups',
            'id_user'        => $config['id_user'],
            'privilege'      => 'RW',
            'returnAllGroup' => $return_all_group,
            'name'           => 'id_group',
            'selected'       => $idGroup,
            'script'         => '',
            'nothing'        => '',
            'nothing_value'  => '',
            'return'         => true,
            'required'       => true,
        ]
    )
);

$table->data[1][0] = html_print_label_input_block(
    __('Background'),
    html_print_select(
        $backgrounds_list,
        'background',
        io_safe_output($background),
        '',
        'None',
        'None.png',
        true
    )
);

$table->rowspan[1][1] = 2;
$table->data[1][1] = html_print_label_input_block(
    __('Background preview'),
    implode('', $backgroundPreviewImages)
);

$table->data[2][] = html_print_label_input_block(
    __('Background image'),
    html_print_input_file(
        'background_image',
        true
    )
);

if ($action === 'new') {
    $backgroundColorInput = html_print_input_color(
        'background_color',
        '#FFFFFF',
        'background_color',
        false,
        true
    );
} else {
    $backgroundColorInput = html_print_input_color(
        'background_color',
        $background_color,
        'background_color',
        false,
        true
    );
}

$table->data[3][] = html_print_label_input_block(
    __('Background color'),
    $backgroundColorInput
);

if ($idVisualConsole) {
    $preimageh = db_get_value_sql('select height from tlayout where id ='.$idVisualConsole);
    $preimagew = db_get_value_sql('select width from tlayout where id ='.$idVisualConsole);
} else {
    $preimageh = 768;
    $preimagew = 1024;
}

$layoutSizeElements = [];

$layoutSizeElements[] = html_print_div(
    [
        'class'   => 'preimage_container',
        'content' => '<span id="preimagew">'.$preimagew.'</span><span>x</span><span id="preimageh">'.$preimageh.'</span>',
    ],
    true
);

$layoutSizeElements[] = html_print_button(
    __('Set custom size'),
    'modsize',
    false,
    '',
    [
        'value' => 'modsize',
        'class' => 'buttonButton mini',
    ],
    true
);

$layoutSizeElements[] = '<span class="opt" style="visibility:hidden;">'.html_print_input_text('width', $preimagew, '', 8, 10, true, false).' x '.html_print_input_text('height', $preimageh, '', 8, 10, true, false).'</span>';
$layoutSizeElements[] = '<span class="opt" style="visibility:hidden;">';
$layoutSizeElements[] = html_print_button(
    __('Get default image size'),
    'getsize',
    false,
    '',
    [
        'value' => 'modsize',
        'class' => 'buttonButton mini',
    ],
    true
);
$layoutSizeElements[] = '</span>';

$table->data[4][] = html_print_label_input_block(
    __('Layout size'),
    html_print_div(
        [
            'class'   => 'flex flex-space-around w600px',
            'content' => implode('', $layoutSizeElements),
        ],
        true
    )
);

$table->data[5][] = html_print_label_input_block(
    __('Favourite visual console'),
    html_print_checkbox_switch(
        'is_favourite',
        0,
        $is_favourite,
        true
    )
);

$table->data[6][] = html_print_label_input_block(
    __('Auto adjust to screen in fullscreen'),
    html_print_checkbox_switch(
        'auto_adjust',
        0,
        $auto_adjust,
        true
    )
);

if ($action === 'new') {
    $textButtonSubmit = __('Save');
    $classButtonSubmit = 'wand';
} else {
    $textButtonSubmit = __('Update');
    $classButtonSubmit = 'update';
}

html_print_table($table);

html_print_action_buttons(
    html_print_submit_button(
        $textButtonSubmit,
        'update_layout',
        false,
        [ 'icon' => $classButtonSubmit ],
        true
    )
);

echo '</form>';
ui_require_css_file('color-picker', 'include/styles/js/');
ui_require_jquery_file('colorpicker');
?>

<script type="text/javascript">

$(document).ready (function () {
    $("#button-modsize").click(function(event){
        event.preventDefault();

        if($('.opt').css('visibility') == 'hidden'){
            $('.opt').css('visibility','visible');
        }

        if ($('#imagen').attr('src') != '') {
            if (parseInt($('#imagen').width()) < 1024){
                alert('Default width is '+$('#imagen').width()+'px, smaller than minimum -> 1024px');
                $('input[name=width]').val('1024');
                $('#preimagew').html(1024);
            }
            else{
                $('input[name=width]').val($('#imagen').width());
                $('#preimagew').html($('#imagen').width());
            }
            if (parseInt($('#imagen').height()) < 768){
                alert('Default height is '+$('#imagen').height()+'px, smaller than minimum -> 768px');
                $('input[name=height]').val('768');
                $('#preimageh').html(768);
            }
            else{
                $('input[name=height]').val($('#imagen').height());
                $('#preimageh').html($('#imagen').height());
            }

        }
    });

    $("#button-getsize").click(function(event){
        event.preventDefault();
        if ($('#imagen').attr('src') != '') {
            if (parseInt($('#imagen').width()) < 1024){
                alert('Default width is '+$('#imagen').width()+'px, smaller than minimum -> 1024px');
                $('input[name=width]').val('1024');
                $('#preimagew').html(1024);
            } else{
                $('input[name=width]').val($('#imagen').width());
                $('#preimagew').html($('#imagen').width());
            }

            if (parseInt($('#imagen').height()) < 768){
                alert('Default height is '+$('#imagen').height()+'px, smaller than minimum -> 768px');
                $('input[name=height]').val('768');
                $('#preimageh').html(768);
            } else{
                $('input[name=height]').val($('#imagen').height());
                $('#preimageh').html($('#imagen').height());
            }
        } else {
            original_image=new Image();
            original_image.src= 'images/console/background/'+$('#background').val();
            if (parseInt(original_image.width) < 1024){
                alert('Default width is '+original_image.width+'px, smaller than minimum -> 1024px');
                $('input[name=width]').val('1024');
                $('#preimagew').html(1024);
            } else {
                $('input[name=width]').val(original_image.height);
                $('#preimagew').html(original_image.height);
            }
            if (parseInt(original_image.height) < 768){
                alert('Default height is '+original_image.height+'px, smaller than minimum -> 768px');
                $('input[name=height]').val('768');
                $('#preimageh').html(768);
            } else {
                $('input[name=height]').val(original_image.height);
                $('#preimageh').html(original_image.height);
            }
        }
    });
    
    $( "button[type=submit]" ).click(function( event ) {
        if (parseInt($('input[name=width]').val()) < 1024){
            alert('Default width is '+$('input[name=width]').val()+'px, smaller than minimum -> 1024px');
            $('input[name=width]').val('1024');
            $('#preimagew').html('1024');
            var x = 1;
        }
            
        if (parseInt($('input[name=height]').val()) < 768){
            alert('Default height is '+$('input[name=height]').val()+'px, smaller than minimum -> 768px');
            $('input[name=height]').val('768');
            $('#preimageh').html('768');
            var y = 1;
        }
            
        if (x || y){
            return false;
        }   
    });
    
    //Preload image size and activate auto image size changer when user click over a image in the selector
    
    var size_changer_state = false;

    $("#background").change(function() {
        $('#imagen2').attr('src', 'images/console/background/'+$('#background').val());
        
        $('#imagen2').width(230);
        $('#imagen2').show();        
    });
    
    $("#background").click(function(){
        if('<?php echo get_parameter('action') == 'edit'; ?>' == false){
            size_changer_state = true;
            }
    });
    
    $("#background").mouseout(function() {
        if(size_changer_state){
            $('#imagen').attr('src','images/console/background/'+$('#background').val());
            $('input[name=width]').val($('#imagen').width());
            $('input[name=height]').val($('#imagen').height());
            $('#preimagew').html($('#imagen').width());
            $('#preimageh').html($('#imagen').height());
            size_changer_state = false;
        }        
    });

    $("#file-background_image").change(function(){
        readURL(this);
    });
    
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#imagen').attr('src', e.target.result);
                $('input[name=width]').val($('#imagen').width());
                $('input[name=height]').val($('#imagen').height());
                $('#preimagew').html($('#imagen').width());
                $('#preimageh').html($('#imagen').height());
                $('#imagen2').attr('src', e.target.result);
                $('#imagen2').width(230);
                $('#imagen2').show();
            }
            reader.readAsDataURL(input.files[0]);
        }
        
    }

    $("#imgInp").change(function(){
        readURL(this);
    });
        
    //$("#text-background_color").attachColorPicker();

    if($("#checkbox-is_favourite").is(":checked")) {
        $("#hidden-is_favourite_sent").val(1);
    }
    else{
        $("#hidden-is_favourite_sent").val(0);
    }

    $("#checkbox-is_favourite").change(function(){
        if($(this).is(":checked")) {
            $("#hidden-is_favourite_sent").val(1);
        }
        else{
            $("#hidden-is_favourite_sent").val(0);
        }
    });

    if($("#checkbox-auto_adjust").is(":checked")) {
        $("#hidden-auto_adjust_sent").val(1);
    }
    else{
        $("#hidden-auto_adjust_sent").val(0);
    }

    $("#checkbox-auto_adjust").change(function(){
        if($(this).is(":checked")) {
            $("#hidden-auto_adjust_sent").val(1);
        }
        else{
            $("#hidden-auto_adjust_sent").val(0);
        }
    });
});

</script>
