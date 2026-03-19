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
 * ScheduledDowntime, this class contain all logic for this section.
 */
class ScheduledDowntime extends Element
{


    /**
     * Constructor
     */
    public function __construct()
    {
        global $config;
        parent::__construct();
        ui_require_javascript_file('pandora_planned_downtimes');
        include_once $config['homedir'].'/include/functions_reporting.php';
        $this->title = __('Scheduled Downtime');
        $this->ajaxMethods = ['getScheduleDowntime'];
    }


    /**
     * List all schedule downtime.
     *
     * @return string
     */
    public function list():string
    {
        $columns = [
            'name',
            'configuration',
            'running',
            'affected',
        ];

        $columnNames = [
            __('Name #Ag.'),
            __('Configuration'),
            __('Running'),
            __('Affected'),
        ];

        return ui_print_datatable(
            [
                'id'                  => 'list_downtime',
                'class'               => 'info_table',
                'style'               => 'width: 90%',
                'dom_elements'        => 'tfp',
                'filter_main_class'   => 'box-flat white_table_graph fixed_filter_bar',
                'columns'             => $columns,
                'column_names'        => $columnNames,
                'ajax_url'            => $this->ajaxController,
                'no_sortable_columns' => [
                    1,
                    2,
                ],
                'ajax_data'           => [
                    'method' => 'getScheduleDowntime',
                    'class'  => static::class,
                ],
                'order'               => [
                    'field'     => 'name',
                    'direction' => 'asc',
                ],
                'default_pagination'  => 5,
                'search_button_class' => 'sub filter float-right',
                'return'              => true,
            ]
        );
    }


    /**
     * Return the schedule downtime for datatable by ajax.
     *
     * @return void
     */
    public function getScheduleDowntime():void
    {
        global $config;

        $start  = get_parameter('start', 0);
        $length = get_parameter('length', $config['block_size']);
        $orderDatatable = get_datatable_order(true);
        $pagination = '';
        $order = '';

        try {
            ob_start();
            if (isset($orderDatatable)) {
                $order = sprintf(
                    ' ORDER BY %s %s',
                    $orderDatatable['field'],
                    $orderDatatable['direction']
                );
            }

            if (isset($length) && $length > 0
                && isset($start) && $start >= 0
            ) {
                $pagination = sprintf(
                    ' LIMIT %d OFFSET %d ',
                    $length,
                    $start
                );
            }

            $columns = [
                'id',
                'name',
                'description',
                'date_from',
                'date_to',
                'executed',
                'id_group',
                'only_alerts',
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
                'saturday',
                'sunday',
                'periodically_time_from',
                'periodically_time_to',
                'periodically_day_from',
                'periodically_day_to',
                'type_downtime',
                'type_execution',
                'type_periodicity',
                'id_user',
                'cron_interval_from',
                'cron_interval_to',
            ];
            if (isset($config['user']) === false) {
                $config['user'] = '';
            }

            $groups = implode(',', array_keys(users_get_groups($config['user'])));
            $columns_str = implode(',', $columns);
            $sql = sprintf(
                'SELECT %s
                FROM tplanned_downtime
                WHERE id_group IN (%s)
                %s %s',
                $columns_str,
                $groups,
                $order,
                $pagination,
            );

            $sql_count = 'SELECT COUNT(id) AS num
                            FROM tplanned_downtime';

            $downtimes = db_get_all_rows_sql($sql);
            if ($downtimes !== false) {
                foreach ($downtimes as $key => $downtime) {
                    if ((int) $downtime['executed'] === 0) {
                        $downtimes[$key]['running'] = html_print_div(
                            [
                                'content' => '',
                                'class'   => 'square stop',
                                'title'   => 'Not running',
                            ],
                            true
                        );
                    } else {
                        $downtimes[$key]['running'] = html_print_div(
                            [
                                'content' => '',
                                'class'   => 'square running',
                                'title'   => 'Running',
                            ],
                            true
                        );
                    }

                    $downtimes[$key]['configuration'] = reporting_format_planned_downtime_dates($downtime);

                    $settings = [
                        'url'         => ui_get_full_url('ajax.php', false, false, false),
                        'loadingText' => __('Loading, this operation might take several minutes...'),
                        'title'       => __('Elements affected'),
                        'id'          => $downtime['id'],
                    ];

                    $downtimes[$key]['affected'] = '<a style="margin-left: 22px;" href="javascript:" onclick=\'dialogAgentModulesAffected('.json_encode($settings).')\'>';
                    $downtimes[$key]['affected'] .= html_print_image(
                        'images/details.svg',
                        true,
                        [
                            'title' => __('Agents and modules affected'),
                            'class' => 'main_menu_icon invert_filter',
                        ]
                    );
                    $downtimes[$key]['affected'] .= '</a>';
                }
            }

            $downtimes_number_res = db_get_all_rows_sql($sql_count);
            $downtimes_number = ($downtimes_number_res !== false) ? $downtimes_number_res[0]['num'] : 0;

            if (empty($downtimes) === true) {
                $downtimes = [];
            }

            echo json_encode(
                [
                    'data'            => $downtimes,
                    'recordsTotal'    => $downtimes_number,
                    'recordsFiltered' => $downtimes_number,
                ]
            );
            // Capture output.
            $response = ob_get_clean();
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }

        json_decode($response);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo $response;
        } else {
            echo json_encode(
                [
                    'success' => false,
                    'error'   => $response,
                ]
            );
        }

        exit;
    }


    /**
     * Check permission acl for this section.
     *
     * @return boolean
     */
    public function checkAcl():bool
    {
        global $config;
        $read_permisson = (bool) check_acl($config['id_user'], 0, 'AR');
        if ($read_permisson === true) {
            return true;
        } else {
            return false;
        }
    }


}
