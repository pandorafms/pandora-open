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
require_once $config['homedir'].'/include/functions_config.php';
require_once $config['homedir'].'/include/functions_snmp_browser.php';
require_once $config['homedir'].'/include/functions_snmp.php';
require_once $config['homedir'].'/include/functions_network_components.php';

global $config;

set_error_handler(
    function ($code, $string, $file, $line) {
        throw new ErrorException($string, null, $code, $file, $line);
    }
);

register_shutdown_function(
    function () {
        $error = error_get_last();
        if (null !== $error) {
            echo $error['message'];
        }
    }
);

try {
    if ((bool) is_ajax() === true) {
        $method = (string) get_parameter('method', '');
        $action = (string) get_parameter('action', '');
        $target_ip = (string) get_parameter('target_ip', '');
        $target_port = (string) get_parameter('target_port', '');
        $community = (string) io_safe_output((get_parameter('community', '')));
        $snmp_version = (string) get_parameter('snmp_browser_version', '');
        $snmp3_auth_user = io_safe_output(get_parameter('snmp3_browser_auth_user'));
        $snmp3_security_level = get_parameter('snmp3_browser_security_level');
        $snmp3_auth_method = get_parameter('snmp3_browser_auth_method');
        $snmp3_auth_pass = io_safe_output(get_parameter('snmp3_browser_auth_pass'));
        $snmp3_privacy_method = get_parameter('snmp3_browser_privacy_method');
        $snmp3_privacy_pass = io_safe_output(get_parameter('snmp3_browser_privacy_pass'));
        $module_target = get_parameter('module_target', '');
        $targets_oids = get_parameter('oids', '');
        $return_id = get_parameter('return_id', false);
        $custom_action = get_parameter('custom_action', '');
        $server_to_exec = get_parameter('server_to_exec');

        if (!is_array($targets_oids)) {
            $targets_oids = explode(',', $targets_oids);
        }

        if ($custom_action != '') {
            $custom_action = urldecode(base64_decode($custom_action));
        }

        // SNMP browser.
        if ($action == 'snmptree') {
            $starting_oid = (string) get_parameter('starting_oid', '.');

            $snmp_tree = snmp_browser_get_tree(
                $target_ip,
                $community,
                $starting_oid,
                $snmp_version,
                $snmp3_auth_user,
                $snmp3_security_level,
                $snmp3_auth_method,
                $snmp3_auth_pass,
                $snmp3_privacy_method,
                $snmp3_privacy_pass,
                'null',
                $server_to_exec,
                $target_port
            );
            if (! is_array($snmp_tree)) {
                echo $snmp_tree;
            } else {
                snmp_browser_print_tree(
                    $snmp_tree,
                    // Id.
                    0,
                    // Depth.
                    0,
                    // Last.
                    0,
                    // Last_array.
                    [],
                    // Sufix.
                    false,
                    // Checked.
                    [],
                    // Return.
                    false,
                    // Descriptive_ids.
                    false,
                    // Previous_id.
                    ''
                );

                // Div for error/succes dialog.
                $output = '<div id="snmp_result_msg" class="invisible"></div>';

                // Dialog error.
                $output .= '<div id="dialog_error" class="invisible" title="'.__('SNMP modules').'">';
                $output .= '<div>';
                $output .= "<div class='w25p float-left'><img class='pdd_l_20px pdd_t_20px' src='images/icono_error_mr.png'></div>";
                $output .= "<div class='w75p float-left'><h3><strong class='verdana font_13pt'>ERROR</strong></h3>";
                $output .= "<p class='verdana font_12pt mrgn_btn_0px'>".__('Error creating the following modules:').'</p>';
                $output .= "<p id='error_text' class='verdana font_12pt;'></p>";
                $output .= '</div>';
                $output .= '</div>';
                $output .= '</div>';

                // Dialog success.
                $output .= '<div id="dialog_success" class="invisible" title="'.__('SNMP modules').'">';
                $output .= '<div>';
                $output .= "<div class='w25p float-left'><img class='pdd_l_20px pdd_t_20px' src='images/icono_exito_mr.png'></div>";
                $output .= "<div class='w75p float-left'><h3><strong class='verdana font_13pt'>SUCCESS</strong></h3>";
                $output .= "<p class='verdana font_12pt'>".__('Modules successfully created').'</p>';
                $output .= '</div>';
                $output .= '</div>';
                $output .= '</div>';

                // Dialog no agent selected.
                $output .= '<div id="dialog_no_agents_selected" class="invisible" title="'.__('SNMP modules').'">';
                $output .= '<div>';
                $output .= "<div class='w25p float-left'><img class='pdd_l_20px pdd_t_20px' src='images/icono_error_mr.png'></div>";
                $output .= "<div class='w75p float-left'><h3><strong class='verdana font_13pt'>ERROR</strong></h3>";
                $output .= "<p class='verdana font_12pt mrgn_btn_0px'>".__('Module must be applied to an agent or a policy').'</p>';
                $output .= "<p id='error_text' class='verdana font_12pt'></p>";
                $output .= '</div>';
                $output .= '</div>';
                $output .= '</div>';

                echo $output;
            }


            return;
        }

        if ($action == 'snmpget') {
            // SNMP get.
            $target_oid = htmlspecialchars_decode(get_parameter('oid', ''));
            $custom_action = get_parameter('custom_action', '');
            if ($custom_action != '') {
                $custom_action = urldecode(base64_decode($custom_action));
            }

            $oid = snmp_browser_get_oid(
                $target_ip,
                $community,
                $target_oid,
                $snmp_version,
                $snmp3_auth_user,
                $snmp3_security_level,
                $snmp3_auth_method,
                $snmp3_auth_pass,
                $snmp3_privacy_method,
                $snmp3_privacy_pass,
                $server_to_exec
            );

            snmp_browser_print_oid(
                $oid,
                $custom_action,
                false,
                $community,
                $snmp_version
            );
            return;
        }

        if ($method == 'snmp_browser_create_modules') {
            // Get target ids from form.
            $use_agent_ip = get_parameter('use_agent_ip', '');
            $id_items = get_parameter('id_item2', null);
            $id_target = null;
            if (empty($id_items) === false) {
                $id_target = explode(',', $id_items[0]);
            }

            if (empty($id_items[0]) && $module_target !== 'network_component') {
                echo json_encode([0 => -1]);
                exit;
            }

            $snmp_extradata = get_parameter('snmp_extradata', '');

            if (!is_array($snmp_extradata)) {
                // Decode SNMP values.
                $snmp_extradata = json_decode(io_safe_output($snmp_extradata), true);
            }


            foreach ($snmp_extradata as $snmp_conf) {
                $snmp_conf_values[$snmp_conf['name']] = $snmp_conf['value'];
            }

            $fail_modules = snmp_browser_create_modules_snmp(
                $module_target,
                $snmp_conf_values,
                $id_target,
                $server_to_exec,
                $use_agent_ip
            );

            // Return fail modules for error/success message.
            echo json_encode($fail_modules);
            exit;
        }

        if ($method == 'snmp_browser_print_create_module_massive') {
            // Get SNMP conf vaues from modal onshow extradata.
            $snmp_extradata = get_parameter('extradata', '');

            $return = snmp_browser_print_create_module_massive($module_target, $snmp_extradata, true);
            echo $return;
            exit;
        }

        if ($method == 'snmp_browser_print_create_policy') {
            $return = snmp_browser_print_create_policy();
            echo $return;
            exit;
        }
    }
} catch (\Exception $e) {
    echo $e->getMessage();
}
