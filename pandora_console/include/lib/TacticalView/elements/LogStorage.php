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
 * LogStorage, this class contain all logic for this section.
 */
class LogStorage extends Element
{


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->title = __('Log storage');
        $this->ajaxMethods = [
            'getStatus',
            'getTotalSources',
            'getStoredData',
            'getAgeOfStoredData',
        ];
        $this->interval = 300000;
        $this->refreshConfig = [
            'status'       => [
                'id'     => 'status-log-storage',
                'method' => 'getStatus',
            ],
            'total-source' => [
                'id'     => 'total-source-log-storage',
                'method' => 'getTotalSources',
            ],
            'total-lines'  => [
                'id'     => 'total-lines-log-storage',
                'method' => 'getStoredData',
            ],
            'age'          => [
                'id'     => 'age-of-stored',
                'method' => 'getAgeOfStoredData',
            ],
        ];
    }


    /**
     * Check if log storage module exist.
     *
     * @return boolean
     */
    public function isEnabled():bool
    {
        global $config;
        if ((bool) $config['log_collector'] === true) {
            return true;
        } else {
            return false;
        }
    }


     /**
      * Returns the html status of log storage.
      *
      * @return string
      */
    public function getStatus():string
    {
        $classDisabled = '';
        if ($this->isEnabled() === true) {
            $value = $this->valueMonitoring('Log server connection');
            $status = ((int) $value[0]['datos'] === 1) ? true : false;
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
        } else {
            $image_status = html_print_image('images/status_check@svg.svg', true);
            $text = html_print_div(
                [
                    'content' => __('Everything\'s OK!'),
                    'class'   => 'status-text',
                ],
                true
            );
            $classDisabled = 'alpha50';
        }

        $output = $image_status.$text;

        return html_print_div(
            [
                'content' => $output,
                'class'   => 'flex_center margin-top-5 '.$classDisabled,
                'style'   => 'margin: 0px 10px 10px 10px;',
                'id'      => 'status-log-storage',
            ],
            true
        );
    }


    /**
     * Returns the html of total sources in log storage.
     *
     * @return string
     */
    public function getTotalSources():string
    {
        if ($this->isEnabled() === true) {
            $data = $this->valueMonitoring('Total sources');
            $value = format_numeric($data[0]['datos']);
        } else {
            $value = __('N/A');
        }

        return html_print_div(
            [
                'content' => $value,
                'class'   => 'text-l',
                'style'   => 'margin: 0px 10px 0px 10px;',
                'id'      => 'total-source-log-storage',
            ],
            true
        );
    }


    /**
     * Returns the html of lines in log storage.
     *
     * @return string
     */
    public function getStoredData():string
    {
        if ($this->isEnabled() === true) {
            $data = $this->valueMonitoring('Total documents');
            $value = format_numeric($data[0]['datos']);
        } else {
            $value = __('N/A');
        }

        return html_print_div(
            [
                'content' => $value,
                'class'   => 'text-l',
                'style'   => 'margin: 0px 10px 0px 10px;',
                'id'      => 'total-lines-log-storage',
            ],
            true
        );
    }


    /**
     * Returns the html of age of stored data.
     *
     * @return string
     */
    public function getAgeOfStoredData():string
    {
        $data = $this->valueMonitoring('Longest data archived');
        $date = $data[0]['datos'];
        if ($date > 0 && $this->isEnabled() === true) {
            $interval = (time() - strtotime($date));
            $days = format_numeric(($interval / 86400), 0);
        } else {
            $days = 'N/A';
        }

        return html_print_div(
            [
                'content' => $days,
                'class'   => 'text-l',
                'style'   => 'margin: 0px 10px 0px 10px;',
                'id'      => 'age-of-stored',
            ],
            true
        );
    }


}
