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

class ModuleGraph
{

    private $correct_acl = false;

    private $acl = 'AR';

    private $id = 0;

    private $id_agent = 0;

    private $graph_type = 'sparse';

    private $period = SECONDS_1DAY;

    private $draw_events = 0;

    private $width = 0;

    private $height = 0;

    private $draw_alerts = 0;

    private $start_date = 0;

    private $time_compare_separated = 0;

    private $time_compare_overlapped = 0;

    private $unknown_graph = 1;

    private $zoom = 1;

    private $baseline = 0;

    private $module = null;

    private $server_id = '';


    function __construct()
    {
        $system = System::getInstance();

        $this->start_date = date('Y-m-d');

        if ($system->checkACL($this->acl)) {
            $this->correct_acl = true;
        } else {
            $this->correct_acl = false;
        }
    }


    private function getFilters()
    {
        $system = System::getInstance();

        $this->id = (int) $system->getRequest('id', 0);
        $this->id_agent = (int) $system->getRequest('id_agent', 0);
        $this->server_id = $system->getRequest('server_id', '');

        $this->module = modules_get_agentmodule($this->id);
        $this->graph_type = return_graphtype($this->module['id_tipo_modulo']);

        $period_hours = $system->getRequest('period_hours', false);

        if ($period_hours == false) {
            $this->period = SECONDS_1DAY;
        } else {
            $this->period = ($period_hours * SECONDS_1HOUR);
        }

        $this->draw_events = (int) $system->getRequest('draw_events', 0);
        $this->draw_alerts = (int) $system->getRequest('draw_alerts', 0);
        $this->start_date = $system->getRequest('start_date', false);
        if ($this->start_date === false) {
            $this->start_date = date('Y-m-d');
        } else {
            $this->start_date = date('Y-m-d', strtotime($this->start_date));
        }

        $this->time_compare_separated = (int) $system->getRequest('time_compare_separated', 0);
        $this->time_compare_overlapped = (int) $system->getRequest('time_compare_overlapped', 0);
        $this->unknown_graph = (int) $system->getRequest('unknown_graph', 0);
        $this->zoom = (int) $system->getRequest('zoom', 1);
        $this->baseline = (int) $system->getRequest('baseline', 0);

        $this->width = (int) $system->getRequest('width', 0);
        $this->width -= 20;
        // Correct the width
        $this->height = (int) $system->getRequest('height', 0);

        // Sancho says "put the height to 1/2 for to make more beautyful"
        $this->height = ($this->height / 1.5);

        $this->height -= 80;
        // Correct the height
        // For to avoid IPHONES when they are in horizontal.
        if ($this->height < 140) {
            $this->height = 140;
        }

    }


    public function ajax($parameter2=false)
    {
        global $config;

        $system = System::getInstance();

        if (!$this->correct_acl) {
            return;
        } else {
            switch ($parameter2) {
                case 'get_graph':
                    $this->getFilters();

                    $correct = 0;
                    $graph = '';
                    $correct = 1;
                    $label = $this->module['nombre'];
                    $unit = db_get_value(
                        'unit',
                        'tagente_modulo',
                        'id_agente_modulo',
                        $this->id
                    );
                    $utime = get_system_time();
                    $current = date('Y-m-d', $utime);

                    if ($this->start_date != $current) {
                        $date = strtotime($this->start_date);
                    } else {
                        $date = $utime;
                    }

                    $urlImage = ui_get_full_url(false);

                    $time_compare = false;
                    if ($this->time_compare_separated) {
                        $time_compare = 'separated';
                    } else if ($this->time_compare_overlapped) {
                        $time_compare = 'overlapped';
                    }

                    // Graph TIP view.
                    if (!isset($config['full_scale_option']) || $config['full_scale_option'] == 0) {
                        $fullscale = 0;
                    } else if ($config['full_scale_option'] == 1) {
                        $fullscale = 1;
                    } else if ($config['full_scale_option'] == 2) {
                        if ($this->graph_type == 'boolean') {
                            $fullscale = 1;
                        } else {
                            $fullscale = 0;
                        }
                    }

                    ob_start();
                    switch ($this->graph_type) {
                        case 'boolean':
                        case 'sparse':
                        case 'string':
                            $params = [
                                'agent_module_id' => $this->id,
                                'period'          => $this->period,
                                'show_events'     => $this->draw_events,
                                'width'           => $this->width,
                                'height'          => $this->height,
                                'show_alerts'     => $this->draw_alerts,
                                'date'            => $date,
                                'unit'            => $unit,
                                'baseline'        => $this->baseline,
                                'homeurl'         => $urlImage,
                                'adapt_key'       => 'adapter_'.$this->graph_type,
                                'compare'         => $time_compare,
                                'show_unknown'    => $this->unknown_graph,
                                'menu'            => false,
                                'type_graph'      => $config['type_module_charts'],
                                'vconsole'        => true,
                                'fullscale'       => $fullscale,
                            ];

                            $graph = grafico_modulo_sparse($params);
                            if ($this->draw_events) {
                                $this->width = 100;
                                $graph .= '<br>';
                                $graph .= graphic_module_events(
                                    $this->id,
                                    $this->width,
                                    $this->height,
                                    $this->period,
                                    $config['homeurl'],
                                    $this->zoom,
                                    'adapted_'.$this->graph_type,
                                    $date
                                );
                            }
                        break;

                        default:
                            $graph .= fs_error_image('../images');
                        break;
                    }

                    $graph = ob_get_clean().$graph;

                    echo json_encode(['correct' => $correct, 'graph' => $graph]);
                break;
            }
        }
    }


    public function show()
    {
        if (!$this->correct_acl) {
            $this->show_fail_acl();
        } else {
            $this->getFilters();
            $this->showModuleGraph();
        }
    }


    private function show_fail_acl()
    {
        $error['type'] = 'onStart';
        $error['title_text'] = __('You don\'t have access to this page');
        $error['content_text'] = System::getDefaultACLFailText();
        $home = new Home();

        $home->show($error);
    }


    private function javascript_code()
    {
        ob_start();

        global $config;

        ?>
        <script type="text/javascript">
            $(document).ready(function() {
                function load_graph() {
                    $("#loading_graph").show();
                    var heigth = $(document).width() / 2;
                    var width = $(document).width();
                    ajax_get_graph($("#id_module").val(), heigth, width, $("#server_id").val());
                }

                load_graph();

                // Detect orientation change to refresh dinamic content
                window.addEventListener("resize", function() {
                    // Reload dinamic content
                    load_graph();
                });
            });

            function ajax_get_graph(id, heigth_graph, width_graph, server_id) {
                postvars = {};
                postvars["action"] = "ajax";
                postvars["parameter1"] = "module_graph";
                postvars["parameter2"] = "get_graph";
                postvars["width"] = width_graph;
                postvars["height"] = heigth_graph;

                postvars["draw_alerts"] = ($("input[name = 'draw_alerts']").is(":checked"))?1:0;
                postvars["draw_events"] = ($("input[name = 'draw_events']").is(":checked"))?1:0;
                postvars["time_compare_separated"] = ($("input[name = 'time_compare_separated']").is(":checked"))?1:0;
                postvars["time_compare_overlapped"] = ($("input[name = 'time_compare_overlapped']").is(":checked"))?1:0;
                postvars["unknown_graph"] = ($("input[name = 'unknown_graph']").is(":checked"))?1:0;;

                postvars["period_hours"] = $("input[name = 'period_hours']").val();
                postvars["zoom"] = $("input[name = 'zoom']").val();
                postvars["start_date"] = $("input[name = 'start_date']").val();

                postvars["id"] = id;

                postvars["server_id"] = server_id;

                $.ajax ({
                    type: "POST",
                    url: "index.php",
                    dataType: "json",
                    data: postvars,
                    success:
                    function (data) {
                        $("#loading_graph").hide();
                        if (data.correct) {
                            $("#graph_content").show();
                            $("#graph_content").html(data.graph);
                        }
                        else {
                            $("#error_graph").show();
                        }
                    },
                    error:
                    function (jqXHR, textStatus, errorThrown) {
                        $("#loading_graph").hide();
                        $("#error_graph").show();
                    }
                    });
            }
        </script>
        <?php
        $javascript_code = ob_get_clean();

        return $javascript_code;
    }


    private function showModuleGraph()
    {
        $agent_alias = agents_get_alias($this->module['id_agente']);

        $ui = Ui::getInstance();

        $ui->createPage();

        if ($this->id_agent) {
            $ui->createDefaultHeader(
                sprintf(__('%s: %s'), get_product_name(), $this->module['nombre']),
                $ui->createHeaderButton(
                    [
                        'icon'  => 'ui-icon-back',
                        'pos'   => 'left',
                        'text'  => __('Back'),
                        'href'  => 'index.php?page=agent&id='.$this->id_agent,
                        'class' => 'header-button-left',
                    ]
                )
            );
        } else {
            $ui->createDefaultHeader(
                sprintf(__('%s: %s'), get_product_name(), $this->module['nombre']),
                $ui->createHeaderButton(
                    [
                        'icon'  => 'ui-icon-back',
                        'pos'   => 'left',
                        'text'  => __('Back'),
                        'href'  => 'index.php?page=modules',
                        'class' => 'header-button-left',
                    ]
                )
            );
        }

        $ui->showFooter(false);
        $ui->beginContent();
            $ui->contentAddHtml(
                $ui->getInput(
                    [
                        'id'    => 'id_module',
                        'value' => $this->id,
                        'type'  => 'hidden',
                    ]
                )
            );
            $ui->contentAddHtml(
                $ui->getInput(
                    [
                        'id'    => 'server_id',
                        'value' => $this->server_id,
                        'type'  => 'hidden',
                    ]
                )
            );
            $title = sprintf(__('Options for %s : %s'), $agent_alias, $this->module['nombre']);
            $ui->contentBeginCollapsible($title, 'filter-collapsible');
                $ui->beginForm('index.php?page=module_graph&id='.$this->id.'&server_id='.$this->server_id);
                    $options = [
                        'name'    => 'draw_alerts',
                        'value'   => 1,
                        'checked' => (bool) $this->draw_alerts,
                        'label'   => __('Show Alerts'),
                    ];
                    $ui->formAddCheckbox($options);

                    $options = [
                        'name'    => 'draw_events',
                        'value'   => 1,
                        'checked' => (bool) $this->draw_events,
                        'label'   => __('Show Events'),
                    ];
                    $ui->formAddCheckbox($options);

                    $options = [
                        'name'    => 'time_compare_separated',
                        'value'   => 1,
                        'checked' => (bool) $this->time_compare_separated,
                        'label'   => __('Time compare (Separated)'),
                    ];
                    $ui->formAddCheckbox($options);

                    $options = [
                        'name'    => 'time_compare_overlapped',
                        'value'   => 1,
                        'checked' => (bool) $this->time_compare_overlapped,
                        'label'   => __('Time compare (Overlapped)'),
                    ];
                    $ui->formAddCheckbox($options);

                    $options = [
                        'name'    => 'unknown_graph',
                        'value'   => 1,
                        'checked' => (bool) $this->unknown_graph,
                        'label'   => __('Show unknown graph'),
                    ];
                    $ui->formAddCheckbox($options);

                    $options = [
                        'label' => __('Time range (hours)'),
                        'name'  => 'period_hours',
                        'value' => ($this->period / SECONDS_1HOUR),
                        'min'   => 0,
                        'max'   => (24 * 30),
                        'step'  => 4,
                    ];
                    $ui->formAddSlider($options);

                    $options = [
                        'name'  => 'start_date',
                        'value' => $this->start_date,
                        'label' => __('Begin date'),
                    ];
                    $ui->formAddInpuDate($options);

                    $options = [
                        'icon'     => 'refresh',
                        'icon_pos' => 'right',
                        'text'     => __('Update graph'),
                    ];
                    $ui->formAddSubmitButton($options);

                    $html = $ui->getEndForm();
                    $ui->contentCollapsibleAddItem($html);
                    $ui->contentEndCollapsible();
                    $ui->contentAddHtml(
                        '<div id="graph_content" class="invisible w100p height_100p center"></div>
				<div id="loading_graph" class="w100p center">'.__('Loading...').'<br /><img src="images/ajax-loader.gif" /></div>
				<div id="error_graph" class="invisible red w100p  center">'.__('Error get the graph').'</div>'
                    );
            $ui->contentAddHtml($this->javascript_code());
        $ui->endContent();
        $ui->showPage();
    }


}
