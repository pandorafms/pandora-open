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

require_once '../include/functions_users.php';
require_once '../include/functions_groupview.php';

class Groups
{

    private $correct_acl = false;

    private $acl = 'AR';

    private $groups = [];

    private $status = [];


    function __construct()
    {
        $system = System::getInstance();
        if ($system->checkACL($this->acl)) {
            $this->correct_acl = true;
            $this->groups = $this->getListGroups();
        } else {
            $this->correct_acl = false;
        }
    }


    public function show()
    {
        if (!$this->correct_acl) {
            $this->show_fail_acl();
        } else {
            $this->show_group();
        }
    }


    private function show_fail_acl()
    {
        $error['type'] = 'onStart';
        $error['title_text'] = __('You don\'t have access to this page');
        $error['content_text'] = System::getDefaultACLFailText();
        $home = new Home();

        $home->show($error);
    }


    private function show_group()
    {
        $ui = Ui::getInstance();
        $ui->createPage();
        $ui->createDefaultHeader(
            __('Groups'),
            $ui->createHeaderButton(
                [
                    'icon'  => 'ui-icon-back',
                    'pos'   => 'left',
                    'text'  => __('Back'),
                    'href'  => 'index.php?page=home',
                    'class' => 'header-button-left',
                ]
            )
        );
        $ui->showFooter(false);
        $ui->beginContent();

        $ui->contentAddHtml('<div class="list_groups" data-role="collapsible-set" data-theme="a" data-content-theme="d">');
            $count = 0;
            $url_agent = 'index.php?page=agents&group=%s&status=%s';
            $url_modules = 'index.php?page=modules&group=%s&status=%s';

        foreach ($this->groups as $group) {
            // Calculate entire row color.
            if ($group['_monitors_alerts_fired_'] > 0) {
                $color_class = 'group_view_alrm';
                $color = ' #f7931e';
                $status_image = ui_print_status_image('agent_alertsfired_ball.png', '', true);
            } else if ($group['_monitors_critical_'] > 0) {
                $color_class = 'group_view_crit';
                $color = ' #f85858';
                $status_image = ui_print_status_image('agent_critical_ball.png', '', true);
            } else if ($group['_monitors_warning_'] > 0) {
                $color_class = 'group_view_warn';
                $color = '#ffea59';
                $status_image = ui_print_status_image('agent_warning_ball.png', '', true);
            } else if ($group['_monitors_ok_'] > 0) {
                $color_class = 'group_view_ok';
                $color = '#6eb432';
                $status_image = ui_print_status_image('agent_ok_ball.png', '', true);
            } else if (($group['_monitors_unknown_'] > 0) || ($group['_agents_unknown_'] > 0)) {
                $color_class = 'group_view_unk';
                $color = '#999999';
                $status_image = ui_print_status_image('agent_no_monitors_ball.png', '', true);
            } else {
                $color_class = '';
                $color = '';
                $status_image = ui_print_status_image('agent_no_data_ball.png', '', true);
            }

            // Show agent counter by states.
            $agents_counter = '['.$group['_total_agents_'];

            if ($group['_monitors_alerts_fired_'] > 0) {
                $agents_counter .= ':';
                $agents_counter .= '<span class="color-orange">'.$group['_monitors_alerts_fired_'].'</span>';
            }

            if ($group['_monitors_critical_'] > 0) {
                $agents_counter .= ':';
                $agents_counter .= '<span class="color-red">'.$group['_monitors_critical_'].'</span>';
            }

            if ($group['_agents_warning_'] > 0) {
                $agents_counter .= ':';
                $agents_counter .= '<span class="color-yellow">'.$group['_agents_warning_'].'</span>';
            }

            if ($group['_agents_unknown_'] > 0) {
                $agents_counter .= ':';
                $agents_counter .= '<span class="color-grey">'.$group['_agents_unknown_'].'</span>';
            }

            if ($group['_agents_not_init_'] > 0) {
                $agents_counter .= ':';
                $agents_counter .= '<span class="color-blue">'.$group['_agents_not_init_'].'</span>';
            }

            if ($group['_agents_ok_'] > 0) {
                $agents_counter .= ':';
                $agents_counter .= '<span class="color-green">'.$group['_agents_ok_'].'</span>';
            }

            $agents_counter .= ']';

            if ($group['_iconImg_'] !== null) {
                $img_group = html_print_image(
                    'images/'.groups_get_icon($group['_id_']),
                    true,
                    false,
                    false,
                    false,
                    false,
                    true
                );
            }

            $group['_iconImg_'] = ($group['_iconImg_'] == '') ? 'world.png' : $group['_iconImg_'];
            $ui->contentAddHtml(
                '
						<style type="text/css">
							.ui-icon-group_'.$count.' {
                                background-color: '.$color.' !important;
                            }
                            
                            .ui-icon-group_'.$count.':after {
                                background-color = #333 !important;
                                background: url("../images/groups_small/'.$group['_iconImg_'].'") no-repeat scroll 0 0 #F3F3F3 !important;
                                background-size: 22px 22px !important;
                            }
						</style>
						'
            );
            $ui->contentAddHtml(
                '<div data-collapsed-icon="group_'.$count.'" '.'data-expanded-icon="group_'.$count.'" data-iconpos="right" data-role="collapsible" data-collapsed="true" data-theme="'.$color_class.'" data-content-theme="d">'
            );
            $ui->contentAddHtml('<h4>'.$img_group.' '.$group['_name_'].'<span class="agents-counter">'.$agents_counter.'</span></h4>');
            $ui->contentAddHtml('<ul data-role="listview" class="groups_sublist">');

            $ui->contentAddHtml(
                '<li data-icon="false"><a href="'.sprintf($url_agent, $group['_id_'], AGENT_STATUS_ALL).'"><span class="name_count">'.html_print_image('images/agent.png', true, ['class' => 'invert_filter'], false, false, false, true).__('Total agents').'</span><span class="number_count">'.$group['_total_agents_'].'</span></a></li>'
            );
            $ui->contentAddHtml(
                '<li data-icon="false"><a href="'.sprintf($url_agent, $group['_id_'], AGENT_STATUS_NOT_INIT).'"><span class="name_count">'.html_print_image('images/agent_notinit.png', true, false, false, false, false, true).__('Agents not init').'</span><span class="number_count">'.$group['_agents_not_init_'].'</span></a></li>'
            );
            $ui->contentAddHtml(
                '<li data-icon="false"><a href="'.sprintf($url_agent, $group['_id_'], AGENT_STATUS_CRITICAL).'"><span class="name_count">'.html_print_image('images/agent_critical.png', true, false, false, false, false, true).__('Agents critical').'</span><span class="number_count">'.$group['_agents_critical_'].'</span></a></li>'
            );
            $ui->contentAddHtml(
                '<li data-icon="false"><a href="'.sprintf($url_agent, $group['_id_'], AGENT_STATUS_UNKNOWN).'"><span class="name_count">'.html_print_image('images/agent_unknown.png', true, false, false, false, false, true).__('Agents unknown').'</span><span class="number_count">'.$group['_agents_unknown_'].'</span></a></li>'
            );
            $ui->contentAddHtml(
                '<li data-icon="false"><a href="'.sprintf($url_modules, $group['_id_'], AGENT_MODULE_STATUS_UNKNOWN).'"><span class="name_count">'.html_print_image('images/module_unknown.png', true, false, false, false, false, true).__('Unknown modules').'</span><span class="number_count">'.$group['_monitors_unknown_'].'</span></a></li>'
            );
            $ui->contentAddHtml(
                '<li data-icon="false"><a href="'.sprintf($url_modules, $group['_id_'], AGENT_MODULE_STATUS_NOT_INIT).'"><span class="name_count">'.html_print_image('images/module_notinit.png', true, false, false, false, false, true).__('Not init modules').'</span><span class="number_count">'.$group['_monitors_not_init_'].'</span></a></li>'
            );
            $ui->contentAddHtml(
                '<li data-icon="false"><a href="'.sprintf($url_modules, $group['_id_'], AGENT_MODULE_STATUS_NORMAL).'"><span class="name_count">'.html_print_image('images/module_ok.png', true, false, false, false, false, true).__('Normal modules').'</span><span class="number_count">'.$group['_monitors_ok_'].'</span></a></li>'
            );
            $ui->contentAddHtml(
                '<li data-icon="false"><a href="'.sprintf($url_modules, $group['_id_'], AGENT_MODULE_STATUS_WARNING).'"><span class="name_count">'.html_print_image('images/module_warning.png', true, false, false, false, false, true).__('Warning modules').'</span><span class="number_count">'.$group['_monitors_warning_'].'</span></a></li>'
            );
            $ui->contentAddHtml(
                '<li data-icon="false"><a href="'.sprintf($url_modules, $group['_id_'], AGENT_MODULE_STATUS_CRITICAL_BAD).'"><span class="name_count">'.html_print_image('images/module_critical.png', true, false, false, false, false, true).__('Critical modules').'</span><span class="number_count">'.$group['_monitors_critical_'].'</span></a></li>'
            );
            $ui->contentAddHtml(
                '<li data-icon="false"><a href=""><span class="name_count">'.html_print_image('images/bell_error.png', true, false, false, false, false, true).__('Alerts fired').'</span><span class="number_count">'.$group['_monitors_alerts_fired_'].'</span></a></li>'
            );
            $ui->contentAddHtml('</ul>');
            $ui->contentAddHtml('</div>');

            $count++;
        }

        $ui->contentAddHtml('</div>');
        $ui->endContent();
        $ui->showPage();
    }


    private function getListGroups()
    {
        $return = [];

        $system = System::getInstance();
        $user = User::getInstance();
        $result_groups = groupview_get_groups_list($system->getConfig('id_user'), 'AR', true);

        return $result_groups['groups'];
    }


}
