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

class Home
{

    protected $global_search = '';

    protected $pagesItems = [];


    function __construct()
    {
        $this->global_search = '';
    }


    public function getPagesItems()
    {
        if (empty($this->pagesItems)) {
            $this->loadPagesItems();
        }

        return $this->pagesItems;
    }


    protected function loadPagesItems()
    {
        $system = System::getInstance();

        $items = [];

        // In home.
        $items['tactical'] = [
            'name'      => __('Tactical view'),
            'filename'  => 'tactical.php',
            'menu_item' => true,
            'icon'      => 'ui-icon-menu-op_monitoring ui-widget-icon-floatbeginning ui-icon-menu-square',
        ];
        $items['events'] = [
            'name'      => __('Events'),
            'filename'  => 'events.php',
            'menu_item' => true,
            'icon'      => 'ui-icon-menu-op_events ui-widget-icon-floatbeginning ui-icon-menu-square',
        ];
        $items['groups'] = [
            'name'      => __('Groups'),
            'filename'  => 'groups.php',
            'menu_item' => true,
            'icon'      => 'ui-icon-menu-group ui-widget-icon-floatbeginning ui-icon-menu-square',
        ];

        // Show Visual consoles only if new system is enabled.
        $items['visualmaps'] = [
            'name'      => __('Visual consoles'),
            'filename'  => 'visualmaps.php',
            'menu_item' => true,
            'icon'      => 'ui-icon-menu-visual_console ui-widget-icon-floatbeginning ui-icon-menu-square',
        ];

        $items['alerts'] = [
            'name'      => __('Alerts'),
            'filename'  => 'alerts.php',
            'menu_item' => true,
            'icon'      => 'ui-icon-menu-op_alerts ui-widget-icon-floatbeginning ui-icon-menu-square',
        ];

        $items['agents'] = [
            'name'      => __('Agents'),
            'filename'  => 'agents.php',
            'menu_item' => true,
            'icon'      => 'ui-icon-menu-agent_ms ui-widget-icon-floatbeginning ui-icon-menu-square',
        ];

        $items['modules'] = [
            'name'      => __('Modules'),
            'filename'  => 'modules.php',
            'menu_item' => true,
            'icon'      => 'ui-icon-menu-brick ui-widget-icon-floatbeginning ui-icon-menu-square',
        ];

        $items['server_status'] = [
            'name'      => __('Server status'),
            'filename'  => 'server_status.php',
            'menu_item' => true,
            'icon'      => 'ui-icon-menu-server-status ui-widget-icon-floatbeginning ui-icon-menu-square',
        ];

        // Not in home.
        $items['agent'] = [
            'name'      => __('Agent'),
            'filename'  => 'agent.php',
            'menu_item' => false,
            'icon'      => '',
        ];
        $items['module_graph'] = [
            'name'      => __('Module graph'),
            'filename'  => 'module_graph.php',
            'menu_item' => false,
            'icon'      => '',
        ];

        $this->pagesItems = $items;
    }


    protected function loadButtons($ui)
    {
        if (empty($this->pagesItems) && $this->pagesItems !== false) {
            $this->loadPagesItems();
        }

        $ui->contentAddHtml('<div class="menu-buttons">');
        foreach ($this->pagesItems as $page => $data) {
            if ($data['menu_item']) {
                $options = [
                    'icon'  => $data['icon'],
                    'pos'   => 'right',
                    'text'  => $data['name'],
                    'href'  => "index.php?page=$page",
                    'class' => $data['class'],
                ];
                $ui->contentAddHtml($ui->createButton($options));
            }
        }

        $ui->contentAddHtml('</div>');
    }


    public function show($error=false)
    {
        $system = System::getInstance();
        $ui = Ui::getInstance();

        include_once $system->getConfig('homedir').'/include/functions_graph.php';

        $ui->createPage();
        if ($system->getRequest('hide_logout', 0)) {
            $left_button = null;
        } else {
            $left_button = $ui->createHeaderButton(
                [
                    'icon'  => 'ui-icon-logout',
                    'pos'   => 'left',
                    'text'  => __('Logout'),
                    'href'  => 'index.php?action=logout',
                    'class' => 'header-button-left logout-text',
                ]
            );
        }

        $user_logged = '';
        $id_user = $system->getConfig('id_user');
        if (!empty($id_user)) {
            $user_logged = "<span id=\"user_logged\">$id_user</span>";
        }

        $ui->createHeader(__('Home'), $left_button, $user_logged);
        $ui->showFooter(false);
        $ui->beginContent();
        $ui->contentAddHtml('<div class="search-home">');
            $ui->beginForm('index.php?page=agents');
            $options = [
                'name'        => 'free_search',
                'value'       => $this->global_search,
                'placeholder' => __('Agent search'),
            ];
            $ui->formAddInputSearch($options);
            $ui->endForm();
            $ui->contentAddHtml('</div>');

            // List of buttons
            $this->loadButtons($ui);

            if (!empty($error)) {
                $error['dialog_id'] = 'error-dialog';
                $ui->addDialog($error);
            }

            $ui->endContent();
            $ui->showPage();
    }


}
