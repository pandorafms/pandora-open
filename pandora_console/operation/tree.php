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
ui_require_css_file('tree');

ui_require_css_file('fixed-bottom-box');

global $config;

$pure          = get_parameter('pure', 0);
$tab           = get_parameter('tab', 'group');
$search_group  = get_parameter('searchGroup', '');
$search_agent  = get_parameter('searchAgent', '');
$status_agent  = get_parameter('statusAgent', AGENT_STATUS_ALL);
$search_module = get_parameter('searchModule', '');
$status_module = get_parameter('statusModule', -1);
$show_not_init_agents = get_parameter('show_not_init_agents', true);
$show_not_init_modules = get_parameter('show_not_init_modules', true);
$group_id      = (int) get_parameter('group_id');
$tag_id        = (int) get_parameter('tag_id');
$strict_acl    = (bool) db_get_value('strict_acl', 'tusuario', 'id_user', $config['id_user']);
$serach_hirearchy = (bool) get_parameter('searchHirearchy', false);
$show_disabled = get_parameter('show_disabled', false);

$url = 'index.php?sec=estado&sec2=operation/tree&refr=0&pure='.$pure.'&tab=%s';

$tabs = [];

if ($strict_acl === false) {
    $tabs['tag'] = [
        'text'   => "<a href='".sprintf($url, 'tag')."'>".html_print_image(
            'images/tag@svg.svg',
            true,
            [
                'title' => __('Tags'),
                'class' => 'invert_filter',
            ]
        ).'</a>',
        'active' => ($tab == 'tag'),
    ];

    $tabs['os'] = [
        'text'   => "<a href='".sprintf($url, 'os')."'>".html_print_image(
            'images/workstation@groups.svg',
            true,
            [
                'title' => __('OS'),
                'class' => 'invert_filter',
            ]
        ).'</a>',
        'active' => ($tab == 'os'),
    ];

    $tabs['group'] = [
        'text'   => "<a href='".sprintf($url, 'group')."'>".html_print_image(
            'images/groups@svg.svg',
            true,
            [
                'title' => __('Groups'),
                'class' => 'invert_filter',
            ]
        ).'</a>',
        'active' => ($tab == 'group'),
    ];

    $tabs['module_group'] = [
        'text'   => "<a href='".sprintf($url, 'module_group')."'>".html_print_image(
            'images/modules-group@svg.svg',
            true,
            [
                'title' => __('Module groups'),
                'class' => 'invert_filter',
            ]
        ).'</a>',
        'active' => ($tab == 'module_group'),
    ];

    $tabs['module'] = [
        'text'   => "<a href='".sprintf($url, 'module')."'>".html_print_image(
            'images/modules@svg.svg',
            true,
            [
                'title' => __('Modules'),
                'class' => 'invert_filter',
            ]
        ).'</a>',
        'active' => ($tab == 'module'),
    ];
}

$header_title = __('Tree view');
$header_sub_title = __('Sort the agents by %s');
switch ($tab) {
    case 'tag':
        $header_sub_title = sprintf($header_sub_title, __('Tags'));
    break;

    case 'os':
        $header_sub_title = sprintf($header_sub_title, __('OS'));
    break;

    case 'group':
        $header_sub_title = sprintf($header_sub_title, __('Groups'));
    break;

    case 'module_group':
        $header_sub_title = sprintf($header_sub_title, __('Module groups'));
    break;

    case 'module':
        $header_sub_title = sprintf($header_sub_title, __('Modules'));
    break;
}

if (!$strict_acl) {
    $header_title = $header_title.' &raquo; '.$header_sub_title;
}



ui_print_standard_header(
    $header_title,
    'images/extensions.png',
    false,
    '',
    false,
    $tabs,
    [
        [
            'link'  => '',
            'label' => __('Monitoring'),
        ],
        [
            'link'  => '',
            'label' => __('View'),
        ],
    ]
);

// ---------------------Tabs -------------------------------------------
// --------------------- form filter -----------------------------------
$table = new StdClass();
$table->width = '100%';
$table->class = 'filter-table-adv';
$table->data = [];
$table->rowspan = [];
$table->size = [];

// Agent filter.
$agent_status_arr = [];
$agent_status_arr[AGENT_STATUS_ALL] = __('All');

// Default.
$agent_status_arr[AGENT_STATUS_NORMAL] = __('Normal');
$agent_status_arr[AGENT_STATUS_WARNING] = __('Warning');
$agent_status_arr[AGENT_STATUS_CRITICAL] = __('Critical');
$agent_status_arr[AGENT_STATUS_UNKNOWN] = __('Unknown');
$agent_status_arr[AGENT_STATUS_NOT_INIT] = __('Not init');
$agent_status_arr[AGENT_STATUS_ALERT_FIRED] = __('Fired alerts');

$table->data['group_row'][] = html_print_label_input_block(
    __('Search group'),
    html_print_input_text('search_group', $search_group, '', 25, 30, true)
);



$table->data['agent_row'][] = html_print_label_input_block(
    __('Search agent'),
    html_print_input_text(
        'search_agent',
        $search_agent,
        '',
        25,
        30,
        true
    )
);

$table->data['agent_row'][] = html_print_label_input_block(
    __('Show not init agents'),
    html_print_checkbox_switch(
        'show_not_init_agents',
        $show_not_init_agents,
        true,
        true
    )
);

$table->data['agent_row'][] = html_print_label_input_block(
    __('Show full hirearchy'),
    html_print_checkbox_switch(
        'serach_hirearchy',
        $serach_hirearchy,
        false,
        true
    )
);

$table->data['agent_row'][] = html_print_label_input_block(
    __('Agent status'),
    html_print_select(
        $agent_status_arr,
        'status_agent',
        $status_agent,
        '',
        '',
        0,
        true,
        false,
        false,
        '',
        false,
        'width:100%'
    ).html_print_input_hidden(
        'show_not_init_modules_hidden',
        $show_not_init_modules,
        true
    )
);

// Button.

    // Module filter.
    $module_status_arr = [];
    $module_status_arr[-1] = __('All');
    // Default.
    $module_status_arr[AGENT_MODULE_STATUS_NORMAL] = __('Normal');
    $module_status_arr[AGENT_MODULE_STATUS_WARNING] = __('Warning');
    $module_status_arr[AGENT_MODULE_STATUS_CRITICAL_BAD] = __('Critical');
    $module_status_arr[AGENT_MODULE_STATUS_UNKNOWN] = __('Unknown');
    $module_status_arr[AGENT_MODULE_STATUS_NOT_INIT] = __('Not init');
    $module_status_arr['fired'] = __('Fired alerts');

    $table->data['last_row'][] = html_print_label_input_block(
        __('Search module'),
        html_print_input_text('search_module', $search_module, '', 25, 30, true)
    );

    $table->data['last_row'][] = html_print_label_input_block(
        __('Show not init modules'),
        html_print_checkbox_switch('show_not_init_modules', $show_not_init_modules, true, true)
    );

    $table->data['last_row'][] = html_print_label_input_block(
        __('Module status'),
        html_print_select(
            $module_status_arr,
            'status_module',
            $status_module,
            '',
            '',
            0,
            true,
            false,
            false,
            '',
            false,
            'width:100%'
        )
    );


$form_html = '<form id="tree_search" method="post" action="index.php?sec=monitoring&sec2=operation/tree&refr=0&tab='.$tab.'&pure='.$config['pure'].'">';
$form_html .= html_print_table($table, true);
$form_html .= html_print_div(
    [
        'class'   => 'action-buttons',
        'content' => html_print_submit_button(
            __('Filter'),
            'uptbutton',
            false,
            [
                'icon' => 'search',
                'mode' => 'mini',
            ],
            true
        ),
    ],
    true
);
$form_html .= '</form>';

ui_toggle(
    $form_html,
    '<span class="subsection_header_title">'.__('Tree search').'</span>',
    'tree_search',
    false,
    true,
    false,
    '',
    'white-box-content',
    'box-flat white_table_graph fixed_filter_bar'
);

html_print_input_hidden('group-id', $group_id);
html_print_input_hidden('tag-id', $tag_id);

// --------------------- form filter -----------------------------------
ui_include_time_picker();
ui_require_jquery_file('ui.datepicker-'.get_user_language(), 'include/javascript/i18n/');

ui_require_javascript_file('TreeController', 'include/javascript/tree/');

ui_print_spinner(__('Loading'));

html_print_div(
    [
        'id'      => 'tree-controller-recipient',
        'content' => '',
    ]
);

$infoHeadTitle = '';
?>


<script type="text/javascript" src="include/javascript/fixed-bottom-box.js"></script>

<script type="text/javascript">
    var treeController = TreeController.getController();

    processTreeSearch();

    $("form#tree_search").submit(function(e) {
        e.preventDefault();
        processTreeSearch();
    });

    function processTreeSearch () {
        // Clear the tree
        if (typeof treeController.recipient != 'undefined' && treeController.recipient.length > 0)
            treeController.recipient.empty();

        showSpinner();
        //$(".loading_tree").show();

        var parameters = {};
        parameters['page'] = "include/ajax/tree.ajax";
        parameters['getChildren'] = 1;
        parameters['type'] = "<?php echo $tab; ?>";
        parameters['filter'] = {};
        parameters['filter']['searchGroup'] = $("input#text-search_group").val();
        parameters['filter']['searchAgent'] = $("input#text-search_agent").val();
        parameters['filter']['statusAgent'] = $("select#status_agent").val();
        parameters['filter']['searchModule'] = $("input#text-search_module").val();
        parameters['filter']['statusModule'] = $("select#status_module").val();
        parameters['filter']['groupID'] = $("input#hidden-group-id").val();
        parameters['filter']['tagID'] = $("input#hidden-tag-id").val();

        if($("#checkbox-serach_hirearchy").is(':checked')){
            parameters['filter']['searchHirearchy'] = 1;
        }
        else{
            parameters['filter']['searchHirearchy'] = 0;
        }

        if($("#checkbox-show_not_init_agents").is(':checked')){
            parameters['filter']['show_not_init_agents'] = 1;
        }
        else{
            parameters['filter']['show_not_init_agents'] = 0;
        }

        if($("#checkbox-show_not_init_modules").is(':checked')){
            parameters['filter']['show_not_init_modules'] = 1;
            $('#hidden-show_not_init_modules_hidden').val(1);
        }
        else{
            parameters['filter']['show_not_init_modules'] = 0;
            $('#hidden-show_not_init_modules_hidden').val(0);
        }

        if($("#checkbox-show_disabled").is(':checked')){
            parameters['filter']['show_disabled'] = 1;
        }
        else{
            parameters['filter']['show_disabled'] = 0;
        }

        $.ajax({
            type: "POST",
            url: "<?php echo ui_get_full_url('ajax.php', false, false, false); ?>",
            data: parameters,
            success: function(data) {
                if (data.success) {
                    hideSpinner();
                    //$(".loading_tree").hide();
                    var foundMessage = '';
                    if (data.tree.length === 0) {
                        foundMessage = "<?php echo __('No data found'); ?>";
                        $("div#tree-controller-recipient").append(foundMessage);
                    } else {
                        switch (parameters['type']) {
                            case 'policies':
                                foundMessage = "<?php echo __('Policies found'); ?>";
                                break;
                            case 'os':
                                foundMessage = "<?php echo __('Operating systems found'); ?>";
                                break;
                            case 'tag':
                                foundMessage = "<?php echo __('Tags found'); ?>";
                                break;
                            case 'module_group':
                                foundMessage = "<?php echo __('Module Groups found'); ?>";
                                break;
                            case 'module':
                                foundMessage = "<?php echo __('Modules found'); ?>";
                                break;
                            case 'group':
                            default:
                                foundMessage = "<?php echo __('Groups found'); ?>";
                                break;
                        }
                    }

                    treeController.init({
                        recipient: $("div#tree-controller-recipient"),
                        detailRecipient: $.fixedBottomBox({ width: 400, height: window.innerHeight * 0.9, headTitle: "<?php echo $infoHeadTitle; ?>" }),
                        page: parameters['page'],
                        emptyMessage: "<?php echo __('No data found'); ?>",
                        foundMessage: foundMessage,
                        tree: data.tree,
                        baseURL: "<?php echo ui_get_full_url(false, false, false, false); ?>",
                        ajaxURL: "<?php echo ui_get_full_url('ajax.php', false, false, false); ?>",
                        filter: parameters['filter'],
                        counterTitles: {
                            total: {
                                agents: "<?php echo __('Total agents'); ?>",
                                modules: "<?php echo __('Total modules'); ?>",
                                none: "<?php echo __('Total'); ?>"
                            },
                            alerts: {
                                agents: "<?php echo __('Fired alerts'); ?>",
                                modules: "<?php echo __('Fired alerts'); ?>",
                                none: "<?php echo __('Fired alerts'); ?>"
                            },
                            critical: {
                                agents: "<?php echo __('Critical agents'); ?>",
                                modules: "<?php echo __('Critical modules'); ?>",
                                none: "<?php echo __('Critical'); ?>"
                            },
                            warning: {
                                agents: "<?php echo __('Warning agents'); ?>",
                                modules: "<?php echo __('Warning modules'); ?>",
                                none: "<?php echo __('Warning'); ?>"
                            },
                            unknown: {
                                agents: "<?php echo __('Unknown agents'); ?>",
                                modules: "<?php echo __('Unknown modules'); ?>",
                                none: "<?php echo __('Unknown'); ?>"
                            },
                            not_init: {
                                agents: "<?php echo __('Not init agents'); ?>",
                                modules: "<?php echo __('Not init modules'); ?>",
                                none: "<?php echo __('Not init'); ?>"
                            },
                            ok: {
                                agents: "<?php echo __('Normal agents'); ?>",
                                modules: "<?php echo __('Normal modules'); ?>",
                                none: "<?php echo __('Normal'); ?>"
                            },
                            not_normal: {
                                agents: "<?php echo __('Not normal agents'); ?>",
                                modules: "<?php echo __('Not normal modules'); ?>",
                                none: "<?php echo __('Not normal'); ?>"
                            },
                        }
                    });
                }
            },
            dataType: "json"
        });
    }
    
    // Show the modal window of an module
    var moduleDetailsWindow = $("<div></div>");
    moduleDetailsWindow
        .hide()
        .prop("id", "module_details_window")
        .appendTo('body');
    function show_module_detail_dialog(module_id, id_agent, server_name, offset, period, module_name) {
        var params = {};
        var f = new Date();
        period = $('#period').val();
        
        params.selection_mode = $('input[name=selection_mode]:checked').val();
        if (!params.selection_mode) {
            params.selection_mode='fromnow';
        }
        
        params.date_from = $('#text-date_from').val();
        if (!params.date_from) {
            params.date_from = f.getFullYear() + "/" + (f.getMonth() +1) + "/" + f.getDate();
        }
        
        params.time_from = $('#text-time_from').val();
        if (!params.time_from) {
            params.time_from = f.getHours() + ":"  + f.getMinutes();
        }
        
        params.date_to = $('#text-date_to').val();
        if (!params.date_to) {
            params.date_to =f.getFullYear() + "/" + (f.getMonth() +1) + "/" + f.getDate();
        }
        
        params.time_to = $('#text-time_to').val();
        if (!params.time_to) {
            params.time_to = f.getHours() + ":"  + f.getMinutes();
        }
        
        params.page = "include/ajax/module";
        params.get_module_detail = 1;
        params.server_name = server_name;
        params.id_agent = id_agent;
        params.id_module = module_id;
        params.offset = offset;
        params.period = period;
        title =   <?php echo "'".__('Module: ')."'"; ?> ;
        $.ajax({
            type: "POST",
            url: "<?php echo ui_get_full_url('ajax.php', false, false, false); ?>",
            data: params,
            dataType: "html",
            success: function(data) {
                $("#module_details_window").hide ()
                    .empty ()
                    .append (data)
                    .dialog ({
                        resizable: true,
                        draggable: true,
                        modal: true,
                        title: title + module_name,
                        overlay: {
                            opacity: 0.5,
                            background: "black"
                        },
                        width: "auto",
                        height: 500
                    }).css({"min-width": "650px"})
                    .show ();
                    refresh_pagination_callback(module_id, id_agent, server_name, module_name);
                    datetime_picker_callback();
                    forced_title_callback();
            }
        });
    }
    
    function datetime_picker_callback() {
        $("#text-time_from, #text-time_to").timepicker({
            showSecond: true,
            timeFormat: '<?php echo TIME_FORMAT_JS; ?>',
            timeOnlyTitle: '<?php echo __('Choose time'); ?>',
            timeText: '<?php echo __('Time'); ?>',
            hourText: '<?php echo __('Hour'); ?>',
            minuteText: '<?php echo __('Minute'); ?>',
            secondText: '<?php echo __('Second'); ?>',
            currentText: '<?php echo __('Now'); ?>',
            closeText: '<?php echo __('Close'); ?>'});
        
        $.datepicker.setDefaults($.datepicker.regional[ "<?php echo get_user_language(); ?>"]);
        $("#text-date_from, #text-date_to").datepicker({dateFormat: "<?php echo DATE_FORMAT_JS; ?>"});
        
    }
    
    function refresh_pagination_callback (module_id, id_agent, server_name,module_name) {
        
        $(".binary_dialog").click( function() {
            
            var classes = $(this).attr('class');
            classes = classes.split(' ');
            var offset_class = classes[2];
            offset_class = offset_class.split('_');
            var offset = offset_class[1];
            
            var period = $('#period').val();
            
            show_module_detail_dialog(module_id, id_agent, server_name, offset, period,module_name);
            return false;
        });
    }
    
</script>
