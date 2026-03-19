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

require_once 'include/functions_agents.php';

$disabledBecauseInPolicy = false;
$disabledTextBecauseInPolicy = '';
$page = get_parameter('page', '');
$id_agente = get_parameter('id_agente', '');
$agent_name = get_parameter('agent_name', agents_get_alias($id_agente));
$id_agente_modulo = get_parameter('id_agent_module', 0);
$custom_integer_2 = get_parameter('custom_integer_2', 0);
$policy = false;

$sql = 'SELECT *
    FROM tagente_modulo
    WHERE id_agente_modulo = '.$id_agente_modulo;

$row = db_get_row_sql($sql);
$is_service = false;
$is_synthetic = false;
$is_synthetic_avg = false;
$ops = false;
if ($row !== false && is_array($row) === true) {
    $prediction_module = $row['prediction_module'];
    $custom_integer_1 = $row['custom_integer_1'];
    $custom_integer_2 = $row['custom_integer_2'];
    $custom_string_1 = $row['custom_string_1'];
    $custom_integer_2 = $row['custom_integer_2'];

    switch ((int) $prediction_module) {
        case MODULE_PREDICTION_SERVICE:
            $selected = 'service_selected';
            $custom_integer_2 = 0;
        break;

        case MODULE_PREDICTION_TRENDING:
            $selected = 'trending_selected';
            $prediction_module = $custom_integer_1;

        break;

        case MODULE_PREDICTION_PLANNING:
            $selected = 'capacity_planning';
            $prediction_module = $custom_integer_1;
            $estimation_interval = $custom_string_1;
            $estimation_type = $custom_string_2;
        break;

        default:
            $prediction_module = $custom_integer_1;
        break;
    }
} else {
    $selected = 'capacity_planning';
    $custom_integer_1 = 0;
}

if (strstr($page, 'policy_modules') === false) {
    $disabledBecauseInPolicy = false;

    if ($disabledBecauseInPolicy) {
        $disabledTextBecauseInPolicy = 'disabled = "disabled"';
    }
}

$extra_title = __('Prediction server module');

$data = [];
$data[0] = __('Source module');
$data[0] .= ui_print_help_icon('prediction_source_module', true);
push_table_simple($data, 'caption_module_service_synthetic_selector');

$data = [];

$data[0] = __('Module');
$data[1] = __('Period');

$table_simple->cellclass['caption_prediction_module'][0] = 'w33p';
$table_simple->cellclass['caption_prediction_module'][1] = 'w33p';
$table_simple->cellclass['caption_prediction_module'][2] = 'w33p';
push_table_simple($data, 'caption_prediction_module');

$data = [];
// Get module and agent of the target prediction module.
if (empty($prediction_module) === false) {
    $id_agente_clean = modules_get_agentmodule_agent($prediction_module);
    $prediction_module_agent = modules_get_agentmodule_agent_name($prediction_module);
    $agent_name_clean = $prediction_module_agent;
    $agent_alias = agents_get_alias($id_agente_clean);
} else {
    $id_agente_clean = 0;
    $agent_name_clean = '';
    $agent_alias = '';
}

$params = [];
$params['return'] = true;
$params['show_helptip'] = true;
$params['input_name'] = 'agent_name';
$params['value'] = $agent_alias;
$params['javascript_is_function_select'] = true;
$params['selectbox_id'] = 'prediction_module';
$params['none_module_text'] = __('Select Module');
$params['use_hidden_input_idagent'] = true;
$params['input_style'] = 'width: 100%;';
$params['hidden_input_idagent_id'] = 'hidden-id_agente_module_prediction';

if (strstr($page, 'policy_modules') === false) {
    $modules = agents_get_modules($id_agente);

    $predictionModuleInput = html_print_select(
        $modules,
        'prediction_module',
        $prediction_module,
        '',
        '',
        0,
        true,
        false,
        true,
        '',
        false,
        false,
        false,
        false,
        false,
        '',
        false,
        false,
        false,
        false,
        true,
        false,
        false,
        '',
        false,
        'pm'
    );
} else {
    $modules = index_array(policies_get_modules($policy_id, false, ['id', 'name']));

    $predictionModuleInput = html_print_select(
        $modules,
        'id_module_policy',
        $module['custom_integer_1'],
        '',
        '',
        0,
        true,
        false,
        true,
        '',
        false,
        false,
        false,
        false,
        false,
        '',
        false,
        false,
        true
    );
}

$data[0] = $predictionModuleInput;
$data[1] = html_print_select(
    [
        '0' => __('Weekly'),
        '1' => __('Monthly'),
        '2' => __('Daily'),
    ],
    'custom_integer_2',
    $module['custom_integer_2'],
    '',
    '',
    0,
    true,
    false,
    true,
    '',
    false,
    false,
    false,
    false,
    false,
    '',
    false,
    false,
    true
);
$data[1] .= html_print_input_hidden('id_agente_module_prediction', $id_agente, true);

$table_simple->cellclass['prediction_module'][0] = 'w33p';
$table_simple->cellclass['prediction_module'][1] = 'w33p';
$table_simple->cellclass['prediction_module'][2] = 'w33p';
push_table_simple($data, 'prediction_module');

$data = [];
$data[0] = __('Calculation type');
$data[1] = __('Future estimation');
$data[2] = __('Limit value');
$table_simple->cellclass['caption_capacity_planning'][0] = 'w33p';
$table_simple->cellclass['caption_capacity_planning'][1] = 'w33p';
$table_simple->cellclass['caption_capacity_planning'][2] = 'w33p';
push_table_simple($data, 'caption_capacity_planning');

$data = [];
$data[0] = html_print_select(
    [
        'estimation_absolute'    => __('Estimated absolute value'),
        'estimation_calculation' => __('Calculation of days to reach limit'),
    ],
    'estimation_type',
    $estimation_type,
    '',
    '',
    0,
    true,
    false,
    true,
    '',
    false,
    'width: 100%;'
);

$data[1] = html_print_input(
    [
        'type'   => 'interval',
        'return' => 'true',
        'name'   => 'estimation_interval',
        'value'  => $estimation_interval,
        'class'  => 'w100p',
    ],
    'div',
    false
);

$data[2] = html_print_input(
    [
        'type'   => 'number',
        'return' => 'true',
        'id'     => 'estimation_days',
        'name'   => 'estimation_days',
        'value'  => $estimation_interval,
        'class'  => 'w100p',
    ]
);
$table_simple->cellclass['capacity_planning'][0] = 'w33p';
$table_simple->cellclass['capacity_planning'][1] = 'w33p';
$table_simple->cellclass['capacity_planning'][2] = 'w33p';
push_table_simple($data, 'capacity_planning');

// Removed common useless parameter.
unset($table_advanced->data[3]);
