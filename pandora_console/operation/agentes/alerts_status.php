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

// Login check.
check_login();

if (is_ajax()) {
    include_once 'include/functions_reporting.php';

    $get_alert_fired = get_parameter('get_alert_fired', 0);

    if ($get_alert_fired) {
        // Calculate alerts fired.
        $data_reporting = reporting_get_group_stats();
        echo $data_reporting['monitor_alerts_fired'];
    }

    return;
}

require_once $config['homedir'].'/include/functions_agents.php';
require_once $config['homedir'].'/operation/agentes/alerts_status.functions.php';
require_once $config['homedir'].'/include/functions_users.php';

$strict_user = db_get_value('strict_acl', 'tusuario', 'id_user', $config['id_user']);

$disabled = get_parameter('disabled', 'all_enabled');
$filter_standby = get_parameter('standby', 'all');
$id_group = (int) get_parameter('ag_group', 0);
// 0 is the All group (selects all groups)
$free_search = get_parameter('free_search', '');
$search_sg = get_parameter('search_sg', 0);
$user_tag_array = tags_get_user_tags($config['id_user'], 'AR', true);

if ($user_tag_array) {
    $user_tag_array = array_values(array_keys($user_tag_array));

    $user_tag = '';

    foreach ($user_tag_array as $key => $value) {
        if ($value === end($user_tag_array)) {
            $user_tag .= $value;
        } else {
            $user_tag .= $value.',';
        }
    }

    $tag_filter = get_parameter('tag_filter', $user_tag);

    $tag_param_validate = explode(',', $tag_filter);

    foreach ($tag_param_validate as $key => $value) {
        if (!in_array($value, $user_tag_array)) {
            db_pandora_audit(
                AUDIT_LOG_ACL_VIOLATION,
                'Trying to access Alert view'
            );
            include 'general/noaccess.php';
            exit;
        }
    }
} else {
    $tag_filter = get_parameter('tag_filter', 0);
}

if ($tag_filter) {
    if ($id_group && $strict_user) {
        $tag_filter = 0;
    }
}

$action_filter = get_parameter('action', 0);

$sec2 = get_parameter_get('sec2');
$sec2 = safe_url_extraclean($sec2);

$sec = get_parameter_get('sec');
$sec = safe_url_extraclean($sec);

$flag_alert = (bool) get_parameter('force_execution', 0);
$alert_validate = (bool) get_parameter('alert_validate', 0);
$tab = get_parameter_get('tab', null);
$op = get_parameter('op', null);

$refr = (int) get_parameter('refr', 0);
$pure = get_parameter('pure', 0);

$url = 'index.php?sec='.$sec.'&sec2='.$sec2.'&refr='.$refr.'&disabled='.$disabled.'&filter_standby='.$filter_standby.'&ag_group='.$id_group.'&tag_filter='.$tag_filter.'&action_filter='.$action_filter;

if ($flag_alert == 1 && check_acl($config['id_user'], $id_group, 'AW')) {
    forceExecution($id_group);
}

if (isset($id_agente) === false || empty($id_agente) === true) {
    $idAgent = get_parameter_get('id_agente', 0);
} else {
    $idAgent = $id_agente;
}

// Show alerts for specific agent.
if ($idAgent != 0) {
    $url = $url.'&id_agente='.$idAgent;

    $id_group = agents_get_agent_group($idAgent);

    // All groups is calculated in ver_agente.php. Avoid to calculate it again.
    if (!isset($all_groups)) {
        $all_groups = agents_get_all_groups_agent($idAgent, $id_group);
    }

    if (!check_acl_one_of_groups($config['id_user'], $all_groups, 'AR') && !check_acl_one_of_groups($config['id_user'], $id_group, 'AW')) {
        db_pandora_audit(
            AUDIT_LOG_ACL_VIOLATION,
            'Trying to access alert view'
        );
        include 'general/noaccess.php';
        exit;
    }

    $idGroup = false;

    $print_agent = false;

    $tab = get_parameter('tab', 'main');

    ob_start();

    if ($tab == 'main') {
        $agent_view_page = true;
    }
} else {
    $agent_a = check_acl($config['id_user'], 0, 'AR');
    $agent_w = check_acl($config['id_user'], 0, 'AW');
    $access = ($agent_a == true) ? 'AR' : (($agent_w == true) ? 'AW' : 'AR');

    if (!$agent_a && !$agent_w) {
        db_pandora_audit(
            AUDIT_LOG_ACL_VIOLATION,
            'Trying to access alert view'
        );
        include 'general/noaccess.php';
        return;
    }

    $agents = array_keys(
        agents_get_group_agents(
            array_keys(
                users_get_groups($config['id_user'], $access, false)
            ),
            false,
            'lower',
            true
        )
    );

    $idGroup = $id_group;
    // If there is no agent defined, it means that it cannot search for the secondary groups.
    $all_groups = [$id_group];

    $print_agent = true;

    // Header.
    ui_print_standard_header(
        __('Alert detail'),
        'images/op_alerts.png',
        false,
        '',
        false,
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

$alerts = [];

if ($op != null) {
    $url = $url.'&op='.$op;
}

if ($tab != null) {
    $url = $url.'&tab='.$tab;
}

if ($pure) {
    $url .= '&pure='.$pure;
}

if (empty($free_search) === false) {
    $url .= '&free_search='.$free_search;
}

$columns = [];
$column_names = [];


if ((bool) check_acl($config['id_user'], $id_group, 'LW') === true || (bool) check_acl($config['id_user'], $id_group, 'LM') === true) {
    array_unshift(
        $column_names,
        [
            'title' => __('Standby'),
            'text'  => __('Standby'),
        ],
        [
            'title' => __('Operations'),
            'text'  => __('Operations'),
            'class' => 'left pdd_l_0px pdd_r_0px w100p',
        ],
    );

    $columns = array_merge(
        ['standby'],
        ['force'],
        $columns
    );

    /*
        if ($print_agent === true) {
        array_push(
            $column_names,
            ['text' => 'Agent']
        );

        $columns = array_merge(
            $columns,
            ['agent_name']
        );
    }*/
}

array_push(
    $column_names,
    ['text' => 'Agent'],
    ['text' => 'Module'],
    ['text' => 'Template'],
    [
        'title' => __('Action'),
        'text'  => __('Action'),
        'style' => 'min-width: 15%;',
    ],
    ['text' => 'Last fired'],
    ['text' => 'Status']
);

$columns = array_merge(
    $columns,
    ['agent_name'],
    ['agent_module_name'],
    ['template_name'],
    ['action'],
    ['last_fired'],
    ['status']
);

    if ((bool) check_acl($config['id_user'], $id_group, 'LW') === true || (bool) check_acl($config['id_user'], $id_group, 'LM') === true) {
        array_unshift(
            $column_names,
            [
                'title' => __('Validate'),
                'text'  => html_print_checkbox('all_validate', 0, false, true, false),
                'class' => 'dt-left',
                'style' => 'max-width: 5%;',
            ]
        );

        $columns = array_merge(
            ['validate'],
            $columns
        );
    }

   
    $no_sortable_columns = [
        0,
        1,
        2,
        -3,
        -1,
    ];


ob_start();
if (isset($agent_view_page) === false) {
    $agent_view_page = false;
}

if ($agent_view_page === true) {
    $alerts_count = alerts_get_alerts(0, '', 'all', -1, true, true, $agent['id_agente']);
    $disabled_alert = false;
    // Optimal limit to display alerts.
    if ((int) $alerts_count > AGENT_ALERT_LIMIT) {
        $disabled_alert = true;
    }

    ui_print_datatable(
        [
            'id'                  => 'alerts_status_datatable',
            'class'               => 'info_table',
            'style'               => 'width: 100%',
            'columns'             => $columns,
            'column_names'        => $column_names,
            'no_sortable_columns' => $no_sortable_columns,
            'ajax_url'            => 'include/ajax/alert_list.ajax',
            'dom_elements'        => 'pfrti',
            'ajax_data'           => [
                'get_agent_alerts_datatable' => 1,
                'id_agent'                   => $idAgent,
                'url'                        => $url,
                'agent_view_page'            => true,
                'all_groups'                 => $all_groups,
            ],
            'drawCallback'        => 'alerts_table_controls()',
            'order'               => [
                'field'     => 'agent_module_name',
                'direction' => 'asc',
            ],
            'zeroRecords'         => __('No alerts found'),
            'emptyTable'          => __('No alerts found'),
            'search_button_class' => 'sub filter float-right secondary',
            'form'                => [
                'inputs'    => [
                    [
                        'label'     => __('Free text for search (*):').ui_print_help_tip(
                            __('Filter by module name, template name or action name'),
                            true
                        ),
                        'type'      => 'text',
                        'name'      => 'free_search_alert',
                        'value'     => $free_search,
                        'size'      => 20,
                        'maxlength' => 100,
                    ],
                ],
                'no_toggle' => true,
                'class'     => 'flex',
            ],
            'start_disabled'      => false,
        ]
    );
} else {
    $tab = get_parameter('tab', 'main');
    $alert_agent_view = false;
    if ($tab == 'alert') {
        $alert_agent_view = true;
    }

    ui_print_datatable(
        [
            'id'                  => 'alerts_status_datatable',
            'class'               => 'info_table',
            'style'               => 'width: 100%;',
            'columns'             => $columns,
            'column_names'        => $column_names,
            'no_sortable_columns' => $no_sortable_columns,
            'ajax_url'            => 'include/ajax/alert_list.ajax',
            'ajax_data'           => [
                'get_agent_alerts_datatable' => 1,
                'id_agent'                   => $idAgent,
                'url'                        => $url,
            ],
            'drawCallback'        => 'alerts_table_controls()',
            'order'               => [
                'field'     => 'agent_module_name',
                'direction' => 'asc',
            ],
            'zeroRecords'         => __('No alerts found'),
            'emptyTable'          => __('No alerts found'),
            'search_button_class' => 'sub filter float-right secondary',
            'filter_main_class'   => 'box-flat white_table_graph fixed_filter_bar',
            'form'                => [
                'html' => printFormFilterAlert(
                    $id_group,
                    $disabled,
                    $free_search,
                    $alert_agent_view,
                    $filter_standby,
                    $tag_filter,
                    true,
                    true,
                    $strict_user
                ),
            ],
            'start_disabled'      => false,
        ]
    );
}

    if (((bool) check_acl($config['id_user'], $id_group, 'AW') === true || (bool) check_acl($config['id_user'], $id_group, 'LM') === true)) {
        if ($agent_view_page === true) {
            html_print_div(
                [
                    'class'   => 'action-buttons pdd_r_5px w100p',
                    'content' => html_print_submit_button(
                        __('Validate'),
                        'alert_validate',
                        false,
                        [
                            'icon' => 'wand',
                            'mode' => 'secondary mini',
                        ],
                        true
                    ),
                ]
            );
        } else {
            html_print_action_buttons(
                html_print_submit_button(
                    __('Validate'),
                    'alert_validate',
                    false,
                    [ 'icon' => 'wand' ],
                    true
                ),
                ['type' => 'form_action']
            );
        }
    } else {
        html_print_action_buttons(
            '',
            ['type' => 'form_action']
        );
    }


$html_content = ob_get_clean();

if ($agent_view_page === true) {
    // Create controlled toggle content.
    html_print_div(
        [
            'class'   => 'agent_details_line',
            'content' => ui_toggle(
                $html_content,
                '<span class="subsection_header_title">'.__('Full list of alerts').'</span>',
                'status_monitor_agent',
                !$alerts_defined,
                ($alerts_count > 0) ? false : true,
                true,
                '',
                '',
                'box-flat white_table_graph w100p'
            ),
        ],
    );
} else {
    // Dump entire content.
    echo $html_content;
}

// Filter control.
echo '<input type="hidden" id="filter_applied" value="0" />';

// Strict user hidden.
echo '<div id="strict_hidden" class="invisible">';
html_print_input_text('strict_user_hidden', $strict_user);

html_print_input_text('is_meta_hidden', 0);
echo '</div>';

ui_require_css_file('cluetip', 'include/styles/js/');
ui_require_jquery_file('cluetip');

if (isset($id_agente)) {
    $system_higher = false;
    $modules_agent = db_get_all_rows_sql(sprintf('SELECT id_agente FROM tagente_modulo WHERE id_agente = %s', $id_agente));
    if (is_array($modules_agent)) {
        $all_modules = db_get_all_rows_sql('SELECT id_agente FROM tagente_modulo');
        $all_agents = db_get_all_rows_sql('SELECT id_agente FROM tagente');
        if (is_array($all_modules) && is_array($all_agents)) {
            if ((count($all_modules) / count($all_agents)) >= 200) {
                $system_higher = true;
            }
        }
    }

    echo '<div id="system_higher" class="invisible_important agent_details_agent_data flex_important"><img src="images/alert-yellow@svg.svg" width="10%" class="mrgn_right_20px">'.__('Your system has a much higher rate of modules per agent than recommended (200 modules per agent). This implies performance problems in the system, please consider reducing the number of modules in this agent.').'</div>';
} else {
    $system_higher = false;
}

?>

<script type="text/javascript">

function alerts_table_controls() {

    $("button.template_details").cluetip ({
        arrows: true,
        attribute: 'href',
        cluetipClass: 'default'
    }).click (function () {
        return false;
    });

    $("a.template_details").cluetip ({
                arrows: true,
                attribute: 'href',
                cluetipClass: 'default'
            });

    $('[id^=checkbox-all_validate]').change(function(){
        if ($("#checkbox-all_validate").prop("checked")) {
            $("input[id^=checkbox-validate]").prop('checked', true);
        }
        else{
            $('[id^=checkbox-validate]').parent().parent().removeClass('checkselected');
            $('[name^=validate]').prop("checked", false);
        }
    });

    $("[id^='div_tip_']").click(function() {
        var id = $(this)
        .attr("id")
        .split("_")[2];

        $("#tip_dialog_" + id).dialog({
            title: $("#tip_dialog_" + id).data("title"),
            modal: true,
            maxWidth: 600,
            minWidth: 400,
            show: {
                effect: "fade",
                duration: 200
            },
            hide: {
                effect: "fade",
                duration: 200
            },
            closeOnEscape: true,
            buttons: {
                Close: function() {
                    $(this).dialog("close");
                }
            }
        });
    });

}

$(document).ready ( function () {
    alerts_table_controls();
    $('#button-alert_validate').on('click', function () {
        validateAlerts();
    });
});

$('#checkbox-search_sg').click(function(){
    if ($('#checkbox-search_sg').val() == 0) {
        $('#checkbox-search_sg').val(1);
    }else {
        $('#checkbox-search_sg').val(0);
    }
});

$('table.alert-status-filter #ag_group').change (function () {
    var strict_user = $("#text-strict_user_hidden").val();
    var is_meta = $("#text-is_meta_hidden").val();

    if (($(this).val() != 0) && (strict_user != 0)) {
        $("table.alert-status-filter #tag_filter").hide();
        if (is_meta) {
            $("table.alert-status-filter #table1-0-4").hide();
        } else {
            $("table.alert-status-filter #table2-0-4").hide();
        }
    } else {
        $("#tag_filter").show();
        if (is_meta) {
            $("table.alert-status-filter #table1-0-4").show();
        } else {
            $("table.alert-status-filter #table2-0-4").show();
        }
    }
}).change();

<?php if ($system_higher === true) { ?>
    $("#system_higher").dialog({
        title: "<?php echo __('Warning'); ?>",
        resizable: true,
        draggable: true,
        modal: true,
        width: 500,
        height: 150,
        buttons: [{
            text: "OK",
            click: function() {
                $(this).dialog("close");
            },
            class: 'invisible_important',
        }],
        overlay: {
            opacity: 0.5,
            background: "black"
        },
        closeOnEscape: false,
        open: function(event, ui) {
            $(".ui-dialog-titlebar-close").hide();
            $("#system_higher").removeClass('invisible_important');
            setTimeout(() => {
                $(".ui-dialog-buttonset").find('button').removeClass('invisible_important');
            }, 4000);
        }
    });
<?php } ?>

function validateAlerts() {
    var alert_ids = [];

    $('[id^=checkbox-validate]:checked').each(function() {
        alert_ids.push($(this).val());
    });

    if (alert_ids.length === 0) {
        confirmDialog({
            title: "<?php echo __('No alert selected'); ?>",
            message: "<?php echo __('You must select at least one alert.'); ?>",
            hideCancelButton: true
        });
    }

    $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {
            alert_ids: alert_ids,
            page: "include/ajax/alert_list.ajax",
            alert_validate: 1,
            all_groups: <?php echo json_encode($all_groups); ?>,
        },
        dataType: "json",
        success: function (data) {
            $("#menu_tab_frame_view").after(data);
            var table = $('#alerts_status_datatable').DataTable({
                ajax: "data.json"
            });

            table.ajax.reload();
        },
    });

}

function show_display_update_action(id_module_action, alert_id, alert_id_agent_module, action_id,id_agent='') {
    $.each($('[id^="update_action-div"]'), function(){
        $(this).html('');
    });
    var params = [];
    params.push("show_update_action_menu=1");
    params.push("id_agent_module=" + alert_id_agent_module);
    params.push("id_module_action=" + id_module_action);
    params.push("id_alert=" + alert_id);
    params.push("id_action=" + action_id);
    params.push("id_agent=" + id_agent);
    params.push("page=include/ajax/alert_list.ajax");
    jQuery.ajax ({
        data: params.join ("&"),
        type: 'POST',
        url: action="<?php echo ui_get_full_url('ajax.php', false, false, false); ?>",
        success: function (data) {
            $('#update_action-div-'+alert_id).html(data);
            $('#update_action-div-'+alert_id).dialog ({
                    resizable: true,
                    draggable: true,
                    title: '<?php echo __('Update action'); ?>',
                    modal: true,
                    overlay: {
                        opacity: 0.5,
                        background: "black"
                    },
                    open: function() {
                        $('#action_select_ajax-'+alert_id).select2();
                    },
                    onclose: function() {
                        $('#update_action-div-'+alert_id).html("");
                    },
                    width: 600,
                    height: 350,
                })
                .show ();
        }
    });
}

function show_add_action(id_alert) {
    $("#add_action-div-" + id_alert).hide ()
        .dialog ({
            resizable: true,
            draggable: true,
            modal: true,
            width: 700,
            title: '<?php echo __('Add action'); ?>',
            modal: true,
            overlay: {
                opacity: 0.5,
                background: "black"
            }
        })
        .show ();
}
</script>