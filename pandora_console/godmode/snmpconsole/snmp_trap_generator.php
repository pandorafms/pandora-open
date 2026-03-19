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

// Check ACL
if (! check_acl($config['id_user'], 0, 'LW')) {
    db_pandora_audit(
        AUDIT_LOG_ACL_VIOLATION,
        'Trying to access SNMP Filter Management'
    );
    include 'general/noaccess.php';
    return;
}

require 'include/functions_snmp.php';
$snmp_host_address = (string) get_parameter('snmp_host_address', 'localhost');
$snmp_community = (string) get_parameter('snmp_community', 'public');
$snmp_oid = (string) get_parameter('snmp_oid', '');
$snmp_agent = (string) get_parameter('snmp_agent', '');
$snmp_type = (int) get_parameter('snmp_type', 0);
$snmp_value = (string) get_parameter('snmp_value', '');
$generate_trap = (bool) get_parameter('generate_trap', 0);

// Header.
ui_print_standard_header(
    __('SNMP Trap generator'),
    'images/op_snmp.png',
    false,
    'snmp_trap_generator_view',
    false,
    [],
    [
        [
            'link'  => '',
            'label' => __('Monitoring'),
        ],
        [
            'link'  => '',
            'label' => __('SNMP'),
        ],
    ]
);


if ($generate_trap) {
    $result = true;
    $error = '';
    if ($snmp_host_address != '' && $snmp_community != '' && $snmp_oid != '' && $snmp_agent != '' && $snmp_value != '' && $snmp_type != -1) {
        $result = snmp_generate_trap($snmp_host_address, $snmp_community, $snmp_oid, $snmp_agent, $snmp_value, $snmp_type);

        if ($result !== true) {
            $error = $result;
            $result = false;
        }
    } else {
        $error = __('Empty parameters');
        $result = false;
    }

    ui_print_result_message(
        $result,
        __('Successfully generated'),
        sprintf(__('Could not be generated: %s'), $error)
    );
}

$table = new stdClass();
$traps_generator = '<form class="max_floating_element_size" method="POST" action="index.php?sec=snmpconsole&sec2=godmode/snmpconsole/snmp_trap_generator">';
$table->width = '100%';
$table->class = 'filter-table-adv databox';
$table->size = [];
$table->data = [];
$table->size[0] = '50%';
$table->size[1] = '50%';
$table->size[2] = '50%';

$table->data[0][0] = html_print_label_input_block(
    __('Host address'),
    html_print_input_text(
        'snmp_host_address',
        $snmp_host_address,
        '',
        50,
        255,
        true
    )
);

$table->data[0][1] = html_print_label_input_block(
    __('Community'),
    html_print_input_text(
        'snmp_community',
        $snmp_community,
        '',
        50,
        255,
        true
    )
);

$table->data[1][0] = html_print_label_input_block(
    __('Enterprise String'),
    html_print_input_text(
        'snmp_oid',
        $snmp_oid,
        '',
        50,
        255,
        true
    )
);

$table->data[1][2] = html_print_label_input_block(
    __('Value'),
    html_print_input_text(
        'snmp_value',
        $snmp_value,
        '',
        50,
        255,
        true
    )
);

$table->data[2][0] = html_print_label_input_block(
    __('SNMP Agent'),
    html_print_input_text(
        'snmp_agent',
        $snmp_agent,
        '',
        50,
        255,
        true
    )
);

$types = [
    0 => 'Cold start (0)',
    1 => 'Warm start (1)',
    2 => 'Link down (2)',
    3 => 'Link up (3)',
    4 => 'Authentication failure (4)',
    5 => 'EGP neighbor loss (5)',
    6 => 'Enterprise (6)',
];
$table->data[2][1] = html_print_label_input_block(
    __('SNMP Type'),
    html_print_select(
        $types,
        'snmp_type',
        $snmp_type,
        '',
        __('Select'),
        -1,
        true,
        false,
        false,
        '',
        false,
        'width: 100%'
    )
);

$traps_generator .= html_print_table($table, true);
$buttons[] = html_print_submit_button(
    __('Generate trap'),
    'btn_generate_trap',
    false,
    [
        'class' => 'sub ok submitButton',
        'icon'  => 'next',
    ],
    true
);
$traps_generator .= '<div class="action-buttons">'.html_print_action_buttons(implode('', $buttons), ['type' => 'form_action'], true).'</div>';
$traps_generator .= html_print_input_hidden('generate_trap', 1, true);

unset($table);
$traps_generator .= '</form>';

echo $traps_generator;
