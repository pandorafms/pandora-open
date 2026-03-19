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
 * Configurations, this class contain all logic for this section.
 */
class Configurations extends Element
{


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->title = __('Configurations');
    }


    /**
     * Get total groups from automonitorization.
     *
     * @return string
     */
    public function getTotalGroups():string
    {
        $value = $this->valueMonitoring('total_groups');
        $total = round($value[0]['datos']);
        $image = html_print_image('images/Tactical_Groups.svg', true);
        $text = '<span class="subtitle">'.__('Groups').'</span>';
        $number = html_print_div(
            [
                'content' => format_numeric($total, 0),
                'class'   => 'text-l text_center',
                'style'   => '',
            ],
            true
        );
        $output = $image.$text.$number;
        return $output;
    }


    /**
     * Get total modules from automonitorization.
     *
     * @return string
     */
    public function getTotalModules():string
    {
        $value = $this->valueMonitoring('total_modules');
        $total = round($value[0]['datos']);
        $image = html_print_image('images/Tactical_Modules.svg', true);
        $text = '<span class="subtitle">'.__('Modules').'</span>';
        $number = html_print_div(
            [
                'content' => format_numeric($total, 0),
                'class'   => 'text-l text_center',
                'style'   => '',
            ],
            true
        );
        $output = $image.$text.$number;
        return $output;
    }


    /**
     * Get total remote plugins from automonitorization.
     *
     * @return string
     */
    public function getTotalRemotePlugins():string
    {
        $totalPLugins = db_get_value(
            'count(*)',
            'tplugin',
            'plugin_type',
            1,
        );

        $sql = 'SELECT count(*) AS total FROM tplugin WHERE plugin_type = 1;';
        $rows = db_process_sql($sql);
        $totalPLugins = 0;
        if (is_array($rows) === true && count($rows) > 0) {
            $totalPLugins = $rows[0]['total'];
        }

        $image = html_print_image('images/Tactical_Plugins.svg', true);
        $text = '<span class="subtitle">'.__('Remote plugins').'</span>';
        $number = html_print_div(
            [
                'content' => format_numeric($totalPLugins, 0),
                'class'   => 'text-l text_center',
                'style'   => '',
            ],
            true
        );
        $output = $image.$text.$number;
        return $output;
    }


    /**
     * Get total module templates from automonitorization.
     *
     * @return string
     */
    public function getTotalModuleTemplate():string
    {
        $countModuleTemplates = db_get_value(
            'count(*)',
            'tnetwork_profile'
        );

        $image = html_print_image('images/Tactical_Module_template.svg', true);
        $text = '<span class="subtitle">'.__('Module templates').'</span>';
        $number = html_print_div(
            [
                'content' => format_numeric($countModuleTemplates, 0),
                'class'   => 'text-l text_center',
                'style'   => '',
            ],
            true
        );
        $output = $image.$text.$number;
        return $output;
    }


    /**
     * Get total not unit modules from automonitorization.
     *
     * @return string
     */
    public function getNotInitModules():string
    {
        $value = $this->valueMonitoring('total_notinit');
        $total = round($value[0]['datos']);
        $image = html_print_image('images/Tactical_Not_init_module.svg', true);
        $text = '<span class="subtitle">'.__('Not-init modules').'</span>';
        $number = html_print_div(
            [
                'content' => format_numeric($total, 0),
                'class'   => 'text-l text_center',
                'style'   => '',
            ],
            true
        );
        $output = $image.$text.$number;
        return $output;
    }


    /**
     * Get total unknow agents from automonitorization.
     *
     * @return string
     */
    public function getTotalUnknowAgents():string
    {
        $value = $this->valueMonitoring('total_unknown');
        $total = round($value[0]['datos']);
        $image = html_print_image('images/Tactical_Unknown_agent.svg', true);
        $text = '<span class="subtitle">'.__('Unknown agents').'</span>';
        $number = html_print_div(
            [
                'content' => format_numeric($total, 0),
                'class'   => 'text-l text_center',
                'style'   => '',
            ],
            true
        );
        $output = $image.$text.$number;
        return $output;
    }


    /**
     * Returns the html of total events.
     *
     * @return string
     */
    public function getTotalEvents():string
    {
        $data = $this->valueMonitoring('last_events_24h');
        $total = $data[0]['datos'];
        $image = html_print_image('images/system_event.svg', true);
        $text = '<span class="subtitle">'.__('Events in last 24 hrs').'</span>';
        $number = html_print_div(
            [
                'content' => format_numeric($total, 0),
                'class'   => 'text-l text_center',
                'style'   => '',
            ],
            true
        );
        $output = $image.$text.$number;
        return $output;
    }


}
