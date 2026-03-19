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

// Visual console required.
if (empty($visualConsole) === true) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access report builder'
    );
    include 'general/noaccess.php';
    exit;
}

$strict_user = db_get_value(
    'strict_acl',
    'tusuario',
    'id_user',
    $config['id_user']
);

// ACL for the existing visual console.
if (!isset($vconsole_write)) {
    $vconsole_write = check_acl(
        $config['id_user'],
        $visualConsole['id_group'],
        'VW'
    );
}

if (!isset($vconsole_manage)) {
    $vconsole_manage = check_acl(
        $config['id_user'],
        $visualConsole['id_group'],
        'VM'
    );
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
require_once $config['homedir'].'/include/functions_agents.php';

$table = new stdClass();
$table->id = 'wizard_table';
$table->head = [];
$table->width = '100%';
$table->class = 'databox filter-table-adv';

$table->size = [];
$table->size[0] = '50%';
$table->size[1] = '50%';

$table->data = [];

$images_list = [];
$all_images = list_files(
    $config['homedir'].'/images/console/icons/',
    'png',
    1,
    0
);
foreach ($all_images as $image_file) {
    if (strpos($image_file, '_bad')) {
        continue;
    }

    if (strpos($image_file, '_ok')) {
        continue;
    }

    if (strpos($image_file, '_warning')) {
        continue;
    }

    $image_file = substr($image_file, 0, (strlen($image_file) - 4));
    $images_list[$image_file] = $image_file;
}

$type_list = [
    STATIC_GRAPH   => __('Static Graph'),
    PERCENTILE_BAR => __('Percentile Item'),
    MODULE_GRAPH   => __('Module graph'),
    SIMPLE_VALUE   => __('Simple value'),
];

$table->rowstyle['all_0'] = 'display: none;';
$table->data['all_0'][0] = html_print_label_input_block(
    __('Type'),
    html_print_select(
        $type_list,
        'type',
        '',
        'hidden_rows()',
        '',
        '',
        true,
        false,
        false
    )
);


$table->rowstyle['staticgraph'] = 'display: none;';
$table->colspan['staticgraph'][0] = 2;
$src = $config['homeurl'].'/images/console/icons/appliance_ok.png';


$table->data['staticgraph'][0] = html_print_label_input_block(
    __('Image'),
    '<div class="flex">'.html_print_select(
        $images_list,
        'image',
        '',
        '',
        '',
        '',
        true,
        false,
        true,
        '',
        false,
        'width: 49%'
    ).'<span id="image_prev" class="mrgn_lft_10px mrgn_top-10px"><img src="'.$src.'"></span></div>'
);

$table->rowstyle['all_1'] = 'display: none;';
$table->data['all_1'][0] = html_print_label_input_block(
    __('Horizontal range between elements (px)'),
    html_print_input_text(
        'range',
        50,
        '',
        5,
        5,
        true
    )
);

$table->data['all_1'][1] = html_print_label_input_block(
    __('Vertical range between elements (px)'),
    html_print_input_text(
        'range_vertical',
        50,
        '',
        5,
        5,
        true
    )
);

$input_size = __('Width').': ';
$input_size .= html_print_input_text('width', 0, '', 5, 5, true);
$input_size .= __('Height').': ';
$input_size .= html_print_input_text('height', 0, '', 5, 5, true);

$table->rowstyle['staticgraph_modulegraph'] = 'display: none;';
$table->data['staticgraph_modulegraph'][0] = html_print_label_input_block(
    __('Size (px)'),
    '<div>'.$input_size.'</div>'
);

$fontf = [
    'Roboto'       => 'Roboto',
    'lato'         => 'Lato',
    'opensans'     => 'Open Sans',
    'nunito'       => 'Nunito',
    'leaguegothic' => 'League Gothic',
];

$fonts = [
    '4pt'   => '4pt',
    '6pt'   => '6pt',
    '8pt'   => '8pt',
    '10pt'  => '10pt',
    '12pt'  => '12pt',
    '14pt'  => '14pt',
    '18pt'  => '18pt',
    '24pt'  => '24pt',
    '28pt'  => '28pt',
    '36pt'  => '36pt',
    '48pt'  => '48pt',
    '60pt'  => '60pt',
    '72pt'  => '72pt',
    '84pt'  => '84pt',
    '96pt'  => '96pt',
    '116pt' => '116pt',
    '128pt' => '128pt',
    '140pt' => '140pt',
    '154pt' => '154pt',
    '196pt' => '196pt',
];


$table->rowstyle['all_9'] = 'display: none;';
$table->data['all_9'][0] = html_print_label_input_block(
    __('Font'),
    html_print_select(
        $fontf,
        'fontf',
        $fontf['Roboto'],
        '',
        '',
        '',
        true
    )
);

$table->rowstyle['all_9'] = 'display: none;';
$table->data['all_9'][1] = html_print_label_input_block(
    __('Font size'),
    html_print_select(
        $fonts,
        'fonts',
        $fonts['12pt'],
        '',
        '',
        '',
        true
    )
);

$table->rowstyle['modulegraph_simplevalue'] = 'display: none;';
$table->data['modulegraph_simplevalue'][0] = html_print_label_input_block(
    __('Period'),
    html_print_extended_select_for_time(
        'period',
        '',
        '',
        '',
        '',
        false,
        true
    )
);

$table->rowstyle['simplevalue'] = 'display: none;';
$table->data['simplevalue'][0] = html_print_label_input_block(
    __('Process'),
    html_print_select(
        [
            PROCESS_VALUE_MIN => __('Min value'),
            PROCESS_VALUE_MAX => __('Max value'),
            PROCESS_VALUE_AVG => __('Avg value'),
        ],
        'process_value',
        PROCESS_VALUE_AVG,
        '',
        __('None'),
        PROCESS_VALUE_NONE,
        true
    )
);

$table->rowstyle['percentileitem_1'] = 'display: none;';
$table->data['percentileitem_1'][0] = html_print_label_input_block(
    __('Width (px)'),
    html_print_input_text('percentileitem_width', 0, '', 5, 5, true)
);

$table->rowstyle['percentileitem_2'] = 'display: none;';
$table->data['percentileitem_2'][0] = html_print_label_input_block(
    __('Max value'),
    html_print_input_text('max_value', 0, '', 5, 5, true)
);

$input_percentile = '<div class="inline-radio-button">'.__('Percentile');
$input_percentile .= html_print_radio_button_extended(
    'type_percentile',
    'percentile',
    '',
    '',
    false,
    '',
    '',
    true
);

$input_percentile .= __('Bubble');
$input_percentile .= html_print_radio_button_extended(
    'type_percentile',
    'bubble',
    '',
    '',
    false,
    '',
    '',
    true
);
$input_percentile .= '</div>';

$table->rowstyle['percentileitem_3'] = 'display: none;';
$table->data['percentileitem_3'][0] = html_print_label_input_block(
    __('Type'),
    $input_percentile
);

$input_value_to_show = '<div class="inline-radio-button">'.__('Percent');
$input_value_to_show .= html_print_radio_button_extended(
    'value_show',
    'percent',
    '',
    '',
    false,
    '',
    '',
    true
);
$input_value_to_show .= __('Value');
$input_value_to_show .= html_print_radio_button_extended(
    'value_show',
    'value',
    '',
    '',
    false,
    '',
    '',
    true
);
$input_value_to_show .= '</div>';

$table->rowstyle['percentileitem_4'] = 'display: none;';
$table->data['percentileitem_4'][0] = html_print_label_input_block(
    __('Value to show'),
    $input_value_to_show
);



$table->rowstyle['all_3'] = 'display: none;';
$table->data['all_3'][0] = html_print_label_input_block(
    __('Groups'),
    html_print_select_groups(
        $config['id_user'],
        'AR',
        true,
        'groups',
        '',
        '',
        '',
        0,
        true
    )
);

$input_one_item_per_agent = '<div class="inline-radio-button">'.__('Yes');
$input_one_item_per_agent .= html_print_radio_button_extended(
    'item_per_agent',
    1,
    '',
    '',
    false,
    'item_per_agent_change(1)',
    '',
    true
).'&nbsp;&nbsp;';
$input_one_item_per_agent .= __('No');
$input_one_item_per_agent .= html_print_radio_button_extended(
    'item_per_agent',
    0,
    '',
    0,
    false,
    'item_per_agent_change(0)',
    '',
    true
);
$input_one_item_per_agent .= html_print_input_hidden(
    'item_per_agent_test',
    0,
    true
);
$input_one_item_per_agent .= '</div>';

$table->rowstyle['all_one_item_per_agent'] = 'display: none';
$table->data['all_one_item_per_agent'][0] = html_print_label_input_block(
    __('One item per agent'),
    $input_one_item_per_agent
);

$agents_list = [];

    $agents_list = agents_get_group_agents(
        0,
        false,
        'none',
        false,
        true
    );


$table->data['all_4_01'][0] = html_print_label_input_block(
    __('Filter agents'),
    html_print_input_text('filter_agents', '', '', false, 255, true)
);

$table->data['all_4_01'][1] = html_print_label_input_block(
    __('Filter modules'),
    html_print_input_text('filter_modules', '', '', false, 255, true)
);

$table->rowstyle['all_4'] = 'display: none;';
$table->data['all_4'][0] = html_print_label_input_block(
    __('Agents').ui_print_help_tip(
        __('If you select several agents, only the common modules will be displayed'),
        true
    ),
    html_print_select(
        $agents_list,
        'id_agents[]',
        0,
        false,
        '',
        '',
        true,
        true
    )
);

$table->data['all_4'][1] = html_print_label_input_block(
    __('Modules'),
    html_print_select(
        [],
        'module[]',
        0,
        false,
        __('None'),
        -1,
        true,
        true
    )
);

$label_type = [
    'agent_module' => __('Agent - Module'),
    'module'       => __('Module'),
    'agent'        => __('Agent'),
    'none'         => __('None'),
];

$table->rowstyle['all_6'] = 'display: none;';
$table->data['all_6'][0] = html_print_label_input_block(
    __('Label'),
    html_print_select(
        $label_type,
        'label_type',
        'agent_module',
        '',
        '',
        '',
        true
    )
);

$input_enable_link = '<div class="inline-radio-button">'.__('Yes');
$input_enable_link .= html_print_radio_button_extended(
    'enable_link',
    1,
    '',
    1,
    false,
    '',
    '',
    true
);
$input_enable_link .= __('No');
$input_enable_link .= html_print_radio_button_extended(
    'enable_link',
    0,
    '',
    1,
    false,
    '',
    '',
    true
);
$input_enable_link .= '</div>';

$table->data['all_6'][1] = html_print_label_input_block(
    __('Enable link agent'),
    $input_enable_link
);

$parents = visual_map_get_items_parents($visualConsole['id']);
if (empty($parents) === true) {
    $parents = [];
}

$table->data['all_8'][0] = html_print_label_input_block(
    __('Set Parent'),
    html_print_select(
        [
            VISUAL_MAP_WIZARD_PARENTS_ITEM_MAP            => __('Item created in the visualmap'),
            VISUAL_MAP_WIZARD_PARENTS_AGENT_RELANTIONSHIP => __('Use the agents relationship (from selected agents)'),
        ],
        'kind_relationship',
        0,
        '',
        __('None'),
        VISUAL_MAP_WIZARD_PARENTS_NONE,
        true,
        false,
        true,
        '',
        false,
        'max-width:50%;'
    )
);

$table->data['all_8'][1] = html_print_label_input_block(
    '<span id="parent_column_2_item_in_visual_map">'.__('Item in the map').'</span><span id="parent_column_2_relationship">'.ui_print_help_tip(
        __('The parenting relationships in %s will be drawn on the map.', get_product_name()),
        true
    ).'</span>',
    '<span id="parent_column_3_item_in_visual_map">'.html_print_select(
        $parents,
        'item_in_the_map',
        0,
        '',
        __('None'),
        0,
        true
    ).'</span>'
);

$table->data['all_10'][0] = html_print_label_input_block(
    __('Max. elements for row'),
    html_print_input_text('max_elements_row', 0, '', false, 255, true)
);

$table->data['all_10'][1] = html_print_label_input_block(
    __('Position'),
    '<div class="flex_center"><span class="mrgn_right_10px">X</span>'.html_print_input_text('pos_x', 0, '', false, 255, true, false, false, '', 'w50p').'<span class="margin-lr-10">Y</span>'.html_print_input_text('pos_y', 0, '', false, 255, true, false, false, '', 'w50p').'</div>'
);

    echo '<form method="post" class="max_floating_element_size" 
		action="index.php?sec=network&sec2=godmode/reporting/visual_console_builder&tab='.$activeTab.'&id_visual_console='.$visualConsole['id'].'"
		onsubmit="if (! confirm(\''.__('Are you sure to add many elements\nin visual map?').'\')) return false; else return check_fields();">';


html_print_table($table);

    html_print_input_hidden('action', 'update');


html_print_input_hidden('id_visual_console', $visualConsole['id']);
html_print_action_buttons(
    html_print_submit_button(
        __('Add'),
        'go',
        false,
        [ 'icon' => 'wand' ],
        true
    ),
    []
);

echo '</form>';

// Trick for it have a traduct text for javascript.
echo '<span id="any_text"     class="invisible">'.__('None').'</span>';
echo '<span id="none_text"    class="invisible">'.__('None').'</span>';
echo '<span id="loading_text" class="invisible">'.__('Loading...').'</span>';

echo '<div id="cv-preview-img" title="'.__('Image preview').'"><center><img /></center></div>';
?>
<script type="text/javascript">

var show_only_enabled_modules = true;
var url_ajax = "ajax.php";

$(document).ready (function () {
    var noneText = $("#none_text").html(); //Trick for catch the translate text.
    
    hidden_rows();
    
    $("#process_value").change(function () {
        selected = $("#process_value").val();
        
        if (selected == <?php echo PROCESS_VALUE_NONE; ?>) {
            $("tr", "#wizard_table").filter(function () {
                return /^.*modulegraph_simplevalue.*/.test(this.id);
            }).hide();
        }
        else {
            $("tr", "#wizard_table").filter(function () {
                return /^.*modulegraph_simplevalue.*/.test(this.id);
            }).show();
        }
    });
    
    $("#groups").change (function () {
        $('#module')
            .prop('disabled', true)
            .empty()
            .append($('<option></option>')
                .html(noneText)
                .attr("None", "")
                .attr('value', -1)
                .prop('selected', true));
        
        $('#id_agents')
            .prop('disabled', true)
            .empty ()
            .css ("width", "auto")
            .css ("max-width", "")
            .append ($('<option></option>').html($("#loading_text").html()));
        
        var data_params = {
            page: "include/ajax/agent",
            get_agents_group: 1,
            id_group: $("#groups").val(),
            serialized: 1,
            mode: "json"
        };
        
        jQuery.ajax ({
            data: data_params,
            type: 'POST',
            url: url_ajax,
            dataType: 'json',
            success: function (data) {
                $('#id_agents').empty();
                
                if (isEmptyObject(data)) {
                    $('#id_agents')
                        .append($('<option></option>')
                            .html(noneText)
                            .attr("None", "")
                            .attr('value', -1)
                            .prop('selected', true));
                }
                else {
                    jQuery.each (data, function (i, val) {
                        var s = js_html_entity_decode(val);
                        $('#id_agents')
                            .append($('<option></option>')
                                .html(s).attr("value", i));
                    });
                }
                
                $('#id_agents').prop('disabled', false);
            }
        });
    });
    
    $("#id_agents").change ( function() {
        if ($("#hidden-item_per_agent_test").val() == 0) {
            var options = {};
            
            agent_changed_by_multiple_agents(options);
        }
    });
    
    $("select[name='kind_relationship']").change(function() {
    
        if ($("input[name='item_per_agent']:checked").val() == "0") {
            $("select[name='kind_relationship'] option[value=<?php echo VISUAL_MAP_WIZARD_PARENTS_AGENT_RELANTIONSHIP; ?>]")
                .attr('disabled', true)
        }
        
        switch ($("select[name='kind_relationship']").val()) {
            case "<?php echo VISUAL_MAP_WIZARD_PARENTS_NONE; ?>":
                $("#parent_column_2_item_in_visual_map").hide();
                $("#parent_column_3_item_in_visual_map").hide();
                $("#parent_column_2_relationship").hide();
                break;
            case "<?php echo VISUAL_MAP_WIZARD_PARENTS_ITEM_MAP; ?>":
                $("#parent_column_2_relationship").hide();
                $("#parent_column_2_item_in_visual_map").show();
                $("#parent_column_3_item_in_visual_map").show();
                break;
            case "<?php echo VISUAL_MAP_WIZARD_PARENTS_AGENT_RELANTIONSHIP; ?>":
                $("#parent_column_2_item_in_visual_map").hide();
                $("#parent_column_3_item_in_visual_map").hide();
                $("#parent_column_2_relationship").show();
                break;
        }
    });
    //Force in the load
    $("select[name='kind_relationship']").trigger('change');
    item_per_agent_change(0);
});

function check_fields() {
    switch ($("#type").val()) {
        case "<?php echo PERCENTILE_BAR; ?>":
        case "<?php echo MODULE_GRAPH; ?>":
        case "<?php echo SIMPLE_VALUE; ?>":
            if (($("#module").val() == "-1") || ($("#module").val() == null)) {
                alert("<?php echo __('Please select any module or modules.'); ?>");
                return false;
            }
            else {
                return true;
            }
            break;
        default:
            return true;
            break;
    }
}

function hidden_rows() {
    $("tr", "#wizard_table").hide(); //Hide all in the form table
    
    //Show the id ".*-all_.*"
    $("tr", "#wizard_table")
        .filter(function () {return /^wizard_table\-all.*/.test(this.id); }).show();
    
    switch ($("#type").val()) {
        case "<?php echo STATIC_GRAPH; ?>":
            $("tr", "#wizard_table").filter(function () {return /^.*staticgraph.*/.test(this.id); }).show();
            break;
        case "<?php echo PERCENTILE_BAR; ?>":
            $("tr", "#wizard_table").filter(function () {return /^.*percentileitem.*/.test(this.id); }).show();
            break;
        case "<?php echo MODULE_GRAPH; ?>":
            $("tr", "#wizard_table").filter(function () {return /^.*modulegraph.*/.test(this.id); }).show();
            break;
        case "<?php echo SIMPLE_VALUE; ?>":
            $("tr", "#wizard_table").filter(function () {return /^.*simplevalue.*/.test(this.id); }).show();
            break;
    }
}

function item_per_agent_change(itemPerAgent) {
    
    // Disable Module select
    if (itemPerAgent == 1) {
        $("select[name='kind_relationship'] option[value=<?php echo VISUAL_MAP_WIZARD_PARENTS_AGENT_RELANTIONSHIP; ?>]")
            .attr('disabled', false);
        
        $('#module').empty();
        $('#module')
            .append($('<option></option>')
                .html (<?php echo "'".__('None')."'"; ?>)
                .attr("value", -1));
        $('#module').attr('disabled', true);
        $('#label_type').empty();
        $('#label_type')
            .append($('<option></option>')
                .html(<?php echo "'".__('Agent')."'"; ?>)
                .attr('value', 'agent').prop('selected', true));
        $('#label_type')
            .append($('<option></option>')
                .html(<?php echo "'".__('None')."'"; ?>)
                .attr('value', 'none'));
        
        $('#hidden-item_per_agent_test').val(1);
    }
    else {
        if ($("select[name='kind_relationship']").val() == <?php echo VISUAL_MAP_WIZARD_PARENTS_AGENT_RELANTIONSHIP; ?>) {
            $("select[name='kind_relationship']").val(
                <?php echo VISUAL_MAP_WIZARD_PARENTS_NONE; ?>);
        }
        $("select[name='kind_relationship'] option[value=<?php echo VISUAL_MAP_WIZARD_PARENTS_AGENT_RELANTIONSHIP; ?>]")
            .attr('disabled', true);
        
        
        $('#module').removeAttr('disabled');
        $('#hidden-item_per_agent_test').val(0);
        $('#label_type').empty();
        $('#label_type')
            .append($('<option></option>')
                .html(<?php echo "'".__('Agent')."'"; ?>)
                .attr('value', 'agent'));
        $('#label_type')
            .append($('<option></option>')
                .html(<?php echo "'".__('Agent - Module')."'"; ?>)
                .attr('value', 'agent_module')
                .prop('selected', true));
        $('#label_type')
            .append($('<option></option>')
                .html(<?php echo "'".__('Module')."'"; ?>)
                .attr('value', 'module'));
        $('#label_type')
            .append($('<option></option>')
                .html(<?php echo "'".__('None')."'"; ?>)
                .attr('value', 'none'));
    
    }
}

$('#text-filter_agents').on('keyup',function(){
    if ($(this).val() !== ''){
        findInSelect('id_agents',$(this).val());
    } else {
        $('#id_agents option').each(function(){
            $(this).removeClass('invisible');
        });
    }
});

$('#text-filter_modules').on('keyup',function(){
    if ($(this).val() !== ''){
        findInSelect('module',$(this).val());
    } else {
        $('#module option').each(function(){
            $(this).removeClass('invisible');
        });
    }
});

function findInSelect(selectid, find){
    var select = $('#'+selectid+' option');
    select.each(function(){
        var regex = new RegExp(find, "gi");
        if ($(this).html().match(regex) === null) {
            $(this).addClass('invisible');
        } else {
            $(this).removeClass('invisible');
        }
    })
}

$('#image').on('change', function() {
    var img = $(this).val();
    var src = "<?php echo $config['homeurl']; ?>"+`/images/console/icons/${img}.png`;

    $('#image_prev').html(`<img src="${src}">`);
});

$('#wizard_table span#image_prev').click(function (e) {
    e.preventDefault();
    const src = $('#wizard_table span#image_prev img').attr('src');

    $("#cv-preview-img img").attr('src', src);
    $("#cv-preview-img").dialog({
        resizable: true,
        draggable: true,
        modal: true,
        width: 'auto',
        clickOutside: true,
        overlay: {
            opacity: 0.5,
            background: "black"
        }
    });
});

</script>
<style type="text/css">
    select[name='kind_relationship'] option[disabled='disabled'] {
        color: red;
        text-decoration: line-through;
    }
</style>
