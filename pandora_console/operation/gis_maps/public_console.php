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

require_once '../../include/config.php';

// Set root on homedir, as defined in setup
chdir($config['homedir']);

ob_start();
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";
echo '<html xmlns="http://www.w3.org/1999/xhtml">'."\n";
echo '<head>';

global $vc_public_view;
$vc_public_view = true;
// This starts the page head. In the call back function,
// things from $page['head'] array will be processed into the head
ob_start('ui_process_page_head');


require_once 'include/functions_gis.php';
require_once $config['homedir'].'/include/functions_agents.php';

ui_require_javascript_file('openlayers.pandora');

$config['remote_addr'] = $_SERVER['REMOTE_ADDR'];

$hash = get_parameter('hash');
$idMap = (int) get_parameter('map_id');
$config['id_user'] = get_parameter('creator_user');

$myhash = md5($config['dbpass'].$idMap.$config['id_user']);

// Check input hash
if ($myhash != $hash) {
    exit;
}


$show_history = get_parameter('show_history', 'n');

$map = db_get_row('tgis_map', 'id_tgis_map', $idMap);
$confMap = gis_get_map_conf($idMap);

// Default open map (used to overwrite unlicensed google map view)
$confMapDefault = get_good_con();
$confMapUrlDefault = json_decode($confMapDefault['conection_data'], true);

$num_baselayer = 0;
// Initialy there is no Gmap base layer.
$gmap_layer = false;
if ($confMap !== false) {
    foreach ($confMap as $mapC) {
        $baselayers[$num_baselayer]['typeBaseLayer'] = $mapC['connection_type'];
        $baselayers[$num_baselayer]['name'] = $mapC['conection_name'];
        $baselayers[$num_baselayer]['num_zoom_levels'] = $mapC['num_zoom_levels'];
        $decodeJSON = json_decode($mapC['conection_data'], true);

        switch ($mapC['connection_type']) {
            case 'OSM':
                $baselayers[$num_baselayer]['url'] = $decodeJSON['url'];
            break;

            case 'Gmap':
                if (!isset($decodeJSON['gmap_key']) || empty($decodeJSON['gmap_key'])) {
                    // If there is not gmap_key, show the default view
                    $baselayers[$num_baselayer]['url'] = $confMapUrlDefault['url'];
                    $baselayers[$num_baselayer]['typeBaseLayer'] = 'OSM';
                } else {
                    $baselayers[$num_baselayer]['gmap_type'] = $decodeJSON['gmap_type'];
                    $baselayers[$num_baselayer]['gmap_key'] = $decodeJSON['gmap_key'];
                    $gmap_key = $decodeJSON['gmap_key'];
                    // Once a Gmap base layer is found we mark it to import the API
                    $gmap_layer = true;
                }
            break;

            case 'Static_Image':
                $baselayers[$num_baselayer]['url'] = $decodeJSON['url'];
                $baselayers[$num_baselayer]['bb_left'] = $decodeJSON['bb_left'];
                $baselayers[$num_baselayer]['bb_right'] = $decodeJSON['bb_right'];
                $baselayers[$num_baselayer]['bb_bottom'] = $decodeJSON['bb_bottom'];
                $baselayers[$num_baselayer]['bb_top'] = $decodeJSON['bb_top'];
                $baselayers[$num_baselayer]['image_width'] = $decodeJSON['image_width'];
                $baselayers[$num_baselayer]['image_height'] = $decodeJSON['image_height'];
            break;

            case 'WMS':
                $baselayers[$num_baselayer]['url'] = $decodeJSON['url'];
                $baselayers[$num_baselayer]['layers'] = $decodeJSON['layers'];
            break;
        }

        $num_baselayer++;
        if ($mapC['default_map_connection'] == 1) {
            $numZoomLevels = $mapC['num_zoom_levels'];
        }
    }
}

if ($gmap_layer === true) {
    if (https_is_running()) {
        ?>
    <script type="text/javascript" src="https://maps.google.com/maps?file=api&v=2&sensor=false&key=<?php echo $gmap_key; ?>" ></script>
        <?php
    } else {
        ?>
    <script type="text/javascript" src="http://maps.google.com/maps?file=api&v=2&sensor=false&key=<?php echo $gmap_key; ?>" ></script>
        <?php
    }
}

$controls = [
    'PanZoomBar',
    'ScaleLine',
    'Navigation',
    'MousePosition',
    'layerSwitcher',
];

$layers = gis_get_layers($idMap);

echo '<div class="gis_layers">';
echo '<h1>'.$map['map_name'].'</h1>';
echo '<br />';

echo "<div id='map' class='map_gis' ></div>";

echo '</div>';

gis_print_map(
    'map',
    $map['zoom_level'],
    $map['initial_latitude'],
    $map['initial_longitude'],
    $baselayers,
    $controls
);

if ($layers != false) {
    foreach ($layers as $layer) {
        gis_make_layer(
            $layer['layer_name'],
            $layer['view_layer'],
            null,
            $layer['id_tmap_layer'],
            1,
            $idMap
        );

        // calling agents_get_group_agents with none to obtain the names in the same case as they are in the DB.
        $agentNamesByGroup = [];
        if ($layer['tgrupo_id_grupo'] >= 0) {
            $agentNamesByGroup = agents_get_group_agents(
                $layer['tgrupo_id_grupo'],
                false,
                'none',
                true,
                true
            );
        }

        $agentNamesByLayer = gis_get_agents_layer($layer['id_tmap_layer']);

        $groupsByAgentId = gis_get_groups_layer_by_agent_id($layer['id_tmap_layer']);
        $agentNamesOfGroupItems = [];
        foreach ($groupsByAgentId as $agentId => $groupInfo) {
            $agentNamesOfGroupItems[$agentId] = $groupInfo['agent_name'];
        }

        $agentNames = array_unique($agentNamesByGroup + $agentNamesByLayer + $agentNamesOfGroupItems);

        foreach ($agentNames as $agentName) {
            $idAgent = agents_get_agent_id($agentName);
            if (!$idAgent) {
                $idAgent = agents_get_agent_id_by_alias($agentName);
                $idAgent = (!empty($idAgent)) ? $idAgent[0]['id_agente'] : 0;
            }

            $coords = gis_get_data_last_position_agent($idAgent);

            if ($coords === false) {
                $coords['stored_latitude'] = $map['default_latitude'];
                $coords['stored_longitude'] = $map['default_longitude'];
            } else {
                if ($show_history == 'y') {
                    $lastPosition = [
                        'longitude' => $coords['stored_longitude'],
                        'latitude'  => $coords['stored_latitude'],
                    ];
                    gis_add_path($layer['layer_name'], $idAgent, $lastPosition);
                }
            }

            $status = agents_get_status($idAgent);
            $icon = gis_get_agent_icon_map($idAgent, true, $status);
            $icon_size = getimagesize($icon);
            $icon_width = $icon_size[0];
            $icon_height = $icon_size[1];
            $icon = ui_get_full_url($icon);

            // Is a group item
            if (!empty($groupsByAgentId[$idAgent])) {
                $groupId = (int) $groupsByAgentId[$idAgent]['id'];
                $groupName = $groupsByAgentId[$idAgent]['name'];

                gis_add_agent_point(
                    $layer['layer_name'],
                    io_safe_output($groupName),
                    $coords['stored_latitude'],
                    $coords['stored_longitude'],
                    $icon,
                    $icon_width,
                    $icon_height,
                    $idAgent,
                    $status,
                    'point_group_info',
                    $groupId
                );
            } else {
                $parent = db_get_value('id_parent', 'tagente', 'id_agente', $idAgent);

                gis_add_agent_point(
                    $layer['layer_name'],
                    io_safe_output($agentName),
                    $coords['stored_latitude'],
                    $coords['stored_longitude'],
                    $icon,
                    $icon_width,
                    $icon_height,
                    $idAgent,
                    $status,
                    'point_agent_info',
                    $parent
                );
            }
        }
    }

    gis_add_parent_lines();

    switch ($config['dbtype']) {
        case 'mysql':
            $timestampLastOperation = db_get_value_sql('SELECT UNIX_TIMESTAMP()');
        break;

        case 'postgresql':
            $timestampLastOperation = db_get_value_sql(
                "SELECT ceil(date_part('epoch', CURRENT_TIMESTAMP))"
            );
        break;

        case 'oracle':
            $timestampLastOperation = db_get_value_sql(
                "SELECT ceil((sysdate - to_date('19700101000000','YYYYMMDDHH24MISS')) * (".SECONDS_1DAY.')) FROM dual'
            );
        break;
    }

    gis_activate_select_control();
    gis_activate_ajax_refresh($layers, $timestampLastOperation, 1, $idMap);

    // Connection lost alert.
    ui_require_css_file('register', 'include/styles/', true);
    $conn_title = __('Connection with console has been lost');
    $conn_text = __('Connection to the console has been lost. Please check your internet connection.');
    ui_require_javascript_file('connection_check');
    set_js_value('absolute_homeurl', ui_get_full_url(false, false, false, false));
    ui_print_message_dialog($conn_title, $conn_text, 'connection', '/images/fail@svg.svg');
}

// Resize GIS map on fullscreen
?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#map").css("height", $(document).height() - 100);
    });
</script>