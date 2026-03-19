<?php

/**
 * Pandora OPEN
 * Copyright (c) 2004-2026 Pandora FMS
 * https://pandoraopen.io
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
if (defined('__PAN_XHPROF__') === false) {
    define('__PAN_XHPROF__', 0);
}

// Needed for InfoBox count.
if (isset($_SESSION['info_box_count']) === true) {
    $_SESSION['info_box_count'] = 0;
}

// Set character encoding to UTF-8
// fixes a lot of multibyte character issues.
if (function_exists('mb_internal_encoding') === true) {
    mb_internal_encoding('UTF-8');
}

// Set to 1 to do not check for installer or config file (for development!).
// Activate gives more error information, not useful for production sites.
$develop_bypass = 0;

if ($develop_bypass !== 1) {
    // If no config file, automatically try to install.
    if (file_exists('include/config.php') === false) {
            $url = explode('/', $_SERVER['REQUEST_URI']);
            $flag_url = 0;
            foreach ($url as $key => $value) {
                if (strpos($value, 'index.php') !== false || $flag_url) {
                    $flag_url = 1;
                    unset($url[$key]);
                }
            }

            $config['homeurl'] = rtrim(join('/', $url), '/');
            $config['homeurl_static'] = $config['homeurl'];
            $login_screen = 'error_noconfig';
            $ownDir = dirname(__FILE__).DIRECTORY_SEPARATOR;
	    $config['homedir'] = $ownDir;
	    $error_title="No config file found";
	    $error_text="Cannot find a proper configuration file at 'include/config.php'. Aborting";
            include 'general/error.php';
            exit;
	 
    }

    if (filesize('include/config.php') == 0) {

            $error_title="Invalid config file found";
            $error_text="Cannot read contents of configuration file at 'include/config.php'. Aborting";
            include 'general/error.php';
            exit;   
    }

    // Check perms for config.php.
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        if ((substr(sprintf('%o', fileperms('include/config.php')), -4) !== '0600')
            && (substr(sprintf('%o', fileperms('include/config.php')), -4) !== '0660')
            && (substr(sprintf('%o', fileperms('include/config.php')), -4) !== '0640')
        ) {
            $url = explode('/', $_SERVER['REQUEST_URI']);
            $flag_url = 0;
            foreach ($url as $key => $value) {
                if (strpos($value, 'index.php') !== false || $flag_url) {
                    $flag_url = 1;
                    unset($url[$key]);
                }
            }

            $config['homeurl'] = rtrim(join('/', $url), '/');
            $config['homeurl_static'] = $config['homeurl'];
            $ownDir = dirname(__FILE__).DIRECTORY_SEPARATOR;
            $config['homedir'] = $ownDir;

            $error_title="Invalid permission for config file";
                $error_text="Aborting startup. Configuration file has insecure file access rights, please check 'include/config.php'. Aborting";
                include 'general/error.php';
                exit;
            }
    }
}

if ((file_exists('include/config.php') === false)
    || (is_readable('include/config.php') === false)
) {
	$error_title="No config file found";
        $error_text="Cannot find a proper configuration file at 'include/config.php'. Aborting";
        include 'general/error.php';
        exit;   
}

require 'vendor/autoload.php';

if (__PAN_XHPROF__ === 1) {
    if (function_exists('tideways_xhprof_enable') === true) {
        tideways_xhprof_enable();
    } else {
        error_log('Cannot find tideways_xhprof_enable function');
    }
}

/*
 * DO NOT CHANGE ORDER OF FOLLOWING REQUIRES.
 */

require_once 'include/config.php';
require_once 'include/functions_config.php';

if (isset($config['console_log_enabled']) === true && (int) $config['console_log_enabled'] === 1) {
    ini_set('log_errors', 1);
    ini_set('error_log', $config['homedir'].'/log/console.log');
} else {
    ini_set('log_errors', 0);
    ini_set('error_log', '');
}

if (isset($config['error']) === true) {
    $login_screen = $config['error'];
    include 'general/error_screen.php';
    exit;
}


if (empty($config['https']) === false && empty($_SERVER['HTTPS']) === true) {
    $query = '';
    if (count($_REQUEST) > 0) {
        // Some (old) browsers don't like the ?&key=var.
        $query .= '?1=1';
    }

    // We don't clean these variables up as they're only being passed along.
    foreach ($_GET as $key => $value) {
        if ($key == 1) {
            continue;
        }

        $query .= '&'.$key.'='.$value;
    }

    foreach ($_POST as $key => $value) {
        $query .= '&'.$key.'='.$value;
    }

    $url = ui_get_full_url($query);

    // Prevent HTTP response splitting attacks
    // http://en.wikipedia.org/wiki/HTTP_response_splitting.
    $url = str_replace("\n", '', $url);

    header('Location: '.$url);
    // Always exit after sending location headers.
    exit;
}

// Pure mode (without menu, header and footer).
$config['pure'] = (bool) get_parameter('pure');

// Auto Refresh page (can now be disabled anywhere in the script).
if (get_parameter('refr') != null) {
    $config['refr'] = (int) get_parameter('refr');
}

// Get possible errors with files.
$errorFileOutput = (string) get_parameter('errorFileOutput');

ob_start();
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";
echo '<html xmlns="http://www.w3.org/1999/xhtml">'."\n";
echo '<head>'."\n";

// This starts the page head. In the callback function,
// $page['head'] array content will be processed into the head.
ob_start('ui_process_page_head');

// Load event.css to display the about section dialog with correct styles.
echo '<link rel="stylesheet" href="'.ui_get_full_url('/include/styles/events.css', false, false, false).'?v='.$config['current_package'].'" type="text/css" />';

echo '<script type="text/javascript">';
echo 'var dispositivo = navigator.userAgent.toLowerCase();';
echo 'if( dispositivo.search(/iphone|ipod|ipad|android/) > -1 ){';
echo 'document.location = "'.ui_get_full_url('/mobile').'";  }';
echo '</script>';

// This tag is included in the buffer passed to ui_process_page_head so
// technically it can be stripped.
echo '</head>'."\n";

require_once 'include/functions_themes.php';
ob_start('ui_process_page_body');

ui_require_javascript_file('pandora');

$config['remote_addr'] = $_SERVER['REMOTE_ADDR'];

$sec2 = get_parameter_get('sec2');
$sec2 = safe_url_extraclean($sec2);
$page = $sec2;
// Reference variable for old time sake.
$sec = get_parameter_get('sec');
$sec = safe_url_extraclean($sec);
// CSRF Validation.
$validatedCSRF = validate_csrf_code();

$process_login = false;

// Update user password.
$change_pass = (int) get_parameter_post('renew_password');

if ($change_pass === 1) {
    $password_old = (string) get_parameter_post('old_password', '');
    $password_new = (string) get_parameter_post('new_password', '');
    $password_confirm = (string) get_parameter_post('confirm_new_password', '');
    $id = (string) get_parameter_post('login', '');

    $changed_pass = login_update_password_check($password_old, $password_new, $password_confirm, $id);
}

$minor_release_message = false;
$searchPage = false;
$search = get_parameter_get('head_search_keywords');
if (strlen($search) > 0) {
    $config['search_keywords'] = io_safe_input(trim(io_safe_output(get_parameter('keywords'))));
    // If not search category providad, we'll use an agent search.
    $config['search_category'] = get_parameter('search_category', 'all');
    if (($config['search_keywords'] !== 'Enter keywords to search') && (strlen($config['search_keywords']) > 0)) {
        $searchPage = true;
    }
}

// Login process.
if (isset($config['id_user']) === false) {
    // Clear error messages.
    unset($_COOKIE['errormsg']);
    setcookie('errormsg', '', -1);

    if (isset($_GET['login']) === true) {
        include_once 'include/functions_db.php';
        // Include it to use escape_string_sql function.
        $config['auth_error'] = '';
        // Set this to the error message from the authorization mechanism.
        $nick = get_parameter_post('nick');
        // This is the variable with the login.
        $pass = get_parameter_post('pass');
        // This is the variable with the password.
        $nick = db_escape_string_sql($nick);
        $pass = db_escape_string_sql($pass);

        // Since now, only the $pass variable are needed.
        unset($_GET['pass'], $_POST['pass'], $_REQUEST['pass']);

        // IP allowed check.
        $user_info = users_get_user_by_id($nick);
        if ((bool) $user_info['allowed_ip_active'] === true) {
            $userIP = $_SERVER['REMOTE_ADDR'];
            $allowedIP = false;
            $arrayIP = explode(',', $user_info['allowed_ip_list']);
            // By default, if the IP definition is no correct, allows all.
            if (empty($arrayIP) === true) {
                $allowedIP = true;
            } else {
                $allowedIP = checkIPInRange($arrayIP, $userIP);
            }

            if ($allowedIP === false) {
                $config['auth_error'] = 'IP not allowed';
                $login_failed = true;
                include_once 'general/login_page.php';
                db_pandora_audit(
                    AUDIT_LOG_USER_REGISTRATION,
                    sprintf(
                        'IP %s not allowed for user %s',
                        $userIP,
                        $nick
                    ),
                    $nick
                );
                while (ob_get_length() > 0) {
                    ob_end_flush();
                }

                exit('</html>');
            }
        }

        // If the auth_code exists, we assume the user has come from
        // double authorization page.
        if (isset($_POST['auth_code']) === true) {
            $double_auth_success = false;

            // The double authentication is activated and the user has
            // surpassed the first step (the login).
            // Now the authentication code provided will be checked.
            if (isset($_SESSION['prepared_login_da']) === true) {
                if (isset($_SESSION['prepared_login_da']['id_user']) === true
                    && isset($_SESSION['prepared_login_da']['timestamp']) === true
                ) {
                    // The user has a maximum of 5 minutes to introduce
                    // the double auth code.
                    $dauth_period = SECONDS_2MINUTES;
                    $now = time();
                    $dauth_time = $_SESSION['prepared_login_da']['timestamp'];

                    if (($now - $dauth_period) < $dauth_time) {
                        // Nick.
                        $nick = $_SESSION['prepared_login_da']['id_user'];
                        // Code.
                        $code = (string) get_parameter_post('auth_code');

                        if (empty($code) === false) {
                            $result = validate_double_auth_code($nick, $code);

                            if ($result === true) {
                                // Double auth success.
                                $double_auth_success = true;
                            } else {
                                // Screen.
                                $login_screen = 'double_auth';
                                // Error message.
                                $config['auth_error'] = __('Invalid code');

                                if (isset($_SESSION['prepared_login_da']['attempts']) === false) {
                                    $_SESSION['prepared_login_da']['attempts'] = 0;
                                }

                                $_SESSION['prepared_login_da']['attempts']++;
                            }
                        } else {
                            // Screen.
                            $login_screen = 'double_auth';
                            // Error message.
                            $config['auth_error'] = __("The code shouldn't be empty");

                            if (isset($_SESSION['prepared_login_da']['attempts']) !== false) {
                                $_SESSION['prepared_login_da']['attempts'] = 0;
                            }

                            $_SESSION['prepared_login_da']['attempts']++;
                        }
                    } else {
                        // Expired login.
                        unset($_SESSION['prepared_login_da']);

                        // Error message.
                        $config['auth_error'] = __('Expired login');
                    }
                } else {
                    // If the code doesn't exist, remove the prepared login.
                    unset($_SESSION['prepared_login_da']);

                    // Error message.
                    $config['auth_error'] = __('Login error');
                }
            } else {
                // If $_SESSION['prepared_login_da'] doesn't exist, the user
                // must login again.
                // Error message.
                $config['auth_error'] = __('Login error');
            }

            // Remove the authenticator code.
            unset($_POST['auth_code'], $code);

            if (!$double_auth_success) {
                $config['auth_error'] = __('Double auth error');
                $login_failed = true;
                include_once 'general/login_page.php';
                db_pandora_audit(
                    AUDIT_LOG_USER_REGISTRATION,
                    'Invalid double auth login: '.$_SERVER['REMOTE_ADDR'],
                    $_SERVER['REMOTE_ADDR']
                );
                while (ob_get_length() > 0) {
                    ob_end_flush();
                }

                exit('</html>');
            }
        }

        $login_button_saml = get_parameter('login_button_saml', false);
        config_update_value('2Fa_auth', '');
        if (isset($double_auth_success) && $double_auth_success) {
            // This values are true cause there are checked before complete
            // the 2nd auth step.
            $nick_in_db = $_SESSION['prepared_login_da']['id_user'];
            $expired_pass = false;
        } else {
            // process_user_login is a virtual function which should be defined in each auth file.
            // It accepts username and password. The rest should be internal to the auth file.
            // The auth file can set $config["auth_error"] to an informative error output or reference their internal error messages to it
            // process_user_login should return false in case of errors or invalid login, the nickname if correct.
            $nick_in_db = process_user_login($nick, $pass);

            $expired_pass = false;
        }

        // CSRF Validation not pass in login.
        if ($validatedCSRF === false) {
            $process_error_message = __(
                '%s cannot verify the origin of the request. Try again, please.',
                get_product_name()
            );

            include_once 'general/login_page.php';
            // Finish the execution.
            exit('</html>');
        }

        if (($nick_in_db !== false) && $expired_pass) {
            // Login ok and password has expired.
            include_once 'general/login_page.php';
            db_pandora_audit(
                AUDIT_LOG_USER_MANAGEMENT,
                'Password expired: '.$nick,
                $nick
            );
            while (ob_get_length() > 0) {
                ob_end_flush();
            }

            exit('</html>');
        } else if (($nick_in_db !== false) && (!$expired_pass)) {
            // Login ok and password has not expired.
            // Double auth check.
            if ((!isset($double_auth_success)
                || !$double_auth_success)
                && is_double_auth_enabled($nick_in_db)
                && (bool) $config['double_auth_enabled'] === true
            ) {
                // Store this values in the session to know if the user login
                // was correct.
                $_SESSION['prepared_login_da'] = [
                    'id_user'   => $nick_in_db,
                    'timestamp' => time(),
                    'attempts'  => 0,
                ];

                // Load the page to introduce the double auth code.
                $login_screen = 'double_auth';
                include_once 'general/login_page.php';
                while (ob_get_length() > 0) {
                    ob_end_flush();
                }

                exit('</html>');
            }

            if (isset($config['pending_sync_process_message']) === true) {
                include_once 'general/login_page.php';
                while (ob_get_length() > 0) {
                    ob_end_flush();
                }

                exit('</html>');
            }

            // Login ok and password has not expired.
            $process_login = true;

            if (is_user_admin($nick)) {
                echo "<script type='text/javascript'>var process_login_ok = 1;</script>";
            } else {
                echo "<script type='text/javascript'>var process_login_ok = 0;</script>";
            }

            if (!isset($_GET['sec2']) && !isset($_GET['sec'])) {
                // Avoid the show homepage when the user go to
                // a specific section of pandora
                // for example when timeout the sesion.
                unset($_GET['sec2']);
                $_GET['sec'] = 'general/logon_ok';
                $home_page = '';
                if (isset($nick)) {
                    $user_info = users_get_user_by_id($nick);
                    $home_page = io_safe_output($user_info['section']);
                    $home_url = $user_info['data_section'];
                    if ($home_page != '') {
                        switch ($home_page) {
                            case 'event_list':
                                $_GET['sec'] = 'eventos';
                                $_GET['sec2'] = 'operation/events/events';
                            break;

                            case 'group_view':
                                $_GET['sec'] = 'view';
                                $_GET['sec2'] = 'operation/agentes/group_view';
                            break;

                            case 'alert_detail':
                                $_GET['sec'] = 'view';
                                $_GET['sec2'] = 'operation/agentes/alerts_status';
                            break;

                            case 'tactical_view':
                                $_GET['sec'] = 'view';
                                $_GET['sec2'] = 'operation/agentes/tactical';
                            break;

                            case 'default':
                            default:
                                $_GET['sec'] = 'general/logon_ok';
                            break;

                            case 'dashboard':
                                $_GET['sec'] = 'reporting';
                                $_GET['sec2'] = 'operation/dashboard/dashboard';
                                $_GET['id_dashboard_select'] = $home_url;
                                $_GET['d_from_main_page'] = 1;
                            break;

                            case 'visual_console':
                                $_GET['sec'] = 'network';
                                $_GET['sec2'] = 'operation/visual_console/index';
                            break;

                            case 'other':
                                $home_url = io_safe_output($home_url);
                                $url_array = parse_url($home_url);
                                parse_str($url_array['query'], $res);
                                foreach ($res as $key => $param) {
                                    $_GET[$key] = $param;
                                }
                            break;
                        }
                    } else {
                        $_GET['sec'] = 'general/logon_ok';
                    }
                }
            }

            if (is_reporting_console_node() === true) {
                $_GET['sec'] = 'discovery';
                $_GET['sec2'] = 'godmode/servers/discovery';
                $_GET['wiz'] = 'tasklist';
                $home_page = '';
            }

            db_logon($nick_in_db, $_SERVER['REMOTE_ADDR']);
            $_SESSION['id_usuario'] = $nick_in_db;
            $config['id_user'] = $nick_in_db;
            $_SESSION['logged'] = true;
            config_prepare_expire_time_session(true);

            // Check if connection goes through F5 balancer. If it does, then
            // don't call config_prepare_session() or user will be back to login
            // all the time.
            $prepare_session = true;
            foreach ($_COOKIE as $key => $value) {
                if (preg_match('/BIGipServer*/', $key)) {
                    $prepare_session = false;
                    break;
                }
            }

            if ($prepare_session) {
                config_prepare_session();
            }

            // ==========================================================
            // -------- SET THE CUSTOM CONFIGS OF USER ------------------
            config_user_set_custom_config();
            // ==========================================================
            // Remove everything that might have to do with people's passwords or logins
            unset($pass, $login_good);

            $user_language = get_user_language($config['id_user']);

            $l10n = null;
            if (file_exists('./include/languages/'.$user_language.'.mo') === true) {
                $cacheFileReader = new CachedFileReader(
                    './include/languages/'.$user_language.'.mo'
                );
                $l10n = new gettext_reader($cacheFileReader);
                $l10n->load_tables();
            }
        } else {
            // Login wrong.
            $login_failed = true;

            include_once 'general/login_page.php';
            db_pandora_audit(
                AUDIT_LOG_USER_REGISTRATION,
                'Invalid login: '.$nick,
                $nick
            );

            while (ob_get_length() > 0) {
                ob_end_flush();
            }

            exit('</html>');
        }

        // Form the url.
        $query_params_redirect = $_GET;
        // Visual console do not want sec2.
        if ($home_page === 'visual_console') {
            unset($query_params_redirect['sec2']);
        }

        // Dashboard do not want sec2.
        if ($home_page === 'dashboard') {
            unset($query_params_redirect['sec2']);
        }

        $redirect_url = '?logged=1';
        foreach ($query_params_redirect as $key => $value) {
            if ($key === 'login') {
                continue;
            }

            $redirect_url .= '&'.safe_url_extraclean($key).'='.safe_url_extraclean($value);
        }

        $double_auth_enabled = (bool) db_get_value('id', 'tuser_double_auth', 'id_user', $config['id_user']);

        header('Location: '.ui_get_full_url('index.php'.$redirect_url));
        exit;
        // Always exit after sending location headers.
    } else if (isset($_POST['auth_token']) === true && (bool) $config['JWT_signature'] !== false) {
        include_once $config['homedir'].'/include/class/JWTRepository.class.php';
        $jwt = new JWTRepository($config['JWT_signature']);
        if ($jwt->setToken($_POST['auth_token']) && $jwt->validate()) {
            $id_user = $jwt->payload()->get('id_user');
            db_logon($id_user, $_SERVER['REMOTE_ADDR']);
            $_SESSION['id_usuario'] = $id_user;
            $config['id_user'] = $id_user;
        } else {
            include_once 'general/login_page.php';
            db_pandora_audit(
                AUDIT_LOG_USER_REGISTRATION,
                'Login token failed',
                'system'
            );
            while (ob_get_length() > 0) {
                ob_end_flush();
            }

            exit('</html>');
        }
    } else if (isset($_GET['bye']) === false) {
        // Boolean parameters.
        $correct_pass_change = (bool) get_parameter('correct_pass_change', false);
        $reset               = (bool) get_parameter('reset', false);
        $first               = (bool) get_parameter('first', false);
        // Strings.
        $reset_hash          = get_parameter('reset_hash');
        $pass1               = get_parameter_post('pass1');
        $pass2               = get_parameter_post('pass2');
        $id_user             = get_parameter_post('id_user');

        $db_reset_pass_entry = false;
        if (empty($reset_hash) === false) {
            $hash_data = explode(':::', $reset_hash);
            $id_user = $hash_data[0];
            $codified_hash = $hash_data[1];

            $db_reset_pass_entry = db_get_value_filter('reset_time', 'treset_pass', ['id_user' => $id_user, 'cod_hash' => $id_user.':::'.$codified_hash]);
        }

        if ($correct_pass_change === true
            && empty($pass1) === false
            && empty($pass2) === false
            && empty($id_user) === false
            && $db_reset_pass_entry !== false
        ) {
            // The CSRF does not be validated.
            if ($validatedCSRF === false) {
                $process_error_message = __(
                    '%s cannot verify the origin of the request. Try again, please.',
                    get_product_name()
                );

                include_once 'general/login_page.php';
                // Finish the execution.
                exit('</html>');
            } else {
                delete_reset_pass_entry($id_user);
                $correct_reset_pass_process = '';
                $process_error_message = '';

                if ($pass1 === $pass2) {
                    $res = update_user_password($id_user, $pass1);
                    if ($res) {
                        db_process_sql_insert(
                            'tsesion',
                            [
                                'id_sesion'   => '',
                                'id_usuario'  => $id_user,
                                'ip_origen'   => $_SERVER['REMOTE_ADDR'],
                                'accion'      => 'Reset&#x20;change',
                                'descripcion' => 'Successful reset password process ',
                                'fecha'       => date('Y-m-d H:i:s'),
                                'utimestamp'  => time(),
                            ]
                        );

                        $correct_reset_pass_process = __('Password changed successfully');

                        register_pass_change_try($id_user, 1);
                    } else {
                        register_pass_change_try($id_user, 0);

                        $process_error_message = __('Failed to change password');
                    }
                } else {
                    register_pass_change_try($id_user, 0);

                    $process_error_message = __('Passwords must be the same');
                }

                include_once 'general/login_page.php';
            }
        } else {
            if (empty($reset_hash) === false) {
                $process_error_message = '';

                if ($db_reset_pass_entry) {
                    if (($db_reset_pass_entry + SECONDS_2HOUR) < time()) {
                        register_pass_change_try($id_user, 0);
                        $process_error_message = __('Too much time since password change request');
                        include_once 'general/login_page.php';
                    }
                } else {
                    register_pass_change_try($id_user, 0);
                    $process_error_message = __('This user has not requested a password change');
                    include_once 'general/login_page.php';
                }
            } else {
                if ($reset === false) {
                    include_once 'general/login_page.php';
                } else {
                    $user_reset_pass = get_parameter('user_reset_pass');
                    $error = '';
                    $mail = '';
                    $show_error = false;

                    if ($first === false) {
                        // The CSRF does not be validated.
                        if ($validatedCSRF === false) {
                            $process_error_message = __(
                                '%s cannot verify the origin of the request. Try again, please.',
                                get_product_name()
                            );

                            include_once 'general/login_page.php';
                            // Finish the execution.
                            exit('</html>');
                        }

                        if (empty($user_reset_pass) === true) {
                            $reset = false;
                            $error = __('Id user cannot be empty');
                            $show_error = true;
                        } else {
                            $check_user = check_user_id($user_reset_pass);

                            if ($check_user === false) {
                                $reset = false;
                                register_pass_change_try($user_reset_pass, 0);
                                $error = __('Error in reset password request');
                                $show_error = true;
                            } else {
                                $check_mail = check_user_have_mail($user_reset_pass);

                                if (!$check_mail) {
                                    $reset = false;
                                    register_pass_change_try($user_reset_pass, 0);
                                    $error = __('This user doesn\'t have a valid email address');
                                    $show_error = true;
                                } else {
                                    $mail = $check_mail;
                                }
                            }
                        }

                        $cod_hash = $user_reset_pass.'::::'.md5(rand(10, 1000000).rand(10, 1000000).rand(10, 1000000));

                        $subject = '['.io_safe_output(get_product_name()).'] '.__('Reset password');
                        $body = __('This is an automatically sent message for user ');
                        $body .= ' "<strong>'.$user_reset_pass.'"</strong>';
                        $body .= '<p />';
                        $body .= __('Please click the link below to reset your password');
                        $body .= '<p />';
                        $body .= '<a href="'.ui_get_full_url('index.php?reset_hash='.$cod_hash).'">'.__('Reset your password').'</a>';
                        $body .= '<p />';
                        $body .= get_product_name();
                        $body .= '<p />';
                        $body .= '<em>'.__('Please do not reply to this email.').'</em>';

                        $result = (bool) send_email_to_user($mail, $body, $subject);

                        if ($result === false) {
                            $process_error_message = __('Error at sending the email');
                        } else {
                            send_token_to_db($user_reset_pass, $cod_hash);
                        }

                        include_once 'general/login_page.php';
                    }
                }
            }
        }

        while (ob_get_length() > 0) {
            ob_end_flush();
        }

        exit('</html>');
    }
} else {
    if (isset($_POST['auth_token']) === true && (bool) $config['JWT_signature'] !== false) {
        include_once $config['homedir'].'/include/class/JWTRepository.class.php';
        $jwt = new JWTRepository($config['JWT_signature']);
        if ($jwt->setToken($_POST['auth_token']) && $jwt->validate()) {
            $iduser = $_SESSION['id_usuario'];
            unset($_SESSION['id_usuario']);
            unset($iduser);
            $id_user = $jwt->payload()->get('id_user');
            db_logon($id_user, $_SERVER['REMOTE_ADDR']);
            $_SESSION['id_usuario'] = $id_user;
            $config['id_user'] = $id_user;
        }
    }

    $user_in_db = db_get_row_filter(
        'tusuario',
        ['id_user' => $config['id_user']],
        '*'
    );
    if ($user_in_db == false) {
        // Logout.
        $_REQUEST = [];
        $_GET = [];
        $_POST = [];
        $config['auth_error'] = __("User doesn\'t exist.");
        $iduser = $_SESSION['id_usuario'];
        unset($_SESSION['id_usuario']);
        unset($iduser);
        include_once 'general/login_page.php';
        while (ob_get_length() > 0) {
            ob_end_flush();
        }

        exit('</html>');
    } else {
        if (((bool) $user_in_db['is_admin'] === false) && ((bool) $user_in_db['not_login'] === true)) {
            // Logout.
            $_REQUEST = [];
            $_GET = [];
            $_POST = [];
            $config['auth_error'] = __('User only can use the API.');
            $iduser = $_SESSION['id_usuario'];
            unset($_SESSION['id_usuario']);
            unset($iduser);
            $login_screen = 'disabled_access_node';
            include_once 'general/login_page.php';
            while (ob_get_length() > 0) {
                ob_end_flush();
            }

            exit('</html>');
        }
    }
}

if ((bool) ($config['maintenance_mode'] ?? false) === true
    && is_user_admin($config['id_user']) === false
) {
    // Show maintenance web-page. For non-admin users only.
    include $config['homedir'].'/general/maintenance.php';

    while (ob_get_length() > 0) {
        ob_end_flush();
    }

    exit('</html>');
}

// Log off.
if (isset($_GET['bye'])) {
    $iduser = $_SESSION['id_usuario'];

    $_SESSION = [];
    session_destroy();
    header_remove('Set-Cookie');
    setcookie(session_name(), $_COOKIE[session_name()], (time() - 4800), '/');

    generate_csrf_code();
    // Process logout.
    include 'general/logoff.php';

    while (ob_get_length() > 0) {
        ob_end_flush();
    }

    exit('</html>');
}

clear_pandora_error_for_header();

if ((bool) ($config['maintenance_mode'] ?? false) === true
    && (bool) users_is_admin() === false
) {
    // Show maintenance web-page. For non-admin users only.
    include 'general/maintenance.php';

    while (ob_get_length() > 0) {
        ob_end_flush();
    }

    exit('</html>');
}

if (is_reporting_console_node() === true
    && (bool) users_is_admin() === false
) {
    include 'general/reporting_console_node.php';
    exit;
}

/*
 * ----------------------------------------------------------------------
    *  EXTENSIONS
    * ----------------------------------------------------------------------
    *
    * Load the basic configurations of extension and add extensions into menu.
    * Load here, because if not, some extensions not load well, I don't why.
 */

$config['logged'] = false;
extensions_load_extensions($process_login);

if ($process_login) {
    // Call all extensions login function.
    extensions_call_login_function();

    unset($_SESSION['new_update']);

    $config['logged'] = true;
}

require_once 'general/register.php';

if (get_parameter('login', 0) !== 0) {
    if ((!isset($config['skip_login_help_dialog']) || $config['skip_login_help_dialog'] == 0)
        && $display_previous_popup === false
        && $config['initial_wizard'] == 1
    ) {
        include_once 'general/login_help_dialog.php';
    }

    $php_version = phpversion();
    $php_version_array = explode('.', $php_version);
    if ($php_version_array[0] < 7) {
        include_once 'general/php_message.php';
    }
}


if ((bool) ($config['maintenance_mode'] ?? false) === true
    && (bool) users_is_admin() === false
) {
    // Show maintenance web-page. For non-admin users only.
    include 'general/maintenance.php';

    while (ob_get_length() > 0) {
        ob_end_flush();
    }

    exit('</html>');
}


// Pure.
if ($config['pure'] == 0) {
    // Menu container prepared to autohide menu.
    $menuCollapsed = (isset($_SESSION['menu_type']) === true && $_SESSION['menu_type'] !== 'classic');
    $menuTypeClass = ($menuCollapsed === true) ? 'collapsed' : 'classic';
    // Snow.
    $string = '<div id="container-snow" class="tpl-snow invisible">
                <div></div><div></div><div></div><div></div><div></div><div></div><div></div>
                <div></div><div></div><div></div><div></div><div></div><div></div><div></div>
                <div></div><div></div><div></div><div></div><div></div><div></div><div></div>
                <div></div><div></div><div></div><div></div><div></div><div></div><div></div>
                <div></div><div></div><div></div><div></div><div></div><div></div><div></div>
                <div></div><div></div><div></div><div></div><div></div><div></div><div></div>
                <div></div><div></div><div></div><div></div><div></div>
              </div>';
    // Container.
    echo '<div id="container">'.$string;

    // Notifications content wrapper
    echo '<div id="notification-content" class="invisible"/></div>';

    // Header.
    echo '<div id="head">';
    include 'general/header.php';
    echo '</div>';
    // Main menu.
    echo sprintf('<div id="page" class="page_%s">', $menuTypeClass);
    echo '<div id="menu">';

    include 'general/main_menu.php';
    echo html_print_go_top();
} else {
    echo '<div id="main_pure">';
    // Require menu only to build structure to use it in ACLs.
    include 'operation/menu.php';
    include 'godmode/menu.php';
}

/*
 * Session locking concurrency speedup!
    * http://es2.php.net/manual/en/ref.session.php#64525
 */

session_write_close();


// Main block of content.
if ($config['pure'] == 0) {
    echo '<div id="main">';
}

if (is_reporting_console_node() === true) {
    echo notify_reporting_console_node();
}

// Page loader / selector.
if ($searchPage) {
    include 'operation/search_results.php';
} else {
    if ($page != '') {
        $main_sec = get_sec($sec);
        if ($main_sec == false) {
            if ($sec == 'extensions') {
                $main_sec = get_parameter('extension_in_menu');
                if (empty($main_sec) === true) {
                    $main_sec = $sec;
                }
            } else if ($sec == 'gextensions') {
                $main_sec = get_parameter('extension_in_menu');
                if (empty($main_sec) === true) {
                    $main_sec = $sec;
                }
            } else {
                $main_sec = $sec;
            }

            $sec = $sec2;
            $sec2 = '';
        }

        $tab = get_parameter('tab', '');
        if (empty($tab) === true) {
            $tab = get_parameter('wiz', '');
        }

        $acl_reporting_console_node = acl_reporting_console_node($page, $tab);
        if ($acl_reporting_console_node === false) {
            include 'general/reporting_console_node.php';
            exit;
        }

        $page .= '.php';

        $sec = $main_sec;
        if (file_exists($page) === true) {
            if ((bool) extensions_is_extension($page) === false) {
                try {
                    include_once $page;
                } catch (Exception $e) {
                    ui_print_error_message(
                        $e->getMessage().' in '.$e->getFile().':'.$e->getLine()
                    );
                }
            } else {
                if ($sec[0] == 'g') {
                    extensions_call_godmode_function(basename($page));
                } else {
                    extensions_call_main_function(basename($page));
                }
            }
        } else {
            ui_print_error_message(__('Sorry! I can\'t find the page!'));
        }
    } else {
        // Home screen chosen by the user.
        $home_page = '';
        if (isset($config['id_user']) === true) {
            $user_info = users_get_user_by_id($config['id_user']);
            $home_page = io_safe_output($user_info['section']);
            $home_url = $user_info['data_section'];
        }

        if ($home_page != '') {
            switch ($home_page) {
                case 'event_list':
                    $_GET['sec'] = 'eventos';
                    $_GET['sec2'] = 'operation/events/events';
                break;

                case 'group_view':
                    $_GET['sec'] = 'view';
                    $_GET['sec2'] = 'operation/agentes/group_view';
                break;

                case 'alert_detail':
                    $_GET['sec'] = 'view';
                    $_GET['sec2'] = 'operation/agentes/alerts_status';
                break;

                case 'tactical_view':
                    $_GET['sec'] = 'view';
                    $_GET['sec2'] = 'operation/agentes/tactical';
                break;

                case 'default':
                default:
                    $_GET['sec2'] = 'general/logon_ok';
                break;

                case 'dashboard':
                    $_GET['specialSec2'] = sprintf('operation/dashboard/dashboard&dashboardId=%s', $home_url);
                    $str = sprintf('sec=reporting&sec2=%s&d_from_main_page=1', $_GET['specialSec2']);
                    parse_str($str, $res);
                    foreach ($res as $key => $param) {
                        $_GET[$key] = $param;
                    }
                break;

                case 'visual_console':
                    $id_visualc = db_get_value('id', 'tlayout', 'name', $home_url);
                    if (($home_url == '') || ($id_visualc == false)) {
                        $str = 'sec=godmode/reporting/map_builder&sec2=godmode/reporting/map_builder';
                    } else {
                        $str = 'sec=network&sec2=operation/visual_console/render_view&id='.$id_visualc;
                    }

                    parse_str($str, $res);
                    foreach ($res as $key => $param) {
                        $_GET[$key] = $param;
                    }
                break;

                case 'other':
                    $home_url = io_safe_output($home_url);
                    $url_array = parse_url($home_url);
                    parse_str($url_array['query'], $res);
                    foreach ($res as $key => $param) {
                        $_GET[$key] = $param;
                    }
                break;

                case 'external_link':
                    $home_url = io_safe_output($home_url);
                    if (strlen($home_url) !== 0) {
                        echo '<script type="text/javascript">document.location="'.$home_url.'"</script>';
                    } else {
                        $_GET['sec2'] = 'general/logon_ok';
                    }
                break;
            }

            if (isset($_GET['sec2']) === true) {
                $file = $_GET['sec2'].'.php';
                // Make file path absolute to prevent accessing remote files.
                $file = __DIR__.'/'.$file;
                // Translate some secs.
                if (isset($_GET['sec']) === false) {
                    $_GET['sec'] = '';
                }

                $main_sec = get_sec($_GET['sec']);
                $_GET['sec'] = ($main_sec == false) ? $_GET['sec'] : $main_sec;

                // Third condition is aimed to prevent from traversal attack.
                if (file_exists($file) === false || strpos(realpath($file), __DIR__) === false) {
                    unset($_GET['sec2']);
                    include 'general/noaccess.php';
                } else {
                    include $file;
                }
            } else {
                include 'general/noaccess.php';
            }
        } else {
            include 'general/logon_ok.php';
        }
    }
}

if (__PAN_XHPROF__ === 1) {
    echo "<span style='font-size: 0.8em;'>";
    echo __('Page generated at').' ';
    echo date('D F d, Y H:i:s', $time).'</span>';
    echo ' - ( ';
    pandora_xhprof_display_result('node_index');
    echo ' )';
    echo '</center>';
}

if ($config['pure'] == 0) {
    // echo '<div id="both"></div>';
    echo '</div>';
    // Main.
    // echo '<div id="both">&nbsp;</div>';
    echo '</div>';
    // Page (id = page).
} else {
    echo '</div>';
    // Main pure.
}

echo html_print_div(
    ['id' => 'wiz_container'],
    true
);

echo html_print_div(
    ['id' => 'um_msg_receiver'],
    true
);

echo html_print_input_hidden(
    'flagEasternEgg',
    $config['eastern_eggs_disabled'],
    false,
    '',
    '',
    'flagEasternEgg'
);

// Connection lost alert.
set_js_value('check_conexion_interval', $config['check_conexion_interval']);
set_js_value('title_conexion_interval', __('Connection with console has been lost'));
set_js_value('status_conexion_interval', __('Connection status').': ');
ui_require_javascript_file('connection_check');
set_js_value('absolute_homeurl', ui_get_full_url(false, false, false, false));
$conn_title = __('Connection with console has been lost');
$conn_text = __('Connection to the console has been lost. Please check your internet connection.');
ui_print_message_dialog($conn_title, $conn_text, 'connection', '/images/fail@svg.svg');

if ($config['pure'] == 0) {
    echo '</div>';
    // Container div.
    echo '</div>';
    // echo '<div id="both"></div>';
    echo '</div>';
}

// Clippy function.
require_once 'include/functions_clippy.php';
clippy_start($sec2);

while (ob_get_length() > 0) {
    ob_end_flush();
}

// Results search header.
echo '<div id="result_order" class="result_order"></div>';

db_print_database_debug();
echo '</html>';

$run_time = format_numeric((microtime(true) - $config['start_time']), 3);
echo "\n<!-- Page generated in ".$run_time." seconds -->\n";

if (isset($_GET['logged']) === false) {
    $_GET['logged'] = '';
}

// Values from PHP to be recovered from JAVASCRIPT.
require 'include/php_to_js_values.php';
?>

<script type="text/javascript" language="javascript">
    // Handle the scroll.
    $(document).ready(scrollFunction());

    // When the user scrolls down 400px from the top of the document, show the
    // button.
    window.onscroll = function() {
        scrollFunction()
    };

    window.onresize = function() {
        scrollFunction()
    };

    function first_time_identification() {
        jQuery.post("ajax.php", {
                "page": "general/register",
                "load_wizards": 'initial'
            },
            function(data) {
                $('#wiz_container').empty()
                    .html(data);
                run_configuration_wizard();
            },
            "html"
        );

    }

    <?php if (empty($errorFileOutput) === false) : ?>
        // There are one issue with the file that you trying to catch. Show a dialog with message.
        $(document).ready(function() {
            confirmDialog({
                title: "<?php echo __('Error'); ?>",
                message: "<?php echo io_safe_output($errorFileOutput); ?>",
                hideCancelButton: true,
            });
        });
    <?php endif; ?>

    function show_modal(id) {
        var match = /notification-(.*)-id-([0-9]+)/.exec(id);
        if (!match) {
            console.error(
                "Cannot handle toast click event. Id not valid: ",
                event.target.id
            );
            return;
        }
        jQuery.post("ajax.php", {
                "page": "godmode/setup/setup_notifications",
                "get_notification": 1,
                "id": match[2]
            },
            function(data) {
                notifications_hide();
                try {
                    var json = JSON.parse(data);
                    $('#um_msg_receiver')
                        .empty()
                        .html(json.mensaje);

                    $('#um_msg_receiver').prop('title', json.subject);

                    // Launch modal.
                    $("#um_msg_receiver").dialog({
                        resizable: true,
                        draggable: true,
                        modal: true,
                        width: 800,
                        height: 600,
                        buttons: [{
                            text: "OK",
                            click: function() {
                                $(this).dialog("close");
                            }
                        }],
                        overlay: {
                            opacity: 0.5,
                            background: "black"
                        },
                        closeOnEscape: false,
                        open: function(event, ui) {
                            $(".ui-dialog-titlebar-close").hide();
                        }
                    });

                    $(".ui-widget-overlay").css("background", "#000");
                    $(".ui-widget-overlay").css("opacity", 0.6);
                    //$(".ui-draggable").css("cursor", "inherit");

                } catch (error) {
                    console.log(error);
                }

            },
            "html"
        );
    }

    // Info messages action.
    $(document).ready(function() {
        var $autocloseTime = <?php echo ((int) $config['notification_autoclose_time'] * 1000); ?>;
        var $listOfMessages = document.querySelectorAll('.info_box_autoclose');
        $listOfMessages.forEach(
            function(item) {
                autoclose_info_box(item.id, $autocloseTime)
            }
        );

        // Cog animations.
        $(".submitButton").click(function(){
            $("#"+this.id+" > .subIcon.cog").addClass("rotation");
        });

        // Easter egg think green.
        let counter = 0;
        $("#keywords").on("click", function(e) {
            counter++;
            let flagEasternEgg = $("#flagEasternEgg").val();
            if (counter == 5 && flagEasternEgg == true) {
                easterEggThinkGreen();
            }
        });
    
        <?php if ($_GET['logged'] === '1') { ?>
            $('#header_table_inner').find('.header_left').trigger('click');
        <?php } ?>
    });

    // Snow animations.
    $(document).ready(function() {
        const date = new Date();
        const today = date.toLocaleDateString();
        const year = date.getFullYear();
        const christmasDay = "25/12/"+year;
        let flagEasternEgg = $("#flagEasternEgg").val();
        if (today === christmasDay && flagEasternEgg == true) {
            $("#container-snow").removeClass('invisible');
            setTimeout(() => {
                $("#container-snow").addClass('invisible');
            }, 30000);
        }

    });

    function easterEggThinkGreen() {
        // Agent detail.
        $('#agent_list > tbody > tr').each(function(index, fila) {
            var divId = $(fila).find('td:eq(5)').attr('id');
            var hasClassRed = $("#"+divId).children('b').children().next().hasClass('red');
            var hasClassGrey = $("#"+divId).children('b').children().next().hasClass('grey');
            if (hasClassRed === true || hasClassGrey === true) {
                $("#"+divId).children('b').children().next().addClass('green');
            }

            var divStatus = $(fila).find('td:eq(6)').attr('id');
            $("#"+divStatus).find('div').attr('style', 'background: #82b92e;');
        });

        // Agent main view.
        const agentDetailsHeaderIcono = $('.agent_details_header > .icono_right').children('img');
        const statusImg = $(agentDetailsHeaderIcono).attr('src');
        if (statusImg !== undefined) {
            if (statusImg.indexOf('critical') >= 0 || statusImg.indexOf('unknown') >= 0) {
                $(agentDetailsHeaderIcono).attr('src', 'images/agent_ok.png');
            }
        }
        // Agent details bullets.
        const agentDetailsBullets = $('.agent_details_bullets > #bullets_modules').children('div').children('div').attr('id');
        var hasClassRed = $("#"+agentDetailsBullets).hasClass('red_background');
        var hasClassGrey = $("#"+agentDetailsBullets).hasClass('grey_background');
        const elementChange = {
            'hasClassRed': hasClassRed,
            'hasClassGrey': hasClassGrey,
            'elementID' : agentDetailsBullets,
            'class' : true
        }
        setClassGreen(elementChange);
        // Header list of modules
        const headerList = $('.white_table_graph_header').children('span').children('div');
        var hasClassRed = $(headerList[5]).children('div').children('div').hasClass('red_background');
        var hasClassGrey = $(headerList[5]).children('div').children('div').hasClass('grey_background');
        elementChange.hasClassRed = hasClassRed;
        elementChange.hasClassGrey = hasClassGrey;
        elementChange.elementID = $(headerList[5]).children('div').children('div').attr('id');
        elementChange.class = true;
        setClassGreen(elementChange);

        // List of modules table.
        $('#table1 > tbody > tr').each(function(index, fila) {
            var divId = $(fila).find('td:eq(4)').attr('id');
            var divStyle = $("#"+divId).children('div').attr('style');
            if (divStyle !== undefined) {
                let findedRed = divStyle.indexOf('background: #e63c52;')
                let findedGrey = divStyle.indexOf('background: #B2B2B2;')
                if (findedRed >= 0 || findedGrey >= 0) {
                    elementChange.hasClassRed = true;
                    elementChange.hasClassGrey = true;
                    elementChange.elementID = $("#"+divId).children('div');
                    elementChange.class = false;
                    setClassGreen(elementChange);
                }
            }
        });

        // latest events table.
        $('#latest_events_table > tbody > tr').each(function(index, fila) {
            // Change status.
            var divId = $(fila).find('td:eq(3)').attr('id');
            var divStyle = $("#"+divId).children('div').attr('style');
            if (divStyle !== undefined) {
                let findedRed = divStyle.indexOf('background: #e63c52');
                let findedGrey = divStyle.indexOf('background: #B2B2B2');
                if (findedRed >= 0 || findedGrey >= 0) {
                    elementChange.hasClassRed = true;
                    elementChange.hasClassGrey = true;
                    elementChange.elementID = $("#"+divId).children('div');
                    elementChange.class = false;
                    setClassGreen(elementChange);
                    // Change Image
                    const tdId = $(fila).find('td:eq(0)').attr('id');
                    var img = $("#"+tdId).children('img');
                    $(img).attr('src', 'images/module_ok.png');
                }
            }
        });

        // Group view.
        //Summary status groups
        $('#summary_status_groups > tbody > tr > td > span').each(function(index, fila) {
            var hasClassRed = $(fila).hasClass('red_background');
            if (hasClassRed) {
                $(fila).removeClass('red_background').addClass('green_background');
            }
        });

        $('#summary_status_groups_detail > tbody > tr >  td').each(function(index, fila) {
            var hasClassRed = $(fila).hasClass('group_view_crit');
            if (hasClassRed) {
                $(fila).removeClass('group_view_crit').addClass('group_view_ok');
                $(fila).children('a').removeClass('group_view_crit').addClass('group_view_ok');
            }
        });

        // Monitor detail.
        // Monitors view table
        $('#monitors_view > tbody > tr').each(function(index, fila) {
            // Change status.
            var divId = $(fila).find('td:eq(6)').attr('id');
            var divStyle = $("#"+divId).children('div').children('div').attr('style');
            if (divStyle !== undefined) {
                let findedRed = divStyle.indexOf('background: #e63c52');
                let findedGrey = divStyle.indexOf('background: #B2B2B2');
                if (findedRed >= 0 || findedGrey >= 0) {
                    elementChange.hasClassRed = true;
                    elementChange.hasClassGrey = true;
                    elementChange.elementID = $("#"+divId).children('div').children('div');
                    elementChange.class = false;
                    setClassGreen(elementChange);
                }
            }
        });

        // Agents Modules.
        $('#agents_modules_table > tbody > tr > td').each(function(index, fila) {
            // Change status.
            var hasClassRed = $(fila).hasClass('group_view_crit');
            var hasClassGrey = $(fila).hasClass('group_view_unk');
            if (hasClassRed == true) {
                $(fila).removeClass('group_view_crit').addClass('group_view_ok');
                $(fila).children('a').removeClass('group_view_crit').addClass('group_view_ok');
            } else if (hasClassRed == false) {
                var hasClassGroupRed = $(fila).children('a').children('div').attr('style');
                if (hasClassGroupRed !== undefined) {
                    let findedRed = hasClassGroupRed.indexOf('background: #e63c52');
                    if (findedRed >= 0) {
                        elementChange.hasClassRed = true;
                        elementChange.hasClassGrey = false;
                        elementChange.elementID = $(fila).children('a').children('div');
                        elementChange.class = false;
                        setClassGreen(elementChange);
                    }
                }
            }

            if (hasClassGrey == true) {
                $(fila).removeClass('group_view_unk').addClass('group_view_ok');
                $(fila).children('a').removeClass('group_view_unk').addClass('group_view_ok');
            } else if (hasClassGrey == false) {
                var hasClassGroupGrey = $(fila).children('a').children('div').attr('style');
                if (hasClassGroupGrey !== undefined) {
                    let findedGrey = hasClassGroupGrey.indexOf('background: #B2B2B2');
                    if (findedGrey >= 0) {
                        elementChange.hasClassRed = false;
                        elementChange.hasClassGrey = true;
                        elementChange.elementID = $(fila).children('a').children('div');
                        elementChange.class = false;
                        setClassGreen(elementChange);
                    }
                }
            }
        });

        // Combined table of agent group and module group.
        $('#agent_group_module_group > tbody > tr > td').each(function(index, fila) {
            var hasClassGroupRed = $(fila).children('div').attr('style');
            if (hasClassGroupRed !== undefined) {
                let findedRed = hasClassGroupRed.indexOf('    background:#e63c52;');
               if (findedRed >= 0) {
                    elementChange.hasClassRed = true;
                    elementChange.hasClassGrey = false;
                    elementChange.elementID = $(fila).children('div');
                    elementChange.class = false;
                    setClassGreen(elementChange);
                }
            }

            var hasClassGroupGrey = $(fila).children('div').attr('style');
            if (hasClassGroupGrey !== undefined) {
                let findedGrey = hasClassGroupGrey.indexOf('    background:#B2B2B2;');
               if (findedGrey >= 0) {
                    elementChange.hasClassRed = false;
                    elementChange.hasClassGrey = true;
                    elementChange.elementID = $(fila).children('div');
                    elementChange.class = false;
                    setClassGreen(elementChange);
                }
            }
        });

    }

    function setClassGreen(element) {
        // Class.
        if ((element.hasClassRed === true || element.hasClassGrey === true) && element.class == true) {
            $("#"+element.elementID).addClass('green_background');
        }
        // Element style.
        if ((element.hasClassRed === true || element.hasClassGrey === true) && element.class == false) {
            element.elementID.css('background', '#82b92e');
        }
    }
</script>
