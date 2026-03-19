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

// Begin.
function forceExecution($id_group)
{
    global $config;

    include_once 'include/functions_alerts.php';
    $id_alert = (int) get_parameter('id_alert');
    alerts_agent_module_force_execution($id_alert);
}


function validateAlert($ids)
{
    if (!empty($ids)) {
        include_once 'include/functions_alerts.php';
        $result = alerts_validate_alert_agent_module($ids);

        return ui_print_result_message(
            $result,
            __('Alert(s) validated'),
            __('Error processing alert(s)'),
            '',
            true
        );
    } else {
        return ui_print_error_message(__('No alert selected'));
    }
}


function printFormFilterAlert(
    $id_group,
    $filter,
    $free_search,
    $alert_agent_view,
    $filter_standby=false,
    $tag_filter=false,
    $action_filter=false,
    $return=false,
    $strict_user=false,
    $access='AR',
    $search_sg=0
) {
    global $config;
    include_once $config['homedir'].'/include/functions_tags.php';

    $table = new StdClass();
    $table->width = '100%';
    $table->class = 'filter-table-adv p020';
    $table->size = [];
    $table->size[0] = '33%';
    $table->size[1] = '33%';
    $table->size[2] = '33%';
    $table->data = [];

    if ($alert_agent_view === false) {
        $table->data[0][0] = html_print_label_input_block(
            __('Group'),
            html_print_select_groups(
                $config['id_user'],
                $access,
                true,
                'ag_group',
                $id_group,
                '',
                '',
                '',
                true,
                false,
                false,
                '',
                false,
                '',
                false,
                false,
                'id_grupo',
                $strict_user
            )
        );
    }

    $alert_status_filter = [];
    $alert_status_filter['all_enabled'] = __('All (Enabled)');
    $alert_status_filter['all'] = __('All');
    $alert_status_filter['fired'] = __('Fired');
    $alert_status_filter['notfired'] = __('Not fired');
    $alert_status_filter['disabled'] = __('Disabled');

    $alert_standby = [];
    $alert_standby['1'] = __('Standby on');
    $alert_standby['0'] = __('Standby off');

    $table->data[0][1] = html_print_label_input_block(
        __('Status'),
        html_print_select(
            $alert_status_filter,
            'disabled',
            $filter,
            '',
            '',
            '',
            true,
            false,
            true,
            '',
            false,
            'width: 100%;'
        )
    );

    $tags = tags_get_user_tags();
    if (empty($tags) === true) {
        $callbackTag = html_print_input_text('tags', __('No tags'), '', 20, 40, true, true);
    } else {
        $callbackTag = html_print_select(
            $tags,
            'tag',
            $tag_filter,
            '',
            __('All'),
            '',
            true,
            false,
            true,
            '',
            false,
            'width: 100%;'
        );
    }

    $table->data[0][2] = html_print_label_input_block(
        __('Tags').ui_print_help_tip(__('Only it is show tags in use.'), true),
        $callbackTag
    );

    $table->data[3][0] = html_print_label_input_block(
        __('Also search in secondary groups'),
        html_print_checkbox_switch_extended('search_sg', 0, 0, false, '', '', true)
    );

    $table->data[2][0] = html_print_label_input_block(
        __('Free text for search').ui_print_help_tip(
            __('Filter by agent name, module name, template name or action name'),
            true
        ),
        html_print_input_text('free_search', $free_search, '', 20, 40, true)
    );

    $table->data[2][1] = html_print_label_input_block(
        __('Standby'),
        html_print_select(
            $alert_standby,
            'standby',
            $filter_standby,
            '',
            __('All'),
            '',
            true,
            false,
            true,
            '',
            false,
            'width: 100%;'
        )
    );

    $alert_action = alerts_get_alert_actions_filter();
    $table->data[2][2] = html_print_label_input_block(
        __('Action'),
        html_print_select(
            $alert_action,
            'action',
            $action_filter,
            '',
            __('All'),
            '',
            true,
            false,
            true,
            '',
            false,
            'width: 100%;'
        )
    );

    $data = html_print_table($table, true);

    if ($return) {
        return $data;
    } else {
        echo $data;
    }
}
