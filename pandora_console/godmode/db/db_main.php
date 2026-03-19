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

global $config;

check_login();

if (! check_acl($config['id_user'], 0, 'DM')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access Database Management'
    );
    include 'general/noaccess.php';
    return;
}

// Get some general DB stats (not very heavy)
// NOTE: this is not realtime monitoring stats, are more focused on DB sanity
$stat_data = db_get_sql('SELECT COUNT(*) FROM tagente_datos WHERE id_agente_modulo != 0');
$stat_data_log4x = db_get_sql('SELECT COUNT(*) FROM tagente_datos_log4x WHERE id_agente_modulo != 0');
$stat_data_string = db_get_sql('SELECT COUNT(*) FROM tagente_datos_string WHERE id_agente_modulo != 0');
$stat_modules = db_get_sql('SELECT COUNT(*) FROM tagente_estado WHERE id_agente_modulo != 0');
$stat_event = db_get_sql(' SELECT COUNT(*) FROM tevento');
$stat_agente = db_get_sql(' SELECT COUNT(*) FROM tagente');
switch ($config['dbtype']) {
    case 'mysql':
        $stat_uknown = db_get_sql('SELECT COUNT(*) FROM tagente WHERE ultimo_contacto < NOW() - (intervalo * 2)');
    break;

    case 'postgresql':
        $stat_uknown = db_get_sql(
            "SELECT COUNT(*)
			FROM tagente
			WHERE ceil(date_part('epoch', ultimo_contacto)) < ceil(date_part('epoch', NOW())) - (intervalo * 2)"
        );
    break;

    case 'oracle':
        $stat_uknown = db_get_sql(
            'SELECT COUNT(*)
			FROM tagente
			WHERE CAST(ultimo_contacto AS DATE) < SYSDATE - (intervalo * 2)'
        );
    break;
}

switch ($config['dbtype']) {
    case 'mysql':
    case 'postgresql':
        $stat_noninit = db_get_sql('SELECT COUNT(*) FROM tagente_estado WHERE utimestamp = 0;');
    break;

    case 'oracle':
        $stat_noninit = db_get_sql('SELECT COUNT(*) FROM tagente_estado WHERE utimestamp = 0');
    break;
}

// Todo: Recalculate this data dinamically using the capacity and total agents
$max_access = 1000000;
$max_data = 12000000;

ui_print_page_header(__('Current database maintenance setup'), 'images/gm_db.png', false, '', true);

echo '<table class=databox width="98%" cellspacing="4" cellpadding="4" border="0">';

// Current setup
echo '<tr><th colspan=2><i>';
echo __('Database setup');
echo '</i></td></tr>';

echo '<tr class="rowOdd"><td>';
echo __('Max. time before compact data');
echo '<td><b>';
echo $config['days_compact'].' '.__('days');
echo '</b></td></tr>';

echo '<tr class="rowPair"><td>';
echo __('Max. time before purge');
echo '<td><b>';
echo $config['days_purge'].' '.__('days');
echo '</b></td></tr>';


// DB size stats
echo '<tr><th colspan=2><i>';
echo __('Database size stats');
echo '</i></td></tr>';

echo '<tr class="rowPair"><td>';
echo __('Total agents');
echo '<td><b>';
echo $stat_agente;
echo '</b></td></tr>';

echo '<tr class="rowOdd"><td>';
echo __('Total events');
echo '<td><b>';
echo $stat_event;
echo '</b></td></tr>';

echo '<tr class="rowPair"><td>';
echo __('Total data items (tagente_datos)');
echo '<td><b>';

if ($stat_data > $max_data) {
    echo "<font color='#ff0000'>$stat_data</font>";
} else {
    echo $stat_data;
}

echo '</b></td></tr>';


echo '<tr class="rowPair"><td>';
echo __('Total log4x items (tagente_datos_log4x)');
echo '<td><b>';

if ($stat_data_log4x > $max_data) {
    echo "<font color='#ff0000'>$stat_data_log4x</font>";
} else {
    echo $stat_data_log4x;
}

echo '</b></td></tr>';


echo '<tr class="rowOdd"><td>';
echo __('Total data string items (tagente_datos_string)');
echo '<td><b>';
echo $stat_data_string;
echo '</b></td></tr>';

echo '<tr class="rowPair"><td>';
echo __('Total modules configured');
echo '<td><b>';
echo $stat_modules;
echo '</b></td></tr>';


// Sanity
echo '<tr><th colspan=2><i>';
echo __('Database sanity');
echo '</i></td></tr>';

echo '<tr class="rowPair"><td>';
echo __('Total uknown agents');
echo '<td><b>';
echo $stat_uknown;
echo '</b></td></tr>';

echo '<tr class="rowOdd"><td>';
echo __('Total non-init modules');
echo '<td><b>';
echo $stat_noninit;
echo '</b></td></tr>';




echo '<tr class="rowPair"><td>';
echo __('Last time on DB maintance');
echo '<td>';

if (!isset($config['db_maintance'])) {
    echo '<b><font size=12px>'.__('Never').'</font></b>';
} else {
    $seconds = (time() - $config['db_maintance']);
    if ($seconds > 90000) {
        // (1,1 days)
        echo "<b><font color='#ff0000' size=12px>";
    } else {
        echo '<font><b>';
    }

    echo human_time_description_raw($seconds);
    echo ' *';
}

echo '</td></tr>';


echo '<tr><td colspan=2>';
echo '<div align="justify"><br><hr width=100%>';
echo '(*) '.__("Please make sure your %s Server settings are correct and that the database maintenance daemon is running. It's very important to keep your database up to date in order to get the best performance and results from %s.", get_product_name(), get_product_name());
echo '</div>';
echo '</td></tr></table>';
