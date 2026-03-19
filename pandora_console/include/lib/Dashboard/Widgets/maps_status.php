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
 * Maps status Widgets.
 */
class MapsStatusWidget extends Widget
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
        $this->title = __('General visual maps report');

        // Name.
        if (empty($this->name) === true) {
            $this->name = 'maps_status';
        }

        // This forces at least a first configuration.
        $this->configurationRequired = false;
        if (empty($this->values['maps']) === true) {
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

        if (isset($decoder['maps']) === true) {
            if (is_array($decoder['maps']) === true) {
                $decoder['maps'][0] = implode(',', $decoder['maps']);
            }

            $values['maps'] = $decoder['maps'];
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

        include_once $config['homedir'].'/include/functions_visual_map.php';

        $return_all_group = false;

        if (users_can_manage_group_all('RM')) {
            $return_all_group = true;
        }

        $selected = explode(',', $values['maps'][0]);

        $dataAllVc = \visual_map_get_user_layouts(
            $config['id_user'],
            false,
            [],
            true,
            false,
            false
        );

        $dataVc = \visual_map_get_user_layouts(
            $config['id_user'],
            false,
            ['can_manage_group_all' => $return_all_group],
            $return_all_group,
            false
        );

        $diff = array_diff_key($dataAllVc, $dataVc);

        if (!empty($diff)) {
            foreach ($diff as $key => $value) {
                if (in_array($key, $selected)) {
                    $dataVc[$key] = $value;
                }
            }
        }

        $fields = array_reduce(
            $dataVc,
            function ($carry, $item) {
                $carry[$item['id']] = io_safe_output($item['name']);
                return $carry;
            },
            []
        );

        $inputs[] = [
            'label'     => __('Maps'),
            'arguments' => [
                'type'     => 'select',
                'fields'   => $fields,
                'name'     => 'maps[]',
                'selected' => explode(',', $values['maps'][0]),
                'multiple' => true,
                'return'   => true,
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

        $values['maps'] = \get_parameter('maps', []);

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

        include_once $config['homedir'].'/include/functions_visual_map.php';

        $user_layouts = \visual_map_get_user_layouts(
            $config['id_user'],
            false,
            [],
            true,
            false,
            false
        );

        $table = new \stdClass();
        $table->class = 'widget_maps_status';
        $table->width = '90%';
        $table->data = [];

        $maps = explode(',', $this->values['maps'][0]);
        $output = '';
        if (isset($maps) === true && empty($maps) === false) {
            foreach ($maps as $id_layout) {
                $check_exist = db_get_value(
                    'id',
                    'tlayout',
                    'id',
                    $id_layout
                );

                if ($check_exist === false) {
                    continue;
                }

                $data = [];

                $url = $config['homeurl'];

                    $url .= sprintf(
                        'index.php?sec=visualc&sec2=operation/visual_console/render_view&refr=%s&id=%s',
                        $config['vc_refr'],
                        $id_layout
                    );

                // This will give us the group name.
                $data[0] = '<a href="'.$url.'">';
                $data[0] .= io_safe_output($user_layouts[$id_layout]['name']);
                $data[0] .= '</a>';

                // Status 0 is OK.
                if (!\visual_map_get_layout_status($id_layout)) {
                    $data[1] = html_print_image(
                        'images/pixel_green.png',
                        true,
                        [
                            'title' => __('OK'),
                            'class' => 'status',
                        ]
                    );
                } else {
                    $data[1] = html_print_image(
                        'images/pixel_red.png',
                        true,
                        [
                            'title' => __('Bad'),
                            'class' => 'status',
                        ]
                    );
                }

                array_push($table->data, $data);
            }

            if (empty($table->data) === false) {
                // 31 px for each map.
                $minHeight = (count($maps) * 31);
                $style = 'min-width:200px; min-height:'.$minHeight.'px';
                $output = '<div class="container-center" style="'.$style.'">';
                $output .= html_print_table($table, true);
                $output .= '</div>';
            } else {
                $output .= '<div class="container-center">';
                $output .= \ui_print_error_message(
                    __('Widget cannot be loaded').'. '.__('Please, configure the widget again to recover it'),
                    '',
                    true
                );
                $output .= '</div>';
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
        return __('General visual maps report');
    }


    /**
     * Get Name.
     *
     * @return string.
     */
    public static function getName()
    {
        return 'maps_status';
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
            'height' => 425,
        ];

        return $size;
    }


}
