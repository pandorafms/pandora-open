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

$searchModules = check_acl($config['id_user'], 0, 'AR');
$searchAgents = check_acl($config['id_user'], 0, 'AR');
$searchAlerts = check_acl($config['id_user'], 0, 'AR');
$searchGraphs = check_acl($config['id_user'], 0, 'RR');
$searchMaps = check_acl($config['id_user'], 0, 'RR');
$searchReports = check_acl($config['id_user'], 0, 'RR');
$searchUsers = check_acl($config['id_user'], 0, 'UM');
$searchPolicies = check_acl($config['id_user'], 0, 'AW');
$searchHelps = true;

echo '<br><div class="margin pdd_10px">';

$anyfound = false;

$table = new stdClass();
$table->id = 'summary';
$table->width = '98%';

$table->style = [];
$table->style[0] = 'font-weight: bold; text-align: left;';
$table->style[1] = 'font-weight: bold; text-align: left;';
$table->style[2] = 'font-weight: bold; text-align: left;';
$table->style[3] = 'font-weight: bold; text-align: left;';
$table->style[4] = 'font-weight: bold; text-align: left;';
$table->style[5] = 'font-weight: bold; text-align: left;';
$table->style[6] = 'font-weight: bold; text-align: left;';
$table->style[7] = 'font-weight: bold; text-align: left;';
$table->style[8] = 'font-weight: bold; text-align: left;';
$table->style[9] = 'font-weight: bold; text-align: left;';
$table->style[10] = 'font-weight: bold; text-align: left;';
$table->style[11] = 'font-weight: bold; text-align: left;';
$table->style[13] = 'font-weight: bold; text-align: left;';
$table->style[14] = 'font-weight: bold; text-align: left;';
$table->style[15] = 'font-weight: bold; text-align: left;';

// Get total agents.
$userGroups = users_get_groups($config['id_user'], 'AR', false);
$id_userGroups = array_keys($userGroups);

$stringSearchSQL = str_replace('&amp;', '&', $stringSearchSQL);
$sql = "SELECT DISTINCT taddress_agent.id_agent FROM taddress
    INNER JOIN taddress_agent ON
    taddress.id_a = taddress_agent.id_a
    WHERE taddress.ip LIKE '$stringSearchSQL'";

    $id = db_get_all_rows_sql($sql);
if ($id != '') {
    $aux = $id[0]['id_agent'];
    $search_sql = " REPLACE(t1.nombre, '&#x20;', ' ') LIKE '".$stringSearchSQL."' OR
        REPLACE(t2.nombre, '&#x20;', ' ') LIKE '".$stringSearchSQL."' OR
        REPLACE(t1.alias, '&#x20;', ' ') LIKE '".$stringSearchSQL."' OR
        REPLACE(t1.comentarios, '&#x20;', ' ') LIKE '".$stringSearchSQL."' OR
        t1.id_agente =".$aux;

    $idCount = count($id);

    if ($idCount >= 2) {
        for ($i = 1; $i < $idCount; $i++) {
            $aux = $id[$i]['id_agent'];
            $search_sql .= " OR t1.id_agente = $aux";
        }
    }
} else {
    $search_sql = " REPLACE(t1.nombre, '&#x20;', ' ') LIKE '".$stringSearchSQL."' OR
        REPLACE(t2.nombre, '&#x20;', ' ') LIKE '".$stringSearchSQL."' OR
        REPLACE(t1.direccion, '&#x20;', ' ') LIKE '".$stringSearchSQL."' OR
        REPLACE(t1.comentarios, '&#x20;', ' ') LIKE '".$stringSearchSQL."' OR
        REPLACE(t1.alias, '&#x20;', ' ') LIKE '".$stringSearchSQL."'";
}

$sql = "
    FROM tagente t1 LEFT JOIN tagent_secondary_group tasg
        ON t1.id_agente = tasg.id_agent
        INNER JOIN tgrupo t2
            ON t2.id_grupo = t1.id_grupo
    WHERE (
            1 = (
                SELECT is_admin
                FROM tusuario
                WHERE id_user = '".$config['id_user']."'
            )
            OR (
                t1.id_grupo IN (".implode(',', $id_userGroups).')
                OR tasg.id_group IN ('.implode(',', $id_userGroups).")
            )
            OR 0 IN (
                SELECT id_grupo
                FROM tusuario_perfil
                WHERE id_usuario = '".$config['id_user']."'
                    AND id_perfil IN (
                        SELECT id_perfil
                        FROM tperfil WHERE agent_view = 1
                    )
                )
        )
        AND (
            ".$search_sql.'
        )
';
$totalAgents = db_get_value_sql(
    'SELECT COUNT(DISTINCT id_agente) AS agent_count '.$sql
);


$table->data[0][0] = html_print_image('images/agent.png', true, ['title' => __('Agents found'), 'class' => 'invert_filter']);
$table->data[0][1] = "<a href='index.php?search_category=agents&keywords=".$config['search_keywords']."&head_search_keywords=Search'>".sprintf(__('%s Found'), $totalAgents).'</a>';
$table->data[0][2] = html_print_image('images/module.png', true, ['title' => __('Modules found'), 'class' => 'invert_filter']);
$table->data[0][3] = "<a href='index.php?search_category=modules&keywords=".$config['search_keywords']."&head_search_keywords=Search'>".sprintf(__('%s Found'), $totalModules).'</a>';

// ------------------- DISABLED FOR SOME INSTALLATIONS------------------
// ~ $table->data[0][4] = html_print_image ("images/bell.png", true, array ("title" => __('Alerts found')));
// ~ $table->data[0][5] = "<a href='index.php?search_category=alerts&keywords=" . $config['search_keywords'] . "&head_search_keywords=Search'>" .
    // ~ sprintf(__("%s Found"), $totalAlerts) . "</a>";
// ---------------------------------------------------------------------
$table->data[0][6] = html_print_image('images/input_user.png', true, ['title' => __('Users found')]);
$table->data[0][7] = "<a href='index.php?search_category=users&keywords=".$config['search_keywords']."&head_search_keywords=Search'>".sprintf(__('%s Found'), $totalUsers).'</a>';
$table->data[0][8] = html_print_image('images/chart_curve.png', true, ['title' => __('Graphs found'), 'class' => 'invert_filter']);
$table->data[0][9] = "<a href='index.php?search_category=graphs&keywords=".$config['search_keywords']."&head_search_keywords=Search'>".sprintf(__('%s Found'), $totalGraphs).'</a>';
$table->data[0][10] = html_print_image('images/reporting.png', true, ['title' => __('Reports found'), 'class' => 'invert_filter']);
$table->data[0][11] = "<a href='index.php?search_category=reports&keywords=".$config['search_keywords']."&head_search_keywords=Search'>".sprintf(__('%s Found'), $totalReports).'</a>';
$table->data[0][12] = html_print_image('images/visual_console_green.png', true, ['title' => __('Visual consoles'), 'class' => 'main_menu_icon invert_filter']);
$table->data[0][13] = "<a href='index.php?search_category=maps&keywords=".$config['search_keywords']."&head_search_keywords=Search'>".sprintf(__('%s Found'), $totalMaps).'</a>';

html_print_table($table);

if ($searchAgents) {
    echo $list_agents;

    echo "<a href='index.php?search_category=agents&keywords=".$config['search_keywords']."&head_search_keywords=Search'>".__('View all matches').'</a>';
}


echo '</div>';
