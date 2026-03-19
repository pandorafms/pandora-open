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
require_once __DIR__.'/vendor/autoload.php';
use \PandoraFMS\Websockets\WSManager;

// Set to true to get full output.
$debug = false;

// 1MB.
$bufferSize = 1048576;

if (file_exists(__DIR__.'/include/config.php') === false
    || is_readable(__DIR__.'/include/config.php') === false
) {
    echo "Main console configuration file not found.\n";
    exit;
}

// Simulate.
$_SERVER['DOCUMENT_ROOT'] = __DIR__.'/../';

// Don't start a session before this import.
// The session is configured and started inside the config process.
require_once __DIR__.'/include/config.php';
require_once __DIR__.'/include/functions.php';
require_once __DIR__.'/include/functions_db.php';
require_once __DIR__.'/include/auth/mysql.php';
require_once __DIR__.'/include/websocket_registrations.php';

// Avoid direct access through browsers.
if (isset($_SERVER['REMOTE_ADDR']) === true) {
    // Force redirection.
    header('Location: '.ui_get_full_url('index.php'));
    exit;
}


if (isset($config['ws_port']) === false) {
    config_update_value('ws_port', 8080);
}

if (isset($config['ws_bind_address']) === false) {
    config_update_value('ws_bind_address', '0.0.0.0');
}

if (isset($config['gotty_host']) === false) {
    config_update_value('gotty_host', '127.0.0.1');
}

if (isset($config['gotty_telnet_port']) === false) {
    config_update_value('gotty_telnet_port', 8082);
}

if (isset($config['gotty_ssh_port']) === false) {
    config_update_value('gotty_ssh_port', 8081);
}

if (isset($config['gotty']) === false) {
    config_update_value('gotty', '/usr/bin/gotty');
}

$os = strtolower(PHP_OS);
if (substr($os, 0, 3) !== 'win') {
    if (empty($config['gotty']) === false) {
        // Allow start without gotty binary. External service.
        if (is_executable($config['gotty']) === false) {
            echo 'Failed to execute gotty ['.$config['gotty']."]\n";
            exit(1);
        }

        $gotty_creds = '';
        if (empty($config['gotty_user']) === false
            && empty($config['gotty_pass']) === false
        ) {
            $gotty_pass = io_output_password($config['gotty_pass']);
            $gotty_creds = " -c '".$config['gotty_user'].':'.$gotty_pass."'";
        }

        // Kill previous gotty running.
        $clean_cmd = 'ps aux | grep "'.$config['gotty'].'"';
        $clean_cmd .= '| grep -v grep | awk \'{print $2}\'';
        $clean_cmd .= '| xargs kill -9 ';
        shell_exec($clean_cmd);

        // Common.
        $base_cmd = 'nohup "'.$config['gotty'].'" '.$gotty_creds;
        $base_cmd .= ' --permit-arguments -a '.$config['gotty_host'].' -w ';

        // Launch gotty - SSH.
        $cmd = $base_cmd.' --port '.$config['gotty_ssh_port'];
        $cmd .= ' ssh >> /var/log/pandora/web_socket.log 2>&1 &';
        shell_exec($cmd);

        // Launch gotty - telnet.
        $cmd = $base_cmd.' --port '.$config['gotty_telnet_port'];
        $cmd .= ' telnet >> /var/log/pandora/web_socket.log 2>&1 &';
        shell_exec($cmd);
    }
}

// Start Web SocketProxy.
$ws = new WSManager(
    // Bind address.
    $config['ws_bind_address'],
    // Bind port.
    (int) $config['ws_port'],
    // Connected handlers.
    ['gotty' => 'proxyConnected'],
    // Process handlers.
    [],
    // ProcessRaw handlers.
    ['gotty' => 'proxyProcessRaw'],
    // Tick handlers.
    [],
    $bufferSize,
    $debug
);

try {
    $ws->run();
} catch (Exception $e) {
    $ws->stdout($e->getMessage());
}
