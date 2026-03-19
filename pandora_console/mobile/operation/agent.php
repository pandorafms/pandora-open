<?php
// phpcs:disable Squiz.NamingConventions.ValidVariableName.MemberNotCamelCaps
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

use PandoraFMS\Event;

// Begin.
require_once '../include/functions_users.php';

class Agent
{

    private $correct_acl = false;

    private $id = 0;

    private $agent = null;


    function __construct()
    {
        $system = System::getInstance();

        $this->id = $system->getRequest('id', 0);

        global $config;

        echo "<script>
		var ismobile = / mobile/i.test(navigator.userAgent);
		var iswindows = /Windows/i.test(navigator.userAgent);
		var ismac = /Macintosh/i.test(navigator.userAgent);
		var isubuntu = /Ubuntu/i.test(navigator.userAgent);
		var isfedora = /Fedora/i.test(navigator.userAgent);
		var isredhat = /Red Hat/i.test(navigator.userAgent);
		var isdebian = /Debian/i.test(navigator.userAgent);
		var isgentoo = /Gentoo/i.test(navigator.userAgent);
		var iscentos = /CentOS/i.test(navigator.userAgent);
		var issuse = /SUSE/i.test(navigator.userAgent);

		if(!(ismobile) && !(iswindows) && !(ismac) && !(isubuntu) && !(isfedora) && !(isredhat) && !(isdebian) && !(isgentoo) && !(iscentos) 
		&& !(issuse)){
			 window.location.href = '".$config['homeurl'].'index.php?sec=estado&sec2=operation/agentes/ver_agente&id_agente='.$this->id."';
			";
            echo '
		}
		</script>';

        $this->agent = agents_get_agents(
            [
                'disabled'  => 0,
                'id_agente' => $this->id,
            ],
            ['*']
        );

        if (!empty($this->agent)) {
            $this->agent = $this->agent[0];

            if ($system->checkACL('AR', $this->agent['id_grupo'])) {
                $this->correct_acl = true;
            } else {
                $this->correct_acl = false;
            }
        } else {
            $this->agent = null;
            $this->correct_acl = true;
        }
    }


    public function show()
    {
        if (!$this->correct_acl) {
            $this->show_fail_acl();
        } else {
            $this->show_agent();
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


    private function show_agent()
    {
        $ui = Ui::getInstance();
        $system = System::getInstance();
        $eventObj = new Events;

        $ui->createPage();

        $options = $eventObj->getEventDialogOptions();
        $ui->addDialog($options);

        if ($this->id != 0) {
            $agent_alias = (string) $this->agent['alias'];

            $agents_filter = (string) $system->getRequest('agents_filter');
            $agents_filter_q_param = empty($agents_filter) ? '' : '&agents_filter='.$agents_filter;

            $ui->createDefaultHeader(
                sprintf('%s', $agent_alias),
                $ui->createHeaderButton(
                    [
                        'icon'  => 'ui-icon-back',
                        'pos'   => 'left',
                        'text'  => __('Back'),
                        'href'  => 'index.php?page=agents'.$agents_filter_q_param,
                        'class' => 'header-button-left',
                    ]
                )
            );
        } else {
            $ui->createDefaultHeader(__('Agents'));
        }

        $ui->showFooter(false);
        $ui->beginContent();
        if (empty($this->agent)) {
            $ui->contentAddHtml(
                '<span class="no-data">'.__('No agent found').'</span>'
            );
        } else {
            $ui->contentBeginGrid();
            if ($this->agent['disabled']) {
                $agent_alias = '<em>'.$agent_alias.'</em>'.ui_print_help_tip(__('Disabled'), true);
            } else if ($this->agent['quiet']) {
                $agent_alias = '<em>'.$agent_alias.'&nbsp;'.html_print_image(
                    'images/dot_blue.png',
                    true,
                    [
                        'border' => '0',
                        'title'  => __('Quiet'),
                        'alt'    => '',
                    ]
                ).'</em>';
            }

                $addresses = agents_get_addresses($this->id);

            $address = $this->agent['direccion'];
            $ip .= (empty($address) === true) ? '<em>'.__('N/A').'</em>' : $address;
            $last_contact = '<b>'.__('Last contact').'</b>:&nbsp;';
            $last_contact .= human_time_comparation($this->agent['ultimo_contacto'], 'tiny');

            if (empty($agent['comentarios']) === true) {
                $description .= '<i>'.__('N/A').'</i>';
            } else {
                $description .= $this->agent['comentarios'];
            }

            $html = '<div class="agent_details" style:"float:left;">';
            $html .= '<span class="agent_name">'.$agent_alias.'</span>';
            $html .= '</div>';
            $html .= '<div class="agent_os">'.ui_print_os_icon(
                $this->agent['id_os'],
                false,
                true,
                true,
                false,
                false,
                false,
                ['class' => 'invert_filter main_menu_icon'],
                false
            ).'</div>';
            $html .= '<div class="agent_list_ips">';
            $html .= $ip.' -  '.groups_get_name($this->agent['id_grupo'], true);
            $html .= '</div>
						<div class="agent_last_contact">';
            $html .= $last_contact;
            $html .= '</div>
						<div class="agent_description">';
            $html .= $description;
            $html .= '</div>';

            $ui->contentGridAddCell($html, 'agent_details');

            ob_start();

            // Fixed width non interactive charts.
            $status_chart_width = 160;
            $graph_width = 160;

            $html = '<div class="agent_graphs">';
            $html .= '<b>'.__('Modules by status').'</b>';
            $html .= '<div id="status_pie" style="margin: auto; width: '.$status_chart_width.'px; margin-bottom: 10px;">';
            $html .= graph_agent_status(
                $this->id,
                $graph_width,
                160,
                true,
                false,
                false,
                true
            );
            $html .= '</div>';
            $graph_js = ob_get_clean();
            $html = $graph_js.$html;

            unset($this->agent['fired_count']);

            if ($this->agent['total_count'] > 0) {
                $html .= '<div class="agents_tiny_stats agents_tiny_stats_tactical">'.reporting_tiny_stats($this->agent, true, 'agent', '&nbsp;').' </div>';
            }

            $html .= '</div>';
            $html .= '<div class="events_bar">';
            $html .= '<b>'.__('Events (24h)').'</b>';
            $html .= '<div id="events_bar" style ="display: flex; flex-direction: row; flex-wrap: wrap; align-items: center; justify-content: center; width: 100%; height: 96px;" >';
            $html .= graph_graphic_agentevents(
                $this->id,
                95,
                45,
                SECONDS_1DAY,
                '',
                true,
                true,
                500,
                1
            );
            $html .= '</div>';
            $html .= '</div>';

            $ui->contentGridAddCell($html, 'agent_graphs');
            $ui->contentEndGrid();

            $modules = new Modules();

                $filters = [
                    'id_agent'    => $this->id,
                    'all_modules' => true,
                    'status'      => -1,
                ];

            // Module searchbox.
            $ui->beginForm('javascript:agent_filter_modules();');
            $ui->formAddInput(
                [
                    'id'   => 'filter-modules',
                    'name' => 'filter-modules',
                ]
            );
            $ui->formAddInput(
                [
                    'id'    => 'filter-modules',
                    'name'  => 'filter-modules',
                    'type'  => 'submit',
                    'value' => __('Search'),
                ]
            );
            $filtering = $ui->getEndForm();

            $modules->setFilters($filters);
            $modules->disabledColumns(['agent']);
            $ui->contentBeginCollapsible(__('Modules'));
            $ui->contentCollapsibleAddItem($filtering);
            $ui->contentCollapsibleAddItem($modules->listModulesHtml(0, true));
            $ui->contentEndCollapsible();

            $alerts = new Alerts();

                $filters = [
                    'id_agent'   => $this->id,
                    'all_alerts' => true,
                ];

            $alerts->setFilters($filters);
            $alerts->disabledColumns(['agent']);
            $ui->contentBeginCollapsible(__('Alerts'));
            $ui->contentCollapsibleAddItem($alerts->listAlertsHtml(true));
            $ui->contentEndCollapsible();

            $events = new Events();
            $events->addJavascriptDialog();

            $ui->contentAddHtml("<a id='detail_event_dialog_hook' href='#detail_event_dialog' class='invisible'>detail_event_hook</a>");
            $ui->contentAddHtml("<a id='detail_event_dialog_error_hook' href='#detail_event_dialog_error' class='invisible'>detail_event_dialog_error_hook</a>");

            $ui->contentBeginCollapsible(sprintf(__('Last %s Events'), $system->getPageSize()), 'agent-last-events');
            $tabledata = $events->listEventsHtml(0, true, 'last_agent_events');
            $ui->contentCollapsibleAddItem($tabledata['table']);
            $ui->contentCollapsibleAddItem($events->putEventsTableJS($this->id));
            $ui->contentEndCollapsible();
        }

        $ui->contentAddLinkListener('last_agent_events');
        $ui->contentAddLinkListener('list_events');
        $ui->contentAddLinkListener('list_agent_Modules');

        $ui->contentAddHtml(
            "<script type=\"text/javascript\">
			$(document).ready(function() {
				function set_same_heigth() {
					//Set same height to boxes
					var max_height = 0;
					if ($('.agent_details').height() > $('.agent_graphs').height()) {
						max_height = $('.agent_details').height();
						$('.agent_graphs').height(max_height);
					}
					else {
						max_height = $('.agent_graphs').height();
						$('.agent_details').height(max_height);
					}
				}

				if ($('.ui-block-a').css('float') != 'none') {
					set_same_heigth();
				}

				$('.ui-collapsible').bind('expand', function () {
					refresh_link_listener_last_agent_events();
					refresh_link_listener_list_agent_Modules();
				});

				// Detect orientation change to refresh dinamic content
				$(window).on({
					orientationchange: function(e) {
						// Keep same height on boxes
						if ($('.ui-block-a').css('float') == 'none') {
							$('.agent_graphs').height('auto');
							$('.agent_details').height('auto');
						}
						else {
							set_same_heigth();
						}
					}
				});

				if ($('.ui-block-a').css('float') != 'none') {
					set_same_heigth();
				}
			});

            function agent_filter_modules() {
                $.mobile.loading('show');
                $.ajax ({
                    type: 'POST',
                    url: 'index.php',
                    dataType: 'text',
                    data: {
                        'action': 'ajax',
                        'parameter1': 'agent',
                        'id': ".$this->id.",
                        'parameter2': 'filter-modules',
                        'filter': $('#filter-modules').val()
                    },
                    success: function(r) {
                        $.mobile.loading('hide');
                        var className = $('#list_agent_Modules').attr('class');
                        if (document.getElementById('list_agent_Modules') == null) {
                            $($('p.no-data')[0]).parent().html(r);
                            className = 'ui-responsive table-stroke ui-table ui-table-reflow';
                        } else {
                            $('#list_agent_Modules').parent().html(r);
                        }
                        $('#list_agent_Modules').addClass(className);
                    },
                    error: function(r, t, e) {
                        $.mobile.loading('hide');
                        console.error(e);
                    }
                });
            }

			</script>"
        );

        $ui->endContent();
        $ui->showPage();
    }


    /**
     * Bob do something!
     *
     * @return void
     */
    public function ajax($parameter2=null)
    {
        $system = System::getInstance();

        if ($parameter2 === 'filter-modules') {
            $name_filter = $system->getRequest('filter', '');
            $modules = new Modules();

            $filters = [
                'id_agent'    => $this->id,
                'all_modules' => true,
                'status'      => -1,
                'name'        => '%'.$name_filter.'%',
            ];

            $modules->setFilters($filters);
            $modules->disabledColumns(['agent']);
            echo $modules->listModulesHtml(0, true);
        }
    }


}
