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

if (check_acl($config['id_user'], 0, 'AR') === false) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Agent Management'
    );
    include 'general/noaccess.php';
    return;
}

require_once 'interface_view.functions.php';
require_once $config['homedir'].'/include/functions_agents.php';

$recursion = get_parameter_switch('recursion', false);
if ($recursion === false) {
    $recursion = get_parameter('recursion', false);
}

$selected_agents = get_parameter('selected_agents', []);
$selected_interfaces = get_parameter('selected_interfaces', []);
$refr = (int) get_parameter('refr', 0);
$offset = (int) get_parameter('offset', 0);
$sort_field = get_parameter('sort_field');
$sort = get_parameter('sort', 'none');
$autosearch = false;
$sec = (string) get_parameter('sec', 'view');
$agent_id = (int) get_parameter('id_agente', 0);

if (isset($subpage) === false) {
    $subpage = '';
}

if ($sec === 'view') {
    ui_print_standard_header(
        __('Interface view').$subpage,
        '',
        false,
        '',
        true,
        [],
        [
            [
                'link'  => '',
                'label' => __('Monitoring'),
            ],
            [
                'link'  => '',
                'label' => __('Views'),
            ],
        ]
    );
}

$agent_filter = ['id_agente' => $agent_id];

// Autosearch if search parameters are different from those by default.
if (empty($selected_agents) === false || empty($selected_interfaces) === false
    || $sort_field !== '' || $sort !== 'none' || $sec === 'estado'
) {
    $autosearch = true;
}

print_filters($sec);

$result = false;

if ($autosearch === true) {
    if ($sec === 'estado') {
        $result = agents_get_network_interfaces(false, $agent_filter);
    } else {
        $result = agents_get_network_interfaces($selected_agents);
    }

    if ($result === false || empty($result) === true) {
        $result = [];
    } else {
        $pagination = ui_pagination(
            count($selected_interfaces),
            false,
            $offset,
            0,
            true,
            'offset',
            false
        );

        html_print_action_buttons(
            '',
            [ 'right_content' => $pagination ]
        );
    }
}

print_table(
    $result,
    $selected_agents,
    $selected_interfaces,
    $sort_field,
    $sort,
    $offset,
    $sec
);

?>
<script type="text/javascript">

$(document).ready(function() {
    var group_id = $("#group_id").val();
    load_agents_selector(group_id);

    var sec = "<?php echo $sec; ?>";
    var agent_id = "<?php echo $agent_id; ?>";

    if (sec === 'estado' && agent_id > 0) {
        load_agent_interfaces_selector([agent_id]);
    }
    $("#selected_agents").filterByText($("#text-filter_agents"));
});


$('#moduletype').click(function() {
    jQuery.get (
        "ajax.php",
        {
            "page": "general/subselect_data_module",
            "module":$('#moduletype').val()
        },
        function (data, status) {
            $("#datatypetittle").show ();
            $("#datatypebox").hide ()
            .empty ()
            .append (data)
            .show ();
        },
        "html"
    );

    return false;
});


function toggle_full_value(id) {
    text = $('#hidden_value_module_' + id).html();
    old_text = $("#value_module_text_" + id).html();
    
    $("#hidden_value_module_" + id).html(old_text);
    
    $("#value_module_text_" + id).html(text);
}

function load_agents_selector(group) {
    
    jQuery.post ("ajax.php",
        {
            "page" : "operation/agentes/ver_agente",
            "get_agents_group_json" : 1,
            "get_agents_also_interfaces" : 1,
            "id_group" : group,
            "privilege" : "AW",
            "keys_prefix" : "_",
            "recursion" : $('#checkbox-recursion').is(':checked')
        },
        function (data, status) {
            $("#selected_agents").html('');
            jQuery.each (data, function (id, value) {
                id = id.substring(1);
                
                option = $("<option></option>")
                    .attr ("value", value["id_agente"])
                    .html (value["alias"]);
                $("#id_agents").append (option);
                $("#selected_agents").append (option);
            });

            var selected_agents = "<?php echo implode(',', $selected_agents); ?>";

            $.each(selected_agents.split(","), function(i,e) {
                $("#selected_agents option[value='" + e + "']").prop(
                    "selected",
                    true
                );
            });

            load_agent_interfaces_selector($("#selected_agents").val());
        },
        "json"
    );
}

$("#group_id").change(function() {
    load_agents_selector(this.value);
});

$("#checkbox-recursion").change (function () {
    jQuery.post ("ajax.php",
        {"page" : "operation/agentes/ver_agente",
            "get_agents_group_json" : 1,
            "get_agents_also_interfaces" : 1,
            "id_group" :     $("#group_id").val(),
            "privilege" : "AW",
            "keys_prefix" : "_",
            "recursion" : $('#checkbox-recursion').is(':checked')
        },
        function (data, status) {
            $("#selected_agents").html('');
            $("#module").html('');
            jQuery.each (data, function (id, value) {
                id = id.substring(1);
                option = $("<option></option>")
                    .attr ("value", value["id_agente"])
                    .html (value["alias"]);
                $("#id_agents").append (option);
                $("#selected_agents").append (option);
            });
        },
        "json"
    );
});

$("#selected_agents").click (function() {
    var selected_agents = $(this).val();

    load_agent_interfaces_selector(selected_agents);
});

function load_agent_interfaces_selector(selected_agents) {
    $("#selected_interfaces").empty();
    jQuery.post ("ajax.php",
        {
            "page" : "include/ajax/agent",
            "get_agents_interfaces" : 1,
            "id_agents" : selected_agents
        },
        function (data, status) {
            $("#module").html('');
            var option = $("<option></option>")
                        .attr ("value", "")
                        .html ("Any");
            $("#selected_interfaces").append(option);
            if (data) {
                Object.values(data).forEach(function(obj) {
                    for (const [key, value] of Object.entries(obj.interfaces)) {
                        option = $("<option></option>")
                        .attr ("value", value.status_module_id)
                        .html ('(' + obj.agent_alias + ') ' + key);
                    $("#selected_interfaces").append(option);
                    }
                });
            }

            var selected_interfaces =
                "<?php echo implode(',', $selected_interfaces); ?>";

            $.each(selected_interfaces.split(","), function(i,e) {
                $("#selected_interfaces option[value='" + e + "']").prop(
                    "selected",
                    true
                );
            });

        },
        "json"
    );
}

</script>
