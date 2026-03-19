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
 * Group status Widgets.
 */
class GroupsStatusWidget extends Widget
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
        $this->values = $this->decoders($this->getOptionsWidget());

        // Positions.
        $this->position = $this->getPositionWidget();

        // Page.
        $this->page = basename(__FILE__);

        // ClassName.
        $class = new \ReflectionClass($this);
        $this->className = $class->getShortName();

        // Title.
        $this->title = __('General group status');

        // Name.
        if (empty($this->name) === true) {
            $this->name = 'groups_status';
        }

        // This forces at least a first configuration.
        $this->configurationRequired = false;
        if (empty($this->values['groupId']) === true) {
            $this->configurationRequired = true;
        } else {
            $check_exist = \db_get_value(
                'id_grupo',
                'tgrupo',
                'id_grupo',
                $this->values['groupId']
            );

            if ($check_exist === false) {
                $this->loadError = true;
            }
        }

        $this->overflow_scrollbars = false;
    }


    /**
     * Decoders hack for retrocompability.
     *
     * @param array $decoder Values.
     *
     * @return array Returns the values ​​with the correct key.
     */
    public function decoders(array $decoder): array
    {
        $values = [];
        // Retrieve global - common inputs.
        $values = parent::decoders($decoder);

        if (isset($decoder['groups']) === true) {
            $values['groupId'] = $decoder['groups'];
        }

        if (isset($decoder['groupId']) === true) {
            $values['groupId'] = $decoder['groupId'];
        }

        if (isset($decoder['groupRecursion']) === true) {
            $values['groupRecursion'] = $decoder['groupRecursion'];
        }

        return $values;
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

        // Restrict access to group.
        $inputs[] = [
            'label'     => __('Groups'),
            'arguments' => [
                'type'           => 'select_groups',
                'name'           => 'groupId',
                'returnAllGroup' => false,
                'privilege'      => 'AR',
                'selected'       => $values['groupId'],
                'return'         => true,
            ],
        ];

        $inputs[] = [
            'label'     => __('Group recursion'),
            'arguments' => [
                'name'  => 'groupRecursion',
                'id'    => 'groupRecursion',
                'type'  => 'switch',
                'value' => $values['groupRecursion'],
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

        $values['groupId'] = \get_parameter('groupId', 0);
        $values['groupRecursion'] = \get_parameter_switch('groupRecursion', 0);

        return $values;
    }


    /**
     * Draw widget.
     *
     * @return string;
     */
    public function load()
    {
        global $config;

        $size = parent::getSize();

        include_once $config['homedir'].'/include/functions_reporting.php';
        include_once $config['homedir'].'/include/functions_graph.php';

        $output = '';

        $stats = \reporting_get_group_stats_resume(
            $this->values['groupId'],
            'AR',
            true,
            (bool) $this->values['groupRecursion']
        );

        $style = 'min-width:200px; min-height:460px;';

        $data = '<div class="widget-groups-status"><span>';
        $data .= ui_print_group_icon(
            $this->values['groupId'],
            true,
            'groups_small',
            'width:50px; padding-right: 10px'
        );
        $data .= '</span>';

            $url = $config['homeurl'];
            $url .= 'index.php?sec=estado&sec2=operation/agentes/estado_agente';
            $url .= '&refr=60&group_id='.$this->values['groupId'];
        

        $data .= '<h1>';
        $data .= '<a href="'.$url.'">';
        $data .= groups_get_name($this->values['groupId']);
        $data .= '</a>';
        $data .= '</h1></div>';

        $data .= '<div class="div_groups_status both">';

        $table = new \stdClass();
        $table->class = 'widget_groups_status';
        $table->cellspacing = '0';
        $table->width = '100%';
        $table->data = [];
        $table->size = [];
        $table->colspan = [];
        $table->cellstyle = [];

        $table->size[0] = '50%';
        $table->size[1] = '50%';

        $style  = 'border-bottom:1px solid #ECECEC; text-align: center;';
        $table->cellstyle[0][0] = $style;
        $table->cellstyle[0][1] = $style;
        $table->cellstyle[1][0] = 'padding-top: 10px;';
        $table->cellstyle[1][1] = 'padding-top: 10px;';

        // Head  agents.
        $table->data[0][0] = '<span>';

        $table->data[0][0] .= html_print_image(
            'images/agent.png',
            true,
            [
                'alt'   => __('Agents'),
                'class' => 'invert_filter',
            ]
        );
        $table->data[0][0] .= ' <b>';
        $table->data[0][0] .= __('Agents');
        $table->data[0][0] .= '</b>';
        $table->data[0][0] .= '</span>';
        $table->data[0][1] = '<span>';
        $table->data[0][1] .= '<b>';
        $table->data[0][1] .= $stats['total_agents'];
        $table->data[0][1] .= '</b>';
        $table->data[0][1] .= '</span>';

        if ($stats['total_agents'] !== 0) {

                $agentdetail_url = $url.'&status=';
            

            // Agent Critical.
            $agent_url = '';
            $agent_url .= $agentdetail_url.'1';
            $agent_data = '<a href="'.$agent_url.'">';
            $agent_data .= $this->getCellCounter(
                $stats['agent_critical'],
                '',
                'bg_ff5'
            );
            $agent_data .= '</a>';

            $table->data[1][0] = $agent_data;

            // Agent Warning.
            $agent_url = '';
            $agent_url .= $agentdetail_url.'2';
            $agent_data = '<a href="'.$agent_url.'">';
            $agent_data .= $this->getCellCounter(
                $stats['agent_warning'],
                '',
                'bg_ffd'
            );
            $agent_data .= '</a>';
            $table->data[2][0] = $agent_data;

            // Agent OK.
            $agent_url = '';
            $agent_url .= $agentdetail_url.'0';
            $agent_data = '<a href="'.$agent_url.'">';
            $agent_data .= $this->getCellCounter(
                $stats['agent_ok'],
                '',
                'bg_82B92E'
            );
            $agent_data .= '</a>';
            $table->data[3][0] = $agent_data;

            // Agent Unknown.
            $agent_url = '';
            $agent_url .= $agentdetail_url.'3';
            $agent_data = '<a href="'.$agent_url.'">';
            $agent_data .= $this->getCellCounter(
                $stats['agent_unknown'],
                '#B2B2B2'
            );
            $agent_data .= '</a>';
            $table->data[1][1] = $agent_data;

            // Agent Not Init.
            $agent_url = '';
            $agent_url .= $agentdetail_url.'5';
            $agent_data = '<a href="'.$agent_url.'">';
            $agent_data .= $this->getCellCounter(
                $stats['agent_not_init'],
                '#4a83f3'
            );
            $agent_data .= '</a>';
            $table->data[2][1] = $agent_data;

            $data .= html_print_table($table, true);
            $data .= '</div>';

            $data .= '<div class="div_groups_status">';

            $table = new \stdClass();
            $table->class = 'widget_groups_status';
            $table->cellspacing = '0';
            $table->width = '100%';
            $table->data = [];
            $table->size = [];
            $table->colspan = [];
            $table->cellstyle = [];

            $table->size[0] = '50%';
            $table->size[1] = '50%';

            $style  = 'border-bottom:1px solid #ECECEC; text-align: center;';
            $table->cellstyle[0][0] = $style;
            $table->cellstyle[0][1] = $style;
            $table->cellstyle[1][0] = 'padding-top: 20px;';
            $table->cellstyle[1][1] = 'padding-top: 20px;';

            // Head  Modules.
            $table->data[0][0] = '<span>';
            $table->data[0][0] .= html_print_image(
                'images/module.png',
                true,
                [
                    'alt'   => __('Modules'),
                    'class' => 'invert_filter',
                ]
            );

            $table->data[0][0] .= '<b>';
            $table->data[0][0] .= __('Modules');
            $table->data[0][0] .= '</b>';
            $table->data[0][0] .= '</span>';
            $table->data[0][1] = '<span>';
            $table->data[0][1] .= '<b>';
            $table->data[0][1] .= $stats['total_checks'];
            $table->data[0][1] .= '</b>';
            $table->data[0][1] .= '</span>';

                $monitordetail_url = 'index.php?sec=view&sec2=operation/agentes/status_monitor&refr=0&ag_group='.$this->values['groupId'].'&status=';
            

            // Modules Critical.
            $module_url = '';
            $module_url .= $monitordetail_url.'1';
            $module_data = '<a href="'.$module_url.'">';
            $module_data .= $this->getCellCounter(
                $stats['monitor_critical'],
                '',
                'bg_ff5'
            );
            $module_data .= '</a>';
            $table->data[1][0] = $module_data;

            // Modules Warning.
            $module_url = '';
            $module_url .= $monitordetail_url.'2';
            $module_data = '<a href="'.$module_url.'">';
            $module_data .= $this->getCellCounter(
                $stats['monitor_warning'],
                '',
                'bg_ffd'
            );
            $module_data .= '</a>';
            $table->data[2][0] = $module_data;

            // Modules OK.
            $module_url = '';
            $module_url .= $monitordetail_url.'0';
            $module_data = '<a href="'.$module_url.'">';
            $module_data .= $this->getCellCounter(
                $stats['monitor_ok'],
                '',
                'bg_82B92E'
            );
            $module_data .= '</a>';
            $table->data[3][0] = $module_data;

            // Modules Unknown.
            $module_url = '';
            $module_url .= $monitordetail_url.'3';
            $module_data = '<a href="'.$module_url.'">';
            $module_data .= $this->getCellCounter(
                $stats['monitor_unknown'],
                '#B2B2B2'
            );
            $module_data .= '</a>';
            $table->data[1][1] = $module_data;

            // Modules Not Init.
            $module_url = '';
            $module_url .= $monitordetail_url.'5';
            $module_data = '<a href="'.$module_url.'">';
            $module_data .= $this->getCellCounter(
                $stats['monitor_not_init'],
                '#4a83f3'
            );
            $module_data .= '</a>';
            $table->data[2][1] = $module_data;

            $data .= html_print_table($table, true);
            $data .= '</div>';
        } else {
            // Not agents in this group.
            $table->colspan[1][0] = 2;
            $table->data[1][0] = __('Not agents in this group');
            $data .= html_print_table($table, true);
            $data .= '</div>';
            $style .= 'justify-content: start; margin-top: 20px';
        }

        $output = '<div class="container-center" style="'.$style.'">';
        $output .= $data;
        $output .= '</div>';

        return $output;
    }


    /**
     * Draw cell.
     *
     * @param integer|null $count Counter.
     * @param string       $color Background color cell.
     *
     * @return string
     */
    protected function getCellCounter(?int $count, string $color='', string $div_class=''):string
    {
        $output = '<div ';

        if ($div_class !== '') {
            $output .= 'class= "'.$div_class.'" ';
        }

        if ($color !== '') {
            $output .= 'style= "background-color:'.$color.'" ';
        }

        $output .= '>';

        if (isset($count) === true
            && $count !== 0
        ) {
            $output .= $count;
        } else {
            $output .= 0;
        }

        $output .= '</div>';
        return $output;
    }


    /**
     * Get description.
     *
     * @return string.
     */
    public static function getDescription()
    {
        return __('General group status');
    }


    /**
     * Get Name.
     *
     * @return string.
     */
    public static function getName()
    {
        return 'groups_status';
    }


    /**
     * Get size Modal Configuration.
     *
     * @return array
     */
    public function getSizeModalConfiguration(): array
    {
        $size = [
            'width'  => 400,
            'height' => 330,
        ];

        return $size;
    }


}
