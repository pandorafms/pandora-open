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

namespace PandoraFMS\TacticalView;

/**
 * Parent element for general tactical view elements
 */
class Element
{

    /**
     * Url of controller.
     *
     * @var string
     */
    public $ajaxController;

    /**
     * List of available ajax methods.
     *
     * @var array
     */
    protected $ajaxMethods = [];

    /**
     * Title of section
     *
     * @var string
     */
    public $title;

    /**
     * Interval for refresh element, 0 for not refresh.
     *
     * @var integer
     */
    public $interval;

    /**
     * Agent of automonitoritation
     *
     * @var array
     */
    protected $monitoringAgent;

    /**
     * Refresh config for async method.
     *
     * @var array
     */
    public $refreshConfig = [];


    /**
     * Contructor
     *
     * @param string $ajax_controller Controller.
     */
    public function __construct(
        $ajax_controller='include/ajax/general_tactical_view.ajax'
    ) {
        global $config;
        $this->interval = 0;
        $this->title = __('Default element');
        $this->ajaxController = $ajax_controller;
        // Without ACL.
        $agent_name = $config['self_monitoring_agent_name'];
        if (empty($agent_name) === true) {
            $agent_name = 'pandora.internals';
        }

        $agent = db_get_row('tagente', 'nombre', $agent_name, '*');
        if (is_array($agent) === true) {
            $this->monitoringAgent = $agent;
        }

        /*
            // With ACL.
            $agent = agents_get_agents(['nombre' => 'pandora.internals']);
            if (is_array($agent) === true && count($agent) > 0) {
                $this->monitoringAgent = $agent[0];
            }
        */
    }


    /**
     * Return error message to target.
     *
     * @param string $msg Error message.
     *
     * @return void
     */
    public static function error(string $msg)
    {
        echo json_encode(['error' => $msg]);
    }


    /**
     * Verifies target method is allowed to be called using AJAX call.
     *
     * @param string $method Method to be invoked via AJAX.
     *
     * @return boolean Available (true), or not (false).
     */
    public function ajaxMethod(string $method):bool
    {
        return in_array($method, $this->ajaxMethods) === true;
    }


    /**
     * Cut the text to display it on the labels.
     *
     * @param string  $text   Text for cut.
     * @param integer $length Length max for text cutted.
     *
     * @return string
     */
    protected function controlSizeText(string $text, int $length=14):string
    {
        if (mb_strlen($text) > $length) {
            $newText = mb_substr($text, 0, $length).'...';
            return $newText;
        } else {
            return $text;
        }
    }


    /**
     * Return a valur from Module of monitoring.
     *
     * @param string  $moduleName Name of module value.
     * @param integer $dateInit   Date init for filter.
     * @param integer $dateEnd    Date end for filter.
     *
     * @return array Array of module data.
     */
    protected function valueMonitoring(string $moduleName, int $dateInit=0, int $dateEnd=0):array
    {
        if (empty($this->monitoringAgent) === false) {
            $module = modules_get_agentmodule_id(io_safe_input($moduleName), $this->monitoringAgent['id_agente']);
            if (is_array($module) === true && key_exists('id_agente_modulo', $module) === true) {
                if ($dateInit === 0 && $dateEnd === 0) {
                    $value = modules_get_last_value($module['id_agente_modulo']);
                    $rawData = [['datos' => $value]];
                } else {
                    $rawData = modules_get_raw_data($module['id_agente_modulo'], $dateInit, $dateEnd);
                }

                if ($rawData === false || is_array($rawData) === false) {
                    return [['datos' => 0]];
                } else {
                    return $rawData;
                }
            } else {
                return [['datos' => 0]];
            }

            return [['datos' => 0]];
        } else {
            return [['datos' => 0]];
        }
    }


    /**
     * Simple image loading for async functions.
     *
     * @return string
     */
    public static function loading():string
    {
        return html_print_div(
            [
                'content' => '<span></span>',
                'class'   => 'spinner-fixed inherit',
            ],
            true
        );
    }


    /**
     * Return the name of class
     *
     * @return string
     */
    public static function nameClass():string
    {
        return static::class;
    }


}
