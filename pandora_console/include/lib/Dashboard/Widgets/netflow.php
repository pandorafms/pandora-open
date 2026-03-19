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
namespace PandoraFMS\Dashboard;

/**
 * Netflow.
 */
class Netflow extends Widget
{

    /**
     * Name widget.
     *
     * @var string
     */
    protected $name;

    /**
     * Title widget.
     *
     * @var string
     */
    protected $title;

    /**
     * Page widget;
     *
     * @var string
     */
    protected $page;

    /**
     * Class name widget.
     *
     * @var [type]
     */
    protected $className;

    /**
     * Values options for each widget.
     *
     * @var [type]
     */
    protected $values;

    /**
     * Configuration required.
     *
     * @var boolean
     */
    protected $configurationRequired;

    /**
     * Error load widget.
     *
     * @var boolean
     */
    protected $loadError;

    /**
     * Width.
     *
     * @var integer
     */
    protected $width;

    /**
     * Heigth.
     *
     * @var integer
     */
    protected $height;

    /**
     * Grid Width.
     *
     * @var integer
     */
    protected $gridWidth;


    /**
     * Construct.
     *
     * @param integer      $cellId      Cell ID.
     * @param integer      $dashboardId Dashboard ID.
     * @param integer      $widgetId    Widget ID.
     * @param integer|null $width       New width.
     * @param integer|null $height      New height.
     * @param integer|null $gridWidth   Grid width.
     */
    public function __construct(
        int $cellId,
        int $dashboardId=0,
        int $widgetId=0,
        ?int $width=0,
        ?int $height=0,
        ?int $gridWidth=0
    ) {
        global $config;

        // Includes.
        include_once $config['homedir'].'/include/functions_graph.php';
        include_once $config['homedir'].'/include/class/NetworkMap.class.php';
        // WARNING: Do not edit. This chunk must be in the constructor.
        parent::__construct(
            $cellId,
            $dashboardId,
            $widgetId
        );

        // Width.
        $this->width = $width;

        // Height.
        $this->height = $height;

        // Grid Width.
        $this->gridWidth = $gridWidth;

        // Options.
        $this->values = $this->getOptionsWidget();

        // Positions.
        $this->position = $this->getPositionWidget();

        // Page.
        $this->page = basename(__FILE__);

        // ClassName.
        $class = new \ReflectionClass($this);
        $this->className = $class->getShortName();

        // Title.
        $this->title = __('Netflow');

        // Name.
        if (empty($this->name) === true) {
            $this->name = 'netflow';
        }
    }


    /**
     * Generates inputs for form (specific).
     *
     * @return array Of inputs.
     *
     * @throws Exception On error.
     */
    public function getFormInputs(): array
    {
        $values = $this->values;

        // Retrieve global - common inputs.
        $inputs = parent::getFormInputs();

        // Default values.
        if (isset($values['quantity']) === false) {
            $values['quantity'] = 5;
        }

        // Default values.
        if (isset($values['period']) === false) {
            $values['period'] = SECONDS_1WEEK;
        }

        // Default values.
        if (isset($values['max_values']) === false) {
            $values['max_values'] = 10;
        }

        // Period.
        $inputs[] = [
            'label'     => __('Interval'),
            'arguments' => [
                'name'          => 'period',
                'type'          => 'interval',
                'value'         => $values['period'],
                'nothing'       => __('None'),
                'nothing_value' => 0,
                'style_icon'    => 'flex-grow: 0',
                'script'        => 'check_period_warning(this, \''.__('Warning').'\', \''.__('Displaying items with extended historical data can have an impact on system performance. We do not recommend that you use intervals longer than 30 days, especially if you combine several of them in a report, dashboard or visual console.').'\')',
                'script_input'  => 'check_period_warning_manual(\'period\', \''.__('Warning').'\', \''.__('Displaying items with extended historical data can have an impact on system performance. We do not recommend that you use intervals longer than 30 days, especially if you combine several of them in a report, dashboard or visual console.').'\')',
                'units_select2' => true,
            ],
        ];
        $chart_types = netflow_get_chart_types();
        $chart_types['usage_map'] = __('Usage map');
        $inputs[] = [
            'label'     => __('Type graph'),
            'arguments' => [
                'name'     => 'chart_type',
                'type'     => 'select',
                'fields'   => $chart_types,
                'selected' => $values['chart_type'],
            ],
        ];

        $aggregate_list = [
            'srcip'   => __('Src Ip Address'),
            'dstip'   => __('Dst Ip Address'),
            'srcport' => __('Src Port'),
            'dstport' => __('Dst Port'),
        ];
        $inputs[] = [
            'label'     => __('Aggregated by'),
            'id'        => 'aggregated',
            'arguments' => [
                'name'     => 'aggregate',
                'type'     => 'select',
                'fields'   => $aggregate_list,
                'selected' => $values['aggregate'],
            ],
        ];

        $inputs[] = [
            'label'     => __('Data to show'),
            'id'        => 'data_to_show',
            'arguments' => [
                'name'     => 'action',
                'type'     => 'select',
                'fields'   => network_get_report_actions(),
                'selected' => $values['action'],
            ],
        ];

        $max_values = [
            '2'  => '2',
            '5'  => '5',
            '10' => '10',
            '15' => '15',
            '20' => '20',
            '25' => '25',
            '50' => '50',
        ];

        $inputs[] = [
            'label'     => __('Max values'),
            'arguments' => [
                'name'     => 'max_values',
                'type'     => 'select',
                'fields'   => $max_values,
                'selected' => $values['max_values'],
            ],
        ];

        return $inputs;
    }


    /**
     * Get Post for widget.
     *
     * @return array
     */
    public function getPost():array
    {
        // Retrieve global - common inputs.
        $values = parent::getPost();

        $values['period'] = \get_parameter('period', 0);
        $values['chart_type'] = \get_parameter('chart_type', '');
        $values['aggregate'] = \get_parameter('aggregate');
        $values['max_values'] = \get_parameter('max_values', 10);
        $values['action'] = \get_parameter('action', 'srcip');

        return $values;
    }


    /**
     * Draw widget.
     *
     * @return string
     */
    public function load()
    {
        ui_require_css_file('netflow_widget', 'include/styles/', true);
        global $config;

        $output = '';

        $size = parent::getSize();

        $start_date = (time() - $this->values['period']);
        $end_date = time();

        if (empty(parent::getPeriod()) === false) {
            $start_date = parent::getDateFrom();
            $end_date = parent::getDateTo();
        }

        if ($this->values['chart_type'] === 'usage_map') {
            $map_data = netflow_build_map_data(
                $start_date,
                $end_date,
                $this->values['max_values'],
                ($this->values['action'] === 'talkers') ? 'srcip' : 'dstip'
            );
            $has_data = !empty($map_data['nodes']);

            if ($has_data === true) {
                $map_manager = new \NetworkMap($map_data);
                $map_manager->printMap();
            } else {
                ui_print_info_message(__('No data to show'));
            }
        } else {
            $netflowContainerClass = ($this->values['chart_type'] === 'netflow_data' || $this->values['chart_type'] === 'netflow_summary' || $this->values['chart_type'] === 'netflow_top_N') ? '' : 'white_box';
            $filter = [
                'aggregate'                   => $this->values['aggregate'],
                'netflow_monitoring_interval' => 300,
            ];

            $output .= html_print_input_hidden(
                'selected_style_theme',
                $config['style'],
                true
            );
            $style = 'width:100%; height: 100%; border: none;';
            if ($this->values['chart_type'] !== 'netflow_area') {
                $style .= ' width: 95%;';
            }

            if ($size['width'] > $size['height']) {
                $size['width'] = $size['height'];
            }

            // Draw the netflow chart.
            $output .= html_print_div(
                [
                    'class'   => $netflowContainerClass,
                    'style'   => $style,
                    'content' => netflow_draw_item(
                        $start_date,
                        $end_date,
                        12,
                        $this->values['chart_type'],
                        $filter,
                        $this->values['max_values'],
                        '',
                        'HTML',
                        0,
                        ($size['width'] + 120),
                        ($size['height'] + 120),
                    ),
                ],
                true
            );
        }

        return $output;

    }


    /**
     * Return aux javascript code for forms.
     *
     * @return string
     */
    public function getFormJS()
    {
        return '
            $(document).ready(function(){
                if($("#chart_type").val() === "usage_map") {
                    $("#data_to_show").show();
                    $("#aggregated").hide();
                } else {
                    $("#data_to_show").hide();
                    $("#aggregated").show();
                }
                $("#chart_type").on("change", function(e){
                    if(this.value === "usage_map") {
                        $("#data_to_show").show();
                        $("#aggregated").hide();
                    } else {
                        $("#data_to_show").hide();
                        $("#aggregated").show();
                    }
                });
            });
        ';
    }


    /**
     * Get description.
     *
     * @return string.
     */
    public static function getDescription()
    {
        return __('Netflow');
    }


    /**
     * Get Name.
     *
     * @return string.
     */
    public static function getName()
    {
        return 'netflow';
    }


    /**
     * Get size Modal Configuration.
     *
     * @return array
     */
    public function getSizeModalConfiguration(): array
    {
        $size = [
            'width'  => 600,
            'height' => 530,
        ];

        return $size;
    }


}
