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
global $config;

require_once $config['homedir'].'/godmode/wizards/Wizard.main.php';
ui_require_css_file('order_interpreter');

/**
 * Class OrderInterpreter.
 */
class OrderInterpreter extends Wizard
{

    /**
     * Allowed methods to be called using AJAX request.
     *
     * @var array
     */
    public $AJAXMethods = ['getResult'];

    /**
     * Url of controller.
     *
     * @var string
     */
    public $ajaxController;

    /**
     * Pages menu
     *
     * @var array
     */
    public $pages_menu;


    /**
     * Generates a JSON error.
     *
     * @param string $msg Error message.
     *
     * @return void
     */
    public function error($msg)
    {
        echo json_encode(
            ['error' => $msg]
        );
    }


    /**
     * Checks if target method is available to be called using AJAX.
     *
     * @param string $method Target method.
     *
     * @return boolean True allowed, false not.
     */
    public function ajaxMethod($method)
    {
        global $config;

        // Check access.
        check_login();

        return in_array($method, $this->AJAXMethods);
    }


    /**
     * Constructor.
     *
     * @param string $ajax_controller Controller.
     *
     * @return object
     * @throws Exception On error.
     */
    public function __construct(
        $ajax_controller='include/ajax/order_interpreter'
    ) {
        global $config;
        $this->ajaxController = $ajax_controller;

        $this->pages_menu = [
            [
                'name' => __('Tactical View'),
                'icon' => ui_get_full_url(
                    'images/menu/monitoring.svg'
                ),
                'url'  => ui_get_full_url(
                    'index.php?sec=view&sec2=operation/agentes/tactical'
                ),
                'acl'  => check_acl(
                    $config['id_user'],
                    0,
                    'AR'
                ) || check_acl(
                    $config['id_user'],
                    0,
                    'AW'
                ),
            ],
            [
                'name' => __('Agent Management'),
                'icon' => ui_get_full_url(
                    'images/menu/resources.svg'
                ),
                'url'  => ui_get_full_url(
                    'index.php?sec=gagente&sec2=godmode/agentes/modificar_agente'
                ),
                'acl'  => check_acl(
                    $config['id_user'],
                    0,
                    'AW'
                ) && check_acl(
                    $config['id_user'],
                    0,
                    'AD'
                ),
            ],
            [
                'name' => __('General Setup'),
                'icon' => ui_get_full_url(
                    'images/menu/settings.svg'
                ),
                'url'  => ui_get_full_url(
                    'index.php?sec=general&sec2=godmode/setup/setup&section=general'
                ),
                'acl'  => check_acl(
                    $config['id_user'],
                    0,
                    'PM'
                ) || is_user_admin(
                    $config['id_user']
                ),
            ],
            [
                'name' => __('List Alerts'),
                'icon' => ui_get_full_url(
                    'images/menu/alerts.svg'
                ),
                'url'  => ui_get_full_url(
                    'index.php?sec=galertas&sec2=godmode/alerts/alert_list'
                ),
                'acl'  => check_acl(
                    $config['id_user'],
                    0,
                    'LW'
                )
                || check_acl(
                    $config['id_user'],
                    0,
                    'AD'
                )
                || check_acl(
                    $config['id_user'],
                    0,
                    'LM'
                ),
            ],
            [
                'name' => __('View Events'),
                'icon' => ui_get_full_url(
                    'images/menu/events.svg'
                ),
                'url'  => ui_get_full_url(
                    'index.php?sec=eventos&sec2=operation/events/events'
                ),
                'acl'  => check_acl(
                    $config['id_user'],
                    0,
                    'ER'
                ) ||
                check_acl(
                    $config['id_user'],
                    0,
                    'EW'
                ) ||
                check_acl(
                    $config['id_user'],
                    0,
                    'EM'
                ),
            ],
            [
                'name' => __('Dashboard'),
                'icon' => ui_get_full_url(
                    'images/menu/reporting.svg'
                ),
                'url'  => ui_get_full_url(
                    'index.php?sec=reporting&sec2=operation/dashboard/dashboard'
                ),
                'acl'  => check_acl(
                    $config['id_user'],
                    0,
                    'RR'
                ),
            ],
            [
                'name' => __('Visual Console'),
                'icon' => ui_get_full_url(
                    'images/menu/network.svg'
                ),
                'url'  => ui_get_full_url(
                    'index.php?sec=network&sec2=godmode/reporting/map_builder'
                ),
                'acl'  => check_acl(
                    $config['id_user'],
                    0,
                    'VR'
                ),
            ],
            [
                'name' => __('Manage Servers'),
                'icon' => ui_get_full_url(
                    'images/menu/servers.svg'
                ),
                'url'  => ui_get_full_url(
                    'index.php?sec=gservers&sec2=godmode/servers/modificar_server'
                ),
                'acl'  => check_acl(
                    $config['id_user'],
                    0,
                    'AW'
                ),
            ],
            [
                'name' => __('Edit User'),
                'icon' => ui_get_full_url(
                    'images/menu/users.svg'
                ),
                'url'  => ui_get_full_url(
                    'index.php?sec=workspace&sec2=operation/users/user_edit'
                ),
                'acl'  => true,
            ],
            [
                'name' => __('Tree View'),
                'icon' => ui_get_full_url(
                    'images/menu/monitoring.svg'
                ),
                'url'  => ui_get_full_url(
                    'index.php?sec=view&sec2=operation/tree'
                ),
                'acl'  => true,
            ],
            [
                'name' => __('Network Component'),
                'icon' => ui_get_full_url(
                    'images/menu/configuration.svg'
                ),
                'url'  => ui_get_full_url(
                    'index.php?sec=gmodules&sec2=godmode/modules/manage_network_components'
                ),
                'acl'  => check_acl(
                    $config['id_user'],
                    0,
                    'PM'
                ),
            ],
            [
                'name' => __('Task List'),
                'icon' => ui_get_full_url(
                    'images/menu/discovery.svg'
                ),
                'url'  => ui_get_full_url(
                    'index.php?sec=discovery&sec2=godmode/servers/discovery&wiz=tasklist'
                ),
                'acl'  => check_acl(
                    $config['id_user'],
                    0,
                    'AR'
                )
                || check_acl(
                    $config['id_user'],
                    0,
                    'AW'
                )
                || check_acl(
                    $config['id_user'],
                    0,
                    'AM'
                )
                || check_acl(
                    $config['id_user'],
                    0,
                    'RR'
                )
                || check_acl(
                    $config['id_user'],
                    0,
                    'RW'
                )
                || check_acl(
                    $config['id_user'],
                    0,
                    'RM'
                )
                || check_acl(
                    $config['id_user'],
                    0,
                    'PM'
                ),
            ],
            [
                'name' => __('Manage Agent Groups'),
                'icon' => ui_get_full_url(
                    'images/menu/users.svg'
                ),
                'url'  => ui_get_full_url(
                    'index.php?sec=gagente&sec2=godmode/groups/group_list&tab=groups'
                ),
                'acl'  => check_acl(
                    $config['id_user'],
                    0,
                    'PM'
                ),
            ],

        ];

    }


    /**
     * Method to print order interpreted on header search input.
     *
     * @return void
     */
    public function getResult()
    {
        global $config;

        // Take value from input search.
        $text = get_parameter('text', '');
        $iterator = 0;
        $more_results = 0;

        if ($text !== '') {
            echo '<div class="show_result_interpreter">';
            echo '<ul id="result_items">';

            foreach ($this->pages_menu as $key => $value) {
                if (preg_match(
                    '/.*'.io_safe_output($text).'.*/i',
                    __('GO TO %s', $value['name'])
                ) && $value['acl']
                ) {
                    if ($iterator <= 9) {
                        echo '<li class="list_found" name="'.$iterator.'" id="'.$iterator.'">';
                        echo '
                        <span class=""> Go to </span> &nbsp;
                        <img src="'.$this->pages_menu[$key]['icon'].'">';
                        echo '&nbsp;
                        <a href="'.$this->pages_menu[$key]['url'].'">
                        '.$value['name'].'</a><br>';
                    }

                    $iterator++;

                    if ($iterator > 10) {
                        $more_results++;
                    }
                }
            }

            if ($iterator > 9) {
                echo '</li>';
            }

            echo $this->loadJS();
            echo '</ul>';
            if ($iterator > 10) {
                echo '<div class="more_results"><span class="">
                  + '.$more_results.' '.__('Results found').'</span></div>';
            }

            if ($iterator === 0) {
                echo '<span class="">'.__('Press enter to search').'</span>';
            }

            echo '</div>';
        }
    }


    /**
     * Load JS content.
     * function to create JS actions.
     *
     * @return string HTML code for javascript functionality.
     */
    public function loadJS()
    {
        ob_start();
        ?>
    <script type="text/javascript">
    
    </script>   
        <?php
        return ob_get_clean();
    }


}
