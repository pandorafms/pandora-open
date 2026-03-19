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


/**
 * Open session.
 *
 * @param string $save_path    Save path.
 * @param string $session_name Session name.
 *
 * @return boolean
 */
function pandora_session_open($save_path, $session_name)
{
    return true;
}


/**
 * Close session.
 *
 * @return boolean
 */
function pandora_session_close()
{
    return true;
}


/**
 * Read a session.
 *
 * @param string $session_id Session ID.
 *
 * @return string Session data.
 */
function pandora_session_read($session_id)
{
    $session_id = addslashes($session_id);

    // Do not use SQL cache here.
    $session_data = db_get_all_rows_sql(
        sprintf(
            'SELECT data
            FROM `tsessions_php` WHERE id_session="%s"',
            $session_id
        ),
        false,
        false
    );

    if (is_array($session_data) === true) {
        $session_data = $session_data[0]['data'];
    }

    if (empty($session_data) === false) {
        return $session_data;
    } else {
        return '';
    }
}


/**
 * Write session data.
 *
 * @param string $session_id Session id.
 * @param string $data       Data.
 *
 * @return boolean
 */
function pandora_session_write($session_id, $data)
{
    $session_id = addslashes($session_id);
    if (is_ajax()) {
        // Avoid session upadte while processing ajax responses - notifications.
        if (get_parameter('check_new_notifications', false)) {
            return true;
        }
    }

    $values = [];
    $values['last_active'] = time();

    if (empty($data) === false) {
        $values['data'] = addslashes($data);
    }

    // Do not use SQL cache here.
    $session_exists = db_get_all_rows_sql(
        sprintf(
            'SELECT id_session
             FROM `tsessions_php` WHERE id_session="%s"',
            $session_id
        ),
        false,
        false
    );

    if ($session_exists === false) {
        $values['id_session'] = $session_id;
        $retval_write = db_process_sql_insert('tsessions_php', $values);
    } else {
        $retval_write = db_process_sql_update(
            'tsessions_php',
            $values,
            ['id_session' => $session_id]
        );
    }

    return $retval_write !== false;
}


/**
 * Destroy a session.
 *
 * @param string $session_id Session Id.
 *
 * @return boolean
 */
function pandora_session_destroy($session_id)
{
    $session_id = addslashes($session_id);

    $retval = (bool) db_process_sql_delete(
        'tsessions_php',
        ['id_session' => $session_id]
    );

    return $retval;
}


/**
 * Session garbage collector.
 *
 * @param integer $max_lifetime Max lifetime.
 *
 * @return boolean.
 */
function pandora_session_gc($max_lifetime=300)
{
    global $config;

    if (isset($config['session_timeout'])) {
        $session_timeout = $config['session_timeout'];
    } else {
        // If $config doesn`t work ...
        $session_timeout = db_get_value(
            'value',
            'tconfig',
            'token',
            'session_timeout'
        );
    }

    if (empty($session_timeout) === false) {
        if ($session_timeout == -1) {
            // The session expires in 10 years.
            $session_timeout = 315576000;
        } else {
            $session_timeout *= 60;
        }

        $max_lifetime = $session_timeout;
    }

    $time_limit = (time() - $max_lifetime);

    $retval = (bool) db_process_sql_delete(
        'tsessions_php',
        [
            'last_active' => '<'.$time_limit,
        ]
    );

    // Deleting cron and empty sessions.
    $sql = 'DELETE FROM tsessions_php WHERE data IS NULL';
    db_process_sql($sql);

    return $retval;
}


/**
 * Enables custom session handlers.
 *
 * @return boolean Context changed or  not.
 */
function enable_session_handlers()
{
    global $config;

    if (isset($config['_using_pandora_sessionhandlers']) !== true
        || $config['_using_pandora_sessionhandlers'] !== true
    ) {
        if (session_status() !== PHP_SESSION_NONE) {
            // Close previous version.
            session_write_close();
        }

        $sesion_handler = session_set_save_handler(
            'pandora_session_open',
            'pandora_session_close',
            'pandora_session_read',
            'pandora_session_write',
            'pandora_session_destroy',
            'pandora_session_gc'
        );

        session_start();

        // Restore previous session.
        $config['_using_pandora_sessionhandlers'] = true;
        return $sesion_handler;
    }

    return false;
}


/**
 * Disables custom session handlers.
 *
 * @param string|null $id_session Force swap to target session.
 *
 * @return void
 */
function disable_session_handlers($id_session=null)
{
    global $config;

    if (session_status() !== PHP_SESSION_NONE) {
        // Close previous version.
        session_write_close();
    }

    $ss = new SessionHandler();
    session_set_save_handler($ss, true);

    if ($id_session !== null) {
        session_id($id_session);
    }

    session_start();

    $config['_using_pandora_sessionhandlers'] = false;
}


// Always enable session handler.
$result_handler = enable_session_handlers();
