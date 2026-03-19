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

use PandoraFMS\TacticalView\Element;

/**
 * MonitoringElements, this class contain all logic for this section.
 */
class MonitoringElements extends Element
{


    /**
     * Constructor
     */
    public function __construct()
    {
        global $config;
        parent::__construct();
        include_once $config['homedir'].'/include/graphs/fgraph.php';
        include_once $config['homedir'].'/include/functions_graph.php';
        $this->title = __('Monitoring elements');
    }


    /**
     * Returns the html of the tags grouped by modules.
     *
     * @return string
     */
    public function getTagsGraph():string
    {
        $sql = 'SELECT name, count(*) AS total
                FROM ttag_module t
                LEFT JOIN ttag ta ON ta.id_tag = t.id_tag
                GROUP BY t.id_tag
                ORDER BY total DESC
                LIMIT 10;';
        $rows = db_process_sql($sql);

        $labels = [];
        $data = [];
        if ($rows !== false) {
            foreach ($rows as $key => $row) {
                if (empty($row['name']) === true) {
                    continue;
                }

                $labels[] = $this->controlSizeText($row['name']);
                $data[] = $row['total'];
            }
        }

        $options = [
            'labels'       => $labels,
            'legend'       => [
                'position' => 'bottom',
                'align'    => 'right',
                'display'  => false,
            ],
            'cutout'       => 80,
            'nodata_image' => [
                'width'  => '100%',
                'height' => '90%',
            ],
        ];
        $pie = ring_graph($data, $options);
        $output = html_print_div(
            [
                'content' => $pie,
                'style'   => 'margin: 0 auto; max-width: 80%; max-height: 220px;',
            ],
            true
        );

        return $output;
    }


    /**
     * Returns the html of the groups grouped by modules.
     *
     * @return string
     */
    public function getModuleGroupGraph():string
    {
        global $config;
        $id_groups = array_keys(users_get_groups($config['id_user'], 'AR', false));

        if (in_array(0, $id_groups) === false) {
            foreach ($id_groups as $key => $id_group) {
                if ((bool) check_acl_restricted_all($config['id_user'], $id_group, 'AR') === false) {
                    unset($id_groups[$key]);
                }
            }
        }

        $id_groups = implode(',', $id_groups);
        $sql = 'SELECT name, count(*) AS total
                FROM tagente_modulo m
                LEFT JOIN tagente a on a.id_agente = m.id_agente
                LEFT JOIN tagent_secondary_group gs ON gs.id_agent = a.id_agente
                LEFT JOIN tmodule_group g ON g.id_mg = m.id_module_group
                WHERE name <> "" AND (a.id_grupo IN ('.$id_groups.') OR gs.id_group IN ('.$id_groups.'))
                GROUP BY m.id_module_group
                ORDER BY total DESC
                LIMIT 10';
        $rows = db_process_sql($sql);

        $labels = [];
        $data = [];
        foreach ($rows as $key => $row) {
            if (empty($row['name']) === true) {
                continue;
            }

            $labels[] = $this->controlSizeText($row['name']);
            $data[] = $row['total'];
        }

        $options = [
            'labels'       => $labels,
            'legend'       => [
                'position' => 'bottom',
                'align'    => 'right',
                'display'  => false,
            ],
            'cutout'       => 80,
            'nodata_image' => [
                'width'  => '100%',
                'height' => '90%',
            ],
        ];
        $pie = ring_graph($data, $options);
        $output = html_print_div(
            [
                'content' => $pie,
                'style'   => 'margin: 0 auto; max-width: 80%; max-height: 220px;',
            ],
            true
        );

        return $output;
    }


    /**
     * Returns the html of the agent grouped by modules.
     *
     * @return string
     */
    public function getAgentGroupsGraph():string
    {
        global $config;
        $id_groups = array_keys(users_get_groups($config['id_user'], 'AR', false));

        if (in_array(0, $id_groups) === false) {
            foreach ($id_groups as $key => $id_group) {
                if ((bool) check_acl_restricted_all($config['id_user'], $id_group, 'AR') === false) {
                    unset($id_groups[$key]);
                }
            }
        }

        $id_groups = implode(',', $id_groups);

        $sql = 'SELECT gr.nombre, count(*) +
                IFNULL((SELECT count(*) AS total
                        FROM tagente second_a
                        LEFT JOIN tagent_secondary_group second_g ON second_g.id_agent = second_a.id_agente
                        WHERE a.id_grupo = second_g.id_group AND second_g.id_group IN ('.$id_groups.')
                        GROUP BY second_g.id_group
                        ), 0) AS total
                FROM tagente a
                LEFT JOIN tgrupo gr ON gr.id_grupo = a.id_grupo
                WHERE a.id_grupo IN ('.$id_groups.')
                GROUP BY a.id_grupo
                ORDER BY total DESC
                LIMIT 10';
        $rows = db_process_sql($sql);

        $labels = [];
        $data = [];
        foreach ($rows as $key => $row) {
            if (empty($row['nombre']) === true) {
                continue;
            }

            $labels[] = $this->controlSizeText(io_safe_output($row['nombre']));
            $data[] = $row['total'];
        }

        $options = [
            'labels'       => $labels,
            'legend'       => [
                'position' => 'bottom',
                'align'    => 'right',
                'display'  => false,
            ],
            'cutout'       => 80,
            'nodata_image' => [
                'width'  => '100%',
                'height' => '90%',
            ],
        ];
        $pie = ring_graph($data, $options);
        $output = html_print_div(
            [
                'content' => $pie,
                'style'   => 'margin: 0 auto; max-width: 80%; max-height: 220px;',
            ],
            true
        );

        return $output;
    }


    /**
     * Returns the html of monitoring by status.
     *
     * @return string
     */
    public function getMonitoringStatusGraph():string
    {
        $pie = graph_agent_status(false, '', '', true, true, false, true, 'redirectStatus', true);
        $output = html_print_div(
            [
                'content' => $pie,
                'style'   => 'margin: 0 auto; max-width: 80%; max-height: 220px;',
                'class'   => 'clickable',
            ],
            true
        );

        return $output;
    }


}
