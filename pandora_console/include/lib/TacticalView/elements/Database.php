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
 * Database, this class contain all logic for this section.
 */
class Database extends Element
{


    /**
     * Constructor
     */
    public function __construct()
    {
        global $config;
        parent::__construct();
        include_once $config['homedir'].'/include/graphs/fgraph.php';
        $this->title = __('Database');
        $this->ajaxMethods = [
            'getStatus',
            'getDataRecords',
            'getEvents',
            'getStringRecords',
            'getReadsGraph',
            'getWritesGraph',
        ];
        $this->interval = 300000;
        $this->refreshConfig = [
            'status'       => [
                'id'     => 'status-database',
                'method' => 'getStatus',
            ],
            'records'      => [
                'id'     => 'data-records',
                'method' => 'getDataRecords',
            ],
            'events'       => [
                'id'     => 'total-events',
                'method' => 'getEvents',
            ],
            'totalRecords' => [
                'id'     => 'total-records',
                'method' => 'getStringRecords',

            ],
            'reads'        => [
                'id'     => 'database-reads',
                'method' => 'getReadsGraph',
            ],
            'writes'       => [
                'id'     => 'database-writes',
                'method' => 'getWritesGraph',
            ],
        ];
    }


    /**
     * Returns the html status of database.
     *
     * @return string
     */
    public function getStatus():string
    {
        // TODO connect to automonitorization.
        $status = true;

        if ($status === true) {
            $image_status = html_print_image('images/status_check@svg.svg', true);
            $text = html_print_div(
                [
                    'content' => __('Everything\'s OK!'),
                    'class'   => 'status-text',
                ],
                true
            );
        } else {
            $image_status = html_print_image('images/status_error@svg.svg', true);
            $text = html_print_div(
                [
                    'content' => __('Something’s wrong'),
                    'class'   => 'status-text',
                ],
                true
            );
        }

        $output = $image_status.$text;

        return html_print_div(
            [
                'content' => $output,
                'class'   => 'flex_center margin-top-5',
                'id'      => 'status-database',
                'style'   => 'margin: 0px 10px 10px 10px;',
            ],
            true
        );
    }


    /**
     * Returns the html records data of database.
     *
     * @return string
     */
    public function getDataRecords():string
    {
        $data = $this->valueMonitoring('mysql_size_of_data');
        $value = format_numeric($data[0]['datos'], 2).' MB';
        return html_print_div(
            [
                'content' => $value,
                'class'   => 'text-l',
                'id'      => 'data-records',
                'style'   => 'margin: 0px 10px 10px 10px;',
            ],
            true
        );
    }


    /**
     * Returns the html of total events.
     *
     * @return string
     */
    public function getEvents():string
    {
        $data = $this->valueMonitoring('last_events_24h');
        $value = format_numeric($data[0]['datos']);
        return html_print_div(
            [
                'content' => $value,
                'class'   => 'text-l',
                'id'      => 'total-events',
                'style'   => 'margin: 0px 10px 10px 10px;',
            ],
            true
        );
    }


    /**
     * Returns the html of total records.
     *
     * @return string
     */
    public function getStringRecords():string
    {
        $data = $this->valueMonitoring('total_string_data');
        $value = format_numeric($data[0]['datos']);
        return html_print_div(
            [
                'content' => $value,
                'class'   => 'text-l',
                'id'      => 'total-records',
                'style'   => 'margin: 0px 10px 10px 10px;',
            ],
            true
        );
    }


    /**
     * Returns the html of total reads database in a graph.
     *
     * @return string
     */
    public function getReadsGraph():string
    {
        $dateInit = (time() - 86400);
        $reads = $this->valueMonitoring('mysql_questions_reads', $dateInit, time());
        $dates = [];
        $string_reads = [];
        $total = 0;
        foreach ($reads as $key => $read) {
            if (isset($read['utimestamp']) === false) {
                $read['utimestamp'] = 0;
            }

            $dates[] = date('d-m-Y H:i:s', $read['utimestamp']);
            $string_reads[] = $read['datos'];
            $total += $read['datos'];
        }

        $options = [
            'labels'   => $dates,
            'legend'   => [ 'display' => false ],
            'tooltips' => [ 'display' => false ],
            'scales'   => [
                'y' => [
                    'grid'    => ['display' => false],
                    'ticks'   => ['display' => false],
                    'display' => false,
                ],
                'x' => [
                    'grid'    => ['display' => false],
                    'display' => false,
                ],
            ],
            'elements' => [ 'point' => [ 'radius' => 0 ] ],
        ];

        $data = [
            [
                'backgroundColor'       => '#EC7176',
                'borderColor'           => '#EC7176',
                'pointBackgroundColor'  => '#EC7176',
                'pointHoverBorderColor' => '#EC7176',
                'data'                  => $string_reads,
            ],
        ];

        $graph_area = html_print_div(
            [
                'content' => line_graph($data, $options),
                'class'   => 'w100p h100p centered',
                'style'   => 'max-height: 83px; max-width: 93%; margin-bottom: 10px;',
            ],
            true
        );

        $total = html_print_div(
            [
                'content' => format_numeric($total),
                'class'   => 'text-xl',
            ],
            true
        );

        $output = html_print_div(
            [
                'content' => $total.$graph_area,
                'id'      => 'database-reads',
            ],
            true
        );

        return $output;
    }


    /**
     * Returns the html of total writes database in a graph.
     *
     * @return string
     */
    public function getWritesGraph():string
    {
        $dateInit = (time() - 86400);
        $writes = $this->valueMonitoring('mysql_questions_writes', $dateInit, time());
        $dates = [];
        $string_writes = [];
        $total = 0;
        foreach ($writes as $key => $write) {
            if (isset($write['utimestamp']) === false) {
                $write['utimestamp'] = 0;
            }

            $dates[] = date('d-m-Y H:i:s', $write['utimestamp']);
            $string_writes[] = $write['datos'];
            $total += $write['datos'];
        }

        $options = [
            'labels'   => $dates,
            'legend'   => [ 'display' => false ],
            'tooltips' => [ 'display' => false ],
            'scales'   => [
                'y' => [
                    'grid'    => ['display' => false],
                    'ticks'   => ['display' => false],
                    'display' => false,
                ],
                'x' => [
                    'grid'    => ['display' => false],
                    'display' => false,
                ],
            ],
            'elements' => [ 'point' => [ 'radius' => 0 ] ],
        ];

        $data = [
            [
                'backgroundColor'       => '#009D9E',
                'borderColor'           => '#009D9E',
                'pointBackgroundColor'  => '#009D9E',
                'pointHoverBorderColor' => '#009D9E',
                'data'                  => $string_writes,
            ],
        ];

        $graph_area = html_print_div(
            [
                'content' => line_graph($data, $options),
                'class'   => 'w100p h100p centered',
                'style'   => 'max-height: 83px; max-width: 93%; margin-bottom: 10px;',
            ],
            true
        );

        $total = html_print_div(
            [
                'content' => format_numeric($total),
                'class'   => 'text-xl',
            ],
            true
        );

        $output = html_print_div(
            [
                'content' => $total.$graph_area,
                'id'      => 'database-writes',
            ],
            true
        );

        return $output;
    }


    /**
     * Check if user can manage database
     *
     * @return boolean
     */
    public function checkAcl():bool
    {
        global $config;
        $db_m = (bool) check_acl($config['id_user'], 0, 'DM');
        return $db_m;
    }


}
