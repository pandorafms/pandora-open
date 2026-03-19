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
 * Module status Widgets.
 */
class ModulesByStatus extends Widget
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
     * Size
     *
     * @var array
     */
    public $size;


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
        $this->title = __('Module status');

        // Name.
        if (empty($this->name) === true) {
            $this->name = 'ModulesByStatus';
        }

        // This forces at least a first configuration.
        // This forces at least a first configuration.
        $this->configurationRequired = false;
        if (empty($this->values['status']) === true && $this->values['status'] !== '0') {
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

        if (isset($decoder['groupId']) === true) {
            $values['groupId'] = $decoder['groupId'];
        }

        if (isset($decoder['search_agent']) === true) {
            $values['search_agent'] = $decoder['search_agent'];
        }

        if (isset($decoder['search']) === true) {
            $values['search'] = $decoder['search'];
        }

        if (isset($decoder['status']) === true) {
            $values['status'] = $decoder['status'];
        }

        if (isset($decoder['limit']) === true) {
            $values['limit'] = $decoder['limit'];
        }

        if (isset($decoder['nodes']) === true) {
            $values['nodes'] = $decoder['nodes'];
        }

        if (isset($decoder['disabled_modules']) === true) {
            $values['disabled_modules'] = $decoder['disabled_modules'];
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
        global $config;

        $values = $this->values;

        // Retrieve global - common inputs.
        $inputs = parent::getFormInputs();

        $return_all_group = false;
        if (users_can_manage_group_all('RM') || $values['groupId'] == 0) {
            $return_all_group = true;
        }

        // Groups.
        $inputs[] = [
            'label'     => __('Group'),
            'arguments' => [
                'type'           => 'select_groups',
                'name'           => 'groupId',
                'returnAllGroup' => $return_all_group,
                'privilege'      => 'AR',
                'selected'       => $values['groupId'],
                'return'         => true,
            ],
        ];

        // Search Agent.
        $inputs[] = [
            'label'     => __('Search agent').ui_print_help_tip(__('Search filter by Agent name field content'), true),
            'arguments' => [
                'name'   => 'search_agent',
                'type'   => 'text',
                'value'  => $values['search_agent'],
                'return' => true,
                'size'   => 0,
            ],
        ];

        // Search.
        $inputs[] = [
            'label'     => __('Search module').ui_print_help_tip(__('Search filter by Module name field content'), true),
            'arguments' => [
                'name'   => 'search',
                'type'   => 'text',
                'value'  => $values['search'],
                'return' => true,
                'size'   => 0,
            ],
        ];

        $inputs[] = [
            'label'     => html_print_div(
                [
                    'class'   => 'flex',
                    'content' => __('Disabled modules').ui_print_help_tip(__('Include disabled modules'), true),
                ],
                true
            ),
            'arguments' => [
                'id'     => 'disabled_modules',
                'name'   => 'disabled_modules',
                'type'   => 'switch',
                'value'  => ($values['disabled_modules'] === null) ? true : $values['disabled_modules'],
                'return' => true,
            ],
        ];

        // Status fields.
        $status_fields = [];
        $status_fields[AGENT_MODULE_STATUS_NORMAL] = __('Normal');
        $status_fields[AGENT_MODULE_STATUS_CRITICAL_BAD] = __('Critical');
        $status_fields[AGENT_MODULE_STATUS_WARNING] = __('Warning');
        $status_fields[AGENT_MODULE_STATUS_UNKNOWN] = __('Unknown');
        $status_fields[AGENT_MODULE_STATUS_NOT_INIT] = __('Not init');
        $status_fields[AGENT_MODULE_STATUS_NOT_NORMAL] = __('Not normal');
        $status_selected = explode(',', $values['status']);

        (isset($values['status']) === false) ? $status_selected = AGENT_MODULE_STATUS_CRITICAL_BAD : '';

        $inputs[] = [
            'label'     => __('Status'),
            'arguments' => [
                'name'       => 'status',
                'type'       => 'select',
                'fields'     => $status_fields,
                'selected'   => $status_selected,
                'return'     => true,
                'multiple'   => true,
                'size'       => count($status_fields),
                'select_all' => false,
                'required'   => true,
            ],
        ];

        // Limit fields.
        $limit_fields = [];
        $limit_fields[5] = 5;
        $limit_fields[10] = 10;
        $limit_fields[25] = 25;
        $limit_fields[100] = 100;
        $limit_fields[200] = 200;
        $limit_fields[500] = 500;
        $limit_fields[1000] = 1000;
        $limit_selected = explode(',', $values['limit']);

        (isset($values['limit']) === false) ? $limit_selected = 5 : '';

        $inputs[] = [
            'label'     => __('Limit'),
            'arguments' => [
                'name'           => 'limit',
                'type'           => 'select',
                'fields'         => $limit_fields,
                'selected'       => $limit_selected,
                'return'         => true,
                'required'       => true,
                'select2_enable' => false,
            ],
        ];

        // Nodes.
        

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
        $values['search'] = \get_parameter('search', '');
        $values['search_agent'] = \get_parameter('search_agent', '');
        $values['status'] = \get_parameter('status', '');
        $values['limit'] = \get_parameter('limit', '');
        $values['nodes'] = \get_parameter('nodes', '');
        $values['disabled_modules'] = \get_parameter_switch('disabled_modules');

        return $values;
    }


    /**
     * Draw widget.
     *
     * @return void Html output;
     */
    public function load()
    {
        // Datatables list.
        try {
            $info_columns = $this->columns();
            $column_names = $info_columns['column_names'];
            $columns = $info_columns['columns'];
            $hash = get_parameter('auth_hash', '');
            $id_user = get_parameter('id_user', '');

            $tableId = 'ModuleByStatus_'.$this->dashboardId.'_'.$this->cellId;
            // Load datatables user interface.
            ui_print_datatable(
                [
                    'id'                 => $tableId,
                    'class'              => 'info_table align-left-important',
                    'style'              => 'width: 100%',
                    'columns'            => $columns,
                    'column_names'       => $column_names,
                    'ajax_url'           => 'include/ajax/module',
                    'ajax_data'          => [
                        'get_data_ModulesByStatus' => 1,
                        'table_id'                 => $tableId,
                        'search_agent'             => $this->values['search_agent'],
                        'search'                   => $this->values['search'],
                        'groupId'                  => $this->values['groupId'],
                        'status'                   => $this->values['status'],
                        'nodes'                    => $this->values['nodes'],
                        'disabled_modules'         => $this->values['disabled_modules'],
                        'auth_hash'                => $hash,
                        'auth_class'               => 'PandoraFMS\Dashboard\Manager',
                        'id_user'                  => $id_user,
                    ],
                    'default_pagination' => $this->values['limit'],
                    'order'              => [
                        'field'     => 'last_status_change',
                        'direction' => 'desc',
                    ],
                    'csv'                => 0,
                    'pagination_options' => [
                        [
                            5,
                            10,
                            25,
                            100,
                            200,
                            500,
                            1000,
                        ],
                        [
                            5,
                            10,
                            25,
                            100,
                            200,
                            500,
                            1000,
                        ],
                    ],
                    'dom_elements'       => 'frtilp',
                ]
            );
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }


    /**
     * Get columns.
     *
     * @return array
     */
    private function columns()
    {
        $columns = [];
        $column_names = [];

            $column_names = [
                __('Module name'),
                __('Agent'),
                __('Last status change'),
                __('Status'),
            ];

            $columns = [
                'nombre',
                'alias',
                [
                    'text'  => 'last_status_change',
                    'class' => '',
                ],
                'estado',
            ];
        

        $data = [
            'columns'      => $columns,
            'column_names' => $column_names,
        ];

        return $data;
    }


    /**
     * Get description.
     *
     * @return string.
     */
    public static function getDescription()
    {
        return __('Modules by status');
    }


    /**
     * Get Name.
     *
     * @return string.
     */
    public static function getName()
    {
        return 'ModulesByStatus';
    }


    /**
     * Get size Modal Configuration.
     *
     * @return array
     */
    public function getSizeModalConfiguration(): array
    {

            $size = [
                'width'  => 470,
                'height' => 480,
            ];
        

        return $size;
    }


}
