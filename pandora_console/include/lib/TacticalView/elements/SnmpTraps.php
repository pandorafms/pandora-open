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
 * SnmpTraps, this class contain all logic for this section.
 */
class SnmpTraps extends Element
{


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->title = __('SNMP Traps');
        $this->ajaxMethods = [
            'getQueues',
            'getTotalSources',
        ];
        $this->interval = 300000;
        $this->refreshConfig = [
            'queues'     => [
                'id'     => 'total-queues',
                'method' => 'getQueues',
            ],
            'total-snmp' => [
                'id'     => 'total-snmp',
                'method' => 'getTotalSources',
            ],
        ];
    }


    /**
     * Check if snmp traps module exist.
     *
     * @return boolean
     */
    public function isEnabled():bool
    {
        if (empty($this->monitoringAgent) === true) {
            return false;
        }

        $existModule = modules_get_agentmodule_id(io_safe_input('snmp_trap_queue'), $this->monitoringAgent['id_agente']);
        if ($existModule === false) {
            return false;
        } else {
            return true;
        }
    }


    /**
     * Returns the html of queues traps.
     *
     * @return string
     */
    public function getQueues():string
    {
        if ($this->isEnabled() === true) {
            $value = $this->valueMonitoring('snmp_trap_queue');
            if (isset($value[0]['data']) === true) {
                $total = round($value[0]['data']);
            } else {
                $total = __('N/A');
            }
        } else {
            $total = __('N/A');
        }

        return html_print_div(
            [
                'content' => $total,
                'class'   => 'text-l',
                'style'   => 'margin: 0px 10px 10px 10px;',
                'id'      => 'total-queues',
            ],
            true
        );
    }


    /**
     * Returns the html of total sources traps.
     *
     * @return string
     */
    public function getTotalSources():string
    {
        if ($this->isEnabled() === true) {
            $value = $this->valueMonitoring('total_trap');
            if (isset($value[0]['data']) === true) {
                $total = round($value[0]['data']);
            } else {
                $total = __('N/A');
            }
        } else {
            $total = __('N/A');
        }

        return html_print_div(
            [
                'content' => $total,
                'class'   => 'text-l',
                'style'   => 'margin: 0px 10px 10px 10px;',
                'id'      => 'total-snmp',
            ],
            true
        );
    }


}
