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
 * Alerts fired Widgets.
 */
class AlertsFiredWidget extends Widget
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
        include_once $config['homedir'].'/include/functions_users.php';
        include_once $config['homedir'].'/include/functions_alerts.php';

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
        $this->title = __('Triggered alerts report');

        // Name.
        if (empty($this->name) === true) {
            $this->name = 'alerts_fired';
        }

        // This forces at least a first configuration.
        $this->configurationRequired = false;
        if (isset($this->values['groupId']) === false) {
            $this->configurationRequired = true;
        } else if ($this->values['groupId'] !== '0') {
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

        if (isset($decoder['group']) === true) {
            $values['groupId'] = $decoder['group'];
        }

        if (isset($decoder['groupId']) === true) {
            $values['groupId'] = $decoder['groupId'];
        }

        if (isset($decoder['group_recursion']) === true) {
            $values['group_recursion'] = $decoder['group_recursion'];
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

        // Group recursion.
        $inputs[] = [
            'label'     => __('Recursion'),
            'arguments' => [
                'wrapper' => 'div',
                'name'    => 'group_recursion',
                'type'    => 'switch',
                'value'   => $values['group_recursion'],
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

        $values['groupId'] = \get_parameter('groupId', 0);
        $values['group_recursion'] = \get_parameter_switch('group_recursion');

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

        if ((int) $this->values['groupId'] === 0) {
            $groups = users_get_groups(false, 'AR', false);
        } else {
            $groups = [$this->values['groupId'] => ''];
        }

        $group_recursion = false;
        if (empty($this->values['group_recursion']) === false) {
            $group_recursion = true;
        }

        if (isset($groups) === true && is_array($groups) === true) {
            $table = new \StdClass();
            $table->class = 'databox data centered';
            $table->cellspacing = '0';
            $table->width = '100%';
            $table->data = [];
            $table->size = [];
            $table->style = [];
            $table->style[0] = 'text-align: left;';
            $table->style[1] = 'text-align: left;';
            $table->style[2] = 'text-align: left;';
            $table->style[3] = 'text-align: left;';

            $url = $config['homeurl'];
            $url .= 'index.php?sec=estado&sec2=operation/agentes/alerts_status';
            $url .= '&refr=60&filter=fired&filter_standby=all&&tab=alert';

            $flag = false;

            $groups_ids = [];
            $groups_ids_tmp = [];
            foreach ($groups as $id_group => $name) {
                if ($group_recursion === true) {
                    $groups_ids_tmp[] = groups_get_children_ids($id_group);
                }
            }

            if ($group_recursion === true) {
                foreach ($groups_ids_tmp as $ids_tmp => $values) {
                    foreach ($values as $value) {
                        $groups_ids[$value] = '';
                    }
                }

                $groups = $groups_ids;
            }

            foreach ($groups as $id_group => $name) {
                $alerts_group = get_group_alerts([$id_group]);
                if (isset($alerts_group['simple']) === true) {
                    $alerts_group = $alerts_group['simple'];
                }

                foreach ($alerts_group as $alert) {
                    $data = [];

                    if ($alert['times_fired'] == 0) {
                        continue;
                    }

                    $flag = true;

                    $data[0] = '<a href="'.$url.'&ag_group='.$id_group.'">';
                    $data[0] .= ui_print_group_icon(
                        $id_group,
                        true,
                        'groups_small',
                        '',
                        false
                    );
                    $data[0] .= '</a>';

                    $data[1] = '<a href="'.$url.'&free_search='.$alert['agent_name'].'">';
                    $data[1] .= $alert['agent_name'];
                    $data[1] .= '</a>';

                    $data[2] = $alert['agent_module_name'];

                    $data[3] = ui_print_timestamp($alert['last_fired'], true);

                    array_push($table->data, $data);
                }
            }

            if ($flag === true) {
                $height = (count($table->data) * 30);
                $style = 'min-width:300px; min-height:'.$height.'px;';
                $output .= '<div class="" style="'.$style.'">';
                $output .= html_print_table($table, true);
                $output .= '</div>';
            } else {
                $output .= '<div class="container-center">';
                $output .= \ui_print_info_message(
                    __('Not alert fired'),
                    '',
                    true
                );
                $output .= '</div>';
            }
        } else {
            $output .= '<div class="container-center">';
            $output .= \ui_print_info_message(
                __('You must select some group'),
                '',
                true
            );
            $output .= '</div>';
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
        return __('Triggered alerts report');
    }


    /**
     * Get Name.
     *
     * @return string.
     */
    public static function getName()
    {
        return 'alerts_fired';
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
            'height' => 260,
        ];

        return $size;
    }


}
