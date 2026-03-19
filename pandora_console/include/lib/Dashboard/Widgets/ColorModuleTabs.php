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

global $config;

/**
 * URL Widgets
 */
class ColorModuleTabs extends Widget
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
     * Cell ID.
     *
     * @var integer
     */
    protected $cellId;


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

        // Cell Id.
        $this->cellId = $cellId;

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
        $this->title = __('Color tabs modules');

        // Name.
        if (empty($this->name) === true) {
            $this->name = 'single_graph';
        }

        // This forces at least a first configuration.
        $this->configurationRequired = false;
        if (empty($this->values['moduleColorModuleTabs']) === true) {
            $this->configurationRequired = true;
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

        $values['agentsColorModuleTabs'] = [];
        if (isset($decoder['agentsColorModuleTabs']) === true) {
            if (isset($decoder['agentsColorModuleTabs'][0]) === true
                && empty($decoder['agentsColorModuleTabs']) === false
            ) {
                $values['agentsColorModuleTabs'] = explode(
                    ',',
                    $decoder['agentsColorModuleTabs'][0]
                );
            }
        }

        if (isset($decoder['selectionColorModuleTabs']) === true) {
            $values['selectionColorModuleTabs'] = $decoder['selectionColorModuleTabs'];
        }

        $values['moduleColorModuleTabs'] = [];
        if (isset($decoder['moduleColorModuleTabs']) === true) {
            if (empty($decoder['moduleColorModuleTabs']) === false) {
                $values['moduleColorModuleTabs'] = $decoder['moduleColorModuleTabs'];
            }
        }

        if (isset($decoder['formatData']) === true) {
            $values['formatData'] = $decoder['formatData'];
        }

        $values['label'] = 'module';
        if (isset($decoder['label']) === true) {
            $values['label'] = $decoder['label'];
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

        // Type Label.
        $fields = [
            'module'       => __('Module'),
            'agent'        => __('Agent'),
            'agent_module' => __('Agent / module'),
        ];

        $inputs[] = [
            'label'     => __('Label'),
            'arguments' => [
                'type'     => 'select',
                'fields'   => $fields,
                'name'     => 'label',
                'selected' => $values['label'],
                'return'   => true,
            ],
        ];

        $inputs[] = [
            'arguments' => [
                'type'                   => 'select_multiple_modules_filtered_select2',
                'agent_values'           => agents_get_agents_selected(0),
                'agent_name'             => 'agentsColorModuleTabs[]',
                'agent_ids'              => $values['agentsColorModuleTabs'],
                'selectionModules'       => $values['selectionColorModuleTabs'],
                'selectionModulesNameId' => 'selectionColorModuleTabs',
                'modules_ids'            => $values['moduleColorModuleTabs'],
                'modules_name'           => 'moduleColorModuleTabs[]',
            ],
        ];

        // Format Data.
        $inputs[] = [
            'label'     => __('Format Data'),
            'arguments' => [
                'name'  => 'formatData',
                'id'    => 'formatData',
                'type'  => 'switch',
                'value' => $values['formatData'],
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

        $values['agentsColorModuleTabs'] = \get_parameter(
            'agentsColorModuleTabs',
            []
        );
        $values['selectionColorModuleTabs'] = \get_parameter(
            'selectionColorModuleTabs',
            0
        );

        $values['moduleColorModuleTabs'] = \get_parameter(
            'moduleColorModuleTabs'
        );

        $agColor = [];
        if (isset($values['agentsColorModuleTabs'][0]) === true
            && empty($values['agentsColorModuleTabs'][0]) === false
        ) {
            $agColor = explode(',', $values['agentsColorModuleTabs'][0]);
        }

        $agModule = [];
        if (isset($values['moduleColorModuleTabs'][0]) === true
            && empty($values['moduleColorModuleTabs'][0]) === false
        ) {
            $agModule = explode(',', $values['moduleColorModuleTabs'][0]);
        }

        $values['moduleColorModuleTabs'] = get_same_modules_all(
            $agColor,
            $agModule
        );

        $values['formatData'] = \get_parameter_switch('formatData', 0);

        $values['label'] = \get_parameter('label', 'module');

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

        include_once $config['homedir'].'/include/functions_graph.php';

        $size = parent::getSize();

        $output = '';

            $modules = $this->getInfoModules(
                $this->values['moduleColorModuleTabs']
            );
        

        if ($modules !== false && empty($modules) === false) {
            $output .= '<div class="container-tabs">';
            foreach ($modules as $module) {
                $output .= $this->drawTabs($module);
            }

            $output .= '</div>';
        } else {
            $output .= '<div class="container-center">';
            $output .= \ui_print_info_message(
                __('Not found modules'),
                '',
                true
            );
            $output .= '</div>';
        }

        return $output;
    }


    /**
     * Get info modules.
     *
     * @param array $modules Modules.
     *
     * @return array Data.
     */
    private function getInfoModules(array $modules): array
    {
        $where = sprintf(
            'tagente_modulo.id_agente_modulo IN (%s)
            AND tagente_modulo.delete_pending = 0',
            implode(',', $modules)
        );

        $sql = sprintf(
            'SELECT tagente_modulo.id_agente_modulo AS `id`,
                tagente_modulo.nombre AS `name`,
                tagente_modulo.unit AS `unit`,
                tagente_estado.datos AS `data`,
                tagente_estado.timestamp AS `timestamp`,
                tagente_estado.estado AS `status`,
                tagente.alias
            FROM tagente_modulo
            LEFT JOIN tagente_estado
                ON tagente_modulo.id_agente_modulo = tagente_estado.id_agente_modulo
            LEFT JOIN tagente
                ON tagente_modulo.id_agente = tagente.id_agente
            WHERE %s',
            $where
        );

        $modules = db_get_all_rows_sql($sql);

        if ($modules === false) {
            $modules = [];
        }

        return $modules;
    }


    /**
     * Draw tab module.
     *
     * @param array $data Info module.
     *
     * @return string Output.
     */
    private function drawTabs(array $data):string
    {
        global $config;

        $background = modules_get_color_status($data['status'], true);
        $color = modules_get_textcolor_status($data['status']);

        $style = 'background-color:'.$background.'; color:'.$color.';';
        $output = '<div class="widget-module-tabs" style="'.$style.'">';
        $output .= '<span class="widget-module-tabs-title">';
        

        $name = '';
        switch ($this->values['label']) {
            case 'agent':
                $name = $data['alias'];
            break;

            case 'agent_module':
                $name = $data['alias'].' / '.$data['name'];
            break;

            default:
            case 'module':
                $name = $data['name'];
            break;
        }

        $output .= $name;
        $output .= '</span>';
        $output .= '<span class="widget-module-tabs-data">';
        if ($data['data'] !== null && $data['data'] !== '') {
            if (isset($this->values['formatData']) === true
                && (bool) $this->values['formatData'] === true
            ) {
                if (is_numeric($data['data']) === true) {
                    $output .= format_for_graph(
                        $data['data'],
                        $config['graph_precision']
                    );
                } else {
                    $output .= ui_print_truncate_text($data['data'], 20);
                }
            } else {
                if (is_numeric($data['data']) === true) {
                    $output .= sla_truncate(
                        $data['data'],
                        $config['graph_precision']
                    );
                } else {
                    $output .= ui_print_truncate_text($data['data'], 20);
                }
            }
        } else {
            $output .= '--';
        }

        $output .= '<span class="widget-module-tabs-unit">';
        $output .= ' '.$data['unit'];
        $output .= '</span>';
        $output .= '</span>';
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
        return __('Color tabs modules');
    }


    /**
     * Get Name.
     *
     * @return string.
     */
    public static function getName()
    {
        return 'ColorModuleTabs';
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
            'height' => 610,
        ];

        return $size;
    }


}
