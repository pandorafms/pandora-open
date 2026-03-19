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

use PandoraFMS\Websockets\WSManager;

/*
 * ============================================================================
 * * GOTTY PROTOCOL: PROXY
 * ============================================================================
 */


/**
 * Connects to internal socket.
 *
 * @param WSManager $ws_object Main WebSocket manager object.
 * @param array     $headers   Communication headers.
 * @param string    $to_addr   Target address (internal).
 * @param integer   $to_port   Target port (internal).
 * @param string    $to_url    Target url (internal).
 *
 * @return socket Active socket or null.
 */
function connectInt(
    WSManager $ws_object,
    array $headers,
    string $to_addr,
    int $to_port,
    string $to_url
) {
    $intSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    // Not sure.
    $connect = socket_connect(
        $intSocket,
        $to_addr,
        $to_port
    );

    if ($connect === false) {
        $ws_object->stderr(socket_last_error($intSocket));
        return null;
    }

    $c_str = 'GET '.$to_url." HTTP/1.1\r\n";
    $c_str .= 'Host: '.$to_addr."\r\n";
    $c_str .= "Upgrade: websocket\r\n";
    $c_str .= "Connection: Upgrade\r\n";
    $c_str .= 'Origin: http://'.$to_addr."\r\n";
    $c_str .= 'Sec-WebSocket-Key: '.$headers['Sec-WebSocket-Key']."\r\n";
    $c_str .= 'Sec-WebSocket-Version: '.$headers['Sec-WebSocket-Version']."\r\n";
    if (isset($headers['Sec-WebSocket-Protocol']) === true) {
        $c_str .= 'Sec-WebSocket-Protocol: '.$headers['Sec-WebSocket-Protocol']."\r\n";
    }

    $c_str .= "\r\n";

    // Send.
    // Register user - internal.
    $intUser = new $ws_object->userClass('INTERNAL-'.uniqid('u'), $intSocket);

    $intUser->headers = [
        'get'                    => $to_url.' HTTP/1.1',
        'host'                   => $to_addr,
        'origin'                 => $to_addr,
        'sec-websocket-protocol' => 'gotty',
    ];

    $ws_object->writeSocket($intUser, $c_str);

    return $intUser;
}


/**
 * Process a connected step on proxied protocols.
 *
 * @param WSManager $ws_object Main WebSocket manager object.
 * @param User      $user      WebSocketUser.
 *
 * @return void
 */
function proxyConnected(
    $ws_object,
    $user
) {
    global $config;

    /*
     * $user->redirect is connected to internal (reflexive).
     * $user->socket is connected to external.
     */

    $failed = false;

    // Gotty. Based on the command selected, redirect to a target port.
    if ($user->requestedResource === '/ssh') {
        $port = $config['gotty_ssh_port'];
    } else if ($user->requestedResource === '/telnet') {
        $port = $config['gotty_telnet_port'];
    } else {
        $failed = true;
    }

    if ($failed === true
        || isset($config['gotty_host']) === false
        || isset($port) === false
    ) {
        $ws_object->disconnect($user->socket);
        return;
    }

    // Switch between ports...
    // Create a new socket connection (internal).
    $intUser = connectInt(
        $ws_object,
        $ws_object->rawHeaders,
        $config['gotty_host'],
        $port,
        '/ws'
    );

    if ($intUser === null) {
        $ws_object->disconnect($user->socket);
        return;
    }

    // Map user.
    $user->intUser = $intUser;
    // And socket.
    $user->intSocket = $intUser->socket;
    $user->redirect = $intUser;
    $intUser->redirect = $user;

    // Keep an eye on changes.
    $ws_object->remoteSockets[$intUser->id] = $intUser->socket;
    $ws_object->remoteUsers[$intUser->id] = $intUser;

    // Ignore. Cleanup socket.
    $ws_object->readSocket($user->intUser);
}


/**
 * Redirects input from user to redirection stabished.
 *
 * @param WSManager     $ws_object Main WebSocket manager object.
 * @param WebSocketUser $user      WebSocket user.
 * @param string        $buffer    Buffer.
 *
 * @return boolean Ok or not.
 */
function proxyProcessRaw($ws_object, $user, $buffer)
{
    if (isset($user->redirect) !== true) {
        $ws_object->disconnect($user->socket);
        return false;
    }

    $ws_object->stderr($user->id.' >> '.$user->redirect->id);
    $ws_object->stderr($ws_object->dump($buffer));
    $ws_object->writeSocket($user->redirect, $buffer);

    return true;
}


/*
 * ============================================================================
 * * ENDS: GOTTY PROTOCOL: PROXY
 * ============================================================================
 */
