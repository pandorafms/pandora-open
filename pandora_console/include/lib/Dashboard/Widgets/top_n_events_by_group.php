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
 * TopN events by group Widgets.
 */
class TopNEventByGroupWidget extends Widget
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

        // Include.
        include_once $config['homedir'].'/include/functions_events.php';
        include_once $config['homedir'].'/include/functions_users.php';
        include_once $config['homedir'].'/include/functions_agents.php';

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
        $this->title = __('Top N events by agent');

        // Name.
        if (empty($this->name) === true) {
            $this->name = 'top_n_events_by_group';
        }

        // This forces at least a first configuration.
        $this->configurationRequired = false;
        if (empty($this->values['groupId']) === true) {
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

        if (isset($decoder['amount']) === true) {
            $values['amountShow'] = $decoder['amount'];
        }

        if (isset($decoder['amountShow']) === true) {
            $values['amountShow'] = $decoder['amountShow'];
        }

        if (isset($decoder['event_view_hr']) === true) {
            $values['maxHours'] = $decoder['event_view_hr'];
        }

        if (isset($decoder['maxHours']) === true) {
            $values['maxHours'] = $decoder['maxHours'];
        }

        if (isset($decoder['id_groups']) === true) {
            if (is_array($decoder['id_groups']) === true) {
                $implode = implode(',', $decoder['id_groups']);
                $values['groupId'] = [];
                $values['groupId'][0] = $implode;
            }
        }

        if (isset($decoder['groupId']) === true) {
            $values['groupId'] = $decoder['groupId'];
        }

        if (isset($decoder['legend_position']) === true) {
            $values['legendPosition'] = $decoder['legend_position'];
        }

        if (isset($decoder['legendPosition']) === true) {
            $values['legendPosition'] = $decoder['legendPosition'];
        }

        if (isset($decoder['show_total_data']) === true) {
            $values['show_total_data'] = $decoder['show_total_data'];
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

        // Default values.
        if (isset($values['amountShow']) === false) {
            $values['amountShow'] = 10;
        }

        if (isset($values['maxHours']) === false) {
            $values['maxHours'] = 8;
        }

        // Text size of value in px.
        $inputs[] = [
            'label'     => __('Amount to show'),
            'arguments' => [
                'name'   => 'amountShow',
                'type'   => 'number',
                'value'  => $values['amountShow'],
                'return' => true,
                'min'    => 0,
            ],
        ];

        // Text size of value in px.
        $inputs[] = [
            'label'     => __('Max. hours old'),
            'arguments' => [
                'name'   => 'maxHours',
                'type'   => 'number',
                'value'  => $values['maxHours'],
                'return' => true,
                'min'    => 0,
            ],
        ];

        $return_all_group = false;

        // Groups.
        $selected_groups = [];
        if ($values['groupId']) {
            $selected_groups = explode(',', $values['groupId'][0]);

            if (users_can_manage_group_all('RM') === true
                || in_array(0, $selected_groups) === true
            ) {
                // Return all group if user has permissions
                // or it is a currently selected group.
                $return_all_group = true;
            }
        } else {
            if (users_can_manage_group_all('RM') === true) {
                $return_all_group = true;
            }
        }

        $inputs[] = [
            'label'     => __('Groups'),
            'arguments' => [
                'type'           => 'select_groups',
                'name'           => 'groupId[]',
                'returnAllGroup' => true,
                'privilege'      => 'AR',
                'selected'       => (empty($selected_groups) === true) ? [0] : $selected_groups,
                'return'         => true,
                'multiple'       => true,
                'returnAllGroup' => $return_all_group,
                'required'       => true,
            ],
        ];

        // Legend Position.
        $fields = [
            'bottom' => __('Bottom'),
            'hidden' => __('No legend'),
        ];

        $inputs[] = [
            'label'     => __('Legend Position'),
            'arguments' => [
                'type'     => 'select',
                'fields'   => $fields,
                'name'     => 'legendPosition',
                'selected' => $values['legendPosition'],
                'return'   => true,
            ],
        ];

        $inputs[] = [
            'label'     => __('Show total data'),
            'arguments' => [
                'type'   => 'switch',
                'name'   => 'show_total_data',
                'value'  => $values['show_total_data'],
                'return' => true,
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

        $values['amountShow'] = \get_parameter('amountShow', 0);
        $values['maxHours'] = \get_parameter('maxHours', 0);
        $values['groupId'] = \get_parameter('groupId', []);
        $values['legendPosition'] = \get_parameter('legendPosition', 0);
        $values['show_total_data'] = \get_parameter_switch('show_total_data', 0);

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

        $size = parent::getSize();
        $show_total_data = (bool) $this->values['show_total_data'];
        $this->values['groupId'] = explode(',', $this->values['groupId'][0]);

        if (empty($this->values['groupId']) === true) {
            $output .= '<div class="container-center">';
            $output .= \ui_print_info_message(
                __('Please select one or more groups.'),
                '',
                true
            );
            $output .= '</div>';
            return $output;
        } else {
            $timestamp = (time() - SECONDS_1HOUR * $this->values['maxHours']);

            $all_group = false;
            // Search all.
            if (array_search('0', $this->values['groupId']) !== false) {
                $all_group = true;
            }

                if ($all_group === false) {
                    $sql = sprintf(
                        'SELECT id_agente, COUNT(*) AS count
                        FROM tevento
                        WHERE utimestamp >= %d
                            AND id_grupo IN (%s)
                        GROUP BY id_agente
                        ORDER BY count DESC
                        LIMIT %d',
                        $timestamp,
                        implode(',', $this->values['groupId']),
                        $this->values['amountShow']
                    );
                } else {
                    $sql = sprintf(
                        'SELECT id_agente, COUNT(*) AS count
                        FROM tevento
                        WHERE utimestamp >= %d
                        GROUP BY id_agente
                        ORDER BY count DESC
                        LIMIT %d',
                        $timestamp,
                        $this->values['amountShow']
                    );
                }

                $result = db_get_all_rows_sql($sql);
            

            if (empty($result) === true) {
                $output .= '<div class="container-center">';
                $output .= \ui_print_error_message(
                    __('There is not data to show.'),
                    '',
                    true
                );
                $output .= '</div>';
                return $output;
            } else {
                $data_pie = [];
                $labels = [];
                $sum = 0;
                foreach ($result as $row) {
                    if ($row['id_agente'] == 0) {
                        $name = __('System');
                    } else {

                            $name = io_safe_output(
                                agents_get_alias($row['id_agente'])
                            );
                        
                    }

                    $name .= ' ('.$row['count'].')';
                    $sum += $row['count'];
                    $labels[] = io_safe_output($name);
                    $data_pie[] = $row['count'];
                }
            }

            $width = $size['width'];
            $height = $size['height'];

            switch ($this->values['legendPosition']) {
                case 'hidden':
                    $height = ($height - 50);
                break;

                default:
                case 'bottom':
                    $numleg = count($data_pie);
                    if ($numleg >= 4) {
                        $numleg = 4;
                    } else if ($numleg < 4 && $numleg > 1) {
                        $numleg = 2;
                    } else if ($numleg == 1) {
                        $numleg = 1.5;
                    }

                    // % is for the pie group the slices and show only 5.
                    $height = ($height - (65 * ($numleg)));
                break;
            }

            if ($show_total_data === true) {
                $output .= ring_graph(
                    $data_pie,
                    [
                        'legend'   => [
                            'display'  => true,
                            'position' => 'right',
                            'align'    => 'center',
                        ],
                        'elements' => [
                            'center' => [
                                'text'  => $sum,
                                'color' => '#2c3e50',
                            ],
                        ],
                        'labels'   => $labels,
                    ]
                );
            } else {
                $output .= pie_graph(
                    $data_pie,
                    [
                        'legend' => [
                            'display'  => true,
                            'position' => 'right',
                            'align'    => 'center',
                        ],
                        'labels' => $labels,
                    ]
                );
            }
        }

        return $output;
    }


    /**
     * Get description.
     *
     * @return string.
     */
    public static function getDescription()
    {
        return __('Top N events by agent');
    }


    /**
     * Get Name.
     *
     * @return string.
     */
    public static function getName()
    {
        return 'top_n_events_by_group';
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
            'height' => 520,
        ];

        return $size;
    }


}
