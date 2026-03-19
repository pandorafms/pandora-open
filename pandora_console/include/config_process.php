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
 * @subpackage Config
 */

use DI\ContainerBuilder;

/*
 * Pandora build version and version
 */
$build_version = 'PC260301'; 
$pandora_version = '1.0.0';

// Do not overwrite default timezone set if defined.
$script_tz = @date_default_timezone_get();
if (empty($script_tz)) {
    date_default_timezone_set('Europe/Berlin');
    ini_set('date.timezone', 'Europe/Berlin');
} else {
    ini_set('date.timezone', $script_tz);
}

// home dir bad defined
if (!is_dir($config['homedir'])) {
    $ownDir = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
    $config['homedir'] = $ownDir;
    $config['error'] = 'homedir_bad_defined';
}



// Help to debug problems. Override global PHP configuration
global $develop_bypass;
if ((int) $develop_bypass === 1) {
    // Develop mode, show all notices and errors on Console (and log it)
    if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
        error_reporting(E_ALL & ~E_DEPRECATED);
    } else {
        error_reporting(E_ALL);
    }

    ini_set('display_errors', 1);
} else {
    // Leave user decide error_level, but limit errors to be displayed only in
    // logs.
    ini_set('display_errors', 0);
}

// Check if mysqli is available
if (!(isset($config['mysqli']))) {
    $config['mysqli'] = extension_loaded('mysqli');
}

$config['start_time'] = microtime(true);

$ownDir = dirname(__FILE__).'/';
$ownDir = str_replace('\\', '/', $ownDir);

// Set by default the MySQL connection for DB, because in older Pandora have not
// this token in the config.php
if (!isset($config['dbtype'])) {
    $config['dbtype'] = 'mysql';
}

if (!isset($config['dbport'])) {
    switch ($config['dbtype']) {
        case 'mysql':
            $config['dbport'] = '3306';
        break;

        case 'postgresql':
            $config['dbport'] = '5432';
        break;

        case 'oracle':
            $config['dbport'] = '1521';
        break;
    }
}

require_once $ownDir.'constants.php';
require_once $ownDir.'functions_db.php';
require_once $ownDir.'functions.php';
require_once $ownDir.'functions_io.php';


// We need a timezone BEFORE calling config_process_config.
// If not we will get ugly warnings. Set Europe/Madrid by default
// Later will be replaced by the good one.
if (!is_dir($config['homedir'])) {
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
    $config['error'] = 'homeurl_bad_defined';
    return;
}


if (!isset($config['homeurl_static'])) {
    $config['homeurl_static'] = $config['homeurl'];
} else {
    if ($config['homeurl_static'] != $config['homeurl']) {
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
        $config['error'] = 'homeurl_bad_defined';
        return;
    }
}



if (! defined('EXTENSIONS_DIR')) {
    define('EXTENSIONS_DIR', 'extensions');
}

db_select_engine();

if (empty($config['remote_config']) === false
    && file_exists($config['remote_config'].'/conf/'.PANDORA_HA_FILE)
    && filesize($config['remote_config'].'/conf/'.PANDORA_HA_FILE) > 0
) {
    $data = file_get_contents($config['remote_config'].'/conf/'.PANDORA_HA_FILE);
    if (empty($data) === false) {
        $ip_list = explode(',', $data);
        // Connects to the first pandora_ha_dbs.conf database.
        $config['dbhost'] = trim($ip_list[0]);
    }
}

$config['dbconnection'] = db_connect();

require_once $ownDir.'functions_config.php';

date_default_timezone_set('Europe/Madrid');

//
// PLEASE DO NOT CHANGE ORDER //////
//
require_once $config['homedir'].'/include/load_session.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

config_process_config();
config_prepare_session();
config_prepare_jwt_signature();

if ((bool) $config['console_log_enabled'] === true) {
    error_reporting(E_ALL ^ E_NOTICE);
}

// Set a the system timezone default
if ((!isset($config['timezone'])) or ($config['timezone'] == '')) {
    $config['timezone'] = 'Europe/Berlin';
}

date_default_timezone_set($config['timezone']);

require_once $ownDir.'streams.php';
require_once $ownDir.'gettext.php';

if (isset($_SERVER['REMOTE_ADDR'])) {
    $config['remote_addr'] = $_SERVER['REMOTE_ADDR'];
} else {
    $config['remote_addr'] = null;
}

// Save the global values
$config['global_block_size'] = $config['block_size'];

if (isset($config['id_user'])) {
    config_user_set_custom_config();
}

// Check if inventory_changes_blacklist is setted, if not create it
if (!isset($config['inventory_changes_blacklist'])) {
    $config['inventory_changes_blacklist'] = [];
}

// NEW UPDATE MANAGER URL
if (!isset($config['url_update_manager'])) {
    config_update_value(
        'url_update_manager',
        'https://licensing.pandoraopen.io/'
    );
}

set_user_language();

require_once $ownDir.'functions_extensions.php';

$config['extensions'] = extensions_get_extensions();

// Function include_graphs_dependencies() it's called in the code below
require_once 'include_graph_dependencies.php';

include_graphs_dependencies($config['homedir'].'/');

// Updates autorefresh time
if (isset($_POST['vc_refr'])) {
    config_update_value('vc_refr', get_parameter('vc_refr', $config['vc_refr']));
}


// ======= Autorefresh code =============================================
if (isset($config['id_user'])) {
    $select = db_process_sql("SELECT autorefresh_white_list FROM tusuario WHERE id_user = '".$config['id_user']."'");
    if (isset($select[0]['value'])) {
        $autorefresh_list = json_decode($select[0]['value']);
    } else {
        $autorefresh_list = null;
    }

    $config['autorefresh_white_list'] = [];
    $config['autorefresh_white_list'] = $autorefresh_list;
} else {
    $config['autorefresh_white_list'] = null;
}

// ======================================================================
// ======================================================================
// Update the $config['homeurl'] with the full url with the special
// cases (reverse proxy, others ports...).
// ======================================================================
$config['homeurl'] = ui_get_full_url(false);


// ======================================================================
// Get the version of DB manager
// ======================================================================
switch ($config['dbtype']) {
    case 'mysql':
        if (!isset($config['quote_string'])) {
            $config['db_quote_string'] = '"';
        }
    break;

    case 'postgresql':
        if (!isset($config['dbversion'])) {
            $result = db_get_sql('select version();');
            $result_chunks = explode(' ', $result);

            $config['dbversion'] = $result_chunks[1];
        }

        if (!isset($config['quote_string'])) {
            $config['db_quote_string'] = "'";
        }
    break;

    case 'oracle':
        if (!isset($config['quote_string'])) {
            $config['db_quote_string'] = "'";
        }
    break;
}

// ======================================================================
// Menu display mode.
if (isset($_SESSION['meny_type']) === true && empty($_SESSION['menu_type']) === false) {
    $config['menu_type'] = $_SESSION['menu_type'];
} else {
    $config['menu_type'] = 'classic';
}


// Log.
if (isset($config['console_log_enabled']) === true
    && $config['console_log_enabled'] == 1
) {
    ini_set('log_errors', true);
    ini_set('error_log', $config['homedir'].'/log/console.log');
} else {
    ini_set('log_errors', false);
    ini_set('error_log', '');
}

global $container;
if (empty($container) === true) {
    include_once $config['homedir'].'/vendor/autoload.php';

    // Solution to load the ContainerBuilder class.
    $containerBuilder = new ContainerBuilder();
    $containerBuilder->addDefinitions(__DIR__.'/../api/v2/config/container.php');

    // Create DI container instance.
    $container = $containerBuilder->build();
}
