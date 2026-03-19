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


/**
 * @package    Include
 * @subpackage Graphs
 */


/**
 * @global array Contents all var configs for the local instalation.
 */
global $config;

require_once $config['homedir'].'/include/functions_graph.php';
require_once $config['homedir'].'/include/functions_users.php';


function custom_graphs_create(
    $id_modules=[],
    $name='',
    $description='',
    $stacked=CUSTOM_GRAPH_AREA,
    $width=0,
    $height=0,
    $events=0,
    $period=0,
    $private=0,
    $id_group=0,
    $user=false,
    $fullscale=0
) {
    global $config;

    if ($user === false) {
        $user = $config['id_user'];
    }

    $id_graph = db_process_sql_insert(
        'tgraph',
        [
            'id_user'           => $user,
            'name'              => $name,
            'description'       => $description,
            'period'            => $period,
            'width'             => $width,
            'height'            => $height,
            'private'           => $private,
            'events'            => $events,
            'stacked'           => $stacked,
            'id_group'          => $id_group,
            'id_graph_template' => 0,
            'fullscale'         => $fullscale,
        ]
    );

    if (empty($id_graph)) {
        return false;
    } else {
        $result = true;
        foreach ($id_modules as $id_module) {
            $result = db_process_sql_insert(
                'tgraph_source',
                [
                    'id_graph'        => $id_graph,
                    'id_agent_module' => $id_module,
                    'weight'          => 1,
                ]
            );

            if (empty($result)) {
                break;
            }
        }

        if (empty($result)) {
            // Not it is a complete insert the modules. Delete all
            db_process_sql_delete(
                'tgraph_source',
                ['id_graph' => $id_graph]
            );

            db_process_sql_delete(
                'tgraph',
                ['id_graph' => $id_graph]
            );

            return false;
        }

        return $id_graph;
    }
}


/**
 * Get all the custom graphs a user can see.
 *
 * @param $id_user User id to check.
 * @param $only_names Wheter to return only graphs names in an associative array
 * or all the values.
 * @param $returnAllGroup Wheter to return graphs of group All or not.
 * @param $privileges Privileges to check in user group
 *
 * @return array graphs of a an user. Empty array if none.
 */
function custom_graphs_get_user($id_user=0, $only_names=false, $returnAllGroup=true, $privileges='RR')
{
    global $config;

    if (!$id_user) {
        $id_user = $config['id_user'];
    }

    $groups = users_get_groups($id_user, $privileges, $returnAllGroup);
    $all_graphs = [];

        $all_graphs = db_get_all_rows_in_table('tgraph', 'name');
    

    if ($all_graphs === false) {
        return [];
    }

    $graphs = [];
    foreach ($all_graphs as $graph) {
        if (!in_array($graph['id_group'], array_keys($groups))) {
            continue;
        }

        if ($graph['id_user'] != $id_user && $graph['private']) {
            continue;
        }

        if ($graph['id_group'] > 0) {
            if (!isset($groups[$graph['id_group']])) {
                continue;
            }
        }

        if ($only_names) {
            $graphs[$graph['id_graph']] = $graph['name'];
        } else {
            $graphs[$graph['id_graph']] = $graph;
            $id_graph = 'id_graph';
            

            $graphsCount = db_get_value_sql(
                'SELECT COUNT(id_gs)
				FROM tgraph_source
				WHERE id_graph = '.$graph[$id_graph]
            );
            $graphs[$graph['id_graph']]['graphs_count'] = $graphsCount;
        }
    }

    return $graphs;
}


function custom_graphs_search($id_group, $search)
{
    if ($id_group != '' && $search != '') {
        $all_graphs = db_get_all_rows_sql('select * from tgraph where id_group = '.$id_group.' AND (REPLACE(name, "&#x20;", " ")  LIKE "%'.$search.'%" OR REPLACE(description, "&#x20;", " ")  LIKE "'.$search.'")');
    } else if ($id_group != '') {
        $all_graphs = db_get_all_rows_sql('select * from tgraph where id_group = '.$id_group.'');
    } else {
        $all_graphs = db_get_all_rows_sql('select * from tgraph where REPLACE(name, "&#x20;", " ") LIKE "%'.$search.'%" OR REPLACE(description, "&#x20;", " ") LIKE "'.$search.'"');
    }

    if ($all_graphs === false) {
        return [];
    }

    $graphs = [];
    foreach ($all_graphs as $graph) {
        $graphsCount = db_get_value_sql(
            'SELECT COUNT(id_gs)
                        FROM tgraph_source
                        WHERE id_graph = '.$graph['id_graph'].''
        );
        $graphs[$graph['id_graph']]['id_graph'] = $graph['id_graph'];
        $graphs[$graph['id_graph']]['graphs_count'] = $graphsCount;
        $graphs[$graph['id_graph']]['name'] = $graph['name'];
        $graphs[$graph['id_graph']]['description'] = $graph['description'];
        $graphs[$graph['id_graph']]['id_group'] = $graph['id_group'];
    }

    return $graphs;
}
