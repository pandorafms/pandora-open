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

if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

$develop_bypass = 0;

require_once 'include/ui.class.php';
require_once 'include/system.class.php';
require_once 'include/db.class.php';
require_once 'include/user.class.php';

/*
    Info:
 * The classes above doesn't start any session before it's properly
 * configured into the file below, but it's important the classes
 * exist at the time the session is started for things like
 * serializing objects stored into the session.
 */
require_once '../include/config.php';
require_once '../include/class/JWTRepository.class.php';

require_once 'operation/home.php';
require_once 'operation/tactical.php';
require_once 'operation/groups.php';
require_once 'operation/events.php';
require_once 'operation/alerts.php';
require_once 'operation/agents.php';
require_once 'operation/modules.php';
require_once 'operation/module_graph.php';
require_once 'operation/agent.php';
require_once 'operation/visualmaps.php';
require_once 'operation/visualmap.php';
require_once 'operation/server_status.php';
require_once 'operation/module_data.php';

$is_mobile = true;

if (!empty($config['https']) && empty($_SERVER['HTTPS'])) {
    $query = '';
    if (count($_REQUEST)) {
        // Some (old) browsers don't like the ?&key=var
        $query .= 'mobile/index.php?1=1';
    }

    // We don't clean these variables up as they're only being passed along
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
    // http://en.wikipedia.org/wiki/HTTP_response_splitting
    $url = str_replace("\n", '', $url);
    header('Location: '.$url);
    exit;
    // Always exit after sending location headers
}

$system = System::getInstance();

require_once $system->getConfig('homedir').'/include/constants.php';

$user = User::getInstance();
$user->saveLogin();

$default_page = 'home';
$page = $system->getRequest('page');
$action = $system->getRequest('action');

// The logout action has priority
if ($action != 'logout') {
    if (!$user->isLogged()) {
        $action = 'login';
    } else if ($user->isWaitingDoubleAuth()) {
        $dauth_period = SECONDS_2MINUTES;
        $now = time();
        $dauth_time = $user->getLoginTime();

        if (($now - $dauth_period) < $dauth_time) {
            $action = 'double_auth';
        }
        // Expired login
        else {
            $action = 'logout';
        }
    }
}

if ($action != 'ajax') {
    $user_language = get_user_language($system->getConfig('id_user'));
    if (file_exists('../include/languages/'.$user_language.'.mo')) {
        $l10n = new gettext_reader(new CachedFileReader('../include/languages/'.$user_language.'.mo'));
        $l10n->load_tables();
    }
}

switch ($action) {
    case 'ajax':
        $parameter1 = $system->getRequest('parameter1', false);
        $parameter2 = $system->getRequest('parameter2', false);

        switch ($parameter1) {
            case 'events':
                $events = new Events();
                $events->ajax($parameter2);
            break;

            case 'agents':
                $agents = new Agents();
                $agents->ajax($parameter2);
            break;

            case 'agent':
                $agent = new Agent();
                $agent->ajax($parameter2);
            break;

            case 'modules':
                $modules = new Modules();
                $modules->ajax($parameter2);
            break;

            case 'module_graph':
                $module_graph = new ModuleGraph();
                $module_graph->ajax($parameter2);
            break;

            case 'visualmap':
                $visualmap = new Visualmap();
                $visualmap->ajax($parameter2);
            break;

            case 'tactical':
                $tactical = new Tactical();
                $tactical->ajax($parameter2);
            break;

            case 'server_status':
                $server_status = new ServerStatus();
                $server_status->ajax($parameter2);
            break;

            case 'services':
                $services = new Services();
                $services->ajax($parameter2);
            break;

            case 'module_data':
                $module_data = new ModuleData();
                $module_data->ajax($parameter2);
            break;

            default:
            break;
        }
    return;

    case 'login':
        if ($user->login() && $user->isLogged()) {

            if ($user->isWaitingDoubleAuth()) {
                if ($user->validateDoubleAuthCode()) {
                    $url = ui_get_full_url('');
                    $url = str_replace("\n", '', $url);
                    $url = str_replace('?action=logout', '', $url);

                    // Logged. Refresh the page.
                    header('Location: '.$url);
                    return;
                } else {
                    $user->showDoubleAuthPage();
                }
            } else {
                $url = ui_get_full_url('');
                $url = str_replace("\n", '', $url);
                $url = str_replace('?action=logout', '', $url);

                // Logged. Refresh the page.
                header('Location: '.$url);
                return;
            }
        } else {
            $user->showLoginPage();
        }
    break;

    case 'double_auth':
        if ($user->isLogged()) {
            if ($user->validateDoubleAuthCode()) {
                $user_language = get_user_language($system->getConfig('id_user'));
                if (file_exists('../include/languages/'.$user_language.'.mo')) {
                    $l10n = new gettext_reader(new CachedFileReader('../include/languages/'.$user_language.'.mo'));
                    $l10n->load_tables();
                }

                if ($_GET['page'] != '') {
                    header('refresh:0; url=http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
                }

                $home = new Home();

                $home->show();
            } else {
                $user->showDoubleAuthPage();
            }
        } else {
            $user->showLoginPage();
        }
    break;

    case 'logout':
        $user->logout();
        $user->showLoginPage();
    break;

    default:
        if (empty($page)) {
            $user_info = $user->getInfo();
            $home_page = $system->safeOutput($user_info['section']);
            $section_data = $user_info['data_section'];

            switch ($home_page) {
                case 'Event list':
                    $page = 'events';
                break;

                case 'Alert detail':
                    $page = 'alerts';
                break;

                case 'Tactical view':
                    $page = 'tactical';
                break;

                case 'Visual console':
                    $page = 'visualmap';
                    $id_map = (int) db_get_value('id', 'tlayout', 'name', $section_data);
                    $_GET['id'] = $id_map;
                break;

                case 'External link':
                    $full_url = ui_get_full_url();
                    $section_data = io_safe_output($section_data);

                    $host_full = parse_url($full_url, PHP_URL_HOST);
                    $host_section = parse_url($section_data, PHP_URL_HOST);

                    if ($host_full !== $host_section) {
                        $has_mobile = strpos($section_data, 'mobile');
                        if ($has_mobile === false) {
                            $pos = strpos($section_data, '/index');
                            if ($pos !== false) {
                                $section_data = substr_replace($section_data, '/mobile', $pos, 0);
                            }
                        }

                        echo '<script type="text/javascript">document.location="'.$section_data.'"</script>';
                    } else {
                        if (strpos($full_url, 'event') !== false) {
                            $page = 'events';
                        }

                        if (strpos($full_url, 'alert') !== false) {
                            $page = 'alerts';
                        }

                        if (strpos($full_url, 'tactical') !== false) {
                            $page = 'tactical';
                        }

                        if (strpos($full_url, 'visual_console') !== false) {
                            $page = 'visualmap';
                        }
                    }
                break;

                case 'Group view':
                default:
                    // No content.
                break;
            }
        }

        switch ($page) {
            case 'home':
            default:
                $home = new Home();

                $home->show();
            break;

            case 'tactical':
                $tactical = new Tactical();
                $tactical->show();
            break;

            case 'groups':
                $groups = new Groups();
                $groups->show();
            break;

            case 'events':
                $events = new Events();
                $events->show();
            break;

            case 'alerts':
                $alerts = new Alerts();
                $alerts->show();
            break;

            case 'agents':
                $agents = new Agents();
                $agents->show();
            break;

            case 'modules':
                $modules = new Modules();
                $modules->show();
            break;

            case 'module_graph':
                $module_graph = new ModuleGraph();
                $module_graph->show();
            break;

            case 'agent':
                $agent = new Agent();
                $agent->show();
            break;

            case 'visualmaps':
                // Show a list of VC.
                $vc_list = new Visualmaps();
                $vc_list->show();
            break;

            case 'visualmap':
                $vc = new Visualmap();
                $vc->show();
            break;

            case 'server_status':
                $server_status = new ServerStatus();
                $server_status->show();
            break;

            case 'services':
                $services = new Services();
                $services->show();
            break;

            case 'module_data':
                $module_data = new ModuleData();
                $module_data->show();
            break;
        }
    break;
}
