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

use PandoraFMS\Tools\Files;

// Load global vars.
global $config;

// Check ACL and Login.
check_login();

if ((bool) check_acl($config['id_user'], 0, 'PM') === false
    || (bool) check_acl($config['id_user'], 0, 'AW') === false
) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Plugin Management'
    );
    include 'general/noaccess.php';
    return;
}

ui_require_css_file('first_task');

    ui_print_standard_header(
        __('PLUGIN REGISTRATION'),
        'images/gm_servers.png',
        false,
        '',
        true,
        [],
        [
            [
                'link'  => '',
                'label' => __('Servers'),
            ],
            [
                'link'  => '',
                'label' => __('Register plugin'),
            ],
        ]
    );

    $management_allowed = true;



$msg = __('This extension makes registering server plugins an easier task. Here you can upload a server plugin in .pspz zipped format. Please refer to the official documentation on how to obtain and use Server Plugins.');
$msg .= '<br><br>';
$msg .= __('You can get more plugins in our');
$msg .= '<a href="https://pandoraopen.io/">';
$msg .= ' '.__('Public Resource Library');
$msg .= '</a>';

// Upload form.
$button = "<form name='submit_plugin' id='submit-plugin' method='post' enctype='multipart/form-data'>";
$button .= "<input type='file' class='w100p' name='plugin_upload' />";
$button .= "<input type='submit' class='button_task button_task_mini mrgn_0px_imp' value='".__('Upload file')."' />";
$button .= '</form>';

$output = ui_print_empty_view(
    __('Register plugins'),
    $msg,
    'plugins.svg',
    $button
);

echo $output;

$zip = null;
$upload = false;
if (isset($_FILES['plugin_upload']) === true) {
    $basepath = $config['attachment_store'].'/plugin';

    $filename = $_FILES['plugin_upload']['name'];
    $uploaded_filename = $_FILES['plugin_upload']['tmp_name'];

    $tmp_path = Files::tempdirnam(
        $config['attachment_store'].'/downloads/',
        'plugin_uploaded_'
    );
    if ($tmp_path === false) {
        $error = __('Failed to create temporary directory');
    }
} else {
    $error = '';
}

if ($error === null) {
    if (Files::unzip($uploaded_filename, $tmp_path) === true) {
        // Successfully extracted to tmp directory.
        // Grant execution over all files found.
        Files::chmod($tmp_path, 0755);

        // Operate.
        $ini_array = parse_ini_file($tmp_path.'/plugin_definition.ini', true);
        // Clean plugin_definition.ini file.
        unlink($tmp_path.'/plugin_definition.ini');

        // Parse with sections.
        if ($ini_array === false) {
            $error = __('Cannot load INI file');
        } else {
            // Relocate files to target destination.
            Files::move($tmp_path.'/', $basepath.'/', true);

            // Extract information.
            $version = preg_replace('/.*[.]/', '', $filename);
            $exec_path = $basepath.'/'.$ini_array['plugin_definition']['filename'];
            $file_exec_path = $exec_path;

            if (isset($ini_array['plugin_definition']['execution_command']) === true
                && empty($ini_array['plugin_definition']['execution_command']) === false
            ) {
                $exec_path = $ini_array['plugin_definition']['execution_command'];
                $exec_path .= ' '.$basepath.'/';
                $exec_path .= $ini_array['plugin_definition']['filename'];
            }

            if (isset($ini_array['plugin_definition']['execution_postcommand']) === true
                && empty($ini_array['plugin_definition']['execution_postcommand']) === false
            ) {
                $exec_path .= ' '.$ini_array['plugin_definition']['execution_postcommand'];
            }

            if (file_exists($file_exec_path) === false) {
                $error = __('Plugin exec not found. Aborting!');
                unlink($config['attachment_store'].'/plugin_definition.ini');
            } else {
                // Verify if a plugin with the same name is already registered.
                $sql = sprintf(
                    'SELECT COUNT(*) FROM tplugin WHERE name = "%s"',
                    io_safe_input($ini_array['plugin_definition']['name'])
                );
                $result = db_get_sql($sql);

                if ($result > 0) {
                    $error = __('Plugin already registered. Aborting!');
                    unlink($config['attachment_store'].'/plugin_definition.ini');
                } else {
                    $values = [
                        'name'         => io_safe_input($ini_array['plugin_definition']['name']),
                        'description'  => io_safe_input($ini_array['plugin_definition']['description']),
                        'max_timeout'  => $ini_array['plugin_definition']['timeout'],
                        'execute'      => io_safe_input($exec_path),
                        'net_dst_opt'  => $ini_array['plugin_definition']['ip_opt'],
                        'net_port_opt' => $ini_array['plugin_definition']['port_opt'],
                        'user_opt'     => $ini_array['plugin_definition']['user_opt'],
                        'pass_opt'     => $ini_array['plugin_definition']['pass_opt'],
                        'parameters'   => $ini_array['plugin_definition']['parameters'],
                        'plugin_type'  => $ini_array['plugin_definition']['plugin_type'],
                    ];

                    switch ($version) {
                        case 'pspz':
                            // Fixed the static parameters
                            // for
                            // the dinamic parameters of pandoras 5.
                            $total_macros = 0;
                            $macros = [];

                            if (isset($values['parameters']) === false) {
                                $values['parameters'] = '';
                            }

                            if (empty($values['net_dst_opt']) === false) {
                                $total_macros++;

                                $macro = [];
                                $macro['macro'] = '_field'.$total_macros.'_';
                                $macro['desc'] = 'Target IP from net';
                                $macro['help'] = '';
                                $macro['value'] = '';

                                $values['parameters'] .= $values['net_dst_opt'].' _field'.$total_macros.'_ ';

                                $macros[(string) $total_macros] = $macro;
                            }

                            if (empty($values['ip_opt']) === false) {
                                $total_macros++;

                                $macro = [];
                                $macro['macro'] = '_field'.$total_macros.'_';
                                $macro['desc'] = 'Target IP';
                                $macro['help'] = '';
                                $macro['value'] = '';

                                $values['parameters'] .= $values['ip_opt'].' _field'.$total_macros.'_ ';

                                $macros[(string) $total_macros] = $macro;
                            }

                            if (empty($values['net_port_opt']) === false) {
                                $total_macros++;

                                $macro = [];
                                $macro['macro'] = '_field'.$total_macros.'_';
                                $macro['desc'] = 'Port from net';
                                $macro['help'] = '';
                                $macro['value'] = '';

                                $values['parameters'] .= $values['net_port_opt'].' _field'.$total_macros.'_ ';

                                $macros[(string) $total_macros] = $macro;
                            }

                            if (empty($values['port_opt']) === false) {
                                $total_macros++;

                                $macro = [];
                                $macro['macro'] = '_field'.$total_macros.'_';
                                $macro['desc'] = 'Port';
                                $macro['help'] = '';
                                $macro['value'] = '';

                                $values['parameters'] .= $values['port_opt'].' _field'.$total_macros.'_ ';

                                $macros[(string) $total_macros] = $macro;
                            }

                            if (empty($values['user_opt']) === false) {
                                $total_macros++;

                                $macro = [];
                                $macro['macro'] = '_field'.$total_macros.'_';
                                $macro['desc'] = 'Username';
                                $macro['help'] = '';
                                $macro['value'] = '';

                                $values['parameters'] .= $values['user_opt'].' _field'.$total_macros.'_ ';

                                $macros[(string) $total_macros] = $macro;
                            }

                            if (empty($values['pass_opt']) === false) {
                                $total_macros++;

                                $macro = [];
                                $macro['macro'] = '_field'.$total_macros.'_';
                                $macro['desc'] = 'Password';
                                $macro['help'] = '';
                                $macro['value'] = '';

                                $values['parameters'] .= $values['pass_opt'].' _field'.$total_macros.'_ ';

                                $macros[(string) $total_macros] = $macro;
                            }

                            // A last parameter is defined always to
                            // add the old "Plug-in parameters" in the
                            // side of the module.
                            $total_macros++;

                            $macro = [];
                            $macro['macro'] = '_field'.$total_macros.'_';
                            $macro['desc'] = 'Plug-in Parameters';
                            $macro['help'] = '';
                            $macro['value'] = '';

                            $values['parameters'] .= ' _field'.$total_macros.'_';

                            $macros[(string) $total_macros] = $macro;
                        break;

                        case 'pspz2':
                            // Fill the macros field.
                            $total_macros = $ini_array['plugin_definition']['total_macros_provided'];

                            $macros = [];
                            for ($it_macros = 1; $it_macros <= $total_macros; $it_macros++) {
                                $label = 'macro_'.$it_macros;

                                $macro = [];

                                $macro['macro'] = '_field'.$it_macros.'_';
                                $macro['hide'] = $ini_array[$label]['hide'];
                                $macro['desc'] = io_safe_input(
                                    $ini_array[$label]['description']
                                );
                                $macro['help'] = io_safe_input(
                                    $ini_array[$label]['help']
                                );
                                $macro['value'] = io_safe_input(
                                    $ini_array[$label]['value']
                                );

                                $macros[(string) $it_macros] = $macro;
                            }
                        break;

                        default:
                            // Not possible.
                        break;
                    }

                    if (empty($macros) === false) {
                        $values['macros'] = json_encode($macros);
                    }

                    $create_id = db_process_sql_insert('tplugin', $values);

                    if (empty($create_id) === true) {
                        ui_print_error_message(
                            __('Plug-in Remote Registered unsuccessfull')
                        );
                        ui_print_info_message(
                            __('Please check the syntax of file "plugin_definition.ini"')
                        );
                    } else {
                        for ($ax = 1; $ax <= $ini_array['plugin_definition']['total_modules_provided']; $ax++) {
                            $label = 'module'.$ax;

                            $plugin_user = '';
                            if (isset($ini_array[$label]['plugin_user']) === true) {
                                $plugin_user = $ini_array[$label]['plugin_user'];
                            }

                            $plugin_pass = '';
                            if (isset($ini_array[$label]['plugin_pass']) === true) {
                                $plugin_pass = $ini_array[$label]['plugin_pass'];
                            }

                            $plugin_parameter = '';
                            if (isset($ini_array[$label]['plugin_parameter']) === true) {
                                $plugin_parameter = $ini_array[$label]['plugin_parameter'];
                            }

                            $unit = '';
                            if (isset($ini_array[$label]['unit']) === true) {
                                $unit = $ini_array[$label]['unit'];
                            }

                            $values = [
                                'name'               => io_safe_input($ini_array[$label]['name']),
                                'description'        => io_safe_input($ini_array[$label]['description']),
                                'id_group'           => $ini_array[$label]['id_group'],
                                'type'               => $ini_array[$label]['type'],
                                'max'                => ($ini_array[$label]['max'] ?? ''),
                                'min'                => ($ini_array[$label]['min'] ?? ''),
                                'module_interval'    => ($ini_array[$label]['module_interval'] ?? ''),
                                'id_module_group'    => $ini_array[$label]['id_module_group'],
                                'id_modulo'          => $ini_array[$label]['id_modulo'],
                                'plugin_user'        => io_safe_input($plugin_user),
                                'plugin_pass'        => io_safe_input($plugin_pass),
                                'plugin_parameter'   => io_safe_input($plugin_parameter),
                                'unit'               => io_safe_input($unit),
                                'max_timeout'        => ($ini_array[$label]['max_timeout'] ?? ''),
                                'history_data'       => ($ini_array[$label]['history_data'] ?? ''),
                                'dynamic_interval'   => ($ini_array[$label]['dynamic_interval'] ?? ''),
                                'dynamic_min'        => ($ini_array[$label]['dynamic_min'] ?? ''),
                                'dynamic_max'        => ($ini_array[$label]['dynamic_max'] ?? ''),
                                'dynamic_two_tailed' => ($ini_array[$label]['dynamic_two_tailed'] ?? ''),
                                'min_warning'        => ($ini_array[$label]['min_warning'] ?? ''),
                                'max_warning'        => ($ini_array[$label]['max_warning'] ?? ''),
                                'str_warning'        => ($ini_array[$label]['str_warning'] ?? ''),
                                'min_critical'       => ($ini_array[$label]['min_critical'] ?? ''),
                                'max_critical'       => ($ini_array[$label]['max_critical'] ?? ''),
                                'str_critical'       => ($ini_array[$label]['str_critical'] ?? ''),
                                'min_ff_event'       => ($ini_array[$label]['min_ff_event'] ?? ''),
                                'tcp_port'           => ($ini_array[$label]['tcp_port'] ?? ''),
                                'id_plugin'          => $create_id,
                            ];

                            $macros_component = $macros;

                            switch ($version) {
                                case 'pspz':
                                    // Fixed the static parameters
                                    // for
                                    // the dinamic parameters of pandoras 5.
                                    foreach ($macros_component as $key => $macro) {
                                        if ($macro['desc'] === 'Target IP from net') {
                                            if (empty($values['ip_target']) === false) {
                                                $macros_component[$key]['value'] = io_safe_input(
                                                    $values['ip_target']
                                                );
                                            }
                                        }

                                        if ($macro['desc'] === 'Target IP') {
                                            if (empty($values['ip_target']) === false) {
                                                $macros_component[$key]['value'] = io_safe_input(
                                                    $values['ip_target']
                                                );
                                            }
                                        } else if ($macro['desc'] === 'Port from net') {
                                            if (empty($values['tcp_port']) === false) {
                                                $macros_component[$key]['value'] = io_safe_input(
                                                    $values['tcp_port']
                                                );
                                            }
                                        } else if ($macro['desc'] === 'Port') {
                                            if (empty($values['tcp_port']) === false) {
                                                $macros_component[$key]['value'] = io_safe_input(
                                                    $values['tcp_port']
                                                );
                                            }
                                        } else if ($macro['desc'] === 'Username') {
                                            if (empty($values['plugin_user']) === false) {
                                                $macros_component[$key]['value'] = io_safe_input(
                                                    $values['plugin_user']
                                                );
                                            }
                                        } else if ($macro['desc'] === 'Password') {
                                            if (empty($values['plugin_pass']) === false) {
                                                $macros_component[$key]['value'] = io_safe_input(
                                                    $values['plugin_pass']
                                                );
                                            }
                                        } else if ($macro['desc'] === 'Plug-in Parameters') {
                                            if (empty($values['plugin_parameter']) === false) {
                                                $macros_component[$key]['value'] = io_safe_input(
                                                    $values['plugin_parameter']
                                                );
                                            }
                                        }
                                    }
                                break;

                                case 'pspz2':
                                    if ($total_macros > 0) {
                                        for ($it_macros = 1; $it_macros <= $total_macros; $it_macros++) {
                                            $macro = 'macro_'.$it_macros.'_value';

                                            // Set the value or use the default.
                                            if (isset($ini_array[$label][$macro]) === true) {
                                                $macros_component[(string) $it_macros]['value'] = io_safe_input(
                                                    $ini_array[$label][$macro]
                                                );
                                            }
                                        }
                                    }
                                break;

                                default:
                                    // Not possible.
                                break;
                            }

                            if (empty($macros_component) === false) {
                                $values['macros'] = json_encode($macros_component);
                            }

                            db_process_sql_insert('tnetwork_component', $values);

                            ui_print_success_message(
                                __('Module plugin registered').' : '.$ini_array[$label]['name']
                            );
                        }

                        ui_print_success_message(
                            __('Plugin').' '.$ini_array['plugin_definition']['name'].' '.__('Registered successfully')
                        );
                    }

                    unlink($config['attachment_store'].'/plugin_definition.ini');
                }
            }
        }


        // Clean.
        Files::rmrf($tmp_path);
    } else {
        $error = __('Unable to uncompress uploaded file');
    }
}

if (isset($uploaded_filename) === true) {
    if (file_exists($uploaded_filename) === true) {
            // Clean temporary files.
            unlink($uploaded_filename);
    }
}


if ($error !== null && $error !== '') {
    ui_print_error_message($error);
}
