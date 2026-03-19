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

// Begin.
namespace PandoraFMS\Websockets;

use \PandoraFMS\Websockets\WebSocketServer;
use \PandoraFMS\User;

require_once __DIR__.'/../../functions.php';

/**
 * Redirects ws communication between two endpoints.
 */
class WSManager extends WebSocketServer
{

    /**
     * 1MB... overkill for an echo server, but potentially plausible for other
     * applications.
     *
     * @var integer
     */
    public $maxBufferSize = 1048576;

    /**
     * Interactive mode.
     *
     * @var boolean
     */
    public $interative = true;

    /**
     * Use a timeout of 100 milliseconds to search for messages..
     *
     * @var integer
     */
    public $timeout = 250;

    /**
     * Handlers for connected step:
     *   'protocol' => 'function';
     *
     * @var array
     */
    public $handlerConnected = [];

    /**
     * Handlers for process step:
     *   'protocol' => 'function';
     *
     * @var array
     */
    public $handlerProcess = [];

    /**
     * Handlers for processRaw step:
     *   'protocol' => 'function';
     *
     * @var array
     */
    public $handlerProcessRaw = [];

    /**
     * Handlers for tick step:
     *   'protocol' => 'function';
     *
     * @var array
     */
    public $handlerTick = [];

    /**
     * Allow only one connection per user session.
     *
     * @var boolean
     */
    public $socketPerSession = false;


    /**
     * Builder.
     *
     * @param string  $listen_addr  Target address (external).
     * @param integer $listen_port  Target port (external).
     * @param array   $connected    Handlers for <connected> step.
     * @param array   $process      Handlers for <process> step.
     * @param array   $processRaw   Handlers for <processRaw> step.
     * @param array   $tick         Handlers for <tick> step.
     * @param integer $bufferLength Max buffer size.
     * @param boolean $debug        Enable traces.
     */
    public function __construct(
        $listen_addr,
        int $listen_port,
        $connected=[],
        $process=[],
        $processRaw=[],
        $tick=[],
        $bufferLength=1048576,
        $debug=false
    ) {
        $this->maxBufferSize = $bufferLength;
        $this->debug = $debug;

        // Configure handlers.
        $this->handlerConnected = $connected;
        $this->handlerProcess = $process;
        $this->handlerProcessRaw = $processRaw;
        $this->handlerTick = $tick;

        $this->userClass = '\\PandoraFMS\\Websockets\\WebSocketUser';
        parent::__construct($listen_addr, $listen_port, $bufferLength);
    }


    /**
     * Call a target handler function.
     *
     * @param User  $user      User.
     * @param array $handler   Internal handler.
     * @param array $arguments Arguments for handler function.
     *
     * @return mixed handler return or null.
     */
    public function callHandler($user, $handler, $arguments)
    {
        if (isset($user->headers['sec-websocket-protocol'])) {
            $proto = $user->headers['sec-websocket-protocol'];
            if (isset($handler[$proto])
                && function_exists($handler[$proto])
            ) {
                // Launch configured handler.
                $this->stderr('Calling '.$handler[$proto]);
                return call_user_func_array(
                    $handler[$proto],
                    array_values(($arguments ?? []))
                );
            }
        }

        return null;
    }


    /**
     * Read from user's socket.
     *
     * @param object  $user  Target user connection.
     * @param integer $flags Socket receive flags:
     *           Flag            Description
     *           MSG_OOB         Process out-of-band data.
     *           MSG_PEEK        Receive data from the beginning of the receive
     *                           queue without removing it from the queue.
     *           MSG_WAITALL     Block until at least len are received. However,
     *                           if a signal is caught or the remote host
     *                           disconnects, the function may return less data.
     *           MSG_DONTWAIT    With this flag set, the function returns even
     *                           if it would normally have blocked.
     *
     * @return string Buffer.
     */
    public function readSocket($user, $flags=0)
    {
        $buffer = '';

        $numBytes = socket_recv(
            $user->socket,
            $buffer,
            $this->maxBufferSize,
            $flags
        );
        if ($numBytes === false) {
            // Failed. Disconnect.
            $this->handleSocketError($user->socket);
            return false;
        } else if ($numBytes == 0) {
            $this->disconnect($user->socket);
            $this->stderr(
                'Client disconnected. TCP connection lost: '.$user->id
            );
            return false;
        }

        $user->lastRawPacket = $buffer;
        return $buffer;
    }


    /**
     * Write to socket.
     *
     * @param object $user    Target user connection.
     * @param string $message Target message to be sent.
     *
     * @return void
     */
    public function writeSocket($user, $message)
    {
        if (is_resource($user->socket) === true
            || ($user->socket instanceof \Socket) === true
        ) {
            if (socket_write($user->socket, $message) === false) {
                $this->disconnect($user->socket);
            }
        } else {
            // Failed. Disconnect all.
            if (isset($user) === true) {
                $this->disconnect($user->socket);
            }

            if (isset($user->redirect) === true) {
                $this->disconnect($user->redirect->socket);
            }
        }
    }


    /**
     * User already connected.
     *
     * @param object $user User.
     *
     * @return void
     */
    public function connected($user)
    {
        global $config;

        $match = [];
        $php_session_id = '';
        \preg_match(
            '/PHPSESSID=(.*)/',
            $user->headers['cookie'],
            $match
        );

        if (is_array($match) === true) {
                $php_session_id = $match[1];
        }

        $php_session_id = \preg_replace('/;.*$/', '', $php_session_id);

        // If being redirected from proxy.
        if (isset($user->headers['x-forwarded-for']) === true) {
            $user->address = $user->headers['x-forwarded-for'];
        }

        $user->account = User::auth(['phpsessionid' => $php_session_id]);
        $_SERVER['REMOTE_ADDR'] = $user->address;

        // Ensure user is allowed to connect.
        if (\check_login(false) === false) {
            $this->disconnect($user->socket);
            \db_pandora_audit(
                AUDIT_LOG_WEB_SOCKETS,
                'Trying to access websockets engine without a valid session',
                'N/A'
            );
            return;
        }

        // User exists, and session is valid.
        \db_pandora_audit(
            AUDIT_LOG_WEB_SOCKETS,
            'WebSocket connection started',
            'N/A'
        );
        $this->stderr('ONLINE '.$user->address.'('.$user->account->idUser.')');

        if ($this->socketPerSession === true) {
            // Disconnect previous sessions.
            $this->cleanupSocketByCookie($user);
        }

        // Launch registered handler.
        $this->callHandler(
            $user,
            $this->handlerConnected,
            [
                $this,
                $user,
            ]
        );
    }


    /**
     * Protocol.
     *
     * @param string $protocol Protocol.
     *
     * @return string
     */
    public function processProtocol($protocol): string
    {
        return 'Sec-Websocket-Protocol: '.$protocol."\r\n";
    }


    /**
     * Process programattic function
     *
     * @return void
     */
    public function tick()
    {
        foreach ($this->users as $user) {
            // Launch registered handler.
            $this->callHandler(
                $user,
                $this->handlerTick,
                [
                    $this,
                    $user,
                ]
            );
        }

    }


    /**
     * Process undecoded user message.
     *
     * @param object $user   User.
     * @param string $buffer Message.
     *
     * @return boolean
     */
    public function processRaw($user, $buffer)
    {
        // Launch registered handler.
        return $this->callHandler(
            $user,
            $this->handlerProcessRaw,
            [
                $this,
                $user,
                $buffer,
            ]
        );
    }


    /**
     * Process user message. Implement.
     *
     * @param object  $user        User.
     * @param string  $message     Message.
     * @param boolean $str_message String message or not.
     *
     * @return void
     */
    public function process($user, $message, $str_message)
    {
        if ($str_message === true) {
            $remmitent = $user->address.'('.$user->account->idUser.')';
            $this->stderr($remmitent.': '.$message);
        }

        // Launch registered handler.
        $this->callHandler(
            $user,
            $this->handlerProcess,
            [
                $this,
                $user,
                $message,
                $str_message,
            ]
        );
    }


    /**
     * Also close internal socket.
     *
     * @param object $user User.
     *
     * @return void
     */
    public function closed($user)
    {
        if ($user->account) {
            $_SERVER['REMOTE_ADDR'] = $user->address;
            \db_pandora_audit(
                AUDIT_LOG_WEB_SOCKETS,
                'WebSocket connection finished',
                'N/A'
            );

            $this->stderr('OFFLINE '.$user->address.'('.$user->account->idUser.')');
        }

        // Ensure both sockets are disconnected.
        $this->disconnect($user->socket);
        if ($user->redirect) {
            $this->disconnect($user->redirect->socket);
        }
    }


}
