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

// Extras required.
\ui_require_css_file('wizard');

// Header.
\ui_print_page_header(
    // Title.
    __('Alerts').' &raquo; '.__('Configure special day'),
    // Icon.
    'images/gm_alerts.png',
    // Return.
    false,
    // Help.
    'alert_special_days',
    // Godmode.
    true,
    // Options.
    $tabs
);

if (empty($message) === false) {
    echo $message;
}

$inputs = [];

// Date.
$inputs[] = [
    'label'     => __('Date'),
    'arguments' => [
        'type'     => 'text',
        'name'     => 'date',
        'required' => true,
        'value'    => $specialDay->date(),
    ],
    'extra'     => html_print_image(
        'images/calendar_view_day.png',
        true,
        [
            'alt'     => 'calendar',
            'onclick' => "scwShow(scwID('text-date'),this);",
            'class'   => 'invert_filter',
        ]
    ),
];

if (users_can_manage_group_all('LM') === true) {
    $display_all_group = true;
} else {
    $display_all_group = false;
}

// Group.
$inputs[] = [
    'label'     => __('Group'),
    'arguments' => [
        'type'           => 'select_groups',
        'returnAllGroup' => $display_all_group,
        'name'           => 'id_group',
        'selected'       => $specialDay->id_group(),
        'required'       => true,
    ],
];

$days = [];
$days[1] = __('Monday');
$days[2] = __('Tuesday');
$days[3] = __('Wednesday');
$days[4] = __('Thursday');
$days[5] = __('Friday');
$days[6] = __('Saturday');
$days[7] = __('Sunday');
$days[8] = __('Holidays');

// Same day of the week.
$inputs[] = [
    'label'     => __('Same day of the week'),
    'arguments' => [
        'name'     => 'day_code',
        'type'     => 'select',
        'fields'   => $days,
        'selected' => ($specialDay->day_code() === null) ? 1 : $specialDay->day_code(),
    ],
];

// Description.
$inputs[] = [
    'label'     => __('Description'),
    'arguments' => [
        'type'     => 'textarea',
        'name'     => 'description',
        'required' => false,
        'value'    => io_safe_output($specialDay->description()),
        'rows'     => 50,
        'columns'  => 30,
    ],
];

// Calendar.
$inputs[] = [
    'arguments' => [
        'type'  => 'hidden',
        'name'  => 'id_calendar',
        'value' => $specialDay->id_calendar(),
    ],
];

// Submit.
html_print_action_buttons(
    html_print_submit_button(
        (($create === true) ? __('Create') : __('Update')),
        'button',
        false,
        [
            'icon' => 'wand',
            'form' => 'form-special-days',
        ],
        true
    )
);

// Print form.
HTML::printForm(
    [
        'form'   => [
            'id'     => 'form-special-days',
            'action' => $url.'&action=save&id='.$specialDay->id(),
            'method' => 'POST',
        ],
        'inputs' => $inputs,
    ],
    false,
    true
);

echo '<div id="modal-alert-templates" class="invisible"></div>';

ui_require_javascript_file('calendar');
ui_require_javascript_file('pandora_alerts');

?>
<script type="text/javascript">
$(document).ready (function () {
    $("#submit-button").click (function (e) {
        e.preventDefault();
        var date = new Date($("#text-date").val());
        var dateformat = date.toLocaleString(
            'default',
            {day: 'numeric', month: 'short',  year: 'numeric'}
        );

        load_templates_alerts_special_days({
            date: $("#text-date").val(),
            id_group: $("#id_group").val(),
            day_code: $("#day_code").val(),
            id_calendar: '<?php echo $id_calendar; ?>',
            btn_ok_text: '<?php echo __('Create'); ?>',
            btn_cancel_text: '<?php echo __('Cancel'); ?>',
            title: dateformat,
            url: '<?php echo ui_get_full_url('ajax.php', false, false, false); ?>',
            page: '<?php echo $ajax_url; ?>',
            loading: '<?php echo __('Loading, this operation might take several minutes...'); ?>',
            name_form: 'form-special-days'
        });
    });
});
</script>