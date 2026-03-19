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
// Load Visual Console.
use Models\VisualConsole\Container as VisualConsole;

use PandoraFMS\User;
/**
 * Maps by users Widgets.
 */
class MapsMadeByUser extends Widget
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
     * Cell Id.
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

        // Include.
        include_once $config['homedir'].'/include/graphs/functions_d3.php';
        include_once $config['homedir'].'/include/functions_visual_map.php';

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
        $this->title = __('Visual Console');

        // Name.
        if (empty($this->name) === true) {
            $this->name = 'maps_made_by_user';
        }

        // This forces at least a first configuration.
        $this->configurationRequired = false;
        if (empty($this->values['vcId']) === true) {
            $this->configurationRequired = true;
        } else {
            try {
                

                $check_exist = db_get_value(
                    'id',
                    'tlayout',
                    'id',
                    $this->values['vcId']
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

        if (isset($decoder['id_layout']) === true) {
            $values['vcId'] = $decoder['id_layout'];
        }

        if (isset($decoder['vcId']) === true) {
            $values['vcId'] = $decoder['vcId'];
        }

        return $values;
    }


    /**
     * Dumps consoles list in json to fullfill select for consoles.
     *
     * @return void
     */
    public function getVisualConsolesList(): void
    {
        $node_id = \get_parameter('nodeId', $this->nodeId);
        

        echo json_encode(
            $this->getVisualConsoles(),
            1
        );

        
    }


    /**
     * Retrieve visual consoles.
     *
     * @return array
     */
    private function getVisualConsoles()
    {
        global $config;

        $return_all_group = false;

        if (users_can_manage_group_all('RM')) {
            $return_all_group = true;
        }

        $fields = \visual_map_get_user_layouts(
            $config['id_user'],
            true,
            ['can_manage_group_all' => $return_all_group],
            $return_all_group
        );

        foreach ($fields as $k => $v) {
            $fields[$k] = \io_safe_output($v);
        }

        // If currently selected graph is not included in fields array
        // (it belongs to a group over which user has no permissions), then add
        // it to fields array.
        // This is aimed to avoid overriding this value when a user with
        // narrower permissions edits widget configuration.
        if ($this->values['vcId'] !== null
            && array_key_exists($this->values['vcId'], $fields) === false
        ) {
            $selected_vc = db_get_value(
                'name',
                'tlayout',
                'id',
                $this->values['vcId']
            );

            $fields[$this->values['vcId']] = $selected_vc;
        }

        return $fields;
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

        $node_id = $this->nodeId;
        

        $fields = $this->getVisualConsoles();

        

        // Visual console.
        $inputs[] = [
            'label'     => __('Visual console'),
            'arguments' => [
                'id'            => 'vcId',
                'type'          => 'select',
                'fields'        => $fields,
                'name'          => 'vcId',
                'selected'      => $values['vcId'],
                'return'        => true,
                'nothing'       => __('None'),
                'nothing_value' => 0,
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

        $values['vcId'] = \get_parameter('vcId', 0);

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

        $visualConsole = null;
        try {
            $visualConsole = VisualConsole::fromDB(
                ['id' => $this->values['vcId']]
            );
        } catch (\Throwable $e) {
            db_pandora_audit(
                AUDIT_LOG_ACL_VIOLATION,
                'Trying to access visual console without Id'
            );
            include 'general/noaccess.php';
            exit;
        }

        $size['width'] = ($size['width'] + 30);

        $ratio = $visualConsole->adjustToViewport($size);
        $visualConsoleData = $visualConsole->toArray();

        $uniq = uniqid();

        $output = '<div class="container-center">';
        // Style.
        $style = 'width:'.$visualConsoleData['width'].'px;';
        // Class.
        $class = 'visual-console-container-dashboard c-'.$uniq;
        // Id.
        $id = 'visual-console-container-'.$this->cellId;
        $output .= '<div style="'.$style.'" class="'.$class.'" id="'.$id.'">';
        $output .= '</div>';
        $output .= '</div>';

        // Check groups can access user.
        $aclUserGroups = [];
        if (users_can_manage_group_all('AR') === true) {
            $aclUserGroups = array_keys(
                users_get_groups(false, 'AR')
            );
        }

        $ignored_params['refr'] = '';
        \ui_require_javascript_file(
            'tiny_mce',
            'include/javascript/tiny_mce/'
        );
        \ui_require_javascript_file(
            'pandora_visual_console',
            'include/javascript/',
            true
        );
        \include_javascript_d3();
        \visual_map_load_client_resources();

        // Load Visual Console Items.
        $visualConsoleItems = VisualConsole::getItemsFromDB(
            $this->values['vcId'],
            $aclUserGroups,
            $ratio
        );

        $visualConsoleItems = array_reduce(
            $visualConsoleItems,
            function ($carry, $item) {
                $carry[] = $item->toArray();
                return $carry;
            },
            []
        );

        $settings = \json_encode(
            [
                'props'                      => $visualConsoleData,
                'items'                      => $visualConsoleItems,
                'baseUrl'                    => ui_get_full_url(
                    '/',
                    false,
                    false,
                    false
                ),
                'ratio'                      => $ratio,
                'size'                       => $size,
                'cellId'                     => (string) $this->cellId,
                'hash'                       => User::generatePublicHash(),
                'id_user'                    => $config['id_user'],
                'page'                       => 'include/ajax/visual_console.ajax',
                'uniq'                       => $uniq,
                'mobile_view_orientation_vc' => false,
            ]
        );

        $output .= '<script type="text/javascript">';
        $output .= '$(document).ready(function () {';
        $output .= 'dashboardLoadVC('.$settings.');';
        $output .= '});';
        $output .= '</script>';

        return $output;
    }


    /**
     * Get description.
     *
     * @return string.
     */
    public static function getDescription()
    {
        return __('Visual Console');
    }


    /**
     * Get Name.
     *
     * @return string.
     */
    public static function getName()
    {
        return 'maps_made_by_user';
    }


    /**
     * Return aux javascript code for forms.
     *
     * @return string
     */
    public function getFormJS()
    {
        ob_start();
        ?>
            $('#node').on('change', function() { 
                $.ajax({
                    method: "POST",
                    url: '<?php echo \ui_get_full_url('ajax.php'); ?>',
                    data: {
                        page: 'operation/dashboard/dashboard',
                        dashboardId: '<?php echo $this->dashboardId; ?>',
                        widgetId: '<?php echo $this->widgetId; ?>',
                        cellId: '<?php echo $this->cellId; ?>',
                        class: '<?php echo __CLASS__; ?>',
                        method: 'getVisualConsolesList',
                        nodeId: $('#node').val()
                    },
                    dataType: 'JSON',
                    success: function(data) {
                        $('#vcId').empty();
                        Object.entries(data).forEach(e => {
                            key = e[0];
                            value = e[1];
                            $('#vcId').append($('<option>').val(key).text(value))
                        });
                        if (Object.entries(data).length == 0) {
                            $('#vcId').append(
                                $('<option>')
                                    .val(-1)
                                    .text("<?php echo __('None'); ?>")
                            );
                        }
                    }
                })
            });
        <?php
        $js = ob_get_clean();
        return $js;
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
            'height' => 270,
        ];

        return $size;
    }


}
