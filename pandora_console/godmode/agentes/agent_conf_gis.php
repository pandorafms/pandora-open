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

if (!isset($id_agente)) {
    die('Not Authorized');
}

require_once 'include/functions_gis.php';
require_once 'include/functions_html.php';
require_once 'include/functions_agents.php';

ui_require_javascript_file('openlayers.pandora');

echo "<div class='margin-bottom-10'></div>";

$agentData = gis_get_data_last_position_agent($id_agente);
$updateGisData = db_get_value('update_gis_data', 'tagente', 'id_agente', $id_agente);
$agent_name = agents_get_name($id_agente);

// Avoid the agents with characters that fails the div.
$agent_name = md5($agent_name);

// Map with the current position
echo '<div id="'.$agent_name.'_agent_map" class="agent_map"></div>';

if (!gis_get_agent_map($id_agente, '500px', '100%', false)) {
    ui_print_error_message(__('There is no default map. Please go to the setup for to set a default map.'));
    echo "<script type='text/javascript'>
		$(document).ready(function() {
			$('#".$agent_name."_agent_map').hide();
		});
		</script>";
}

if ($agentData === false) {
    ui_print_info_message(
        [
            'no_close' => true,
            'message'  => __("There is no GIS data for this agent, so it's positioned in default position of map."),
        ]
    );
}

ui_print_warning_message(
    [
        'no_close' => true,
        'message'  => __("When you change the Agent position, the agent automatically activates the 'Ignore new GIS data' option"),
    ]
);

$table = new stdClass();
$table->width = '100%';
$table->class = 'databox filter-table-adv mrgn_top_15px pdd_t_0px_important';
$table->data = [];
$table->cellpadding = 0;
$table->cellspacing = 0;
$table->head[0] = __('Agent position');
$table->head_colspan[0] = 4;
$table->headstyle[0] = 'text-align:center';
$table->size[0] = '50%';
$table->size[2] = '50%';

$table->data[1][0] = html_print_label_input_block(
    __('Latitude: '),
    html_print_input_text_extended(
        'latitude',
        $agentData['stored_latitude'],
        'text-latitude',
        '',
        20,
        20,
        false,
        '',
        [
            'onchange' => 'setIgnoreGISDataEnabled()',
            'onkeyup'  => 'setIgnoreGISDataEnabled()',
        ],
        true
    )
);

$table->data[1][1] = html_print_label_input_block(
    __('Longitude: '),
    html_print_input_text_extended(
        'longitude',
        $agentData['stored_longitude'],
        'text-longitude',
        '',
        20,
        20,
        false,
        '',
        [
            'onchange' => 'setIgnoreGISDataEnabled()',
            'onkeyup'  => 'setIgnoreGISDataEnabled()',
        ],
        true
    )
);

$table->data[2][0] = html_print_label_input_block(
    __('Altitude: '),
    html_print_input_text_extended(
        'altitude',
        $agentData['stored_altitude'],
        'text-altitude',
        '',
        10,
        10,
        false,
        '',
        [
            'onchange' => 'setIgnoreGISDataEnabled()',
            'onkeyup'  => 'setIgnoreGISDataEnabled()',
        ],
        true
    )
);

$table->data[2][1] = html_print_label_input_block(
    __('Ignore new GIS data').': ',
    '<div class="flex mrgn_top_5px">'.__('Yes').' '.html_print_radio_button_extended(
        'update_gis_data',
        0,
        '',
        $updateGisData,
        false,
        '',
        'class="mrgn_right_40px"',
        true
    ).__('No').' '.html_print_radio_button_extended(
        'update_gis_data',
        1,
        '',
        $updateGisData,
        false,
        '',
        'class="mrgn_right_40px"',
        true
    ).'</div>'
);

$url = 'index.php?sec=gagente&sec2=godmode/agentes/configurar_agente&tab=gis&id_agente='.$id_agente;
echo "<form method='post' action='".$url."' onsubmit ='return validateFormFields();' class='max_floating_element_size'>";
html_print_input_hidden('update_gis', 1);
html_print_table($table);

html_print_action_buttons(
    html_print_submit_button(
        __('Update'),
        '',
        false,
        ['icon' => 'wand'],
        true
    )
);
echo '</form>';
?>
<script type="text/javascript">
function setIgnoreGISDataEnabled() {
    $("#radiobtn0002").removeAttr("checked");
    $("#radiobtn0001").attr("checked","checked");
}

function validateFormFields() {
    longitude = $('input[name=longitude]').val();
    latitude = $('input[name=latitude]').val();
    altitude = $('input[name=altitude]').val();
    valid = true;
    
    $('input[name=longitude]').css('background', '#ffffff');
    $('input[name=latitude]').css('background', '#ffffff');
    $('input[name=altitude]').css('background', '#ffffff');
    
    //Validate longitude
    if ((jQuery.trim(longitude).length == 0) ||
        isNaN(parseFloat(longitude))) {
        $('input[name=longitude]').css('background', '#cc0000');
        
        valid = false;
    }
    
    //Validate latitude
    if ((jQuery.trim(latitude).length == 0) ||
        isNaN(parseFloat(latitude))) {
        $('input[name=latitude]').css('background', '#cc0000');
        
        valid = false;
    }
    
    //Validate altitude
    if ((jQuery.trim(altitude).length == 0) ||
        isNaN(parseFloat(altitude))) {
            $('input[name=altitude]').val(1);
    }
    
    if (valid) return true;
    else return false;
}

$(document).ready (
    function () { 
        function changePositionAgent(e) {
            var lonlat = map.getLonLatFromViewPortPx(e.xy);
            var layer = map.getLayersByName("layer_for_agent_<?php echo $agent_name; ?>");
            
            layer = layer[0];
            feature = layer.features[0];
            
            lonlat.transform(map.getProjectionObject(), map.displayProjection); //transform the lonlat in object proyection to "standar proyection"
            
            $('input[name=latitude]').val(lonlat.lat);
            $('input[name=longitude]').val(lonlat.lon);
            
            if ($('input[name=altitude]').val().length == 0)
                $('input[name=altitude]').val(0)
            
            setIgnoreGISDataEnabled();
            
            //return to no-standar the proyection for to move
            feature.move(lonlat.transform(map.displayProjection, map.getProjectionObject()));
        }
        
        js_activateEvents(changePositionAgent);
    });
</script>
