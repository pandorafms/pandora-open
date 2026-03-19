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
 * @subpackage UI
 */

require_once $config['homedir'].'/include/functions_groups.php';


/**
 */
function renders_agent_field($agent, $field, $field_value=false, $return=false)
{
    global $config;

    if (empty($agent)) {
        return '';
    }

    $output = '';
    switch ($field) {
        case 'group_name':
            if (! isset($agent['id_grupo'])) {
                return '';
            }

            $output = groups_get_name($agent['id_grupo'], true);

        break;

        case 'group_icon':
            if (! isset($agent['id_grupo'])) {
                return '';
            }

            $output = ui_print_group_icon($agent['id_grupo'], true);

        break;

        case 'group':
            if (! isset($agent['id_grupo'])) {
                return '';
            }

            $output = ui_print_group_icon($agent['id_grupo'], true);
            $output .= ' ';
            $output .= groups_get_name($agent['id_grupo']);

        break;

        case 'view_link':
            if (! isset($agent['nombre'])) {
                return '';
            }

            if (! isset($agent['id_agente'])) {
                return '';
            }

            $output = '<a class="agent_link" id="agent-'.$agent['id_agente'].'" href="index.php?sec=estado&sec2=operation/agentes/ver_agente&id_agente='.$agent['id_agente'].'">';
            $output .= $agent['nombre'];
            $output .= '</a>';

        break;

        case 'name':
            if (! isset($agent['nombre'])) {
                return '';
            }

            $output = $agent['nombre'];

        break;

        case 'status':
            if (! isset($agent['id_agente'])) {
                return ui_print_status_image(STATUS_AGENT_NO_DATA, '', $return);
            }

            include_once 'include/functions_reporting.php';
            $info = reporting_get_agent_module_info($agent['id_agente']);
            $output = $info['status_img'];

        break;

        case 'ajax_link':
            if (! $field_value || ! is_array($field_value)) {
                return '';
            }

            if (! isset($field_value['callback'])) {
                return '';
            }

            if (! isset($agent['id_agente'])) {
                return '';
            }

            $parameters = $agent['id_agente'];
            if (isset($field_value['parameters'])) {
                $parameters = implode(',', $field_value['parameters']);
            }

            $text = __('Action');
            if (isset($field_value['name'])) {
                $text = $field_value['name'];
            }

            if (isset($field_value['image'])) {
                $text = html_print_image($field_value['image'], true, ['title' => $text]);
            }

            $output = '<a href="#" onclick="'.$field_value['callback'].'(this, '.$parameters.'); return false"">';
            $output .= $text;
            $output .= '</a>';

        break;

        default:
            if (! isset($agent[$field])) {
                return '';
            }

            $ouput = $agent[$field];
    }

    if ($return) {
        return $output;
    }

    echo $output;
}
