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
 * OS quick report Widgets.
 */
class OsQuickReportWidget extends Widget
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
     * Dashboard ID.
     *
     * @var integer
     */
    protected $dashboardId;

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
     */
    public function __construct(
        int $cellId,
        int $dashboardId=0,
        int $widgetId=0,
        ?int $width=0,
        ?int $height=0
    ) {
        global $config;

        // Includes.
        include_once $config['homedir'].'/include/functions_os.php';

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

        // Cell Id.
        $this->cellId = $cellId;

        // Widget ID.
        $this->widgetId = $widgetId;

        // Dashboard ID.
        $this->dashboardId = $dashboardId;

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
        $this->title = __('OS quick report');

        // Name.
        if (empty($this->name) === true) {
            $this->name = 'os_quick_report';
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
        // Retrieve global - common inputs.
        $inputs = parent::getFormInputs();

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

        $values = $this->values;

        $result = [];
        $os_array = os_get_os();
        foreach ($os_array as $os) {
            $id_os = (int) $os['id_os'];
            $total = os_agents_total($id_os);
            if ((int) $total === 0) {
                continue;
            }

            $result[$id_os]['name'] = $os['name'];
            $result[$id_os]['total'] = (int) $total;
            $result[$id_os]['normal'] = (int) os_agents_ok($id_os);
            $result[$id_os]['critical'] = (int) os_agents_critical($id_os);
            $result[$id_os]['unknown'] = (int) os_agents_unknown($id_os);
        }

        $output = '';
        if (empty($result) === false) {
            $table = new \stdClass();

            $table->class = 'info_table';
            $table->width = '100%';
            $table->cellpadding = 0;
            $table->cellspacing = 0;
            $table->size = [];
            $table->size[0] = '10%';
            $table->size[1] = '10%';
            $table->size[2] = '20%';
            $table->size[3] = '20%';
            $table->size[4] = '20%';
            $table->size[5] = '20%';

            $table->align = [];
            $table->align[0] = 'center';
            $table->align[1] = 'left';
            $table->align[2] = 'center';
            $table->align[3] = 'center';
            $table->align[4] = 'center';
            $table->align[5] = 'center';

            $table->head = [];
            $table->head[0] = __('OS');
            $table->head[1] = __('OS name');
            $table->head[2] = ucfirst(__('total'));
            $table->head[3] = ucfirst(__('normal'));
            $table->head[4] = ucfirst(__('critical'));
            $table->head[5] = ucfirst(__('unknown'));

            $table->headstyle = [];
            $table->headstyle[0] = 'text-align:center;background-color: '.$values['background'];
            $table->headstyle[1] = 'background-color: '.$values['background'];
            $table->headstyle[2] = 'text-align:center;background-color: '.$values['background'];
            $table->headstyle[3] = 'text-align:center;background-color: '.$values['background'];
            $table->headstyle[4] = 'text-align:center;background-color: '.$values['background'];
            $table->headstyle[5] = 'text-align:center;background-color: '.$values['background'];

            $table->style = [];
            $table->style[0] = 'background-color: '.$values['background'].';';
            $table->style[1] = 'background-color: '.$values['background'].';';
            $table->style[2] = 'background-color: '.$values['background'].'; font-size: 1.5em; font-weight: bolder;';
            $table->style[3] = 'background-color: '.$values['background'].'; font-size: 1.5em; font-weight: bolder;';
            $table->style[4] = 'background-color: '.$values['background'].';';
            $table->style[5] = 'background-color: '.$values['background'].';';

            foreach ($result as $id => $os) {
                $data = [];
                ($os['critical'] > 0) ? $color_critical = 'color: '.COL_CRITICAL.';' : $color_critical = '';
                ($os['unknown'] > 0) ? $color_unknown = 'color: '.COL_UNKNOWN.';' : $color_unknown = '';

                $data[0] = ui_print_os_icon($id, false, true);
                $data[1] = $os['name'];
                $data[2] = $os['total'];
                $data[3] = $os['normal'];
                $data[4] = '<span class="widget-module-tabs-data" style="'.$color_critical.'">'.$os['critical'].'</span>';
                $data[5] = '<span class="widget-module-tabs-data" style="'.$color_unknown.'">'.$os['unknown'].'</span>';

                $table->data[] = $data;
            }

            $output = html_print_table($table, true);
        } else {
            $output = 'No data available';
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
        return __('OS quick report');
    }


    /**
     * Get Name.
     *
     * @return string.
     */
    public static function getName()
    {
        return 'os_quick_report';
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
            'height' => 205,
        ];

        return $size;
    }


}
