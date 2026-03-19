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
require 'vendor/autoload.php';

define('AJAX', true);

if (defined('__PAN_XHPROF__') === false) {
    define('__PAN_XHPROF__', 0);
}

if (__PAN_XHPROF__ === 1) {
    if (function_exists('tideways_xhprof_enable') === true) {
        tideways_xhprof_enable();
    }
}

if (file_exists('include/config.php') === false
    || is_readable('include/config.php') === false
) {
    exit;
}

// Don't start a session before this import.
// The session is configured and started inside the config process.
require_once 'include/config.php';
require_once 'include/functions.php';
require_once 'include/functions_db.php';
require_once 'include/auth/mysql.php';

if (isset($config['console_log_enabled']) === true
    && $config['console_log_enabled'] == 1
) {
    ini_set('log_errors', true);
    ini_set('error_log', $config['homedir'].'/log/console.log');
} else {
    ini_set('log_errors', false);
    ini_set('error_log', '');
}

// Sometimes input is badly retrieved from caller...
if (empty($_REQUEST) === true) {
    $data = explode('&', urldecode(file_get_contents('php://input')));
    foreach ($data as $d) {
        $r = explode('=', $d, 2);
        $_POST[$r[0]] = $r[1];
        $_GET[$r[0]] = $r[1];
    }
}

// Hash login process.
if (isset($_POST['auth_token']) === true && (bool) $config['JWT_signature'] !== false) {
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
}

// Another auth class example: PandoraFMS\Dashboard\Manager.
$auth_class = io_safe_output(
    get_parameter('auth_class', 'PandoraFMS\User')
);

$page = (string) get_parameter('page');
$page = safe_url_extraclean($page);
$page .= '.php';
$page = realpath($page);
$public_hash = get_parameter('auth_hash', false);
$public_login = false;


if (false === ((bool) get_parameter('doLogin', false) === true
    && $page === realpath('include/rest-api/index.php'))
) {
    // Check user.
    if (class_exists($auth_class) === false || $public_hash === false) {
        check_login();
    } else {
        if ($auth_class::validatePublicHash($public_hash) === false) {
            db_pandora_audit(
                AUDIT_LOG_USER_REGISTRATION,
                'Trying to access public dashboard (Invalid public hash)'
            );
            include 'general/noaccess.php';
            exit;
        }

        // OK. Simulated user log in. If you want to use your own auth_class
        // remember to set $config['force_instant_logout'] to true to avoid
        // persistent user login.
    }
}

ob_start();

$config['remote_addr'] = $_SERVER['REMOTE_ADDR'];

$config['id_user'] = $_SESSION['id_usuario'];



if (file_exists($page) === true) {
    include_once $page;
} else {
    echo '<br /><b class="error">Sorry! I can\'t find the page '.$page.'!</b>';
}

if (__PAN_XHPROF__ === 1) {
    pandora_xhprof_display_result('ajax', 'console');
}


if (isset($config['force_instant_logout']) === true
    && $config['force_instant_logout'] === true
) {
    // Force user logout.
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $iduser = $_SESSION['id_usuario'];
    $_SESSION = [];
    session_destroy();
    header_remove('Set-Cookie');
    if (isset($_COOKIE[session_name()]) === true) {
        setcookie(session_name(), $_COOKIE[session_name()], (time() - 4800), '/');
    }

    if ($config['auth'] === 'saml' && empty($public_hash) === true) {
        include_once $config['saml_path'].'simplesamlphp/lib/_autoload.php';
        $as = new SimpleSAML_Auth_Simple('PandoraFMS');
        $as->logout();
    }
}


while (ob_get_length() > 0) {
    ob_end_flush();
}
