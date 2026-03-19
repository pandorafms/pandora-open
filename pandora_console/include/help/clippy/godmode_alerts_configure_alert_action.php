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
    // Help tour about the email alert module (step 3)
    // ------------------------------------------------------------------
    $return_tours['tours']['email_alert_module_step_3'] = [];
    $return_tours['tours']['email_alert_module_step_3']['steps'] = [];
    $return_tours['tours']['email_alert_module_step_3']['steps'][] = [
        'element' => 'input[name="name"]',
        'intro'   => __('Fill the name of your action.'),
    ];
    $return_tours['tours']['email_alert_module_step_3']['steps'][] = [
        'element' => 'select[name="group"]',
        'intro'   => __('Select the group in the drop-down list and filter for ACL (the user in this group can use your action to create an alert).'),
    ];
    $return_tours['tours']['email_alert_module_step_3']['steps'][] = [
        'element' => 'select[name="id_command"]',
        'intro'   => __('In the command field select "email".'),
    ];
    $return_tours['tours']['email_alert_module_step_3']['steps'][] = [
        'element' => 'input[name="action_threshold"]',
        'intro'   => __('In the threshold field enter the seconds. The help icon show more information.').'<br />'.ui_print_help_icon('action_threshold', true, '', 'images/help.png'),
    ];
    $return_tours['tours']['email_alert_module_step_3']['steps'][] = [
        'element'  => '#table_macros',
        'position' => 'bottom',
        'intro'    => __('In the first field enter the email address/addresses where you want to receive the email alerts separated with comas ( , ) or white spaces.'),
    ];
    $return_tours['tours']['email_alert_module_step_3']['steps'][] = [
        'element'  => '#table_macros',
        'position' => 'bottom',
        'intro'    => __('In the "Subject"  field  you can use the macros _agent_ or _module_ for each name.'),
    ];
    $return_tours['tours']['email_alert_module_step_3']['steps'][] = [
        'element'  => '#table_macros',
        'position' => 'bottom',
        'intro'    => __('In the text field, you can also use macros. Get more information about the macros by clicking on the help icon.').'<br />'.ui_print_help_icon('alert_config', true, '', 'images/help.png'),
    ];
    $return_tours['tours']['email_alert_module_step_3']['steps'][] = [
        'element'  => 'input[name="create"]',
        'position' => 'left',
        'intro'    => __('Click on Create button to create the action.'),
    ];
    $return_tours['tours']['email_alert_module_step_3']['conf'] = [];
    $return_tours['tours']['email_alert_module_step_3']['conf']['show_bullets'] = 0;
    $return_tours['tours']['email_alert_module_step_3']['conf']['show_step_numbers'] = 0;
    $return_tours['tours']['email_alert_module_step_3']['conf']['next_help'] = 'email_alert_module_step_4';
    // ==================================================================
    return $return_tours;
}
