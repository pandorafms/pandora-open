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

require_once $config['homedir'].'/include/functions_agents.php';
require_once $config['homedir'].'/include/functions_modules.php';

/**
 * Module value Widgets.
 */
class ModuleValueWidget extends Widget
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
        $this->title = __('Module value');

        // Name.
        if (empty($this->name) === true) {
            $this->name = 'module_value';
        }

        // This forces at least a first configuration.
        $this->configurationRequired = false;
        if (empty($this->values['moduleId']) === true) {
            $this->configurationRequired = true;
        } else {
            try {
                

                $check_exist = db_get_sql(
                    sprintf(
                        'SELECT id_agente_modulo
                        FROM tagente_modulo
                        WHERE id_agente_modulo = %s
                            AND delete_pending = 0',
                        $this->values['moduleId']
                    )
                );
            } catch (\Exception $e) {
                // Unexistent agent.
                

                $check_exist = false;
            } finally {
                
            }

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

        if (isset($decoder['label_'.$this->cellId]) === true) {
            $values['label'] = $decoder['label_'.$this->cellId];
        }

        if (isset($decoder['label']) === true) {
            $values['label'] = $decoder['label'];
        }

        if (isset($decoder['id_agent_'.$this->cellId]) === true) {
            $values['agentId'] = $decoder['id_agent_'.$this->cellId];
        }

        if (isset($decoder['agentId']) === true) {
            $values['agentId'] = $decoder['agentId'];
        }

        if (isset($decoder['id_module_'.$this->cellId]) === true) {
            $values['moduleId'] = $decoder['id_module_'.$this->cellId];
        }

        if (isset($decoder['moduleId']) === true) {
            $values['moduleId'] = $decoder['moduleId'];
        }

        if (isset($decoder['size_value_'.$this->cellId]) === true) {
            $values['sizeValue'] = $decoder['size_value_'.$this->cellId];
        }

        if (isset($decoder['sizeValue']) === true) {
            $values['sizeValue'] = $decoder['sizeValue'];
        }

        if (isset($decoder['size_label_'.$this->cellId]) === true) {
            $values['sizeLabel'] = $decoder['size_label_'.$this->cellId];
        }

        if (isset($decoder['sizeLabel']) === true) {
            $values['sizeLabel'] = $decoder['sizeLabel'];
        }

        if (isset($decoder['horizontal']) === true) {
            $values['horizontal'] = $decoder['horizontal'];
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

        // Default values.
        if (isset($values['sizeLabel']) === false) {
            $values['sizeLabel'] = 20;
        }

        if (isset($values['sizeValue']) === false) {
            $values['sizeValue'] = 20;
        }

        // Retrieve global - common inputs.
        $inputs = parent::getFormInputs();

        // Label.
        $inputs[] = [
            'label'     => __('Label'),
            'arguments' => [
                'name'   => 'label',
                'type'   => 'text',
                'value'  => $values['label'],
                'return' => true,
                'size'   => 0,
            ],
        ];

        // Autocomplete agents.
        $inputs[] = [
            'label'     => __('Agent'),
            'arguments' => [
                'type'               => 'autocomplete_agent',
                'name'               => 'agentAlias',
                'id_agent_hidden'    => $values['agentId'],
                'name_agent_hidden'  => 'agentId',
                'server_id_hidden'   => null,
                'return'             => true,
                'module_input'       => true,
                'module_name'        => 'moduleId',
                'module_none'        => false,
                'size'               => 0,
            ],
        ];

        // Autocomplete module.
        $inputs[] = [
            'label'     => __('Module'),
            'arguments' => [
                'type'           => 'autocomplete_module',
                'name'           => 'moduleId',
                'selected'       => $values['moduleId'],
                'return'         => true,
                'sort'           => false,
                'agent_id'       => $values['agentId'],
                'style'          => 'width: inherit;',
                'filter_modules' => (users_access_to_agent($values['agentId'], 'AR', false) === false) ? [$values['moduleId']] : [],
                'nothing'        => __('None'),
                'nothing_value'  => 0,
            ],
        ];

        // Text size of value in px.
        $inputs[] = [
            'label'     => __('Text size of value in px'),
            'arguments' => [
                'name'   => 'sizeValue',
                'type'   => 'number',
                'value'  => $values['sizeValue'],
                'return' => true,
                'min'    => 0,
            ],
        ];

        // Text size of label in px.
        $inputs[] = [
            'label'     => __('Text size of label in px'),
            'arguments' => [
                'name'   => 'sizeLabel',
                'type'   => 'number',
                'value'  => $values['sizeLabel'],
                'return' => true,
                'min'    => 0,
            ],
        ];

        // Horizontal.
        $inputs[] = [
            'label'     => __('Horizontal').ui_print_help_tip(__('If not, layout is vertical'), true),
            'arguments' => [
                'wrapper' => 'div',
                'name'    => 'horizontal',
                'type'    => 'switch',
                'value'   => $values['horizontal'],
                'return'  => true,
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

        $values['label'] = \get_parameter('label', '');
        $values['agentId'] = \get_parameter('agentId', 0);
        
        $values['moduleId'] = \get_parameter('moduleId', 0);
        $values['sizeValue'] = \get_parameter('sizeValue', 0);
        $values['sizeLabel'] = \get_parameter_switch('sizeLabel');
        $values['horizontal'] = \get_parameter_switch('horizontal');

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

        $output = '';

        $id_module = $this->values['moduleId'];
        $unit = modules_get_unit($this->values['moduleId']);

        $data_module = \modules_get_last_value($id_module);

        $label = $this->values['label'];
        $sizeLabel = (isset($this->values['sizeLabel']) === true) ? $this->values['sizeLabel'] : 40;
        $sizeValue = (isset($this->values['sizeValue']) === true) ? $this->values['sizeValue'] : 40;

        $db_status = modules_get_agentmodule_status($this->values['moduleId']);
        $module_value = modules_get_last_value($this->values['moduleId']);
        $status = 0;
        $title = '';
        modules_get_status($this->values['moduleId'], $db_status, $module_value, $status, $title);
        $color = match ($status) {
            STATUS_MODULE_NO_DATA => COL_NOTINIT,
            STATUS_MODULE_CRITICAL => COL_CRITICAL,
            STATUS_MODULE_WARNING => COL_WARNING,
            STATUS_MODULE_OK => COL_NORMAL,
            STATUS_MODULE_UNKNOWN => COL_UNKNOWN,
        };

        $uuid = uniqid();
        $output .= '<div class="container-center" id="container-'.$uuid.'">';

        $orientation = '';
        $margin_bottom = '';
        if ((int) $this->values['horizontal'] === 1) {
            $orientation = 'flex aligni_center';
        } else {
            $orientation = 'grid';
            $margin_bottom = 'mrgn_btn_20px';
        }

        // General div.
        $output .= '<div class="'.$orientation.'" id="general-'.$uuid.'">';

        // Div value.
        $output .= '<div class="pdd_l_15px pdd_r_15px '.$margin_bottom.'" style="flex: 0 1 '.$sizeValue.'px; line-height: '.$sizeValue.'px; font-size:'.$sizeValue.'px; color: '.$color.'">';

        if (is_numeric($data_module) === true) {
            $dataDatos = remove_right_zeros(
                number_format(
                    $data_module,
                    $config['graph_precision'],
                    $config['decimal_separator'],
                    $config['thousand_separator']
                )
            ).$unit;
        } else {
            $dataDatos = trim($data_module);
        }

        $output .= $dataDatos;

        $output .= '</div>';

        if (empty($label) === false) {
            // Div Label.
            $output .= '<div class="pdd_l_15px pdd_r_15px" style="flex: 1 1 '.$sizeLabel.'px; line-height: '.$sizeLabel.'px; font-size:'.$sizeLabel.'px;">'.$label.'</div>';
        }

        $output .= '</div>';
        $output .= '</div>';

        $output .= '<script>
        var containerWidth = document.querySelector("#container-'.$uuid.'").offsetWidth;
        var generalWidth = document.querySelector("#general-'.$uuid.'").offsetWidth;

        if (generalWidth >= containerWidth) {
            $("#container-'.$uuid.'").css("align-items", "flex-start");
        } else {
            $("#container-'.$uuid.'").css("align-items", "center");
        }
        </script>';
        return $output;
    }


    /**
     * Get description.
     *
     * @return string.
     */
    public static function getDescription()
    {
        return __('Module value');
    }


    /**
     * Get Name.
     *
     * @return string.
     */
    public static function getName()
    {
        return 'module_value';
    }


    /**
     * Get size Modal Configuration.
     *
     * @return array
     */
    public function getSizeModalConfiguration(): array
    {
        $size = [
            'width'  => 450,
            'height' => 490,
        ];

        return $size;
    }


}
