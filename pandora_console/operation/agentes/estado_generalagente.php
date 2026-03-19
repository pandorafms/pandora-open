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

require_once 'include/functions_agents.php';
require_once $config['homedir'].'/include/functions_graph.php';
require_once $config['homedir'].'/include/functions_groups.php';
require_once $config['homedir'].'/include/functions_ui.php';
require_once $config['homedir'].'/include/functions_reporting_html.php';

require_once $config['homedir'].'/include/functions_clippy.php';

check_login();

$strict_user = (bool) db_get_value(
    'strict_acl',
    'tusuario',
    'id_user',
    $config['id_user']
);

$id_agente = get_parameter_get('id_agente', -1);

$agent = db_get_row('tagente', 'id_agente', $id_agente);

if (empty($agent['server_name']) === true) {
    ui_print_error_message(
        __('The agent has not assigned server. Maybe agent does not run fine.')
    );
}

if ($agent === false) {
    ui_print_error_message(__('There was a problem loading agent'));
    return;
}

if (check_acl_one_of_groups($config['id_user'], $all_groups, 'AR') === false
    && check_acl_one_of_groups($config['id_user'], $all_groups, 'AW') === false
) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Agent General Information'
    );
    include_once 'general/noaccess.php';
    return;
}

$alive_animation = agents_get_starmap($id_agente, 200, 50);

/*
 * START: TABLE AGENT BUILD.
 */

$agentStatusHeader = get_resume_agent_status_header($agent);

// Fixed width non interactive charts.
$agentStatusGraph = get_status_agent_chart_pie($id_agente, 150);

$agentCountModules = html_print_div(
    [
        'class'   => 'agent_details_bullets',
        'content' => reporting_tiny_stats(
            $agent,
            true,
            'agent',
            // Useless.
            ':',
            true
        ),
    ],
    true
);

$table_status = new stdClass();
$table_status->id = 'agent_status_main';
$table_status->width = '90%';
$table_status->height = 'auto';
$table_status->cellspacing = 0;
$table_status->cellpadding = 0;
$table_status->class = 'floating_form';
$table_status->style[0] = 'height: 28px; width: 30%; padding-right: 5px; text-align: end; vertical-align: top';
$table_status->style[1] = 'height: 28px; width: 70%; padding-left: 5px; font-weight: lighter; vertical-align: top';

$os_agent_text = '';
$os_name = get_os_name((int) $agent['id_os']);
if (empty($agent['os_version']) !== true) {
    $agent['os_version'] = io_safe_output($agent['os_version']);
    if (strpos($agent['os_version'], '(') !== false) {
        $os_name = preg_split('/[0-9]|[\(]/', $agent['os_version'])[0];
        if (strlen($os_name) === 0) {
            $os_name = get_os_name((int) $agent['id_os']);
            $os_agent_text = $agent['os_version'];
        } else {
            $os_version = explode($os_name, explode('(', $agent['os_version'])[0])[1];
            $os_version_name = preg_split('/[\(]|[\)]/', $agent['os_version']);
            $os_agent_text = $os_version.' ('.$os_version_name[1].')';
        }
    } else {
        $os_name = preg_split('/[0-9]/', $agent['os_version'])[0];
        $os_agent_text = $agent['os_version'];
        if (empty($os_name) === false) {
            $os_version = explode($os_name, explode('(', $agent['os_version'])[0])[1];
            $os_agent_text = $os_version;
        }
    }
}

$table_status->data['agent_os'][0] = html_print_div([ 'style' => 'width: 16px; position: relative; left: 75%', 'content' => ui_print_os_icon($agent['id_os'], false, true, true, false, false, false, ['width' => '16px'])], true);
$table_status->data['agent_os'][1] = $os_name;

if (empty($agent['os_version']) !== true) {
    $table_status->data['agent_os_version'][0] = __('OS Version');
    $table_status->data['agent_os_version'][1] = $os_agent_text;
}

$addresses = agents_get_addresses($id_agente, true);
$address = agents_get_address($id_agente);

foreach ($addresses as $k => $add) {
    if ($add == $address) {
        unset($addresses[$k]);
    }
}

if (empty($address) === false) {
    $address_text = '<span class="bolder" >'.$address.'</span>';
    if (!empty($addresses) === true) {
        if (count($addresses) > 3) {
            $address_text .= '&nbsp&nbsp<span id="deploy_sec_ips_down">'.html_print_image(
                'images/sort_down_black.png',
                true,
                ['alt' => 'down']
            ).'</span><span id="deploy_sec_ips_up" style="display: none;">'.html_print_image(
                'images/sort_up_black.png',
                true,
                ['alt' => 'up']
            ).'</span>';
            $address_text .= '<div id="secondary_ips" class="invisible">';
        }

        $first_key = key($addresses);
        foreach ($addresses as $key => $sec_address) {
            if ($first_key !== $key || count($addresses) <= 3) {
                $address_text .= '<br/>';
            }

            $address_text .= '<span class="italic">'.$sec_address.'</span>';
        }

        if (count($addresses) > 3) {
            $address_text .= '</div>';
        }
    }

    $table_status->data['ip_address'][0] = __('IP address');
    $table_status->data['ip_address'][1] = (empty($address) === true) ? '<em>'.__('N/A').'</em>' : $address_text;
}

$table_status->data['agent_version'][0] = __('Agent Version');
$table_status->data['agent_version'][1] = (empty($agent['agent_version']) === true) ? '<i>'.__('N/A').'</i>' : $agent['agent_version'];

$table_status->data['description'][0] = __('Description');
$table_status->data['description'][1] = (empty($agent['comentarios']) === true) ? '<em>'.__('N/A').'</em>' : ui_print_truncate_text($agent['comentarios'], 'description', true);

$agentEventsHeader = html_print_div(
    [
        'class'   => 'agent_details_header',
        'content' => '<span class="subsection_header_title">'.__('Events (Last 24h)').'</span>',
    ],
    true
);

$agentEventsGraph = html_print_div(
    [
        'class'   => 'white-table-graph-content',
        'content' => graph_graphic_agentevents(
            $id_agente,
            95,
            50,
            SECONDS_1DAY,
            '',
            true,
            true,
            500
        ),
        'style'   => 'margin-top: -25px',
    ],
    true
);

$table_agent = $agentStatusHeader.'
    <div class="agent_details_content">
        <div class="agent_details_graph">
            '.$agentStatusGraph.$agentCountModules.'
        </div>
        <div class="agent_details_info">
            '.$alive_animation.html_print_table($table_status, true).'
        </div>
    </div>
    <div class="agent_details_graph">
        '.$agentEventsHeader.$agentEventsGraph.'
    </div>';


/*
 * END: TABLE AGENT BUILD.
 */


/*
 * START: TABLE DATA BUILD
 */

$data_opcional = new stdClass();
$data_opcional->id = 'agent_data_main';
$data_opcional->width = '100%';
$data_opcional->class = 'floating_form';
// Gis and url address.
$agentAdditionalContent = '';
// Position Information.
/*
    if ((bool) $config['activate_gis'] === true) {
    $dataPositionAgent = gis_get_data_last_position_agent(
        $agent['id_agente']
    );
    if (is_array($dataPositionAgent) === true && $dataPositionAgent['stored_longitude'] !== '' && $dataPositionAgent['stored_latitude'] !== '') {
        $data_opcional->data['agent_position'][0] = __('Position (Long, Lat)');

        $dataOptionalOutput = html_print_anchor(
            [
                'href'    => 'index.php?sec=estado&amp;sec2=operation/agentes/ver_agente&amp;tab=gis&amp;id_agente='.$id_agente,
                'content' => $dataPositionAgent['stored_longitude'].', '.$dataPositionAgent['stored_latitude'],
            ],
            true
        );

        if (empty($dataPositionAgent['description']) === false) {
            $dataOptionalOutput .= ' ('.$dataPositionAgent['description'].')';
        }

        $data_opcional->data['agent_position'][1] = $dataOptionalOutput;
    }
}*/

// If the url description is set.
/*
    if (empty($agent['url_address']) === false) {
    $data_opcional->data['url_address'][0] = __('Url address');
    $data_opcional->data['url_address'][1] = html_print_anchor(
        [
            'href'    => $agent['url_address'],
            'content' => $agent['url_address'],
        ],
        true
    );
}*/


// Other IP address and timezone offset.
/*
    if (empty($addresses) === false) {
    $data_opcional->data['other_ip_address'][0] = __('Other IP addresses');
    $data_opcional->data['other_ip_address'][1] = html_print_div(
        [
            'class'   => 'overflow-y mx_height50px',
            'content' => implode('<br>', $addresses),
        ],
        true
    );
    }
*/
// Timezone Offset.
/*
    if ((int) $agent['timezone_offset'] !== 0) {
    $data_opcional->data['timezone_offset'][0] = __('Timezone Offset');
    $data_opcional->data['timezone_offset'][1] = $agent['timezone_offset'];
}*/

// Custom fields.
$fields = db_get_all_rows_filter(
    'tagent_custom_fields',
    ['display_on_front' => 1]
);
if ($fields === false) {
    $fields = [];
}

$custom_fields = [];
foreach ($fields as $field) {
    $custom_value = db_get_all_rows_sql(
        'select tagent_custom_data.description,tagent_custom_fields.is_password_type from tagent_custom_fields 
        INNER JOIN tagent_custom_data ON tagent_custom_fields.id_field = tagent_custom_data.id_field where tagent_custom_fields.id_field = '.$field['id_field'].' and tagent_custom_data.id_agent = '.$id_agente
    );

    if ($custom_value[0]['description'] !== false && $custom_value[0]['description'] != '') {
        $data = [];

        $data[0] = '<b>'.$field['name'].ui_print_help_tip(__('Custom field'), true).'</b>';
        if ($custom_value[0]['is_password_type']) {
                $data[1] = '&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;';
        } else if ($field['is_link_enabled'] === '1') {
            $custom_link_type = io_safe_output($custom_value[0]['description']);
            $custom_link_type = json_decode($custom_link_type);
            list($link_text, $link_url) = $custom_link_type;
            if (json_last_error() !== JSON_ERROR_NONE) {
                $link_text = '';
                $link_url = '';
            }

            if ($link_text === '') {
                $link_text = $link_url;
            }

            $data[1] = '<a target="_blank" href="'.$link_url.'">'.$link_text.'</a>';
        } else {
            $custom_value[0]['description'] = ui_bbcode_to_html($custom_value[0]['description']);
            $data[1] = $custom_value[0]['description'];
        }

        $custom_fields[] = $data;
    }
}

$custom_fields_count = count($custom_fields);
for ($i = 0; $i < $custom_fields_count; $i++) {
    $first_column = $custom_fields[$i];
    $j = ($i + 1);
    $second_column = $custom_fields[$j];

    if (is_array($second_column) === true) {
        $columns = array_merge($first_column, $second_column);
    } else {
        $columns = $first_column;
        if ($data_opcional->data !== null) {
            $filas = count($data_opcional->data);
        }

        $data_opcional->colspan[$filas][1] = 3;
    }

    $data_opcional->data[] = $columns;

    $i++;
}

/*
 * END: TABLE DATA BUILD
 */

    // This javascript piece of code is used to make expandible
    // the body of the table.
?>
    <script type="text/javascript">
        $(document).ready (function () {

            $("#agent_data_main").find("thead").click (function () {
                close_table('#agent_data_main');
            })
            .css('cursor', 'pointer');

            $("#table_events").find(".white_table_graph_header").click (function () {
                close_table_white('#table_events');
            })
            .css('cursor', 'pointer');

            $("#table_access_rate").find(".white_table_graph_header").click (function () {
                close_table_white('#table_access_rate');
            })
            .css('cursor', 'pointer');

            function close_table(id){
                var arrow = $(id).find("thead").find("img");
                if (arrow.hasClass("closed")) {
                    arrow.removeClass("closed");
                    arrow.prop("src", "images/arrow_down_green.png");
                    $(id).find("tbody").show();
                } else {
                    arrow.addClass("closed");
                    arrow.prop("src", "images/arrow_right_green.png");
                    $(id).find("tbody").hide();
                }
            }

            function close_table_white(id){
                var arrow = $(id).find(".white_table_graph_header").find("img");
                if (arrow.hasClass("closed")) {
                    arrow.removeClass("closed");
                    arrow.prop("src", "images/arrow_down_green.png");
                    $(id).find(".white_table_graph_content").show();
                } else {
                    arrow.addClass("closed");
                    arrow.prop("src", "images/arrow_right_green.png");
                    $(id).find(".white_table_graph_content").hide();
                }
            }
        });
    </script>
<?php
$agent_contact = get_resume_agent_concat($id_agente, $all_groups, $agent);

$agentDetails = html_print_div(
    [
        'class'   => 'box-flat agent_details_col',
        'content' => $table_agent,
    ],
    true
);

$agentContact = html_print_div(
    [
        'class'   => 'box-flat agent_details_col mrgn_lft_20px',
        'content' => $agent_contact,
    ],
    true
);

/*
 * EVENTS TABLE END.
 */
if (isset($data_opcional) === false || isset($data_opcional->data) === false || empty($data_opcional->data) === true) {
    $agentAdditionalInfo = '';
} else {
    $agentAdditionalInfo = ui_toggle(
        html_print_table($data_opcional, true),
        '<span class="subsection_header_title">'.__('Agent data').'</span>',
        'status_monitor_agent',
        false,
        false,
        true,
        '',
        'white-box-content',
        'box-flat white_table_graph w100p'
    );
}

$agentIncidents = (isset($table_incident) === false) ? '' : html_print_table($table_incident, true);

html_print_div(
    [
        'class'   => 'agent_details_first_row agent_details_line',
        'content' => $agentDetails.$agentContact,
    ]
);

html_print_div(
    [
        'class'   => 'agent_details_line',
        'content' => $agentEvents,
    ]
);

if (empty($agentAdditionalInfo) === false) {
    html_print_div(
        [
            'class'   => 'agent_details_line',
            'content' => $agentAdditionalInfo,
        ]
    );
}

if (empty($agentIncidents) === false) {
    html_print_div(
        [
            'class'   => 'agent_details_line',
            'content' => ui_toggle(
                '<div class=\'w100p\' id=\'agent_incident\'>'.$agentIncidents.'</div>',
                '<span class="subsection_header_title">'.__('Active issue on this agent').'</span>',
                __('Agent incident main'),
                'agent_incident',
                false,
                true,
                '',
                'box-flat white-box-content no_border',
                'box-flat white_table_graph w100p',
            ),
        ],
    );
}

$count_network_incerfaces = agents_get_network_interfaces([$agent], false, false, 0, true);
if ($count_network_incerfaces > 0) {
    ui_toggle(
        html_print_div(
            [
                'id'      => 'agent_interface_info',
                'content' => '',
                'class'   => 'w100p',
            ],
            true
        ),
        '<b>'.__('Interface information (SNMP)').'</b>',
        '',
        'interface-table-status-agent',
        true
    );
}
?>

<script type="text/javascript">
    $(document).ready (function () {
        $('#deploy_sec_ips_up').on('click', function() {
            $('#secondary_ips').hide();
            // Avoid setting display block property with show/hide methods.
            $('#deploy_sec_ips_up').css('display', 'none');
            $('#deploy_sec_ips_down').css('display', '');
        });

        $('#deploy_sec_ips_down').on('click', function() {
            $('#secondary_ips').show();
            // Avoid setting display block property with show/hide methods.
            $('#deploy_sec_ips_down').css('display', 'none');
            $('#deploy_sec_ips_up').css('display', '');
        });
        <?php if ($count_network_incerfaces > 0) { ?>
        load_list_snmp();
        <?php } ?>
    });

    function load_list_snmp() {
        var parameters = {};
        parameters['list_snmp_modules'] = 1;
        parameters['agent'] = <?php echo $id_agente; ?>;
        parameters['page'] = 'include/ajax/module';
        $('#agent_interface_info').empty();
        jQuery.ajax ({
            data: parameters,
            type: 'POST',
            url: "ajax.php",
            dataType: 'html',
            success: function (data) {
                $('#agent_interface_info').empty();
                $('#agent_interface_info').html(data);
            }
        });
    }

    function change_page_snmp(offset) {
        var parameters = {};
        parameters['list_snmp_modules'] = 1;
        parameters["offset"] = offset;
        parameters['agent'] = <?php echo $id_agente; ?>;
        parameters['page'] = 'include/ajax/module';
        $('#agent_interface_info').empty();
        jQuery.ajax ({
            data: parameters,
            type: 'POST',
            url: "ajax.php",
            dataType: 'html',
            success: function (data) {
                $('#agent_interface_info').empty();
                $('#agent_interface_info').html(data);
            }
        });
    }
</script>
