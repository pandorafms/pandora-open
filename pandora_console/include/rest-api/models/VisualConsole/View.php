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

// Begin.
namespace Models\VisualConsole;
use Models\VisualConsole\Container as VisualConsole;

define('__DEBUG', 1);

global $config;
require_once $config['homedir'].'/include/class/HTML.class.php';
/**
 * Global HTML generic class.
 */
class View extends \HTML
{


    /**
     * Tabs.
     *
     * @return string
     */
    public function loadTabs()
    {
        $type = (int) \get_parameter('type', 0);
        $itemId = (int) \get_parameter('itemId', 0);
        $vCId = (int) \get_parameter('vCId', 0);

        $url = ui_get_full_url(false, false, false, false);
        $url .= 'ajax.php?page=include/rest-api/index';
        $url .= '&loadtabs=2';
        $url .= '&type='.$type;
        $url .= '&itemId='.$itemId;
        $url .= '&vCId='.$vCId;

        $tabs = [
            [
                'name'  => __('Label settings'),
                'id'    => 'tab-label',
                'href'  => $url.'&tabSelected=label',
                'img'   => 'tag@svg.svg',
                'class' => 'main_menu_icon invert_filter',
            ],
            [
                'name'  => __('General settings'),
                'id'    => 'tab-general',
                'href'  => $url.'&tabSelected=general',
                'img'   => 'configuration@svg.svg',
                'class' => 'main_menu_icon invert_filter',
            ],
            [
                'name'  => __('Specific settings'),
                'id'    => 'tab-specific',
                'href'  => $url.'&tabSelected=specific',
                'img'   => 'incremental-data@svg.svg',
                'class' => 'main_menu_icon invert_filter',
            ],
        ];

        $activetabs = 2;
        if ($type === LABEL) {
            $activetabs = 0;
        } else if ($type === LINE_ITEM
            || $type === NETWORK_LINK
        ) {
            $activetabs = 0;
            $tabs = [
                [
                    'name'  => __('Specific settings'),
                    'id'    => 'tab-specific',
                    'href'  => $url.'&tabSelected=specific',
                    'img'   => 'responses.svg',
                    'class' => 'main_menu_icon invert_filter',
                ],
            ];
        } else if ($type === BOX_ITEM || $type === COLOR_CLOUD || $type === ODOMETER) {
            $activetabs = 1;
            $tabs = [
                [
                    'name'  => __('General settings'),
                    'id'    => 'tab-general',
                    'href'  => $url.'&tabSelected=general',
                    'img'   => 'edit.svg',
                    'class' => 'main_menu_icon invert_filter',
                ],
                [
                    'name'  => __('Specific settings'),
                    'id'    => 'tab-specific',
                    'href'  => $url.'&tabSelected=specific',
                    'img'   => 'responses.svg',
                    'class' => 'main_menu_icon invert_filter',
                ],
            ];
        }

        $result = html_print_tabs($tabs);

        // TODO:Change other place.
        ui_require_javascript_file('tinymce', 'vendor/tinymce/tinymce/');
        $js = '<script>
	            $(function() {
                    $tabs = $( "#html-tabs" ).tabs({
                        beforeLoad: function (event, ui) {
                            if (ui.tab.data("loaded")) {
                                event.preventDefault();
                                return;
                            }
                            ui.ajaxSettings.cache = false;
                            ui.jqXHR.done(function() {
                                ui.tab.data( "loaded", true );
                            });
                            ui.jqXHR.fail(function () {
                                ui.panel.html(
                                    "'.__('The data could not be loaded. Please reload the page or try again later.').'"
                                );
                            });
                        },
                        load: function( event, ui ) {
                            var active = $( "#html-tabs" ).tabs( "option", "active" );
                            if (active === 0) {
                                // Remove.
                                UndefineTinyMCE("#textarea_label");
                                // Initialice.
                                defineTinyMCE("#textarea_label");
                            }
                        },
                        active: '.$activetabs.'
                    });';
        $js .= '});';
        $js .= '</script>';

        return $result.$js;
    }


    /**
     * Generates a form for you <3
     *
     * @return string HTML code for Form.
     *
     * @throws \Exception On error.
     */
    public function loadForm()
    {
        // Load desired form based on item type.
        $values = [];
        $type = get_parameter('type', null);
        $tabSelected = get_parameter('tabSelected', 'label');
        $itemId = (int) get_parameter('itemId', 0);
        $vCId = (int) \get_parameter('vCId', 0);

        $itemClass = VisualConsole::getItemClass($type);

        if ($itemClass === null || empty($itemClass)) {
            throw new \Exception(__('Item type not valid ['.$type.']'));
        }

        if (\method_exists($itemClass, 'getFormInputs') === false) {
            throw new \Exception(
                __('Item type has no getFormInputs method ['.$type.']')
            );
        }
        $form = [
            'action' => '#',
            'method' => 'POST',
            'id'     => 'itemForm-'.$tabSelected,
            'class'  => 'discovery modal',
            'extra'  => 'novalidate',
        ];

        if ($itemId !== 0) {
            $item = VisualConsole::getItemFromDB($itemId);
            $values = $item->toArray();
        } else {
            $values['type'] = $type;
        }

        $values['tabSelected'] = $tabSelected;
        $values['vCId'] = $vCId;

        // Retrieve inputs.
        $inputs = $itemClass::getFormInputs($values);
        
        // Generate Form.
        $form = $this->printForm(
            [
                'form'   => $form,
                'inputs' => $inputs,
            ],
            true
        );

        return $form;

    }


    /**
     * Process a form.
     *
     * @return string JSON response.
     */
    public function processForm()
    {
        global $config;

        $result = null;

        // Inserted data in new item.
        $vCId = \get_parameter('vCId', 0);
        $type = \get_parameter('type', null);
        $itemId = (int) \get_parameter('itemId', 0);

        // Type.
        $data['type'] = $type;

        // Page Label for each item.
        $tabLabel = (bool) \get_parameter('tabLabel', false);
        if ($tabLabel === true) {
            $data['label'] = \get_parameter('label');
            $data['labelPosition'] = \get_parameter('labelPosition');
        }

        // Page general for each item.
        $tabGeneral = (bool) \get_parameter('tabGeneral', false);
        if ($tabGeneral === true) {
            // Size.
            $data['width'] = \get_parameter('width');
            $data['height'] = \get_parameter('height');

            // Position.
            $data['x'] = \get_parameter('x');
            $data['y'] = \get_parameter('y');

            // Enable link.
            $data['isLinkEnabled'] = \get_parameter_switch('isLinkEnabled');

            // Show on top.
            $data['isOnTop'] = \get_parameter_switch('isOnTop');

            // Parent.
            $data['parentId'] = \get_parameter('parentId');

            // ACL.
            $data['aclGroupId'] = \get_parameter('aclGroupId');

            // Cache.
            $data['cacheExpiration_select'] = \get_parameter(
                'cacheExpiration_select'
            );
            $data['cacheExpiration_text'] = \get_parameter(
                'cacheExpiration_text'
            );
            $data['cacheExpiration'] = \get_parameter('cacheExpiration');
            $data['cacheExpiration_units'] = \get_parameter(
                'cacheExpiration_units'
            );
        } else {
            // Only Create, settings default values if not enter tab general.
            if ($itemId === 0 && $type != LINE_ITEM && $type != NETWORK_LINK) {
                $class = VisualConsole::getItemClass((int) $type);
                $data = $class::getDefaultGeneralValues($data);
            }
        }

        // Linked other VC.
        $data['linkedLayoutId'] = \get_parameter(
            'linkedLayoutId',
            0
        );
        $data['linkedLayoutNodeId'] = \get_parameter(
            'linkedLayoutNodeId',
            0
        );
        $data['linkedLayoutStatusType'] = \get_parameter(
            'linkedLayoutStatusType',
            'default'
        );
        $data['linkedLayoutStatusTypeWeight'] = \get_parameter(
            'linkedLayoutStatusTypeWeight'
        );
        $data['linkedLayoutStatusTypeCriticalThreshold'] = \get_parameter(
            'linkedLayoutStatusTypeCriticalThreshold'
        );
        $data['linkedLayoutStatusTypeWarningThreshold'] = \get_parameter(
            'linkedLayoutStatusTypeWarningThreshold'
        );

        // Page specific data for each item.
        switch ($type) {
            case STATIC_GRAPH:
                $data['imageSrc'] = \get_parameter('imageSrc');
                $data['agentId'] = \get_parameter('agentId');
                $data['moduleId'] = \get_parameter('moduleId');
                $data['showLastValueTooltip'] = \get_parameter(
                    'showLastValueTooltip'
                );
            break;

            case MODULE_GRAPH:
                $data['backgroundType'] = \get_parameter('backgroundType');
                $type = \get_parameter('choosetype');
                $data['agentId'] = \get_parameter('agentId');
                $data['moduleId'] = \get_parameter('moduleId');
                $data['customGraphId'] = \get_parameter('customGraphId');
                if ($type === 'module') {
                    $data['customGraphId'] = 0;
                }

                $data['graphType'] = \get_parameter('graphType');
                $data['showLegend'] = \get_parameter_switch('showLegend');
                $data['period'] = \get_parameter('period');
                $data['periodicityChart'] = \get_parameter_switch('periodicityChart');
                $data['periodMaximum'] = \get_parameter_switch('periodMaximum');
                $data['periodMinimum'] = \get_parameter_switch('periodMinimum');
                $data['periodAverage'] = \get_parameter_switch('periodAverage');
                $data['periodSummatory'] = \get_parameter_switch('periodSummatory');
                $data['periodSliceChart'] = \get_parameter('periodSliceChart');
                $data['periodMode'] = \get_parameter('periodMode');
            break;

            case SIMPLE_VALUE:
            case SIMPLE_VALUE_MAX:
            case SIMPLE_VALUE_MIN:
            case SIMPLE_VALUE_AVG:
                $data['agentId'] = \get_parameter('agentId');
                $data['moduleId'] = \get_parameter('moduleId');
                $data['processValue'] = \get_parameter('processValue');
                $data['period'] = \get_parameter('period');
                // Insert line default position ball end.
                if ($itemId === 0 && empty($data['label']) === true) {
                    $data['label'] = '(_value_)';
                }
            break;

            case PERCENTILE_BAR:
            case PERCENTILE_BUBBLE:
            case CIRCULAR_PROGRESS_BAR:
            case CIRCULAR_INTERIOR_PROGRESS_BAR:
                $data['percentileType'] = \get_parameter('percentileType');
                $data['minValue'] = \get_parameter('minValue');
                $data['maxValue'] = \get_parameter('maxValue');
                $data['valueType'] = \get_parameter('valueType');
                $data['color'] = \get_parameter('color');
                $data['labelColor'] = \get_parameter('labelColor');
                $data['agentId'] = \get_parameter('agentId');
                $data['moduleId'] = \get_parameter('moduleId');
            break;

            case ICON:
                $data['imageSrc'] = \get_parameter('imageSrc');
            break;

            case GROUP_ITEM:
                $data['imageSrc'] = \get_parameter('imageSrc');
                $data['recursiveGroup'] = \get_parameter_switch(
                    'recursiveGroup',
                    0
                );
                $data['showStatistics'] = \get_parameter_switch(
                    'showStatistics',
                    0
                );
                $data['groupId'] = \get_parameter('groupId');
            break;

            case BOX_ITEM:
                $data['borderColor'] = \get_parameter('borderColor');
                $data['borderWidth'] = \get_parameter('borderWidth');
                $data['fillColor'] = \get_parameter('fillColor');
                $data['fillTransparent'] = \get_parameter_switch(
                    'fillTransparent'
                );
            break;

            case LINE_ITEM:
                $data['borderColor'] = \get_parameter('borderColor');
                $data['borderWidth'] = \get_parameter('borderWidth');
                $data['isOnTop'] = \get_parameter_switch('isOnTop');
                // Insert line default position ball end.
                if ($itemId === 0) {
                    $data['height'] = 100;
                    $data['width'] = 100;
                }
            break;

            case AUTO_SLA_GRAPH:
                $data['agentId'] = \get_parameter('agentId');
                $data['agentAlias'] = \get_parameter('agentAlias');
                $data['moduleId'] = \get_parameter('moduleId');
                $data['maxTime'] = \get_parameter('maxTime');
                $data['legendColor'] = \get_parameter('legendColor');
            break;

            case DONUT_GRAPH:
                $data['agentId'] = \get_parameter('agentId');
                $data['moduleId'] = \get_parameter('moduleId');
                $data['legendBackgroundColor'] = \get_parameter(
                    'legendBackgroundColor',
                    '#ffffff'
                );
            break;

            case BARS_GRAPH:
                $data['backgroundColor'] = \get_parameter('backgroundColor');
                $data['typeGraph'] = \get_parameter('typeGraph');
                $data['gridColor'] = \get_parameter('gridColor');
                $data['agentId'] = \get_parameter('agentId');
                $data['moduleId'] = \get_parameter('moduleId');
            break;

            case CLOCK:
                $data['clockType'] = \get_parameter('clockType');
                $data['clockFormat'] = \get_parameter('clockFormat');
                $data['width'] = \get_parameter('width');
                $data['clockTimezone'] = \get_parameter('clockTimezone');
                $data['color'] = \get_parameter('color');
            break;

            case COLOR_CLOUD:
                $data['agentId'] = \get_parameter('agentId');
                $data['moduleId'] = \get_parameter('moduleId');
                $data['defaultColor'] = \get_parameter('defaultColor');

                $rangeFrom = \get_parameter('rangeFrom');
                $rangeTo = \get_parameter('rangeTo');
                $rangeColor = \get_parameter('rangeColor');

                $arrayRangeColor = [];
                foreach ($rangeFrom as $key => $value) {
                    $arrayRangeColor[$key] = [
                        'color'     => $rangeColor[$key],
                        'fromValue' => $value,
                        'toValue'   => $rangeTo[$key],
                    ];
                }

                $data['colorRanges'] = $arrayRangeColor;
            break;

            case SERVICE:
                $imageSrc = \get_parameter('imageSrc');
                if ($imageSrc === '0') {
                    $imageSrc = '';
                }

                $data['imageSrc'] = $imageSrc;
                $data['serviceId'] = \get_parameter('serviceId');
            break;

            case LABEL:
                $data['isLinkEnabled'] = true;
            break;

            case NETWORK_LINK:
                $data['borderColor'] = \get_parameter('borderColor');
                $data['borderWidth'] = \get_parameter('borderWidth');
                $data['isOnTop'] = \get_parameter_switch('isOnTop');
                // Insert line default position ball end.
                if ($itemId === 0) {
                    $data['height'] = 100;
                    $data['width'] = 100;
                }
            break;

            case ODOMETER:
                $data['agentId'] = \get_parameter('agentId');
                $data['agentAlias'] = \get_parameter('agentAlias');
                $data['moduleId'] = \get_parameter('moduleId');
                $data['titleColor'] = \get_parameter('titleColor');
                $data['title'] = \get_parameter('title');
                if ($itemId === 0) {
                    $data['height'] = 150;
                    $data['width'] = 300;
                }
            break;

            case BASIC_CHART:
                $data['agentId'] = \get_parameter('agentId');
                $data['agentAlias'] = \get_parameter('agentAlias');
                $data['moduleId'] = \get_parameter('moduleId');
                $data['period'] = \get_parameter('period');
                $data['moduleNameColor'] = \get_parameter('moduleNameColor');
                if ($itemId === 0) {
                    $data['height'] = 110;
                    $data['width'] = 375;
                }
            break;

            default:
                // Not posible.
            break;
        }

        if (isset($itemId) === false || $itemId === 0) {
            // CreateVC.
            $class = VisualConsole::getItemClass((int) $data['type']);
            try {
                // Save the new item.
                $data['id_layout'] = $vCId;
                $itemId = $class::create($data);
            } catch (\Exception $e) {
                // Bad params.
                echo $e->getMessage();
                if (__DEBUG === 1) {
                    echo '<pre>'.$e->getTraceAsString().'</pre>';
                }

                http_response_code(400);
                return false;
            }

            // Extract data new item inserted.
            try {
                $item = VisualConsole::getItemFromDB($itemId);
                $result = $item->toArray();
            } catch (\Exception $e) {
                // Bad params.
                echo $e->getMessage();
                if (__DEBUG === 1) {
                    echo '<pre>'.$e->getTraceAsString().'</pre>';
                }

                http_response_code(400);
                return false;
            }
        } else {
            // UpdateVC.
            try {
                $item = VisualConsole::getItemFromDB($itemId);
            } catch (\Exception $e) {
                // Bad params.
                echo $e->getMessage();
                if (__DEBUG === 1) {
                    echo '<pre>'.$e->getTraceAsString().'</pre>';
                }

                http_response_code(400);
                return false;
            }

            $itemData = $item->toArray();
            $itemType = $itemData['type'];
            $itemAclGroupId = $itemData['aclGroupId'];

            // ACL.
            $aclRead = check_acl($config['id_user'], $itemAclGroupId, 'VR');
            $aclWrite = check_acl($config['id_user'], $itemAclGroupId, 'VW');
            $aclManage = check_acl($config['id_user'], $itemAclGroupId, 'VM');

            if (!$aclRead && !$aclWrite && !$aclManage) {
                db_pandora_audit(
                    AUDIT_LOG_ACL_VIOLATION,
                    'Trying to access visual console without group access'
                );
                http_response_code(403);
                return false;
            }

            // Check also the group Id for the group item.
            if ($itemType === GROUP_ITEM) {
                $itemGroupId = $itemData['groupId'];
                // ACL.
                $aclRead = check_acl($config['id_user'], $itemGroupId, 'VR');
                $aclWrite = check_acl($config['id_user'], $itemGroupId, 'VW');
                $aclManage = check_acl($config['id_user'], $itemGroupId, 'VM');

                if (!$aclRead && !$aclWrite && !$aclManage) {
                    db_pandora_audit(
                        AUDIT_LOG_ACL_VIOLATION,
                        'Trying to access visual console without group access'
                    );
                    http_response_code(403);
                    return false;
                }
            }

            if (is_array($data) === true && empty($data) === false) {
                try {
                    // Save the new item.
                    $data['id_layout'] = $vCId;
                    $data['id'] = $itemId;
                    $item->save($data);
                    $result = $item->toArray();
                } catch (\Throwable $th) {
                    // There is no item in the database.
                    echo false;
                    return false;
                }
            }
        }

        return json_encode($result);
    }


    /**
     * Returns a popup for networkLink viewer.
     *
     * @return void
     *
     * phpcs:disable Squiz.Commenting.FunctionCommentThrowTag.Missing
     */
    public function networkLinkPopup()
    {
        global $config;

        try {
            include_once $config['homedir'].'/include/functions_graph.php';
            $item_idFrom = get_parameter('from');
            $item_idTo = get_parameter('to');

            $itemFrom = db_get_row_filter(
                'tlayout_data',
                ['id' => $item_idFrom]
            );

            $itemTo = db_get_row_filter(
                'tlayout_data',
                ['id' => $item_idTo]
            );

            // Interface chart base configuration.
            $params = [
                'period'  => SECONDS_6HOURS,
                'width'   => '90%',
                'height'  => 150,
                'date'    => time(),
                'homeurl' => $config['homeurl'],
            ];

            if ($config['type_interface_charts'] == 'line') {
                $stacked = CUSTOM_GRAPH_LINE;
            } else {
                $stacked = CUSTOM_GRAPH_AREA;
            }

            $params_combined = [
                'weight_list'    => [],
                'projection'     => false,
                'from_interface' => true,
                'return'         => 0,
                'stacked'        => $stacked,
            ];

            // Interface FROM.

            $params['server_id'] = null;

            $from = new \PandoraFMS\Module((int) $itemFrom['id_agente_modulo']);

            if ((bool) $from->isInterfaceModule() === true) {
                $interface_name = $from->getInterfaceName();
                if ($interface_name !== null) {
                    $data = $from->agent()->getInterfaceMetrics(
                        $interface_name
                    );

                    echo '<h3 class="center">'.__('NetworkLink from').'</h3>';
                    echo '<div class="margin-top-10 interface-status from w90p centered flex-row-vcenter">';
                    ui_print_module_status($data['status']->lastStatus());
                    echo '<span class="margin-left-1">';
                    echo __('Interface %s status', $interface_name);
                    echo '</span>';
                    echo '</div>';

                    $interface_traffic_modules = [
                        __('In')  => $data['in']->id_agente_modulo(),
                        __('Out') => $data['out']->id_agente_modulo(),
                    ];

                    $params['unit_name'] = array_fill(
                        0,
                        count($interface_traffic_modules),
                        $config['interface_unit']
                    );

                    $params_combined['labels'] = array_keys(
                        $interface_traffic_modules
                    );

                    $params_combined['modules_series'] = array_values(
                        $interface_traffic_modules
                    );

                    // Graph.
                    echo '<div id="stat-win-interface-graph from">';

                    

                    \graphic_combined_module(
                        array_values($interface_traffic_modules),
                        $params,
                        $params_combined
                    );

                    echo '</div>';
                }
            } else {
                
            }


            $params['server_id'] = null;

            $to = new \PandoraFMS\Module((int) $itemTo['id_agente_modulo']);

            if ((bool) $to->isInterfaceModule() === true) {
                $interface_name = $to->getInterfaceName();
                if ($interface_name !== null) {
                    $data = $to->agent()->getInterfaceMetrics(
                        $interface_name
                    );

                    echo '<h3 class="center">'.__('NetworkLink to').'</h3>';
                    echo '<div class="interface-status from w90p centered flex-row-vcenter">';
                    ui_print_module_status($data['status']->lastStatus());
                    echo '<span class="margin-left-1">';
                    echo __('Interface %s status', $interface_name);
                    echo '</span>';
                    echo '</div>';

                    $interface_traffic_modules = [
                        __('In')  => $data['in']->id_agente_modulo(),
                        __('Out') => $data['out']->id_agente_modulo(),
                    ];

                    $params['unit_name'] = array_fill(
                        0,
                        count($interface_traffic_modules),
                        $config['interface_unit']
                    );

                    $params_combined['labels'] = array_keys(
                        $interface_traffic_modules
                    );

                    $params_combined['modules_series'] = array_values(
                        $interface_traffic_modules
                    );

                    // Graph.
                    echo '<div id="stat-win-interface-graph to">';

                    

                    \graphic_combined_module(
                        array_values($interface_traffic_modules),
                        $params,
                        $params_combined
                    );

                    echo '</div>';
                }
            } else {
                
            }
        } catch (\Exception $e) {
            echo __('Failed to generate charts: %s', $e->getMessage());
        }
    }


}
