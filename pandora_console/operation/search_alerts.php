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

global $config;

require_once 'include/functions_alerts.php';
require_once $config['homedir'].'/include/functions_agents.php';
require_once $config['homedir'].'/include/functions_modules.php';

$searchAlerts = check_acl($config['id_user'], 0, 'AR');

if ($alerts === false || $totalAlerts == 0 || !$searchAlerts) {
    echo "<br><div class='nf'>".__('Zero results found')."</div>\n";
} else {
    $table = new stdClass();
    $table->cellpadding = 4;
    $table->cellspacing = 4;
    $table->width = '98%';
    $table->class = 'databox';

    $table->head = [];
    $table->head[0] = ''.' '.'<a href="index.php?search_category=alerts&keywords='.$config['search_keywords'].'&head_search_keywords=abc&offset='.$offset.'&sort_field=disabled&sort=up">'.html_print_image('images/sort_up.png', true, ['style' => $selectDisabledUp]).'</a>'.'<a href="index.php?search_category=alerts&keywords='.$config['search_keywords'].'&head_search_keywords=abc&offset='.$offset.'&sort_field=disabled&sort=down">'.html_print_image('images/sort_down.png', true, ['style' => $selectDisabledDown]).'</a>';
    $table->head[1] = __('Agent').' '.'<a href="index.php?search_category=alerts&keywords='.$config['search_keywords'].'&head_search_keywords=abc&offset='.$offset.'&sort_field=agent&sort=up">'.html_print_image('images/sort_up.png', true, ['style' => $selectAgentUp]).'</a>'.'<a href="index.php?search_category=alerts&keywords='.$config['search_keywords'].'&head_search_keywords=abc&offset='.$offset.'&sort_field=agent&sort=down">'.html_print_image('images/sort_down.png', true, ['style' => $selectAgentDown]).'</a>';
    $table->head[2] = __('Module').' '.'<a href="index.php?search_category=alerts&keywords='.$config['search_keywords'].'&head_search_keywords=abc&offset='.$offset.'&sort_field=module&sort=up">'.html_print_image('images/sort_up.png', true, ['style' => $selectModuleUp]).'</a>'.'<a href="index.php?search_category=alerts&keywords='.$config['search_keywords'].'&head_search_keywords=abc&offset='.$offset.'&sort_field=module&sort=down">'.html_print_image('images/sort_down.png', true, ['style' => $selectModuleDown]).'</a>';
    $table->head[3] = __('Template').' '.'<a href="index.php?search_category=alerts&keywords='.$config['search_keywords'].'&head_search_keywords=abc&offset='.$offset.'&sort_field=template&sort=up">'.html_print_image('images/sort_up.png', true, ['style' => $selectTemplateUp]).'</a>'.'<a href="index.php?search_category=alerts&keywords='.$config['search_keywords'].'&head_search_keywords=abc&offset='.$offset.'&sort_field=template&sort=down">'.html_print_image('images/sort_down.png', true, ['style' => $selectTemplateDown]).'</a>';
    $table->head[4] = __('Action');

    $table->align = [];
    $table->align[0] = 'center';
    $table->align[1] = 'left';
    $table->align[2] = 'left';
    $table->align[3] = 'left';
    $table->align[4] = 'left';

    $table->headstyle = [];
    $table->headstyle[0] = 'text-align: center';
    $table->headstyle[1] = 'text-align: left';
    $table->headstyle[2] = 'text-align: left';
    $table->headstyle[3] = 'text-align: left';
    $table->headstyle[4] = 'text-align: left';

    $table->valign = [];
    $table->valign[0] = 'top';
    $table->valign[1] = 'top';
    $table->valign[2] = 'top';
    $table->valign[3] = 'top';
    $table->valign[4] = 'top';

    $table->data = [];
    foreach ($alerts as $alert) {
        if ($alert['disabled']) {
            $disabledCell = html_print_image('images/lightbulb_off.png', true, ['title' => 'disable', 'alt' => 'disable', 'class' => 'filter_none']);
        } else {
            $disabledCell = html_print_image('images/lightbulb.png', true, ['alt' => 'enable', 'title' => 'enable']);
        }

        $actionCell = '';
        if (strlen($alert['actions']) > 0) {
            $arrayActions = explode(',', $alert['actions']);
            $actionCell = '<ul class="action_list">';
            foreach ($arrayActions as $action) {
                $actionCell .= '<li><div><span class="action_name">'.$action.'</span></div><br /></li>';
            }

            $actionCell .= '</ul>';
        }


        array_push(
            $table->data,
            [
                $disabledCell,
                ui_print_agent_name($alert['id_agente'], true),
                $alert['module_name'],
                $alert['template_name'],
                $actionCell,
            ]
        );
    }

    echo '<br />';
    ui_pagination($totalAlerts);
    html_print_table($table);
    unset($table);
    ui_pagination($totalAlerts);
}
