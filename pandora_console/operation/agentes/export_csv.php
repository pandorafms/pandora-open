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

require_once '../../include/config.php';
require_once '../../include/functions.php';
require_once '../../include/functions_db.php';
require_once '../../include/functions_modules.php';
require_once '../../include/functions_agents.php';

$config['id_user'] = $_SESSION['id_usuario'];
if (! check_acl($config['id_user'], 0, 'AR') && ! check_acl($config['id_user'], 0, 'AW')) {
    include '../../general/noaccess.php';
    return;
}

if (isset($_GET['agentmodule']) && isset($_GET['agent'])) {
    $id_agentmodule = $_GET['agentmodule'];
    $id_agent = $_GET['agent'];
    $agentmodule_name = modules_get_agentmodule_name($id_agentmodule);
    if (! check_acl($config['id_user'], agents_get_agent_group($id_agent), 'AR')) {
        db_pandora_audit(
            AUDIT_LOG_ACL_VIOLATION,
            'Trying to access Agent Export Data'
        );
        include '../../general/noaccess.php';
        exit;
    }

    $now = date('Y/m/d H:i:s');

    // Show contentype header
    header('Content-type: text/txt');
    header('Content-Disposition: attachment; filename="pandora_export_'.$agentmodule_name.'.txt"');

    if (isset($_GET['from_date'])) {
        $from_date = $_GET['from_date'];
    } else {
        $from_date = $now;
    }

    if (isset($_GET['to_date'])) {
        $to_date = $_GET['to_date'];
    } else {
        $to_date = $now;
    }

    // Convert to unix date
    $from_date = date('U', strtotime($from_date));
    $to_date = date('U', strtotime($to_date));

    // Make the query
    $sql1 = "
		SELECT *
		FROM tdatos
		WHERE id_agente = $id_agent
			AND id_agente_modulo = $id_agentmodule";
    $tipo = modules_get_moduletype_name(modules_get_agentmodule_type($id_agentmodule));
    if ($tipo == 'generic_data_string') {
        $sql1 = "
			SELECT *
			FROM tagente_datos_string
			WHERE utimestamp > $from_date AND utimestamp < $to_date
				AND id_agente_modulo = $id_agentmodule
			ORDER BY utimestamp DESC";
    } else {
        $sql1 = "
			SELECT *
			FROM tagente_datos
			WHERE utimestamp > $from_date AND utimestamp < $to_date
				AND id_agente_modulo = $id_agentmodule
			ORDER BY utimestamp DESC";
    }

    $result1 = db_get_all_rows_sql($sql1, true);
    if ($result1 === false) {
        $result1 = [];
    }

    // Render data
    foreach ($result1 as $row) {
        echo $agentmodule_name;
        echo ',';
        echo $row['datos'];
        echo ',';
        echo $row['utimestamp'];
        echo chr(13);
    }
}
