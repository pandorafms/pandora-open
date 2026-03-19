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
 * Events, this class contain all logic for this section.
 */
class Events extends Element
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
        $this->title = __('Events');
        $this->ajaxMethods = [
            'getEventsGraph',
            'getEventsCriticalityGraph',
            'getEventsStatusValidateGraph',
            'getEventsStatusGraph',
        ];
    }


    /**
     * Return the html graph of events in last 24h.
     *
     * @return string
     */
    public function getEventsGraph():string
    {
        global $config;
        $id_groups = array_keys(users_get_groups($config['id_user'], 'AR', false));
        $id_groups = implode(',', $id_groups);
        $event_view_h = (int) ($config['event_view_hr'] > 24) ? 24 : $config['event_view_hr'];
        $time_events = ($event_view_h * 3600);
        $intervalh = (time() - $time_events);
        $sql = 'SELECT utimestamp
                FROM tevento
                WHERE utimestamp >= '.$intervalh.' AND id_grupo IN ('.$id_groups.') ORDER BY utimestamp DESC;';
        $rows = db_process_sql($sql);
        $cut_seconds = ($time_events / 24);
        $now = (time() - 300);
        $cuts_intervals = [];
        for ($i = 0; $i < 24; $i++) {
            $cuts_intervals[$now] = 0;
            $now -= $cut_seconds;
        }

        foreach ($rows as $key => $row) {
            foreach ($cuts_intervals as $time => $count) {
                if ($row['utimestamp'] > $time) {
                    $cuts_intervals[$time]++;
                    break;
                }
            }
        }

        $cuts_intervals = array_reverse($cuts_intervals, true);
        $graph_values = [];
        $colors = [];
        $max_value = 0;
        foreach ($cuts_intervals as $utimestamp => $count) {
            if ($max_value < $count) {
                $max_value = $count;
            }

            $graph_values[] = [
                'y' => $count,
                'x' => date('d-m-Y H:i:s', $utimestamp),
            ];
        }

        $danger = $max_value;
        $ok = ($max_value / 3);

        foreach ($graph_values as $key => $value) {
            if ($value['y'] >= $danger) {
                $colors[] = '#EC7176';
            }

            if ($value['y'] >= $ok && $value['y'] < $danger) {
                $colors[] = '#FCAB10';
            }

            if ($value['y'] < $ok) {
                $colors[] = '#82B92E';
            }
        }

        $options = [
            'height'       => 237,
            'legend'       => ['display' => false],
            'scales'       => [
                'x' => [
                    'bounds'  => 'data',
                    'grid'    => ['display' => false],
                    'display' => false,
                ],
                'y' => [
                    'grid' => ['display' => false],
                ],
            ],
            'colors'       => $colors,
            'borderColors' => ['#ffffff'],
        ];

        $bar = vbar_graph($graph_values, $options);

        $output = html_print_div(
            [
                'content' => $bar,
                'class'   => 'margin-top-5 w100p relative',
                'style'   => 'max-height: 250px;',
            ],
            true
        );

        return $output;
    }


    /**
     * Return the html graph of events in last 8h grouped by criticity.
     *
     * @return string
     */
    public function getEventsCriticalityGraph():string
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

        if (users_can_manage_group_all() === true) {
            $id_groups[] = 0;
        }

        $id_groups = implode(',', $id_groups);
        $event_view_h = (int) ($config['event_view_hr'] > 24) ? 24 : $config['event_view_hr'];
        $time_events = ($event_view_h * 3600);
        $intervalh = (time() - $time_events);
        $sql = 'SELECT criticity, count(*)  AS total
        FROM tevento
        WHERE utimestamp >= '.$intervalh.' AND id_grupo IN ('.$id_groups.')
        group by criticity';

        $rows = db_process_sql($sql);

        $labels = [];
        $data = [];
        $colors = [];
        foreach ($rows as $key => $row) {
            switch ($row['criticity']) {
                case EVENT_CRIT_CRITICAL:
                    $label = __('CRITICAL');
                    $colors[] = COL_CRITICAL;
                break;

                case EVENT_CRIT_MAINTENANCE:
                    $label = __('MAINTENANCE');
                    $colors[] = COL_MAINTENANCE;
                break;

                case EVENT_CRIT_INFORMATIONAL:
                    $label = __('INFORMATIONAL');
                    $colors[] = COL_INFORMATIONAL;
                break;

                case EVENT_CRIT_MAJOR:
                    $label = __('MAJOR');
                    $colors[] = COL_MAJOR;
                break;

                case EVENT_CRIT_MINOR:
                    $label = __('MINOR');
                    $colors[] = COL_MINOR;
                break;

                case EVENT_CRIT_NORMAL:
                    $label = __('NORMAL');
                    $colors[] = COL_NORMAL;
                break;

                case EVENT_CRIT_WARNING:
                    $label = __('WARNING');
                    $colors[] = COL_WARNING;
                break;

                default:
                    $colors[] = COL_UNKNOWN;
                    $label = __('UNKNOWN');
                break;
            }

            $labels[] = $this->controlSizeText($label);
            $data[] = $row['total'];
        }

        $options = [
            'waterMark'    => false,
            'labels'       => $labels,
            'legend'       => ['display' => false],
            'cutout'       => 80,
            'nodata_image' => ['width' => '100%'],
            'colors'       => $colors,
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
     * Return the html graph of events in last 8h grouped by status validate.
     *
     * @return string
     */
    public function getEventsStatusValidateGraph():string
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

        if (users_can_manage_group_all() === true) {
            $id_groups[] = 0;
        }

        $id_groups = implode(',', $id_groups);
        $event_view_h = (int) ($config['event_view_hr'] > 24) ? 24 : $config['event_view_hr'];
        $time_events = ($event_view_h * 3600);
        $intervalh = (time() - $time_events);
        $sql = 'SELECT estado, count(*)  AS total
        FROM tevento
        WHERE utimestamp >= '.$intervalh.' AND id_grupo IN ('.$id_groups.')
        group by estado';

        $rows = db_process_sql($sql);

        $labels = [];
        $data = [];
        foreach ($rows as $key => $row) {
            switch ($row['estado']) {
                case '2':
                    $label = __('In process');
                break;

                case '0':
                    $label = __('New events');
                break;

                case '3':
                    $label = __('Not validated');
                break;

                case '1':
                    $label = __('Validated events');
                break;

                default:
                    $label = __('Unknow');
                break;
            }

            $labels[] = $label;
            $data[] = $row['total'];
        }

        $options = [
            'waterMark'    => false,
            'labels'       => $labels,
            'legend'       => ['display' => false],
            'cutout'       => 80,
            'nodata_image' => ['width' => '100%'],
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
     * Return the html graph of events in last 8h grouped by status.
     *
     * @return string
     */
    public function getEventsStatusGraph():string
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

        if (users_can_manage_group_all() === true) {
            $id_groups[] = 0;
        }

        $id_groups = implode(',', $id_groups);
        $event_view_h = (int) ($config['event_view_hr'] > 24) ? 24 : $config['event_view_hr'];
        $time_events = ($event_view_h * 3600);
        $intervalh = (time() - $time_events);
        $sql = 'SELECT criticity, count(*)  AS total
        FROM tevento
        WHERE utimestamp >= '.$intervalh.' AND id_grupo IN ('.$id_groups.')
        group by criticity';

        $rows = db_process_sql($sql);

        $labels = [];
        $data = [];
        $colors = [];
        foreach ($rows as $key => $row) {
            if ($row['criticity'] != EVENT_CRIT_CRITICAL
                && $row['criticity'] != EVENT_CRIT_NORMAL
                && $row['criticity'] != EVENT_CRIT_WARNING
            ) {
                continue;
            }

            switch ($row['criticity']) {
                case EVENT_CRIT_CRITICAL:
                    $label = __('CRITICAL');
                    $colors[] = COL_CRITICAL;
                break;

                case EVENT_CRIT_NORMAL:
                    $label = __('NORMAL');
                    $colors[] = COL_NORMAL;
                break;

                case EVENT_CRIT_WARNING:
                    $label = __('WARNING');
                    $colors[] = COL_WARNING;
                break;

                default:
                    // Nothing.
                break;
            }

            $labels[] = $this->controlSizeText($label);
            $data[] = $row['total'];
        }

        $options = [
            'labels'       => $labels,
            'legend'       => ['display' => false],
            'cutout'       => 80,
            'nodata_image' => ['width' => '100%'],
            'colors'       => $colors,
        ];

        // To avoid that if a value is too small it is not seen.
        $percentages = [];
        $total = array_sum($data);
        foreach ($data as $key => $value) {
            $percentage = (($value / $total) * 100);
            if ($percentage < 1 && $percentage > 0) {
                $percentage = 1;
            }

            $percentages[$key] = format_numeric($percentage, 0);
        }

        $data = $percentages;

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
     * Return the datatable events in last 8 hours.
     *
     * @return string
     */
    public function getDataTableEvents()
    {
        $column_names = [
            __('S'),
            __('Event'),
            __('Date'),
        ];

        $fields = [
            'mini_severity',
            'evento',
            'timestamp',
        ];
        return ui_print_datatable(
            [
                'id'                             => 'datatable_events',
                'class'                          => 'info_table events',
                'style'                          => 'width: 90%;',
                'ajax_url'                       => 'operation/events/events',
                'ajax_data'                      => [
                    'get_events'         => 1,
                    'compact_date'       => 1,
                    'external_url'       => 1,
                    'compact_name_event' => 1,
                ],
                'order'                          => [
                    'field'     => 'timestamp',
                    'direction' => 'desc',
                ],
                'column_names'                   => $column_names,
                'columns'                        => $fields,
                'ajax_return_operation'          => 'buffers',
                'ajax_return_operation_function' => 'process_buffers',
                'return'                         => true,
                'csv'                            => 0,
                'dom_elements'                   => 'tfp',
                'default_pagination'             => 8,
            ]
        );
    }


    /**
     * Check permission user for view events section.
     *
     * @return boolean
     */
    public function checkAcl():bool
    {
        global $config;
        $event_a = (bool) check_acl($config['id_user'], 0, 'ER');
        return $event_a;
    }


}
