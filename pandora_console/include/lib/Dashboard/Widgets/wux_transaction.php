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
 * Wux transaction Widgets.
 */
class WuxWidget extends Widget
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
        $this->title = __('Agent WUX transaction');

        // Name.
        if (empty($this->name) === true) {
            $this->name = 'wux_transaction';
        }

        // Must be configured before using.
        $this->configurationRequired = false;
        if (empty($this->values['agentId']) === true) {
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

        if (isset($decoder['id_agent_'.$this->cellId]) === true) {
            $values['agentId'] = $decoder['id_agent_'.$this->cellId];
        }

        if (isset($decoder['agentId']) === true) {
            $values['agentId'] = $decoder['agentId'];
        }

        if (isset($decoder['wux_transaction_'.$this->cellId]) === true) {
            $values['transactionId'] = $decoder['wux_transaction_'.$this->cellId];
        }

        if (isset($decoder['transactionId']) === true) {
            $values['transactionId'] = $decoder['transactionId'];
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
                'module_name'        => 'transactionId',
                'module_none'        => false,
                'from_wux'           => true,
                'size'               => 0,
                'required'           => true,
            ],
        ];

        // Autocomplete module.
        $inputs[] = [
            'label'     => __('Wux transaction'),
            'arguments' => [
                'type'           => 'autocomplete_module',
                'fields'         => [],
                'name'           => 'transactionId',
                'selected'       => $values['transactionId'],
                'return'         => true,
                'sort'           => false,
                'agent_id'       => $values['agentId'],
                'style'          => 'width: inherit;',
                'from_wux'       => true,
                'required'       => true,
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

        $values['agentId'] = \get_parameter('agentId', 0);
        
        $values['transactionId'] = \get_parameter('transactionId', 0);

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

        include_once $config['homedir'].'/include/graphs/functions_d3.php';

        $size = parent::getSize();

        $id_agent = $this->values['agentId'];
        $wux_transaction = $this->values['transactionId'];

        $output = '';
        if (empty($wux_transaction) === true) {
            $output .= '<div class="container-center">';
            $output .= \ui_print_info_message(
                [
                    'no_close' => true,
                    'message'  => __('No wux transaction selected.'),
                ],
                '',
                true
            );
            $output .= '</div>';
        } else {
            $wux_transaction_name = \get_wux_trans_name($wux_transaction);
            $wux_transaction_ppal = \get_id_trans_ppal($wux_transaction);
            $phases = \wux_transaction_get_phases(
                $wux_transaction_ppal,
                $wux_transaction_name,
                $wux_transaction_ppal
            );

            if ($phases) {
                $global_time = \wux_transaction_get_global_time(
                    $wux_transaction_ppal
                );

                $last_try = (\get_system_time() - \time_w_fixed_tz(
                    \wux_transaction_get_last_try(
                        $wux_transaction_ppal
                    )
                ));

                $wux_transaction_statistics = \wux_transaction_statistics(
                    $wux_transaction
                );

                $have_errors = false;
                $first_error = false;

                foreach ($phases as $phase) {
                    if ($phase['status'] != 0) {
                        if (!$first_error) {
                            $first_error = true;
                            $error_image = \get_last_error_image_wux(
                                $wux_transaction_ppal
                            );
                        }

                        $have_errors = true;
                    }
                }

                $output .= '<div>';

                $output .= '<div>';
                $output .= \ux_console_phases_donut(
                    $phases,
                    ($id_agent + \rand(10, 1000)),
                    $size['width'],
                    ($size['height'] - 45),
                    true
                );
                $output .= '</div>';

                $output .= '</div>';
            } else {
                $output .= \ui_print_info_message(
                    [
                        'no_close' => true,
                        'message'  => __('Phase modules not found'),
                    ],
                    '',
                    true
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
        return __('Agent WUX transaction');
    }


    /**
     * Get Name.
     *
     * @return string.
     */
    public static function getName()
    {
        return 'wux_transaction';
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
            'height' => 330,
        ];

        return $size;
    }


}
