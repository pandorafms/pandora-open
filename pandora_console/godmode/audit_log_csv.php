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
 * be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
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

$ownDir = dirname(__FILE__).'/';
$ownDir = str_replace('\\', '/', $ownDir);

// Don't start a session before this import.
// The session is configured and started inside the config process.
require_once $ownDir.'../include/config.php';

require_once $config['homedir'].'/include/functions.php';
require_once $config['homedir'].'/include/functions_db.php';
require_once $config['homedir'].'/include/auth/mysql.php';

global $config;

// Login check
if (!isset($_SESSION['id_usuario'])) {
    $config['id_user'] = null;
} else {
    $config['id_user'] = $_SESSION['id_usuario'];
}

check_login();

if (! check_acl($config['id_user'], 0, 'PM')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access audit CSV export'
    );
    include 'general/noaccess.php';
    exit;
}

$filter_type = (string) get_parameter('filter_type');
$filter_user = (string) get_parameter('filter_user');
$filter_text = (string) get_parameter('filter_text');
$filter_period = get_parameter('filter_period', null);
$filter_period = ($filter_period !== null) ? (int) $filter_period : 24;
$filter_ip = (string) get_parameter('filter_ip');

$filter = '1=1';

if (!empty($filter_type)) {
    $filter .= sprintf(" AND accion = '%s'", $filter_type);
}

if (!empty($filter_user)) {
    $filter .= sprintf(" AND id_usuario = '%s'", $filter_user);
}

if (!empty($filter_text)) {
    $filter .= sprintf(" AND (accion LIKE '%%%s%%' OR descripcion LIKE '%%%s%%')", $filter_text, $filter_text);
}

if (!empty($filter_ip)) {
    $filter .= sprintf(" AND ip_origen LIKE '%%%s%%'", $filter_ip);
}

if (!empty($filter_period)) {
    switch ($config['dbtype']) {
        case 'mysql':
            $filter .= ' AND fecha >= DATE_ADD(NOW(), INTERVAL -'.$filter_period.' HOUR)';
        break;

        case 'postgresql':
            $filter .= ' AND fecha >= NOW() - INTERVAL \''.$filter_period.' HOUR \'';
        break;

        case 'oracle':
            $filter .= ' AND fecha >= (SYSTIMESTAMP - INTERVAL \''.$filter_period.'\' HOUR)';
        break;
    }
}

$sql = sprintf('SELECT * FROM tsesion WHERE %s ORDER BY fecha DESC', $filter);
$result = db_get_all_rows_sql($sql);

print_audit_csv($result);
