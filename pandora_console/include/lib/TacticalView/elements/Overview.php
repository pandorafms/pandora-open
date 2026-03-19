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
 * Overview, this class contain all logic for this section.
 */
class Overview extends Element
{


    /**
     * Constructor
     */
    public function __construct()
    {
        global $config;
        parent::__construct();
        include_once $config['homedir'].'/include/graphs/fgraph.php';
        if (is_ajax() === true) {
            include_once $config['homedir'].'/include/functions_servers.php';
        }

        $this->title = __('General overview');
        $this->ajaxMethods = [
            'getLogSizeStatus',
            'getServerStatus',
            'getCPULoadGraph',
        ];
        $this->interval = 300000;
        $this->refreshConfig = [
            'logSizeStatus' => [
                'id'     => 'status-log-size',
                'method' => 'getLogSizeStatus',
            ],
            'ServerStatus'  => [
                'id'     => 'status-servers',
                'method' => 'getServerStatus',
            ],
            'cpuStatus'     => [
                'id'     => 'status-cpu',
                'method' => 'getCPULoadGraph',
            ],
        ];
    }


    /**
     * Return the html log size status.
     *
     * @return string
     */
    public function getLogSizeStatus():string
    {
        $size = $this->valueMonitoring('console_log_size');
        $status = ($size[0]['datos'] < 1000) ? true : false;

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
                    'content' => __('Too size log size'),
                    'class'   => 'status-text',
                ],
                true
            );
        }

        $output = $image_status.$text;

        return html_print_div(
            [
                'content' => $output,
                'class'   => 'margin-top-5 flex_center',
                'id'      => 'status-log-size',
            ],
            true
        );

    }


    /**
     * Return the html Servers status.
     *
     * @return string
     */
    public function getServerStatus():string
    {
        $status = check_all_servers_up();

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

        $output = '<a href="index.php?sec=gservers&sec2=godmode/servers/modificar_server" class="flex_center">'.$image_status.$text.'</a>';

        return html_print_div(
            [
                'content' => $output,
                'class'   => 'margin-top-5',
                'id'      => 'status-servers',
            ],
            true
        );

    }


    /**
     * Returns the html of the used licenses.
     *
     * @return string
     */
    public function getLicenseUsageGraph():string
    {
        $agents = agents_get_agents();
        $enabled_agents = agents_get_agents(
            false,
            false,
            'AR',
            [
                'field' => 'nombre',
                'order' => 'ASC',
            ],
            false,
            1
        );
        if (is_array($agents) === true) {
            $total = count($agents);
        } else {
            $total = 0;
        }

        if ($total > 0 && is_array($enabled_agents) === true) {
            $total_disabled_agents = round((($total - count($enabled_agents)) * 100) / $total);
            $total_enabled_agents = round((count($enabled_agents) * 100) / $total);
        } else {
            $total_disabled_agents = 100;
            $total_enabled_agents = 0;
        }

        if ($total_enabled_agents > 0) {
            $data['agents_enabled'] = [
                'label' => __('% Agents enabled'),
                'perc'  => $total_enabled_agents,
                'color' => '#1C4E6B',
            ];
        }

        if ($total_disabled_agents > 0) {
            $data['agents_disabled'] = [
                'label' => __('% Agents disabled'),
                'perc'  => $total_disabled_agents,
                'color' => '#5C63A2',
            ];
        }

        $bar = $this->printHorizontalBar($data);
        $output = html_print_div(
            [
                'content' => $bar,
                'style'   => 'margin: 0 auto;',
            ],
            true
        );

        return $output;
    }


    /**
     * Print horizontal bar divided by percentage.
     *
     * @param array $data Required [perc, color, label].
     *
     * @return string
     */
    private function printHorizontalBar(array $data):string
    {
        $output = '<div id="horizontalBar">';
        $output .= '<div class="labels">';
        foreach ($data as $key => $value) {
            $output .= html_print_div(
                [
                    'content' => '<div style="background: '.$value['color'].'"></div><span>'.$value['label'].'</span>',
                    'class'   => 'label',
                ],
                true
            );
        }

        $output .= '</div>';
        $output .= '<div class="bar">';
        foreach ($data as $key => $value) {
            $output .= html_print_div(
                [
                    'content' => $value['perc'].' %',
                    'style'   => 'width: '.$value['perc'].'%; background-color: '.$value['color'].';',
                ],
                true
            );
        }

        $output .= '</div>';
        $output .= '
            <div class="marks">
            <div class="mark"><div class="line mark0"></div><span class="number">0 %</span></div>
            <div class="mark"><div class="line mark20"></div><span class="number number20">20 %</span></div>
            <div class="mark"><div class="line mark40"></div><span class="number number40">40 %</span></div>
            <div class="mark"><div class="line mark60"></div><span class="number number60">60 %</span></div>
            <div class="mark"><div class="line mark80"></div><span class="number number80">80 %</span></div>
            <div class="mark"><div class="line mark100"></div><span class="number number100">100 %</span></div>
            </div>';
        $output .= '</div>';

        return $output;
    }


    /**
     * Returns the html of a graph with the cpu load.
     *
     * @return string
     */
    public function getCPULoadGraph():string
    {
        $data_last24h = $this->valueMonitoring('CPU Load', (time() - 86400), time());
        $dates = [];
        $cpu_load = [];
        foreach ($data_last24h as $key => $raw_data) {
            $dates[] = date('H:m:s', $raw_data['utimestamp']);
            $cpu_load[] = $raw_data['datos'];
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
                'data'                  => $cpu_load,
            ],
        ];

        $graph_area = html_print_div(
            [
                'content' => line_graph($data, $options),
                'class'   => 'margin-top-5 w100p h100p',
                'style'   => 'max-height: 50px;',
                'id'      => 'status-cpu',
            ],
            true
        );

        $output = $graph_area;

        return $output;
    }


}
