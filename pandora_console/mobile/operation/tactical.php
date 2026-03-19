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

require '../include/functions_tactical.php';
require '../include/functions_servers.php';

class Tactical
{

    private $correct_acl = false;

    private $acl = 'AR';


    function __construct()
    {
        $system = System::getInstance();

        if ($system->checkACL($this->acl)) {
            $this->correct_acl = true;
        } else {
            $this->correct_acl = false;
        }
    }


    public function show()
    {
        if (!$this->correct_acl) {
            $this->show_fail_acl();
        } else {
            $this->show_tactical();
        }
    }


    public function ajax($parameter2=false)
    {
        $system = System::getInstance();

        if (!$this->correct_acl) {
            return;
        } else {
            switch ($parameter2) {
                case 'render_status_pie':
                    $links = $system->getRequest('links', '');
                    $links = $system->safeOutput($links);
                    $data = $system->getRequest('data', '');
                    $data = $system->safeOutput($data);
                    $data = str_replace('\\', '', $data);
                    $links = str_replace('\\', '', $links);
                    $width = $system->getRequest('width', 230);

                    $max_width = 399;

                    if ($width > $max_width) {
                        $width = $max_width;
                    }

                    echo reporting_get_stats_modules_status(json_decode($data, true), $width, ($width / 2), json_decode($links, true));
                exit;
            }
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


    private function show_tactical()
    {
        $ui = Ui::getInstance();

        $ui->createPage();
        $ui->createDefaultHeader(
            __('Tactical view'),
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
            $ui->contentBeginGrid('responsive');
                // ~ $data = reporting_get_group_stats();
                $all_data = tactical_status_modules_agents($config['id_user'], false, 'AR');

                $data = [];

                $data['monitor_not_init'] = (int) $all_data['_monitors_not_init_'];
                $data['monitor_unknown'] = (int) $all_data['_monitors_unknown_'];
                $data['monitor_ok'] = (int) $all_data['_monitors_ok_'];
                $data['monitor_warning'] = (int) $all_data['_monitors_warning_'];
                $data['monitor_critical'] = (int) $all_data['_monitors_critical_'];
                $data['monitor_not_normal'] = (int) $all_data['_monitor_not_normal_'];
                $data['monitor_alerts'] = (int) $all_data['_monitors_alerts_'];
                $data['monitor_alerts_fired'] = (int) $all_data['_monitors_alerts_fired_'];
                $data['monitor_total'] = (int) $all_data['_monitor_total_'];

                $data['total_agents'] = (int) $all_data['_total_agents_'];

                $data['monitor_checks'] = (int) $all_data['_monitor_checks_'];

                // Percentages
        if (!empty($all_data)) {
            if ($data['monitor_not_normal'] > 0 && $data['monitor_checks'] > 0) {
                $data['monitor_health'] = format_numeric((100 - ($data['monitor_not_normal'] / ($data['monitor_checks'] / 100))), 1);
            } else {
                $data['monitor_health'] = 100;
            }

            if ($data['monitor_not_init'] > 0 && $data['monitor_checks'] > 0) {
                $data['module_sanity'] = format_numeric((100 - ($data['monitor_not_init'] / ($data['monitor_checks'] / 100))), 1);
            } else {
                $data['module_sanity'] = 100;
            }

            if (isset($data['alerts'])) {
                if ($data['monitor_alerts_fired'] > 0 && $data['alerts'] > 0) {
                    $data['alert_level'] = format_numeric((100 - ($data['monitor_alerts_fired'] / ($data['alerts'] / 100))), 1);
                } else {
                    $data['alert_level'] = 100;
                }
            } else {
                $data['alert_level'] = 100;
                $data['alerts'] = 0;
            }

            $data['monitor_bad'] = ($data['monitor_critical'] + $data['monitor_warning']);
            if ($data['monitor_bad'] > 0 && $data['monitor_checks'] > 0) {
                $data['global_health'] = format_numeric((100 - ($data['monitor_bad'] / ($data['monitor_checks'] / 100))), 1);
            } else {
                $data['global_health'] = 100;
            }

            $data['server_sanity'] = format_numeric((100 - $data['module_sanity']), 1);
        }

                $data['mobile'] = true;

                $formatted_data = reporting_get_stats_indicators_mobile($data, 100, 10, false);
                $formatted_data_untiny = reporting_get_stats_indicators_mobile($data, 140, 15, false);

                $overview = '<table class="tactical_bars">
						<tr>
							<td>'.$formatted_data['server_health']['title'].'</td>
							<td class="tiny tactical_bar">'.$formatted_data['server_health']['graph'].'</td>
							<td class="untiny tactical_bar">'.$formatted_data_untiny['server_health']['graph'].'</td>
						</tr>
						<tr>
							<td>'.$formatted_data['monitor_health']['title'].'</td>
							<td class="tiny tactical_bar">'.$formatted_data['monitor_health']['graph'].'</td>
							<td class="untiny tactical_bar">'.$formatted_data_untiny['monitor_health']['graph'].'</td>
						</tr>
						<tr>
							<td>'.$formatted_data['module_sanity']['title'].'</td>
							<td class="tiny tactical_bar">'.$formatted_data['module_sanity']['graph'].'</td>
							<td class="untiny tactical_bar">'.$formatted_data_untiny['module_sanity']['graph'].'</td>
						</tr>
						<tr>
							<td>'.$formatted_data['alert_level']['title'].'</td>
							<td class="tiny tactical_bar">'.$formatted_data['alert_level']['graph'].'</td>
							<td class="untiny tactical_bar">'.$formatted_data_untiny['alert_level']['graph'].'</td>
						</tr>
					</table>';

                $agents_monitors = reporting_get_stats_agents_monitors($data);
                $alerts_stats = reporting_get_stats_alerts($data);

                $overview .= "<div class='hr'></div>\n".$agents_monitors;
                $overview .= "<div class='hr'></div>\n".$alerts_stats;

                $ui->contentGridAddCell($overview, 'tactical1');

                $links['monitor_critical'] = 'index.php?page=modules&status=1';
                $links['monitor_warning'] = 'index.php?page=modules&status=2';
                $links['monitor_ok'] = 'index.php?page=modules&status=0';
                $links['monitor_unknown'] = 'index.php?page=modules&status=3';
                $links['monitor_not_init'] = 'index.php?page=modules&status=5';

                $formatted_data = "<div id='status_pie'></div>";
                $formatted_data .= html_print_div(['id' => 'status_pie_links', 'content' => io_safe_input(json_encode($links)), 'hidden' => '1'], true);
                $formatted_data .= html_print_div(['id' => 'status_pie_data', 'content' => io_safe_input(json_encode($data)), 'hidden' => '1'], true);

                $formatted_data = $formatted_data;
                $ui->contentGridAddCell($formatted_data, 'tactical2');
            $ui->contentEndGrid();

            $this->getLastActivity();
            $ui->contentBeginCollapsible(__('Last activity'));

                $table = new Table();
                $table->id = 'last-activity';
                $table->importFromHash($this->getLastActivity());
                $ui->contentCollapsibleAddItem($table->getHTML());
            $ui->contentEndCollapsible();
            $ui->contentAddHtml(
                "<script type=\"text/javascript\">
			$(document).ready(function() {
				function set_same_heigth() {
					//Set same height to boxes
					var max_height = 0;
					if ($('#tactical1').height() > $('#tactical2 .tactical_set').height()) {
						max_height = $('#tactical1').height();
						$('#tactical2 .tactical_set').height(max_height);
					}
					else {
						max_height = $('#tactical2 .tactical_set').height();
						//~ $('#tactical1').height(max_height);
					}
				}
				
				function ajax_load_status_pie() {
					$('#status_pie').html('<div class=\"center\"> ".__('Loading...')."<br><img src=\"images/ajax-loader.gif\" /></div>');
					
					var pie_width = $('#tactical2').width() * 0.9;

					postvars = {};
					postvars[\"action\"] = \"ajax\";
					postvars[\"parameter1\"] = \"tactical\";
					postvars[\"parameter2\"] = \"render_status_pie\";
					postvars[\"links\"] = $('#status_pie_links').text();
					postvars[\"data\"] = $('#status_pie_data').text();
					postvars[\"width\"] = pie_width;
					$.post(\"index.php\",
						postvars,
						function (data) {
							$('#status_pie').html(data);
							set_same_heigth();
						},
						\"html\");
				}
				
				// Detect orientation change to refresh dinamic content
				$(window).on({
					orientationchange: function(e) {
						// Refresh events bar
						ajax_load_status_pie();
						
						// Keep same height on boxes
						if ($('.ui-block-b').css('float') == 'none') {
							$('#tactical1').height('auto');
							$('#tactical2').height('auto');
						}
						else {
							set_same_heigth();
						}
					}
				});
									
				if ($('.ui-block-b').css('float') != 'none') {
					set_same_heigth();
				}
				
				ajax_load_status_pie();
			});			
			</script>"
            );
        $ui->endContent();
        $ui->showPage();
    }


    private function getLastActivity()
    {
        global $config;

        switch ($config['dbtype']) {
            case 'mysql':
                $sql = sprintf(
                    'SELECT id_usuario,accion,fecha,ip_origen,descripcion,utimestamp
					FROM tsesion
					WHERE (`utimestamp` > UNIX_TIMESTAMP(NOW()) - '.SECONDS_1WEEK.") 
						AND `id_usuario` = '%s' ORDER BY `utimestamp` DESC LIMIT 10",
                    $config['id_user']
                );
            break;

            case 'postgresql':
                $sql = sprintf(
                    "SELECT \"id_usuario\", accion, fecha, \"ip_origen\", descripcion, utimestamp
					FROM tsesion
					WHERE (\"utimestamp\" > ceil(date_part('epoch', CURRENT_TIMESTAMP)) - ".SECONDS_1WEEK.") 
						AND \"id_usuario\" = '%s' ORDER BY \"utimestamp\" DESC LIMIT 10",
                    $config['id_user']
                );
            break;

            case 'oracle':
                $sql = sprintf(
                    "SELECT id_usuario, accion, fecha, ip_origen, descripcion, utimestamp
					FROM tsesion
					WHERE ((utimestamp > ceil((sysdate - to_date('19700101000000','YYYYMMDDHH24MISS')) * (".SECONDS_1DAY.')) - '.SECONDS_1WEEK.") 
						AND id_usuario = '%s') AND rownum <= 10 ORDER BY utimestamp DESC",
                    $config['id_user']
                );
            break;
        }

        $sessions = db_get_all_rows_sql($sql);

        if ($sessions === false) {
            $sessions = [];
        }

        $return = [];
        foreach ($sessions as $session) {
            $data = [];

            switch ($config['dbtype']) {
                case 'mysql':
                case 'oracle':
                    $session_id_usuario = $session['id_usuario'];
                    $session_ip_origen = $session['ip_origen'];
                break;

                case 'postgresql':
                    $session_id_usuario = $session['id_usuario'];
                    $session_ip_origen = $session['ip_origen'];
                break;
            }

            $data[__('Action')] = ui_print_session_action_icon($session['accion'], true);
            $data[__('User')] = $session_id_usuario.' - '.ui_print_truncate_text(io_safe_output($session['descripcion']), 40, false);
            $data[__('Date')] = '<span class="muted">'.human_time_comparation($session['utimestamp'], 'tiny').'</span>';
            $data[__('Source IP')] = '<span class="muted">'.$session_ip_origen.'</span>';

            $return[] = $data;
        }

        return $return;
    }


}
