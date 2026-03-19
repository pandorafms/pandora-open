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


/**
 * Generate a action button for submit the form.
 *
 * @param string  $action       Action to send in form.
 * @param string  $buttonAction Action of the button: Create, Update or Delete.
 * @param string  $tableWidth   Set the table width for the container.
 * @param boolean $return       If true, return a formed string.
 *
 * @return string
 */
function attachActionButton(
    string $action,
    string $buttonAction,
    string $tableWidth,
    bool $return=false,
    string $SelectAction=''
) {
    switch ($buttonAction) {
        case 'add':
            $caption = 'Add';
            $class = 'add';
        break;

        case 'copy':
            $caption = 'Copy';
            $class = 'wand';
        break;

        case 'create':
            $caption = 'Create';
            $class = 'upd';
        break;

        case 'update':
            $caption = 'Update';
            $class = 'upd';
        break;

        case 'delete':
            $caption = 'Delete';
            $class = 'delete';
        break;

        default:
            // Do none.
        break;
    }

    html_print_action_buttons(
        html_print_input_hidden(
            $action,
            1
        ).html_print_button(
            __($caption),
            'go',
            false,
            '',
            ['icon' => $class],
            true
        ),
        ['right_content' => $SelectAction],
        $return
    );
}


/**
 * Get table inputs for massive operation agents edit and delete.
 *
 * @param array $params Params.
 *
 * @return string Output.
 */
function get_table_inputs_masive_agents($params)
{
    global $config;

    $table = new stdClass;
    $table->id = 'delete_table';
    $table->class = 'databox filters filter-table-adv';
    $table->width = '100%';
    $table->data = [];
    $table->style = [];
    $table->style[0] = 'font-weight: bold;';
    $table->style[2] = 'font-weight: bold';
    $table->size = [];
    $table->size[0] = '50%';
    $table->size[1] = '50%';

    $table->data = [];
    $table->data[0][0] = html_print_label_input_block(
        __('Group'),
        html_print_select_groups(
            false,
            'AW',
            true,
            'id_group',
            $params['id_group'],
            false,
            '',
            '',
            true,
            false,
            false,
            '',
            false,
            'width:100%; max-width: 420px;'
        )
    );

    $table->data[0][1] = html_print_label_input_block(
        __('Group recursion'),
        html_print_checkbox(
            'recursion',
            1,
            $params['recursion'],
            true,
            false
        )
    );

    $status_list = [];
    $status_list[AGENT_STATUS_NORMAL] = __('Normal');
    $status_list[AGENT_STATUS_WARNING] = __('Warning');
    $status_list[AGENT_STATUS_CRITICAL] = __('Critical');
    $status_list[AGENT_STATUS_UNKNOWN] = __('Unknown');
    $status_list[AGENT_STATUS_NOT_NORMAL] = __('Not normal');
    $status_list[AGENT_STATUS_NOT_INIT] = __('Not init');
    $table->data[1][0] = html_print_label_input_block(
        __('Status'),
        html_print_select(
            $status_list,
            'status_agents',
            'selected',
            '',
            __('All'),
            AGENT_STATUS_ALL,
            true,
            false,
            true,
            '',
            false,
            'width:100%; max-width: 420px;'
        )
    );

    $table->data[1][1] = html_print_label_input_block(
        __('Show agents'),
        html_print_select(
            [
                0 => 'Only enabled',
                1 => 'Only disabled',
            ],
            'disabled',
            2,
            '',
            __('All'),
            2,
            true,
            false,
            true,
            '',
            false,
            'width:100%; max-width: 420px;'
        )
    );

    

    $os_list = os_get_os(true);

    $table->data[3][0] = html_print_label_input_block(
        __('OS'),
        html_print_select(
            $os_list,
            'os_agent',
            'selected',
            '',
            __('All'),
            '',
            true,
            false,
            true,
            '',
            false,
            'width:100%; max-width: 420px;'
        )
    );

    $table->data[3][1] = html_print_label_input_block(
        __('OS Version'),
        html_print_input_text(
            'os_agent_version',
            '',
            __('Select OS version'),
            35,
            255,
            true,
            false,
            false,
            '',
            'w100p'
        )
    );

    $label_agents = __('Agents');
    $label_agents .= '<span id="agent_loading" class="invisible">';
    $label_agents .= html_print_image('images/spinner.png', true);
    $label_agents .= '</span>';

    $agents = [];

        $agents = agents_get_group_agents(
            array_keys(users_get_groups($config['id_user'], 'AW', false)),
            ['disabled' => 2],
            'none'
        );
    

    $table->data[4][0] = html_print_label_input_block(
        $label_agents,
        html_print_select(
            $agents,
            'id_agents[]',
            0,
            false,
            '',
            '',
            true,
            true,
            true,
            '',
            false,
            'width: 100%; max-height: 100px',
            false,
            false,
            false,
            '',
            false,
            false,
            false,
            false,
            true,
            true,
            true
        )
    );

    $output = html_print_table($table, true);

    return $output;
}
