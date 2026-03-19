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


/**
 * @package    Include
 * @subpackage Clippy
 */


function clippy_start_page_homepage()
{
    global $config;

    $clippy_is_annoying = (int) get_cookie('clippy_is_annoying', 0);
    $nagios = (int) get_cookie('nagios', -1);

    $easter_egg_toy = ($nagios % 6);
    if (($easter_egg_toy == 5)
        || ($easter_egg_toy == -1)
    ) {
        $image = 'images/clippy/clippy.png';
    } else {
        $image = 'images/clippy/easter_egg_0'.$easter_egg_toy.'.png';
    }

    if ($image != 'easter_egg_04.png') {
        $style = 'display: block; position: absolute; left: -112px; top: -80px;';
    } else {
        $style = 'display: block; position: absolute; left: -200px; top: -80px;';
    }

    clippy_clean_help();

    $pandorin_img = html_print_image(
        $image,
        true,
        [
            'id'      => 'clippy_toy',
            'onclick' => 'easter_egg_clippy(1);',
        ]
    );

    $pandorin_chkb = html_print_checkbox_extended(
        'clippy_is_annoying',
        1,
        $clippy_is_annoying,
        false,
        'set_clippy_annoying()',
        '',
        true
    );

    $return_tours = [];
    $return_tours['first_step_by_default'] = true;
    $return_tours['tours'] = [];

    // ==================================================================
    // Help tour with the some task for to help the user.
    // ------------------------------------------------------------------
    $return_tours['tours']['homepage'] = [];
    $return_tours['tours']['homepage']['steps'] = [];
    $return_tours['tours']['homepage']['steps'][] = [
        'element' => '#clippy',
        'intro'   => '<div class="clippy_body left pdd_l_20px pdd_r_20px">'.__('Hi, can I help you?').'<br/><br/>'.__('Let me introduce my self: I am Pandorin, the annoying assistant of %s. You can follow my steps to do basic tasks in %s or you can close me and never see me again.', get_product_name(), get_product_name()).'<br /> <br /> <div class="clippy_body font_7pt">'.$pandorin_chkb.__('Close this wizard and don\'t open it again.').'</div></div><div class="relative"><div id="pandorin" style="'.$style.'">'.$pandorin_img.'</div></div>',
    ];
    $return_tours['tours']['homepage']['steps'][] = [
        'element' => '#clippy',
        'intro'   => __('Which task would you like to do first?').'<br/><br/><ul class="left mrgn_lft_10px list-type-disc"><li>'."<a href='javascript: clippy_go_link_show_help(\"index.php?sec=gagente&sec2=godmode/agentes/modificar_agente\", \"monitoring_server_step_1\");'>".__('Ping a Linux or Windows server using a %s agent.', get_product_name()).'</a></li><li>'."<a href='javascript: clippy_go_link_show_help(\"index.php\", \"email_alert_module_step_1\");'>".__('Create a alert by email in a critical module.').'</a></li></ul>',
    ];
    $return_tours['tours']['homepage']['conf'] = [];
    $return_tours['tours']['homepage']['conf']['show_bullets'] = 0;
    $return_tours['tours']['homepage']['conf']['show_step_numbers'] = 0;
    $return_tours['tours']['homepage']['conf']['name_obj_js_tour'] = 'intro_homepage';
    $return_tours['tours']['homepage']['conf']['other_js'] = "
		var started = 0;
		
		function show_clippy() {
			if (intro_homepage.started()) {
				started = 1;
			}
			else {
				started = 0;
			}
			
			if (started == 0)
				intro_homepage.start();
		}
		
		var nagios = -1;
		function easter_egg_clippy(click) {
			if (readCookie('nagios')) {
				nagios = readCookie('nagios');
			}
			
			if (click)
				nagios++;
			
			if (nagios > 5) {
				easter_egg_toy = nagios % 6;
				
				if ((easter_egg_toy == 5) ||
					(easter_egg_toy == -1)) {
					image = 'images/clippy/clippy.png';
				}
				else {
					image = 'images/clippy/easter_egg_0' + easter_egg_toy + '.png';
				}
				
				$('#clippy_toy').attr('src', image);
				if (easter_egg_toy == 4) {
					$('#pandorin').css('left', '-200px');
				}
				else {
					$('#pandorin').css('left', '-112px');
				}
				
				document.cookie = 'nagios=' + nagios;
			}
		}
		
		function set_clippy_annoying() {
			var now = new Date();
			var time = now.getTime();
			var expireTime = time + 3600000 * 24 * 360 * 20;
			now.setTime(expireTime);
			
			checked = $('input[name=\'clippy_is_annoying\']')
				.is(':checked');
			//intro_homepage.exit();
			
			if (checked) {
				document.cookie = 'clippy_is_annoying=1;expires=' +
					now.toGMTString() + ';';
			}
			else {
				document.cookie = 'clippy_is_annoying=0;expires=' +
					now.toGMTString() + ';';
			}
		}
		
		function readCookie(name) {
			var nameEQ = name + '=';
			var ca = document.cookie.split(';');
			
			for(var i=0;i < ca.length;i++) {
				var c = ca[i];
				
				while (c.charAt(0)==' ')
					c = c.substring(1,c.length);
				if (c.indexOf(nameEQ) == 0)
					return c.substring(nameEQ.length,c.length);
			}
			return null;
		}
		
		";
    if ($config['logged']) {
        $return_tours['tours']['homepage']['conf']['autostart'] = true;
    } else {
        $return_tours['tours']['homepage']['conf']['autostart'] = false;
    }

    if ($config['tutorial_mode'] == 'on_demand') {
        $return_tours['tours']['homepage']['conf']['autostart'] = false;
    }

    if ($clippy_is_annoying === 1) {
        $return_tours['tours']['homepage']['conf']['autostart'] = false;
    }

    // ==================================================================
    // ==================================================================
    // Help tour about the email alert module (step 1)
    // ------------------------------------------------------------------
    $return_tours['tours']['email_alert_module_step_1'] = [];
    $return_tours['tours']['email_alert_module_step_1']['steps'] = [];
    $return_tours['tours']['email_alert_module_step_1']['steps'][] = [
        'element' => '#clippy',
        'intro'   => __('The first thing you have to do is to setup the e-mail config on the %s Server.', get_product_name()).'<br />'.ui_print_help_icon('context_pandora_server_email', true, '', 'images/help.png').'<br />'.__('If you have it already configured you can go to the next step.'),
    ];
    $return_tours['tours']['email_alert_module_step_1']['steps'][] = [
        'element'  => '#icon_god-alerts',
        'position' => 'top',
        'intro'    => __('Now, pull down the Manage alerts menu and click on Actions. '),
    ];
    $return_tours['tours']['email_alert_module_step_1']['conf'] = [];
    $return_tours['tours']['email_alert_module_step_1']['conf']['show_bullets'] = 0;
    $return_tours['tours']['email_alert_module_step_1']['conf']['show_step_numbers'] = 0;

    $return_tours['tours']['email_alert_module_step_1']['conf']['complete_js'] = '
		;
		';
    $return_tours['tours']['email_alert_module_step_1']['conf']['exit_js'] = '
		location.reload();
		';
    $return_tours['tours']['email_alert_module_step_1']['conf']['next_help'] = 'email_alert_module_step_2';
    // ==================================================================
    return $return_tours;
}
