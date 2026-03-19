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
 * @subpackage Clippy
 */


function clippy_start_page()
{
    $return_tours = [];
    $return_tours['first_step_by_default'] = false;
    $return_tours['tours'] = [];

    // ==================================================================
    // Help tour about the email alert module (step 2)
    // ------------------------------------------------------------------
    $return_tours['tours']['email_alert_module_step_2'] = [];
    $return_tours['tours']['email_alert_module_step_2']['steps'] = [];
    $return_tours['tours']['email_alert_module_step_2']['steps'][] = [
        'element'  => 'input[name="create"]',
        'position' => 'left',
        'intro'    => __('Let me show you how to create an email action: Click on Create button and fill the form showed in the following screen.'),
    ];
    $return_tours['tours']['email_alert_module_step_2']['conf'] = [];
    $return_tours['tours']['email_alert_module_step_2']['conf']['show_bullets'] = 0;
    $return_tours['tours']['email_alert_module_step_2']['conf']['show_step_numbers'] = 0;
    $return_tours['tours']['email_alert_module_step_2']['conf']['next_help'] = 'email_alert_module_step_3';
    // ==================================================================
    // ==================================================================
    // Help tour about the email alert module (step 4)
    // ------------------------------------------------------------------
    $return_tours['tours']['email_alert_module_step_4'] = [];
    $return_tours['tours']['email_alert_module_step_4']['steps'] = [];
    $return_tours['tours']['email_alert_module_step_4']['steps'][] = [
        'element' => '#clippy',
        'intro'   => __('Now, you have to go to the monitors list and look for a critical module to apply the alert.'),
    ];
    $return_tours['tours']['email_alert_module_step_4']['steps'][] = [
        'element'  => '#icon_oper-agents',
        'position' => 'right',
        'intro'    => __('Click on the arrow to drop down the Monitoring submenu and select Monitor Detail.'),
    ];
    $return_tours['tours']['email_alert_module_step_4']['conf'] = [];
    $return_tours['tours']['email_alert_module_step_4']['conf']['show_bullets'] = 0;
    $return_tours['tours']['email_alert_module_step_4']['conf']['show_step_numbers'] = 0;
    $return_tours['tours']['email_alert_module_step_4']['conf']['next_help'] = 'email_alert_module_step_5';
    // ==================================================================
    return $return_tours;
}
