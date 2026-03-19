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

require_once 'include/functions_modules.php';
require_once 'include/functions_events.php';
require_once 'include/functions_groups.php';
require_once 'include/functions_netflow.php';
require_once 'include/functions_reporting_xml.php';
// Login check
if (isset($_GET['direct']) && $_GET['direct']) {
    /*
        This is in case somebody wants to access the XML directly without
        having the possibility to login and handle sessions

        Use this URL: https://yourserver/pandora_console/operation/reporting/reporting_xml.php?id=<reportid>&direct=1

        Although it's not recommended, you can put your login and password
        in a GET request (append &nick=<yourlogin>&password=<password>).

        You SHOULD put it in a POST but some programs
        might not be able to handle it without extensive re-programming
        Either way, you should have a read-only user for getting reports

        XMLHttpRequest can do it (example):

        var reportid = 3;
        var login = "yourlogin";
        var password = "yourpassword";
        var url = "https://<yourserver>/pandora_console/operation/reporting/reporting_xml.php?id="+urlencode(reportid)+"&direct=1";
        var params = "nick="+urlencode(login)+"&pass="+urlencode(password);
        var xmlHttp = new XMLHttpRequest();
        var textout = "";
        try {
        xmlHttp.open("POST", url, false);
        xmlHttp.send(params);
        if(xmlHttp.readyState == 4 && xmlHttp.status == 200) {
            textout = xmlHttp.responseXML;
        }
        }
        catch (err) {
        alert ("error");
        }
    */
    include_once '../../include/config.php';
    include_once '../../include/functions_reporting.php';
    include_once '../../include/functions_db.php';

    $nick = get_parameter('nick');
    $pass = get_parameter('pass');

    $nick = process_user_login($nick, $pass);

    if ($nick !== false) {
        unset($_GET['sec2']);
        $_GET['sec'] = 'general/logon_ok';
        db_logon($nick, $_SERVER['REMOTE_ADDR']);
        $_SESSION['id_usuario'] = $nick;
        $config['id_user'] = $nick;
        // Remove everything that might have to do with people's passwords or logins.
        unset($_GET['pass'], $pass, $_POST['pass'], $_REQUEST['pass'], $login_good);
    } else {
        // User not known.
        $login_failed = true;
        include_once 'general/login_page.php';
        db_pandora_audit(
            AUDIT_LOG_USER_REGISTRATION,
            'Invalid login: '.$nick,
            $nick
        );
        exit;
    }
} else {
    include_once 'include/config.php';
    include_once 'include/functions_reporting.php';
    include_once 'include/functions_db.php';
}

global $config;

check_login();

$id_report = (int) get_parameter('id');
$filename = (string) get_parameter('filename');

$date_mode = get_parameter('date_mode', 'none');

$date_init = get_parameter('date_init', '');
if (empty($date_init) === false) {
    $date_end = get_parameter('date_end', time());
    $period = ($date_end - $date_init);
    $date = date('Y-m-d', $date_end);
    $time = date('H:i:s', $date_end);
}


$report = reporting_make_reporting_data(
    null,
    $id_report,
    $date,
    $time,
    $period,
    'static'
);

if (empty($filename)) {
    $filename = $report['name'].'_report_'.date('Y-m-d_His');
}

reporting_xml_get_report($report, $filename);

exit;
